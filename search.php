<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
class CSearchBasic extends CHtmlBlock
{
	function action()
	{
		global $g_user;
		$cmd = get_param("cmd", "");
		if ($cmd == "delete")
		{
			$id = to_sql(get_param("id", 0), "Number");
			DB::execute("DELETE FROM search_save WHERE user_id=" . $g_user['user_id'] . " AND id=" . $id . "");
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;

        $advSearchOption = Common::isOptionActive('adv_search');
        if ($advSearchOption) {
            $html->setvar('url_search', 'search_advanced.php');
        } else {
            $html->setvar('url_search', 'search.php');
        }

		DB::query("SELECT id, name, query FROM search_save WHERE user_id = " . to_sql($g_user['user_id'], 'Number') . " ORDER BY id");
		while ($row = DB::fetch_row())
		{
			$html->setvar('id', $row['id']);
			$html->setvar('name', $row['name']);
			$html->setvar('query', $row['query']);
            $html->parse('search_save', true);
		}

		if (isset($g_user['orientation']))
		{
			$or_title = DB::result("SELECT title FROM const_orientation WHERE id=" . $g_user['orientation'] . "");
			$html->setvar('orientation', l($or_title));
			$html->setvar('orientation_search', DB::result("SELECT search FROM const_orientation WHERE id=" . $g_user['orientation'] . ""));
		}


        $userAge = Common::getOption('users_age');
        $userAgeMax = Common::getOption('users_age_max');
		$html->setvar('p_age_from_options', n_options($userAge, $userAgeMax, get_param('p_age_from', $userAge)));
		$html->setvar('p_age_to_options', n_options($userAge, $userAgeMax, get_param('p_age_to', $userAgeMax)));

        $status = array();

		if (Common::isOptionActive('online_tab_enabled')) {
            $status += array("online" => l('online'));
        }
        if (Common::isOptionActive('new_tab_enabled')) {
            $status += array("new" => l('new'));
        }
        if (Common::isOptionActive('birthdays_tab_enabled ')) {
            $status += array("birthday" => l('birthday'));
        }
        if (Common::isOptionActive('i_viewed_tab_enabled')) {
            $html->parse('i_viewed');
        }
        if (Common::isOptionActive('viewed_me_tab_enabled')) {
            $html->parse('viewed_me');
        }

        $quickSearchParsingBlocks=Menu::getSubmenuItemsList('quick_search');
        if ($html->blockexists('quick_search')) {
            Menu::parseSubmenu($html,'quick_search');
        } else {
            foreach($quickSearchParsingBlocks as $k=>$v){
                $html->parse($v);
            }
        }

        if (!empty($status)) {
            $status += array("all" => l('all'));
            $html->setvar('status_options', h_options($status, get_param('status', 'all')));
            $html->parse('tab_enabled', false);
        }

        // if (UserFields::isActive('relation')) {
        //     $checks = get_checks_param('p_relation');
        //     $this->parseChecks($html, "SELECT id, title FROM const_relation", $checks, 2, 0, 'p_relation');
        //     $html->parse('relation', false);
        // }

        if (UserFields::isActive('relation')) {
            $checks = get_checks_param('p_relation');
            //$this->parseChecks($html, "SELECT id, title FROM const_relation", $checks, 2, 0, 'p_relation');
			$this->parseChecks($html, "SELECT id, title FROM var_income", $checks, 5, 0, 'p_relation'); //nnsscc-diamond-20200309            
            $html->parse('relation', false);
        }

        if (UserFields::isActive('orientation') && Common::isOptionActive('your_orientation')) {
            if (isset($g_user['p_orientation']) && $g_user['p_orientation']) {
                CSearch::parseChecks($html, "orientation", "SELECT id, title FROM const_orientation ORDER BY id ASC", $g_user['p_orientation'], 2, 0, true, 'orientation_search');
            }
        }
		if (get_param("cmd", "") == "location") {
			$country = get_param("country", $g_user['country_id']);
			$state = get_param("state", $g_user['state_id']);
		} else {
			$country = get_param("country", $g_user['country_id']);
			$state = get_param("state", $g_user['state_id']);
		}

		$html->setvar("country_options", Common::listCountries($country));
		$html->setvar("state_options", Common::listStates($country, $state));

		$state = DB::result("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql($country, "Number") . " AND state_id=" . to_sql($state, "Number") . ";");

		if ($state != '' and $state != 0)
		{
			$html->setvar("city_options", Common::listCities($state, get_param("city", $g_user['city_id'])));
		}

		$html->setvar('search_name', he(get_param('search_name', l('my_search'))));
		if (get_param("save_search", 0) == 1)
		{
			$html->setvar('save_checked', ' checked');
		}

        $html->setvar('keyword_value', get_param('keyword', ''));

        if ($advSearchOption || Common::isOptionActive('saved_searches')) {
            $html->parse('menu_search_basic', false);
        }

        if ($advSearchOption) {
            $html->parse('menu_search_advanced', false);
        }

        $html->setvar('search_max_length', Common::getOption('search_name_max_length'));

        if (Common::isOptionActive('saved_searches')) {
            $html->parse('menu_search_saved', false);
            $html->parse('search_save_checked', false);
            $html->parse('search_save_checked_js', false);
            $html->parse('search_saved', false);
        }

		if (!Common::isOptionActive('no_profiles_without_photos_search')) {
			$html->setvar('checks_photo', get_param('photo', 0) ? ' checked' : '');
			$html->parse('with_photo', false);
		}


		parent::parseBlock($html);
	}

	function parseChecks(&$html, $sql, $mask, $num_columns = 1, $add = 0, $p = '')
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

$page = new CSearchBasic("", $g['tmpl']['dir_tmpl_main'] . "search.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$mailMenu = new CMenuSection('search', $g['tmpl']['dir_tmpl_main'] . "_menu_search.html");
$mailMenu->setActive('search');
$page->add($mailMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);




include("./_include/core/main_close.php");

?>
