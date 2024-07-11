<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CHotdatesCalendar extends CHtmlBlock
{
	var $m_need_container = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

        $isCalendarSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');
        $day_time = intval(get_param('day_time'));
        $hotdateDayLoadMore = get_param('hotdate_day_load_more');
        $guest_sign_action = get_param('guest_sign_action', '');

        $guid = guid();
        $uid = User::getParamUid($guid);

        if($html->varExists('calendar_uid')){
            $html->setvar('calendar_uid', $uid);
        }

        if($html->varExists('page_url')){
            $html->setvar('page_url', Common::pageUrl('user_hotdate_calendar', $uid));
        }

        if($guest_sign_action == '1') {
            $guest_sign_day = get_param('guest_sign_day', '');
            $hotdate_id = intval(get_param('hotdate_id'));
            TaskCalendarHotdate::parseHotdatesDay($html, strtotime($guest_sign_day), $uid, $hotdate_id);
        } elseif ($day_time){
            TaskCalendarHotdate::parseHotdatesDay($html, $day_time, $uid);
        } elseif ($hotdateDayLoadMore){
            TaskCalendarHotdate::parseHotdatesDay($html, strtotime($hotdateDayLoadMore), $uid);
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
				TaskCalendarHotdate::parseHotdatesDay($html, $day_time, $uid);
	            $day_time += 24 * 60 * 60;
			};
		}

        if ($isCalendarSocial) {
            if ($hotdateDayLoadMore) {
                $html->parse('add_hotdates', false);
            } elseif($guest_sign_action == '1') {
                $hotdate_id = intval(get_param('hotdate_id'));
                $html->setvar('hotdate_id', $hotdate_id);
                $html->parse('update_hotdates', false);
            } else {
                $html->parse('set_hotdates', false);
            }
        }

		parent::parseBlock($html);
	}
}