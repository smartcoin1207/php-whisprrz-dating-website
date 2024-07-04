<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class OutsideImages
{
    static public $addStyleLoad = false;
    static public $addScriptLoad = '';
    static public $userId = 0;

    static $sizesIm = array(
        array(
	        'width' => 588,
	        'height' => 440,
	        'allow_smaller' => true,
	        'file_postfix' => 'th',
            ),
        array(
	        'width' => 1024,
	        'height' => 768,
	        'allow_smaller' => true,
	        'file_postfix' => 'b',
            )
    );

    static $sizesBaseImage = array('orig' => array(0, 0),
                                   'b'    => array(1920, 1080),
                                   'th'   => array(888, 888));

	static public function filter_to_db($text, $old_text = null, $screenLink = false)
    {
    	self::do_upload_images($text);

    	$old_ids = self::retrieve_ids($old_text);
    	$new_ids = self::retrieve_ids($text);

    	foreach($old_ids as $old_id)
    	{
    		$key = array_search($new_ids, $old_id);
    		if($key !== false)
    		{
    			unset($new_ids[$key]);
    		}
    		else
    		{
    			self::delete_image($old_id);
    		}
    	}

        if ($screenLink) {
            self::do_upload_link_meta_site($text);
        }

    	return $text;
    }

    static public function filter_to_html($text, $start_tag, $end_tag, $a_class, $target = '_blank', $isCalcMaxWidth = false, $prfId = '', $imMsg = false, $parseMetaLink = false)
    {
        global $g;
        global $sitePart;

        $tmplWallType = Common::getOptionTemplate('wall_type');
        $isEdgeWall = $tmplWallType == 'edge';

        $ids = self::retrieve_ids($text);

        foreach ($ids as $id){
        	$image = self::image_by_id($id);
        	if ($image){
                self::imageThereReupload($image);

                $ext = $image['gif'] ? 'gif' : 'jpg';

                $filePrefix = $g['path']['url_files'] . "outside_images/" . $image['image_id'];
                $image_meta_url = $image_b = "{$filePrefix}_b." . $ext;
                $image_th = "{$filePrefix}_th." . $ext;

                if ($isCalcMaxWidth) {
                    Wall::calcMaxImageWidth($image_th);
                }

                $tmplImageWidth = Common::getOption('im_send_image_width', 'template_options');

                $addStyleLoad = '';
                if ($tmplImageWidth && self::$addStyleLoad !== false) {
                    $newWidth = $image['width'];
                    $newHeight = $image['height'];
                    if (!$newWidth || !$newHeight) {
                        if (custom_file_exists($image_th)) {
                            $imageSize = @custom_getimagesize($image_th);
                            $newWidth = $imageSize[0];
                            $newHeight = $imageSize[1];
                        }
                        if ($newWidth && $newHeight) {
                            DB::update('outside_image', array('width' => $newWidth, 'height' => $newHeight), 'image_id = ' . to_sql($id));
                        }
                    }
                    if ($newWidth && $newHeight) {
                        if (Common::isOptionActiveTemplate('im_send_image_data_params')) {
                            $date = Common::dateFormat($image['created_at'], 'photo_date');
                            $timeAgo = timeAgo($image['created_at'], 'now', 'string', 60, 'second');

                            $userinfo = User::getInfoBasic(self::$userId);
                            $userName = toAttr($userinfo['name']);
                            $userPhoto = toAttr(User::getPhotoDefault(self::$userId, 'r'));
                            $userUrl = toAttr(User::url(self::$userId, $userinfo));

                            $addStyleLoad = 'data-width="' . $newWidth . '" data-height="' . $newHeight . '" ' .
                                                  'data-date="' . $date . '" data-time-ago="' . $timeAgo . '" ' .
                                                  'data-user-name="' . $userName . '" data-user-url="' . $userUrl . '" ' .
                                                  'data-user-photo="' . $userPhoto . '" ';
                        } else {
                            $display = get_param('display');
                            $maxWidth = $tmplImageWidth['default'];
                            if (isset($tmplImageWidth[$display])) {
                                $maxWidth = $tmplImageWidth[$display];
                            }
                            //if (CIm::$modFakeReplies) {
                                //$maxWidth = 330;
                            //}

                            if ($newWidth > $maxWidth) {
                                $ratio = $maxWidth/$newWidth;
                                $newWidth = $maxWidth;
                                $newHeight = round($newHeight * $ratio, 1);
                            }
                            $addStyleLoad = 'style="width:' . $newWidth . 'px; height:' . $newHeight . 'px;"';
                        }

                    }
                }

                $tagId = 'outside_img_' . $prfId . $id;
                $tag = '{img:' . $id . '}';

                $description = '';
                $targetBlank = '';
                if ($parseMetaLink && $image['meta_id']) {//Link meta
                    $meta = self::metaLinkInfoById($image['meta_id']);
                    if ($meta) {
                        $image_th = $image_b;
                        $a_class = 'timeline_photo_link_meta';
                        $metaUrl = $meta['canonical'] ? $meta['canonical'] : $meta['url'];
                        $image_meta_url = $metaUrl;
                        $targetBlank = ' target="_blank"';
                        $target = '_blank';

                        $metaTitle = $meta['title'];
                        if (!$metaTitle) {
                            $metaTitle = $metaUrl;
                        }
                        $description = '<a class="wall_post_meta_link_bl" target="_blank" href="' . $metaUrl . '">';
                        $description .= '<span class="wall_post_meta_link_title">' . $metaTitle . '</span>';

                        if ($meta['description']) {
                            $description .= '<span class="wall_post_meta_link_description">' . $meta['description'] . '</span>';
                        }
                        $description .= '</a>';

                        $start_tag = str_replace('{start_additional_class}', 'wall_image_post_meta_link', $start_tag);
                    }
                }

                $start_tag = str_replace('{start_additional_class}', '', $start_tag);

                if ($isEdgeWall && !$imMsg) {
                    $tagHtml = $start_tag
                                . '<a data-id="' . $tagId . '"' . $targetBlank . ' class="' . $a_class . ' ' . $tagId . '" href="' . $image_meta_url . '">'
                                    . '<img src="' . $image_th . '" alt=""/>'
                                . '</a>'
                                . $description
                               . $end_tag;

                    if ($sitePart != 'administration') {
                        $tagHtml .= "<script class=\"init_show_load_img\">onLoadImgTimeLine('" . $tagId . "');</script>";
                        //$tagHtml .= "<script>clWall.onLoadImgTimeLine('" . $tagId . "');</script>";
                    }
                } else {
                    $tagHtml = $start_tag
                                    . '<a target="' . $target . '" class="' . $a_class . '" href="' . $image_meta_url . '">'
                                        . '<img ' . self::$addScriptLoad . $addStyleLoad . ' data-src-b="' . $image_b . '" id="' . $tagId . '" src="' . $image_th . '" alt=""/>'
                                    . '</a>'
                                    . $description
                                . $end_tag;
                }
                $text = Common::getTextTagsToBr($text, $tag, $tagHtml);
                #$text = str_replace($tag, $start_tag . '<a target="' . $target . '" class="'.$a_class.'" href="' . $image_b . '"><img src="' . $image_th . '" alt=""/></a>' . $end_tag, $text);
        	}
        }

        return $text;
    }

    static public function on_delete($text)
    {
        $ids = self::retrieve_ids($text);

        foreach($ids as $id)
        {
        	self::delete_image($id);
        }
    }

    static private function delete_image($id)
    {
        global $g;

    	$image = self::image_by_id($id);
    	if($image){
	    	if($image['image_n_links'] > 1)	{
	    		DB::execute("UPDATE outside_image SET image_n_links = image_n_links - 1 WHERE image_id = " . to_sql($image['image_id']) . " LIMIT 1");
	    	} else {
	    		$filePrefix = $g['path']['dir_files'] . 'outside_images/' . $image['image_id'];

                $ext = $image['gif'] ? 'gif' : 'jpg';

                foreach(self::$sizesBaseImage as $k => $size) {

                    $file = "{$filePrefix}_{$k}.{$ext}";
                    Common::saveFileSize($file, false);
	            	@unlink($file);
                }
                self::deleteImageDb($image['image_id']);
	    	}
    	}
    }

    static private function retrieve_ids($text)
    {
    	return grabs($text, '{img:', '}');
    }

    static private function do_upload_images(&$text)
    {
        $text = Common::parseLinks($text, '', '', 'getImgTagToDb');
    }

    static private function do_upload_link_meta_site(&$text)
    {
        //$text = Common::parseLinks($text, '', '', 'getLinkMetaSite');

        $pattern = "/\b(?:((?:https?|ftp)):\/\/|www\.)([-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])/i";
        preg_match_all($pattern , $text, $matches);
        if (isset($matches[0]) && $matches[0]) {
            $reversed = array_reverse($matches[0]);
            foreach ($reversed as $key => $link) {
                $linkResult = self::uploadLinkMetaSite($link);
                if(strpos($linkResult, '{meta_link') !== false) {
                    $text = $text . ' ' . $linkResult;
                    break;
                }
            }
            //$lastLink = array_pop($matches[0]);
            //$text = $text . ' ' . self::uploadLinkMetaSite($lastLink);

            //print_r_pre($lastLink);
        }
    }

    static private function image_by_id($id)
    {
    	return DB::row("SELECT * FROM outside_image WHERE image_id = " . to_sql($id) . " LIMIT 1", DB_MAX_INDEX);
    }

    public static function createBaseImageFile($image, $baseFileName, $sourceFile, $metaId = 0)
    {
        global $g;


        $watermarkParams = CProfilePhoto::watermarkParams();

        // make copy of source file if enough memory or load from file

        $first = true;

        /* Maximum image width https://sitesman.com/s/1014/1014-2019-02-18_13-19-06.png */
        foreach(self::$sizesBaseImage as $k => $size) {
            $fileName = $baseFileName . $k . '.jpg';
            if($first) {
                //$sizeX = imageSX($image->image);
                //$sizeY = imageSY($image->image);
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
                    $image->loadImage($sourceFile);
                }
                $image->saveImageResourceCopy = true;

            }
            if ($k != 'orig') {
                $sizeX = $size[0];
                $sizeY = $size[1];
                if ($metaId) {
                    $image->resizeWH($sizeX, $sizeY, false, '', 0, '', '', false);
                } else {
                    $image->resizeWH($sizeX, $sizeY, false, $g['image']['logo'], $watermarkParams['font_size'], $watermarkParams['file'], $watermarkParams['position']);
                }
            }

            if(!$image->saveImage($fileName, $g['image']['quality'])) {
                return false;
            }
            Common::saveFileSize($fileName);
            //$image->clearImage();

            @chmod($fileName, 0777);

            $first = false;
        }

        return true;
    }

    static public function do_upload_image($url, $reupload = false, $metaId = 0)
    {
        global $g;

        $guid = guid();
        $image = DB::one('outside_image', '`outside_url` = ' . to_sql($url));
        if($image && !$reupload) {
            DB::execute('UPDATE `outside_image` SET `image_n_links` = `image_n_links` + 1 WHERE `outside_url` = ' . to_sql($url));
            return $image;
        }

        if (!$reupload) {
            $image = array(
                'user_id' => $guid,
                'outside_url' => $url
            );
        }

        $hash = $guid . '_' . md5(rand(100000, 9999999));
    	$tempFile = $g['path']['dir_files'] . "temp/outgoing_image_{$hash}.txt";
        @copyUrlToFile($url, $tempFile);
        $result = false;

        $imageId = 0;
        if ($image && isset($image['image_id'])) {
            $imageId = $image['image_id'];
        }
        if (file_exists($tempFile)){
            $im = new Image();
            if ($im->loadImage($tempFile)) {

                $isGif = ($im->image_type === 'GIF' && $im->isAnimatedGif($tempFile));

                if (!$reupload) {
                    $row = array('user_id'       => $image['user_id'],
                                 'outside_url'   => $image['outside_url'],
                                 'image_n_links' => 1,
                                 'created_at'    => date('Y-m-d H:i:s'),
                                 'meta_id'       => $metaId,
                                 'gif'           => $isGif,
                           );
                    DB::insert('outside_image', $row);
                    $image['image_id'] = DB::insert_id();
                    $imageId = $image['image_id'];
                }

                $sFile_ = $g['path']['dir_files'] . "outside_images/" . $image['image_id'] . '_';

                if($isGif) {
                    copy($tempFile, $sFile_ . 'b.gif');
                    copy($tempFile, $sFile_ . 'orig.gif');
                    copy($tempFile, $sFile_ . 'th.gif');
                    $result = $image;
                } else {
                    if (self::createBaseImageFile($im, $sFile_, $tempFile, $metaId)){
                        $result = $image;
                    }
                }
            }
            @unlink($tempFile);
        }

        if (!$result && $imageId) {
            self::deleteImageDb($imageId);
        }

        return $result;
    }

    static public function deleteImageDb($id)
    {
        DB::delete('outside_image', '`image_id` = ' . to_sql($id));
    }

    static public function do_upload_image_old($url, $image_sizes, $reupload = false)
    {
        global $g;
        global $g_user;

        $image = DB::row("SELECT * FROM outside_image WHERE outside_url = " . to_sql($url) . " LIMIT 1");

        if($image && !$reupload)
        {
            DB::execute("UPDATE outside_image SET image_n_links = image_n_links + 1 WHERE outside_url = " . to_sql($url) . " LIMIT 1");

            return $image;
        }

        if (!$reupload) {
        $image = array(
            'user_id' => $g_user['user_id'],
            'outside_url' => $url);
        }

    	$temp_file = $g['path']['dir_files'] . "temp/outgoing_image_" . md5(rand(100000, 9999999)) . '.txt';
        @copyUrlToFile($url, $temp_file);
        if (file_exists($temp_file))
        {
            $failed = false;

            if (!$reupload) {
                    DB::execute('INSERT INTO `outside_image`
                                    SET user_id = ' . to_sql($image['user_id'], 'Number') .
                                     ', outside_url = ' . to_sql($image['outside_url']) .
                                     ', image_n_links = 1, created_at = NOW()');

                    $image['image_id'] = DB::insert_id();
            }
        	$file_prefix = $g['path']['dir_files'] . "outside_images/" . $image['image_id'];

        	foreach($image_sizes as $image_size)
            {
            	$im = new Image();
	            if ($im->loadImage($temp_file)) {
                    $flag = true;
                    if (!$image_size['allow_smaller'] ||
                        ($im->getWidth() > $image_size['width'] &&
                        $im->getHeight() > $image_size['height']))
                    {
                        if ($flag) {
                            $imWidth = $im->getWidth();
                            if($imWidth > $image_size['width']) {
                                $imWidth = $image_size['width'];
                            }
                            $im->resizeWH($imWidth, $image_size['height']);
                        } else {
                             $im->resizeCroppedMiddle($image_size['width'], $image_size['height']);
                        }
                    }
                    elseif ($im->getWidth() > $image_size['width'])
                    {
                        $im->resizeW($image_size['width']);
                    }
                    elseif ($im->getHeight() > $image_size['height'])
                    {
                        $im->resizeH($image_size['height']);
                    }
                    else
                    {
                    	copy($temp_file, $file_prefix . "_".$image_size['file_postfix'].".jpg");
                        Common::saveFileSize($file_prefix . "_".$image_size['file_postfix'].".jpg");
                    	//break;
                    }

	                $im->saveImage($file_prefix . "_".$image_size['file_postfix'].".jpg", $g['image']['quality']);
                    Common::saveFileSize($file_prefix . "_".$image_size['file_postfix'].".jpg");
	            }
	            else
	            {
	                $failed = true;
	                break;
	            }
            }

            if(!$failed)
            {
	            //original
	            $im = new Image();
	            if ($im->loadImage($temp_file)) {
	                $im->saveImage($file_prefix . "_orig.jpg", $g['image']['quality_orig']);//80
                        Common::saveFileSize($file_prefix . "_orig.jpg");
	            }
	            else
	            {
	            	$failed = true;
	            }
            }

            @unlink($temp_file);

            if($failed)
            {
            	DB::execute('DELETE FROM outside_image WHERE image_id = ' . to_sql($image['image_id'], 'Number') . ' LIMIT 1');

            	return null;
            }

            return $image;
        }

        return null;
    }

    static public function imageThereReupload($image)
    {
        global $g;

        $filePrefix = $g['path']['url_files'];
        if (self::$addStyleLoad && Common::getTmplSet() == 'old' && !Common::isMobile()) {//OLD template im image
            $filePrefix = $g['path']['url_files_root'];
        }
        $filePrefix .= 'outside_images/' . $image['image_id'];

        $ext = $image['gif'] ? 'gif' : 'jpg';

        $imageTh = "{$filePrefix}_th.{$ext}";

        if (!custom_file_exists($imageTh)) {
            $imageOrig = "{$filePrefix}_orig.{$ext}";
            $imageB = "{$filePrefix}_b.{$ext}";
            Common::saveFileSize(array($imageOrig, $imageB, $imageTh), false);
            @unlink($imageOrig);
            @unlink($imageB);
            @unlink($imageTh);
            self::do_upload_image($image['outside_url'], true);
        }
    }

    /* Meta links */
    static private function retrieve_ids_meta_links($text)
    {
    	return grabs($text, '{meta_link:', '}');
    }

    static public function filter_meta_link_to_html($text)
    {

        $metaLinks = self::retrieve_ids_meta_links($text);
        //var_dump_pre($metaLinks);

        foreach ($metaLinks as $id){
            $tag = '{meta_link:' . $id . '}';
            $id = explode(':', $id);
            $idImage = isset($id[1]) ? $id[1] : 0;
            $id = $id[0];

        	$meta = self::metaLinkInfoById($id);
            if ($meta){
                if ($idImage) {
                    $text = Common::getTextTagsToBr($text, $tag, '{img:' . $idImage . '}');
                } else {
                    $metaUrl = $meta['canonical'] ? $meta['canonical'] : $meta['url'];
                    $metaTitle = $meta['title'];
                    if (!$metaTitle) {
                        $metaTitle = $metaUrl;
                    }
                    $description = '<a class="wall_post_meta_link_bl" target="_blank" href="' . $metaUrl . '">';
                    $description .= '<span class="wall_post_meta_link_title">' . $metaTitle . '</span>';

                    if ($meta['description']) {
                        $description .= '<span class="wall_post_meta_link_description">' . $meta['description'] . '</span>';
                    }
                    $description .= '</a>';
                    $text = Common::getTextTagsToBr($text, $tag, $description);
                }
            } else {
                $text = Common::getTextTagsToBr($text, $tag, '');
            }
        }

    	return $text;
    }

    static public function deleteMetaLinks($text)
    {
        $ids = self::retrieve_ids_meta_links($text);

        foreach($ids as $id)
        {
        	self::deleteOneMetaLink($id);
        }
    }

    static private function deleteOneMetaLink($id)
    {
        global $g;

        $id = explode(':', $id);

        $id = $id[0];
        $meta = self::metaLinkInfoById($id);
        if($meta){
            if ($meta['links_number'] > 1)	{
	    		DB::execute('UPDATE `meta_link_info` SET links_number = links_number - 1 WHERE `id` = ' . to_sql($id));
	    	} else {
                DB::delete('meta_link_info', 'id = ' . to_sql($id));
	    	}
            if ($meta['image_id']) {
                self::delete_image($meta['image_id']);
            }
        }
    }

    static private function metaLinkInfoById($id)
    {
    	return DB::row("SELECT * FROM meta_link_info WHERE id = " . to_sql($id) . " LIMIT 1");
    }

    static public function uploadLinkMetaSite($url)
    {
        global $g;

        $guid = guid();

        $info = DB::one('meta_link_info', '`url` = ' . to_sql($url));
        if($info) {
            DB::execute('UPDATE `meta_link_info` SET `links_number` = `links_number` + 1 WHERE `url` = ' . to_sql($url));
            if ($info['image_id']) {
                DB::execute('UPDATE `outside_image` SET `image_n_links` = `image_n_links` + 1 WHERE `meta_id` = ' . to_sql($info['id']));

                $url = " {meta_link:" . $info['id']  . ':' . $info['image_id'] . '}';
            } else {
                $url = " {meta_link:" . $info['id'] . '}';
            }
            return $url;
        }

        $metaInfo = getMetaSite($url);

        if (!$metaInfo['title'] && !$metaInfo['description'] && !$metaInfo['image']) {
            return $url;
        }

        $row = array(
            'user_id'       => $guid,
            'url'           => $url,
            'links_number'  => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'title'         => $metaInfo['title'],
            'description'   => $metaInfo['description'],
            'canonical'     => $metaInfo['canonical']
        );
        DB::insert('meta_link_info', $row);
        $metaId = DB::insert_id();

        $urlResult = " {meta_link:" . $metaId . '}';

        if (!$metaInfo['image']) {
            return $urlResult;
        }

        $image = OutsideImages::do_upload_image($metaInfo['image'], false, $metaId);

        if ($image && isset($image['image_id'])) {
            DB::update('meta_link_info', array('image_id' => $image['image_id']), 'id = ' . to_sql($metaId));
            $urlResult = " {meta_link:" . $metaId . ':' . $image['image_id'] . '}';
        }

        return $urlResult;
    }

    static public function uploadLinkMetaSite_ALL($url)
    {
        global $g;

        $guid = guid();

        $info = DB::one('meta_link_info', '`url` = ' . to_sql($url));
        if($info) {
            DB::execute('UPDATE `meta_link_info` SET `links_number` = `links_number` + 1 WHERE `url` = ' . to_sql($url));
            if ($info['image_id']) {
                DB::execute('UPDATE `outside_image` SET `image_n_links` = `image_n_links` + 1 WHERE `meta_id` = ' . to_sql($info['id']));

                $url = $url . " {meta_link:" . $info['id']  . ':' . $info['image_id'] . '}';
            } else {
                $url = $url . " {meta_link:" . $info['id'] . '}';
            }
            return $url;
        }

        $metaInfo = getMetaSite($url);

        if (!$metaInfo['title'] && !$metaInfo['description'] && !$metaInfo['image']) {
            return $url;
        }

        $row = array(
            'user_id'       => $guid,
            'url'           => $url,
            'links_number'  => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'title'         => $metaInfo['title'],
            'description'   => $metaInfo['description'],
            'canonical'     => $metaInfo['canonical']
        );
        DB::insert('meta_link_info', $row);
        $metaId = DB::insert_id();

        $urlResult = $url . " {meta_link:" . $metaId . '}';

        if (!$metaInfo['image']) {
            return $urlResult;
        }

        $image = OutsideImages::do_upload_image($metaInfo['image'], false, $metaId);

        if ($image && isset($image['image_id'])) {
            DB::update('meta_link_info', array('image_id' => $image['image_id']), 'id = ' . to_sql($metaId));
            $urlResult = $url . " {meta_link:" . $metaId . ':' . $image['image_id'] . '}';
        }

        return $urlResult;
    }
    /* Meta links */
}