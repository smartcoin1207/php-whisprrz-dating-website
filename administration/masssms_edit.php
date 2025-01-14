<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23

include("../_include/core/administration_start.php");

class CAdminSMS extends CHtmlBlock
{
	var $message_massmail = "";
	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "delete_id") {
			$id = get_param("id", "");
			$id = DB::result("SELECT id FROM sms WHERE id=" . to_sql($id, "Text") . "");

			if ($id != "0") {
				DB::execute("DELETE FROM sms WHERE id=" . to_sql($id, "Text") . "");
				$this->message_massmail = "Phone number deleted from database.";
			} else {
				$this->message_massmail = "This Phone number absent in database.";
			}
		}

		if ($cmd == "syncphone") {
			DB::query("SELECT user_id FROM user WHERE nsc_phone!='' AND set_sms_alert='on' AND carrier_provider!='' AND is_verified_c_provider='1'", 2);
			while ($row = DB::fetch_row(2)) {
				if (DB::count('sms', "user_id=" . to_sql($row['user_id'], 'Number') . "") == 0) {
					DB::insert("sms", array("user_id" => $row['user_id']));
				}
			}
			redirect('masssms_edit.php?action=saved');
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $p;
		$html->setvar("message_massmail", $this->message_massmail);

		parent::parseBlock($html);
	}
}

class CSMSs extends CHtmlList
{
	function init()
	{
		$this->m_sql_count = "SELECT COUNT(id) FROM sms";
		$this->m_sql = "SELECT id, user_id FROM sms";

		$where = "";

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = " id";
		$this->m_sql_from_add = "";

		$this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_field['id'] = array("id", null);
		$this->m_field['user_id'] = array("user_id", null);
	}

	function onItem(&$html, $row, $i, $last)
	{
		DB::query("SELECT `nsc_phone`, `name` FROM user WHERE user_id=" . to_sql($row['user_id'], "Number") . "", 2);
		if ($row2 = DB::fetch_row(2)) {
			$html->setvar("user_id", $row['user_id']);
			$html->setvar("nsc_phone", $row2['nsc_phone']);
			$html->setvar("user_name", $row2['name']);
			$html->parse("user", false);
			$html->setblockvar("nouser", "");
		} else {
			$html->parse("nouser", false);
			$html->setblockvar("user", "");
		}

		if (IS_DEMO) {
			$this->m_field['user_id'][1] = 'disabled@ondemoadmin.cp';
		}

		if ($i % 2 == 0) {
			$html->setvar("class", 'color');
			$html->setvar("decl", '_l');
			$html->setvar("decr", '_r');
		} else {
			$html->setvar("class", '');
			$html->setvar("decl", '');
			$html->setvar("decr", '');
		}

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CAdminSMS("", $g['tmpl']['dir_tmpl_administration'] . "masssms_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$smss = new CSMSs("smss", null);
$page->add($smss);

include("../_include/core/administration_close.php");
