<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsForumCommentList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_need_not_found_message = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = 10;

        $forum_id = get_param('forum_id');
        $forum = CGroupsTools::retrieve_forum_by_id($forum_id);
        if($forum)
        {
            $group = CGroupsTools::retrieve_group_by_id($forum['group_id']);
        	$_REQUEST['group_id'] = $forum['group_id'];

        	$html->setvar('group_id', $forum['group_id']);
        	$html->setvar('forum_id', $forum['forum_id']);
            $html->setvar('forum_title', strcut(to_html($forum['forum_title']), 24));
            $html->setvar('forum_title_full', to_html(he($forum['forum_title'])));

        	$sql_base = CGroupsTools::comments_by_forum_sql_base($forum['forum_id']);

	        $n_results = CGroupsTools::count_from_sql_base($sql_base);

	        $page = get_param('forum_comment_list_page', 1);
	        $n_pages = ceil($n_results / $n_results_per_page);

	        if($page == 'last')
               $page = $n_pages;

	        $page = max(1, min($n_pages, $page));

	        $html->setvar('page', $page);

	        if($group['user_id'] == $g_user['user_id'] || CGroupsTools::is_group_member($group['group_id']))
            {
            	$html->setvar('allow_comments', 'true');
                $html->parse('new_comment_form');
            }
            else
            {
                $html->setvar('allow_comments', 'false');
            }

            if($this->m_need_container)
            {
                $html->parse('container_header');
                $html->parse('container_footer');
            }

	        $comments = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

	        if(count($comments))
	        {
                $state = User::isNarrowBox('groups_forum');
                $thumbnail_postfix = ($state) ? 'th' : 'orig';
		        foreach($comments as $comment)
		        {
					$html->setvar('posted_comment_id', $comment['comment_id']);

					$html->setvar('comment_id', $comment['comment_id']);
		            $html->setvar('comment_text', CGroupsTools::filter_text_to_html($comment['comment_text'], true, $thumbnail_postfix));
		            $html->setvar('comment_created_at_date', Common::itemDateFormat($comment['created_at']));
		            #$html->setvar('comment_created_at_time', date("g:ia", strtotime($comment['created_at'])));

	                $html->setvar('comment_user_id', $comment['user_id']);
	                $html->setvar('comment_user_name', strcut(to_html($comment['name']), 40));
	                $html->setvar('comment_user_name_full', to_html($comment['name']));

	                if($group['user_id'] == $g_user['user_id'] || $comment['user_id'] == $g_user['user_id'])
                        $html->parse('delete_button', false);
                    else
                        $html->setblockvar('delete_button', '');

	                $html->setvar('comment_user_photo', $g['path']['url_files'] . User::getPhotoDefault($comment['user_id'], "r"));

	                $comment_comments = CGroupsTools::retrieve_from_sql_base(
                        CGroupsTools::comments_by_forum_comment_sql_base($comment['comment_id']));
	                foreach($comment_comments as $comment_comment)
	                {
                        $html->setvar('comment_comment_id', $comment_comment['comment_id']);
                        $html->setvar('comment_comment_text', CGroupsTools::filter_text_to_html($comment_comment['comment_text'], true, $thumbnail_postfix, false));//to_html
                        $html->setvar('comment_comment_created_at_date', Common::itemDateFormat($comment_comment['created_at']));
                        #$html->setvar('comment_comment_created_at_time', date("g:ia", strtotime($comment_comment['created_at'])));

	                    $html->setvar('user_id', $comment_comment['user_id']);
	                    $html->setvar('user_name', strcut(to_html($comment_comment['name']), 40));
	                    $html->setvar('user_name_full', to_html($comment_comment['name']));

	                    if($group['user_id'] == $g_user['user_id'] || $comment_comment['user_id'] == $g_user['user_id'] || $comment['user_id'] == $g_user['user_id'])
	                        $html->parse('comment_delete_button', false);
	                    else
	                        $html->setblockvar('comment_delete_button', '');

	                    $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault($comment_comment['user_id'], "r"));

	                    $html->parse("comment_comment");
	                }

		            $html->parse("comment");
		            $html->clean("comment_comment");
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

		        $html->parse("comments");
	        }
	        else
	        {
	        	if($this->m_need_not_found_message)
                    $html->parse("no_comments_message");
	        	$html->parse("no_comments");
	        }
        }
        else
            redirect('groups.php');

		parent::parseBlock($html);
	}
}

