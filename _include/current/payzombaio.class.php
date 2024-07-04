<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PayZombaio extends Pay
{
    public static $system = 'zombaio';
    public static $url = '';
    public static $pspid = '';

    public static function before()
    {
        $order = self::createOrder();

        $isCredits = (self::getPlanField('type') === 'credits');

        $params = array(
            'SiteID' => trim(Common::getOption('siteid', self::getSystem())),
            'PricingID' => trim(self::getPlanField('zombaio_pricing_id')),
            'return_url' => self::callbackUrl(array('client_return_action' => 1)),
            'return_url_approve' => self::callbackUrl(array('client_return_action' => 1)),
            'return_url_decline' => self::getRequestUri(Common::urlSiteSubfolders() . '/upgrade.php'),
            'return_url_error' => self::callbackUrl(array('client_return_action' => 1)),
        );

        if($isCredits) {
            $params['identifier'] = $order;
            $params['approve_url'] = self::callbackUrl(array('client_return_action' => 1));
            $params['decline_url'] = self::getRequestUri(Common::urlSiteSubfolders() . '/upgrade.php');
            $params['DynAmount_Value'] = self::getPlanField('amount');
            $params['DynAmount_Hash'] = md5(trim(Common::getOption('zombaiogwpass', self::getSystem())) . $params['DynAmount_Value']);
        } else {
            $params['processor_id'] = $order;
        }

        $creditsPayment = '';
        if($isCredits) {
            $creditsPayment = '_credits';
        }

        $url = "https://secure.zombaio.com/get_proxy{$creditsPayment}.asp?" . http_build_query($params);

        redirect($url);
    }

    public static function after()
    {
        $custom = '';

        $subscriptionId = get_param('SUBSCRIPTION_ID');

        $paymentId = get_param('processor_id');
        if(!$paymentId) {
            $paymentId = get_param('Identifier');
        }
        if(!$paymentId && $subscriptionId) {
            $paymentInfo = self::getPaymentBeforeBySubscriptionId($subscriptionId);
            if(isset($paymentInfo['id'])) {
                $paymentId = $paymentInfo['id'];
            }
        }

        log_payment($paymentId);

        $paymentBefore = self::getPaymentBeforeById($paymentId);

        $isClientReturnAction = get_param('client_return_action');

        $action = get_param('Action');

		if(!$isClientReturnAction && !$paymentId && (get_param('ZombaioGWPass') === trim(Common::getOption('zombaiogwpass', self::getSystem()))) && $action) {
			//var_dump_pre($_REQUEST);
			die('OK');
		}

        if ($paymentBefore && (get_param('ZombaioGWPass') === trim(Common::getOption('zombaiogwpass', self::getSystem())))) {

            if($action == 'user.add') {
                self::updateSubscriptionId($paymentId, $subscriptionId);
            } elseif ($action == 'user.delete') {
                die('OK');
            } elseif ($action == 'rebill') {
                self::setAcceptPayment(true);
            } elseif ($action == 'user.addcredits') {

            } elseif ($action != '') {
                die('OK');
            }

            $plan = self::getOptionsPlan($paymentBefore['item']);
            if ($plan) {
                $custom = $paymentBefore['code'];
            }

        } else {
            global $g;
            redirect($g['to_root'] . Common::getHomePage());
        }

        $result = self::getAfterHtml($custom, self::getSystem());

        if(!$isClientReturnAction) {
            $result = 'OK';
        }

        echo $result;
    }

}