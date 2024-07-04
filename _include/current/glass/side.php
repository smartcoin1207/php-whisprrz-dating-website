<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CVidsSide extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;
		CBanner::getBlock($html, 'right_column');
        $curpage = curpage();

        if (1) {
            $side_comments = 1;
            $side_rates = 1;
        } else {
            $side_views = 1;
            $side_new = 1;
        }

        if (isset($side_views) and $side_views) {
            $items = CVidsTools::getVideosViews('0,2');
            $html->items('item_views', $items, '', 'is_my');
            $html->parse('side_views');
        }
        if (isset($side_rates) and $side_rates) {
            $items = CVidsTools::getVideosRates('0,2');
            $html->items('item_rates', $items, '', 'is_my');
            $html->parse('side_rates');
        }
        if (isset($side_comments) and $side_comments) {
            $items = CVidsTools::getVideosComments('0,2');
            $html->items('item_comments', $items, '', 'is_my');
            $html->parse('side_comments');
        }
        if (isset($side_new) and $side_new) {
            $items = CVidsTools::getVideosNew('0,2');
            $html->items('item_new', $items, '', 'is_my');
            $html->parse('side_new');
        }

        if (par('m') == 'search') {
            $html->assign('search_query', CVidsTools::filterSearchQuery(param('id')));
        }

		parent::parseBlock($html);
	}
}

