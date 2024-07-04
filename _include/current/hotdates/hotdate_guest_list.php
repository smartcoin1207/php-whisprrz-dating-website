<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class ChotdateshotdateGuestList extends CHtmlBlock
{
    var $m_need_container = true;

    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;

        $hotdates_quests_per_page = Common::getOption('hotdates_quests_per_page', 'template_options');

        $n_results_per_page = $hotdates_quests_per_page ? $hotdates_quests_per_page : 6;

        $hotdate_id = get_param('hotdate_id');

        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);
        if($hotdate)
        {
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);

            $sql_base = ChotdatesTools::guests_by_hotdate_sql_base($hotdate['hotdate_id']);

            $n_results = ChotdatesTools::count_from_sql_base($sql_base);

            $page = intval(get_param('hotdate_guest_list_page', 1));
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

            if(ChotdatesTools::is_hotdate_finished($hotdate))
            {
                $html->parse('hotdate_finished_title');
            }
            else
            {
                if($hotdate['user_id'] == $g_user['user_id'])
                {
                    $html->parse('hotdate_guest_delete_title');
                } else
                {
                    $guest = DB::row('SELECT * FROM hotdates_hotdate_guest WHERE hotdate_id = ' . $hotdate['hotdate_id'] . " AND user_id = " . $g_user['user_id'] . " LIMIT 1");
                    if(!$guest) {
                        $signin_available = ChotdatesTools::getSignAvailable($hotdate);
                        if($signin_available) {
                            $html->parse('hotdate_will_you_come_title');
                        }
                    } else {
                        $html->parse('hotdate_youre_coming_title');
                    }                   
                }
            }

            /* popcorn modified hotdate guest 2024-05-23 start */
            $guest_sql = "SELECT * FROM hotdates_hotdate_guest WHERE hotdate_id = " . to_sql($hotdate['hotdate_id'], 'Text') . " AND user_id = " . to_sql(guid(), 'Text') . "";

            $guest_me = DB::row($guest_sql);
            if(isset($guest_me['user_id']) && $guest_me['user_id']) {
                if(Common::isOptionActive('hotdate_wall_enabled'))
                $html->parse('hotdate_wall_buttons');
            }
            /* popcorn modified hotdate guest 2024-05-23 start */

            $guests = ChotdatesTools::retrieve_from_sql_base($sql_base, $n_results_per_page, ($page - 1) * $n_results_per_page);

            $n_guests = 0;
            foreach($guests as $guest)
            {
                if($guest['user_id']==$hotdate['user_id']) $html->setvar('n_guests', l('Host'));
                else $html->setvar('n_guests', l('hotdates_n_guests_' . $guest['guest_n_friends']));

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

                if($hotdate['user_id'] != $guest['user_id'] && $hotdate['user_id'] == guid()) {
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

