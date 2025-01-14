<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Pay
{
    private static $system;
    private static $plans;
    public static $plan;
    public static $acceptPayment = null;
    public static $setSessionUpgradeNotification = true;
    private static $subscriptionExpireTimestamp = false;

    static function getUrl()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$request = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$request = $_SERVER['REQUEST_URI'];
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
			$request = $_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$request .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			$request = '';
		}
		return Common::urlProtocol() . '://' . $_SERVER['HTTP_HOST'] . $request;
	}

    static function logPayment($p)
    {
        global $g;
        $data = "Varibles:\n{\n";
        $data .= var_export($p, true);
        $data .= "}\n\n";
        if (isset($_SERVER['HTTP_REFERER'])) {
            $data .= "From:\n{\n";$data .= "\t" . 'HTTP_REFERER' . " => " . $_SERVER['HTTP_REFERER'] . "\n";
            $data .= "}\n\n";
        }
        $data .= "GET:\n{\n";
        $data .= var_export($_GET, true);
        $data .= "}\n\n";
        $data .= "POST:\n{\n";
        $data .= var_export($_POST, true);
        $data .= "}\n\n";

        DB::execute('INSERT INTO `payment_after`
                        SET	`dt` = ' . to_sql(date('Y-m-d H:i:s')) . ',
                            `data` = ' . to_sql($data));
    }

    static function getCode($item, $amount)
    {
        $code = md5(md5(microtime()) . md5(rand(0, 100000)));
        $code = base64_encode($code . "-" . get_session('user_id') . '-' . $item . '-' . $amount);
        return $code;
    }

    static function insertBefore($data)
    {
        $data['dt'] = date('Y-m-d H:i:s');
        $data['user_id'] = get_session('user_id');
        if(!isset($data['type'])) {
            $data['type'] = get_param('type');
        }
        DB::insert('payment_before', $data);
        return DB::insert_id();
    }

    static function updateBefore($data)
    {
        // into a separate method -> where
        $where = '`user_id` = ' . to_sql($data['user_id'], 'Number') .
                 ' AND `item` = ' . to_sql($data['item'], 'Number') .
                 ' AND `system` = ' . to_sql($data['system']) .
                 ' AND `code` = ' . to_sql($data['code']);

        DB::update('payment_before', array('system' => $data['system'] . ' payed'), $where);
    }

    static function updateBeforeById($id, $data)
    {
        DB::update('payment_before', $data, '`id` = ' . to_sql($id));
    }

    static function checkPlan()
    {
        $item = get_param('item');
        $item = DB::result('SELECT `item` FROM `payment_plan` WHERE `item` = ' . to_sql($item));
        return (get_session('user_id') == '' || $item == 0) ? false : $item;
    }

    static function getOptionsPlan($item)
    {
        DB::query('SELECT * FROM `payment_plan` WHERE `item` = ' . to_sql($item, 'Number'));
        return DB::fetch_row();
    }

    static function getOptionsPayment($custom, $system)
    {
        $p = explode('-', base64_decode($custom));
        self::logPayment($p);

        $responseData = array();
        if (count($p) > 1) {
            $code = to_sql($custom, 'Plain');
            $user_id = $p[1];
            $item = $p[2];

            // `system` removed for subscription support
            DB::query('SELECT *
                         FROM `payment_before`
                        WHERE `user_id` = ' . to_sql($user_id, 'Number') .
                        ' AND `item` = ' . to_sql($item, "Number") .
                        ' AND `code` = ' . to_sql($code));

            $payment = DB::fetch_row();

            $acceptPayment = true;

            // prevent double payment if not subscription(credits etc)
            if($payment['system'] === $system . ' payed') {
                $acceptPayment = false;
            }

            $acceptPaymentValue = self::getAcceptPayment();
            if($acceptPaymentValue !== null) {
                $acceptPayment = $acceptPaymentValue;
            }

            if($system == 'paypal') {

                if($acceptPayment === false && get_param('txn_type') === 'subscr_payment') {
                    $acceptPayment = true;
                }

                $paymentStatus = get_param('payment_status');
                $st = get_param('st');

                $completed = 'Completed';

                if($paymentStatus !== $completed && $st !== $completed) {
                    $acceptPayment = false;
                }

            }

            if($system == 'iapgoogle' || $system == 'iapapple') {
                if($acceptPayment === false && self::getSubscriptionExpireTimestamp()) {
                    $acceptPayment = true;
                }
            }

            if ($acceptPayment && DB::num_rows()) {
                self::upgradeUser($user_id, $item);
            }

            $data = array('user_id' => $user_id,
                          'item' => $item,
                          'system' => $system,
                          'code' => $code);

            if($acceptPayment) {
                self::updateBefore($data);
            }
            $responseData['type'] = $payment['type'];
            $responseData['request_uri'] = $payment['request_uri'];
            $responseData['item'] = $item;

            self::setAcceptPayment($acceptPayment);
        }
        return $responseData;
    }

    static function getAfterHtml($custom, $system)
    {
        $pay = self::getOptionsPayment($custom, $system);
        $data = self::getFormData($pay);

        if(get_param('client_return_action') && $data['cmd'] === 'payment_error') {
            $data['cmd'] = '';
        }

        return '<html><body>
                <form name="check_out_form" action="' . $data['action'] . '" method="post">
                <input type="hidden" name="cmd" value="' . $data['cmd'] . '">
                ' . $data['type'] . '
                <script language="JavaScript">document.forms["check_out_form"].submit();</script>
                </form>
                </body></html>';
    }

    static function getFormData($pay)
    {
        $type = '';
        $optionTmplSet = Common::getOption('set', 'template_options');
        $url = Common::urlSiteSubfolders();
        if (empty($pay)) {
            $action = $url . 'upgrade.php';
            if ($optionTmplSet == 'urban') {
                $action = $url . 'profile_view.php';
            }
            $cmd = 'payment_error_off';
        } else {
            $cmd = 'payment_thank';
            $action = $url . 'upgraded.php';
            if ($optionTmplSet == 'urban') {
                $action = $pay['request_uri'];
                $typePlan = getTypePaymentPlan($pay['item']);
                if ($typePlan == 'credits') {
                    if (isset($pay['type']) && $pay['type']) {
                        $type = '<input type="hidden" name="type" value="' . $pay['type'] . '">';
                    }
                }
            }
        }
        return array('action' => $action, 'type' => $type, 'cmd' => $cmd);
    }

    static function getUrlPaymentSystem($item, $type = '') {
        $system = get_param('system');
        $url = '';
        $urlPay = '_pay/' . $system . '/before.php';
        $requestUri = get_param('request_uri');
        if (file_exists($urlPay)) {
            $url = $urlPay . '?item=' . $item . '&type=' . $type . '&request_uri=' . $requestUri;
        }
        return trim($url);
    }

    static function getRequestUri($requestUri = '')
    {
        $optionTmplSet = Common::getOption('set', 'template_options');
        if ($optionTmplSet == 'urban') {
            $requestUri = base64_decode(get_param('request_uri'));
            $requestUri = self::prepareRequestUri($requestUri);
        }
        return $requestUri;
    }

    static function getTypePaymentPlan($item)
    {
        $sql = 'SELECT `type` FROM `payment_plan` WHERE `item` = ' . to_sql($item);
        return DB::result($sql);
    }

    static function getServicePrice($type, $field = null) {

        static $cache = array();

        if (!Common::isCreditsEnabled()) {
            return 0;
        }

        if(!isset($cache[$type])) {
            $cache[$type] = DB::select('payment_price', '`alias` = ' . to_sql($type));
        }

        $price = $cache[$type];

        if (!empty($price) && isset($price[0])) {
            $price = $price[0];
        }
        if ($field !== null) {
            $price = $price[$field];
        }

        return $price;
    }

    static function upgradeUser($user_id, $item, $setResponseSes = true)
    {
		global $g;

		DB::query('SELECT * FROM `payment_plan` WHERE `item` = ' . to_sql($item ));
		$row = DB::fetch_row();

        if(self::getSystem() == 'fortumo') {
            if(isset($_REQUEST['credit_name'])) {
                $row['gold_days'] = get_param('amount');
            }
        }

        if ($row['type'] == 'credits') {
            DB::execute('UPDATE `user`
                            SET credits = credits + ' . to_sql($row['gold_days'], 'Number') . '
                          WHERE `user_id` = ' . to_sql($user_id, 'Number'));
            if ($setResponseSes && self::$setSessionUpgradeNotification) {
                set_session('response_refill_credits', true);
            }
        } else {

            CStatsTools::count('gold_memberships', $user_id);

            $timeStamp = time();
            $date = date('Y-m-d', $timeStamp + 3600);
            $paidDays = $row['gold_days'];

            $subscriptionExpireTimestamp = self::getSubscriptionExpireTimestamp();
            if($subscriptionExpireTimestamp) {
                $paidDays = self::timestampDiffToDays($subscriptionExpireTimestamp);
                $timeStamp = $subscriptionExpireTimestamp;
            }

            $timeStamp += 3600; //+60 minutes
            $hour = intval(date('H', $timeStamp));

            if($paidDays) {
                DB::execute('UPDATE `user`
                                SET `gold_days` = ' . to_sql($paidDays, 'Number') . ',
                                    `type` = ' . to_sql($row['type']) . ',
                                    `sp_sending_messages_per_day` = 0,
                                    `payment_day`='.to_sql($date).',
                                    `payment_hour`='.to_sql($hour).'
                              WHERE `user_id` = ' . to_sql($user_id, 'Number'));

                User::upgradeCouple($user_id, $row['gold_days'], $row['type']);
                if ($setResponseSes && self::$setSessionUpgradeNotification) {
                    set_session('response_superpowers_activated', true);
                }
            }
        }

        $price = $row['amount'];
        $partner = DB::result('SELECT `partner`
                                 FROM `user`
                                WHERE `user_id` = ' . to_sql($user_id, 'Number'));

        $plus = ($price / 100) * $g['options']['partner_percent'];
        DB::execute('UPDATE `partner`
                        SET account = (account+' . to_sql($plus, 'Number') . '),
                            summary = (summary+' . to_sql($plus, 'Number') . '),
                            count_golds = (count_golds+1)
                      WHERE `partner_id` = ' . to_sql($partner, 'Number'));

        $p_partner = DB::result('SELECT `p_partner` FROM `partner` WHERE `partner_id` = ' . to_sql($partner, 'Number'));
        $plus = ($price / 100) * $g['options']['partner_percent_ref'];
        DB::execute('UPDATE partner
                        SET account = (account+' . to_sql($plus, 'Number') . '),
                            summary = (summary+' . to_sql($plus, 'Number') . ")
                      WHERE `partner_id` = " . to_sql($p_partner, 'Number'));
        return $row['type'];
    }

    static function parsePaymentPlan(&$html, $type = 'payment', $price = null)
    {
        global $g_user;
        global $pay;

        $optionTmplTypePaymentPlan = Common::getOption('type_payment_plan', 'template_options');
        $kTypeModule = 'type_payment_module';
        if ($type == 'credits') {
            $kTypeModule = 'type_payment_module_credits';
        }
        $typePaymentModule = Common::getOption($kTypeModule, 'template_options');
        $where = '';
        $diff = $g_user['credits'] - $price;
        if ($price !== null && $diff < 0) {
           $where = ' AND `gold_days` >= ' . to_sql($price - $g_user['credits']) . ' ';
        }

        $whereApp = Pay::whereApp();

        $sql = "SELECT *
                  FROM `payment_plan`
                 WHERE `type` = " . to_sql($type) . " `set` = " .to_sql('urban', 'Text').  " " . $where . $whereApp . "
                 ORDER BY `item` ASC";
        DB::query($sql);

        if (!DB::num_rows()){
            $sql = "SELECT *
                      FROM `payment_plan`
                     WHERE `type` = " . to_sql($type) . " `set` = " .to_sql('urban', 'Text').  " "  . $whereApp . "
                     ORDER BY `item` ASC";
            DB::query($sql);
        }

        $items = array();
    $isDefault = 0;
        $lKey = 'payment_module_urban';
        if ($typePaymentModule) {
            $lKey = $typePaymentModule;
        }

        $isAppAndroid = Common::isAppAndroid();

        while ($row = DB::fetch_row()) {
            $enabledPays=array();
            $payOff=array_flip(explode(',',$row['payment_modules_off'] . Pay::getIapPaymentSystemsOffString()));
            reset($pay);
            foreach($pay as $p=>$v){
                if(!isset($payOff[$p])){
                    $enabledPays[]=$p;
                }
            }
            if(count($enabledPays)>0){
                if ($row['default']) {
                    $isDefault = 1;
                }
                $row['type'] = l($row['type']);
                $name = $row['item_name'];
                $lItemName = lCascade($name, array($name . '_' . $optionTmplTypePaymentPlan, $name));
                $vars = array('item' => $lItemName,
                              'currency_sign' => l('currency_sign'),
                              'amount' => $row['amount'],
                              'amount_old' => $row['amount_old']);

                $row['item_name'] = lSetVars($lKey, $vars);
                $items[] = $row;
            }
        }
        $i = 0;
        foreach ($items as $key => $item) {
            $html->setvar('payment_selected', $item['default'] || (!$i && !$isDefault) ? 'selected' : '');
            htmlSetVars($html, $item);
            $html->parse('payment_item_old');

            if($isAppAndroid) {
                $html->setvar('in_app_purchase_product_id', trim($item[PayIapGoogle::getSystem() . '_product_id']));
                $html->setvar('in_app_purchase_product_type', $item['type'] === 'credits' ? 'consumable' : 'paid subscription');
                $html->parse('in_app_purchase_product');
            }

            $i++;
        }
        self::$plans = $items;

        if($isAppAndroid && $i && Common::isOptionActive('active', 'iapgoogle')) {
            $html->parse('in_app_purchase_products');
        }

        return self::$plans;

    }

    static function parsePaymentSystem(&$html, $planItems=array())
    {
        global $pay;

        if(Common::isApp() && ((Common::isAppAndroid() && Common::isOptionActive('active', 'iapgoogle')) || (Common::isAppIos() && Common::isOptionActive('active', 'iapapple')))) {
            return;
        }

        $hideForPlanArr=array();
        foreach($planItems as $k=>$item){
            $payOff=explode(',',$item['payment_modules_off']);
            foreach($payOff as $k1=>$v1){
                if(!isset($hideForPlanArr[$v1])){
                    $hideForPlanArr[$v1]=array();
                }
                $hideForPlanArr[$v1][]='hide_for_'.$item['item'];
            }
        }
        foreach ($pay as $system => $value) {
            if ($pay[$system]['active'] == 'Y') {
                $html->setvar('system', $system);
                $html->setvar('system_name', l($system));
                if(isset($hideForPlanArr[$system])){
                    $html->setvar('hide_class', implode(" ",$hideForPlanArr[$system]));
                } else {
                    $html->setvar('hide_class', "");
                }
                $html->parse('payment_system', true);
            }
        }
        $html->parse('payment_systems');
    }

    public static function getPaymentBeforeById($id)
    {
        $sql = 'SELECT * FROM `payment_before`
            WHERE `id` = ' . to_sql($id);
        return DB::row($sql);
    }

    public static function getPaymentBeforeBySubscriptionId($id)
    {
        $sql = 'SELECT * FROM payment_before
            WHERE subscription_id = ' . to_sql($id) . '
                AND  `system` IN (' . to_sql(self::getSystem()) . ', ' . to_sql(self::getSystem() . ' payed') . ')';
        return DB::row($sql);
    }

    public static function getPaymentBeforeApp($packageName, $productId, $purchaseToken)
    {
        $sql = 'SELECT * FROM payment_before
            WHERE `subscription_id` = ' . to_sql($purchaseToken) .
                ' AND `app_package_name` = ' . to_sql($packageName) .
                ' AND `app_product_id` = ' . to_sql($productId) .
                ' AND `system` IN (' . to_sql(self::getSystem()) . ', ' . to_sql(self::getSystem() . ' payed') . ')';
        return DB::row($sql);
    }

    /*
    public static function getPaymentBeforeByOrderId($orderId)
    {
        $where = '`order_id` = ' . to_sql($orderId) . ' AND `system` IN (' . to_sql(self::getSystem()) . ', ' . to_sql(self::getSystem() . ' payed') . ')';
        return DB::one('payment_before', $where);
    }


    public static function getPaymentBeforeByToken($token)
    {
        $where = '`token` = ' . to_sql($token) . ' AND `system` IN (' . to_sql(self::getSystem()) . ', ' . to_sql(self::getSystem() . ' payed') . ')';
        return DB::one('payment_before', $where);
    }
     */

    public static function callbackUrl($params = array())
    {
        if($params) {
            $params = '?' . http_build_query($params);
        } else {
            $params = '';
        }
        return Common::urlSiteSubfolders() . '_pay/' . self::getSystem() . '/after.php' . $params;
    }

    public static function getSystem()
    {
        return static::$system;
    }

    public static function createOrder($data = array())
    {
        $item = self::checkPlan();
        if ($item === false) {
            exit;
        }

        $requestUri = self::getRequestUri();

        $plan = self::getOptionsPlan($item);
        self::setPlan($plan);
        $code = self::getCode($item, $plan['amount']);

        /*
        $data = array(
            'item' => $item,
            'system' => self::getSystem(),
            'code' => $code,
            'request_uri' => $requestUri,
        );
         */

        $data['item'] = $item;
        $data['system'] = self::getSystem();
        $data['code'] = $code;
        $data['request_uri'] = $requestUri;

        return self::insertBefore($data);
    }

    public static function setPlan($plan)
    {
        static::$plan = $plan;
    }

    public static function getPlanField($field)
    {
        return isset(static::$plan[$field]) ? static::$plan[$field] : '';
    }

    public static function updateSubscriptionId($id, $subscriptionId) {
        DB::update('payment_before', array('subscription_id' => $subscriptionId), 'id = ' . to_sql($id));
    }

    public static function setAcceptPayment($acceptPayment) {
        self::$acceptPayment = $acceptPayment;
    }

    public static function getAcceptPayment() {
        return self::$acceptPayment;
    }

    static function prepareRequestUri($uri)
    {
        if ($uri) {
            $paramsRequestUri = explode('?', $uri);
            $uri = $paramsRequestUri[0];
            if (isset($paramsRequestUri[1])) {
                $urlGetParams = $paramsRequestUri[1];
                $delParams = array('set_template',
                                   'set_template_runtime',
                                   'set_template_mobile',
                                   'set_template_mobile_runtime',
                                   'site_part_runtime',
                                   'upload_page_content_ajax',
                                   'ajax',
                                   'cmd');
                foreach ($delParams as $par) {
                    $urlGetParams = del_param($par, $urlGetParams, true, false);
                    if (!$urlGetParams) {
                        break;
                    }
                }
                if ($urlGetParams) {
                    $uri .= '?' . $urlGetParams;
                }
            }
        }
        return $uri;
    }

    public static function timestampDiffToDays($time)
    {
        $days = ceil(($time - time()) / (24 * 3600));
        if ($days <= 0) {
            $days = 0;
        }

        return $days;
    }

    protected static function setSubscriptionExpireTimestamp($timestamp)
    {
        self::$subscriptionExpireTimestamp = $timestamp;
    }

    public static function getSubscriptionExpireTimestamp()
    {
        return self::$subscriptionExpireTimestamp;
    }

    public static function parseInAppPurchaseProducts(&$html)
    {
        if(!guid() || !$html->blockexists('in_app_purchase_products') || !Common::isApp() || ((Common::isAppAndroid() && !Common::isOptionActive('active', 'iapgoogle')) || (Common::isAppIos() && !Common::isOptionActive('active', 'iapapple')))) {
            return;
        }

        if(Common::isAppAndroid()) {
            $iapPaymentModule = PayIapGoogle::getSystem();
        } else {
            $iapPaymentModule = PayIapApple::getSystem();
        }
        $where = '`payment_modules_off` NOT LIKE "%' . to_sql($iapPaymentModule, 'Plain') . '%"
            AND `' . to_sql($iapPaymentModule . '_product_id', 'Plain') . '` != ""';

        $paymentPlans = DB::select('payment_plan', $where);

        if($paymentPlans) {
            foreach($paymentPlans as $paymentPlan) {
                $html->setvar('in_app_purchase_item', $paymentPlan['item']);
                $html->setvar('in_app_purchase_product_id', trim($paymentPlan[$iapPaymentModule . '_product_id']));
                $html->setvar('in_app_purchase_product_type', $paymentPlan['type'] === 'credits' ? 'consumable' : 'paid subscription');
                $html->parse('in_app_purchase_product');
            }
            $html->parse('in_app_purchase_products');
        }

    }

    public static function getIapPaymentSystemsOffString()
    {
        $iapPaymentSystemsOffString = '';

//        if(!Common::isAppAndroid()) {
//            $iapPaymentSystemsOffString = ',' . PayIapGoogle::getSystem();
//        }

        if(!Common::isApp()) {
            $iapPaymentSystemsOffString = ',' . PayIapGoogle::getSystem() . ',' . PayIapApple::getSystem();
        }

        return $iapPaymentSystemsOffString;
    }

    public static function whereApp()
    {
        // for app parse only Google Play/App Store payment plans
        $iapPaymentModule = '';

        $isApp = Common::isApp();

        if($isApp) {
            if(Common::isAppAndroid()) {
                if(Common::isOptionActive('active', 'iapgoogle')) {
                    $iapPaymentModule = PayIapGoogle::getSystem();
                }
            } else {
                if(Common::isOptionActive('active', 'iapapple')) {
                    $iapPaymentModule = PayIapApple::getSystem();
                }
            }
        }

        $whereApp = '';
        if($iapPaymentModule !== '') {
            $whereApp = ' AND `payment_modules_off` NOT LIKE "%' . to_sql($iapPaymentModule, 'Plain') . '%"
                AND `' . to_sql($iapPaymentModule . '_product_id', 'Plain') . '` != ""';
        }

        return $whereApp;
    }

    public static function getActiveSubscriptionGooglePlay($uid)
    {
        $where = '`user_id` = ' . to_sql($uid) . '
                AND `type` = "subscription"
                AND `system` = "iapgoogle payed"
                AND `subscription_expiry_time` > ' . to_sql(time());
        $subscription = DB::one('payment_before', $where, '`subscription_expiry_time` DESC');

        return $subscription;
    }

    public static function parseGooglePlaySubscrionManageButton($html)
    {
        if(Common::isAppAndroid()) {
            $subscription = self::getActiveSubscriptionGooglePlay(guid());
            if($subscription) {
                $html->setvar('google_play_subscriptions_url', 'https://play.google.com/store/account/subscriptions?sku=' . $subscription['app_product_id'] . '&package=' . $subscription['app_package_name']);
                $html->parse('google_play_subscriptions');
            }
        }
    }

}