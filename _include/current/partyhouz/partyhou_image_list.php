<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CpartyhouzpartyhouImageList extends CHtmlBlock
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
		
        $partyhou_id = get_param('partyhou_id');
		
        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
        if($partyhou)
		{
			$shift = intval(get_param('images_shift', 0));
			
			$html->setvar('partyhou_id', $partyhou['partyhou_id']);
			$html->setvar('partyhou_title', to_html($partyhou['partyhou_title']));
			$html->setvar('shift', $shift);
			
			$total_n_images = DB::result("SELECT COUNT(*) FROM partyhouz_partyhou_image WHERE partyhou_id=" . $partyhou['partyhou_id']);
			
			DB::query("SELECT * FROM partyhouz_partyhou_image WHERE partyhou_id=" . $partyhou['partyhou_id'] . " ORDER BY image_id DESC LIMIT " . to_sql($shift, 'Number') . ', 4');
			$n_images = 0;
			$active = get_param("active",0);
			while($image = DB::fetch_row())
			{
				if($n_images==$active)  $html->setvar("li_class","active");
                                else $html->setvar("li_class","");
				$html->setvar("n",$n_images);
				$html->setvar("image_thumbnail_b", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th_b.jpg");
				$html->setvar("image_thumbnail_s", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th_s.jpg");
				$html->setvar("image_file", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_b.jpg");
				$html->setvar("partyhou_image_id", $image['image_id']);
				$html->parse("image");
				++$n_images;
			}

			for($image_n = $n_images; $image_n != 4; ++$image_n)
			{
			// special image for entry/partyhou
                if($partyhou['partyhou_private']) $html->parse("no_image_entry");
                else $html->parse("no_image");
			}
			
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
            for($image_n = 0; $image_n != 4; ++$image_n)
            {
				$html->parse("no_image");
            }
		}
		
		parent::parseBlock($html);
	}
}
