<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Router {

    static public $includePageCompatibleWithSystem = '';
    static public $isUserPageCompatibleWithSystem = false;

    static function init(){
        global $g;
        global $p;

        $g['router'] = array(
            'load'      => 0,
            'load_core' => 0,
            'page'      => ''
        );

        if ($p == 'router.php') {
            $g['router']['load'] = 1;
            $g['router']['load_core'] = 1;
            $routerPage = isset($_GET['router_page']) ? $_GET['router_page'] : '';
            if (!$routerPage) {
                $routerPage = Router::getIncludePageCompatibleWithSystem();
            }
            $g['router']['page'] = $routerPage . '.php';
            $p = $g['router']['page'];
        }
    }

    static function getIncludePage($nameSeo = null, $checkEnabledGroup = false){
        global $g;

        if (self::$includePageCompatibleWithSystem) {
            return self::$includePageCompatibleWithSystem . '.php';
        }

        if ($nameSeo === null) {
            $nameSeo = get_param('name_seo');
        }

        if (!$nameSeo) {
            return '';
        }

        if ($checkEnabledGroup && get_param_int('group_id') && !Common::isOptionActiveTemplate('groups_social_enabled')) {
            return '';
        }

        $page = '';
        $uid = 0;
        $groupInfo = Groups::getInfoFromNameSeo($nameSeo);

        if ($groupInfo) {//Groups
            $nameSeo = User::getNameSeoFromUid($groupInfo['user_id']);
            if ($nameSeo) {


                $g['is_page_group'] = true;

                if (!Common::isOptionActiveTemplate('groups_social_enabled')) {
                    return '';
                }

                Groups::isBan($groupInfo['group_id']);

                $uid = $groupInfo['user_id'];
                $_GET['name_seo'] = $nameSeo;
                $_GET['group_id'] = $groupInfo['group_id'];

                $_GET['view'] = $groupInfo['page'] ? 'group_page' : 'group';
                $_GET['type_group'] = $groupInfo['page'] ? 'page' : 'group';
            }
        } else {//Users
            $uid = User::getUidFromNameSeo($nameSeo);
        }

        if ($uid && $g['router']['page']) {
            $_GET['display'] = 'profile';
            $page = $g['router']['page'];
            if (!$groupInfo && $page === 'groups_social_subscribers') {
                $page = '';
            }
        }

        return $page;
    }

    /* Name SEO */
    static function getUniqueNameSeo($name) {
        $sql = 'SELECT `name_seo`
                  FROM `groups_social`
                 WHERE `name_seo` LIKE \'' . to_sql($name, 'Plain') . '-%\'' ;
        $allNameSeo = DB::column($sql);

        $sql = 'SELECT `name_seo`
                  FROM `user`
                 WHERE `name_seo` LIKE \'' . to_sql($name, 'Plain') . '-%\'' ;
        $allNameSeoUser = DB::column($sql);

        $allNameSeo = array_merge($allNameSeo, $allNameSeoUser);
        $nameSeo = self::getIndexNameSeo($name, $allNameSeo);
        return $nameSeo;
    }

    static function getIndexNameSeo($name, $allNameSeo) {
        $i = 0;
        do {
            $i++;
            $nameSeo = "{$name}-{$i}";
        } while(in_array($nameSeo, $allNameSeo) && $i < 1000000);
        return $nameSeo;
    }

    static function prepareNameSeo($name){
        if(!$name) {
            $name ='';
        }
        return mb_strtolower(str_replace(array(' ', '?', '*', '|', '>', '<', ':'), '_', $name), 'utf-8');
    }

    static function getNameSeo($name, $id = 0, $type = 'group', $getUnique = true) {
        $name = self::prepareNameSeo($name);
        $sql = 'SELECT `group_id` FROM `groups_social` WHERE `name_seo` = ' . to_sql($name);
        if ($id && $type == 'group') {
            $sql .= ' AND `group_id` != ' . to_sql($id);
        }
        $isNameExists = DB::result($sql, 0, DB_MAX_INDEX);
        if (!$isNameExists) {
            $sql = 'SELECT `user_id` FROM `user` WHERE `name_seo` = ' . to_sql($name);
            if ($id && $type == 'user') {
                $sql .= ' AND `user_id` != ' . to_sql($id);
            }
            $isNameExists = DB::result($sql, 0, DB_MAX_INDEX);
        }
        $nameSeo = '';
        if ($isNameExists) {
            if ($getUnique) {
                $nameSeo = self::getUniqueNameSeo($name);
            }
        } else {
            $nameSeo = $name;
        }
        return $nameSeo;
    }
    /* Name SEO */

    static function checkParamInt($param) {
        return strval($param) === strval(intval($param));
    }

    static function checkParamDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    static function getCheckParam($param) {
        $checkParam = 'Int';
        if ($param == 'date') {
            $checkParam = 'Date';
        }
        return $checkParam;
    }

    static function getIncludePageCompatibleWithSystem($nameSeo = null) {
        if ($nameSeo === null) {
            $nameSeo = get_param('name_seo');
        }

        $includePage = 'search_results';
        if (!$nameSeo) {
            return $includePage;
        }

        $nameSeoParam = $nameSeo;
        $nameSeoPart = array();
        if (mb_strpos($nameSeo, '/', 0, 'UTF-8') !== false) {
            $nameSeoPart = explode('/', $nameSeo);
            $nameSeoParam = $nameSeoPart[0];
        }

        $groupInfo = Groups::getInfoFromNameSeo($nameSeoParam);
        if ($groupInfo) {
            $uid = $groupInfo['user_id'];
        } else {
            $uid = User::getUidFromNameSeo($nameSeoParam);
        }

        //var_dump_pre($uid, true);

        if ($uid) {
            $_GET['name_seo'] = $nameSeoParam;
        } else {
            $_GET['name_seo'] = '';
        }

        $countSeoPart = count($nameSeoPart);

        $nameSeoParamFromCheck = $nameSeoParam;
        $pagesSysytem = self::getPagesCompatibleWithSystem();
        $isPageSysytem = isset($pagesSysytem[$nameSeoParamFromCheck]);
        $lavelUrl = 1;
        if (!$isPageSysytem && $uid && $countSeoPart > 1) {
            $nameSeoParamFromCheck = $nameSeoPart[1];
            $isPageSysytem = isset($pagesSysytem[$nameSeoParamFromCheck]);
            $lavelUrl = 2;
        }

        if ($isPageSysytem) {
            $pagesSysytemData = $pagesSysytem[$nameSeoParamFromCheck];
            $isArrayPagesSysytemData = is_array($pagesSysytemData);

            if ($isArrayPagesSysytemData) {
                $includePage = $pagesSysytemData['page'];
                if (isset($pagesSysytemData['params']) && $pagesSysytemData['params']) {
                    foreach ($pagesSysytemData['params'] as $key => $value) {
                        $_GET[$key] = $value;
                    }
                }
            } else {
                $includePage = $pagesSysytemData;
            }
            if (!$uid) {
                self::$includePageCompatibleWithSystem = $includePage;
            }

            if ($countSeoPart > 1){
                $baseSeoUrl = $countSeoPart - 1;
                if ($baseSeoUrl > 0) {
                    $_GET['base_seo_url'] = $baseSeoUrl;//$lavelUrl
                }

                $paramSecondary = 'page';
                if ($isArrayPagesSysytemData && isset($pagesSysytemData['param_secondary'])) {
                    $paramSecondary = $pagesSysytemData['param_secondary'];
                }
                $paramValue = false;

                if ($paramSecondary == 'date') {
                    if (isset($nameSeoPart[$lavelUrl])) {
                        if (self::checkParamDate($nameSeoPart[$lavelUrl])) {
                            $paramValue = $nameSeoPart[$lavelUrl];
                        }
                    }
                } elseif (isset($nameSeoPart[$lavelUrl]) && self::checkParamInt($nameSeoPart[$lavelUrl])) {
                    $paramValue = intval($nameSeoPart[$lavelUrl]);
                } elseif ($uid) {
                    if (count($nameSeoPart) == 3) {
                        if (self::checkParamInt($nameSeoPart[2])) {
                            $paramValue = intval($nameSeoPart[2]);
                            $_GET['base_seo_url'] = 2;
                        } elseif ($isArrayPagesSysytemData && isset($pagesSysytemData['page_secondary'])) {//Blog post
                            if ($nameSeoPart[2]) {
                                $includePage = $pagesSysytemData['page_secondary'];
                                $paramSecondary = $pagesSysytemData['page_param_secondary'];
                                $paramValue = $nameSeoPart[2];
                                $_GET['base_seo_url'] = 2;
                            }
                        }
                    }
                }

                if ($paramValue !== false) {
                    $_GET[$paramSecondary] = $paramValue ? $paramValue : 1;
                }

                $paramsSecondaryOther = array('param_secondary_1');
                foreach ($paramsSecondaryOther as $param) {
                    if ($isArrayPagesSysytemData && isset($pagesSysytemData[$param])
                            && is_array($pagesSysytemData[$param])) {
                        $param1 = $pagesSysytemData[$param][1];
                        if (isset($nameSeoPart[$param1])) {
                            $param2 = $pagesSysytemData[$param][0];
                            $_GET[$param2] = $nameSeoPart[$param1];
                        }
                    }
                }

            }

            if (isset($pagesSysytemData['params']) && $pagesSysytemData['params']) {
                $_GET['router_query_string'] = http_build_query($pagesSysytemData['params']);
            }
        }

        return $includePage;
    }

    static function getPagesCompatibleWithSystem($isGetKeys = false) {
        global $p;

        if ($p != 'router.php' && !$isGetKeys) {
            return array();
        }

        $pages = array(
            'undefined' => 'undefined',
            'about'                 => 'about',
            'contact'               => 'contact',
            'terms'                 => array('page' => 'info', 'params' => array('page' => 'term_cond')),
            'privacy_policy'        => array('page' => 'info', 'params' => array('page' => 'priv_policy')),

            'index'                 => 'index',
            'login'                 => array('page' => 'join', 'params' => array('cmd' => 'please_login')),
            'join'                  => 'join',
            'join2'                 => 'join2',
            'forget_password'       => 'forget_password',
            'email_not_confirmed'   => 'email_not_confirmed',

            'games'                 => 'games',
            'messages'              => 'messages',
            'page'                  => 'page',
            'audiochat'             => 'audiochat',
            'videochat'             => 'videochat',
            'mutual_attractions'    => 'mutual_attractions',
            'users_viewed_me'       => 'users_viewed_me',
            'profile_view'          => 'profile_view',
            'users_rated_me'        => 'users_rated_me',
            'increase_popularity'   => 'increase_popularity',
            'profile_settings'      => 'profile_settings',
            'mail_whos_interest'    => 'mail_whos_interest',
            'general_chat'          => 'general_chat',
            'moderator'             => 'moderator',
            'upgrade'               => 'upgrade',
            'user_block_list'       => 'user_block_list',
            'search_results'        => 'search_results',
            'encounters'            => array('page' => 'search_results', 'params' => array('display' => 'encounters')),
            'hot_or_not'            => array('page' => 'search_results', 'params' => array('display' => 'encounters')),
            'rate_people'           => array('page' => 'search_results', 'params' => array('display' => 'rate_people')),
            'favorite_list'         => 'favorite_list',

            'my_friends'           => 'my_friends',
            'friends_list'         => 'my_friends',
            'private_photo_access' => 'my_friends',
            'friends'              => array('page' => 'friends_list', 'page_param_secondary' => 'name_seo', 'param_secondary' => 'offset'),
            'friends_online'       => array('page' => 'friends_list_online', 'page_param_secondary' => 'name_seo', 'param_secondary' => 'offset'),

            'blogs_add'                => 'blogs_add',
            'blog_edit'                => array('page' => 'blogs_add', 'param_secondary' => 'blog_id'),
            'blogs'                    => array('page' => 'blogs_list', 'page_secondary' => 'blogs_post', 'page_param_secondary' => 'blog_seo'),
            'blogs_post_liked'         => array('page' => 'search_results', 'params' => array('show' => 'blogs_post_liked'), 'param_secondary' => 'blog_id'),
            'blogs_post_liked_comment' => array('page' => 'search_results', 'params' => array('show' => 'blogs_post_liked_comment'), 'param_secondary' => 'comment_id'),


            'calendar'              => array('page' => 'main_calendar', 'param_secondary' => 'date', 'param_secondary_1' => array('task_id', 3)),
            'task_create'           => array('page' => 'calendar_task_create', 'param_secondary' => 'date'),
            'task_edit'             => array('page' => 'calendar_task_edit', 'param_secondary' => 'event_id'),

            'event_calendar'        => array('page' => 'events_calendar', 'param_secondary' => 'date', 'param_secondary_1' => array('task_id', 3)),
            'hotdate_calendar'              => array('page' => 'hotdates_calendar', 'param_secondary' => 'date', 'param_secondary_1' => array('task_id', 3)),
            'partyhou_calendar'              => array('page' => 'partyhouz_calendar', 'param_secondary' => 'date', 'param_secondary_1' => array('task_id', 3)),

            'photos'                => 'photos_list',
            'profile_photo'         => 'profile_photo',
            'profile_photo_nsc_couple'         => 'profile_photo_nsc_couple',
            'photo_liked'           => array('page' => 'search_results', 'params' => array('show' => 'photo_liked'), 'param_secondary' => 'photo_id'),
            'photo_liked_comment'   => array('page' => 'search_results', 'params' => array('show' => 'photo_liked_comment'), 'param_secondary' => 'comment_id'),


            'vids'                  => 'vids_list',
            'video_liked'           => array('page' => 'search_results', 'params' => array('show' => 'video_liked'), 'param_secondary' => 'video_id'),
            'video_liked_comment'   => array('page' => 'search_results', 'params' => array('show' => 'video_liked_comment'), 'param_secondary' => 'comment_id'),


            'wall'                  => 'wall',
            'wall_liked'            => array('page' => 'search_results', 'params' => array('show' => 'wall_liked'), 'param_secondary' => 'wall_item_id'),
            'wall_liked_comment'    => array('page' => 'search_results', 'params' => array('show' => 'wall_liked_comment'), 'param_secondary' => 'comment_id'),
            'wall_shared'           => array('page' => 'search_results', 'params' => array('show' => 'wall_shared'), 'param_secondary' => 'wall_shared_item_id'),

            /* 3DCity */
            'city'                 => array('page' => 'city', 'params' => array('place' => 'city')),
            'street_chat'          => array('page' => 'city', 'params' => array('place' => 'street_chat')),
            '3d_labyrinth'         => array('page' => 'city', 'params' => array('place' => '3d_labyrinth')),
            '3d_tic_tac_toe'       => array('page' => 'city', 'params' => array('place' => '3d_tic_tac_toe')),
            '3d_connect_four'      => array('page' => 'city', 'params' => array('place' => '3d_connect_four')),
            '3d_chess'             => array('page' => 'city', 'params' => array('place' => '3d_chess')),
            '3d_giant_checkers'    => array('page' => 'city', 'params' => array('place' => '3d_giant_checkers')),
            '3d_sea_battle'        => array('page' => 'city', 'params' => array('place' => '3d_sea_battle')),
            '3d_reversi'           => array('page' => 'city', 'params' => array('place' => '3d_reversi')),
            '3d_hoverboard_racing' => array('page' => 'city', 'params' => array('place' => '3d_hoverboard_racing')),
            '3d_space_racing'      => array('page' => 'city', 'params' => array('place' => '3d_space_racing')),
            '3d_space_labyrinth'   => array('page' => 'city', 'params' => array('place' => '3d_space_labyrinth')),
            '3d_building_room'     => array('page' => 'city', 'params' => array('place' => '3d_building_room')),
            '3d_virtual_office'    => array('page' => 'city', 'params' => array('place' => '3d_virtual_office')),
            '3d_rubiks_cube'       => array('page' => 'city', 'params' => array('place' => '3d_rubiks_cube')),
            '3d_ice_cave_racing'   => array('page' => 'city', 'params' => array('place' => '3d_ice_cave_racing')),
            '3d_neon_racing'       => array('page' => 'city', 'params' => array('place' => '3d_neon_racing')),
            '3d_busy_road'         => array('page' => 'city', 'params' => array('place' => '3d_busy_road')),
            '3d_billiards'         => array('page' => 'city', 'params' => array('place' => '3d_billiards')),
            /* 3DCity */

            'mutual_likes'         => array('page' => 'mutual_attractions'),
            'who_likes_you'        => array('page' => 'mutual_attractions', 'params' => array('cmd' => 'who_likes_you')),
            'whom_you_like'        => array('page' => 'mutual_attractions', 'params' => array('cmd' => 'whom_you_like')),

            'social_network_info'  => array('page' => 'info', 'params' => array('page' => 'social_network_info')),

            /* Groups + Pages */
            'group_add'            => 'group_add',
            'group_edit'           => array('page' => 'group_add', 'param_secondary' => 'group_id', 'params' => array('cmd' => 'edit')),
            'page_add'             => array('page' => 'group_add', 'params' => array('view' => 'group_page')),
            'page_edit'            => array('page' => 'group_add', 'param_secondary' => 'group_id', 'params' => array('cmd' => 'edit', 'view' => 'group_page')),

            'photos_pages'         => array('page' => 'photos_list', 'param_secondary' => 'page', 'params' => array('view_list' => 'group_page')),
            'photos_groups'        => array('page' => 'photos_list', 'param_secondary' => 'page', 'params' => array('view_list' => 'group')),
            'photos_my_pages'      => array('page' => 'photos_list', 'param_secondary' => 'page', 'page_param_secondary' => 'name_seo', 'params' => array('view_list' => 'group_page')),
            'photos_my_groups'     => array('page' => 'photos_list', 'param_secondary' => 'page', 'page_param_secondary' => 'name_seo', 'params' => array('view_list' => 'group')),

            'vids_pages'           => array('page' => 'vids_list', 'param_secondary' => 'page', 'params' => array('view_list' => 'group_page')),
            'vids_groups'          => array('page' => 'vids_list', 'param_secondary' => 'page', 'params' => array('view_list' => 'group')),
            'vids_my_pages'        => array('page' => 'vids_list', 'param_secondary' => 'page', 'page_param_secondary' => 'name_seo', 'params' => array('view_list' => 'group_page')),
            'vids_my_groups'       => array('page' => 'vids_list', 'param_secondary' => 'page', 'page_param_secondary' => 'name_seo', 'params' => array('view_list' => 'group')),


            'songs_pages'          => array('page' => 'songs_list', 'param_secondary' => 'page', 'params' => array('view_list' => 'group_page')),
            'songs_groups'         => array('page' => 'songs_list', 'param_secondary' => 'page', 'params' => array('view_list' => 'group')),
            'songs_my_pages'      => array('page' => 'songs_list', 'param_secondary' => 'page', 'page_param_secondary' => 'name_seo', 'params' => array('view_list' => 'group_page')),
            'songs_my_groups'     => array('page' => 'songs_list', 'param_secondary' => 'page', 'page_param_secondary' => 'name_seo', 'params' => array('view_list' => 'group')),

            'groups'               => array('page' => 'groups_list', 'page_secondary' => 'groups_list', 'page_param_secondary' => 'name_seo'),
            'pages'                => array('page' => 'groups_list', 'page_secondary' => 'groups_list', 'page_param_secondary' => 'name_seo', 'params' => array('view' => 'group_page')),

            'block_list'           => array('page' => 'groups_social_block_list', 'page_param_secondary' => 'name_seo'),
            'subscribers'          => array('page' => 'groups_social_subscribers', 'page_param_secondary' => 'name_seo'),
            'select_group_users'   => array('page' => 'select_group_users', 'page_param_secondary' => 'name_seo'),
            'group_mail'   => array('page' => 'group_mail', 'page_param_secondary' => 'name_seo'),
            'group_invite'   => array('page' => 'group_invite', 'page_param_secondary' => 'name_seo'),
            'group_moderator'   => array('page' => 'group_moderator', 'page_param_secondary' => 'name_seo'),
            'group_owner'   => array('page' => 'group_owner', 'page_param_secondary' => 'name_seo'),

            'liked'                => array('page' => 'groups_social_subscribers', 'page_param_secondary' => 'name_seo'),
            /* Groups + Pages */

            'live'                 => array('page' => 'live_streaming', 'param_secondary' => 'live'),
            'live_'                => array('page' => 'live_streaming', 'param_secondary' => 'live', 'params' => array('stream' => 1)),
            'live_list'            => 'live_list',
            'live_list_finished'   => 'live_list_finished',
            'songs'                => 'songs_list',
            'events_guest_users' => 'events_guest_users',
            'hotdates_guest_users' => 'hotdates_guest_users',
            'partyhouz_guest_users' => 'partyhouz_guest_users',
            'forum' => 'forum',
        );

        return $pages;
    }

}