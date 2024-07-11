<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Class TaskCalendarHotdate {

    static $tableHotdate = 'hotdates_hotdate';
    static $length_title_one = 40; // 
    static $length_title_more = 20; //
    static $uid = null;

    static function getTaskById($id)
    {
        if (!$id) {
            return false;
        }
        $hotdate = DB::one(self::$tableHotdate, 'hotdate_id = ' . to_sql($id));

        return $hotdate;
    }

    static function getWhereByDay($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {
        global $p;

        if ($uid === null) {
            $uid = self::getUid($uid);
        }

        $is_approved = "";
        if(!Common::isOptionActive('hotdates_show_before_approval')) {
            $is_approved = " AND (approved = 1 OR (approved = 0 AND user_id = " . to_sql(guid(), 'Number') . "))";
        }

        if ($where) {
            $where .= ' AND ';
        }

        if ($dayTimeConvert) {
            $dayTime = date("Y-m-d", $dayTime);
        }

        if ($p == 'main_calendar.php' || $p == 'main_calendar_ajax.php') {
            $where_guest = " OR hotdate_id IN (SELECT gh.hotdate_id FROM " . self::$tableHotdate . " AS gh LEFT JOIN hotdates_hotdate_guest AS gg ON gh.hotdate_id = gg.hotdate_id WHERE gg.user_id= " . to_sql($uid) . " AND gh.user_id != " . to_sql($uid) . " )";

            $where .= " `hotdate_datetime` >= '" . $dayTime . "' " .
                " AND `hotdate_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) ".
                " AND ((`user_to` = " . to_sql($uid) . " OR `user_id` = " . to_sql($uid) . ")" . $where_guest . ")" . $is_approved ;
        } else {
            $where .= " `hotdate_datetime` >= '" . $dayTime . "' " .
                " AND `hotdate_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) " . $is_approved;
        }

        $whereSearch = self::getSearchWhere($dayTime, $uid);
        if($whereSearch) {
            $where .= $whereSearch;
        }
        
        return $where;
    }

    static function getSearchWhere($dayTime, $uid)
    {
        $search = get_param('search', '');
        if($search != '1') {
            return false;
        }

        $country_id = get_param('country_id', '');
        $state_id = get_param('state_id', '');
        $city_id = get_param('city_id', '');
        $category_id = get_param('hotdate_category_id', '');
        $distance = get_param('distance', '');
        $couple = get_param('couple', '');
        $male = get_param('male', '');
        $female = get_param('female', '');
        $transgender = get_param('transgender', '');
        $nonbinary = get_param('nonbinary', '');
        $from_add = '';
        $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = e.city_id";
        $from_add .= " LEFT JOIN user AS u ON u.user_id = e.user_id";

        $whereLocation = "";
        if($city_id) {
            if($distance == 'all') {
                $whereLocation = "";
            } elseif ($distance) {
                $whereLocation = inradius($city_id, $distance);
            } else {
                $whereLocation = " AND e.city_id=" . to_sql($city_id, 'Number');
            }
        }

        $whereCategory = '';
        if($category_id != '0' && $category_id) {
            $whereCategory = " AND e.category_id = " . to_sql($category_id, 'Text');
        }

        $conditions = array();

        if ($couple == 1) {
            $conditions[] = '5';
        }

        if ($male == 1) {
            $conditions[] = '1';
        }
        
        if ($female == 1) {
            $conditions[] = '2';
        }

        if ($transgender == 1) {
            $conditions[] = '6';
        }
        
        if ($nonbinary == 1) {
            $conditions[] = '7';
        }


        $inOrentationClause = implode(', ', $conditions);
        if(!$inOrentationClause) {
            $inOrentationClause = to_sql('xxx', 'Text');
        }

        $where_clause = " WHERE 1=1 " . $whereLocation . $whereCategory . " AND u.orientation IN (" . $inOrentationClause . ")";
        $whereSearch = " AND hotdate_id IN (SELECT e.hotdate_id FROM " . self::$tableHotdate . " AS e " . $from_add . $where_clause . ")";

        return $whereSearch;
    }

    static function getSqlTasksByDay($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {

        $where = self::getWhereByDay($dayTime, $where, $uid, $dayTimeConvert);

        $sql = self::$tableHotdate .
               " WHERE " . $where .
               " ORDER BY `hotdate_datetime` ASC, `hotdate_id` ASC";

        return array('query' => $sql, 'columns' => '*');
    }

    static function getCountOpenTasksByCurrentDay($uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }

        $date = time();

        return self::getCountOpenTasksByDay($date, $uid);
    }

    static function getCountOpenTasksByDay($dayTime, $uid)
    {
        $where = self::getWhereByDay($dayTime, '`done_user` = 0', $uid);

        return DB::count(self::$tableHotdate, $where);
    }

    static function getListTasksByDay($dayTime = null, $uid = null, $order = '`hotdate_datetime` ASC, `hotdate_id` ASC', $limit = null)
    {
        if ($uid === null) {
            $uid = guid();
        }

        if ($dayTime === null) {
            $dayTime = time();
        }

        if ($limit === null) {
            $optionTmplName = Common::getTmplName();
            $limit = 4;
            $limitTemplate = Common::getOption('number_notif_title', "{$optionTmplName}_events_settings");
            if ($limitTemplate !== null && $limitTemplate) {
                $limit = $limitTemplate;
            }
        }

        $where = self::getWhereByDay($dayTime, '`done_user` = 0', $uid);

        return DB::select(self::$tableHotdate, $where, $order, $limit);
    }

    static function getNextTask($lastHotdateId = null, $limit = 1)
    {
        if ($lastHotdateId === null) {
            $lastHotdateId = get_param_int('last_id');
        }

        if (!$lastHotdateId) {
            return false;
        }

        $where = 'hotdate_id = ' . to_sql($lastHotdateId);
        $hotdate = DB::one(self::$tableHotdate, $where);
        if (!$hotdate) {
            return false;
        }
        $uid = self::getUid();

        $where = 'hotdate_id < ' . to_sql($lastHotdateId);
        $sqlBase = ChotdatesTools::hotdates_by_calendar_day(strtotime($hotdate['hotdate_datetime']), $where, $uid);
        $hotdate = ChotdatesTools::retrieve_from_sql_base($sqlBase, $limit);

        return $hotdate;
    }

    static function getNextTaskMoreCount($hotdateId)
    {

        $where = 'hotdate_id = ' . to_sql($hotdateId);
        $hotdate = DB::one(self::$tableHotdate, $where);
        if (!$hotdate) {
            return false;
        }

        $uid = self::getUid();

        $where = 'hotdate_id < ' . to_sql($hotdateId);
        $sqlBase = ChotdatesTools::hotdates_by_calendar_day(strtotime($hotdate['hotdate_datetime']), $where, $uid);
        $count = ChotdatesTools::count_from_sql_base($sqlBase);

        return $count;
    }

    static function markSeen($id)
    {
        $guid = guid();
        $where = 'hotdate_id = ' . to_sql($id)
              . ' AND (`user_id` = ' . to_sql($guid) . ' OR `user_to` = ' . to_sql($guid) . ')';
        DB::update(self::$tableHotdate, array('done_new' => 0), $where);
    }

    static function done($hotdateId = null)
    {
        if ($hotdateId === null) {
            $hotdateId = get_param_int('hotdate_id');
        }

        $guid = guid();
        $hotdate = self::getTaskById($hotdateId);
        if($hotdate && ($hotdate['user_id'] == $guid || $hotdate['user_to'] == $guid)){
            $where = 'hotdate_id = ' . to_sql($hotdateId);

            $done = DB::result('SELECT `done_user` FROM ' . self::$tableHotdate . ' WHERE ' . $where);
            $result = $done ? 0 : $guid;
            $new = $result && $done != $guid ? 1 : 0;
            DB::update(self::$tableHotdate, array('done_user' => $result, 'done_new' => $new), $where);
            return $result;

        }
        return false;
    }

    static function getHotdatesOwnerCounts($day_time, $uid)
    {
        $hotdatesOwner = DB::select(self::$tableHotdate, self::getWhereByDay($day_time, '', $uid));
        $checkHotdatesOwner = array('my' => 0, 'other' => 0);
        foreach ($hotdatesOwner as $key => $hotdatesOwnerItem) {
            if ($hotdatesOwnerItem['user_id'] == $uid) {
                $checkHotdatesOwner['my']++;
            } else {
                $checkHotdatesOwner['other']++;
            }
        }

        return $checkHotdatesOwner;
    }

    static function parseHotdate(&$html, $hotdate, $n_results)
    {
        global $g;
        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');
        $guid = guid();

        /* Edge */
        if ($html->varExists('hotdate_id')) {
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
        }

        if ($html->varExists('hotdate_done')) {
            $html->setvar('hotdate_done', $hotdate['done_user']);
        }

        $userInfo = User::getInfoBasic($hotdate['user_id']);
        if ($html->varExists('hotdate_user_name_js')
                && Common::isOptionActive('calendar_item_show_name_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('hotdate_user_name_js', toJs($userInfo['name']));
        }


        if ($html->varExists('hotdate_user_photo')
                && Common::isOptionActive('calendar_item_show_photo_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('hotdate_user_photo', User::getPhotoDefault($hotdate['user_id'], 'm'));
            $html->setvar('hotdate_user_is_online', intval(User::isOnline($hotdate['user_id'], $userInfo)));
        }

        if ($html->varExists('hotdate_user_url')){
            $html->setvar('hotdate_user_url', User::url($hotdate['user_id'], $userInfo));
        }

        if ($html->varExists('hotdate_user_uid')) {
            $html->setvar('hotdate_user_uid', $hotdate['user_id']);
        }

        if ($html->varExists('hotdate_user_to_uid')) {
            $html->setvar('hotdate_user_to_uid', $hotdate['user_to']);
        }

        if ($html->varExists('hotdate_edit_url')) {
            $html->setvar('hotdate_edit_url', $g['path']['url_main']."hotdates_hotdate_edit.php?hotdate_id=".$hotdate['hotdate_id']);
            $html->setvar('hotdate_show_url', $g['path']['url_main']."hotdates_hotdate_show.php?hotdate_id=".$hotdate['hotdate_id']);
        }

        // if ($html->varExists('hotdate_image')) {
            $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id']);
            $html->setvar('hotdate_image', toJs($images['image_thumbnail']));
        // }

        if ($html->varExists('hotdate_title_js')) {
            $html->setvar('hotdate_title_js', toJs($hotdate['hotdate_title']));
        }

        if($html->varExists('hotdate_category')) {
            $html->setvar('hotdate_category', ChotdatesTools::getHotdateCategory($hotdate['category_id']));
        }

        if ($html->varExists('hotdate_description_js') && Common::isOptionActive('calendar_item_show_description', "{$optionTmplName}_events_settings")) {
            $description = Common::parseLinksTag(to_html($hotdate['hotdate_description']), 'a', '&lt;', 'parseLinksSmile');
            $html->setvar('hotdate_description_js', toJs($description));
        }

        if ($html->blockExists('my_hotdate_class')) {
            $html->subcond($hotdate['user_id'] == $guid, 'my_hotdate_class', 'other_hotdate_class');
        }
        /* Edge */

        if ($n_results == 1) {
            $html->setvar('calendar_day_value', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), self::$length_title_one));
            $html->parse('set_day');
        } else {
            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), self::$length_title_more));
        }

        $html->setvar('hotdate_id', $hotdate['hotdate_id']);

        $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));
        if(!$hotdate['hotdate_private']) {
            $html->setvar('hotdate_n_guests', $hotdate['hotdate_n_guests']);
            $html->parse('guests',false);
        } else {
            $html->setblockvar('guests',"");
        }

        $isParseTime = true;
        if ($isCalendarSocial) {
            $isParseTime = Common::isOptionActive('calendar_item_show_time', "{$optionTmplName}_events_settings");
        }
        if ($isParseTime) {
            $html->setvar('hotdate_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdate_time')));
        }

        if (!$isCalendarSocial) {
            $random = true;
            if ($isCalendarSocial) {
                $random = false;
            }
            $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id'], $random);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);
        }

        $city_id = $hotdate['city_id'];
        $city_info = DB::row("SELECT  * FROM geo_city WHERE city_id=" . to_sql($city_id));

        $state_info = null;
        if($city_info) {
            $state_id = $city_info['state_id'];
            $state_info = DB::row("SELECT * FROM geo_state WHERE state_id = " . to_sql($state_id));
        }

        $city_title = isset($city_info['city_title']) ? $city_info['city_title'] : '';
        $state_title = isset($state_info['state_title']) ? $state_info['state_title'] : '';
        
        $state_lang = l('state');
        $city_lang = l('city');
        $hotdate_wall_url = Common::pageUrl('hotdate_wall', $hotdate['hotdate_id']);
        if(!Common::isOptionActive("hotdate_wall_enabled")) {
            $hotdate_wall_url = "";
        }

        $hotdate_address = $hotdate['hotdate_address'];
        $hotdate_place = $hotdate['hotdate_place'];
        $hotdate_site = $hotdate['hotdate_site'];
        $hotdate_phone = $hotdate['hotdate_phone'];
        $approved = $hotdate['approved'];

        $sql = "SELECT * FROM hotdates_hotdate_guest WHERE hotdate_id=" . to_sql($hotdate['hotdate_id']) . " AND user_id=" . to_sql(guid()) . " LIMIT 1";
        $guest_user = DB::row($sql);
        $is_own = $hotdate['user_id'] == guid();
        
        /** popcorn modified 2024-05-23 start*/
        $signin_available = ChotdatesTools::getSignAvailable($hotdate);

        #additional data
        $hotdate_additional_data = array(
            'state_title' => $state_title,
            'city_title' => $city_title,
            'wall' => $hotdate_wall_url,
            'address' => $hotdate_address,
            'place' => $hotdate_place,
            'site' => $hotdate_site,
            'phone' => $hotdate_phone,
            'approved' => $approved,
            'is_member' => isset($guest_user['user_id']) ? true : false,
            'accepted' => (isset($guest_user['accepted']) && $guest_user['accepted'] == 1) ? true  : false,
            'signin_available' => $signin_available,
            'is_finished' => ChotdatesTools::is_hotdate_finished($hotdate),
            'is_own' => $is_own
        );
        $html->setvar('hotdate_additional_data', json_encode($hotdate_additional_data));

        /** popcorn modified 2024-05-23 end*/

        $html->parse('hotdate');
    }

    static function getNumberHotdateLoad()
    {
        $optionTmplName = Common::getTmplName();

        $numberHotdate = 2;
        $numberHotdateTemplate = Common::getOption('number_calendar_item', "{$optionTmplName}_events_settings");
        if ($numberHotdateTemplate !== null && $numberHotdateTemplate) {
            $numberHotdate = $numberHotdateTemplate;
        }
        return $numberHotdate;
    }

    static function getGuestHotdates ($day_time, $uid = null) 
    {
        try {
            global $g_user;

            if ($uid === null) {
                $uid = self::getUid($uid);
            }

            $sql_guest = "SELECT e.* FROM hotdates_hotdate as e
            LEFT JOIN hotdates_hotdate_guest as eg ON e.hotdate_id = eg.hotdate_id
            WHERE eg.user_id = " . to_sql($uid) .
            " AND e.user_id != " . to_sql($uid) .
            " AND e.hotdate_datetime >= '" . date("Y-m-d", $day_time) . "' " .
            " AND e.hotdate_datetime < DATE_ADD('" . date("Y-m-d", $day_time) . "', INTERVAL 1 DAY)";
            $guest_hotdates = DB::rows($sql_guest);
    
            // var_dump($sql_guest); die();
            $n = 0;
            foreach ($guest_hotdates as $key => $guest_hotdate) {
                $n++;
            } 
            return $guest_hotdates;

        } catch (\Throwable $th) {
            return [];
        }
    }

    static function getGuestHotdatesCount ($guest_hotdates) {
        $n = 0;
        foreach ($guest_hotdates as $key => $guest_hotdate) {
            $n++;
        } 

        return $n;
    }

    static function parseHotdatesDay(&$html, $day_time, $uid = null, $hotdate_id = '')
    {
        global $p;
        global $g;

        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');
        $hotdateDayLoadMore = get_param('hotdate_day_load_more');
        $guid = guid();
        $uid = self::getUid($uid);

        $html->clean('day_action');
        $html->clean('hotdate');
        $html->clean('pager');

        // $guest_hotdates = self::getGuestHotdates($day_time, $uid);
        // $n = self::getGuestHotdatesCount($guest_hotdates);
        $calendar_day = Common::dateFormat($day_time,'calendar_day',false);

        $today = date("Ymd", $day_time) == date("Ymd");

        $html->setvar('calendar_day', $calendar_day);
        $html->setvar('day_time', $day_time);
        $html->setvar('calendar_day_title', l(date("D", $day_time)));

        if ($isCalendarSocial) {
            $vars = array(
                'datetime_day' => date("j", $day_time)
            );
            $html->assign('hotdate', $vars);
        }

        $html->setvar('calendar_datetime', Common::dateFormat($day_time,'calendar_datetime', false, false, true));

        if ($isCalendarSocial) {
            $hotdatesOwner = self::getHotdatesOwnerCounts($day_time, $uid);
            // $hotdatesOwner['other'] = $hotdatesOwner['other'] + $n;
            $html->setvar('day_owners', json_encode($hotdatesOwner));
        }

        $sql_base = ChotdatesTools::hotdates_by_calendar_day($day_time, '', $uid);

        //print_r($sql_base, true);
        $n_results = ChotdatesTools::count_from_sql_base($sql_base);
        // $n_results = $n_results + $n;
        if ($n_results == 1) {
            $html->setvar('empty', 'one_');
        } elseif ($n_results > 1) {
            $html->setvar('empty', 'full_');
        } else {
            $html->setvar('empty', 'empty_');
        }

        if ($n_results != 1) {
            $html->setvar('calendar_day_value', Common::dateFormat($day_time,'calendar_day_value', false, false, true));
            $html->parse('set_day');
        }

        if(date("N", $day_time) > 5) {
            if($today) {
                $html->parse('holiday_today', false);
                $html->clean('holiday_not_today');
            } else {
                $html->parse('holiday_not_today', false);
                $html->clean('holiday_today');
            }

            $html->parse('holiday', false);
            $html->clean('not_holiday');
        } else {
            if($today) {
                $html->parse('today', false);
                $html->clean('not_today');
            } else {
                $html->parse('not_today', false);
                $html->clean('today');
            }

            $html->parse('not_holiday', false);
            $html->clean('holiday');
        }

        $n_results_per_page = self::getNumberHotdateLoad();

        if($n_results) {

            $page = intval(get_param('hotdate_calendar_day_page', 1));
            $n_pages = ceil($n_results / $n_results_per_page);
            $page = max(1, min($n_pages, $page));

            $html->setvar('page', $page);

            $limit = $n_results_per_page;
            $shift = ($page - 1) * $n_results_per_page;

            if($hotdate_id) {
                $limit = 0;
                $shift = 0;
            }

            $hotdates = ChotdatesTools::retrieve_from_sql_base($sql_base, $limit, $shift);

            if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
                $whereNotifId = '';
                $hotdateIdNotif = get_param_int('neid');
                if (!$hotdateIdNotif) {
                    $hotdateIdNotif = get_param_int('task_id');
                }

                $isCheckHotdate = false;
                if ($p == 'hotdates_calendar.php' && $hotdateIdNotif) {
                    $html->setvar('highlight_hotdate_id', $hotdateIdNotif);
                    $html->parse('highlight_hotdate', false);

                    foreach ($hotdates as $key => $hotdate) {
                        if ($hotdate['hotdate_id'] == $hotdateIdNotif) {
                            $isCheckHotdate = true;
                            break;
                        }
                    }
                    if (!$isCheckHotdate) {
                        $limit = 0;
                        $sql_base = ChotdatesTools::hotdates_by_calendar_day($day_time, '`hotdate_id` <= ' . to_sql($hotdateIdNotif), $uid);
                        $hotdates = ChotdatesTools::retrieve_from_sql_base($sql_base);
                        if ($n_results == count($hotdates)) {
                            $n_results = $n_results_per_page;
                        }
                    }
                }

                $whereDone = '`done_new` = 1';
                $whereUpdateDone = TaskCalendarHotdate::getWhereByDay($day_time, $whereDone, $uid);

                $sqlLimit = ($limit ? " LIMIT " .  intval($shift) . ", " . intval($limit) : '');
                $sqlSelectDone_1 = "SELECT hotdate_id FROM " . self::$tableHotdate
                                 . ' WHERE ' . $whereUpdateDone
                                 . $sqlLimit;

                $sqlSelectDone = 'SELECT hotdate_id FROM (' . $sqlSelectDone_1 . ') tmp';
                $sqlUpdateDone = 'UPDATE ' . self::$tableHotdate . ' SET `done_new` = 0
                                    WHERE  hotdate_id IN (' .  $sqlSelectDone . ')';
                DB::execute($sqlUpdateDone);
            }
            
            // foreach ($guest_hotdates as $key => $guest_hotdate) {
            //     array_push($hotdates, $guest_hotdate);
            // }

            foreach($hotdates as $hotdate) {
                self::parseHotdate($html, $hotdate, $n_results);
            }


            if($n_pages > 1){
                if($page > 1) {   
                    $html->setvar('page_n', $page-1);
                    $html->parse('pager_prev');
                } else {
                    $html->parse('pager_prev_inactive');
                }

                if($page < $n_pages) {
                    $html->setvar('page_n', $page+1);
                    $html->parse('pager_next');
                } else {
                    $html->parse('pager_next_inactive');
                }

                $html->parse('pager');
            }
        } else {
            //$html->clean('hotdate');
        }

        if ($n_results > $n_results_per_page) {
            $html->setvar('hotdates_num', $n_results - $n_results_per_page);
            $html->parse('block_hotdates_num', false);
        } else {
            $html->setvar('hotdates_num', 0);
            $html->clean('block_hotdates_num');
        }

        if ($isCalendarSocial && !$hotdateDayLoadMore) {
            $actionTitle = '';
            if (!$n_results) {
                $actionTitle = toJsL('no_task');
            }
            $html->setvar('hotdate_title_js', $actionTitle);
            $html->setvar('url_create_new_item', $g['path']['url_main']."hotdates_hotdate_edit.php");
            $html->parse('day_action', false);
        }

        $html->parse('day');
    }

    static function searchUsersFromName()
    {
        $responseData = '';
        $name = trim(get_param('name'));
        if ($name) {
            $sql = 'SELECT `user_id`, `name` FROM `user` WHERE `name` LIKE "' . DB::esc_like($name) . '%"';
            $users = DB::rows($sql);
            if ($users) {
                foreach ($users as $key => $user) {
                    $responseData .= '<li class="search_user_item" data-name="' . toAttr($user['name']) . '" data-uid="' . $user['user_id'] . '" >' . $user['name'] . '</li>';
                }
            }
        }
        return $responseData;
    }

    static function getUid($uid = null)
    {
        if (self::$uid !== null) {
            return self::$uid;
        }

        if ($uid === null) {
            $uid = User::getParamUid(guid());
        }

        self::$uid = $uid;

        return $uid;
    }

    static function getNotifTitle($count = null, $uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }
        if ($count === null) {
            $count = self::getCountOpenTasksByCurrentDay($uid);
        }
        if ($count) {
            $lTasks = $count == 1 ? 'active_tasks_one' : 'active_tasks';
            $tasksList = TaskCalendarHotdate::getListTasksByDay(null, null, '`hotdate_datetime` DESC, `hotdate_id` DESC');
            $countList = count($tasksList);

            $tasksListTitle = array();
            foreach ($tasksList as $task) {
                $tasksListTitle[] = $task['hotdate_title'];
            }
            $tasksListTitle = implode(', ', $tasksListTitle);
            $lData = array('task_list' => trim($tasksListTitle));
            if ($count > $countList) {
                $lTasks = 'active_tasks_all';
                $lData['task_more'] = $count - $countList;
            }
            $newTasksTitle = lSetVars($lTasks, $lData);
        } else {
            $newTasksTitle = l('notification_title_calendar');
        }

        return $newTasksTitle;
    }
}