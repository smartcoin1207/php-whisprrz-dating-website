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

Groups::checkAccessGroup();

global $g_user, $g;
$groupId = Groups::getParamId();
$gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
$group = DB::row($gsql);
if(!$group) {
    Common::toHomePage();
} 

$group_nameseo = $group['name_seo'];

$sql = "SELECT  * FROM groups_social_subscribers WHERE group_id = " . to_sql($groupId, 'Text') . "AND user_id = " . to_sql(guid(), 'Text');
$my_subscriber  = DB::row($sql);
$moderator_options = json_decode($my_subscriber['moderator_options'], true);

if($g_user['user_id'] != $group['user_id'] && !$moderator_options['group_mail']) {
    $group_url = $g['path']['url_main'] . $group_nameseo;
    redirect($group_url);
}

class CGroupMail extends CHtmlBlock
{
    public function init()
    {

    }

    public function action()
    {
        global $g, $g_user;

        $cmd = get_param('cmd', '');
        if ($cmd == "sent") {

            $type = get_param('type');
            $name = get_param('name');

            $subject = Common::filterProfileText(strip_tags(get_param('subject')));
            if (trim($subject) == '') {
                $subject = l('no_subject');
            }

            $text = Common::filterProfileText(get_param('text'));
            if ($type == 'postcard') {
                $text = urldecode($text);
            }
            $text = trim(strip_tags($text));

            $saved_user_list_id = get_param('saved_user_list', 0);
            $groupId = Groups::getParamId();

            if ($saved_user_list_id == 'saved_all') {
                $user_sql = "SELECT user_id FROM groups_social_subscribers WHERE group_id = " . to_sql($groupId, 'Number');
            } elseif($saved_user_list_id == 'saved_male') {
                $user_sql = "SELECT gs.user_id FROM groups_social_subscribers AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.group_id = " . to_sql($groupId, 'Number') . " AND gs.group_user_id=" . to_sql(guid(), 'Number') . " AND u.orientation=1";
            } elseif ($saved_user_list_id == 'saved_female') {
                $user_sql = "SELECT gs.user_id FROM groups_social_subscribers AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.group_id = " . to_sql($groupId, 'Number') . " AND gs.group_user_id=" . to_sql(guid(), 'Number') . " AND u.orientation=2";
            } elseif ($saved_user_list_id == 'saved_couple') {
                $user_sql = "SELECT gs.user_id FROM groups_social_subscribers AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.group_id = " . to_sql($groupId, 'Number') . " AND gs.group_user_id=" . to_sql(guid(), 'Number') . " AND u.orientation=5";
            } elseif ($saved_user_list_id == 'saved_transgender') {
                $user_sql = "SELECT gs.user_id FROM groups_social_subscribers AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.group_id = " . to_sql($groupId, 'Number') . " AND gs.group_user_id=" . to_sql(guid(), 'Number') . " AND u.orientation=6";
            } elseif ($saved_user_list_id == 'saved_nonbinary') {
                $user_sql = "SELECT gs.user_id FROM groups_social_subscribers AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.group_id = " . to_sql($groupId, 'Number') . " AND gs.group_user_id=" . to_sql(guid(), 'Number') . " AND u.orientation=7";
            }

            if (in_array($saved_user_list_id, ['saved_all', 'saved_male', 'saved_female', 'saved_couple', 'saved_transgender', 'saved_nonbinary'])) {
                $users_rows = DB::rows($user_sql);
                $user_ids = [];
                foreach ($users_rows as $row) {
                    $user_id = $row['user_id'];
                    $user_ids[] = $user_id;
                }

                $selected_members = $user_ids;
            } else {
                $sql = "SELECT user_ids FROM saved_user_list WHERE id = " . to_sql($saved_user_list_id, 'Number');
                $saved_row = DB::row($sql);
    
                $selected_members = json_decode($saved_row['user_ids'], true);
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

                    $groupId = Groups::getParamId();
                    $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
                    $group = DB::row($gsql);

                    $group_nameseo = $group['name_seo'];

                    $url_to = $g['path']['url_main'] . $group_nameseo;
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
        $guid = guid();
        $uid = User::getParamUid(0);

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();
        $groupId = Groups::getParamId();

        if (!$groupId) {
            Common::toHomePage();
        }

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);

        $group_nameseo = $group['name_seo'];

        $urlpage = $g['path']['url_main'] . $group_nameseo . "/group_mail";
        $html->setvar('url_page', $urlpage);

        $select_url = $g['path']['url_main'] . $group_nameseo . "/select_group_users";
        $html->setvar('url_select_page', $select_url);

        // $saved_user_list = self::getSavedUserList($groupId);
        // $html->setvar('saved_user_list', $saved_user_list);

        $select_group_user_url = $g['path']['url_main'] . $group_nameseo . "/select_group_users";
        $html->setvar('select_group_user_url', $select_group_user_url);

        parent::parseBlock($html);
    }

    function getSavedUserList($groupId)
    {
        $sql = "SELECT id, title FROM saved_user_list WHERE user_id = " . to_sql(guid(), 'Number') . " AND event_id = " . to_sql($groupId, 'Number') . " AND type = 'group'";
        return Common::getSavedUserList($sql);
    }

    function getNameSeo()
    {
        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if(!$group) {
            Common::toHomePage();
        } 

        $group_nameseo = $group['name_seo'];

        return $group_nameseo;
    }

    function isOwner() {
        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if(!$group) {
            Common::toHomePage();
        } 

        if($g_user['user_id'] != $group['user_id']) {
            return false;
        } else {
            return true;
        }
    }
}

$page = new CGroupMail("", $g['tmpl']['dir_tmpl_main'] . "group_mail_compose.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$saved_user_list = new CSelectUserList('saved_user_list', $g['tmpl']['dir_tmpl_main'] . "select_user_list.html");
$saved_user_list->event_id = $groupId;
$saved_user_list->userlist_type = 'group';
$page->add($saved_user_list);

$mail_templates_list = new CMailTemplates('mail_templates_list', $g['tmpl']['dir_tmpl_main'] . "mail_templates.html");
$mail_templates_list->template_type = 'GROUP_MAIL';
$page->add($mail_templates_list);

include './_include/core/main_close.php';
