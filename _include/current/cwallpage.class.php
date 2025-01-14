<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CWallPage extends CHtmlBlock {

    static $noItems = false;

    function parseBlock(&$html)
    {
        global $p;
        global $g_user;

        $optionTemplateName = Common::getTmplName();
        $tmplWallType = Common::getOptionTemplate('wall_type');

        $guid = guid();

        $uid = get_param('uid', $guid);

        if (!User::isExistsByUid($uid)) {
            redirect(Common::pageUrl('wall'));
        }

        $groupId = 0;
        $groupInfo = array();
        $isParseModuleItrmAdd = true;
        if ($tmplWallType == 'edge') {
            $groupId = Groups::getParamId();
            if ($groupId) {
                $groupInfo = Groups::getInfoBasic($groupId);
                if ($groupInfo['page'] && $groupInfo['user_id'] != $guid) {
                    $isParseModuleItrmAdd = false;
                }
            }
        }

        if ($html->varExists('wall_group_id')) {
            $html->setvar('wall_group_id', $groupId);
        }

        if ($html->varExists('guid')) {
            $html->setvar('guid', $guid);
        }

        if ($html->varExists('number_comments_frm_show')) {
            $html->setvar('number_comments_frm_show', Common::getOptionTemplateInt('wall_number_comments_to_show_bottom_frm'));
        }

        $html->setvar('max_filesize', Common::getOption('photo_size'));

        if ($optionTemplateName == 'edge') {
            TemplateEdge::parseColumn($html, $uid);
        }

        $wallSee = Wall::isOnlySeeFriends($uid);
        $wallPost = Wall::isOnlyPostFriends($uid);
        $isOnlyFriendsWallPosts = intval(Common::isOptionActive('only_friends_wall_posts'));

        if ($html->varExists('is_only_friends_wall_posts')) {
            $html->setvar('is_only_friends_wall_posts', $isOnlyFriendsWallPosts);
        }

        if ($html->varExists('is_friend')) {
            $html->setvar('is_friend', intval($uid == $guid || User::isFriend ($uid, $guid)));
        }

        if ($html->varExists('is_only_friends_wall')) {
            $isOnlyFriendsWall = 'yes';
            if (!$wallSee) {
                $isOnlyFriendsWall = 'no';
            } elseif (!$wallPost) {
                $isOnlyFriendsWall = 'no_post';
            }
            $html->setvar('is_only_friends_wall', $isOnlyFriendsWall);
        }

        //if (!$wallSee) {
            //redirect('search_results.php?display=profile_info&uid=' . $uid);
        //}

        /* CProfilePhoto Init */
        if ($html->varExists('photo_rating_enabled')) {
            $html->setvar('photo_rating_enabled', intval(Common::isOptionActive('photo_rating_enabled')));
        }
        /* CProfilePhoto Init */

        if($guid) {
            if ($groupId  && $groupInfo['page'] && $groupInfo['user_id'] == $guid) {
                $vars = array(
                    'group_id' => $groupId,
                    'photo' => GroupsPhoto::getPhotoDefault($guid, $groupId, 'r'),
                    'name'  => toJsL($groupInfo['title']),
                    'age'   => '',
                    'url'   => Groups::url($groupId, $groupInfo)
                );
            } else {
                $vars = array(
                    'group_id' => 0,
                    'photo' => User::getPhotoDefault($guid, 'r'),
                    'name'  => $g_user['name'],
                    'age'   => $g_user['age'],
                    'url'   => User::url($guid)
                );
            }

            foreach ($vars as $key => $value) {
                $var = $key . '_guid';
                if ($html->varExists($var)) {
                    $html->setvar($var, $value);
                }
            }

        }

        if ($html->varExists('photo_file_size_limit')) {
            $maxFileSize = Common::getOption('photo_size');
            $html->setvar('photo_file_size_limit', mb_to_bytes($maxFileSize));
            $html->setvar('max_photo_file_size_limit', lSetVars('max_file_size', array('size'=>$maxFileSize)));
        }

        if ($html->varExists('is_profile_wall')) {
            $html->setvar('is_profile_wall', intval($p != 'wall.php'));
        }

        if ($html->varExists('is_friends_profile_wall')) {
            $html->setvar('is_friends_profile_wall', intval(User::isFriend($uid, guid())));
        }

        if ($tmplWallType == 'edge') {//Parse template for clone comment
            $html->setvar('comments_reply_item_user_photo', '1px.png');
            $html->setvar('wall_item_comment_photo_r', '1px.png');
            $html->setvar('comment_guid_photo', '1px.png');
            $html->parse('comments_reply_item_delete', false);
            $html->parse('comments_reply_item', false);
            $html->parse('comments_reply_list', false);
            $html->parse('wall_comment_delete', false);
            $html->parse('wall_item_comment', false);
        }

        $html->setvar('url_page_current', Common::urlPage());

        $html->setvar('wall_user_id', $uid);
        $html->setvar('wall_comments_preload_count', Wall::getCommentsPreloadCount());


        $block = 'wall_other';
        $blockTitle = 'wall_title_other';
        $blockAdd = 'wall_add_other';
        $blockFriends = 'wall_friends_other';
        $blockWallDistance = 'wall_filter_distance';

        if ($uid == guid()) {
            $block = 'wall_my';
            $blockTitle = 'wall_title_my';
            $blockAdd = 'wall_add_my';
            $blockFriends = 'wall_friends_my';
            $html->parse('wall_search_for_friends');
            if($p != 'wall.php') {
                $html->parse('wall_header_dropbox');
                $html->parse('wall_public_swingbook_link');
            }
            $html->parse('wall_go_lives');

        } else {

            $row = User::freeAccessApply(User::getInfoBasic($uid));

            $wallAddOther = lSetVars('wall_add_other', array('name' => User::nameShort($row['name'])));
            $html->setvar('wall_add_other', $wallAddOther);
            $html->setvar('wall_name_news_feed', lSetVars('wall_name_news_feed', array('name' => User::nameShort($row['name']))));

            if (guid()) {
                if (User::isFriend(guid(), $uid)) {
                    $html->parse('wall_unfriend');
                } else {
                    $html->parse('wall_add_friend');
                }
                User::parseImLink($html, $row['user_id'], $row['type'], $row['gold_days'], 'wall_im');
            }
        }

        //date for calendar search end
        $distances['all'] = 'All';
        $distances['5'] = '5 mi';
        $distances['10'] = '10 mi';
        $distances['15'] = '15 mi';
        $distances['20'] = '20 mi';
        $distances['50'] = '50 mi';
        $distances['100'] = '100 mi';
        $distances['200'] = '200 mi';
        $options = '';

        $radius = get_param('radius', '');
        if(!$radius) {
            $radius = 'all';
        }

        foreach ($distances as $key => $distance) {
            $selected = "";
            if($radius == $key) {
                $selected = "selected";
            }
            $option = '<option value="' . $key . '" ' . $selected . '>' . $distance . '</option>';
            $options .= $option;
        }
        
        $html->setvar('wall_filter_distance_options', $options);

        //distance for calendar search end
        if($p == 'wall.php') {
            $html->parse($blockWallDistance, false);
        }

        User::parseUserinfoModule($html, $uid);

        $html->parse($block);
        $html->parse($blockTitle);
        $html->parse($blockAdd);
        $html->parse($blockFriends);    

        if($p == 'wall.php') {
             $html->parse('wall_profile_swingbook_link');
        }

        global $g;

        if(!($p == "event_wall.php" || $p == "hotdate_wall.php" || $p == "partyhou_wall.php") && !(isset($g['is_page_group'])  && $g['is_page_group'])) {
            $html->parse('wall_header');
        }

        $block = 'wall_head';
        if ($p == 'wall.php' && $html->blockExists($block)) {
            if (!$isOnlyFriendsWallPosts) {
                $mode = Wall::getChangeMode();
                $html->setvar('mode_value', $mode);
                $html->setvar("{$block}_mode", l("menu_{$mode}"));
                $html->parse("{$block}_{$mode}", false);
                $html->parse("{$block}_mode", false);
            }
            $html->parse($block);
        }

        $itemWall = get_param('wall_item');//scroll to Item for Urban
        $html->setvar('only_see', $wallSee ? 'yes' : 'no');


        if ($html->varExists('wall_single_item')) {//EDGE
            $html->setvar('wall_single_item', Wall::getSingleItemMode());
        }
        $isParseUpdater = !Wall::isSingleItemMode();
        if ($tmplWallType == 'edge') {
            $isParseUpdater = true;
        }
        if ($isParseUpdater) {
            $html->parse('wall_updater');
            $html->parse('wall_load_old_items');
            if ($itemWall) {
                $html->setvar('wall_scroll_to_item_id', $itemWall);
                $html->parse('wall_scroll_to_item');
            }
            if ($wallSee) {
                if ($wallPost) {
                    if(Common::isOptionActive('gallery')) {
                        $html->parse('wall_add_comment_image');
                    }
                    $isParseWallAddComment = true;
                    if ($tmplWallType == 'edge' && $uid != $guid){
                        if ($groupId) {
                            $isParseWallAddComment = true;
                            if (!$groupInfo['page'] && $groupInfo['private'] == 'N') {
                                $isParseWallAddComment = Groups::isSubscribeUser($guid, $groupId);
                            }
                        } else {
                            $isParseWallAddComment = !User::isEntryBlocked($guid, $uid);
                        }
                    }

                    Wall::$isShowPostInput = $isParseWallAddComment;
                    if ($isParseWallAddComment) {
                        $html->parse('wall_add_comment');
                    }
                }
                $html->parse('wall_gallery');
                $html->parse('wall_history');
            }
        }

        if ($wallSee) {
            $html->parse('only_friends_posts_one');
            $html->parse('only_friends_posts_two');
        }
        if(Common::isOptionActive('invite_friends')) {
            $html->parse('invite_on');
        }

        if (self::$noItems && $html->blockExists('wall_no_items')) {
            $html->parse('wall_no_items');
        }

        $item = get_param_int('item');
        if (!$item && $isParseModuleItrmAdd && $html->blockExists('wall_item_add_field')) {
            $html->parse('wall_item_add_field', false);
        } else {
            Wall::$isShowPostInput = false;
        }

        if ($item && $html->varExists('wall_notif_cid')) {
            $html->setvar('wall_notif_cid', get_param_int('ncid'));
            $html->setvar('wall_notif_pcid', get_param_int('npcid'));
        }

        parent::parseBlock($html);
    }

}