<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");
require_once("./_include/current/approve_mail_sent.php");

class CUserCertify extends CHtmlBlock
{

    var $message;

    function action()
    {
        global $g;
        global $g_user;
        $cmd = get_param("cmd", "");

        if ($cmd == "submit_certify") {

            $user_to = get_param("uid", "");

            $certify_text = get_param("certify_text", "");

            /*$sql = "INSERT INTO user_report (user_from, user_to, priority, report_text) VALUES (" . to_sql($g_user['user_id'], 'Number') . ", 
            " . to_sql($user_to, 'Number') . ", " . to_sql($priority) . ", " . to_sql($report_text) . ")";
            DB::execute($sql);*/

            $data = array(
                'user_from' => $g_user['user_id'],
                'user_to' => $user_to,
                'certify_text' => $certify_text
            );
            DB::insert('user_certify', $data);

            $sender_user = User::getInfoBasic($g_user['user_id']);
            $to_username = User::getInfoBasic($user_to, "name");

            $url = $g['path']['base_url_main_head'] . "my_certify.php";

            $vars = array("username" => $to_username, "sender_username" => $sender_user['name'], "certify_link" => "<a href=\"$url\">" . l("click_here_to_check") . "</a>");

            $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'certify_email'");
            $subject = $mail_row['subject'];
            $text = $mail_row['text'];
            $subject = $mail_row['subject'];
            $ful_text = Common::replaceByVars($text, $vars);
            $subject = Common::replaceByVars($subject, $vars);
            CApproveMail::approve_sent_mail($user_to, $subject, $ful_text);

            set_session("saved", "yes");
            redirect("user_certify.php?uid={$user_to}");
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

$page = new CUserCertify("", $g['tmpl']['dir_tmpl_main'] . "user_certify.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
