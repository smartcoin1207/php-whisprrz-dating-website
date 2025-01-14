<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
//   Rade 2023-09-23
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
	        $hotdate_id = get_param('hotdate_id');
	        DB::query("SELECT m.* ".
	            "FROM hotdates_hotdate as m ".
	            "WHERE m.hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1");
	        if($hotdate = DB::fetch_row())
	        {
                      DB::query("SELECT m.*, cn.*, st.*, ct.* " .
                        "FROM hotdates_hotdate as m, geo_country as cn, geo_state as st, geo_city as ct " .
                        "WHERE m.hotdate_id=" . to_sql($hotdate_id, 'Number') . " AND m.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
                      if ($hotdate = DB::fetch_row()) {
                            $category_id = get_param('category',$hotdate['category_id']);
                            $city_id = get_param('city',$hotdate['city_id']);
                            $hotdate_private = get_param('hotdate_private',$hotdate['hotdate_private']);
                            $hotdate_title = get_param('hotdate_title',$hotdate['hotdate_title']);
                            $hotdate_description = get_param('hotdate_description',$hotdate['hotdate_description']);
                            $hotdate_date = get_param('hotdate_date');
                            $hotdate_time = get_param('hotdate_time');
                            $hotdate_address = get_param('hotdate_address',$hotdate['hotdate_address']);
                            $hotdate_place = get_param('hotdate_place',$hotdate['hotdate_place']);
                            $hotdate_site = get_param('hotdate_site',$hotdate['hotdate_site']);
                            $hotdate_phone = get_param('hotdate_phone',$hotdate['hotdate_phone']);

                            $hotdate_date = date("Y-m-d", strtotime($hotdate_date));

                            DB::execute('UPDATE hotdates_hotdate SET ' .
                                    'category_id=' . to_sql($category_id) .
                                    ', city_id=' . to_sql($city_id) .
                                    ', hotdate_private=' . to_sql($hotdate_private) .
                                    ', hotdate_title=' . to_sql($hotdate_title) .
                                    ', hotdate_description=' . to_sql($hotdate_description) .
                                    ', hotdate_datetime=' . to_sql($hotdate_date . ' ' . $hotdate_time) .
                                    ', hotdate_address=' . to_sql($hotdate_address) .
                                    ', hotdate_place=' . to_sql($hotdate_place) .
                                    ', hotdate_site=' . to_sql($hotdate_site) .
                                    ', hotdate_phone=' . to_sql($hotdate_phone) .
                                    ', updated_at=NOW() WHERE hotdate_id=' . $hotdate['hotdate_id']);

                            redirect("hotdates_hotdate_edit.php?hotdate_id=" . $hotdate['hotdate_id'] . "&action=saved");
                }
            }
        }
    }
	function parseBlock(&$html)
	{
		global $g;

        $hotdate_id = get_param('hotdate_id');
        DB::query("SELECT m.*, cn.*, st.*, ct.* ".
            "FROM hotdates_hotdate as m, geo_country as cn, geo_state as st, geo_city as ct ".
            "WHERE m.hotdate_id=" . to_sql($hotdate_id, 'Number') . " AND m.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
        if($hotdate = DB::fetch_row())
        {
        	$html->setvar('user_id', $hotdate['user_id']);
        	$html->setvar('hotdate_id', $hotdate['hotdate_id']);
        	$html->setvar('hotdate_private', $hotdate['hotdate_private']);
        	$html->setvar('hotdate_title', he($hotdate['hotdate_title']));
            $html->setvar('hotdate_date', date("m/d/Y", strtotime($hotdate['hotdate_datetime'])));
            $html->setvar('hotdate_time', date("H:i", strtotime($hotdate['hotdate_datetime'])));
        	$html->setvar('hotdate_description', $hotdate['hotdate_description']);
            $html->setvar('hotdate_datetime', $hotdate['hotdate_datetime']);
        	$html->setvar('hotdate_address', he($hotdate['hotdate_address']));
        	$html->setvar('hotdate_place', he($hotdate['hotdate_place']));
        	$html->setvar('hotdate_site', $hotdate['hotdate_site']);
        	$html->setvar('hotdate_phone', he($hotdate['hotdate_phone']));
            $category_options = '';
            DB::query("SELECT * FROM hotdates_category ORDER BY category_id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $hotdate['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], $lang, 'hotdates_category'); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);
/*
            $hotdate_private_options = '<option value=0 ' . ((!$hotdate['hotdate_private']) ? 'selected="selected"' : '') . '>';
            $hotdate_private_options .= l('public'); 
            $hotdate_private_options .= '</option>';           
            $hotdate_private_options .= '<option value=1 ' . (($hotdate['hotdate_private']) ? 'selected="selected"' : '') . '>';
            $hotdate_private_options .= l('private'); 
            $hotdate_private_options .= '</option>';           
            $html->setvar("hotdate_private_options", $hotdate_private_options);*/
            
            $html->setvar("country_options", DB::db_options("SELECT country_id, country_title FROM geo_country;", $hotdate['country_id']));
            $html->setvar("state_options", DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id=" . $hotdate['country_id'] . " ORDER BY state_title;", $hotdate['state_id']));
            $html->setvar("city_options", DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . $hotdate['state_id'] . " ORDER BY city_title;", $hotdate['city_id']));
        
            DB::query("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=" . $hotdate['hotdate_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_b.jpg");
                $html->setvar("image_id", $image['image_id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            $html->parse('photo_edit');

           if(empty($hotdate['hotdate_private'])) {
                $html->parse('hotdate_private_off',false);
            }
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "hotdates_hotdate_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");