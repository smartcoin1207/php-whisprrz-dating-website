<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CJoinForm extends CHtmlBlock {

    var $message = "";
    var $login = "";
    var $responseData = "";
    var $ajax = 0;

    function init()
    {
        global $g;
        global $l;
        global $gc;
        global $g_user;

        $this->ajax = get_param('ajax');
        $name = trim(get_param('join_handle', ''));
        $pass = get_param('join_password', '');
        $mail = trim(get_param('email'));
        $country = intval(get_param('country'));
        $this->message = "";

        // Fix - prevent registration by authorized users via ajax requests
        if ($this->ajax && guid()){
            $this->setResponseData('redirect', Common::getHomePage());
            return;
        }

        $cmd = get_param('cmd');

        if ($cmd == 'fb_register') {
            $this->message = Social::setJoinInfo();
        }

        // Everything related $this-> responseData and $this->ajax this URBAN
        if ($cmd == 'register') {

            $msg = $name;
            /*$censured = false;
            $censuredFile = dirname(__FILE__) . '/../../_server/im_new/feature/censured.php';
            $to_user = 1;
            if (file_exists($censuredFile)) include($censuredFile);*/
            $msg = censured($msg);
            if ($msg != $name) {
                $validate = l('username_contains_invalid_characters');
                $this->message .=  $validate;
                $this->setResponseData('name', $validate);
            } else {
                $validate = User::validateName($name);
                $this->message .= $validate;
                $this->setResponseData('name', $validate);
            }

            //$this->message .= User::validateEmail($mail);
            $validate = User::validatePassword($pass);
            $this->message .= $validate;
            $this->setResponseData('password', $validate);

            /*$day    = (int)get_param("day", 1);
            $month  = (int)get_param("month", 1);
            $year   = (int)get_param("year", 1980);*/
            $month  = intval(Common::getDefaultBirthday('month', get_param('month')));
            $day    = intval(Common::getDefaultBirthday('day', get_param('day')));
            $year   = intval(Common::getDefaultBirthday('year', get_param('year')));


            $isIos = Common::isAppIos();
            //$isIos = true;
            // ??? Check for the correct date on the IOS does not need !checkdate($month, $day, $year)

            //$this->message .= User::validateBirthday($month, $day, $year);

            $validate = User::validateEmail($mail);
            $this->message .= $validate;
            $this->setResponseData('mail', $validate);

            $validate = User::validateBirthday($month, $day, $year);
            $this->message .= $validate;
            $this->setResponseData('birthday', $validate);

            
            $defaultOrientation = User::getDefaultOrientation();
            if(Common::isEdgeLmsMode()) {
                $defaultOrientation = LMS::getDefaultUserType();
            }

            $orientation = get_param('orientation', $defaultOrientation);

            $state = intval(get_param('state'));
            $city = intval(get_param('city'));

            if ($isIos) {
                if (!$orientation) {
                    $orientation = $defaultOrientation;
                }
                function getCityId($stateId) {
                    $sql = 'SELECT `city_id`
                              FROM `geo_city`
                             WHERE `state_id` = ' . to_sql($stateId, 'Number') .
                           ' ORDER BY `city_title` ASC
                             LIMIT 1';
                    return DB::result($sql);
                }
                if (!$country) {
                    //$geoInfo = IP::geoInfoCity();
                    $geoInfo = getDemoCapitalCountry();
                    if ($geoInfo) {
                        $country = $geoInfo['country_id'];
                        $state = $geoInfo['state_id'];
                        $city = $geoInfo['city_id'];
                    }
                } elseif (!$state) {
                    $sql = 'SELECT `state_id`
                              FROM `geo_state`
                             WHERE `country_id` = ' . to_sql($country, 'Number') .
                           ' ORDER BY `state_title` ASC
                             LIMIT 1';
                    $state = DB::result($sql);
                    $city = getCityId($state);
                } elseif (!$city) {
                    $city = getCityId($state);
                }
                $default = DB::result('SELECT `id` FROM `const_orientation` WHERE `default` = 1', 0, 1);
            }

            if (!$this->ajax) {
                $this->message .= User::validateCountry($country);
            }

            /* URBAN */
            if ($this->ajax) {
                $validate = User::validateLocation($country, $state, $city, true);
                $this->setResponseData('location', $validate);
            }
            $isCustomRegister = Common::isOptionActive('custom_user_registration', 'template_options');
            if (Common::isMobile() || ($this->ajax && !$isCustomRegister)) {
                if (Common::isOptionActive('recaptcha_enabled')) {
                    require_once('_server/securimage/initRecaptcha.php');
                    $secretKey = Common::getOption('recaptcha_secret_key');
                    $recaptcha = new \ReCaptcha\ReCaptcha($secretKey);
                    $recaptchaResponse = get_param('recaptcha');//g-recaptcha-response
                    $resp = $recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR']);
                    if (!$resp->isSuccess()){
                        $this->message .=  l('incorrect_captcha');
                        $this->setResponseData('recaptcha', l('incorrect_captcha'));
                    }
                } else {
                    $captcha = get_param('captcha');
                    if (!Securimage::check($captcha)) {
                        $this->message .=  l('incorrect_captcha');
                        $this->setResponseData('captcha', l('incorrect_captcha'));
                    }
                }
            }
            /* URBAN */

            /*if(Common::isMobile()) {
                $captcha = get_param('captcha');
                if(!check_captcha($captcha,'',false,false)) {
                    $this->message .=  l('incorrect_captcha');
                }
            }*/

            if ($this->message == "" && $this->responseData == '') {
                set_session("j_name", $name);
                set_session("j_password", $pass);
                set_session("j_mail", $mail);
                set_session("j_month", $month);
                set_session("j_day", $day);
                set_session("j_year", $year);
                set_session("j_country", $country);
                if ($this->ajax) {
                    set_session("j_state", $state);
                    set_session("j_city", $city);
                }
                if ($isCustomRegister) {
                    if (get_param('orientation', false) !== false) {
                        set_session("j_orientation", $orientation);
                    }
                    if (UserFields::isActiveSexuality()) {
                        $pSexuality = get_param('p_sexuality');
                        if ($pSexuality) {
                            set_session("j_sexuality", $pSexuality);
                        }
                    }
                } else {
                    set_session("j_orientation", $orientation);
                }

                if($orientation == '5') {
                    set_session('j_couple_profile', 0);
                }
               
                if ((Common::isMobile() || ($this->ajax && !$isCustomRegister)) && $g['options']['join'] == "one_foto" && !Common::isOptionActive('join_with_photo_only') && 1!=1) {
                    $uid = User::add();
                    if (!$uid) {
                        $this->message = l('exists_email');
                        $this->setResponseData('mail', l('exists_email'));
                    } else {
                        $g_user['user_id'] = $uid;
                        if (get_session('social_photo', false) != false) {
                            uploadphoto($g_user['user_id'], '', '', (Common::isOptionActive('photo_approval') ? 0 : 1), '', get_session('social_photo'));
                            set_session('social_photo', false);
                        }
                        if (Common::isOptionActive('manual_user_approval')) {
                            if ($this->ajax){
                                $this->setResponseData('wait_approval', 'wait_approval');


                            } else {
                                redirect('index.php?cmd=wait_approval');
                            }
                        }

                        if ($this->ajax){
                            if ($this->responseData == '') {
                                $this->setResponseData('redirect', Common::getHomePage());
                            }
                        } else {
                            Common::toHomePage();
                        }
                    }
                } elseif ($isCustomRegister) {
                    $this->setResponseData('redirect', Common::pageUrl('join2'));
                } elseif ($g['options']['join'] == "full") {
                    $this->setResponseData('redirect', Common::pageUrl('join2'));

                } else {
                    $this->setResponseData('redirect', Common::pageUrl('join3.php'));

                    // redirect("join3.php");
                }
            }
        }
    }

    function setResponseData($name, $validate) {
        if($this->ajax && $validate) {
            $this->responseData .= "<span class='" . $name . "'>" . strip_tags($validate) . '</span>';
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_info;
        global $l;
        global $p;

        $isUploadPageAjax = get_param('upload_page_content_ajax');

        $cmd = get_param('cmd');
        if ($cmd == 'exists_email') {
            $html->parse('exists_email', false);
        }
        $html->setvar('users_age', Common::getOption('users_age'));

        $isCustomRegister = Common::isOptionActive('custom_user_registration', 'template_options');
        $optionTmplSet = Common::getOption('set', 'template_options');
        $optionTmplName = Common::getTmplName();

            if ($optionTmplName == 'edge') {
                $blocks = array('register_now_social_1', 'register_now_social_2', 'register_now_social_3', 'register_now_mobile_social');
                foreach ($blocks as $key => $block) {
                    Social::parse($html, $block);
                }
            } elseif($p=='join_facebook.php'){
                Social::parse($html);
            }
        
        htmlSetVars($html, $g_info);

        $isIos = Common::isAppIos();
        //$isIos = true;
        $html->setvar('is_ios', intval($isIos));
        if($isIos) {
            $html->setvar('required_field_sign', l('required_field_sign'));
        }

        Common::parseCaptcha($html);
        if (Common::isMobile()) {
            $this->message = str_replace('<br>', '\n', $this->message);
        }

        $formatDateMonths = 'F';
        $optionFormatDateMonths = Common::getOption('format_date_months_join', 'template_options');
        if ($optionFormatDateMonths) {
            $formatDateMonths = $optionFormatDateMonths;
        }

        $defaultBirthday = Common::getDefaultBirthday();
        $defaultDay = $defaultBirthday['day'];
        $defaultMonth = $defaultBirthday['month'];
        $defaultYear = $defaultBirthday['year'];
        if ($isIos) {
            $defaultDay = 0;
            $defaultMonth = 0;
            $defaultYear = 0;
        }
        $vars = array(
            'autocomplete' => autocomplete_off(),
            'join_message' => $this->message,
            'month_options' => h_options(Common::plListMonths($formatDateMonths, $isIos), get_param('month', $defaultMonth)),
            'day_options' => n_options(1, 31, get_param('day', $defaultDay), $isIos),
            'year_options' => n_options(date('Y') - $g['options']['users_age_max'], date("Y") - $g['options']['users_age'], get_param("year", $defaultYear), $isIos),
            //'orientation_options' => DB::db_options("SELECT id, title FROM const_orientation", get_param("orientation", "")),
            'looking_options' => DB::db_options("SELECT id, IF(title!='',CONCAT('join_',title),title)  FROM const_looking", get_param("looking", '')),
            'language_value' => $g['lang_loaded'],
            'orientation_class' => ''
        );

        $isParseBlockIAm = false;
        if (UserFields::isActive('orientation')) {
            $vars['orientation_class'] = 'orientation_bl';
            $default = 0;
            $selectedOrientation = 0;
            if (!$isIos) {
                $default = DB::result('SELECT `id` FROM `const_orientation` WHERE `default` = 1', 0, 1);
                $selectedOrientation = get_param("orientation", $default);
                //if (!$default) {
                    //$default = '';
                //}
            }
            $vars['orientation_options'] = '';
            if (!$default){
                $lPleaseChoose = l('please_choose');
                if ($optionTmplName == 'edge') {
                    $lPleaseChoose = l('i_am');
                }
                $vars['orientation_options'] = '<option value="0" selected="selected">' . $lPleaseChoose . '</option>';
            }
            $vars['orientation_options'] .= DB::db_options("SELECT id, title FROM const_orientation ORDER BY id ASC", $selectedOrientation);
            $isParseBlockIAm = true;
        }

        if ($optionTmplName == 'edge') {
            if(TemplateEdge::isModeLms()) {
                LMS::joinPrepareProfileTypes($vars, $isIos);
                $isParseBlockIAm = true;
            }
        }

        //$defaultCountry = '';

//        if (Common::isOptionActive('register_location_by_ip', 'template_options')) {
//            $geoInfo = IP::geoInfoCity();
//            $defaultCountry = $geoInfo['country_id'];
//        }

        $defaultCountry = 0;
        $defaultState = 0;
        $defaultCity = 0;
        //$geoInfo = IP::geoInfoCity();
        $geoInfo = getDemoCapitalCountry();
        if ($geoInfo) {
            $selectedCountry = $geoInfo['country_id'];
            $selectedState = $geoInfo['state_id'];
            $selectedCity = $geoInfo['city_id'];
        }

        if (!$isIos) {
            $defaultCountry = $selectedCountry;
            $defaultState = $selectedState;
            if ($cmd != 'fb_login') {
                $defaultState = get_param('state', $defaultState);
            }
            $defaultCity = get_param('city', $selectedCity);
        }

        //}
        //$countrySelected = get_param('country', $defaultCountry);

        $isSetDefaultJoin = false;
        if ($optionTmplName == 'impact_mobile' && $isUploadPageAjax) {//Set default locations retry join frm
            $selectedJoinCountry = get_cookie('impact_mobile_join_country_default', true);
            if ($selectedJoinCountry != '') {
                $isSetDefaultJoin = true;
                $selectedCountry = $selectedJoinCountry;
                $defaultCountry = $selectedJoinCountry;

                $selectedState = get_cookie('impact_mobile_join_state_default', true);
                $defaultState = $selectedState;

                $defaultCity = get_cookie('impact_mobile_join_city_default', true);

                $isIos = true;
            }
        }

        if($html->varexists('country_options')) {
            Common::setPleaseChoose(l('choose_a_country'));
            $vars['country_options'] = Common::listCountries($defaultCountry, true, false, $isIos);
        }

        //if (isset($geoInfo)) {
            /*$stateSelected = $geoInfo['state_id'];
            if ($cmd != 'fb_login') {
                $stateSelected = get_param('state', $geoInfo['state_id']);
            }*/
        if($html->varexists('state_options')) {
            if(($isIos && !$defaultCountry) || ($isSetDefaultJoin && !$defaultState && !$defaultCountry)){
                $vars['state_options'] = "<option value=\"0\" selected=\"selected\">" . l('choose_a_state') . "</option>";
            } else {
                Common::setPleaseChoose(l('choose_a_state'));
                $vars['state_options'] = Common::listStates($selectedCountry, $defaultState, false, $isIos);
            }
        }

        if($html->varexists('city_options')) {
            //$citySelected = get_param('city', $geoInfo['city_id']);
            if(($isIos && !$defaultState) || ($isSetDefaultJoin && !$defaultState && !$defaultCity)){
                $vars['city_options'] = "<option value=\"0\" selected=\"selected\">" . l('choose_a_city') . "</option>";
            } else {
                Common::setPleaseChoose(l('choose_a_city'));
                $vars['city_options'] = Common::listCities($selectedState, $defaultCity, false, $isIos);
            }
        }

        //}

        // add state and city options
        // choose location by ip

        $plainVars = array(
            'join_handle',
            'join_password',
            'verify_password',
            'email',
            'verify_email',
        );
        foreach ($plainVars as $var) {
            $vars[$var] = get_param($var, '');
        }

        $vars['username_length'] = $g['options']['username_length'];
        $vars['username_length_min'] = $g['options']['username_length_min'];
        $vars['max_min_length_username'] = sprintf(toJsL('max_min_length_username'), $g['options']['username_length_min'], $g['options']['username_length']);

        $vars['password_length_min'] = $g['options']['password_length_min'];
        $vars['password_length_max'] = $g['options']['password_length_max'];
        $vars['max_min_length_password'] = sprintf(toJsL('max_min_length_password'), $g['options']['password_length_min'], $g['options']['password_length_max']);

        $vars['mail_length_max'] = $g['options']['mail_length_max'];

        htmlSetVars($html, $vars);

        if (Common::isOptionActiveTemplate('join_location_allow_disabled')) {
            $isActiveLocation = Common::isOptionActive('location_enabled', "{$optionTmplName}_join_page_settings");
            if (!$isActiveLocation) {
                $geoDefaultInfo = IP::geoInfoCityDefault();
                $locationInfo = array(
                    'country_id' => $geoDefaultInfo['country_id'],
                    'state_id'   => $geoDefaultInfo['state_id'],
                    'city_id' => $geoDefaultInfo['city_id'],
                );
                $html->assign('', $locationInfo);
            }
            $html->subcond($isActiveLocation, 'join_location_show', 'join_location_default');
        }

        if (Common::isOptionActiveTemplate('join_birthday_disabled')) {
            $isDisabledBirthday = User::isDisabledBirthday();
            if (!$isDisabledBirthday && $html->blockExists('join_birthday')) {
                $html->parse('join_birthday', false);
            }
            $html->setvar('birthday_disabled', intval($isDisabledBirthday));
        }

        if (UserFields::isActive('orientation')) {
            $html->parse('field_orientation', false);
            $html->parse('field_orientation_js', false);
        }

        if ($isCustomRegister && $p == 'join_facebook.php') {
            if (UserFields::isActiveSexuality()) {
                $default = DB::result('SELECT `id` FROM `var_sexuality` WHERE `default` = 1', 0, 1);

                $options = '';

                if (!$default) {
                    $options = '<option value="0" selected="selected">' . l('please_choose') . '</option>';
                }
                $options .= DB::db_options('SELECT id, title FROM var_sexuality ORDER BY id ASC', $default);
                $html->setvar('options_sexuality', $options);
                $html->parse('field_sexuality', false);
                if ($isParseBlockIAm) {
                    $html->parse('field_i_am_separate', false);
                }
                $isParseBlockIAm = true;
            }
            if ($isParseBlockIAm) {
                $html->parse('field_i_am', false);
            }
        }

        if ($this->message != '') {
            $html->parse('join_message');
        }

        /* Impact */
        if ($isCustomRegister && $p != 'join_facebook.php') {
            $defaultOrientation = User::getDefaultOrientation();
            $orientation = get_param('orientation', $defaultOrientation);
            set_session("j_orientation", $orientation);
            if (UserFields::isActiveSexuality()) {
                set_session("j_sexuality", get_param('p_sexuality'));
            }
        }
        /* Impact */

        parent::parseBlock($html);
    }

}