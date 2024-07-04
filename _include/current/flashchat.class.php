<?php

class Flashchat {

    static $updateUsersList = false;
    static $defaultRoom = null;
    static $currentRoom = 0;
    static $tmplMsgJoined = 'joined the room';
    static $tmplMsgExit = 'left the room';
    static $onlineTime = 60;

    static function getDefaultLimitMsg()
    {
        return Common::getOption('chat_history_messages');
    }

    static function getDefaultRoom()
    {
        if (self::$defaultRoom === null) {
            $sql = 'SELECT `id`
                      FROM `flashchat_rooms`
                     WHERE `status` = 1
                     ORDER BY `position` ASC
                     LIMIT 1';
            $default = DB::result($sql);
            self::$defaultRoom = $default;
        }
        return self::$defaultRoom;
    }

    static function setUpdateUsersList($updateUsersList)
    {
        self::$updateUsersList = $updateUsersList;
    }

    static function getUpdateUsersList()
    {
        return self::$updateUsersList;
    }

    static function parseRooms(&$html)
    {
        $sql = 'SELECT *
                  FROM `flashchat_rooms`
                 WHERE `status` = 1
              ORDER BY `position` ASC';
        $rooms = DB::rows($sql);
        $selected = self::getDefaultRoom();
        if (Common::isOptionTemplateSet('urban')) {
            $user = self::user();
            if ($user) {
                $selected = $user['room'];
            }
        }
        $pLang = 'flashchat.php';
        foreach ($rooms as $key => $room) {
            if ($room['id'] == $selected) {
                $html->setvar('room_name_selected', lp($room['name'], $pLang));
                $html->parse('room_selected', false);
            } else {
                $html->clean('room_selected');
            }
            $html->setvar('room_id', $room['id']);
            $html->setvar('room_name', lp($room['name'], $pLang));
            $html->parse('room');
        }
        if (count($rooms) > 1) {
            $html->parse('rooms');
        }
    }

    static function getCurrentRoom()
    {
        $currentRoom = self::$currentRoom;
        if (!$currentRoom) {
            $sql = 'SELECT `room`
                      FROM `flashchat_users`
                     WHERE `user_id` = ' . to_sql(guser('user_id'));
            $currentRoom =  DB::result($sql);
        }
        return $currentRoom;
    }

    static function getRoomName($room)
    {
        $roomName = '';
        if ($room) {
            $sql = 'SELECT `name`
                      FROM `flashchat_rooms`
                     WHERE `status` = 1 AND `id` = ' . to_sql($room);
            $name = DB::result($sql);
            if ($name) {
                $roomName = $name;
            }
        }
        return $roomName;
    }

    static function roomName($room)
    {
        $roomName = '';

        $room--;
        if($room < 0) {
            $room = 0;
        }

        $sql = 'SELECT name FROM flashchat_rooms
            WHERE status = 1
            ORDER BY `position` ASC
            LIMIT ' . to_sql($room, 'Number') . ',1';
        $roomInfo = DB::row($sql, DB_MAX_INDEX);
        if(isset($roomInfo['name'])) {
            $roomName = $roomInfo['name'];
        }
        return $roomName;
    }

    static function updateLastVisitUser($time = null)
	{
        global $g_user;
        if ($g_user) {
            if ($time === null) {
                $time = time();
            }
            $where = '`user_id` = ' . to_sql($g_user['user_id']);
            DB::update('flashchat_users', array('time_out' => $time), $where);
        }
    }

    static function updateVisit($userId = null)
    {
        if ($userId === null) {
            $userId = guser('user_id');
        }
        $now = time();

        $sessionUpdateLoginTime = 'flashchat_update_login_time';

        $updateLoginTimeout = 20;

        $updateLoginTime = get_session($sessionUpdateLoginTime);
        $lastUpdateTime = $now - intval($updateLoginTime);
        if($lastUpdateTime > $updateLoginTimeout) {
            $sql = 'UPDATE flashchat_users
					   SET time_out = ' . to_sql($now) . '
                     WHERE user_id = ' . to_sql($userId);
            DB::execute($sql);
            set_session($sessionUpdateLoginTime, $now);
        }
        return $now;
    }

    static function isUserBanned()
    {
        return self::isBanned(self::getUser());
    }

    static function isBanned($user)
    {
        $banned = false;
        if($user['status'] > 0) {
            $deltaTime = time() - $user['time_out'];
            if ($deltaTime > $user['status']) {
                $sql = 'UPDATE `flashchat_users`
					       SET `status` = 0
                         WHERE `user_id` = ' . to_sql(guser('user_id'));
                DB::execute($sql);
            } else {
                $banned = true;
            }
        }

        return $banned;
    }

    static function getUser($uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }
        $sql = 'SELECT *
                  FROM `flashchat_users`
                 WHERE `user_id` = ' . to_sql($uid);
        return DB::row($sql);
    }

    static function user()
    {
        $sql = 'SELECT *
			      FROM `flashchat_users`
                 WHERE `user_id` = ' . to_sql(guser('user_id'));
        return DB::row($sql);
    }

    static function login($isSetRoom = false)
    {
        $user = self::user();
        $room = self::getDefaultRoom();
        $isUrban = Common::isOptionTemplateSet('urban');
        if ($user) {
            if(self::isBanned($user)) {
                if ($isUrban) {
                    set_session('error_accessing_user', l('you_have_been_banned_by_the_admin_please_try_later'));
                }
                redirect(Common::getHomePage());
            }
            $time = self::updateVisit();
            if ($isUrban) {
                $room = $user['room'];
			} elseif ($isSetRoom) {
				$row = array('room' => $room);
				DB::update('flashchat_users', $row, '`user_id` = ' . guser('user_id'));
			}
        } else {
            $time = self::joined($room);
        }
        self::$currentRoom = $room;
        if (!get_param('message')) {
            $isSend = true;
            /*$systemMsg = DB::select('flashchat_messages', '`room` = ' . to_sql($room), 'id DESC', 1);
            if ($systemMsg && isset($systemMsg[0])) {
                $systemMsg = $systemMsg[0];
                if ($systemMsg['status'] == 'system'
                        && $systemMsg['user_id'] = guid()
                        && mb_strpos($systemMsg['msgtext'], self::$tmplMsgJoined) !== false) {
                    $isSend = false;
                }
            }*/
            if ($isSend) {
                $message = to_sql(self::$tmplMsgJoined . ' ' . Flashchat::getRoomName($room), 'Plain');
                if (!self::checkLastMsgSystemMy($message)) {
                    self::send($message, $room, 'system', $time);
                }
            }
        }
    }

    static function checkLastMsgSystemMy($msg)
    {
        global $g_user;

        $sql = 'SELECT * FROM `flashchat_messages` ORDER BY id DESC LIMIT 1';
        $lastMsg = DB::row($sql);
        if ($lastMsg) {
            return $lastMsg['user_id'] == $g_user['user_id'] && $lastMsg['status'] == 'system'
                   && $lastMsg['msgtext'] == $msg;
        }
        return false;
    }

    static function joined($room = null, $user = null)
    {
        global $g_user;
        if ($room === null) {
            $room = self::getDefaultRoom();
        }
        if ($user === null) {
            $uid = $g_user['user_id'];
            $userName = $g_user['name'];
            $userGender = $g_user['gender'];
        } else {
            $uid = $user['user_id'];
            $userName = $user['name'];
            $userGender = $user['gender'];
        }

        $time = time();
        $row = array('user_id' => $uid,
                     'login' => $userName,
                     'mess_color' => '',
                     'time_out' => $time,
                     'status' => 0,
                     'sys_color' => '',
                     'room' => $room,
                     'gender' => mb_strtolower($userGender, 'UTF-8'));
        DB::insert('flashchat_users', $row);
        return $time;
    }

    static function logout()
	{
        self::updateLastVisitUser(time() - self::$onlineTime);
        set_cookie('general_chat_logout', '', -1, true, false);

        $user = self::user();
        if ($user) {
            $currentRoom = $user['room'];
            $message = to_sql(self::$tmplMsgExit . ' ' . Flashchat::getRoomName($currentRoom), 'Plain');
            if (!self::checkLastMsgSystemMy($message)) {
                self::send($message, $currentRoom, 'system');
            }
        }
    }

    static function getNumberUsersVisitors()
	{
        $sql = 'SELECT COUNT(*)
                  FROM `flashchat_users`
                 WHERE `time_out` > ' . to_sql(time() - self::$onlineTime);
        return DB::result($sql, 0, 0, true);
    }

    static function parseMsg($message, $status = '', $room = 0, $uid = 0)
    {
        if ($status == 'system') {
            $message = Flashchat::translateSystemMsg($message, $room, $uid);
        } else {
            $message = Common::parseLinksTag(to_html($message, true), 'a', '&lt;', 'parseLinksSmile');
        }
        return $message;
    }

    static function send($msg, $room, $status = '', $time = null, $send = null, $uid = null, $userName = null, $isDemoBot = false)
    {
        global $g_user;

        if ($uid === null) {
            $uid = $g_user['user_id'];
        }
        if ($userName === null) {
            $userName = $g_user['name'];
        }
        if ($status != 'system') {
            if (!$uid) {
                return 'logout';
            } elseif (self::isUserBanned()) {
                return 'system_user_banned';
            }
        }
        if ($time === null) {
            $time = time();
        }
        if ($send === null) {
            $send = $time;
        }

        if ($status == 'system') {
            //$msg = Flashchat::translateSystemMessage($msg);
        } else {
            $to_user = $uid;
            if(!$isDemoBot) {
                $msg = str_replace("<", "&lt;", $msg);
                $msg = censured($msg);
            }
            /*$censuredFile = dirname(__FILE__) . '/../../_server/im_new/feature/censured.php';
            if (file_exists($censuredFile)) include($censuredFile);
            if ($censured){
                return 'system_user_banned';
            }*/
        }

        $row = array('time' => $time,
                     'status' => $status,
                     'msgtext' => $msg,
                     'user' => $userName,
                     'room' => $room,
                     'user_id' => $uid,
                     'send' => $send);
        DB::insert('flashchat_messages', $row);

        $lastMid = DB::insert_id();

        if(IS_DEMO && $status != 'system') {
            Demo::addFlashchatMessage($row);
        }

        return $lastMid;
    }

    static function changeRoom($room, $oldRoom)
    {
        $message = to_sql(self::$tmplMsgExit . ' ' . Flashchat::getRoomName($oldRoom), 'Plain');
        if (!self::checkLastMsgSystemMy($message)) {
            self::send($message, $oldRoom, 'system');
        }

        DB::update('flashchat_users', array('room' => $room), '`user_id` = ' . to_sql(guid()));
        self::login();
    }

    //Пока так чтоб совместить со старыми
    static function translateSystemMsg($message, $room, $uid)
    {
        $pLang = 'flashchat.php';
        $name = '';
        $parts = array(self::$tmplMsgJoined, self::$tmplMsgExit);
        foreach ($parts as $part) {
            if (mb_strpos($message, $part, 0, 'UTF-8') !== false) {
                $message = '';
                // if (Common::isOptionActive('add_name_system_msg_general_chat', 'template_options')) {
                    $message = '<a href="' . User::url($uid) . '">' . User::getInfoBasic($uid, 'name') . '</a> ';
                // }
                $message .= lp($part, $pLang) . ' ' . lp(self::getRoomName($room), $pLang);
                break;
            }
        }
        return $message;
    }

    static function translateSystemMessage($message)
    {
        static $roomsReplace = array();

        if(!$roomsReplace) {
            $sql = 'SELECT * FROM flashchat_rooms';
            $roomsList = DB::rows($sql, DB_MAX_INDEX);

            $pLang = 'flashchat.php';
            $lJoinedTheRoom = lp('joined the room', $pLang);
            $lLeftTheRoom = lp('left the room', $pLang);

            foreach($roomsList as $roomInfo) {
                $roomName = lp($roomInfo['name'], $pLang);
                $roomsReplace['joined the room ' . $roomInfo['name']] = $lJoinedTheRoom . ' ' . $roomName;
                $roomsReplaceLeft['left the room ' . $roomInfo['name']] = $lLeftTheRoom . ' ' . $roomName;
            }
        }

        if(isset($roomsReplace[$message])) {
            $message = $roomsReplace[$message];
            self::setUpdateUsersList(true);
        }
        if(isset($roomsReplaceLeft[$message])) {
            $message = $roomsReplaceLeft[$message];
            self::setUpdateUsersList(true);
        }

        return $message;
    }

    static function clearHistory()
    {
        $rooms = DB::field('flashchat_rooms', 'id');
        foreach ($rooms as $room) {
            $where = '`room` = ' . to_sql($room);
            $count = DB::count('flashchat_messages', $where);
            if ($count > self::getDefaultLimitMsg()) {
                DB::delete('flashchat_messages', $where, 'id ASC', $count - self::getDefaultLimitMsg());
            }
        }
    }

    static function sendMsgByDemoUsers()
	{
        global $g_user;

        $demoMsgs = getDemoMessages(null, '', 'chat');
        $demoUsers = array(1 => array(440, 451, 438),
                           2 => array(447, 432, 442),
                           3 => array(456, 12, 450),
                           4 => array(446, 443, 458),
        );
        $demoMsgCount = count($demoMsgs);
        foreach ($demoUsers as $room => $users) {
            $uid = $users[array_rand($users)];
            $user = User::getInfoBasic($uid);
            $sql = 'SELECT *
                      FROM `flashchat_messages`
                     WHERE `room` = ' . to_sql($room, 'Number') . ' AND `status` != "system"
                     ORDER BY id DESC';
            /*$msgs = DB::rows($sql);
            $msgsCount = count($msgs);
            $num = 0;
            if ($msgsCount){
                $num = floor($msgsCount/$demoMsgCount);
            }
            $sql .= ' LIMIT ' . to_sql($num * $demoMsgCount, 'Number') . ', ' . to_sql($demoMsgCount, 'Number');*/
            $sql .= ' LIMIT ' . to_sql($demoMsgCount - 1, 'Number');
            $msgs = DB::rows($sql);
            $msg = getDemoMsg($msgs, 'msgtext', $demoMsgs);
            if (!self::getUser($uid)) {
                self::joined($room, $user);
            }
            $g_user['user_id'] = $uid;
            self::updateLastVisitUser();
            self::send($msg, $room, '', null, null, $uid, $user['name'], true);
        }
    }

}