<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#почта
class CFolders extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
		global $no_folders;

        CBanner::getBlock($html, 'right_column');
		if (!(isset($no_folders) and $no_folders)) {
			$mc = DB::result("SELECT COUNT(id) FROM mail_msg WHERE user_id=" . $g_user['user_id'] . "");

			$mc = round($mc*100/Common::getOption('mails_limit_max'));
            if ($mc > 100) $mc = 100;
            $me = 100 - $mc;

			$html->setvar("mc", $mc);
			$html->setvar("me", $me);
			$i = 0;
			DB::query("SELECT id, name FROM mail_folder WHERE user_id=" . $g_user['user_id'] . "");
			while ($row = DB::fetch_row()) {
				$html->setvar("id", $row['id']);
				$html->setvar("name", strip_tags($row['name']));
				$html->parse("myfolder", true);
				$i++;
			}
            $count =  DB::result("SELECT COUNT(user_id) FROM mail_msg WHERE user_id=".guid()." AND  `folder`=2");
            if($count > 0) {
                $html->parse('trash_empty_on');
            }
			if ($i < 5) $html->parse("addfolder", true);
			parent::parseBlock($html);
		}
	}
}

class CHtmlMailList extends CUsers
{
	var $m_on_page = 20;
	var $m_folder = 1;
	var $total_init;
	function action()
	{
		global $g_user, $g_info;
		global $g;

		DB::execute("UPDATE user SET new_mails=(SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder = 1 AND user_id=" . $g_user['user_id'] . ") WHERE user_id=" . $g_user['user_id'] . "");

		$cmd = get_param("cmd", "");
		if ($cmd == "delete") {
			$countDelete = 0;
			$sql = array();
			$folder = get_param("folder", "");
			$id = get_param_array("id");
			$action = get_param('action');
			foreach ($id as $k => $v) {
				if ($folder == 2)
					DB::execute("
						DELETE FROM mail_msg
						WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($v, "Number") . "
					");
				else
					DB::execute("
						UPDATE mail_msg
						SET folder=2
						WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($v, "Number") . "
					");
				$sqlM = "SELECT `new`
                                          FROM `mail_msg`
                                         WHERE `id` = " . to_sql($v, 'Number') . "
                                         LIMIT 1";
				$new = DB::result($sqlM);
				if ($action == 1 && $folder == 1 && $new == 'Y') {
					$countDelete++;
				}
			}
			if ($action == 'delete_one') {
				set_session('delete_message', true);
			}
			if ($countDelete > 0) {
				$countMail = $g_user['new_mails'] - $countDelete;
				$sql['new_mails'] =  $countMail;
				DB::update('user', $sql, 'user_id = ' . to_sql($g_user['user_id'], 'Number'));
				$g_info['new_mails'] = $countMail;
			}
		}
		if ($cmd == "move") {
			$id = get_param_array("id");
			foreach ($id as $k => $v) {
				DB::execute("
					UPDATE mail_msg
					SET folder=" . $this->m_folder . "
					WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($v, "Number") . "
				");
			}
			$g_info['new_mails'] = DB::result("SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder = 1 AND user_id=" . $g_user['user_id']);
			DB::execute("UPDATE user SET new_mails={$g_info['new_mails']} WHERE user_id=" . $g_user['user_id'] . "");
		}
		if ($cmd == "empty_trash") {
			DB::execute("
				DELETE FROM mail_msg
				WHERE user_id=" . $g_user['user_id'] . " AND folder=2
			");
		}
	}
	function parseBlock(&$html)
	{
		global $l;
		global $g;
		global $g_user;
		global $g_info;

		$opts = DB::db_options("SELECT id, name FROM mail_folder WHERE user_id=" . $g_user['user_id'] . " OR user_id=0 ORDER BY id", "");
		$html->setvar("folder_options", $opts);
		if ($this->m_folder == 1) $fname = l('Inbox');
		elseif ($this->m_folder == 2) $fname = l('Trash');
		elseif ($this->m_folder == 3) $fname = l('Sent mail');
		else $fname = DB::result("SELECT name FROM mail_folder WHERE id=" . $this->m_folder . "");

		if ($this->m_folder == 1) {
			$html->setvar('folder_style', '');
			$html->parse('inbox_navigation', true);
			$html->setvar('new_mails_folder', $g_info['new_mails']);
		} else if ($this->m_folder == 2) {
			$html->setvar('folder_style', ' trash');
			$html->parse('trash_navigation', true);
		} else if ($this->m_folder == 3) {
			$html->setvar('folder_style', ' sent');
			$html->parse('sent_navigation', true);
		}
		//$html->setvar("folder", strip_tags($fname));
		if ($this->m_folder == 1) {
			$this->m_folder_total = $g_info['new_mails'];
		}
		$this->m_folder_name = $fname;
		$html->setvar("mail_folder_id", $this->m_folder);
		if ($this->m_folder == 3)
			$html->setvar('from_to', l('to'));
		else
			$html->setvar('from_to', l('from'));

		// MAIL FOLDER ICON
		if ($this->m_folder > 0 && $this->m_folder < 4) $html->setvar("selected_folder", $this->m_folder);
		$html->parse('yes_images');
		// MAIL FOLDER ICON
		if (Common::isOptionActive('wink')) {
			$html->parse('wink_on', false);
		}

		$send = get_session('send_message');
		$delete = get_session('delete_message');
		if ($send == true || $delete == true) {
			if ($send == true) {
				$msg = l('send_message');
				delses('send_message');
			} else {
				$msg = l('delete_message');
				delses('delete_message');
			}
			$html->setvar('display_search_email', 'none');
			$html->setvar('info_message', $msg);
			$html->parse('info_message', false);
			$html->parse('info_message_js', false);
		} else {
			$html->setvar('display_search_email', 'block');
		}
		if ($this->m_search != '') {
			$html->setvar('search_text', $this->m_search);
		}
		$count = DB::result($this->m_sql_count . " WHERE " . $this->m_sql_where);
		if ($count > 0) {
			$html->parse('table_header_checkbox');
		}
		parent::parseBlock($html);
	}
	function init()
	{
		parent::init();

		global $g_user;

		$this->m_sql_count = "SELECT COUNT(m.id)
                                FROM (mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_from)
                                " . $this->m_sql_from_add;

		$like = '';
		if ($this->m_search !== false) {
			$like = "(IF(m.subject LIKE '%" . to_sql($this->m_search, 'Plain') . "%', 2, 0) + IF(m.text LIKE '%" . to_sql($this->m_search, 'Plain') . "%', 1, 0)) AS orders,";
		}

		if ($this->m_folder == 3) {
			$this->m_sql = "
			SELECT u.user_id, u.gender, u.orientation, u.name, u.last_visit, u.gold_days, u.type, u.rating, u.relation, u.partner_type,
			(DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))) AS age,
			 " . $like . "
			 m.id, m.subject, m.date_sent, IF(m.receiver_read = 'N' OR m.receiver_read = '', 'Y', 'N') as new, m.system,
			 u.is_photo, u.city, u.state, u.country, u.partner_type, u.nsc_couple_id, u.set_nsc_banner_activity, u.set_events_banner_activity, u.nsc_phone, u.nsc_join_phone, u.set_my_presence_couples, u.set_my_presence_everyone, u.set_my_presence_males, u.set_my_presence_females,  u.set_my_presence_transgender,  u.set_my_presence_nonbinary, u.set_profile_visitor,u.set_profile_visitor_couples, u.set_profile_visitor_males, u.set_profile_visitor_females, u.set_profile_visitor_transgender, u.set_profile_visitor_nonbinary, u.set_album_video_couples, u.set_album_video_everyone, u.set_album_video_males, u.set_album_video_females, u.set_album_video_transgender, u.set_album_video_nonbinary
	  FROM (mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_to)
				" . $this->m_sql_from_add; //nnsscc-diamond-20200328
			// , u.set_photo_couples, u.set_photo_everyone, u.set_photo_males, u.set_photo_females,u.set_album_couples, u.set_album_everyone, u.set_album_males, u.set_album_females
		} else {
			$this->m_sql = "SELECT u.user_id, u.gender, u.orientation, u.name, u.last_visit, u.gold_days, u.type, u.rating, u.relation, u.partner_type,	

                       (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))) AS age,
                       " . $like . "
                       m.id, m.subject, m.date_sent, m.new, m.system,
                       u.is_photo, u.city, u.state, u.country, u.partner_type, u.nsc_couple_id, u.set_nsc_banner_activity, u.set_events_banner_activity, u.nsc_phone, u.nsc_join_phone, u.set_my_presence_couples, u.set_my_presence_everyone, u.set_my_presence_males, u.set_my_presence_females,u.set_my_presence_transgender,  u.set_my_presence_nonbinary, u.set_profile_visitor,u.set_profile_visitor_couples, u.set_profile_visitor_males, u.set_profile_visitor_females, u.set_profile_visitor_transgender, u.set_profile_visitor_nonbinary, u.set_album_video_couples, u.set_album_video_everyone, u.set_album_video_males, u.set_album_video_females, u.set_album_video_transgender, u.set_album_video_nonbinary
                  FROM (mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_from)
				" . $this->m_sql_from_add; //nnsscc-diamond-20200328
			// ,  u.set_photo_couples, u.set_photo_everyone, u.set_photo_males, u.set_photo_females,u.set_album_couples, u.set_album_everyone, u.set_album_males, u.set_album_females
		}

		//$this->total_init = DB::result($this->m_sql_count . " WHERE " . $this->m_sql_where);

		$this->m_field['id'] = array("id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['name_short'] = array("name_short", null);
		$this->m_field['subject'] = array("subject", null);
		$this->m_field['last_visit'] = array("last_visit", null);
		$this->m_field['date_sent'] = array("date_sent", null);
		$this->m_field['new'] = array("new", null);
		$this->m_field_default = $this->m_field;

		$this->m_params = set_param("display", "list");
		$this->m_params = del_param("cmd", $this->m_params);
		$this->m_params = del_param("id", $this->m_params);
		$this->m_params = del_param("display", $this->m_params);
		$this->m_params = del_param("offset", $this->m_params);
		$this->m_params_pages = set_param("display", "list", $this->m_params);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		global $l;

		$row['subject'] = strip_tags($row['subject']);
		$this->m_field['subject'][1] = $row['subject'];

		#echo $row['subject'] . "\r\n" . $this->m_field['subject'][1] . "\r\n";
		parent::onItem($html, $row, $i, $last);

		if (!isset($row['country_title'])) $this->m_field['country_title'][1] = '-';
		if (!isset($row['state_title'])) $this->m_field['state_title'][1] = '-';
		if (!isset($row['city_title'])) $this->m_field['city_title'][1] = '-';

		if ($row['new'] == 'Y') {
			$html->setblockvar("mail_old", "");
			$html->parse("mail_new", false);
			$html->setvar("unread", "unread");
			$html->setvar("bold", "<b>");
			$html->setvar("bold_end", "</b>");
		} else {
			$html->setblockvar("mail_new", "");
			$html->parse("mail_old", false);
			$html->setvar("unread", "");
			$html->setvar("bold", "");
			$html->setvar("bold_end", "");
		}
		//$this->m_field['subject'][1] = mb_strlen($row['subject'],"UTF-8") > 30 ? trim(mb_substr($row['subject'], 0, 30,"UTF-8")) . "..." : $row['subject'];
		$this->m_field['date_sent'][1] = Common::dateFormat($row['date_sent'], 'mail_date_sent', FALSE);
		$this->m_field['name'][1] = $row['name'];
		$this->m_field['name_short'][1] = User::nameOneLetterFull($row['name']);
		// rows
		$html->setvar("row", ($i + 1) % 2 + 1);

		if (Common::isMobile()) {
			if (!$row['subject']) $row['subject'] = '...';
			if (date("Ymd") == date("Ymd", $row['date_sent'])) {
				$formatName = 'mobile_mail_datetime_today';
			} else {
				$formatName = 'mobile_mail_datetime';
			}
			$this->m_field['date_sent'][1] = Common::dateFormat($row['date_sent'], $formatName, FALSE);
			$this->m_field['subject'][1] = strcut($row['subject'], 15);
		}
		if ($row['system']) {
			$html->clean('from');
			$html->clean('from_user');
			$html->parse('from_admin', false);
		} else {
			$html->clean('from_admin');
			$html->parse('from', false);
			$html->parse('from_user', false);
		}
	}
}
class CHtmlMailText extends CUsers
{
	var $m_on_page = 1;
	var $m_folder = 1;
	function action()
	{
		global $g_user;
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "delete")
		{
			$id = get_param_array("id");
			foreach ($id as $k => $v)
			{
				DB::execute("
					UPDATE mail_msg
					SET folder=2
					WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($v, "Number") . "
				");
			}
		}
		if ($cmd == "move")
		{
			$id = get_param_array("id");
			foreach ($id as $k => $v)
			{
				DB::execute("
					UPDATE mail_msg
					SET folder=" . $this->m_folder . "
					WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($v, "Number") . "
				");
			}
            $g_info['new_mails'] = DB::result("SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder = 1 AND user_id=" . $g_user['user_id']);
            DB::execute("UPDATE user SET new_mails={$g_info['new_mails']} WHERE user_id=" . $g_user['user_id'] . "");
		}
		if ($cmd == "empty_trash")
		{
			DB::execute("
				DELETE FROM mail_msg
				WHERE user_id=" . $g_user['user_id'] . " AND folder=2
			");
		}
		parent::action();
	}
	function parseBlock(&$html)
	{
		global $g_user;
		global $g;
		global $l;

        $offset = ceil(get_param('offset', 0) / 20);
        $offset = ($offset - 1) * 20 + 1;
		$html->setvar('offset_list', $offset);

		$this->m_folder = 2;

		if($this->m_folder == 1)
		{
			$html->setvar('folder_style', '');
		}
		else if($this->m_folder == 2)
		{
			$html->setvar('folder_style', ' trash');
		}
		else if($this->m_folder == 3)
		{
			$html->setvar('folder_style', ' sent');
		}

		$opts = DB::db_options("SELECT id, name FROM mail_folder WHERE user_id=" . $g_user['user_id'] . " OR user_id=0 ORDER BY id", "");
		$html->setvar("folder_options", $opts);
		if ($this->m_folder == 1) $fname = l('Inbox');
		elseif ($this->m_folder == 2) $fname = l('Trash');
		elseif ($this->m_folder == 3) $fname = l('Sent mail');
		else $fname = DB::result("SELECT name FROM mail_folder WHERE id=" . $this->m_folder . "");
		$html->setvar("folder", strip_tags($fname));
		$html->setvar("mail_folder_id", $this->m_folder);

        // MAIL FOLDER ICON

        if($this->m_folder > 0 && $this->m_folder < 4) $html->setvar("selected_folder",$this->m_folder);

        // MAIL FOLDER ICON

		parent::parseBlock($html);
	}

	function init()
	{
		parent::init();
		global $g;
		global $g_user;

		$this->m_sql_count = "
			SELECT COUNT(m.id)
			FROM (mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_from)
			" . $this->m_sql_from_add . "
		";

        $like = '';
        if ($this->m_search !== false) {
            $like = "(IF(m.subject LIKE '%" . to_sql($this->m_search, 'Plain'). "%', 2, 0) + IF(m.text LIKE '%" . to_sql($this->m_search, 'Plain'). "%', 1, 0)) AS orders,";
        }

		if ($this->m_folder == 3)
		{
			$this->m_sql = "
				SELECT u.user_id, u.gender, u.last_visit, u.gold_days, u.type, u.name, u2.name AS user_from,  u.name AS user_to, u.partner_type,
				u.user_id AS user_to_id,
				u.orientation,
				(DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(u2.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(u2.birth, '00-%m-%d'))) AS age,
                " . $like . "
				m.id, m.subject,  u.set_my_presence_couples, u.set_my_presence_everyone, u.set_my_presence_males, u.set_my_presence_females, u.set_my_presence_transgender,  u.set_my_presence_nonbinary, m.date_sent, m.sent_id, m.receiver_read, m.new, m.text, m.type as mtype, m.system,
				u.is_photo, u.city, u.state, u.country, u.rating, u.relation
				FROM ((mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_to)
				LEFT JOIN user AS u2 ON u2.user_id=m.user_from)
				" . $this->m_sql_from_add . "
			";
		}
		else
		{
			$this->m_sql = "
				SELECT u.user_id, u.gender, u.last_visit, u.gold_days, u.type, u.name, u.name AS user_from,  u2.name AS user_to, u2.user_id AS user_to_id, u.orientation, u.partner_type,
				(DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(u2.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(u2.birth, '00-%m-%d'))) AS age,
                " . $like . "
                m.id, m.subject, u.set_my_presence_couples, u.set_my_presence_everyone, u.set_my_presence_males, u.set_my_presence_females, u.set_my_presence_transgender,  u.set_my_presence_nonbinary, m.date_sent, m.sent_id, m.receiver_read, m.new, m.text, m.type as mtype, m.system,
				u.is_photo,	u.city, u.state, u.country, u.rating, u.relation
				FROM ((mail_msg AS m LEFT JOIN user AS u ON u.user_id=m.user_from)
				LEFT JOIN user AS u2 ON u2.user_id=m.user_to)
				" . $this->m_sql_from_add . "
			";
		}
		$this->m_field['photo_id'] = array("photo", null);
		$this->m_field['last_visit'] = array("last_visit", null);
		$this->m_field['orientation'] = array("orientation", null);
		$this->m_field['id'] = array("id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['user_from'] = array("user_from", null);
		$this->m_field['user_to'] = array("user_to", null);
		$this->m_field['user_to_id'] = array("user_to_id", null);
		$this->m_field['subject'] = array("subject", null);
		$this->m_field['date_sent'] = array("date_sent", null);
		$this->m_field['new'] = array("new", null);
		$this->m_field['text'] = array("text", null);
		$this->m_field_default = $this->m_field;

		$this->m_params = set_param("display", "text");
		$this->m_params = del_param("cmd", $this->m_params);
		$this->m_params = del_param("id", $this->m_params);
		//$this->m_params = del_param("display", $this->m_params);
		//$this->m_params = del_param("offset", $this->m_params);
		$this->m_params_pages = set_param("display", "text", $this->m_params);
	}

    function onPostParse(&$html,$row=array()) {
        if(Common::isMobile()) {
            $photoDefaultSize = 'r';
        } else {
            $photoDefaultSize = 's';
        }

        if ($this->m_folder == 3) {
            if ($row['system']) {
                $html->setvar('photo', User::photoFileCheck(array('user_id' => 0, 'photo_id' => 0), $photoDefaultSize));
                $html->parse('to_admin');
            } else {
                $html->setvar('photo',User::getPhotoDefault($row['user_to_id'], $photoDefaultSize, false));
                $html->setvar('id_user', $row['user_to_id']);
                $html->parse('photo_buble');
                $html->parse('to');
            }
        } else {
            if ($row['system']) {
                $html->setvar('photo', User::photoFileCheck(array('user_id' => 0, 'photo_id' => 0), $photoDefaultSize));
                $html->parse('from_admin');
            } else {
                if (guid() == $row['user_id']) {
                    $html->setvar('id_user', $row['user_to_id']);
                    $html->setvar('photo',User::getPhotoDefault($row['user_to_id'], $photoDefaultSize, false));
                } else {
                    $html->setvar('id_user', $row['user_id']);
                    $html->setvar('photo',User::getPhotoDefault($row['user_id'], $photoDefaultSize, false));
                }
                $html->parse('photo_buble');
                $html->parse('from');
            }
        }
        if (Common::isOptionActive('im')) {
            $html->parse('im', false);
        }
        if(Common::isOptionActive('contact_blocking')) {
            $html->parse('contact_blocking_add');
        }
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }
        if ($this->m_folder == 1 && !$row['system'])
		{
			$html->parse('reply_button', false);
		}
        if (!$row['system']) {
            $html->parse('action_block', false);
        }
        $html->parse($this->m_name . "_item", false);
    }

	function onItem(&$html, $row, $i, $last)
	{
		global $g_user;
        global $g_info;
		global $g;
		global $l;

		$real_text = Common::parseLinksTag($row['text']);
		$row['subject'] = strip_tags($row['subject']);
		$row['text'] = strip_tags($row['text']);
        //$this->m_field['subject'][1] = strip_tags($this->m_field['subject'][1]);
        //$this->m_field['text'][1] = strip_tags($this->m_field['text'][1]);
		parent::onItem($html, $row, $i, $last);
		if (!isset($row['country_title'])) $this->m_field['country_title'][1] = '-';
		if (!isset($row['state_title'])) $this->m_field['state_title'][1] = '-';
		if (!isset($row['city_title'])) $this->m_field['city_title'][1] = '-';
		if ($row['mtype'] == 'postcard') {
			#$params = explode('|', $row['text']);
			#foreach ($params as $k => $param) if (strpos($param, '.swf')  !== false || strpos($param, '.mp3')  !== false || strpos($param, '.jpg')  !== false) $params[$k] = './_server/postcard/' . $param;
			#echo $row['text'] = implode('|', $params);
			$html->setvar('text', urlencode(strip_tags($row['text'])));
            $html->setvar('flash_postcard', User::flashPostcard(null, 'mail', urlencode(strip_tags($row['text']))));
			$html->parse('postcard', true);
			$html->parse('top_postcard', true);
			$html->parse('buttom_postcard', true);
			global $no_folders;
			$no_folders = true;
		} else {
			$html->setvar('text', $real_text);
			// var_dump($real_text); die();
			$html->parse('plain', true);
			$html->parse('top_plain', true);
			$html->parse('buttom_plain', true);
		}

		#DB::execute("UPDATE user SET new_mails=IF((SELECT new FROM mail_msg WHERE id=" . $row['id'] . ")='Y',new_mails-1,new_mails) WHERE user_id=" . to_sql($g_user['user_id'], "Number") . "");
		DB::execute("UPDATE mail_msg SET new='N' WHERE user_id=" . $g_user['user_id'] . " AND id=" . $row['id'] . "");
        if ($row['user_to_id'] == $g_user['user_id']) {
            if ($row['receiver_read'] != 'Y')
                DB::execute("UPDATE `mail_msg` SET `receiver_read` = 'Y' WHERE user_id = " . to_sql($g_user['user_id'], 'Number') . " AND id = " . to_sql($row['id'], 'Number'));
            if ($row['sent_id'] != 0)
                DB::execute("UPDATE `mail_msg` SET `receiver_read` = 'Y' WHERE id = " . to_sql($row['sent_id'], 'Number'));
        }
		// echo "=====================";

        $sql = "SELECT COUNT(id)
                  FROM `mail_msg`
                 WHERE `new` = 'Y'
                   AND `folder` = 1
                   AND `user_id` = " . to_sql($g_user['user_id'], 'Number');
        $g_info['new_mails'] = DB::result($sql);
        set_session('new_mails', $g_info['new_mails']);
        $sql = "UPDATE `user`
                   SET `new_mails` = " . to_sql($g_info['new_mails'], 'Number')
               . " WHERE `user_id` = " . to_sql($g_user['user_id'], 'Number');
        DB::execute($sql);

        $this->m_field['date_sent'][1] = Common::dateFormat($row['date_sent'], 'mailtext_date_sent',false);

		if(Common::isMobile()) {
			$this->m_field['subject'][1] = strcut($row['subject'], 25);
			$html->setvar('subject_full', mail_chain($row['subject']));
		}
		$this->m_field['text'][1] = nl2br(strip_tags($real_text, '<a><br>'));

        // INFO
        global $m_info;
        if($this->m_folder == 3) {
            $to_user = User::getInfoBasic($row['user_to_id']);

            $m_info['user_name'] = $to_user['name'];
            $m_info['user_age'] = $to_user['age'];
            $m_info['user_country_sub'] = $to_user['country'];
        } else {
            $from_user =  User::getInfoBasic($row['user_id']);
            $m_info['user_name'] = $row['user_from'];
            $m_info['user_age'] = $from_user['age'];
            $m_info['user_country_sub'] = $row['country'];
        }
        // INFO
	}
}

class CHtmlUsersListFav extends CUsers
{
	var $m_on_page = 20;
	function init()
	{
		parent::init();
		global $g;
		global $g_user;

		$this->m_sql_count = "SELECT COUNT(u.user_id) FROM user AS u " . $this->m_sql_from_add . "";

		$this->m_sql = "
			SELECT u.user_id, u.gender, u.orientation, u.rating, u.name, u.gold_days, u.type, u.partner_type, u.set_my_presence_couples, u.set_my_presence_males, u.set_my_presence_females, u.set_my_presence_transgender,  u.set_my_presence_nonbinary, u.set_my_presence_everyone,  (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age, u.last_visit,
			i.comment,
			u.is_photo,	u.city, u.state, u.country, u.relation,
			IF(u.city_id=" . $g_user['city_id'] . ", 1, 0) +
			IF(u.state_id=" . $g_user['state_id'] . ", 1, 0) +
			IF(u.country_id=" . $g_user['country_id'] . ", 1, 0) AS near

			FROM user AS u

			" . $this->m_sql_from_add . "
		";

		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['photo_id'] = array("photo", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['age'] = array("age", null);
		$this->m_field['comment'] = array("comment", null);
		$this->m_field['last_visit'] = array("last_visit", null);
		$this->m_field_default = $this->m_field;

		$this->m_params = set_param("display", "list");
		$this->m_params = del_param("cmd", $this->m_params);
		$this->m_params = del_param("id", $this->m_params);
		$this->m_params = del_param("display", $this->m_params);
		$this->m_params = del_param("offset", $this->m_params);
		$this->m_params_pages = set_param("display", "list", $this->m_params);
	}
}
class CHtmlUsersListInt extends CUsers
{
	var $m_on_page = 20;
	function init()
	{
		parent::init();
		global $g;
		global $g_user;

		$this->m_sql_count = "SELECT COUNT(u.user_id) FROM user AS u " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT u.*,
            (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))) AS age,
            u.state AS state_title, u.country AS country_title,	u.city AS city_title,
			IF(u.city_id=" . $g_user['city_id'] . ", 1, 0) +
			IF(u.state_id=" . $g_user['state_id'] . ", 1, 0) +
			IF(u.country_id=" . $g_user['country_id'] . ", 1, 0) AS near
            " . to_sql($this->fieldsFromAdd, 'Plain') . "
			FROM user AS u
			" . $this->m_sql_from_add . "
		";

		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['photo_id'] = array("photo", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['age'] = array("age", null);
		$this->m_field['last_visit'] = array("last_visit", null);
		$this->m_field['city_title'] = array("city", null);
		$this->m_field['state_title'] = array("state", null);
		$this->m_field['country_title'] = array("country", null);
		$this->m_field_default = $this->m_field;

		$this->m_params = set_param("display", "list");
		$this->m_params = del_param("cmd", $this->m_params);
		$this->m_params = del_param("id", $this->m_params);
		$this->m_params = del_param("display", $this->m_params);
		$this->m_params = del_param("offset", $this->m_params);
		$this->m_params_pages = set_param("display", "list", $this->m_params);
	}
}

?>