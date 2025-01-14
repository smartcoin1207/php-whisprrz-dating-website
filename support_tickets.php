<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");
require_once("./_include/current/approve_mail_sent.php");

class CSupportTickets extends CHtmlBlock
{

    var $message;

    function action()
    {
        global $g;
        global $g_user;
        $action = get_param('action');
        if ($action == "delete_ticket") {
            $ticketId = get_param('id');

            $where = 'id = ' . to_sql($ticketId);
            $ticket = DB::one('support_tickets', $where);
            if ($ticket && !empty($ticket['attachment'])) {
                $patch = Common::getOption('url_files', 'path');
                unlink($patch . "support_ticket/" . $ticket['attachment']);
            }

            $ticket_replies = DB::select('ticket_replies', 'ticket_id =' . to_sql($ticketId));
            foreach ($ticket_replies as $ticket_replie) {
                if ($ticket_replie && !empty($ticket_replie['attachment'])) {
                    $patch = Common::getOption('url_files', 'path');
                    $patch . "support_ticket/" . $ticket_replie['attachment'];
                    unlink($patch . "support_ticket/" . $ticket_replie['attachment']);
                }
                DB::delete('ticket_replies', 'id =' . to_sql($ticket_replie['id']));
            }

            DB::delete('support_tickets', $where);
            set_session("saved", "yes");
            redirect("support_tickets.php");
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user;

        DB::query("SELECT * FROM support_tickets WHERE user_from={$g_user['user_id']} ORDER BY priority", 2);

        while ($row = DB::fetch_row(2)) {
            if ($row['status'] == '0') {
                $row['status_title'] = ucfirst(l('close'));
            } else {
                $row['status_title'] = ucfirst(l('open'));
            }

            if ($row['attachment'] != '') {
                $patch = Common::getOption('url_files', 'path');
                $attached_file = $patch . "support_ticket/" . $row['attachment'];
                $row['attachment'] = '<a href="' . $attached_file . '" data-lightbox="attachment_' . $row['id'] . '">' . l('click_to_open') . '</a>';
            }
            $row['view_reply'] = ' | <a href="view_ticket.php?id=' . $row['id'] . '">' . l('reply') . '</a>';
            $row['title'] = $row['title'];
            $row['msg'] = nl2br($row['msg']);
            $row['priority'] = ucfirst($row['priority']);
            $row['date'] = date("d M, Y", strtotime($row['date']));

            $unreadticketmessage = DB::row("SELECT count(id) as unread FROM `ticket_replies` where ticket_id={$row['id']} and user_read='0'");
            if ($unreadticketmessage['unread'] > 0){
                $row['unread'] = "<span class=\"unread_message\"><span class=\"purple\">{$unreadticketmessage['unread']}</span></span>";
            }else{
                $row['unread'] = "";
            }

            foreach ($row as $k => $v) {
                $html->setvar($k, $v);
            }

            $html->parse('ticket_list', true);
        }

        $totalmesasge = DB::row("SELECT count(st.id) as total FROM `support_tickets` st LEFT JOIN ticket_replies tr on tr.ticket_id = st.id where user_from={$g_user['user_id']}");
        $unreadmessage = DB::row("SELECT count(st.id) as unread FROM `support_tickets` st LEFT JOIN ticket_replies tr on tr.ticket_id = st.id where user_from={$g_user['user_id']} and user_read='0'");
        $html->setvar("total_messages", $totalmesasge['total']);
        $html->setvar("unread_messages", $unreadmessage['unread']);

        $saved = get_session("saved");
        $html->setvar("saved", $saved);
        delses("saved");

        parent::parseBlock($html);
    }
}

$page = new CSupportTickets("", $g['tmpl']['dir_tmpl_main'] . "support_tickets.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
