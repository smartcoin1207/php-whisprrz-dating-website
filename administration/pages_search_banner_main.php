<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. 
This file is built by cobra.  --- 20200209*/
// Rade 2023-09-23
include("../_include/core/administration_start.php");

class CBanners extends CHtmlBlock
{
	function action()
	{
        global $g_user;
        global $l;
        global $g;

        $isEventSocial = Common::isOptionActiveTemplate('event_social_enabled');

        $cmd = get_param('cmd');
		$slider_type = get_param('slider_type');
        $isAjax = get_param_int('ajax');
        if($cmd == 'search'){
			$user_id = get_param('user_id');
			$place_id = get_param('place_id');
			$city_id = get_param('city_id');
			$state_id = get_param('state');
			$country_id = get_param('country');
			$zipcode = get_param('zipcode');
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
			$where_sql="";
			if(!is_null($user_id) && $user_id!="0"){
				$where_sql = $where_sql." and e.user_id=".$user_id;
			}
			if(!is_null($place_id) && $place_id!="0"){
				$where_sql = $where_sql." and e.slider_type='".$place_id."'";
			}
			if(!is_null($country_id) && $country_id!="0"){
				if($where_sql==""){
					$where_sql = $where_sql." and e.country_id=".$country_id;
				}else{
					$where_sql = $where_sql." and e.country_id=".$country_id;
				}
			}
			
			if(!is_null($state_id) && $state_id!="0"){
				if($where_sql==""){
					$where_sql = $where_sql." and e.state_id=".$state_id;
				}else{
					$where_sql = $where_sql." and e.state_id=".$state_id;
				}
			}
			
			if(!is_null($city_id) && $city_id!="0"){
				if($where_sql==""){
					$where_sql = $where_sql." and e.city_id=".$city_id;
				}else{
					$where_sql = $where_sql." and e.city_id=".$city_id;
				}
			}
			
			if(!is_null(get_param('zipcode')) && get_param('zipcode')!=""){
				if($where_sql==""){
					$where_sql = $where_sql." and e.zipcode = '".$zipcode."'";
				}else{
					$where_sql = $where_sql." and e.zipcode = '".$zipcode."'";
				}
			}
			
			if(!is_null(get_param("link")) && get_param("link")!=""){
				if($where_sql==""){
					$where_sql = $where_sql." and e.link like '%".get_param("link")."%'";
				}else{
					$where_sql = $where_sql." and e.link like '%".get_param("link")."%'";
				}
			}
			if(!is_null(get_param("title")) && get_param("title")!=""){
				if($where_sql==""){
					$where_sql = $where_sql." and e.title like '%".get_param("title")."%'";
				}else{
					$where_sql = $where_sql." and e.title like '%".get_param("title")."%'";
				}
			}		
			
			if(get_param('from_date')!=get_param('end_date')){
				$from = date('Y-m-d',strtotime(get_param('from_date')));
				$to = date('Y-m-d',strtotime(get_param('end_date')));
				if($where_sql==""){
					$where_sql = $where_sql." and not(e.from_datetime > CAST('" . $to . "' AS DATETIME) or e.end_datetime < CAST('" . $from . "' AS DATETIME))";
				}else{
					$where_sql = $where_sql." and not(e.from_datetime > CAST('" . $to . "' AS DATETIME) or e.end_datetime < CAST('" . $from . "' AS DATETIME))";
				}
			}
			
			redirect('pages_search_banner_main.php?where_sql='.$where_sql);
		}else if($cmd == 'effect_save'){
			DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('slider_type'))." WHERE config.option='effects' AND module = 'wowslider'");
            redirect('banner.php');
		}else if($cmd == 'distance_save'){
			DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('distance'))." WHERE config.option='distance' AND module = 'wowslider'");
            DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('delay_time'))." WHERE config.option='delay_time' AND module = 'wowslider'");
            DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('duration_time'))." WHERE config.option='duration_time' AND module = 'wowslider'");
			DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('title_size'))." WHERE config.option='title_size' AND module = 'wowslider'");
            DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('title_color'))." WHERE config.option='title_color' AND module = 'wowslider'");
            DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('title_back_color'))." WHERE config.option='title_back_color' AND module = 'wowslider'");
			DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('title_bottom'))." WHERE config.option='title_bottom' AND module = 'wowslider'");
			DB::execute("UPDATE config SET " .
                    " value=".to_sql(get_param('econtrol_size'))." WHERE config.option='control_size' AND module = 'wowslider'");
            if(!is_null(get_param('econtrol_state')) && get_param('econtrol_state')==1){
				DB::execute("UPDATE config SET " .
						" value=".to_sql(get_param('econtrol_state'))." WHERE config.option='control_visible' AND module = 'wowslider'");
			}else{
				DB::execute("UPDATE config SET " .
						" value=0 WHERE config.option='control_visible' AND module = 'wowslider'");
			}
			if(!is_null(get_param('title_sliding_state')) && get_param('title_sliding_state')==1){
				DB::execute("UPDATE config SET " .
						" value=".to_sql(get_param('title_sliding_state'))." WHERE config.option='title_sliding_state' AND module = 'wowslider'");
			}else{
				DB::execute("UPDATE config SET " .
						" value=0 WHERE config.option='title_sliding_state' AND module = 'wowslider'");
			}
			redirect('banner.php');
		}else if($cmd == 'save')
        {
            $event_id = get_param('event_id');

            $city_id = intval(get_param('city_id', ($g_user['city_id'] == 0) ? 1 : $g_user['city_id']));
			$state_id = intval(get_param('state', ($g_user['state_id'] == 0) ? 1 : $g_user['state_id']));
			$country_id = intval(get_param('country', ($g_user['country_id'] == 0) ? 1 : $g_user['country_id']));
            $zipcode = get_param('zipcode');
            $file_name = "";
            if ($_FILES['slider_image']['name'] != ""){
                $target_dir = $g['path']['url_files']."/wowslider/";
                $file_name=Date("U").".jpg";
                $target_file = $target_dir . $file_name;
                move_uploaded_file($_FILES["slider_image"]["tmp_name"], $target_file);
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
                DB::execute("UPDATE wowslider_main SET " .
                    " zipcode=".to_sql($zipcode).
                    (($file_name=="")?"":(", img_path=".to_sql($file_name))).
                    ", link=".to_sql(get_param('link')).
					", distance=".to_sql(get_param('distance')).
                    ", title=".to_sql(get_param('title')).
                    ", from_datetime=".to_sql(date_format($fdt, 'Y-m-d H:i:s')).
                    ", end_datetime=".to_sql(date_format($edt, 'Y-m-d H:i:s')).
                    ", city_id=".to_sql($city_id, 'Number').
					", city='".$city."'".
					", state='".$state."'".
					", country='".$country."'".
					", country_id=".to_sql($country_id, 'Number').
					", state_id=".to_sql($state_id, 'Number').
					", slider_type=".to_sql(get_param('place_id')).
                    " WHERE event_id=" . to_sql($event_id, 'Number') . " LIMIT 1");				
                redirect('banner.php');
            }else{
                DB::execute("INSERT INTO wowslider_main SET ".
                    " user_id=".to_sql($g_user['user_id'], 'Number').
                    ", zipcode=".to_sql($zipcode).
                    ", img_path=".to_sql($file_name).
                    ", link=".to_sql(get_param('link')).
					", distance=".to_sql(get_param('distance')).
                    ", title=".to_sql(get_param('title')).
                    ", from_datetime=".to_sql(date_format($fdt, 'Y-m-d H:i:s')).
                    ", end_datetime=".to_sql(date_format($edt, 'Y-m-d H:i:s')).
                    ", city_id=".to_sql($city_id, 'Number').
					", city='".$city."'".
					", state='".$state."'".
					", country='".$country."'".
					", country_id=".to_sql($country_id, 'Number').
					", state_id=".to_sql($state_id, 'Number').
					", slider_type=".to_sql(get_param('place_id')).
                    ", is_check=0".
                ""
                );
                $event_id = DB::insert_id();
            }
        }
        if($cmd == 'del'){
            $eid = get_param('event_id');
            DB::execute("DELETE FROM wowslider_main WHERE ".
                " event_id=".to_sql($eid).
            ""
            );
        }
        if($cmd == 'ischeck'){
            $eid = get_param('event_id');
            $is_check = get_param('is_check');
			DB::execute("UPDATE wowslider_main SET ".
            " is_check=0".
			"");
            DB::execute("UPDATE wowslider_main SET ".
            " is_check=".to_sql($is_check).
            " WHERE ".
                " event_id=".to_sql($eid).
            ""
            );
            echo json_encode(array("status"=>"success"));
            exit;
        }
		if($cmd == 'ischeck_user'){
            $eid = get_param('event_id');
			$uid = get_param('user_id');
            $is_check = get_param('is_check');
			DB::execute("UPDATE wowslider_main SET ".
            " distance=0".
			" WHERE ".
                " user_id=".to_sql($uid).
			"");
            DB::execute("UPDATE wowslider_main SET ".
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
		$where_sql = get_param('where_sql');
        $event_id = get_param('event_id');
		$event = DB::row("SELECT e.*, cn.*, ct.*, st.* ".
            "FROM wowslider_main as e, geo_country as cn, geo_state as st, geo_city as ct ".   				
            "WHERE e.event_id=" . to_sql($event_id, 'Number') . " AND ".
			"e.city_id = ct.city_id AND ct.state_id = st.state_id AND ct.country_id = cn.country_id " .	
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
		DB::query("SELECT * FROM user ORDER BY user_id ASC");
		$user_option = '<option id="option_user_0" value="0" selected>All</option>';
		while($users = DB::fetch_row()){
			if($event && $event['user_id']==$users['user_id']){
				$user_option = $user_option.'<option id="option_user_'.$users['user_id'].'" value="'.$users['user_id'].'" selected>'.$users['name'].'</option>';
			}else{
				$user_option = $user_option.'<option id="option_user_'.$users['user_id'].'" value="'.$users['user_id'].'">'.$users['name'].'</option>';
			}
		}
		$html->setvar('user_options', $user_option);
		DB::query("SELECT * FROM banners_places WHERE place!='right_column' and place!='right_column_paid' ORDER BY id ASC");
		$place_option = "";
		while($places = DB::fetch_row()){
			if($event && $event['slider_type']==$places['place']){
				$place_option = $place_option.'<option id="option_place_'.$places['id'].'" value="'.$places['place'].'" selected>'.$places['place'].'</option>';
			}else{
				$place_option = $place_option.'<option id="option_place_'.$places['id'].'" value="'.$places['place'].'">'.$places['place'].'</option>';
			}
		}
		$html->setvar('place_option', $place_option);
		if($event){
            $html->setvar('edit_event_date',$g['date_formats'][$formatTypeJS]);
            $html->setvar('edit_event_time',$g['date_formats']['edit_event_time']);
            $html->setvar("distance", $event['distance']);	
			$html->setvar("is_check", ($event['is_check']==1)?"checked":"");
			$html->setvar("is_check_user", ($event['distance']==1)?"checked":"");
			$html->setvar("user_id", $event['user_id']);	
			$html->setvar("place", $event['slider_type']);
            $html->setvar('event_id', $event['event_id']);
			$html->setvar('eevent_id', $event['event_id']);
            $html->setvar('etitle', he($event['title']));
            $html->setvar('ezipcode', $event['zipcode']);
            $html->setvar('from_date', Common::dateFormat($event['from_datetime'], $formatData));
            $html->setvar('from_time', Common::dateFormat($event['from_datetime'], 'edit_event_time'));
            $html->setvar('end_date', Common::dateFormat($event['end_datetime'], $formatData));
            $html->setvar('end_time', Common::dateFormat($event['end_datetime'], 'edit_event_time'));
            $html->setvar('elink', $event['link']);
            $html->setvar('eimg_path', $event['img_path']);
            $html->setvar("eimg_path", $g['path']['url_files']."/wowslider/" . $event['img_path']);
			$html->setvar('user_name', "");
            
            $html->parse('edit_title');
            $html->parse('edit_button');

        } elseif($event_id) {
            redirect('banner.php');
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
		/*
        if(get_param('slider_type')){
            $html->setvar('slider_type', get_param('slider_type'));
        }else{
            $html->setvar('slider_type', 'turn');
        }
		*/
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
        $html->setvar('event_private', $event_private);

        $html->setvar("country_options", Common::listCountries($event ? $event['country_id'] : $g_user['country_id']));
        $html->setvar("state_options", Common::listStates($event ? $event['country_id'] : $g_user['country_id'], $event ? $event['state_id'] : $g_user['state_id']));
        $html->setvar("city_options", Common::listCities($event ? $event['state_id'] : $g_user['state_id'], $event ? $event['city_id'] : $g_user['city_id']));
		//echo("SELECT e.* wowslider_main as e WHERE 1 = 1 ".(($event_id)?("AND e. event_id=".$event_id):"")." ".$where_sql." ORDER BY e.event_id DESC");
        DB::query("SELECT e.* FROM wowslider_main as e WHERE 1 = 1 ".(($event_id)?("AND e. event_id=".$event_id):"")." ".$where_sql." ORDER BY e.event_id DESC");
		//DB::query("SELECT e.*,u.* FROM wowslider_main as e,user as u WHERE u.user_id = e.user_id and ".(($event_id)?("AND e. event_id=".$event_id):"")." ORDER BY e.event_id DESC");
        $no=0;
        $html->setblockvar('forum_category', '');
		
        while($image = DB::fetch_row())
        {
            $class=($no==0)?"active":"";
            $html->setvar("cls", $class);
            $html->setvar("eevent_id", $image['event_id']);	
			$html->setvar("distance", $image['distance']);	
			$html->setvar("user_id", $image['user_id']);				
            $html->setvar("img_path", $g['path']['url_files'] . "wowslider/" . $image['img_path']);
            $html->setvar("city_id", $image['city_id']);
            $html->setvar("zipcode", $image['zipcode']);
            $html->setvar("title", $image['title']);
			$html->setvar("place", $image['slider_type']);
            $html->setvar("link", $image['link']);
			$html->setvar("user_name", "");
            $html->setvar("is_check", ($image['is_check']==1)?"checked":"");
			$html->setvar("is_check_user", ($image['distance']==1)?"checked":"");
			$html->setvar("date_in_v", substr($image['from_datetime'],0,10));
			$html->setvar("date_out_v", substr($image['end_datetime'],0,10));			
			$html->setvar("country_v", $image['country']);
			$html->setvar("state_v", $image['state']);
			$html->setvar("city_v", $image['city']);
            $html->parse('forum_category', true);
            $html->parse('forum_category1', true);
            $html->parse('forum_category2', true);
            $no++;
        }
		$distance = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='distance' AND module = 'wowslider'" .
			" LIMIT 1");
		$duration_time = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='duration_time' AND module = 'wowslider'" .
			" LIMIT 1");
		$delay_time = DB::row("SELECT * ".
            "FROM config ".            
            "WHERE config.option='delay_time' AND module = 'wowslider'" .
			" LIMIT 1");
		$html->setvar('edistance', $distance['value']);
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
        //$settings = CEventsTools::settings();

        if(!$event_private)
        {
            $html->parse('event_location');
            $html->parse('event_parameters');
        }
		
        $html->setvar('calendar_month', html_entity_decode(l('calendar_month'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays', html_entity_decode(l('calendar_weekdays'), ENT_QUOTES, 'UTF-8'));
        $html->setvar('calendar_weekdays_short', html_entity_decode(l('calendar_weekdays_short'), ENT_QUOTES, 'UTF-8'));

        //TemplateEdge::parseColumn($html);
		parent::parseBlock($html);
	}
}

$page = new CBanners('', $g['tmpl']['dir_tmpl_administration'] . 'pages_search_banner_main.html');

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
