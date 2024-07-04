<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include "./_include/core/main_start.php";

class CGroupsInvite extends CHtmlBlock
{
    public $m_group;

    public function action()
    {
        global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd', '');

        if($cmd == 'save') {
            $group_mail = get_param('group_mail', '');
            $group_invite= get_param('group_invite', '');
            $group_leave = get_param('group_leave', '');
            $group_block  = get_param('group_block', '');
            $group_moderator  = get_param('group_moderator', '');

            $group_id = Groups::getParamId();
            $user_id = get_param('user_id', '');

            $options = array(
                'group_mail' => $group_mail,
                'group_invite' => $group_invite,
                'group_leave' => $group_leave,
                'group_block' => $group_block,
                'group_moderator' => $group_moderator
            );

            $options_encode = json_encode($options);
            $vars = array(
                'moderator_options' => $options_encode
            );

            // var_dump($group_mail, $group_invite, $group_invite, $group_block); die();
            DB::update('groups_social_subscribers', $vars, '`user_id` = ' . to_sql($user_id, 'Text') . ' AND `group_id` = ' . to_sql($group_id, 'Text') . '');
        }       
    }

    public function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $group_id = Groups::getParamId();

        if(!$group_id) {
            Common::toHomePage();
        }

        $user_id = get_param('user_id', '');
        $group_nameseo = self::getNameSeo();

        if (!self::isModerationAllowed() || !$user_id) {
            $group_url = $g['path']['url_main'] . $group_nameseo;
            redirect($group_url);
        }

        $usersql = "SELECT * FROM user WHERE user_id = " . to_sql($user_id, 'Text');
        $user = DB::row($usersql);
        if(!$user) {
            $group_url = $g['path']['url_main'] . $group_nameseo;
            redirect($group_url);
        }

        $username = $user['name'];
        $html->setvar('username', $username);

        $vars = array('username' => $username);
                $html->setvar('title_current', lSetVars(l('title_current'), $vars));

        //get moderator options from groups_social_subscribers table.
        $sql = "SELECT * FROM groups_social_subscribers WHERE group_id = " . to_sql($group_id, 'Text') . " AND user_id = " . to_sql($user_id, 'Text');
        $group = DB::row($sql);
        $group_moderator = $group['moderator_options'];

        $moderator_options = json_decode($group_moderator, true) ;

        $moderators = array(
            'group_mail' => array('name' => 'group_mail', 'label' => l('group_mail'), 'checked' => (isset($moderator_options['group_mail']) && $moderator_options['group_mail']) ? 'checked': ''),
            'group_invite' => array('name' => 'group_invite', 'label' => l('group_invite'), 'checked' => (isset($moderator_options['group_invite']) && $moderator_options['group_invite']) ? 'checked': ''),
            'group_leave' => array('name' => 'group_leave', 'label' => l('group_leave'), 'checked' => (isset($moderator_options['group_leave']) && $moderator_options['group_leave']) ? 'checked': ''),
            'group_block' => array('name' => 'group_block', 'label' => l('group_block'), 'checked' => (isset($moderator_options['group_block']) && $moderator_options['group_block']) ? 'checked': ''),
            'group_moderator' => array('name' => 'group_moderator', 'label' => l('group_moderator'), 'checked' => (isset($moderator_options['group_moderator']) && $moderator_options['group_moderator']) ? 'checked': '')
        );

        foreach ($moderators as $key => $value) {
            $html->setvar('moderator_name', $key);
            $html->setvar('moderator_label', $value['label']);
            $html->setvar('checked', $value['checked']);

            $html->parse('group_moderator_item', true);
        }

        $html->parse('group_moderator', false);


        parent::parseBlock($html);
    }

    public function getNameSeo()
    {

        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }

        $group_nameseo = $group['name_seo'];

        return $group_nameseo;
    }

    public function isModerationAllowed()
    {
        global $g_user, $g;

        $groupId = Groups::getParamId();
        
        $sql = "SELECT  * FROM groups_social_subscribers WHERE group_id = " . to_sql($groupId, 'Text') . "AND user_id = " . to_sql(guid(), 'Text');
        $my_subscriber  = DB::row($sql);
        $moderator_options = json_decode($my_subscriber['moderator_options'], true);

        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }


        if (guid() != $group['user_id'] && !$moderator_options['group_moderator']) {
            return false;
        } else {
            return true;
        }
    }


    // public function isOwner()
    // {
    //     global $g_user, $g;

    //     $groupId = Groups::getParamId();
    //     $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
    //     $group = DB::row($gsql);
    //     if (!$group) {
    //         Common::toHomePage();
    //     }

    //     if ($g_user['user_id'] != $group['user_id']) {
    //         return false;
    //     } else {
    //         return true;
    //     }
    // }
}

$page = new CGroupsInvite("", $g['tmpl']['dir_tmpl_main'] . "group_moderator.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include "./_include/core/main_close.php";
