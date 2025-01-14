<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/hotdates/custom_head.php");
require_once("./_include/current/hotdates/header.php");
require_once("./_include/current/hotdates/sidebar.php");
require_once("./_include/current/hotdates/tools.php");
require_once("./_include/current/hotdates/hotdate_show.php");
require_once("./_include/current/hotdates/hotdate_image_list.php");
require_once("./_include/current/hotdates/hotdate_guest_list.php");
require_once("./_include/current/hotdates/hotdate_comment_list.php");

class CHotdates extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $hotdate_id = intval(get_param('hotdate_id'));
            
            if($hotdate_id)
            {
                $hotdate = CHotdatesTools::retrieve_hotdate_by_id($hotdate_id);
                
                if($hotdate)
                {
                    $guests = ChotdatesTools::getGuestUsers($hotdate_id);

                    $is_guest = false;
                    
                    foreach ($guests as $key => $guest) {
                        if($guest['user_id'] == $g_user['user_id']) {
                            $is_guest = true;
                            break;
                        }
                    }

                    if(!$is_guest && $g_user['user_id'] != $hotdate['user_id']) {
                        redirect('hotdates_hotdate_show.php?hotdate_id=' . $hotdate_id);
                    }
                    $time = DB::result('SELECT NOW()');
                    for($image_n = 1; $image_n <= 4; ++$image_n)
                    {
                        $name = "image_" . $image_n;
                        CHotdatesTools::do_upload_hotdate_image($name, $hotdate_id, $time, $hotdate['hotdate_private'] ? false : true);
                    }

                    redirect('hotdates_hotdate_show.php?hotdate_id=' . $hotdate['hotdate_id']);
                }
            }
            redirect('hotdates.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $hotdate_id = get_param('hotdate_id');

		$hotdate = CHotdatesTools::retrieve_hotdate_by_id($hotdate_id);

        $guests = ChotdatesTools::getGuestUsers($hotdate_id);
        $is_guest = false;
        foreach ($guests as $key => $guest) {
            if($guest['user_id'] == $g_user['user_id']) {
                $is_guest = true;
                break;
            }
        }

        if(!$is_guest && $g_user['user_id'] != $hotdate['user_id']) {
            redirect('hotdates_hotdate_show.php?hotdate_id=' . $hotdate_id);
        }

        if($hotdate)
        {
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
        	$html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), 20));
            $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));
        }

        parent::parseBlock($html);
	}
}

$page = new CHotdates("", $g['tmpl']['dir_tmpl_main'] . "hotdates_hotdate_add_photos.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$hotdates_custom_head = new CHotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
$header->add($hotdates_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$hotdates_hotdate_show = new CHotdatesHotdateShow("hotdates_hotdate_show", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_show.html");
$page->add($hotdates_hotdate_show);
$hotdates_hotdate_image_list = new CHotdatesHotdateImageList("hotdates_hotdate_image_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_image_list.html");
$hotdates_hotdate_show->add($hotdates_hotdate_image_list);

$hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
$page->add($hotdates_header);
$hotdates_hotdate_guest_list = new CHotdatesHotdateGuestList("hotdates_hotdate_guest_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_guest_list.html");
$page->add($hotdates_hotdate_guest_list);
$hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
$hotdates_sidebar->m_first_block = "";
$hotdates_sidebar->m_second_block = "hotdate_show";
$page->add($hotdates_sidebar);

include("./_include/core/main_close.php");
