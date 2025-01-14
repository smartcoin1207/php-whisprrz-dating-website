<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

// require_once("./_include/current/events/custom_head.php");
require_once("./_include/current/events/header.php");
require_once("./_include/current/events/sidebar.php");
require_once("./_include/current/events/tools.php");
require_once("./_include/current/events/event_show.php");
require_once("./_include/current/events/event_image_list.php");
require_once("./_include/current/events/event_guest_list.php");
require_once("./_include/current/events/event_comment_list.php");
require_once("./_include/current/events/event_list.php");

class CEvents extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        // DEMO
		if(defined('DEMO_EVENTS')) {
            $events = array();
			$where = " e.event_title='Norah Jones' AND ";
			$sql_base = CEventsTools::events_upcoming_main_page_sql_base($where);
			$events_demo = CEventsTools::retrieve_from_sql_base($sql_base, 1);
            if (isset($events_demo[0]))
                $events[0] = $events_demo[0];

			$where = " e.event_title='Bon Jovi' AND ";
			$sql_base = CEventsTools::events_upcoming_main_page_sql_base($where);
			$events_demo = CEventsTools::retrieve_from_sql_base($sql_base, 1);
            if (isset($events_demo[0]))
                $events[1] = $events_demo[0];
		}
		else {
			$sql_base = CEventsTools::events_upcoming_main_page_sql_base();
			$events = CEventsTools::retrieve_from_sql_base($sql_base, 2);
		}

        $event_n = 1;

        foreach($events as $event)
        {
            $html->setvar('event_id', $event['event_id']);
            $html->setvar('event_title', strcut(to_html($event['event_title']), 20));
            $html->setvar('event_title_full', to_html($event['event_title']));

            $html->setvar('event_n_comments', $event['event_n_comments']);
            $html->setvar('event_n_guests', $event['event_n_guests']);
            $html->setvar('event_place', strcut(to_html($event['event_place']), 13));
            $html->setvar('event_place_full', to_html($event['event_place']));

	        $html->setvar('event_date', to_html(Common::dateFormat($event['event_datetime'],'events_event_date')));
	        $html->setvar('event_datetime_raw', to_html($event['event_datetime']));
	        $html->setvar('event_time', to_html(Common::dateFormat($event['event_datetime'],'events_event_time')));

            $images = CEventsTools::event_images($event['event_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail_b"]);

            if($event_n == count($events))
                $html->parse("event_last");

            $html->parse("event");

            ++$event_n;
        }

		parent::parseBlock($html);
	}
}

$page = new CEvents("", $g['tmpl']['dir_tmpl_main'] . "events.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
// $events_custom_head = new CEventsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_events_custom_head.html");
$header->add($events_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$events_header = new CEventsHeader("events_header", $g['tmpl']['dir_tmpl_main'] . "_events_header.html");
$page->add($events_header);
$events_sidebar = new CEventsSidebar("events_sidebar", $g['tmpl']['dir_tmpl_main'] . "_events_sidebar.html");
$events_sidebar->m_second_block = "popular_finished";
$page->add($events_sidebar);

$events_event_list = new CEventsEventList("events_event_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_list.html");
$events_event_list->m_list_type = "most_anticipated";
$page->add($events_event_list);