<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

use function PHPUnit\Framework\fileExists;

class LiveStreaming extends CommentsBase
{

    static $table = 'live_streaming';
    static $tableViewers = 'live_streaming_viewers';
    static $tableVideo = 'vids_video';
    static $tableVideoComments = 'vids_comment';
    static $tableVideoTags = 'vids_tags';
    static $tableVideoTagsRel = 'vids_tags_relations';

    static $liveId = 0;
    static $isGetDataWithFilter = false;

    static function includePath()
    {
        return dirname(__FILE__) . '/../../';
    }

    static function createLive()
    {
        $responseData = false;
        $guid = guid();
        $uid = get_param_int('uid');

        global $p;

        $event_id = get_param('event_id', '');
        $hotdate_id = get_param('hotdate_id');
        $partyhou_id = get_param('partyhou_id');

        $ehp_id = 0;
        $ehp_type = '';
        if($event_id) {
            $ehp_id = $event_id;
            $ehp_type = 'event';
        } elseif($hotdate_id) {
            $ehp_id = $hotdate_id;
            $ehp_type = 'hotdate';
        } elseif($partyhou_id) {
            $ehp_id = $partyhou_id;
            $ehp_type = 'partyhou';
        }
 
        $group_id = get_param('group_id', '0');
        if ($uid) {
            $defCats = CVidsTools::getDefaultCats();

            setses('video_upload_subject', trim(get_param('title')));
            setses('video_upload_text'   , '');
            setses('video_upload_tags'   , l('live_streaming_tag_default'));
            setses('video_upload_cat'    , implode(',', $defCats));
            setses('video_upload_private', '0');
            setses('video_no_validate', true);

            $videoId = CVidsTools::insertVideo('', 2, false, 1, true);

            CVidsTools::cleanVideoInfoToSession();

            $_GET['type'] = 'video';
            $_GET['photo_id'] = $videoId;
            $_GET['tags'] = l('live_streaming_tag_default');

            $data = array(
                'user_id' => $uid,
                'video_id' => $videoId,
                'hash' => hash_generate(64),
            );
            if (IS_DEMO) {
                $data['demo_session'] = get_param('demo_session');
            }

            DB::insert(self::$table, $data);
            $liveId = DB::insert_id();

            DB::update('vids_video', array('live_id' => $liveId, 'group_id' => $group_id), '`id` = ' . to_sql($videoId));
            
            CProfilePhoto::updateTags();

            $responseData = array('id' => $liveId, 'hash' => $data['hash']);
        }
        return $responseData;
    }

    static function deleteLive($id)
    {
        DB::delete(self::$table, 'id = ' . to_sql($id));
        DB::delete(self::$tableViewers, 'live_id = ' . to_sql($id));
    }

    static function deleteUserLive($uid)
    {
        $lives = DB::select(self::$table, 'user_id = ' . to_sql($uid));
        if ($lives) {
            foreach ($lives as $key => $live) {
                self::deleteLive($live['id']);
            }
        }
    }

    static function deleteUserBrokenLive($uid = 0)
    {
        require_once("vids/tools.php");

        $time = time() - 120;
        $where = "date_start != '0000-00-00 00:00:00'"
          . " AND date_stop = '0000-00-00 00:00:00'"
          . " AND status != 0"
          . " AND status < " . to_sql($time);
        if ($uid) {
            $where .= " AND user_id = " . to_sql($uid);
        }

        $lives = DB::select(self::$table, $where);
        if ($lives) {
            foreach ($lives as $key => $live) {
                self::deleteLive($live['id']);
                CVidsTools::delVideoById($live['video_id'], true);
            }
        }
    }

    static function postWall()
    {
        global $g;

        $liveId = get_param_int('live_id');
        $image = get_param('image');
        if($liveId && preg_match("/^data:image\/(?<extension>(?:png|gif|jpg|jpeg));base64,(?<image>.+)$/", $image, $matchings)){
            $imageData = base64_decode($matchings['image']);
            $extension = $matchings['extension'];

            $liveInfo = self::getInfoLive($liveId);
            $videoId = $liveInfo['video_id'];
            $videoInfo1 = DB::row('SELECT * FROM vids_video WHERE `id` =' . to_sql($videoId));


            $group_id = $videoInfo1['group_id'];
            if(!$group_id) {
                $group_id = 0;
            }

            $file = $g['path']['dir_files'] . "temp/live_image_" . $videoId . '.jpg';// . $extension;
            if($videoId && file_put_contents($file, $imageData)){
                $image = new Image();
                if ($image->loadImage($file)) {

                    if(getFileDirectoryType('video') == 2) {
                        $fileVideo = $g['path']['dir_files'] . 'temp/video/' . $videoId;
                    } else {
                        $fileVideo = $g['path']['dir_files'] . 'video/' . $videoId;
                    }

                    $watermarkParams = CProfilePhoto::watermarkParams();

                    $sizesBasePhoto = array(
                        'src'=> array('_src'),
                        'bm' => array('_bm', $g['image']['big_mobile_x'], $g['image']['big_mobile_y']),
                        'b'  => array('_b', 286, 161),
                        'o'  => array('', 160, 120),
                    );
                    $first = true;
                    $isPostWall = true;

                    foreach($sizesBasePhoto as $size => $sizeConfig) {
                        if($first) {
                            if((memory_get_usage() * 2 + 1024 * 1024 * 16) < getMemoryLimitInBytes()) {
                                $image->imageCopy = Image::copyImageResource($image->image);
                            } else {
                                // use only if need prevent loading image from drive when low memory
                                //$image->saveImageResourceCopy = true;
                            }
                        } else {
                            if($image->imageCopy) {
                                $image->image = $image->imageCopy;
                            } else {
                                $image->loadImage($file);
                            }
                            $image->saveImageResourceCopy = true;
                        }
                        if ($size == 'bm') {
                            $image->resizeWH($sizeConfig[1], $sizeConfig[2], false, $g['image']['logo'], $watermarkParams['font_size'], $watermarkParams['file'], $watermarkParams['position']);
                        } elseif ($size == 'src') {

                        } else {
                            $image->resizeCroppedMiddle($sizeConfig[1], $sizeConfig[2], $g['image']['logo'], 0);
                        }

                        $fileName = $fileVideo . $sizeConfig[0] . '.jpg';
                        if(!$image->saveImage($fileName, $g['image']['quality'])) {
                            if ($size == 'bm') {
                                $isPostWall = false;
                            }
                        } else {
                            @chmod($fileName, 0777);
                        }
                        $image->clearImage();

                        $first = false;
                    }

                    //rename($file, $fileVideo . '_src.jpg');

                    $path = array($fileVideo . '.jpg', $fileVideo . '_b.jpg', $fileVideo . '_bm.jpg', $fileVideo . '_src.jpg');
                    Common::saveFileSize($path);

                    //popcorn modified s3 bucket video
                    if(getFileDirectoryType('video') == 2) {
                        if(file_exists($g['path']['dir_files'] . 'temp/video/' . $videoId . '.jpg')) {
                            custom_file_upload($g['path']['dir_files'] . 'temp/video/' . $videoId . '.jpg', 'video/' . $videoId . '.jpg');
                        }
                        if(file_exists($g['path']['dir_files'] . 'temp/video/' . $videoId . '_b.jpg')) {
                            custom_file_upload($g['path']['dir_files'] . 'temp/video/' . $videoId . '_b.jpg', 'video/' . $videoId . '_b.jpg');
                        }
                        if(file_exists($g['path']['dir_files'] . 'temp/video/' . $videoId . '_bm.jpg')) {
                            custom_file_upload($g['path']['dir_files'] . 'temp/video/' . $videoId . '_bm.jpg', 'video/' . $videoId . '_bm.jpg');
                        }
                        if(file_exists($g['path']['dir_files'] . 'temp/video/' . $videoId . '_src.jpg')) {
                            custom_file_upload($g['path']['dir_files'] . 'temp/video/' . $videoId . '_src.jpg', 'video/' . $videoId . '_src.jpg');
                        }
                    }

                    if (file_exists($file)) {
                        unlink($file);
                    }

                    if ($isPostWall) {
                        $wallId = Wall::add('vids', $videoId, false, '', false, 0, 'profile', 0, '', $group_id, 1);
                        DB::update(self::$tableVideo, array('wall_id' => $wallId), '`id` = ' . to_sql($videoId));

                        DB::update(self::$table, array('is_upload_photo' => 1, 'wall_id' => $wallId), '`id` = ' . to_sql($liveId));
                    }

                    //popcorn modified s3 bucket video
                    if(getFileDirectoryType('video') == 2) {
                        $responseData = custom_getFileDirectUrl($g['path']['url_files'] . 'video/' . $videoId . '_bm.jpg');
                    } else {
                        $responseData = $g['path']['url_files'] . 'video/' . $videoId . '_bm.jpg';
                    }
                    
                    // $responseData = $g['path']['url_files'] . 'video/' . $videoId . '_bm.jpg';

                } else {
                    $responseData = false;
                }
            } else {
                $responseData = false;
            }

        } else {
            $responseData = false;
        }

        return $responseData;
    }

    static function getInfoLive($id, $field = false, $dbIndex = 0, $cache = true)
    {
        $keyField = $field ? $field : 0;
        $key = 'live_streaming_info_' . $id;
        $info = null;
        if ($cache) {
            $info = Cache::get($key);
        }
        if($info === null) {
            $sql = 'SELECT * FROM `' . self::$table . '` WHERE `id` = ' . to_sql($id, 'Number');
            $info = DB::row($sql, $dbIndex);

            Cache::add($key, $info);
        }

        $return = $info;

        if($field !== false) {
            $return = isset($info[$field]) ? $info[$field] : '';
        }

        return $return;
    }

    static function updateLive($liveId, $data)
    {
        DB::update(self::$table, $data, 'id = ' . to_sql($liveId));

        $key = 'live_streaming_info_' . $liveId;
        $info = Cache::get($key);
        if($info) {
            $info = array_merge($info, $data);
            Cache::add($key, $info);
        }
    }

    static function getVideoId($id)
    {
        $videoId = 0;
        if ($id) {
            $videoId = self::getInfoLive($id, 'video_id');
        }
        return $videoId;
    }

    static function checkStatusLive($time, $currentTime = null)
    {
        if ($currentTime == null) {
            $currentTime = time();
        }

        $time = intval($time);
        $d = 60;//If time is more than 2 minutes
        $d1 = abs($currentTime - $time);

        if($time && $d1 && $d1 > $d){
            $time = 0;
        }

        return $time;
    }

    static function setStatusLive($liveId = null, $liveTime = null)
    {

        $liveIdEnd = get_param_int('live_id_end');
        if ($liveIdEnd) {
            return;
        }

        if ($liveId === null) {
            $liveId = get_param_int('live_id');
        }

        if ($liveTime === null) {
            $liveTime = get_param_int('live_time');
        }

        if ($liveId && $liveTime) {
            $liveTime = self::checkStatusLive($liveTime);
            self::updateLive($liveId, array('status' => $liveTime));

            $data = array('live_now_id' => $liveTime ? $liveId : 0,
                          'live_now_status' => $liveTime);
            User::update($data);
        }
    }


    static function getUserLiveNowId($uid)
    {
        if (!self::isAviableLiveStreaming()) {
            return 0;
        }

        $userInfo = User::getInfoBasic($uid);
        if (!$userInfo || !$userInfo['live_now_id']) {
            return 0;
        }

        if (IS_DEMO) {
            $demoLive = self::getInfoLive($userInfo['live_now_id'], 'demo', DB_MAX_INDEX);
            if ($demoLive) {
                return $userInfo['live_now_id'];
            }
        }
        $timeoutSec = 10000;
        $timeoutSecServer = $timeoutSec/1000*1.5;

        $time = $userInfo['live_now_status'];
        $currentTime = time();
        $time = self::checkStatusLive($time, $currentTime);

        if (!$time) {
            return 0;
        }

        return (($currentTime - $time) <= $timeoutSecServer) ? $userInfo['live_now_id'] : 0;
    }

    static function getIdByLiveStreaming($callId, $type = 'livestream')
    {
        if ($type) {
            $type = '_' . $type;
        }
        $key = domain() . '_' . $type . '_' . $callId;
        $key = str_replace(array('.'), '_', $key);
        return $key;
    }

    static function setLiveStart()
    {
        $liveId = get_param_int('live_id');

        $time = time();
        $data = array('live_now_id' => $liveId,
                      'live_now_status' => $time);
        User::update($data);

        $data = array('date_start' => date('Y-m-d H:i:s'),
                      'status' => $time);
        self::updateLive($liveId, $data);

        return true;
    }

    static function setLiveStop()
    {
        $liveId = get_param_int('live_id');

        $data = array('live_now_id' => 0,
                      'live_now_status' => 0);
        User::update($data);

        self::setLiveStopOne($liveId);

        return true;
    }

    static function setLiveStopOne($liveId)
    {
        $liveInfo = self::getInfoLive($liveId);
        if ($liveInfo) {
            if ($liveInfo['wall_id']) {
                Wall::removeById($liveInfo['wall_id']);
                $data = array('photo_id' => $liveInfo['video_id'], 'video' => 1, 'wall_id' => 0);
                DB::update('users_reports', $data, '`wall_id` = ' . to_sql($liveInfo['wall_id']));
            }

            DB::update(self::$tableVideo, array('wall_id' => 0), 'id = ' . to_sql($liveInfo['video_id']));

            $data = array('date_stop' => date('Y-m-d H:i:s'),
                          'status' => 0,
                          'wall_id' => 0);
            self::updateLive($liveId, $data);
        }
    }

    static function saveRecordChunk()
    {
        global $g;

        $guid = guid();
        $liveId = get_param_int('id');
        $id = self::getVideoId($liveId);

        $responseData = array('error' => 'Live streaming save error.');
        $upload = isset($_FILES['file_ls_video']) ? $_FILES['file_ls_video'] : null;
        $inc = get_param_int('inc');
        $save = get_param_int('save');
        if ($save) {
            self::setLiveStop();
        }

        if (is_array($upload) && $id){

            $video = $upload['tmp_name'];

            if (isValidMimeType($video)) {
                $fileTemp = 'live_stream_' . $guid . '_' . $liveId . '_' . $inc;
                move_uploaded_file($video, $g['path']['dir_files'] . "temp/{$fileTemp}.txt");

                $r = VideoUpload::upload(0, $fileTemp, 'mp4', 'temp', !$inc);
                if($r === true){
                    if ($save) {
                        $fileLiveSaveTemp = 'live_stream_' . $guid . '_' . $liveId;
                        $fileLiveSave = VideoUploadFfmpeg::concatChunks($fileLiveSaveTemp, $id, $inc);
                        if ($fileLiveSave){

                            $filesImage = array(
                                $fileLiveSaveTemp . '_0.jpg'     => '.jpg',
                                $fileLiveSaveTemp . '_0_b.jpg'   => '_b.jpg',
                                $fileLiveSaveTemp . '_0_src.jpg' => '_src.jpg',
                            );
                            foreach ($filesImage as $key => $ext) {
                                $fileExists = $g['path']['dir_files'] . 'temp/' . $key;
                                if (file_exists($fileExists)) {
                                    if(getFileDirectoryType('video') == 2) {
                                        rename($fileExists, $g['path'][''] . 'temp/video/' . $id . $ext);
                                    } else {
                                        rename($fileExists, $g['path'][''] . 'video/' . $id . $ext);
                                    }
                                }
                            }
                            $active = 1;
                            if (Common::isOptionActive('video_approval')){
                                //$active = 3;
                            }
                            $data = array('active' => $active);
                            DB::update('vids_video', $data, '`id` = ' . to_sql($id));

                            $responseData = array('video' => 'save');
                        } else {
                            $responseData = array('error' => l('error_converting_video'));
                        }
                    } else {
                        $responseData = array('chunk' => $inc);
                    }
                } else {
                    $responseData = array('error' => $r);
                }
            } else {
                $responseData = array('error' => l('accept_file_types'));
            }
        }
        return $responseData;
    }

    static function saveRecordFile_OLD_ALL()
    {
        global $g;

        $guid = guid();
        $liveId = get_param_int('id');
        $id = self::getVideoId($liveId);

        self::setLiveStop();

        $responseData = array('error' => 'Live streaming save error.');
        $upload = isset($_FILES['file_ls_video']) ? $_FILES['file_ls_video'] : null;
        if (is_array($upload) && $id){

            $video = $upload['tmp_name'];

            if (isValidMimeType($video)) {
                $fileTemp = $guid . '_' . time() . '_' . mt_rand();
                
                //popcorn modified s3 bucket video
                if(getFileDirectoryType('video') == 2) {
                    move_uploaded_file($video, $g['path']['dir_files'] . "temp/video/" . $fileTemp . ".txt");
                } else {
                    move_uploaded_file($video, $g['path']['dir_files'] . "video/" . $fileTemp . ".txt");
                }

                $r = VideoUpload::upload(0, $fileTemp, 'mp4');
                if($r === true){
                    $saveFile = VideoUpload::upload($id, $fileTemp);

                    if ($saveFile) {

                        $active = 1;
                        if (Common::isOptionActive('video_approval')){
                            //$active = 3;
                        }
                        $data = array('active' => $active);
                        DB::update('vids_video', $data, '`id` = ' . to_sql($id));

                        $size = $upload['size'];
                        $type = $upload['type'];

                        $urlFiles = $g['path']['url_files'];
                        $responseData = array(
                            'id' => $id,
                            'src_v' => $urlFiles . 'video/' . $id . '.mp4',
                            'src_r' => $urlFiles . 'video/' . $id . '.jpg',
                            'src_b' => $urlFiles . 'video/' . $id . '_b.jpg',
                            'size' => $size,
                            'type' => $type,
                        );
                    } else {
                        $responseData = array('error' => l('error_converting_video'));
                    }
                } else {
                    $responseData = array('error' => $r);
                }
            } else {
                $responseData = array('error' => l('accept_file_types'));
            }
        }
        return $responseData;
    }

    static function saveRecordFile()
    {
        global $g;

        $result = 'Something wrong';

        $liveId = get_param_int('live_id');
        $hash = get_param('hash');

        $where = '`id` = ' . to_sql($liveId) . '
            AND `hash` = ' . to_sql($hash) . '
            AND is_upload_video = 0';
        $video = DB::one(self::$table, $where);

        if($video) {
            $urlVideo = get_param('video');
            $fileSize = get_param('size');

            if ($urlVideo && $fileSize) {
                $videoId = $video['video_id'];
                if(VideoUpload::downloadReadyFiles($videoId, $urlVideo, $fileSize)) {
                    /* Control the end of the live */
                    if (self::getInfoLive($liveId, 'status')) {
                        self::setLiveStopOne($liveId);
                    }
                    /* Control the end of the live */

                    $wallId = Wall::add('vids', $videoId, $video['user_id']);

                    $videoInfo1 = DB::row('SELECT * FROM vids_video WHERE `id` =' . to_sql($videoId));

                    $group_id = $videoInfo1['group_id'];
                    if(!$group_id) {
                        $group_id = 0;
                    }
                    DB::update('wall', array('group_id' => $group_id), '`id` = ' . to_sql($wallId));

                    $data = array('is_upload_video' => 1,
                                  'is_upload_photo' => 1,
                                  'wall_id' => $wallId);
                    DB::update(self::$table, $data, '`id` = ' . to_sql($liveId));

                    if(Common::isOptionActive('video_approval')){
                        $active = 3;
                    } else {
                        $active = 1;
                    }

                    $data = array('active' => $active,
                                  'code' => 'site:' . $videoId,
                                  'wall_id' => $wallId);
                    DB::update(self::$tableVideo, $data, '`id` = ' . to_sql($videoId));

                    $sql = 'UPDATE `user` SET vid_videos = vid_videos + 1 WHERE user_id = ' . to_sql($video['user_id']);
                    DB::execute($sql);

                    $result = 'saved';
                } else {
                    $result = 'error_save_files ' . VideoUpload::$error;
                }
            } else {
                $result = 'error_no_video_param';
            }

        } else {
            $result = 'error_live_stream_not_found';
        }
        $responseData = array('result' => $result);

        return $responseData;
    }

    /* Comments */
    static function setInfoLive(&$html, $videoInfo, $liveId)
    {
        if ($videoInfo) {
            $vars = array(
                'id' => $liveId,
                'last_action_like' => $videoInfo['last_action_like'],
                'last_action_comment' => $videoInfo['last_action_comment'],
                'comments_count' => $videoInfo['count_comments'],
                //'like_user_list' => '',
                'last_action_comment_like' => $videoInfo['last_action_comment_like']
            );
            $html->assign('ls', $vars);
        }
    }

    static function parseListViewers(&$html, $liveId)
    {
        global $g;

        $isDemoViewer = get_param_int('live_demo_viewer');

        $block = 'ls_list_viewers_item';

        if (IS_DEMO && $isDemoViewer) {
            $viewers = DB::select('user', '', '', 20);
        } else {
            $viewers = DB::select(self::$tableViewers, '`live_id` = ' . to_sql($liveId));
        }

        //var_dump_pre($viewers);
        //$viewers = DB::select('user', '', 'RAND()', 115);
        //$viewers = array();

        $viewersCount = count($viewers);
        $html->setvar('ls_list_viewers_count', $viewersCount);
        $html->setvar('ls_list_viewers_title', lSetVars('watching_now', array('count' => $viewersCount)));
        $guid = guid();

        foreach ($viewers as $key => $user) {
            $userInfo = User::getInfoBasic($user['user_id']);
            $vars = array(
                'user_id'  => $user['user_id'],
                'url'      => User::url($user['user_id'], $userInfo),
                'name'     => $userInfo['name'],
                'photo'    => $g['path']['url_files'] . User::getPhotoDefault($user['user_id'], 'r'),
            );
            $html->assign($block, $vars);
            $html->subcond($user['user_id'] != $guid, "{$block}_chat");
            $html->parse($block, true);
        }
    }

    static function parseBlockComments(&$html, $id)
    {
        global $g;

        //return;
        $guid = guid();

        $cmd = get_param('cmd');

        $liveId = 1;
        $liveId = get_param_int('live_id');
        $vars = array(
            'guid'     => $guid,
            'id'       => $liveId,
            'video_id' => $id
        );
        $html->assign('ls', $vars);

        if ($id) {
            $videoInfo = DB::one(self::$tableVideo, '`id` = ' . to_sql($id));
            if (!$videoInfo) {
                return;
            }
            self::setInfoLive($html, $videoInfo, $liveId);
        } else {
            return;
        }

        $userInfo = User::getInfoBasic($guid);
        $vars = array(
                'uid'   => $guid,
                'photo' => User::getPhotoDefault($guid, 'r', false, $userInfo['gender']),
                'url'   => User::url($guid)
        );
        $html->assign('comment_guid', $vars);

        if ($cmd == 'live_stream_start_viewer'){
            
            //popcorn modified s3 bucket video 
            if(getFileDirectoryType('video') == 2) {
                $urlImage = custom_getFileDirectUrl($g['path']['url_files'] . 'video/' . $videoInfo['id'] . '_bm.jpg');
            } else {
                $urlImage = $g['path']['url_files'] . 'video/' . $videoInfo['id'] . '_bm.jpg';
            }
            $fileImage = $g['path']['dir_files'] . 'video/' . $videoInfo['id'] . '_bm.jpg';

            if (custom_file_exists($fileImage)) {
                $html->setvar('set_url_image_live', $urlImage);
                $html->parse('set_url_image_live', false);
            }

            if ($videoInfo['subject']) {
                $html->setvar('set_live_description', toJs($videoInfo['subject']));
                $html->parse('set_live_description', false);
            }

            if(!in_array($guid, explode(',', $videoInfo['users_reports']))){
                $html->setvar('ls_report_video_id', $videoInfo['id']);
                $html->setvar('ls_report_user_id', $videoInfo['user_id']);
                $html->parse('ls_report_user', false);
            }
        }

        $comments_enabled = true;
        if ($comments_enabled && $id) {

            self::parseListViewers($html, $liveId);

            self::parseLikes($html, $id);
            self::parseComments($html, $id);

            $html->parse('ls_module_action', false);
        }
    }

    static function addComment()
    {
        global $g_user;

        $msg = trim(get_param('comment'));
        $videoId = get_param_int('id');

        $guid = guid();
        $commentInfo = array();
        $path = self::includePath();

        $msg = censured($msg);

        $videoInfo = DB::row('SELECT * FROM `' . self::$tableVideo . '` WHERE `id` = ' . to_sql($videoId));

        $groupId = 0;
        $groupUserId = 0;
        $videoUserId = 0;
        if ($videoInfo) {
            $videoUserId = $videoInfo['user_id'];
            $groupId = $videoInfo['group_id'];
            if ($groupId) {
                $groupUserId = $videoInfo['user_id'];
            }
        }

        if (!$videoUserId) {
            return $commentInfo;
        }

        $audioMessageId = get_param_int('audio_message_id');
        $imageUpload = get_param_int('image_upload');
        if ($videoId && ($msg != '' || $audioMessageId || $imageUpload)) {
            $send = get_param('send', time());
            $date = date('Y-m-d H:i:s');
            $parentId = get_param_int('reply_id');

            include_once($path . '_include/current/vids/tools.php');

            CStatsTools::count('videos_comments');

            /* Fix old template */
            $isAddWall = false;
            if ($videoInfo['active'] == 1) {
                $isAddWall = true;
            }
            /* Fix old template */
            $info = CVidsTools::insertCommentByVideoId($videoId, $msg, $date, $isAddWall, $videoUserId, true, $groupId, $groupUserId);
            $cid = 0;
            if (is_array($info)) {
                $cid = $info['cid'];
                $msg = $info['text'];
            }

            if ($videoUserId != $guid) {
                User::updatePopularity($videoUserId);
            }

            $commentInfo['count_comments'] = 0;
            $commentInfo['count_comments_replies'] = 0;
            if ($parentId) {
                $sql = 'SELECT `replies` FROM `' . self::$tableVideoComments . '` WHERE `id` = ' . to_sql($parentId);
                $commentInfo['count_comments_replies'] = DB::result($sql);
            } else {
                $dataInfo = DB::one(self::$tableVideo, '`id` = ' . to_sql($videoId));

                if ($dataInfo) {
                    $commentInfo['count_comments'] = $dataInfo['count_comments'];
                }
            }

            $commentInfo['id'] = $cid;
            $commentInfo['parent_id'] = $parentId;
            $commentInfo['comment'] = $msg;
            $commentInfo['date'] = $date;
            $commentInfo['user_id'] = $guid;
            $commentInfo['display_profile'] = User::displayProfile();
            $commentInfo['send'] = $send;

            $user = User::getInfoBasic($guid, false, 2);
            $commentInfo['user_name'] = $user['name'];
            $commentInfo['user_photo'] = User::getPhotoDefault($guid, 'r', false, $user['gender']);
            $commentInfo['user_photo_id'] = User::getPhotoDefault($guid, 'r', true);
            $commentInfo['audio_message_id'] = $audioMessageId;
            $commentInfo['video_id'] = $videoId;
        }

        return $commentInfo;
    }
    /* Comments */


    /* Updater */

    static function itemAddToArray($item, &$array, $prf = '')
    {
        $item = intval($item);
        if ($item > 0) {
            $array[] = $item . $prf;
        }
    }

    static function getCommentParentId($commentsReplies, $rcid)
    {
        foreach ($commentsReplies as $key => $value) {
            if (isset($value[$rcid])) {
                return $key;
                break;
            }
        }
        return 0;
    }

    static function getSqlComments($id, $parentId = 0)
    {
        $sql = 'SELECT `id`
                  FROM `' . self::$tableVideoComments . '`
                 WHERE `video_id` = ' . to_sql($id, 'Number') . '
                   AND `parent_id` = ' . to_sql($parentId);
        return $sql;
    }

    static function update(&$html)
    {
        $guid = guid();

        $liveId = get_param_int('live_id');
        $liveIdEnd = get_param_int('live_id_end');

        $liveData = self::getInfoLive($liveId);
        if (!$liveData) {//Removed by admin report
            $html->parse('ls_delete_admin', false);
            return;
        }

        $videoId = $liveData['video_id'];
        if (!$videoId) {
            return;
        }

        $videoInfo = DB::one(self::$tableVideo, '`id` = ' . to_sql($videoId));
        if (!$videoInfo) {
            return;
        }

        $liveInfo = jsonDecodeParamArray('live_info');
        if (!$liveInfo || !Common::isValidArray($liveInfo)) {
            return;
        }

        if (!$liveIdEnd) {
            self::parseListViewers($html, $liveId);
        }

        /* Update like live */
        if ($videoInfo['last_action_like'] != $liveInfo['like']) {
            $html->setvar('ls_like_id', $liveId);
            $html->setvar('ls_like_last_action_like', $videoInfo['last_action_like']);

            if ($videoInfo['like'] > 0) {
                self::parseLikes($html, $videoId, $videoInfo['like']);
            } else {
                $html->setvar('ls_like_user_list', '');
                $html->parse('ls_like');
            }
        }
        /* Update like live */

        /* Update info live */
        self::setInfoLive($html, $videoInfo, $liveId);
        $html->parse('ls_update_info_script', false);
        /* Update info live */

        /* Update like from comment */
        if ($videoInfo['last_action_comment_like'] != $liveInfo['actionLikeComment']) {
            $tableSql = 'vids_comment';
            $tableLikesSql = 'vids_comments_likes';
            $where = "video_id = " . to_sql($videoId);

            $sql = "SELECT `id`, `parent_id`, `likes` FROM `" . $tableSql . "`
                     WHERE " . $where .
                     " AND `last_action_like` > STR_TO_DATE(" . to_sql($liveInfo['actionLikeComment']) . ", '%Y-%m-%d %H:%i:%s')" .
                     " AND `last_action_like` <= STR_TO_DATE(" . to_sql($videoInfo['last_action_comment_like']) . ", '%Y-%m-%d %H:%i:%s')";
            $commentsLikes = DB::rows($sql);
            $commentLikeUpdatedResponse = array();
            if ($commentsLikes) {
                foreach ($commentsLikes as $key => $data) {
                    $sql = "SELECT `id` FROM {$tableLikesSql} WHERE `user_id` = " . to_sql($guid) . " AND `cid` = "  . to_sql($data['id']);
                    $commentsLikes[$key]['my_like'] = DB::result($sql, 0, DB_MAX_INDEX);
                    $commentsLikes[$key]['likes_users'] = User::getTitleLikeUsersComment($data['id'], $data['likes'], 'vids');
                }
                $commentLikeUpdatedResponse = array('actionLikeComment' => $videoInfo['last_action_comment_like'],
                                                    'items' => $commentsLikes);
            }

            $html->setvar('ls_update_like_comments_ls_id', $liveId);
            $html->setvar('ls_update_like_comments', json_encode($commentLikeUpdatedResponse));
            $html->parse('ls_update_like_comments');
        }
        /* Update like from comment */

        /* Update comments */
        $commentsAll = jsonDecodeParamArray('comments_all');
        $comments = jsonDecodeParamArray('comments');
        $commentsReplies = jsonDecodeParamArray('comments_replies');
        $commentsExistsVideo = array();
        //var_dump_pre($videoInfo['last_action_comment']);
        //var_dump_pre($liveInfo['comment']);

        $isCommentsUpdate = false;
        if ($videoInfo['last_action_comment'] != $liveInfo['comment']) {
            $isCommentsUpdate = true;

            $html->setvar('ls_id', $liveId);
            $html->setvar('ls_last_action_comment', $videoInfo['last_action_comment']);
            $html->setvar('ls_comments_count', $videoInfo['count_comments']);


            // Check comments in this item that exists on site
            $commentsOfItem = isset($commentsAll[$liveId]) ? $commentsAll[$liveId] : false;
            //var_dump_pre($commentsOfItem, true);

            if ($commentsOfItem) {
                $commentsPreparedVideo = array();
                foreach ($commentsOfItem as $commentOfItem => $valueOfItem) {
                    self::itemAddToArray($valueOfItem, $commentsPreparedVideo);
                }
                //var_dump_pre($commentsPreparedVideo, true);

                if ($commentsPreparedVideo) {
                    $commentsPreparedSql = implode(',', $commentsPreparedVideo);

                    $sql = "SELECT * FROM `" . self::$tableVideoComments . "`
                             WHERE id IN (" . $commentsPreparedSql . ')';
                    DB::query($sql);

                    $commentsExists = array();
                    $blockCommentsRepliesCount = 'ls_item_comments_replies_count';
                    while ($row = DB::fetch_row()) {
                        $commentsExists[] = $row['id'];
                        if ($row['parent_id']) {
                            continue;
                        }

                        $commentsExistsVideo[] = $row['id'];

                        /* Count replies comments */
                        $html->setvar("{$blockCommentsRepliesCount}_cid", $row['id']);
                        $html->setvar("{$blockCommentsRepliesCount}_count", $row['replies']);
                        $html->parse($blockCommentsRepliesCount, true);
                        /* Count replies comments */
                    }
                    $commentsNotExists = array_diff($commentsPreparedVideo, $commentsExists);
                    //var_dump_pre($commentsExists);
                    //var_dump_pre($commentsNotExists);

                    if ($commentsNotExists) {
                        foreach ($commentsNotExists as $k => $commentNotExists) {
                            $cid = self::getCommentParentId($commentsReplies, $commentNotExists);
                            if ($cid) {
                                if (array_search($cid, $commentsNotExists) !== false) {
                                    unset($commentsNotExists[$k]);
                                }
                            }
                        }

                        //print_r_pre($commentsNotExists);
                        //exit();

                        foreach ($commentsNotExists as $commentNotExists) {
                            $cid = $commentNotExists;
                            $rcid = 0;
                            if (!isset($commentsReplies[$commentNotExists])) {
                                $rcid = $cid;
                                $cid = self::getCommentParentId($commentsReplies, $cid);
                                unset($commentsReplies[$cid][$rcid]);
                            }
                            //echo $cid . '/' . $rcid;
                            $html->setvar('ls_id', $liveId);
                            $html->setvar('ls_item_comment_id_delete', $cid);
                            $html->setvar('ls_item_comment_reply_id_delete', $rcid);
                            $html->parse('ls_item_comment_delete_script');

                            unset($commentsAll[$liveId][$commentNotExists]);
                            unset($comments[$liveId][$commentNotExists]);
                        }
                        $html->parse('ls_item_comments_delete_script');
                    }
                }
            }
        }


        $itemsCommentFirstId = jsonDecodeParamArray('comments_first');

        //print_r_pre($commentsReplyLastId);
        //print_r_pre($commentsExistsPhoto);

        $limit = CProfilePhoto::getNumberShowComments(false, 'live');
        $limitReplies = CProfilePhoto::getNumberShowComments(true, 'live');

        $lastId = 0;
        if (isset($itemsCommentFirstId[$liveId])) {
            $lastId = $itemsCommentFirstId[$liveId];
        }

        $parseComments = self::parseComments($html, $videoId, '', 0, $lastId, 'id ASC');

        /* If you delete a comment, you need to add up to the limit */
        $countExistsComments = 0;
        if (isset($comments[$liveId])) {
            $countExistsComments = count($comments[$liveId]);
        }

        $countExistsComments += count($parseComments);
        //var_dump_pre($parseComments);
        //var_dump_pre($countExistsComments);

        if (!isset($comments[$liveId]) || $countExistsComments < $limit) {
            self::$commentCustomClass = 'comment_attach';

            $sql = self::getSqlComments($videoId, 0);
            $sql .= ' ORDER BY `id` DESC LIMIT ' . $limit;
            DB::query($sql, 8);
            if (DB::num_rows(8)) {
                while ($commentAttach = DB::fetch_row(8)) {
                    if (!isset($comments[$liveId][$commentAttach['id']]) && !isset($parseComments[$commentAttach['id']])) {
                        self::parseComments($html, $videoId, '', $commentAttach['id']);
                        //break;
                    }
                }
            }
            self::$commentCustomClass = '';
        }
        /* If you delete a comment, you need to add up to the limit */

        /* Update replies comments */
        if ($isCommentsUpdate && $commentsExistsVideo) {
            $commentsReplyLastId = jsonDecodeParamArray('comments_reply_last');

            foreach ($commentsExistsVideo as $k => $comId) {
                $lastId = 0;
                if (isset($commentsReplyLastId[$comId])) {
                    $lastId = $commentsReplyLastId[$comId];
                }
                $paramLastId = get_param('last_id');
                $_POST['last_id'] = $lastId;

                self::$commentCustomClass = 'comment_attach_reply';
                Wall::$commentReplyCustomClass = 'comment_attach_reply_one';
                Wall::$commentsReplyParse = array();

                //parseComments(&$html, $id, $limit = false, $comId, $lastId)
                self::parseComments($html, $videoId, '', $comId, $lastId);
                //parseComments(&$html, $id, $index = 2, $start = 0, $limit = 3, $cid = 0, $where = '', $row = false)
                //Wall::parseComments($html, $itemExists, 1, 0, 0, $comId);

                Wall::$commentReplyCustomClass = '';
                self::$commentCustomClass = '';

                $_POST['last_id'] = $paramLastId;

                $countExistsComments = 0;
                if (isset($commentsReplies[$comId])) {
                    $countExistsComments = count($commentsReplies[$comId]);
                }

                $countExistsComments += count(Wall::$commentsReplyParse);

                //var_dump_pre($countExistsComments);
                if (!isset($commentsReplies[$comId]) || $countExistsComments < $limitReplies) {
                    Wall::$commentCustomClass = 'comment_attach_reply_add';
                    Wall::$commentReplyCustomClass = 'comment_attach_reply_one_add';

                    self::parseComments($html, $videoId, '', $comId);

                    Wall::$commentReplyCustomClass = '';
                    Wall::$commentCustomClass = '';
                }
            }
        }

        /* Update replies comments */
        /* Update comments */

        //var_dump_pre($liveInfo, true);
    }

    /* List */
    static public function getTypeOrderList($notRandom = false, $lang = false)
    {
        global $p;

        if ($lang !== false) {
            $pLast = $p;
            $p = 'live_list.php';
        }
        $list = array(
            'order_new'              => l('order_new', $lang),
            'order_most_commented'   => l('order_most_commented', $lang),
            'order_most_viewed'      => l('order_most_viewed', $lang),
            'order_random'           => l('order_random', $lang)
        );
        if ($lang !== false) {
            $p = $pLast;
        }
        if ($notRandom) {
            unset($list['order_random']);
        }
        return $list;
    }

    static function getOrderList($typeOrder = '')
    {
        $orderBy = 'date_start DESC, id DESC';
        if ($typeOrder == 'order_most_commented') {
            $orderBy = 'count_comments DESC, id DESC';
        } elseif ($typeOrder == 'order_most_viewed') {
            $orderBy = 'count_viewers DESC, id DESC';
        } elseif ($typeOrder == 'order_random') {
            $orderBy = 'RAND()';
        }

        if (IS_DEMO) {
            $orderBy = 'id DESC';
        }

        return $orderBy;
    }

    static public function getWhereTags($table = '', $tags = null) {
        if ($tags === null) {
            $tags = trim(get_param('tags'));
        }

        if (!$tags) {
            return '';
        }

        $tags =  explode(',', trim($tags));
        if (!is_array($tags)) {
            return '';
        }

        $whereSql = 'no_tags';
        $where = '';
        $i = 0;
        foreach ($tags as $k => $tag) {
            $tag = trim($tag);
            if ($tag) {
                if ($i) {
                   $where .= ' OR ';
                }
                $where .= '`tag` LIKE "%' . DB::esc_like($tag) . '%"';
            }
            $i++;
        }
        if ($where) {
            $sql = "SELECT `id` FROM `" . self::$tableVideoTags . "` WHERE ({$where})";
            $tagsId = DB::rows($sql);
            $tags = array();
            if ($tagsId) {
                foreach ($tagsId as $k => $tag) {
                    $tags[] = $tag['id'];
                }
                $whereSql = implode(',', $tags);
                $whereSql = " AND {$table}tag_id IN({$whereSql}) AND {$table}live_id != 0";
            }
        }

        return $whereSql;
    }

    static function getWhereList($table = '', $online = true, $uid = 0) {
        $guid = guid();

        $time = time() - 60;//Fix delete hanging live
        $where = "{$table}date_start != '0000-00-00 00:00:00'"
          . " AND {$table}date_stop = '0000-00-00 00:00:00'"
          . " AND ({$table}status != 0)"
          . " AND {$table}is_upload_photo = 1"
          . " AND {$table}status > " . to_sql($time);

        if (!$online) {
            $where_1 = "{$table}date_start != '0000-00-00 00:00:00'"
                . " AND {$table}date_stop != '0000-00-00 00:00:00'"
                . " AND {$table}status = 0";
            $where = "(({$where}) OR ({$where_1}))";
        }

        if (!$uid) {
            $where .= " AND {$table}user_id != " . to_sql($guid);
        }
        if ($uid) {
            $where .= " AND {$table}user_id = " . to_sql($uid);
        } elseif ($guid) {
            $isShowMyLive = Common::isOptionActive('show_your_live_browse_lives', 'edge_member_settings');
            $onlyFriends = false;
            if (self::$isGetDataWithFilter) {
                $onlyFriends = get_param_int('only_friends', false);
                if ($onlyFriends) {
                    $friends = User::friendsList($guid, $isShowMyLive);
                    if ($friends) {
                        $where .= " AND {$table}user_id IN ({$friends})";
                    }
                }
            }
            if (!$onlyFriends && !$isShowMyLive) {
                $where .= " AND {$table}user_id != " . to_sql($guid);
            }
        }

        /*if (!$uid) {
            $searchQuery = trim(get_param('search_query'));
            if ($searchQuery) {
                $searchQuery = urldecode($searchQuery);
                $where .= " AND {$table}subject  LIKE '%" . to_sql($searchQuery, 'Plain') . "%'";
            }
        }*/

        return $where;
    }

    static public function getTotalLiveNow() {
        self::$isGetDataWithFilter = true;
        $itemsTotal = self::getTotalLive(true, 0);
        self::$isGetDataWithFilter = false;

        return $itemsTotal;
    }

    static public function getTotalLiveFinished() {
        $defaultOnlyLive = get_param_int('only_live');

        $_GET['only_live'] = 1;
        CProfileVideo::$isGetDataWithFilter = true;
        $itemsTotal = CProfileVideo::getTotalVideos(0, 0);
        CProfileVideo::$isGetDataWithFilter = false;
        $_GET['only_live'] = $defaultOnlyLive;

        return $itemsTotal;
    }

    static public function getTotalLive($online = true, $uid = null) {
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
        }

        if ($whereTags == 'no_tags') {
            return 0;
        }
        if ($whereTags) {
            $where = self::getWhereList('LR.', $online, $uid);
            $sql = 'SELECT COUNT(*) FROM (
                        SELECT COUNT(*)
                          FROM `' . self::$tableVideoTagsRel . '` AS TR
                          JOIN `' . self::$table . '` AS LR ON LR.video_id = TR.video_id
                         WHERE ' . $where
                                 . $whereTags
                     . ' GROUP BY LR.video_id) AS LT';

            return DB::result($sql);
        } else {
            $where = self::getWhereList('', $online, $uid);
            return DB::count(self::$table, $where);
        }
    }

    static public function getLists($limit = '', $typeOrder = '', $online = true, $uid = 0, $whereSql = '', $order = '', $noData = false) {

        global $p;

        $guid = guid();

        $result = array();



        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
            if ($whereTags == 'no_tags') {
                return $result;
            }
        }

        if ($order === '') {
            if ($typeOrder == '') {
                $typeOrder = Common::getOption('list_live_type_order', 'edge_general_settings');
            }
            $order = self::getOrderList($typeOrder);
        }

        if ($order) {
            $order = ' ORDER BY ' . $order;
        }

        $groupBy = '';
        $isShowNotEnded = false;
        if (!$guid && !Common::isOptionActive('list_live_show_not_ended', 'edge_main_page_settings')) {
            $isShowNotEnded = true;
        }

        $fromAdd = '';
        $whereBlocked = '';
        if (Common::isOptionActive('contact_blocking')){
            $guidSql = to_sql($guid);
            $fromAdd = " LEFT JOIN user_block_list AS ubl1 ON (ubl1.user_to = U.user_id AND ubl1.user_from = " . $guidSql . ")
                         LEFT JOIN user_block_list AS ubl2 ON (ubl2.user_from = U.user_id AND ubl2.user_to = " . $guidSql . ")";
            $whereBlocked =' AND ubl1.id IS NULL AND ubl2.id IS NULL';
        }

        if ($whereTags) {
            $where = self::getWhereList('LR.', $online, $uid);
            $groupBy = ' GROUP BY LR.video_id ';
            if ($isShowNotEnded) {
                $groupBy = ' GROUP BY LR.user_id ';
            }

            $sql = 'SELECT LR.*, U.name, U.name_seo, U.country, U.city, U.gender
                      FROM `' . self::$tableVideoTagsRel . '` AS TR
                      JOIN `' . self::$table . '` AS LR ON LR.video_id = TR.video_id
                      JOIN `user` AS U ON U.user_id = LR.user_id
                      ' . $fromAdd . '
                     WHERE ' . $where
                             . $whereTags
                             . $whereBlocked
                             . $groupBy
                             . $order
                             . $limit;
        } else {
            $where = self::getWhereList('L.', $online, $uid);
            if ($isShowNotEnded) {
                $groupBy = ' GROUP BY L.user_id ';
            }
            $sql = 'SELECT L.*, U.name, U.name_seo, U.country, U.city, U.gender
                      FROM `' . self::$table . '` AS L
                      JOIN `user` AS U ON U.user_id = L.user_id
                      ' . $fromAdd . '
                     WHERE ' . $where
                             . $whereBlocked
                             . $whereSql
                             . $groupBy
                             . $order
                             . $limit;
        }

        $lives = DB::rows($sql);
        if ($noData) {
            return $lives;
        }

        foreach ($lives as $item) {
            $liveId = $item['id'];
            $result[$liveId] = $item;

            if ($isShowNotEnded && $item['date_start'] != '0000-00-00 00:00:00') {
                $timeAgo = timeAgo(date('Y-m-d H:i:s'), 'now', 'string', 60, 'second', true);
            } else {
                $timeAgo = timeAgo($item['date_start'], 'now', 'string', 60, 'second', true);
            }

            $result[$liveId]['time_ago'] = $timeAgo;

            $videoInfo = DB::one(self::$tableVideo, '`id` = ' . to_sql($item['video_id']));

            $result[$liveId]['subject'] = $videoInfo['subject'];
            $result[$liveId]['src_bm'] = User::getVideoFile($videoInfo, 'bm', '');

            if (Common::isMobile()) {
                $result[$liveId]['user_url'] = 'profile_view.php?user_id=' . $item['user_id'];
            } else {
                $userInfo = array('name' => $item['name'], 'name_seo' => $item['name_seo']);
                $result[$liveId]['user_url'] = User::url($item['user_id'], $userInfo);
            }

            if (true) {
            //if ($item['demo']) {
                $result[$liveId]['url'] = Common::pageUrl('live_id', $item['user_id'], $item['id']);
            } else {
                $result[$liveId]['url'] = Common::pageUrl('live', $item['user_id']);
            }


            if (Common::isOptionActiveTemplate('gallery_tags') || Common::isOptionActiveTemplate('gallery_tags_template')) {
                $tags = CProfileVideo::getTags($item['video_id']);
                $result[$liveId]['tags'] = $tags;
                $tagsData = CProfilePhoto::getTagsMedia($tags, 0, 0, 'vids');

                $result[$liveId]['tags_title'] = $tagsData['title'];
                $result[$liveId]['tags_html'] = $tagsData['html'];
            }
        }

        return $result;
    }

    static public function searchLive($curLiveId = null, $prev = null) {
        $result = '';

        if ($curLiveId === null) {
            $curLiveId = get_param_int('cur_live_id');
        }

        if ($prev === null) {
            $prev = get_param_int('prev');
        }

        $where = ' AND L.id ' . ($prev ? '<' : '>') . to_sql($curLiveId);
        $order = $prev ? 'id DESC' : 'id ASC';
        $liveInfo = self::getLists(1, '', true, 0, $where, $order, true);
        if (!$liveInfo) {
            $liveInfo = self::getLists(1, '', true, 0, ' AND L.id != ' . to_sql($curLiveId), $order, true);
        }
        if ($liveInfo && isset($liveInfo[0])) {
            $liveInfo = $liveInfo[0];
            $result = Common::pageUrl('live_id', $liveInfo['user_id'], $liveInfo['id']);
        }

        return $result;
    }
    /* List */

    /* List viewers */
    static function updateMyViewer()
    {
        $liveId = get_param_int('live_id');
        $liveIdEnd = get_param_int('live_id_end');
        if ($liveIdEnd) {
            return;
        }

        $sql = "INSERT INTO " . self::$tableViewers . " (`live_id`, `user_id`)
                     VALUES (" . to_sql($liveId, 'Number') . ", "
                               . to_sql(guid(), 'Number') . ")
                         ON DUPLICATE KEY UPDATE `live_id` = VALUES(live_id)";
        DB::execute($sql);
    }

    static function updateViewers($users = null)
    {
        $liveId = get_param_int('live_id');
        $liveIdEnd = get_param_int('live_id_end');
        if (!$liveId || $liveIdEnd) {
            return false;
        }

        if ($users === null) {
            $users = get_param('live_list_viewers');
        }
        if ($users && is_string($users)) {
            $users = json_decode($users, true);
            if (!is_array($users)) {
                $users = array();
            }
        } else {
            $users = array();
        }

        if (!$users) {
            DB::delete(self::$tableViewers, '`live_id` = ' . to_sql($liveId));
            return true;
        }

        $userExistsSql = implode(',', array_flip($users));
        if ($userExistsSql) {
            $sql = 'DELETE FROM ' . self::$tableViewers .
                        ' WHERE `live_id` = ' . to_sql($liveId) .
                          ' AND `user_id` NOT IN (' . $userExistsSql . ')';
            DB::execute($sql);
        }

        foreach ($users as $uid => $sess) {
            $sql = "INSERT INTO " . self::$tableViewers . " (`live_id`, `user_id`)
                     VALUES (" . to_sql($liveId, 'Number') . ", "
                               . to_sql($uid, 'Number') . ")
                         ON DUPLICATE KEY UPDATE `live_id` = VALUES(live_id)";
            DB::execute($sql);
        }

        return true;
    }
    /* List viewers */

    static function parseJs(&$html, $js)
    {
        $html->setvar('script_js', $js);
    }

    /* Demo */
    static function updateDemoData()
    {
        if (!IS_DEMO) {
            return;
        }

        $time = time() + 3600;
        $sql = "UPDATE `live_streaming`
               SET `status` = " . $time . "
             WHERE `demo` = 1";
        DB::execute($sql);

        $sql = "UPDATE `live_streaming`
               SET `date_start` = ADDDATE(NOW(), INTERVAL - FLOOR(10*RAND()) MINUTE)
             WHERE `demo` = 1 AND ADDDATE(`date_start`, INTERVAL 15 MINUTE) < NOW()";
        DB::execute($sql);
    }
    /* Demo */

    static function checkAviableLiveStreaming()
    {
        if (!self::isAviableLiveStreaming()) {
            redirect(Common::getHomePage());
        }
    }

    static function isAviableLiveStreaming()
    {
        $isAvailable = true;

        // ios app only special version because need camera/audio permissions
        if(!Common::isOptionActive('live_streaming') || (Common::isAppIos() && !Common::isAppIosWebrtcAvailable())) {
            $isAvailable = false;
        }

        return $isAvailable;
    }

    static function notAviableLiveStreamingAdmin()
    {
        global $sitePart;

        return $sitePart != 'administration' && !LiveStreaming::isAviableLiveStreaming();
    }

    static function paid()
    {
        global $g_user;

        $price = Pay::getServicePrice('live_stream', 'credits');
        if($price > 0 && Common::isCreditsEnabled()){
            if($g_user['credits'] >= $price){
                $credit = $g_user['credits'] - $price;
                $responseData = $credit;
                User::update(array('credits' => $credit));
            } else {
                $responseData = $g_user['credits'];
            }
        }else{
            $responseData = $g_user['credits'];
        }
        return $responseData;
    }

    static function deletePastLive()
    {
        $vid = get_param_int('video_id');
        if (!$vid) {
            return false;
        }

        $videoInfo = DB::row('SELECT * FROM vids_video WHERE `id` =' . to_sql($vid));
        if (!$videoInfo || $videoInfo['user_id'] != guid()) {
            return false;
        }
        $liveId = $videoInfo['live_id'];

        CVidsTools::delVideoById($vid, true);
        self::deleteLive($liveId);

        return true;
    }

    function parseBlock(&$html) {

        global $g_user;

        $cmd = get_param('cmd');
        $id = get_param_int('id');
        $liveId = get_param_int('live_id');
        if ($liveId) {
            self::$liveId = $liveId;
        }

        if ($cmd == 'live_stream_like') {
            $like = get_param_int('like');
            $likesInfo = self::addLikeMediaContent($id, $like, !$like, 'vids');
            if ($likesInfo !== false) {
                $likes = $likesInfo['likes'];

                self::parseLikes($html, $id, $likes);
            }
        } elseif ($cmd == 'get_live_stream_comment') {
            if ($id) {
                self::parseComments($html, $id);
            }
        } elseif ($cmd == 'get_live_stream_comment_replies') {
            self::parseCommentsReplies($html);
        } elseif ($cmd == 'live_stream_start' || $cmd == 'live_stream_start_viewer') {
            if ($cmd == 'live_stream_start_viewer') {
                self::updateMyViewer();
            }
            self::parseBlockComments($html, $id);
        } elseif ($cmd == 'live_stream_comment_add') {
            $comment = self::addComment();
            if (!empty($comment)) {
                $block = 'comment';
                $isReply = get_param_int('reply_id');
                if ($isReply) {
                    $block = 'comments_reply_item';
                }

                $typeGallery = Common::getOptionTemplate('gallery_type');
                if ($typeGallery == 'edge'){
                    $groupId = 0;
                    $comment['item_group_id'] = 0;
                    $comment = CProfilePhoto::prepareDataComment($comment, 'live');
                }

                $html->setvar('comment_ls_id', $liveId);
                if (!$isReply) {
                    $dataComment = DB::one(self::$tableVideo, '`id` = ' . to_sql($id));
                    $vars = array(
                        'last_action_like' => $dataComment['last_action_like'],
                        'last_action_comment' => $dataComment['last_action_comment'],
                        'comments_count' => $dataComment['count_comments'],
                    );
                    $html->assign('comment_ls', $vars);
                }

                if(!isset($comment['content_item_id']) && isset($comment['video_id'])) {
                    $comment['content_item_id'] = $comment['video_id'];
                }

                Wall::$isParseCommentsBlog = true;
                CProfilePhoto::parseComment($html, $comment, $block, 'live');
                Wall::$isParseCommentsBlog = false;

                if ($isReply) {
                    $html->parse('comments_reply_list');
                }
            }
        } elseif ($cmd == 'live_stream_comment_delete') {
            $videoId = get_param_int('video_id');
            $cid = get_param_int('cid');
            $lastId = get_param_int('last_id');
            $cidRarent = get_param_int('cid_parent');

            $listComments = get_param('list_comments');
            if ($listComments && is_string($listComments)) {
                $listComments = json_decode($listComments, true);
                if (!is_array($listComments)) {
                    $listComments = array();
                }
            } else {
                $listComments = array();
            }
            $countListComment = count($listComments);

            include_once('./_include/current/vids/tools.php');
            CVidsTools::deleteCommentVideoByAjax($cid);

            $rcid = 0;
            if ($cidRarent) {
                $rcid = $cid;
                $cid = $cidRarent;
                $limit = CProfilePhoto::getNumberShowComments(true, 'live');
                $count = $countListComment ? $countListComment-- : 0;
                if ($limit > $count) {
                    self::parseComments($html, $videoId, 1, $cid, $lastId);
                }
            } else {
                $limit = CProfilePhoto::getNumberShowComments(false, 'live');
                $count = $countListComment ? $countListComment-- : 0;
                if ($limit > $count) {
                    self::parseComments($html, $videoId, 1, 0, $lastId);
                }
            }
            $js = "<script>clStream.commentDeleteFromPage('{$id}','" . $cid . "','" . $rcid . "')</script>";
            self::parseJs($html, $js);
        } elseif ($cmd == 'update_im') {
            self::update($html);
        }

        parent::parseBlock($html);
    }
}