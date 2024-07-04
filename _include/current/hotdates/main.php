<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once("./_include/current/hotdates/custom_head.php");
require_once("./_include/current/hotdates/header.php");
require_once("./_include/current/hotdates/sidebar.php");
require_once("./_include/current/hotdates/tools.php");
require_once("./_include/current/hotdates/hotdate_show.php");
require_once("./_include/current/hotdates/hotdate_image_list.php");
require_once("./_include/current/hotdates/hotdate_guest_list.php");
require_once("./_include/current/hotdates/hotdate_comment_list.php");
require_once("./_include/current/hotdates/hotdate_list.php");

class Chotdates extends CHtmlBlock
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
		if(defined('DEMO_hotdates')) {
            $hotdates = array();
			$where = " e.hotdate_title='Norah Jones' AND ";
			$sql_base = ChotdatesTools::hotdates_upcoming_main_page_sql_base($where);
			$hotdates_demo = ChotdatesTools::retrieve_from_sql_base($sql_base, 1);
            if (isset($hotdates_demo[0]))
                $hotdates[0] = $hotdates_demo[0];

			$where = " e.hotdate_title='Bon Jovi' AND ";
			$sql_base = ChotdatesTools::hotdates_upcoming_main_page_sql_base($where);
			$hotdates_demo = ChotdatesTools::retrieve_from_sql_base($sql_base, 1);
            if (isset($hotdates_demo[0]))
                $hotdates[1] = $hotdates_demo[0];
		}
		else {
			$sql_base = ChotdatesTools::hotdates_upcoming_main_page_sql_base();
			$hotdates = ChotdatesTools::retrieve_from_sql_base($sql_base, 2);
		}

        $hotdate_n = 1;

        foreach($hotdates as $hotdate)
        {
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), 20));
            $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));

            $html->setvar('hotdate_n_comments', $hotdate['hotdate_n_comments']);
            $html->setvar('hotdate_n_guests', $hotdate['hotdate_n_guests']);
            $html->setvar('hotdate_place', strcut(to_html($hotdate['hotdate_place']), 13));
            $html->setvar('hotdate_place_full', to_html($hotdate['hotdate_place']));

	        $html->setvar('hotdate_date', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_date')));
	        $html->setvar('hotdate_datetime_raw', to_html($hotdate['hotdate_datetime']));
	        $html->setvar('hotdate_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_time')));

            $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail_b"]);

            if($hotdate_n == count($hotdates))
                $html->parse("hotdate_last");

            $html->parse("hotdate");

            ++$hotdate_n;
        }

		parent::parseBlock($html);
	}
}

$page = new Chotdates("", $g['tmpl']['dir_tmpl_main'] . "hotdates.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$hotdates_custom_head = new ChotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
$header->add($hotdates_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$hotdates_header = new ChotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
$page->add($hotdates_header);
$hotdates_sidebar = new ChotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
$hotdates_sidebar->m_second_block = "popular_finished";
$page->add($hotdates_sidebar);

$hotdates_hotdate_list = new ChotdateshotdateList("hotdates_hotdate_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$hotdates_hotdate_list->m_list_type = "most_anticipated";
$page->add($hotdates_hotdate_list);