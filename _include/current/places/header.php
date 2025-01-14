<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

payment_check('places');

class CPlacesHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

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
		$html->setvar('location', strcut($location, 18));
		$html->setvar('location_full', $location);

        if(guid()) {
            $html->parse('places_header_location');
        }

		DB::query("SELECT * FROM places_category ORDER BY position");
		$categories = array();
		while($category = DB::fetch_row())
		{
			$categories[] = $category;
		}

		for($category_n = 0; $category_n != count($categories); ++$category_n)
		{
			$html->setvar('category_id', $categories[$category_n]['id']);
			$html->setvar('category_title', l($categories[$category_n]['title'],false,'places_category'));

			if($category_n == count($categories) - 1)
				$html->parse("places_category_last", false);
			$html->parse("places_category", true);
		}

		parent::parseBlock($html);
	}
}

