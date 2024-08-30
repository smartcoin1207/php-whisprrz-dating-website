<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/adv.class.php");

function do_action()
{
	global $g_user;

	$craigs_id = intval(get_param('craigs_id'));
    $cat_name = get_param('cat_name', '');
    $ajax = get_param_int('ajax');

	if($craigs_id && $cat_name) {
        if($ajax) {
            if(isset($g_user['moderator_craigs']) && $g_user['moderator_craigs']) {
                CAdvTools::deleteAdv($cat_name, $craigs_id, null, true);
            }
            die(getResponseDataAjaxByAuth(true));
	    }
    }
}

do_action();

include("./_include/core/main_close.php");