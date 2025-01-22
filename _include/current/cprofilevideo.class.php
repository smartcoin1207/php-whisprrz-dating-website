<?php
class CProfileVideo
{
    static $isGetDataWithFilter = false;

    static function includePath()
    {
        return dirname(__FILE__) . '/../../';
    }

    static public function getTotalVideos($uid = null, $groupId = 0, $showAllMyVideo = false, $tab = 'public') // Divyesh - Added on 11-04-2024
    {

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
            $where = self::getWhereList('V.', $uid, $groupId, $showAllMyVideo, $tab); // Divyesh - Added on 11-04-2024
            $sql = 'SELECT COUNT(*) FROM (
                        SELECT COUNT(*)
                          FROM `vids_tags_relations` AS TR
                          JOIN `vids_video` AS V ON V.id = TR.video_id
                          LEFT JOIN `user` AS U ON U.user_id = V.user_id 
                         WHERE ' . $where
                                 . $whereTags
                     . ' GROUP BY V.id) AS VT';

            return DB::result($sql);
        } else {
            $where = self::getWhereList('V.', $uid, $groupId, $showAllMyVideo, $tab); // Divyesh - Added on 11-04-2024

            $sql = 'SELECT COUNT(*) FROM `vids_video` AS V JOIN `user` AS U ON V.user_id=U.user_id where ' . $where;
            $count = DB::result($sql);
            return $count;
        }
    }

    static function getWhereList($table = '', $uid = 0, $groupId = 0, $showAllMyVideo = false, $tab = 'public') // Divyesh - Added on 11-04-2024
    {
        global $g_user;
        $guid = guid();

        if($groupId) {
            $uid = 0;
        }

        $whereGroup = " AND {$table}group_id = " . to_sql($groupId);
        $groupsPhotoList = Groups::getTypeContentList();
        if ($groupsPhotoList) {
            if (!$uid) {
                $showAllMyVideo = true;
            }
            $whereGroup = " AND {$table}group_id != 0";
            if ($groupsPhotoList == 'group_page') {
                $whereGroup .= " AND {$table}group_page = 1";
            } else {
                $listGroups = Groups::getUserListGroupsSubscribers();
                $whereGroupPrivate = "({$table}group_private = 'Y' AND {$table}group_id IN (" . $listGroups . '))';
                $whereGroup .= " AND {$table}group_page = 0 AND ({$table}group_private = 'N' OR " . $whereGroupPrivate . ")";
            }
        }

        $vis = " AND {$table}active != '2' ";
        if (!$uid || ($uid && $uid != $guid)) {
            $whereApprove = '';
            if (Common::isOptionActive('video_approval')) {
                if(Common::isOptionActive('video_show_before_approval')) {
                    $whereApprove = "";
                } else {
                    $whereApprove = " {$table}active = '1' ";
                }
            }
            if ($whereApprove) {
                if ($showAllMyVideo) {
                    $whereApprove = "({$whereApprove} OR {$table}user_id = " . to_sql($guid) . ")";
                }
                $vis = ' AND ' . $whereApprove;
            }
        }

        $vis .= $whereGroup;

        //$vis .= " AND {$table}private = 0 ";//so far, only public albums
        $where = " {$table}is_uploaded = 1 AND {$table}active != '2' " . $vis ;

        /* Divyesh - Added on 11-04-2024 */ //Popcorn modified 2025-01-23
        if ($tab == 'private' || $tab == '2'){
            $where .= " AND {$table}`private` = 'Y' ";
        }else if($tab == true || $tab == 'public'){
            $where .= " AND {$table}`private` = 'N' ";
        }
        /* Divyesh - Added on 11-04-2024 */

        if ($uid) {
            $where .= " AND {$table}user_id = " . to_sql($uid);
        } elseif ($guid) {
            $isShowMyVideo = Common::isOptionActive('show_your_video_browse_videos', 'edge_member_settings');
            $onlyFriends = false;
            if (self::$isGetDataWithFilter) {
                $onlyFriends = get_param_int('only_friends', false);
                if ($onlyFriends) {
                    $friends = User::friendsList($guid, $isShowMyVideo);
                    if ($friends) {
                        $where .= " AND {$table}user_id IN ({$friends})";
                    }
                }
                $onlyLive = get_param_int('only_live', false);
                if ($onlyLive) {
                    $where .= " AND {$table}live_id != 0";
                }
            }
            if (!$onlyFriends && !$isShowMyVideo) {
                $where .= " AND {$table}user_id != " . to_sql($guid);
            }
        }
        if (!$uid) {
            $searchQuery = trim(get_param('search_query'));
            if ($searchQuery) {
                $searchQuery = urldecode($searchQuery);
                $where .= " AND {$table}subject  LIKE '%" . to_sql($searchQuery, 'Plain') . "%'";
            }
        }
        
        if($guid) {
            $where_orientation = " AND NOT (V.user_id!=".$guid." AND ((". $g_user['orientation'] ."=5 AND U.set_album_video_couples=2 ) OR ( ". $g_user['orientation'] ."=1 AND U.set_album_video_males=2 ) OR ( ". $g_user['orientation'] ."=2 AND U.set_album_video_females=2 ) OR ( ". $g_user['orientation'] ."=6 AND U.set_album_video_transgender=2 ) OR ( ". $g_user['orientation'] ."=7 AND U.set_album_video_nonbinary=2 ))) ";
            if(!$groupId) {
                $where =  $where . $where_orientation;
            }
        }

        return $where;
    }

    /*
     * All videos on the site except the current user
     */
    static function getOrderList($typeOrder = '')
    {
        $orderBy = 'V.dt DESC, V.id DESC';
        if ($typeOrder == 'order_most_commented') {
            $orderBy = 'V.count_comments DESC, V.id DESC';
        } elseif ($typeOrder == 'order_most_viewed') {
            $orderBy = 'V.count_views DESC, V.id DESC';
        } elseif ($typeOrder == 'order_random') {
            $orderBy = 'RAND()';
        }

        return $orderBy;
    }

    static public function getTags($id)
    {
        $sql = 'SELECT TR.tag_id, T.tag
                  FROM `vids_tags_relations` as TR
                  LEFT JOIN `vids_tags` as T ON TR.tag_id = T.id
                 WHERE TR.video_id = ' . to_sql($id) . ' ORDER BY T.id';
        $tagsPhoto = DB::all($sql);
        $tags = array();
        if ($tagsPhoto) {
            foreach ($tagsPhoto as $key => $tag) {
                $tags[$tag['tag_id']] = $tag['tag'];
            }
        }
        return $tags;
    }

    static public function getTagInfo($id)
    {
        if (!$id) {
            return false;
        }
        $tag = DB::one('vids_tags', '`id` = ' . to_sql($id));

        return $tag;
    }

    static public function getWhereTags($table = '', $tags = null)
    {
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
            $sql = "SELECT `id` FROM `vids_tags` WHERE ({$where})";
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

    static public function getFromAddForAccess($access) {
        $sql_from_add = " ";
        if ($access == 'private' || $access = '2') {
           $sql_from_add .= " LEFT JOIN invited_private_vids AS ip ON V.user_id=ip.friend_id"; 
        }
        
        return $sql_from_add;
    }

    static function getVideosList($typeOrder = '', $limit = '', $uid = null, $getOffset = false, $cache = true, $vid = 0, $whereSql = '', $groupId = 0, $showAllMyVideo = false)
    {
        global $g;

        $result = array();
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        if ($typeOrder == '') {
            $typeOrder = Common::getOption('list_videos_type_order', 'edge_general_settings');
        }

        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        $access_video_tab = get_param('offset', '');

        $key = 'CProfileVideo_getVideosList_' . $uid . '_' . $vid . '_' . $typeOrder . str_replace(' ', '_', $limit) . '_' . intval($getOffset);
        if ($cache) {
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
            if ($whereTags == 'no_tags') {
                return $result;
            }
        }

        include_once(self::includePath() . '_include/current/video_hosts.php');

        $autoPlayDefault = VideoHosts::getAutoplay();
        VideoHosts::setAutoplay(Common::isOptionActive('video_autoplay'));

        $guid = guid();
        $photoIds = array();

        $where = self::getWhereList('V.', $uid, $groupId, $showAllMyVideo, $access_video_tab);
        if ($whereSql) {
            $where .= $whereSql;
        }

        if (!$guid) {
            $where .= ($where ? " AND " : " ") . " U.set_who_view_profile != 'members'";
        }

        if ($vid) {//Wall
            $where .= ' AND V.id = ' . to_sql($vid);
        }

        $order = self::getOrderList($typeOrder);
        if ($order) {
            $order = ' ORDER BY ' . $order;
        }

        if ($whereTags) {
            $sql = 'SELECT V.*, U.name, U.name_seo, U.country, U.city, U.gender
                      FROM `vids_tags_relations` AS TR
                      JOIN `vids_video` AS V  ON V.id = TR.video_id
                      JOIN `user` AS U ON U.user_id = V.user_id' . self::getFromAddForAccess($access_video_tab) .
                     ' WHERE ' . $where
                             . $whereTags
                             . ' GROUP BY V.id '
                             . $order
                             . $limit;
        } else {
            $sql = 'SELECT V.*, U.name, U.name_seo, U.country, U.city
                      FROM `vids_video` AS V
                      JOIN `user` AS U ON U.user_id = V.user_id' . self::getFromAddForAccess($access_video_tab) .
                     ' WHERE ' . $where
                              . $order
                              . $limit;
        }

        $videos = DB::rows($sql);

        foreach ($videos as $item) {
            $pid = 'v_' . $item['id'];

            $result[$pid]['name'] = $item['name'];
            $result[$pid]['user_info'] = array('name' => $item['name'], 'name_seo' => $item['name_seo']);
            $result[$pid]['user_id'] = $item['user_id'];


            $userData = User::getDataUserOrGroup($item['user_id'], $item['group_id']);
            $result[$pid]['user_name'] = $userData['name'];
            $result[$pid]['user_name_short'] = $userData['name_short'];
            $result[$pid]['user_url'] = $userData['url'];
            $result[$pid]['user_photo_r'] = $userData['photo'];

            $userData = User::getDataUserOrGroup($guid, $item['group_id']);
            $result[$pid]['responding_user'] = $guid . '_' . $item['group_id'];
            $result[$pid]['responding_user_name'] = $userData['name'];
            $result[$pid]['responding_user_name_short'] = $userData['name_short'];
            $result[$pid]['responding_user_url'] = $userData['url'];
            $result[$pid]['responding_user_photo_r'] = $userData['photo'];

            $result[$pid]['city'] = $item['city'];
            $result[$pid]['country'] = $item['country'];

            $result[$pid]['video_id'] = $item['id'];
            $result[$pid]['photo_id'] = $pid;

            $result[$pid]['created'] = $item['dt'];
            $result[$pid]['subject'] = $item['subject'];
            $result[$pid]['description'] = $item['subject'];
            $result[$pid]['private'] = $item['private'];
            $result[$pid]['default'] = 0;
            $result[$pid]['default_group'] = 0;
            $result[$pid]['group_id'] = $item['group_id'];
            $result[$pid]['visible'] = $item['active'] == 1 ? 'Y' : 'N';
            $result[$pid]['count_comments'] = $item['count_comments'];
            $result[$pid]['src_b'] = User::getVideoFile($item, 'b', '');
            $result[$pid]['src_s'] = User::getVideoFile($item, 's', '');
            $result[$pid]['src_src'] = User::getVideoFile($item, 'src', '');
            $result[$pid]['src_v'] = custom_getFileDirectUrl($g['path']['url_files'] . User::getVideoFile($item, 'video_src', ''));

            $clearUrl = explode('?', User::getVideoFile($item, 'video_src', ''));
            $result[$pid]['format'] = mb_strtolower(pathinfo($clearUrl[0], PATHINFO_EXTENSION));
            VideoHosts::$items[$item['id']] = $item;
            $result[$pid]['html_code'] = VideoHosts::getHtmlCodeOneFromSite($item['id'], 807, 454, true, 'auto', '_gallery');
            $result[$pid]['is_video'] = 1;
            $result[$pid]['reports'] = $item['users_reports'];

            $result[$pid]['comments_count'] = $item['count_comments'];

            $result[$pid]['time_ago'] = timeAgo($item['dt'], 'now', 'string', 60, 'second');
            $result[$pid]['date'] = Common::dateFormat($item['dt'], 'photo_date');
            $result[$pid]['hide_header'] = $item['hide_header']*1;

            $result[$pid]['like'] = $item['like'];
            $result[$pid]['dislike'] = $item['dislike'];

            $result[$pid]['my_like'] = '';
            $like = DB::one('vids_likes', 'video_id = ' . to_sql($item['id']) . ' AND `user_id` = ' . to_sql($guid));
            if ($like) {
                $result[$pid]['my_like'] = $like['like'] ? 'Y' : 'N';
            }

            if (Common::isOptionActiveTemplate('gallery_tags') || Common::isOptionActiveTemplate('gallery_tags_template')) {
                $tags = self::getTags($item['id']);
                $result[$pid]['tags'] = $tags;
                $tagsData = CProfilePhoto::getTagsMedia($tags, $item['group_id'], $item['group_page'], 'vids');

                $result[$pid]['tags_title'] = $tagsData['title'];
                $result[$pid]['tags_html'] = $tagsData['html'];
            }

            $result[$pid]['live_id'] = intval($item['live_id']);

            $photoIds[] = $pid;
        }

        VideoHosts::setAutoplay($autoPlayDefault);

        if ($getOffset && $photoIds) {
            $total = count($result);
            foreach($photoIds as $photoIdkey => $pid) {
                if ($total == 1) {
                    $next = 0;
                    $prev = 0;
                } else {
                    if ($photoIdkey == 0) {
                        $next = $photoIdkey + 1;
                        $prev = $total - 1;
                    } elseif ($photoIdkey == $total - 1) {
                        $next = 0;
                        $prev = $photoIdkey - 1;
                    } else {
                        $next = $photoIdkey + 1;
                        $prev = $photoIdkey - 1;
                    }
                }

                $result[$pid]['offset'] = $photoIdkey;
                $result[$pid]['next'] = $next;
                $result[$pid]['prev'] = $prev;
                $result[$pid]['next_id'] = $photoIds[$next];
                $result[$pid]['prev_id'] = $photoIds[$prev];

                $isFriend = 1;

                $private = $result[$photoIds[$prev]]['private'];
                if($private == '1' && !$isFriend && $uid != $guid) {
                    $title = '';
                } else {
                    $title = $result[$photoIds[$prev]]['description'];
                }
                $result[$pid]['prev_title'] = $title;


                $private = $result[$photoIds[$next]]['private'];
                if($private == '1' && !$isFriend && $uid != $guid) {
                    $title = '';
                } else {
                    $title = $result[$photoIds[$next]]['description'];
                }
                $result[$pid]['next_title'] = $title;
            }
        }

        Cache::add($key, $result);

        return $result;
    }
    /* Divyesh - Added on 11-04-2024 */
    static function getAccessVideosList ($typeOrder = '', $tab = '', $limit = '', $uid = null, $getOffset = false, $cache = true, $vid = 0, $whereSql = '', $groupId = 0, $showAllMyVideo = false){
        global $g;

        $result = array();
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        if ($typeOrder == '') {
            $typeOrder = Common::getOption('list_videos_type_order', 'edge_general_settings');
        }

        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        $key = 'CProfileVideo_getVideosList_' . $uid . '_' . $vid . '_' . $typeOrder . str_replace(' ', '_', $limit) . '_' . intval($getOffset);
        if ($cache) {
            $videos = Cache::get($key);
            if ($videos !== null) {
                return $videos;
            }
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
            if ($whereTags == 'no_tags') {
                return $result;
            }
        }

        include_once(self::includePath() . '_include/current/video_hosts.php');

        $autoPlayDefault = VideoHosts::getAutoplay();
        VideoHosts::setAutoplay(Common::isOptionActive('video_autoplay'));

        $guid = guid();
        $photoIds = array();

        $where = self::getWhereList('V.', $uid, $groupId, $showAllMyVideo, $tab);
        if ($whereSql) {
            $where .= $whereSql;
        }

        if (!$guid) {
            $where .= ($where ? " AND " : " ") . " U.set_who_view_profile != 'members'";
        }

        if ($vid) {//Wall
            $where .= ' AND V.id = ' . to_sql($vid);
        }

        $order = self::getOrderList($typeOrder);
        if ($order) {
            $order = ' ORDER BY ' . $order;
        }

        if ($whereTags) {
            $sql = 'SELECT V.*, U.name, U.name_seo, U.country, U.city, U.gender
                      FROM `vids_tags_relations` AS TR
                      JOIN `vids_video` AS V  ON V.id = TR.video_id
                      JOIN `user` AS U ON U.user_id = V.user_id' . self::getFromAddForAccess($tab) .
                     ' WHERE ' . $where
                             . $whereTags
                             . ' GROUP BY V.id '
                             . $order
                             . $limit;
        } else {
            $sql = 'SELECT V.*, U.name, U.name_seo, U.country, U.city
                      FROM `vids_video` AS V
                      JOIN `user` AS U ON U.user_id = V.user_id' . self::getFromAddForAccess($tab) .
                     ' WHERE ' . $where
                              . $order
                              . $limit;
        }

        $videos = DB::rows($sql);

        foreach ($videos as $item) {
            $pid = 'v_' . $item['id'];

            $result[$pid]['name'] = $item['name'];
            $result[$pid]['user_info'] = array('name' => $item['name'], 'name_seo' => $item['name_seo']);
            $result[$pid]['user_id'] = $item['user_id'];


            $userData = User::getDataUserOrGroup($item['user_id'], $item['group_id']);
            $result[$pid]['user_name'] = $userData['name'];
            $result[$pid]['user_name_short'] = $userData['name_short'];
            $result[$pid]['user_url'] = $userData['url'];
            $result[$pid]['user_photo_r'] = $userData['photo'];

            $userData = User::getDataUserOrGroup($guid, $item['group_id']);
            $result[$pid]['responding_user'] = $guid . '_' . $item['group_id'];
            $result[$pid]['responding_user_name'] = $userData['name'];
            $result[$pid]['responding_user_name_short'] = $userData['name_short'];
            $result[$pid]['responding_user_url'] = $userData['url'];
            $result[$pid]['responding_user_photo_r'] = $userData['photo'];

            $result[$pid]['city'] = $item['city'];
            $result[$pid]['country'] = $item['country'];

            $result[$pid]['video_id'] = $item['id'];
            $result[$pid]['photo_id'] = $pid;

            $result[$pid]['created'] = $item['dt'];
            $result[$pid]['subject'] = $item['subject'];
            $result[$pid]['description'] = $item['subject'];
            $result[$pid]['private'] = $item['private'];
            $result[$pid]['default'] = 0;
            $result[$pid]['default_group'] = 0;
            $result[$pid]['group_id'] = $item['group_id'];
            $result[$pid]['visible'] = $item['active'] == 1 ? 'Y' : 'N';
            $result[$pid]['count_comments'] = $item['count_comments'];
            $result[$pid]['src_b'] = User::getVideoFile($item, 'b', '');
            $result[$pid]['src_s'] = User::getVideoFile($item, 's', '');
            $result[$pid]['src_src'] = User::getVideoFile($item, 'src', '');
            $result[$pid]['src_v'] =  custom_getFileDirectUrl($g['path']['url_files'] . User::getVideoFile($item, 'video_src', ''));
            $result[$pid]['src_download'] = User::getVideoFile($item, 'video_src', '');
            
            $clearUrl = explode('?', User::getVideoFile($item, 'video_src', ''));
            $result[$pid]['format'] = mb_strtolower(pathinfo($clearUrl[0], PATHINFO_EXTENSION));
            VideoHosts::$items[$item['id']] = $item;
            $result[$pid]['html_code'] = VideoHosts::getHtmlCodeOneFromSite($item['id'], 807, 454, true, 'auto', '_gallery');
            $result[$pid]['is_video'] = 1;
            $result[$pid]['reports'] = $item['users_reports'];

            $result[$pid]['comments_count'] = $item['count_comments'];

            $result[$pid]['time_ago'] = timeAgo($item['dt'], 'now', 'string', 60, 'second');
            $result[$pid]['date'] = Common::dateFormat($item['dt'], 'photo_date');
            $result[$pid]['hide_header'] = $item['hide_header']*1;

            $result[$pid]['like'] = $item['like'];
            $result[$pid]['dislike'] = $item['dislike'];

            $result[$pid]['my_like'] = '';
            $like = DB::one('vids_likes', 'video_id = ' . to_sql($item['id']) . ' AND `user_id` = ' . to_sql($guid));
            if ($like) {
                $result[$pid]['my_like'] = $like['like'] ? 'Y' : 'N';
            }

            if (Common::isOptionActiveTemplate('gallery_tags') || Common::isOptionActiveTemplate('gallery_tags_template')) {
                $tags = self::getTags($item['id']);
                $result[$pid]['tags'] = $tags;
                $tagsData = CProfilePhoto::getTagsMedia($tags, $item['group_id'], $item['group_page'], 'vids');

                $result[$pid]['tags_title'] = $tagsData['title'];
                $result[$pid]['tags_html'] = $tagsData['html'];
            }

            $result[$pid]['live_id'] = intval($item['live_id']);

            $photoIds[] = $pid;
        }

        VideoHosts::setAutoplay($autoPlayDefault);

        if ($getOffset && $photoIds) {
            $total = count($result);
            foreach($photoIds as $photoIdkey => $pid) {
                if ($total == 1) {
                    $next = 0;
                    $prev = 0;
                } else {
                    if ($photoIdkey == 0) {
                        $next = $photoIdkey + 1;
                        $prev = $total - 1;
                    } elseif ($photoIdkey == $total - 1) {
                        $next = 0;
                        $prev = $photoIdkey - 1;
                    } else {
                        $next = $photoIdkey + 1;
                        $prev = $photoIdkey - 1;
                    }
                }

                $result[$pid]['offset'] = $photoIdkey;
                $result[$pid]['next'] = $next;
                $result[$pid]['prev'] = $prev;
                $result[$pid]['next_id'] = $photoIds[$next];
                $result[$pid]['prev_id'] = $photoIds[$prev];

                $isFriend = 1;

                $private = $result[$photoIds[$prev]]['private'];
                if($private == '1' && !$isFriend && $uid != $guid) {
                    $title = '';
                } else {
                    $title = $result[$photoIds[$prev]]['description'];
                }
                $result[$pid]['prev_title'] = $title;


                $private = $result[$photoIds[$next]]['private'];
                if($private == '1' && !$isFriend && $uid != $guid) {
                    $title = '';
                } else {
                    $title = $result[$photoIds[$next]]['description'];
                }
                $result[$pid]['next_title'] = $title;
            }
        }

        Cache::add($key, $result);

        return $result;
    }
    /* Divyesh - Added on 11-04-2024 */

    /*
     * For self::getVideosList
     */
    static public function getTypeOrderVideosList($notRandom = false, $lang = false)
    {
        global $p;

        if ($lang !== false) {
            $pLast = $p;
            $p = 'vids_list.php';
        }
        $list = array(
            'order_new'              => l('order_new', $lang),
            'order_most_commented'   => l('order_most_commented', $lang),
            'order_most_viewed'      => l('order_most_viewed', $lang),
            'order_random'           => l('order_random', $lang)
        );
        if ($lang !== false) {
            $p = $pLast;
        }
        if ($notRandom) {
            unset($list['order_random']);
        }
        return $list;
    }

    /*
     * count_comments =  photo commets + replies comments
     * count_comments_replies = replies comments
     */
    static function updateCountComment($vid)
    {
        $countComments = '(SELECT COUNT(*)
                             FROM `vids_comment`
                            WHERE `parent_id` = 0
                              AND `video_id` = ' . to_sql($vid) . ')';

        $countCommentsReplies = '(SELECT COUNT(*)
                                 FROM `vids_comment`
                                WHERE `parent_id` != 0
                                  AND `video_id` = ' . to_sql($vid) . ')';

        $sql = 'UPDATE `vids_video` SET
                `count_comments` = ' . $countComments . ',
                `count_comments_replies` = ' . $countCommentsReplies . ',
                `last_action_comment` = ' . to_sql(date('Y-m-d H:i:s')) . '
                 WHERE `id` = ' . to_sql($vid);

        $liveId = DB::result('SELECT `live_id` FROM `vids_video` WHERE `id` = ' . to_sql($vid));
        if ($liveId) {
            $sql_1 = 'UPDATE `live_streaming` SET
                           `count_comments` = ' . $countComments . ',
                           `count_comments_replies` = ' . $countCommentsReplies . '
                     WHERE `id` = ' . to_sql($liveId);
            DB::execute($sql_1);
        }

        DB::execute($sql);
    }

    static function updateCountCommentReplies($cid)
    {
        $sql = "SELECT COUNT(*)
                  FROM `vids_comment`
                 WHERE `parent_id` = " . to_sql($cid);
        $countCommentsReplies = DB::result($sql);
        $sql = "UPDATE `vids_comment` SET
                `replies` = " . $countCommentsReplies . '
                WHERE `id` = ' . to_sql($cid);
        DB::execute($sql);
    }

    static function getCountCommentReplies($cid)
    {
        $sql = 'SELECT COUNT(*)
                  FROM `vids_comment`
                 WHERE `parent_id` = ' . to_sql($cid);
        return DB::result($sql);
    }

    static function getCountComment($pid)
    {
        $sql = 'SELECT `count_comments`
                  FROM `vids_video`
                 WHERE `id` = ' . to_sql($pid);
        return DB::result($sql, 0, DB_MAX_INDEX);
    }

    static function getId($pid)
    {
        return str_replace('v_', '', $pid);
    }
}