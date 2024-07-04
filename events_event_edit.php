<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/events/custom_head.php");
require_once("./_include/current/events/header.php");
require_once("./_include/current/events/sidebar.php");
require_once("./_include/current/events/tools.php");
class CEvents extends CHtmlBlock
{
    function action()
    {
        global $g_user;
        global $l;
        global $g;

        $isEventSocial = Common::isOptionActiveTemplate('event_social_enabled');

        $cmd = get_param('cmd');
        $isAjax = get_param_int('ajax');
        if($cmd == 'save')
        {
            $tmplName = Common::getTmplName();
            $tmplName  = 'oryx';
            $event_id = get_param('event_id');
            $event_private = intval(get_param('event_private')) ? 1 : 0;
            $event_access = 'P';
            if ($isEventSocial) {
                $event_access = get_param('event_access');
            }

            $category_id = intval(get_param('category_id', DB::result('SELECT category_id FROM events_category ORDER BY category_id ASC LIMIT 1')));
            $event_title = get_param('event_title');
            $event_description = get_param('event_description');

            $city_id = intval(get_param('city_id', ($g_user['city_id'] == 0) ? 1 : $g_user['city_id']));
            $event_date = get_param('event_date');
            $event_time = get_param('event_time');
            $event_address = get_param('event_address');
            $event_place = get_param('event_place');
            $event_site = get_param('event_site');
            $event_phone = get_param('event_phone');

            //popcorn modified 2024-05-24
            $event_approval = get_param('event_approval', '') == 'on' ? 1 : 0;
            $signin_couples  = get_param('signin_couples', '') == 'on' ? 1 : 0;
            $signin_females  = get_param('signin_females', '') == 'on' ? 1 : 0;
            $signin_males  = get_param('signin_males', '') == 'on' ? 1 : 0;
            $signin_transgender = get_param('signin_transgender', '') == 'on' ? 1 : 0;
            $signin_nonbinary  = get_param('signin_nonbinary', '') == 'on' ? 1 : 0;

            $signin_everyone  = get_param('signin_everyone', '') == 'on' ? 1 : 0;

            $isSaveEvent = $event_title && $event_date && $event_time;
            if ($tmplName != 'edge') {
                $isSaveEvent = $isSaveEvent && $event_description;
            }

            if($isSaveEvent) {
                $timeCurrent = date("Y-m-d H:i:s");
                $formatJS = $g['date_formats']['edit_event_time'];

                $formatData = 'edit_event_date';
                if($tmplName == 'edge'){
                    $formatData = 'task_date';
                }

                $formatType = $g['date_formats'][$formatData];
                $format = str_replace("|", "?", $formatType);
                $date = date_create_from_format($format, $event_date);
                $event_date = date_format($date, 'Y-m-d');
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
                $time=DateTime::createFromFormat($format, $event_time, $zone);

                $event_time = date_format($time, 'H:i');

                $dt=DateTime::createFromFormat('Y-m-d H:i', $event_date.' '.$event_time, $zone);
                if(Common::getOption('timezone', 'main')){
                    $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
                } else {
                    $zone = new DateTimeZone(date_default_timezone_get());
                }
                $dt->setTimezone($zone);


                $event_description = Common::filter_text_to_db($event_description, false);
                if($event_id)
                {
                    $event_id_exists = true;
                    if(!CEventsTools::retrieve_event_for_edit_by_id($event_id))
                        redirect('music.php');

                    $event_row = DB::row("SELECT *  FROM events_event WHERE event_id = " . to_sql($event_id));
                    $current_event_approved = $event_row['approved'];

                    DB::execute("UPDATE events_event SET " .
                                " category_id=".to_sql($category_id, 'Number').
                                ", event_private=".to_sql($event_private, 'Number').
                                ", access_private=".to_sql($event_access).
                                ", event_title=".to_sql($event_title).
                                ", event_description=".to_sql($event_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", event_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", event_address=".to_sql($event_address).
                                ", event_place=".to_sql($event_place).
                                ", event_site=".to_sql($event_site).
                                ", event_phone=".to_sql($event_phone).
                                ", event_approval=".to_sql($event_approval).
                                ", signin_couples=".to_sql($signin_couples).
                                ", signin_females=".to_sql($signin_females).
                                ", signin_males=".to_sql($signin_males).
                                ", signin_transgender=".to_sql($signin_transgender).
                                ", signin_nonbinary=".to_sql($signin_nonbinary).
                                ", signin_everyone=".to_sql($signin_everyone).
                                ", updated_at = NOW() WHERE event_id=" . to_sql($event_id, 'Number') . " LIMIT 1");

                                if(!Common::isOptionActive('events_approval') || $current_event_approved) {
                                    if(!$event_private){
                                        Wall::add('event_edited', $event_id);
                                    }
                                } 
                }
                else
                {
                    $event_approved = Common::isOptionActive('events_approval') ? 0 : 1 ;
                    DB::execute("INSERT INTO events_event SET ".
                                " user_id=".to_sql($g_user['user_id'], 'Number').
                                ", user_to=".to_sql($g_user['user_id'], 'Number').

                                ", category_id=".to_sql($category_id, 'Number').
                                ", event_private=".to_sql($event_private, 'Number').
                                ", access_private=".to_sql($event_access).
                                ", event_title=".to_sql($event_title).
                                ", event_description=".to_sql($event_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", event_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", event_address=".to_sql($event_address).
                                ", event_place=".to_sql($event_place).
                                ", event_site=".to_sql($event_site).
                                ", event_phone=".to_sql($event_phone).
                                ", event_approval=".to_sql($event_approval).
                                ", signin_couples=".to_sql($signin_couples).
                                ", signin_females=".to_sql($signin_females).
                                ", signin_males=".to_sql($signin_males).
                                ", signin_transgender=".to_sql($signin_transgender).
                                ", signin_nonbinary=".to_sql($signin_nonbinary).
                                ", signin_everyone=".to_sql($signin_everyone).
                                ", approved=".to_sql($event_approved).
                                ", created_at = " . to_sql($timeCurrent, 'Text') .
                            ", updated_at = " . to_sql($timeCurrent, 'Text').
                            ""
                            );


                    $event_id = DB::insert_id();
                    CStatsTools::count('events_created');

                    CEventsTools::create_event_guest($event_id, 0);

                    if(!Common::isOptionActive('events_approval')) {
                        if(!$event_private){
                            Wall::add('event_added', $event_id);
                        }
                    }
                }

                $addOnWall = isset($event_id_exists) ? true : false;

                // if (Common::isOptionActiveTemplate('event_social_enabled')) {
                //     $eventPhotoId = get_param_int('event_photo_id');
                //     if(isset($event_id_exists) && !$eventPhotoId){
                //         CEventsTools::delete_event_image_all($event_id);
                //     }
                //     $imageTempId = get_param_int('event_photo_id');
                //     if ($imageTempId) {
                //         $imageTemp = Common::getOption('dir_files', 'path') . 'temp/tmp_event_' . $imageTempId . '.jpg';
                //         CEventsTools::do_upload_event_image('', $event_id, $timeCurrent, $addOnWall, $imageTemp);
                //     }
                // } else {
                    for($image_n = 1; $image_n <= 4; ++$image_n) {
                        $name = "image_" . $image_n;
                        CEventsTools::do_upload_event_image($name, $event_id, $timeCurrent, $addOnWall);
                    }
                // }

                CEventsTools::update_event($event_id);

                if ($isAjax) {
                    $url = Common::pageUrl('calendar', 0, $event_date);
                    die(getResponseDataAjaxByAuth(array('redirect' => $url)));
                } else {
                    redirect('events_event_show.php?event_id='.$event_id);
                }
            }
            if ($isAjax) {
                die(getResponseDataAjaxByAuth(array('error' => true)));
            } else {
                redirect('events.php');
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
        $isEventSocial = Common::isOptionActiveTemplate('event_social_enabled');
        $isEventSocial = false;

        $guid = guid();
        $event_private = intval(get_param('event_private')) ? 1 : 0;

        $event_id = get_param('event_id');
        $event = CEventsTools::retrieve_event_for_edit_by_id($event_id);

        $formatData = 'edit_event_date';
        if ($tmplName == 'oryx'){
            $formatTypeJS = 'edit_event_date';

        } elseif($tmplName == 'edge'){
            $formatTypeJS = 'task_date';
            $formatData = 'task_date';
        } else {
            $formatTypeJS = 'edit_event_date_mixer_js';
        }

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

        if($event){
            $event_private = $event['event_private'];
            $html->setvar('event_id', $event['event_id']);
            $html->setvar('event_title', he($event['event_title']));
            $html->setvar('event_description', $event['event_description']);
            $html->setvar('event_date', Common::dateFormat($event['event_datetime'], $formatData));
            $html->setvar('event_time', Common::dateFormat($event['event_datetime'], 'edit_event_time'));
            $html->setvar('event_address', $event['event_address']);
            $html->setvar('event_place', $event['event_place']);
            $html->setvar('event_site', $event['event_site']);
            $html->setvar('event_phone', $event['event_phone']);

            $html->setvar('edit_event_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_event_time',$g['date_formats']['edit_event_time']);
            $html->setvar('event_approval', $event['event_approval']);
            $html->setvar('signin_couples', $event['signin_couples']);
            $html->setvar('signin_females', $event['signin_females']);
            $html->setvar('signin_males', $event['signin_males']);
            $html->setvar('signin_transgender', $event['signin_transgender']);
            $html->setvar('signin_nonbinary', $event['signin_nonbinary']);
            $html->setvar('signin_everyone', $event['signin_everyone']);

            if (!$isEventSocial) {
                DB::query("SELECT * FROM events_event_image WHERE event_id=" . $event['event_id'] . " ORDER BY created_at ASC");
                $n_images = 0;
                while($image = DB::fetch_row())
                {
                    $html->setvar("image_id", $image['image_id']);
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_b.jpg");
                    $html->parse("image");
                    ++$n_images;
                }
                if($n_images)
                    $html->parse('edit_images');
            }
            if($event_private)
            {
                $html->parse('edit_private_title');
                $html->parse('edit_private_button');
            }
            else
            {
                $html->parse('edit_title');
                $html->parse('edit_button');
            }

            if ($isEventSocial) {
                $event_btn_create = l('btn_save');
                $event_btn_class = 'btn_edit';

                /*$images = CEventsTools::event_images($event['event_id'], false);
                $photoUrl = $images['image_file'];
                if (!$images['system']) {
                    $html->setvar('event_photo_id', $images['photo_id']);
                }
                $event_btn_upload_photo = $images['system'] ? l('choose_an_image') : l('use_another');*/
            }
        } elseif($event_id) {
            redirect('events.php');
        } else {
            $html->setvar('event_id', '');

            if (!$isEventSocial) {
                $html->setvar('event_title', l('event_title'));
                $html->setvar('event_description', l('no_description'));
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

            $html->setvar('event_date', htmlspecialchars(Common::dateFormat($date.' '.$hour.':'.$minute, $formatData)));
            $setTime = $isEventSocial ? '10:00' : $hour.':'.$minute;

            $html->setvar('event_time', Common::dateFormat($setTime, 'edit_event_time'));
            $html->setvar('edit_event_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_event_time',$g['date_formats']['edit_event_time']);

            //popcorn modified
            $html->setvar('event_approval', '0');
            $html->setvar('signin_couples', '1');
            $html->setvar('signin_females', '1');
            $html->setvar('signin_males', '1');
            $html->setvar('signin_everyone', '1');
            $html->setvar('signin_transgender', '1');
            $html->setvar('signin_nonbinary', '1');


            if ($isEventSocial) {
                $event_btn_create = l('btn_create');
                $event_btn_class = 'btn_create';

                /*$images = CEventsTools::event_images(0, false);
                $photoUrl = $images['image_file'];

                $event_btn_upload_photo = l('choose_an_image');*/
            }

            if($event_private) {
                $html->parse('create_private_title');
                $html->parse('create_private_button');
            } else {
                $html->parse('create_title');
                $html->parse('create_button');
            }
        }

        if ($isEventSocial) {
            //$html->setvar('event_photo_url', $photoUrl);

            //$html->parse('bl_photo_delete', false);

            $html->setvar('event_btn_class', $event_btn_class);
            $html->setvar('event_btn_create', $event_btn_create);
            //$html->setvar('event_photo_btn_upload', $event_btn_upload_photo);


            $friends = User::getListFriends($guid);
            foreach ($friends as $friend) {
                $html->setvar('list_friend_event_user_id', $friend['friend_id']);
                $html->setvar('list_friend_event_name', $friend['name']);
                $html->setvar('list_friend_event_photo', User::getPhotoDefault($friend['friend_id'], 's'));

                $html->parse('list_friend_event', true);
            }

        }


        $html->setvar('event_private', $event_private);

        $html->setvar("country_options", Common::listCountries($event ? $event['country_id'] : $g_user['country_id']));
        $html->setvar("state_options", Common::listStates($event ? $event['country_id'] : $g_user['country_id'], $event ? $event['state_id'] : $g_user['state_id']));
        $html->setvar("city_options", Common::listCities($event ? $event['state_id'] : $g_user['state_id'], $event ? $event['city_id'] : $g_user['city_id']));

        $settings = CEventsTools::settings();

        $category_options = '';
        DB::query("SELECT * FROM events_category ORDER BY category_id");
        $selected_category_id = $event ? $event['category_id'] : $settings['category_id'];
        while($category = DB::fetch_row())
        {
            if(!$selected_category_id)
                $selected_category_id = $category['category_id'];

            $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
            $category_options .= l($category['category_title'], false, 'events_category');
            $category_options .= '</option>';
        }
        $html->setvar("category_options", $category_options);

        if(!$event_private)
        {
            $html->parse('event_location');
            $html->parse('event_parameters');
        }

        $html->setvar('calendar_month', html_entity_decode(l('calendar_month'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays', html_entity_decode(l('calendar_weekdays'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays_short', html_entity_decode(l('calendar_weekdays_short'), ENT_QUOTES, 'UTF-8'));
        

        // TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
    }
}


$page = new CEvents("", getPageCustomTemplate('events_event_edit.html', 'events_event_edit_template'));

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

// if (Common::isParseModule('events_custom_head')) {
    $events_custom_head = new CEventsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_events_custom_head.html");
    $header->add($events_custom_head);
// }

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


// if (Common::isParseModule('events_header')) {
    $events_header = new CEventsHeader("events_header", $g['tmpl']['dir_tmpl_main'] . "_events_header.html");
    $page->add($events_header);
// }

// if (Common::isParseModule('events_sidebar')) {
    $events_sidebar = new CEventsSidebar("events_sidebar", $g['tmpl']['dir_tmpl_main'] . "_events_sidebar.html");
    $page->add($events_sidebar);
// }

include("./_include/core/main_close.php");