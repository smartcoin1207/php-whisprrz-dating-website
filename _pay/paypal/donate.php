<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = "../../";
include($g['to_root'] . "_include/core/main_start.php");

$system = "paypal";

$item = get_param('item');
$amount = get_param('amount');

if($item < 0 || $item > 1) {
    exit;
}

$urlSite = Common::urlSiteSubfolders();

$returnCancel = $urlSite . 'donation_cobra.php';
$requestUri = Pay::getRequestUri();
if ($requestUri != '') {
    $returnCancel = $requestUri;
}

$code = Pay::getCode($item, $amount);

$params = array(
    // cobra-5555 Error Code
    'cmd' => '_xclick',
    //'cmd' => '_xclick-subscriptions',
    'cmd' => '_xclick',
    'business' => $pay[$system]['business'],
    'currency_code' => $pay[$system]['currency_code'],    
	'amount' => $amount,	
	'item_name' => '1 month',
	'item_number' => '1',
    'no_note' => '1',
    'no_shipping' => '1',
    // 'rm' => '2',
    'return' => $urlSite . 'home.php',
    'cancel_return' => $returnCancel,
    'custom' => $code,
    'charset' => 'utf-8',
    // 'src' => '1',
);

if($item == '1') {
    $params['cmd'] = '_xclick-subscriptions';
    $params['charset'] = 'utf-8';
    $params['a3'] = $amount;
    $params['p3'] = 3;
    $params['t3'] = 3;
}

$demoUrlPrefix = '';

if($pay[$system]['demo'] == 'Y') {
    $demoUrlPrefix = 'sandbox.';
}

$url = "https://www.{$demoUrlPrefix}paypal.com/cgi-bin/webscr";

$paymentUrl = $url . '?' . http_build_query($params);

redirect($paymentUrl);