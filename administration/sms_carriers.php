<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23
include("../_include/core/administration_start.php");

class CCarrier extends CHtmlList
{
	function action()
	{
	}
	function init()
	{
		global $g;

		$this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(c.id) FROM carrier AS c " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT c.*
			FROM carrier AS c 
			" . $this->m_sql_from_add . "
		";

		$this->m_field['id'] = array("id", null);
		$this->m_field['country_id'] = array("country_id", null);
		$this->m_field['state_id'] = array("state_id", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['email'] = array("email", null);

		$where = "";
		#$this->m_debug = "Y";

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

		/*if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }*/
		
		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CCarrier("main", $g['tmpl']['dir_tmpl_administration'] . "sms_carriers.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
