<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PayFortumo extends Pay
{
    public static $system = 'fortumo';

    public static function signature($params, $secret)
    {
        ksort($params);

        $str = '';
        foreach ($params as $k => $v) {
            if ($k != 'sig') {
                $str .= "$k=$v";
            }
        }
        $str .= $secret;
        $signature = md5($str);

        return $signature;
    }

    public static function checkSignature($params, $secret)
    {
        $signature = self::signature($params, $secret);
        return ($params['sig'] == $signature);
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
        $params['product_name'] = $plan['item_name'];
        $params['cuid'] = self::insertBefore($data);
        $params['callback_url'] = self::callbackUrl(array('client_return_action' => 1, 'cuid' => $params['cuid']));

        if($plan['type'] == 'credits') {
            unset($params['product_name']);
            $params['credit_name'] = l('credits');
        }

        $params['sig'] = self::signature($params, $plan['fortumo_secret']);

        $url = "https://pay.fortumo.com/mobile_payments/{$plan['fortumo_service_id']}?" . http_build_query($params);

        redirect($url);
    }

    public static function after()
    {
        $custom = '';
        $paymentBefore = self::getPaymentBeforeById(get_param('cuid'));

        if ($paymentBefore) {

            if(get_param('client_return_action')) {
                self::setAcceptPayment(false);
                $custom = $paymentBefore['code'];
            } else {
                $plan = self::getOptionsPlan($paymentBefore['item']);
                if ($plan) {
                    if (self::checkSignature($_GET, $plan['fortumo_secret'])) {
                        if (preg_match("/completed/i", get_param('status'))) {
                            $custom = $paymentBefore['code'];
                        }
                    }
                }
            }
        }

        echo self::getAfterHtml($custom, self::getSystem());
    }

}