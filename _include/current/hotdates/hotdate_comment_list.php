<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class ChotdateshotdateCommentList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_need_not_found_message = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = 5;
		
        $hotdate_id = get_param('hotdate_id');
        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
        if($hotdate)
        {
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), 24));
            $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));

        	$sql_base = ChotdatesTools::comments_by_hotdate_sql_base($hotdate['hotdate_id']);

	        $n_results = ChotdatesTools::count_from_sql_base($sql_base);

	        $page = intval(get_param('hotdate_comment_list_page', 1));
	        $n_pages = ceil($n_results / $n_results_per_page);
	        $page = max(1, min($n_pages, $page));

	        $html->setvar('page', $page);

            if($this->m_need_container)
            {
                $html->parse('container_header');
                $html->parse('container_footer');
            }

	        $comments = ChotdatesTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

	        if(count($comments))
	        {
                $i=0;
                $state = User::isNarrowBox('hotdates');
                $thumbnail_postfix = ($state) ? 'th' : 'orig';
		        foreach($comments as $comment)
		        {
		        	if($i++==0) $html->setvar('posted_comment_id', $comment['comment_id']);

		        	$html->setvar('comment_id', $comment['comment_id']);
		            $html->setvar('comment_text', ChotdatesTools::filter_text_to_html($comment['comment_text']));

		            $html->setvar('comment_created_at_date', Common::itemDateFormat($comment['created_at']));
		            #$html->setvar('comment_created_at_time', date("H:i", strtotime($comment['created_at'])));

	                $html->setvar('comment_user_id', $comment['user_id']);
	                $html->setvar('comment_user_name', strcut(to_html($comment['name']), 40));
	                $html->setvar('comment_user_name_full', to_html($comment['name']));

	                if($hotdate['user_id'] == $g_user['user_id'] || $comment['user_id'] == $g_user['user_id'])
                        $html->parse('delete_button', false);
                    else
                        $html->setblockvar('delete_button', '');

	                $html->setvar('comment_user_photo', $g['path']['url_files'] . User::getPhotoDefault($comment['user_id'], "r"));

	                $comment_comments = ChotdatesTools::retrieve_from_sql_base(ChotdatesTools::comments_by_comment_sql_base($comment['comment_id']));
	                foreach($comment_comments as $comment_comment)
	                {
                        $html->setvar('comment_comment_id', $comment_comment['comment_id']);
                        $html->setvar('comment_comment_text', ChotdatesTools::filter_text_to_html($comment_comment['comment_text'], true, $thumbnail_postfix, false));
                        $html->setvar('comment_comment_created_at_date', Common::itemDateFormat($comment_comment['created_at']));
                        #$html->setvar('comment_comment_created_at_time', date("g:ia", strtotime($comment_comment['created_at'])));

	                    $html->setvar('user_id', $comment_comment['user_id']);
	                    $html->setvar('user_name', strcut(to_html($comment_comment['name']), 40));
	                    $html->setvar('user_name_full', to_html($comment_comment['name']));

	                    if($hotdate['user_id'] == $g_user['user_id'] || $comment_comment['user_id'] == $g_user['user_id'] || $comment['user_id'] == $g_user['user_id'])
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

		parent::parseBlock($html);
	}
}

