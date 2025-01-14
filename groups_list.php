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

$isPage = Groups::isPage();
$isAjaxRequest = get_param('ajax');
$keyOption = $isPage ? 'list_pages_hide_from_guests' : 'list_groups_hide_from_guests';
$hideFromGuests = Common::isOptionActive($keyOption, "{$optionTmplName}_general_settings");
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
        $uid = User::getParamUid(0);

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();

        $typeGroup = get_param('view');
        $isPage = Groups::isPage();

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $pageTitle = $isPage ? l('title_pages') : l('title_groups');
        $pageDescription = '';
        $pageClass = 'groups_list';

        $filter = $isPage ? 'pages_filters' : 'groups_filters';
        if ($uid) {
            if ($uid == $guid) {
                $pageTitle = $isPage ? l('your_pages') : l('your_groups');
            } else {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);

                $lKey = $isPage ? 'title_pages_someones' : 'title_groups_someones';
                $pageTitle = lSetVars($lKey, array('name' => $name));

                $lKey = $isPage ? 'pages_here_you_can_browse_the_users' : 'groups_here_you_can_browse_the_users';
                $pageDescription = l($lKey);
            }
            $pageClass .= ' groups_list_user';
        }

        $pageUrl = Groups::pageUrl($uid);

        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => $uid,
                      'page_guid'   => $guid,
                      'url_pages'   => $pageUrl,
                      'page_type'   => 'videos',
                      'page_filter' => intval(!$uid),
                      'type_group'  => $typeGroup
                );

        $pagerOnPage = GroupsList::getNumberItems();
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_groups';
        $class = "Template{$optionTmplName}";

        $typeOrderDefault = GroupsList::getTypeOrder();

        if (!$ajax && !$uid) {
            $tagId = get_param_int('tag');
            $tagInfo = Groups::getTagInfo($tagId);
            if ($tagInfo) {
                $_GET['tags'] = $tagInfo['tag'];
                //User::updateUserFilter($filter, array('tags'));
            }
            User::setUserFilterParam($filter, $typeOrderDefault);
        }

        GroupsList::$isGetDataWithFilter = true;
        $itemsTotal = GroupsList::getTotalGroups($uid);
        GroupsList::$isGetDataWithFilter = false;

        $typeOrder = get_param('type_order', $typeOrderDefault);

        if (!$ajax) {
            $vars['tags'] = toAttr(get_param('tags'));
            $vars['search_query'] = get_param('search_query');
            $vars['type_order'] = $typeOrder;
            $vars['filter_order_list_options'] = h_options(GroupsList::getTypeOrderList(true), $typeOrder);

            $html->assign('', $vars);

            if ($guid) {
                if (!$uid || $uid == $guid) {
                    if ($isPage) {
                        $html->setvar('groups_add_url', Common::pageUrl('page_add'));
                    } else {
                        $html->setvar('groups_add_url', Common::pageUrl('group_add'));
                    }
                    $html->parse('page_link_groups_add', false);
                }
            }

            if (!$uid) {
                if ($isPage) {
                    $linkVars = array(
                        'my_url' => Common::pageUrl('user_pages_list'),
                        'my_title' => l('my_pages'),
                        'my_icon' => ListBlocksOrder::getIconSvg('file_picture'),

                        'videos_url' => Common::pageUrl('pages_vids_list'),
                        'videos_icon' => ListBlocksOrder::getIconSvg('film'),

                        'photos_url' => Common::pageUrl('pages_photos_list'),
                        'photos_icon' => ListBlocksOrder::getIconSvg('picture'),

                        'songs_url' => Common::pageUrl('pages_songs_list'),
                        'songs_icon' => ListBlocksOrder::getIconSvg('audio_group'),
                    );
                } else {
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
                }
                $html->assign('page_link_groups', $linkVars);

                if ($guid) {
                    if (GroupsList::getTotalGroupsFromUser($guid, $isPage)) {
                        $html->parse('page_link_my_groups_user', false);
                    }
                    $html->parse('page_link_my_groups_delimiter', false);
                }

                $html->parse('page_link_my_groups', false);

                $html->setvar('page_link_groups_search_title', l('search'));
                $html->parse('group_sarch_advanced', false);


                $html->parse('page_link_delimiter', false);

                $html->parse('page_search_query', false);
                $html->parse('page_filter_no_result', false);
                $html->parse('page_filter', false);
            }

            if (!$uid || $uid == $guid) {
                $html->parse('wrap_head_links', false);
            }
            // TemplateEdge::parseColumn($html, $uid ? $uid : $guid);
        } else {
            $html->setvar('num_total', $itemsTotal);
        }

        GroupsList::$isGetDataWithFilter = true;
        $rows = $class::parseListGroups($html, $typeOrder, $limit, $block);
        GroupsList::$isGetDataWithFilter = false;
        if ($rows) {
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl);
        } else {
            if ($isPage) {
                $pageNothingHereYet = lSetVars('page_nothing_here_yet_page', array('url' => Common::pageUrl('page_add')));
            } else {
                $pageNothingHereYet = lSetVars('page_nothing_here_yet_group', array('url' => Common::pageUrl('group_add')));
            }
            $html->setvar('page_nothing_here_yet', $pageNothingHereYet);

            $html->parse("list_noitems");
        }

        if ($guid && $ajax && !$uid) {
            User::updateUserFilter($filter);
        }
        parent::parseBlock($html);
    }
}

$uid = get_param_int('uid');
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = array('main'   => $dirTmpl . 'page_list.html',
                  'list'   => $dirTmpl . '_list_page_info.html',
                  'filter' => $dirTmpl . '_list_page_filter.html',
                  'items'  => $dirTmpl . '_list_page_items_groups.html',
                  'item'   => $dirTmpl . '_list_groups_item.html',
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