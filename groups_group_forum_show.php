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
require_once("./_include/current/groups/group_comment_list_sidebar.php");
require_once("./_include/current/groups/forum_comment_list.php");
require_once("./_include/current/groups/group_forum_list_sidebar.php");

class CGroups extends CHtmlBlock
{
	var $m_group;

	function action()
	{
		global $g_user;
        global $l;
        global $g;
        $cmd = get_param('cmd');
        $forum_id = get_param("forum_id",0);
        $group_id = DB::field("groups_forum","group_id","forum_id=" . to_sql($forum_id, 'Number'));

        if(count($group_id) == 0) {
            redirect('groups.php');
        }

        $private = DB::field("groups_group","group_private","group_id=".$group_id[0]);

        if(count($private) == 0) {
            redirect('groups.php');
        }
        if ($cmd == 'delete') {
            $group_id = DB::field('groups_forum', 'group_id', 'forum_id = ' . to_sql($forum_id, 'Number'));
            DB::delete('groups_forum', 'forum_id = ' . to_sql($forum_id, 'Number'));
            redirect('groups_group_forum_list.php?group_id=' . $group_id[0]);
        }

        if((!CGroupsTools::is_group_member($group_id[0]))&&($private[0]))
            redirect("groups.php");

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $forum_id = get_param('forum_id');
        $forum = CGroupsTools::retrieve_forum_by_id($forum_id);
        if($forum)
        {
            $html->setvar('forum_title_full', $forum['forum_title']);
            $html->setvar('forum_title', hard_trim(to_html($forum['forum_title']), 20));
            $html->setvar('forum_description', to_html($forum['forum_description']));
            if ($forum['user_id'] == $g_user['user_id']) {
                $html->setvar('forum_id', $forum_id);
                $html->parse('forum_desc_edit');
                $html->parse('forum_title_edit');
                $html->parse('forum_edit_js');
                $html->parse('forum_delete');
            } else {
                $html->parse('forum_desc_edit_no');
                $html->parse('forum_title_edit_no');
            }

            DB::execute("UPDATE groups_forum SET " .
                " forum_n_views=forum_n_views+1 WHERE forum_id=" . to_sql($forum_id, 'Number') . " LIMIT 1");
        }

        $group_id = DB::field('groups_forum', 'group_id', 'forum_id=' . to_sql($forum_id, 'Number'));
        $html->setvar('group_id', $group_id[0]);

        $state = User::isNarrowBox('groups_forum');
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

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups_group_forum_show.html");


$groups_group_show = new CGroupsGroupShow("groups_group_show", $g['tmpl']['dir_tmpl_main'] . "_groups_group_show.html");
$page->add($groups_group_show);
$groups_group_image_list = new CGroupsGroupImageList("groups_group_image_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_image_list.html");
$groups_group_show->add($groups_group_image_list);

$groups_header = new CGroupsHeader("groups_header", $g['tmpl']['dir_tmpl_main'] . "_groups_header.html");
$page->add($groups_header);

$groups_group_member_list = new CGroupsGroupMemberList("groups_group_member_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_member_list.html");
$page->add($groups_group_member_list);
$groups_group_comment_list_sidebar = new CGroupsGroupCommentListSidebar("groups_group_comment_list_sidebar", $g['tmpl']['dir_tmpl_main'] . "_groups_group_comment_list_sidebar.html");
$page->add($groups_group_comment_list_sidebar);

$groups_forum_comment_list = new CGroupsForumCommentList("groups_forum_comment_list", $g['tmpl']['dir_tmpl_main'] . "_groups_forum_comment_list.html");
$groups_forum_comment_list->m_need_not_found_message = false;
$page->add($groups_forum_comment_list);


$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$groups_custom_head = new CGroupsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_groups_custom_head.html");
$header->add($groups_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");
