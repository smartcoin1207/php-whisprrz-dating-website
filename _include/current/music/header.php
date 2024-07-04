<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

		$html->setvar('n_songs', DB::result("SELECT COUNT(song_id) FROM music_song"));
		
		$settings = CMusicTools::settings();

        DB::query("SELECT * FROM music_category ORDER BY position");
        $categories = array(array('category_id' => 0, 'category_title' => l('all', false, 'music_category')));
        while($category = DB::fetch_row())
        {
            $categories[] = $category;
        }
		
        for($category_n = 0; $category_n != count($categories); ++$category_n)
        {
            $category = $categories[$category_n];
        	
        	$html->setvar('category_id', $category['category_id']);
            $html->setvar('category_title', l($category['category_title'], false, 'music_category'));
            
            if($category_n == count($categories) - 1)
                $html->setvar("class_last", ' class="last"');
            else
                $html->setvar("class_last", '');
                
            if($category['category_id'] == $settings['category_id'])
            {
                $html->parse('categories_item_active', false);
                $html->setblockvar('categories_item_not_active', '');	
            }
            else
            {
                $html->parse('categories_item_not_active', false);
                $html->setblockvar('categories_item_active', '');   
            }

            $html->parse("categories_item", true);          
        }
        
        $limits = array('today', 'week', 'month', 'all');
        for($limit_n = 0; $limit_n != count($limits); ++$limit_n)
        {
            $limit = array('limit_id' => $limits[$limit_n], 'limit_title' => 'music_limit_' . $limits[$limit_n]);
            
            $html->setvar('limit_id', $limit['limit_id']);
            $html->setvar('limit_title', isset($l['all'][$limit['limit_title']]) ? $l['all'][$limit['limit_title']] : $limit['limit_title']);
            
            if($limit_n == count($limits) - 1)
                $html->setvar("class_last", ' class="last"');
            else
                $html->setvar("class_last", '');
            
            if($limit['limit_id'] == $settings['setting_limit'])
            {
                $html->parse('limits_item_active', false);
                $html->setblockvar('limits_item_not_active', '');   
            }
            else
            {
                $html->parse('limits_item_not_active', false);
                $html->setblockvar('limits_item_active', '');   
            }

            $html->parse("limits_item", true);          
        }
        
		parent::parseBlock($html);
	}
}

