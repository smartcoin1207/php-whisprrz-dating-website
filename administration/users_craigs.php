<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23
include("../_include/core/administration_start.php");
require_once("../_include/current/vids/includes.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
        global $p;
	}

	function parseBlock(&$html)
	{
        global $g;
        for($i=1;$i<=11;$i++)
			{
				DB::query("select * from adv_cats where id=".$i);
				if ($cat = DB::fetch_row()) {

                    $adv_table = "adv_" . $cat['eng'];
                    $rows = DB::rows("SELECT * FROM ". $adv_table . " WHERE approved = 0");
                    if($rows){
                        foreach ($rows as $key => $row) {

                            $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                            $row['user_profile_link'] = User::url($row['user_id']);
                            foreach ($row as $k => $v) {
                                $html->setvar($k, $v);
                            }

                            $html->setvar('field_title', $row['body']);
                            $html->setvar('description', $row['subject']);

                            $html->setvar('obj_type', 'craigs');
                            $html->setvar('obj_id_type', 'craigs_id');
                            $html->setvar('obj_id', $row['id']);

                            $html->setvar("var_adv_cat_id", $cat['id']);
                            $html->setvar("var_adv_cat_name", l($cat['name']));
                            $html->setvar("var_adv_cat_name_eng", $cat['eng']);


                            DB::query("select * from adv_razd where id=".to_sql($row['razd_id'])."");
                            if (($adv_razd = DB::fetch_row())){
                                $html->setvar("var_adv_razd_id", $adv_razd['id']);
                                $html->setvar("var_adv_razd_name", l($adv_razd['name'])); 
                            }
                            $html->parse('category', true);

                            if(isset($row['price'])) {
                                $html->setvar('price', $row['price']);
                                $html->parse('price', true);
                            }

                            $img_rows = DB::rows("SELECT * FROM adv_images WHERE adv_cat_id = '" . $row['cat_id'] . "' and adv_id = '" . $row['id'] . "' ");
                            foreach ($img_rows as $key => $img) {
                                $html->setvar("image_thumbnail", $g['path']['url_files'] . "adv_images/" . $img['id'] . "_th.jpg");
                                $html->setvar("image_file", $g['path']['url_files'] . "adv_images/" . $img['id'] . "_b.jpg");
                                $html->setvar('image_id', $img['id']);
                                $html->parse('moderator_img_item', true);
                            }
                            if($img_rows) {
                                $html->parse('image', true);
                            }

                            $html->parse('moderator_item', true);
                            $html->clean('price');
                            $html->clean('category');

                            $html->clean('image');
                            $html->clean('moderator_img_item'); 
                        }
                    }
				}
			}
            
            $html->setvar('delete_moderator_object_url', 'administration/users_approve_delete_ajax.php');
            $html->setvar('approve_moderator_object_url', 'administration/users_approve_ajax.php');
            $html->setvar('delete_img_ajax_php', 'administration/users_approve_delete_ajax.php');
            $html->setvar('redirect_url', 'administration/users_criags.php');
            $html->setvar('confirm_delete_action', l('confirm_delete_craigs.php'));
            $html->setvar('confirm_approve_action', l('confirm_approve_craigs'));
            $html->setvar('confirm_approve_all_action', l('confirm_approve_all_craigs'));
            $html->parse('moderator_section', true);
		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_craigs.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");