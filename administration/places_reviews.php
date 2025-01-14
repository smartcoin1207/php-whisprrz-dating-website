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

        $this->m_sql_count = "SELECT COUNT(r.id) FROM places_review AS r " . $this->m_sql_from_add . "";
        $this->m_sql = "
            SELECT r.*
            FROM places_review AS r
            " . $this->m_sql_from_add . "
        ";

		$this->m_field['id'] = array("id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['place_id'] = array("place_id", null);
		$this->m_field['title'] = array("title", null);
        $this->m_field['text'] = array("text", null);
		$this->m_field['n_votes'] = array("n_votes", null);
		$this->m_field['created_at'] = array("created_at", null);
		$this->m_field['updated_at'] = array("updated_at", null);

		$where = "";
        #$this->m_debug = "Y";

        $place_id = get_param('place_id');
        if($place_id)
        {
            $where .= " AND place_id = " . to_sql($place_id, 'Number');
        }

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $this->m_field['user_id'][1] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
        if ($this->m_field['user_id'][1] == "") $this->m_field['user_id'][1] = "blank";

		$this->m_field['place_id'][1] = DB::result("SELECT name FROM places_place WHERE id=" . $row['place_id'] . "", 0, 2);
		if ($this->m_field['place_id'][1] == "") $this->m_field['place_id'][1] = "blank";

		$this->m_field['title'][1] = strcut($row['title'], 48);

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "places_reviews.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuPlace());

include("../_include/core/administration_close.php");