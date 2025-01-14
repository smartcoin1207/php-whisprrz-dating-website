<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */




function delete_user($user_id)
{
    global $g;

	if (!function_exists('guser')) {
		function guser($field = null)
		{
			global $g_user;
			if ($field == null) {
				return $g_user;
			} else {
				return isset($g_user[$field]) ? $g_user[$field] : null;
			}
		}
	}

	global $g_user;

	User::deleteFileProfileBgCover($user_id, 0);

	$g_user['user_id'] = $user_id;

    // stop subscriptions
    User::stopAppSubscriptions($user_id);

    City::deleteUser($user_id);

    Spotlight::removeItem($user_id);

    DB::delete('users_reports', '`user_from` = ' . to_sql($user_id) . ' OR `user_to` = ' . to_sql($user_id));
    DB::delete('users_private_note', '`from_user_id` = ' . to_sql($user_id));


    $sql = "SELECT `interest`
              FROM `user_interests` AS UI
             WHERE `user_id` = " . to_sql($user_id, 'Number');
    $interests = DB::rows($sql);
    foreach ($interests as $id) {
        Interests::deleteInterest($user_id, $id[0]);
    }
    Encounters::removeLikeToMeet($user_id);

    ProfileGift::deleteAllGiftsUser($user_id);

	DB::query("SELECT * FROM user WHERE user_id=" . to_sql($user_id, "Number") . "");
	$u = DB::fetch_row();

	DB::delete('users_checkbox', '`user_id` = ' . to_sql($user_id, 'Number'));

    require_once("adv.class.php");
    CAdvTools::deleteUser($user_id);
	/*DB::execute("DELETE FROM adv_cars WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_film WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_housting WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_items WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_jobs WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_music WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_myspace WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_personals WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_services WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM adv_sale WHERE user_id=" . to_sql($user_id, "Number") . "");*/


	DB::execute("DELETE FROM audio_invite WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM audio_reject WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM game_invite WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM game_reject WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM video_invite WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM video_reject WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM im_msg WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM im_open WHERE from_user=" . to_sql($user_id, "Number") . " OR  to_user=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM search_save WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM mail_folder WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM mail_msg WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_id=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM users_block WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM users_favorite WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM users_interest WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM users_view WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM user_block_list WHERE user_from=" . to_sql($user_id, "Number") . " OR  user_to=" . to_sql($user_id, "Number") . "");

    DB::delete('users_comments', 'user_id = ' . to_sql($user_id) . ' OR from_user_id = ' . to_sql($user_id));

    $sql = 'SELECT backgrounds FROM users_flash
        WHERE user_id = ' . to_sql($user_id, 'Number');
    $backgrounds = DB::result($sql);
    if($backgrounds) {
        $backgrounds = explode('|', $backgrounds);
        foreach ($backgrounds as $background) {
            $filePath = $g['path']['dir_files'] . 'postcard/' . $background;
            $files = array($filePath . '_src.jpg', $filePath . '.jpg');
            Common::saveFileSize($files, false);
            foreach($files as $file) {
                @unlink($file);
            }
        }
    }

    DB::execute("DELETE FROM users_flash WHERE user_id=" . to_sql($user_id, "Number"));

	// BLOGS

	require_once("blogs/tools.php");

	$ids = DB::column("SELECT id FROM blogs_post WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) {
        CBlogsTools::delPostByIdByAdmin($id, true);
    }

	$ids = DB::column("SELECT id FROM blogs_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id)  {
        CBlogsTools::delCommentByIdByAdmin($id);
    }

	DB::execute("DELETE FROM blogs_subscribe WHERE subscriber_user_id=" . to_sql($user_id, "Number") . " OR blogger_user_id = " . to_sql($user_id, "Number"));
	// BLOGS


	// EVENTS
	DB::execute("DELETE FROM events_setting WHERE user_id=" . to_sql($user_id, "Number") . "");

	require_once("events/tools.php");
	$ids = DB::column("SELECT event_id FROM events_event WHERE user_id=" . to_sql($user_id, "Number") . " OR user_to = " . to_sql($user_id, "Number"));
	foreach($ids as $id) CEventsTools::delete_event($id, true);

	$ids = DB::column("SELECT comment_id FROM events_event_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CEventsTools::delete_event_comment($id, true);

	$ids = DB::column("SELECT comment_id FROM events_event_comment_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) DB::execute("DELETE FROM events_event_comment_comment WHERE comment_id=".$id." LIMIT 1");


	$ids = DB::column("SELECT event_id FROM events_event_guest WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CEventsTools::delete_event_guest($id, true);
	// EVENTS


	DB::execute("DELETE FROM friends WHERE user_id=" . to_sql($user_id, "Number") . " OR fr_user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM friends_requests WHERE user_id=" . to_sql($user_id, "Number") . " OR friend_id=" . to_sql($user_id, "Number") . "");

    $friends = DB::select('wall', '`section` = "friends" AND `item_id` = ' . to_sql($user_id));
    if($friends) {
        foreach($friends as $friend) {
            Wall::removeById($friend['id']);
        }
    }

    $sql = 'SELECT id FROM gallery_albums
        WHERE user_id = ' . to_sql($user_id, 'Number');
    $ids = DB::column($sql);
    foreach($ids as $id) {
        Gallery::albumDelete($id, $user_id);
    }

    Gallery::commentsDeleteByUid($user_id, true);

	// GROUPS
	require_once("groups/tools.php");

	$ids = DB::column("SELECT group_id FROM groups_group WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_group($id, true);

	$ids = DB::column("SELECT group_id FROM groups_group_member WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_group_member($id, $user_id, true);

	DB::execute('DELETE FROM `wowslider` WHERE `user_id` = ' . to_sql($user_id, "Number").'');


	$ids = DB::column("SELECT comment_id FROM groups_group_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_group_comment($id, true);

	$ids = DB::column("SELECT comment_id FROM groups_group_comment_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_group_comment_comment($id, true);
    //CGroupsTools::delete_group_comment($id, true);

	### group forum

	$ids = DB::column("SELECT forum_id FROM groups_forum WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_group_forum($id, true);

	$ids = DB::column("SELECT comment_id FROM groups_forum_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_forum_comment($id, true);

	$ids = DB::column("SELECT comment_id FROM groups_forum_comment_comment WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CGroupsTools::delete_forum_comment_comment($id, true);
    //DB::execute("DELETE FROM groups_forum_comment_comment WHERE comment_id=".$id." LIMIT 1");
	// GROUPS

    //Groups social - EDGE
    Groups::deleteUserGroups($user_id);

    $photoLikes = DB::select('photo_likes', '`user_id` = ' . $user_id . ' AND `photo_user_id` != ' . $user_id);
    foreach ($photoLikes as $key => $item) {
        DB::execute('DELETE FROM `photo_likes` WHERE `id` = ' . to_sql($item['id']));

        $sql = 'SELECT COUNT(*) FROM `photo_likes`
                 WHERE `like` = 1 AND `photo_id` = ' . to_sql($item['photo_id']);
        $like = DB::result($sql);

        $sql = 'SELECT COUNT(*) FROM `photo_likes`
                 WHERE `like` = 0 AND `photo_id` = ' . to_sql($item['photo_id']);
        $dislike = DB::result($sql);

        $data = array('like' => $like, 'dislike' => $dislike);

        DB::update('photo', $data, 'photo_id = ' . to_sql($item['photo_id']));
    }

	deletephotos($user_id);

	DB::execute("DELETE FROM email WHERE mail=" . to_sql($u['mail'], "Text") . "");
	DB::execute("DELETE FROM texts WHERE user_id=" . to_sql($user_id, "Number") . "");

    $sql = 'SELECT user_editor_xml FROM userinfo
        WHERE user_id = ' . to_sql($user_id, 'Number');
    $xml = DB::result($sql);
    User::flashProfileFilesDelete($xml);

	DB::execute("DELETE FROM userinfo WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM userpartner WHERE user_id=" . to_sql($user_id, "Number") . "");


	// delete from forum
	require_once(dirname(__FILE__)."/forum.php");


	DB::execute("DELETE FROM forum_setting WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM forum_read_marker WHERE user_id=" . to_sql($user_id, "Number") . "");

	DB::execute("DELETE FROM forum_read_marker WHERE user_id=" . to_sql($user_id, "Number") . "");

	$ids = DB::column("SELECT id FROM forum_topic WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CForumTopic::delete_by_id($id);

	$ids = DB::column("SELECT id FROM forum_message WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CForumMessage::delete_by_id($id);
	// delete from forum


	// delete from places
	#only comments
	require_once("places/tools.php");
	$ids = DB::column("SELECT id FROM places_review WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CPlacesTools::delete_review($id, true);
    $ids = DB::column("SELECT id FROM places_place WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CPlacesTools::delete_place($id, true);
	// delete from places


	// delete from music
	#only files and comments
	require_once("music/tools.php");
	$ids = DB::column("SELECT song_id FROM music_song WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CMusicTools::delete_song($id, true);
    $ids = DB::column("SELECT musician_id FROM music_musician WHERE user_id=" . to_sql($user_id, "Number") . "");
    foreach($ids as $id) CMusicTools::delete_musician($id, true);

	// delete from music

	// delete from widgets
	DB::execute("DELETE FROM widgets WHERE user_id=" . to_sql($user_id, "Number") . "");
	DB::execute("DELETE FROM profile_status WHERE user_id=" . to_sql($user_id, "Number") . "");

    $sql = 'SELECT id, photo_id FROM photo_comments WHERE user_id = ' . to_sql($user_id, "Number");
    $photoComments = DB::rows($sql);
    if($photoComments) {
        foreach($photoComments as $photoComment) {
            CProfilePhoto::deleteComment($photoComment['id'], $photoComment['photo_id']);
        }
    }

    CProfilePhoto::removeRatingsLeaveUser($user_id);

	// delete from vids
	require_once("vids/tools.php");


    $photoLikes = DB::select('vids_likes', '`user_id` = ' . $user_id . ' AND `video_user_id` != ' . $user_id);
    foreach ($photoLikes as $key => $item) {
        DB::execute('DELETE FROM `vids_likes` WHERE `id` = ' . to_sql($item['id']));

        $sql = 'SELECT COUNT(*) FROM `vids_likes`
                 WHERE `like` = 1 AND `video_id` = ' . to_sql($item['video_id']);
        $like = DB::result($sql);

        $sql = 'SELECT COUNT(*) FROM `vids_likes`
                 WHERE `like` = 0 AND `video_id` = ' . to_sql($item['video_id']);
        $dislike = DB::result($sql);

        $data = array('like' => $like, 'dislike' => $dislike);

        DB::update('vids_video', $data, 'id = ' . to_sql($item['video_id']));
    }


	$ids = DB::column("SELECT id FROM vids_video WHERE user_id=" . to_sql($user_id, "Number") . "");
	foreach($ids as $id) CVidsTools::delVideoById($id, true);

    $sql = 'SELECT id FROM vids_comment WHERE user_id = ' . to_sql($user_id, "Number");
    $vidsComments = DB::rows($sql);
    if($vidsComments) {
        foreach($vidsComments as $vidsComment) {
            CVidsTools::delCommentById($vidsComment['id']);
        }
    }

    LiveStreaming::deleteUserLive($user_id);
	// delete from vids

    Wall::removeByUid($user_id);

    $sql = 'DELETE FROM im_contact_replied WHERE user_id = ' . to_sql($user_id, "Number");
    DB::execute($sql);

    $sql = 'SELECT * FROM im_contact_replied WHERE user_to = ' . to_sql($user_id, "Number");
    $imContactsWaitingReply = DB::rows($sql);
    if($imContactsWaitingReply) {
        foreach($imContactsWaitingReply as $imContactWaitingReply) {
            $sql = 'DELETE FROM im_contact_replied
                WHERE user_id = ' . to_sql($imContactWaitingReply['user_id'], "Number") . '
                    AND user_to = ' . to_sql($user_id, "Number");
            DB::execute($sql);
            CIm::updateUserReplyRate($imContactWaitingReply['user_id']);
        }
    }

    AudioGreeting::delete();

    ImAudioMessage::deleteByUid($user_id);

    PushNotification::deleteTokensByUserId($user_id);

    $photoCommentLikes = DB::select('photo_comments_likes', '`user_id` = ' . to_sql($user_id, "Number") . ' OR `comment_user_id` = ' . to_sql($user_id, "Number"));
    if($photoCommentLikes) {
        $getSrc = $_GET;
        foreach($photoCommentLikes as $photoCommentLike) {
            $_GET = array(
                'type' => 'photo',
                'cid' => $photoCommentLike['cid'],
                'like' => 0,
                'user_id' => $photoCommentLike['photo_user_id'],
                'id' => $photoCommentLike['photo_id'],
            );
            CProfilePhoto::updateLikeComment();
        }
        $_GET = $getSrc;
    }

    $videoCommentLikes = DB::select('vids_comments_likes', '`user_id` = ' . to_sql($user_id, "Number") . ' OR `comment_user_id` = ' . to_sql($user_id, "Number"));
    if($videoCommentLikes) {
        $getSrc = $_GET;
        foreach($videoCommentLikes as $videoCommentLike) {
            $_GET = array(
                'type' => 'video',
                'cid' => $videoCommentLike['cid'],
                'like' => 0,
                'user_id' => $videoCommentLike['video_user_id'],
                'id' => 'v_' . $videoCommentLike['video_id'],
            );
            CProfilePhoto::updateLikeComment();
        }
        $_GET = $getSrc;
    }

    DB::delete('flashchat_messages', '`user_id` = ' . to_sql($user_id, 'Number'));
    DB::delete('flashchat_users', '`user_id` = ' . to_sql($user_id, 'Number'));

	DB::execute("DELETE FROM user WHERE user_id=" . to_sql($user_id, "Number") . "");
}

function uploadBlog($user_id, $id)
{
	global $g;
	$name = "image";
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
	{
		$im = new Image();
		if ($im->loadImage($_FILES[$name]['tmp_name']))
		{
			$im->resizeCropped($g['image']['small_x'], $g['image']['small_y'], $g['image']['logo'], 0);
			$im->saveImage($g['path']['dir_files'] . "blog/" . $id . ".jpg", $g['image']['quality']);
		}
		$im = new Image();
		if ($im->loadImage($_FILES[$name]['tmp_name']))
		{
			$im->resizeCropped(112, 114, $g['image']['logo'], 0);
			$im->saveImage($g['path']['dir_files'] . "blog/" . $id . "_r.jpg", $g['image']['quality']);
		}
	}
}
function deleteBlog($id)
{
	global $g;
	if (custom_file_exists($g['path']['dir_files'] . "blog/" . $id . ".jpg")) unlink($g['path']['dir_files'] . "blog/" . $id . ".jpg");
	if (custom_file_exists($g['path']['dir_files'] . "blog/" . $id . "_r.jpg")) unlink($g['path']['dir_files'] . "blog/" . $id . "_r.jpg");

}

function validatephoto($name)
{
	global $g, $g_options;//,$l;

	//$ret = "";
	//$exts = Array("gif", "jpg", "jpeg", "png");

	$isImageUploadData = get_param_int('image_upload_data');
	if (isset($_FILES[$name])
		&& ($isImageUploadData ? file_exists($_FILES[$name]["tmp_name"]) : is_uploaded_file($_FILES[$name]["tmp_name"])))
	{
        if (!Image::isValid($_FILES[$name]["tmp_name"])) {
            return l('file_type_incorrect');
        }

		if ($_FILES[$name]["size"] > ceil($g['options']["photo_size"] * 1024 * 1024)) {
            return str_replace("{size}",$g['options']["photo_size"], l('file_size_larger'));
        }

		/*$sP = "";
		foreach ($exts as $ext)
		{
			if ($sP != "") $sP .= "|";
			$sP .= "(\." . $ext . ")";
		}
		$sP = "/(" . $sP . ")$/i";

		if (preg_match($sP, $_FILES[$name]['name']) != 1)
		{
			return l('file_type_incorrect');
		}*/
	} elseif (!isset($_FILES[$name]) || $_FILES[$name]["tmp_name"] == "" or $_FILES[$name]["size"] == 0) {
		return str_replace("{size}",$g['options']["photo_size"],l('profile_photo_incorrect'));
	} else {
		if ($g_options['photo_need'] == "Y") return 0;
	}

	return;
}
function uploadphoto($user_id, $photo_name, $description, $vis = 0, $dir = "", $url = false, $name = "photo_file", $access = false, $unlink = false, $isCity = false)
{
	global $g;

    $file = false;

	$isImageUploadData = get_param_int('image_upload_data');
    if (isset($_FILES[$name])
			&& ($isImageUploadData ? file_exists($_FILES[$name]["tmp_name"]) : is_uploaded_file($_FILES[$name]["tmp_name"]))) {
        $file = $_FILES[$name]['tmp_name'];
    } elseif($url) {
        $file = $url;
    } elseif($dir) {
        $file = $dir;
    }

    $photo_id = 0;
    $im = new Image();
	if ($im->loadImage($file)) {
        if ($vis !== 'P') {
			if ($g['options']['photo_approval'] == 'N' || $isCity) {
				DB::execute("UPDATE user SET is_photo='Y' WHERE user_id=" . to_sql($user_id, "Number") . "");
				$vis = 'Y';
			} else {
                $vis = 'N';
                if(Common::isEnabledAutoMail('approve_image_admin')) {
                    $vars = array(
                        'name'  => User::getInfoBasic($user_id,'name'),
                    );
                    Common::sendAutomail(Common::getOption('administration', 'lang_value'), Common::getOption('info_mail', 'main'), 'approve_image_admin', $vars);
                }
			}
		}

        $table = 'photo';
        $folder = '';
        $isCityVisitor = City::isVisitorUser();
        if ($isCityVisitor) {
            $table = City::getTable('city_photo');
            $folder = 'city/';
        }
        $photo_name = strip_tags($photo_name);
        $description = strip_tags($description);
        $date = date('Y-m-d H:i:s');

        $hash = Common::generateFileNameHash();

		$in_custom_folder = "N";
		$custom_folder_id = 0;

		$sql_1 = "";
		$sql_2 = "";
		if ($access=="private"){
			$sql_1 = "private,";
			$sql_2 = '"Y",';
		}else if ($access=="personal"){
			$sql_1 = "personal,";
			$sql_2 = '"Y",';
		}else if ($access=="folder"){
			$sql_1 = "in_custom_folder,";
			$sql_2 = '"Y",';
		} elseif (strpos($access, 'folder_') === 0) {
            $access_parts = explode('folder_', $access, 2);
			$in_custom_folder = "Y";
			$custom_folder_id = $access_parts[1];
        }
        
        $sql = 'INSERT INTO ' . $table . ' (user_id, photo_name, description, visible, date, in_custom_folder, custom_folder_id, '.$sql_1.' hash)
                VALUES (' . to_sql($user_id, 'Number') . ',
                        ' . to_sql($photo_name, 'Text') . ',
                        ' . to_sql($description, 'Text') . ',
                        ' . to_sql($vis, 'Text') . ',
                        ' . to_sql($date) . ',
						' . to_sql($in_custom_folder) . ',
						' . to_sql($custom_folder_id) . ',
                        '.$sql_2.'
                        ' . to_sql($hash) . ')';

		DB::execute($sql);

		$photo_id = DB::insert_id();
		
        $sFile_ = CProfilePhoto::createBasePhotoFilePath($user_id, $photo_id, $hash);
		//popcorn modified s3 bucket photo upload 2024-05-06
		if(isS3SubDirectory($sFile_)) {
			$sFile_ = CProfilePhoto::createBasePhotoUploadFilePath($user_id, $photo_id, $hash);
		}

		$extFile = Gallery::getExtUploadFile($file);

        $checkGif = $isCity ? false : true;
        if($isCity) {
            $extFile = 'jpg';
        }

        if(CProfilePhoto::createBasePhotoFile($im, $sFile_, $file, $checkGif)) {
            if(!$isCity && Common::isOptionActive('nudity_filter_enabled')) {
                $nudityFilter = new NudityFilter(false, Common::getOption('nudity_filter_threshold'), $im->imageCopy);
                if($nudityFilter->isPorn()) {
                    if($vis !== 'P') {
                        DB::update($table, array('visible' => 'Nudity', 'nudity' => 1), 'photo_id = ' . $photo_id);
                        if($g['options']['photo_approval'] != 'Y') {
                            if(Common::isEnabledAutoMail('approve_image_admin')) {
                                $vars = array(
                                    'name'  => User::getInfoBasic($user_id,'name'),
                                );
                                Common::sendAutomail(Common::getOption('administration', 'lang_value'), Common::getOption('info_mail', 'main'), 'approve_image_admin', $vars);
                            }
                        }
                    } else {
                        set_session('photo_nudity_' . $photo_id, true);
                    }
                }
            }
            CProfilePhoto::createPhotoSizesPreviews($sFile_, $im->imageCopy, $photo_id);

			if ($extFile == 'gif') {
				DB::update($table, array('gif' => 1), 'photo_id = ' . $photo_id);
			}
            // Fix upload error, move_uploaded_file don't work here
            @copyUrlToFile($file, $sFile_ . "src.{$extFile}");
            //$im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
            @chmod($sFile_ . "src.{$extFile}", 0777);
        }

        $fileTypes = CProfilePhoto::getSizes();
        foreach($fileTypes as $fileType) {
			if ($extFile == 'gif' && in_array($fileType, CProfilePhoto::$sizesAllowedGifPhoto)) {
				$isExistsFile = file_exists($sFile_ . $fileType . '.gif');
			} else {
				$isExistsFile = file_exists($sFile_ . $fileType . '.jpg');
			}
            if(!$isExistsFile) {
                // error - delete files
                deletephoto($user_id, $photo_id);
                return 0;
            }
        }

        CProfilePhoto::addPhotoFileSizes($sFile_);

        if (!$isCityVisitor) {
            User::setAvailabilityPublicPhoto(guid());
            //For Urban in CProfilePhoto::publishPhotos()
            if (Common::getOption('set', 'template_options') != 'urban') {
                Wall::addItemForUser($photo_id, 'photo', guid());
            }
        }
	}
    if ($unlink || $isImageUploadData) {
        @unlink($file);
    }

    return $photo_id;
}

//check is default photo set
function photoDefaultCheck(){
	global $g_user;
	$nsc_couple_id = $g_user['nsc_couple_id'];
	$is_nsc_couple_page = get_param('is_nsc_couple_page', 0);
    // current default photo
    $sql = 'SELECT photo_id FROM photo
        WHERE user_id = ' . to_sql($is_nsc_couple_page ? $nsc_couple_id : guid(), 'Number') . '
            AND `default` = "Y"
        LIMIT 1';
    $photoDefault = DB::result($sql);
    if($photoDefault == 0){
        $sql = "UPDATE photo
            SET `default`='Y'
            WHERE user_id = " . to_sql($is_nsc_couple_page ? $nsc_couple_id : guid(), 'Number') .
            ' LIMIT 1';
        DB::execute($sql);
    }
}

function updatephoto($user_id, $photo_id, $url = '')
{
	global $g;
	$name = "photo_file";

	$sFile_ = $g['path']['dir_files'] . "photo/" . $user_id . "_" . $photo_id . "_";

    $file = false;

	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
	{
		$file = $_FILES[$name]['tmp_name'];
	}
	elseif($url)
	{
		$file = $url;
	}

    if($file) {
        do_load_picture($sFile_, $file);
    }
}

function do_load_picture($destination_fname_base, $source_fname)
{
	global $g;
	$im = new Image();

	if ($im->loadImage($source_fname)) {
		$im->resizeWH($g['image']['big_x'], $g['image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
		$im->saveImage($destination_fname_base . "b.jpg", $g['image']['quality']);
		@chmod($destination_fname_base . "b.jpg", 0777);
	}
	if ($im->loadImage($destination_fname_base . "b.jpg", $g['image']['quality'])) {
		$im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
		$im->saveImage($destination_fname_base . "m.jpg", $g['image']['quality']);
		@chmod($destination_fname_base . "m.jpg", 0777);
	}
	if ($im->loadImage($destination_fname_base . "b.jpg", $g['image']['quality'])) {
		$im->resizeCropped($g['image']['small_x'], $g['image']['small_y'], $g['image']['logo'], 0);
		$im->saveImage($destination_fname_base . "s.jpg", $g['image']['quality']);
		@chmod($destination_fname_base . "s.jpg", 0777);
	}
	if ($im->loadImage($destination_fname_base . "b.jpg", $g['image']['quality'])) {
		$im->resizeCropped($g['image']['root_x'], $g['image']['root_y'], $g['image']['logo'], 0);
		$im->saveImage($destination_fname_base . "r.jpg", $g['image']['quality']);
		@chmod($destination_fname_base . "r.jpg", 0777);
	}
    if ($im->loadImage($source_fname)) {
		$im->saveImage($destination_fname_base . "src.jpg", $g['image']['quality_orig']);
		@chmod($destination_fname_base . "src.jpg", 0777);
	}


}



function validatephoto_uploader($name)
{
	global $g,$l;

	$file = $g['path']['dir_files'] . "temp/".$name.".dat";

	if(!file_exists($file)) {
		@unlink($file);
		//print "1 $file";
		return str_replace("{size}",$g['options']["photo_size"],isset($l['all']['profile_photo_incorrect']) ? $l['all']['profile_photo_incorrect'] : "Photo incorect (file size larger than {size} Mb or unknown file type)<br>");
	}

	if(filesize($file) == 0) {
		@unlink($file);
		return str_replace("{size}",$g['options']["photo_size"],isset($l['all']['profile_photo_incorrect']) ? $l['all']['profile_photo_incorrect'] : "Photo incorect (file size larger than {size} Mb or unknown file type)<br>");
	}

	// test photo file type
	$img_sz = custom_getimagesize($file);

	if($img_sz[2]<1 || $img_sz[2]>3) {
		@unlink($file);
		return str_replace("{size}",$g['options']["photo_size"],isset($l['all']['profile_photo_incorrect']) ? $l['all']['profile_photo_incorrect'] : "Photo incorect (file size larger than {size} Mb or unknown file type)<br>");
	}
	$file_size_message = isset($l['all']['file_size_larger']) ? $l['all']['file_size_larger'] : "File size larger than {size} Mb<br>";
	if(filesize($file) > $g['options']["photo_size"] * 1024 * 1024) {
		@unlink($file);
		return str_replace("{size}",$g['options']["photo_size"],$file_size_message);
	}

	return "";
}
function uploadphoto_uploader($user_id, $name, $photo_name, $description, $vis = 0, $dir = "")
{
	global $g;

	$file = $g['path']['dir_files'] . "temp/".$name.".dat";

	if(custom_file_exists($file))
	{
		if ($g['sql']['photo_vis'] == "") DB::execute("UPDATE user SET is_photo='Y' WHERE user_id=" . to_sql($user_id, "Number") . "");
		DB::execute("
			INSERT INTO photo (user_id, photo_name, description, visible)
			VALUES (
			" . to_sql($user_id, "Number") . ",
			" . to_sql($photo_name, "Text") . ",
			" . to_sql($description, "Text") . ",
			" . ($vis == 1 ? "'N'" : "'N'") . "
			)"
		);
		$photo_id = DB::insert_id();

		$sFile_ = $g['path']['dir_files'] . "photo/" . $user_id . "_" . $photo_id . "_";
		$im = new Image();

		if ($im->loadImage($file)) {
			$im->resizeWH($g['image']['big_x'], $g['image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
			$im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
			@chmod($sFile_ . "b.jpg", 0777);
			// DELETE tmp file
			@unlink($file);
		}
		if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
			$im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "m.jpg", $g['image']['quality']);
			@chmod($sFile_ . "m.jpg", 0777);
		}
		if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
			$im->resizeCropped($g['image']['small_x'], $g['image']['small_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "s.jpg", $g['image']['quality']);
			@chmod($sFile_ . "s.jpg", 0777);
		}
		if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
			$im->resizeCropped($g['image']['root_x'], $g['image']['root_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "r.jpg", $g['image']['quality']);
			@chmod($sFile_ . "r.jpg", 0777);
		}
		@unlink($file);
	}
}




function uploadpict($id,$dir,$name = "photo_file",$col=2)
{
	global $g;
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
	{
		$sFile_ = $g['path']['dir_files'] .$dir."/".$id . "_";
		$im = new Image();

		if ($im->loadImage($_FILES[$name]['tmp_name'])  && $col>=4)
		{
			$im->resizeWH($g['image']['big_x'], $g['image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
			$im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
			@chmod($sFile_ . "b.jpg", 0777);
		}
		if ($im->loadImage($_FILES[$name]['tmp_name']) && $col>=3)
		{
			$im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "m.jpg", $g['image']['quality']);
			@chmod($sFile_ . "m.jpg", 0777);
		}
		if ($im->loadImage($_FILES[$name]['tmp_name']) && $col>=2)
		{
			$im->resizeCropped($g['image']['small_x'], $g['image']['small_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "s.jpg", $g['image']['quality']);
			@chmod($sFile_ . "s.jpg", 0777);
		}
		if ($im->loadImage($_FILES[$name]['tmp_name']) && $col>=1)
		{
			$im->resizeCropped($g['image']['root_x'], $g['image']['root_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "r.jpg", $g['image']['quality']);
			@chmod($sFile_ . "r.jpg", 0777);
		}
	}
}

function uploadpict_uploader($id,$dir,$name,$col=2)
{
	global $g;

	// check if file is image

	if(!validatephoto_uploader($name)){

		$sFile_ = $g['path']['dir_files'] .$dir."/".$id . "_";
		$im = new Image();

		$file = $g['path']['dir_files'] . "temp/".$name.".dat";

		if ($im->loadImage($file)  && $col>=4)
		{
			$im->resizeWH($g['image']['big_x'], $g['image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
			$im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
			@chmod($sFile_ . "b.jpg", 0777);
		}
		if ($im->loadImage($file) && $col>=3)
		{
			$im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "m.jpg", $g['image']['quality']);
			@chmod($sFile_ . "m.jpg", 0777);
		}
		if ($im->loadImage($file) && $col>=2)
		{
			$im->resizeCropped($g['image']['small_x'], $g['image']['small_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "s.jpg", $g['image']['quality']);
			@chmod($sFile_ . "s.jpg", 0777);
		}
		if ($im->loadImage($file) && $col>=1)
		{
			$im->resizeCropped($g['image']['root_x'], $g['image']['root_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . "r.jpg", $g['image']['quality']);
			@chmod($sFile_ . "r.jpg", 0777);
		}
		@unlink($file);

	}
}

function deletephoto($user_id, $photo_id, $groupId = 0)
{
	global $g;
    $optionSetTemplate = Common::getOption('set', 'template_options');

	//popcorn modified s3 bucket photo 2024-05-03 start
	$photo = DB::row('SELECT * FROM `photo` WHERE `photo_id` = ' . to_sql($photo_id) . ' AND `user_id` = ' . $user_id . ' LIMIT 1');
	$sFile_ = $g['path']['dir_files'] . "photo/" . $photo_id . "_" . $photo['hash'] . "_";
	//popcorn modified s3 bucket photo 2024-05-03 end

    CProfilePhoto::subtractPhotoFileSizes($sFile_);

    CProfilePhoto::deleteFiles($sFile_);

    if ($optionSetTemplate == 'urban') {
        $photoItemWall = DB::result('SELECT `wall_id` FROM `photo` WHERE `photo_id` = ' . to_sql($photo_id));
    }
	DB::execute("DELETE FROM photo WHERE user_id=" . to_sql($user_id, "Number") . " AND photo_id=" . to_sql($photo_id, "Number"));
	DB::execute("DELETE FROM photo_face_user_relation WHERE user_photo_id=" . to_sql($user_id, "Number") . " AND photo_id=" . to_sql($photo_id, "Number"));

    if (!$groupId) {
        $c = DB::result("SELECT count(photo_id) FROM photo WHERE user_id=" . to_sql($user_id, "Number") . " " . $g['sql']['photo_vis'] . "");
        if ($c == 0) DB::execute("UPDATE user SET is_photo='N' WHERE user_id=" . to_sql($user_id, "Number") . "");

        User::setAvailabilityPublicPhoto($user_id);
    }

    // remove photo comments
    $sql = 'SELECT * FROM photo_comments
        WHERE photo_id = ' . to_sql($photo_id, 'Number');
    $rows = DB::rows($sql);
    foreach($rows as $row) {
        Wall::remove('photo_comment', $row['id'], $row['user_id']);
    }

	DB::execute('DELETE FROM `photo_comments` WHERE `photo_id` = ' . to_sql($photo_id, 'Number'));
    DB::execute('DELETE FROM `photo_comments_likes` WHERE `photo_id` = ' . to_sql($photo_id, 'Number'));
    DB::execute('DELETE FROM `photo_rate` WHERE `photo_id` = ' . to_sql($photo_id, 'Number'));

    DB::execute('DELETE FROM `photo_likes` WHERE `photo_id` = ' . to_sql($photo_id, 'Number'));

    if ($optionSetTemplate == 'urban') {
        Wall::remove('photo_default', $photo_id, $user_id);
        if ($photoItemWall) {
            $countPhotoToItemWall = DB::count('photo', '`wall_id` = ' . to_sql($photoItemWall));
            if ($countPhotoToItemWall) {
                $wallParams = DB::count('photo', '`visible` = "Y" AND `wall_id` = ' . to_sql($photoItemWall));
                if ($wallParams) {
                    $wallParams = 1;
                }
                DB::update('wall', array('params' => $wallParams), '`id` = ' . to_sql($photoItemWall));
            } else {
                Wall::remove('photo', 0, $user_id, $photoItemWall);
            }
            Wall::deleteItemForUserByItemOnly($photo_id, 'photo');
        }
    } else {
        Wall::remove('photo', $photo_id, $user_id);
        Wall::remove('photo_default', $photo_id, $user_id);
        Wall::deleteItemForUserByItemOnly($photo_id, 'photo');
    }

    DB::delete('users_reports', '`photo_id` = ' . to_sql($photo_id));

    CProfilePhoto::deleteTags($photo_id);
	DB::delete('photo_face_user_relation', '`photo_id` = ' . to_sql($photo_id));
}

function deletephotos($user_id, $groupId = 0)
{
	global $g;

    $where = '';
    if ($groupId) {
        $where = ' AND `group_id` = ' . to_sql($groupId);
    }
	$photos = DB::rows("SELECT photo_id FROM photo WHERE user_id=" . to_sql($user_id, "Number") . $where);
    if($photos) {
        foreach($photos as $photo) {
            deletephoto($user_id, $photo['photo_id'], $groupId);
        }
    }
	CProfilePhoto::removeRelationUserFaceDetect($user_id);
}

function validateVideo($name)
{
	global $g,$l;
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
	{
		if ($_FILES[$name]["size"] > ceil($g['options']["video_size"] * 1024 * 1024))
				{
	$file_size_message = isset($l['all']['file_size_larger']) ? $l['all']['file_size_larger'] : "File size larger than {size} Mb<br>";
	return str_replace("{size}",$g['options']["video_size"],$file_size_message);
				}

	}
	elseif ($_FILES[$name]["tmp_name"] == "" or $_FILES[$name]["size"] == 0)
	{
		return str_replace("{size}",$g['options']["video_size"],isset($l['all']['profile_video_incorrect']) ? $l['all']['profile_video_incorrect'] : "Video incorect (file size larger than {size} Mb or unknown file type)<br>");
	}

	return;
}
function uploadVideo($user_id, $video_id, $video_name, $description, $main = 0, $vis = 0, $dir = "")
{
	global $g;
	$name = "video_file";

	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
	{
		if ($main == 1)
		{
			DB::execute("UPDATE video SET main='N' WHERE user_id=" . to_sql($user_id, "Number"));
		}

		if ($video_id == "")
		{

			$path = pathinfo($_FILES[$name]["name"]);
			if (strtolower($path['extension']) == "avi" or
				strtolower($path['extension']) == "mpg" or
				strtolower($path['extension']) == "mpeg" or
				strtolower($path['extension']) == "mof" or
				strtolower($path['extension']) == "sfw" or
				strtolower($path['extension']) == "wmv")
			{
				DB::execute("
					INSERT INTO video (user_id, type, main, video_name, description, visible)
					VALUES (
					" . to_sql($user_id, "Number") . ",
					'" . $path['extension'] . "',
					" . ($main == 1 ? "'Y'" : "'N'") . ",
					" . to_sql($video_name, "Text") . ",
					" . to_sql($description, "Text") . ",
					" . ($vis == 1 ? "'Y'" : "'N'") . "
					)"
				);
				$video_id = DB::insert_id();
			}
		}
		else
		{
			if ($main == 1)
			{
				DB::execute("
					UPDATE video SET main='Y'
					WHERE user_id=" . to_sql($user_id, "Number") . " AND video_id=" . to_sql($video_id, "Number")
				);
			}
		}

		$sFile_ =  $g['path']['dir_files'] . "video/" . $user_id . "_" . $video_id . "." . $path['extension'];

		if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
			$path = pathinfo($_FILES[$name]["name"]);
			if (strtolower($path['extension']) == "avi" or
				strtolower($path['extension']) == "mpg" or
				strtolower($path['extension']) == "mpeg" or
				strtolower($path['extension']) == "mov" or
				strtolower($path['extension']) == "sfw" or
				strtolower($path['extension']) == "wmv") {

				move_uploaded_file($_FILES[$name]["tmp_name"], $sFile_);
				@chmod($sFile_, 0777);
			}
		}
	}
}
function deleteVideo($user_id, $video_id)
{
	global $g;
	$sFile_ = $g['path']['dir_files'] . "video/" . $user_id . "_" . $video_id ;
	if (is_writable($sFile_ . ".avi")) unlink($sFile_ . ".avi");
	if (is_writable($sFile_ . ".mpeg")) unlink($sFile_ . ".mpeg");
	if (is_writable($sFile_ . ".wma")) unlink($sFile_ . ".wma");
	DB::execute("DELETE FROM video WHERE user_id=" . to_sql($user_id, "Number") . " AND video_id=" . to_sql($video_id, "Number"));
}

function validateAudio($name)
{
	global $g;
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
	{
		if ($_FILES[$name]["size"] > ceil($g['options']["audio_size"] * 1024 * 1024))
				{
	global $l;
	$file_size_message = isset($l['all']['file_size_larger']) ? $l['all']['file_size_larger'] : "File size larger than {size} Mb<br>";
	return str_replace("{size}",$g['options']["audio_size"],$file_size_message);
	}
	}
	elseif ($_FILES[$name]["tmp_name"] == "" or $_FILES[$name]["size"] == 0)
	{
		#print_r($_FILES);
		return str_replace("{size}",$g['options']["audio_size"],isset($l['all']['profile_audio_incorrect']) ? $l['all']['profile_audio_incorrect'] : "Audio incorect (file size larger than {size} Mb or unknown file type)<br>");
	}

	return;
}
function uploadAudio($user_id, $audio_id, $audio_name, $description, $main = 0, $vis = 0, $dir = "")
{
	global $g;
	$name = "audio_file";

	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
		if ($main == 1) {
			DB::execute("UPDATE audio SET main='N' WHERE user_id=" . to_sql($user_id, "Number"));
		}

		if ($audio_id == "") {
			$path = pathinfo($_FILES[$name]["name"]);
			if (strtolower($path['extension']) == "mp3") {
				DB::execute("
					INSERT INTO audio (user_id, type, main, audio_name, description, visible)
					VALUES (
					" . to_sql($user_id, "Number") . ",
					'" . $path['extension'] . "',
					" . ($main == 1 ? "'Y'" : "'N'") . ",
					" . to_sql($audio_name, "Text") . ",
					" . to_sql($description, "Text") . ",
					" . ($vis == 1 ? "'Y'" : "'N'") . "
					)"
				);
				$audio_id = DB::insert_id();
			}
		} else {
			if ($main == 1) {
				DB::execute("
					UPDATE audio SET main='Y'
					WHERE user_id=" . to_sql($user_id, "Number") . " AND audio_id=" . to_sql($audio_id, "Number")
				);
			}
		}

		$sFile_ = $g['path']['dir_files'] . "audio/" . $user_id . "_" . $audio_id . ".". strtolower($path['extension']);

		if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
			$path = pathinfo($_FILES[$name]["name"]);
			if (strtolower($path['extension']) == "mp3") {
				move_uploaded_file($_FILES[$name]["tmp_name"], $sFile_);
				@chmod($sFile_, 0777);
			}
		}
	}

	$name = "photo_file";

	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"])) {
		$sFile_ = $g['path']['dir_files'] . "audio/" . $user_id . "_" . $audio_id;
		$im = new Image();

		if ($im->loadImage($_FILES[$name]['tmp_name'])) {
			$im->resizeCropped($g['image']['medium_y'], $g['image']['medium_y'], $g['image']['logo'], 0);
			$im->saveImage($sFile_ . ".jpg", $g['image']['quality']);
			@chmod($sFile_ . ".jpg", 0777);
		}
	}
}
function deleteAudio($user_id, $audio_id)
{
	global $g;
	$sFile_ = $g['path']['dir_files'] . "audio/" . $user_id . "_" . $audio_id;
	if (is_writable($sFile_ . ".mp3")) unlink($sFile_ . ".mp3");
	if (is_writable($sFile_ . ".wav")) unlink($sFile_ . ".wav");
	if (is_writable($sFile_ . ".jpg")) unlink($sFile_ . ".jpg");
	DB::execute("DELETE FROM audio WHERE user_id=" . to_sql($user_id, "Number") . " AND audio_id=" . to_sql($audio_id, "Number"));
}
