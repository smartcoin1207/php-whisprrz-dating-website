<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CUsersReports extends AdminReportsContent
{
	function init()
	{
		global $g;

        $this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(UR.id) FROM users_reports AS UR " . $this->m_sql_from_add;
		$this->m_sql = "SELECT UR.*, UF.name AS name_from, UT.name AS name_to, UT.ban_global AS ban_to, UT.gender AS gender_to
                          FROM users_reports AS UR
                          JOIN user AS UF ON UF.user_id = UR.user_from
                          JOIN user AS UT ON UT.user_id = UR.user_to" . $this->m_sql_from_add;

        $this->m_field['id'] = array("id", null);
        $this->m_field['date'] = array("date", null);
        $this->m_field['user_from'] = array("user_from", null);
        $this->m_field['user_to'] = array("user_to", null);
        $this->m_field['msg'] = array("msg", null);
        $this->m_field['ban_to'] = array("ban_to", null);
        $this->m_field['name_from'] = array("name_from", null);
        $this->m_field['name_to'] = array("name_to", null);
        $this->m_field['photo_id'] = array("photo_id", null);
        $this->m_field['video'] = array("video", null);

        $this->m_sql_where = "UR.photo_id != 0 AND UR.wall_id = 0 AND UR.group_id = 0";
		$this->m_sql_order = "id";
		#$this->m_debug = "Y";
	}
}

$page = new CUsersReports('main', $g['tmpl']['dir_tmpl_administration'] . 'users_reports_content.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuBlock());

include('../_include/core/administration_close.php');