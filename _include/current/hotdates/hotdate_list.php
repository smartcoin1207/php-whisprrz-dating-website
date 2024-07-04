<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class ChotdateshotdateList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "by_hotdatesian";
	var $m_hotdatesian_id = null;
	var $m_exclude_hotdate_id = null;
	var $m_n_results_per_page = 10;
	var $m_hotdate_where_when = true;
    var $m_country_id = null;
    var $m_category_id = null;
    var $m_hotdatesian_founded = null;
    var $m_hotdate_datetime = null;
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
        $hotdate_where_when = get_param('hotdate_where_when', $this->m_hotdate_where_when);
        $list_type = get_param('list_type', $this->m_list_type);
        $hotdatesian_id = get_param('hotdatesian_id', $this->m_hotdatesian_id);
        $user_id = get_param('user_id', $g_user['user_id']);

        $country_id = get_param('country_id', $this->m_country_id);
        $category_id = get_param('category_id', $this->m_category_id);
        $hotdatesian_founded = get_param('hotdatesian_founded', $this->m_hotdatesian_founded);
        $hotdate_datetime = get_param('hotdate_datetime', $this->m_hotdate_datetime);
        if ($hotdate_datetime == "") {
            $hotdate_datetime = get_param('datetime', $this->m_hotdate_datetime);
        }
        // query can be 0
		$query = strval(get_param('query', $this->m_query));
        $hotdate_place = get_param('hotdate_place');
        $upcoming = get_param('upcoming', $this->m_upcoming);

        switch($list_type)
        {
            case "by_user":
                if($this->m_upcoming)
                    $sql_base = ChotdatesTools::hotdates_by_user_sql_base($user_id);
                else
                    $sql_base = ChotdatesTools::hotdates_by_user_as_guest_sql_base($user_id);
                break;
            case "most_discussed":
                $sql_base = ChotdatesTools::hotdates_most_discussed_sql_base();
                break;
            case "most_anticipated":
                $sql_base = ChotdatesTools::hotdates_most_anticipated_sql_base();
                break;
            case "popular_finished":
                $sql_base = ChotdatesTools::hotdates_popular_finished_sql_base();
                break;
            case "upcoming":
                $sql_base = ChotdatesTools::hotdates_upcoming_sql_base();
                break;
	    case "random":
                $sql_base = ChotdatesTools::hotdates_random_hotdates_sql_base($upcoming);
                break;
            case "search":
				if($query!="")
				{
                    $sql_base = ChotdatesTools::hotdates_by_query_sql_base($query, $upcoming);

				}
				else if($hotdate_place)
				{
                    $sql_base = ChotdatesTools::hotdates_by_place_sql_base($hotdate_place, $upcoming);
				}
				else if($category_id)
				{
					$sql_base = ChotdatesTools::hotdates_by_category_id_sql_base($category_id, $upcoming);
				}
				else if($hotdate_datetime)
				{
                    $sql_base = ChotdatesTools::hotdates_by_hotdate_datetime_sql_base($hotdate_datetime, $upcoming);
				}
				else  $sql_base = ChotdatesTools::hotdates_by_query_sql_base("", $upcoming);

                break;
			case "past_alike":
				$hotdate_id = get_param('hotdate_id');
		        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
				$sql_base = ChotdatesTools::hotdates_past_hotdates_alike_sql_base($hotdate);
                break;
			case "coming":
				$hotdate_id = get_param('hotdate_id');
		        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
				$sql_base = ChotdatesTools::hotdates_coming_hotdates_sql_base($hotdate);
				break;

            default:
        		$sql_base = ChotdatesTools::hotdates_recent_sql_base();
        		break;
        }

        $n_results = ChotdatesTools::count_from_sql_base($sql_base);

        if(!$n_results && $list_type == "by_hotdatesian")
        {
        	$sql_base = ChotdatesTools::hotdates_by_hotdatesian_sql_base($hotdatesian_id);
        	$n_results = ChotdatesTools::count_from_sql_base($sql_base);
        }

        $this->m_n_results = $n_results;

        /*if(!$n_results && $list_type == "search")
        {
        	$sql_base = ChotdatesTools::hotdates_by_rand_sql_base();
            $n_results = min($n_results_per_page, ChotdatesTools::count_from_sql_base($sql_base));
        }*/

        $page = intval(get_param('hotdates_hotdate_list_page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('page', $page);
        $html->setvar('list_type', $list_type);
        $html->setvar('hotdatesian_id', $hotdatesian_id);
        $html->setvar('user_id', $user_id);
        $html->setvar('hotdate_id', get_param('hotdate_id'));
        $html->setvar('n_results_per_page', $n_results_per_page);
        $html->setvar('hotdate_where_when', $hotdate_where_when);

		$html->setvar('country_id', $country_id);
		$html->setvar('category_id', $category_id);
		$html->setvar('hotdatesian_founded', $hotdatesian_founded);
		$html->setvar('hotdate_datetime', $hotdate_datetime);
		$html->setvar('query', urlencode($query));
		$html->setvar('hotdate_place', $hotdate_place);
		$html->setvar('upcoming', $upcoming);

        if($this->m_need_container)
        {
            $html->parse('container_header');
            $html->parse('container_footer');
        }

        $hotdates = ChotdatesTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($hotdates))
        {
        	if($hotdate_where_when)
                $html->parse('hotdates_where_when_title');
            else
                $html->parse('hotdates_when_guests_comments_title');

	        foreach($hotdates as $hotdate)
	        {
	            $html->clean('hotdate_where_when_rows');
	            $html->clean('hotdate_when_guests_comments_rows');

	        	$html->setvar('hotdate_id', $hotdate['hotdate_id']);
	            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), 20));
	            $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));

	            $html->setvar('hotdate_n_comments', $hotdate['hotdate_n_comments']);
	            $html->setvar('hotdate_n_guests', $hotdate['hotdate_n_guests']);
	            $html->setvar('hotdate_place', strcut(to_html($hotdate['hotdate_place']), 16));
	            $html->setvar('hotdate_place_full', to_html($hotdate['hotdate_place']));

	            $html->setvar('hotdate_date', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_date')));
	            $html->setvar('hotdate_datetime_raw', to_html($hotdate['hotdate_datetime']));
	            $html->setvar('hotdate_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_time')));

	            $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id']);
	            $html->setvar("image_thumbnail", $images["image_thumbnail"]);

	            if($hotdate_where_when)
	                $html->parse('hotdate_where_when_rows');
	            else
	                $html->parse('hotdate_when_guests_comments_rows');

	            $html->parse("hotdate");
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

            if($hotdate_where_when)
                $html->parse('hotdates_where_when_footer');
            else
                $html->parse('hotdates_when_guests_comments_footer');

            $html->parse("hotdates");
        }
        else
        {
            if($this->m_need_not_found_message)
                $html->parse("no_hotdates_message");
        	$html->parse("no_hotdates");
        }

		parent::parseBlock($html);
	}
}

