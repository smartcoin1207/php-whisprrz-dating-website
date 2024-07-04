<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['no_headers'] = true;

include("./_include/core/main_start.php");

$photoId = get_param_int('photo_id');
$photo_cmd = get_param('photo_cmd', '');
$photo = array();
$filename = false;

if($photo_cmd == 'event_photos') {
    $where = '`image_id` = ' . to_sql($photoId);
    $isSiteAdministrator = Common::isSiteAdministrator();
    if(!$isSiteAdministrator) {
        $where .= ' AND `user_id` = ' . to_sql(guid());
    }
    $photo = DB::one('events_event_image', $where);
    if ($photo ) {
        if($isSiteAdministrator) {
            $photo['private'] = 'N';
        }
        $filename = $g['path']['dir_files'] . "events_event_images/" . $photo['image_id'] . "_src.jpg";
        $part = explode('?', $filename);//no version
        $filename = $part[0];
    }
} elseif($photo_cmd == 'hotdate_photos') {
    $where = '`image_id` = ' . to_sql($photoId);
    $isSiteAdministrator = Common::isSiteAdministrator();
    if(!$isSiteAdministrator) {
        $where .= ' AND `user_id` = ' . to_sql(guid());
    }
    $photo = DB::one('hotdates_hotdate_image', $where);
    if ($photo ) {
        if($isSiteAdministrator) {
            $photo['private'] = 'N';
        }
        $filename = $g['path']['dir_files'] . "hotdates_hotdate_images/" . $photo['image_id'] . "_src.jpg";
        $part = explode('?', $filename);//no version
        $filename = $part[0];
    }
} elseif($photo_cmd == 'partyhou_photos') {
    $where = '`image_id` = ' . to_sql($photoId);
    $isSiteAdministrator = Common::isSiteAdministrator();
    if(!$isSiteAdministrator) {
        $where .= ' AND `user_id` = ' . to_sql(guid());
    }
    $photo = DB::one('partyhouz_partyhou_image', $where);
    if ($photo ) {
        if($isSiteAdministrator) {
            $photo['private'] = 'N';
        }
        $filename = $g['path']['dir_files'] . "partyhouz_partyhou_images/" . $photo['image_id'] . "_src.jpg";
        $part = explode('?', $filename);//no version
        $filename = $part[0];
    }
} else {
    if ($photoId) {
        $where = '`photo_id` = ' . to_sql($photoId);
        $isSiteAdministrator = Common::isSiteAdministrator();
        if(!$isSiteAdministrator) {
            $where .= ' AND `user_id` = ' . to_sql(guid());
        }
        $photo = DB::one('photo', $where);
        if ($photo) {
            if($isSiteAdministrator) {
                $photo['private'] = 'N';
            }
            $filename = $g['path']['dir_files'] . User::getPhotoFile($photo, 'src', '');
            $part = explode('?', $filename);//no version
            $filename = $part[0];
        }
    }
}

$ext = 'jpg';
if ($filename && custom_file_exists($filename)) {

    CProfilePhoto::createFileOrigImage($filename);

    $headers = apache_request_headers();
    $filemtime = gmdate('D, d M Y H:i:s', custom_filemtime($filename)) . ' GMT';

    if (isset($headers['If-Modified-Since']) && ($headers['If-Modified-Since'] == $filemtime)) {
        header('Last-Modified: ' . $filemtime, true, 304);
    } else {
        header('Pragma: public');
        header('Cache-Control: max-age=86400');
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Last-Modified: ' . $filemtime, true, 200);

        // Fix mime-type for IE on some server configurations
        if($ext == 'jpg') {
            $ext = 'jpeg';
        }

        header('Content-Type: image/' . $ext);
        
        //popcorn modified s3 bucket image editor 2024-05-09
        if(isS3SubDirectory($filename)) {
            $new_path = custom_temp_file_download($filename);
            if($new_path) {
                $filename = $new_path;
            }
        }
        
        $im = new Image();
        $im->loadImage($filename);

        $imageSideSize = 2000;

        if($im->getHeight() > $imageSideSize || $im->getWidth() > $imageSideSize) {
        //        $imageSizes = array();
        //        $imageSizeSubTypes = array('x', 'y');
        //        foreach(CProfilePhoto::$sizesBasePhoto as $imageSizeType) {
        //            foreach($imageSizeSubTypes as $imageSizeSubType) {
        //                $imageSizes[] = intval(trim(Common::getOption($imageSizeType . '_' . $imageSizeSubType, 'image')));
        //            }
        //        }
        //
        //        $maxImageSize = max($imageSizes);
        //        $imageSideSize = min(2000, $maxImageSize);

            $im->resizeWH($imageSideSize, $imageSideSize, false, '', 0, '', '', false);
            imageJpeg($im->$image, null, $g['image']['quality']);
            $im->clearImage();
        } else {
            //header('Content-Length: ' . filesize($filename));
            readfile($filename);
        }

        @unlink($filename);
    }
} else {
    header("HTTP/1.0 404 Not Found");
}