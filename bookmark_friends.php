<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/friends.php");

class CPhoto extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;
		CBanner::getBlock($html, 'right_column');
		$user_id = get_param("id", "");
		$confirm = get_param("confirm", "");
		$action = get_param("action", "");
		if (empty($confirm)) $confirm="";
		if (empty($user_id)) $user_id="";

		if ($action=='add')
		{
			if ($confirm=="")
			{
				$user_photo = User::getPhotoDefault($user_id,"r");

				$result_user=DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".to_sql($user_id,"Number")."' LIMIT 0, 1");
				$row_user=DB::fetch_row();

				$html->setvar("age", $row_user['age']);
				$html->setvar("city", $row_user['city'] != "" ? $row_user['city'] : l('blank'));
				$html->setvar("state", $row_user['state'] != "" ? $row_user['state'] : l('blank'));
				$html->setvar("country", $row_user['country'] != "" ? $row_user['country'] : l('blank'));
				$html->setvar("user_name", $row_user['name']);
				$html->setvar("user_photo", $user_photo);
				$html->setvar("user_id", $user_id);
				$html->parse("add_bookmark", true);
			}
			else
			{
				if (!empty($_POST['visible']) and $_POST['visible']=='on') $visible="YES";
				else $visible="NO";
				$result_user=DB::query("SELECT id FROM friends WHERE user_id='".$g_user['user_id']."' and fr_user_id='".to_sql($user_id,"Number")."' LIMIT 0, 1");
				$num=DB::num_rows();
				if ($num==0)
				{
					$result_user=DB::execute("INSERT INTO friends (user_id, fr_user_id, bookmark, visible_bookmark) VALUES ('".$g_user['user_id']."', '".to_sql($user_id,"Number")."', 'YES', '".$visible."')");
				}
				else
				{
					$row=DB::fetch_row();
					$result_user=DB::execute("UPDATE friends SET bookmark='YES', visible_bookmark='".$visible."' WHERE id='".$row['id']."'");
				}
				redirect("bookmark_friends.php?action=show");
			}
		}
		else if ($action=='show')
		{
			$result_user=DB::query("SELECT * FROM friends WHERE user_id='".$g_user['user_id']."'");

			$num=DB::num_rows();
			if ($num>0)
			{
                while ($row = DB::fetch_row()){
                    $rows[] = $row;
                }

                foreach ($rows as $key11 => $row) 
				{
					$user_photo = User::getPhotoDefault($row['fr_user_id'],"r");

					$result_user=DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".to_sql($row['fr_user_id'],"Number")."' LIMIT 0, 1",2);
					$row_user=DB::fetch_row(2);

					$bmv = 'Bookmark visible';
					$bmh = 'Bookmark hidden';
					if (isset($l['bookmark_friends.php']['bookmark_visible'])) {
						$bmv = $l['bookmark_friends.php']['bookmark_visible'];
					}
					if (isset($l['bookmark_friends.php']['bookmark_hidden'])) {
						$bmh = $l['bookmark_friends.php']['bookmark_hidden'];
					}

					if ($row['visible_bookmark']=='YES') $visibility=$bmv;
					else $visibility=$bmh;

					$html->setvar("age", $row_user['age']);
					$html->setvar("city", $row_user['city'] != "" ? $row_user['city'] : l('blank'));
					$html->setvar("state", $row_user['state'] != "" ? $row_user['state'] : l('blank'));
					$html->setvar("country", $row_user['country'] != "" ? $row_user['country'] : l('blank'));
					$html->setvar("user_name", $row_user['name']);
					$html->setvar("user_photo", $user_photo);
					$html->setvar("user_id", $row_user['user_id']);
					$html->setvar("visibility", $visibility);
                    CFlipCard::parseFlipCard($html, $row_user);
					$html->parse("item_my_show_bookmark", true);
				}
				$html->parse("show_my_bookmark", true);
			} else {
				$html->parse("error_my_show_bookmark", true);
			}

			$result_user=DB::query("SELECT * FROM friends WHERE fr_user_id='".$g_user['user_id']."' and visible_bookmark='YES'");
			$num=DB::num_rows();
			if ($num>0)
			{
                $rows = array();
                while ($row = DB::fetch_row()){
                    $rows[] = $row;
                }

                foreach ($rows as $key11 => $row) {
					$user_photo = User::getPhotoDefault($row['user_id'],"r");

					$result_user=DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".to_sql($row['user_id'],"Number")."' LIMIT 0, 1",2);
					$row_user=DB::fetch_row(2);

					if ($row['visible_bookmark']=='YES') $visibility="Bookmark visible from ".$row_user['name']."";
					else $visibility="Bookmark hidden from ".$row_user['name']."";

					$html->setvar("age", $row_user['age']);
					$html->setvar("city", $row_user['city'] != "" ? $row_user['city'] : l('blank'));
					$html->setvar("state", $row_user['state'] != "" ? $row_user['state'] : l('blank'));
					$html->setvar("country", $row_user['country'] != "" ? $row_user['country'] : l('blank'));
					$html->setvar("user_name", $row_user['name']);
					$html->setvar("user_photo", $user_photo);
					$html->setvar("user_id", $row_user['user_id']);
					$html->setvar("visibility", $visibility);
					CFlipCard::parseFlipCard($html, $row_user);

					$html->parse("item_your_show_bookmark", true);

				}
				$html->parse("show_your_bookmark", true);
			}
			else
			{
				$html->parse("error_your_show_bookmark", true);
			}
		}
		else if ($action=='del')
		{
			if ($confirm=="")
			{
				$user_photo = User::getPhotoDefault($user_id,"r");

				$result_user=DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".to_sql($user_id,"Number")."' LIMIT 0, 1",2);
				$row_user=DB::fetch_row(2);

				$html->setvar("age", $row_user['age']);
				$html->setvar("city", $row_user['city'] != "" ? $row_user['city'] : l('blank'));
				$html->setvar("state", $row_user['state'] != "" ? $row_user['state'] : l('blank'));
				$html->setvar("country", $row_user['country'] != "" ? $row_user['country'] : l('blank'));
				$html->setvar("user_name", $row_user['name']);
				$html->setvar("user_photo", $user_photo);
				$html->setvar("user_id", $user_id);
                CFlipCard::parseFlipCard($html, $row_user);

				$html->parse("del_bookmark", true);
			}
			else
			{
				$result_user=DB::execute("DELETE FROM friends WHERE user_id='".$g_user['user_id']."' and fr_user_id='".to_sql($user_id,"Number")."'");
				redirect("bookmark_friends.php?action=show");
			}
		}
		else if ($action=='hide')
		{
			$result_user=DB::execute("UPDATE friends SET visible_bookmark='NO' WHERE user_id='".to_sql($user_id,"Number")."' and fr_user_id='".$g_user['user_id']."'");
			redirect("bookmark_friends.php?action=show");
		}
		parent::parseBlock($html);
	}
}

$page = new CPhoto("", $g['tmpl']['dir_tmpl_main'] . "bookmark_friends.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$friends_menu = new CFriendsMenu("friends_menu", $g['tmpl']['dir_tmpl_main'] . "_friends_menu.html");
$friends_menu->active_button = "bookmarks";
$page->add($friends_menu);

include("./_include/core/main_close.php");

?>

