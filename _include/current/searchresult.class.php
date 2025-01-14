<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
Class SearchResult{
    static function search() {
        global $g;
        global $g_user;
        global $p;

        $optionTmplName = Common::getTmplName();
        $optionTmplName = "oryx";
        $show = get_param('show');
        $isCustomShowList = in_array($show, array('wall_liked', 'wall_shared', 'wall_liked_comment', 'photo_liked', 'photo_liked_comment', 'video_liked', 'video_liked_comment'));

        if ($optionTmplName == 'edge' && $isCustomShowList) {
            if (isset($l[$p]["page_title_{$show}"])) {//EDGE
                $l[$p]['page_title'] = $l[$p]["page_title_{$show}"];
            }
            Common::$pagerUrl = Common::pageUrl($show);
        }

        $addWhereLocation = true;
        $display = get_param('display');

        if (!guid() && $optionTmplName == 'edge') {
            if (!$display && Common::isOptionActive('list_people_hide_from_guests', "{$optionTmplName}_general_settings")) {
                Common::toLoginPage();
            } elseif ($display == 'profile') {
                
                Common::toLoginPage();
            }
        }

        if (!guid() && $optionTmplName == 'oryx') {
            if($display){
                Common::toLoginPage();
            }
        }

        if (!get_param('join_search_page')){
            Common::checkAreaLogin();
        }

        if (in_array($display, array('encounters','rate_people'))) {
            if (guid()) {
                if ($display == 'encounters') {
                    $isShow = true;
                    if ($optionTmplName == 'impact') {
                        $isShow = CustomPage::checkItemShow('column_narrow_hot_or_not');
                    } elseif ($optionTmplName == 'urban') {
                        $submenuItem = Menu::getIndexItemSubmenu('header_menu', 'header_menu_encounters_item');
                        $isShow = $submenuItem !== false;
                    }
                    if (!$isShow) {
                        Common::toHomePage();
                    }
                } elseif ($display == 'rate_people' && !Common::isOptionActive('photo_rating_enabled')) {
                    Common::toHomePage();
                }
            } else {
                Common::toLoginPage();
            }
        }

        CustomPage::setSelectedMenuItemInSearchResults($display);

        $params = get_params_string();

        $userSearchFiltersUpdate = false;
        $optionSet = Common::getOption('set', 'template_options');
        $optionSet = "old";
        $isFreeSite = Common::isOptionActive('free_site');
        if (guid()){
            /* Redirect blocked user */
            User::accessCheckToProfile();
            /* Redirect blocked user */

            /* Encounters */
            $isAjaxRequest = get_param('ajax');
            if ($display == 'encounters' && $isAjaxRequest) {
                Encounters::undoLike();
            }
            /* Encounters */
            $name = trim(get_param('search_name', ''));
            if ($name == '') $name = l('my_search');
            if (get_param('save_search', 0) == 1)
            {
                $name = to_sql($name, 'Text');
                $id = DB::result("SELECT id FROM search_save WHERE user_id=" . to_sql(guid(), 'Number') . " AND name=" . $name . "");

                if ($id == 0)
                {
                    $num_save = DB::result("SELECT COUNT(id) FROM search_save WHERE user_id=" . to_sql(guid(), 'Number') . "");
                    $query = to_sql($params, "Text");
                    DB::execute("INSERT INTO search_save (name, user_id, query) VALUES (" . $name . ", " . to_sql(guid(), 'Number') . ", " . $query . ")");
                }
                else
                {
                    $query = to_sql($params, "Text");
                    DB::execute("UPDATE search_save SET query=" . $query . " WHERE id=" . $id . "");
                }
            }

        }
        if ($optionSet == 'urban' && ($display == '' || $display == 'encounters' || $display == 'rate_people')) {
            if (guid()) {
                global $g_user;
                $userinfo = User::getInfoFull($g_user['user_id'], 0, true);

                $g_user['user_search_filters_mobile'] = User::getParamsFilter('user_search_filters_mobile', $userinfo['user_search_filters_mobile']);
                $g_user['state_filter_search'] = $userinfo['state_filter_search'];
                if(!get_param('set_filter') || get_param('set_filter_interest')) {
                    User::setGetParamsFilter('user_search_filters', $userinfo);
            
                    $_GET['with_photo'] = get_param('with_photo', 1);
                    if (get_param('set_filter_interest')) {
                        $userSearchFiltersUpdate = true;
                    }
                } else {
                    $g_user['user_search_filters'] = User::getParamsFilter('user_search_filters', $userinfo['user_search_filters']);
                    User::updateParamsFilterUser();
                
                }
                $userSearchFiltersUpdate = true;
            } else {
                $isAjaxRequest = get_param('ajax');
                $setDefaultIamHereTo = false;
                $paramIamHereTo = get_param('i_am_here_to');
                if (!get_param('set_filter')) {
                    $setDefaultIamHereTo = true;
                    $userinfo['state_filter_search'] = 1;
                    $userinfo['user_search_filters'] = User::setDefaultParamsFilterUser(0, false);
                    User::setGetParamsFilter('user_search_filters', $userinfo);
                } elseif (!$paramIamHereTo && !$isAjaxRequest) {
                    $setDefaultIamHereTo = true;
                }
                if ($setDefaultIamHereTo && UserFields::isActive('i_am_here_to') && $optionTmplName != 'edge') {
                    $sql = 'SELECT `id` FROM `const_i_am_here_to`';
                    $iAmHereTo = DB::result($sql);
                    if ($iAmHereTo) {
                        $_GET['i_am_here_to'] = $iAmHereTo;
                    }
                }
            }
        }

        $where = "";
        $whereCore = "1=1 ";
        $userSearchFilters = array();
        //eric-cuigao-20201121-start
        if(isset($g_user['orientation']) && $g_user['orientation']==5){
            $where .= " and  u.set_my_presence_couples=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==1){
            $where .= " and u.set_my_presence_males=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==2){
            $where .= " and u.set_my_presence_females=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==6){
            $where .= " and u.set_my_presence_transgender=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==7){
            $where .= " and u.set_my_presence_nonbinary=2";
        }

        //eric-cuigao-20201121-end
        $user['i_am_here_to'] = (int) get_param('i_am_here_to', '');
        if ($user['i_am_here_to'])
        {
            //$where .= " AND u.i_am_here_to = " . to_sql($user['i_am_here_to']);
        }

        $user["horoscope"] = (int) get_checks_param("p_star_sign");
        if ($user["horoscope"])
        {
            $where .= " AND " . $user["horoscope"] . " & (1 << (cast(u.horoscope AS signed) - 1))";
        }

        $pOrientationSearch = intval(get_param('p_orientation_search'));
        if ($pOrientationSearch) {
            $user["p_orientation"] = $pOrientationSearch;
        } else {
            $user["p_orientation"] = (int) get_checks_param("p_orientation");
        }

        if ($user["p_orientation"] > 0)
        {
            $where .= " AND " . $user["p_orientation"] . " & (1 << (cast(u.orientation AS signed) - 1))";
        }
        $user["p_relation"] = (int) get_checks_param("p_relation");
        if ($user["p_relation"] != "0")
        {
            $where .= " AND " . $user["p_relation"] . " & (1 << (cast(u.relation AS signed) - 1))";
        }

        $user['name'] = get_param("name_key", "");
        if ($user['name'] != '')
        {
            $where .= " AND u.name LIKE '%" . to_sql($user['name'], "Plain") . "%'";
        }

        $user['name'] = get_param("name", "");
        if ($user['name'] != "")
        {
            $where .= " AND u.name=" . to_sql($user['name'], 'Text') . "";
        }

        $user['name_seo'] = get_param('name_seo');
        if ($user['name_seo'] != '')
        {
            $where .= ' AND u.name_seo = ' . to_sql($user['name_seo'], 'Text');
        }

        $user['p_age_from'] = (int) get_param("p_age_from", 0);
        $user['p_age_to'] = (int) get_param("p_age_to", 0);
        $userAgeToSrc = $user['p_age_to'];
        if ($user['p_age_from'] == $g['options']['users_age']) $user['p_age_from'] = 0;
        if ($user['p_age_to'] == $g['options']['users_age_max']) $user['p_age_to'] = 10000;
        if ($user['p_age_from'] != 0)
        {
            $where .= " AND u.birth <= " . to_sql(Common::ageToDate($user['p_age_from']));
        }

        if ($userAgeToSrc && $userAgeToSrc != $g['options']['users_age_max'])
        {
            $where .= " AND u.birth >= " . to_sql(Common::ageToDate($userAgeToSrc, true));
        }

        foreach ($g['user_var'] as $k => $v){
            $user[$k] = intval(get_param($k, ''));
        }

        $typeFields = array('from', 'checks', 'checkbox');
        $numCheckbox = 0;
        $from_add = '';
        $from_group = '';
        foreach ($g['user_var'] as $k => $v)
        {
            if (in_array($v['type'], $typeFields) && $v['status'] == 'active')
            {
                if ($v['type'] == 'from'){

                    $key = $k;
                    if (substr($key, 0, 2) == "p_") $key = substr($key, 2);
                    if (substr($key, -5) == "_from") $key = substr($key, 0, strlen($key) - 5);

                    $valFieldFrom = $user[$k];

                    $fieldTo = substr($k, 0, strlen($k) - 4) . 'to';
                    $valFieldTo = intval($user[$fieldTo]);


                    if ($valFieldTo) {
                        $where .= ' AND i.' . $key . '<=' . $valFieldTo;

                        if(!$valFieldFrom) {
                            $valFieldFrom = 1;
                        }
                    }

                    if($valFieldFrom) {
                        $where .= " AND i." . $key . ">=" . intval($valFieldFrom);
                    }

                    if($valFieldFrom || $valFieldTo) {
                        // save real value for defaul select value
                        $valFieldFrom = $user[$k];

                        $keyFilter = $k;

                        if(!$valFieldFrom) {
                            $keyFilter = $fieldTo;
                        }

                        $userSearchFilters[$keyFilter] = array(
                            'field' => $key,
                            'values' => array($k => $valFieldFrom, $fieldTo => $valFieldTo),
                        );

                        //$userSearchFilters[$k] = array($user[$k], $valFieldsTo);
                    }
                } elseif ($v['type'] == 'checks' && $user[$k] != 0) {
                        // $user[$k] = intval(get_checks_param($k));
                        if ($user[$k] != 0)
                        {
                            $key = $k;
                            if (substr($key, 0, 2) == "p_") $key = substr($key, 2);

                            $userSearchFilters[$k] = array(
                                'field' => $key,
                                'value' => get_param_array($k),
                            );


                            if ($k != 'p_star_sign') {
                                //$where .= " AND " . to_sql($user[$k], 'Number') . " & (1 << (cast(i." . $key . " AS signed) - 1))"; //nnsscc-diamond-20200504
                            }
                        }
                } elseif ($v['type'] == 'checkbox' && $user[$k] != 0) {

                        $params = get_param_array($k);
                        foreach ($params as $key => $value) {
                            if ($value == 0) {
                                unset($params[$key]);
                            }
                        }
                        if (!empty($params)){
                            $userSearchFilters[$k] = array(
                                'field' => $k,
                                'value' => $params,
                            );
                            $nameTable = 'uck' . $numCheckbox;
                            $from_add .= " LEFT JOIN users_checkbox AS " . $nameTable . " ON " . $nameTable . ".user_id = u.user_id AND " . $nameTable . ".field = " . to_sql($v['id'], 'Number') . " AND "  . $nameTable . ".value IN (" . implode($params, ',') . ")";
                            $where .=  " AND " . $nameTable . ".user_id IS NOT NULL";
                            $numCheckbox++;
                        }
                }
            }
        }

        if ($numCheckbox) {
            $from_group = 'u.user_id';
        }

        $paramUid = get_param('uid');
        $onlyPhotos = Common::isOptionActive('no_profiles_without_photos_search');
        $withPhoto = intval(get_param('with_photo'));

        if ($display == 'encounters' || $display == 'rate_people') {
            $onlyPhotos = true;
        } 
        if ((get_param("photo", "") == "1" || $onlyPhotos) && (!$user['name_seo'] && !$paramUid)){
            $where .= " AND u.is_photo='Y'";
        }

        if (get_param("couple", "") == "1"){
            $where .= " AND u.couple='Y'";
        }

        $status = get_param('status');
        if ($status == "online"){
            $time = date('Y-m-d H:i:s', time() - $g['options']['online_time'] * 60);
            $where .= ' AND (u.last_visit> ' . to_sql($time, 'Text') . ' OR u.use_as_online=1)';
        }
        elseif ($status == "new"){
            $where .= ' AND u.register > ' . to_sql(date('Y-m-d H:00:00', (time() - $g['options']['new_time'] * 3600 * 24)), 'Text');
        }
        elseif ($status == "birthday"){
            $where .= " AND (DAYOFMONTH(u.birth)=DAYOFMONTH('" . date('Y-m-d H:i:s') . "') AND MONTH(u.birth)=MONTH('" . date('Y-m-d H:i:s') . "'))";
        }


        $day = to_sql(get_param('day', 0), 'Number');
        $month = to_sql(get_param('month', 0), 'Number');
        $year = to_sql(get_param('year', 0), 'Number');

        if($day && $month && $year) {
            $month = sprintf('%02d', $month);
            $day = sprintf('%02d', $day);
            $where .= " AND u.register >= '$year-$month-$day 00:00:00' ";
        }

        $day = to_sql(get_param('day_to', 0), 'Number');
        $month = to_sql(get_param('month_to', 0), 'Number');
        $year = to_sql(get_param('year_to', 0), 'Number');

        if($day && $month && $year) {
            $month = sprintf('%02d', $month);
            $day = sprintf('%02d', $day);
            $where .= " AND u.register <= '$year-$month-$day 23:59:59' ";
        }

        // IF active distance search, then exclude others
        // DISTANCE
        $distance = (int) get_param('radius', 0);
        $user['city'] = (int) get_param("city", 0);
        $user['state'] = (int) get_param("state", 0);
        $user['country'] = (int) get_param("country", 0);
        $peopleNearby = get_param_int('people_nearby');
        $maxDistance = Common::getOption('max_search_distance');
        $map_lat = get_param('lat', '');
        $map_log = get_param('log', '');
        //nnsscc-diamond-20200330-start
        $keyword = trim(get_param("keyword", ""));
        if(trim($keyword)!=""){ 
            $user['city'] = 0;
            $user['state'] = 0;
            $user['country'] = 0;
        }
        //nnsscc-diamond-20200330-end
        $isFilterSocial = Common::isOptionActiveTemplate('list_users_filter_social');
        $isActiveLocation = Common::isOptionActive('location_enabled', "{$optionTmplName}_join_page_settings");
        $notActiveLocationFilterSocial = $isFilterSocial && !$isActiveLocation;
        if ($notActiveLocationFilterSocial) {
            $peopleNearby = 0;
        }
        if ($peopleNearby) {
            $userLocation = array('country' => 0, 'state' => 0, 'city' => 0);
            $guid = guid();
            if ($guid) {
                $gUserCountryId = guser('geo_position_country_id');
                $gUserCityId = guser('geo_position_city_id');
            } else {
                $geoCityInfo = IP::geoInfoCity();
                $gUserCountryId = $geoCityInfo['country_id'];
                $gUserCityId = $geoCityInfo['city_id'];
            }
            if($distance == 0){//In the whole city
                $whereLocation = " AND u.geo_position_city_id = " . to_sql($gUserCityId);
            } elseif (Common::getOption('max_filter_distance') == 'max_search_country' && $distance > $maxDistance) {//In the whole country
                $whereLocation = " AND u.geo_position_country_id = " . to_sql($gUserCountryId);
            } else {
                $whereLocation = getInRadiusWhere($distance);
            }
        } else {
            if($distance > $maxDistance && $user['city']>0) {
                $user['city'] = 0;
                $user['state'] = 0;
            }

            $allCountriesSearch = get_param_int('all_countries');
            if ($notActiveLocationFilterSocial) {
                $allCountriesSearch = 1;
            }
            if ($allCountriesSearch){
                $user['city'] = 0;
                $user['state'] = 0;
                $user['country'] = 0;
            }
            // search only by distance from selected city
            $whereLocation = '';

            if($distance && $user['city'])
            {
                // find MAX geo values
                $whereLocation = inradius($user['city'], $distance);
                $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = u.city_id";
            } else if($map_lat && $map_log && $distance) {
                $whereLocation = inradiusLatLong($map_lat, $map_log, $distance);
                $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = u.city_id";

            } else {
                $whereLocation = Common::getWhereSearchLocation($user);
            }
        }

        $from_add .= " LEFT JOIN userinfo AS i ON u.user_id=i.user_id ";

        $search_header = get_param("search_header", "");
        $where_search = '';
        //nnsscc-diamond-20200304-start
        $from_add .= " JOIN userinfo ui ON ui.user_id = u.user_id ";
        $sexuality = get_param("p_sexuality","");
        if(is_array($sexuality)){
            foreach ($sexuality as $k => $v){
                $where .= " and ui.income ='" . $v ."'";
            }
        }
        //nnsscc-diamond-20200304-end
        if ($keyword != "")
        {
            if ($search_header == 1) {
                $where_search = ' OR u.mail =' . to_sql($keyword, 'Text');
            }
            
            $keyword_search_sql = "";
            $keyword = to_sql(strip_tags($keyword), "Plain");
            if($search_header != 1) {
                foreach ($g['user_var'] as $k => $v) if ($v['type'] == "text" or $v['type'] == "textarea") $keyword_search_sql .= " OR i." . $k . " LIKE '%" . $keyword . "%'";
            }
            $where .= " AND (u.name LIKE '%" . $keyword . "%'" . $keyword_search_sql . $where_search . ") ";

            if($search_header != 1) {
                //startnnsscc-diamond-20200221
                $where .= " OR u.geo_position_city LIKE '%" . $keyword . "%'";
                $where .= " OR u.geo_position_state LIKE '%" . $keyword . "%'";
                $where .= " OR u.geo_position_country LIKE '%" . $keyword . "%'";
                //startnnsscc-diamond-20200328-start
                $from_add .= " LEFT JOIN var_ethnicity  v_ethnicity ON ui.ethnicity   = v_ethnicity.id "; 
                $where .= " or v_ethnicity.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_age_preference  v_age_preference ON ui.age_preference   = v_age_preference.id "; 
                $where .= " or v_age_preference.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_appearance  v_appearance ON ui.appearance   = v_appearance.id "; 
                $where .= " or v_appearance.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_body  v_body ON ui.body   = v_body.id "; 
                $where .= " or v_body.title like '%" . $keyword ."%'";  

                $from_add .= " LEFT JOIN var_can_you_host  v_can_you_host ON ui.can_you_host   = v_can_you_host.id "; 
                $where .= " or v_can_you_host.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_drinking  v_drinking ON ui.drinking   = v_drinking.id "; 
                $where .= " or v_drinking.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_education  v_education ON ui.education   = v_education.id "; 
                $where .= " or v_education.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_eye  v_eye ON ui.eye   = v_eye.id "; 
                $where .= " or v_eye.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_first_date  v_first_date ON ui.first_date   = v_first_date.id "; 
                $where .= " or v_first_date.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_hair  v_hair ON ui.hair   = v_hair.id "; 
                $where .= " or v_hair.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_height  v_height ON ui.height   = v_height.id "; 
                $where .= " or v_height.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_humor  v_humor ON ui.humor   = v_humor.id "; 
                $where .= " or v_humor.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_income  vi ON ui.income   = vi.id ";
                $where .= " or vi.title like '%" . $keyword ."%'";

                $from_add .= " LEFT JOIN var_smoking  v_smoking ON ui.smoking   = v_smoking.id "; 
                $where .= " or v_smoking.title like '%" . $keyword ."%'";

                $from_add .=" LEFT JOIN var_status vs ON ui.status   = vs.id ";
                $where .= " or vs.title LIKE '%" . $keyword ."%'";

                $from_add .=" LEFT JOIN const_orientation      co ON u.orientation   = co.id ";
                $where .= " or co.title LIKE '%" . $keyword ."%'";
                //end
            }
        }

        $ht = User::isHiddenSql();

        if(get_param('moderator_view_profile')) {
            $ht = ' 1 ';
        }

        $wallLikesItemId = get_param_int('wall_item_id');
        if($wallLikesItemId) {
            $whereSql = ' AND wl.wall_item_id = ' . to_sql($wallLikesItemId, 'Number');
            if ($optionTmplName == 'edge') {
                $where = $whereSql;
            } else {
                $where .= $whereSql;
            }
            $from_add .= ' LEFT JOIN wall_likes AS wl ON wl.user_id = u.user_id ';
            // show hidden profiles in likes
            $ht = ' 1 ';
        } elseif ($show == 'photo_liked') {
            $photoId = get_param_int('photo_id');
            $where = ' AND wl.photo_id = ' . to_sql($photoId) . ' AND wl.like = 1';
            $from_add .= ' LEFT JOIN photo_likes AS wl ON wl.user_id = u.user_id ';
            $ht = ' 1 ';
        } elseif ($show == 'video_liked') {
            $videoId = get_param_int('video_id');
            $where = ' AND wl.video_id = ' . to_sql($videoId) . ' AND wl.like = 1';
            $from_add .= ' LEFT JOIN vids_likes AS wl ON wl.user_id = u.user_id ';
            $ht = ' 1 ';
        }

        if ($show == 'wall_shared') {
            $wallSharedItemId = get_param_int('wall_shared_item_id');
            $whereSql = " AND wl.section = 'share' AND item_id = " . to_sql($wallSharedItemId, 'Number');
            if ($optionTmplName == 'edge') {
                $where = $whereSql;
            } else {
                $where .= $whereSql;
            }
            $from_add .= ' LEFT JOIN wall AS wl ON wl.user_id = u.user_id ';
            $ht = ' 1 ';
        } elseif($show == 'wall_liked_comment' || $show == 'photo_liked_comment' || $show == 'video_liked_comment') {
            $table = 'photo';
            if ($show == 'video_liked_comment') {
                $table = 'vids';
            } elseif ($show == 'wall_liked_comment') {
                $table = 'wall';
            }
            $likesCommentId = get_param_int('comment_id');
            $whereSql = ' AND lc.cid = ' . to_sql($likesCommentId, 'Number');
            if ($optionTmplName == 'edge') {
                $where = $whereSql;
            } else {
                $where .= $whereSql;
            }
            $from_add .= " LEFT JOIN {$table}_comments_likes AS lc ON lc.user_id = u.user_id ";
            $ht = ' 1 ';
        }

        $whereCore .= ' AND ' . $ht;

        $uidsExclude = get_param('uids_exclude', '');
        if($uidsExclude) {
            $where .= ' AND u.user_id NOT IN (' . to_sql($uidsExclude, 'Plain') . ') ';
        }

        $paramUid=false;
        if (get_param('uid') != '') {
                $where = "u.user_id=" . intval(get_param('uid')) . "";

                $whereCore = "1=1 ";
                $userSearchFilters = array();
                //eric-cuigao-20201121-start
                if(isset($g_user['orientation']) && $g_user['orientation']==5){
                    $where .= " and  u.set_my_presence_couples=2";
                }else if(isset($g_user['orientation']) && $g_user['orientation']==1){
                    $where .= " and u.set_my_presence_males=2";
                }else if(isset($g_user['orientation']) && $g_user['orientation']==2){
                    $where .= " and u.set_my_presence_females=2";
                }else if(isset($g_user['orientation']) && $g_user['orientation']==6){
                    $where .= " and u.set_my_presence_transgender=2";
                }else if(isset($g_user['orientation']) && $g_user['orientation']==7){
                    $where .= " and u.set_my_presence_nonbinary=2";
                }

                $where .= ' AND ' . $ht;

            $paramUid=true;
        } else {

            $interest = get_param('interest');
            if($interest) {
                $from_add .= ' JOIN user_interests AS uint ON (u.user_id = uint.user_id AND uint.interest = ' . to_sql($interest) .') ';
            }

            $where = $ht . " " . $where . " ";
        }

        $order = '';

        $orderNear = Common::getSearchOrderNear();

        if ($withPhoto && $display != 'encounters' && $display != 'rate_people') {
            $order = "is_photo DESC, ";
        }

        if($user['i_am_here_to']) {
            Common::prepareSearchWhereOrderByIAmHereTo($where, $order, $user['i_am_here_to']);
        }

        if ($optionSet == 'urban') {
            if ($display == '') {
                $order .= ($isFreeSite) ? $orderNear . ' user_id DESC' : 'date_search DESC, ' . $orderNear . ' user_id DESC';
            }
        } else {
            $order .= $orderNear . ' user_id DESC';
        }

        if ($isCustomShowList) {
            $order = ' date DESC';
        }

        if (Common::isOptionActive('do_not_show_me_in_search', 'template_options')
            && !$isCustomShowList
            && $g_user['user_id'] != User::getRequestUserId()) {
            $where .=" AND u.user_id != " . to_sql($g_user['user_id'], 'Number');
            $whereCore.=" AND u.user_id != " . to_sql($g_user['user_id'], 'Number');
        }

        if (get_param('join_search_page')){
            $where .=" AND u.is_photo_public = 'Y' ";
        }
        if ($g_user['user_id']) {
            $guidSql = to_sql($g_user['user_id'], 'Number');
            if ($optionSet == 'urban' && Common::isOptionActive('contact_blocking') && !$isCustomShowList) {
                //$order = ' date_search DESC, near DESC,  user_id DESC';
                $isAllowYouToViewBlockedProfile = Common::isOptionActive('allow_you_to_view_blocked_profile', 'template_options');
                if (!$isAllowYouToViewBlockedProfile ||
                    ($isAllowYouToViewBlockedProfile && !$user['name_seo'] && !get_param('uid'))) {
                    $from_add .= " LEFT JOIN user_block_list AS ubl1 ON (ubl1.user_to = u.user_id AND ubl1.user_from = " . $guidSql . ")
                                LEFT JOIN user_block_list AS ubl2 ON (ubl2.user_from = u.user_id AND ubl2.user_to = " . $guidSql . ")";
                    $where .=' AND ubl1.id IS NULL AND ubl2.id IS NULL';
                    $whereCore.=' AND ubl1.id IS NULL AND ubl2.id IS NULL';
                }
            }
            if ($display == 'encounters') {


                $where .=" AND u.is_photo_public = 'Y'
                        AND u.user_id != " . $guidSql;
                //if ($uidEnc == '') {
                if (!$paramUid) {
                $where .=' AND enc1.user_from IS NULL AND enc2.user_from IS NULL ';

                        $from_add .= " LEFT JOIN encounters AS enc1 ON (u.user_id = enc1.user_to AND enc1.user_from = " . $guidSql . ")
                                    LEFT JOIN encounters AS enc2 ON (u.user_id = enc2.user_from AND enc2.user_to = " . $guidSql . "
                                                            AND ((enc2.from_reply != 'N' AND enc2.to_reply != 'P')OR(enc2.from_reply = 'N')))";

                }

                if ($isFreeSite) {
                    $order .= 'near DESC,  user_id DESC';
                } else {
                    $orderDate = $optionTmplName == 'impact' ? 'date_search' : 'date_encounters';
                    $order .= $orderDate . ' DESC, near DESC,  user_id DESC';
                }

                if(!$paramUid && Users_List::isBigBase()) {
                    Encounters::prepareFastSelect($where, $whereLocation, $from_add, $order);
                    $addWhereLocation = false;
                    $order = '';
                }

            } elseif ($display == 'rate_people') {


                $where .=" AND u.is_photo_public = 'Y'
                        AND u.user_id != " . $guidSql .
                        ' AND upr.photo_id IS NULL ';
                $from_add .= ' LEFT JOIN photo AS up ON u.user_id = up.user_id AND up.private = "N" ' . CProfilePhoto::wherePhotoIsVisible('up') . '
                            LEFT JOIN photo_rate AS upr ON up.photo_id = upr.photo_id AND upr.user_id = ' . $guidSql;
                $order .= 'votes ASC, RAND()';
                $from_group = 'u.user_id';

            /*** ???????? ??????? ??? ???????? ???-?? ??????? *************************************/
                if(!$paramUid){
                    if(Users_List::isBigBase()){
            
                        $where = User::getRatePhotoWhereOnBigBase($where, $from_add, $user, $whereLocation, $order);
                        $addWhereLocation = false;

                        $order = '';
                    }
                }
            /****************************************************/
            }
        }

        /* Reset filter country */
        $isResetFilter = false;

        if (($display == 'encounters' || $display == 'rate_people') && $paramUid) {
            $addWhereLocation = false;
        } elseif ($isCustomShowList) {
            $addWhereLocation = false;
        }
        if ($addWhereLocation) {
            $where .= $whereLocation;
        }
        /* Reset filter country */

        if ($g_user['user_id']) {
            $interest = get_param('interest');
            if ($interest) {
                $userSearchFilters['interest'] = array(
                    'field' => 'interest',
                    'value' => $interest,
                );
            }

            $userSearchFiltersMobile = array();
            $locations = array('country', 'state', 'city');
            foreach($locations as $location) {
                $userSearchFilters[$location] = array(
                    'field' => $location,
                    'value' => get_param($location),
                );
                $userSearchFiltersMobile[$location] = $userSearchFilters[$location];
            }

            $userSearchFilters['radius'] = array(
                'field' => 'radius',
                'value' => get_param('radius'),
            );
            $userSearchFiltersMobile['radius'] = $userSearchFilters['radius'];

            $userSearchFilters['people_nearby'] = array(
                'field' => 'people_nearby',
                'value' => get_param_int('people_nearby'),
            );
            $userSearchFiltersMobile['people_nearby'] = $userSearchFilters['people_nearby'];

            $userSearchFilters['all_countries'] = array(
                'field' => 'all_countries',
                'value' => get_param('all_countries',0),
            );

            $userSearchFilters['status'] = array(
                'field' => 'status',
                'value' => $status,
            );
            $userSearchFiltersMobile['status'] = $userSearchFilters['status'];

            $userSearchFilters['with_photo'] = array(
                'field' => 'with_photo',
                'value' => $withPhoto,
            );
            $userSearchFiltersMobile['with_photo'] = $userSearchFilters['with_photo'];

            if(($userSearchFiltersUpdate || $isResetFilter) && $userSearchFilters) {
                $userSearchFiltersOrdered = array();
                // add filters order as param
                // TODO: move it to JS
                foreach($_GET as $key => $value) {
                    if(isset($userSearchFilters[$key])) {
                        $userSearchFiltersOrdered[$key] = $userSearchFilters[$key];
                    }
                }

                $filter = json_encode($userSearchFiltersOrdered);
                //echo '<br><br>FILTER>' . $filter . '<br><br>';
                // update if new only
                if(guser('user_search_filters') != $filter) {
                    User::updateParamsFilter('user_search_filters', $filter);
                    $userSearchFiltersMobile = json_encode($userSearchFiltersMobile);
                    if (guser('user_search_filters_mobile') != $userSearchFiltersMobile) {
                        User::updateParamsFilter('user_search_filters_mobile', $userSearchFiltersMobile);
                    }
                    /*$data = array(
                        'user_search_filters' => $filter
                    );
                    $userSearchFiltersMobile = json_encode($userSearchFiltersMobile);
                    if (guser('user_search_filters_mobile') != $userSearchFiltersMobile) {
                        $data['user_search_filters_mobile'] = $userSearchFiltersMobile;
                    }

                    User::update($data, guid(), 'userinfo');*/
                }

                /*if(get_cookie('settings_filter_search') != $filter) {
                    set_cookie('settings_filter_search', $filter);
                }
                #echo get_cookie('settings_filter_search');*/
            }
        }

        foreach ($userSearchFilters as $key => $value) {
            if (strpos($key, "p_") === 0) {

                if($value['value']) {

                    $where  .= " AND ui." . $value['field'] . " IN (". implode(", ", $value['value']) . ") ";
                }

            }
        }

        // var_dump($g_user['relation']); die();

        $p_looking_fors = get_param('p_looking_for', '');
        $whereLookingFor = '';
        if($p_looking_fors) {
            $i = 0;

            foreach ($p_looking_fors as $key => $value) {
                $x = ' AND SUBSTRING_INDEX(SUBSTRING_INDEX(relation, ", ", ' . $value . '), ":", -1) IN ("4", "5") ';
                $whereLookingFor .= $x;
            }

            $where .= $whereLookingFor;
        }

        $global_username_search=get_param('global_search_by_username');
        $redirectIfSingle=false;
        if(trim($global_username_search)!=''){
            $where=$whereCore.' AND u.name LIKE "%'.to_sql($global_username_search,'Plain').'%"';
            $redirectIfSingle=true;
        }

        if ($optionTmplName == 'edge') {
            if ($show == 'wall_shared' || $show == 'wall_liked') {
                $order = 'wl.id DESC, is_photo DESC,  near DESC,  user_id DESC';
            } elseif ($show == 'wall_liked_comment' || $show == 'photo_liked_comment' || $show == 'video_liked_comment') {
                $order = 'lc.id DESC, is_photo DESC,  near DESC,  user_id DESC';
            }
        }

        // Return user_data as JSON

        if(get_param('map', '')) {
            $responseData = array (
                'from_add' => $from_add,
                'where' => $where
            );

            $from_add .= " LEFT JOIN const_orientation AS co ON u.orientation = co.id ";

            //who's allowed to see me on map
            if(isset($g_user['orientation']) && $g_user['orientation']==5){
                $where .= " and  u.set_my_map_couples=1 ";
            } else if(isset($g_user['orientation']) && $g_user['orientation']==1){
                $where .= " and u.set_my_map_males=1 ";
            } else if(isset($g_user['orientation']) && $g_user['orientation']==2){
                $where .= " and u.set_my_map_females=1 ";
            } else if(isset($g_user['orientation']) && $g_user['orientation']==6){
                $where .= " and u.set_my_map_transgender=1 ";
            } else if(isset($g_user['orientation']) && $g_user['orientation']==7){
                $where .= " and u.set_my_map_nonbinary=1 ";
            }

            //popcorn modified 2024-09-20
            
            //can see only friends 
            $where .= " AND !(u.set_show_only_friends_map = 1 AND !(frx.friend_id = " . to_sql($g_user['user_id'], "Text") . " OR frx1.user_id = " . to_sql($g_user['user_id'], "Text") . ")) ";
            $from_add .= " LEFT JOIN friends_requests AS frx ON frx.user_id = u.user_id 
                        LEFT JOIN friends_requests AS frx1 ON u.user_id = frx1.friend_id ";
            
            $where .= " AND ( 1=1 ";
            if(!(isset($g_user['set_my_map_couples']) && $g_user['set_my_map_couples']==1)) {
                $where .= " AND u.orientation != 5";
            } 

            if(!(isset($g_user['set_my_map_males']) && $g_user['set_my_map_males']==1)) {
                $where .= " AND u.orientation != 1";
            } 

            if(!(isset($g_user['set_my_map_females']) && $g_user['set_my_map_females']==1)) {
                $where .= " AND u.orientation != 2";
            } 

            if(!(isset($g_user['set_my_map_transgender']) && $g_user['set_my_map_transgender']==1)) {
                $where .= " AND u.orientation != 6";
            } 

            if(!(isset($g_user['set_my_map_nonbinary']) && $g_user['set_my_map_nonbinary']==1)) {
                $where .= " AND u.orientation != 7";
            } 

            //show only friends on map
            if(isset($g_user['set_show_only_friends_map']) && $g_user['set_show_only_friends_map'] == '1') {
                $where .= " AND (f.friend_id=" . to_sql($g_user['user_id'], "Text") . " OR f1.user_id=" . to_sql($g_user['user_id'], "Text")  . ") ";
                $from_add .= "LEFT JOIN friends_requests AS f ON f.user_id=u.user_id LEFT JOIN friends_requests AS f1 ON u.user_id=f1.friend_id ";
            }

            $where .= " )";

            //don't show me on map
            if(isset($g_user['set_show_me_map']) && $g_user['set_show_me_map'] == '2') {
                $where .= " and u.user_id !=" . to_sql($g_user['user_id'], "Text") . " ";
            } else {
                $where .= " or u.user_id =" . to_sql($g_user['user_id'], "Text") . " ";
            }

            //friend only can see, only I can see friend.
        
            $orderby = '';
            if(guser()['user_id']) {
                $orderby = "ORDER BY CASE WHEN u.user_id=".to_sql($g_user['user_id'], 'Text') . "THEN 0 ELSE 1 END, u.user_id";
            }

            $sql = "SELECT DISTINCT u.*, co.title as c_orientation FROM user u " . $from_add . "where " . $where . $orderby ; 
            $rows = DB::rows($sql);

            $map_users = [];
            foreach ($rows as $k => $v) {
                $flip = CFlipCard::flipFields($v['user_id']);
                $geo = User::getGeoPosition($v['city_id']);

                $banner_count = 0;
                $banner_count_limit = 8;
                $sql_event = "SELECT e.* FROM events_event AS e, events_event_guest AS eg WHERE eg.event_id=e.event_id AND e.event_private=0 AND eg.user_id=" . $v['user_id'] . " AND DATE_ADD(e.event_datetime, INTERVAL 3 HOUR) > NOW() ORDER BY e.event_n_comments DESC, e.event_datetime ASC LIMIT 0,10";
                $events = DB::rows($sql_event);

                $events_items = [];
                if(count($events) && isset($v['set_events_banner_activity']) && $v['set_events_banner_activity'] == 1) {
                    foreach ($events as $event) {
                        $event_item = [];
                        $images = CFlipCard::event_images($event['event_id']);
                        $event_item['title'] = $event['event_title'];
                        $event_item['image'] = $images['image_thumbnail'];
                        $events_items[] = $event_item;
                        $banner_count++;
                        if($banner_count == $banner_count_limit)  return;
                    }
                }

                $sql_hotdate = "SELECT e.* FROM hotdates_hotdate AS e, hotdates_hotdate_guest AS eg WHERE eg.hotdate_id=e.hotdate_id AND eg.user_id=" . $v['user_id'] . " AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() ORDER BY e.hotdate_n_comments DESC, e.hotdate_datetime ASC LIMIT 0,10";
                $hotdates =DB::rows($sql_hotdate);
                $hotdates_items = [];

                if (count($hotdates) && isset($v['set_nsc_banner_activity']) && $v['set_nsc_banner_activity'] == 1) {
                    foreach ($hotdates as $hotdate) {
                        $hotdate_item = [];
                        $images = CFlipCard::hotdate_images($hotdate['hotdate_id']);
                        $hotdate_item['title'] = $hotdate['hotdate_title'];
                        $hotdate_item['image'] = $images['image_thumbnail'];
                        $hotdates_items[] = $hotdate_item;
                        $banner_count++;
                        if($banner_count == $banner_count_limit) return;
                    }
                }

                $sql_partyhou = "SELECT e.* FROM partyhouz_partyhou AS e, partyhouz_partyhou_guest AS eg WHERE eg.partyhou_id=e.partyhou_id AND eg.user_id=" . $v['user_id'] . " AND DATE_ADD(e.partyhou_datetime, INTERVAL 3 HOUR) > NOW() ORDER BY e.partyhou_n_comments DESC, e.partyhou_datetime ASC LIMIT 0,10";
                $partyhouz = DB::rows($sql_partyhou);


                $partyhouz_items = [];

                foreach ($partyhouz as $partyhou) {
                    $partyhou_item = [];
                    $images = CFlipCard::partyhou_images($partyhou['partyhou_id']);
                    $partyhou_item['title'] = $partyhou['partyhou_title'];
                    $partyhou_item['image'] = $images['image_thumbnail'];
                    $partyhouz_items[] = $partyhou_item;
                    $banner_count++;
                    if($banner_count == $banner_count_limit) return;
                }

                $userinfo = DB::row('SELECT * FROM userinfo WHERE user_id = ' . to_sql($v['user_id'], 'Text'));

                // Append user photo to user data
                $user_photo = User::getPhotoDefault($v['user_id'], "m");
                $row['photo_url'] = $user_photo;

                $map_user = [];
                $map_user['user_id'] = $v['user_id'];
                $map_user['name'] = $v['name'];
                // $map_user['geo_position_lat'] = floatval($geo['geo_position_lat']) / IP::MULTIPLICATOR;
                // $map_user['geo_position_long'] = floatval($geo['geo_position_long']) / IP::MULTIPLICATOR;

                $map_user['geo_position_lat'] = $v['geo_position_lat'];
                $map_user['geo_position_long'] = $v['geo_position_long'];
                $map_user['orientation'] = $v['orientation'];
                $map_user['relation'] = $v['relation'];
                $map_user['c_orientation'] = $v['c_orientation'];
                $map_user['name_seo'] = $v['name_seo'];
                $map_user['photo_url'] = $user_photo;
                $map_user['smoking'] = $userinfo['smoking'];
                $map_user['drinking'] = $userinfo['drinking'];
                $map_user['events_items'] = $events_items;
                $map_user['hotdates_items'] = $hotdates_items;
                $map_user['partyhouz_items'] = $partyhouz_items;

                foreach ($flip as $key => $value) {
                    if(!is_numeric($key)){
                        $map_user[$key] = $value;
                    }   
                }

                $map_users[] = $map_user;
            }   

            if(get_param('ajax', '')){
                header('Content-Type: application/json');
                echo json_encode($map_users); die();
                exit;    
            }
            
        }

        if(get_param('map', '')) {
            return $map_users ;
        } else {
            $page = Users_List::show($where, $order, $from_add, '', $from_group,$redirectIfSingle);
            return $page;
        }
    }
}


