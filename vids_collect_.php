<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
include('./_include/current/vids/start.php');

if(!guid() && get_param('m') == 'my') {
    Common::toLoginPage();
}

class CPage extends CHtmlBlock
{
    function init()
    {
        /*global $g;
        $g['main']['title'] = $g['main']['title'] . ' :: ' . l('Most Discussed Posts');
        $g['main']['description'] = l('Most Discussed Posts');*/
    }
    function action()
    {
        if (ipar('del') > 0) {
            CVidsTools::delVideoById(ipar('del'));
        }
    }
    function parseBlock(&$html)
    {
        $lUse = true;
        switch (par('m')) {
            case 'my':
                $title = 'My Videos';
                $total_vids = CVidsTools::countVideosByUser(guid());
                break;
            case 'user':
                if (ipar('id') == guid()) {
                    redirect('vids_collect.php?m=my');
                }
                $user = user(ipar('id'));
                $lUse = false;
                $title = lSetVars('user_name_videos', array('name' => $user['name']));
                $total_vids = CVidsTools::countVideosByUser($user['user_id']);
                break;
            case 'rates':
                $title = 'Top Rated';
                $total_vids = CVidsTools::countVideosRates();
                break;
            case 'comments':
                $title = 'Most Discussed';
                $total_vids = CVidsTools::countVideosComments();
                break;
            case 'views':
                $title = 'Top Plays';
                $total_vids = CVidsTools::countVideosViews();
                break;
            case 'featured':
                $title = 'Featured Videos';
                $total_vids = CVidsTools::countVideosFeatured();
                break;
            case 'subscripts':
                $title = 'Subcriptions';
                if (count(CVidsTools::getSubscriptionsIds()) == 0) {
                    redirect('vids.php');
                }
                $total_vids = CVidsTools::countVideosBySubscriptions();
                break;
            case 'friends':
                $title = 'Friends Videos';
                $total_vids = CVidsTools::countVideosByFriends();
                break;
            case 'search':
                $title = 'Search Results';
                $q = CVidsTools::filterSearchQuery(param('id'));
                $html->assign('query', $q);
                $total_vids = CVidsTools::countVideosByQuery($q);
                if($total_vids==0)
                {
                    $title = l('vids_nothing_found');
                    $total_vids = CVidsTools::countVideosByRand();
                }
                break;
            default:
                $title = 'New Videos';
                $total_vids = CVidsTools::countVideosNew();
        }

        $html->assign('collect_title', $lUse ? l($title) : $title);
        $html->assign('mod', par('m'));
        $html->assign('mod_id', urlencode(par('id')));
        $html->assign('on_page_vids', 5);
        $html->assign('total_vids', $total_vids);
        if ($total_vids > 5) $html->parse('pages');
        if ($total_vids > 0) $html->parse('items');

        parent::parseBlock($html);
    }
}
vids_render_page();

include('./_include/core/main_close.php');
