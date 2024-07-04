<?php
/* (C) Websplosion LTD., 2001-2014

gregory modified in 7/4/2023


IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

// if(isset($g['options']['widgets']) && $g['options']['widgets']=="N") Common::toHomePage();

if (defined('IS_DEMO') && IS_DEMO) {
    define('WIDGET_DEMO_WHERE', ' AND session = "' . addslashes(session_id()) . '" ');
    define('WIDGET_DEMO_INSERT', ', session = "' . addslashes(session_id()) . '", session_date = NOW()');
} else {
    define('WIDGET_DEMO_WHERE', '');
    define('WIDGET_DEMO_INSERT', '');
}

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

        $widgetsCount = 10;//nnsscc-diamond-20200317

        $widgetsBySections = array(
            2 => 'blogs',
            4 => 'profile_status',
            5 => 'events',
            6 => 'mail',
            7 => 'forum',
            8 => 'gallery',
			9 => 'chat',//nnsscc-diamond-20200317
			10=> 'chat' 
        );
        

        $rowIndex = 0;
		for($i=1;$i<=$widgetsCount;$i++) {
            // if(isset($widgetsBySections[$i])) {
            //     if(!Common::isOptionActive($widgetsBySections[$i])) {
            //         continue;
            //     }
            // }

            $status = "off";

            $sql = "SELECT * FROM widgets
                WHERE widget = $i
                AND user_id = " . $g_user['user_id'] . WIDGET_DEMO_WHERE;

            DB::query($sql);
            if ($row = DB::fetch_row()) {

                if($row['status']==0) $status = "off";
                if($row['status']==1) $status = "on";
                if($row['status']==2) $status = "home";

                $html->setvar("status_".$status, "checked=checked");
            }
            else $html->setvar("status_".$status, "checked=checked");

            if($status=="off") {
                $html->setvar("status_on", "");
                $html->setvar("status_home", "");
            }

            if($status=="on") {
                $html->setvar("status_off", "");
                $html->setvar("status_home", "");
            }

            if($status=="home") {
                $html->setvar("status_on", "");
                $html->setvar("status_off", "");
            }

            //$rowIndex++;
                $html->setvar('row', ($rowIndex++) % 2 + 1);
                $html->setvar("widget_title", isset($l["widgets.php"]["widget_title_$i"]) ? $l["widgets.php"]["widget_title_$i"] : "");
                $html->setvar("widget_description", isset($l['widgets.php']["widget_description_$i"]) ? $l['widgets.php']["widget_description_$i"] : "");
                $html->setvar("widget", $i);
                $html->parse("widget",true);
        }
                $html->setvar("email", $g_user['mail']);

		parent::parseBlock($html);
	}
}



$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "widgets.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>