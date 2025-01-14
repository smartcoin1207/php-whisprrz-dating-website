<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-24
include("../_include/core/administration_start.php");

$cmd = get_param('cmd');

if ($cmd == "admin_send_mail") {
    global $g;
    
    $id = get_param('uid');

    $block = User::isBlocked('mail', $id, guid());
    $to_myself = (guid() == to_sql($id, "Number"));
    $empty_text = (trim(get_param("text", "")) == '');
    $subject = Common::filterProfileText(strip_tags(get_param('subject')));
    $text = Common::filterProfileText(get_param('text'));

    $text = trim(strip_tags($text));
    $status = "";
    $textHash = md5(mb_strtolower($text, 'UTF-8'));
    if (User::isBanMails($textHash) || User::isBanMailsIp()) {
        $status = "banmails";
    }

    if ($status == "" && $id != 0 and $block == 0 and !$to_myself and !$empty_text) {
        $admin_id = DB::field("user", "user_id", "admin='1'", '', '1');
        $idMailFrom = 0;
        $sqlInto = '';
        $sqlValue = '';
        if (get_param('type') != 'postcard') {
            $sqlInto = ', text_hash';
            $sqlValue = ', ' . to_sql($textHash);
        }
        //if (get_param('save') == '1') {
            DB::execute("
							INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, new, type, receiver_read" . $sqlInto . ")
							VALUES(
							" . $admin_id[0] . ",
							" . $admin_id[0] . ",
							" . to_sql($id, "Number") . ",
							" . 3 . ",
							" . to_sql($subject, 'Text') . ",
							" . to_sql($text, 'Text') . ",
							" . time() . ",
							'N',
							" . to_sql(get_param('type')) . ",
                            'N'" . $sqlValue . ")
						");

            $idMailFrom = DB::insert_id();
       // }

        DB::execute("
					INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, type, receiver_read, sent_id" . $sqlInto . ")
						VALUES(
						" . to_sql($id, "Number") . ",
						" . $admin_id[0] . ",
						" . to_sql($id, "Number") . ",
						" . 1 . ",
						" . to_sql($subject, 'Text') . ",
						" . to_sql($text, 'Text') . ",
						" . time() . ",
						" . to_sql(get_param('type')) . ",
                        'N',
                        " . to_sql($idMailFrom, 'Number') . $sqlValue . ")
					");
        $idMailTo = DB::insert_id();
        DB::execute("UPDATE user SET new_mails=new_mails+1 WHERE user_id=" . to_sql($id, "Number") . "");
        CStatsTools::count('mail_messages_sent');
        User::updateActivity($id);

        /* START - Divyesh - 01082023 */
        $userTo = User::getInfoBasic($id);

        Common::usersms('new_mail_sms', $userTo, 'set_sms_alert_rm');

        /* END - Divyesh - 01082023 */

        if (Common::isEnabledAutoMail('mail_message')) {
            DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($id, 'Number'));
            if ($row = DB::fetch_row()) {
                if ($row['set_email_mail'] != '2') {
                    $textMail = (Common::isOptionActive('mail_message_alert')) ? $text : '';
                    $vars = array(
                        'title' => $g['main']['title'],
                        'name'  => $g['main']['title'],
                        'text'  => $textMail,
                        'mid' => $idMailTo
                    );
                    Common::sendAutomail($row['lang'], $row['mail'], 'mail_message', $vars);
                }
            }
        }

        $status = "ok";
    }

    echo json_encode(array("status" => $status));
}

if ($cmd == "fetch_country") {
    $countryOpt = "";
    $countries = DB::rows('SELECT * FROM geo_country WHERE hidden="0" ORDER BY country_title');
    foreach ($countries as $country) {
        $countryOpt .= "<option value='{$country['country_id']}'>{$country['country_title']}</option>";
    }
    echo json_encode(array("option" => $countryOpt));
}

if ($cmd == "auto_suggest") {
    $search = get_param('q');
    $users = DB::rows('SELECT * FROM user WHERE `name` LIKE "' . $search . '%"');

    $userOpt = [];
    foreach ($users as $user) {
        $userOpt[] = array("value" => $user['name'], "data" => $user['name']);
    }

    echo json_encode(array("suggestions" => $userOpt));
}

if ($cmd == "sendtextsms") {
    $phone = get_param('phone');
    $carrier = get_param('carrier');
    $user_id = get_param('user_id');

    $sql = "SELECT * FROM user WHERE user_id={$user_id}";
    DB::query($sql);
    $user_row = DB::fetch_row();

    $carriernumber = str_replace("number", $phone, $carrier);
    $smsAuto = Common::autosmsInfo('test_sms', $user_row['lang'], 2);

    $subject = $smsAuto['subject'];
    $subject = str_replace("{title}", $g['main']['title'], $subject);
    $subject = str_replace("{name}", $user_row['name'], $subject);
    
    $message = strip_tags($smsAuto['text']);
    $message = str_replace("{name}", $user_row['name'], $message);
    $message = str_replace("{title}", $g['main']['title'], $message);

    if (send_sms("{$carriernumber}", $g['main']['info_mail'], $subject, $message)){
        echo json_encode(array("status" => "ok"));
    }else{
        echo json_encode(array("status" => "error"));
    }
}

if ($cmd == 'addcarrier') {
    $country_id = get_param('country_id');
    $state_id = get_param('state_id');
    $name = get_param('name');
    $email = "number@" . get_param('email');
    if (empty($name) || empty($email)) {
        $res['status'] = 'error';
    } else {
        $res = array("name" => $name, "email" => $email);
        $checkCarrier = DB::count("carrier", "`email` LIKE \"" . $email . "\"");
        if ($checkCarrier == 0) {
            $sql = "INSERT INTO carrier (`country_id`,`state_id`, `name`, `email`, `status`) VALUES(" . $country_id . ", " . $state_id . ", " . to_sql($name) . ", " . to_sql($email) . ", '1')";
            DB::execute($sql);
            //$where = " WHERE country_id={$g_user['country_id']} AND state_id={$g_user['state_id']} ";
            $where = " WHERE country_id={$country_id}  ";
            $carriers_options = Common::getCarrierOptionsSelect($where);
            $res['status'] = 'added';
            $res['options'] = $carriers_options;
        } else {
            $res['status'] = 'exist';
        }
    }
    echo json_encode($res);
}

if ($cmd == "fetch_country_regions") {
    $stateOpt = [];
    $country_ids = get_param('country');
    $country_ids = explode(",", $country_ids);

    foreach ($country_ids as $country_id) {
        $states = DB::rows('SELECT * FROM geo_state WHERE hidden="0" and country_id=' . to_sql($country_id, "Number") . ' ORDER BY state_title');
        $stateOption = "";
        foreach ($states as $state) {
            $stateOption .= "<option value='{$state['country_id']}'>{$state['state_title']}</option>";
        }
        $stateOpt[$country_id] = $stateOption;
    }
    echo json_encode(array("options" => $stateOpt));
}
if ($cmd == "fetch_country_region") {
    $stateOpt = "";
    $country_id = get_param('country');

    $states = DB::rows('SELECT * FROM geo_state WHERE hidden="0" and country_id=' . to_sql($country_id, "Number") . ' ORDER BY state_title');
    foreach ($states as $state) {
        $stateOpt .= "<option value='{$state['country_id']}'>{$state['state_title']}</option>";
    }

    echo json_encode(array("option" => $stateOpt));
}

if ($cmd == "delete_carrier") {
    $id = get_param('id');
    DB::execute("DELETE FROM carrier WHERE id=" . to_sql($id, "Number") . "");
    echo json_encode(array("status" => "ok"));
}

if ($cmd == "save_carrier") {
    $id = get_param('carrier_id');
    $country_id = get_param('country_id');
    $state_id = get_param('state_id');
    $name = get_param('name');
    $email = get_param('email');

    DB::execute("
				UPDATE carrier
				SET
				country_id=" . to_sql($country_id, "Number") . ",
				state_id=" . to_sql($state_id, "Number") . ",
				name=" . to_sql($name, "Text") . ",
				email=" . to_sql($email, "Text") . "
				WHERE id=" . to_sql($id, "Number") . "
			");
    echo json_encode(array("status" => "ok"));
}

include("../_include/core/administration_close.php");
