<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

payment_check('adv_add');

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;
		
		$cat_name = get_param('cat'); 
		$id = get_param('id');

		$selected_cat = null;
		$adv = null;
		$image_n = 1;
		        //pupup windows
				$popup_row = DB::row("SELECT  * FROM posting_info  WHERE page = 'popup_hotdates' LIMIT 1");
				$html->setvar('popup_title', $popup_row['header']);
				$html->setvar('popup_confirm_button', $popup_row['active']);
				$html->setvar('popup_decline_button', $popup_row['deactive']);
				$var = array();
				$text = $popup_row['text'];
				$var['username'] = $g_user['name'];
				$var['posting_terms'] = l('popup_posting_terms');
				$var['privacy_policy'] = l('popup_privacy_policy');
				$var['time'] = date('Y-m-d h:i:s A');
				$ful_text = Common::replaceByVars($text, $var);
				$html->setvar('popup_text', $ful_text);
				$html->parse('popup_confirm_terms_policy', true);
		
		if($id && $cat_name)
		{
			DB::query("select * from adv_cats where eng = " . to_sql($cat_name, 'String') . " LIMIT 1");
			$selected_cat = DB::fetch_row();

			if($selected_cat)
			{
				DB::query("select * from adv_" . $selected_cat['eng'] . " where id = " . to_sql($id) . " LIMIT 1");
				$adv = DB::fetch_row();
				
				if($adv)
				{
					if(isset($adv['telecommute']) && $adv['telecommute']) $adv['telecommute'] = 'checked';
					if(isset($adv['contract']) && $adv['contract']) $adv['contract'] = 'checked';
					if(isset($adv['internship']) && $adv['internship']) $adv['internship'] = 'checked';
					if(isset($adv['part_time']) && $adv['part_time']) $adv['part_time'] = 'checked';
					if(isset($adv['non_profit']) && $adv['non_profit']) $adv['non_profit'] = 'checked';
					
					foreach($adv as $key => $value)
					{
						$html->setvar("adv_" . $key, $value);
					}
						$html->setvar("adv_subject", he($adv['subject']));
					
					$html->setvar('orig_id', $adv['id']);
					$html->setvar('orig_cat_id', $selected_cat['id']);
					
					DB::query("select * from adv_images where adv_cat_id = " . $selected_cat['id'] . " and adv_id = " . $adv['id'] . " LIMIT 4");
					while($image = DB::fetch_row())
					{
						$html->setvar("image_" . $image_n . "_thumbnail", $g['path']['url_files'] . "adv_images/" . $image['id'] . "_th_s.jpg");
						$html->setvar("image_" . $image_n . "_file", $g['path']['url_files'] . "adv_images/" . $image['id'] . "_b.jpg");
						$html->parse("image_" . $image_n);
						
						++$image_n;
					}
				}
			}
		}

		for(;$image_n <= 4; ++$image_n)
		{
			$html->parse("image_" . $image_n . "_empty");
		}
		
		if(!$selected_cat)
		{
			DB::query("select * from adv_cats where id = " . to_sql(get_param('cat_id', 0)) . " LIMIT 1");
			$selected_cat = DB::fetch_row();
		}
		
		DB::query("select * from adv_razd where id = " . to_sql(get_param('razd_id', 0)) . " LIMIT 1");
		$selected_razd = DB::fetch_row();
		
		DB::query("select * from adv_cats order by name", 1);
		while($cat = DB::fetch_row(1))
		{
			if(!$selected_cat)
				$selected_cat = $cat;
				
			$html->setvar("razd_select_" . $cat['eng'], 
				DB::db_options("select id,name from adv_razd where cat_id=".$cat['id'], 
				$adv ? $adv['razd_id'] : ($selected_razd ? $selected_razd['id'] : 0)));
			$html->setvar("cat_" . $cat['eng'] . "_id", $cat['id']);
		}

		
		$html->setvar("cat_select", DB::db_options("select id, name from adv_cats order by name", $selected_cat['id']));
		$html->setvar("cat_id", $selected_cat['id']);
		
		$br_select_items = '';
		
		for($i = 0; $i != 6; ++$i)
		{
			$br_select_items .= "<option value=$i" . (($adv && isset($adv['br']) && $adv['br'] == $i) ? ' selected' : '' ) . ">$i</option>";
		}
		$html->setvar('br_select_items', $br_select_items);
			
		if($adv)
		{
			$html->parse("edit_title",true);
			$html->parse("edit_submit",true);
		}
		else
		{
			$html->parse("add_title",true);
			$html->parse("add_submit",true);
		}

		$html->setvar("user_id", get_param("user_id"));
		parent::parseBlock($html);
	}
}
#$a = mysql_

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv_edit.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
