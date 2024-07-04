<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CEventsHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

        $event_datetime = get_param('event_datetime');
        if ($event_datetime != '') {
            $date = explode(' ', $event_datetime);
            $html->setvar('event_date', $date[0]);
            $html->parse('private_event_date');
            $html->parse('event_date');
        }
        
        $country = null;
        if($g_user['country_id'])
            $country = DB::result("SELECT country_title FROM geo_country WHERE country_id = " . $g_user['country_id']);
        if(!$country)
            $country = "unknown";

        $city = null;
        $city_id = null;
        if($g_user['city_id'])
        {
            $city_id = $g_user['city_id'];
            $city = DB::result("SELECT city_title FROM geo_city WHERE city_id = " . $g_user['city_id']);
        }

        $html->setvar('country', $country);
        $html->setvar('city', $city);
        $html->setvar('city_id', $city_id);

        $location = ($city ? ($city . ', ') : '') . $country;
        $html->setvar('location', strcut($location, 40));
        $html->setvar('location_full', $location);

        if(guid()) {
            $html->parse('event_header_location');
        }

		$settings = CEventsTools::settings();

        DB::query("SELECT * FROM events_category ORDER BY position");
        $categories = array(array('category_id' => 0, 'category_title' => l('all', false, 'events_category')));
        while($category = DB::fetch_row())
        {
            $categories[] = $category;
        }

        for($category_n = 0; $category_n != count($categories); ++$category_n)
        {
            $category = $categories[$category_n];

        	$html->setvar('category_id', $category['category_id']);
            $html->setvar('category_title', l($category['category_title'], false, 'events_category'));

            if($category_n == count($categories) - 1)
                $html->setvar("class_last", ' class="last"');
            else
                $html->setvar("class_last", '');

            if($category['category_id'] == $settings['category_id'])
            {
                $html->parse('categories_item_active', false);
                $html->setblockvar('categories_item_not_active', '');
            }
            else
            {
                $html->parse('categories_item_not_active', false);
                $html->setblockvar('categories_item_active', '');
            }

            $html->parse("categories_item", true);
        }

		parent::parseBlock($html);
	}
}

