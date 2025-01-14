<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
if (!Common::isOptionActive('mail')) {
    redirect(Common::toHomePage());
}
$folder = 3;
$cmd = get_param('cmd', '');
$uid = get_param('uid', '');

class CSentMailPage extends CHtmlBlock
{
    function parseBlock(&$html)
    {
        global $g;
        global $g_user;

        $uid = get_param('uid', '');
        $start = get_param_int('start', 0);

        $eu = ($start - 0);

        $customLimit = Common::getOption('user_custom_per_page', 'template_options');
        $limit = ($customLimit) ? $customLimit : 10;

        $sqlCount = 'SELECT COUNT(*) FROM `mail_msg` WHERE `user_from` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `user_to` = ' . to_sql($uid, 'Number') . ' AND folder="3"';
        $count = DB::result($sqlCount, 0, 2);

        $sql = 'SELECT *
              FROM `mail_msg`
             WHERE `user_from` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `user_to` = ' . to_sql($uid, 'Number') . 
             ' AND folder="3" ORDER BY id DESC LIMIT ' . to_sql($eu, 'Number') . ", " . to_sql($limit, 'Number');
        DB::query($sql);

        while ($row = DB::fetch_row()) {
            $html->setvar("subject", $row['subject']);
            $html->setvar("text", $row['text']);
            $html->setvar("date_sent", date("d-M-y h:i A", $row['date_sent']));
            $html->parse("mail", true);
        }

        
        $username = User::getInfoBasic($uid, "name");
        $html->setvar("user_name", $username);
        $html->setvar("user_id", $uid);

        if ($count == 0){
            $html->parse("no_mail", true);
        }else{
            $html->setvar("no_mail", false);
        }

        Common::parsePagesList($html, 'top', $count, $start, $limit);
        Common::parsePagesList($html, 'down', $count, $start, $limit);
        
        parent::parseBlock($html);
    }
}


$page = new CSentMailPage("", $g['tmpl']['dir_tmpl_main'] . "sent_mail.html");

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$page->add($footer);

include("./_include/core/main_close.php");
