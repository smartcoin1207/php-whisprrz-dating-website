<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
if(!Common::isOptionActive('mail')) {
   redirect(Common::toHomePage());
}
$mid = get_param('mid');
$type = get_param('display', 'list');
$folder = (int) get_param("folder", '');
$cmd = get_param('cmd', '');
$search = get_param('search', false);
if ($type == 'text'
    && $mid != ''
    && $folder == '') {
    $sql = 'SELECT `folder`
              FROM `mail_msg`
             WHERE `id` = ' . to_sql($mid, 'Number') . '
               AND `user_id` = ' . to_sql(guid(), 'Number');
	   
    $folder = DB::result($sql);
    if (!$folder) {
       redirect('mail.php');
    }
}
if(empty($_GET['cmd']) || $_GET['cmd'] != 'lang')
    payment_check('mail_read');

class CMailPage extends CHtmlBlock
{
    function action() {

        $cmd = get_param('cmd', '');

        if($cmd == 'lang') {
            header('Content-Type: text/xml; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            $words = array(
                'loading',
                'background',
                'object'
            );
            $lang = '<lang>';
            foreach($words as $wordKey) {
                $lang .= "<word name='$wordKey'>" . l($wordKey,false) . '</word>';
            }
            $lang .= '</lang>';

            echo $lang;
            die();
        }
        parent::action();
    }
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
		global $m_info;

		// receive and set variables for ballon

		$html->setvar("user_name", $m_info['user_name']);
		$html->setvar("user_age", $m_info['user_age']);
		$html->setvar("user_country_sub", $m_info['user_country_sub']);
        //if(Common::isOptionActive('wink')) {
            //$html->parse('wink_on', false);
        //}
		$html->parse("show_info", true);
		parent::parseBlock($html);
	}
}


if ($type == "list")
    $list = new CHtmlMailList("users_list", $g['tmpl']['dir_tmpl_main'] . "_mail_list.html");
elseif ($type == "text"){
    $list = new CHtmlMailText("users_list", $g['tmpl']['dir_tmpl_main'] . "_mail_text.html");
}
else redirect("mail.php");

$page = new CMailPage("", $g['tmpl']['dir_tmpl_main'] . "mail.html");

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");

$list->m_on_bar = 1;
$list->m_folder = ($folder == '') ? 1 : $folder;
if (DB::result("SELECT id FROM mail_folder WHERE id=" . $list->m_folder . "") == 0) $list->m_folder = 1;

$list->m_sql_where = "m.user_id=" . $g_user['user_id'] . " AND m.folder=" . $list->m_folder . "";

if($mid != '') {
    $list->m_sql_where .= ' AND m.id = ' . to_sql($mid, 'Number');
}

$list->m_sql_order = "m.id DESC";
$list->m_sql_from_add = "";

if($search !== false ) {
    //$list->m_debug = 'Y';
    //$list->m_on_page = 2;
    $list->m_search = $search;
    $list->m_sql_order = 'orders DESC, m.id DESC';
    $list->m_sql_where .= " AND (m.subject LIKE '%" . to_sql($search, 'Plain'). "%' OR m.text LIKE '%" . to_sql($search, 'Plain'). "%')";
}

$mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
$mailMenu->setActive('messages');
$list->add($mailMenu);

$page->add($list);

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$page->add($footer);

$folders = new CFolders("folders", $g['tmpl']['dir_tmpl_main'] . "_folders.html");
$page->add($folders);

include("./_include/core/main_close.php");
?>
