<?php

include("./_include/core/main_start.php");

$pageFrom = get_param('page_from');
if($pageFrom) {
    set_session('social_login_page_from', $pageFrom);
}

$redirect = get_param('redirect');
if($redirect) {
    redirect($redirect);
}

$nameSocial = get_param('module', '');

if (method_exists($nameSocial, 'oAuthApi')) {
    $social = $nameSocial::getInstance();
    if(!$social) {
        Common::toLoginPage();
    }
    Social::checkParams($social);
    $social->oAuthApi();
}