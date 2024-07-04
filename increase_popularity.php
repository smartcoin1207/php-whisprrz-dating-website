<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

$optionTmplTypePaymentPlan = Common::getOption('type_payment_plan', 'template_options');
$isPopularityImpact = $optionTmplTypePaymentPlan == 'impact';

checkByAuth();

if (!Common::isCreditsEnabled() && !get_param('ajax')) {
    redirect(Common::getHomePage());
}

class CIncreasePopularity extends UserFields  {

    private $response = '';
    private $balans = 0;
    private $spotlightLastId = 0;

    function action() {
        global $g;
        global $g_user;

        $isAjaxRequest = get_param('ajax');
        $optionTmplTypePaymentPlan = Common::getOption('type_payment_plan', 'template_options');
        $isPopularityImpact = $optionTmplTypePaymentPlan == 'impact' || $optionTmplTypePaymentPlan == 'edge';

        // Redirect pay system
        $param = explode('-', base64_decode(get_param('custom')));
        //$response = get_param('response');
        if (count($param) && isset($param[5])) {
            $this->response = $param[5];// before.php -> payment_error
        }
        if ($g_user['user_id'] && $isAjaxRequest && $this->response != 'payment_error') {
            $responseData = false;
            $action = get_param('action');
            if ($action) {
                $type = get_param('type', 0);
                $item = get_param('item');
                $userCredits = $g_user['credits'];
                if (($action == 'refill' || $action == 'payment') && $item) {
                    if (IS_DEMO) {
                        $planCredits = Pay::getOptionsPlan($item);
                        $userCredits = $userCredits + $planCredits['gold_days'];
                        $data = array('credits' => $userCredits);
                        User::update($data);
                        if ($action == 'refill' || (in_array($type, array('gift', 'video_chat', 'audio_chat')))) {
                            if ($isPopularityImpact) {
                                $responseData = 'payment_success';
                                $this->balans = $userCredits;
                            } else {
                                $responseData = array();
                                $responseData['type'] = 'demo';
                                $responseData['balans'] = $userCredits;
                                die(getResponseDataAjaxByAuth($responseData));
                            }
                        } else {
                            $action = 'payment_service';
                        }
                    } else {
                        $urlPaymentSystem = Pay::getUrlPaymentSystem($item, $type);
                        if ($urlPaymentSystem) {
                            $responseData = array();
                            $responseData['type'] = 'url_system';
                            $responseData['url'] = $urlPaymentSystem;
                        }
                        die(getResponseDataAjaxByAuth($responseData));
                    }
                }
                if ($action == 'payment_service' && $type && $type != 'gift'){
                    $this->balans = $userCredits;
                    $servicePrice = Pay::getServicePrice($type);
                    $payment = $userCredits - $servicePrice['credits'];
                    if ($payment >= 0) {
                        $popularity = User::getMaxPopularityInCity($g_user['city_id'], true);
                        if (!$popularity) {
                            $popularity = 1;
                        }
                        $data = array('popularity' => $popularity,
                                      'credits' =>  $payment);
                        if ($servicePrice['alias'] == 'spotlight') {
                            $this->spotlightLastId = Spotlight::addItem();
                        } else {
                            $data['date_' . $servicePrice['alias']] = date('Y-m-d H:i:s');
                        }
                        User::update($data);
                        $this->balans = $payment;
                        $responseData = 'payment_success';
                    } else {
                        $responseData = 'service_not_paid';
                    }
                }
                $this->response = $responseData;
            }
        }
    }

    function getResponseDataDemo() {

    }

    function parsePaymentPlanAndSystem(&$html) {
        global $pay;

        $optionTmplTypePaymentPlan = Common::getOption('type_payment_plan', 'template_options');
        $isPopularityImpact = $optionTmplTypePaymentPlan == 'impact' || $optionTmplTypePaymentPlan == 'edge';
        $currentPlanId = get_param('current_plan',0);
        $currentSystemId = get_param('current_system');
        $prf = '';
        $lKeyPaymentModule = 'payment_module_urban';
        if ($isPopularityImpact) {
            $prf = '_boost';
            $lKeyPaymentModule = Common::getOption('type_payment_module_credits', 'template_options');
        }
        // TO class Pay
        $sql = "SELECT *
                  FROM `payment_plan`
                 WHERE `type` = 'credits' " . Pay::whereApp() . "
                 ORDER BY `default` DESC, `item` ASC";
        DB::query($sql);

        $blockPayment = 'payment_item';
        $currentPayOff=array();
        while ($row = DB::fetch_row()) {
            $enabledPays = array();
            $payOff=array_flip(explode(',',$row['payment_modules_off'] . Pay::getIapPaymentSystemsOffString()));
            reset($pay);
            foreach($pay as $p=>$v){
                if (!isset($payOff[$p])){
                    $enabledPays[] = $p . $prf;
                }
            }
            if (count($enabledPays)>0){
                if ($html->blockExists('pay_enabled_item')) {
                    $html->setvar('plan_key', $row['item']);
                    $html->setvar('pays_enabled', "'".implode("','",$enabledPays)."'");
                    $html->parse('pay_enabled_item');
                }
                if($currentPlanId==0){
                    $currentPlanId=$row['item'];
                }
                $selected='';

                if($currentPlanId==$row['item']){
                    $selected = 'selected';
                    if ($isPopularityImpact) {
                        $selected = 'checked';
                    }
                    $html->parse($blockPayment . '_selected', false);
                } else {
                    $html->clean($blockPayment . '_selected');
                }
                $html->setvar($blockPayment, $row['item']);
                $name = $row['item_name'];
                $lItemName = lCascade($name, array($name . '_' . $optionTmplTypePaymentPlan, $name));
                if ($html->varExists($blockPayment . '_system_name')) {
                    $html->setvar($blockPayment . '_system_name', $lItemName);
                    $html->setvar($blockPayment . '_system_name_js', toJs($lItemName));
                }
                if ($html->varExists($blockPayment . '_id')) {
                    $html->setvar($blockPayment . '_id', $row['item']);
                }
                $vars = array('item' => $lItemName,
                              'currency_sign' => l('currency_sign'),
                              'amount' => $row['amount']);
                if ($isPopularityImpact) {
                    $lKeyPaymentModule = Common::getOption('type_payment_module_credits', 'template_options');
                    if ($row['amount_old'] != '0.00') {
                        $lKeyPaymentModule .= '_old';
                        $vars['amount_old'] = $row['amount_old'];
                    }
                }
                $html->setvar($blockPayment . '_name', lSetVars($lKeyPaymentModule, $vars));
                if ($html->varExists($blockPayment . '_total_price')) {
                    $html->setvar($blockPayment . '_total_price', lSetVars('payment_module_impact_price_total', $vars));
                }
                $html->setvar($blockPayment . '_selected', $selected);
                $html->parse($blockPayment, true);
            }

            if($currentPlanId==$row['item']){
                $currentPayOff=$payOff;
            }

        }

        if(Common::isAppAndroid() && Common::isOptionActive('active', 'iapgoogle')) {
            return;
        }

        $blockSystem = 'payment_system';
        reset($pay);
        foreach ($pay as $system => $value) {
            if ($pay[$system]['active'] == 'Y' && (!isset($currentPayOff[$system]) || $isPopularityImpact)) {
                $selected='';
                if( $currentSystemId==$system){
                    $selected='selected';
                }
                $html->setvar($blockSystem, $system);
                $html->setvar($blockSystem . '_name', l($system));
                $html->setvar($blockSystem . '_selected', $selected);
                $html->parse($blockSystem, true);
                if ($html->blockExists("{$blockSystem}_block")) {
                    $html->parse("{$blockSystem}_block", true);
                }
            }
        }
    }

    function parseBlock(&$html) {
        global $g;
        global $g_user;

        $cmd = get_param('cmd');
        $type = get_param('type');
		$typeParam = $type;
		if ($type == 'live_stream_past') {
			$type = 'live_stream';
		}
        $gifts_credits = 0;
        if ($g_user['user_id']) {
            $alias = '';
            $action = get_param('action', 'payment');
            if ($type != 'gift') {
                $servicePrice = Pay::getServicePrice($type);
                if (!empty($servicePrice)) {
                    $credits = $servicePrice['credits'];
                    $alias = $servicePrice['alias'];
                }
            } else {
                $id = get_param('id');
                $gifts_credits = intval(get_param('credits',0));
                $credits = DB::result('SELECT `credits` FROM `gifts` WHERE `id` = ' . to_sql($id, 'Number'));
                $alias = 'gift';
            }
            if ($cmd == 'payment_thank' || $this->response == 'payment_error') {
                $html->parse('inc_pop_show', false);
            }
            $html->setvar('action', $action);
            $html->setvar('type_service', $typeParam == 'live_stream_past' ? $typeParam : $type);

            if ($html->varExists('payment_plan_description')) {
                $html->setvar('payment_plan_description', l('payment_plan_description_' . $type));
            }
            if (($cmd == 'pp_payment' || $cmd == 'payment_thank') && $type) {
                $html->setvar('type', $type);
                $needCredits = $credits + intval($gifts_credits);
                if ($g_user['credits'] < $needCredits) {
                    if ($cmd == 'payment_thank') {
                        $desc = l('still_not_enough_credits_for_the_service');
                    } else {
                        $desc = l('refill_now_the_more_you_buy');
                    }
                    $html->setvar('costs', $credits);
                    $html->setvar('pp_payment_desc', $desc);
                    $html->setvar('pp_payment_title', l('pp_payment_' . $alias));
                    $this->parsePaymentPlanAndSystem($html);
                    $html->parse('payment');
                } else {
                    $html->setvar('costs', $credits);
                    $html->setvar('service_costs', lSetVars('this_service_costs_credits', array('credit' => $credits)));
                    $html->setvar('balans', $g_user['credits']);
                    $lCredits = lSetVarsCascade('you_have_credits', array('credit' => $g_user['credits']));
                    $html->setvar('you_have_credits', $lCredits);
                    $html->parse('have_credits');
                }
            } elseif ($cmd == 'pp_refill') {
                $html->setvar('pp_payment_desc', l('refill_now_the_more_you_buy'));
                $html->setvar('type', 0);
                $html->setvar('pp_payment_title', l('refill_now'));
                $this->parsePaymentPlanAndSystem($html);
                $html->parse('payment');
            } elseif ($this->response == 'payment_error') {
                $html->parse('payment_error');
            } elseif ($this->response == 'payment_success' || $cmd == 'payment_thank') {
                $block = 'payment_success';
                if ($cmd == 'payment_thank') {
                    $balans = $g_user['credits'];
                    $msg = l('your_credits_have_been_refilled');
                } else {
                    $balans = $this->balans;
                    $msg = l('success_' . $alias);
                    if ($alias == 'spotlight') {
                        $blockSpotlight = 'spotlight_item';
                        $html->setvar($block . '_type', 'spotlight');
                        $html->setvar($blockSpotlight . '_photo_id', User::getPhotoDefault($g_user['user_id'], 'r', true));
                        $html->setvar($blockSpotlight . '_photo', User::getPhotoDefault($g_user['user_id'], 'r'));
                        $html->setvar($blockSpotlight . '_name', $g_user['name']);
                        $birth = explode('-', $g_user['birth']);
                        $html->setvar($blockSpotlight . '_age', User::getAge($birth[0], $birth[1], $birth[2]));
                        $html->setvar($blockSpotlight . '_user_id', $g_user['user_id']);
                        $html->setvar($blockSpotlight . '_user_profile_link', User::url($g_user['user_id'],$g_user));
                        $html->setvar($blockSpotlight . '_id', $this->spotlightLastId);
                        $html->parse($blockSpotlight, false);
                    }
                    $level = User::getLevelOfPopularity($g_user['user_id'], true);
                    $html->setvar($block . '_level_decor', $level);
                    $html->setvar($block . '_level', l($level));
                    $html->setvar($block . '_activity_services', User::isActivityAllServices(true));
                    $html->parse($block . '_activity_services');
                }

                $html->setvar($block . '_boosts_left', lSetVars('credits_left', array('credit' => $this->balans)));
                $html->setvar($block . '_credit_balance', lSetVars('credit_balance', array('credit' => $this->balans)));
                $html->setvar($block . '_balans', $this->balans);
                $html->setvar($block . '_msg', $msg);
                if ($html->varExists($block . '_title')) {
                    $title = l('success_payment_' . $type);
                    if ($type == 'refill') {
                        $title = l('the_credits_have_been_added');
                    }
                    $html->setvar($block . '_title',  $title);
                }
                $blockBtn = $block . '_' . $type . '_btn';
                if ($html->blockExists($blockBtn)) {
                    $html->parse($blockBtn, false);
                }
                $html->parse($block);
            }

            $html->setvar('main_photo', User::getPhotoDefault($g_user['user_id'], 'r'));

            $services = array('spotlight', 'search', 'encounters');
            foreach ($services as $item) {
                $html->cond(User::isActiveService($item), $item. '_active', $item . '_inactive');
            }

            if(Common::isOptionActive('spotlight_enabled_urban')){
                $html->parse('spotlight_enabled',false);
            }
            $html->setvar('balans', $g_user['credits']);
        }
        parent::parseBlock($html);
    }
}

$isAjaxRequest = get_param('ajax');
if ($isAjaxRequest) {
    $listTmpl = array('main' => $g['tmpl']['dir_tmpl_main'] . '_pp_increase_popularity.html',
                      'spotlight' => $g['tmpl']['dir_tmpl_main'] . '_spotlight_items.html');
    if ($isPopularityImpact || $optionTmplTypePaymentPlan == 'edge') {
        $listTmpl = $g['tmpl']['dir_tmpl_main'] . '_pp_increase_popularity.html';
    }

} else {
    $listTmpl = array('main' => $g['tmpl']['dir_tmpl_main'] . 'increase_popularity.html',
                      'pp_increase_popularity' => $g['tmpl']['dir_tmpl_main'] . '_pp_increase_popularity.html');
}

$page = new CIncreasePopularity('', $listTmpl);

if ($isAjaxRequest) {
    die(getResponsePageAjaxAuth($page));
}

$header = new CHeader('header', $g['tmpl']['dir_tmpl_main'] . '_header.html');
$page->add($header);

$column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
$page->add($column_narrow);

$footer = new CFooter('footer', $g['tmpl']['dir_tmpl_main'] . '_footer.html');
$page->add($footer);

include('./_include/core/main_close.php');