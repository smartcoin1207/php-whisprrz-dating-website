<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

putenv('PAGSEGURO_ENV=production');
putenv('PAGSEGURO_EMAIL=' . trim(Common::getOption('email', 'pagseguro')));
putenv('PAGSEGURO_TOKEN_PRODUCTION=' . trim(Common::getOption('token', 'pagseguro')));
putenv('PAGSEGURO_APP_ID_PRODUCTION=' . trim(Common::getOption('application_id', 'pagseguro')));
putenv('PAGSEGURO_APP_KEY_PRODUCTION=' . trim(Common::getOption('application_key', 'pagseguro')));

$autoloadersList = spl_autoload_functions();

foreach($autoloadersList as $autoloaderListItem) {
    spl_autoload_unregister($autoloaderListItem);
}

include_once __DIR__ . '/../../_pay/pagseguro/PagSeguroLibrary/PagSeguroLibrary.php';

foreach($autoloadersList as $autoloaderListItem) {
    spl_autoload_register($autoloaderListItem);
}

class PayPagseguro extends Pay
{
    public static $system = 'pagseguro';

    public static function before()
    {
        $order = self::createOrder();

        // Instantiate a new payment request
        $paymentRequest = new PagSeguroPaymentRequest();

        // Set the currency
        $paymentRequest->setCurrency(trim(Common::getOption('currency_code', self::getSystem())));

        // Set a reference code for this payment request. It is useful to identify this payment
        // in future notifications.
        $paymentRequest->setReference($order);

        // Set the url used by PagSeguro to redirect user after checkout process ends
        $paymentRequest->setRedirectUrl(self::callbackUrl(array('client_return_action' => 1, 'order' => $order)));

        // Another way to set checkout parameters
        $paymentRequest->addParameter('notificationURL', self::callbackUrl());
        $paymentRequest->addIndexedParameter('itemId', self::getPlanField('item'), 1);
        $paymentRequest->addIndexedParameter('itemDescription', trim(self::getPlanField('item_name')), 1);
        $paymentRequest->addIndexedParameter('itemQuantity', '1', 1);
        $paymentRequest->addIndexedParameter('itemAmount', self::getPlanField('amount'), 1);

        try {

            /*
             * #### Credentials #####
             * Replace the parameters below with your credentials
             * You can also get your credentials from a config file. See an example:
             * $credentials = new PagSeguroAccountCredentials("vendedor@lojamodelo.com.br",
             * "E231B2C9BCC8474DA2E260B6C8CF60D3");
             */

            // seller authentication
            $credentials = self::credentials();

            // application authentication
            //$credentials = PagSeguroConfig::getApplicationCredentials();

            //$credentials->setAuthorizationCode("E231B2C9BCC8474DA2E260B6C8CF60D3");

            // Register this payment request in PagSeguro to obtain the payment URL to redirect your customer.
            $url = $paymentRequest->register($credentials);

            redirect($url);

        } catch (PagSeguroServiceException $e) {
            die($e->getMessage());
        }

    }

    public static function after()
    {
        $custom = '';

        log_payment($custom);

        $code = (isset($_REQUEST['notificationCode']) && trim($_REQUEST['notificationCode']) !== "" ?
            trim($_REQUEST['notificationCode']) : null);
        $type = (isset($_REQUEST['notificationType']) && trim($_REQUEST['notificationType']) !== "" ?
            trim($_REQUEST['notificationType']) : null);

        if ($code && $type) {

            $notificationType = new PagSeguroNotificationType($type);
            $strType = $notificationType->getTypeFromValue();

            if($strType == 'TRANSACTION') {

                try {
                    $credentials = self::credentials();
                    $transaction = PagSeguroNotificationService::checkTransaction($credentials, $code);
                    $paymentId = $transaction->getReference();

                    $status = $transaction->getStatus();
                    $statusValue = $status->getValue();

                    $paymentBefore = self::getPaymentBeforeById($paymentId);

                    $plan = self::getOptionsPlan($paymentBefore['item']);
                    if ($plan && $statusValue == 3) {
                        $custom = $paymentBefore['code'];
                    }

                } catch (PagSeguroServiceException $e) {
                    die($e->getMessage());
                }

            }
        } else {
            self::setAcceptPayment(false);
            $paymentBefore = self::getPaymentBeforeById(get_param('order'));
            if($paymentBefore) {
                $custom = $paymentBefore['code'];
            }
        }

        $result = self::getAfterHtml($custom, self::getSystem());

        if(!get_param('client_return_action')) {
            $result = 'OK';
        }

        echo $result;

    }

    public static function credentials()
    {
        $credentials = new PagSeguroAccountCredentials(trim(Common::getOption('email', self::getSystem())), trim(Common::getOption('token', self::getSystem())));

        return $credentials;
    }

}