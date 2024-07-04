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
require_once("./_include/current/music/musician_list.php");

class CMusic extends CHtmlBlock
{
	var $m_music_song_list;
	var $m_music_musician_list;

	function action()
	{
		global $g_user;

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $from = get_param("from");
		if($from == "music_my")
		{
			$html->parse('song_list_from_my_title');
			$html->parse('song_list');
		}
		else if($this->m_music_musician_list->m_n_results || $this->m_music_song_list->m_n_results)
        {
			if($this->m_music_musician_list->m_n_results)
	        {
	            $html->parse('musician_list');
	        }

	        if($this->m_music_song_list->m_n_results)
			{
				$html->parse('song_list_search_title');
				$html->parse('song_list');
			}
        }
        else
        {
        	$html->parse('song_list_not_found_title');
        	$html->parse('song_list');
        }

		parent::parseBlock($html);
	}
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_search.html");
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

$music_song_list = new CMusicSongList("music_song_list", $g['tmpl']['dir_tmpl_main'] . "_music_song_list.html");
$music_song_list->m_list_type = "search";
$page->add($music_song_list);
$page->m_music_song_list = $music_song_list;

$music_musician_list = new CMusicMusicianList("music_musician_list", $g['tmpl']['dir_tmpl_main'] . "_music_musician_list.html");
$music_musician_list->m_list_type = "search";
$page->add($music_musician_list);
$page->m_music_musician_list = $music_musician_list;

include("./_include/core/main_close.php");
