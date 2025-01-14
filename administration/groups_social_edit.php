<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$groupInfo = Groups::getInfoBasic(get_param_int('group_id'));
if(!$groupInfo) {
    redirect('groups_social.php');
}

class CForm extends CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action() {
        global $g;
        global $g_user;
        global $p;

		$cmd = get_param('cmd');

        if ($cmd) {
            $groupId = get_param_int('group_id');
            $groupInfo = Groups::getInfoBasic($groupId);
            $g_user = User::getInfoFull($groupInfo['user_id']);

            if ($cmd == 'update') {
                $data = array(
                    'title' => get_param('title'),
                    'description' => get_param('description')
                );
                Groups::update($data, $groupId);
                redirect("$p?group_id={$groupId}&action=saved");
            } elseif ($cmd == 'insert_photo') {

				$fileTemp = $g['path']['dir_files'] . 'temp/admin_upload_user_profile_' . time();
				Common::uploadDataImageFromSetData($fileTemp, 'photo_file');

                $description = get_param('description');

                $this->message = User::validatePhoto('photo_file');

                if ($this->message == "") {
                    $g['options']['photo_approval'] = 'N';
                    $g['options']['nudity_filter_enabled'] = 'N';
                    $photo_id = uploadphoto($g_user['user_id'], '', $description, 1, '../', false, 'photo_file', get_param('private'));

                    //popcorn modified s3 bucket photo 2024-05-06 start
                    if(getFileDirectoryType('photo') == 2) {
                        $photo = DB::one('photo', '`photo_id` = ' . to_sql($photo_id));

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

                    $data = array(
                        'group_id' => $groupId,
                        'group_page' => $groupInfo['page'],
                        'group_private' => $groupInfo['private'],
                    );
                    DB::update('photo', $data, '`photo_id` = ' . to_sql($photo_id));
                    redirect("$p?group_id={$groupId}&action=saved");
                }
            } elseif ($cmd == 'delete_photo') {
                $photo_id = get_param_int('photo_id');
                if (!$photo_id){
                    return;
                }
                deletephoto($g_user['user_id'], $photo_id, $groupId);
                redirect("{$p}?group_id={$groupId}&action=delete");
            } elseif ($cmd == 'approve_photo') {
                $photo_id = get_param_int('photo_id');
                Moderator::setNotificationTypePhoto();
                User::photoApproval($photo_id, 'add');
                Moderator::sendNotificationApproved();
                redirect("$p?group_id={$groupId}&action=saved");
            }
        }
	}

	function parseBlock(&$html)
	{
        global $g;

        $html->setvar('message', $this->message);

        $id = get_param_int('group_id');
        $groupInfo = Groups::getInfoBasic($id);

        $html->setvar('group_id', $id);
        $html->setvar('group_title', $groupInfo['title']);
        $html->setvar('group_user_id', $groupInfo['user_id']);

        $html->setvar('group_description', $groupInfo['description']);

        $whereNoPrivatePhoto = '';
        $noPrivatePhoto = CProfilePhoto::isHidePrivatePhoto();
        if ($noPrivatePhoto) {
            $whereNoPrivatePhoto = ' AND `private` = "N" ';
        }
        $sql = "SELECT COUNT(photo_id)
                  FROM photo WHERE `group_id` = " . to_sql($id) .
                "  AND `visible` != 'P'
                   AND `group_id` != 0 " . $whereNoPrivatePhoto;
		$num_photos = DB::result($sql);

		if ($num_photos < 4) {
			$html->parse("photo_upload", true);
		}

		$html->setvar("num_photos", $num_photos);

		DB::query("SELECT *, IF(private='Y', 1, 0) AS access FROM photo WHERE `group_id` = " . to_sql($id) . " AND `visible` != 'P' AND `group_id` != 0 " . $whereNoPrivatePhoto . " ORDER BY access, photo_id DESC;");

        $noPrivatePhoto = Common::isOptionActiveTemplate('no_private_photos');
		for ($i = 1; $i <= $num_photos; $i++)
		{
			$html->setvar("numer", $i);

			if ($row = DB::fetch_row())
			{
                $html->setvar('photo', User::getPhotoFile($row, 's', ''));
				$html->setvar('photo_id', $row['photo_id']);
                if (!$noPrivatePhoto) {
                    $html->setvar("photo_access", l($row['private']=='N'?'make_private':'make_public'));
                    $html->parse('photo_access', false);
                }


				$html->setvar("photo_name", $row['photo_name']);
				$html->setvar("description", nl2br($row['description']));

				$html->setvar("visible", $row['visible'] == "Y" ? "" : "(pending audit)");
                if ($row['visible'] == "Y") {
                    $html->clean('photo_approve');
                } else {
                    $html->parse('photo_approve', false);
                }

				if ($i == 1 or $i == 3) $html->parse("photo_odd", true);
				else $html->setblockvar("photo_odd", "");

				if ($i == 2) $html->parse("photo_even", true);
				else $html->setblockvar("photo_even", "");

                if($i % 4 == 0) {
                    $html->parse('photo_delimiter');
                } else {
                    $html->setblockvar('photo_delimiter', '');
                }

				$html->subcond(!$row['gif'], 'photo_edit_image');
				$html->subcond(!$row['gif'], 'photo_rotate');

				$html->parse("photo_item", true);

				$html->parse("photo", false);
			}
		}

		$html->parse("photo_edit", true);
        if (!Common::isOptionActive('personal_settings')) {
            $html->parse('btn_update', false);
        }

        if (!$noPrivatePhoto) {
            $html->parse('photo_add_access', false);
        }

		parent::parseBlock($html);
	}
}

$page = new CForm('', $g['tmpl']['dir_tmpl_administration'] . 'groups_social_edit.html');

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGroupsSocial());

include("../_include/core/administration_close.php");