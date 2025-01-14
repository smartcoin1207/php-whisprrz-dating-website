<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/places/header.php");
require_once("./_include/current/places/sidebar.php");

class CPlacesPlaceCreate extends CHtmlBlock
{
	function action()
	{
		global $g_user;

		$cmd = get_param('cmd');
		if($cmd == 'create')
		{
			$category_id = get_param('category');
			DB::query('SELECT * FROM places_category WHERE id = ' . to_sql($category_id, 'Number'));
			if($category = DB::fetch_row())
			{
				$country_id = get_param('country');
				DB::query('SELECT * FROM geo_country WHERE country_id = ' . to_sql($country_id, 'Number'));
				if($country = DB::fetch_row())
				{
					$state_id = get_param('state');
					DB::query('SELECT * FROM geo_state WHERE country_id = ' . $country['country_id'] . ' AND state_id = ' . to_sql($state_id, 'Number'));
					if($state = DB::fetch_row())
					{
						$city_id = get_param('city');
						DB::query('SELECT * FROM geo_city WHERE country_id = ' . $country['country_id'] . ' AND state_id = ' . $state['state_id'] . ' AND city_id = ' . to_sql($city_id, 'Number'));
						if($city = DB::fetch_row())
						{
							$name = get_param('name');
							$phone = get_param('phone');
							$site = get_param('site');
							$about = get_param('about');
							$address = get_param('address');

							if($name)
							{
								DB::execute('INSERT INTO places_place SET category_id=' . $category['id'] . ', user_id=' . $g_user['user_id'] .
									', name=' . to_sql($name) . ', phone=' . to_sql($phone) . ', site=' . to_sql($site) . ', about=' . to_sql($about) .
									', address=' . to_sql($address) . ', city_id=' . $city['city_id'] .
									', rating=0, n_votes=0, created_at=NOW(), updated_at=NOW()');

								$id = DB::insert_id();

                                Wall::addItemForUser($id, 'places', guid());

								redirect('places_review_edit.php?id=' . $id);
							}
						}
					}
				}
			}

			redirect('home.php');
		}
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

		$category_options = '';
		DB::query("SELECT * FROM places_category ORDER BY id");
		while($category = DB::fetch_row())
		{
			$category_options .= '<option value=' . $category['id'] . '>';
			$category_options .= l($category['title'], false, 'places_category');
			$category_options .= '</option>';
		}
		$html->setvar("category_options", $category_options);

		$html->setvar("country_options", Common::listCountries($g_user['country_id']));
		$html->setvar("state_options", Common::listStates($g_user['country_id'], $g_user['state_id']));
		$html->setvar("city_options", Common::listCities($g_user['state_id'], $g_user['city_id']));

		$name = get_param('name');
		$html->setvar('place_name', he(to_html($name)));

		parent::parseBlock($html);
	}
}

$page = new CPlacesPlaceCreate("", $g['tmpl']['dir_tmpl_main'] . "places_place_create.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$places_header = new CPlacesHeader("places_header", $g['tmpl']['dir_tmpl_main'] . "_places_header.html");
$page->add($places_header);
$places_sidebar = new CPlacesSidebar("places_sidebar", $g['tmpl']['dir_tmpl_main'] . "_places_sidebar.html");
$page->add($places_sidebar);

include("./_include/core/main_close.php");

?>