<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

$id = get_param('id');
$key = get_param('key');

if($id && $key) {

    $sql = 'SELECT * FROM invites
        WHERE id = ' . to_sql($id, 'Number') . '
        AND invite_key = ' . to_sql($key, 'Text');
    $row = DB::row($sql);

    if($row) {
        if(guid()) {
            User::friendAdd($row['user_id'], guid(), 1);
            User::inviteDelete($row['id']);
        } else {
            // save for accept at login
            set_session('invite', "$id:$key");
        }
        if (!Common::isMobile()) {
            redirect('friends_requests.php?action=approve&user_id=' . $row['user_id']);
        } else {
            redirect('my_friends.php');
        }
    }
}
Common::toHomePage();