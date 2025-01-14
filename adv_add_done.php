<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

payment_check('adv_add');

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		$html->setvar("cat",get_param("cat"));
		$html->setvar("id",get_param("id"));
		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv_add_done.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
