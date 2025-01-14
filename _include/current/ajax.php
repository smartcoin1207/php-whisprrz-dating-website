<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include_once($g['path']['dir_main'] . "_include/current/xajax.inc.php");
$xajax = new xajax($g['path']['url_main'] . "_server/server.php");


$xajax->registerFunction("init_client");

$xajax->registerFunction("countries");
$xajax->registerFunction("states");
$xajax->registerFunction("cities");

$xajax->registerFunction("update");

$xajax->registerFunction("im");
$xajax->registerFunction("im_open_new");
$xajax->registerFunction("im_sent");
$xajax->registerFunction("im_close");
$xajax->registerFunction("im_save_position");
$xajax->registerFunction("im_save_position_input");
$xajax->registerFunction("read_msg");
$xajax->registerFunction("is_writing");

#$xajax->registerFunction("im_update");

#$xajax->registerFunction("game_update");
$xajax->registerFunction("game_invite");
$xajax->registerFunction("game_reject");
$xajax->registerFunction("game_go");

#$xajax->registerFunction("video_update");
$xajax->registerFunction("video_invite");
$xajax->registerFunction("video_reject");
$xajax->registerFunction("video_go");

#$xajax->registerFunction("audio_update");
$xajax->registerFunction("audio_invite");
$xajax->registerFunction("audio_reject");
$xajax->registerFunction("audio_go");

$xajax->registerFunction("saveAlbumTitle");
$xajax->registerFunction("saveAlbumDesc");
$xajax->registerFunction("saveImageTitle");
$xajax->registerFunction("saveImageDesc");

$xajax->registerFunction("check_captcha");
$xajax->registerFunction("check_captcha_mod");
$xajax->registerFunction("sound");

$xajax->registerFunction("widget_save");
$xajax->registerFunction("widget_show");
$xajax->registerFunction("widget_close");
$xajax->registerFunction("widget_site");
$xajax->registerFunction("widget_home");
$xajax->registerFunction("widget_up");
$xajax->registerFunction("widget_update");
$xajax->registerFunction("widget_calendar_shift");
$xajax->registerFunction("widget_friends_scroll");
$xajax->registerFunction("widget");

$xajax->registerFunction("profile_status");
$xajax->registerFunction("photo_default");
$xajax->registerFunction("photo_private");
$xajax->registerFunction("profile_photo_save_title");
$xajax->registerFunction("profile_photo_save_desc");

$xajax->registerFunction("admin_group_member");
$xajax->registerFunction("update_info_user");
$xajax->registerFunction("update_site_title");
$xajax->registerFunction("unset_window_active");

$xajax->registerFunction("uploading_msg");

// nudity image filter checkbox
$xajax->registerFunction("nd_filter_change");
