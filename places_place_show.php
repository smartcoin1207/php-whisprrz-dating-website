<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
require_once("./_include/current/places/header.php");
require_once("./_include/current/places/sidebar.php");
require_once("./_include/current/places/place_show.php");
require_once("./_include/current/places/place_image_list.php");
require_once("./_include/current/places/tools.php");

class CPlaceShow extends CHtmlBlock
{
	function action()
	{
            $cmd = get_param('cmd');
            $id = get_param('id', 0);
            if ($cmd == 'delete') {
                CPlacesTools::delete_place($id);
                redirect('places.php');
            }
        }

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		$id = get_param('id');
		DB::query("SELECT * FROM places_place WHERE id=" . to_sql($id, 'Number') . " LIMIT 1");
		if($place = DB::fetch_row())
		{
			$n_reviews_per_page = 10;

			$html->setvar('place_id', $place['id']);
			$html->setvar('place_name', strcut(to_html($place['name']), 20));

			$sql_base = CPlacesTools::reviews_by_place_sql_base($place['id']);
			$n_reviews = CPlacesTools::count_from_sql_base($sql_base);

			$page = intval(get_param('page', 1));
			$n_pages = ceil($n_reviews / $n_reviews_per_page);
			$page = max(1, min($n_pages, $page));

			$html->setvar('page', $page);

			$review_id = intval(get_param('review_id'));
			if($review_id)
			{
				$reviews = CPlacesTools::retrieve_from_sql_base($sql_base);

				$need_page = 1;

				$review_n = 0;
				foreach($reviews as $review)
				{
					if($review['id'] == $review_id)
					{
						$need_page = floor($review_n / $n_reviews_per_page) + 1;

						break;
					}

					++$review_n;
				}

				redirect('places_place_show.php?id=' . $place['id'] .'&page=' . $need_page);
			}

			$reviews = CPlacesTools::retrieve_from_sql_base($sql_base, $n_reviews_per_page, ($page - 1) * $n_reviews_per_page);

			foreach($reviews as $review)
			{
				$rating = 0;

				DB::query("SELECT * FROM places_place_vote WHERE place_id = " . $place['id'] . " AND user_id = " . $review['user_id'] . " LIMIT 1");
				if($vote = DB::fetch_row())
				{
					$rating = $vote['rating'];
				}

				for($rating_n = 1; $rating_n <= 10; ++$rating_n)
					$html->setvar('place_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

				DB::query("SELECT * FROM user WHERE user_id = " . $review['user_id'] . " LIMIT 1");
				if($user = DB::fetch_row())
				{
					$html->setvar('review_user_name', $user['name']);
					$html->setvar('review_user_photo', $g['path']['url_files'] . User::getPhotoDefault($user['user_id'], "r"));
				}

				if($review['user_id'] == $g_user['user_id'])
				{
					$html->parse('review_editing_options', false);
				}
				else
				{
					$html->setblockvar('review_editing_options', '');
				}


				if($review['user_id'] == $g_user['user_id'] ||
				    DB::result("SELECT COUNT(id) FROM places_review_vote WHERE review_id = " . $review['id'] . " AND user_id = " . $g_user['user_id']))
				{
    				$html->setvar('review_vote_hand_active', 'style="display:none;"');
                    if($review['user_id'] == $g_user['user_id'])
                        $html->setvar('review_vote_hand_inactive', ' title="' . (isset($l['all']['places_you_can_not_vote_for_your_own_review']) ? $l['all']['places_you_can_not_vote_for_your_own_review'] : "") . '"');
                    else
                        $html->setvar('review_vote_hand_inactive', ' title="' . (isset($l['all']['places_you_have_already_voted_for_this_review']) ? $l['all']['places_you_have_already_voted_for_this_review'] : "") . '"');
				}
				else
				{
					$html->setvar('review_vote_hand_inactive', 'style="display:none;"');
					$html->setvar('review_vote_hand_active', '');
				}

				$html->setvar('review_id', $review['id']);
				$html->setvar('review_title', strcut(to_html($review['title']), 40));
				$html->setvar('review_text', to_html(Common::parseLinksSmile($review['text']),true,true));
				$html->setvar('review_date', Common::dateFormat($review['created_at'], 'places_review_date'));
				$html->setvar('review_n_votes', to_html($review['n_votes']));

				$html->parse('review');
			}

			$links = pager_get_pages_links($n_pages, $page);

			if($n_pages > 1)
			{
				if($page > 1)
				{
					$html->setvar('page_n', $page-1);
					$html->parse('pager_prev');
				}

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
		}
		else
		{
			redirect('home.php');
		}

		parent::parseBlock($html);
	}
}

$id = get_param('id');
DB::query("SELECT p.*, c.title, cn.*, st.*, ct.* ".
    "FROM places_place as p, places_category as c, geo_country as cn, geo_state as st, geo_city as ct ".
    "WHERE p.id=" . to_sql($id, 'Number') . " AND p.category_id = c.id AND ".
    "p.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
if($place = DB::fetch_row())
{
    global $g;
    $g['main']['title'] = html_meta_sanitize($g['main']['title'] . ' :: ' . $place['name'] . ', ' . $place['city_title'] . ', ' . $place['address']);
    $g['main']['description'] = html_meta_sanitize($place['about']);
}

$page = new CPlaceShow("", $g['tmpl']['dir_tmpl_main'] . "places_place_show.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$places_header = new CPlacesHeader("places_header", $g['tmpl']['dir_tmpl_main'] . "_places_header.html");
$page->add($places_header);
$places_sidebar = new CPlacesSidebar("places_sidebar", $g['tmpl']['dir_tmpl_main'] . "_places_sidebar.html");
$page->add($places_sidebar);
$places_place_show = new CPlacesPlaceShow("places_place_show", $g['tmpl']['dir_tmpl_main'] . "_places_place_show.html");
$page->add($places_place_show);
$places_place_image_list = new CPlacesPlaceImageList("places_place_image_list", $g['tmpl']['dir_tmpl_main'] . "_places_place_image_list.html");
$places_place_show->add($places_place_image_list);

include("./_include/core/main_close.php");

?>