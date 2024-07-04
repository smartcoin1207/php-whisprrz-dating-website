<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CGroupsGroupForumListSidebar extends CHtmlBlock
{
    var $m_need_container = true;
    
    function parseBlock(&$html)
    {
        global $g_user;
        global $l;
        global $g;
        
        $n_results_per_page = 10;
        
        $group_id = get_param('group_id');

        $group = CGroupsTools::retrieve_group_by_id($group_id);
        if($group)
        {
            $html->setvar('group_id', $group['group_id']);

            $sql_base = CGroupsTools::forums_by_group_recently_updated_sql_base($group['group_id']);
            $forums = CGroupsTools::retrieve_from_sql_base($sql_base, $n_results_per_page);
            
            if(count($forums))
            {
	            foreach($forums as $forum)
	            {
	                $html->setvar('forum_id', $forum['forum_id']);
	                $html->setvar('forum_title_full', to_html(he($forum['forum_title'])));
	                $html->setvar('forum_title', strcut(to_html($forum['forum_title']), 30));
	                
	                $html->parse('forum');
	            }
            }
            else
            {
            	$html->parse('no_forums');
            }
        }

        parent::parseBlock($html);
    }
}
