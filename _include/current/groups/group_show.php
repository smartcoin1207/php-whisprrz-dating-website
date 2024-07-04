<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsGroupShow extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $group_id = get_param('group_id');

        if(!$group_id)
        {
        	$forum_id = get_param('forum_id');
        	$forum = CGroupsTools::retrieve_forum_by_id($forum_id);
        	if($forum)
        	{
        		$group_id = $forum['group_id'];
				$_REQUEST['group_id'] = $forum['group_id'];
        	}
        }
        $group = CGroupsTools::retrieve_group_by_id($group_id);
        if($group)
        {
        	$title_length = 32;

            $html->setvar('group_founder', User::getInfoBasic($group['user_id'], 'name'));

        	$html->setvar('group_id', $group['group_id']);
            $html->setvar('group_title', he(strcut(to_html($group['group_title']), $title_length)));
            $html->setvar('group_title_full', he(to_html($group['group_title'])));
            $html->setvar('category_title', to_html(l($group['category_title'],false,'groups_category')));
            $html->setvar('category_title_full', to_html(l($group['category_title'],false,'groups_category')));
            $html->setvar('category_id', $group['category_id']);
            $html->setvar('group_n_posts', $group['group_n_posts']);
            $html->setvar('group_n_members', $group['group_n_members']);
            $description = to_html(trim(he($group['group_description'])));
            $description_short = strcut($description, 248);
            $html->setvar('group_description', $description_short);
            $html->setvar('group_description_full', $description);
            $html->setvar('description_collapse', ($description == $description_short) ? 0 : 1);
            // SEO
            $g['main']['title'] = $g['main']['title'] . ' :: ' . he(to_html($group['group_title']));
            $g['main']['description'] = he($description_short);

            $images = CGroupsTools::group_images($group['group_id'], false);
            $html->setvar("image_thumbnail_b", $images["image_thumbnail_b"]);
            $html->setvar("image_file", $images["image_file"]);
	    if($images["image_file"] == $g['tmpl']['url_tmpl_main'] . "images/groups/foto_02_l.jpg")
	    {
		$html->parse("no_image");
            }else{
		$html->parse("image");
	    }

            $html->parse('category');
            if($g_user['user_id'] == $group['user_id'])
            {
                $html->parse('group_edit', false);
                $html->parse('group_invite_button', false);
                $html->parse('group_functions', true);
            }
            else
            {
            	if(CGroupsTools::is_group_member($group['group_id']))
            	{
                    $html->parse('group_leave', true);
                    $html->parse('group_leave_button', false);
                    $html->parse('group_functions', true);
            	}
            	else
            	{
            		if($group['group_private'])
                        $html->parse('group_join_private_button', false);
            		else
                        $html->parse('group_join_button', false);
            	}
            }
        }
        else
        {
            $html->setvar('group_title', to_html(l('groups_new_group')));
            $html->setvar('group_title_full', to_html(l('groups_new_group')));
            $html->setvar('group_n_posts', to_html('---'));
            $html->setvar('group_n_members', to_html('---'));
            $html->setvar('group_description', to_html(l('groups_show_group_default_description')));
            $html->setvar('group_founder', guser('name'));

            $images = CGroupsTools::group_images(0, false);

            $html->setvar("image_thumbnail_b", $images["image_thumbnail_b"]);
            $html->setvar("image_file", $images["image_file"]);
			$html->parse("no_image");

            $html->parse('no_category');
        }

		parent::parseBlock($html);
	}
}

