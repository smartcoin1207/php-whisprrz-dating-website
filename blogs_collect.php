<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include('./_include/core/main_start.php');
include('./_include/current/blogs/start.php');

class CPage extends CHtmlBlock
{
    public $type = null;
    function init()
    {
        $this->type = param('t');
        if (!in_array($this->type, array('s','f'))) {
            $this->type = 's';
        }
    }
    function action()
    {
        if (intval(param('unsubscribe')) > 0) {
            CBlogsTools::removeSubscription(intval(param('unsubscribe')));
        }
    }
    function parseBlock(&$html)
    {
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 10;
        $pagerUrl = g('path','url_main') . 'blogs_collect.php?t=' . $this->type . '&p=%s';

        switch ($this->type) {
            case 'f':
                $html->parse('friends');
                $itemsTotal = CBlogsTools::countPostsByFriends();
                $items = CBlogsTools::getPostsByFriends((($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);
                break;
            case 's':
            default:
                $html->parse('subscriptions');
                $itemsTotal = CBlogsTools::countPostsBySubscriptions();
                $items = CBlogsTools::getPostsBySubscriptions((($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);
                break;
        }
	    $html->assign('t', $this->type);

        $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
        $html->assign('pager', $pager->getLiPages());
        if ($pager->getTotalPage() > 1) {
            $html->parse('pager_block');
        }
        $total = count($items);
        $i = 0;
		foreach ($items as $post) {
			$i++;
			$html->assign('post', $post);

			$html->subcond($i != $total, 'post_not_last');
			$html->subcond(guser('user_id') == $post['user_id'], 'post_edit');
			$html->subcond(!$post['text_is_short'], 'post_short_link');
			$html->subcond($post['subject'] != '', 'post_is_subject');

            if ($this->type == 's') {
                $html->subparse('post_unsubscribe');
            }

            $html->clean('post_comment');
            $html->items('post_comment', CBlogsTools::getCommentsByPostId($post['id'], true), '', 'is_my');

			$html->parse('post');
		}

        parent::parseBlock($html);
    }
}

blogs_render_page();
include('./_include/core/main_close.php');
