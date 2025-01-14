<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CMusicTools {

    static $m_settings = null;
    static $player_containers = array();

    static function split_search_to_words($search) {
        $search = str_replace(array(',', ';', '!', '?', '.'), array(' ', ' ', ' ', ' ', ' '), $search);

        $_words = explode(" ", $search);
        $words = array();
        foreach ($_words as $word) {
            $word = trim($word);

            if (mb_strlen($word) > 2)
                $words[] = $word;
        }

        return $words;
    }

    static function order_by_from_settings() {
        $wheres = array();

        $settings = self::settings();

        if ($settings['category_id'])
            $wheres[] = 'm.category_id = ' . $settings['category_id'] . ' DESC';

        switch ($settings['setting_limit']) {
            case 'today':
                $wheres[] = 's.created_at > SUBDATE(NOW(), INTERVAL 1 DAY) DESC';
                break;
            case 'week':
                $wheres[] = 's.created_at > SUBDATE(NOW(), INTERVAL 7 DAY) DESC';
                break;
            case 'month':
                $wheres[] = 's.created_at > SUBDATE(NOW(), INTERVAL 30 DAY) DESC';
                break;
        }

        return implode(", ", $wheres);
    }

    static function musician_order_by_from_settings() {
        $wheres = array();

        $settings = self::settings();

        if ($settings['category_id'])
            $wheres[] = 'm.category_id = ' . $settings['category_id'] . ' DESC';

        switch ($settings['setting_limit']) {
            case 'today':
                $wheres[] = 'm.created_at > SUBDATE(NOW(), INTERVAL 1 DAY) DESC';
                break;
            case 'week':
                $wheres[] = 'm.created_at > SUBDATE(NOW(), INTERVAL 7 DAY) DESC';
                break;
            case 'month':
                $wheres[] = 'm.created_at > SUBDATE(NOW(), INTERVAL 30 DAY) DESC';
                break;
        }

        return implode(", ", $wheres);
    }

    static function songs_by_user_sql_base($user_id) {
        $sql = "music_song as s, user as u, music_musician as m WHERE s.user_id = " . to_sql($user_id, 'Number') .
                " AND s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function musicians_by_query_sql_base($query) {
        $order_by_from_settings = self::musician_order_by_from_settings();
        $where_from_query = '';

        $words = self::split_search_to_words($query);
        $searches = array();

        foreach ($words as $word) {
            $searches[] = "CONCAT_WS('', m.musician_name, m.musician_about) LIKE " . to_sql('%' . $word . '%');
        }

        DB::query("SELECT * FROM music_category ORDER BY category_id");
        $categories = array();
        while ($category = DB::fetch_row()) {
            $category['category_title'] = isset($l['all'][$category['category_title']]) ? $l['all'][$category['category_title']] : $category['category_title'];
            foreach ($words as $word) {
                if (stripos($category['category_title'], $word) !== false) {
                    $categories[] = $category['category_id'];
                    break;
                }
            }
        }

        if (count($categories))
            array_unshift($searches, "m.category_id IN (" . implode(', ', $categories) . ")");

        if (count($searches))
            $where_from_query = "(" . implode(' OR ', $searches) . ") AND ";

        $sql = "music_musician as m, user as u WHERE " . $where_from_query .
                " m.user_id = u.user_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " m.created_at DESC";

        return array('query' => $sql, 'columns' => ' m.*, u.user_id, u.name');
    }

    static function musicians_by_song_year_sql_base($song_year) {
        return self::musicians_by_musician_founded_sql_base($song_year);
    }

    static function musicians_by_musician_founded_sql_base($musician_founded) {
        $order_by_from_settings = self::musician_order_by_from_settings();

        $sql = "music_musician as m, user as u WHERE m.musician_founded = " . to_sql($musician_founded) . ' AND ' .
                " m.user_id = u.user_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " m.created_at DESC";

        return array('query' => $sql, 'columns' => ' m.*, u.user_id, u.name');
    }

    static function musicians_by_country_id_sql_base($country_id) {
        $order_by_from_settings = self::musician_order_by_from_settings();

        $sql = "music_musician as m, user as u WHERE m.country_id = " . to_sql($country_id) . ' AND ' .
                " m.user_id = u.user_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " m.created_at DESC";

        return array('query' => $sql, 'columns' => ' m.*, u.user_id, u.name');
    }

    static function musicians_by_category_id_sql_base($category_id) {
        $order_by_from_settings = self::musician_order_by_from_settings();

        $sql = "music_musician as m, user as u WHERE m.category_id = " . to_sql($category_id) . ' AND ' .
                " m.user_id = u.user_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " m.created_at DESC";

        return array('query' => $sql, 'columns' => ' m.*, u.user_id, u.name');
    }

    static function musicians_by_user_sql_base($user_id) {
        $sql = "music_musician as m, user as u WHERE m.user_id = " . to_sql($user_id, 'Number') .
                " AND m.user_id = u.user_id " .
                " ORDER BY m.created_at DESC";

        return array('query' => $sql, 'columns' => ' m.*, u.user_id, u.name');
    }

    static function musicians_recent_sql_base() {
        $sql = "music_musician as m, user as u WHERE " .
                " m.user_id = u.user_id ORDER BY m.created_at DESC";

        return array('query' => $sql, 'columns' => ' m.*, u.user_id, u.name');
    }

    static function songs_by_rand_sql_base() {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " RAND()";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_by_musician_sql_base($musician_id, $exclude_song_id = null) {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.musician_id = " . to_sql($musician_id, 'Number') .
                " AND s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                ($exclude_song_id ? (" AND s.song_id <> " . to_sql($exclude_song_id, 'Number')) : '') .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_rating DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_by_song_year_sql_base($song_year) {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.song_year = " . to_sql($song_year) .
                " AND s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_rating DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_by_musician_founded_sql_base($musician_founded) {
        return self::songs_by_song_year_sql_base($musician_founded);
    }

    static function songs_by_country_id_sql_base($country_id) {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE m.country_id = " . to_sql($country_id) .
                " AND s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_rating DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_by_category_id_sql_base($category_id) {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE m.category_id = " . to_sql($category_id) .
                " AND s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_rating DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_by_query_sql_base($query) {
        $order_by_from_settings = self::order_by_from_settings();

        $where_from_query = '';

        $words = self::split_search_to_words($query);
        $searches = array();

        foreach ($words as $word) {
            $searches[] = "CONCAT_WS('', s.song_title, s.song_about) LIKE " . to_sql('%' . $word . '%');
        }

        DB::query("SELECT * FROM music_category ORDER BY category_id");
        $categories = array();
        while ($category = DB::fetch_row()) {
            $category['category_title'] = isset($l['all'][$category['category_title']]) ? $l['all'][$category['category_title']] : $category['category_title'];
            foreach ($words as $word) {
                if (stripos($category['category_title'], $word) !== false) {
                    $categories[] = $category['category_id'];
                    break;
                }
            }
        }

        if (count($categories))
            array_unshift($searches, "m.category_id IN (" . implode(', ', $categories) . ")");

        if (count($searches))
            $where_from_query = "(" . implode(' OR ', $searches) . ") AND ";

        $sql = "music_song as s, user as u, music_musician as m WHERE " . $where_from_query .
                " s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_rating DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_recent_sql_base() {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_top_plays_sql_base() {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_n_plays DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_top_rated_sql_base() {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_rating DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function songs_most_discussed_sql_base() {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "music_song as s, user as u, music_musician as m WHERE s.user_id = u.user_id AND s.musician_id = m.musician_id " .
                " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') . " s.song_n_comments DESC, s.created_at DESC";

        return array('query' => $sql, 'columns' => 's.*, m.*, u.user_id, u.name');
    }

    static function comments_by_musician_sql_base($musician_id) {
        $sql = "music_musician_comment as c, user as u WHERE c.musician_id = " . to_sql($musician_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at DESC";

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function comments_by_song_sql_base($song_id) {
        $sql = "music_song_comment as c, user as u WHERE c.song_id = " . to_sql($song_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at DESC";

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function retrieve_from_sql($sql) {
        DB::query($sql);
        $results = array();

        while ($row = DB::fetch_row()) {
            $results[] = $row;
        }

        return $results;
    }

    static function retrieve_from_sql_base($sql_base, $limit = 0, $shift = 0) {
        return self::retrieve_from_sql("SELECT " . $sql_base['columns'] . " FROM " . $sql_base['query'] . ($limit ? (" LIMIT " . intval($shift) . ", " . intval($limit)) : ''));
    }

    static function count_from_sql_base($sql_base) {
        return DB::result("SELECT COUNT(*) FROM " . $sql_base['query']);
    }

    static function settings() {
        global $g_user;

        if (!self::$m_settings) {
            self::$m_settings = DB::row("SELECT * FROM music_setting WHERE user_id = " . $g_user['user_id'] . " LIMIT 1");
            if (!self::$m_settings) {
                self::$m_settings = array('category_id' => 0, 'setting_limit' => 'all');
            }
        }

        return self::$m_settings;
    }

    static function setting_set($name, $value) {
        self::settings();

        self::$m_settings[$name] = $value;
    }

    static function settings_save() {
        global $g_user;

        self::settings();

        if (isset(self::$m_settings['setting_id'])) {
            DB::execute("UPDATE music_setting SET category_id = " . to_sql(self::$m_settings['category_id'], 'Number') .
                    ", setting_limit = " . to_sql(self::$m_settings['setting_limit']) .
                    " WHERE user_id = " . $g_user['user_id']);
        } else {
            DB::execute("INSERT INTO music_setting SET category_id = " . to_sql(self::$m_settings['category_id'], 'Number') .
                    ", setting_limit = " . to_sql(self::$m_settings['setting_limit']) .
                    ", user_id = " . $g_user['user_id']);
        }
    }

    static function generate_upload_name() {
        global $g;

        do {
            $upload_slug = md5(rand(100000, 999999));
            $upload_name = 'upload_' . $upload_slug;
        } while (custom_file_exists($g['path']['dir_files'] . "music/tmp/" . $upload_name . ".mp3"));

        return $upload_name;
    }

    static function cleanup_upload_folder() {
        global $g;

        $path = $g['path']['dir_files'] . "music/tmp/";

        //popcorn modified s3 bucket music upload 2024-05-06
        if(isS3SubDirectory($path)) {
            $path = $g['path']['dir_files'] . "temp/";
        }

        $files = scandir($path);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.svn') {
                $file_path = $path . DIRECTORY_SEPARATOR . $file;
                if (is_file($file_path)) {
                    $path_parts = pathinfo($file_path);

                    if (isset($path_parts['extension']) && $path_parts['extension'] == 'mp3') {
                        if (time() - filemtime($file_path) > 24 * 60 * 60)
                            @unlink($file_path);
                    }
                }
            }
        }
    }

    static function move_uploaded_file($upload_name, $song_id) {
        global $g;
        $upload_name = self::sanitize_upload_name($upload_name);
        $filename = $g['path']['dir_files'] . "music/tmp/" . $upload_name . ".mp3";

        //popcorn modified s3 bucket music upload 2024-05-06
        if(isS3SubDirectory($filename)) {
            $filename = $g['path']['dir_files'] . "temp/" . $upload_name . ".mp3";
        }
        
        if (file_exists($filename)) {
            //popcorn modified s3 bucket music upload 2024-05-06
            if(isS3SubDirectory($g['path']['dir_files'] . "music/tmp/" . $upload_name . ".mp3")) {
                custom_file_upload($filename, self::song_filename($song_id));
                return true;
            } else {
                $new_filename = $g['path']['dir_files'] . self::song_filename($song_id);
                if (file_exists($new_filename))
                    Common::saveFileSize($new_filename, false);
                    @unlink($new_filename);
                if (rename($filename, $new_filename)) {
                    Common::saveFileSize($new_filename);
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    static function sanitize_upload_name($upload_name) {
        return str_replace(array('/', '\\', '.'), array('', '', ''), $upload_name);
    }

    static function do_upload() {
        global $g;

        self::cleanup_upload_folder();

        if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']["tmp_name"])) {
            $upload_name = get_param('upload_name');
            if ($upload_name) {
                move_uploaded_file($_FILES['file']["tmp_name"], $g['path']['dir_files'] . "music/tmp/" . self::sanitize_upload_name($upload_name) . ".mp3");
                CStatsTools::count('mp3_uploaded');
                die('ok');
            }
        }
    }

    static function do_upload_musician_image($name, $musician_id, $time = false) {
        global $g;
        global $g_user;

        if (!$time) {
            $timeToSql = 'NOW()';
        } else {
            $timeToSql = to_sql($time, 'Text');
        }

        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
            DB::execute("insert into music_musician_image set musician_id = " . $musician_id . ", user_id = " . $g_user['user_id'] . ", created_at = $timeToSql;");
            $image_id = DB::insert_id();

            $sFile_ = $g['path']['dir_files'] . "music_musician_images/" . $image_id . "_";
            $im = new Image();

            if ($im->loadImage($_FILES[$name]['tmp_name'])) {
                #Old settings $g['music_musician_image']['big_x'], $g['music_musician_image']['big_y'],
                $im->resizeWH($im->getWidth(), $im->getHeight(), false, $g['image']['logo'], $g['image']['logo_size']);
                $im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "b.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['music_musician_image']['thumbnail_x'], $g['music_musician_image']['thumbnail_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['music_musician_image']['thumbnail_big_x'], $g['music_musician_image']['thumbnail_big_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_b.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['music_musician_image']['thumbnail_small_x'], $g['music_musician_image']['thumbnail_small_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_s.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_s.jpg", 0777);
            }
            if ($im->loadImage($_FILES[$name]['tmp_name'])) {
                $im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
                @chmod($sFile_ . "src.jpg", 0777);
            }

            $path = array($sFile_ . 'b.jpg', $sFile_ . 'th.jpg', $sFile_ . 'th_b.jpg', $sFile_ . 'th_s.jpg', $sFile_ . 'src.jpg');
            Common::saveFileSize($path);
            Wall::add('musician_photo', $musician_id, false, $time, true);

            self::update_musician($musician_id);
        }
    }

    static function do_upload_song_image($name, $song_id, $time = false, $fileImage = null) {
        global $g;
        global $g_user;

        if (!$time) {
            $timeToSql = 'NOW()';
        } else {
            $timeToSql = to_sql($time, 'Text');
        }

        if ($fileImage === null
            && isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
            $fileImage = $_FILES[$name]["tmp_name"];
        }
        if ($fileImage) {
            DB::execute("insert into music_song_image set song_id = " . $song_id . ", user_id = " . $g_user['user_id'] . ", created_at = $timeToSql;");
            $image_id = DB::insert_id();
            
            $sFile_ = $g['path']['dir_files'] . "music_song_images/" . $image_id . "_";

            //popcorn modified s3 bucket music image upload 2024-05-06
            if(isS3SubDirectory($sFile_)) {
                $sFile_ = $g['path']['dir_files'] . "temp/music_song_images/" . $image_id . "_";
            }
            $im = new Image();

            if ($im->loadImage($fileImage)) {
                #Oryx уже отжил с флеш плеером поменял под EDGE 825х825
                $im->resizeWH($g['music_song_image']['big_x'], $g['music_song_image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
                $im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "b.jpg", 0777);
            }

            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {//85х64
                $im->resizeCropped($g['music_song_image']['thumbnail_x'], $g['music_song_image']['thumbnail_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th.jpg", 0777);
            }

            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {//160х120
                $im->resizeCropped($g['music_song_image']['thumbnail_big_x'], $g['music_song_image']['thumbnail_big_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_b.jpg", 0777);
            }

            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {//39х29
                $im->resizeCropped($g['music_song_image']['thumbnail_small_x'], $g['music_song_image']['thumbnail_small_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_s.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_s.jpg", 0777);
            }

            if ($im->loadImage($fileImage)) {
                $im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
                @chmod($sFile_ . "src.jpg", 0777);
            }

            $path = array($sFile_ . 'b.jpg', $sFile_ . 'th.jpg', $sFile_ . 'th_b.jpg', $sFile_ . 'th_s.jpg', $sFile_ . 'src.jpg');
            Common::saveFileSize($path);
            Wall::add('music_photo', $song_id, false, $time, true);

            //popcorn modified s3 bucket music image upload 2024-05-06
            if(isS3SubDirectory($g['path']['dir_files'] . "music_song_images/" . $image_id . "_")) {
                $file_types = array('b.jpg', 'th.jpg', 'th_b.jpg', 'th_s.jpg', 'src.jpg');

                foreach ($file_types as $file_type) {
                    $file_path = $sFile_ . $file_type;

                    if(file_exists($file_path)) {
                        custom_file_upload($file_path, "music_song_images/" . $image_id . '_' . $file_type);
                    }
                }
            }

            self::update_song($song_id);
        }
    }

    static function update_musician($musician_id) {
        $n_images = DB::result("SELECT COUNT(image_id) FROM music_musician_image WHERE musician_id = " . to_sql($musician_id, 'Number'));

        $rating_row = DB::row("SELECT SUM(vote_rating), COUNT(vote_id) FROM music_musician_vote WHERE musician_id = " . to_sql($musician_id, 'Number'));
        $n_votes = $rating_row['COUNT(vote_id)'] ? $rating_row['COUNT(vote_id)'] : 0;
        $overal_rating = $n_votes ? (floor($rating_row['SUM(vote_rating)'] / $n_votes)) : 0;

        DB::execute("UPDATE music_musician SET musician_has_images = " . ($n_images ? 1 : 0) .
                ", musician_rating=" . $overal_rating .
                ", musician_n_votes=" . $n_votes .
                ", updated_at = NOW() WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
    }

    static function update_song($song_id) {
        $n_comments = DB::result("SELECT COUNT(comment_id) FROM music_song_comment WHERE song_id = " . to_sql($song_id, 'Number'));

        $n_images = DB::result("SELECT COUNT(image_id) FROM music_song_image WHERE song_id = " . to_sql($song_id, 'Number'));

        $rating_row = DB::row("SELECT SUM(vote_rating), COUNT(vote_id) FROM music_song_vote WHERE song_id = " . to_sql($song_id, 'Number'));

        $n_votes = $rating_row['COUNT(vote_id)'] ? $rating_row['COUNT(vote_id)'] : 0;
        $overal_rating = $n_votes ? (floor($rating_row['SUM(vote_rating)'] / $n_votes)) : 0;

        DB::execute("UPDATE music_song SET song_has_images = " . ($n_images ? 1 : 0) .
                ", song_rating=" . $overal_rating .
                ", song_n_votes=" . $n_votes .
                ", song_n_comments=" . $n_comments .
                ", updated_at = NOW() WHERE song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
    }

    static function song_filename($song_id) {
        return "music/" . $song_id . ".mp3";
    }

    static function song_images($song_id, $musician_id) {
        global $g;

        if ($n_images = DB::result("SELECT COUNT(image_id) FROM music_song_image WHERE song_id=" . to_sql($song_id, 'Number') . " LIMIT 1")) {
            $image_n = rand(0, $n_images - 1);
            $image = DB::row("SELECT * FROM music_song_image WHERE song_id=" . to_sql($song_id, 'Number') . " ORDER BY image_id LIMIT " . $image_n . ", 1");

            return array(
                "image_thumbnail" => $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th.jpg",
                "image_thumbnail_s" => $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th_s.jpg",
                "image_thumbnail_b" => $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th_b.jpg",
                "image_file" => $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_b.jpg");
        } else {
            return self::musician_images($musician_id);
        }
    }

    static function musician_images($musician_id) {
        global $g;

        if ($n_images = DB::result("SELECT COUNT(image_id) FROM music_musician_image WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1")) {
            $image_n = rand(0, $n_images - 1);
            $image = DB::row("SELECT * FROM music_musician_image WHERE musician_id=" . to_sql($musician_id, 'Number') . " ORDER BY image_id LIMIT " . $image_n . ", 1");

            return array(
                "image_thumbnail" => $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th.jpg",
                "image_thumbnail_s" => $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th_s.jpg",
                "image_thumbnail_b" => $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th_b.jpg",
                "image_file" => $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_b.jpg");
        } else {
            return array(
                "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/music/foto_02.jpg",
                "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/music/foto_s02.jpg",
                "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/music/foto_02_l.jpg",
                "image_file" => $g['tmpl']['url_tmpl_main'] . "images/music/foto_02_l.jpg");
        }
    }

    static function song_player($song_id, $song_length, $container_id, $player, $player_lx, $player_ly) {
        global $g;

        $player_url = $g['tmpl']['url_tmpl_main'] . 'player/' . $player;
        $express_install_url = $g['tmpl']['url_tmpl_main'] . '_server/swfobject/expressInstall.swf';
        DB::query("SELECT `updated_at` FROM music_song WHERE song_id= ".$song_id);
        if($song = DB::fetch_row()) {
            $song_filename = urlencode($g['path']['url_files'] . self::song_filename($song_id) .'?time='.  $song['updated_at']);
        }

        if (!isset(self::$player_containers[$container_id])){
            self::$player_containers[$container_id] = 1;
        }

        $song_index = ($container_id - 1) * 100 + self::$player_containers[$container_id];
        ++self::$player_containers[$container_id];

        $songFile = $g['path']['url_files'] . CMusicTools::song_filename($song_id) .'?time='.  $song['updated_at'];
        $code = '<audio class="player_audio" src="' . $songFile . '" controls></audio>';

        return $code;

        $code = <<<EOT
<script type="text/javascript">
    var flashvars = {};
    flashvars.song = "{$song_filename}";
    flashvars.volume = 100;
    flashvars.duration = {$song_length};
    flashvars.index = {$song_index};
    var params = {};
    params.play = "true";
    params.loop = "false";
    params.wmode = "transparent";
    params.allowscriptaccess = "always";
    params.quality = "high";
    var attributes = {};
    attributes.styleclass = "music_flash_player";
    attributes.song_index = {$song_index};
    attributes.song_id = {$song_id};
    swfobject.embedSWF("{$player_url}", "music_flash_player_{$song_index}", "{$player_lx}", "{$player_ly}", "9.0.0", "{$express_install_url}", flashvars, params, attributes);

    </script><div style="width:{$player_lx};height:{$player_ly}" id="music_flash_player_{$song_index}"><a href="https://www.adobe.com/go/getflashplayer" target="_blank"><img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"></a></div>
EOT;

        return $code;
    }

    static function delete_musician_comment($comment_id, $admin = false) {
        $comment = DB::row("SELECT * FROM music_musician as m, music_musician_comment as c WHERE c.comment_id=" . to_sql($comment_id, 'Number') .
                        " AND m.musician_id = c.musician_id " .
                        ($admin ? "" : " AND (m.user_id = " . to_sql(guid(), 'Number') . " OR c.user_id = " . to_sql(guid(), 'Number') . " )") .
                        " LIMIT 1");
        if ($comment) {
            DB::execute("DELETE FROM music_musician_comment WHERE comment_id=" . $comment['comment_id'] . " LIMIT 1");
            Wall::remove('musician_comment', $comment_id, 0);
            CMusicTools::update_musician($comment['musician_id']);
        }
    }

    static function delete_song_comment($comment_id, $admin = false) {
        $comment = DB::row("SELECT * FROM music_song as m, music_song_comment as c WHERE c.comment_id=" . to_sql($comment_id, 'Number') .
                        " AND m.song_id = c.song_id " .
                        ($admin ? "" : " AND (m.user_id = " . to_sql(guid(), 'Number') . " OR c.user_id = " . to_sql(guid(), 'Number') . " )") .
                        " LIMIT 1");
        if ($comment) {

            DB::execute("DELETE FROM music_song_comment WHERE comment_id=" . $comment['comment_id'] . " LIMIT 1");

            Wall::remove('music_comment', $comment_id);

            CMusicTools::update_song($comment['song_id']);
        }
    }

    static function delete_song_image($image_id, $admin = false) {
        global $g;
        global $g_user;

        $image = DB::row("SELECT i.* FROM music_song_image as i, music_song as s, music_musician as m WHERE i.image_id=" . to_sql($image_id, 'Number') .
                        " AND i.song_id = s.song_id " .
                        " AND s.musician_id = m.musician_id " .
                        ($admin ? "" : " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ")") .
                        " LIMIT 1");
        if ($image) {
            $filename_base = $g['path']['url_files'] . "music_song_images/" . $image['image_id'];
            $path = array($filename_base . '_b.jpg', $filename_base . '_th.jpg', $filename_base . '_th_b.jpg', $filename_base . '_th_s.jpg', $filename_base . '_src.jpg');
            Common::saveFileSize($path, false);
            $filename = $filename_base . "_th.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_s.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_b.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_b.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_src.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);

            //popcorn modified s3 bucket music image delete 2024-05-06
            if(isS3SubDirectory($filename_base)) {
                custom_file_delete($filename_base . "_th.jpg");
                custom_file_delete($filename_base . "_th_s.jpg");
                custom_file_delete($filename_base . "_th_b.jpg");
                custom_file_delete($filename_base . "_b.jpg");
                custom_file_delete($filename_base . "_src.jpg");
            }

            DB::execute("DELETE FROM music_song_image WHERE image_id=" . $image['image_id'] . " LIMIT 1");

            Wall::removeImages('music_photo', $image['song_id'], $image['created_at'], 0, 'music_song_image', 'song_id');

            Wall::deleteItemForUserByItem($image['song_id'], 'music', $image['user_id']);

            CMusicTools::update_song($image['song_id']);
        }
    }

    static function delete_musician_image($image_id, $admin = false) {
        global $g;
        global $g_user;

        $image = DB::row("SELECT i.* FROM music_musician_image as i, music_musician as s, music_musician as m WHERE i.image_id=" . to_sql($image_id, 'Number') .
                        " AND i.musician_id = s.musician_id " .
                        " AND s.musician_id = m.musician_id " .
                        ($admin ? "" : " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ") ") .
                        " LIMIT 1");
        if ($image) {
            $filename_base = $g['path']['url_files'] . "music_musician_images/" . $image['image_id'];
            $path = array($filename_base . '_b.jpg', $filename_base . '_th.jpg', $filename_base . '_th_b.jpg', $filename_base . '_th_s.jpg', $filename_base . '_src.jpg');
            Common::saveFileSize($path, false);
            $filename = $filename_base . "_th.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_s.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_b.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_b.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_src.jpg";
            if (custom_file_exists($filename))
                @unlink($filename);

            DB::execute("DELETE FROM music_musician_image WHERE image_id=" . $image['image_id'] . " LIMIT 1");
            Wall::removeImages('musician_photo', $image['musician_id'], $image['created_at'], 0, 'music_musician_image', 'musician_id');

            Wall::deleteItemForUserByItem($image['musician_id'], 'musician', $image['user_id']);

            CMusicTools::update_musician($image['musician_id']);
        }
    }

    static function delete_song($song_id, $admin = false) {
        global $g;
        global $g_user;

        $song = DB::row("SELECT * FROM music_song as s, music_musician as m WHERE s.song_id=" . to_sql($song_id, 'Number') .
                        " AND s.musician_id = m.musician_id " .
                        ($admin ? "" : " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ")") .
                        " LIMIT 1");
        if ($song) {
            Wall::deleteItemForUserByItemOnly($song_id, 'music');

            DB::query("SELECT * FROM music_song_image WHERE song_id=" . $song['song_id'], 1);
            while ($image = DB::fetch_row(1)) {
                self::delete_song_image($image['image_id'], $admin);
            }

            $filename = $g['path']['dir_files'] . self::song_filename($song_id);
            Common::saveFileSize($filename, false);
            if (custom_file_exists($filename)) {
                //popcorn modified s3 bucket music delete 2024-05-06
                if(isS3SubDirectory($filename)) {
                    custom_file_delete($filename);
                } else {
                    @unlink($filename);
                }
            }

            $sql = 'SELECT * FROM music_song_comment
                WHERE song_id = ' . to_sql($song['song_id'], 'Number');
            $rows = DB::rows($sql, 2);

            DB::execute("DELETE FROM music_song_comment WHERE song_id=" . $song['song_id']);
            DB::execute("DELETE FROM music_song_vote WHERE song_id=" . $song['song_id']);

            if (is_array($rows) && count($rows)) {
                foreach ($rows as $row) {
                    Wall::remove('music_comment', $row['comment_id'], 0);
                }
            }

            DB::execute("DELETE FROM music_song WHERE song_id=" . $song['song_id'] . " LIMIT 1");

            Wall::removeBySiteSection('music', $song_id);

            CMusicTools::update_musician($song['musician_id']);
        }
    }

    static function delete_musician($musician_id, $admin = false) {
        global $g;
        global $g_user;

        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') .
                        ($admin ? "" : (" AND user_id = " . $g_user['user_id'])) . " LIMIT 1");
        if ($musician) {
            $sql = "SELECT * FROM music_musician_image WHERE musician_id=" . $musician['musician_id'];

            $rows = DB::rows($sql, 2);

            if (is_array($rows) && count($rows)) {
                foreach ($rows as $row) {
                    self::delete_musician_image($row['image_id'], $admin);
                }
            }
            DB::execute("DELETE FROM music_musician_image WHERE musician_id=" . $musician['musician_id']);

            DB::query("SELECT * FROM music_song WHERE musician_id=" . $musician['musician_id'], 2);
            while ($song = DB::fetch_row(2)) {
                self::delete_song($song['song_id'], $admin);
            }

            $sql = 'SELECT * FROM music_musician_comment
                WHERE musician_id = ' . to_sql($musician['musician_id'], 'Number');
            $rows = DB::rows($sql, 2);

            DB::execute("DELETE FROM music_musician_comment WHERE musician_id=" . $musician['musician_id']);

            if (is_array($rows) && count($rows)) {
                foreach ($rows as $row) {
                    Wall::remove('musician_comment', $row['comment_id'], 0);
                }
            }

            DB::execute("DELETE FROM music_musician_vote WHERE musician_id=" . $musician['musician_id']);

            DB::execute("DELETE FROM music_musician WHERE musician_id=" . $musician['musician_id'] . " LIMIT 1");

            Wall::removeBySiteSection('musician', $musician_id);
        }
    }

}

class CMusicMp3File {

    protected $block;
    protected $blockpos;
    protected $blockmax;
    protected $blocksize;
    protected $fd;
    protected $bitpos;
    protected $mp3data;

    public function __construct($filename) {
        $this->powarr = array(0 => 1, 1 => 2, 2 => 4, 3 => 8, 4 => 16, 5 => 32, 6 => 64, 7 => 128);
        $this->blockmax = 1024;

        $this->mp3data = array();
        $this->mp3data['Filesize'] = filesize($filename);

        $this->fd = fopen($filename, 'rb');
        $this->prefetchblock();
        $this->readmp3frame();
    }

    public function __destruct() {
        fclose($this->fd);
    }

    //-------------------
    public function get_metadata() {
        return $this->mp3data;
    }

    protected function readmp3frame() {
        $iscbrmp3 = true;
        if ($this->startswithid3())
            $this->skipid3tag();
        else if ($this->containsvbrxing()) {
            $this->mp3data['Encoding'] = 'VBR';
            $iscbrmp3 = false;
        } else if ($this->startswithpk()) {
            $this->mp3data['Encoding'] = 'Unknown';
            $iscbrmp3 = false;
        }

        if ($iscbrmp3) {
            $i = 0;
            $max = 5000;
            //look in 5000 bytes...
            //the largest framesize is 4609bytes(256kbps@8000Hz  mp3)
            for ($i = 0; $i < $max; $i++) {
                //looking for 1111 1111 111 (frame synchronization bits)
                if ($this->getnextbyte() == 0xFF)
                    if ($this->getnextbit() && $this->getnextbit() && $this->getnextbit())
                        break;
            }
            if ($i == $max)
                $iscbrmp3 = false;
        }

        if ($iscbrmp3) {
            $this->mp3data['Encoding'] = 'CBR';
            $this->mp3data['MPEG version'] = $this->getnextbits(2);
            $this->mp3data['Layer Description'] = $this->getnextbits(2);
            $this->mp3data['Protection Bit'] = $this->getnextbits(1);
            $this->mp3data['Bitrate Index'] = $this->getnextbits(4);
            $this->mp3data['Sampling Freq Idx'] = $this->getnextbits(2);
            $this->mp3data['Padding Bit'] = $this->getnextbits(1);
            $this->mp3data['Private Bit'] = $this->getnextbits(1);
            $this->mp3data['Channel Mode'] = $this->getnextbits(2);
            $this->mp3data['Mode Extension'] = $this->getnextbits(2);
            $this->mp3data['Copyright'] = $this->getnextbits(1);
            $this->mp3data['Original Media'] = $this->getnextbits(1);
            $this->mp3data['Emphasis'] = $this->getnextbits(1);
            $this->mp3data['Bitrate'] = self::bitratelookup($this->mp3data);
            $this->mp3data['Sampling Rate'] = self::samplelookup($this->mp3data);
            $this->mp3data['Frame Size'] = self::getframesize($this->mp3data);
            $this->mp3data['Length'] = self::getduration($this->mp3data, $this->tell2());
            $this->mp3data['Length mm:ss'] = self::seconds_to_mmss($this->mp3data['Length']);

            if ($this->mp3data['Bitrate'] == 'bad' ||
                    $this->mp3data['Bitrate'] == 'free' ||
                    $this->mp3data['Sampling Rate'] == 'unknown' ||
                    $this->mp3data['Frame Size'] == 'unknown' ||
                    $this->mp3data['Length'] == 'unknown')
                $this->mp3data = array('Filesize' => $this->mp3data['Filesize'], 'Encoding' => 'Unknown');
        }
        else {
            if (!isset($this->mp3data['Encoding']))
                $this->mp3data['Encoding'] = 'Unknown';
        }
    }

    protected function tell() {
        return ftell($this->fd);
    }

    protected function tell2() {
        return ftell($this->fd) - $this->blockmax + $this->blockpos - 1;
    }

    protected function startswithid3() {
        return ($this->block[1] == 73 && //I
                $this->block[2] == 68 && //D
                $this->block[3] == 51);  //3
    }

    protected function startswithpk() {
        return ($this->block[1] == 80 && //P
                $this->block[2] == 75);  //K
    }

    protected function containsvbrxing() {
        //echo "<!--".$this->block[37]." ".$this->block[38]."-->";
        //echo "<!--".$this->block[39]." ".$this->block[40]."-->";
        return(
                ($this->block[37] == 88 && //X 0x58
                $this->block[38] == 105 && //i 0x69
                $this->block[39] == 110 && //n 0x6E
                $this->block[40] == 103)   //g 0x67
                /*               ||
                  ($this->block[21]==88  && //X 0x58
                  $this->block[22]==105 && //i 0x69
                  $this->block[23]==110 && //n 0x6E
                  $this->block[24]==103)   //g 0x67 */
                );
    }

    protected function debugbytes() {
        for ($j = 0; $j < 10; $j++) {
            for ($i = 0; $i < 8; $i++) {
                if ($i == 4)
                    echo " ";
                echo $this->getnextbit();
            }
            echo "<BR>";
        }
    }

    protected function prefetchblock() {
        $block = fread($this->fd, $this->blockmax);
        $this->blocksize = strlen($block);
        $this->block = unpack("C*", $block);
        $this->blockpos = 0;
    }

    protected function skipid3tag() {
        $bits = $this->getnextbits(24); //ID3
        $bits.=$this->getnextbits(24); //v.v flags
        //3 bytes 1 version byte 2 byte flags
        $arr = array();
        $arr['ID3v2 Major version'] = bindec(substr($bits, 24, 8));
        $arr['ID3v2 Minor version'] = bindec(substr($bits, 32, 8));
        $arr['ID3v2 flags'] = bindec(substr($bits, 40, 8));
        if (substr($bits, 40, 1))
            $arr['Unsynchronisation'] = true;
        if (substr($bits, 41, 1))
            $arr['Extended header'] = true;
        if (substr($bits, 42, 1))
            $arr['Experimental indicator'] = true;
        if (substr($bits, 43, 1))
            $arr['Footer present'] = true;

        $size = "";
        for ($i = 0; $i < 4; $i++) {
            $this->getnextbit(); //skip this bit, should be 0
            $size.= $this->getnextbits(7);
        }

        $arr['ID3v2 Tags Size'] = bindec($size); //now the size is in bytes;
        if ($arr['ID3v2 Tags Size'] - $this->blockmax > 0) {
            fseek($this->fd, $arr['ID3v2 Tags Size'] + 10);
            $this->prefetchblock();
            if (isset($arr['Footer present']) && $arr['Footer present']) {
                for ($i = 0; $i < 10; $i++)
                    $this->getnextbyte();//10 footer bytes
            }
        } else {
            for ($i = 0; $i < $arr['ID3v2 Tags Size']; $i++)
                $this->getnextbyte();
        }
    }

    protected function getnextbit() {
        if ($this->bitpos == 8)
            return false;

        $b = 0;
        $whichbit = 7 - $this->bitpos;
        $mult = $this->powarr[$whichbit]; //$mult = pow(2,7-$this->pos);
        $b = $this->block[$this->blockpos + 1] & $mult;
        $b = $b >> $whichbit;
        $this->bitpos++;

        if ($this->bitpos == 8) {
            $this->blockpos++;

            if ($this->blockpos == $this->blockmax) { //end of block reached
                $this->prefetchblock();
            } else if ($this->blockpos == $this->blocksize) {//end of short block reached (shorter than blockmax)
                return; //eof
            }

            $this->bitpos = 0;
        }
        return $b;
    }

    protected function getnextbits($n = 1) {
        $b = "";
        for ($i = 0; $i < $n; $i++)
            $b.=$this->getnextbit();
        return $b;
    }

    protected function getnextbyte() {
        if ($this->blockpos >= $this->blocksize)
            return;

        $this->bitpos = 0;
        $b = $this->block[$this->blockpos + 1];
        $this->blockpos++;
        return $b;
    }

    //-----------------------------------------------------------------------------
    public static function is_layer1(&$mp3) {
        return ($mp3['Layer Description'] == '11');
    }

    public static function is_layer2(&$mp3) {
        return ($mp3['Layer Description'] == '10');
    }

    public static function is_layer3(&$mp3) {
        return ($mp3['Layer Description'] == '01');
    }

    public static function is_mpeg10(&$mp3) {
        return ($mp3['MPEG version'] == '11');
    }

    public static function is_mpeg20(&$mp3) {
        return ($mp3['MPEG version'] == '10');
    }

    public static function is_mpeg25(&$mp3) {
        return ($mp3['MPEG version'] == '00');
    }

    public static function is_mpeg20or25(&$mp3) {
        return ($mp3['MPEG version'][1] == '0');
    }

    //-----------------------------------------------------------------------------
    public static function bitratelookup(&$mp3) {
        //bits               V1,L1  V1,L2  V1,L3  V2,L1  V2,L2&L3
        $array = array();
        $array['0000'] = array('free', 'free', 'free', 'free', 'free');
        $array['0001'] = array('32', '32', '32', '32', '8');
        $array['0010'] = array('64', '48', '40', '48', '16');
        $array['0011'] = array('96', '56', '48', '56', '24');
        $array['0100'] = array('128', '64', '56', '64', '32');
        $array['0101'] = array('160', '80', '64', '80', '40');
        $array['0110'] = array('192', '96', '80', '96', '48');
        $array['0111'] = array('224', '112', '96', '112', '56');
        $array['1000'] = array('256', '128', '112', '128', '64');
        $array['1001'] = array('288', '160', '128', '144', '80');
        $array['1010'] = array('320', '192', '160', '160', '96');
        $array['1011'] = array('352', '224', '192', '176', '112');
        $array['1100'] = array('384', '256', '224', '192', '128');
        $array['1101'] = array('416', '320', '256', '224', '144');
        $array['1110'] = array('448', '384', '320', '256', '160');
        $array['1111'] = array('bad', 'bad', 'bad', 'bad', 'bad');

        $whichcolumn = -1;
        if (self::is_mpeg10($mp3) && self::is_layer1($mp3))//V1,L1
            $whichcolumn = 0;
        else if (self::is_mpeg10($mp3) && self::is_layer2($mp3))//V1,L2
            $whichcolumn = 1;
        else if (self::is_mpeg10($mp3) && self::is_layer3($mp3))//V1,L3
            $whichcolumn = 2;
        else if (self::is_mpeg20or25($mp3) && self::is_layer1($mp3))//V2,L1
            $whichcolumn = 3;
        else if (self::is_mpeg20or25($mp3) && (self::is_layer2($mp3) || self::is_layer3($mp3)))
            $whichcolumn = 4; //V2,   L2||L3

        if (isset($array[$mp3['Bitrate Index']][$whichcolumn]))
            return $array[$mp3['Bitrate Index']][$whichcolumn];
        else
            return "bad";
    }

    //-----------------------------------------------------------------------------
    public static function samplelookup(&$mp3) {
        //bits               MPEG1   MPEG2   MPEG2.5
        $array = array();
        $array['00'] = array('44100', '22050', '11025');
        $array['01'] = array('48000', '24000', '12000');
        $array['10'] = array('32000', '16000', '8000');
        $array['11'] = array('res', 'res', 'res');

        $whichcolumn = -1;
        if (self::is_mpeg10($mp3))
            $whichcolumn = 0;
        else if (self::is_mpeg20($mp3))
            $whichcolumn = 1;
        else if (self::is_mpeg25($mp3))
            $whichcolumn = 2;

        if (isset($array[$mp3['Sampling Freq Idx']][$whichcolumn]))
            return $array[$mp3['Sampling Freq Idx']][$whichcolumn];
        else
            return 'unknown';
    }

    //-----------------------------------------------------------------------------
    public static function getframesize(&$mp3) {
        if ($mp3['Sampling Rate'] > 0) {
            return ceil((144 * $mp3['Bitrate'] * 1000) / $mp3['Sampling Rate']) + $mp3['Padding Bit'];
        }
        return 'unknown';
    }

    //-----------------------------------------------------------------------------
    public static function getduration(&$mp3, $startat) {
        if ($mp3['Bitrate'] > 0) {
            $KBps = ($mp3['Bitrate'] * 1000) / 8;
            $datasize = ($mp3['Filesize'] - ($startat / 8));
            $length = $datasize / $KBps;
            return sprintf("%d", $length);
        }
        return "unknown";
    }

    //-----------------------------------------------------------------------------
    public static function seconds_to_mmss($duration) {
        return sprintf("%d:%02d", ($duration / 60), $duration % 60);
    }

}
