<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class Chotdateshotdateshow extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $hotdate_id = get_param('hotdate_id');

        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
        if($hotdate)
        {
            $guests = ChotdatesTools::getGuestUsers($hotdate_id);

            $is_guest = false;
            
            foreach ($guests as $key => $guest) {
                if($guest['user_id'] == $g_user['user_id']) {
                    $is_guest = true;
                    break;
                }
            }

        	$title_length = 32;

            if(!$hotdate['hotdate_private'] && ChotdatesTools::is_hotdate_finished($hotdate))
            {
            	$title_length = 16;
            	$html->parse('hotdate_finished');
            }

            $sql_hotdate = "SELECT * FROM user WHERE user_id = " . to_sql($hotdate['user_id']);
            $hotdate_user = DB::row($sql_hotdate);
            $name_seo = $hotdate_user['name_seo'];
            $username = $hotdate_user['name'];

            $photo_url = User::getPhotoDefault($hotdate['user_id']);
            $html->setvar('user_name', $username);
            $html->setvar('name_seo', $name_seo);
            $html->setvar('photo', $photo_url);

            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), $title_length));
            $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));
            $html->setvar('category_title', strcut(to_html(l($hotdate['category_title'], false, 'hotdates_category')), 8));
            $html->setvar('category_title_full', to_html(l($hotdate['category_title'], false, 'hotdates_category')));
            $html->setvar('category_id', $hotdate['category_id']);
            $html->setvar('hotdate_n_comments', $hotdate['hotdate_n_comments']);
            $html->setvar('hotdate_n_guests', $hotdate['hotdate_n_guests']);
	        $html->setvar('hotdate_date', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_date')));
	        $html->setvar('hotdate_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_time')));
            $html->setvar('hotdate_place', hard_trim(to_html($hotdate['hotdate_place']), 25));
            $html->setvar('hotdate_place_full', to_html($hotdate['hotdate_place']));
            $html->setvar('city_title', strcut(to_html($hotdate['city_title']), 13));
            $html->setvar('city_title_full', to_html($hotdate['city_title']));
            $html->setvar('hotdate_address', hard_trim(to_html($hotdate['hotdate_address']), 25));
            $html->setvar('hotdate_address_full', to_html($hotdate['hotdate_address']));

	    if($hotdate['hotdate_site']!="")
	    {
				$site_name = str_ireplace("http://", "", $hotdate['hotdate_site']);
                if(strripos($site_name, "/") === strlen($site_name) - 1)
                $site_name = substr($site_name, 0, strlen($site_name) - 1);
                $html->setvar('hotdate_site', strcut(to_html($site_name), 24));
                // prepare URL
				if(substr($hotdate['hotdate_site'],0,7)!="http://") $hotdate['hotdate_site'] = "http://".$hotdate['hotdate_site'];
				$html->setvar('hotdate_site_full', to_html($hotdate['hotdate_site']));
                $html->parse('hotdate_site');

            }

	    if($hotdate['hotdate_phone']!="")
	    {
            $html->setvar('hotdate_phone', strcut(to_html($hotdate['hotdate_phone']), 13));
            $html->setvar('hotdate_phone_full', to_html($hotdate['hotdate_phone']));
            $html->parse('hotdate_phone');
	    }
	    /*$words = explode(' ',$hotdate['hotdate_description']);
	    $nw = 25;

	    if( sizeof($words) >= $nw)
	    {
                $description ="";

                for($i=0;$i<$nw;$i++)
                {
                     if($words[$i]==" ") $nw++;
                     $description.=$words[$i]." ";
                }
	    } else {
                $description = $hotdate['hotdate_description'];
	    }*/

        $description = trim(he($hotdate['hotdate_description']));//nl2br
		$description_short = hard_trim($description, 185);

        $html->setvar('hotdate_description', $description_short);
        $html->setvar('hotdate_description_full', $description);
        $html->setvar('description_collapse', ($description == $description_short) ? 0 : 1);

        $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id'], false);
        $html->setvar("image_thumbnail_b", $images["image_thumbnail_b"]);
        $html->setvar("image_file", $images["image_file"]);
        $html->setvar('photo_id', $images['photo_id']);
	    if(($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_clock_l.gif")||($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/hotdates/foto_02_l.jpg"))
	    {
		$html->parse("no_image");
            }else{
		$html->parse("image");
	    }
            if($hotdate['hotdate_private'])
            {
                $html->parse('hotdate_private_functions', true);
            	$html->parse('hotdate_private');
            }
            else
            {
	            if($g_user['user_id'] == $hotdate['user_id'])
	                $html->parse('hotdate_edit', false);

                if($is_guest || $g_user['user_id'] == $hotdate['user_id']) {
                    $html->parse('hotdate_functions', true);
                }
	            
            	$html->parse('hotdate');
            }

// SEO

$g['main']['title'] = $g['main']['title'] . ' :: ' . htmlentities(to_html($hotdate['hotdate_title']),ENT_QUOTES,"UTF-8");
$g['main']['description'] = htmlentities($description."...",ENT_QUOTES,"UTF-8");

	   }
        else
        {
        	redirect('hotdates.php');
        }

		parent::parseBlock($html);
	}
}

