<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PayPayuLatam extends Pay
{
    public static $system = 'payulatam';

    public static function before()
    {
        $order = self::createOrder();

        $isDemo = Common::isOptionActive('demo', self::getSystem());
        if($isDemo) {
            $order = $_SERVER['HTTP_HOST'] . $order;
        }

        $params = array(
            'merchantId' => Common::getOption('merchant_id', self::getSystem()),
            'accountId' => Common::getOption('account_id', self::getSystem()),
            'description' => self::getPlanField('item_name'),
            'referenceCode' => $order,
            'amount' => floatval(self::getPlanField('amount')),
            'currency' => Common::getOption('currency_code', self::getSystem()),

            'signature' => self::signature(array(
                Common::getOption('api_key', self::getSystem()),
                Common::getOption('merchant_id', self::getSystem()),
                $order,
                floatval(self::getPlanField('amount')),
                Common::getOption('currency_code', self::getSystem()),
            )),
            'algorithmSignature' => 'MD5',

            'confirmationUrl' => self::callbackUrl(),
            'responseUrl' => self::callbackUrl(array('client_return_action' => 1)),
        );

        $url = 'https://' . ($isDemo ? 'sandbox.' : '') . 'gateway.payulatam.com/ppp-web-gateway/?' . http_build_query($params);

        redirect($url);
    }

    public static function after()
    {
        $custom = '';
        $paymentId = get_param('reference_sale');

        log_payment($paymentId);

        $isResponsePage = get_param('client_return_action');

        $paymentBefore = self::getPaymentBeforeById($paymentId);

        if ($paymentBefore) {

            $statusParam = 'state_pol';
            if($isResponsePage) {
                $statusParam = 'transactionState';
            }

            $status = intval(get_param($statusParam));

            if ($status == 4) {
                $plan = self::getOptionsPlan($paymentBefore['item']);
                if ($plan) {

                    $newValue = (string) get_param('value');
                    if(substr($newValue, -1) === '0') {
                        $newValue = substr($newValue, 0, -1);
                    }

                    if($isResponsePage) {
                        $newValue = round(get_param('value'), 1, PHP_ROUND_HALF_EVEN);
                    }

                    $params = array(
                        Common::getOption('api_key', self::getSystem()),
                        Common::getOption('merchant_id', self::getSystem()),
                        $paymentId,
                        $newValue,
                        get_param('currency'),
                        $status,
                    );

                    if (get_param('sign') == self::signature($params)) {
                        $custom = $paymentBefore['code'];
                    }
                }
            }
        }

        $result = self::getAfterHtml($custom, self::getSystem());

        if(!$isResponsePage) {
            $result = 'OK';
        }

        echo $result;
    }

    public static function signature($params)
    {
        $string = implode('~', $params);
        $signature = md5($string);

        return $signature;
    }

}