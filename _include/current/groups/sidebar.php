<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsSidebar extends CHtmlBlock
{
	var $m_first_block = "recently_added";
	var $m_second_block = "most_discussed";
	
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

		if($this->m_first_block)
            $this->parseSubBlock($html, $this->m_first_block, 1);
        if($this->m_second_block)
            $this->parseSubBlock($html, $this->m_second_block, 2);

		parent::parseBlock($html);
	}

    function parseSubBlock(&$html, $block_type, $block_n)
    {
        global $g_user;
        global $l;
        global $g;
        
        $browse_all_params = "";
        
        switch($block_type)
        {
            case "most_discussed":
                $sql_base = CGroupsTools::groups_most_discussed_sql_base();
            	break;
            case "recently_added":
                $sql_base = CGroupsTools::groups_recently_added_sql_base();
                break;
            case "most_popular":
                $sql_base = CGroupsTools::groups_most_popular_sql_base();
                break;
        }

        $block_title = l('groups_' . $block_type);
        $html->setvar('block_title', $block_title);  
    	$html->setvar('block_type', $block_type);
    	$html->setvar('browse_all_params', $browse_all_params);
    	
        $groups = CGroupsTools::retrieve_from_sql_base($sql_base, 2);
        $group_n = 1;

        foreach($groups as $group)
        {
            $html->setvar('group_id', $group['group_id']);
            $html->setvar('group_title', strcut(to_html(he($group['group_title'])), 20));
            $html->setvar('group_title_full', he(to_html($group['group_title'])));

            $html->setvar('group_n_comments', $group['group_n_comments']);
            $html->setvar('group_n_posts', $group['group_n_posts']);
            $html->setvar('group_n_members', $group['group_n_members']);
            
            $html->setvar('group_description', strcut(to_html($group['group_description']), 40));

            $images = CGroupsTools::group_images($group['group_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);
            
            if($group_n != count($groups))
                $html->parse("group_" . $block_n . "_not_last");
            else
                $html->setblockvar("group" . $block_n . "_not_last", '');

	        if(CGroupsTools::is_group_member($group['group_id'])||(!$group['group_private']))  
                {
                    $html->clean("private_group_alert");
                    $html->clean("private_group_alert2");
                    $html->subparse("group_link");  
                    $html->subparse("group_link_img");  
                    $html->subparse("group_link2");  
                    $html->subparse("group_link_img2");  

                } else {
                     $html->clean("group_link");
                     $html->clean("group_link_img"); 
                     $html->clean("group_link_button");  
                     $html->clean("group_link2");
                     $html->clean("group_link_img2");  
                     $html->clean("group_link_button2");  
                     $html->subparse("private_group_alert");
                     $html->subparse("private_group_alert2");
                }
            
            $html->parse("group_" . $block_n);
            
            ++$group_n;
        }
    	
    	$html->parse('block_' . $block_n);
    }
}

