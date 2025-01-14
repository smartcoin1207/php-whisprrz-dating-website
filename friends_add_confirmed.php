<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/friends.php");

class CHtmlAdd extends CHtmlBlock
{
	var $m_on_page = 20;
	var $message = "";

	function parseBlock(&$html)
	{
		global $g_user;

		$user_id = get_param("user_id");

		DB::query("SELECT * FROM user WHERE user_id=" . to_sql($user_id, "Number") . " ");

		if (($row = DB::fetch_row()) && $user_id != $g_user["user_id"])
		{
			$html->setvar("name", $row['name']);
			$html->setvar("friend_user_id", $row['user_id']);
		}
		else
		{
			Common::toHomePage();
		}

        $html->setvar('name_profile', lSetVars('name_profile', array('name' => $row['name'])));
        $html->setvar('you_are_name_friend', lSetVars('you_are_name_friend', array('name' => $row['name'])));
        $html->setvar('display', User::displayProfile());

		parent::parseBlock($html);
	}
}

$page = new CHtmlAdd("", $g['tmpl']['dir_tmpl_main'] . "friends_add_confirmed.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);
$friends_menu = new CFriendsMenu("friends_menu", $g['tmpl']['dir_tmpl_main'] . "_friends_menu.html");
$page->add($friends_menu);


include("./_include/core/main_close.php");

?>
