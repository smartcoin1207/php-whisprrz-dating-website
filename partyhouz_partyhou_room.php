<?php
/* (C) Websplosion LTD., 2001-2014
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc
This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/custom_head.php");
require_once("./_include/current/partyhouz/header.php");
require_once("./_include/current/partyhouz/sidebar.php");
require_once("./_include/current/partyhouz/tools.php");
payment_check('create_partyhou');

class CpartyhouzRoom extends CHtmlBlock
{

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $partyhou_id = get_param("partyhou_id");
        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);

        //$md5Hash = md5($partyhou_id . $partyhou["partyhou_title"]);

        $html->setvar('user_name', $g_user['name']);
        $html->setvar('user_mail', $g_user['mail']);
        $html->setvar('room_name', $partyhou['partyhou_title']);

        parent::parseBlock($html);
    }
}

$page = new CpartyhouzRoom("", getPageCustomTemplate('partyhouz_partyhou_room.html', 'partyhouz_partyhou_room_template'));

include("./_include/core/main_close.php");