<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/vids/includes.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
        global $p;

		$video = get_param_array("do");
		$comment = get_param_array("comment");
		$redirect = false;

        foreach ($video as $k => $v){

            Moderator::setNotificationTypeVideo();

			if ($v == 'add') {
				DB::execute("UPDATE vids_video SET active=1 WHERE id=" . ((int) $k) . "");
                /*
				DB::query("SELECT * FROM vids_video WHERE id=" . ((int) $k) . "", 2);
				if ($row = DB::fetch_row(2)) {
					DB::execute("UPDATE user SET is_photo='Y' WHERE user_id=" . $row['user_id'] . "");
                    User::setAvailabilityPublicPhoto($row['user_id']);
				}
                 *
                 */
                DB::update('wall', array('params' => ""), '`item_id` = ' . to_sql((int) $k).' AND section="vids"');
				$redirect = true;

                $sql = 'SELECT * FROM vids_video WHERE id = ' . to_sql($k);
                $videoInfo = DB::row($sql);

                if(isset($videoInfo['user_id'])) {
                    Moderator::prepareNotificationInfo($videoInfo['user_id'], $videoInfo);
                }
                Moderator::sendNotificationApproved();
			} elseif ($v == 'private') {
				DB::execute("UPDATE vids_video SET active=1, `private`='Y' WHERE id=" . ((int) $k) . "");
			} elseif ($v == 'public') {
				DB::execute("UPDATE vids_video SET active=1, `private`='N' WHERE id=" . ((int) $k) . "");
			} elseif ($v == 'del') {
				DB::query("SELECT * FROM vids_video WHERE id=" . ((int) $k) . "", 2);
				if ($row = DB::fetch_row(2)) {
					//deletephoto($row['user_id'], $row['photo_id']);
                    CVidsTools::delVideoById($row['id'],true);
					if(isset($comment[$k])) {
						$row['comment_declined'] = trim($comment[$k]);
					}
                    Moderator::prepareNotificationInfo($row['user_id'], $row);
				}
				$redirect = true;
                Moderator::sendNotificationDeclined();
			}
		}


		if($redirect) redirect($p."?action=saved");

	}

	function parseBlock(&$html)
	{
		global $g_options;

		$html->setvar("message", $this->message);

		$table = get_param("t", "tips");
		$html->setvar("table", $table);

        $html->setvar('photo_height', Common::getOption('medium_y', 'image'));

        Common::setOptionRuntime('player_native', 'video_player_type');

		DB::query("SELECT * FROM vids_video WHERE active=3 AND group_id=0 ORDER BY id LIMIT 20", 1);
		$num=DB::num_rows(1);
		while ($row = DB::fetch_row(1))
		{
			$row['user_name'] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
			foreach ($row as $k => $v)
			{
				$html->setvar($k, $v);
			}

            $video = CVidsTools::getVideoById($row['id'], true);
            if (!isset($video) or !is_array($video)) {
                continue;
            }
            $html->setvar('video_html_code', $video['html_code']);

            $html->setvar('video_id', $row['id']);
            if ($row['private'] == '1'){
                $html->setvar('private', l('Private'));
            } else {
                $html->setvar('private', l('Public'));
            }
			$html->parse("video", true);
		}
		if($num==0){
			$html->parse("msg",true);
		} else {
			$html->parse("videos",true);
		}
		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_video.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsers());

include("../_include/core/administration_close.php");