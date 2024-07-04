<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
payment_check('search_advanced');

class CMapSearchAdvanced extends UserFields
{
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;

        $this->parseFieldsAll($html, 'search_advanced');
		
		if (!Common::isOptionActive('no_profiles_without_photos_search')) {
			if (get_param('photo') == '1') {
				$html->setvar("photo_checked", 'checked="checked"');
			}
			$html->parse('with_photo', false);
		}
		if (get_param('couple') == '1') {
			$html->setvar("couple_checked", 'checked="checked"');
		}
		$html->setvar("keyword", he(strip_tags(get_param("keyword", ""))));
		$html->setvar("search_name", he(strip_tags(get_param("search_name", ""))));

        if (true) {
            $checks = get_checks_param('p_orientation');
			$this->parseChecks1($html, "SELECT id, title FROM const_relation", $checks, 5, 0, 'p_orientation'); //nnsscc-diamond-20200309            
            $html->parse('orientation', false);
        }



        if (Common::isOptionActive('adv_search')) {
            $html->parse('menu_search_advanced', false);
        }	

        if (Common::isOptionActive('couples')) {
            $html->parse('couples', false);
        }

        $html->setvar('search_max_length', Common::getOption('search_name_max_length'));

        if (Common::isOptionActive('saved_searches') && guid()) {
            $html->parse('menu_search_saved', false);
            $html->parse('search_saved_js', false);
            $html->parse('search_saved', false);
        }
		parent::parseBlock($html);
	}

		function parseChecks1(&$html, $sql, $mask, $num_columns = 1, $add = 0, $p = '')
	{
		global $l;
		if (DB::query($sql))
		{
			$i = 0;
			$total_checks = DB::num_rows();
			$in_column = ceil(($total_checks + $add) / $num_columns);

			if ($p == '') {
				$p = 'check';
			}

			while ($row = DB::fetch_row())
			{
				$i++;

				$html->setvar('id', $row[0]);
				$html->setvar('title', l($row[1]));
				if ($mask & (1 << ($row[0] - 1)))
				{
					$html->setvar('checked', ' checked');
				} else {
					$html->setvar('checked', '');
				}

				if ($i % $in_column == 0 and $i != 0 and ($i != $total_checks or $add > 0) and $num_columns != 1)
				{
					$html->parse($p . "_column", false);
				}
				else
				{
					$html->setblockvar($p . "_column", "");
				}

				$html->parse($p, true);
			}
			$html->parse($p . "s", true);
			$html->setblockvar($p, "");
			DB::free_result();
		}
	}
}

if (Common::isOptionActive('adv_search') == false) {
    redirect('search.php');
}

$page = new CMapSearchAdvanced("", $g['tmpl']['dir_tmpl_main'] . "map_search_advanced.html");
#$page->setUser('empty');
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$mailMenu = new CMenuSection('search', $g['tmpl']['dir_tmpl_main'] . "_menu_search.html");
$mailMenu->setActive('map_advanced');
$page->add($mailMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);




include("./_include/core/main_close.php");

?>
