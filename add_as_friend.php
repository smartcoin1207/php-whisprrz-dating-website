<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

class CProfile extends CHtmlBlock
{

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
		parent::parseBlock($html);
	}
}

class CFriendRequest extends CHtmlBlock
{
	var $user_id = 0;

	function action()
	{
	}

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;
		global $gc;

		if(!$this->user_id || $this->user_id == $g_user['user_id'])
			redirect('home.php');

		DB::query('SELECT * FROM friends_requests WHERE user_id='.to_sql($g_user['user_id'], 'Number')." AND friend_id=".to_sql($this->user_id, 'Number'));
		if(!DB::fetch_row())
			DB::execute('INSERT INTO friends_requests SET user_id='.to_sql($g_user['user_id'], 'Number').", friend_id=".to_sql($this->user_id, 'Number'));

		$html->setvar('user_id', $this->user_id);

		if($this->user_id == $g_user['user_id'])
		{
			$html->parse('my_friends', true);
		}
		else
		{
			DB::query("SELECT * FROM friends WHERE
				(user_id='".to_sql($this->user_id, 'Number')."' AND fr_user_id=".to_sql($g_user['user_id'], 'Number').") OR
				(fr_user_id='".to_sql($this->user_id, 'Number')."' AND user_id=".to_sql($g_user['user_id'], 'Number').")" , 1);
			if(DB::num_rows() == 0)
			{
				$html->parse('add_as_friend', false);
			}

			$html->setvar('name', DB::result("SELECT name FROM user WHERE user_id=".to_sql($this->user_id, 'Number')));
			$html->parse('users_friends', true);
		}

		DB::query("SELECT * FROM friends WHERE user_id='".to_sql($this->user_id, 'Number')."' and data<>'0000-00-00' ORDER BY data DESC LIMIT 4");

		for($friend_n = 0; $friend_n != 4; ++$friend_n)
		{
			if($friend_row = DB::fetch_row())
			{
				$result_photo_user = DB::query("SELECT * FROM user WHERE user_id='".to_sql($friend_row['fr_user_id'],"Number")."' LIMIT 1",1);
				$row = DB::fetch_row(1);

				if (!isset($row['photo_id'])) $row['photo_id'] = DB::result("SELECT photo_id FROM photo WHERE user_id=" . $row['user_id'] . " " . $g['sql']['photo_vis'] . "  LIMIT 1", 0, 2);
				if ($row['photo_id'] != "" and custom_file_exists($g['path']['dir_files'] . "photo/" . $row['user_id'] . "_" . $row['photo_id'] . "_r.jpg")) $row['photo'] = "photo/" . $row['user_id'] . "_" . $row['photo_id'] . "_" . $g['options']['main_users_photo_size'] . ".jpg";
				else $row['photo'] = "nophoto_" . $row['gender'] . "_" . $g['options']['main_users_photo_size'] . ".jpg";

				$row['last_visit'] = time_mysql_dt2u($row['last_visit']);
				if (((time() - $row['last_visit']) / 60) < $g['options']['online_time'])
				$html->parse('online', false);

				foreach ($row as $k => $v) $html->setvar($k, $v);

				$html->parse('friend_photo', true);
			}
			else
			{
				$html->parse('friend_blank', true);
			}
		}

		parent::parseBlock($html);
	}
}

g_user_full();

$user_id = get_param('user_id', $g_user['user_id']);

$page = new CFriendRequest("friends_list", $g['tmpl']['dir_tmpl_main'] . "add_as_friend.html");
$page->user_id = $user_id;
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
