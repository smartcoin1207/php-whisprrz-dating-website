<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include("./_include/core/main_start.php");
require_once(__DIR__ . '/_include/current/groups/tools.php');

$key = get_param("key");
$gid = get_param("group_id");
$cmd = get_param("cmd");

    if ($cmd == 'join' && $gid && $key) {
        $sql = 'SELECT * FROM groups_invite
            WHERE group_id = ' . to_sql($gid) . '
                AND invite_key = ' . to_sql($key);
        $invite = DB::row($sql);

        if ($invite) {
            if($invite['user_id'] == 0) {
                if (!guid()) {
                    set_session('group_key', $key);
                    set_session('group_id', $gid);
                    redirect('join.php');
                }
            } else {
            }
        } else {
            Common::toHomePage();
        }
    }

    function do_action($uid = null)
    {
        global $g;
        if(!$uid) {
            redirect('join.php');
        } else {
            global $g_user;
            $g_user['user_id'] = $uid;
        }

        $cmd = get_param('cmd');

        if ($cmd == 'join' && User::isExistsByUid($uid)) {
            $group_id = get_param('group_id');
            $group = DB::row("SELECT * FROM `groups_social` WHERE group_id = " . to_sql($group_id, 'Text') . "");
            if ($group) {
                $key = get_param('key');
                if ($key) {
                    $sql = 'SELECT * FROM groups_invite
                        WHERE group_id = ' . to_sql($group['group_id']) . '
                            AND invite_key = ' . to_sql($key);

                    $groups_invite = DB::row($sql);

                    if ($groups_invite) {
                        if($group['private'] == 'Y') {
                            $gr = Groups::subscribeAction(guid(), $group_id, 'request');
                            $ga = Groups::subscribeAction(guid(), $group_id, 'approve');
                        } else if($group['private'] == 'N') {
                            $gr = Groups::subscribeAction(guid(), $group_id, 'request');
                        }
                        
                        $sql = 'DELETE FROM groups_invite
                            WHERE group_id = ' . to_sql($group['group_id']) . '
                                AND ( invite_key = ' . to_sql($key) . '
                                    OR
                                    user_id = ' . to_sql(guid()) . '
                                )';
                        DB::execute($sql);
                        $group_nameseo = getNameSeo($group_id);
                        $success_url = $g['path']['url_main'] . $group_nameseo;
                        redirect($success_url);
                    }
                }
            }
        }
        Common::toHomePage();
    }

    function getNameSeo($group_id)
    {

        global $g_user, $g;

        $groupId = $group_id;
        $g_sql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($g_sql);
        if (!$group) {
            Common::toHomePage();
        }


        $group_nameseo = $group['name_seo'];
        if(!$group_nameseo) {
            return '';
        }
        return $group_nameseo;
    }



do_action(guid());

include("./_include/core/main_close.php");