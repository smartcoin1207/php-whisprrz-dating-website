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
payment_check('mail_read');

class CAddFolder extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";
	function action()
	{
		global $g_user;

		$cmd = get_param("cmd", "");
		if ($cmd == "add")
		{
			$name = pl_substr(get_param('folder_name', ''), 0, 15);
			if ($name != "" and $name != "Inbox" and $name != "Sent Mail" and $name != "Trash")
			{
				$count = DB::result("SELECT COUNT(id) FROM mail_folder WHERE user_id=" . $g_user['user_id'] . "");

				if ($count <= 4)
				{
					DB::execute("INSERT INTO mail_folder (name, user_id) VALUES(" . to_sql($name) . ", " . $g_user['user_id'] . ")");
					redirect("mail_folder.php");
				}
				else
				{
					$this->message = "Maximum 5 folders.<br>";
				}
			}
			else
			{
				$this->message = "Incorrect Folder Name.<br>";
			}
		}
	}
	function parseBlock(&$html)
	{
		$html->setvar("message", $this->message);

		$ids = get_param_array("id");
		$id = isset($ids[0]) ? $ids[0] : 0;

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

		$to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mail.php";
		$html->setvar("page_from", get_param("page_from", $to));
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }
		parent::parseBlock($html);
	}
}

$page = new CAddFolder("", $g['tmpl']['dir_tmpl_main'] . "mail_folder_add.html");
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
