<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/music/custom_head.php");
require_once("./_include/current/music/header.php");
require_once("./_include/current/music/sidebar.php");
require_once("./_include/current/music/song_list.php");

class CMusic extends CHtmlBlock
{
	function action()
	{
		global $g_user;

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $sql_base = CMusicTools::songs_top_plays_sql_base();
        $songs = CMusicTools::retrieve_from_sql_base($sql_base, 2);
        $song_n = 1;

        foreach($songs as $song)
        {
        	$html->setvar('song_id', $song['song_id']);
            $html->setvar('song_title', strcut(to_html($song['song_title']), 20));
            $html->setvar('song_title_full', to_html($song['song_title']));
            $html->setvar('song_length', $song['song_length']);
            $html->setvar('song_n_plays', $song['song_n_plays']);
            $html->setvar('song_n_comments', $song['song_n_comments']);
            $html->setvar('song_player',
                CMusicTools::song_player(
                    $song['song_id'],
                    $song['song_length'],
                    2,
                    "MiddleClipPlayer.swf",
                    163,
                    26));

            $html->setvar('musician_id', $song['musician_id']);
            $html->setvar('musician_name', strcut(to_html($song['musician_name']), 12));
            $html->setvar('musician_name_full', to_html($song['musician_name']));

            $rating = $song['song_rating'];

            for($rating_n = 1; $rating_n <= 10; ++$rating_n)
                $html->setvar('music_song_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

            $images = CMusicTools::song_images($song['song_id'], $song['musician_id']);
            $html->setvar("image_thumbnail_b", $images["image_thumbnail_b"]);

            if($song_n == count($songs))
                $html->parse("song_last");

            $html->parse("song");

            ++$song_n;
        }

		parent::parseBlock($html);
	}
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$music_custom_head = new CMusicCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_music_custom_head.html");
$header->add($music_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$music_header = new CMusicHeader("music_header", $g['tmpl']['dir_tmpl_main'] . "_music_header.html");
$page->add($music_header);
$music_sidebar = new CMusicSidebar("music_sidebar", $g['tmpl']['dir_tmpl_main'] . "_music_sidebar.html");
$music_sidebar->m_first_block = "most_discussed";
$music_sidebar->m_second_block = "top_rated";
$page->add($music_sidebar);

$music_song_list = new CMusicSongList("music_song_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_list.html");
$music_song_list->m_list_type = "recent";
$page->add($music_song_list);

include("./_include/core/main_close.php");
