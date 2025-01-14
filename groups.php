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

        $sql_base = CGroupsTools::groups_most_popular_sql_base();
        $groups = CGroupsTools::retrieve_from_sql_base($sql_base, 2);
        $group_n = 1;

        if(CGroupsTools::number_of_groups_where_user_is_member($g_user["user_id"]))
            $html->parse("my_groups");

        foreach($groups as $group)
        {
            $html->setvar('group_id', $group['group_id']);
            $html->setvar('group_title', strcut(to_html($group['group_title']), 25));
            $html->setvar('group_title_full', to_html($group['group_title']));

            $html->setvar('group_n_comments', $group['group_n_comments']);
            $html->setvar('group_n_posts', $group['group_n_posts']);
            $html->setvar('group_n_members', $group['group_n_members']);

            $html->setvar('group_description', strcut(to_html($group['group_description']), 30));

            $images = CGroupsTools::group_images($group['group_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail_b"]);

            if($group_n == count($groups))
                $html->parse("group_last");

	        if(CGroupsTools::is_group_member($group['group_id'])||(!$group['group_private']))
                {
                    $html->clean("private_group_alert");
                    $html->subparse("group_link");
                    $html->subparse("group_link_img");

                } else {
                     $html->clean("group_link");
                     $html->clean("group_link_img");
                     $html->subparse("private_group_alert");
                }

            $html->parse("group");

            ++$group_n;
        }

		parent::parseBlock($html);
	}
}

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups.html");
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

$groups_message_list = new CGroupsMessageList("groups_message_list", $g['tmpl']['dir_tmpl_main'] . "_groups_message_list.html");
$groups_message_list->m_no_private = true;
$groups_message_list->m_list_type = "recently_added";
$page->add($groups_message_list);

include("./_include/core/main_close.php");
