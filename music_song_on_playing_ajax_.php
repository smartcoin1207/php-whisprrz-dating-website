<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/music/tools.php");

function do_action()
{
	$song_id = intval(get_param('song_id'));
	
	if($song_id)
	{
        $song = DB::row("SELECT * FROM music_song WHERE song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
        if($song)
        {
            DB::execute("UPDATE music_song SET song_n_plays = song_n_plays + 1 WHERE song_id=".$song['song_id']. " LIMIT 1");
            
            CMusicTools::update_musician($song['musician_id']);
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>