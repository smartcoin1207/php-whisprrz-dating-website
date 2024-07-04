<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$p = 'blogs.php';

include_once(dirname(__FILE__) . '/start.php');

class CPage extends CHtmlBlock
{
    function init()
    {
        global $g;
        $g['main']['title'] = $g['main']['title'] . ' :: ' . lr('blogs');
        $g['main']['description'] = $g['main']['title'] . ' :: ' . lr('blogs');
    }
    function parseBlock(&$html)
    {
        $html->items('pop', CBlogsTools::getPopularBloggers());
        $html->items('discuss', CBlogsTools::getDiscussed());
        parent::parseBlock($html);
    }
}

blogs_render_page();