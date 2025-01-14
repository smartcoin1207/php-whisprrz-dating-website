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

class CFolder extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";
	function action()
	{
		global $g_user;

		$cmd = get_param("cmd", "");
		if ($cmd == "delete")
		{
			$id = get_param_array("folder_id");
			foreach ($id as $k => $v)
			{
				DB::execute("
					DELETE FROM mail_folder
					WHERE user_id=" . $g_user['user_id'] . " AND id=" . to_sql($v, "Number") . "
				");

				DB::execute("
					UPDATE mail_msg
					SET folder=2
					WHERE user_id=" . $g_user['user_id'] . " AND folder=" . to_sql($v, "Number") . "
				");
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g_user;

		$html->setvar("message", $this->message);

		$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE folder=1 AND user_id=" . $g_user['user_id'] . "");
		$html->setvar("count_msg_inbox", $count);
		$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE folder=2 AND user_id=" . $g_user['user_id'] . "");
		$html->setvar("count_msg_trash", $count);
		$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE folder=3 AND user_id=" . $g_user['user_id'] . "");
		$html->setvar("count_msg_sent_mail", $count);

		$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder=1 AND user_id=" . $g_user['user_id'] . "");
		$html->setvar("count_msg_new_inbox", $count);
		$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder=2 AND user_id=" . $g_user['user_id'] . "");
		$html->setvar("count_msg_new_trash", $count);
		$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder=3 AND user_id=" . $g_user['user_id'] . "");
		$html->setvar("count_msg_new_sent_mail", $count);

		$i = 0;
		DB::query("SELECT id, name FROM mail_folder WHERE user_id=" . $g_user['user_id'] . " ORDER BY id", 2);
		while ($row = DB::fetch_row(2))
		{
			$html->setvar("id", $row['id']);
			$html->setvar("name", $row['name']);
			$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE folder=" . $row['id'] . " AND user_id=" . $g_user['user_id'] . "");
			$html->setvar("count_msg", $count);
			$count = DB::result("SELECT COUNT(id) FROM mail_msg WHERE new='Y' AND folder=" . $row['id'] . " AND user_id=" . $g_user['user_id'] . "");
			$html->setvar("count_msg_new", $count);
			$html->parse("folders_item", true);
			$i++;
		}

		for ($j = $i; $j < 5; $j++)
		{
			$html->parse("folders_add", true);
		}
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }
		parent::parseBlock($html);
	}
}

$page = new CFolder("", $g['tmpl']['dir_tmpl_main'] . "mail_folder.html");
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
