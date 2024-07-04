<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
if(!Common::isOptionActive('wink')) {
    redirect(Common::toHomePage());
}



class CHtmlUsersListAction extends CHtmlUsersListInt
{
	var $m_on_page = 20;


	function action()
	{
		global $g_user;

		$cmd = get_param("cmd", "");
		if ($cmd == "delete")
		{
			$id = get_param_array("id");
			foreach ($id as $k => $v)
			{
				DB::execute("
					DELETE FROM users_interest
					WHERE user_from=" . $g_user['user_id'] . " AND user_to=" . to_sql($v, "Number") . "
					ORDER BY id LIMIT 1
				");
			}
		}
        $interest = DB::count('users_interest', '`user_from` = ' . to_sql(guid(), 'Number'));
        if ($interest == 0) {
            redirect('mail.php');
        }
	}
    function parseBlock(&$html) {
        $this->m_folder_name = l('menu_mail_sent_interest');
        parent::parseBlock($html);
    }
}
class CMailSentInterest extends CHtmlBlock {
    
    function parseBlock(&$html) {
        $type = get_param("display", "list");
        /*if (Common::isOptionActive('mail')) {
            $html->parse('mail_on');
        }
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on');
        }*/
        if($type == "photo") {
            $html->parse('photo_on');
        } else {
          $html->parse('photo_off');  
        }
        
        parent::parseBlock($html);
    }
} 

$page = new CMailSentInterest("", $g['tmpl']['dir_tmpl_main'] . "mail_sent_interest.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$type = get_param("display", "list");
if ($type == "list") $list = new CHtmlUsersListAction("users_list", $g['tmpl']['dir_tmpl_main'] . "_mail_interest.html");
elseif ($type == "profile" || $type == "profile_info") $list = new CUsersProfile("users_list", $g['tmpl']['dir_tmpl_main'] . "_profile.html");
elseif ($type == "photo") $list = new CHtmlUsersPhoto("users_list", $g['tmpl']['dir_tmpl_main'] . "_photo.html");
else
{
	redirect("mail.php");
}

$list->m_view = 0;
$list->m_sql_where = "1";
$list->m_sql_order = "i.id DESC ";
$list->m_sql_from_add = " JOIN users_interest AS i ON (u.user_id=i.user_to AND i.user_from=" . $g_user['user_id'] . ")";
$page->add($list);
if(Common::isOptionActive('mail')) {
    $folders = new CFolders("folders", $g['tmpl']['dir_tmpl_main'] . "_folders.html");
    $page->add($folders);
} else {
     $search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
     $page->add($search);
}

$mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
$mailMenu->setActive('sent_interest');
$page->add($mailMenu);

include("./_include/core/main_close.php");

?>
