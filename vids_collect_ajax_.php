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
    function parseBlock(&$html)
    {
        $limit = ipar('offset') . ',' . ipar('limit');
        switch (par('m')) {
            case 'my':
                $items = CVidsTools::getVideosByUser(guid(), $limit);
                break;
            case 'user':
                $items = CVidsTools::getVideosByUser(ipar('id'), $limit);
                break;
            case 'rates':
                $items = CVidsTools::getVideosRates($limit);
                break;
            case 'comments':
                $items = CVidsTools::getVideosComments($limit);
                break;
            case 'views':
                $items = CVidsTools::getVideosViews($limit);
                break;
            case 'featured':
                $items = CVidsTools::getVideosFeatured($limit);
                break;
            case 'subscripts':
                $items = CVidsTools::getVideosBySubscriptions($limit);
                break;
            case 'friends':
                $items = CVidsTools::getVideosByFriends($limit);
                break;
            case 'search':
                $q = CVidsTools::filterSearchQuery(param('id'));
                if(CVidsTools::countVideosByQuery($q)!=0)
                {
                    $items = CVidsTools::getVideosByQuery($q, $limit);
                } else {
                    $items = CVidsTools::getVideosByRand($limit);

                }
                break;
            default:
                $items = CVidsTools::getVideosNew($limit);
        }

        $html->items('item', $items, '', 'is_my' . (par('m') == 'subscripts' ? '|unsubscribe' : ''));
        parent::parseBlock($html);
    }
}

vids_render_page();
include('./_include/core/main_close.php');
