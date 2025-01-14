<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

payment_check('mail_read');

class CEditFolder extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";
	function action()
	{
		global $g_user;

		$cmd = get_param("cmd", "");
		if ($cmd == "edit")
		{
			$name = pl_substr(get_param("folder_name", ""), 0, 15);
			if ($name != "" and $name != "Inbox" and $name != "Sent Mail" and $name != "Trash")
			{
				$id = DB::result("SELECT id FROM mail_folder WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql(get_param("id", 0), "Number") . "");
				DB::execute("UPDATE mail_folder SET name=" . to_sql($name) . " WHERE user_id=" . $g_user['user_id'] . " AND id=" . $id . "");

				redirect("mail_folder.php");
			}
			else
			{
				$this->message = "Incorrect Folder Name.<br>";
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g_user;

		$html->setvar("message", $this->message);

		$ids = get_param_array("folder_id");
		$id = isset($ids[0]) ? $ids[0] : 0;

		DB::query("SELECT id, name FROM mail_folder WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($id, "Number") . "");

		if ($row = DB::fetch_row())
		{
			$html->setvar("name", $row['name']);
			$html->setvar("id", $row['id']);
		}
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }
		parent::parseBlock($html);
	}
}

$page = new CEditFolder("", $g['tmpl']['dir_tmpl_main'] . "mail_folder_edit.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$folders = new CFolders("folders", $g['tmpl']['dir_tmpl_main'] . "_folders.html");
$page->add($folders);

$mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
$mailMenu->setActive('messages');
$page->add($mailMenu);

include("./_include/core/main_close.php");

?>
