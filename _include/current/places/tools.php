<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CPlacesTools
{
	static function split_search_to_words($search)
	{
		$search = str_replace(array(',', ';', '!', '?', '.'), array(' ', ' ', ' ', ' ', ' '), $search);

		$_words = explode(" ", $search);
		$words = array();
		foreach($_words as $word)
		{
			$word = trim($word);

			if(mb_strlen($word) > 2)
                $words[] = $word;
		}

		return $words;
	}

	static function reviews_by_place_sql_base($place_id)
	{
		$sql = "places_review WHERE place_id = " . to_sql($place_id, 'Number') . " ORDER BY created_at DESC";

		return array('query' => $sql, 'columns' => '*');
	}

	static function best_reviews_sql_base($city_id, $state_id, $country_id)
	{
		if($city_id)
		{
			DB::query("SELECT * FROM geo_city WHERE city_id=".to_sql($city_id, 'Number'));
			if($city = DB::fetch_row())
			{
				$city_id = $city['city_id'];
				$state_id = $city['state_id'];
				$country_id = $city['country_id'];
			}
			else
				$city_id = null;
		}
		if(!$city_id && $state_id)
		{
			DB::query("SELECT * FROM geo_state WHERE state_id=".to_sql($state_id, 'Number'));
			if($state = DB::fetch_row())
			{
				$state_id = $state['state_id'];
				$country_id = $state['country_id'];
			}
			else
				$state_id = null;
		}
		if(!$city_id && !$state_id && $country_id)
		{
			DB::query("SELECT * FROM geo_country WHERE country_id=".to_sql($country_id, 'Number'));
			if($country = DB::fetch_row())
			{
				$country_id = $country['country_id'];
			}
			else
				$country_id = null;
		}

		$orders = array();

		if($city_id)
			$orders[] = "c.city_id = $city_id DESC";
		if($state_id)
			$orders[] = "c.state_id = $state_id DESC";
		if($country_id)
			$orders[] = "c.country_id = $country_id DESC";
		$orders[] = "r.n_votes DESC";
		$orders[] = "r.created_at DESC";

		$sql = "places_review as r, places_place as p, geo_city as c WHERE r.place_id = p.id AND p.city_id = c.city_id ORDER BY " . implode(", ", $orders);

		return array('query' => $sql, 'columns' => 'r.*, p.id as place_id, p.name, p.rating, p.n_reviews, c.*');
	}

	static function places_by_location_sql_base($city_id, $state_id, $country_id)
	{
        $city = null;
        $state = null;
        $country = null;

        if($city_id)
        {
            DB::query("SELECT * FROM geo_city WHERE city_id=".to_sql($city_id, 'Number'));
            if($city = DB::fetch_row())
            {
                $city_id = $city['city_id'];
                $state_id = $city['state_id'];
                $country_id = $city['country_id'];
            }
            else
                $city_id = null;
        }
        if(!$city_id && $state_id)
        {
            DB::query("SELECT * FROM geo_state WHERE state_id=".to_sql($state_id, 'Number'));
            if($state = DB::fetch_row())
            {
                $state_id = $state['state_id'];
                $country_id = $state['country_id'];
            }
            else
                $state_id = null;
        }
        if(!$city_id && !$state_id && $country_id)
        {
            DB::query("SELECT * FROM geo_country WHERE country_id=".to_sql($country_id, 'Number'));
            if($country = DB::fetch_row())
            {
                $country_id = $country['country_id'];
            }
            else
                $country_id = null;
        }

        $wheres = array();
        if($city_id)
            $wheres[] = "c.city_id = $city_id";
        if($state_id)
            $wheres[] = "c.state_id = $state_id";
        if($country_id)
            $wheres[] = "c.country_id = $country_id";

        $where = trim(implode(' AND ', $wheres));
        if($where != '') {
            $where = ' AND ' . $where;
        }

        $sql = "places_place as p, geo_city as c, geo_state as st, geo_country as cn, places_category as cat WHERE p.city_id = c.city_id AND p.category_id = cat.id AND st.state_id = c.state_id AND cn.country_id = c.country_id " . $where;

        return array('query' => $sql, 'columns' => 'p.*, c.*, cat.title', 'city' => $city, 'state' => $state, 'country' => $country);
	}

	static function best_places_sql_base($city_id, $state_id, $country_id, $category_id = null, $search_city_id = null, $search_what = null, $search_where = null, $sort_by = null)
	{
		$city = null;
		$state = null;
		$country = null;

		if($city_id)
		{
			DB::query("SELECT * FROM geo_city WHERE city_id=".to_sql($city_id, 'Number'));
			if($city = DB::fetch_row())
			{
				$city_id = $city['city_id'];
				$state_id = $city['state_id'];
				$country_id = $city['country_id'];
			}
			else
				$city_id = null;
		}
		if(!$city_id && $state_id)
		{
			DB::query("SELECT * FROM geo_state WHERE state_id=".to_sql($state_id, 'Number'));
			if($state = DB::fetch_row())
			{
				$state_id = $state['state_id'];
				$country_id = $state['country_id'];
			}
			else
				$state_id = null;
		}
		if(!$city_id && !$state_id && $country_id)
		{
			DB::query("SELECT * FROM geo_country WHERE country_id=".to_sql($country_id, 'Number'));
			if($country = DB::fetch_row())
			{
				$country_id = $country['country_id'];
			}
			else
				$country_id = null;
		}

		$orders = array();

		if($sort_by == 'rating')
		{
	        $orders[] = "p.rating DESC";
		}
		else if($sort_by == 'recent')
		{
            $orders[] = "p.created_at DESC";
		}
		else if($sort_by == 'reviews')
		{
            $orders[] = "p.reviews_rating DESC";
		}
		else if($sort_by == 'name')
		{
            $orders[] = "p.name ASC";
		}
		else
		{
			if($city_id)
				$orders[] = "c.city_id = $city_id DESC";
			if($state_id)
				$orders[] = "c.state_id = $state_id DESC";
			if($country_id)
				$orders[] = "c.country_id = $country_id DESC";
            $orders[] = "p.has_images DESC";
            $orders[] = "p.rating DESC";
			$orders[] = "p.n_reviews DESC";
			$orders[] = "p.reviews_rating DESC";
			$orders[] = "p.created_at DESC";
		}

		$where = "";

		if($category_id)
		{
			$where .= " AND p.category_id = " . to_sql($category_id, 'Number') . " ";
		}

        if($search_city_id)
        {
            $where .= " AND p.city_id = " . to_sql($search_city_id, 'Number') . " ";
        }

		if($search_what)
		{
            $words = self::split_search_to_words($search_what);
            $searches = array();

            foreach($words as $word)
            {
                $searches[] = "CONCAT_WS('', p.name, p.about) LIKE " . to_sql('%'.$word.'%');
            }

            DB::query("SELECT * FROM places_category ORDER BY id");
	        $categories = array();
	        while($category = DB::fetch_row())
	        {
	            $category['title'] = isset($l['all'][$category['title']]) ? $l['all'][$category['title']] : $category['title'];
	            foreach($words as $word)
	            {
                    if(stripos($category['title'], $word) !== false)
                    {
                        $categories[] = $category['id'];
                        break;
                    }
	            }
	        }

	        if(count($categories))
                array_unshift($searches, "p.category_id IN (" . implode(', ',  $categories) . ")");

            if(count($searches))
                $where .= " AND (" . implode(' OR ',  $searches) . ") ";
		}

        if($search_where)
        {
            $words = self::split_search_to_words($search_where);
            $searches = array();

            foreach($words as $word)
        	{
                $searches[] = "CONCAT_WS('', c.city_title, st.state_title, cn.country_title, p.address) LIKE " . to_sql('%'.$word.'%');
        	}

            if(count($searches))
                $where .= " AND (" . implode(' OR ',  $searches) . ") ";
        }

		$sql = "places_place as p, geo_city as c, geo_state as st, geo_country as cn, places_category as cat WHERE p.city_id = c.city_id AND p.category_id = cat.id AND st.state_id = c.state_id AND cn.country_id = c.country_id ".$where." ORDER BY " . implode(", ", $orders);

		return array('query' => $sql, 'columns' => 'p.*, c.*, cat.title', 'city' => $city, 'state' => $state, 'country' => $country);
	}

	static function retrieve_from_sql($sql)
	{
		DB::query($sql);
		$results = array();

		while($row = DB::fetch_row())
		{
			$results[] = $row;
		}

		return $results;
	}

	static function retrieve_from_sql_base($sql_base, $limit = 0, $shift = 0)
	{
		return self::retrieve_from_sql("SELECT " . $sql_base['columns'] . " FROM " . $sql_base['query'] . ($limit ? (" LIMIT " .  intval($shift) . ", " . intval($limit)) : ''));
	}

	static function count_from_sql_base($sql_base)
	{
		return DB::result("SELECT COUNT(*) FROM " . $sql_base['query']);
	}

	static function place_update_has_photos($place_id)
	{
		$n_images = DB::result("SELECT COUNT(id) FROM places_place_image WHERE place_id = " . to_sql($place_id, 'Number'));
		DB::execute("UPDATE places_place SET has_images = " . ($n_images ? 1 : 0) . " WHERE id = " . to_sql($place_id, 'Number') . " LIMIT 1");
	}

	static function place_update_n_reviews($place_id)
	{
		DB::query("SELECT COUNT(id), SUM(n_votes) FROM places_review WHERE place_id = " . to_sql($place_id, 'Number'));
		$row = DB::fetch_row();
		DB::execute("UPDATE places_place SET n_reviews = '" . $row['COUNT(id)'] . "', reviews_rating = '" . $row['SUM(n_votes)'] . "' WHERE id = " . to_sql($place_id, 'Number') . " LIMIT 1");
	}

    static function delete_place_image($image_id, $admin = false)
    {
        global $g;
        global $g_user;

        $image = DB::row("SELECT i.* FROM places_place_image as i, places_place as p WHERE i.id=" . to_sql($image_id, 'Number') .
            " AND i.place_id = p.id " .
            ($admin ? "" : (" AND (p.user_id = " . $g_user['user_id'] . " OR i.user_id = " . $g_user['user_id'] . ")  ")) .
            " LIMIT 1");
        if($image)
        {
            $filename_base = $g['path']['url_files'] . "places_images/" . $image['id'];

			//popcorn modified s3 bucket places_images delete image 2024-05-06
			if(isS3SubDirectory($filename_base)) {
				$file_sizes = array('_b.jpg', '_th.jpg', '_th_b.jpg', '_th_s.jpg', '_src.jpg');

				foreach ($file_sizes as $key => $size) {
					custom_file_delete($filename_base . $size);
					
				}
			} else {
				$path = array($filename_base . '_b.jpg', $filename_base . '_th.jpg', $filename_base . '_th_b.jpg', $filename_base . '_src.jpg');
				Common::saveFileSize($path, false);
				$filename = $filename_base . "_th.jpg";
				if(custom_file_exists($filename))
					@unlink($filename);
				$filename = $filename_base . "_th_b.jpg";
				if(custom_file_exists($filename))
					@unlink($filename);
				$filename = $filename_base . "_b.jpg";
				if(custom_file_exists($filename))
					@unlink($filename);
				$filename = $filename_base . "_src.jpg";
				if(custom_file_exists($filename))
					@unlink($filename);
			}
            

            DB::execute("DELETE FROM places_place_image WHERE id=".$image['id']. " LIMIT 1");

            Wall::removeImages('places_photo', $image['place_id'], $image['created_at'], 0, 'places_place_image', 'place_id');

            Wall::deleteItemForUserByItem($image['place_id'], 'places', $image['user_id']);

            self::place_update_has_photos($image['place_id']);
        }
    }

    static function delete_review($review_id, $admin = false)
    {
        global $g;
        global $g_user;

        $review = DB::row("SELECT r.* FROM places_review as r WHERE r.id=" . to_sql($review_id, 'Number') .
            ($admin ? "" : (" AND r.user_id = " . $g_user['user_id'] . " ")) .
            " LIMIT 1");
        if($review)
        {
            DB::execute("DELETE FROM places_review_vote WHERE review_id=".$review['id']);
            DB::execute("DELETE FROM places_review WHERE id=".$review['id']. " LIMIT 1");
            Wall::remove('places_review', $review_id, 0);

            Wall::deleteItemForUserByItem($review['place_id'], 'places', $review['user_id']);

            CPlacesTools::place_update_n_reviews($review['place_id']);
        }
    }

    static function delete_place($place_id, $admin = false)
    {
        global $g;
        global $g_user;

        $place = DB::row("SELECT p.* FROM places_place as p WHERE p.id=" . to_sql($place_id, 'Number') .
            ($admin ? "" : (" AND p.user_id = " . $g_user['user_id'] . " ")) .
            " LIMIT 1");
        if($place)
        {
            DB::query("SELECT * FROM places_place_image WHERE place_id=".$place['id'], 2);
            while($image = DB::fetch_row(2))
            {
                self::delete_place_image($image['id'], $admin);
            }

            DB::query("SELECT * FROM places_review WHERE place_id=".$place['id'], 2);
            while($review = DB::fetch_row(2))
            {
                self::delete_review($review['id'], $admin);
            }

            DB::execute("DELETE FROM places_place_vote WHERE place_id=".$place['id']);
            DB::execute("DELETE FROM places_place WHERE id=".$place['id']. " LIMIT 1");

            Wall::removeBySiteSection('places', $place['id']);
        }
    }
}

