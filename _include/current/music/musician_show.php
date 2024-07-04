<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicMusicianShow extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $song_id = intval(get_param('song_id', 0));
        $song = null;
        if($song_id)
            $song = DB::row("SELECT * FROM music_song WHERE song_id = " . to_sql($song_id, 'Number') . " LIMIT 1");

        if(!$song)
            $musician_id = get_param('musician_id');
        else
            $musician_id = $song['musician_id'];

        $musician = DB::row("SELECT m.*, c.*, cn.* ".
            "FROM music_musician as m, music_category as c, geo_country as cn ".
            "WHERE m.musician_id=" . to_sql($musician_id, 'Number') . " AND m.category_id = c.category_id AND ".
            "m.country_id = cn.country_id LIMIT 1");
        if(!$musician)
        {
        	$html->setvar('musician_name', strcut(to_html(get_param('musician_name', 'undefined')), 32));
        	//$html->setvar('musician_about', l('music_musician_about_empty'));

        	$html->parse('musician_no_info', true);

        	$html->parse("no_image");
        }
        else
        {
            $html->setvar('musician_id', $musician['musician_id']);
        	$html->setvar('musician_name', strcut(to_html($musician['musician_name']), 32));
        	$html->setvar('musician_name_full', to_html(he($musician['musician_name'])));

            $html->setvar('country_title', strcut(to_html($musician['country_title']), 14));
            $html->setvar('country_title_full', to_html($musician['country_title']));
            $html->setvar('country_id', $musician['country_id']);

            $category_title = l($musician['category_title'], false, 'music_category');
            $html->setvar('category_title', strcut(to_html($category_title), 12));
            $html->setvar('category_title_full', to_html($category_title));
            $html->setvar('category_id', $musician['category_id']);

            $html->setvar('musician_founded', to_html($musician['musician_founded']));
            $html->setvar('musician_leader', strcut(to_html($musician['musician_leader']), 32));

            $about = to_html(trim(he($musician['musician_about'])), true, false);
            $about_short = strcut($about, 290);
            $html->setvar('musician_about', $about_short);
            $html->setvar('musician_about_full', $about);
            $html->setvar('about_collapse', ($about == $about_short) ? 0 : 1);

        	$html->parse('musician_upload_song_button', true);
            $html->parse('musician_info', true);

        	if($g_user['user_id'] == $musician['user_id']) {
                $html->parse('musician_edit', false);
                $html->parse('musician_functions', true);
            }

            $image = DB::row("SELECT * FROM music_musician_image WHERE musician_id=" . $musician['musician_id'] . " ORDER BY image_id DESC LIMIT 1");
            if($image)
            {
                $html->setvar("image_thumbnail_b", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th_b.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_b.jpg");
                $html->parse("image");
            }
            else
            {
            	$html->parse("no_image");
            }
        }

		parent::parseBlock($html);
	}
}

