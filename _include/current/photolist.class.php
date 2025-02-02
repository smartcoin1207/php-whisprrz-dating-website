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

    }

    function parseBlock(&$html)
    {
        global $g_user;
        $guid = guid();
        $uid = User::getParamUid(0);
        $ajax = get_param('ajax');
        $groupId = Groups::getParamId();
        $profile_photo = get_param("profile_photo", '');
        $profile_photo_nsc_couple = get_param("profile_photo_nsc_couple", '');

        $folder_sql = "SELECT * FROM custom_folders WHERE user_id=" . to_sql($uid, "Number");
        $folders = DB::rows($folder_sql);

        $optionTmplName = Common::getTmplName();
        $groupsPhotoList = Groups::getParamTypeContentList();
        if ($groupsPhotoList) {
            $isPagesPhotosList = $groupsPhotoList == 'group_page';
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
        $page_offset = "";

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
                if ($isPagesPhotosList) {
                    $pageUrl = Common::pageUrl('user_my_pages_photos_list', $guid);
                } else {
                    if ($uid == $guid) {
                        $pageUrl = Common::pageUrl('user_my_groups_photos_list', $guid);
                    } else {
                        $pageUrl = Common::pageUrl('groups_photos_list', $uid);
                    }
                }
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
        } else if($profile_photo_nsc_couple == 1) {
            $pageUrl = Common::pageUrl("profile_photo_nsc_couple_list");
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

        /* popcorn modified 2024-10-08 */
        if(!$groupId) {
            global $g_user;
            
            $is_private_photo_access = User::checkPhotoTabAccess('invited_private', $uid);
            $is_personal_photo_access = User::checkPhotoTabAccess('invited_personal', $uid);

            /* Popcorn modified 2024-11-08 custom folders start */
            $offset = get_param('offset', '');
            $offset = empty($offset) ? '' : $offset;
            $page_offset = $offset ? "?offset=" . $offset : "";
            $tab = "public";

            if ($uid) {
                $active_tab_public = $active_tab_private = $active_tab_personal = '';

                if (is_numeric($offset) && $offset > 0) {
                    $tab = 'folder';
                } else if ($offset == 'private') {
                    $tab = "private";
                    $active_tab_private = 'active';
                } else if ($offset == 'personal') {
                    $tab = "personal";
                    $active_tab_personal = 'active';
                } else if(!$offset) {
                    $active_tab_public = 'active';
                }
                
                $html->setvar('active_tab_public', $active_tab_public);
                $html->setvar('active_tab_private', $active_tab_private);
                $html->setvar('active_tab_personal', $active_tab_personal);
                if($profile_photo == 1) {
                    $html->setvar('url_page_user_photos_list', $pageUrl);
                } else if($profile_photo_nsc_couple == 1) {
                    $html->setvar('url_page_user_photos_list', $pageUrl);
                }

                $html->setvar('url_page_user_photos_list', $pageUrl);
                
                if ($is_private_photo_access || $uid == $g_user['user_id'] || $uid == $g_user['nsc_couple_id'])
                    $html->parse('show_private_tab', false);    
                
                if ($is_personal_photo_access || $uid == $g_user['user_id'] || $uid == $g_user['nsc_couple_id'])
                    $html->parse('show_personal_tab', false);

                //custom child folders
                foreach ($folders as $key => $folder) {
                    $is_folder_access = User::checkPhotoTabAccess('invited_folder', $uid, $folder['id']);

                    if($is_folder_access || guid() == $uid || $uid == $g_user['nsc_couple_id']) {
                        $folder_offset = $folder['id'];
                        $custom_folder_name = $folder['name'];
                        $active_folder = $offset == $folder['id'] ? 'active' : '';
                        $html->setvar('active_folder', $active_folder);
                        $html->setvar('custom_folder_offset', $folder_offset);
                        $html->setvar('custom_folder_name', $custom_folder_name);
                        $html->parse('custom_child_folder_tab');
                    }
                }
                
                $html->parse('show_folder_tab', false);
                $html->clean('custom_child_folder_tab');

                if (!empty($uid)){
                    $html->parse('show_myphoto_tabs', false);
                }
            } else {
                $active_tab_public = $active_tab_private = $active_tab_personal = $active_tab_folder = '';

                if ($offset == 'custom_folders') {
                    $tab = 'folder';
                    $active_tab_folder = 'active';
                } else if ($offset == 'private') {
                    $tab = "private";
                    $active_tab_private = 'active';
                } else if ($offset == 'personal') {
                    $tab = "personal";
                    $active_tab_personal = 'active';
                } else if(!$offset) {
                    $active_tab_public = 'active';
                }

                $html->setvar('active_tab_public', $active_tab_public);
                $html->setvar('active_tab_private', $active_tab_private);
                $html->setvar('active_tab_personal', $active_tab_personal);
                $html->setvar('active_tab_folder', $active_tab_folder);
    
                /* Divyesh - Added on 20042024 */
                $html->setvar('url_page_photos_list' , $pageUrl);
                $html->parse('show_all_tabs', false);
                /* Divyesh - Added on 20042024 */
            }
            /* Popcorn modified 2024-11-08 custom folders start */
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
                    if (!$uid || $uid == $guid || $uid == $g_user['nsc_couple_id']) {
                        if($profile_photo == 1 || $profile_photo_nsc_couple == 1) {
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

            if (!$uid || $uid == $guid || $uid == $g_user['nsc_couple_id']) {
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