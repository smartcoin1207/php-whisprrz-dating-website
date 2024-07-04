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
        DB::query("SELECT * FROM events_event WHERE approved = 0 ORDER BY event_id LIMIT 30", 2);
        $num = DB::num_rows(2);
        while ($row = DB::fetch_row(2)) {
            $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
            $row['user_profile_link']=User::url($row['user_id']);
            foreach ($row as $k => $v) {
                $html->setvar($k, $v);
            }
            $html->setvar('description', $row['event_description']);
            $html->setvar('field_title', $row['event_title']);

            $img_sql = "SELECT * FROM events_event_image WHERE event_id = '" . $row['event_id'] . "' " ;
            $img_rows = DB::rows($img_sql);

            foreach ($img_rows as $key => $img_row) {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "events_event_images/" . $img_row['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "events_event_images/" . $img_row['image_id'] . "_b.jpg");
                $html->setvar('image_id', $img_row['image_id']);
                $html->parse('moderator_img_item', true);
            } 
            if($img_rows) {
                $html->parse('image', true);
            }

            $html->setvar('obj_id_type', 'event_id');
            $html->setvar('obj_id', $row['event_id']);


            $html->parse('moderator_item', true);
            $html->clean('image');
            $html->clean('moderator_img_item');
        }
        
        $html->setvar('delete_moderator_object_url', 'administration/events_event_delete.php');
        $html->setvar('approve_moderator_object_url', 'administration/users_approve_ajax.php');
        $html->setvar('delete_img_ajax_php', 'administration/events_event_image_delete_ajax.php');
        $html->setvar('redirect_url', 'administration/users_events.php');
        $html->setvar('confirm_delete_action', l('confirm_delete_events'));
        $html->setvar('confirm_approve_action', l('confirm_approve_events'));
        $html->setvar('confirm_approve_all_action', l('confirm_approve_all_events'));

        $html->parse('moderator_section', true);
        $html->parse('sections', true);
		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_events.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");