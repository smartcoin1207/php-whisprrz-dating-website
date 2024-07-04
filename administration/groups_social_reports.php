<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CUsersReports extends CHtmlList
{
	function action()
	{
		global $p;

		$cmd = get_param('cmd');
        if ($cmd) {
            $del = get_param('delete');
            $reportsId = get_param('report_id');
            $pid = get_param('photo_id');
            $uid = get_param('user_id');
            $groupId = get_param('group_id');

            if ($cmd == 'delete_report' && $reportsId){
                $listUsersReport = explode(',', $reportsId);
                foreach ($listUsersReport as $rid) {
                    $where = 'id = ' . to_sql($rid);
                    $report = DB::select('users_reports', $where);
                    $usersToReport = array();
                    if ($report && isset($report[0])) {
                        $report = $report[0];
                        $usersToReport = User::getInfoBasic($report['user_to'], 'users_reports');
                        DB::delete('users_reports', $where);
                    }
                    if ($usersToReport) {
                        $usersToReport = explode(',', $usersToReport);
                        unset_from_array($report['user_from'], $usersToReport);
                        User::update(array('users_reports' => implode(',', $usersToReport)), $report['user_to']);
                    }
                }
                redirect("{$p}?action=delete");
            }elseif ($cmd == 'ban' && $groupId) {
                Groups::ban($groupId);
                redirect();
            }
        }
	}
	function init()
	{
		global $g;

        $this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(UR.id) FROM users_reports AS UR " . $this->m_sql_from_add;
		$this->m_sql = "SELECT UR.*, UF.name AS name_from, UT.name AS name_to, G.title AS group_title, G.ban_global AS group_ban_to
                          FROM users_reports AS UR
                          LEFT JOIN user AS UF ON UF.user_id = UR.user_from
                          LEFT JOIN user AS UT ON UT.user_id = UR.user_to
                          LEFT JOIN groups_social AS G ON G.group_id = UR.group_id" . $this->m_sql_from_add;

        $this->m_field['id'] = array("id", null);
        $this->m_field['date'] = array("date", null);
        $this->m_field['user_from'] = array("user_from", null);
        $this->m_field['user_to'] = array("user_to", null);
        $this->m_field['msg'] = array("msg", null);
        $this->m_field['group_ban_to'] = array("group_ban_to", null);
        $this->m_field['name_from'] = array("name_from", null);
        $this->m_field['name_to'] = array("name_to", null);
        $this->m_field['group_title'] = array("group_title", null);

        $this->m_sql_where = "UR.photo_id = 0 AND UR.group_id != 0 AND UR.wall_id = 0 ";
		$this->m_sql_order = "id";
		#$this->m_debug = "Y";
	}

    function onPostParse(&$html)
	{
        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        if($row['group_ban_to']){
			$this->m_field['group_ban_to'][1] = l('unban');
		} else {
			$this->m_field['group_ban_to'][1] = l('ban');
		}
        $this->m_field['msg'][1] = nl2br($row['msg']);

        $userLink = User::url($row['user_to']);
        $html->setvar('user_owner_url', $userLink);

        $userLink = User::url($row['user_from']);
        $html->setvar('user_from_url', $userLink);

        $groupLink = Groups::url($row['group_id']);
        $html->setvar('group_url', $groupLink);

        $html->setvar('group_id', $row['group_id']);

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CUsersReports('main', $g['tmpl']['dir_tmpl_administration'] . 'groups_social_reports.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuGroupsSocial());

include('../_include/core/administration_close.php');