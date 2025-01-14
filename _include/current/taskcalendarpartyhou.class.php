<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Class TaskCalendarPartyhou {

    static $tablePartyhou = 'partyhouz_partyhou';
    static $length_title_one = 40; //
    static $length_title_more = 20; //
    static $uid = null;

    static function getTaskById($id)
    {
        if (!$id) {
            return false;
        }
        $partyhou = DB::one(self::$tablePartyhou, 'partyhou_id = ' . to_sql($id));

        return $partyhou;
    }

    static function getWhereByDay($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {
        global $p;

        if ($uid === null) {
            $uid = self::getUid($uid);
        }

        $is_approved = "";
        if(!Common::isOptionActive('partyhouz_show_before_approval')) {
            $is_approved = " AND (approved = 1 OR (approved = 0 AND user_id = " . to_sql(guid(), 'Number') . "))";
        }

        if ($where) {
            $where .= ' AND ';
        }

        if ($dayTimeConvert) {
            $dayTime = date("Y-m-d", $dayTime);
        }

        if ($p == 'main_calendar.php' || $p == 'main_calendar_ajax.php') {
            $where_guest = " OR partyhou_id IN (SELECT gp.partyhou_id FROM " . self::$tablePartyhou . " AS gp LEFT JOIN partyhouz_partyhou_guest AS gg ON gp.partyhou_id = gg.partyhou_id WHERE gg.user_id= " . to_sql($uid) . " AND gp.user_id != " . to_sql($uid) . " )";
            $where .= " `partyhou_datetime` >= '" . $dayTime . "' " .
                " AND `partyhou_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) ".
                " AND ((`user_to` = " . to_sql($uid) . " OR `user_id` = " . to_sql($uid) . ")" . $where_guest . ")" . $is_approved ;
        } else {
            $where .= " `partyhou_datetime` >= '" . $dayTime . "' " .
                  " AND `partyhou_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) " . $is_approved ;
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
        $category_id = get_param('partyhou_category_id', '');
        $distance = get_param('distance', '');
        $couple = get_param('couple', '');
        $male = get_param('male', '');
        $female = get_param('female', '');
        $transgender = get_param('transgender', '');
        $nonbinary = get_param('nonbinary', '');

        $locked = get_param('partyhouz_locked');
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

        //locked
        $whereLocked = '';
        if($locked == 'all') {
            $whereLocked = "";
        } elseif($locked == 'lock') {
            $whereLocked = " AND e.is_lock=1";
        } elseif($locked == 'unlock') {
            $whereLocked = " AND e.is_lock=0";
        }

        $inOrentationClause = implode(', ', $conditions);
        if(!$inOrentationClause) {
            $inOrentationClause = to_sql('xxx', 'Text');
        }

        $where_clause = " WHERE 1=1 " . $whereLocation . $whereCategory . $whereLocked . " AND u.orientation IN (" . $inOrentationClause . ")";
        $whereSearch = " AND partyhou_id IN (SELECT e.partyhou_id FROM " . self::$tablePartyhou . " AS e " . $from_add . $where_clause . ")";

        return $whereSearch;
    }

    static function getSqlTasksByDay($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {
        $where = self::getWhereByDay($dayTime, $where, $uid, $dayTimeConvert);

        $sql = self::$tablePartyhou .
               " WHERE " . $where .
               " ORDER BY `partyhou_datetime` ASC, `partyhou_id` ASC";

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

        return DB::count(self::$tablePartyhou, $where);
    }

    static function getListTasksByDay($dayTime = null, $uid = null, $order = '`partyhou_datetime` ASC, `partyhou_id` ASC', $limit = null)
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

        return DB::select(self::$tablePartyhou, $where, $order, $limit);
    }

    static function getNextTask($lastPartyhouId = null, $limit = 1)
    {
        if ($lastPartyhouId === null) {
            $lastPartyhouId = get_param_int('last_id');
        }

        if (!$lastPartyhouId) {
            return false;
        }

        $where = 'partyhou_id = ' . to_sql($lastPartyhouId);
        $partyhou = DB::one(self::$tablePartyhou, $where);
        if (!$partyhou) {
            return false;
        }
        $uid = self::getUid();

        $where = 'partyhou_id < ' . to_sql($lastPartyhouId);
        $sqlBase = CpartyhouzTools::partyhouz_by_calendar_day(strtotime($partyhou['partyhou_datetime']), $where, $uid);
        $partyhou = CpartyhouzTools::retrieve_from_sql_base($sqlBase, $limit);

        return $partyhou;
    }

    static function getNextTaskMoreCount($partyhouId)
    {

        $where = 'partyhou_id = ' . to_sql($partyhouId);
        $partyhou = DB::one(self::$tablePartyhou, $where);
        if (!$partyhou) {
            return false;
        }

        $uid = self::getUid();

        $where = 'partyhou_id < ' . to_sql($partyhouId);
        $sqlBase = CpartyhouzTools::partyhouz_by_calendar_day(strtotime($partyhou['partyhou_datetime']), $where, $uid);
        $count = CpartyhouzTools::count_from_sql_base($sqlBase);

        return $count;
    }

    static function markSeen($id)
    {
        $guid = guid();
        $where = 'partyhou_id = ' . to_sql($id)
              . ' AND (`user_id` = ' . to_sql($guid) . ' OR `user_to` = ' . to_sql($guid) . ')';
        DB::update(self::$tablePartyhou, array('done_new' => 0), $where);
    }

    static function done($partyhouId = null)
    {
        if ($partyhouId === null) {
            $partyhouId = get_param_int('partyhou_id');
        }

        $guid = guid();
        $partyhou = self::getTaskById($partyhouId);
        if($partyhou && ($partyhou['user_id'] == $guid || $partyhou['user_to'] == $guid)){
            $where = 'partyhou_id = ' . to_sql($partyhouId);

            $done = DB::result('SELECT `done_user` FROM ' . self::$tablePartyhou . ' WHERE ' . $where);
            $result = $done ? 0 : $guid;
            $new = $result && $done != $guid ? 1 : 0;
            DB::update(self::$tablePartyhou, array('done_user' => $result, 'done_new' => $new), $where);
            return $result;

        }
        return false;
    }

    static function getPartyhouzOwnerCounts($day_time, $uid)
    {
        $partyhouzOwner = DB::select(self::$tablePartyhou, self::getWhereByDay($day_time, '', $uid));
        $checkPartyhouzOwner = array('my' => 0, 'other' => 0);
        foreach ($partyhouzOwner as $key => $partyhouzOwnerItem) {
            if ($partyhouzOwnerItem['user_id'] == $uid) {
                $checkPartyhouzOwner['my']++;
            } else {
                $checkPartyhouzOwner['other']++;
            }
        }

        return $checkPartyhouzOwner;
    }

    static function parsePartyhou(&$html, $partyhou, $n_results)
    {
        global $g;
        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('partyhou_social_enabled');
        $guid = guid();

        /* Edge */
        if ($html->varExists('partyhou_id')) {
            $html->setvar('partyhou_id', $partyhou['partyhou_id']);
        }

        if ($html->varExists('partyhou_done')) {
            $html->setvar('partyhou_done', $partyhou['done_user']);
        }

        $userInfo = User::getInfoBasic($partyhou['user_id']);
        if ($html->varExists('partyhou_user_name_js')
                && Common::isOptionActive('calendar_item_show_name_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('partyhou_user_name_js', toJs($userInfo['name']));
        }


        if ($html->varExists('partyhou_user_photo')
                && Common::isOptionActive('calendar_item_show_photo_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('partyhou_user_photo', User::getPhotoDefault($partyhou['user_id'], 'm'));
            $html->setvar('partyhou_user_is_online', intval(User::isOnline($partyhou['user_id'], $userInfo)));
        }

        if ($html->varExists('partyhou_user_url')){
            $html->setvar('partyhou_user_url', User::url($partyhou['user_id'], $userInfo));
        }

        if ($html->varExists('partyhou_user_uid')) {
            $html->setvar('partyhou_user_uid', $partyhou['user_id']);
        }

        if ($html->varExists('partyhou_user_to_uid')) {
            $html->setvar('partyhou_user_to_uid', $partyhou['user_to']);
        }

        if ($html->varExists('partyhou_edit_url')) {
            $html->setvar('partyhou_edit_url', $g['path']['url_main']."partyhouz_partyhou_edit.php?partyhou_id=".$partyhou['partyhou_id']);
            $html->setvar('partyhou_show_url', $g['path']['url_main']."partyhouz_partyhou_show.php?partyhou_id=".$partyhou['partyhou_id']);
        }

        // if ($html->varExists('partyhou_image')) {
            $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id']);
            $html->setvar('partyhou_image', toJs($images['image_thumbnail']));
        // }

        if ($html->varExists('partyhou_title_js')) {
            $html->setvar('partyhou_title_js', toJs($partyhou['partyhou_title']));
        }

        if($html->varExists('partyhou_category')) {
            $category_txt = CpartyhouzTools::getPartyhouCategory($partyhou['category_id']) . ($partyhou['is_lock'] == '1' ? l('partyhou_locked') : l('partyhou_unlocked'));
            $html->setvar('partyhou_category', $category_txt);
        }

        if ($html->varExists('partyhou_description_js') && Common::isOptionActive('calendar_item_show_description', "{$optionTmplName}_events_settings")) {
            $description = Common::parseLinksTag(to_html($partyhou['partyhou_description']), 'a', '&lt;', 'parseLinksSmile');
            $html->setvar('partyhou_description_js', toJs($description));
        }

        if ($html->blockExists('my_partyhou_class')) {
            $html->subcond($partyhou['user_id'] == $guid, 'my_partyhou_class', 'other_partyhou_class');
        }
        /* Edge */

        if ($n_results == 1) {
            $html->setvar('calendar_day_value', $partyhou['partyhou_id']);
            $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), self::$length_title_one));
            $html->parse('set_day');
        } else {
            $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), self::$length_title_more));
        }

        $html->setvar('partyhou_id', $partyhou['partyhou_id']);

        $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));
        if(!$partyhou['partyhou_private']) {
            $html->setvar('partyhou_n_guests', $partyhou['partyhou_n_guests']);
            $html->parse('guests',false);
        } else {
            $html->setblockvar('guests',"");
        }

        $isParseTime = true;
        if ($isCalendarSocial) {
            $isParseTime = Common::isOptionActive('calendar_item_show_time', "{$optionTmplName}_events_settings");
        }
        if ($isParseTime) {
            $html->setvar('partyhou_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhou_time')));
        }

        if (!$isCalendarSocial) {
            $random = true;
            if ($isCalendarSocial) {
                $random = false;
            }
            $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id'], $random);
            
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);
        }

        
        $city_id = $partyhou['city_id'];
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
        $partyhou_wall_url = Common::pageUrl('partyhou_wall', $partyhou['partyhou_id']);
        if(!Common::isOptionActive("partyhou_wall_enabled")) {
            $partyhou_wall_url = "";
        }

        $partyhou_address = $partyhou['partyhou_address'];
        $partyhou_place = $partyhou['partyhou_place'];
        $partyhou_site = $partyhou['partyhou_site'];
        $partyhou_phone = $partyhou['partyhou_phone'];
        $approved = $partyhou['approved'];

        $sql = "SELECT * FROM partyhouz_partyhou_guest WHERE partyhou_id=" . to_sql($partyhou['partyhou_id']) . " AND user_id=" . to_sql(guid()) . " LIMIT 1";
        $guest_user = DB::row($sql);
        $is_own = $partyhou['user_id'] == guid();
        
        /** popcorn modified 2024-05-23 start*/
        #additional data

        $signin_available = CpartyhouzTools::getSignAvailable($partyhou);
        if ($partyhou['is_open_partyhouz'] == 1) {
            $partyhouId = $partyhou['partyhou_id'];
            $sql = "SELECT partyhouz_open.resets 
            FROM partyhouz_partyhou AS m 
            LEFT JOIN partyhouz_open ON FIND_IN_SET(m.partyhou_id, partyhouz_open.partyhou_ids) 
            WHERE m.partyhou_id = $partyhouId ";

            $partyhou_open_resets = DB::row($sql);
            $resets = $partyhou_open_resets['resets'];
        } else {
            $resets = Common::getOption('normal_partyhouz_delay_time', 'options');
        }

        $partyhouEndDatetime = new DateTime($partyhou['partyhou_datetime']);
        $partyhouEndDatetime->modify('+' . $resets . ' minutes');
        $partyhouEndDatetime = $partyhouEndDatetime->format('Y-m-d H:i:s');
        $partyhouEndDatetime_time = to_html(Common::dateFormat($partyhouEndDatetime, 'partyhou_time'));

        $partyhou_additional_data = array(
            'state_title' => $state_title,
            'city_title' => $city_title,
            'wall' => $partyhou_wall_url,
            'address' => $partyhou_address,
            'join_url' => Common::getPartyhouJoinPageUrl($partyhou['partyhou_id']),
            'partyhou_end_time' => $partyhouEndDatetime_time,
            'place' => $partyhou_place,
            'site' => $partyhou_site,
            'phone' => $partyhou_phone,
            'approved' => $approved,
            'is_member' => isset($guest_user['user_id']) ? true : false,
            'accepted' => (isset($guest_user['accepted']) && $guest_user['accepted'] == 1) ? true  : false,
            'signin_available' => $signin_available,
            'is_finished' => CpartyhouzTools::is_partyhou_finished($partyhou),
            'type' => 'partyhou',
            'is_own' => $is_own
        );
        $html->setvar('partyhou_additional_data', json_encode($partyhou_additional_data));
        /** popcorn modified 2024-05-23 end*/

        $html->parse('partyhou');
    }

    static function getNumberPartyhouLoad()
    {
        $optionTmplName = Common::getTmplName();

        $numberPartyhou = 2;
        $numberPartyhouTemplate = Common::getOption('number_calendar_item', "{$optionTmplName}_events_settings");
        if ($numberPartyhouTemplate !== null && $numberPartyhouTemplate) {
            $numberPartyhou = $numberPartyhouTemplate;
        }
        return $numberPartyhou;
    }

    static function getGuestPartyhouz ($day_time, $uid = null) 
    {
        try {

            if ($uid === null) {
                $uid = self::getUid($uid);
            }
            global $g_user;

            $is_approved = " AND (e.approved = 1 OR (e.approved = 0 AND e.user_id = " . to_sql($uid, 'Number') . "))";
            $sql_guest = "SELECT e.* FROM partyhouz_partyhou as e
            LEFT JOIN partyhouz_partyhou_guest as eg ON e.partyhou_id = eg.partyhou_id
            WHERE eg.user_id = " . to_sql($uid) .
            " AND e.user_id != " . to_sql($uid) .
            " AND e.partyhou_datetime >= '" . date("Y-m-d", $day_time) . "' " .
            " AND e.partyhou_datetime < DATE_ADD('" . date("Y-m-d", $day_time) . "', INTERVAL 1 DAY)" . $is_approved ;
            $guest_partyhouz = DB::rows($sql_guest);
    
            var_dump($sql_guest); die();
            $n = 0;
            foreach ($guest_partyhouz as $key => $guest_partyhou) {
                $n++;
            } 
            return $guest_partyhouz;

        } catch (\Throwable $th) {
            return [];
        }
    }

    static function getGuestPartyhouzCount ($guest_partyhouz) {
        $n = 0;
        foreach ($guest_partyhouz as $key => $guest_partyhou) {
            $n++;
        } 

        return $n;
    }

    static function parsePartyhouzDay(&$html, $day_time, $uid = null, $partyhou_id = '')
    {
        global $p;
        global $g;

        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('partyhou_social_enabled');
        $partyhouDayLoadMore = get_param('partyhou_day_load_more');
        $guid = guid();
        $uid = self::getUid($uid);

        $html->clean('day_action');
        $html->clean('partyhou');
        $html->clean('pager');

        // $guest_partyhouz = self::getGuestPartyhouz($day_time, $uid);
        // $n = self::getGuestPartyhouzCount($guest_partyhouz);
        $calendar_day = Common::dateFormat($day_time,'calendar_day',false);

        $today = date("Ymd", $day_time) == date("Ymd");

        $html->setvar('calendar_day', $calendar_day);
        $html->setvar('day_time', $day_time);
        $html->setvar('calendar_day_title', l(date("D", $day_time)));

        if ($isCalendarSocial) {
            $vars = array(
                'datetime_day' => date("j", $day_time)
            );
            $html->assign('partyhou', $vars);
        }

        $html->setvar('calendar_datetime', Common::dateFormat($day_time,'calendar_datetime', false, false, true));

        if ($isCalendarSocial) {
            $partyhouzOwner = self::getPartyhouzOwnerCounts($day_time, $uid);
            // $partyhouzOwner['other'] = $partyhouzOwner['other'] + $n;
            $html->setvar('day_owners', json_encode($partyhouzOwner));
        }

        $sql_base = CpartyhouzTools::partyhouz_by_calendar_day($day_time, '', $uid);

        //print_r($sql_base, true);
        $n_results = CpartyhouzTools::count_from_sql_base($sql_base);
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

        $n_results_per_page = self::getNumberPartyhouLoad();               
                
        if($n_results) {

            $page = intval(get_param('partyhou_calendar_day_page', 1));
            $n_pages = ceil($n_results / $n_results_per_page);
            $page = max(1, min($n_pages, $page));

            $html->setvar('page', $page);

            $limit = $n_results_per_page;
            $shift = ($page - 1) * $n_results_per_page;

            if($partyhou_id) {
                $limit = 0;
                $shift = 0;
            }

            $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_base, $limit, $shift);
            if (Common::isOptionActiveTemplate('partyhou_social_enabled')) {
                $whereNotifId = '';
                $partyhouIdNotif = get_param_int('neid');
                if (!$partyhouIdNotif) {
                    $partyhouIdNotif = get_param_int('task_id');
                }

                $isCheckPartyhou = false;
                if ($p == 'partyhouz_calendar.php' && $partyhouIdNotif) {
                    $html->setvar('highlight_partyhou_id', $partyhouIdNotif);
                    $html->parse('highlight_partyhou', false);

                    foreach ($partyhouz as $key => $partyhou) {
                        if ($partyhou['partyhou_id'] == $partyhouIdNotif) {
                            $isCheckPartyhou = true;
                            break;
                        }
                    }
                    if (!$isCheckPartyhou) {
                        $limit = 0;
                        $sql_base = CpartyhouzTools::partyhouz_by_calendar_day($day_time, '`partyhou_id` <= ' . to_sql($partyhouIdNotif), $uid);
                        $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_base);
                        if ($n_results == count($partyhouz)) {
                            $n_results = $n_results_per_page;
                        }
                    }
                }

                $whereDone = '`done_new` = 1';
                $whereUpdateDone = TaskCalendarPartyhou::getWhereByDay($day_time, $whereDone, $uid);

                $sqlLimit = ($limit ? " LIMIT " .  intval($shift) . ", " . intval($limit) : '');
                $sqlSelectDone_1 = "SELECT partyhou_id FROM " . self::$tablePartyhou
                                 . ' WHERE ' . $whereUpdateDone
                                 . $sqlLimit;

                $sqlSelectDone = 'SELECT partyhou_id FROM (' . $sqlSelectDone_1 . ') tmp';
                $sqlUpdateDone = 'UPDATE ' . self::$tablePartyhou . ' SET `done_new` = 0
                                    WHERE  partyhou_id IN (' .  $sqlSelectDone . ')';
                DB::execute($sqlUpdateDone);
            }
            
            foreach($partyhouz as $partyhou) {
                self::parsePartyhou($html, $partyhou, $n_results);
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
            //$html->clean('partyhou');
        }

        if ($n_results > $n_results_per_page) {
            $html->setvar('partyhouz_num', $n_results - $n_results_per_page);
            $html->parse('block_partyhouz_num', false);
        } else {
            $html->setvar('partyhouz_num', 0);
            $html->clean('block_partyhouz_num');
        }

        if ($isCalendarSocial && !$partyhouDayLoadMore) {
            $actionTitle = '';
            if (!$n_results) {
                $actionTitle = toJsL('no_task');
            }
            $html->setvar('partyhou_title_js', $actionTitle);
            $html->setvar('url_create_new_item', $g['path']['url_main']."partyhouz_partyhou_edit.php");
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
            $tasksList = TaskCalendarPartyhou::getListTasksByDay(null, null, '`partyhou_datetime` DESC, `partyhou_id` DESC');
            $countList = count($tasksList);

            $tasksListTitle = array();
            foreach ($tasksList as $task) {
                $tasksListTitle[] = $task['partyhou_title'];
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