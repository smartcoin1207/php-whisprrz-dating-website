<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include "./_include/core/main_start.php";
include("./_include/current/mail.templates.class.php");

function generate_group_key($gid)
{
    $key = md5($gid . time() . microtime() . md5(rand(100, 100000)));
    return $key;
}

class CGroupsInvite extends CHtmlBlock
{
    public $m_group;

    public function action()
    {
        global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd', '');
        if ($cmd == 'sent' && self::isOwnerOrModerator()) {

            $group_id = Groups::getParamId();
            if (!$group_id) {
                Common::toHomePage();
            }

            $name = get_param("name", "");
            $text = get_param("text", "");
            $subject = get_param('subject', '');

            $invite_user_id = DB::result("SELECT user_id FROM user WHERE name=" . to_sql($name) . "");

            if ($invite_user_id && $text && $subject) {
                // JOIN URL
                $url = "http://" . $_SERVER['HTTP_HOST'] . str_replace("\\", "", dirname($_SERVER['PHP_SELF'])) . "/groups_invite.php?cmd=join&group_id=" . $group_id . "&key=";

                $block = User::isBlocked('mail', $invite_user_id, guid());
                //$block = 0;
                if ($invite_user_id != 0 and $block == 0) {

                    // GENERATE KEY - WILL BE USED FOR AUTHORIZATION

                    $key = generate_group_key($group_id);

                    DB::execute("DELETE FROM groups_invite WHERE group_id=" . $group_id . " AND user_id=" . to_sql($invite_user_id, "Number"));

                    // SAVE KEY - FIRST OF ALL
                    DB::execute("INSERT INTO groups_invite
                                                    SET group_id=" . $group_id . ",
                                                        user_id=" . to_sql($invite_user_id, "Number") . ",
                                                        invite_key='$key',
                                                        created_at = NOW();");
                    DB::execute('UPDATE user SET last_visit=last_visit, new_mails=new_mails+1 WHERE user_id=' . to_sql($invite_user_id, 'Number'));

                    DB::query('SELECT `mail`, `lang` FROM `user` WHERE `user_id` = ' . to_sql($invite_user_id, 'Number'));
                    if ($row = DB::fetch_row()) {
                        if (Common::isEnabledAutoMail('invite_group')) {
                            $vars = array('subject' => $subject,
                                'message' => $text,
                                'key' => $key,
                                'group_id' => $group_id);
                            Common::sendAutomail($row['lang'], $row['mail'], 'invite_group', $vars);
                        }

                        $vars = array('subject' => $subject,
                            'message' => $text,
                            'url_start' => "<a href='" . $url . $key . "'>",
                            'url_end' => "</a>");
                        $emailAutoSite = Common::automailInfo('invite_group_site', $row['lang']);
                        $subject = Common::replaceByVars($emailAutoSite['subject'], $vars);
                        $text = Common::replaceByVars($emailAutoSite['text'], $vars);
                        $sql = "INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent)
                                     VALUES(" . to_sql($invite_user_id, "Number") . ",
                                     " . $g_user['user_id'] . ",
                                     " . to_sql($invite_user_id, "Number") . ",
                                     " . 1 . ",
                                     " . to_sql($subject, "Text") . ",
                                     " . to_sql($text, "Text") . ",
                                     " . time() . ")";
                        DB::execute($sql);

                        $userToInfo = User::getInfoBasic($invite_user_id);

                        if($userToInfo)
                        {
                            Common::usersms('new_mail_sms', $userToInfo, 'set_sms_alert_rm');
                        }
                    }
                }
            }
        }
    }

    public function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $group_id = Groups::getParamId();

        if (Common::isEnabledAutoMail('invite_group')) {
            $html->parse('emails');
        }

        $group_nameseo = self::getNameSeo();

        if (!self::isOwnerOrModerator()) {
            $group_url = $g['path']['url_main'] . $group_nameseo;
            redirect($group_url);
        }

        $request_post_url = $g['path']['url_main'] . $group_nameseo . '/group_invite';
        $html->setvar('request_post_url', $request_post_url);

        $invite_user_id = get_param('user_id', '');
        if($invite_user_id) {
            $sql_user = "SELECT * FROM user WHERE user_id = " . to_sql($invite_user_id, 'Text') ;
            $invite_user = DB::row($sql_user);
            if($invite_user) {
                $html->setvar('username', $invite_user['name']);
                $html->parse('invite_user_name_label', false); 
            } else {
                $html->parse('invite_user_name_text', false);

            }
            
        } else {
            $html->parse('invite_user_name_text', false);
        }

        parent::parseBlock($html);
    }

    public function getNameSeo()
    {

        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }

        $group_nameseo = $group['name_seo'];

        return $group_nameseo;
    }

    public function isOwnerOrModerator()
    {
        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }

        $sql = "SELECT  * FROM groups_social_subscribers WHERE group_id = " . to_sql($groupId, 'Text') . "AND user_id = " . to_sql(guid(), 'Text'); 
        $my_subscriber  = DB::row($sql);
        $moderator_options = json_decode($my_subscriber['moderator_options'], true);

        if ($g_user['user_id'] != $group['user_id'] && !$moderator_options['group_invite']) {
            return false;
        } else {
            return true;
        }
    }
}

$page = new CGroupsInvite("", $g['tmpl']['dir_tmpl_main'] . "group_invite.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$mail_templates_list = new CMailTemplates('mail_templates_list', $g['tmpl']['dir_tmpl_main'] . "mail_templates.html");
$mail_templates_list->template_type='GROUP_INVITE';
$page->add($mail_templates_list);


include "./_include/core/main_close.php";
