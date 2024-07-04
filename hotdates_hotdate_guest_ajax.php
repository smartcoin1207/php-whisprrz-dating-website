<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

require_once("./_include/current/hotdates/tools.php");

function do_action() {
    //popcorn modified 2024-05-26
    $cmd = get_param('cmd');
    $hotdate_id = intval(get_param('hotdate_id'));
    
    if(ChotdatesTools::guestHandle($hotdate_id, $cmd)) {
        echo 'ok'; die();
    } else {
        echo  'error'; die();
    }
}

do_action();

include("./_include/core/main_close.php");
?>