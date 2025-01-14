<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

g_user_full();
$where = "";
$typeFields = array('from', 'checks');
foreach ($g['user_var'] as $k => $v)
{
    if (in_array($v['type'], $typeFields) && $v['status'] == 'active')
    {
        $key = $k;
        if ($v['type'] == "from"){
            if (isset($g_user[$k]) && $g_user[$k] != 0){
                if(strpos($k,"p_")===0){
                    $key = substr($k, 2);
                }
                if(strrpos($key,"_from")===(strlen($key)-strlen("_from"))){
                    $key = substr($key, 0,(strlen($key)-strlen("_from")));
                }
                $where .= " AND (i." . $key . ">" . $g_user[$k];
                $fieldsTo = substr($k, 0, strlen($k) - 4) . 'to';
                if ($g_user[$fieldsTo] != 0)
                    $where .= " AND i." . $key . "<" . $g_user[$fieldsTo] . ") ";
                else
                    $where .= ') ';
                }
        }elseif ($v['type'] == "checks"){
            $user[$k] = get_checks_param($k);
            if (isset($g_user[$k]) && $g_user[$k] != 0){
                    if(strpos($k,"p_")===0){
                        $key = substr($k, 2);
                    }
                    //$where .= " AND " . $g_user[$k] . " & (1 << (" . $key . " - 1))";
                    $where .= " AND " . to_sql($g_user[$k], 'Number') . " & (1 << (cast(i." . $key . " AS signed) - 1))";
            }
        }
    }
}

if (isset($g_user["p_relation"]) && $g_user["p_relation"] != "0")
{
    $where .= " AND " . $g_user["p_relation"] . " & (1 << (cast(u.relation AS signed) - 1))";
    //$where .= " AND " . $g_user["p_relation"] . " & (1 << (relation - 1))";
}
if (isset($g_user['p_age_from']) && $g_user['p_age_from'] == $g['options']['users_age']) $g_user['p_age_from'] = 0;
if (isset($g_user['p_age_to']) && $g_user['p_age_to'] == $g['options']['users_age_max']) $g_user['p_age_to'] = 100;
if ((isset($g_user['p_age_from']) && $g_user['p_age_from'] != 0) or isset($g_user['p_age_to']) && $g_user['p_age_to'] != 0)
{
//  $where .= " AND (
//      YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth)))>=" . $g_user['p_age_from'] . "
//      AND
//      YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth)))<=" . $g_user['p_age_to'] . "
//  ) ";

//    $where .= " AND (
//        DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(u.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(u.birth, '00-%m-%d'))
//) >= " . $g_user['p_age_from'] . " AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(u.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(u.birth, '00-%m-%d'))
// <= " . $g_user['p_age_to'] . "
//  ) ";

}


$user['p_age_from'] = (int) $g_user['p_age_from'];
$user['p_age_to'] = (int) $g_user['p_age_to'];

$userAgeToSrc = $user['p_age_to'];
if ($user['p_age_from'] == $g['options']['users_age']) $user['p_age_from'] = 0;
if ($user['p_age_to'] == $g['options']['users_age_max']) $user['p_age_to'] = 100;
if ($user['p_age_from'] != 0)
{
    $where .= " AND u.birth <= " . to_sql(Common::ageToDate($user['p_age_from']));
}

if ($userAgeToSrc && $userAgeToSrc != $g['options']['users_age_max'])
{
    $where .= " AND u.birth >= " . to_sql(Common::ageToDate($userAgeToSrc, true));
}


$partnerOrientation = User::getPartnerOrientationWhereSql();
if($partnerOrientation) {
    $partnerOrientation = ' AND ' . $partnerOrientation;
}

$where = "u.hide_time=0 AND u.user_id!=" . $g_user['user_id'] . $partnerOrientation . $where . " ";
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
$order = "near DESC, last_visit DESC, user_id DESC";

if(Users_List::isBigBase()) {
    $order = "last_visit DESC";
}

$from_add = "LEFT JOIN userinfo as i ON u.user_id=i.user_id";


$page = Users_List::show($where, $order, $from_add, 'users_list_base.html');

include("./_include/core/main_close.php");