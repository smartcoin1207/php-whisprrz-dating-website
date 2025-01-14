<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminSticker extends CHtmlBlock
{

	var $message = "";

	function action()
	{
        global $g;
        global $p;

        $cmd = get_param('cmd');
        if ($cmd == 'update') {
			$col = get_param('col', 1);
			$active = get_param('active');
			$order = get_param('order');
            foreach ($order as $pos => $id) {
				$data = array('position' => to_sql($pos, 'Number'),
							  'active' => isset($active[$id]) ? 1 : 0);
                DB::update('stickers', $data, '`id` = ' . to_sql($id, 'Number'));
            }
            redirect('stickers_collections_sticker.php?action=saved&col=' . $col);
        }
	}

	function parseBlock(&$html)
	{

		$col = get_param('col', 1);
		$html->setvar('collection_param', $col);

		$listCollections = Common::getStickersCollections(true);
		$listCollectionsOpt = array();
		foreach ($listCollections as $item) {
			//$listCollectionsOpt[$item['id']] = lSetVars('collection_title', array('number' => $item['id']));
			$listCollectionsOpt[$item['id']] = $item['id'];
		}
		$html->setvar('collections_options', h_options($listCollectionsOpt, $col));

		if (isset($listCollections[$col])) {
			$listCollections = $listCollections[$col];
		} else {
			$listCollections = false;
		}

		if ($listCollections) {
			$files = $listCollections['files'];
			//print_r_pre($files, true);
			$num = 0;
			foreach ($files as $item) {
				$num++;
				$html->setvar('num', $item['id']);
				$html->setvar('id', $item['id']);
				$html->setvar('img', $item['src']);
				$html->setvar('checked', $item['active'] ? 'checked' : '');

				if ($item['animate']) {
					$html->parse('sticker_item_animate');
				}
				$html->parse('sticker_item', true);
			}
			$html->parse('stickers');
		} else {
			$html->parse('no_stickers', false);
		}

        parent::parseBlock($html);
	}
}

$page = new CAdminSticker("", $g['tmpl']['dir_tmpl_administration'] . "stickers_collections_sticker.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuStickers());

include("../_include/core/administration_close.php");