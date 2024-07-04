<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23
include("../_include/core/administration_start.php");
require_once("../_include/current/vids/includes.php");

class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        //   $html->setvar('email', guser("mail"));
        // $html->setvar('password', guser("password"));
		parent::parseBlock($html);
	}
}


$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "vids_radios.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

// $page->add(new CAdminPageMenuVids());

include("../_include/core/administration_close.php");
