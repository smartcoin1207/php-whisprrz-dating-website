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
require_once("./_include/current/music/musician_show.php");
require_once("./_include/current/music/musician_image_list.php");
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
        	$musician_id = intval(get_param('musician_id'));
            if($musician_id)
            {
				$musician = DB::row("SELECT * FROM music_musician as s, music_musician as m WHERE s.musician_id=" . to_sql($musician_id, 'Number') .
		            " AND s.musician_id = m.musician_id " .
		            " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ") LIMIT 1");
                if($musician)
                {
                    $time = DB::result('SELECT NOW()');
                    for($image_n = 1; $image_n <= 4; ++$image_n)
                    {
                        $name = "image_" . $image_n;
                        CMusicTools::do_upload_musician_image($name, $musician_id, $time);
                    }

                    redirect('music_musician_show.php?musician_id=' . $musician['musician_id']);
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

        $musician_id = intval(get_param('musician_id', 0));
        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id = " . to_sql($musician_id, 'Number') . " LIMIT 1");

        if($musician)
        {
            $html->setvar('musician_id', $musician['musician_id']);
        	$html->setvar('musician_name', $musician['musician_name']);
            $html->setvar('musician_about', $musician['musician_about']);
        }
		else
            redirect('music.php');

		parent::parseBlock($html);
	}
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_musician_add_photos.html");
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

include("./_include/core/main_close.php");
