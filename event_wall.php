<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");

$event_id = get_param('event_id', '');

if(!$event_id || !Common::isOptionActive('event_wall_enabled')) {
    Common::toHomePage();
}
$sql = "SELECT * FROM `events_event` WHERE event_id = " . to_sql($event_id, 'Number') . " LIMIT 1";
$event = DB::rows($sql);

//popcorn modified 2024-05-28
$guest_sql = "SELECT * FROM `events_event_guest` WHERE event_id = " . to_sql($event_id, 'Number') . " AND user_id = "  . to_sql(guid(), 'Number') . " LIMIT 1";
$guest = DB::row($guest_sql);

if(!$event || !$guest) {
    Common::toHomePage();
}

if($event) {
    set_session('site_section_item_id_ehp', $event_id);
}

$page = Wall_Page::show();

include("./_include/core/main_close.php");