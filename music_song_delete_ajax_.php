<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/music/tools.php");

function do_action()
{
	global $g_user;

	$song_id = intval(get_param('song_id'));
    $ajax = get_param_int('ajax');//EDGE
    $guid = guid();
    $uid = get_param_int('uid');

    $response = false;
	if($song_id)
	{
        if ($ajax && $guid != $uid) {
            return $response;
        }

        CMusicTools::delete_song($song_id);
        $response = true;
        if ($ajax) {
            $groupId = get_param_int('group_id');
            $count = Songs::getTotal($guid, $groupId);
            $response = array(
                'count' => $count,
                'count_title' => lSetVars('edge_column_songs_title', array('count' => $count))
            );
        }

	}
    return $response;
}

$response = do_action();

if (get_param_int('ajax')) {//EDGE
    echo getResponseAjaxByAuth(guid(), $response);
}

include("./_include/core/main_close.php");