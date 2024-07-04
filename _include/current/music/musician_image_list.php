<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CMusicMusicianImageList extends CHtmlBlock
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
		
        $song_id = intval(get_param('song_id', 0));
        $song = null;
        if($song_id)
            $song = DB::row("SELECT * FROM music_song WHERE song_id = " . to_sql($song_id, 'Number') . " LIMIT 1");
        
        if(!$song)
            $musician_id = get_param('musician_id');
        else
            $musician_id = $song['musician_id'];
		
		$musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
		if($musician)
		{
			$shift = intval(get_param('images_shift', 0));
			
			$html->setvar('musician_id', $musician['musician_id']);
			$html->setvar('musician_name', to_html($musician['musician_name']));
			$html->setvar('shift', $shift);
			
			$total_n_images = DB::result("SELECT COUNT(*) FROM music_musician_image WHERE musician_id=" . $musician['musician_id']);
			
			DB::query("SELECT * FROM music_musician_image WHERE musician_id=" . $musician['musician_id'] . " ORDER BY image_id DESC LIMIT " . to_sql($shift, 'Number') . ', 4');
			$n_images = 0;
			$active = get_param("active",0);
			while($image = DB::fetch_row())
			{
				if($n_images==$active)  $html->setvar("li_class","active");
                                else $html->setvar("li_class","");
				$html->setvar("n",$n_images);
				$html->setvar("image_thumbnail_b", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th_b.jpg");
				$html->setvar("image_thumbnail_s", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th_s.jpg");
				$html->setvar("image_file", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_b.jpg");
				$html->parse("image");
				++$n_images;
			}

			for($image_n = $n_images; $image_n != 4; ++$image_n)
			{
				$html->parse("no_image");
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
