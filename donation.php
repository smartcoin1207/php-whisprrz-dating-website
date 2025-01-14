<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

checkByAuth();

$cmd = get_param('cmd');
$optionTmplSet = Common::getOption('set', 'template_options');
if (Common::isOptionActive('free_site')
    //|| ($optionTmplSet == 'urban' && !Common::isOptionActive('access_paying'))
    ) {
    redirect(Common::getHomePage());
}

if($optionTmplSet != 'urban' && isUpgraded()) {
    redirect('upgraded.php');
}
if(Common::getOption('upgraded_redirect_to_home_page', 'template_options') && isUpgraded()) {
    $param = array();
    if ($cmd == 'payment_thank') {
        $param = array('cmd' => 'payment_thank');
    }
    redirect(User::url(guid(), null, $param));
}

function isUpgraded() {

    global $g_user;

    $result = false;
    if ($g_user['gold_days'] > 0 and $g_user['type'] != '' and $g_user['type'] != 'none') {
        $option = get_param('option');
        $check = DB::result('SELECT `code` FROM `payment_type` WHERE `type` = ' . to_sql($g_user['type']) . ' and `code` = ' . to_sql($option), 0, 4);
        if ($check != 0 || empty($option)) {
            $cmd = get_param('cmd');
            if ($cmd != 'show' && $cmd != 'save') {
                $result = true;
            }
        }
    }

    return $result;
}

class CGold extends CHtmlBlock {

    function action() {
        $system = get_param('system');
        $item = get_param('item', 1);
        $cmd = get_param('cmd');
        if ($cmd == 'save') {
            $isAjaxRequest = get_param('ajax');
            $responseData = 'before_error';
            $urlPay = '_pay/' . $system . '/before.php';
            $requestUri = get_param('request_uri');
            if (!$requestUri) {
                $requestUri = Pay::getUrl();
            }
            $requestUri = base64_encode($requestUri);
            if (file_exists($urlPay)) {
                $urlRedirect = $urlPay . '?item=' . $item . '&request_uri=' . $requestUri;
                if ($isAjaxRequest) {
                    $responseData = array();
                    if (IS_DEMO) {
                        Pay::upgradeUser(guid(), $item);
                        $responseData['type'] = 'demo';
                        g_user_full();
                        $vars = array('data' => User::getWhatDateActiveSuperPowers());
                        $responseData['date'] = lSetVars('super_powers_active_till', $vars);
                    } else {
                        $responseData['type'] = 'url_system';
                        $responseData['url'] = $urlRedirect;
                    }
                } else {
                    redirect($urlRedirect);
                }
            }
            if ($isAjaxRequest) {
                die(getResponseDataAjaxByAuth($responseData));
            }
        }
    }

    function parseBlock(&$html) {
        global $g;
        global $g_user;
        global $pay;

        $html->setvar('request_uri', base64_decode(get_param('request_uri')));
        if ($html->varExists('url_home_page')) {
            $html->setvar('url_home_page', Common::getHomePage());
        }
        $paymentModules = $g['payment_modules'];

        $optionTmplSet = Common::getOption('set', 'template_options');
        $optionTmplTypePaymentPlan = Common::getOption('type_payment_plan', 'template_options');
        if (IS_DEMO && $optionTmplSet != 'urban') {
            if (DB::execute("UPDATE user SET gold_days=9999, type='platinum' WHERE user_id=" . $g_user['user_id'] . "")) {
                redirect("upgraded.php");
            }
        }
        $cmd = get_param('cmd');
        if ($cmd == 'payment_error') {
            $html->parse('payment_error');
        }

        $where = "`type` IN ('none','gold','silver','platinum')";
        $order = '';
        if ($optionTmplSet == 'urban') {
            $where = "`type` = 'payment' AND `set` = 'urban' ";
            $order = '`default` DESC,';
        }
        if (isTypePlanImpact()) {
            $order = '';
        }
        $sql = 'SELECT *
                  FROM `payment_plan`
                 WHERE ' . $where .
               ' ORDER BY ' . $order . ' `item` ASC';
        DB::query($sql);

        $items = array();
        $num = 0;
        while ($row = DB::fetch_row()) {
            $row['type'] = l($row['type']);
            if ($optionTmplSet == 'urban') {
                $vars = array('item' => l($row['item_name']),
                              'currency_sign' => l('currency_sign'),
                              'amount' => $row['amount'],
                              'amount_old' => $row['amount_old']);
                $lKey = 'payment_module_urban';
                if (isTypePlanImpact()) {
                    $lKey = 'payment_module_impact';
                    $row['item_total_price'] = lSetVars('payment_module_impact_price_total', $vars);
                    $row['item_new_price'] = lSetVars('payment_module_impact_price_new', $vars);
                    $row['item_old_price'] = $row['amount_old'] != '0.00' ? lSetVars('payment_module_impact_price_old', $vars) : '';
                    $row['item_num'] = $num++;
                }
                $row['item_name'] = lSetVars($lKey, $vars);
                $row['item_name_js'] = lSetVars($lKey, $vars, 'toJsL');
            } else {
                $row['item_name'] = lp($row['item_name']);
            }
            $items[] = $row;
        }

        $index = '';
        $defaultSystem = '';
        $systemChecked = 'checked';
        foreach ($pay as $system => $value) {
            if ($pay[$system]['active'] == 'Y') {
                $showThisPay=false;
                foreach($items as $ki=>$item){
                    $payOff=explode(',',$item['payment_modules_off']);
                    $payOff=array_flip(explode(',',$item['payment_modules_off']));
                    if(!isset($payOff[$system])){
                        $showThisPay=true;
                        break;
                    }
                }
                if($showThisPay){
                    $html->setvar('system_name', l($system));
                    if ($defaultSystem == '') {
                        $defaultSystem = $system;
                        $html->setvar('system_default', $system);
                    }
                    $html->setvar('system_checked', $systemChecked);
                    parse_payment_system($html, $system, $items, $index);
                    if ($index == '') {
                        $index = '2';
                    } else {
                        $index = '';
                    }
                    $systemChecked = '';
                }
            }
        }

        $html->setvar('system_current', get_param('system', $defaultSystem));

        $paymentChecked = 'checked';
        $blockItem = 'payment_item_old';
        foreach ($items as $key => $item) {
            $enabledPays=array();
            $payOff=array_flip(explode(',',$item['payment_modules_off']));
            reset($pay);
            foreach($pay as $p=>$v){
                if(!isset($payOff[$p])){
                    $value = $p;
                    if (!isTypePlanImpact()) {
                        $value = l($p);
                    }
                    $enabledPays[] = $value;
                }
            }
            if(count($enabledPays)>0){
                $html->setVar('plan_key',$item['item']);
                $html->setVar('pays_enabled',"'".implode("','",$enabledPays)."'");
                $html->parse('pay_enabled_item');
                if (isTypePlanImpact()) {
                    if ($item['default']) {
                        $html->parse("{$blockItem}_checked", false);
                        $html->parse("{$blockItem}_selected", false);
                    } else {
                        $html->clean("{$blockItem}_checked");
                        $html->clean("{$blockItem}_selected");
                    }
                } else {
                    $html->setvar('payment_checked', $paymentChecked);
                    $paymentChecked = '';
                }
                htmlSetVars($html, $item);
                $html->parse($blockItem);
            }
        }

        if ($optionTmplSet == 'urban') {
            $typePaymentFeatures = Common::getOption('type_payment_features', 'template_options');
            $typePaymentFeatures = '%' . $typePaymentFeatures . '%';
            $blockSuperPowers = 'super_powers';
            if (isUpgraded()) {
                $vars = array('data' => User::getWhatDateActiveSuperPowers());
                $html->setvar($blockSuperPowers . '_active_till', lSetVars('super_powers_active_till', $vars));
                $blockResponse = 'response_superpowers_activated';
                if (get_session($blockResponse)) {
                    $html->parse($blockResponse, false);
                    delses($blockResponse);
                }
                $html->parse($blockSuperPowers . '_activated', false);
            } else {
                $html->parse($blockSuperPowers . '_activate');
            }

            if($cmd == 'payment_thank') {
                $html->parse($blockSuperPowers . '_activated', false);
            }

            $features = DB::select('payment_features', '`type` LIKE ' . to_sql($typePaymentFeatures) . ' AND `status` = 1');
            foreach ($features as $key => $item) {
                if ($item['alias'] == '3d_city' && !Common::isModuleCityActive()) {
                    continue;
                }
                if ($item['alias'] == 'videochat' && !Common::isOptionActive('videochat')) {
                    continue;
                }
                if ($item['alias'] == 'audiochat' && !Common::isOptionActive('audiochat')) {
                    continue;
                }
                $html->parse("feature_{$item['alias']}", false);
            }
            $numFeatures = Common::isAvailableFeaturesSuperPowers();
            if (isTypePlanImpact()) {
                if ($numFeatures) {
                    $pageTitle = lSetVars('page_title_impact', array('num' => $numFeatures));
                } else {
                    $pageTitle = l('page_title_impact_no_features');
                }
                $html->setvar('page_title', $pageTitle );
            }
            if (!$numFeatures) {
                $html->parse('no_features', false);
            }
        }

        if ($optionTmplTypePaymentPlan == 'edge') {
            TemplateEdge::parseColumn($html);
        }

        parent::parseBlock($html);
    }

}

function isTypePlanImpact() {
    $optionTmplTypePaymentPlan = Common::getOption('type_payment_plan', 'template_options');
    return $optionTmplTypePaymentPlan == 'impact' || $optionTmplTypePaymentPlan == 'edge';
}

function parse_payment_system(&$html, $system, $items, $index = "") {
    $html->setvar("system", $system);
    $html->setvar("row", $index);

    $hideForPlanArr=array();
    foreach($items as $k=>$item){
        $payOff=explode(',',$item['payment_modules_off']);
        foreach($payOff as $k1=>$v1){
            if(!isset($hideForPlanArr[$v1])){
                $hideForPlanArr[$v1]=array();
            }
            $hideForPlanArr[$v1][]='hide_for_'.$item['item'];
        }
    }


    $count = count($items);
    $i = 1;
    foreach ($items as $row) {
        $payOff=explode(',',$row['payment_modules_off']);
        foreach($payOff as $k1=>$v1){
            if($v1==$system){
                continue 2;
            }
        }
        foreach ($row as $k => $v) {
            $html->setvar($k, $v);
        }
       // if ($i < $count)
            $html->parse("separator", false);
       // else
       //     $html->setblockvar("separator", "");

        if(isset($hideForPlanArr[$system])){
            $html->setvar('hide_class', implode(" ",$hideForPlanArr[$system]));
        } else {
            $html->setvar('hide_class', "");
        }

        $html->parse("payment_item", true);
        $i++;
    }

    $block = 'payment_system';
    if ($html->blockExists("{$block}_block")) {
        $html->parse("{$block}_block", true);
    }

    $html->parse($block, true);
    $html->setblockvar("payment_item", "");
}

$page = new CGold("", getPageCustomTemplate('upgrade.html', 'upgrade_template'));
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);


if (Common::isParseModule('profile_colum_narrow')){
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
}


$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");