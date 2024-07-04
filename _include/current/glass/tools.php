<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CVidsTools
{
    const ALLOWTAGS = '<b><i><u><s><strike><strong><em>';

    static $wherePrivateCondition = '';
    static $hardTrim = false;
    static $numberTrim = 28;
    static $admin = false;

    static function addWherePrivateCondition($where = '')
    {
        if(!defined('ADMINISTRATOR')) {
            $and = '';
            if(trim($where) != '') {
                $and = ' AND ';
            }
            $where .= $and . '((private = 0) OR (private = 1 AND user_id = ' . to_sql(guid(), 'Number') . ')) AND group_id = 0 ';
        }

        return $where;
    }

    static public function stags($str)
    {
        return strip_tags_attributes($str, self::ALLOWTAGS);
    }


    static public function countUsers()
    {
        return DB::count('user', 'vid_videos>0');
    }
    static public function getUsers($limit = '0,6')
    {
        //blog_posts>0
        $pops = DB::select('user', 'vid_videos>0', 'vid_visits DESC, vid_comments DESC, vid_videos DESC', $limit);
        foreach ($pops as $k => &$v) {
            $v['photo'] = urphoto($v['user_id']);
        }
        return $pops;
    }

    static protected function _countVideos($where = '')
    {
        $where = self::addWherePrivateCondition($where);
        return DB::count('glass_video', $where);
    }
    static protected function _getVideos($where, $order, $limit)
    {
        $where = self::addWherePrivateCondition($where);
        return self::_filterVideosToHtml(DB::select('glass_video', $where, $order, $limit, '*, count_comments + count_comments_replies AS count_comments_all'));
    }
    static protected function _countVideosFilter($where = '')
    {
        return self::_countVideos(self::_prepareWhereVideos($where));
    }
    static protected function _getVideosFilter($where, $order, $limit)
    {
        return self::_getVideos(self::_prepareWhereVideos($where), $order, $limit);
    }


    static protected function _prepareWhereVideos($where)
    {
        $wa = array();
        if ($where != '') {
            $wa[] = '(' . $where . ')';
        }
        if (self::getFilter() != '') {
            $wa[] = self::getFilter();
        }

        return implode(' AND ', $wa);
    }
    static public function getFilter()
    {
        $wa = array();

        $period = ses('vids_period');
        switch ($period) {
            case 'Today':
                $wa[] = 'TO_DAYS(dt)=TO_DAYS(NOW())';
                break;
            case 'Week':
                $wa[] = 'YEARWEEK(dt)=YEARWEEK(NOW())';
                break;
            case 'Month':
                $wa[] = 'MONTH(dt)=MONTH(NOW()) AND YEAR(dt)=YEAR(NOW())';
                break;
        }

        $cat = ses('vids_cat');
        if (in_array($cat, self::getCatsId()) and $cat != 'All') {
            $wa[] = "FIND_IN_SET('" . DB::esc($cat) . "',cat) > 0";
        }

        return implode(' AND ', $wa);
    }
    static public function setFilter()
    {
        //$cat = ucfirst(strtolower(par('cat')));
        $cat = get_param('cat', 0);
        if (in_array($cat, self::getCatsId())) {
            setses('vids_cat', $cat);
        } elseif ($cat != '') {
            setses('vids_cat', '');
        }

        $period = ucfirst(strtolower(par('period')));
        if (in_array($period, self::getPeriods())) {
            setses('vids_period', $period);
        } elseif ($period != '') {
            setses('vids_period', '');
        }
    }
    static public function getFilterCat()
    {
        $cat = ses('vids_cat');
        if (in_array($cat, self::getCatsId())) {
            return $cat;
        } else {
            return 'All';
        }
    }
    static public function getFilterPeriod()
    {
        $period = ucfirst(strtolower(ses('vids_period')));
        if (in_array($period, self::getPeriods())) {
            return $period;
        } else {
            return 'All';
        }
    }

    static public function getCatsAll($row = array('All'))
    {
      DB::query("SELECT * FROM `vids_category` ORDER BY `position`");
      while($item = DB::fetch_row()) {
          $row[$item['category_id']] = $item['category_title'];
      }
      return $row;
    }

    static public function getCats($row = array('All'))
    {
      DB::query("SELECT * FROM `vids_category` ORDER BY `position`");
      while($item = DB::fetch_row()) {
          $row[] = $item['category_title'];
      }
      return $row;
    }

    static public function getCatsId($row = array('All'))
    {
      DB::query("SELECT * FROM `vids_category` ORDER BY `position`");
      while($item = DB::fetch_row()) {
          $row[] = $item['category_id'];
      }
      return $row;
    }

    static public function getCatsIdTitle()
    {
      DB::query("SELECT * FROM `vids_category` ORDER BY `position`");
      $id = array();
      $title = array();
      $i = 1;
      while($item = DB::fetch_row()) {
          $title[$i] = $item['category_title'];
          $id[$i] = $item['category_id'];
          $i++;
      }
      return array('title' => $title, 'id' => $id);
    }

    static public function getDefaultCats()
    {
      $result = array();
      $categories = DB::rows("SELECT `category_id` FROM `vids_category` WHERE `check` = 1");
      foreach ($categories as $item) {
          $result[] = $item['category_id'];
      }
      return $result;
    }

    static public function getPeriods()
    {
        return explode('|', 'All|Today|Week|Month');
    }




    /* Selections by popular, new, discussed, etc. */
    static public function countTotal()
    {
        return DB::count('glass_video', self::addWherePrivateCondition());
    }

    static public function countVideosAdmin()
    {
        return self::_countVideosFilter();
    }
    static public function getVideosAdmin($limit = '0,2')
    {
        return self::_getVideos('', 'dt DESC', $limit);
    }


    static public function countVideosViews()
    {
        return self::_countVideosFilter();
    }
    static public function getVideosViews($limit = '0,2')
    {
        return self::_getVideosFilter('', 'count_views DESC', $limit);
    }
    static public function countVideosComments()
    {
        return self::_countVideosFilter();
    }
    static public function getVideosComments($limit = '0,2')
    {
        return self::_getVideosFilter('', 'count_comments_all DESC, count_views DESC', $limit);
    }
    static public function countVideosNew()
    {
        return self::_countVideosFilter();
    }
    static public function getVideosNew($limit = '0,2')
    {
        return self::_getVideosFilter('', 'dt DESC', $limit);
    }
    static public function countVideosFeatured()
    {
        return self::_countVideosFilter();
    }
    static public function getVideosFeatured($limit = '0,2')
    {
        return self::_getVideosFilter('', 'dt DESC', $limit);
    }
    static public function countVideosRates()
    {
        return self::_countVideosFilter();
    }
    static public function getVideosRates($limit = '0,2')
    {
        return self::_getVideosFilter('', 'rating DESC', $limit);
    }

    /* Selections by user */
    static public function countVideosByUser($user_id)
    {
        return self::_countVideos("user_id='" . intval($user_id) . "'");
    }
    static public function getVideosByUser($user_id, $limit = '0,10')
    {
        return self::_getVideos("user_id='" . intval($user_id) . "'", 'dt DESC', $limit);
    }
    static public function getVideosByUserExcept($user_id, $exceptId, $limit = '0,10')
    {
        return self::_getVideos("user_id='" . intval($user_id) . "' AND id!='" . intval($exceptId) . "'", 'dt DESC', $limit);
    }
    static public function getVideosByUserSite($user_id, $limit = '0,10')
    {
        return self::_getVideos("user_id='" . intval($user_id) . "' AND code LIKE 'site:%'", 'dt DESC', $limit);
    }


    /* Search */
    static public function getVideosByRand($limit = '0,10')
    {
        return self::_getVideos("", 'RAND()', $limit);
    }
        static public function countVideosByRand()
        {
            return DB::count('glass_video','','RAND()');
        }
    static public function filterSearchQuery($q)
    {
        return htmlspecialchars(self::_filterSearchIndex($q, false), ENT_QUOTES);
    }
    static public function countVideosByQuery($q)
    {
        $q = self::_filterSearchIndex($q, false);
        $q = DB::esc_like($q);
        return DB::count('glass_video', "search_index LIKE '% " . $q . " %'");
    }
    static public function getVideosByQuery($q, $limit = '0,10')
    {
        $q = self::_filterSearchIndex($q, false);
        $q = DB::esc_like($q);
        //self::incrementQuery($q);
        $videos = DB::select('glass_video', "search_index LIKE '% " . $q . " %'", 'dt DESC', $limit);
        $videos_r = array();
        foreach ($videos as $k => $v) {
            $videos_r[$k] = self::_filterVideoToHtml($v, true);
            $videos_r[$k]['context'] = self::_getContext($q, $v['text']);
        }
        return $videos_r;
    }
    static protected function _getContext($q, $v)
    {
        $qlen = pl_strlen($q);
        $halflen = floor((130 - $qlen) / 2.5);
        $pos = strpos($v, $q);
        if ($pos !== false) {
            if ($pos <= $halflen) {
                $v = neat_trim($v, 130);
            } else {
                $pre_q = substr($v, 0, $pos);
                $video_q = substr($v, $pos + pl_strlen($q));
                $pre_q = neat_trimr($pre_q, $halflen);
                $video_q = neat_trim($video_q, $halflen);
                $v = $pre_q . ' ' . $q . ' ' . $video_q;
                $v = str_replace('  ', ' ', $v);
            }
        } else {
            $v = neat_trim($v, 130);
        }
        $v = str_replace($q, "<i>$q</i>", $v);
        return $v;
    }

    /* List hot searches
    static public function incrementQuery($q)
    {
        $q = self::_filterSearchIndex($q, false);
        $hs = DB::one('vids_hotsearch', "text LIKE '" . $q . "'");
        if (is_array($hs)) {
            DB::update('vids_hotsearch', array('count' => new DBNoEsc('count+1'), 'dt' => date('Y-m-d H:i:s')),
                       "id='" . $hs['id'] . "'");
        } else {
            DB::insert('vids_hotsearch', array('text' => $q, 'count' => '1', 'dt' => date('Y-m-d H:i:s')));
        }
    }
    static public function countHotSearches()
    {
        return DB::count('vids_hotsearch');
    }
    static public function getHotSearches($limit = '0,10')
    {
        $rows = DB::select('vids_hotsearch', '', 'count DESC', $limit);
        $rows_r = array();
        foreach ($rows as $k => $v) {
            $rows_r[$k] = $v;
            $rows_r[$k]['urltext'] = urlencode($v['text']);
            $rows_r[$k]['dt_readable'] = pl_date('n M Y, H:i', $v['dt']);
        }
        return $rows_r;
    }*/

    /* Comments to video */
    static public function countCommentsNew()
    {
        return DB::count('vids_comment');
    }
    static public function getCommentsNew($limit = '0,20')
    {
        $rows = DB::select('vids_comment', '', 'dt DESC', $limit);
        $rows_r = array();
        foreach ($rows as $k => $v) {
            $rows_r[] = self::_filterCommentToHtml($v);
        }
        return $rows_r;
    }
    static public function countCommentsByVideoId($video_id)
    {
        return DB::count('vids_comment', "video_id='" . intval($video_id) . "'");
    }
    static public function getCommentsByVideoId($video_id, $limit = '')
    {
        $rows = DB::select('vids_comment', "video_id='" . intval($video_id) . "'", 'dt DESC', $limit);
        $rows_r = array();
        foreach ($rows as $k => $v) {
            $v['text'] = Common::parseLinksSmile($v['text']);

            $rows_r[] = self::_filterCommentToHtml($v);
        }
        return $rows_r;
    }
    static public function getCommentById($id)
    {
        $row = DB::one('vids_comment', "id='" . intval($id) . "'");
        $row = self::_filterCommentToHtml($row);
        return $row;
    }
    static public function insertCommentByVideoId($video_id, $text = null, $date = null, $isAddWall = true, $videoUserId = null, $getInfo = false, $groupId = 0, $groupUserId = 0)
    {
        $video = self::getVideoById($video_id);
        $guid = guid();
        if ($text === null) {
            $text = get_param('text');
        }
        if ($date === null) {
            $date = date('Y-m-d H:i:s');
        }
        $r = 0;
        if (is_array($video)) {
            $parentId = get_param_int('reply_id');
            $send = get_param('send', time());
            $commentUserId = 0;
            if ($videoUserId === null) {
                $videoUserId = DB::result('SELECT `user_id` FROM `glass_video` WHERE `id` = ' . to_sql($video_id));
            }
            if ($parentId) {
                $sql = "SELECT `user_id` FROM `vids_comment` WHERE `id` = " . to_sql($parentId);
                $commentUserId = DB::result($sql);
                $isNew = intval($commentUserId != $guid);
            } else {
                $isNew = intval($videoUserId != $guid);
            }

            if (Common::isOptionActiveTemplate('gallery_comment_parse_media')) {
                $text = OutsideImages::filter_to_db($text);
                $text = VideoHosts::textUrlToVideoCode($text);
            } else {
                $text = self::_filterCommentText($text);
            }

            $row = array(
                'user_id'       => $guid,
                'video_id'      => $video['id'],
                'video_user_id' => $videoUserId,
                'dt'            => $date,
                'text'          => $text,
                'send'          => $send,
                'parent_id'     => $parentId,
                'parent_user_id' => $commentUserId,
                'is_new'        => $isNew,
                'group_id'      => $groupId,
                'group_user_id' => $groupUserId
            );
            DB::insert('vids_comment', $row);
            $r = DB::insert_id();

            DB::update('user', array('vid_comments' => new DBNoEsc('vid_comments+1')), "user_id='" . $video['user_id'] . "'");
            if ($parentId) {
                CProfileVideo::updateCountCommentReplies($parentId);
            }
            CProfileVideo::updateCountComment($video['id']);
            if ($isAddWall) {
                Wall::setSiteSectionItemId($video_id);
                Wall::add('vids_comment', $r);
                Wall::addItemForUser($video['id'], 'vids');
            }
            Wall::updateCountCommentsCustomItem($video['id'], 'vids');
        }

        if ($getInfo) {
            $r = array('cid' => $r,
                       'text' => $text);
        }

        return $r;
    }
    static public function delCommentById($id)
    {
        $comment = self::getCommentById($id);
        if (is_array($comment)) {

            $video = self::getVideoById($comment['video_id']);

            if(!is_array($video)) {
                return false;
            }

            if($comment['user_id'] != guid() && $video['user_id'] != guid()) {
                return false;
            }

            Wall::remove('vids_comment', $id, $comment['user_id']);

            DB::delete('vids_comment', 'id = ' . to_sql($comment['id']));

            DB::delete('vids_comments_likes', '`cid` = ' . to_sql($comment['id']));

            if (is_array($video)) {
                DB::update('user', array('vid_comments' => new DBNoEsc('vid_comments-1')), "user_id='" . $video['user_id'] . "' AND vid_comments > 0");
                Wall::updateCountCommentsCustomItem($comment['video_id'], 'vids');
                Wall::remove('vids_comment', $comment['id'], $comment['user_id']);

                if ($comment['parent_id']) {
                    CProfileVideo::updateCountCommentReplies($comment['parent_id']);
                } else {
                    $parentComments = DB::select('vids_comment', '`parent_id` = ' . to_sql($comment['id']));
                    foreach ($parentComments as $key => $item) {
                        Wall::remove('vids_comment', $item['id'], $item['user_id']);
                        DB::delete('vids_comments_likes', '`cid` = ' . to_sql($item['id']));
                    }
                    DB::delete('vids_comment', '`parent_id` = ' . to_sql($comment['id']));
                }

                CProfileVideo::updateCountComment($comment['video_id']);
                return true;
            }
        }
        return false;

    }

    static function deleteCommentVideoByAjax($cid = 0)
    {
        global $g_user;

        $responseData = false;
        $cid = get_param('cid', $cid);
        if ($g_user['user_id'] && $cid) {
            if (CVidsTools::delCommentById($cid)) {
                $responseData = array();
                if (Common::getTmplName() == 'edge') {
                    $pid = CProfileVideo::getId(get_param('pid'));
                    $parentId = get_param_int('parent_id');
                    if ($parentId == $cid) {
                        $responseData = CProfileVideo::getCountComment($pid);
                    } else {
                        $responseData = CProfileVideo::getCountCommentReplies($parentId);
                    }
                }
            }
        }
        return $responseData;
    }

    static public function delCommentByIdByAdmin($id)
    {
        $comment = self::getCommentById($id);

        Wall::remove('vids_comment', $id, $comment['user_id']);

        if (is_array($comment)) {
            DB::delete('vids_comment', "id='" . $comment['id'] . "'");
            $video = self::getVideoById($comment['video_id']);

            if (is_array($video)) {
                DB::update('user', array('vid_comments' => new DBNoEsc('vid_comments-1')), "user_id='" . $video['user_id'] . "'");
                $sql = 'SELECT COUNT(*) FROM vids_comment
                    WHERE video_id = ' . to_sql($comment['video_id'], 'Number');
                $comments = DB::result($sql);
                DB::update('glass_video', array('count_comments' => $comments), "id='" . $video['id'] . "'");
            }
        }
    }
    static protected function _filterCommentToHtml($row)
    {
        global $g;
        $row['text_readable'] = nl2br($row['text']);

        if (guser('user_id') == $row['user_id']) {
            $row['is_my'] = true;
        } else {
            $row['is_my'] = false;
        }

        //$format = 'd/m/y H:i:s';
        $row['dt_readable'] = Common::dateFormat($row['dt'], 'vids_comment', false);

        $row['user'] = user($row['user_id']);
        $row['user_photo'] = urphoto($row['user_id']);

        return $row;
    }
    static protected function _filterCommentText($v)
    {
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        //$v = htmlspecialchars($v, ENT_QUOTES);
        $v = trim($v);
        return $v;
    }
    static public function filterCommentText($v)
    {
        return self::_filterCommentText($v);
    }





    /*[> Subscriptions <]*/
    static public function isSubscrided($subscriber_id, $uploader_id)
    {
        $uid1 = intval($subscriber_id);
        $uid2 = intval($uploader_id);
        $r = DB::count('vids_subscribe', "subscriber_user_id='" . $uid1 . "' AND uploader_user_id='" . $uid2 . "'", '', '', '', DB_MAX_INDEX, true);
        return ($r > 0);
    }
    static public function addSubscription($uploader_id)
    {
        if (!self::isSubscrided(guser('user_id'), $uploader_id)
            and intval($uploader_id) > 0 and guser('user_id') > 0
            and intval($uploader_id) != guser('user_id')
        ) {
            $row = array(
                'subscriber_user_id' => guser('user_id'),
                'uploader_user_id' => intval($uploader_id),
            );
            DB::insert('vids_subscribe', $row);
        }
    }
    static public function removeSubscription($uploader_id)
    {
        DB::delete('vids_subscribe', "subscriber_user_id='" . guser('user_id') . "' AND uploader_user_id='" . intval($uploader_id) . "'");
    }

    static public function getSubscriptionsIds()
    {
        return DB::field('vids_subscribe', 'uploader_user_id', "subscriber_user_id='" . guser('user_id') . "'");
    }
    static public function getVideosBySubscriptions($limit = '0,10')
    {
        return self::getVideosByUsersIds(self::getSubscriptionsIds(), $limit);
    }
    static public function getVideosByFriends($limit = '0,10')
    {
        return self::getVideosByUsersIds(getFriendsIds(), $limit);
    }
    static public function getVideosByUsersIds($ids, $limit = '0,10')
    {
        if (count($ids) > 0) {
            return self::_getVideos("user_id IN (" . implode(',', $ids) . ")", 'dt DESC', $limit);
        } else {
            return array();
        }
    }
    static public function countVideosBySubscriptions()
    {
        return self::countVideosByUsersIds(self::getSubscriptionsIds());
    }
    static public function countVideosByFriends()
    {
        return self::countVideosByUsersIds(getFriendsIds());
    }
    static public function countVideosByUsersIds($ids)
    {
        if (count($ids) > 0) {
            return self::_countVideos("user_id IN (" . implode(',', $ids) . ")");
        } else {
            return 0;
        }
    }

    /* One video viewing and editing */
    static public function increaseViewCountVideoById($id, $user_id)
    {
        $responseData = false;
        if (guid()) {
            //DB::update('glass_video', array('count_views' => new DBNoEsc('count_views+1')), 'id=\'' . intval($id) . '\'');
            self::viewVideoByIdAndUserId($id, $user_id);
            $responseData = true;
        }
        return $responseData;
    }

    static public function viewVideoByIdAndUserId($id, $user_id)
    {
        if ($user_id != guid()) {
            DB::update('glass_video', array('count_views' => new DBNoEsc('count_views+1')), 'id=\'' . intval($id) . '\'');
            DB::update('user', array('vid_visits' => new DBNoEsc('vid_visits+1')), 'user_id=\'' . intval($user_id) . '\'');
        }
    }

    static public function getVideoById($id, $withUser = false, $widthMedia = null, $heightMedia = null)
    {
        $video = DB::one('glass_video', "id='" . intval($id) . "'");
        if (is_array($video)) {
            VideoHosts::$items[$video['id']] = $video;
            $video = self::_filterVideoToHtml($video, $withUser, $widthMedia, $heightMedia);
        }
        return $video;
    }
    /*static public function getVideoFromPost()
    {
        return array(
            'subject'       => self::_filterSubject(par("subject"), par("text")),
            'text'          => self::_filterText(par("text")),
            'tags'          => self::_filterText(par("tags")),
        );
    }*/
    static public function getNullVideo()
    {
        return self::_filterVideoToHtml(array(
            'id'             => '0',
            'user_id'        => '',
            'dt'             => '',
            'count_comments' => '0',
            'count_views'    => '0',
            'subject'        => '',
            'text'           => '',
            'search_index'   => '',
            'code'           => '',
            'tags'           => '',
            'active'         => '1',
            'private'        => '0',
            'cat'            => '0',
        ));
    }
    static public function saveVideoInfoToSession()
    {
        setses('video_upload_subject', par("subject"));
        setses('video_upload_text'   , par("text"));
        setses('video_upload_tags'   , par("tags"));
        setses('video_upload_cat'    , (is_array(par("cat")) ? implode(",", par('cat')) : ''));
        setses('video_upload_private', par("private"));
    }
    static public function cleanVideoInfoToSession()
    {
        setses('video_upload_subject', null);
        setses('video_upload_text'   , null);
        setses('video_upload_tags'   , null);
        setses('video_upload_cat'    , null);
        setses('video_upload_private', null);
    }
    static public function insertVideo($file="", $active=1, $isAddData = true, $isUploaded = 0)
    {

        if (!self::validateVideoInfoSession() && !get_session("video_no_validate",false)) {
            return;
        }

        if($file=="")
        {
            if (!self::validateVideoCode()) {
                return;
            }
        }

        DB::insert('glass_video', array(
            'user_id'        => guser('user_id'),
            'dt'             => date('Y-m-d H:i:s'),
            'subject'        => self::_filterSubject(ses("video_upload_subject"), 1000),
            'text'           => self::_filterText(ses("video_upload_text")),
            'search_index'   => self::_filterSearchIndex(ses("video_upload_subject") . ' ' . ses("video_upload_text")),
            'tags'           => self::_filterTags(ses("video_upload_tags")),
            'cat'            => ses('video_upload_cat'),
            'private'        => (ses("video_upload_private") == '1' ? '1' : '0'),
            'active'         => $active,
            'is_uploaded'    => $isUploaded,
            'name'          =>$file.".mp4"
        ));
        $id = DB::insert_id();          
        if (self::validateVideoCode()) {
            DB::update('glass_video', array('code' => VideoHosts::filterOneToDb(par('code'))), 'id="' . $id . '"');
            DB::update('user', array('vid_videos' => new DBNoEsc('vid_videos+1')), 'user_id=' . guser('user_id'));
            Wall::add('looking_glass', $id);
        }

        if($file!=""){
            if (VideoUpload::upload($id, $file)) {
                DB::update('glass_video', array('code' => 'site:' . $id . ''), 'id="' . $id . '"');
                if ($isAddData) {
                    DB::update('user', array('vid_videos' => new DBNoEsc('vid_videos+1')), 'user_id=' . guser('user_id'));
                    Wall::add('looking_glass', $id);
                }
            } else {
                DB::delete('glass_video', 'id="' . $id . '"');
                return;
            }
        }

        return $id;
    }
    static public function updateVideoById($id, $isAdmin = false)
    {
        $private = par("private") == '1' ? 1 : 0;
        $row = array(
            'subject'        => self::_filterSubject(par("subject"), 1000),
            'text'           => self::_filterText(par("text")),
            'search_index'   => self::_filterSearchIndex(par("subject") . ' ' . par("text")),
            'tags'           => self::_filterTags(par("tags")),
            'cat'            => (is_array(par("cat")) ? implode(",", par('cat')) : ''),
            'private'        => $private,
        );

        if(Common::getTmplSet() != 'old') {
            unset($row['text']);
            unset($row['tags']);
            unset($row['cat']);
            unset($row['private']);
            unset($row['search_index']);

            $videoInfo = self::getVideoById($id);
            if($videoInfo) {
                $private = $videoInfo['private'];
                $row['search_index'] = self::_filterSearchIndex(par("subject") . ' ' . $videoInfo['text']);
            }
        }

        DB::update('glass_video', $row, 'id=\'' . intval($id). '\'' . ($isAdmin ? '' : ' AND user_id=\'' . guser('user_id') . '\''));
        Wall::updateAccessItemById($id, 'vids', $private ? 'private' : 'public');
    }
    static public function rateById($id, $rate)
    {
        $id = intval($id);
        $rate = intval($rate);
        if ($rate < 1 or $rate > 10 or $id <= 0) {
            return;
        }

        $video = DB::one('glass_video', "id='" . $id . "'");
        if (is_array($video)) {
            $isRated = DB::one('vids_rate', "video_id='" . $id . "' AND user_id='" . guid() . "'");
            if (is_array($isRated)) {
                if ($video['count_rates'] > 0) {
                    $newRate = ($video['rating'] * $video['count_rates'] - $video['rating'] + $rate) / $video['count_rates'];
                    DB::update('glass_video', array('rating' => $newRate), "id='" . $id . "'");
                } else {
                    $newRate = $rate;
                    DB::update('glass_video', array('rating' => $newRate, 'count_rates' => new DBNoEsc('count_rates+1')), "id='" . $id . "'");
                }
            } else {
                $newRate = ($video['rating'] * $video['count_rates'] + $rate) / ($video['count_rates'] + 1);
                DB::insert('vids_rate', array('video_id' => $id, 'user_id' => guid()));
                DB::update('glass_video', array('rating' => $newRate, 'count_rates' => new DBNoEsc('count_rates+1')), "id='" . $id . "'");
            }

            Wall::addItemForUser($video['id'], 'vids');
        }
    }
    static public function rateAlready($id)
    {
        $id = intval($id);
        $isRated = DB::one('vids_rate', "video_id='" . $id . "' AND user_id='" . guid() . "'");
        return is_array($isRated);
    }
    static public function delVideoById($id, $isAdmin = false)
    {
        $id = intval($id);
        $video = DB::one('glass_video', "id='" . $id . "'");
        if (is_array($video)) {
            VideoUpload::delete($id);
            DB::delete('glass_video', "id='" . $id . "'" . ($isAdmin ? '' : ' AND user_id=\'' . guser('user_id') . '\''));
            Wall::remove('vids', $id);

            $sql = 'SELECT *
                      FROM vids_comment
                     WHERE video_id = ' . to_sql($id, 'Number');
            DB::query($sql, 1);
            while($row = DB::fetch_row(1)) {
                Wall::remove('vids_comment', $row['id']);
            }

            DB::delete('vids_comment', 'video_id = ' . to_sql($id));
            DB::delete('vids_comments_likes', 'video_id = ' . to_sql($id));

            DB::execute('DELETE FROM `vids_likes` WHERE `video_id` = ' . to_sql($id, 'Number'));


            $groupId = $video['group_id'];
            $data =  array('vid_videos' => 'vid_videos - 1');
            $where = ' AND vid_videos > 0';
            if ($groupId) {
                $isPage = Groups::getInfoBasic($groupId, 'pages');
                if ($isPage) {
                    $data =  array('vid_videos_pages' => 'vid_videos_pages - 1');
                    $where = ' AND vid_videos_pages > 0';
                } else {
                    $data =  array('vid_videos_groups' => 'vid_videos_groups-1');
                    $where = ' AND vid_videos_groups > 0';
                }
            }
            DB::update('user', $data, 'user_id = ' . to_sql($video['user_id']) . $where, '', '', true);

            DB::update('user', array('video_greeting' => 0), "user_id='" . $video['user_id'] . "' AND video_greeting = ".$id);

            Wall::removeBySiteSection('vids', $id);

            DB::delete('users_reports', 'video = 1 AND `photo_id` = ' . to_sql($id));

            CProfilePhoto::deleteTags($id, 'video');
        }
    }


    /* Validators */
    static public function validateVideoInfo()
    {
        return (self::_filterSubject(par("subject")) != ''
            and self::_filterText(par("text")) != ''
            and self::_filterTags(par("tags")) != ''
            and (is_array(par("cat")) ? "'". implode(",", par('cat')) . "'" : '') != ''
            and par("private") != '');
    }
    static public function validateVideoInfoSession()
    {
        return (self::_filterSubject(ses("video_upload_subject")) != ''
            and self::_filterText(ses("video_upload_text")) != ''
            and self::_filterTags(ses("video_upload_tags")) != ''
            and ses('video_upload_cat') != ''
            and ses("video_upload_private") != '');
    }
    static public function validateVideoFile()
    {
        return (self::validateVideoCode() or self::validateVideoUpload());
    }
    static public function validateVideoCode($onlyYoutube = false)
    {
        return (VideoHosts::filterOneToDb(par('code'), $onlyYoutube) != '');
    }

    static public function validateVideoUpload()
    {
        return (isset($_FILES['Filedata']) and $_FILES['Filedata']['error'] == 0);
    }


    /* One video text filters */
    static public function _filterVideosToHtml($videos, $withUser = false, $hard = false)
    {
        $videos_r = array();
        foreach ($videos as $k => $v) {
            $video = self::_filterVideoToHtml($v, $withUser);
            $video['subject'] = (self::$hardTrim) ? hard_trim($video['subject'], self::$numberTrim) : neat_trim($video['subject'], self::$numberTrim);
            $videos_r[] = $video;
        }
        return $videos_r;
    }
    static protected function _filterVideoToHtml($row, $withUser = false, $widthMedia = 444, $heightMedia = null)
    {
        global $g;
        //if ($withUser) {
            $row['user'] = user($row['user_id']);
            $row['user_photo'] = urphoto($row['user_id']);
        //}
        if ($widthMedia == null) {
            $widthMedia = 444;
        }
        $row['text_readable'] = nl2br(self::_filterLinksTagsToHtml($row['text']));
        $row['text_short'] = nl2br(neat_trim(self::_filterLinksTagsToHtml($row['text']), 135));
        //$row['dt_readable'] = pl_date((date('Y') != pl_date('Y', $row['dt']) ? 'F j Y \a\t h:ia' : 'F j \a\t h:ia'), $row['dt']);
        $row['dt_readable'] =  Common::dateFormat($row['dt'], 'vids_dt_readable', false);
        $row['dt_time'] = Common::dateFormat($row['dt'], 'vids_dt_time', false);
        $row['html_code'] = VideoHosts::filterOneFromDb($row['code'], $widthMedia, $heightMedia);
        $row['image'] = VideoHosts::filterOneImageFromDb($row['code']);
        $row['image_b'] = VideoHosts::filterOneImageBigFromDb($row['code']);
        //$row['image_m'] = VideoHosts::filterImageMediumOneFromDb($row['code']);
        $row['ext'] = VideoHosts::$ext;

        if (guser('user_id') == $row['user_id']) {
            $row['is_my'] = true;
        } else {
            $row['is_my'] = false;
        }

        if ((Common::getTmplSet() == 'old') && self::isSubscrided(guser('user_id'), $row['user_id'])) {
            $row['unsubscribe'] = true;
        } else {
            $row['unsubscribe'] = false;
        }

        $row['rating'] = number_format($row['rating'], 1);
        $ceil_rating = round($row['rating']);
        for ($i = 1; $i <= 10; $i++) {
            if ($ceil_rating == $i) {
                $row['rating_check'][$i] = ' checked="checked" ';
            } else {
                $row['rating_check'][$i] = '';
            }
        }

        // rating
        if( $ceil_rating == 0 ) for($i=1;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        if( $ceil_rating == 1 )
        {
            $row['rating_1'] = "starbright_half";
            for($i=2;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 2 )
        {
            $row['rating_1'] = "starbright";
            for($i=2;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 3 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright_half";
            for($i=3;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 4 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright";
            for($i=3;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 5 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright";
            $row['rating_3'] = "starbright_half";
            for($i=4;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 6 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright";
            $row['rating_3'] = "starbright";
            for($i=4;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 7 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright";
            $row['rating_3'] = "starbright";
            $row['rating_4'] = "starbright_half";
            for($i=5;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }
        if( $ceil_rating == 8 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright";
            $row['rating_3'] = "starbright";
            $row['rating_4'] = "starbright";
            for($i=5;$i<=5;$i++) $row['rating_'.$i] = "stardim";
        }

        if( $ceil_rating == 9 )
        {
            $row['rating_1'] = "starbright";
            $row['rating_2'] = "starbright";
            $row['rating_3'] = "starbright";
            $row['rating_4'] = "starbright";
            $row['rating_5'] = "starbright_half";
        }
        if( $ceil_rating == 10 )
        {
            for($i=1;$i<=5;$i++) $row['rating_'.$i] = "starbright";
        }

        $videoType = explode(':', $row['code']);
        $row['video_type'] = $videoType[0];

        if (!Common::isOptionActiveTemplate('comments_replies') || self::$admin)  {
            $row['count_comments'] = $row['count_comments'] + $row['count_comments_replies'];
        }

        return $row;
    }
    static protected function _filterLinksTagsToHtml($text)
    {
        global $g;
        /*$ends = explode("|", " |\n|,|)|(");
        foreach ($ends as $end) {
            $grabs = grabs($text, 'http://', $end, true);
            foreach ($grabs as $gr) {
                $gr = trim($gr);
                $text = str_replace($gr, '<a href="' . $gr . '">' . hard_trim($gr, 40) . '</a>', $text);
            }
        }*/
        return Common::parseLinks($text);
    }
    static protected function _filterSubject($subject, $maxLength = 50)
    {
        $subject = str_replace("\r\n", "\n", $subject);
        $subject = str_replace("\r", "\n", $subject);
        $subject = self::stags($subject);
        $subject = trim($subject);
        $subject = neat_trim($subject, $maxLength);

        return $subject;
    }
    static protected function _filterText($v)
    {
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        $v = trim($v);
        return $v;
    }
    static protected function _filterTags($v)
    {
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        $v = trim($v);

        $varr = explode(',', $v);
        $tags = array();
        foreach ($varr as $tag) {
            $tags[] = trim($tag);
        }
        $v = implode(',', $tags);

        return $v;
    }
    static protected function _filterSearchIndex($v, $wrapper = true)
    {
        $v = strip_tags($v);
        $symbols = explode(' ', "` ~ ! @ # $ % ^ & * ( ) _ + - = | \\ | / ? \" ' < > [ ] { } № ; : . , \t \r \n");
        //$symbols = explode(' ', "% _  \\  / < > \t \r \n");
        $symbols = explode(' ', "\t \r \n");
        $v = str_replace($symbols, ' ', $v);
        while (strpos($v, '  ') !== false) {
            $v = str_replace('  ', ' ', $v);
        }
        //$v = htmlspecialchars($v, ENT_QUOTES);
        if ($wrapper) {
            $v = ' ' . trim($v) . ' ';
        }
        return $v;
    }
    static public function filterText($v)
    {
        return self::_filterText($v);
    }
}

class VideoUploadService
{
    static function host()
    {
        global $g;
        return 'http://' . $g['media_server'] . '/media_server/';
    }

    static function apiCall($params = array())
    {
        return urlGetContents(self::host() . 'videoconvertor.php?' . http_build_query($params));
    }

    static function convert($video_file, $output_video, $output_img, $format='mp4')
    {
        set_time_limit(3600);

        $c = self::apiCall(array('cmd' => 'convert', 'url' => $video_file, 'format' => $format, 'type' => 'hd'));

        parse_str($c, $ca);

        if (isset($ca['flv']) and isset($ca['jpeg']) and isset($ca['path'])) {
            copyUrlToFile($ca['path'] . $ca['flv'], $output_video);
            copyUrlToFile($ca['path'] . $ca['jpeg'], $output_img);
            self::deleteOnHost($ca['flv']);
            self::deleteOnHost($ca['jpeg']);
            return 'OK';
        } else {
            return $c;
        }
    }
    static function deleteOnHost($file)
    {
        self::apiCall(array('cmd' => 'delete', 'file' => $file));
    }
}

class VideoUploadFfmpeg
{
    const FFMPEG_PATH_NIX = '/usr/bin/ffmpeg';
    //const FLVTOOl2_PATH_NIX = '/usr/bin/flvtool2';
    //const FFMPEG_PATH_WIN = 'C:\Program Files\ffmpeg\ffmpeg.exe';
    const FFMPEG_PATH_WIN = 'C:\ffmpeg\bin\ffmpeg.exe';
    //const FLVTOOl2_PATH_WIN = 'C:\flvtool2\flvtool2.exe';
    static function convert($video_file, $output_video, $output_img)
    {
        if (DIRECTORY_SEPARATOR == '/') {
            $ffmpeg = self::FFMPEG_PATH_NIX;
            //$flvtool2= self::FLVTOOl2_PATH_NIX;
        } elseif (DIRECTORY_SEPARATOR == '\\') {
            $ffmpeg = self::FFMPEG_PATH_WIN;
            //$flvtool2 = self::FLVTOOl2_PATH_WIN;
        }
        //exec($ffmpeg . ' -i ' . $video_file . ' -ab 256k -qscale 5 -ar 44100 -maxrate 3000k -s 640x360 ' . $output_video);

        $strictExperimental = '';
        if (file_exists(__DIR__ . '/../../config/ffmpeg_strict_experimental.txt')) {
            $strictExperimental = ' -strict experimental';
        }

        $cmd = $ffmpeg . ' -i ' . $video_file . $strictExperimental . ' -movflags +faststart -crf 29 -vf "scale=min(iw*720/ih\,1280):min(720\,ih*1280/iw), pad=1280:720:(1280-iw)/2:(720-ih)/2" ' . $output_video;
        exec($cmd);
        //exec($flvtool2 . ' -UkP ' . $output_video);

        $cmd = $ffmpeg . ' -i ' . $video_file . $strictExperimental . ' -qscale 1 -vframes 1 -s 1280x720 -ss 1.2 -vf "scale=min(iw*720/ih\,1280):min(720\,ih*1280/iw), pad=1280:720:(1280-iw)/2:(720-ih)/2" -f image2 ' . $output_img;
        //echo $cmd;
        exec($cmd);

        return 'OK';
    }
}

class VideoUpload
{
    static function upload($id, $file, $format='mp4')
    {
            global $g;

            if($id==0)
            {
                // convert file
                // host path to file
                $file_id = $g['path']['dir_files'] . "video/" . $file;

                //popcorn modified s3 bucket video upload 2024-05-7
                if(getFileDirectoryType('video') == 2) {
                    $file_id = $g['path']['dir_files'] . "temp/video/" . $file;
                } else {
                    $file_id = $g['path']['dir_files'] . "video/" . $file;
                }
            
                DB::close();
                copy($file_id.".txt", __DIR__ . '/../../../VideoCall/videos/'.$file.".mp4");
                if (file_exists(dirname(__FILE__) . '/../../config/ffmpeg.txt')) {
                    $result=VideoUploadFfmpeg::convert($file_id . '.txt', $file_id . '.'.$format, $file_id . '_.jpg');
                } else {
                    $result=VideoUploadService::convert(self::urlFilesVideo() . $file . ".txt", $file_id . '.' . $format, $file_id . '_.jpg', $format);
                }
                
                DB::connect();      
                DB::insert('glass_video', array(
                    'user_id'        => guser('user_id'),
                    'dt'             => date('Y-m-d H:i:s'),
                    'subject'        => 'looking glass',
                    'text'           => ' ',
                    'search_index'   => ' ',
                    'tags'           => 'video',
                    'cat'            => '9',
                    'private'        => 0,
                    'active'         => 1,
                    'is_uploaded'    => 1,
                    'name'           => $file.".mp4"
                ));
                $id = DB::insert_id();
                if (file_exists($file_id . ".txt")) {
                    unlink($file_id . '.txt');
                }
                if($result=='OK'){
                    if ($im = new Image() and @$im->loadImage($file_id . '_.jpg')) {
                        $im->resizeCroppedMiddle(286, 161, $g['image']['logo'], 0);
                        $im->saveImage($file_id . '_b.jpg', $g['image']['quality']);
                    }
                    if ($im = new Image() and @$im->loadImage($file_id . '_.jpg')) {
                        $im->resizeCroppedMiddle(160, 120, $g['image']['logo'], 0);
                        $im->saveImage($file_id . '.jpg', $g['image']['quality']);
                    }
                    /*if ($im = new Image() and @$im->loadImage($file_id . '_.jpg')) {
                        $im->resizeCroppedMiddle(90, 100, $g['image']['logo'], 0);
                        $im->saveImage($file_id . '_s.jpg', $g['image']['quality']);
                    }*/
                    if ($im = new Image() and @$im->loadImage($file_id . '_.jpg')) {
                        rename($file_id . '_.jpg', $file_id . '_src.jpg');
                        //$im->saveImage($file_id . '_src.jpg', 100);
                    }
                    //$file_id . '_s.jpg',
                    $path = array($file_id . '.mp4', $file_id . '.jpg', $file_id . '_b.jpg', $file_id . '_src.jpg');
                    Common::saveFileSize($path);

                    if (file_exists($file_id . "_.jpg")) {
                        unlink($file_id . '_.jpg');
                    }
                    
                    return true;
                } else {
                    if (!$result){
                        $result = l('error_converting_video');
                    }
                    return $result;
                }
            }

            // assign to user
            if($id>0)
            {
                //popcorn modified s3 bucket video file upload 2024-05-07
                if(getFileDirectoryType('video') == 2) {
                    $file = $g['path']['dir_files'] . "temp/video/" . $file;
                    $file_new = $g['path']['dir_files'] . "temp/video/" . $id;
                } else {
                    $file = $g['path']['dir_files'] . "video/" . $file;
                    $file_new = $g['path']['dir_files'] . "video/" . $id;
                }
                // rename files
                @rename($file.".".$format,$file_new.".".$format);
                @rename($file.".jpg",$file_new.".jpg");
                @rename($file."_b.jpg",$file_new."_b.jpg");
                //@rename($file."_s.jpg",$file_new."_s.jpg");
                @rename($file."_src.jpg",$file_new."_src.jpg");

                //popcorn modified s3 bucket video and video image upload 2024-05-07
                if(getFileDirectoryType('video') == 2) {
                    //video mp4 file upload to s3
                    $file_path = $file_new . '.' . $format;
                    custom_file_upload($file_path, "video/" . $id . '.' . $format);
                    
                    //video image upload to s3
                    custom_file_upload($file_new . '.jpg', "video/" . $id . '.jpg');
                    custom_file_upload($file_new . '_src.jpg', "video/" . $id . '_src.jpg');
                    custom_file_upload($file_new . '_b.jpg', "video/" . $id . '_b.jpg');
                    custom_file_upload($file_new . '_bm.jpg', "video/" . $id . '_bm.jpg');
                }

                //$path = array($file_new . '.flv', $file_new . '.jpg', $file_new . '_b.jpg');
                //Common::saveFileSize($path);
                // delete source file
                if (custom_file_exists($file . ".txt")) {
                    unlink($file . '.txt');
                }
                if (custom_file_exists($file_new . ".txt")) {
                    unlink($file_new . '.txt');
                }
            }

            return true;


    }
    static function delete($id)
    {
            global $g;

            //popcorn modified s3 bucket video file delete 2024-05-07
            if(getFileDirectoryType('video') == 2) {
                $file = $g['path']['dir_files'] . "video/" . $id;

                custom_file_delete($file . '.txt');
                custom_file_delete($file . '.mp4');
                custom_file_delete($file . '.flv');
                custom_file_delete($file . '.jpg');
                custom_file_delete($file . '_b.jpg');
                custom_file_delete($file . '_src.jpg');
            } else {
                $file = $g['path']['dir_files'] . "video/" . $id;
                $path = array($file . '.txt', $file . '.mp4', $file . '.flv', $file . '.jpg', $file . '_b.jpg', $file . '_src.jpg');
                Common::saveFileSize($path, false);
                if (custom_file_exists($file . '.txt')) unlink($file . '.txt');
                if (custom_file_exists($file . '.mp4')) unlink($file . '.mp4');
                if (custom_file_exists($file . '.flv')) unlink($file . '.flv');
                if (custom_file_exists($file . '.jpg')) unlink($file . '.jpg');
                //if (custom_file_exists($file . '_s.jpg')) unlink($file . '_s.jpg');
                if (custom_file_exists($file . '_b.jpg')) unlink($file . '_b.jpg');
                if (custom_file_exists($file . '_src.jpg')) unlink($file . '_src.jpg');
                /*if (custom_file_exists($file . '._m.jpg')) unlink($file . '.txt');*/
            }
    }
    static function urlFilesVideo()
    {
        global $g;

        //popcorn modified s3 bucket video 2024-05-07
        if(getFileDirectoryType('video') == 2) {
            $url = Common::urlSiteSubfolders() . $g['dir_files'] . 'temp/' . 'video/';
        } else {
            $url = Common::urlSiteSubfolders() . $g['dir_files'] . 'video/';
        }
        return $url;
    }

    static function updateFromString()
    {
        global $g;
        $isSaved = false;

        $imageString = Common::getFileFromStringParam('image');

        $fileString = Common::getFileFromStringParam('video');

        $id = get_param_int('id');
        $uid = guid();

        $sql = 'SELECT `user_id` FROM `glass_video` WHERE `id` = ' . to_sql($id);
        if(DB::result($sql) == $uid) {

            if($fileString) {

                //popcorn modified s3 bucket video 2024-05-07
                if(getFileDirectoryType('video') == 2) {
                    $basePath = Common::getOption('dir_files', 'path') . 'temp/video/' . $id;
                } else {
                    $basePath = Common::getOption('dir_files', 'path') . 'video/' . $id;
                }

                $path = array($basePath . '.mp4', $basePath . '.jpg', $basePath . '_b.jpg', $basePath . '_src.jpg');
                Common::saveFileSize($path, false);

                $filePath = $basePath . '.mp4';

                if(file_put_contents($filePath, $fileString) === strlen($fileString)) {

                    $sql = 'UPDATE `glass_video`
                        SET `version` = `version` + 1
                        WHERE id = ' . to_sql($id) . ' AND user_id = ' . to_sql($uid);
                    DB::execute($sql);

                    if($imageString) {
                        $filePath = $basePath . '_src.jpg';
                        if(file_put_contents($filePath, $imageString) === strlen($fileString)) {
                            if ($im = new Image() and @$im->loadImage($filePath)) {
                                $im->resizeCroppedMiddle(286, 161, $g['image']['logo'], 0);
                                $im->saveImage($basePath . '_b.jpg', $g['image']['quality']);
                            }
                            if ($im = new Image() and @$im->loadImage($filePath)) {
                                $im->resizeCroppedMiddle(160, 120, $g['image']['logo'], 0);
                                $im->saveImage($basePath . '.jpg', $g['image']['quality']);
                            }
                        }
                    }

                    //popcorn modified s3 bucket video upload from string 2024-05-07
                    if(getFileDirectoryType('video') == 2) {
                        custom_file_upload($filePath, 'video/' . $id . '.mp4');
                        custom_file_upload($basePath . '._src.jpg', 'video/' . $id . '._src.jpg');
                        custom_file_upload($basePath . '._b.jpg', 'video/' . $id . '._b.jpg');
                        custom_file_upload($basePath . '.jpg', 'video/' . $id . '.jpg');
                    }

                    Common::saveFileSize($path);

                    $isSaved = true;
                }
            }

        }

        return $isSaved;
    }
}
