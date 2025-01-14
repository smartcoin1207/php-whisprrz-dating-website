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

		$this->m_sql_count = "SELECT COUNT(m.event_id) FROM events_event AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM events_event AS m
			" . $this->m_sql_from_add . "
		";

		$this->m_field['event_id'] = array("event_id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['category_id'] = array("category_id", null);
		$this->m_field['event_title'] = array("event_title", null);
		$this->m_field['event_datetime'] = array("event_datetime", null);
		$this->m_field['event_has_images'] = array("event_has_images", null);
        $this->m_field['event_n_guests'] = array("event_n_guests", null);
		$this->m_field['created_at'] = array("created_at", null);

		$where = "";
		#$this->m_debug = "Y";

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "event_id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

		$this->m_field['event_has_images'][1] =  ($row['event_has_images'])? l('yes'):l('no');
        $this->m_field['user_id'][1] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
        if ($this->m_field['user_id'][1] == "") $this->m_field['user_id'][1] = "blank";

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

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "events_events.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuEvents());

include("../_include/core/administration_close.php");