<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CBlogsTools
{
    const ALLOWTAGS = '<b><i><u><s><strike><strong><em>';
    const STRLEN = 90;
    const STRLENSUBJECT = 80;
    const STRLENSUBJECTBLOG = 65;
    const STRLENCONTEXT = 35;
    const STRLENMEDIUM = 35;
    const STRLENVERYSHORT = 22;
    const VIDEOSTARTTAG = '<div class="blogs_video">';
    const VIDEOENDTAG = '</div>';
    const IMAGESTARTTAG = '<div class="blogs_image">';
    const IMAGEENDTAG = '</div>';
    static $videoWidth = 424;

    static $parseSmile = true;
    static $imagesLimit = 0;
    static $thumbnail_postfix = "s";

    static $factor_strlen = 2.5;

    static function setImagesLimit($limit)
    {
        self::$imagesLimit = $limit;
    }

    static function getImagesLimit()
    {
        return self::$imagesLimit;
    }

    static public function stags($str)
    {
        return strip_tags_attributes($str, self::ALLOWTAGS);
    }

    /* Subscriptions */
	static public function isSubscrided($subscriber_id, $blogger_id)
	{
        $uid1 = intval($subscriber_id);
        $uid2 = intval($blogger_id);
        $r = DB::count('blogs_subscribe', "subscriber_user_id='" . $uid1 . "' AND blogger_user_id='" . $uid2 . "'");
        return ($r > 0);
    }
	static public function addSubscription($blogger_id)
	{
        if (!self::isSubscrided(guser('user_id'), $blogger_id)
            and intval($blogger_id) > 0 and guser('user_id') > 0
            and intval($blogger_id) != guser('user_id')
        ) {
            $row = array(
                'subscriber_user_id' => guser('user_id'),
                'blogger_user_id' => intval($blogger_id),
            );
            DB::insert('blogs_subscribe', $row);
        }
    }
	static public function removeSubscription($blogger_id)
	{
        DB::delete('blogs_subscribe', "subscriber_user_id='" . guser('user_id') . "' AND blogger_user_id='" . intval($blogger_id) . "'");
    }
	static public function getSubscriptionsIds()
	{
        return DB::field('blogs_subscribe', 'blogger_user_id', "subscriber_user_id='" . guser('user_id') . "'");
    }
	static public function getPostsBySubscriptions($limit = '0,10')
	{
        return self::getPostsByUsersIds(self::getSubscriptionsIds(), $limit);
    }
	static public function getPostsByFriends($limit = '0,10')
	{
        return self::getPostsByUsersIds(getFriendsIds(), $limit);
    }
	static public function getPostsByUsersIds($ids, $limit = '0,10')
	{
        if (count($ids) > 0) {
            $posts = DB::select('blogs_post', "user_id IN (" . implode(',', $ids) . ")", 'dt DESC', $limit);
            $posts_r = array();
            foreach ($posts as $k => $v) {
                $posts_r[] = self::_filterPostToHtml($v, true);
            }
            return $posts_r;
        } else {
            return array();
        }
    }
	static public function countPostsBySubscriptions()
	{
        return self::countPostsByUsersIds(self::getSubscriptionsIds());
    }
	static public function countPostsByFriends()
	{
        return self::countPostsByUsersIds(getFriendsIds());
    }
	static public function countPostsByUsersIds($ids)
	{
        if (count($ids) > 0) {
            return DB::count('blogs_post', "user_id IN (" . implode(',', $ids) . ")");
        } else {
            return 0;
        }
    }


    /* Search */
	static public function getPostsByRand($limit = '0,10')
	{
        $posts = DB::select('blogs_post', "", 'RAND()', $limit);
        $posts_r = array();
        foreach ($posts as $k => $v) {
            $posts_r[$k] = self::_filterPostToHtml($v, true);
        }
        return $posts_r;
    }
	static public function filterSearchQuery($q)
	{
        return htmlspecialchars(self::_filterSearchIndex($q, false), ENT_QUOTES);
    }
	static public function countPostsByQuery($q)
	{
        $q = self::_filterSearchIndex($q, false);
        $q = DB::esc_like($q);
        return DB::count('blogs_post', "search_index LIKE '% " . $q . " %'");
    }
	static public function getPostsByQuery($q, $limit = '0,10')
	{
        $q = self::_filterSearchIndex($q, false);
        $q = DB::esc_like($q);
        self::incrementQuery($q);
        $posts = DB::select('blogs_post', "search_index LIKE '% " . $q . " %'", 'dt DESC', $limit);
        $posts_r = array();
        foreach ($posts as $k => $v) {
            $posts_r[$k] = self::_filterPostToHtml($v, true);
            $posts_r[$k]['context'] = self::_getContext($q, self::_filterRemoveUnusedTags($v['text']));
        }
        return $posts_r;
    }
	static public function incrementQuery($q)
	{
        $q = self::_filterSearchIndex($q, false);
        if (trim($q) != '') {
            $hs = DB::one('blogs_hotsearch', "text LIKE '" . $q . "'");
            if (is_array($hs)) {
                DB::update('blogs_hotsearch', array('count' => new DBNoEsc('count+1'), 'dt' => date('Y-m-d H:i:s')), "id='" . $hs['id'] . "'");
            } else {
                DB::insert('blogs_hotsearch', array('text' => $q, 'count' => '1', 'dt' => date('Y-m-d H:i:s')));
            }
        }
    }
	static public function countHotSearches()
	{
        return DB::count('blogs_hotsearch');
    }
	static public function getHotSearches($limit = '0,10')
	{
        $rows = DB::select('blogs_hotsearch', '', 'count DESC', $limit);
        $rows_r = array();
        foreach ($rows as $k => $v) {
                $rows_r[$k] = $v;
                $rows_r[$k]['urltext'] = urlencode($v['text']);
                $rows_r[$k]['dt_readable'] = Common::dateFormat($v['dt'],'blogs_searches_readable_datetime');
        }
        return $rows_r;
    }
	static protected function _getContext($q, $v)
	{
        $qlen = pl_strlen($q);
        $halflen = floor((self::STRLENCONTEXT - $qlen) / 2.5);
        $pos = strpos($v, $q);
        if ($pos !== false) {
            if ($pos <= $halflen) {
                $v = neat_trim($v, self::STRLENCONTEXT);
            } else {
                $pre_q = substr($v, 0, $pos);
                $post_q = substr($v, $pos + pl_strlen($q));
                $pre_q = neat_trimr($pre_q, $halflen);
                $post_q = neat_trim($post_q, $halflen);
                $v = $pre_q . ' ' . $q . ' ' . $post_q;
                $v = str_replace('  ', ' ', $v);
            }
        } else {
            $v = neat_trim($v, self::STRLENCONTEXT);
        }
        $v = str_replace($q, "<i>$q</i>", $v);
        return $v;
    }


    /* Selections by popular, new, discussed, etc. */
	static public function getTotalPosts()
	{
        return DB::count('blogs_post');
    }
	static public function countPopularBloggers()
	{
        return DB::count('user', 'blog_posts>0');
    }
	static public function getPopularBloggers($limit = '0,6', $addUserInfo = true,  $needLastPost = true)
	{
        //blog_posts>0
        $pops = DB::select('user', 'blog_posts>0', 'blog_visits DESC, blog_comments DESC, blog_posts DESC', $limit);
        foreach ($pops as $k => &$v) {
            if ($addUserInfo) {
                $v['photo'] = urphoto($v['user_id']);
                $v['name_short'] = User::nameShort($v['name']);
                $v['user_name'] = $v['name'];
                $v['user_country'] = $v['country'];
                $v['user_city'] = $v['city'];
            }
            if ($needLastPost) {
                $v['post'] = self::getLastPostByUser($v['user_id']);
            }
        }
        return $pops;
    }
	static public function countPopular()
	{
        return DB::count('blogs_post');
    }
	static public function getPopular($limit = '0,6')
	{
        $posts = DB::select('blogs_post', '', 'count_views DESC', $limit);
        $posts_r = array();
        foreach ($posts as $k => $v) {
            $posts_r[] = self::_filterPostToHtml($v, true);
        }
        return $posts_r;
    }
	static public function countDiscussed()
	{
        return DB::count('blogs_post');
    }
	static public function getDiscussed($limit = '0,9')
	{
        $posts = DB::select('blogs_post', '', 'count_comments DESC, count_views DESC', $limit);
        $posts_r = array();
        foreach ($posts as $k => $v) {
            $posts_r[] = self::_filterPostToHtml($v, true);
        }
        return $posts_r;
    }
	static public function countNew()
	{
        return DB::count('blogs_post');
    }

	static public function getNew($limit = '0,6')
	{
        $posts = DB::select('blogs_post', '', 'dt DESC', $limit);
        $posts_r = array();
        foreach ($posts as $k => $v) {
            $posts_r[] = self::_filterPostToHtml($v, true);
        }
        return $posts_r;
    }


    /* Selections by user */
	static public function countPostsByUser($user_id)
	{
        return DB::count('blogs_post', "user_id='" . intval($user_id) . "'");
    }
	static public function getPostsByUser($user_id, $limit = '0,10')
	{
        $posts = DB::select('blogs_post', "user_id='" . intval($user_id) . "'", 'dt DESC', $limit);
        $posts_r = array();
        foreach ($posts as $k => $v) {
            $posts_r[] = self::_filterPostToHtml($v);
        }
        return $posts_r;
    }
	static public function getLastPostByUser($user_id)
	{
        $post = DB::one('blogs_post', "user_id='" . intval($user_id) . "'", 'dt DESC');
        if (is_array($post)) {
            $post = self::_filterPostToHtml($post);
        } else {
            $post = self::getNullPost();
        }
        return $post;
    }

    /* One post viewing and editing */
	static public function viewPostByIdAndUserId($id, $user_id)
	{
        DB::update('blogs_post', array('count_views' => new DBNoEsc('count_views+1')), 'id=\'' . intval($id) . '\'');
        DB::update('user', array('blog_visits' => new DBNoEsc('blog_visits+1')), 'user_id=\'' . intval($user_id) . '\'');
    }
	static public function viewBlogByUserId($user_id)
	{
        DB::update('user', array('blog_visits' => new DBNoEsc('blog_visits+1')), 'user_id=\'' . intval($user_id) . '\'');
    }
	static public function getPostById($id, $withUser = false)
	{
        $post = DB::one('blogs_post', "id='" . intval($id) . "'");
        if (is_array($post)) {
            $post = self::_filterPostToHtml($post, $withUser);
        }
        return $post;
    }
	static public function getPostFromPost()
	{
        return array(
            'subject'       => self::_filterSubject(param("subject"), param("text")),
            'text'          => self::_filterText(param("text")),
        );
    }
	static public function getPostFromPostNotNull()
	{
        $a = self::getPostFromPost();
        $ar = array();
        foreach ($a as $k => $v) {
            if ($v != '') {
                $ar[$k] = $v;
            }
        }
        return $ar;
    }
	static public function getNullPost()
	{
        $row = array(
            'id'             => '0',
            'user_id'        => '',
            'dt'             => '',
            'count_comments' => '0',
            'subject'        => '',
            'text'           => '',
            'search_index'   => '',
            'images'         => '',
        );
        return self::_filterPostToHtml($row);
    }
    static public function uploadImg($post_id, $img_id, $file)
    {
        global $g;
        $im = new Image();
        if ($im->loadImage($file)) {
            $filePrefix = $g['path']['dir_files'] . "blogs/" . $post_id . '_' . $img_id;
            
            //popcorn modified s3 bucket blogs image 2024-05-06
            if(isS3SubDirectory($filePrefix)) {
                $filePrefix = $g['path']['dir_files'] . "temp/blogs/" . $post_id . '_' . $img_id;
            }

            //on post edit small
            $im->resizeCroppedMiddle(85, 64);
            $im->saveImage($filePrefix . "_t.jpg", $g['image']['quality']);

            //to main page
            $im->loadImage($file);
            $im->resizeCroppedMiddle(116, 116);
            $im->saveImage($filePrefix . "_m.jpg", $g['image']['quality']);

            //popcorn modified s3 bucket blogs image upload 2024-05-06
            @copyUrlToFile($file, $filePrefix . "_o.jpg");
            @chmod($filePrefix . "_o.jpg", 0777);
            Blogs::createBasePhotoFile($file, $post_id, $img_id);

            $path = array($filePrefix . '_t.jpg',
                          $filePrefix . '_m.jpg',
                          $filePrefix . '_s.jpg',
                          $filePrefix . '_bm.jpg',
                          $filePrefix . "_o.jpg");

            Common::saveFileSize($path);
            return true;
        } else {
            return false;
        }
    }

    static public function deleteImg($post_id, $img_id)
    {
        global $g;
        $filePrefix = $g['path']['dir_files'] . "blogs/" . $post_id . '_' . $img_id;
        $path = array($filePrefix . '_t.jpg', $filePrefix . '_m.jpg',
                      $filePrefix . '_s.jpg', $filePrefix . '_bm.jpg',
                      $filePrefix . "_o.jpg");
        Common::saveFileSize($path, false);

        //popcorn modified s3 bucket blogs images delete 2024-05-06
        if(isS3SubDirectory($filePrefix)) {
            custom_file_delete($filePrefix . "_t.jpg");
            custom_file_delete($filePrefix . "_s.jpg");
            custom_file_delete($filePrefix . "_bm.jpg");
            custom_file_delete($filePrefix . "_m.jpg");
            custom_file_delete($filePrefix . "_o.jpg");
        } else {
            if (custom_file_exists($filePrefix . "_t.jpg")) unlink($filePrefix . "_t.jpg");
            if (custom_file_exists($filePrefix . "_s.jpg")) unlink($filePrefix . "_s.jpg");
            if (custom_file_exists($filePrefix . "_bm.jpg")) unlink($filePrefix . "_bm.jpg");
            if (custom_file_exists($filePrefix . "_m.jpg")) unlink($filePrefix . "_m.jpg");
            if (custom_file_exists($filePrefix . "_o.jpg")) unlink($filePrefix . "_o.jpg");
        }
    }

    static public function getImg($post_id, $img_id, $size)
    {
        global $g;
        $file = $g['path']['url_files'] . "blogs/{$post_id}_{$img_id}_{$size}.jpg" ;
        if (!custom_file_exists($file)) {
            $file = '';
        }
        return $file;
    }

    static public function existsImg($post_id, $img_id)
    {
        global $g;
        $filePrefix = $g['path']['dir_files'] . "blogs/" . $post_id . '_' . $img_id;
        return (custom_file_exists($filePrefix . "_s.jpg") and custom_file_exists($filePrefix . "_m.jpg") and custom_file_exists($filePrefix . "_o.jpg") and custom_file_exists($filePrefix . "_t.jpg"));
    }

    static public function deleteImgs($post_id, $imgs)
    {
        $r = array();
        foreach ($imgs as $i) {
            self::deleteImg($post_id, $i);
        }
        return $r;
    }

    static public function getOnlyExistsImg($post_id, $imgs)
    {
        $r = array();
        foreach ($imgs as $i) {
            if ($i != '' and self::existsImg($post_id, $i)) {
                $r[$i] = $i;
            }
        }
        return $r;
    }
	static public function insertPost()
	{
        $row = array(
            'user_id'        => guser('user_id'),
            'dt'             => date('Y-m-d H:i:s'),
            'count_comments' => '0',
            'subject'        => self::_filterSubject(param("subject"), param("text")),
            //'text'           => self::_filterText(param("text")),
            'search_index'   => self::_filterSearchIndex(param("subject") . ' ' . param("text")),
        );
        DB::insert('blogs_post', $row);
        $id = DB::insert_id();
        DB::update('user', array('blog_posts' => new DBNoEsc('blog_posts+1')), 'user_id=' . guser('user_id'));

        Wall::add('blog_post', $id);

        self::uploadImagesAndFilterText($id);
    }

	static public function uploadImagesAndFilterText($id)
	{
        global $g;
        $id = intval($id);
        $post = DB::one('blogs_post', "id='" . $id . "'");

        $r = explode('|', $post['images']);
        $i = intval($r[count($r) - 1]) + 1;
        if (isset($_FILES['img']['tmp_name'])) {
            foreach ($_FILES['img']['tmp_name'] as $k => $v) {
                if ($_FILES['img']['error'][$k] == '0' and is_uploaded_file($v) and $_FILES['img']['size'][$k] < 5*1024*1024*1024) {
                    if (self::uploadImg($id, $i, $v)) {
                        $r[$i] = $i;
                        $i++;
                    }
                }
            }
        }

        $text = param("text");

        $exts = explode('|', 'png|jpg|jpeg|gif');
        foreach ($exts as $ext) {
            $objs = grabs($text, 'http://', '.' . $ext, true);
            foreach ($objs as $obj) {
                $file = $g['path']['dir_files'] . "temp/blogs_" . $id . '.txt';
                @copyUrlToFile($obj, $file);
                if (file_exists($file)) {
                    if (self::uploadImg($id, $i, $file)) {
                        $r[$i] = $i;
                        $text = str_replace($obj, '{img:' . $i . '}', $text);
                        $i++;
                    }
                    unlink($file);
                }
            }
        }

        $r = self::getOnlyExistsImg($id, $r);
        $imgs = implode("|", $r);

        $text = self::_filterImgTagsToDb($text, $id);
        $text = self::_filterText($text);

        DB::update('blogs_post', array('images' => $imgs, 'text' => $text), "id='$id'");
    }

	static public function updatePostOnlyExistsImgs($id)
	{
        global $g;
        $id = intval($id);
        $post = DB::one('blogs_post', "id='" . $id . "'");

        $r = explode('|', $post['images']);
        $r = self::getOnlyExistsImg($id, $r);
        $imgs = implode("|", $r);

        DB::update('blogs_post', array('images' => $imgs), "id='$id'");
    }

	static public function updatePostById($id)
	{
        $row = array(
            'subject'        => self::_filterSubject(param("subject"), param("text")),
            //'text'           => self::_filterText(param("text")),
            'search_index'   => self::_filterSearchIndex(param("subject") . ' ' . param("text")),
        );
        DB::update('blogs_post', $row, 'user_id=\'' . intval(guser('user_id')) . '\' AND id=\'' . intval($id) . '\'');
        self::uploadImagesAndFilterText($id);
    }

	static public function updatePostByIdByAdmin($id)
	{
        $row = array(
            'subject'        => self::_filterSubject(param("subject"), param("text")),
            //'text'           => self::_filterText(param("text")),
            'search_index'   => self::_filterSearchIndex(param("subject") . ' ' . param("text")),
        );
        DB::update('blogs_post', $row, 'id=\'' . intval($id) . '\'');
        self::uploadImagesAndFilterText($id);
    }

	static public function delPostById($id)
	{
        Blogs::deletePost($id);
    }

    static public function delPostByIdByAdmin($id)
	{
        Blogs::deletePost($id, true);
    }

    /* One post text filters */
	static function _filterImgTagsToDb($text, $id)
    {
        $grabs = grabs($text, '{img:', '}');
        foreach ($grabs as $gr) {
            if (!self::existsImg($id, $gr)) {
                $text = str_replace('{img:' . $gr . '}', '', $text);
            }
        }
        return $text;
    }

	static protected function _filterPostToHtml($row, $withUser = false)
	{

        global $p, $l;

        $isSocialBlogs = Common::isOptionActiveTemplate('blogs_social_enabled');

        if ($withUser) {
            $row['user'] = user($row['user_id']);
            $row['user_photo'] = urphoto($row['user_id']);
        }

        $textWithoutTags = '';
        if ($isSocialBlogs) {
            //$row['text_readable'] = nl2br($row['text']);
            $row['text_readable'] = self::_filterMediaTagsToHtml($row['text'], $row);
            $row['text_readable'] = newLineToParagraph(trim($row['text_readable']), array('<div'));

            $textWithoutTags = strip_tags($row['text']);

            $row['tags_value'] = he(Blogs::getTagsView($row['id']));
        } else {
            /* Fix new blog to old template */
            //$row['text'] = Blogs::prepareTagsText($row['text']);//strip_tags($row['text'], self::ALLOWTAGS . '<a>');
            //Remove tag <a>
            $row['text'] = Blogs::_sanitizeLink($row['text']);

            $textWithoutTags = $row['text'];
            /* Fix new blog to old template */

            $row['text_readable'] = self::_filterMediaTagsToHtml(self::_filterLinksTagsToHtml($row['text']), $row);
            #$row['text_readable'] = nl2br(trim($row['text_readable']));
            $row['text_readable'] = newLineToParagraph(trim($row['text_readable']), array('<div'));
        }

        $row['subject_real'] = $row['subject'];

        if ($row['subject'] == '') {
            $text = $row['text'];
            if (strpos($text, "\n") !== false) {
                $subject = substr($text, 0, strpos($text, "\n"));
            } else {
                $subject = $text;
            }
            $row['subject_req'] = str_replace("\n", ' ', neat_trim(preg_replace("/{+[^{](.*?)+\}/", "",$text), self::STRLENSUBJECT));
        } else {
            $row['subject_req'] = $row['subject'];
        }

        if(trim($row['subject_req']=="") && $p!="blogs_write.php") $row['subject_req'] = l('no_subject');

        $row['subject'] = hard_trim($row['subject_req'], self::STRLENSUBJECTBLOG);
        $row['subject_short'] = hard_trim($row['subject_req'], self::STRLENVERYSHORT);
        $row['subject_medium'] = hard_trim($row['subject_req'], self::STRLENMEDIUM);


        $maxlen = floor(self::STRLEN * self::$factor_strlen);

        $stext = $textWithoutTags;

        if (pl_strlen($stext) >  $maxlen) {
            $text = self::_filterLinksTagsToHtml(neat_trim($textWithoutTags, $maxlen));
            $text = self::_filterRemoveUnusedTags($text);
            $row['text_short'] = trim(self::_filterRemoveEmptyLines($text));
            $row['text_short_readable'] = nl2br($row['text_short']);
            $row['text_is_short'] = false;
        } else {
            $row['text_short'] = self::_filterRemoveUnusedTags($textWithoutTags);
            $row['text_short_readable'] = $row['text_readable'];
            $row['text_is_short'] = true;
        }
        $row['text_short_onlymedia'] = self::_filterMediaTagsToHtml(self::_filterMediaTagsOnly(nl2br($row['text'])), $row);


        if (date('Y') != pl_date('Y', $row['dt'])) {
            $row['dt_readable'] = Common::dateFormat($row['dt'], 'blogs_datetime', false);
        } else {
            $row['dt_readable'] = Common::dateFormat($row['dt'], 'blogs_datetime_this_year', false);
        }
        $row['dt_readable2'] = Common::dateFormat($row['dt'], 'blogs_list_datetime', false);
        $row['dt_readable3'] = Common::dateFormat($row['dt'], 'blogs_list_date', false);

        return $row;
    }
	static protected function _filterRemoveEmptyLines($text)
    {
        $lines = explode("\n", $text);
        $text = array();
        foreach ($lines as $line) {
            if (trim($line) != '') {
                $text[] = $line;
            }
        }
        $text = implode("\n", $text);
        return $text;
    }
	static protected function _filterMediaTagsToHtml($text, $row, $thumbnail_postfix = "s")
    {
        if (Common::isOptionActiveTemplate('blogs_social_enabled')) {
            $text = Blogs::_filterVideoTags($text);
            $text = Blogs::_filterImgTags($text, $row);
        } else {
            $text = VideoHosts::filterFromDb($text, self::VIDEOSTARTTAG, self::VIDEOENDTAG, self::$videoWidth);
            $text = self::_filterImgTags($text, $row);
        }

        $text = self::_filterRemoveUnusedTags($text);
        return $text;
    }
	static protected function _filterLinksTagsToHtml($text)
    {

        if (!self::$parseSmile) {
            $text = Common::parseLinks($text);
        } else {
            $text = Common::parseLinksSmile($text);
        }
		return $text;

/*
		global $g;
        $ends = explode("|", " |\n|,|)|(");
        foreach ($ends as $end) {
            $grabs = grabs($text, 'http://', $end, true);
            foreach ($grabs as $gr) {
                $gr = trim($gr);
                $text = str_replace($gr, '<a href="' . $gr . '">' . hard_trim($gr, 40) . '</a>', $text);
            }
        }
        return $text;
*/
    }
	static protected function _filterImgTags($text, $row)
    {
        global $g;
        $grabs = grabs($text, '{img:', '}');
        $tags_replaced = array();
        foreach ($grabs as $gr) {
            if (self::existsImg($row['id'], $gr)) {
                $file = $g['path']['url_files'] . "blogs/" . $row['id'] . '_' . $gr . '_' . self::$thumbnail_postfix . '.jpg';
                $file2 = $g['path']['url_files'] . "blogs/" . $row['id'] . '_' . $gr . '_o.jpg';
                $text = str_replace('{img:' . $gr . '}',
                        self::IMAGESTARTTAG . '<a class="lightbox" href="' . $file2 . '"><img id="blogsimg-' . $row['id'] . '_' . $gr . '" src="' . $file . '" alt=""/></a>' . self::IMAGEENDTAG, $text);
                $tags_replaced[] = $gr;
            }
        }
        $imgs = explode('|', $row['images']);
        $counter = 0;
        foreach ($imgs as $gr) {
            if (!in_array($gr, $tags_replaced) and self::existsImg($row['id'], $gr)) {
                $file = $g['path']['url_files'] . "blogs/" . $row['id'] . '_' . $gr . '_' . self::$thumbnail_postfix . '.jpg';
                $file2 = $g['path']['url_files'] . "blogs/" . $row['id'] . '_' . $gr . '_o.jpg';
                $text .= self::IMAGESTARTTAG . '<a class="lightbox" href="' . $file2 . '"><img id="blogsimg-' . $row['id'] . '_' . $gr . '" src="' . $file . '" alt=""/></a>' . self::IMAGEENDTAG;
                $counter++;
                if(self::getImagesLimit() > 0 && self::getImagesLimit() >= $counter) {
                    break;
                }
            }
        }
        return $text;
    }
	static protected function _filterMediaTagsOnly($text)
    {
        $grabs = Common::grabsTags($text);
        //$grabs = grabs($text, '{', '}', true);
        $text = implode("\n", $grabs);
        return $text;
    }
	static protected function _filterRemoveUnusedTags($text)
    {
        $grabs = Common::grabsTags($text);
        //$grabs = grabs($text, '{', '}', true);
        foreach ($grabs as $gr) {
            $text = str_replace($gr, "", $text);
        }
        return $text;
    }
	static protected function _filterSubject($subject, $text)
	{
        $subject = str_replace("\r\n", "\n", $subject);
        $subject = str_replace("\r", "\n", $subject);
        $subject = self::stags($subject);
        $subject = neat_trim($subject, self::STRLENSUBJECT);
        //$subject = htmlspecialchars($subject, ENT_QUOTES);
        $subject = trim($subject);

        return $subject;
    }
	static protected function _filterText($v)
	{
        $v = VideoHosts::filterToDb($v);
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        //$v = htmlspecialchars($v, ENT_QUOTES);
        $v = trim($v);
        return $v;
    }
	static protected function _filterSearchIndex($v, $wrapper = true)
	{
        $v = strip_tags($v);
        $symbols = explode(' ', "` ~ ! @ # $ % ^ & * ( ) _ + - = | \\ | / ? \" ' < > [ ] { } № ; : . , \t \r \n");
        //$symbols = explode(' ', "% _  \\  / < > \t \r \n");
        $symbols = explode(' ', "\t \r \n");
        $v = str_replace($symbols, ' ', $v);
        while (strpos($v, '  ') !== false) {
            $v = str_replace('  ', ' ', $v);
        }
        //$v = htmlspecialchars($v, ENT_QUOTES);
        if ($wrapper) {
            $v = ' ' . trim($v) . ' ';
        }
        return $v;
    }
	static public function filterText($v)
	{
        return self::_filterText($v);
    }


    /* Comments to post */
	static public function countCommentsNew()
	{
        return DB::count('blogs_comment');
    }
	static public function getCommentsNew($limit = '0,20')
	{
        $rows = DB::select('blogs_comment', '', 'dt DESC', $limit);
        $rows_r = array();
        foreach ($rows as $k => $v) {
            $data = self::_filterCommentToHtml($v);
            $data['text'] = Wall::prepareComment($data['text'], true);
            $rows_r[] = $data;
        }
        return $rows_r;
    }
	static public function countCommentsByPostId($post_id)
	{
        return DB::count('blogs_comment', "post_id='" . intval($post_id) . "'");
    }
	static public function getCommentsByPostId($post_id, $onlyTwo = false)
	{
        $rows = DB::select('blogs_comment', "post_id='" . intval($post_id) . "'", ($onlyTwo ? 'dt DESC' : 'dt'), ($onlyTwo ? '0,2' : ''));
        $rows_r = array();
        foreach ($rows as $k => $v) {
            $rows_r[] = self::_filterCommentToHtml($v);
        }
        return $rows_r;
    }
	static public function getCommentsByPostIdAdmin($post_id, $limit)
	{
        $rows = DB::select('blogs_comment', "post_id='" . intval($post_id) . "'", ('dt'), $limit);
        $rows_r = array();
        foreach ($rows as $k => $v) {

            $data = self::_filterCommentToHtml($v);
            $data['text'] = Wall::prepareComment($data['text'], true);
            $rows_r[] = $data;
        }
        return $rows_r;
    }
	static public function getCommentById($id)
	{
        $row = DB::one('blogs_comment', "id='" . intval($id) . "'");
        if($row) {
            $row = self::_filterCommentToHtml($row);
        }
        return $row;
    }
	static public function insertCommentByPostId($post_id)
	{
        if(guser('user_id') == 0) {
            redirect('join.php?cmd=login');
        }

        $post = self::getPostById($post_id);
        if (is_array($post)) {
            $row = array(
                'user_id'       => guser('user_id'),
                'post_id'       => $post['id'],
                'dt'            => date('Y-m-d H:i:s'),
                'text'          => self::_filterCommentText(param("text")),
            );
            DB::insert('blogs_comment', $row);
            $r = DB::insert_id();
            DB::update('user', array('blog_comments' => new DBNoEsc('blog_comments+1')), "user_id='" . $post['user_id'] . "'");
            DB::update('blogs_post', array('count_comments' => new DBNoEsc('count_comments+1')), "id='" . $post['id'] . "'");

            Wall::addItemForUser($post_id, 'blog', guid());
            Wall::setSiteSection('blog');
            Wall::setSiteSectionItemId($post_id);
            Wall::add('blog_comment', $r, false, '', false, guid());

            return $r;
        }
    }

	static public function delCommentById($id, $isAdmin = false)
	{
        Blogs::deleteComment($id, $isAdmin);
    }

    static public function delCommentByIdByAdmin($id)
	{
        self::delCommentById($id, true);
    }

    static protected function _filterCommentToHtml($row)
	{
        $row['text_readable'] = nl2br(Common::parseLinksSmile($row['text']));

        if (guser('user_id') == $row['user_id']) {
            $row['is_my'] = true;
        } else {
            $row['is_my'] = false;
        }

        if (date('Y') != pl_date('Y', $row['dt'])) {
            $row['dt_readable'] = Common::dateFormat($row['dt'], 'blogs_comment_datetime', false);
        } else {
            $row['dt_readable'] = Common::dateFormat($row['dt'], 'blogs_comment_datetime_this_year', false);
        }
        $row['time_ago'] = timeAgo($row['dt'], 'now', 'string', 60, 'second');

        $row['user'] = user($row['user_id']);
        $row['user_photo'] = urphoto($row['user_id']);

        return $row;
    }
	static protected function _filterCommentText($v)
	{
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        //$v = htmlspecialchars($v, ENT_QUOTES);
        $v = trim($v);
        return $v;
    }

	static public function filterCommentText($v)
	{
        return self::_filterCommentText($v);
    }
}