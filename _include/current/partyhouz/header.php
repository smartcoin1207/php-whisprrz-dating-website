<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CpartyhouzHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

        $partyhou_datetime = get_param('partyhou_datetime');
        if ($partyhou_datetime != '') {
            $date = explode(' ', $partyhou_datetime);
            $html->setvar('partyhou_date', $date[0]);
            $html->parse('private_partyhou_date');
            $html->parse('partyhou_date');
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
            $html->parse('partyhou_header_location');
        }

		$settings = CpartyhouzTools::settings();

        $query = get_param('query');
        $html->setvar('query', $query);

        $search_type_item = get_param('search_type_item');
        $html->setvar('search_type_item', $search_type_item);

        DB::query("SELECT * FROM partyhouz_category ORDER BY position");
        $categories = array(array('category_id' => 0, 'category_title' => l('all', false, 'partyhouz_category')));
        while($category = DB::fetch_row())
        {
            $categories[] = $category;
        }

        for($category_n = 0; $category_n != count($categories); ++$category_n)
        {
            $category = $categories[$category_n];

        	$html->setvar('category_id', $category['category_id']);
            $html->setvar('category_title', l($category['category_title'], false, 'partyhouz_category'));

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

