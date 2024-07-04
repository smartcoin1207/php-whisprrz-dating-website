<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";

include("./_include/core/main_start.php");


function redirectCalendar(){
    global $g;

    $url = $g['path']['base_url_main'] . Common::pageUrl('user_calendar', guid());
    redirect($url);
}

if (Common::isOptionActiveTemplate('event_social_enabled') && false) {
    if (!Common::isOptionActive('calendar_enabled', 'edge_events_settings')) {
        Common::redirectFromWithBaseUrl('profile_view');
    }

    $eventId = get_param('event_id');
    if ($p == 'calendar_task_edit.php' && !$eventId) {
        redirectCalendar();
    }
    $p = 'events_event_edit.php';
}

if (Common::isParseModule('events_custom_head')) {
    require_once("./_include/current/events/custom_head.php");
}

if (Common::isParseModule('events_header')) {
    require_once("./_include/current/events/header.php");
}

if (Common::isParseModule('events_sidebar')) {
    require_once("./_include/current/events/sidebar.php");
}

require_once("./_include/current/events/tools.php");

class CEvents1 extends CHtmlBlock
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
            $tmplName = 'oryx';
            $event_id = get_param('event_id');
            $event_private = intval(get_param('event_private')) ? 1 : 0;
            $event_user_to = 0;
            if ($isEventSocial) {
                $event_private = 1;
                $event_user_to = get_param_int('event_user_to');
                $event_user_to_name = trim(get_param('event_user_to_name'));
                if (!$event_user_to && $event_user_to_name) {
                    $event_user_to = DB::result("SELECT user_id FROM user WHERE name = " . to_sql($event_user_to_name));
                }
                if (!$event_user_to) {
                    $event_user_to = $g_user['user_id'];
                }
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

            $isSaveEvent = $event_title && $event_date && $event_time;
            if ($tmplName != 'edge') {
                $isSaveEvent = $isSaveEvent && $event_description;
            }
            if($isSaveEvent) {
                $timeCurrent = date("Y-m-d H:i:s");

                $formatDate = $g['date_formats']['event_date'];
                $formatTime = $g['date_formats']['edit_event_time'];
                if($tmplName == 'edge'){
                    $formatDate = 'Y-m-d';
                    $formatTime = $g['date_formats']['task_time'];
                }

                $format = str_replace("|", "?", $formatDate);
                $date = date_create_from_format($format, $event_date);
                $event_date = date_format($date, 'Y-m-d');

                $format = str_replace("|", "?", $formatTime);
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
                $time = DateTime::createFromFormat($format, $event_time, $zone);

                $event_time = date_format($time, 'H:i');

                $dt=DateTime::createFromFormat('Y-m-d H:i', $event_date.' '.$event_time, $zone);
                if(Common::getOption('timezone', 'main')){
                    $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
                } else {
                    $zone = new DateTimeZone(date_default_timezone_get());
                }
                $dt->setTimezone($zone);


                $event_description = Common::filter_text_to_db($event_description, false);
                if($event_id) {
                    $event_id_exists = true;
                    if(!CEventsTools::retrieve_event_for_edit_by_id($event_id)){
                        if ($isAjax) {
                            die(getResponseDataAjaxByAuth(array('error' => true)));
                        } else {
                            redirect('events.php');
                        }
                    }

                    DB::execute("UPDATE events_event SET " .
                                " user_to = ".to_sql($event_user_to, 'Number').
                                ", category_id=".to_sql($category_id, 'Number').
                                ", event_private=".to_sql($event_private, 'Number').
                                ", event_title=".to_sql($event_title).
                                ", event_description=".to_sql($event_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", event_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", event_address=".to_sql($event_address).
                                ", event_place=".to_sql($event_place).
                                ", event_site=".to_sql($event_site).
                                ", event_phone=".to_sql($event_phone).
                                ", updated_at = NOW() WHERE event_id=" . to_sql($event_id, 'Number') . " LIMIT 1");
                } else {
                    DB::execute("INSERT INTO events_event SET ".
                                " user_id=".to_sql($g_user['user_id'], 'Number').
                                ", user_to = ".to_sql($event_user_to, 'Number').
                                ", category_id=".to_sql($category_id, 'Number').
                                ", event_private=".to_sql($event_private, 'Number').
                                ", event_title=".to_sql($event_title).
                                ", event_description=".to_sql($event_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", event_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", event_address=".to_sql($event_address).
                                ", event_place=".to_sql($event_place).
                                ", event_site=".to_sql($event_site).
                                ", event_phone=".to_sql($event_phone).
                                ", created_at = " . to_sql($timeCurrent, 'Text') .
                            ", updated_at = " . to_sql($timeCurrent, 'Text').
                            ""
                            );


                    $event_id = DB::insert_id();
                    CStatsTools::count('events_created');

                    CEventsTools::create_event_guest($event_id, 0);
                    if(!$event_private){
                        Wall::add('event_added', $event_id);
                    }
                }
                DB::update('userinfo', array('create_task_user_id' => $event_user_to), 'user_id = ' . to_sql(guid()));

                $addOnWall = isset($event_id_exists) ? true : false;

                if ($isEventSocial) {
                    /*$eventPhotoId = get_param_int('event_photo_id');
                    if(isset($event_id_exists) && !$eventPhotoId){
                        CEventsTools::delete_event_image_all($event_id);
                    }
                    $imageTempId = get_param_int('event_photo_id');
                    if ($imageTempId) {
                        $imageTemp = Common::getOption('dir_files', 'path') . 'temp/tmp_event_' . $imageTempId . '.jpg';
                        CEventsTools::do_upload_event_image('', $event_id, $timeCurrent, $addOnWall, $imageTemp);
                    }*/
                } else {
                    for($image_n = 1; $image_n <= 4; ++$image_n){
                        $name = "image_" . $image_n;
                        CEventsTools::do_upload_event_image($name, $event_id, $timeCurrent, $addOnWall);
                    }
                }

                CEventsTools::update_event($event_id);

                if ($isAjax) {
                    $url = Common::pageUrl('user_calendar', $event_user_to, $event_date, array('task_id' => $event_id), $event_id);

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
        global $p;
        $tmplName = Common::getTmplName();
        $tmplName = 'oryx';
        $isEventSocial = Common::isOptionActiveTemplate('event_social_enabled');

        $guid = guid();
        $uid = User::getParamUid();
        $event_private = intval(get_param('event_private')) ? 1 : 0;

        $event_id = get_param('event_id');
        $event = CEventsTools::retrieve_event_for_edit_by_id($event_id);

        $formatTime = 'edit_event_time';
        $formatData = 'event_date';
        if ($tmplName == 'oryx'){
            $formatTypeJS = 'edit_event_date';
        } else {
            $formatTypeJS = 'edit_event_date_mixer_js';
        }
        $dateFormatJs = $g['date_formats'][$formatTypeJS];

        $isFormatDate = false;
        if ($tmplName == 'edge') {
            $formatTime = 'task_time';
            $dateFormatJs = l('calendar_format_php');
            $formatData = $dateFormatJs;
            $isFormatDate = true;
        }

        if($event){
            $event_private = $event['event_private'];
        	$html->setvar('event_id', $event['event_id']);
            $html->setvar('event_title', he($event['event_title']));
            $html->setvar('event_description', $event['event_description']);
            if ($html->blockExists('event_description_show')) {
                if (trim($event['event_description'])) {
                    $html->parse('event_description_show', false);
                    $html->setvar('event_description_btn_expand', l('click_to_collapse'));
                } else {
                    $html->setvar('event_description_btn_expand', l('click_to_expand'));
                }
            }
            $html->setvar('event_date', Common::dateFormat($event['event_datetime'], $formatData, true, false, false, false, $isFormatDate));

            $date = DateTime::createFromFormat('Y-m-d H:i:s', $event['event_datetime']);
            $date = $date->format('Y-m-d');

            $dateJs = date_create_from_format('Y-m-d H:i:s', $event['event_datetime']);
            $html->setvar('event_date_js', date_format($dateJs, 'Y-m-d'));
            $html->setvar('event_time', Common::dateFormat($event['event_datetime'], $formatTime));
            $html->setvar('event_address', $event['event_address']);
            $html->setvar('event_place', $event['event_place']);
            $html->setvar('event_site', $event['event_site']);
            $html->setvar('event_phone', $event['event_phone']);

            $html->setvar('edit_event_date', $dateFormatJs);
            $html->setvar('edit_event_time', $g['date_formats'][$formatTime]);

            if ($isEventSocial) {
                $pageTitle = l('edit_task');
                $event_user_to = $event['user_to'];
                $event_btn_create = l('btn_save');
                $event_btn_class = 'btn_edit';
                $event_btn_disabled = '';
            } else {
                DB::query("SELECT * FROM events_event_image WHERE event_id=" . $event['event_id'] . " ORDER BY created_at ASC");
                $n_images = 0;
                while($image = DB::fetch_row()){
                    $html->setvar("image_id", $image['image_id']);
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_b.jpg");
                    $html->parse("image");
                    ++$n_images;
                }
                if($n_images){
                    $html->parse('edit_images');
                }
                if ($event_private) {
                    $html->parse('edit_private_title');
                    $html->parse('edit_private_button');
                } else {
                    $html->parse('edit_title');
                    $html->parse('edit_button');
                }
            }
        } elseif($event_id) {
            if ($isEventSocial) {
                redirectCalendar();
            } else {
                redirect('events.php');
            }
        } else {
            if ($isEventSocial) {
                $pageTitle = l('create_new_task');
                $event_private = 1;
                $event_user_to = $uid;
                $html->setvar('event_description_btn_expand', l('click_to_expand'));
            } else {
                $html->setvar('event_title', l('event_title'));
                $html->setvar('event_description', l('no_description'));
            }

            $curDate = date('Y-m-d');
            $date = get_param('date', $curDate);
            $hour = date("H");
            $minute = date("i");

            if ($isEventSocial && false) {
                $minute = ceil($minute/10)*10;
                $hour = str_pad((int)$hour + 1, 2, '0', STR_PAD_LEFT);
            } else {
                if ($curDate == $date) {
                    if((int)$minute>0 && (int)$minute<30){
                        $minute = "30";
                    } elseif((int)$minute>30){
                        $minute = "00";
                        $hour = str_pad((int)$hour+1, 2, '0', STR_PAD_LEFT);
                    }
                } else {
                    $hour = '10';
                    $minute = '00';
                    /*$sql_base = TaskCalendar::getSqlTasksByDay($date, '', $uid, false);
                    $eventsCurDay = CEventsTools::retrieve_from_sql_base($sql_base, 1, 0);*/
                }
            }

            $html->setvar('event_date', htmlspecialchars(Common::dateFormat($date.' '.$hour.':'.$minute, $formatData, true, false, false, false, $isFormatDate)));

            // var_dump(htmlspecialchars(Common::dateFormat($date, 'event_date', true, false, false, false, $isFormatDate))); die();
            // $html->setvar('event_date', '03/08/24');
            $html->setvar('event_date_js', $date);
            $setTime = $hour.':'.$minute;
            $html->setvar('event_time', Common::dateFormat($setTime, $formatTime));
            // var_dump($dateFormatJs); die();
            $html->setvar('edit_event_date', $dateFormatJs);
            $html->setvar('edit_event_time', $g['date_formats'][$formatTime]);

            if ($isEventSocial) {
                $event_btn_create = l('btn_create');
                $event_btn_class = 'btn_create';
                $event_btn_disabled = 'disabled';

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
            if (get_param_int('show_back')) {
                $html->setvar('url_back', Common::pageUrl('user_calendar', $event_user_to, $date));
                $html->parse('page_url_back', false);
            }

            $html->setvar('page_title', $pageTitle);

            $html->subcond($event_private, 'private_access', 'public_access');

            $html->setvar('event_btn_class', $event_btn_class);
            $html->setvar('event_btn_disabled', $event_btn_disabled);
            $html->setvar('event_btn_create', $event_btn_create);

            if ($event_user_to == $guid && !get_param('date')){
                $userInfo = User::getInfoFull($guid);
                if ($userInfo['create_task_user_id']) {
                    $event_user_to = $userInfo['create_task_user_id'];
                }
            }

            if ($event_user_to && $event_user_to != $guid) {
                $userToName = User::getInfoBasic($event_user_to, 'name');
            } else {
                $event_user_to = $guid;
                $userToName = l('myself');
            }

            $friends = array(0 => array(
                'user_id' => $guid,
                'name' => l('me'),
            ));

            $friendsUser = User::getListFriends($guid);
            if ($friendsUser) {
                $friends = array_merge($friends, $friendsUser);
            }

            if ($friends) {
                $blockFriend = 'list_friend_event';
                foreach ($friends as $friend) {
                    $fid = $friend['user_id'];
                    $userName = $friend['user_id'] == $guid ? l('myself') : $friend['name'];
                    if ($fid == $event_user_to) {
                        $html->parse("{$blockFriend}_selected", false);
                    } else {
                        $html->clean("{$blockFriend}_selected");
                    }
                    $info = array(
                        'user_id' => $fid,
                        'name'    => toAttr($userName),
                        'name_title'   => toAttr($friend['name']),
                        'photo'   => User::getPhotoDefault($fid, 's')
                    );
                    $html->assign($blockFriend, $info);
                    $html->parse("{$blockFriend}_item", true);
                }
                $html->parse("{$blockFriend}_show", false);
                $html->parse($blockFriend, false);
            }

            $userToInfo = array(
                'id' => $event_user_to,
                'name' => $userToName
            );
            $html->assign('user_to', $userToInfo);
        }

        $html->setvar('event_private', $event_private);

        if (!$isEventSocial) {
            $html->setvar("country_options", Common::listCountries($event ? $event['country_id'] : $g_user['country_id']));
            $html->setvar("state_options", Common::listStates($event ? $event['country_id'] : $g_user['country_id'], $event ? $event['state_id'] : $g_user['state_id']));
            $html->setvar("city_options", Common::listCities($event ? $event['state_id'] : $g_user['state_id'], $event ? $event['city_id'] : $g_user['city_id']));

            $settings = CEventsTools::settings();

            $category_options = '';
            DB::query("SELECT * FROM events_category ORDER BY category_id");
            $selected_category_id = $event ? $event['category_id'] : $settings['category_id'];
            while($category = DB::fetch_row()) {
                if(!$selected_category_id)
                    $selected_category_id = $category['category_id'];

                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], false, 'events_category');
                $category_options .= '</option>';
            }
            $html->setvar("category_options", $category_options);
        }

        if(!$event_private) {
            $html->parse('event_location');
            $html->parse('event_parameters');
        }

        $html->setvar('calendar_month', html_entity_decode(l('calendar_month'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays', html_entity_decode(l('calendar_weekdays'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays_short', html_entity_decode(l('calendar_weekdays_short'), ENT_QUOTES, 'UTF-8'));

        TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
	}
}

$tmpl = getPageCustomTemplate('events_event_edit.html', 'events_event_edit_template');
if (get_param_int('upload_page_content_ajax')) {
    $tmpl = $g['tmpl']['dir_tmpl_main'] . '_calendar_edit_task.html';
}

$tmpl = $g['tmpl']['dir_tmpl_main'] . 'events_event_edit_task.html';

$page = new CEvents1("", $tmpl);

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

if (Common::isParseModule('events_custom_head')) {
    $events_custom_head = new CEventsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_events_custom_head.html");
    $header->add($events_custom_head);
}

$page->add($header);

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

if (Common::isParseModule('events_header')) {
    $events_header = new CEventsHeader("events_header", $g['tmpl']['dir_tmpl_main'] . "_events_header.html");
    $page->add($events_header);
}

if (Common::isParseModule('events_sidebar')) {
    $events_sidebar = new CEventsSidebar("events_sidebar", $g['tmpl']['dir_tmpl_main'] . "_events_sidebar.html");
    $page->add($events_sidebar);
}

loadPageContentAjax($page);

include("./_include/core/main_close.php");