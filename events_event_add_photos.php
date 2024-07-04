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

class CEvents extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $event_id = intval(get_param('event_id'));
            if($event_id)
            {
                $event = CEventsTools::retrieve_event_by_id($event_id);
                if($event)
                {
                    $guests = CEventsTools::getGuestUsers($event_id);

                    $is_guest = false;
                    
                    foreach ($guests as $key => $guest) {
                        if($guest['user_id'] == $g_user['user_id']) {
                            $is_guest = true;
                            break;
                        }
                    }

                    if(!$is_guest && $g_user['user_id'] != $event['user_id']) {
                        redirect('events_event_show.php?event_id=' . $event_id);
                    }

                    $time = DB::result('SELECT NOW()');
                    for($image_n = 1; $image_n <= 4; ++$image_n)
                    {
                        $name = "image_" . $image_n;
                        CEventsTools::do_upload_event_image($name, $event_id, $time, $event['event_private'] ? false : true);
                    }

                    redirect('events_event_show.php?event_id=' . $event['event_id']);
                }
            }
            redirect('events.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $event_id = get_param('event_id');

		$event = CEventsTools::retrieve_event_by_id($event_id);

        $guests = CEventsTools::getGuestUsers($event_id);

        $is_guest = false;
        
        foreach ($guests as $key => $guest) {
            if($guest['user_id'] == $g_user['user_id']) {
                $is_guest = true;
                break;
            }
        }

        if(!$is_guest && $g_user['user_id'] != $event['user_id']) {
            redirect('events_event_show.php?event_id=' . $event_id);
        }

        if($event)
        {
            $html->setvar('event_id', $event['event_id']);
        	$html->setvar('event_title', strcut(to_html($event['event_title']), 20));
            $html->setvar('event_title_full', to_html($event['event_title']));
        }

        parent::parseBlock($html);
	}
}

$page = new CEvents("", $g['tmpl']['dir_tmpl_main'] . "events_event_add_photos.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$events_custom_head = new CEventsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_events_custom_head.html");
$header->add($events_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$events_event_show = new CEventsEventShow("events_event_show", $g['tmpl']['dir_tmpl_main'] . "_events_event_show.html");
$page->add($events_event_show);
$events_event_image_list = new CEventsEventImageList("events_event_image_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_image_list.html");
$events_event_show->add($events_event_image_list);

$events_header = new CEventsHeader("events_header", $g['tmpl']['dir_tmpl_main'] . "_events_header.html");
$page->add($events_header);
$events_event_guest_list = new CEventsEventGuestList("events_event_guest_list", $g['tmpl']['dir_tmpl_main'] . "_events_event_guest_list.html");
$page->add($events_event_guest_list);
$events_sidebar = new CEventsSidebar("events_sidebar", $g['tmpl']['dir_tmpl_main'] . "_events_sidebar.html");
$events_sidebar->m_first_block = "";
$events_sidebar->m_second_block = "event_show";
$page->add($events_sidebar);

include("./_include/core/main_close.php");
