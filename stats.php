<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

class CStats extends CHtmlBlock {

    function parseBlock(&$html) {
        for ($i = 0; $i <= 11; $i++) {
            $html->setvar('comments_' . $i, 1);
            $html->setvar('shared_' . $i, 1);
            $html->setvar('posts_' . $i, 1);
        }
        DB::query('SELECT * FROM `wall_stats` WHERE `user_id`=' . to_sql(guid(), 'Number'));
        while ($stat = DB::fetch_row()) {


            //$month = explode('.', $stat['date']);
            //$month = $month[1] - 1;
            // В БД изменён тип поля на date, был varchar надо explode('-', $stat['date']);
            $month = '';
            if ($month == $i) {
                $html->setvar('comments_' . $month, $stat['comments']);
                $html->setvar('shared_' . $month, $stat['shared_posts']);
                $html->setvar('posts_' . $month, $stat['wall_posts']);
            }
        }
        $M = DB::result("SELECT COUNT(`gender`) FROM friends_requests  LEFT JOIN user ON (user.user_id = friends_requests.user_id AND friends_requests.friend_id = ".guid().") OR (user.user_id = friends_requests.friend_id AND friends_requests.user_id = ".guid().") WHERE friends_requests.accepted = 1 AND user.gender = 'M' ");
        $F = DB::result("SELECT COUNT(`gender`) FROM friends_requests  LEFT JOIN user ON (user.user_id = friends_requests.user_id AND friends_requests.friend_id = ".guid().") OR (user.user_id = friends_requests.friend_id AND friends_requests.user_id = ".guid().") WHERE friends_requests.accepted = 1 AND user.gender = 'F' ");
        $result=DB::result("SELECT COUNT(`user_id`) FROM friends_requests WHERE (user_id=".guid()." OR friend_id=".guid().") AND accepted=1 ORDER BY created_at DESC LIMIT 0,10");
        $html->setvar('count_m',$M/$result);
        $html->setvar('count_f',$F/$result);
        DB::query('SELECT * FROM var_status');
        $row = array();
        while ($status = DB::fetch_row()) {
            $row[$status['title']] = DB::result("SELECT COUNT(*) FROM friends_requests  LEFT JOIN userinfo ON (userinfo.user_id = friends_requests.user_id AND friends_requests.friend_id = ".guid().") OR (userinfo.user_id = friends_requests.friend_id AND friends_requests.user_id = ".guid().") WHERE friends_requests.accepted = 1 and userinfo.status =".$status['id'],0,1);
        }
        #var_dump($row);exit;
        foreach($row as $item) {
            if(!empty($item))
                $html->setvar('status_item',$item/$result);
            $html->parse('status');
        }
        parent::parseBlock($html);
    }

}

$page = new CStats("", $g['tmpl']['dir_tmpl_main'] . "stats.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);
include("./_include/core/main_close.php");
?>