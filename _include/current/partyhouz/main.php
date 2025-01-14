<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once("./_include/current/partyhouz/custom_head.php");
require_once("./_include/current/partyhouz/header.php");
require_once("./_include/current/partyhouz/sidebar.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_show.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");
require_once("./_include/current/partyhouz/partyhou_guest_list.php");
require_once("./_include/current/partyhouz/partyhou_comment_list.php");
require_once("./_include/current/partyhouz/partyhou_list.php");

class Cpartyhouz extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        // DEMO
		if(defined('DEMO_partyhouz')) {
            $partyhouz = array();
			$where = " e.partyhou_title='Norah Jones' AND ";
			$sql_base = CpartyhouzTools::partyhouz_upcoming_main_page_sql_base($where);
			$partyhouz_demo = CpartyhouzTools::retrieve_from_sql_base($sql_base, 1);
            if (isset($partyhouz_demo[0]))
                $partyhouz[0] = $partyhouz_demo[0];

			$where = " e.partyhou_title='Bon Jovi' AND ";
			$sql_base = CpartyhouzTools::partyhouz_upcoming_main_page_sql_base($where);
			$partyhouz_demo = CpartyhouzTools::retrieve_from_sql_base($sql_base, 1);
            if (isset($partyhouz_demo[0]))
                $partyhouz[1] = $partyhouz_demo[0];
		}
		else {
			$sql_base = CpartyhouzTools::partyhouz_upcoming_main_page_sql_base();
			$partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_base, 2);
		}

        $partyhou_n = 1;

        foreach($partyhouz as $partyhou)
        {
            $html->setvar('partyhou_id', $partyhou['partyhou_id']);
            $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), 20));
            $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));

            $html->setvar('partyhou_n_comments', $partyhou['partyhou_n_comments']);
            $html->setvar('partyhou_n_guests', $partyhou['partyhou_n_guests']);

	        $html->setvar('partyhou_date', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_date')));
	        $html->setvar('partyhou_datetime_raw', to_html($partyhou['partyhou_datetime']));
	        $html->setvar('partyhou_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_time')));

            $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail_b"]);

            if($partyhou_n == count($partyhouz))
                $html->parse("partyhou_last");

            $html->parse("partyhou");

            ++$partyhou_n;
        }

		parent::parseBlock($html);
	}
}

$page = new Cpartyhouz("", $g['tmpl']['dir_tmpl_main'] . "partyhouz.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$partyhouz_custom_head = new CpartyhouzCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_custom_head.html");
$header->add($partyhouz_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
$page->add($partyhouz_header);
$partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
$partyhouz_sidebar->m_second_block = "popular_finished";
$page->add($partyhouz_sidebar);

$partyhouz_partyhou_list = new CpartyhouzpartyhouList("partyhouz_partyhou_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$partyhouz_partyhou_list->m_list_type = "most_anticipated";
$page->add($partyhouz_partyhou_list);