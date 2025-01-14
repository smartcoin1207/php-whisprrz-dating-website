<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CPartyhouCalendarSearch extends CHtmlBlock
{

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        //calendar search
        $user = $g_user;
        $html->setvar('search_title', 'Serch Partyhouz');

        $calendarSearchUrl = $g['path']['url_main'] . 'partyhou_calendar';
        $html->setvar('calendar_search_url', $calendarSearchUrl);

        $html->setvar('user_city', $user['city']);
        $html->setvar('user_state', $user['state']);
        $html->setvar('user_country', $user['country']);
        $country = $g_user['country_id'];
        $state   = $g_user['state_id'];
        $city    = $g_user['city_id'];

        $html->setvar('country_options', Common::listCountries($country));
        $html->setvar('state_options', Common::listStates($country, $state));
        $html->setvar('city_options', Common::listCities($state, $city));
        $html->parse('location', false);

        // //event category start
        $sql = 'SELECT * FROM `partyhouz_category`';
        $rows = DB::rows($sql);
        $categories['0'] = "All";
        foreach ($rows as $key => $row) {
            $categories[$row['category_id']] = $row['category_title'];
        }
        $html->setvar('category_options', h_options($categories, ''));
        $html->parse('category_partyhou_select', false);
        //event category end

        // locked partyhou calendar search start
        $partyhouz_locked['all'] = l('partyhouz_locked_all');
        $partyhouz_locked['lock'] = l('partyhouz_locked_lock');
        $partyhouz_locked['unlock'] = l('partyhouz_locked_unlock');
        $html->setvar('partyhouz_lock_options', h_options($partyhouz_locked, 'all'));
        $html->parse('partyhou_lock_select', false);
        // locked partyhou calendar search end

        //date for calendar search start
        $isIos = Common::isAppIos();

        $formatDateMonths = 'F';
        $optionFormatDateMonths = Common::getOption('format_date_months_join', 'template_options');
        if ($optionFormatDateMonths) {
            $formatDateMonths = $optionFormatDateMonths;
        }

        $defaultMonth = date('m');
        $defaultYear = date('Y');
        
        $html->setvar('month_options', h_options(Common::plListMonths($formatDateMonths, $isIos), get_param('month', $defaultMonth)));
        $html->setvar('year_options', n_options(date('Y') - 5, date('Y') + 5, get_param("year", $defaultYear), $isIos));
        
        //date for calendar search end

        //distance for calendar search start
        $distances['all'] = 'All';
        $distances['5'] = '5 mi';
        $distances['10'] = '10 mi';
        $distances['15'] = '15 mi';
        $distances['20'] = '20 mi';
        $distances['50'] = '50 mi';
        $distances['100'] = '100 mi';
        $distances['200'] = '200 mi';
        $html->setvar('distance_options', h_options($distances, '0'));
        //distance for calendar search end

        $html->parse('calendar_search');

        parent::parseBlock($html);
    }
}

