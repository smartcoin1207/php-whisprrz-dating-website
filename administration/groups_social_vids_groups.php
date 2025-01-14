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
    static public function countGroups()
	{
        $sql = 'SELECT group_id FROM `vids_comment`
                     WHERE `group_id` > 0
                     GROUP BY `group_id`';

        return count(DB::rows($sql));
    }

	static public function getGroups($limit = '0,6')
	{

        $sql = 'SELECT group_id FROM
               (SELECT * FROM `vids_video`
                 WHERE `group_id` > 0
                 GROUP BY `group_id` ORDER BY dt DESC, id DESC ) AS VV LIMIT ' . $limit;

        $pops = DB::rows($sql);
        foreach ($pops as $k => &$v) {
            $groupInfo = Groups::getInfoBasic($v['group_id']);
            $v['name'] = $groupInfo['title'];
            $v['group_id'] = $v['group_id'];
            $v['type'] = $groupInfo['page'] ? l('group_type_page') : l('group_type_group');


            $where = 'group_id =' . to_sql($v['group_id']);
            $v['vid_videos'] = DB::count('vids_video', $where);

            $sql = 'SELECT SUM(count_views) AS count FROM vids_video WHERE ' . $where;
            $v['vid_visits'] = DB::result($sql, 0, 2);

            $v['vid_comments'] = DB::count('vids_comment', $where);
        }
        return $pops;
    }

	function parseBlock(&$html)
	{
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 20;
        $pagerUrl = g('path','url_administration') . 'groups_social_vids_groups.php?p=%s';

        $itemsTotal = self::countGroups();
        $items = self::getGroups((($page - 1) * $pagerOnPage) . ',' . $pagerOnPage);

        if ($itemsTotal > $pagerOnPage) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            $html->assign('pager', $pager->getAbPages());
            $html->assign('itemsTotal', $itemsTotal);
            $html->parse('pages');
        }

        $html->items('item', admin_color_lines($items));
        $html->cond(count($items) > 0, 'items', 'noitems');

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "groups_social_vids_groups.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGroupsSocialVids());

include("../_include/core/administration_close.php");
