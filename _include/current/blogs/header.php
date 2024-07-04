<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CBlogsHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        if (param('q') != '') {
            $html->assign('topquery', CBlogsTools::filterSearchQuery(param('q')));
        } else {
            $html->assign('topquery', l('blogs_search_text'));
            $html->parse('cleanquery');
        }
        $html->assign('total_posts', CBlogsTools::getTotalPosts());
		parent::parseBlock($html);
	}
}
