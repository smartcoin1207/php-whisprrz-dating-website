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

		$this->m_sql_count = "SELECT COUNT(m.event_id) FROM events_event_comment AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM events_event_comment AS m
			" . $this->m_sql_from_add . "
		";

        $this->m_field['comment_id'] = array("comment_id", null);
		$this->m_field['event_id'] = array("event_id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['comment_text'] = array("comment_text", null);
		$this->m_field['created_at'] = array("created_at", null);

		$where = "";
		#$this->m_debug = "Y";

        $event_id = get_param('event_id');
        if($event_id)
        {
            $where .= " AND event_id = " . to_sql($event_id, 'Number');
        }

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "event_id";
		$this->m_sql_from_add = "";
	}

	function parseBlock(&$html)
	{
		$eventId = get_param_int('event_id');
		$html->setvar('event_ids', $eventId);
		if ($eventId) {
			$html->setvar('event_title', DB::result("SELECT event_title FROM events_event WHERE event_id=" . to_sql($eventId), 0, 2));
			$html->parse('event_title', false);
		}

		parent::parseBlock($html);
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $this->m_field['user_id'][1] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
        if ($this->m_field['user_id'][1] == "") $this->m_field['user_id'][1] = "blank";

        $this->m_field['event_id'][1] = DB::result("SELECT event_title FROM events_event WHERE event_id=" . $row['event_id'] . "", 0, 2);
        if ($this->m_field['event_id'][1] == "") $this->m_field['event_id'][1] = "blank";

        $this->m_field['comment_text'][1] = strcut($row['comment_text'], 48);

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

		$eventId = get_param_int('event_id');
		if (!$eventId) {
			$html->parse('event_th', false);
			$html->setvar('event_title_td', $this->m_field['event_id'][1]);
			$html->parse('event_td', false);
		}

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "events_event_comments.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuEvents());

include("../_include/core/administration_close.php");