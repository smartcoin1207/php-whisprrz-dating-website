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
                $user_max = get_param('user_max');
                $resets = get_param('resets');

                list($hours, $minutes) = explode(':', $resets);
                $resets = ($hours * 60) + $minutes;
                
                $is_disabled  = get_param('is_disabled', '') == 'on' ? 1 : 0;
                DB::execute('UPDATE partyhouz_open SET ' . 
                    ' is_disabled=' . to_sql($is_disabled,"Number") .
                    ', user_max=' . to_sql($user_max,"Number") .
                    ', resets=' . to_sql($resets,"Number") .
					' WHERE open_partyhouz_id=' . $open_partyhouz_id);
                
                DB::query("SELECT m.*, partyhouz_partyhou.* ".
                    "FROM partyhouz_open as m ".
                    " LEFT JOIN partyhouz_partyhou ON  FIND_IN_SET(partyhouz_partyhou.partyhou_id, m.partyhou_ids) WHERE m.open_partyhouz_id=" . $open_partyhouz_id );

                if ($partyhou = DB::fetch_row())
                {
                    $category_id = get_param('category',$partyhou['category_id']);
                    $city_id = get_param('city',$partyhou['city_id']);
                    $partyhou_private = get_param('partyhou_private',$partyhou['partyhou_private']);
                    $partyhou_title = get_param('partyhou_title',$partyhou['partyhou_title']);
                    $partyhou_description = get_param('partyhou_description',$partyhou['partyhou_description']);
                    $partyhou_date = get_param('partyhou_date');
                    $partyhou_time = get_param('partyhou_time');
                    // $partyhou_address = get_param('partyhou_address',$partyhou['partyhou_address']);
                    $signin_couples  = get_param('signin_couples', '') == 'on' ? 1 : 0;
                    $signin_females  = get_param('signin_females', '') == 'on' ? 1 : 0;
                    $signin_males  = get_param('signin_males', '') == 'on' ? 1 : 0;
                    $signin_transgender  = get_param('signin_transgender', '') == 'on' ? 1 : 0;
                    $signin_nonbinary  = get_param('signin_nonbinary', '') == 'on' ? 1 : 0;
                    $signin_everyone  = get_param('signin_everyone', '') == 'on' ? 1 : 0;

                    $partyhou_date = date("Y-m-d", strtotime($partyhou_date));

                    DB::execute('UPDATE partyhouz_partyhou SET ' .
                        'category_id=' . to_sql($category_id) .
                        ', city_id=' . to_sql($city_id) .
                        ', partyhou_private=' . to_sql($partyhou_private) .
                        ', partyhou_title=' . to_sql($partyhou_title) .
                        ', partyhou_description=' . to_sql($partyhou_description) .
                        ', partyhou_datetime=' . to_sql($partyhou_date . ' ' . $partyhou_time) .
                        // ', partyhou_address=' . to_sql($partyhou_address) .
                        ', signin_couples='.to_sql($signin_couples).
                        ', signin_females='.to_sql($signin_females).
                        ', signin_males='.to_sql($signin_males).
                        ', signin_transgender='.to_sql($signin_transgender).
                        ', signin_nonbinary='.to_sql($signin_nonbinary).
                        ', signin_everyone='.to_sql($signin_everyone).
                        ', updated_at=NOW() WHERE partyhou_id=' . to_sql($partyhou['partyhou_id'], 'Number'));
                }
	        }
			
		    redirect("partyhouz_open_edit.php?open_partyhouz_id=".$open_partyhouz_id);
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $open_partyhouz_id = get_param('open_partyhouz_id');
        DB::query("SELECT m.*, partyhouz_partyhou.*, partyhouz_category.category_title as category_title ".
            "FROM partyhouz_open as m ".
            "LEFT JOIN partyhouz_partyhou ON FIND_IN_SET(partyhouz_partyhou.partyhou_id, m.partyhou_ids) LEFT JOIN partyhouz_category ON partyhouz_partyhou.category_id = partyhouz_category.category_id WHERE m.open_partyhouz_id=" . to_sql($open_partyhouz_id, 'Number') . " LIMIT 1");
        if($open_partyhouz = DB::fetch_row())
        {
        	$html->setvar('open_partyhouz_id', $open_partyhouz['open_partyhouz_id']);
        	$html->setvar('category_title', htmlentities($open_partyhouz['category_title'],ENT_QUOTES,"UTF-8"));
        	$html->setvar('partyhou_id', $open_partyhouz['partyhou_id']);
        	$html->setvar('partyhou_title', htmlentities($open_partyhouz['partyhou_title'],ENT_QUOTES,"UTF-8"));
            $html->setvar('partyhou_date', date("m/d/Y", strtotime($open_partyhouz['partyhou_datetime'])));
            $html->setvar('partyhou_time', date("H:i", strtotime($open_partyhouz['partyhou_datetime'])));
        	$html->setvar('partyhou_description', $open_partyhouz['partyhou_description']);
            $html->setvar('partyhou_datetime', $open_partyhouz['partyhou_datetime']);
        	// $html->setvar('partyhou_address', he($open_partyhouz['partyhou_address']));

            $category_options = "";
            DB::query("SELECT * FROM partyhouz_category ORDER BY category_id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $open_partyhouz['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], $lang, 'partyhouz_category'); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);
            
            $city = DB::row("SELECT * FROM `geo_city` WHERE city_id = " . to_sql($open_partyhouz['city_id'], 'Text'));

            $html->setvar("country_options", Common::listCountries(isset($city['country_id']) && $city['country_id'] ? $city['country_id'] : ''));
            $html->setvar("state_options", Common::listStates(isset($city['country_id']) && $city['country_id'] ? $city['country_id'] : '', isset($city['state_id']) && $city['state_id'] ? $city['state_id'] : ''));
            $html->setvar("city_options", Common::listCities(isset($city['state_id']) && $city['state_id'] ? $city['state_id'] : '', isset($city['city_id']) && $city['city_id'] ? $city['city_id'] : '' ));

        	$html->setvar('signin_couples', $open_partyhouz['signin_couples']);
        	$html->setvar('signin_females', $open_partyhouz['signin_females']);
        	$html->setvar('signin_males', $open_partyhouz['signin_males']);
        	$html->setvar('signin_transgender', $open_partyhouz['signin_transgender']);
        	$html->setvar('signin_nonbinary', $open_partyhouz['signin_nonbinary']);
        	$html->setvar('signin_everyone', $open_partyhouz['signin_everyone']);

        	$html->setvar('user_max', $open_partyhouz['user_max']);

            $hours = floor($open_partyhouz['resets'] / 60);
            $minutes = $open_partyhouz['resets'] % 60;
            $resets = sprintf('%02d:%02d', $hours, $minutes);
        	$html->setvar('resets', $resets);

            $html->setvar('is_disabled', $open_partyhouz['is_disabled']);
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
