<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
include('./_include/current/favorite.class.php');

$optionTmplName = Common::getTmplName();
if ($optionTmplName != 'edge' || !Common::isOptionActive('favorite_add')) {
    Common::toHomePage();
}
$hideFromGuests = Common::isOptionActive('list_photos_hide_from_guests', "{$optionTmplName}_general_settings");

$isAjaxRequest = get_param('ajax');
if (!guid() && !$isAjaxRequest) {
    Common::toLoginPage();
}

class CPage extends CHtmlBlock
{
    function init()
    {
    }

    function parseBlock(&$html)
    {
        $guid = guid();
        $uid = User::getParamUid(0);

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();
        $search_query = get_param('search_query', '');

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $pageTitle = l('page_title');
        $pageDescription = '';
        $pageUrl = Common::pageUrl('favorite_list');

        $vars = array('page_title'   => $pageTitle,
                      'page_description' => $pageDescription,
                      'url_pages'    => $pageUrl,
                      'page_number' => $page,
                      'page_user_id' => $guid,
                      'page_param'   => 'page',
                      'page_type' => 'favorite_list',
                      'page_filter' => 1,
                      'page_guid' => $guid,
                    );

        $pagerOnPage = Common::getOptionInt('list_people_number_users', "{$optionTmplName}_general_settings");
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        if (!$mOnBar) {
            $mOnBar = 5;
        }
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_photos';
        $class = "Template{$optionTmplName}";

        $itemsTotal = 0;

        $sql = "SELECT DISTINCT u.user_id FROM user AS u LEFT JOIN friends AS f1 ON u.user_id=f1.fr_user_id LEFT JOIN friends as f2 ON u.user_id=f2.user_id WHERE f1.user_id=" . to_sql(guid(), 'Text') . " OR f2.fr_user_id = " . to_sql(guid(), 'Text') . "";

        DB::query($sql);
        $itemsTotal = DB::num_rows();

        if ($ajax) {
            $html->setvar('num_total', $itemsTotal);
        } else {
            $html->assign('', $vars);

            TemplateEdge::parseColumn($html, $uid ? $uid : $guid);
        }

        $html->parse('page_filter', false);
        // $html->parse('page_search_query', false);
        $html->parse('wrap_head_links', false);

        $profileDisplayType = Common::getOption('list_people_display_type', "{$optionTmplName}_general_settings");
        $block = "list_people_{$profileDisplayType}";
        $html->parse($block, false);

        $rows = CFavorite::getListSubscribers(false, $limit, false, $search_query);

        if ($rows) {
            $numberRow = Common::getOptionInt('list_people_number_row', "{$optionTmplName}_general_settings");

            foreach ($rows as $key => $row) {
                $class::parseUser($html, $row, $numberRow, $profileDisplayType, 'users_list_item');
                $html->parse('users_list_item');
            }
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl);
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