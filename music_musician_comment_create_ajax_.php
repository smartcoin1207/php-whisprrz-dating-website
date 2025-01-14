<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

require_once("./_include/current/music/tools.php");

function do_action()
{
	global $g_user;

	$musician_id = intval(get_param('musician_id'));
	$comment_text = get_param('comment_text');

	if($musician_id && $comment_text)
	{
        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
        if($musician)
        {
            $comment_text = Common::filter_text_to_db($comment_text, false);
            DB::execute("INSERT INTO music_musician_comment SET musician_id=".$musician['musician_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql($comment_text).
                                ", created_at = NOW()");

            $id = DB::insert_id();

            Wall::setSiteSection('musician');
            Wall::setSiteSectionItemId($musician_id);
            Wall::addItemForUser($musician_id, 'musician', guid());

            Wall::add('musician_comment', $id);

            CMusicTools::update_musician($musician['musician_id']);
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>