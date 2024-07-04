<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");
require_once("./_include/current/approve_mail_sent.php");
    
if(!Common::isOptionActive('reports_approval')) {
    redirect(Common::getHomePage());
}

class CUserReport extends CHtmlBlock
{

    var $message;

    function action()
    {
        global $g;
        global $g_user;
        $cmd = get_param("cmd", "");

        if(!Common::isOptionActive('reports_approval')) {
            return false;
        }

        if ($cmd == "submit_report") {

            $user_to = get_param("uid", "");
            $priority = get_param("priority", "");
            $report_title = get_param("report_title", "");
            $report_text = get_param("report_text", "");

            /*$sql = "INSERT INTO user_report (user_from, user_to, priority, report_text) VALUES (" . to_sql($g_user['user_id'], 'Number') . ", 
            " . to_sql($user_to, 'Number') . ", " . to_sql($priority) . ", " . to_sql($report_text) . ")";
            DB::execute($sql);*/

            $data = array(
                'user_from' => $g_user['user_id'],
                'user_to' => $user_to,
                'title' => $report_title,
                'priority' => $priority,
                'msg' => $report_text,
                'photo_id' => 0
            );
            DB::insert('users_reports', $data);

            $user = User::getInfoBasic($g_user['user_id']);
            $reported_username = User::getInfoBasic($user_to, "name");

            $vars = array("username" => $user['name'], "reported_username" => $reported_username);

            $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'report_submit_email'");
            $subject = $mail_row['subject'];
            $text = $mail_row['text'];
            $subject = $mail_row['subject'];
            $ful_text = Common::replaceByVars($text, $vars);
            $subject = Common::replaceByVars($subject, $vars);
            CApproveMail::approve_sent_mail($g_user['user_id'], $subject, $ful_text);


            set_session("saved", "yes");
            redirect("user_report.php?uid={$user_to}");
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user;

        $uid = get_param("uid", "");
        $html->setvar("user_id", $uid);

        $saved = get_session("saved");
        $html->setvar("saved", $saved);
        delses("saved");
        $username = User::getInfoBasic($uid, "name");
        $html->setvar("username", $username);

        parent::parseBlock($html);
    }
}

$page = new CUserReport("", $g['tmpl']['dir_tmpl_main'] . "user_report.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
