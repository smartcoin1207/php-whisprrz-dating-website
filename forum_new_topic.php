<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/forum.php");

payment_check('forum');

class CForumTopics extends CHtmlBlock
{
	var $error = false;
	var $thread_title_error = '';
	var $thread_message_error = '';

	function action()
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;

		$cmd = get_param("cmd");
		if($cmd == 'post')
		{
	        $forum_id = get_param("forum_id");

	        $forum = CForumForum::retrieve_by_id($forum_id);
	        if(!$forum)
	            redirect("forum.php");

	        $title       = get_param("thread_title", "");
	        $message     = get_param("thread_message", "");

	        if(trim($title) == '')
	        {
	        	$this->error = true;
	        	$this->thread_title_error = l('required_field');
	        }

	        if(trim($message) == '')
	        {
	            $this->error = true;
	            $this->thread_message_error = l('required_field');
	        }

	        if(!$this->error)
	        {
	        	$topic_id = CForumTopic::create_new($forum_id, $g_user['user_id'], $title,  $message);
	        	redirect('forum_topic.php?topic_id=' . $topic_id);
	        }
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $title       = get_param("thread_title", "");
        $message     = get_param("thread_message", "");

        $html->setvar("thread_title", htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));
        $html->setvar("thread_message", htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

        $html->setvar("thread_title_error", $this->thread_title_error);
        $html->setvar("thread_message_error", $this->thread_message_error);

		$forum_id = get_param("forum_id");

		$forum = CForumForum::retrieve_by_id($forum_id);

        $html->setvar('forum_id', $forum['id']);
        $html->setvar('forum_title', l($forum['title'], false, 'forum_title'));

        $subforum = $forum;

        while($subforum['parent_forum_id'])
        {
        	$subforum = CForumForum::retrieve_by_id($subforum['parent_forum_id']);
        }

        $category = CForumCategory::retrieve_by_id($subforum['category_id']);

        $html->setvar('category_id', $category['id']);
        $html->setvar('category_title', l($category['title'], false, 'forum_category_title'));


		parent::parseBlock($html);
	}
}

$g['main']['title'] = $g['main']['title'] . ' :: ' . l('post_new_thread');
$g['main']['description'] = $g['main']['title'];

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_new_topic.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
