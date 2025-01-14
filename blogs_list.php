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
$hideFromGuests = Common::isOptionActive('list_blog_posts_hide_from_guests', "{$optionTmplName}_general_settings");
if (!guid() && !$isAjaxRequest) {
    $uid = User::getParamUid(0);
    if ($hideFromGuests || $uid) {
        Common::toLoginPage();
    }
}

User::accessCheckToProfile(true);

class CPage extends CHtmlBlock
{
    function init()
    {

    }

    function parseBlock(&$html)
    {
        $guid = guid();

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();
        $uid = User::getParamUid(0);

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $pageTitle = l('page_title');
        $pageDescription = '';
        $pageClass = 'blogs_list';

        if ($uid) {
            if ($uid == $guid) {
                $pageTitle = l('your_blogs');
            } else {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);
                $pageTitle = lSetVars('page_title_someones', array('name' => $name));
                $pageDescription = l('here_you_can_browse_the_users_blogs');
            }
            $pageClass .= ' blogs_list_user';
        }

        $pageUrl = Common::pageUrl('blogs_list');
        if ($uid) {
            $pageUrl = Common::pageUrl('user_blogs_list', $uid);
        }
        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => $uid,
                      'page_guid' => $guid,
                      'url_pages' => $pageUrl,
                      'page_type' => 'blogs',
                      'page_filter' => intval(!$uid)
        );

        $typeOrderDefault = Common::getOption('list_blog_posts_type_order', "{$optionTmplName}_general_settings");
        $pagerOnPage = Common::getOptionInt('list_blog_posts_number_items', "{$optionTmplName}_general_settings");
        if ($uid) {
            if ($uid == $guid) {
                $typeOrderDefault = Common::getOption('list_blog_my_posts_type_order', "{$optionTmplName}_blogs_settings");
                $pagerOnPage = Common::getOptionInt('list_blog_my_posts_number_items', "{$optionTmplName}_blogs_settings");
            } else {
                $typeOrderDefault = Common::getOption('list_blog_someones_posts_type_order', "{$optionTmplName}_blogs_settings");
                $pagerOnPage = Common::getOptionInt('list_blog_someones_posts_number_items', "{$optionTmplName}_blogs_settings");
            }
        }

        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_blog_posts';
        $class = "Template{$optionTmplName}";

        $filter = 'blogs_filters';

        if (!$ajax && !$uid) {
            $tagId = get_param_int('tag');
            $tagInfo = Blogs::getTagInfo($tagId);
            if ($tagInfo) {
                $_GET['tags'] = $tagInfo['tag'];
                //User::updateUserFilter($filter, array('tags'));
            }
            User::setUserFilterParam($filter, $typeOrderDefault);
            if (get_param_int('only_friends')) {
                $html->setvar('only_friends', 1);
                $html->parse('module_search_only_friends', false);
            }
        }

        Blogs::$isGetDataWithFilter = true;
        $itemsTotal = Blogs::getTotalBlogs($uid);
        Blogs::$isGetDataWithFilter = false;

        $typeOrder = get_param('type_order', $typeOrderDefault);

        if (!$ajax) {
            $vars['tags'] = toAttr(get_param('tags'));
            $vars['search_query'] = get_param('search_query');
            $vars['type_order'] = $typeOrder;
            $vars['filter_order_list_options'] = h_options(Blogs::getTypeOrderList(true), $typeOrder);

            $html->assign('', $vars);

            if ($guid) {
                if (!$uid || $uid == $guid) {
                    $html->parse('page_link_add_blog', false);
                    if (!$uid) {
                        $html->setvar('page_link_my_blogs_url', Common::pageUrl('user_blogs_list', $guid));
                        $html->parse('page_link_my_blogs', false);
                    }
                }
                $html->parse('page_filter_only_friends', false);
            }
            if (!$uid) {
                if ($guid) {
                    $html->parse('page_link_delimiter', false);
                }
                $html->parse('page_search_query', false);
                $html->parse('page_filter_no_result', false);
                $html->parse('page_filter', false);
            }

            if (!$uid || $uid == $guid) {
                $html->parse('wrap_head_links', false);
            }
            TemplateEdge::parseColumn($html, $uid ? $uid : $guid);
        } else {
            $html->setvar('num_total', $itemsTotal);
        }

        Blogs::$isGetDataWithFilter = true;
        $rows = $class::parseListBlogs($html, $typeOrder, $limit, $block);
        Blogs::$isGetDataWithFilter = false;

        if ($rows) {
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar);
        } else {
            $html->parse("list_noitems");
        }

        if ($guid && $ajax && !$uid) {
            User::updateUserFilter('blogs_filters');
        }

        $html->parse('blogs_list', false);

        parent::parseBlock($html);
    }
}

$uid = get_param_int('uid');
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = array('main'   => $dirTmpl . 'page_list.html',
                  'list'   => $dirTmpl . '_list_page_info.html',
                  'filter' => $dirTmpl . '_list_page_filter.html',
                  'items'  => $dirTmpl . '_list_page_items.html',
                  'item'   => $dirTmpl . '_list_blogs_item.html',
                  'pages'  => $dirTmpl . '_list_page_pages.html');
if ($uid) {
   unset($tmplList['filter']);
}
if ($isAjaxRequest) {
    $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
    unset($tmplList['filter']);
} elseif (TemplateEdge::isTemplateColums()) {
    $tmplList['list'] = $dirTmpl . '_list_page_info_columns.html';
    $tmplList['profile_column_left'] = $dirTmpl . '_profile_column_left.html';
    $tmplList['profile_column_right'] = $dirTmpl . '_profile_column_right.html';
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