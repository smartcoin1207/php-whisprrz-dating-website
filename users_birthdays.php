<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

$where = ' u.user_id != ' . to_sql(guid(), 'Number') . ' ' . $g['sql']['your_orientation'] . " AND hide_time=0 AND (DAYOFMONTH(birth)=DAYOFMONTH('" . date('Y-m-d H:i:s') . "') AND MONTH(birth)=MONTH(" . to_sql(date('Y-m-d H:i:s'), 'Text') . '))';

// var_dump($where); die();
//eric-cuigao-20201121-start
if(isset($g_user['orientation']) && $g_user['orientation']==5){
	$where .= "  and u.set_my_presence_couples=2";
} else if(isset($g_user['orientation']) && $g_user['orientation']==1){
	$where .= "  and u.set_my_presence_males=2";
} else if(isset($g_user['orientation']) && $g_user['orientation']==2){
	$where .= "  and u.set_my_presence_females=2";
} else if(isset($g_user['orientation']) && $g_user['orientation']==6){
	$where .= "  and u.set_my_presence_transgender=2";
} else if(isset($g_user['orientation']) && $g_user['orientation']==7){
	$where .= "  and u.set_my_presence_nonbinary=2";
}
// else{
// 	$where .= " and u.set_my_presence_everyone=2";
// }
//eric-cuigao-20201121-end
$order = 'near DESC, user_id';

$page = Users_List::show($where, $order, null, 'users_list_base.html');

include("./_include/core/main_close.php");