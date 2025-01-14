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

if(!Common::isOptionActive('wowslider')) {
    redirect(Common::getHomePage());
}


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

            $event_id = get_param('event_id');
            $city_id = intval(get_param('city_id', ($g_user['city_id'] == 0) ? 1 : $g_user['city_id']));
            $state_id = intval(get_param('state', ($g_user['state_id'] == 0) ? 1 : $g_user['state_id']));
            $country_id = intval(get_param('country', ($g_user['country_id'] == 0) ? 1 : $g_user['country_id']));
            $zipcode = get_param('zipcode');

            $file_name = "";
            if ($_FILES['image_1']['name'] != ""){
                $target_dir = $g['path']['url_files']."wowslider/";
                //popcorn modified s3 bucket wowslider 2024-05-06
                if(getFileDirectoryType('wowslider') == 2) {
                    $target_dir = $g['path']['url_files']."temp/wowslider/";
                }

                $file_name=Date("U").".jpg";
                $target_file = $target_dir . $file_name;
                move_uploaded_file($_FILES["image_1"]["tmp_name"], $target_file);
                
                //popcorn modified s3 bucket wowslider 2024-05-06
                if(getFileDirectoryType('wowslider') == 2) {
                    custom_file_upload($target_file, "wowslider/" . $file_name);
                }
            }

            $event_date = get_param('from_date');
            $event_time = get_param('from_time');

            $formatJS = $g['date_formats']['edit_event_time'];

            $formatData = 'edit_event_date';
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

            $fdt=DateTime::createFromFormat('Y-m-d H:i', $event_date.' '.$event_time, $zone);
            if(Common::getOption('timezone', 'main')){
                $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
            } else {
                $zone = new DateTimeZone(date_default_timezone_get());
            }
            $fdt->setTimezone($zone);

            $event_date = get_param('end_date');
            $event_time = get_param('end_time');

            $formatJS = $g['date_formats']['edit_event_time'];

            $formatData = 'edit_event_date';
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

            $edt=DateTime::createFromFormat('Y-m-d H:i', $event_date.' '.$event_time, $zone);
            if(Common::getOption('timezone', 'main')){
                $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
            } else {
                $zone = new DateTimeZone(date_default_timezone_get());
            }
            $edt->setTimezone($zone);

            $country = "";$state="";$city="";
            $country_v = DB::row("SELECT * FROM geo_country WHERE country_id='".$country_id."' LIMIT 1");
            $state_v = DB::row("SELECT * FROM geo_state WHERE state_id='".$state_id."' LIMIT 1");
            $city_v = DB::row("SELECT * FROM geo_city WHERE city_id='".$city_id."' LIMIT 1");
            if(!is_null($country_v['country_title']))
                $country = $country_v['country_title'];
            if(!is_null($state_v['state_title']))
                $state = $state_v['state_title'];
            if(!is_null($city_v['city_title']))
                $city = $city_v['city_title'];
    
            if($event_id)
            {
                $event_id_exists = true;
                DB::execute("UPDATE wowslider SET " .
                    " zipcode=".to_sql($zipcode).
                    (($file_name=="")?"":(", img_path=".to_sql($file_name))).
                    ", link=".to_sql(get_param('link')).
                    ", title=".to_sql(get_param('title')).
                    ", description=".to_sql(get_param('event_description')).
                    ", from_datetime=".to_sql(date_format($fdt, 'Y-m-d H:i:s')).
                    ", end_datetime=".to_sql(date_format($edt, 'Y-m-d H:i:s')).
                    ", city_id=".to_sql($city_id, 'Number').
                    ", city='".$city."'".
                    ", state='".$state."'".
                    ", country='".$country."'".
                    ", country_id=".to_sql($country_id, 'Number').
                    ", state_id=".to_sql($state_id, 'Number').
                    " WHERE event_id=" . to_sql($event_id, 'Number') . " LIMIT 1");
                //redirect('wowslider.php');
            }else{
                $wowslider_approved = Common::isOptionActive('wowslider_approval') ? 0 : 1 ;
                
                DB::execute("INSERT INTO wowslider SET ".
                    " user_id=".to_sql($g_user['user_id'], 'Number').
                    ", zipcode=".to_sql($zipcode).
                    ", img_path=".to_sql($file_name).
                    ", link=".to_sql(get_param('link')).
                    ", title=".to_sql(get_param('title')).
                    ", description=".to_sql(get_param('event_description')).
                    ", from_datetime=".to_sql(date_format($fdt, 'Y-m-d H:i:s')).
                    ", end_datetime=".to_sql(date_format($edt, 'Y-m-d H:i:s')).
                    ", city_id=".to_sql($city_id, 'Number').
                    ", city='".$city."'".
                    ", state='".$state."'".
                    ", country='".$country."'".
                    ", country_id=".to_sql($country_id, 'Number').
                    ", state_id=".to_sql($state_id, 'Number').
                    ", approved=".to_sql($wowslider_approved, 'Number').
                    ", is_check=0".
                ""
                );
                $event_id = DB::insert_id();
            }
            
            $wevent_id = $event_id;
            $tmplName = Common::getTmplName();
            $event_id = $wevent_id;
            $event_private = intval(get_param('event_private')) ? 1 : 0;
            $event_access = 'P';
            if ($isEventSocial) {
                $event_access = get_param('event_access');
            }

            $category_id = 7;
            $event_title = get_param('title');
            $event_description = get_param('event_description');

            $city_id = intval(get_param('city_id', ($g_user['city_id'] == 0) ? 1 : $g_user['city_id']));
            //$event_date = get_param('event_date');
            //$event_time = get_param('event_time');
            $event_address = get_param('event_address');
            $event_place = get_param('event_place');
            $event_site = get_param('link');
            $event_phone = get_param('event_phone');

            $isSaveEvent = $event_title && $event_date && $event_time;
            if ($tmplName != 'edge') {
                $isSaveEvent = $isSaveEvent && $event_description;
            }
            $isSaveEvent = 1;
            if($isSaveEvent) {
                $timeCurrent = date("Y-m-d H:i:s");
                $formatJS = $g['date_formats']['edit_event_time'];

                $formatData = 'edit_event_date';
                if($tmplName == 'edge'){
                    $formatData = 'task_date';
                }
 
                $dt=DateTime::createFromFormat('Y-m-d H:i', $event_date.' '.$event_time, $zone);
                if(Common::getOption('timezone', 'main')){
                    $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
                } else {
                    $zone = new DateTimeZone(date_default_timezone_get());
                }
                $dt->setTimezone($zone);  
                $event = DB::row("SELECT * FROM events_event WHERE wevent_id=" . to_sql($wevent_id, 'Number') . " LIMIT 1");
                if($event && $event_id_exists)
                {
                    $event_id_exists = true;                    
                    DB::execute("UPDATE events_event SET " .
                                " category_id=".to_sql($category_id, 'Number').
                                ", event_private=".to_sql($event_private, 'Number').
                                ", access_private=".to_sql($event_access).
                                ", event_title=".to_sql($event_title).
                                ", event_description=".to_sql($event_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", event_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", event_address=".to_sql($event_address).
                                ", event_site=".to_sql($event_site).
                                ", updated_at = NOW() WHERE wevent_id=" . to_sql($wevent_id, 'Number') . " LIMIT 1");
                }
                else
                {
                    DB::execute("INSERT INTO events_event SET ".
                                " user_id=".to_sql($g_user['user_id'], 'Number').
                                ", category_id=".to_sql($category_id, 'Number').
                                ", event_private=".to_sql($event_private, 'Number').
                                ", access_private=".to_sql($event_access).
                                ", event_title=".to_sql($event_title).
                                ", event_description=".to_sql($event_description).
                                ", city_id=".to_sql($city_id, 'Number').
                                ", event_datetime=".to_sql(date_format($dt, 'Y-m-d H:i:s')).
                                ", event_address=".to_sql($event_address).
                                ", event_site=".to_sql($event_site).
                                ", wevent_id=".to_sql($wevent_id, 'Number').
                                ", created_at = " . to_sql($timeCurrent, 'Text') .
                            ", updated_at = " . to_sql($timeCurrent, 'Text').
                            ", approved = 1".
                            ""
                            );


                    $event_id = DB::insert_id();
                    CStatsTools::count('events_created');

                    CEventsTools::create_event_guest($event_id, 0);
                    if(!$event_private){
                        Wall::add('event_added', $event_id);
                    }

                }

                $addOnWall = isset($event_id_exists) ? true : false;

                if (Common::isOptionActiveTemplate('event_social_enabled')) {
                    $eventPhotoId = get_param_int('event_photo_id');
                    if(isset($event_id_exists) && !$eventPhotoId){
                        CEventsTools::delete_event_image_all($event_id);
                    }
                    $imageTempId = get_param_int('event_photo_id');
                    if ($imageTempId) {
                        $imageTemp = Common::getOption('dir_files', 'path') . 'temp/tmp_event_' . $imageTempId . '.jpg';
                        CEventsTools::do_upload_event_image('', $event_id, $timeCurrent, $addOnWall, $imageTemp);
                    }
                } else {
                    for($image_n = 1; $image_n <= 4; ++$image_n){
                        $name = "image_" . $image_n;
                        CEventsTools::do_upload_event_image($name, $event_id, $timeCurrent, $target_file);
                    }
                }

                CEventsTools::update_event($event_id);

                if ($isAjax) {
                    $url = Common::pageUrl('calendar', 0, $event_date);
                    die(getResponseDataAjaxByAuth(array('redirect' => $url)));
                } else {
                    redirect('wowslider.php?event_id='.$wevent_id);
                    //redirect('events_event_show.php?event_id='.$event_id);
                }

            }
            if ($isAjax) {
               die(getResponseDataAjaxByAuth(array('error' => true)));
            } else {
                redirect('wowslider.php');
            }
        }
        if($cmd == 'del'){
            $eid = get_param('event_id');
            DB::execute("DELETE FROM wowslider WHERE ".
                " event_id=".to_sql($eid).
            ""
            );
        }
        if($cmd == 'ischeck'){
            $eid = get_param('event_id');
            $is_check = get_param('is_check');
            DB::execute("UPDATE wowslider SET ".
            " distance=0".
            " WHERE ".
                " user_id=".$g_user['user_id'].
            "");
            DB::execute("UPDATE wowslider SET ".
            " distance=".to_sql($is_check).
            " WHERE ".
                " event_id=".to_sql($eid).
            ""
            );
            echo json_encode(array("status"=>"success"));
            exit;
        }
        
    }

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $tmplName = Common::getTmplName();
        $isEventSocial = Common::isOptionActiveTemplate('event_social_enabled');

        $guid = guid();
        $event_private = intval(get_param('event_private')) ? 1 : 0;
        
        $event_id = get_param('event_id');
    
        $event = DB::row("SELECT e.*, cn.*, ct.*, st.* ".
            "FROM wowslider as e, geo_country as cn, geo_state as st, geo_city as ct ".
            "WHERE e.event_id=" . to_sql($event_id, 'Number') . " AND ".
            "e.city_id = ct.city_id AND ct.state_id = st.state_id AND ct.country_id = cn.country_id " .
            " AND e.user_id = " . $g_user['user_id'].
            " LIMIT 1");

        $formatData = 'edit_event_date';
        if ($tmplName == 'oryx'){
            $formatTypeJS = 'edit_event_date';

        } elseif($tmplName == 'edge'){
            $formatTypeJS = 'task_date';
            $formatData = 'task_date';
        } else {
            $formatTypeJS = 'edit_event_date_mixer_js';
        }

        $event_btn_create = '';
        $event_btn_class = '';

        $html->setvar('user_email',$g_user['mail']);
        if($event){
            $html->setvar('edit_event_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_event_time',$g['date_formats']['edit_event_time']);
            $html->setvar('eevent_id', $event['event_id']);
            $html->setvar('event_id', $event['event_id']);
            $html->setvar('etitle', he($event['title']));
            $html->setvar('event_description', $event['description']);
            $html->setvar('ezipcode', $event['zipcode']);
            $html->setvar('from_date', Common::dateFormat($event['from_datetime'], $formatData));
            $html->setvar('from_time', Common::dateFormat($event['from_datetime'], 'edit_event_time'));
            $html->setvar('end_date', Common::dateFormat($event['end_datetime'], $formatData));
            $html->setvar('end_time', Common::dateFormat($event['end_datetime'], 'edit_event_time'));
            $html->setvar('elink', $event['link']);
            $html->setvar('eimg_path', $event['img_path']);
            $html->setvar("eimg_path", $g['path']['url_files']."/wowslider/" . $event['img_path']);

            
            $html->parse('edit_title');
            $html->parse('edit_button');

        } elseif($event_id) {
            redirect('wowslider.php');
        } else {
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
            $date = htmlspecialchars(Common::dateFormat($date.' '.$hour.':'.$minute, $formatData));
            $html->setvar('from_date', $date);
            $setTime = $isEventSocial ? '10:00' : $hour.':'.$minute;
            $time = Common::dateFormat($setTime, 'edit_event_time');
            $html->setvar('from_time', $time);
            $html->setvar('end_date', $date);
            $html->setvar('end_time', $time);
            $html->setvar('edit_event_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_event_time',$g['date_formats']['edit_event_time']);

            if ($isEventSocial) {
                $event_btn_create = l('btn_create');
                $event_btn_class = 'btn_create';
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

            $html->setvar('event_btn_class', $event_btn_class);
            $html->setvar('event_btn_create', $event_btn_create);
            $friends = User::getListFriends($guid);
            foreach ($friends as $friend) {
                $html->setvar('list_friend_event_user_id', $friend['friend_id']);
                $html->setvar('list_friend_event_name', $friend['name']);
                $html->setvar('list_friend_event_photo', User::getPhotoDefault($friend['friend_id'], 's'));

                $html->parse('list_friend_event', true);
            }

        }

           //pupup windows

           $popup_row = DB::row("SELECT  * FROM posting_info  WHERE page = 'popup_wowslider' LIMIT 1");
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
        


        $html->setvar('event_private', $event_private);

        $country_id = $event ? $event['country_id'] : $g_user['country_id'];



        $sql_code = "SELECT code FROM geo_country WHERE country_id = " . to_sql($country_id, 'Number');

        DB::query($sql_code);
        $country_code = DB::fetch_row(0);

        $html->setvar('current_country_code', $country_code[0]);
        $html->parse('current_country_code', false);
        $html->setvar("country_options", Common::listCountries($event ? $event['country_id'] : $g_user['country_id']));
        $html->setvar("state_options", Common::listStates($event ? $event['country_id'] : $g_user['country_id'], $event ? $event['state_id'] : $g_user['state_id']));
        $html->setvar("city_options", Common::listCities($event ? $event['state_id'] : $g_user['state_id'], $event ? $event['city_id'] : $g_user['city_id']));

        $distance = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='distance' AND module = 'wowslider'" .
            " LIMIT 1");
            
        
        
        $from_add = "";
        
        if(get_param('distance')){
            $whereLocation = inradius($g_user['city_id'], get_param('distance'));
            $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = e.city_id"; 
            $distance_val = get_param('distance');
        }else if(isset($distance['value'])){
            $whereLocation = inradius($g_user['city_id'], $distance['value']);
            $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = e.city_id"; 
            $distance_val = $distance['value'];
        }
        $html->setvar('edistance', $distance_val);
        DB::query("SELECT * FROM wowslider WHERE user_id='".$g_user['user_id']."' ".(($event_id)?("AND event_id=".$event_id):"")." ORDER BY event_id DESC");
        //$sql = "SELECT e.*,u.* FROM user as u,wowslider as e ".$from_add." WHERE u.user_id = e.user_id ".(($event_id)?("AND e. event_id=".$event_id):"").$whereLocation." ORDER BY e.event_id DESC";
        //DB::query($sql);
        $no=0;
        $html->setblockvar('forum_category', '');
        while($image = DB::fetch_row())
        {
            
            $class=($no==0)?"active":"";
            $html->setvar("cls", $class);
            $html->setvar("eevent_id", $image['event_id']);
            $html->setvar("img_path", $g['path']['url_files'] . "wowslider/" . $image['img_path']);
            $html->setvar("city_id", $image['city_id']);
            $html->setvar("zipcode", $image['zipcode']);
            $html->setvar("title", $image['title']);
            $html->setvar("link", $image['link']);
            $html->setvar("user_name", $g_user['name']);            
            $html->setvar("is_check", ($image['distance']==1)?"checked":"");
            $html->setvar("date_in_v", substr($image['from_datetime'],0,10));
            $html->setvar("date_out_v", substr($image['end_datetime'],0,10));           
            $html->setvar("country_v", $image['country']);
            $html->setvar("state_v", $image['state']);
            $html->setvar("city_v", $image['city']);
            if($g_user["user_id"]==$image['user_id']){
                $html->parse('edit_slide', false);
                $html->setblockvar('none_edit_slide', '');  
            }
            else{
                $html->parse('none_edit_slide', false);             
                $html->setblockvar('edit_slide', '');
            }           
            $html->parse('forum_category', true);
            $html->parse('forum_category1', true);
            $html->parse('forum_category2', true);          
            $no++;
        }   
        $duration_time = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='duration_time' AND module = 'wowslider'" .
            " LIMIT 1");
        $delay_time = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='delay_time' AND module = 'wowslider'" .
            " LIMIT 1");
        $html->setvar('delay_time', $delay_time['value']);
        $html->setvar('duration_time', $duration_time['value']);
        $title_size_v = "2.5";
        $title_bottom_v = 5;
        $title_color_v = "#FFFFFF";
        $title_back_color_v = "#000000";
        $title_sliding_state_v = 1;
        $econtrol_size_v = "5";
        $econtrol_state_v = 1;
        
        $title_size = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='title_size' AND module = 'wowslider'" .
            " LIMIT 1");
            
        if($title_size['value']!=""){
            $title_size_v=$title_size['value'];      
        }
        $title_color = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='title_color' AND module = 'wowslider'" .
            " LIMIT 1");
            
        if($title_color['value']!=""){
            $title_color_v=$title_color['value'];         
        }
        $title_back_color = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='title_back_color' AND module = 'wowslider'" .
            " LIMIT 1");
        if($title_back_color['value']!=""){
            $title_back_color_v=$title_back_color['value'];         
        }
        $title_bottom = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='title_bottom' AND module = 'wowslider'" .
            " LIMIT 1");
        if($title_bottom['value']!=""){
            $title_bottom_v=$title_bottom['value'];         
        }
        $title_sliding_state = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='title_sliding_state' AND module = 'wowslider'" .
            " LIMIT 1");
        if($title_sliding_state['value']!=""){
            if($title_sliding_state['value']==1){
                $title_sliding_state_v="checked";       
                $html->setvar('title_hidden', "inline-block");              
            }else{
                $title_sliding_state_v="";
                $html->setvar('title_hidden', "none");                              
            }
        }
        $econtrol_size = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='control_size' AND module = 'wowslider'" .
            " LIMIT 1");
        if($econtrol_size['value']!=""){
            $econtrol_size_v=$econtrol_size['value'];         
        }
        $econtrol_state = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='control_visible' AND module = 'wowslider'" .
            " LIMIT 1");
        if($econtrol_state['value']!=""){
            if($econtrol_state['value']==1){
                $econtrol_state_v="checked";       
                $html->setvar('econtrol_hidden', "inline-block");               
            }else{
                $econtrol_state_v="";
                $html->setvar('econtrol_hidden', "none");                               
            }
        }
        $html->setvar('econtrol_state', $econtrol_state_v);
        $html->setvar('econtrol_size', $econtrol_size_v);
        $html->setvar('title_sliding_state', $title_sliding_state_v);
        $html->setvar('title_bottom', $title_bottom_v);
        $html->setvar('title_size', $title_size_v);
        $html->setvar('title_color', $title_color_v);
        $html->setvar('title_back_color', $title_back_color_v);
        $effects = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='effects' AND module = 'wowslider'" .
            " LIMIT 1");
            
        $html->setvar('slider_type', $effects['value']);
        
        $html->setvar("imgcount", $no);
        $settings = CEventsTools::settings();

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

// $page = new CEvents("", getPageCustomTemplate('wowslider.html', 'events_event_edit_template'));


$page = new CEvents("", $g['tmpl']['dir_tmpl_main'] . "wowslider.html" );

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

include("./_include/core/main_close.php");