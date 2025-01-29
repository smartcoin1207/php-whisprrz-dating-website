<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

if (!Common::isOptionActiveTemplate('live_enabled')) {
    Common::toHomePage();
}

$optionTmplName = Common::getTmplName();
$noEdge = $optionTmplName != 'edge';
$isOnlyLive = get_param_int('only_live');
if (($optionTmplName == 'impact' || $optionTmplName == 'urban') && $isOnlyLive) {
	$noEdge = false;
}
if ($noEdge) {
    Common::toHomePage();
}

$isAjaxRequest = get_param('ajax');
if (!$isAjaxRequest && $p == 'live_list_finished.php') {
	LiveStreaming::checkAviableLiveStreaming();
}
if ($optionTmplName == 'edge') {
	$hideFromGuests = Common::isOptionActive('list_videos_hide_from_guests', "{$optionTmplName}_general_settings");
} else {
	$hideFromGuests = true;
}

if (!guid() && !$isAjaxRequest) {
    $uid = User::getParamUid(0);
    if ($hideFromGuests || $uid) {
        Common::toLoginPage();
    }
}

/* Divyesh - Added on 11-04-2024 */
$offset = get_param('offset');
$offset = empty($offset) ? 1 : $offset;
$uid = User::getParamUid(0);

$is_private_video_access = User::checkPhotoTabAccess('invited_private_vids', $uid);

if ($uid != $g_user['user_id'] && ($offset == 2 && !$is_private_video_access)) {
    $uname = User::getInfoBasic($uid, 'name_seo');
    redirect("{$uname}/vids");
}
/* Divyesh - Added on 11-04-2024 */

Groups::checkAccessGroup();
User::accessCheckToProfile(true);

if ($isOnlyLive) {
	CustomPage::setSelectedMenuItemByTitle('column_narrow_live_list_finished');
}

class CPage extends CHtmlBlock
{
    function init()
    {

    }

    function parseBlock(&$html)
    {
		global $p;
        global $g_user, $is_private_video_access;

        /* Divyesh - added on 11-04-2024 */
        $offset = get_param('offset');
        $offset = empty($offset) ? '1' : $offset;
        $tab = "public";
        $active_tab_1 = $active_tab_2 = '';
        if ($offset == 2) {
            $tab = "private";
            $active_tab_2 = 'active';
        } else {
            $active_tab_1 = 'active';
        }
        /* Divyesh - added on 11-04-2024 */

        $guid = guid();

		$isPageLiveFinished = $p == 'live_list_finished.php';
		if ($isPageLiveFinished) {
			$html->setvar('guid', guid());
		}

        $ajax = get_param('ajax');
        $optionTmplName = Common::getTmplName();
		$isEdge = $optionTmplName == 'edge';

        $uid = User::getParamUid(0);


        $groupsPhotoList = Groups::getParamTypeContentList();
        $isPagesPhotosList = $groupsPhotoList == 'group_page';
        if ($groupsPhotoList) {
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
        $pageClass = 'videos_list';

        if ($uid) {
            if ($groupId) {
                $pageTitle = lSetVars('group_page_title_someones', array('name' => $groupInfo['title']));
            } elseif ($uid == $guid) {
                if ($groupsPhotoList){
                    $pageTitle = $isPagesPhotosList ? l('videos_your_pages') : l('videos_your_groups');
                } else {
                    $pageTitle = l('your_videos');
                }
            } else {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);
                if ($groupsPhotoList) {
                    $pageTitle = lSetVars('group_page_title_someones', array('name' => $name));
                } else {
                    $pageTitle = lSetVars('page_title_someones', array('name' => $name));
                }
                $pageDescription = l('here_you_can_browse_the_users_videos');
            }
            $pageClass .= ' videos_list_user';
        } elseif($groupsPhotoList) {
            $pageTitle = $isPagesPhotosList ? l('page_title_pages') : l('page_title_groups');
        }

		if ($isPageLiveFinished) {
			$pageUrl = Common::pageUrl('live_list_finished');
		} else {
			$pageUrl = Common::pageUrl('vids_list');
		}
        if ($uid) {
            if ($groupsPhotoList){
                $pageUrl = $isPagesPhotosList ? Common::pageUrl('user_my_pages_vids_list', $guid) : Common::pageUrl('user_my_groups_vids_list', $uid);
            } elseif ($groupId) {
                $pageUrl = Common::pageUrl('group_vids_list', $groupId);
            } else {
                $pageUrl = Common::pageUrl('user_vids_list', $uid);
            }
        } elseif ($groupsPhotoList){
            $pageUrl = $isPagesPhotosList ? Common::pageUrl('pages_vids_list') : Common::pageUrl('groups_vids_list');
        }

        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => $uid,
                      'page_guid' => $guid,
                      'url_pages' => $pageUrl,
                      'page_type' => 'videos',
                      'page_filter' => intval(!$uid),
                      'group_id'    => $groupId,
                      'group_type'  => $groupsPhotoList ? $groupsPhotoList : ''
                );

		if ($isEdge) {
			$pagerOnPage = Common::getOptionInt('list_videos_number_items', "{$optionTmplName}_general_settings");
		} else {
			$pagerOnPage = Common::getOptionTemplateInt('list_live_number_items');
		}
        
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');

        $limit = (($page - 1) * $pagerOnPage) . ',' . $pagerOnPage;

        $block = 'list_video';
        $class = "Template{$optionTmplName}";
		if ($isEdge) {
			$typeOrderDefault = Common::getOption('list_videos_type_order', "{$optionTmplName}_general_settings");
		} else {
			$class = "TemplateBase";
			$typeOrderDefault = 'new';
		}

        $filter = 'videos_filters';

        $filter = CProfilePhoto::getNameFilter('videos');
        if (!$ajax && !$uid && $isEdge) {
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

        $html->setvar('url_page_user_videos_list', $pageUrl);
        /* Divyesh - Added on 11-04-2024 */
        if ($uid === $g_user['user_id'] || User::isFriend($uid, $g_user['user_id'])) {

            $html->setvar('active_tab_1', $active_tab_1);
            $html->setvar('active_tab_2', $active_tab_2);
            
            // Check Access "true"
            
            if ($is_private_video_access || $uid == $g_user['user_id'])
                $html->parse('show_private_tabs', false);    
            
            $html->parse('show_video_tabs', false);
        }
        if ($uid == $g_user['user_id']){
            $html->parse('show_video_update_function', false);
        }
        /* Divyesh - Added on 11-04-2024 */

        CProfileVideo::$isGetDataWithFilter = true;
        $itemsTotal = CProfileVideo::getTotalVideos($uid, $groupId, true, $tab); // Divyesh - Added on 11-04-2024
        CProfileVideo::$isGetDataWithFilter = false;

        $typeOrder = get_param('type_order', $typeOrderDefault);

        if (!$ajax) {
			if ($html->varExists('auto_play_video')) {
				$html->setvar('auto_play_video', Common::isOptionActive('video_autoplay')?'autoplay':'');
			}
			if ($isEdge) {
				$vars['tags'] = toAttr(get_param('tags'));
				$vars['search_query'] = get_param('search_query');
				$vars['type_order'] = $typeOrder;
				$vars['filter_order_list_options'] = h_options(CProfileVideo::getTypeOrderVideosList(true), $typeOrder);
			}

            $html->assign('', $vars);

            if ($guid) {
                if ($groupsPhotoList) {
                    if ($uid) {
                        $blockLink = 'page_link_groups_videos';
                        $urlLink = $isPagesPhotosList ? Common::pageUrl('pages_vids_list') : Common::pageUrl('groups_vids_list');
                        $urlTitle = $isPagesPhotosList ? l('link_pages_videos') : l('link_groups_videos');
                    } else {
                        $urlTitle = '';
                        $blockLink = 'page_link_my_groups_videos';
                        $urlLink = $isPagesPhotosList ? Common::pageUrl('user_my_pages_vids_list', $guid) : Common::pageUrl('user_my_groups_vids_list', $guid);
                    }
                    $html->setvar("{$blockLink}_url", $urlLink);
                    if ($urlTitle) {
                        $html->setvar("{$blockLink}_title", $urlTitle);
                    }

                    $html->parse($blockLink, false);
                } else {
                    if (!$isPageLiveFinished && (!$uid || $uid == $guid)) {
                        $html->parse('page_link_video_upload', false);
                        if (!$uid) {
                            $html->parse('page_link_my_video', false);
                        }
                    }
                    $html->parse('page_filter_only_friends', false);
					if (!$isPageLiveFinished) {
						$html->parse('page_filter_only_live', false);
					}
                }
            }
            if (!$uid) {
                if ($guid && !$groupsPhotoList && !$isPageLiveFinished) {
                    $html->parse('page_link_delimiter', false);
                }
                $html->parse('page_search_query', false);
                $html->parse('page_filter_no_result', false);
                $html->parse('page_filter', false);
            }

            $show = get_param('show');
            if ($show == 'video_gallery') {
                $html->setvar('show_video_id', get_param_int('video_id'));
                $html->setvar('show_comment_id', get_param_int('cid'));
                $html->parse('show_video_gallery_js', false);
            }

            if (!$uid || $uid == $guid) {
                $html->parse('wrap_head_links', false);
            }
            // TemplateEdge::parseColumn($html, $uid ? $uid : $guid);
        } else {
            $html->setvar('num_total', $itemsTotal);
        }

        CProfileVideo::$isGetDataWithFilter = true;

        $rows = $class::parseListTabVideos($html, $typeOrder, $tab, $limit, $block, $groupId, true); // Divyesh - Added on 11-04-2024

        CProfileVideo::$isGetDataWithFilter = false;
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
                  'item'   => $dirTmpl . '_list_vids_item.html',
                  'pages'  => $dirTmpl . '_list_page_pages.html');
if ($uid) {
	unset($tmplList['filter']);
}

if ($isAjaxRequest) {
    $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
    unset($tmplList['filter']);
} elseif (TemplateEdge::isTemplateColums()) {
    $tmplList['list'] = $dirTmpl . '_list_video_info_columns.html';
    $tmplList['profile_column_left'] = $dirTmpl . '_profile_column_left.html';
    $tmplList['profile_column_right'] = $dirTmpl . '_profile_column_right.html';
} else {
	if (Common::isOptionActiveTemplate('live_list_filter_disabled')) {//Impact
		unset($tmplList['filter']);
		$tmplList['photos_init'] = $dirTmpl . '_profile_photos_init.html';
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