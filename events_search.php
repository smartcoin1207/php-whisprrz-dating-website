<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/events/custom_head.php");
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
    var $m_upcoming_events_list;
    var $m_finished_events_list;
    var $m_upcoming_random_events_list;
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

        $event_place = get_param('event_place');
        $html->setvar('event_place', $event_place);
        $category_id = get_param('category_id');
        $html->setvar('category_title', l(DB::result("SELECT category_title FROM events_category WHERE category_id = " . to_sql($category_id) . " LIMIT 1"), false, 'events_category'));
        $event_datetime = get_param('event_datetime');
        $html->setvar('event_datetime', Common::dateFormat($event_datetime, 'event_datetime'));

        if($this->m_upcoming_events_list->m_n_results || $this->m_finished_events_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);

        	if($this->m_upcoming_events_list->m_n_results)
            {
                if($query)
                    $html->parse('upcoming_events_query_title');
                if($event_place)
                    $html->parse('upcoming_events_event_place_title');
                if($category_id)
                    $html->parse('upcoming_events_category_id_title');
                if($event_datetime)
                    $html->parse('upcoming_events_event_datetime_title');

            	$html->parse('upcoming_events_list');
            }

            if($this->m_finished_events_list->m_n_results)
            {
                if($query)
                    $html->parse('finished_events_query_title');
                if($event_place)
                    $html->parse('finished_events_event_place_title');
                if($category_id)
                    $html->parse('finished_events_category_id_title');
                if($event_datetime)
                    $html->parse('finished_events_event_datetime_title');

                $html->parse('finished_events_list');
            }
        }
        else
        {
            if($this->m_upcoming_random_events_list->m_n_results)
            {
                if($event_place)
                    $html->parse('upcoming_random_events_event_place_title');
                if($category_id)
                    $html->parse('upcoming_random_events_category_id_title');
                if($event_datetime)
                    $html->parse('upcoming_random_events_event_datetime_title');

            	$html->parse('upcoming_random_events_list');
            }



            $html->parse('events_not_found');
        }

		parent::parseBlock($html);
	}
}

$page = new CEvents("", $g['tmpl']['dir_tmpl_main'] . "events_search.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$events_custom_head = new CEventsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_events_custom_head.html");
$header->add($events_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$events_header = new CEventsHeader("events_header", $g['tmpl']['dir_tmpl_main'] . "_events_header.html");
$page->add($events_header);
$events_sidebar = new CEventsSidebar("events_sidebar", $g['tmpl']['dir_tmpl_main'] . "_events_sidebar.html");
$events_sidebar->m_second_block = "most_anticipated";
$page->add($events_sidebar);

$upcoming_events_event_list = new CEventsEventList("upcoming_events_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_list.html");
$upcoming_events_event_list->m_list_type = "search";
$upcoming_events_event_list->m_event_where_when = false;
$upcoming_events_event_list->m_upcoming = 1;
$page->m_upcoming_events_list = $upcoming_events_event_list;
$page->add($upcoming_events_event_list);

$finished_events_event_list = new CEventsEventList("finished_events_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_list.html");
$finished_events_event_list->m_list_type = "search";
$finished_events_event_list->m_event_where_when = false;
$finished_events_event_list->m_upcoming = 0;
$page->m_finished_events_list = $finished_events_event_list;
$page->add($finished_events_event_list);


$upcoming_random_events_event_list = new CEventsEventList("upcoming_random_events_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_list.html");
$upcoming_random_events_event_list->m_list_type = "random";
$upcoming_random_events_event_list->m_event_where_when = false;
$upcoming_random_events_event_list->m_upcoming = 1;
$page->m_upcoming_random_events_list = $upcoming_random_events_event_list;
$page->add($upcoming_random_events_event_list);


include("./_include/core/main_close.php");
