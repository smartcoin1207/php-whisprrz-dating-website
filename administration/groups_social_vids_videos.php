<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/vids/includes.php");

class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 20;
        $pagerUrl = g('path','url_administration') . 'groups_social_vids_videos.php?p=%s';

        CVidsTools::$admin = true;
        $itemsTotal = CVidsTools::countVideosAdmin('`group_id` != 0');
        $items = CVidsTools::getVideosAdmin('`group_id` != 0', (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);


        foreach ($items as $key => $value) {
            $items[$key]['user_name'] = strcut($value['user_name'], 48);
        }

        if ($itemsTotal > $pagerOnPage) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            $html->assign('pager', $pager->getAbPages());
            $html->assign('itemsTotal', $itemsTotal);
            $html->parse('pages');
        }

        $html->items('item', admin_color_lines($items), null, 'count_comments');
        $html->cond(count($items) > 0, 'items', 'noitems');

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "groups_social_vids_videos.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGroupsSocialVids());

include("../_include/core/administration_close.php");
