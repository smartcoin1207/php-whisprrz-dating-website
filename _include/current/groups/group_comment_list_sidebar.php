<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CGroupsGroupCommentListSidebar extends CHtmlBlock
{
    var $m_need_container = true;

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $n_results_per_page = 10;

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

            $sql_base = CGroupsTools::comments_by_group_recently_added_sql_base($group['group_id']);
            $comments = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page);

            if(count($comments))
            {
	            foreach($comments as $comment)
	            {
                    $html->setvar('user_name_full', $comment['user_name']);
	            	$html->setvar('user_name', strcut(to_html($comment['user_name']), 16));
                    $html->setvar('comment_id', $comment['comment_id']);
	                //$html->setvar('comment_text', strcut(to_html(he($comment['comment_text'])), 100));
                    $ctext = strcut(to_html(CGroupsTools::filterRemoveUnusedTags($comment['comment_text'])), 100);
                    if (trim($ctext) == '') {
                        $ctext = l('[Click to view the media]');
                    }
	                $html->setvar('comment_text', $ctext);

                    $html->setvar('comment_created_at_date', Common::itemDateFormat($comment['created_at']));
	                #$html->setvar('comment_created_at_time', date("g:ia", strtotime($comment['created_at'])));

	                $html->parse('comment');
	            }
            }
            else
            {
            	$html->parse('no_comments');
            }
        }

        parent::parseBlock($html);
    }
}
