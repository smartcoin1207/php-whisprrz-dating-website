<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

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
	var $m_hotdate;

	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $hotdate_id = get_param('hotdate_id', '');
        if($hotdate_id) {
	        $is_approved = CHotdatesTools::is_approved_sql();
	        $hotdate_sql = "SELECT * FROM hotdates_hotdate e WHERE hotdate_id = " . to_sql($hotdate_id) . $is_approved . " LIMIT 1";

	        $hotdate = DB::row($hotdate_sql);

	        if(!$hotdate) {
	        		redirect(Common::toHomePage());
	        }
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		if($this->m_hotdate && !$this->m_hotdate['hotdate_private'])
		{
			$html->parse('comments_title');
		}

        $state = User::isNarrowBox('hotdates');
        if  ($state) {
           $html->setvar('display', 'table-cell'); 
           $html->setvar('hide_narrow_box', 'block'); 
           $html->setvar('show_narrow_box', 'none'); 
        } else {
           $html->setvar('display', 'none'); 
           $html->setvar('hide_narrow_box', 'none'); 
           $html->setvar('show_narrow_box', 'block');            
        }
        
		parent::parseBlock($html);
	}
}

$page = new CHotdates("", $g['tmpl']['dir_tmpl_main'] . "hotdates_hotdate_show.html");


$hotdates_hotdate_show = new CHotdatesHotdateShow("hotdates_hotdate_show", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_show.html");
$page->add($hotdates_hotdate_show);
$hotdates_hotdate_image_list = new CHotdatesHotdateImageList("hotdates_hotdate_image_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_image_list.html");
$hotdates_hotdate_show->add($hotdates_hotdate_image_list);

$hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
$page->add($hotdates_header);

{
    $hotdate_id = get_param('hotdate_id');
    if (!User::isNarrowBox('hotdates')) CHotdatesTools::$thumbnail_postfix = 'orig';
    $hotdate = CHotdatesTools::retrieve_hotdate_by_id($hotdate_id);
    if($hotdate)
    {
        $page->m_hotdate = $hotdate;

    	$hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
    	$hotdates_sidebar->m_first_block = "";
        $hotdates_sidebar->m_second_block = "hotdate_show";
        $page->add($hotdates_sidebar);

        if($hotdate['hotdate_private'])
		{
            $hotdates_sidebar->m_first_block = "most_discussed";
			$hotdates_sidebar->m_second_block = "popular_finished";
		}
    	else
    	{
	        $hotdates_hotdate_guest_list = new CHotdatesHotdateGuestList("hotdates_hotdate_guest_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_guest_list.html");
	        $page->add($hotdates_hotdate_guest_list);

			$hotdates_hotdate_comment_list = new CHotdatesHotdateCommentList("hotdates_hotdate_comment_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_comment_list.html");
			$hotdates_hotdate_comment_list->m_need_not_found_message = false;
			$page->add($hotdates_hotdate_comment_list);
    	}
    }
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$hotdates_custom_head = new CHotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
$header->add($hotdates_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
