<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CEventsEventList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "by_eventsian";
	var $m_eventsian_id = null;
	var $m_exclude_event_id = null;
	var $m_n_results_per_page = 10;
	var $m_event_where_when = true;
    var $m_country_id = null;
    var $m_category_id = null;
    var $m_eventsian_founded = null;
    var $m_event_datetime = null;
    var $m_query = null;
    var $m_need_not_found_message = true;
    var $m_n_results = null;
    var $m_upcoming = 0;

	function parseBlock(&$html)
	{

		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = get_param('n_results_per_page', $this->m_n_results_per_page);
        $event_where_when = get_param('event_where_when', $this->m_event_where_when);
        $list_type = get_param('list_type', $this->m_list_type);
        $eventsian_id = get_param('eventsian_id', $this->m_eventsian_id);
        $user_id = get_param('user_id', $g_user['user_id']);

        $country_id = get_param('country_id', $this->m_country_id);
        $category_id = get_param('category_id', $this->m_category_id);
        $eventsian_founded = get_param('eventsian_founded', $this->m_eventsian_founded);
        $event_datetime = get_param('event_datetime', $this->m_event_datetime);
        // query can be 0
		$query = strval(get_param('query', $this->m_query));
        $event_place = get_param('event_place');
        $upcoming = get_param('upcoming', $this->m_upcoming);

        switch($list_type)
        {
            case "by_user":
                if($this->m_upcoming)
                    $sql_base = CEventsTools::events_by_user_sql_base($user_id);
                else
                    $sql_base = CEventsTools::events_by_user_as_guest_sql_base($user_id);
                break;
            case "most_discussed":
                $sql_base = CEventsTools::events_most_discussed_sql_base();
                break;
            case "most_anticipated":
                $sql_base = CEventsTools::events_most_anticipated_sql_base();
                break;
            case "popular_finished":
                $sql_base = CEventsTools::events_popular_finished_sql_base();
                break;
            case "upcoming":
                $sql_base = CEventsTools::events_upcoming_sql_base();
                break;
	    case "random":
                $sql_base = CEventsTools::events_random_events_sql_base($upcoming);
                break;
            case "search":
				if($query!="")
				{
                    $sql_base = CEventsTools::events_by_query_sql_base($query, $upcoming);

				}
				else if($event_place)
				{
                    $sql_base = CEventsTools::events_by_place_sql_base($event_place, $upcoming);
				}
				else if($category_id)
				{
					$sql_base = CEventsTools::events_by_category_id_sql_base($category_id, $upcoming);
				}
				else if($event_datetime)
				{
                    $sql_base = CEventsTools::events_by_event_datetime_sql_base($event_datetime, $upcoming);
				}
				else  $sql_base = CEventsTools::events_by_query_sql_base("", $upcoming);

                break;
			case "past_alike":
				$event_id = get_param('event_id');
		        $event = CEventsTools::retrieve_event_by_id($event_id);
				$sql_base = CEventsTools::events_past_events_alike_sql_base($event);
                break;
			case "coming":
				$event_id = get_param('event_id');
		        $event = CEventsTools::retrieve_event_by_id($event_id);
				$sql_base = CEventsTools::events_coming_events_sql_base($event);
				break;

            default:
        		$sql_base = CEventsTools::events_recent_sql_base();
        		break;
        }

        $n_results = CEventsTools::count_from_sql_base($sql_base);

        if(!$n_results && $list_type == "by_eventsian")
        {
        	$sql_base = CEventsTools::events_by_eventsian_sql_base($eventsian_id);
        	$n_results = CEventsTools::count_from_sql_base($sql_base);
        }

        $this->m_n_results = $n_results;

        /*if(!$n_results && $list_type == "search")
        {
        	$sql_base = CEventsTools::events_by_rand_sql_base();
            $n_results = min($n_results_per_page, CEventsTools::count_from_sql_base($sql_base));
        }*/

        $page = intval(get_param('events_event_list_page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('page', $page);
        $html->setvar('list_type', $list_type);
        $html->setvar('eventsian_id', $eventsian_id);
        $html->setvar('user_id', $user_id);
        $html->setvar('event_id', get_param('event_id'));
        $html->setvar('n_results_per_page', $n_results_per_page);
        $html->setvar('event_where_when', $event_where_when);

		$html->setvar('country_id', $country_id);
		$html->setvar('category_id', $category_id);
		$html->setvar('eventsian_founded', $eventsian_founded);
		$html->setvar('event_datetime', $event_datetime);
		$html->setvar('query', urlencode($query));
		$html->setvar('event_place', $event_place);
		$html->setvar('upcoming', $upcoming);

        if($this->m_need_container)
        {
            $html->parse('container_header');
            $html->parse('container_footer');
        }

        $events = CEventsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($events))
        {
        	if($event_where_when)
                $html->parse('events_where_when_title');
            else
                $html->parse('events_when_guests_comments_title');

	        foreach($events as $event)
	        {
	            $html->clean('event_where_when_rows');
	            $html->clean('event_when_guests_comments_rows');

	        	$html->setvar('event_id', $event['event_id']);
	            $html->setvar('event_title', strcut(to_html($event['event_title']), 20));
	            $html->setvar('event_title_full', to_html($event['event_title']));

	            $html->setvar('event_n_comments', $event['event_n_comments']);
	            $html->setvar('event_n_guests', $event['event_n_guests']);
	            $html->setvar('event_place', strcut(to_html($event['event_place']), 16));
	            $html->setvar('event_place_full', to_html($event['event_place']));

	            $html->setvar('event_date', to_html(Common::dateFormat($event['event_datetime'],'events_event_date')));
	            $html->setvar('event_datetime_raw', to_html($event['event_datetime']));
	            $html->setvar('event_time', to_html(Common::dateFormat($event['event_datetime'],'events_event_time')));

	            $images = CEventsTools::event_images($event['event_id']);
	            $html->setvar("image_thumbnail", $images["image_thumbnail"]);

	            if($event_where_when)
	                $html->parse('event_where_when_rows');
	            else
	                $html->parse('event_when_guests_comments_rows');

	            $html->parse("event");
	        }

            if($n_pages > 1)
            {
                if($page > 1)
                {
                    $html->setvar('page_n', $page-1);
                    $html->parse('pager_prev');
                }

                $links = pager_get_pages_links($n_pages, $page);

                foreach($links as $link)
                {
                    $html->setvar('page_n', $link);

                    if($page == $link)
                    {
                        $html->parse('pager_link_active', false);
                        $html->setblockvar('pager_link_not_active', '');
                    }
                    else
                    {
                        $html->parse('pager_link_not_active', false);
                        $html->setblockvar('pager_link_active', '');
                    }
                    $html->parse('pager_link');
                }

                if($page < $n_pages)
                {
                    $html->setvar('page_n', $page+1);
                    $html->parse('pager_next');
                }

                $html->parse('pager');
            }

            if($event_where_when)
                $html->parse('events_where_when_footer');
            else
                $html->parse('events_when_guests_comments_footer');

            $html->parse("events");
        }
        else
        {
            if($this->m_need_not_found_message)
                $html->parse("no_events_message");
        	$html->parse("no_events");
        }

		parent::parseBlock($html);
	}
}

