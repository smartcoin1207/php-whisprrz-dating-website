<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
include('./_include/current/blogs/start.php');

class CPage extends CHtmlBlock
{
    public $user = null;
    function init()
    {
        if (intval(param('id')) > 0) {
            $this->user = user(param('id'));
            if (!is_array($this->user)) {
                $this->user = guser();
            }
        } else {
            $this->user = guser();
        }

        if ($this->user['user_id'] == 0) {
            Common::toLoginPage();
        }

        if ($this->user['user_id'] != guid()) {
            CBlogsTools::viewBlogByUserId($this->user['user_id']);
        }

        global $g;
        $g['main']['title'] = $g['main']['title'] . ' :: ' . lSetVars('user_blog', array('name' => $this->user['name']));

        $lastPost = CBlogsTools::getLastPostByUser($this->user['user_id']);
        $g['main']['description'] = htmlspecialchars(strip_tags($lastPost['text_short']));
    }
    function action()
    {
        if (intval(param('del')) > 0) {
            CBlogsTools::delPostById(param('del'));
        }
        if (intval(param('subscribe')) == '1') {
            CBlogsTools::addSubscription($this->user['user_id']);
        }
    }
    function parseBlock(&$html)
    {
        $html->assign('blogger', $this->user);
        $html->assign('blogger_photo', urphoto($this->user['user_id']));

        if ($this->user['user_id'] == guser('user_id')) {
            $html->assign('blog_title', l('My Blog'));
            $html->parse('write');
        } else {
            $html->assign('blog_title', lSetVars('user_blog', array('name' => $this->user['name'])));

            if (!CBlogsTools::isSubscrided(guser('user_id'), $this->user['user_id'])) {
                $html->parse('add_subscribe');
            }
            if (!isFriends(guser('user_id'), $this->user['user_id'])) {
                $html->parse('add_friend');
            }
        }

        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 10;
        $pagerUrl = g('path','url_main') . 'blogs_blog.php?id=' . $this->user['user_id'] . '&p=%s';

        $itemsTotal = CBlogsTools::countPostsByUser($this->user['user_id']);
        $items = CBlogsTools::getPostsByUser($this->user['user_id'], (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);

        if ($itemsTotal > 0) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            $html->assign('pager', $pager->getLiPages());

            $total = count($items);
            $i = 0;
            foreach ($items as $post) {
                $i++;
                $html->assign('post', $post);

                $html->subcond($i != $total, 'post_not_last');
                $html->subcond(guid() == $post['user_id'], 'post_edit');
                $html->subcond(!$post['text_is_short'], 'post_short_link');
                $html->subcond($post['subject'] != '', 'post_is_subject');

                $html->clean('post_comment');
                $html->items('post_comment', CBlogsTools::getCommentsByPostId($post['id'], true), '', 'is_my');

                $html->parse('post');
            }
            $html->parse('posts');
        } else {
            $html->parse('noposts');
        }
        parent::parseBlock($html);
    }
}

blogs_render_page();
include('./_include/core/main_close.php');
