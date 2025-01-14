<?php
class Chat extends CHtmlBlock
{

    private static $userId;
    private static $action;
    private static $type = '';
    private static $tableInvite;
    private static $tableReject;
	private static $city3D = false;

    function action()
	{

        $cmd = get_param('cmd');
    }

	static function set3DCity($city)
    {
		self::$city3D = $city;
	}

	static function getWhere()
    {
		return self::$city3D ? ' `city` = 1 ' : ' `city` = 0 ';
	}

    static function setType($type = null)
    {
        if ($type === null) {
            $type = get_param('type', 'audio');
        }

        if($type !== 'video' && $type !== 'audio') {
            $type = 'video';
        }

        self::$type = $type;
        self::$tableInvite = $type . '_invite';
        self::$tableReject = $type . '_reject';
    }

    static function setUserId($uid)
    {
        self::$userId = $uid;
    }

    static function setAction($action)
    {
        self::$action = $action;
    }

    static function isAction($data)
    {
        return is_array($data)
               &&isset($data['action'])&&!empty($data['action'])
               &&isset($data['user_id'])&&!empty($data['user_id']);
    }

    static function invite()
    {
        global $g_user;
        global $l;

        $optionTmplSet = Common::getOption('set', 'template_options');

        $result = false;
        $uid = get_param('user_id');
        $responseData = false;
        self::setType();

        $groupId = Groups::getParamId();
		$city = get_param_int('city');

        if ($g_user['user_id'] && $uid) {
            if ($optionTmplSet == 'urban') {
                $check = User::isEntryBlocked($uid, guid());
            } else {
                $check = User::isBlocked(self::$type . 'chat', $uid, guid());
            }
            if ($check) {
                $responseData = l('you_are_in_block_list');
            }
            $isFreeSite = Common::isOptionActive('free_site');
            $isSuperPowers = User::isSuperPowers();
             if (!$responseData && !$isFreeSite && !$isSuperPowers) {
                if (Common::isActiveFeatureSuperPowers(self::$type . 'chat')) {
                    $responseData = 'upgrade';
                } else if (Common::isActiveFeatureSuperPowers('chat_with_popular_users') && !$city){
                    $level = User::getLevelOfPopularity($uid);
                    if ($level == 'very_high') {
                        $gender = User::getInfoBasic($uid, 'gender');
                        $type = 'chat_to_user_popular_' . mb_strtolower($gender, 'UTF-8');
                        $vars = array('name' => User::getInfoBasic($uid, 'name'),
                                  'url' =>  "search_results.php?display=profile&uid={$uid}");
                        if (Common::isMobile()) {
                            $vars['url'] = "profile_view.php?user_id={$uid}";
                        }
                        $responseData = Common::lSetLink($type, $vars);
                        $requestUri = self::$type . 'chat.php?id=' . $uid . '&type=' . self::$type;
                        $vars = array('url' => 'upgrade.php?request_uri=' . base64_encode($requestUri));
                        $responseData = Common::lSetLink($responseData, $vars, false, 1);
                    }
                }
            }

            if (!$responseData) {
                /*if (IS_DEMO && $optionTmplSet == 'urban'
                    && Common::getOption('type_media_chat') == 'webrtc'
                    && User::isDemoUser($uid)
                    && !get_param('device')) {
                    return 'demo_user';
                }*/

                $sql = 'SELECT *
                          FROM `' . self::$tableInvite . '`
                         WHERE `to_user` = ' . to_sql($uid, 'Number') .
                         ' AND `from_user` = ' . to_sql($g_user['user_id'], 'Number') .
                         ' AND `group_id` = ' . to_sql($groupId, 'Number') .
						 ' AND ' . self::getWhere();
                DB::query($sql);

                if (!($row = DB::fetch_row())) {
                    $sql = 'INSERT INTO `' . self::$tableInvite . '`
                               SET `to_user` = ' . to_sql($uid, 'Number') . ',
                                   `from_user` = ' . to_sql($g_user['user_id'], 'Number') . ',
                                   `group_id` = ' . to_sql($groupId, 'Number') . ',
								   `city` = ' . to_sql($city, 'Number');
                    DB::execute($sql);
                } else {
                    $sql = 'DELETE FROM `' . self::$tableInvite . '`
                             WHERE `to_user` = ' . to_sql($uid, 'Number') .
                             ' AND `from_user` =' . to_sql($g_user['user_id'], 'Number') .
                             ' AND `group_id` = ' . to_sql($groupId, 'Number') .
							 ' AND ' . self::getWhere();
                    DB::execute($sql);
                    $sql = 'INSERT INTO `' . self::$tableInvite . '`
                               SET `to_user` = ' . to_sql($uid, 'Number') . ',
                                   `from_user` = ' . to_sql($g_user['user_id'], 'Number') . ',
                                   `group_id` = ' . to_sql($groupId, 'Number') . ',
								   `city` = ' . to_sql($city, 'Number');
                    DB::execute($sql);
                }

                $userInfo = User::getInfoBasic($uid);
                if($userInfo && !self::$city3D && User::isOptionSettings('set_notif_push_notifications', $userInfo)) {
                    $message = Common::replaceByVars(l(self::$type . '_chat_from_user_notifications', loadLanguageSiteMobile($userInfo['lang'])), array('user_name' => User::nameShort(guser('name'))));

                    if(self::$type == 'video') {
                        PushNotification::sendChatVideo($uid, $message);
                    } else {
                        PushNotification::sendChatAudio($uid, $message);
                    }
                }

                $responseData = true;
            }
        }
        return $responseData;
    }

    static function update($type, $uid = 0, $checkRequest = false)
    {
        global $g_user;

        $responseData = array();
        self::setType($type);

        if ($g_user['user_id']) {
			$requestCity = 0;
            $groupId = 0;
            $sql = 'SELECT *
                      FROM `' . self::$tableInvite . '`
                     WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number');
            if ($uid) {
                $sql .= ' AND `from_user` = ' . to_sql($uid);
            }
			$sql .= ' AND ' . self::getWhere();

            DB::query($sql);
            if ($row = DB::fetch_row()){
                $groupId = $row['group_id'];
				$requestCity = $row['city'];
                $responseData = array('action' => 'request',
                                      'user_id' => $row['from_user'],
                                      'group_id' => $groupId);
                $sql = 'DELETE FROM `' . self::$tableInvite . '`
                         WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                         ' AND `from_user` = ' . to_sql($row['from_user'], 'Number') .
                         ' AND `group_id` = ' . to_sql($groupId, 'Number') .
						 ' AND ' . self::getWhere();
                DB::execute($sql);
                $isFreeSite = Common::isOptionActive('free_site');
                $isSuperPowers = User::isSuperPowers();
                if (!$isFreeSite && !$isSuperPowers) {
                    $isUpgrade = false;
                    $offChatWithPopular = Common::isOptionActive('chat_with_popular_users_off', 'template_options');
                    if (Common::isActiveFeatureSuperPowers($type . 'chat')) {
                        $isUpgrade = true;
                    } else if (!$offChatWithPopular && Common::isActiveFeatureSuperPowers('chat_with_popular_users') && !$requestCity){
                        $level = User::getLevelOfPopularity($row['from_user']);
                        if ($level == 'very_high') {
                            $isUpgrade = true;
                        }
                    }
                    if ($isUpgrade) {
                        $requestUri = $type . 'chat.php?id=' . $row['from_user'] . '&type=' . $type;
                        $responseData = array('action' => 'request',
                                              'user_id' => $row['from_user'],
                                              'type' => $type . 'chat',
                                              'request_uri' =>  base64_encode($requestUri));
                    }
                }
            }

            $sql = 'SELECT *
                      FROM `' . self::$tableReject . '`
                     WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number');
            if ($uid) {
                $sql .= ' AND `from_user` = ' . to_sql($uid);
            }
			$sql .= ' AND ' . self::getWhere();

            DB::query($sql);
            if ($row = DB::fetch_row()){
                $groupId = $row['group_id'];
                $responseData['action'] = ($row['go'] == 'N') ? 'reject' : 'start_talk';
                $responseData['user_id'] = $row['from_user'];
				$requestCity = $row['city'];
                $sql = 'DELETE FROM `' . self::$tableReject . '`
                         WHERE `to_user` = ' . to_sql($g_user['user_id'], 'Number') .
                         ' AND `from_user` =' . to_sql($row['from_user'], 'Number') .
                         ' AND `group_id` = ' . to_sql($groupId, 'Number') .
						 ' AND ' . self::getWhere();
                DB::execute($sql);
            }

            //Check online user
            if (!$checkRequest
                && isset($responseData['action']) && $responseData['action'] == 'request'
                && !User::isOnline($responseData['user_id'], null, true)) {
                $responseData = array();
            }

            if (!empty($responseData)) {
                $user = User::getInfoBasic($responseData['user_id']);
                $responseData['user_name'] = User::nameShort($user['name']);
				$responseData['city'] = $user['city'];
                $responseData['request_city'] = $requestCity;
				if ($requestCity) {
					$responseData['city_user_id'] =  CityUser::getCityUserIdBySityUserId($responseData['user_id']);
				}
                $responseData['age'] = $user['age'];
                $responseData['url'] = User::url($responseData['user_id'], $user);
                $responseData['user_url'] = $responseData['url'];
                $responseData['photo'] = User::getPhotoDefault($responseData['user_id'], 'm');
                if ($groupId) {
                    $groupInfo = Groups::getInfoBasic($groupId);
                    if ($groupInfo) {
                        $responseData['group_id'] = $groupInfo['group_id'];
                        $responseData['group_name'] = $groupInfo['title'];
                        $responseData['group_page'] = $groupInfo['page']*1;
                    }
                } else {
                    unset($responseData['group_id']);
                }
            }
        }
        return $responseData;

    }

    static function checkRequest($type, $uid)
    {

        global $g_user;

        if (!$g_user['user_id'] || !in_array($type, array('audio', 'video')) || !$uid) {
            return false;
        }

        $responseData = self::update($type, 0, true);
        if ($responseData) {
            return $responseData;
        }

        self::setType($type);

        $responseData = array('action' => 'request',
                              'user_id' => $uid);

        $isFreeSite = Common::isOptionActive('free_site');
        $isSuperPowers = User::isSuperPowers();
        if (!$isFreeSite && !$isSuperPowers) {
            $isUpgrade = false;
            $offChatWithPopular = Common::isOptionActive('chat_with_popular_users_off', 'template_options');
            if (Common::isActiveFeatureSuperPowers($type . 'chat')) {
                $isUpgrade = true;
            } else if (!$offChatWithPopular && Common::isActiveFeatureSuperPowers('chat_with_popular_users')){
                $level = User::getLevelOfPopularity($row['from_user']);
                if ($level == 'very_high') {
                    $isUpgrade = true;
                }
            }
            if ($isUpgrade) {
                $requestUri = $type . 'chat.php?id=' . $uid . '&type=' . $type;
                $responseData = array('action' => 'request',
                                      'user_id' => $row['from_user'],
                                      'type' => $type . 'chat',
                                      'request_uri' =>  base64_encode($requestUri));
            }
        }

        $user = User::getInfoBasic($responseData['user_id']);
        if ($user) {
            $responseData['user_name'] = User::nameShort($user['name']);
            $responseData['city'] = $user['city'];
            $responseData['age'] = $user['age'];
            $responseData['url'] = User::url($responseData['user_id'], $user);
            $responseData['user_url'] = $responseData['url'];
            $responseData['photo'] = User::getPhotoDefault($responseData['user_id'], 'm');

            if (isset($responseData['group_id'])) {
                $groupId = $responseData['group_id'];
                $groupInfo = Groups::getInfoBasic($groupId);
                if ($groupInfo) {
                    $responseData['group_id'] = $groupInfo['group_id'];
                    $responseData['group_name'] = $groupInfo['title'];
                    $responseData['group_page'] = $groupInfo['page'];
                } else {
                    unset($responseData['group_id']);
                }
            }
        } else {
            $responseData = false;
        }

        return $responseData;
    }

    static function reject()
    {
        global $g_user;

        $responseData = false;
        $groupId = Groups::getParamId();
        $uid = get_param('user_id');
        self::setType();

        if ($g_user['user_id'] && $uid) {
			$city = get_param_int('city');
            $sql = 'INSERT INTO `' . self::$tableReject . '`
                       SET `to_user` = ' . to_sql($uid, 'Number') . ',
                           `from_user` = ' . to_sql($g_user['user_id'], 'Number') . ",
                           `group_id` = " . to_sql($groupId, 'Number') . ",
						   `city` = " . to_sql($city, 'Number') . ",
                           `go` = 'N'";
            DB::execute($sql);
            $responseData = true;
        }
        return $responseData;
    }

    static function talk()
    {
        global $g_user;

        $responseData = false;
        $groupId = Groups::getParamId();
        $uid = get_param('user_id', get_param('id'));
        self::setType();

        if ($g_user['user_id'] && $uid) {
			$city = get_param_int('city');
            $sql = 'INSERT INTO `' . self::$tableReject .
                    '` SET `to_user` = ' . to_sql($uid, 'Number') . ',
                           `from_user` = ' . to_sql($g_user['user_id'], 'Number') . ",
					       `group_id` = " . to_sql($groupId, 'Number') . ",
						   `city` = " . to_sql($city, 'Number') . ",
						   `go` ='Y'";
       		DB::execute($sql);
            $responseData = true;
        }
        return $responseData;
    }

    static function paid()
    {
        global $g_user;

        $responseData = false;
        $uid = get_param('user_id');
        self::setType();

        $price=Pay::getServicePrice(self::$type.'_chat', 'credits');
        if($price>0 && Common::isCreditsEnabled()){
            if($g_user['credits']>=$price){
                $responseData = $price;
                $data = array('credits' => $g_user['credits']-$price);
                User::update($data);
            } else {
                $responseData = -1;
            }
        }else{
            $responseData = true;
        }
        return $responseData;
    }


    static function getIdByChat($callId, $client = true, $type = '')
    {
        if ($client) {
            $uid = guser('user_id') . '_' . $callId;
        } else {
            $uid = $callId . '_' . guser('user_id');
        }
        if ($type) {
            $type = '_' . $type;
        }
        $key = domain() . '_' . $type . '_' . $uid;
        $key = str_replace(array('.'), '_', $key);
        return $key;
    }

    //never used - may need
    static function parseMediaChat($html, $type = '', $uid = 0, $redirect = false, $alwaysParseChat = true) {
        global $g;
		global $g_user;

        $callUid = intval(get_param('id', $uid));
        $clientId = $g_user['user_id'];

        $sql = "SELECT *
                  FROM `user`
                 WHERE `user_id` = " . to_sql($callUid);
        DB::query($sql);
        $isParseChat = true;
		if ($row = DB::fetch_row()){
            $sql = "DELETE FROM `video_reject`
                     WHERE `to_user` = " . to_sql($g_user['user_id'])
                   . " AND `from_user` = " . to_sql($row['user_id']);
            DB::execute($sql);

			if (User::isOnline($callUid, $row)) {
				#foreach ($row as $k => $v) $html->setvar($k, $v);
				$html->setvar('enemy_name', $row['name']);
				$html->setvar('my_name', $g_user['name']);
			} else {
                $isParseChat = $alwaysParseChat;
                $html->parse('alert_js');
            }
		} elseif ($redirect) {
            Common::toHomePage();
        }

        $typeChat = Common::getOption('type_media_chat');

    	$html->setvar("{$type}_type_chat", $typeChat);

        if ($typeChat == 'webrtc') {
            if (IS_DEMO && get_param('demo')) {
                $html->setvar('demo_url', Common::urlSiteSubfolders());
                $html->setvar('demo_user_gender', mb_strtolower(User::getInfoBasic($callUid, 'gender'), 'UTF-8'));
                $html->setvar('demo', 1);
            }
            $clientId = Chat::getIdByChat($callUid, true, $type);
            $callUid = Chat::getIdByChat($callUid, false, $type);
        }
        $html->setvar("{$type}_client_id", $clientId);
        $html->setvar("{$type}_call_to_id", $callUid);

        if ($isParseChat && $typeChat == 'webrtc') {
            $html->setvar('media_server', $g['media_server']);
            $html->parse("{$type}_chat_webrtc_script", false);
            $html->parse("{$type}_chat_webrtc_js", false);
        }
        $html->parse("{$type}_chat_{$typeChat}", false);
    }

    static function getMediaConstraints() {
        $mediaConstraints = array(
            'width' => array('min' => 'webrtc_camera_resolution_width_min',
                             'ideal' => 'webrtc_camera_resolution_width_ideal',
                             'max' => 'webrtc_camera_resolution_width_max'),
            'framerate' => array('min' => 'webrtc_framerate_min',
                                 'ideal' => 'webrtc_framerate_ideal',
                                 'max' => 'webrtc_framerate_max'),
        );
        foreach ($mediaConstraints as $option => $rows) {
            foreach ($rows as $key => $value) {
                $mediaConstraints[$option][$key] = Common::getOption($value);
            }
        }

        $mediaConstraints = json_encode($mediaConstraints);
        if ($mediaConstraints === false) {
            $mediaConstraints = '{"width":{"min":"640","ideal":"720","max":"1280"},"framerate":{"min":"15","ideal":"18","max":"24"}}';
        }

        return $mediaConstraints;
    }

	function parseBlock(&$html) {

		global $g_user;

        $cmd = get_param('cmd');

        //if (self::$action == 'request') {
            //if (self::parseInvite($html)) {

            //}
        //}
        parent::parseBlock($html);

	}
}