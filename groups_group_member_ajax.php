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
	
    $cmd = get_param('cmd');
    $group_id = intval(get_param('group_id'));
    
    if($group_id)
    {
        if($cmd == "remove")
	    {
	        $user_id = intval(get_param('user_id'));
	    	
	        if($g_user['user_id']==DB::result("SELECT user_id FROM groups_group WHERE group_id=".to_sql($group_id,"Number"))) CGroupsTools::delete_group_member($group_id, $user_id);
	        echo 'ok';
	        die();
	    }
    }
}

do_action();

include("./_include/core/main_close.php");