<?php

use FFMpeg\Filters\Audio\CustomFilter;

class Gallery
{
    static private $fileName;
    static private $uploadFile;
    static private $uploadFileThumb;
    static private $uploadFileSrc;
    static public $userToIm = 0;

    static function creatFolders($folder)
    {
        self::creatDir('images', null);
        self::creatDir('thumb', null);
        self::creatDir('images', $folder);
        self::creatDir('thumb', $folder);

        self::setMod('images', $folder);
        self::setMod('thumb', $folder);
    }

    static function creatDir($dir, $folder)
	{
        $path = self::getPath($dir, $folder);
        
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
    }

    static function getNewFolder($folder)
	{
        $path = self::getPath('images', $folder);
        $pathDir = self::getPath('images', null);
        if (empty($folder) || !is_dir($path)) {
            $folder = 1;
            while (custom_file_exists("{$pathDir}/{$folder}")) {
                $folder++;
            }
        }
        return $folder;
    }

    static function setMod($dir, $folder)
	{
        $path = self::getPath($dir, $folder);
        @chmod($path, 0777);
    }

    static function getPath($dir, $folder)
	{
        global $g, $g_user;

        if ($folder !== null) {
            $folder = '/' . $folder;
        }
        
        $path = "{$g['path']['dir_files']}gallery/{$dir}/{$g_user['user_id']}{$folder}";
        //popcorn modified s3 bucket gallery 2024-05-06
        if(isS3SubDirectory($path)) {
            $path = "{$g['path']['dir_files']}temp/gallery/{$dir}/{$g_user['user_id']}{$folder}";
        }
        $path = str_replace("\\", "/", $path);

        return $path;
    }

    static function setName($folder, $ext = 'jpg')
	{
        $name = 0;
        $path = self::getPath('images', $folder);
        $pathThumb = self::getPath('thumb', $folder);
        do {
            $name++;
        } while (custom_file_exists(str_replace( "temp/" , "",  "{$path}/{$name}.{$ext}")));

        self::$fileName = "{$name}.{$ext}";
        self::$uploadFile = "{$path}/{$name}.{$ext}";
        self::$uploadFileThumb = "{$pathThumb}/{$name}.{$ext}";
        self::$uploadFileSrc = "{$path}/{$name}_src.{$ext}";
    }

    static function getFileName()
	{
        return self::$fileName;
    }

    static function getUploadFile()
	{
        return self::$uploadFile;
    }

    static function getUploadFileSrc()
	{
        return self::$uploadFileSrc;
    }

    static function getUploadFileThumb()
	{
        return self::$uploadFileThumb;
    }

    static function getTitleAlbumWall()
	{
        return 'Timeline Photos';
    }

    static function uploadWall($descImage)
	{
        global $g, $g_user;

        $id = 0;
		$exts = array('jpg', 'gif');
		foreach ($exts as $ext) {
			$file = Wall::getTempFileUploadImage($ext);
			if (custom_file_exists($file)) {
				$id = self::upload($file, 0, 'Timeline Photos', $descImage);
				@unlink($file);
				$fileTempTh = Common::getOption('dir_files', 'path') . Wall::$tempNameFileBase . '_th.' . $ext;
				@unlink($fileTempTh);
				break;
			}
		}

        return $id;
    }

    static function uploadIm($descImage, $prf = 'tmp_chat')
	{
        global $g, $g_user;

		$id = 0;
		$exts = array('jpg', 'gif');
		foreach ($exts as $ext) {
			$file = self::getTempFileUploadImageIm($prf, $ext);
			if (file_exists($file)) {
				$id = self::upload($file, 'chat_system', 'Chat', $descImage, '', true);
				//@unlink($file);
				break;
			}
		}

        return $id;
    }

    static function getTempFileUploadImageIm($prf = 'tmp_chat', $ext = 'jpg')
    {
        $ind = get_param_int('ind');
        if ($ind) {
            $ind = '_' . $ind;
        } else {
            $ind = '';
        }
        return Common::getOption('url_files', 'path') . 'temp/' . $prf . '_' . guid() . $ind . '.' . $ext;
    }

	static function getExtUploadFile($file)
	{
		$ext = 'jpg';
		/*$imgInfo = @getimagesize($file);
		if (is_array($imgInfo) && $imgInfo[2] == 1) {//Gif
			$ext = 'gif';
		}*/
		if(Image::isAnimatedGif($file)){
			$ext = 'gif';
		}
		return $ext;
	}


    static function uploadImage($file, $folder)
	{
        global $g;

        $im = new Image();
        if (!$im->loadImage($file)) {
            return false;
        }

        self::creatFolders($folder);

		$ext = 'jpg';
		if ($folder == 0 || $folder == 'chat_system') {//Gif - Wall - Timeline Photos
			$ext = self::getExtUploadFile($file);
		}
        self::setName($folder, $ext);

        $sizesBaseImage = array(
            'th'  => array('path' => self::$uploadFileThumb,
                           'w' => 100,
                           'h' => 100
                     ),
            'src' => array('path' => self::$uploadFileSrc,
                           'w' => 1920,
                           'h' => 1080
                     ),
            'b'   => array('path' => self::$uploadFile,
                           'w' => $g['image']['gallery_width'],
                           'h' => $g['image']['gallery_height']
                     )
        );

        if ($folder == 'chat_system') {//Upload image Im
            $sizesBaseImage['b']['w'] = $sizesBaseImage['src']['w'] = $im->getWidth();
            $sizesBaseImage['b']['h'] = $sizesBaseImage['src']['h'] = $im->getHeight();
        }

        $result = false;
		if ($ext == 'gif') {
			foreach ($sizesBaseImage as $key => $params) {
				if ($key == 'th') {
					$im->resizeCropped($params['w'], $params['h']);
					$saveImage = $im->saveImage($params['path'], $g['image']['quality'], 'gif');
				} else {
					if(copy($file, $params['path'])){
						@chmod($params['path'], 0777);
						$saveImage = true;
					}
				}
				if ($saveImage) {
					if ($key == 'b') {
						$imgInfo =@custom_getimagesize($file);
						if ($imgInfo) {
							$result = array('w' => $imgInfo[0], 'h' => $imgInfo[1]);
						}
					} elseif ($key == 'th') {
						CStatsTools::count('pics_uploaded');
					}
					Common::saveFileSize($params['path']);
				} else {
					break;
				}
			}
			return $result;
		}

		$watermarkParams = CProfilePhoto::watermarkParams();
        foreach ($sizesBaseImage as $key => $params) {
            $im->prepareImageResource($file);
            if ($key == 'th') {
                $im->resizeCropped($params['w'], $params['h']);
            } else {
                $im->resizeWH($params['w'], $params['h'], false, $g['image']['logo'], $watermarkParams['font_size'], $watermarkParams['file'], $watermarkParams['position']);
            }

            if($im->saveImage($params['path'], $g['image']['quality'])){
                if ($key == 'b') {
                    $imWidth = $im->getWidth();
                    $imHeight = $im->getHeight();
                    $result = array('w' => $imWidth, 'h' => $imHeight);
                } elseif ($key == 'th') {
                    CStatsTools::count('pics_uploaded');
                }
                Common::saveFileSize($params['path']);
            } else {
                break;
            }

        }

        $im->cleanFirstImageResource();

        return $result;
    }

    static function upload($file, $folder, $title, $desc = '', $time = '', $noAddWall = false)
	{
        global $g, $g_user;

        $guid = $g_user['user_id'];
        $wallUid = Wall::getUid();
        $isAllowLoadStrangerAlbum = false;
        if (Common::getOption('set', 'template_options') == 'urban' && $wallUid != $guid) {
            //$isAllowLoadStrangerAlbum = true;
            //$g_user['user_id'] = $wallUid;
        }

        //if ($isAllowLoadStrangerAlbum) {
        //    $g_user['user_id'] = $guid;
        //}

        $imgSize = self::uploadImage($file, $folder);
        if (!$imgSize) {
            return false;
        }

        $id = DB::result('SELECT `id`
                            FROM `gallery_albums`
                           WHERE `folder` = ' . to_sql($folder)
                         . ' AND `user_id` = ' . to_sql($guid, 'Number'));

        $date = date('Y-m-d H:i:s');
        if ($time == '') {
            $time = $date;
        }
        if($id == 0) {
            $access = 'public';
            if ($noAddWall) {
                $access = 'private';
            }
            $sql = "INSERT INTO `gallery_albums` ( `user_id` , `parentid` , `folder` , `title` , `desc` , `date` , `place` , `show` , `thumb` , `sort_type` , `sort_order` , `views`, `access` )
                                         VALUES ('" . $guid . "', NULL, " . to_sql($folder) . ', ' . to_sql(he(l($title))) . ", '', '" . $date . "', '' , '1', '" . self::$fileName . "', NULL , NULL , '0', " . to_sql($access) . ")";
            DB::execute($sql);
            $id = DB::insert_id();
        } else {
            $access = DB::result('SELECT `access`
                                    FROM `gallery_albums`
                                   WHERE `folder` = '.to_sql($folder)
                                 . ' AND `user_id` = ' . $guid);
        }

        $translated = '';
        if (self::$userToIm) {
            CIm::$msgTranslated = CIm::getTranslate($desc, self::$userToIm);
        }

        $row = array('user_id' => $guid,
                     'albumid' => $id,
                     'filename' => self::$fileName,
                     'width' => $imgSize['w'],
                     'height' => $imgSize['h'],
                     'datetime' => $time,
                     'title' => '',
                     'desc' => $desc
                );
        DB::insert('gallery_images', $row);
        $imgId = DB::insert_id();

   
        $sizesBaseImage = array(
            'th'  => array('path' => self::$uploadFileThumb,
                           'w' => 100,
                           'h' => 100
                     ),
            'src' => array('path' => self::$uploadFileSrc,
                           'w' => 1920,
                           'h' => 1080
                     ),
            'b'   => array('path' => self::$uploadFile,
                           'w' => $g['image']['gallery_width'],
                           'h' => $g['image']['gallery_height']
                     )
        );

        // var_dump($sizesBaseImage); die();
        foreach ($sizesBaseImage as $key => $params) {
            if(isS3SubDirectory(str_replace("temp/gallery", 'gallery', $params['path']))) {
                if($params['path']) {
                    $parts = explode("_files/temp/", $params['path']);
                    if (isset($parts[1]) && !empty($parts[1])) {

                        custom_file_upload($params['path'], $parts[1]);
                    }
                }
            }
        }

        if ($noAddWall) {
            @unlink($file);
            return $imgId;
        }

        Wall::addItemForUser($imgId, 'pics', $guid);
        Wall::addItemForUser($imgId, 'pics', $wallUid);
        $paramsSection = '';
        if (!$folder) {
            $paramsSection = 'timeline';
        }
        return Wall::add('pics', $id, $wallUid, $time, true, 0, $access, $guid, $paramsSection);
    }

    static function imageDelete($id, $uid, $deleteEmptyAlbum = true)
    {
        $sql = 'SELECT i.*, a.folder, a.thumb
            FROM gallery_images AS i
            LEFT JOIN gallery_albums AS a ON i.albumid=a.id
            WHERE i.id = ' . to_sql($id, 'Number') . '
                AND i.user_id = ' . to_sql($uid, 'Number');
        DB::query($sql, 1);
        if($row = DB::fetch_row(1)) {
            // IMAGE
            $sql = 'DELETE FROM gallery_images
                WHERE id = ' . to_sql($id, 'Number');
            DB::execute($sql);
            // COMMENTS
            $sql = 'SELECT * FROM gallery_comments
                WHERE imageid = ' . to_sql($row['id']);
            DB::query($sql, 2);
            while($comment = DB::fetch_row(2)) {
                self::commentDelete($comment['id'], $uid, true);
            }

            // FILES
            $row['type'] = 'images';
            $fileName = $row['filename'];
            $prepareFile = explode('.', $fileName);
            $image = self::albumPath($row) . $fileName;
            $imageSrc = self::albumPath($row) . $prepareFile[0] . '_src.' . $prepareFile[1];
            $row['type'] = 'thumb';
            $thumb = self::albumPath($row) . $fileName;
            Common::saveFileSize(array($image, $thumb, $imageSrc), false);
            
            //popcorn  modified s3 bucket gallery image delete
            if(isS3SubDirectory($image)) {
                custom_file_delete($image);
                custom_file_delete($imageSrc);
                custom_file_delete($thumb);
            }

            @unlink($image);
            @unlink($imageSrc);
            @unlink($thumb);

            // check if exists minimum one image from set
            $albumId = $row['albumid'];
            $time = $row['datetime'];

            $sql = 'SELECT COUNT(*) FROM gallery_images
                WHERE albumid = ' . to_sql($albumId, 'Number') . '
                    AND datetime = ' . to_sql($time, 'Text');
            $count = DB::result($sql, 0, 2);
            if($count == 0) {
                Wall::removeByParams('pics', $albumId, $row['datetime']);
            }

            if($deleteEmptyAlbum) {
                $sql = 'SELECT COUNT(*) FROM gallery_images
                    WHERE user_id = ' . to_sql($uid, 'Number') . '
                        AND albumid = ' . to_sql($albumId, 'Number');
                $images = DB::result($sql, 0, 2);
                if($images == 0) {
                    self::albumDelete($albumId, $uid);
                    return 'album_empty';
                }
            }

            if (!custom_file_exists(self::albumPath($row) . $row['thumb'])) {
                $sql = 'SELECT filename
                    FROM gallery_images
                    WHERE user_id = ' . to_sql($uid, 'Number') . '
                        AND albumid = ' . to_sql($albumId, 'Number');
                $thumb = DB::result($sql, 0, 2);
                $sql = 'UPDATE gallery_albums
                    SET thumb = ' . to_sql($thumb, 'Text') . '
                    WHERE user_id = ' . to_sql($uid, 'Number') . '
                        AND id = ' . to_sql($albumId, 'Number');
                DB::execute($sql);
            }

        }
    }

    static function albumDelete($id, $uid)
    {
        $sql = 'SELECT * FROM gallery_images
            WHERE albumid = ' . to_sql($id) . ' AND user_id = ' . to_sql($uid, 'Number');
        $rows = DB::rows($sql);
        if(Common::isValidArray($rows)) {
            foreach($rows as $row) {
                self::imageDelete($row['id'], $row['user_id'], false);
            }
        }

        $sql = 'SELECT * FROM gallery_albums
            WHERE id = ' . to_sql($id, 'Number');
        $vars = DB::row($sql);

        $sql = 'DELETE FROM gallery_albums
            WHERE id = ' . to_sql($id, 'Number');
        DB::execute($sql);

        if($vars) {
            $vars['type'] = 'images';
            $dirMain = self::albumPath($vars);
            $vars['type'] = 'thumb';
            $dirThumb = self::albumPath($vars);

            Common::dirRemove($dirMain);
            Common::dirRemove($dirThumb);
        }

        if (DB::count('gallery_albums', 'user_id = ' . to_sql($uid, 'Number')) == 0) {
            $vars['type'] = 'images';
            $dirMain = self::albumPathUser($vars);
            $vars['type'] = 'thumb';
            $dirThumb = self::albumPathUser($vars);

            Common::dirRemove($dirMain);
            Common::dirRemove($dirThumb);
        }
    }

    static function albumPath($vars)
    {
        global $g;
        return $g['path']['dir_files'] . 'gallery/' . $vars['type'] . '/' . $vars['user_id'] . '/' . $vars['folder'] . '/';
    }

    static function albumPathUser($vars)
    {
        global $g;
        return $g['path']['dir_files'] . 'gallery/' . $vars['type'] . '/' . $vars['user_id'] . '/';
    }

    static function commentsDeleteByUid($uid, $isAdmin = false)
    {
        $sql = 'SELECT id FROM gallery_comments
            WHERE user_id = ' . to_sql($uid, 'Number');
        $ids = DB::column($sql);
        foreach($ids as $id) {
            self::commentDelete($id, $uid, $isAdmin);
        }
    }

    static function commentDelete($id, $uid = false, $isAdmin = false, $dbIndex = DB_MAX_INDEX)
    {
        $delete = $isAdmin;

        if(!$delete && $uid) {
            $sql = 'SELECT * FROM gallery_comments WHERE id = ' . to_sql($id);
            DB::query($sql, $dbIndex);
            while($row = DB::fetch_row($dbIndex)) {
                if($row['user_id'] == $uid) {
                    $delete = true;
                } else {
                    $sql = 'SELECT user_id FROM gallery_images
                        WHERE id = ' . to_sql($row['imageid']);
                    $imageOwner = DB::result($sql, 0, $dbIndex);
                    if($imageOwner == $uid) {
                        $delete = true;
                    }
                }
            }
        }

        if($delete) {
            $sql = 'DELETE FROM `gallery_comments`
                WHERE id = ' . to_sql($id);
            DB::execute($sql);
            Wall::remove('pics_comment', $id);
        }
    }
}