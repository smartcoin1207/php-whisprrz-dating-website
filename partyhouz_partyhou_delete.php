<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/approve_mail_sent.php");

function do_action()
{
	global $g_user;

    $partyhou_id = intval(get_param('partyhou_id'));
    $ajax = get_param_int('ajax');

	if($partyhou_id){
        if($ajax) {
            if(isset($g_user['moderator_partyhouz']) && $g_user['moderator_partyhouz']) {

                $partyhouz = DB::row("SELECT * FROM partyhouz_partyhou WHERE partyhou_id = '" . $partyhou_id . "'");
                $user_id = $partyhouz['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '".$user_id."'");

                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'partyhouz_deleted'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $partyhouz['partyhou_title'];
                $ful_text = Common::replaceByVars($text, $var);


                CpartyhouzTools::delete_partyhou($partyhou_id, true);
                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text);

            }
            die(getResponseDataAjaxByAuth(true));
        } else {
            CpartyhouzTools::delete_partyhou($partyhou_id);
            redirect('partyhouz_calendar.php');
        }
	}
}


do_action();

include("./_include/core/main_close.php");