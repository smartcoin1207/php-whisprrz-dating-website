<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include_once __DIR__ . '/../../_pay/stripe/stripe-php-7.57.0/init.php';

class PayStripe extends Pay
{
    public static $system = 'stripe';

    public static function before()
    {

        $urlMain = Common::urlSiteSubfolders();
        $returnCancel = $urlMain;
        $requestUri = Pay::getRequestUri();
        if ($requestUri != '') {
            $returnCancel = $requestUri;
        }

        $order = self::createOrder();

        $secretKey = trim(Common::getOption('secret_key', self::getSystem()));
        $publicKey = trim(Common::getOption('public_key', self::getSystem()));

        \Stripe\Stripe::setApiKey($secretKey);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
				'price_data' => [
                    'currency' => trim(Common::getOption('currency_code', self::getSystem())),
                    'unit_amount' => floatval(self::getPlanField('amount')) * 100,
                    'product_data' => [
                        'name' => self::getPlanField('item_name'),
                    ],
				],
                'quantity' => 1,
            ]],
            'success_url' => self::callbackUrl(array('client_return_action' => 1)),
            'cancel_url' => $returnCancel,
            'payment_intent_data' => ['metadata' => ['order_id' => $order]],
			'mode' => 'payment',
        ]);

        if($session) {
            $html = <<<HTML
                <script src="https://js.stripe.com/v3/"></script>
                <script>
                    var stripe = Stripe('$publicKey');
                    stripe.redirectToCheckout({
                        sessionId: '{$session->id}'
                    }).then(function (result) {
                        // If `redirectToCheckout` fails due to a browser or network
                        // error, display the localized error message to your customer
                        // using `result.error.message`.
                        if(result.error.message) {
                            alert(result.error.message);
                            location.href = '$returnCancel';
                        }
                    });
                </script>
HTML;
        } else {
            $html = "<script>location.href = '$returnCancel';</script>";
        }

        echo $html;
    }

    public static function after()
    {
        $custom = '';

        $secretKey = trim(Common::getOption('secret_key', self::getSystem()));
        $webhookKey = trim(Common::getOption('webhook_key', self::getSystem()));

        \Stripe\Stripe::setApiKey($secretKey);

        $payload = @file_get_contents('php://input');
        $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
        $event = null;

        log_payment(array($payload));

        $isCheckSignActive = (!get_param('client_return_action') && $webhookKey);

        if($payload) {

            try {

                if($isCheckSignActive) {
                    $event = \Stripe\Webhook::constructEvent(
                        $payload, $sig_header, $webhookKey
                    );
                } else {
                    if($payload) {
                        $event = \Stripe\Event::constructFrom(
                            json_decode($payload, true)
                        );
                    }
                }

            } catch(\UnexpectedValueException $e) {
                // Invalid payload
                http_response_code(400);
                exit();
            } catch(\Stripe\Error\SignatureVerification $e) {
                // Invalid signature
                http_response_code(400);
                exit();
            }

        }

        if($event) {
            switch ($event->type) {
                case 'charge.succeeded':
                    $paymentId = $event->data->object->metadata->order_id;
                    $paymentBefore = self::getPaymentBeforeById($paymentId);

                    if ($paymentBefore) {
                        $plan = self::getOptionsPlan($paymentBefore['item']);
                        if ($plan) {
                            $custom = $paymentBefore['code'];
                        }
                    }

                    break;
                default:
                    // Unexpected event type
                    http_response_code(400);
                    exit();
            }
        }

        $result = self::getAfterHtml($custom, self::getSystem());

        if(!get_param('client_return_action')) {
            $result = 'OK';
        }

        echo $result;
    }

}