<?php

class FacebookBase {

    public static $instance;
    public static $nameSocial = 'facebook';
    public static $me = null;

    public function getUserId()
    {
        $me = self::getMe();
        return isset($me['id']) ? $me['id'] : 0;
    }

    public function loginRedirectUrl()
    {
        $urlSite = Common::urlSite() . 'join_facebook.php?cmd=fb_login';

        $url = '';

        $helper = self::$instance->getRedirectLoginHelper();
        $permissions = array('email');

        $url = $helper->getLoginUrl($urlSite, $permissions);

        return $url;
    }

    public function setJoinInfo()
    {
        global $g;

        $me = self::getMe();

        if($me) {

            set_session(self::$nameSocial.'_id', 0);
            set_session(self::$nameSocial.'_photo', false);
            set_session('social_id', 0);
            set_session('social_photo', false);

            // check if already registered
            if ($me) {

                if (isset($me['email'])) {
                    if (get_param('email') == '') {
                        $_GET['email'] = $me['email'];
                        $_GET['verify_email'] = $me['email'];
                    }
                }

                if (isset($me['name'])) {
                    if (get_param('join_handle') == '') {
                        $_GET['join_handle'] = $me['name'];
                    }
                }

                if (isset($me['birthday']) && $me['birthday']) {
                    $birthDate = explode('/', $me['birthday']);

                    if (is_array($birthDate) && count($birthDate)) {
                        if (get_param('month') == '') {
                            $_GET['month'] = $birthDate[0];
                        }
                        if (get_param('day') == '') {
                            $_GET['day'] = $birthDate[1];
                        }
                        if (get_param('year') == '') {
                            $_GET['year'] = $birthDate[2];
                        }
                    }
                }

                set_session(self::$nameSocial.'_id', $me['id']);
                set_session('social_id', $me['id']);
                set_session('social_type', self::$nameSocial);
                // set picture if exists
                if(isset($me['picture']['data']['is_silhouette']) && $me['picture']['data']['is_silhouette'] === false) {
                    set_session('social_photo', 'http://graph.facebook.com/' . $me['id'] . '/picture?width=' . $g['image']['big_x']);
                }

                if(isset($me['gender'])) {
                    $gender = $me['gender'];
                    if($gender == 'male') {
                        $gender = 'M';
                    }
                    if($gender == 'female') {
                        $gender = 'F';
                    }

                    $sql = 'SELECT id FROM const_orientation
                        WHERE gender = ' . to_sql($gender, 'Text') . '
                        ORDER BY id ASC LIMIT 1';
                    $orientation = DB::result($sql);

                    if($orientation) {
                        $_GET['orientation'] = $orientation;
                    }
                }
            }
        }
    }

    static function getLikeButtonScript()
    {
        //$script = self::getLikeButtonPluginScript();
        $script = "<div id=\"fb-root\"></div>
                <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = \"//connect.facebook.net/{lang}/sdk.js#xfbml=1&appId={app_id}&version=v2.3\";
                fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>";
        $parse = array('{lang}', '{app_id}');
        $replace = array(Common::getLocaleCode(), Common::getOption('facebook_appid'));

        return str_replace($parse, $replace, $script);

    }

    static function getLikeButtonHtml()
    {
        $htmlButton = '<div class="fb-like" data-href="{url_page}" data-layout="button_count" data-action="like" data-show-faces="true"></div>';
        $button = trim(Common::getOption('facebook_like_button_html', 'main'));
        $html = $button ? $button : $htmlButton;

        return str_replace('{url_page}', Common::urlSiteSubfolders() . Common::page(), $html);
    }

    static function getMe()
    {
        if(self::$me === null) {

            self::$me = false;

            $helper = self::$instance->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            if($accessToken) {
                self::$instance->setDefaultAccessToken($accessToken);
                $response = self::$instance->get('/me?fields=id,picture,name,location,email,birthday,gender');
                self::$me = $response->getDecodedBody();
            }
        }

        return self::$me;
    }

}


if (!DEV_PROFILING && version_compare(PHP_VERSION, '5.4.0', '>=')) {

    $autoloadersList = spl_autoload_functions();

    foreach($autoloadersList as $autoloaderListItem) {
        spl_autoload_unregister($autoloaderListItem);
    }

    include_once __DIR__ . '/facebook/php-graph-sdk/src/Facebook/autoload.php';

    foreach($autoloadersList as $autoloaderListItem) {
        spl_autoload_register($autoloaderListItem);
    }

    class Facebook extends Facebook\Facebook
    {

        public static function getInstance()
        {
            global $g;

            if(Common::isAppAndroid()) {
                return false;
            }

            if (isset($g['options'][self::$nameSocial . '_appid'])
                && isset($g['options'][self::$nameSocial . '_secret'])
                && $g['options'][self::$nameSocial . '_appid'] != ''
                && $g['options'][self::$nameSocial . '_secret'] != ''
            ) {

                if(self::$instance === null) {
                    self::$instance = new self(array(
                        'app_id' => $g['options'][self::$nameSocial . '_appid'],
                        'app_secret' => $g['options'][self::$nameSocial . '_secret'],
                        'default_graph_version' => 'v2.2',
                        'persistent_data_handler' => new Facebook\PersistentData\CustomSessionPersistentDataHandler(),
                    ));
                }

                return self::$instance;
            } else {
                return false;
            }
        }

    }

} else {

    // disabled in PHP 5.3 and older
    class Facebook extends FacebookBase {

        public static function getInstance()
        {
            return false;
        }

    }

}

