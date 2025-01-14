<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/hotdates/custom_head.php");
require_once("./_include/current/hotdates/header.php");
require_once("./_include/current/hotdates/sidebar.php");
require_once("./_include/current/hotdates/tools.php");
require_once("./_include/current/hotdates/hotdate_show.php");
require_once("./_include/current/hotdates/hotdate_image_list.php");
require_once("./_include/current/hotdates/hotdate_guest_list.php");
require_once("./_include/current/hotdates/hotdate_comment_list.php");
require_once("./_include/current/hotdates/hotdate_list.php");

class CHotdates extends CHtmlBlock
{
    var $m_upcoming_hotdates_list;
    var $m_finished_hotdates_list;
    var $m_upcoming_random_hotdates_list;
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

        $hotdate_place = get_param('hotdate_place');
        $html->setvar('hotdate_place', $hotdate_place);
        $category_id = get_param('category_id');
        $html->setvar('category_title', l(DB::result("SELECT category_title FROM hotdates_category WHERE category_id = " . to_sql($category_id) . " LIMIT 1"), false, 'hotdates_category'));
        $hotdate_datetime = get_param('hotdate_datetime');
        $html->setvar('hotdate_datetime', Common::dateFormat($hotdate_datetime, 'hotdate_datetime'));

        if($this->m_upcoming_hotdates_list->m_n_results || $this->m_finished_hotdates_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);

        	if($this->m_upcoming_hotdates_list->m_n_results)
            {
                if($query)
                    $html->parse('upcoming_hotdates_query_title');
                if($hotdate_place)
                    $html->parse('upcoming_hotdates_hotdate_place_title');
                if($category_id)
                    $html->parse('upcoming_hotdates_category_id_title');
                if($hotdate_datetime)
                    $html->parse('upcoming_hotdates_hotdate_datetime_title');

            	$html->parse('upcoming_hotdates_list');
            }

            if($this->m_finished_hotdates_list->m_n_results)
            {
                if($query)
                    $html->parse('finished_hotdates_query_title');
                if($hotdate_place)
                    $html->parse('finished_hotdates_hotdate_place_title');
                if($category_id)
                    $html->parse('finished_hotdates_category_id_title');
                if($hotdate_datetime)
                    $html->parse('finished_hotdates_hotdate_datetime_title');

                $html->parse('finished_hotdates_list');
            }
        }
        else
        {
            if($this->m_upcoming_random_hotdates_list->m_n_results)
            {
                if($hotdate_place)
                    $html->parse('upcoming_random_hotdates_hotdate_place_title');
                if($category_id)
                    $html->parse('upcoming_random_hotdates_category_id_title');
                if($hotdate_datetime)
                    $html->parse('upcoming_random_hotdates_hotdate_datetime_title');

            	$html->parse('upcoming_random_hotdates_list');
            }



            $html->parse('hotdates_not_found');
        }

		parent::parseBlock($html);
	}
}

$page = new CHotdates("", $g['tmpl']['dir_tmpl_main'] . "hotdates_search.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$hotdates_custom_head = new CHotdatesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_hotdates_custom_head.html");
$header->add($hotdates_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$hotdates_header = new CHotdatesHeader("hotdates_header", $g['tmpl']['dir_tmpl_main'] . "_hotdates_header.html");
$page->add($hotdates_header);
$hotdates_sidebar = new CHotdatesSidebar("hotdates_sidebar", $g['tmpl']['dir_tmpl_main'] . "_hotdates_sidebar.html");
$hotdates_sidebar->m_second_block = "most_anticipated";
$page->add($hotdates_sidebar);

$upcoming_hotdates_hotdate_list = new CHotdatesHotdateList("upcoming_hotdates_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$upcoming_hotdates_hotdate_list->m_list_type = "search";
$upcoming_hotdates_hotdate_list->m_hotdate_where_when = false;
$upcoming_hotdates_hotdate_list->m_upcoming = 1;
$page->m_upcoming_hotdates_list = $upcoming_hotdates_hotdate_list;
$page->add($upcoming_hotdates_hotdate_list);

$finished_hotdates_hotdate_list = new CHotdatesHotdateList("finished_hotdates_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$finished_hotdates_hotdate_list->m_list_type = "search";
$finished_hotdates_hotdate_list->m_hotdate_where_when = false;
$finished_hotdates_hotdate_list->m_upcoming = 0;
$page->m_finished_hotdates_list = $finished_hotdates_hotdate_list;
$page->add($finished_hotdates_hotdate_list);


$upcoming_random_hotdates_hotdate_list = new CHotdatesHotdateList("upcoming_random_hotdates_list", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_list.html");
$upcoming_random_hotdates_hotdate_list->m_list_type = "random";
$upcoming_random_hotdates_hotdate_list->m_hotdate_where_when = false;
$upcoming_random_hotdates_hotdate_list->m_upcoming = 1;
$page->m_upcoming_random_hotdates_list = $upcoming_random_hotdates_hotdate_list;
$page->add($upcoming_random_hotdates_hotdate_list);


include("./_include/core/main_close.php");
