<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['mobile_redirect_off'] = true;
include("./_include/core/main_start.php");

$nameFile = Common::sanitizeFilename(get_param('file', 'tmpl_header.css'));
$page = new CssPage('', $g['tmpl']['tmpl_loaded_dir'] . 'css/' . $nameFile);

include('./_include/core/main_close.php');