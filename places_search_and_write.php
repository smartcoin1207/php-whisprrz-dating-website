<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/places/header.php");
require_once("./_include/current/places/sidebar.php");
require_once("./_include/current/places/tools.php");
require_once("./_include/current/places/place_list_top.php");

class CPlaceShow extends CHtmlBlock
{
	function action()
	{
	}
	
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		parent::parseBlock($html);
	}
}

$page = new CPlaceShow("", $g['tmpl']['dir_tmpl_main'] . "places_search_and_write.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$places_header = new CPlacesHeader("places_header", $g['tmpl']['dir_tmpl_main'] . "_places_header.html");
$page->add($places_header);
$places_sidebar = new CPlacesSidebar("places_sidebar", $g['tmpl']['dir_tmpl_main'] . "_places_sidebar.html");
$page->add($places_sidebar);
$places_place_list_top = new CPlacesPlaceListTop("places_place_list_top", $g['tmpl']['dir_tmpl_main'] . "_places_place_list_top.html");
$page->add($places_place_list_top);

include("./_include/core/main_close.php");

?>