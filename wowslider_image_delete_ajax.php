<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/events/tools.php");
require_once("./_include/current/adv.class.php");

function do_action()
{
	global $g_user;
	
	$image_id = intval(get_param('image_id'));
	$ajax = get_param('ajax');
	$craigs_id = get_param('craigs_id');

	if($image_id)
	{
		if($ajax) {
			if($craigs_id) {
				if(isset($g_user['moderator_craigs']) && $g_user['moderator_craigs']) {
					CAdvTools::deleteImageOne($image_id);
				}
			} else {
				if(isset($g_user['moderator_events']) && $g_user['moderator_events']) {
					CEventsTools::delete_event_image($image_id, true);
				}
			}
			
		} else {
			CEventsTools::delete_event_image($image_id);

		}
        echo 'ok';
        die();
	}
}

do_action();

include("./_include/core/main_close.php");

?>