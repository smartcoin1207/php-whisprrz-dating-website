<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsGroupMemberList extends CHtmlBlock
{
	var $m_need_container = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $groups_members_per_page = Common::getOption('groups_members_per_page', 'template_options');
        $n_results_per_page = $groups_members_per_page ? $groups_members_per_page : 6;

		$group_id = get_param('group_id');

		if(!$group_id)
        {
            $forum_id = get_param('forum_id');
            $forum = CGroupsTools::retrieve_forum_by_id($forum_id);
            if($forum)
            {
                $group_id = $forum['group_id'];
            }
        }

        $group = CGroupsTools::retrieve_group_by_id($group_id);
        if($group)
        {
        	$html->setvar('group_id', $group['group_id']);

            $sql_base = CGroupsTools::members_by_group_sql_base($group['group_id']);

            $n_results = CGroupsTools::count_from_sql_base($sql_base);
        }
        else
        {
            $html->setvar('group_id', 0);
            $n_results = 0;
        }

        {
            $page = intval(get_param('group_member_list_page', 1));
            $n_pages = ceil($n_results / $n_results_per_page);
            $page = max(1, min($n_pages, $page));

            $html->setvar('page', $page);
            $html->setvar('members_count', $n_results);
            $html->setvar('first_member_n', ($page - 1) * $n_results_per_page + 1);
            $html->setvar('last_member_n', min(($page) * $n_results_per_page, $n_results));

            if($this->m_need_container)
            {
                $html->parse('container_header');
                $html->parse('container_footer');
            }
        }

        if($group)
        {
            $members = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);
        }
        else
        {
        	$members = array();
        }

        {
            $n_members = 0;
            foreach($members as $member)
            {
                $html->clean('remove_member_button');

            	$html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault($member['user_id'], "r"));
                $html->setvar('user_name', User::nameShort($member['name']));
                $html->setvar('user_name_profile', $member['name']);
                $html->setvar('user_id', $member['user_id']);

                if(($group['user_id'] == $g_user['user_id'])and($g_user['user_id'] != $member['user_id']))
                    $html->parse('remove_member_button');

                $html->parse('member_photo', false);
                $html->setblockvar('member_no_photo', '');

                $html->parse('member');

                ++$n_members;
            }

            for(; $n_members < $n_results_per_page; ++$n_members)
            {
                $html->parse('member_no_photo', false);
                $html->setblockvar('member_photo', '');
                $html->parse('member');
            }

            if($page > 1)
            {
                $html->setvar('page_n', $page-1);
                $html->parse('pager_prev');
            }
            else
            {
            	$html->parse('pager_prev_inactive');
            }

            if($page < $n_pages)
            {
                $html->setvar('page_n', $page+1);
                $html->parse('pager_next');
            }
            else
            {
            	$html->parse('pager_next_inactive');
            }
        }

		parent::parseBlock($html);
	}
}

