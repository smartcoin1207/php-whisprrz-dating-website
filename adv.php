<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("./_include/core/main_start.php");

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

        CBanner::getBlock($html, 'right_column');
        
		if(empty($_GET['cat_id']))
		{
			$html->setvar("select_cats", DB::db_options("select id, name from adv_cats order by name"));

			$is_approved = "";
	        if(!Common::isOptionActive('adv_show_before_approval')) {
	            $is_approved = " AND (approved = 1 OR (approved = 0 AND user_id = " . to_sql(guid(), 'Number') . "))";
	        }

			for($i=1;$i<=11;$i++)
			{
				DB::query("select * from adv_cats where id=".$i);
				if ($cat = DB::fetch_row()) {
					$str = '';
					$str .= l($cat['name']) . ' (<a href="adv_cat.php?cat_id='.$cat['id'].'">' . l('all_adv_cats') . '</a>)';
					$html->setvar("cat_name".$i, $str);
					$str = '';
					DB::query("select * from adv_razd where cat_id=".$cat['id'].' order by name', 1);
					while ($row = DB::fetch_row(1)) {
                        $count = DB::count('adv_' . $cat['eng'], '`razd_id` = ' . to_sql($row['id'], 'Number') . $is_approved);
                        $countStr = '';
                        if ($count >0) {
                            $countStr = ' ' . lSetVars(l('adv_count'), array('count'=>$count));
                        }
						$str .= '<a href="adv_cat.php?cat_id='.$cat['id'].'&razd_id='.$row['id'].'">' . l($row['name']) . '</a>' . $countStr . '<br>';
					}
					$html->setvar("goods".$i, $str);
				}
			}
		}

		$html->setvar("user_id", get_param("user_id"));
		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
