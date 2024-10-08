<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/events/tools.php");
require_once("./_include/current/approve_mail_sent.php");

function do_action()
{
	global $g_user;

    $isAll = get_param('isAll', '');
    $ajax = get_param_int('ajax');

    if($ajax) {
        if($isAll) {
            $event_id_all = get_param('event_id', '');
            $hotdate_id_all = get_param('hotdate_id', '');
            $partyhou_id_all = get_param('partyhou_id', '');
            $craigs_id_all = get_param('craigs_id', '');
            $wowslider_id_all = get_param('wowslider_id', '');

          
            if($event_id_all && isset($g_user['moderator_events']) && $g_user['moderator_events']) {

                foreach ($event_id_all as $key => $event_id) {


                    $event = DB::row("SELECT * FROM events_event WHERE event_id = '" . $event_id . "'");
                    $event_private = $event['event_private'];
                    $user_id = $event['user_id'];
                    $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");
    
                    $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'events_approved'");
                    $text = $mail_row['text'];
                    $subject = $mail_row['subject'];
                    $var['name'] = $user['name'];
                    $var['item_title'] = $event['event_title'];
                    $ful_text = Common::replaceByVars($text, $var);
    
                    CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                    DB::execute("UPDATE events_event SET approved = 1   WHERE event_id = '".$event_id ."'");

                    if(!$event_private){
                        Wall::add('event_added', $event_id, $user_id);
                    }
                }
            } else if($hotdate_id_all && isset($g_user['moderator_hotdates']) && $g_user['moderator_hotdates']) {
                foreach ($hotdate_id_all as $key => $hotdate_id) {
                    $hotdate = DB::row("SELECT * FROM hotdates_hotdate WHERE hotdate_id = '" . $hotdate_id . "'");
                    $hotdate_private = $hotdate['hotdate_private'];
                    $user_id = $hotdate['user_id'];
                    $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");
    
                    $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'hotdates_approved'");
                    $text = $mail_row['text'];
                    $subject = $mail_row['subject'];
                    $var['name'] = $user['name'];
                    $var['item_title'] = $hotdate['hotdate_title'];
                    $ful_text = Common::replaceByVars($text, $var);
    
                    CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);
    
                    DB::execute("UPDATE hotdates_hotdate SET approved = 1   WHERE hotdate_id = '".$hotdate_id ."'");
                    if(!$hotdate_private) {
                        Wall::add('hotdate_added', $hotdate_id, $user_id);
                    }
                }
            } else if($partyhou_id_all && isset($g_user['moderator_partyhouz']) && $g_user['moderator_partyhouz']) {
                foreach ($partyhou_id_all as $key => $partyhou_id) {
                    $partyhouz = DB::row("SELECT * FROM partyhouz_partyhou WHERE partyhou_id = '" . $partyhou_id . "'");
                    $partyhou_private = $partyhouz['partyhou_private'];

                    $user_id = $partyhouz['user_id'];
                    $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");
    
                    $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'partyhouz_approved'");
                    $text = $mail_row['text'];
                    $subject = $mail_row['subject'];
                    $var['name'] = $user['name'];
                    $var['item_title'] = $partyhouz['partyhou_title'];
                    $ful_text = Common::replaceByVars($text, $var);
    
    
                    CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                    DB::execute("UPDATE partyhouz_partyhou SET approved = 1   WHERE partyhou_id = '".$partyhou_id ."'");
                    if(!$partyhou_private) {
                        Wall::add('partyhou_added', $partyhou_id, $user_id);
                    }
                }
            } else if($craigs_id_all && isset($g_user['moderator_craigs']) && $g_user['moderator_craigs']) {
                foreach ($craigs_id_all as $key => $craig) {
                    preg_match('/(\d+)([a-zA-Z]+)/', $craig, $matches);
                    $craigs_id = $matches[1];
                    $cat_name = $matches[2];
                    $table = "adv_" .$cat_name;

                    $craigs = DB::row("SELECT * FROM ". $table ." WHERE id = '" . $craigs_id . "'");
                    $user_id = $craigs['user_id'];
                    $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");

                    $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'craigs_approved'");
                    $text = $mail_row['text'];
                    $subject = $mail_row['subject'];
                    $var['name'] = $user['name'];
                    $var['item_title'] = $craigs['subject'];
                    $ful_text = Common::replaceByVars($text, $var);

                    CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                    DB::execute("UPDATE " . $table . " SET approved  = 1 WHERE id = '" . $craigs_id . "'");
                    // Wall::add('partyhou_added', $partyhou_id, $user_id);

                }
            } else if($wowslider_id_all && isset($g_user['moderator_wowslider']) && $g_user['moderator_wowslider']) {
                foreach ($wowslider_id_all as $key => $wowslider_id) {
                    $wowslider = DB::row("SELECT * FROM wowslider WHERE event_id = '" . $wowslider_id . "'");
                    $user_id = $wowslider['user_id'];
                    $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");
    
                    $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'wowslider_approved'");
                    $text = $mail_row['text'];
                    $subject = $mail_row['subject'];
                    $var['name'] = $user['name'];
                    $var['item_title'] = $wowslider['title'];
                    $ful_text = Common::replaceByVars($text, $var);
    
    
                    CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                    DB::execute("UPDATE wowslider SET approved = 1   WHERE event_id = '".$wowslider_id ."'");
                }
            }
            
                die(getResponseDataAjaxByAuth(true));
        } else {
            $event_id = get_param('event_id', '');
            $hotdate_id = get_param('hotdate_id', '');
            $partyhou_id = get_param('partyhou_id', '');
            $craigs_id = get_param('craigs_id', '');
            $wowslider_id = get_param('wowslider_id', '');

            if($event_id && isset($g_user['moderator_events']) && $g_user['moderator_events']) {

                $event = DB::row("SELECT * FROM events_event WHERE event_id = '" . $event_id . "'");
                $event_private = $event['event_private'];

                $user_id = $event['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'events_approved'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $event['event_title'];
                $ful_text = Common::replaceByVars($text, $var);

                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);
                DB::execute("UPDATE events_event SET approved = 1   WHERE event_id = '".$event_id ."'");
                if(!$event_private){
                    Wall::add('event_added', $event_id, $user_id);
                }

            } else if($hotdate_id && isset($g_user['moderator_hotdates']) && $g_user['moderator_hotdates']) {

                $hotdate = DB::row("SELECT * FROM hotdates_hotdate WHERE hotdate_id = '" . $hotdate_id . "'");
                $user_id = $hotdate['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");
                $hotdate_private = $hotdate['hotdate_private'];

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'hotdates_approved'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $hotdate['hotdate_title'];
                $ful_text = Common::replaceByVars($text, $var);

                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                DB::execute("UPDATE hotdates_hotdate SET approved = 1   WHERE hotdate_id = '".$hotdate_id ."'");
                if(!$hotdate_private) {
                    Wall::add('hotdate_added', $hotdate_id, $user_id);
                }

            } else if($partyhou_id && isset($g_user['moderator_partyhouz']) && $g_user['moderator_partyhouz']) {

                $partyhouz = DB::row("SELECT * FROM partyhouz_partyhou WHERE partyhou_id = '" . $partyhou_id . "'");
                $user_id = $partyhouz['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");
                $partyhou_private = $partyhouz['partyhou_private'];

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'partyhouz_approved'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $partyhouz['partyhou_title'];
                $ful_text = Common::replaceByVars($text, $var);


                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                DB::execute("UPDATE partyhouz_partyhou SET approved = 1   WHERE partyhou_id = '".$partyhou_id ."'");

                if(!$partyhou_private) {
                    Wall::add('partyhou_added', $partyhou_id, $user_id);
                }

            } else if($craigs_id && isset($g_user['moderator_craigs']) && $g_user['moderator_craigs']) {
                $table = "adv_" . get_param('cat_name', '');

                $craigs = DB::row("SELECT * FROM ". $table ." WHERE id = '" . $craigs_id . "'");
                $user_id = $craigs['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'craigs_approved'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $craigs['subject'];
                $ful_text = Common::replaceByVars($text, $var);

                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                DB::execute("UPDATE " . $table . " SET approved  = 1 WHERE id = '" . $craigs_id . "'");
            } else if($wowslider_id && isset($g_user['moderator_wowslider']) && $g_user['moderator_wowslider']) {

                $wowslider = DB::row("SELECT * FROM wowslider WHERE event_id = '" . $wowslider_id . "'");
                $user_id = $wowslider['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'wowslider_approved'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $wowslider['title'];
                $ful_text = Common::replaceByVars($text, $var);

                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

                DB::execute("UPDATE wowslider SET approved = 1   WHERE event_id = '".$wowslider_id ."'");
            }
            
            die(getResponseDataAjaxByAuth(true));
        }
    }
   
}

do_action();

include("./_include/core/main_close.php");