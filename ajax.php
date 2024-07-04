<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$g['mobile_redirect_off'] = true;
include('./_include/core/main_start.php');
include_once('./_include/current/vids/tools.php');

$guid = guid();

$siteGuid = get_param('site_guid', false);
if ($siteGuid !== false && $siteGuid != $guid) {
    echo getResponseAjaxByAuth(false);
    die();
}

global $g;
global $g_user;
global $p;

$tmplName = Common::getOption('name', 'template_options');
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
if (get_param('view')=='mobile') {
    $g['path']['url_tmpl'] = '../_frameworks/';
    $g['path']['url_files'] = '../' . $g['dir_files'];
    $dirTmpl = $g['tmpl']['dir_tmpl_mobile'];
}

$cmd = get_param('cmd');
$file = get_param('file');
$upload = get_param('type_upload');
$id = get_param('id');
$isAuth = ($guid) ? true : false;

if ($file != '') {
    include('./_include/current/fileupload.class.php');
    $file = sanitize_upload_name_all($file);
    $upload_dir = Common::getOption('dir_files', 'path');

    if($upload == 'video') {
        $upload_dir = $upload_dir . 'video/';
        $file_save = $file . '.txt';
        $options = array('upload_dir' => $upload_dir,
                         'file_name_save' => $file_save);
        //'max_file_size' => Common::getOption('video_size')
        //'accept_file_types' => '/\.(3gp|avi|flv|3mkv|mov|mpeg|mpg|wmv|mp4)$/i',
        //VideoUpload::upload(0, $file);
        //die();
    }elseif ($upload == 'music') {
        $upload_dir = $upload_dir . 'music/tmp/';
        $file_save = $file . '.mp3';
    }
    $options = array('upload_dir' => $upload_dir,
                     'file_name_save' => $file_save);
    $upload_handler = new UploadHandler($options);

    die();
}
if ($cmd == 'login') {

     $login  = get_param('user');
    $password = get_param('password');
    $login_user = User::getUserByLoginAndPassword($login, $password);


    $login_type = get_param('login_type');
    if($login_type == '2') {
        DB::execute("UPDATE user SET login_type = 0 where user_id ='" . $login_user['user_id'] . "' ;");
        $l_t = $login_type;

        $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . to_sql($login_user['user_id'], 'Number'), 1);
        if($nsc_new_couple_row['orientation']=="5"){
            if($nsc_new_couple_row['nsc_couple_id']>0){
                DB::execute("UPDATE user SET login_type = '0' where user_id ='" . to_sql($login_user['user_id'], 'Number') . "'");
                DB::execute("UPDATE user SET login_type = '0' where user_id ='" . to_sql($login_user['nsc_couple_id'], 'Number') . "'");

            }
        }
    } else if($login_type == '1') {
        DB::execute("UPDATE user SET login_type = '".get_param(('login_type'))."' where user_id ='" . $login_user['user_id'] . "' ;");
        $l_t = $login_type;

        $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . to_sql($login_user['user_id'], 'Number'), 1);
        if($nsc_new_couple_row['orientation']=="5"){
            if($nsc_new_couple_row['nsc_couple_id']>0){
                DB::execute("UPDATE user SET login_type = '0' where user_id ='" . to_sql($login_user['user_id'], 'Number') . "'");
                DB::execute("UPDATE user SET login_type = '0' where user_id ='" . to_sql($login_user['nsc_couple_id'], 'Number') . "'");            }
        }
    } else {
        DB::execute("UPDATE user SET login_type = '".get_param(('login_type'))."' where user_id ='" . $login_user['user_id'] . "' ;");

        $l_t = $login_type;

        $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . to_sql($login_user['user_id'], 'Number'), 1);
        if($nsc_new_couple_row['orientation']=="5"){
            if($nsc_new_couple_row['nsc_couple_id']>0){
                DB::execute("UPDATE user SET login_type = '" . $l_t . "' where user_id ='" . to_sql($login_user['nsc_couple_id'], 'Number') . "'");

            }
        }
    }   



    $result = new CLoginForm('', '', '', '', true);
    $result->action(false);

   
    //echo ;
    die($result->message);
// GEO
}elseif($cmd == 'states') {
    $country = get_param('country');
    $selected = get_param('selected', '');
    echo Common::listStates($country, $selected);
// GEO
} elseif ($cmd == "save_search") {
    $name = trim(get_param('name', ''));
    if ($name == '') $name = htmlentities(l('my_search'), ENT_QUOTES, 'UTF-8');
    echo DB::count('search_save', 'name = ' . to_sql($name, 'Text'));
} elseif ($cmd == "saveoptions") {
    $id = intval(get_param('id', '0'));
    $data = htmlentities(get_param('data', ''), ENT_QUOTES, 'UTF-8');
    $type = get_param('type', '');
    $typeData = get_param('type_data', '');
    switch ($type) {
        case 'status':
            if($id) {
               User::updateProfileStatus($data, $id);
            } else {
               User::updateProfileStatus($data);

            }
            break;

        case 'photo':
            $userId = DB::result("SELECT `user_id` FROM `photo` WHERE `photo_id`=" . $id);
            if ($userId == $g_user['user_id'])
            {
                $sql = "UPDATE `photo`
                           SET `description` = " . to_sql($data, 'Text')
                     . " WHERE `photo_id` = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
            break;

        case 'album':
            $userId = DB::result("SELECT `user_id` FROM `gallery_albums` WHERE `id`=" . $id);
            if ($userId == $g_user['user_id'])
            {
                $sql = "UPDATE `gallery_albums`
                           SET `" . to_sql($typeData, 'Plain')  . "` = " . to_sql($data, 'Text')
                     . " WHERE `id` = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
            break;

        case 'image':
            $userId = DB::result("SELECT `user_id` FROM `gallery_images` WHERE `id`=" . $id);
            if ($userId == $g_user['user_id'])
            {
                $sql = "UPDATE `gallery_images`
                           SET `desc` = " . to_sql($data, 'Text')
                     . " WHERE `id` = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
            break;
        case 'places_place':
            $userId = DB::result("SELECT `user_id` FROM `places_place` WHERE `id`=" . $id);
            if ($userId == $g_user['user_id'])
            {
                $sql = "UPDATE `places_place`
                           SET `" . to_sql($typeData, 'Plain')  . "` = " . to_sql($data, 'Text')
                     . " WHERE `id` = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
            break;
        case 'groups_forum':
            $userId = DB::result("SELECT `user_id` FROM `groups_forum` WHERE `forum_id`=" . $id);
            if ($userId == $g_user['user_id'])
            {
                $sql = "UPDATE `groups_forum`
                           SET `" . to_sql($typeData, 'Plain')  . "` = " . to_sql($data, 'Text')
                     . " WHERE `forum_id` = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
            break;
        case 'event_private_desc':
            $userId = DB::result("SELECT `user_id` FROM `events_event` WHERE `event_id`=" . to_sql($id, 'Number'));
            if ($userId == $g_user['user_id'])
            {
                $sql = "UPDATE `events_event`
                           SET `" . to_sql($typeData, 'Plain')  . "` = " . to_sql($data, 'Text')
                     . " WHERE `event_id` = " . to_sql($id, 'Number');
                DB::execute($sql);
            }
            break;

    }
} elseif ($cmd == "save_state_narrow_box") {
    $type = get_param('type');
    $state = get_param('state');
    if (guid()) {
        $allStateNarrowBox = guser('state_narrow_box');
        if ($allStateNarrowBox != NULL) {
            $data = unserialize(stripcslashes($allStateNarrowBox));
        }
        $data[$type] = $state;
        DB::update('user', array('state_narrow_box' => serialize($data)), '`user_id` = ' . to_sql($g_user['user_id'], 'Number'));
    } else {
        set_cookie('state_narrow_box_' . $type, $state);
    }
// URBAN
}elseif($cmd == 'get_list_info_city') {//not used вроде
	$isAuth = true;
	$sql = 'SELECT city_id, city_title, state_id FROM geo_city
			 WHERE country_id = ' . to_sql(get_param('country_id',0), 'Number') . '
		     ORDER BY city_title ASC';
	$responseData = DB::all($sql);
	if (!$responseData) {
		$responseData = array();
	}
}elseif(in_array($cmd, array('geo_cities', 'geo_states', 'geo_countries'))) {
    $responseData = false;
    $isFilter = intval(get_param('filter'));
    if ($isFilter) {
        $isAuth = true;
    }
    if ($isAuth) {
        $id = get_param('select_id');
        $selected = get_param('selected');
        $list = get_param('list', true);
        //$response['status'] = 1;
        $type = str_replace('geo_', '', $cmd);
        $method = 'list' . ucfirst($type);
        $response['list'] = Common::$method($id, $selected, $list);
        $response['type'] = $type;
        $responseData = $response;
    }
}elseif($cmd == 'geo_param_map') {
    $responseData = false;
    $userId = guid();
    if ($userId) {
        $city = get_param('city');
        $state = get_param('state');
        $country = get_param('country');
        $sql = 'SELECT Ct.lat as lt, Ct.long as ln, Ct.city_title as city, C.country_title as country, S.state_title as state
                  FROM geo_city as Ct, geo_country as C, geo_state as S
                 WHERE Ct.city_id = ' . to_sql($city, 'Number') . '
                   AND C.country_id = Ct.country_id
                   AND S.state_id = ' . to_sql($state, 'Number') . '
                   AND S.state_id = Ct.state_id
                 LIMIT 1';
        $cityInfo = DB::row($sql);
        if (!empty($cityInfo)) {
            $vars = array('country' => $cityInfo['country'],
                          'state' => $cityInfo['state'],
                          'city' => $cityInfo['city'],
                          'country_id' => $country,
                          'state_id' => $state,
                          'city_id' => $city);
            DB::update('user', $vars, '`user_id` = ' . to_sql($userId, 'Number'));

            User::updateFilterLocationChangingUserLocation();
            User::updateGeoPosition($city);

            $response['status'] = 1;
            if (Common::getOption('maps_service') == 'Bing'){
                $response['url'] = Common::getMapImageUrl($cityInfo['lt'] / IP::MULTIPLICATOR, $cityInfo['ln'] / IP::MULTIPLICATOR, 459, 277, 10, TRUE);
            } elseif(Common::getOption('maps_service') == 'Google') {
                $response['url'] = Common::getMapImageUrl($cityInfo['lt'] / IP::MULTIPLICATOR, $cityInfo['ln'] / IP::MULTIPLICATOR, 459, 277, 1, TRUE);
            }
            $response['country'] = l($cityInfo['country']);
            $response['country_id'] = $country;
            $response['state_id'] = $state;
            $response['city_id'] = $city;
            $responseData = $response;
        }
    }
} elseif($cmd == 'upload_song'){
    $upload = isset($_FILES['file_song']) ? $_FILES['file_song'] : null;
    if (is_array($upload)){
        $song = $upload['tmp_name'];
        $fileTempId = $guid . '_' . time() . '_' . mt_rand();
        $fileTempName = $fileTempId . '.mp3';
        $fileTemp = $g['path']['dir_files'] . 'music/tmp/' . $fileTempName;
        //popcorn modified s3 bucket music upload 2024-05-06
        if(isS3SubDirectory($fileTemp)) {
            $fileTemp = $g['path']['dir_files'] . 'temp/' . $fileTempName;
        }
        move_uploaded_file($song, $fileTemp);

        $length = Mp3::getDuration($fileTemp);
        if ($length <= 0) {
            $response = array('error' => l('invalid_song_data_type'));
        } else {
            $response = array(
                'id' => $fileTempId,
                'src' => Songs::getImageDefault(0, 'th_b'),
                'length' => $length
            );
        }
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => l('song_file_upload_failed')));
    }
    die();
} elseif ($cmd == 'photo_add_upload') {
    $type = get_param('type', 'public');
    $isOne = get_param_int('one');

    if($type == 'video'){
        $upload = isset($_FILES["file_{$type}"]) ? $_FILES["file_{$type}"] : null;
        if (is_array($upload)){
            $defCats = CVidsTools::getDefaultCats();
            setses('video_upload_subject', '');
            setses('video_upload_text'   , '');
            setses('video_upload_tags'   , '');
            setses('video_upload_cat'    , implode(',',$defCats));
            setses('video_upload_private', '0');
            setses('video_no_validate', true);

            $resps = array();
            $processes = array();
            $videos = $upload['tmp_name'];

            if ($isOne) {
                $videos = array($upload['tmp_name']);
            }
            foreach ($videos as $index => $value) {
                if (isValidMimeType($value)) {
                    $fileTemp = $guid . '_' . time() . '_' . mt_rand();
                    //VideoUpload::upload(0, sanitize_upload_name_all($value));

                    //popcorn modified s3 bucket video file upload 2024-05-07
                    if(getFileDirectoryType('video') == 2) {
                        move_uploaded_file($value, $g['path']['dir_files'] . "temp/video/" . $fileTemp . ".txt");
                    } else {
                        move_uploaded_file($value, $g['path']['dir_files'] . "video/" . $fileTemp . ".txt");
                    }

                    $processes[] = $id;
                    $r = VideoUpload::upload(0, $fileTemp, 'mp4');
                    if($r === true){
						$id = CVidsTools::insertVideo($fileTemp, 2, false, 1);

                        if ($isOne) {
                            $size = $upload['size'];
                            $type = $upload['type'];
                            $url = $upload['tmp_name'];
                        } else {
                            $size = $upload['size'][$index];
                            $type = $upload['type'][$index];
                            $url = $upload['tmp_name'][$index];
                        }
                        $resp['file_video'][$index] = array(
                            'name' => get_param('file_name'),
                            'size' => $size,
                            'type' => $type,
                            'file_name' => $index,
                            'url' => $url,
                            'deleteUrl' => '',
                            'deleteType' => 'DELETE',
                            'id' => 'v_' . $id,
                            'src_r' => 'video/' . $id . '.jpg',
                            'src_b' => 'video/' . $id . '_b.jpg'
                        );
                        if (!$id) {
                            $resp['file_video'][$index] = array(
                                'error' => l('error_converting_video')
                            );
						}
                    } else {
                        $resp['file_video'][$index]['error'] = $r;
                    }
                } else {
                    $resp['file_video'][$index]['error'] = l('accept_file_types');
                }
            }
            echo json_encode($resp);
        } else {
            echo 'error';
        }
        die();
    } elseif(CProfilePhoto::isActivityEHP()) {
        if(CProfilePhoto::getPhotoCmd() == PhotoCmdsEHP::EVENT_PHOTOS) {
            $event_id = get_param('event_id');
            $event = CEventsTools::retrieve_event_by_id($event_id);
            if($event) {
                $time = DB::result('SELECT NOW()');
                $name = 'file_photo';
                $result =  CEventsTools::do_upload_event_image($name, $event_id, $time, $event['event_private'] ? false : true);

                if (is_array($result)) {
                    echo json_encode($result);
                } else {
                    echo json_encode(array('error' => l('photo_file_upload_failed')));
                }
            } else {
                echo json_encode(array('error' => l('photo_file_upload_failed')));
            }
        } elseif(CProfilePhoto::getPhotoCmd() == PhotoCmdsEHP::HOTDATE_PHOTOS) {
            $hotdate_id = get_param('hotdate_id');
            $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
            if($hotdate) {
                $time = DB::result('SELECT NOW()');
                $name = 'file_photo';
                $result =  ChotdatesTools::do_upload_hotdate_image($name, $hotdate_id, $time, $hotdate['hotdate_private'] ? false : true);

                if (is_array($result)) {
                    echo json_encode($result);
                } else {
                    echo json_encode(array('error' => l('photo_file_upload_failed')));
                }
            } else {
                echo json_encode(array('error' => l('photo_file_upload_failed')));
            }
        } elseif(CProfilePhoto::getPhotoCmd() == PhotoCmdsEHP::PARTYHOU_PHOTOS) {
            $partyhou_id = get_param('partyhou_id');
            $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
            if($partyhou) {
                $time = DB::result('SELECT NOW()');
                $name = 'file_photo';
                $result =  CpartyhouzTools::do_upload_partyhou_image($name, $partyhou_id, $time, $partyhou['partyhou_private'] ? false : true);

                if (is_array($result)) {
                    echo json_encode($result);
                } else {
                    echo json_encode(array('error' => l('photo_file_upload_failed')));
                }
            } else {
                echo json_encode(array('error' => l('photo_file_upload_failed')));
            }
        }

        die();
    } elseif($isOne){
        $responseData = CProfilePhoto::validate(get_param('file_input'));
        if (isset($responseData['error'])) {
            echo json_encode(array('error' => $responseData['error']));
        } else {
            $responseData = CProfilePhoto::photoUpload('', get_param('file_input'));
            if (is_array($responseData)) {
                echo json_encode($responseData);
            } else {
                echo json_encode(array('error' => l('photo_file_upload_failed')));
            }
        }
        die();
    } else {
        $maxFileSize = Common::getOption('photo_size');
        $minW = intval(Common::getOption('min_photo_width_urban', 'image'));
        $minH = intval(Common::getOption('min_photo_height_urban', 'image'));
        $options = array('upload_dir' => $g['path']['dir_files'] . 'temp/',
                                             'upload_url' => $g['path']['url_files'] . 'temp/',
                                             'param_name' => "file_{$type}",
                         //'file_name_save' => get_param('file_name') . '.jpg',
                         'max_file_size' => mb_to_bytes($maxFileSize),
                         'min_width' => $minW,
                         'min_height' => $minH);

        $vars = array('width' => $minW,
                      'height' => $minH);
        $minWH = lSetVars('photo_file_upload_small_width_height', $vars);
        $error = array('max_file_size' => lSetVars('max_file_size', array('size'=>$maxFileSize)),
                       'min_width' => $minWH,
                       'min_height' => $minWH);
        include('./_include/current/fileupload.class.php');
        $upload_handler = new CPhotoUploadHandler($options, true, $error);
        die();
    }
// Profile Edit
} elseif ($cmd == 'pp_profile_edit_main') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new CProfileEditMain('', "{$dirTmpl}_pp_profile_edit_main.html", false, false, false, 'birthday');
    }
} elseif ($cmd == 'profile_edit_main_save') {//Settings
    $responseData = CProfileEditMain::UpdateBasicInfo();
    if ($tmplName == 'edge' && UserFields::isActiveAboutMe() && $responseData && is_array($responseData)) {
        $addResponseData = $responseData;
        $responsePage = new CProfileEditMain('', null, false, false, false, 'profile_about_urban', $guid);
        $responsePage->response = $addResponseData;
    }

} elseif ($cmd == 'profile_edit_save_all') {
    $responsePage = new CProfileEditMain('', null, false, false, false, 'personal_edit_urban_mobile', $guid);
    $profileInfoModule = TemplateEdge::getProfileInfoModule();
} elseif ($cmd == 'pp_profile_about_edit' || $cmd == 'update_about_field') {
    $responsePage = false;
    if ($isAuth) {
        $tmpl = "{$dirTmpl}_pp_profile_edit_about.html";
        if (Common::isOptionActive('profile_basic_fields_edit_to_page', 'template_options')) {
            $tmpl = null;
        }
        $responsePage = new CProfileEditMain('', $tmpl, false, false, false, 'profile_about_urban', $guid);

    }
} elseif ($cmd == 'pp_profile_private_note') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new ProfilePrivateNote('', "{$dirTmpl}_pp_profile_edit_private_note.html");
    }
} elseif ($cmd == 'update_private_note') {
    $responseData = ProfilePrivateNote::update();
} elseif ($cmd == 'pp_profile_edit_field_personal' || $cmd == 'update_personal_field') {
    $responsePage = false;
    if ($isAuth) {
        if ($cmd == 'update_personal_field') {
            $tmpl = "{$dirTmpl}_items_fields_profile.html";
            $typeParse = 'profile_html_urban';
        } else {
            $tmpl = "{$dirTmpl}_pp_profile_edit_personal.html";
            $typeParse = 'personal_edit_urban';
        }
        $responsePage = new CProfileEditMain('', $tmpl, false, false, false, $typeParse, $guid);
        if ($cmd == 'update_personal_field') {
            $responsePage->setAllowedGruops(array(1,2));
            $responsePage->setBanCustomFields(array('location_map', 'interests'));
        } else {
            $responsePage->setFormatValue('entities');
        }
    }
/* Interest */
} elseif ($cmd == 'pp_iterests_category') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new CProfileEditMain('', "{$dirTmpl}_pp_interests_category.html", false, false, false, 'interests_category_urban', $guid);
        $responsePage->setCustomFields(array('interests'));
        $responsePage->setMostPopularInterest(false);
    }
} elseif ($cmd == 'delete_interest') {
    $responseData = Interests::deleteInterest();//error checking
} elseif (in_array($cmd, array('more_interests', 'add_new_interest', 'search_interests'))) {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new Interests('', "{$dirTmpl}_interests_item.html");
    }
/* Interest */
} elseif ($cmd == 'photo_rotate') {
    $responseData = CProfilePhoto::photoRotate();
} elseif ($cmd == 'photo_edit_image') {
    $responseData = CProfilePhoto::updateFromString();
} elseif ($cmd == 'photo_restore_image') {
    $responseData = CProfilePhoto::restoreImage();
} elseif ($cmd == 'set_photo_access') {
    $id = get_param('id');
    $responseData = CProfilePhoto::setPhotoPrivate($id);//error checking
} elseif ($cmd == 'delete_photo') {
    $id = get_param('id');
    $responseData = CProfilePhoto::deletePhoto($id);
/* Gallery */
} elseif ($cmd == 'face_detect_data_save') {
	$responseData = CProfilePhoto::saveFaceDetectData();
} elseif ($cmd == 'face_detect_set_friend') {
	$responseData = CProfilePhoto::setFaceDetectFriend();
} elseif ($cmd == 'face_detect_clear_friend') {
	$responseData = CProfilePhoto::clearFaceDetectFriend();
} elseif ($cmd == 'face_detect_clear_friend_all') {
	$responseData = CProfilePhoto::clearFaceDetectAllFriend();
} elseif ($cmd == 'send_request_private_access') {
    $responseData = CIm::sendRequestPrivateAccess();
} elseif ($cmd == 'publish_photos_gallery') {
	$responseData = false;
    if ($isAuth) {
        $responseData = CProfilePhoto::publishPhotos();//error checking
    }
} elseif ($cmd == 'publish_make_private') { /* Divyesh - Added on 11-04-2024 */
	$responseData = false;
    if ($isAuth) {
        $responseData = CProfilePhoto::privateVideos();
    }
    /* Divyesh - Added on 11-04-2024 */
} elseif ($cmd == 'delete_pending_photos') {
    $responseData = false;
    if ($isAuth) {
		$type = get_param('type');
        if ($type != '') {
            if($type=='video'){
                CProfilePhoto::deleteOldPendingVideos($type);
            } else {
                CProfilePhoto::deleteOldPendingPhotos($type);
            }
            $responseData = true;
        }
    }
} elseif ($cmd == 'publish_one_photo') {
    $responseData = false;
    if ($isAuth) {
		$pid = get_param('photo_id');
        if ($pid) {
            CProfilePhoto::publishOnePhoto($pid);
            $responseData = true;
        }
    }
} elseif ($cmd == 'photo_comment_add') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_comment_template');
        $responsePage = new CProfilePhoto('', $tmpls);
    }
} elseif ($cmd == 'photo_comment_delete') {
    $pid = get_param('pid', 0);
    if (CProfilePhoto::isVideo($pid)){
        $responseData = CVidsTools::deleteCommentVideoByAjax();
    } else {
        $responseData = CProfilePhoto::deleteComment();
    }
} elseif ($cmd == 'comment_like') {
    $responseData = CProfilePhoto::updateLikeComment();
} elseif ($cmd == 'update_media_tags') {
    $responseData = CProfilePhoto::updateTags();
} elseif ($cmd == 'increase_view_count_video') {
    CStatsTools::count('videos_viewed');
    $responseData = CVidsTools::increaseViewCountVideoById(get_param_int('vid'), get_param_int('user_id'));
} elseif ($cmd == 'photo_save_desc') {
    $responseData = CProfilePhoto::savePhotoDescription();
} elseif ($cmd == 'set_photo_default') {
    $responseData = CProfilePhoto::setPhotoDefault();
} elseif ($cmd == 'hide_from_header_picture') {
    $responseData = CProfilePhoto::hideFromHeaderPicture();
} elseif ($cmd == 'get_photo_comment' || $cmd == 'get_video_comment') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_template');
            $responsePage = new CProfilePhoto('', $tmpls);
    }
}

/** popcorn added 2024-05-29 start */
elseif ($cmd == 'get_user_comment') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_template');
            $responsePage = new CProfilePhoto('', $tmpls);
    }
} 
/** popcorn added 2024-05-29 end */

elseif($cmd == 'get_event_photos') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_template');
            $responsePage = new CProfilePhoto('', $tmpls);
    }
}  elseif($cmd == 'get_hotdate_photos') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_template');
            $responsePage = new CProfilePhoto('', $tmpls);
    }
} elseif($cmd == 'get_partyhou_photos') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_template');
            $responsePage = new CProfilePhoto('', $tmpls);
    }
} elseif ($cmd == 'get_blog_post_comment') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'blogs_post_comment_template');
        $responsePage = new Blogs('', $tmpls);
    }
} elseif ($cmd == 'get_comment_replies') {
    $responsePage = false;
    if ($isAuth) {
        $type = get_param('type', 'photo');
        if ($type == 'blogs_post') {
            $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'blogs_post_comment_replies_template');
        } else {
            $tmpls = getPageCustomTemplate('_photo_gallery_items.html', 'pp_gallery_template');
        }

        $responsePage = new CProfilePhoto('', $tmpls);
    }
} elseif ($cmd == 'pp_profile_gallery_photo') {
    $responsePage = false;
    if ($isAuth) {
        CProfilePhoto::$addRatingToInfo = true;
        $responsePage = new CProfilePhoto('', "{$dirTmpl}_pp_photo_gallery.html");
    }
} elseif ($cmd == 'pp_profile_gallery_video') {
    $responsePage = false;
    if ($isAuth) {
		$responsePage = new CProfilePhoto('', "{$dirTmpl}_pp_video_gallery.html");
    }
} elseif ($cmd == 'set_rate_photo') {
    $responseData = CProfilePhoto::setRated();
    if (!empty($responseData)) {
        $page = new CProfilePhoto('', "{$dirTmpl}_photo_gallery_items.html");
        $page = trim(getParsePageAjax($page));
        if (!empty($page)) {
            $responseData['comment'] = $page;
        }
    }
} elseif ($cmd == 'delete_rate_photo') {
    $responseData = CProfilePhoto::deleteRated();
} elseif ($cmd == 'hide_rated_me_item') {
    $responseData = CProfilePhoto::hideRatedMeItem();
} elseif ($cmd == 'set_media_views') {
    $responseData = CProfilePhoto::setMediaViews();
} elseif ($cmd == 'photo_like_add') {
    $responseData = CProfilePhoto::addLike();
/* Gallery */
/* Message */
} elseif ($cmd == 'pp_messages') {
    $responsePage = false;
    if ($isAuth) {
		$showIm = intval(get_param('show_im', 0));
        $typeIm = Common::getOptionTemplate('im_type');
        $uploadImEdge = get_param_int('upload_im') && $typeIm == 'edge';
		if ($showIm || $uploadImEdge) {
			$baseTmpl = "{$dirTmpl}_pp_messages_items.html";
		} else {
			$baseTmpl = "{$dirTmpl}_pp_messages.html";
		}
		$tmpls = array(
			'main' => $baseTmpl,
			'user_list' => "{$dirTmpl}_pp_messages_list_user.html",
			'message_list' => "{$dirTmpl}_pp_messages_list_msg.html",
			'message_list_title' => "{$dirTmpl}_pp_messages_list_msg_title.html",
		);
        $responsePage = new CIm('', $tmpls);
    }
} elseif ($cmd == 'send_message') {
    if ($isAuth) {
        $userTo = get_param('user_to');
        if ($guid == $userTo) {
            $responseData = array('redirect' => 1, 'url' => Common::getHomePage());
        } else {
            $responsePage = new CIm('', "{$dirTmpl}_pp_messages_list_msg.html");
        }
    } else {
        $responsePage = false;
    }
} elseif ($cmd == 'uploading_msg') {
    $tmpl = "{$dirTmpl}_pp_messages_list_msg.html";
    if (get_param('display') == 'open_list_chats') {
        $tmpl = "{$dirTmpl}_pp_list_chats_open_im_msg.html";
    }
    $responsePage = new CIm('', $tmpl);
} elseif ($cmd == 'set_status_users') {//??? not like this
    $responseData = User::setStatusUsersIm();
} elseif ($cmd == 'set_im_sound') {
    $responseData = User::saveImSound();
} elseif ($cmd == 'delete_users_im') {
    $responseData = CIm::closeSelectedIm();
} elseif ($cmd == 'clear_history_messages') {
    if (get_param_int('only_close_im')) {
        $responseData = CIm::closeIm(null, false);
    } else {
        $responseData = CIm::clearHistoryMessages();
    }
    if (get_param_int('get_count_msg_all')) {
        $responseData = array('new_message' => array('count' => CIm::getCountNewMessages(),
                                                     'enabled' => CIm::getCountAllMsgIm()));
    }
} elseif ($cmd == 'im_read_msg') {
	$mid = get_param_int('mid');
	$userFrom = get_param_int('user_from');
	CIm::setMessageAsReadOneMsg($mid, $userFrom, false);
	$responseData = array('new_message' => array('count' => CIm::getCountNewMessages(),
                                                 'enabled' => CIm::getCountAllMsgIm()));
} elseif ($cmd == 'delete_empty_im') {
	$responseData = CIm::closeEmptyIm();

} elseif ($cmd == 'block_user_group') {
    $responseData = Groups::blockFull();
    if($responseData) {
        $groupIdIm = Groups::getParamOneId();
        $groupId = Groups::getParamId();
        $responseData = array();
        if($groupIdIm == $groupId) {
            $responseData = array(
                'group_id' => $groupId,
                'counter' => Groups::getNumberSubscribers(),
                'list_subscribe' =>  TemplateEdge::getListFriends()
            );
        }
    }
} elseif ($cmd == 'unblock_user_group') {
    $responseData = Groups::blockRemove();
    if ($responseData) {
        $groupIdIm = Groups::getParamOneId();
        $groupId = Groups::getParamId();
        $responseData = array();
        if($groupIdIm == $groupId) {
            $responseData = array(
                'group_id' => $groupId,
                'counter' => Groups::getNumberSubscribers()
            );
        }
    }

} elseif ($cmd == 'group_moderator') {
    $responseData = Groups::setModerator();
    if($responseData) {
        $groupId = Groups::getParamId();
        $user_id = get_param('user_id', '');

        $responseData = array(
            'group_id' => $groupId,
            'user_id' => $user_id,
            'status' => 'success'
        );
    }
} elseif ($cmd == 'group_unmoderator') {
    $responseData = Groups::setUnModerator();
    if($responseData) {
        $groupId = Groups::getParamId();
        $user_id = get_param('user_id', '');
        $responseData = array(
            'group_id' => $groupId,
            'user_id' => $user_id,
            'status' => 'success'
        );
    }
} elseif ($cmd == 'block_user'
          || $cmd == 'block_visitor_user'
          || $cmd == 'block_user_rated_photo') {
    $responseData = false;
    $uid = get_param('user_id', 0);
    if ($isAuth && $uid) {
        $responseData = User::blockFull($uid, $cmd == 'block_visitor_user');
        if($responseData) {
            if ($tmplName == 'impact') {
                $responseData = '<script>' . Menu::updateCounterAjaxImpact('', true) . '</script>';
            } elseif ($tmplName == 'impact_mobile') {
                $responseData = Menu::getListCounterImpactMobile();
            } elseif ($tmplName == 'edge') {
                $responseData = array(
                                    'script' => TemplateEdge::updateListFriends($uid),
                                    'wall_only_post' => Wall::isOnlyPostFriends($uid, null, false)
                                );
            } else {
                $responseData = array();
                $responseData['number'] = User::getCountBlocked();
                if ($cmd == 'block_user_rated_photo') {
                    $responseData = CProfilePhoto::getNumberUsersRatedMePhoto();
                }
            }
        }
    }
/* Message */
} elseif ($cmd == 'delete_visitor_user') {
    $responseData = false;
    $uid = get_param('user_id', 0);
    if ($isAuth && $uid) {
        DB::delete('users_view', '`user_from` = ' . to_sql($uid, 'Number') . ' AND `user_to` = ' . to_sql($guid, 'Number'));
        $responseData = true;
    }
/* Contact */
} elseif ($cmd == 'pp_contact') {
    $isAuth = true;

	class CContact extends CHtmlBlock
	{

		function parseBlock(&$html)
		{
			if (!guid()){
				Common::parseCaptcha($html);

				$blocksVisitor = array('contact_email', 'contact_username', 'contact_subject', 'contact_captcha');
				foreach ($blocksVisitor as $block) {
					if ($html->blockExists($block)) {
						$html->parse($block, false);
					}
				}
			}

			parent::parseBlock($html);
		}
	}


	$responsePage = new CContact('', "{$dirTmpl}_pp_contact.html");
/* Contact */
/* City */
} elseif ($cmd == 'pp_profile_city_choose') {
    $responsePage = false;
    $location = get_param_array('location');
    $isSearchList = get_param('is_search_list',false);
    if ($location) {
        $isAuth = true;
    }
    if ($isAuth) {
		$responsePage = new CGeo('', "{$dirTmpl}_pp_choose_city.html");
        $responsePage->isSearchList=$isSearchList;
        if($location) {
            $responsePage->location = $location;
        }
    }
} elseif($cmd=='pp_get_filter_module_location_title'){
    $isAuth = true;
    $country=get_param('country',0);
    $state=get_param('state',0);
    $city=get_param('city',0);

    $location = UsersFilter::getLocationCityTitle(array('country'=>$country,'state'=>$state,'city'=>$city));
    $responseData = $location['city_title'];
} elseif($cmd=='pp_get_find_new_friends_title'){
    $isAuth = true;
    $country=get_param('country',0);
    $state=get_param('state',0);
    $city=get_param('city',0);
    $responseData = UsersFilter::getLocationFindNewFriendsTitle(array('country'=>$country,'state'=>$state,'city'=>$city), get_param_int('radius'));

/* City */
/* Edit loking */
} elseif ($cmd == 'pp_profile_edit_looking') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new CProfileEditMain('', "{$dirTmpl}_pp_edit_looking_for.html", false, false, false, 'edit_looking_for_urban');
    }
} elseif ($cmd == 'update_edit_looking') {
    $responseData = false;
    if ($isAuth) {
        $responseData = UserFields::updateLookingFor($guid);
    }
/* Edit loking */
} elseif ($cmd == 'set_profile_bg') {
    $responseData = false;
    if ($isAuth) {
        $bg = get_param('bg');
        DB::update('user', array('profile_bg'=>$bg), '`user_id` = ' . to_sql($guid, 'Number'));
            echo $bg; die();

        $responseData = true;
        if ($bg) {
            $image = $g['tmpl']['dir_tmpl_main'] . '/images/patterns_sm/' . $bg;
            $linkColor = changeLinkColorOfBackgroundBright($image);
            cache_update("profile_bg_link_color_bright_{$bg}", $linkColor);
            $responseData = $linkColor;
        }
        if (!$responseData) {
            $responseData = 'none';
        }
    }
} elseif ($cmd == 'profile_bg_video') {
    $responseData = false;
    if ($isAuth) {
        $code = get_param_array('code');
        if (!isset($code['title'])) {
            if (isset($code[0]) && $code[0]) {
                $url = 'https://www.youtube.com/oembed?url=youtu.be/' . $code[0];
                $oembed_text = @urlGetContents($url);
                if ($oembed_data = json_decode($oembed_text, true)) {
                    /*$code['ratio'] = @round($oembed_data['width']/$oembed_data['height'], 3)?:1.778;
                    $code['title'] = @$oembed_data['title'];
                    $code['title'] = isset($oembed_data['title']) ? $oembed_data['title'] : '';*/

                    $ratio = 1.778;
                    $width = 0;
                    $height = 0;
                    if (isset($oembed_data['width'])) {
                        $width = $oembed_data['width'];
                    }
                    if (isset($oembed_data['height'])) {
                        $height = $oembed_data['height'];
                    }
                    if ($width && $height) {
                        $ratio = round($oembed_data['width']/$oembed_data['height'], 3);
                    }
                    $code['ratio'] = $ratio;
                    $code['title'] = isset($oembed_data['title']) ? $oembed_data['title'] : '';
                    $code['width'] = $width;
                    $code['height'] = $height;
                } else {
                    $code = '';
                }
            } else {
                $code = '';
            }
        }
        if ($code) {
            //$data = defined('JSON_UNESCAPED_UNICODE') ? json_encode($code, JSON_UNESCAPED_UNICODE) : json_encode($code);
            $data = json_encode($code);
            $responseData = $code;
        } else {
            $data = '{}';
            $responseData = 'get_info_video_error';
        }
        if ($data) User::update(array('profile_bg_video' => $data));
    }
} elseif ($cmd == 'user_unblock') {//??? Not user_block_list.php
    $responseData = false;
    $userTo = get_param('user_to', 0);
    if ($isAuth && $userTo) {
        User::blockRemoveAll($guid, $userTo);
        $responseData = array('number' => User::getCountBlocked());
        if ($tmplName == 'edge') {
            $responseData['wall_only_post'] = Wall::isOnlyPostFriends($userTo, null, false);
        }
    }
/* Gift */
} elseif ($cmd == 'send_gift') {
    $responsePage = new ProfileGift('', "{$dirTmpl}_profile_gift.html");
} elseif ($cmd == 'delete_text_gift') {
    $responseData = ProfileGift::deleteText();
} elseif ($cmd == 'delete_gift') {
    $responseData = ProfileGift::delete(); //error checking
} elseif ($cmd == 'set_state_filter') {
    $responseData = true;
    $state = get_param('state', 0);
    if ($isAuth) {
        $data = array('state_filter_search' => $state);
        DB::update('userinfo', $data, '`user_id` = ' . to_sql(guid()));
    } else {
        set_cookie('state_filter_search', $state);
        //var_dump(get_cookie('state_filter_search'));
    }
/* Gift */
} elseif ($cmd == 'upload_image_wall') {
    $name = get_param('input_name');
    $responseData = CProfilePhoto::validate($name, '');
    if (!$responseData) {
        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $file = $_FILES[$name]['tmp_name'];

			$ext = Gallery::getExtUploadFile($file);

            $fileTemp = Wall::getTempFileUploadImage($ext);
			$fileTempUrl = Common::getOption('url_files', 'path') . Wall::$tempNameFileBase . '.' . $ext;

			$fileTempThName = Wall::$tempNameFileBase . '_th.' . $ext;
			$fileTempTh = Common::getOption('dir_files', 'path') . $fileTempThName;
			$fileTempThUrl = Common::getOption('url_files', 'path') . $fileTempThName;

            @copy($file, $fileTemp);
			@chmod($fileTemp, 0777);
            @unlink($file);

			$fileTempRes = '';
			if ($ext == 'gif') {
				@copy($fileTemp, $fileTempTh);
				@chmod($fileTempTh, 0777);
				$fileTempRes = $fileTempThUrl;
			} else {
				$im = new Image();
				if($im->loadImage($fileTemp)){
					$im->resizeWH(180, 180, false, '', 0, '', '', false);
					$im->saveImage($fileTempTh, 90);
					@chmod($fileTempTh, 0777);
					$fileTempRes = $fileTempThUrl;
				}
			}

			if ($tmplName == 'edge'){
				$responseData = array('status' => 'complete_upload_image',
									  'file' => $fileTempUrl,
									  'ext' => $ext,
									  'file_th' => $fileTempRes,
                                      'image_editor_enabled' => Common::isImageEditorEnabled($ext),
                        );
			} else {
				$responseData = 'complete_upload_image';
			}
        } else {
			if ($tmplName == 'edge'){
				$responseData = array('status' => 'error', 'error' => l('photo_file_upload_failed'));
			} else {
				$responseData = l('photo_file_upload_failed');
			}
        }
    }
} elseif ($cmd == 'send_wink') {
    $responseData = Common::sendWink(get_param('uid'));
} elseif ($cmd == 'remove_wink') {
    $responseData = false;
    $uid = get_param('user_id');
    if($guid && $uid){
        DB::execute('DELETE FROM `users_interest`
                      WHERE `user_to` = ' . to_sql($guid)
                    . ' AND `user_from` = ' . to_sql($uid));
        $responseData = true;
    }
} elseif ($cmd == 'report_user') {
    $responseData = User::sendReport();
} elseif ($cmd == 'get_available_credits') {
    $responseData = $g_user['credits'];
/* TimeZone */
} elseif ($cmd == 'timezone') {
    global $p;
    $pCurrent = $p;
    $p = 'profile_settings.php';
    $time = array('time_utc' => gmdate('Y-m-d H:i:s'),
                  'time_local' => TimeZone::getDateTimeZone(get_param('zone')));
    echo lSetVars('info_timezone', $time);
    $p = $pCurrent;
    die();
/* TimeZone */
} elseif($cmd=='add_greeting_video'){
    $video_id=get_param('video_id',false);
    if($video_id){
        $sql="SELECT user_id FROM vids_video WHERE id=".to_sql($video_id)." LIMIT 1";
        $user_id=DB::result($sql);
        if($user_id==$guid){
            $sql="UPDATE `user` SET `video_greeting`=".to_sql($video_id)." WHERE user_id=".to_sql($guid);
            DB::execute($sql);
            $responseData = true;
        }
    }
/* Impact */
} elseif($cmd == 'set_want_to_meet'){//Like
	$data = MutualAttractions::setWantToMeet();
	if (get_param('display') == 'encounters') {

	} else {
		$responseData = $data;
	}
    $uid = get_param('uid', 0);
    if ($isAuth && intval(get_param('unblock')) && $uid) {
        User::blockRemoveAll($guid, $uid);
        $responseData['number_blocked'] = User::getCountBlocked();
    }

} elseif($cmd=='ads_visible'){
    $responseData = false;
    if ($isAuth) {
        $status = intval(get_param('status'));
        if ($status && !User::accessCheckFeatureSuperPowers('kill_the_ads')) {
            $responseData = 'upgrade';
        } else {
            $ads = json_decode($g_user['hide_ads'], true);
            if (!$ads) {
                $ads = array();
            }
            $ads[$tmplName] = $status;
            DB::update('user', array('hide_ads' => json_encode($ads)), '`user_id` = ' . to_sql($guid));
            $responseData = true;
        }
    }
} elseif($cmd=='init_list_im'){
    $responsePage = false;
    if ($isAuth) {
		$tmpls = array(
            'main' => "{$dirTmpl}_pp_list_chats.html",
			'list_chats' => "{$dirTmpl}_pp_list_chats_open.html",
			'list_chats_item' => "{$dirTmpl}_pp_list_chats_open_item.html",
			'list_chats_im' => "{$dirTmpl}_pp_list_chats_open_im.html",
			'list_chats_im_msg' => "{$dirTmpl}_pp_list_chats_open_im_msg.html",
		);
        $responsePage = new CIm('', $tmpls);
    }
} elseif ($cmd == 'open_im_with_user') {
    $responseData = false;
    if ($isAuth) {
        $tmpls = array(
			'main' => "{$dirTmpl}_pp_list_chats_open_im.html",
			'list_chats_im_msg' => "{$dirTmpl}_pp_list_chats_open_im_msg.html",
		);
        $page = new CIm('', $tmpls);
        $responseData = getParsePageAjax($page);
        $_GET['cmd'] = 'update_im';
        $_GET['display'] = 'open_list_chats';
        $_POST['user_id'] = 0;
        $_POST['last_id'] = get_param('last_id_temp');
        $page = new CIm('', "{$dirTmpl}_pp_list_chats_open_item.html");
		$responseData .= '<div class="update_built_im">' . getParsePageAjax($page) . '</div>';
    }
} elseif ($cmd == 'set_visible_open_im') {
    $responseData = CIm::setVisibleOpenIm();
} elseif ($cmd == 'set_do_not_show_me_visitors') {
    User::update(array('set_do_not_show_me_visitors' => 1));
    $responseData = true;
/* Impact */
} elseif($cmd=='get_visitors'){
	$responseData = User::getUsersVisitors();
} elseif($cmd=='broadcast_open'){
    $responseData = User::setLastBroadcast();
} elseif($cmd=='broadcast_close'){
    $responseData = User::setLastBroadcast('0000-00-00 00:00:00');
} elseif($cmd=='favorite_action'){
    $responseData = User::actionFavorite();
} elseif($cmd=='get_page_info'){
    $isAuth = true;
    $responseData = PageInfo::getInfo(get_param('type'));
} elseif($cmd == 'mark_see_event'){
    $responseData = false;
    if ($isAuth) {
        $responseData = User::markSeenEvent();
    }
} elseif($cmd == 'mark_see_all_event'){
    $responseData = false;
    if ($isAuth) {
		$groupIdEvent = Groups::getEventId();
        $responseData = array(
			'update_list' => User::getListGlobalEvents(null, 'ASC', $groupIdEvent, true, true),
			'count' => User::getNumberGlobalEvents(false, $groupIdEvent)
		);
    }
} elseif($cmd == 'get_more_event'){
    $responseData = false;
    if ($isAuth) {
        $groupId = Groups::getParamId();
        $groupIdEvent = 0;
        if (Groups::isMyGroup()) {
            $groupIdEvent = $groupId;
        }
        $loadEvents = 0;
        $loadLimitDefault = Common::getOptionInt('number_load_notif_events', 'edge_member_settings');
        if (!$loadLimitDefault) {
            $loadLimitDefault = 1;
        }
        $loadLimit = $loadLimitDefault + 1;
        $listEvents = User::getListGlobalEvents($loadLimit, 'DESC', $groupIdEvent);
        if (count($listEvents) > $loadLimitDefault) {
            $loadEvents = 1;
            unset($listEvents[$loadLimitDefault]);
        }
        $responseData = array(
            'data' => $listEvents,
            'load' => $loadEvents
        );
    }
} elseif($cmd == 'save_photo_file') {

    if(CProfilePhoto::updateFromString()) {
        $responseData = array(
            'result' => 'success',
        );
    } else {
        $responseData = array(
            'result' => 'error',
        );
    }

} elseif($cmd == 'save_audio_greeting') {

    if(AudioGreeting::save()) {
        $responseData = array(
            'result' => 'success',
        );
    } else {
        $responseData = array(
            'result' => 'error',
        );
    }
} elseif($cmd == 'save_video_file') {

    if(VideoUpload::updateFromString()) {
        $responseData = array(
            'result' => 'success',
        );
    } else {
        $responseData = array(
            'result' => 'error',
        );
    }
} elseif($cmd == 'save_im_audio_message_IOS') {

    $id = ImAudioMessage::save();

    if($id) {
        $responseData = array(
            'result' => 'success',
            'id' => $id,
        );
    } else {
        $responseData = array(
            'result' => 'error',
        );
    }
} elseif($cmd == 'save_im_audio_message') {
	$responseData = ImAudioMessage::saveBlob();
} elseif($cmd == 'im_audio_message_delete') {
    $id = get_param('id');
	//$responseData = ImAudioMessage::delete($id);
    if(ImAudioMessage::delete($id)) {
        $responseData = array(
            'result' => 'success',
        );
    } else {
        $responseData = array(
            'result' => 'error',
        );
    }
} elseif($cmd == 'group_add') {
    $responseData = Groups::add();
} elseif($cmd == 'group_update') {
    $responseData = Groups::updateInfo();

} elseif($cmd == 'group_check_name_seo') {
    $responseData = Groups::checkNameSeo();
} elseif($cmd == 'delete_temp_photo_group') {
    $responseData = GroupsPhoto::deleteTempPhoto();
} elseif($cmd == 'group_subscribe_action') {
    $uid = get_param_int('user_id', $guid);
    $responseData = Groups::subscribeAction($uid);
} elseif($cmd == 'group_subscribe_action_notif') {
    $uid = get_param_int('uid');
    $responseData = Groups::subscribeAction($uid);
} elseif($cmd == 'group_delete') {
    $responseData = Groups::delete(get_param_int('group_id'));
} elseif($cmd == 'delete_messages') {
    $responseData = CIm::deleteMessages();

} elseif($cmd == 'upload_temp_photo_event') {
    $name = 'event_photo_file';
    $responseData = CProfilePhoto::validate($name, '');
    if (!$responseData) {
        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $file = $_FILES[$name]['tmp_name'];
            $imageTemp = 'temp/tmp_event_' . $guid . '.jpg';
            $fileTemp = Common::getOption('dir_files', 'path') . $imageTemp;
            @copy($file, $fileTemp);
			@chmod($fileTemp, 0777);
            @unlink($file);
            $responseData = array('id' => $guid, 'image' => $imageTemp);
        } else {
            $responseData = array('error' => l('photo_file_upload_failed'));
        }
    }
} elseif($cmd == 'delete_photo_event_social') {//delete all photo
    $eventId = get_param_int('event_id');
    if ($eventId) {
        require_once("./_include/current/events/tools.php");
        $responseData = CEventsTools::delete_event_image_all($eventId);
    } else {
        $responseData = false;
    }
} elseif($cmd == 'task_done') {
    $responseData = TaskCalendar::done();
    if ($responseData !== false) {
        $responseData = array(
            'done' => $responseData
        );
        $date = get_param('date');

        if (get_param_int('uid') == $guid && get_param('date') == date('Y-m-d')) {
            $countNewTask = TaskCalendar::getCountOpenTasksByCurrentDay();
            $newTasksTitle = TaskCalendar::getNotifTitle($countNewTask);
            $responseData['my_open_task'] = array(
                'count' => $countNewTask,
                'title'   => toJs($newTasksTitle)
            );
        }
    }

} elseif ($cmd == 'upload_image_im' || $cmd == 'upload_comment_image') {
    $name = get_param('input_name');
    $responseData = CProfilePhoto::validate($name, '');
    if ($responseData) {
        $responseData = $responseData['error'];
    } else {
        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $file = $_FILES[$name]['tmp_name'];

            $ext = Gallery::getExtUploadFile($file);
			$typeImage = 'tmp_chat';
			if ($cmd == 'upload_comment_image') {
				$typeImage = 'tmp_' . get_param('type');
			}
			$fileTemp = Gallery::getTempFileUploadImageIm($typeImage, $ext);

            @copy($file, $fileTemp);
			@chmod($fileTemp, 0777);
            @unlink($file);
			if ($tmplName == 'edge'){
				$responseData = array('status' => 'complete_upload_image', 'file' => $fileTemp, 'ext' => $ext, 'image_editor_enabled' => Common::isImageEditorEnabled($ext));
			} else {
				$responseData = 'complete_upload_image';
			}
        } else {
			if ($tmplName == 'edge'){
				$responseData = array('status' => 'error', 'error' => l('photo_file_upload_failed'));
			} else {
				$responseData = l('photo_file_upload_failed');
			}
        }
    }
} elseif ($cmd == 'upload_image_im_change') {
	$responseData = CIm::uploadImageChangeEdit();
	$fileUrlTh = get_param('file_th');
	if ($fileUrlTh) {
		$fileUrl = get_param('file_url');
		$im = new Image();
		if($im->loadImage($fileUrl)){
			$im->resizeWH(180, 180, false, '', 0, '', '', false);
			$im->saveImage($fileUrlTh, 90);
		}
	}
} elseif ($cmd == 'search_users_from_name') {
    $responseData = TaskCalendar::searchUsersFromName();
} elseif ($cmd == 'upload_temp_blog_image') {
    $name = get_param('input_name');
    $responseData = CProfilePhoto::validate($name, '');
    if (!$responseData) {
        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $file = $_FILES[$name]['tmp_name'];
            $fileTemp = Blogs::getTempFileUploadImage();
            $fileTempDir = Common::getOption('dir_files', 'path') . $fileTemp;
            @copy($file, $fileTempDir);
			@chmod($fileTempDir, 0777);
            @unlink($file);
            $responseData = array('id' => 0,
                                  'url' => $fileTemp);
        } else {
            $responseData = array('error' => l('photo_file_upload_failed'));
        }
    }
} elseif($cmd == 'blog_add') {
    include('./_include/current/blogs/tools.php');
    $responseData = Blogs::addPost();
} elseif ($cmd == 'blogs_post_comment_add') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = $g['tmpl']['dir_tmpl_main'] . '_blogs_post_comment_item.html';
        $responsePage = new Blogs('', $tmpls);
    }
} elseif ($cmd == 'blogs_post_delete') {
    include('./_include/current/blogs/tools.php');
    $responseData = Blogs::deletePost();
} elseif ($cmd == 'blogs_post_comment_delete') {
    $responseData = Blogs::deleteComment();
} elseif ($cmd == 'blogs_post_comment_like') {
    $responseData = Blogs::updateLikeComment();
} elseif ($cmd == 'blogs_post_like' || $cmd == 'blogs_post_unlike') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = $g['tmpl']['dir_tmpl_main'] . '_blogs_post_ajax.html';
        $responsePage = new Blogs('', $tmpls);
    }
} elseif ($cmd == 'mobile_app_push_token_add') {
    $responseData = PushNotification::addToken();
} elseif ($cmd == 'mobile_app_push_token_delete') {
    $responseDataNotAuth = PushNotification::deleteToken();
} elseif ($cmd == 'get_location_user') {
    $responseDataNotAuth = CityUser::getUserCurrentLocationInfo();
/* Live stream */
} elseif ($cmd == 'live_stream_create') {
    $responseData = LiveStreaming::createLive();
} elseif ($cmd == 'live_stream_start' || $cmd == 'live_stream_start_viewer') {
    if ($cmd != 'live_stream_start_viewer'){
        LiveStreaming::setLiveStart();
    }

    $responsePage = false;
    if ($isAuth ) {
        $_GET['id'] = LiveStreaming::getVideoId(get_param_int('live_id'));
        $tmpls = array('main' => "{$dirTmpl}_live_streaming_content.html",
                       'list_viewers' => "{$dirTmpl}_live_streaming_list_viewers.html",
                       'likes' => "{$dirTmpl}_live_streaming_likes.html",
                       'comments_list' => "{$dirTmpl}_live_streaming_comment_item.html");
        $responsePage = new LiveStreaming('', $tmpls);
    }
} elseif ($cmd == 'live_stream_stop') {
    $responseData = LiveStreaming::setLiveStop();
} elseif ($cmd == 'live_stream_chunk_save') {
    $responseData = LiveStreaming::saveRecordChunk();
} elseif ($cmd == 'live_stream_comment_like') {
    $responseData = LiveStreaming::updateLikeComment();
} elseif ($cmd == 'live_stream_like') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new LiveStreaming('', "{$dirTmpl}_live_streaming_likes.html");
    }
} elseif ($cmd == 'get_live_stream_comment') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = array(
            'main' => "{$dirTmpl}_live_streaming_comment_items.html",
            'comments_list' => "{$dirTmpl}_live_streaming_comment_item.html"
        );
        $responsePage = new LiveStreaming('', $tmpls);
    }
} elseif ($cmd == 'get_live_stream_comment_replies') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new LiveStreaming('', "{$dirTmpl}_live_streaming_comment_item.html");
    }
} elseif ($cmd == 'live_stream_comment_add') {
    $responsePage = false;
    if ($isAuth) {
        $responsePage = new LiveStreaming('', "{$dirTmpl}_live_streaming_comment_item.html");
    }
} elseif ($cmd == 'live_stream_save_record_file') {
    $responseDataNotAuth = LiveStreaming::saveRecordFile();
} elseif ($cmd == 'live_stream_comment_delete') {
    $responsePage = false;
    if ($isAuth) {
        $tmpls = array(
            'main' => "{$dirTmpl}_live_streaming_comment_items.html",
            'comments_list' => "{$dirTmpl}_live_streaming_comment_item.html"
        );
        $responsePage = new LiveStreaming('', $tmpls);
    }
} elseif ($cmd == 'live_stream_post_wall') {
    $responseData = LiveStreaming::postWall();

} elseif ($cmd == 'live_search') {
    $responseData = LiveStreaming::searchLive();
} elseif ($cmd == 'live_stream_paid') {
    $responseData = LiveStreaming::paid();
/* Live stream */

} elseif ($cmd == 'song_increase_plays') {
    $responseData = Songs::increasePlays();
} elseif ($cmd == 'publish_songs') {
	$responseData = false;
    if ($isAuth) {
        $responseData = Songs::publishSongs();
    }
} elseif ($cmd == 'upload_temp_song_image') {
    $name = 'song_image';
    $responseData = CProfilePhoto::validate($name);
    if (!$responseData) {
		$responseData = array('error' => l('photo_file_upload_failed'));
        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $file = $_FILES[$name]['tmp_name'];

            $fileTempName = get_param('id') . '.jpeg';
            $fileTemp = 'music/tmp/' . $fileTempName;
            $fileTemp_1 = $g['path']['dir_files'] . $fileTemp;

            //popcorn modified s3 bucket music image upload 2024-05-06
            if(isS3SubDirectory($fileTemp_1)) {
                $fileTemp = 'temp/' . $fileTempName;
                $fileTemp_1 = $g['path']['dir_files'] . $fileTemp;
            }

			if (move_uploaded_file($file, $fileTemp_1)) {
				@chmod($fileTemp_1, 0777);
				$responseData = array('src' => $fileTemp);
			}
        }
    }
} elseif ($cmd == 'upload_default_photo') {
	$inputName = get_param('input_name');
	$responseData = CProfilePhoto::validate($inputName);
    if (!isset($responseData['error'])) {
		$responseData = array('error' => l('photo_file_upload_failed'));
		$pid = CProfilePhoto::photoUpload('', $inputName, false, true);
		if ($pid) {
			$photos = array(array('id' => $pid, 'desc' => '', 'set_photo_default' => 1));
			$result = CProfilePhoto::publishPhotos($photos, 'public', null, true);
			if (isset($result['data']['photo_default'])) {
				$responseData = $result;
                $responseData['data']['isImageEditorEnabled'] = Common::isImageEditorEnabled();
				if ($responseData['data']['photo_default'] != $pid) {
					$responseData = array('error' => l('the_photo_is_awaiting_approval_by_the_admin'));
				}
			}
		}
	}
} elseif ($cmd == 'upload_profile_cover') {
	$inputName = get_param('input_name');
	$responseData = CProfilePhoto::validate($inputName, '');
	if (!isset($responseData['error'])) {
		$responseData = array('error' => l('photo_file_upload_failed'));
		if (isset($_FILES[$inputName]) && is_uploaded_file($_FILES[$inputName]['tmp_name'])) {
            $file = $_FILES[$inputName]['tmp_name'];
			$info = @getimagesize( $file);
			if ($info && isset($info[0]) && isset($info[1])) {
				$groupId = get_param_int('group_id');
				$maxW = 1920;
				$minW = Common::getOptionInt('min_width_profile_cover_img', 'edge_member_settings');
				if ($minW > $maxW || !$minW) {
					$minW = $maxW;
				}
				$minH = Common::getOptionInt('min_height_profile_cover_img', 'edge_member_settings');
				if (!$minH) {
					$minH = 310;
				}
				$imgW = $info[0];
				$imgH = $info[1];
				if ($imgW < $minW || $imgH < $minH) {
					$vars = array('width' => $minW,
								  'height' => $minH);
					$responseData = array('error' => lSetVars('photo_file_upload_small_width_height', $vars));
				} else {
					$ext = Image::isAnimatedGif($file, true) ? 'gif' : 'jpg';
					$prf = $groupId ? '_' . $groupId : '';
					$fileName = 'tmp_cover_' . $guid . $prf . '.' . $ext;
					$fileTemp = 'temp/' . $fileName;
					$fileTempDir = $g['path']['dir_files'] . $fileTemp;

					if ($ext == 'jpg' && $imgW > $maxW) {
						$im = new Image();
						if($im->loadImage($file)){
							$im->resizeWH($maxW, $imgH, false, '', 0, '', '', false);
							$im->saveImage($fileTempDir, 100);
							@chmod($fileTempDir, 0777);
							$responseData = array('src' => $g['path']['url_files'] . $fileTemp, 'filename' => $fileName);
						}
					} elseif(move_uploaded_file($file, $fileTempDir)){
						@chmod($fileTempDir, 0777);
						$responseData = array('src' => $g['path']['url_files'] . $fileTemp, 'filename' => $fileName);
					}

				}
			}
		}
	}
} elseif ($cmd == 'set_profile_cover') {
	$responseData = User::setProfileBgCover();
} elseif ($cmd == 'clear_profile_cover') {
	$responseData = User::clearProfileBgCover();
}


if (isset($responsePage)) {
    echo getResponsePageAjaxByAuth($isAuth, $responsePage);
}

if (isset($responseData)) {
    echo getResponseAjaxByAuth($isAuth, $responseData);
}
// URBAN

if(isset($responseDataNotAuth)) {
    echo getResponseDataAjax($responseDataNotAuth);
}

DB::close();