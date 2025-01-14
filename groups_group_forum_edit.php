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
require_once("./_include/current/groups/group_comment_list_sidebar.php");

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
            $forum_id = get_param('forum_id');
            $group_id = get_param('group_id');

            $forum_title = get_param('forum_title');
            $forum_description = get_param('forum_description');

            if($group_id &&
                $forum_title &&
	            $forum_description)
            {
                $forum_description = Common::filter_text_to_db($forum_description,false);
                if($forum_id)
                {
                    if(!CGroupsTools::retrieve_forum_for_edit_by_id($forum_id))
                        redirect('groups.php');

                    DB::execute("UPDATE groups_forum SET " .
                                ", forum_title=".to_sql($forum_title).
                                ", forum_description=".to_sql($forum_description).
                                ", updated_at = NOW() WHERE forum_id=" . to_sql($forum_id, 'Number') . " LIMIT 1");
                }
                else
                {
                	$forum_id = CGroupsTools::create_forum($group_id, $forum_title, $forum_description);
                    //Wall::add('group_forum', $forum_id);
                }

                redirect('groups_group_forum_show.php?forum_id='.$forum_id);
            }
            redirect('groups.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $forum_id = get_param('forum_id');
        $forum = CGroupsTools::retrieve_forum_for_edit_by_id($forum_id);
        if($forum)
        {
        	$html->setvar('forum_id', $forum['forum_id']);
        	$html->setvar('group_id', $forum['group_id']);
            $html->setvar('forum_title', $forum['forum_title']);
            $html->setvar('forum_description', $forum['forum_description']);

            $html->parse('edit_title');
            $html->parse('edit_button');
        }
        elseif($forum_id)
        {
            redirect('groups.php');
        }
        else
        {
	        $group_id = get_param('group_id');
	        $group = CGroupsTools::retrieve_group_by_id($group_id);
	        if($group)
	        {
	            $html->setvar('group_id', $group['group_id']);
	        	$html->setvar('forum_title', l('groups_default_title'));
	            $html->setvar('forum_description', l('groups_default_description'));

	            $html->parse('create_title');
	            $html->parse('create_button');
	        }
	        else
	        {
	        	redirect('groups.php');
	        }
        }

        parent::parseBlock($html);
	}
}

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups_group_forum_edit.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$groups_custom_head = new CGroupsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_groups_custom_head.html");
$header->add($groups_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$groups_header = new CGroupsHeader("groups_header", $g['tmpl']['dir_tmpl_main'] . "_groups_header.html");
$page->add($groups_header);

$groups_group_show = new CGroupsGroupShow("groups_group_show", $g['tmpl']['dir_tmpl_main'] . "_groups_group_show.html");
$page->add($groups_group_show);
$groups_group_image_list = new CGroupsGroupImageList("groups_group_image_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_image_list.html");
$groups_group_show->add($groups_group_image_list);

$groups_group_member_list = new CGroupsGroupMemberList("groups_group_member_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_member_list.html");
$page->add($groups_group_member_list);
$groups_group_comment_list_sidebar = new CGroupsGroupCommentListSidebar("groups_group_comment_list_sidebar", $g['tmpl']['dir_tmpl_main'] . "_groups_group_comment_list_sidebar.html");
$page->add($groups_group_comment_list_sidebar);

include("./_include/core/main_close.php");
