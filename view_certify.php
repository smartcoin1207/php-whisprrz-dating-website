<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");


class CViewCertify extends CHtmlBlock
{
	function action()
	{
		
	}
	function parseBlock(&$html)
	{
		global $g_user;
		$uid = get_param('uid', '');

		DB::query("SELECT UC.*, UF.name AS name_from
		FROM user_certify AS UC
		JOIN user AS UT ON UT.user_id = UC.user_to 
		JOIN user AS UF ON UF.user_id = UC.user_from 
		WHERE UC.is_approved='1' AND UC.user_to=" . to_sql($uid, "Number"));

		while ($row = DB::fetch_row()) {

			$html->setvar('id', $row['id']);
			$html->setvar('name_from', $row['name_from']);
			$html->setvar('certify_text', $row['certify_text']);
			$html->setvar('submit_date', date("d M, Y", strtotime($row['submit_date'])));
			$html->parse("view_certify");
		}

		$username = User::getInfoBasic($uid, "name");
		$html->setvar("username", $username);
		$html->setvar("user_id", $uid);

		parent::parseBlock($html);
	}
}

$page = new CViewCertify("", $g['tmpl']['dir_tmpl_main'] . "view_certify.html");

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$page->add($footer);

include("./_include/core/main_close.php");
