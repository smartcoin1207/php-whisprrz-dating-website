<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

payment_check('adv_add');

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

		$orig_cat_id = get_param('orig_cat_id');
		DB::query("select * from adv_cats where id = " . to_sql($orig_cat_id) . " LIMIT 1");
		$orig_adv_cat = DB::fetch_row();
		$orig_images = array();

		if($orig_adv_cat)
		{
			$orig_id = get_param('orig_id');

			DB::query("select * from adv_" . $orig_adv_cat['eng'] . " where id = " . to_sql($orig_id) . " LIMIT 1");
			$orig_adv = DB::fetch_row();

			if(isset($orig_adv))
			{
				if($orig_adv['user_id'] != get_session('user_id'))
				{
					unset($orig_adv);
				}
				else
				{
					DB::query("select * from adv_images where adv_cat_id = " . $orig_adv_cat['id'] . " and adv_id = " . $orig_adv['id'] . " LIMIT 4");
					$image_n = 1;
					while($image = DB::fetch_row())
					{
						$orig_images[$image_n] = $image;
						++$image_n;
					}
				}
			}
		}

		$cat_id = get_param('cat_id');

		if($cat_id)
		{
			DB::query("select * from adv_cats where id = " . to_sql($cat_id) . " LIMIT 1");
			$adv_cat = DB::fetch_row();

			if($adv_cat)
			{
				$to_sql = array();

				$to_sql["user_id"] = get_session('user_id');
				$to_sql["subject"] = to_sql(get_param('subject'));
				$to_sql["cat_id"] = to_sql(get_param('cat_id'));
				$to_sql["razd_id"] = to_sql(get_param('razd_select_' . $adv_cat['id']));
				switch($adv_cat['eng'])
				{
					case 'jobs':
						$to_sql["telecommute"] = get_param('telecommute') ? 1 : 0;
						$to_sql["contract"] = get_param('contract') ? 1 : 0;
						$to_sql["internship"] = get_param('internship') ? 1 : 0;
						$to_sql["part_time"] = get_param('part_time') ? 1 : 0;
						$to_sql["non_profit"] = get_param('non_profit') ? 1 : 0;
						break;
					case 'housting':
						$to_sql["rent"] = to_sql(get_param('rent'));
						$to_sql["br"] = to_sql(get_param('br'));
						break;
					case 'myspace':
						$to_sql["age"] = to_sql(get_param('age'));
						break;
					case 'services':
						$to_sql["price"] = to_sql(get_param('price'));
						break;
					case 'casting':
						$to_sql["age"] = to_sql(get_param('age'));
						break;
					case 'personals':
						$to_sql["age"] = to_sql(get_param('age'));
						break;
					case 'sale':
						$to_sql["price"] = to_sql(get_param('price'));
						break;
					case 'cars':
						$to_sql["price"] = to_sql(get_param('price'));
						break;
				}
                $to_sql["body"] = to_sql(Common::filter_text_to_db(get_param('body'), false));

				foreach($to_sql as $value)
				{
					if($value === '')
						redirect('adv.php');
				}

				$to_sql_statements = array();
				foreach($to_sql as $key => $value)
				{
					$to_sql_statements[] = $key . ' = ' . $value;
				}

				if(!isset($orig_adv) || $orig_adv_cat['id'] != $adv_cat['id'])
				{
					$sql = "insert into adv_" . $adv_cat['eng'] . ' set ';

					$sql .= implode(', ', $to_sql_statements);

					$adv_approved = Common::isOptionActive('craigslicks_approval') ? 0 : 1 ;
					$sql .= ", approved = ". to_sql($adv_approved, 'Number');

					DB::execute($sql);
					$adv_id = DB::insert_id();

                    if(!isset($orig_adv)) {
                        CStatsTools::count('ads_published');
                    }
				}
				else
				{
					$adv_id = $orig_adv['id'];
				}

				global $g;

				for($image_n = 1; $image_n <= 4; ++$image_n)
				{
					$name = "image_" . $image_n;
					if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
					{
						if(isset($orig_images[$image_n]))
						{
							$image_id = $orig_images[$image_n]['id'];
						}
						else
						{
							DB::execute("insert into adv_images set adv_cat_id = " . $adv_cat['id'] . ", adv_id = " . $adv_id);
							$image_id = DB::insert_id();
						}

						$sFile_ = $g['path']['dir_files'] . "adv_images/" . $image_id . "_";
						//popcorn modified s3 bucket adv_images image upload 2024-05-06
						if(isS3SubDirectory($sFile_)) {
							$sFile_ = $g['path']['dir_files'] . "temp/adv_images/" . $image_id . "_";
						}

                        $imagesFiles = array($sFile_ . 'b.jpg', $sFile_ . 'th.jpg', $sFile_ . 'th_s.jpg', $sFile_ . 'src.jpg');
                        //Common::existsFilesUserDelete($imagesFiles);
						$im = new Image();
						if ($im->loadImage($_FILES[$name]['tmp_name'])) {
                            Common::existsOneFileUserDelete($sFile_ . 'b.jpg');
							$im->resizeWH($g['adv_image']['big_x'], $g['adv_image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
							$im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
							@chmod($sFile_ . "b.jpg", 0777);
						}
						if ($im->loadImage($_FILES[$name]['tmp_name'], $g['image']['quality'])) {
                            Common::existsOneFileUserDelete($sFile_ . 'th.jpg');
							$im->resizeCropped($g['adv_image']['thumbnail_x'], $g['adv_image']['thumbnail_y'], $g['image']['logo'], 0);
							$im->saveImage($sFile_ . "th.jpg", $g['image']['quality']);
							@chmod($sFile_ . "th.jpg", 0777);
						}
						if ($im->loadImage($_FILES[$name]['tmp_name'], $g['image']['quality'])) {
                            Common::existsOneFileUserDelete($sFile_ . 'th_s.jpg');
							$im->resizeCropped($g['adv_image']['thumbnail_small_x'], $g['adv_image']['thumbnail_small_y'], $g['image']['logo'], 0);
							$im->saveImage($sFile_ . "th_s.jpg", $g['image']['quality']);
							@chmod($sFile_ . "th_s.jpg", 0777);
						}
                        if ($im->loadImage($_FILES[$name]['tmp_name'])) {
                            Common::existsOneFileUserDelete($sFile_ . 'src.jpg');
							$im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
							@chmod($sFile_ . "src.jpg", 0777);
						}
                        Common::saveFileSize($imagesFiles);

						//popcorn modified s3 bucket adv_images image upload 2024-05-06
						if(isS3SubDirectory($g['path']['dir_files'] . "adv_images/" . $image_id . "_")) {
							$file_sizes = array('b.jpg', 'th.jpg', 'th_s.jpg', 'src.jpg');

							foreach ($file_sizes as $key => $size) {
								if(file_exists($sFile_ . $size)) {
									custom_file_upload($sFile_ . $size, "adv_images/" . $image_id . "_" . $size);
								}
							}
						}

					}
				}

				if(isset($orig_adv) && $orig_adv_cat['id'] == $adv_cat['id'])
				{
					$sql = "update adv_" . $adv_cat['eng'] . ' set ';

					$sql .= implode(', ', $to_sql_statements);
					$sql .= ' where id = ' . $orig_adv['id'] . ' limit 1';

					DB::execute($sql);

					redirect('adv_good.php?cat=' . $adv_cat['eng'] . '&id=' . $orig_adv['id']);
				}

				if(isset($orig_adv))
				{
					DB::execute("delete from adv_" . $orig_adv_cat['eng'] . ' where id=' . $orig_adv['id'] . ' limit 1');
					DB::query("update adv_images set adv_cat_id = " . $adv_cat['id'] . " and adv_id = " . $adv_id);
				}

				redirect('adv_add_done.php?cat=' . $adv_cat['eng'] . '&id=' . $adv_id);
			}
		}

		redirect('adv.php');
		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv_add_done.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
