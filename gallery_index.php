<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

payment_check('gallery_view');
if (isset($g['options']['gallery']) and $g['options']['gallery'] == "N") redirect('home.php');

class CGallery_Index extends CHtmlBlock
{
    function parseAlbums(&$html, $block, $sql, $order)
    {
        $itemsOnRow = 7;
        $limit = 28;
        $sql = $sql . ' ORDER BY ' . $order . ' LIMIT 0,' . $limit;

        DB::query($sql);
        $albums = DB::num_rows();
        while ($row = DB::fetch_row()) {

            $html->setvar("album_name", strip_tags($row['title']));
            $html->setvar("album_id", $row['id']);
            $html->setvar("album_user_id", $row['user_id']);
            $html->setvar("user_name", $row['name']);

            $thumb_href="gallery/thumb/".$row['user_id']."/".$row['folder']."/".$row['thumb'];
            $html->setvar("gallery_thumb_path", $thumb_href);

            $html->parse("albums_" . $block, true);
        }

		$add = ($limit - $albums) % $itemsOnRow;
		if($add > 0) {
            for($n=0; $n < $add; $n++) {
                $html->parse('no_albums_' . $block, true);
            }
		}
    }

	function parseBlock(&$html)
	{
        global $g_user;
        if (guid()) {

            $where = ' (a.access = "public"
                OR a.user_id = '. to_sql(guid(), 'Number') . '
                OR (a.access = "friends" AND (f1.friend_id IS NOT NULL OR f2.friend_id IS NOT NULL OR
                                f3.friend_id IS NOT NULL OR f4.friend_id IS NOT NULL)) )';

            if(guser('albums_to_see') == 'friends') {
                $where = ' (a.user_id = ' . to_sql(guid(), 'Number') . '
                    OR ( (f1.friend_id IS NOT NULL OR f2.friend_id IS NOT NULL OR
                                f3.friend_id IS NOT NULL OR f4.friend_id IS NOT NULL) AND a.access != "private" ))';
            }

            $where_orientation = " AND NOT (a.user_id!=".guid()." AND ((". $g_user['orientation'] ."=5 AND u.set_photo_couples=2 ) OR ( ". $g_user['orientation'] ."=1 AND u.set_photo_males=2 ) OR ( ". $g_user['orientation'] ."=2 AND u.set_photo_females=2 ) OR ( ". $g_user['orientation'] ."=6 AND u.set_photo_transgender=2 ) OR ( ". $g_user['orientation'] ."=7 AND u.set_photo_nonbinary=2 ))) ";
            $where .= $where_orientation;
            $where = CGalleryAlbums::getCustomWhere($where);

            $sql = 'SELECT a.*, u.name
                FROM gallery_albums AS a
                JOIN user AS u ON a.user_id = u.user_id
                LEFT JOIN friends_requests as f1 ON ( f1.user_id=a.user_id AND f1.friend_id=a.user_id ) AND f1.accepted = 1
                LEFT JOIN friends_requests as f2 ON ( f2.user_id=a.user_id AND f2.friend_id=' . to_sql(guid(), 'Number') . ' ) AND f2.accepted = 1
                LEFT JOIN friends_requests as f3 ON ( f3.user_id=' . to_sql(guid(), 'Number') . ' AND f3.friend_id=a.user_id ) AND f3.accepted = 1
                LEFT JOIN friends_requests as f4 ON ( f4.user_id=' . to_sql(guid(), 'Number') . ' AND f4.friend_id=' . to_sql(guid(), 'Number') . ' ) AND f4.accepted = 1
                WHERE ' . $where;

        } else {
            $where = 'a.access = "public"';
            $where = CGalleryAlbums::getCustomWhere($where);

            $sql = 'SELECT a.*, u.name
                FROM gallery_albums AS a
                JOIN user AS u ON a.user_id = u.user_id
                WHERE ' . $where;;
        }

        $block = 'new';
        $order = ' a.date DESC ';
        $this->parseAlbums($html, $block, $sql, $order);

        $block = 'popular';
        $order = ' a.views DESC ';
        $this->parseAlbums($html, $block, $sql, $order);

		parent::parseBlock($html);
	}
}


$page = new CGallery_Index("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_index.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>