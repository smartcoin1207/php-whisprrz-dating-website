<?php
class Songs
{
    static $table = 'music_song';
    static $tableImage = 'music_song_image';
    static $tableCategory = 'music_category';
    static $tableAlbum = 'music_musician';
    static $categoryDefault = 'Different';
    static $albumDefault = 'Different singers';

    static $isGetDataWithFilter = false;

    static function createCategory($title)
    {
        if (!$title) {
            return 0;
        }
        $category = DB::one(self::$tableCategory, '`category_title` = ' . to_sql($title));
        if (!$category) {
            $sql = 'SELECT `position` FROM ' . self::$tableCategory . ' ORDER BY position DESC LIMIT 1';
            $position = DB::result($sql);
            $data = array(
                'category_title' => $title,
                'position' => $position + 1
            );
            DB::insert(self::$tableCategory, $data);
            $categoryId = DB::insert_id();

            return $categoryId;
        }
        return 0;
    }

    static function createCategoryDefault()
    {
        self::createCategory(self::$categoryDefault);
    }

    static function getDefaultCategoryId()
    {
        $category = DB::one(self::$tableCategory, '`category_title` = ' . to_sql(self::$categoryDefault));
        if ($category) {
            $categoryId = $category['category_id'];
        } else {
            $categoryId = self::createCategoryDefault();
        }
        return $categoryId;
    }

    static function createAlbum($name, $categoryId)
    {
        $guid = guid();
        $date = date('Y-m-d H:i:s');

        $countryId = 0;
        $cityInfo = IP::geoInfoCityDefault();
        if(isset($cityInfo['country_id'])) {
            $countryId = $cityInfo['country_id'];
        }

        $name = trim($name);

        $data = array(
                'user_id' => $guid,
                'musician_name' => $name,
                'musician_founded' => date('Y'),
                'category_id' => $categoryId,
                'country_id' => $countryId,
                'created_at' => $date,
                'updated_at' => $date
        );
        DB::insert(self::$tableAlbum, $data);

        $albumId = DB::insert_id();

        return $albumId;
    }

    static function createAlbumDefault()
    {
        $categoryId = self::getDefaultCategoryId();
        return self::createAlbum(self::$albumDefault, $categoryId);
    }

    static function getDefaultAlbumId()
    {
        $album = DB::one(self::$tableAlbum, '`musician_name` = ' . to_sql(self::$albumDefault) . ' AND `user_id` = ' . guid());
        if ($album) {
            $albumId = $album['musician_id'];
        } else {
            $albumId = self::createAlbumDefault();
        }
        return $albumId;
    }

    static function publishSongs()
    {
        global $g;

        $guid = guid();
        $songs = get_param('songs');

        if (!$songs) {
            return false;
        }

        $groupId = get_param_int('group_id');

        $response = array();

        require_once("./_include/current/music/tools.php");
        CMusicTools::cleanup_upload_folder();

        $albumId = self::getDefaultAlbumId();

        $groupPage = 0;
        $groupPrivate = 'N';
        if ($groupId) {
            $groupInfo = Groups::getInfoBasic($groupId);
            $groupPage = $groupInfo['page'];
            $groupPrivate = $groupInfo['private'];
        }

        foreach ($songs as $fileName => $song) {

            $songTitle = strip_tags($song['desc']);//255
            $date = date('Y-m-d H:i:s');

            $data = array(
                'user_id' => $guid,
                'song_title' => $songTitle,
                'musician_id' => $albumId,
                'song_year'  => date('Y'),
                'song_length' => ceil($song['length']),
                'created_at' => $date,
                'updated_at' => $date,
                'group_id' => $groupId,
                'group_page' => $groupPage,
                'group_private' => $groupPrivate
            );
            DB::insert(self::$table, $data);
            $songId = DB::insert_id();

            $fileImage = false;
            if ($song['cover']) {

                //popcorn modified s3 bucket music upload 2024-05-06
                $fileImage = $g['path']['dir_files'] . "music/tmp/{$fileName}.jpeg";

                if(isS3SubDirectory($fileImage)) {
                    $fileImage = $g['path']['dir_files'] . "temp/{$fileName}.jpeg";
                }

                if (!file_exists($fileImage)){
                    $fileImage = false;
                }
            }

            if (CMusicTools::move_uploaded_file($fileName, $songId) === false) {
                CMusicTools::delete_song($songId);
                if ($fileImage) {
                    @unlink($fileImage);
                }
            } else {
                CStatsTools::count('mp3_uploaded');
                if ($fileImage) {
                    CMusicTools::do_upload_song_image('', $songId, false, $fileImage);
                }
                Wall::add('music', $songId);
            }
        }

        $count = self::getTotal($guid, $groupId);
        $response = array(
            'data' => array(
                'count' => $count,
                'count_title' => lSetVars('edge_column_songs_title', array('count' => $count))
            )
        );

        return $response;
    }

    static function getInfo($id, $field = false, $useCache = true)
    {
        $post =  DB::one(self::$table, 'song_id = ' . to_sql($id), '', '*', '', DB_MAX_INDEX, $useCache);

        if($field !== false) {
            $post = isset($post[$field]) ? $post[$field] : '';
        }

        return $post;
    }

    static function isNoImage($image)
    {
        $image = str_replace('music_song_images', '', $image);
        if(strpos($image, 'song_') !== false) {
            return true;
        }
        return false;
    }

    static function getImageDefault($id, $size = 'b', $item = null, $cache = true)
    {
        global $g;

        $key = 'song_image_default_' . $size . '_' . intval($id);

        $image = null;
        if ($cache) {
            $image = Cache::get($key);
        }

        if ($image !== null) {
            return $image;
        }

        if ($item === null) {
            $item = self::getInfo($id);
        }

        $sql = 'SELECT * FROM ' . to_sql(self::$tableImage, 'Plain') .
               ' WHERE `song_id`=' . to_sql($id) . " ORDER BY `image_id` LIMIT 0, 1";
        $imageInfo = DB::row($sql);
        if ($imageInfo) {
            $image = $g['path']['url_files'] . "music_song_images/" . $imageInfo['image_id'] . "_{$size}.jpg";
        }

        /*$placeholder = false;
        $image = explode('|', $item['images']);
        if ($image) {
            $image = CBlogsTools::getImg($id, $image[0], 'bm');
        }*/

        if (!$image) {
            $image = $g['path']['url_files'] . "edge_song_{$size}.png";
        }
        /*$result = array('image' => $image, 'placeholder' => $placeholder);*/


        Cache::add($key, $image);

        return $image;
    }

    static function getFile($id)
    {
        global $g;

        return custom_getFileDirectUrl($g['path']['url_files'] .  'music/' . $id . '.mp3');
    }

    static function increasePlays($id = null)
    {
        if ($id === null) {
            $id = get_param_int('song_id');
        }
        if ($id) {
            $sql = 'UPDATE ' . to_sql(self::$table, 'Plain') . ' SET song_n_plays=song_n_plays + 1 WHERE `song_id`=' . to_sql($id);
        }
        DB::execute($sql);

        return true;
    }

    /* List */
    static public function getTagInfo($id)
	{
        if (!$id) {
            return false;
        }
        $tag = array();//DB::one('songs_tags', '`id` = ' . to_sql($id));

        return $tag;
    }

    static function getWhereList($table = '', $uid = 0, $groupId = 0, $showAllMySongs = false, $onlyPublic = false)
    {
        $guid = guid();
        $where = " 1=1 ";
        $whereGroup = " AND {$table}group_id = " . to_sql($groupId);

        $groupsList = Groups::getTypeContentList();
        if ($groupsList) {
            if (!$uid) {
                $showAllMySongs = true;
            }
            $whereGroup = " AND {$table}group_id != 0";
            if ($groupsList == 'group_page') {
                $whereGroup .= " AND {$table}group_page = 1";
            } else {
                $listGroups = Groups::getUserListGroupsSubscribers();
                $whereGroupPrivate = "({$table}group_private = 'Y' AND {$table}group_id IN (" . $listGroups . '))';

                $whereGroup .= " AND {$table}group_page = 0 AND ({$table}group_private = 'N' OR " . $whereGroupPrivate . ")";
            }
        }
        $where .= $whereGroup;

        if (!$guid || $uid != $guid) {
            $whereApprove = '';
            /*if (Common::isOptionActive('photo_approval')) {
                $whereApprove = "{$table}visible = 'Y' ";
            } elseif (Common::isOptionActive('nudity_filter_enabled')) {
                $whereApprove = "{$table}visible IN ('Y', 'N') ";
            }*/
            if ($whereApprove) {
                if ($showAllMySongs) {
                    $whereApprove = "({$whereApprove} OR {$table}user_id = " . to_sql($guid) . ")";
                }
                $where .= ' AND ' . $whereApprove;
            }
        }

        if ($uid) {
            $where .= " AND {$table}user_id = " . to_sql($uid);
        } elseif ($guid) {
            $isShowMySongs = Common::isOptionActive('show_your_song_browse_songs', 'edge_member_settings');
            $onlyFriends = false;
            if (self::$isGetDataWithFilter) {
                $onlyFriends = get_param_int('only_friends', false);
                if ($onlyFriends) {
                    $friends = User::friendsList($guid, $isShowMySongs);
                    if ($friends) {
                        $where .= " AND {$table}user_id IN ({$friends})";
                    }
                }
            }
            if (!$onlyFriends && !$isShowMySongs) {
                $where .= " AND {$table}user_id != " . to_sql($guid);
            }
        }
        if (!$uid) {
            $searchQuery = trim(get_param('search_query'));
            if ($searchQuery) {
                $searchQuery = urldecode($searchQuery);
                $where .= " AND {$table}song_title  LIKE '%" . to_sql($searchQuery, 'Plain') . "%'";
            }
        }

        return $where;
    }

    static function getOrderList($typeOrder = '')
    {
        $orderBy = 'S.`created_at` DESC, S.`song_id` DESC';
        if ($typeOrder == 'order_random') {
            $orderBy = 'RAND()';
        }else if ($typeOrder == 'order_most_commented') {
            $orderBy = 'S.`song_n_comments` DESC, S.`song_id` DESC';
        }else if ($typeOrder == 'order_most_plays') {
            $orderBy = 'S.`song_n_plays` DESC, S.`song_id` DESC';
        }

        return $orderBy;
    }

    static public function getTypeOrderList($notRandom = false, $lang = false)
	{
        global $p;

        if ($lang !== false) {
            $pLast = $p;
            $p = 'songs_list.php';
        }
        $list = array(
            'order_new'             => l('order_new', $lang),
            //'order_most_commented'  => l('order_most_commented', $lang),
            'order_most_plays'      => l('order_most_plays', $lang),
            'order_random'          => l('order_random', $lang)
        );
        if ($lang !== false) {
            $p = $pLast;
        }
        if ($notRandom) {
            unset($list['order_random']);
        }
        return $list;
    }

    static public function getTotal($uid = null, $groupId = 0)
	{
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            //$whereTags = self::getWhereTags('TR.');
        }
        if ($whereTags == 'no_tags') {
            return 0;
        }
        if ($whereTags) {
            $where = self::getWhereList('V.', $uid, $groupId);
            $sql = 'SELECT COUNT(*) FROM (
                        SELECT COUNT(*)
                          FROM `vids_tags_relations` AS TR
                          JOIN `vids_video` AS V ON V.id = TR.video_id
                         WHERE ' . $where
                                 . $whereTags
                     . ' GROUP BY V.id) AS VT';
            return DB::result($sql);
        } else {
            $where = self::getWhereList('', $uid, $groupId);
            return DB::count(self::$table, $where);
        }
    }

    static public function getList($typeOrder, $limit = '0, 4', $uid = null, $groupId = 0, $showAllMySongs = false, $onlyPublic = true) {

        global $p;

        $result = array();
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        $whereTags = '';
        if (self::$isGetDataWithFilter) {
            /*$whereTags = self::getWhereTags('TR.');
            if ($whereTags == 'no_tags') {
                return $result;
            }*/
        }

        $order = self::getOrderList($typeOrder);
        if ($order) {
            $order = ' ORDER BY ' . $order;
        }

        $groupBy = '';

        if ($whereTags) {
            $where = self::getWhereList('LR.', $uid, $groupId, $showAllMySongs, $onlyPublic);
            $groupBy = ' GROUP BY LR.song_id ';

            $sql = 'SELECT LR.*, U.name, U.name_seo, U.country, U.city, U.gender
                      FROM `' . self::$tableVideoTagsRel . '` AS TR
                      JOIN `' . self::$table . '` AS LR ON LR.song_id = TR.song_id
                      JOIN `user` AS U ON U.user_id = LR.user_id
                     WHERE ' . $where
                             . $whereTags
                             . $groupBy
                             . $order
                             . $limit;
        } else {
            $where = self::getWhereList('S.', $uid, $groupId, $showAllMySongs, $onlyPublic);

            $sql = 'SELECT S.*, U.name, U.name_seo, U.country, U.city, U.gender
                      FROM `' . self::$table . '` AS S
                      JOIN `user` AS U ON U.user_id = S.user_id
                     WHERE ' . $where
                             . $order
                             . $limit;
        }


        $songs = DB::rows($sql);

        return $songs;
    }
    /* List */
}