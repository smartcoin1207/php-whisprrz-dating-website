<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PaySkrill extends Pay
{
    public static $system = 'skrill';

    public static function before()
    {
        $order = self::createOrder();

        $params = array(
            'pay_to_email' => Common::getOption('email', self::getSystem()),
            'amount' => floatval(self::getPlanField('amount')),
            'currency' => Common::getOption('currency_code', self::getSystem()),
            'transaction_id' => $order,
            'status_url' => self::callbackUrl(),
            'return_url' => self::callbackUrl(array('client_return_action' => 1)),
            'cancel_url' => self::getRequestUri(Common::urlSiteSubfolders() . '/upgrade.php'),
        );

        $url = 'https://pay.skrill.com?' . http_build_query($params);

        redirect($url);
    }

    public static function after()
    {
        $custom = '';
        $paymentId = get_param('transaction_id');

        log_payment($paymentId);

        $paymentBefore = self::getPaymentBeforeById($paymentId);

        if ($paymentBefore) {

            $status = intval(get_param('status'));

            if ($status == 2) {
                $plan = self::getOptionsPlan($paymentBefore['item']);
                if ($plan) {
                    if (get_param('md5sig') == self::signature()) {
                        $custom = $paymentBefore['code'];
                    }
                }
            }
        }

        $result = self::getAfterHtml($custom, self::getSystem());

        if(!get_param('client_return_action')) {
            $result = 'OK';
        }

        echo $result;
    }

    public static function signature()
    {
        $secretWord = trim(Common::getOption('secret_word', self::getSystem()));

        $string = get_param('merchant_id') . get_param('transaction_id') . strtoupper(md5($secretWord)) . get_param('mb_amount') . get_param('mb_currency') . get_param('status');

        $signature = md5($string);

        return strtoupper($signature);
    }

}