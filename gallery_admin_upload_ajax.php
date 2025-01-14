<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include('./_include/core/main_start.php');
include('./_include/current/fileapi.class.php');

$action = get_param('action');
if ($action == 'success_upload') {
    delses('time_upload');
    delses('folder_upload');
}elseif ($action == 'creat_folder') {
    $albumSelect = intval(get_param('albumselect'));
        if ($albumSelect != 0 && is_dir($g['path']['dir_files'] . 'gallery/images/' . $g_user['user_id'] . '/' . $albumSelect)) {
            $folder = $albumSelect;
        } else {
            $folder = 1;
            while (custom_file_exists($g['path']['dir_files'] . 'gallery/images/' . $g_user['user_id'] . '/' . $folder)) {
                $folder++;
            }
    }
    $response['folder'] = $folder;
    $response['time'] = DB::result('SELECT NOW()');
    //set_session('time_upload', DB::result('SELECT NOW()'));
    //set_session('folder_upload', $folder);
    echo json_encode($response);
}elseif ($action == 'upload') {
    if( !empty($_SERVER['HTTP_ORIGIN']) ){
	// Enable CORS
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type');
    }

    if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ){
	exit;
    }

    if( strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ){
    $files	= FileAPI::getFiles(); // Retrieve File List
    $images	= array();

    $error = false;
    if (isset($files['filedata']['tmp_name'])&& Image::isValid($files['filedata']['tmp_name'])){
            //$folder = get_session('folder_upload');
            //$time = get_session('time_upload');
            $folder = get_param('folder');
            $time = get_param('time');

            $imgSize = Gallery::uploadImage($files['filedata']['tmp_name'], $folder);
            if (!$imgSize) {
                return false;
            }
            $name = Gallery::getFileName();
            $imWidth = $imgSize['w'];
            $imHeight = $imgSize['h'];

            /*$id = DB::result('SELECT `id`
                                FROM `gallery_albums`
                               WHERE `folder` = ' . to_sql($folder) . ' AND `user_id` = ' . to_sql($g_user['user_id'], 'Number'));*/
            $albumInfo = DB::row('SELECT `id`, `access`, `title`
                                    FROM `gallery_albums`
                                   WHERE `folder` = '.to_sql($folder)
                                 . ' AND `user_id` = ' . $g_user['user_id']);
            $newAlbum = false;
            if(!$albumInfo) {
                //New album required
                // Creating
                $album_title = get_param('albumtitle', '');
                $date = date('Y-m-d H:i:s');
                $sql = "INSERT INTO `gallery_albums` ( `user_id` , `parentid` , `folder` , `title` , `desc` , `date` , `place` , `show` , `thumb` , `sort_type` , `sort_order` , `views` )
                             VALUES ('" . $g_user['user_id'] . "', NULL, " . to_sql($folder) . ', ' . to_sql(he($album_title)) . ", '', '" . $date . "', '' , '1', '" . $name . "', NULL , NULL , '0')";
                DB::execute($sql);
                $id = DB::insert_id();
                $access = 'public';
                $newAlbum = true;
            } else {
                $id = $albumInfo['id'];
                $access = $albumInfo['access'];
                $album_title = $albumInfo['title'];
                /*$access = DB::result('SELECT `access`
                                        FROM `gallery_albums`
                                       WHERE `folder` = '.to_sql($folder)
                                     . ' AND `user_id` = ' . $g_user['user_id']);*/
            }
            $tableImages = 'gallery_images';
            $row = array('user_id' => guid(),
                         'albumid' => $id,
                         'filename' => $name,
                         'width' => $imWidth,
                         'height' => $imHeight,
                         'datetime' => $time,
                         'title' => '',
                         'desc' => '',
            );
            DB::insert($tableImages, $row);
            $imgId = DB::insert_id();

            // rename files to id

            $uploadDir = Gallery::getPath('images', $folder);
            $uploadDirThumb = Gallery::getPath('thumb', $folder);

            $renameFiles = array(
                Gallery::getUploadFile() => $uploadDir . '/' . $imgId . '.jpg',
                Gallery::getUploadFileSrc() => $uploadDir . '/' . $imgId . '_src.jpg',
                Gallery::getUploadFileThumb() => $uploadDirThumb . '/' . $imgId . '.jpg',
            );

            foreach($renameFiles as $oldFile => $newFile) {
                rename($oldFile, $newFile);
            }

            $imgFile = $imgId . '.jpg';
            DB::update($tableImages, array('filename' => $imgFile), 'id = ' . to_sql($imgId));
            if($newAlbum) {
                DB::update('gallery_albums', array('thumb' => $imgFile), 'id = ' . to_sql($id));
            }

            // popcorn modified s3 bucket gallery upload 2024-05-06
            $sizesBaseImage = array(
                'th'  => array('path' => $uploadDir . '/' . $imgId . '.jpg',
                               'w' => 100,
                               'h' => 100
                         ),
                'src' => array('path' => $uploadDir . '/' . $imgId . '_src.jpg',
                               'w' => 1920,
                               'h' => 1080
                         ),
                'b'   => array('path' => $uploadDirThumb . '/' . $imgId . '.jpg',
                               'w' => $g['image']['gallery_width'],
                               'h' => $g['image']['gallery_height']
                         )
            );

            if ($folder == 'chat_system') {//Upload image Im
                $sizesBaseImage['b']['w'] = $sizesBaseImage['src']['w'] = $im->getWidth();
                $sizesBaseImage['b']['h'] = $sizesBaseImage['src']['h'] = $im->getHeight();
            }

            foreach ($sizesBaseImage as $key => $params) {
                if(isS3SubDirectory($params['path'])) {
                    if($params['path']) {
                        $parts = explode("_files/temp/", $params['path']);
                        if (isset($parts[1]) && !empty($parts[1])) {
                            custom_file_upload($params['path'], $parts[1]);
                        }
                    }
                }
            }

            Wall::addItemForUser($imgId, 'pics', guid());
            $paramsSection = '';
            /*if (!$folder) {
                $paramsSection = 'timeline';
            }*/
            Wall::add('pics', $id, false, $time, true, 0, $access, true, $paramsSection);
        }

	// Fetch all image-info from files list


        //echo 'folder-'.$folder;//.'-'.$name.'-'.$nameSrc
        //var_dump($files);
        //fetchImages($files, $images);
	// JSONP callback name
	$jsonp	= isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : null;


	// JSON-data for server response
	$json	= array(
                  'id' => $id,
 		  //'images'	=> $images,
		  'data'	=> array('_REQUEST' => $_REQUEST, '_FILES' => $files)
	);
	// Server response: "HTTP/1.1 200 OK"
	FileAPI::makeResponse(array(
		  'status' => FileAPI::OK
		, 'statusText' => 'OK'
		, 'body' => $json
	), $jsonp);
	exit;
    }
}

function fetchImages($files, &$images, $name = 'file'){
	if( isset($files['tmp_name']) ){
		$filename = $files['tmp_name'];
		list($mime)	= explode(';', @mime_content_type($filename));

		if( strpos($mime, 'image') !== false ){
			$size = getimagesize($filename);
			$base64 = base64_encode(file_get_contents($filename));

			$images[$name] = array(
				  'width'	=> $size[0]
				, 'height'	=> $size[1]
				, 'mime'	=> $mime
				, 'size'	=> filesize($filename)
				, 'dataURL'	=> 'data:'. $mime .';base64,'. $base64
			);
		}
	} else {
		foreach( $files as $name => $file ){
			fetchImages($file, $images, $name);
		}
	}
}


include('./_include/core/main_close.php');