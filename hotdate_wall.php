<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");

$hotdate_id = get_param('hotdate_id', '');
if(!$hotdate_id || !Common::isOptionActive('hotdate_wall_enabled')) {
    Common::toHomePage();
}
$sql = "SELECT * FROM `hotdates_hotdate` WHERE hotdate_id = " . to_sql($hotdate_id, 'Number') . " LIMIT 1";
$hotdate = DB::rows($sql);
if(!$hotdate) {
    Common::toHomePage();
}

if($hotdate) {
    set_session('site_section_item_id_ehp', $hotdate_id);
}

$page = Wall_Page::show();

include("./_include/core/main_close.php");