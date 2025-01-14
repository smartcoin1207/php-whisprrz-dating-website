<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

class CGroups extends CHtmlBlock
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

        $linkVars = array(
            'my_url' => Common::pageUrl('user_groups_list'),
            'my_title' => l('my_groups'),
            'my_icon' => ListBlocksOrder::getIconSvg('groups_my'),

            'videos_url' => Common::pageUrl('groups_vids_list'),
            'videos_icon' => ListBlocksOrder::getIconSvg('film'),

            'photos_url' => Common::pageUrl('groups_photos_list'),
            'photos_icon' => ListBlocksOrder::getIconSvg('picture'),

            'songs_url' => Common::pageUrl('groups_songs_list'),
            'songs_icon' => ListBlocksOrder::getIconSvg('audio_group'),
        );
        $html->assign('page_link_groups', $linkVars);

        $vars = array(
                'page_title'  => l('page_title'),
        );
    
        $html->assign('', $vars);

        $html->setvar('groups_add_url', Common::pageUrl('group_add'));
        $html->parse('page_link_groups_add', false);
        $html->parse('edge_groups', false);
        $html->parse('page_link_my_groups', false);
        $html->setvar('page_link_groups_search_title', l('search'));
        $html->parse('page_link_delimiter', false);
    
        //Location for Search Groups
        $country = $g_user['country_id'];
        $state   = $g_user['state_id'];
        $city    = $g_user['city_id'];

        $html->setvar('country_options', Common::listCountries($country));
        $html->setvar('state_options', Common::listStates($country, $state));
        $html->setvar('city_options', Common::listCities($state, $city));
        $html->parse('location', false);

        //Category for Search Groups
        $sql_category = 'SELECT * FROM `groups_category`';
        $categories = DB::rows($sql_category);

        $groups_category = [];

        $groups_category['all'] = "All";
        foreach ($categories as $key => $row) {
            $groups_category[$row['category_id']] = $row['category_title'];
        }

        $html->setvar('category_options', h_options($groups_category, 'all'));
        $html->parse('bl_category', false);

        //Groups I own
        $sql_own = "SELECT * FROM groups_social WHERE user_id = " . to_sql(guid(), 'Text') . " AND `page`= '0'" ;
        $myOwnGroups = DB::rows($sql_own);

        if($myOwnGroups) {
            foreach ($myOwnGroups as $key => $value) {
                $group_url = $g['path']['url_main'] . $value['name_seo'];
                $html->setvar('group_url', $group_url);
                $html->setvar('group_title', $value['title']);
                $html->parse('group_item', true);
            }
            $html->setvar('group_topic_title', l('groups_own'));
            $html->parse('group_items', false);
            $html->parse('group_own', false);
            $html->clean('group_item');
            $html->clean('group_items');
        }

        //Groups I Belongs To
        $sql_belong = "SELECT * FROM groups_social as g LEFT JOIN groups_social_subscribers as gs ON g.group_id = gs.group_id WHERE gs.user_id = " . to_sql(guid(), 'Text')  . " AND gs.group_user_id != " . to_sql(guid(), 'Text') . " AND g.page='0'";
        $belongGroups = DB::rows($sql_belong);

        if($belongGroups) {
            foreach ($belongGroups as $key => $value) {
                $group_url = $g['path']['url_main'] . $value['name_seo'];
                $html->setvar('group_url', $group_url);
                $html->setvar('group_title', $value['title']);
                $html->parse('group_belong_item', true);
            }
            $html->setvar('group_topic_title', l('groups_belong_to'));
            $html->parse('group_belong_items', false);
            $html->parse('group_belong', false);
            $html->clean('group_belong_item');
            $html->clean('group_belong_items');
        }

        //Newest Groups 
        $newest_days = 2;
        $newest_num = 10;
        
        $sql_newest = "SELECT * FROM groups_social WHERE date >= " . to_sql(date('Y-m-d H:00:00', time() - intval($newest_days) * 3600 * 24)) . " AND `page`=0" . " LIMIT  " . $newest_num ;
        $newest_groups = DB::rows($sql_newest);

        if(1==1 || $newest_groups) {
            foreach ($newest_groups as $key => $value) {
                $group_url = $g['path']['url_main'] . $value['name_seo'];
                $html->setvar('group_url', $group_url);
                $html->setvar('group_title', $value['title']);
                $html->parse('group_newest_item', true);
            }
            $html->setvar('group_topic_title', l('groups_newest'));
            $html->parse('group_newest_items', false);
            $html->parse('group_newest', false);
            $html->clean('group_newest_item');
            $html->clean('group_newest_items');
        }

        //Most Popular
        $popular_days = 2;
        $popular_num = 10;
        $sql_popular = "SELECT DISTINCT g.* FROM groups_social as g LEFT JOIN groups_social_subscribers as gs ON g.group_id=gs.group_id WHERE gs.created_at >= " . to_sql(date('Y-m-d H:00:00', time() - intval($popular_days) * 3600 * 24)) . " AND g.page='0'" . " LIMIT  " . $popular_num;
        $popular_groups = DB::rows($sql_popular);
        
        if(1==1 || $popular_groups) {
            foreach ($popular_groups as $key => $value) {
                $group_url = $g['path']['url_main'] . $value['name_seo'];
                $html->setvar('group_url', $group_url);
                $html->setvar('group_title', $value['title']);
                $html->parse('group_popular_item', true);
            }
            $html->setvar('group_topic_title', l('groups_popular'));
            $html->parse('group_popular_items', false);
            $html->parse('group_popular', false);
            $html->clean('group_popular_item');
            $html->clean('group_popular_items');
        }

        //Closet Groups
        $closetDistance = 75; // mile
        $from_add = " LEFT JOIN geo_city AS gc ON gc.city_id = gs.city_id";
        $city_id = $g_user['city_id'];
        $inRadiusWhere = inradius($city_id, $closetDistance);

        $sql_closet = "SELECT * FROM groups_social as gs " . $from_add .  " WHERE 1=1 " . $inRadiusWhere . " AND gs.page='0'" ; 
        $closet_groups = DB::rows($sql_closet); 

        if($closet_groups) {
            foreach ($closet_groups as $key => $value) {
                $group_url = $g['path']['url_main'] . $value['name_seo'];
                $html->setvar('group_url', $group_url);
                $html->setvar('group_title', $value['title']);
                $html->parse('group_closet_item', true);
            }
            $html->setvar('group_topic_title', l('groups_closet'));
            $html->parse('group_closet_items', false);
            $html->parse('group_closet', false);
            $html->clean('group_closet_item');
            $html->clean('group_closet_items');
        }

        //Distance radius options
        $unit = l(Common::getOption('unit_distance'));

        $radius = array('all' => l('all'));
        $max = intval(Common::getOption('max_search_distance'));
        if ($max > 0) {
            $interval = round($max / 8);
            $min = $interval;
            for ($i = 0; $i < 7; $i++) {
                $radius[$min] = $min . " " . $unit;
                $min += $interval;
            }
        }
        $radius[$max] = $max . " " . $unit;

        $html->setvar('radius_options', h_options($radius, get_param('radius', 'all')));
        $html->parse('radius_options', false);
        
        $html->parse('group_advanced_search_filter', false);
        $html->parse('wrap_head_links', false);
        //search result
        $cmd = get_param('cmd', '');

        if($cmd == 'search_group') {
            $search_query = get_param('search_query', '');
            $name_where = '';
            if($search_query) {
                $name_where = ' AND gs.title LIKE ' . to_sql("%" . $search_query . "%", 'Text') . ' ';
            }

            $category = get_param('group_category', '');
            $category_where = '';
            if($category != 'all' && $category) {
                $category_where = ' AND gs.category_id = ' . to_sql($category, 'Text') . ' ';
            }

            $radius = get_param('group_radius', '');
            $radius_where = '';
            $from_add = '';
            if($radius != 'all' && $radius) {
                $closetDistance = $radius; // mile
                $from_add = " LEFT JOIN geo_city AS gc ON gc.city_id = gs.city_id";
                $city_id = $g_user['city_id'];
                $radius_where = inradius($city_id, $closetDistance);
            }

            $country_id = get_param('group_country', '');
            $state_id = get_param('group_state', '');
            $city_id = get_param('group_city', '');

            $country_where = '';
            if($city_id) {
                $country_where = ' AND gs.city_id = ' . to_sql($city_id, 'Text') . ' ';
            } elseif (!$city_id && $state_id) {
                $country_where = ' AND gs.state_id = ' . to_sql($state_id, 'Text') . ' ';
            } elseif (!$city_id && !$state_id && $country_id) {
                $country_where = ' AND gs.country_id = ' . to_sql($country_id, 'Text') . ' ';
            } 

            $where = $name_where . $category_where . $radius_where . $country_where;

            $search_sql = "SELECT * FROM groups_social as gs LEFT JOIN groups_category as gcat ON gs.category_id = gcat.category_id " . $from_add .  " WHERE 1=1 " . $where . " AND gs.page='0'";
            $search_groups = DB::rows($search_sql);

            if($search_groups) {
                foreach ($search_groups as $key => $value) {
                    $group_url = $g['path']['url_main'] . $value['name_seo'];
                    $html->setvar('group_url', $group_url);
                    $html->setvar('group_title', $value['title']);
                    $html->parse('group_search_item', true);
                }
                $html->setvar('group_topic_title', l('search_results'));
                $html->parse('group_search_items', false);
                $html->parse('group_search_result', false);
                $html->clean('group_search_item');
                $html->clean('group_search_items');
            } else {
                $html->setvar('group_topic_title', l('search_results'));
                $html->parse('no_empty', false);
                $html->parse('group_search_result', false);
            } 
        }
        parent::parseBlock($html);
    }
}

$dirTmpl = $g['tmpl']['dir_tmpl_main'];

$tmplList = array('main'   => $dirTmpl . 'groups_search_advanced.html',
                  'list'   => $dirTmpl . '_list_page_info_columns.html'
                  );

$page = new CGroups("", $tmplList);
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");
