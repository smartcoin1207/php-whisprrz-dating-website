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
$hideFromGuests = Common::isOptionActive('list_songs_hide_from_guests', "{$optionTmplName}_general_settings");
if (!guid() && !$isAjaxRequest) {
    $uid = User::getParamUid(0);
    if ($hideFromGuests || $uid) {
        Common::toLoginPage();
    }
}

Groups::checkAccessGroup();
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

        $groupsList = Groups::getParamTypeContentList();
        $isPagesList = $groupsList == 'group_page';
        if ($groupsList) {
            $groupId = 0;
        } else {
            $groupId = Groups::getParamId();
            if ($groupId) {
                $groupInfo = Groups::getInfoBasic($groupId);
                if (!$groupInfo) {
                    $groupId = 0;
                }
            }
        }


        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $pageTitle = l('page_title');
        $pageDescription = '';
        $pageClass = 'songs_list';


        if ($uid) {
            if ($groupId) {
                $pageTitle = lSetVars('page_title_someones', array('name' => $groupInfo['title']));
            } elseif ($uid == $guid) {
                if ($groupsList){
                    $pageTitle = $isPagesList ? l('songs_your_pages') : l('songs_your_groups');
                } else {
                    $pageTitle = l('your_songs');
                }
            } else {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);
                $pageTitle = lSetVars('page_title_someones', array('name' => $name));
                $pageDescription = l('here_you_can_listen_to_the_users_songs');
            }
            $pageClass .= ' songs_list_user';
        } elseif($groupsList) {
            $pageTitle = $isPagesList ? l('page_title_pages') : l('page_title_groups');
        }

        $pageUrl = Common::pageUrl('songs_list');
        if ($uid) {
            if ($groupsList){
                $pageUrl = $isPagesList ? Common::pageUrl('user_my_pages_songs_list', $guid) : Common::pageUrl('user_my_groups_songs_list', $guid);
            } elseif ($groupId) {
                $pageUrl = Common::pageUrl('group_songs_list', $groupId);
            } else {
                $pageUrl = Common::pageUrl('user_songs_list', $uid);
            }
        } elseif ($groupsList){
            $pageUrl = $isPagesList ? Common::pageUrl('pages_songs_list') : Common::pageUrl('groups_songs_list');
        }

        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => $uid,
                      'page_guid' => $guid,
                      'url_pages' => $pageUrl,
                      'page_type' => 'songs',
                      'page_filter' => intval(!$uid),
                      'group_id'    => $groupId,
                      'group_type'  => $groupsList ? $groupsList : ''
                );

        $pagerOnPage = Common::getOptionInt('list_songs_number_items', "{$optionTmplName}_general_settings");
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_songs';
        $class = "Template{$optionTmplName}";

        $typeOrderDefault = Common::getOption('list_songs_type_order', "{$optionTmplName}_general_settings");
        $filter = 'songs_filters';

        $filter = CProfilePhoto::getNameFilter('songs');
        if (!$ajax && !$uid) {
            $tagId = get_param_int('tag');
            $tagInfo = Songs::getTagInfo($tagId);
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

        Songs::$isGetDataWithFilter = true;
        $itemsTotal = Songs::getTotal($uid, $groupId);

        Songs::$isGetDataWithFilter = false;

        $typeOrder = get_param('type_order', $typeOrderDefault);

        if (!$ajax) {
            $vars['tags'] = toAttr(get_param('tags'));
            $vars['search_query'] = get_param('search_query');
            $vars['type_order'] = $typeOrder;
            $vars['filter_order_list_options'] = h_options(Songs::getTypeOrderList(true), $typeOrder);

            $html->assign('', $vars);

            $html->parse('page_filter_tags_hide', false);

            if ($guid) {
                if ($groupsList) {
                    if ($uid) {
                        $blockLink = 'page_link_groups_songs';
                        $urlLink = $isPagesList ? Common::pageUrl('pages_songs_list') : Common::pageUrl('groups_songs_list');
                        $urlTitle = $isPagesList ? l('link_pages_songs') : l('link_groups_songs');
                    } else {
                        $urlTitle = '';
                        $blockLink = 'page_link_my_groups_songs';
                        $urlLink = $isPagesList ? Common::pageUrl('user_my_pages_songs_list', $guid) : Common::pageUrl('user_my_groups_songs_list', $guid);
                    }
                    $html->setvar("{$blockLink}_url", $urlLink);
                    if ($urlTitle) {
                        $html->setvar("{$blockLink}_title", $urlTitle);
                    }

                    $html->parse($blockLink, false);
                } else {
                    if (!$uid || $uid == $guid) {
                        $html->parse('page_link_songs_upload', false);
                        if (!$uid) {
                            $html->parse('page_link_my_songs', false);
                        }
                    }
                    $html->parse('page_filter_only_friends', false);
                }
            }
            if (!$uid) {
                if ($guid && !$groupsList) {
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

        Songs::$isGetDataWithFilter = true;
        $rows = $class::parseListSongs($html, $typeOrder, $limit, $block, $groupId, true);
        Songs::$isGetDataWithFilter = false;
        if ($rows) {
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl);
        } else {
            $html->parse("list_noitems");
        }

        if ($guid && $ajax && !$uid) {
            User::updateUserFilter($filter);
        }
        parent::parseBlock($html);
    }
}

Groups::setTypeContentList();

$uid = get_param_int('uid');
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = array('main'   => $dirTmpl . 'page_list.html',
                  'list'   => $dirTmpl . '_list_page_info.html',
                  'filter' => $dirTmpl . '_list_page_filter.html',
                  'items'  => $dirTmpl . '_list_page_items.html',
                  'item'   => $dirTmpl . '_list_songs_item.html',
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