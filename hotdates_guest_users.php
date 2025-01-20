<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

$optionTmplName = Common::getTmplName();
if ($optionTmplName != 'edge') {
    Common::toHomePage();
}

$isAjaxRequest = get_param('ajax');
$hideFromGuests = Common::isOptionActive('list_photos_hide_from_guests', "{$optionTmplName}_general_settings");
if (!guid() && !$isAjaxRequest) {
    $uid = User::getParamUid(0);
    if ($hideFromGuests || $uid) {
        Common::toLoginPage();
    }
}

class CPage extends CHtmlBlock
{

    function action() {
        $guid = guid();
        $uid = User::getParamUid(0);

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $data_cmd = get_param('data-cmd', '');
        $removeUid = get_param('removeUid', '');
        $hotdate_id = ChotdatesTools::getParamHotdateId();

        if($data_cmd == 'guest_remove' && $ajax) {
            if($removeUid) {
                DB::execute("DELETE FROM hotdates_hotdate_guest WHERE hotdate_id= " . to_sql($hotdate_id) . " AND user_id=" . to_sql($removeUid));
            }
        }
    }

    function init()
    {
    }

    function parseBlock(&$html)
    {
        $guid = guid();
        $uid = User::getParamUid(0);

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $hotdate_id = ChotdatesTools::getParamHotdateId();
        if(!$hotdate_id) {
            Common::toLoginPage();
        }

        $search_query = get_param('search_query', '');

        $sql_hotdate = "SELECT * FROM hotdates_hotdate WHERE hotdate_id=" . to_sql($hotdate_id);
        $hotdate = DB::row($sql_hotdate);
        if(!$hotdate) {
            Common::toLoginPage();
        }

        $pageTitle = lSetVars('page_title', array('hotdate_title' => $hotdate['hotdate_title']));

        $vars = array('page_title'   => $pageTitle,
                      'page_description' => 'DDD',
                      'url_pages'    => './hotdates_guest_users?hotdate_id='. $hotdate_id ,
                      'page_number' => $page,
                      'page_user_id' =>' $guid',
                      'page_param'   => 'page',
                      'page_type' => 'guest_users',
                      'page_filter' => 1,
                      'page_guid' => $guid
                    );

        $pagerOnPage = Common::getOptionInt('list_people_number_users', "{$optionTmplName}_general_settings");

        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        if (!$mOnBar) {
            $mOnBar = 5;
        }
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $offset = ($page-1) * $pagerOnPage;

        $block = 'list_photos';
        $class = "Template{$optionTmplName}";

        $search_query_where = "";
        if($search_query) {
            $search_query_where = ' AND u.name LIKE ' . to_sql("%" . $search_query . "%", 'Text');
        }

        $itemsTotal = DB::result('SELECT COUNT(*) FROM hotdates_hotdate_guest F LEFT JOIN user as u ON u.user_id = F.user_id WHERE hotdate_id = ' . to_sql($hotdate_id) . $search_query_where);

        if ($ajax) {
            $html->setvar('num_total', $itemsTotal);
        } else {
            $html->assign('', $vars);

            // TemplateEdge::parseColumn($html, $guid);
            $blockItem = 'right_banner';
            CBanner::getBlock($html, 'right_column');
        }

        $html->parse('page_filter', false);
        $html->parse('page_search_query', false);
        $html->parse('wrap_head_links', false);

        $profileDisplayType = Common::getOption('list_people_display_type', "{$optionTmplName}_general_settings");
        $block = "list_people_{$profileDisplayType}";
        $html->parse($block, false);

        $rows = DB::rows(" SELECT u.*, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(u.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(u.birth, '00-%m-%d')) AS age FROM hotdates_hotdate_guest as F LEFT JOIN user as u ON F.user_id=u.user_id WHERE F.hotdate_id = " . to_sql($hotdate_id) . $search_query_where .  " LIMIT " . $pagerOnPage . " OFFSET " . $offset);

        if ($rows) {
            $numberRow = Common::getOptionInt('list_people_number_row', "{$optionTmplName}_general_settings");

            foreach ($rows as $key => $row) {
                $class::parseUser($html, $row, $numberRow, $profileDisplayType, 'users_list_item');
                $html->parse('users_list_item');
            }

            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, './');
        } else {
            $html->parse('list_noitems');
        }

        parent::parseBlock($html);
    }
}

$tmplList = getPageCustomTemplate('groups_subscribers_template.html', 'groups_subscribers_template');

$dirTmpl = $g['tmpl']['dir_tmpl_main'];

if ($isAjaxRequest) {
    $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
    unset($tmplList['filter']);
} elseif (TemplateEdge::isTemplateColums()) {
    $tmplList['list'] = $dirTmpl . '_list_page_info_columns.html';
    $tmplList['profile_column_left'] = $dirTmpl . '_profile_column_left.html';
    $tmplList['profile_column_right'] = $dirTmpl . '_profile_column_right.html';
} 

if(!$isAjaxRequest) {
    $tmplList['filter'] = $dirTmpl . '_list_subscribers_filter.html';
}

$page = new CPage("", $tmplList);

if($isAjaxRequest) {
    getResponsePageAjaxByAuthStop($page, $hideFromGuests ? guid() : 1);
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);



include('./_include/core/main_close.php');