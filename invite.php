<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");
include("./_include/current/friends.php");

$confirm = get_param('confirm');
if($confirm != 'ok') {
    payment_check('invite');
}

class CInvite extends CHtmlBlock {

    var $message;

    function action()
    {
        global $g;
        global $g_user;
        $cmd = get_param("cmd", "");
        $confirm = get_param("confirm", "");
        $user_id = get_param("user_id", "");
        $fr_user_id = get_param("fr_user_id", "");

        $my_message = get_param("msg", "");

        $emailAuto = Common::automailInfo('invite', Common::getOption('lang_loaded', 'main'));

        $subject = $emailAuto['subject'];
        $subject = str_replace("{title}", $g['main']['title'], $subject);
        $subject = str_replace("{name}", $g_user['name'], $subject);

        $message = $emailAuto['text'];
        $message = str_replace("{name}", $g_user['name'], $message);
        $message = str_replace("{title}", $g['main']['title'], $message);



        if ($my_message != '')
          $message.="\r\n\r\n" . $my_message;

// FIRST : simple mails

        if ($cmd == "invite") {
            for ($i = 1; $i < 5; $i++) {
                $fname = get_param("fn$i", "");
                $lname = get_param("ln$i", "");
                $email = get_param("em$i", "");


                if ($email && trim($email) != guser('mail')) {
                    // SEND INVITE
                    //$text = $this->friend_link($message, $email);
                    $row = $this->friend_key();
                    // $email = trim("$fname $lname<$email>");
                    $text = ($my_message == '') ? '' : $my_message;
                    $vars = array('fid' =>  $g_user['user_id'],
                                  'name' => $g_user['name'],
                                  'title' => $g['main']['title'],
                                  'key' => $row['key'],
                                  'id' => $row['id'],
                                  'text' => $my_message);

                    // var_dump($email); die();

                    Common::sendAutomail(Common::getOption('lang_loaded', 'main'), $email, 'invite', $vars);
                    //send_mail(trim("$fname $lname<$email>"), $g['main']['info_mail'], $subject, $text);
                }
            }
            redirect("invite.php?cmd=invites_sent");
        }

// SECOND : inviters

        if ($cmd == "invite_friends") {
            $my_message = get_param("message", "");

// CHECKBOXES
            $contact = get_param_array("contact");
            $email = get_param_array("email");
// MAILS

            $emails = array();
            foreach ($contact as $val)
                $emails[] = $email[$val];

            $i = 0;

            while (isset($emails[$i])) {
                $text = $this->friend_link($message, $emails[$i]);
                if (trim($emails[$i]) != '') {
                    send_mail($emails[$i], $g['main']['info_mail'], $subject, $text);
                    #print $emails[$i];
                }
                $i++;
            }
            redirect("invite.php?cmd=invites_sent");
        }

        if ($confirm == 'ok') {
            $sql = 'INSERT IGNORE INTO friends_request
                SET accepted = 1,
                    created_at = ' . to_sql(date('Y-m-d H:i:s'), 'Text') . ',
                    user_id = ' . to_sql($user_id, 'Number') . ',
                    friend_id = ' . to_sql(guid(), 'Number');
            DB::execute($sql);

            redirect('my_friends.php');
        }
    }

    function friend_key()
    {
        global $g_user;

        $key = md5(guid() . microtime() . time() . rand(0, 1000000));

        $sql = 'INSERT INTO invites
            SET user_id = ' . to_sql(guid(), 'Number') . ',
                invite_key = ' . to_sql($key, 'Text');
        DB::execute($sql);
        $id = DB::insert_id();

        return array('key' => $key, 'id' => $id);
    }

    function friend_link($message, $email)
    {
        global $g_user;

        $key = md5(guid() . microtime() . time() . rand(0, 1000000));

        $sql = 'INSERT INTO invites
            SET user_id = ' . to_sql(guid(), 'Number') . ',
                invite_key = ' . to_sql($key, 'Text');
        DB::execute($sql);
        $id = DB::insert_id();

        $link = Common::urlSite() . 'invite_confirm.php?id=' . $id . '&key=' . $key;

        $message = str_replace('{link}', $link, $message);

        return $link;
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user;
        global $l;
        CBanner::getBlock($html, 'right_column');

        $cmd = get_param("cmd", "");
        $action = get_param("action", "");
        $service = get_param("service", "");
        $confirm = get_param("confirm", "");
        $user_id = get_param("user_id", "");
        $fr_user_id = get_param("fr_user_id", "");
        $import = get_param("import", "");

        for ($index = 1; $index <= 4; $index++) {
            $html->setvar('index', $index);
            $html->parse('invite_emails_row');
        }

        if ($cmd == "") {
            $html->parse("invite_emails");
            $html->parse("invite_form", true);
        }

        if ($cmd == "invites_sent") {
            $html->parse("invites_sent", true);
            $html->parse("invite_emails");
            $html->parse("invite_form", true);
        }

        if ($cmd == "show") {
            $html->setvar("service", $service);
            $html->setvar("row", "_1");
            $html->setvar($service, "_module");
            $html->parse("invite_module", true);
            $html->parse("invite_form", true);
        }

        if ($confirm == "ok") {
            $result = DB::execute("UPDATE friends SET data='" . date("Y-m-d") . "' WHERE md5(user_id)=" . to_sql($user_id) . " and md5(fr_user_id)=" . to_sql($fr_user_id) . "");
            $html->parse("invite_confirm", true);
        }



        parent::parseBlock($html);
    }

}

$page = new CInvite("", $g['tmpl']['dir_tmpl_main'] . "invite.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$show = get_param("show", "all");
$friends_menu = new CFriendsMenu("friends_menu", $g['tmpl']['dir_tmpl_main'] . "_friends_menu.html");
$friends_menu->active_button = "wall_invite_friends";
$page->add($friends_menu);

include("./_include/core/main_close.php");
?>