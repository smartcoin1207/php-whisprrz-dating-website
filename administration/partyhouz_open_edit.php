<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Open_PartyhouZ senior-dev-1019 2024-10-18

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "update")
		{
	        $open_partyhouz_id = get_param('open_partyhouz_id');
	        DB::query("SELECT m.* ".
	            "FROM partyhouz_open as m ".
	            "WHERE m.open_partyhouz_id=" . to_sql($open_partyhouz_id, 'Number') . " LIMIT 1");
	        if($open_partyhouz = DB::fetch_row())
	        {
                $room_max = get_param('room_max');
                $allowed_gender = get_param('allowed_gender');
                $user_max = get_param('user_max');
                $resets = get_param('resets');
                DB::execute('UPDATE partyhouz_open SET ' . 
                    ' room_max=' . to_sql($room_max,"Number") .
                    ', allowed_gender=' . to_sql($allowed_gender,"Number") .
                    ', user_max=' . to_sql($user_max,"Number") .
                    ', resets=' . to_sql($resets,"Number") .
					' WHERE open_partyhouz_id=' . $open_partyhouz_id);
                redirect("partyhouz_open_edit.php?open_partyhouz_id=".$open_partyhouz_id);
	        }
			
		    redirect("partyhouz_open_edit.php?open_partyhouz_id=".$open_partyhouz_id);
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $open_partyhouz_id = get_param('open_partyhouz_id');
        DB::query("SELECT * ".
            "FROM partyhouz_open as m ".
            " LEFT JOIN partyhouz_category ON m.category_id = partyhouz_category.category_id LEFT JOIN partyhouz_partyhou on m.partyhou_ids = partyhouz_partyhou.partyhou_id WHERE m.open_partyhouz_id=" . to_sql($open_partyhouz_id, 'Number') . " LIMIT 1");
        if($open_partyhouz = DB::fetch_row())
        {
        	$html->setvar('open_partyhouz_id', $open_partyhouz['open_partyhouz_id']);
        	$html->setvar('category_title', htmlentities($open_partyhouz['category_title'],ENT_QUOTES,"UTF-8"));
        	$html->setvar('partyhou_ids', $open_partyhouz['partyhou_ids']);
        	$html->setvar('partyhou_title', htmlentities($open_partyhouz['partyhou_title'],ENT_QUOTES,"UTF-8"));
            $html->setvar('room_max', $open_partyhouz['room_max']);

            $gender_opts = "";
            for ($i=0; $i < 6; $i++) {
                if( $i == 0 ) {
                    $gender_label = "Everyone";
                } elseif ( $i == 1 ) {
                    $gender_label = "Couples";
                } elseif ( $i == 2 ) {
                    $gender_label = "Females";
                } elseif ( $i == 3 ) {
                    $gender_label = "Males";
                } elseif ( $i == 4 ) {
                    $gender_label = "Transgender";
                } else {
                    $gender_label = "Non-binary";
                }
                $gender_opts .= "<option value='". $i ."' ". ( $i == $open_partyhouz['allowed_gender']? 'selected': '' ) .">". $gender_label ."</option>";
            }
        	$html->setvar('gender_opts', $gender_opts);
        	$html->setvar('user_max', $open_partyhouz['user_max']);
        	$html->setvar('resets', $open_partyhouz['resets']);
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partyhouz_open_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
