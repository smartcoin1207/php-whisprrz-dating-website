<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/groups/custom_head.php");
require_once("./_include/current/groups/header.php");
require_once("./_include/current/groups/sidebar.php");
require_once("./_include/current/groups/tools.php");
require_once("./_include/current/groups/group_show.php");
require_once("./_include/current/groups/group_image_list.php");
require_once("./_include/current/groups/group_member_list.php");



class CGroups extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $group_id = get_param('group_id');

            $category_id = intval(get_param('category_id'));
            $group_title = get_param('group_title');
            $group_description = get_param('group_description');
            $group_private = get_param('group_private');

            if($group_title &&
	            $group_description &&
	            $category_id)
            {
                $group_description = Common::filter_text_to_db($group_description, false);
                if($group_id)
                {
                    if(!CGroupsTools::retrieve_group_for_edit_by_id($group_id))
                        redirect('groups.php');

                    DB::execute("UPDATE groups_group SET " .
                                " category_id=".to_sql($category_id, 'Number').
                                ", group_private=".to_sql($group_private, 'Number').
                                ", group_title=".to_sql($group_title).
                                ", group_description=".to_sql($group_description).
                                ", updated_at = NOW() WHERE group_id=" . to_sql($group_id, 'Number') . " LIMIT 1");
                }
                else
                {
                	$group_id = CGroupsTools::create_group($category_id, $group_private, $group_title, $group_description);
                }

                for($image_n = 1; $image_n <= 4; ++$image_n)
                {
                    $name = "image_" . $image_n;
                    CGroupsTools::do_upload_group_image($name, $group_id);
                }

                CGroupsTools::update_group($group_id);
                CStatsTools::count('groups_created');
                redirect('groups_group_show.php?group_id='.$group_id);
            }
            redirect('groups.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $group_id = get_param('group_id');
        $group = CGroupsTools::retrieve_group_for_edit_by_id($group_id);
        if($group)
        {
        	$html->setvar('group_id', $group['group_id']);
            $html->setvar('group_title', he($group['group_title']));
            $html->setvar('group_description', $group['group_description']);

            DB::query("SELECT * FROM groups_group_image WHERE group_id=" . $group['group_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_id", $image['image_id']);
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_b.jpg");
                $html->parse("image");
                ++$n_images;
            }

            if($n_images)
                $html->parse('edit_images');

            $html->parse('edit_title');
            $html->parse('edit_button');
        }
        elseif($group_id)
        {
            redirect('groups.php');
        }
        else
        {

            $html->setvar('group_title', l('groups_default_title'));
            $html->setvar('group_description', l('groups_default_description'));

            $html->parse('create_title');
            $html->parse('create_button');
        }

        $group_private = $group ? $group['group_private'] : 0;
        $private_options = '';
        $private_options .= '<option value=0 ' . ($group_private ? '' : 'selected="selected"') . '>';
        $private_options .= l('groups_public');
        $private_options .= '</option>';
        $private_options .= '<option value=1 ' . ($group_private ? 'selected="selected"' : '') . '>';
        $private_options .= l('groups_private');
        $private_options .= '</option>';
        $html->setvar("private_options", $private_options);

        $settings = CGroupsTools::settings();

        $category_options = '';
        DB::query("SELECT * FROM groups_category ORDER BY category_id");
        $selected_category_id = $group ? $group['category_id'] : $settings['category_id'];
        while($category = DB::fetch_row())
        {
            if(!$selected_category_id)
                $selected_category_id = $category['category_id'];

            $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $selected_category_id) ? 'selected="selected"' : '') . '>';
            $category_options .= l($category['category_title'], false, 'groups_category');
            $category_options .= '</option>';
        }
        $html->setvar("category_options", $category_options);

        parent::parseBlock($html);
	}
}

$page = new CGroups("", $g['tmpl']['dir_tmpl_main'] . "groups_group_edit.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$groups_custom_head = new CGroupsCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_groups_custom_head.html");
$header->add($groups_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$groups_header = new CGroupsHeader("groups_header", $g['tmpl']['dir_tmpl_main'] . "_groups_header.html");
$page->add($groups_header);

$groups_group_show = new CGroupsGroupShow("groups_group_show", $g['tmpl']['dir_tmpl_main'] . "_groups_group_show.html");
$page->add($groups_group_show);
$groups_group_image_list = new CGroupsGroupImageList("groups_group_image_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_image_list.html");
$groups_group_show->add($groups_group_image_list);

$groups_group_member_list = new CGroupsGroupMemberList("groups_group_member_list", $g['tmpl']['dir_tmpl_main'] . "_groups_group_member_list.html");
$page->add($groups_group_member_list);

include("./_include/core/main_close.php");
