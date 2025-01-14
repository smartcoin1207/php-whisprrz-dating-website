<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
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
    var $m_upcoming_partyhouz_list;
    var $m_finished_partyhouz_list;
    var $m_upcoming_random_partyhouz_list;
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

        $partyhou_place = get_param('partyhou_place');
        $html->setvar('partyhou_place', $partyhou_place);
        $category_id = get_param('category_id');
        $html->setvar('category_title', l(DB::result("SELECT category_title FROM partyhouz_category WHERE category_id = " . to_sql($category_id) . " LIMIT 1"), false, 'partyhouz_category'));
        $partyhou_datetime = get_param('partyhou_datetime');
        $html->setvar('partyhou_datetime', Common::dateFormat($partyhou_datetime, 'partyhou_datetime'));

        if($this->m_upcoming_partyhouz_list->m_n_results || $this->m_finished_partyhouz_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);

        	if($this->m_upcoming_partyhouz_list->m_n_results)
            {
                if($query)
                    $html->parse('upcoming_partyhouz_query_title');
                if($partyhou_place)
                    $html->parse('upcoming_partyhouz_partyhou_place_title');
                if($category_id)
                    $html->parse('upcoming_partyhouz_category_id_title');
                if($partyhou_datetime)
                    $html->parse('upcoming_partyhouz_partyhou_datetime_title');

            	$html->parse('upcoming_partyhouz_list');
            }

            if($this->m_finished_partyhouz_list->m_n_results)
            {
                if($query)
                    $html->parse('finished_partyhouz_query_title');
                if($partyhou_place)
                    $html->parse('finished_partyhouz_partyhou_place_title');
                if($category_id)
                    $html->parse('finished_partyhouz_category_id_title');
                if($partyhou_datetime)
                    $html->parse('finished_partyhouz_partyhou_datetime_title');

                $html->parse('finished_partyhouz_list');
            }
        }
        else
        {
            
            if($this->m_upcoming_random_partyhouz_list->m_n_results)
            {
                if($partyhou_place)
                    $html->parse('upcoming_random_partyhouz_partyhou_place_title');
                if($category_id)
                    $html->parse('upcoming_random_partyhouz_category_id_title');
                if($partyhou_datetime)
                    $html->parse('upcoming_random_partyhouz_partyhou_datetime_title');

            	$html->parse('upcoming_random_partyhouz_list');
            }



            $html->parse('partyhouz_not_found');
        }

		parent::parseBlock($html);
	}
}

$page = new Cpartyhouz("", $g['tmpl']['dir_tmpl_main'] . "partyhouz_search.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$partyhouz_custom_head = new CpartyhouzCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_custom_head.html");
$header->add($partyhouz_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
$page->add($partyhouz_header);
$partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
$partyhouz_sidebar->m_second_block = "most_anticipated";
$page->add($partyhouz_sidebar);

$upcoming_partyhouz_partyhou_list = new CpartyhouzpartyhouList("upcoming_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$upcoming_partyhouz_partyhou_list->m_list_type = "search";
$upcoming_partyhouz_partyhou_list->m_partyhou_where_when = false;
$upcoming_partyhouz_partyhou_list->m_upcoming = 1;
$page->m_upcoming_partyhouz_list = $upcoming_partyhouz_partyhou_list;
$page->add($upcoming_partyhouz_partyhou_list);

$finished_partyhouz_partyhou_list = new CpartyhouzpartyhouList("finished_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$finished_partyhouz_partyhou_list->m_list_type = "search";
$finished_partyhouz_partyhou_list->m_partyhou_where_when = false;
$finished_partyhouz_partyhou_list->m_upcoming = 0;
$page->m_finished_partyhouz_list = $finished_partyhouz_partyhou_list;
$page->add($finished_partyhouz_partyhou_list);


$upcoming_random_partyhouz_partyhou_list = new CpartyhouzpartyhouList("upcoming_random_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$upcoming_random_partyhouz_partyhou_list->m_list_type = "random";
$upcoming_random_partyhouz_partyhou_list->m_partyhou_where_when = false;
$upcoming_random_partyhouz_partyhou_list->m_upcoming = 1;
$page->m_upcoming_random_partyhouz_list = $upcoming_random_partyhouz_partyhou_list;
$page->add($upcoming_random_partyhouz_partyhou_list);


include("./_include/core/main_close.php");
