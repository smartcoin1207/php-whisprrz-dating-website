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

        $hotdate_id = get_param('hotdate_id');
		
        $sql = "SELECT  * FROM hotdates_hotdate_guest WHERE hotdate_id = " . to_sql($hotdate_id, 'Text') . "AND user_id = " . to_sql(guid(), 'Text');
        $my_subscriber = DB::row($sql);

        if (!self::isOwner()) {
            $redirect_url = $g['path']['url_main'] . "hotdate_wall.php?hotdate_id=" . $hotdate_id;
            redirect($redirect_url);
        }

        $current_url = $g['path']['url_main'] . "select_hotdate_users.php?hotdate_id=" . $hotdate_id;
        $cmd = get_param('cmd', '');
        $save = get_param('save', '');
        $clear = get_param('clear', '');
        $selected_members = [];

        if ($save == 'all') {
            $members = ChotdatesTools::getGuestUsers($hotdate_id);
            $selected_members = [];

            if ($members) {
                foreach ($members as $key => $value) {
                    if ($g_user['user_id'] == $value['user_id']) {
                        continue;
                    }

                    $selected_members[$value['user_id']] = '1';
                }
            }
        }

        if ($clear == 'all') {
            $selected_members = [];
        }

        if ($cmd == 'save') {
            $users = get_param_array('users', []);

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
        }

        if($cmd == 'save' || $clear == 'all' || $save == 'all') {
            $table = "mass_mail_saved_user_list";
            
            $exist_row = DB::row("SELECT * FROM " . $table . " WHERE event_id = " . to_sql($hotdate_id) . " AND event_type = 'hotdate'" );

            if(isset($exist_row)) {
                $sql = "UPDATE mass_mail_saved_user_list SET userlist = " . to_sql(json_encode($selected_members)) . " WHERE event_id = " . to_sql($hotdate_id);
            } else {
                $sql = "INSERT INTO mass_mail_saved_user_list (event_id, userlist, event_type) values(" . to_sql($hotdate_id, 'Text') . ", " . to_sql(json_encode($selected_members), 'Text') .  ", 'hotdate')";
            }

            dB::execute($sql);
        }

        if($clear == 'all' || $save =='all') {
            redirect($current_url);
        }
    }

    public function init()
    {
        global $g;
        global $p;
        global $g_user;

        $display = get_param('display_p', '5');

        $hotdate_id = get_param('hotdate_id', '');
        if (!$hotdate_id) {
            redirect(Common::getHomePage());
        }

        $this->m_sql_from_add = " LEFT JOIN user as u ON u.user_id=gs.user_id ";

        if ($display == "all") {
          
        }
        $this->m_on_page = $display;
        $this->m_on_bar = 10;
        $this->m_sql_count = "SELECT COUNT(u.user_id) FROM hotdates_hotdate_guest AS gs " . $this->m_sql_from_add . "";

        $this->m_sql = "SELECT u.user_id, u.name FROM hotdates_hotdate_guest AS gs " . $this->m_sql_from_add . " ";

        $this->m_field['user_id'] = array("user_id", null);
        $this->m_field['name'] = array("name", null);

        $where = " AND hotdate_id=" . to_sql($hotdate_id, 'Text') . "AND u.user_id != " . to_sql($g_user['user_id'], 'Text');

        $this->m_sql_where = "1" . $where;
        $this->m_sql_order = "user_id";

        $result = DB::query($this->m_sql);
    }

    public function isOwner()
    {
        global $g_user, $g;

        $hotdate_id = get_param('hotdate_id', '');
        $gsql = "SELECT * FROM hotdates_hotdate where hotdate_id = " . to_sql($hotdate_id, 'Text');
        $hotdate = DB::row($gsql);
        if (!$hotdate) {
            Common::toHomePage();
        }

        if ($g_user['user_id'] != $hotdate['user_id']) {
            return false;
        } else {
            return true;
        }
    }

    public function parseBlock(&$html)
    {
        global $g;
        $table = "mass_mail_saved_user_list";

        $hotdate_id = get_param('hotdate_id', '');
        $gsql = "SELECT * FROM hotdates_hotdate_guest where hotdate_id = " . to_sql($hotdate_id, 'Text');
        $hotdate_guests = DB::row($gsql);

        $url = $g['path']['url_main'] . "select_hotdate_users.php?hotdate_id=" . $hotdate_id;
        $html->setvar('url_page', $url);
        $html->setvar('url_event_mail', $g['path']['url_main'] . "hotdate_mail.php?hotdate_id=" . $hotdate_id);

        $page_options = array("5" => "5 / Page", "50" => "50 / Page", "75" => "75 / Page", "250" => "250 / Page", "all" => "All / Page");
        $selected = get_param('display_p', '5');
        $opt_html = "";
        foreach ($page_options as $key => $page) {
            $opt_html .= "<option value='{$key}' " . ($key == $selected ? 'selected' : '') . ">{$page}</option>";
        }
        $html->setvar("page_option", $opt_html);
        $html->setvar("display_p", $selected);

        $sql = "SELECT * FROM " . $table . " WHERE  event_id = " . to_sql($hotdate_id) . " AND event_type = 'hotdate'";
        $saved_users_list = DB::row($sql);
        if($saved_users_list) {
            $user_list = $saved_users_list['userlist'];
            $selected_members = json_decode($user_list, true);
        } else {
            $selected_members = [];
        }

        $x = [];
        if ($selected_members) {
            foreach ($selected_members as $k => $v) {
                $x[] = $k;
            }
        }

        $total_member_count = ChotdatesTools::getTotalGuestsCount($hotdate_id);
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
