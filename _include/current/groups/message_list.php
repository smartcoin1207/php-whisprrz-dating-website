<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsMessageList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "recently_added";
	var $m_groupsian_id = null;
	var $m_exclude_message_id = null;
	var $m_n_results_per_page = 10;
	var $m_message_where_when = true;
        var $m_country_id = null;
        var $m_category_id = null;
        var $m_groupsian_founded = null;
        var $m_message_datetime = null;
        var $m_query = null;
        var $m_need_not_found_message = true;
        var $m_n_results = null;
        var $m_upcoming = 0;
        var $m_no_private = false;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = get_param('n_results_per_page', $this->m_n_results_per_page);
        $message_where_when = get_param('message_where_when', $this->m_message_where_when);
        $list_type = get_param('list_type', $this->m_list_type);
        $groupsian_id = get_param('groupsian_id', $this->m_groupsian_id);
        $user_id = get_param('user_id', $g_user['user_id']);

        $country_id = get_param('country_id', $this->m_country_id);
        $category_id = get_param('category_id', $this->m_category_id);
        $groupsian_founded = get_param('groupsian_founded', $this->m_groupsian_founded);
        $message_datetime = get_param('message_datetime', $this->m_message_datetime);
        $query = get_param('query', $this->m_query);
        $message_place = get_param('message_place');
        $upcoming = get_param('upcoming', $this->m_upcoming);

        switch($list_type)
        {
            case "recently_added":
                if ($this->m_no_private) {
                    CGroupsTools::$isNoPrivate = true;
                }
                $sql_base = CGroupsTools::messages_recently_added_sql_base();
                break;
            case "search":
				// default
				$sql_base = CGroupsTools::messages_recently_added_sql_base();

                if($query)
                {
                    $sql_base = CGroupsTools::messages_by_query_sql_base($query);
                }
                else if($category_id)
                {
                	return;
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

        $page = intval(get_param('groups_message_list_page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('page', $page);
        $html->setvar('list_type', $list_type);
        $html->setvar('groupsian_id', $groupsian_id);
        $html->setvar('user_id', $user_id);
        $html->setvar('n_results_per_page', $n_results_per_page);
        $html->setvar('message_where_when', $message_where_when);

		$html->setvar('country_id', $country_id);
		$html->setvar('category_id', $category_id);
		$html->setvar('groupsian_founded', $groupsian_founded);
		$html->setvar('message_datetime', $message_datetime);
		$html->setvar('query', urlencode($query));
		$html->setvar('message_place', $message_place);
		$html->setvar('upcoming', $upcoming);

        if($this->m_need_container)
        {
            $html->parse('container_header');
            $html->parse('container_footer');
        }

        $groups = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($groups))
        {
        	if($message_where_when)
                $html->parse('groups_where_when_title');
            else
                $html->parse('groups_when_guests_comments_title');

	        foreach($groups as $message)
	        {
	            $html->clean('message_where_when_rows');
	            $html->clean('message_when_guests_comments_rows');

	        	$html->setvar('comment_id', $message['comment_id']);
				//$html->setvar('comment_text', strcut(to_html($message['comment_text']), 40));
                $ctext = strcut(to_html(CGroupsTools::filterRemoveUnusedTags($message['comment_text'])), 73);
                if (trim($ctext) == '') {
                    $ctext = l('[Click to view the media]');
                }
	            $html->setvar('comment_text', $ctext);
	            $html->setvar('comment_text_full', to_html(he($message['comment_text'])));

                $html->setvar('forum_id', $message['forum_id']);
                $html->setvar('forum_title', strcut(to_html($message['forum_title']), 15));
                $html->setvar('forum_title_full', to_html(he($message['forum_title'])));

	            $html->setvar('comment_user_name_full', to_html($message['user_name']));
	            $html->setvar('comment_user_name', to_html(User::nameShort($message['user_name'])));

	            $html->setvar('comment_created_at', Common::dateFormat($message['created_at'],'comment_created_at'));



                if($message_where_when)
                    $html->parse('message_where_when_rows');
                else
                    $html->parse('message_when_guests_comments_rows');

                $forum = CGroupsTools::retrieve_forum_by_id($message['forum_id']);
                $group = CGroupsTools::retrieve_group_by_id($forum['group_id']);
	        if(CGroupsTools::is_group_member($group['group_id'])||(!$group['group_private']))
                {
                    $html->clean("private_group_alert");
                    $html->subparse("forum_link");
                    $html->subparse("forum_message_link");
                    $html->subparse("forum_link_button");

                } else {
                     $html->clean("forum_link");
                     $html->clean("forum_message_link");
                     $html->clean("forum_link_button");
                     $html->subparse("private_group_alert");
                }


                $html->parse("message");
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

            if($message_where_when)
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

