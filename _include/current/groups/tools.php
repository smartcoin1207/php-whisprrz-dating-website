<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once(dirname(__FILE__) . "/../video_hosts.php");
require_once(dirname(__FILE__) . "/../outside_images.php");

class CGroupsTools
{
    static $m_settings = null;
    static $player_containers = array();
    static $isNoPrivate = false;
    const ALLOWTAGS = '<b><i><u><s><strike><strong><em>';
    const VIDEOSTARTTAG = '<div class="groups_video">';
    const VIDEOENDTAG = '</div>';
    const IMAGESTARTTAG = '<div class="groups_image">';
    const IMAGEENDTAG = '</div>';
    static $videoWidth = 392;
    static $videoWidthComment = 354;

    static $outside_image_sizes = array(
        array(
            'width' => 390,
            'height' => 292,
            'allow_smaller' => true,
            'file_postfix' => 'th',
            ),
        );

    static public function stags($str)
    {
        return strip_tags_attributes($str, self::ALLOWTAGS);
    }

    static function filter_text_to_db($v, $parse_media = true, $old_text = null)
    {
        if($parse_media)
        {
           $v = VideoHosts::filterToDb($v);
           $v = OutsideImages::filter_to_db($v, $old_text);
        }
        $v = str_replace("\r\n", "\n", $v);
        $v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        //$v = htmlspecialchars($v, ENT_QUOTES);
        $v = trim($v);
        return $v;
    }

    static function filter_text_to_html($text, $parse_media = true, $thumbnail_postfix = "th", $comment = true)
    {
        $text = self::_filterLinksTagsToHtml($text);
        if($parse_media)
        {
            if ($comment) {
                $videoWidthCustom = self::$videoWidth;
            } else {
                $videoWidthCustom = self::$videoWidthComment;
            }

            $text = VideoHosts::filterFromDb($text, self::VIDEOSTARTTAG, self::VIDEOENDTAG, $videoWidthCustom);
            $text = OutsideImages::filter_to_html($text, self::IMAGESTARTTAG, self::IMAGEENDTAG , "lightbox");
        }
        $text = self::_filterRemoveUnusedTags($text);
        $text = nl2br(trim($text));
        return $text;
    }


    static protected function _filterLinksTagsToHtml($text)
    {

		return Common::parseLinksSmile($text);

/*      parse_links
		global $g;
        $ends = explode("|", " |\n|,|)|(");
        foreach ($ends as $end) {
            $grabs = grabs($text, 'http://', $end, true);
            foreach ($grabs as $gr) {
                $gr = trim($gr);
                $text = str_replace($gr, '<a href="' . $gr . '">' . $gr . '</a>', $text);
            }
        }
        return $text;
*/

    }

    static public function filterRemoveUnusedTags($text)
    {
        return self::_filterRemoveUnusedTags($text);
    }

    static protected function _filterRemoveUnusedTags($text)
    {
        //$grabs = grabs($text, '{', '}', true);
        $grabs = Common::grabsTags($text);
        foreach ($grabs as $gr) {
            $text = str_replace($gr, "", $text);
        }
        return $text;
    }

    static function members_by_group_sql_base($group_id)
    {
        $sql = "groups_group_member as g, user as u WHERE g.group_id = " . to_sql($group_id, 'Number') .
            " AND g.user_id = u.user_id  ".
            " ORDER BY g.created_at DESC";

        return array('query' => $sql, 'columns' => 'g.*, u.user_id, u.name');
    }

    static function comments_by_group_sql_base($group_id)
    {
        $sql = "groups_group_comment as c, user as u WHERE c.group_id = " . to_sql($group_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at DESC";

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function comments_by_comment_sql_base($comment_id)
    {
        $sql = "groups_group_comment_comment as c, user as u WHERE c.parent_comment_id = " . to_sql($comment_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at ASC";//DESC

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function comments_by_forum_sql_base($forum_id)
    {
        $sql = "groups_forum_comment as c, user as u WHERE c.forum_id = " . to_sql($forum_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at ASC";

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function comments_by_forum_comment_sql_base($comment_id)
    {
        $sql = "groups_forum_comment_comment as c, user as u WHERE c.parent_comment_id = " . to_sql($comment_id, 'Number') . " AND c.user_id = u.user_id ORDER BY created_at ASC";

        return array('query' => $sql, 'columns' => 'c.*, u.user_id, u.name');
    }

    static function retrieve_from_sql($sql)
    {
        DB::query($sql);
        $results = array();

        while($row = DB::fetch_row())
        {
            $results[] = $row;
        }

        return $results;
    }

    static function retrieve_from_sql_base($sql_base, $limit = 0, $shift = 0)
    {
        return self::retrieve_from_sql("SELECT " . $sql_base['columns'] . " FROM " . $sql_base['query'] . ($limit ? (" LIMIT " .  intval($shift) . ", " . intval($limit)) : ''));
    }

    static function count_from_sql_base($sql_base)
    {
        return DB::result("SELECT COUNT(*) FROM " . $sql_base['query']);
    }

    static function split_search_to_words($search)
    {
        $search = str_replace(array(',', ';', '!', '?', '.'), array(' ', ' ', ' ', ' ', ' '), $search);

        $_words = explode(" ", $search);
        $words = array();
        foreach($_words as $word)
        {
            $word = trim($word);

            if(mb_strlen($word) > 0)
                $words[] = $word;
        }

        return $words;
    }

	static function order_by_from_settings($need_prefix = true)
    {
        $orders = array();

    	$settings = self::settings();

    	if($settings['category_id'])
            $orders[] = ($need_prefix ? 'e.' : '' ) . 'category_id = ' . $settings['category_id'] . ' DESC';

        return implode(", ", $orders);
    }

    static function groups_by_user_as_member_sql_base($user_id)
    {
        $sql = "groups_group as e, groups_group_member as g, user as u WHERE e.group_id = g.group_id AND 1 AND g.user_id=" . to_sql($user_id, 'Number') .
            " AND u.user_id=".to_sql($user_id, 'Number')."  ORDER BY (".to_sql($user_id, 'Number')."= e.user_id) DESC, e.created_at  DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_by_user_sql_base($user_id)
    {
        $sql = "groups_group as e, user as u WHERE e.user_id = u.user_id " .
            " AND e.user_id = " . to_sql($user_id, 'Number') .
            " ORDER BY e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_most_discussed_sql_base()
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "groups_group as e, user as u WHERE e.user_id = u.user_id " .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.group_n_comments DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_recently_added_sql_base()
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "groups_group as e, user as u WHERE e.user_id = u.user_id " .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_most_popular_sql_base()
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "groups_group as e, user as u WHERE e.user_id = u.user_id AND e.group_private = 0 " .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.group_n_members DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_by_query_sql_base($query)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $words = self::split_search_to_words($query);
        $searches = array();

        foreach($words as $word)
        {
            $searches[] = "(CONCAT_WS('', e.group_title, e.group_description) LIKE " . to_sql('%'.$word.'%') . ")";
        }

        $where_from_searches = count($searches) ? ("(" . implode(' OR ',  $searches) . ")") : "";

        $order_by_from_settings = self::order_by_from_settings();

        $sql = "groups_group as e, user as u WHERE e.user_id = u.user_id AND " .
            ($where_from_searches ? ($where_from_searches . "") : '1') .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.group_n_members DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_by_category_id_sql_base($category_id)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "groups_group as e, user as u WHERE e.user_id = u.user_id AND " .
            " e.category_id = " . to_sql($category_id, 'Number') .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.group_n_members DESC, e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*, u.name as user_name');
    }

    static function groups_by_place_sql_base($place, $upcoming)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $sql = "groups_group as e WHERE 1 AND DATE_ADD(e.group_datetime, INTERVAL 3 HOUR) " . ($upcoming ? '>' : '<=') . " NOW() AND " .
            "e.group_place LIKE " . to_sql($place) .
            " ORDER BY " .
            ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " e.group_datetime " . ($upcoming ? 'ASC' : 'DESC') . ", e.created_at DESC";

        return array('query' => $sql, 'columns' => 'e.*');
    }

    static function forums_by_group_recently_updated_sql_base($group_id)
    {
        $sql = "groups_forum as f WHERE f.group_id = " . to_sql($group_id, 'Number') .
            " ORDER BY f.updated_at DESC, f.created_at DESC";

        return array('query' => $sql, 'columns' => 'f.*');
    }

    static function comments_by_group_recently_added_sql_base($group_id)
    {
        $sql = "groups_group_comment as c, user as u WHERE c.user_id = u.user_id AND c.group_id = " . to_sql($group_id, 'Number') .
            " ORDER BY c.created_at DESC";

        return array('query' => $sql, 'columns' => 'c.*, u.name as user_name');
    }

    static function comments_by_forum_recently_added_sql_base($forum_id)
    {
        $sql = "groups_forum_comment as c, user as u WHERE c.user_id = u.user_id AND c.forum_id = " . to_sql($forum_id, 'Number') .
            " ORDER BY c.created_at DESC";

        return array('query' => $sql, 'columns' => 'c.*, u.name as user_name');
    }

    static function messages_recently_added_sql_base()
    {
        $order_by_from_settings = self::order_by_from_settings(false);
        $private = '';
        if (self::$isNoPrivate) {
            $private = 'AND e.group_private = 0';
        }
        $sql = "((SELECT c.comment_text, c.comment_id, c.created_at, u.name as user_name, f.forum_id, f.forum_title, e.group_id, e.category_id, e.group_title FROM " .
            " groups_forum_comment as c, user as u, groups_forum as f, groups_group as e WHERE " .
            "c.forum_id = f.forum_id AND f.group_id = e.group_id AND c.user_id = u.user_id " . $private . ") UNION " .
            "(SELECT cc.comment_text, cc.comment_id, cc.created_at, u.name as user_name, f.forum_id, f.forum_title, e.group_id, e.category_id, e.group_title FROM " .
            " groups_forum_comment_comment as cc, groups_forum_comment as c, user as u, groups_forum as f, groups_group as e WHERE " .
            " cc.parent_comment_id = c.comment_id AND c.forum_id = f.forum_id AND f.group_id = e.group_id AND cc.user_id = u.user_id  " . $private . "))" .
            " as t " .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " t.created_at DESC";

        return array('query' => $sql, 'columns' => '*');
    }

    static function messages_by_query_sql_base($query)
    {
        $order_by_from_settings = self::order_by_from_settings();

        $words = self::split_search_to_words($query);
        $searches = array();

        foreach($words as $word)
        {
            $searches[] = "(CONCAT_WS('', comment_text, forum_title, group_title) LIKE " . to_sql('%'.$word.'%') . ")";
        }

        $where_from_searches = count($searches) ? ("(" . implode(' OR ',  $searches) . ")") : "";

    	$order_by_from_settings = self::order_by_from_settings(false);

        $sql = "((SELECT c.comment_text, c.comment_id, c.created_at, u.name as user_name, f.forum_id, f.forum_title, e.group_id, e.category_id, e.group_title FROM " .
            " groups_forum_comment as c, user as u, groups_forum as f, groups_group as e WHERE " .
            "c.forum_id = f.forum_id AND f.group_id = e.group_id AND c.user_id = u.user_id) UNION " .
            "(SELECT cc.comment_text, cc.comment_id, cc.created_at, u.name as user_name, f.forum_id, f.forum_title, e.group_id, e.category_id, e.group_title FROM " .
            " groups_forum_comment_comment as cc, groups_forum_comment as c, user as u, groups_forum as f, groups_group as e WHERE " .
            " cc.parent_comment_id = c.comment_id AND c.forum_id = f.forum_id AND f.group_id = e.group_id AND c.user_id = u.user_id))" .
            " as t "
            . ($where_from_searches ? " WHERE {$where_from_searches}" : '') .
            " ORDER BY " . ($order_by_from_settings ? ($order_by_from_settings . ", ") : '') .
            " t.created_at DESC";

        return array('query' => $sql, 'columns' => '*');
    }

    static function number_of_groups_where_user_is_member($user_id)
    {
        $sql_base = self::groups_by_user_as_member_sql_base($user_id);
        return self::count_from_sql_base($sql_base);
    }


    static function settings()
	{
		global $g_user;

		if(!self::$m_settings)
		{
			self::$m_settings = DB::row("SELECT * FROM groups_setting WHERE user_id = " . $g_user['user_id'] . " LIMIT 1");
	        if(!self::$m_settings)
	        {
	        	self::$m_settings = array('category_id' => 0);
	        }
		}

		return self::$m_settings;
	}

	static function setting_set($name, $value)
	{
		self::settings();

		self::$m_settings[$name] = $value;
	}

    static function settings_save()
    {
        global $g_user;

        self::settings();

        if(isset(self::$m_settings['setting_id']))
        {
        	DB::execute("UPDATE groups_setting SET category_id = " . to_sql(self::$m_settings['category_id'], 'Number') .
        	   " WHERE user_id = " . $g_user['user_id']);
        }
        else
        {
            DB::execute("INSERT INTO groups_setting SET category_id = " . to_sql(self::$m_settings['category_id'], 'Number') .
               ", user_id = " . $g_user['user_id']);
        }
    }

    static function do_upload_group_image($name, $group_id)
    {
        global $g;
        global $g_user;

    	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
        {
            DB::execute("insert into groups_group_image set group_id = " . $group_id . ", user_id = " . $g_user['user_id'] . ", created_at = NOW();");
            $image_id = DB::insert_id();

            $sFile_ = $g['path']['dir_files'] . "groups_group_images/" . $image_id . "_";
            $im = new Image();

            if ($im->loadImage($_FILES[$name]['tmp_name'])) {
                $im->resizeWH($im->getWidth(), $im->getHeight(), false, $g['image']['logo'], $g['image']['logo_size']);
                $im->saveImage($sFile_ . "b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "b.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['groups_group_image']['thumbnail_x'], $g['groups_group_image']['thumbnail_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['groups_group_image']['thumbnail_big_x'], $g['groups_group_image']['thumbnail_big_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_b.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_b.jpg", 0777);
            }
            if ($im->loadImage($sFile_ . "b.jpg", $g['image']['quality'])) {
                $im->resizeCropped($g['groups_group_image']['thumbnail_small_x'], $g['groups_group_image']['thumbnail_small_y'], $g['image']['logo'], 0);
                $im->saveImage($sFile_ . "th_s.jpg", $g['image']['quality']);
                @chmod($sFile_ . "th_s.jpg", 0777);
            }
            if ($im->loadImage($_FILES[$name]['tmp_name'])) {
                $im->saveImage($sFile_ . "src.jpg", $g['image']['quality_orig']);
                @chmod($sFile_ . "src.jpg", 0777);
            }
            $path = array($sFile_ . 'b.jpg', $sFile_ . 'th.jpg', $sFile_ . 'th_b.jpg', $sFile_ . 'th_s.jpg', $sFile_ . 'src.jpg');
            Common::saveFileSize($path);
            self::update_group($group_id);
        }
    }

    static function update_group($group_id)
    {
        $n_images = DB::result("SELECT COUNT(image_id) FROM groups_group_image WHERE group_id = ".to_sql($group_id, 'Number'));
        $n_members = DB::result("SELECT COUNT(member_id) FROM groups_group_member WHERE group_id = ".to_sql($group_id, 'Number'));
        $n_comments = DB::result("SELECT COUNT(comment_id) FROM groups_group_comment WHERE group_id = ".to_sql($group_id, 'Number')) +
            DB::result("SELECT COUNT(cc.comment_id) FROM groups_group_comment_comment as cc, groups_group_comment as c " .
            "WHERE cc.parent_comment_id  = c.comment_id AND c.group_id = ".to_sql($group_id, 'Number'));
        $n_posts = $n_comments +
            DB::result("SELECT SUM(forum_n_comments) FROM groups_forum WHERE group_id = ".to_sql($group_id, 'Number'));

        DB::execute("UPDATE groups_group SET group_has_images = ". ($n_images ? 1 : 0) .
            ", group_n_members=".($n_members ? $n_members : 0).
            ", group_n_comments=".($n_comments).
            ", group_n_posts=".($n_posts).
            ", updated_at = NOW() WHERE group_id=" . to_sql($group_id, 'Number') . " LIMIT 1");
    }

    static function update_group_social($group_id)
    {
        $n_images = DB::result("SELECT COUNT(image_id) FROM groups_group_image WHERE group_id = ".to_sql($group_id, 'Number'));
        $n_members = DB::result("SELECT COUNT(member_id) FROM groups_group_member WHERE group_id = ".to_sql($group_id, 'Number'));
        $n_comments = DB::result("SELECT COUNT(id) FROM wall_comments WHERE group_id = ".to_sql($group_id, 'Number'));
        $n_posts = $n_comments +
            DB::result("SELECT SUM(forum_n_comments) FROM groups_forum WHERE group_id = ".to_sql($group_id, 'Number'));

        DB::execute("UPDATE groups_social SET  count_comments=".($n_comments).
            ", count_posts=".($n_posts).
            " WHERE group_id=" . to_sql($group_id, 'Number') . " LIMIT 1");
    }

    static function update_forum($forum_id)
    {
        $forum = self::retrieve_forum_by_id($forum_id);
        if($forum)
        {
	        $n_comments = DB::result("SELECT COUNT(comment_id) FROM groups_forum_comment WHERE forum_id = ".to_sql($forum_id, 'Number')) +
	            DB::result("SELECT COUNT(cc.comment_id) FROM groups_forum_comment_comment as cc, groups_forum_comment as c " .
	            "WHERE cc.parent_comment_id  = c.comment_id AND c.forum_id = ".to_sql($forum_id, 'Number'));

	        DB::execute("UPDATE groups_forum SET " .
	            " forum_n_comments=".($n_comments).
	            ", updated_at = NOW() WHERE forum_id=" . to_sql($forum_id, 'Number') . " LIMIT 1");

	        self::update_group($forum['group_id']);
        }
    }

    static function group_images($group_id, $random = true)
    {
    	global $g;

        if($n_images = DB::result("SELECT COUNT(image_id) FROM groups_group_image WHERE group_id=" . to_sql($group_id, 'Number') . " LIMIT 1"))
        {
            $image_n = $random ? rand(0, $n_images-1) : 0;
        	$image = DB::row("SELECT * FROM groups_group_image WHERE group_id=" . to_sql($group_id, 'Number') . " ORDER BY image_id DESC LIMIT " . $image_n . ", 1");

        	return array(
        	   "image_thumbnail" => $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_th.jpg",
        	   "image_thumbnail_s" => $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_th_s.jpg",
        	   "image_thumbnail_b" => $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_th_b.jpg",
        	   "image_file" => $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_b.jpg");
        }
        else
        {
            return array(
               "image_thumbnail" => $g['tmpl']['url_tmpl_main'] . "images/groups/foto_02.jpg",
               "image_thumbnail_s" => $g['tmpl']['url_tmpl_main'] . "images/groups/carusel_groups.gif",
               "image_thumbnail_b" => $g['tmpl']['url_tmpl_main'] . "images/groups/foto_02_l.jpg",
               "image_file" => $g['tmpl']['url_tmpl_main'] . "images/groups/foto_02_l.jpg");
        }
    }

    static function delete_group_image($image_id, $admin = false)
    {
        global $g;
        global $g_user;

        $image = DB::row("SELECT * FROM groups_group_image as i, groups_group as s, groups_group as m WHERE i.image_id=" . to_sql($image_id, 'Number') .
            " AND i.group_id = s.group_id " .
            " AND s.group_id = m.group_id " .
            ($admin ? "" : " AND (m.user_id = " . $g_user['user_id'] . " OR s.user_id = " . $g_user['user_id'] . ") ") .
            " LIMIT 1");
        if($image)
        {
            $filename_base = $g['path']['url_files'] . "groups_group_images/" . $image['image_id'];
            $path = array($filename_base . '_b.jpg', $filename_base . '_th.jpg', $filename_base . '_th_b.jpg', $filename_base . '_th_s.jpg', $filename_base . '_src.jpg');
            Common::saveFileSize($path, false);
            $filename = $filename_base . "_th.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_s.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_b.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_b.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_src.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);

            DB::execute("DELETE FROM groups_group_image WHERE image_id=".$image['image_id']. " LIMIT 1");

            CGroupsTools::update_group($image['group_id']);
        }
    }

    static function delete_group($group_id, $admin = false)
    {
        global $g;
        global $g_user;

        $group = self::retrieve_group_by_id($group_id);
        if($group && ($admin || $group['user_id'] == $g_user['user_id']))
        {
            DB::query("SELECT * FROM groups_group_image WHERE group_id=".$group['group_id'], 2);
            while($image = DB::fetch_row(2))
            {
                self::delete_group_image($image['image_id'], $admin);
            }

            DB::query("SELECT * FROM groups_group_comment WHERE group_id=".$group['group_id'], 2);
            while($comment = DB::fetch_row(2))
            {
                self::delete_group_comment($comment['comment_id'], $admin);
            }

            DB::query("SELECT * FROM groups_forum WHERE group_id=".$group['group_id'], 2);
            while($forum = DB::fetch_row(2))
            {
                self::delete_group_forum($forum['forum_id'], $admin);
            }

            DB::execute("DELETE FROM groups_group_member WHERE group_id=".$group['group_id']. " LIMIT 1");
            DB::execute("DELETE FROM groups_group WHERE group_id=".$group['group_id']. " LIMIT 1");

            Wall::removeBySiteSection('group', $group['group_id']);
        }
    }



    static function delete_group_social($group_id, $admin = false)
    {
        global $g;
        global $g_user;

        $group = self::retrieve_group_by_id($group_id);

        $group =  DB::row("SELECT * FROM groups_social WHERE group_id=" . to_sql($group_id, 'Number'). " LIMIT 1");


        if($group && ($admin || $group['user_id'] == $g_user['user_id']))
        {
            DB::query("SELECT * FROM groups_group_image WHERE group_id=".$group['group_id'], 2);
            while($image = DB::fetch_row(2))
            {
                self::delete_group_image($image['image_id'], $admin);
            }

            // DB::query("SELECT * FROM wall_comments WHERE group_id=".$group['group_id'], 2);
            // while($comment = DB::fetch_row(2))
            // {
            //     self::delete_group_social_comment($comment['id'], $admin);
            // }

            DB::query("SELECT * FROM groups_forum WHERE group_id=".$group['group_id'], 2);
            while($forum = DB::fetch_row(2))
            {
                self::delete_group_forum($forum['forum_id'], $admin);
            }

            DB::execute("DELETE FROM groups_social_subscribers WHERE group_id=".$group['group_id']. " LIMIT 1");
            DB::execute("DELETE FROM groups_social WHERE group_id=".$group['group_id']. " LIMIT 1");


            Wall::removeByGroupSection('group', $group['group_id']);
        }
    }



    static function delete_group_comment($comment_id, $admin = false)
    {
        global $g;
        global $g_user;

    	$comment = DB::row("SELECT * FROM groups_group as m, groups_group_comment as c WHERE c.comment_id=" . to_sql($comment_id, 'Number') .
            " AND m.group_id = c.group_id " .
            ($admin ? "" : (" AND (m.user_id = " . $g_user['user_id'] . " OR c.user_id = " . $g_user['user_id'] . " )")) .
            " LIMIT 1");
        if($comment)
        {
            OutsideImages::on_delete($comment['comment_text']);

            $sql = 'SELECT * FROM groups_group_comment_comment
                WHERE parent_comment_id = ' . to_sql($comment_id, 'Number');
            $subComments = DB::rows($sql);
            if(is_array($subComments) && count($subComments)) {
                foreach($subComments as $subComment) {
                    self::delete_group_comment_comment($subComment['comment_id'], true);
                }
            }

            DB::execute("DELETE FROM groups_group_comment WHERE comment_id=".$comment['comment_id']. " LIMIT 1");

            Wall::remove('group_wall', $comment_id, $comment['user_id']);

            CGroupsTools::update_group($comment['group_id']);
        }
    }

   

    static function delete_group_comment_comment($comment_id, $admin = false)
    {
        $sql = "SELECT cc.*, c.group_id FROM groups_group as m, groups_group_comment_comment as cc, groups_group_comment as c WHERE cc.comment_id=" . to_sql($comment_id, 'Number') .
            " AND m.group_id = c.group_id AND cc.parent_comment_id = c.comment_id " .
            ($admin ? "" : (" AND (m.user_id = " . guid() . " OR cc.user_id = " . guid() . " )")) .
            " LIMIT 1";
        $comment = DB::row($sql);
        if($comment)
        {
            OutsideImages::on_delete($comment['comment_text']);

            DB::execute("DELETE FROM groups_group_comment_comment WHERE comment_id=".to_sql($comment['comment_id']));

            Wall::remove('group_wall_comment', $comment_id, $comment['user_id']);

            CGroupsTools::update_group($comment['group_id']);
        }
    }

     static function delete_group_social_comment($comment_id, $admin = false)
    {
        global $g;
        global $g_user;

        $comment = DB::row("SELECT * FROM groups_social as m, wall_comments as c WHERE c.id=" . to_sql($comment_id, 'Number') .
            " AND m.group_id = c.group_id " .
            ($admin ? "" : (" AND (m.user_id = " . $g_user['user_id'] . " OR c.user_id = " . $g_user['user_id'] . " )")) .
            " LIMIT 1");
        if($comment)
        {
            OutsideImages::on_delete($comment['comment']);

            $sql = 'SELECT * FROM wall_comments
                WHERE parent_id = ' . to_sql($comment_id, 'Number');
            $subComments = DB::rows($sql);
            if(is_array($subComments) && count($subComments)) {
                foreach($subComments as $subComment) {
                    self::delete_group_social_comment_comment($subComment['id'], true);
                }
            }

            DB::execute("DELETE FROM wall_comments WHERE id=".$comment['id']. " LIMIT 1");


            CGroupsTools::update_group_social($comment['group_id']);
        }

    }

     static function delete_group_social_comment_comment($comment_id, $admin = false)
    {
 
        $comment = DB::row("SELECT * FROM groups_social as m, wall_comments as c WHERE c.id=" . to_sql($comment_id, 'Number') .
            " AND m.group_id = c.group_id " .
            ($admin ? "" : (" AND (m.user_id = " . $g_user['user_id'] . " OR c.user_id = " . $g_user['user_id'] . " )")) .
            " LIMIT 1");

        if($comment)
        {
            OutsideImages::on_delete($comment['comment']);

            DB::execute("DELETE FROM wall_comments WHERE id=".to_sql($comment['id']));


            CGroupsTools::update_group_social($comment['group_id']);
        }
    }

    static function delete_group_forum($forum_id, $admin = false)
    {
        global $g;
        global $g_user;

        $forum = DB::row("SELECT * FROM groups_forum as f, groups_group as g WHERE f.forum_id=" . to_sql($forum_id, 'Number') .
            " AND f.group_id = g.group_id " .
            ($admin ? "" : (" AND g.user_id = " . $g_user['user_id'])) .
            " LIMIT 1");

        if($forum)
        {
            DB::query("SELECT * FROM groups_forum_comment WHERE forum_id=".$forum['forum_id'], 3);
            while($comment = DB::fetch_row(3))
            {
                self::delete_forum_comment($comment['comment_id'], $admin, false);
            }

            DB::execute("DELETE FROM groups_forum WHERE forum_id=".$forum['forum_id']. " LIMIT 1");

            CGroupsTools::update_group($forum['group_id']);
        }
    }

    static function delete_forum_comment($comment_id, $admin = false, $update_forum = true)
    {
        global $g;
        global $g_user;

        $comment = DB::row("SELECT * FROM groups_forum as m, groups_group as g, groups_forum_comment as c WHERE c.comment_id=" . to_sql($comment_id, 'Number') .
            " AND m.forum_id = c.forum_id AND g.group_id = m.group_id " .
            ($admin ? "" : (" AND (g.user_id = " . $g_user['user_id'] . " OR c.user_id = " . $g_user['user_id'] . " )")) .
            " LIMIT 1");
        if($comment)
        {
            OutsideImages::on_delete($comment['comment_text']);

            $sql = 'SELECT * FROM groups_forum_comment_comment
                WHERE parent_comment_id = ' . to_sql($comment['comment_id'], 'Number');
            $subComments = DB::rows($sql);
            if(is_array($subComments) && count($subComments)) {
                foreach($subComments as $subComment) {
                    self::delete_forum_comment_comment($subComment['comment_id'], true);
                }
            }

        	DB::execute("DELETE FROM groups_forum_comment WHERE comment_id=".$comment['comment_id']. " LIMIT 1");

            Wall::remove('group_forum_post', $comment_id, 0);

            if($update_forum)
                CGroupsTools::update_forum($comment['forum_id']);
        }
    }

    static function delete_forum_comment_comment($comment_id, $admin = false, $update_forum = true)
    {
        $comment = DB::row("SELECT cc.*, c.forum_id FROM groups_forum as m,
                groups_group as g,
                groups_forum_comment as c,
                groups_forum_comment_comment as cc
            WHERE cc.comment_id=" . to_sql($comment_id, 'Number') .
            " AND m.forum_id = c.forum_id AND g.group_id = m.group_id AND c.comment_id = cc.parent_comment_id " .
            ($admin ? "" : (" AND (g.user_id = " . guid() . " OR cc.user_id = " . guid() . " )")) .
            " LIMIT 1");
        if($comment)
        {
            OutsideImages::on_delete($comment['comment_text']);

        	DB::execute('DELETE FROM groups_forum_comment_comment
                WHERE comment_id = ' . to_sql($comment['comment_id']), 'Number');

            Wall::remove('group_forum_post_comment', $comment_id, $comment['user_id']);

            if($update_forum) {
                CGroupsTools::update_forum($comment['forum_id']);
            }
        }
    }

    static function retrieve_group_by_id($group_id)
    {
        return self::retrieve_group_for_edit_by_id($group_id, true);
    }

    static function retrieve_group_for_edit_by_id($group_id, $admin = false)
    {
        global $g_user;

    	return DB::row("SELECT e.*, c.* ".
            "FROM groups_group as e, groups_category as c ".
            "WHERE e.group_id=" . to_sql($group_id, 'Number') . " AND e.category_id = c.category_id ".
            ($admin ? "" : " AND e.user_id = " . $g_user['user_id']) .
            " LIMIT 1");
    }

    static function retrieve_forum_by_id($forum_id)
    {
        return self::retrieve_forum_for_edit_by_id($forum_id, true);
    }

    static function retrieve_forum_for_edit_by_id($forum_id, $admin = false)
    {
        global $g_user;

        return DB::row("SELECT e.* ".
            "FROM groups_forum as e ".
            "WHERE e.forum_id=" . to_sql($forum_id, 'Number') .
            ($admin ? "" : " AND e.user_id = " . $g_user['user_id']) .
            " LIMIT 1");
    }

    static function delete_group_member($group_id, $user_id, $group_need_update = true)
    {
    	global $g_user;

    	DB::execute("DELETE FROM groups_group_member WHERE group_id=".to_sql($group_id, 'Nubmer')." AND user_id=".to_sql($user_id, 'Number'));

        Wall::deleteItemForUserByItem($group_id, 'group', $user_id);

    	if($group_need_update)
    	   self::update_group($group_id);
    }

    static function create_group_member($group_id)
    {
        global $g_user;

    	self::delete_group_member($group_id, $g_user['user_id'], false);

        DB::execute("INSERT INTO groups_group_member SET group_id = " . to_sql($group_id, 'Number') .
            ", user_id = " . to_sql($g_user['user_id'], 'Number') .
            ", created_at = NOW()");

        $id = DB::insert_id();

        Wall::add('group_join', $group_id);

    	self::update_group($group_id);
    }

    static function is_group_member($group_id)
    {
        global $g_user;

    	return DB::row('SELECT * FROM groups_group_member WHERE group_id = ' . to_sql($group_id, 'Number') . " AND user_id = " . $g_user['user_id'] . " LIMIT 1") ? true : false;
    }

    static function join_group($group_id,$owner=false)
    {
        global $g_user;

    	$group = self::retrieve_group_by_id($group_id);

    	if($group && $owner && !self::is_group_member($group_id))
    	{
    		self::create_group_member($group_id);
    		return;
    	}

    	if($group &&
           !$group['group_private'] /*&&
           $group['user_id'] != $g_user['user_id']*/ &&
    	   !self::is_group_member($group_id))
       {
            self::create_group_member($group_id);
       }
    }

    static function leave_group($group_id)
    {
        global $g_user;

        $group = self::retrieve_group_by_id($group_id);
        if($group &&
           self::is_group_member($group_id))
       {
            self::delete_group_member($group_id, $g_user['user_id']);
       }
    }

    static function create_forum_comment($forum_id, $comment_text)
    {
        global $g_user;

    	$forum = DB::row("SELECT * FROM groups_forum WHERE forum_id=" . to_sql($forum_id, 'Number') . " LIMIT 1");
        if($forum)
        {
            $group = CGroupsTools::retrieve_group_by_id($forum['group_id']);
            if(($group['user_id'] == $g_user['user_id'] || CGroupsTools::is_group_member($group['group_id'])))
            {
                DB::execute("INSERT INTO groups_forum_comment SET forum_id=".$forum['forum_id'].
                                    ", user_id=".$g_user['user_id'].
                                    ", comment_text=".to_sql(CGroupsTools::filter_text_to_db($comment_text)).
                                    ", created_at = NOW()");

                $id = DB::insert_id();

                Wall::setSiteSection('group');
                Wall::setSiteSectionItemId($group['group_id']);

                Wall::add('group_forum_post', $id);

                CGroupsTools::update_forum($forum['forum_id']);
            }
        }
    }

    static function create_forum($group_id, $forum_title, $forum_description)
    {
        global $g_user;

    	if(!CGroupsTools::is_group_member($group_id))
            redirect('groups.php');

        DB::execute("INSERT INTO groups_forum SET ".
            " group_id=".to_sql($group_id, 'Number').
            ", user_id=".to_sql($g_user['user_id'], 'Number').
            ", forum_title=".to_sql($forum_title).
            ", forum_description=".to_sql($forum_description).
            ", created_at = NOW(), updated_at = NOW()");
        return DB::insert_id();
    }


    static function create_group($category_id, $group_private, $group_title, $group_description)
    {
        global $g_user;

        DB::execute("INSERT INTO groups_group SET ".
            " user_id=".to_sql($g_user['user_id'], 'Number').
            ", category_id=".to_sql($category_id, 'Number').
            ", group_private=".to_sql($group_private, 'Number').
            ", group_title=".to_sql($group_title).
            ", group_description=".to_sql($group_description).
            ", created_at = NOW(), updated_at = NOW()");
        $group_id = DB::insert_id();
        self::join_group($group_id,true);

        return $group_id;
    }
}
