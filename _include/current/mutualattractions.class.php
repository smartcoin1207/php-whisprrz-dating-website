<?php
class MutualAttractions extends CHtmlBlock
{
    static $first = true;

    static function isMutualAttraction($uid)
	{
        global $g_user;

        $where = '((`user_from` = ' . to_sql($g_user['user_id']) . ' AND `user_to` = ' . to_sql($uid) . ")
               OR (`user_from` = " . to_sql($uid) . ' AND `user_to` = ' . to_sql($g_user['user_id']) . "))
              AND `from_reply` IN('Y','M') AND `to_reply` IN('Y','M')";
        return DB::count('encounters', $where);
    }

    static function isAttractionFrom($uid)
	{
        global $g_user;

        //$where = '(`user_from` = ' . to_sql($uid) . ' AND `user_to` = ' . to_sql($g_user['user_id']) . ") AND `from_reply` IN('Y','M')";

        $where = '((`user_from` = ' . to_sql($uid) . ' AND `user_to` = ' . to_sql($g_user['user_id']) . ") AND `from_reply` IN('Y','M'))" .
                 ' OR ((`user_from` = ' . to_sql($g_user['user_id']) . ' AND `user_to` = ' . to_sql($uid) . ") AND `to_reply` IN('Y','M'))";

        $result = DB::field('encounters', 'from_reply', $where);
        return isset($result[0])?$result[0]:'';
    }

    static function getWhereMutual()
	{
        $guid = guid();

        $where = array(
            '`user_from` = ' . to_sql($guid, 'Number') . ' AND `from_reply` != "N" AND `to_reply` NOT IN("P","N")',
            '`user_to` = ' . to_sql($guid, 'Number') . ' AND `from_reply` != "N" AND `to_reply` NOT IN("P","N")',
        );

        return $where;
	}

    static function getWhereWanted()
	{
        global $g_user;

        return "`user_to` = " . to_sql($g_user['user_id'], 'Number') .
               " AND `from_reply` != 'N' AND `to_reply` = 'P'";

	}

    static function getWhereWhomYouLike()
	{
        $guid = guid();

        $where = array(
            "`user_from` = " . to_sql($guid) . " AND `from_reply` IN('Y','M')",
            "`user_to` = " . to_sql($guid) . " AND `to_reply` IN('Y','M')",
        );

        return $where;
	}

    static function getWhereWhoLikesYou()
	{
        $guid = guid();

        $where = array(
            "`user_from` = " . to_sql($guid) . " AND `to_reply` IN('Y','M')",
            "`user_to` = " . to_sql($guid) . " AND `from_reply` IN('Y','M')",
        );

        return $where;
	}

    static function setWantToMeet($uid = null, $status = null)
	{
        global $g_user;
        if ($uid === null) {
            $uid = get_param('uid', 0);
        }
        if ($status === null) {
            $status = get_param('status');
        }

        $responseData = array(
            'status' => '',
            'number' => 0,
            'number_mutual_likes' => 0,
            'number_whom_you_like' => 0,
        );

		if ($g_user['user_id']&& $uid && $status) {
            if ($status == 'N') {
                $responseData['status'] = 'del';
                //DB::delete('encounters', '`id` = ' . to_sql($uid, 'Number'));
                $method = 'Wanted';
            } else {
                $responseData['status'] = 'met';
                //DB::update('encounters', array('to_reply' => $status),'`id` = ' . to_sql($uid, 'Number'));
                $method = 'Mutual';
            }
            Encounters::likeToMeet($uid, $status);

            $optionTmplName = Common::getOption('name', 'template_options');
            $isMutual = self::isMutualAttraction($uid);
            $responseData['isMutual'] = $isMutual;
            if($isMutual) {
                $responseData['urlProfile'] = User::url($uid);
                $responseData['urlPhoto'] = User::getPhotoDefault($uid, 'r');
            }
            $responseData['number'] = self::getNumberMutualAttractions($method);
            if ($optionTmplName == 'impact_mobile') {
                $responseData = Menu::getListCounterImpactMobile($responseData);
            } else {
                $responseData = self::getCounters($responseData);
            }
        }

        return $responseData;

    }

    static function getNumberMutualAttractions($method = 'Mutual', $new = false)
    {
        global $g_user;

        $method = 'getWhere' . $method;
        $whereNew = '';
        if ($new) {
            $whereNew = ' AND ((`user_from` = ' . to_sql(guid()) . '  AND `new` = "Y") OR (`user_to` =  ' . to_sql(guid()) . ' AND `new_to` = "Y"))';
        }

        $where = self::$method();

        if(is_array($where)) {
            $sql = 'SELECT
                (SELECT COUNT(*) FROM `encounters` WHERE ' . $where[0] . ($new ? ' AND `new` = "Y"' : '') . ')
                +
                (SELECT COUNT(*) FROM `encounters` WHERE ' . $where[1] . ($new ? ' AND `new_to` = "Y"' : '') . ')
                ';
        } else {
            $sql = 'SELECT COUNT(*) FROM `encounters` WHERE ' . $where . $whereNew;
        }

        return DB::result($sql);
    }

    static function unlike($uid = null) {
        global $g_user;

        $responseData = false;
        if ($uid === null) {
            $uid = get_param('user_id', 0);
        }
        if ($g_user['user_id'] && $uid) {
            $responseData = Encounters::likeToMeet($uid, 'N');
        }
        return $responseData;
    }

    static function remove($uidFrom, $uidTo = null)
	{
        if ($uidTo == null) {
			$uidTo = guid();
		}
        $where = '(`user_from` = ' . to_sql($uidTo) . ' AND `user_to` = ' . to_sql($uidFrom) . ")
               OR (`user_from` = " . to_sql($uidFrom) . ' AND `user_to` = ' . to_sql($uidTo) . ")";
        DB::delete('encounters', $where);
    }

    function parseItemBlockImpact(&$html, $row, &$curId) {
        $guid = guid();
        $curId = $row['id'];
        if ($row['user_from'] == $guid) {
            $uid = $row['user_to'];
            $isNew = ($row['new'] == 'Y');
        } else {
            $uid = $row['user_from'];
            $isNew = ($row['new_to'] == 'Y');
        }

        if ($html->varExists('item_time_ago_cl')) {
            $html->setvar('item_time_ago_cl', $isNew ? 'new' : '');
        }
        $html->setvar('item_time_ago', $isNew ? l('new') : '');

        $user = User::getInfoBasic($uid, false, 2);
        $user['id'] = $curId;
        User::parseItemBasicList($html, $user, true);

        /*$html->setvar('item_id', $curId);
        $html->setvar('photo_m', User::getPhotoDefault($user['user_id'], 'm', false, $user['gender']));
        $html->setvar('name_one_letter_short', User::nameOneLetterShort($user['name']));
        $html->setvar('age', $user['age']);
        $html->setvar('user_gender', $user['gender'] == 'M' ? l('man') : l('woman'));
        $html->setvar('city', l($user['city']));
        $html->setvar('user_profile_link', User::url($uid, $user));
        User::parseCharts($html, $uid, 'list');*/

        $html->parse('users_list_item', true);
    }

    static function setViewedNewItems() {
        $cmd = get_param('cmd', get_param('display'));
        if ($cmd == 'want_to_meet_you') {
            $method = 'Wanted';
        } elseif ($cmd == 'whom_you_like') {
            $method = 'WhomYouLike';
        } elseif ($cmd == 'who_likes_you') {
            $method = 'WhoLikesYou';
        }else{
            $method = 'Mutual';
        }

        $method = 'getWhere' . $method;
        if (method_exists('MutualAttractions', $method)) {
            $whereAction = self::$method();

            if(is_array($whereAction)) {
                $where = $whereAction[0] . ' AND `new` = "Y"';
                DB::update('encounters', array('new' => 'N'), $where);
                $where = $whereAction[1] . ' AND `new_to` = "Y"';
                DB::update('encounters', array('new_to' => 'N'), $where);
            } else {
                $where = $whereAction . ' AND `user_from` = ' . to_sql(guid()) . '  AND `new` = "Y"';
                DB::update('encounters', array('new' => 'N'), $where);
                $where = $whereAction .' AND `user_to` =  ' . to_sql(guid()) . ' AND `new_to` = "Y"';
                DB::update('encounters', array('new_to' => 'N'), $where);
            }
        }
    }

    static function parseItemBlock(&$html, $row, &$curId) {
        $isBlocking = Common::isOptionActive('contact_blocking');
        $guid = guid();
        if (self::$first && !get_param('ajax', 0)) {
            $html->parse('border_none', false);
            $html->clean('border_top');
            self::$first = false;
        } else {
            $html->parse('border_top', false);
            $html->clean('border_none');
        }

        if ($row['user_from'] == $guid) {
            $userTo = $row['user_to'];
            $userReply = $row['to_reply'];
            $mainReply = $row['from_reply'];
        } else {
            $userTo = $row['user_from'];
            $userReply = $row['from_reply'];
            $mainReply = $row['to_reply'];
        }

        $gender = User::getInfoBasic($userTo, 'gender', 1);
        $html->setvar('question_encounters', l('would_you_like_to_meet_' . $gender));

        $curId = $row['id'];
        $html->setvar('id', $row['id']);

        $sql = 'SELECT COUNT(*) FROM `users_view`
                 WHERE `user_from` = ' . to_sql($guid) .
                 ' AND `user_to` = ' . to_sql($userTo);
        $blockNew = 'user_new';
        if (DB::result($sql, 0, 1)) {
            $html->clean($blockNew);
        } else {
            $html->parse($blockNew, false);
        }

        $html->setvar('said_main', lSetVars('said_main', array('reply' => l('said_' . $mainReply))));
        $blockMainGreen = 'green_main';
        $blockMainGrey = 'grey_main';
        if ($mainReply == 'Y') {
            $html->parse($blockMainGreen, false);
            $html->clean($blockMainGrey);
        } else {
            $html->parse($blockMainGrey, false);
            $html->clean($blockMainGreen);
        }

        $html->setvar('said_user', lSetVars('user_said_' . $gender, array('reply' => l('said_' . $userReply))));
        $blockUserGreen = 'green_user';
        $blockUserGrey = 'grey_user';
        if ($userReply == 'Y') {
            $html->parse($blockUserGreen, false);
            $html->clean($blockUserGrey);
        } else {
            $html->parse($blockUserGrey, false);
            $html->clean($blockUserGreen);
        }

        $user = User::getInfoBasic($userTo, false, 2);

        $html->setvar('user_id', $userTo);
        $html->setvar('user_profile_link', User::url($userTo));
        $html->setvar('user_name', $user['name']);
        $html->setvar('user_photo', User::getPhotoDefault($userTo, 'r'));
        $html->setvar('user_age', $user['age']);
        $html->setvar('user_city', l($user['city']));

        if ($isBlocking) {
            $html->parse('user_blocking', false);
        }

        User::isBlockedMeSetvar($html, $userTo);

        $blockGifts = 'gifts_enabled';
        if (Common::isOptionActive($blockGifts)) {
            $html->parse($blockGifts, false);
        } else {
            $html->parse('gifts_disabled', false);
        }
        $html->parse('mutual_attractions_item', true);
    }

	function parseBlock(&$html) {
		global $g;

        $guid = guid();

        if ($guid) {
            $optionTmplName = Common::getOption('name', 'template_options');
            $cmd = get_param('cmd');
            //$maxId = DB::result('SELECT MAX(`id`) FROM `encounters`');
            $lastId = get_param_int('last_id');

            $mOnPage = Common::getOption('user_custom_per_page', 'template_options');
            $html->setvar('on_page', $mOnPage);
            $limit = get_param_int('limit', $mOnPage) + 1;

            $isWanted = $cmd == 'want_to_meet_you';
            if (!$cmd) {
                $where = self::getWhereMutual();
            } elseif ($cmd == 'want_to_meet_you') {
                $where = self::getWhereWanted();
            } elseif ($cmd == 'whom_you_like') {//кто мне нравится - who I like
                $where = self::getWhereWhomYouLike();
            } elseif ($cmd == 'who_likes_you') {//кому я нравлюcь - who likes me
                $where = self::getWhereWhoLikesYou();
            }

            $sql = self::prepareSql($where, $lastId, $limit);

            DB::query($sql);

            $curId = 0;
            $method = 'parseItemBlock';
            $onItemTemplateMethod = 'parseItemBlock' . $optionTmplName;
            if (method_exists('MutualAttractions', $onItemTemplateMethod)) {
                $method = $onItemTemplateMethod;
            }

            $rowsCounter = 0;

            $endId = 0;

            while ($row = DB::fetch_row()){
                /*if ($row['user_from'] == guid() && $row['new'] == 'Y') {
                    DB::update('encounters', array('new' => 'N'), '`id` = ' . to_sql($row['id']));
                } elseif($row['user_to'] == guid() && $row['new_to'] == 'Y') {
                    DB::update('encounters', array('new_to' => 'N'), '`id` = ' . to_sql($row['id']));
                }*/

                $rowsCounter++;

                $endId = $row['id'];

                if($rowsCounter == $limit) {
                    break;
                }

                self::$method($html, $row, $curId);
            }

            $blockLastLoadItem = 'mutual_attractions_last_load_item';
            if ($optionTmplName == 'impact') {//impact
                $pageClass = 'mutual_likes';
                if ($cmd) {
                    $pageClass = $cmd;
                }
                $html->setvar('page_class', $pageClass);
                $pageTitle = l('column_narrow_' . $pageClass);
                $html->setvar('page_title', $pageTitle);
                $html->setvar('cmd', $cmd);
                $blockLastLoadItem = 'users_list_last_load_item';
            } else {
                if ($isWanted) {
                    $html->setvar('page_title', l('want_to_meet_you'));
                    $html->parse('cmd_ajax');
                    $html->parse('wanted_cont');
                } else {
                    $html->setvar('page_title', l('mutual_attractions'));
                    $html->parse('mutual_cont');
                }
            }
            if ($endId == $curId || $curId == 0) {
                $html->parse($blockLastLoadItem);
            }
            if ($curId == 0 && $endId == 0) {
                $html->parse('no_one_here_yet');
            } else {
                $html->setvar('last_id', $curId);
                if ($html->blockExists('users_list_last_id')) {
                    $html->parse('users_list_last_id', false);
                }
            }
        }
		parent::parseBlock($html);
	}

    static function getCounters($responseData = array())
    {
        $responseData['number_mutual_likes'] = self::getNumberMutualAttractions('Mutual');
        $responseData['number_mutual_likes_new'] = self::getNumberMutualAttractions('Mutual', true);
        $responseData['number_whom_you_like'] = self::getNumberMutualAttractions('WhomYouLike');
        $responseData['number_whom_you_like_new'] = 0; //self::getNumberMutualAttractions('WhomYouLike', true);
        $responseData['number_who_likes_you'] = self::getNumberMutualAttractions('WhoLikesYou');
        $responseData['number_who_likes_you_new'] = self::getNumberMutualAttractions('WhoLikesYou', true);

        return $responseData;
    }

    static public function prepareSql($where, $lastId, $limit)
    {
        if(is_array($where)) {
            $sql = 'SELECT * FROM (
                (SELECT * FROM `encounters` WHERE ' . self::addLastIdToWhere($where[0], $lastId) . ')
                UNION
                (SELECT * FROM `encounters` WHERE ' . self::addLastIdToWhere($where[1], $lastId) . ')
            ) AS T ORDER BY `id` DESC LIMIT ' . to_sql($limit, 'Number');
        } else {
            $sql = 'SELECT * FROM `encounters`
                WHERE ' . self::addLastIdToWhere($where, $lastId) . ' ORDER BY `id` DESC
                LIMIT ' . to_sql($limit, 'Number');
        }

        return $sql;
    }

    static public function addLastIdToWhere($where, $lastId)
    {
        if($lastId) {
            $where .= ' AND id < ' . to_sql($lastId, 'Number');
        }

        return $where;
    }
}