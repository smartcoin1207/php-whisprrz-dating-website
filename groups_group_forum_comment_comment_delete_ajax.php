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
        /*$comment = DB::row("SELECT cc.*, m.forum_id FROM groups_forum as m, groups_forum_comment_comment as cc, groups_forum_comment as c, groups_group as g WHERE cc.comment_id=" . to_sql($comment_id, 'Number') . 
            " AND cc.parent_comment_id = c.comment_id " .    
            " AND m.forum_id = c.forum_id " .
            " AND m.group_id = g.group_id " .
            " AND (g.user_id = " . $g_user['user_id'] . " OR c.user_id = " . $g_user['user_id'] . " OR cc.user_id = " . $g_user['user_id'] . " )" . 
            " LIMIT 1");
        if($comment)
        {
            DB::execute("DELETE FROM groups_forum_comment_comment WHERE comment_id=".$comment['comment_id']." LIMIT 1");
            
            CGroupsTools::update_forum($comment['forum_id']);
            
            echo 'ok';
            die();
        }*/
        CGroupsTools::delete_forum_comment_comment($comment_id);
        echo 'ok';
        die();     
	}
}

do_action();

include("./_include/core/main_close.php");

?>