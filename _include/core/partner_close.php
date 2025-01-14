<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

if (isset($page)) {
	$page->init();
	$page->action();
	$tmp = null;
	$page->parse($tmp);
}

if(USE_FILES_CACHE) {
    $cacheInitSer = serialize($g['cache_init']);
    $cacheSer = serialize($g['cache']);

    if(md5($cacheInitSer) != md5($cacheSer)) {
        cache_update($cachePageFile, $cacheSer);
    }
}

DB::close();

$g['error_handled'] = true;