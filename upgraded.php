<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

if ($g_user['gold_days'] == 0 or $g_user['type'] == '' or $g_user['type'] == 'none')
{
	#p('asdasdasd');
	redirect("upgrade.php");
}

class CGold extends CHtmlBlock
{
	function action()
	{
		$cmd = get_param("cmd", "");
		if ($cmd = "cc")
		{

		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

    if($g['options']['city'] == "Y") $html->parse("city");
    if($g['options']['rating'] == "Y") $html->parse("rating");

		parent::parseBlock($html);
	}
}

$page = new CGold("", $g['tmpl']['dir_tmpl_main'] . "upgraded.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);




include("./_include/core/main_close.php");

?>
