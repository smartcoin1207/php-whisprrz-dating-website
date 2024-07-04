<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
if (isset($page)) {
    $page->init();
	if (IS_DEMO && $sitePart === 'administration') {
		if ($p == 'index.php' || ($p == 'users_edit.php' && get_param('cmd') == 'location')) {
            $page->action();
        }
	} else {
		$page->action();
	}
	$tmp = null;
	$page->parse($tmp);
}
if (isset($pageWall)) {
    $pageWall->init();
    $pageWall->action();
    $tmp = null;
    echo get_json_encode($pageWall->parse($tmp, true));
}

if(USE_FILES_CACHE) {
    if($g['cache_init'] != $g['cache']) {
        cache_update($cachePageFile, serialize($g['cache']));
    }
}

if (!isset($DB_no_close)) DB::close();

if(DEV_PROFILING) {
    $xhprofData = xhprof_disable();

    $XHPROF_ROOT = $xhprof['root'];
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

    $xhprofRuns = new XHProfRuns_Default();
    $runId = $xhprofRuns->save_run($xhprofData, "xhprof_testing");

    $search = array('{domain}', '{run_id}');
    $replace = array($_SERVER['HTTP_HOST'], $runId);
    $url = str_replace($search, $replace, $xhprof['url']);

    echo "<a href='$url' target='_blank'>STAT</a>";
}

$g['error_handled'] = true;