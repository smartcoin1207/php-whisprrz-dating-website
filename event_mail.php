<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include './_include/core/main_start.php';

$optionTmplName = Common::getTmplName();
if ($optionTmplName != 'edge') {
    Common::toHomePage();
}

$isAjaxRequest = get_param('ajax');
$hideFromGuests = Common::isOptionActive('list_photos_hide_from_guests', "{$optionTmplName}_general_settings");
if (!guid() && !$isAjaxRequest) {
    $uid = User::getParamUid(0);
    if ($hideFromGuests || $uid) {
        Common::toLoginPage();
    }
}


global $g_user, $g;
$event_id = get_param('event_id', '');
$gsql = "SELECT * FROM events_event where event_id = " . to_sql($event_id, 'Text');
$event = DB::row($gsql);
if(!$event) {
    Common::toHomePage();
} 


$sql = "SELECT  * FROM events_event_guest WHERE event_id = " . to_sql($event_id, 'Text') . "AND user_id = " . to_sql(guid(), 'Text');
$my_subscriber  = DB::row($sql);
// $moderator_options = json_decode($my_subscriber['moderator_options'], true);

if($g_user['user_id'] != $event['user_id']) {
    $event_url = $g['path']['url_main'] . "event_wall.php?event_id" . $event_id;
    redirect($event_url);
}

class CEventMail extends CHtmlBlock
{

    public function init()
    {

    }

    public function action()
    {
        global $g, $g_user;
        
        $table = "mass_mail_saved_user_list";
        $cmd = get_param('cmd', '');
        if ($cmd == "sent") {

            $type = get_param('type');
            $name = get_param('name');

            $subject = Common::filterProfileText(strip_tags(get_param('subject')));
            if (trim($subject) == '') {
                $subject = l('no_subject');
            }

            $event_id = get_param('event_id', '');

            $text = Common::filterProfileText(get_param('text'));
            if ($type == 'postcard') {
                $text = urldecode($text);
            }
            $text = trim(strip_tags($text));

            $sql = "SELECT * FROM " . $table . " WHERE  event_id = " . to_sql($event_id) . " AND event_type = 'event'";
            $saved_users_list = DB::row($sql);
            if($saved_users_list) {
                $user_list = $saved_users_list['userlist'];
                $selected_members = json_decode($user_list, true);
            } else {
                $selected_members = [];
            }

            if ($selected_members && $subject != '' && $text != '') {
                $textHash = md5(mb_strtolower($text, 'UTF-8'));
                if (User::isBanMails($textHash) || User::isBanMailsIp()) {
                    redirect('ban_mails.php');
                }

                foreach ($selected_members as $key => $value) {
                    $id = $key;
                    if($key == $g_user['user_id']) {
                        continue;
                    }
                    $block = User::isBlocked('mail', $id, guid());
                    $to_myself = (guid() == to_sql($id, "Number"));
                    $empty_text = (trim(get_param("text", "")) == '');

                    if ($id != 0 and $block == 0 and !$to_myself and !$empty_text) {
                        $idMailFrom = 0;
                        $sqlInto = '';
                        $sqlValue = '';
                        if (get_param('type') != 'postcard') {
                            $sqlInto = ', text_hash';
                            $sqlValue = ', ' . to_sql($textHash);
                        }
                        if (get_param('save') == '1') {
                            DB::execute("
                                INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, new, type, receiver_read" . $sqlInto . ")
                                VALUES(
                                " . $g_user['user_id'] . ",
                                " . $g_user['user_id'] . ",
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
                        }

                        DB::execute("
                        INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, type, receiver_read, sent_id" . $sqlInto . ")
                            VALUES(
                            " . to_sql($id, "Number") . ",
                            " . $g_user['user_id'] . ",
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

                        if (Common::isEnabledAutoMail('mail_message')) {
                            DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($id, 'Number'));
                            if ($row = DB::fetch_row()) {
                                if ($row['set_email_mail'] != '2') {
                                    $textMail = (Common::isOptionActive('mail_message_alert')) ? $text : '';
                                    $vars = array('title' => $g['main']['title'],
                                        'name' => $g_user['name'],
                                        'text' => $textMail,
                                        'mid' => $idMailTo);
                                    Common::sendAutomail($row['lang'], $row['mail'], 'mail_message', $vars);
                                }
                            }
                        }

                        $userToInfo = User::getInfoBasic($id);

                        if($userToInfo)
                        {
                            Common::usersms('new_mail_sms', $userToInfo, 'set_sms_alert_rm');
                        }
                    }
                }

                $this->message_sent = true;
                if ($this->message_sent) {
                    $to = get_param('page_from', '');
                    set_session('send_message', true);

                    $event_id = get_param('event_id', '');
                    $gsql = "SELECT * FROM events_event where event_id = " . to_sql($event_id, 'Text');
                    $event = DB::row($gsql);

                    $url_to = $g['path']['url_main'] . "event_wall.php?event_id=" . $event_id;
                    redirect($url_to);
                }
                $this->message = l('Successfuly sent to members');
                $this->message .= '<br>';
            } else {
                $this->message = l('Incorrect subject or message');
                $this->message .= '<br>';
            }
        }
    }

    public function parseBlock(&$html)
    {
        global $g;
        $event_id = get_param('event_id', '');
        $table = "mass_mail_saved_user_list";

        if (!$event_id) {
            Common::toHomePage();
        }

        $total_member_count = CEventsTools::getTotalGuestsCount($event_id);

        $sql = "SELECT * FROM " . $table . " WHERE  event_id = " . to_sql($event_id) . " AND event_type = 'event'";
        $saved_users_list = DB::row($sql);
        if($saved_users_list) {
            $user_list = $saved_users_list['userlist'];
            $selected_members = json_decode($user_list, true);
        } else {
            $selected_members = [];
        }

        $member_count = 0;
        if ($selected_members) {
            $member_count = count($selected_members);
        }

        $message = "Selected " . $member_count . "/" . $total_member_count;
        $html->setvar('member_count_message', $message);

        $event_id = get_param('event_id', '');

        $urlpage = $g['path']['url_main'] . "event_mail.php?event_id=" . $event_id;
        $html->setvar('url_page', $urlpage);

        $select_url = $g['path']['url_main'] . "select_event_users.php?event_id=" . $event_id;
        $html->setvar('url_select_page', $select_url);

        parent::parseBlock($html);
    }
}

$page = new CEventMail("", $g['tmpl']['dir_tmpl_main'] . "event_mail_compose.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include './_include/core/main_close.php';
