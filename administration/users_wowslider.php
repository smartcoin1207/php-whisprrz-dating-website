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
        DB::query("SELECT * FROM wowslider WHERE approved = 0 ORDER BY event_id", 2);
        $num = DB::num_rows(2);
        while ($row = DB::fetch_row(2)) {
            $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
            $row['user_profile_link']=User::url($row['user_id']);
            foreach ($row as $k => $v) {
                $html->setvar($k, $v);
            }
            $html->setvar('description', $row['description']);
            $html->setvar('field_title', $row['title']);

            $html->setvar("image_thumbnail", $g['path']['url_files'] . "wowslider/" . $row['img_path']);
            $html->setvar("image_file", $g['path']['url_files'] . "wowslider/" . $row['img_path']);
            
            $html->setvar('image_id', substr($row['img_path'], 0, strpos($row['img_path'], ".")));
            $html->parse('moderator_img_item', true);
            if($row['img_path'])
            $html->parse('image', true);

            $html->setvar('obj_id_type', 'wowslider_id');
            $html->setvar('obj_id', $row['event_id']);


            $html->parse('moderator_item', true);
            $html->clean('image');
            $html->clean('moderator_img_item');
        }
        
        $html->setvar('delete_moderator_object_url', 'administration/users_approve_delete_ajax.php');
        $html->setvar('approve_moderator_object_url', 'administration/users_approve_ajax.php');
        $html->setvar('delete_img_ajax_php', 'administration/users_approve_delete_ajax.php');
        $html->setvar('redirect_url', 'administration/users_wowslider.php');
        $html->setvar('confirm_delete_action', l('confirm_delete_wowslider'));
        $html->setvar('confirm_approve_action', l('confirm_approve_wowslider'));
        $html->setvar('confirm_approve_all_action', l('confirm_approve_all_wowslider'));

        $html->parse('moderator_section', true);
        $html->parse('sections', true);
        $html->parse('approve_button_not_submit', true);
		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_wowslider.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");