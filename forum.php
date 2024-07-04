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

class CForumCategories extends CHtmlBlock
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

		$categories = CForumCategory::retrieve_all();

		foreach($categories as $category)
		{
			$forums = CForumForum::select_by_category_id($category['id']);
                $html->setblockvar('forum_forum', '');

                foreach($forums as $forum)
            {
                $last_topic = CForumTopic::retrieve_last_updated_topic_by_forum_id($forum['id']);

                $html->setblockvar('forum_last_post', '');
                $html->setblockvar('forum_last_post_none', '');

	            if($last_topic)
	            {
                    //print_r($last_topic);

	            	$html->setvar('topic_id', $last_topic['id']);
                    $html->setvar('topic_title', $last_topic['title']);
                    $html->setvar('topic_created_at', $last_topic['created_at']);

	            	$html->parse('forum_last_post', false);
	            }
            	else
            	{
            		$html->parse('forum_last_post_none', false);
            	}

            	$html->setvar('forum_id', $forum['id']);
	            $html->setvar('forum_title', l($forum['title'], false, 'forum_title'));
	            $html->setvar('forum_description',l($forum['description'], false, 'forum_description'));
	            $html->setvar('forum_n_topics', $forum['n_topics']);
	            $html->setvar('forum_n_messages', $forum['n_messages']);

	            $html->parse('forum_forum', true);
            }

            $html->setvar('category_id', $category['id']);
            $html->setvar('category_title', l($category['title'], false, 'forum_category_title'));

	        if(user_has_role_admin_view())
	            $html->parse('forum_category_admin', false);

            $html->parse('forum_category', true);
		}

		if(user_has_role_admin_view())
            $html->parse('admin_new_category', true);

		parent::parseBlock($html);
	}
}

$page = new CForumCategories("", $g['tmpl']['dir_tmpl_main'] . "forum.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
