<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";

include('./_include/core/main_start.php');

if (!Common::isOptionActiveTemplate('live_enabled')) {
    Common::toHomePage();
}

$optionTmplName = Common::getTmplName();
$isAjaxRequest = get_param('ajax');
if ($optionTmplName == 'edge') {
	$hideFromGuests = Common::isOptionActive('list_live_hide_from_guests', "{$optionTmplName}_general_settings");
} else {
	$hideFromGuests = true;
}

if (!guid() && !$isAjaxRequest && $hideFromGuests) {
    Common::toLoginPage();
}

if (!$isAjaxRequest) {
	LiveStreaming::checkAviableLiveStreaming();

    LiveStreaming::updateDemoData();
    LiveStreaming::deleteUserBrokenLive();
}

CustomPage::setSelectedMenuItemByTitle('column_narrow_live_list');


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
		$isEdge = $optionTmplName == 'edge';
        $uid = User::getParamUid(0);

        $page = get_param_int('page');
        $page = $page < 1 ? 1 : $page;

        $pageTitle = l('page_title');
        $pageDescription = '';
        $pageClass = 'videos_list';
        $pageUrl = Common::pageUrl('live_list');

        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => 0,
                      'page_guid' => $guid,
                      'url_pages' => $pageUrl,
                      'page_type' => 'lives',
                      'page_filter' => 1,
                      'group_id'    => 0,
                      'group_type'  => ''
                );


		if ($isEdge) {
			$pagerOnPage = Common::getOptionInt('list_live_number_items', "{$optionTmplName}_general_settings");
		} else {
			$pagerOnPage = Common::getOptionTemplateInt('list_live_number_items');
		}
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_live';
		$class = "Template{$optionTmplName}";
		if ($isEdge) {
			$typeOrderDefault = Common::getOption('list_live_type_order', "{$optionTmplName}_general_settings");
		} else {
			$class = "TemplateBase";
			$typeOrderDefault = 'new';
		}

        $filter = 'live_filters';
        if (!$ajax && $isEdge) {
            $tagId = get_param_int('tag');
            $tagInfo = CProfileVideo::getTagInfo($tagId);
            if ($tagInfo) {
                $_GET['tags'] = $tagInfo['tag'];
                //User::updateUserFilter($filter, array('tags'));
            }
            User::setUserFilterParam($filter, $typeOrderDefault);
            if (get_param_int('only_friends')) {
                $html->setvar('only_friends', 1);
                $html->parse('module_search_only_friends', false);
            }

            if (get_param_int('only_live')) {
                $html->setvar('only_live', 1);
                $html->parse('module_search_only_live', false);
            }
        }

        $online = true;
        if (!$guid) {
            $online = Common::isOptionActive('list_live_show_not_ended', 'edge_main_page_settings');
        }
        LiveStreaming::$isGetDataWithFilter = true;
        $itemsTotal = LiveStreaming::getTotalLive($online, 0);
        LiveStreaming::$isGetDataWithFilter = false;

        $typeOrder = get_param('type_order', $typeOrderDefault);

        if ($ajax) {
			$html->setvar('num_total', $itemsTotal);
        } else {
			if ($isEdge) {
				$vars['tags'] = toAttr(get_param('tags'));
				$vars['search_query'] = get_param('search_query');
				$vars['type_order'] = $typeOrder;
				$vars['filter_order_list_options'] = h_options(LiveStreaming::getTypeOrderList(true), $typeOrder);
			}

            $html->assign('', $vars);

            $html->parse('page_filter_tags_hide', false);

            if ($guid) {
                $html->parse('page_filter_only_friends', false);
            }
            if (!$uid) {
                //$html->parse('page_search_query', false);
                $html->parse('page_filter_no_result', false);
                $html->parse('page_filter', false);
            }

			$html->parse('page_title_circle', false);

            $html->parse('wrap_head_links', false);
            // TemplateEdge::parseColumn($html, $guid);
        }

        LiveStreaming::$isGetDataWithFilter = true;
        $rows = $class::parseListLive($html, $typeOrder, $limit, $block, $online);
        LiveStreaming::$isGetDataWithFilter = false;
        if ($rows) {
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl);
        } else {
            $html->parse("list_noitems");
        }

        if ($guid && $ajax) {
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
                  'item'   => $dirTmpl . '_list_live_item.html',
                  'pages'  => $dirTmpl . '_list_page_pages.html');

if ($isAjaxRequest) {
    $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
    unset($tmplList['filter']);
} elseif (TemplateEdge::isTemplateColums()) {
    $tmplList['list'] = $dirTmpl . '_list_page_info_columns.html';
    $tmplList['profile_column_left'] = $dirTmpl . '_profile_column_left.html';
    $tmplList['profile_column_right'] = $dirTmpl . '_profile_column_right.html';
} else {
	if (Common::isOptionActiveTemplate('live_list_filter_disabled')) {//Impact
		unset($tmplList['filter']);
	}
}

$page = new CPage("", $tmplList);

if($isAjaxRequest) {
    getResponsePageAjaxByAuthStop($page, $hideFromGuests ? guid() : 1);
}

if (Common::isParseModule('profile_colum_narrow')){
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include('./_include/core/main_close.php');