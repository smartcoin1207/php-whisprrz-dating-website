<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CPhotoList extends CHtmlBlock
{
    function init()
    {
    }

    function action()
    {
        $cmd = get_param("cmd", "");
        $photo_id = get_param("id", 0);

        if ($cmd == "main")	{
			User::photoToDefault($photo_id);
			redirect();
		}

        if($cmd == 'private' || $cmd == 'public') {
            CProfilePhoto::setPhotoPrivate($photo_id);
            redirect();
        }

		/* Divyesh - Added on 11-04-2024 */
		if ($cmd == 'personal' || $cmd == 'remove_personal') {
			CProfilePhoto::setPhotoPersonal($photo_id);
			redirect();
		}
		if ($cmd == 'move_to_folder' || $cmd == 'remove_from_folder') {
			CProfilePhoto::setPhotoCustomFolder($photo_id);
			redirect();
		}
    }

    function parseBlock(&$html)
    {
        $guid = guid();
        $ajax = get_param('ajax');
        $groupId = Groups::getParamId();
        $profile_photo = get_param("profile_photo", '');

        $page_offset = "";

        if(!$groupId) {
            global $g_user, $is_folder_photo_access, $is_private_photo_access, $is_personal_photo_access;

            /* Divyesh - added on 11-04-2024 */
            $offset = get_param('offset');
            $offset = empty($offset) ? '1' : $offset;
            $tab = "public";
            $active_tab_1 = $active_tab_2 = $active_tab_3 = $active_tab_4 = '';
            if ($offset == 2) {
                $tab = "private";
                $active_tab_2 = 'active';
            } else if ($offset == 3) {
                $tab = "personal";
                $active_tab_3 = 'active';
            } else if ($offset == 4) {
                $tab = 'folder';
                $active_tab_4 = 'active';
            } else {
                $active_tab_1 = 'active';
            }

            if($offset == 2 || $offset == 3 || $offset == 4) {
                $page_offset = "?offset=" . $offset;
            } else {
                $page_offset = "";
            }
            /* Divyesh - added on 11-04-2024 */
        }

        $optionTmplName = Common::getTmplName();
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
        $pageClass = 'photos_list';

        if ($uid) {
            if ($groupId) {
                $pageTitle = lSetVars('page_title_someones', array('name' => $groupInfo['title']));
            } elseif ($uid == $guid) {
                if ($groupsPhotoList){
                    $pageTitle = $isPagesPhotosList ? l('photos_your_pages') : l('photos_your_groups');
                } else {
                    $pageTitle = l('your_photos');
                }
            } else {
                $name = User::getInfoBasic($uid, 'name');
                $name = User::nameShort($name);
                $pageTitle = lSetVars('page_title_someones', array('name' => $name));
                $pageDescription = l('here_you_can_browse_the_users_photos');
            }
            $pageClass .= ' photos_list_user';
        } elseif($groupsPhotoList) {
            $pageTitle = $isPagesPhotosList ? l('page_title_pages') : l('page_title_groups');
        }

        $pageUrl = Common::pageUrl('photos_list');
        if ($uid) {
            if ($groupsPhotoList) {
                $pageUrl = $isPagesPhotosList ? Common::pageUrl('user_my_pages_photos_list', $guid) : Common::pageUrl('user_my_groups_photos_list', $guid);
            } elseif ($groupId) {
                $pageUrl = Common::pageUrl('group_photos_list', $groupId);
            } else {
                $pageUrl = Common::pageUrl('user_photos_list', $uid);
            }

        } elseif ($groupsPhotoList) {
            $pageUrl = $isPagesPhotosList ? Common::pageUrl('pages_photos_list') : Common::pageUrl('groups_photos_list');
        }

        if($profile_photo == 1) {
            $pageUrl = Common::pageUrl("profile_photo_list");
        }        

        $vars = array(
            'page_number' => $page,
            'page_class'  => $pageClass,
            'page_title'  => $pageTitle,
            'page_description' => $pageDescription,
            'page_user_id'     => $uid,
            'page_guid' => $guid,
            'url_pages' => $pageUrl,
            'page_type' => 'photos',
            'page_filter' => intval(!$uid),
            'group_id'    => $groupId,
            'group_type'  => $groupsPhotoList ? $groupsPhotoList : '',
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
                    //User::updateUserFilter($filter, array('tags'));
                }
                User::setUserFilterParam($filter, $typeOrderDefault);
                if (get_param_int('only_friends')) {
                    $html->setvar('only_friends', 1);
                    $html->parse('module_search_only_friends', false);
                }
            }
        }

        if(!$groupId) {
            /* Divyesh - Added on 11-04-2024 */
            $html->setvar('active_tab_1', $active_tab_1);
            $html->setvar('active_tab_2', $active_tab_2);
            $html->setvar('active_tab_3', $active_tab_3);
            $html->setvar('active_tab_4', $active_tab_4);
            if ($uid === $g_user['user_id'] || User::isFriend($uid, $g_user['user_id'])) {
                $html->setvar('custom_folder', User::getInfoBasic($uid, 'custom_folder'));
                
                if($profile_photo == 1) {
                    $html->setvar('url_page_user_photos_list', $pageUrl);
                }
                
                if ($is_private_photo_access || $uid == $g_user['user_id'])
                    $html->parse('show_private_tab', false);    
                
                if ($is_personal_photo_access || $uid == $g_user['user_id'])
                    $html->parse('show_personal_tab', false);

                if ($is_folder_photo_access || $uid == $g_user['user_id'])
                    $html->parse('show_folder_tab', false);

                if (!empty($uid)){
                    $html->parse('show_myphoto_tabs', false);
                } 
            } else {
                /* Divyesh - Added on 20042024 */
                $pageUrlMy = Common::pageUrl('photos');
                $html->setvar('url_page_photos_list' , $pageUrlMy);
                $html->parse('show_all_tabs', false);
                /* Divyesh - Added on 20042024 */
            }
            /* Divyesh - Added on 11-04-2024 */
        }
        
        CProfilePhoto::$isGetDataWithFilter = true;
        /* Divyesh - Added on 20042024 */
        /**popcorn modified 2024-05-17 */
        if($groupId) {
            $itemsTotal = CProfilePhoto::getTotalPhotos($uid, false, $groupId);
        /**popcorn modified 2024-05-17 */

        } else {
            if ($uid) {
                $itemsTotal = CProfilePhoto::getTotalPhotos($uid, $tab, $groupId);
            }else{
                $itemsTotal = CProfilePhoto::getTotalAccessedPhotos($uid, $tab, $groupId);
            }
        }
        
        /* Divyesh - Added on 20042024 */

        CProfilePhoto::$isGetDataWithFilter = false;

        $typeOrder = get_param('type_order', $typeOrderDefault);

        if (!$ajax) {
            $vars['tags'] = toAttr(get_param('tags'));
            $vars['search_query'] = get_param('search_query');
            $vars['type_order'] = $typeOrder;
            $vars['filter_order_list_options'] = h_options(CProfilePhoto::getTypeOrderPhotosList(true), $typeOrder);

            $html->assign('', $vars);

            if ($guid) {
                if ($groupsPhotoList) {
                    if ($uid) {
                        $blockLink = 'page_link_groups_photos';
                        $urlLink = $isPagesPhotosList ? Common::pageUrl('pages_photos_list') : Common::pageUrl('groups_photos_list');
                        $urlTitle = $isPagesPhotosList ? l('menu_pages_photos_edge') : l('menu_groups_photos_edge');
                    } else {
                        $urlTitle = '';
                        $blockLink = 'page_link_my_groups_photos';
                        $urlLink = $isPagesPhotosList ? Common::pageUrl('user_my_pages_photos_list', $guid) : Common::pageUrl('user_my_groups_photos_list', $guid);
                    }
                    $html->setvar("{$blockLink}_url", $urlLink);
                    if ($urlTitle) {
                        $html->setvar("{$blockLink}_title", $urlTitle);
                    }
                    $html->parse($blockLink, false);
                } else {
                    if (!$uid || $uid == $guid) {
                        if($profile_photo == 1) {
                            $html->parse('page_link_custom_folder', false);
                            $html->parse('page_link_add_custom_folder', false);
                        }

                        $html->parse('page_link_photo_upload', false);
                        if (!$uid) {
                            $html->parse('page_link_my_photo', false);
                        }
                    }
                    $html->parse('page_filter_only_friends', false);
                }
            }
            if (!$uid) {
                if ($guid && !$groupsPhotoList) {
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
        } else {
            $html->setvar('num_total', $itemsTotal);
        }

        CProfilePhoto::$isGetDataWithFilter = true;
        if($groupId) {
            $rows = $class::parseListPhotos($html, $typeOrder, $limit, $groupId, true);
        } else {
            /* Divyesh - Added on 20042024 */
            if ($uid) {
                $public_rows = $class::parseListTabPhotos($html, $typeOrder, $tab, $limit, $groupId, true); // Divyesh - Added on 11-04-2024
            } else{
                $public_rows = $class::parseListTabsPhotos($html, $typeOrder, $tab, $limit, $groupId, true); // Divyesh - Added on 11-04-2024
            }
            /* Divyesh - Added on 20042024 */
        }

        CProfilePhoto::$isGetDataWithFilter = false;

        if ( $groupId ? $rows : $public_rows) {
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl, 'page', $page_offset );
        } else {
            $html->parse("list_noitems");
        }

        if ($guid && $ajax && !$uid) {
            User::updateUserFilter($filter);
        }
        parent::parseBlock($html);
    }
}