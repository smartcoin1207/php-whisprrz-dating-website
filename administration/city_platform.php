<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminCityPlatforms extends CHtmlBlock {
    function action() {
        global $g;
        $cmd = get_param('cmd');
		parent::action();
        if($cmd == 'update_platform') {
            $platforms = get_param_array('platform');
            $activated = get_param_array('activated');
            $activatedPl = array();
            foreach ($platforms as $id => $value) {
                $activatedPl[$id] = intval(isset($activated[$id]));
            }
			Config::update('3d_city_platform', 'max_number', json_encode($platforms));
            Config::update('3d_city_platform', 'activated', json_encode($activatedPl));
            redirect('city_platform.php?action=saved');
        }
    }

    function parseBlock(&$html) {
        global $g;

        $platforms = Common::getOption('max_number', '3d_city_platform');
        if ($platforms) {
			$platforms = json_decode($platforms, true);
            $activated = json_decode(Common::getOption('activated', '3d_city_platform'), true);

            foreach ($platforms as $id => $num) {
                $html->setvar('id', $id);
				$html->setvar('label', l($id));
				$html->setvar('num', $num);
                $html->setvar('checked', !isset($activated[$id]) || $activated[$id] ? 'checked' : '');
				$html->parse('item', true);
            }
        }
        parent::parseBlock($html);
    }

}

$page = new CAdminCityPlatforms('', $g['tmpl']['dir_tmpl_administration'] . 'city_platform.html');

$items = new CAdminConfig('config_fields', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
$items->setModule('3d_city_street_chat');
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include("../_include/core/administration_close.php");