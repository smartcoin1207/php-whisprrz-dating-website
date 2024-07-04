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
	
	$comment_id = intval(get_param('comment_id'));
	
	if($comment_id)
	{
        CGroupsTools::delete_group_comment($comment_id);
            
        echo 'ok';
        die();
	}
}

do_action();

include("./_include/core/main_close.php");

?>