<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

require_once("./_include/current/hotdates/tools.php");

function do_action()
{
	global $g_user;

	$comment_id = intval(get_param('comment_id'));
	$comment_text = get_param('comment_text');

	if($comment_id && $comment_text)
	{
        $comment = DB::row("SELECT * FROM hotdates_hotdate_comment WHERE comment_id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
        if($comment)
        {
            DB::execute("INSERT INTO hotdates_hotdate_comment_comment SET parent_comment_id=".$comment['comment_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql(CHotdatesTools::filter_text_to_db($comment_text)).
                                ", created_at = NOW()");

            $id = DB::insert_id();
            Wall::setSiteSection('hotdate');
            Wall::setSiteSectionItemId($comment['hotdate_id']);
            Wall::add('hotdate_comment_comment', $id);


            CHotdatesTools::update_hotdate($comment['hotdate_id']);


            echo 'ok';
            die();
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>