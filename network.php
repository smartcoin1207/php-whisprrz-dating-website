<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "login";
include("./_include/core/main_start.php");

class CNetworkGraph extends CHtmlBlock
{

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $sql = '(SELECT U.*
                   FROM `friends_requests` as F,
                        `user` as U
                  WHERE (F.user_id = ' . to_sql($g_user['user_id'], 'Number') . ' AND U.user_id = F.friend_id
                     OR
                        F.friend_id = ' . to_sql($g_user['user_id'], 'Number') . ' AND U.user_id = F.user_id)
                   AND F.accepted = 1)
                 UNION
                (SELECT * FROM `user` WHERE `user_id` = ' . to_sql($g_user['user_id'], 'Number') . ' LIMIT 1)';
        DB::query($sql);

        $friends = User::getFriendsList($g_user['user_id'], true);
        $delimiterFriends = '';
        $delimiterRelations = '';
        while ($row = DB::fetch_row()) {

           $photo = User::getPhotoDefault($row['user_id'], 'r');
           $html->setvar('user_id', $row['user_id']);
           $html->setvar('user_name', $row['name']);
           $html->setvar('user_photo', $photo);
           $html->setvar('delimiter_friends', $delimiterFriends);
           $html->parse('friends_item', true);
           $delimiterFriends = ',';

           $sql = 'SELECT `friend_id`, `activity`
                FROM `friends_requests`
                WHERE accepted = 1
                    AND user_id = ' . to_sql($row['user_id'], 'Number')
                . ' AND friend_id IN (' . $friends . ')';
           DB::query($sql, 1);
           $colorUpper = get_session('color_upper');
           $colorLower = get_session('color_lower');

           while ($item = DB::fetch_row(1)) {
                $activity[$row['user_id']] = $item['activity'];
                $html->setvar('from_user_id', $row['user_id']);
                $html->setvar('to_user_id', $item['friend_id']);
                $html->setvar('activity', $item['activity']);
                if ($row['user_id'] == $g_user['user_id']
                    || $item['friend_id'] == $g_user['user_id']) {
                    $html->setvar('color', $colorLower);
                } else {
                    $html->setvar('color', '#BFBFBF');
                }
                $html->setvar('color_set', $colorUpper);
                $html->setvar('delimiter_relations', $delimiterRelations);
                $html->parse('relations_item', true);
                $delimiterRelations = ',';
           }
        }
		parent::parseBlock($html);
	}
}

class CNetwork extends CHtmlBlock
{
    function action()
	{
		$cmd = get_param('cmd', '');
        if ($cmd == 'graph') {
            $graph = new CNetworkGraph('', Common::getOption('dir_tmpl_main', 'tmpl') . "_network_graph.html");
            echo $graph->parse($tmp, true);
            die();
        }
    }

}

$page = new CNetwork("", $g['tmpl']['dir_tmpl_main'] . "network.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
?>
