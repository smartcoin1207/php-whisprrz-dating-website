<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CityFaceEditor
{
    static function host()
    {
        global $g;

        $url = $g['media_server'] . '/3dcity_face.php';

        $file = __DIR__ . '/../config/3dcity_face.php';
        if(file_exists($file)) {
            include $file;
        }
        return $url;
    }

    static function apiCall($params = array())
    {
        return urlGetContents(self::host() . '?' . http_build_query($params));
    }

    static function deleteOnHost($file)
    {
        self::apiCall(array('cmd' => 'delete', 'file' => $file));
    }

    static function checkChangesFaceParams($cityUid, $photoId, $paramsFace, $colorHex, $colorHexL){
        //Check the current face is edited and changed if its parameters
        $faceId = 0;
        $where = '`photo_id` = ' . to_sql($photoId) .
        	 ' AND `user_id` = ' . to_sql($cityUid);
        $currentFace = DB::select(City::getTable('city_avatar_face'), $where);
        if ($currentFace && isset($currentFace[0])){
            $faceId = $currentFace[0]['id'];
            if (($currentFace[0]['head_color'] == $colorHex || $currentFace[0]['head_color'] == $colorHexL)
                && $currentFace[0]['params'] == $paramsFace) {
                //If you do not change simply return Complete
                $faceId = null;
                CityFaceEditor::sendMessageComplete($cityUid, $photoId, $currentFace[0]['hash'], $currentFace[0]['head_color']);
            }
        }
        return $faceId;
    }

    static function saveFaceParams($cityUid, $faceId, $photoId, $params, $colorHex){
        global $g;
        $hash = time();
        $row = array(
            'photo_id' => $photoId,
            'user_id' => $cityUid,
            'params' => $params,
            'head_color' => $colorHex,
            'hash' => $hash);
        if ($faceId) {
            DB::update(City::getTable('city_avatar_face'), $row, '`id` = ' . to_sql($faceId));
        } else {
            DB::insert(City::getTable('city_avatar_face'), $row);
        }
        self::sendMessageComplete($cityUid, $photoId, $hash, $colorHex);
    }

    static function sendMessageComplete($uid, $photoId, $hash, $colorHex){
        global $g;
        $fileFaceCity = "{$g['path']['url_files_city']}city/users/{$uid}_{$photoId}.jpg";
        self::sendMessage("complete@{$fileFaceCity}?v={$hash}@$colorHex");
    }

    static function sendMessage($message){
        echo $message . str_repeat(' ',1024*64);
        @ob_flush();
        flush();
    }

    static function getHexByRGB($rgb) {
        return sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
    }
}