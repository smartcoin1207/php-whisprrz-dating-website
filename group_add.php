<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/groups/tools.php");

$cmd = get_param('cmd');
$groupId = get_param_int('group_id');
$guid = guid();

$pageAlias = 'user_groups_list';
$feature = 'create_group_paid';
if (Groups::isPage()) {
    $pageAlias = 'user_pages_list';
    $feature = 'create_page_paid';
}

// if (!User::accessCheckFeatureSuperPowers($feature) && $cmd != 'edit') {
//     redirect(Common::pageUrl('upgrade'));
// }

if ($cmd == 'edit') {
    $isRedirect = false;
    if ($groupId) {
        $groupInfo = Groups::getInfoBasic($groupId);
        if (!$groupInfo || $groupInfo['user_id'] != $guid) {
            $isRedirect = true;
        }
    } else {
        $isRedirect = true;
    }
    if ($isRedirect) {
        redirect(Common::pageUrl($pageAlias, $guid));
    }
}

class CGroups extends CHtmlBlock
{
    function action()
    {

    }

    function parseBlock(&$html)
    {
        global $g_user;

        $optionTmplName = Common::getTmplName();
        $guid = guid();
        $groupId = get_param_int('group_id');
        $groupType = 'group';
        $isGroupPage = Groups::isPage();
        if ($isGroupPage) {
            $groupType = 'page';
        }

        if ($groupId) {
            $vars = array(
                'page_title' => array('page' => l('page_edit'),
                                      'group' => l('group_edit'))
            );
        } else {
            $vars = array(
                'page_title' => array('page' => l('please_enter_the_page_details'),
                                      'group' => l('please_enter_the_group_details'))
            );
        }

        $html->setvar('init_is_page', intval($isGroupPage));

        $vars1['custom_settings_title'] = array('page' => l('page_custom_settings_title'),
                                           'group' => l('group_custom_settings_title')
                                     );
        foreach ($vars1 as $key => $value) {
            $html->setvar($key, $value[$groupType]);
        }


        $vars['field_photo_title'] = array('page' => l('page_profile_picture'),
                                           'group' => l('group_profile_picture')
                                     );
        foreach ($vars as $key => $value) {
            $html->setvar($key, $value[$groupType]);
        }

        $isPhotoBlDelete = true;
        $groupInfo = array();

        $protocol = Common::urlProtocol();
        $urlSite = str_replace($protocol . '://', '', Common::urlSiteSubfolders());

        if ($groupId) {
            $groupInfo = Groups::getInfoBasic($groupId);
            $groupInfo['tags'] = he(Groups::getTagsView($groupId));
            $groupInfo['photo_url'] = GroupsPhoto::getPhotoDefault($guid, $groupId, 'b');
            $photoId = GroupsPhoto::getPhotoDefault($guid, $groupId, 'b', true);
            $groupInfo['photo_id'] = $photoId;
            $groupInfo['photo_btn_upload'] = $photoId ? l('use_another') : l('choose_an_image');
            $groupInfo['cmd'] = 'group_update';
            $groupInfo['btn_create'] = l('save');
            $groupInfo['btn_class'] = $isGroupPage ? 'btn_publish_page' : 'btn_group_page';
            $groupInfo['url_site'] = $urlSite;
            $groupInfo['title'] = he($groupInfo['title']);

            if (!$isGroupPage) {
                $html->cond($groupInfo['private'] == 'Y', 'access_private', 'access_public');
            }
            $html->assign('group', $groupInfo);
            if ($photoId) {
                $isPhotoBlDelete = false;
            }
        } else {
            $vars = array(
                'cmd' => 'group_add',
                'photo_url' => "{$optionTmplName}_nophoto_group_b.png",
                'photo_btn_upload' => l('choose_an_image'),
                'btn_create' => $isGroupPage ? l('publish_page') : l('publish_group'),
                'btn_class' => $isGroupPage ? 'btn_publish_page' : 'btn_group_page',
                'url_site' => $urlSite
            );
            $html->assign('group', $vars);
        }
        $html->parse('bl_photo_update', false);

        if (!$isGroupPage) {
            $html->parse('bl_access', false);
        }

        if ($isPhotoBlDelete) {
            $html->parse('bl_photo_delete', false);
        }

        if (Common::isOptionActiveTemplate('join_location_allow_disabled')) {
            if (Common::isOptionActive('location_enabled', "{$optionTmplName}_join_page_settings")) {
                if ($groupId) {
                    $country = $groupInfo['country_id'];
                    $state   = $groupInfo['state_id'];
                    $city    = $groupInfo['city_id'];
                } else {
                    $country = $g_user['country_id'];
                    $state   = $g_user['state_id'];
                    $city    = $g_user['city_id'];
                }

                $html->setvar('country_options', Common::listCountries($country));
                $html->setvar('state_options', Common::listStates($country, $state));
                $html->setvar('city_options', Common::listCities($state, $city));

                $html->parse('location', false);
            }
        }
        if(true) {
            $sql = 'SELECT * FROM `groups_category`';
            $rows = DB::rows($sql);


            $groups_category = [];

            foreach ($rows as $key => $row) {
                $groups_category[$row['category_id']] = $row['category_title'];
            }

            $html->setvar('category_options', h_options($groups_category, isset($groupInfo['category_id']) ? $groupInfo['category_id'] : ''));
            $html->parse('bl_category', false);
        }

        $show_owner_checked = "";
        if(isset($groupInfo['show_owner']) && $groupInfo['show_owner']) {
            $show_owner_checked = "checked";    
        } else {
            $show_owner_checked = "";
        }

        $html->setvar('show_owner_checked', $show_owner_checked);

        TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
    }
}

$page = new CGroups("", getPageCustomTemplate('group_add.html', 'group_add_template'));
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");