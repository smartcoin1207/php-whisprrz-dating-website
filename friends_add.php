<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

checkByAuth();

include("./_include/current/friends.php");



class CHtmlAdd extends CHtmlBlock {

    var $m_on_page = 20;
    var $message = "";
    var $responseData = false;

    function action()
    {
        global $g_user;
        global $l;

        $uid = get_param('uid', '');
        $cmd = get_param('cmd', '');
        $isAjaxResponse = get_param('ajax');
        $this->responseData = false;
        $to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : Common::getHomePage();

        if($uid == guid() && !$isAjaxResponse) {
            redirect($to);
        }

        $redirect = '';
        if ($uid) {
            $user = User::getInfoBasic($uid);
            if ($user) {
                if (User::isFriendRequestExists($uid, guid()) == $uid) {
                    User::friendAdd($uid, guid(), 1);
                    $redirect = $to;
                    $this->responseData = true;
                }
                if (User::isFriend($uid, guid())) {
                    $redirect = $to;
                    $this->responseData = true;
                }
                if (!$isAjaxResponse && $redirect) {
                    redirect($redirect);
                }
            }
        }

        if ($cmd == 'add') {
            if ($user) {
                if (!User::isFriendRequestExists($uid, guid())) {
                    $comment = trim(get_param('comment'));
                    User::friendRequestSend($user, $comment);
                }
                $this->responseData = true;
                if (!$isAjaxResponse && Common::getOption('set', 'template_options') != 'urban') {
                    redirect("friends_add_alerted.php?user_id=" . $user['user_id']);
                }
            }
            if (!$isAjaxResponse) {
                Common::toHomePage();
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g_user;

        $html->setvar("message", $this->message);

        $uid = get_param("uid");

        DB::query("SELECT * FROM user WHERE user_id=" . to_sql($uid, "Number") . " ");

        if (($row = DB::fetch_row()) && $uid != $g_user["user_id"]) {
            $html->setvar("name", $row['name']);
            $html->setvar("friend_user_id", $row['user_id']);
            $html->setvar('add_name_to_friends', lSetVars('add_name_to_friends', array('name' => $row['name'])));
            $html->parse("add_id", true);
        } else {
            Common::toHomePage();
        }

        parent::parseBlock($html);
    }

}

$isAjaxResponse = get_param('ajax');
if ($isAjaxResponse) {
    $page = new CHtmlAdd('', '', '', '', true);
    $page->action(false);
    die(getResponseDataAjaxByAuth($page->responseData));
}


$page = new CHtmlAdd("", $g['tmpl']['dir_tmpl_main'] . "friends_add.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);
$friends_menu = new CFriendsMenu("friends_menu", $g['tmpl']['dir_tmpl_main'] . "_friends_menu.html");
$page->add($friends_menu);


include("./_include/core/main_close.php");