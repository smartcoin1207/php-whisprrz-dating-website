<?php

class UsersFilter extends UserFields {

    public $parseSearchModule = true;
    public static $location = null;
    public static $locationTitle = null;
    public static $locationTitleDb = null;
    public static $locationDefault = array();

    public static function getLocationNotLogged()
    {
        $location = array('country' => 197,
                          'state' => 3060,
                          'city' => 44863);

        $settings = json_decode(self::getSettingsNotLogged(), true);
        $allowedOption = array('country', 'state', 'city');
        foreach ($settings as $param => $data) {
            if(in_array($param, $allowedOption)) {
                $location[$param] = $data['value'];
            }
        }
        return $location;
    }

    public static function getSettingsNotLogged()
    {
        $settings = get_cookie('settings_filter_search');
        if ($settings == '') {
            $settings = array('i_am_here_to' => array('field' => 'i_am_here_to', 'value' => 1),
                              'p_orientation' => array('field' => 'p_orientation', 'value' => 3),
                              'p_age_from' => array('field' => 'p_age_from', 'value' => 18),
                              'p_age_to' => array('field' => 'p_age_to', 'value' => 100),
                              'country' => array('field' => 'country', 'value' => 197),
                              'state' => array('field' => 'state', 'value' => 3060),
                              'city' => array('field' => 'city', 'value' => 44863),
                              'radius' => array('field' => 'radius', 'value' => intval(Common::getOption('max_search_distance')) + 1),
                              'all_countries' => array('field' => 'all_countries', 'value' => 0),

            );
            $settings = json_encode($settings, true);
            set_cookie('settings_filter_search', $settings);
        }
        return $settings;
    }

    public static function setSettingsNotLogged()
    {
        global $g_user;

        $settings = json_decode(self::getSettingsNotLogged(), true);
        foreach ($settings as $param => $data) {
            $get = $data['value'];
            if ($param == 'p_orientation') {
                $get = UserFields::checksToParamsArray('const_orientation', $data['value']);
            }
            $_GET[$param] = $get;
            $g_user[$param] = $data['value'];
        }
    }

    public static function getLocation()
    {
        if(!self::$location) {
            self::$location = self::location();
        }

        return self::$location;
    }

    public static function getLocationTitleDb()
    {
        if(!self::$locationTitleDb) {
            $location = self::getLocation();
            if(isset($location['city_title'])) {
                self::$locationTitleDb = $location['city_title'];
            }
        }
        return self::$locationTitleDb;
    }

    public static function getLocationTitle()
    {
        if(!self::$locationTitle) {
            /*$location = self::getLocation();
            if(isset($location['city_title'])) {
                self::$locationTitle = lSetVars('find_new_friends_in_city_now', array('city' => $location['city_title']));
            }*/

            $location = self::getLocation();

            self::$locationTitle = self::getLocationFindNewFriendsTitle($location);
            /*
            $title = self::getLocationTitleDb();
            if ($title) {
                self::$locationTitle = lSetVars('find_new_friends_in_city_now', array('city' => $title));
            }
            */
        }
        return self::$locationTitle;
    }

    public static function location()
    {
        global $g_user;

        $guid = guid();
        //$geoInfo = IP::geoInfoCity();
        $geoInfo = getDemoCapitalCountry();
        $location = array(
            'country' => get_param('country', $geoInfo['country_id']),
            'state' => get_param('state', $geoInfo['state_id']),
            'city' => get_param('city', $geoInfo['city_id']),
        );

        $locationVisitor = $location;
        if (!$guid) {
            if (!$locationVisitor['state']) {
                $locationVisitor['state'] = $geoInfo['state_id'];
            }
            if (!$locationVisitor['city']) {
                $locationVisitor['city'] = $geoInfo['city_id'];
            }
        }
        self::$locationDefault = $locationVisitor;

        $city = get_param('city');
        if (!$city && get_param('state')) {
            $location['city'] = 0;
        } elseif (!$city && get_param('country')) {
            $location['state'] = 0;
            $location['city'] = 0;
        }

        //$location = self::getLocationNotLogged();
        // location
        if($guid) {
            $city = guser('city_id');
            if($city) {
                $location = array(
                    'country' => get_param('country', guser('country_id')),
                    'state' => get_param('state', guser('state_id')),
                    'city' => get_param('city', $city),
                );
            }
        }
        $location = self::getLocationCityTitle($location);
        return $location;
    }

    public static function getLocationCityTitle($location, $byCountry = false){
        if($location['city']!=0 && $byCountry === false){
            $sql = 'SELECT city_title FROM geo_city
                     WHERE city_id = ' . to_sql($location['city']);
            $location['city_title'] = l(DB::result($sql, 0, 0, true));
        } elseif($location['state']!=0 && $byCountry === false){
            $sql = 'SELECT state_title FROM geo_state
                     WHERE state_id = ' . to_sql($location['state']);
            $location['state_title'] = l(DB::result($sql, 0, 0, true));
            $location['city_title'] = lSetVars('all_cities_by_region',array('state'=>$location['state_title']));
        } elseif($location['country']!=0){
            $sql = 'SELECT country_title FROM geo_country
                     WHERE country_id = ' . to_sql($location['country']);
            $location['country_title'] = l(DB::result($sql, 0, 0, true));
            $location['city_title'] = lSetVars('all_regions_by_country',array('country'=>$location['country_title']));
        } else {
            $location['city_title'] = l('all_countries');
        }
        return $location;
    }

    public static function getLocationFindNewFriendsTitle($location, $radius = null)
    {
        $peopleNearby = 0;
        $byCountry = false;
        if (guid()) {
            $filters = guser('user_search_filters');
            $filtersInfo = json_decode($filters, true);
            if (isset($filtersInfo['people_nearby']) && $filtersInfo['people_nearby']['value']) {
                $peopleNearby = 1;
            }
            if ($radius === null) {
                $radius = 0;
                if (isset($filtersInfo['radius'])) {
                    $radius = $filtersInfo['radius']['value'];
                }
            }
            if ($radius && isset($location['city']) && $location['city']) {
                $maxSearchDistance = intval(Common::getOption('max_search_distance'));
                $isMaxFilterDistanceCountry = intval(Common::getOption('max_filter_distance') == 'max_search_country');
                if ($isMaxFilterDistanceCountry && $radius > $maxSearchDistance) {
                    $byCountry = true;
                }
            }
        }
        if ($peopleNearby) {
            $title = array('city_title' => guser('geo_position_city'),
                           'state_title' => guser('geo_position_state'),
                           'country_title' => guser('geo_position_country'));
            if ($byCountry) {
                $location = array('city' => 1,
                                  'state' => 0,
                                  'country' => 1);
            } else {
                $location = array('city' => 1,
                                  'state' => 1,
                                  'country' => 1);
            }
        } else {
            $title = self::getLocationCityTitle($location, $byCountry);
        }

        $value = lSetVars('find_new_friends_in_city_now',array('city'=>$title['city_title']));
        if($location['country']==0){
            $keys = array('find_new_friends_from_all_countries');
            $value = lCascade($value,$keys);
        } elseif($location['state']==0){
            $keys = array('find_new_friends_from_all_regions');
            $value = lCascade($value,$keys);
            if(isset($title['country_title'])){
               $value = Common::replaceByVars($value, array('country'=>$title['country_title']));
            }
        } elseif($location['city']==0){
            $keys = array('find_new_friends_from_all_cities');
            $value = lCascade($value,$keys);
            if(isset($title['state_title'])){
                $value = Common::replaceByVars($value, array('state'=>$title['state_title']));
            }
        }
        return $value;
    }

    public function parseBlock(&$html)
    {
        $isFilterSocial = Common::isOptionActiveTemplate('list_users_filter_social');
        $optionTmplName = Common::getOption('name', 'template_options');
        $this->selectionFields['search_advanced'] = array('from', 'checks', 'group');

        // I am here to
        // orientations
        // age
        // distance
        // location
        // 3 custom filters


        /*if($uid) {
        } else {
            $filters = self::getSettingsNotLogged();
        }*/

        // show active filters with active options
        $uid = guid();
        $maxSearchDistance = intval(Common::getOption('max_search_distance'));
        $isMaxFilterDistanceCountry = intval(Common::getOption('max_filter_distance') == 'max_search_country');
       // if($uid) {
        if ($uid) {
            $filters = guser('user_search_filters');
            $stateFilter = guser('state_filter_search');
            if ($optionTmplName == 'impact' && !User::isSuperPowers()) {
                $stateFilter = 0;
            }
            $dataLokingFor = User::getInfoBasic($uid);
        } else {
            $filters = User::setDefaultParamsFilterUser($uid, false);
            $stateFilter = 1;
            $dataLokingFor = array();
        }

        //global $g_user;
        //$userinfo = User::getInfoFull($uid);
        //$g_user['user_search_filters'] = $userinfo['user_search_filters'];
        $radius = 0;
        $peopleNearby = 0;
        if ($filters) {
                //echo $filters . '<br>';
                $filtersInfo = json_decode($filters, true);
                //var_dump_pre($filtersInfo);
                $notFilterFields = array(
                    'country', 'state', 'city', 'radius', 'all_countries', 'with_photo', 'people_nearby'
                );
                $filterNumber = 1;
                foreach($filtersInfo as $filterInfoKey => $filterInfoValue) {
                    if(in_array($filterInfoValue['field'], $notFilterFields)) {
                        continue;
                    }
                    if($filterInfoKey == 'status') {
                        continue;
                    }
                    $html->setvar('filter_number', $filterNumber);
                    $html->setvar('filter_value', $filterInfoValue['field']);
                    $html->parse('filter_init');
                    $filterNumber++;
                }

                if(isset($filtersInfo['radius'])) {
                    if(isset($filtersInfo['city']) && $filtersInfo['city']==0){
                        $filtersInfo['radius']['value']=0;
                    }

                    if (!$isMaxFilterDistanceCountry && $filtersInfo['radius']['value'] > $maxSearchDistance) {
                        $filtersInfo['radius']['value'] = $maxSearchDistance;
                    }
                    $html->setvar('radius', $filtersInfo['radius']['value']);
                    $radius = $filtersInfo['radius']['value'];
                }

                if (isset($filtersInfo['people_nearby']) && $filtersInfo['people_nearby']['value']) {
                    $peopleNearby = 1;
                }

                if(isset($filtersInfo['all_countries']) && $filtersInfo['all_countries']['value']==1) {
                    $html->setvar('all_countries_checked', "checked");
                }
                if(!isset($filtersInfo['status'])){
                    $filtersInfo['status'] = '';
                }
                if(!isset($filtersInfo['with_photo']) || $filtersInfo['with_photo']['value']){
                    $html->parse('module_search_with_photo', false);
                }
        }

        $html->setvar('people_nearby', $peopleNearby);


        $html->parse('filter_show_active');
        // $dataLokingFor['city'] = l(self::getLocationTitleDb());

        $searchByRadiusAllCountry = ($radius > intval(Common::getOption('max_search_distance')));

        $location = self::getLocation();
        $location = self::getLocationCityTitle($location, $searchByRadiusAllCountry);

        if (!$uid) {
            $sql = 'SELECT city_title FROM geo_city
                     WHERE city_id = ' . to_sql(self::$locationDefault['city']);
            $html->setvar('city_title_visitor', toJsL(DB::result($sql, 0, 0, true)));
            $sql = 'SELECT country_title FROM geo_country
                     WHERE country_id = ' . to_sql(self::$locationDefault['country']);
            $html->setvar('country_title_visitor', toJsL(DB::result($sql, 0, 0, true)));
        }

        $dataLokingFor['radius'] = $radius;

        $dataLokingFor['country'] = '';
        if($searchByRadiusAllCountry) {
            if ($peopleNearby) {
                $dataLokingFor['country'] = l(guser('geo_position_country'));
            } elseif (isset($location['country_title'])) {
                $dataLokingFor['country'] = $location['country_title'];
            }
        }

        if ($peopleNearby) {
            $dataLokingFor['city'] = l(guser('geo_position_city'));
            $dataLokingFor['all_items_select'] = 0;
        } else {
            $dataLokingFor['city'] = $location['city_title'];

            if($location['city']==0){
                $dataLokingFor['all_items_select']=1;
            } else {
                $dataLokingFor['all_items_select']=0;
            }
        }

        $html->setvar('offset', get_param('offset', 1));
        $html->setvar('max_filter_distance_country', $isMaxFilterDistanceCountry);
        $html->setvar('radius_max', $maxSearchDistance + 1);
        $vars = array('unit' => l(Common::getOption('unit_distance')));
        $html->setvar('slider_within', lSetVars('slider_within', $vars));
        $html->setvar('profile_search_filter_radius', lSetVars('profile_search_filter_radius', $vars));

        // Edge parse social
        self::parseLookingForSocialTemplate($html, $filters);

        //if ($isFilterSocial) {
        //    self::parseLookingForSocialTemplate($html, $filters);
        //    parent::parseBlock($html);
        //    return;
        //}

        // While for unauthorized do not need
        UsersFilterHead::parseHeaderFilter($html);

        if ($uid) {
            /* State Filter */
            $titlesJs = array();
            //if (UserFields::isActive('i_am_here_to')) {
                $titles = Cache::get('field_values_const_i_am_here_to');
                if(!$titles) {
                    $titles = DB::select('const_i_am_here_to');
                }
                foreach ($titles as $key => $item) {
                    $titlesJs['i_am_here_to'][$item['id']] = User::prepareIAmHereToValue($item['id'], 'search');
                }
            //}
            //if (UserFields::isActive('orientation')) {

                $titlesJs['p_orientation'][0] = l('somebody');

                $orientations = DB::rows('SELECT * FROM const_orientation', 1, true);
                if($orientations) {
                    foreach ($orientations as $orientation) {
                        $title = UserFields::translation('orientation', $orientation['title'], 'filter');
                        if(l('profile_orientations_allow_lowercase') == 'Y') {
                            $title = mb_strtolower($title, 'UTF-8');
                        }
                        $titlesJs['p_orientation'][$orientation['id']] = $title;
                    }
                }
            //

            $vars = array();
            if(!UserFields::isActive('i_am_here_to')) {
                $vars['here_to'] = User::prepareIAmHereInactive();
            }

            $html->setvar('general_title', lSetVars('wants_to_with_search', $vars));

            if ($titlesJs) {
                foreach ($titlesJs as $key => $value) {
                    $block = 'set_general_l';
                    $html->setvar($block . '_key', $key);
                    $html->setvar($block . '_value', json_encode($value));
                    $html->parse($block, true);
                }
            }

            $html->setvar('filter_looking_for', User::getLookingFor($uid, $dataLokingFor, 'search'));
        }

        if ($stateFilter) {
            $html->parse('part_hide');
            $html->parse('btn_extended_show');
        } else {
            $html->parse('general_class');
            $html->parse('general_hide');
            $html->parse('part1_hide');
            $html->parse('btn_extended_hide');
        }
        /* State Filter */

        $this->parseFieldsAll($html, 'search_advanced');

        $this->parseSearchModule = true;
        $this->parseLookingFor($html);
        $this->parseSearchModule = false;

        $isAllowedExtendedSearch = intval(User::isSuperPowers() || Common::isOptionActive('free_site')) || !Common::isActiveFeatureSuperPowers('extended_search');
        $html->setvar('is_super_powers', $isAllowedExtendedSearch);

        $dataLocation = self::getLocation();
        if ($peopleNearby) {
            $dataLocation['city_title'] = l('filter_people_nearby');
        }
        htmlSetVars($html, $dataLocation);

        // find appearance position start
        $fields = array();
        $fieldsAppearance = array();
        foreach($this->gFields as $field => $info) {
            if(isset($info['type']) && ($info['type'] == 'int' || $info['type'] == 'checkbox' || $info['type'] == 'group')) {
                // only selects with checks and intervals
                if($info['type'] == 'int' && (!isset($this->gFields['p_' . $field]) && !isset($this->gFields['p_' . $field . '_from']))) {
                    continue;
                }
                $fieldTitle = l($info['title']);
                if(isset($info['group']) && $info['group'] == 2) {
                    $fieldsAppearance[$field] = $fieldTitle;
                } else {
                    $fields[$field] = $fieldTitle;
                }
            }
        }

        $fieldsSorted = array();
        if($fieldsAppearance && $fields) {
            foreach($fields as $fieldName => $fieldTitle) {
                if($fieldName == 'appearance_group') {
                    $fieldsSorted = array_merge($fieldsSorted, $fieldsAppearance);
                } else {
                    $fieldsSorted[$fieldName] = $fieldTitle;
                }
            }
        } else {
            $fieldsSorted = $fields;
        }

        $html->setvar('filter_options', h_options($fieldsSorted, ''));
        if ($html->blockExists('scroll_to_filter') && get_param('back')) {
            $html->parse('scroll_to_filter', false);
        }
        parent::parseBlock($html);
    }
}