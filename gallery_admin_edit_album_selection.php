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

class CGallery_AdminEditAlbumSelection extends CHtmlBlock
{
	function action()
	{
		parent::action();

		$action=get_param("action", "");

		if($action=="deletealbum")
		{
			$album_id=intval(get_param("album_id", 0));

			if($album_id==0)
				return;

            Gallery::albumDelete($album_id, guid());
		}
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

        $where = CGalleryAlbums::getCustomWhere('user_id = ' . to_sql($g_user['user_id']), '');
		DB::query('SELECT * FROM gallery_albums WHERE ' . $where . ' ORDER BY id DESC');
        
		$count=DB::num_rows();


		for($i=0; $i<$count; $i++)
		{
			if ($row = DB::fetch_row())
			{
				$html->setvar("album_id", $row["id"]);
				$html->setvar("album_title", $row["title"]);

				$thumb_href = $g['dir_files'] . "gallery/thumb/".$row['user_id']."/".$row['folder']."/".$row['thumb'];
				$html->setvar("album_thumb", $thumb_href);

                $sql = 'SELECT COUNT(*) FROM gallery_images
                    WHERE albumid = ' . to_sql($row['id']);
                $html->setvar('img_count', DB::result($sql, 0, 2));

				$html->parse("album", true);
			}
		}



		parent::parseBlock($html);
	}
}


$page = new CGallery_AdminEditAlbumSelection("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_admin_edit_album_selection.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$galleryMenu = new CMenuSection('gallery_menu', $g['tmpl']['dir_tmpl_main'] . "_gallery_menu.html");
$galleryMenu->setActive('edit_album');
$page->add($galleryMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>