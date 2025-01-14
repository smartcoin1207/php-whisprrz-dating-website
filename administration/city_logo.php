<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminCityLogo extends CAdminOptions{

    function init() {
		global $p;
		$block = array();
		$parts = array();
		$blockTitle = array();
		$rooms = DB::select('city_rooms', '`hide` = 0');
		foreach ($rooms as $room) {
			$block["logo_location_{$room['id']}"] = 1;
			$parts["logo_location_{$room['id']}"] = '';
			$blockTitle["logo_location_{$room['id']}"] = $room['name'];
		}
        $this->setBlock($block);
		$this->setParts($parts);
		$this->setTitleBlock($blockTitle);
		$p = 'city.php';
		$this->setLang(loadLanguageAdmin());
        parent::init();
    }

    function action()
    {
        global $g;

		$cmd = get_param('cmd');
		if($cmd && !isset($_FILES['logo'])){
			$block = get_param('block');
			$this->updateParamLogoCity($block);
		}
        parent::action();
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;

        parent::parseBlock($html);
    }

}

$page = new CAdminCityLogo("", $g['tmpl']['dir_tmpl_administration'] . "city_logo.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include("../_include/core/administration_close.php");