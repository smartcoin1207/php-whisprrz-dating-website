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
    function init()
    {
        global $g;
        $g['main']['title'] = $g['main']['title'] . ' :: ' . l('Most Popular Bloggers');
        $g['main']['description'] = l('Most Popular Bloggers');
    }
    function parseBlock(&$html)
    {
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 15;
        $pagerUrl = g('path','url_main') . 'blogs_popular.php?p=%s';

        $itemsTotal = CBlogsTools::countPopularBloggers();
        $items = CBlogsTools::getPopularBloggers((($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);

        $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
        $html->assign('pager', $pager->getLiPages());

        $html->items('pop', $items);
        parent::parseBlock($html);
    }
}

blogs_render_page();
include('./_include/core/main_close.php');
