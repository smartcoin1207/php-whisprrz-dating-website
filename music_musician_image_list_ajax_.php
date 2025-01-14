<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/music/musician_image_list.php");

$page = new CMusicMusicianImageList("", $g['tmpl']['dir_tmpl_main'] . "_music_musician_image_list.html");

include("./_include/core/main_close.php");

?>