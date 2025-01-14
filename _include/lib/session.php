<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
/*???
if (substr($_SERVER['HTTP_HOST'], 0, 4) == "www.") $cdomain = "." . substr($_SERVER['HTTP_HOST'], 4);
else $cdomain = "." . $_SERVER['HTTP_HOST'];*/
ini_set("session.auto_start", 0);
ini_set("session.use_cookies", 1);
ini_set("session.use_only_cookies", 1);
ini_set("session.use_trans_sid", 0);
$sessionCookieName = 'sid';
session_name($sessionCookieName);
session_set_cookie_params(0, '/', null, false, true);
if (isset($_COOKIE[$sessionCookieName])) {
    if (!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $_COOKIE[$sessionCookieName])) {
        unset($_COOKIE[$sessionCookieName]);
        // setcookie('sid', session_id(), (time() + 3600 * 24 * 30), '/', $cdomain, false, false);
    }
}
session_start();
session_write_close();
// setcookie('sid', session_id(), (time() + 3600 * 24 * 30), '/', $cdomain, false, false);
// setcookie('sid', session_id(), (time() + 3600 * 24 * 30), '/');
