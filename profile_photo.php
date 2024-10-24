<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/photolist.class.php");
include("./_include/current/menu_section.class.php");

//only auth user profile image 
$guid = guid();
$g_user = User::getInfoBasic($guid);
$_GET['name_seo'] = $g_user['name_seo'];
$_GET['profile_photo'] = 1;

$groupId = Groups::getParamId();

$optionTmplName = Common::getTmplName();
if ($optionTmplName != 'edge') {
    Common::toHomePage();
}

$isAjaxRequest = get_param('ajax');
$hideFromGuests = Common::isOptionActive('list_photos_hide_from_guests', "{$optionTmplName}_general_settings");
if (!guid() && !$isAjaxRequest) {
    $uid = User::getParamUid(0);
    if ($hideFromGuests || $uid) {
        Common::toLoginPage();
    }
}

Groups::checkAccessGroup();
User::accessCheckToProfile(true);

Groups::setTypeContentList();

$uid = get_param_int('uid');
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = array(
    'main'   => $dirTmpl . 'profile_photo.html',
    'list'   => $dirTmpl . '_list_page_info.html',
    'filter' => $dirTmpl . '_list_page_filter.html',
    'items'  => $dirTmpl . '_list_page_items.html',
    'item'   => $dirTmpl . '_list_photos_item.html',
    'pages'  => $dirTmpl . '_list_page_pages.html'
);
if ($uid) {
    unset($tmplList['filter']);
}
if ($isAjaxRequest) {
    $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
    unset($tmplList['filter']);
} elseif (TemplateEdge::isTemplateColums()) {
    if($groupId) {
        $tmplList['list'] = $dirTmpl . '_list_page_info_columns.html';
    } else {
        $tmplList['list'] = $dirTmpl . '_list_photo_info_columns.html';  // Divyesh - Added on 11-04-2024
    }
    $tmplList['profile_column_left'] = $dirTmpl . '_profile_column_left.html';
    $tmplList['profile_column_right'] = $dirTmpl . '_profile_column_right.html';
}
$page = new CPhotoList("", $tmplList);

if ($isAjaxRequest) {
    getResponsePageAjaxByAuthStop($page, $hideFromGuests ? guid() : 1);
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('photos');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");

?>
