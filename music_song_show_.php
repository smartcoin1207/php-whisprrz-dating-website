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
require_once("./_include/current/music/song_show.php");
require_once("./_include/current/music/song_image_list.php");
require_once("./_include/current/music/tools.php");
require_once("./_include/current/music/song_list.php");
require_once("./_include/current/music/song_comment_list.php");

class CMusic extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $song_id = get_param('song_id');

        $song = DB::row("SELECT * FROM music_song as s, music_musician as m WHERE s.song_id=" . to_sql($song_id, 'Number') . " AND s.musician_id = m.musician_id LIMIT 1");
        if($song)
        {
            $html->setvar('song_id', $song['song_id']);
            $html->setvar('song_title', strcut(to_html($song['song_title']), 32));
            $html->setvar('musician_name', strcut(to_html($song['musician_name']), 32));
            $html->setvar('user_name', guser('name'));
            $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault(guid(), "r"));
        }
        else
            redirect('home.php');

		parent::parseBlock($html);
	}
}

$song_id = get_param('song_id');
$song = DB::row("SELECT s.*, m.* ".
    "FROM music_song as s, music_musician as m ".
    "WHERE s.song_id=" . to_sql($song_id, 'Number') . " AND m.musician_id = s.musician_id LIMIT 1");
if($song)
{
    global $g;
    $g['main']['title'] = html_meta_sanitize($g['main']['title'] . ' :: ' . $song['musician_name'] . ' :: ' . $song['song_title']);
    $g['main']['description'] = html_meta_sanitize($song['song_about']);
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_song_show.html");
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

$music_song_list = new CMusicSongList("music_song_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_list.html");
$music_song_list->m_musician_id = DB::result("SELECT musician_id FROM music_song WHERE song_id=" . to_sql(get_param('song_id'), 'Number') . " LIMIT 1");;
$music_song_list->m_exclude_song_id = get_param('song_id');
$page->add($music_song_list);
$music_song_comment_list = new CMusicSongCommentList("music_song_comment_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_comment_list.html");
$page->add($music_song_comment_list);

include("./_include/core/main_close.php");
