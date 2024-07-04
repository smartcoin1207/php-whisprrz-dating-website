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
require_once("./_include/current/hotdates/hotdate_list.php");

class CHotdates extends CHtmlBlock
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
        
		parent::parseBlock($html);
	}
}

$page = new CHotdates("", $g['tmpl']['dir_tmpl_main'] . "hotdates_hotdate_list_past_hotdates_alike.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$hotdates_custom_head = new CHotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
$header->add($hotdates_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
$page->add($hotdates_header);
$hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
$page->add($hotdates_sidebar);

$hotdates_hotdate_list = new CHotdatesHotdateList("hotdates_hotdate_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$hotdates_hotdate_list->m_list_type = "past_alike";
$page->add($hotdates_hotdate_list);

include("./_include/core/main_close.php");
