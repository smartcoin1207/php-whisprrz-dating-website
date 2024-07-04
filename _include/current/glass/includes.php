<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once dirname(__FILE__) . '/tools.php';
require_once dirname(__FILE__) . '/header.php';
require_once dirname(__FILE__) . '/footer.php';
require_once dirname(__FILE__) . '/side.php';
require_once dirname(__FILE__) . '/../video_hosts.php';
require_once dirname(__FILE__) . '/../pager.php';
require_once dirname(__FILE__) . '/../friends.php';
require_once dirname(__FILE__) . '/../users.php';

if(isset($sitePart) && $sitePart == 'administration') {
    CVidsTools::$admin = true;
}
