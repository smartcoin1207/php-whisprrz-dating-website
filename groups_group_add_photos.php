<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/groups/custom_head.php");
require_once("./_include/current/groups/header.php");
require_once("./_include/current/groups/sidebar.php");
require_once("./_include/current/groups/tools.php");
require_once("./_include/current/groups/group_show.php");
require_once("./_include/current/groups/group_image_list.php");
require_once("./_include/current/groups/group_member_list.php");
require_once("./_include/current/groups/group_comment_list.php");
require_once("./_include/current/groups/group_forum_list_sidebar.php");

class CGroups extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;
		
        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $group_id = intval(get_param('group_id'));
            if($group_id)
            {
                $group = CGroupsTools::retrieve_group_by_id($group_id);
                if($group)
                {
                    for($image_n = 1; $image_n <= 4; ++$image_n)
                    {
                        $name = "image_" . $image_n;
                        CGroupsTools::do_upload_group_image($name, $group_id);
                    }
            
                    redirect('groups_group_show.php?group_id=' . $group['group_id']);
                }
            }
            redirect('groups.php');
        }
	}
	
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;
		
        $group_id = get_param('group_id');

		$group = CGroupsTools::retrieve_group_by_id($group_id);
        if($group)
        {
            $html->setvar('group_id', $group['group_id']);
        	$html->setvar('group_title', strcut(to_html($group['group_title']), 20));
            $html->setvar('group_title_full', to_html($group['group_title']));
        }
        	
        parent::parseBlock($html);
	}
}

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups_group_add_photos.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$groups_custom_head = new CGroupsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_groups_custom_head.html");
$header->add($groups_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$groups_group_show = new CGroupsGroupShow("groups_group_show", $g['tmpl']['dir_tmpl_main'] . "_groups_group_show.html");
$page->add($groups_group_show);
$groups_group_image_list = new CGroupsGroupImageList("groups_group_image_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_image_list.html");
$groups_group_show->add($groups_group_image_list);

$groups_header = new CGroupsHeader("groups_header", $g['tmpl']['dir_tmpl_main'] . "_groups_header.html");
$page->add($groups_header);

$groups_group_member_list = new CGroupsGroupMemberList("groups_group_member_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_member_list.html");
$page->add($groups_group_member_list);
$groups_group_forum_list_sidebar = new CGroupsGroupForumListSidebar("groups_group_forum_list_sidebar", $g['tmpl']['dir_tmpl_main'] . "_groups_group_forum_list_sidebar.html");
$page->add($groups_group_forum_list_sidebar);

$groups_group_comment_list = new CGroupsGroupCommentList("groups_group_comment_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_comment_list.html");
$groups_group_comment_list->m_need_not_found_message = false;
$page->add($groups_group_comment_list);

include("./_include/core/main_close.php");
