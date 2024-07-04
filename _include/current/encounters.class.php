<?php
class Encounters extends CHtmlBlock
{
    static $fastSelectSql = '';
    static $fastSelectUid = 0;

    static function removeLikeToMeet($uid)
	{
        $where = '`user_from` = ' . to_sql($uid) . ' OR `user_to` = ' . to_sql($uid);
        DB::delete('encounters', $where);
    }


    static function parseLikeToMeet(&$html, $uid, $userIsPhotoPublic = null, $block = 'wall_like_to_meet', $reply = 'P')
	{
        $result = false;
        if ($html->blockExists($block)) {
            $guid = guid();
            if ($userIsPhotoPublic === null) {
                $userIsPhotoPublic = User::getInfoBasic($uid, 'is_photo_public');
            }
            if ($guid && $guid != $uid && $userIsPhotoPublic == 'Y') {
                $sql = 'SELECT `id`
                          FROM `encounters`
                         WHERE (`user_to` = ' . to_sql($guid, 'Number') .
                         ' AND `user_from` = ' . to_sql($uid, 'Number') .
                         " AND `to_reply` = 'Y')" .
                          ' OR (`user_from` = ' . to_sql($guid, 'Number') .
                         ' AND `user_to` = ' . to_sql($uid, 'Number') .
                         " AND `from_reply` = 'Y')";
                $isLike = DB::result($sql, 0, 0, true);
                //already have my like
                if ($isLike) {
                    $html->setvar("{$block}_title", toAttrL('wall_unlike_this_user'));
                    $html->setvar("{$block}_selected", 'active');
                } else {
                    $html->setvar("{$block}_selected", '');
                    $html->setvar("{$block}_title", toAttrL('wall_like_this_user'));
                }
                $html->parse($block, false);
                $result = true;
            }
        }
        return $result;
    }

    static function isLikeToMeet($uid)
	{
        global $g_user;

        $where = '(`user_from` = ' . to_sql($g_user['user_id']) . ' AND `user_to` = ' . to_sql($uid) . " AND `to_reply` = 'N')
               OR (`user_from` = " . to_sql($uid) . ' AND `user_to` = ' . to_sql($g_user['user_id']) . " AND `from_reply` = 'N')";

        $info = DB::select('encounters', $where);

        return empty($info);
    }

	static function isWantsToMeet($uidFrom, $uidTo = null)
	{
		if ($uidTo == null) {
			$uidTo = guid();
		}
        $where = '(`user_from` = ' . to_sql($uidTo) . ' AND `user_to` = ' . to_sql($uidFrom) . " AND `from_reply` IN('Y','M'))
               OR (`user_from` = " . to_sql($uidFrom) . ' AND `user_to` = ' . to_sql($uidTo) . " AND `to_reply` IN('Y','M'))";

        return DB::count('encounters', $where);
    }

    static function undoLike()
	{
        global $g_user;

        $cmdEnc = get_param('cmd_enc', 0);

        if ($g_user['user_id'] && $cmdEnc == 'undo') {
            $uidEnc = get_param('uid_enc', 0);
            if ($uidEnc) {
                $where = '(`user_from` = ' . to_sql($g_user['user_id']) . ' AND `user_to` = ' . to_sql($uidEnc) . ')
                       OR ( `user_from` = ' . to_sql($uidEnc) . ' AND `user_to` = ' . to_sql($g_user['user_id']) . ')';

                $info = DB::select('encounters', $where);

                if (!empty($info) && isset($info[0])) {
                    $info = $info[0];
                    if ($info['user_from'] == $g_user['user_id']) {
                        DB::delete('encounters', '`id` = ' . to_sql($info['id']));
                        // If at this moment had someone
                        // but if the answer is "N" it does not snap back, its in the sample will not be ?
                        if ($info['to_reply'] != 'P') {
                            $data = array('user_from' => $uidEnc,
                                          'user_to' => $g_user['user_id'],
                                          'from_reply' => $info['to_reply'],
                                          'to_reply' => 'P');
                            DB::insert('encounters', $data);
                            DB::update('encounters', $data, '`id` = ' . to_sql($info['id']));
                        }
                    } else {
                        $data = array('to_reply' => 'P');
                        DB::update('encounters', $data, '`id` = ' . to_sql($info['id']));
                    }
                }
            }
        }
	}

    static function likeToMeet($uidEnc = null, $replyEnc = null, $cmdEnc = null, $new = 'Y')
	{
        global $g_user;

        $responseData = false;

        if ($cmdEnc === null) {
            $cmdEnc = get_param('cmd_enc', 'reply');
        }

        if ($g_user['user_id'] && $cmdEnc == 'reply') {
            if ($uidEnc === null) {
                $uidEnc = get_param('uid_enc', 0);
            }
            if ($replyEnc === null) {
                $replyEnc = get_param('reply_enc', 0);
            }
            if ($replyEnc && $uidEnc) {
                $userEncInfo = User::getInfoBasic($uidEnc);
                if (!$userEncInfo) {
                    return false;
                }
                /*$allowLikeProfileWithoutPhotos = Common::isOptionActive('allow_like_profile_without_photos', 'template_options');
                if (!$allowLikeProfileWithoutPhotos
                    && ($userEncInfo['is_photo'] == 'N'
                        || ($userEncInfo['is_photo_public'] == 'N'
                            && !User::isFriend($g_user['user_id'], $uidEnc)))) {
                    return true;
                }*/
                $isUpdate = false;
                /*$where = to_sql($g_user['user_id'], 'Number') . ',' . to_sql($uidEnc, 'Number');
                $where = '`user_from` IN (' . $where . ') AND `user_to` IN (' .$where . ')';*/
                $where = '(`user_from`=' . to_sql($g_user['user_id']) . ' AND `user_to`=' . to_sql($uidEnc) . ')
					   OR (`user_from`=' . to_sql($uidEnc) . ' AND `user_to`=' . to_sql($g_user['user_id']) . ')';
                $encounter = DB::select('encounters', $where);
                if (!empty($encounter) && isset($encounter[0])) {
                    $vars = array();
                    $vars['new'] = $new;
                    $vars['new_to'] = $new;
                    $encounter = $encounter[0];
                    if ($encounter['user_from'] == $g_user['user_id']) {
                        $vars['from_reply'] = $replyEnc;
                        if($replyEnc == 'N') {
                            unset($vars['new']);
                            unset($vars['new_to']);
                        }
                    } else {
                        $vars['to_reply'] = $replyEnc;
                        if($replyEnc == 'N') {
                            unset($vars['new']);
                            unset($vars['new_to']);
                        }
                    }
                    DB::update('encounters', $vars, '`id` = ' . to_sql($encounter['id'], 'Number'));
                    $isUpdate = true;
                } else {
                    $vars = array('user_from' => $g_user['user_id'],
                                  'user_to' => $uidEnc,
                                  'from_reply' => $replyEnc,
                                  'new' => $new,
                                  'new_to' => $new,
                        );
                    DB::insert('encounters', $vars);
                }
                if ($replyEnc != 'N') {
                    $typeAutoMail = $isUpdate ? 'mutual_attraction' : 'want_to_meet_you';
                    if (Common::isEnabledAutoMail($typeAutoMail)) {
                        $typeSettings = $isUpdate ? 'set_notif_mutual_attraction' : 'set_notif_want_to_meet_you';
                        $isNotifUser = User::isOptionSettings($typeSettings, $userEncInfo);
                        if ($isNotifUser) {
                            $vars = array('title' => Common::getOption('title', 'main'),
                                          'name' => $userEncInfo['name'],
                                          'uid_sender' => $g_user['user_id'],
                                          'name_sender'  => $g_user['name'],
                                          'url_site' => Common::urlSiteSubfolders());
                            Common::sendAutomail($userEncInfo['lang'], $userEncInfo['mail'], $typeAutoMail, $vars);
                        }
                    }
                }
                $responseData = true;
            }
        }
        return $responseData;
	}

    static function prepareFastSelect($where, $whereLocation, $from_add, $order, $countForResults = 1)
    {
        global $g_user;

        $orderTypes = explode(',', $order);

        $sqls = array();
        $countForSearch = 100;

        $whereEncountersAddons = array(1);

        foreach($orderTypes as $orderType) {
            if(strpos(trim($orderType), 'i_am_here_to') === 0) {
                $whereEncountersAddons[] = str_replace('DESC', '', $orderType);
                break;
            }
        }

        foreach($whereEncountersAddons as $whereEncountersAddon) {

            foreach($orderTypes as $orderType) {
                if( (strpos(trim($orderType), 'near ') === 0) || (strpos(trim($orderType), 'i_am_here_to') === 0) ) {
                    continue;
                }

                $sqls[] = '(SELECT u.*, IF(u.city_id=' . $g_user['city_id'] . ', 1, 0) +
                IF(u.state_id=' . $g_user['state_id'] . ', 1, 0) +
                IF(u.country_id=' . $g_user['country_id'] . ', 1, 0) AS near FROM user AS u ' . $from_add . '
                    WHERE ' . $where . $whereLocation . ' AND ' . $whereEncountersAddon . '
                    ORDER BY ' . $orderType . ' LIMIT ' . $countForSearch . ')';
            }

        }

        self::$fastSelectSql = implode(" UNION ", $sqls) . ' ORDER BY ' . $order . ' LIMIT ' . $countForResults;
    }

    static function getFastSelectWhere($where, $countForResults = 1)
    {
        if(self::$fastSelectSql) {
            if ($countForResults > 1) {
                $users = array();
                $lastUid = 0;
                $rows = DB::rows(self::$fastSelectSql);
                foreach ($rows as $row) {
                    $users[] =  $row['user_id'];
                    $lastUid = $row['user_id'];
                }
                if ($users) {
                    $where = ' u.user_id IN (' . implode(',', $users) . ')';
                    self::$fastSelectUid = $lastUid;
                }
            } else {
                $result = DB::row(self::$fastSelectSql);
                $uid = isset($result['user_id']) ? $result['user_id'] : 0;
                $where = ' u.user_id = ' . to_sql($uid);
            }
        }
        return $where;
    }
}