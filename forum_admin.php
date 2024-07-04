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
	var $thread_description_error = '';
	var $thread_message_error = '';

	function action()
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;

		$cmd = get_param("cmd");
		if($cmd == 'delete_message')
		{
	        $message_id = get_param("message_id");

	        $message = CForumMessage::retrieve_by_id($message_id);
	        if(!$message)
	            redirect("forum.php");

            if($message['user_id'] == guid()) {
                CForumMessage::delete_by_id($message_id);
            }

            redirect('forum_topic.php?topic_id=' . $message['topic_id']);
		}

        if($cmd == 'delete_topic')
        {
            $topic_id = get_param("topic_id");

            $topic = CForumTopic::retrieve_by_id($topic_id);
            if(!$topic)
                redirect("forum.php");

            if($topic['user_id'] == guid()) {
                CForumTopic::delete_by_id($topic_id);
            }

            redirect('forum_forum.php?forum_id=' . $topic['forum_id']);
        }

        if($cmd == 'delete_forum')
        {
            $forum_id = get_param("forum_id");

            $forum = CForumForum::retrieve_by_id($forum_id);
            if(!$forum)
                redirect("forum.php");

            if(user_has_role_admin_edit())
                CForumForum::delete_by_id($forum_id);

            redirect('forum.php');
        }

        if($cmd == 'delete_category')
        {
            $category_id = get_param("category_id");

            $category = CForumCategory::retrieve_by_id($category_id);
            if(!$category)
                redirect("forum.php");

            if(user_has_role_admin_edit())
                CForumCategory::delete_by_id($category_id);

            redirect('forum.php');
        }

        redirect("forum.php");
	}
}

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_admin.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
