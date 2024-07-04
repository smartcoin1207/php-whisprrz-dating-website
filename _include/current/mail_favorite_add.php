<?php
/* (C) Websplosion LTD., 2001-2014

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

		$cmd = get_param("cmd", "");
		if ($cmd == "add")
		{
			$name = get_param("name", "");
			if ($name != "")
			{
				if(strtolower($name) == strtolower($g_user['name']))
				{
					$this->message = l('cannot_self');
					return;
				}
				$id = DB::result("SELECT user_id FROM user WHERE name=" . to_sql($name, "Text") . "");
				$add = DB::result("SELECT id FROM users_favorite WHERE	user_from=" . $g_user['user_id'] . " AND user_to=" . to_sql($id, "Number") . "");
				if ($id != 0)
				{
					if ($add == 0)
					{
						$comment = to_sql(get_param("comment", ""), "Text");
						DB::execute("
							INSERT INTO users_favorite (user_from, user_to, comment)
							VALUES(" . $g_user['user_id'] . "," . to_sql($id, "Number") . ", " . $comment . ")
						");
						#redirect(get_param("page_from", ""));

                        CStatsTools::count('added_to_favourites');
					}
					else
					{
						$comment = to_sql(get_param("comment", ""), "Text");
						DB::execute("
							UPDATE users_favorite
							SET comment=" . $comment . "
							WHERE user_from=" . $g_user['user_id'] . " AND user_to=" . to_sql($id, "Number") . "
						");
						#redirect(get_param("page_from", ""));
					}

				}
				else
				{
					$this->message = l('incorrect_username');
				}
			} else {
                           $this->message = l('name_is_empty');
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