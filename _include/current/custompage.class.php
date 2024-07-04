<?php
class CustomPage extends CHtmlBlock{

    static public $selectedMenuItem = array('id' => 0, 'menu_title' => '');

    static function checkAccessPage($id = null)
    {
        if ($id === null) {
            $id = get_param('id');
        }
        if (!$id) {
            //redirect(Common::toHomePage());
            redirect('index.php');
        }
        $page = DB::select('pages', '`id` = ' . to_sql($id) . ' OR `parent` = ' .  to_sql($id));
        if (!isset($page[0]) || !$page[0]['status']) {
            //redirect(Common::toHomePage());
            redirect('index.php');
        }
        if (!guid() && $page[0]['hide_from_guests']) {// && Common::isOptionActive('hide_site_from_guests')
            Common::toLoginPage();
        }
    }

    static function isParseCity()
    {
        $optionTmplName = Common::getOption('name', 'template_options');
        return (Common::getOption('3dcity_menu_item_position', '3d_city') == 'right' || $optionTmplName == 'impact') && Common::isModuleCityActive();
    }

    static function parseMenu(&$html, $section = 'narrow', $dbIndex = DB_MAX_INDEX, $where = '')
    {
        global $g;
        global $g_user;

        $block = "{$section}_menu";
        if (!$html->blockExists($block)) {
            return false;
        }

        $optionTmplName = Common::getOption('name', 'template_options');
        $menuSystem = array();
        $viewers = array('count' => 0, 'new' => 0);
        if ($section == 'narrow') {
            $viewers = User::getNumberViewersMeProfiles();
            $numberMutualAttractions = MutualAttractions::getNumberMutualAttractions();
            $menuSystem = array(
                'column_narrow_profile_visitors' => array(
                            'url' => Common::pageUrl('users_viewed_me'),
                            'count' => $viewers['count'],
                            'count_id' => 'narrow_visitors_count'),
                'column_narrow_can_see_your_private_photos' => array(
                            'url' => Common::pageUrl('my_friends'),
                            'count_id' => 'narrow_private_photo_count',
                            'count' => User::getNumberFriends()),
            );
            $menuSystemTmpl = array();
            if ($optionTmplName === 'urban') {
                $menuSystemTmpl = array(
                    'column_narrow_general_chat' => array(
                            'url' => Common::pageUrl('general_chat'),
                            'count' => Flashchat::getNumberUsersVisitors(),
                            'count_id' => 'narrow_general_chat_count'),
                    'column_narrow_want_to_meet_you' => array(
                            'url' => Common::pageUrl('mutual_attractions') . '?cmd=want_to_meet_you',
                            'count_id' => 'narrow_want_count',
                            'count' => MutualAttractions::getNumberMutualAttractions('Wanted')),
                    'column_narrow_mutual_attractions' => array(
                            'url' => Common::pageUrl('mutual_attractions'),
                            'link_id' => 'mutual_attractions',//left did not find this ID
                            'count_id' => 'narrow_mutual_count',
                            'count' => $numberMutualAttractions),
                );
            } elseif ($optionTmplName === 'impact') {
                $menuSystem['column_narrow_can_see_your_private_photos']['url'] = Common::pageUrl('private_photo_access');
                $menuSystemTmpl = array(
                    'column_narrow_search_results'  => array(
                            'url' => Common::pageUrl('search_results'),
                            'count' => '',
                            'count_id' => ''),
                    'column_narrow_messages' => array(
                            'url' => Common::pageUrl('messages'),
                            'count' => CIm::getCountNewMessages(),
                            'count_id' => 'narrow_messages'),
                    'column_narrow_who_likes_you' => array(
                            'url' => Common::pageUrl('who_likes_you'),
                            'count_id' => 'narrow_who_likes_you_count',
                            'count' => MutualAttractions::getNumberMutualAttractions('WhoLikesYou')),
                    'column_narrow_whom_you_like' => array(
                            'url' => Common::pageUrl('whom_you_like'),
                            'count_id' => 'narrow_whom_you_like_count',
                            'count' =>  MutualAttractions::getNumberMutualAttractions('WhomYouLike')),
                    'column_narrow_mutual_likes' => array(
                            'url' => Common::pageUrl('mutual_likes'),
                            'count_id' => 'narrow_mutual_likes_count',
                            'count' => $numberMutualAttractions),
                    'column_narrow_hot_or_not'   => array(
                            'url' => Common::pageUrl('hot_or_not'),
                            'count' => '',
                            'count_id' => ''),
                );
            }
            $menuSystem = array_merge($menuSystem, $menuSystemTmpl);

			if (LiveStreaming::isAviableLiveStreaming()) {
				$menuSystem['column_narrow_live_list'] = array(
					'url' => Common::pageUrl('live_list'),
                    'count' => LiveStreaming::getTotalLiveNow(),
                    'count_id' => 'narrow_live_list_count'
				);

				$menuSystem['column_narrow_live_list_finished'] = array(
					'url' => Common::pageUrl('live_list_finished'),
                    'count' => LiveStreaming::getTotalLiveFinished(),
                    'count_id' => 'narrow_live_list_finished_count'
				);

				$menuSystem['column_narrow_live'] = array(
					'url' => Common::pageUrl('live', guid()),
                    'count' => '',
                    'count_id' => ''
				);
			} else {
				$where .= " AND `menu_title` != 'column_narrow_live_list'";
				$where .= " AND `menu_title` != 'column_narrow_live_list_finished'";
				$where .= " AND `menu_title` != 'column_narrow_live'";
			}

            $numbersCity = null;
            if (self::isParseCity()) {
                $numbersCity = City::getNumberUsersVisitors();
                $menuSystem['column_narrow_3dcity'] = array(
                            'url' => City::url('city', false, false),
                            'count_id' => 'narrow_city_count',
                            'count' => $numbersCity['all_number']);
            } else {
                $where .= " AND `menu_title` != 'column_narrow_3dcity'";
            }
            if (City::isActiveStreetChat()) {
                if ($numbersCity === null) {
                    $numbersCity = City::getNumberUsersVisitors();
                }
                $menuSystem['column_narrow_street_chat'] = array(
                            'url' => City::url('street_chat', false, false),
                            'count_id' => 'narrow_street_chat_count',
                            'count' => $numbersCity[12]);
            } else {
                $where .= " AND `menu_title` != 'column_narrow_street_chat'";
            }
            if (City::isActiveGames()) {
                if ($numbersCity === null) {
                    $numbersCity = City::getNumberUsersVisitors();
                }
                $menuSystem['column_narrow_game_choose'] = array(
                            'url' => Common::pageUrl('games'),
                            'count_id' => 'column_narrow_game_choose_count',
                            'count' => City::getNumberUsersGames($numbersCity));
            } else {
                $where .= " AND `menu_title` != 'column_narrow_game_choose'";
            }
            if (Common::isOptionActive('friends_enabled')) {
                $menuSystem['column_narrow_friends'] = array(
                            'url' => Common::pageUrl('my_friends') . '?show=all_and_pending',
                            'count_id' => 'narrow_friends_count',
                            'count' => User::getNumberFriendsAndPending());
            } else {
                $where .= " AND `menu_title` != 'column_narrow_friends'";
            }
            if (Common::isOptionActive('flashchat')) {
                $menuSystem['column_narrow_general_chat'] = array(
                            'url' => Common::pageUrl('general_chat'),
                            'count' => Flashchat::getNumberUsersVisitors(),
                            'count_id' => 'narrow_general_chat_count');
            } else {
                $where .= " AND `menu_title` != 'column_narrow_general_chat'";
            }
            if (Common::isOptionActive('wink')) {
                $menuSystem['column_narrow_winks_from'] = array(
                            'url' => Common::pageUrl('mail_whos_interest'),
                            'count' => DB::count('users_interest', "`user_to` = " . to_sql($g_user['user_id'])),
                            'count_id' => 'narrow_winks_from_count');
            } else {
                $where .= " AND `menu_title` != 'column_narrow_winks_from'";
            }

            if (Common::isOptionActive('photo_rating_enabled')) {
                if($optionTmplName != 'impact') {
                    $menuSystem['column_narrow_rated_your_photos'] = array('url' => Common::pageUrl('users_rated_me'),
                                'count' => CProfilePhoto::getNumberUsersRatedMePhoto(),
                                'count_id' => 'narrow_rated_photos_count');
                }
            } else {
                $where .= " AND `menu_title` != 'column_narrow_rated_your_photos'";
            }

            $sql = 'SELECT COUNT(*)
                      FROM `user_block_list`
                     WHERE `user_from` = ' . to_sql($g_user['user_id'], 'Number');
            $numberBlockedUsers = DB::result($sql);
            if (Common::isOptionActive('contact_blocking')) {
                $menuSystem['column_narrow_blocked'] = array(
                            'url' => Common::pageUrl('user_block_list'),
                            'link_id' => 'user_block_list',
                            'count_id' => 'narrow_blocked_count',
                            'count' => $numberBlockedUsers);
            } else {
                $where .= " AND `menu_title` != 'column_narrow_blocked'";
            }
            $html->setvar('number_blocked_users', $numberBlockedUsers);
        } else if ($section == 'bottom' || $section == 'bottom_visitor') {
            $menuSystem = array(
                'menu_bottom_about_us' => array(
                            'url' => Common::pageUrl('about'),
                            'count_id' => 'menu_bottom_about_us'),
                /* Edge */
                'menu_people_edge' => array(
                            'url' => Common::pageUrl('search_results'),
                            'count_id' => 'menu_bottom_search_results'),
                'menu_terms_edge' => array(
                            'url' => Common::pageUrl('terms'),
                            'count_id' => 'menu_bottom_terms'),
                'menu_privacy_policy_edge' => array(
                            'url' => Common::pageUrl('privacy_policy'),
                            'count_id' => 'menu_bottom_privacy_policy'),
                'menu_blogs_edge' => array(
                            'url' => Common::pageUrl('blogs_list'),
                            'count_id' => 'menu_bottom_blogs'),
                'menu_photos_edge' => array(
                            'url' => Common::pageUrl('photos_list'),
                            'count_id' => 'menu_bottom_photos'),
                'menu_videos_edge' => array(
                            'url' => Common::pageUrl('vids_list'),
                            'count_id' => 'menu_bottom_videos')
                /* Edge */
            );
            if (Common::isOptionActive('partner')) {
                $menuSystem['menu_bottom_affiliates'] = array(
                            'url' => 'partner/',
                            'count_id' => 'menu_bottom_affiliates',
                            'target' => true);
            } else {
                $where .= " AND `menu_title` != 'menu_bottom_affiliates'";
            }
            if (Common::isOptionActive('contact')) {
                $menuSystem['menu_bottom_contact_us'] = array(
                            'url' => Common::pageUrl('contact'),
                            'count_id' => 'menu_bottom_contact_us');
            } else {
                $where .= " AND `menu_title` != 'menu_bottom_contact_us'";
            }
        }

        $menuSystem['column_narrow_invite'] = array(
            'url' => '',
            'show_class' => true,
        );

        $sectionSql = $section;
        if ($section == 'bottom_visitor') {
            $sectionSql = 'bottom';
        }
        $setTmpl = '%' . $optionTmplName . '%';
        $menuCurrent = array();
        $sql = "SELECT *
                  FROM `pages`
                 WHERE `status` = 1
                   AND `section` = " . to_sql($sectionSql) .
                 " AND (`set` LIKE " . to_sql($setTmpl) . " OR `set` = '')
                   AND (`lang` = 'default' OR `lang` = " . to_sql($g['lang_loaded']) . ")" . $where . "
                 ORDER BY `parent` ASC, `position` ASC";
        DB::setFetchType(MYSQL_ASSOC);
        $menu = DB::rows($sql, $dbIndex);
        DB::setFetchType(MYSQL_BOTH);

        $systemMenuItems = array();

        foreach ($menu as $item) {
            if ($item['lang'] == 'default') {
                $menuCurrent[$item['id']]['id'] = $item['id'];
                $menuCurrent[$item['id']]['menu_title'] = $item['menu_title'];
                $menuCurrent[$item['id']]['system'] = $item['system'];
                $menuCurrent[$item['id']]['menu_style'] = $item['menu_style'];
                $menuCurrent[$item['id']]['set'] = $item['set'];
            } elseif (trim($item['menu_title']) && isset($menuCurrent[$item['parent']])) {
                $menuCurrent[$item['parent']]['menu_title'] = $item['menu_title'];
            }
            if($item['system']) {
                $systemMenuItems[$item['menu_title']] = true;
            }
        }

		$isParse = false;
        if ($menuCurrent) {

            if($systemMenuItems) {
                if(isset($systemMenuItems['column_narrow_3dcity'])) {
                    if(isset($systemMenuItems['column_narrow_street_chat'])) {
                        $menuSystem['column_narrow_3dcity']['count'] -= $menuSystem['column_narrow_street_chat']['count'];
                    }
                    if(isset($systemMenuItems['column_narrow_game_choose'])) {
                        $menuSystem['column_narrow_3dcity']['count'] -= $menuSystem['column_narrow_game_choose']['count'];
                    }
                }
            }

            //$menuCurrent=array_reverse($menuCurrent);
            $blockItem = "{$block}_item";
            $blockItemInactive = "{$blockItem}_inactive" ;
            $blockDecor = 'narrow_menu_item_decor';
			$blockStatus = 'narrow_menu_item_status';
            $i = 0;
            foreach ($menuCurrent as $item) {
                //$allowTmpls = explode(',', $item['set']);
                //if ($item['system'] && !in_array($optionTmplName, $allowTmpls)) {
                    //continue;
                //}
                $html->clean($blockItemInactive);
                // number_rated_your_photos
                $html->clean($blockDecor);
                $id = $item['id'];
                $menuTitle = $item['menu_title'];
                if ($item['system']) {
                    $menuTitle = $titleField = lCascade(l($menuTitle), array($menuTitle . '_' . $optionTmplName));
                    if (!$item['menu_style']) {
                        $html->parse($blockItemInactive);
                    }
                }
                $html->setvar("{$blockItem}_title", $menuTitle);

                $linkId = "{$block}_link_{$id}";
                $countId = "{$block}_count_{$id}";
                $count = '';
                $url = Common::pageUrl('page') . "?id={$id}";

                $alias = null;

                $isParseCount = false;
                if ($item['system']) {
                    $alias = $item['menu_title'];
                    // number_rated_your_photos
                    if ($alias == 'column_narrow_rated_your_photos') {
                        $usersRatedMyPhotoNew = CProfilePhoto::getNumberUsersRatedMePhoto(null, true);
                        if ($usersRatedMyPhotoNew) {
                            $html->parse($blockDecor, false);
                        }
                    } elseif ($alias == 'column_narrow_winks_from') {
                        $newWink = DB::count('users_interest', "`new` = 'Y' AND `user_to` = " . to_sql($g_user['user_id']));
                        if ($newWink) {
                            $html->parse($blockDecor, false);
                        }
                    } elseif ($alias == 'column_narrow_profile_visitors' && $viewers['new']) {
                        $html->parse($blockDecor, false);
                    } elseif ($alias == 'column_narrow_who_likes_you' && MutualAttractions::getNumberMutualAttractions('WhoLikesYou', true)) {
                        $html->parse($blockDecor, false);
                    } elseif (false && $alias == 'column_narrow_whom_you_like' && MutualAttractions::getNumberMutualAttractions('WhomYouLike', true)) {
                        $html->parse($blockDecor, false);
                    } elseif ($alias == 'column_narrow_mutual_likes' && MutualAttractions::getNumberMutualAttractions('Mutual', true)) {
                        $html->parse($blockDecor, false);
                    } elseif ($alias == 'column_narrow_invite') {
                        if(!Common::isMobile(true)) {
                            $detect = new mobile_detect();
                            if($detect->isMobile()) {
                                continue;
                            }
                        }
                    }

					$html->subcond($alias == 'column_narrow_live_list', $blockStatus);

                    if (isset($menuSystem[$alias]['url'])) {
                        $url = $menuSystem[$alias]['url'];
                    }
                    if (isset($menuSystem[$alias]['link_id']) && $menuSystem[$alias]['link_id']) {
                        $linkId = $menuSystem[$alias]['link_id'];
                    }
                    // delete

                    if (isset($menuSystem[$alias]['count_id']) && $menuSystem[$alias]['count_id']) {
                        $countId = $menuSystem[$alias]['count_id'];
                        $isParseCount = true;
                    }
                    if (isset($menuSystem[$alias]['count'])) {
                        $count = $menuSystem[$alias]['count'];
                        if($optionTmplName === 'impact' && $count == 0) {
                            $count = '';
                        }
                    }
                    if ($alias == 'column_narrow_messages' && $count) {
                        $html->parse($blockDecor, false);
                    }
                }

                if ($html->blockExists($blockItem . '_target')) {
                    if (isset($menuSystem[$alias]['target'])) {
                        $html->parse($blockItem . '_target', false);
                    } else {
                        $html->clean($blockItem . '_target');
                    }
                }

                if ($html->varExists("{$blockItem}_link_id")) {
                    $html->setvar("{$blockItem}_link_id", $linkId);
                }
                if ($html->varExists("{$blockItem}_count_id")) {
                    $html->setvar("{$blockItem}_count_id", $countId);
                }
                if ($html->varExists("{$blockItem}_count")) {
                    $html->setvar("{$blockItem}_count", $count);
                }
                if ($html->blockExists($blockItem . '_count')) {
                    if ($isParseCount) {
                        $html->parse($blockItem . '_count', false);
                    } else {
                        $html->clean($blockItem . '_count');
                    }
                }

                $isShowClass = (isset($menuSystem[$alias]['show_class']) && $menuSystem[$alias]['show_class'])
                                || ($optionTmplName == 'impact' && $item['system']);

                $inactiveMenuItemStyle = ($item['system'] && !$item['menu_style']) ? 'inactive' : '';

                $itemClass = ($isShowClass ? $item['menu_title'] : '') .
                    ' ' . (self::isActiveMenuItem($item) ? 'menu_selected' : '') .
                    ' ' . $inactiveMenuItemStyle;

                $html->setvar("{$blockItem}_class", $itemClass);

                $html->setvar("{$blockItem}_url", $url);
                if (($section == 'bottom' || $section == 'bottom_visitor') && $html->blockExists($blockItem . '_stick') && $i) {
                    $html->parse($blockItem . '_stick', false);
                }
                $i = 1;
                $html->parse($blockItem, true);
				$isParse = true;
            }
            $html->parse($block);
        }

		return $isParse;
    }

    static function parsePage(&$html, $id = null, $alias = 'page', $shortLength = 155) {
        global $g;

        if ($id === null) {
            $id = get_param('id');
        }
        if (!$id) {
            $id = self::getIdFromAlias();
        }
        if (!$id) {
            Common::toHomePage();
        }

        $currentPage = array();
        DB::setFetchType(MYSQL_ASSOC);
        $sql = "SELECT `title`, `content`, `lang`, `status`
                  FROM `pages`
                 WHERE (`lang` = 'default' AND `id` = " . to_sql($id) . ")
                    OR (`lang` =" . to_sql($g['lang_loaded']) . " AND `parent` = ". to_sql($id) . ")";
        $page = DB::rows($sql);
        DB::setFetchType(MYSQL_BOTH);
        foreach ($page as $item) {
            if ($item['lang'] == 'default') {
                if (!$currentPage) {
                    $currentPage = $item;
                } else {
                    if (!isset($currentPage['title'])) {
                        $currentPage['title'] = $item['title'];
                    }
                    if (!isset($currentPage['content'])) {
                        $currentPage['content'] = $item['content'];
                    }
                }
            } else {
                if (trim($item['title'])) {
                    $currentPage['title'] = $item['title'];
                }
                if (trim($item['content'])) {
                    $currentPage['content'] = $item['content'];
                }
            }
        }
        if (isset($currentPage['content']) && $currentPage['content']) {
            $currentPage['content'] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $currentPage['content']);

            $currentPage['content_short'] = neat_trim(strip_tags($currentPage['content']), $shortLength);
        }
        $html->assign($alias, $currentPage);
    }

    function parseBlock(&$html)
    {

        TemplateEdge::parseColumn($html);

        self::parsePage($html);
        parent::parseBlock($html);
    }

    static function isMenuIconExists($id)
    {
        global $g;

        $iconFile = $g['tmpl']['dir_tmpl_main'] . 'images/menu_icons/' . $id . '.png';

        return file_exists($iconFile);
    }

    static function menuIconUrl($id)
    {
        global $g;

        $urlPageIcon = false;

        if (self::isMenuIconExists($id)) {
            $urlPageIcon = $g['tmpl']['url_tmpl_main'] . 'images/menu_icons/' . $id . '.png';
        }

        return $urlPageIcon;
    }

    static function parseCssFile(&$html)
    {
        $block = 'page_menu_item';

        if($html->blockExists($block)) {
            $sql = 'SELECT * FROM pages
                WHERE `set` = ""
                    AND `status` = 1
                    AND `system` = 0
                    AND `section` = "narrow"';
            $pages = DB::rows($sql);
            if($pages) {
                foreach($pages as $page) {
                    if(self::isMenuIconExists($page['id'])) {
                        $html->setvar($block . '_id', $page['id']);
                        $html->setvar($block . '_icon_url', self::menuIconUrl($page['id']));
                        $html->parse($block);
                    }
                }
            }
        }

    }

    static function isMenuIconUploadAvailable()
    {
        return Common::getOption('menu_narrow_icon_upload', 'template_options');
    }

    static public function setSelectedMenuItemById($id)
    {
        static::setSelectedMenuItem($id);
    }

    static public function setSelectedMenuItemByTitle($title)
    {
        static::setSelectedMenuItem(0, $title);
    }

    static public function setSelectedMenuItem($id = 0, $title = '')
    {
        static::$selectedMenuItem['id'] = $id;
        static::$selectedMenuItem['menu_title'] = $title;
    }

    static public function isActiveMenuItem($item)
    {
        $isActive = ( ($item['id'] == static::$selectedMenuItem['id']) ||
                ($item['system'] && $item['menu_title'] == static::$selectedMenuItem['menu_title']) );
        return $isActive;
    }

    static public function setSelectedMenuItemInSearchResults($display)
    {
        $type = '';
        if($display == 'encounters') {
            $type = 'hot_or_not';
        } elseif($display == '') {
            $type = 'search_results';
        }

        if($type) {
            static::setSelectedMenuItemByTitleType($type);
        }
    }

    static public function setSelectedMenuItemInMutualAttractions($cmd)
    {
        $type = 'mutual_likes';
        if($cmd) {
            $type = $cmd;
        }

        static::setSelectedMenuItemByTitleType($type);
    }

    static public function setSelectedMenuItemByTitleType($type)
    {
        static::setSelectedMenuItemByTitle('column_narrow_' . $type);
    }

    static public function getIdFromAlias($page = null, $section = 'bottom')
    {
        if ($page === null) {
            $page = get_param('page');
        }
        if (!$page) {
            return 0;
        }
        $optionTmplName = Common::getOption('name', 'template_options');
        $sql = "SELECT `id`
                  FROM `pages`
                 WHERE `status` = 1
                   AND `section` = " . to_sql($section) . "
                   AND `lang` = 'default'
                   AND `menu_title` = " . to_sql($page) . "
                   AND `set` LIKE '%{$optionTmplName}%'";
        return DB::result($sql);
    }

    static public function checkItemShow($page, $section = 'narrow')//column_narrow_hot_or_not
    {
        $optionTmplName = Common::getOption('name', 'template_options');
        $sql = "SELECT `id`
                  FROM `pages`
                 WHERE `status` = 1
                   AND `section` = " . to_sql($section) . "
                   AND `lang` = 'default'
                   AND `menu_title` = " . to_sql($page) . "
                   AND `set` LIKE '%{$optionTmplName}%'";
        return DB::result($sql);
    }

}