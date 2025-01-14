<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

class CPhoto extends CHtmlBlock
{
	var $sMessage = "";

	function action()
	{
		global $g;
		global $g_user;
		global $l;

		$cmd = get_param("cmd", "");
        $photo_name = get_param("photo_name", "");
        $description = get_param("description", "");
        $photo_id = get_param("id", 0);

		if ($cmd == "insert")
		{
			$this->message = "";

			$this->message = validatephoto("photo_file");

			if ($this->message == "")
			{
                CStatsTools::count('photos_uploaded');
				$id = uploadphoto($g_user['user_id'], $photo_name, $description, $g['options']['photo_approval']=='Y'?0:1);

				//popcorn modified s3 bucket photo 2024-05-06 start
				$photo = DB::one('photo', '`photo_id` = ' . to_sql($id));

				if(getFileDirectoryType('photo') == 2) {
					$fileTypes = CProfilePhoto::getSizes();
					$ext = '.jpg';
					if(file_exists($g['path']['dir_files'] . 'temp/' . $photo['photo_id'] . '_' . $photo['hash'] . '_bm.gif')) {
						$ext = '.gif';
					}
	
					foreach ($fileTypes as $fileType) {
						$file_path = $g['path']['dir_files'] . 'temp/' . $photo['photo_id'] . '_' . $photo['hash'] . '_' . $fileType . $ext;
						if(file_exists($file_path)) {
							custom_file_upload($file_path, 'photo/' . $photo['photo_id'] . '_' . $photo['hash'] . '_' . $fileType . $ext);
						}
					}
				}
				//popcorn modified s3 bucket photo 2024-05-06 end
                redirect();
			}
		}

		if ($cmd == "update")
		{

			if ($photo_id == 0)
			{
				return;
			}
			$this->message = "";

			if (strlen($description) > 100 or !preg_match("/^[a-zA-Z\-_\.0-9\r\n\s]{0,100}$/", $description) and $description != "")
			{
				#$this->message .= "The Description incorrect.<br>";
			}

			if ($this->message != "")
			{
				return;
			}
			else
			{
                $photo_name = strip_tags($photo_name);
                $description = strip_tags($description);
				DB::execute("UPDATE photo SET
					photo_name=" . to_sql($photo_name) . ", description=" . to_sql($description) . "
					WHERE photo_id=" . to_sql($photo_id, "Number") . " AND user_id=" . $g_user['user_id'] . ";"
				);
			}
		}

		if ($cmd == "delete") {
			if ($photo_id)	{
                CProfilePhoto::deletePhoto($photo_id);
			}
            redirect();
		}

		if ($cmd == "main")	{
			User::photoToDefault($photo_id);
			redirect();
		}

        if($cmd == 'private' || $cmd == 'public') {
            CProfilePhoto::setPhotoPrivate($photo_id);
            redirect();
        }

		/* Divyesh - Added on 11-04-2024 */
		if ($cmd == 'personal' || $cmd == 'remove_personal') {
			CProfilePhoto::setPhotoPersonal($photo_id);
			redirect();
		}
		
		if ($cmd == 'move_to_folder' || $cmd == 'remove_from_folder') {
			CProfilePhoto::setPhotoCustomFolder($photo_id);
			redirect();
		}
		/* Divyesh - Added on 11-04-2024 */
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		if (isset($this->message)) {
			$html->setvar("message", $this->message);
			$html->parse("message");
		}

		$num_photos = DB::result("SELECT COUNT(photo_id) FROM photo WHERE user_id=" . $g_user['user_id'] . " AND visible != 'P'  AND group_id = 0");
		$html->setvar("num_photos", $num_photos);
        $html->setvar('name', $g_user['name']);
		$html->setvar('custom_folder', $g_user['custom_folder']);

		DB::query("SELECT * FROM photo WHERE user_id=" . $g_user['user_id'] . "  AND visible != 'P' AND group_id = 0 ORDER BY photo_id ASC");

		if (($num_photos + 1) % 2 == 0) $num_photos++;

		$row_index = 1;
		$row_buffer = 1;

		for ($i = 1; $i <= $num_photos; $i++)
		{
			$html->setvar("numer", $i);
			$row_index = $row_buffer;
			$html->setvar("row",$row_index);

			if ($row = DB::fetch_row())
			{
                $html->setvar('photo', User::getPhotoFile($row, 's', guser('gender')));
                $html->setvar('hash', $row['hash']);
				$html->setvar("photo_id", $row['photo_id']);
                $html->setvar("photo_offset", User::photoOffset($g_user['user_id'], $row['photo_id']));
				$html->setvar("user_id", $row['user_id']);
				$html->setvar("photo_name", $row['photo_name']);
				$html->setvar("description", nl2br(hard_trim(strip_tags($row['description']), 63)));

                $isPhotoApproval = CProfilePhoto::isPhotoOnVerification($row['visible']);
                $html->setvar("visible", $isPhotoApproval ? l("(pending audit)") : '');
                $blockPhoto = 'photo_item_photo';
                $blockPhotoNotApproval = "{$blockPhoto}_not_approval";
                if ($isPhotoApproval) {
                    $html->parse($blockPhotoNotApproval, false);
                    $html->clean($blockPhoto);
                } else {
                    $html->parse($blockPhoto, false);
                    $html->clean($blockPhotoNotApproval);
                }

				if($row['default']=='Y') {
                    $html->setvar('default_title', l('default'));
                    $html->setvar('default',"checked='checked'");
                } else {
                    $html->setvar('default_title', l('make_default'));
                    $html->setvar("default","");
                }

				if($row['private']=='Y') {
                    $html->setvar('private_title', l('private'));
                    $html->setvar("private","checked='checked'");
                } else {
                    $html->setvar('private_title', l('make_private'));
                    $html->setvar("private","");
                }
				/* Divyesh - Added on 11-04-2024 */
				if ($row['personal'] == 'Y') {
					$html->setvar('personal_title', l('remove_personal'));
					$html->setvar("personal", "remove_personal");
				} else {
					$html->setvar('personal_title', l('make_personal'));
					$html->setvar("personal", "personal");
				}
				if ($row['in_custom_folder'] == 'Y') {
					$html->setvar('folder_title', l('remove_from') . ' ' . $g_user['custom_folder']);
					$html->setvar("folder", "remove_from_folder");
				} else {
					$html->setvar('folder_title', l('move_to') . ' ' . $g_user['custom_folder']);
					$html->setvar("folder", "move_to_folder");
				}
				/* Divyesh - Added on 11-04-2024 */

				$html->setvar("make_image_desc_editable", "<script type=\"text/javascript\">initEditableDesc('DescEditable" . $row['photo_id'] . "', " . $row['photo_id'] . ");</script>");
				$html->setvar("make_image_title_editable", "<script type=\"text/javascript\">initEditableTitle('TitleEditable" . $row['photo_id'] . "', " . $row['photo_id'] . ");</script>");


                if(!$isPhotoApproval) {
                    if($row['default'] == 'N') {
                        $html->parse("set_default", false);
                        $html->setblockvar("default", '');
                    } else {
                        $html->parse("default", false);
                        $html->setblockvar("set_default", '');
                    }
                } else {
                    $html->setblockvar("default", '');
                    $html->setblockvar("set_default", '');
                }

                if($row['private'] == 'N') {
                    $html->parse("set_private", false);
                    $html->setblockvar("set_public", '');
                } else {
                    $html->parse("set_public", false);
                    $html->setblockvar("set_private", '');
                }

				/* Divyesh - Added on 11-04-2024 */
				if (!empty($g_user['custom_folder'])) {
					$html->parse("set_custom_folder", false);
				} else {
					$html->setblockvar("set_custom_folder", '');
				}
				/* Divyesh - Added on 11-04-2024 */


				if (($i + 1) % 2 == 0) {
				$html->parse("photo_odd", true);
				}
				else $html->setblockvar("photo_odd", "");

				if ($i % 2 == 0) {
					$html->parse("photo_even", true);
					if($row_index==1) $row_buffer = 2;
					if($row_index==2) $row_buffer = 1;
				}
				else $html->setblockvar("photo_even", "");

				$html->parse("photo_item", true);
				$html->parse("photo", false);
			}
			else
			{
				$html->setvar("photo_id", "");
				$html->setvar("user_id", "");
				$html->setvar("photo_name", "");
				$html->setvar("description", "");

				$html->setvar("visible", "");

				$html->setvar("orientation", $g_user['orientation']);

				if (($i + 1) % 2 == 0) $html->parse("nophoto_odd", true);
				else $html->setblockvar("nophoto_odd", "");

				if ($i % 2 == 0) $html->parse("nophoto_even", true);
				else $html->setblockvar("nophoto_even", "");

				$html->parse("nophoto_item", true);
				$html->parse("photo", false);
			}

		}

		$html->parse("photo_edit", true);
        if (Common::isOptionActive('photo_approval')) {
            $html->parse('photo_rules');
        }
        $html->setvar('profile_photo_description_length', Common::getOption('profile_photo_description_length'));
        $html->setvar('max_size', str_replace('{size}', Common::getOption('photo_size'), l('Max size')));

		$html->parse("photo_upload", true);

		parent::parseBlock($html);
	}
}

g_user_full();
$page = new CPhoto("", $g['tmpl']['dir_tmpl_main'] . "profile_photo1.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('photos');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");

?>