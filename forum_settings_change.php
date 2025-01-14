<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/forum.php");

$settings = CForum::settings();

$sort_by = get_param("sort_by", 'last_post');
$sort_by_dir = get_param("sort_by_dir", 'desc');

CForum::setting_set('sort_by', $sort_by);
CForum::setting_set('sort_by_dir', $sort_by_dir);

CForum::settings_save();

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "forum.php"; 
redirect($return_to);

include("./_include/core/main_close.php");
