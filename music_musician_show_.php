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
require_once("./_include/current/music/musician_show.php");
require_once("./_include/current/music/musician_image_list.php");
require_once("./_include/current/music/tools.php");
require_once("./_include/current/music/song_list.php");
require_once("./_include/current/music/musician_comment_list.php");

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

        $musician_id = get_param('musician_id');

        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
        if($musician)
        {
        	$html->setvar('musician_id', $musician['musician_id']);
        	$html->setvar('musician_name', strcut(to_html($musician['musician_name']), 32));


            $html->setvar('user_name', guser('name'));
            $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault(guid(), "r"));

            $sql_base = CMusicTools::songs_by_musician_sql_base($musician['musician_id']);
            $n_results = CMusicTools::count_from_sql_base($sql_base);
            if($n_results)
                $html->parse('musician_songs');
        }
        else
            redirect('home.php');

		parent::parseBlock($html);
	}
}

$musician_id = get_param('musician_id');
$musician = DB::row("SELECT m.*, c.*, cn.* ".
    "FROM music_musician as m, music_category as c, geo_country as cn ".
    "WHERE m.musician_id=" . to_sql($musician_id, 'Number') . " AND m.category_id = c.category_id AND ".
    "m.country_id = cn.country_id LIMIT 1");
if($musician)
{
    global $g;
    $g['main']['title'] = html_meta_sanitize($g['main']['title'] . ' :: ' . $musician['musician_name']);
    $g['main']['description'] = html_meta_sanitize($musician['musician_about']);
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_musician_show.html");
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
$music_musician_show = new CMusicMusicianShow("music_musician_show", $g['tmpl']['dir_tmpl_main'] . "_music_musician_show.html");
$page->add($music_musician_show);
$music_musician_image_list = new CMusicMusicianImageList("music_musician_image_list", $g['tmpl']['dir_tmpl_main'] . "_music_musician_image_list.html");
$music_musician_show->add($music_musician_image_list);

$music_musician_song_list = new CMusicSongList("music_musician_song_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_list.html");
$music_musician_song_list->m_need_not_found_message = false;
$page->add($music_musician_song_list);
$music_musician_comment_list = new CMusicMusicianCommentList("music_musician_comment_list", $g['tmpl']['dir_tmpl_main'] . "_music_musician_comment_list.html");
$music_musician_comment_list->m_need_not_found_message = false;
$page->add($music_musician_comment_list);

include("./_include/core/main_close.php");
