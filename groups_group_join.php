<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/groups/tools.php");

function do_action()
{
	global $g_user;
	
	$group_id = intval(get_param('group_id'));
	
	if($group_id)
	{
        CGroupsTools::join_group($group_id);
	}
	$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "groups.php"; 
	redirect($return_to);
}

do_action();

include("./_include/core/main_close.php");
