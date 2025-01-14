<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
include('./_include/current/vids/start.php');

class CPage extends CHtmlBlock
{
    function init()
    {
        global $g;
    }
    function parseBlock(&$html)
    {
        $myu = guser();
        if (guser('vid_videos') > 0) {
            $html->parse('my_vids');
        }
        if (count(CVidsTools::getSubscriptionsIds()) > 0) {
            $html->parse('my_subscriptions');
        }
        $itemvs = CVidsTools::getVideosViews('0,2');
		$html->items('itemv', $itemvs, '', 'is_my');
        CVidsTools::$numberTrim = 42;
        CVidsTools::$hardTrim = true;
        $items = CVidsTools::getVideosNew('0,2');
        
        $html->items('item', $items, '', 'is_my');
        parent::parseBlock($html);
    }
}

vids_render_page();
include('./_include/core/main_close.php');
