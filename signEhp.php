<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

require_once("./_include/current/hotdates/tools.php");
require_once("./_include/current/events/tools.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/main/calendar.php");
require_once("./_include/current/events/calendar.php");
require_once("./_include/current/hotdates/calendar.php");
require_once("./_include/current/partyhouz/calendar.php");

function do_action() {
    global $g_user, $g, $p;

    $cmd = get_param('cmd');
    $event_id = intval(get_param('event_id'));
    $hotdate_id = intval(get_param('hotdate_id'));
    $partyhou_id = intval((get_param('partyhou_id')));
    $mainCalendar = get_param('mainCalendar', '');

    if ($event_id) {
        $event = CEventsTools::retrieve_event_by_id($event_id);
        
        if(CEventsTools::guestHandle($event_id, $cmd)) {
            $pending = $event['event_approval'] == 1 ? true: false;
            
            $date_time = $event['event_datetime'];
            $_POST['guest_sign_day'] = $date_time;
            
            $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar.html";
            if (Common::isOptionActiveTemplate('event_social_enabled')) {
                $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar_items.html";
            }

            if($mainCalendar == '1') {
                $p = 'main_calendar_ajax.php';
                $page = new CMainCalendar("", $tmpl);
            } else {
                $page = new CEventsCalendar("", $tmpl);
            }
            $page->m_need_container = false;
            loadPageContentAjaxSign($page, $pending);
        } else {
            $response = json_encode(array(
                'success' => false,
            ));
            echo $response; die();
        }
    } elseif($hotdate_id) {
        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
        
        if(ChotdatesTools::guestHandle($hotdate_id, $cmd)) {
            $pending = $hotdate['hotdate_approval'] == 1 ? true: false;
            
            $date_time = $hotdate['hotdate_datetime'];
            $_POST['guest_sign_day'] = $date_time;
            
            if($mainCalendar == '1') {
                $p = 'main_calendar_ajax.php';
                $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar.html";
                if (Common::isOptionActiveTemplate('event_social_enabled')) {
                    $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar_items.html";
                }
                $page = new CMainCalendar("", $tmpl);
            } else {
                $tmpl = $g['tmpl']['dir_tmpl_main'] . "_hotdates_calendar.html";
                if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
                    $tmpl = $g['tmpl']['dir_tmpl_main'] . "_hotdates_calendar_items.html";
                }
                $page = new CHotdatesCalendar("", $tmpl);
            }

            $page->m_need_container = false;
            loadPageContentAjaxSign($page, $pending);
        } else {
            $response = json_encode(array(
                'success' => false,
            ));
            echo $response; die();
        }
    } elseif($partyhou_id) {
        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
        
        if(CpartyhouzTools::guestHandle($partyhou_id, $cmd)) {
            $pending = $partyhou['partyhou_approval'] == 1 ? true: false;
            
            $date_time = $partyhou['partyhou_datetime'];
            $_POST['guest_sign_day'] = $date_time;

            if($mainCalendar == '1') {
                $p = 'main_calendar_ajax.php';

                $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar.html";
                if (Common::isOptionActiveTemplate('event_social_enabled')) { 
                    $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar_items.html";
                }
                $page = new CMainCalendar("", $tmpl);
            } else {
                $tmpl = $g['tmpl']['dir_tmpl_main'] . "_partyhouz_calendar.html";
                if (Common::isOptionActiveTemplate('partyhou_social_enabled')) {
                    $tmpl = $g['tmpl']['dir_tmpl_main'] . "_partyhouz_calendar_items.html";
                }
                $page = new CPartyhouzCalendar("", $tmpl);
            }
            
            $page->m_need_container = false;
            loadPageContentAjaxSign($page, $pending);
        } else {
            $response = json_encode(array(
                'success' => false,
            ));
            echo $response; die();
        }
    } else {
        $response = json_encode(array(
            'success' => false,
        ));
        echo $response;
        die();
    }
}

do_action();

include("./_include/core/main_close.php");
?>