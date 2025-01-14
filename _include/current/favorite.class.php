<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CFavorite {
    static $table = 'friends';
    static $responseData = '';
    static $isPages = null;
    static $isContentList = null;

    public static function getListSubscribers($online = false, $limit = '', $fidIndex = false, $search_query = null) {
        global $g;

        $key = 'favorite_users_' . intval($online) . '_' . intval($fidIndex) . ($limit ? '_' . $limit : '_all');
        $subscribersList = Cache::get($key);
        if ($subscribersList !== null) {
            return $subscribersList;
        }

        if ($limit) {
            $limit = ' LIMIT ' . $limit;
        }

        $whereOnline = '';
        if ($online) {
            $whereOnline = ' AND (U.last_visit > ' . to_sql(date('Y-m-d H:i:s', time() - Common::getOption('online_time') * 60)) . ' OR U.use_as_online=1) ';
        }

        $sql = "SELECT DISTINCT U.*,
                DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(U.birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(U.birth, '00-%m-%d')) AS age
                FROM `user` as U
                        LEFT JOIN `" . self::$table . "` AS F1 ON U.user_id = F1.user_id
                        LEFT JOIN `" . self::$table . "` AS F2 ON U.user_id = F2.fr_user_id
                        WHERE (F1.fr_user_id =  " . to_sql(guid(), 'Number') . " OR F2.user_id = " . to_sql(guid(), 'Number') . ")" . $whereOnline .  '
                        ORDER BY user_id DESC' . $limit;

        $fetchType = DB::getFetchType();
        DB::setFetchType(MYSQL_ASSOC);
        $subscribersList = DB::rows($sql, 5, true);
        DB::setFetchType($fetchType);
        if ($fidIndex) {
            $result = array();
            foreach ($subscribersList as $key => $item) {
                $suid = $item['user_id'];
                $result[$suid] = $item;
                $photo = $g['path']['url_files'] . User::getPhotoDefault($item['user_id'], 's', false, $item['gender']);
                $result[$suid]['friend_photo'] = $photo;
                $result[$suid]['friend_url'] = User::url($suid);
                $result[$suid]['friend_name'] = $item['name'];
            }
            $subscribersList = $result;
        }
        Cache::add($key, $subscribersList);

        return $subscribersList;
    }
}