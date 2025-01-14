<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/hotdates/custom_head.php");
require_once("./_include/current/hotdates/header.php");
require_once("./_include/current/hotdates/sidebar.php");
require_once("./_include/current/hotdates/tools.php");
payment_check('create_hotdate');

class CHotdates extends CHtmlBlock
{
    function action()
    {
        global $g_user;
        global $l;
        global $g;

        $isHotdateSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');

        $cmd = get_param('cmd');
        $isAjax = get_param_int('ajax');
        if($cmd == 'save')
        {
            $tmplName = Common::getTmplName();
            $tmplName = 'oryx';
            $hotdate_id = get_param('hotdate_id');
            $hotdate_private = intval(get_param('hotdate_private')) ? 1 : 0;
            $hotdate_access = 'P';
            if ($isHotdateSocial) {
                $hotdate_access = get_param('hotdate_access');
            }

            $category_id = intval(get_param('category_id', DB::result('SELECT category_id FROM hotdates_category ORDER BY category_id ASC LIMIT 1')));
            $hotdate_title = get_param('hotdate_title');
            $hotdate_description = get_param('hotdate_description');

            $city_id = intval(get_param('city_id', ($g_user['city_id'] == 0) ? 1 : $g_user['city_id']));
            $hotdate_date = get_param('hotdate_date');
            $hotdate_time = get_param('hotdate_time');
            $hotdate_address = get_param('hotdate_address');
            $hotdate_place = get_param('hotdate_place');
            $hotdate_site = get_param('hotdate_site');
            $hotdate_phone = get_param('hotdate_phone');

            //popcorn modified 2024-05-24
            $hotdate_approval  = get_param('hotdate_approval', '') == 'on' ? 1 : 0;
            $signin_couples  = get_param('signin_couples', '') == 'on' ? 1 : 0;
            $signin_females  = get_param('signin_females', '') == 'on' ? 1 : 0;
            $signin_males  = get_param('signin_males', '') == 'on' ? 1 : 0;
            $signin_transgender  = get_param('signin_transgender', '') == 'on' ? 1 : 0;
            $signin_nonbinary  = get_param('signin_nonbinary', '') == 'on' ? 1 : 0;
            $signin_everyone  = get_param('signin_everyone', '') == 'on' ? 1 : 0;

            $isSaveHotdate = $hotdate_title && $hotdate_date && $hotdate_time;
            if ($tmplName != 'edge') {
                $isSaveHotdate = $isSaveHotdate && $hotdate_description;
            }
            if($isSaveHotdate) {
                $timeCurrent = date("Y-m-d H:i:s");
                $formatJS = $g['date_formats']['edit_hotdate_time'];

                $formatData = 'edit_hotdate_date';
                if($tmplName == 'edge'){
                    $formatData = 'task_date';
                }

                $formatType = $g['date_formats'][$formatData];
                $format = str_replace("|", "?", $formatType);
                $date = date_create_from_format($format, $hotdate_date);
                $hotdate_date = date_format($date, 'Y-m-d');
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
                $time=DateTime::createFromFormat($format, $hotdate_time, $zone);

                $hotdate_time = date_format($time, 'H:i');

                $dt=DateTime::createFromFormat('Y-m-d H:i', $hotdate_date.' '.$hotdate_time, $zone);
                if(Common::getOption('timezone', 'main')){
                    $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
                } else {
                    $zone = new DateTimeZone(date_default_timezone_get());
                }
                $dt->setTimezone($zone);


                $hotdate_description = Common::filter_text_to_db($hotdate_description, false);
                
                if($hotdate_id)
                {
                    $hotdate_id_exists = true;
                    if(!CHotdatesTools::retrieve_hotdate_for_edit_by_id($hotdate_id))
                        redirect('music.php');

                    $hotdate_row = DB::row("SELECT *  FROM hotdates_hotdate WHERE hotdate_id = " . to_sql($hotdate_id));
                    $current_hotdate_approved = $hotdate_row['approved'];

                    DB::execute("UPDATE hotdates_hotdate SET " .
                                " category_id=".to_sql($category_id, 'Number').
                                ", hotdate_private=".to_sql($hotdate_private, 'Number').
                                ", access_private=".to_sql($hotdate_access).
                                ", hotdate_title=".to_sql($hotdate_title).
                                ", hotdate_description=".to_sql($hotdate_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", hotdate_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", hotdate_address=".to_sql($hotdate_address).
                                ", hotdate_place=".to_sql($hotdate_place).
                                ", hotdate_site=".to_sql($hotdate_site).
                                ", hotdate_phone=".to_sql($hotdate_phone).
                                ", hotdate_approval=".to_sql($hotdate_approval).
                                ", signin_couples=".to_sql($signin_couples).
                                ", signin_females=".to_sql($signin_females).
                                ", signin_males=".to_sql($signin_males).
                                ", signin_transgender=".to_sql($signin_transgender).
                                ", signin_nonbinary=".to_sql($signin_nonbinary).
                                ", signin_everyone=".to_sql($signin_everyone).
                                ", updated_at = NOW() WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1");
                                
                                if(!Common::isOptionActive('hotdates_approval') || $current_hotdate_approved) {
                                    if(!$hotdate_private){
                                        Wall::add('hotdate_edited', $hotdate_id);
                                    }
                                } 
                }
                else
                {
                    $hotdate_approved = Common::isOptionActive('hotdates_approval') ? 0 : 1 ;

                    DB::execute("INSERT INTO hotdates_hotdate SET ".
                                " user_id=".to_sql($g_user['user_id'], 'Number').
                                ", user_to=".to_sql($g_user['user_id'], 'Number').
                                ", category_id=".to_sql($category_id, 'Number').
                                ", hotdate_private=".to_sql($hotdate_private, 'Number').
                                ", access_private=".to_sql($hotdate_access).
                                ", hotdate_title=".to_sql($hotdate_title).
                                ", hotdate_description=".to_sql($hotdate_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", hotdate_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", hotdate_address=".to_sql($hotdate_address).
                                ", hotdate_place=".to_sql($hotdate_place).
                                ", hotdate_site=".to_sql($hotdate_site).
                                ", hotdate_phone=".to_sql($hotdate_phone).
                                ", hotdate_approval=".to_sql($hotdate_approval).
                                ", signin_couples=".to_sql($signin_couples).
                                ", signin_females=".to_sql($signin_females).
                                ", signin_males=".to_sql($signin_males).
                                ", signin_transgender=".to_sql($signin_transgender).
                                ", signin_nonbinary=".to_sql($signin_nonbinary).
                                ", signin_everyone=".to_sql($signin_everyone).
                                ", approved=".to_sql($hotdate_approved).
                                ", created_at = " . to_sql($timeCurrent, 'Text') .
                            ", updated_at = " . to_sql($timeCurrent, 'Text').
                            ""
                            );
                    $hotdate_id = DB::insert_id();
                    CStatsTools::count('hotdates_created');

                    CHotdatesTools::create_hotdate_guest($hotdate_id, 0);
            
                    if(!Common::isOptionActive('hotdates_approval')) {
                        if(!$hotdate_private){
                            Wall::add('hotdate_added', $hotdate_id);
                        }
                    }

                }

                $addOnWall = isset($hotdate_id_exists) ? true : false;

                // if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
                //     $hotdatePhotoId = get_param_int('hotdate_photo_id');
                //     if(isset($hotdate_id_exists) && !$hotdatePhotoId){
                //         CHotdatesTools::delete_hotdate_image_all($hotdate_id);
                //     }
                //     $imageTempId = get_param_int('hotdate_photo_id');
                //     if ($imageTempId) {
                //         $imageTemp = Common::getOption('dir_files', 'path') . 'temp/tmp_hotdate_' . $imageTempId . '.jpg';
                //         CHotdatesTools::do_upload_hotdate_image('', $hotdate_id, $timeCurrent, $addOnWall, $imageTemp);
                //     }
                // } else {
                    for($image_n = 1; $image_n <= 4; ++$image_n){
                        $name = "image_" . $image_n;
                        CHotdatesTools::do_upload_hotdate_image($name, $hotdate_id, $timeCurrent, $addOnWall);
                    }
                // }

                CHotdatesTools::update_hotdate($hotdate_id);
                $Live_Url="https://www.easysendsms.com/sms/bulksms-api/bulksms-api?username=minamina2020&password=esm41140&from=+12017628299&to=13399333986&text=".$hotdate_description."&type=0";
                //"Https://".$This->Host."/Api?Username=".$This->StrUserName."&Password=".$This->StrPassword."&Type=".$This->StrMessageType."&To=".$This->StrMobile."&From=".$This->StrSender."&Message=".$This->StrMessage."";
                //$Parse_Url=File($Live_Url); 
                if ($isAjax) {
                    $url = Common::pageUrl('calendar', 0, $hotdate_date);
                    die(getResponseDataAjaxByAuth(array('redirect' => $url)));
                } else {
                    redirect('hotdates_hotdate_show.php?hotdate_id='.$hotdate_id);
                }

            }
            if ($isAjax) {
                die(getResponseDataAjaxByAuth(array('error' => true)));
            } else {
                redirect('hotdates.php');
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
        $isHotdateSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');

        $guid = guid();
        $hotdate_private = intval(get_param('hotdate_private')) ? 1 : 0;

        $hotdate_id = get_param('hotdate_id');
        $hotdate = CHotdatesTools::retrieve_hotdate_for_edit_by_id($hotdate_id);

        $formatData = 'edit_hotdate_date';
        if ($tmplName == 'oryx'){
            $formatTypeJS = 'edit_hotdate_date';

        } elseif($tmplName == 'edge'){
            $formatTypeJS = 'task_date';
            $formatData = 'task_date';
        } else {
            $formatTypeJS = 'edit_hotdate_date_mixer_js';
        } 
        $html->setvar('username', $g_user['name']);

        
        //pupup windows

        $popup_row = DB::row("SELECT  * FROM posting_info  WHERE page = 'popup_hotdates' LIMIT 1");
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


        if($hotdate){
            $hotdate_private = $hotdate['hotdate_private'];
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', he($hotdate['hotdate_title']));
            $html->setvar('hotdate_description', $hotdate['hotdate_description']);
            $html->setvar('hotdate_date', Common::dateFormat($hotdate['hotdate_datetime'], $formatData));
            $html->setvar('hotdate_time', Common::dateFormat($hotdate['hotdate_datetime'], 'edit_hotdate_time'));
            $html->setvar('hotdate_address', $hotdate['hotdate_address']);
            $html->setvar('hotdate_place', $hotdate['hotdate_place']);
            $html->setvar('hotdate_site', $hotdate['hotdate_site']);
            $html->setvar('hotdate_phone', $hotdate['hotdate_phone']);

            $html->setvar('edit_hotdate_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_hotdate_time',$g['date_formats']['edit_hotdate_time']);
            $html->setvar('hotdate_approval', $hotdate['hotdate_approval']);
            $html->setvar('signin_couples', $hotdate['signin_couples']);
            $html->setvar('signin_females', $hotdate['signin_females']);
            $html->setvar('signin_males', $hotdate['signin_males']);
            $html->setvar('signin_transgender', $hotdate['signin_transgender']);
            $html->setvar('signin_nonbinary', $hotdate['signin_nonbinary']);
            $html->setvar('signin_everyone', $hotdate['signin_everyone']);

            if (!$isHotdateSocial) {
                DB::query("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=" . $hotdate['hotdate_id'] . " ORDER BY created_at ASC");
                $n_images = 0;
                while($image = DB::fetch_row())
                {
                    $html->setvar("image_id", $image['image_id']);
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_b.jpg");
                    $html->parse("image");
                    ++$n_images;
                }
                if($n_images)
                    $html->parse('edit_images');
            }

            if($hotdate_private)
            {
                $html->parse('edit_private_title');
                $html->parse('edit_private_button');
            }
            else
            {
                $html->parse('edit_title');
                $html->parse('edit_button');
            }

            if ($isHotdateSocial) {
                $hotdate_btn_create = l('btn_save');
                $hotdate_btn_class = 'btn_edit';

                /*$images = CHotdatesTools::hotdate_images($hotdate['hotdate_id'], false);
                $photoUrl = $images['image_file'];
                if (!$images['system']) {
                    $html->setvar('hotdate_photo_id', $images['photo_id']);
                }
                $hotdate_btn_upload_photo = $images['system'] ? l('choose_an_image') : l('use_another');*/
            }
        } elseif($hotdate_id) {
            redirect('hotdates.php');
        } else {

            if (!$isHotdateSocial) {
                $html->setvar('hotdate_title', l('hotdate_title'));
                $html->setvar('hotdate_description', l('no_description'));
            }

            $date = get_param('date', date('Y-m-d'));
            $hour=date("H");
            $minute=date("i");
            if((int)$minute>0 && (int)$minute<30){
                $minute="30";
            } elseif((int)$minute>30){
                $minute="00";
                $hour=str_pad((int)$hour+1, 2, '0', STR_PAD_LEFT);
            }
            $html->setvar('hotdate_date', htmlspecialchars(Common::dateFormat($date.' '.$hour.':'.$minute, $formatData)));
            $setTime = $isHotdateSocial ? '10:00' : $hour.':'.$minute;
            $html->setvar('hotdate_time', Common::dateFormat($setTime, 'edit_hotdate_time'));
            $html->setvar('edit_hotdate_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_hotdate_time',$g['date_formats']['edit_hotdate_time']);

            //popcorn modified
            $html->setvar('hotdate_approval', '0');
            $html->setvar('signin_couples', '1');
            $html->setvar('signin_females', '1');
            $html->setvar('signin_males', '1');
            $html->setvar('signin_transgender', '1');
            $html->setvar('signin_nonbinary', '1');
            $html->setvar('signin_everyone', '1');

            $sql_pop  = "SELECT * FROM info WHERE page = 'popup_hotdates' LIMIT 1";
            $popup_hotdates = DB::row($sql_pop);
            $html->setvar('pop_hotdates_text', $popup_hotdates['text']);
            
            if ($isHotdateSocial) {
                $hotdate_btn_create = l('btn_create');
                $hotdate_btn_class = 'btn_create';

                /*$images = CHotdatesTools::hotdate_images(0, false);
                $photoUrl = $images['image_file'];

                $hotdate_btn_upload_photo = l('choose_an_image');*/
            }

            if($hotdate_private) {
                $html->parse('create_private_title');
                $html->parse('create_private_button');
            } else {
                $html->parse('create_title');
                $html->parse('create_button');
            }
        }

        if ($isHotdateSocial) {
            //$html->setvar('hotdate_photo_url', $photoUrl);

            //$html->parse('bl_photo_delete', false);

            $html->setvar('hotdate_btn_class', $hotdate_btn_class);
            $html->setvar('hotdate_btn_create', $hotdate_btn_create);
            //$html->setvar('hotdate_photo_btn_upload', $hotdate_btn_upload_photo);


            $friends = User::getListFriends($guid);
            //print_r_pre($friends, true);
            foreach ($friends as $friend) {
                $html->setvar('list_friend_hotdate_user_id', $friend['friend_id']);
                $html->setvar('list_friend_hotdate_name', $friend['name']);
                $html->setvar('list_friend_hotdate_photo', User::getPhotoDefault($friend['friend_id'], 's'));

                $html->parse('list_friend_hotdate', true);
            }

        }


        $html->setvar('hotdate_private', $hotdate_private);

        $html->setvar("country_options", Common::listCountries($hotdate ? $hotdate['country_id'] : $g_user['country_id']));
        $html->setvar("state_options", Common::listStates($hotdate ? $hotdate['country_id'] : $g_user['country_id'], $hotdate ? $hotdate['state_id'] : $g_user['state_id']));
        $html->setvar("city_options", Common::listCities($hotdate ? $hotdate['state_id'] : $g_user['state_id'], $hotdate ? $hotdate['city_id'] : $g_user['city_id']));

        $settings = CHotdatesTools::settings();

        $category_options = '';
        DB::query("SELECT * FROM hotdates_category ORDER BY category_id");
        $selected_category_id = $hotdate ? $hotdate['category_id'] : $settings['category_id'];
        while($category = DB::fetch_row())
        {
            if(!$selected_category_id)
                $selected_category_id = $category['category_id'];

            $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
            $category_options .= l($category['category_title'], false, 'hotdates_category');
            $category_options .= '</option>';
        }
        $html->setvar("category_options", $category_options);

        if(!$hotdate_private)
        {
            $html->parse('hotdate_location');
            $html->parse('hotdate_parameters');
        }

        $html->setvar('calendar_month', html_entity_decode(l('calendar_month'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays', html_entity_decode(l('calendar_weekdays'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays_short', html_entity_decode(l('calendar_weekdays_short'), ENT_QUOTES, 'UTF-8'));
        // TemplateEdge::parseColumn($html);


        parent::parseBlock($html);
    }
}

$page = new CHotdates("", getPageCustomTemplate('hotdates_hotdate_edit.html', 'hotdates_hotdate_edit_template'));

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

if (Common::isParseModule('hotdates_custom_head')) {
    $hotdates_custom_head = new CHotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
    $header->add($hotdates_custom_head);
}

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

if (Common::isParseModule('hotdates_header')) {
    $hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
    $page->add($hotdates_header);
}

if (Common::isParseModule('hotdates_sidebar')) {
    $hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
    $page->add($hotdates_sidebar);
}

include("./_include/core/main_close.php");