<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CEventsEventGuestList extends CHtmlBlock
{
	var $m_need_container = true;

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $events_quests_per_page = Common::getOption('events_quests_per_page', 'template_options');

        $n_results_per_page = $events_quests_per_page ? $events_quests_per_page : 6;

		$event_id = get_param('event_id');

        $event = CEventsTools::retrieve_event_by_id($event_id);
        if($event)
        {
        	$html->setvar('event_id', $event['event_id']);

            $sql_base = CEventsTools::guests_by_event_sql_base($event['event_id']);

            $n_results = CEventsTools::count_from_sql_base($sql_base);

            $page = intval(get_param('event_guest_list_page', 1));
            $n_pages = ceil($n_results / $n_results_per_page);
            $page = max(1, min($n_pages, $page));

            $html->setvar('page', $page);
            $html->setvar('guests_count', $n_results);
            $html->setvar('first_guest_n', ($page - 1) * $n_results_per_page + 1);
            $html->setvar('last_guest_n', min(($page) * $n_results_per_page, $n_results));

            if($this->m_need_container)
            {
                $html->parse('container_header');
                $html->parse('container_footer');
            }

            if(CEventsTools::is_event_finished($event))
            {
                $html->parse('event_finished_title');
            }
            else
            {
                if($event['user_id'] == $g_user['user_id'])
                {
                    $html->parse('event_guest_delete_title');
                }
                else
                {
                    $guest = DB::row('SELECT * FROM events_event_guest WHERE event_id = ' . $event['event_id'] . " AND user_id = " . $g_user['user_id'] . " LIMIT 1");
                    if(!$guest) {
                        $signin_available = CEventsTools::getSignAvailable($event);
                        if($signin_available) {
                            $html->parse('event_will_you_come_title');
                        }
                    } else {
                        $html->parse('event_youre_coming_title');
                    }
                }
            }

            /* popcorn modified event guest 2024-05-23 start */
            $guest_sql = "SELECT * FROM events_event_guest WHERE event_id = " . to_sql($event['event_id'], 'Text') . " AND user_id = " . to_sql(guid(), 'Text') . " AND accepted = 1";

            $guest_me = DB::row($guest_sql);
            if(isset($guest_me['user_id']) && $guest_me['user_id'] || guid() == $event['user_id']) {
                if(Common::isOptionActive('event_wall_enabled')) {
                    $html->parse('event_wall_buttons');
                }
                
            }
            /* popcorn modified event guest 2024-05-23 start */

            $guests = CEventsTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

            $n_guests = 0;
            foreach($guests as $guest)
            {
				if($guest['user_id']==$event['user_id']) $html->setvar('n_guests', l('Host'));
                else $html->setvar('n_guests', l('events_n_guests_' . $guest['guest_n_friends']));

                if($guest['guest_n_friends'])
                {
                	$html->parse('guest_photo_with_friends', false);
                	$html->setblockvar('guest_photo_alone', '');
                }
                else
                {
                    $html->parse('guest_photo_alone', false);
                    $html->setblockvar('guest_photo_with_friends', '');
                }

                $html->setvar('guest_user_id', $guest['user_id']);

                if(($event['user_id'] != $guest['user_id']) && ($event['user_id']) == guid()) {
                    $html->parse('guest_delete_checkbox', false);
                }

                if( isset($guest['accepted']) && $guest['accepted'] == 1) {
                    $html->parse('guest_approve_check', false);
                } else {
                    $html->clean('guest_approve_check');
                }

                $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault($guest['user_id'], "r"));
                $html->setvar('user_name', $guest['name']);

            	$html->parse('guest_photo', false);
                $html->clean('guest_delete_checkbox');

                $html->setblockvar('guest_no_photo', '');
                $html->parse('guest');

                ++$n_guests;
            }

            for(; $n_guests < $n_results_per_page; ++$n_guests)
            {
                $html->parse('guest_no_photo', false);
                $html->setblockvar('guest_photo', '');
                $html->parse('guest');
            }

            if($page > 1)
            {
                $html->setvar('page_n', $page-1);
                $html->parse('pager_prev');
            }
            else
            {
            	$html->parse('pager_prev_inactive');
            }

            if($page < $n_pages)
            {
                $html->setvar('page_n', $page+1);
                $html->parse('pager_next');
            }
            else
            {
            	$html->parse('pager_next_inactive');
            }
        }

		parent::parseBlock($html);
	}
}