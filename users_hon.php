<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("./_include/core/main_start.php");
class CHon extends CHtmlUsersPhoto
{
 	var $imessage = "";
	var $m_on_page = 1;
	var $int_offset;

	function Updaterating($user_name, $add_rating)
	{
		// CHECK USER EXIST

		DB::query("SELECT user_id, rating FROM user WHERE name=".to_sql($user_name)."");
		$r = DB::fetch_row();
		$uid = $r['user_id'];
		$cur_rating = $r['rating'];
		if ($uid==0) return;
		$cur_rating=intval($cur_rating);

		if($add_rating<1)
		{
			$add_rating=1;
		}

		if($add_rating>7)
		{
			$add_rating=7;
		}

		$cur_rating+=$add_rating;
		DB::execute("UPDATE user SET rating=".$cur_rating.", last_visit=last_visit WHERE `user_id` ='". $uid. "';");
	    CStatsTools::count('hot_or_not_votes');

      }

	function action()
	{


		$uname=get_param("uname", -1);
		$urating=get_param("rset", -1);

		if($uname != -1 && $urating != -1)
		{
                        if (!guid()) Common::toLoginPage();
                        $this->Updaterating($uname, $urating);
		}

		$tmp_offset=get_param("offset", "");

		if($tmp_offset=="")
		{
			$this->int_offset=2;
		}
		else
		{
			$tmp_offset=intval($tmp_offset);
			$this->int_offset=$tmp_offset+1;
		}

		parent::action();
	}


	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

		$html->setvar("new_offset", "".$this->int_offset);

		$html->setvar("hot_button_1_text", isset($l['users_hon.php']['button_1']) ? $l['users_hon.php']['button_1'] : "1");
		$html->setvar("hot_button_2_text", isset($l['users_hon.php']['button_2']) ? $l['users_hon.php']['button_2'] : "2");
		$html->setvar("hot_button_3_text", isset($l['users_hon.php']['button_3']) ? $l['users_hon.php']['button_3'] : "3");
		$html->setvar("hot_button_4_text", isset($l['users_hon.php']['button_4']) ? $l['users_hon.php']['button_4'] : "4");
		$html->setvar("hot_button_5_text", isset($l['users_hon.php']['button_5']) ? $l['users_hon.php']['button_5'] : "5");
		$html->setvar("hot_button_6_text", isset($l['users_hon.php']['button_6']) ? $l['users_hon.php']['button_6'] : "6");
		$html->setvar("hot_button_7_text", isset($l['users_hon.php']['button_7']) ? $l['users_hon.php']['button_7'] : "7");

		$html->setvar("interest_message", $this->imessage);

		//die("".$this->int_offset);
		$html->setvar("user_id", get_param("user_id"));
		parent::parseBlock($html);
	}



}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "users_hon.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$page->m_sql_where = "u.is_photo='Y' AND u.user_id!=" . $g_user['user_id'] . " AND hide_time=0 " . $g['sql']['your_orientation'] . " ";
$page->m_sql_order = "RAND()";

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
