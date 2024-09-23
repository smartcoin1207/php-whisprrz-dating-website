<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

$optionTmplName = Common::getTmplName();
if ($optionTmplName != 'edge' || !Common::isOptionActive('favorite_add')) {
    Common::toHomePage();
}

$isAjaxRequest = get_param('ajax');
if (!guid() && !$isAjaxRequest) {
    Common::toLoginPage();
}

class CPage extends CHtmlBlock {

    function parseBlock(&$html) {
        $ajax = get_param('ajax');
        if (!$ajax) {
            TemplateEdge::parseColumn($html);
        }

        $html->setvar('url_pages', Common::pageUrl('favorite_list'));

        parent::parseBlock($html);
    }
}

$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = getPageCustomTemplate(null, 'search_results_list');
if ($isAjaxRequest) {
    $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
    unset($tmplList['users_filter']);
    $page = new CPage("", $dirTmpl . 'search_results_ajax.html');
} elseif (TemplateEdge::isTemplateColums()) {
    $tmplList['main'] = $dirTmpl . '_list_users_info_columns.html';
    $tmplList['profile_column_left'] = $dirTmpl . '_profile_column_left.html';
    $tmplList['profile_column_right'] = $dirTmpl . '_profile_column_right.html';
    $page = new CPage("", $dirTmpl . 'search_results.html');
}


$list = new CHtmlUsersListFav('users_list', $tmplList);
$list->m_on_page = 9;
$list->m_view = 1;
$list->m_sql_where = "1";
$list->m_sql_order = "i.id ";
$list->m_sql_from_add = "JOIN friends AS i ON (u.user_id=i.fr_user_id AND i.user_id=" . guid() . ")";

$page->add($list);

if (!$isAjaxRequest) {
    $complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
    $page->add($complite);  
    $header = new CHeader("header", $dirTmpl . "_header.html");
    $page->add($header);
    $footer = new CFooter("footer", $dirTmpl . "_footer.html");
    $page->add($footer);
}

include('./_include/core/main_close.php');