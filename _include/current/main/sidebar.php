<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CEventsSidebar extends CHtmlBlock
{
	var $m_first_block = "most_discussed";
	var $m_second_block = "most_anticipated";

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		CBanner::getBlock($html, 'right_column');
		if($this->m_first_block)
            $this->parseSubBlock($html, $this->m_first_block, 1);
        if($this->m_second_block)
            $this->parseSubBlock($html, $this->m_second_block, 2);

		parent::parseBlock($html);
	}

    function parseSubBlock(&$html, $block_type, $block_n)
    {
		
        global $g_user;
        global $l;
        global $g;

        $browse_all_params = "";
        $demo = false;
		if(defined('DEMO_EVENTS')) $demo = true;

        switch($block_type)
        {
            case "most_discussed":
                $sql_base = CEventsTools::events_most_discussed_sql_base();

				// DEMO
				if($demo)
				{
					$where = " e.event_title='Avatar' AND ";
					$sql_base = CEventsTools::events_most_discussed_sql_base($where);
					$events_demo = CEventsTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($events_demo[0]))
                        $events[0] = $events_demo[0];

					$where = " e.event_title='NickelBack' AND ";
					$sql_base = CEventsTools::events_most_discussed_sql_base($where);
					$events_demo = CEventsTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($events_demo[0]))
                        $events[1] = $events_demo[0];
				}

            	break;
            case "most_anticipated":
                $sql_base = CEventsTools::events_most_anticipated_sql_base();
                break;
            case "popular_finished":
                $sql_base = CEventsTools::events_popular_finished_sql_base();

				// DEMO
				if($demo)
				{
					$where = " e.event_title='Norah Jones' AND ";
					$sql_base = CEventsTools::events_popular_finished_sql_base($where);
					$events_demo = CEventsTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($events_demo[0]))
                        $events[0] = $events_demo[0];

					$where = " e.event_title='Carrie Underwood' AND ";
					$sql_base = CEventsTools::events_popular_finished_sql_base($where);
					$events_demo = CEventsTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($events_demo[0]))
                        $events[1] = $events_demo[0];
				}

                break;
            case "event_show":
		        $event_id = get_param('event_id');
		        $event = CEventsTools::retrieve_event_by_id($event_id);

		        $browse_all_params = "?event_id=" . $event['event_id'];

		        if(CEventsTools::is_event_finished($event))
		        {
		        	$block_type = l('coming_events');
		        	$sql_base = CEventsTools::events_coming_events_sql_base($event);

					// SHOW always 2 events if nothing found

					$events_test = CEventsTools::retrieve_from_sql_base($sql_base, 2);
					$check = count($events_test);

					$remove_id[0] = 0;

					if( $check < 2 )
					{
						if($check==1) {
						$events[0] = $events_test[0];
						$remove_id[] = $events[0]['event_id'];
						}

						$check_2 = 2 - $check;
						// remove previous ID
						$sql_base = CEventsTools::events_coming_events_category_sql_base($event,$remove_id);
						$events_test = CEventsTools::retrieve_from_sql_base($sql_base, 2-$check_2);
						$check = count($events_test);
						if( $check < $check_2 )
						{
							$sql_base = CEventsTools::events_coming_events_all_sql_base($event,$remove_id);
							$events_test = CEventsTools::retrieve_from_sql_base($sql_base, $check_2);
							if($check_2==1 && count($events_test)) $events[1] = $events_test[0];
						}
						else
						{
							if($check_2==1 && count($events_test)) $events[1] = $events_test[0];
						}

					}

		        }
		        else
		        {
		        	$block_type = l('past_events_alike');
		        	$sql_base = CEventsTools::events_past_events_alike_sql_base($event);

					// SHOW always 2 events if nothing found

					$events_test = CEventsTools::retrieve_from_sql_base($sql_base, 2);
					$check = count($events_test);

					$remove_id[0] = 0;

					if( $check < 2 )
					{
						if($check==1) {
						$events[0] = $events_test[0];
						$remove_id[] = $events[0]['event_id'];
						}

						$check_2 = 2 - $check;
						// remove previous ID
						$sql_base = CEventsTools::events_past_events_alike_category_sql_base($event,$remove_id);
						$events_test = CEventsTools::retrieve_from_sql_base($sql_base, 2-$check_2);
						$check = count($events_test);
						if( $check < $check_2 )
						{
							$sql_base = CEventsTools::events_past_events_alike_all_sql_base($event,$remove_id);
							$events_test = CEventsTools::retrieve_from_sql_base($sql_base, $check_2);
							if($check_2==1  && count($events_test)) $events[1] = $events_test[0];
						}
						else
						{
							if($check_2==1 && count($events_test)) $events[1] = $events_test[0];
						}

					}
		        }

            	break;
        }

		#print_r($sql_base);

        $block_title = l('events_' . $block_type);
        $html->setvar('block_title', $block_title);
    	$html->setvar('block_type', $block_type);
    	$html->setvar('browse_all_params', $browse_all_params);

		if(!isset($events)) $events = CEventsTools::retrieve_from_sql_base($sql_base, 2);
        $event_n = 1;

        foreach($events as $event)
        {
            $html->setvar('event_id', $event['event_id']);
            $html->setvar('event_title', strcut(to_html($event['event_title']), 20));
            $html->setvar('event_title_full', to_html($event['event_title']));

            $html->setvar('event_n_comments', $event['event_n_comments']);
            $html->setvar('event_n_guests', $event['event_n_guests']);
            $html->setvar('event_place',  $event['event_place']);
            $html->setvar('event_place_full', to_html($event['event_place']));

	        $html->setvar('event_date', to_html(Common::dateFormat($event['event_datetime'],'events_event_date')));
	        $html->setvar('event_datetime_raw', to_html($event['event_datetime']));
	        $html->setvar('event_time', to_html(Common::dateFormat($event['event_datetime'],'events_event_time')));

            $images = CEventsTools::event_images($event['event_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);

            if($event_n != count($events))
                $html->parse("event_" . $block_n . "_not_last");
            else
                $html->setblockvar("event" . $block_n . "_not_last", '');

            $html->parse("event_" . $block_n);

            ++$event_n;
        }

    	$html->parse('block_' . $block_n);
    }
}


