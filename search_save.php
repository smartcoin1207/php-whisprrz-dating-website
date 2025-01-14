<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
class CSearchSave extends CHtmlBlock
{

	function action()
	{
		global $g_user;

		$cmd = get_param("cmd", "");
		if ($cmd == "delete")
		{
			$id = to_sql(get_param("id", 0), "Number");
			DB::execute("DELETE FROM search_save WHERE user_id=" . $g_user['user_id'] . " AND id=" . $id . "");
		}
	}

	function parseBlock(&$html)
	{
		global $g_options;
		global $g_user;
        
        $advSearchOption = Common::isOptionActive('adv_search');
        if ($advSearchOption) {
            $html->setvar('url_search', 'search_advanced.php');
        } else {
            $html->setvar('url_search', 'search.php');
        }
        
		DB::query("SELECT id, name, query FROM search_save WHERE user_id=" . $g_user['user_id'] . " ORDER BY id");
		while ($row = DB::fetch_row())
		{
			$html->setvar("id", $row['id']);
			$html->setvar("name", $row['name']);
			$html->setvar("query", $row['query']);

            if(strpos($row['query'], 'map=map')) {
                $html->setvar('search_url', 'maps.php');
            } else {
                $html->setvar('search_url', 'search_results.php');
            }

			$html->parse("search_save", true);
		}
        
        $i = 0;
        if (Common::isOptionActive('online_tab_enabled')) {
            $html->parse('online_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('new_tab_enabled')) {
            $html->parse('new_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('birthdays_tab_enabled ')) {
            $html->parse('birthdays_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('matching_tab_enabled')) {
            $html->parse('matching_tab_enabled', false);
            $i++;
        }
        
        if ($html->blockexists('quick_search')) {
            Menu::parseSubmenu($html,'quick_search');
        } else {
            $quickSearchParsingBlocks=Menu::getSubmenuItemsList('quick_search');
            foreach($quickSearchParsingBlocks as $k=>$v){
                $html->parse($v);
            }
        }
        if ($i != 0) {
            $html->parse('tab_enabled', false);
        }
        
        if ($advSearchOption) {
            $html->parse('menu_search_advanced', false);
        }
        


        
		parent::parseBlock($html);
	}
}

if (Common::isOptionActive('saved_searches') == false) {
    redirect('search.php');
}

$page = new CSearchSave("", $g['tmpl']['dir_tmpl_main'] . "search_save.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$mailMenu = new CMenuSection('search', $g['tmpl']['dir_tmpl_main'] . "_menu_search.html");
$mailMenu->setActive('save');
$page->add($mailMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);




include("./_include/core/main_close.php");

?>
