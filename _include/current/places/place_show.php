<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CPlacesPlaceShow extends CHtmlBlock
{
	var $show_rating = true;

	function action()
	{
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

		$id = get_param('id');
		DB::query("SELECT p.*, c.title, cn.*, st.*, ct.* ".
			"FROM places_place as p, places_category as c, geo_country as cn, geo_state as st, geo_city as ct ".
			"WHERE p.id=" . to_sql($id, 'Number') . " AND p.category_id = c.id AND ".
			"p.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
		if($place = DB::fetch_row())
		{
			$rating = $place['rating'];

			for($rating_n = 1; $rating_n <= 10; ++$rating_n)
				$html->setvar('place_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');

		    if(DB::result("SELECT COUNT(id) FROM places_place_vote WHERE place_id = ".$place['id']." AND user_id = ".$g_user['user_id']." LIMIT 1"))
    		    $html->setvar('place_rating_caps', ', readOnly:true');

			global $p;
			// nothign show on edit page
			if($p!="places_review_edit.php") {
            if(DB::result("SELECT COUNT(id) FROM places_review WHERE place_id = ".$place['id']." AND user_id = ".$g_user['user_id']." LIMIT 1"))
                $html->parse('place_edit_review', false);
            else
                $html->parse('place_create_review', false);
    		}

			$html->setvar('place_id', $place['id']);
			$html->setvar('place_category_id', to_html($place['category_id']));
			$html->setvar('place_category_title', to_html(l($place['title'], false, 'places_category')));

			// change length for edit page
			global $p;
			if($p=="places_review_edit.php" || $p=="places_place_add_photos.php") $place_name_width = 1000;
			else $place_name_width = 21;

			$site_name = str_ireplace("http://", "", $place['site']);
			if(strripos($site_name, "/") === strlen($site_name) - 1)
                $site_name = substr($site_name, 0, strlen($site_name) - 1);

			$html->setvar('place_name_cut', he(strcut(to_html($place['name']), $place_name_width)));
			$html->setvar('place_name', to_html(he($place['name'])));
			$html->setvar('place_phone', to_html($place['phone']));
			$html->setvar('place_site', strcut(to_html($site_name), 40));
			$html->setvar('place_site_url', to_html($place['site']));
			$html->setvar('place_about', to_html(Common::parseLinks($place['about'])));
			$html->setvar('place_address', to_html($place['address']));
			$html->setvar('place_city_id', to_html($place['city_id']));
			$html->setvar('place_city_title', to_html($place['city_title']));
			$html->setvar('place_state_id', to_html($place['state_id']));
			$html->setvar('place_state_title', to_html($place['state_title']));
			$html->setvar('place_country_id', to_html($place['country_id']));
			$html->setvar('place_country_title', to_html($place['country_title']));

			if($this->show_rating)
			{
				$html->parse('place_rating');
			}
			if ($place['user_id'] == guid()
                && $p == 'places_place_show.php'
                && !empty($id)) {
                $html->setvar('plase_name_length', 21);
                $html->parse('plase_edit_js');
                $html->parse('plase_name_edit');
                $html->parse('plase_about_edit');
                $html->parse('place_delete');
            } else {
                $html->parse('plase_name_edit_no');
                $html->parse('plase_about_edit_no');
            }

			if($place['phone'])
				$html->parse('place_phone');
			if($place['site'])
				$html->parse('place_site');
		}
		else
		{
			redirect('home.php');
		}

		parent::parseBlock($html);
	}
}
