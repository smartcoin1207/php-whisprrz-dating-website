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

	$song_id = intval(get_param('song_id'));
	$comment_text = get_param('comment_text');

	if($song_id && $comment_text)
	{
        $song = DB::row("SELECT * FROM music_song WHERE song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
        if($song)
        {
            $comment_text = Common::filter_text_to_db($comment_text, false);
            DB::execute("INSERT INTO music_song_comment SET song_id=".$song['song_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql($comment_text).
                                ", created_at = NOW()");

            $id = DB::insert_id();

            Wall::setSiteSection('music');
            Wall::setSiteSectionItemId($song['song_id']);
            Wall::add('music_comment', $id);
            Wall::addItemForUser($song['song_id'], 'music', guid());

            CMusicTools::update_song($song['song_id']);
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>