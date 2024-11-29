<?php
$g['mobile_redirect_off'] = true;
include('./_include/core/main_start.php');
include_once('./_include/current/vids/tools.php');

$guid = guid();

$siteGuid = get_param('site_guid', false);
if ($siteGuid !== false && $siteGuid != $guid) {
    echo getResponseAjaxByAuth(false);
    die();
}

global $g;
global $g_user;
global $p;

$cmd = get_param('cmd');
$id = get_param('id');
$isAuth = ($guid) ? true : false;

if ($cmd == 'sendcode') { /* Start Divyesh - 21-07-23 */
    $phone = get_param('phone');
    $carrier = get_param('carrier');

    $verifycode = Common::generateVerifyCode();

    $queryUpdate = "UPDATE user SET ";
    $queryUpdate .= "verify_code=" . to_sql($verifycode);
    $queryUpdate .= ", verify_code_date_time=" . to_sql(date("Y-m-d H:i:s"));
    $queryUpdate .= ", is_verified_c_provider='0'";
    $queryUpdate .= " WHERE user_id=" . $g_user['user_id'];
    DB::execute($queryUpdate);
    $carriernumber = str_replace("number", $phone, $carrier);
    /*$f = fopen("code.txt", "a");
    fwrite($f, "Code - " . $verifycode . "\n");
    fclose($f);*/

    $smsAuto = Common::autosmsInfo('verify_code', $g_user['lang'], 1);
    

    $subject = $smsAuto['subject'];
    $subject = str_replace("{title}", $g['main']['title'], $subject);
    $subject = str_replace("{name}", $g_user['name'], $subject);
    
    $message = strip_tags($smsAuto['text']);
    $message = str_replace("{name}", $g_user['name'], $message);
    $message = str_replace("{title}", $g['main']['title'], $message);
    $message = str_replace("{code}", $verifycode, $message);

    if (send_sms("{$carriernumber}", $g['main']['info_mail'], $subject, $message)) {
        echo json_encode(array("status" => "ok"));
    } else {
        echo json_encode(array("status" => "fail"));
    }
} elseif ($cmd == 'custom_folder') { /* Divyesh - Added on 11-04-2024 */
    $folder = get_param('folder');
    $queryUpdate = "UPDATE user SET ";
    $queryUpdate .= "custom_folder='{$folder}' WHERE user_id=" . $g_user['user_id'];
    if (DB::execute($queryUpdate)) {
        echo json_encode(array("status" => "ok"));
    } else {
        echo json_encode(array("status" => "fail"));
    }
} elseif ($cmd == 'vid_private') { 
    $id = get_param('v_id');
    $video = get_param('video');
    $video = ($video == 'private') ? 'Y' : 'N';
    $queryUpdate = "UPDATE vids_video SET `private`='{$video}' WHERE user_id=" . $g_user['user_id']." AND id=".$id;
    if (DB::execute($queryUpdate)) {
        echo json_encode(array("status" => "ok"));
    } else {
        echo json_encode(array("status" => "fail"));
    }
} /* Divyesh - Added on 11-04-2024 */
 elseif ($cmd == 'removephone') {
    $queryUpdate = "UPDATE user SET ";
    $queryUpdate .= "verify_code='', verify_code_date_time=" . to_sql("0000-00-00 00:00:00");
    $queryUpdate .= ", is_verified_c_provider='0', nsc_phone='', nsc_join_phone='', set_sms_alert=''";
    $queryUpdate .= ", set_sms_alert_mi='', set_sms_alert_hd='', set_sms_alert_pi='', set_sms_alert_rm='', set_sms_alert_wm='', carrier_provider='' ";
    $queryUpdate .= " WHERE user_id=" . $g_user['user_id'];
    if (DB::execute($queryUpdate)) {
        echo json_encode(array("status" => "ok"));
    } else {
        echo json_encode(array("status" => "fail"));
    }
} elseif ($cmd == 'verifyphne') {
    $code = get_param('code');

    if ($code == $g_user['verify_code']) {
        $queryUpdate = "UPDATE user SET ";
        $queryUpdate .= "verify_code='', verify_code_date_time=" . to_sql("0000-00-00 00:00:00");
        $queryUpdate .= ", is_verified_c_provider='1'";
        $queryUpdate .= " WHERE user_id=" . $g_user['user_id'];
        DB::execute($queryUpdate);
        echo json_encode(array("status" => "done"));
    } else {
        echo json_encode(array("status" => "error"));
    }
} elseif ($cmd == 'addcarrier') {
    $name = get_param('name');
    $email = "number@" . get_param('email');
    if (empty($name) || empty($email)) {
        $res['status'] = 'error';
    } else {
        $res = array("name" => $name, "email" => $email);
        $checkCarrier = DB::count("carrier", "`email` LIKE \"" . $email . "\"");
        if ($checkCarrier == 0) {
            $sql = "INSERT INTO carrier (`country_id`,`state_id`, `name`, `email`, `status`) VALUES(" . $g_user['country_id'] . ", " . $g_user['state_id'] . ", " . to_sql($name) . ", " . to_sql($email) . ", '1')";
            DB::execute($sql);
            //$where = " WHERE country_id={$g_user['country_id']} AND state_id={$g_user['state_id']} ";
            $where = " WHERE country_id={$g_user['country_id']} ";
            $carriers_options = Common::getCarrierOptionsSelect($where);
            $res['status'] = 'added';
            $res['options'] = $carriers_options;
        } else {
            $res['status'] = 'exist';
        }
    }
    echo json_encode($res); /* End Divyesh - 21-07-23 */
} elseif ($cmd == 'add_custom_folder') {
    $folder = get_param('folder');
    $is_nsc_couple_page = get_param('is_nsc_couple_page');
    $user_id = guid();
    if($is_nsc_couple_page == 1) {
        $user_id = $g_user['nsc_couple_id'] ?? 0;
    }
    $sql = "INSERT INTO `custom_folders` (`user_id`, `name`) VALUES (" . to_sql($user_id, 'Text') . ", " . to_sql($folder, 'Text') . ")";
    DB::execute($sql);
    $folder_id = DB::insert_id();

    if ($folder_id) {
        echo json_encode(array("status" => "ok"));
    } else {
        echo json_encode(array("status" => "fail"));
    }
}
DB::close();
