<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['mobile_redirect_off'] = true;
include('./_include/core/main_start.php');

$url = '_server/city_js/index.php?place=' . get_param('p');
if (CityBase::isEdgeMobile()) {
    $url = '_server/city_js/index.php?view=mobile&place=' . get_param('p');
}
redirect($url);