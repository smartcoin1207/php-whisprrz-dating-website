<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CTicketReply extends CHtmlBlock
{
    function action()
    {
        global $p;
        global $g;
        global $g_user;

        $cmd = get_param("cmd", "");

        if ($cmd == "reply_ticket") {
            $id = get_param("id");
            $ticket_reply = get_param("ticket_reply");
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
                        $filename = "ticket_reply_" . time() . "." . $ext;

                        $im->saveImage($file . $filename, $g['image']['quality_orig']);
                        unset($im);

                        $attachment = $filename;
                    }
                }
            }

            $admin_id = DB::field("user", "user_id", "admin='1'", '', '1');

            $data = array(
                'ticket_id' => $id,
                'user_id' => $admin_id[0],
                'msg' => $ticket_reply,
                'attachment' => $attachment
            );

            DB::insert('ticket_replies', $data);

            DB::update('support_tickets', array('last_reply' => $admin_id[0]), '`id` = ' . to_sql($id, 'Number'));

            set_session("saved", "yes");
            redirect("{$p}?ticket_id=" . $id);
        }
    }
    function parseBlock(&$html)
    {
        global $g;
        global $p;

        $id = get_param('ticket_id');

        DB::query("SELECT * FROM `support_tickets` WHERE id={$id}");

        $ticket = DB::fetch_row();
        $patch = Common::getOption('url_files', 'path');
        $user = User::getInfoBasic($ticket['user_from']);
        $userProfile = $patch . User::getPhotoDefault($ticket['user_from']);

        $html->setvar("photo", $userProfile);
        $html->setvar("id", $id);
        $html->setvar("id_user", $ticket['user_from']);
        $html->setvar("user_from", $user['name']);
        $html->setvar("subject", $ticket['title']);
        $html->setvar("priority", ucfirst($ticket['priority']));
        $html->setvar("text", nl2br($ticket['msg']));
        $html->setvar("date_sent", date("d M, Y h:i a", strtotime($ticket['date'])));

        $attach = '';
        if (!empty($ticket['attachment'])) {

            $attached_file = $patch . "support_ticket/" . $ticket['attachment'];
            $attach = '<a href="' . $attached_file . '" data-lightbox="attachment_' . $ticket['id'] . '"><img class="img_border" src="' . $attached_file . '" style="width: 100px;"></a>';
        }

        $html->setvar("ticket_attachment", $attach);

        // Get replies
        DB::query("SELECT id as tr_id, user_id, msg, attachment as tr_attachment, `date` FROM ticket_replies WHERE ticket_id=" . to_sql($id, "Number") . " ORDER BY `date` asc", 2);
        while ($row = DB::fetch_row(2)) {
            if ($row['tr_attachment'] != '') {
                $patch = Common::getOption('url_files', 'path');
                $attached_file = $patch . "support_ticket/" . $row['tr_attachment'];
                $row['tr_attachment'] = '<a href="' . $attached_file . '" data-lightbox="attachment_' . $row['tr_id'] . '"><img class="img_border" src="' . $attached_file . '" style="width: 100px;"></a>';
            }
            $reply_by = User::getInfoBasic($row['user_id'], "name");
            $row['msg'] = nl2br($row['msg']);
            $row['date'] = date("d M, Y h:i a", strtotime($row['date']));
            $row['reply_by'] = $reply_by;
            foreach ($row as $k => $v) {
                $html->setvar($k, $v);
            }

            $html->parse('ticket_reply_list', true);
        }

        parent::parseBlock($html);
    }
}

$page = new CTicketReply('main', $g['tmpl']['dir_tmpl_administration'] . 'ticket_reply.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuBlock());

include('../_include/core/administration_close.php');
