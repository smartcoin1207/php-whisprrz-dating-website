<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class GroupsPhoto {

    static function getPhotoDefault($uid, $groupId, $size = 's', $returnId = false, $noVisPrivate = false, $getPhotoInfo = false, $isOnlyAvailableToAll = false, $cache = true) {
        return User::getPhotoDefault($uid, $size, $returnId, false, DB_MAX_INDEX, $noVisPrivate, $getPhotoInfo, $isOnlyAvailableToAll, false, $groupId, $cache = true);
    }

    static function photoToDefault($pid, $groupId) {
        $guid = guid();

        $whereGroup = ' AND `group_id` = ' . to_sql($groupId);
        $sql = 'SELECT `photo_id` FROM `photo`
                 WHERE `user_id` = ' . to_sql($guid) . $whereGroup .
                 ' AND `default_group` = "Y"
                 LIMIT 1';
        $photoDefault = DB::result($sql);

        if($photoDefault == 0) {
            $sql = 'SELECT `photo_id` FROM `photo`
                     WHERE `user_id` = ' . to_sql($guid) . $whereGroup .
                ' ORDER BY photo_id ASC
                     LIMIT 1';
            $photoDefault = DB::result($sql);
        }

        $sql = "UPDATE `photo`
                   SET `default_group` = 'Y'
                 WHERE `photo_id` = " . to_sql($pid) . "
                   AND `user_id` = " . to_sql($guid);
        DB::execute($sql);

        $sql = "UPDATE `photo`
                   SET `default_group` = 'N'
                 WHERE `photo_id` != " . to_sql($pid) . $whereGroup . "
                   AND `user_id` = " . to_sql($guid);
        DB::execute($sql);

        if($photoDefault && $photoDefault != $pid) {
            $sql = 'SELECT `private` FROM `photo`
                     WHERE `photo_id` = ' . to_sql($pid) .
                     ' AND `user_id` = ' . to_sql($guid) .
                   ' LIMIT 1';
            $access = (DB::result($sql) == 'Y') ? 'friends' : 'public';
            Wall::add('photo_default', $pid, false, $pid, false, 0, $access, 0, '', $groupId);
        }
    }

    static function isPhotoDefaultPublic($groupId, $uid = null, $checkApprovalPhoto = false) {
        $responseData = false;
        if ($uid === null) {
            $uid = guid();
        }
        if ($uid) {
            $numbersPhoto = self::getNumberPhotos($groupId);
            if (!$numbersPhoto) {
                return false;
            }
            $pid = self::getPhotoDefault($uid, $groupId, '', true);
            $where = '`photo_id` = ' . to_sql($pid) . ' AND `private` = "N"';
            if ($checkApprovalPhoto) {
                $where .= ' AND `visible` = "Y"';
            }
            $responseData = DB::count('photo', $where);
        }
        return $responseData;
    }

    static function getNumberPhotos($groupId, $uid = null, $private = null) {

        if ($uid === null) {
            $uid = guid();
        }

        $where = '`user_id` = ' . to_sql($uid)
          . " AND `visible` != 'P'
              AND `group_id` = " . to_sql($groupId);

        if ($private !== null) {
            $where .= ' AND `private` = ' . to_sql($private ? 'Y' : 'N');
        }

        return DB::count('photo', $where);
    }

    static function checkPhotoDefault($pid, $groupId, $type = 'public') {
        $guid = guid();
        if(!self::getPhotoDefault($guid, $groupId, '', true)
            || (!self::isPhotoDefaultPublic($groupId) && $type == 'public')){
            self::photoToDefault($pid, $groupId);
        }
    }

    static function deleteTempPhoto(){
        $guid = guid();
        $pid = get_param_int('photo_id');

        if (!$guid || !$pid) {
            return false;
        }

        $where = '`photo_id` = ' . to_sql($pid) . ' AND `user_id` =' . $guid;
        $photoInfo = DB::one('photo', $where);
        if (!$photoInfo) {
            return false;
        }
        deletephoto($guid, $pid);
        return true;
    }

    static public function isVisiblePlugPrivatePhotoFromId($uid, $pid, $groupId) {
        $key = "no_vis_private_photo_{$uid}_{$pid}_{$groupId}";
        return Cache::get($key);
    }
}