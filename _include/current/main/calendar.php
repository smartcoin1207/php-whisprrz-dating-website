<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

use Symfony\Component\VarDumper\VarDumper;

require_once('tools.php');
require_once('./_include/current/events/tools.php');
require_once('./_include/current/hotdates/tools.php');
require_once('./_include/current/partyhouz/tools.php');

class CMainCalendar extends CHtmlBlock
{
	var $m_need_container = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

        $isCalendarSocial = Common::isOptionActiveTemplate('event_social_enabled');
        $day_time = intval(get_param('day_time'));
        $eventDayLoadMore = get_param('event_day_load_more');
        $guest_sign_action = get_param('guest_sign_action', '');

        $orientation_see_sql = " 1=1 ";
        if($g_user['orientation'] == '1') {
            $orientation_see_sql = "u.set_male_see_my_calendar=1";
        } else if($g_user['orientation'] == '2') {
            $orientation_see_sql = "u.set_female_see_my_calendar=1";
        } else if($g_user['orientation'] == '5') {
            $orientation_see_sql = "u.set_couple_see_my_calendar=1";
        } else if($g_user['orientation'] == '6') {
            $orientation_see_sql = "u.set_transgender_see_my_calendar=1";
        } else if($g_user['orientation'] == '7') {
            $orientation_see_sql = "u.set_nonbinary_see_my_calendar=1";
        }

        //who's allowed to see my calendar start
        $sql = "SELECT u.user_id FROM user as u LEFT JOIN friends_requests as fr ON fr.user_id = u.user_id " . 
        " WHERE ((fr.user_id = " . to_sql(guid()) . " OR fr.friend_id = " . to_sql(guid()) . ") AND u.set_friends_see_my_calendar = 1)" . 
        " OR " . $orientation_see_sql;

        $can_see_user_ids = DB::rows($sql);
     
        $guid = guid();
        $uid = User::getParamUid($guid);

        $can_see = false;
        foreach ($can_see_user_ids as $key => $user) {
        	$user['userd'] = $uid;
        	if($user['user_id'] == $uid) {
        		$can_see = true;
        		break;
        	}
        }

        if(!$can_see && $guid != $uid) {
        	$ajax_request = get_param('upload_page_content_ajax', '');
        	if($ajax_request == '1') {
        		$ajax_request = '';
     		    echo json_encode(l('calendar_can_not_see'));
     		    die();
        	} else {
        		common::toHomePage();
        	}
        }

        //who's allowed to see my calendar end

        //who's allowed to post other user calendar start

        $orientation_post_sql = " 1=1 ";
        if($g_user['orientation'] == '1') {
            $orientation_post_sql = "u.set_male_post_my_calendar=1";
        } else if($g_user['orientation'] == '2') {
            $orientation_post_sql = "u.set_female_post_my_calendar=1";
        } else if($g_user['orientation'] == '5') {
            $orientation_post_sql = "u.set_couple_post_my_calendar=1";
        } else if($g_user['orientation'] == '6') {
            $orientation_post_sql = "u.set_transgender_post_my_calendar=1";
        } else if($g_user['orientation'] == '7') {
            $orientation_post_sql = "u.set_nonbinary_post_my_calendar=1";
        }

        $sql_post = "SELECT u.user_id FROM user as u LEFT JOIN friends_requests as fr ON fr.user_id = u.user_id " . 
        " WHERE u.user_id = " . to_sql($guid) . " OR ((fr.user_id = " . to_sql($guid) . " OR fr.friend_id = " . to_sql($guid) . ") AND u.set_friends_post_my_calendar = 1)".
        " OR " . $orientation_post_sql ." OR FIND_IN_SET(". to_sql($guid) .", u.set_post_my_calendar_users) > 0";

        $can_post_user_ids = DB::rows($sql_post);
        
        $can_post = false;
        foreach ($can_post_user_ids as $key => $user) {
        	$user['userd'] = $uid;
        	if($user['user_id'] == $uid) {
        		$can_post = true;
        		break;
        	}
        }

        $canPost = true;

        if(!$can_post && $guid != $uid) {
            $canPost = false;
        }
         //who's allowed to post other user calendar end
        if($html->varExists('calendar_uid')){
            $html->setvar('calendar_uid', $uid);
        }

        if($html->varExists('page_url')){
            $html->setvar('page_url', Common::pageUrl('user_calendar', $uid));
        }

        if($guest_sign_action == '1') {
            $guest_sign_day = get_param('guest_sign_day', '');

            $event_id = intval(get_param('event_id', ''));
            $hotdate_id = intval(get_param('hotdate_id', ''));
            $partyhou_id = intval(get_param('partyhou_id', ''));

            if($event_id) {
                $event_id = $event_id;
            } elseif($hotdate_id) {
                $event_id = $hotdate_id;
            } elseif($partyhou_id) {
                $event_id = $partyhou_id;
            }

            TaskCalendarMain::parseMainDay($html, strtotime($guest_sign_day), $uid, $canPost, $event_id);
        } elseif ($day_time){
            TaskCalendarMain::parseMainDay($html, $day_time, $uid, $canPost);
        } elseif ($eventDayLoadMore){
            TaskCalendarMain::parseMainDay($html, strtotime($eventDayLoadMore), $uid, $canPost);
        } else {
            $calendar_month = intval(get_param('calendar_month', date("n")));
			$calendar_year = intval(get_param('calendar_year', date("Y")));

            $calendarDate = get_param('date');
            if ($calendarDate) {
                $d = DateTime::createFromFormat('Y-m-d', $calendarDate);
                if ($d && $d->format('Y-m-d') == $calendarDate) {
                    $calendar_day = intval(date('j', strtotime($calendarDate)));
                    $calendar_month = intval(date('n', strtotime($calendarDate)));
                    $calendar_year = intval(date('Y', strtotime($calendarDate)));

                    $html->setvar('calendar_init_day', $calendar_day);
                    $html->setvar('calendar_init_date', $calendarDate);
                }
            }

			$need_container = get_param('need_container', $this->m_need_container);

			if($calendar_month > 12)
			{
	            $calendar_month = $calendar_month - 12;
	            $calendar_year++;
			}
	        if($calendar_month < 1)
	        {
	            $calendar_month = $calendar_month + 12;
	            $calendar_year--;
	        }

			$html->setvar('calendar_month', $calendar_month);
			$html->setvar('calendar_month_title', l(date("F", strtotime('2010-'.$calendar_month.'-01'))));
			$html->setvar('calendar_year', $calendar_year);

			if($need_container)
			{
				$html->parse('container_header');
				$html->parse('container_footer');
			}
            $html->parse('table_header');
            $html->parse('table_footer');

			$day_time = strtotime($calendar_year.'-'.$calendar_month.'-01');

			while(intval(date("n", $day_time)) == $calendar_month){
	            TaskCalendarMain::parseMainDay($html, $day_time, $uid, $canPost);
	            $day_time += 24 * 60 * 60;
			};
		}

        if ($isCalendarSocial) {
            if ($eventDayLoadMore) {
                $html->parse('add_events', false);
            }  elseif($guest_sign_action == '1') {
                $event_id = intval(get_param('event_id', ''));
                $hotdate_id = intval(get_param('hotdate_id', ''));
                $partyhou_id = intval(get_param('partyhou_id', ''));
                if($event_id) {
                    $maincalendar_type = 'event';
                    $html->setvar('event_id', $event_id);
                } elseif($hotdate_id) {
                    $maincalendar_type = 'hotdate';
                    $html->setvar('event_id', $hotdate_id);
                } elseif($partyhou_id) {
                    $maincalendar_type = 'partyhou';
                    $html->setvar('event_id', $partyhou_id);
                }

                $html->setvar('maincalendar_type', $maincalendar_type);
                $html->parse('update_events', false);
            } else {
                $html->parse('set_events', false);
            }
        }

		parent::parseBlock($html);
	}
}