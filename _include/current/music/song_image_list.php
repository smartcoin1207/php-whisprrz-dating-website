<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CMusicSongImageList extends CHtmlBlock
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
		
        $song_id = get_param('song_id');
		
		$song = DB::row("SELECT * FROM music_song WHERE song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
		if($song)
		{
			$shift = intval(get_param('images_shift', 0));
			
			$html->setvar('song_id', $song['song_id']);
			$html->setvar('song_title', to_html(he($song['song_title'])));
			$html->setvar('shift', $shift);
			
			$total_n_images = DB::result("SELECT COUNT(*) FROM music_song_image WHERE song_id=" . $song['song_id']);
			
			DB::query("SELECT * FROM music_song_image WHERE song_id=" . $song['song_id'] . " ORDER BY image_id DESC LIMIT " . to_sql($shift, 'Number') . ', 4');
			$n_images = 0;
			$n_images = 0;
			$active = get_param("active",0);
			while($image = DB::fetch_row())
			{
				if($n_images==$active)  $html->setvar("li_class","active");
                                else $html->setvar("li_class","");
				$html->setvar("n",$n_images);
				$html->setvar("image_thumbnail_b", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th_b.jpg");
				$html->setvar("image_thumbnail_s", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th_s.jpg");
				$html->setvar("image_file", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_b.jpg");
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
