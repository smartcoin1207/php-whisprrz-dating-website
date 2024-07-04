<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/places/header.php");
require_once("./_include/current/places/sidebar.php");
require_once("./_include/current/places/place_show.php");
require_once("./_include/current/places/place_image_list.php");
require_once("./_include/current/places/tools.php");

class CPlaceReviewEdit extends CHtmlBlock
{
	function action()
	{
		global $g_user;
		global $g;
		global $l;

		$cmd = get_param('cmd');
		if($cmd == 'save')
		{
			$id = get_param('id');
			DB::query("SELECT * FROM places_place WHERE id=" . to_sql($id, 'Number') . " LIMIT 1");
			if($place = DB::fetch_row())
			{
				$title = get_param('title');
				$text = get_param('text');

				if($title && $text)
				{
                    $text = Common::filter_text_to_db($text, false);
					DB::query("SELECT * FROM places_review WHERE place_id=" . $place['id'] . " AND user_id = " . $g_user['user_id'] . " LIMIT 1");
					if($review = DB::fetch_row())
					{
						DB::execute("UPDATE places_review SET title=".to_sql($title).", text=".to_sql($text).", updated_at = NOW() WHERE place_id=" . $place['id'] . " AND user_id = " . $g_user['user_id'] . " LIMIT 1");
						$review_id = $review['id'];
					}
					else
					{
						DB::execute("INSERT INTO places_review SET title=".to_sql($title).", text=".to_sql($text).", place_id=" . $place['id'] . ", user_id = " . $g_user['user_id'] . ", created_at = NOW(), updated_at = NOW()");
						$review_id = DB::insert_id();
                        Wall::add('places_review', $review_id);
                        Wall::addItemForUser($id, 'places', guid());
					}

                    $time = DB::result('SELECT NOW()');

					for($image_n = 1; $image_n <= 4; ++$image_n)
					{
						$name = "image_" . $image_n;
						if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
						{
							DB::execute("insert into places_place_image set place_id = " . $place['id'] . ", user_id = " . $g_user['user_id'] . ", created_at = " . to_sql($time, 'Text'));
							$image_id = DB::insert_id();

							$sFile_ = $g['path']['dir_files'] . "places_images/" . $image_id . "_";
							//popcorn modified s3 bucket places_images upload image 2024-05-06
							if(isS3SubDirectory($sFile_)) {
								$sFile_ = $g['path']['dir_files'] . "temp/places_images/" . $image_id . "_";
							}
							$im = new Image();
                            if ($im->loadImage($_FILES[$name]['tmp_name'])) {
								$im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
								@chmod($sFile_ . "src.jpg", 0777);
							}
							if ($im->loadImage($_FILES[$name]['tmp_name'])) {
								$im->resizeWH($g['places_image']['big_x'], $g['places_image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
								$im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
								@chmod($sFile_ . "b.jpg", 0777);

							}
							if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
								$im->resizeCropped($g['places_image']['thumbnail_x'], $g['places_image']['thumbnail_y'], $g['image']['logo'], 0);
								$im->saveImage($sFile_ . "th.jpg", $g['image']['quality']);
								@chmod($sFile_ . "th.jpg", 0777);
							}
							if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
								$im->resizeCropped($g['places_image']['thumbnail_big_x'], $g['places_image']['thumbnail_big_y'], $g['image']['logo'], 0);
								$im->saveImage($sFile_ . "th_b.jpg", $g['image']['quality']);
								@chmod($sFile_ . "th_b.jpg", 0777);

                                Wall::add('places_photo', $place['id'], false, $time, true);
							}
							$path = array($sFile_ . 'b.jpg', $sFile_ . 'th.jpg', $sFile_ . 'th_b.jpg', $sFile_ . 'src.jpg');
							Common::saveFileSize($path);

							//popcorn modified s3 bucket places_images upload image 2024-05-06
							if(isS3SubDirectory($g['path']['dir_files'] . "places_images/" . $image_id . "_")) {
								$file_sizes = array('b.jpg', 'th.jpg', 'th_b.jpg', 'src.jpg');
								foreach ($file_sizes as $key => $size) {
									if(file_exists($sFile_ . $size)) {
										custom_file_upload($sFile_ . $size, "places_images/" . $image_id . "_" . $size);
									}
								}
							}
						}
					}

					CPlacesTools::place_update_has_photos($place['id']);
					CPlacesTools::place_update_n_reviews($place['id']);

					redirect('places_place_show.php?id=' . $place['id'] . '&review_id=' . $review_id . '#jump_to_review_' . $review_id);
				}
			}
			else
				redirect('home.php');
		}
        else if($cmd == 'delete')
        {
        	$place_id = get_param('id');
        	$page = get_param('page', 1);

        	if($review = DB::row("SELECT * FROM places_review WHERE place_id=" . to_sql($place_id, 'Number') . " AND user_id = " . $g_user['user_id'] . " LIMIT 1"))
        	{
                if($review['user_id'] != $g_user['user_id'])
                    redirect('places.php');

                        CPlacesTools::delete_review($review['id']);

        		redirect('places_place_show.php?id=' . $place_id .'&page=' . $page);
        	}
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
			$rating = 0;

			DB::query("SELECT * FROM places_place_vote WHERE place_id = " . $place['id'] . " AND user_id = " . $g_user['user_id'] . " LIMIT 1");
			if($vote = DB::fetch_row())
			{
				$rating = $vote['rating'];
			}

			for($rating_n = 1; $rating_n <= 10; ++$rating_n)
			{
				$html->setvar('place_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');
			}

			$html->setvar('place_id', $place['id']);
			$html->setvar('place_name', to_html($place['name']));

			DB::query("SELECT * FROM places_review WHERE place_id=" . $place['id'] . " AND user_id = " . $g_user['user_id'] . " LIMIT 1");
			if($review = DB::fetch_row())
			{
				$html->parse('edit_title');

				$html->setvar('review_title', he(to_html($review['title'])));
				$html->setvar('review_text', str_replace("<br />","\r\n",$review['text']));
                $html->parse('review_delete');
			}
			else
			{
				$html->parse('create_title');
			}

			DB::query("SELECT * FROM places_place_image WHERE place_id=" . to_sql($id, 'Number') . " AND user_id=" . $g_user['user_id'] . " ORDER BY created_at ASC");
			while($image = DB::fetch_row())
			{
				$html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
				$html->setvar("image_file", $g['path']['url_files'] . "places_images/" . $image['id'] . "_b.jpg");
				$html->setvar("image_id", $image['id']);
				$html->parse("image");
			}
		}
		else
			redirect('home.php');

		parent::parseBlock($html);
	}
}

$page = new CPlaceReviewEdit("", $g['tmpl']['dir_tmpl_main'] . "places_review_edit.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$places_header = new CPlacesHeader("places_header", $g['tmpl']['dir_tmpl_main'] . "_places_header.html");
$page->add($places_header);
$places_sidebar = new CPlacesSidebar("places_sidebar", $g['tmpl']['dir_tmpl_main'] . "_places_sidebar.html");
$page->add($places_sidebar);
$places_place_show = new CPlacesPlaceShow("places_place_show", $g['tmpl']['dir_tmpl_main'] . "_places_place_show.html");
$places_place_show->show_rating = false;
$page->add($places_place_show);
$places_place_image_list = new CPlacesPlaceImageList("places_place_image_list", $g['tmpl']['dir_tmpl_main'] . "_places_place_image_list.html");
$places_place_image_list->show_add_button = false;
$places_place_show->add($places_place_image_list);

include("./_include/core/main_close.php");

?>