<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Class TaskCalendarMain {

    static $tableEvent = 'events_event';
    static $tableHotdate = 'hotdates_hotdate';
    static $tablePartyhou = 'partyhouz_partyhou';

    static $length_title_one = 40; // Длинна названия если событие одно
    static $length_title_more = 20; // Длинна названия если событий несколько
    static $uid = null;

    static function getTaskById($id)
    {
        if (!$id) {
            return false;
        }
        $event = DB::one(self::$tableEvent, 'event_id = ' . to_sql($id));

        return $event;
    }

    static function getWhereByDay($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {

        if ($uid === null) {
            $uid = self::getUid($uid);
        }

        $is_approved = "";
        if(!Common::isOptionActive('events_show_before_approval')) {
            $is_approved = " AND (approved = 1 OR (approved = 0 AND user_id = " . to_sql(guid(), 'Number') . "))";
        }
        
        if ($where) {
            $where .= ' AND ';
        }
        if ($dayTimeConvert) {
            $dayTime = date("Y-m-d", $dayTime);
        }
        $where .= " `event_datetime` >= '" . $dayTime . "' " .
                  " AND `event_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) " .
                  " AND `user_to` = " . to_sql($uid) . $is_approved;
        return $where;
    }

    static function getWhereByDayHotdate($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {
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

        $where .= " `hotdate_datetime` >= '" . $dayTime . "' " .
                  " AND `hotdate_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) " .
                  " AND `user_to` = " . to_sql($uid) . $is_approved;

        return $where;
    }

    static function getWhereByDayPartyhou($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {
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
        $where .= " `partyhou_datetime` >= '" . $dayTime . "' " .
                  " AND `partyhou_datetime` < DATE_ADD('" . $dayTime . "', INTERVAL 1 DAY) " .
                  " AND `user_to` = " . to_sql($uid) . $is_approved;
        return $where;
    }

    static function getSqlTasksByDay($dayTime, $where = '', $uid = null, $dayTimeConvert = true)
    {
        $where = self::getWhereByDay($dayTime, $where, $uid, $dayTimeConvert);

        $sql = self::$tableEvent .
               " WHERE " . $where .
               " ORDER BY `event_datetime` ASC, `event_id` ASC";

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

        return DB::count(self::$tableEvent, $where);
    }

    static function getListTasksByDay($dayTime = null, $uid = null, $order = '`event_datetime` ASC, `event_id` ASC', $limit = null)
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

        return DB::select(self::$tableEvent, $where, $order, $limit);
    }

    static function getNextTask($lastEventId = null, $limit = 1)
    {
        if ($lastEventId === null) {
            $lastEventId = get_param_int('last_id');
        }

        if (!$lastEventId) {
            return false;
        }

        $where = 'event_id = ' . to_sql($lastEventId);
        $event = DB::one(self::$tableEvent, $where);
        if (!$event) {
            return false;
        }
        $uid = self::getUid();

        $where = 'event_id < ' . to_sql($lastEventId);
        $sqlBase = CEventsTools::events_by_calendar_day(strtotime($event['event_datetime']), $where, $uid);
        $event = CEventsTools::retrieve_from_sql_base($sqlBase, $limit);

        return $event;
    }

    static function getNextTaskMoreCount($eventId)
    {

        $where = 'event_id = ' . to_sql($eventId);
        $event = DB::one(self::$tableEvent, $where);
        if (!$event) {
            return false;
        }

        $uid = self::getUid();

        $where = 'event_id < ' . to_sql($eventId);
        $sqlBase = CEventsTools::events_by_calendar_day(strtotime($event['event_datetime']), $where, $uid);
        $count = CEventsTools::count_from_sql_base($sqlBase);

        return $count;
    }

    static function markSeen($id)
    {
        $guid = guid();
        $where = 'event_id = ' . to_sql($id)
              . ' AND (`user_id` = ' . to_sql($guid) . ' OR `user_to` = ' . to_sql($guid) . ')';
        DB::update(self::$tableEvent, array('done_new' => 0), $where);
    }

    static function done($eventId = null)
    {
        if ($eventId === null) {
            $eventId = get_param_int('event_id');
        }

        $guid = guid();
        $event = self::getTaskById($eventId);
        if($event && ($event['user_id'] == $guid || $event['user_to'] == $guid)){
            $where = 'event_id = ' . to_sql($eventId);

            $done = DB::result('SELECT `done_user` FROM ' . self::$tableEvent . ' WHERE ' . $where);
            $result = $done ? 0 : $guid;
            $new = $result && $done != $guid ? 1 : 0;
            DB::update(self::$tableEvent, array('done_user' => $result, 'done_new' => $new), $where);
            return $result;

        }
        return false;
    }

    static function getEventsOwnerCounts($day_time, $uid)
    {
        $guid = guid();
        $eventsOwner = DB::select(self::$tableEvent, TaskCalendar::getWhereByDay($day_time, '', $uid));
        $checkEventsOwner = array('my' => 0, 'other' => 0);
        foreach ($eventsOwner as $key => $eventsOwnerItem) {
            if ($eventsOwnerItem['user_id'] == $uid) {
                $checkEventsOwner['my']++;
            } else {
                $checkEventsOwner['other']++;
            }
        }

        return $checkEventsOwner;
    }

    static function getHotdatesOwnerCounts($day_time, $uid)
    {
        $guid = guid();
        $hotdatesOwner = DB::select(self::$tableHotdate, TaskCalendarHotdate::getWhereByDay($day_time, '', $uid));
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

    static function getPartyhouzOwnerCounts($day_time, $uid)
    {
        $guid = guid();
        $partyhouzOwner = DB::select(self::$tablePartyhou, TaskCalendarPartyhou::getWhereByDay($day_time, '', $uid));
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

    static function parseEvent(&$html, $event, $n_results)
    {
        global $g;
        global $g_user;
        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('event_social_enabled');
        $guid = guid();

        /* Edge */
        if ($html->varExists('event_id')) {
            $html->setvar('event_id', $event['event_id']);
        }

        if ($html->varExists('event_done')) {
            $html->setvar('event_done', $event['done_user']);
        }

        $userInfo = User::getInfoBasic($event['user_id']);
        if ($html->varExists('event_user_name_js')
                && Common::isOptionActive('calendar_item_show_name_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('event_user_name_js', toJs($userInfo['name']));
        }

        if ($html->varExists('event_user_photo')
                && Common::isOptionActive('calendar_item_show_photo_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('event_user_photo', User::getPhotoDefault($event['user_id'], 'm'));
            $html->setvar('event_user_is_online', intval(User::isOnline($event['user_id'], $userInfo)));
        }

        if ($html->varExists('event_user_url')){
            $html->setvar('event_user_url', User::url($event['user_id'], $userInfo));
        }

        if ($html->varExists('event_user_uid')) {
            $html->setvar('event_user_uid', $event['user_id']);
        }

        if ($html->varExists('event_user_to_uid')) {
            $html->setvar('event_user_to_uid', $event['user_to']);
        }

        if ($html->varExists('event_edit_url')) {
            if($event['event_private'] == '1'){
                $html->setvar('event_edit_url', Common::pageUrl('task_edit', 0, $event['event_id']));
                $html->setvar('event_show_url', Common::pageUrl('task_edit', 0, $event['event_id']));
            } else {
                $html->setvar('event_edit_url', $g['path']['url_main']."events_event_edit.php?event_id=".$event['event_id']);
                $html->setvar('event_show_url', $g['path']['url_main']."events_event_show.php?event_id=".$event['event_id']);
            }
        }
        
        // if ($html->varExists('event_image')) {
            $images = CEventsTools::event_images($event['event_id']);
            $html->setvar('event_image', toJs($images['image_thumbnail']));
        // }

        if ($html->varExists('event_title_js')) {
            $html->setvar('event_title_js', toJs($event['event_title']));
        }

        if($html->varExists('event_category')) {
            $html->setvar('event_category', CEventsTools::getEventCategory($event['category_id']));
        } 

        if ($html->varExists('event_description_js') && Common::isOptionActive('calendar_item_show_description', "{$optionTmplName}_events_settings")) {
            $description = Common::parseLinksTag(to_html($event['event_description']), 'a', '&lt;', 'parseLinksSmile');
            $html->setvar('event_description_js', toJs($description));
        }

        if ($html->blockExists('my_event_class')) {
            $html->subcond($event['user_id'] == $guid, 'my_event_class', 'other_event_class');
        }
        /* Edge */

        if ($n_results == 1) {
            $html->setvar('calendar_day_value', $event['event_id']);
            $html->setvar('event_title', strcut(to_html($event['event_title']), self::$length_title_one));
            $html->parse('set_day');
        } else {
            $html->setvar('event_title', strcut(to_html($event['event_title']), self::$length_title_more));
        }

        $html->setvar('event_id', $event['event_id']);

        $html->setvar('event_title_full', to_html($event['event_title']));
        if(!$event['event_private']) {
            $html->setvar('event_n_guests', $event['event_n_guests']);
            $html->parse('guests',false);
        } else {
            $html->setblockvar('guests',"");
        }

        $isParseTime = true;
        if ($isCalendarSocial) {
            $isParseTime = Common::isOptionActive('calendar_item_show_time', "{$optionTmplName}_events_settings");
        }
        if ($isParseTime) {
            $html->setvar('event_time', to_html(Common::dateFormat($event['event_datetime'],'event_time')));
        }

        if (!$isCalendarSocial) {
            $random = true;
            if ($isCalendarSocial) {
                $random = false;
            }
            $images = CEventsTools::event_images($event['event_id'], $random);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);
        }

        $city_id = $event['city_id'];
        $city_info = DB::row("SELECT  * FROM geo_city WHERE city_id=" . to_sql($city_id));

        $state_info = null;
        if($city_info) {
            $state_id = $city_info['state_id'];
            $state_info = DB::row("SELECT * FROM geo_state WHERE state_id = " . to_sql($state_id));
        }

        $city_title = isset($city_info['city_title']) ? $city_info['city_title'] : '';
        $state_title = isset($state_info['state_title']) ? $state_info['state_title'] : '';

        $event_wall_url = Common::pageUrl('event_wall', $event['event_id']);
        if(!Common::isOptionActive("event_wall_enabled")) {
            $event_wall_url = "";
        }
        
        $event_address = $event['event_address'];
        $event_place = $event['event_place'];
        $event_site = $event['event_site'];
        $event_phone = $event['event_phone'];
        $approved = $event['approved'];

        $sql = "SELECT * FROM events_event_guest WHERE event_id=" . to_sql($event['event_id']) . " AND user_id=" . to_sql(guid()) . " LIMIT 1";
        $guest_user = DB::row($sql);
        $is_own = $event['user_id'] == guid();
        $signin_available = CEventsTools::getSignAvailable($event);
        #additional data
        $event_additional_data = array(
            'state_title' => $state_title,
            'city_title' => $city_title,
            'wall' => $event_wall_url,
            'address' => $event_address,
            'place' => $event_place,
            'site' => $event_site,
            'phone' => $event_phone,
            'approved' => $approved,
            'is_member' => isset($guest_user['user_id']) ? true : false,
            'accepted' => (isset($guest_user['accepted']) && $guest_user['accepted'] == 1) ? true  : false,
            'is_own' => $is_own,
            'is_finished' => CEventsTools::is_event_finished($event),
            'signin_available' => $signin_available,
            'type' => 'event'
        );
        $html->setvar('event_additional_data', json_encode($event_additional_data));
        $html->setvar('ehp_type', 'event');

        $html->parse('event');
    }

    static function parseHotdate(&$html, $hotdate, $n_results)
    {
        global $g;
        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('hotdate_social_enabled');
        $guid = guid();

        /* Edge */
        if ($html->varExists('event_id')) {
            $html->setvar('event_id', $hotdate['hotdate_id']);
        }

        if ($html->varExists('event_done')) {
            $html->setvar('event_done', $hotdate['done_user']);
        }

        $userInfo = User::getInfoBasic($hotdate['user_id']);
        if ($html->varExists('event_user_name_js')
                && Common::isOptionActive('calendar_item_show_name_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('event_user_name_js', toJs($userInfo['name']));
        }

        if ($html->varExists('event_user_photo')
                && Common::isOptionActive('calendar_item_show_photo_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('event_user_photo', User::getPhotoDefault($hotdate['user_id'], 'm'));
            $html->setvar('event_user_is_online', intval(User::isOnline($hotdate['user_id'], $userInfo)));
        }

        if ($html->varExists('event_user_url')){
            $html->setvar('event_user_url', User::url($hotdate['user_id'], $userInfo));
        }

        if ($html->varExists('event_user_uid')) {
            $html->setvar('event_user_uid', $hotdate['user_id']);
        }

        if ($html->varExists('event_user_to_uid')) {
            $html->setvar('event_user_to_uid', $hotdate['user_to']);
        }

        if ($html->varExists('event_edit_url')) {
            $html->setvar('event_edit_url', $g['path']['url_main']."hotdates_hotdate_edit.php?hotdate_id=".$hotdate['hotdate_id']);
            $html->setvar('event_show_url', $g['path']['url_main']."hotdates_hotdate_show.php?hotdate_id=".$hotdate['hotdate_id']);
        }

        // if ($html->varExists('event_image')) {
            $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id']);
            $html->setvar('event_image', toJs($images['image_thumbnail']));
        // }

        if ($html->varExists('event_title_js')) {
            $html->setvar('event_title_js', toJs($hotdate['hotdate_title']));
        }

        if($html->varExists('event_category')) {
            $html->setvar('event_category', ChotdatesTools::getHotdateCategory($hotdate['category_id']));
        }

        if ($html->varExists('event_description_js') && Common::isOptionActive('calendar_item_show_description', "{$optionTmplName}_events_settings")) {
            $description = Common::parseLinksTag(to_html($hotdate['hotdate_description']), 'a', '&lt;', 'parseLinksSmile');
            $html->setvar('event_description_js', toJs($description));
        }

        if ($html->blockExists('my_hotdate_class')) {
            $html->subcond($hotdate['user_id'] == $guid, 'my_hotdate_class', 'other_hotdate_class');
        }
        /* Edge */

        if ($n_results == 1) {
            $html->setvar('calendar_day_value', $hotdate['hotdate_id']);
            $html->setvar('event_title', strcut(to_html($hotdate['hotdate_title']), self::$length_title_one));
            $html->parse('set_day');
        } else {
            $html->setvar('event_title', strcut(to_html($hotdate['hotdate_title']), self::$length_title_more));
        }

        $html->setvar('event_id', $hotdate['hotdate_id']);

        $html->setvar('event_title_full', to_html($hotdate['hotdate_title']));
        if(!$hotdate['hotdate_private']) {
            $html->setvar('event_n_guests', $hotdate['hotdate_n_guests']);
            $html->parse('guests',false);
        } else {
            $html->setblockvar('guests',"");
        }

        $isParseTime = true;
        if ($isCalendarSocial) {
            $isParseTime = Common::isOptionActive('calendar_item_show_time', "{$optionTmplName}_events_settings");
        }
        if ($isParseTime) {
            $html->setvar('event_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdate_time')));
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
            'is_own' => $is_own,
            'type' => 'hotdate'
        );

        $html->setvar('event_additional_data', json_encode($hotdate_additional_data));
        $html->setvar('ehp_type', 'hotdate');

        $html->parse('event');
    }

    static function parsePartyhou(&$html, $partyhou, $n_results)
    {
        global $g;
        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('partyhou_social_enabled');
        $guid = guid();

        /* Edge */
        if ($html->varExists('event_id')) {
            $html->setvar('event_id', $partyhou['partyhou_id']);
        }

        if ($html->varExists('event_done')) {
            $html->setvar('event_done', $partyhou['done_user']);
        }

        $userInfo = User::getInfoBasic($partyhou['user_id']);
        if ($html->varExists('event_user_name_js')
                && Common::isOptionActive('calendar_item_show_name_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('event_user_name_js', toJs($userInfo['name']));
        }


        if ($html->varExists('event_user_photo')
                && Common::isOptionActive('calendar_item_show_photo_user', "{$optionTmplName}_events_settings")) {
            $html->setvar('event_user_photo', User::getPhotoDefault($partyhou['user_id'], 'm'));
            $html->setvar('event_user_is_online', intval(User::isOnline($partyhou['user_id'], $userInfo)));
        }

        if ($html->varExists('event_user_url')){
            $html->setvar('event_user_url', User::url($partyhou['user_id'], $userInfo));
        }

        if ($html->varExists('event_user_uid')) {
            $html->setvar('event_user_uid', $partyhou['user_id']);
        }

        if ($html->varExists('event_user_to_uid')) {
            $html->setvar('event_user_to_uid', $partyhou['user_to']);
        }

        if ($html->varExists('event_edit_url')) {
            $html->setvar('event_edit_url', $g['path']['url_main']."partyhouz_partyhou_edit.php?partyhou_id=".$partyhou['partyhou_id']);
            $html->setvar('event_show_url', $g['path']['url_main']."partyhouz_partyhou_show.php?partyhou_id=".$partyhou['partyhou_id']);
        }

        // if ($html->varExists('event_image')) {
            $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id']);
            $html->setvar('event_image', toJs($images['image_thumbnail']));
        // }

        if ($html->varExists('event_title_js')) {
            $html->setvar('event_title_js', toJs($partyhou['partyhou_title']));
        }

        if($html->varExists('event_category')) {
            $category_txt = CpartyhouzTools::getPartyhouCategory($partyhou['category_id']) . ($partyhou['is_lock'] == '1' ? l('partyhou_locked') : l('partyhou_unlocked'));
            $html->setvar('event_category', $category_txt);
        }

        if ($html->varExists('event_description_js') && Common::isOptionActive('calendar_item_show_description', "{$optionTmplName}_events_settings")) {
            $description = Common::parseLinksTag(to_html($partyhou['partyhou_description']), 'a', '&lt;', 'parseLinksSmile');
            $html->setvar('event_description_js', toJs($description));
        }

        if ($html->blockExists('my_partyhou_class')) {
            $html->subcond($partyhou['user_id'] == $guid, 'my_partyhou_class', 'other_partyhou_class');
        }
        /* Edge */

        if ($n_results == 1) {
            $html->setvar('calendar_day_value', $partyhou['partyhou_id']);
            $html->setvar('event_title', strcut(to_html($partyhou['partyhou_title']), self::$length_title_one));
            $html->parse('set_day');
        } else {
            $html->setvar('event_title', strcut(to_html($partyhou['partyhou_title']), self::$length_title_more));
        }

        $html->setvar('event_id', $partyhou['partyhou_id']);

        $html->setvar('event_title_full', to_html($partyhou['partyhou_title']));
        if(!$partyhou['partyhou_private']) {
            $html->setvar('event_n_guests', $partyhou['partyhou_n_guests']);
            $html->parse('guests',false);
        } else {
            $html->setblockvar('guests',"");
        }

        $isParseTime = true;
        if ($isCalendarSocial) {
            $isParseTime = Common::isOptionActive('calendar_item_show_time', "{$optionTmplName}_events_settings");
        }
        if ($isParseTime) {
            $html->setvar('event_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhou_time')));
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
        $signin_available = CpartyhouzTools::getSignAvailable($partyhou);

        #additional data
        $partyhou_additional_data = array(
            'state_title' => $state_title,
            'city_title' => $city_title,
            'wall' => $partyhou_wall_url,
            'address' => $partyhou_address,
            'place' => $partyhou_place,
            'site' => $partyhou_site,
            'phone' => $partyhou_phone,
            'approved' => $approved,
            'is_member' => isset($guest_user['user_id']) ? true : false,
            'accepted' => (isset($guest_user['accepted']) && $guest_user['accepted'] == 1) ? true  : false,
            'is_finished' => CpartyhouzTools::is_partyhou_finished($partyhou),
            'signin_available' => $signin_available,
            'is_own' => $is_own,
            'type' => 'partyhou'
        );
        $html->setvar('event_additional_data', json_encode($partyhou_additional_data));        
        $html->setvar('ehp_type', 'partyhou');

        /** popcorn modified 2024-05-23 end*/

        $html->parse('event');
    }

    static function getNumberEventLoad()
    {
        $optionTmplName = Common::getTmplName();
        $numberEvent = 2;
        $numberEventTemplate = Common::getOption('number_calendar_item', "{$optionTmplName}_events_settings");
        if ($numberEventTemplate !== null && $numberEventTemplate) {
            $numberEvent = $numberEventTemplate;
        }
        return $numberEvent;
    }

    static function parseMainDay(&$html, $day_time, $uid = null, $can_post=true, $event_id = '')
    {
        global $p;
        $optionTmplName = Common::getTmplName();
        $isCalendarSocial = Common::isOptionActiveTemplate('event_social_enabled');
       
        $eventDayLoadMore = get_param('event_day_load_more');
        $hotdateDayLoadMore = get_param('hotdate_day_load_more');
        $partyhouDayLoadMore = get_param('partyhou_day_load_more');

        $guid = guid();
        $uid = self::getUid($uid);

        $html->clean('day_action');
        $html->clean('event');
        $html->clean('pager');
        $calendar_day = Common::dateFormat($day_time,'calendar_day',false);

        $today = date("Ymd", $day_time) == date("Ymd");

        $html->setvar('calendar_day', $calendar_day);
        $html->setvar('day_time', $day_time);
        $html->setvar('calendar_day_title', l(date("D", $day_time)));

        if ($isCalendarSocial) {
            $vars = array(
                'datetime_day' => date("j", $day_time)
            );
            $html->assign('event', $vars);
            $html->assign('hotdate', $vars);
            $html->assign('partyhou', $vars);
        }

        $html->setvar('calendar_datetime', Common::dateFormat($day_time,'calendar_datetime', false, false, true));
        
        if ($isCalendarSocial) {
            $eventsOwner = self::getEventsOwnerCounts($day_time, $uid);
            $hotdatesOwner = self::getHotdatesOwnerCounts($day_time, $uid);
            $partyhouzOwner = self::getPartyhouzOwnerCounts($day_time, $uid);

            $dayOwners['my'] = $eventsOwner['my'] + $hotdatesOwner['my'] + $partyhouzOwner['my'];
            $dayOwners['other'] = $eventsOwner['other'] + $hotdatesOwner['other'] + $partyhouzOwner['other'];
            $html->setvar('day_owners', json_encode($dayOwners));
        }

        $sql_event_base = CEventsTools::events_by_calendar_day($day_time, '', $uid);
        $n_event_results = CEventsTools::count_from_sql_base($sql_event_base);
        
        $sql_hotdate_base = ChotdatesTools::hotdates_by_calendar_day($day_time, '', $uid);
        $n_hotdate_results = ChotdatesTools::count_from_sql_base($sql_hotdate_base);
        
        $sql_partyhou_base = CpartyhouzTools::partyhouz_by_calendar_day($day_time, '', $uid);
        $n_partyhou_results = CpartyhouzTools::count_from_sql_base($sql_partyhou_base);
        
        $n_results = $n_event_results + $n_hotdate_results + $n_partyhou_results;

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

        $n_results_per_page = self::getNumberEventLoad();

        if($n_results) {
            $page = intval(get_param('event_calendar_day_page', 1));
            $n_pages = ceil($n_results / $n_results_per_page);
            $page = max(1, min($n_pages, $page));
            $html->setvar('page', $page);

            $limit = $n_results_per_page;
            $shift = ($page - 1) * $n_results_per_page;

            if($event_id) {
                $limit = 0;
                $shift = 0;

                $events = CEventsTools::retrieve_from_sql_base($sql_event_base, $limit, $shift);
                $hotdates = CEventsTools::retrieve_from_sql_base($sql_hotdate_base, $limit, $shift);
                $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_partyhou_base, $limit, $shift);
            } else {
                    $ehps = self::getMainEhpsBySqlBase($sql_event_base, $sql_hotdate_base, $sql_partyhou_base, $limit, $shift);

                    $events = $ehps['new_events'];
                    $hotdates = $ehps['new_hotdates'];
                    $partyhouz = $ehps['new_partyhouz'];
            }

            if (Common::isOptionActiveTemplate('event_social_enabled')) {
                $whereNotifId = '';
                $eventIdNotif = get_param_int('neid');
                if (!$eventIdNotif) {
                    $eventIdNotif = get_param_int('task_id');
                }

                $isCheckEvent = false;
                if ($p == 'events_calendar.php' && $eventIdNotif) {
                    $html->setvar('highlight_event_id', $eventIdNotif);
                    $html->parse('highlight_event', false);

                    foreach ($events as $key => $event) {
                        if ($event['event_id'] == $eventIdNotif) {
                            $isCheckEvent = true;
                            break;
                        }
                    }
                    if (!$isCheckEvent) {
                        $limit = 0;
                        $sql_base = CEventsTools::events_by_calendar_day($day_time, '`event_id` <= ' . to_sql($eventIdNotif), $uid);
                        $events = CEventsTools::retrieve_from_sql_base($sql_base);
                        if ($n_results == count($events)) {
                            $n_results = $n_results_per_page;
                        }
                    }
                }

                $whereDone = '`done_new` = 1';
                $whereUpdateDone = TaskCalendar::getWhereByDay($day_time, $whereDone, $uid);
                $sqlLimit = ($limit ? " LIMIT " .  intval($shift) . ", " . intval($limit) : '');
                $sqlSelectDone_1 = "SELECT event_id FROM " . self::$tableEvent
                                 . ' WHERE ' . $whereUpdateDone
                                 . $sqlLimit;

                $sqlSelectDone = 'SELECT event_id FROM (' . $sqlSelectDone_1 . ') tmp';
                $sqlUpdateDone = 'UPDATE ' . self::$tableEvent . ' SET `done_new` = 0
                                    WHERE  event_id IN (' .  $sqlSelectDone . ')';
                DB::execute($sqlUpdateDone);
            }

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
                        $hotdates = ChotdatesTools::retrieve_from_sql_base($sql_hotdate_base);
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
                        $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_partyhou_base);
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

            foreach($events as $event) {
                self::parseEvent($html, $event, $n_results);
            }

            foreach($hotdates as $hotdate) {
                self::parseHotdate($html, $hotdate, $n_results);
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
        }

        if ($n_results > $n_results_per_page) {
            $html->setvar('events_num', $n_results - $n_results_per_page);
            $html->parse('block_events_num', false);
        } else {
            $html->setvar('events_num', 0);
            $html->clean('block_events_num');
        }

        if ($isCalendarSocial && !$eventDayLoadMore) {
            $actionTitle = '';
            if (!$n_results) {
                $actionTitle = toJsL('no_task');
            }
            $html->setvar('event_title_js', $actionTitle);
            $html->setvar('url_create_new_item', Common::pageUrl('task_create', $uid, date('Y-m-d', $day_time)));
            if(!$can_post) {
                $html->setvar('url_create_new_item', '');
            }
            $html->parse('day_action', false);
        }

        $html->parse('day', true);
    }

    static function getMainEhpsBySqlBase($sql_event_base, $sql_hotdate_base, $sql_partyhou_base, $limit, $shift) {
        // Retrieve all events, hotdates, and partyhouz without limits
        $events = CEventsTools::retrieve_from_sql_base($sql_event_base, 0, 0);
        $hotdates = CEventsTools::retrieve_from_sql_base($sql_hotdate_base, 0, 0);
        $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_partyhou_base, 0, 0);

        // Add a type to each array to identify them later
        foreach ($events as &$event) {
            $event['type'] = 'event';
        }
        foreach ($hotdates as &$hotdate) {
            $hotdate['type'] = 'hotdate';
        }
        foreach ($partyhouz as &$partyhou) {
            $partyhou['type'] = 'partyhou';
        }

        // Merge all arrays
        $all_items = array_merge($events, $hotdates, $partyhouz);

        // Sort the merged array by datetime
        usort($all_items, function($a, $b) {
            return strtotime($a['event_datetime'] ?? $a['hotdate_datetime'] ?? $a['partyhou_datetime']) - 
                   strtotime($b['event_datetime'] ?? $b['hotdate_datetime'] ?? $b['partyhou_datetime']);
        });

        // Apply the shift and limit
        $sliced_items = array_slice($all_items, $shift, $limit);

        // Split the sorted array back into the three categories
        $new_events = [];
        $new_hotdates = [];
        $new_partyhouz = [];

        foreach ($sliced_items as $item) {
            if ($item['type'] == 'event') {
                $new_events[] = $item;
            } elseif ($item['type'] == 'hotdate') {
                $new_hotdates[] = $item;
            } elseif ($item['type'] == 'partyhou') {
                $new_partyhouz[] = $item;
            }
        }

        // Return the new arrays
        return [
            'new_events' => $new_events,
            'new_hotdates' => $new_hotdates,
            'new_partyhouz' => $new_partyhouz
        ];
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
            $tasksList = TaskCalendarMain::getListTasksByDay(null, null, '`event_datetime` DESC, `event_id` DESC');
            $countList = count($tasksList);

            $tasksListTitle = array();
            foreach ($tasksList as $task) {
                $tasksListTitle[] = $task['event_title'];
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