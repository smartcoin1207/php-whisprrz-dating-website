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
	$comment_text = get_param('comment_text');

	if($comment_id && $comment_text)
	{
        $comment = DB::row("SELECT * FROM groups_forum_comment WHERE comment_id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
        if($comment)
        {
	        $forum = DB::row("SELECT * FROM groups_forum WHERE forum_id=" . to_sql($comment['forum_id'], 'Number') . " LIMIT 1");
        	if($forum)
	        {
	            $group = CGroupsTools::retrieve_group_by_id($forum['group_id']);
	            if(($group['user_id'] == $g_user['user_id'] || CGroupsTools::is_group_member($group['group_id'])))
	            {
		        	DB::execute("INSERT INTO groups_forum_comment_comment SET parent_comment_id=".$comment['comment_id'].
		                                ", user_id=".$g_user['user_id'].
		                                ", comment_text=".to_sql(CGroupsTools::filter_text_to_db($comment_text)).
		                                ", created_at = NOW()");

                    $id = DB::insert_id();
                    Wall::setSiteSection('group');
                    Wall::setSiteSectionItemId($group['group_id']);
                    Wall::add('group_forum_post_comment', $id);

		            CGroupsTools::update_forum($comment['forum_id']);

		            echo 'ok';
		            die();
	            }
	        }
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>