<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

function isFriends($uid1, $uid2)
{
    $uid1 = intval($uid1);
    $uid2 = intval($uid2);
    $r = DB::count('friends_requests', "((user_id='" . $uid1 . "' AND friend_id='" . $uid2 . "') OR (friend_id='" . $uid1 . "' AND user_id='" . $uid2 . "')) AND accepted = 1");
    return ($r > 0);
}
function getFriendsIds()
{
    $rows = DB::select('friends_requests', "(user_id='" . guser('user_id') . "' OR friend_id='" . guser('user_id') . "') AND accepted = 1");
    $r = array();
    foreach ($rows as $row) {
        if (guser('user_id') == $row['user_id']) {
            $r[$row['friend_id']] = $row['friend_id'];
        } else {
            $r[$row['user_id']] = $row['user_id'];
        }
    }
    return $r;
}

class CFriendsMenu extends CHtmlBlock
{
	var $active_button = "friends";

	function parseBlock(&$html)
	{
		global $g_user;
		$html->setvar("button_" . $this->active_button . "_active", "_active");
		$html->setvar("button_oryx_" . $this->active_button . "_active", "active_btn");

        $sql = "SELECT * FROM custom_folders WHERE user_id = " . to_sql(guid(), 'Number');
        $folders = DB::rows($sql);
        $folder_id = get_param('folder_id', '');
        foreach ($folders as $key => $folder) {
            $html->setvar('folder_name', $folder['name']);
            $html->setvar('folder_id', $folder['id']);
            if($folder_id == $folder['id']) {
                $html->setvar('folder_active', 'active_btn');
            } else {
                $html->setvar('folder_active', '');
            }
            $html->parse('folder_item', true);
        }

        $html->parse('folder_invite', false);
        $html->clean('folder_item');
        $html->clean('folder_active');

		$n_friend_requests = DB::result("SELECT COUNT(user_id) FROM friends_requests WHERE friend_id = " . $g_user["user_id"] . " AND accepted=0");
		$html->setvar("n_friend_requests", $n_friend_requests);
        if(Common::isOptionActive('contact_blocking')) {
            $html->parse("contact_blocking");
        }
		if($n_friend_requests)
			$html->parse("friend_requests_exists", true);
		else
			$html->parse("no_friend_requests", true);

        // if (Common::isOptionActive('bookmarks')) {
            $html->parse('friend_bookmarks', false);
        // }
        // if(Common::isOptionActive('invite_friends')) {
            $html->parse('invite_on');
        // }   
		parent::parseBlock($html);
	}
}


