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
	var $message_message_error = '';

	function action()
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;

		$cmd = get_param("cmd");
		if($cmd == 'post')
		{
	        $topic_id = get_param("topic_id");

	        $topic = CForumTopic::retrieve_by_id($topic_id);
            if(!$topic)
	            redirect("forum.php");

	        $title = get_param("message_title", "");
            $message = get_param("message_message", "");

	        if(trim($message) == '')
	        {
	            $this->error = true;
	            $this->message_message_error = l('required_field');
	        }

	        if(!$this->error)
	        {
	        	$message_id = CForumMessage::create_new($topic_id, $g_user['user_id'], $title, $message);
	        	CStatsTools::count('new_forum_posts');
                redirect('forum_topic.php?topic_id=' . $topic_id . '&message_id=' . $message_id);
	        }
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $message     = get_param("message_message", "");
        $title     = get_param("message_title", "");

        $html->setvar("message_message", he($message));
        $html->setvar("message_title", he($title));

        $html->setvar("message_message_error", $this->message_message_error);

        $topic_id = get_param("topic_id");

        $topic = CForumTopic::retrieve_by_id($topic_id);

        $html->setvar('topic_id', $topic['id']);
        $html->setvar('topic_title', $topic['title']);
        $html->setvar('topic_message', $topic['message']);
        $html->setvar('topic_created_at', $topic['created_at']);

        $topic_user = user_select_by_id($topic['user_id']);
        $html->setvar('topic_user_id', $topic_user['user_id']);
        $html->setvar('topic_user_name', $topic_user['name']);
        $html->setvar('topic_user_register', $topic_user['register']);

        $forum = CForumForum::retrieve_by_id($topic['forum_id']);

        $subforum = $forum;

        while($subforum)
        {
            $html->setvar('subforum_id', $subforum['id']);
            $html->setvar('subforum_title', l($subforum['title'], false, 'forum_title'));
            $html->parse('navbar_level', true);

            if($subforum['parent_forum_id'])
               $subforum = CForumForum::retrieve_by_id($subforum['parent_forum_id']);
            else
               break;
        }

        $category = CForumCategory::retrieve_by_id($subforum['category_id']);

        $html->setvar('category_id', $category['id']);
        $html->setvar('category_title', l($category['title'], false, 'forum_category_title'));


		parent::parseBlock($html);
	}
}

$g['main']['title'] = $g['main']['title'] . ' :: ' . l('post_new_message');
$g['main']['description'] = $g['main']['title'];

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_new_message.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
