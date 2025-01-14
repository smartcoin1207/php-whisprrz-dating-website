<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

function user_has_role_admin_view()
{
	global $g_user;

	return $g_user['user_id'] && ($g_user['role'] == 'admin' || $g_user['role'] == 'demo_admin');
}

function user_has_role_admin_edit()
{
    global $g_user;

    return $g_user['user_id'] && ($g_user['role'] == 'admin');
}

function hash_generate($hash_length = 64, $chars = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
{
    $i = 0;
    $hash = "";
    while ($i < $hash_length)
    {
      $hash .= $chars[mt_rand(0, strlen($chars)-1)];
      $i++;
    }

    return $hash;
}

function user_change_email($user_id, $email, $type = 'confirm_email')
{
	global $g;

    User::emailChange(guser('mail'), $email);

    $row = DB::one('user', 'active_code != "" AND mail = ' . to_sql($email) . ' AND user_id = ' . to_sql($user_id));

    if($row) {
        $hash = $row['active_code'];
    } else {
        do {
            $hash = hash_generate(40);
        } while(DB::result("SELECT user_id FROM user WHERE active_code = " . to_sql($hash, "Text") .";") != 0);

        $sql = 'UPDATE user '
                . 'SET active_code = ' . to_sql($hash) . ', '
                . 'change_mail = ' . to_sql(date('Y-m-d H:i:s')) . ', '
                . 'mail = ' . to_sql($email)
                . 'WHERE user_id = ' . to_sql($user_id, 'Number');
        DB::execute($sql);
    }

    if (Common::isEnabledAutoMail($type)) {

        $name = guser('name');
        if(!$name) {
            $name = get_session('j_name');
        }

        $vars = array(
            'title' => $g['main']['title'],
            'confirm_link' => Common::urlSite() . 'confirm_email.php?hash=' . $hash,
            'hash' => $hash,
            'name' => $name,
        );
        Common::sendAutomail(Common::getOption('lang_loaded', 'main'), $email, $type, $vars);
    }

}

function friend_delete($user_id, $friend_id)
{
	DB::execute("DELETE FROM friends WHERE user_id=" . to_sql($user_id, 'Number') . " AND fr_user_id = " . to_sql($friend_id, 'Number'));
	DB::execute("DELETE FROM friends WHERE user_id=" . to_sql($friend_id, 'Number') . " AND fr_user_id = " . to_sql($user_id, 'Number'));
}

function friend_add($user_id, $friend_id)
{
	friend_delete($user_id, $friend_id);

	DB::execute("INSERT INTO friends SET user_id=" . to_sql($user_id, 'Number') . ", fr_user_id = " . to_sql($friend_id, 'Number') . ", DATA='".date('Y-m-d H:i:s')."'");
	DB::execute("INSERT INTO friends SET user_id=" . to_sql($friend_id, 'Number') . ", fr_user_id = " . to_sql($user_id, 'Number') . ", DATA='".date('Y-m-d H:i:s')."'");
}

function user_blocked($user_id, $this_user_id = null)
{
	global $g_user;
	$this_user_id = $this_user_id ? $this_user_id : $g_user['user_id'];

	$check_mail = DB::result("select user_to from users_block where user_from=".to_sql($this_user_id)." and user_to=".to_sql($user_id));
	if($check_mail)
		return true;

	$check_block = DB::result("select user_to from user_block_list where user_from=".to_sql($this_user_id)." and user_to=".to_sql($user_id). " AND (im > 0 OR audio > 0 OR video > 0 OR game > 0)");
	if($check_block)
		return true;

	return false;
}

function user_mail_blocked_by($user_id)
{
	global $g_user;

	$check_mail = DB::result("select user_to from users_block where user_from=".to_sql($user_id)." and user_to=".to_sql($g_user['user_id']));
	if($check_mail)
		return true;

	return false;
}

function user_service_blocked_by($user_id, $service)
{
	global $g_user;

	$check_block = DB::result("select user_to from user_block_list where user_from=".to_sql($user_id)." and user_to=".to_sql($g_user['user_id']). " and " . $service . "  > 0");
	if($check_block)
		return true;

	return false;
}

function user_select_by_id($user_id)
{
    DB::query("SELECT * FROM `user` WHERE `user_id` = " . to_sql($user_id) . " LIMIT 1;");

    if($row = DB::fetch_row())
    {
        return $row;
    }

    return null;
}