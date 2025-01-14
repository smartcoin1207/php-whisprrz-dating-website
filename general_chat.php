<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

if (!Common::isOptionActive('flashchat')) {
    redirect(Common::getHomePage());
}

checkByAuth();

$isAjaxRequest = get_param('ajax');

if(!$isAjaxRequest) {
    CStatsTools::count('flash_chat_opened');
}

CustomPage::setSelectedMenuItemByTitle('column_narrow_general_chat');

class CChatGeneral extends CHtmlBlock
{
    static $cmd = '';
    static $isAjaxRequest = false;
    static $sendId;

	function action()
	{
		global $g;
		global $g_user;

        self::$isAjaxRequest = get_param('ajax');
        self::$cmd = get_param('cmd');
        Flashchat::updateLastVisitUser();
        if (self::$isAjaxRequest) {
            $cmd = get_param('cmd');
            if ($cmd == 'general_chat_send') {
                $text = get_param('messages');
                self::$sendId = Flashchat::send($text, get_param('room'), '', null, get_param('send'));
                if (in_array(self::$sendId, array('logout', 'system_user_banned'))) {
                    die(getResponseDataAjaxByAuth(self::$sendId));
                }
            } elseif ($cmd == 'general_chat_change_room'){
                Flashchat::changeRoom(get_param('room'), get_param('room_old'));
            }
        } else {
            Flashchat::login();
        }
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
        //To make a class Flashchat

        $guid = $g_user['user_id'];
        $urlFiles = $g['path']['url_files'];
        $currentRoom = Flashchat::getCurrentRoom();
        $html->setvar('current_room', $currentRoom);
        $html->setvar('number_users_visitors', Flashchat::getNumberUsersVisitors());

        $block = 'message';
        $where = '`room` = ' . to_sql($currentRoom);
        $limitMsg = '0, ' . Flashchat::getDefaultLimitMsg();
        if (self::$isAjaxRequest) {
            if (self::$cmd == 'general_chat_send') {
                $where .= ' AND `id` = ' . to_sql(self::$sendId);
            }elseif (self::$cmd != 'general_chat_change_room') {
                $limitMsg = '';
                $where .= ' AND `id` > ' . to_sql(get_param('last_id'));
            }
        } else {
            $html->setvar('home_page', Common::getHomePage());
            $html->setvar('user_id', $guid);
            Flashchat::parseRooms($html);

            $html->setvar("{$block}_user_id", $guid);
            $html->setvar("{$block}_profile_link", User::url($guid));
            $html->setvar("{$block}_user_name", $g_user['name']);
            $html->setvar("{$block}_user_photo", $urlFiles . User::getPhotoDefault($guid, 'r'));
            $html->parse("{$block}_tmpl", false);
        }

        $userPhoto = array();

        $messages = DB::select('flashchat_messages', $where, 'id DESC', $limitMsg);
        krsort($messages);
        $lastId = 0;
        $isBlockAnswer = $html->blockExists("{$block}_responder") && $html->blockExists("{$block}_answer");
        foreach ($messages as $key => $message) {
            $lastId = $message['id'];
            $html->setvar("{$block}_id", $message['id']);
            $html->setvar("{$block}_room", $message['room']);
            $html->setvar("{$block}_send", $message['send']);

            $text = $message['msgtext'];
            $uid = $message['user_id'];
            $text = Flashchat::parseMsg($text, $message['status'], $message['room'], $uid);
            $html->setvar("{$block}_text", nl2br($text));
            $html->setvar("{$block}_user_id", $uid);
            $html->setvar("{$block}_profile_link", User::url($uid));
            if (!isset($userPhoto[$uid]['photo'])) {
                $userPhoto[$uid]['photo'] = $urlFiles . User::getPhotoDefault($uid, 'r');
            }
            $html->setvar("{$block}_user_photo", $userPhoto[$uid]['photo']);
            if (!isset($userPhoto[$uid]['name'])) {
                $userPhoto[$uid]['name'] = User::getInfoBasic($uid, 'name');
            }
            $html->setvar("{$block}_user_name", $userPhoto[$uid]['name']);
            if ($html->varExists("{$block}_date_time_ago")) {
                $html->setvar("{$block}_date_time_ago", timeAgo(date('Y-m-d H:i:s',$message['time']), 'now', 'string', 60, 'second'));
            }else{
                $html->setvar("{$block}_date", Common::dateFormat($message['time'], 'general_chat', false, false, true));
            }
            if ($isBlockAnswer) {
                if ($uid == guid()) {
                    $html->clean("{$block}_responder");
                    $html->parse("{$block}_answer", false);
                } else {
                    $html->clean("{$block}_answer");
                    $html->parse("{$block}_responder", false);
                }
            }
            $html->parse($block, true);
        }

        if ($lastId) {
           $html->setvar('last_id', $lastId);
           $html->parse("{$block}_list", false);
        }

        $blockUser = 'list_user';
        $where = '`room` = ' . to_sql($currentRoom)
               . ' AND `time_out` > ' . to_sql(time() - Flashchat::$onlineTime);
        $users = DB::select('flashchat_users', $where, '`time_out` DESC');
        foreach ($users as $user) {
            $userInfo = User::getInfoBasic($user['user_id']);
            $html->setvar("{$blockUser}_photo", User::getPhotoDefault($user['user_id'], 'r'));
            $html->setvar("{$blockUser}_name", User::nameOneLetterFull($userInfo['name']));
            $html->setvar("{$blockUser}_age", $userInfo['age']);
            $html->setvar("{$blockUser}_city", l($userInfo['city']));
            $html->setvar("{$blockUser}_user_id", $user['user_id']);
            $html->setvar("{$blockUser}_profile_link", User::url($user['user_id']),$userInfo);
            $html->parse($blockUser);
        }
		parent::parseBlock($html);
	}
}

$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmpls = array(
    'main' => "{$dirTmpl}general_chat.html",
    'list_users' => "{$dirTmpl}_general_chat_list_users.html",
    'list_messages' => "{$dirTmpl}_general_chat_list_msg.html",
    'message' => "{$dirTmpl}_general_chat_msg.html",
);
if($isAjaxRequest) {
    $tmpls['main'] = "{$dirTmpl}_general_chat_ajax.html";
    if (get_param('cmd') == 'general_chat_send') {
        unset($tmpls['list_users']);
    }
}
$page = new CChatGeneral('', $tmpls);

if ($isAjaxRequest) {
    stopScript(getResponsePageAjaxAuth($page));
}
if (Common::isParseModule('profile_colum_narrow')){
    $columnNarrow = new CProfileNarowBox('profile_column_narrow', "{$dirTmpl}_profile_column_narrow.html");
    Page::addParts($page, $columnNarrow);
} else {
    Page::addParts($page);
}

include("./_include/core/main_close.php");