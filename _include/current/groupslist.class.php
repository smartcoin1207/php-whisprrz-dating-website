<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class GroupsList extends Groups{

    static $isGetDataWithFilter = false;
    static $tbGroup = 'groups_social';
    static $tbTags = 'groups_social_tags';
    static $tbTagsRelations = 'groups_social_tags_relations';

    static public function getTotalGroupsFromUser($uid = null, $isPage = false) {
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        $key = "GroupsList_getTotalGroupsFromUser_{$uid}_{$isPage}";
        $count = Cache::get($key);
        if($count === null) {
            $where = self::getWhereList('', $uid, $isPage);
            $count = DB::count(self::$table, $where);
            Cache::add($key, $count);
        }

        return $count;
    }

    static public function getTotalGroups($uid = null, $isPage = null, $whereCustomUser = '') {
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
        }
        if ($whereTags == 'no_tags') {
            return 0;
        }
        if ($whereTags) {
            $where = self::getWhereList('GR.', $uid);
            $sql = 'SELECT COUNT(*) FROM (
                        SELECT COUNT(*)
                          FROM `' . self::$tbTagsRelations . '` AS TR
                          JOIN `' . self::$tbGroup . '` AS GR ON GR.group_id = TR.group_id
                         WHERE ' . $where
                                 . $whereTags
                     . ' GROUP BY GR.group_id) AS GT';
            return DB::result($sql);
        } else {
            $where = self::getWhereList('', $uid, $isPage, $whereCustomUser);
            return DB::count(self::$table, $where);
        }
    }

    static function getWhereList($table = '', $uid = 0, $isPage = null, $whereCustomUser = '') {
        $guid = guid();
        $where = '';

        if ($isPage === null) {
            $isPage = self::isPage();
        }
        $where = " {$table}page = " . to_sql(intval($isPage));

        if ($uid) {
			if ($whereCustomUser) {
				$where .= " AND ({$table}user_id = " . to_sql($uid) . "{$whereCustomUser})";
			} else {
				$where .= " AND {$table}user_id = " . to_sql($uid);
			}
        } elseif ($guid) {
            $option = self::isPage() ? 'show_your_page_browse_pages' : 'show_your_group_browse_groups';
            $isShowMyGroup = Common::isOptionActive($option, 'edge_member_settings');
            if (!$isShowMyGroup) {
                $where .= " AND {$table}user_id != " . to_sql($guid);
            }
        }
        if (!$uid) {
            $searchQuery = trim(get_param('search_query'));
            if ($searchQuery) {
                $searchQuery = urldecode($searchQuery);
                $where .= " AND ({$table}title  LIKE '%" . to_sql($searchQuery, 'Plain') . "%'
                              OR {$table}description LIKE '%" . to_sql($searchQuery, 'Plain') . "%')";
            }
        }

        return $where;
    }

    static public function getTypeOrderList($notRandom = false, $lang = false)	{
        global $p;

        if ($lang !== false) {
            $pLast = $p;
            $p = 'groups_list.php';
        }
        $list = array(
            'order_new_groups'      => l('order_new_groups', $lang),
            'order_most_commented'  => l('order_most_commented', $lang),
            'order_most_posts'      => l('order_most_posts', $lang),
            'order_random'          => l('order_random', $lang)
        );
        if ($lang !== false) {
            $p = $pLast;
        }
        if ($notRandom) {
            unset($list['order_random']);
        }
        return $list;
    }

    static function getOrderList($typeOrder = '', $table = '') {
        $orderBy = 'date DESC, group_id DESC';
        if ($typeOrder == 'order_most_commented') {
            $orderBy = 'count_comments DESC, group_id DESC';
        } elseif ($typeOrder == 'order_most_posts') {
            $orderBy = 'count_posts DESC, group_id DESC';
        } elseif ($typeOrder == 'order_random') {
            $orderBy = 'RAND()';
        }

        return $orderBy;
    }

    static public function getListGroups($limit = '', $typeOrder = '', $uid = null, $isPage = null, $whereCustomUser = '') {

        $result = array();
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        if ($isPage === null) {
            $isPage = self::isPage();
        }

        if ($typeOrder == '') {
            $typeOrderDefault = self::getTypeOrder($isPage);
        }

        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
            if ($whereTags == 'no_tags') {
                return $result;
            }
        }

        $order = self::getOrderList($typeOrder);
        if ($order) {
			if ($whereCustomUser) {
				$order = ' ORDER BY user_groups DESC, ' . $order;
			} else {
				$order = ' ORDER BY ' . $order;
			}
        }

        if ($whereTags) {
            $where = self::getWhereList('GR.', $uid, $isPage);
            $sql = 'SELECT GR.*
                      FROM `' . self::$tbTagsRelations . '` AS TR
                      JOIN `' . self::$tbGroup . '` AS GR ON GR.group_id = TR.group_id
                     WHERE ' . $where
                             . $whereTags
                             . ' GROUP BY GR.group_id '
                             . $order
                             . $limit;
        } else {
            $where = self::getWhereList('', $uid, $isPage, $whereCustomUser);

            $sql = 'SELECT *, IF(`user_id` = ' . to_sql($uid) . ', 1, 0) AS user_groups
                      FROM `' . self::$table . '`
                     WHERE ' . $where
                             . $order
                             . $limit;
        }
		//print_r_pre($sql, true);

        $groups = DB::rows($sql);
        foreach ($groups as $item) {
            $groupId = $item['group_id'];
            $result[$groupId] = $item;
            $result[$groupId]['url'] = Groups::url($groupId, $item);
        }

        return $result;
    }

    static function getOptionGroup($key = '', $isPage = false, $module = 'edge_general_settings') {
        $prf = $isPage ? 'pages' : 'groups';
        if ($key == 'number_row') {
            $result = Common::getOptionInt("list_{$prf}_number_row", $module);
        } elseif ($key == 'type_order') {
            $result = Common::getOption("list_{$prf}_type_order", $module);
        } elseif ($key == 'number_items') {
            $result = Common::getOptionInt("list_{$prf}_number_items", $module);
        } elseif ($key == 'display_type') {
            $result = Common::getOption("list_{$prf}_display_type", $module);
        }


        return $result;
    }

    static public function getNumberRow($isPage = null) {
        global $p;

        $optionTmplName = Common::getTmplName();
        if ($isPage === null) {
            $isPage = self::isPage();
        }
        $module = "{$optionTmplName}_general_settings";
        if ($p == 'index.php') {
            $module = "{$optionTmplName}_main_page_settings";
        }

        $number = self::getOptionGroup('number_row', $isPage, $module);

        return $number;
    }

    static public function getNumberItems($isPage = null) {
        global $p;

        $optionTmplName = Common::getTmplName();
        if ($isPage === null) {
            $isPage = self::isPage();
        }

        $module = "{$optionTmplName}_general_settings";
        if ($p == 'index.php') {
            $module = "{$optionTmplName}_main_page_settings";
        }

        $number = self::getOptionGroup('number_items', $isPage, $module);
        return $number;
    }

    static public function getTypeOrder($isPage = null) {
        global $p;

        $optionTmplName = Common::getTmplName();
        if ($isPage === null) {
            $isPage = self::isPage();
        }

        $module = "{$optionTmplName}_general_settings";
        if ($p == 'index.php') {
            $module = "{$optionTmplName}_main_page_settings";
        }

        $typeOrder = self::getOptionGroup('type_order', $isPage, $module);

        return $typeOrder;
    }

    static public function getDisplayType($isPage = null) {
        global $p;

        $optionTmplName = Common::getTmplName();
        if ($isPage === null) {
            $isPage = self::isPage();
        }

        $module = "{$optionTmplName}_general_settings";
        if ($p == 'index.php') {
            $module = "{$optionTmplName}_main_page_settings";
        }

        $typeOrder = self::getOptionGroup('display_type', $isPage, $module);

        return $typeOrder;
    }


    static public function getWhereTags($table = '', $tags = null) {
        if ($tags === null) {
            $tags = trim(get_param('tags'));
        }

        if (!$tags) {
            return '';
        }

        $tags =  explode(',', trim($tags));
        if (!is_array($tags)) {
            return '';
        }

        $whereSql = 'no_tags';
        $where = '';
        $i = 0;
        foreach ($tags as $k => $tag) {
            $tag = trim($tag);
            if ($tag) {
                if ($i) {
                   $where .= ' OR ';
                }
                $where .= '`tag` LIKE "%' . DB::esc_like($tag) . '%"';
            }
            $i++;
        }
        if ($where) {
            $sql = "SELECT `id` FROM `" . self::$tbTags . "` WHERE ({$where})";
            $tagsId = DB::rows($sql);
            $tags = array();
            if ($tagsId) {
                foreach ($tagsId as $k => $tag) {
                    $tags[] = $tag['id'];
                }
                $whereSql = implode(',', $tags);
                $whereSql = " AND {$table}tag_id IN({$whereSql})";
            }
        }

        return $whereSql;
    }
}