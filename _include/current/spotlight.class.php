<?php
class Spotlight extends CHtmlBlock
{
    public $location = null;
    public $update = true;

    function action() {
        global $g_user;

    }

    static function addItem($uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }
        if ($uid) {
            DB::insert('spotlight', array('user_id' => $uid));
            return DB::insert_id();
        }
        return 0;
    }

    static function removeItem($uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }
        if ($uid) {
            DB::delete('spotlight', '`user_id` = ' . to_sql($uid));
        }
    }

    static function mobileCheckAdd()
    {
        global $g_user;

        $responseData = false;
        if ($g_user['user_id']) {
            $responseData = Pay::getServicePrice('spotlight');
            $responseData['error'] = '';
            $responseData['msg'] = lSetVars('this_service_costs_credits', array('credit' => $g_user['credits'], 'price' => $responseData['credits']));
            if ($g_user['is_photo_public'] == 'N') {
                $responseData['error'] = 'not_photo_public';
            } elseif($g_user['credits'] - $responseData['credits'] < 0) {
                $responseData['error'] = 'refill_credits';
            }
        }
        return $responseData;
    }

    static function mobileActivated()
    {
        global $g_user;

        $responseData = false;
        if ($g_user['user_id']) {
            if ($g_user['is_photo_public'] == 'N') {
                return 'not_photo_public';
            }
            $responseData = 'refill_credits';
            $notFree = !Common::isOptionActive('free_site');
            $currentCredits = $g_user['credits'];
            $price = Pay::getServicePrice('spotlight');
            $currentCredits = $g_user['credits'] - $price['credits'];
            if ($currentCredits >= 0) {
                $responseData = array('id' => self::addItem(),
                                      'uid' => $g_user['user_id'],
                                      'photo_url' => User::getPhotoDefault($g_user['user_id'], 'm'));
                $popularity = User::getMaxPopularityInCity($g_user['city_id'], true);
                if (!$popularity) {
                    $popularity = 1;
                }
                $data = array('popularity' => $popularity,
                              'credits' =>  $currentCredits);
                User::update($data);
            }
        }
        return $responseData;
    }

    static function isThere()
    {
        global $g_user;
        global $sitePart;

        if (!Common::isCreditsEnabled() && $sitePart != 'administration') {
            return false;
        }

        $isMainSpotlight = false;
        $sql = self::getSql();
        $spotlightUsers = DB::rows($sql);
        foreach ($spotlightUsers as $user) {
            if ($user['user_id'] == $g_user['user_id']) {
                /*if (User::getPhotoDefault($g_user['user_id'], 's', true)) {
                    $isMainSpotlight = true;
                }*/
                $isMainSpotlight = true;
                break;
            }
        }
        return $isMainSpotlight;
    }

    static function getSql()
    {
        global $g_user;

        $spotlightPhotosNumber = Common::getOption('spotlight_photos_number');
        $sqlCity = to_sql($g_user['city_id']);
        $sqlState = to_sql($g_user['state_id']);
        $sqlCountry =  to_sql($g_user['country_id']);
        $sql = 'SELECT s.id AS item_id, u.*, IF(u.city_id=' . $sqlCity . ', 1, 0) + IF(u.state_id=' . $sqlState . ', 1, 0) + IF(u.country_id=' . $sqlCountry . ', 1, 0) AS near
                  FROM spotlight AS s
                  LEFT JOIN user AS u ON u.user_id = s.user_id
                  LEFT JOIN user_block_list AS ubl1 ON (ubl1.user_to = u.user_id AND ubl1.user_from = ' . to_sql($g_user['user_id']) . ')
                  LEFT JOIN user_block_list AS ubl2 ON (ubl2.user_from = u.user_id AND ubl2.user_to = ' . to_sql($g_user['user_id']) . ')
                 WHERE u.country_id = ' . $sqlCountry .
                  " AND " . User::isHiddenSql() . "
                   AND ubl1.id IS NULL
                   AND ubl2.id IS NULL
                 ORDER BY near DESC, s.id DESC
                LIMIT " . to_sql($spotlightPhotosNumber, 'Number');
        return $sql;
    }

    static function parseSpotlight(&$html, $update = false, $hasAlreadyItems = array())
    {
        global $g_user, $p;

        $blockSpotlight = 'spotlight';
        if (Common::isCreditsEnabled() && Common::isOptionActive('spotlight_enabled_urban')) {
            $spotlightPhotosNumber = Common::getOption('spotlight_photos_number');
            $html->setvar('spotlight_photos_number', $spotlightPhotosNumber);
            if ($html->varExists('hide_my_presence')) {
                $html->setvar('hide_my_presence', User::getInfoBasic($g_user['user_id'], 'set_hide_my_presence'));
            }

            $sql = self::getSql();

            $spotlightUsers = DB::rows($sql);
            $blockSpotlightItem = $blockSpotlight . '_item';
            $i = 0;
            $isMainSpotlight = false;

            if ($html->varExists('from_page')) {
                $display = get_param('display');
                $fromPage = 'search';
                if ($p == 'users_viewed_me.php') {
                    $fromPage = 'users_viewed_me';
                } elseif ($p == 'mutual_attractions.php') {
                    $fromPage = $display ? 'want_to_meet_you' : 'matches';
                }
                $html->setvar('from_page', $fromPage);
            }
            foreach ($spotlightUsers as $user) {
                if ($update && in_array($user['item_id'], $hasAlreadyItems)) {
                    continue;
                }
                if ($user['user_id'] == $g_user['user_id']) {
                    $isMainSpotlight = true;
                    $html->setvar($blockSpotlightItem . '_photo_id', User::getPhotoDefault($user['user_id'], 'r', true));
                    $html->parse($blockSpotlightItem . '_main', true);
                } else {
                    $html->clean($blockSpotlightItem . '_main');
                }

                $key = 'spotlight_item_photo_id' . $user['user_id'];
                $photoId = Cache::get($key);
                if($photoId === null) {
                    $photoId = User::getPhotoDefault($user['user_id'], 's', true, $user['gender'], DB_MAX_INDEX, true, false, true);
                }
                Cache::add($key, $photoId);
                if (!$photoId) {
                    continue;
                }

                if($html->varExists('url_profile')) {
                    $html->setvar('url_profile', User::url($user['user_id'], $user));
                }

                $size = 'r';
                if (Common::isMobile()) {
                    $size = 'm';
                }
                $urlPhoto = User::getPhotoDefault($user['user_id'], $size, false, $user['gender']);
                $html->setvar($blockSpotlightItem . '_name', $user['name']);
                $birth = explode('-', $user['birth']);
                $html->setvar($blockSpotlightItem . '_age', User::getAge($birth[0], $birth[1], $birth[2]));
                $html->setvar($blockSpotlightItem . '_city', l($user['city']));
                $html->setvar($blockSpotlightItem . '_photo', $urlPhoto);
                $html->setvar($blockSpotlightItem . '_id', $user['item_id']);
                $html->setvar($blockSpotlightItem . '_user_id', $user['user_id']);
                if ($update) {
                    $html->parse($blockSpotlightItem . '_update_start', false);
                    $html->parse($blockSpotlightItem . '_update_end', false);
                };
                $html->parse($blockSpotlightItem);
                $i++;

            }
            $html->setvar($blockSpotlight . '_costs', intval(Pay::getServicePrice('spotlight', 'credits')));
            $html->setvar($blockSpotlight . '_i_am', intval($isMainSpotlight));
            $title = l('put_me_here');
            if ($isMainSpotlight) {
                $html->parse($blockSpotlight . '_top');
                $html->parse($blockSpotlight . '_increase_hide');
                $title = l('your_are_here');
            } else {
                $html->setvar($blockSpotlight . '_main_photo_id', User::getPhotoDefault($g_user['user_id'], 'r', true, $g_user['gender']));
                $html->setvar($blockSpotlight . '_main_photo', User::getPhotoDefault($g_user['user_id'], 'r', false, $g_user['gender']));
                $html->parse($blockSpotlight . '_increase');
                $html->parse($blockSpotlight . '_your_hide');
            }
            /* Urban mobile */
            if ($html->varExists($blockSpotlight . '_title')) {
                $html->setvar($blockSpotlight . '_title', $title);
            }
            if ($html->varExists($blockSpotlight . '_is_service')) {
                $html->setvar($blockSpotlight . '_is_service', $isMainSpotlight);
            }
            if ($html->varExists($blockSpotlight . '_request_uri')) {
                $html->setvar($blockSpotlight . '_request_uri', Pay::getUrl());
            }
            $block = 'response_refill_credits';
            if ($html->blockExists($block) && get_session($block)) {
                delses($block);
                $html->parse($block, false);
            }
            /* Urban mobile */

            $maxNumberEmptyBlock = 13;
            $customMaxNumberEmptyBlock = Common::getOption('number_empty_block_spotlight', 'template_options');
            if ($customMaxNumberEmptyBlock) {
                $maxNumberEmptyBlock = $customMaxNumberEmptyBlock;
            }
            if ($i < $maxNumberEmptyBlock) {
                for ($j = 0; $j < $maxNumberEmptyBlock - $i; $j++) {
                    $html->parse($blockSpotlightItem . '_empty', true);
                }
            }

            $html->parse($blockSpotlight);
        }
    }

    function parseBlock(&$html)
	{
        self::parseSpotlight($html, $this->update, get_param_array('spotlight_items'));

		parent::parseBlock($html);
	}
}
