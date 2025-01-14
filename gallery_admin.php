<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
payment_check('gallery_edit');
if (isset($g['options']['gallery']) and $g['options']['gallery'] == "N") redirect('home.php');

class CGallery_Admin extends CHtmlBlock
{
	function action()
	{
		parent::action();
	}
	function init()
	{
	 	parent::init();
		global $g;
		global $g_user;
	}
	
	function parseBlock(&$html)
	{
		global $g_user;

		$html->setvar("user_id", $g_user["user_id"]);
		parent::parseBlock($html);
	}
}


$page = new CGallery_Admin("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_admin.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$galleryMenu = new CMenuSection('gallery_menu', $g['tmpl']['dir_tmpl_main'] . "_gallery_menu.html");
$galleryMenu->setActive('gallery_admin');
$page->add($galleryMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>