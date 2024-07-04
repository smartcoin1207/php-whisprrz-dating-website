<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CSearch extends CHtmlBlock {

    function parseBlock(&$html)
    {
        global $g;
        global $l;
        global $g_user;
		global $g_info;
		global $gc;

        if(!(isset($g_user['p_orientation']) && $g_user['p_orientation'])) {
            return false;
        }

        CBanner::getBlock($html, 'right_column');
        foreach ($g_user as $k => $v) $html->setvar($k, $v);

		if ($g['options']['your_orientation'] == 'Y') {
			$html->setvar("p_orientation_for_search", $g_user['p_orientation']);
            $g_info['users_new'] = DB::result_cache("users_new_" . to_php_alfabet($g_user['p_orientation']), 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 " . $g['sql']['your_orientation'] .  " AND register>'" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "'"); # AND register>(" . (time() - 60 * 60 * 24 * 10) . "
            $g_info['users_new_near'] = DB::result_cache("users_new_near_" . to_php_alfabet($g_user['p_orientation'] . "_" . $g_user['city_id']), 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 " . $g['sql']['your_orientation'] .  " AND register>'" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "' AND city_id=" . $g_user['city_id'] . "");
		} else {
			$g_info['users_new'] = DB::result_cache("users_new", 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 AND register>'" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "'"); # AND register>(" . (time() - 60 * 60 * 24 * 10) . "
			$g_info['users_new_near'] = DB::result_cache("users_new_near" . to_php_alfabet($g_user['city_id']), 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 AND city_id=" . $g_user['city_id'] . " AND register>'".date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time']))."'");
		}

		foreach ($g_info as $k => $v) $html->setvar($k, $v);

        if ($g_user['city_id'] > 0 and $g_user['city'] != '') {
            if (UserFields::isActive('orientation') && Common::isOptionActive('your_orientation')) {
                CSearch::parseChecks($html, "p_orientation", "SELECT id, title FROM const_orientation ORDER BY id ASC", $g_user['p_orientation'], 2, 0, true);
            }
			$html->parse("new_near", true);
		}

        if(Common::isOptionActive('viewed_me_tab_enabled')) {
            $html->parse('viewed_me', true);
        }

        if(Common::isOptionActive('mail')) {
            $html->parse('mail_on');
        }
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on');
        }
        if (Common::isOptionActive('biorythm')) {
			$html->parse("biorythm", false);
		}


        $html->setvar("p_age_from_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], get_param("p_age_from", $g['options']['users_age'])));
        $html->setvar("p_age_to_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], get_param("p_age_to", $g['options']['users_age_max'])));

        UserFields::parseStatus($html);

        if (UserFields::isActive('orientation') && Common::isOptionActive('your_orientation')) {
            if (isset($g_user['p_orientation']) && $g_user['p_orientation']) {

                // $this->parseChecks($html, "p_orientation_status", "SELECT id, title FROM const_orientation ORDER BY id ASC", $g_user['p_orientation'], 2, 0, true);
                // $html->setvar("p_orientation", $g_user['p_orientation']);
                // $html->parse("p_orientation", true);
            }
        }

                //nnsscc-diamond-20200304-start
        if (UserFields::isActive('orientation')) {
            $checks = 0;
            $this->parseChecks($html, "p_orientation_status", "SELECT id, title FROM const_orientation ORDER BY id ASC", $checks, 2, 0);
            $html->parse('orientation', false);
        }

        if (UserFields::isActive('relation')) {

            $checks = 0;
            $this->parseChecks($html, "p_relation", "SELECT id, title FROM var_income ORDER BY id ASC", $checks, 3, 0);
            $html->parse('relation', false);
            //$this->parseChecks($html, "p_relation", "SELECT id, title FROM const_relation ORDER BY id ASC", $checks, 2, 0);
            //$html->parse('relation', false);
        }
        if (Common::isOptionActive('adv_search')) {
            $html->parse('menu_search_advanced', false);
        }

        if (Common::isOptionActive('saved_searches')) {
            $html->parse('search_saved_js', false);
            $html->parse('menu_search_saved', false);
            $html->parse('search_saved', false);
        }

		if (!Common::isOptionActive('no_profiles_without_photos_search')) {
			$html->setvar('checks_photo', get_param('photo', 0) ? ' checked' : '');
			$html->parse('with_photo', false);
		}

        parent::parseBlock($html);
    }

    public static function parseChecks(&$html, $name, $sql, $mask, $num_columns = 1, $add = 0, $parseValue = false, $varName = false)
    {
        global $l;
        if (DB::query($sql)) {
            $i = 0;
            $total_checks = DB::num_rows();
            $in_column = ceil(($total_checks + $add) / $num_columns);

            while ($row = DB::fetch_row()) {
                $i++;
                $html->setvar("id", $row[0]);
                $html->setvar("title", isset($l['all'][to_php_alfabet($row[1])]) ? $l['all'][to_php_alfabet($row[1])] : $row[1]);


                if ($mask & (1 << ($row[0] - 1))) {
                    $html->setvar("checked", " checked");
                    if($parseValue) {
                        $html->setvar($varName ? $varName : $name, $row[0]);
                        $html->parse($name);
                    }
                } else {
                    $html->setvar("checked", "");
                }

                if ($i % $in_column == 0 and $i != 0 and ($i != $total_checks or $add > 0) and $num_columns != 1) {
                    $html->parse($name . "_column", true);
                } else {
                    $html->setblockvar($name . "_column", "");
                }

                if(!$parseValue) {
                        $html->parse($name, true);
                }
            }

            DB::free_result();
        }
    }

}