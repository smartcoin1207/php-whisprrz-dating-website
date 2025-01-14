<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
class CMaps extends CHtmlBlock 
{
    static $cmd = '';
    static $isAjaxRequest = false;

    function action()
    {
        global $g;
        global $g_user;
        self::$isAjaxRequest = get_param('ajax');
        self::$cmd = get_param('cmd');

        if (self::$isAjaxRequest) {
            $cmd = get_param('cmd');

            $lat = get_param('latitude', '');
            $long = get_param('longitude', '');

            if($lat && $long) {
                DB::update('user', array('geo_position_lat' => to_sql($lat, 'Float') , 'geo_position_long' => to_sql($long, 'Float')), '`user_id` = ' . to_sql($g_user['user_id'], 'Number'));
            }
            
            echo json_encode('success');
            exit;
        } else {
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user;       

        $geoCityInfo = IP::geoInfoCity();
        $lat = $geoCityInfo['lat'];
        $long = $geoCityInfo['long'];

        if(get_param('map', '')) {
            $map_users = SearchResult::search();
            $html->setvar('map_users', json_encode($map_users));
            $html->setvar('advanced', 'advanced') ;
        } else {
            $html->setvar('map_users', json_encode(array()));
        }

        //loggedin
        if(guser()['user_id']) {
            $geo = User::getGeoPosition(guser()['city_id']);
            $geo_position_lat = floatval($geo['geo_position_lat']) / IP::MULTIPLICATOR;
            $geo_position_long = floatval($geo['geo_position_long']) / IP::MULTIPLICATOR;

            $html->setvar('geo_position_lat', $geo_position_lat);
            $html->setvar('geo_position_long', $geo_position_long);
            $html->setvar('city_id', guser()['city_id']);

            if(isset(guser()['set_show_me_map']) && guser()['set_show_me_map'] == '1') {
                $html->setvar('show_me', '1');
            }
            $html->parse('loggedin', false);
        }

        $field_count =  Common::getOption('map_user_field_number');
        $pic_size =  Common::getOption('map_pic_text_size');
        $pin_drop_size =  Common::getOption('map_drop_pin_size');

        $refresh_rate =  Common::getOption('map_refresh_rate');
        $map_default_mile = Common::getOption('map_default_mile');
        $map_click_to_profile_mode = Common::getOption('pic_click_mode_on_map');
        $long_click_time = Common::getOption('long_click_time');
        $map_adv_search = Common::isOptionActive('adv_map_search');
        $map_droppin_initial_number = Common::getOption('map_dropdown_initial_number');
        $map_same_location_feet_range = Common::getOption('map_same_location_feet_range');

        if($map_adv_search) {
            $html->parse('map_adv_search', false);
        }

        $html->setvar('column_count', $field_count);
        $html->parse('column_count', false);

        $html->setvar('map_pic_click_mode', $map_click_to_profile_mode);
        $html->setvar('long_click_time', $long_click_time);
        $html->setvar('map_droppin_initial_number', $map_droppin_initial_number);
        $html->setvar('map_same_location_feet_range', $map_same_location_feet_range);

        $map_miles = DB::row("SELECT * FROM config WHERE `option` = " . to_sql('map_default_mile', 'Text'));

        if($map_default_mile) {
            $options = $map_miles['options'];
            $optionsValues = explode('|', $options);
            $optionsArray = array();

            $map_default_mile_options = "";
            foreach ($optionsValues as $optionValue) {
                $selected = "";
                if($map_default_mile == $optionValue) {
                    $selected = " selected";
                } 
                $loptionValue = $optionValue . " miles";
                if($optionValue == 'all') {
                   $loptionValue = "All"; 
                }
                $map_default_mile_options .= "<option value=".$optionValue . $selected . ">" . $loptionValue . "</option>";
            }
        }

        $map_api_key = Common::getOption('google_map_api');
        $html->setvar('map_miles_options', $map_default_mile_options);
        $html->parse('map_miles_options', false);

        $html->setvar('map_pic_size', $pic_size);
        $html->setvar('pin_drop_size', $pin_drop_size);

        $html->setvar('map_api_key', $map_api_key);
        $html->parse('map_api_key', false);


        //flipcard start
        $l_sql = "SELECT id, title FROM looking_level";
        $l_levels = DB::rows($l_sql);

        $c_rows = DB::rows("SELECT *FROM const_relation");

        $flip_items = ["income", "status", "smoking", "drinking", "education", "height", "body", "hair", "eye", "ethnicity", "first_date", "live_where", "living_with", "appearance", "age_preference", "humor", "can_you_host"];
        $flip_item_titles = [];
        foreach ($flip_items as $key => $flip_item) {
            $flip_item_titles[$flip_item] =  l($flip_item);
        }

        $html->setvar('l_levels', json_encode($l_levels));
        $html->setvar('relations', json_encode($c_rows));
        $html->setvar('flip_items_title', json_encode($flip_item_titles));
        $html->parse('variables', false);
        //flipcard end

        //pupup windows
        $popup_row = DB::row("SELECT  * FROM posting_info  WHERE page = 'popup_maps' LIMIT 1");
        $html->setvar('popup_title', $popup_row['header']);
        $html->setvar('popup_confirm_button', $popup_row['active']);
        $html->setvar('popup_decline_button', $popup_row['deactive']);
        $var = array();
        $text = $popup_row['text'];
        $var['username'] = $g_user['name'];
        $var['map_terms'] = l('popup_map_terms');
        $var['time'] = date('Y-m-d h:i:s A');
        $var['privacy_policy'] = l('popup_privacy_policy');
        $ful_text = Common::replaceByVars($text, $var);
        $html->setvar('popup_text', $ful_text);
        $html->parse('popup_confirm_terms_policy', true);

        parent::parseBlock($html);
    }
}

$page = new CMaps("", $g['tmpl']['dir_tmpl_main'] . "maps.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");

$page->add($header);
$page->add($footer);

include("./_include/core/main_close.php");
