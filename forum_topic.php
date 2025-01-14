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

class CForumTopics extends CHtmlBlock {
	function action() {
		global $g;
		global $g_user;
		global $g_info;

		$message_id = get_param("message_id");
		if($message_id) {
			$topic_id = get_param("topic_id");

			$topic = CForumTopic::retrieve_by_id($topic_id);

			$messages = CForumMessage::select_by_topic_id($topic['id']);

			for($message_n = 0; $message_n != count($messages); ++$message_n) {
				if($messages[$message_n]['id'] == $message_id) {
					redirect('forum_topic.php?topic_id=' . $topic_id . '&page=' . (ceil($message_n / $g["forum"]["n_messages_per_page"]) + 1) . '#message_' . $message_id);
				}
			}

			redirect('forum_topic.php?topic_id=' . $topic_id);
		}
	}

	function parseBlock(&$html) {
		global $g;
		global $g_user;

		$topic_id = get_param("topic_id");
		CForumTopic::increment_n_views($topic_id);

		$topic = CForumTopic::retrieve_by_id($topic_id);

        if(!$topic) {
            redirect('forum.php');
        }

		$html->setvar('topic_id', $topic['id']);
		$html->setvar('topic_title', $topic['title']);
		$html->setvar('topic_message', CForum::make_smiley(CForumMessage::filter_text_to_html($topic['message'])));
		#$html->setvar('topic_message', to_html(CForumMessage::filter_text_to_html($topic['message'])));
		$html->setvar('topic_created_at',Common::dateFormat($topic['created_at'], 'forum_topic_created_at',false) );

		if($topic['user_id'] == guid()) {
			$html->parse('forum_topic_admin', false);
        } else {
            $html->setblockvar('forum_topic_admin', '');
        }

		$topic_user = user_select_by_id($topic['user_id']);
		$html->setvar('topic_user_id', $topic_user['user_id']);
		$html->setvar('topic_user_name', $topic_user['name']);
		$html->setvar('topic_user_register', Common::dateFormat($topic_user['register'], 'topic_user_register',false));
		$html->setvar('topic_user_n_messages', $topic_user['forum_n_messages']);

        $photo = User::getPhotoDefault($topic_user['user_id'], 'r');

		$html->setvar('topic_user_photo', $g['path']['url_files'] . $photo);

		$forum = CForumForum::retrieve_by_id($topic['forum_id']);

		$subforum = $forum;

		while($subforum) {
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

		$n_messages = CForumMessage::count_by_topic_id($topic['id']);
		$n_per_page = $g["forum"]["n_messages_per_page"];
		$n_pages = ceil($n_messages / $n_per_page);

		$page = get_param("page", 1);
		$page = max($page, 1);
		$page = min($page, max($n_pages, 1));
		$html->setvar('page', $page);

		$messages = CForumMessage::select_by_topic_id($topic['id'], $n_per_page, ($page - 1) * $n_per_page);

		$message_n = ($page - 1) * $n_per_page + 2;

		foreach($messages as $message) {
			$html->setvar('message_id', $message['id']);

			$html->setblockvar('forum_message_title', '');
			if($message['title']) {
				$html->setvar('message_title', $message['title']);
				$html->parse('forum_message_title', false);
			}

			$html->setvar('message_message', CForum::make_smiley(  CForumMessage::filter_text_to_html($message['message'])));


			$html->setvar('message_created_at', Common::dateFormat($message['created_at'], 'forum_message_created_at',false));

			$message_user = user_select_by_id($message['user_id']);
			$html->setvar('message_user_id', $message_user['user_id']);
			$html->setvar('message_user_name', $message_user['name']);
			$html->setvar('message_user_register', Common::dateFormat($message_user['register'], 'message_user_register',false));
			$html->setvar('message_user_n_messages', $message_user['forum_n_messages']);

            $photo = User::getPhotoDefault($message_user['user_id'], 'r');

			$html->setvar('message_user_photo', $g['path']['url_files'] . $photo);

			$html->setvar('message_n', $message_n);

			if($message['user_id'] == guid()) {
				$html->parse('forum_message_admin', false);
            } else {
                $html->setblockvar('forum_message_admin', '');
            }

			$html->parse('forum_message', true);

			++$message_n;
		}

		if($n_pages > 1) {
			$n_links = 5;
			$links = array();
			$tmp   = $page - floor($n_links / 2);
			$check = $n_pages - $n_links + 1;
			$limit = ($check > 0) ? $check : 1;
			$begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

			$i = $begin;
			while (($i < $begin + $n_links) && ($i <= $n_pages)) {
				$links[] = $i++;
			}

			if($page > 1) {
				$html->setvar('link_page', $page - 1);
				$html->parse('page_navigator_prev', true);
			}

			if($page < $n_pages) {
				$html->setvar('link_page', $page + 1);
				$html->parse('page_navigator_next', true);
			}

			foreach($links as $link) {
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

$topic_id = get_param("topic_id");

$topic = CForumTopic::retrieve_by_id($topic_id);
$g['main']['title'] = $g['main']['title'] . ' :: ' . l('forum') . ' : ' . $topic['title'];
$g['main']['description'] = $g['main']['title'];

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_topic.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
