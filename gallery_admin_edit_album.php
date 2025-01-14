<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
payment_check('gallery_edit');
if (isset($g['options']['gallery']) and $g['options']['gallery'] == "N") redirect('home.php');

class CGallery_AdminEditAlbum extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		$album_id=intval(get_param("album_id", 0));
		if (DB::result("SELECT id FROM `gallery_albums` WHERE `id`=".$album_id . " AND user_id=" . $g_user['user_id'] . "") == 0) {
			redirect('home.php');
		}

		$action=get_param("action", "");

		if($action=="save")
		{
			$album_title  = htmlentities(get_param("albumtitle", ""),ENT_QUOTES,"UTF-8");
			$album_desc   = htmlentities(get_param("albumdesc", ""),ENT_QUOTES,"UTF-8");
			$album_thumb  = get_param("thumb", "");
            $album_access = get_param("access", "");
			$album_id     = intval(get_param("album_id", 0));

			if($album_id==0)
				return;

			$sql = "UPDATE `gallery_albums`
                       SET `access` = " . to_sql($album_access)
                      . ", `title` = " . to_sql($album_title)
                      . ", `desc` = " . to_sql($album_desc)
                      . ", `thumb` = " . to_sql($album_thumb)
                . " WHERE `id` = $album_id";

			DB::execute($sql);

            Wall::UpdateAccessPics($album_id, $album_access);
            // IMAGES


			$total_images=get_param("totalimages", 0);
			for($i=0; $i<$total_images; $i++)
			{
				$tmp_title=get_param("$i-title", "");
				$tmp_desc=get_param("$i-desc", "");
				$tmp_id=intval(get_param("$i-id", ""));
                if ($tmp_desc == l('description')) $tmp_desc = '';
				$sql = "UPDATE gallery_images SET `title` = " . to_sql($tmp_title) . ", `desc` = " . to_sql($tmp_desc) . " WHERE `id` = $tmp_id";
				DB::execute($sql);
			}
			redirect("gallery_admin_edit_album_selection.php");
		}


		if($action=="deleteimage")
		{
			$image_id=intval(get_param("image_id", 0));

            if(Gallery::imageDelete($image_id, guid()) == 'album_empty') {
                redirect('gallery_admin_edit_album_selection.php');
            }
		}


		parent::action();
	}
	function init()
	{
	 	parent::init();
		global $g;
		global $g_user;
	}
	function parseBlock(&$html)
	{
		global $g_user;

		global $g;
		$html->setvar("gallery_album_title_length",$g['options']['gallery_album_title_length']);
		$html->setvar("gallery_album_description_length",$g['options']['gallery_album_description_length']);
		$html->setvar("gallery_image_title_length",$g['options']['gallery_image_title_length']);
		$html->setvar("gallery_image_description_length",$g['options']['gallery_image_description_length']);



		$album_id=intval(get_param("album_id", 0));
		if($album_id==0)
		{
			redirect("gallery_admin_edit_album_selection.php");
		}

		DB::query("SELECT * FROM `gallery_albums` WHERE `id`=".$album_id);
		$row=DB::fetch_row();

        if ($row['folder'] == 'chat_system') {
            redirect("gallery_admin_edit_album_selection.php");
        }

		$html->setvar("album_title", $row["title"]);
		$html->setvar("album_desc", $row["desc"]);
		$html->setvar("album_id", $row["id"]);
        $set_access = array(
            'public' =>  l('Public'),
            'friends' =>  l('Friends only'),
            'private' =>  l('Private'),
        );
		$html->setvar("set_access", h_options($set_access, get_param("access", $row["access"])));

		DB::query("SELECT i.*, a.folder, a.thumb FROM gallery_images AS i, gallery_albums AS a WHERE i.albumid = a.id AND albumid = ".$album_id." ORDER BY id DESC ");
		$count=DB::num_rows();

		$html->setvar("num_images", $count);

		for($i=0; $i<$count; $i++)
		{
			if ($row = DB::fetch_row())
			{
				$thumb_href= $g['dir_files'] . "gallery/thumb/".$row['user_id']."/".$row['folder']."/".$row['filename'];
				$html->setvar("img_fullpath", $thumb_href);

				$html->setvar("img_filename", $row['filename']);
				$html->setvar("img_title", $row['title']);

				$html->parse("thumb", true);


				$html->setvar("image_id", $row['id']);
                $description = (trim($row['desc']) == '') ? l('description') : $row['desc'];
				$html->setvar("img_desc", $description);
				$html->setvar("current_image", $i);

				$html->parse("image", true);
			}

		}

		if($count>0) {
			$html->parse("images");
			$html->parse("images_select");
		}
		parent::parseBlock($html);
	}
}


$page = new CGallery_AdminEditAlbum("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_admin_edit_album.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$galleryMenu = new CMenuSection('gallery_menu', $g['tmpl']['dir_tmpl_main'] . "_gallery_menu.html");
$galleryMenu->setActive('edit_album');
$page->add($galleryMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>
