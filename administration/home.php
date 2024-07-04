<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminLogin extends CHtmlBlock
{

	function parseBlock(&$html)
	{
        global $g;

		if (Common::isAdminModer()) {
			$html->setvar('total_users', DB::count('user'));
			$html->setvar('total_users_membership', DB::count('user', 'gold_days != 0'));
			$html->setvar('total_orders', DB::count('payment_before', "`system` LIKE '%payed'"));

			$html->setvar('total_partners', DB::count('partner'));

			$countVideos = DB::count('vids_video');
			//$sql = "SELECT SUM(`videos_uploaded`) FROM `stats` WHERE orientation = 0";
			//$countVideos = DB::result($sql);
			$html->setvar('total_videos', $countVideos);

			$html->setvar('total_photos', DB::count('photo'));

			$sql = "SELECT SUM(`im_messages`) FROM `stats` WHERE orientation = 0";
			$countImMessages = DB::result($sql);
			if (!$countImMessages) {
				$countImMessages = 0;
			}
			$html->setvar('total_messages', $countImMessages);

			$sql = "SELECT SUM(`3d_city_started`) FROM `stats` WHERE orientation = 0";
			$count3DCity = DB::result($sql);
			if (!$count3DCity) {
				$count3DCity = 0;
			}
			$html->setvar('total_city', $count3DCity);
		}

        $html->setvar('year_title', date('Y'));
        $html->setvar('month_title', l(date('F')) . ' ' . date('Y'));
        $html->setvar('day_title', date('j') . ' ' . l(date('F')) . ' ' . date('Y'));
        $fDate = DB::result('SELECT date FROM stats ORDER BY date LIMIT 1');

        if ($fDate != '') {
            $fDate = (strlen($fDate) == 8
                        ? array(substr($fDate, 0, 4), substr($fDate, 4, 2), substr($fDate, 6, 2))
                        : array(substr($fDate, 0, 4), substr($fDate, 5, 2), substr($fDate, 8, 2)));
            $html->setvar('since_title', intval($fDate[2]) . ' '
                                       . l(date('F', mktime(0, 0, 0, intval($fDate[1])))) . ' ' . intval($fDate[0]));


            $columns = DB::column('SHOW FIELDS FROM stats');
            unset($columns[0]);
            unset($columns[1]);

            $columns=unsetDisabledStats($columns);
            $cols = array();
            foreach ($columns as $col) {
                $cols[] = 'sum(' . $col . ') as ' . $col . '';
            }
            $cols = implode(', ', $cols);

            $sqlDay = "SELECT * FROM stats"
                 . " WHERE orientation = 0 AND date = '" . date('Y-m-d') . "'";
            $sqlMonth = "SELECT $cols FROM stats"
                 . " WHERE orientation = 0 AND MONTH(date) = '" . date('m') . "' AND YEAR(date) = '" . date('Y') . "'";
            $sqlYear = "SELECT $cols FROM stats"
                 . " WHERE orientation = 0 AND YEAR(date) = '" . date('Y') . "'";
            $sqlTotal = "SELECT $cols FROM stats"
                 . " WHERE orientation = 0";

            $day = DB::row($sqlDay);
            $month = DB::row($sqlMonth);
            $year = DB::row($sqlYear);
            $total = DB::row($sqlTotal);

            $i = 1;
            foreach ($columns as $col) {
                if ($i % 2 == 0) {
                    $html->setvar("class", 'color');
                    $html->setvar("decl", '_l');
                    $html->setvar("decr", '_r');
                } else {
                    $html->setvar("class", '');
                    $html->setvar("decl", '');
                    $html->setvar("decr", '');
                }
                $i++;

                $html->setvar('name', lr($col));
                $html->setvar('day', isset($day[$col]) ? $day[$col] : '0');
                $html->setvar('month', isset($month[$col]) ? $month[$col] : '0');
                $html->setvar('year', isset($year[$col]) ? $year[$col] : '0');
                $html->setvar('total', isset($total[$col]) ? $total[$col] : '0');

                $html->parse('row', true);
            }

			CStatsTools::parseChart($html);

			/* Modern */
			if (Common::isAdminModer()) {

				$sqlAverageLogins = "SELECT AVG(logins) FROM `stats` WHERE `orientation` = 0";
                $averageLogins = intval(DB::result($sqlAverageLogins));
				$html->setvar('average_logins', $averageLogins);

				/* Weeks logins */
				$month = date('m');
				$year = date('Y');

				$weekData = array();
				$sqlMonthLogins = "SELECT `date`, `logins` FROM `stats`
									WHERE `orientation` = 0 AND MONTH(date) = " . to_sql($month) .
									" AND YEAR(date) = " . to_sql($year);
				$monthLogins = DB::all($sqlMonthLogins);

				foreach ($monthLogins as $key => $item) {
					$weekData[date('j', strtotime($item['date']))] = $item['logins'];
				}

				$weekDataChart = array();
				$numDays = date('t');
				$numWeek = 0;
				$d = 1;
				for ($i = 1; $i <= $numDays; $i++) {
					if (!isset($weekDataChart[$numWeek])) {
						$weekDataChart[$numWeek] = 0;
					}
					if (isset($weekData[$i])) {
						$weekDataChart[$numWeek] += $weekData[$i];
					}
					if($i % 7 == 0) {
						$numWeek++;
					}
				}

				$block = 'weeks_logins';
				foreach ($weekDataChart as $num => $value) {
					$html->setvar("{$block}_num", $num);
					$titleWeek = pl_date('M', date('Y-m-d')) . ' ' . ($num + 1) . ' ' . l('week');
					$html->setvar("{$block}_labels", $titleWeek);
					$html->setvar("{$block}_series", $value);
					$html->parse($block, true);
				}
				/* Weeks logins */

				/* Month logins */
				$colors = array('rgba(201, 15, 2, 1)',  'rgba(157, 98, 224, 1)',
						'rgba(61, 190, 74, 1)', 'rgba(18, 88, 187, 1)',
                        'rgba(162, 130, 2, 1)', 'rgba(157, 98, 93, 1)',
						'rgba(49, 98, 93, 1)', 'rgba(248, 186, 40, 1)',
                        'rgba(48, 88, 26, 1)',   'rgba(48, 88, 115, 1)',
                        'rgba(95, 38, 68, 1)',   'rgba(253, 53, 115, 1)',
                        'rgba(207, 182, 115, 1)','rgba(61, 67, 74, 1)');

				$sql = 'SELECT SUM(`logins`) AS `logins`, `date`, `orientation`
						  FROM `stats`
						 WHERE MONTH(date) <= ' . to_sql($month) . '
						   AND YEAR(date) = ' . to_sql($year);
				$where = ' AND orientation = 0 GROUP BY MONTH(date)';
				$statsAll = DB::rows($sql . $where);

				$prepareOr = array();
				$prepareOrTitle = array();
				foreach ($statsAll as $row) {
					$dt = date('n', strtotime($row['date']));
					$prepareOr[0][$dt] = $row['logins'];
					$prepareAll[$dt] = $row['logins'];
					//$html->setvar('date_vs', pl_date('j M', $row['date']));
					//$html->setvar('count_vs', $row['logins']);
					//$html->parse('item_stats_all_vs');
				}


				if (isset($prepareOr[0]) && $prepareOr[0]) {
					$prepareOrTitle[0] = l('overall');
					function clearMap($n){
						return 0;
					}

					$orientations = DB::select('const_orientation');
					foreach ($orientations as $row) {
						$prepareOrTitle[$row['id']] = $row['title'];
						$prepareOr[$row['id']] = array_map('clearMap', $prepareOr[0]);
					}

					$where = ' AND orientation != 0 GROUP BY MONTH(date), orientation';
					$statsOr = DB::rows($sql . $where);
					foreach ($statsOr as $row) {
						$id = $row['orientation'];
						if (isset($prepareOr[$id])){
							$dt = date('n', strtotime($row['date']));
							$prepareOr[$id][$dt] = $row['logins'];
						}
					}

					$month = intval($month);

					for ($i = 1; $i <= $month; $i++) {
						if (!isset($prepareOr[0][$i])) {
							foreach ($prepareOr as $or => $row) {
								$prepareOr[$or][$i] = 0;
								ksort($prepareOr[$or]);
							}
						}
					}
					//var_dump_pre($prepareOr, true);

					$i = 0;
					foreach ($prepareOr as $or => $rows) {
						if (isset($colors[$i])) {
							$color =  $colors[$i];
						} else {
							$i = 0;
							$color =  $colors[0];
						}
						$html->setvar('color_vs', $color);
						$i++;
						$html->setvar('title_vs', l($prepareOrTitle[$or]));
						$html->setvar('data_title_vs', toJsL($prepareOrTitle[$or]));
						$html->parse('item_stats_title_vs', true);
						foreach ($rows as $date => $count) {
							if (!$or) {
								$html->setvar('date_vs', $date);
								$html->parse('item_stats_date_vs', true);
							}
							$html->setvar('or_vs', $or);
							$html->setvar('count_vs', $count);
							$html->parse('item_data_stats_vs', true);
						}
						$html->parse('item_data_vs', true);
						$html->clean('item_data_stats_vs');
					}
				}
				/* Month logins */

				/* Visitors per country today */
				$lang = loadLanguageAdmin();

				$block = 'country_visitor_day';
				$sql = 'SELECT * FROM geo_country';
				$countries = DB::all($sql);
				foreach ($countries as $key => $country) {
					$countryTitle = l($country['country_title'], $lang);
					$html->setvar("{$block}_name", toJs($countryTitle));
					$html->setvar("{$block}_code", $country['code']);
					$html->parse($block . '_country', true);
				}

				$m = 10000000;
				$sql = "SELECT * FROM stats_country  WHERE orientation = 0 AND date = '" . date('Y-m-d') . "'";
				$countryVisitors = DB::all($sql);
				foreach ($countryVisitors as $key => $country) {
					$sql = 'SELECT * FROM geo_country  WHERE country_id = ' . to_sql($country['country_id']);
					$countryInfo = DB::row($sql);

					$countryTitle = l($countryInfo['country_title'], $lang);
					$html->setvar("{$block}_title", toJs($countryTitle . ' (' . $country['count'] . ')'));
					$html->setvar("{$block}_code", $countryInfo['code']);
					$html->setvar("{$block}_count", $country['count']);
					$html->setvar("{$block}_long", $countryInfo['long']/$m);
					$html->setvar("{$block}_lat", $countryInfo['lat']/$m);
					$html->parse($block, true);
				}
				/* Visitors per country today */

			}
			/* Modern */


            $html->parse('stats', true);
			$html->parse('stats_1', true);
        } else {
            $html->parse('nostats', true);
        }

		parent::parseBlock($html);
	}
}


$page = new CAdminLogin("", $g['tmpl']['dir_tmpl_administration'] . "home.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");