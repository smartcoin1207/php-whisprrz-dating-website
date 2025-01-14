<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");

function do_action()
{
	global $g_user;
	
	$image_id = intval(get_param('image_id'));
	$ajax = get_param('ajax');

	if($image_id)
	{

		if($ajax) {
			if(isset($g_user['moderator_partyhouz']) && $g_user['moderator_partyhouz']) {
				CpartyhouzTools::delete_partyhou_image($image_id, true);
            }
		} else {
			CpartyhouzTools::delete_partyhou_image($image_id);

		}

        echo 'ok';
        die();
	}
}

do_action();

include("./_include/core/main_close.php");

?>