<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("./_include/core/main_start.php");


require_once("./_include/current/hotdates/tools.php");

function do_action() {
    global $g_user;

    $cmd = get_param('cmd');
    $event_id = intval(get_param('event_id'));
    $hotdate_id = intval(get_param('hotdate_id'));
    $partyhou_id = intval((get_param('partyhou_id')));

    if ($event_id) {
        $event = CEventsTools::retrieve_event_by_id($event_id);
        
        if(CEventsTools::guestHandle($event_id, $cmd)) {
            $response = json_encode( array(
                'success' =>  true,
                'event_id' => $event_id
            ));
            if($cmd == 'add') {
                $response = json_encode( array(
                    'success' =>  true,
                    'pending' => $event['event_approval'] == 1 ? true : false,
                    'event_id' => $event_id
                ));
            }
            
            echo $response; die();
        } else {
            $response = json_encode(array(
                'success' => false,
            ));
            echo $response; die();
        }
    } elseif($hotdate_id) {
        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
        
        if(ChotdatesTools::guestHandle($hotdate_id, $cmd)) {
            $response = json_encode( array(
                'success' =>  true,
                'event_id' => $hotdate_id
            ));
            if($cmd == 'add') {
                $response = json_encode( array(
                    'success' =>  true,
                    'pending' => $hotdate['hotdate_approval'] == 1 ? true : false,
                    'event_id' => $hotdate_id
                ));
            }
            
            echo $response; die();
        } else {
            $response = json_encode(array(
                'success' => false,
            ));
            echo $response; die();
        }
    } elseif($partyhou_id) {
        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
        
        if(CpartyhouzTools::guestHandle($partyhou_id, $cmd)) {
            $response = json_encode( array(
                'success' =>  true,
                'partyhou_id' => $partyhou_id
            ));
            if($cmd == 'add') {
                $response = json_encode( array(
                    'success' =>  true,
                    'pending' => $partyhou['partyhou_approval'] == 1 ? true : false,
                    'partyhou_id' => $partyhou_id
                ));
            }
            
            echo $response; die();
        } else {
            $response = json_encode(array(
                'success' => false,
            ));
            echo $response; die();
        }
    }
    else {
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