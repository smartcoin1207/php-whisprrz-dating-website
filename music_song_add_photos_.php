<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/music/custom_head.php");
require_once("./_include/current/music/header.php");
require_once("./_include/current/music/sidebar.php");
require_once("./_include/current/music/song_show.php");
require_once("./_include/current/music/song_image_list.php");
require_once("./_include/current/music/tools.php");

class CMusic extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
        	$song_id = intval(get_param('song_id'));
            if($song_id)
            {
				$song = DB::row("SELECT * FROM music_song as s, music_musician as m WHERE s.song_id=" . to_sql($song_id, 'Number') .
		            " AND s.musician_id = m.musician_id " .
		            " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ") LIMIT 1");
                if($song)
                {
                    $time = DB::result('SELECT NOW()');
                    for($image_n = 1; $image_n <= 4; ++$image_n)
                    {
                        $name = "image_" . $image_n;
                        CMusicTools::do_upload_song_image($name, $song_id, $time);
                    }

                    redirect('music_song_show.php?song_id=' . $song['song_id']);
                }
            }
            redirect('music.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $song_id = intval(get_param('song_id', 0));
        $song = DB::row("SELECT * FROM music_song WHERE song_id = " . to_sql($song_id, 'Number') . " LIMIT 1");

        if($song)
        {
            $html->setvar('song_id', $song['song_id']);
        	$html->setvar('song_title', $song['song_title']);
            $html->setvar('song_about', $song['song_about']);
        }
		else
            redirect('music.php');

		parent::parseBlock($html);
	}
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_song_add_photos.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$music_custom_head = new CMusicCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_music_custom_head.html");
$header->add($music_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$music_header = new CMusicHeader("music_header", $g['tmpl']['dir_tmpl_main'] . "_music_header.html");
$page->add($music_header);
$music_sidebar = new CMusicSidebar("music_sidebar", $g['tmpl']['dir_tmpl_main'] . "_music_sidebar.html");
$page->add($music_sidebar);
$music_song_show = new CMusicSongShow("music_song_show", $g['tmpl']['dir_tmpl_main'] . "_music_song_show.html");
$page->add($music_song_show);
$music_song_image_list = new CMusicSongImageList("music_song_image_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_image_list.html");
$music_song_show->add($music_song_image_list);

include("./_include/core/main_close.php");
