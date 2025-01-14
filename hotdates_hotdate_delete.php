<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/hotdates/tools.php");
require_once("./_include/current/approve_mail_sent.php");

function do_action()
{
	global $g_user;

	$hotdate_id = intval(get_param('hotdate_id'));
    $ajax = get_param_int('ajax');

	if($hotdate_id){
        if($ajax) {
            if(isset($g_user['moderator_hotdates']) && $g_user['moderator_hotdates']) {

                
                $hotdate = DB::row("SELECT * FROM hotdates_hotdate WHERE hotdate_id = '" . $hotdate_id . "'");
                $user_id = $hotdate['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'hotdates_deleted'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $hotdate['hotdate_title'];
                $ful_text = Common::replaceByVars($text, $var);


                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);
                CHotdatesTools::delete_hotdate($hotdate_id, true);
            }
            die(getResponseDataAjaxByAuth(true));
        } else {
            CHotdatesTools::delete_hotdate($hotdate_id);
            redirect('hotdates_calendar.php');
        }
	}
}

do_action();

include("./_include/core/main_close.php");