<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

if (get_param("offset") <= 0) $_GET['offset'] = 1;

class CFrameList extends CUsers
{
	var $m_on_page = 5;
	var $m_on_line = 5;

	function init()
	{
		$this->m_on_page = get_param("step", 5);
		$this->m_on_line = get_param("step", 5);
		parent::init();
	}
}

$page = new CFrameList("list", $g['tmpl']['dir_tmpl_main'] . "frame_users_new.html");
$page->m_sql_where = "u.is_photo='Y' AND u.user_id!=" . $g_user['user_id'] . " AND u.hide_time=0 " . $g['sql']['your_orientation'] . " ";
$page->m_sql_order = "u.is_photo, u.user_id DESC";

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>