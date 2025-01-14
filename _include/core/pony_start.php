<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = '../';
include("../_include/core/starter.php");

$g['mobile'] = true;
$g['path']['url_main'] = './';

$sitePart = 'mobile';

include(dirname(__FILE__) . '/../../../_include/core/start.php');
include(dirname(__FILE__) . '/../../../_include/core/main_auth.php');
$mobile = countFrameworks('mobile');
if(!$mobile || !Common::isOptionActive('mobile_enabled')) {
    redirect('../');
}
Social::init();

$sections = array(
    'comment',
    'status',
    'photo_comment',
    'photo_default',
    'field_status',
    'pics',
    'photo',
    'music_photo',
    'musician_photo',
    'event_photo',
    'places_photo',
    'friends',
);
Wall::setSiteSectionsOnly($sections);
$sections = array(
    'photo',
    'interests'
);
Wall::setSectionsHidden($sections);
Wall::setIsMobile(true);

VideoHosts::setMobile(true);

$g['options']['fast_join'] = 'Y';

$g['path']['url_tmpl'] = '../_frameworks/';
$g['path']['url_files'] = "../{$dirFiles}";

$g['tmpl']['url_tmpl_mobile'] = $g['path']['url_tmpl'] . 'mobile/' . $g['tmpl']['tmpl_loaded'] . '/';
$g['tmpl']['url_tmpl_common_apps'] = "{$g['path']['url_tmpl']}common/apps/";
$g['path']['url_city'] = "../_server/city_js/";

if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/WebKit/",$_SERVER['HTTP_USER_AGENT'])) {
    $g['to_head'][] = "\n" . '<link rel="stylesheet" href="'.$g['tmpl']['url_tmpl_mobile'].'css/webkit.css" type="text/css" media="all"/>';
}

include(dirname(__FILE__) . '/../current/mobile_common.php');
include(dirname(__FILE__) . '/../current/user_menu.php');