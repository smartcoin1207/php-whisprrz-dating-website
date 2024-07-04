<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
if(empty($_GET['cmd']) || $_GET['cmd'] != 'lang')
    $area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

if (guid() && Common::getTmplName() == 'edge1') {
    // redirect(User::url(guid()));
}
if(guid() && Common::isOptionActive('seo_friendly_urls') && Common::getOptionSetTmpl() == 'urban' && empty($_REQUEST)) {
    // redirect(User::url(guid()));
}

$_GET['display'] = get_param('display', User::displayProfile());

$where = ' u.user_id = ' . to_sql(guid(), 'Number');
$order = '';
$page = Users_List::show($where, $order);

if (Common::isParseModule('complite')) {
    $complite = new CComplite('complite', $g['tmpl']['dir_tmpl_main'] . '_complite.html');
    $page->add($complite);
}
if (Common::isParseModule('profile_menu')) {
    $profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
    $profile_menu->setActive('view');
    $page->add($profile_menu);
}

include('./_include/core/main_close.php');