<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "public";
include("./_include/core/main_start.php");

class CJoin2 extends UserFields//CHtmlBlock
{
	function action()
	{
        global $g;
        global $g_user;


        $isAjaxRequest = get_param('ajax');
        $cmd = get_param('cmd');
        if ($isAjaxRequest) {
            $responseData = false;
            if ($cmd == 'photo_upload') {
                $file = get_param('file');
                $responseData = CProfilePhoto::validate($file);
                if (!$responseData) {
                    $file = $_FILES[$file]['tmp_name'];
                    $name = 'tmp_join_impact_' . time() . '_' . mt_rand() . '_';
                    $saveFile = $g['path']['dir_files'] . 'temp/' . $name;
                    $im = new Image();
                    if ($im->loadImage($file)) {
                        $im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
                        $im->saveImage($saveFile . 'm.jpg', $g['image']['quality']);
                        @chmod($saveFile . 'm.jpg', 0777);
                        @copy($file, $saveFile . 'src.jpg');
                        @chmod($saveFile . 'src.jpg', 0777);
                        $responseData = $name;
                    }
                }
            } elseif ($cmd == 'check_captcha') {
                $responseData = check_captcha_mod(get_param('captcha'), '', false, false, '', '');
                if ($responseData) {
                    foreach ($this->gFields as $key => $field) {
                        if (in_array($field['type'], array('text', 'textarea'))
                                && ($field['group'] == 0 || $field['group'] == 3)) {
                            if (!isset($field['join_status'])) {
                                $field['join_status'] = 1;
                            }
                            if ($field['join_status']) {
                                set_session("j_{$key}", get_param($key));
                            }
                        }
                    }
                    $fileName = get_param('photo');
                    $uid = User::add();
                    if (!$uid) {
                        $responseData = $this->getResponseData('exists_email', Common::pageUrl('join'));
                    } else {
                        $g_user['user_id'] = $uid;
                        /*if (self::isActiveSexuality()) {
                            $userSearchFilters = array('p_sexuality' =>
                                array(
                                    'field' => 'sexuality',
                                    'value' => array(get_session('j_f_sexuality')),
                                )
                            );
                            User::updateParamsFilterUserInfoForData('user_search_filters', $userSearchFilters);
                        }*/
                        if ($fileName) {
                            $photo = $g['path']['dir_files'] . 'temp/' . $fileName . 'src.jpg';
                            uploadphoto($g_user['user_id'], '', '', intval(Common::isOptionActive('photo_approval')), $photo);
                        }
                        if (Common::isOptionActive('manual_user_approval')) {
                            $responseData = $this->getResponseData('wait_approval', Common::pageUrl('login'));
                        } else {
                            $responseData = $this->getResponseData('redirect', Common::getHomePage());
                        }
                    }
                }else{
                    $responseData = $this->getResponseData('error_captcha', '');
                }
            }
            die(getResponseDataAjax($responseData));
        } elseif ($cmd == 'action'){
			global $g;
			$this->message = "";
            // $this->message .= User::validateLocation(get_session('j_country'), get_param('state', ''), get_param('city', ''));
            $this->verification('texts');

			$paf = get_param('partner_age_from', '');
			$pat = get_param('partner_age_to', '');
			if ($paf > $pat) {
				$this->message .= l('partner_age_incorect') . '<br>';
			}

			if ($this->message == "")
			{
                $orientation = get_session("j_orientation");
                // var_dump($orientation); die();
				//start-nnsscc-diamond
				$couple_profile = get_session("j_couple_profile");
				if($couple_profile==1){   //partner page = second user page
					// set_session("j_state_couple", get_param('state', ''));
					// set_session("j_city_couple", get_param("city", ''));
					set_session("j_nsc_couple_type", get_param('nsc_couple_type', ''));
					// set_session("j_nsc_couple_year", get_param('year', ''));
					// set_session("j_nsc_couple_month", get_param('month', ''));
					// set_session("j_nsc_couple_day", get_param('day', ''));					
					set_session("j_partner_age_from_couple", $paf);
					set_session("j_partner_age_to_couple", $pat);
					set_session("j_relation_couple", get_param("relation", ""));

					foreach ($g['user_var'] as $k => $v)
					{
						if ((substr($k, 0, 2) != "p_") && ($v['type'] != 'const'))
						{
							set_session("j_couple_" . $k, get_param($k, ""));
						}
					}				
					set_session("j_couple_orientation",0);
					redirect('join3.php');
				}
				else if($orientation==5){  //first user page
					set_session("j_couple_profile", 1);
					set_session("j_couple_type", get_param('nsc_couple_type', ''));
					// set_session("j_year", get_param('year', ''));
					// set_session("j_month", get_param('month', ''));
					// set_session("j_day", get_param('day', ''));
					// set_session("j_state", get_param('state', ''));
					// set_session("j_city", get_param("city", ''));
					set_session("j_partner_age_from_couple", $paf);
					set_session("j_partner_age_to_couple", $pat);
					set_session("j_relation_couple", get_param("relation", ""));

					foreach ($g['user_var'] as $k => $v)
					{
						if ((substr($k, 0, 2) != "p_") && ($v['type'] != 'const'))
						{
							set_session("j_" . $k, get_param($k, ""));
						}
					}
					redirect('join2.php');
				}else{
					set_session("j_couple_type", get_param('nsc_couple_type', ''));
					// set_session("j_year", get_param('year', ''));
					// set_session("j_month", get_param('month', ''));
					// set_session("j_day", get_param('day', ''));
					// set_session("j_state", get_param('state', ''));
					// set_session("j_city", get_param("city", ''));
					set_session("j_partner_age_from", $paf);
					set_session("j_partner_age_to", $pat);
					set_session("j_relation", get_param("relation", ""));

					foreach ($g['user_var'] as $k => $v)
					{
						if ((substr($k, 0, 2) != "p_") && ($v['type'] != 'const'))
						{
							set_session("j_" . $k, get_param($k, ""));
						}
					}
					redirect('join3.php');
				}
				//end-nnsscc-diamond
			}
		}
	}

    function getResponseData($name, $data) {
        return "<span class='" . $name . "'>" . strip_tags($data) . '</span>';
    }

    function parseLastStep(&$html)
	{
        $html->setvar('user_name', get_session('j_name'));
        $cityId = get_session('j_city');
        $html->setvar('user_city', l(Common::getLocationTitle('city', $cityId)));
        $month = get_session('j_month');
        $day = get_session('j_day');
        $year = get_session('j_year');
        $html->setvar('user_age', User::getAge($year, $month, $day));
        Common::parseCaptcha($html);
        $html->parse('photo', false);
				   
    }

	function parseBlock(&$html)
	{
        global $g;
        //start-nnsscc-diamond
		$isIos = Common::isAppIos();
		$formatDateMonths = 'F';
        $optionFormatDateMonths = Common::getOption('format_date_months_join', 'template_options');
        if ($optionFormatDateMonths) {
            $formatDateMonths = $optionFormatDateMonths;
        }

        $defaultBirthday = Common::getDefaultBirthday();
        $defaultDay = $defaultBirthday['day'];
        $defaultMonth = $defaultBirthday['month'];
        $defaultYear = $defaultBirthday['year'];
        if ($isIos && Common::getTmplName() != 'edge') {
            $defaultDay = 0;
            $defaultMonth = 0;
            $defaultYear = 0;
        }
		$html->setvar('month_options', h_options(Common::plListMonths($formatDateMonths, $isIos), get_param('month', $defaultMonth)));
        $html->setvar('day_options', n_options(1, 31, get_param('day', $defaultDay), $isIos));
        $html->setvar('year_options', n_options(date('Y') - $g['options']['users_age_max'], date("Y") - $g['options']['users_age'], get_param("year", $defaultYear), $isIos));

        $nick_sql = "SELECT * FROM var_nickname";
        $nicknames = DB::rows($nick_sql);
        $nickname_current = "Select Partner Type";

        $nsc_type_option = "";
        foreach ($nicknames as $key => $nickname) {
            $checked = "unselected";
            $html->setvar('nickname_checked', $checked);

            $html->setvar('nickname_title', $nickname['title']);
            $html->setvar('nickname_id', $nickname['id']);
            $html->parse('nickname', true);

        }

        $html->setvar('nickname_current', $nickname_current);

        $first_nickname = get_session('j_couple_type');
        // echo $first_nickname; die();

        if($first_nickname) {
            $html->setvar('nsc_nickname_label', l('2nd_half_nickname'));
        } else {
            $html->setvar('nsc_nickname_label', l('primary_nickname'));

        }
        $html->parse("nsc_couple_profile_type",true); //nnsscc_diamond-20200325
        $html->clean('nickname');
        
		// $nick_sql = "SELECT * FROM var_nickname";
        // $nicknames = DB::rows($nick_sql);
        // $nsc_couple_type_option = "";

        // foreach ($nicknames as $key => $nickname) {
        //     $nsc_couple_type_option = $nsc_couple_type_option."<option value='".$nickname['id']."'>".$nickname['title']."</option>";
        // }

		// $html->setvar('nsc_couple_type_option',$nsc_couple_type_option);



         //end-nnsscc-diamond
		$isCustomRegister = Common::isOptionActive('custom_user_registration', 'template_options');
        if ($isCustomRegister) {
            $html->setvar('usersinfo_pages_per_join', Common::getOption('usersinfo_pages_per_join', 'template_options'));

            $html->setvar('header_url_logo', Common::getUrlLogo());
            $html->setvar('url_logo', Common::getHomePage());

            $isOneStepRegistration = Common::getOption('join_impact') == 'one_foto';
            $isJoinWithPhotoOnly = Common::isOptionActive('join_with_photo_only');
            $html->setvar('join_with_photo_only', intval($isJoinWithPhotoOnly));
            $numberPhotoLikes = intval(Common::getOption('join_number_photo_likes'));
            if ($numberPhotoLikes <= 0) {
                $numberPhotoLikes = 1;
            }
            $html->setvar('number_photo_likes', $numberPhotoLikes);
            $html->setvar('slogan_2', lSetVars('teach_us_your_type_like_people', array('number' => $numberPhotoLikes)), 'toJsL');

            $this->parseLastStep($html);
            $filedsData = array();
            if (!$isOneStepRegistration) {
                foreach ($this->gFields as $key => $field) {
                    if (!isset($field['join_status'])) {
                        $field['join_status'] = 1;
                    }
                    if (in_array($field['type'], array('text', 'textarea'))
                            && ($field['group'] == 0 || $field['group'] == 3)) {
                        if ($field['join_status']) {
                            $html->setvar('maxlen', $field['length']);
                            $title = l($field['title']);
                            $html->setvar('field', $title);
                            $html->setvar('name', $key);
                            $lKeyDesc = "field_description_{$key}";
                            $lVal = l($lKeyDesc);
                            if ($lKeyDesc == $lVal){
                                $lVal = '';
                            }
                            $html->setvar('value', $lVal);
                            $clean = $field['type'] == 'text' ? 'textarea' : 'text';
                            $html->parse($field['type'], false);
                            $html->clean($clean);
                            $html->parse('basic');
                        }
                    } else {
                        $data = self::checkFiledQuestion($key);
                        if ($data && $field['join_status'] && isset($field['question_title']) && $field['question_title']) {
                            if (isset($field['answer'])) {
                                $answers = json_decode($field['answer'], true);
                                if ($data['type_field'] == 'checks') {
                                    foreach ($answers as $k => $rows) {
                                        foreach ($rows as $k => $answer) {
                                            if($answer['from'] || $answer['to']) {
                                                $filedsData[$key] = $field;
                                                break(1);
                                            }
                                        }
                                    }
                                } elseif($answers['no'] || $answers['yes']) {
                                    $filedsData[$key] = $field;
                                }
                            }
                        }
                    }
                }
            }
            if ($filedsData) {
                $countFields = count($filedsData);
                $i = $countFields;
                foreach ($filedsData as $key => $field) {
                    if ($i == 1) {
                        $html->parse('question_item_first', false);
                    }
                    $html->setvar('question_item_name', $key);
                    $vars = array('number' => $i--, 'number_all' => $countFields);
                    $html->setvar('question_item_number', lSetVars('number_question', $vars));
                    $html->setvar('question_item_question', l($field['question_title']));
                    $html->parse('question_item', true);
                }
                $html->setvar('slogan_1', lSetVars('answer_questions_to_calculate_your_best_matches', array('number' => $countFields)), 'toJsL');
                $html->parse('question', false);
                $html->parse('show_join_step', false);
                $html->parse('final_step_hide', false);
            } else {


                $html->parse('users_likes_js1', false);
                $html->parse('users_likes1', false);
            }
        }else{
            $this->parseFieldsAll($html, 'join');
        }

        if ($html->varExists('photo_file_size_limit')) {
            $maxFileSize = Common::getOption('photo_size');
            $html->setvar('photo_file_size_limit', mb_to_bytes($maxFileSize));
            $html->setvar('max_photo_file_size_limit', lSetVars('max_file_size', array('size'=>$maxFileSize)));
        }

		if (isset($this->message)) $html->setvar('join_message', $this->message);
		parent::parseBlock($html);
	}
}

$page = new CJoin2("", $g['tmpl']['dir_tmpl_main'] . "join2.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");