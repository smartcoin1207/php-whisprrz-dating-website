<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include "./_include/core/main_start.php";

global $g;
$group_id = Groups::getParamId();

if(!$group_id) {
    Common::toHomePage();
}

$sql = "SELECT * FROM groups_social WHERE group_id = " . to_sql($group_id, 'Text');
$group_owner = DB::row($sql); 

$user_id = $group_owner['user_id'];

$sql1 = "SELECT * FROM user WHERE user_id = " . to_sql($user_id, 'Text');
$user = DB::row($sql1);

$group_owner_url = $g['path']['url_main'] . $user['name_seo'];

redirect($group_owner_url);

include "./_include/core/main_close.php";
