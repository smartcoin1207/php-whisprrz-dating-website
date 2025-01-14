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

        $group_seo = self::getNameSeo();
		
		$groupId = Groups::getParamId();
        $sql = "SELECT  * FROM groups_social_subscribers WHERE group_id = " . to_sql($groupId, 'Text') . " AND user_id = " . to_sql(guid(), 'Text');
        $my_subscriber = DB::row($sql);
        $moderator_options = json_decode($my_subscriber['moderator_options'], true);
				
		if (!self::isOwner() && !$moderator_options['group_mail']) {
            $redirect_url = $g['path']['url_main'] . $group_seo;
            redirect($redirect_url);
        }

        $current_url = $g['path']['url_main'] . $group_seo . '/select_group_users';
        $cmd = get_param('cmd', '');
        $save = get_param('save', '');
        $clear = get_param('clear', '');

        if($cmd == "save_user_list") {
            $users = get_param_array('users');
            $title = get_param('title');
            
            $row = array
            (
                'user_id' => guid(),
                'user_ids' => json_encode($users),
                'event_id' => $groupId,
                'type' => 'group',
                'title' => $title
            );
            DB::insert('saved_user_list', $row);
            $id = DB::insert_id();

            echo json_encode(array("users" => $users, "status" => "success"));
            exit;
        }
    }

    public function init()
    {
        global $g;
        global $p;
        global $g_user;

        $display = get_param('display_p', '50');

        $groupId = Groups::getParamId();
        if (!$groupId) {
            redirect(Common::getHomePage());
        }

        $this->m_sql_from_add = " LEFT JOIN user as u ON u.user_id=gs.user_id ";

        if ($display == "all") {
            DB::query("SELECT count(u.user_id) as total_row FROM groups_social_subscribers AS gs
                        " . $this->m_sql_from_add . "");
            $row = DB::fetch_row();
            $display = $row['total_row'];
        }
        $this->m_on_page = $display;
        $this->m_on_bar = 10;
        $this->m_sql_count = "SELECT COUNT(u.user_id) FROM groups_social_subscribers AS gs " . $this->m_sql_from_add . "";

        $this->m_sql = "SELECT u.user_id, u.name FROM groups_social_subscribers AS gs " . $this->m_sql_from_add . " ";

        $this->m_field['user_id'] = array("user_id", null);
        $this->m_field['name'] = array("name", null);

        $where = " AND group_id=" . to_sql($groupId, 'Text') . "AND u.user_id != " . to_sql($g_user['user_id'], 'Text');

        $this->m_sql_where = "1" . $where;
        $this->m_sql_order = "user_id";

        $result = DB::query($this->m_sql);
    }

    public function getNameSeo()
    {

        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }

        $group_nameseo = $group['name_seo'];
        return $group_nameseo;

    }

    public function isOwner()
    {
        global $g_user, $g;

        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);
        if (!$group) {
            Common::toHomePage();
        }

        if ($g_user['user_id'] != $group['user_id']) {
            return false;
        } else {
            return true;
        }
    }

    public function parseBlock(&$html)
    {
        global $g;
        $groupId = Groups::getParamId();
        $gsql = "SELECT * FROM groups_social where group_id = " . to_sql($groupId, 'Text');
        $group = DB::row($gsql);

        $group_nameseo = $group['name_seo'];

        $url = $g['path']['url_main'] . $group_nameseo . "/select_group_users";
        $html->setvar('url_page', $url);
        $html->setvar('url_group_mail', $g['path']['url_main'] . $group_nameseo . "/group_mail");

        $page_options = array("5" => "5 / Page", "50" => "50 / Page", "75" => "75 / Page", "250" => "250 / Page", "all" => "All / Page");
        $selected = get_param('display_p', '50');
        $opt_html = "";
        foreach ($page_options as $key => $page) {
            $opt_html .= "<option value='{$key}' " . ($key == $selected ? 'selected' : '') . ">{$page}</option>";
        }
        $html->setvar("page_option", $opt_html);
        $html->setvar("display_p", $selected);

        $total_member_count = Groups::getNumberSubscribers($groupId);
        $total_member_count -= 1;

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

$page = new CUsersResults("main", $g['tmpl']['dir_tmpl_main'] . "select_group_users.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include "./_include/core/main_close.php";
