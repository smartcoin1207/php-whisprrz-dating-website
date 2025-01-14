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

class CGallery_AdminEditComment extends CHtmlBlock
{
	function action()
	{
		global $g_user;
		parent::action();

		$action=get_param("action", "");

		if($action=="savecomment")
		{
			$id=intval(get_param("id", ""));
			if($id!="")
			{
				  $email = get_param('email',"");
				  $website = get_param('website',"");
				  $comment = get_param('comment',"");

				  $website=str_replace("http://", "", $website);

				$sql = "UPDATE `gallery_comments` SET  email = " . to_sql($email) . ", website = " . to_sql($website) . ", comment = " . to_sql($comment) . " WHERE id = $id" . "";

				DB::execute($sql);
				redirect("gallery_admin_comments.php");
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g_user;

		$cid=intval(get_param("id", 0));
		if($cid==0)
		{
			redirect("gallery_admin_comments.php");
		}

		DB::query("SELECT c.id, i.title, i.filename, a.folder, a.title AS albumtitle,  c.website,  c.comment, c.email FROM gallery_comments AS c, gallery_images AS i, gallery_albums AS a WHERE c.imageid = i.id AND i.albumid = a.id AND i.user_id = ".$g_user["user_id"]." AND c.id = ".$cid."  ORDER BY c.id DESC ");

		$row = DB::fetch_row();

		$html->setvar("comment_id", $row["id"]);
		$html->setvar("comment_text", $row["comment"]);

		$html->setvar("comment_author_website", "http://".$row["website"]);
		$html->setvar("comment_author_email", $row["email"]);

		parent::parseBlock($html);
	}
}


$page = new CGallery_AdminEditComment("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_admin_editcomment.html");
$galleryMenu = new CMenuSection('gallery_menu', $g['tmpl']['dir_tmpl_main'] . "_gallery_menu.html");
$galleryMenu->setActive('gallery_comments');
$page->add($galleryMenu);
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>
