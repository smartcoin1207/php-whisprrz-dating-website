<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
        global $p;
        global $g_user;

		$photo = get_param_array("do");
		$comment = get_param_array("comment");
		$redirect = false;

		foreach ($photo as $k => $v){

            Moderator::setNotificationTypePhoto();

			if ($v == 'del') {
				DB::query("SELECT * FROM photo WHERE photo_id=" . ((int) $k) . "", 2);
				if ($row = DB::fetch_row(2)) {
					deletephoto($row['user_id'], $row['photo_id']);

					$row['comment_declined'] = trim($comment[$k]);
                    Moderator::prepareNotificationInfo($row['user_id'], $row);
				}
				$redirect = true;
                Moderator::sendNotificationDeclined();
			} else { // Divyesh - Added on 11-04-2024
				/*DB::execute("UPDATE photo SET visible='Y' WHERE photo_id=" . ((int) $k) . "");
				DB::query("SELECT * FROM photo WHERE photo_id=" . ((int) $k) . "", 2);
				if ($row = DB::fetch_row(2)) {
                    $g_user['user_id'] = $row['user_id'];
					DB::execute("UPDATE user SET is_photo='Y' WHERE user_id=" . $row['user_id'] . "");
                    User::setAvailabilityPublicPhoto($row['user_id']);
				}
                $wallId = DB::result('SELECT `wall_id` FROM `photo` WHERE photo_id = ' . to_sql($k, 'Number'));
                if ($wallId) {
                    DB::update('wall', array('params' => 1), '`id` = ' . to_sql($wallId));
                }
                if ($v == 'access') {
                    CProfilePhoto::setPhotoPrivate($k);
                }*/
                User::photoApproval($k, $v);
				$redirect = true;
                Moderator::sendNotificationApproved();
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
		$noPrivatePhoto = Common::isOptionActiveTemplate('no_private_photos');

		DB::query("SELECT * FROM photo WHERE " . CProfilePhoto::moderatorVisibleFilter() . " AND `group_id` = 0 ORDER BY photo_id LIMIT 20");
		$num=DB::num_rows();
		while ($row = DB::fetch_row())
		{
			$row['user_name'] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
			$custom_folder = DB::result("SELECT custom_folder FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
			if (!empty($custom_folder)) {
				$html->setvar('custom_folder', $custom_folder);
			}
			foreach ($row as $k => $v)
			{
				$html->setvar($k, $v);
			}

			$html->setvar('photo_m', User::photoFileCheck($row, 'm'));
			$html->setvar('photo_b', User::photoFileCheck($row, 'b'));
			if (!$noPrivatePhoto) {
				/* Divyesh - Added on 11-04-2024 */
				if ($row['private'] == 'Y') {
					$html->setvar('status', l('Private'));
					$html->setvar('private_check', 'checked="checked"');
					$html->setvar("personal_check", '');
					$html->setvar("folder_check", '');
					$html->setvar("public_check", '');
				} else if ($row['personal'] == 'Y') {
					$html->setvar('status', l('personal'));
					$html->setvar('personal_check', 'checked="checked"');
					$html->setvar("private_check", '');
					$html->setvar("folder_check", '');
					$html->setvar("public_check", '');
				} else if ($row['in_custom_folder'] == 'Y' && !empty($custom_folder)) {
					$html->setvar('status', $custom_folder);
					$html->setvar('folder_check', 'checked="checked"');
					$html->setvar("private_check", '');
					$html->setvar("personal_check", '');
					$html->setvar("public_check", '');
				} else {
					$html->setvar('status', l('Public'));
					$html->setvar('public_check', 'checked="checked"');
					$html->setvar("private_check", '');
					$html->setvar("personal_check", '');
					$html->setvar("folder_check", '');
				}
				
				if (!empty($custom_folder)) {
					$html->setvar("folder_access", l('move_to') . " " . $custom_folder);
					$html->parse("show_folder_access", false);
				} else {
					$html->setblockvar("show_folder_access", '');
				}
				
				//$html->setvar("photo_access", l($row['private']=='N'?'make_private':'make_public'));
				//$html->parse('photo_access', false);
				
				/* Divyesh - Added on 11-04-2024 */
				
			}

			$html->subcond($row['gif'], 'photo_edit_image');
			$html->subcond(!$row['gif'], 'photo_edit_image_1');
			$html->subcond(!$row['gif'], 'photo_rotate');

			$html->parse("photo", true);
			
		}
		if($num){
			$html->parse("photos");
		} else {
			$html->parse("msg");
		}

		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_photo.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsers());

include("../_include/core/administration_close.php");