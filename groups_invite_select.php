<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include "./_include/core/main_start.php";

function generate_group_key($gid)
{
    $key = md5($gid . time() . microtime() . md5(rand(100, 100000)));
    return $key;
}

class CGroupsInviteSelect extends CHtmlBlock
{
    public $m_group;

    public function action()
    {
        global $g_user;
        global $l;
        global $g;       
    }

    public function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $username = 'Tim';
        $vars = array('username' => $username);
        $html->setvar('title_current', lSetVars(l('title_current'), $vars));

        $sql = "SELECT * FROM groups_social WHERE user_id = " . to_sql(guid(), 'Text');
        $my_groups = DB::rows($sql);

        $sql1 = "SELECT * FROM groups_social_subscribers WHERE user_id = " . to_sql(guid(), 'Text') . " AND group_user_id != " . to_sql(guid(), 'Text') . "AND group_moderator = 'Y'" ;
        $other_groups = DB::rows($sql1);

        
        // var_dump($$other_groups); die();

        $uid = get_param('uid', '');

        if($my_groups || $other_groups) {
            foreach ($my_groups as $key => $group) {
                $group_name = $group['title'];
                $group_nameseo = $group['name_seo'];
                $group_description = $group['description'];
                $group_url = $g['path']['url_main'] . $group_nameseo . "/group_invite?user_id=" . $uid  ; 

                $html->setvar('group_url', $group_url);
                $html->setvar('group_name', $group_name);
                $html->setvar('group_description', $group_description);
                $html->setvar('group_owner', self::getUsername($group['user_id']));
                $html->parse('group_item', true);
            }

            foreach ($other_groups as $key => $value) {
                $group_id = $value['group_id'];
                $msql = "SELECT * FROM groups_social WHERE group_id = " . to_sql($group_id, 'Text');
                $group = DB::row($msql);
                if($group) {
                    $group_name = $group['title'];
                    $group_nameseo = $group['name_seo'];
                    $group_description = $group['description'];
                    $group_url = $g['path']['url_main'] . $group_nameseo . "/group_invite?user_id=" . $uid  ; 

                    $html->setvar('group_url', $group_url);
                    $html->setvar('group_name', $group_name);
                    $html->setvar('group_description', $group_description);
                    $html->setvar('group_owner', self::getUsername($group['user_id']));

                    $html->parse('group_item', true);
                }
                
            }

            $html->parse('group_items',  false);
        }
        parent::parseBlock($html);
    }

    public function getNameSeo($group_id)
    {

        global $g_user, $g;

        $groupId = $group_id;
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }

        $group_nameseo = $group['name_seo'];

        return $group_nameseo;
    }

    public function getUsername($user_id) {
        global $g_user, $g;
        
        $sql = "SELECT * FROM user WHERE user_id = " . to_sql($user_id, 'Text');
        $user = DB::row($sql);
        if($user) {
            return $user['name'];
        } else {
            return '';
        }        
    }

}

$page = new CGroupsInviteSelect("", $g['tmpl']['dir_tmpl_main'] . "groups_invite_select.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include "./_include/core/main_close.php";
