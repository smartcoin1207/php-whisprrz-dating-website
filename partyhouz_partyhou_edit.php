<?php
/* (C) Websplosion LTD., 2001-2014
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc
This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/custom_head.php");
require_once("./_include/current/partyhouz/header.php");
require_once("./_include/current/partyhouz/sidebar.php");
require_once("./_include/current/partyhouz/tools.php");
payment_check('partyhouz_partyhou_edit');

class Cpartyhouz extends CHtmlBlock
{
    function action()
    {
        global $g_user;
        global $l;
        global $g;

        $ispartyhouzocial = Common::isOptionActiveTemplate('partyhou_social_enabled');

        $cmd = get_param('cmd');
        $isAjax = get_param_int('ajax');
        if ($cmd == 'save') {
            $tmplName = Common::getTmplName();
            $tmplName = 'oryx';
            $partyhou_id = get_param('partyhou_id');
            $partyhou_private = intval(get_param('partyhou_private')) ? 1 : 0;
            $partyhou_access = 'P';
            if ($ispartyhouzocial) {
                $partyhou_access = get_param('partyhou_access');
            }

            $category_id = intval(get_param('category_id', DB::result('SELECT category_id FROM partyhouz_category ORDER BY category_id ASC LIMIT 1')));
            $partyhou_title = get_param('partyhou_title');

            $partyhou_date = get_param('partyhou_date');
            $partyhou_time = get_param('partyhou_time');

            $isSavepartyhou = $partyhou_title && $partyhou_date && $partyhou_time;
            if ($tmplName != 'edge') {
                $isSavepartyhou = $isSavepartyhou;
            }
            if ($isSavepartyhou) {
                $timeCurrent = date("Y-m-d H:i:s");
                $formatJS = $g['date_formats']['edit_partyhou_time'];

                $formatData = 'edit_partyhou_date';
                if($tmplName == 'edge'){
                    $formatData = 'task_date';
                }

                $formatType = $g['date_formats'][$formatData];
                $format = str_replace("|", "?", $formatType);
                $date = date_create_from_format($format, $partyhou_date);
                if(!$date) {
                    return;
                }
                $partyhou_date = date_format($date, 'Y-m-d');
                $format = str_replace("|", "?", $formatJS);
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
                $time=DateTime::createFromFormat($format, $partyhou_time, $zone);

                $partyhou_time = date_format($time, 'H:i');

                $dt=DateTime::createFromFormat('Y-m-d H:i', $partyhou_date.' '.$partyhou_time, $zone);
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

                //popcorn modified 2024-05-24
                $partyhou_approval  = get_param('partyhou_approval', '') == '1' ? 1 : 0;
                $signin_couples  = get_param('signin_couples', '') == '1' ? 1 : 0;
                $signin_females  = get_param('signin_females', '') == '1' ? 1 : 0;
                $signin_males  = get_param('signin_males', '') == '1' ? 1 : 0;
                $signin_transgender  = get_param('signin_transgender', '') == '1' ? 1 : 0;
                $signin_nonbinary  = get_param('signin_nonbinary', '') == '1' ? 1 : 0;
                $signin_everyone  = get_param('signin_everyone', '') == '1' ? 1 : 0;
                $is_open_partyhouz = get_param('is_open_partyhouz', '') == '1' ? 1 : 0;

                $invited_users = get_param("invited_users");

                if ($partyhou_id) {
                    $partyhou_id_exists = true;
                    if (!CpartyhouzTools::retrieve_partyhou_for_edit_by_id($partyhou_id))
                        redirect('music.php');

                    $partyhou_row = DB::row("SELECT *  FROM partyhouz_partyhou WHERE partyhou_id = " . to_sql($partyhou_id));
                    $current_partyhou_approved = $partyhou_row['approved'];

                    DB::execute("UPDATE partyhouz_partyhou SET " .
                        " category_id=" . to_sql($category_id, 'Number') .
                        ", partyhou_private=" . to_sql($partyhou_private, 'Number') .
                        ", access_private=" . to_sql($partyhou_access) .
                        ", partyhou_title=" . to_sql($partyhou_title) .
                        ", user_id=" . to_sql($user_id) .
                        ", user_to=" . to_sql($user_id) .
                        ", user_mail=" . to_sql($user_mail) .
                        ", user_name=" . to_sql($user_name) .
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
                        ", partyhou_datetime=" . to_sql(date_format($dt, 'Y-m-d H:i:s')) .
                        ", updated_at = NOW() WHERE partyhou_id=" . to_sql($partyhou_id, 'Number') . " LIMIT 1");

                        if(Common::isOptionActive('partyhouz_approval') || $current_partyhou_approved) {
                            if (!$partyhou_private) {
                                Wall::add('partyhou_edited', $partyhou_id);
                            }
                        }
                        
                } else {
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

                        /* START - Divyesh - 18082023 */
                        $invited_user = User::getInfoBasic($invited_user_id);

                        Common::usersms('party_invite_sms', $invited_user, 'set_sms_alert_pi');

                        /* END - Divyesh - 18082023 */

                        /* start Rade - 20230821 */
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
                            /*if (strip_tags($text) == $text) {
                                $text = nl2br($text);
                            }*/
                            $data['text'] = $text;
                            //$data['header']  = $emailAuto['header'];
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

                            //$urlTmpl = str_replace($g['path']['url_main'], '', $g['tmpl']['url_tmpl_administration']);
                            //$data['url_admin'] = Common::urlSiteSubfolders() . $urlTmpl;
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
                        /* end Rade - 20230821 */
                    }

                    CpartyhouzTools::create_partyhou_guest($partyhou_id, 0);

                    if(!Common::isOptionActive('partyhouz_approval')) {
                        if (!$partyhou_private) {
                            Wall::add('partyhou_added', $partyhou_id);
                        }
                    }
                }

                $addOnWall = isset($partyhou_id_exists) ? true : false;

                // if (Common::isOptionActiveTemplate('partyhou_social_enabled')) {
                //     $partyhouPhotoId = get_param_int('partyhou_photo_id');
                //     if (isset($partyhou_id_exists) && !$partyhouPhotoId) {
                //         CpartyhouzTools::delete_partyhou_image_all($partyhou_id);
                //     }
                //     $imageTempId = get_param_int('partyhou_photo_id');
                //     if ($imageTempId) {
                //         $imageTemp = Common::getOption('dir_files', 'path') . 'temp/tmp_partyhou_' . $imageTempId . '.jpg';
                //         CpartyhouzTools::do_upload_partyhou_image('', $partyhou_id, $timeCurrent, $addOnWall, $imageTemp);
                //     }
                // } else {
                    for ($image_n = 1; $image_n <= 4; ++$image_n) {
                        $name = "image_" . $image_n;
                        CpartyhouzTools::do_upload_partyhou_image($name, $partyhou_id, $timeCurrent, $addOnWall);
                    }
                // }

                CpartyhouzTools::update_partyhou($partyhou_id);
                //$Live_Url="https://www.easysendsms.com/sms/bulksms-api/bulksms-api?username=minamina2020&password=esm41140&from=+12017628299&to=13399333986&type=0";
                //"Https://".$This->Host."/Api?Username=".$This->StrUserName."&Password=".$This->StrPassword."&Type=".$This->StrMessageType."&To=".$This->StrMobile."&From=".$This->StrSender."&Message=".$This->StrMessage."";
                //$Parse_Url=File($Live_Url); 
                if ($isAjax) {
                    $url = Common::pageUrl('calendar', 0, $dt->format('Y-m-d'));
                    die(getResponseDataAjaxByAuth(array('redirect' => $url)));
                } else {
                    redirect('partyhouz_partyhou_show.php?partyhou_id=' . $partyhou_id);
                }

            }
            if ($isAjax) {
                die(getResponseDataAjaxByAuth(array('error' => true)));
            } else {
                redirect('partyhouz.php');
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $tmplName = Common::getTmplName();
        $tmplName = 'oryx';
        $ispartyhouzocial = Common::isOptionActiveTemplate('partyhou_social_enabled');

        $guid = guid();
        $partyhou_private = intval(get_param('partyhou_private')) ? 1 : 0;

        $partyhou_id = get_param('partyhou_id');
        $partyhou = CpartyhouzTools::retrieve_partyhou_for_edit_by_id($partyhou_id);

        $formatData = 'edit_partyhou_date';
        if ($tmplName == 'oryx') {
            $formatTypeJS = 'edit_partyhou_date';

        } elseif ($tmplName == 'edge') {
            $formatTypeJS = 'task_date';
            $formatData = 'task_date';
        } else {
            $formatTypeJS = 'edit_partyhou_date_mixer_js';
        }
              //pupup windows

              $popup_row = DB::row("SELECT  * FROM posting_info  WHERE page = 'popup_partyhouz' LIMIT 1");
              $html->setvar('popup_title', $popup_row['header']);
              $html->setvar('popup_confirm_button', $popup_row['active']);
              $html->setvar('popup_decline_button', $popup_row['deactive']);
              $var = array();
              $text = $popup_row['text'];
              $var['username'] = $g_user['name'];
              $var['posting_terms'] = l('popup_posting_terms');
              $var['time'] = date('Y-m-d h:i:s A');
              $var['privacy_policy'] = l('popup_privacy_policy');
              $ful_text = Common::replaceByVars($text, $var);
              $html->setvar('popup_text', $ful_text);
              $html->parse('popup_confirm_terms_policy', true);
        if ($partyhou) {
            $partyhou_private = $partyhou['partyhou_private'];
            $html->setvar('partyhou_id', $partyhou['partyhou_id']);
            $html->setvar('partyhou_title', ($partyhou['partyhou_title']));
            $html->setvar('partyhou_datetime', Common::dateFormat($partyhou['partyhou_datetime'], $formatData));

            $html->setvar('partyhou_approval', $partyhou['partyhou_approval']);
            $html->setvar('signin_couples', $partyhou['signin_couples']);
            $html->setvar('signin_females', $partyhou['signin_females']);
            $html->setvar('signin_males', $partyhou['signin_males']);
            $html->setvar('signin_transgender', $partyhou['signin_transgender']);
            $html->setvar('signin_nonbinary', $partyhou['signin_nonbinary']);
            $html->setvar('signin_everyone', $partyhou['signin_everyone']);

            if (!$ispartyhouzocial) {
                DB::query("SELECT * FROM partyhouz_partyhou_image WHERE partyhou_id=" . $partyhou['partyhou_id'] . " ORDER BY created_at ASC");
                $n_images = 0;
                while ($image = DB::fetch_row()) {
                    $html->setvar("image_id", $image['image_id']);
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_b.jpg");
                    $html->parse("image");
                    ++$n_images;
                }
                if ($n_images)
                    $html->parse('edit_images');
            }
            if ($partyhou_private) {
                $html->parse('edit_private_title');
                $html->parse('edit_private_button');
            } else {
                $html->parse('edit_title');
                $html->parse('edit_button');
            }

            if ($ispartyhouzocial) {
                $partyhou_btn_create = l('btn_save');
                $partyhou_btn_class = 'btn_edit';

                /*$images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id'], false);
                $photoUrl = $images['image_file'];
                if (!$images['system']) {
                $html->setvar('partyhou_photo_id', $images['photo_id']);
                }
                $partyhou_btn_upload_photo = $images['system'] ? l('choose_an_image') : l('use_another');*/
            }
        } elseif ($partyhou_id) {
            redirect('partyhouz.php');
        } else {

            if (!$ispartyhouzocial) {
                $html->setvar('partyhou_title', l('partyhou_title'));
            }

            $html->setvar('partyhou_approval', '0');
            $html->setvar('signin_couples', '1');
            $html->setvar('signin_females', '1');
            $html->setvar('signin_males', '1');
            $html->setvar('signin_transgender', '1');
            $html->setvar('signin_nonbinary', '1');
            $html->setvar('signin_everyone', '1');

            $date = get_param('date', date('Y-m-d'));
            $hour = date("H");
            $minute = date("i");
            if ((int) $minute > 0 && (int) $minute < 30) {
                $minute = "30";
            } elseif ((int) $minute > 30) {
                $minute = "00";
                $hour = str_pad((int) $hour + 1, 2, '0', STR_PAD_LEFT);
            }
            $html->setvar('partyhou_datetime', htmlspecialchars(Common::dateFormat($date . ' ' . $hour . ':' . $minute, $formatData)));

            if ($ispartyhouzocial) {
                $partyhou_btn_create = l('btn_create');
                $partyhou_btn_class = 'btn_create';

                /*$images = CpartyhouzTools::partyhou_images(0, false);
                $photoUrl = $images['image_file'];
                $partyhou_btn_upload_photo = l('choose_an_image');*/
            }

            if ($partyhou_private) {
                $html->parse('create_private_title');
                $html->parse('create_private_button');
            } else {
                $html->parse('create_title');
                $html->parse('create_button');
            }
        }

        if ($ispartyhouzocial) {
            //$html->setvar('partyhou_photo_url', $photoUrl);
            //$html->parse('bl_photo_delete', false);

            $html->setvar('partyhou_btn_class', $partyhou_btn_class);
            $html->setvar('partyhou_btn_create', $partyhou_btn_create);
            //$html->setvar('partyhou_photo_btn_upload', $partyhou_btn_upload_photo);

            $friends = User::getListFriends($guid);
            foreach ($friends as $friend) {
                $html->setvar('list_friend_partyhou_user_id', $friend['friend_id']);
                $html->setvar('list_friend_partyhou_name', $friend['name']);
                $html->setvar('list_friend_partyhou_photo', User::getPhotoDefault($friend['friend_id'], 's'));
                $html->parse('list_friend_partyhou', true);
            }

        }

        $html->setvar('edit_partyhou_date',$g['date_formats'][$formatTypeJS]);
        $html->setvar('edit_partyhou_time',$g['date_formats']['edit_partyhou_time']);

        $html->setvar('partyhou_private', $partyhou_private);

        $settings = CpartyhouzTools::settings();

        $category_options = '';
        DB::query("SELECT * FROM partyhouz_category ORDER BY category_id");
        $selected_category_id = $partyhou ? $partyhou['category_id'] : $settings['category_id'];
        while ($category = DB::fetch_row()) {
            if (!$selected_category_id)
                $selected_category_id = $category['category_id'];

            $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
            $category_options .= l($category['category_title'], false, 'partyhouz_category');
            $category_options .= '</option>';
        }
        $html->setvar("category_options", $category_options);

        if (!$partyhou_private) {
            $html->parse('partyhou_location');
            $html->parse('partyhou_parameters');
        }

        $html->setvar('user_id', $g_user['user_id']);
        $html->setvar('user_name', $g_user['name']);
        $html->setvar('calendar_month', html_entity_decode(l('calendar_month'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays', html_entity_decode(l('calendar_weekdays'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays_short', html_entity_decode(l('calendar_weekdays_short'), ENT_QUOTES, 'UTF-8'));

        // TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
    }
}

$page = new Cpartyhouz("", getPageCustomTemplate('partyhouz_partyhou_edit.html', 'partyhouz_partyhou_edit_template'));

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

if (Common::isParseModule('partyhouz_custom_head')) {
    $partyhouz_custom_head = new CpartyhouzCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_custom_head.html");
    $header->add($partyhouz_custom_head);
}

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

if (Common::isParseModule('partyhouz_header')) {
    $partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
    $page->add($partyhouz_header);
}

if (Common::isParseModule('partyhouz_sidebar')) {
    $partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
    $page->add($partyhouz_sidebar);
}

include("./_include/core/main_close.php");