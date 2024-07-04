<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";

include("./_include/core/main_start.php");

$where = "u.user_id!=" . to_sql($g_user['user_id'], "Number") . "";
//eric-cuigao-20201121-start
if(isset($g_user['orientation']) && $g_user['orientation']==5){
	$where .= " and u.set_my_presence_couples=2";
}else if(isset($g_user['orientation']) && $g_user['orientation']==1){
	$where .= " and u.set_my_presence_males=2";
}else if(isset($g_user['orientation']) && $g_user['orientation']==2){
	$where .= " and u.set_my_presence_females=2";
}else if(isset($g_user['orientation']) && $g_user['orientation']==6){
	$where .= " and u.set_my_presence_transgender=2";
}else if(isset($g_user['orientation']) && $g_user['orientation']==7){
	$where .= " and u.set_my_presence_nonbinary=2";
}
// else{
// 	$where .= " and u.set_my_presence_everyone=2";
// }
//eric-cuigao-20201121-end
$order = "v.id DESC";
$from_add = " JOIN users_view AS v ON (u.user_id=v.user_to AND v.user_from=" . to_sql($g_user['user_id'], "Number") . ")";

$page = Users_List::show($where, $order, $from_add, 'users_list_base.html');

include("./_include/core/main_close.php");