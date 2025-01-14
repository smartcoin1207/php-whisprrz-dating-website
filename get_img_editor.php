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

//event image

if($photo_cmd == 'event_photos') {
    $where = '`image_id` = ' . to_sql($photoId);
    $isSiteAdministrator = Common::isSiteAdministrator();
    $isEventOwner = false;

    $pre_photo = DB::one("events_event_image", $where);
    if($pre_photo) {
        $event_id = $pre_photo['event_id'];
        $event = CEventsTools::retrieve_event_by_id($event_id);
        if($event) {
            $event_user_id = $event['event_id'];
            if($event_user_id == guid()) {
                $isEventOwner = true;
            }
        }
    }

    if(!$isSiteAdministrator && !$isEventOwner) {
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
    $isHotdateOwner = false;

    $pre_photo = DB::one("hotdates_hotdate_image", $where);
    if($pre_photo) {
        $hotdate_id = $pre_photo['hotdate_id'];
        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
        if($hotdate) {
            $hotdate_user_id = $hotdate['user_id'];
            if($hotdate_user_id == guid()) {
                $isHotdateOwner = true;
            }
        }
    }

    if(!$isSiteAdministrator && !$isHotdateOwner) {
        $where .= ' AND `user_id` = ' . to_sql(guid());
    }
    $photo = DB::one('hotdates_hotdate_image', $where);
    if ($photo) {
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
    $isPartyhouOwner = false;

    $pre_photo = DB::one("partyhouz_partyhou_image", $where);
    if($pre_photo) {
        $partyhou_id = $pre_photo['partyhou_id'];
        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
        if($partyhou) {
            $partyhou_user_id = $partyhou['user_id'];
            if($partyhou_user_id == guid()) {
                $isPartyhouOwner = true;
            }
        }
    }

    if(!$isSiteAdministrator && !$isHotdateOwner) {
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
            $where .= ' AND ' . Common::getNscUserWhere('IN');
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
            $im->resizeWH($imageSideSize, $imageSideSize, false, '', 0, '', '', false);
            imageJpeg($im->$image, null, $g['image']['quality']);
            $im->clearImage();
        } else {
            readfile($filename);
        }

        @unlink($filename);
    }
} else {
    header("HTTP/1.0 404 Not Found");
}