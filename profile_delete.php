<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

if(!Common::isOptionActive('delete_enabled')) {
    redirect(Common::toHomePage());
} 

class CProfileDelete extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		$cmd = get_param_post('cmd', '');
		if (IS_DEMO and is_demo_user()) {

        } else {
			if ($cmd == 'delete') {
                User::delete(guid());
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g;

		if ($g['options']['music'] == "Y")
		{
			$html->parse("my_music", true);
		}

		if ($g['options']['blogs'] == "Y")
		{
			$html->parse("my_blog", true);
		}


		parent::parseBlock($html);
	}
}

$page = new CProfileDelete("", $g['tmpl']['dir_tmpl_main'] . "profile_delete.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");

?>