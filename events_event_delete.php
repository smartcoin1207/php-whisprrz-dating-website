<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/events/tools.php");


class CEventLoad extends CHtmlBlock
{
    static $dayTime = '';
    static $day = '';

	function parseBlock(&$html)
	{
        /*$event = TaskCalendar::getNextTask();
        if ($event && isset($event[0])) {
            $event = $event[0];
        }
        if ($event) {
            TaskCalendar::parseEvent($html, $event, 0);
            $html->parse('day');

            $html->setvar('events_last_more', TaskCalendar::getNextTaskMoreCount($event['event_id']));
            $html->parse('add_events', false);
        } else {
            $html->setvar('event_id', get_param_int('event_id'));
            $html->parse('no_load_more', false);
        }*/

        $eventsOwner = array('my' => 0, 'other' => 0);
        if (self::$dayTime) {
            $eventsOwner = TaskCalendar::getEventsOwnerCounts(self::$dayTime, guid());
        }

        $block = 'day_owners_update';
        $html->setvar("{$block}_day", self::$day);
        $html->setvar($block, json_encode($eventsOwner));
        $html->parse($block, false);

        $block = 'update_counter_my_task';
        $countNewTask = TaskCalendar::getCountOpenTasksByCurrentDay();
        $newTasksTitle = TaskCalendar::getNotifTitle($countNewTask);
        $html->setvar("{$block}_count", $countNewTask);
        $html->setvar("{$block}_title", $newTasksTitle);
        $html->parse($block, false);

        parent::parseBlock($html);
    }
}

$isCalendarSocial = Common::isOptionActiveTemplate('event_social_enabled');
if ($isCalendarSocial) {
    $eventId = get_param_int('event_id');
    $where = 'event_id = ' . to_sql($eventId);
    $event = DB::one('events_event', $where);
    if ($event &&  isset($event[0])) {
        CEventLoad::$dayTime = strtotime($event['event_datetime']);
        CEventLoad::$day = intval(date('j', CEventLoad::$dayTime));
    }
}

function deleteEvent(){
	global $g_user;

	$event_id = get_param_int('event_id');

	if($event_id){
        CEventsTools::delete_event($event_id);
	}
    $ajax = get_param_int('ajax');
    if (!$ajax) {
        redirect('events_calendar.php');
    }
}

deleteEvent();

$ajax = get_param_int('ajax');
if ($ajax && $isCalendarSocial) {
    $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar_items.html";
    $page = new CEventLoad("", $tmpl);
    loadPageContentAjax($page);
}

include("./_include/core/main_close.php");