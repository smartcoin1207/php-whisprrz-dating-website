<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

class CProfileNscCouple extends UserFields  {

    function action() {
        global $g;
        global $g_user;

        $nsc_couple_id = $g_user['nsc_couple_id'];
		$nsc_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . $nsc_couple_id, 1);

        $cmd = get_param('cmd', '');

        if ($cmd == 'update') {
            $this->message = '';

            $mail = trim(get_param('email', ''));
            $orientation = (int) get_param('orientation', $g_user['orientation']);
			$relation = get_param("p_looking_relation");

            $name = trim(get_param('user_name', $g_user['name']));
			/*
            if (Common::isOptionActive('allow_users_to_change_their_logins')) {
                $this->message .= User::validateName($name);
            } else {
                $name = $g_user['name'];
            }
			*/
            $month = (int)get_param('month', 1);
            $day   = (int)get_param('day', 1);
            $year  = (int)get_param('year', 1980);

            $country = get_param('country', '');
            $state   = get_param('state', '');
            $city    = get_param('city', 'Partner');
			$nsc_couple_type    = get_param('nsc_couple_type', '');

            $this->message .= User::validate('email,birthday,location');
            $this->verification('pr_check');
			//$nsc_couple_id = guid()+1;
            $nsc_couple_id = $g_user['nsc_couple_id'];
			
			//if ($this->message == '') {
                $h = zodiac($year . '-' . $month .'-' . $day);				
                DB::execute("
						UPDATE user SET
                        name = " . to_sql($name, 'Text') . ",
                        relation = " . to_sql($relation, 'Text') . ",
						mail=" . to_sql($mail, 'Text') . ",
						country_id=" . to_sql($country, 'Number') . ",
						state_id=" . to_sql($state, 'Number') . ",
						city_id=" . to_sql($city, 'Number') . ",
						country=" . to_sql(Common::getLocationTitle('country', $country), 'Text') . ",
						state=" . to_sql(Common::getLocationTitle('state', $state), 'Text') . ",
						city=" . to_sql(Common::getLocationTitle('city', $city), 'Text') . ",
						birth='" . $year . "-" . $month . "-" . $day . "',
						horoscope='" . $h . "',
						partner_type='" . $nsc_couple_type . "'
						WHERE user_id=" . $nsc_couple_id . ";
				");

                User::setOrientation($nsc_couple_id);
                if (self::isActive('orientation') && !Common::isOptionActive('your_orientation')) {
                    User::update(array('p_orientation' => get_checks_param('p_orientation')));
                }

                $this->updateTextsApprovalNscCouple('profile', $nsc_couple_id);

				$nsc_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . $nsc_couple_id, 1);

                DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . $nsc_couple_id . "");
                $g_user = DB::fetch_row();
                //g_user_full();

                /* if($mail!=$g_user['mail'])
                  {
                  user_change_email($g_user['user_id'], $mail);
                  redirect("email_not_confirmed.php");
                  } */
				
				redirect();
            //}
        } elseif ($cmd == "couple_request") {
            $this->message = '';

            $couple_mail = get_param("couple_mail", "");
            $couple_id = DB::result("SELECT user_id FROM user WHERE name LIKE " . to_sql($couple_mail) . "");
            if ($couple_id == $nsc_couple_id) {
                $this->message = l('cannot_self');
                User::$error['couple'] = true;
            }elseif ($couple_id > 0) {
                $request_to = DB::result("SELECT user_id FROM user WHERE couple_to = '" . $nsc_couple_id . "' AND  user_id = " . $couple_id);
                if ($request_to > 0) {
                    /*Old couples*/
                    DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` = 0  WHERE `couple_id` = " . to_sql($nsc_couple_id, 'Number'));
                    $coupleToOld = User::getInfoBasic($request_to, 'couple_id');
                    if ($coupleToOld > 0) {
                        DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` = 0  WHERE `user_id` = " . to_sql($coupleToOld, 'Number'));
                    }
                    DB::execute("UPDATE user SET couple='Y',couple_id='" . $nsc_couple_row['couple_from'] . "',couple_from = 0 WHERE user_id=" . $nsc_couple_id . "");
                    DB::execute("UPDATE user SET couple='Y',couple_id='" . $nsc_couple_id . "',couple_to = 0 WHERE user_id=" . $nsc_couple_row['couple_from'] . "");
                    DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
                    // $g_user = DB::fetch_row();
                    g_user_full();
                } else {
                    /*Old couples from*/
                    $coupleToOld = User::getInfoBasic($couple_id, 'couple_from');
                    if ($coupleToOld > 0)
                        DB::execute("UPDATE `user` SET `couple_to` = 0 WHERE `user_id` = " . to_sql($coupleToOld, 'Number'));
                    DB::execute("UPDATE `user` SET `couple_from` = " . to_sql($nsc_couple_id, 'Number') . " WHERE `user_id` = " . to_sql($couple_id, 'Number'));
                    DB::execute("UPDATE `user` SET `couple_to` = " . to_sql($couple_id, 'Number') . " WHERE `user_id` = " . to_sql($nsc_couple_id, 'Number'));
                }
            }
            else {
                $this->message = l('user_not_exists');
                User::$error['couple'] = true;
            }
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            // $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_request_cancel") {

            DB::execute("UPDATE user SET couple_from = 0 WHERE user_id=" . $nsc_couple_row['couple_to'] . "");
            DB::execute("UPDATE user SET  couple_to = 0  WHERE user_id=" . $nsc_couple_id . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            // $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_query_cancel") {
            DB::execute("UPDATE user SET  couple_to = 0  WHERE user_id=" . $nsc_couple_row['couple_from'] . "");
            DB::execute("UPDATE user SET couple_from = 0 WHERE user_id=" . $nsc_couple_id . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            // $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_cancel") {

            DB::execute("UPDATE user SET couple = 'N',couple_id =0  WHERE user_id=" . $nsc_couple_row['couple_id'] . "");
            DB::execute("UPDATE user SET couple = 'N',couple_id=0  WHERE user_id=" . $nsc_couple_id . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            // $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_approve") {
            /*Old couples request*/
            if ($nsc_couple_row['couple_to']) {
                DB::execute('UPDATE `user` SET `couple_from` = 0 WHERE `user_id` = ' . to_sql($nsc_couple_row['couple_to'], 'Number'));
                DB::execute('UPDATE `user` SET `couple_to` = 0  WHERE `user_id` = ' . to_sql($nsc_couple_id, 'Number'));
            }
            /*Old couples request*/
            /*Old couples*/
            DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` =0  WHERE `couple_id` = " . to_sql($nsc_couple_id, 'Number'));
            $coupleToOld = User::getInfoBasic($nsc_couple_row['couple_from'], 'couple_id');
            if ($coupleToOld > 0) {
                DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` = 0  WHERE `user_id` = " . to_sql($coupleToOld, 'Number'));
            }
            DB::execute("UPDATE user SET couple='Y',couple_id='" . $nsc_couple_row['couple_from'] . "',couple_from = 0 WHERE user_id=" . $nsc_couple_id . "");
            DB::execute("UPDATE user SET couple='Y',couple_id='" . $nsc_couple_id . "',couple_to = 0 WHERE user_id=" . $nsc_couple_row['couple_from'] . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            // $g_user = DB::fetch_row();
            g_user_full();
        }
        //elseif ($cmd == "confirmed")
        //{
        //$this->message = l("Email confirmed")."<br><br>";
        //}
    }

    function parseBlock(&$html) {
        global $g;
        global $l;
        global $g_user;

        $cmd = get_param('cmd', '');
        if ($cmd == "confirmed"){
            $html->parse('alert_email_confirmed');
        }

        if (isset($this->message))
            $html->setvar('update_message', $this->message);

        //$this->setValueTexts();
        if (!Common::isOptionActive('allow_users_to_change_profile_type')) {
            $this->setBanCustomFields(array('orientation'));
            $orientation = DB::result("SELECT title FROM const_orientation WHERE id = " . to_sql($g_user['orientation']));
            $html->setvar('orientation_value', l($orientation));
            $html->parse('orientation_edit_off');
        }
        $this->formatValue = 'entities';
		$nsc_couple_id = $g_user['nsc_couple_id'];	
		if(!isset($nsc_couple_id)) $nsc_couple_id=0;		
        $this->parseFieldsAll($html, 'profile', false,$nsc_couple_id);

        $html->setvar("name", $g_user['name']);
        $html->setvar("mail", get_param("mail", $g_user['mail']));

		//start-nnsscc-diamond	
		//$html->setvar("nsc_couple_name", "test");		
		//$nsc_couple_id = $g_user['user_id']+1;			
		$nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . $nsc_couple_id, 1);
		$html->setvar("nsc_couple_user_id", $nsc_new_couple_row['user_id']);
		$html->setvar("nsc_couple_user_profile_link", User::url($nsc_new_couple_row['user_id'], $nsc_new_couple_row));
		$html->setvar("nsc_couple_name", $nsc_new_couple_row['name']);	
		$html->setvar("nsc_couple_mail", $nsc_new_couple_row['mail']);	
		$html->setvar("nsc_couple_type", $nsc_new_couple_row['partner_type']);			
		$html->setvar("nsc_couple_name_short", User::nameShort($nsc_new_couple_row['name']));
		$html->setvar("nsc_couple_name_one_letter", User::nameOneLetterFull($nsc_new_couple_row['name']));
		$html->setvar("nsc_couple_name_one_letter_short", User::nameOneLetterShort($nsc_new_couple_row['name']));
		$html->parse('nsc_couple_name_edit_on',true);
		$html->parse('nsc_couple_name_error',true);	
		$html->parse('nsc_couple_const',true);
		
		$d = explode('-', $nsc_new_couple_row['birth']);
		$html->setvar('month_options', h_options(Common::listMonths(), get_param('month', (int) $d[1])));
		$html->setvar('day_options', n_options(1, 31, get_param('day', (int) $d[2])));
		$html->setvar('year_options', n_options(date("Y") - $g['options']['users_age_max'], date("Y") - $g['options']['users_age'] + 1, get_param("year", (int) $d[0])));
		
		$country = get_param('country', $nsc_new_couple_row['country_id']);
		$state   = get_param('state', $nsc_new_couple_row['state_id']);
        $city    = get_param('city', $nsc_new_couple_row['city_id']);
		$html->setvar('country_options', Common::listCountries($country));
        $html->setvar('state_options', Common::listStates($country, $state));	
		$html->setvar('city_options', Common::listCities($state, $city));		
		
		$html->parse('nsc_couple_p_orientations',true);	
		$html->parse('nsc_couple_p_orientation',true);		
		// $sel00 = "selected";		$sel01 = "";		$sel02 = "";
		// $sel03 = "";		$sel04 = "";		$sel05 = "";
		// $sel06 = "";		$sel07 = "";		$sel08 = "";
		// $sel09 = "";		$sel10 = "";		$sel11 = "";
		// $sel12 = "";		$sel13 = "";		$sel14 = "";
		// $sel15 = "";		$sel16 = "";		$sel17 = "";
		// $sel18 = "";		$sel19 = "";		$sel20 = "";
		// $sel21 = "";		$sel22 = "";		$sel23 = "";
		// $sel24 = "";		$sel25 = "";		$sel26 = "";
		// $sel27 = "";		$sel28 = "";		
		
		// if($nsc_new_couple_row['partner_type']==="Man") {
		// 	$sel00 = "";		$sel01 = "selected";		$sel02 = "";
		// 	$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";
		// 	$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";
		// 	$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";
		// 	$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";
		// 	$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Woman"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "selected";
		// 	$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";
		// 	$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";
		// 	$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";
		// 	$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";
		// 	$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Better half"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";
		// 	$sel03 = "selected";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";
		// 	$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";
		// 	$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";
		// 	$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";
		// 	$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Worst half"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";
		// 	$sel03 = "";		$sel04 = "selected";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";
		// 	$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";
		// 	$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";
		// 	$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";
		// 	$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="1st half"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";
		// 	$sel03 = "";		$sel04 = "";		$sel05 = "selected";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";
		// 	$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";
		// 	$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";
		// 	$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";
		// 	$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="2nd half"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "selected";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Significant Other"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "selected";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Boyfriend"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "selected";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Girlfriend"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "selected";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Husband"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "selected";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Wife"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "selected";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Domestic partner"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "selected";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="live in lover"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "selected";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Mate"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "selected";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Soulmate"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "selected";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Spouse"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "selected";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Darling"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "selected";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Fiancé"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "selected";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="First and last"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "selected";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Flame"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "selected";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Honey"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "selected";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="love"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "selected";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Lover"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "selected";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Number one"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "selected";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="One and only"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "selected";		$sel26 = "";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Sweetheart"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "selected";			$sel27 = "";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="Tootsie"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "selected";		$sel28 = "";		
		// }else if($nsc_new_couple_row['partner_type']==="True love"){
		// 	$sel00 = "";		$sel01 = "";		$sel02 = "";			$sel03 = "";		$sel04 = "";		$sel05 = "";
		// 	$sel06 = "";		$sel07 = "";		$sel08 = "";			$sel09 = "";		$sel10 = "";		$sel11 = "";
		// 	$sel12 = "";		$sel13 = "";		$sel14 = "";			$sel15 = "";		$sel16 = "";		$sel17 = "";
		// 	$sel18 = "";		$sel19 = "";		$sel20 = "";			$sel21 = "";		$sel22 = "";		$sel23 = "";
		// 	$sel24 = "";		$sel25 = "";		$sel26 = "";			$sel27 = "";		$sel28 = "selected";		
		// }
		// $nsc_couple_type_option = "<option value='".l('partner')."' ".$sel00.">".l('partner')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Man')."' ".$sel01.">".l('Man')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Woman')."' ".$sel02.">".l('Woman')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Better_half')."' ".$sel03.">".l('Better_half')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Worst_half')."' ".$sel04.">".l('Worst_half')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('1st_half')."' ".$sel05.">".l('1st_half')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('2nd_half')."' ".$sel06.">".l('2nd_half')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Significant_other')."' ".$sel07.">".l('significant_other')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Boyfriend')."' ".$sel08.">".l('Boyfriend')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Girlfriend')."' ".$sel09.">".l('Girlfriend')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Husband')."' ".$sel10.">".l('Husband')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Wife')."' ".$sel11.">".l('Wife')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Domestic_partner')."' ".$sel12.">".l('Domestic_partner')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('live_in_lover')."' ".$sel13.">".l('live_in_lover')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Mate')."' ".$sel14.">".l('Mate')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Soulmate')."' ".$sel15.">".l('Soulmate')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Spouse')."' ".$sel16.">".l('Spouse')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Darling')."' ".$sel17.">".l('Darling')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Fiancé')."' ".$sel18.">".l('Fiancé')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('First_and_last')."' ".$sel19.">".l('First_and_last')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Flame')."' ".$sel20.">".l('Flame')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Honey')."' ".$sel21.">".l('Honey')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('love')."' ".$sel22.">".l('love')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('lover')."' ".$sel23.">".l('lover')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Number_one')."' ".$sel24.">".l('Number_one')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('One_and_only')."' ".$sel25.">".l('One_and_only')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Sweetheart')."' ".$sel26.">".l('Sweetheart')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('Tootsie')."' ".$sel27.">".l('Tootsie')."</option>";
		// $nsc_couple_type_option = $nsc_couple_type_option."<option value='".l('True_love')."' ".$sel28.">".l('True_love')."</option>";
		


		$nick_sql = "SELECT * FROM var_nickname";
			$nicknames = DB::rows($nick_sql);
			$nickname_current = "";

			$nsc_type_option = "";
			foreach ($nicknames as $key => $nickname) {
				$checked = "unselected";
				if($nsc_new_couple_row['partner_type'] == $nickname['id']) {
					$checked = "selected";
					$nickname_current = $nickname['title'];
				}	

				$html->setvar('nickname_checked', $checked);
				$html->setvar('nickname_title', $nickname['title']);
				$html->setvar('nickname_id', $nickname['id']);
				$html->parse('nickname', true);

			}

			$html->setvar('nickname_current', $nickname_current);
			

		$html->parse("nsc_couple_profile_type",true); //nnsscc_diamond-20200325
		$html->clean('nickname');

		if($g_user['orientation']==="5"){
			$html->parse("nsc_couple_profile",true); //nnsscc_diamond
			$html->parse("nsc_couple_profile_photo", true);
			//$nsc_couple_id = $g_user['user_id']+1;
			$nsc_couple_id = $g_user['nsc_couple_id'];
			$nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . $nsc_couple_id, 1);
			$html->setvar("nsc_couple_user_id", $nsc_new_couple_row['user_id']);
			$html->setvar("nsc_couple_user_profile_link", User::url($nsc_new_couple_row['user_id'], $nsc_new_couple_row));
			$html->setvar("nsc_couple_name", $nsc_new_couple_row['name']);	
			$html->setvar("nsc_couple_mail", $nsc_new_couple_row['mail']);				
			$html->setvar("nsc_couple_name_short", User::nameShort($nsc_new_couple_row['name']));
			$html->setvar("nsc_couple_name_one_letter", User::nameOneLetterFull($nsc_new_couple_row['name']));
			$html->setvar("nsc_couple_name_one_letter_short", User::nameOneLetterShort($nsc_new_couple_row['name']));
			
		}
		//end-nnsscc-diamond
		
        foreach (User::$error as $key => $item) {
            $html->parse($key . '_error', false);
        }
        if ($nsc_new_couple_row['couple_from'] > 0) {
            $html->setvar("couple_name", DB::result("SELECT name FROM user WHERE user_id=" . $nsc_new_couple_row['couple_from'] . ""));
            $html->parse("couple_approve", true);
        }
        if ($nsc_new_couple_row['couple_to'] > 0) {
            $html->setvar("couple_name", DB::result("SELECT name FROM user WHERE user_id=" . $nsc_new_couple_row['couple_to'] . ""));
            $html->parse("couple_request_cancel", true);
        }

        if ($nsc_new_couple_row['couple'] == 'Y' and $nsc_new_couple_row['couple_id'] > 0) {
            $html->setvar("couple_name", DB::result("SELECT name FROM user WHERE user_id=" . $nsc_new_couple_row['couple_id'] . ""));
            $html->parse("couple_cancel", true);
        }
        if ($nsc_new_couple_row['couple_to'] == 0
            //&& $g_user['couple_from'] == 0
            && $nsc_new_couple_row['couple_id'] == 0
            && $nsc_new_couple_row['couple'] != 'Y')
            $html->parse("couple_request", true);


        if (Common::isOptionActive('couples')) {
            $html->parse('couple');
        }
        if (Common::isOptionActive('allow_users_to_change_their_logins')) {
            $html->parse('name_edit_on');
            $html->setvar('username_length', $g['options']['username_length']);
            $html->setvar('username_length_min', $g['options']['username_length_min']);
            $html->parse('name_edit_on_js');
        } else {
            $html->parse('name_edit_off');
        }
        if (Common::isOptionActive('partner_settings', 'options') || Common::isOptionActive('personal_settings', 'options')) {
            if (Common::isOptionActive('partner_settings', 'options')) {
                $html->parse('yes_nsc_partner');
            }
            if (Common::isOptionActive('personal_settings', 'options')) {
                $html->parse('yes_nsc_personal');
            }
            $html->parse('yes_nsc_settings');
        }

        $html->setvar('mail_length_max', Common::getOption('mail_length_max'));

// PAYMENTS
        if (!Common::isOptionActive('free_site')) {
            if ($nsc_new_couple_row['gold_days'] > 0 && $nsc_new_couple_row['type'] != 'none') {
                $lType = l($nsc_new_couple_row['type']);
                $paidDays = $nsc_new_couple_row['gold_days'];
                $vars = array(
                    'paid_type' => $lType,
                    'payment_type' => $lType,
                    'paid_days' => $paidDays,
                    'gold_days' => $paidDays,
                    'days_left' => l('days_left')
                );
                $html->setvar('payment_paid', lSetVars(l('payment_module'), $vars));
                $html->parse('payment_paid');
            } elseif ($nsc_new_couple_row['free_access']) {
                $type = DB::result('SELECT free FROM const_orientation WHERE id=' . $nsc_new_couple_row['orientation']);
                $html->setvar('payment_free_access', l($type));
                $html->parse('payment_free_access');
            } else {
                $vars = array('url' => $g['path']['url_main'] . 'upgrade.php');
                $html->setvar('payment_free', lSetVars(l('free_upgrade_module'), $vars));
                $html->parse('payment_free');
            }

            $html->parse('paid_site');
        }
// PAYMENTS
        if (self::isActive('orientation')) {
            if (Common::isOptionActive('your_orientation')) {
                if (guser('p_orientation')) {
                    $name = User::getTitleOrientationLookingFor(array('p_orientation' => guser('p_orientation')));
                    $html->setvar('name', $name);
                    $html->parse('looking_your_orientation');
                }
            } else {
                $this->parseChecks($html, 'p_orientation', $g['user_var']['orientation'], 2, 0, false, 'p_orientation', true);
            }
        }

        parent::parseBlock($html);
    }

}

g_user_full();

$page = new CProfileNscCouple("", $g['tmpl']['dir_tmpl_main'] . "profile_nsc_couple.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('profile_nsc_couple');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");
?>
