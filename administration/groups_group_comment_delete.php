<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/groups/tools.php");

function do_action()
{
	global $g_user;

	$groupId = get_param_int('group_id');
	$delimiter = '&';
	$id = intval(get_param('comment_id'));
	if($id){
        CGroupsTools::delete_group_social_comment($id, true);
		$return_to = "groups_group_comments.php?action=delete";
	} else {
		$delimiter = '?';
		$return_to = "groups_group_comments.php";
	}
	if ($groupId) {
		$return_to .= $delimiter . 'group_id=' . $groupId;
	}
	if (!Common::isAdminModer()) {
		$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "administration/groups_groups.php"; 
	}

    redirect($return_to);
}

do_action();

include("../_include/core/administration_close.php");
