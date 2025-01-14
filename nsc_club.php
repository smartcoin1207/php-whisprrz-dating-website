<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. 
  
  This file is created and built by cobra --- 2020-02-06.
  */

include("./_include/core/main_start.php");
include("./_include/current/nsc_club.option.php");

class CDonation extends CHtmlBlock  {

    function action() {
        
    }

    function parseBlock(&$html) {
        $lang = Common::getOption('lang_loaded', 'main');
        $id = get_param('id');
		
		if(strpos($id, "?display=text") !== false) {
			$updatedString = str_replace("?display=text", "", $id);
			$id = $updatedString;
		}
		
		if(isset($id) && $id!=""){
			DB::query("SELECT * FROM `pages` WHERE `set` = '' AND `lang` = 'default' AND `id` = ".$id." ORDER BY position");
			while ($row = DB::fetch_row()) {
				$allowTmpls = explode(',', $row['set']);
				if ($row['system'] && !in_array($optionTmplName, $allowTmpls)) {
					continue;
				}
				if (isset($allowedPage[$row['menu_title']]) && !$allowedPage[$row['menu_title']]) {
					continue;
				}
				$alias = $row['menu_title'];
				$html->setvar('id', $row['id']);				
				if ($row['system']) {
					$row['menu_title'] = l($row['menu_title'], $lang);
				}
				$html->setvar('club_title', $row['menu_title']);
				$html->setvar('club_content', $row['content']);				
			}
		}else{
		
			$club_content = ClubOption::getBgPath('club_content');
			$html->setvar('club_content', $club_content);
		}
        parent::parseBlock($html);
    }

}

$page = new CDonation("", $g['tmpl']['dir_tmpl_main'] . "nsc_club.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
