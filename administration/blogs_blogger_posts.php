<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/blogs/includes.php");

class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $uid = ipar('id');
        if ($uid > 0) {
            $user = user($uid);
        }
        if (!isset($user) or !is_array($user)) {
            redirect('blogs_posts.php');
        }


        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 20;
        $pagerUrl = g('path','url_administration') . 'blogs_blogger_posts.php?id=' . $uid . '&p=%s';

        $itemsTotal = CBlogsTools::countPostsByUser($uid);
        $items = CBlogsTools::getPostsByUser($uid, (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);

        if ($itemsTotal > $pagerOnPage) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            if ($html->varExists('pager_li_modern')) {
				$html->assign('pager_li_modern', $pager->getLiPagesModern());
			} else {
				$html->assign('pager', $pager->getAbPages());
			}
            $html->assign('itemsTotal', $itemsTotal);
            $html->parse('pages');
        }

        $html->assign('blogger', $user);
        $html->items('item', admin_color_lines($items), null, 'count_comments');
        $html->cond(count($items) > 0, 'items', 'noitems');

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "blogs_blogger_posts.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuBlogs());

include("../_include/core/administration_close.php");
