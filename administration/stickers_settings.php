<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminStickerSettings extends CHtmlBlock
{

	function parseBlock(&$html)
	{

        parent::parseBlock($html);
	}
}

$page = new CAdminStickerSettings("", $g['tmpl']['dir_tmpl_administration'] . "stickers_settings.html");

$items = new CAdminConfig('config_fields', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
$items->setModule('edge_stickers');
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuStickers());

include("../_include/core/administration_close.php");