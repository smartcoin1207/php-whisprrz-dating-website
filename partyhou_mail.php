<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include './_include/core/main_start.php';
include("./_include/current/mail.templates.class.php");
include("./_include/current/select.userlist.class.php");

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
$partyhou_id = get_param('partyhou_id', '');
$gsql = "SELECT * FROM partyhouz_partyhou where partyhou_id = " . to_sql($partyhou_id, 'Text');
$partyhou = DB::row($gsql);
if(!$partyhou) {
    Common::toHomePage();
} 


$sql = "SELECT  * FROM partyhouz_partyhou_guest WHERE partyhou_id = " . to_sql($partyhou_id, 'Text') . "AND user_id = " . to_sql(guid(), 'Text');
$my_subscriber  = DB::row($sql);
// $moderator_options = json_decode($my_subscriber['moderator_options'], true);

if($g_user['user_id'] != $partyhou['user_id']) {
    $partyhou_url = $g['path']['url_main'] . "partyhou_wall.php?partyhou_id" . $partyhou_id;
    redirect($partyhou_url);
}

class CPartyhouMail extends CHtmlBlock
{

    public function init()
    {

    }

    public function action()
    {
        global $g, $g_user;
        $table = "saved_user_list";

        $cmd = get_param('cmd', '');
        if ($cmd == "sent") {

            $type = get_param('type');
            $name = get_param('name');

            $subject = Common::filterProfileText(strip_tags(get_param('subject')));
            if (trim($subject) == '') {
                $subject = l('no_subject');
            }

            $partyhou_id = get_param('partyhou_id', '');

            $text = Common::filterProfileText(get_param('text'));
            if ($type == 'postcard') {
                $text = urldecode($text);
            }
            $text = trim(strip_tags($text));

            $saved_user_list_id = get_param('saved_user_list', 0);
            $partyhou_id = get_param('partyhou_id', '');

            if ($saved_user_list_id == 'saved_all') {
                $user_sql = "SELECT user_id FROM partyhouz_partyhou_guest WHERE partyhou_id = " . to_sql($partyhou_id, 'Number');
            } elseif($saved_user_list_id == 'saved_male') {
                $user_sql = "SELECT gs.user_id FROM partyhouz_partyhou_guest AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.partyhou_id = " . to_sql($partyhou_id, 'Number') . " AND u.orientation=1";
            } elseif ($saved_user_list_id == 'saved_female') {
                $user_sql = "SELECT gs.user_id FROM partyhouz_partyhou_guest AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.partyhou_id = " . to_sql($partyhou_id, 'Number') . " AND u.orientation=2";
            } elseif ($saved_user_list_id == 'saved_couple') {
                $user_sql = "SELECT gs.user_id FROM partyhouz_partyhou_guest AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.partyhou_id = " . to_sql($partyhou_id, 'Number') . " AND u.orientation=5";
            } elseif ($saved_user_list_id == 'saved_transgender') {
                $user_sql = "SELECT gs.user_id FROM partyhouz_partyhou_guest AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.partyhou_id = " . to_sql($partyhou_id, 'Number') . " AND u.orientation=6";
            }

            if (in_array($saved_user_list_id, ['saved_all', 'saved_male', 'saved_female', 'saved_couple', 'saved_transgender'])) {
                $users_rows = DB::rows($user_sql);
                $user_ids = [];
                foreach ($users_rows as $row) {
                    $user_id = $row['user_id'];
                    $user_ids[] = $user_id;
                }

                $selected_members = $user_ids;
            } else {
                $sql = "SELECT * FROM " . $table . " WHERE `id` = " . to_sql($saved_user_list_id) . " AND type = 'event'";
                $saved_users_list = DB::row($sql);
                if($saved_users_list) {
                    $user_list = $saved_users_list['user_ids'];
                    $selected_members = json_decode($user_list, true);
                } else {
                    $selected_members = [];
                }
            }

            if ($selected_members && $subject != '' && $text != '') {
                $textHash = md5(mb_strtolower($text, 'UTF-8'));
                if (User::isBanMails($textHash) || User::isBanMailsIp()) {
                    redirect('ban_mails.php');
                }

                foreach ($selected_members as $key => $value) {
                    $id = $value;

                    if($value == $g_user['user_id']) {
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

                    $partyhou_id = get_param('partyhou_id', '');
                    $gsql = "SELECT * FROM partyhouz_partyhou where partyhou_id = " . to_sql($partyhou_id, 'Text');
                    $partyhou = DB::row($gsql);

                    $url_to = $g['path']['url_main'] . "partyhou_wall.php?partyhou_id=" . $partyhou_id;
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
        $partyhou_id = get_param('partyhou_id', '');
        $table = "saved_user_list";

        if (!$partyhou_id) {
            Common::toHomePage();
        }

        $sql = "SELECT * FROM " . $table . " WHERE  event_id = " . to_sql($partyhou_id) . " AND type = 'partyhou'";
        $saved_users_list = DB::row($sql);
        if($saved_users_list) {
            $user_list = $saved_users_list['user_ids'];
            $selected_members = json_decode($user_list, true);
        } else {
            $selected_members = [];
        }

        $partyhou_id = get_param('partyhou_id', '');

        $urlpage = $g['path']['url_main'] . "partyhou_mail.php?partyhou_id=" . $partyhou_id;
        $html->setvar('url_page', $urlpage);

        // $saved_user_list = self::getSavedUserList($partyhou_id);
        // $html->setvar('saved_user_list', $saved_user_list);

        // $select_partyhou_user_url = $g['path']['url_main'] . "select_partyhou_users.php?partyhou_id=" . $partyhou_id;
        // $html->setvar('select_event_user_url', $select_partyhou_user_url);

        parent::parseBlock($html);
    }

    function getSavedUserList($partyhou_id)
    {
        $sql = "SELECT id, title FROM saved_user_list WHERE user_id = " . to_sql(guid(), 'Number') . " AND event_id = " . to_sql($partyhou_id, 'Number') . " AND type = 'partyhou'";
        return Common::getSavedUserList($sql);
    }
}

$page = new CPartyhouMail("", $g['tmpl']['dir_tmpl_main'] . "event_mail_compose.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$saved_user_list = new CSelectUserList('saved_user_list', $g['tmpl']['dir_tmpl_main'] . "select_user_list.html");
$saved_user_list->event_id = $partyhou_id;
$saved_user_list->userlist_type = 'partyhou';
$page->add($saved_user_list);

$mail_templates_list = new CMailTemplates('mail_templates_list', $g['tmpl']['dir_tmpl_main'] . "mail_templates.html");
$mail_templates_list->template_type = 'PARTYHOU_MAIL';
$page->add($mail_templates_list);

include './_include/core/main_close.php';
