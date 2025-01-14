<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

		$html->setvar("user_id", intval(get_param("user_id")));
		parent::parseBlock($html);
	}
}





class CAdv extends CHtmlList
{
	var $var_eng;
	function init()
	{
		parent::init();
		global $g;
		global $g_user;

		$this->m_on_page = 30;
		$str = '';
		$find_str = '';
		$sql = '';
		if(!empty($_GET['find_str'])){
			$find_str = '&find_str='.get_param('find_str');

			$sql = "  and (subject like '%".get_param('find_str')."%' or body like '%".get_param('find_str')."%' ) ";
		}else{
			$sql='';
		}

		DB::query("select * from adv_cats");
		$sql_count_parts = array();
		$sql_parts = array();
		while($cat = DB::fetch_row())
		{
			$cats[] = $cat;

			$sql_count_parts[] = "select id, user_id, subject, body from adv_".$cat['eng']."";
			$sql_parts[] = "select C.id,subject,body,C.user_id,created,U.name as login,'".$cat['eng']."' as var_eng from adv_".$cat['eng']." as C	join user as U on U.user_id=C.user_id";
		}

		$this->m_sql_count = "select count(t.id) as col FROM ((" . implode(") UNION (", $sql_count_parts) . ")) as t ";
		$this->m_sql = "select * FROM ((" . implode(") UNION (", $sql_parts) . ")) as t ";
		$this->m_sql_where = "t.user_id=".to_sql($g_user["user_id"],"Number")."  ".$sql;
		$this->m_sql_order = " created desc ";

		$this->var_eng = $cat['eng'];
		$this->m_field['id'] = array("id", null);
		$this->m_field['subject'] = array("subject", null);
		$this->m_field['login'] = array("login", null);
		$this->m_field['created'] = array("created", null);
		$this->m_field['var_eng'] = array("var_eng", null);

//		$this->m_field['user_id'] = array("var_user_id", null);
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$this->m_field['subject'][1] = to_html($row['subject']);
		$this->m_field['login'][1] = to_html($row['login']);
		$html->setvar("subject", $row['subject']);
		$html->setvar("login", $row['login']);
		$html->setvar("date_add",Common::dateFormat($row['created'], 'adv_date', false));

		if($i==1) $html->setvar("first",1);
		else $html->setvar("first","");

		//$html->parse("adv_list_item",true);
		//$html->parse("adv_list",true);
	}



	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv_cat.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$adv_list = new CAdv("adv_list", null);
$page->add($adv_list);


include("./_include/core/main_close.php");

?>
