<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include("./_include/current/gallery_functions.php");

payment_check('gallery_view');
if (!Common::getOption('gallery')) Common::toHomePage();

$album_id = intval(get_param('album_id', '0'));
if ($album_id == 0) {
	redirect('gallery_index.php');
}

$row = DB::row("SELECT `access`, `user_id`, `folder` FROM `gallery_albums` WHERE id = " . to_sql($album_id, 'Numeric'));
if (guid() != $row['user_id']){
    if ($row['access'] == 'private' || ($row['access'] == 'friends' && !User::isFriend(guid(), $row['user_id']))) {
        redirect('gallery_index.php');
    }
}

if ($row['folder'] == 'chat_system') {
    redirect('gallery_index.php');
}

class CGallery_Album extends CHtmlList
{
	function action()
	{
		global $g_user;

        parent::action();
		$album_id = get_param("album_id", "0");
		$album_id = intval($album_id);
		DB::execute("UPDATE gallery_albums SET views=(views+1) WHERE id = " . $album_id . "");
 	}

	function init()
	{
	 	parent::init();
		global $g;
		global $g_user;
		global $l;

		$this->m_on_page = 42;
		$this->m_sql_count = "SELECT COUNT(a.id) FROM (gallery_albums AS a LEFT JOIN gallery_images AS i ON a.id=i.albumid AND a.show='1' AND i.show=1) " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT a.*, i.*
			FROM (gallery_albums AS a LEFT JOIN gallery_images AS i ON a.id=i.albumid AND a.show='1' AND i.show=1)
			" . $this->m_sql_from_add . "
		";

	}
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
		global $l;

		global $g;
		$html->setvar("gallery_album_title_length", $g['options']['gallery_album_title_length']);
		$html->setvar("gallery_album_description_length", $g['options']['gallery_album_description_length']);

		$album_id=get_param("album_id", "0");
		$album_id=intval($album_id);

		if($album_id == 0)
		{
			redirect("gallery_index.php");
		}

		DB::query("SELECT user_id, title FROM `gallery_albums` WHERE `id`=".$album_id."");
		$row=DB::fetch_row();
		if(!is_array($row))
		{
			redirect("gallery_index.php");
		}

		$u_id=intval($row["user_id"]);

		$album_title=$row["title"];
		$album_desc=DB::result("SELECT `desc` FROM `gallery_albums` WHERE `id`=".$album_id."");

		if($u_id==$g_user["user_id"])
		{
			$html->setvar("album_id", $album_id);
            $html->parse("delimiter", false);
			$html->parse("album_edit", false);
			$html->setvar("ttl_cursor","cursor:pointer;");
		}

		$sql="
			SELECT user_id, name FROM user
			WHERE user_id=".$u_id;

		DB::query($sql);
		$row=DB::fetch_row();

		$html->setvar("album_user_id", $row['user_id']);


		$html->setvar("album_name", $album_title);

		$html->setvar("album_desc", $album_desc);
		if (trim($album_desc)) {
            $html->parse("delimiter", false);
            $html->parse("albim_description_oryx", false);
        } else {
            if ($u_id == $g_user['user_id']) {
               $html->parse("albim_description_oryx", false);
            }
        }


		$html->setvar("user_id", $u_id);
		$html->setvar("name", $row['name']);
		$html->setvar("name_prof", $row['name']);

		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
    {
		global $g;
		global $l;
		global $g_user;

		if($row["id"]=="")	return;

		$html->setvar("img_title", $row['title']);
		$html->setvar("img_desc", $row['desc']);
		$html->setvar("img_id", $row['id']);

		$thumb_href="gallery/thumb/".$row['user_id']."/".$row['folder']."/".$row['filename'];
		$html->setvar("img_thumb_path", $thumb_href);


		$html->parse("images", true);

// SHOW NOTHING BLOCKS

		if($i==$last) {
		$add = (42 - $last) % 7;
		if($add>0) {
		for($n=0;$n<$add;$n++) $html->parse("no_images", true);
		}
		}

// SHOW NOTHING BLOCKS

    }
}

$page = new CGallery_Album("gallery_albums_all", $g['tmpl']['dir_tmpl_main'] . "gallery_album.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$page->m_sql_where = "a.show !=0 AND a.id=".$album_id."";

include("./_include/core/main_close.php");

?>