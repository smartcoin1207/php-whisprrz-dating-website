<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CMobileUserMenu extends CHtmlBlock {

    function action() {
        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $typeMenu = Common::getOption('user_menu_type', 'template_options_mobile');
            $section = 'mobile_user_menu';
            if ($typeMenu) {
                $section .= '_' . $typeMenu;
            }
            $order = get_param('order');
            $status = get_param('order_status');
            $additional = get_param('additional');
            foreach ($order as $key => $item) {
                if(empty($status[$item])) {
                    $stat = 'N';
                } else {
                    $stat = 'Y';
                }
                $where = '';
                if ($typeMenu == 'impact') {
                    $add = intval(isset($additional[$item]));
                    $where = ',`additional` = ' . to_sql($add);
                }
                $sql = "UPDATE `col_order`
                           SET `position` = " . to_sql($key) . ",
                               `status` = " . to_sql($stat) .
                                $where . "
                         WHERE `name` = " . to_sql($item) .
                         ' AND `section` = ' . to_sql($section);
                DB::execute($sql);
            }

            $setHomePageMobile = get_param('set_home_page_mobile');
            Config::update('options', 'set_home_page_mobile', $setHomePageMobile, true);

            global $p;
            redirect($p."?action=saved");
            redirect();
        }
    }

    function parseBlock(&$html) {
        $where = '';

        $typeMenu = Common::getOption('user_menu_type', 'template_options_mobile');
        $section = 'mobile_user_menu';
        $prf = '';
        if ($typeMenu) {
            $section .= '_' . $typeMenu;
            $prf .= '_' . $typeMenu;
        }

        if (!Common::isOptionActive('photo_rating_enabled')) {
            $where = "AND `name` != 'photo_rating'";
        }
        if (!Common::getOption('user_menu_no_profile', 'template_options_mobile')) {
            $html->parse('order_profile', false);
        }
        if ($typeMenu == 'impact') {
            $html->parse('additional_menu', false);
        }

        DB::query("SELECT * FROM `col_order` WHERE `section` = '{$section}' {$where} ORDER BY position");
        $lang = loadLanguageAdminMobile();
        while ($row = DB::fetch_row()) {
            if (in_array($row['name'], array('3d_city', 'street_chat', 'game_choose')) && !Common::isModuleCityActive()) {
                continue;
            }
            $html->setvar('name_block', l('user_menu_' . $row['name'] . $prf, $lang));
            $html->setvar('name_block_field', $row['name']);
            if ($row['status'] == 'Y'){
                $html->setvar('checked', 'checked');
            } else {
                $html->setvar('checked', '');
            }
            $checked = Common::getOption('set_home_page_mobile');
            $html->setvar('current_checked', $checked);
            if ($row['name'] == 'logout' || $row['name'] == 'verify_account') {
                $html->clean('order_item_set_home_page');
            } else {
                $html->parse('order_item_set_home_page', false);
            }
            if ($typeMenu == 'impact') {
                $html->setvar('additional_checked', $row['additional'] ? 'checked' : '');
                $html->parse('additional_menu_item', false);
            }
            $html->parse('order_item');
        }
        parent::parseBlock($html);
    }

}

$page = new CMobileUserMenu("", $g['tmpl']['dir_tmpl_administration'] . "user_menu_order.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuOptions());

include("../_include/core/administration_close.php");