<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CStatsTools
{
    static public function getSection()
    {
        return array(
        'logins',
        'registrations',
        'mail_messages_sent',
        'postcards_sent',
        'videos_uploaded',
        'videos_viewed',
        'videos_comments',
        'profiles_viewved',
        '3d_city_started',
        'hot_or_not_votes',
        'pics_uploaded',
        'photos_uploaded',
        'added_to_friends',
        '3d_chat_opened',
        'flash_chat_opened',
        'new_blogs',
        'events_created',
		'hotdates_created',//nnsscc-diamond-20200502
		'partyhouz_created',
		'lookinglass_created',
        'new_forum_posts',
        'groups_created',
        'winks_sent',
        'gold_memberships',
        'ads_published',
        'replies_to_ads',
        'mp3_uploaded',
        'user_blocks',
        'games_started',
        'im_started',
        'im_messages',
        'added_to_favourites',
        'blog_search_used',
        'user_search_used',
        'gifts_sent',);
    }

    static public function genStats()
    {
        
        $columns = self::getSection();

        DB::execute('LOCK TABLES stats WRITE, stats_country WRITE');
		DB::execute('TRUNCATE TABLE stats');
		DB::execute('TRUNCATE TABLE stats_country');

		$countries = array(229, 230, 74, 81, 107, 179, 14, 40, 22, 31, 100);

        $start = 600;
        $i = $start;

        while ($i >= 0) {
            $date = dateadd('', -$i);
            $i--;
            $weightTime = ceil(($start - $i) / 30);

            $total = array();
            for ($ort = 2; $ort >= 0; $ort--) {
                $sql = array();
                foreach ($columns as $col => $weight) {
                    $weight = rand(5, 20);
                    if (!isset($total[$col])) {
                        $total[$col] = 0;
                    }
                    if ($ort != 0) {
                        $val = ($ort <= 2 ? 10 : 1) * $weight * $weightTime;
                        $num = rand(ceil($val * 0.7), ceil($val * 1.3));
                        $sql[] = "'" . $num . "'";
                        $total[$col] += $num;
                    } else {
                        $sql[] = "'" . $total[$col] . "'";
                    }
                }
                $sql = implode(",\n", $sql);
                $query = "INSERT INTO stats VALUES(
                    '$date',
                    '$ort',
                    $sql
                )";
				DB::execute($query);

                #echo $query . '<br>';
            }

			foreach ($countries as $key => $countryId) {
				$countVisit = rand(1000, 5000);
				$sql = "INSERT INTO `stats_country` VALUES('{$date}',0,{$countryId},{$countVisit})";
				DB::execute($sql);
			}
        }
        DB::execute('UNLOCK TABLES');

    }
    static public function count($field, $userId = NULL)
    {
     

        $columnsW = self::getSection();

        if (in_array($field, $columnsW)) {

            $date = date('Y-m-d');

            $orientationRow = '';

            // authorized members by orientation
            if ($userId === NULL) {
                $orientation = intval(guser('orientation'));
            } else {
                $orientation = intval(User::getInfoBasic($userId, 'orientation'));
            }

            if ($orientation) {
                $orientationRow = ",($orientation, '$date', 1)";
            }

            $query = "INSERT INTO stats (`orientation`, `date`, `$field`)
                VALUES (0, '$date', 1)$orientationRow
                ON DUPLICATE KEY UPDATE `$field` = `$field` + 1";


            DB::execute($query);

			if ($field == 'logins') {
				$infoCity = IP::geoInfoCity();

				if ($userId === NULL) {
					$countryId = intval(guser('country_id'));
				} else {
					$countryId = intval(User::getInfoBasic($userId, 'country_id'));
				}
				$orientationRow = '';
				if ($orientation) {
					$orientationRow = ",($orientation, '{$date}', '{$countryId}', 1)";
				}
				$query = "INSERT INTO `stats_country` (`orientation`, `date`, `country_id`, `count`)
						  VALUES (0, '{$date}', '{$countryId}', 1)$orientationRow
					ON DUPLICATE KEY UPDATE `count` = `count` + 1";
				DB::execute($query);

			}
        }

    }

    static public function countTest($field, $userId = NULL)
    {
        //$columnsW = explode(" ",
        //'logins registrations mail_messages_sent postcards_sent videos_uploaded videos_viewed videos_comments profiles_viewved 3d_city_started hot_or_not_votes pics_uploaded photos_uploaded added_to_friends 3d_chat_opened flash_chat_opened new_blogs events_created new_forum_posts groups_created winks_sent gold_memberships ads_published replies_to_ads mp3_uploaded user_blocks games_started im_started im_messages added_to_favourites blog_search_used user_search_used gifts_sent');

        $columnsW = self::getSection();

        if (in_array($field, $columnsW)) {

            $date = date('Y-m-d');

            $additionalRow = '';

            // authorized members by orientation
            if ($userId === NULL) {
                $id = guid();
                $ort = intval(guser('orientation'));
            } else {
                $id = $userId;
                $ort = User::getInfoBasic($id, 'orientation');
            }

            if ($ort) {
                $additionalRow = ",($ort, '$date', 1)";
            }

            $query = "INSERT INTO stats (`orientation`, `date`, `$field`) VALUES (0, '$date', 1)$additionalRow ON DUPLICATE KEY UPDATE `$field` = `$field` + 1";

            //echo $query . '<br>';

            DB::execute($query);
        }

    }

    static public function parseChart(&$html, $param = 'logins', $month = null, $year = null)
    {
        $colors = array('rgba(201, 15, 2, 1)',  'rgba(157, 98, 224, 1)',
						'rgba(61, 190, 74, 1)', 'rgba(18, 88, 187, 1)',
                        'rgba(162, 130, 2, 1)', 'rgba(157, 98, 93, 1)',
						'rgba(49, 98, 93, 1)', 'rgba(248, 186, 40, 1)',
                        'rgba(48, 88, 26, 1)',   'rgba(48, 88, 115, 1)',
                        'rgba(95, 38, 68, 1)',   'rgba(253, 53, 115, 1)',
                        'rgba(207, 182, 115, 1)','rgba(61, 67, 74, 1)');
        if ($month === null) {
            $month = date('m');
        }
        if ($year === null) {
            $year = date('Y');
        }
        $sql = 'SELECT `' . to_sql($param, 'Plain') . '`, `date`, `orientation`
                  FROM `stats`
                 WHERE MONTH(date) = ' . to_sql($month) . '
                   AND YEAR(date) = ' . to_sql($year);
        $where = ' AND orientation = 0';
        $statsAll = DB::rows($sql . $where);
        // var_dump($sql . $where); die();
        // var_dump($statsAll); die();

        $prepareOr = array();
        $prepareOrTitle = array();
        foreach ($statsAll as $row) {
            $prepareOr[0][$row['date']] = $row[$param];
            $prepareAll[$row['date']] = $row[$param];
            $html->setvar('date', pl_date('j M', $row['date']));
            $html->setvar('count', $row[$param]);
            $html->parse('item_stats_all');
        }

        if (isset($prepareOr[0]) && $prepareOr[0]) {
            $prepareOrTitle[0] = l('overall');
            function clear($n){
                return 0;
            }

            $orientations = DB::select('const_orientation');
            foreach ($orientations as $row) {
                $prepareOrTitle[$row['id']] = $row['title'];
                $prepareOr[$row['id']] = array_map('clear', $prepareOr[0]);
            }

            $where = ' AND orientation != 0';
            $statsOr = DB::rows($sql . $where);
            foreach ($statsOr as $row) {
                $id = $row['orientation'];
                if (isset($prepareOr[$id])){
                    $prepareOr[$id][$row['date']] = $row[$param];
                }
            }
            $i = 0;
            foreach ($prepareOr as $or => $rows) {
                if (isset($colors[$i])) {
                    $color =  $colors[$i];
                } else {
                    $i = 0;
                    $color =  $colors[0];
                }
                $html->setvar('color', $color);
                $i++;
                $html->setvar('title', l($prepareOrTitle[$or]));
                $html->setvar('data_title', toJsL($prepareOrTitle[$or]));
                $html->parse('item_stats_title', true);
                foreach ($rows as $date => $count) {
                    if (!$or) {
                        $html->setvar('date', Common::dateFormat($date, 'statistics'));
                        $html->parse('item_stats_date', true);
                    }
                    $html->setvar('or', $or);
                    $html->setvar('count', $count);
                    $html->parse('item_data_stats', true);
                }
                $html->parse('item_data', true);
                $html->clean('item_data_stats');
            }
        }
    }
}