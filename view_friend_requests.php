<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

class CFriendRequest extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;

		DB::query("SELECT * FROM friends_requests WHERE friend_id = " . to_sql($g_user['user_id'], 'Number') . " LIMIT 1");
		if(!DB::fetch_row())
		{
			redirect('home.php');
		}

		$cmd = get_param("cmd", "");

		if($cmd == 'reject')
		{
			DB::execute("DELETE FROM friends_requests WHERE friend_id = " . to_sql($g_user['user_id'], 'Number'). " AND user_id = " . to_sql(get_param("user_id", ""), 'Number') ." LIMIT 1");
			redirect('view_friend_requests.php');
		}
		if($cmd == 'accept')
		{
			DB::execute("DELETE FROM friends_requests WHERE friend_id = " . to_sql($g_user['user_id'], 'Number'). " AND user_id = " . to_sql(get_param("user_id", ""), 'Number') ." LIMIT 1");

			friend_add($g_user['user_id'], get_param("user_id", ""));

			redirect('view_friend_requests.php');
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;
		global $gc;

		DB::query("SELECT * FROM friends_requests WHERE friend_id = " . to_sql($g_user['user_id'], 'Number') . " LIMIT 1");
		if($request = DB::fetch_row())
		{
			DB::query("SELECT * FROM user WHERE user_id = " . to_sql($request['user_id'], 'Number') . " LIMIT 1");
			$friend = DB::fetch_row();

			if (!isset($friend['photo_id'])) $friend['photo_id'] = DB::result("SELECT photo_id FROM photo WHERE user_id=" . $friend['user_id'] . " " . $g['sql']['photo_vis'] . "  LIMIT 1", 0, 2);
			if ($friend['photo_id'] != "" and custom_file_exists($g['path']['dir_files'] . "photo/" . $friend['user_id'] . "_" . $friend['photo_id'] . "_r.jpg")) $friend['photo'] = "photo/" . $friend['user_id'] . "_" . $friend['photo_id'] . "_" . $g['options']['main_users_photo_size'] . ".jpg";
			else $friend['photo'] = "nophoto_" . $friend['gender'] . "_" . $g['options']['main_users_photo_size'] . ".jpg";

			$friend['last_visit'] = time_mysql_dt2u($friend['last_visit']);
			if (((time() - $friend['last_visit']) / 60) < $g['options']['online_time'])
			$html->parse('online', false);

			$html->setvar('friend_user_id', $friend['user_id']);
			$html->setvar('friend_name', $friend['name']);

			$html->setvar('photo', $friend['photo']);
			$html->setvar('last_visit', $friend['last_visit']);
		}
		else
		{
			redirect('home.php');
		}

		parent::parseBlock($html);
	}
}

g_user_full();

$page = new CFriendRequest("friends_list", $g['tmpl']['dir_tmpl_main'] . "view_friend_requests.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
