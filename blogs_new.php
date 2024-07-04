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
        $g['main']['title'] = $g['main']['title'] . ' :: ' . l('New Blog Posts');
        $g['main']['description'] = l('New Blog Posts');
    }
    function parseBlock(&$html)
    {
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 15;
        $pagerUrl = g('path','url_main') . 'blogs_new.php?p=%s';

        $itemsTotal = CBlogsTools::countNew();
        $items = CBlogsTools::getNew((($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);

        $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
        $html->assign('pager', $pager->getLiPages());

        $html->items('post', $items);
        parent::parseBlock($html);
    }
}

blogs_render_page();
include('./_include/core/main_close.php');
