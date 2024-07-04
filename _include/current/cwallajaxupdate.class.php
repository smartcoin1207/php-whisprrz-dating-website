<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CWallAjaxUpdate extends CWallAjax {

    function itemAddToArray($item, &$array, $prf = '')
    {
        $item = intval($item);
        if ($item > 0) {
            $array[] = $item . $prf;
        }
    }

    function getId($id)
    {
        $search  = array('_p', '_v');
        $replace = array('', '');
        return str_replace($search, $replace, $id);
    }

    function prepareCommentArray($array)
    {
        if (!$array) {
            return array();
        }

        $result = array();
        $search  = array('_p', '_v');
        $replace = array('', '');
        foreach ($array as $key => $value) {
            $k = str_replace($search, $replace, $key);
            $result[$k] = array();
            foreach ($value as $key1 => $value1) {
                $k1 = str_replace($search, $replace, $key1);
                $v = str_replace($search, $replace, $value1);
                $result[$k][$k1] = $v;
            }
        }
        return $result;
    }

    function getCommentParentId($commentsReplies, $rcid)
    {
        foreach ($commentsReplies as $key => $value) {
            if (isset($value[$rcid])) {
                return $key;
                break;
            }
        }
        return 0;
    }

    function parseBlockEdge(&$html)
    {

        global $p;
        global $g_user;

        $cmd = get_param('cmd');
        if ($cmd != 'update') {
            return;
        }

        $guid = guid();
        $id = get_param_int('id');
        $uid = get_param_int('wall_uid', $guid);
        Wall::setUid($uid);

        $city_id = $g_user['city_id'];

        $userinfo_sql = "SELECT * FROM userinfo WHERE user_id = " . to_sql($guid) . "LIMIT 1";
        $userinfo = DB::row($userinfo_sql);

        $wall_distance_filter = isset($userinfo['wall_distance_filter']) ? $userinfo['wall_distance_filter'] : '';

        $groupId = Groups::getParamId();
        Wall::setGroupId($groupId);

        $this->isAuthOnly($cmd);

        if ($html->varExists('is_friend')) {
            $html->setvar('is_friend', intval($uid == $guid || User::isFriend ($uid, $guid)));
        }

        $items = jsonDecodeParamArray('items');
        if (!$items || !Common::isValidArray($items)) {
            return;
        }

        $itemsPrepared = array();
        $itemsKeys = array_keys($items);
        foreach ($itemsKeys as $itemKey) {
            $this->itemAddToArray($itemKey, $itemsPrepared);
        }

        /* Friends where */
        //$where = '';
        $whereSingleItem = '';
        $oneOnly = get_param_int('single_item');
        if ($oneOnly) {
            $whereSingleItem = ' AND w.id = ' . intval($oneOnly);
        }
        $where = $whereSingleItem;

        $groupsSubscribersListWallUid = 0;

        if ($groupId) {
            $where .= ' AND w.group_id = ' . to_sql($groupId);
        } else {
            $grupsSubscribersList = Groups::getUserGroupsSubscribers($uid);

            $groupsSubscribersListWallUid = Groups::getUserGroupsSubscribers($uid);
            $whereSubscribers = '';
            if ($groupsSubscribersListWallUid) {
                $whereSubscribers = ' OR w.group_id IN (' . $groupsSubscribersListWallUid . ')';
            }
            $where .= ' AND (w.group_id = 0 OR (w.group_id != 0 AND w.section = "share") ' . $whereSubscribers . ') ';
            $where .= ' AND w.group_id = 0 ';
        }

        $isProfileWall = intval(get_param('is_profile_wall'));
        $guidSql = to_sql($guid, 'Number');
        $itemsUpdate = array();
        $itemsPreparedWhere = implode(',', $itemsPrepared);
        if ($itemsPreparedWhere) {

            $item_param = get_param('single_item', '');

            $whereFriendsProfile = ' OR (w.access = "profile" AND (w.user_id = ' . $guid . ' OR w.item_user_id = '. $guid .'))';

            if($isProfileWall == 1) {
                $whereFriendsProfile = ' OR (w.access = "profile")';
                        if($groupId)
                            $whereFriendsProfile .= ' OR (w.access = "group")';
            }
            else if(($isProfileWall == 0) && $item_param) {
                        $whereFriendsProfile = ' OR (w.access = "profile") OR (w.access = "group")';

            } else if($isProfileWall == 0) {
                $whereFriendsProfile = '';
            }

            $whereFriends = '';
            if ($groupId) {
                $isSubscribers = Groups::isSubscribeOrCreatedUser($guid, $groupId);
                $whereSubscribers = '';
                if ($isSubscribers) {
                    $whereSubscribers = ' OR w.access = "friends"';
                }
                $whereFriends = '(w.access = "public"' .
                                 $whereSubscribers .
                            ' OR (w.access = "private" AND ((w.comment_user_id = 0 AND (w.user_id = ' . $guid . ' OR w.item_user_id = '. $guid .')) OR (w.comment_user_id != 0 AND w.comment_user_id = ' . $guid . ')))' . $whereFriendsProfile . ')';

            } else {

                $frd = User::getFriendsList(guid(), true);
                $whereFriends = '(w.access = "public"
                              OR (w.access = "friends" AND ((w.comment_user_id = 0 AND ( w.user_id IN (' . $frd . ') OR w.item_user_id IN ('. $guid .'))) OR (w.comment_user_id != 0 AND w.comment_user_id IN (' . $frd . '))))
                              OR (w.access = "private" AND ((w.comment_user_id = 0 AND (w.user_id = ' . $guid . ' OR w.item_user_id = '. $guid .')) OR (w.comment_user_id != 0 AND w.comment_user_id = ' . $guid . ')))' .$whereFriendsProfile. ')';

                $whereFriends = '(w.group_id = 0 AND ' . $whereFriends . ')';

                if ($guid == $uid) {
                    $groupsSubscribersList = Groups::getUserGroupsSubscribers($guid);
                } else {
                    $groupsSubscribersList = Groups::getUserGroupsSubscribers($guid, true);
                }

                $whereSubscribers = '';
                if ($groupsSubscribersList) {
                    $whereSubscribers = ' OR (w.access = "friends" AND w.group_id IN(' . $groupsSubscribersList . ')) ';
                }

                $whereSubscribers = '(w.access = "public" ' .
                                    $whereSubscribers .
                            '     OR (w.access = "private" AND ((w.comment_user_id = 0 AND (w.user_id = ' . $guid . ' OR w.item_user_id = '. $guid .')) OR (w.comment_user_id != 0 AND w.comment_user_id = ' . $guid . ')))'.$whereFriendsProfile.')';
                $whereSubscribers = '(w.group_id != 0 AND ' . $whereSubscribers . ')';

                $whereFriends = '((' . $whereFriends . ') OR (' . $whereSubscribers . '))';
            }

            if ($whereFriends) {
                $whereFriends = ' AND ' . $whereFriends;
            }

            if ($isProfileWall) {
                $whereUser = ' AND ( w.user_id = ' . to_sql($uid, 'Number') . ' OR w.item_user_id = ' . to_sql($uid, 'Number') . ')';

                if (!$groupId && $groupsSubscribersListWallUid) {

                    $whereUser = ' AND (
                                    ((w.user_id = ' . to_sql($uid, 'Number') . ' OR w.item_user_id = '. to_sql($uid, 'Number') . ') AND w.group_id = 0)

                                      OR (
                                           w.group_id != 0 AND
                                           (
                                                ((w.comment_user_id = ' . to_sql($uid) . ' OR (w.user_id = ' . to_sql($uid) . ' OR w.item_user_id = ' . to_sql($uid) . '))'
                                                . ' AND  w.group_id IN(' . $groupsSubscribersListWallUid . '))
                                             OR (w.section = "share"  AND  (w.user_id = ' . to_sql($uid) . ' OR w.item_user_id = ' . to_sql($uid) . '))
                                            )
                                          )
                                    ) ';
                }

                if($groupId) {
                    $where = $whereFriends .  $where;
                } else {
                    $where = $whereUser . $whereFriends .  $where;
                }

                $fromAdd = '';
                if (Common::isOptionActive('contact_blocking')) {
                    $fromAdd = ' LEFT JOIN groups_user_block_list AS ugbl ON (ugbl.user_id = ' . $guidSql . ' AND ugbl.group_id = w.group_id AND w.group_id != 0) ';
                    $where .= ' AND ugbl.id IS NULL ';
                }

                if(Wall::isEHPWall()) {
                    $site_section_item_id_ehp = get_session('site_section_item_id_ehp');
    
                    $ehp_site_section = Wall::getSiteSectionEHP();
                    // $whereEHPSql = " w.site_section_item_id = " . to_sql($site_section_item_id_ehp, 'Number') . " AND w.site_section = " . to_sql($ehp_site_section, 'Text') . " ";

                    $whereEHPSql = ' AND w.site_section_item_id=' . to_sql($site_section_item_id_ehp, 'Number') . ' AND w.site_section=' . to_sql($ehp_site_section, 'Text');
                    $where = $whereEHPSql;
                }

                $sql = 'SELECT w.*
                          FROM `wall` AS w ' . $fromAdd .  '
                         WHERE w.id IN (' . $itemsPreparedWhere . ')' . $where;
                $itemsUpdate = DB::all($sql);
            } else {
                $sqlJoinBlocked = '';
                if (Common::isOptionActive('contact_blocking')) {
                    $sqlJoinBlocked = 'LEFT JOIN user_block_list AS ubl1 ON (ubl1.user_to = u.user_id AND ubl1.user_from = ' . $guidSql . ' AND w.group_id = 0)
                                       LEFT JOIN user_block_list AS ubl2 ON (ubl2.user_from = u.user_id AND ubl2.user_to = ' . $guidSql . ' AND w.group_id = 0) ' .
                                      'LEFT JOIN groups_user_block_list AS ugbl ON (ugbl.user_id = ' . $guidSql . ' AND ugbl.group_id = w.group_id AND w.group_id != 0) ';
                    /*$where .= ' AND (u.user_id = ' . $guidSql .  ' OR (ubl1.id IS NULL AND ubl2.id IS NULL))
                                AND ((ucom1.user_id = ' . $guidSql . ' OR (ubl1.id IS NULL AND ubl2.id IS NULL))
                                  OR (ucom2.user_id = ' . $guidSql . ' OR (ubl1.id IS NULL AND ubl2.id IS NULL)))';*/

                    $where .= 'AND ubl1.id IS NULL AND ubl2.id IS NULL AND ugbl.id IS NULL ';
                }

                $whereUser = '';
                if ($groupsSubscribersListWallUid) {
                    $whereUser = ' AND (w.group_id = 0 OR w.group_id IN(' . $groupsSubscribersListWallUid . ')) ';
                }

                $from_wall_distance_add = "";

                if($groupId) {
                    $where = $whereFriends . $where;
                } else {
                    $whereDistanceSql = "";
                    if($wall_distance_filter) {
                        $from_wall_distance_add = " LEFT JOIN geo_city AS gc ON gc.city_id = u.city_id";
                        $whereDistanceSql = inradius($city_id, $wall_distance_filter);
                    }
                    $where = $whereFriends . $whereUser . $where . $whereDistanceSql;
                }

                if(Wall::isEHPWall()) {
                    $site_section_item_id_ehp = get_session('site_section_item_id_ehp');
    
                    $ehp_site_section = Wall::getSiteSectionEHP();
                    $whereEHPSql = ' w.site_section_item_id = ' . to_sql($site_section_item_id_ehp, 'Number') . ' AND w.site_section = ' . to_sql($ehp_site_section, 'Text');
                    $where = $whereEHPSql;
                }

                $sql = 'SELECT w.*
                          FROM `wall` AS w
                          LEFT JOIN user AS u ON u.user_id = w.user_id
                          LEFT JOIN userinfo AS i ON u.user_id = i.user_id ' .
                          $sqlJoinBlocked .
                          $from_wall_distance_add . 
                        ' LEFT JOIN user AS ucom1 ON ucom1.user_id = w.comment_user_id
                          LEFT JOIN user AS ucom2 ON w.comment_user_id = 0 AND ucom2.user_id = w.user_id
                         WHERE w.id IN (' . $itemsPreparedWhere . ')' . $where;
                $itemsUpdate = DB::all($sql);
            }
        }

        /* Prepare data update */
        $itemsExists = array();
        $itemsExistsInfoAll = array();
        $itemsExistsInfo = array();//Update info posts
        $likeUpdated = array();    //Update likes
        $commentUpdated = array(); //Update comments
        $commentLikeUpdated = array(); //Update count like comments

        if ($itemsUpdate) {
            foreach ($itemsUpdate as $k1 => $row) {
                $itemsExists[] = $row['id'];
                $itemsExistsInfoAll[$row['id']] = $row;
                //if (!isset($items[$row['id']])) {
                    //continue;
                //}

                $likesAction = $row['last_action_like'];
                $likes = $row['likes'];
                if (($row['section'] == 'vids' || $row['section'] == 'photo') && $row['item_id']) {
                    $likes = $row['likes_media'];
                    $likesAction = $row['last_action_like_media'];
                    $row['likes'] = $likes;
                    $row['last_action_like'] = $likesAction;
                }
                if ($likesAction != $items[$row['id']]['like']) {
                    $likeUpdated[$row['id']] = array('date_like' => $likesAction,
                                                     'count' => $likes,
                                                     'section' => $row['section'],
                                                     'item_id' => $row['item_id']);
                    $itemsExistsInfo[$row['id']] = $row;
                }
                if ($row['last_action_comment'] != $items[$row['id']]['comment']) {
                    $commentUpdated[$row['id']] = array('section' => $row['section'],
                                                        'item_id' => $row['item_id'],
                                                        'date_comment' => $row['last_action_comment'],
                                                        'count' => $row['comments_item']
                                              );
                    $itemsExistsInfo[$row['id']] = $row;
                }
                if ($row['last_action_comment_like'] != $items[$row['id']]['actionLikeComment']) {
                    $commentLikeUpdated[$row['id']] = array('section' => $items[$row['id']]['section'],
                                                            'item_id' => $row['item_id'],
                                                            'dt1' => $items[$row['id']]['actionLikeComment'],
                                                            'dt2' => $row['last_action_comment_like']);
                }
                if ($row['section'] != 'share' && $row['last_action_shares'] != $items[$row['id']]['shares']) {
                    $itemsExistsInfo[$row['id']] = $row;
                    $itemsExistsInfo[$row['id']]['my_shared'] = Wall::isShared($row['id']);
                }

                if($row['section'] == 'share') {
                    $share_comment = $row['share_comment'];
                    
                    if($share_comment) {
                        $html->setvar('wall_share_comment', $share_comment);
                        $html->parse('wall_share_comment', false);
                    }
                }
            }
        }
        /* Prepare data update */

        /* Delete no exists posts */
        $itemsNotExists = array_diff($itemsPrepared, $itemsExists);
        if ($itemsNotExists) {
            foreach ($itemsNotExists as $itemNotExists) {
                $html->setvar('wall_item_id_delete', $itemNotExists);
                $html->parse('wall_item_delete_script');
            }
            $html->parse('wall_items_delete_script');
        }
        /* Delete no exists posts */

        /* Update like for exists posts */
        if ($likeUpdated) {
            foreach ($likeUpdated as $key => $value) {
                $html->setvar('id', $key);
                $html->setvar('wall_last_action_like', $value['date_like']);
                if ($value['section'] == 'vids' || ($value['section'] == 'photo' && $value['item_id'])) {
                    $type = $value['section'] == 'vids' ? 'video' : 'photo';
                    $mediaData = CProfilePhoto::getLikeWallData($type, $value['item_id']);
                    CProfilePhoto::parseLikeWallData($html, $mediaData);
                }
                if ($value['count'] > 0) {
                    Wall::parseLikes($html, $key, $value['count']);
                } else {
                    $html->setvar('wall_like_user_list', '');
                    $html->parse('wall_item_like');
                }
            }
        }
        /* Update like for exists posts */

        /* Update info exists post */
        if ($itemsExistsInfo) {
            foreach ($itemsExistsInfo as $itemExistsInfo) {
                $html->setvar('id', $itemExistsInfo['id']);
                $html->setvar('wall_last_action_like', $itemExistsInfo['last_action_like']);
                $html->setvar('wall_last_action_comment', $itemExistsInfo['last_action_comment']);
                $html->setvar('wall_comments_count', $itemExistsInfo['comments_item']);
                $html->setvar('wall_last_action_shares', $itemExistsInfo['last_action_shares']);
                $html->setvar('wall_item_shares_count', intval($itemExistsInfo['shares_count']));
                $html->setvar('wall_item_my_shared', isset($itemExistsInfo['my_shared']) ? $itemExistsInfo['my_shared'] : 0);

                $html->setvar('wall_item_comment_is_viewed', Wall::isCommentViewed($itemExistsInfo['id'], guid()));
                $html->parse('wall_item_info');
            }
            $html->parse('wall_item_info_script');
        }
        /* Update info exists post */

        /* Update like from comment */
        $commentLikeUpdatedResponse = array();
        foreach ($commentLikeUpdated as $key => $data) {
           //var_dump_pre($date['section'], true);
            if ($data['section'] == 'photo') {
                $tableSql = 'photo_comments';
                $tableLikesSql = 'photo_comments_likes';
                $where = "photo_id = " . to_sql($data['item_id']);
            } elseif ($data['section'] == 'vids') {
                $tableSql = 'vids_comment';
                $tableLikesSql = 'vids_comments_likes';
                $where = "video_id = " . to_sql($data['item_id']);
            } else {
                $tableSql = 'wall_comments';
                $tableLikesSql = 'wall_comments_likes';
                $where = "wall_item_id = " . to_sql($key);
            }
            $sql = "SELECT `id`, `parent_id`, `likes` FROM `" . $tableSql . "`
                     WHERE " . $where .
                     " AND `last_action_like` > STR_TO_DATE(" . to_sql($data['dt1']) . ", '%Y-%m-%d %H:%i:%s')" .
                     " AND `last_action_like` <= STR_TO_DATE(" . to_sql($data['dt2']) . ", '%Y-%m-%d %H:%i:%s')";
            $commentsLikes = DB::rows($sql);
            foreach ($commentsLikes as $key1 => $data1) {
                $sql = "SELECT `id` FROM {$tableLikesSql} WHERE `user_id` = " . to_sql($guid) . " AND `cid` = "  . to_sql($data1['id']);
                $commentsLikes[$key1]['my_like'] = DB::result($sql, 0, DB_MAX_INDEX);
                $commentsLikes[$key1]['likes_users'] = User::getTitleLikeUsersComment($data1['id'], $data1['likes'], $data['section']);
            }
            $commentLikeUpdatedResponse[$key] = array('actionLikeComment' => $data['dt2'],
                                                      'section' => $data['section'],
                                                      'items' => $commentsLikes);
        }
        if ($commentLikeUpdatedResponse) {
            $html->setvar('wall_update_like_comments', json_encode($commentLikeUpdatedResponse));
            $html->parse('wall_update_like_comments');
        }
        /* Update like from comment */

        $commentsAll = jsonDecodeParamArray('comments_all');
        $comments = jsonDecodeParamArray('comments');
        $commentsReplies = jsonDecodeParamArray('comments_replies');

        //$commentsAll = $this->prepareCommentArray($commentsAll);
        //print_r_pre($commentsAll);
        //$comments = $this->prepareCommentArray($comments);
        //print_r_pre($comments);
        //$commentsReplies = $this->prepareCommentArray($commentsReplies);
        //print_r_pre($commentsReplies);

        $commentsExistsPosts = array();
        $commentsExistsPhoto = array();
        $commentsExistsVideo = array();
        if ($commentUpdated) {
            foreach ($commentUpdated as $key => $value) {
                $html->setvar('id', $key);
                $html->setvar('wall_last_action_comment', $value['date_comment']);
                $html->setvar('wall_comments_count', $value['count']);


                // check comments in this item that exists on site
                $commentsOfItem = isset($commentsAll[$key]) ? $commentsAll[$key] : false;
                if ($commentsOfItem) {
                    $commentsPrepared = array();
                    $commentsPreparedPhoto = array();
                    $commentsPreparedVideo = array();
                    foreach ($commentsOfItem as $commentOfItem => $valueOfItem) {
                        if ($value['section'] == 'photo' && $value['item_id']) {
                            $this->itemAddToArray($valueOfItem, $commentsPreparedPhoto);
                        } elseif ($value['section'] == 'vids') {
                            $this->itemAddToArray($valueOfItem, $commentsPreparedVideo);
                        } else {
                            $this->itemAddToArray($valueOfItem, $commentsPrepared);
                        }
                    }

                    $data = array('wall'  => $commentsPrepared,
                                  'photo' => $commentsPreparedPhoto,
                                  'video' => $commentsPreparedVideo);

                    foreach ($data as $k => $v) {
                        if (!$v) continue;
                        $commentsPreparedSql = implode(',', $v);
                        $prf = Wall::getPrfMediaId($k);
                        if ($k == 'photo' || $k == 'video') {
                            $table = $k == 'photo' ? 'photo_comments' : 'vids_comment';
                            $sql = "SELECT * FROM `{$table}`
                                     WHERE id IN (" . $commentsPreparedSql . ')';
                        } else {
                            $sql = 'SELECT * FROM wall_comments
                                     WHERE wall_item_id = ' . to_sql($key) . '
                                       AND id IN (' . $commentsPreparedSql . ')';
                        }
                        DB::query($sql);

                        $commentsExists = array();
                        $blockCommentsRepliesCount = 'wall_item_comments_replies_count';
                        while ($row = DB::fetch_row()) {
                            $commentsExists[] = $row['id'];
                            if ($row['parent_id']) {
                                continue;
                            }
                            if ($k == 'photo') {
                                if (!isset($commentsExistsPhoto[$key])) {
                                    $commentsExistsPhoto[$key] = array();
                                }
                                $commentsExistsPhoto[$key][] = $row['id'];
                            } elseif ($k == 'video') {
                                if (!isset($commentsExistsVideo[$key])) {
                                    $commentsExistsVideo[$key] = array();
                                }
                                $commentsExistsVideo[$key][] = $row['id'];
                            } else {
                                if (!isset($commentsExistsPosts[$key])) {
                                    $commentsExistsPosts[$key] = array();
                                }
                                $commentsExistsPosts[$key][] = $row['id'];
                            }
                            /* Count replies comments */
                            $html->setvar("{$blockCommentsRepliesCount}_cid", $row['id'] . $prf);
                            $html->setvar("{$blockCommentsRepliesCount}_count", $row['replies']);
                            $html->parse($blockCommentsRepliesCount, true);
                            /* Count replies comments */
                        }
                        $commentsNotExists = array_diff($v, $commentsExists);
                     
                        if ($commentsNotExists) {
                            foreach ($commentsNotExists as $k => $commentNotExists) {
                                $cid = $this->getCommentParentId($commentsReplies, $commentNotExists . $prf);
                                if ($cid) {
                                    $cid = $this->getId($cid);
                                    if (array_search($cid, $commentsNotExists) !== false) {
                                        unset($commentsNotExists[$k]);
                                    }
                                }
                            }

                            foreach ($commentsNotExists as $commentNotExists) {
                                $cid = $commentNotExists;
                                $rcid = 0;
                                if (!isset($commentsReplies[$commentNotExists . $prf])) {
                                    $rcid = $cid;
                                    $cid = $this->getCommentParentId($commentsReplies, $cid . $prf);
                                    //var_dump_pre($commentsReplies[Wall::addPrfMediaId($cid, $prf)]);
                                    //var_dump_pre(Wall::addPrfMediaId($rcid, $prf));
                                    unset($commentsReplies[Wall::addPrfMediaId($cid, $prf)][Wall::addPrfMediaId($rcid, $prf)]);
                                }
                                //echo $cid . '/' . $rcid;
                                $html->setvar('id', $key);
                                $html->setvar('wall_item_comment_id_delete', Wall::addPrfMediaId($cid, $prf));
                                $html->setvar('wall_item_comment_reply_id_delete', Wall::addPrfMediaId($rcid, $prf));
                                $html->parse('wall_item_comment_delete_script');

                                unset($commentsAll[$key][$commentNotExists . $prf]);
                                unset($comments[$key][$commentNotExists . $prf]);

                            }
                            $html->parse('wall_item_comments_delete_script');
                        }
                    }
                }
            }
        }

        if ($itemsExists) {
            $commentsReplyLastId = jsonDecodeParamArray('comments_reply_last');
            $itemsCommentFirstId = jsonDecodeParamArray('comments_first');
            /*
             * 1) New comments in the beginning
             *
             */
            //print_r_pre($commentsReplyLastId);
            //print_r_pre($commentsExistsPhoto);
            //return;
            $limit = Wall::getCommentsPreloadCount();
            $limitReplies = Wall::getNumberShowCommentsReplies();
            //print_r_pre($commentsAll);
            $parse = false;
            foreach ($itemsExists as $itemExists) {
                $lastId = 0;
                if (isset($itemsCommentFirstId[$itemExists])) {
                    $lastId = $itemsCommentFirstId[$itemExists];
                }
                $parseComments = Wall::parseComments($html, $itemExists, 1, 0, '', 0, ' AND c.id > ' . to_sql($lastId, 'Number'));

                /* If you delete a comment, you need to add up to the limit */
                $countExistsComments = 0;
                if (isset($comments[$itemExists])) {
                    $countExistsComments = count($comments[$itemExists]);
                }
                $countExistsComments += count($parseComments);
                //var_dump_pre($parseComments);
                //var_dump_pre($countExistsComments);
                if (!isset($comments[$itemExists]) || $countExistsComments < $limit) {
                    Wall::$commentCustomClass = 'comment_attach';
                    $infoSection = $itemsExistsInfoAll[$itemExists];
                    $section = $infoSection['section'];
                    $prf = Wall::getPrfMediaId($section);
                    $idItem = $itemExists;
                    if ($section == 'photo' || $section == 'vids') {
                        $idItem = $infoSection['item_id'];
                    }
                    $sql = $this->getSqlComments($section, $idItem, 0);
                    $sql .= ' ORDER BY `id` DESC LIMIT ' . $limit;
                    DB::query($sql, 8);
                    if (DB::num_rows(8)) {
                        while ($commentAttach = DB::fetch_row(8)) {
                            if (!isset($comments[$itemExists][$commentAttach['id'] . $prf]) && !isset($parseComments[$commentAttach['id']])) {
                                Wall::parseComments($html, $itemExists, 1, 0, '', $commentAttach['id']);
                                //break;
                            }
                        }
                    }
                    Wall::$commentCustomClass = '';
                }
                /* If you delete a comment, you need to add up to the limit */

                /* Update replies comments */
                if (isset($commentUpdated[$itemExists])) {
                    $section = $itemsExistsInfo[$itemExists]['section'];
                    $prf = Wall::getPrfMediaId($section);
                    $commentsExistsData = $commentsExistsPosts;
                    if ($section == 'photo') {
                        $commentsExistsData = $commentsExistsPhoto;
                    } elseif ($section == 'vids') {
                        $commentsExistsData = $commentsExistsVideo;
                    }
                    if (isset($commentsExistsData[$itemExists])) {
                        //$html->parse('comments_reply_start',  false);
                        foreach ($commentsExistsData[$itemExists] as $k => $commentExists) {
                            $idPrf = $commentExists . $prf;
                            $lastId = 0;
                            if (isset($commentsReplyLastId[$idPrf])) {
                                $lastId = $commentsReplyLastId[$idPrf];
                            }
                            $paramLastId = get_param('last_id');
                            $_GET['last_id'] = $lastId;
                            Wall::$commentCustomClass = 'comment_attach_reply';
                            Wall::$commentReplyCustomClass = 'comment_attach_reply_one';
                            Wall::$commentsReplyParse = array();
                            Wall::parseComments($html, $itemExists, 1, 0, 0, $commentExists);
                            Wall::$commentReplyCustomClass = '';
                            Wall::$commentCustomClass = '';
                            $_GET['last_id'] = $paramLastId;

                            $countExistsComments = 0;
                            if (isset($commentsReplies[$idPrf])) {
                                $countExistsComments = count($commentsReplies[$idPrf]);
                            }
                            $countExistsComments += count(Wall::$commentsReplyParse);
                            //var_dump_pre($countExistsComments);
                            if (!isset($commentsReplies[$idPrf]) || $countExistsComments < $limitReplies) {
                                Wall::$commentCustomClass = 'comment_attach_reply_add';
                                Wall::$commentReplyCustomClass = 'comment_attach_reply_one_add';
                                Wall::parseComments($html, $itemExists, 1, 0, 0, $commentExists);
                                Wall::$commentReplyCustomClass = '';
                                Wall::$commentCustomClass = '';
                                //var_dump_pre($commentExists);
                            }
                        }
                        //$html->parse('comments_reply_end', false);
                    }
                }
                /* Update replies comments */
            }
        }

        if ($html->blockExists('update_counter_posts')) {
            $count = Wall::getCountItems();
            $html->setvar('counter_posts', $count);
            $html->parse('update_counter_posts', false);
        }

    }

    function getSqlComments($section, $id, $parentId = 0)
    {
        if ($section == 'photo') {
            $sql = 'SELECT `id`
                      FROM `photo_comments`
                     WHERE `photo_id` = ' . to_sql($id, 'Number') . '
                       AND `parent_id` = ' . to_sql($parentId);
        } elseif ($section == 'vids') {
            $sql = 'SELECT `id`
                      FROM `vids_comment`
                     WHERE `video_id` = ' . to_sql($id, 'Number') . '
                       AND `parent_id` = ' . to_sql($parentId);
        } else {
            $sql = 'SELECT `id`
                      FROM `wall_comments`
                     WHERE `wall_item_id` = ' . to_sql($id, 'Number') . '
                       AND `parent_id` = ' . to_sql($parentId);
        }
        return $sql;
    }

    function parseBlock(&$html)
    {

        $tmplWallType = Common::getOptionTemplate('wall_type');
        if ($tmplWallType == 'edge') {
            $this->parseBlockEdge($html);
            parent::parseBlock($html);
            return;
        }

        $cmd = get_param('cmd');
        $id = get_param('id');
        $guid = guid();
        $uid = get_param('wall_uid', $guid);

        Wall::setUid($uid);

        $this->isAuthOnly($cmd);

        if ($html->varExists('is_friend')) {
            $html->setvar('is_friend', intval($uid == $guid || User::isFriend ($uid, $guid)));
        }

        if ($cmd == 'update') {
            $optionTemplateSet = Common::getOption('set', 'template_options');

            // load new items
            $items = get_param_array('items');
            $comments = get_param_array('comments');

            // check if items still exists
            // check last action time
            if (Common::isValidArray($items)) {
                $itemsPrepared = array();
                $itemsKeys = array_keys($items);
                foreach ($itemsKeys as $itemKey) {
                    $this->itemAddToArray($itemKey, $itemsPrepared);
                }

                if (count($itemsPrepared)) {
                    $itemsPreparedWhere = implode(',', $itemsPrepared);
                    $where = '';
                    if ($optionTemplateSet == 'urban') {
                        $isProfileWall = intval(get_param('is_profile_wall'));
                        $typeWall = get_param('type_wall');
                        if (!$isProfileWall && $typeWall != 'all') {
                            $uids = User::getFriendsList($uid, true);
                            $where = " AND (user_id IN({$uids}) OR comment_user_id IN({$uids}))";
                        }
                    }
                    $sql = 'SELECT *
                              FROM `wall`
                             WHERE `id` IN (' . $itemsPreparedWhere . ')' . $where;
                    DB::query($sql);

                    $itemsExists = array();
                    $itemsExistsInfo = array();
                    $likeUpdated = array();
                    $commentUpdated = array();

                    // update like/comment info
                    while ($row = DB::fetch_row()) {

                        $itemsExists[] = $row['id'];
                        if ($row['last_action_like'] != $items[$row['id']]['like']) {
                            $likeUpdated[$row['id']] = array('date_like' => $row['last_action_like'], 'date_comment' => $row['last_action_comment'], 'count' => $row['likes']);
                            $itemsExistsInfo[$row['id']] = $row;
                        }
                        if ($row['last_action_comment'] != $items[$row['id']]['comment']) {
                            $commentUpdated[$row['id']] = array('date_like' => $row['last_action_like'], 'date_comment' => $row['last_action_comment'], 'count' => $row['comments']);
                            $itemsExistsInfo[$row['id']] = $row;
                        }
                    }

                    $itemsNotExists = array_diff($itemsPrepared, $itemsExists);

                    if (count($itemsNotExists)) {
                        foreach ($itemsNotExists as $itemNotExists) {
                            $html->setvar('wall_item_id_delete', $itemNotExists);
                            $html->parse('wall_item_delete_script');
                        }
                        $html->parse('wall_items_delete_script');
                    }

                    // update info for exists items
                    //
                    // update info for exists items

                    if (count($likeUpdated)) {
                        foreach ($likeUpdated as $key => $value) {
                            $html->setvar('wall_last_action_like', $value['date_like']);
                            $html->setvar('wall_last_action_comment', $value['date_comment']);
                            $html->setvar('id', $key);
                            if ($value['count'] > 0) {
                                Wall::parseLikes($html, $key, $value['count']);
                            } elseif ($optionTemplateSet == 'urban') {
                                $html->setvar('wall_like_user_list', '');
                                $html->setvar('wall_like_user_info_list', json_encode(array()));
                                $html->parse('wall_item_like');
                            } else {
                                $html->parse('wall_item_like_hide');
                            }
                        }
                    }

                    if (count($commentUpdated)) {
                        foreach ($commentUpdated as $key => $value) {
                            $html->setvar('wall_last_action_like', $value['date_like']);
                            $html->setvar('wall_last_action_comment', $value['date_comment']);
                            $html->setvar('wall_comments_count', $value['count']);
                            $html->setvar('id', $key);

                            // check comments in this item that exists on site
                            $commentsOfItem = isset($comments[$key]) ? $comments[$key] : false;
                            if ($commentsOfItem) {

                                $commentsPrepared = array();
                                foreach ($commentsOfItem as $commentOfItem => $valueOfItem) {
                                    $this->itemAddToArray($valueOfItem, $commentsPrepared);
                                }

                                if (count($commentsPrepared)) {
                                    $commentsPreparedWhere = implode(',', $commentsPrepared);

                                    $sql = 'SELECT * FROM wall_comments
                                    WHERE wall_item_id = ' . to_sql($key) . '
                                        AND id IN (' . $commentsPreparedWhere . ')';
                                    DB::query($sql);

                                    $commentsExists = array();

                                    while ($row = DB::fetch_row()) {
                                        $commentsExists[] = $row['id'];
                                    }

                                    $commentsNotExists = array_diff($commentsPrepared, $commentsExists);

                                    if (count($commentsNotExists)) {
                                        foreach ($commentsNotExists as $commentNotExists) {
                                            unset($comments[$key][$commentNotExists]);
                                            $html->setvar('id', $key);
                                            $html->setvar('wall_item_comment_id_delete', $commentNotExists);
                                            $html->parse('wall_item_comment_delete_script');
                                        }
                                        $html->parse('wall_item_comments_delete_script');
                                    }
                                }
                            }
                        }
                    }

                    if (count($itemsExists)) {
                        // load new comments to items
                        // 1) only to page exists items
                        // 2) only if item has less 3 exists comments - then we need to add new comments on page
                        $onePostId = get_param('one_post_id');
                        $limit = Wall::getCommentsPreloadCount();
                        foreach ($itemsExists as $itemExists) {
                            if ($optionTemplateSet == 'urban' && (!$onePostId || $itemExists != $onePostId)) {
                                continue;
                            }
                            if (!isset($comments[$itemExists]) || count($comments[$itemExists]) < $limit) {
                                $order = 'ASC';
                                if ($optionTemplateSet == 'urban') {
                                    $order = 'DESC';
                                }
                                $sql = 'SELECT c.*, u.*, c.user_id AS comment_user_id,
                                w.user_id AS wall_user_id, w.comments
                                FROM wall_comments AS c
                                LEFT JOIN user AS u ON u.user_id = c.user_id
                                JOIN wall AS w ON w.id = c.wall_item_id
                                WHERE wall_item_id = ' . to_sql($itemExists, 'Number') . '
                                    ORDER BY c.id ' . $order . ' LIMIT ' . $limit;
                                DB::query($sql);
                                while ($comment = DB::fetch_row()) {
                                    if (!isset($comments[$itemExists][$comment['id']])) {
                                        $html->setvar('id', $itemExists);
                                        $html->parse('wall_item_comment_attach_start');
                                        $html->parse('wall_item_comment_attach_end');
                                        Wall::parseComments($html, $itemExists, 1, 0, '', $comment['id']);
                                        //Wall::parseComments($html, $itemExists, 1, $limit);
                                        // parse special comment-copy script
                                        break;
                                    }
                                }
                            }
                            if ($onePostId){
                                Wall::parseComments($html, $itemExists, 1, 0, '', 0, ' AND c.id > ' . to_sql(get_param('last_comment_id'), 'Number'));
                            }
                        }
                    }

                    if (count($itemsExistsInfo)) {
                        foreach ($itemsExistsInfo as $itemExistsInfo) {
                            $html->setvar('id', $itemExistsInfo['id']);
                            $html->setvar('wall_last_action_like', $itemExistsInfo['last_action_like']);
                            $html->setvar('wall_last_action_comment', $itemExistsInfo['last_action_comment']);
                            $html->setvar('wall_comments_count', $itemExistsInfo['comments']);
                            $html->setvar('wall_item_comment_is_viewed', Wall::isCommentViewed($itemExistsInfo['id'], guid()));
                            $html->parse('wall_item_info');
                        }
                        $html->parse('wall_item_info_script');
                    }
                }
                if (Common::getOption('custom_wall_show', 'template_options')) {
                    $block = 'wall_interests';
                    $itemsInterestExists = array_diff($itemsPrepared, $itemsNotExists);
                    foreach ($itemsInterestExists as $id) {
                        if (!isset($items[$id])) {
                            continue;
                        }
                        $listInterest = trim($items[$id]['listInterests']);
                        if (!empty($listInterest)){
                            $isActionInterests = false;
                            $interestsItem = explode(',', $listInterest);
                            $interestsKey = array();
                            $sql = 'SELECT UI.*, I.category, I.interest AS title
                                      FROM `user_interests` AS UI,
                                           `interests` AS I
                                     WHERE UI.`wall_id` = ' . to_sql($id, 'Number') .
                                     ' AND I.`id` = UI.interest ORDER BY UI.`id` ASC' ;
                            $interests = DB::all($sql);

                            if ($interests) {
                                foreach ($interests as $interest) {
                                    $interestsKey[]=$interest['interest'];
                                }
                            }
                            $html->setvar('id', $id);
                            foreach ($interestsItem as $intId) {
                                if (!in_array($intId, $interestsKey)) {
                                    $html->setvar('int_id', $intId);
                                    $html->parse("{$block}_remove", true);
                                    $isActionInterests = true;
                                }
                            }

                            $guidInterestsAll = array();
                            $guidInterests = User::getInterests(guid());
                            foreach ($guidInterests as $item) {
                                $guidInterestsAll[$item['id']] = $item;
                                $guidInterestsAll[$item['id']]['main'] = 1;
                            }
                            foreach ($interests as $interest) {
                                if (!in_array($interest['interest'], $interestsItem)) {
                                    $html->setvar('int_id', $interest['interest']);
                                    $html->setvar('int_cat', $interest['category']);
                                    $html->setvar('int_title', he($interest['title']));
                                    if(isset($guidInterestsAll[$interest['interest']]) && $interest['user_id']!=guid()){
                                        $html->setvar('main_interest', 'interest_item');
                                    } else {
                                        $html->setvar('main_interest', '');
                                    }

                                    $html->parse("{$block}_show", true);
                                    $isActionInterests = true;
                                }
                            }
                            if ($isActionInterests) {
                                $html->setvar("{$block}_list", implode(',', $interestsKey));
                                $html->parse("{$block}_info", true);
                            }
                            if ($isActionInterests) {
                                $html->parse($block, true);
                            }
                        }
                    }

                }
            }
        }

        parent::parseBlock($html);
    }

}