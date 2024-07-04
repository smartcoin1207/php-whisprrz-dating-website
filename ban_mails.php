<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "login";
include('./_include/core/main_start.php');

class CBan extends CHtmlBlock
{
    function parseBlock(&$html)
    {
	parent::parseBlock($html);
    }
}

$page = new CBan('', $g['tmpl']['dir_tmpl_main'] . 'ban_mails.html');
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . '_header.html');
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . '_footer.html');
$page->add($footer);

include('./_include/core/main_close.php');