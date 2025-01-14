<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";

include("./_include/core/main_start.php");



$isSocialCalendar = Common::isOptionActiveTemplate('event_social_enabled');

if (Common::isParseModule('events_header')) {
    require_once("./_include/current/events/custom_head.php");
}

if (Common::isParseModule('events_header')) {
    require_once("./_include/current/events/header.php");
}

if (Common::isParseModule('events_sidebar')) {
    require_once("./_include/current/events/sidebar.php");
}

if($isSocialCalendar) {
    require_once("./_include/current/main/tools.php");
    require_once("./_include/current/main/calendar.php");
}
require_once("./_include/current/events/header.php");
require_once("./_include/current/events/sidebar.php");
require_once("./_include/current/main/tools.php");
require_once("./_include/current/events/event_show.php");
require_once("./_include/current/events/event_image_list.php");
require_once("./_include/current/events/event_guest_list.php");
require_once("./_include/current/events/event_comment_list.php");
require_once("./_include/current/events/event_list.php");
require_once("./_include/current/main/calendar.php");

class CMain extends CHtmlBlock
{
    function action()
    {
        global $g_user;
        global $l;
        global $g;

    }

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;
        global $p;

        // var_dump($g_user); die();

        $guid = guid();
        $uid = User::getParamUid();

        $user = DB::row("SELECT * FROM  user WHERE user_id = '" . to_sql($uid, 'Number') . "'");
        if($html->varExists('page_title')){
            $pageTitle = l('your_calendar');
            $main_calendar_title = l('main_calendar_title');
            $event_calendar_title = l('event_calendar_title');
            $hotdate_calendar_title = l('hotdate_calendar_title');
            $partyhou_calendar_title = l('partyhou_calendar_title');

            if($uid == $guid) {
                $html->setvar('url_page_task_create', Common::pageUrl('task_create', $uid));
                $html->parse('url_page_task_create');
            }

            if ($uid != $guid) {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);
                $pageTitle = lSetVars('page_title_someones', array('name' => $name));

                $main_calendar_title = lSetVars('main_calendar_title_someones', array('name' => $name));
                $event_calendar_title = lSetVars('event_calendar_title_someones', array('name' => $name));
                $hotdate_calendar_title = lSetVars('hotdate_calendar_title_someones', array('name' => $name));
                $partyhou_calendar_title = lSetVars('partyhou_calendar_title_someones', array('name' => $name));

                $html->setvar('page_title_user_photo', User::getPhotoDefault($uid, 'r'));

                $html->parse('page_title_user_photo', false);
                $html->parse('page_title_header_photo', false);
            }
            $html->setvar('page_title', $pageTitle);
            $html->setvar('main_calendar_title', $main_calendar_title);
            $html->setvar('event_calendar_title', $event_calendar_title);
            $html->setvar('hotdate_calendar_title', $hotdate_calendar_title);
            $html->setvar('partyhou_calendar_title', $partyhou_calendar_title);
        }

        if($html->varExists('main_calendar_url')){
            $html->setvar('main_calendar_url', Common::pageUrl('user_calendar', $uid));
        }

        if($html->varExists('event_calendar_url')){
            $html->setvar('event_calendar_url', Common::pageUrl('user_event_calendar', $uid));
        }

        if($html->varExists('hotdate_calendar_url')){
            $html->setvar('hotdate_calendar_url', Common::pageUrl('user_hotdate_calendar', $uid));
        }

        if($html->varExists('partyhou_calendar_url')){
            $html->setvar('partyhou_calendar_url', Common::pageUrl('user_partyhou_calendar', $uid));
        }
        
        //calendar search

        $html->setvar('search_title', 'Serch Events');

        $calendarSearchUrl = $g['path']['url_main'] . 'event_calendar';
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

        //event category start
        $sql = 'SELECT * FROM `events_category`';
        $rows = DB::rows($sql);
        $plans['0'] = "All";
        foreach ($rows as $key => $row) {
            $plans[$row['category_id']] = $row['category_title'];
        }
        $html->setvar('category_options', h_options($plans, ''));
        $html->parse('category_event_select', false);
        //event category end

        //event category start
        $sql = 'SELECT * FROM `hotdates_category`';
        $rows = DB::rows($sql);
        $plans['0'] = "All";
        foreach ($rows as $key => $row) {
            $plans[$row['category_id']] = $row['category_title'];
        }
        $html->setvar('category_options', h_options($plans, ''));
        $html->parse('category_hotdate_select', false);
        //event category end

        //event category start
        $sql = 'SELECT * FROM `partyhouz_category`';
        $rows = DB::rows($sql);
        $plans['0'] = "All";
        foreach ($rows as $key => $row) {
            $plans[$row['category_id']] = $row['category_title'];
        }
        $html->setvar('category_options', h_options($plans, ''));
        $html->parse('category_partyhou_select', false);
        //event category end

        // locked partyhou calendar search start
        $partyhouz_locked['all'] = l('partyhouz_locked_all');
        $partyhouz_locked['lock'] = l('partyhouz_locked_lock');
        $partyhouz_locked['unlock'] = l('partyhouz_locked_unlock');
        $html->setvar('partyhouz_lock_options', h_options($partyhouz_locked, 'all'));
        $html->parse('partyhou_lock_select', false);
        // locked partyhou calendar search end

        // date for calendar search start
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

        TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
    }
}

$page = new CMain("", getPageCustomTemplate('events_calendar.html', 'calendar_template'));
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$tmpl = $g['tmpl']['dir_tmpl_main'] . "_events_calendar.html";
if (Common::isOptionActiveTemplate('event_social_enabled')) {
    $tmpl = array(
        'main' => $g['tmpl']['dir_tmpl_main'] . '_events_calendar.html',
        'items' => $g['tmpl']['dir_tmpl_main'] . '_events_calendar_items.html',
    );
}
$main_calendar = new CMainCalendar("events_calendar", $tmpl);


$page->add($main_calendar);

include("./_include/core/main_close.php");
