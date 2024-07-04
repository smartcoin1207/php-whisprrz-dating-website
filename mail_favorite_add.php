<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");


class CHtmlAdd extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";

	function action()
	{
		global $g_user;
        global $l;


       	$ids = get_param_array("id");
		$cmd = get_param("cmd", "");
		$cmd_ajax = get_param("cmd_ajax", "");

		if(!$cmd_ajax) {
			if((!$ids && !$cmd) || ! get_param('id')) {
	       		redirect(Common::getHomePage());
	       	}	
		}

		if ($cmd == "add")
		{
            $comment = to_sql(get_param("comment", ""), "Text");
            $fr_user_id = get_param('fr_user_id', "");

			$bookmark_exist = DB::result("SELECT id FROM friends WHERE user_id=" . $g_user['user_id'] . " AND fr_user_id=" . to_sql($fr_user_id, "Number") . "");

			if($bookmark_exist) {
				DB::execute("
					UPDATE friends
					SET comment=" . $comment . "
					WHERE user_id=" . $g_user['user_id'] . " AND fr_user_id=" . to_sql($fr_user_id, "Number") . "
				");
			} else {
				DB::execute("
					INSERT INTO friends SET user_id=" . $g_user['user_id'] . ", fr_user_id=" . to_sql($fr_user_id, "Number") . ", comment=" . $comment . ", bookmark='YES' "
				);
			}
		}
	}

	function parseBlock(&$html)
	{

		$cmd = get_param("cmd", "");
		$cmd_ajax = get_param("cmd_ajax", "");
        if($cmd_ajax)
		{
			if($this->message!="")
			{
				$html->setvar("error_message",$this->message);
				$html->setvar("prevent_cache",time().rand(0,1000));
				$html->parse("error_alert");
			}else{
				$to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mail_favorite.php";
				//$html->setvar("page_from", get_param("page_from", $to));
                                $html->setvar("page_from", 'mail_favorite.php');
				$html->parse("redirect");
			}
			parent::parseBlock($html);
			return;
		}
		$ids = get_param_array("id");
		$id = isset($ids[0]) ? $ids[0] : 0;

		DB::query("SELECT user_id, name FROM user WHERE user_id=" . to_sql($id, "Number") . " ");

		if ($row = DB::fetch_row())
		{
			$html->setvar("name", $row['name']);
			$html->setvar('fr_user_id', $id);
			$html->parse("add_id", true);
		}
		else
		{
			$html->parse("add_name", true);
		}
        if(Common::isOptionActive('mail')) {
            $html->parse('mail_on');
            $html->parse('mail_on2');
        }
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
            $html->parse('wink_on2', false);
        }
		$to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mail_favorite.php";
		$html->setvar("page_from", get_param("page_from", $to));
                $html->parse("mail_favorite_add_page");

		parent::parseBlock($html);
	}
}

$page = new CHtmlAdd("", $g['tmpl']['dir_tmpl_main'] . "mail_favorite_add.html");

$cmd_ajax = get_param("cmd_ajax", "");

if(!$cmd_ajax) {
    $header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
    $page->add($header);
    $footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
    $page->add($footer);

    $folders = new CFolders("folders", $g['tmpl']['dir_tmpl_main'] . "_folders.html");
    $page->add($folders);

    $mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
    $mailMenu->setActive('favorites');
    $page->add($mailMenu);



}
include("./_include/core/main_close.php");