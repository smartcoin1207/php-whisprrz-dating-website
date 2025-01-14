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
require_once("./_include/current/music/song_list.php");
require_once("./_include/current/music/musician_list.php");

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
		
		$query = get_param('query');
		
		if($query)
		{
            $sql_base = CMusicTools::musicians_by_query_sql_base($query);
            if(CMusicTools::count_from_sql_base($sql_base))
            {
            	$html->parse('search_header');
            }
            else
            {
            	$html->parse('search_no_results_header');
            }
		}
		else
		{
            $html->parse('no_search_header');
		}

		parent::parseBlock($html);
	}
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_search_and_upload.html");
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

if(get_param('query'))
{
	$music_musician_list = new CMusicMusicianList("music_song_list", $g['tmpl']['dir_tmpl_main'] . "_music_musician_list.html");
	$music_musician_list->m_list_type = "search";
	$page->add($music_musician_list);
}
else
{
	$music_song_list = new CMusicSongList("music_song_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_list.html");
	$music_song_list->m_list_type = "recent";
	$page->add($music_song_list);
}

include("./_include/core/main_close.php");
