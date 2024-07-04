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
$isAuth = ($guid) ? true : false;

if ($cmd == "admin_send_mail") {
    global $g;
    
    $id = get_param('uid');

    $block = User::isBlocked('mail', $id, guid());
    $to_myself = (guid() == to_sql($id, "Number"));
    $to_myself = false;
    $empty_text = (trim(get_param("text", "")) == '');
    $subject = Common::filterProfileText(strip_tags(get_param('subject')));
    $text = Common::filterProfileText(get_param('text'));

    $text = trim(strip_tags($text));
    $status = "";
    $textHash = md5(mb_strtolower($text, 'UTF-8'));
    if (User::isBanMails($textHash) || User::isBanMailsIp()) {
        $status = "banmails";
    }

    $admin_id = DB::field("user", "user_id", "admin='1'", '', '1');
    
    if ($status == "" && $id != 0 and $block == 0 and !$to_myself and !$empty_text) {
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
/* Added by Divyesh on 14-10-2023 */
if ($cmd == "ticket_status"){
    $status = get_param('status');
    $tid = get_param('tid');
    $res = "error";
    if (DB::update('support_tickets', array('status' => $status), "id={$tid}"))
        $res = "ok";

    echo json_encode(array("res" => $res));
}
