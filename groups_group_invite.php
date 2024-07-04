<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/groups/custom_head.php");
require_once("./_include/current/groups/header.php");
require_once("./_include/current/groups/sidebar.php");
require_once("./_include/current/groups/tools.php");
require_once("./_include/current/groups/group_show.php");
require_once("./_include/current/groups/group_image_list.php");
require_once("./_include/current/groups/group_member_list.php");
require_once("./_include/current/groups/group_comment_list.php");
require_once("./_include/current/groups/group_forum_list.php");
require_once("./_include/current/groups/group_comment_list_sidebar.php");

function generate_group_key($gid){
	$key = md5($gid.time().microtime().md5(rand(100,100000)));
	return $key;
}

class CGroups extends CHtmlBlock
{
	var $m_group;

	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $subject = get_param("invite_subject", "");
            $text_original = $text = get_param("invite_message", "");

            $group_id = get_param('group_id');
	        $group = CGroupsTools::retrieve_group_by_id($group_id);
	        if($subject && $text && $group && ($group['user_id'] == $g_user['user_id'] || CGroupsTools::is_group_member($group['group_id'])))
	        {
                // JOIN URL
                $url = "http://".$_SERVER['HTTP_HOST'] . str_replace("\\", "", dirname($_SERVER['PHP_SELF'])) . "/groups_invite.php?cmd=join&group_id=" . $group['group_id'] . "&key=";

                // MAIL TEXT
                //$text_source = $text."\n".l('groups_to_join_click_here');

                $gid = $group['group_id'];

                $friend_requests = DB::rows("SELECT * FROM friends_requests WHERE (user_id='".$g_user['user_id']."' OR friend_id='".$g_user['user_id']."') AND accepted=1");
	            foreach($friend_requests as $friend_request)
	            {
	                $friend_id = isset($row['fr_user_id']) ? $row['fr_user_id'] : (($friend_request['user_id'] == $g_user['user_id']) ? $friend_request['friend_id'] : $friend_request['user_id']);
	                if(get_param('friend_' . $friend_id))
	                {
                        $id = $friend_id;
                        $block = User::isBlocked('mail', $id, guid());
	                    //$block = 0;
	                    if ($id != 0 and $block == 0)
	                    {
                                    // GENERATE KEY - WILL BE USED FOR AUTHORIZATION
                                    $key = generate_group_key($gid);
                                    //$text = str_replace("{url}",$url.$key,$text_source);

                                    DB::execute("DELETE FROM groups_invite WHERE group_id=" . $group['group_id'] . " AND user_id=" . to_sql($id, "Number"));

                                    // SAVE KEY - FIRST OF ALL
                                    DB::execute("INSERT INTO groups_invite
                                                    SET group_id=" . $group['group_id'] . ",
					                                    user_id=" . to_sql($id, "Number") . ",
					                                    invite_key='$key',
					                                    created_at = NOW();");
                                    DB::execute('UPDATE user SET last_visit=last_visit, new_mails=new_mails+1 WHERE user_id=' . to_sql($id, 'Number'));

                                    //DB::query("SELECT name, orientation, mail, set_email_mail, lang FROM user WHERE user_id='" . $id . "'");
                                    DB::query('SELECT `mail`, `lang` FROM `user` WHERE `user_id` = ' . to_sql($id, 'Number'));
                                    if ($row = DB::fetch_row()) {
                                        if (Common::isEnabledAutoMail('invite_group')) {
                                            $vars = array('subject' => $subject,
                                                          'message' => $text_original,
                                                          'key' => $key,
                                                          'group_id' => $group['group_id']);
                                            Common::sendAutomail($row['lang'], $row['mail'], 'invite_group', $vars);
                                        }

                                        $vars = array('subject' =>  $subject,
                                                      'message' => $text_original,
                                                      'url_start' => "<a href='" . $url . $key . "'>",
                                                      'url_end' => "</a>" );
                                        $emailAutoSait = Common::automailInfo('invite_group_site', $row['lang']);
                                        $subject = Common::replaceByVars($emailAutoSait['subject'], $vars);
                                        $text = Common::replaceByVars($emailAutoSait['text'], $vars);
                                        $sql = "INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent)
                                                     VALUES(" . to_sql($id, "Number") . ",
                                                     " . $g_user['user_id'] . ",
                                                     " . to_sql($id, "Number") . ",
                                                     " . 1 . ",
                                                     " . to_sql($subject,"Text") . ",
                                                     " . to_sql($text,"Text") . ",
                                                     " . time() . ")";
                                        DB::execute($sql);

                                        $userToInfo = User::getInfoBasic($id);

                                        if($userToInfo)
                                        {
                                            Common::usersms('new_mail_sms', $userToInfo, 'set_sms_alert_rm');
                                        }
                                    }

		                }
	                }
	            }

	            $emails = preg_split("/,/",trim(get_param("invite_emails")));

	            if(is_array($emails)){
				    foreach($emails as $mail){
					    // working only with real emails
						if(trim($mail)) {
							// GENERATE KEY - WILL BE USED FOR INVITE INDENTIFICATION
							$key = generate_group_key($gid);
							//$text = str_replace("{url}",$url.$key,$text_source);

	                            // SAVE KEY - FIRST OF ALL
	                        DB::execute("
	                            INSERT INTO groups_invite SET
	                            group_id=" . $group['group_id'] . ",
	                            user_id=" . to_sql(0, "Number") . ",
	                            invite_key='$key',
	                            created_at = NOW();");

                            if (Common::isEnabledAutoMail('invite_group')) {
                                $vars = array('subject' =>  $subject,
                                              'message' => $text_original,
                                              'key' => $key,
                                              'group_id' => $group['group_id']);

                                Common::sendAutomail(Common::getOption('lang_loaded', 'main'), trim($mail), 'invite_group', $vars);
                            }
					    }
                    }
				}

				redirect('groups_group_show.php?group_id=' . $group['group_id'] . '&alert=invites_send');
	        }
	        else
	            redirect('groups.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $lastFriend = 0;
        $group_id = get_param('group_id');
        $group = CGroupsTools::retrieve_group_by_id($group_id);
        if($group)
        {
            $title_length = 32;

            $html->setvar('group_id', $group['group_id']);
            $html->setvar('group_title', strcut(to_html($group['group_title']), $title_length));
            $html->setvar('group_title_full', to_html($group['group_title']));

            $friend_requests = DB::rows("SELECT * FROM friends_requests WHERE (user_id='".$g_user['user_id']."' OR friend_id='".$g_user['user_id']."') AND accepted=1");
            foreach($friend_requests as $friend_request)
            {
                $friend_id = isset($row['fr_user_id']) ? $row['fr_user_id'] : (($friend_request['user_id'] == $g_user['user_id']) ? $friend_request['friend_id'] : $friend_request['user_id']);

                $sql = "SELECT COUNT(`group_id`) FROM `groups_group_member`
                         WHERE `user_id` = " . to_sql($friend_id, 'Number') . "
                           AND `group_id` = " . to_sql($group_id, 'Number');
                if (!DB::result($sql))
                {
                    $user = DB::row("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".to_sql($friend_id,"Number")."' LIMIT 0, 1");
                    $name = explode(' ', to_html($user['name']));
                    $html->setvar("user_name", $name[0]);
                    $html->setvar("user_name_full", to_html($user['name']));
                    $html->setvar("user_photo", $g['path']['url_files'] . User::getPhotoDefault($friend_id,"r"));
                    $html->setvar("user_id", $user['user_id']);
                    $lastFriend++;
                    $html->parse('friend');
                }
            }
            $noEmpty = (floor($lastFriend / 6)+1) * 6 - $lastFriend;
            if($noEmpty > 0 && ($noEmpty != 6 || $lastFriend == 0)) {
                for($i = 0; $i != $noEmpty; $i++)
                    $html->parse('friend_empty');
            }
            if (Common::isEnabledAutoMail('invite_group')){
                $html->parse('emails');
            }

        }

		parent::parseBlock($html);
	}
}

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups_group_invite.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$groups_custom_head = new CGroupsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_groups_custom_head.html");
$header->add($groups_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$groups_group_show = new CGroupsGroupShow("groups_group_show", $g['tmpl']['dir_tmpl_main'] . "_groups_group_show.html");
$page->add($groups_group_show);
$groups_group_image_list = new CGroupsGroupImageList("groups_group_image_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_image_list.html");
$groups_group_show->add($groups_group_image_list);

$groups_header = new CGroupsHeader("groups_header", $g['tmpl']['dir_tmpl_main'] . "_groups_header.html");
$page->add($groups_header);

$groups_group_member_list = new CGroupsGroupMemberList("groups_group_member_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_member_list.html");
$page->add($groups_group_member_list);
$groups_group_comment_list_sidebar = new CGroupsGroupCommentListSidebar("groups_group_comment_list_sidebar", $g['tmpl']['dir_tmpl_main'] . "_groups_group_comment_list_sidebar.html");
$page->add($groups_group_comment_list_sidebar);

$groups_group_forum_list = new CGroupsGroupForumList("groups_group_forum_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_forum_list.html");
$groups_group_forum_list->m_need_not_found_message = false;
$page->add($groups_group_forum_list);

include("./_include/core/main_close.php");
