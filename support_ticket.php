<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");
require_once("./_include/current/approve_mail_sent.php");

class CSupportTicket extends CHtmlBlock
{

    var $message;

    function action()
    {
        global $g;
        global $g_user;
        $cmd = get_param("cmd", "");

        if ($cmd == "submit_ticket") {

            // Get Tier 1 user id
            $assign_to = get_tier_user(1);
            $priority = get_param("priority", "");
            $report_title = get_param("ticket_title", "");
            $report_text = get_param("ticket_text", "");
            $attachment = "";

            if ($_FILES['attachment']['name'] != '') {
                $allowed =  array('jpeg', 'jpg', "png", "gif");
                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $imageError = "jpeg, png, gif only";
                } else {

                    $im = new Image();
                    if ($im->loadImage($_FILES['attachment']['tmp_name'])) {
                        $patch = Common::getOption('url_files', 'path');
                        $file = $patch . "support_ticket/";
                        $filename = "support_ticket_" . time() . "." . $ext;
                        
                        $im->saveImage($file . $filename, $g['image']['quality_orig']);
                        unset($im);

                        $attachment = $filename;
                    }
                }
            }

            $data = array(
                'user_from' => $g_user['user_id'],
                'assign_to' => $assign_to,
                'title' => $report_title,
                'priority' => $priority,
                'msg' => $report_text,
                'attachment' => $attachment,
                'last_reply' => $g_user['user_id']
            );

            DB::insert('support_tickets', $data);
            // var_dump($data); die()
            
            $last_id = DB::insert_id();
            $where = 'id = ' . to_sql($last_id);
            $ticket = DB::one('support_tickets', $where);

            $user = User::getInfoBasic($g_user['user_id']);

            $url = $g['path']['base_url_main_head'] . "support_tickets.php";
            
            $vars = array("username" => $user['name'], "ticket_link" => "<a href=\"$url\">" . l("click_here_to_check") . "</a>", "date" => date('n/j/y', strtotime($ticket['date'])));

            $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'support_ticket'");
            $subject = $mail_row['subject'];
            $text = $mail_row['text'];
            $subject = $mail_row['subject'];
            $ful_text = Common::replaceByVars($text, $vars);
            $subject = Common::replaceByVars($subject, $vars);
            CApproveMail::approve_sent_mail($g_user['user_id'], $subject, $ful_text);


            set_session("saved", "yes");
            redirect("support_ticket.php");
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user;

        $uid = get_param("uid", "");

        $saved = get_session("saved");
        $html->setvar("saved", $saved);
        delses("saved");
        $username = User::getInfoBasic($uid, "name");
        $html->setvar("username", $username);
        
        parent::parseBlock($html);
    }
}

$page = new CSupportTicket("", $g['tmpl']['dir_tmpl_main'] . "support_ticket.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
