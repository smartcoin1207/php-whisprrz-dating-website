<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CGroupsHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;

		$html->setvar('total_n_groups', DB::result('SELECT COUNT(group_id) FROM groups_group'));

		$settings = CGroupsTools::settings();

        DB::query("SELECT * FROM groups_category ORDER BY position");
        $categories = array(array('category_id' => 0, 'category_title' => l('all', false, 'groups_category')));
        while($category = DB::fetch_row())
        {
            $categories[] = $category;
        }

        for($category_n = 0; $category_n != count($categories); ++$category_n)
        {
            $category = $categories[$category_n];

        	$html->setvar('category_id', $category['category_id']);
            $html->setvar('category_title', l($category['category_title'],false,'groups_category'));

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

		parent::parseBlock($html);
	}
}

