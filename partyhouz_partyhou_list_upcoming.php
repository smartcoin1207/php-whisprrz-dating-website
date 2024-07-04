<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/custom_head.php");
require_once("./_include/current/partyhouz/header.php");
require_once("./_include/current/partyhouz/sidebar.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_show.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");
require_once("./_include/current/partyhouz/partyhou_guest_list.php");
require_once("./_include/current/partyhouz/partyhou_comment_list.php");
require_once("./_include/current/partyhouz/partyhou_list.php");
require_once("./_include/current/partyhouz/partyhou_search.php");

class Cpartyhouz extends CHtmlBlock
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

$page = new Cpartyhouz("", $g['tmpl']['dir_tmpl_main'] . "partyhouz_partyhou_list_upcoming.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$partyhouz_custom_head = new CpartyhouzCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_custom_head.html");
$header->add($partyhouz_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
$page->add($partyhouz_header);
$partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
$partyhouz_sidebar->m_second_block = "popular_finished";
$page->add($partyhouz_sidebar);

$partyhouz_calendar_search = new CPartyhouCalendarSearch("partyhouz_calendar_search", $g['tmpl']['dir_tmpl_main'] . "_calendar_search.html");
$page->add($partyhouz_calendar_search);

$partyhouz_partyhou_list = new CpartyhouzpartyhouList("partyhouz_partyhou_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$partyhouz_partyhou_list->m_list_type = "upcoming";
$page->add($partyhouz_partyhou_list);

include("./_include/core/main_close.php");
