<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

if(!Common::isOptionActive('wink')) {
    redirect(Common::toHomePage());
}
payment_check('show_interest');

class CInterest extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";
	var $id;
	var $subject;
	var $text;
	function action()
	{
		global $g_user;
		$this->imessage = "";

		$cmd = get_param("cmd", "");
		if ($cmd == "interest")
		{
			$id = intval(get_param('id'));
			if ($id != 0)
			{
                Common::sendWink($id);
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g_user;

		$id = get_param("id", 0);
		DB::query("SELECT user_id, name FROM user WHERE user_id=" . to_sql($id, "Number") . " ");
		if ($row = DB::fetch_row())
		{
			$html->setvar("uname", $row['name']);
		}
		else
		{
			Common::toHomePage();
		}
        if(Common::isOptionActive('mail')) {
            $html->parse('mail_on');
        }
		$to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mail.php";
		$path_parts = pathinfo($to);
		$html->setvar("page_from", $path_parts['basename']);

		$params = del_param("cmd");
		$params = del_param("id", $params);
		$html->setvar("params", $params);

		parent::parseBlock($html);
	}
}

$page = new CInterest("", $g['tmpl']['dir_tmpl_main'] . "show_interest.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);

$mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
$mailMenu->setActive('messages');
$page->add($mailMenu);

include("./_include/core/main_close.php");

?>
