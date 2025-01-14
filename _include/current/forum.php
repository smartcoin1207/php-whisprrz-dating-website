<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once(dirname(__FILE__) . "/video_hosts.php");
require_once(dirname(__FILE__) . "/outside_images.php");

class CForum
{
    static $m_settings = null;

    static $smileys = array(
	       ':D' => 'img/forum/smilies/biggrin.gif',
	       ':o' => 'img/forum/smilies/redface.gif',
	       ':)' => 'img/forum/smilies/smile.gif',
	       ':(' => 'img/forum/smilies/frown.gif',
	       ':confused:' => 'img/forum/smilies/confused.gif',
	       ':mad:' => 'img/forum/smilies/mad.gif',
	       ':p' => 'img/forum/smilies/tongue.gif',
	       ';)' => 'img/forum/smilies/wink.gif',
	       ':rolleyes:' => 'img/forum/smilies/rolleyes.gif',
	       ':cool:' => 'img/forum/smilies/cool.gif',
	       ':eek:' => 'img/forum/smilies/eek.gif',
	   );

	static function make_smiley($text)
	{
		global $g;

		foreach(self::$smileys as $smiley => $image)
		{
			$text = str_replace($smiley, '<img src="' . $g['tmpl']['url_tmpl_main'] . $image . '" />', $text);
		}

		return $text;
	}

    static function user_update_n_messages($user_id)
    {
        $n_messages = CForumTopic::count_by_user_id($user_id);
        $n_messages += CForumMessage::count_by_user_id($user_id);

        DB::execute("
            UPDATE `user` SET
            forum_n_messages = " . to_sql($n_messages, 'Number') . "
            WHERE user_id = '" . to_sql($user_id, 'Number') . "'
            ");
    }

    static function settings()
    {
        global $g_user;

        if(!self::$m_settings)
        {
            self::$m_settings = DB::row("SELECT * FROM forum_setting WHERE user_id = " . $g_user['user_id'] . " LIMIT 1");
            if(!self::$m_settings)
            {
                self::$m_settings = array('sort_by' => 'last_post', 'sort_by_dir' => 'desc');
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
            DB::execute("UPDATE forum_setting SET sort_by = " . to_sql(self::$m_settings['sort_by']) .
               ", sort_by_dir = " . to_sql(self::$m_settings['sort_by_dir']) .
               " WHERE user_id = " . $g_user['user_id']);
        }
        else
        {
            DB::execute("INSERT INTO forum_setting SET sort_by = " . to_sql(self::$m_settings['sort_by']) .
               ", sort_by_dir = " . to_sql(self::$m_settings['sort_by_dir']) .
               ", user_id = " . $g_user['user_id']);
        }
    }
}

class CForumCategory
{
    static function create_new($title)
    {
        $sort_rank = DB::result("SELECT MAX(sort_rank) FROM forum_category");
        $sort_rank = $sort_rank ? ($sort_rank + 1) : 1;
    	DB::execute("INSERT INTO forum_category SET " .
           "title = " . to_sql($title, "Text") . ", " .
           "sort_rank = $sort_rank, ".
           "created_at = NOW(), ".
           "updated_at = NOW() ".
           "");

        $id = DB::insert_id();

        return $id;
    }

    static function save($data)
    {
    	DB::execute("UPDATE forum_category SET " .
           "title = " . to_sql($data['title'], "Text") . ", " .
    	   "sort_rank = " . to_sql($data['sort_rank'], "Text") . ", " .
           "updated_at = NOW() ".
           "WHERE id = " . to_sql($data['id'], "Number") . " LIMIT 1");
    }

    static function order_change($id, $up)
    {
    	$item = self::retrieve_by_id($id);
    	if($item)
    	{
            $swap_item = DB::row("SELECT * FROM `forum_category` WHERE sort_rank " . ($up ? '<' : '>') . " " . $item['sort_rank'] . " ORDER BY sort_rank " . ($up ? "DESC" : "ASC") . " LIMIT 1;");
            if($swap_item)
            {
                $rank = $item['sort_rank'];
                $item['sort_rank'] = $swap_item['sort_rank'];
                $swap_item['sort_rank'] = $rank;

                self::save($item);
                self::save($swap_item);
            }
    	}
    }

    static function delete_by_id($id)
    {
        $category = self::retrieve_by_id($id);

        $forums = CForumForum::select_by_category_id($id);

        foreach($forums as $forum)
            CForumForum::delete_by_id($forum['id']);

        DB::execute("DELETE FROM `forum_category` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");
    }

	static function retrieve_all()
	{
	   	DB::query("SELECT * FROM `forum_category` ORDER BY sort_rank, id;");

	   	$results = array();

	   	while($row = DB::fetch_row())
	   	{
	   		$results[] = $row;
	   	}

        return $results;
	}

    static function retrieve_by_id($id)
    {
        DB::query("SELECT * FROM `forum_category` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");

        if($row = DB::fetch_row())
        {
            return $row;
        }

        return null;
    }
}

class CForumForum
{
    static function create_new($category_id, $title, $description)
    {
        $sort_rank = DB::result("SELECT MAX(sort_rank) FROM forum_forum");
        $sort_rank = $sort_rank ? ($sort_rank + 1) : 1;

        DB::execute("INSERT INTO forum_forum SET " .
           "category_id = " . to_sql($category_id, "Number") . ", " .
           "title = " . to_sql($title, "Text") . ", " .
           "description = " . to_sql($description, "Text") . ", " .
           "sort_rank = $sort_rank, ".
           "n_topics = 0, ".
           "n_messages = 0, ".
           "created_at = NOW(), ".
           "updated_at = NOW() ".
           "");

        $id = DB::insert_id();

        return $id;
    }

    static function save($data)
    {
        DB::execute("UPDATE forum_forum SET " .
            "title = " . to_sql($data['title'], "Text") . ", " .
            "description = " . to_sql($data['description'], "Text") . ", " .
            "sort_rank = " . to_sql($data['sort_rank'], "Text") . ", " .
            "updated_at = NOW() ".
            "WHERE id = " . to_sql($data['id'], "Number") . " LIMIT 1");
    }

    static function delete_by_id($id)
    {
        $forum = self::retrieve_by_id($id);

        $topics = CForumTopic::select_by_forum_id($id);

        foreach($topics as $topic)
            CForumTopic::delete_by_id($topic['id']);

        DB::execute("DELETE FROM `forum_forum` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");
    }

    static function order_change($id, $up)
    {
        $item = self::retrieve_by_id($id);
        if($item)
        {
            $swap_item = DB::row("SELECT * FROM `forum_forum` WHERE sort_rank " . ($up ? '<' : '>') . " " . $item['sort_rank'] . " ORDER BY sort_rank " . ($up ? "DESC" : "ASC") . " LIMIT 1;");
            if($swap_item)
            {
                $rank = $item['sort_rank'];
                $item['sort_rank'] = $swap_item['sort_rank'];
                $swap_item['sort_rank'] = $rank;

                self::save($item);
                self::save($swap_item);
            }
        }
    }

	static function select_by_category_id($category_id)
    {
        DB::query("SELECT * FROM `forum_forum` WHERE `category_id` = " . to_sql($category_id, 'Number') . " ORDER BY sort_rank, id;");

        $results = array();

        while($row = DB::fetch_row())
        {
            $results[] = $row;
        }

        return $results;
    }

    static function retrieve_by_id($id)
    {
        DB::query("SELECT * FROM `forum_forum` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");

        if($row = DB::fetch_row())
        {
            return $row;
        }

        return null;
    }

    static function update_n_topics($id)
    {
    	$n_topics = CForumTopic::count_by_forum_id($id);

    	DB::execute("
            UPDATE `forum_forum` SET
            n_topics = " . to_sql($n_topics, 'Number') . ",
            updated_at = NOW()
            WHERE id = '" . to_sql($id, 'Number') . "'
            ");
    }

    static function update_n_messages($id)
    {
        $n_messages = CForumTopic::sum_n_messages_by_forum_id($id);

        DB::execute("
            UPDATE `forum_forum` SET
            n_messages = " . to_sql($n_messages, 'Number') . ",
            updated_at = NOW()
            WHERE id = '" . to_sql($id, 'Number') . "'
            ");
    }
}

class CForumTopic
{
    static $outside_image_sizes = array(
        array(
            'width' => 495,
            'height' => 370,
            'allow_smaller' => true,
            'file_postfix' => 'th',
            ),
    );

    static function retrieve_by_id($id)
    {
        DB::query("SELECT * FROM `forum_topic` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");

        if($row = DB::fetch_row())
        {
            return $row;
        }

        return null;
    }

    static function select_by_forum_id($forum_id, $limit = 0, $skip = 0, $order_by = 'thread', $order_by_dir = '')
    {
        $order_query = "ORDER BY updated_at DESC";

        switch($order_by)
        {
        	case 'thread':
                $order_query = "ORDER BY title " . $order_by_dir;
        		break;
            case 'replies':
                $order_query = "ORDER BY n_messages " . $order_by_dir;
                break;
            case 'last_post':
                $order_query = "ORDER BY updated_at " . $order_by_dir;
                break;
            case 'views':
                $order_query = "ORDER BY n_views " . $order_by_dir;
                break;
        }

    	DB::query("SELECT * FROM `forum_topic` WHERE `forum_id` = " . to_sql($forum_id, 'Number') . " " . $order_query .
            ($limit ? (" LIMIT " . $skip . ", " . $limit) : ""));

        $results = array();

        while($row = DB::fetch_row())
        {
            $results[] = $row;
        }

        return $results;
    }

    static function count_by_forum_id($forum_id)
    {
    	return DB::result("SELECT COUNT(id) FROM `forum_topic` WHERE `forum_id` = " . to_sql($forum_id, 'Number'));
    }

    static function count_by_user_id($user_id)
    {
        return DB::result("SELECT COUNT(id) FROM `forum_topic` WHERE `user_id` = " . to_sql($user_id, 'Number'));
    }

    static function sum_n_messages_by_forum_id($forum_id)
    {
        return DB::result("SELECT SUM(n_messages) FROM `forum_topic` WHERE `forum_id` = " . to_sql($forum_id, 'Number'));
    }

	static function retrieve_last_updated_topic_by_forum_id($forum_id)
    {
        DB::query("SELECT * FROM `forum_topic` WHERE `forum_id` = " . to_sql($forum_id, 'Number') . " ORDER BY updated_at DESC LIMIT 1;");

        if($row = DB::fetch_row())
        {
            return $row;
        }

        return null;
    }

    static function create_new($forum_id, $user_id, $title,  $message)
    {

    	DB::execute("INSERT INTO forum_topic SET " .
    	   "forum_id = " . to_sql($forum_id, "Number") . ", " .
    	   "user_id = " . to_sql($user_id, "Number") . ", " .
    	   "title = " . to_sql(strip_tags($title,"<b><i><u><center><span>"), "Text") . ", " .
    	   "message = " . to_sql(CForumMessage::filter_text_to_db($message), "Text") . ", " .
    	   "n_messages = 0, ".
    	   "n_views = 0, ".
    	   "created_at = NOW(), ".
    	   "updated_at = NOW() ".
    	   "");

    	$id = DB::insert_id();

        CForumForum::update_n_topics($forum_id);
        CForum::user_update_n_messages($user_id);

        Wall::setSiteSectionItemId($id);
        Wall::add('forum_thread', $id);

        return $id;
    }

    static function delete_by_id($id)
    {
        $topic = self::retrieve_by_id($id);

        $messages = CForumMessage::select_by_topic_id($id);

        foreach($messages as $message)
            CForumMessage::delete_by_id($message['id']);

        OutsideImages::on_delete($topic['message']);
        DB::execute("DELETE FROM `forum_topic` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");

        Wall::removeBySiteSection('forum', $id);

        CForumForum::update_n_topics($topic['forum_id']);
        CForum::user_update_n_messages($topic['user_id']);
    }

    static function update_n_messages($id)
    {
        $n_messages = CForumMessage::count_by_topic_id($id);

        $sql = "UPDATE `forum_topic` SET
            n_messages = " . to_sql($n_messages, 'Number') . ",
            n_views = n_views,
            updated_at = NOW()
            WHERE id = '" . to_sql($id, 'Number') . "'
            ";
        DB::execute($sql);

        $topic = self::retrieve_by_id($id);
        CForumForum::update_n_messages($topic['forum_id']);
    }

    static function increment_n_views($id)
    {
        DB::execute("
            UPDATE `forum_topic` SET
            n_views = n_views + 1
            WHERE id = '" . to_sql($id, 'Number') . "'
            ");
    }
}

class CForumMessage
{

    static $outside_image_sizes = array(
        array(
            'width' => 495,
            'height' => 370,
            'allow_smaller' => true,
            'file_postfix' => 'th',
            ),
    );

    const ALLOWTAGS = '<b><i><u><s><strike><strong><em>';
    const VIDEOSTARTTAG = '<div class="forum_video">';
    const VIDEOENDTAG = '</div><br/>';
    static $videoWidth = 495;


    static public function stags($str)
    {
        return strip_tags_attributes($str, self::ALLOWTAGS);
    }

    static function filter_text_to_db($v, $parse_media = true, $old_text = null)
    {
        if($parse_media)
        {
           $v = VideoHosts::textUrlToVideoCode($v);
           $v = VideoHosts::filterToDb($v);
           $v = OutsideImages::filter_to_db($v, $old_text);
        }
        //$v = str_replace("\r\n", "\n", $v);
        //$v = str_replace("\r", "\n", $v);
        $v = self::stags($v);
        //$v = htmlspecialchars($v, ENT_QUOTES);
        $v = trim($v);
        return $v;
    }

    static function filter_text_to_html($text, $parse_media = true, $startTag = self::VIDEOSTARTTAG, $endTag = self::VIDEOENDTAG)
    {
        $text = self::_filterLinksTagsToHtml($text);
        if($parse_media)
        {
            $text = VideoHosts::filterFromDb($text, $startTag, $endTag, self::$videoWidth);
            $text = OutsideImages::filter_to_html($text, $startTag, $endTag, "lightbox");
        }
        $text = self::_filterRemoveUnusedTags($text);
        $text= preg_replace("/\n+/si", "\n", $text);
        $text = nl2br(trim($text));
        return $text;
    }


    static protected function _filterLinksTagsToHtml($text)
    {

	return Common::parseLinks($text);

	/*
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

    static protected function _filterRemoveUnusedTags($text)
    {
        $grabs = Common::grabsTags($text);
        //$grabs = grabs($text, '{', '}', true);
        foreach ($grabs as $gr) {
            $text = str_replace($gr, "", $text);
        }
        return $text;
    }

    static function retrieve_by_id($id)
    {
        DB::query("SELECT * FROM `forum_message` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");

        if($row = DB::fetch_row())
        {
            return $row;
        }

        return null;
    }

    static function delete_by_id($id)
    {
        $message = self::retrieve_by_id($id);
        OutsideImages::on_delete($message['message']);
    	DB::execute("DELETE FROM `forum_message` WHERE `id` = " . to_sql($id, 'Number') . " LIMIT 1;");
    	CForumTopic::update_n_messages($message['topic_id']);
        //CForum::user_update_n_messages($message['user_id']);
        Wall::remove('forum_post', $id, 0);
    }

	static function retrieve_last_updated_message_by_topic_id($topic_id)
    {
        DB::query("SELECT * FROM `forum_message` WHERE `topic_id` = " . to_sql($topic_id, 'Number') . " ORDER BY updated_at DESC LIMIT 1;");

        if($row = DB::fetch_row())
        {
            return $row;
        }

        return null;
    }

    static function select_by_topic_id($topic_id, $limit = 0, $skip = 0)
    {
        DB::query("SELECT * FROM `forum_message` WHERE `topic_id` = " . to_sql($topic_id, 'Number') . " ORDER BY id" .
            ($limit ? (" LIMIT " . $skip . ", " . $limit) : ""));

        $results = array();

        while($row = DB::fetch_row())
        {
            $results[] = $row;
        }

        return $results;
    }

    static function create_new($topic_id, $user_id, $title, $message)
    {

        DB::execute("INSERT INTO forum_message SET " .
           "topic_id = " . to_sql($topic_id, "Number") . ", " .
           "user_id = " . to_sql($user_id, "Number") . ", " .
           "title = " . to_sql($title, "Text") . ", " .
           "message = " . to_sql(self::filter_text_to_db($message), "Text") . ", " .
           "created_at = NOW(), ".
           "updated_at = NOW() ".
           "");

        $id = DB::insert_id();

        Wall::setSiteSectionItemId($topic_id);
        Wall::add('forum_post', $id);

        CForumTopic::update_n_messages($topic_id);
        CForum::user_update_n_messages($user_id);

        return $id;
    }

    static function count_by_topic_id($topic_id)
    {
        return DB::result("SELECT COUNT(id) FROM `forum_message` WHERE `topic_id` = " . to_sql($topic_id, 'Number'));
    }

    static function count_by_user_id($user_id)
    {
        return DB::result("SELECT COUNT(id) FROM `forum_message` WHERE `user_id` = " . to_sql($user_id, 'Number'));
    }

}