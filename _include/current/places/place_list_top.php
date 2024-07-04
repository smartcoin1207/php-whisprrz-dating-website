<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CPlacesPlaceListTop extends CHtmlBlock
{
	var $shift = 0;
    var $title = true;

	function action()
	{
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;
        global $p;

        $what = get_param('what');
        $where = get_param('where');
        $need_write_review_button = false;

        if($what || $where)
        {
            $sql_base = CPlacesTools::best_places_sql_base($g_user['city_id'], $g_user['state_id'], $g_user['country_id'], null, null, $what, $where, 'rating');
            $need_write_review_button = true;
        }
        else
        {
            $sql_base = CPlacesTools::best_places_sql_base($g_user['city_id'], $g_user['state_id'], $g_user['country_id']);

	        $location_title = 'undefined';

	        if($sql_base['city'])
	        {
	            $location_title = $sql_base['city']['city_title'];
	        }
	        else if($sql_base['state'])
	        {
	            $location_title = $sql_base['state']['state_title'];
	        }
	        if($sql_base['country'])
	        {
	            $location_title = $sql_base['country']['country_title'];
	        }

	        $html->setvar('location_title', $location_title);
            $count = CPlacesTools::count_from_sql_base(CPlacesTools::places_by_location_sql_base($g_user['city_id'], $g_user['state_id'], $g_user['country_id']));
            if($count - $this->shift > 3 && $location_title != 'undefined') {
                $html->parse('title_top_location');
            } elseif ($this->title && $count == 0) {
                $html->parse('title_top');
            } elseif ($count > 0) {
                $html->parse('title_top');
            }
            if ($count <= 2) {
                $this->shift = 0;
            }
        }

		$places = CPlacesTools::retrieve_from_sql_base($sql_base, 3, $this->shift);

		if($what || $where)
		{
            if(count($places))
            {
                $html->parse('title_search');
            }
            else
            {
            	$html->parse('title_search_no_result');
            }

		}

		foreach($places as $place)
		{
			$html->setvar('place_name', strcut(to_html(he($place['name'])), 32));
			$html->setvar('place_id', to_html($place['id']));
			$html->setvar('place_city_id', $place['city_id']);
			$html->setvar('place_city_title', strcut(to_html($place['city_title']), 25 - min(15, pl_strlen(to_html($place['address'])))));
			$html->setvar('place_address', strcut(to_html($place['address']), 25 - min(10, pl_strlen(to_html($place['city_title'])))));
			$html->setvar('place_address_to_url', urlencode($place['address']));
			$html->setvar('place_n_reviews', $place['n_reviews']);
			$html->setvar('place_category_id', to_html($place['category_id']));
			$html->setvar('place_category_title', to_html(l($place['title'], false, 'places_category')));

			$rating = $place['rating'];
			for($rating_n = 1; $rating_n <= 10; ++$rating_n)
				$html->setvar('place_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

            if($place['n_reviews'] == 1)
                $html->setvar('label_places_reviews', isset($l['all']['places_review']) ? $l['all']['places_review'] : '');
            else
                $html->setvar('label_places_reviews', isset($l['all']['places_reviews']) ? $l['all']['places_reviews'] : '');

			if($place['n_reviews'])
			{
				DB::query("SELECT * FROM places_review WHERE place_id=" . $place['id'] . " ORDER BY n_votes DESC LIMIT 1");
				if($review = DB::fetch_row())
				{
					$review_user_name = DB::result("SELECT name FROM user WHERE user_id=" . $review['user_id'] ." LIMIT 1");

					$html->setvar('review_user_name', $review_user_name);
					$html->setvar('review_text', strcut(strip_tags($review['text']), 100));

					$html->parse('place_review', false);
					$html->setblockvar("place_no_review", "");
				}
				else
				{
					$html->parse("place_no_review", false);
					$html->setblockvar("place_review", "");
				}
			}
			else
			{
				$html->parse("place_no_review", false);
				$html->setblockvar("place_review", "");
			}

			DB::query("SELECT * FROM places_place_image WHERE place_id=" . $place['id'] . " ORDER BY id DESC LIMIT 1");
			if($image = DB::fetch_row())
			{
				$html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
				$html->setvar("image_thumbnail_big", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th_b.jpg");
				$html->parse('place_photo', false);
				$html->setblockvar("place_no_photo", "");
			}
			else
			{
				$html->parse('place_no_photo', false);
				$html->setblockvar("place_photo", "");
			}

			if($need_write_review_button)
			{
	            if(DB::result("SELECT COUNT(id) FROM places_review WHERE place_id = ".$place['id']." AND user_id = ".$g_user['user_id']." LIMIT 1"))
	            {
	                $html->parse('place_edit_review', false);
	                $html->setblockvar('place_create_review', '');
	            }
	            else
	            {
	                $html->parse('place_create_review', false);
	                $html->setblockvar('place_edit_review', '');
	            }

                $html->parse('place_write_review', false);
			}
                        if ($p != 'index.php') {
                            $html->parse('place_play', false);
                        } else {
                            $html->parse('place_play_style', false);
                        }
			$html->parse('place');
		}

		parent::parseBlock($html);
	}
}
