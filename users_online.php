<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

//start gregory mann modified at 7/13/11:01 am.  access other user's profile photo 
$display = get_param('display','');
if(get_param('display') == "photo" && get_param('uid') != ''){
    $where_photo = "select * from user where user_id=".get_param('uid');
    $row_photo = DB::row($where_photo);
    $can_see_photo = true;
    if(isset($g_user['orientation']) && $g_user['orientation'] ==5  && $row_photo['set_photo_couples']==2 ){
        $can_see_photo = false;
    } else if(isset($g_user['orientation']) && $g_user['orientation'] ==1  && $row_photo['set_photo_males']==2 ){
        $can_see_photo = false;
    } else if(isset($g_user['orientation']) && $g_user['orientation'] ==2  && $row_photo['set_photo_females']==2 ){
        $can_see_photo = false;
    } else if(isset($g_user['orientation']) && $g_user['orientation'] ==6  && $row_photo['set_photo_transgender']==2 ){
        $can_see_photo = false;
    } else if(isset($g_user['orientation']) && $g_user['orientation'] ==7  && $row_photo['set_photo_nonbinary']==2 ){
        $can_see_photo = false;
    }

    if(!$can_see_photo) {
        $redirect_url1 = "users_online.php?display=profile&private_set=".get_param('uid')."&uid=".get_param('uid');
        redirect($redirect_url1);
    }


}

//end gregory mann modified at 7/13/11:01 am. access other user's profile photo 

if (get_param('uid') != '') {
      $where = 'u.user_id = ' . to_sql(get_param('uid'), 'Number');
} else {
    //$defaultOnlineView = User::defaultOnlineView();
    //$filter = $defaultOnlineView != '' ? $defaultOnlineView : $g['sql']['your_orientation'];
    $filter = '';
    $where = 'u.user_id != ' . guid() . '
        AND hide_time = 0 ' . $filter. '
        AND (last_visit > ' . to_sql((date("Y-m-d H:i:00", time() - $g['options']['online_time'] * 60)), 'Text') . ' OR use_as_online=1)';
}
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
//     $where .= " and u.set_my_presence_everyone=2";
// }
//eric-cuigao-20201121-end

$order = 'is_photo DESC, ' . Common::getSearchOrderNear() . ' user_id DESC';

$page = Users_List::show($where, $order, null, 'users_list_base.html');

include("./_include/core/main_close.php");