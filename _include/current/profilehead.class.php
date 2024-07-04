<?php
class ProfileHead extends CHtmlBlock
{
    static private $uid = 0;
    static private $userInfo = null;


    static function setUserId($uid)
    {
        self::$uid = $uid;
    }

    static function setUserInfo($userInfo)
    {
        self::$userInfo = $userInfo;
    }

    static function getUserInfo()
    {
        if (self::$uid && (self::$userInfo === null || !is_array(self::$userInfo))) {
            self::$userInfo = User::getInfoBasic(self::$uid);
        }

        return self::$userInfo;
    }

    static function parseHead(&$html, $user = null)
    {
        global $g;
		global $g_user;
        global $p;

        if ($user === null) {
            $user = self::getUserInfo();
        }
        if (!empty($user)) {
            foreach ($user as $key => $value) {
                if (is_string($key)) {
                    if (in_array($key, array('city_title', 'state_title', 'country_title'))) {
                        $value = l($value);
                    }
                    $html->setvar($key, $value);
                }
            }
            if (guid() == $user['user_id']) {
                $html->parse('profile_edit_main');
            } else {
                $setOffline = false;
                if (User::isOnline($user['user_id'], $user)) {
                    $status = l('on_the_site_now');
                    $html->parse('status_online', false);
                    $html->clean('status_offline');
                } else {
                    $setOffline = true;
                    $status = lSetVars('was_online_' . $user['gender'], array('date' => Common::dateFormat($user['last_visit'], 'profile_was_online_date',false)));
                    $html->parse('status_offline', false);
                    $html->clean('status_online');
                }

                if ($html->varExists('last_visit_urban_offline')) {
                    if (!$setOffline) {
                        $status = lSetVars('was_online_' . $user['gender'], array('date' => Common::dateFormat($user['last_visit'], 'profile_was_online_date',false)));
                    }
                    $html->setvar('last_visit_urban_offline', $status);
                } else {
                    $html->setvar('last_visit_urban', $status);
                }

                $blockVisitor = 'profile_visitor_title';
                if ($html->blockexists($blockVisitor)) {
                    $html->setvar('profile_visitor_link', User::url($user['user_id']));

                    User::parseRefererBackUrl($html, $user['user_id'], $blockVisitor . '_back');

                    //if ($g_user['user_id'])
                        $blockVisitorMenu = $blockVisitor . '_menu';

                        $notLocked = !User::isEntryBlocked($user['user_id'], $g_user['user_id']);

                        if ($notLocked) {
                            if ($user['is_photo_public'] == 'Y' && Encounters::isLikeToMeet($user['user_id'])) {
                                $html->setvar('meet_her', l('meet_her_' . $user['gender']));
                                $html->parse($blockVisitorMenu . '_meet');
                            }
                            if (Common::isOptionActive('videochat') && $p != 'videochat.php') {
                                $html->parse($blockVisitorMenu . '_video_chat');
                            }
                        }

                        /* Additional */
                        //Собрать в массив
                        $blockAdditionalMenu = 'menu_additional';
                        $isParseAdditionalMenu = false;
                        if ($notLocked && Common::isOptionActive('audiochat') && $p != 'audiochat.php') {
                            $isParseAdditionalMenu = false;
                            $html->parse($blockAdditionalMenu . '_audio_chat_first');
                            $html->parse($blockAdditionalMenu . '_audio_chat');
                        }

                        if (City::isActiveStreetChat()) {
                            if ($isParseAdditionalMenu) {
                                $isParseAdditionalMenu = false;
                                $html->parse($blockAdditionalMenu . '_street_chat_first');
                            }
                            $html->parse($blockAdditionalMenu . '_street_chat');
                        }

                        if (Common::isOptionActive('gifts_enabled')) {
                            User::isBlockedMeSetvar($html, $user['user_id']);
                            if ($isParseAdditionalMenu) {
                                $isParseAdditionalMenu = false;
                                $html->parse($blockAdditionalMenu . '_gifts_first');
                            }
                            $html->parse($blockAdditionalMenu . '_gifts');
                        }

                        if (Common::isOptionActive('wink')) {
                            if ($isParseAdditionalMenu) {
                                $isParseAdditionalMenu = false;
                                $html->parse($blockAdditionalMenu . '_wink_first');
                            }
                            $html->parse($blockAdditionalMenu . '_wink');
                        }

                        if (Common::isOptionActive('friends_enabled')) {
                            $isFriendRequested = User::isFriendRequestExists($user['user_id'], $g_user['user_id']);
                            $isFriend = User::isFriend($user['user_id'], $g_user['user_id']);
                            if (empty($isFriendRequested) && empty($isFriend)) {
                            }
                            $isParseActionFriends = false;
                            $action = 'request';
                            $class = 'friend_add';
                            $title = l('add_to_friends');
                            if (User::isFriend($user['user_id'], $g_user['user_id'])) {
                                $action = 'remove';
                                $class = 'friend_remove';
                                $title = l('unfriend');
                                $isParseActionFriends = true;
                            }elseif (User::isFriendRequestExists($user['user_id'], $g_user['user_id'])!=$g_user['user_id']) {
                                $isParseActionFriends = true;
                            }
                            $html->setvar('friend_action', $action);
                            $html->setvar('friend_action_class', $class);
                            $html->setvar('friend_action_title', $title);
                            if ($isParseAdditionalMenu && $isParseActionFriends) {
                                $isParseAdditionalMenu = false;
                                $html->parse("{$blockAdditionalMenu}_friend_first");
                            }
                            if (!$isParseActionFriends) {
                                $html->parse("{$blockAdditionalMenu}_no_friend_add");
                            }
                        } else {
                            $html->parse("{$blockAdditionalMenu}_no_friend_add");
                        }

                        if (Common::isOptionActive('contact_blocking')) {
                            if ($isParseAdditionalMenu) {
                                $isParseAdditionalMenu = false;
                                $html->parse($blockAdditionalMenu . '_contact_first');
                            }
                            $html->parse($blockAdditionalMenu . '_contact_blocking');
                        }

                        if (!in_array($g_user['user_id'], explode(',', $user['users_reports']))) {
                            if ($isParseAdditionalMenu) {
                                $isParseAdditionalMenu = false;
                                $html->parse($blockAdditionalMenu . '_report_first');
                            }
                            $html->parse($blockAdditionalMenu . '_report');
                        }

                        if ($isParseAdditionalMenu) {
                            $html->parse("{$blockAdditionalMenu}_hide");
                        }
                        /* Additional */


                        $splitByBlocks=array(array('name_block'=>'visitor_menu','count'=>3),
                                             array('name_block'=>'visitor_menu_more','count'=>0),
                                            );

                        Menu::parseSubmenu($html,'visitor_menu',0,null,$splitByBlocks);

                        $html->parse($blockVisitorMenu);

                        $isFreeSite = Common::isOptionActive('free_site');
                        if (!$isFreeSite) {
                            $blockVisitorFunk = $blockVisitor . '_funk';
                            if (User::isSuperPowers($user['gold_days'], $user['orientation'])) {
                                $html->cond(User::isSuperPowers(), $blockVisitorFunk . '_sp_active', $blockVisitorFunk . '_sp_not_active');
                            }
                            $raisedSearchTimeAgo = timeAgo($user['date_search'], 'now', 'string', 60, 'second');
                            if ($raisedSearchTimeAgo != 'Bad date') {
                                $html->setvar('raised_time_ago', lSetVars('raised_his_profile_time_ago_' . $user['gender'], array('time' => $raisedSearchTimeAgo)));
                                $html->parse($blockVisitorFunk . '_raised_search');
                            }
                            $level = User::getLevelOfPopularity($user['user_id']);
                            if ($level == 'very_high') {
                                $html->parse($blockVisitorFunk . '_popular');
                            }
                            $html->parse($blockVisitorFunk);
                        }
                    //}
                    $html->parse($blockVisitor);
                }
            }

        }

    }

	function parseBlock(&$html)
    {
        self::parseHead($html);

		parent::parseBlock($html);
	}
}