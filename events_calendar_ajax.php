<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/events/calendar.php");

$tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar.html";
if (Common::isOptionActiveTemplate('event_social_enabled')) {
    $tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar_items.html";
}
$page = new CEventsCalendar("", $tmpl);
$page->m_need_container = false;

loadPageContentAjax($page);

include("./_include/core/main_close.php");