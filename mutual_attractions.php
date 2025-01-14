<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

$isAjaxRequest = get_param('ajax', 0);
$cmd = get_param('cmd');

if (($cmd == 'want_to_meet_you' || $cmd == 'who_likes_you')
        && !User::accessCheckFeatureSuperPowers('encounters')) {
    redirect('upgrade.php');
}

CustomPage::setSelectedMenuItemInMutualAttractions($cmd);
MutualAttractions::setViewedNewItems();

$optionTmplName = Common::getOption('name', 'template_options');
if ($optionTmplName == 'impact') {
    if($isAjaxRequest) {
        $listTmpl = array('main' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base_items.html',
                          'item_charts' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base_item_charts.html'
                    );
    } else {
        $listTmpl = array('main' => $g['tmpl']['dir_tmpl_main'] . 'mutual_attractions.html',
                          'items' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base_items.html',
                          'item_charts' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base_item_charts.html'
                    );
    }
} else {
    $tmpl = ($cmd == 'want_to_meet_you') ? '_want_to_meet_you_items.html' : '_mutual_attractions_items.html';
    if($isAjaxRequest) {
        $listTmpl = $g['tmpl']['dir_tmpl_main'] . $tmpl;
    } else {
        $listTmpl = array('main' => $g['tmpl']['dir_tmpl_main'] . 'mutual_attractions.html',
                          'users_list' => $g['tmpl']['dir_tmpl_main'] . $tmpl,
                    );
    }
}
//var_dump_pre($listTmpl);
//die();
$page = new MutualAttractions('', $listTmpl);
if($isAjaxRequest) {
    if ($cmd == 'delete_mutual_user') {
        die(getResponseDataAjaxByAuth(MutualAttractions::unlike()));
    } elseif ($cmd == 'set_want_to_meet') {
        die(getResponseDataAjaxByAuth(MutualAttractions::setWantToMeet()));
    } else {
        stopScript(getResponsePageAjaxAuth($page));
    }
}

$header = new CHeader('header', $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
if (Common::isParseModule('profile_colum_narrow')){
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
}
$footer = new CFooter('footer', $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");