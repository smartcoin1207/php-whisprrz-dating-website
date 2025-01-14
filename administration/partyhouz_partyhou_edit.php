<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23

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
	        $partyhou_id = get_param('partyhou_id');
	        DB::query("SELECT m.* ".
	            "FROM partyhouz_partyhou as m ".
	            "WHERE m.partyhou_id=" . to_sql($partyhou_id, 'Number') . " LIMIT 1");
	        if($partyhou = DB::fetch_row())
	        {
                      DB::query("SELECT m.* " .
                        "FROM partyhouz_partyhou as m " .
                        "WHERE m.partyhou_id=" . to_sql($partyhou_id, 'Number') . " LIMIT 1");
                      if ($partyhou = DB::fetch_row()) {
                            $category_id = get_param('category',$partyhou['category_id']);
                            $city_id = get_param('city',$partyhou['city_id']);
                            $partyhou_private = get_param('partyhou_private',$partyhou['partyhou_private']);
                            $partyhou_title = get_param('partyhou_title',$partyhou['partyhou_title']);
                            $partyhou_description = get_param('partyhou_description',$partyhou['partyhou_description']);
                            $partyhou_date = get_param('partyhou_date');
                            $partyhou_time = get_param('partyhou_time');
                            $partyhou_address = get_param('partyhou_address',$partyhou['partyhou_address']);
                            $partyhou_place = get_param('partyhou_place',$partyhou['partyhou_place']);
                            $partyhou_site = get_param('partyhou_site',$partyhou['partyhou_site']);
                            $partyhou_phone = get_param('partyhou_phone',$partyhou['partyhou_phone']);

                            $partyhou_date = date("Y-m-d", strtotime($partyhou_date));

                            // var_dump($city_id); die()

                            DB::execute('UPDATE partyhouz_partyhou SET ' .
                                    'category_id=' . to_sql($category_id) .
                                    ', city_id=' . to_sql($city_id) .
                                    ', partyhou_private=' . to_sql($partyhou_private) .
                                    ', partyhou_title=' . to_sql($partyhou_title) .
                                    ', partyhou_description=' . to_sql($partyhou_description) .
                                    ', partyhou_datetime=' . to_sql($partyhou_date . ' ' . $partyhou_time) .
                                    ', partyhou_address=' . to_sql($partyhou_address) .
                                    ', partyhou_place=' . to_sql($partyhou_place) .
                                    ', partyhou_site=' . to_sql($partyhou_site) .
                                    ', partyhou_phone=' . to_sql($partyhou_phone) .
                                    ', updated_at=NOW() WHERE partyhou_id=' . $partyhou['partyhou_id']);

                            redirect("partyhouz_partyhou_edit.php?partyhou_id=" . $partyhou['partyhou_id'] . "&action=saved");
                }
            }
        }
    }
	function parseBlock(&$html)
	{
		global $g;

        $partyhou_id = get_param('partyhou_id');
    
        $partyhou = DB::row("SELECT m.* ".
            "FROM partyhouz_partyhou as m ".
            "WHERE m.partyhou_id=" . to_sql($partyhou_id, 'Number') . "  LIMIT 1");

        if($partyhou )
        {

        	$html->setvar('user_id', $partyhou['user_id']);
        	$html->setvar('partyhou_id', $partyhou['partyhou_id']);
        	$html->setvar('partyhou_private', $partyhou['partyhou_private']);
        	$html->setvar('partyhou_title', he($partyhou['partyhou_title']));
            $html->setvar('partyhou_date', date("m/d/Y", strtotime($partyhou['partyhou_datetime'])));
            $html->setvar('partyhou_time', date("H:i", strtotime($partyhou['partyhou_datetime'])));
        	$html->setvar('partyhou_description', $partyhou['partyhou_description']);
            $html->setvar('partyhou_datetime', $partyhou['partyhou_datetime']);
        	$html->setvar('partyhou_address', he($partyhou['partyhou_address']));
        	$html->setvar('partyhou_place', he($partyhou['partyhou_place']));
        	$html->setvar('partyhou_site', $partyhou['partyhou_site']);
        	$html->setvar('partyhou_phone', he($partyhou['partyhou_phone']));

            // var_dump($partyhou['city_id'] . ''); die();

            $category_options = '';
            DB::query("SELECT * FROM partyhouz_category ORDER BY category_id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $partyhou['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], $lang, 'partyhouz_category'); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);
/*
            $partyhou_private_options = '<option value=0 ' . ((!$partyhou['partyhou_private']) ? 'selected="selected"' : '') . '>';
            $partyhou_private_options .= l('public'); 
            $partyhou_private_options .= '</option>';           
            $partyhou_private_options .= '<option value=1 ' . (($partyhou['partyhou_private']) ? 'selected="selected"' : '') . '>';
            $partyhou_private_options .= l('private'); 
            $partyhou_private_options .= '</option>';           
            $html->setvar("partyhou_private_options", $partyhou_private_options);*/

            $city = DB::row("SELECT * FROM `geo_city` WHERE city_id = " . to_sql($partyhou['city_id'], 'Text'));
            // var_dump($partyhou);
            // var_dump($city); die();

            // var_dump($city); die();

            $html->setvar("country_options", Common::listCountries(isset($city['country_id']) && $city['country_id'] ? $city['country_id'] : ''));
            $html->setvar("state_options", Common::listStates(isset($city['country_id']) && $city['country_id'] ? $city['country_id'] : '', isset($city['state_id']) && $city['state_id'] ? $city['state_id'] : ''));
            $html->setvar("city_options", Common::listCities(isset($city['state_id']) && $city['state_id'] ? $city['state_id'] : '', isset($city['city_id']) && $city['city_id'] ? $city['city_id'] : '' ));

            
            // $html->setvar("country_options", DB::db_options("SELECT country_id, country_title FROM geo_country;", $partyhou['country_id']));
            // $html->setvar("state_options", DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id=" . $partyhou['country_id'] . " ORDER BY state_title;", $partyhou['state_id']));
            // $html->setvar("city_options", DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . $partyhou['state_id'] . " ORDER BY city_title;", $partyhou['city_id']));
        
            DB::query("SELECT * FROM partyhouz_partyhou_image WHERE partyhou_id=" . $partyhou['partyhou_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_b.jpg");
                $html->setvar("image_id", $image['image_id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            $html->parse('photo_edit');

           if(empty($partyhou['partyhou_private'])) {
                $html->parse('partyhou_private_off',false);
            }
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partyhouz_partyhou_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");