<?php
class CBanner extends CHtmlBlock
{
    public $type = 'home';

    public function setType($type)
    {
        $this->type = $type;
    }

    public static function getBannerHtml($banner)
    {
        global $g;
        $blockHtml = '';
        if (Common::isApp() && Common::isOptionActive('use_only_admob_in_apps')) {
            if (strpos($banner['place'], 'admob_') === 0) {
                $blockHtml = $banner['code'];
            }
        } else {
            if ($banner['type'] == 'flash') {
                $blockHtml = User::flashBanner($banner['filename'], $banner['width'], $banner['height']);
            } elseif ($banner['type'] == 'code') {
                $blockHtml = $banner['code'];
            } else {
                $bannerPatch = $g['path']['url_files'] . 'banner/' . str_replace(' ', '_', $banner['filename']);
                $blockHtml = "<a target=\"_blank\" href=\"" . $banner['url'] . "\"><img src=\"" . $bannerPatch . "\" alt=\"" . $banner['alt'] . "\" /></a>";
            }
        }
        return $blockHtml;
    }

    public static function getHtml($place)
    {
        global $g;

        $tmpl = to_sql(Common::getOption('tmpl_loaded', 'tmpl'), 'Plain');

        $lang = Common::getOption('lang_loaded', 'main');

        $sql = 'SELECT `type` FROM `banners_places`
                 WHERE `place` = ' . to_sql($place) . '
                   AND `active` = 1';
        $type = DB::result($sql, 0, DB_MAX_INDEX, true);

        $where = ' `place` = ' . to_sql($place) . '
               AND `active` = 1
               AND (templates LIKE "%' . $tmpl . '%" OR templates = "")
               AND (langs LIKE "%' . $lang . '%" OR langs = "")';

        $sql = 'SELECT COUNT(*) FROM `banners` WHERE ' . $where;
        $count = DB::result($sql, 0, DB_MAX_INDEX, true);

        if ($count == 0) {
            return false;
        }

        $numberBannersPlace = intval(Common::getOption('number_banners_place_' . $place, 'template_options'));
        if ($type == 'static') {
            $sql = "SELECT *
                      FROM `banners`
                     WHERE $where LIMIT 1";
        } elseif ($type == 'random') {

            //$where .= ' AND `id` >= ' . rand(0, $maxId);
            $limitStart = rand(0, $count - 1);

            if ($numberBannersPlace) {
                $sql = "SELECT *
                          FROM `banners`
                         WHERE {$where}
                         LIMIT $limitStart, " . to_sql($numberBannersPlace, 'Number');
            } else {
                $sql = "SELECT *
                          FROM `banners`
                         WHERE $where LIMIT $limitStart, 1";
            }
        } else {
            return false;
        }

        DB::query($sql);
        if (DB::num_rows() == 0) {
            return false;
        }

        $result = array();
        if ($numberBannersPlace) {
            while ($row = DB::fetch_row()) {
                $result[] = self::getBannerHtml($row);
            }
        } else {
            $banner = DB::fetch_row();
            $result = array(self::getBannerHtml($banner));
        }

        return $result;
    }

    public static function getAllowBannerSql($prefix = '', $flag = 0)
    {
        $allowBanner = Common::getOption('banners_places', 'template_options');
        $whereNotAllow = '';
        $where = '';
        if (!empty($prefix)) {
            $prefix .= '.';
        }
        if (countFrameworks('mobile') == 0 || Common::isOptionActive('no_mobile_template', 'template_options')) {
            $where = $prefix . "place NOT LIKE '%mobile%' ";
        } else {
            if (!Common::isOptionActive('banner_header_mobile', 'template_options_mobile')) {
                $where = " " . $prefix . "place != 'header_mobile'";
            }
        }

        if (countFrameworks('main') == 0) {
            $where = $prefix . "place LIKE '%mobile%' ";
        } else {
            if (is_array($allowBanner) && !empty($allowBanner)) {
                $placeNot = array();
                foreach ($allowBanner as $pl => $is) {
                    if (!$is) {
                        $placeNot[] = to_sql($pl);
                        $placeNot[] = to_sql("{$pl}_paid");
                    }
                }
                if (!empty($placeNot)) {
                    $whereNotAllow = $prefix . 'place NOT IN (' . implode(',', $placeNot) . ')';
                }
            }
        }

        if ($whereNotAllow != '') {
            $where .= ($where != '') ? " AND {$whereNotAllow}" : $whereNotAllow;
        }

        if (Common::isOptionActiveTemplate('not_show_banner_paid')) {
            $where .= $where != '' ? ' AND ' : '';
            $where .= $prefix . "place NOT LIKE '%_paid'";
        }
        if ($where != '') {
            if ($flag == 0) {
                $where = ' WHERE ' . $where;
            } elseif ($flag == 1) {
                $where = ' AND ' . $where;
            }
        }
        return $where;

    }

    public static function getBlock(&$html, $type, $prf = '')
    {
        global $p;
        global $g;
        global $g_user;

        $optionNameTmpl = Common::getOption('name', 'template_options');
        if ($optionNameTmpl == 'impact' && $p == 'city.php') {
            return false;
        }

        $pos = $type;
        $block = 'banner_' . trim($type) . $prf;

        $isParseBanner = false;

        if ($html->blockexists($block)) {

            $class = str_replace('.php', '', $p);
            $optionSetTmpl = Common::getOption('set', 'template_options');
            // Mobile Urbana
            if ($optionSetTmpl == 'urban') {
                $display = get_param('display');
                if ($p == 'profile_settings.php') {
                    if ($display) {
                        $class .= "_{$display}";
                    }
                } elseif ($p == 'upgrade.php' && $g_user) {
                    $action = get_param('action');
                    if (!$action && User::isSuperPowers()) {
                        $class .= "_activated_super_power";
                    }
                } elseif ($p == 'search_results.php' && $optionNameTmpl == 'impact'
                    && ($display == 'profile' || $display == 'encounters')) {
                    $class = 'profile_view';
                }
            }
            // Mobile Urbana
            if (!Common::isOptionActiveTemplate('not_show_banner_paid') && User::isPaid(guid())) {
                // $type = $type . '_paid';
            }

            $banners = self::getHtml($type);
            if ($banners !== false) {
                // var_dump($type);
                foreach ($banners as $baner) {
                    $html->setvar($block, $baner);
                    //nnsscc-diamond-20200420-start
                    if ($type == "right_column") {

                        $distance = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='distance' AND module = 'wowslider'" .
                            " LIMIT 1");
                        $from_add = "";
                        if (isset($distance['value'])) {
                            $whereLocation = inradius($g_user['city_id'], $distance['value']);
                            $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = e.city_id";
                        }

                        $is_approved = "";
                        if (!Common::isOptionActive('whisp_show_before_approval')) {
                            $is_approved = " AND (approved = 1 OR (approved = 0 AND user_id = " . to_sql(guid(), 'Number') . "))";
                        }

                        DB::query("SELECT * FROM (SELECT * FROM wowslider t1 WHERE NOT EXISTS (SELECT * FROM wowslider higher WHERE higher.user_id = t1.user_id  AND (
					        CASE
					          WHEN EXISTS (SELECT * FROM wowslider h WHERE h.is_check = 1 AND h.user_id = t1.user_id)
					          THEN t1.is_check != 1
					          ELSE t1.is_check != 1
					        END
					      ) AND NOW() > higher.from_datetime AND NOW() < higher.end_datetime) OR distance=1 ORDER BY event_id) e " . $from_add . " WHERE NOW() > e.from_datetime AND NOW() < e.end_datetime AND (1=1 " . $whereLocation . " OR e.distance=1) " . $is_approved . " ORDER BY event_id DESC");

                        $no = 0;
                        $banner_val = '<div class="events_new_decor_l">';
                        $banner_val = $banner_val . '<div class="pl_top ">';
                        $banner_val = $banner_val . '<link rel="stylesheet" type="text/css" href="./wow/engine1/style_right.css" />';
                        $banner_val = $banner_val . '<div class="wow_container_right">';
                        $banner_val = $banner_val . '<div id="wowslider-container-right">';
                        $banner_val = $banner_val . '<div class="ws_images">';
                        $banner_val = $banner_val . '<ul>';
                        while ($image = DB::fetch_row()) {

                            $banner_val = $banner_val . '<li><a href="' . $image["link"] . '" target="_blank">';
                            $banner_val = $banner_val . '<div class="more_detail_content hidden" style="font-size: 10px; position: absolute; top: 10px;width:100%; text-align:center;color:#00FF00;">';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">country : ' . $image['country'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">state : ' . $image['state'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">city : ' . $image['city'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">start : ' . $image['from_datetime'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">end : ' . $image['end_datetime'] . '</p>';
                            $banner_val = $banner_val . '</div>';
                            $banner_val = $banner_val . '<img src="' . $g['path']['url_files'] . 'wowslider/' . $image['img_path'] . '" alt="1" title="' . $image['title'] . '" id="wows1_0"/></a></li>';

                            if ($no == 0) {

                            } else {

                            }
                            $no++;
                        }
                        if ($no == 0) {

                            $is_approved = "";
                            if (!Common::isOptionActive('show_before_approval')) {
                                $is_approved = " AND (e.approved = 1 OR (e.approved = 0 AND e.user_id = " . to_sql(guid(), 'Number') . "))";
                            }

                            DB::query("SELECT * FROM wowslider as e " . $from_add . " WHERE distance=1 " . $whereLocation . $is_approved . " GROUP BY e.user_id  ORDER BY event_id DESC");
                            while ($image = DB::fetch_row()) {
                                $banner_val = $banner_val . '<li><a href="' . $image["link"] . '" target="_blank">';
                                $banner_val = $banner_val . '<div class="more_detail_content hidden" style="font-size: 10px; position: absolute; top: 10px;width:100%; text-align:center;color:#00FF00;">';
                                $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">country : ' . $image['country'] . '</p>';
                                $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">state : ' . $image['state'] . '</p>';
                                $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">city : ' . $image['city'] . '</p>';
                                $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">start : ' . $image['from_datetime'] . '</p>';
                                $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">end : ' . $image['end_datetime'] . '</p>';
                                $banner_val = $banner_val . '</div>';
                                $banner_val = $banner_val . '<img src="' . $g['path']['url_files'] . 'wowslider/' . $image['img_path'] . '" alt="1" title="' . $image['title'] . '" id="wows1_0"/></a></li>';
                                $no++;
                            }
                        }

                        $banner_val = $banner_val . '</ul>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '<div class="ws_bullets"><div>';
                        $banner_val = $banner_val . '</div></div>';
                        $banner_val = $banner_val . '<div class="ws_script" style="position:absolute;left:-99%"><a href="http://wowslider.net">html slider</a> by WOWSlider.com v9.0</div>';
                        $banner_val = $banner_val . '<div class="ws_shadow"></div>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '<div>';
                        $banner_val = $banner_val . '<script>';
                        $banner_val = $banner_val . 'var effect_option = "turn"; ';
                        $banner_val = $banner_val . 'var duration_time_v = 40*100; ';
                        $banner_val = $banner_val . 'var delay_time_v = 50*100; ';
                        $duration_time_v = 4000;
                        $delay_time_v = 5000;
                        $title_size_v = "2.5";
                        $title_bottom_v = 5;
                        $title_color_v = "#FFFFFF";
                        $title_back_color_v = "#000000";
                        $title_sliding_state_v = "inline-block";
                        $control_visible_v = "block";
                        $control_size_v = 5;
                        $effects = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='effects' AND module = 'wowslider'" .
                            " LIMIT 1");

                        if ($effects['value'] != "") {
                            $banner_val = $banner_val . 'effect_option="' . $effects['value'] . '";';
                            $banner_val = $banner_val . ' var effect_option_array = effect_option.split(",")';
                        }
                        $duration_time = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='duration_time' AND module = 'wowslider'" .
                            " LIMIT 1");

                        if ($duration_time['value'] != "") {
                            $duration_time_v = 1000 * $duration_time['value'];
                        }
                        $delay_time = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='delay_time' AND module = 'wowslider'" .
                            " LIMIT 1");

                        if ($delay_time['value'] != "") {
                            $delay_time_v = 1000 * $delay_time['value'];
                        }
                        $banner_val = $banner_val . '</script>';
                        $title_size = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_size' AND module = 'wowslider'" .
                            " LIMIT 1");

                        if ($title_size['value'] != "") {
                            $title_size_v = $title_size['value'];
                        }
                        $title_color = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_color' AND module = 'wowslider'" .
                            " LIMIT 1");

                        if ($title_color['value'] != "") {
                            $title_color_v = $title_color['value'];
                        }
                        $title_back_color = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_back_color' AND module = 'wowslider'" .
                            " LIMIT 1");

                        if ($title_back_color['value'] != "") {
                            $title_back_color_v = $title_back_color['value'];
                        }
                        $title_bottom = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_bottom' AND module = 'wowslider'" .
                            " LIMIT 1");
                        if ($title_bottom['value'] != "") {
                            $title_bottom_v = $title_bottom['value'];
                        }
                        $title_sliding_state = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_sliding_state' AND module = 'wowslider'" .
                            " LIMIT 1");
                        if ($title_sliding_state['value'] != "") {
                            if ($title_sliding_state['value'] == 1) {
                                $title_sliding_state_v = "inline-block";
                            } else {
                                $title_sliding_state_v = "none";
                            }
                        }
                        $control_size = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='control_size' AND module = 'wowslider'" .
                            " LIMIT 1");
                        if ($control_size['value'] != "") {
                            $control_size_v = $control_size['value'];
                        }
                        $control_visible = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='control_visible' AND module = 'wowslider'" .
                            " LIMIT 1");
                        if ($control_visible['value'] != "") {
                            if ($control_visible['value'] == 1) {
                                $control_visible_v = "block";
                            } else {
                                $control_visible_v = "none";
                            }
                        }
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '<style>';
                        $banner_val = $banner_val . '#wowslider-container-right .ws_images .ws_list img, #wowslider-container-right .ws_images > div > img {border-radius:5px;}';
                        $banner_val = $banner_val . '#wowslider-container-right .ws-title{display:' . $title_sliding_state_v . ' !important;}';
                        $banner_val = $banner_val . '#wowslider-container-right .ws-title{bottom:' . $title_bottom_v . '% !important;}';
                        $banner_val = $banner_val . '#wowslider-container-right .ws-title{font-size:' . $title_size_v . 'em !important;}';
                        $banner_val = $banner_val . '#wowslider-container-right .ws-title{color:' . $title_color_v . ' !important;}';
                        $banner_val = $banner_val . '#wowslider-container-right .ws-title span, #wowslider-container-right .ws-title div {background-color: ' . $title_back_color_v . ' !important;}';
                        $banner_val = $banner_val . '.more_detail_content.hidden{display:none;}';
                        $banner_val = $banner_val . '.ws_controls{display:' . $control_visible_v . ' !important;}';
                        $banner_val = $banner_val . '#wowslider-container-right a.ws_next, #wowslider-container-right a.ws_prev{width:' . $control_size_v . 'em; height:' . $control_size_v . 'em;}';
                        $banner_val = $banner_val . '</style>';
                        $banner_val = $banner_val . '<script type="text/javascript" src="./wow/engine1/wowslider.js"></script>';
                        $banner_val = $banner_val . '<script type="text/javascript" src="./wow/engine1/script.js" id="script_type"></script>';
                        $banner_val = $banner_val . '<script>';
                        $banner_val = $banner_val . 'var typeurl="./wow/engine1/script";';
                        $banner_val = $banner_val . '	var options = {
									effect:effect_option,
									prev:"",
									next:"",
									duration:' . $duration_time_v . ',
									delay:' . $delay_time_v . ',
									width:1280,
									height:720,
									autoPlay:true,
									autoPlayVideo:false,
									playPause:true,
									stopOnHover:false,
									loop:false,
									bullets:1,
									caption:true,
									captionEffect:"parallax",
									controls:true,
									controlsThumb:false,
									responsive:1,
									fullScreen:false,
									gestures:2,
									onBeforeStep:0,
									images:0
								};
							var wowSliderContent = jQuery(".wow_container_right").html();
							var slider = jQuery("#wowslider-container-right").wowSlider(options);
						</script>';
                        $banner_val = $banner_val . '<p id="more_detail_banner" style="font-size: 12px; height: 25px; color:#000000;text-align:center;cursor:pointer;">' . l('click_Banner_for_more_detail') . '</p>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '</div>';

                        if ($no > 0) {
                            $html->setvar("banner_right_column", $banner_val);
                        } else {
                            $html->setvar("banner_right_column", "");
                        }
                    } else {
                        $distance = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='distance' AND module = 'wowslider'" .
                            " LIMIT 1");

                        $from_add = "";
                        if (isset($distance['value'])) {
                            $whereLocation = inradius(isset($g_user['city_id']) ? $g_user['city_id'] : '', $distance['value']);
                            $from_add .= " LEFT JOIN geo_city AS gc ON gc.city_id = e.city_id";
                        }

                        $sql_1 = "SELECT * FROM wowslider_main e " . $from_add . " WHERE slider_type='" . $type . "' AND ((e.is_check=1 " . $whereLocation . ") OR distance=1) AND NOW() > from_datetime AND NOW() < end_datetime ORDER BY event_id DESC";
                        DB::query($sql_1);
                        $no = 0;
                        $banner_val = '<div class="events_new_decor_l">';
                        $banner_val = $banner_val . '<div class="pl_top ">';
                        $banner_val = $banner_val . '<link rel="stylesheet" type="text/css" href="./wow/engine1/style_' . $type . '.css" />';
                        $banner_val = $banner_val . '<div class="wow_container_' . $type . '">';
                        $banner_val = $banner_val . '<div id="wowslider-container-' . $type . '">';
                        $banner_val = $banner_val . '<div class="ws_images">';
                        $banner_val = $banner_val . '<ul>';
                        while ($image = DB::fetch_row()) {
                            $banner_val = $banner_val . '<li><a href="' . $image["link"] . '" target="_blank">';
                            $banner_val = $banner_val . '<div class="more_detail_content hidden" style="font-size: 10px; position: absolute; top: 10px;width:100%; text-align:center;color:#00FF00;">';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">country : ' . $image['country'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">state : ' . $image['state'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">city : ' . $image['city'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">start : ' . $image['from_datetime'] . '</p>';
                            $banner_val = $banner_val . '<p style="margin:0px;line-height:13px;">end : ' . $image['end_datetime'] . '</p>';
                            $banner_val = $banner_val . '</div>';
                            $banner_val = $banner_val . '<img src="' . $g['path']['url_files'] . 'wowslider/' . $image['img_path'] . '" alt="1" title="' . $image['title'] . '" id="wows1_0"/></a></li>';

                            if ($no == 0) {

                            } else {

                            }
                            $no++;
                        }

                        $banner_val = $banner_val . '</ul>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '<div class="ws_bullets"><div>';
                        $banner_val = $banner_val . '</div></div>';
                        $banner_val = $banner_val . '<div class="ws_script" style="position:absolute;left:-99%"><a href="http://wowslider.net">html slider</a> by WOWSlider.com v9.0</div>';
                        $banner_val = $banner_val . '<div class="ws_shadow"></div>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '<div>';
                        $banner_val = $banner_val . '<script>';
                        $banner_val = $banner_val . 'var effect_option = "turn"; ';
                        $banner_val = $banner_val . 'var duration_time_v = 40*100; ';
                        $banner_val = $banner_val . 'var delay_time_v = 50*100; ';
                        $duration_time_v = 4000;
                        $delay_time_v = 5000;
                        $title_size_v = "2.5";
                        $title_bottom_v = 5;
                        $title_color_v = "#FFFFFF";
                        $title_back_color_v = "#000000";
                        $title_sliding_state_v = "inline-block";
                        $control_visible_v = "block";
                        $control_size_v = 5;
                        $effects = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='effects' AND module = 'wowslider_main'" .
                            " LIMIT 1");

                        if ($effects['value'] != "") {
                            $banner_val = $banner_val . 'effect_option="' . $effects['value'] . '";';
                            $banner_val = $banner_val . ' var effect_option_array = effect_option.split(",")';
                        }
                        $duration_time = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='duration_time' AND module = 'wowslider_main'" .
                            " LIMIT 1");

                        if ($duration_time['value'] != "") {
                            $duration_time_v = 1000 * $duration_time['value'];
                        }
                        $delay_time = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='delay_time' AND module = 'wowslider_main'" .
                            " LIMIT 1");

                        if ($delay_time['value'] != "") {
                            $delay_time_v = 1000 * $delay_time['value'];
                        }
                        $banner_val = $banner_val . '</script>';
                        $title_size = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_size' AND module = 'wowslider_main'" .
                            " LIMIT 1");

                        if ($title_size['value'] != "") {
                            $title_size_v = $title_size['value'];
                        }
                        $title_color = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_color' AND module = 'wowslider_main'" .
                            " LIMIT 1");

                        if ($title_color['value'] != "") {
                            $title_color_v = $title_color['value'];
                        }
                        $title_back_color = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_back_color' AND module = 'wowslider_main'" .
                            " LIMIT 1");

                        if ($title_back_color['value'] != "") {
                            $title_back_color_v = $title_back_color['value'];
                        }
                        $title_bottom = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_bottom' AND module = 'wowslider_main'" .
                            " LIMIT 1");
                        if ($title_bottom['value'] != "") {
                            $title_bottom_v = $title_bottom['value'];
                        }
                        $title_sliding_state = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='title_sliding_state' AND module = 'wowslider_main'" .
                            " LIMIT 1");
                        if ($title_sliding_state['value'] != "") {
                            if ($title_sliding_state['value'] == 1) {
                                $title_sliding_state_v = "inline-block";
                            } else {
                                $title_sliding_state_v = "none";
                            }
                        }
                        $control_size = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='control_size' AND module = 'wowslider_main'" .
                            " LIMIT 1");
                        if ($control_size['value'] != "") {
                            $control_size_v = $control_size['value'];
                        }
                        $control_visible = DB::row("SELECT * " .
                            "FROM config " .
                            "WHERE config.option='control_visible' AND module = 'wowslider_main'" .
                            " LIMIT 1");
                        if ($control_visible['value'] != "") {
                            if ($control_visible['value'] == 1) {
                                $control_visible_v = "block";
                            } else {
                                $control_visible_v = "none";
                            }
                        }
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '<style>';
                        $banner_val = $banner_val . '.pl_top { margin-top: 0px;}';
                        $banner_val = $banner_val . '.events_new_decor_l { background: transparent;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' .ws_images .ws_list img, #wowslider-container-' . $type . ' .ws_images > div > img {border-radius:5px;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' .ws-title{display:' . $title_sliding_state_v . ' !important;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' .ws-title{bottom:' . $title_bottom_v . '% !important;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' .ws-title{font-size:' . $title_size_v . 'em !important;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' .ws-title{color:' . $title_color_v . ' !important;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' .ws-title span, #wowslider-container-' . $type . ' .ws-title div {background-color: ' . $title_back_color_v . ' !important;}';
                        $banner_val = $banner_val . '.more_detail_content.hidden{display:none;}';
                        $banner_val = $banner_val . '.ws_controls{display:' . $control_visible_v . ' !important;}';
                        $banner_val = $banner_val . '#wowslider-container-' . $type . ' a.ws_next, #wowslider-container-' . $type . ' a.ws_prev{width:' . $control_size_v . 'em; height:' . $control_size_v . 'em;}';
                        $banner_val = $banner_val . '</style>';
                        $banner_val = $banner_val . '<script type="text/javascript" src="./wow/engine1/wowslider.js"></script>';
                        $banner_val = $banner_val . '<script type="text/javascript" src="./wow/engine1/script.js" id="script_type"></script>';
                        $banner_val = $banner_val . '<script>';
                        $banner_val = $banner_val . 'var typeurl="./wow/engine1/script";';
                        $banner_val = $banner_val . '	var options = {
									effect:effect_option,
									prev:"",
									next:"",
									duration:' . $duration_time_v . ',
									delay:' . $delay_time_v . ',
									width:160,
									height:100,
									autoPlay:true,
									autoPlayVideo:false,
									playPause:true,
									stopOnHover:false,
									loop:false,
									bullets:1,
									caption:true,
									captionEffect:"parallax",
									controls:true,
									controlsThumb:false,
									responsive:1,
									fullScreen:false,
									gestures:2,
									onBeforeStep:0,
									images:0
								};
							var wowSliderContent = $(".wow_container_' . $type . '").html();
							var slider = $("#wowslider-container-' . $type . '").wowSlider(options);
						</script>';
                        $banner_val = $banner_val . '<p id="more_detail_banner" style="display:none; font-size: 12px; height: 25px; color:#000000;text-align:center;cursor:pointer;">' . l('click_Banner_for_more_detail') . '</p>';
                        $banner_val = $banner_val . '</div>';
                        $banner_val = $banner_val . '</div>';

                        if ($no > 0) {
                            $html->setvar("banner_" . $type, $banner_val);
                        } else {
                            $html->setvar("banner_" . $type, "");
                        }
                    }

                    $html->setvar("{$block}_class", $type . '_' . $class);
                    $html->parse($block, true);
                    $isParseBanner = true;
                }
            }

            if ($isParseBanner) {
                $isHide = false;
                if ($html->blockExists($block . '_list_hide') && guid()) {
                    if (Common::isActiveFeatureSuperPowers('kill_the_ads')) {
                        if (User::isSuperPowers()) {
                            $ads = json_decode($g_user['hide_ads'], true);
                            if ($ads && isset($ads[$optionNameTmpl]) && $ads[$optionNameTmpl]) {
                                $isHide = true;
                                $html->clean($block);
                                $html->parse($block . '_list_hide', false);
                            } elseif ($html->blockExists($block . '_icon_hide')) {
                                $html->parse($block . '_icon_hide', false);
                            }
                        }
                        $html->setvar($block . '_title', $isHide ? l('show_ads') : l('remove_ads'));
                        if ($isHide) {
                            $html->parse($block . '_show', false);
                        } else {
                            $html->clean($block . '_show');
                        }
                        $html->parse($block . '_list_action');
                    }
                }
                if ($html->blockexists($block . '_list')) {
                    $html->parse($block . '_list', false);
                }
                if (($optionNameTmpl == 'impact' || $optionNameTmpl == 'edge') && $html->blockexists("{$block}_bl")) {
                    $isParseGeneralBl = true;
                    if ($optionNameTmpl == 'edge') {
                        if (guid() && $isHide && in_array($type, array('footer_paid', 'footer'))) {
                            $isParseGeneralBl = false;
                        }
                    } else {
                        if (!guid() && !in_array($p, array('about.php', 'contact.php', 'page.php'))) {
                            $isParseGeneralBl = false;
                        }
                        if (guid() && $isHide && in_array($type, array('header_paid', 'header'))) {
                            $isParseGeneralBl = false;
                        }
                    }
                    if ($isParseGeneralBl) {
                        $html->parse("{$block}_bl", false);
                    }
                }
            } else {
                $html->setvar("{$block}_class", 'empty');
            }

        }
        return $isParseBanner;
    }

    public static function getBlockAll(&$html, $types)
    {
        $banners = explode(',', $types);
        if (is_array($banners)) {
            foreach ($banners as $type) {
                self::getBlock($html, $type);
            }
        }
    }

    public function parseBlock(&$html)
    {
        self::getBlock($html, $this->type);
        parent::parseBlock($html);
    }

    public static function isAdmobVisible($html)
    {
        $block = 'admob_banner_status';
        if (Common::isApp() && $html->blockExists($block)) {

            $isAdmobVisible = 'true';

            if (Common::isActiveFeatureSuperPowers('kill_the_ads')) {
                if (User::isSuperPowers()) {
                    $tmplName = Common::getTmplName();
                    $ads = json_decode(guser('hide_ads'), true);
                    if ($ads && isset($ads[$tmplName]) && $ads[$tmplName]) {
                        $isAdmobVisible = 'false';
                    }
                }
            }

            $html->setvar('isAdmobBannerVisible', $isAdmobVisible);
            $html->parse($block);
        }
    }

}
