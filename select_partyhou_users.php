<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include './_include/core/main_start.php';

class CUsersResults extends CHtmlList
{
    public function action()
    {
        global $g, $p, $g_user;

        $partyhou_id = get_param('partyhou_id');
        $session_key = $partyhou_id . "_partyhou_selected_members";
		
        $sql = "SELECT  * FROM partyhouz_partyhou_guest WHERE partyhou_id = " . to_sql($partyhou_id, 'Text') . "AND user_id = " . to_sql(guid(), 'Text');
        $my_subscriber = DB::row($sql);
        // $moderator_options = json_decode($my_subscriber['moderator_options'], true);
				
		// if (!self::isOwner() && !$moderator_options['partyhou_mail']) {
        //     $redirect_url = $g['path']['url_main'] . "partyhou_wall.php?partyhou_id=" . $partyhou_id;
        //     redirect($redirect_url);
        // }

        if (!self::isOwner()) {
            $redirect_url = $g['path']['url_main'] . "partyhou_wall.php?partyhou_id=" . $partyhou_id;
            redirect($redirect_url);
        }

        $current_url = $g['path']['url_main'] . "select_partyhou_users.php?partyhou_id=" . $partyhou_id;
        $cmd = get_param('cmd', '');
        $save = get_param('save', '');
        $clear = get_param('clear', '');

        if ($save == 'all') {
            $members = CpartyhouzTools::getGuestUsers($partyhou_id);
            $selected_members = [];

            if ($members) {
                foreach ($members as $key => $value) {
                    if ($g_user['user_id'] == $value['user_id']) {
                        continue;
                    }

                    $selected_members[$value['user_id']] = '1';
                }
            }

            set_session($session_key, json_encode($selected_members));
            redirect($current_url);
        }
        if ($clear == 'all') {
            $selected_members = [];
            set_session($session_key, json_encode($selected_members));
            redirect($current_url);
        }

        if ($cmd == 'save') {
            $users = get_param_array('users');
            $selected_members_session = get_session($session_key);
            $selected_members = json_decode($selected_members_session, true);

            if (!$selected_members) {
                $selected_members = array();
            }
            if ($users) {
                foreach ($users as $key => $value) {
                    if ($value == '1') {
                        $selected_members[$key] = '1';
                    } else {
                        if (isset($selected_members[$key])) {
                            unset($selected_members[$key]);
                        }

                    }
                }
            }

            set_session($session_key, json_encode($selected_members));
        }
    }

    public function init()
    {
        global $g;
        global $p;
        global $g_user;

        $display = get_param('display_p', '5');

        $partyhou_id = get_param('partyhou_id', '');
        if (!$partyhou_id) {
            redirect(Common::getHomePage());
        }

        $this->m_sql_from_add = " LEFT JOIN user as u ON u.user_id=gs.user_id ";

        if ($display == "all") {
          
            $display = $row['total_row'];
        }
        $this->m_on_page = $display;
        $this->m_on_bar = 10;
        $this->m_sql_count = "SELECT COUNT(u.user_id) FROM partyhouz_partyhou_guest AS gs " . $this->m_sql_from_add . "";

        $this->m_sql = "SELECT u.user_id, u.name FROM partyhouz_partyhou_guest AS gs " . $this->m_sql_from_add . " ";

        $this->m_field['user_id'] = array("user_id", null);
        $this->m_field['name'] = array("name", null);

        $where = " AND partyhou_id=" . to_sql($partyhou_id, 'Text') . "AND u.user_id != " . to_sql($g_user['user_id'], 'Text');

        $this->m_sql_where = "1" . $where;
        $this->m_sql_order = "user_id";

        $result = DB::query($this->m_sql);
    }

    public function isOwner()
    {
        global $g_user, $g;

        $partyhou_id = get_param('partyhou_id', '');
        $gsql = "SELECT * FROM partyhouz_partyhou where partyhou_id = " . to_sql($partyhou_id, 'Text');
        $partyhou = DB::row($gsql);
        if (!$partyhou) {
            Common::toHomePage();
        }

        if ($g_user['user_id'] != $partyhou['user_id']) {
            return false;
        } else {
            return true;
        }
    }

    public function parseBlock(&$html)
    {
        global $g;
        $partyhou_id = get_param('partyhou_id', '');
        $gsql = "SELECT * FROM partyhouz_partyhou_guest where partyhou_id = " . to_sql($partyhou_id, 'Text');
        $partyhou_guests = DB::row($gsql);

        $url = $g['path']['url_main'] . "select_partyhou_users.php?partyhou_id=" . $partyhou_id;
        $html->setvar('url_page', $url);
        $html->setvar('url_event_mail', $g['path']['url_main'] . "partyhou_mail.php?partyhou_id=" . $partyhou_id);

        $page_options = array("5" => "5 / Page", "50" => "50 / Page", "75" => "75 / Page", "250" => "250 / Page", "all" => "All / Page");
        $selected = get_param('display_p', '5');
        $opt_html = "";
        foreach ($page_options as $key => $page) {
            $opt_html .= "<option value='{$key}' " . ($key == $selected ? 'selected' : '') . ">{$page}</option>";
        }
        $html->setvar("page_option", $opt_html);
        $html->setvar("display_p", $selected);

        $session_key = $partyhou_id . "_partyhou_selected_members";
        $selected_members_session = get_session($session_key);
        $selected_members = json_decode($selected_members_session, true);

        $x = [];
        if ($selected_members) {
            foreach ($selected_members as $k => $v) {
                $x[] = $k;
            }
        }

        $total_member_count = CpartyhouzTools::getTotalGuestsCount($partyhou_id);
        $total_member_count -= 1;

        $member_count = 0;
        if ($selected_members) {
            $member_count = count($selected_members);
        }

        $member_count_message = "Saved Members:  " . $member_count . " / " . $total_member_count;
        $html->setvar('member_count_message', $member_count_message);

        $html->setvar('member_message', 'sss');
        $html->setvar('selected_members_session', json_encode($x));

        $user_column = 5;
        $html->setvar('user_list_column', $user_column);
        $html->parse('script', false);

        parent::parseBlock($html);
    }
    public function onPostParse(&$html)
    {
        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
    }
    public function onItem(&$html, $row, $i, $last)
    {
        global $g;

        // CUsersResultsBase::parse($html, $this->m_field, $row);

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }
        parent::onItem($html, $row, $i, $last);
    }
}

$page = new CUsersResults("main", $g['tmpl']['dir_tmpl_main'] . "select_event_users.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include "./_include/core/main_close.php";
