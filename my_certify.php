<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");


class CMyCertify extends CHtmlBlock
{
	function action()
	{
		global $g_user;

		$cmd = get_param('cmd', '');
		$id = get_param('id', '');
		if ($cmd == "delete" && $id > 0) {

			$sql = "SELECT user_from FROM user_certify WHERE id = '". $id ."' limit 1";
			$user_from = DB::result($sql);

			
			$where = 'id = ' . to_sql($id);
			
			DB::delete('user_certify', $where);

			$where_wall_del = 'DELETE FROM wall WHERE comment_user_id = ' . to_sql($user_from) . ' AND section = "certify_text"';
			DB::execute($where_wall_del);

			set_session("deleted", "yes");
			redirect("my_certify.php");
		} else if ($cmd == "disapprove" && $id > 0) {
			// After share change status
			$where = 'id = ' . to_sql($id);
			DB::update('user_certify', array("is_approved" => "0"), $where);

			set_session("update", "yes");
			redirect("my_certify.php");
		} else if ($cmd == "approve" && $id > 0) {
			// After share change status
			$where = 'id = ' . to_sql($id);
			DB::update('user_certify', array("is_approved" => "1"), $where);

			set_session("update", "yes");
			redirect("my_certify.php");
		} else if ($cmd == "wallpost" && $id > 0) {
			// Add code for wall post

			$row = DB::one("user_certify", "id={$id}");

			$comment = trim(strip_tags($row['certify_text']));

			$comment = Common::newLinesLimit($comment, 2);
			$comment = OutsideImages::filter_to_db($comment);
			$comment = VideoHosts::textUrlToVideoCode($comment);
			
			$id = intval(Wall::addWall('certify_text', 0, $row['user_from'], $g_user['user_id'], $comment));
			
			wall::addComment($id);

			set_session("wallpost", "yes");
			redirect("my_certify.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		DB::query("SELECT UC.*, UF.name AS name_from
		FROM user_certify AS UC
		JOIN user AS UT ON UT.user_id = UC.user_to 
		JOIN user AS UF ON UF.user_id = UC.user_from 
		WHERE UC.user_to=" . to_sql($g_user['user_id'], "Number"));

		while ($row = DB::fetch_row()) {

			$html->setvar('id', $row['id']);
			$html->setvar('name_from', $row['name_from']);
			$html->setvar('certify_text', $row['certify_text']);
			if ($row['is_approved'] == '1') {
				$html->setvar('approve', 'disapprove');
				$html->setvar('approve_text', l('disapprove'));
			} else {
				$html->setvar('approve', 'approve');
				$html->setvar('approve_text', l('approve'));
			}
			$html->setvar('submit_date', date("d M, Y", strtotime($row['submit_date'])));
			$html->parse("my_certify");
		}

		$saved = get_session("deleted");
		$html->setvar("deleted", $saved);
		delses("deleted");

		$saved = get_session("update");
		$html->setvar("update", $saved);
		delses("update");

		$saved = get_session("wallpost");
		$html->setvar("wallpost", $saved);
		delses("wallpost");

		parent::parseBlock($html);
	}
}

$page = new CMyCertify("", $g['tmpl']['dir_tmpl_main'] . "my_certify.html");

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$page->add($footer);

include("./_include/core/main_close.php");
