<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicMusicianList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "by_user";
	var $m_musician_id = null;
	var $m_exclude_musician_id = null;
	var $m_n_results_per_page = 10;
    var $m_country_id = null;
    var $m_category_id = null;
    var $m_musician_founded = null;
    var $m_song_year = null;
    var $m_query = null;
    var $m_n_results = null;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

		$musician_actions = false;
		$user_actions = false;

        $n_results_per_page = get_param('n_results_per_page', $this->m_n_results_per_page);
        $list_type = get_param('list_type', $this->m_list_type);
        $musician_id = get_param('musician_id', $this->m_musician_id);
        $user_id = get_param('user_id', $g_user['user_id']);

        $country_id = get_param('country_id', $this->m_country_id);
        $category_id = get_param('category_id', $this->m_category_id);
        $musician_founded = get_param('musician_founded', $this->m_musician_founded);
        $song_year = get_param('song_year', $this->m_song_year);
        $query = get_param('query', $this->m_query);

        switch($list_type)
        {
            case "search":
                if($country_id)
                {
                    $sql_base = CMusicTools::musicians_by_country_id_sql_base($country_id);
                }
                else if($category_id)
                {
                    $sql_base = CMusicTools::musicians_by_category_id_sql_base($category_id);
                }
                else if($musician_founded)
                {
                    $sql_base = CMusicTools::musicians_by_musician_founded_sql_base($musician_founded);
                }
                else if($song_year)
                {
                    $sql_base = CMusicTools::musicians_by_song_year_sql_base($song_year);
                }
                else if($query)
                {
                    $sql_base = CMusicTools::musicians_by_query_sql_base($query);
                }
                else
                {
                    $sql_base = CMusicTools::musicians_recent_sql_base();
                }
                break;
        	default:
                if($user_id == $g_user['user_id'])
                {
                    $user_actions = true;
                }
                $sql_base = CMusicTools::musicians_by_user_sql_base($user_id, $this->m_exclude_musician_id);
                break;
        }

        $n_results = CMusicTools::count_from_sql_base($sql_base);

        $this->m_n_results = $n_results;

        $page = intval(get_param('musician_musician_list_page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('page', $page);
        $html->setvar('list_type', $list_type);
        $html->setvar('musician_id', $musician_id);
        $html->setvar('user_id', $user_id);
        $html->setvar('n_results_per_page', $n_results_per_page);

        $html->setvar('country_id', $country_id);
        $html->setvar('category_id', $category_id);
        $html->setvar('musician_founded', $musician_founded);
        $html->setvar('song_year', $song_year);
        $html->setvar('query', urlencode($query));

        if($this->m_need_container)
        {
            $html->parse('container_header');
            $html->parse('container_footer');
        }

        $musicians = CMusicTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($musicians))
        {
        	if($musician_actions)
                $html->parse('musician_actions_title');
            else
                $html->parse('musician_name');

            if($user_actions)
                $html->parse('user_actions_title');
            else
                $html->parse('user_title');

	        foreach($musicians as $musician)
	        {
	            $html->setvar('musician_id', $musician['musician_id']);
	            $html->setvar('musician_name', strcut(to_html($musician['musician_name']), 20));
	            $html->setvar('musician_name_full', to_html(he($musician['musician_name'])));
	            $html->setvar('musician_n_songs', DB::result("SELECT COUNT(song_id) FROM music_song WHERE musician_id = " . $musician['musician_id']));

                $html->setvar('user_id', $musician['user_id']);
                $html->setvar('user_name', strcut(to_html($musician['name']), 12));
                $html->setvar('user_name_full', he(to_html($musician['name'])));

	            $images = CMusicTools::musician_images($musician['musician_id']);
                    $html->setvar("image_thumbnail_s", $images["image_thumbnail_s"]);

	            if($musician_actions)
	                $html->parse('musician_actions_row', false);
	            else
	                $html->parse('musician_row', false);

	            if($user_actions)
	                $html->parse('user_actions_row', false);
	            else
	                $html->parse('user_row', false);

	            $html->parse("musician");
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

            $html->parse("musicians");
        }
        else
        {
            $html->parse("no_musicians");
        }

		parent::parseBlock($html);
	}
}

