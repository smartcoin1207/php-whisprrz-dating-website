<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsGroupForumList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_need_not_found_message = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = 10;

        $group_id = get_param('group_id');
        $group = CGroupsTools::retrieve_group_by_id($group_id);
        if($group)
        {
            $html->setvar('group_id', $group['group_id']);
            $html->setvar('group_title', strcut(to_html(he($group['group_title'])), 24));
            $html->setvar('group_title_full', to_html(he($group['group_title'])));

        	$sql_base = CGroupsTools::forums_by_group_recently_updated_sql_base($group['group_id']);

	        $n_results = CGroupsTools::count_from_sql_base($sql_base);

	        $page = intval(get_param('group_forum_list_page', 1));
	        $n_pages = ceil($n_results / $n_results_per_page);
	        $page = max(1, min($n_pages, $page));

	        $html->setvar('page', $page);

            if($this->m_need_container)
            {
                $html->parse('container_header');
                $html->parse('container_footer');
            }

	        $forums = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

	        if(count($forums))
	        {
		        foreach($forums as $forum)
		        {
		        	$html->clean('forum_delete');
		        	$html->clean('forum_last_message');
		        	$html->clean('forum_last_message_never');

		        	$html->setvar('forum_id', $forum['forum_id']);
                    $html->setvar('forum_title_full', to_html(he($forum['forum_title'])));
                    $html->setvar('forum_title', strcut(to_html($forum['forum_title']), 30));
                    $html->setvar('forum_description', strcut(to_html($forum['forum_description']), 100));
                    $html->setvar('forum_n_comments', $forum['forum_n_comments']);
                    $html->setvar('forum_n_views', $forum['forum_n_views']);

		            if($group['user_id'] == $g_user['user_id'])
		            {
		            	$html->parse('forum_delete');
		            }

		            $last_comments = CGroupsTools::retrieve_from_sql_base(
                        CGroupsTools::comments_by_forum_recently_added_sql_base($forum['forum_id']), 1);

                    if(count($last_comments))
                    {
                        $comment = $last_comments[0];

	                    $html->setvar('user_name_full', $comment['user_name']);
	                    $html->setvar('user_name', strcut(to_html($comment['user_name']), 6));
	                    $html->setvar('comment_created_at', Common::dateFormat($comment['created_at'],'comment_created_at'));

                        $html->parse('forum_last_message');
                    }
                    else
                    {
                    	$html->parse('forum_last_message_never');
                    }

                    $html->parse('forum');
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

		        $html->parse("forums");
	        }
	        else
	        {
	        	if($this->m_need_not_found_message)
                    $html->parse("no_forums_message");
	        	$html->parse("no_forums");
	        }
        }
        else
            redirect('groups.php');

		parent::parseBlock($html);
	}
}

