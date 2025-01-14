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

class CGallery_AdminComments extends CHtmlBlock
{
    function deleteComment($id)
    {
        Gallery::commentDelete($id, guid());
    }

	function action()
	{
		parent::action();

		$action=get_param("action", "");

		if($action=="deletecomments")
		{
            // delete only comments to own images!!!
			$ids=get_param("ids", "");
			if($ids!="")
			{
				$total = count($ids);
				if ($total > 0)
				{
				  foreach ($ids as $id) {
					$this->deleteComment($id);
				  }
				}
			}
			$id=intval(get_param("id", ""));
			if($id!="")
			{
				$this->deleteComment($id);
			}
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
		DB::query("SELECT c.id, i.title, i.filename, a.folder, a.title AS albumtitle, c.name, c.website, (c.date) AS date, c.comment, c.email FROM gallery_comments AS c, gallery_images AS i, gallery_albums AS a WHERE c.imageid = i.id AND i.albumid = a.id AND i.user_id = ".$g_user["user_id"]." ORDER BY c.id DESC ");
		$count=DB::num_rows();


		for($i=0; $i<$count; $i++)
		{
			if ($row = DB::fetch_row())
			{
				$html->setvar("comment_id", $row["id"]);
				$html->setvar("comment_album_title", strip_tags($row["albumtitle"]));
				$html->setvar("comment_image_filename", strip_tags($row["filename"]));
				$html->setvar("comment_author_name", strip_tags($row["name"]));
				$html->setvar("comment_date", Common::dateFormat($row["date"], 'user_comment_date'));
				$html->setvar("comment_text", strip_tags($row["comment"]));

				$html->setvar("comment_author_website", "http://".strip_tags($row["website"]));
				$html->setvar("comment_author_email", strip_tags($row["email"]));

				$str=wordwrap(strip_tags($row["comment"]), 75, '\n');
				$lines = explode('\n', $str);
				$str = implode('%0D%0A', $lines);
				$str = $row["name"]." commented on ".$row["filename"]." in the album ".$row["albumtitle"].": %0D%0A%0D%0A" . $str;

				$html->setvar("comment_reply_body", strip_tags($str));


				$html->parse("comment", true);
			}
		}

		parent::parseBlock($html);
	}
}


$page = new CGallery_AdminComments("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_admin_comments.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$galleryMenu = new CMenuSection('gallery_menu', $g['tmpl']['dir_tmpl_main'] . "_gallery_menu.html");
$galleryMenu->setActive('gallery_comments');
$page->add($galleryMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>