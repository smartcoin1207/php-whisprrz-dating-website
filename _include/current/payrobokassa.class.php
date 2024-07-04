<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PayRobokassa extends Pay
{

    public static $system = 'robokassa';

    public static function signatureRequest($params)
    {
        $signature = md5("{$params['MerchantLogin']}:{$params['OutSum']}:{$params['InvoiceID']}:" . (isset($params['OutSumCurrency']) ? $params['OutSumCurrency'] . ':' : '') . Common::getOption('password_1', self::getSystem()));
        return $signature;
    }

    public static function checkSignature($params, $passwordType = 1)
    {
        $signature = md5("{$params['OutSum']}:{$params['InvId']}:" . Common::getOption('password_' . $passwordType, self::getSystem()));
        return (strtoupper($params['SignatureValue']) == strtoupper($signature));
    }

    public static function before()
    {
        $item = Pay::checkPlan();
        if ($item === false) {
            exit;
        }

        $requestUri = Pay::getRequestUri();

        $plan = Pay::getOptionsPlan($item);
        $code = Pay::getCode($item, $plan['amount']);

        $data = array(
            'item' => $item,
            'system' => self::getSystem(),
            'code' => $code,
            'request_uri' => $requestUri
        );

        $params = array();
        $params['MerchantLogin'] = Common::getOption('merchant_login', self::getSystem());
        $params['OutSum'] = $plan['amount'];
        $params['InvoiceID'] = self::insertBefore($data);
        $params['Description'] = $plan['item_name'];
        $params['Encoding'] = 'utf-8';

        $currencyCode = trim(Common::getOption('currency_code', self::getSystem()));
        if($currencyCode) {
            $params['OutSumCurrency'] = $currencyCode;
        }

        $params['SignatureValue'] = self::signatureRequest($params);

        $url = 'https://merchant.roboxchange.com/Index.aspx?' . http_build_query($params);
        redirect($url);
    }

    public static function after()
    {
        $custom = '';

        $paymentId = get_param('InvId');

        $paymentBefore = self::getPaymentBeforeById($paymentId);
        if ($paymentBefore) {

            $signaturePasswordType = 1;
            $type = get_param('type', 'result');

            if ($type != 'fail') {
                if ($type == 'result') {
                    $signaturePasswordType = 2;
                }

                $plan = self::getOptionsPlan($paymentBefore['item']);
                if ($plan) {
                    if (self::checkSignature($_REQUEST, $signaturePasswordType)) {
                        $custom = $paymentBefore['code'];
                    }
                }

                if ($type == 'result') {
                    self::getAfterHtml($custom, self::getSystem());
                    echo "OK$paymentId\n";
                    return;
                }
            }
        }

        echo self::getAfterHtml($custom, self::getSystem());
    }

}
