<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/music/custom_head.php");
require_once("./_include/current/music/header.php");
require_once("./_include/current/music/sidebar.php");
require_once("./_include/current/music/musician_show.php");
require_once("./_include/current/music/musician_image_list.php");
require_once("./_include/current/music/tools.php");

class CMusic extends CHtmlBlock {

    function action() {
        global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        $song_id = intval(get_param('song_id'));

        if ($cmd == 'save') {
            $musician_id = intval(get_param('musician_id'));
			$song_title = strip_tags(get_param('song_title'));

            $song_about = get_param('song_about');
            $song_year = get_param('song_year');
            $upload_name = get_param('upload_name');

            if ($musician_id && $song_title &&  $song_year && $upload_name) {
                $song = null;
                $length = 0;
                if(custom_file_exists($g['path']['dir_files'] . "music/tmp/" . $upload_name . ".mp3")) {
                    $length = Mp3::getDuration($g['path']['dir_files'] . "music/tmp/" . $upload_name . ".mp3");
                    // error while reading ID3 info - go to home music page
                    if ($length <= 0) {
                        redirect("music.php");
                    }
                }
                $song_about = Common::filter_text_to_db($song_about, false);
                if ($song_id) {
                    $song = DB::row("SELECT s.*, m.* FROM music_song as s, music_musician as m WHERE song_id = " . to_sql($song_id, 'Number') .
                                    " AND s.musician_id = m.musician_id " .
                                    " AND (s.user_id = " . $g_user['user_id'] . " OR m.user_id = " . $g_user['user_id'] . ") LIMIT 1");
                    if (!$song)
                        redirect('music.php');
                    if ($length <= 0) {
                        $length = $song['song_length'];
                    }
                    DB::execute("UPDATE music_song SET song_title=" . to_sql($song_title) .
                            ", musician_id=" . to_sql($musician_id, 'Number') .
                            ", song_about=" . to_sql($song_about) .
                            ", song_year=" . to_sql($song_year) .
                            ", song_length=" . to_sql(ceil($length), 'Number') .
                            ", updated_at = NOW() WHERE song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
                } else {
                    DB::execute("INSERT INTO music_song SET song_title=" . to_sql($song_title) .
                            ", user_id=" . $g_user['user_id'] .
                            ", musician_id=" . to_sql($musician_id, 'Number') .
                            ", song_about=" . to_sql($song_about) .
                            ", song_year=" . to_sql($song_year) .
                            ", song_length=" . to_sql(ceil($length), 'Number') .
                            ", created_at = NOW(), updated_at = NOW()");
                    $song_id = DB::insert_id();

                    Wall::add('music', $song_id);
                }
                if(CMusicTools::move_uploaded_file($upload_name, $song_id) === false) {
                    CMusicTools::delete_song($song_id);
                    redirect('music.php');
                }
                CStatsTools::count('mp3_uploaded');
                CMusicTools::cleanup_upload_folder();

                if ($song)
                    redirect('music_song_show.php?song_id=' . $song_id);
                else
                    redirect('music_song_add_photos.php?song_id=' . $song_id);
            }
            redirect('music.php');
        }

        if ($cmd == 'delete') {
            CMusicTools::delete_song($song_id);
            redirect('music.php');
        }
    }

    function parseBlock(&$html) {
        global $g_user;
        global $l;
        global $g;

        $maxFileSize = Common::getOption('music_mp3_file_size_limit_mbs');
        $html->setvar('upload_name', CMusicTools::generate_upload_name());
        $html->setvar('mp3_file_size_limit', mb_to_bytes($maxFileSize));
        $html->setvar('mp3_max_chunk_size', Common::getOption('music_mp3_max_chunk_size_kb', 'upload_files') * 1024);
        $html->setvar('max_file_size_limit', lSetVars('max_file_size', array('size'=>$maxFileSize)));

        $song_id = intval(get_param('song_id', 0));
        $song = null;
        $html->setvar('song_id', $song_id);

        if ($song_id) {
            $song = DB::row("SELECT * FROM music_song WHERE song_id = " . to_sql($song_id, 'Number') . " LIMIT 1");
            $html->parse('edit_js');
        } else {
            $html->parse('add_js');
        }

        if (!$song)
            $musician_id = get_param('musician_id');
        else
            $musician_id = $song['musician_id'];

        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");

        if ($musician) {
            $html->setvar('musician_id', $musician['musician_id']);
            $html->setvar('musician_name', $musician['musician_name']);

            if ($song) {

                $html->setvar('song_title', he($song['song_title']));
                $html->setvar('song_about', $song['song_about']);

                $html->parse("title_edit");
                $html->parse("submit_edit");

                DB::query("SELECT * FROM music_song_image WHERE song_id=" . $song['song_id'] . " ORDER BY created_at ASC");
                $n_images = 0;
                while ($image = DB::fetch_row()) {
                    $html->setvar("image_id", $image['image_id']);
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_b.jpg");
                    $html->parse("image");
                    ++$n_images;
                }

                if ($n_images > 0)
                    $html->parse("images");
            }
            else {
                $html->parse("title_create");
                $html->parse("submit_create");

                if (trim($song['song_title']) == '') {
                    $html->setvar('song_title', l('Untitled'));
                }
            }

            $song_year_options = '';
            $current_year = intval(date("Y", time()));
            $song_year = $song ? $song['song_year'] : $current_year;
            for ($year = $current_year; $year != $current_year - 101; --$year) {
                $song_year_options .= '<option value=' . $year . ' ' . (($year == $song_year) ? 'selected="selected"' : '') . '>';
                $song_year_options .= $year;
                $song_year_options .= '</option>';
            }
            $html->setvar("song_year_options", $song_year_options);
        }
        else
            redirect('music.php');

        parent::parseBlock($html);
    }

}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_song_edit.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$music_custom_head = new CMusicCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_music_custom_head.html");
$header->add($music_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$music_header = new CMusicHeader("music_header", $g['tmpl']['dir_tmpl_main'] . "_music_header.html");
$page->add($music_header);
$music_sidebar = new CMusicSidebar("music_sidebar", $g['tmpl']['dir_tmpl_main'] . "_music_sidebar.html");
$page->add($music_sidebar);
$music_musician_show = new CMusicMusicianShow("music_musician_show", $g['tmpl']['dir_tmpl_main'] . "_music_musician_show.html");
$page->add($music_musician_show);
$music_musician_image_list = new CMusicMusicianImageList("music_musician_image_list", $g['tmpl']['dir_tmpl_main'] . "_music_musician_image_list.html");
$music_musician_show->add($music_musician_image_list);

include("./_include/core/main_close.php");
