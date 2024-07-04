<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include_once(__DIR__ . '/google-api-php-client/vendor/autoload.php');

class PayIapGoogle extends Pay
{
    public static $system = 'iapgoogle';

    const ERROR_INVALID_PAYLOAD = 6778001;
    const ERROR_CONNECTION_FAILED = 6778002;
    const ERROR_PURCHASE_EXPIRED = 6778003;
    const ERROR_PURCHASE_CONSUMED = 6778004;
    const ERROR_INTERNAL_ERROR = 6778005;
    const ERROR_NEED_MORE_DATA = 6778006;

    private static $googleService = null;

    public static function after()
    {
        self::$setSessionUpgradeNotification = false;

        $cmd = get_param('cmd', '');
        if($cmd == 'verify') {
            self::verify();
        } elseif($cmd == 'finish') {
            self::finish();
        } elseif($cmd == 'notification') {
            self::realTimeNotification();
        }

    }

    private static function verify()
    {
        $result = array(
            'ok' => false,
            'data' => array(
                'code' => self::ERROR_INVALID_PAYLOAD,
            ),
        );

        $result['data']['step'] = 'Start';

        $paymentInfo = self::getDataFromRequest();

        if($paymentInfo) {

            $result['data']['step'] = 'Payload';

            if(isset($paymentInfo['transaction']['receipt'])) {

                $result['data']['step'] = 'Receipt';

                $receipt = self::prepareReceiptData($paymentInfo['transaction']['receipt']);

                if($receipt) {

                    $result['data']['step'] = 'Receipt Check';

                    $productId = $receipt['productId'];

                    $paymentPlan = self::getPaymentPlan($productId);
                    if($paymentPlan) {
                        $isCredits = ($paymentPlan['type'] == 'credits');
                        $result = self::processPurchase($receipt['packageName'], $productId, $receipt['purchaseToken'], $isCredits, $result);
                    } else {
                        $result['error']['message'] = "Payment plan for $productId not found";
                    }
                }
            }
        }

        echo json_encode($result);
    }

    private static function finish()
    {
        $result = 'Error';
        $credits = 0;
        $uid = guid();

        $transaction = get_param('transaction');

        log_payment(array('user_id' => $uid, 'cmd' => 'finish', 'transaction' => $transaction));

        if(isset($transaction['receipt'])) {
            $receipt = self::prepareReceiptData($transaction['receipt']);
            if($receipt) {

                $paymentBefore = self::getPaymentBeforeApp($receipt['packageName'], $receipt['productId'], $receipt['purchaseToken']);

                if ($paymentBefore) {

                    if($paymentBefore['type'] === 'subscription') {
                        self::setSubscriptionExpireTimestamp($paymentBefore['subscription_expiry_time']);
                    }

                    self::getOptionsPayment($paymentBefore['code'], self::$system);

                    if(self::getAcceptPayment()) {

                        $result = 'ok';

                        if(self::getTypePaymentPlan($paymentBefore['item']) == 'credits') {
                            $credits = User::getInfoBasic($uid, 'credits', 0, false);
                        } else {
                            if(strpos(get_param('page'), 'upgrade') !== false) {
                                $paymentBefore['request_uri'] = 'upgrade.php?result=paid';
                            }
                        }

                    } else {
                        $result = 'Payment already finished';
                    }
                } else {
                    $result = 'Payment Not Found';
                }
            } else {
                $result = 'Receipt Prepare Error';
            }

        } else {
            $result = 'Receipt Not Found';
        }

        $data = array('result' => $result, 'credits' => $credits, 'request_uri' => $paymentBefore['request_uri']);
        echo getResponseDataAjax($data);
    }

    private static function prepareReceiptData($receiptJson)
    {
        $receipt = json_decode($receiptJson, true);

        $fields = array(
            'packageName',
            'productId',
            'purchaseTime',
            'purchaseState',
            'purchaseToken',
        );

        foreach($fields as $field) {
            if(!isset($receipt[$field])) {
                $receipt = null;
                break;
            }
        }

        return $receipt;
    }

    private static function addOrder($type, $packageName, $productId, $purchaseToken, $subscriptionExpireTime = false)
    {
        $siteOrderId = 0;

        $order = self::getPaymentBeforeApp($packageName, $productId, $purchaseToken);
        if($order) {
            $siteOrderId = $order['id'];
            if($subscriptionExpireTime !== false) {
                self::updateBeforeById($siteOrderId, array('subscription_expiry_time' => $subscriptionExpireTime));
            }
        } else {
            if(self::setPaymentPlanItemByAppProductId($productId)) {

                $data = array(
                    'type' => $type,
                    'app_package_name' => $packageName,
                    'app_product_id' => $productId,
                    'subscription_id' => $purchaseToken,
                );

                if($subscriptionExpireTime !== false) {
                    $data['subscription_expiry_time'] = $subscriptionExpireTime;
                }

                $siteOrderId = self::createOrder($data);
                self::deleteOrderDuplicates($packageName, $productId, $purchaseToken);

            }
        }

        return $siteOrderId;
    }

    private static function deleteOrderDuplicates($packageName, $productId, $purchaseToken)
    {
        $where = '`system` = ' . to_sql(self::$system) .
            ' AND `subscription_id` = ' . to_sql($purchaseToken) .
            ' AND `app_package_name` = ' . to_sql($packageName) .
            ' AND `app_product_id` = ' . to_sql($productId);

        $sql = 'DELETE FROM `payment_before`
            WHERE ' . $where . '
                AND `id` != (
                    SELECT MIN(`id`) FROM (
                        SELECT * FROM `payment_before`
                        WHERE ' . $where . ') AS p
                )';
        DB::execute($sql);
    }

    private static function realTimeNotification()
    {
        http_response_code(502);

        $data = self::getDataFromRequest();
        if(isset($data['message']['data'])) {
            $messageData = json_decode(base64_decode($data['message']['data']), true);
            if(isset($messageData['subscriptionNotification'])) {
                $addSubscriptionTypes = array(
                    1, // SUBSCRIPTION_RECOVERED - A subscription was recovered from account hold.
                    2, // SUBSCRIPTION_RENEWED - An active subscription was renewed.
                    4, // SUBSCRIPTION_PURCHASED - A new subscription was purchased.
                    7, // SUBSCRIPTION_RESTARTED - User has reactivated their subscription from Play > Account > Subscriptions (requires opt-in for subscription restoration).
                    12, // SUBSCRIPTION_REVOKED - A subscription has been revoked from the user before the expiration time.
                );

                $notificationType = $messageData['subscriptionNotification']['notificationType'];

                if(in_array($notificationType, $addSubscriptionTypes)) {

                    $packageName = $messageData['packageName'];
                    $productId = $messageData['subscriptionNotification']['subscriptionId'];
                    $purchaseToken = $messageData['subscriptionNotification']['purchaseToken'];

                    $payment = self::getPaymentBeforeApp($packageName, $productId, $purchaseToken);

                    if($payment) {
                        $purchase = self::getPurchaseData($packageName, $productId, $purchaseToken, false);
                        if($purchase) {
                            if($notificationType === 12) {
                                $time = time();
                                if($payment['subscription_expiry_time'] > $time) {
                                    self::updateBeforeById($payment['id'], array('subscription_expiry_time' => $time));
                                    User::update(array('type' => 'none', 'gold_days' => 0), $payment['user_id']);
                                }
                            } else {
                                $expireTime = ceil($purchase->expiryTimeMillis / 1000);
                                self::setSubscriptionExpireTimestamp($expireTime);
                                self::updateBeforeById($payment['id'], array('subscription_expiry_time' => $expireTime));
                                self::getOptionsPayment($payment['code'], self::$system);
                            }
                        }
                    } else {
                        //if(!self::subscriptionRevoke($packageName, $productId, $purchaseToken)) {
                        //    return;
                        //}
                    }
                }
            } elseif(isset($messageData['oneTimeProductNotification'])) {

                if($messageData['oneTimeProductNotification']['notificationType'] == 1) {

                    $packageName = $messageData['packageName'];
                    $productId = $messageData['oneTimeProductNotification']['sku'];
                    $purchaseToken = $messageData['oneTimeProductNotification']['purchaseToken'];

                    $payment = self::getPaymentBeforeApp($packageName, $productId, $purchaseToken);

                    if($payment) {
                        $purchase = self::getPurchaseData($packageName, $productId, $purchaseToken, true);
                        if($purchase) {
                            self::getOptionsPayment($payment['code'], self::$system);
                        }
                    }
                }
            }
        }

        http_response_code(200);
    }

    private static function getDataFromRequest()
    {
        $data = false;

        $json = @file_get_contents('php://input');

        log_payment(array('user_id' => guid(), 'json' => $json));

        if($json) {
            $data = json_decode($json, true);
        }

        return $data;
    }

    private static function getPaymentPlan($productId)
    {
        return DB::one('payment_plan', '`iapgoogle_product_id` = ' . to_sql($productId));
    }

    public static function subscriptionRevoke($packageName, $productId, $purchaseToken)
    {
        $isRevoked = false;
        try {
            $service = self::getGoogleService();
            $result = $service->purchases_subscriptions->revoke($packageName, $productId, $purchaseToken);

            if($result && $result->getStatusCode() === 204) {
                $isRevoked = true;
                $paymentBefore = self::getPaymentBeforeApp($packageName, $productId, $purchaseToken);
                $time = time();
                if($paymentBefore && $paymentBefore['subscription_expiry_time'] > $time) {
                    self::updateBeforeById($paymentBefore['id'], array('subscription_expiry_time' => $time));
                    User::update(array('type' => 'none', 'gold_days' => 0), $paymentBefore['user_id']);
                }
            }
        } catch (Exception $e) {
            // subscription not exists
            if($e->getCode() == 400) {
                $isRevoked = true;
            }
        }

        return $isRevoked;
    }

    public static function subscriptionCancel($packageName, $productId, $purchaseToken)
    {
        $isCancelled = false;
        try {
            $service = self::getGoogleService();
            $result = $service->purchases_subscriptions->cancel($packageName, $productId, $purchaseToken);

            if($result && $result->getStatusCode() === 204) {
                $isCancelled = true;
            }
        } catch (Exception $e) {
            // subscription not exists
            if($e->getCode() == 400) {
                $isCancelled = true;
            }
        }

        return $isCancelled;
    }

    private static function getGoogleService()
    {
        if(self::$googleService === null) {
            $client = new \Google_Client();
            $client->setAuthConfig(trim(Common::getOption('service_account_key', self::getSystem())), false);
            $client->addScope('https://www.googleapis.com/auth/androidpublisher');
            self::$googleService = new \Google_Service_AndroidPublisher($client);
        }

        return self::$googleService;
    }

    private static function setPaymentPlanItemByAppProductId($productId)
    {
        $sql = 'SELECT `item` FROM `payment_plan`
            WHERE `iapgoogle_product_id` = ' . to_sql($productId);
        $item = DB::result($sql);

        $_GET['item'] = $item;

        return $item;
    }

    private static function getPurchaseData($packageName, $productId, $purchaseToken, $isCredits)
    {
        // TODO: catch connection error via curl

        $service = self::getGoogleService();

        $purchase = false;

        if($isCredits) {
            $purchase = $service->purchases_products->get($packageName, $productId, $purchaseToken);
        } else {
            $purchase = $service->purchases_subscriptions->get($packageName, $productId, $purchaseToken);
        }

        log_payment(array('purchase' => var_export($purchase, true)));

        return $purchase;
    }

    private static function processPurchase($packageName, $productId, $purchaseToken, $isCredits, $result)
    {
        $purchase = self::getPurchaseData($packageName, $productId, $purchaseToken, $isCredits);
        if($purchase) {

            $type = false;
            $subscriptionExpireTime = false;

            $result['data']['step'] = "purchase > $isCredits";

            if($isCredits) {
                if($purchase->consumptionState === 0 && $purchase->purchaseState === 0) {
                    $type = 'credits';
                }
            } else {
                // paymentState promo = 2
                if($purchase->acknowledgementState === 0 && $purchase->paymentState >= 1) {
                    $type = 'subscription';
                    $subscriptionExpireTime = ceil($purchase->expiryTimeMillis / 1000);
                }

                // for pause subscription and other similar requests
                if($purchase->acknowledgementState === 1 && $purchase->paymentState === 1) {
                    $result['ok'] = true;
                    $result['data']['step'] = "subscription acknowledgementState === 1";
                    unset($result['data']['code']);
                }
            }

            if($type !== false && self::addOrder($type, $packageName, $productId, $purchaseToken, $subscriptionExpireTime)) {
                $result['ok'] = true;
                unset($result['data']['code']);
            }
        } else {
            $result['error']['message'] = 'No purchase info';
        }

        return $result;
    }

}