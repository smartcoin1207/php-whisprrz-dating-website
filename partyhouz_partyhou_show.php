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

class Cpartyhouz extends CHtmlBlock
{
	var $m_partyhou;

	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $partyhou_id = get_param('partyhou_id', '');
        if($partyhou_id) {
	        $is_approved = CpartyhouzTools::is_approved_sql();
	        $partyhou_sql = "SELECT * FROM partyhouz_partyhou e WHERE partyhou_id = " . to_sql($partyhou_id) . $is_approved . " LIMIT 1";

	        $partyhou = DB::row($partyhou_sql);

	        if(!$partyhou) {
	        		redirect(Common::toHomePage());
	        }
        }

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		if($this->m_partyhou && !$this->m_partyhou['partyhou_private'])
		{
			$html->parse('comments_title');
		}

        $state = User::isNarrowBox('partyhouz');
        if  ($state) {
           $html->setvar('display', 'table-cell'); 
           $html->setvar('hide_narrow_box', 'block'); 
           $html->setvar('show_narrow_box', 'none'); 
        } else {
           $html->setvar('display', 'none'); 
           $html->setvar('hide_narrow_box', 'none'); 
           $html->setvar('show_narrow_box', 'block');            
        }
        
		parent::parseBlock($html);
	}
}

$page = new Cpartyhouz("", $g['tmpl']['dir_tmpl_main'] . "partyhouz_partyhou_show.html");


$partyhouz_partyhou_show = new Cpartyhouzpartyhouzhow("partyhouz_partyhou_show", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_show.html");
$page->add($partyhouz_partyhou_show);
$partyhouz_partyhou_image_list = new CpartyhouzpartyhouImageList("partyhouz_partyhou_image_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_image_list.html");
$partyhouz_partyhou_show->add($partyhouz_partyhou_image_list);

$partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
$page->add($partyhouz_header);

{
    $partyhou_id = get_param('partyhou_id');
    if (!User::isNarrowBox('partyhouz')) CpartyhouzTools::$thumbnail_postfix = 'orig';
    $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
    if($partyhou)
    {
        $page->m_partyhou = $partyhou;

    	$partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
    	$partyhouz_sidebar->m_first_block = "";
        $partyhouz_sidebar->m_second_block = "partyhou_show";
        $page->add($partyhouz_sidebar);

        if($partyhou['partyhou_private'])
		{
            $partyhouz_sidebar->m_first_block = "most_discussed";
			$partyhouz_sidebar->m_second_block = "popular_finished";
		}
    	else
    	{
	        $partyhouz_partyhou_guest_list = new CpartyhouzpartyhouGuestList("partyhouz_partyhou_guest_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_guest_list.html");
	        $page->add($partyhouz_partyhou_guest_list);

			$partyhouz_partyhou_comment_list = new CpartyhouzpartyhouCommentList("partyhouz_partyhou_comment_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_comment_list.html");
			$partyhouz_partyhou_comment_list->m_need_not_found_message = false;
			$page->add($partyhouz_partyhou_comment_list);
    	}
    }
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$partyhouz_custom_head = new CpartyhouzCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_custom_head.html");
$header->add($partyhouz_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
