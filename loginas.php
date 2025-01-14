<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


if (!file_exists(dirname(__FILE__) . '/_include/config/db.php')) {
    $redirect = 'Location: _install/install.php';
    header($redirect);
}

include("./_include/core/main_start.php");
//include("./_include/current/blogs/tools.php");

if (get_param('cmd') == 'loginasuser' && get_session('admin_auth') == 'Y') {

    User::logoutWoRedirect();
    $login = get_param('user', '');
    $loginField = "mail";
    $sql = 'SELECT user_id FROM user
                WHERE ' . to_sql($loginField, 'Plain') . ' = ' . to_sql($login, 'Text');
    $id = DB::result($sql);
    
    if ($id == 0) {
        redirect('index.php?cmd=login_incorrect');
    }else{
        set_session('user_id', $id);
        set_session('user_id_verify', $id);

        User::updateLastVisit($id);
        CStatsTools::count('logins', $id);
        redirect(Common::getHomePage());
    }
}

include("./_include/core/main_close.php");
