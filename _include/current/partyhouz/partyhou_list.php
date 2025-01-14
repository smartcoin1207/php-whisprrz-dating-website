<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CpartyhouzpartyhouList extends CHtmlBlock
{
	var $m_need_container = true;
	var $m_list_type = "by_partyhouzian";
	var $m_partyhouzian_id = null;
	var $m_exclude_partyhou_id = null;
	var $m_n_results_per_page = 10;
	var $m_partyhou_where_when = true;
    var $m_country_id = null;
    var $m_category_id = null;
    var $m_partyhouzian_founded = null;
    var $m_partyhou_datetime = null;
    var $m_query = null;
    var $m_search_type_item = null;
    var $m_need_not_found_message = true;
    var $m_n_results = null;
    var $m_upcoming = 0;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $n_results_per_page = get_param('n_results_per_page', $this->m_n_results_per_page);
        $partyhou_where_when = get_param('partyhou_where_when', $this->m_partyhou_where_when);
        $list_type = get_param('list_type', $this->m_list_type);
        $partyhouzian_id = get_param('partyhouzian_id', $this->m_partyhouzian_id);
        $user_id = get_param('user_id', $g_user['user_id']);

        $country_id = get_param('country_id', $this->m_country_id);
        $category_id = get_param('category_id', $this->m_category_id);
        $partyhouzian_founded = get_param('partyhouzian_founded', $this->m_partyhouzian_founded);
        $partyhou_datetime = get_param('partyhou_datetime', $this->m_partyhou_datetime);
        if ($partyhou_datetime == "") {
            $partyhou_datetime = get_param('datetime', $this->m_partyhou_datetime);
        }
        // query can be 0
		$query = strval(get_param('query', $this->m_query));
		$search_type_item = strval(get_param('search_type_item', $this->m_search_type_item));
        $upcoming = get_param('upcoming', $this->m_upcoming);

        switch($list_type)
        {
            case "by_user":
                if($this->m_upcoming)
                    $sql_base = CpartyhouzTools::partyhouz_by_user_sql_base($user_id);
                else
                    $sql_base = CpartyhouzTools::partyhouz_by_user_as_guest_sql_base($user_id);
                break;
            case "most_discussed":
                $sql_base = CpartyhouzTools::partyhouz_most_discussed_sql_base();
                break;
            case "most_anticipated":
                $sql_base = CpartyhouzTools::partyhouz_most_anticipated_sql_base();
                break;
            case "popular_finished":
                $sql_base = CpartyhouzTools::partyhouz_popular_finished_sql_base();
                break;
            case "upcoming":
                $sql_base = CpartyhouzTools::partyhouz_upcoming_sql_base();
                break;
            case "openparty":
                $sql_base = CpartyhouzTools::partyhouz_open_party_sql_base(" e.is_open_partyhouz = 1 ");
                break;
            case "random":
                $sql_base = CpartyhouzTools::partyhouz_random_partyhouz_sql_base($upcoming);
                break;
            case "search":
				if($query != "" && $search_type_item != "")
				{
                    $sql_base = CpartyhouzTools::partyhouz_by_query_sql_base($query, $search_type_item, $upcoming);

				}
				else if($category_id)
				{
					$sql_base = CpartyhouzTools::partyhouz_by_category_id_sql_base($category_id, $upcoming);
				}
				else if($partyhou_datetime)
				{
                    $sql_base = CpartyhouzTools::partyhouz_by_partyhou_datetime_sql_base($partyhou_datetime, $upcoming);
				}
				else  $sql_base = CpartyhouzTools::partyhouz_by_query_sql_base("", 1, $upcoming);

                break;
			case "past_alike":
				$partyhou_id = get_param('partyhou_id');
		        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
				$sql_base = CpartyhouzTools::partyhouz_past_partyhouz_alike_sql_base($partyhou);
                break;
			case "coming":
				$partyhou_id = get_param('partyhou_id');
		        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
				$sql_base = CpartyhouzTools::partyhouz_coming_partyhouz_sql_base($partyhou);
				break;

            default:
        		$sql_base = CpartyhouzTools::partyhouz_recent_sql_base();
        		break;
        }

        // Start Open_PartyhouZ senior-dev-1019 2024-10-21
        
        if($list_type !== "openparty")
        {
            $sql_base['query'] = str_replace("WHERE", " WHERE e.is_open_partyhouz = 0 AND", $sql_base['query']);
        } else { 
            $sql_base['query'] = str_replace("WHERE", " LEFT JOIN partyhouz_open ON FIND_IN_SET(e.partyhou_id, partyhouz_open.partyhou_ids) WHERE e.is_open_partyhouz = 1 AND ", $sql_base['query']);
        }
        // End Open_PartyhouZ senior-dev-1019 2024-10-21
        // var_dump($sql_base['query']);exit;

        $n_results = CpartyhouzTools::count_from_sql_base($sql_base);

        if(!$n_results && $list_type == "by_partyhouzian")
        {
        	$sql_base = CpartyhouzTools::partyhouz_by_partyhouzian_sql_base($partyhouzian_id);
        	$n_results = CpartyhouzTools::count_from_sql_base($sql_base);
        }

        $this->m_n_results = $n_results;

        /*if(!$n_results && $list_type == "search")
        {
        	$sql_base = CpartyhouzTools::partyhouz_by_rand_sql_base();
            $n_results = min($n_results_per_page, CpartyhouzTools::count_from_sql_base($sql_base));
        }*/

        $page = intval(get_param('partyhouz_partyhou_list_page', 1));
        $n_pages = ceil($n_results / $n_results_per_page);
        $page = max(1, min($n_pages, $page));

        $html->setvar('page', $page);
        $html->setvar('list_type', $list_type);
        $html->setvar('partyhouzian_id', $partyhouzian_id);
        $html->setvar('user_id', $user_id);
        $html->setvar('partyhou_id', get_param('partyhou_id'));
        $html->setvar('n_results_per_page', $n_results_per_page);
        $html->setvar('partyhou_where_when', $partyhou_where_when);

		$html->setvar('country_id', $country_id);
		$html->setvar('category_id', $category_id);
		$html->setvar('partyhouzian_founded', $partyhouzian_founded);
		$html->setvar('partyhou_datetime', $partyhou_datetime);
		$html->setvar('query', urlencode($query));
		$html->setvar('upcoming', $upcoming);

        if($this->m_need_container)
        {
            $html->parse('container_header');
            $html->parse('container_footer');
        }

        $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

        if(count($partyhouz))
        {
        	if($partyhou_where_when)
                $html->parse('partyhouz_where_when_title');
            else
                $html->parse('partyhouz_when_guests_comments_title');

	        foreach($partyhouz as $partyhou)
	        {
	            $html->clean('partyhou_where_when_rows');
	            $html->clean('partyhou_when_guests_comments_rows');

	        	$html->setvar('partyhou_id', $partyhou['partyhou_id']);
	            $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), 20));
	            $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));

	            $html->setvar('partyhou_n_comments', $partyhou['partyhou_n_comments']);
	            $html->setvar('partyhou_n_guests', $partyhou['partyhou_n_guests']);
                $html->setvar('partyhou_host_name', $partyhou['name']);
                $html->setvar('partyhou_host_id', $partyhou['user_id']);

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
	            $html->setvar('partyhou_date', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_date')));
	            $html->setvar('partyhou_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_time')));
	            $html->setvar('partyhou_datetime_raw', to_html($partyhou['partyhou_datetime']));
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


	            $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id']);
	            $html->setvar("image_thumbnail", $images["image_thumbnail"]);

	            if($partyhou_where_when)
	                $html->parse('partyhou_where_when_rows');
	            else
	                $html->parse('partyhou_when_guests_comments_rows');

	            $html->parse("partyhou1");
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

            if($partyhou_where_when)
                $html->parse('partyhouz_where_when_footer');
            else
                $html->parse('partyhouz_when_guests_comments_footer');

            $html->parse("partyhouz");
        }
        else
        {
            if($this->m_need_not_found_message)
                $html->parse("no_partyhouz_message");
        	$html->parse("no_partyhouz");
        }


        // making flip box
        $host_ids = array_unique(array_column($partyhouz, "user_id"));
        $host_ids_str = implode(',', $host_ids);
        $flipbox_user_query = "SELECT u.*, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
        ) AS age FROM user AS u WHERE u.user_id IN ($host_ids_str)";

        foreach($host_ids as $host_id)
        {
            parse_user_host($html, $flipbox_user_query, $host_id);
        }
        
		parent::parseBlock($html);
	}
}

function parse_user_host(&$html, $sql, $i){

	$fliptxt = "host";
	return parse_user_common($html, $sql, $i, $fliptxt);
}

function parse_user_common(&$html, $sql, $i=0, $fliptxt="") {
    global $users_stop_list, $member_index, $status_style;
	global $g_user; //cuigao-diamond-20201113
    if(Users_List::isBigBase()) {
        $sql = 'SELECT * FROM (' . $sql . ' ORDER BY u.user_id DESC LIMIT 100) AS u_tmp';
    }

	if ($fliptxt == "") {
		$sql .= ' ORDER BY RAND() LIMIT 1';
	} else {
		$sql .= ' ORDER BY u.user_id DESC LIMIT '."0".',1';
	}
	
	$row = DB::row($sql);

	
	if(!is_array($row)) return false;


	//cuigao-diamond-20201113-start
    $set_my_presence_couples = $row['set_my_presence_couples'];
	$set_my_presence_males = $row['set_my_presence_males'];
	$set_my_presence_females = $row['set_my_presence_females'];
    $set_my_presence_transgender = $row['set_my_presence_transgender'];
    $set_my_presence_nonbinary = $row['set_my_presence_nonbinary'];
	// $set_my_presence_everyone = $row['set_my_presence_everyone'];
	if($g_user['orientation']==5 && $set_my_presence_couples==1){		
		return false;
	}
	if($g_user['orientation']==1 && $set_my_presence_males==1){
		return false;
	}
	if($g_user['orientation']==2 && $set_my_presence_females==1){
		return false;
	}
    if($g_user['orientation']==6 && $set_my_presence_transgender==1){
		return false;
	}
    if($g_user['orientation']==7 && $set_my_presence_nonbinary==1){
		return false;
	}
	// if($set_my_presence_everyone==1){		
	// 	return false;
	// }
	//cuigao-diamond-20201113-end

	$users_stop_list .= ",".$row['user_id'];
	

	$row['country_title'] = trim($row['country']);
	if(pl_strlen($row['country_title']) > 7 ) $row['country_title'] = pl_substr($row['country_title'], 0, 7) . "...";
	foreach($row as $k=>$v) {
        if ($k == 'name') $v = User::nameOneLetterFull($v);
		if($k =='orientation') {
			$orientation_row = DB::row("SELECT * FROM const_orientation WHERE id = " . $v . ";");
			$v = " -". $orientation_row['title'];

		}
		// if($k == 'orientation') $v = "   -" . l($v);
        $html->assign("members_".$k, $v);
    }

	// PHOTO
	$html->assign("members_photo",User::getPhotoDefault($row['user_id'],"s"));

	// STATUS
	$status = DB::row("SELECT status FROM profile_status WHERE user_id=".$row['user_id']);
	if(is_array($status)) {
		$html->assign("members_status",$status['status']);
		$html->assign("members_status_style",$status_style++%6 + 1);
	}
    if (Common::isOptionActive('profile_status')) {
        $html->subcond(is_array($status), "user_status");
    }

	$profile_viewed_me = false;
	if($fliptxt == "viewed_me") {
		if($g_user['orientation']==5 && $row['set_profile_visitor_couples']==2){	
			$profile_viewed_me = true;
		} else if($g_user['orientation']==1 && $row['set_profile_visitor_males']==2){	
			$profile_viewed_me = true;
		} else if($g_user['orientation']==2 && $row['set_profile_visitor_females']==2){		
			$profile_viewed_me = true;
		} else if($g_user['orientation']==6 && $row['set_profile_visitor_transgender']==2){		
			$profile_viewed_me = true;
		} else if($g_user['orientation']==7 && $row['set_profile_visitor_nonbinary']==2){		
			$profile_viewed_me = true;
		} 
	} else {
		$profile_viewed_me = true;
	}
	if(!$profile_viewed_me) return false;

	$fliptxt_index = '';
	$tmp_suffix = '';

	if($fliptxt == "") {
		$fliptxt_index = $member_index;
		$tmp_suffix = '';
	} else {
		$fliptxt_index = $fliptxt.$i;
		$tmp_suffix = '_' . $fliptxt;

	}

	$html->assign("members_num", $fliptxt_index);

	CFlipCard::parseFlipCard($html, $row);
	$html->parse("users_new_item" . $tmp_suffix, true);


	return true;
}





