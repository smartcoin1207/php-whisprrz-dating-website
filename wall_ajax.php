<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

$siteGuid = get_param('site_guid', false);
if ($siteGuid !== false && $siteGuid != guid()) {
    echo getResponseAjaxByAuth(false);
    die();
}

$uid = get_param('wall_uid', '');

$tmplName = Common::getTmplName();
$numberComments = Common::getOption('wall_comments_by_default');
Wall::setCommentsLoadCount($numberComments);

$noAccessGroup = false;
if (Common::isOptionActiveTemplate('groups_social_enabled')) {
    $groupId = Groups::getParamId();
    if ($groupId) {
        $noAccessGroup = !Groups::isUserAccess($groupId);
    }
}

if (Common::isOptionTemplateSet('urban')) {
	Wall::setCommentsPreloadCount($numberComments);
    if ($uid == guid()) {
        $pageWall = Wall::ajaxPage();
    } else {
        if($noAccessGroup) {
            echo get_json_encode('no_post_group');
        } elseif (!Wall::isOnlySeeFriends($uid)) {
            echo get_json_encode('no');
        } elseif (!Wall::isOnlyPostFriends($uid)) {
            echo get_json_encode('no_post');
        } else {
            $pageWall = Wall::ajaxPage();
        }
    }
} else {
    $isSee = get_param('is_see', '');
    if (Wall::isOnlySeeFriends($uid)) {
        if ($isSee == 'no') {
            echo get_json_encode('no');
        } else {
            $pageWall = Wall::ajaxPage();
        }
    } elseif ($isSee == 'yes') {
        echo get_json_encode('no');
    }
}

include('./_include/core/main_close.php');