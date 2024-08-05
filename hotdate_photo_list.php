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
        $hotdate_id = get_param('hotdate_id', '');

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $pageTitle = l('page_title');
        $pageDescription = '';
        $pageClass = 'photos_list';

        if ($uid) {
            if ($uid == $guid) {
             
                    $pageTitle = l('your_photos');
            } else {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);
                $pageTitle = lSetVars('page_title_someones', array('name' => $name));
                $pageDescription = l('here_you_can_browse_the_users_photos');
            }
            $pageClass .= ' photos_list_user';
        }

        $pageTitle = l('hotdate_photo_list');

        $user_id = get_param('user_id', '');
        if($user_id) {
            $pageUrl = Common::pageUrl('hotdate_photo', $hotdate_id) . "&user_id=" . $user_id;
        } else {
            $pageUrl = Common::pageUrl('hotdate_photo', $hotdate_id);
        }

        $page_userinfo = User::getInfoBasic($user_id);
        if($page_userinfo) {
            if($user_id == guid()) {
                $pageTitle = "My " . $pageTitle;
            } else {
                $pageTitle = $page_userinfo['name'] . " " . $pageTitle;
            }
        } else {
            $pageUrl = Common::pageUrl('hotdate_photo', $hotdate_id);
        }

        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => $uid,
                      'page_guid' => $guid,
                      'url_pages' => $pageUrl,
                      'page_type' => 'photos',
                      'page_filter' => intval(!$uid)
        );

        $pagerOnPage = Common::getOptionInt('list_photos_number_items', "{$optionTmplName}_general_settings");
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_photos';
        $class = "Template{$optionTmplName}";

        $typeOrderDefault = Common::getOption('list_photos_type_order', "{$optionTmplName}_general_settings");

        $filter = CProfilePhoto::getNameFilter();
        if (!$ajax) {
            if ($uid) {

            } else {
                $tagId = get_param_int('tag');
                $tagInfo = CProfilePhoto::getTagInfo($tagId);
                if ($tagInfo) {
                    $_GET['tags'] = $tagInfo['tag'];
                    User::updateUserFilter($filter, array('tags'));
                }
                User::setUserFilterParam($filter, $typeOrderDefault);
                if (get_param_int('only_friends')) {
                    $html->setvar('only_friends', 1);
                    $html->parse('module_search_only_friends', false);
                }
            }
        }

        $itemsTotal = ChotdatesTools::totalHotdateImages($hotdate_id);
        $typeOrder = get_param('type_order', $typeOrderDefault);
        $itemsTotal = CProfilePhoto::PhotoListCountEHP($user_id);

        if (!$ajax) {
            $vars['tags'] = toAttr(get_param('tags'));
            $vars['search_query'] = get_param('search_query');
            $vars['type_order'] = $typeOrder;
            $vars['filter_order_list_options'] = h_options(CProfilePhoto::getTypeOrderPhotosList(true), $typeOrder);
            $html->assign('', $vars);

            if ($guid) {
                    if (!$uid || $uid == $guid) {
                        $html->parse('page_link_photo_upload', false);
                        if (!$uid) {
                            $pageUrlMy = Common::pageUrl('hotdate_photo', $hotdate_id) . "&user_id=" . guid();
                            $html->setvar('url_page_user_photos_list' , $pageUrlMy);
                            $html->parse('page_link_my_photo', false);
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
            $show = get_param('show');
            if ($show == 'gallery') {
                $html->setvar('show_photo_id', get_param_int('photo_id'));
                $html->setvar('show_comment_id', get_param_int('cid'));
                $html->parse('show_gallery_js', false);
            }

            if (!$uid || $uid == $guid) {
                $html->parse('wrap_head_links', false);
            }
            // TemplateEdge::parseColumn($html, $uid ? $uid : $guid);
            CBanner::getBlock($html, 'right_column');

        } else {
            $html->setvar('num_total', $itemsTotal);
        }
        
        $rows = $class::parseListPhotos($html, $typeOrder, $limit, '', true, $user_id);

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

$uid = get_param_int('uid');
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = array('main'   => $dirTmpl . 'page_list.html',
                  'list'   => $dirTmpl . '_list_page_info.html',
                  'filter' => $dirTmpl . '_list_page_filter.html',
                  'items'  => $dirTmpl . '_list_page_items.html',
                  'item'   => $dirTmpl . '_list_photos_item.html',
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