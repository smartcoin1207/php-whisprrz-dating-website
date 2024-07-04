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

class CPlaceShow extends CHtmlBlock
{
	function action()
	{
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		$n_results_per_page = 10;

		$category = null;
		$category_id = get_param('category_id');
		if($category_id)
			$category = DB::row("SELECT * FROM places_category WHERE id = " . to_sql($category_id, 'Number') . " LIMIT 1");

        $city = null;
		$city_id = get_param('city_id');
        if($city_id)
            $city = DB::row("SELECT * FROM geo_city WHERE city_id = " . to_sql($city_id, 'Number') . " LIMIT 1");

		$what = get_param('what', null);
		$where = get_param('where', null);

        $sort_by = get_param('sort', 'rating');
        $html->setvar('sort', $sort_by);

        if($what)
        {
            $html->setvar('search_what', strcut(to_html($what), 16));
        }
		else if($category)
		{
			$html->setvar('search_what', strcut(to_html((l($category['title'], false, 'places_category'))), 16));
		}
		else
		{
			$html->parse('search_places');
		}

        $sql_base = CPlacesTools::best_places_sql_base(
            $g_user['city_id'], $g_user['state_id'], $g_user['country_id'],
            $category ? $category['id'] : null,
            $city ? $city['city_id'] : null,
            $what,
            $where,
            $sort_by);

        $n_results = CPlacesTools::count_from_sql_base($sql_base);

        $page = intval(get_param('page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('search_n_results', $n_results);
        $html->setvar('page', $page);

		$location_title = null;
        if($where)
		{
			$location_title = to_html($where);
		}
        else if($city)
        {
            $location_title = $city['city_title'];
        }

		if($location_title)
		{
            $html->parse('search_in');
            $html->setvar('search_where', strcut($location_title, 16));
		}

        $html->setvar('search_params', 'category_id=' . $category['id'] . '&where=' . $where . '&what=' . $what.'&');

        if($sort_by == 'rating')
            $html->parse('sort_by_rating_active');
        else
            $html->parse('sort_by_rating');

        if($sort_by == 'recent')
            $html->parse('sort_by_recent_active');
        else
            $html->parse('sort_by_recent');

        if($sort_by == 'reviews')
            $html->parse('sort_by_reviews_active');
        else
            $html->parse('sort_by_reviews');

        if($sort_by == 'name')
            $html->parse('sort_by_name_active');
        else
            $html->parse('sort_by_name');

        $places = CPlacesTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        $place_n = 1;
        foreach($places as $place)
        {
            $html->setvar('place_name', strcut(to_html($place['name']), 16));
            $html->setvar('place_id', to_html($place['id']));
            $html->setvar('place_city_id', $place['city_id']);
            $html->setvar('place_city_title', strcut(to_html($place['city_title']), 10));
            $html->setvar('place_n_reviews', $place['n_reviews']);

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

            DB::query("SELECT * FROM places_place_image WHERE place_id=" . $place['id'] . " ORDER BY created_at ASC LIMIT 1");
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

            if($place_n < count($places))
            {
                $html->parse('place_line', false);
            }
            else
            {
                $html->setblockvar("place_line", "");
            }

            if(guid() == $place['user_id']) {
                $html->parse('place_delete', false);
            }

            $html->parse('place');

            ++$place_n;
        }

        if($n_pages > 1)
        {
            if($page > 1)
            {
                $html->setvar('page_n', $page-1);
                $html->parse('pager_prev');
            }

            $links = pager_get_pages_links($n_pages, $page);

            foreach($links as $link)
            {
                $html->setvar('page_n', $link);

                if($page == $link)
                {
                    $html->parse('pager_link_active', false);
                    $html->setblockvar('pager_link_not_active', '');
                }
                else
                {
                    $html->parse('pager_link_not_active', false);
                    $html->setblockvar('pager_link_active', '');
                }
                $html->parse('pager_link');
            }

            if($page < $n_pages)
            {
                $html->setvar('page_n', $page+1);
                $html->parse('pager_next');
            }

            $html->parse('pager');
        }

		parent::parseBlock($html);
	}
}

$page = new CPlaceShow("", $g['tmpl']['dir_tmpl_main'] . "places_search.html");
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