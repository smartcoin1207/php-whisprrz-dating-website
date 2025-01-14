<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Class Menu {
    static $urlHomePage = false;
    static $itemUrl = array(    
            'gallery' => 'gallery_index.php',
            'users' => 'users_online.php',
            'profile' => 'profile_view.php',
            'edit_profile' => 'profile.php',
            'rating' => 'users_hon.php',
            'my' => 'my_friends.php',
            'partner' => 'partner/',
            'new_todo' => 'events_event_edit.php?event_private=1',
            'my_videos' => 'vids'
        );

    static $itemAuthOnly = array(
        'home',
        'wall',
        'games',
        'city',
        'flashchat',
        'mail',
        'chat',
        'profile',
        'my',
        'upgrade',
        'partner',
    );

    static $itemLangKeys = array(
        'vids' => 'menu_videogallery',
        'mail' => 'menu_messages',
        'users' => 'menu_whos_online',
        'adv' => 'menu_adv_dating',
        'partner' => 'affiliates',
    );

    static function itemLangKey($key)
    {
        if(isset(self::$itemLangKeys[$key])) {
            $langKey = self::$itemLangKeys[$key];
        } else {
            $langKey = 'menu_' . $key;
        }
        return $langKey;
    }

    static function getUrlHomePage()
    {
        if(self::$urlHomePage === false) {
            self::$urlHomePage = self::homePage();
        }
        return self::$urlHomePage;
    }

    static function isItemAuthOnly($menu)
    {
        if(Common::isOptionActive('main_page_by_first_menu_item_after_login', 'template_options')) {
            if(!guid()) {
                $isMenuAuthOnly = true;
            }
        } else {
            $isMenuAuthOnly = in_array($menu, self::$itemAuthOnly);
        }

        return $isMenuAuthOnly;
    }

    //popcorn modified 2023-10-31
    static function menuItemUrl($menu)
    {
        
        
        
        global $g_user;
        if(isset($g_user['name_seo'])) {
            $itemUrl = array( 
                'flashchat' => 'general_chat.php',
                
                'events' =>  $g_user['name_seo'].'/event_calendar',
                'hot dates' =>  $g_user['name_seo'].'/hotdate_calendar',
                'Party House' => $g_user['name_seo'].'/partyhou_calendar',

                'search' => 'search_results',
                'events_calendar' => $g_user['name_seo'].'/event_calendar',
                'calendar' => $g_user['name_seo'].'/calendar',
                'gallery' => 'gallery_index.php',
                'users' => 'users_online.php',
                'profile' => 'profile_view.php',
                'edit_profile' => 'profile.php',
                'rating' => 'users_hon.php',
                'my' => 'my_friends.php',
                'partner' => 'partner/',
                'new_todo' => 'events_event_edit.php?event_private=1',
                'my_videos' => $g_user['name_seo'].'/vids',
                'add_a_videos' => '',
                'photos' => 'photos',
                'my_photos' => $g_user['name_seo'].'/photos',
                'add_a_photo' => '',
                'my_music' => $g_user['name_seo'] . '/songs',
                'add_music' => '',
                'friends_online' => $g_user['name_seo'] . '/friends_online',
                'invite_friends_by_sms' => '',
                'profile_visitors' => 'users_viewed_me',
                'favorites' => 'favorite_list',
                'blocked_users' => 'user_block_list',
                'post_to_blog' => 'blogs_add',
                'my_groups' => $g_user['name_seo'] . '/groups',
                'group_vids' => 'vids_groups',
                'my_group_videos' => $g_user['name_seo'].'/vids_my_groups',
                'group_pics' => 'photos_groups',
                'my_groups_pics' => $g_user['name_seo']. '/photos_my_groups',
                'group_music' => 'songs_groups',
                'my_groups_music' => $g_user['name_seo'].'/songs_my_groups', 
                'create_group' => 'group_add',
                'pages' => 'pages',
                'page_vids' => 'vids_pages',
                'my_pages_videos' => $g_user['name_seo'].'/vids_my_pages',
                'page_pics' => 'photos_pages',
                'my_pages_photos' => $g_user['name_seo'].'/photos_my_pages',
                'page_music' => 'songs_pages',
                'my_pages_music' => $g_user['name_seo'].'/songs_my_pages',
                'create_page' => 'page_add',
                'my_calendar' => $g_user['name_seo'].'/calendar',
                'create_new_task' => $g_user['name_seo'].'/task_create',
                'invite_friends' => 'invite.php',
                'verify_account' => '',
                'refill_credits' => '',
                'whisp_radio' => 'Whisprrz Radio.php',
                'whisprrz_wevents' => 'Whisprrz Wevents.php',
                'my_blog' => $g_user['name_seo'] . '/blogs',

            );

            self::$itemUrl = $itemUrl;
        }



        //nnsscc-diamond-202002020307-start
        $optionTmplSet = Common::getTmplSet();
        if ($optionTmplSet == 'old' && $menu=='flashchat') {
            //return "oryx_public_chat.php";
            return "general_chat.php";
        }
        //nnsscc-diamond-202002020307-end
        // if($menu=='Party House') {//nnscc-diamond-20201028-start
        //     $mail = "";
        //     $name = "";
        //     if(isset($g_user['mail'])){
        //         $mail = $g_user['mail'];
        //     }
        //     if(isset($g_user['name'])){
        //         $name = $g_user['name'];
        //     }
        //     return "partyhouz.php";
        // }
        if($menu=='LookingGlass') {
            //return "VideoCall";
        }

        if ($menu=='live') {
            return "live_streaming.php";
        }
        //nnscc-diamond-20201028-end
        
        return isset(self::$itemUrl[$menu]) ? self::$itemUrl[$menu] : $menu . '.php';
    }
    //popcorn modified 2023-10-31


    static function homePage($uid = null)
    {
        global $p;

        $optionTmplSet = Common::getTmplSet();
        $optionTmplSet = 'old';
        if ($optionTmplSet == 'urban') {
            $optionTmplName = Common::getTmplName();

            if ($optionTmplName == 'edge') {
                $setHomePageUrban = Common::getOption('set_home_page', 'edge');
                $defaultProfileTab = Common::getOption('set_default_profile_tab', 'edge');
                $profileUrl = 'profile_view';
                if(IS_DEMO) {
                    $profileUrl = '';
                }
                $prepareUrl = true;
                if (guid()) {
                    if (Common::isOptionActive('seo_friendly_urls')) {
                        $profileUrl = User::url(guid());
                        $prepareUrl = false;
                    }
                    if ($defaultProfileTab == 'menu_inner_videos_edge') {
                        $profileUrl = 'user_vids_list';
                    } elseif ($defaultProfileTab == 'menu_inner_photos_edge') {
                        $profileUrl = 'user_photos_list';
                    } elseif ($defaultProfileTab == 'menu_inner_friends_edge') {
                        $profileUrl = 'user_friends_list';
                    }
                    $pageURLs = ListBlocksOrder::getOrderItemsList('member_header_menu', true);
                }

                $pageURLs = ListBlocksOrder::getOrderItemsList('member_home_page', false);
                $pageURLs['menu_profile_edge']['url'] = $profileUrl;
                if ($setHomePageUrban && isset($pageURLs[$setHomePageUrban])) {
                    $prepareUrl = !isset($pageURLs[$setHomePageUrban]['url_real']);
                    $setHomePageUrban = isset($pageURLs[$setHomePageUrban]['url_real']) ? $pageURLs[$setHomePageUrban]['url_real'] : $pageURLs[$setHomePageUrban]['url'];
                } else {
                    $setHomePageUrban = $profileUrl;
                }

                if ($prepareUrl) {
                    $url = Common::pageUrl($setHomePageUrban, $uid);
                } else {
                    $url = $setHomePageUrban;
                }
                return  $url;
            }

            $viewHomePage = Common::getOption('view_home_page_urban');
            if(Common::getOption('site_part', 'main') == 'main'){
                $pageURLs = array(
                            'header_menu_wall' => Common::pageUrl('wall'),
                            'header_menu_encounters' => 'search_results.php?display=encounters',
                            'header_menu_people_nearby' => Common::pageUrl('search_results')
                );

                $setHomePageUrban = Common::getOption('set_home_page_urban');
                if ($setHomePageUrban == 'menu_city') {
                    if ($p != 'city.php' && !CityBase::isCityInTab()) {
                        $pageURLs['menu_city'] = City::url('city', false, false);
                    }
                }
                if ($setHomePageUrban && isset($pageURLs[$setHomePageUrban])) {
                    return $pageURLs[$setHomePageUrban];
                }
                if (guid() && $viewHomePage == 'profile_view' && Common::isOptionActive('seo_friendly_urls')) {
                    $viewHomePage = User::url(guid());
                }
                return Common::pageUrl($viewHomePage);
            } elseif(Common::getOption('site_part', 'main')){
                $pageURLs = array(
                        'profile' => Common::pageUrl('profile_view'),
                        'people_nearby' => Common::pageUrl('search_results'),
                        'encounters' => 'search_results.php?display=encounters',
                        'photo_rating' => 'search_results.php?display=rate_people',
                        'messages' => Common::pageUrl('messages'),
                        'friends' => Common::pageUrl('my_friends'),
                        'matches' => 'mutual_attractions.php',
                        'who_likes_you' => 'mutual_attractions.php?display=want_to_meet_you',
                        'profile_visitors' => Common::pageUrl('users_viewed_me'),
                        'game_choose' => Common::pageUrl('games')
                );
                $pageURLsTmpl = array();
                if ($optionTmplName == 'impact_mobile') {
                    unset($pageURLs['photo_rating']);
                    unset($pageURLs['friends']);
                    unset($pageURLs['matches']);
                    $pageURLsTmpl = array(
                        'who_likes_you' => Common::pageUrl('who_likes_you'),
                        'profile_view' => Common::pageUrl('profile_view'),
                        'search_results' => 'search_results',
                        'hot_or_not' => Common::pageUrl('hot_or_not'),
                        'whom_you_like' => Common::pageUrl('whom_you_like'),
                        'mutual_likes' => Common::pageUrl('mutual_likes'),
                        'can_see_your_private_photos' => Common::pageUrl('private_photo_access'),
                        'boost' => Common::pageUrl('profile_boost'),
                        'upgrade' => Common::pageUrl('upgrade'),
                        'settings' => Common::pageUrl('profile_settings'),
                        'live' => Common::pageUrl('live', guid()),
                        'live_list' => Common::pageUrl('live_list'),
                        'live_list_finished' => Common::pageUrl('live_list_finished')
                    );

                    if (!User::accessCheckFeatureSuperPowers('live_streaming')) {
                        $pageURLsTmpl['live'] = Common::pageUrl('upgrade');
                    }
                }

                $pageURLs = array_merge($pageURLs, $pageURLsTmpl);
                $setHomePageMobile = Common::getOption('set_home_page_mobile');

                if (in_array($setHomePageMobile, array('3d_city', 'street_chat'))) {
                    if (!CityBase::isCityInTab()) {
                        $pageURLs['3d_city'] = City::url('city', false, false);
                        $pageURLs['street_chat'] = City::url('street_chat', false, false);
                    }
                } elseif (in_array($setHomePageMobile, array('live_list', 'live_list_finished', 'live'))
                            && !LiveStreaming::isAviableLiveStreaming()) {
                    $setHomePageMobile = 'profile_view';
                }

                if(isset($pageURLs[$setHomePageMobile])){
                    if($pageURLs[$setHomePageMobile] == 'upgrade' && Common::isOptionActive('free_site')) {
                        $pageURLs[$setHomePageMobile] = Common::pageUrl($viewHomePage);
                    }
                    return $pageURLs[$setHomePageMobile];
                }
                return Common::pageUrl($viewHomePage);
            }
        }

        if (Common::getOption('site_part', 'main') == 'administration'
            || Common::getOption('site_part', 'main') == 'partner') {
            return 'home.php';
        }
        $templateHome = Common::getOption('home_page', 'template_options');
        if ($templateHome) {
            return $templateHome;
        }

        $menu = 'home';

        if (Common::isWallActive() && (Common::isOptionActive('feed_as_home_page') || Common::getOption('home_page_mode') == 'social')) {
            $menu = 'wall';
        }

        // probably index.php will be main page always in this case...
        if(Common::isOptionActive('main_page_by_first_menu_item', 'template_options')) {
            $menuItems = self::mainMenuItems();
            if(Common::isValidArray($menuItems)) {
                reset($menuItems);
                $menu = key($menuItems);
            }
        }

        if(guid() === 0 && self::isItemAuthOnly($menu)) {
            $menu = 'index';
        }

        $urlHomePage = self::menuItemUrl($menu);

        return $urlHomePage;
    }

    static function mainMenuItems($admin = false)
    {
        global $g;
        #$menuItems = Config::getOptionsAll('menu');

        $menuItems = $g['menu'];

        if (!$admin) {
            foreach ($menuItems as $option => $value) {
                if (!Config::getPosition('menu', $option)) {
                    unset($menuItems[$option]);
                }
            }
        }
        $menuItems = Common::demoSetMainSection($menuItems);
        asort($menuItems);

        // disable menu items
        $homePageMode = Common::getOption('home_page_mode');

        if ($homePageMode == 'social') {
            unset($menuItems['home']);
        } elseif ($homePageMode == 'dating') {
            unset($menuItems['wall']);
        }

        if(Common::isOptionActive('free_site') || ($admin === false && (!guid() || (guid() && (!User::isPaidFree() || User::isFreeAccess()))))) {
            unset($menuItems['upgrade']);
        }

        if ($admin === false && !guid()) {
            unset($menuItems['profile']);
            unset($menuItems['edit_profile']);
        }

        if(Common::isOptionActive('city') && !Common::isModuleCityExists()) {
            Common::optionDeactivate('city');
        }

        if (!Common::isOptionActive('online_tab_enabled')) {
            unset($menuItems['users']);
        }
        $isCalendar = Common::isOptionActive('events_calendar');
        if ( (!guid() && $admin === false) || !$isCalendar) {
            unset($menuItems['new_todo']);
        }
        if (!$isCalendar) {
            unset($menuItems['events_calendar']);
        }

        if (!Common::isOptionActive('network') || !Common::isOptionActive('network', 'template_options')) {
            unset($menuItems['network']);
        }
        if (!Common::isOptionActive('stats') || !Common::isOptionActive('stats', 'template_options')) {
            unset($menuItems['stats']);
        }
        $menuItemsHide = isset($g['template_options']['menu_items_hide']) ? $g['template_options']['menu_items_hide'] : false;

        foreach ($menuItems as $menuItem => $order) {

            if(is_array($menuItemsHide) && in_array($menuItem, $menuItemsHide)) {
                unset($menuItems[$menuItem]);
                continue;
            }

            $page = $menuItem;
            if ($page == 'vids') {
                $page = 'videogallery';
            }

            if (Common::getOption($page)) {
                if(!Common::isOptionActive($page)) {
                    unset($menuItems[$menuItem]);
                }
            }
        }

        // var_dump($menuItems); die();
        return $menuItems;
    }

    static function getMenuItemsKeys()
    {
        return array(
            'vids' => 'menu_videogallery',
            'mail' => 'menu_messages',
            'users' => 'menu_whos_online',
            'adv' => 'menu_adv_dating',
            'partner' => 'affiliates',
        );

    }

    static function getPageActive()
    {
        $pageActive = Common::page();

        if ($pageActive == 'users_hon.php')
            $menuItemActive = 'rating';
        elseif ($pageActive == 'bookmark_friends.php')
            $menuItemActive = 'my';
        else {
            $tmp = explode('.', $pageActive);
            $pa = explode('_', $tmp[0]);
            $menuItemActive = $pa[0];
        }
        return $menuItemActive;
    }

    static function parseMainMenu(&$html,array $target)
    {
        global $l;
        global $g;

        $menuItems = self::mainMenuItems();

        $menuItemKeys = self::getMenuItemsKeys();
        $menuItemActive = self::getPageActive();

        $menuLimit = isset($l['all']['menu_items']) ? $l['all']['menu_items']-1 : 16; //nnsscc-diamond
        $menuCounter = 0;
        $menuItemsCount = count($menuItems);

        $menuItemStart = true;

        foreach($menuItems as $menuItem => $value) {
            $html->setvar('menu_item_index', $menuCounter);

            // popcorn modified start 2023-10-31
            $html->setvar('custom_id_for_edge', '');
            $html->setvar('custom_class_for_edge', '');


            if($menuItem == 'add_a_videos'){
                $html->setvar('custom_id_for_edge', 'navbar_menu_video_add_edge');
            }
            if($menuItem == 'add_a_photo'){
                $html->setvar('custom_id_for_edge', 'navbar_menu_photo_add_edge');
            }
            if($menuItem == 'add_music'){
                $html->setvar('custom_id_for_edge', 'navbar_menu_song_add_edge');
            }
            if($menuItem == 'refill_credits'){
                $html->setvar('custom_id_for_edge', 'navbar_menu_refill_credits_edge');
            }
            
            if($menuItem == 'verify_account'){
                $html->setvar('custom_class_for_edge', 'menu_profile_verification_edge');
            }
            // popcorn modified end 2023-10-31

            $menuCounter++;
            $menuBlock = 'menu_item';
            $menuBlockActive = 'menu_item_active';
            if($menuCounter > $menuLimit) {
                $menuBlock = 'menu_more_item';
                $menuBlockActive = 'menu_more_item_active';
            }

//            $langKey = 'menu_' . $menuItem;
//            if(isset($menuItemKeys[$menuItem])) {
//                $langKey = $menuItemKeys[$menuItem];
//            }
            $langKey = self::itemLangKey($menuItem);
            if(!empty($target)) {

                $targetCode = '';
                if(in_array($menuItem,$target)) {
                    $targetCode = 'target="_blank"';
                }
                $html->setvar('target', $targetCode);
            }
            $html->setvar('menu_item_title', l($langKey));
            $html->setvar('menu_item_page', self::menuItemUrl($menuItem));

            // special parse for first item
            if($menuItemStart) {
                $html->parse('menu_item_start', false);
                $menuItemStart = false;
            } else {
                $html->setblockvar('menu_item_start', '');
            }

            // special class of end item
            if ($menuCounter == $menuItemsCount) {
                $html->setvar('class_last', 'last');
            }
            if ($menuCounter == $menuItemsCount && $menuCounter <= $menuLimit) {
                $html->parse('menu_item_end', false);
            }
            // parse active item
            if($menuItem == $menuItemActive) {
                $html->parse($menuBlockActive, false);
            } else {
                $html->setblockvar($menuBlockActive, '');
            }

            $html->parse($menuBlock);
        }
        //nnsscc-diamond-2000229-start
        $lang = loadLanguageAdmin();
        DB::query("SELECT * FROM `pages` WHERE `set` = '' AND `lang` = 'default' AND `section` = 'narrow' ORDER BY position");
        while ($row = DB::fetch_row()) {
            $allowTmpls = explode(',', $row['set']);
            if ($row['system'] && !in_array($optionTmplName, $allowTmpls)) {
                continue;
            }
            if (isset($allowedPage[$row['menu_title']]) && !$allowedPage[$row['menu_title']]) {
                continue;
            }
            $alias = $row['menu_title'];
            $html->setvar('id', $row['id']);
            $html->setvar('system', $row['system']);
            $html->setvar('alias', $alias);
            if ($row['system']) {
                $row['menu_title'] = l($row['menu_title'], $lang);
            }
            $html->setvar('menu_title', $row['menu_title']);
            $html->setvar('menu_item_title', $row['menu_title']);
            $html->setvar('menu_item_page', 'nsc_club.php?id='.$row['id']);
            if ($row['section']=="narrow") {                
                $html->parse('menu_more_nsc_club_item');
            } else {
                $html->clean('menu_more_nsc_club_item');
            }           
        }
        //nnsscc-diamond-2000229-end
        if ($menuCounter > $menuLimit) {
            $html->parse('menu_more');
        }
        if ($menuCounter) {
            $html->parse('menu_site');
        }
    }

    static function parseMainMenuTable(&$html,array $target)
    {
        global $l;
        global $g;

        $menuItems = self::mainMenuItems();

        $menuItemKeys = self::getMenuItemsKeys();
        $menuItemActive = self::getPageActive();

        $menuCounter = 0;
        $menuItemsCount = count($menuItems);
        $numberItemsMenu = Common::getOption('number_items_menu', 'template_options');
        $numberItemsMenu = ($numberItemsMenu == NULL) ? 22 : $numberItemsMenu;
        $numberItemsTd = 2;
        $numberItemsTr = ceil($numberItemsMenu / $numberItemsTd);

        $numberEmpty = 0;
        if ($numberItemsTr > $menuItemsCount) {
            $numberEmpty = $numberItemsTr - $menuItemsCount;
        } elseif ($numberItemsTr < $menuItemsCount
                  && $menuItemsCount < $numberItemsMenu) {
            $numberEmpty = $numberItemsMenu - $menuItemsCount;
        }
        for ($i = 1; $i <= $numberEmpty; $i++) {
            $menuItems['empty' . $i] = 'empty';
        }
        $isParseMenu = false;
        foreach($menuItems as $menuItem => $value) {
            if ($value != 'empty') {

                $isParseMenu = true;
                $html->setvar('menu_item_index', $menuCounter);
                $langKey = self::itemLangKey($menuItem);
                if(!empty($target)) {

                    $targetCode = '';
                    if(in_array($menuItem,$target)) {
                        $targetCode = 'target="_blank"';
                    }
                    $html->setvar('target', $targetCode);
                }
                $html->setvar('menu_item_title', l($langKey));
                $html->setvar('menu_item_page', self::menuItemUrl($menuItem));
                // parse active item
                if($menuItem == $menuItemActive) {
                    $html->parse('menu_item_active_table', false);
                } else {
                    $html->setblockvar('menu_item_active_table', '');
                }
                $html->setblockvar('menu_item_empty_table', '');
                $html->parse('menu_item_table', false);
            } else {

                $html->setblockvar('menu_item_table', '');
                $html->parse('menu_item_empty_table', false);
            }
            $menuCounter++;
            $html->parse('menu_item_table_td', true);
            if ($menuCounter % $numberItemsTr == 0) {
               $html->parse('menu_item_table_tr');
               $html->setblockvar('menu_item_table_td', '');
            }
        }
        if ($isParseMenu) {
            $html->parse('main_menu_table');
        } else {
            $html->parse('main_menu_not');
        }
    }

    static function parseMainMenuOldVersion(&$html)
    {
        global $g;
        global $p;
        global $l;

        $basePages = array(
            "music",
            "vids",
            "events",
            "places",
            // "groups",
            "blogs",
            "games",
            "city",
            "gallery",
            "flashchat",
            "mail",
            "chat",
            "forum",
            "users",
            "search",
            "profile",
            "rating",
            "top5",
            "my",
            "adv",
            "partner",
        );

        if ($g['options']['home_page_mode'] == 'social') {
            $pagesBySettings = array(
                "wall",
            );
        } elseif ($g['options']['home_page_mode'] == 'dating') {
            $pagesBySettings = array(
                "home",
            );
        } else {
            $pagesBySettings = array(
                "home",
                "wall",
            );
        }   


        $pages = array_merge($pagesBySettings, $basePages);

        // check if site sections active before parsing and remove items

        $pages_final = array();

        foreach ($pages as $pageCheck) {

            $page = $pageCheck;
            if ($page == 'vids') {
                $page = 'videogallery';
            }

            if (isset($g['options'][$page])) {
                if ($g['options'][$page] == "Y") {
                    $pages_final[] = $pageCheck;
                }
            } else {
                $pages_final[] = $pageCheck;
            }
        }

        if($g['options']['free_site'] == 'N') {
            if (!User::isPaid(guid())) {
                $pages_final[] = 'upgrade';
            }
        }

        // get language parameter

        $menu_limit = isset($l['all']['menu_items']) ? $l['all']['menu_items'] : 16;
        $menu_counter = 0;
        $menu_more = "";
        $menu_items = count($pages_final);

        foreach ($pages_final as $v) {

            $menu_counter++;

            if ($p == "users_hon.php")
                $pa[0] = "rating";
            elseif ($p == "bookmark_friends.php")
                $pa[0] = "my";
            else {
                $tmp = explode(".", $p);
                $pa = explode("_", $tmp[0]);
            }

            // parse "MORE" items
            if ($menu_counter > $menu_limit)
                $menu_more = "more_";
            // Special class of end item
            if ($menu_items == $menu_counter)
                $html->setvar("class_last", "last");

            if ($v == $pa[0]) {
                $html->parse("menu_$menu_more" . $v . "_active");
            } else {
                $html->parse("menu_$menu_more" . $v . "");
            }

        }
        if ($menu_counter > $menu_limit) {
            $html->parse("menu_next");
        }

    }

    static function getSubmenuItemsList($module='submenu')
    {
        global $p;
        $optionTmplName = Common::getOption('name', 'template_options');
        $items=array();
        if($module=='submenu'){
            $items=array(
                                    'info_mail' => 'mail_on_item',
                                    'info_interests'=>'wink_on_item',
                                    'info_online'=>'online_tab_header_item',
                                    'profile_pics'=>'profile_pics_header_item',
                                    'my_photos'=>'header_gallery_item',
                                    'your_audio'=>'header_music_item',
                                    'your_vids'=>'header_videogallery_item',
                                    'your_blog'=>'header_blogs_item',
                                    'your_settings'=>'header_your_settings_item',
                                    'menu_widgets'=>'header_widgets_item',
                                    'menu_my_account'=>'header_my_account_item',
                                );

        } elseif($module=='header_menu'){
            $items=array(
                                    'header_menu_people_nearby' => 'header_menu_people_nearby_item',
                                    'header_menu_encounters'=>'header_menu_encounters_item',
                                    'header_menu_messages'=>'header_menu_messages_item',
                                    'menu_city'=>'header_menu_city_module_item',
                                    'header_menu_wall'=>'header_menu_wall_item',
                                );
            if (Common::getOption('3dcity_menu_item_position', '3d_city') == 'right' || !Common::isOptionActive('city')) {
                unset($items['menu_city']);
            }
            if (!Common::isWallActive()) {
                unset($items['header_menu_wall']);
            }
            /*if(get_session('set_wall_module') != 'on') {
                unset($items['header_menu_wall']);
            }*/
        } elseif($module=='quick_search'){
            $items=array(
                                    'whos_online' => 'quick_search_whos_online_item',
                                    'whos_new'=>'quick_search_whos_new_item',
                                    'birthdays'=>'quick_search_birthdays_item',
                                    'menu_users_featured'=>'quick_search_menu_users_featured_item',
                                    'i_viewed'=>'quick_search_i_viewed_item',
                                    'viewed_me'=>'quick_search_viewed_me_item',
                                );
        } elseif($module=='profile_tabs'){
            $items=array(
                                    'tab_profile' => 'profile_tabs_tab_profile_item',
                                    'tab_photos'=>'profile_tabs_tab_photos_item',
                                    'header_menu_wall'=>'profile_tabs_wall_item',
                                );
            if ($optionTmplName == 'impact') {
                $items = array(
                            'tab_profile' => 'profile_tabs_tab_profile_item',
                            'tab_photos'=>'profile_tabs_tab_photos_item'
                         );
            }
            if (!Common::isWallActive()) {
                unset($items['header_menu_wall']);
            }
        } elseif($module=='visitor_menu'){
            $items=array(
                                    'chat_nowe' => 'visitor_menu_chat_nowe_item',
                                    'meet'=>'visitor_menu_meet_her_f_item',
                                    'profile_menu_video_chat'=>'visitor_menu_profile_menu_video_chat_item',
                                    'profile_menu_audio_chat'=>'visitor_menu_profile_menu_audio_chat_item',
                                    'profile_menu_street_chat' => 'visitor_menu_profile_menu_street_chat_item',
                                    'gift'=>'visitor_menu_gift_item',
                                    'wink'=>'visitor_menu_wink_item',
                                    'friends'=>'visitor_menu_friends_item',
                                    'block'=>'visitor_menu_block_item',
                                    'profile_menu_report'=>'visitor_menu_profile_menu_report_item',
                                );
        } elseif($module == 'header_menu_impact'){
            $items = array('header_menu_credits_balans' => 'header_menu_credits_balans_item',
                           'header_menu_profile' => 'header_menu_profile_item',
                           // 'header_menu_settings' => 'header_menu_settings_item',
                           'header_menu_upgrade' => 'header_menu_upgrade_item',
                           'header_menu_moderator' => 'header_menu_moderator_item',
                           'header_menu_sign_out' => 'header_menu_sign_out_item'
            );
            if (Common::isOptionActive('free_site') || User::isSuperPowers()) {
                unset($items['header_menu_upgrade']);
            }


            if (!Moderator::checkAccess(true) && $p != 'headermenu_order.php') {
                unset($items['header_menu_moderator']);
            }
            if (!Common::isCreditsEnabled()) {
                unset($items['header_menu_credits_balans']);
            }
        }

        return $items;

    }

    static function isActiveSubmenuItem($module = 'submenu', $item = '')
    {
        if (!$item) {
            return false;
        }
        $orderList = Common::getOption('order_list', $module);
        if ($orderList) {
            $submenuOrderList = unserialize($orderList);
            if (is_array($submenuOrderList)) {
                if (isset($submenuOrderList[$item])) {
                    return $submenuOrderList[$item];
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    static function getIndexItemSubmenu($module, $item)
    {
        $orderTabs = self::getListSubmenu($module);
        return array_search($item, $orderTabs);
    }

    static function getListSubmenu($module = 'submenu')
    {
        $submenuItemsBlocks = Menu::getSubmenuItemsList($module);
        $orderSubmenu=array();
        $orderList = Common::getOption('order_list', $module);
        if ($orderList) {
            $submenu_order_list=unserialize($orderList);
            if(!is_array($submenu_order_list)){
                $submenu_order_list=array();
            }
            foreach($submenu_order_list as $k=>$v){
                if(isset($submenuItemsBlocks[$k])){
                    if($v == 1){
                        $orderSubmenu[] = $submenuItemsBlocks[$k];
                    }
                    unset($submenuItemsBlocks[$k]);
                }
            }
        }

        foreach($submenuItemsBlocks as $k=>$v){
            $orderSubmenu[] = $v;
        }

        return $orderSubmenu;
    }

    /*
        $splitByBlocks - распределение элементов по блокам, массив
                        name_block - имя блока в который должен поместиться пункт,
                        count - количество пунктов, которые помещаются в блок, если 0 - то все оставшиеся
                        $splitByBlocks=array(array('name_block'=>'visitor_menu','count'=>3),
                                             array('name_block'=>'visitor_menu_more','count'=>0),
                                            );

    */
    static function parseSubmenu(&$html, $module='submenu', $selected = 0, $notParseKey = null, $splitByBlocks=array())
    {
        global $g;

        /*$submenuItemsBlocks=Menu::getSubmenuItemsList($module);
        $orderSubmenu=array();

        $orderList=Common::getOption('order_list',$module);
        if($orderList){
            $submenu_order_list=unserialize($orderList);
            if(!is_array($submenu_order_list)){
                $submenu_order_list=array();
            }

            foreach($submenu_order_list as $k=>$v){
                if(isset($submenuItemsBlocks[$k])){
                    if($v==1){
                        $orderSubmenu[]=$submenuItemsBlocks[$k];
                    }
                    unset($submenuItemsBlocks[$k]);
                }
            }
        }

        foreach($submenuItemsBlocks as $k=>$v){
            $orderSubmenu[]=$v;
        }*/

        $orderSubmenu = self::getListSubmenu($module);

        if ($notParseKey !== null) {
            unset_from_array($notParseKey, $orderSubmenu, false);
            $orderSubmenu = array_values($orderSubmenu);
        }

        //print_r_pre($orderSubmenu);
        $selectedItem = 0;
        if (is_string($selected) && in_array($selected, $orderSubmenu)) {
            $selectedItem = array_search($selected, $orderSubmenu);
        } elseif (isset($orderSubmenu[$selected])) {
            $selectedItem = $selected;
        }

        $currentBlock=$module;
        $currentCount=count($orderSubmenu);
        $count=0;

        $blocksForParsing=array();

        if(count($splitByBlocks)>0){
            $tmpBlockParams=current($splitByBlocks);
            $currentBlock=$tmpBlockParams['name_block'];
            $currentCount=$tmpBlockParams['count'];
            if($currentCount==0){
                $currentCount=count($orderSubmenu);
            }
        }

        //Сделать проверку на наличе в массиве $selected - $selectedKey если заданы и если нет то (0)первый делать активный
        foreach($orderSubmenu as $k=>$v){
            if ($selectedItem == $k) {
                $blockSelected = "{$v}_selected";
                $blockSelectedCont = "{$blockSelected}_content";
                $html->parse($blockSelected, false);
                if ($html->blockExists($blockSelectedCont)) {
                    $html->parse($blockSelectedCont, false);
                }
            }
            //$html->parse_to($v,true,$module);

            if($count>=$currentCount){
                $tmpBlockParams=next($splitByBlocks);
                if($tmpBlockParams){
                    $currentBlock=$tmpBlockParams['name_block'];
                    $currentCount=$tmpBlockParams['count'];
                    if($currentCount==0){
                        $currentCount=count($orderSubmenu);
                    }
                    $count=0;
                }

            }

            $html->parse_to($v,true,$currentBlock);

            //Проверяем есть ли содержимое блока (содержимое блока может быть выключено в конфиге в нескольких местах)
            $blName=$html->getname($v, true);
            $block_value = "";
            if (isset($html->blocks[$blName])){
                $block_array = $html->blocks[$blName];
                if(is_array($block_array)){
                    $globals = $html->globals;
                    $array_size = $block_array[0];
                    for($i = 1; $i <= $array_size; $i++){
                        $block_value .= isset($globals[$block_array[$i]]) ? $globals[$block_array[$i]] : "";
                    }
                }
            }

            //если блок не пустой - считаем его
            if(trim($block_value)!=''){
                if(!isset($blocksForParsing[$currentBlock])){
                    $blocksForParsing[$currentBlock]=0;
                }
                $blocksForParsing[$currentBlock]++;
                $count++;
            }
        }

        if(count($orderSubmenu)>0){
            foreach($blocksForParsing as $m=>$v){
                if($v>0){
                    $html->parse($m);
                    if($html->blockexists($m.'_container') && $m!=$module){
                        $html->parse($m.'_container');
                    }
                }
            }
            $html->parse($module.'_container');
        }
    }

    static function updateCounterAjaxImpact($curPage = '', $updateMessages = false)
    {
        global $p;

        $scriptJs = '';
        if ($curPage == 'messages.php' || $updateMessages) {
            $scriptJs .= "messages.updateCounter(" . intval(CIm::getCountNewMessages()) . ");";
        }

        $scriptJs .= "updateCounter('#narrow_blocked_count', " . User::getCountBlocked() . ", true);";
        $viewers = User::getNumberViewersMeProfiles();
        $scriptJs .= "updateCounter('#narrow_visitors_count'," . intval($viewers['count']) . ',' . $viewers['new'] . ",true);";
        $scriptJs .= "updateCountersLikes(checkDataAjax('" . getResponseAjaxByAuth(true, MutualAttractions::getCounters()) . "'));";
        $scriptJs .= 'updateCounter("#narrow_private_photo_count", ' . User::getNumberFriends() . ');';

        return $scriptJs;
    }


    static function getCounterMessagesImpactMobile()
    {
        $countImInfo = CIm::getLastNewMessageInfo();
        $countImInfo['count'] = CIm::getCountNewMessages(null, get_param('user_to'));
        return array('info_im' => $countImInfo,
                                          'info_city' => City::getLastNewMessageInfo());
    }

    static function getListCounterImpactMobile($listCounters = array())
    {
        $viewers = User::getNumberViewersMeProfiles();
        $listCounters['profile_visitors'] = array('count' => $viewers['count'], 'new' => $viewers['new']);
        $listCounters['can_see_your_private_photos'] = array('count' => User::getNumberFriends());

        $listCounters['messages'] = self::getCounterMessagesImpactMobile();

        $listCountersMutual = MutualAttractions::getCounters();
        $listCountersMutualRes = array();
        foreach ($listCountersMutual as $key => $value) {
            if (mb_stripos($key, '_new') === false && mb_stripos($key, 'number_') !== false){
                $listCountersMutualRes[str_replace('number_', '', $key)] = array('count' => $value);
            }
        }

        $listCounters = array_merge($listCounters, $listCountersMutualRes);

        return $listCounters;
    }
}