<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
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

        $redirect = true;

        if($this->m_upcoming_partyhouz_list->m_n_results)
        {
            $html->parse('partyhouz_i_created');
            $redirect = false;
        }

        if($this->m_finished_partyhouz_list->m_n_results)
        {
        	$html->parse('partyhouz_i_will_visit');
            $redirect = false;
        }

        if($redirect) {
            redirect('partyhouz_partyhou_edit.php');
        }

		parent::parseBlock($html);
	}
}

$page = new Cpartyhouz("", $g['tmpl']['dir_tmpl_main'] . "partyhouz_my.html");
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
$upcoming_partyhouz_partyhou_list->m_list_type = "by_user";
$upcoming_partyhouz_partyhou_list->m_partyhou_where_when = false;
$upcoming_partyhouz_partyhou_list->m_upcoming = 1;
$page->m_upcoming_partyhouz_list = $upcoming_partyhouz_partyhou_list;
$page->add($upcoming_partyhouz_partyhou_list);

$finished_partyhouz_partyhou_list = new CpartyhouzpartyhouList("finished_partyhouz_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_list.html");
$finished_partyhouz_partyhou_list->m_list_type = "by_user";
$finished_partyhouz_partyhou_list->m_partyhou_where_when = false;
$finished_partyhouz_partyhou_list->m_upcoming = 0;
$page->m_finished_partyhouz_list = $finished_partyhouz_partyhou_list;
$page->add($finished_partyhouz_partyhou_list);

include("./_include/core/main_close.php");
