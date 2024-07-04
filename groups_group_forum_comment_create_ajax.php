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
	$forum_id = intval(get_param('forum_id'));
	$comment_text = get_param('comment_text');
	
	if($forum_id && $comment_text)
	{
		CGroupsTools::create_forum_comment($forum_id, $comment_text);
                
        echo 'ok';
        die();
	}
}

do_action();

include("./_include/core/main_close.php");

?>