<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$optionTmplName = Common::getOption('name', 'template_options');
if ($optionTmplName == 'impact') {
    $l['all']['profile_col_narrow'] = $l['all']['impact_left_column'];
    $l['profile_col_narrow.php']['title_current'] = $l['all']['impact_left_column'];
}

class CProfileColumnNarrow extends CHtmlBlock {

    function action() {
        global $p;

        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $order = get_param('order');
            $status = get_param('order_status');
            foreach ($order as $key => $item) {
                if(empty($status[$item])) {
                    $stat = 'N';
                } else {
                    $stat = 'Y';
                }
               DB::execute("UPDATE `col_order` SET `position`=".to_sql($key).",`status`='".$stat."' WHERE name=".to_sql($item));
            }
            redirect($p. '?action=saved');
        }
    }

    function parseBlock(&$html) {
        $section = 'narrow';
        $where = '';
        $optionTmplName = Common::getOption('name', 'template_options');
        if ($optionTmplName == 'impact') {
            $section = 'impact_left_column';
        } else {
            if (!Common::isOptionActive('photo_rating_enabled')) {
                $where = "AND `name` != 'rating_photos'";
            }
        }

        DB::query("SELECT * FROM `col_order` WHERE `section` = '{$section}' {$where} ORDER BY position");
        while ($row = DB::fetch_row()) {
            $html->setvar('name_block', l($row['name']));
            $html->setvar('name_block_field', $row['name']);
            if ($row['status'] == 'Y')
                $html->setvar('checked', 'checked');
            else
                $html->setvar('checked', '');
            $html->parse('order_item');
        }
        parent::parseBlock($html);
    }

}

$page = new CProfileColumnNarrow("", $g['tmpl']['dir_tmpl_administration'] . "profile_col_narrow.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuOptions());

include("../_include/core/administration_close.php");