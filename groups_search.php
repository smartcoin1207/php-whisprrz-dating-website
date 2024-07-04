<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/groups/custom_head.php");
require_once("./_include/current/groups/header.php");
require_once("./_include/current/groups/sidebar.php");
require_once("./_include/current/groups/tools.php");
require_once("./_include/current/groups/group_show.php");
require_once("./_include/current/groups/group_image_list.php");
require_once("./_include/current/groups/group_member_list.php");
require_once("./_include/current/groups/group_comment_list.php");
require_once("./_include/current/groups/group_list.php");
require_once("./_include/current/groups/message_list.php");

class CGroups extends CHtmlBlock
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

        if($this->m_groups_group_list->m_n_results || $this->m_groups_message_list->m_n_results)
        {
            $query = get_param('query');
            $html->setvar('query', $query);
            $category_id = get_param('category_id');
            if($category_id)
                $html->setvar('category_title', l(DB::result("SELECT category_title FROM groups_category WHERE category_id = " . to_sql($category_id) . " LIMIT 1")));

            if($this->m_groups_group_list->m_n_results)
            {
                if($query)
                    $html->parse('search_groups_group_query_title');
                if($category_id)
                    $html->parse('search_groups_group_category_id_title');

                $html->parse('search_groups_group_list');
            }

            if($this->m_groups_message_list->m_n_results)
            {
                if($query)
                    $html->parse('search_groups_message_query_title');
                if($category_id)
                    $html->parse('search_groups_message_category_id_title');

                $html->parse('search_groups_message_list');
            }
        }
        else
        {
            $html->parse('groups_not_found_title');
        }

		parent::parseBlock($html);
	}
}

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups_search.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$groups_custom_head = new CGroupsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_groups_custom_head.html");
$header->add($groups_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$groups_header = new CGroupsHeader("groups_header", $g['tmpl']['dir_tmpl_main'] . "_groups_header.html");
$page->add($groups_header);
$groups_sidebar = new CGroupsSidebar("groups_sidebar", $g['tmpl']['dir_tmpl_main'] . "_groups_sidebar.html");
$page->add($groups_sidebar);

$groups_group_list = new CGroupsGroupList("groups_group_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_list.html");
$groups_group_list->m_list_type = "search";
$page->add($groups_group_list);
$page->m_groups_group_list = $groups_group_list;

$groups_message_list = new CGroupsMessageList("groups_message_list", $g['tmpl']['dir_tmpl_main'] . "_groups_message_list.html");
$groups_message_list->m_list_type = "search";
$page->add($groups_message_list);
$page->m_groups_message_list = $groups_message_list;

include("./_include/core/main_close.php");
