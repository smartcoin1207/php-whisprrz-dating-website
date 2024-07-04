<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CEventsEventShow extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $event_id = get_param('event_id');

        $event = CEventsTools::retrieve_event_by_id($event_id);
        if($event)
        {
        	$title_length = 32;

            if(!$event['event_private'] && CEventsTools::is_event_finished($event))
            {
            	$title_length = 16;
            	$html->parse('event_finished');
            }

            $html->setvar('event_id', $event['event_id']);
            $html->setvar('event_title', strcut(to_html($event['event_title']), $title_length));
            $html->setvar('event_title_full', to_html($event['event_title']));
            $html->setvar('category_title', strcut(to_html(l($event['category_title'], false, 'events_category')), 8));
            $html->setvar('category_title_full', to_html(l($event['category_title'], false, 'events_category')));
            $html->setvar('category_id', $event['category_id']);
            $html->setvar('event_n_comments', $event['event_n_comments']);
            $html->setvar('event_n_guests', $event['event_n_guests']);
	        $html->setvar('event_date', to_html(Common::dateFormat($event['event_datetime'],'events_event_date')));
	        $html->setvar('event_time', to_html(Common::dateFormat($event['event_datetime'],'events_event_time')));
            $html->setvar('event_place', hard_trim(to_html($event['event_place']), 25));
            $html->setvar('event_place_full', to_html($event['event_place']));
            $html->setvar('city_title', strcut(to_html($event['city_title']), 13));
            $html->setvar('city_title_full', to_html($event['city_title']));
            $html->setvar('event_address', hard_trim(to_html($event['event_address']), 25));
            $html->setvar('event_address_full', to_html($event['event_address']));

	    if($event['event_site']!="")
	    {
				$site_name = str_ireplace("http://", "", $event['event_site']);
                if(strripos($site_name, "/") === strlen($site_name) - 1)
                $site_name = substr($site_name, 0, strlen($site_name) - 1);
                $html->setvar('event_site', strcut(to_html($site_name), 24));
                // prepare URL
				if(substr($event['event_site'],0,7)!="http://") $event['event_site'] = "http://".$event['event_site'];
				$html->setvar('event_site_full', to_html($event['event_site']));
                $html->parse('event_site');

            }

	    if($event['event_phone']!="")
	    {
            $html->setvar('event_phone', strcut(to_html($event['event_phone']), 13));
            $html->setvar('event_phone_full', to_html($event['event_phone']));
            $html->parse('event_phone');
	    }
	    /*$words = explode(' ',$event['event_description']);
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
                $description = $event['event_description'];
	    }*/

        $description = trim(he($event['event_description']));//nl2br
		$description_short = hard_trim($description, 185);

        $html->setvar('event_description', $description_short);
        $html->setvar('event_description_full', $description);
        $html->setvar('description_collapse', ($description == $description_short) ? 0 : 1);

        $images = CEventsTools::event_images($event['event_id'], false);
        $html->setvar("image_thumbnail_b", $images["image_thumbnail_b"]);
        $html->setvar("image_file", $images["image_file"]);
	    if(($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/events/foto_clock_l.gif")||($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/events/foto_02_l.jpg"))
	    {
		$html->parse("no_image");
            }else{
		$html->parse("image");
	    }
            if($event['event_private'])
            {
                $html->parse('event_private_functions', true);
            	$html->parse('event_private');
            }
            else
            {
	            if($g_user['user_id'] == $event['user_id'])
	                $html->parse('event_edit', false);
	            $html->parse('event_functions', true);

            	$html->parse('event');
            }

// SEO

$g['main']['title'] = $g['main']['title'] . ' :: ' . htmlentities(to_html($event['event_title']),ENT_QUOTES,"UTF-8");
$g['main']['description'] = htmlentities($description."...",ENT_QUOTES,"UTF-8");

	   }
        else
        {
        	redirect('events.php');
        }

		parent::parseBlock($html);
	}
}

