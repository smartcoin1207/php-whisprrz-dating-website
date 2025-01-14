<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

// DB settings - user unique for every site to prevent MySQL attacks on all domains via one site
$domainSettingsFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $domain . '.php';
if(file_exists($domainSettingsFile)) {
    include($domainSettingsFile);
    if(isset($g['redirect'])) {
        redirect($g['redirect']);
    }
} else {
    #$toMainSite = 'URL';
    #redirect($toMainSite);
}