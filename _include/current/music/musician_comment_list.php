<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicMusicianCommentList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_need_not_found_message = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = 5;

        $musician_id = get_param('musician_id');
        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
        if($musician)
        {
            $html->setvar('musician_id', $musician['musician_id']);
            $html->setvar('musician_name', strcut(to_html($musician['musician_name']), 24));
            $html->setvar('musician_name_full', to_html(he($musician['musician_name'])));

        	$sql_base = CMusicTools::comments_by_musician_sql_base($musician['musician_id']);

	        $n_results = CMusicTools::count_from_sql_base($sql_base);

	        $page = intval(get_param('musician_comment_list_page', 1));
	        $n_pages = ceil($n_results / $n_results_per_page);
	        $page = max(1, min($n_pages, $page));

	        $html->setvar('page', $page);

            if($this->m_need_container)
            {
                $html->parse('container_header');
                $html->parse('container_footer');
            }

	        $comments = CMusicTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

	        if(count($comments))
	        {
		        foreach($comments as $comment)
		        {
		            $html->setvar('comment_id', $comment['comment_id']);
		            $html->setvar('comment_text', to_html(Common::parseLinksSmile($comment['comment_text']),true,true));
		            $html->setvar('comment_created_at', Common::dateFormat($comment['created_at'],'music_comment_created_at'));

	                $html->setvar('user_id', $comment['user_id']);
	                $html->setvar('user_name', strcut(to_html($comment['name']), 40));
	                $html->setvar('user_name_full', to_html(he($comment['name'])));

	                if($musician['user_id'] == $g_user['user_id'] || $comment['user_id'] == $g_user['user_id'])
                        $html->parse('delete_button', false);
                    else
                        $html->setblockvar('delete_button', '');

	                $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault($comment['user_id'], "r"));

		            $html->parse("comment");
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
	        	//$html->parse("no_comments");
	        }
        }

		parent::parseBlock($html);
	}
}

