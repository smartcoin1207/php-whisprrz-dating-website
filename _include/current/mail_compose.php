<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

if(!Common::isOptionActive('mail')) {
    redirect(Common::toHomePage());
}
payment_check('mail_compose');

class CCompose extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";
	var $message_sent = false;
	var $id;
	var $subject;
	var $text;
	var $type = 'plain';
	function action()
	{
		global $g_user;
		global $g;
		global $l;

		$cmd = get_param("cmd", "");
		if ($cmd == "reply")
		{
			$msg = intval(get_param("msg"));
			$sql = "
				SELECT u.user_id AS user_from, u2.user_id AS user_to,
				m.id, m.subject, m.text, m.type AS mtype
				FROM ((mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_from)
				LEFT JOIN user AS u2 ON u2.user_id=m.user_to)
				WHERE m.id=" . $msg . " AND m.user_id=" . $g_user['user_id'] . "
			";
			DB::query($sql);
			if ($row = DB::fetch_row())
			{
				$this->id = $row['user_from'] != $g_user['user_id'] ? $row['user_from'] : $row['user_to'];
				$this->subject = mail_chain($row['subject']);
                $text = str_replace("\n", "\n> ", $row['text']);
                $text = str_replace("\n> >","\n>>",$text);
				if ($row['mtype'] == 'plain' or $row['mtype'] == '') $this->text = "\n\n\n> " . $text;
				else $this->text = "";
			}
		}
		if ($cmd == "forward")
		{
			$msg = intval(get_param("msg"));
			$sql = "
				SELECT u.user_id AS user_from, u2.user_id AS user_to,
				m.id, m.subject, m.text, m.type AS mtype
				FROM ((mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_from)
				LEFT JOIN user AS u2 ON u2.user_id=m.user_to)
				WHERE m.id=" . $msg . " AND m.user_id=" . $g_user['user_id'] . "
			";
			DB::query($sql);
			if ($row = DB::fetch_row())
			{
				$this->subject = mail_chain($row['subject'], 'Fw');
				if ($row['mtype'] == 'plain' or $row['mtype'] == '') {
                $text = str_replace("\n", "\n> ", $row['text']);
                $text = str_replace("\n> >","\n>>",$text);
				$this->text = "\n\n\n> " . $text;
				} else {
					$this->text = urlencode($row['text']);
					$this->type = 'postcard';
				}
			}
		}
		if ($cmd == "sent")
		{
            $type = get_param('type');
			$name = get_param('name');

			$subject = Common::filterProfileText(strip_tags(get_param('subject')));
            if (trim($subject) == '') {
                $subject = l('no_subject');
            }

            $text = Common::filterProfileText(get_param('text'));
            if($type == 'postcard') {
                $text = urldecode($text);
            }
            $text = trim(strip_tags($text));

            if ($name != '' && $subject != '' && $text != '')
            {
                $textHash = md5(mb_strtolower($text, 'UTF-8'));
                if (User::isBanMails($textHash) || User::isBanMailsIp()) {
                    redirect('ban_mails.php');
                }

                $id = DB::result("SELECT user_id FROM user WHERE name=" . to_sql($name) . "");
                $block = User::isBlocked('mail', $id, guid());
                $to_myself = (guid() == to_sql($id, "Number"));
                $empty_text = (trim(get_param("text", "")) == '');

				if ($id != 0 and $block == 0 and !$to_myself and !$empty_text)
				{
                    $idMailFrom = 0;
                    $sqlInto = '';
                    $sqlValue = '';
                    if (get_param('type') != 'postcard') {
                        $sqlInto = ', text_hash';
                        $sqlValue = ', ' . to_sql($textHash);
                    }
                    if (get_param('save') == '1')
					{
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

					/* START - Divyesh - 01082023 */
					$userTo = User::getInfoBasic($id);

					Common::usersms('new_mail_sms', $userTo, 'set_sms_alert_rm');

					/* END - Divyesh - 01082023 */

                    if (Common::isEnabledAutoMail('mail_message')) {
                        DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($id, 'Number'));
                        if ($row = DB::fetch_row())
                        {
                            if ($row['set_email_mail'] != '2')
                            {
                                $textMail = (Common::isOptionActive('mail_message_alert')) ? $text : '';
                                $vars = array('title' => $g['main']['title'],
                                              'name'  => $g_user['name'],
                                              'text'  => $textMail,
                                              'mid' => $idMailTo);
                                Common::sendAutomail($row['lang'], $row['mail'], 'mail_message', $vars);
                            }
                        }
                    }

                    $this->message_sent = true;
                    if ($this->message_sent) {
                        $to = get_param('page_from', '');
                        set_session('send_message', true);
                        //if ($to != 'mail.php') {
                            //$to .= '&mail=sent';
                        //}
                        redirect($to);
                        //redirect('mail.php');
                    }

				}
				elseif ($block > 0)
				{
                    $this->message = l('You are in Block List');
					$this->message .= '<br>';
				}
				elseif ($to_myself)
				{
                    $this->message = l('You can not do this with yourself!');
					$this->message .= '<br>';
				}
				elseif ($empty_text)
				{
                    $this->message = l('Message text is empty!');
					$this->message .= '<br>';
				}
				else
				{
                    $this->message = l('Incorrect Username');
					$this->message .= '<br>';
				}
			}
			else
			{
                $this->message = l('Incorrect Username, subject or message');
				$this->message .= '<br>';
			}
		}
	}
	function parseBlock(&$html)
	{
            global $g_user;
            $uid = guid();

            $to = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'display=profile')) ? $_SERVER['HTTP_REFERER'] : 'mail.php';
            #$to = get_param("page_from", $to);
            #$to = "mail.php";
            $html->setvar("message", $this->message);

            $html->setvar("name", get_param("name", ''));
            $html->setvar("subject", he($this->subject));
            $html->setvar("text", $this->text);

            if (get_param('cmd', '') == 'reply') {
               $html->parse('reply_js');
               $html->parse('reply_compare_js');
            }

            $sql = "SELECT U.name, SUM(IF(M.id IS NULL, 0, 1)) as count_mail
                  FROM `user` as U,
                       `friends_requests` as F
             LEFT JOIN `mail_msg` as M
                    ON M.user_id = " . to_sql($uid, 'Number') . "
                       AND (M.user_from = " . to_sql($uid, 'Number') . " AND (F.user_id = M.user_to
                            OR F.friend_id = M.user_to)
                       OR   M.user_to = " . to_sql($uid, 'Number') . "	AND (F.user_id = M.user_from
					        OR F.friend_id = M.user_from))
                 WHERE (F.user_id = " . to_sql($uid, 'Number') . "
                        OR
                        F.friend_id = " . to_sql($uid, 'Number') . ")
                   AND F.accepted = 1
                   AND (U.user_id = F.friend_id OR U.user_id = F.user_id)
                   AND U.user_id != " . to_sql($uid, 'Number') . "
                 GROUP BY U.name
                 ORDER BY count_mail DESC LIMIT " . to_sql(Common::getOption('number_friends_show_mail'), 'Number');


        $friends = DB::rows($sql);
        $i = 0;
		$num_columns = 3;
		$total_checks = count($friends);
		$in_column = ceil(($total_checks) / $num_columns);

        foreach ($friends as $row) {
            $i++;
            $html->setvar('fname', User::nameOneLetterFull($row['name']));
            $html->setvar('fname_set', $row['name']);
            $html->setvar('count', $row['count_mail']);
			if ($i % $in_column == 0 and $i != 0 and $num_columns != 1) {
				$html->parse("favorite_column", false);
            } else {
				$html->setblockvar("favorite_column", "");
            }
			$html->parse("favorite", true);
		}


		if (isset($this->id))
		{
			$id = $this->id;
		} else {
			$ids = get_param_array("id");
			$id = isset($ids[0]) ? $ids[0] : 0;
		}

		DB::query("SELECT user_id, name FROM user WHERE user_id=" . to_sql($id, "Number") . " ");

		if ($row = DB::fetch_row())
		{
			$html->setvar("name", $row['name']);
			$html->parse("add_id", true);
		}
		else
		{
			$html->parse("add_name", true);
		}


		$html->setvar("page_from", $to);

		if ($this->type == 'plain')  $html->parse("plain", true);
		else  $html->parse("postcard", true);
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }

		parent::parseBlock($html);
	}
}

$page = new CCompose("", $g['tmpl']['dir_tmpl_main'] . "mail_compose.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$folders = new CFolders("folders", $g['tmpl']['dir_tmpl_main'] . "_folders.html");
$page->add($folders);

$mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
$mailMenu->setActive('compose');
$page->add($mailMenu);

include("./_include/core/main_close.php");
?>
