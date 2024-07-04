<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/friends.php");

function do_action()
{
	global $g_user;

	$user_id = get_param('user_id');
    $responseData = false;
    $redirect = 'home.php';
	if($user_id) {
		DB::query('SELECT * FROM user WHERE user_id=' . to_sql($user_id));
		if($user = DB::fetch_row()) {
			if($user['user_id'] != $g_user['user_id']) {
                $sql = 'SELECT * FROM friends_requests
                         WHERE user_id IN (' . to_sql($user['user_id'], 'Number') . ',' . to_sql(guid(), 'Number') . ')
                           AND friend_id IN (' . to_sql($user['user_id'], 'Number') . ',' . to_sql(guid(), 'Number') . ')';
				DB::query($sql);
				if($request = DB::fetch_row()){
					$action = get_param('action');
					if($action == 'approve' && $request['user_id'] == $user['user_id']) {
                        User::friendApprove($user['user_id'], guid());
                        $responseData = true;
                        $redirect = 'friends_add_confirmed.php?user_id=' . $user['user_id'];
					} elseif ($action == 'decline' && $request['user_id'] == $user['user_id']) {
                        User::friendDecline($user['user_id'], guid());
                        $responseData = true;
                        $redirect = 'my_friends.php?show=requests';
					} elseif($action == 'remove') {
                        User::friendDelete(guid(), $user['user_id']);
                        $responseData = true;
                        $redirect = 'my_friends.php?show=all';
					}
				}
			}
		}
	}
    $isAjaxResponse = get_param('ajax');
    if ($isAjaxResponse) {
        die(getResponseDataAjaxByAuth($responseData));
    } else {
        redirect($redirect);
    }
}

do_action();

include("./_include/core/main_close.php");