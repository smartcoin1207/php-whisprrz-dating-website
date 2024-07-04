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

class CEvents extends CHtmlBlock
{
	var $m_event;

	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $event_id = get_param('event_id', '');
        if($event_id) {
	        $is_approved = CEventsTools::is_approved_sql();
	        $event_sql = "SELECT * FROM events_event e WHERE event_id = " . to_sql($event_id) . $is_approved . " LIMIT 1";

	        $event = DB::row($event_sql);

	        if(!$event) {
	        		redirect(Common::toHomePage());
	        }
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		if($this->m_event && !$this->m_event['event_private'])
		{
			$html->parse('comments_title');
		}

        $state = User::isNarrowBox('events');
        if  ($state) {
           $html->setvar('display', 'table-cell'); 
           $html->setvar('hide_narrow_box', 'block'); 
           $html->setvar('show_narrow_box', 'none'); 
        } else {
           $html->setvar('display', 'none'); 
           $html->setvar('hide_narrow_box', 'none'); 
           $html->setvar('show_narrow_box', 'block');            
        }
        
		parent::parseBlock($html);
	}
}

$page = new CEvents("", $g['tmpl']['dir_tmpl_main'] . "events_event_show.html");


$events_event_show = new CEventsEventShow("events_event_show", $g['tmpl']['dir_tmpl_main'] . "_events_event_show.html");
$page->add($events_event_show);
$events_event_image_list = new CEventsEventImageList("events_event_image_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_image_list.html");
$events_event_show->add($events_event_image_list);

$events_header = new CEventsHeader("events_header", $g['tmpl']['dir_tmpl_main'] . "_events_header.html");
$page->add($events_header);

{
    $event_id = get_param('event_id');
    if (!User::isNarrowBox('events')) CEventsTools::$thumbnail_postfix = 'orig';
    $event = CEventsTools::retrieve_event_by_id($event_id);
    if($event)
    {
        $page->m_event = $event;

    	$events_sidebar = new CEventsSidebar("events_sidebar", $g['tmpl']['dir_tmpl_main'] . "_events_sidebar.html");
    	$events_sidebar->m_first_block = "";
        $events_sidebar->m_second_block = "event_show";
        $page->add($events_sidebar);

        if($event['event_private'])
		{
            $events_sidebar->m_first_block = "most_discussed";
			$events_sidebar->m_second_block = "popular_finished";
		}
    	else
    	{
	        $events_event_guest_list = new CEventsEventGuestList("events_event_guest_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_guest_list.html");
	        $page->add($events_event_guest_list);

			$events_event_comment_list = new CEventsEventCommentList("events_event_comment_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_comment_list.html");
			$events_event_comment_list->m_need_not_found_message = false;
			$page->add($events_event_comment_list);
    	}
    }
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$events_custom_head = new CEventsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_events_custom_head.html");
$header->add($events_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
