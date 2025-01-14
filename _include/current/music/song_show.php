<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicSongShow extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $song_id = get_param('song_id');

        $song = DB::row("SELECT s.user_id AS song_user_id, s.*, m.* ".
            "FROM music_song as s, music_musician as m ".
            "WHERE s.song_id=" . to_sql($song_id, 'Number') . " AND m.musician_id = s.musician_id LIMIT 1");
        if($song)
        {
            $html->setvar('uploaded_name', User::getInfoBasic($song['song_user_id'], 'name'));
            $html->setvar('song_id', $song['song_id']);
        	$html->setvar('song_title', strcut(to_html($song['song_title']), 32));
        	$html->setvar('song_title_full', to_html(he($song['song_title'])));
            $html->setvar('song_year', to_html($song['song_year']));

            $about = to_html(trim(he($song['song_about'])));
            $about_short = strcut($about, 290);
            $html->setvar('song_about', $about_short);
            $html->setvar('song_about_full', $about);
            $html->setvar('about_collapse', ($about == $about_short) ? 0 : 1);

            $html->setvar('song_filename', $g['path']['url_files'] . CMusicTools::song_filename($song['song_id']));
            $html->setvar('song_length', $song['song_length']);
            $html->setvar('song_player',
                CMusicTools::song_player(
                    $song['song_id'],
                    $song['song_length'],
                    1,
                    "BigClipPlayer.swf",
                    264,
                    26));

            $html->setvar('musician_id', $song['musician_id']);
            $html->setvar('musician_name', strcut(to_html($song['musician_name']), 48));
            $html->setvar('musician_name_full', to_html(he($song['musician_name'])));

            $rating = $song['song_rating'];

            for($rating_n = 1; $rating_n <= 10; ++$rating_n)
                $html->setvar('music_song_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

            if(DB::result("SELECT vote_id FROM music_song_vote WHERE song_id = ".$song['song_id']." AND user_id = ".$g_user['user_id']." LIMIT 1"))
                $html->setvar('music_song_rating_caps', ', readOnly:true');

        	if($g_user['user_id'] == $song['user_id']) {
                $html->parse('song_edit', false);
                $html->parse('song_functions', false);
            }

            $image = DB::row("SELECT * FROM music_song_image WHERE song_id=" . $song['song_id'] . " ORDER BY image_id DESC LIMIT 1");
            if($image)
            {
                $html->setvar("image_thumbnail_b", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th_b.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_b.jpg");
                $html->parse("image");
            }
            else
            {
            	$html->parse("no_image");
            }
        }
        else
        {
        	redirect('music.php');
        }

		parent::parseBlock($html);
	}
}

