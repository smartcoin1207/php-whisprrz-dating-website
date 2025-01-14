<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

$siteGuid = get_param('site_guid', false);
if ($siteGuid !== false && $siteGuid != guid()) {
    echo getResponseAjaxByAuth(false);
    die();
}

global $g;

class WallShare extends CHtmlBlock
{
    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;
        
        $siteGuid = get_param('site_guid', false);
        $wall_id = get_param('id', '');
        $view  = get_param('view', '');
        $uid = get_param('wall_uid', '');
        $groupId = get_param('group_id', '');

        $share_wall = DB::row("SELECT * FROM wall WHERE id =" . to_sql($wall_id, 'Text'));
        if(!(isset($share_wall['shareable']) && $share_wall['shareable'] == '0') && $share_wall['user_id'] != guid()) {
            $html->parse('wall_share_not_shareable', false);
        } else {
            //group own
            $sql_own = "SELECT * FROM `groups_social` as g WHERE g.user_id = " . to_sql(guid(), 'Text') . " AND g.group_id != " . to_sql($groupId, 'Text');
            $myOwnGroups = DB::rows($sql_own);

            //belong
            $sql_belong = "SELECT * FROM `groups_social` as g LEFT JOIN `groups_social_subscribers` as gs ON g.group_id = gs.group_id WHERE gs.user_id = " . to_sql(guid(), 'Text')  . " AND gs.group_user_id != " . to_sql(guid(), 'Text') . " AND g.group_id != " . to_sql($groupId, 'Text');
            $belongGroups = DB::rows($sql_belong);

            $sizePhoto = 'm';

            $groups = [];

            foreach ($myOwnGroups as $key => $value) {
                $groups[] = $value;
            }

            foreach ($belongGroups as $key => $value) {
                $groups[] = $value;
            }

            if(1==1 || $groupId != '') {
                $html->parse('wall_share_public', false);
            }

            foreach ($groups as $key => $group) {
                $userPhoto = GroupsPhoto::getPhotoDefault(guid(), $group['group_id'], $sizePhoto);

                $html->setvar('group_id', $group['group_id']);
                $html->setvar('group_photo', $userPhoto);
                $html->setvar('group_title', $group['title']);
                $html->parse('wall_share_group_item', true);
            }

            $html->parse('wall_share_items', false);
        }

        parent::parseBlock($html);
    }
}

$dirTmpl = $g['tmpl']['dir_tmpl_main'];

$tmpl = "{$dirTmpl}_wall_share_item.html";

$responsePage = new WallShare('', $tmpl);

if (isset($responsePage)) {
    echo getResponsePageAjaxByAuth(true, $responsePage);
}

// include('./_include/core/main_close.php');