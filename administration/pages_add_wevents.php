<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. 
This file is built by cobra.  --- 20200209*/
// Rade 2023-09-23
include("../_include/core/administration_start.php");

class CWevents extends CHtmlBlock
{
	function action()
	{
        
	}

	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
}

$page = new CWevents('', $g['tmpl']['dir_tmpl_administration'] . 'pages_add_wevents.html');

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>