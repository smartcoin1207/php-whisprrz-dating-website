<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CGalleryAlbums extends CHtmlList {

    var $m_on_page = 42;
    var $m_on_row = 7;
    var $userAlbums = false;

    function init()
    {
        parent::init();
        global $g_user;


        if (guid()) {
            $where = ' a.access = "public"
                OR a.user_id = ' . to_sql(guid(), 'Number') . '
                OR (a.access = "friends" AND (f1.friend_id IS NOT NULL OR f2.friend_id IS NOT NULL OR
                                f3.friend_id IS NOT NULL OR f4.friend_id IS NOT NULL)) ';

            if (guser('albums_to_see') == 'friends') {
                $where = ' a.user_id = ' . to_sql(guid(), 'Number') . '
                    OR ( (f1.friend_id IS NOT NULL OR f2.friend_id IS NOT NULL OR
                                f3.friend_id IS NOT NULL OR f4.friend_id IS NOT NULL) AND a.access != "private" )';
            }

            $query = 'FROM gallery_albums AS a
                JOIN user AS u ON a.user_id = u.user_id
                LEFT JOIN friends_requests as f1 ON ( f1.user_id=a.user_id AND f1.friend_id=a.user_id ) AND f1.accepted = 1
                LEFT JOIN friends_requests as f2 ON ( f2.user_id=a.user_id AND f2.friend_id=' . to_sql(guid(), 'Number') . ' ) AND f2.accepted = 1
                LEFT JOIN friends_requests as f3 ON ( f3.user_id=' . to_sql(guid(), 'Number') . ' AND f3.friend_id=a.user_id ) AND f3.accepted = 1
                LEFT JOIN friends_requests as f4 ON ( f4.user_id=' . to_sql(guid(), 'Number') . ' AND f4.friend_id=' . to_sql(guid(), 'Number') . ' ) AND f4.accepted = 1
                ';
        } else {
            $query = 'FROM gallery_albums AS a
                JOIN user AS u ON a.user_id = u.user_id';
        }

        $this->m_sql_count = 'SELECT COUNT(a.id) ' . $query . $this->m_sql_from_add;
        $this->m_sql = 'SELECT a.*, u.name ' . $query . $this->m_sql_from_add;
    }


    static public function getCustomWhere($where, $table = 'a.')
    {
        if ($where) {
            $where = '(' . $where .  ') AND ';
        }
        $where .= ' ' . $table . 'folder != "chat_system" ';

        return $where;
    }

    function parseBlock(&$html)
    {
        $uid = intval(get_param('user_id', 0));
        $sql = "SELECT u.*, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) AS age
            FROM user AS u
            WHERE u.user_id = " . to_sql($uid, 'Number');
        DB::query($sql);
        if ($row = DB::fetch_row()) {
            $html->setvar('user_id', $uid);
            $html->setvar('name', $row['name']);
            $html->setvar('age', $row['age']);
        }


        if ($this->userAlbums) {
            $html->parse('albums_user');
        } else {
            $html->parse('albums_all');
        }


        $params = get_params_string();
        $params = del_param("sort", $params);

        $html->setvar('params', $params);

        $sort = get_param('sort', 'date');

        $sortOptions = array('views', 'date', 'title');
        foreach ($sortOptions as $sortOption) {
            if ($sort != $sortOption) {
                $html->parse('sort_' . $sortOption);
                $html->parse('sort_' . $sortOption . '_end');
            }
        }

        parent::parseBlock($html);
    }

    function onItem(&$html, $row, $i, $last)
    {
        global $g;
        global $l;
        global $g_user;

        $html->setvar("album_name", $row['title']);
        $html->setvar("album_id", $row['id']);
        $html->setvar("album_user_id", $row['user_id']);
        $html->setvar("user_name", $row['name']);
        $html->setvar("name_prof", $row['name']);

        $thumb_href = "gallery/thumb/" . $row['user_id'] . "/" . $row['folder'] . "/" . $row['thumb'];
        $html->setvar("gallery_thumb_path", $thumb_href);

// SHOW NOTHING BLOCKS

        if ($i == $last) {
            $add = ($this->m_on_page - $last) % $this->m_on_row;
            if ($add > 0) {
                for ($n = 0; $n < $add; $n++)
                    $html->parse("no_albums", true);
            }
        }

// SHOW NOTHING BLOCKS

        $html->parse("albums", true);
    }

}