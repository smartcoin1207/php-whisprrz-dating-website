<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/groups/tools.php");

$settings = CGroupsTools::settings();

$category_id = intval(get_param('category_id', $settings['category_id']));
CGroupsTools::setting_set('category_id', $category_id);

CGroupsTools::settings_save();

//$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "groups.php";
$return_to = "groups.php";
redirect($return_to);

include("./_include/core/main_close.php");
