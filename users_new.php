<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

if(Common::isOptionActive('page_users_new_disabled', 'template_options')) {
    redirect('search_results.php?status=new');
}

if (get_param('uid') != '') {
    $where = "u.user_id=" . (intval(get_param('uid'))) . '';
} else {
        $where = "u.user_id!=" . $g_user['user_id'] . " AND u.hide_time=0 " . $g['sql']['your_orientation'];
        if(!IS_DEMO) {
            //$where .= " AND register >= (NOW() - INTERVAL " . intval($g['options']['new_time'])  . " DAY)";
            $where .= " AND register >= " . to_sql(date('Y-m-d H:00:00', time() - intval($g['options']['new_time']) * 3600 * 24));
        }


        //eric-cuigao-20201121-start
        if(isset($g_user['orientation']) && $g_user['orientation']==5){
            $where .= "and u.set_my_presence_couples=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==1){
            $where .= "and u.set_my_presence_males=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==2){
            $where .= "and u.set_my_presence_females=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==6){
            $where .= "and u.set_my_presence_transgender=2";
        }else if(isset($g_user['orientation']) && $g_user['orientation']==7){
            $where .= "and u.set_my_presence_nonbinary=2";
        }
        // else{
        //     $where .= " and u.set_my_presence_everyone=2";
        // }
        //eric-cuigao-20201121-end
}

$order = "u.is_photo DESC, u.user_id DESC";

$page = Users_List::show($where, $order, null, 'users_list_base.html');

include("./_include/core/main_close.php");