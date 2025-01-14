<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23

include("../_include/core/administration_start.php");
require_once("../_include/current/partyhouz/tools.php");

class CForm extends CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action()
	{
		global $g;
        global $g_user;
        global $l;
		
		$cmd = get_param("cmd", "");
        $isAjax = get_param_int('ajax');

		if ($cmd == "add")
		{
			$category_title = trim(get_param('category_title'));
			DB::query("SELECT position FROM partyhouz_category ORDER BY position DESC LIMIT 1");
			$sql = DB::fetch_row();
			if($category_title) DB::execute('INSERT INTO partyhouz_category VALUES(NULL,'. to_sql($category_title).','.to_sql($sql['position']+1).')');
			
			// Start Open_PartyhouZ senior-dev-1019 2024-10-17
			$category_id = DB::insert_id();

			if($category_id)
			{
				$ispartyhouzocial = Common::isOptionActiveTemplate('partyhou_social_enabled');

				$tmplName = Common::getTmplName();
				$tmplName = 'oryx';
				$partyhou_id = get_param('partyhou_id');
				$partyhou_private = intval(get_param('partyhou_private')) ? 1 : 0;
				$partyhou_access = 'P';
				if ($ispartyhouzocial) {
					$partyhou_access = get_param('partyhou_access');
				}
	
				$partyhou_title = get_param('partyhou_title', $category_title);
	
				$timeCurrent = date("Y-m-d H:i:s");

                if($g_user['timezone']!='' && Common::isOptionActive('user_choose_time_zone')){
                    $usersTimeZone=$g_user['timezone'];
                } elseif(Common::getOption('timezone', 'main')) {
                    $usersTimeZone=Common::getOption('timezone', 'main');
                } else {
                    if (function_exists('date_default_timezone_set')) {
                        $usersTimeZone = date_default_timezone_get();
                    } else {
                        $usersTimeZone = 'UTC';
                    }
                }
                $zone = new DateTimeZone($usersTimeZone);
                $dt=DateTime::createFromFormat('Y-m-d H:i:s', $timeCurrent, $zone);
				
				$timeCurrent = date("Y-m-d H:i:s");
				if(Common::getOption('timezone', 'main')){
					$zone = new DateTimeZone(Common::getOption('timezone', 'main'));
				} else {
					$zone = new DateTimeZone(date_default_timezone_get());
				}
				$dt->setTimezone($zone);

				$user_id = $g_user["user_id"];
				$user_mail = $g_user["mail"];
				$user_name = $g_user["name"];
				$lock_code = get_param("lock_code");
				$is_open = get_param("is_open");
				$is_friends = get_param("is_friends");
				$is_group = get_param("is_group");
				$is_lock = get_param("is_lock");
				$cum_couples = get_param("cum_couples");
				$cum_females = get_param("cum_females");
				$cum_males = get_param("cum_males");
				$cum_transgender = get_param("cum_transgender");
				$cum_nonbinary = get_param("cum_nonbinary");
				$cum_everyone = get_param("cum_everyone");
				$lookin_couples = get_param("lookin_couples");
				$lookin_females = get_param("lookin_females");
				$lookin_males = get_param("lookin_males");
				$lookin_transgender = get_param("lookin_transgender");
				$lookin_nonbinary = get_param("lookin_nonbinary");

				$lookin_everyone = get_param("lookin_everyone");
				$saved_name = get_param("saved_name");
				$is_saved = get_param("is_saved");

				$partyhou_approval  = get_param('partyhou_approval', '') == '1' ? 1 : 0;
				$signin_couples  = get_param('signin_couples', '') == '1' ? 1 : 0;
				$signin_females  = get_param('signin_females', '') == '1' ? 1 : 0;
				$signin_males  = get_param('signin_males', '') == '1' ? 1 : 0;
				$signin_transgender  = get_param('signin_transgender', '') == '1' ? 1 : 0;
				$signin_nonbinary  = get_param('signin_nonbinary', '') == '1' ? 1 : 0;
				$signin_everyone  = get_param('signin_everyone', '') == '1' ? 1 : 0;
				$is_open_partyhouz = 1;

				$invited_users = get_param("invited_users");

				$partyhou_approved = Common::isOptionActive('partyhouz_approval') ? 0 : 1 ;

				DB::execute(
					"INSERT INTO partyhouz_partyhou SET " .
					" user_id=" . to_sql($g_user['user_id'], 'Number') .
					", user_to=" . to_sql($user_id) .

					", category_id=" . to_sql($category_id, 'Number') .
					", partyhou_private=" . to_sql($partyhou_private, 'Number') .
					", access_private=" . to_sql($partyhou_access) .
					", partyhou_title=" . to_sql($partyhou_title) .
					", lock_code=" . to_sql($lock_code) .
					", is_open=" . to_sql($is_open) .
					", is_friends=" . to_sql($is_friends) .
					", is_group=" . to_sql($is_group) .
					", is_lock=" . to_sql($is_lock) .
					", cum_couples=" . to_sql($cum_couples) .
					", cum_females=" . to_sql($cum_females) .
					", cum_males=" . to_sql($cum_males) .
					", cum_transgender=" . to_sql($cum_transgender) .
					", cum_nonbinary=" . to_sql($cum_nonbinary) .
					", cum_everyone=" . to_sql($cum_everyone) .
					", lookin_couples=" . to_sql($lookin_couples) .
					", lookin_females=" . to_sql($lookin_females) .
					", lookin_males=" . to_sql($lookin_males) .
					", lookin_transgender=" . to_sql($lookin_transgender) .
					", lookin_nonbinary=" . to_sql($lookin_nonbinary) .
					", lookin_everyone=" . to_sql($lookin_everyone) .
					", partyhou_approval=".to_sql($partyhou_approval).
					", signin_couples=".to_sql($signin_couples).
					", signin_females=".to_sql($signin_females).
					", signin_males=".to_sql($signin_males).
					", signin_transgender=".to_sql($signin_transgender).
					", signin_nonbinary=".to_sql($signin_nonbinary).
					", signin_everyone=".to_sql($signin_everyone).
					", is_open_partyhouz=".to_sql($is_open_partyhouz).
					", saved_name=" . to_sql($saved_name) .
					", is_saved=" . to_sql($is_saved) .
					", invited_user_ids=" . to_sql($invited_users) .
					", approved=".to_sql($partyhou_approved).
					", partyhou_datetime=" . to_sql(date_format($dt, 'Y-m-d H:i:s')) .
					", created_at = " . to_sql($timeCurrent, 'Text') .
					", updated_at = " . to_sql($timeCurrent, 'Text') .
					""
				);

				$partyhou_id = DB::insert_id();
				CStatsTools::count('partyhouz_created');
				$invited_user_ids = explode(',', $invited_users);
				foreach ($invited_user_ids as $invited_user_id) {
					CpartyhouzTools::create_partyhou_invites($partyhou_id, intval($invited_user_id));

					$invited_user = User::getInfoBasic($invited_user_id);

					Common::usersms('party_invite_sms', $invited_user, 'set_sms_alert_pi');

					$cum_string = "";
					if ($cum_males == 1) {
						$cum_string = "Males / ";
					}
					if ($cum_females == 1) {
						$cum_string = $cum_string . "Females / ";
					}
					if ($cum_couples == 1) {
						$cum_string = $cum_string . "Couples /";
					}

					if ($cum_transgender == 1) {
						$cum_string = $cum_string . "Transgender /";
					}
					
					if ($cum_nonbinary == 1) {
						$cum_string = $cum_string . "Nonbinary";
					}

					if ($cum_everyone == 1) {
						$cum_string = "Everyone";
					}
					$cum_string = "cum to " . $cum_string;

					$locked_string = "";
					if ($is_lock == 1) {
						$locked_string = "Room is Locked";
					} else {
						$locked_string = "Room is Unlocked";
					}
					
					$lookin_string = "";
					if ($lookin_males == 1) {
						$lookin_string = "Males / ";
					}
					if ($lookin_females == 1) {
						$lookin_string = $lookin_string . "Females / ";
					}
					if ($lookin_couples == 1) {
						$lookin_string = $lookin_string . "Couples /";
					}

					if ($lookin_transgender == 1) {
						$lookin_string = $lookin_string . "Transgender /";
					}

					if ($lookin_nonbinary == 1) {
						$lookin_string = $lookin_string . "Nonbinary";
					}
					if ($lookin_everyone == 1) {
						$lookin_string = "Everyone";
					}
					$lookin_string = "Lookin to " . $lookin_string;
					$vars = array(
						'name' => $invited_user['name'],
						'hostname' => $g_user['name'],
						'host_url' => $g['path']['url_main']."search_results.php?display=profile&uid=".$g_user['user_id'],
						'partyhou_url' => $g['path']['url_main']."partyhouz_partyhou_show.php?partyhou_id=".$partyhou_id,
						'partyhou_datetime' => date_format($dt, 'Y-m-d H:i:s'),
						'cum_allowed' => $cum_string,
						'lookin_allowed' => $lookin_string,
						'room_link' => $g['path']['url_main']."partyhouz_partyhou_room.php?partyhou_id=".$partyhou_id
					);
					$emailAuto = Common::automailInfo("invited_partyhouz", Common::getOption('lang_loaded', 'main'), DB_MAX_INDEX);
					$data = array('subject' => '', 'text' => '');
					$subject = $emailAuto['subject'];
					$text = $emailAuto['text'];
					$data['subject'] = $subject;
					$data['text'] = $text;

					$vars['to_user_name'] = $invited_user['name'];
					$vars['to_user_id'] = $invited_user['user_id'];
					$vars['from_user_name'] = $g_user['name'];
					$vars['from_user_id'] = $g_user['user_id'];
					$vars['name'] = $g_user['name'];
					$vars['title'] = $partyhou_title;                        

					if(Common::isValidArray($vars)) {
						
						$subject = Common::replaceByVars($subject, $vars);
						$data['subject'] = $subject;

						if (strip_tags($text) == $text) {
							$text = nl2br($text);
						}
						if (isset($vars['text']) &&
							(strip_tags($vars['text']) == $vars['text'])) {
							$vars['text'] = nl2br($vars['text']);
						}
						$text = Common::replaceByVars($text, $vars);
						$data['text'] = $text;
						$data['header']  = Common::replaceByVars($emailAuto['header'], $vars);
						$data['button']  = $emailAuto['button'];

						Common::prepareUrlAutoMail("invited_partyhouz", $vars);

						$autoMailTmpl = "invited_partyhouz" . '_' . Common::getOption('set', 'template_options');
						$urlAutoMail = isset(Common::$urlAutoMail[$autoMailTmpl]) ? Common::$urlAutoMail[$autoMailTmpl] : Common::$urlAutoMail['invited_partyhouz'];
						$optionTmplName = Common::getOption('name', 'template_options');
						if (isset(Common::$urlAutoMailTemplate[$optionTmplName])
								&& isset(Common::$urlAutoMailTemplate[$optionTmplName]['invited_partyhouz'])) {
							$urlAutoMail = Common::$urlAutoMailTemplate[$optionTmplName]['invited_partyhouz'];
						}

						if(in_array('invited_partyhouz', Common::$urlAutoMailByCurrentLocation)) {
							$data['url'] = Common::urlSite() . Common::replaceByVars($urlAutoMail, $vars);
						} else {
							$data['url'] = Common::urlSiteSubfolders() . Common::replaceByVars($urlAutoMail, $vars);
						}

						if(!in_array('invited_partyhouz', Common::$urlAutoMailAutologinOff)) {
							$receiverBasicInfo = $invited_user;
							if($receiverBasicInfo) {
								$data['url'] = User::urlAddAutologin($data['url'], $receiverBasicInfo);
							}
						}

						$data['url_logo_auto_mail'] = Common::getUrlLogoAutoMail();
						$data['thanks'] = Common::replaceByVars($emailAuto['thanks'], array('title' => Common::getOption('title', 'main')));
						$text = Common::replaceByVars($emailAuto['template'], $data);
						$emailAuto['subject'] = $data['subject'];
					}
					$_GET['user_from'] = $g_user['user_id'];
					$_GET['user_to'] = $invited_user['user_id'];
					$_GET['type'] = "html";
					$_GET['save'] = 1;
					$_GET['subject'] = $emailAuto['subject'];
					$_GET['text'] = $data['text'];
					Common::sendMailPartyhou(true, "invited_partyhouz");
				}

				CpartyhouzTools::create_partyhou_guest($partyhou_id, 0);

				if(!Common::isOptionActive('partyhouz_approval')) {
					if (!$partyhou_private) {
						Wall::add('partyhou_added', $partyhou_id);
					}
				}

				$addOnWall = isset($partyhou_id_exists) ? true : false;

				for ($image_n = 1; $image_n <= 4; ++$image_n) {
					$name = "image_" . $image_n;
					CpartyhouzTools::do_upload_partyhou_image($name, $partyhou_id, $timeCurrent, $addOnWall);
				}

				CpartyhouzTools::update_partyhou($partyhou_id);
				DB::execute('INSERT INTO partyhouz_open VALUES(NULL,'. to_sql($category_id) .','. to_sql($partyhou_id).',0,0,0,3000)');
				
				// End Open_PartyhouZ senior-dev-1019 2024-10-17
			}
			
			redirect("partyhouz_categories.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partyhouz_category_add.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");