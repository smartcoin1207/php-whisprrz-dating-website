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

class CMusic extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $musician_id = get_param('musician_id');
        	$musician_name = get_param('musician_name');
            $country_id = get_param('country_id');
            $musician_founded = get_param('musician_founded');
            $musician_leader = get_param('musician_leader');
            $musician_about = get_param('musician_about');
            $category_id = intval(get_param('category_id'));

            if($musician_name &&
	            $country_id &&
	            $musician_founded &&
	            $musician_leader &&
	            $musician_about &&
	            $category_id)
            {
	            $category = DB::row('SELECT * FROM music_category WHERE category_id = ' . to_sql($category_id, 'Number'));
	            if($category)
	            {
	                $country = DB::row('SELECT * FROM geo_country WHERE country_id = ' . to_sql($country_id, 'Number'));
	                if($country)
	                {
                        $musician_about = Common::filter_text_to_db($musician_about, false);
	                    if($musician_id)
	                    {
		                    if(!DB::row("SELECT * FROM music_musician WHERE musician_id = " . to_sql($musician_id, 'Number') . " AND user_id = " .$g_user['user_id'] . " LIMIT 1"))
		                        redirect('music.php');

                            DB::execute("UPDATE music_musician SET musician_name=".to_sql($musician_name).
		                        ", country_id=".to_sql($country_id, 'Number').
		                        ", musician_founded=".to_sql($musician_founded).
		                        ", musician_leader=".to_sql($musician_leader).
		                        ", musician_about=".to_sql($musician_about).
		                        ", category_id=".to_sql($category_id, 'Number').
		                        ", updated_at = NOW() WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
	                    }
	                    else
	                    {
	                        DB::execute("INSERT INTO music_musician SET musician_name=".to_sql($musician_name).
		                        ", user_id=".$g_user['user_id'].
	                            ", country_id=".to_sql($country_id, 'Number').
	                            ", musician_founded=".to_sql($musician_founded).
	                            ", musician_leader=".to_sql($musician_leader).
	                            ", musician_about=".to_sql($musician_about).
	                            ", category_id=".to_sql($category_id, 'Number').
	                            ", created_at = NOW(), updated_at = NOW()");
	                        $musician_id = DB::insert_id();

                            Wall::add('musician', $musician_id);
	                    }

	                    for($image_n = 1; $image_n <= 4; ++$image_n)
	                    {
	                        $name = "image_" . $image_n;
                            $time = DB::result('SELECT NOW()');
	                        CMusicTools::do_upload_musician_image($name, $musician_id, $time);
	                    }

	                    CMusicTools::update_musician($musician_id);

	                	redirect('music_musician_show.php?musician_id='.$musician_id);
	                }
	            }
            }
            redirect('music.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		$musician_id = get_param('musician_id');
        $musician = DB::row("SELECT m.*, c.*, cn.* ".
            "FROM music_musician as m, music_category as c, geo_country as cn ".
            "WHERE m.musician_id=" . to_sql($musician_id, 'Number') . " AND m.category_id = c.category_id AND ".
            "m.country_id = cn.country_id LIMIT 1");

        if($musician)
        {
            $html->setvar('musician_id', $musician['musician_id']);
            $html->setvar('musician_name', he($musician['musician_name']));

            $html->setvar('musician_leader', $musician['musician_leader']);
            $html->setvar('musician_about', $musician['musician_about']);

            $html->parse('edit_title');

            DB::query("SELECT * FROM music_musician_image WHERE musician_id=" . $musician['musician_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_id", $image['image_id']);
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_b.jpg");
                $html->parse("image");
                ++$n_images;
            }

            if($n_images > 0)
                $html->parse("images");

            $html->parse('edit_images');
        }
        else
        {
        	$musician_name = get_param('musician_name');
			if($musician_name)
			{
                $html->setvar('musician_id', 0);
				$html->setvar('musician_name', strcut(he(to_html($musician_name)), 24));

				$html->parse('create_title');
				$html->parse('create_images');
                $html->parse('create_images_js');
			}
			else
	            redirect('music.php');
        }

        $html->setvar("country_options", Common::listCountries($musician ? $musician['country_id'] : $g_user['country_id']));

        $settings = CMusicTools::settings();

        $musician_founded_options = '';
        $current_year = intval(date("Y", time()));
        $musician_founded = $musician ? $musician['musician_founded'] : $current_year;
        for($year = $current_year; $year != $current_year - 101; --$year)
        {
        	$musician_founded_options .= '<option value=' . $year . ' ' . (($year == $musician_founded) ? 'selected="selected"' : '') . '>';
            $musician_founded_options .= $year;
            $musician_founded_options .= '</option>';
        }
        $html->setvar("musician_founded_options", $musician_founded_options);

        $category_options = '';
        DB::query("SELECT * FROM music_category ORDER BY category_id");
        $selected_category_id = $musician ? $musician['category_id'] : $settings['category_id'];
        while($category = DB::fetch_row())
        {
            if(!$selected_category_id)
                $selected_category_id = $category['category_id'];

            $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
            $category_options .= l($category['category_title'], false, 'music_category');
            $category_options .= '</option>';
        }
        $html->setvar("category_options", $category_options);

		parent::parseBlock($html);
	}
}

$page = new CMusic("", $g['tmpl']['dir_tmpl_main'] . "music_musician_edit.html");
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
