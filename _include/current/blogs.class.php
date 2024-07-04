<?php
class Blogs extends CHtmlBlock
{
    static $isGetDataWithFilter = false;
    static $responseData = '';
    static $indSanitize = 2;
    const STRLENSUBJECT = 255;
    const ALLOWTAGS = '<b><span><h3>';

    static $IMAGESTARTTAG = '<div class="blog_image_post">';
    static $IMAGEENDTAG = '</div>';
    static $IMAGESTARTTAG_temp = '';
    static $IMAGEENDTAG_temp = '';

    static $VIDEOSTARTTAG = '<div class="wall_video_post">';
    static $VIDEOENDTAG = '</div>';
    static $VIDEOSTARTTAG_temp = '';
    static $VIDEOENDTAG_temp = '';
    static $videoWidth = 424;

    static $maxLenTextShort = 150;


    static function resetTagsHtml()
    {
        self::$IMAGESTARTTAG_temp = self::$IMAGESTARTTAG;
        self::$IMAGEENDTAG_temp = self::$IMAGEENDTAG;

        self::$IMAGESTARTTAG = '';
        self::$IMAGEENDTAG = '';

        self::$VIDEOSTARTTAG_temp = self::$IMAGESTARTTAG;
        self::$VIDEOENDTAG_temp = self::$IMAGEENDTAG;

        self::$VIDEOSTARTTAG = '';
        self::$VIDEOENDTAG = '';
    }

    static function restoreTagsHtml()
    {
        self::$IMAGESTARTTAG = self::$IMAGESTARTTAG_temp;
        self::$IMAGEENDTAG = self::$IMAGEENDTAG_temp;

        self::$VIDEOSTARTTAG = self::$VIDEOSTARTTAG_temp;
        self::$VIDEOENDTAG = self::$VIDEOENDTAG_temp;
    }

    static function includePath()
    {
        return dirname(__FILE__) . '/../../';
    }

    static function setResponseData($name, $validate)
    {
        self::$responseData .= "<span class='" . $name . "'>" . strip_tags($validate) . '</span>';
    }

    static function stags($str)
    {
        return strip_tags_attributes($str, self::ALLOWTAGS);
    }

    static protected function _filterSubject($subject)
    {
        $subject = str_replace(array("\r\n", "\r", "\n"), " ", $subject);
        $subject = strip_tags($subject);
        $subject = neat_trim($subject, self::STRLENSUBJECT);
        $subject = hard_trim($subject, self::STRLENSUBJECT, '');

        return $subject;
    }

    static protected function _filterSearchIndex($v, $wrapper = true)
	{
        $v = strip_tags($v);
        $v = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $v);
        $v = preg_replace("/ {2,}/", " ", $v);
        if ($wrapper) {
            $v = ' ' . trim($v) . ' ';
        }

        return $v;
    }

    /* Name seo */
    static protected function prepareNameSeo($subject)
	{
        $search = array('[', '%', '#', '&', "'", '"', '/', '\\',
                        '<', '>', '*', '|', ']',
                        '.', ',', ':', ';', '?', );
        $subject = mb_strtolower(str_replace($search, "", $subject), 'utf-8');
        $subject = str_replace('-', " ", $subject);
        $subject = preg_replace("/ {2,}/", " ", $subject);
        $subject = trim($subject);

        $len = self::STRLENSUBJECT - 10;
        $subject = neat_trim($subject, $len);
        $subject = hard_trim($subject, $len, '');

        $subject = str_replace(" ", "_", $subject);

        return $subject;
    }

    static function getIndexNameSeo($name, $allNameSeo)
    {
        $i = 0;
        do {
            $i++;
            $nameSeo = "{$name}-{$i}";
        } while(in_array($nameSeo, $allNameSeo) && $i < 1000000);
        return $nameSeo;
    }

    static function getUniqueNameSeo($name, $uid)
    {
        $sql = 'SELECT `name_seo`
				  FROM `blogs_post`
                 WHERE `name_seo` LIKE \'' . to_sql($name, 'Plain') . '-%\' AND `user_id` = ' . to_sql($uid);
		$allNameSeo = DB::column($sql);

        $nameSeo = self::getIndexNameSeo($name, $allNameSeo);
        return $nameSeo;
    }

    static function getNameSeo($name, $uid, $id = 0, $getUnique = true)
    {
        $name = self::prepareNameSeo($name);
        $sql = 'SELECT `id` FROM `blogs_post`
                 WHERE `name_seo` = ' . to_sql($name) . ' AND `user_id` = ' . to_sql($uid);
        if ($id) {
            $sql .= ' AND `id` != ' . to_sql($id);
        }
        $isNameExists = DB::result($sql, 0, DB_MAX_INDEX);

        $nameSeo = '';
        if ($isNameExists) {
            if ($getUnique) {
                $nameSeo = self::getUniqueNameSeo($name, $uid);
            }
        } else {
            $nameSeo = $name;
        }
        return $nameSeo;
    }
    /* Name seo */

    static function addPost($id = null)
    {
        global $g;
        global $g_user;

        if ($id === null) {
            $id = get_param_int('blog_id');
        }

        $subject = trim(get_param('subject'));
        if (!$subject) {
            self::setResponseData('subject', l('required_field'));
            return self::$responseData;
        }

        $subjectStr = strval($subject);
        $subjectInt = intval($subjectStr);
        if ($subjectStr === strval($subjectInt)){
            self::setResponseData('subject', l('title_cannot_contain_numbers_only'));
            return self::$responseData;
        }

        $titleCensured = censured($subject);
        if ($titleCensured != $subject) {
            self::setResponseData('subject', l('title_contains_invalid_characters'));
            return self::$responseData;
        }

        $guid = guid();
        $isUpdate = $id;

        $text = trim(get_param('text'));
        if (!$text) {
            self::setResponseData('text', l('required_field'));
            return self::$responseData;
        }

        $text = strip_tags($text, '<a><p><br><b><h3>');

        $data = array(
            'user_id'        => $guid,
            'dt'             => date('Y-m-d H:i:s'),
            'count_comments' => '0',
            'subject'        => self::_filterSubject($subject, $text),
            'name_seo'       => self::getNameSeo($subject, $guid, $id),
            'text'           => $text,
            'word_count'     => self::wordCount($text),
            'search_index'   => self::_filterSearchIndex($subject . ' ' . $text),
            'comments_enabled' => get_param_int('comments_enabled')
        );

        if ($id) {
            unset($data['user_id']);
            unset($data['dt']);
            unset($data['count_comments']);
            DB::update('blogs_post', $data, '`id` = ' . to_sql($id));

            $data['user_id'] = $guid;
        } else {
            DB::insert('blogs_post', $data);
            $id = DB::insert_id();
            DB::update('user', array('blog_posts' => 'blog_posts+1'), 'user_id = ' . to_sql($guid), '', '', true);
        }

        $data['id'] = $id;

        $images = get_param_array('images');
        $imgs = self::uploadImages($id, $images, $text);

        if (!$imgs) {
            $pathVideo = 'https://i.ytimg.com/vi/';
            $objs = grabs($text, '{youtube:', '}');
            foreach ($objs as $obj) {
                $previews = array('maxresdefault.jpg', 'hqdefault.jpg', 'mqdefault.jpg');
                foreach ($previews as $preview) {
                    $urlPreview = $pathVideo . $obj . '/' . $preview;
                    $fileTemp = $g['path']['dir_files'] . 'temp/blogs_' . $id . '.txt';
                    @copyUrlToFile($urlPreview, $fileTemp);
                    if (custom_file_exists($fileTemp)) {
                        CBlogsTools::uploadImg($id, 1, $fileTemp);
                        DB::update('blogs_post', array('images' => '1'), 'id = ' . to_sql($id));
                        break(2);
                    }
                }
            }
        }

        self::updateTags($id);

        if (!$isUpdate) {
            Wall::add('blog_post', $id);
        }

        self::setResponseData('redirect', self::url($id, $data));
        return self::$responseData;
    }

    static public function uploadImages($blogId, $images, $text)
	{
        global $g;

        $post = DB::one('blogs_post', 'id = ' . to_sql($blogId));
        if (!$post) {
            return false;
        }

        $imagesList = explode('|', $post['images']);
        $i = intval($imagesList[count($imagesList) - 1]) + 1;

        $imagesListNotExists = array_flip($imagesList);

        if (is_array($images)) {
            foreach ($images as $k => $image) {
                if ($image['id']) {
                    $text = str_replace('{img:' . $k . '}', '{img:' . $image['id'] . '}', $text);
                    unset($imagesListNotExists[$image['id']]);
                    continue;
                }
                $isUpload = false;
                $fileTemp = $g['path']['dir_files'] . 'temp/blogs_' . $blogId . '.';
                if(preg_match("/^data:image\/(?<extension>(?:png|gif|jpg|jpeg));base64,(?<image>.+)$/", $image['src'], $matchings)){
                    $imageData = base64_decode($matchings['image']);
                    $extension = $matchings['extension'];
                    $fileTemp .= $extension;
                    if(file_put_contents($fileTemp, $imageData)){//Может file size проверить?
                        $isUpload = true;
                    }
                } else {
                    $fileTemp .= 'txt';
                    @copyUrlToFile($image['src'], $fileTemp);
                    if (custom_file_exists($fileTemp)) {
                        $isUpload = true;
                    }
                }

                $removeTagImg = true;
                if ($isUpload) {
                    if (CBlogsTools::uploadImg($blogId, $i, $fileTemp)) {
                        $imagesList[$i] = $i;
                        $text = str_replace('{img:' . $k . '}', '{img:' . $i . '}', $text);
                        $i++;
                        $removeTagImg = false;
                    }
                    unlink($fileTemp);
                }

                if ($removeTagImg) {
                    $text = str_replace('{img:' . $image['id'] . '}', '', $text);
                    //{img:} - удалить с текста
                }
            }
        }

        foreach ($imagesListNotExists as $id => $v) {
            $key = array_search($id, $imagesList);
            if ($key !== false) {
                unset($imagesList[$key]);
            }
            CBlogsTools::deleteImg($blogId, $id);
        }

        $imagesList = CBlogsTools::getOnlyExistsImg($blogId, $imagesList);

        $imgs = implode("|", $imagesList);

        $text = CBlogsTools::_filterImgTagsToDb($text, $blogId);

        DB::update('blogs_post', array('images' => $imgs, 'text' => $text), 'id = ' . to_sql($blogId));

        return $imagesList;
    }

    static function _filterImgTags($text, $row)
    {
        global $g;
        $grabs = grabs($text, '{img:', '}');
        foreach ($grabs as $gr) {
            $image = self::getImgFileBig($row['id'], $gr, '');
            if ($image) {
                $file = $g['path']['url_files'] . $image;
                $id = $row['id'];

                $date = Common::dateFormat($row['dt'], 'photo_date');
                $timeAgo = timeAgo($row['dt'], 'now', 'string', 60, 'second');

                $userinfo = User::getInfoBasic($row['user_id']);
                $userName = toAttr($userinfo['name']);
                $userPhoto = toAttr(User::getPhotoDefault($row['user_id'], 'r'));
                $userUrl = toAttr(User::url($row['user_id'], $userinfo));

                $dataAttr = 'data-id="' . $gr . '" ' .
                            'data-date="' . $date . '" data-time-ago="' . $timeAgo . '" ' .
                            'data-user-name="' . $userName . '" data-user-url="' . $userUrl . '" ' .
                            'data-user-photo="' . $userPhoto . '" ';
                $raplace =
                        self::$IMAGESTARTTAG .
                        '<img ' . $dataAttr . ' id="blog_post_img_' . $id . '_' . $gr . '" src="' . $file . '"/>'
                        . self::$IMAGEENDTAG;
                $text = str_replace('{img:' . $gr . '}', $raplace, $text);
            }
        }
        return $text;
    }

    static function _filterVideoTags($text)
    {
        $text = VideoHosts::filterFromDb($text, self::$VIDEOSTARTTAG, self::$VIDEOENDTAG, self::$videoWidth);

        return $text;
    }

    static function getParamId()
    {
        global $p;

        $key = 'blog_getParamId';
        $id = Cache::get($key);
        if($id !== null) {
            return $id;
        }

        if(is_array(get_param('id'))) {
            $id_p = get_param('id');
            $idParam = strval($id_p[0]);
        } else {
            $idParam = strval(get_param('id'));
        }

        $id = intval($idParam);

        if (!$id || $idParam !== strval($id) || $p != 'blogs_post.php'){
            $id = 0;
        }

        if (!$id) {
            $nameSeo = get_param('blog_seo');
            if ($nameSeo) {
                $uid = User::getParamUid(0);
                $id = self::getIdFromNameSeo($nameSeo, $uid);
            }
		}

        $id = intval($id);

        Cache::add($key, $id);

        return $id;
    }

    static function getIdFromNameSeo($nameSeo, $uid)
    {
        $key = 'blog_id_from_name_seo_' . $nameSeo;
        $id = Cache::get($key);
        if($id === null) {
            $sql = 'SELECT `id` FROM `blogs_post`
                     WHERE `name_seo` = ' . to_sql($nameSeo)
                   . ' AND `user_id` = ' . to_sql($uid);
            $id = DB::result($sql, 0, DB_MAX_INDEX);
            Cache::add($key, $id);
        }

        return $id;
    }

    static function getInfo($id, $field = false, $useCache = true)
    {
        $post =  DB::one('blogs_post', 'id = ' . to_sql($id), '', '*', '', DB_MAX_INDEX, $useCache);

        if($field !== false) {
            $post = isset($post[$field]) ? $post[$field] : '';
        }

        return $post;
    }


    static function url($id, $info = null, $params = null, $isCache = true)
    {
        if (Common::isOptionActive('seo_friendly_urls')) {

            $key = 'blog_seo_friendly_url_' . $id;
            $url = null;
            if ($isCache) {
                $url = Cache::get($key);
            }

            $paramsAddSymbol = '?';
            if ($url === null) {
                if ($info === null) {
                    $info = self::getInfo($id);
                }
                if ($info['name_seo']) {
                    $nameSeo = $info['name_seo'];
                } else {
                    $nameSeo = self::getNameSeo($info['subject'], $info['user_id']);
                    DB::update('blogs_post', array('name_seo' => $nameSeo), 'id = ' . to_sql($id));
                }

                $userSeo = User::url($info['user_id'], null, null, true, true);
                $url = $userSeo . '/blogs/' . $nameSeo;

                Cache::add($key, $url);
            }
        } else {
            $paramsAddSymbol = '&';
            $url = 'blogs_post.php?id=' . $id;
        }

        $url .= $params ? $paramsAddSymbol . http_build_query($params) : '';

        return $url;
    }

    static public function getTotalBlogs($uid = null)
	{
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
            $where = self::getWhereList('B.', $uid);
            if ($where) {
                $whereTags = ' AND ' . $whereTags;
            }
            $sql = 'SELECT COUNT(*) FROM (
                            SELECT COUNT(*)
                              FROM `blogs_post_tags_relations` AS TR
                              JOIN `blogs_post` AS B ON B.id = TR.blog_id
                             WHERE ' . $where
                                     . $whereTags
                         . ' GROUP BY B.id) AS BLT';
            return DB::result($sql);
        } else {
            $where = self::getWhereList('', $uid);
            return DB::count('blogs_post', $where);
        }
    }

    static function getWhereList($table = '', $uid = 0)
    {
        $guid = guid();

        $where = '';
        $delimiter = '';
        if ($uid) {
            $where .= "{$table}user_id = " . to_sql($uid);
        } elseif ($guid) {
            $isShowMyBlogs = Common::isOptionActive('show_your_blog_browse_blogs', 'edge_member_settings');
            $onlyFriends = false;
            if (self::$isGetDataWithFilter) {
                $onlyFriends = get_param_int('only_friends', false);
                if ($onlyFriends) {
                    $friends = User::friendsList($guid, $isShowMyBlogs);
                    if ($friends) {
                        $where .= " {$table}user_id IN ({$friends})";
                        $delimiter = ' AND ';
                    }
                }
            }
            if (!$onlyFriends && !$isShowMyBlogs) {
                $where .= $delimiter . " {$table}user_id != " . to_sql($guid);
                $delimiter = ' AND ';
            }
        }
        if (!$uid) {
            $searchQuery = trim(get_param('search_query'));
            if ($searchQuery) {
                $searchQuery = urldecode($searchQuery);
                $where .= $delimiter . " {$table}subject  LIKE '%" . to_sql($searchQuery, 'Plain') . "%'";
            }
        }

        return $where;
    }

    static function getOrderList($typeOrder = '')
    {
        $orderBy = 'B.dt DESC';
        if ($typeOrder == 'order_most_commented') {
            $orderBy = 'B.count_comments DESC, B.dt DESC';
        } elseif ($typeOrder == 'order_most_viewed') {
            $orderBy = 'B.count_views DESC, B.dt DESC';
        } elseif ($typeOrder == 'order_random') {
            $orderBy = 'RAND()';
        }

        return $orderBy;
    }

    static public function getTags($id)
	{
        $sql = 'SELECT TR.tag_id, T.tag
                  FROM `blogs_post_tags_relations` as TR
                  LEFT JOIN `blogs_post_tags` as T ON TR.tag_id = T.id
                 WHERE TR.blog_id = ' . to_sql($id) . ' ORDER BY T.id';
        $tagsBlog = DB::all($sql);
        $tags = array();
        if ($tagsBlog) {
            foreach ($tagsBlog as $key => $tag) {
                $tags[$tag['tag_id']] = $tag['tag'];
            }
        }
        return $tags;
    }

    static public function getTagsView($blogId, $title = true) {
        $tags = self::getTags($blogId);
        $tagsTitle = '';
        $tagsHtml = '';
        if ($tags) {
            $pageList = Common::pageUrl('blogs_list');
            foreach ($tags as $id => $tag) {
                $tagsHtml .= ', <a href="' . $pageList . '?tag=' . $id . '">' . $tag . '</a>';
                $tagsTitle .= ', ' . $tag;
            }
            $tagsHtml = substr($tagsHtml, 1);
            $tagsTitle = substr($tagsTitle, 1);
        }
        if ($title) {
            return trim($tagsTitle);
        } else {
            return trim($tagsHtml);
        }
    }

    static public function getTagInfo($id)
	{
        if (!$id) {
            return false;
        }
        $tag = DB::one('blogs_post_tags', '`id` = ' . to_sql($id));

        return $tag;
    }

    static public function deleteTags($blogId)
	{
        Common::deleteTags($blogId, 'blogs');
    }

    static public function updateTags($blogId = null){
        $guid = guid();
        if ($blogId === null) {
            $blogId = get_param_int('blog_id');
        }
        if (!$guid || !$blogId) {
            return false;
        }

        $table = 'blogs_post';
        $fieldId = 'id';

        $tableTags = 'blogs_post_tags';
        $tableTagsRelations = 'blogs_post_tags_relations';
        $fieldRelationsId = 'blog_id';

        $sql = "SELECT `{$fieldId}`
                  FROM `{$table}`
                 WHERE `{$fieldId}` = " . to_sql($blogId)
               . ' AND `user_id` = ' . to_sql($guid);
        if (!DB::result($sql)) {
            return false;
        }

        $result = array();
        $tags = trim(get_param('tags'));
        $result['tags_title'] = $tags;
        $tags = explode(',', $tags);
        $tags = array_map('trim', $tags);
        $result['tags'] = $tags;
        $tagsSql = array_map('to_sql', $tags);

        $tagsTemp = array();
        $tagsDelete = array();

        $tagsExists = DB::select($tableTags, '`tag` IN (' . implode(',', $tagsSql) . ')');
        $tagsExistsCount = array();
        foreach ($tagsExists as $key => $item) {
            $tagsTemp[$item['id']] = $item['tag'];
            $tagsExistsCount[$item['id']] = $item['counter'];
        }
        $tagsExists = $tagsTemp;

        $sql = "SELECT TR.tag_id, T.counter
                  FROM `{$tableTagsRelations}` as TR
                  LEFT JOIN `{$tableTags}` as T ON TR.tag_id = T.id
                 WHERE TR.{$fieldRelationsId} = " . to_sql($blogId);
        $tagsBlog = DB::all($sql);
        if ($tagsBlog) {
            $tagsTemp = array();
            foreach ($tagsBlog as $key => $item) {
                $tagsTemp[$item['tag_id']] = $item['counter'];
            }
            $tagsBlog = $tagsTemp;

            foreach ($tagsBlog as $id => $count) {
                if (!isset($tagsExists[$id])) {
                    $tagsDelete[$id] = $count;
                }
            }
        }

        $tagsUpdate = array();
        foreach ($tags as $key => $tag) {
            if (!$tag) {
                unset($tags[$key]);
                continue;
            }
            $id = array_search($tag, $tagsExists);
            if ($id) {
                unset($tags[$key]);
                if (!isset($tagsBlog[$id])) {
                    $tagsUpdate[$id] = 1;
                }
            }
        }

        if ($tags) {
            foreach ($tags as $key => $value) {
                DB::insert($tableTags, array('tag' => $value, 'counter' => 1));
                $id = DB::insert_id();
                DB::insert($tableTagsRelations, array("{$fieldRelationsId}" => $blogId, 'tag_id' => $id));
            }
        }

        if ($tagsDelete) {
            foreach ($tagsDelete as $id => $count) {
                DB::delete($tableTagsRelations, "`{$fieldRelationsId}` = " . to_sql($blogId) . ' AND `tag_id` = ' . to_sql($id));
                if (intval($count) > 1) {
                    DB::execute("UPDATE {$tableTags} SET counter = counter - 1 WHERE id=" . to_sql($id));
                } else {
                    DB::delete($tableTags, '`id` = ' . to_sql($id));
                }
            }
        }

        if ($tagsUpdate) {
            foreach ($tagsUpdate as $id => $count) {
                DB::insert($tableTagsRelations, array("{$fieldRelationsId}" => $blogId, 'tag_id' => $id));
                DB::execute("UPDATE {$tableTags} SET counter = counter + 1 WHERE id=" . to_sql($id));
            }
        }

        $tags = self::getTags($blogId);

        $tagsHtml = '';
        foreach ($tags as $id => $tag) {
            $tagsHtml .= ' <a href="' . Common::pageUrl('pages_list') . '?tag=' . $id . '">' . $tag . '</a>';
        }
        $result['tags_html'] = $tagsHtml;

        return $result;
    }

    static public function getWhereTags($table = '', $tags = null)
	{
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
                $where .= '`tag` LIKE "%' . to_sql($tag, 'Plain') . '%"';
            }
            $i++;
        }
        if ($where) {
            $sql = "SELECT `id` FROM `blogs_post_tags` WHERE ({$where})";
            $tagsId = DB::rows($sql);
            $tags = array();
            if ($tagsId) {
                foreach ($tagsId as $k => $tag) {
                    $tags[] = $tag['id'];
                }
                $whereSql = implode(',', $tags);
                $whereSql = " {$table}tag_id IN({$whereSql})";
            }
        }

        return $whereSql;
    }


    static function getList($typeOrder = '', $limit = '', $uid = null, $setOrder = false)
    {
        $result = array();

        $whereTags = '';

        if (self::$isGetDataWithFilter) {
            $whereTags = self::getWhereTags('TR.');
            if ($whereTags == 'no_tags') {
                return $result;
            }
        }

        include_once(self::includePath() . '_include/current/blogs/tools.php');

        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        $guid = guid();

        $optionTmplName = Common::getTmplName();

        $where = self::getWhereList('B.', $uid);

        if (!$guid) {
            $where .= ($where ? " AND " : " ") . " U.set_who_view_profile != 'members'";
        }

        if ($typeOrder == '') {
            $typeOrder = Common::getOption('list_blog_posts_type_order', "{$optionTmplName}_general_settings");
        }
        if ($setOrder) {
            $orderBy = ' ORDER BY ' . $typeOrder;
        } else {
            $orderBy = self::getOrderList($typeOrder);
            if ($orderBy) {
                $orderBy = ' ORDER BY ' . $orderBy;
            }
        }

        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        if ($where) {
            $where = ' WHERE ' . $where;
        }

        if ($whereTags) {
            $whereTags = ($where ? ' AND ' : ' WHERE ') . $whereTags;
        }

        if ($whereTags) {
            $sql = 'SELECT B.*, U.name, U.name_seo, U.country, U.city
                      FROM `blogs_post_tags_relations` AS TR
                      JOIN `blogs_post` AS B  ON B.id = TR.blog_id
                      JOIN `user` AS U ON U.user_id = B.user_id '
                             . $where
                             . $whereTags
                             . ' GROUP BY B.id '
                             . $orderBy
                             . $limit;
        } else {
            $sql = 'SELECT B.*, U.name, U.name_seo, U.country, U.city
                      FROM `blogs_post` AS B
                      JOIN `user` AS U ON U.user_id = B.user_id '
                             . $where
                             . $orderBy
                             . $limit;
        }
        $posts = DB::rows($sql);
        $rows = array();
        foreach ($posts as $k => $post) {
            $post['user_info'] = array('name' => $post['name'], 'name_seo' => $post['name_seo']);
            $post['text_short'] = self::getTextShort($post['text']);
            $rows[] = $post;
        }

        return $rows;
    }

    static public function getTypeOrderList($notRandom = false, $lang = false)
	{
        global $p;

        if ($lang !== false) {
            $pLast = $p;
            $p = 'blogs_list.php';
        }
        $list = array(
            'order_new_blogs'        => l('order_new_blogs', $lang),
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

    static function getTempFileUploadImage()
    {
        $ind = get_param_int('ind');
        if ($ind) {
            $ind = '_' . $ind;
        } else {
            $ind = '';
        }
        return 'temp/tmp_blog_' . guid() . $ind . '.jpg';
    }

    static function prepareTagsText($text, $allowTags = '<a><h3><p>')
	{
        $text = str_replace(array("\r\n", "\r", "\n"), " ", $text);
        //$text = nl2br($text);

        $text = strip_tags($text, $allowTags);
        $s1 = array('<p>', '</p>', '<h3>', '</h3>');
        $s2   = array(" ", " ", " ", " ");
        $text = str_replace($s1, $s2, $text);
        $text = preg_replace("/ {2,}/", " ", $text);
        $text = trim($text);
        //var_dump_pre($text, true);

        return $text;
    }

    static function wordCount($text)
	{
        $text = self::prepareTagsText($text, '<h3><p>');
        //var_dump_pre($text, true);
        $count = count(explode(" ", $text));//str_word_count()

        return $count;
    }

    static function getWordCount($blogId)
	{
        $blogInfo = self::getInfo($blogId);
        if ($blogInfo['word_count']) {
            return $blogInfo['word_count'];
        } else {
            $count = self::wordCount($blogInfo['text']);
            DB::update('blogs_post', array('word_count' => $count), '`id` = ' . to_sql($blogId));

            return $count;
        }
    }

    static function getTextShort($text, $maxLenTextShort = null)
	{
        if ($maxLenTextShort === null) {
            $maxLenTextShort = self::$maxLenTextShort;
        }
        $text = self::prepareTagsText($text);

        self::$indSanitize = 3;
        $text = self::_sanitizeLink($text);
        self::$indSanitize = 2;
        $text = self::_filterRemoveUnusedTags($text);
        $text = neat_trim($text, $maxLenTextShort);
        //$text = self::_filterLinksTagsToHtml(neat_trim($text, $maxLenTextShort));

        return $text;
    }

    static function callSanitize($matches){
        return isset($matches[self::$indSanitize]) ? $matches[self::$indSanitize] : '';
    }

    static function _sanitizeLink($text)
	{
        $text = preg_replace_callback('%(<a[^>]href=["|\']{1}([^"|\']+)["|\']{1}>([^<]+)</a>)%', 'Blogs::callSanitize', $text);

        return $text;
    }

    static protected function _filterRemoveUnusedTags($text)
    {
        $grabs = Common::grabsTags($text);
        foreach ($grabs as $gr) {
            $text = str_replace($gr, "", $text);
        }
        return $text;
    }

    /* Comments */
    static function parseComments(&$html, $id)
    {
        global $g_user;

        $optionTemplateName = Common::getTmplName();
        $guid = $g_user['user_id'];
        $cmd = get_param('cmd');
        $isGetLoadComments = $cmd == 'get_blog_post_comment';

        $table = 'blogs_comment';
        $fieldId = 'post_id';
        $row = DB::one('blogs_post', '`id` = ' . to_sql($id));
        $type = 'blogs_post';

        $comments = array();

        if (!$row || !$row['comments_enabled']) {
            return;
        }

        $lastId = get_param_int('last_id');
        $limit = '';
        $where = '';
        $whereSql = '';
        $whereEventSql = '';

        $numberComments = CProfilePhoto::getNumberShowComments(false, $type);
        $loadMore = get_param_int('load_more');
        if ($loadMore) {
            $numberComments = CProfilePhoto::getNumberShowCommentsLoadMore(false, $type);
            $where .= ' AND `id` < ' . to_sql($lastId);
        } else {
            $where .= ' AND `id` > ' . to_sql($lastId);
        }
        $limitParam = get_param_int('limit');
        if ($limitParam) {
            $numberComments = $limitParam;
        }
        $limit = ' LIMIT ' . $numberComments;

        $var = "{$fieldId}_comments";
        if ($html->varExists($var)) {
            $html->setvar($var, $id);
        }

        $showEventReplyCommentId = 0;
        $showCommentParentId = 0;

        $where .= ' AND `parent_id` = 0';
        $whereEventSql .= ' AND `parent_id` = 0';

        $sql = "SELECT *
                  FROM `{$table}`
                 WHERE `{$fieldId}` = " . to_sql($id, 'Number') . $where .
               " ORDER BY id DESC " . $limit;
        if ($row['count_comments']) {
            $comments = DB::all($sql);
        }

        if (!$loadMore) {
            $countComments = $row['count_comments'];
            $html->setvar('blogs_post_comments_count', $countComments);

            $lVar = 'wall_comments_count';
            if ($countComments == 1) {
                $lVar = 'wall_comments_one_count';
            }
            $html->setvar('blogs_post_comments_count_title', lSetVars($lVar, array('comments_count' => $countComments)));
            $html->subcond(!$countComments, 'blogs_post_comments_count_title_hide');
        }

        /* Show comment from event */
        $showCommentId = get_param_int('show_comment_id');
        if ($comments && $showCommentId && $isGetLoadComments) {
                $sql = "SELECT `parent_id`
                          FROM `{$table}`
                         WHERE `id` = " . to_sql($showCommentId) .
                       " LIMIT 1";
                $showCommentParentId = DB::result($sql);
                if ($showCommentParentId) {
                    $showEventReplyCommentId = $showCommentId;
                } else {
                    $showCommentParentId = $showCommentId;
                }
                $isExistsShowComment = false;
                foreach ($comments as $key => $comment) {
                            if ($comment['id'] == $showCommentParentId) {
                                $isExistsShowComment = true;
                                break;
                            }
                }

                if (!$isExistsShowComment) {
                            $sql = "SELECT *
                                      FROM `{$table}`
                                     WHERE `{$fieldId}` = " . to_sql($id, 'Number') . $whereEventSql .
                                     ' AND `id` >= ' . to_sql($showCommentParentId) .
                                   " ORDER BY id DESC";
                            $comments = DB::all($sql);
                            $numberComments = count($comments);
                }
            }
            /* Show comment from event */

            if (!$loadMore) {
                krsort($comments);
            }

            $count = count($comments);
            $countRows = $count;

            $commentsLikes = CProfilePhoto::getAllLikesCommentsFromUser($type);
            if ($count > 0) {
                $i = 0;
                Wall::$isParseCommentsBlog = true;
                foreach ($comments as $key => $comment) {

                    $comment['item_group_id'] = 0;
                    if ($i == $numberComments) {
                        break;
                    }

                    $comment['comment'] = $comment['text'];
                    $comment['date'] = $comment['dt'];

                    $commentInfo = CProfilePhoto::prepareDataComment($comment, $type);
                    if (!$commentInfo){
                        continue;
                    }

                    $commentInfo['photo_user_id'] = $row['user_id'];

                    $parseShowEventReplyCommentId = 0;
                    if ($showEventReplyCommentId && $showCommentParentId == $comment['id']) {
                        $parseShowEventReplyCommentId = $showEventReplyCommentId;
                    }

                    CProfilePhoto::parseRepliesComments($html, $comment['id'], $comment['replies'], false, $commentsLikes, $type, false, $parseShowEventReplyCommentId);

                    if (isset($commentsLikes[$comment['id']])) {
                        $commentInfo['like'] = 1;
                    }

                    CProfilePhoto::parseComment($html, $commentInfo, 'comment', $type);

                    $i++;
                }
                Wall::$isParseCommentsBlog = false;
                if ($loadMore === 0) {
                    $whereSql .= ' AND `parent_id` = 0';
                    $count = DB::count($table, "`{$fieldId}` = " . to_sql($id) . $whereSql);
                }
            }

			ImAudioMessage::parseControlAudioCommentPost($html);

            //$html->parse('comment_block_end');
            if ($count > $numberComments) {
                $blockLoadMoreNumber = 'blog_post_load_more_comments_number';
                if ($html->blockExists($blockLoadMoreNumber)) {
                    $html->setvar("{$blockLoadMoreNumber}_title", l('view_previous_comments'));
                    $vars = array(
                        'view_number' => $numberComments,
                        'all_number' => $count
                    );
                    $html->setvar($blockLoadMoreNumber, lSetVars('view_previous_comments_number', $vars));
                    $html->parse($blockLoadMoreNumber, false);
                }
                $html->parse('blog_post_load_more_comments', false);
            } else {
                //$html->parse('items_comment_no_border');
            }

        if (!$loadMore) {
            $numberCommentsFrmShow = Common::getOption('number_comments_show_bottom_frm', "{$optionTemplateName}_blogs_settings");
            if (!$numberCommentsFrmShow) {
                $numberCommentsFrmShow = 2;
            }
            $html->setvar('number_comments_frm_show', $numberCommentsFrmShow);
            if ($row['count_comments']) {
                $blockFeedBottomFrm = 'blogs_feed_comment_bottom_frm_show';
                $commentsCount = count($comments);

                if ($commentsCount > $numberCommentsFrmShow) {
                    $html->parse($blockFeedBottomFrm, false);
                }
            } else {

            }
            $html->parse('blogs_feed_comment_top_frm_show', false);
        }

        $block = 'blog_post_comments_enabled';
        $html->parse($block, false);


        return $comments;
    }

    static function parseLikes(&$html, $id, $likes, $index = 2, $row = null)
    {

        $guid = guid();
        $likeClass = 'wall_like_hide';

        $iLikeIt = false;
        $listLikesUser = array();
        $listLikesUserInfo = array();
        $listLikesUserPhoto = array();
        if ($likes) {

            $likeClass = '';
            if (Common::isMobile(false, true)) {
                $limit = 3;
                if($likes > $limit) {
                    $limit = 2;
                } else {
                    $limit = $likes;
                }
            } else {
                $limit = 4;
                if($likes > $limit) {
                    $limit = 3;
                } else {
                    $limit = $likes;
                }
            }

            $uids = User::getFriendsList($guid);
            $sql = 'SELECT u.user_id, u.name,
                           IF(u.user_id = ' . to_sql($guid) . ', 2,
                           IF(u.user_id IN (' . to_sql($uids, 'Plain') . '), 1, 0)) AS order_param, u.name_seo, u.gender
                      FROM `blogs_post_likes` AS BL
                      LEFT JOIN user AS u ON u.user_id = BL.user_id
                     WHERE BL.blog_id = ' . to_sql($id) . '
                     ORDER BY order_param DESC, id DESC LIMIT ' . $limit;

            DB::query($sql, $index);

            $wallLikeDelimiter = '';
            $counter = 0;
            $uidsExclude = array();
            $userLike = false;

            while ($user = DB::fetch_row($index)) {
                $userLike = $user;
                $uidsExclude[] = $user['user_id'];
                $counter++;
                if($user['name'] == guser('name')) {
                    if($likes > 1) {
                        $html->parse('wall_like_you', false);
                    }
                    $iLikeIt = true;
                    $wallLikeDelimiter = ', ';
                } else {
                    if($counter == $likes && $likes > 1) {
                        $wallLikeDelimiter = ' ' . l('and') . ' ';
                    }
                    if($counter == 1) {
                        $wallLikeDelimiter = '';
                    }

                    $html->setvar('wall_like_delimiter', $wallLikeDelimiter);

                    $userName = $user['name'];
                    $userUrl = User::url($user['user_id']);

                    $html->setvar('wall_like_user_url', $userUrl);
                    $html->setvar('wall_like_name', $userName);

                    if($likes > 1) {
                        $html->parse('wall_like_user', true);
                    }
                    if($likes > 2) {
                        $wallLikeDelimiter = ', ';
                    }
                }
            }

            $listLikesUser = $uidsExclude;
            if($likes == 1) {
                if($iLikeIt) {
                    $wallLikeOne = l('wall_only_you_like');
                } else {
                    $nameUrl = Common::getLinkHtml(User::url($userLike['user_id'])) . $userLike['name'] . '</a>';
                    $userLikeGender = User::getInfoBasic($userLike['user_id'], 'gender');
                    if($userLikeGender == '') {
                        $userLikeGender = 'm';
                    }
                    $wallLikeOne = lSetVars('wall_only_one_person_likes' . '_' . mb_strtolower($userLikeGender, 'UTF-8'), array('name' => $nameUrl, 'url_profile' => User::url($userLike['user_id'])));
                }

                $html->setvar('wall_like_one', $wallLikeOne);
                $html->parse('wall_like_one', false);
            } elseif($likes > $limit) {
                $wallLikesCount = $likes - $limit;
                $urlStart = Common::getOption('url_main', 'path') . Common::pageUrl('blogs_post_liked', null, $id);
                $urlStart = '<a href="' . $urlStart . '">';


                $vars = array(
                    'count' => $wallLikesCount,
                    'url_start' => $urlStart,
                    'url_end' => '</a>',
                );
                $html->setvar('wall_and_more_people_like_this', lSetVars('wall_and_more_people_like_this', $vars));

                $html->parse('wall_like_more', true);
            } elseif ($likes > 1) {
                $html->parse('wall_like_names', true);
            }
        }

        $clLikeHidden = '';
        if($iLikeIt) {
            $clLikeHidden = 'wall_like_hidden';
            $html->parse('link_unlike');
        } else {
            $html->parse('icon_like');
        }
        $html->setvar('wall_like_hidden', $clLikeHidden);

        if ($listLikesUser) {
            $html->setvar('blog_post_like_user_list', implode(',', $listLikesUser));
        } else {
            $html->setvar('blog_post_like_user_list', '');
        }

        $html->parse('blog_post_module_like', false);

        $html->setvar('blog_post_like_class', $likeClass);
        $html->parse('blog_post_like', false);
    }

    static function updateCountComment($id)
    {
        $countCommentsAll = '(SELECT COUNT(*)
                                FROM `blogs_comment`
                               WHERE `post_id` = ' . to_sql($id) . ')';
        $countComments = 'count_comments';
        $countCommentsReplies = 'count_comments_replies';

        $countComments = '(SELECT COUNT(*)
                             FROM `blogs_comment`
                            WHERE `parent_id` = 0
                              AND `post_id` = ' . to_sql($id) . ')';
        $countCommentsReplies = '(SELECT COUNT(*)
                                    FROM `blogs_comment`
                                   WHERE `parent_id` != 0
                                     AND `post_id` = ' . to_sql($id) . ')';

        $sql = 'UPDATE `blogs_post` SET
                `count_comments_all` = ' . $countCommentsAll . ',
                `count_comments` = ' . $countComments . ',
                `count_comments_replies` = ' . $countCommentsReplies . '
                 WHERE `id` = ' . to_sql($id);

        DB::execute($sql);
    }

    static function updateCountCommentReplies($cid)
    {
        $sql = 'SELECT COUNT(*)
                  FROM `blogs_comment`
                 WHERE `parent_id` = ' . to_sql($cid);
        $countCommentsReplies = DB::result($sql);
        $sql = 'UPDATE `blogs_comment` SET
                `replies` = ' . $countCommentsReplies . '
                WHERE `id` = ' . to_sql($cid);
        DB::execute($sql);
    }

    static function getCountCommentReplies($cid)
    {
        $sql = 'SELECT COUNT(*)
                  FROM `blogs_comment`
                 WHERE `parent_id` = ' . to_sql($cid);
        return DB::result($sql);
    }

    static function getCountComment($id)
    {
        $sql = 'SELECT `count_comments`
                  FROM `blogs_post`
                 WHERE `id` = ' . to_sql($id);
        return DB::result($sql, 0, DB_MAX_INDEX);
    }

    static function addComment($isNotifComments = true)
    {
        global $g_user;

        $msg = trim(get_param('comment'));
        $blogId = get_param_int('blog_id');

        $guid = guid();
        $commentInfo = array();

        $msg = censured($msg);

        $blogInfo = DB::one('blogs_post', '`id` = ' . to_sql($blogId));
        $blogUserId = 0;
        if ($blogInfo) {
            $blogUserId = $blogInfo['user_id'];
        }

        if (!$blogUserId) {
            return $commentInfo;
        }

		$audioMessageId = get_param_int('audio_message_id');
		$imageUpload = get_param_int('image_upload');
        if ($blogId && ($msg != '' || $audioMessageId || $imageUpload)) {
            $send = get_param('send', time());
            $date = date('Y-m-d H:i:s');
            $parentId = get_param_int('reply_id');

            $autoMail = 'new_comment_photo';

			$msg = Wall::addCommentPrepare($msg, false);

            $commentUserId = 0;
            if ($parentId) {
                $sql = "SELECT `user_id` FROM `blogs_comment` WHERE `id` = " . to_sql($parentId);
                $commentUserId = DB::result($sql);
                $isNew = intval($commentUserId != $guid);
            } else {
                $isNew = intval($blogUserId != $guid);
            }

            $sql = "INSERT INTO `blogs_comment` (`id`, `user_id`, `post_id`, `post_user_id`, `dt`, `text`, `parent_id`, `parent_user_id`, `is_new`, `audio_message_id`, `send`)
                        VALUES (NULL, " . to_sql($guid, 'Number') . ', ' .
                        to_sql($blogId, 'Number') . ","
                      . to_sql($blogUserId, 'Number') . ",'"
                      . $date . "',"
                      . to_sql($msg) . ","
                      . to_sql($parentId, 'Number') . ","
                      . to_sql($commentUserId, 'Number') . ","
                      . to_sql($isNew, 'Number') . ","
					  . to_sql($audioMessageId) . ","
                      . to_sql($send) . ")";
            DB::execute($sql);
            $cid = DB::insert_id();

			ImAudioMessage::updateImMsgId($audioMessageId, $cid, 'blog_comment_id');

            if ($parentId) {
                self::updateCountCommentReplies($parentId);
            } else {
                $data = array('blog_comments' => 'blog_comments+1');
                DB::update('user', $data, 'user_id = ' . to_sql($blogUserId), '', '', true);
            }

            self::updateCountComment($blogId);


            Wall::addItemForUser($blogId, 'blog', $guid);
            Wall::setSiteSection('blog');
            Wall::setSiteSectionItemId($blogId);
            Wall::add('blog_comment', $cid, false, '', false, $guid);


            /*if ($blogUserId != $guid) {
                User::updatePopularity($photoUserId);
            }*/

			$commentInfo['post_id'] = $blogId;
            $commentInfo['count_comments'] = 0;
            $commentInfo['count_comments_replies'] = 0;
            if ($parentId) {
                $sql = "SELECT `replies` FROM `blogs_comment` WHERE `id` = " . to_sql($parentId);
                $commentInfo['count_comments_replies'] = DB::result($sql);
            } else {
                $blogInfo = DB::one('blogs_post', '`id` = ' . to_sql($blogId));
                if ($blogInfo) {
                    $commentInfo['count_comments'] = $blogInfo['count_comments'];
                }
            }

            $commentInfo['id'] = $cid;
            $commentInfo['parent_id'] = $parentId;
            $commentInfo['text'] = $msg;
            $commentInfo['dt'] = $date;
            $commentInfo['user_id'] = $guid;
            $commentInfo['send'] = $send;

            $user = User::getInfoBasic($guid, false, 2);
            $commentInfo['user_name'] = $user['name'];
            $commentInfo['user_photo'] = User::getPhotoDefault($guid, 'r', false, $user['gender']);
			$commentInfo['user_photo_id'] = User::getPhotoDefault($guid, 'r', true);
			$commentInfo['audio_message_id'] = $audioMessageId;


            if (false && $blogUserId != $guid && $isNotifComments && Common::isEnabledAutoMail($autoMail)) {
                $userInfo = User::getInfoBasic($blogUserId);
                $isNotif = User::isOptionSettings('set_notif_new_comments', $userInfo);
                if ($isNotif) {
                    $vars = array('title' => Common::getOption('title', 'main'),
                                  'name' => $userInfo['name'],
                                  'name_sender'  => $g_user['name'],
                                  'id' => $photoId,
                                  'url_site' => Common::urlSite(),
                                  'cid' => $cid);
                    Common::sendAutomail($userInfo['lang'], $userInfo['mail'], $autoMail, $vars);
                }
            }
        }

        return $commentInfo;
    }

    static function deletePost($blogId = null, $isAdmin = false)
	{
        if ($blogId === null) {
            $blogId = get_param_int('blog_id');
        }

        if (!$blogId) {
            return false;
        }

        $post = DB::one('blogs_post', 'id = ' . to_sql($blogId));
        $guid = guid();

        if (!$post) {
            return false;
        }

        if ($isAdmin) {
            $guid = $post['user_id'];
        }

        if ($post['user_id'] != $guid) {
            return false;
        }

        CBlogsTools::deleteImgs($blogId, explode("|", $post['images']));

        DB::delete('blogs_post', 'id = ' . to_sql($blogId));
        DB::delete('blogs_post_likes', 'blog_id = ' . to_sql($blogId));

        $sql = 'SELECT `id` FROM `blogs_comment`
                 WHERE `post_id` = ' . to_sql($blogId, 'Number');
        $comments = DB::rows($sql);

        if ($comments) {
            foreach($comments as $comment) {
                self::deleteComment($comment['id'], $isAdmin);
            }
        }

        DB::update('user', array('blog_posts' => 'blog_posts-1'), 'user_id = ' . to_sql($guid), '', '', true);
        self::deleteTags($blogId);


        Wall::removeBySiteSection('blog', $blogId);
        Wall::removeBySiteSection('blog_post', $blogId);

        return true;
    }


    static function deleteComment($commentId = 0, $isAdmin = false)
    {
        global $g_user;

        $responseData = false;
        $guid = $g_user['user_id'];
        if ($isAdmin) {
            $guid = 1;
        }

        if (!$guid) {
            return false;
        }

        $cid = get_param('cid', $commentId);

        if (!$cid) {
            return false;
        }

        $sql = 'SELECT *
                  FROM `blogs_comment`
                 WHERE id = ' . to_sql($cid, 'Number');
        $comment = DB::row($sql);
        if (!$comment) {
            return false;
        }

        if ($isAdmin) {
            $guid = $comment['user_id'];
        }

        if ($guid == $comment['user_id'] || $guid == $comment['post_user_id']) {
            $responseData = array();

            DB::update('user', array('blog_comments' => 'blog_comments-1'), 'user_id = ' . to_sql($comment['post_user_id']) . ' AND blog_comments > 0', '', '', true);

            Wall::remove('blog_comment', $comment['id'], $comment['user_id']);
			Wall::removeImgComment($comment['text']);

            DB::delete('blogs_comment', '`id` = ' . to_sql($cid));
            DB::delete('blogs_comments_likes', '`cid` = ' . to_sql($cid));

			ImAudioMessage::delete($comment['id'], $comment['user_id'], 'blog_comment_id');

            if ($comment['parent_id']) {
                self::updateCountCommentReplies($comment['parent_id']);
            } else {
                $parentComments = DB::select('blogs_comment', '`parent_id` = ' . to_sql($cid));
                foreach ($parentComments as $key => $commentItem) {
                    Wall::remove('blog_comment', $commentItem['id'], $commentItem['user_id']);
					Wall::removeImgComment($commentItem['text']);

                    DB::delete('blogs_comments_likes', '`cid` = ' . to_sql($commentItem['id']));
                }
                DB::delete('blogs_comment', '`parent_id` = ' . to_sql($cid));
            }

            self::updateCountComment($comment['post_id']);
            if ($comment['parent_id']) {
                $responseData = self::getCountCommentReplies($comment['parent_id']);
            } else {
                $responseData = self::getCountComment($comment['post_id']);
            }
        }

        return $responseData;
    }
    /* Comments */


    static public function getImg($blogId, $imgId, $size)
    {
        global $g;

        $file = "blogs/{$blogId}_{$imgId}_{$size}.jpg" ;
        if (!custom_file_exists($g['path']['url_files'] . $file)) {
            $file = '';
        }

        return $file;
    }

    static public function getImgFileBig($blogId, $imgId, $imageFileDefault = 'blog_bm.png')
    {
        $imageFile = $imageFileDefault;
        $sizesImage = array('bm', 's');//Fix old template
        foreach ($sizesImage as $size) {
            $imageSrc = self::getImg($blogId, $imgId, $size);
            if ($imageSrc) {
                $imageFile = $imageSrc;
                break;
            }
        }

        return $imageFile;
    }

    public static function createBasePhotoFile($file, $postId, $imgId)
    {
        global $g;

        $image = new Image();
        if ($image->loadImage($file)) {
            $sizesBasePhotoDefault = CProfilePhoto::$sizesBasePhoto;

            CProfilePhoto::$sizesBasePhoto = array(
                'bm' => 'blog_big',
                's'  => 'blog_middle',
            );

            $baseFileName = $g['path']['dir_files'] . "blogs/{$postId}_{$imgId}_";

            //popcorn modified s3 bucket blogs 2024-05-06
            if(isS3SubDirectory($baseFileName)) {
                $baseFileName = $g['path']['dir_files'] . "temp/blogs/{$postId}_{$imgId}_";
            }
            if(CProfilePhoto::createBasePhotoFile($image, $baseFileName, $file)) {
                //popcorn modified s3 bucket blogs 2024-05-06
                $file_sizes = ['bm', 's', 't', 'm', 'o'];
                if(isS3SubDirectory($g['path']['dir_files'] . "blogs/{$postId}_{$imgId}_")) {
                    foreach ($file_sizes as $key => $size) {
                        if(file_exists($baseFileName . $size . '.jpg')) {
                            custom_file_upload($baseFileName . $size . '.jpg', "blogs/{$postId}_{$imgId}_" . $size . '.jpg');
                        }
                    }
                }
            }

            CProfilePhoto::$sizesBasePhoto = $sizesBasePhotoDefault;
        }
    }

    static function updateLikeComment()
    {
        $uid = guid();
        $id = get_param_int('blog_id');
        $cid = get_param_int('cid');
        $like = get_param_int('like');

        $date = date('Y-m-d H:i:s');
        if ($like) {
            $commentInfo = DB::select('blogs_comment', '`id` = ' . to_sql($cid));
            if (!isset($commentInfo[0])) {
                return false;
            }
            $commentInfo = $commentInfo[0];
            $commentUid = $commentInfo['user_id'];

            $isNew = intval($commentUid != $uid);

            $postUserId = DB::result('SELECT `user_id` FROM `blogs_post` WHERE `id` = ' . to_sql($id));

            $sql = 'INSERT IGNORE INTO `blogs_comments_likes`
                       SET `user_id` = ' . to_sql($uid) . ',
                           `cid` = ' . to_sql($cid) . ',
                           `date` = ' . to_sql($date) . ',
                           `is_new` = ' . to_sql($isNew) . ',
                           `comment_user_id` = ' . to_sql($commentUid) . ',
                           `post_user_id` = ' . to_sql($postUserId) . ',
                           `post_id` = ' . to_sql($id);
            DB::execute($sql);
            $isNewLikes = 1;
        } else {
            $where = '`user_id` = '  . to_sql($uid) .
                ' AND `cid` = '   . to_sql($cid) .
                ' AND `post_id` = '   . to_sql($id);
            DB::delete('blogs_comments_likes', $where);
            $sql = 'SELECT COUNT(id)
                      FROM `blogs_comments_likes`
                     WHERE `cid` = ' . to_sql($cid)
                 . ' LIMIT 1';
            $isNewLikes = DB::result($sql);
        }


        $countLikes = DB::count('blogs_comments_likes', '`cid` = ' . to_sql($cid));
        $data = array('likes' => $countLikes,
                      'last_action_like' => $date,
                      'is_new_like' => $isNewLikes);
        DB::update('blogs_comment', $data, '`id` = ' . to_sql($cid));

        DB::update('blogs_post', array('last_action_comment_like' => $date), '`id` = ' . to_sql($id));
        //self::sendAlert('like', $id, $uid);

        return array('likes' => $countLikes, 'date' => $date, 'likes_users' => User::getTitleLikeUsersComment($cid, $countLikes, 'blogs_post'));
    }


    static function addLikePost($blogId = null, $uid = null)
    {
        if ($blogId === null) {
            $blogId = get_param_int('blog_id');
        }
        if ($uid === null) {
            $uid = guid();
        }

        $blogInfo = DB::one('blogs_post', '`id` = ' . to_sql($blogId));
        if (!$blogInfo) {
            return false;
        }

        $date = date('Y-m-d H:i:s');
        $sql = 'INSERT IGNORE INTO `blogs_post_likes`
                   SET `user_id` = ' . to_sql($uid) . ',
                       `blog_id` = ' . to_sql($blogId) . ',
                date = ' . to_sql($date);
        DB::execute($sql);

        $likes = DB::count('blogs_post_likes', '`blog_id` = ' . to_sql($blogId));

        DB::update('blogs_post', array('likes' => $likes, 'last_action_like' => $date), '`id` = ' . to_sql($blogId));
        //self::sendAlert('like', $id, $uid);

        return $likes;
    }


    static function removeLikePost($blogId = null, $uid = null)
    {
        if ($blogId === null) {
            $blogId = get_param_int('blog_id');
        }
        if ($uid === null) {
            $uid = guid();
        }

        $blogInfo = DB::one('blogs_post', '`id` = ' . to_sql($blogId));
        if (!$blogInfo) {
            return false;
        }

        $where = '`user_id` = ' . to_sql($uid) . ' AND `blog_id` = ' . to_sql($blogId);
        DB::delete('blogs_post_likes', $where);

        $date = date('Y-m-d H:i:s');
        $likes = DB::count('blogs_post_likes', '`blog_id` = ' . to_sql($blogId));

        DB::update('blogs_post', array('likes' => $likes, 'last_action_like' => $date), '`id` = ' . to_sql($blogId));

        return $likes;
    }

    static function getImageDefault($id, $size = 'bm', $item = null, $cache = true)
    {
        global $g;

        $key = 'blogs_post_default_' . $size . '_' . intval($id);

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
        $placeholder = false;
        $image = explode('|', $item['images']);
        if ($image) {
            $image = CBlogsTools::getImg($id, $image[0], 'bm');
        }

        if (!$image) {
            $placeholder = true;
            $image = $g['path']['url_files'] . "blog_{$size}.png";
        }
        $result = array('image' => $image, 'placeholder' => $placeholder);

        Cache::add($key, $result);

        return $result;
    }

    function parseBlock(&$html)
	{
        $cmd = get_param('cmd');
        $id = get_param_int('blog_id');
        $type = 'blogs_post';

        if ($cmd == 'get_blog_post_comment') {
            if ($id) {
                self::parseComments($html, $id);
            }
        } elseif ($cmd == 'blogs_post_comment_add') {
            $comment = self::addComment();

            if ($comment && $id) {
                $block = 'comment';
                $isReply = get_param_int('reply_id');
                if ($isReply) {
                    $block = 'comments_reply_item';
                }

                $comment['item_group_id'] = 0;
                $comment['comment'] = $comment['text'];
                $comment['date'] = $comment['dt'];

                Wall::$isParseCommentsBlog = true;
                $comment = CProfilePhoto::prepareDataComment($comment, $type);
                CProfilePhoto::parseComment($html, $comment, $block, $type);
                Wall::$isParseCommentsBlog = false;

                if ($isReply) {
                    $html->parse('comments_reply_list');
                }
            }
        } elseif ($cmd == 'blogs_post_like' || $cmd == 'blogs_post_unlike') {
            if ($cmd == 'blogs_post_like') {
                $likes = self::addLikePost();
            } else {
                $likes = self::removeLikePost();
            }
            $blogId = get_param_int('blog_id');
            self::parseLikes($html, $blogId, $likes);
        }

        parent::parseBlock($html);
    }

}