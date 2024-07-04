<?php
class Interests extends CHtmlBlock
{

    function action()
    {
        $cmd = get_param('cmd');

    }

    static function deleteFullInterest($id)
    {
        if ($id) {
            $where = '`interest` = ' . to_sql($id);
            $usersInterests = DB::select('user_interests', $where);
            foreach ($usersInterests as $item) {
                self::removeInterestsItemWall($item['user_id'], $id, $item['wall_id']);
            }
            DB::delete('interests', '`id` = ' . to_sql($id));
            DB::delete('user_interests', '`interest` = ' . to_sql($id));
        }
    }

    static function deleteInterest($uid = null, $id = null)
    {
        $result = false;
        if ($uid === null) {
            $uid = guid();
        }
        if ($id === null) {
            $id = intval(get_param('id'));
        }
        if ($uid && $id) {
            $where = '`user_id` = ' . to_sql($uid, 'Number') .
                     ' AND `interest` = ' . to_sql($id, 'Number');
            self::removeInterestsItemWall($uid, $id);
            DB::delete('user_interests', $where);
            $where = '`id` = ' . to_sql($id, 'Number');
            $interests = DB::select('interests', $where);
            if ($interests && isset($interests[0])) {
                $interests = $interests[0];
                $count = $interests['counter'] - 1;
                if ($count || !$interests['user_id']) {
                    DB::update('interests', array('counter' => $count), $where);
                } else {
                    DB::delete('interests', $where);
                }
            }
            $result = true;
        }
        return $result;
    }

    static function removeInterestsItemWall($uid, $id, $wallId = null)
    {
        if ($wallId === null) {
            $where = '`user_id` = ' . to_sql($uid, 'Number') .
                     ' AND `interest` = ' . to_sql($id, 'Number');
            $wallId = DB::field('user_interests', 'wall_id', $where);
            if ($wallId && isset($wallId[0])) {
                $wallId = $wallId[0];
            } else {
                $wallId = 0;
            }
        }
        if ($wallId) {
            $where = '`user_id` = ' . to_sql($uid, 'Number') .
                     ' AND `wall_id` = ' . to_sql($wallId, 'Number');
            $countWall = DB::count('user_interests', $where) - 1;
            if (!$countWall) {
                Wall::removeById($wallId);
            }
        }
    }
    
    static function getInterestById($id)
    {
		$sql = 'SELECT I.*
                  FROM `interests` AS I
                 WHERE I.id = ' . to_sql($id, 'Number') .' LIMIT 1' ;
        return DB::row($sql);
    }

    static function getTitleCategory($id)
    {
        $categoryTitle = '';
        $category = DB::field('const_interests', 'title', '`id` = ' . to_sql($id));
        if (isset($category[0])) {
            $categoryTitle = l($category[0]);
        }
        return $categoryTitle;
    }

    static function addInterest($catId, $value, $lang, $uid = null)
    {
        if ($uid === null) {
            global $g_user;
            $uid = $g_user['user_id'];
        }
        $value = trim($value);
        if (!$catId || !$value || !$lang) {
            return 0;
        }

        $isInterest = 1;
        $id = 0;
        if ($uid) {
            $sql = "SELECT `id`
                      FROM `interests`
                     WHERE `category` = " . to_sql($catId, 'Number') .
                     " AND `interest` = " . to_sql($value);
            $id = DB::result($sql);
        }
        if (!$id) {
            $vars = array('category' => to_sql($catId, 'Number'),
                          'user_id' => to_sql($uid, 'Number'),
                          'interest' => $value,
                          'counter' => $uid ? 1 : 0,
                          'lang' => $lang);
            DB::insert('interests', $vars);
            $id = DB::insert_id();
            $isInterest = 0;
        } else {
            $isInterest = DB::count('user_interests', '`user_id` = ' . to_sql($uid, 'Number') . ' AND `interest` = ' . to_sql($id, 'Number'));
            if (!$isInterest) {
                $sql = "UPDATE `interests` SET counter = counter+1 WHERE id = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
        }
        return $isInterest ? 0 : $id;
    }


    function parseBlock(&$html)
	{
        global $g, $g_user;

        $cmd = get_param('cmd');
        $catId = get_param('cat_id', 0);
        $value = trim(get_param('value'));

        $langLoad = Common::getOption('lang_loaded', 'main');
        $orderLang = "i.lang = " . to_sql($langLoad) . " DESC, ";
        if ($cmd == 'search_interests' && !empty($value)) {
            $html->setvar('interest_search', lSetVars('add_a_new_interest_btn', array('name' => $value)));
            $value = $value . '%';
            $sql = "SELECT i.*
                      FROM interests as i
                      LEFT JOIN user_interests as ui ON i.id = ui.interest AND ui.user_id=" . to_sql($g_user['user_id'], 'Number') . "
                     WHERE ui.id IS NULL AND i.interest LIKE " . to_sql($value) . "
                     ORDER BY " . $orderLang . " i.counter DESC, i.id DESC LIMIT 10";
            DB::query($sql);
            while ($row = DB::fetch_row()){
                $html->setvar('int_id', $row['id']);
                $html->setvar('cat_id', $row['category']);
                $html->setvar('interest_class', UserFields::getArrayNameIcoField('interests', $row['category'], 'list'));
                $html->setvar('interest', mb_ucfirst($row['interest']));
                $html->parse('interests_search_item', true);
            }
            $html->parse('interests_search');

        } elseif ($cmd == 'more_interests' && $catId) {
            $step = get_param('step', 0);
            $where = '';
            if ($catId != 1) {
                $where = 'AND i.category = ' . to_sql($catId, 'Number');
            }
            $numberCat = UserFields::getNumberNotEmptyCategory();
            $isEndStep = 0;
            $sql = 'SELECT i.*
                      FROM interests as i
                      LEFT JOIN user_interests as ui ON i.id = ui.interest AND ui.user_id=' . to_sql($g_user['user_id'], 'Number') . '
                     WHERE ui.id IS NULL ' . $where .
                   ' ORDER BY ' . $orderLang . ' i.counter DESC, i.id DESC LIMIT ' . to_sql($step*$numberCat, 'Number') . ', ' . to_sql($numberCat, 'Number');

            DB::query($sql);
            if (!DB::num_rows()) {
                $isEndStep = 1;
                $sql = 'SELECT i.*
                          FROM interests as i
                          LEFT JOIN user_interests as ui ON i.id = ui.interest AND ui.user_id=' . to_sql($g_user['user_id'], 'Number') . '
                         WHERE ui.id IS NULL ' . $where . '
                         ORDER BY ' . $orderLang . ' i.counter DESC, i.id DESC LIMIT ' . to_sql($numberCat, 'Number');
                DB::query($sql);
            }
            while ($row = DB::fetch_row()){
                $html->setvar('int_id', $row['id']);
                $html->setvar('cat_id', $row['category']);
                $html->setvar('interest', mb_ucfirst($row['interest']));
                $html->parse('interests_custom_item', true);
            }
            $html->setvar('cat_id_js', $catId);
            $html->setvar('end_step', $isEndStep);
            $html->parse('interests_custom_item_list');

        } elseif ($cmd == 'add_new_interest' && $catId && !empty($value)) {
            /*$id = 0;
            $isInterest = 1;
            $sql = "SELECT `id`
                      FROM `interests`
                     WHERE `category` = " . to_sql($catId, 'Number') .
                     " AND `interest` = " . to_sql($value);
            $id = DB::result($sql);
            if (empty($id)) {
                    $vars = array('category' => to_sql($catId, 'Number'),
                                  'user_id' => to_sql($g_user['user_id'], 'Number'),
                                  'interest' => $value,
                                  'counter' => 1,
                                  'lang' => $langLoad);
                    DB::insert('interests', $vars);
                    $id = DB::insert_id();
                    $isInterest = 0;
            } else {
                $isInterest = DB::count('user_interests', '`user_id` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `interest` = ' . to_sql($id, 'Number'));
                if (!$isInterest) {
                    $sql = "UPDATE `interests` SET counter = counter+1 WHERE id = " . to_sql($id, 'Number');
                    DB::execute($sql);
                }
            }*/

            $idInterest = self::addInterest($catId, $value, $langLoad);
            if ($idInterest) {
                $wallId = Wall::addGroup('interests');
                $vars = array('user_id' => to_sql($g_user['user_id'], 'Number'),
                              'interest' => to_sql($idInterest, 'Number'),
                              'wall_id' => $wallId);
                DB::insert('user_interests', $vars);
                $html->setvar('int_id', $idInterest);
                $html->setvar('cat_id', $catId);
                $html->setvar('interest_class', UserFields::getArrayNameIcoField('interests', $catId, 'normal'));
                $titleUpper = mb_ucfirst($value);
                $html->setvar('interest', $titleUpper);
                $html->setvar('interest_he', he($titleUpper));
                $html->parse('new_interests');
            }
		}

        parent::parseBlock($html);
	}
}