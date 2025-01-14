<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


#$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/forum.php");

payment_check('forum');

class CForumTopics extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		global $g_info;
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		$forum_id = get_param("forum_id");

		$forum = CForumForum::retrieve_by_id($forum_id);

        if(!$forum) {
            redirect('forum.php');
        }

        $html->setvar('forum_id', $forum['id']);
        $html->setvar('forum_title', l($forum['title'], false, 'forum_title'));

        if(user_has_role_admin_view())
            $html->parse('forum_forum_admin', false);

        $subforum = $forum;

        while($subforum['parent_forum_id'])
        {
        	$subforum = CForumForum::retrieve_by_id($subforum['parent_forum_id']);
        }

        $category = CForumCategory::retrieve_by_id($subforum['category_id']);

        $html->setvar('category_id', $category['id']);
        $html->setvar('category_title', l($category['title'], false, 'forum_category_title'));

        $settings = CForum::settings();

        $sort_by = $settings['sort_by'];
        $sort_by_dir = $settings['sort_by_dir'];

        if($sort_by == 'thread' && $sort_by_dir == 'asc')
            $html->setvar('sort_by_thread', '&sort_by=thread&sort_by_dir=desc');
        else
            $html->setvar('sort_by_thread', '&sort_by=thread&sort_by_dir=asc');

        if($sort_by == 'thread_starter' && $sort_by_dir == 'asc')
            $html->setvar('sort_by_thread_starter', '&sort_by=thread_starter&sort_by_dir=desc');
        else
            $html->setvar('sort_by_thread_starter', '&sort_by=thread_starter&sort_by_dir=asc');

        if($sort_by == 'last_post' && $sort_by_dir == 'asc')
            $html->setvar('sort_by_last_post', '&sort_by=last_post&sort_by_dir=desc');
        else
            $html->setvar('sort_by_last_post', '&sort_by=last_post&sort_by_dir=asc');

        if($sort_by == 'replies' && $sort_by_dir == 'asc')
            $html->setvar('sort_by_replies', '&sort_by=replies&sort_by_dir=desc');
        else
            $html->setvar('sort_by_replies', '&sort_by=replies&sort_by_dir=asc');

        if($sort_by == 'views' && $sort_by_dir == 'asc')
            $html->setvar('sort_by_views', '&sort_by=views&sort_by_dir=desc');
        else
            $html->setvar('sort_by_views', '&sort_by=views&sort_by_dir=asc');

        $n_topics = CForumTopic::count_by_forum_id($forum['id']);
        $n_per_page = $g["forum"]["n_topics_per_page"];
        $n_pages = ceil($n_topics / $n_per_page);

        $page = get_param("page", 1);
        $page = max($page, 1);
        $page = min($page, max($n_pages, 1));
        $html->setvar('page', $page);

        $topics = CForumTopic::select_by_forum_id($forum['id'], $n_per_page, ($page - 1) * $n_per_page, $sort_by, $sort_by_dir);
        if(count($topics))
        {
        	foreach($topics as $topic)
        	{
                $html->setblockvar('forum_topic_last_message', '');
                $html->setblockvar('forum_topic_last_message_none', '');

        		$html->setvar('topic_id', $topic['id']);
                $html->setvar('topic_title', $topic['title']);
                $html->setvar('topic_n_messages', $topic['n_messages']);
                $html->setvar('topic_n_views', $topic['n_views']);
                $html->setvar('topic_created_at', Common::dateFormat($topic['created_at'], 'forum_topic_created_at', false));

                $user = user_select_by_id($topic['user_id']);
                $html->setvar('topic_user_id', $user['user_id']);
                $html->setvar('topic_user_name', $user['name']);

                $last_message = CForumMessage::retrieve_last_updated_message_by_topic_id($topic['id']);
                if($last_message)
                {
	                $html->setvar('last_message_id', $last_message['id']);
	                $html->setvar('last_message_created_at', Common::dateFormat($last_message['created_at'], 'forum_last_message_created_at', false));

	                $user = user_select_by_id($last_message['user_id']);
	                $html->setvar('last_message_user_id', $user['user_id']);
	                $html->setvar('last_message_user_name', $user['name']);

	                $html->parse('forum_topic_last_message', false);
                }
                else
                {
                    $html->setvar('created_topic', $topic['created_at']);
                	$html->parse('forum_topic_last_message_none', false);
                }

                $html->parse('forum_topic', true);
        	}
        }
        else
        {
        	$html->parse('forum_topic_none', true);
        }

        if($n_pages > 1)
        {
	        $n_links = 5;
        	$links = array();
	        $tmp   = $page - floor($n_links / 2);
	        $check = $n_pages - $n_links + 1;
	        $limit = ($check > 0) ? $check : 1;
	        $begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

	        $i = $begin;
	        while (($i < $begin + $n_links) && ($i <= $n_pages))
	        {
	            $links[] = $i++;
	        }

	        if($page > 1)
	        {
                $html->setvar('link_page', $page - 1);
	        	$html->parse('page_navigator_prev', true);
	        }

            if($page < $n_pages)
            {
                $html->setvar('link_page', $page + 1);
            	$html->parse('page_navigator_next', true);
            }

            foreach($links as $link)
            {
                $html->setblockvar('page_navigator_item_current', '');
                $html->setblockvar('page_navigator_item_normal', '');

                $html->setvar('link_page', $link);

                if($link == $page)
                    $html->parse('page_navigator_item_current', false);
                else
                    $html->parse('page_navigator_item_normal', false);

                $html->parse('page_navigator_item', true);
            }

	        $html->parse('page_navigator', true);
        }

		parent::parseBlock($html);
	}
}

$forum_id = get_param("forum_id");

$forum = CForumForum::retrieve_by_id($forum_id);
$g['main']['title'] = $g['main']['title'] . ' :: ' . l('threads_in_forum') . ' : ' . $forum['title'];
$g['main']['description'] = $g['main']['title'];

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_forum.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
