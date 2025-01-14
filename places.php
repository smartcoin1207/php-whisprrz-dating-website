<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/places/header.php");
require_once("./_include/current/places/sidebar.php");
require_once("./_include/current/places/tools.php");
require_once("./_include/current/places/place_list_top.php");

class CPlaces extends CHtmlBlock
{
	function action()
	{
		global $g_user;

	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		$places = CPlacesTools::retrieve_from_sql_base(CPlacesTools::best_places_sql_base($g_user['city_id'], $g_user['state_id'], $g_user['country_id']), 2);

		foreach($places as $place)
		{
			$html->setvar('place_name', strcut(to_html($place['name']), 16));
			$html->setvar('place_id', to_html($place['id']));
			$html->setvar('place_city_title', strcut(to_html($place['city_title']), 10));
			$html->setvar('place_n_reviews', DB::result("SELECT COUNT(id) FROM places_review WHERE place_id = " . $place['id']));

			$rating = $place['rating'];
			for($rating_n = 1; $rating_n <= 10; ++$rating_n)
				$html->setvar('place_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

			DB::query("SELECT * FROM places_place_image WHERE place_id=" . $place['id'] . " ORDER BY created_at ASC LIMIT 1");
			if($image = DB::fetch_row())
			{
				$html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
				$html->setvar("image_thumbnail_big", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th_b.jpg");
				$html->parse('like_place_photo', false);
				$html->setblockvar("like_place_no_photo", "");
			}
			else
			{
				$html->parse('like_place_no_photo', false);
				$html->setblockvar("like_place_photo", "");
			}

			$html->parse('like_place');
		}

		parent::parseBlock($html);
	}
}

$page = new CPlaces("", $g['tmpl']['dir_tmpl_main'] . "places.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$places_header = new CPlacesHeader("places_header", $g['tmpl']['dir_tmpl_main'] . "_places_header.html");
$page->add($places_header);
$places_sidebar = new CPlacesSidebar("places_sidebar", $g['tmpl']['dir_tmpl_main'] . "_places_sidebar.html");
$page->add($places_sidebar);
$places_place_list_top = new CPlacesPlaceListTop("places_place_list_top", $g['tmpl']['dir_tmpl_main'] . "_places_place_list_top.html");
$places_place_list_top->shift = 2;
$page->add($places_place_list_top);

include("./_include/core/main_close.php");
