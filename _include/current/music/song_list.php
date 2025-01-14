<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicSongList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "by_musician";
	var $m_musician_id = null;
	var $m_exclude_song_id = null;
	var $m_n_results_per_page = 10;
    var $m_country_id = null;
    var $m_category_id = null;
    var $m_musician_founded = null;
    var $m_song_year = null;
    var $m_query = null;
    var $m_need_not_found_message = true;
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
        	case "by_musician":
		        $musician = DB::row("SELECT * FROM music_musician WHERE musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
		        if($musician && $musician['user_id'] == $g_user['user_id'])
		        {
		        	$musician_actions = true;
		        }
        		$sql_base = CMusicTools::songs_by_musician_sql_base($musician_id, $this->m_exclude_song_id);
                break;
            case "by_user":
                if($user_id == $g_user['user_id'])
                {
                    $user_actions = true;
                }
                $sql_base = CMusicTools::songs_by_user_sql_base($user_id, $this->m_exclude_song_id);
                break;
            case "top_plays":
                $sql_base = CMusicTools::songs_top_plays_sql_base();
                break;
            case "top_rated":
                $sql_base = CMusicTools::songs_top_rated_sql_base();
                break;
            case "most_discussed":
                $sql_base = CMusicTools::songs_most_discussed_sql_base();
                break;
            case "search":
            	if($country_id)
            	{
                    $sql_base = CMusicTools::songs_by_country_id_sql_base($country_id);
            	}
				else if($category_id)
				{
					$sql_base = CMusicTools::songs_by_category_id_sql_base($category_id);
				}
				else if($musician_founded)
				{
                    $sql_base = CMusicTools::songs_by_musician_founded_sql_base($musician_founded);
				}
				else if($song_year)
				{
                    $sql_base = CMusicTools::songs_by_song_year_sql_base($song_year);
				}
				else if($query)
				{
                    $sql_base = CMusicTools::songs_by_query_sql_base($query);
				}
				else
				{
                    $sql_base = CMusicTools::songs_recent_sql_base();
				}

                break;
            default:
        		$sql_base = CMusicTools::songs_recent_sql_base();
        		break;
        }

        $n_results = CMusicTools::count_from_sql_base($sql_base);
        if(!$n_results && $list_type == "by_musician")
        {
        	$sql_base = CMusicTools::songs_by_musician_sql_base($musician_id);
        	$n_results = CMusicTools::count_from_sql_base($sql_base);
        }

        $this->m_n_results = $n_results;

        if(!$n_results && $list_type == "search")
        {
        	$sql_base = CMusicTools::songs_by_rand_sql_base();
            $n_results = min($n_results_per_page, CMusicTools::count_from_sql_base($sql_base));
        }

        $page = intval(get_param('musician_song_list_page', 1));
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

        $songs = CMusicTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($songs))
        {
        	if($musician_actions)
                $html->parse('musician_actions_title');
            else
                $html->parse('musician_title');

            if($user_actions)
                $html->parse('user_actions_title');
            else
                $html->parse('user_title');

	        foreach($songs as $song)
	        {
	            $html->setvar('song_id', $song['song_id']);
	            $html->setvar('song_title', strcut(to_html($song['song_title']), 20));
	            $html->setvar('song_title_full', to_html(he($song['song_title'])));
	            $html->setvar('song_filename', $g['path']['url_files'] . CMusicTools::song_filename($song['song_id']));
	            $html->setvar('song_length', $song['song_length']);
	            $html->setvar('song_player',
	                CMusicTools::song_player(
	                    $song['song_id'],
	                    $song['song_length'],
	                    3,
	                    "LittleClipPlayer.swf",
	                    94,
	                    26));

                $html->setvar('user_id', $song['user_id']);
                $html->setvar('user_name', strcut(to_html(User::nameShort($song['name'])), 12));
                $html->setvar('user_name_full', to_html($song['name']));

	            $html->setvar('musician_id', $song['musician_id']);
	            $html->setvar('musician_name', strcut(to_html($song['musician_name']), 12));
	            $html->setvar('musician_name_full', to_html(he($song['musician_name'])));

	            $images = CMusicTools::song_images($song['song_id'], $song['musician_id']);
                    $html->setvar("image_thumbnail_s", $images["image_thumbnail_s"]);

	            if($musician_actions)
	                $html->parse('musician_actions_row', false);
	            else
	                $html->parse('musician_row', false);

	            if($user_actions)
	                $html->parse('user_actions_row', false);
	            else
	                $html->parse('user_row', false);

	            $html->parse("song");
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

            $html->parse("songs");
        }
        else
        {
            if($this->m_need_not_found_message)
                $html->parse("no_songs_message");
        	$html->parse("no_songs");
        }

		parent::parseBlock($html);
	}
}

