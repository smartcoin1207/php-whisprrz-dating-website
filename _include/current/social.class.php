<?php

class Social {
    static $social = false;
    static $currentActive = false;
    static $socialArr = array();
    static $pages = array(
            'index.php',
            'join.php',
            'join_facebook.php',
            'profile_settings.php',
            'search_results.php',
            'page.php',
            'blogs_list.php',
            'vids_list.php',
            'profile_view.php'
        );

    static  $socialNames = array('facebook','linkedin','google_plus','vk','twitter');

    static $activeItems = array();

    static function setActive($name)
    {
        self::$currentActive=$name;
    }

    static function getActive()
    {
        return self::$currentActive;
    }

    static function setSocial($social)
    {
        self::$socialArr = $social;
    }

    static function getSocial()
    {
        return self::$socialArr;
    }

    static function getCurrentSocial()
    {
        $currentSocial = self::getActive();
        $socialArr = self::getSocial();
        return isset($socialArr[$currentSocial]) ? $socialArr[$currentSocial] : null;
    }

    static function getPages()
    {
        return self::$pages;
    }

    static function addActiveItem($item, $position)
    {
        self::$activeItems[$item] = $position;
    }

    static function getActiveItems()
    {
        return self::$activeItems;
    }

    static function apiInitParams()
    {
        return false;
    }


    static function init()
    {
        global $g;
        global $p;

        $template = Common::getTmplName();

        if ($template != 'edge') {
            if (!in_array($p, self::getPages())
                    && !Common::isOptionActive('profile_verification_enabled')) {
                return;
            }
            //if($p == 'search_results.php' && Common::isMobile()) {
                //return;
            //}
        }

        foreach(self::$socialNames as $k=>$v){
            $socialArr[$v]=false;
        }
        foreach($socialArr as $n=>$s){
            if(Common::isOptionActive('social_login_'.$n) || true){
                $socialArr[$n]=$n::getInstance();
                if($socialArr[$n]) {
                    self::addActiveItem($n, Common::getOption($n . '_place'));
                }
            }
        }

        asort(self::$activeItems);

        self::setSocial($socialArr);

    }

    static function parseLinks(&$html, $block = 'footer_social_links')
    {
        if ($html->blockExists($block)) {

            $socialArr = self::getSocial();
            $orderButtons = array();
            foreach($socialArr as $n => $social){
                $orderButtons[$n] = Common::getOption("{$n}_place");
            }
            asort($orderButtons);
            $blockItem = $block . '_item';
            $isParse = false;
            foreach ($orderButtons as $social => $v){
                $socialUrl = Common::getOption("{$social}_url");
                if ($socialUrl) {
                    $html->setvar("{$blockItem}_url", $socialUrl);
                    $html->setvar("{$blockItem}_title", l("{$social}_title"));

                    $prf = '';
                    if ($social != 'vk' && $social != 'google_plus') {
                        $prf = '-square';
                    }
                    if ($social == 'google_plus') {
                        $social = 'google';
                    }

                    $html->setvar("{$blockItem}_prf", $prf);
                    $html->setvar("{$blockItem}_type", $social);

                    $html->parse($blockItem, true);
                    $isParse = true;
                }
            }
            if ($isParse) {
                $html->parse($block, false);
            }
        }
    }


    static function parse(&$html, $block = 'social_login')
    {


        global $g;

        if(!(Common::isOptionActive('social_login_enabled', "edge_main_page_settings"))) {
            return false;
        }   

        $socialArr = self::getSocial();
        $orderButtons = array();
        foreach($socialArr as $n=>$social){
            $orderButtons[$n]=Common::getOption($n.'_place');
        }
        asort($orderButtons);

        if ($html->blockExists($block) && !PWA::isModePwa()) {//весь код обернуть в это
            $blockItem = $block . '_item';
            $isParse = false;
            $template = Common::getTmplName();
            foreach($orderButtons as $n=>$v){
                $social=$socialArr[$n];
              //  $n=replaceForMethod($n);
             // $html->setvar('facebook_app_id', $g['options']['facebook_appid']);
                $html->setvar($n.'_app_id', Common::getOption($n.'_appid'));
                if($social!=false){
                    /*
                    $html->setvar($n.'_login_url', $social->loginRedirectUrl());
                    $html->parse($n.'_login', false);
                    $html->parse($n.'_register', false);
                    $html->parse($n.'_register_dating', false);
                    */
                    //$social->parse();

                    //{l_log_in_with_facebook} Когда дин пункт останется может название нужно будет вывдить
                    //$html->setvar($blockItem . '_title', l());
                    if ($template == 'edge' && $n == 'google_plus') {
                        $n = 'google';
                    }
                    $html->setvar($blockItem . '_type', $n);
                    $html->setvar($blockItem . '_url', $social->loginRedirectUrl());
                    $html->parse($blockItem, true);
                    $isParse = true;
                } else {
                    $html->parse('no_facebook_login', false);// основная версия http://clip2net.com/s/3znqz0a
                }
            }
            if ($isParse) {
                $html->parse($block, false);
            } else {
                $html->parse($block . '_no_class', false);//для мобильного посмотреть нужен будет вообще этот класс после вёрстки
            }
        } elseif($socialArr && $socialArr['facebook']) {
            $social=$socialArr['facebook'];
            $n='facebook';
            $html->setvar('facebook_app_id', $g['options']['facebook_appid']);
            $html->setvar($n.'_app_id', Common::getOption($n.'_appid'));
            $html->setvar($n.'_login_url', $social->loginRedirectUrl());
            $html->parse($n.'_login', false);
            $html->parse($n.'_register', false);
            $html->parse($n.'_register_dating', false);

        }

    }

    static function parseSettings(&$html)
    {
        global $g_user;

        $socialArr=self::getSocial();

        foreach($socialArr as $n=>$social){
            if($g_user['user_id'] && $social) {
                if($g_user[$n.'_id']) {
                    $html->parse($n.'_disconnect');
                } else {
                    $html->parse($n.'_connect');
                }
            }

        }
    }

    static function login()
    {
        $currentSocial = self::getActive();

        $social = self::getCurrentSocial();

        if ($social) {
            $fid=$social->getUserId();

            if ($fid) {
                $sql = 'SELECT `user_id`, `active` FROM user
                    WHERE `'.$currentSocial.'_id` = ' . to_sql($fid, 'Text');
                $user = DB::row($sql);
                $userApproval = 0;
                $uid = 0;
                if ($user != false) {
                    $uid = $user['user_id'];
                    $userApproval = $user['active'];
                }
                if ($uid) {
                    $isApproval = true;
                    if (Common::isOptionActive('manual_user_approval') && !$userApproval) {
                        $isApproval = false;
                    }
                    if ($isApproval) {
                        delses('logout');
                        set_session('user_id', $uid);
                        set_session('user_id_verify', $uid);

                        CStatsTools::count('logins', $uid);
                        User::updateLastVisit($uid);

                        Common::toHomePage();
                    } else {
                        $page = (Common::isMobile()) ? 'index.php' : 'join.php';
                        redirect("{$page}?cmd=wait_approval");
                    }

                }
            }
            $social->setJoinInfo();
        }
    }

    static function connect($to = '')
    {
        $currentSocial = self::getActive();

        $social = self::getCurrentSocial();

        if($social) {
            $social->setJoinInfo();

            $sql = 'UPDATE user
                SET '.$currentSocial.'_id = ' . to_sql(get_session($currentSocial.'_id'), 'Text') . '
                WHERE user_id = ' . guid();
            DB::execute($sql);
            set_session($currentSocial.'_id', '');
        }

        redirect($to);
    }

    static function disconnect()
    {
        $currentSocial = self::getActive();
        $sql = 'UPDATE user
            SET '.$currentSocial.'_id = ""
            WHERE user_id = ' . guid();
        DB::execute($sql);
        set_session($currentSocial.'_id', '');

        redirect();
    }

    static function checkParams($social)
    {
        $params=$social->getParamsAuth();
        if(strlen($params['client_id']) == 0 || strlen($params['client_secret']) == 0){
            redirect('index.php');
        }
        return true;

    }

    static function getCallbackUrl($method)
    {
        /*$currentUrl='';
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on')) {
            $currentUrl.='https://';
        } else {
            $currentUrl.='http://';
        }
        $currentUrl .= $_SERVER['SERVER_NAME'];
        $uri=explode('?',$_SERVER['REQUEST_URI']);
        $currentUrl .= $uri[0].'?module='.$method;

        return $currentUrl;*/
        //return Common::urlPage(true);
        return Common::urlSite() . 'social_login.php?module=' . $method;
    }

    static function logError($message)
    {
            //file_puts_content('./_files/temp/error_oauth.html',$message);
 //       echo $message;exit;
    }
}