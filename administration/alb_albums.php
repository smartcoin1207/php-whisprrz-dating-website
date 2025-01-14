<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
include("../_include/current/pager.php");
class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $pid = ipar('id');
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 20;
        if ($pid > 0){
            $pagerUrl = g('path','url_administration') . 'alb_albums.php?id='.$pid.'&p=%s';
        } else {
            $pagerUrl = g('path','url_administration') . 'alb_albums.php?p=%s';
        }
        if ($pid > 0){
            $itemsTotal = DB::count('gallery_albums','`user_id` = '.to_sql($pid,'Number').'');
        } else {
            $itemsTotal = DB::count('gallery_albums');
        }

        $items = array();
        if ($page == 1){
            if ($pid > 0){
                $items = DB::all("SELECT id,title,user_id FROM gallery_albums WHERE `user_id` = ". to_sql($pid,"Number") ." LIMIT ". to_sql($pagerOnPage,"Number") . ";");
            } else {
                $items = DB::all("SELECT id,title,user_id FROM gallery_albums LIMIT ". to_sql($pagerOnPage,"Number") . ";");
            }
        }  else {
            if ($pid > 0){
                $items = DB::all("SELECT id,title,user_id FROM gallery_albums WHERE `user_id` = ". to_sql($pid,"Number") ." LIMIT ". to_sql(($page - 1)*$pagerOnPage,"Number") . ",". to_sql($pagerOnPage,"Number") . ";");
            } else {
                $items = DB::all("SELECT id,title,user_id FROM gallery_albums LIMIT ". to_sql(($page - 1)*$pagerOnPage,"Number") . ",". to_sql($pagerOnPage,"Number") . ";");
            }
        }

        for ($i=0; $i < count($items); $i++) {
            $items[$i]['user_name'] = DB::row("SELECT name FROM user WHERE `user_id` = ". to_sql($items[$i]['user_id'],"Number") . ";");
            $items[$i]['user_name'] = $items[$i]['user_name'][0];
        }

       if ($itemsTotal > $pagerOnPage) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            if ($html->varExists('pager_li_modern')) {
				$html->assign('pager_li_modern', $pager->getLiPagesModern());
			} else {
				$html->assign('pager', $pager->getAbPages());
			}
            $html->assign('itemsTotal', $itemsTotal);
            $html->parse('pages');
        }
        $html->items('item', admin_color_lines($items), null);
        $html->cond(count($items) > 0, 'items', 'noitems');

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "alb_albums.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageAlbum());

include("../_include/core/administration_close.php");