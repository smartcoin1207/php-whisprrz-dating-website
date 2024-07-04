<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/hotdates/hotdate_comment_list.php");

$page = new CHotdatesHotdateCommentList("", $g['tmpl']['dir_tmpl_main'] . "_hotdates_hotdate_comment_list.html");
$page->m_need_container = false;

include("./_include/core/main_close.php");
