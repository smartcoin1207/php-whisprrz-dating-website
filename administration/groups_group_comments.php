<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/places/tools.php");

class CPlaces extends CHtmlList
{
	function action()
	{
	}
	function init()
	{
		global $g;

		$this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(m.group_id) FROM wall_comments AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM wall_comments AS m
			" . $this->m_sql_from_add . "
		";

        $this->m_field['id'] = array("comment_id", null);
		$this->m_field['group_id'] = array("group_id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['comment'] = array("comment_text", null);
		$this->m_field['date'] = array("created_at", null);

		$where = "";
		#$this->m_debug = "Y";

        $group_id = get_param('group_id');
        if($group_id)
        {
            $where .= " AND group_id = " . to_sql($group_id, 'Number');
        }

        $where .= " AND group_id != 0 AND parent_id=0 ";

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "group_id, id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		$groupId = get_param_int('group_id');
		$html->setvar('group_ids', $groupId);
		if ($groupId) {
			$html->setvar('group_title', DB::result("SELECT group_title FROM groups_group WHERE group_id=" . to_sql($groupId), 0, 2));
			$html->parse('group_title', false);
		}

		parent::parseBlock($html);
	}

	function onItem(&$html, $row, $i, $last)
	{


		// var_dump($row); die();
		global $g;

        $this->m_field['user_id'][1] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
        if ($this->m_field['user_id'][1] == "") $this->m_field['user_id'][1] = "blank";


        $group_title = DB::result("SELECT title FROM groups_social WHERE group_id=" . $row['group_id'] . "", 0, 2);
		$this->m_field['group_id'][1] = strcut($group_title, 20);

        if ($this->m_field['group_id'][1] == "") $this->m_field['group_id'][1] = "blank";

        $this->m_field['comment'][1] = strcut($row['comment'], 48);

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

		$groupId = get_param_int('group_id');
		if (!$groupId) {
			$html->parse('group_th', false);
			$html->setvar('group_title_td', $this->m_field['group_id'][1]);
			$html->parse('group_td', false);
		}

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "groups_group_comments.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGroups());

include("../_include/core/administration_close.php");