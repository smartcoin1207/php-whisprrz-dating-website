<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

CStatsTools::count('3d_chat_opened');

payment_check('3d_chat');

class CChat extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		global $g_info;
	}
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		parent::parseBlock($html);
	}
}

$page = new CChat("", $g['tmpl']['dir_tmpl_main'] . "chat.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$users_new = new CHtmlBlock("users_new", null);
$page->add($users_new);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);

include("./_include/core/main_close.php");

?>
