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
        if ($cmd == 'update_collection') {
			$active = get_param('active');
			$order = get_param('order');
            foreach ($order as $pos => $id) {
				$data = array('position' => to_sql($pos, 'Number'),
							  'active' => isset($active[$id]) ? 1 : 0);
                DB::update('stickers_collections', $data, '`id` = ' . to_sql($id, 'Number'));
            }
            redirect('stickers_collections.php?action=saved');
        }
	}

	function parseBlock(&$html)
	{

		$listCollections = Common::getStickersCollections(true);
		if ($listCollections) {
			$num = 0;
			foreach ($listCollections as $item) {
				$num++;
				$html->setvar('num', $item['id']);
				$html->setvar('id', $item['id']);
				$html->setvar('img', $item['img']);
				$html->setvar('count', count($item['files']));
				$html->setvar('checked', $item['active'] ? 'checked' : '');
				$html->parse('collection_item', true);
			}
			$html->parse('collections');
		} else {
			$html->parse('no_collections', false);
		}

        parent::parseBlock($html);
	}
}

$page = new CAdminSticker("", $g['tmpl']['dir_tmpl_administration'] . "stickers_collections.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuStickers());

include("../_include/core/administration_close.php");