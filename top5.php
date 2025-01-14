<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = "./";
include("./_include/core/main_start.php");

class CTop5 extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;

        $sql = "SELECT * FROM user AS u
            WHERE rating != 0
                AND is_photo='Y'
                AND u.user_id != " . to_sql(guid()) . "
                AND u.hide_time = 0 " . $g['sql']['your_orientation'] . "
            ORDER BY rating DESC
            LIMIT 5";

		DB::query($sql);
		if (DB::num_rows() < 5)
		{
			$html->parse("notop5", true);
		}
		else
		{
			$i = 0;
			while ($row = DB::fetch_row())
			{
				$i++;
				$html->setvar("name_" . $i, $row['name']);
				$html->setvar("rating_" . $i, $row['rating']);
				$html->setvar("photo_" . $i, User::getPhotoDefault($row['user_id'],"r"));
			}
			$html->parse("top5", true);
		}

		parent::parseBlock($html);
	}
}

$page = new CTop5("", $g['tmpl']['dir_tmpl_main'] . "top5.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$users_new = new CHtmlBlock("users_new", null);
$page->add($users_new);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);

include("./_include/core/main_close.php");

?>
