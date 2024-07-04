<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CBlogsSide extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $pop_on = 0;
        $new_on = 1;
        $hts_on = 2;
        $pages = array(
            'blogs'              => array(0,1,1),
            'blogs_write'        => array(1,0,1),
            'blogs_post'         => array(0,1,1),
            'blogs_blog'         => array(0,1,1),

            'blogs_popular'      => array(0,1,1),
            'blogs_discussed'    => array(0,1,1),
            'blogs_new'          => array(1,0,1),

            'blogs_collect'      => array(0,1,1),

            'blogs_hot_searches' => array(1,1,0),
            'blogs_results'      => array(0,1,1),
        );

        $curpage = curpage();
        if($curpage == 'index') {
            $curpage = 'blogs';
        }
        if (isset($pages[$curpage][$pop_on]) and $pages[$curpage][$pop_on] == 1) {
            $html->items('sidepop', CBlogsTools::getPopularBloggers());
            $html->parse('popular_blogs');
        }
        if (isset($pages[$curpage][$new_on]) and $pages[$curpage][$new_on] == 1) {
            $html->items('sidenew', CBlogsTools::getNew());
            $html->parse('new_blogs');
        }
        if (isset($pages[$curpage][$hts_on]) and $pages[$curpage][$hts_on] == 1) {
            $html->items('hs', CBlogsTools::getHotSearches());
            $html->parse('hot_searches');
        }
        if ($curpage == 'blogs_post') {
            $state = User::isNarrowBox('blogs');
            $html->setvar('display', ($state) ? 'table-cell' : 'none');
        } else {
            $html->setvar('display', 'table-cell');
        }

		parent::parseBlock($html);
	}
}

