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

    $url = $g['path']['base_url_main'] . Common::pageUrl('user_hotdate_calendar', guid());
    redirect($url);
}


// if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
//     if (!Common::isOptionActive('calendar_enabled', 'edge_hotdates_settings')) {
//         die();
//         Common::redirectFromWithBaseUrl('profile_view');
//     }

//     $hotdateId = get_param('hotdate_id');
//     if ($p == 'calendar_task_hotdate_edit.php' && !$hotdateId) {
//         redirectCalendar();
//     }
//     $p = 'hotdates_hotdate_task_edit.php';
// }
if (Common::isParseModule('hotdates_custom_head')) {
    require_once("./_include/current/hotdates/custom_head.php");
}

if (Common::isParseModule('hotdates_header')) {
    require_once("./_include/current/hotdates/header.php");
}

if (Common::isParseModule('hotdates_sidebar')) {
    require_once("./_include/current/hotdates/sidebar.php");
}

require_once("./_include/current/hotdates/tools.php");


class CHotdates1 extends CHtmlBlock
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
            $hotdate_id = get_param('hotdate_id');
            $hotdate_private = intval(get_param('hotdate_private')) ? 1 : 0;
            $hotdate_user_to = 0;
            if ($isHotdateSocial) {
                $hotdate_private = 1;
                $hotdate_user_to = get_param_int('hotdate_user_to');
                $hotdate_user_to_name = trim(get_param('hotdate_user_to_name'));
                if (!$hotdate_user_to && $hotdate_user_to_name) {
                    $hotdate_user_to = DB::result("SELECT user_id FROM user WHERE name = " . to_sql($hotdate_user_to_name));
                }
                if (!$hotdate_user_to) {
                    $hotdate_user_to = $g_user['user_id'];
                }
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

            $isSaveHotdate = $hotdate_title && $hotdate_date && $hotdate_time;
            if ($tmplName != 'edge') {
                $isSaveHotdate = $isSaveHotdate && $hotdate_description;
            }
            if($isSaveHotdate) {
                $timeCurrent = date("Y-m-d H:i:s");

                $formatDate = $g['date_formats']['edit_hotdate_date'];
                $formatTime = $g['date_formats']['edit_hotdate_time'];
                if($tmplName == 'edge'){
                    $formatDate = 'Y-m-d';
                    $formatTime = $g['date_formats']['task_time'];
                }

                $format = str_replace("|", "?", $formatDate);
                $date = date_create_from_format($format, $hotdate_date);
                $hotdate_date = date_format($date, 'Y-m-d');

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
                $time = DateTime::createFromFormat($format, $hotdate_time, $zone);

                $hotdate_time = date_format($time, 'H:i');

                $dt=DateTime::createFromFormat('Y-m-d H:i', $hotdate_date.' '.$hotdate_time, $zone);
                if(Common::getOption('timezone', 'main')){
                    $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
                } else {
                    $zone = new DateTimeZone(date_default_timezone_get());
                }
                $dt->setTimezone($zone);


                $hotdate_description = Common::filter_text_to_db($hotdate_description, false);
                if($hotdate_id) {
                    $hotdate_id_exists = true;
                    if(!CHotdatesTools::retrieve_hotdate_for_edit_by_id($hotdate_id)){
                        if ($isAjax) {
                            die(getResponseDataAjaxByAuth(array('error' => true)));
                        } else {
                            redirect('hotdates.php');
                        }
                    }

                    DB::execute("UPDATE hotdates_hotdate SET " .
                                " user_to = ".to_sql($hotdate_user_to, 'Number').
                                ", category_id=".to_sql($category_id, 'Number').
                                ", hotdate_private=".to_sql($hotdate_private, 'Number').
                                ", hotdate_title=".to_sql($hotdate_title).
                                ", hotdate_description=".to_sql($hotdate_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", hotdate_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", hotdate_address=".to_sql($hotdate_address).
                                ", hotdate_place=".to_sql($hotdate_place).
                                ", hotdate_site=".to_sql($hotdate_site).
                                ", hotdate_phone=".to_sql($hotdate_phone).
                                ", updated_at = NOW() WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1");
                } else {
                    DB::execute("INSERT INTO hotdates_hotdate SET ".
                                " user_id=".to_sql($g_user['user_id'], 'Number').
                                ", user_to = ".to_sql($hotdate_user_to, 'Number').
                                ", category_id=".to_sql($category_id, 'Number').
                                ", hotdate_private=".to_sql($hotdate_private, 'Number').
                                ", hotdate_title=".to_sql($hotdate_title).
                                ", hotdate_description=".to_sql($hotdate_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", hotdate_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", hotdate_address=".to_sql($hotdate_address).
                                ", hotdate_place=".to_sql($hotdate_place).
                                ", hotdate_site=".to_sql($hotdate_site).
                                ", hotdate_phone=".to_sql($hotdate_phone).
                                ", created_at = " . to_sql($timeCurrent, 'Text') .
                            ", updated_at = " . to_sql($timeCurrent, 'Text').
                            ""
                            );


                    $hotdate_id = DB::insert_id();
                    CStatsTools::count('hotdates_created');

                    CHotdatesTools::create_hotdate_guest($hotdate_id, 0);
                    if(!$hotdate_private){
                        Wall::add('hotdate_added', $hotdate_id);
                    }
                }
                DB::update('userinfo', array('create_task_user_id' => $hotdate_user_to), 'user_id = ' . to_sql(guid()));

                $addOnWall = isset($hotdate_id_exists) ? true : false;

                if ($isHotdateSocial) {
                    /*$hotdatePhotoId = get_param_int('hotdate_photo_id');
                    if(isset($hotdate_id_exists) && !$hotdatePhotoId){
                        CHotdatesTools::delete_hotdate_image_all($hotdate_id);
                    }
                    $imageTempId = get_param_int('hotdate_photo_id');
                    if ($imageTempId) {
                        $imageTemp = Common::getOption('dir_files', 'path') . 'temp/tmp_hotdate_' . $imageTempId . '.jpg';
                        CHotdatesTools::do_upload_hotdate_image('', $hotdate_id, $timeCurrent, $addOnWall, $imageTemp);
                    }*/
                } else {
                    for($image_n = 1; $image_n <= 4; ++$image_n){
                        $name = "image_" . $image_n;
                        CHotdatesTools::do_upload_hotdate_image($name, $hotdate_id, $timeCurrent, $addOnWall);
                    }
                }

                CHotdatesTools::update_hotdate($hotdate_id);

                if ($isAjax) {
                    $url = Common::pageUrl('user_hotdate_calendar', $hotdate_user_to, $hotdate_date, array('task_id' => $hotdate_id), $hotdate_id);

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
        $isHotdateSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');

        $guid = guid();
        $uid = User::getParamUid();
        $hotdate_private = intval(get_param('hotdate_private')) ? 1 : 0;

        $hotdate_id = get_param('hotdate_id');
        $hotdate = CHotdatesTools::retrieve_hotdate_for_edit_by_id($hotdate_id);

        $formatTime = 'edit_hotdate_time';
        $formatData = 'edit_hotdate_date';
        if ($tmplName == 'oryx'){
            $formatTypeJS = 'edit_hotdate_date';
        } else {
            $formatTypeJS = 'edit_hotdate_date_mixer_js';
        }
        $dateFormatJs = $g['date_formats'][$formatTypeJS];

        $isFormatDate = false;
        if ($tmplName == 'edge') {
            $formatTime = 'task_time';
            $dateFormatJs = l('calendar_format_php');
            $formatData = $dateFormatJs;
            $isFormatDate = true;
        }

        if($hotdate){
            $hotdate_private = $hotdate['hotdate_private'];
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', he($hotdate['hotdate_title']));
            $html->setvar('hotdate_description', $hotdate['hotdate_description']);
            if ($html->blockExists('hotdate_description_show')) {
                if (trim($hotdate['hotdate_description'])) {
                    $html->parse('hotdate_description_show', false);
                    $html->setvar('hotdate_description_btn_expand', l('click_to_collapse'));
                } else {
                    $html->setvar('hotdate_description_btn_expand', l('click_to_expand'));
                }
            }
            $html->setvar('hotdate_date', Common::dateFormat($hotdate['hotdate_datetime'], $formatData, true, false, false, false, $isFormatDate));

            $date = DateTime::createFromFormat('Y-m-d H:i:s', $hotdate['hotdate_datetime']);
            $date = $date->format('Y-m-d');

            $dateJs = date_create_from_format('Y-m-d H:i:s', $hotdate['hotdate_datetime']);
            $html->setvar('hotdate_date_js', date_format($dateJs, 'Y-m-d'));
            $html->setvar('hotdate_time', Common::dateFormat($hotdate['hotdate_datetime'], $formatTime));
            $html->setvar('hotdate_address', $hotdate['hotdate_address']);
            $html->setvar('hotdate_place', $hotdate['hotdate_place']);
            $html->setvar('hotdate_site', $hotdate['hotdate_site']);
            $html->setvar('hotdate_phone', $hotdate['hotdate_phone']);

            $html->setvar('edit_hotdate_date', $dateFormatJs);
            $html->setvar('edit_hotdate_time', $g['date_formats'][$formatTime]);

            if ($isHotdateSocial) {
                $pageTitle = l('edit_task');
                $hotdate_user_to = $hotdate['user_to'];
                $hotdate_btn_create = l('btn_save');
                $hotdate_btn_class = 'btn_edit';
                $hotdate_btn_disabled = '';
            } else {
                DB::query("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=" . $hotdate['hotdate_id'] . " ORDER BY created_at ASC");
                $n_images = 0;
                while($image = DB::fetch_row()){
                    $html->setvar("image_id", $image['image_id']);
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_b.jpg");
                    $html->parse("image");
                    ++$n_images;
                }
                if($n_images){
                    $html->parse('edit_images');
                }
                if ($hotdate_private) {
                    $html->parse('edit_private_title');
                    $html->parse('edit_private_button');
                } else {
                    $html->parse('edit_title');
                    $html->parse('edit_button');
                }
            }
        } elseif($hotdate_id) {
            if ($isHotdateSocial) {
                redirectCalendar();
            } else {
                redirect('hotdates.php');
            }
        } else {
            if ($isHotdateSocial) {
                $pageTitle = l('create_new_task');
                $hotdate_private = 1;
                $hotdate_user_to = $uid;
                $html->setvar('hotdate_description_btn_expand', l('click_to_expand'));
            } else {
                $html->setvar('hotdate_title', l('hotdate_title'));
                $html->setvar('hotdate_description', l('no_description'));
            }

            $curDate = date('Y-m-d');
            $date = get_param('date', $curDate);
            $hour = date("H");
            $minute = date("i");

            if ($isHotdateSocial && false) {
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
                    $hotdatesCurDay = CHotdatesTools::retrieve_from_sql_base($sql_base, 1, 0);*/
                }
            }

            $html->setvar('hotdate_date', htmlspecialchars(Common::dateFormat($date.' '.$hour.':'.$minute, $formatData, true, false, false, false, $isFormatDate)));
            $html->setvar('hotdate_date_js', $date);
            $setTime = $hour.':'.$minute;
            $html->setvar('hotdate_time', Common::dateFormat($setTime, $formatTime));
            $html->setvar('edit_hotdate_date', $dateFormatJs);
            $html->setvar('edit_hotdate_time', $g['date_formats'][$formatTime]);

            if ($isHotdateSocial) {
                $hotdate_btn_create = l('btn_create');
                $hotdate_btn_class = 'btn_create';
                $hotdate_btn_disabled = 'disabled';

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
            if (get_param_int('show_back')) {
                $html->setvar('url_back', Common::pageUrl('user_calendar', $hotdate_user_to, $date));
                $html->parse('page_url_back', false);
            }

            $html->setvar('page_title', $pageTitle);

            $html->subcond($hotdate_private, 'private_access', 'public_access');

            $html->setvar('hotdate_btn_class', $hotdate_btn_class);
            $html->setvar('hotdate_btn_disabled', $hotdate_btn_disabled);
            $html->setvar('hotdate_btn_create', $hotdate_btn_create);

            if ($hotdate_user_to == $guid && !get_param('date')){
                $userInfo = User::getInfoFull($guid);
                if ($userInfo['create_task_user_id']) {
                    $hotdate_user_to = $userInfo['create_task_user_id'];
                }
            }

            if ($hotdate_user_to && $hotdate_user_to != $guid) {
                $userToName = User::getInfoBasic($hotdate_user_to, 'name');
            } else {
                $hotdate_user_to = $guid;
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
                $blockFriend = 'list_friend_hotdate';
                foreach ($friends as $friend) {
                    $fid = $friend['user_id'];
                    $userName = $friend['user_id'] == $guid ? l('myself') : $friend['name'];
                    if ($fid == $hotdate_user_to) {
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
                'id' => $hotdate_user_to,
                'name' => $userToName
            );
            $html->assign('user_to', $userToInfo);
        }

        $html->setvar('hotdate_private', $hotdate_private);

        if (!$isHotdateSocial) {
            $html->setvar("country_options", Common::listCountries($hotdate ? $hotdate['country_id'] : $g_user['country_id']));
            $html->setvar("state_options", Common::listStates($hotdate ? $hotdate['country_id'] : $g_user['country_id'], $hotdate ? $hotdate['state_id'] : $g_user['state_id']));
            $html->setvar("city_options", Common::listCities($hotdate ? $hotdate['state_id'] : $g_user['state_id'], $hotdate ? $hotdate['city_id'] : $g_user['city_id']));

            $settings = CHotdatesTools::settings();

            $category_options = '';
            DB::query("SELECT * FROM hotdates_category ORDER BY category_id");
            $selected_category_id = $hotdate ? $hotdate['category_id'] : $settings['category_id'];
            while($category = DB::fetch_row()) {
                if(!$selected_category_id)
                    $selected_category_id = $category['category_id'];

                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], false, 'hotdates_category');
                $category_options .= '</option>';
            }
            $html->setvar("category_options", $category_options);
        }

        if(!$hotdate_private) {
            $html->parse('hotdate_location');
            $html->parse('hotdate_parameters');
        }

        $html->setvar('calendar_month', html_entity_decode(l('calendar_month'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays', html_entity_decode(l('calendar_weekdays'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays_short', html_entity_decode(l('calendar_weekdays_short'), ENT_QUOTES, 'UTF-8'));

        TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
    }
}


$tmpl = array(
    'main' => $g['tmpl']['dir_tmpl_main']."hotdates_hotdate_edit_task.html",
);

$page = new CHotdates1("", $tmpl);


$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

// if (Common::isParseModule('hotdates_custom_head')) {
//     $hotdates_custom_head = new CHotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
//     $header->add($hotdates_custom_head);
// }

$page->add($header);

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

// if (Common::isParseModule('hotdates_header')) {
//     $hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
//     $page->add($hotdates_header);
// }

// if (Common::isParseModule('hotdates_sidebar')) {
//     $hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
//     $page->add($hotdates_sidebar);
// }

loadPageContentAjax($page);

include("./_include/core/main_close.php");