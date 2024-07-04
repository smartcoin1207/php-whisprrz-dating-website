<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CPlacesSidebar extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;
		      CBanner::getBlock($html, 'right_column');
		$reviews = CPlacesTools::retrieve_from_sql_base(CPlacesTools::best_reviews_sql_base($g_user['city_id'], $g_user['state_id'], $g_user['country_id']), 6);

		$review_n = 1;
		foreach($reviews as $review)
		{
			$prefix = ($review_n > 1) ? '' : 'day_';

			$html->setvar('place_name', he(hard_trim(to_html($review['name']), 20)));
			$html->setvar('place_id', to_html($review['place_id']));
			$html->setvar('place_city_title', strcut(to_html($review['city_title']), 10));
			$html->setvar('place_city_id', $review['city_id']);
			$html->setvar('place_n_reviews', $review['n_reviews']);

			$rating = $review['rating'];
			for($rating_n = 1; $rating_n <= 10; ++$rating_n)
				$html->setvar('place_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

			$review_user_name = DB::result("SELECT name FROM user WHERE user_id=" . $review['user_id'] ." LIMIT 1");

			$html->setvar('review_id', $review['id']);
			$html->setvar('review_n_votes', $review['n_votes']);
			$html->setvar('review_user_name', $review_user_name);
			$html->setvar('review_text', strcut(strip_tags($review['text']), 100));

            if($review['n_reviews'] == 1)
                $html->setvar('label_places_reviews', isset($l['all']['places_review']) ? $l['all']['places_review'] : '');
            else
                $html->setvar('label_places_reviews', isset($l['all']['places_reviews']) ? $l['all']['places_reviews'] : '');

			if($review_n == 1)
			{
				DB::query("SELECT * FROM places_place_image WHERE place_id=" . $review['place_id'] . " ORDER BY created_at ASC LIMIT 1");
				if($image = DB::fetch_row())
				{
					$html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
					$html->parse($prefix . 'review_photo', false);
					$html->setblockvar($prefix . "review_no_photo", "");
				}
				else
				{
					$html->parse($prefix . 'review_no_photo', false);
					$html->setblockvar($prefix . "review_photo", "");
				}
			}

			$html->parse($prefix . 'review');

			++$review_n;
		}

		parent::parseBlock($html);
	}
}

