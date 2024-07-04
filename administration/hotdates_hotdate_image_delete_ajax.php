<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
//   Rade 2023-09-23
include("../_include/core/administration_start.php");
require_once("../_include/current/hotdates/tools.php");

function do_action()
{
	global $g_user;
	
	$image_id = intval(get_param('image_id'));
	
	if($image_id)
	{
        ChotdatesTools::delete_hotdate_image($image_id, true);
	}
}

do_action();

include("../_include/core/administration_close.php");
