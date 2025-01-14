<?php
class CIm extends CHtmlBlock
{
    static $demoWhere = '';
    static $demoInsert = '';
    static $isMobileGeneralChatUpdate = false;
    static $isMobileOneChat = false;
    static $isPageListChats = false;
    static $isNotificationParsed = false;
    static $isReadMsg = false;
    static $usersListMobileGeneralChat = null;
    static $usersListMobileGeneralChatOrder = array();
    static $countMessagesFromUsers = null;
    static public $msgTranslated = '';


    static function getGroupId()
    {
        $groupId = 0;
        if (Common::isOptionActiveTemplate('groups_social_enabled')){
            $groupId = get_param_int('group_im_id');
            // $groupId = get_param_int('group_id');
        }

        return $groupId;
    }

    static function getTable($msg = false, $groupId = null)
    {
        $tableImOpen = 'im_open';
        $tableImMsg = 'im_msg';

        $table = $msg ? $tableImMsg : $tableImOpen;

        return $table;
    }

    static function getAddInsert($rev = false, $fromGroupId = null, $toGroupId = null)
    {
        $groupId = self::getGroupId();
        $add = '';
        if ($groupId) {
            $fromGroupId = get_param_int('from_group_id');
            $toGroupId = get_param_int('to_group_id');

            if ($rev) {
                $add = ', `group_id` = ' . to_sql($groupId) . '
                        , `from_group_id` = ' . to_sql($toGroupId) . '
                        , `to_group_id` = ' . to_sql($fromGroupId);
            } else {
                $add = ', `group_id` = ' . to_sql($groupId) . '
                        , `from_group_id` = ' . to_sql($fromGroupId) . '
                        , `to_group_id` = ' . to_sql($toGroupId);
            }
        }

        return $add;
    }

    static function getWhereGroupOr($rev = false, $groupId = null, $fromGroupId = null, $toGroupId = null, $table = '')
    {
        if ($groupId === null) {
            $groupId = self::getGroupId();
        }
        if ($groupId) {
            if ($fromGroupId === null) {
                $fromGroupId = get_param_int('from_group_id');
            }
            if ($toGroupId === null) {
                $toGroupId = get_param_int('to_group_id');
            }
            if ($rev) {
                $where = " AND ({$table}from_group_id = " . to_sql($toGroupId) . " OR {$table}to_group_id = " . to_sql($fromGroupId) . ")";
            } else {
                $where = " AND ({$table}from_group_id = " . to_sql($fromGroupId) . " OR {$table}to_group_id = " . to_sql($toGroupId) . ")";
            }
        } else {
            $where = " AND ({$table}from_group_id = 0 AND {$table}to_group_id = 0)";
        }

        return $where;
    }

    static function getWhereGroup($rev = false, $groupId = null, $fromGroupId = null, $toGroupId = null, $table = '')
    {
        if ($groupId === null) {
            $groupId = self::getGroupId();
        }
        if ($groupId) {
            if ($fromGroupId === null) {
                $fromGroupId = get_param_int('from_group_id');
            }
            if ($toGroupId === null) {
                $toGroupId = get_param_int('to_group_id');
            }
            if ($rev) {
                $where = " AND ({$table}from_group_id = " . to_sql($toGroupId) . " AND {$table}to_group_id = " . to_sql($fromGroupId) . ")";
            } else {
                $where = " AND ({$table}from_group_id = " . to_sql($fromGroupId) . " AND {$table}to_group_id = " . to_sql($toGroupId) . ")";
            }
        } else {
            $where = " AND ({$table}from_group_id = 0 AND {$table}to_group_id = 0)";
        }

        return $where;
    }

    static function getWhereGroupMsg($fromGroupId = 0, $toGroupId = 0, $rev = false)
    {

        if (!Common::isOptionActiveTemplate('groups_social_enabled')){
            $fromGroupId = 0;
            $toGroupId = 0;
        }

        if ($rev) {
            $where = ' AND `from_group_id` = ' .  to_sql($toGroupId) .
                     ' AND `to_group_id` = ' .  to_sql($fromGroupId);
        } else {
            $where = ' AND `from_group_id` = ' .  to_sql($fromGroupId) .
                     ' AND `to_group_id` = ' .  to_sql($toGroupId);
        }

        return $where;
    }

    static function getWhereNoGroupIm($table = '')
    {
        $where = '';
        if (!Common::isOptionActiveTemplate('groups_social_enabled')) {
            $where =  " AND ({$table}from_group_id = 0 AND {$table}to_group_id = 0) ";
        }
        return $where;
    }

    /* SET */
    static function setCurrentData($table = '', $set = false)
    {
        if (defined('IS_DEMO') && IS_DEMO) {
            if (empty(self::$demoWhere) || empty(self::$demoInsert) || $set) {
                self::$demoWhere = " AND {$table}session = " . to_sql(addslashes(session_id())) . ' ';
                self::$demoInsert = ", {$table}session = " . to_sql(addslashes(session_id())) . ", {$table}session_date = NOW()";
            }
        }else{
            self::$demoWhere = '';
        }
    }

    static function getWhereNoSysytem($table = '')
    {
        $where = '';
        if (Common::isOptionActiveTemplate('im_no_system_msg')) {
            /* system_type
             * 1 - welcoming_message
             * 2 - image upload
             */
            $where = " AND ({$table}`system` != 1 OR ({$table}`system` = 1 AND {$table}`system_type` IN(1,2))) ";
            //$where = ' AND (system != 1 OR system_type = 1)';
        }

        return $where;
    }

    static function checkTimeStatus($time)
    {

    }

    static function setWriting($status = null)
    {
        if ($status === null) {
            $status = self::jsonDecodeParamArray('status_writing');
        }
        if ($status && is_array($status)) {
            self::setCurrentData();
            foreach ($status as $user => $time) {
                self::setLastWriting($user, $time);
            }
        }
    }

    static function setWritingMobileOneChat()
    {
        global $g_user;

        $status = get_param_int('status_writing');
        $userTo = get_param('user_to');
        if ($status && $userTo) {
            self::setLastWriting($userTo, $status);
        }
    }

    static function checkTimeLastWriting($time, $currentTime = null)
    {
        if ($currentTime == null) {
            $currentTime = time();
        }

        $time = intval($time);
        $d = 120;//If time is more than 2 minutes
        $d1 = abs($currentTime - $time);
        if($time && $d1 && $d1 > $d){
            $time = 0;
        }

        return $time;
    }

    static function statusLastWriting($time, $currentTime = null, $timeoutSecServer = null)
    {
        if ($currentTime == null) {
            $currentTime = time();
        }

        if ($timeoutSecServer === null) {
            $timeoutSecServer = get_param('timeout_server');
        }

        $time = self::checkTimeLastWriting($time, $currentTime);

        if (!$time) {
            return 0;
        }

        return (($currentTime - $time) <= $timeoutSecServer) ? 1 : 0;
    }

    static function setLastWriting($userId, $time)
    {
        $time = self::checkTimeLastWriting($time);
        self::setCurrentData();
        $where = self::getWhereMessagesFrom($userId) . self::$demoWhere;
        DB::update(self::getTable(), array('last_writing' => $time), $where);
    }

    static function getWritingUser($timeoutSecServer = null, $userTo = null)
    {
        if ($timeoutSecServer === null) {
            $timeoutSecServer = get_param('timeout_server');
        }
        self::setCurrentData();

        $fromUserWriting = array();
        $writing = array();
        $currentTime = time();

        $userTo = get_param_int('user_to', get_param_int('user_current'));
        if ($userTo) {
            $where = self::getWhereMessagesTo($userTo) . ' AND `last_writing` != 0 ' . self::$demoWhere;
            $user = DB::one(self::getTable(), $where);
            if ($user) {
                $writing[$user['from_user']] = self::statusLastWriting($user['last_writing'], $currentTime, $timeoutSecServer);
            }
        } else {
            $param = 'users_list';
            if (get_param('users_list_open_im')) {
                $param = 'users_list_open_im';
            }
            $usersList = self::jsonDecodeParamArray($param);

            if ($usersList) {
                $where = '`to_user` = ' . to_sql(guid())
                  . ' AND `last_writing` != 0 '
                  . ' AND `from_user` IN(' . self::getSqlImplodeKeys($usersList)  . ')'
                  . self::$demoWhere;
                $fromUserWriting = DB::select(self::getTable(), $where);
                if ($fromUserWriting) {
                    foreach ($fromUserWriting as $user) {
                        $writing[$user['from_user']] = self::statusLastWriting($user['last_writing'], $currentTime, $timeoutSecServer);
                    }
                }
            }
        }

        return $writing;
    }

    static function setLastViewedIm($currentUser = null)
    {
        global $g_user;

        if ($g_user['user_id']) {
            if ($currentUser === null) {
                $currentUser = get_param('user_current');
            }
            if (!empty($currentUser)) {
                self::setCurrentData();
                $sql = 'UPDATE `' . self::getTable() . '` SET `z` = ' . time() .
                       ' WHERE `to_user` = ' . to_sql($currentUser, 'Number') . "
                           AND `from_user` = " . to_sql($g_user['user_id'], 'Number') .
                        self::getWhereGroup() .
                        self::$demoWhere;
                DB::execute($sql);
            }
        }
    }

    static function setVisibleOpenIm($uid = null, $visible = 'Y', $groupId = null)
    {
        global $g_user;
        $response = false;
        if ($g_user['user_id']) {
            if ($uid === null) {
                $uid = get_param('user_id');
            }
            if ($groupId === null) {
                $groupId = self::getGroupId();
            }
            $paramVisible = get_param('visible');
            if ($paramVisible && in_array($paramVisible, array('Y', 'N', 'C'))) {
                $visible = $paramVisible;
            }
            if ($uid) {
                self::setCurrentData();
                $sql = 'UPDATE `' . self::getTable() .
                        '` SET `im_open_visible` = ' . to_sql($visible) .
                       ' WHERE `to_user` = ' . to_sql($uid, 'Number') . '
                           AND `to_group_id` = ' . to_sql($groupId) . '
                           AND `from_user` = ' . to_sql($g_user['user_id'], 'Number') .
                        self::$demoWhere;
                DB::execute($sql);
                $response = true;
            }
        }
        return $response;
    }

    static function getWhereAudioMsgRead()
    {
        if (!Common::isOptionActive('im_audio_messages')) {
            return '';
        }
        $where = ' AND `audio_message_id` = 0 ';
        return $where;
    }

    static function setMessageAsReadOneMsg($mid, $userFrom, $checkAudio = true)
    {
        $isMode = get_param('is_mode_fb');
        if ($isMode != 'false') return true;

        $isGroupsSocial = Common::isOptionActiveTemplate('groups_social_enabled');
        if ($mid) {
            $where = '`id` = ' . to_sql($mid) . ($checkAudio ? self::getWhereAudioMsgRead() : '');
            $sql = 'SELECT * FROM `' . self::getTable(true) . '`
                     WHERE ' . $where;
            $msgInfo = DB::row($sql);
            if ($msgInfo) {
                DB::update(self::getTable(true), array('is_new' => 0), $where);
                if ($isGroupsSocial) {
                    $fromGroupId = $msgInfo['from_group_id'];
                    $toGroupId = $msgInfo['to_group_id'];
                    $groupId = $fromGroupId ? $fromGroupId : $toGroupId;
                    $count = self::getCountNewMessagesWithGroup($userFrom, $groupId, $fromGroupId, $toGroupId);

                    $whereSql = self::getWhereGroup(true, $groupId, $fromGroupId, $toGroupId);
                } else {
                    $whereSql = '';
                    $count = self::getCountNewMessages($userFrom);
                }

                if(!$count){
                    $where = '`from_user` = ' . to_sql(guid()) .
                             ' AND `to_user`= ' . to_sql($userFrom) .
                             $whereSql;//For demo . self::$demoWhere
                    DB::update(self::getTable(), array('is_new_msg' => 0), $where);
                }
            }
        }
        return true;
    }

    static function setMessageAsRead($userFrom = null, $getCount = true, $groupId = null, $fromGroupId = null, $toGroupId = null)
    {
        $isMode = get_param('is_mode_fb', 'false');

        if ($isMode != 'false') return true;
        $guid = guid();
        if ($userFrom === null) {
            $userFrom = get_param('user_current', get_param('user_id'));
        }
        if (!$guid || !$userFrom) {
            return true;
        }

        $where = '`from_user` = ' . to_sql($guid) .
                 ' AND `to_user`= ' . to_sql($userFrom) .
                 self::getWhereGroup(false, $groupId, $fromGroupId, $toGroupId);
                 //For demo . self::$demoWhere
        $sql = 'SELECT * FROM `' . self::getTable() . '`
                 WHERE ' . $where . self::$demoWhere;

        $row = DB::row($sql, DB_MAX_INDEX);
        if($row && $row['is_new_msg']) {

            DB::update(self::getTable(), array('is_new_msg' => 0), $where);

            $where = '`from_user` = ' . to_sql($userFrom) .
                     ' AND `to_user`= ' . to_sql($guid) .
                     ' AND `is_new` = 1' . self::getWhereAudioMsgRead() .
                     self::getWhereGroup(true, $groupId, $fromGroupId, $toGroupId);
            DB::update(self::getTable(true), array('is_new' => 0), $where);
        }

        return $getCount ? CIm::getCountNewMessages() : '';
    }

    static function setMessageMobileAsRead($isJson = false)//??? NOT USED
    {
        global $g_user;

        if ($g_user['user_id']) {
            $isMode = get_param('is_mode_fb');
            if ($isJson) {//Impact - messages.php - one chat
                $display = get_param('display');
                $cache = self::jsonDecodeParamArray('cache_messages');
                if (!$cache) {
                    return false;
                }
                if ($display == 'one_chat') {
                    $cache = array_shift($cache);
                }
            } else {//Urban mobile - messages.php
                $cache = get_param_array('cache_messages');
            }

            if ($isMode == 'false' && !empty($cache)) {
                $where = '`id` IN(';
                $prf = '';
                foreach ($cache as $users => $item) {
                    $key = $display == 'one_chat' ? $users : key($item);
                    $where .= $prf . $key;
                    $prf = ',';
                }
                $where .= ')';
                $where .= self::getWhereAudioMsgRead();
                DB::update(self::getTable(true), array('is_new' => 0), $where);
            }
            return true;
        } else {
            return false;
        }
    }

    static function setStatusUsers(&$html, $usersList = null, $alwaysCheck = false)
    {
        if ($usersList === null) {
            $usersList = self::jsonDecodeParamArray('users_list');
        }

        if (!$usersList) return;
        $usersStatus = array();

        /*
        foreach ($usersList as $userId => $online) {
            $userOnline = intval(User::isOnline($userId));
            if ($userOnline != $online || $alwaysCheck) {
                $usersStatus[$userId] = $userOnline;
            }
        }*/

        $rows = DB::select('user', 'user_id IN (' . self::getSqlImplodeKeys($usersList) . ')', '', '', 'user_id, last_visit');

        if($rows) {
            foreach($rows as $row) {
                $userId = $row['user_id'];
                $userOnline = intval(User::isOnline($userId, $row));
                if ($userOnline != $usersList[$userId] || $alwaysCheck) {
                    $usersStatus[$userId] = $userOnline;
                }
            }
        }

        if ($usersStatus) {
            $html->setvar('update_users_status_check', intval($alwaysCheck));
            $html->setvar('update_users_status', json_encode($usersStatus));
            $html->parse('update_users_status', false);
        }
    }

    static function clearMessageGalleryImage($msg)
    {
        $img = grabs($msg, '{img_upload:', '}');
        if (isset($img[0])) {
            $sql = "SELECT * FROM gallery_images WHERE id = " . to_sql($img[0]);
            $image = DB::row($sql, DB_MAX_INDEX);
            if ($image) {
                Gallery::imageDelete($img[0], $image['user_id'], false);
            }
        }
    }

    static function clearHistoryMessages($userId = null)
    {
        global $g_user;

        $responseData = false;

        if ($userId === null) {
            $userId = get_param('user_id', 0);
        }
        if ($g_user['user_id'] && $userId) {
            self::setCurrentData();

            DB::update(self::getTable(true), array('from_user_deleted' => 1), self::getWhereMessagesFrom($userId) . self::getWhereGroup());
            DB::update(self::getTable(true), array('to_user_deleted' => 1), self::getWhereMessagesTo($userId) . self::getWhereGroup(true));

            if (!User::isFriend($userId, $g_user['user_id'])) {
                $mid = self::getMidRequestPrivateAccess($userId);
                if ($mid) {
                    self::sendPrivateDeclined($userId, $mid);
                }
            }

            $toDelMsg = DB::select('im_msg', self::getWhereMessagesFrom($userId) . ' AND `to_user_deleted` = 1 AND `system` = 1');
            foreach ($toDelMsg as $key => $msg ){
                self::clearMessageGalleryImage($msg['msg']);
            }
            $toDelMsg = DB::select('im_msg', self::getWhereMessagesFrom($userId) . ' AND `to_user_deleted` = 1 AND `system` != 1');
            foreach ($toDelMsg as $key => $msg ){
                OutsideImages::on_delete($msg['msg'], OutsideImages::$sizesIm);
            }


            $fromDelMsg = DB::select('im_msg', self::getWhereMessagesTo($userId) . ' AND `from_user_deleted` = 1 AND `system` = 1');
            foreach ($fromDelMsg as $key => $msg ){
                self::clearMessageGalleryImage($msg['msg']);
            }
            $fromDelMsg = DB::select('im_msg', self::getWhereMessagesTo($userId) . ' AND `from_user_deleted` = 1 AND `system` != 1');
            foreach ($fromDelMsg as $key => $msg ){
                OutsideImages::on_delete($msg['msg'], OutsideImages::$sizesIm);
            }

            // remove audio files
            $wheres = array(
                self::getWhereMessagesFrom($userId) . ' AND `to_user_deleted` = 1 AND `audio_message_id` > 0',
                self::getWhereMessagesTo($userId) . ' AND `from_user_deleted` = 1 AND `audio_message_id` > 0',
            );

            foreach($wheres as $where) {
                $rows = DB::select(self::getTable(true), $where);
                if($rows) {
                    foreach($rows as $row) {
                        ImAudioMessage::delete($row['audio_message_id'], $row['from_user']);
                    }
                }
            }

            DB::delete(self::getTable(true), self::getWhereMessagesFrom($userId)  . self::getWhereGroup() . ' AND `to_user_deleted` = 1');
            DB::delete(self::getTable(true), self::getWhereMessagesTo($userId) . self::getWhereGroup(true) . ' AND `from_user_deleted` = 1');
            DB::delete(self::getTable(), self::getWhereMessagesFrom($userId) . self::getWhereGroup(). self::$demoWhere);
            $responseData = true;
        }
        return $responseData;
    }

    static function deleteMessages()
    {
        $guid = guid();
        $mid = get_param_int('mid');
        $fromMe = get_param_int('from_me');

        if ($mid && $guid) {
            $where = '`id` = ' . to_sql($mid);
            $msgInfo = DB::one(self::getTable(true), $where);
            if ($msgInfo) {
                $data = array();
                $isDeleteMsg = false;
                if ($msgInfo['from_user'] == $guid) {
                    $data['from_user_deleted'] = 1;
                    if ($fromMe) {
                        if ($msgInfo['to_user_deleted']) {
                            $isDeleteMsg = true;
                        }
                    } else {
                        $isDeleteMsg = true;
                    }
                } else {
                    $data['to_user_deleted'] = 1;
                    if ($msgInfo['from_user_deleted']) {
                        $isDeleteMsg = true;
                    }
                }
                if ($isDeleteMsg) {
                    self::clearMessageGalleryImage($msgInfo['msg']);
                    OutsideImages::on_delete($msgInfo['msg'], OutsideImages::$sizesIm);
                    ImAudioMessage::delete($msgInfo['audio_message_id'], $msgInfo['from_user']);

                    DB::delete(self::getTable(true), $where);
                    return true;
                }
                if ($fromMe) {
                    DB::update(self::getTable(true), $data, $where);
                }
            }
        }

        return true;
    }
    /* SET */
    /* GET */
    static function getDateIm($date)
    {
        return Common::dateFormat($date, 'im_datatime');
    }

    static function lastId($return = true)
    {
        $sql = 'SELECT MAX(`id`) FROM ' . self::getTable(true);

        $lastId = intval(DB::result($sql));

        set_session('im_id', $lastId);
        if($return) {
            return get_session('im_id');
        }

        /*
        DB::query("SHOW TABLE STATUS LIKE '" . self::getTable(true) . "'");
        $line = DB::fetch_row();
        if (intval($line['Auto_increment']) == 0)
            set_session('im_id', 0);
        else
            set_session('im_id', intval($line['Auto_increment']) - 1);

        return get_session('im_id');
         */
    }

    static function getCountAllMsgIm()
    {
        global $g_user;

        $where = self::getWhereNoSysytem();

        $sql = 'SELECT
                (SELECT COUNT(*) FROM `' . self::getTable(true) . '`
                  WHERE `from_user` = ' . to_sql($g_user['user_id'], 'Number') . '
                    AND `from_user_deleted` = 0 ' . $where . ' LIMIT 1)
                +
                (SELECT COUNT(*) FROM `' . self::getTable(true) . '`
                  WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                  ' AND `to_user_deleted` = 0 ' . $where . ' LIMIT 1)
                ';

        return DB::result($sql);
    }

    static function getCountMsgIm($userId)
    {
        global $g_user;

        $where = self::getWhereNoSysytem();
        if (Common::isOptionActive('gifts_disabled', 'template_options')) {
            //$where = " AND `msg` NOT LIKE '{gift:%'";
        }

        $sql = 'SELECT
                (SELECT COUNT(*) FROM `' . self::getTable(true) . '`
                  WHERE `to_user` = ' . to_sql($userId, 'Number') .
                  ' AND `from_user` = ' . to_sql($g_user['user_id'], 'Number') . '
                    AND `from_user_deleted` = 0 ' . $where . ')
                +
                (SELECT COUNT(*) FROM `' . self::getTable(true) . '`
                  WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                  ' AND `from_user` = ' . to_sql($userId, 'Number') . '
                    AND `to_user_deleted` = 0 ' . $where . ')
                ';

        return DB::result($sql);
    }

    static function getDataNewMessagesLast($limit = '', $order = 'id ASC')
    {
        global $g_user;
        $msg = array();
        if ($g_user) {
            $lastNewMsgId = intval(get_param('last_new_msg_id'));
            $where = '`is_new` = 1
                  AND `to_user` = ' . to_sql($g_user['user_id']) .
                ' AND `to_user_deleted` = 0 ' . self::getWhereNoSysytem() .  '
                  AND `id` > ' . to_sql($lastNewMsgId);
            $msg = DB::select(self::getTable(true), $where, $order, $limit);
        }

        return $msg;
    }

    static function getDataJsNewMessages()
    {
        global $g_user;

        $js = '';
        $urlFiles = Common::getOption('url_files', 'path');
        $allMsgs = array();
        $msgs = self::getDataNewMessagesLast();
        if ($msgs) {
            $i = 0;
            foreach ($msgs as $msg) {

                $msg=self::switchOnTranslate($msg);

                $allMsgs[$i]['id'] = $msg['id'];
                $vars = array('url' => User::url($msg['from_user']),
                              'name' => User::nameOneLetterFull($msg['name']));
                $allMsgs[$i]['title'] = Common::lSetLink('name_sent_you_a_message', $vars);

                $vars['user_id'] = $msg['from_user'];
                $photo = $urlFiles . User::getPhotoDefault($msg['from_user'], 'r');
                $allMsgs[$i]['photo'] = $photo;
                $vars['text'] = hard_trim($msg['msg'], 55);

                $system = $msg['system'];
                if (stristr($msg['msg'], '{img:') !== false || stristr($msg['msg'], '{youtube:') !== false) {
                    $system = 1;
                }

                if ($system) {
                    $types = array('private_photo_request_approved',
                                   'private_photo_request_declined',
                                   'private_photo_request',
                                   '{gift:',
                                   'welcoming_message',
                                   '{img_upload:',
                                   '{img:',
                                   '{youtube:');
                    foreach ($types as $type) {
                        if(stristr($msg['msg'], $type) !== false) {
                            if ($type == '{gift:') {
                                $vars['text'] = l('sent_you_a_gift');
                            } else if ($type == 'private_photo_request') {
                                $vars['text'] = l('private_photo_report');
                            } else if ($type == 'private_photo_request_approved') {
                                $vars['text'] = l('private_photo_request_approved_notif');
                            } else if ($type == 'welcoming_message') {
                                $emailAuto = Common::sendAutomail(Common::getOption('lang_loaded', 'main'), '','welcoming_message', array('name' => guser('name')), false, DB_MAX_INDEX, true);
                                $vars['text'] = hard_trim($emailAuto['text'], 55);
                            } else if ($type == '{img_upload:') {
                                $vars['text'] = l('image_preview');
                            } else if ($type == '{img:' || $type == '{youtube:') {
                                if ($type == '{img:') {
                                    $images = grabs($msg['msg'], '{img:', '}');
                                    foreach ($images as $id){
                                        $msg['msg'] = Common::getTextTagsToBr($msg['msg'], '{img:' . $id . '}', l('image_preview'));
                                    }
                                }
                                $vids = grabs($msg['msg'], '{youtube:', '}');
                                foreach ($vids as $id){
                                    $urlVideo = 'https://www.youtube.com/watch?v=' . $id;
                                    $msg['msg'] = Common::getTextTagsToBr($msg['msg'], '{youtube:' . $id . '}', $urlVideo);
                                }
                                $vars['text'] = $msg['msg'];
                            } else {
                                $vars['text'] = l($type);
                            }
                            break;
                        }
                    }
                }
                $allMsgs[$i]['text'] = Common::replaceByVars(l('sent_you_new_message'), $vars);
                $i++;
            }
        }
        return defined('JSON_UNESCAPED_UNICODE') ? json_encode($allMsgs, JSON_UNESCAPED_UNICODE) : json_encode($allMsgs);
    }


    static function getCountNewMessagesWithGroup($fromUser, $groupId, $fromGroupId, $toGroupId)
    {
        $countMsgNew = 0;

        $toUser = guid();

        if ($toUser) {
            $where = 'WHERE `is_new` = 1
                        AND `to_user` = ' . to_sql($toUser, 'Number') .
                      ' AND `to_user_deleted` = 0
                        AND `from_user` = ' . to_sql($fromUser, 'Number');

            $where .= self::getWhereNoSysytem();

            $where .= self::getWhereGroup(false, $groupId, $fromGroupId, $toGroupId);

            $sql = 'SELECT COUNT(*) FROM `' . self::getTable(true) . '` ' . $where;
            $countMsgNew = DB::result($sql, 0, 2);

        }

        return $countMsgNew;
    }

    static function getCountNewMessages($fromUser = null, $exceptUser = null, $toUser = null)
    {
        $countMsgNew = 0;

        if(!$toUser) {
            $toUser = guid();
        }

        if ($toUser) {
            $where = 'WHERE `is_new` = 1
                        AND `to_user` = ' . to_sql($toUser, 'Number') .
                      ' AND `to_user_deleted` = 0';
            if (!empty($fromUser)) {
                $where .= ' AND `from_user` = ' . to_sql($fromUser, 'Number');
            }elseif (!empty($exceptUser)) {
                $where .= ' AND `from_user` != ' . to_sql($exceptUser, 'Number');
            }

            //member to group owner message ignore
            $where .= " AND !(`from_group_id` = 0 AND `group_id` != 0 AND `to_group_id` != 0) ";

            $where .= self::getWhereNoSysytem();

            if($fromUser && $exceptUser === null && self::$countMessagesFromUsers !== null) {
                $countMsgNew = isset(self::$countMessagesFromUsers[$fromUser]) ? self::$countMessagesFromUsers[$fromUser] : 0;
            } else {
                $sql = 'SELECT COUNT(*) FROM `' . self::getTable(true) . '` ' . $where;
                $countMsgNew = DB::result($sql, 0, 2);
            }
        }

        return $countMsgNew;
    }


    static function getLastNewMessageInfo()
    {
        global $g_user;

        $optionTmplName = Common::getTmplName();
        $info = array('uid' => 0, 'message' => '');
        if ($g_user && (Common::isApp() || $optionTmplName == 'edge')) {
            $where = 'WHERE `is_new` = 1
                        AND `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                      ' AND `to_user_deleted` = 0';
            $where .= self::getWhereNoSysytem();

            $sql = 'SELECT from_user
                FROM `' . self::getTable(true) . '` USE INDEX (is_new_to_user_to_user_deleted_id) ' . $where . '
                ORDER BY id DESC LIMIT 1';
            $uid = DB::result($sql, 0, 2);
            if($uid) {
                $info['uid'] = $uid;
                $info['message'] = addslashes(lSetVars('app_notification_text', array('name' => User::nameShort(User::getInfoBasic($uid, 'name')))));
            }
        }

        return $info;
    }

    static function getCountNewMessagesFromUsers()
    {
        global $g_user;

        $countMsgNewFromUsers = array();
        if ($g_user) {
            $sql = 'SELECT SUM(is_new) as count, `from_user` FROM `' . self::getTable(true) . '`
                     WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                     ' AND `to_user_deleted` = 0 ' . self::getWhereNoSysytem() . '
                     GROUP BY `from_user`';

            $countMsgNew = DB::rows($sql);
            foreach ($countMsgNew as $item) {
                $countMsgNewFromUsers[$item['from_user']] = $item['count'];
            }
        }

        return $countMsgNewFromUsers;
    }

    static function getCountNewMessagesFromListUsers($json = true)
    {
        if(self::$countMessagesFromUsers === null) {
            $usersNewMsg = self::getCountNewMessagesFromUsers();
        } else {
            $usersNewMsg = self::$countMessagesFromUsers;
        }

        $usersNewMsg['all'] = array_sum($usersNewMsg);
        return $json ? json_encode($usersNewMsg) : $usersNewMsg;
    }

    static function getLastWatchedMsgId()
    {
        global $g_user;

        $lastId = 0;
        if ($g_user) {
            $where = 'WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') . self::getWhereNoSysytem() . ' ORDER BY `id` DESC LIMIT 1';
            $lastId = DB::result('SELECT `id` FROM `' . self::getTable(true) . '` ' . $where);
        }
        return $lastId;
    }

    static function setWindowEvent()
    {
        global $g_user;

        if ($g_user) {
            delses('window_count_event_last');
            $lastMsg = self::getLastWatchedMsgId();
            $where = '`to_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND id > ' . to_sql($lastMsg, 'Number');
            $where .= self::getWhereNoSysytem();
            $count = DB::count(self::getTable(true), $where);
            set_session('window_last_im_msg', $lastMsg);
            set_session('window_count_event', $count);
        }

    }

    static function getWhereMessagesFrom($userId)
    {
        global $g_user;

        $where = ' `to_user` = ' . to_sql($userId, 'Number') .
                  ' AND
                   `from_user` = ' . to_sql($g_user['user_id'], 'Number') . ' ';
        return $where;
    }

    static function getWhereMessagesTo($userId)
    {
        global $g_user;

        $where = ' `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                  ' AND
                   `from_user` = ' . to_sql($userId, 'Number') . ' ';
        return $where;
    }

    static function getWhereMessages($userId)
    {
        global $g_user;

        $where = '(`to_user` = ' . to_sql($userId, 'Number') .
                  ' AND
                   `from_user` = ' . to_sql($g_user['user_id'], 'Number') . ')
               OR (`to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                  ' AND
                   `from_user` = ' . to_sql($userId, 'Number') . ') ';
        return $where;
    }
    /* GET */


    /* UPDATE */
    static function parseReadAudioMessage(&$html, $block = 'show_read_audio_marks')//Edge audio
    {
        if (!$html->blockExists($block) || !guid()) {
            return false;
        }

        $typeIm = Common::getOptionTemplate('im_type');
        if ($typeIm != 'edge') {
            return false;
        }

        $getReadMsgFromIm = self::jsonDecodeParamArray('get_read_msg_audio_from_im');
        if (!$getReadMsgFromIm) {
            return false;
        }

        if (Common::allowedFeatureSuperPowersFromTemplate('message_read_receipts')
                && !User::accessCheckFeatureSuperPowers('message_read_receipts')) {
            return false;
        }


        $result = false;
        $sql = 'SELECT `id`
                      FROM `' . self::getTable(true) . '`
                     WHERE `id` IN(' . self::getSqlImplodeKeys($getReadMsgFromIm) . ')'
                   . ' AND `is_new` = 0';
        $msgsRead = DB::all($sql);
        if ($msgsRead) {
            $msgsReadResult = array();
            foreach ($msgsRead as $key => $msg) {
                $msgsReadResult[$msg['id']] = 1;
            }
            $html->setvar($block, json_encode($msgsReadResult));
            $html->parse($block, false);
        }
        //var_dump_pre($msgsRead);

            /*if ($lastMsgsRead) {
                $msgsReadResult = array();
                foreach ($lastMsgsRead as $msg) {
                    if ($typeIm == 'edge') {
                        $key = $msg['to_user'] . '_';
                        $key .= $msg['to_group_id'] ? $msg['to_group_id'] : $msg['from_group_id'];
                        $msgsReadResult[$key] = $msg['max_id'];
                    } else {
                        $msgsReadResult[$msg['to_user']] = $msg['max_id'];
                    }
                }
                $html->setvar($block, json_encode($msgsReadResult));
                $html->parse($block, false);
                $result = true;
            }*/

        return $result;
    }

    static function parseReadMessage(&$html, $block = 'show_read_marks')
    {
        if (!$html->blockExists($block) || !guid()) {
            return false;
        }

        $typeIm = Common::getOptionTemplate('im_type');
        $userTo = get_param_int('user_to', get_param_int('user_current'));
        if ($typeIm == 'edge') {
            $userTo = 0;
        }
        $getReadMsgFromIm = $userTo ? get_param_int('get_read_msg_from_im') : self::jsonDecodeParamArray('get_read_msg_from_im');
        if (!$getReadMsgFromIm) {
            return false;
        }

        if (Common::allowedFeatureSuperPowersFromTemplate('message_read_receipts')
                && !User::accessCheckFeatureSuperPowers('message_read_receipts')) {
            return false;
        }


        $result = false;
        if ($userTo) {
            $sql = 'SELECT MAX(`id`)
                      FROM `' . self::getTable(true) . '`
                     WHERE `from_user` = ' . to_sql(guid())
                   . ' AND `to_user` =' . to_sql($userTo)
                   . ' AND `is_new` = 0';
                     //AND `from_user_deleted` = 0'
            $lastMsgReadId = DB::result($sql);
            if ($lastMsgReadId) {
                $html->setvar($block, $lastMsgReadId);
                $html->parse($block, false);
                $result = true;
            }
        } else {
            $groupBy = '`to_user`';
            $from = '';
            if ($typeIm == 'edge') {
                $groupBy = '`to_user`, `to_group_id`, `from_group_id`';
                $from = ', `to_group_id`, `from_group_id`';
            }
            $sql = 'SELECT MAX(`id`) AS max_id, `to_user` ' . $from . '
                      FROM `' . self::getTable(true) . '`
                     WHERE `from_user` = ' . to_sql(guid())
                   . ' AND `to_user` IN(' . self::getSqlImplodeKeys($getReadMsgFromIm) . ')'
                   . ' AND `is_new` = 0'
                     //AND `from_user_deleted` = 0'
                 . ' GROUP BY ' . $groupBy;
            $lastMsgsRead = DB::all($sql);

            if ($lastMsgsRead) {
                $msgsReadResult = array();
                foreach ($lastMsgsRead as $msg) {
                    if ($typeIm == 'edge') {
                        $key = $msg['to_user'] . '_';
                        $key .= $msg['to_group_id'] ? $msg['to_group_id'] : $msg['from_group_id'];
                        $msgsReadResult[$key] = $msg['max_id'];
                    } else {
                        $msgsReadResult[$msg['to_user']] = $msg['max_id'];
                    }
                }
                $html->setvar($block, json_encode($msgsReadResult));
                $html->parse($block, false);
                $result = true;
            }
        }

        return $result;
    }


    static function updateMessagesLast(&$html)
    {
        global $g_user;
        $lastId = get_param('last_id');
        self::updateMessages($html, $lastId);

        /* Verification of the existence of messages - EDGE */
        if ($html->blockExists('messages_existing_ajax')) {
            $getListMsg = self::jsonDecodeParamArray('list_msg_current_im');
            if ($getListMsg) {
                $where = '`id` IN(' . self::getSqlImplodeKeys($getListMsg)  . ')';
                $existingMessages = DB::field(self::getTable(true), 'id' , $where);
                $existingMessages = array_flip($existingMessages);
                $deleteMsg = array();
                foreach ($getListMsg as $id => $uid) {
                    if(!isset($existingMessages[$id])){
                        $deleteMsg[$id] = $uid;
                    }
                }
                if ($deleteMsg) {
                    $html->setvar('existing_messages', json_encode($deleteMsg));
                    $html->parse('messages_existing_ajax', false);
                }
            }
        }
        /* Verification of the existence of messages - EDGE */

        /* Verification of the existence of messages Not used - DISABLED
        $where = '(`from_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `from_user_deleted` = 0)
                    OR
                  (`to_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `to_user_deleted` = 0)';
        $existingMessages = DB::field(self::getTable(true), 'id' ,$where);
        $html->setvar('existing_messages', json_encode(array_flip($existingMessages)));
        $html->parse('messages_existing_ajax', true);
        /* Verification of the existence of messages */
    }

    static function updateMessages(&$html, $lastId, $setIsReadMsg = null)
    {
        global $g_user;

        if ($g_user['user_id'] > 0)
        {
            $optionTmplName = Common::getOption('name', 'template_options');

            $isUpdate = false;
            $received = false;

            $isFbMode = get_param('is_mode_fb');
            //if ($isFbMode == 'false' && $optionTmplName != 'urban_mobile') {
                //self::setMessageAsRead();
            //}
            $isUpdateMsgOpenListChats = get_param('display') == 'update_msg_open_list_chats';
            if (!$isUpdateMsgOpenListChats) {
                self::setStatusUsers($html);
            }

            $userTo = get_param('user_to');
            $userToOneChat = null;
            if (self::$isMobileOneChat && $userTo) {
                $userToOneChat = $userTo;
                $where = ' ((`to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                            ' AND `from_user` = ' . to_sql($userTo, 'Number') .
                            ' AND `to_user_deleted` = 0)
                            OR
                            (`from_user` = ' . to_sql($g_user['user_id'], 'Number') .
                            ' AND `to_user` = ' . to_sql($userTo, 'Number') .
                            ' AND `from_user_deleted` = 0)) ';
            } else {
                $where = ' ((`to_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `to_user_deleted` = 0)
                            OR
                            (`from_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `from_user_deleted` = 0)) ';
            }

            $where .= self::getWhereNoSysytem();

            $listUsersOpen = array();

            if ($isUpdateMsgOpenListChats) {
                $listUsersOpen = self::jsonDecodeParamArray('users_list_open_im');
                if (!is_array($listUsersOpen)) {
                    $listUsersOpen = array();
                }
            }

            $fromUserWriting = array();
            if ($html->blockExists('update_writing_users')) {
                $fromUserWriting = self::getWritingUser(null, $userToOneChat);
            }

            $sql = 'SELECT *
                      FROM `' . self::getTable(true) . '`
                     WHERE ' . $where . '
                       AND id > ' . to_sql($lastId, 'Number') .
                   ' ORDER BY id ASC';
            DB::query($sql, 1);

            while ($row = DB::fetch_row(1))
            {
                $html->clean('message_list');
                if($lastId == $row['id']) {
                    break;
                }

                $isUpdate = true;

                if ($g_user['user_id'] == $row['to_user']) {
                    $received = true;
                    $userTo = $row['from_user'];
                    if (isset($fromUserWriting[$userTo])) {
                        $fromUserWriting[$userTo] = 0;
                    }
                } else {
                    $userTo = $row['to_user'];
                }
                /* Impact */
                $prevMsgUid = 0;
                self::parseResponderInfo($html, $row['from_user'], $prevMsgUid, $row);
                /* Impact */

                self::parseImOneMsg($html, $row, true, 1, $isFbMode, $listUsersOpen);
                //$html->clean('message_responder');
                //$html->clean('message_answer');
                $html->parse('message_list', true);
            }

            $isVisibleMessages = get_param('is_visible_messages');
            if ($html->blockExists('update_writing_users') && $fromUserWriting && ($isVisibleMessages == 'true' || self::$isMobileOneChat)) {
                $updateWritingUsers = array();
                $deleteWritingUsers = array();
                foreach ($fromUserWriting as $user => $status) {
                    if ($status) {
                        $updateWritingUsers[$user] = 1;
                    } else {
                        $deleteWritingUsers[$user] = 1;
                    }
                }
                if ($updateWritingUsers) {
                    $isUpdate = true;
                    $html->setvar('update_writing_users', json_encode($updateWritingUsers));
                    $html->parse('update_writing_users', false);
                }

                if ($deleteWritingUsers) {
                    $isUpdate = true;
                    $where = '`to_user` = ' . to_sql(guid())
                      . ' AND `from_user` IN(' . self::getSqlImplodeKeys($deleteWritingUsers)  . ')';
                    DB::update(self::getTable(), array('last_writing' => 0), $where);
                    $html->setvar('delete_writing_users', json_encode($deleteWritingUsers));
                    $html->parse('delete_writing_users', false);
                }
            }

            $isParseRead = self::parseReadMessage($html);
            $isParseReadAudio = self::parseReadAudioMessage($html);

            if ($isUpdate || $isParseRead || $isParseReadAudio) {
                if ($received && $g_user['sound'] != 2) {
                    $html->parse('sound');
                }
                self::lastId(false);
                //$html->setvar('last_id', self::getLastId());
                $html->parse('update_messages');
            }
        }
    }
    /* UPDATE */
    static function closeEmptyIm()
    {
        global $g_user;
        $responseData = false;
        if ($g_user['user_id']) {
            self::setCurrentData();
            $sql = 'SELECT `to_user`
                      FROM `' . self::getTable() . '`
                     WHERE `from_user` = ' . to_sql($g_user['user_id'], 'Number') . self::$demoWhere;
                     //' AND `mid` = 1 ' . self::$demoWhere;
            DB::query($sql, 1);
            while ($row = DB::fetch_row(1)){
                $count = self::getCountMsgIm($row['to_user']);
                if (!$count) {
                    self::closeIm($row['to_user'], false);
                }
            }
            $responseData = true;
        }
        return $responseData;
    }

    static function closeEmptyOneIm($userTo)
    {
        $count = self::getCountMsgIm($userTo);
        if (!$count) {
            self::closeIm($userTo, false);
        }
    }

    static function closeSelectedIm($users = null)
    {
        global $g_user;

        $responseData = false;
        if ($g_user['user_id']) {
            if ($users === null) {
                $users = get_param_array('delete_im');
            }
            if (!empty($users)) {
                foreach ($users as $userId => $value) {
                    self::closeIm($userId);
                }
                $responseData = true;
            }
        }
        return $responseData;
    }

    static function closeIm($userId = null, $isDeletedMsg = true)
    {
        global $g_user;

        if ($g_user['user_id']) {
            if ($userId === null) {
                $userId = get_param('user_id');
            }
            if ($isDeletedMsg) {
                self::clearHistoryMessages($userId);
            }
            if (!empty($userId)) {
                self::setCurrentData();
                $where = '`to_user` = ' . to_sql($userId, 'Number') .
                    ' AND `from_user` = ' . to_sql($g_user['user_id'], 'Number') .
                    self::$demoWhere .
                    self::getWhereGroup();
                DB::delete(self::getTable(), $where);
            }
        }
    }

    static function firstOpenIm($userId, $isUpdate = true, $isVisible = false, $lastMid = 0)
    {


        global $g_user;

        $guid = $g_user['user_id'];
        if (!$guid) return;

        self::setCurrentData();

        $visible = '';
        if ($isVisible) {
            $visible = ", `im_open_visible` = 'Y'";
        }
        $groupAdd = self::getAddInsert();

        if ($lastMid) {
            $sql = 'INSERT INTO `' . self::getTable() . '`
                       SET `from_user` = ' . to_sql($guid, 'Number') . ',
                           `to_user` = ' . to_sql($userId, 'Number') . ',
                           `mid` = ' . to_sql($lastMid, 'Number')
                           . $visible
                           . $groupAdd
                           . self::$demoInsert .
                      ' ON DUPLICATE KEY UPDATE
                            `mid` = ' . to_sql(to_sql($lastMid, 'Number')) . $visible;
        } else {
            $z = time();
            $zSql = $isUpdate ? ',`z` = ' . $z : '';
            $isNewMsg = self::getCountNewMessages($userId) ? 1 : 0;
            $sql = 'INSERT INTO `' . self::getTable() . '`
                       SET `from_user` = ' . to_sql($guid, 'Number') . ',
                           `to_user` = ' . to_sql($userId, 'Number') . ',
                           `mid` = 1,
                           `z` = ' . $z . ','
                        . '`is_new_msg` = ' . $isNewMsg
                        . $visible
                        . $groupAdd
                        . self::$demoInsert .
                      ' ON DUPLICATE KEY UPDATE
                            mid = IF(mid > 0, mid, 1)' . $visible . $zSql;
        }
        DB::execute($sql);

        if($userId != '100000001') {
            $sql = 'INSERT IGNORE INTO `' . self::getTable() . '`
                            SET `from_user` = ' . to_sql($userId, 'Number') . ',
                                `to_user` = ' . to_sql($guid, 'Number') . ',
                                `mid` = 0 '
                                . self::getAddInsert(true)
                                . self::$demoInsert;
            DB::execute($sql);
        }
        
    }

    static function firstOpenImGroup($userId, $lastMid = false)
    {
        global $g_user;

        $guid = $g_user['user_id'];
        if (!$guid) return;

        $isUpdate = true;
        self::setCurrentData();

        $visible = '';
            $visible = ", `im_open_visible` = 'Y'";
        $groupId = self::getGroupId();
        $groupAdd = ', `group_id` = ' . to_sql($groupId);

        if ($lastMid) {
            $sql = 'INSERT INTO `' . self::getTable() . '`
                       SET `from_user` = ' . to_sql($guid, 'Number') . ',
                           `to_user` = ' . to_sql($userId, 'Number') . ',
                           `mid` = ' . to_sql($lastMid, 'Number')
                           . $visible
                           // . $groupAdd
                           . self::$demoInsert .
                      ' ON DUPLICATE KEY UPDATE
                            `mid` = ' . to_sql(to_sql($lastMid, 'Number')) . $visible;
        } else {
            $z = time();
            $zSql = $isUpdate ? ',`z` = ' . $z : '';
            $isNewMsg = self::getCountNewMessages($userId) ? 1 : 0;
            $sql = 'INSERT INTO `' . self::getTable() . '`
                       SET `from_user` = ' . to_sql($guid, 'Number') . ',
                           `to_user` = ' . to_sql($userId, 'Number') . ',
                           `mid` = 1,
                           `z` = ' . $z . ','
                        . '`is_new_msg` = ' . $isNewMsg
                        . $visible
                        // . $groupAdd
                        . self::$demoInsert .
                      ' ON DUPLICATE KEY UPDATE
                            mid = IF(mid > 0, mid, 1)' . $visible . $zSql;
        }
        DB::execute($sql);

        if($userId != '100000001') {
            $sql = 'INSERT IGNORE INTO `' . self::getTable() . '`
                            SET `from_user` = ' . to_sql($userId, 'Number') . ',
                                `to_user` = ' . to_sql($guid, 'Number') . ',
                                `mid` = 0 '
                                // . self::getAddInsert(true)
                                . self::$demoInsert;
            DB::execute($sql);
        }
        

    }

    static function getMidRequestPrivateAccess($userId)
    {
        $mid = 0;
        $where = self::getWhereMessagesTo($userId) . " AND `system` = 1 AND `msg` = 'private_photo_request'";
        $privatPhotoRequest = DB::field(self::getTable(true), 'id' , $where);
        if (!empty($privatPhotoRequest) && isset($privatPhotoRequest[0])) {
              $mid = $privatPhotoRequest[0];
        }
        return $mid;
    }

    static function sendRequestMsgPrivateAccess($userTo, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d H:i:s');
        }
        self::addMessageToDb($userTo, 'private_photo_request', $date, 1, 0, true, true, 1);
        self::addMessageToDb($userTo, 'private_photo_report', $date, 0, 1, true, true, 1, 1);
    }

    static function updateSystemMessagePrivateAccess($userTo, $typeMsg, $typeMsgAddDb, $date = null,  $mid = null)
    {
        if ($date === null) {
            $date = date('Y-m-d H:i:s');
        }
        if ($mid === null) {
            $mid = self::getMidRequestPrivateAccess($userTo);
        }
        if ($mid) {
            self::updateCustomMessageToDb($mid, $userTo, $typeMsg);
        }
        self::addMessageToDb($userTo, $typeMsgAddDb, $date, 1, 0, true, true, 1);
        self::closeEmptyOneIm($userTo);
    }

    static function sendRequestPrivateAccess($userTo = null, $type = null)
    {
        global $g_user;

        $response = false;

        if ($g_user['user_id']) {
            if ($userTo === null) {
                $userTo = get_param('user_to', 0);
            }
            if (empty($userTo)) {
                return false;
            }
            $fromDelete = 1;
            $toDelete = 0;
            $date = date('Y-m-d H:i:s');
            $mid = get_param('mid', null);
            if ($type === null) {
                $type = get_param('type', 'request_access');
            }
            $isFriendRequestExists = User::isFriendRequestExists($userTo, $g_user['user_id']);
            $isFriend = User::isFriend($userTo, $g_user['user_id']);
            if ($type == 'request_access') {
                $user = User::getInfoBasic($userTo);
                if (!empty($user)) {
                    if (!$isFriendRequestExists && !$isFriend) {
                        User::friendRequestSend($user, '', false);
                    }
                    self::sendRequestMsgPrivateAccess($userTo);
                    $response = true;
                }
            } elseif ($type == 'request_approved') {
                //if ($isFriendRequestExists) {
                    User::friendApprove($userTo, $g_user['user_id'], false, false);
                    self::updateSystemMessagePrivateAccess($userTo, 'you_granted_access', 'private_photo_request_approved', $date, $mid);
                    $response = true;
                //}
            } elseif ($type == 'request_declined') {
                $response = self::sendPrivateDeclined($userTo, $mid, $isFriend, $isFriendRequestExists);
            }
        }
        return $response;
    }

    static function sendPrivateDeclined($userTo, $mid = null,  $isFriend = null, $isFriendRequestExists = null)
    {
        global $g_user;

        if ($mid === null) {
            $mid = get_param('mid', null);
        }
        if ($isFriend === null) {
            $isFriend = User::isFriend($userTo, $g_user['user_id']);
        }
        if ($isFriendRequestExists === null) {
            $isFriendRequestExists = User::isFriendRequestExists($userTo, $g_user['user_id']);
        }

        $isAction = false;
        if ($isFriend) {
            User::friendDelete($userTo, $g_user['user_id']);
            self::updateSystemMessagePrivateAccess($userTo, 'private_photo_request_declined', 'private_photo_request_declined', null, $mid);
        } else if ($isFriendRequestExists) {
            User::friendDecline($userTo, $g_user['user_id']);
            if ($isFriendRequestExists == $g_user['user_id']) {
                $where = "`to_user` = " . to_sql($userTo)
                       . " AND `from_user` = " . to_sql($g_user['user_id'])
                       . "  AND `system` = 1 AND (`msg` = 'private_photo_request' OR `msg` = 'private_photo_report')";
                DB::delete(self::getTable(true), $where);
            } else {
                self::updateSystemMessagePrivateAccess($userTo, 'private_photo_request_declined', 'private_photo_request_declined', null, $mid);
            }
        }

        return true;
    }

    static function updateCustomMessageToDb($mid, $userTo, $msg)
    {
        global $g_user;
        $data = array('from_user' => $g_user['user_id'],
                      'to_user' => $userTo,
                      'name' => $g_user['name'],
                      'msg' => $msg,
                      'from_user_deleted' => 0,
                      'to_user_deleted' => 1);
        DB::update(self::getTable(true), $data, '`id` = ' . to_sql($mid ,'Number'));
    }


    static function addMessageToDb($userTo, $msg, $date = null, $fromDelete = 0, $toDelete = 0, $firstIm = false, $popularity = true, $system = 0, $is_new = 1, $send = 0, $msgHash = '')
    {
        global $g_user;

        //if ($firstIm) {
        $optionTmplName = Common::getOption('name', 'template_options');
        $visible = false;
        if ($optionTmplName == 'impact') {
            $visible = true;
        }
        self::firstOpenIm($userTo, true, $visible);//???????????
        //}
        if ($date === null) {
            $date = date('Y-m-d H:i:s');
        }
        $translated = '';
        $systemType = 0;
        if (!$system) {
            $translated = self::getTranslate($msg,$userTo);
        } elseif ($msg == 'welcoming_message') {
            $systemType = 1;
        } else {
            $img = grabs($msg, '{img_upload:', '}');
            if (isset($img[0])) {
                $systemType = 2;
                $translated = self::$msgTranslated;
                self::$msgTranslated = '';
            }
        }

        $userToSql = to_sql($userTo, 'Number');
        $gUser = to_sql($g_user['user_id'], 'Number');
        $sql = 'INSERT INTO `' . self::getTable(true) . '`
                   SET `from_user` = ' . $gUser . ',
                       `to_user` = ' . $userToSql . ',
                       `born` = ' . to_sql($date) . ',
                       `ip` = ' . to_sql(IP::getIp()) . ',
                       `name` = ' . to_sql($g_user['name']) . ',
                       `msg` = ' . to_sql($msg) . ',
                       `msg_translation` = ' . to_sql($translated) . ',
                       `from_user_deleted` = ' . to_sql($fromDelete) . ',
                       `to_user_deleted` = ' . to_sql($toDelete) . ',
                       `system` = ' . to_sql($system) . ',
                       `system_type` = ' . to_sql($systemType) . ',
                       `send` = ' . to_sql($send) . ',
                       `msg_hash` = ' . to_sql($msgHash) . ',
                       `is_new` = ' . to_sql($is_new) .
                       self::getAddInsert();

        $audioMessageId = get_param_int('audio_message_id');

        if($audioMessageId) {
            $sql .= ', `audio_message_id` = ' . to_sql($audioMessageId);
        }

        DB::execute($sql);
        $lastMid = DB::insert_id();

        ImAudioMessage::updateImMsgId($audioMessageId, $lastMid);

        if(IS_DEMO && !$system) {
            Demo::addImMessage($gUser, $userToSql, $date, $msg);
        }

        $groupId = self::getGroupId();

        $isFreeSite = Common::isOptionActive('free_site');
        if ($groupId) {
            $isFreeSite = true;
        } else {
            if(self::isContactReplyItemExists($gUser, $userToSql)) {
                self::markContactAsReplied($gUser, $userToSql);
            } else {
                self::addContactReplyItem($userToSql, $gUser);
            }
        }

        $sqlData = array('mid' => $lastMid);
        $isActiveSpecialDelivery = Common::isActiveFeatureSuperPowers('special_delivery');
        if ($isFreeSite
              || (($isActiveSpecialDelivery && User::isSuperPowers())
                    ||!$isActiveSpecialDelivery)) {
            $sqlData['z'] = time();
        }

        self::setCurrentData();

        $isUpdateFromIm = true;
        if ($msg == 'welcoming_message' && $system) {
            $lastMid = 1;
            $isUpdateFromIm = false;
        }
        self::firstOpenIm($userTo, true, $visible, $lastMid);
        self::updateLastIdFromAddMessage($userTo, $lastMid, $sqlData, $is_new, $isUpdateFromIm);

        if ($popularity && !$groupId) {
            User::updatePopularity($userTo);
        }
        return $lastMid;
    }

    static function updateLastIdFromAddMessage($userTo, $lastMid, $sqlData = null, $isNew = null, $isUpdateFromIm = true)
    {
        self::setCurrentData();

        if ($sqlData === null) {
            $sqlData = array('mid' => $lastMid, 'z' => time());
        }

        $userToSql = to_sql($userTo, 'Number');
        $gUser = to_sql(guid(), 'Number');

        if ($isUpdateFromIm) {
            $where = '`from_user` =  ' . $gUser . ' AND `to_user` =  ' . $userToSql . self::getWhereGroup();//For demo . self::$demoWhere
            DB::update(self::getTable(), $sqlData, $where);//For demo , '', 1
        }

        $where = '`from_user` =  ' . $userToSql . ' AND `to_user` =  ' . $gUser . self::getWhereGroup(true);//For demo  . self::$demoWhere
        if ($isNew === null || $isNew) {
            $sqlData['is_new_msg'] = 1;
        }
        DB::update(self::getTable(), $sqlData, $where);//For demo , '', 1
    }

    static function addMessage(&$html, $userTo = null, $msg = null, $parseMsg = true)
    {
        global $g;
        global $g_user;

        if ($userTo === null) {
            $userTo = get_param('user_to', 0);
        }

        if ($msg === null) {
            $msg = get_param('msg');
        }

        Common::updatePopularitySticker();

        $msg = trim($msg);
        $fromDelete = get_param('from_delete', 0);
        $toDelete = get_param('to_delete', 0);
        $send = get_param('send', 0);
        $groupId = self::getGroupId();
        $guid = $g_user['user_id'];

        $cmd = get_param('cmd');
        if ($cmd == 'send_message' && get_param_int('retry')) {
            $sql = 'SELECT `id` FROM `' . self::getTable(true) . '`
                     WHERE `from_user` = ' . to_sql($g_user['user_id']) .
                     ' AND `to_user` = ' . to_sql($userTo) .
                     ' AND `send` = ' . to_sql($send) .
                     self::getWhereGroup();
            $isAlreadySent = DB::result($sql);
            if ($isAlreadySent) {
                return false;
            }
        }

        $row = array();
        $responseData = false;

        $audioMessageId = get_param_int('audio_message_id');
        $imageUpload = get_param_int('image_upload');
        $imSendImage = Common::isOptionActiveTemplate('im_send_image');

        if ($g_user['user_id'] && $userTo
                && ($msg != '' || $audioMessageId || $imageUpload)) {
            $optionTmplName = Common::getOption('name', 'template_options');
            $blockMsg = 'message';
            $isFreeSite = Common::isOptionActive('free_site');
            $isSuperPowers = User::isSuperPowers();
            $isCreditsEnabled = Common::isOptionActive('credits_enabled');
            $system = 0;
            $systemType = 0;
            //$isBlocked = User::isBlocked('im', $userTo, $g_user['user_id']);

            if ($groupId){
                $isBlocked = Groups::isEntryBlocked($groupId, $guid);
            } else {
                $isBlocked = User::isEntryBlocked($userTo, $guid);
            }

            $isNotifSend = true;
            $cost = Pay::getServicePrice('message','credits');
            $notMsgToDb = false;

            if ($isBlocked || self::isBanSendMsg($msg)) {
                $msg = $isBlocked ? 'sent_to_block_list' : 'sent_to_block_list_spam';
                $system = 1;
                $isNotifSend = false;
                //$toDelete = 1;
                $notMsgToDb = true;
            } elseif (!$isFreeSite && !$isSuperPowers) {
                $notAllowedChatWithPopularUsers = Common::isOptionActive('not_allowed_chat_with_popular_users', 'template_options');
                $numberSpMsgDay = $g_user['sp_sending_messages_per_day'] + 1;
                if ($numberSpMsgDay > Common::getOption('sp_sending_messages_per_day_urban')) {
                    $gender = User::getInfoBasic($userTo, 'gender');
                    $msg = 'msg_limit_is_reached_' . mb_strtolower($gender, 'UTF-8');
                    $system = 1;
                    $isNotifSend = false;
                    //$toDelete = 1;
                    $notMsgToDb = true;
                } elseif (!$notAllowedChatWithPopularUsers
                            && User::getLevelOfPopularity($userTo) == 'very_high'
                                && Common::isActiveFeatureSuperPowers('chat_with_popular_users')) {
                    $gender = User::getInfoBasic($userTo, 'gender');
                    $msg = 'sent_to_user_popular_' . mb_strtolower($gender, 'UTF-8');
                    $system = 1;
                    $isNotifSend = false;
                    //$toDelete = 1;
                    $notMsgToDb = true;
                } else  {
                    User::update(array('sp_sending_messages_per_day' => $numberSpMsgDay));
                }
            }

            if($isCreditsEnabled && $isNotifSend){
                if($g_user['credits'] >= $cost){
                    $newCredits = $g_user['credits'] - $cost;
                    $data = array('credits' => $newCredits);
                    User::update($data);
                    $row['new_credits'] = $newCredits;
                } else {
                    $gender = User::getInfoBasic($userTo, 'gender');
                    $msg = 'no_credits_for_msgs_' . mb_strtolower($gender, 'UTF-8');
                    $system = 1;
                    $isNotifSend = false;
                   // $toDelete = 1;
                    $notMsgToDb = true;
                }
            }

            self::setCurrentData();

            $msg = str_replace("<", "&lt;", $msg);
            $msg = censured($msg);

            $date = date('Y-m-d H:i:s');

            if ($notMsgToDb) {
                $lastMid = 'system_' . time();
            }else{
                if ($imSendImage) {
                    if ($imageUpload) {
                        Gallery::$userToIm = $userTo;
                        $imgId = Gallery::uploadIm($msg);
                        Gallery::$userToIm = 0;
                        if ($imgId) {
                            $system = 1;
                            $msg = '{img_upload:' . $imgId . '}';
                            $systemType = 2;
                        }
                    } else {
                        $msg = OutsideImages::filter_to_db($msg);
                        $msg = VideoHosts::textUrlToVideoCode($msg);
                    }
                }
                $msgHash = md5(mb_strtolower($msg, 'UTF-8'));
                $lastMid = self::addMessageToDb($userTo, $msg, $date, $fromDelete, $toDelete, false, true, $system, 1, $send, $msgHash);
                CStatsTools::count('im_messages');
            }
            $row['from_user'] = $g_user['user_id'];
            $row['to_user'] = $userTo;
            $row['msg'] = $msg;
            $row['id'] = $lastMid;
            $row['born'] = $date;
            $row['name'] = $g_user['name'];
            $row['is_new'] = 1;
            $row['system'] = $system;
            $row['system_type'] = $systemType;
            $row['send'] = $send;
            $row['audio_message_id'] = $audioMessageId;
            $row['from_group_id'] = get_param_int('from_group_id');
            $row['to_group_id'] = get_param_int('to_group_id');
            if ($parseMsg) {
                self::parseImOneMsg($html, $row, true, 0);
            }

            $userInfo = User::getInfoBasic($userTo);

            $senderName = $g_user['name'];
            $senderGroupId = 0;
            $toName = $userInfo['name'];
            $dataPushNotification = array();
            if (Common::isOptionActiveTemplate('groups_social_enabled')) {
                if ($row['from_group_id']) {
                    $groupInfo = Groups::getInfoBasic($row['from_group_id']);
                    if ($groupInfo) {
                        $senderGroupId = $row['from_group_id'];
                        $senderName = $groupInfo['title'];
                    }
                } elseif ($row['to_group_id']) {
                    $groupInfo = Groups::getInfoBasic($row['to_group_id']);
                    if ($groupInfo) {
                        $senderGroupId = $row['to_group_id'];
                        $toName = $groupInfo['title'];
                    }
                }
                if ($senderGroupId) {
                    $dataPushNotification['group_user_id'] = $groupInfo['user_id'];
                    $dataPushNotification['group_id'] = $senderGroupId;
                }
            }

            if ($isNotifSend
                && Common::isEnabledAutoMail('new_message')
                && User::isOptionSettings('set_notif_new_msg', $userInfo)
                && !User::isOnline($userTo, $userInfo)) {
                $vars = array('title' => Common::getOption('title', 'main'),
                              'name' => $toName,
                              'uid' => $userTo,
                              'name_sender'  => $senderName,
                              'uid_sender' => $g_user['user_id'],
                              'group_id_sender' => $senderGroupId,
                              'url_site' => Common::urlSite());
                Common::sendAutomail($userInfo['lang'], $userInfo['mail'], 'new_message', $vars);
            }

            $userToInfo = User::getInfoBasic($userTo);

            Common::usersms('new_msg_sms', $userToInfo, 'set_sms_alert_mi');

            if($isNotifSend && User::isOptionSettings('set_notif_push_notifications', $userInfo)) {
                if (Common::getOptionTemplate('im_type') == 'edge') {
                    $dataPushNotification['url'] = User::url($userTo, null, array('show' => 'message', 'uid_sender' => $guid, 'group_id_sender' => $senderGroupId));
                }

                PushNotification::sendIm($userTo, Common::replaceByVars(l('app_notification_text', loadLanguageSiteMobile($userInfo['lang'])), array('name' => User::nameShort($senderName))), $dataPushNotification);
            }

            if ($parseMsg) {
                $html->parse($blockMsg . '_list');
            }
            /* Impact mobile */
            if (get_param('send_msg_from_profile') && $notMsgToDb) {
                if ($msg == 'sent_to_block_list') {
                    $responseData = 'you_are_in_block_list';
                } elseif ($msg == 'sent_to_block_list_spam') {
                    $responseData = 'you_are_in_block_list_spam';
                } elseif ($msg == 'msg_limit_is_reached_f' || $msg == 'msg_limit_is_reached_m') {
                    $responseData = $msg;
                }else{
                    $responseData = 'buy_credits';
                }
            }else{
               $responseData = true;
            }
        }
        return $responseData;
    }

    static function parseTitleIm(&$html, $toUser, $show, $userInfo = null)
    {
        $guid = guid();

        if ($userInfo === null) {
            $userInfo = User::getInfoBasic($toUser);
        }

        if ($userInfo) {

            $cmd = get_param('cmd');
            $isOpenImImpact = $cmd == 'open_im_with_user';

            $sizePhoto = 'r';
            if ($isOpenImImpact) {
                $sizePhoto = 'm';
            }

            $groupId = $userInfo['to_group_id'];
            if (!Common::isOptionActiveTemplate('groups_social_enabled')) {
                $groupId = 0;
            }
            if ($groupId) {
                $groupInfo = Groups::getInfoBasic($groupId);
                $userName = $groupInfo['title'];
                $userPhoto = GroupsPhoto::getPhotoDefault($toUser, $groupId, $sizePhoto);
                $userAge = '';
                $userCity = '';
                $userNameTitle = '';
                $userLink = Groups::url($groupId, $groupInfo);
            } elseif ($userInfo['from_group_id'] && $userInfo['to_user'] == '100000001') {
                $groupId = $userInfo['from_group_id'];
                $groupInfo = Groups::getInfoBasic($groupId);
                $userName = 'members (' .  $groupInfo['title'] . ')';
                $userPhoto = GroupsPhoto::getPhotoDefault($userInfo['from_user'], $groupId, $sizePhoto);
                $userAge = '';
                $userCity = '';
                $userNameTitle = '';
                $userLink = Groups::url($groupId, $groupInfo) . '/subscribers';
            } 
             else {
                $userPhoto = User::getPhotoDefault($toUser, $sizePhoto, false, $userInfo['gender']);
                $userName = $userInfo['name'];
                /*if ($userInfo['from_group_id']) {
                    $groupName = Groups::getInfoBasic($userInfo['from_group_id'], 'title');
                    $userName .= ' to "' . $groupName . '"';
                }*/
                $userAge = $userInfo['age'];
                $userCity = l($userInfo['city']);
                $userNameTitle = $userAge . ', ' . $userCity;
                $userLink = User::url($toUser, $userInfo);
            }

            $html->setvar('user_to_id', $toUser);
            $html->setvar('users_to_group_id', $userInfo['from_group_id'] ? $userInfo['from_group_id'] : $userInfo['to_group_id']);
            $html->setvar('users_to_from_group_id', $userInfo['from_group_id']);
            $html->setvar('users_to_to_group_id', $userInfo['to_group_id']);

            $html->setvar('user_to_profile_link', $userLink);

            $html->setvar('user_to_photo', $userPhoto);
            $html->setvar('user_to_name', $userName);
            $varNameShort = 'user_to_name_short';
            if ($html->varExists($varNameShort)) {
                $html->setvar($varNameShort, User::nameOneLetterFull($userName));
            }

            $html->setvar('user_to_age', $userAge);
            $html->setvar('user_to_city', $userCity);

            $varNameTitle = 'user_to_name_title';
            if ($html->varExists($varNameTitle)) {
                $html->setvar($varNameTitle, $userNameTitle);
            }

            // DUBL take UserFields->parseInterests
            $userInterests = User::getInterests($toUser);

            if (!empty($userInterests) && !$isOpenImImpact) {
                $guidInterests = User::getInterests(guid());
                $userInterestsAll = array();
                $guidInterestsAll = array();

                foreach ($guidInterests as $item) {
                    $guidInterestsAll[$item['id']] = $item;
                    $guidInterestsAll[$item['id']]['main'] = 1;
                }
                foreach ($userInterests as $item) {
                    $userInterestsAll[$item['id']] = $item;
                }
                $userInterests = array_merge(array_intersect_key($guidInterestsAll, $userInterestsAll), array_diff_key($userInterestsAll, $guidInterestsAll));

                $i = 0;
                $j = 0;
                foreach ($userInterests as $item) {
                    if ($i == 4) {
                        break;
                    }
                    $html->setvar('cat_id', $item['category']);
                    $titleUpper = mb_ucfirst($item['interest']);
                    $html->setvar('interest', $titleUpper);
                    $html->setvar('interest_he', he($titleUpper));
                    $html->setvar('int_id', $item['id']);
                    if (isset($item['main'])) {
                        $j++;
                        $html->parse('main_interest', false);
                        $type = 'shared';
                    } else {
                        $type = 'normal';
                        $html->clean('main_interest');
                    }
                    $html->setvar('interest_class', UserFields::getArrayNameIcoField('interests', $item['category'], $type));

                    $html->parse('interest_item', true);
                    $i++;
                }
                // interest_dots_custom - Out of UserFields->parseInterests
                if ($j == 4) {
                    $html->parse('interest_dots_custom');
                }

                $html->parse('list_interest', false);
                $html->clean('interest_item');
            } else {
                $html->clean('list_interest');
            }

            if (Common::isOptionActive('videochat')){
                $html->parse('videochat_button', false);
            }

            if (Common::isOptionActive('audiochat')){
                $html->parse('audiochat_button', false);
            }

            if (City::isActiveStreetChat()) {
                $html->parse('citychat_button', false);
            }

            if (!User::isFriend($toUser, $guid)
                && User::isFriendRequestExists($toUser, $guid) != $guid) {
                $html->parse('friend_add', false);
            } else {
                $html->clean('friend_add');
            }

            $html->parse('user_info_name', false);

            if (!$show) {
                $html->parse('user_info_hide', false);
                $html->parse('user_pic_hide', false);
            } else {
                $html->setvar('current_group_id', $userInfo['from_group_id'] ? $userInfo['from_group_id'] : $userInfo['to_group_id']);
                $html->setvar('current_from_group_id', $userInfo['from_group_id']);
                $html->setvar('current_to_group_id', $userInfo['to_group_id']);
                $html->setvar('current_user_to_id', $toUser);
            }
            $html->parse('user_to_info');
        }
    }

    static function parseStatusOnline(&$html, $toUser, $userInfo = null)
    {
        if ($userInfo === null) {
            $userInfo = User::getInfoBasic($toUser);
        }

        $isOnline = User::isOnline($toUser, $userInfo);
        if ($isOnline) {
            $online = 1;
            $html->parse('status_online_title', false);
            $html->parse('status_online', false);
            $html->clean('status_offline');
        } else {
            $online = 0;
            $html->parse('status_offline', false);
            $html->clean('status_online');
            $html->clean('status_online_title');
        }
        $html->setvar('status_online', $online);
        if ($html->varExists('open_im_active')) {
            $html->setvar('open_im_active', $isOnline ? 'active' : 'noactive');
        }
    }

    static function parseStatusGroup(&$html, $row, $sub_users = [])
    {
        $groupId = $row['group_id'];
        $to_user_id = get_param('user_id', '');
        $request_groupId = self::getGroupId();

        $from_user_id = $row['from_user'];
        $to_user_id = $row['to_user'];

        $is_group_sub = false;
        
        if($sub_users) {
            $group_sub_users = [];
            foreach ($sub_users as $key => $value) {
                $group_sub_users[$value['user_id']] = $value;
            }

            if(isset($group_sub_users[$to_user_id])) {
                $is_group_sub = true;
            }
        }

        if (($groupId && $groupId == $request_groupId) || $is_group_sub) {
            $html->parse('status_group', false);
        } else {
            $html->clean('status_group');
        }
    }

    static function parseInfoUserToIm(&$html, $toUser, $userInfo)
    {
        global $g_user;

        if ($userInfo) {
            $lastMsg = '';
            /* EDGE */

            if (isset($userInfo['last_msg'])) {
                $audioMessageId = isset($userInfo['audio_message_id']) ? $userInfo['audio_message_id'] : 0;
                if ($userInfo['last_msg']) {
                    $lastMsg = self::grabsRequestNotif($userInfo['last_msg'], $userInfo['last_msg_system'], $audioMessageId);
                    /*if ($userInfo['last_msg_system']) {//only "welcoming_message"
                        $lastMsg = self::grabsRequest($userInfo['last_msg'], $userInfo['last_msg_from_user'], $userInfo['last_msg_to_user']);
                    } else {
                        $lastMsg = Common::parseLinksTag(to_html($userInfo['last_msg']), 'a', '&lt;', 'parseLinksSmile');
                    }*/
                } elseif ($userInfo['last_msg_system'] === NULL) {//Fix last msg gift - rarely can be met
                    $sql = 'SELECT * FROM `' . self::getTable(true) . '`
                             WHERE `from_user_deleted` = 0
                               AND ((`from_user` = ' . to_sql($g_user['user_id']) . ' AND `to_user` = ' . to_sql($toUser) . ')
                                OR (`from_user` = ' . to_sql($toUser) . ' AND `to_user` = ' . to_sql($g_user['user_id']) . ')) '
                           . ' AND `group_id` = ' . to_sql($userInfo['group_id']) .
                              self::getWhereNoSysytem('') .
                           ' ORDER BY `id` DESC LIMIT 1';
                    $lastMsgInfo = DB::row($sql, DB_MAX_INDEX);
                    if (isset($lastMsgInfo['msg'])) {
                        $lastMsg = self::grabsRequestNotif($lastMsgInfo['msg'], $lastMsgInfo['system'], $audioMessageId);
                    }
                }

                if ($lastMsg) {
                    $lastMsg = Common::parseLinksSmile($lastMsg);
                }

                if ($userInfo['last_msg_from_user'] == guid() && $lastMsg) {
                    $lastMsg = lSetVars('you_message', array('message' => $lastMsg));
                }
            }

            /*if(isset($userInfo['audio_message_id']) && $userInfo['audio_message_id'] && $lastMsg == '') {
                $lastMsg = '<i class="fa fa-play-circle" aria-hidden="true"></i>';
            }*/

            $groupId = $userInfo['to_group_id'];
            if (!Common::isOptionActiveTemplate('groups_social_enabled')) {
                $groupId = 0;
            }
            $groupType = '';
            $groupLink = '';
            $groupPhoto = '';
            $imName = '';
            if ($groupId) {
                $groupsInfo = Groups::getInfoBasic($groupId);
                $userName = $groupsInfo['title'];
                $imName = $groupsInfo['title'];
                $userPhoto = GroupsPhoto::getPhotoDefault($toUser, $groupId, 'r');
                if ($groupsInfo['page']) {
                    $groupType = 'page';
                }

            } elseif ($userInfo['from_group_id'] && $userInfo['to_user'] == '100000001') {
                $groupId = $userInfo['from_group_id'];
                $groupsInfo = Groups::getInfoBasic($groupId);
                $imName = $groupsInfo['title'];
                $userName = lSetVars('group_to_all_members', array('group_name' => $imName));
                
                $userPhoto = GroupsPhoto::getPhotoDefault($userInfo['from_user'], $groupId, 'r');
                if ($groupsInfo['page']) {
                    $groupType = 'page';
                }
            } else {
                $userPhoto = User::getPhotoDefault($toUser, 'r', false, $userInfo['gender']);
                $userName = $userInfo['name'];
                $imName = $userName;
                if ($userInfo['from_group_id']) {
                    $groupsInfo = Groups::getInfoBasic($userInfo['from_group_id']);
                    $imName = $groupsInfo['title'];
                    $userName = lSetVars('user_to_group_im', array('user_name' => $userName, 'group_name' => $imName));

                    $groupLink = Groups::url($userInfo['from_group_id'], $groupsInfo);
                    $groupPhoto = GroupsPhoto::getPhotoDefault($groupsInfo['user_id'], $userInfo['from_group_id'], 'm');
                }
            }
            /* EDGE */
            $vars = array(
                'user_id'  => $toUser,
                'name'     => $userName,
                'photo'    => $userPhoto,
                'last_msg' => $lastMsg,
                'group_id' => $userInfo['from_group_id'] ? $userInfo['from_group_id'] : $userInfo['to_group_id'],
                'group_type'  => $groupType,
                'group_link'  => $groupLink,
                'group_photo' => $groupPhoto,
                'im_name'  => toJs($imName),
                'im_name_real'  => toJs($userInfo['name']),
                'from_group_id' => $userInfo['from_group_id'],
                'to_group_id' => $userInfo['to_group_id'],
                'calendar_url' => Common::pageUrl('user_calendar', $toUser),
                'report_sent' => User::isReportUser($toUser, $userInfo),
            );

            $html->assign('list_users_item', $vars);

            self::parseStatusOnline($html, $toUser, $userInfo);
        }
    }

    static function cleanBlockMsg(&$html, $noCleanBlock)
    {
        $blocks = array('message_text',
                        'message_gift');
        foreach ($blocks as $block) {
            if ($block != $noCleanBlock) {
                $html->clean($block);
            }
        }
    }

    static function grabsRequestNotifToAttr($msg, $system, $audioMessageId)
    {
        $msgNotif = self::grabsRequestNotif($msg, $system, $audioMessageId);
        $msgNotif = str_replace(array("\r\n", "\n"), ' ', $msgNotif);
        $msgNotif = Common::parseLinksSmile($msgNotif);
        return toAttr($msgNotif);
    }

    static function grabsRequestNotif($msg, $system, $audioMessageId = 0)
    {
        global $g;

        if (!$system
                && stristr($msg, '{img:') === false
                && stristr($msg, '{youtube:') === false
                && !$audioMessageId) {
            return $msg;
        }

        if ($audioMessageId) {
            $msg = '<i class="fa fa-play-circle" aria-hidden="true"></i>' . $msg;
        }

        $typeIm = Common::getOptionTemplate('im_type');
        $previewImage = l('image_preview');
        if ($typeIm == 'edge') {
            $previewImage = '<i class="fa fa-picture-o" aria-hidden="true"></i>';
        }
        $types = array('private_photo_request_approved',
                       'private_photo_request_declined',
                       'private_photo_request',
                       '{gift:',
                       'welcoming_message',
                       '{img_upload:',
                       '{img:',
                       '{youtube:',
                       'msg_limit_is_reached_f',
                       'msg_limit_is_reached_m',
                       'sent_to_block_list',
                       'no_credits_for_msgs_m',
                       'no_credits_for_msgs_f',

                 );
        foreach ($types as $type) {
            if (stristr($msg, $type) !== false) {
                if ($type == '{gift:') {
                    $msg = l('sent_you_a_gift');
                } else if ($type == 'private_photo_request') {
                    $msg = l('private_photo_report');
                } else if ($type == 'private_photo_request_approved') {
                    $msg = l('private_photo_request_approved_notif');
                } else if ($type == 'welcoming_message') {
                    $emailAuto = Common::sendAutomail(Common::getOption('lang_loaded', 'main'), '','welcoming_message', array('name' => guser('name')), false, DB_MAX_INDEX, true);
                    $msg = $emailAuto['text'];
                } else if ($type == '{img_upload:') {
                    if ($typeIm == 'edge') {
                        $image = grabs($msg, '{img_upload:', '}');
                        if (isset($image[0])) {
                            $sql = "SELECT `desc` FROM `gallery_images` WHERE `id` = " . to_sql($image[0]);
                            $desc = DB::result($sql);
                            if (!$desc) {
                                $desc = '';
                            }
                            $msg = Common::getTextTagsToBr($msg, '{img_upload:' . $image[0] . '}', $previewImage) . $desc;
                        }
                    } else {
                        $msg = l('image_sent');
                    }
                } else if ($type == '{img:' || $type == '{youtube:') {
                    if ($type == '{img:') {
                        $images = grabs($msg, '{img:', '}');
                        foreach ($images as $id){
                            /*$image = DB::row("SELECT * FROM outside_image WHERE image_id = " . to_sql($id) . " LIMIT 1");
                            $replace = $previewImage;
                            $imageUrl = Common::urlSiteSubfolders() . '_files/outside_images/' . $image['image_id'] . '_b.jpg';
                            $msg = Common::getTextTagsToBr($msg, $tag, $imageUrl);
                            if (!$image){
                                $replace = '';
                            }*/
                            $msg = Common::getTextTagsToBr($msg, '{img:' . $id . '}', $previewImage);
                        }
                    }
                    $vids = grabs($msg, '{youtube:', '}');
                    foreach ($vids as $id){
                        $urlVideo = 'https://www.youtube.com/watch?v=' . $id;
                        $msg = Common::getTextTagsToBr($msg, '{youtube:' . $id . '}', $urlVideo);
                    }
                } else if ($type == 'msg_limit_is_reached_f' || $type == 'msg_limit_is_reached_m') {
                    $msg = l('msg_limit_is_reached_notif');
                } elseif ($type == 'no_credits_for_msgs_m' || $type == 'no_credits_for_msgs_f') {
                    $msg = str_replace(array('{link_start}', '{link_end}'), '', l($type));
                } else {
                    $msg = l($type);
                }
                break;
            }
        }
        return $msg;
    }

    static function grabsRequest($msg, $msgUserId, $toUserId, $admin = false, $param = '')
    {
        global $g;

        $types = array('private_photo_request',
                       'private_photo_report',
                       'private_photo_request_approved',
                       'private_photo_request_declined',
                       'private_photo_you_granted_access',
                       'sent_to_user_popular_f',
                       'sent_to_user_popular_m',
                       'msg_limit_is_reached_f',
                       'msg_limit_is_reached_m',
                       'sent_to_block_list',
                       'sent_to_block_list_spam',
                       'no_credits_for_msgs_m',
                       'no_credits_for_msgs_f',
                       'welcoming_message',
                       '{img_upload:'
                       );
        $craftedMsg = '';
        if (!empty($msg)) {
            $optionTmplName = Common::getOption('name', 'template_options');
            foreach ($types as $type) {
                $parse = false;
                if ($type == '{img_upload:') {
                    $img = grabs($msg, '{img_upload:', '}');
                    if (isset($img[0])) {
                        $parse = true;
                    }
                } else {
                    $parse = stristr($type, $msg) !== FALSE;
                }
                if($parse) {
                    if ($type == 'private_photo_request') {
                        $attrLink = array('class' => 'photo_grant_access',
                                          'data-user-id' => $msgUserId);
                        $vars = array('url' => '');
                        $craftedMsg = Common::lSetLink($type, $vars, false, '', $attrLink);
                        $attrLink1 = array('class' => 'photo_deny_access',
                                           'data-user-id' => $msgUserId);
                        $craftedMsg = Common::lSetLink($craftedMsg, $vars, false, 1, $attrLink1);
                    } elseif ($type == 'private_photo_request_approved') {
                        $attrLink = array();
                        $vars = array('url' => 'search_results.php?display=profile&uid=' . $msgUserId . '&show=gallery');
                        if ($optionTmplName == 'urban_mobile') {
                            $vars['url'] = 'profile_view.php?user_id=' . $msgUserId;
                        }elseif ($optionTmplName == 'impact_mobile') {
                            $attrLink = array('class' => 'go_to_albums',
                                              'data-layer-loader' => 'true');
                            $vars['url'] = 'profile_view.php?user_id=' . $msgUserId.'&show=albums';
                        }
                        $craftedMsg = Common::lSetLink('private_photo_request_approved', $vars, false, '', $attrLink);
                    } elseif ($type == 'sent_to_user_popular_f' || $type == 'sent_to_user_popular_m') {
                        if ($optionTmplName == 'urban_mobile') {
                            $url = 'profile_view.php?user_id=';
                        } else {
                            $url = 'search_results.php?display=profile&uid=';
                        }
                        $vars = array('name' => User::getInfoBasic($toUserId, 'name', 5),
                                      'url' =>  $url . $toUserId);
                        $craftedMsg = Common::lSetLink($type, $vars);
                        $vars = array('url' => 'upgrade.php');
                        $craftedMsg = Common::lSetLink($craftedMsg, $vars, false, 1);
                    } elseif ($type == 'msg_limit_is_reached_f' || $type == 'msg_limit_is_reached_m') {
                        $vars = array('number' => Common::getOption('sp_sending_messages_per_day_urban'),
                                      'url' => 'upgrade.php');
                        $craftedMsg = Common::lSetLink($type, $vars, false, '', array(), 'l', true);
                    } elseif ($type == 'no_credits_for_msgs_m' || $type == 'no_credits_for_msgs_f') {
                        $attr = array();
                        if ($optionTmplName == 'urban_mobile' || $optionTmplName == 'impact_mobile') {
                            $url = 'upgrade.php?action=refill_credits';
                            if ($optionTmplName == 'impact_mobile') {
                                $url = 'upgrade.php?action=refill_credits&service=message&request_uid=' . $toUserId;
                                $attr = array('class' => 'refill_credits go_to_page',
                                              'data-cl-loader' => 'loader_msg_access');
                            }
                        } elseif($optionTmplName == 'impact' || $optionTmplName == 'edge') {
                            $url = '';
                            $attr = array('class' => 'credits_balans');
                        } else {
                            $url = 'increase_popularity.php';
                        }
                        $vars = array('url' => $url);
                        $craftedMsg = Common::lSetLink($type, $vars, false, '', $attr);
                    } elseif ($type == 'welcoming_message') {
                        $vars = array('name' => User::getInfoBasic($toUserId, 'name'));
                        $emailAuto = Common::sendAutomail(Common::getOption('lang_loaded', 'main'), '','welcoming_message', $vars, false, DB_MAX_INDEX, true);
                        $craftedMsg = $emailAuto['text'];
                    } elseif ($type == 'sent_to_block_list_spam') {
                        $craftedMsg = l('your_account_is_blocked_for_the_suspicios_usage_of_the_messaging');
                    } elseif ($type == '{img_upload:') {
                        $sql = "SELECT i.*, i.id as image_id, i.desc as img_desc, a.folder FROM (gallery_images AS i LEFT JOIN gallery_albums AS a ON i.albumid=a.id) WHERE i.id = " . to_sql($img[0]);
                        $image = DB::row($sql, DB_MAX_INDEX);
                        if ($image) {
                            $urlFiles = $g['path']['url_files'];
                            $imageUrlBig = $urlFiles . 'gallery/images/' . $image['user_id'] . '/' . $image['folder'] . '/' . $image['filename'];

                            /*
                             * Impact width
                             * - one chat 504px;
                             *
                             */
                            $style = '';
                            $tmplImageWidth = Common::getOption('im_send_image_width', 'template_options');
                            if ($tmplImageWidth && !$admin) {

                                $newWidth = $image['width'];
                                $newHeight = $image['height'];

                                if (Common::isOptionActiveTemplate('im_send_image_data_params')) {
                                    $date = Common::dateFormat($image['datetime'], 'photo_date');
                                    $timeAgo = timeAgo($image['datetime'], 'now', 'string', 60, 'second');

                                    $userinfo = User::getInfoBasic($image['user_id']);
                                    $userName = toAttr($userinfo['name']);
                                    $userPhoto = toAttr(User::getPhotoDefault($image['user_id'], 'r'));
                                    $userUrl = toAttr(User::url($image['user_id'], $userinfo));

                                    $style = 'data-width="' . $newWidth . '" data-height="' . $newHeight . '" ' .
                                             'data-date="' . $date . '" data-time-ago="' . $timeAgo . '" ' .
                                             'data-user-name="' . $userName . '" data-user-url="' . $userUrl . '" ' .
                                             'data-user-photo="' . $userPhoto . '" ';
                                } else {
                                    $display = get_param('display');
                                    $maxWidth = $tmplImageWidth['default'];
                                    if (isset($tmplImageWidth[$display])) {
                                        $maxWidth = $tmplImageWidth[$display];
                                    }

                                    if ($newWidth > $maxWidth) {
                                        $ratio = $maxWidth/$newWidth;
                                        $newWidth = $maxWidth;
                                        $newHeight = round($newHeight * $ratio, 1);
                                    }
                                    $style = 'style="width:' . $newWidth . 'px; height:' . $newHeight . 'px;"';
                                }
                            }
                            $description = $param ? $param : $image['desc'];
                            if ($description) {
                                $description = Common::parseLinksSmile($description);
                            }
                            $craftedMsg = '<div class="mod_im_msg_image">'
                                            . '<a target="_blank" id="lightbox_pics_' . $image['image_id'] . '" class="lightbox_pics_im" href="' . $imageUrlBig . '">'
                                                . '<img ' . $style . ' onload="showImageIm(this)" src="' . $imageUrlBig . '"/>'
                                            . '</a>'
                                            . '<div class="mod_im_msg_image_desc">' . $description . '</div>'
                                        . '</div>';
                        } else {
                            $craftedMsg = '<div class="mod_im_msg_image"></div>';
                        }
                    } else {
                        $craftedMsg = l($type);
                    }
                    break;
                }
            }
        }
        return $craftedMsg;
    }

    static function grabsMsg(&$html, $msg, $msgUserId, $toUserId, $isReturnMsg = false, $param = '')
    {
        global $g_user;

        $blockMsg = 'message';
        $gift = grabs($msg, '{gift:', '}');
        $giftCrd = 0;
        $isGiftsDisabled = false;
        if (isset($gift[0])) {
            self::cleanBlockMsg($html, $blockMsg . '_gift');
            $giftInfo = explode(':', $gift[0]);
            $giftId = $giftInfo[0];
            $giftImg = $giftInfo[1];
            if(count($giftInfo)>2 && Common::isTransferCreditsEnabled()){
                $giftCrd = $giftInfo[2];
            }
            $msgGift = DB::result('SELECT `text` FROM `user_gift` WHERE `id` = ' . to_sql($giftId, 'Number'), 0, 3);
            $urlImg = ProfileGift::getUrlImg($giftImg);

            $isGiftsDisabled = Common::isOptionActiveTemplate('gifts_disabled');
            if ($isGiftsDisabled) {
                $msg = trim(str_replace("{gift:{$giftId}:{$giftImg}:{$giftCrd}}", '<img height="20" src=' . $urlImg . " />  {$msgGift}", $msg));
            } else {
                $html->setvar('gift_img_url', $urlImg);

                if($giftCrd>0){
                    $msgGift = trim($msgGift.' + '.lSetVars('credit_balance',array('credit'=>$giftCrd)));
                    $html->setvar('credits', $g_user['credits']);
                    $html->parse($blockMsg . '_set_credits', false);
                }

                if ($msgGift){
                    $html->setvar('gift_text', $msgGift);
                    $html->parse($blockMsg . '_gift_text');
                }
                $html->parse($blockMsg . '_gift', false);
                $html->clean('message_gift_text');
            }
        }
        if (!$gift || $isGiftsDisabled) {
            self::cleanBlockMsg($html, $blockMsg . '_text');
            $requestPrivatePhotoMsg = self::grabsRequest($msg, $msgUserId, $toUserId, false, $param);
            if($requestPrivatePhotoMsg != ''){
                $msg = $requestPrivatePhotoMsg;
            }
            if ($isReturnMsg) {
                return $msg;
            }
            $html->setvar($blockMsg, $msg);
            $html->parse($blockMsg . '_text', false);
        }
        return $msg;
    }

    static function parseImOneMsg(&$html, $row, $js = false, $update = 0, $isFbMode = 'false', $listUsersOpen = array())
    {
        global $g_user, $g;

        if (!empty($row) && is_array($row)) {
            $optionTmplName = Common::getOption('name', 'template_options');
            $typeIm = Common::getOptionTemplate('im_type');
            $cmd = get_param('cmd');
            $userCurrent = get_param_int('user_current');
            $userCurrentGroup = get_param_int('user_current_group');
            $showIm = intval(get_param('show_im', 0));
            $isMyMsg = $row['from_user'] == $g_user['user_id'];
            $isAdmin = $row['to_user'] == $g_user['user_id'] && $row['system'];
            $isUpdateMsgOpenListChats = get_param('display') == 'update_msg_open_list_chats';
            if ($g_user['user_id'] == $row['to_user']) {
                $userTo = $row['from_user'];
            } else {
                $userTo = $row['to_user'];
            }
            if(!$isMyMsg){
                if (!$row['system']) {
                    $row = self::switchOnTranslate($row);
                }
            } else {
                $row['msg_translation'] = '';
            }

            $blockMsg = 'message';
            if ($html->varExists("{$blockMsg}_notif")) {//Edge
                $audioMessageId = isset($row['audio_message_id']) ? $row['audio_message_id'] : 0;
                $html->setvar("{$blockMsg}_notif", self::grabsRequestNotifToAttr($row['msg'], $row['system'], $audioMessageId));
            }
            //$msg = Common::parseLinksTag(to_html($row['msg']), 'a', '&lt;', 'parseLinksSmile');
            if ($html->varExists($blockMsg . '_sticker_bl')) {
                $html->setvar($blockMsg . '_sticker_bl', isCheckStickerText($row['msg']) ? 'sticker_wrap_bl' : '');
            }

            $msg = self::prepareMediaFromComment($row['msg'], $row['from_user']);
            $html->setvar('tit_class', $isMyMsg ? 'blue' : 'green');

            /* Mobile */
            if ($html->varExists($blockMsg . '_whose')) {
                $html->setvar($blockMsg . '_whose', $isMyMsg ? 'right' : 'left');
            }
            /* Mobile */
            $html->setvar($blockMsg . '_send', $row['send']);
            $html->setvar($blockMsg . '_id', $row['id']);


            $html->setvar($blockMsg . '_group_id', $row['from_group_id'] ? $row['from_group_id'] : $row['to_group_id']);
            $html->setvar($blockMsg . '_from_group_id', $row['from_group_id']);
            $html->setvar($blockMsg . '_to_group_id', $row['to_group_id']);

            $html->setvar($blockMsg . '_user_id', $row['from_user']);
            $html->setvar($blockMsg . '_user_name', $row['name']);
            $html->setvar($blockMsg . '_user_profile_link', User::url($row['from_user']));

            $userInfo = User::getInfoBasic($row['from_user'], false, 3);
            $formatName = 'im_datetime';
            $isSetRead = ((!$isMyMsg && $row['is_new'] == 1) || $isAdmin) && $isFbMode == 'false';
            $isSetDate = true;
            if ($optionTmplName == 'urban_mobile') {
                if(self::$isNotificationParsed) {
                    $html->clean($blockMsg . '_new');
                }
                $formatName = self::getFormatDateMobile($row['born']);
                //mark read one since not all at once deduces
                if (self::$isMobileOneChat && !$isMyMsg && $row['is_new'] == 1) {
                //if (self::$isMobileOneChat && $isSetRead) {
                    if(!self::$isNotificationParsed) {
                        $html->setvar($blockMsg . '_notification_text', addslashes(lSetVars('app_notification_text', array('name' => User::nameShort($row['name'])))));
                        $html->parse($blockMsg . '_new', false);
                        self::$isNotificationParsed = true;
                    }
                    if ($isSetRead) {
                        self::setMessageAsReadOneMsg($row['id'], $row['from_user']);
                    }
                }
            } elseif ($optionTmplName == 'impact' || $optionTmplName == 'impact_mobile') {
                $isSetDate = false;
                if (!self::$isMobileOneChat && !self::$isPageListChats && $cmd != 'open_im_with_user') {
                    $g['date_formats']['im_general_impact_part_1'] = 'j M';
                    $g['date_formats']['im_general_impact_part_2'] = 'Y';
                    $html->setvar($blockMsg . '_date_part_1', Common::dateFormat($row['born'], 'im_general_impact_part_1', false));
                    $html->setvar($blockMsg . '_date_part_2', Common::dateFormat($row['born'], 'im_general_impact_part_2', false));
                }else{
                    $html->setvar($blockMsg . '_date', timeAgo($row['born'], 'now', 'string', 60, 'second'));
                    if ($isSetRead) {
                         if ($isUpdateMsgOpenListChats) {
                            if(isset($listUsersOpen[$userTo])&&$listUsersOpen[$userTo]){//read only open im
                                self::setMessageAsReadOneMsg($row['id'], $row['from_user']);
                            }
                        }else{
                            self::setMessageAsReadOneMsg($row['id'], $row['from_user']);
                        }
                    }
                }

                /* Impact общая страница IM*/
                if ($html->varExists('user_from_photo')) {
                    $userRespondentId = $row['from_user'];
                    if ($isMyMsg && !self::$isMobileOneChat) {
                        $userRespondentId = $row['to_user'];
                    }
                    $userRespondent = User::getInfoBasic($userRespondentId, false, 3);
                    $keyPhotoRespondent = 'user_respondent_photo_im_' . $userRespondentId;
                    $keyPhotoPlugPrivatePhoto = 'user_plug_private_photos_' . $userRespondentId;
                    $userPhotoUrl = Cache::get($keyPhotoRespondent);
                    if ($userPhotoUrl === null) {
                        $sizePhoto = 'r';
                        if ($optionTmplName == 'impact_mobile') {
                            $sizePhoto = 'm';
                        }
                        $userPhotoUrl = User::getPhotoDefault($userRespondentId, $sizePhoto, false, $userRespondent['gender']);
                        Cache::add($keyPhotoRespondent, $userPhotoUrl);
                        $userPhotoId = User::getPhotoDefault($userRespondentId, $sizePhoto, true);
                        $isPlugPrivate = User::isVisiblePlugPrivatePhotoFromId($userRespondentId, $userPhotoId);
                        Cache::add($keyPhotoPlugPrivatePhoto, $isPlugPrivate);
                    }
                    $html->setvar('user_from_photo', $userPhotoUrl);
                    $html->setvar('user_from_url', User::url($userRespondentId));
                    if ($html->blockExists('message_plug_private_photos') && !$isMyMsg) {
                        if (Cache::get($keyPhotoPlugPrivatePhoto)) {
                            $html->parse('message_plug_private_photos', false);
                        } else {
                            $html->clean('message_plug_private_photos');
                        }
                    }
                }

                $countNewMsg = 0;
                if ($html->varExists('user_from_new_msg_count')) {
                    $countNewMsg = self::getCountNewMessages($userRespondentId);
                    $html->setvar('user_from_new_msg_count', $countNewMsg);
                }

                if ($html->varExists('user_from_name')) {
                    $name = $userInfo['name'];
                    $titleNewMsgCount = '';
                    if ($isMyMsg) {
                        $name = lSetVars('you_to_msg', array('name' => $userRespondent['name']));
                    } elseif ($countNewMsg > 1){
                        $titleNewMsgCount = lSetVars('general_im_title_count', array('count' => $countNewMsg));
                    }
                    $html->setvar('user_from_title_new_msg_count', $titleNewMsgCount);
                    $html->setvar('user_from_name', $name);
                }
                if ($html->varExists('user_from_one_name')) {
                    $html->setvar('user_from_one_name', $userInfo['name']);
                }
                /*Impact*/
            } elseif (($cmd == 'update_im' && $row['from_user'] == $userCurrent) && $isSetRead) {
                if ($typeIm == 'edge') {
                    $groupIdMsg = $row['from_group_id'] ? $row['from_group_id'] : $row['to_group_id'];
                    if ($userCurrentGroup == $groupIdMsg) {
                        self::setMessageAsReadOneMsg($row['id'], $row['from_user']);
                    }
                } else {
                    self::setMessageAsReadOneMsg($row['id'], $row['from_user']);
                }
            }

            if ($isSetDate) {
                if ($typeIm == 'edge') {
                    $html->setvar($blockMsg . '_date', timeAgo($row['born'], 'now', 'string', 60, 'second'));
                } else {
                    $html->setvar($blockMsg . '_date', Common::dateFormat($row['born'], $formatName, false));
                }
            }

            $isFreeSite = Common::isOptionActive('free_site');
            //$goldDays = User::getInfoBasic($row['from_user'], 'gold_days', 3);

            /* Cache */
            $keyCache = 'is_active_feature_special_delivery';
            $isActiveSpecialDelivery = Cache::get($keyCache);
            if ($isActiveSpecialDelivery === null) {
                $isActiveSpecialDelivery = Common::isActiveFeatureSuperPowers('special_delivery');
                Cache::add($keyCache, $isActiveSpecialDelivery);
            }

            $keyCache = 'is_super_powers_' . $row['from_user'];
            $isSuperPowersFromUser = Cache::get($keyCache);
            if ($isSuperPowersFromUser === null) {
                $isSuperPowersFromUser = User::isSuperPowers($userInfo['gold_days'], $userInfo['orientation']);
                Cache::add($keyCache, $isSuperPowersFromUser);
            }
            /* Cache */

            $isSpecialDelivery = $isFreeSite || (($isActiveSpecialDelivery && $isSuperPowersFromUser)||!$isActiveSpecialDelivery);
            if (IS_DEMO) {
                $isSpecialDelivery = $isSpecialDelivery || in_array($row['from_user'], array(12, 454, 439, 443));
            }
            $html->setvar($blockMsg . '_user_special_delivery', intval($isSpecialDelivery));
            $html->setvar($blockMsg . '_user_is_new', intval($row['is_new']));
            $html->setvar($blockMsg . '_to_user_id', $isMyMsg ? $row['to_user'] : $row['from_user']);
            $html->setvar($blockMsg . '_update', $update);
            // check in the basic version

            if (Common::allowedFeatureSuperPowersFromTemplate('message_read_receipts')) {
                $keyCache = 'is_message_read_receipts_' . $row['from_user'];
                $isMessageReadReceipts = Cache::get($keyCache);
                if ($isMessageReadReceipts === null) {
                    $isMessageReadReceipts = User::accessCheckFeatureSuperPowers('message_read_receipts', $userInfo['gold_days'], $userInfo['orientation']);
                    Cache::add($keyCache, $isMessageReadReceipts);
                }
                if($isMessageReadReceipts) {
                    $html->parse('upgrade_hide', false);
                    if ($row['is_new'] == 0 && $isMyMsg && !$isAdmin) {
                        $html->clean('read_hide');
                    } else {
                        $html->parse('read_hide', false);
                    }
                } else {
                    $html->clean('upgrade_hide');
                    $html->parse('read_hide', false);
                }
            } else {
                if ($row['is_new'] == 0 && $isMyMsg && !$isAdmin) {
                    $html->clean('read');
                } else {
                    $html->parse('read', false);
                }
            }

            /* Audio msg */
            $isExistsAudioMsg = isset($row['audio_message_id']) && $row['audio_message_id'];
            if($isExistsAudioMsg) {
                $html->setvar('message_audio_id', $row['audio_message_id']);
                if ($isMyMsg) {
                    $blockMsgType = '_answer';
                } else {
                    $blockMsgType = '_responder';
                    $html->setvar('message_responder_msg_id', $row['is_new'] ? $row['id'] : 0);
                }
                $audioUrl = ImAudioMessage::getUrl($row['audio_message_id']);
                $html->setvar($blockMsg . $blockMsgType . '_audio_message_file', $audioUrl);
                $html->parse($blockMsg . $blockMsgType . '_audio', false);
                if ($html->blockExists($blockMsg . '_audio')) {
                    $html->setvar($blockMsg . '_audio_message_file', $audioUrl);
                    $html->parse($blockMsg . '_audio', false);
                }
            }
            /* Audio msg */

            /* Mobile */
            if ($html->blockExists($blockMsg . '_time')) {
                $html->parse($blockMsg . '_time', false);
            }
            /* Mobile */

            $notDisplayTitle = array('sent_to_user_popular_f',
                                     'sent_to_user_popular_m',
                                     'msg_limit_is_reached_f',
                                     'msg_limit_is_reached_m',
                                     'sent_to_block_list',
                                     'sent_to_block_list_spam',
                                     'welcoming_message',
                               );
            if ($row['system'] && in_array($msg, $notDisplayTitle)) {
                $html->clean($blockMsg . '_info');
            } else {
                $html->parse($blockMsg . '_info', false);
            }
            // check in the basic version
            // Will integrate
            $isBlockList = 0;
            $isParseTranslation = true;
            if ($row['system'] && $row['system_type'] != 2) {//
                if ($msg == 'sent_to_block_list' || $msg == 'sent_to_block_list_spam') {
                    $isBlockList = 1;
                }
                self::grabsMsg($html, $msg, $row['from_user'], $row['to_user']);
                $isParseTranslation = false;
            } else {
                $html->setvar($blockMsg . '_block_list', 0);
                self::cleanBlockMsg($html, $blockMsg . '_text');

                $msgTemp = $msg;
                if ($row['system_type'] == 2) {
                    $msgTranslation = '';
                    if(!$isMyMsg && trim($row['msg_translation'])){
                        $msgTranslation = $row['msg_translation'];
                    }
                    $msgTemp = self::grabsMsg($html, $msg, $row['from_user'], $row['to_user'], true, $msgTranslation);
                }
                $html->setvar($blockMsg, nl2br($msgTemp));

                if(!$isMyMsg && trim($row['msg_translation']) != '' && Common::isOptionActive('autotranslator_show_original')){
                    if ($row['system_type'] == 2) {
                        $msg_original = self::grabsMsg($html, $msg, $row['from_user'], $row['to_user'], true);
                    } else {
                        $msg_original = self::prepareMediaFromComment($row['msg_translation'], $row['from_user']);
                    }

                    $html->setvar($blockMsg . '_original', nl2br($msg_original));

                    /*$msg_original = Common::parseLinksTag(to_html($row['msg_translation']), 'a', '&lt;', 'parseLinksSmile');
                    $html->setvar($blockMsg.'_original', nl2br($msg_original));*/

                    $html->parse($blockMsg . '_original_text', false);
                } else {
                    $html->clean($blockMsg . '_original_text');
                }

                $html->parse($blockMsg . '_text', false);
            }
            if ($html->varExists($blockMsg . '_block_list')) {
                $html->setvar($blockMsg . '_block_list', $isBlockList);
            }
            // Will integrate

            /*if ($row['is_new'] == 0 && $isMyMsg) {
                $html->clean('read');
            } else {
                $html->parse('read', false);
            }
            $notDisplayTitle = array('sent_to_user_popular_f',
                                     'sent_to_user_popular_m',
                                     'msg_limit_is_reached_f',
                                     'msg_limit_is_reached_m',
                                     'sent_to_block_list',
                                     'sent_to_block_list_spam'
                               );
            if ($row['system'] && in_array($msg, $notDisplayTitle)) {
                $html->clean($blockMsg . '_info');
            } else {
                $html->parse($blockMsg . '_info', false);
            }*/


            if(isset($row['new_credits']) && $row['new_credits']>=0){
                $html->setvar('credits', $row['new_credits']);
                $html->setvar('new_credits_balans', lSetVars('credit_balance', array('credit' => $row['new_credits'])));
                $html->parse($blockMsg . '_set_credits', false);
            }

            if ($js) {
                $html->parse($blockMsg . '_ajax', false);
            }



            /* Imapct */
            if ($isMyMsg) {
                $html->clean($blockMsg. '_responder_report');
                $html->clean($blockMsg. '_responder');
                if(Common::isOptionActive('reports_approval')) {
                    $html->parse($blockMsg. '_answer_report', false);
                }
                $html->parse($blockMsg. '_answer', true);
            } else {
                $blockNewMsg = "{$blockMsg}_to_new";
                if ($html->blockExists($blockNewMsg)) {
                    $html->subcond($row['is_new'], $blockNewMsg);
                }

                $html->clean($blockMsg. '_answer_report');
                $html->clean($blockMsg. '_answer');

                if(Common::isOptionActive('reports_approval')) {
                    $html->parse($blockMsg. '_responder_report', false);
                }
                $html->parse($blockMsg. '_responder', true);
            }

            /* Audio msg */
            if($isExistsAudioMsg) {
                $html->clean($blockMsg . $blockMsgType . '_audio');
            }
            /* Audio msg */

            $html->parse($blockMsg, true);

            /* Audio msg */
            if($isExistsAudioMsg && $html->blockExists($blockMsg . '_audio')) {
                $html->clean($blockMsg . '_audio');
            }
            /* Audio msg */
        }
    }

    static function getWhereAllMessages($toUser, $limit = '', $order = 'ASC', $limitMsgParams = false, $fromGroupId = 0, $toGroupId = 0)
    {
        global $g_user;

        $limitParam = $limit;

        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        $where = self::getWhereNoSysytem();
        if (Common::isOptionActive('gifts_disabled', 'template_options')) {
            //$where = " AND `msg` NOT LIKE '{gift:%'";
        }

        if($limitParam == 1) {

            if($order == 'DESC') {
                $aggregateFunction = 'MAX';
            } else {
                $aggregateFunction = 'MIN';
            }

            $sql = 'SELECT * FROM ' . self::getTable(true) . ' WHERE id = (
                SELECT ' . $aggregateFunction . '(mid) FROM (
                    (SELECT ' . $aggregateFunction . '(id) AS mid FROM `' . self::getTable(true) . '`
                        WHERE `to_user` = ' . to_sql($toUser, 'Number') . '
                            AND `from_user` = ' . to_sql($g_user['user_id'], 'Number') . '
                            AND `from_user_deleted` = 0 ' . self::getWhereGroupMsg($fromGroupId, $toGroupId) . $where . ')
                    UNION
                    (SELECT ' . $aggregateFunction . '(id) AS mid FROM `' . self::getTable(true) . '`
                        WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') . '
                            AND `from_user` = ' . to_sql($toUser, 'Number') . '
                            AND `to_user_deleted` = 0 ' . self::getWhereGroupMsg($fromGroupId, $toGroupId, true) . $where . ')
                ) AS T)';
        } else {

            if($limitMsgParams !== false) {
                $limitAll = $limitMsgParams[0] + $limitMsgParams[1];
            } else {
                $limitAll = $limitParam;
            }

            if($limitAll != '') {
                $limitAll = ' LIMIT ' . $limitAll;
            }

            $sql = '(SELECT * FROM `' . self::getTable(true) . '` USE INDEX (to_user_from_user_from_user_deleted_id)
                        WHERE `to_user` = ' . to_sql($toUser, 'Number') . '
                            AND `from_user` = ' . to_sql($g_user['user_id'], 'Number') . '
                            AND `from_user_deleted` = 0 '. self::getWhereGroupMsg($fromGroupId, $toGroupId) . $where . '
                        ORDER BY id ' . $order . ' ' . $limitAll . ')
                    UNION
                    (SELECT * FROM `' . self::getTable(true) . '` USE INDEX (to_user_from_user_to_user_deleted_id)
                        WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') . '
                            AND `from_user` = ' . to_sql($toUser, 'Number') . '
                            AND `to_user_deleted` = 0 ' . self::getWhereGroupMsg($fromGroupId, $toGroupId, true) . $where . '
                        ORDER BY id ' . $order . ' ' . $limitAll . ')
                    ORDER BY id ' . $order . $limit;
        }

        return $sql;
    }

    static function parseImMessages(&$html, $toUser, $show = true, $limit = '', $order = 'ASC', $userPhotoUrl = '', $limitMsgParams = false, $fromGroupId = 0, $toGroupId = 0)
    {
        global $g_user;

        $firstMsgId = 0;
        $isSetFirstMsgId = self::$isMobileOneChat || self::$isPageListChats;
        if ($isSetFirstMsgId) {
            $firstMsgId = DB::result(self::getWhereAllMessages($toUser, 1, 'ASC', false, $fromGroupId, $toGroupId),0,2);
            $html->setvar('first_msg_id', $firstMsgId);
        }

        $prevMsgUid = 0;
        $sql = self::getWhereAllMessages($toUser, $limit, $order, $limitMsgParams, $fromGroupId, $toGroupId);
        $rows = DB::rows($sql, 2);
        if (self::$isMobileOneChat) {
            $blockStart = 'messages_block_start';
            $blockEnd = 'messages_block_end';
            $i = 0;
            $isMore = true;
        }
        krsort($rows);

        $blockList = 'message_list';
        $blockMsg = 'message';
        $numberMsg = count($rows);
        foreach ($rows as $key => $row) {
            if (self::$isMobileOneChat) {
                if ($row['from_user'] != $g_user['user_id']) {
                    $html->setvar($blockStart . '_from_user', $row['from_user']);
                    $html->setvar($blockStart . '_photo_url', $userPhotoUrl);
                    $html->parse($blockStart . '_photo');
                }
                if ($i == 0) {
                    $html->parse($blockStart);
                } else {
                    if (($prevMsgUid == $g_user['user_id'] && $row['from_user'] != $g_user['user_id'])
                        || ($prevMsgUid != $g_user['user_id'] && $row['from_user'] == $g_user['user_id'])    ) {
                        $html->parse($blockEnd);
                        $html->parse($blockList);
                        $html->clean($blockEnd);
                        $html->parse($blockStart);
                    }
                }
                $prevMsgUid = $row['from_user'];
                $i++;
                $html->parse($blockList);
                $html->clean($blockStart . '_photo');
                $html->clean($blockStart);
                $html->clean($blockEnd);
            }

            /* Impact */
            self::parseResponderInfo($html, $row['from_user'], $prevMsgUid, $row);
            /* Impact */

            self::parseImOneMsg($html, $row);

            if (self::$isMobileOneChat) {
                $html->parse($blockList);
                $html->clean($blockMsg . '_responder');
                $html->clean($blockMsg . '_answer');
                $html->clean($blockMsg);
                if ($i == $numberMsg) {
                    $html->parse($blockEnd);
                    $html->parse($blockList);
                }
                if ($isMore && $firstMsgId == $row['id']) {
                    $isMore = false;
                }
            }
        }

        if (self::$isMobileOneChat) {
            if ($isMore && $firstMsgId) {
                $html->parse('one_chat_profile_pic_hide', false);
            }
        } else {
            if (!$show) {
                $html->parse('message_list_hide', false);
            }
            $html->parse('message_list_start', false);
            $html->parse('message_list_end', false);
            $html->parse($blockList);
            $html->clean($blockMsg);
        }
        return $numberMsg;
    }

    /* Mobile */
    static function getFormatDateMobile($date)
    {
        #2008-12-12 02:02:02

        $yearMsg = substr($date, 0, 4);
        $monthMsg = substr($date, 5, 2);
        $dayMsg = substr($date, 8, 2);

        $curDate = date("Y-m-d");
        $year = substr($curDate, 0, 4);
        $month = substr($curDate, 5, 2);
        $day = substr($curDate, 8, 2);

        $format = 'im_mobile_datetime';
        if ($dayMsg == $day
            && $monthMsg == $month
            && $yearMsg == $year) {
            $format = 'im_mobile_datetime_today';
        } elseif ($monthMsg == $month
                  && $yearMsg == $year) {
            $format = 'im_mobile_datetime_this_month';
        }
        return $format;

    }

    static function isNeededTranslate($toUser)
    {
        global $g_user;
        if(Common::isOptionActive('autotranslator_enabled')){
            $toUser=User::getInfoBasic($toUser);
            if($toUser['lang']!=$g_user['lang']){
                $translatedOff=explode(',',$toUser['translation_off']);
                if(!in_array($g_user['lang'],$translatedOff)){
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return false;
    }

    static function getTranslate($msg='',$toUser=0,$emptyIfNotTranslated=true)
    {
        global $g_user;
        if($emptyIfNotTranslated){
            $trMsg='';
        } else {
            $trMsg=$msg;
        }

        if(self::isNeededTranslate($toUser)){
            $toLang=User::getInfoBasic($toUser,'lang');
            $trMsg=Common::autoTranslate($msg, $g_user['lang'], $toLang);
        }

        return $trMsg;
    }

    static function initListOrderImMobileGeneral($limit)
    {
        $userListNewMsg = array();
        if (self::$usersListMobileGeneralChat === null) {
            self::setCurrentData('IMO.');
            $sql = "SELECT IMO.*, CU.name, CU.name_seo, CU.gender,
                           CU.last_visit, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(CU.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(CU.birth, '00-%m-%d')) AS age
                      FROM `" . self::getTable() . "` AS IMO
                      LEFT JOIN `user` AS CU ON CU.user_id = IMO.to_user
                     WHERE `from_user` = " . to_sql(guid())
                   . ' AND IMO.mid > 0 '
                    . self::$demoWhere
                 . ' ORDER BY is_new_msg DESC, z DESC, mid DESC LIMIT ' . to_sql($limit, 'Plain');
            self::setCurrentData('', true);
            $usersList = DB::all($sql,3);
            //self::$usersListMobileGeneralChat = $usersList;
            self::$usersListMobileGeneralChat = array();
            foreach ($usersList as $key => $user) {
                self::$usersListMobileGeneralChat[$user['to_user']] = $user;
                $userListNewMsg[$user['to_user']] = $user['is_new_msg'];
            }
        }
        return $userListNewMsg;
    }

    static function parseImMobile(&$html, $isPageListChats = null)
    {
        global $g, $g_user;

        self::setCurrentData();

        self::closeEmptyIm();

        $userId = intval(get_param('user_id', 0));
        $cmd = get_param('cmd');
        $display = get_param('display');
        self::$isMobileOneChat = $display == 'one_chat';
        if ($isPageListChats === null) {
            $isPageListChats = $display == 'open_list_chats';
        }
        self::$isPageListChats = $isPageListChats;
        $isInitListChats = $cmd == 'init_list_im';

        $fetchUsers = null;
        $limitStart = get_param('limit_start', 0);
        $limitLoad = 0;

        $limitMsgParams = false;

        if (self::$isMobileOneChat) {
            $optionImHistory = Common::getOption('im_history_messages');
            $html->setvar('is_one_chat', $display);
            $limitMsg = $limitStart . ', ' . $optionImHistory;

            $limitMsgParams = array($limitStart, $optionImHistory);

            $html->setvar('user_to', $userId);
            if ($html->varExists('current_user_photo')) {
                $userPhotoUrl = User::getPhotoDefault($g_user['user_id'], 'r', false, $g_user['gender']);
                $html->setvar('current_user_photo', $userPhotoUrl);
                $html->setvar('current_user_name', $g_user['name']);
            }
        } else {
            $limitLoad = intval(get_param('limit'));//when deleted the chat in the general chat, then loaded one
            $optionImHistory = Common::getOption('im_history_chat');
            if ($limitLoad) {
                $optionImHistory = $limitLoad;
            }
            $limitMsg = 1;
            if ($isInitListChats) {
                $limitMsg = Common::getOption('im_history_messages');
                $initListChatsNumberVisibleIm = get_param_int('number_visible_im');
                if (!$initListChatsNumberVisibleIm) {
                    $initListChatsNumberVisibleIm = 10;
                }
            }
            $html->parse('general', false);

            self::$countMessagesFromUsers = self::getCountNewMessagesFromUsers();
        }
        $html->setvar('limit_start', $limitStart);
        if ($html->varExists('im_history_messages')) {
            $html->setvar('im_history_messages', $optionImHistory);
        }
        if ($html->varExists('im_history_list_messages')) {
            $html->setvar('im_history_list_messages', $limitMsg);
        }

        if (IS_DEMO && !self::$isMobileOneChat) {
            DB::query('SELECT *
                         FROM ' . self::getTable(true) . '
                        WHERE ((to_user = ' . to_sql($g_user['user_id'], 'Number') . ' AND to_user_deleted = 0)
                           OR  (from_user = ' . to_sql($g_user['user_id'], 'Number') . ' AND from_user_deleted = 0))
                          AND id > 0', 2);
            while ($item = DB::fetch_row(2)){
                $userIdOpen = ($g_user['user_id'] == $item['to_user']) ? $item['from_user'] : $item['to_user'];
                self::firstOpenIm($userIdOpen, false);
            }
        }

        $html->setvar('user_id', $g_user['user_id']);
        $html->setvar('user_name', $g_user['name']);

        $isImOpenOneRowOnly = false;

        $where = '`from_user` = ' . to_sql($g_user['user_id'], 'Number');
        $where .= self::getWhereNoGroupIm();
        if ($userId) {
            self::firstOpenIm($userId, self::$isMobileOneChat);
            $where .= ' AND `to_user` = ' . to_sql($userId, 'Number');
            $isImOpenOneRowOnly = true;
        } elseif (self::$isMobileGeneralChatUpdate) {
            $usersList = self::jsonDecodeParamArray('users_list');
            if (!is_array($usersList)) {
                $usersList = array();
            }
            $count = count($usersList);
            $maxI = $count > $optionImHistory ? $count : $optionImHistory;

            $listNewMsgForUsersData = self::initListOrderImMobileGeneral($maxI);

            $usersRemove = $usersList;
            $i = 0;
            //$usersParse = array();
            $usersParseOrder = array();
            $usersListOrder = array();
            $fetchUsers = array();
            $lastId = get_param('last_id');
            foreach (self::$usersListMobileGeneralChat as $uid => $user) {
                /*if (($count && $i == $count)
                     || (!$count && $i == $optionImHistory)) {
                    break;
                }*/
                if ($i == $maxI) {
                    break;
                }
                if (!isset($usersList[$uid])) {
                    //$usersParse[] = $uid;
                    $usersParseOrder[$uid] = $i;
                    $fetchUsers[] = $user;
                } else {
                    //echo self::$usersListMobileGeneralChat[$uid]['mid'].'-'. $lastId;
                    if ($user['mid'] > $lastId) {
                        $usersParseOrder[$uid] = $i;
                        $fetchUsers[] = $user;
                    }
                    unset($usersRemove[$uid]);
                }
                $usersListOrder[$uid] = $i;
                $i++;
            }
            if ($usersRemove) {
                $html->setvar('general_remove_users_list', json_encode($usersRemove));
                $html->parse('general_remove_users', false);
            }
            if ($usersListOrder) {
                $listNewMsgForUsers = array();
                $numberNewMsgForUsers = array();
                $isUpdateNewMsgNumber = $html->varExists('general_users_list_new_msg_count');
                foreach ($usersListOrder as $uid => $value) {
                    $listNewMsgForUsers[$uid] = $listNewMsgForUsersData[$uid];
                    if ($isUpdateNewMsgNumber) {
                        $numberNewMsgForUsers[$uid] = self::getCountNewMessages($uid);
                    }
                }
                if ($isUpdateNewMsgNumber) {
                    $html->setvar('general_users_list_new_msg_count', json_encode($numberNewMsgForUsers));
                }
                $html->setvar('general_users_list_new_msg', json_encode($listNewMsgForUsers));
                $html->setvar('general_order_users_list', json_encode($usersListOrder));
                $html->parse('general_order_users', false);
            }

            $usersOpenIm = self::jsonDecodeParamArray('users_list_open_im');
            if ($usersOpenIm) {
                self::setStatusUsers($html, $usersOpenIm, true);

                $existsImOpen = array();
                $sqlExists = 'SELECT `to_user` FROM `' . self::getTable() . '`
                               WHERE `from_user` = ' . to_sql($g_user['user_id'], 'Number')
                             . ' AND `to_user` IN(' . self::getSqlImplodeKeys($usersOpenIm)  . ')'
                             . ' AND mid > 0 ' . self::$demoWhere;
                $existsIm = DB::rows($sqlExists);
                if ($existsIm) {
                    foreach ($existsIm as $key => $item) {
                        $existsImOpen[$item['to_user']] = 1;
                    }
                }
                $html->setvar('update_exists_im_value', json_encode($existsImOpen));
                $html->parse('update_exists_im', false);
            } else {
                self::setStatusUsers($html);
            }
            self::parseReadMessage($html, 'general_show_read_marks');
            if (!$fetchUsers) {
                return;
            }
        }

        self::setCurrentData('IMO.');
        $sql = "SELECT IMO.*, CU.name, CU.name_seo, CU.gender,
                       CU.last_visit, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(CU.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(CU.birth, '00-%m-%d')) AS age
                  FROM `" . self::getTable() . "` AS IMO
                  LEFT JOIN `user` AS CU ON CU.user_id = IMO.to_user
                 WHERE " . $where
               . ' AND IMO.mid > 0 '
               . self::$demoWhere;
        self::setCurrentData('', true);

        $order = ' ORDER BY IMO.is_new_msg DESC, IMO.z DESC, IMO.mid DESC';
        if ($isInitListChats) {
            $sqlInitList = 'SELECT * FROM (
                            (' . $sql . $order . ' LIMIT 0,' . $optionImHistory . ')
                            UNION
                            (' . $sql  . ' AND IMO.im_open_visible != "C" ' . $order . ')) AS IMOU
                            GROUP BY to_user ORDER BY is_new_msg DESC, z DESC, mid DESC';
        }

        if(!$isImOpenOneRowOnly) {
            $sql .= $order;
        }

        if (!self::$isMobileOneChat && !$userId && !self::$isMobileGeneralChatUpdate) {
            $limitOpenChat = ' LIMIT ' . $limitStart . ',' . $optionImHistory;
            $thereIm = DB::count(self::getTable(), $where . ' AND mid > 0 ' . self::$demoWhere);
            $html->setvar('stop_more', $thereIm > ($limitStart + $optionImHistory) ? 0 : 1);
            $html->setvar('stop_more', $thereIm ? 0 : 1);
            $html->setvar('limit_load', $limitLoad);
            $html->parse('general_limit_info');
            if ($isInitListChats) {
                $sql = $sqlInitList;
            } else {
                $sql .= $limitOpenChat;
            }
        }

        if ($fetchUsers === null) {
            $fetchUsers = DB::all($sql, 1);
        }

        $blockListUsers = '';
        $blockListUsersItem = 'messages_users_list_item';
        if ($isPageListChats) {
            $blockListUsers = 'list_chats_open_users';
            $blockListUsersItem = 'list_chats_open_users_item';
        }

        $html->setvar('user_to_photo', 'empty.gif');
        $blockNewMessages = 'new_messages_item';
        $i = 0;
        $j = 0;

        //print_r_pre($fetchUsers,true);
        foreach ($fetchUsers as $k => $row) {

            if (self::$isMobileGeneralChatUpdate) {
                $html->setvar('user_to_order', $usersParseOrder[$row['to_user']]);
            }

            //$userInfo = User::getInfoBasic($row['to_user']);
            $userInfo = array(
                'name'       => $row['name'],
                'name_seo'   => $row['name_seo'],
                'gender'     => $row['gender'],
                'age'        => $row['age'],
                'last_visit' => $row['last_visit']
            );
            $html->setvar('user_to_id', $row['to_user']);
            $userPhotoUrl = '';
            $userUrl = '';
            if (!$isInitListChats) {
                $userUrl = User::url($row['to_user'], $userInfo);
                if ($html->varExists('user_to_profile_link')) {
                    $html->setvar('user_to_profile_link', $userUrl);
                }
                if ($html->varExists('user_to_photo') || $html->blockExists("{$blockListUsersItem}_plug_private_photos")) {
                    $sizePhoto = self::$isMobileOneChat ? 'm' : 'r';
                    $userPhotoUrl = User::getPhotoDefault($row['to_user'], $sizePhoto, false, $userInfo['gender']);
                    if ($html->blockExists("{$blockListUsersItem}_plug_private_photos")) {// != $isInitListChats
                        $userPhotoId = User::getPhotoDefault($row['to_user'], $sizePhoto, true);
                        if (User::isVisiblePlugPrivatePhotoFromId($row['to_user'], $userPhotoId)){
                            $html->parse("{$blockListUsersItem}_plug_private_photos", false);
                        } else {
                            $html->clean("{$blockListUsersItem}_plug_private_photos");
                        }
                    }

                    $html->setvar('user_to_name', $userInfo['name']);
                    $html->setvar('user_to_photo', $userPhotoUrl);
                    if ($html->varExists('user_to_age')) {
                        $html->setvar('user_to_age', $userInfo['age']);
                    }
                    if ($html->varExists('user_to_profile_url')) {
                        $html->setvar('user_to_profile_url',  $userUrl);
                    }
                }
            }
            if($isInitListChats) {//Impact Init List Load Page
                $html->setvar('user_to_name', $userInfo['name']);

                self::parseStatusOnline($html, $row['to_user'], $userInfo);

                if ($row['is_new_msg']) {
                    $html->parse($blockNewMessages, true);
                }
                if ($row['im_open_visible'] != 'C') {
                    $html->setvar('user_to_age', $userInfo['age']);
                    if ($j < $initListChatsNumberVisibleIm && $row['im_open_visible'] == 'Y') {
                        self::setMessageAsRead($row['to_user'], false);
                    }
                    self::parseOpenImTitle($html, $row['to_user'], $userInfo['name']);
                    if ($j < $initListChatsNumberVisibleIm) {
                        $userUrl = User::url($row['to_user'], $userInfo);
                        $html->setvar('user_to_profile_link', $userUrl);

                        $userPhotoUrl = User::getPhotoDefault($row['to_user'], 'r', false, $userInfo['gender']);
                        $html->setvar('user_to_photo', $userPhotoUrl);
                        self::parseImMessages($html, $row['to_user'], true, $limitMsg, 'DESC', $userPhotoUrl, $limitMsgParams);

                        $isVisibleOpenImOne = $row['im_open_visible'] == 'Y';
                        $isVisibleOpenImOneDemo = intval(get_cookie('im_open_visible_demo'));
                        if (defined('IS_DEMO') && IS_DEMO && !$isVisibleOpenImOneDemo) {
                            $isVisibleOpenImOne = true;
                            set_cookie('im_open_visible_demo', true);
                        }
                        if($isVisibleOpenImOne){
                            $html->parse('open_im_show', false);
                        }else{
                            $html->clean('open_im_show', false);
                        }
                        $html->parse('open_im', true);
                    }else{
                        $html->parse('list_chats_open_item_more', true);
                    }
                    $html->clean('message');
                    $html->clean('message_list');
                    $j++;
                }
                if ($i < $optionImHistory) {
                    if (!$userPhotoUrl) {
                        $userPhotoUrl = User::getPhotoDefault($row['to_user'], 'r', false, $userInfo['gender']);
                        $html->setvar('user_to_photo', $userPhotoUrl);
                    }
                    if (!$userUrl) {
                        $userUrl = User::url($row['to_user'], $userInfo);
                        $html->setvar('user_to_profile_link', $userUrl);
                    }
                    $html->parse($blockListUsersItem);
                    $html->clean($blockNewMessages);
                }
            }else{
                $isMsg = false;
                if (!$isPageListChats) {
                    if (!$userPhotoUrl) {
                        $userPhotoUrl = User::getPhotoDefault($row['to_user'], 'r', false, $userInfo['gender']);
                    }
                    $isMsg = self::parseImMessages($html, $row['to_user'], true, $limitMsg, 'DESC', $userPhotoUrl, $limitMsgParams);
                }
                $isParseIm = true;
                if (self::$isMobileOneChat) {
                    if (!$isMsg) {
                        $isParseIm = false;
                    }
                } else {
                    self::parseStatusOnline($html, $row['to_user'], $userInfo);
                    if ($row['is_new_msg']) {
                        $html->parse($blockNewMessages, true);
                    }
                }
                if ($isParseIm) {
                    $html->parse($blockListUsersItem);
                    $html->clean($blockNewMessages);
                    $html->clean('message_list');
                }
            }
            $i++;
        }
        if (self::$isMobileOneChat) {
            //$html->setvar('first_msg_id', $firstMsgId);
            $html->parse('set_data_one_chat', false);
        } else {
            $html->parse('one_chat_profile_pic_hide', false);
            $html->parse('set_general_chat', false);
        }
        $html->setvar('last_id', self::lastId());
        if ($isPageListChats) {
            $isOpen = intval(get_cookie('open_list_chats', true));
            $isOpenDemo = intval(get_cookie('open_list_chats_demo', true));
            if (defined('IS_DEMO') && IS_DEMO && !$isOpenDemo) {
                $isOpen = true;
            }
            if ($isOpen) {
                $html->parse($blockListUsers . '_show', false);
            }
            $countNewMessages = CIm::getCountNewMessages();
            $titleOnlineCount = l('popup_messages_list_title_empty');
            if ($countNewMessages) {
                $titleOnlineCount = lSetVars('popup_messages_list_title', array('count' => $countNewMessages));
            }
            $html->setvar($blockListUsers . '_count_value', $countNewMessages);
            $html->setvar($blockListUsers . '_count', $titleOnlineCount);
            $html->parse($blockListUsers, false);
            if ($isInitListChats) {
                $html->setvar('open_im_all_new_msg_count', self::getCountNewMessagesFromListUsers());
            }
        }
    }
    /* Mobile */
    /* Impact list small popup IM */
    static function parseOpenImTitle(&$html, $uid, $name = null, $countNewMsg = null)
    {
        if ($name === null) {
            $name = User::getInfoBasic($uid, 'name');
        }
        if ($countNewMsg === null) {
            $countNewMsg = self::getCountNewMessages($uid);
        }
        if ($countNewMsg) {
            $vars =  array('name' => $name, 'count' => $countNewMsg);
            $title = lSetVars('open_im_title_count', $vars);
        } else {
            $vars =  array('name' => $name);
            $title = lSetVars('open_im_title', $vars);
        }
        $html->setvar('open_im_new_msg_count', $countNewMsg);
        $html->setvar('open_im_title', $title);
    }

    static function parseResponderInfo(&$html, $fromUid, &$prevMsgUid, $row)
    {
        $blockMsg = 'message';
        $blockResponderInfo = $blockMsg . '_responder_info';
        if ($html->blockExists($blockResponderInfo)) {
            $blockResponderInfo = $blockMsg . '_responder_info';
            $blockAnswerInfo = $blockMsg . '_answer_info';
            $html->clean($blockResponderInfo . '_arrow');
            $html->clean($blockResponderInfo);
            $html->clean($blockAnswerInfo . '_arrow');
            $html->clean($blockAnswerInfo);

            $blockParse = $fromUid == guid() ? $blockAnswerInfo : $blockResponderInfo;
            if ($prevMsgUid != $fromUid) {

                $groupId = 0;
                if (Common::isOptionActiveTemplate('groups_social_enabled')) {
                    if ($fromUid == $row['from_user'] && $row['from_group_id']) {
                        $groupId = $row['from_group_id'];
                    } elseif($fromUid == $row['to_user'] && $row['to_group_id']){
                        $groupId = $row['to_group_id'];
                    }
                }
                if ($groupId) {
                    $groupInfo = Groups::getInfoBasic($groupId);
                    $userName = $groupInfo['title'];
                    $userPhoto = GroupsPhoto::getPhotoDefault($fromUid, $groupId, 'm');
                    $userAge = '';
                    $userLink = Groups::url($groupId, $groupInfo);
                } else {
                    $userInfo = User::getInfoBasic($fromUid);
                    $userName = User::nameOneLetterFull($userInfo['name']);
                    $userAge = $userInfo['age'];
                    $userLink = User::url($fromUid, $userInfo);
                    $userPhoto = User::getPhotoDefault($fromUid, 'm', false, $userInfo['gender']);
                }

                $html->setvar($blockParse . '_user_id', $fromUid);
                $html->setvar($blockParse . '_photo_url', $userPhoto);
                $html->setvar($blockParse . '_name', $userName);
                $html->setvar($blockParse . '_age', $userAge);
                $html->setvar($blockParse . '_profile_url', $userLink);

                $html->parse($blockParse);
                $html->parse($blockParse . '_arrow');
            }

            $prevMsgUid = $fromUid;
            $html->clean($blockMsg . '_responder');
            $html->clean($blockMsg . '_answer');
        }
    }
    /* Impact list small popup IM */

    function parseStatusActive(&$html, $groupId) 
    {
        if($groupId) {
            $html->parse('status_group_active', false);
            $html->clean('status_all_active', false);
            $html->parse('show_only_group', false);
            $html->parse('show_only_group_button', false);
        } else {
            $html->parse('status_all_active', false);
            $html->clean('status_group_active', false);
            $html->parse('show_only_all', false);
        }
    }

    function parseBlock(&$html)
    {

        global $g, $g_user;

        $guid = $g_user['user_id'];
        $cmd = get_param('cmd');
        $display = get_param('display');
        self::$isMobileOneChat = $display == 'one_chat';
        self::$isMobileGeneralChatUpdate = ($display == 'general_chat') && $cmd == 'update_im';
        self::$isPageListChats = $display == 'open_list_chats'  && ($cmd == 'update_im' || $cmd == 'uploading_msg');
        self::setCurrentData();

        $allowedCmd = array('pp_messages','uploading_msg','open_im_with_user');
        //'open_im_with_user' - Impact


        if ($guid && in_array($cmd, $allowedCmd)) {

            self::closeEmptyIm();

            $userId = intval(get_param('user_id'));
            $showIm = intval(get_param('show_im'));
            $uploadIm = intval(get_param('upload_im'));
            $notUploadingMsg = $cmd != 'uploading_msg';
            $isOpenImImpact = $cmd == 'open_im_with_user';
            $typeIm = Common::getOptionTemplate('im_type');

            $groupId = self::getGroupId();
            $fromGroupId = 0;
            $toGroupId = 0;
            $isGroupsSocial = Common::isOptionActiveTemplate('groups_social_enabled');
            $isFbMode = get_param('is_mode_fb');

            if ($groupId){
                $fromGroupId = get_param_int('from_group_id');
                $toGroupId = get_param_int('to_group_id');
            }

            // if($userId && !$fromGroupId && $toGroupId && $groupId) {
            //     $group = Groups::getInfoBasic($groupId);
            //     if($group) {
            //         if($group['user_id'] == guid()) {
            //             return false;
            //         }
            //     }
            // }

            $where = '';
            if (!$isGroupsSocial) {
                $where = self::getWhereNoGroupIm('IMO.');
            }

            $sub_users = [];
            if($groupId && $userId != '100000001' && $isFbMode != 'true')
            {
                $groupId = self::getGroupId();
                $groupInfo = Groups::getInfoBasic($groupId);
                if($groupInfo) {
                     $subscribers_sql = "SELECT * FROM `user` as u LEFT JOIN `groups_social_subscribers` gs ON u.user_id = gs.user_id WHERE gs.group_id=".to_sql($groupId) . " AND u.user_id != " . to_sql(guid(), 'Text')  . " AND u.user_id != " . to_sql($groupInfo['user_id']);

                    $sub_users = DB::rows($subscribers_sql);
                    
                    foreach ($sub_users as $key => $value) {
                        self::firstOpenImGroup($value['user_id']);
                    }
                }
            }

            if ($userId) {
                if (!$showIm) {
                    
                    self::setMessageAsRead($userId, false);
                }

                self::firstOpenIm($userId, !$showIm, $isOpenImImpact);
            }

            $isShowUserImInfo = true;
            if ($showIm) {
                $isShowUserImInfo = false;
            }

            if (IS_DEMO && $notUploadingMsg && (!$userId || ($userId && $showIm))) {
                if (!$userId) {
                    DB::query('SELECT *
                                 FROM ' . self::getTable(true) . '
                                WHERE ((to_user = ' . to_sql($g_user['user_id'], 'Number') . ' AND to_user_deleted = 0)
                                   OR (from_user = ' . to_sql($g_user['user_id'], 'Number') . ' AND from_user_deleted = 0))
                                  AND id > 0', 4);
                    while ($row2 = DB::fetch_row(4)){
                        $userIdOpen = ($g_user['user_id'] == $row2['to_user']) ? $row2['from_user'] : $row2['to_user'];
                        self::firstOpenIm($userIdOpen, false);
                    }
                } else {
                    self::firstOpenIm($userId, false);
                }
            }

            if ($isOpenImImpact && $userId) {
                self::setVisibleOpenIm($userId);
            }

            $html->setvar('user_id', $g_user['user_id']);
            $html->setvar('user_name', $g_user['name']);

            $html->cond($g_user['sound'] == 2, 'no_sound', 'is_sound');

            if ($showIm || !$notUploadingMsg || $uploadIm || $isOpenImImpact) {
                $where .= ' AND IMO.to_user = ' . to_sql($userId, 'Number');
                if ($groupId) {
                    $where .= ' AND (IMO.from_group_id = ' . to_sql($fromGroupId) . ' AND IMO.to_group_id = ' . to_sql($toGroupId) . ')';
                } else {
                    $where .= ' AND (IMO.from_group_id = 0 AND IMO.to_group_id = 0)';
                }
            }

            if($groupId && $userId == '100000001') {
                $where .= ' AND ((IMO.group_id = 0) OR IMO.group_id = ' . to_sql($groupId, 'Text') . ')';
            } elseif ($groupId) {
                $where .= ' AND ((IMO.group_id = 0) OR (IMO.group_id >0 AND IMO.group_id=' . to_sql($groupId, 'Text') . ')) ';
                
                // group_social subscribers and $group_leader
            } else {
                $where .= ' AND ((IMO.group_id >0 AND IMO.group_id != IMO.from_group_id AND IMO.group_id = IMO.to_group_id) OR IMO.group_id = 0) ';
            }

            $currentUser = get_param('user_current');
            $isFbModeTitle = get_param('is_mode_fb');
            $optionImHistory = Common::getOption('im_history_messages','options');
            $limitStart = get_param('limit_start', 0);
            $limitMsg = $limitStart . ', ' . $optionImHistory;
            $html->setvar('limit_start', $limitStart);

            $limitMsgParams = array($limitStart, $optionImHistory);


            self::setCurrentData('IMO.');
      
            $joinSql = '';
            $selectSql = '';
            if ($typeIm == 'edge') {
                $joinSql = 'LEFT JOIN `' . self::getTable(true) . '` AS IMM '
                         . ' ON IMM.id = IMO.mid  AND IMM.to_user = IMO.to_user '
                         . ' AND ((IMM.from_user_deleted = 0 AND IMM.from_user = ' . to_sql($guid) . ') OR (IMM.to_user_deleted = 0 AND IMM.to_user = ' . to_sql($guid) . ')) '
                         . ' AND (IMO.group_id = IMM.group_id)'
                         . self::getWhereNoSysytem('IMM.');

                        // . ' AND ((IMO.from_group_id = IMM.to_group_id OR IMO.to_group_id = IMM.from_group_id) AND IMO.group_id = IMM.group_id)'
                $selectSql = ', IF(IMM.msg IS NULL, "", IMM.msg) AS last_msg, IMM.from_user AS last_msg_from_user, IMM.to_user AS last_msg_to_user, IMM.system AS last_msg_system';

                if(ImAudioMessage::isActive()) {
                    $selectSql .= ', IMM.audio_message_id AS audio_message_id';
                }
            }
            $sql = "SELECT IMO.*, CU.name, CU.name_seo, CU.gender, CU.city,
                           CU.last_visit, CU.users_reports,
                           DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(CU.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(CU.birth, '00-%m-%d')) AS age " .
                      $selectSql .
                    ' FROM `' . self::getTable() . '` AS IMO
                      LEFT JOIN `user` AS CU ON CU.user_id = IMO.to_user ' .
                      $joinSql .
                   ' WHERE IMO.from_user = ' . to_sql($guid) .
                     ' AND IMO.mid > 0 '
                    . $where
                    . self::$demoWhere .
                   ' ORDER BY IMO.z DESC';
            self::setCurrentData('', true);
            DB::query($sql, 1);

            // var_dump($sql); die();

            self::$isReadMsg = false;
            if (!$userId
                || ($userId && !$currentUser)
                || ($currentUser == $userId && $isFbModeTitle == 'false')) {
            }

            self::parseStatusActive($html, $groupId);

            $countMsgNewAll = 0;
            $isParseMessagesLastActiveIm = true;

            while ($row = DB::fetch_row(1))
            {
                $userInfo = $row;//User::getInfoBasic($row['to_user']);
                if ($html->varExists('first_msg_id')) {
                    $firstMsgId = DB::result(self::getWhereAllMessages($row['to_user'], 1, 'ASC', false, $row['from_group_id'], $row['to_group_id']),0,2);
                    $html->setvar('first_msg_id', $firstMsgId);
                }
                if ($notUploadingMsg && !$isOpenImImpact) {
                    self::parseInfoUserToIm($html, $row['to_user'], $userInfo);
                }
                if ($isParseMessagesLastActiveIm) {
                    if ($notUploadingMsg) {
                        self::parseTitleIm($html, $row['to_user'], $isShowUserImInfo, $userInfo);
                        if (!$showIm) {
                            $groupIdIm = 0;
                            $fromGroupIdIm = 0;
                            $toGroupIdIm = 0;
                            if ($isGroupsSocial) {
                                $groupIdIm = $row['from_group_id'] || $row['to_group_id'];
                                if ($groupIdIm) {
                                    $groupIdIm = $row['from_group_id'] ? $row['from_group_id'] : $row['to_group_id'];
                                    $fromGroupIdIm = $row['from_group_id'];
                                    $toGroupIdIm = $row['to_group_id'];
                                }
                            }
                            self::setMessageAsRead($row['to_user'], false, $groupIdIm, $fromGroupIdIm, $toGroupIdIm);
                        }
                    }
                    self::parseImMessages($html, $row['to_user'], $isShowUserImInfo, $limitMsg, 'DESC', '', $limitMsgParams, $row['from_group_id'], $row['to_group_id']);
                    $isParseMessagesLastActiveIm = false;
                }

                self::parseStatusGroup($html, $row, $sub_users);

                if ($notUploadingMsg) {
                    $countMsgNew = self::getCountNewMessages($row['to_user']);
                    $countMsgNewAll += $countMsgNew;
                    $blockCountNewHide = 'count_new_hide';
                    $blockCountNewShow = 'count_new_show';
                    $blockSelectedUser = 'selected_user';
                    //'upload_im_new' - so that the counters of new messages are parsed
                    if ($isShowUserImInfo && !get_param_int('upload_im_new')) {
                        $isShowUserImInfo = false;
                        $html->parse($blockSelectedUser);
                        $html->parse($blockCountNewHide);
                        } else {
                        $html->setvar('messages_count_new', $countMsgNew);
                        if ($countMsgNew) {
                            $html->parse($blockCountNewShow, false);
                            $html->clean($blockCountNewHide);
                        } else {
                            $html->parse($blockCountNewHide, false);
                            $html->clean($blockCountNewShow);
                        }
                        $html->clean($blockSelectedUser);
                    }
                    $html->parse('list_users');
                }
                if ($html->varExists('user_to_profile_url')) {
                    $html->setvar('user_to_profile_url',  User::url($row['to_user'], $userInfo));
                }

                if ($isOpenImImpact && $userId) {
                    self::parseStatusOnline($html, $row['to_user'], $userInfo);
                    self::parseOpenImTitle($html, $row['to_user']);
                    $html->setvar('open_im_all_new_msg_count', self::getCountNewMessagesFromListUsers());
                    $html->parse('open_im_js');
                    $html->parse('open_im');
                }
            }

            if ($countMsgNewAll) {
                $html->setvar('messages_count', $countMsgNewAll);
            }
            if ($html->varExists('messages_count_data')) {
                $dataMessageCount = array('count' => self::getCountNewMessages(), 'enabled' => self::getCountAllMsgIm());
                $html->setvar('messages_count_data', json_encode($dataMessageCount));
            }
            $html->setvar('last_id', self::lastId());
            if ($notUploadingMsg && !$showIm) {

                if (Common::isOptionActive('contact_blocking')) {
                    $html->parse('user_blocking');
                }
                if(Common::isOptionActive('reports_approval')) {
                    $html->parse('user_report', false);
                }
                if (City::isActiveStreetChat()) {
                    $html->parse('user_invite_streetchat');
                }
                if (Common::isOptionActive('videochat')) {
                    $html->parse('user_invite_videochat');
                }
                if (Common::isOptionActive('audiochat')) {
                    $html->parse('user_invite_audiochat');
                }
                if (Common::isOptionActive('calendar_enabled', 'edge_events_settings')) {
                    $html->parse('user_menu_calendar');
                }
                if ($isShowUserImInfo) {
                    $html->setvar('current_user_to_id', 0);
                } else {
                    $html->parse('message_list_empty_hide');
                }
                                

                //if (Common::isOptionActive('im_audio_messages')) {
                    //$html->setvar('im_audio_message_enabled', 'im_audio_message_enabled');
                    //$html->parse('im_audio_message_recorder_control');
                //}

                if(Common::isAppIos() && ImAudioMessage::isActive()) {
                    $html->setvar('app_ios_auth_key', User::urlAddAutologin('', $g_user));
                    $html->setvar('im_audio_message_enabled', 'im_audio_message_enabled');
                    $html->parse('app_ios_im_audio_message_recorder');
                }

                $html->parse('message_list_empty');
            }
        } elseif ($guid && (self::$isMobileGeneralChatUpdate || self::$isPageListChats)) {
            if (self::$isPageListChats) {
                self::$isMobileGeneralChatUpdate=true;
            }
            self::parseImMobile($html, self::$isPageListChats);
        } elseif ($guid && $cmd == 'update_im') {
            self::$isPageListChats = get_param('display') == 'update_msg_open_list_chats';
            self::updateMessagesLast($html);
        } elseif ($guid && $cmd == 'send_message') {
            $userTo = get_param('user_to', 0);
            $from_group_id = get_param('from_group_id', '');
            
            if($userTo == '100000001' && $from_group_id) {
                
                $rows = Groups::getListSubscribers($from_group_id);

                foreach ($rows as $key => $value) {
                    if($value['user_id'] == guid()) {
                        continue;
                    }
                    self::addMessage($html, $value['user_id']);
                }
            } 
                self::addMessage($html);
            //sleep(20);
        } elseif ($guid && $cmd == 'init_list_im') {//impact init list chat load page
            self::parseImMobile($html, true);
        }

        parent::parseBlock($html);
    }

    static function switchOnTranslate($msg)
    {
        if(trim($msg['msg_translation'])!=''){
            $tmp=$msg['msg'];
            $msg['msg']=$msg['msg_translation'];
            $msg['msg_translation']=$tmp;
        }
        return $msg;
    }

    static function replyOnNewContactRate($user)
    {
        return isset($user['im_reply_new_contact_rate']) ? $user['im_reply_new_contact_rate'] : 0;
    }

    static function replyOnNewContactRateLevel($user)
    {
        $levels = array(
            30 => 'medium',
            70 => 'high',
        );

        $rate = self::replyOnNewContactRate($user);

        $rateColor = 'low';

        foreach($levels as $level => $levelColor) {
            if($rate > $level) {
                $rateColor = $levelColor;
            }
        }

        return $rateColor;
    }

    static function markContactAsReplied($userId, $userTo)
    {
        DB::update('im_contact_replied', array('replied' => 1), 'user_id = ' . to_sql($userId) . ' AND user_to = ' . to_sql($userTo));
        self::updateUserReplyRate($userId);
    }

    static function addContactReplyItem($userId, $userTo)
    {
        if($userId == $userTo || !$userId || !$userTo || guser('welcoming_message_sender')) {
            return;
        }
        $sql = 'INSERT IGNORE INTO im_contact_replied
            SET user_id = ' . to_sql($userId) . ', user_to = ' . to_sql($userTo);

        DB::execute($sql);
        $isAdded = DB::affected_rows();

        if($isAdded) {
            self::updateUserReplyRate($userId);
        }
    }

    static function isContactReplied($userId, $userTo)
    {
        $sql = 'SELECT replied FROM im_contact_replied
            WHERE user_id = ' . to_sql($userId) . '
                AND user_to = ' . to_sql($userTo);
        return DB::result($sql);
    }

    static function isContactReplyItemExists($userId, $userTo)
    {
        $sql = 'SELECT user_id FROM im_contact_replied
            WHERE user_id = ' . to_sql($userId) . '
                AND user_to = ' . to_sql($userTo);
        return DB::result($sql);
    }

    static function calculateContactReplyRate($uid)
    {
        $sql = 'SELECT COUNT(*) FROM im_contact_replied
            WHERE user_id = ' . to_sql($uid);
        $contactsCount = DB::result($sql);

        $sql = 'SELECT COUNT(*) FROM im_contact_replied
            WHERE user_id = ' . to_sql($uid) . ' AND replied = 1';
        $contactsRepliedCount = DB::result($sql);

        $rate = 0;

        if($contactsCount) {
            $rate = intval(100 * $contactsRepliedCount / $contactsCount);
        } else {
            $rate = 100;
        }

        return $rate;
    }

    static function updateUserReplyRate($uid)
    {
        $rate = self::calculateContactReplyRate($uid);
        User::update(array('im_reply_new_contact_rate' => $rate), $uid);
    }

    static public function jsonDecodeParamArray($param = null, $data = null)
    {
        if ($data === null && $param !== null) {
            $data = get_param($param);
        }
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (!is_array($data)) {
            $data = array();
        }
        return $data;
    }

    static public function getSqlImplodeKeys($data)
    {
        //return to_sql(implode(',', array_keys($data)), 'Plain');
        $result = '';
        $keys = array_keys($data);
        $delimiter = '';
        if($keys) {
            foreach($keys as $key) {
                $result .= $delimiter . to_sql($key);
                $delimiter = ',';
            }
        }
        return $result;
    }

    static function isBanSendMsg($msg)
    {
        global $g_user;

        if ($g_user['use_as_online']) {
            return false;
        }

        if (User::isBanMailsIp()){
            return true;
        }

        $minLength = Common::getOptionInt('auto_ban_messages_min_length');
        if ($minLength && mb_strlen($msg, 'UTF-8') < $minLength) {
            return false;
        }

        $isBan = false;
        $guid = guid();

        $textHash = md5(mb_strtolower($msg, 'UTF-8'));

        $numberAutoBan = Common::getOptionInt('auto_ban_messages');
        if ($numberAutoBan) {
            $where =  ' `from_user` = ' . to_sql($guid) .
                      ' AND `born` > ' . to_sql(date('Y-m-d H:i:s', $g_user['ban_time_release'])) .
                      ' AND `msg_hash` = ' . to_sql($textHash);
            $count = DB::count('im_msg', $where, '', $numberAutoBan);
            if ($count >= $numberAutoBan) {
                User::setBan();
                $isBan = true;
            }
        }
        return $isBan;
    }

    static function prepareMediaFromComment($msg, $uid, $admin = false)
    {
        global $p;

        $typeIm = Common::getOptionTemplate('im_type');
        $optionTmplSet = Common::getTmplSet();
        $display = get_param('display');
        $defaultVideoMobile = VideoHosts::getMobile();

        $classImage = 'lightbox';
        if ($typeIm == 'edge') {
            VideoHosts::setMobile(false);
            $classImage = 'lightbox_pics_im';
        } else {
            if ($optionTmplSet == 'old') {
                VideoHosts::setMobile(Common::isMobile());
            } elseif ($typeIm == 'urban') {
                VideoHosts::setMobile(false);
            } else {
                if (($p == 'messages.php' && !$display) || $display == 'general_chat') {
                    VideoHosts::setMobile(true);
                } elseif (($p != 'messages.php' || $display == 'open_list_chats') && $display != 'one_chat') {
                    VideoHosts::setMobile(true);
                } else {
                    VideoHosts::setMobile(false);
                }
            }
        }

        if ($admin) {
            VideoHosts::setMobile(false);
        }

        $defaultVideoEmbed = VideoHosts::getEmbedUrlShow();
        VideoHosts::setEmbedUrlShow(false);

        $pretag = '<div class="mod_im_msg_video">';
        $posttag = '</div>';

        $msg = ltrim($msg);
        $msg = wrapTextInConntentWithMedia($msg, '<div class="im_msg_wrap">', '</div>');

        $msg = Common::parseLinksTag(to_html($msg), 'a', '&lt;', 'parseLinksSmile');

        VideoHosts::$imMsg = true;
        $msg = VideoHosts::filterFromDbYoutube($msg, $pretag, $posttag);
        VideoHosts::$imMsg = false;

        if (!$admin) {
            OutsideImages::$addStyleLoad = true;
            OutsideImages::$userId = $uid;
            OutsideImages::$addScriptLoad = 'onload="showImageIm(this)"';
        }

        $msg = OutsideImages::filter_to_html($msg, '<div class="mod_im_msg_image">', '</div>', $classImage, '_blank', false, '', true);

        OutsideImages::$addStyleLoad = false;
        OutsideImages::$addScriptLoad = '';

        VideoHosts::setMobile($defaultVideoMobile);
        VideoHosts::setEmbedUrlShow($defaultVideoEmbed);

        return $msg;
    }

    static function uploadImageChangeEdit()
    {
        $fileUrl = get_param('file_url');
        if (!$fileUrl) {
            return false;
        }

        $imageString = str_replace(' ', '+', get_param('image'));

        if($imageString) {
            if(preg_match("/^data:image\/(?<extension>(?:png|gif|jpg|jpeg));base64,(?<image>.+)$/", $imageString, $matchings)){
                $imageString = base64_decode($matchings['image']);
                file_put_contents($fileUrl, $imageString);
                return true;
            } else {
                return false;
            }
        }
    }

}