<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

class CPhotoEditNscCouple extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;


		if ($g['options']['music'] == "Y")
		{
			$html->parse("my_music", true);
		}

		if ($g['options']['blogs'] == "Y")
		{
			$html->parse("my_blog", true);
		}


		DB::query("SELECT photo_id, user_id, photo_name, description, visible FROM photo WHERE user_id=" . $g_user['nsc_couple_id'] . " AND photo_id=" . to_sql(get_param("id", 0), "Number") . ";");

		if ($row = DB::fetch_row())
		{
			$html->setvar("photo_id", $row['photo_id']);
			$html->setvar("user_id", $row['user_id']);
			$html->setvar("photo_name", strip_tags($row['photo_name']));
			$html->setvar("description", strip_tags($row['description']));
			$html->setvar("photo", User::getPhotoProfile($row['photo_id'], 'b', guser('gender')));
			$html->setvar("visible", $row['visible'] == "N" ? "(pending audit)" : "");
            $html->setvar('profile_photo_description_length', Common::getOption('profile_photo_description_length'));
		}
		else
		{
			redirect("profile_photo_nsc_couple.php");
		}

		parent::parseBlock($html);
	}
}

g_user_full();
$page = new CPhotoEditNscCouple("", $g['tmpl']['dir_tmpl_main'] . "profile_photo_edit_nsc_couple.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('photos_nsc_couple');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");

?>
