<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PayMultisafepay extends Pay
{

    public static $system = 'multisafepay';
    public static $msp = null;

    public static function before()
    {
        $order = self::createOrder();

        self::initPaymentSystem();

        self::$msp->merchant['notification_url'] = self::callbackUrl(array('type' => 'initial'));
        self::$msp->merchant['cancel_url'] = self::getRequestUri(Common::urlSiteSubfolders() . '/upgrade.php');
        // optional automatic redirect back to the shop:
        self::$msp->merchant['redirect_url'] = self::redirectUrl($order);

        /*
         * Transaction Details
         */
        self::$msp->transaction['id'] = $order; // generally the shop's order ID is used here
        self::$msp->transaction['currency'] = Common::getOption('currency_code', self::getSystem());
        self::$msp->transaction['amount'] = self::getPlanField('amount') * 100; // cents
        self::$msp->transaction['description'] = self::getPlanField('item_name');

        // returns a payment url
        $url = self::$msp->startTransaction();

        if (self::$msp->error) {
            echo 'Error ' . self::$msp->error_code . ': ' . self::$msp->error;
            exit();
        }

        redirect($url);
    }

    public static function after()
    {
        self::initPaymentSystem();

        // transaction id (same as the transaction->id given in the transaction request)
        $transactionid = get_param('transactionid');

        // (notify.php?type=initial is used as notification_url and should output a link)
        $initial = (get_param('type') == 'initial');

        /*
         * Transaction Details
         */
        self::$msp->transaction['id'] = $transactionid;

        // returns the status
        $status = self::$msp->getStatus();

        $p['status'] = $status;

        log_payment($p);

        if (self::$msp->error && !$initial) { // only show error if we dont need to display the link
            echo "Error " . self::$msp->error_code . ": " . self::$msp->error;
            exit();
        }

        switch ($status) {
            case "initialized": // waiting
                break;
            case "completed":   // payment complete
                break;
            case "uncleared":   // waiting (credit cards or direct debit)
                break;
            case "void":        // canceled
                break;
            case "declined":    // declined
                break;
            case "refunded":    // refunded
                break;
            case "expired":     // expired
                break;
            default:
        }

        $custom = '';

        $paymentBefore = self::getPaymentBeforeById($transactionid);
        if ($paymentBefore) {
            $plan = self::getOptionsPlan($paymentBefore['item']);
            if ($plan && $status == 'completed') {
                $custom = $paymentBefore['code'];
            }
        }

        self::getAfterHtml($custom, self::getSystem());

        if ($initial) {
            // displayed at the last page of the transaction proces (if no redirect_url is set)
            echo '<a href="' . self::redirectUrl($transactionid) . '">Return</a>';
        } else {
            // link to notify.php for MultiSafepay back-end (for delayed payment notifications)
            // backend expects an "ok" if no error occurred
            echo "ok";
        }

        if (get_param('return_success')) {
            echo self::getAfterHtml($custom, self::getSystem());
        }
    }

    public static function initPaymentSystem()
    {
        self::$msp = new MultiSafepay();
        self::$msp->test = Common::getOption('demo', self::getSystem()) == 'Y' ? true : false;
        self::$msp->merchant['account_id'] = Common::getOption('account_id', self::getSystem());
        self::$msp->merchant['site_id'] = Common::getOption('site_id', self::getSystem());
        self::$msp->merchant['site_code'] = Common::getOption('site_code', self::getSystem());
    }

    public static function redirectUrl($transactionid)
    {
        return self::callbackUrl(array('transactionid' => $transactionid, 'return_success' => '1'));
    }

}
