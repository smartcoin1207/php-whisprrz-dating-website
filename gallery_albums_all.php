<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

payment_check('gallery_view');
if (isset($g['options']['gallery']) and $g['options']['gallery'] == "N") Common::toHomePage();

$page = new CGalleryAlbums("gallery_albums_all", $g['tmpl']['dir_tmpl_main'] . "gallery_albums_all.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$where = ' a.access = "public" ';

if(guid()) {
    if(guser('albums_to_see') == 'friends') {
        $where = ' (a.user_id = ' . to_sql(guid(), 'Number') . '
            OR ( (f1.friend_id IS NOT NULL OR f2.friend_id IS NOT NULL OR
                                f3.friend_id IS NOT NULL OR f4.friend_id IS NOT NULL) AND a.access != "private" ))';
    } else {
        $where = ' (a.access = "public"
            OR a.user_id = '. to_sql(guid(), 'Number') . '
            OR (a.access = "friends" AND (f1.friend_id IS NOT NULL OR f2.friend_id IS NOT NULL OR
                                f3.friend_id IS NOT NULL OR f4.friend_id IS NOT NULL)))';
    }

    $where_orientation = " AND NOT (a.user_id!=".guid()." AND ((". $g_user['orientation'] ."=5 AND u.set_photo_couples=2 ) OR ( ". $g_user['orientation'] ."=1 AND u.set_photo_males=2 ) OR ( ". $g_user['orientation'] ."=2 AND u.set_photo_females=2 ) OR ( ". $g_user['orientation'] ."=6 AND u.set_photo_transgender=2 ) OR ( ". $g_user['orientation'] ."=7 AND u.set_photo_nonbinary=2 ))) ";
    $where .= $where_orientation;
}

$where = CGalleryAlbums::getCustomWhere($where);

//$page->m_debug = 'Y';
$page->m_sql_where = $where;

$sort_order = get_param('sort', 'date');
if($sort_order == 'views') {
	$page->m_sql_order = 'a.views DESC';
} elseif($sort_order == 'title') {
	$page->m_sql_order = 'a.title ASC';
} else {
	$page->m_sql_order = 'a.date DESC';
}

include("./_include/core/main_close.php");

?>