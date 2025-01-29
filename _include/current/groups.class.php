<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Groups {

    static $table = 'groups_social';
    static $tableSubscribers = 'groups_social_subscribers';
    static $responseData = '';
    static $isPages = null;
    static $isContentList = null;

    static function getTypeParam($groupId) {
        $typeParam = '';
        $groupInfo = self::getInfoBasic($groupId);
        if ($groupInfo) {
            $typeParam = 'view=' . ($groupInfo['page'] ? 'group_page' : 'group');
            $typeParam .= '&uid=' . $groupInfo['user_id'];
        }
        return $typeParam;
    }

    static function getParamId_OLD() {
        $id = 0;
        $gid = get_param_int('group_id');
        $view = get_param('view');
        if ($gid && $view == 'group_page') {
            $id = $gid;
        }
        return $id;
    }


    static function getParamId() {
        $key = 'Group_getParamId';
        $id = Cache::get($key);
        if($id !== null) {
            return $id;
        }

        $uidParam = strval(get_param('group_id'));
        $id = intval($uidParam);

        if (!$id || $uidParam !== strval($id)){
            $id = 0;
        }

        if (!$id) {
            $id = 0;
            $nameSeo = get_param('name_seo');
            if ($nameSeo) {
                $groupInfo = self::getInfoFromNameSeo($nameSeo);
                if ($groupInfo) {
                    $id = $groupInfo['group_id'];
                }
            }
		}
        Cache::add($key, $id);

        return $id;
    }

    static function getParamOneId(){
        $groupId = 0;
        if (Common::isOptionActiveTemplate('groups_social_enabled')){
            $groupId = get_param_int('group_im_id');
        }

        return $groupId;
    }

    static function getEventId() {
        $groupIdEvent = 0;
        if (Groups::isMyGroup()) {
            $groupIdEvent = self::getParamId();
        }
        return $groupIdEvent;
    }

    static function isMyGroup() {
        $result = false;
        $groupId = self::getParamId();
        if ($groupId) {
            $groupUid = self::getInfoBasic($groupId, 'user_id');
            $result = $groupUid == guid();
        }
        return $result;
    }

    static function setIsPage($is) {
        self::$isPages = $is;
    }

    static function getIsPage() {
        return self::$isPages;
    }

    static function setTypeContentList($typeContentList = null) {
        if ($typeContentList === null && self::$isContentList === null) {
            $typeContentList = self::getParamTypeContentList();
        }
        if ($typeContentList !== null) {
            self::$isContentList = $typeContentList;
        }
    }

    static function getTypeContentList() {
        return self::$isContentList;
    }

    static function getParamTypeContentList() {
        return get_param('view_list', false);
    }

    static function isPage($typeGroup = null) {
        if (self::$isPages !== null) {
            return self::$isPages;
        }
        if ($typeGroup === null) {
            $typeGroup = get_param('type_group', false);
            if ($typeGroup === false) {
                $typeGroup = get_param('view');
            }
        }
        self::$isPages = $typeGroup == 'page' || $typeGroup == 'group_page';

        return self::$isPages;
    }

    static function isAccessGroup() {

        $key = 'Group_isAccess';
        $result = Cache::get($key);
        if($result !== null) {
            return $result;
        }

        $result = 'no_group';
        $groupId = self::getParamId();
        if ($groupId) {
            if (self::isPage() || self::isMyGroup()) {
                $result = true;
            }
            $guid = guid();
            $groupInfo = self::getInfoBasic($groupId);
            if ($groupInfo['private'] == 'Y' && $groupInfo['user_id'] != $guid && !self::isSubscribeUser($guid, $groupId)) {
                $result = false;
            } else {
                $result = true;
            }
        }

        Cache::add($key, $result);

        return $result;
    }

    static function checkAccessGroup() {
        $isAccessGroup = self::isAccessGroup();
        if ($isAccessGroup !== 'no_group' && !$isAccessGroup) {
            $groupId = self::getParamId();
            redirect(self::url($groupId, null, null, false, true));
        }
    }

    static function setResponseData($name, $validate) {
        self::$responseData .= "<span class='" . $name . "'>" . strip_tags($validate) . '</span>';
    }

    static function updateInfo() {
        $id = get_param_int('group_id');
        if (!$id || !self::getInfoBasic($id)) {
            return false;
        }
        $sql = 'SELECT `group_id` FROM `' . self::$table . '` WHERE group_id = ' . to_sql($id, 'Number');
        if (!DB::result($sql)) {
            return false;
        }
        return self::add($id);
    }


    static function checkNameSeo($nameSeo = null, $id = null) {
        if ($id === null) {
            $id = get_param_int('group_id');
        }
        if ($nameSeo === null) {
            $nameSeo = trim(get_param('name_seo'));
        }

        $nameCensured = censured($nameSeo);
        if ($nameCensured != $nameSeo) {
            return l('public_url_contains_invalid_characters');
        }

        if (preg_match('/[%#&\'"\/\\\\<>*|]/', $nameSeo)) {
            return l('invalid_public_url');
        }

        if (!User::checkNameCompatibilityWithSystem($nameSeo)) {
            return l('public_url_cannot_be_used');
        }

        $nameSeo = Router::getNameSeo($nameSeo, $id, 'group', false);
        if (!$nameSeo) {
            return l('public_url_exists');
        }
        return '';
    }

    static function add($id = 0) {
        global $g_user;

        $title = trim(get_param('title'));
        if (!$title) {
            self::setResponseData('title', l('required_field'));
            return self::$responseData;
        }

        $titleCensured = censured($title);
        if ($titleCensured != $title) {
            self::setResponseData('title', l('title_contains_invalid_characters'));
            return self::$responseData;
        }
		$title = strip_tags($title);

        $guid = guid();
        $isUpdate = $id;

        $description = trim(get_param('description'));
		$description = strip_tags($description);

        $nameSeo = trim(get_param('name_seo'));

        $nameSeoError = self::checkNameSeo($nameSeo, $id);
        if ($nameSeoError) {
            self::setResponseData('name_seo', $nameSeoError);
            return self::$responseData;
        }

		$nameSeo = Router::prepareNameSeo($nameSeo);

        $countryId = get_param_int('country_id', 0);
        $stateId = get_param_int('state_id', 0);
        $cityId = get_param_int('city_id', 0);
        $category_id = get_param('category_id', '');
        $show_owner = get_param('group_show_owner', '');

        $countryTitle = '';
        $stateTitle = '';
        $cityTitle = '';
        if ($countryId) {
            $countryTitle = Common::getLocationTitle('country', $countryId);
            if ($countryId && $stateId) {
                $stateTitle = Common::getLocationTitle('state', $stateId);
                if ($cityId) {
                    $cityTitle = Common::getLocationTitle('city', $cityId);
                }
            }
        }

        $isGroupPage = get_param_int('is_page');
        if ($isGroupPage) {
            $private = 'N';
        } else {
            $private = get_param('private', 'N');
        }
        $date = date('Y-m-d H:i:s');
        $vars = array(
            'page' => $isGroupPage,
            'private' => $private,
            'title' => $title,
            'description' => $description,
            'name_seo' => $nameSeo,
            'country' => $countryTitle,
            'country_id' => $countryId,
            'state' => $stateTitle,
            'state_id' => $stateId,
            'city' => $cityTitle,
            'city_id' => $cityId,
            'user_id' => $guid,
            'category_id' => $category_id,
            'show_owner' => $show_owner
        );

        if (!$id) {
            $vars['date'] = $date;
        }

        if ($id) {
            $accessPrivate = self::getInfoBasic($id, 'private');
            self::update($vars, $id);
                if ($accessPrivate != $private) {
                    if ($private == 'Y') {
                        //Delete share
                        DB::delete('wall', "section = 'share' AND group_id = " . to_sql($id));
                        //Update status
                        $sql = "UPDATE `wall` SET `access` = 'friends' WHERE `group_id` = " . to_sql($id);
                        DB::execute($sql);
                        //Remove subscribers(liked)
                        DB::delete(self::$tableSubscribers, 'group_id = ' . to_sql($id));
                        //Remove Im
                        $usersIm = DB::rows('SELECT `from_user` FROM `im_open` WHERE `to_group_id` = ' . to_sql($id) . ' AND `to_user` = ' . to_sql($guid));
                        foreach ($usersIm as $key => $userIm) {
                            $_GET['group_im_id'] = $id;
                            $_GET['to_group_id'] = $id;

                            $g_user['user_id'] = $userIm['from_user'];
                            CIm::closeIm($guid);

                            $_GET['from_group_id'] = $id;
                            $_GET['to_group_id'] = 0;
                            $g_user['user_id'] = $guid;
                            CIm::closeIm($userIm['from_user']);
                        }
                    }
                    
                    DB::update(self::$tableSubscribers, array('group_private' => $private), 'group_id = ' . to_sql($id));
                    DB::update('photo', array('group_private' => $private), 'group_id = ' . to_sql($id));
                    DB::update('vids_video', array('group_private' => $private), 'group_id = ' . to_sql($id));
                }
            } else {
                DB::insert(self::$table, $vars);
                $id = DB::insert_id();
                if (!$isGroupPage) {
                    DB::update('photo', array('group_private' => $private), 'group_id = ' . to_sql($id));
                    DB::update('vids_video', array('group_private' => $private), 'group_id = ' . to_sql($id));
                }
                self::subscribeAction($guid, $id, 'request');

                Wall::add('group_social_created', $id);
            }

        $photoId = get_param_int('photo_id');
        if ($photoId) {
            CProfilePhoto::publishPhotos(array(array('id' => $photoId, 'desc' => '')), 'public', $id);
            if ($isUpdate) {
                GroupsPhoto::photoToDefault($photoId, $id);
            }
        }

        self::updateTags($id);
        $msgAlert = $isGroupPage ? 'your_new_page_has_been_created' : 'your_new_group_has_been_created';
        if ($isUpdate) {
            $msgAlert = $isGroupPage ? 'your_page_has_been_updated' : 'your_group_has_been_updated';
        }
        set_session('alert_after_page_loaded', $msgAlert);

        self::setResponseData('redirect', self::url($id, $vars));
        return self::$responseData;
    }

    static function getInfoFromNameSeo($nameSeo) {
        $key = 'group_info_from_name_seo_' . $nameSeo;
        $uid = Cache::get($key);
        if($uid === null) {
            $sql = 'SELECT * FROM ' . self::$table . ' WHERE `name_seo` = ' . to_sql($nameSeo);
            $uid = DB::row($sql, 0, DB_MAX_INDEX);
            Cache::add($key, $uid);
        }

        return $uid;
    }

    static function getInfoBasic($id, $field = false, $dbIndex = 0, $cache = true) {
        $keyField = $field ? $field : 0;
        $key = 'groupinfo_' . $id . '_' . $keyField;
        $return = $info = null;
		if($id) {
			if ($cache) {
				$info = Cache::get($key);
			}
			if($info === null) {
				$sql = 'SELECT * FROM `' . self::$table . '` WHERE group_id = ' . to_sql($id, 'Number');
				$info = DB::row($sql, $dbIndex);

				Cache::add($key, $info);
			}

			$return = $info;

			if($field !== false) {
				$return = isset($info[$field]) ? $info[$field] : '';
			}
		}

        return $return;
    }

    static function update($data, $id = null) {
        if($id === null) {
            $id = self::getParamId();
        }
        if(!$id) {
            return;
        }
        DB::update(self::$table, $data, 'group_id = ' . to_sql($id));

        $key = 'groupinfo_' . $id . '_0';
        $info = Cache::get($key);
        if($info) {
            $info = array_merge($info, $data);
            Cache::add($key, $info);
        }
    }

    static function updateCountPosts($id)
    {
        $sql = "SELECT COUNT(*) FROM `wall` AS w
            WHERE `group_id` = " . to_sql($id) . " AND w.section IN ('comment','photo','vids','pics','group_social_created','photo_default','blog_post','music')";

        $data = array(
            'count_posts' => DB::result($sql),
        );
        self::update($data, $id);
    }

    static function updateCountComments($id)
    {
        $data = array(
            'count_comments' => DB::count('wall_comments', '`group_id` = ' . to_sql($id)),
        );
        self::update($data, $id);
    }

    static function getActiveTabsAlias() {
        $defaultProfileTab = Common::getOption('set_default_groups_tab', 'edge');
        $profileTabUrl = false;
        if ($defaultProfileTab == 'menu_inner_videos_edge') {
            $profileTabUrl = 'group_vids_list';
        } elseif ($defaultProfileTab == 'menu_inner_photos_edge') {
            $profileTabUrl = 'group_photos_list';
        } elseif ($defaultProfileTab == 'menu_inner_subscribers_edge') {
            $profileTabUrl = 'user_friends_list';
        }
        return $profileTabUrl;
    }

    static function url($id, $groupInfo = null, $params = null, $isCache = true, $default = false) {

        $optionTmplName = Common::getTmplName();
        if ($optionTmplName == 'edge' && !$default) {
            $profileTabUrl = self::getActiveTabsAlias();
            if ($profileTabUrl) {
                $key = 'group_seo_friendly_url_tab_' . $id;
                $url = null;
                if ($isCache) {
                    $url = Cache::get($key);
                }
                if ($url === null) {
                    $url = Common::pageUrl($profileTabUrl, $id);
                    Cache::add($key, $url);
                }
                return $url;
            }
        }

        if (Common::isOptionActive('seo_friendly_urls')) {
            $paramsAddSymbol = '?';
            $key = 'group_seo_friendly_url_' . $id;
            $url = null;
            if ($isCache) {
                $url = Cache::get($key);
            }
            if ($url === null) {
                if ($groupInfo === null || !isset($groupInfo['name_seo'])) {
                    $groupInfo = self::getInfoBasic($id, false, DB_MAX_INDEX);
                }
                $nameSeo = $groupInfo['name_seo'];
                if (!$nameSeo) {
                    $nameSeo = Router::getNameSeo($groupInfo['title']);
                    $data = array('name_seo' => $nameSeo);
                    self::update($data, $id);
                }
                $url = $nameSeo;
                Cache::add($key, $url);
            }
        } else {
            $paramsAddSymbol = '&';
            $key = 'group_url_' . $id;
            $url = null;
            if ($isCache) {
                $url = Cache::get($key);
            }
            if ($url === null) {
                if ($groupInfo === null) {
                    $groupInfo = self::getInfoBasic($id, false, DB_MAX_INDEX);
                }
                $typeGroupParam = self::getTypeParam($id);
                $url = 'search_results.php?display=profile&group_id=' . $id . '&' .  $typeGroupParam;
            }

        }

        $url .= $params ? $paramsAddSymbol . http_build_query($params) : '';

        return $url;
    }

    static function pageUrl($uid = 0) {

        if ($uid) {
            $key = self::isPage() ? 'user_pages_list' : 'user_groups_list';
        } else {
            $key = self::isPage() ? 'pages_list' : 'groups_list';
        }

        return Common::pageUrl($key, $uid);
    }

    /* Tags */
    static public function getTags($gid) {
        $sql = 'SELECT TR.tag_id, T.tag
                  FROM `groups_social_tags_relations` as TR
                  LEFT JOIN `groups_social_tags` as T ON TR.tag_id = T.id
                 WHERE TR.group_id = ' . to_sql($gid) . ' ORDER BY T.id';
        $tagsGroup = DB::all($sql);
        $tags = array();
        if ($tagsGroup) {
            foreach ($tagsGroup as $key => $tag) {
                $tags[$tag['tag_id']] = $tag['tag'];
            }
        }
        return $tags;
    }

    static public function getTagsView($groupId, $title = true) {
        $tags = Groups::getTags($groupId);
        $tagsTitle = '';
        $tagsHtml = '';
        if ($tags) {
            $isPage = intval(self::getInfoBasic($groupId, 'page'));
            $pageList = $isPage ? Common::pageUrl('pages_list') : Common::pageUrl('groups_list');
            foreach ($tags as $id => $tag) {
                $tagsHtml .= ', <a href="' . $pageList . '?tag=' . $id . '">' . $tag . '</a>';
                $tagsTitle .= ', ' . $tag;
            }
            $tagsHtml = substr($tagsHtml, 1);
            $tagsTitle = substr($tagsTitle, 1);
        }
        if ($title) {
            return trim($tagsTitle);
        } else {
            return trim($tagsHtml);
        }
    }

    static public function getTagInfo($id)	{
        if (!$id) {
            return false;
        }
        $tag = DB::one('groups_social_tags', '`id` = ' . to_sql($id));

        return $tag;
    }

    static public function updateTags($groupId = null){
        $guid = guid();
        if ($groupId === null) {
            $groupId = get_param_int('photo_id');
        }
        if (!$guid || !$groupId) {
            return false;
        }

        $table = 'groups_social';
        $fieldId = 'group_id';
        $tableTags = 'groups_social_tags';
        $tableTagsRelations = 'groups_social_tags_relations';
        $fieldRelationsId = 'group_id';

        $sql = "SELECT `{$fieldId}`
                  FROM `{$table}`
                 WHERE `{$fieldId}` = " . to_sql($groupId)
               . ' AND `user_id` = ' . to_sql($guid);
        if (!DB::result($sql)) {
            return false;
        }

        $result = array();
        $tags = trim(get_param('tags'));
        $result['tags_title'] = $tags;
        $tags = explode(',', $tags);
        $tags = array_map('trim', $tags);
        $result['tags'] = $tags;
        $tagsSql = array_map('to_sql', $tags);

        $tagsTemp = array();
        $tagsDelete = array();

        $tagsExists = DB::select($tableTags, '`tag` IN (' . implode(',', $tagsSql) . ')');
        $tagsExistsCount = array();
        foreach ($tagsExists as $key => $item) {
            $tagsTemp[$item['id']] = $item['tag'];
            $tagsExistsCount[$item['id']] = $item['counter'];
        }
        $tagsExists = $tagsTemp;

        $sql = "SELECT TR.tag_id, T.counter
                  FROM `{$tableTagsRelations}` as TR
                  LEFT JOIN `{$tableTags}` as T ON TR.tag_id = T.id
                 WHERE TR.{$fieldRelationsId} = " . to_sql($groupId);
        $tagsGroup = DB::all($sql);
        if ($tagsGroup) {
            $tagsTemp = array();
            foreach ($tagsGroup as $key => $item) {
                $tagsTemp[$item['tag_id']] = $item['counter'];
            }
            $tagsGroup = $tagsTemp;

            foreach ($tagsGroup as $id => $count) {
                if (!isset($tagsExists[$id])) {
                    $tagsDelete[$id] = $count;
                }
            }
        }

        $tagsUpdate = array();
        foreach ($tags as $key => $tag) {
            if (!$tag) {
                unset($tags[$key]);
                continue;
            }
            $id = array_search($tag, $tagsExists);
            if ($id) {
                unset($tags[$key]);
                if (!isset($tagsGroup[$id])) {
                    $tagsUpdate[$id] = 1;
                }
            }
        }

        if ($tags) {
            foreach ($tags as $key => $value) {
                DB::insert($tableTags, array('tag' => $value, 'counter' => 1));
                $id = DB::insert_id();
                DB::insert($tableTagsRelations, array("{$fieldRelationsId}" => $groupId, 'tag_id' => $id));
            }
        }

        if ($tagsDelete) {
            foreach ($tagsDelete as $id => $count) {
                DB::delete($tableTagsRelations, "`{$fieldRelationsId}` = " . to_sql($groupId) . ' AND `tag_id` = ' . to_sql($id));
                if (intval($count) > 1) {
                    DB::execute("UPDATE {$tableTags} SET counter = counter - 1 WHERE id=" . to_sql($id));
                } else {
                    DB::delete($tableTags, '`id` = ' . to_sql($id));
                }
            }
        }

        if ($tagsUpdate) {
            foreach ($tagsUpdate as $id => $count) {
                DB::insert($tableTagsRelations, array("{$fieldRelationsId}" => $groupId, 'tag_id' => $id));
                DB::execute("UPDATE {$tableTags} SET counter = counter + 1 WHERE id=" . to_sql($id));
            }
        }

        $tags = self::getTags($groupId);

        $tagsHtml = '';
        foreach ($tags as $id => $tag) {
            $tagsHtml .= ' <a href="' . Common::pageUrl('pages_list') . '?tag=' . $id . '">' . $tag . '</a>';
        }
        $result['tags_html'] = $tagsHtml;

        return $result;
    }
    /* Tags */

    /* Subscribe */
    static function isSubscribeOrCreatedUser($uid, $groupId, $cash = true) {
        $groupUserId = self::getInfoBasic($groupId, 'user_id', DB_MAX_INDEX, $cash);
        if ($groupUserId == $uid) {
            return true;
        }
        $sql = 'SELECT `accepted` FROM `' . self::$tableSubscribers . '`
                 WHERE `user_id` = ' . to_sql($uid) . '
                   AND `group_id` = ' . to_sql($groupId);
        return DB::result($sql, 0, DB_MAX_INDEX, $cash);
    }

    static function isSubscribeUser($uid, $groupId, $cash = true) {
        $sql = 'SELECT `accepted` FROM `' . self::$tableSubscribers . '`
                 WHERE `user_id` = ' . to_sql($uid) . '
                   AND `group_id` = ' . to_sql($groupId);
        return DB::result($sql, 0, DB_MAX_INDEX, $cash);
    }

    static function getSubscribeRequestInfo($uid, $groupId) {
        $where = '`user_id` = ' . to_sql($uid) . '
              AND `group_id` = ' . to_sql($groupId);
        return DB::one(self::$tableSubscribers, $where);
    }

    static function getNumberRequestsToSubscribePending($groupId = null) {
        $sql = "SELECT COUNT(*)
                  FROM `" . self::$tableSubscribers . "`
                 WHERE `group_id` = " . to_sql($groupId) .
                 " AND `accepted` = 0";
		return DB::result($sql);
    }

    static function deleteSubscribe($uid, $groupId) {
        $where = '`user_id` = ' . to_sql($uid) . '
              AND `group_id` = ' . to_sql($groupId);
        return DB::delete(self::$tableSubscribers, $where);
    }

    static function approveSubscribe($uid, $groupId, $accepted = 1) {
        $groupInfo = self::getInfoBasic($groupId);

        $where = '`user_id` = ' . to_sql($uid) . '
              AND `group_id` = ' . to_sql($groupId);
        $data = array(
                    'group_private' => $groupInfo['private'],
                    'accepted' => $accepted,
                    'approve_at' => date('Y-m-d H:i:s'),
                    'is_new' => 1
                );


        $groupInfo = self::getInfoBasic($groupId);

        if (Common::isEnabledAutoMail('group_subscribe_new')
                && $groupInfo['user_id'] != $uid) {

            $typeMail = 'group_subscribe_new';

            $userInfo = User::getInfoBasic($groupInfo['user_id']);

            $vars = array('name' => guser('name'),
                          'group_url' => Common::urlSite() . self::url($groupId, $groupInfo),
                          'group_title' => $groupInfo['title'],
                          'uid' => $uid
            );
            Common::sendGroupAutomail($userInfo['lang'], $userInfo['mail'], $typeMail, $vars, $groupInfo);
        }

        return DB::update(self::$tableSubscribers, $data, $where);
    }

    static function subscribeRequestAdd($uid, $groupId, $accepted = false, $isSendMail = true) {
        $groupInfo = self::getInfoBasic($groupId);
        if ($accepted === NULL) {
            $accepted = intval($groupInfo['page'] || $groupInfo['private'] == 'N');
        }

        $date = date('Y-m-d H:i:s');
        $sql = "INSERT IGNORE INTO `" . self::$tableSubscribers . "`
                   SET `user_id` = " . to_sql($uid) .
                    ", `group_id` = " . to_sql($groupId) .
                    ", `group_user_id` = " . to_sql($groupInfo['user_id']) .
                    ", `group_private` = " . to_sql($groupInfo['private']) .
                    ", `page` = " . to_sql($groupInfo['page']) .
                    ", `accepted` = " . to_sql($accepted) .
                    ", `is_new` = 1" .
                    ", `created_at` = " . to_sql($date);

        if($accepted){
            $sql .= ", `approve_at` = " . to_sql($date) .
                    " ON DUPLICATE KEY UPDATE `approve_at` = " . to_sql($date) .
                                           ", `group_private` = " . to_sql($groupInfo['private']);
        }
        
        DB::execute($sql);
        $subscriber_id = DB::insert_id();
        Wall::add('group_join', $subscriber_id, $uid);

        if ($isSendMail && Common::isEnabledAutoMail('group_subscribe_request')
                && $groupInfo['user_id'] != $uid && !$groupInfo['page']) {

            $typeMail = 'group_subscribe_new';
            if (!$accepted) {
                $typeMail = 'group_subscribe_request';
                $uid = $groupInfo['user_id'];
            }

            $userInfo = User::getInfoBasic($groupInfo['user_id']);

            $vars = array('name' => guser('name'),
                          'group_url' => Common::urlSite() . self::url($groupId, $groupInfo),
                          'group_title' => $groupInfo['title'],
                          'uid' => $uid
            );
            Common::sendGroupAutomail($userInfo['lang'], $userInfo['mail'], $typeMail, $vars, $groupInfo);
        }
    }

    static function subscribeAction($uid = null, $groupId = null, $action = null) {
        if ($uid === null) {
            $uid = guid();
        }

        if ($groupId === null) {
            $groupId = get_param_int('group_id');
        }

        $groupInfo = self::getInfoBasic($groupId);
        if (!$groupInfo) {
            return false;
        }

        $isPage = $groupInfo['page'] || $groupInfo['private'] == 'N';
        if ($action === null) {
            $action = get_param('action');
        }

        $requestInfo = self::getSubscribeRequestInfo($uid, $groupId);
        if (!$action) {
            return false;
            if ($requestInfo) {
                $action = 'remove';//Page
                if(!$isPage) {
                    if(!$requestInfo['accepted']){
                        $action = 'remove_request';
                    }
                }
            } else {
                $action = 'request';
            }
        }
        if (!$action || !$groupId || !$uid) {
            return false;
        }

        $actionResponse = false;
        if ($action == 'approve') {
            if ($requestInfo && $requestInfo['user_id'] != $uid) {
                return false;
            }
            $actionResponse = 'remove';
            self::approveSubscribe($uid, $groupId);
        } elseif ($action == 'request') {
            $actionResponse = $isPage ? 'approve' : 'remove_request';
            if ($requestInfo) {
                if (!$isPage && $requestInfo['accepted']) {
                    $actionResponse = 'approve';
                }
            } else {
                $accepted = NULL;
                if ($uid == $groupInfo['user_id']) {
                    $accepted = 1;
                    $actionResponse = 'approve';
                }
                self::subscribeRequestAdd($uid, $groupId, $accepted);
            }
        } elseif ($action == 'remove' || $action == 'remove_request') {
            $actionResponse = 'request';
            self::deleteSubscribe($uid, $groupId);
        }

        if ($actionResponse) {
            $view = get_param('view');
            $getCounterPending = get_param_int('get_counter_pending');
            $data = array(
                        'group_id' => $groupId,
                        'group_private' => $groupInfo['private'],
                        'action' => $actionResponse,
                        'counter' => self::getNumberSubscribers($groupId),
                        'list_subscribe' => TemplateEdge::getListFriends($uid, false, $groupId)
                    );
            if ($view == 'group') {
                //$data['counter'] = self::getNumberSubscribers($groupId);
                //$data['list_subscribe'] = TemplateEdge::getListFriends($uid, false, $groupId);
                if ($getCounterPending) {
                    $data['counter_pending'] = self::getNumberRequestsToSubscribePending($groupId);
                }
            } else {
                if ($getCounterPending) {
                    $data['counter_pending'] = TemplateEdge::getNumberFriendsAndSubscribersPending();
                }
            }

        } else {
            $data = false;
        }

        return $data;
    }

    static function getSubscribePending($groupId, $where = '', $order = 'DESC')
    {
        global $g;

        $sql = "SELECT FR.*, CU.name, CU.name_seo
                  FROM `" . self::$tableSubscribers . "` AS FR
                  LEFT JOIN `user` AS CU ON CU.user_id = FR.user_id
                 WHERE FR.group_id = " . to_sql($groupId) .
                 " AND FR.accepted = 0 " . $where . " ORDER BY FR.created_at {$order}";
        $result = DB::rows($sql);
        $friendsPending = array();
        $groupName = self::getInfoBasic($groupId, 'title');
        foreach ($result as $key => $item) {
            $urlUserPending = User::url($item['user_id'], array('name' => $item['name'], 'name_seo' => $item['name_seo']));
            $vars = array('name' => User::nameOneLetterFull($item['name']),
                          'url'  => $urlUserPending,
                          'group_title' => $groupName
            );
            $title = Common::lSetLink('wants_to_join_the_group_page_group', $vars);
            $friendsPending[] = array(
                'group_id' => $groupId,
                'user_id'  => $item['user_id'],
                'user_id_sel'  => $item['user_id'] . '_' . $groupId,
                'title'    => $title,
                'created'  => $item['created_at'],
                'url'      => $urlUserPending,
                'photo'    => $g['path']['url_files'] . User::getPhotoDefault($item['user_id']),
                'btn_approve' => l('group_approve_join'),
                'btn_reject'  => l('group_reject_join'),
            );
        }
        return $friendsPending;
    }

    static function getListSubscribers($groupId = null, $online = false, $limit = '', $fidIndex = false, $search_query = null) {
        global $g;

        if ($groupId == null) {
            $groupId = Groups::getParamId();
        }

        $key = 'Groups_getListSubscribers_' . $groupId . '_' . intval($online) . '_' . intval($fidIndex) . ($limit ? '_' . $limit : '_all');
        $subscribersList = Cache::get($key);
        if ($subscribersList !== null) {
            return $subscribersList;
        }

        if (!$groupId) {
            Cache::add($key, array());
            return array();
        }

        if ($limit) {
            $limit = ' LIMIT ' . $limit;
        }

        // var_dump($limit); die();

        $whereOnline = '';
        if ($online) {
            $whereOnline = ' AND U.last_visit > ' . to_sql(date('Y-m-d H:i:s', time() - Common::getOption('online_time') * 60)) . ' ';
        }

        $search_query_where = "";

        if($search_query) {
            if($search_query == 'moderator') {
                $search_query_where = " AND F.group_id = " . to_sql($groupId, 'Text') . " AND F.group_moderator = 'Y' ";
            } elseif ($search_query == 'owner') {
                $search_query_where = " AND F.user_id = F.group_user_id";
            } else {
                $search_query_where = " AND U.name LIKE " . to_sql("%" . $search_query . "%", 'Text'). " ";
            }
        }

        $sql = "SELECT F.*, U.*, G.page, G.user_id AS group_user_id,
                       DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(U.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(U.birth, '00-%m-%d')) AS age
                   FROM `" . self::$tableSubscribers . "` as F
                   JOIN `user` AS U ON U.user_id = F.user_id
                    JOIN `" . self::$table . "` AS G ON G.group_id = F.group_id
                  WHERE F.accepted = 1
                    AND F.group_id = " . to_sql($groupId) . $whereOnline . $search_query_where .  '
                  ORDER BY approve_at DESC, id DESC' . $limit;
                  
        $sql = "SELECT F.*, U.*, G.page, G.user_id AS group_user_id,
               DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(U.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(U.birth, '00-%m-%d')) AS age
           FROM `" . self::$tableSubscribers . "` AS F
           JOIN `user` AS U ON U.user_id = F.user_id
           JOIN `" . self::$table . "` AS G ON G.group_id = F.group_id
          WHERE F.accepted = 1
            AND F.group_id = " . to_sql($groupId) . $whereOnline . $search_query_where . '
          ORDER BY (F.user_id = G.user_id) DESC, approve_at DESC, id DESC' . $limit;

                  // var_dump($sql); die();

        $fetchType = DB::getFetchType();
        DB::setFetchType(MYSQL_ASSOC);
        $subscribersList = DB::rows($sql, 5, true);
        DB::setFetchType($fetchType);
        if ($fidIndex) {
            $result = array();
            foreach ($subscribersList as $key => $item) {
                $suid = $item['user_id'];
                $result[$suid] = $item;
                $photo = $g['path']['url_files'] .  User::getPhotoDefault($item['user_id'], 's', false, $item['gender']);
                $result[$suid]['friend_photo'] = $photo;
                $result[$suid]['friend_url'] = User::url($suid);
                $result[$suid]['friend_name'] = $item['name'];
            }
            $subscribersList = $result;
        }
        Cache::add($key, $subscribersList);

        return $subscribersList;
    }

    static function getNumberSubscribersOnline($groupId = null) {

        if ($groupId == null) {
            $groupId = Groups::getParamId();
        }

        $whereOnline = ' AND U.last_visit > ' . to_sql(date('Y-m-d H:i:s', time() - Common::getOption('online_time') * 60)) . ' ';

        $sql = 'SELECT COUNT(*)
                  FROM `' . self::$tableSubscribers . '` as F
                  JOIN `user` AS U ON U.user_id = F.user_id
                 WHERE F.accepted = 1
                   AND F.group_id = ' . to_sql($groupId) . $whereOnline;

        return DB::result($sql);
    }

    static function getNumberModerator($groupId = null) {
        $sql = "SELECT COUNT(*) FROM `groups_social_subscribers` as gs WHERE gs.group_moderator = 'Y' AND gs.group_id = " . to_sql($groupId, 'Text');
        return DB::result($sql);
    }

    static function getNumberSubscribers($groupId = null, $search_query = null) {

        if ($groupId == null) {
            $groupId = Groups::getParamId();
        }

        $sql = 'SELECT COUNT(*) FROM `' . self::$tableSubscribers . '`
                 WHERE `group_id` = ' . to_sql($groupId) . '
                   AND `accepted` = 1';

        if($search_query) {
            $sql = 'SELECT COUNT(*) FROM `' . self::$tableSubscribers . '` as g LEFT JOIN user as u ON u.user_id = g.user_id WHERE `group_id` = ' . to_sql($groupId) . '
               AND `accepted` = 1' . ' AND u.name LIKE ' . to_sql("%" . $search_query . "%", 'Text') . ''; 
        }
        return DB::result($sql);
    }

    static function getUserGroupsSubscribers($uid = null, $includeCreateGroup = false) {

        if ($uid === null) {
            $uid = guid();
        }

        $groupList = '';
        $groupListGreated = array();
        if ($includeCreateGroup) {
            $sql = 'SELECT `group_id` FROM `' . self::$table . '` WHERE user_id = ' . to_sql($uid, 'Number');
            $rowsCreated = DB::rows($sql, DB_MAX_INDEX);

            if($rowsCreated) {
                foreach ($rowsCreated as $row) {
                    $groupListGreated[$row['group_id']] = 1;
                    $groupList .= ',' . $row['group_id'];
                }
            }
        }

        $sql = 'SELECT `group_id` FROM `' . self::$tableSubscribers . '`
                 WHERE `user_id` = ' . to_sql($uid) . ' AND accepted = 1';
        $rows = DB::rows($sql, DB_MAX_INDEX, true);
        if($rows) {
            foreach ($rows as $row) {
                if (!isset($groupListGreated[$row['group_id']])) {
                    $groupList .= ',' . $row['group_id'];
                }
            }
        }
        if ($groupList) {
            $groupList = substr($groupList, 1);
        }

        return $groupList;
    }

    static function getSubscribersListGroup($groupId, $uid = 0, $include = false) {
        $key = 'getSubscribersListGroup_' . $groupId . '_' . intval($include);
        $subscribersList = Cache::get($key);
        if($subscribersList === null) {
            $subscribersList = self::friendsList($groupId, $uid, $include);
            Cache::add($key, $subscribersList);
        }

        return $subscribersList;
    }

    static function getSubscribersList($groupId, $uid = 0, $include = false) {
        if($include) {
            $uids = $uid;
        } else {
            $uids = 0;
        }

        $sql = 'SELECT `user_id` FROM `' . self::$tableSubscribers . '`
                 WHERE `group_id` = ' . to_sql($groupId) . ' AND accepted = 1';

        $rows = DB::rows($sql, DB_MAX_INDEX, true);

        if($rows) {
            foreach ($rows as $row) {
                $uids .= ',' . $row['user_id'];
            }
        }

        return $uids;
    }

    static function getUserListGroupsSubscribers($uid = null, $includeUserGroup = true, $onlyPrivateGroup = true, $isGroupPages = false) {
        if ($uid === null) {
            $uid = guid();
        }
        $uids = 0;

        $groupsList = array();
        if($includeUserGroup) {
            $where = '';
            if ($onlyPrivateGroup) {
                $where = ' AND `private` = "Y"';
            }
            if ($isGroupPages) {
                $where = ' AND `page` = 1';
            }
            $sql = 'SELECT `group_id` FROM `' . self::$table . '`
                     WHERE `user_id` = ' . to_sql($uid) . $where;
            $rows = DB::rows($sql, DB_MAX_INDEX, true);
            foreach ($rows as $row) {
                $groupsList[$row['group_id']] = 1;
            }
        }
        $sql = 'SELECT `group_id` FROM `' . self::$tableSubscribers . '`
                 WHERE `user_id` = ' . to_sql($uid) . ' AND accepted = 1';
        $rows = DB::rows($sql, DB_MAX_INDEX, true);
        foreach ($rows as $row) {
            $groupsList[$row['group_id']] = 1;
        }
        if($groupsList) {
            foreach ($groupsList as $groupId => $row) {
                $uids .= ',' . $groupId;
            }
        }
        return $uids;
    }

    /* Subscribe */

    /* Report */
    static function sendReport($groupId = null) {
        $uid = guid();

        if ($groupId === null) {
            $groupId = self::getParamId();
        }
        $userTo = get_param('user_to');
        if (!$uid || !$userTo || !$groupId) {
            return false;
        }

        $groupInfo = self::getInfoBasic($groupId);

        if(!self::isReport($groupId)) {
            $data = array('user_from' => $uid,
                          'user_to' => $userTo,
                          'msg' => get_param('msg'),
                          'group_id' => $groupId);
            DB::insert('users_reports', $data);

			if(Common::isEnabledAutoMail('report_group_admin')) {
                $vars = array(
                    'name' => User::getInfoBasic($uid,'name')
                );

                Common::sendGroupAutomail(Common::getOption('administration', 'lang_value'), Common::getOption('info_mail', 'main'), 'report_group_admin', $vars, $groupInfo);
			}
        }

        return true;
    }

    static function isReport($groupId, $uid = null, $groupInfo = null) {
        if ($uid === null) {
            $uid = guid();
        }

        if ($groupInfo !== null && isset($groupInfo['users_reports'])) {
            $is = in_array($uid, explode(',', $groupInfo['users_reports']));
            return intval($is);
        }

        $whereReport = '`user_from` = ' . to_sql($uid)
                    . ' AND `group_id` = ' . to_sql($groupId)
                    . ' AND `photo_id` = 0'
                    . ' AND `video` = 0'
                    . ' AND `wall_id` = 0'
                    . ' AND `comment_id` = 0';
        return DB::count('users_reports', $whereReport, '', '', '', DB_MAX_INDEX, true);
    }
    /* Report */

    static function isBan($groupId = 0) {
        if (!$groupId) {
            return;
        }
        $groupInfo = self::getInfoBasic($groupId);
		if ($groupInfo && $groupInfo['ban_global'] == 1) {
            $msgAlert = $groupInfo['page'] ? 'page_banned_by_site_administrator' : 'group_banned_by_site_administrator';
            set_session('alert_after_page_loaded', $msgAlert);

			Common::toHomePage();
		}
    }

    static function ban($groupId) {
        $sql = 'UPDATE ' . self::$table . '
                   SET `ban_global` = 1 - `ban_global`
                 WHERE `group_id` = '. to_sql($groupId, 'Number');
        DB::execute($sql);
    }


    static function getCountPosts($uid, $groupId)
    {
        $uidWall = Wall::getUid();

        Wall::setUid($uid);

        $html = null;
        $count = Wall::parseItems($html, false, false, false, true, $groupId);
        Wall::setUid($uidWall);

        return $count;
    }

    static function deleteUserGroups($uid) {
        $sql = 'SELECT `group_id` FROM `' . self::$table . '`
                 WHERE `user_id` = ' . to_sql($uid);
        $groups = DB::column($sql);
        foreach($groups as $groupId) {
            self::delete($groupId);
        }
    }

    static function delete($groupId, $admin = false) {
        global $g;
        global $g_user;

        if (!$groupId) {
            return false;
        }

        $groupInfo = self::getInfoBasic($groupId);
        if (!$groupInfo) {
            return false;
        }

        if ($admin) {
            $g_user['user_id'] = $groupInfo['user_id'];
        }

        $guid = guid();

        if ($groupInfo['user_id'] != $guid) {
            return false;
        }

        $user_id = $guid;

        $where = '(`user_from` = ' . to_sql($user_id) . ' OR `user_to` = ' . to_sql($user_id) . ') AND `group_id` = ' . to_sql($groupId);
        DB::delete('users_reports', $where);

        $where = '(`from_user` = ' . to_sql($user_id) . ' OR `to_user` = ' . to_sql($user_id) . ') AND `group_id` = ' . to_sql($groupId);
        $tables = array('audio_invite',
                        'audio_reject',
                        'video_invite',
                        'video_reject');
        foreach ($tables as $key => $table) {
            DB::delete($table, $where);
        }

        $whereIm = '`from_group_id` = ' . to_sql($groupId) . ' OR `to_group_id` = ' . to_sql($groupId);
        DB::delete('im_msg', $whereIm);
        DB::delete('im_open', $whereIm);

        //DB::execute("DELETE FROM users_block WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");

        deletephotos($user_id, $groupId);

		User::clearProfileBgCover($user_id, $groupId);

        require_once('vids/tools.php');
        $ids = DB::column('SELECT id FROM `vids_video` WHERE `user_id` = ' . to_sql($user_id) . ' AND `group_id` = ' . to_sql($groupId));
        foreach($ids as $id) {
            CVidsTools::delVideoById($id, true);
        }

        $groupTags = DB::column('SELECT id FROM `groups_social_tags_relations` WHERE `group_id` = ' . to_sql($groupId));
        foreach($groupTags as $id) {
            DB::delete('groups_social_tags', '`id` = ' . to_sql($id) . ' AND `counter` = 1');
        }
        DB::delete('groups_social_tags_relations', '`group_id` = ' . to_sql($groupId));


        Wall::removeByUid($user_id, 0, $groupId);
        //group_social_created
        $sql = "SELECT `id` FROM `wall` WHERE section = 'group_social_created' AND item_id = " . to_sql($groupId);
        $idCreated = DB::result($sql);
        if ($idCreated) {
            Wall::removeById($idCreated);
        }

        $whereGroup = '`group_id` = ' . to_sql($groupId);
        $tables = array('groups_social_subscribers',
                        'groups_social');
        foreach ($tables as $key => $table) {
            DB::delete($table, $whereGroup);
        }

        $isPage = $groupInfo['page'];
        $count = GroupsList::getTotalGroups($guid, $isPage);
        $type = $isPage ? 'pages' : 'groups';
        $lTitle = "edge_column_{$type}_title";
        if ($guid != $groupInfo['user_id']) {
            $lTitle = "edge_column_{$type}_title_other_user";
        }

        $response = array(
            'group_id' => $groupId,
            'type'     => $type,
            'count'    => $count,
            'count_title' => lSetVars($lTitle, array('count' => $count))
        );

        return $response;
    }

    /* Block user */
    static function blockUser($groupId, $groupUserId, $uid) {
        $sql = 'INSERT IGNORE INTO `groups_user_block_list`
                   SET `group_id` = ' . to_sql($groupId) .
                    ', `group_user_id` = ' . to_sql($groupUserId) .
                    ', `user_id` = ' . to_sql($uid);
        DB::execute($sql);
    }

    static function blockRemove($groupId = null, $uid = null) {
        if ($groupId === null) {
            $groupId = self::getParamOneId();
        }

        if ($uid === null) {
            $uid = get_param_int('user_id');
        }

        $groupUserId = guid();
        $sql = 'DELETE FROM `groups_user_block_list`
                 WHERE `group_id` = ' . to_sql($groupId, 'Number') . '
                   AND `group_user_id` = ' . to_sql($groupUserId) . '
                   AND `user_id` = ' . to_sql($uid, 'Number');
        DB::execute($sql);

        return true;
    }

    static function setModerator($groupId = null, $uid = null) {
        if ($groupId === null) {
            $groupId = get_param('group_id', '');
        } 
        if ($uid === null) {
            $uid = get_param('user_id', '');
        }
        try{
            $sql = "UPDATE `groups_social_subscribers` SET group_moderator = 'Y' WHERE group_id = ". to_sql($groupId, 'Text') . " AND user_id = " . to_sql($uid, 'Text');
            DB::execute($sql);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }


    static function setUnModerator($groupId = null, $uid = null) {
        if ($groupId === null) {
            $groupId = get_param('group_id', '');
        } 
        if ($uid === null) {
            $uid = get_param('user_id', '');
        }
        try{
            $sql = "UPDATE `groups_social_subscribers` SET group_moderator = 'N' WHERE group_id = ". to_sql($groupId, 'Text') . " AND user_id = " . to_sql($uid, 'Text');
            DB::execute($sql);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    static function blockFull($uid = null, $groupId = null) {
        $guid = guid();
        if (!$guid) {
            return false;
        }

        if ($groupId === null) {
            $groupId = self::getParamOneId();
        }

        if ($uid === null) {
            $uid = get_param_int('user_id');
        }

        if (!$groupId || !$uid) {
            return false;
        }

        CIm::closeIm($uid);

        $groupUserId = self::getInfoBasic($groupId, 'user_id');
        self::blockUser($groupId, $groupUserId, $uid);

        self::subscribeAction($uid, $groupId, 'remove');

        DB::delete('wall', "section = 'share' AND group_id = " . to_sql($groupId) . ' AND `user_id` = ' . to_sql($uid));

        CStatsTools::count('group_user_blocks');

        return true;
    }

    static function isEntryBlocked($groupId, $uid, $dbIndex = DB_MAX_INDEX) {
        if(!Common::isOptionActive('contact_blocking')) {
            return 0;
        }

        $sql = 'SELECT `id`
                  FROM `groups_user_block_list`
                 WHERE `group_id` = ' . to_sql($groupId, 'Number') . '
                   AND `user_id` = ' . to_sql($uid, 'Number');
        return DB::result($sql, 0, $dbIndex, true);
    }

    static function getNumberBlocked($groupId) {
        $sql = 'SELECT COUNT(*) FROM `groups_user_block_list`
                 WHERE `group_id` = ' . to_sql($groupId) . '
                   AND `group_user_id` = ' . to_sql(guid());
        return DB::result($sql);
    }

    static function getListBlocked($groupId, $limit = '', $fidIndex = false) {
        global $g;

        $key = 'Groups_getListBlocked_' . $groupId . '_' . intval($fidIndex) . ($limit ? '_' . $limit : '_all');
        $blockedList = Cache::get($key);
        if ($blockedList !== null) {
            return $blockedList;
        }

        if (!$groupId) {
            Cache::add($key, array());
            return array();
        }

        if ($limit) {
            $limit = ' LIMIT ' . $limit;
        }

		$year = to_sql(date('Y'), 'Text');
        $monthAndDay = to_sql(date('00-m-d'), 'Text');

        $sql = "SELECT B.*, U.*, ($year - DATE_FORMAT(U.birth, '%Y') - ($monthAndDay < DATE_FORMAT(U.birth, '00-%m-%d') ) ) AS age
                   FROM `groups_user_block_list` as B
                   JOIN `user` AS U ON U.user_id = B.user_id
                  WHERE B.group_id = " . to_sql($groupId) . "
                    AND B.group_user_id = " . to_sql(guid()) . "
                  ORDER BY id DESC" . $limit;


        $fetchType = DB::getFetchType();
        DB::setFetchType(MYSQL_ASSOC);
        $blockedList = DB::rows($sql, 5, true);
        DB::setFetchType($fetchType);
        if ($fidIndex) {
            $result = array();
            foreach ($blockedList as $key => $item) {
                $suid = $item['user_id'];
                $result[$suid] = $item;
                $photo = $g['path']['url_files'] .  User::getPhotoDefault($item['user_id'], 's', false, $item['gender']);
                $result[$suid]['friend_photo'] = $photo;
                $result[$suid]['friend_url'] = User::url($suid);
                $result[$suid]['friend_name'] = $item['name'];

            }
            $blockedList = $result;
        }
        Cache::add($key, $blockedList);

        return $blockedList;
    }
    /* Block user */

    static function isFreeAccess($groupId, $groupInfo = null) {
        if ($groupInfo === null) {
            $groupInfo = self::getInfoBasic($groupId);
        }
        return intval($groupInfo['page'] || $groupInfo['private'] == 'N');
    }

    static function isUserAccess($groupId, $uid = null, $groupInfo = null) {

        if ($groupInfo === null) {
            $groupInfo = self::getInfoBasic($groupId);
        }

        $isFreeAccess = self::isFreeAccess($groupId, $groupInfo);

        if ($isFreeAccess || $groupInfo['user_id'] == $uid) {
            return true;
        }

        if ($uid === null) {
            $uid = guid();
        }

        return self::isSubscribeUser($uid, $groupId, false);

    }
}