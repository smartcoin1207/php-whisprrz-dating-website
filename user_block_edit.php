<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

if(!Common::isOptionActive('contact_blocking')) {
    redirect('home.php');
}

$uid = intval(get_param('uid', 0));
if($uid) {
    $name = User::getInfoBasic($uid, 'name');
    if(!$name) {
        redirect('user_block_list.php');
    }
} else {
    $name = get_param('name', '');
    $sql = 'SELECT user_id FROM user
        WHERE name = ' . to_sql($name, 'Text');
    $uid = DB::result($sql);
    if(!$uid) {
        redirect('user_block_list.php');
    }

}
include("./_include/current/friends.php");



class CBlockEdit extends CHtmlBlock
{
    var $uid = 0;
    var $name = '';

	function action()
	{
		$action = get_param('action');

		if($action == 'update') {

            $add = '';

            $blockOptions = User::getBlockOptionsActiveSections();
            if(count($blockOptions)) {
                $delimiter = '';
                foreach($blockOptions as $blockOption) {
                    $add .= $delimiter . to_sql($blockOption, 'Plain') . ' = ' . to_sql(get_param('block_option_' . $blockOption), 'Number');
                    $delimiter = ', ';
                }
            }

            if($add) {
                $where = ' WHERE user_from = ' . to_sql(guid(), 'Number') . '
                        AND user_to = ' . to_sql($this->uid, 'Number');

                $sql = 'SELECT user_from
                    FROM user_block_list ' . $where;
                if(DB::result($sql) != guid()) {
                    $sql = 'INSERT INTO user_block_list
                        SET user_from = ' . to_sql(guid(), 'Number') . ',
                            user_to = ' . to_sql($this->uid, 'Number') . ',
                            ' . $add;
                } else {
                    $sql = 'UPDATE user_block_list SET ' . $add . $where;
                }
                DB::execute($sql);

                $whereBlocks = '';
                $blockOptionsAll = array_keys(User::getBlockOptions());
                foreach($blockOptionsAll as $blockOptionAll) {
                    $whereBlocks .= ' AND ' . to_sql($blockOptionAll, 'Plain') . ' = 0 ';
                }

                $sql = 'SELECT user_from
                    FROM user_block_list ' . $where . $whereBlocks;
                $check = DB::result($sql);
                if($check == guid()) {
                    $sql = 'DELETE FROM user_block_list ' . $where;
                    DB::execute($sql);
                }
                if (get_param('block_option_im')) {
                    CIm::closeIm($this->uid);
                }
                User::friendDelete(guid(), $this->uid);
                MutualAttractions::unlike($this->uid);
            }
            CStatsTools::count('user_blocks');
			redirect('user_block_list.php');
		}


	}

	function parseBlock(&$html)
	{
     	$html->setvar('user_name', $this->name);
     	$html->setvar('user_id', $this->uid);
		$photo = User::getPhotoDefault($this->uid, 'r');
		$html->setvar('user_photo', $photo);

        $sql = 'SELECT * FROM user_block_list
            WHERE user_from = ' . to_sql(guid(), 'Number') . '
                AND user_to = ' . to_sql($this->uid, 'Number');
		DB::query($sql);
		$block = DB::row($sql);

        $blockOptions = User::getBlockOptionsActive();
        $parsed = false;
        foreach($blockOptions as $blockOption => $title) {
            if(isset($block[$blockOption]) && $block[$blockOption] == 1) {
                $checked = 'checked';
            } else {
                $checked = '';
            }

            $html->setvar('option_checked', $checked);
            $html->setvar('option', $blockOption);
            $html->setvar('option_title', l($title));

            if($blockOption == 'wall' && Common::isOptionActive('block_list_hide_wall', 'template_options')) {
                $html->parse('wall');
                continue;
            }

            if($parsed) {
                $html->parse('delimiter', false);
            }
            $html->parse('option');
            $parsed = true;
        }
        $html->parse('options', false);

		parent::parseBlock($html);
	}
}

$page = new CBlockEdit("", $g['tmpl']['dir_tmpl_main'] . "user_block_edit.html");

$page->uid = $uid;
$page->name = $name;

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$friends_menu = new CFriendsMenu("friends_menu", $g['tmpl']['dir_tmpl_main'] . "_friends_menu.html");
$friends_menu->active_button = "blocked";
$page->add($friends_menu);

include("./_include/core/main_close.php");

?>