<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/music/tools.php");

$settings = CMusicTools::settings();

$category_id = intval(get_param('category_id', $settings['category_id']));
CMusicTools::setting_set('category_id', $category_id);

$limit = get_param('limit', $settings['setting_limit']);
CMusicTools::setting_set('setting_limit', $limit);

CMusicTools::settings_save();

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "music.php";
redirect($return_to);

include("./_include/core/main_close.php");
