<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");

$partyhou_id = get_param('partyhou_id', '');
if(!$partyhou_id || !Common::isOptionActive('partyhou_wall_enabled')) {
    Common::toHomePage();
}
$sql = "SELECT * FROM `partyhouz_partyhou` WHERE partyhou_id = " . to_sql($partyhou_id, 'Number') . " LIMIT 1";
$partyhou = DB::rows($sql);
if(!$partyhou) {
    Common::toHomePage();
}

if($partyhou) {
    set_session('site_section_item_id_ehp', $partyhou_id);
}

$page = Wall_Page::show();

include("./_include/core/main_close.php");