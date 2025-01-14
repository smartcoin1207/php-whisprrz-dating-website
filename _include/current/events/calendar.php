<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CEventsCalendar extends CHtmlBlock
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
        
        $guid = guid();
        $uid = User::getParamUid($guid);

        if($html->varExists('calendar_uid')){
            $html->setvar('calendar_uid', $uid);
        }

        if($html->varExists('page_url')){
            $html->setvar('page_url', Common::pageUrl('user_event_calendar', $uid));
        }

        if($guest_sign_action == '1') {
            $guest_sign_day = get_param('guest_sign_day', '');
            $event_id = intval(get_param('event_id'));
            TaskCalendar::parseEventsDay($html, strtotime($guest_sign_day), $uid, $event_id);
        } elseif ($day_time){
            TaskCalendar::parseEventsDay($html, $day_time, $uid);
        } elseif ($eventDayLoadMore){
            TaskCalendar::parseEventsDay($html, strtotime($eventDayLoadMore), $uid);
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
				TaskCalendar::parseEventsDay($html, $day_time, $uid);
	            $day_time += 24 * 60 * 60;
			};
		}

        if ($isCalendarSocial) {
            if ($eventDayLoadMore) {
                $html->parse('add_events', false);
            } elseif($guest_sign_action == '1') {
                $event_id = intval(get_param('event_id'));
                $html->setvar('event_id', $event_id);
                $html->parse('update_events', false);
            } else {
                $html->parse('set_events', false);
            }
        }

		parent::parseBlock($html);
	}
}