<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CPlaces extends CHtmlList
{
	function action()
	{
        global $p, $g;

        $del = get_param('delete');
        $banned = get_param_int('ban');
        $isRedirect = false;
		if ($del) {
            $groups =  explode(',', $del);
            foreach ($groups as $groupId) {
                $groupInfo = Groups::getInfoBasic($groupId);
                if ($groupInfo && $groupInfo['user_id'] && Common::isEnabledAutoMail('admin_group_social_delete')) {
                    DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($groupInfo['user_id']));
                    $row = DB::fetch_row();
                    $vars = array(
                        'title' => $g['main']['title'],
                        'name' => $row['name'],
                    );
                    //Common::sendAutomail($row['lang'], $row['mail'], 'admin_group_social_delete', $vars);
                }
                Groups::delete($groupId, true);
            }
			$isRedirect = true;
		} elseif ($banned) {
			Groups::ban($banned);
            $isRedirect = true;
		}
        if ($isRedirect) {
            $offset = intval(get_param('offset'));
            if ($offset) {
                $offset = "?offset={$offset}";
            } else {
                $offset = '';
            }
            redirect($p . $offset);
        }
	}

	function init()
	{
		global $g;

		$this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(m.group_id) FROM groups_social AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
              SELECT m.*, u.name AS user_name
                FROM groups_social AS m
           LEFT JOIN user AS u ON u.user_id = m.user_id
			" . $this->m_sql_from_add . "
		";

		$this->m_field['group_id'] = array("group_id", null);
        $this->m_field['ban_global'] = array("ban_global", null);
		$this->m_field['user_name'] = array("user_name", null);
		$this->m_field['title'] = array("title", null);
        $this->m_field['page'] = array("page", null);
		$this->m_field['date'] = array("date", null);

		$where = "m.page = 1";
		#$this->m_debug = "Y";

		$this->m_sql_where = $where;
		$this->m_sql_order = "group_id";
		$this->m_sql_from_add = "";
	}

	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}

    function onPostParse(&$html)
	{
        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        if($row['ban_global']){
			$this->m_field['ban_global'][1] = l('unban');
		} else {
			$this->m_field['ban_global'][1] = l('ban');
		}

        $userLink = User::url($row['user_id']);
        $html->setvar('user_url', $userLink);

        $groupLink = Groups::url($row['group_id']);
        $html->setvar('group_url', $groupLink);

        $html->setvar('group_type', $row['page'] ? l('page') : l('group'));

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

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "groups_social.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGroupsSocial());

include("../_include/core/administration_close.php");