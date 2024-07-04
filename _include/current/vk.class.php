<?php

class vk
{
    private static $instance;
    private static $nameSocial='vk';

    private function getUrlAuth()
    {
      return 'social_login.php?module='.self::$nameSocial;
    }

    private function getUrlToken()
    {
        return  '';
    }

    public function getParamsAuth()
    {
        $client_id = Common::getOption(self::$nameSocial.'_appid');
        $client_secret = Common::getOption(self::$nameSocial.'_secret');

        $params = array(
            'response_type' => 'code',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'email'
        );

        return $params;
    }

    public function getUserInfo($code)
    {
        $result = false;
        $userInfo=false;

        $userInfo=get_session(self::$nameSocial.'_user_info',false);

        return $userInfo;
    }


    public static function getInstance()
    {
        global $g;
        if (isset($g['options'][self::$nameSocial.'_appid'])
            && isset($g['options'][self::$nameSocial.'_secret'])
            && $g['options'][self::$nameSocial.'_appid'] != ''
            && $g['options'][self::$nameSocial.'_secret'] != ''
        ) {

            if(self::$instance === null){
                // Create our Application instance (replace this with your appId and secret)
                self::$instance = new self(array(
                    'appId' => $g['options'][self::$nameSocial.'_appid'],
                    'secret' => $g['options'][self::$nameSocial.'_secret'],
                    'cookie' => true,
                ));

            }

            return  self::$instance;
        } else {
            return false;
        }
    }

    public function parse()
    {

    }

    public function getUserId()
    {
        $userInfo = get_session(self::$nameSocial.'_user_info');

        if(isset($userInfo['id'])){
            return $userInfo['id'];
        } else {
            return false;
        }
    }

    public function loginRedirectUrl()
    {
        $url = '';
        $url = $this->getUrlAuth();
        return $url;
    }

    public function setJoinInfo()
    {
        global $g;


        $me = get_session(self::$nameSocial.'_user_info');

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

            if(!isset($me['first_name'])){
                $me['first_name']='';
            }

            if(!isset($me['last_name'])){
                $me['last_name']='';
            }

            if (isset($me['first_name'])) {
                if (get_param('join_handle') == '') {
                    $_GET['join_handle'] = implode(' ',array($me['first_name'],$me['last_name']));
                }
            }

                if (isset($me['bdate']) && $me['bdate']) {
                    $birthDate = explode('.', $me['bdate']);

                    if (is_array($birthDate) && count($birthDate)) {
                        if (get_param('month') == '' && isset($birthDate[1])) {
                            $_GET['month'] = $birthDate[1];
                        }
                        if (get_param('day') == '' && isset($birthDate[0])) {
                            $_GET['day'] = $birthDate[0];
                        }
                        if (get_param('year') == '' && isset($birthDate[2])) {
                            $_GET['year'] = $birthDate[2];
                        }
                    }
                }


            set_session(self::$nameSocial.'_id', $me['id']);
            set_session('social_id', $me['id']);
            set_session('social_type', self::$nameSocial);
            // set picture if exists
            if(isset($me['photo_big']) && strpos($me['photo_big'],'camera_200.png')===false) {
                set_session('social_photo', $me['photo_big']);
            } else {
                set_session('social_photo', '');
            }

                    if(isset($me['sex'])) {
                        $gender = $me['sex'];
                        if($gender == 1) {
                            $gender = 'F';
                        }
                        if($gender == 2) {
                            $gender = 'M';
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


    static function getLikeButtonScript()
    {

        return '';

    }

    static function getLikeButtonHtml()
    {
        return '';
    }

    public function oAuthApi()
    {
        $nameSocial=self::$nameSocial;
        $params=$this->getParamsAuth();
        $currentUrl = Common::urlSite() . $this->loginRedirectUrl();
        $email = '';

        if (isset($_GET['code'])) {
            $result = false;
            $params['redirect_uri']=$currentUrl;
            $params['code'] = $_GET['code'];
            $params['scope'] = 'email';
            $token = json_decode(urlGetContents('https://oauth.vk.com/access_token', 'get', $params), true);

            if (isset($token['access_token'])) {
                $params = array(
                    'uids'         => $token['user_id'],
                    'fields'       => 'id,first_name,last_name,screen_name,sex,bdate,photo_big',
                    'access_token' => $token['access_token'],
                    'scope' => 'email',
                    'v' => '5.120',
                );

                if(isset($token['email'])){
                    $email = $token['email'];
                }

                $userInfo = json_decode(urlGetContents('https://api.vk.com/method/users.get', 'get', $params), true);
                if (isset($userInfo['response'][0]['id'])) {
                    $userInfo = $userInfo['response'][0];
                    $userInfo['email'] = $email;
                    $result = true;
                }
            }

            if ($result) {
                set_session('vk_user_info', $userInfo);
                redirect('join_facebook.php?cmd=vk_login');
            } else {
                Common::toHomePage();
            }
        }



        $url = 'http://oauth.vk.com/authorize';

            $params2 = array(
                'client_id'     => $params['client_id'],
                'redirect_uri'  => $currentUrl,
                'response_type' => 'code',
                'scope' => 'email'
            );

        redirect($url . '?' . http_build_query($params2));

    }

}