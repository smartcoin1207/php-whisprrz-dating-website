<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class Cpartyhouzpartyhouzhow extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $partyhou_id = get_param('partyhou_id');

        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
        if($partyhou)
        {
            $guests = CpartyhouzTools::getGuestUsers($partyhou_id);

            $is_guest = false;
            
            foreach ($guests as $key => $guest) {
                if($guest['user_id'] == $g_user['user_id']) {
                    $is_guest = true;
                    break;
                }
            }

        	$title_length = 32;

            if(!$partyhou['partyhou_private'] && CpartyhouzTools::is_partyhou_finished($partyhou))
            {
            	$title_length = 16;
            	$html->parse('partyhou_finished');
            }

            $cum_string = "";
            if ($partyhou['cum_males'] == 1) {
                $cum_string = "Males / ";
            }
            if ($partyhou['cum_females'] == 1) {
                $cum_string = $cum_string . "Females / ";
            }
            if ($partyhou['cum_couples'] == 1) {
                $cum_string = $cum_string . "Couples / ";
            }
            if ($partyhou['cum_transgender'] == 1) {
                $cum_string = $cum_string . "Transgender / ";
            }
            if ($partyhou['cum_nonbinary'] == 1) {
                $cum_string = $cum_string . "Nonbinary";
            }
            if ($partyhou['cum_everyone'] == 1) {
                $cum_string = "Everyone";
            }
            $cum_string = "Cum to " . $cum_string;

            $locked_string = "";
            if ($partyhou['is_lock'] == 1) {
                $locked_string = "Room is Locked";
            } else {
                $locked_string = "Room is Unlocked";
            }

            $lookin_string = "";
            if ($partyhou['lookin_males'] == 1) {
                $lookin_string = "Males / ";
            }
            if ($partyhou['lookin_females'] == 1) {
                $lookin_string = $lookin_string . "Females / ";
            }
            if ($partyhou['lookin_couples'] == 1) {
                $lookin_string = $lookin_string . "Couples / ";
            }
            if ($partyhou['lookin_transgender'] == 1) {
                $lookin_string = $lookin_string . "Transgender / ";
            }
            if ($partyhou['lookin_nonbinary'] == 1) {
                $lookin_string = $lookin_string . "Nonbinary";
            }
            if ($partyhou['lookin_everyone'] == 1) {
                $lookin_string = "Everyone";
            }
            $lookin_string = "Lookin to " . $lookin_string;
            $html->setvar('lookin_string', $lookin_string);
            $html->setvar('cum_string', $cum_string);
            $html->setvar('locked_string', $locked_string);

            $datetime = new DateTime($partyhou['partyhou_datetime']); // Replace this with your desired datetime
            $now = new DateTime();

            $interval = $now->diff($datetime);
            $days = $interval->format('%a');
            $hours = $interval->format('%h');
            $minutes = $interval->format('%i');

            if ($interval->invert) {
                $sign = "-";
            } else {
                $sign = "+";
            }

            $formattedDifference = "{$days}:{$hours}:{$minutes}";
            $html->setvar('formattedDifference', $formattedDifference);
            $html->setvar('sign', $sign);

            $sql_partyhou = "SELECT * FROM user WHERE user_id = " . to_sql($partyhou['user_id']);
            $partyhou_user = DB::row($sql_partyhou);
            $name_seo = $partyhou_user['name_seo'];
            $username = $partyhou_user['name'];

            $photo_url = User::getPhotoDefault($partyhou['user_id']);
            $html->setvar('user_name', $username);
            $html->setvar('name_seo', $name_seo);
            $html->setvar('photo', $photo_url);

            $html->setvar('partyhou_id', $partyhou['partyhou_id']);
            $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), $title_length));
            $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));
            $html->setvar('category_title', strcut(to_html(l($partyhou['category_title'], false, 'partyhouz_category')), 8));
            $html->setvar('category_title_full', to_html(l($partyhou['category_title'], false, 'partyhouz_category')));
            $html->setvar('category_id', $partyhou['category_id']);
            $html->setvar('partyhou_n_comments', $partyhou['partyhou_n_comments']);
            $html->setvar('partyhou_n_guests', $partyhou['partyhou_n_guests']);
	        $html->setvar('partyhou_date', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_date')));
	        $html->setvar('partyhou_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_time')));
            $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault($partyhou['user_id'], "r"));
            $html->setvar('user_name', $partyhou['name']);

	    /*$words = explode(' ',$partyhou['partyhou_description']);
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
                $description = $partyhou['partyhou_description'];
	    }*/


        $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id'], false);
        $html->setvar("image_thumbnail_b", $images["image_thumbnail_b"]);
        $html->setvar("image_file", $images["image_file"]);
        $html->setvar("photo_id", $images['photo_id']);

	    if(($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_clock_l.gif")||($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/partyhouz/foto_02_l.jpg"))
	    {
		$html->parse("no_image");
            }else{
		$html->parse("image");
	    }
            if($partyhou['partyhou_private'])
            {
                $html->parse('partyhou_private_functions', true);
            	$html->parse('partyhou_private');
            }
            else
            {
	            if($g_user['user_id'] == $partyhou['user_id'])
	                $html->parse('partyhou_edit', false);

                if($is_guest || $g_user['user_id'] == $partyhou['user_id']) {
                    $html->parse('partyhou_functions', true);
                }
	            
            	$html->parse('partyhou');
            }

// SEO

$g['main']['title'] = $g['main']['title'] . ' :: ' . htmlentities(to_html($partyhou['partyhou_title']),ENT_QUOTES,"UTF-8");
$g['main']['description'] = htmlentities("...",ENT_QUOTES,"UTF-8");

	   }
        else
        {
        	redirect('partyhouz.php');
        }

		parent::parseBlock($html);
	}
}

