<?php

/* (C) Websplosion LTD., 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


class CFlipCard
{

    public static function parseFlipCard(&$html, $row)
    {
        CFlipCard::parseFlipBox($html, $row);
    }

    public static function parseFlipBox(&$html, $row)
    {

        $html->assign("members_num_1", $row['user_id']);

        $orientation_row = DB::row("SELECT * FROM const_orientation WHERE id = " . $row['orientation'] . ";");
        $v = " -" . $orientation_row['title'];
        $html->assign("members_orientation", $v);

        $user_photo = User::getPhotoDefault($row['user_id'], "r", false, $row['gender']);
        $html->setvar('members_photo', $user_photo);

        $flip_fields = CFlipCard::flipFields($row['user_id']);

        $filp_indexes_about = ["income", "status", "smoking", "drinking", "education", "height", "body", "hair", "eye"];
        $filp_indexes_other = ["ethnicity", "first_date", "live_where", "living_with", "appearance", "age_preference", "humor", "can_you_host"];

        foreach ($filp_indexes_about as $key => $value) {
            $html->setvar('field', l($value));
            $value1 = "-";
            if ($flip_fields[$value . '_title'] != "") {
                $value1 = $flip_fields[$value . '_title'];
            }
            $html->setvar('value', $value1);

            $sql = "SELECT * FROM userinfo WHERE user_id = " . $row['user_id'] . ";";
            $row1 = DB::row($sql);

            $icon_limit = array(
                'drinking' => array('limit' => [2, 3, 4], 'path' => "./_files/icons/drinking.png"),
                'smoking' => array('limit' => [1, 2, 4], 'path' => "./_files/icons/smoking.png"),
            );

            if (isset($icon_limit[$value]) && in_array($row1[$value], $icon_limit[$value]['limit'])) {
                $img_path = $icon_limit[$value]['path'];

                $personal_icon_style = "background-image: url('" . $img_path . "')";
                $html->setvar('personal_icon_style', $personal_icon_style);

                $html->parse('icon_item', true);
            }

            $html->parse('fields_about_row', true);
            $html->clean('icon_item');
        }

        CFlipCard::parseIcons($html, $row['user_id']);

        $flips = array(
            array(
                "color" => "red",
                "field" => "Cpl: ",
            ),
            array(
                "color" => "green",
                "field" => "Mal: ",
            ),
            array(
                "color" => "blue",
                "field" => "Fem: ",
            ),
        );

        CFlipCard::parseFlipboxScales($html, "relation", $row);

        $html->setvar("name_short", User::nameShort($row['name']));
        $html->parse("fields_about", false);
        $html->clean('fields_about_row');

        $html->clean('what_looking_sliders');

        $html->clean('flip_looking_for_item');

        foreach ($filp_indexes_other as $key => $value) {
            $html->setvar('field', l($value));
            $value1 = "-";
            if ($flip_fields[$value . '_title'] != "") {
                $value1 = $flip_fields[$value . '_title'];
            }
            $html->setvar('value', $value1);
            $html->parse('fields_other_row', true);
            $html->clean('hotdate');
            $html->clean('event');
            $html->clean('partyhou');
        }

        CFlipCard::parseBanners($html, $row);

        $html->parse("fields_other", false);
        $html->clean('fields_other_row');

        $html->parse("users_new_item2", true);
        $html->clean('fields_about');
        $html->clean('fields_other');
    }

    // display icons about whether smoking and drinking level...
    public static function parseIcons(&$html, $user_id)
    {

        $sql = "SELECT * FROM userinfo WHERE user_id = " . $user_id . ";";
        $row1 = DB::row($sql);

        $icon_limit = array(
            'drinking' => array('limit' => [2, 3, 4], 'path' => "./_files/icons/drinking.png"),
            'smoking' => array('limit' => [1, 2, 4], 'path' => "./_files/icons/smoking.png"),
        );

        foreach ($icon_limit as $key => $value) {
            if (in_array($row1[$key], $value['limit'])) {
                $img_path = $value['path'];

                $personal_icon_style = "background-image: url('" . $img_path . "')";
                $html->setvar('personal_icon_style', $personal_icon_style);

                $html->parse('icon_item', true);
            }

        }

        $html->parse('icon_items', false);
        $html->clean('icon_item');

    }

    public static function parseFlipboxScales(&$html, $name, $flip_user)
    {

        $mask = ""; //value from scale sliders fields of user table.
        $mask = $flip_user[$name];
        $maskArray = [];

        $parts = explode(", ", $mask);

        foreach ($parts as $part) {
            if (strpos($part, ":") !== false) {
                list($key, $value) = explode(":", $part);
                $maskArray[(int) $key] = (int) $value;
            }
        }

        // get from const_relation, const_orientation table
        $csql = "SELECT * FROM const_" . $name . ";";

        if ($name == "p_orientation") {
            $csql = "SELECT * FROM const_orientation;";
        }

        $c_rows = DB::rows($csql);

        // get levels from looking_level table
        $l_sql = "SELECT id, title FROM looking_level;";
        $l_levels = DB::rows($l_sql);

        $scale_back_colors = ['#e91720', '#f79122', '#f8eb10', '#92d14f', '#3ab34a'];

        foreach ($c_rows as $key => $c_row) {

            $scale_field_name = $c_row['title'];
            $html->setvar('scale_field_name', $scale_field_name);

            $scale_value = 3;
            foreach ($l_levels as $level_id => $level_title) {
                if (isset($maskArray[$c_row['id']]) && $level_title['id'] == $maskArray[$c_row['id']]) {
                    $scale_value = (int) $level_title['id'];
                    break;
                }
            }

            $item_index = 0;
            foreach ($l_levels as $level_id => $level_title) {
                // $back_item_style = "background-color: " . $scale_back_colors[$item_index];

                $back_item_style = "background-color: rgb(28, 27, 27)";

                $html->setvar('back_item_style', $back_item_style);
                $html->parse('scale_back_item', true);
                $item_index++;
            }

            $scale_length = $scale_value * 21 - 3;
            $scale_var_style = "background-color: " . $scale_back_colors[$scale_value - 1] . "; width: " . $scale_length . "px;";

            if ($scale_value == 5) {
                $scale_length = $scale_value * 21 - 5;
                $scale_var_style = "background-color: " . $scale_back_colors[$scale_value - 1] . "; width: " . $scale_length . "px;";

                $scale_var_style = $scale_var_style . "border-top-right-radius: 7px; border-bottom-right-radius: 7px;";
            }

            $html->setvar('scale_bar_back_style', "background-color: " . $scale_back_colors[$scale_value - 1] . "; color: black;");

            $html->setvar('scale_bar_style', $scale_var_style);

            $scale_level_name = l("set_" . $l_levels[$scale_value - 1]['title']);
            $html->setvar('scale_level_name', $scale_level_name);

            $html->parse('scale_slider', true);
            $html->clean('scale_back_item');

        }

        $html->parse('what_looking_sliders', true);

        $html->clean('scale_slider');

    }

    public static function parseBanners(&$html, $row)
    {

        $banner_count = 0;
        $banner_count_limit = 8;
        $sql = "SELECT e.* FROM events_event AS e, events_event_guest AS eg WHERE eg.event_id=e.event_id AND e.event_private=0 AND eg.user_id=" . $row['user_id'] . " AND DATE_ADD(e.event_datetime, INTERVAL 3 HOUR) > NOW() ORDER BY e.event_n_comments DESC, e.event_datetime ASC LIMIT 0,10";
        DB::query($sql);
        $events = array();

        while ($events_row = DB::fetch_row()) {
            $events[] = $events_row;
        }
        if (count($events) && isset($row['set_events_banner_activity']) && $row['set_events_banner_activity'] == 1) {

            foreach ($events as $event) {
                $html->clean('event_where_when_rows');
                $html->clean('event_when_guests_comments_rows');

                $html->setvar('event_id', $event['event_id']);
                $html->setvar('event_title', strcut(to_html($event['event_title']), 20));
                $html->setvar('event_title_full', to_html($event['event_title']));

                $html->setvar('event_n_comments', $event['event_n_comments']);
                $html->setvar('event_n_guests', $event['event_n_guests']);
                $html->setvar('event_place', strcut(to_html($event['event_place']), 16));
                $html->setvar('event_place_full', to_html($event['event_place']));

                $html->setvar('event_date', to_html(Common::dateFormat($event['event_datetime'], 'events_event_date')));
                $html->setvar('event_datetime_raw', to_html($event['event_datetime']));
                $html->setvar('event_time', to_html(Common::dateFormat($event['event_datetime'], 'events_event_time')));

                $images = CFlipCard::event_images($event['event_id']);
                $html->setvar("image_thumbnail", $images["image_thumbnail"]);
                $html->parse("event");
                $banner_count++;
                if($banner_count == $banner_count_limit) return;
            }
        }

        $sql = "SELECT e.* FROM hotdates_hotdate AS e, hotdates_hotdate_guest AS eg WHERE eg.hotdate_id=e.hotdate_id AND eg.user_id=" . $row['user_id'] . " AND DATE_ADD(e.hotdate_datetime, INTERVAL 3 HOUR) > NOW() ORDER BY e.hotdate_n_comments DESC, e.hotdate_datetime ASC LIMIT 0,10";
        DB::query($sql);
        $hotdates = array();

        while ($hotdates_row = DB::fetch_row()) {
            $hotdates[] = $hotdates_row;
        }
        if (count($hotdates) && isset($row['set_nsc_banner_activity']) && $row['set_nsc_banner_activity'] == 1) {

            foreach ($hotdates as $hotdate) {
                $html->clean('hotdate_where_when_rows');
                $html->clean('hotdate_when_guests_comments_rows');

                $html->setvar('hotdate_id', $hotdate['hotdate_id']);
                $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), 20));
                $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));

                $html->setvar('hotdate_n_comments', $hotdate['hotdate_n_comments']);
                $html->setvar('hotdate_n_guests', $hotdate['hotdate_n_guests']);
                $html->setvar('hotdate_place', strcut(to_html($hotdate['hotdate_place']), 16));
                $html->setvar('hotdate_place_full', to_html($hotdate['hotdate_place']));

                $html->setvar('hotdate_date', to_html(Common::dateFormat($hotdate['hotdate_datetime'], 'hotdates_hotdate_date')));
                $html->setvar('hotdate_datetime_raw', to_html($hotdate['hotdate_datetime']));
                $html->setvar('hotdate_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'], 'hotdates_hotdate_time')));

                $images = CFlipCard::hotdate_images($hotdate['hotdate_id']);
                $html->setvar("image_thumbnail", $images["image_thumbnail"]);
                $html->parse("hotdate", true);
                $banner_count++;
                if($banner_count == $banner_count_limit) return;

            }
        }

        $sql = "SELECT e.* FROM partyhouz_partyhou AS e, partyhouz_partyhou_guest AS eg WHERE eg.partyhou_id=e.partyhou_id AND eg.user_id=" . $row['user_id'] . " AND DATE_ADD(e.partyhou_datetime, INTERVAL 3 HOUR) > NOW() ORDER BY e.partyhou_n_comments DESC, e.partyhou_datetime ASC LIMIT 0,10";
        DB::query($sql);
        $partyhous = array();

        while ($partyhous_row = DB::fetch_row()) {
            $partyhous[] = $partyhous_row;
        }
        if (count($partyhous) && isset($row['set_nsc_banner_activity']) && $row['set_nsc_banner_activity'] == 1) {

            foreach ($partyhous as $partyhou) {
                $html->clean('partyhou_where_when_rows');
                $html->clean('partyhou_when_guests_comments_rows');

                $html->setvar('partyhou_id', $partyhou['partyhou_id']);
                $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), 20));
                $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));

                $html->setvar('partyhou_n_comments', $partyhou['partyhou_n_comments']);
                $html->setvar('partyhou_n_guests', $partyhou['partyhou_n_guests']);
                // $html->setvar('partyhou_place', strcut(to_html($partyhou['partyhou_place']), 16));
                // $html->setvar('partyhou_place_full', to_html($partyhou['partyhou_place']));

                // $html->setvar('partyhou_date', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhous_partyhou_date')));
                // $html->setvar('partyhou_datetime_raw', to_html($partyhou['partyhou_datetime']));
                // $html->setvar('partyhou_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhous_partyhou_time')));

                $images = CFlipCard::partyhou_images($partyhou['partyhou_id']);
                $html->setvar("image_thumbnail", $images["image_thumbnail"]);
                $html->parse("partyhou", true);
                $banner_count++;
                if($banner_count == $banner_count_limit) return;

            }
        }
    }

    // get images for events, hotdates, partyhouz
    //nnsscc_diamond-20200320-start
    public static function event_images($event_id, $random = true)
    {
        global $g;

        if ($n_images = DB::result("SELECT COUNT(image_id) FROM events_event_image WHERE event_id=" . to_sql($event_id, 'Number') . " LIMIT 1")) {
            $image_n = $random ? rand(0, $n_images - 1) : 0;
            $image = DB::row("SELECT * FROM events_event_image WHERE event_id=" . to_sql($event_id, 'Number') . " ORDER BY image_id DESC LIMIT " . $image_n . ", 1");

            return array(
                "image_thumbnail" => $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_th.jpg",
                "image_thumbnail_s" => $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_th_s.jpg",
                "image_thumbnail_b" => $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_th_b.jpg",
                "image_file" => $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_b.jpg",
                "photo_id" => $image['image_id'],
                "system" => 0);
        } else {

            $type = DB::result("SELECT event_private FROM events_event WHERE event_id=" . to_sql($event_id, "Number"));

            // entry
            if ($type == 1) {
                $images = array(
                    "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/events/carusel_foto_clock.gif",
                    "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/events/carusel_foto_clock.gif",
                    "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/events/foto_clock_l.gif",
                    "image_file" => $g['tmpl']['url_tmpl_main'] . "images/events/foto_clock_l.gif",
                    "sysytem" => 1,
                    "photo_id" => 0,
                );
            } else {
                $images = array(
                    "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/events/foto_02.jpg",
                    "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/events/carusel_foto01.gif",
                    "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/events/foto_02_l.jpg",
                    "image_file" => $g['tmpl']['url_tmpl_main'] . "images/events/foto_02_l.jpg",
                    "sysytem" => 1,
                    "photo_id" => 0,
                );
            }

            return $images;
        }
    }
    public static function hotdate_images($hotdate_id, $random = true)
    {
        global $g;

        if ($n_images = DB::result("SELECT COUNT(image_id) FROM hotdates_hotdate_image WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1")) {
            $image_n = $random ? rand(0, $n_images - 1) : 0;
            $image = DB::row("SELECT * FROM hotdates_hotdate_image WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " ORDER BY image_id DESC LIMIT " . $image_n . ", 1");

            return array(
                "image_thumbnail" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th.jpg",
                "image_thumbnail_s" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th_s.jpg",
                "image_thumbnail_b" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_th_b.jpg",
                "image_file" => $g['path']['url_files'] . "hotdates_hotdate_images/" . $image['image_id'] . "_b.jpg",
                "photo_id" => $image['image_id'],
                "system" => 0);
        } else {

            $type = DB::result("SELECT hotdate_private FROM hotdates_hotdate WHERE hotdate_id=" . to_sql($hotdate_id, "Number"));

            // entry
            if ($type == 1) {
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
    //nnsscc_diamond-20200320-end

    public static function partyhou_images($partyhou_id, $random = true)
    {
        global $g;

        if ($n_images = DB::result("SELECT COUNT(image_id) FROM partyhouz_partyhou_image WHERE partyhou_id=" . to_sql($partyhou_id, 'Number') . " LIMIT 1")) {
            $image_n = $random ? rand(0, $n_images - 1) : 0;
            $image = DB::row("SELECT * FROM partyhouz_partyhou_image WHERE partyhou_id=" . to_sql($partyhou_id, 'Number') . " ORDER BY image_id DESC LIMIT " . $image_n . ", 1");

            return array(
                "image_thumbnail" => $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th.jpg",
                "image_thumbnail_s" => $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th_s.jpg",
                "image_thumbnail_b" => $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_th_b.jpg",
                "image_file" => $g['path']['url_files'] . "partyhouz_partyhou_images/" . $image['image_id'] . "_b.jpg",
                "photo_id" => $image['image_id'],
                "system" => 0);
        } else {

            $type = DB::result("SELECT partyhou_private FROM partyhouz_partyhou WHERE partyhou_id=" . to_sql($partyhou_id, "Number"));

            // entry
            if ($type == 1) {
                $images = array(
                    "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/carusel_foto_clock.gif",
                    "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/carusel_foto_clock.gif",
                    "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_clock_l.gif",
                    "image_file" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_clock_l.gif",
                    "sysytem" => 1,
                    "photo_id" => 0,
                );
            } else {
                $images = array(
                    "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_02.jpg",
                    "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/carusel_foto01.gif",
                    "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_02_l.jpg",
                    "image_file" => $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_02_l.jpg",
                    "sysytem" => 1,
                    "photo_id" => 0,
                );
            }

            return $images;
        }
    }

    public static function flipFields($user_id)
    {

        $filp_sql = "SELECT ui.user_id";
        $keyword = "";
        $where = "";
        $select_add = "";
        $from_add = " FROM userinfo AS ui ";

        $select_add .= " , vi.title AS income_title";
        $from_add .= " LEFT JOIN var_income  vi ON ui.income   = vi.id ";
        $where .= " or vi.title like '%" . $keyword . "%'";

        $select_add .= " , vs.title AS status_title";
        $from_add .= " LEFT JOIN var_status      vs ON ui.status   = vs.id ";
        $where .= " or vs.title LIKE '%" . $keyword . "%'";

        $select_add .= " , v_smoking.title AS smoking_title";
        $from_add .= " LEFT JOIN var_smoking  v_smoking ON ui.smoking   = v_smoking.id ";
        $where .= " or v_smoking.title like '%" . $keyword . "%'";

        $select_add .= " , v_drinking.title AS drinking_title";
        $from_add .= " LEFT JOIN var_drinking  v_drinking ON ui.drinking   = v_drinking.id ";
        $where .= " or v_drinking.title like '%" . $keyword . "%'";

        $select_add .= " , v_education.title AS education_title";
        $from_add .= " LEFT JOIN var_education  v_education ON ui.education   = v_education.id ";
        $where .= " or v_education.title like '%" . $keyword . "%'";

        $select_add .= " , v_height.title AS height_title, v_height.value_cm AS height_value_cm, v_height.value_f AS height_value_f";
        $from_add .= " LEFT JOIN var_height  v_height ON ui.height   = v_height.id ";
        $where .= " or v_height.title like '%" . $keyword . "%'";

        $select_add .= " , v_body.title AS body_title";
        $from_add .= " LEFT JOIN var_body  v_body ON ui.body   = v_body.id ";
        $where .= " or v_body.title like '%" . $keyword . "%'";

        $select_add .= " , v_hair.title AS hair_title";
        $from_add .= " LEFT JOIN var_hair  v_hair ON ui.hair   = v_hair.id ";
        $where .= " or v_hair.title like '%" . $keyword . "%'";

        $select_add .= " , v_eye.title AS eye_title";
        $from_add .= " LEFT JOIN var_eye  v_eye ON ui.eye   = v_eye.id ";
        $where .= " or v_eye.title like '%" . $keyword . "%'";

        $select_add .= " , v_ethnicity.title AS ethnicity_title";
        $from_add .= " LEFT JOIN var_ethnicity  v_ethnicity ON ui.ethnicity   = v_ethnicity.id ";
        $where .= " or v_ethnicity.title like '%" . $keyword . "%'";

        $select_add .= " , v_first_date.title AS first_date_title";
        $from_add .= " LEFT JOIN var_first_date  v_first_date ON ui.first_date   = v_first_date.id ";
        $where .= " or v_first_date.title like '%" . $keyword . "%'";

        $select_add .= " , v_live_where.title AS live_where_title";
        $from_add .= " LEFT JOIN var_live_where  v_live_where ON ui.live_where = v_live_where.id ";
        $where .= " or v_live_where.title like '%" . $keyword . "%'";

        $select_add .= " , v_living_with.title AS living_with_title";
        $from_add .= " LEFT JOIN var_living_with  v_living_with ON ui.living_with = v_living_with.id ";
        $where .= " or v_living_with.title like '%" . $keyword . "%'";

        $select_add .= " , v_appearance.title AS appearance_title";
        $from_add .= " LEFT JOIN var_appearance  v_appearance ON ui.appearance   = v_appearance.id ";
        $where .= " or v_appearance.title like '%" . $keyword . "%'";

        $select_add .= " , v_age_preference.title AS age_preference_title";
        $from_add .= " LEFT JOIN var_age_preference  v_age_preference ON ui.age_preference   = v_age_preference.id ";
        $where .= " or v_age_preference.title like '%" . $keyword . "%'";

        $select_add .= " , v_humor.title AS humor_title";
        $from_add .= " LEFT JOIN var_humor  v_humor ON ui.humor   = v_humor.id ";
        $where .= " or v_humor.title like '%" . $keyword . "%'";

        $select_add .= " , v_can_you_host.title AS can_you_host_title";
        $from_add .= " LEFT JOIN var_can_you_host  v_can_you_host ON ui.can_you_host   = v_can_you_host.id ";
        $where .= " or v_can_you_host.title like '%" . $keyword . "%'";

        //$from_add .= " LEFT JOIN var_hobbies  v_hobbies ON ui.hobbies   = v_hobbies.id ";
        //$where .= " or v_hobbies.title like '%" . $keyword ."%'";

        $filp_sql .= $select_add . $from_add . "WHERE ui.user_id =" . $user_id;
        $row1 = DB::row($filp_sql);

        return $row1;
    }

}
