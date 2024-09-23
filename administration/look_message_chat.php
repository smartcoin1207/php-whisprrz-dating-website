<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");

class CHon extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		$action = get_param('action');
		$id = get_param('id');
		if($action=='delete' && !empty($id)){
			DB::execute("delete from flashchat_messages where id=".to_sql($id));
			global $p;
			redirect($p."?user_id=".get_param("user_id"));
		}
	}
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;
		$user_id = get_param('user_id',0);
		$user_name=DB::result("select name from user where user_id=".to_sql($user_id));
		$html->setvar("user_name", $user_name);
		parent::parseBlock($html);
	}
}

class Cgroups extends CHtmlList
{
	function init()
	{
		parent::init();
		global $g;
		global $g_user;
		$user_id = get_param('user_id');
		$this->m_on_page = 10;
		$this->m_sql_count = "select count(M.id)  from flashchat_messages as M  join user as U on U.user_id = M.user_id";
		$this->m_sql = "select M.*  from flashchat_messages as M  join user as U on U.user_id = M.user_id";
		$this->m_sql_where = " U.user_id = ".to_sql( $user_id);
		$this->m_sql_order = " time desc ";
		$this->m_params = "&user_id=".get_param('user_id');
		$this->m_field['id'] = array("id", null);
		$this->m_field['msgtext'] = array("msgtext", null);
		$this->m_field['time'] = array("time", null);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$user_id = get_param('user_id');
		$html->setvar("id", $row['id']);
		$html->setvar("user_id", $user_id);
		$html->setvar("text", $row['msgtext']);
		$html->setvar("data", date('d-m-Y h:m:s',($row['time'])));

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }
	}
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		$user_id = get_param('user_id');
		$html->setvar("user_id", $user_id);

		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_administration'] . "look_message_chat.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$group_list = new Cgroups("mail_list", null);
$page->add($group_list);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsers());

include("../_include/core/administration_close.php");
