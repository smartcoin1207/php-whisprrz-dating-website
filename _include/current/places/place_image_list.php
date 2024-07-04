<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CPlacesPlaceImageList extends CHtmlBlock
{
	var $show_add_button = true;
	
	function action()
	{
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
			$shift = intval(get_param('images_shift', 0));
			$show_add_button = intval(get_param('show_add_button', $this->show_add_button));			
			
			$html->setvar('place_id', $place['id']);
			$html->setvar('place_name', to_html(he($place['name'])));
			$html->setvar('shift', $shift);
			$html->setvar('show_add_button', $show_add_button);
			
			$total_n_images = DB::result("SELECT COUNT(*) FROM places_place_image WHERE place_id=" . to_sql($id, 'Number'));
			
			DB::query("SELECT * FROM places_place_image WHERE place_id=" . to_sql($id, 'Number') . " ORDER BY id DESC LIMIT " . to_sql($shift, 'Number') . ', 4');
			$n_images = 0;
			while($image = DB::fetch_row())
			{
				$html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
				$html->setvar("image_file", $g['path']['url_files'] . "places_images/" . $image['id'] . "_b.jpg");
				$html->parse("image");
				++$n_images;
			}

			for($image_n = $n_images; $image_n != 4; ++$image_n)
			{
				$html->parse("no_image");
			}
			
			if($show_add_button)
				$html->parse("add_button");
			
			if($shift > 0)
				$html->parse("left_active");
			else
				$html->parse("left_inactive");

			if($total_n_images - $shift > 4)
				$html->parse("right_active");
			else
				$html->parse("right_inactive");
		}
		else
		{
			redirect('home.php');
		}
		
		parent::parseBlock($html);
	}
}
