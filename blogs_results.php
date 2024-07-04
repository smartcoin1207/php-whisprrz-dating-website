<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

if(get_param('search_header') == 1) {
    $query = trim(get_param('q'));
    if($query != '') {
        $sql = 'SELECT name FROM user
            WHERE name = ' . to_sql($query, 'Text') . '
                OR mail = ' . to_sql($query, 'Text') . '
            LIMIT 1';
        $name = DB::result($sql);
        if($name) {
            redirect('search_results.php?display=profile&name=' . $name);
        }
    }
}

include('./_include/current/blogs/start.php');

CStatsTools::count('blog_search_used');

class CPage extends CHtmlBlock
{
    function init()
    {
        $q = CBlogsTools::filterSearchQuery(param('q'));

        global $g;
        $g['main']['title'] = $g['main']['title'] . ' :: ' . l('Search results for "') . $q . '"';
        $g['main']['description'] = l('Search results for ') . $q;
    }
    function parseBlock(&$html)
    {
        $q = CBlogsTools::filterSearchQuery(param('q'));
        $html->assign('query', $q);

        if(trim($q)) {
            $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
            $pagerOnPage = 15;
            // decode non-latin symbols because "%" is format symbol
            $pagerUrl = g('path','url_main') . 'blogs_results.php?q=' . urldecode($q) . '&p=%s';

            $itemsTotal = CBlogsTools::countPostsByQuery(param('q'));
            $items = CBlogsTools::getPostsByQuery(param('q'), (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);
        }

        if (isset($items) && count($items) > 0) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            $html->assign('pager', $pager->getLiPages());
            $html->items('postf', $items);
            $html->parse('postsfound');
        } else {
            $html->items('post', CBlogsTools::getPostsByRand('0,10'));
            $html->parse('postsnotfound');
        }
        parent::parseBlock($html);
    }
}

blogs_render_page();
include('./_include/core/main_close.php');
