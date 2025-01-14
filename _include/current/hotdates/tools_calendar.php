<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once(dirname(__FILE__) . "/../video_hosts.php");
require_once(dirname(__FILE__) . "/../outside_images.php");

class CHotdatesTools_Calendar
{
    static $m_settings = null;
    static $player_containers = array();
    static $thumbnail_postfix = 'th';

    const ALLOWTAGS = '<b><i><u><s><strike><strong><em>';
    const VIDEOSTARTTAG = '<div class="hotdates_video">';
    const VIDEOENDTAG = '</div>';
    static $videoWidth = 390;
    static $videoWidthComment = 354;

    static $outside_image_sizes = array(
        array(
            'width' => 390,
            'height' => 292,
            'allow_smaller' => true,
            'file_postfix' => 'th',
            ),
        );

    static public function stags($str)
    {
        return strip_tags_attributes($str, self::ALLOWTAGS);
    }

    static function filter_text_to_db($v, $parse_media = true, $old_text = null)
    {
        if($parse_media)
        {
           $v = VideoHosts::filterToDb($v);
           $v = OutsideImages::filter_to_db($v, $old_text);
        }
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        //$v = htmlspecialchars($v, ENT_QUOTES);
        $v = trim($v);
        return $v;
    }

    static function filter_text_to_html($text, $parse_media = true, $thumbnail_postfix = "th", $comment = true)
    {
        $text = self::_filterLinksTagsToHtml($text);
        if($parse_media)
        {
            if ($comment) {
                $videoWidthCustom = self::$videoWidth;
            } else {
                $videoWidthCustom = self::$videoWidthComment;
            }

            $text = VideoHosts::filterFromDb($text, self::VIDEOSTARTTAG, self::VIDEOENDTAG, $videoWidthCustom);
            $text = OutsideImages::filter_to_html($text, self::VIDEOSTARTTAG, self::VIDEOENDTAG, "lightbox");
        }
        $text = self::_filterRemoveUnusedTags($text);
        $text = nl2br(trim($text));
        return $text;
    }

    static protected function _filterLinksTagsToHtml($text)
    {

        return Common::parseLinksSmile($text);
    }

    static protected function _filterRemoveUnusedTags($text)
    {
        //$grabs = grabs($text, '{', '}', true);
        $grabs = Common::grabsTags($text);
        foreach ($grabs as $gr) {
            $text = str_replace($gr, "", $text);
        }
        return $text;
    }

    static function guests_by_hotdate_sql_base($hotdate_id)
    {
        $sql = "hotdates_hotdate_guest as g, user as u WHERE g.hotdate_id = " . to_sql($hotdate_id, 'Number') .
            " AND g.user_id = u.user_id  ".
            " ORDER BY g.created_at DESC";

        return array('query' => $sql, 'columns' => 'g.*, u.user_id, u.name');
    }

    static function comments_by_hotdate_sql_base($hotdate_id)
    {
        $sql = "hotdates_hotdate_comment as c, user as u WHERE c.hotdate_id = " . to_sql($hotdate_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at DESC";

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function comments_by_comment_sql_base($comment_id)
    {
        $sql = "hotdates_hotdate_comment_comment as c, user as u WHERE c.parent_comment_id = " . to_sql($comment_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at ASC";//DESC

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function retrieve_from_sql($sql)
    {

        DB::query($sql);
        $results = array();

        while($row = DB::fetch_row())
        {
            $results[] = $row;
        }

        return $results;
    }

    static function retrieve_from_sql_base($sql_base, $limit = 0, $shift = 0)
    {
        return self::retrieve_from_sql("SELECT " . $sql_base['columns'] . " FROM " . $sql_base['query'] . ($limit ? (" LIMIT " .  intval($shift) . ", " . intval($limit)) : ''));
    }

    static function count_from_sql_base($sql_base)
    {
        return DB::result("SELECT COUNT(*) FROM " . $sql_base['query']);
    }

    static function split_search_to_words($search)
    {
        $search = str_replace(array(',', ';', '!', '?', '.'), array(' ', ' ', ' ', ' ', ' '), $search);

        $_words = explode(" ", $search);
        $words = array();
        foreach($_words as $word)
        {
            $word = trim($word);

            if(mb_strlen($word) > 0)
                $words[] = $word;
        }

        return $words;
    }

    static function order_by_from_settings()
    {
        $orders = array();

        if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
            return '';
        }

        $settings = self::settings();

        if($settings['category_id'])
            $orders[] = 'e.category_id = ' . $settings['category_id'] . ' DESC';

        global $g_user;
        $city_id = $g_user['city_id'];
        $state_id = $g_user['state_id'];
        $country_id = $g_user['country_id'];

        if($city_id)
        {
            DB::query("SELECT * FROM geo_city WHERE city_id=".to_sql($city_id, 'Number'));
            if($city = DB::fetch_row())
            {
                $city_id = $city['city_id'];
                $state_id = $city['state_id'];
                $country_id = $city['country_id'];
            }
            else
                $city_id = null;
        }
        if(!$city_id && $state_id)
        {
            DB::query("SELECT * FROM geo_state WHERE state_id=".to_sql($state_id, 'Number'));
            if($state = DB::fetch_row())
            {
                $state_id = $state['state_id'];
                $country_id = $state['country_id'];
            }
            else
                $state_id = null;
        }
        if(!$city_id && !$state_id && $country_id)
        {
            DB::query("SELECT * FROM geo_country WHERE country_id=".to_sql($country_id, 'Number'));
            if($country = DB::fetch_row())
            {
                $country_id = $country['country_id'];
            }
            else
                $country_id = null;
        }

        if($city_id)
            $orders[] = "c.city_id = $city_id DESC";
        if($state_id)
            $orders[] = "c.state_id = $state_id DESC";
        if($country_id)
            $orders[] = "c.country_id = $country_id DESC";

        return implode(", ", $orders);
    }


    static function hotdates_by_user_sql_base($user_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND e.user_id=" . to_sql($user_id, 'Number') .
            " ORDER BY e.hotdate_datetime DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_by_user_as_guest_sql_base($user_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, hotdates_hotdate_guest as g, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_id = g.hotdate_id AND e.hotdate_private = 0 AND g.user_id=" . to_sql($user_id, 'Number') .
            " ORDER BY e.hotdate_datetime DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_most_discussed_sql_base($where="")
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE $where c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() " .
            "  ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_n_comments DESC, e.hotdate_datetime ASC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_most_anticipated_sql_base()
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() " .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_n_guests DESC, e.hotdate_datetime ASC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_popular_finished_sql_base($where="")
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE $where c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) <= NOW() " .
            "  ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_n_guests DESC, e.hotdate_datetime DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_upcoming_sql_base($where="")
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE $where c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() " .
            "  ORDER BY e.hotdate_datetime ASC " . ($order_by_from_settings ? (", " . $order_by_from_settings . "") : '');

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_upcoming_main_page_sql_base($where="")
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE $where c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() " .
            "  ORDER BY DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW(), e.hotdate_datetime ASC " . ($order_by_from_settings ? (', ' . $order_by_from_settings . "") : '');

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_coming_hotdates_sql_base($hotdate)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $words = self::split_search_to_words($hotdate['hotdate_title']);
        $searches = array();

        foreach($words as $word)
        {
            $searches[] = "(CONCAT_WS('', e.hotdate_title, e.hotdate_description) LIKE " . to_sql('%'.$word.'%') . ")";
        }

        $where_from_searches = count($searches) ? ("(" . implode(' OR ',  $searches) . ")") : "";

        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() AND e.hotdate_id <> " . $hotdate['hotdate_id'] . " AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime ASC, e.created_at ASC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_coming_hotdates_category_sql_base($hotdate,$remove_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $not_in = "0";

        foreach($remove_id as $id)
        {
            $not_in .= ",$id";
        }

        $where_from_searches = " e.category_id=".$hotdate['category_id']." ";

        $sql = "hotdates_hotdate as e, geo_city as c WHERE e.hotdate_id NOT IN ($not_in) AND c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() AND e.hotdate_id <> " . $hotdate['hotdate_id'] . " AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime ASC, e.created_at ASC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_coming_hotdates_all_sql_base($hotdate,$remove_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $not_in = "0";

        foreach($remove_id as $id)
        {
            $not_in .= ",$id";
        }

        $where_from_searches = " 1 ";

        $sql = "hotdates_hotdate as e, geo_city as c WHERE e.hotdate_id NOT IN ($not_in) AND c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() AND e.hotdate_id <> " . $hotdate['hotdate_id'] . " AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime ASC, e.created_at ASC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_by_calendar_day($day_time, $where = '', $uid = null)
    {
        global $g_user;

        if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
            return TaskCalendarHotdate::getSqlTasksByDay($day_time, $where, $uid);
        }

        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e
                LEFT JOIN hotdates_hotdate_guest as eg ON e.hotdate_id = eg.hotdate_id,
                geo_city as c
                WHERE c.city_id = e.city_id " .
                " AND eg.user_id = " . to_sql($g_user['user_id']) .
                " AND e.hotdate_datetime >= '" . date("Y-m-d", $day_time) . "' " .
                " AND e.hotdate_datetime < DATE_ADD('" . date("Y-m-d", $day_time) . "', INTERVAL 1 DAY) " .
                " ORDER BY e.hotdate_datetime ASC, e.hotdate_id ASC" ;

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_past_hotdates_alike_sql_base($hotdate)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $words = self::split_search_to_words($hotdate['hotdate_title']);
        $searches = array();

        foreach($words as $word)
        {
            $searches[] = "(CONCAT_WS('', e.hotdate_title, e.hotdate_description) LIKE " . to_sql('%'.$word.'%') . ")";
        }

        $where_from_searches = count($searches) ? ("(" . implode(' OR ',  $searches) . ")") : "";

        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR)  < NOW() AND e.hotdate_id <> " . $hotdate['hotdate_id'] . " AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_past_hotdates_alike_category_sql_base($hotdate,$remove_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $not_in = "0";

        foreach($remove_id as $id)
        {
            $not_in .= ",$id";
        }

        $where_from_searches = " e.category_id=".$hotdate['category_id']." ";

        $sql = "hotdates_hotdate as e, geo_city as c WHERE e.hotdate_id NOT IN ($not_in) AND c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR)  < NOW() AND e.hotdate_id <> " . $hotdate['hotdate_id'] . " AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_past_hotdates_alike_all_sql_base($hotdate,$remove_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $not_in = "0";

        foreach($remove_id as $id)
        {
            $not_in .= ",$id";
        }

        $where_from_searches = "1";

        $sql = "hotdates_hotdate as e, geo_city as c WHERE e.hotdate_id NOT IN ($not_in) AND c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR)  < NOW() AND e.hotdate_id <> " . $hotdate['hotdate_id'] . " AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_by_query_sql_base($query, $upcoming)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $words = self::split_search_to_words($query);
        $searches = array();

        foreach($words as $word)
        {
            $searches[] = "(CONCAT_WS('', e.hotdate_title, e.hotdate_place) LIKE " . to_sql('%'.$word.'%') . ")";
        }

        $where_from_searches = count($searches) ? ("(" . implode(' OR ',  $searches) . ")") : "";
        global $g_user;
        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND (e.hotdate_private = 0 OR (e.hotdate_private=1 AND e.user_id=".$g_user['user_id'].") ) AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) " . ($upcoming ? '>' : '<=') . " NOW() AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime " . ($upcoming ? 'ASC' : 'DESC') . ", e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_by_place_sql_base($place, $upcoming)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) " . ($upcoming ? '>' : '<=') . " NOW() AND " .
            "e.hotdate_place LIKE " . to_sql($place) .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime " . ($upcoming ? 'ASC' : 'DESC') . ", e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_by_category_id_sql_base($category_id, $upcoming)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) " . ($upcoming ? '>' : '<=') . " NOW() AND " .
            "e.category_id LIKE " . to_sql($category_id, 'Number') .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime " . ($upcoming ? 'ASC' : 'DESC') . ", e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_by_hotdate_datetime_sql_base($hotdate_datetime, $upcoming)
    {
        $order_by_from_settings = self::order_by_from_settings();

        // ADDED: view hotdates and own tasks for this day
        global $g_user;
        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND (e.hotdate_private = 0 OR e.user_id = " . $g_user['user_id'] . ") AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) " . ($upcoming ? '>' : '<=') . " NOW() AND " .
            "e.hotdate_datetime >= DATE(" . to_sql($hotdate_datetime) . ") AND e.hotdate_datetime < DATE_ADD(DATE(" . to_sql($hotdate_datetime) . "), INTERVAL 1 DAY) " .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.hotdate_datetime " . ($upcoming ? 'ASC' : 'DESC') . ", e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }

    static function hotdates_random_hotdates_sql_base($upcoming)
    {
        $order_by_from_settings = self::order_by_from_settings();
        $sql = "hotdates_hotdate as e, geo_city as c WHERE c.city_id = e.city_id AND e.hotdate_private = 0 AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) " . ($upcoming ? '>' : '<=') . " NOW() ORDER BY RAND()";
        return array('query' => $sql, 'columns' => 'e.*, c.city_title');
    }
    static function settings()
    {
        global $g_user;

        if(!self::$m_settings)
        {
            self::$m_settings = DB::row("SELECT * FROM hotdates_setting WHERE user_id = " . $g_user['user_id'] . " LIMIT 1");
            if(!self::$m_settings)
            {
                self::$m_settings = array('category_id' => 0);
            }
        }

        return self::$m_settings;
    }

    static function setting_set($name, $value)
    {
        self::settings();

        self::$m_settings[$name] = $value;
    }

    static function settings_save()
    {
        global $g_user;

        self::settings();

        if(isset(self::$m_settings['setting_id']))
        {
            DB::execute("UPDATE hotdates_setting SET category_id = " . to_sql(self::$m_settings['category_id'], 'Number') .
               " WHERE user_id = " . $g_user['user_id']);
        }
        else
        {
            DB::execute("INSERT INTO hotdates_setting SET category_id = " . to_sql(self::$m_settings['category_id'], 'Number') .
               ", user_id = " . $g_user['user_id']);
        }
    }

    static function do_upload_hotdate_image($name, $hotdate_id, $time = false, $addOnWall = true, $file = false)
    {
        global $g;
        global $g_user;

        if(!$time) {
            $timeToSql = 'NOW()';
        } else {
            $timeToSql = to_sql($time, 'Text');
        }

        if ($file === false) {
            if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
                $file = $_FILES[$name]['tmp_name'];
            }
        }

        if ($file)
        {
            DB::execute("insert into hotdates_hotdate_image set hotdate_id = " . $hotdate_id . ", user_id = " . $g_user['user_id'] . ", created_at = $timeToSql");
            $image_id = DB::insert_id();

            $sFile_ = $g['path']['dir_files'] . "hotdates_hotdate_images/" . $image_id . "_";
            $im = new Image();

            if ($im->loadImage($file)) {
                $im->resizeWH($im->getWidth(), $im->getHeight(), false, $g['image']['logo'], $g['image']['logo_size']);
                $im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "b.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['hotdates_hotdate_image']['thumbnail_x'], $g['hotdates_hotdate_image']['thumbnail_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['hotdates_hotdate_image']['thumbnail_big_x'], $g['hotdates_hotdate_image']['thumbnail_big_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_b.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['hotdates_hotdate_image']['thumbnail_small_x'], $g['hotdates_hotdate_image']['thumbnail_small_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_s.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_s.jpg", 0777);
            }
            if ($im->loadImage($file)) {
                $im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
                @chmod($sFile_ . "src.jpg", 0777);
            }
            $path = array($sFile_ . 'b.jpg', $sFile_ . 'th.jpg', $sFile_ . 'th_b.jpg', $sFile_ . 'th_s.jpg', $sFile_ . 'src.jpg');
            Common::saveFileSize($path);

            if (!get_param('hotdate_private', 0) && $addOnWall) {
                Wall::add('hotdate_photo', $hotdate_id, false, $time, true);
            }
            self::update_hotdate($hotdate_id);
        }
    }

    static function update_hotdate($hotdate_id)
    {
        $n_images = DB::result("SELECT COUNT(image_id) FROM hotdates_hotdate_image WHERE hotdate_id = ".to_sql($hotdate_id, 'Number'));
        $n_guests = DB::result("SELECT SUM(guest_n_friends + 1) FROM hotdates_hotdate_guest WHERE hotdate_id = ".to_sql($hotdate_id, 'Number'));
        $n_comments = DB::result("SELECT COUNT(comment_id) FROM hotdates_hotdate_comment WHERE hotdate_id = ".to_sql($hotdate_id, 'Number')) +
            DB::result("SELECT COUNT(cc.comment_id) FROM hotdates_hotdate_comment_comment as cc, hotdates_hotdate_comment as c " .
            "WHERE cc.parent_comment_id  = c.comment_id AND c.hotdate_id = ".to_sql($hotdate_id, 'Number'));

        DB::execute("UPDATE hotdates_hotdate SET hotdate_has_images = ". ($n_images ? 1 : 0) .
            ", hotdate_n_guests=".($n_guests ? $n_guests : 0).
            ", hotdate_n_comments=".($n_comments).
            ", updated_at = NOW() WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1");
    }

    static function hotdate_images($hotdate_id, $random = true)
    {
        global $g;

        if($n_images = DB::result("SELECT COUNT(image_id) FROM hotdates_hotdate_image WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1"))
        {
            $image_n = $random ? rand(0, $n_images-1) : 0;
            $image = DB::row("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " ORDER BY image_id DESC LIMIT " . $image_n . ", 1");

            return array(
               "image_thumbnail" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th.jpg",
               "image_thumbnail_s" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th_s.jpg",
               "image_thumbnail_b" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th_b.jpg",
               "image_file" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_b.jpg",
               "photo_id" => $image['image_id'],
               "system" => 0);
        } else {

            // if (Common::isOptionActiveTemplate('hotdate_social_enabled')) {
            //     $images = array(
            //         "image_thumbnail"   => $g['tmpl']['url_tmpl_main'] . "images/hotdate_clock_s.png",
            //         "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/hotdate_clock_s.png",
            //         "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/hotdate_clock_b.png",
            //         "image_file"        => $g['tmpl']['url_tmpl_main'] . "images/hotdate_clock_b.png",
            //         "system" => 1,
            //         "photo_id" => 0,
            //     );
            //     return $images;
            // }
            // entry or hotdate images

            $type = DB::result("SELECT hotdate_private FROM hotdates_hotdate WHERE hotdate_id=".to_sql($hotdate_id,"Number"));

        // entry
            if($type==1) {
                $images = array(
                    "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/carusel_foto_clock.gif",
                    "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/carusel_foto_clock.gif",
                    "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_clock_l.gif",
                    "image_file" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_clock_l.gif",
                    "sysytem" => 1,
                    "photo_id" => 0,
                );
            } else {
                $images = array(
                    "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_02.jpg",
                    "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/carusel_foto01.gif",
                    "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_02_l.jpg",
                    "image_file" => $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_02_l.jpg",
                    "sysytem" => 1,
                    "photo_id" => 0,
                );
            }

            return $images;
        }
    }

    static function delete_hotdate_image_all($hotdateId, $admin = false)
    {
        $hotdate = self::retrieve_hotdate_by_id($hotdateId);
        if($hotdate && ($admin || $hotdate['user_id'] == guid())){
            DB::query("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=" . to_sql($hotdateId), 2);
            while($image = DB::fetch_row(2)){
                self::delete_hotdate_image($image['image_id'], $admin);
            }
        }
        return true;
    }

    static function delete_hotdate_image($image_id, $admin = false)
    {
        global $g;
        global $g_user;

        $image = DB::row("SELECT i.* FROM hotdates_hotdate_image as i, hotdates_hotdate as s, hotdates_hotdate as m WHERE i.image_id=" . to_sql($image_id, 'Number') .
            " AND i.hotdate_id = s.hotdate_id " .
            " AND s.hotdate_id = m.hotdate_id " .
            ($admin ? "" : " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ") ") .
            " LIMIT 1");
        if($image)
        {
            $filename_base = $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'];
            $path = array($filename_base . '_b.jpg', $filename_base . '_th.jpg', $filename_base . '_th_b.jpg', $filename_base . '_th_s.jpg', $filename_base . '_src.jpg');
            Common::saveFileSize($path, false);
            $filename = $filename_base . "_th.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_s.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_b.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_b.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_src.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);

            DB::execute("DELETE FROM hotdates_hotdate_image WHERE image_id=".$image['image_id']. " LIMIT 1");
            Wall::removeImages('hotdate_photo', $image['hotdate_id'], $image['created_at'], 0, 'hotdates_hotdate_image', 'hotdate_id');
            CHotdatesTools::update_hotdate($image['hotdate_id']);
        }
    }

    static function delete_hotdate($hotdate_id, $admin = false)
    {
        global $g;
        global $g_user;

        $hotdate = self::retrieve_hotdate_by_id($hotdate_id);
        if($hotdate && ($admin || $hotdate['user_id'] == $g_user['user_id']))
        {
            DB::query("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=".$hotdate['hotdate_id'], 2);
            while($image = DB::fetch_row(2))
            {
                self::delete_hotdate_image($image['image_id'], $admin);
            }

            DB::query("SELECT * FROM hotdates_hotdate_comment WHERE hotdate_id=".$hotdate['hotdate_id'], 2);
            while($comment = DB::fetch_row(2))
            {
                self::delete_hotdate_comment($comment['comment_id'], $admin);
            }
            DB::execute("DELETE FROM hotdates_hotdate_guest WHERE hotdate_id=".$hotdate['hotdate_id']);//. " LIMIT 1"
            DB::execute("DELETE FROM hotdates_hotdate WHERE hotdate_id=".$hotdate['hotdate_id']. " LIMIT 1");

            Wall::removeBySiteSection('hotdate', $hotdate['hotdate_id']);
        }
    }

    static function delete_hotdate_comment($comment_id, $admin = false)
    {
        $comment = DB::row("SELECT * FROM hotdates_hotdate as m, hotdates_hotdate_comment as c WHERE c.comment_id=" . to_sql($comment_id, 'Number') .
            " AND m.hotdate_id = c.hotdate_id " .
            ($admin ? "" : (" AND (m.user_id = " . guid() . " OR c.user_id = " . guid() . " )")) .
            " LIMIT 1");
        if($comment)
        {
            OutsideImages::on_delete($comment['comment_text']);

            // Delete subcomments for every user
            $sql = 'SELECT cc.*, c.hotdate_id FROM hotdates_hotdate_comment_comment AS cc
                JOIN hotdates_hotdate_comment AS c ON c.comment_id = cc.parent_comment_id
                WHERE c.hotdate_id = ' . to_sql($comment['hotdate_id'], 'Number') . '
                GROUP BY cc.user_id';
            $subComments = DB::rows($sql);

            if(is_array($subComments)) {
                foreach($subComments as $subComment) {
                    self::delete_hotdate_comment_comment($subComment['comment_id'], true);
                }
            }

            DB::execute("DELETE FROM hotdates_hotdate_comment WHERE comment_id=".$comment['comment_id']. " LIMIT 1");

            Wall::remove('hotdate_comment', $comment_id, $comment['user_id']);

            CHotdatesTools::update_hotdate($comment['hotdate_id']);
        }
    }

    static function delete_hotdate_comment_comment($comment_id, $admin = false, $dbIndex = DB_MAX_INDEX)
    {
        $sql = "SELECT cc.*, c.hotdate_id FROM hotdates_hotdate as m, hotdates_hotdate_comment_comment as cc, hotdates_hotdate_comment as c WHERE cc.comment_id=" . to_sql($comment_id, 'Number') .
            " AND cc.parent_comment_id = c.comment_id " .
            " AND m.hotdate_id = c.hotdate_id " .
            ($admin ? "" : (" AND (m.user_id = " . guid() . " OR c.user_id = " . guid() . " OR cc.user_id = " . guid() . " )")) .
            " LIMIT 1";

        $comment = DB::row($sql, $dbIndex);
        if($comment)
        {
            OutsideImages::on_delete($comment['comment_text']);

            DB::execute("DELETE FROM hotdates_hotdate_comment_comment WHERE comment_id=".$comment['comment_id']);

            Wall::remove('hotdate_comment_comment', $comment_id, $comment['user_id']);

            CHotdatesTools::update_hotdate($comment['hotdate_id']);
        }
    }

    static function retrieve_hotdate_by_id($hotdate_id)
    {
        return self::retrieve_hotdate_for_edit_by_id($hotdate_id, true);
    }

    static function retrieve_hotdate_for_edit_by_id($hotdate_id, $admin = false)
    {
        global $g_user;

        return DB::row("SELECT e.*, c.*, cn.*, ct.*, st.* ".
            "FROM hotdates_hotdate as e, hotdates_category as c, geo_country as cn, geo_state as st, geo_city as ct ".
            "WHERE e.hotdate_id=" . to_sql($hotdate_id, 'Number') . " AND e.category_id = c.category_id AND ".
            "e.city_id = ct.city_id AND ct.state_id = st.state_id AND ct.country_id = cn.country_id " .
            ($admin ? "" : " AND e.user_id = " . $g_user['user_id']) .
            " LIMIT 1");
    }

    static function is_hotdate_finished($hotdate)
    {
        return strtotime($hotdate['hotdate_datetime']) + (3 * 60 * 60) < time();
    }

    static function delete_hotdate_guest($hotdate_id, $hotdate_need_update = true)
    {
        global $g_user;

        DB::execute("DELETE FROM hotdates_hotdate_guest WHERE hotdate_id=".to_sql($hotdate_id, 'Nubmer')." AND user_id=".$g_user['user_id']);

        if($hotdate_need_update)
           self::update_hotdate($hotdate_id);
    }

    static function create_hotdate_guest($hotdate_id, $n_friends)
    {
        global $g_user;

        self::delete_hotdate_guest($hotdate_id, false);

        DB::execute("INSERT INTO hotdates_hotdate_guest SET hotdate_id = " . to_sql($hotdate_id, 'Number') .
            ", user_id = " . to_sql($g_user['user_id'], 'Number') .
            ", guest_n_friends = " . to_sql($n_friends, 'Number') .
            ", created_at = NOW()");

        self::update_hotdate($hotdate_id);
    }
}
