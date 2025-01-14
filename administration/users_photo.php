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

		$sql = "SELECT * FROM photo WHERE " . CProfilePhoto::moderatorVisibleFilter() . " AND `group_id` = 0 ORDER BY photo_id LIMIT 20";
		$num = DB::count('photo', CProfilePhoto::moderatorVisibleFilter() . " AND `group_id` = 0", '', 20 );
		$rows = DB::rows($sql);

		foreach ($rows as $row) {
			$row['user_name'] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
			$folder_sql = "SELECT * FROM custom_folders WHERE user_id = " . to_sql($row['user_id'], "Number");
			$custom_folders = DB::rows($folder_sql);
			
			foreach ($row as $k => $v)
			{
				$html->setvar($k, $v);
			}

			$html->setvar('photo_m', User::photoFileCheck($row, 'm'));
			$html->setvar('photo_b', User::photoFileCheck($row, 'b'));
			
			if (!$noPrivatePhoto) {
				/* Popcorn modified 2024-11-05 custom folders start */
				$photo_accesses = [
					[ "value" => 'public', "label" => "public"],
					[ "value" => 'private', "label" => "private"],
					[ "value" => 'personal', "label" => "personal"],
				];

				foreach ($custom_folders as $folder) {
					$photo_accesses[] = ["value" => $folder['id'], "label" => $folder['name']];
				}

				foreach ($photo_accesses as $photo_access) {
					$html->setvar('status', l($photo_access['label']));
					$html->setvar('photo_access_label', $photo_access['label']);
					$html->setvar('photo_access_value', $photo_access['value']);

					$photo_access_check = false;
					if($photo_access['label'] == 'public') {
						$photo_access_check = true;
					} else if($photo_access['label'] == 'private' && $row['private'] == "Y") {
						$photo_access_check = true;
					} else if($photo_access['label'] == 'personal' && $row['personal'] == "Y") {
						$photo_access_check = true;
					} else if(is_numeric($photo_access['value']) && $photo_access['value'] > 0) {
						if($row['in_custom_folder'] == 'Y' && ((int) $row['custom_folder_id']) > 0  && $photo_access['value'] == $row['custom_folder_id']) {
							$photo_access_check = true;
						}
					}

					if($photo_access_check) {
						$html->setvar('photo_access_check', 'checked="checked"');
					} else {
						$html->setvar('photo_access_check', '');
					}

					$html->parse('photo_access_item', true);
				}

				$html->parse('photo_access', false);
				$html->clean('photo_access_item');
				/* Popcorn modified 2024-11-05 custom folders start */
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