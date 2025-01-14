<?php

class Google_plus
{
    private static $instance;
    private static $nameSocial='google_plus';


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
        );

        return $params;
    }


    public static function getInstance()
    {
        global $g;

        if(Common::isAppIos() || (Common::isAppAndroid() && strpos($_SERVER['HTTP_USER_AGENT'], '; wv') !== false)) {
            return false;
        }

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

            if (isset($me['name'])) {
                if (get_param('join_handle') == '') {
                    $_GET['join_handle'] = $me['name'];
                }
            }


            set_session(self::$nameSocial.'_id', $me['id']);
            set_session('social_id', $me['id']);
            set_session('social_type', self::$nameSocial);
            // set picture if exists
            if(isset($me['picture']) &&
                $me['picture']!=
                'https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg' &&
                md5_file($me['picture'])!='f741f6fb93497e342bf3d623673d040b'
            ) {
                set_session('social_photo', $me['picture']);
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

        require(dirname(__FILE__).'/../../_include/current/oauth/http.php');
        require(dirname(__FILE__).'/../../_include/current/oauth/oauth_client.php');

        $currentUrl = Social::getCallbackUrl($nameSocial);

        $client = new oauth_client_class;
        $client->server = 'Google';
        $client->debug = false;
        $client->debug_http = true;
        $client->scope = 'https://www.googleapis.com/auth/userinfo.email '.
            'https://www.googleapis.com/auth/userinfo.profile';

        $userBirthday='';

        $client->redirect_uri = $currentUrl;
        $client->offline = true;
        $client->client_id = $params['client_id'];
        $application_line = __LINE__;
        $client->client_secret = $params['client_secret'];

        if(($success = $client->Initialize()))
        {
            if(($success = $client->Process()))
            {
                if(strlen($client->authorization_error))
                {
                    $client->error = $client->authorization_error;
                    $success = false;
                }
                elseif(strlen($client->access_token))
                {
                    /*
                    $success = $client->CallAPI(
                    'https://www.googleapis.com/plus/v1/people/me',
                        'GET', array(), array('FailOnAccessError'=>true), $user);
                    if($success){
                        if(isset($user->birthday)){
                            $userBirthday=$user->birthday;
                        }
                    }*/
                    $success = $client->CallAPI(
                        'https://www.googleapis.com/oauth2/v1/userinfo',
                        'GET', array(), array('FailOnAccessError'=>true), $user);
                }
            }
            $success = $client->Finalize($success);


        }

        if($client->exit){
        exit;
        }

        if($success)
        {
            $user->birthday = $userBirthday;
            $userInfo = (array)$user;
            set_session($nameSocial.'_user_info', $userInfo);
            redirect('join_facebook.php?cmd=gl_login');         /*******************/
        } else {
            $message = HtmlSpecialChars($client->error);
            Social::logError($message);
            redirect('join.php');
        }




    }



}