<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
payment_check('biorythm');

class CBioRythm extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $gc;
		global $gm;
		global $g_user;
		global $l;

		$id = get_param("id", 0);

		DB::query("SELECT user_id, name, birth FROM user WHERE user_id=" . to_sql($id, "Number") . " ");

		if ($row = DB::fetch_row())
		{
			$html->setvar("user_id", $row['user_id']);
			if ($row['user_id'] == $g_user['user_id']) {
				$html->setvar("information", l('my_information'));
			} else {
				$html->setvar("information", lSetVars('information', array('name' => $row['name'])));
			}
			$bio_date=$row['birth'];
		}
		else
		{
			Common::toHomePage();
		}

		include ("_include/current/bio.php"); // file with class
		$bior = new BioR ($bio_date);// new instance

        $file = $g['path']['dir_files'] . 'bio/'.$id.'_bio.png';

		@chmod($file, 0777);
		$bior->DrawBior($file); // build diagram image and put it to disk
                Common::saveFileSize($file);
		parent::parseBlock($html);
	}
}

$page = new CBioRythm("", $g['tmpl']['dir_tmpl_main'] . "biorythm.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
