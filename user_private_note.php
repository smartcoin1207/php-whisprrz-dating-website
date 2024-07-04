<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");
include("./_include/current/friends.php");

class CPrivateNote extends CHtmlBlock
{

    var $message;

    function action()
    {
        global $g;
        global $g_user;
        $cmd = get_param("cmd", "");
        $user_id = get_param("uid", "");

        $my_message = get_param("note", "");
        
        if ($cmd == "save_note") {

            $sql = "INSERT INTO users_private_note (user_id, from_user_id, comment) VALUES (" . to_sql($g_user['user_id'], 'Number') . ", 
            " . to_sql($user_id, 'Number') . ", " . to_sql($my_message) . ") ON DUPLICATE KEY UPDATE comment=" . to_sql($my_message) . "";
            DB::execute($sql);
            set_session("saved", "yes");
            redirect("user_private_note.php?uid={$user_id}");
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user;
        
        $uid = get_param("uid", "");
        $html->setvar("user_id", $uid);
        
        DB::query("SELECT comment FROM users_private_note WHERE user_id=" . get_session("user_id") . " AND from_user_id=" . $uid . "");
        $comment = DB::fetch_row();
        
        $html->setvar("note", $comment['comment']);
        
        $saved = get_session("saved");
        $html->setvar("saved", $saved);
        delses("saved");
        $username = User::getInfoBasic($uid, "name");
        $html->setvar("username", $username);

        parent::parseBlock($html);
    }
}

$page = new CPrivateNote("", $g['tmpl']['dir_tmpl_main'] . "user_private_note.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
