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
	global $g;
	global $l;

	$id = get_param('song_id');
	$song = DB::row("SELECT * FROM music_song WHERE song_id=" . to_sql($id, 'Number') . " LIMIT 1");
	if($song)
	{
		$rating = intval(get_param('rating'));

		if($rating and $rating > 0 and $rating <=10)
		{
			DB::query("SELECT * FROM music_song_vote WHERE user_id=" . $g_user['user_id'] . " AND song_id = " . $song['song_id'] . " LIMIT 1");
			if($place_vote = DB::fetch_row())
			{
				DB::execute("UPDATE music_song_vote SET vote_rating = " . to_sql($rating, 'Number') . ", updated_at = NOW() ".
					"WHERE user_id=" . $g_user['user_id'] . " AND song_id = " . $song['song_id'] .
					" LIMIT 1");
			}
			else
			{
				DB::execute("INSERT INTO music_song_vote SET user_id=" . $g_user['user_id'] . ", song_id = " . $song['song_id'] .
					", vote_rating = " . to_sql($rating, 'Number') . ", created_at = NOW(), updated_at = NOW()");
			}

			CMusicTools::update_song($song['song_id']);

            Wall::addItemForUser($song['song_id'], 'music', guid());

		}

		die('ok');
	}
	else
		die('error');
}

do_action();

include("./_include/core/main_close.php");

?>