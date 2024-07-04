<?php
/* (C) Websplosion LLC, 2001-2021

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
require_once("./_include/current/events/event_show.php");
require_once("./_include/current/events/event_image_list.php");
require_once("./_include/current/events/event_guest_list.php");
require_once("./_include/current/events/event_comment_list.php");
require_once("./_include/current/events/event_list.php");

class CEvents extends CHtmlBlock
{
    var $m_upcoming_events_list;
    var $m_finished_events_list;

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

        $redirect = true;

        if($this->m_upcoming_events_list->m_n_results)
        {
            $html->parse('events_i_created');
            $redirect = false;
        }

        if($this->m_finished_events_list->m_n_results)
        {
        	$html->parse('events_i_will_visit');
            $redirect = false;
        }

        if($redirect) {
            redirect('events_event_edit.php');
        }

		parent::parseBlock($html);
	}
}

$page = new CEvents("", $g['tmpl']['dir_tmpl_main'] . "events_my.html");
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
$upcoming_events_event_list->m_list_type = "by_user";
$upcoming_events_event_list->m_event_where_when = false;
$upcoming_events_event_list->m_upcoming = 1;
$page->m_upcoming_events_list = $upcoming_events_event_list;
$page->add($upcoming_events_event_list);

$finished_events_event_list = new CEventsEventList("finished_events_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_list.html");
$finished_events_event_list->m_list_type = "by_user";
$finished_events_event_list->m_event_where_when = false;
$finished_events_event_list->m_upcoming = 0;
$page->m_finished_events_list = $finished_events_event_list;
$page->add($finished_events_event_list);

include("./_include/core/main_close.php");
