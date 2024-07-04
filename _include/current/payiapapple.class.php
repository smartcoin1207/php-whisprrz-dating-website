<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PayIapApple extends Pay
{
    public static $system = 'iapapple';

    const ERROR_INVALID_PAYLOAD = 6778001;

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

            if(isset($paymentInfo['transaction']['appStoreReceipt'])) {

                $result['data']['step'] = 'Receipt';

                $receipt = self::prepareReceiptData($paymentInfo['transaction']['appStoreReceipt']);

                if($receipt) {

                    $result['data']['step'] = 'Receipt Check';

                    $productId = isset($receipt['latest_receipt_info'][0]['product_id']) ? $receipt['latest_receipt_info'][0]['product_id'] : 0;

                    $transactionsInReceipt = array();

                    if(isset($receipt['latest_receipt_info'])) {
                        foreach($receipt['latest_receipt_info'] as $latestReceiptItem) {
                            $transactionsInReceipt[] = $latestReceiptItem['transaction_id'];
                        }
                    }

                    $paymentPlan = self::getPaymentPlan($productId);
                    if($paymentPlan) {
                        $isCredits = ($paymentPlan['type'] == 'credits');
                        $transaction = isset($receipt['latest_receipt_info'][0]) ? $receipt['latest_receipt_info'][0] : false;
                        $result = self::processPurchase($receipt['receipt']['bundle_id'], $productId, $transaction, $isCredits, $transactionsInReceipt, $result);
                        if(!$isCredits && isset($paymentInfo['transactions'])) {
                            $result['data']['customTransactions'] = $paymentInfo['transactions'];
                        }
                    } else {
                        if($paymentInfo['type'] === 'application') {
                           $result['ok'] = true;
                        } else {
                            $result['error']['message'] = "Payment plan for $productId not found";
                        }
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

        $message = '';

        $transaction = get_param('transaction');

        log_payment(array('user_id' => $uid, 'cmd' => 'finish', 'transaction' => $transaction));

        if(!isset($transaction['id']) && isset($transaction['appStoreReceipt'])) {

            $receipt = self::prepareReceiptData($transaction['appStoreReceipt']);

            if($receipt) {

                $result = 'Receipt Check Error';

                if(isset($receipt['latest_receipt_info'][0]['transaction_id'])) {
                    $transaction = $receipt['latest_receipt_info'][0];
                }

            }
        }

        $transaction['id'] = self::prepareTransactionId($transaction);

        if($transaction['id'] !== 0) {

            $paymentBefore = self::getPaymentBeforeBySubscriptionId($transaction['id']);

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
                        if($paymentBefore['subscription_expiry_time'] > time()) {

                            if(strpos(get_param('page'), 'upgrade') !== false) {
                                $paymentBefore['request_uri'] = 'upgrade.php?result=paid';
                            }

                        } else {
                            $result = 'no_alert';
                            $message = 'old subscription notification ' . $paymentBefore['subscription_expiry_time'];
                        }
                    }

                } else {
                    $result = 'no_alert';
                    $message = 'Payment Already Accepted';
                }
            } else {
                $result = 'Payment Not Found ' . $transaction['id'];
            }

        } else {
            $result = 'no_alert';
            $message = 'Transaction ID Not Found';
        }

        $data = array('result' => $result, 'message' => $message, 'credits' => $credits, 'request_uri' => isset($paymentBefore['request_uri']) ? $paymentBefore['request_uri'] : '');
        echo getResponseDataAjax($data);
    }

    private static function prepareReceiptData($appStoreReceipt)
    {
        $receipt = null;

        $receipt = self::decodeReceipt($appStoreReceipt);

        if($receipt) {
            if(isset($receipt['latest_receipt_info'])) {

                $fields = array(
                    'bundle_id',
                    'in_app',
                );

                foreach($fields as $field) {
                    if(!isset($receipt['receipt'][$field])) {
                        $receipt = null;
                        break;
                    }
                }
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
        if(isset($data['unified_receipt']) && $data['password'] == trim(Common::getOption('shared_secret', self::getSystem()))) {
            if(isset($data['unified_receipt']['latest_receipt_info'][0])) {
                $transaction = $data['unified_receipt']['latest_receipt_info'][0];
                $packageName = $data['bid'];
                $productId = $transaction['product_id'];
                $originalTransactionId = $transaction['original_transaction_id'];

                $payment = self::getPaymentBeforeApp($packageName, $productId, $originalTransactionId);
                if($payment) {
                    $subscriptionExpireTime = $transaction['expires_date_ms'] / 1000;
                    self::setSubscriptionExpireTimestamp($subscriptionExpireTime);
                    self::updateBeforeById($payment['id'], array('subscription_expiry_time' => $subscriptionExpireTime));
                    self::getOptionsPayment($payment['code'], self::$system);
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
        return DB::one('payment_plan', '`iapapple_product_id` = ' . to_sql($productId));
    }

    public static function subscriptionRevoke($packageName, $productId, $purchaseToken)
    {

    }

    public static function subscriptionCancel($packageName, $productId, $purchaseToken)
    {

    }

    private static function setPaymentPlanItemByAppProductId($productId)
    {
        $sql = 'SELECT `item` FROM `payment_plan`
            WHERE `iapapple_product_id` = ' . to_sql($productId);
        $item = DB::result($sql);

        $_GET['item'] = $item;

        return $item;
    }

    private static function processPurchase($packageName, $productId, $transaction, $isCredits, $transactionsInReceipt, $result)
    {
        $type = false;

        $result['data']['step'] = "purchase > $isCredits";

        if($isCredits) {
            $type = 'credits';
        } else {
            $type = 'subscription';
        }

        $transactionId = self::prepareTransactionId($transaction);
        $subscriptionExpireTime = isset($transaction['expires_date_ms']) ? ($transaction['expires_date_ms'] / 1000) : 0;

        if($type !== false && self::addOrder($type, $packageName, $productId, $transactionId, $subscriptionExpireTime)) {
            $result['ok'] = true;
            unset($result['data']['code']);
            $result['data']['customTransactionId'] = $transaction['transaction_id'];
            $result['data']['customOriginalTransactionId'] = $transaction['original_transaction_id'];
            $result['data']['customProductId'] = $productId;
            $result['data']['customTransactionsInReceipt'] = $transactionsInReceipt;
        }

        return $result;
    }

    public static function decodeReceipt($receipt)
    {
        $urlProduct = 'https://buy.itunes.apple.com/verifyReceipt';
        $urlSandbox = 'https://sandbox.itunes.apple.com/verifyReceipt';

        $decodedReceipt = self::decodeReceiptSendRequest($receipt, $urlProduct);
        if($decodedReceipt !== null && $decodedReceipt['status'] === 21007) {
            $decodedReceipt = self::decodeReceiptSendRequest($receipt, $urlSandbox);
        }

        log_payment(array('decodeReceipt result' => $decodedReceipt));
        return $decodedReceipt;
    }

    private static function decodeReceiptSendRequest($receipt, $url)
    {
        $decodedReceipt = null;

        $params = json_encode(array(
            'receipt-data' => $receipt,
            'password' => trim(Common::getOption('shared_secret', self::getSystem())),
            'exclude-old-transactions' => true,
        ));

        $headers = array(
            'Content-Type:application/json',
        );

        $decodedReceiptJson = urlGetContents($url, 'post', $params, 60, false, $headers);
        if($decodedReceiptJson) {
            $decodedReceipt = json_decode($decodedReceiptJson, true);
        }

        return $decodedReceipt;
    }

    private static function prepareTransactionId($transaction)
    {
        $transactionId = 0;

        if(isset($transaction['original_transaction_id'])) {
            $transactionId = $transaction['original_transaction_id'];
        } elseif (isset($transaction['transaction_id'])) {
            $transactionId = $transaction['transaction_id'];
        } elseif (isset($transaction['id'])) {
            $transactionId = $transaction['id'];
        }

        return $transactionId;
    }

}