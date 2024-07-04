<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "delete"){
			DB::execute("
				DELETE FROM contact WHERE
				id=" . to_sql(get_param("id", ""), "Number") . "
			");
			$url = "contact.php?action=delete";
			if (get_param('order') == 'name') {
				$url .= '&order=name';
			}
			redirect($url);
		}

		if ($cmd == "delete_all"){
			$id = get_param_int('id');
			$mail = DB::result("SELECT mail FROM `contact` WHERE `id` = " . to_sql($id));
			if ($mail) {
				DB::execute("DELETE FROM `contact` WHERE `mail` = " . to_sql($mail));
				$url = "contact.php?action=delete";
				if (get_param('order') == 'name') {
					$url .= '&order=name';
				}
				redirect($url);
			}
		}

		if ($cmd == "answer")
		{
			#echo get_param("mail") . " " . $g['main']['info_mail'] . " " . $g['main']['title'] . " " . get_param("answer", "");
			$id = get_param("id", "");
			$contact = DB::row("SELECT mail, name FROM contact WHERE id=".to_sql($id),0);
			send_mail(
				$contact['mail'],
				$g['main']['info_mail'],
				$g['main']['title'],
				get_param("answer", "")
			);
            if (Common::isEnabledAutoMail('contact')) {
                //Sent message copy to admin
                $vars = array(
                            'title' => mail_chain($g['main']['title'].' '.strip_tags($contact['name'])),
                            'name' => $contact['name'],
                            'from' => $contact['mail'],
                            'comment' => get_param("answer", ""),
                        );
                Common::sendAutomail(Common::getOption('administration', 'lang_value'), $g['main']['info_mail'], 'contact', $vars);
            }
			$url = "contact.php?done=" . $id;
			if (get_param('order') == 'name') {
				$url .= '&order=name';
			}
			redirect($url);
		}
	}
	function parseBlock(&$html)
	{


		global $g;

		$html->setvar("message", $this->message);
		$done = get_param("done", 0);

		$isTmplModern = Common::isAdminModer();
		if ($isTmplModern) {

			$orderBy = 'id';
			$orderByTitle = l('date');
			if (get_param('order') == 'name') {
				$orderBy = 'name';
				$orderByTitle = l('name');
				$html->setvar('params_order', '&order=name');
				$html->setvar('params_order_frm', '?order=name');
			}
			$html->setvar('sort_by', $orderByTitle);

			$fetchType = DB::getFetchType();
			DB::setFetchType(MYSQL_ASSOC);
			DB::query('SELECT * FROM contact ORDER BY ' . $orderBy);
			$contacts = array();
			while ($row = DB::fetch_row()){
				$key = $row['mail'];
				if (!isset($contacts[$key])) {
					$contacts[$key] = $row;
					$alias = str_replace(array('.', '@', '-'), '_', $row['mail']);
					$contacts[$key]['alias'] = $alias;
					$contacts[$key]['msg_id'] = $row['id'];
					$contacts[$key]['photo'] = User::getPhotoDefault($row['user_id'], 'r');
					$contacts[$key]['time_ago'] = '';
					if ($row['date']) {
						$contacts[$key]['time_ago'] = timeAgo($row['date'], 'now', 'string', 60, 'second');
					}
					$contacts[$key]['messages'] = array();
				}
				$contacts[$key]['messages'][] = array($row['comment'], $row['date'], $row['id'], $alias);
			}
			DB::setFetchType($fetchType);

			$countContacts = count($contacts);
			//var_dump_pre($contacts, true);
			$i = 0;
			$isShowMsg = true;
			foreach ($contacts as $key => $row) {
				$i++;
				$messages = $row['messages'];
				$data = $row;
				$lastMsg = array_pop($data['messages']);
				if ($lastMsg === null) {
					$lastMsg = '';
				} else {
					$lastMsg = $lastMsg[0];
				}
				unset($data['messages']);
				$data['messages_last'] = $lastMsg;

				$html->assign('contact', $data);
				$html->subcond($i == 1, 'question_current');
				$html->subcond($i != $countContacts, 'question_decor');
				$html->parse('question', true);

				/* Messages list */
				$isCollapse = true;
				$countMsg = count($messages);
				$j = 1;
				foreach ($messages as $k => $msg) {
					$html->setvar('msg_text', $msg[0]);
					$html->setvar('msg_date', Common::dateFormat($msg[1], 'l, d F Y H:i', true, false, false, false, true));
					$html->setvar('msg_id', $msg[2]);
					$html->setvar('msg_photo', $row['photo']);
					$html->setvar('msg_name', $row['name']);
					$html->subcond($isCollapse, 'msg_show');
					$html->subcond($j != $countMsg, 'msg_hr');
					$isCollapse = false;
					$j++;
					$html->parse('msg', true);
				}

				$html->subcond($isShowMsg, 'messages_show');
				$isShowMsg = false;

				$html->parse('messages', true);
				$html->clean('msg');
				/* Messages list */
			}

			if ($countContacts) {
				$html->parse('contact_list', false);
			} else {
				$html->parse('no_contact_list', false);
			}

			if ($done){
				$html->parse('answer_send', false);
			}
		} else {

			DB::query("SELECT * FROM contact ORDER BY id");
			while ($row = DB::fetch_row()){
				foreach ($row as $k => $v){
					$v = nl2br($v);
					$html->setvar($k, strip_tags($v, '<br>'));
				}

				if($done == $row['id'])	{
					$html->setvar("result",l('done'));
				} else {
					$html->setvar("result","");
				}
				$html->parse("question", true);
			}
		}

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "contact.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");