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
	$comment_text = get_param('comment_text');

	if($group_id && $comment_text)
	{
        $group = DB::row("SELECT * FROM groups_group WHERE group_id=" . to_sql($group_id, 'Number') . " LIMIT 1");
        if($group && ($group['user_id'] == $g_user['user_id'] || CGroupsTools::is_group_member($group['group_id'])))
        {
            DB::execute("INSERT INTO groups_group_comment SET group_id=".$group['group_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql(CGroupsTools::filter_text_to_db($comment_text)).
                                ", created_at = NOW()");

            $id = DB::insert_id();
            Wall::setSiteSection('group');
            Wall::setSiteSectionItemId($group_id);
            Wall::add('group_wall', $id);

            CGroupsTools::update_group($group['group_id']);

            echo 'ok';
            die();
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>