<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CVidsHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        /*if (param('q') != '') {
            $html->assign('topquery', CVidsTools::filterSearchQuery(param('q')));
        } else {
            $html->assign('topquery', 'Search text...');
            $html->parse('cleanquery');
        }
		*/
        $html->assign('total_vids_count', CVidsTools::countTotal());

        $cats = CVidsTools::getCatsAll();
        $cat = CVidsTools::getFilterCat();
        $num = 0;
        foreach ($cats as $id => $item) {
            $html->assign('cat_id', $id);
            /*if($num==0)
                $html->assign('cat_name', l($item, false, 'vids_category'));
            else
                $html->assign('cat_name', l($item, false, 'vids_category'));*/

            $html->assign('cat_name', l($item, false, 'vids_category'));
            $html->subcond($cat == $id, 'cat_current', 'cat_link');
            $html->cond($num == count($cats) - 1, 'cat_last');
            $html->parse('cat');
            $num++;
        }

        $periods = CVidsTools::getPeriods();
        $period = CVidsTools::getFilterPeriod();
        foreach ($periods as $num => $item) {
            $html->assign('period_id', $item);
            $html->assign('period_name', l($item . '_time'));
            $html->subcond($period == $item, 'period_current', 'period_link');
            $html->cond($num == count($periods) - 1, 'period_last');
            $html->parse('period');
        }


        parent::parseBlock($html);
	}
}
