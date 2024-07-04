<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CommentsBase extends CHtmlBlock
{

    static $typeContent = 'live';
    static $prfBlock = 'ls';
    static $table = 'live_streaming';

    static $tableBase = 'vids_video';
    static $tableBaseId = 'id';

    static $tableLikes = 'vids_likes';
    static $tableLikesFieldId = 'video_id';
    static $tableLikesFieldUserId = 'video_user_id';

    static $tableComments = 'vids_comment';
    static $tableCommentsId = 'id';
    static $tableCommentsFieldId = 'video_id';


    static $tableCommentLikes = 'vids_comments_likes';
    static $tableCommentLikesId = 'id';
    static $tableCommentLikesFieldId = 'video_id';
    static $tableCommentLikesFieldUserId = 'video_user_id';

    static $commentCustomClass = '';
    static $commentReplyCustomClass = '';

    static function getNameBlock($block)
    {
        $blocks = array('live' => array('likes' => 'ls_like'));


        return $blocks[self::$typeContent][$block];
    }

    static function updateLikeComment($type = 'live')
    {
        $uid = guid();
        $id = get_param_int('id');
        $cid = get_param_int('cid');
        $like = get_param_int('like');

        $date = date('Y-m-d H:i:s');
        if ($like) {
            $where = to_sql(self::$tableCommentsId, 'Plain') . ' = ' . to_sql($cid);
            $commentInfo = DB::select(self::$tableComments, $where);
            if (!isset($commentInfo[0])) {
                return false;
            }
            $commentInfo = $commentInfo[0];
            $commentUid = $commentInfo['user_id'];

            $isNew = intval($commentUid != $uid);

            $sql = 'SELECT `user_id`
                      FROM ' . to_sql(self::$tableBase, 'Plain') .
                   ' WHERE `id` = ' . to_sql($id);
            $likeUserId = DB::result($sql);

            $sql = 'INSERT IGNORE INTO `' . to_sql(self::$tableCommentLikes, 'Plain') . '`
                       SET `user_id` = ' . to_sql($uid) . ',
                           `cid` = ' . to_sql($cid) . ',
                           `date` = ' . to_sql($date) . ',
                           `is_new` = ' . to_sql($isNew) . ',
                           `comment_user_id` = ' . to_sql($commentUid) . ',
                           `' . to_sql(self::$tableCommentLikesFieldUserId, 'Plain') . '` = ' . to_sql($likeUserId) . ',
                           `' . to_sql(self::$tableCommentLikesFieldId, 'Plain') . '` = ' . to_sql($id);
            DB::execute($sql);
            $isNewLikes = 1;

            $countLikes = DB::count(self::$tableCommentLikes, '`cid` = ' . to_sql($cid));
        } else {
            $where = '`user_id` = '  . to_sql($uid) .
                ' AND `cid` = '   . to_sql($cid) .
                ' AND `' . to_sql(self::$tableCommentLikesFieldId, 'Plain') . '` = '   . to_sql($id);
            DB::delete(self::$tableCommentLikes, $where);

            $countLikes = DB::count(self::$tableCommentLikes, '`cid` = ' . to_sql($cid));
            $isNewLikes = $countLikes ? 1 : 0;
        }

        $data = array('likes' => $countLikes,
                      'last_action_like' => $date,
                      'is_new_like' => $isNewLikes);
        DB::update(self::$tableComments, $data, '`id` = ' . to_sql($cid));

        DB::update(self::$tableBase, array('last_action_comment_like' => $date), '`id` = ' . to_sql($id));
        //self::sendAlert('like', $id, $uid);

        return array('likes' => $countLikes,
                     'date' => $date,
                     'likes_users' => User::getTitleLikeUsersComment($cid, $countLikes, $type));
    }

    static function parseLikes(&$html, $id, $likes = null, $index = 2)
    {

        $guid = guid();
        $likeClass = 'wall_like_hide';

        $row = DB::one(self::$tableBase, '`' . self::$tableBaseId . '` = ' . to_sql($id));
        if ($likes === null) {
            if ($row) {
                $likes = $row['like'];
            } else {
                $likes = 0;
            }
        }

        $block = self::getNameBlock('likes');

        $iLikeIt = false;
        $listLikesUser = array();
        if ($likes) {
            $likeClass = '';
            if (Common::isMobile(false, true)) {
                $limit = 3;
                if($likes > $limit) {
                    $limit = 2;
                } else {
                    $limit = $likes;
                }
            } else {
                $limit = 4;
                if($likes > $limit) {
                    $limit = 3;
                } else {
                    $limit = $likes;
                }
            }

            $uids = User::getFriendsList($guid);
            $sql = 'SELECT u.user_id, u.name,
                           IF(u.user_id = ' . to_sql($guid) . ', 2,
                           IF(u.user_id IN (' . to_sql($uids, 'Plain') . '), 1, 0)) AS order_param, u.name_seo, u.gender
                      FROM `' . self::$tableLikes . '` AS BL
                      LEFT JOIN user AS u ON u.user_id = BL.user_id
                     WHERE BL.' . self::$tableLikesFieldId . ' = ' . to_sql($id) . '
                     ORDER BY order_param DESC, id DESC LIMIT ' . $limit;

            DB::query($sql, $index);

            $wallLikeDelimiter = '';
            $counter = 0;
            $uidsExclude = array();
            $userLike = false;

            while ($user = DB::fetch_row($index)) {
                $userLike = $user;
                $uidsExclude[] = $user['user_id'];
                $counter++;

                if($user['name'] == guser('name')) {
                    if($likes > 1) {
                        $html->parse("{$block}_you", false);
                    }
                    $iLikeIt = true;
                    $wallLikeDelimiter = ', ';
                } else {
                    if($counter == $likes && $likes > 1) {
                        $wallLikeDelimiter = ' ' . l('and') . ' ';
                    }
                    if($counter == 1) {
                        $wallLikeDelimiter = '';
                    }

                    $html->setvar("{$block}_delimiter", $wallLikeDelimiter);

                    $userName = $user['name'];
                    $userUrl = User::url($user['user_id']);
					$html->setvar("{$block}_user_id", $user['user_id']);
                    $html->setvar("{$block}_user_url", $userUrl);
                    $html->setvar("{$block}_name", $userName);

                    if($likes > 1) {
                        $html->parse("{$block}_user", true);
                    }
                    if($likes > 2) {
                        $wallLikeDelimiter = ', ';
                    }
                }
            }

            $listLikesUser = $uidsExclude;
            if($likes == 1) {

                if($iLikeIt) {
                    $wallLikeOne = l('wall_only_you_like');
                } else {
                    $nameUrl = Common::getLinkHtml(User::url($userLike['user_id'])) . $userLike['name'] . '</a>';
                    $userLikeGender = User::getInfoBasic($userLike['user_id'], 'gender');
                    if($userLikeGender == '') {
                        $userLikeGender = 'm';
                    }
                    $wallLikeOne = lSetVars('wall_only_one_person_likes' . '_' . mb_strtolower($userLikeGender, 'UTF-8'), array('name' => $nameUrl, 'url_profile' => User::url($userLike['user_id'])));
                }
                $html->setvar("{$block}_one", $wallLikeOne);
                $html->parse("{$block}_one", false);
            } elseif($likes > $limit) {
                $wallLikesCount = $likes - $limit;
                $urlStart = Common::getOption('url_main', 'path') . Common::pageUrl('blogs_post_liked', null, $id);
                $urlStart = '<a href="' . $urlStart . '">';


                $vars = array(
                    'count' => $wallLikesCount,
                    'url_start' => $urlStart,
                    'url_end' => '</a>',
                );
                $html->setvar("{$block}_and_more_people_like_this", lSetVars('wall_and_more_people_like_this', $vars));

                $html->parse("{$block}_more", true);
            } elseif ($likes > 1) {
                $html->parse("{$block}_names", true);
            }
        }

        $clLikeHidden = '';
        if($iLikeIt) {
            $clLikeHidden = 'wall_like_hidden';
            $html->parse('link_unlike');
        } else {
            $html->parse('icon_like');
        }
        $html->setvar("{$block}_hidden", $clLikeHidden);


        if ($listLikesUser) {
            $html->setvar("{$block}_user_list", implode(',', $listLikesUser));
        } else {
            $html->setvar("{$block}_user_list", '');
        }

        if ($row) {
            $html->setvar("{$block}_count", $likes);
            $html->setvar("{$block}_id", $row['live_id']);
            $html->setvar("{$block}_last_action_like", $row['last_action_like']);
        }

        $html->parse("{$block}_module_like", false);

        $html->setvar("{$block}_class", $likeClass);

        $html->parse($block, false);
    }


    static function updateWallItemLikes($wallId, $tableLikes, $field){
        if (!$wallId) {
            return;
        }
        $likes = '(SELECT COUNT(*) FROM `' . $tableLikes . '` AS l
                    WHERE l.' . $field . ' = w.item_id AND l.like = 1)';
        $likeLastAction = to_sql(date('Y-m-d H:i:s'));

        $sql = 'UPDATE wall AS w SET
                 likes_media = ' . $likes . ',
                 last_action_like_media = ' . $likeLastAction .  '
                 WHERE id = ' . to_sql($wallId);
        DB::execute($sql);
    }


    static function addLikeMediaContent($id = null, $like = null, $remove = false, $sectionWall = '', $prfId = '', $uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }

        if ($id === null) {
            $id = get_param('photo_id');
        }

        if ($like === null) {
            $like = get_param_int('like');
        }

        $info = DB::one(self::$tableBase, self::$tableBaseId . ' = ' . to_sql($id));
        if (!$info) {
            return false;
        }

        $isGroupsType = false;
        $groupId = 0;
        $groupUserId = 0;
        if (isset($info['group_id'])) {
            $isGroupsType = true;
            $groupId = $info['group_id'];
            if ($groupId) {
                $groupUserId = Groups::getInfoBasic($groupId, 'user_id');
                if (!$groupUserId) {
                    $groupId = 0;
                }
            }
        }

        $currentLike = false;
        $where = '`user_id` = ' . to_sql($uid) . ' AND `' . self::$tableLikesFieldId . '` = ' . to_sql($id);
        $sql = 'SELECT `like` FROM ' . self::$tableLikes . '
                 WHERE ' . $where;
        if (!$remove) {
            $currentLike = DB::one(self::$tableLikes, $where);
        }

        $date = date('Y-m-d H:i:s');
        if ($remove || ($currentLike && $currentLike['like'] == $like)) {
            DB::delete(self::$tableLikes, $where);
            $like = '';
        } else {
            $sqlGroup = '';
            if ($isGroupsType) {
                $sqlGroup = ', `group_id` = ' . to_sql($groupId) . ',
                               `group_user_id` = ' . to_sql($groupUserId);
            }

            $sql = 'INSERT IGNORE INTO ' . self::$tableLikes . '
                       SET `user_id` = ' . to_sql($uid) . ',
                           `' . self::$tableLikesFieldId . '` = ' . to_sql($id) . ',
                           `' . self::$tableLikesFieldUserId . '` = ' . to_sql($info['user_id']) . ',
                           `like` = ' . to_sql($like) . ',
                           `date` = ' . to_sql($date) . $sqlGroup .
                      ' ON DUPLICATE KEY UPDATE
                          `like` = ' . to_sql($like);
            DB::execute($sql);
            $like = $like ? 'Y' : 'N';
        }

        if ($sectionWall) {
            $sql = 'SELECT `id`
                      FROM `wall`
                     WHERE `section` = ' . to_sql($sectionWall) .
                     ' AND `item_id` = ' . to_sql($id);
            $wallId = DB::result($sql);

            self::updateWallItemLikes($wallId, self::$tableLikes, self::$tableLikesFieldId);
        }

        $sql = 'SELECT COUNT(*) FROM `' . self::$tableLikes .  '`
                 WHERE `like` = 1 AND `' . self::$tableLikesFieldId . '` = ' . to_sql($id);
        $countLike = DB::result($sql);

        $sql = 'SELECT COUNT(*) FROM `' . self::$tableLikes .  '`
                 WHERE `like` = 0 AND `' . self::$tableLikesFieldId . '` = ' . to_sql($id);
        $dislike = DB::result($sql);

        $data = array('like' => $countLike,
                      'dislike' => $dislike,
                      'last_action_like' => $date);

        DB::update(self::$tableBase, $data,  self::$tableBaseId . ' = ' . to_sql($id));

        $data['id'] = $prfId . $id;
        $data['my_like'] = $like;
        $data['likes'] = $countLike;

        return $data;
    }


    static function addLikeContent($id = null, $uid = null)
    {
        if ($id === null) {
            $id = get_param_int('id');
        }
        if ($uid === null) {
            $uid = guid();
        }

        $info = DB::one(self::$tableBase, '`' . to_sql(self::$tableBaseId, 'Plain') . '` = ' . to_sql($id));
        if (!$info) {
            return false;
        }

        $date = date('Y-m-d H:i:s');
        $sql = 'INSERT IGNORE INTO `' . to_sql(self::$tableLikes, 'Plain') . '`
                   SET `user_id` = ' . to_sql($uid) . ',
                       `' . to_sql(self::$tableLikesFieldId, 'Plain') . '` = ' . to_sql($id) . ',
                date = ' . to_sql($date);
        DB::execute($sql);

        $likes = DB::count(self::$tableLikes, '`' . to_sql(self::$tableLikesFieldId, 'Plain') . '` = ' . to_sql($id));

        $data = array('like' => $likes, 'last_action_like' => $date);
        DB::update(self::$tableBase, $data, '`' . to_sql(self::$tableBaseId, 'Plain') . '` = ' . to_sql($id));
        //self::sendAlert('like', $id, $uid);

        return $likes;
    }


    static function removeLikeContent($id = null, $uid = null)
    {
        if ($id === null) {
            $id = get_param_int('id');
        }
        if ($uid === null) {
            $uid = guid();
        }

        $info = DB::one(self::$tableBase, '`' . to_sql(self::$tableBaseId, 'Plain') . '` = ' . to_sql($id));
        if (!$info) {
            return false;
        }

        $where = '`user_id` = ' . to_sql($uid) .
            ' AND `' . to_sql(self::$tableLikesFieldId, 'Plain') . '` = ' . to_sql($id);
        DB::delete(self::$tableLikes, $where);

        $date = date('Y-m-d H:i:s');
        $likes = DB::count(self::$tableLikes, '`' . to_sql(self::$tableLikesFieldId, 'Plain') . '` = ' . to_sql($id));

        $data = array('like' => $likes, 'last_action_like' => $date);
        DB::update(self::$tableBase, $data, '`' . to_sql(self::$tableBaseId, 'Plain') . '` = ' . to_sql($id));

        return $likes;
    }

    static function parseComments(&$html, $id, $limit = false, $cid = 0, $lastId = null, $order = null)
    {
        global $g_user;

        $optionTemplateName = Common::getTmplName();
        $guid = $g_user['user_id'];

        $cmd = get_param('cmd');
        $isGetLoadComments = $cmd == 'get_live_stream_comment';

        $row = DB::one(self::$tableBase, '`' . self::$tableBaseId . '` = ' . to_sql($id));

        $comments = array();


        if (!$row) {// || !$row['comments_enabled']
            return;
        }


        if ($lastId === null) {
            $lastId = get_param_int('last_id');
        }

        $where = '';
        $whereSql = '';
        $whereEventSql = '';

        $numberComments = CProfilePhoto::getNumberShowComments(false, self::$typeContent);
        $loadMore = get_param_int('load_more');
        if ($loadMore || ($cmd == 'live_stream_comment_delete' && !get_param_int('cid_parent'))) {
            $numberComments = CProfilePhoto::getNumberShowCommentsLoadMore(false, self::$typeContent);
            $where .= ' AND `id` < ' . to_sql($lastId);
        } elseif($cid) {
            $where .= ' AND `id` = ' . to_sql($cid);
        } else {
            $where .= ' AND `id` > ' . to_sql($lastId);
        }


        $limitParam = get_param_int('limit');
        if ($limitParam) {
            $numberComments = $limitParam;
        }
        //var_dump_pre($limit);
        if ($limit === false) {
            $limit = ' LIMIT ' . $numberComments;
        } elseif ($limit) {
            $limit = ' LIMIT ' . $limit;
        }


        $var = self::$tableCommentsFieldId . "_comments";
        if ($html->varExists($var)) {
            $html->setvar($var, $id);
        }

        $showEventReplyCommentId = 0;
        $showCommentParentId = 0;

        $where .= ' AND `parent_id` = 0';
        $whereEventSql .= ' AND `parent_id` = 0';

        if ($order === null) {
            $order = ' ORDER BY id DESC ';
        } else {
            $order = ' ORDER BY ' . $order;
        }
        $sql = "SELECT *
                  FROM `" . self::$tableComments. "`
                 WHERE `" . self::$tableCommentsFieldId . "` = " . to_sql($id, 'Number') .
                $where . $order . $limit;
        if ($row['count_comments']) {
            $comments = DB::all($sql);
        }

        if (!$loadMore) {
            $countComments = $row['count_comments'];
            $html->setvar(self::$prfBlock . '_comments_count', $countComments);

            $lVar = 'wall_comments_count';
            if ($countComments == 1) {
                $lVar = 'wall_comments_one_count';
            }
            $html->setvar(self::$prfBlock . '_comments_count_title', lSetVars($lVar, array('comments_count' => $countComments)));
            $html->subcond(!$countComments, self::$prfBlock . '_comments_count_title_hide');
        }

        /* Show comment from event */
        $showCommentId = get_param_int('show_comment_id');
        if ($comments && $showCommentId && $isGetLoadComments) {
            $sql = "SELECT `parent_id`
                      FROM `" . self::$tableComments . "`
                     WHERE `" . self::$tableCommentsId . "` = " . to_sql($showCommentId) .
                   " LIMIT 1";
            $showCommentParentId = DB::result($sql);
            if ($showCommentParentId) {
                $showEventReplyCommentId = $showCommentId;
            } else {
                $showCommentParentId = $showCommentId;
            }
            $isExistsShowComment = false;
            foreach ($comments as $key => $comment) {
                if ($comment['id'] == $showCommentParentId) {
                    $isExistsShowComment = true;
                    break;
                }
            }

            if (!$isExistsShowComment) {
                $sql = "SELECT *
                          FROM `" . self::$tableComments . "`
                         WHERE `" . self::$tableCommentsFieldId . "` = " . to_sql($id, 'Number') . $whereEventSql .
                         ' AND `id` >= ' . to_sql($showCommentParentId) .
                       " ORDER BY id DESC";
                $comments = DB::all($sql);
                $numberComments = count($comments);
            }
        }
        /* Show comment from event */

        if (!$loadMore) {
            //krsort($comments);
        }

        $count = count($comments);
        $countRows = $count;


        $commentsLikes = CProfilePhoto::getAllLikesCommentsFromUser(self::$typeContent);
        if ($count > 0) {
            $i = 0;
            Wall::$isParseCommentsBlog = true;
            if (self::$typeContent == 'live') {
                LiveStreaming::$liveId = $row['live_id'];
            }
            foreach ($comments as $key => $comment) {

                    $comment['item_group_id'] = 0;
                    if ($i == $numberComments) {
                        break;
                    }

                    $comment['comment'] = $comment['text'];
                    $comment['date'] = $comment['dt'];

                    $commentInfo = CProfilePhoto::prepareDataComment($comment, self::$typeContent);
                    if (!$commentInfo){
                        continue;
                    }

                    $commentInfo['photo_user_id'] = $row['user_id'];

                    $parseShowEventReplyCommentId = 0;
                    if ($showEventReplyCommentId && $showCommentParentId == $comment['id']) {
                        $parseShowEventReplyCommentId = $showEventReplyCommentId;
                    }

                    if (self::$typeContent == 'live') {
                        $vars = array(
                            'id' => $row['live_id'],
                            'comment_id' => $comment['id'],
                            'last_action_like' => $row['last_action_like'],
                            'last_action_comment' => $row['last_action_comment'],
                            'comments_count' => $row['count_comments'],
                        );
                        $html->assign('comment_ls', $vars);
                    }

                    $html->setvar('comment_custom_class', self::$commentCustomClass);

                    CProfilePhoto::parseRepliesComments($html, $comment['id'], $comment['replies'], false, $commentsLikes, self::$typeContent, false, $parseShowEventReplyCommentId);

                    if (isset($commentsLikes[$comment['id']])) {
                        $commentInfo['like'] = 1;
                    }

                    CProfilePhoto::parseComment($html, $commentInfo, 'comment', self::$typeContent);

                    $i++;
            }
            Wall::$isParseCommentsBlog = false;
            if ($loadMore === 0) {
                $whereSql .= ' AND `parent_id` = 0';
                $count = DB::count(self::$tableComments, "`" . self::$tableCommentsFieldId . "` = " . to_sql($id) . $whereSql);
            }
        }

		ImAudioMessage::parseControlAudioCommentPost($html);

        //$html->parse('comment_block_end');
        if ($count > $numberComments) {
                $blockLoadMoreNumber = self::$prfBlock . '_load_more_comments_number';
                if ($html->blockExists($blockLoadMoreNumber)) {
                    $lPrevious = self::$typeContent == 'live' ? l('view_next_comments') : l('view_previous_comments');
                    $html->setvar("{$blockLoadMoreNumber}_title", $lPrevious);
                    $vars = array(
                        'view_number' => $numberComments,
                        'all_number' => $count
                    );
                    $html->setvar($blockLoadMoreNumber, lSetVars('view_previous_comments_number', $vars));
                    $html->parse($blockLoadMoreNumber, false);
                }
                $html->parse(self::$prfBlock . '_load_more_comments', false);
        } else {
            //$html->parse('items_comment_no_border');
        }

        if (!$loadMore) {
            $numberCommentsFrmShow = Common::getOption('number_comments_show_bottom_frm', "{$optionTemplateName}_live_settings");
            if (!$numberCommentsFrmShow) {
                $numberCommentsFrmShow = 2;
            }
            $html->setvar('number_comments_frm_show', $numberCommentsFrmShow);
            if ($row['count_comments']) {
                $blockFeedBottomFrm = self::$prfBlock . '_feed_comment_bottom_frm_show';
                $commentsCount = count($comments);

                if ($commentsCount > $numberCommentsFrmShow) {
                    $html->parse($blockFeedBottomFrm, false);
                }
            } else {

            }
            $html->parse(self::$prfBlock . '_feed_comment_top_frm_show', false);
        }

        $block = self::$prfBlock . '_comments_enabled';
        $html->parse($block, false);


        return $comments;
    }

    static function parseCommentsReplies(&$html, $cid = null)
    {
        global $g_user;

        if ($cid === null) {
            $cid = get_param_int('comment_id');
        }
        if (!$cid) {
            return;
        }

        $comment = DB::one(self::$tableComments, '`' . self::$tableCommentsId  . '` = ' . to_sql($cid));
        if (!$comment) {
            return;
        }

        if (self::$typeContent == 'live') {
            LiveStreaming::$liveId = get_param_int('live_id');
        }
        if (CProfilePhoto::parseRepliesComments($html, $cid, $comment['replies'], true, null, self::$typeContent)){
            $html->parse('comment');
        }
    }


	function parseBlock(&$html) {

		global $g_user;


        parent::parseBlock($html);

	}
}