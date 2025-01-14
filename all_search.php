<?php
/* (C) Websplosion LTD., 2001-2014

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
require_once("./_include/current/hotdates/custom_head.php");
require_once("./_include/current/hotdates/header.php");
require_once("./_include/current/hotdates/sidebar.php");
require_once("./_include/current/hotdates/tools.php");
require_once("./_include/current/hotdates/hotdate_show.php");
require_once("./_include/current/hotdates/hotdate_image_list.php");
require_once("./_include/current/hotdates/hotdate_guest_list.php");
require_once("./_include/current/hotdates/hotdate_comment_list.php");
require_once("./_include/current/hotdates/hotdate_list.php");
require_once("./_include/current/partyhouz/custom_head.php");
require_once("./_include/current/partyhouz/header.php");
require_once("./_include/current/partyhouz/sidebar.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_show.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");
require_once("./_include/current/partyhouz/partyhou_guest_list.php");
require_once("./_include/current/partyhouz/partyhou_comment_list.php");
require_once("./_include/current/partyhouz/partyhou_list.php");

class CAll extends CHtmlBlock
{
    var $m_upcoming_events_list;
    var $m_finished_events_list;
    var $m_upcoming_random_events_list;

    var $m_upcoming_hotdates_list;
    var $m_finished_hotdates_list;
    var $m_upcoming_random_hotdates_list;

    var $m_upcoming_partyhouz_list;
    var $m_finished_partyhouz_list;
    var $m_upcoming_random_partyhouz_list;
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
        $event_datetime = get_param('datetime');
        $dateTime = new DateTime($event_datetime);
        $event_outputDate = $dateTime->format('M/d/Y');
        $html->setvar('event_datetime', $event_outputDate);

        if($this->m_upcoming_events_list->m_n_results || $this->m_finished_events_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);

        	if($this->m_upcoming_events_list->m_n_results)
            {
                if($event_datetime)
                    $html->parse('upcoming_events_event_datetime_title');

            	$html->parse('upcoming_events_list');
            }

            if($this->m_finished_events_list->m_n_results)
            {
                if($event_datetime)
                    $html->parse('finished_events_event_datetime_title');

                $html->parse('finished_events_list');
            }
        }
        
        $hotdate_place = get_param('hotdate_place');
        $html->setvar('hotdate_place', $hotdate_place);
        $category_id = get_param('category_id');
        $html->setvar('category_title', l(DB::result("SELECT category_title FROM hotdates_category WHERE category_id = " . to_sql($category_id) . " LIMIT 1"), false, 'hotdates_category'));
        $hotdate_datetime = get_param('datetime');
        $dateTime = new DateTime($hotdate_datetime);
        $hotdate_outputDate = $dateTime->format('M/d/Y');
        $html->setvar('hotdate_datetime', $hotdate_outputDate);

        if($this->m_upcoming_hotdates_list->m_n_results || $this->m_finished_hotdates_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);

        	if($this->m_upcoming_hotdates_list->m_n_results)
            {
                if($hotdate_datetime)
                    $html->parse('upcoming_hotdates_hotdate_datetime_title');

            	$html->parse('upcoming_hotdates_list');
            }

            if($this->m_finished_hotdates_list->m_n_results)
            {
                if($hotdate_datetime)
                    $html->parse('finished_hotdates_hotdate_datetime_title');

                $html->parse('finished_hotdates_list');
            }
        }

        $partyhou_place = get_param('partyhou_place');
        $html->setvar('partyhou_place', $partyhou_place);
        $category_id = get_param('category_id');
        $html->setvar('category_title', l(DB::result("SELECT category_title FROM partyhouz_category WHERE category_id = " . to_sql($category_id) . " LIMIT 1"), false, 'partyhouz_category'));
        $partyhou_datetime = get_param('datetime');
        $dateTime = new DateTime($partyhou_datetime);
        $partyhou_outputDate = $dateTime->format('M/d/Y');
        $html->setvar('partyhou_datetime', $partyhou_outputDate);

        if($this->m_upcoming_partyhouz_list->m_n_results || $this->m_finished_partyhouz_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);

        	if($this->m_upcoming_partyhouz_list->m_n_results)
            {
                if($partyhou_datetime)
                    $html->parse('upcoming_partyhouz_partyhou_datetime_title');

            	$html->parse('upcoming_partyhouz_list');
            }

            if($this->m_finished_partyhouz_list->m_n_results)
            {
                if($partyhou_datetime)
                    $html->parse('finished_partyhouz_partyhou_datetime_title');

                $html->parse('finished_partyhouz_list');
            }
        }

		parent::parseBlock($html);
	}
}

$page = new CAll("", $g['tmpl']['dir_tmpl_main'] . "all_search.html");
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

$hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
$page->add($hotdates_header);
$hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
$hotdates_sidebar->m_second_block = "most_anticipated";
$page->add($hotdates_sidebar);

$upcoming_hotdates_hotdate_list = new CHotdatesHotdateList("upcoming_hotdates_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$upcoming_hotdates_hotdate_list->m_list_type = "search";
$upcoming_hotdates_hotdate_list->m_hotdate_where_when = false;
$upcoming_hotdates_hotdate_list->m_upcoming = 1;
$page->m_upcoming_hotdates_list = $upcoming_hotdates_hotdate_list;
$page->add($upcoming_hotdates_hotdate_list);

$finished_hotdates_hotdate_list = new CHotdatesHotdateList("finished_hotdates_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$finished_hotdates_hotdate_list->m_list_type = "search";
$finished_hotdates_hotdate_list->m_hotdate_where_when = false;
$finished_hotdates_hotdate_list->m_upcoming = 0;
$page->m_finished_hotdates_list = $finished_hotdates_hotdate_list;
$page->add($finished_hotdates_hotdate_list);


$upcoming_random_hotdates_hotdate_list = new CHotdatesHotdateList("upcoming_random_hotdates_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$upcoming_random_hotdates_hotdate_list->m_list_type = "random";
$upcoming_random_hotdates_hotdate_list->m_hotdate_where_when = false;
$upcoming_random_hotdates_hotdate_list->m_upcoming = 1;
$page->m_upcoming_random_hotdates_list = $upcoming_random_hotdates_hotdate_list;
$page->add($upcoming_random_hotdates_hotdate_list);

$partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
$page->add($partyhouz_header);
$partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
$partyhouz_sidebar->m_second_block = "most_anticipated";
$page->add($partyhouz_sidebar);

$upcoming_partyhouz_partyhou_list = new CpartyhouzpartyhouList("upcoming_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$upcoming_partyhouz_partyhou_list->m_list_type = "search";
$upcoming_partyhouz_partyhou_list->m_partyhou_where_when = false;
$upcoming_partyhouz_partyhou_list->m_upcoming = 1;
$page->m_upcoming_partyhouz_list = $upcoming_partyhouz_partyhou_list;
$page->add($upcoming_partyhouz_partyhou_list);

$finished_partyhouz_partyhou_list = new CpartyhouzpartyhouList("finished_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$finished_partyhouz_partyhou_list->m_list_type = "search";
$finished_partyhouz_partyhou_list->m_partyhou_where_when = false;
$finished_partyhouz_partyhou_list->m_upcoming = 0;
$page->m_finished_partyhouz_list = $finished_partyhouz_partyhou_list;
$page->add($finished_partyhouz_partyhou_list);


$upcoming_random_partyhouz_partyhou_list = new CpartyhouzpartyhouList("upcoming_random_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$upcoming_random_partyhouz_partyhou_list->m_list_type = "random";
$upcoming_random_partyhouz_partyhou_list->m_partyhou_where_when = false;
$upcoming_random_partyhouz_partyhou_list->m_upcoming = 1;
$page->m_upcoming_random_partyhouz_list = $upcoming_random_partyhouz_partyhou_list;
$page->add($upcoming_random_partyhouz_partyhou_list);


include("./_include/core/main_close.php");
