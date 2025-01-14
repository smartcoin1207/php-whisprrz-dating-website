<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsGroupList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "recently_added";
	var $m_groupsian_id = null;
	var $m_exclude_group_id = null;
	var $m_n_results_per_page = 10;
	var $m_group_where_when = true;
    var $m_country_id = null;
    var $m_category_id = null;
    var $m_groupsian_founded = null;
    var $m_group_datetime = null;
    var $m_query = null;
    var $m_need_not_found_message = true;
    var $m_n_results = null;
    var $m_upcoming = 0;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = get_param('n_results_per_page', $this->m_n_results_per_page);
        $group_where_when = get_param('group_where_when', $this->m_group_where_when);
        $list_type = get_param('list_type', $this->m_list_type);
        $groupsian_id = get_param('groupsian_id', $this->m_groupsian_id);
        $user_id = get_param('user_id', $g_user['user_id']);

        $country_id = get_param('country_id', $this->m_country_id);
        $category_id = get_param('category_id', $this->m_category_id);
        $groupsian_founded = get_param('groupsian_founded', $this->m_groupsian_founded);
        $group_datetime = get_param('group_datetime', $this->m_group_datetime);
        $query = get_param('query', $this->m_query);
        $group_place = get_param('group_place');
        $upcoming = get_param('upcoming', $this->m_upcoming);

        switch($list_type)
        {
            case "membership":
                $sql_base = CGroupsTools::groups_by_user_as_member_sql_base($user_id);
                break;
            case "by_user":
                $sql_base = CGroupsTools::groups_by_user_sql_base($user_id);
                break;
            case "most_discussed":
                $sql_base = CGroupsTools::groups_most_discussed_sql_base();
                break;
            case "recently_added":
                $sql_base = CGroupsTools::groups_recently_added_sql_base();
                break;
            case "most_popular":
                $sql_base = CGroupsTools::groups_most_popular_sql_base();
                break;
            case "search":
				// default
				$sql_base = CGroupsTools::groups_recently_added_sql_base();

                if($query)
                {
                    $sql_base = CGroupsTools::groups_by_query_sql_base($query);
                }
                else if($category_id)
                {
                    $sql_base = CGroupsTools::groups_by_category_id_sql_base($category_id);
                }


                break;
        }

        $n_results = CGroupsTools::count_from_sql_base($sql_base);

        if(!$n_results && $list_type == "by_groupsian")
        {
        	$sql_base = CGroupsTools::groups_by_groupsian_sql_base($groupsian_id);
        	$n_results = CGroupsTools::count_from_sql_base($sql_base);
        }

        $this->m_n_results = $n_results;

        /*if(!$n_results && $list_type == "search")
        {
        	$sql_base = CGroupsTools::groups_by_rand_sql_base();
            $n_results = min($n_results_per_page, CGroupsTools::count_from_sql_base($sql_base));
        }*/

        $page = intval(get_param('groups_group_list_page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('page', $page);
        $html->setvar('list_type', $list_type);
        $html->setvar('groupsian_id', $groupsian_id);
        $html->setvar('user_id', $user_id);
        $html->setvar('n_results_per_page', $n_results_per_page);
        $html->setvar('group_where_when', $group_where_when);

		$html->setvar('country_id', $country_id);
		$html->setvar('category_id', $category_id);
		$html->setvar('groupsian_founded', $groupsian_founded);
		$html->setvar('group_datetime', $group_datetime);
		$html->setvar('query', urlencode($query));
		$html->setvar('group_place', $group_place);
		$html->setvar('upcoming', $upcoming);

        if($this->m_need_container)
        {
            $html->parse('container_header');
            $html->parse('container_footer');
        }

        $groups = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($groups))
        {
        	if($group_where_when)
                $html->parse('groups_where_when_title');
            else
                $html->parse('groups_when_guests_comments_title');

	        foreach($groups as $group)
	        {
	            $html->clean('group_where_when_rows');
	            $html->clean('group_when_guests_comments_rows');

	        	$html->setvar('group_id', $group['group_id']);
	            $html->setvar('group_title', he(strcut(to_html($group['group_title']), 20)));
	            $html->setvar('group_title_full', he(to_html($group['group_title'])));

	            $html->setvar('group_n_comments', $group['group_n_comments']);
	            $html->setvar('group_n_members', $group['group_n_members']);
	            $html->setvar('group_n_posts', $group['group_n_posts']);

	            $html->setvar('group_user_name_full', to_html($group['user_name']));
	            $html->setvar('group_user_name', strcut(to_html(User::nameShort($group['user_name'])), 16));

	            $html->setvar('group_created_at', Common::dateFormat($group['created_at'],'group_created_at'));
	        if(CGroupsTools::is_group_member($group['group_id'])||(!$group['group_private']))
                {
                    $html->clean("private_group_alert");
                    $html->subparse("group_link");
                    $html->subparse("group_link_button");

                } else {
                     $html->clean("group_link");
                     $html->clean("group_link_button");
                     $html->subparse("private_group_alert");
                }
                if($group_where_when)
                    $html->parse('group_where_when_rows');
                else
                    $html->parse('group_when_guests_comments_rows');

                $html->parse("group");
	        }

            if($n_pages > 1)
            {
                if($page > 1)
                {
                    $html->setvar('page_n', $page-1);
                    $html->parse('pager_prev');
                }

                $links = pager_get_pages_links($n_pages, $page);

                foreach($links as $link)
                {
                    $html->setvar('page_n', $link);

                    if($page == $link)
                    {
                        $html->parse('pager_link_active', false);
                        $html->setblockvar('pager_link_not_active', '');
                    }
                    else
                    {
                        $html->parse('pager_link_not_active', false);
                        $html->setblockvar('pager_link_active', '');
                    }
                    $html->parse('pager_link');
                }

                if($page < $n_pages)
                {
                    $html->setvar('page_n', $page+1);
                    $html->parse('pager_next');
                }

                $html->parse('pager');
            }

            if($group_where_when)
                $html->parse('groups_where_when_footer');
            else
                $html->parse('groups_when_guests_comments_footer');

            $html->parse("groups");
        }
        else
        {
            if($this->m_need_not_found_message)
                $html->parse("no_groups_message");
        	$html->parse("no_groups");
        }

		parent::parseBlock($html);
	}
}

