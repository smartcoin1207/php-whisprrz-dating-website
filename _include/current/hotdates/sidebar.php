<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class ChotdatesSidebar extends CHtmlBlock
{
	var $m_first_block = "most_discussed";
	var $m_second_block = "most_anticipated";

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		CBanner::getBlock($html, 'right_column');
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
        $demo = false;
		if(defined('DEMO_hotdates')) $demo = true;

        switch($block_type)
        {
            case "most_discussed":
                $sql_base = ChotdatesTools::hotdates_most_discussed_sql_base();

				// DEMO
				if($demo)
				{
					$where = " e.hotdate_title='Avatar' AND ";
					$sql_base = ChotdatesTools::hotdates_most_discussed_sql_base($where);
					$hotdates_demo = ChotdatesTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($hotdates_demo[0]))
                        $hotdates[0] = $hotdates_demo[0];

					$where = " e.hotdate_title='NickelBack' AND ";
					$sql_base = ChotdatesTools::hotdates_most_discussed_sql_base($where);
					$hotdates_demo = ChotdatesTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($hotdates_demo[0]))
                        $hotdates[1] = $hotdates_demo[0];
				}

            	break;
            case "most_anticipated":
                $sql_base = ChotdatesTools::hotdates_most_anticipated_sql_base();
                break;
            case "popular_finished":
                $sql_base = ChotdatesTools::hotdates_popular_finished_sql_base();

				// DEMO
				if($demo)
				{
					$where = " e.hotdate_title='Norah Jones' AND ";
					$sql_base = ChotdatesTools::hotdates_popular_finished_sql_base($where);
					$hotdates_demo = ChotdatesTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($hotdates_demo[0]))
                        $hotdates[0] = $hotdates_demo[0];

					$where = " e.hotdate_title='Carrie Underwood' AND ";
					$sql_base = ChotdatesTools::hotdates_popular_finished_sql_base($where);
					$hotdates_demo = ChotdatesTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($hotdates_demo[0]))
                        $hotdates[1] = $hotdates_demo[0];
				}

                break;
            case "hotdate_show":
		        $hotdate_id = get_param('hotdate_id');
		        $hotdate = ChotdatesTools::retrieve_hotdate_by_id($hotdate_id);

		        $browse_all_params = "?hotdate_id=" . $hotdate['hotdate_id'];

		        if(ChotdatesTools::is_hotdate_finished($hotdate))
		        {
		        	$block_type = l('coming_hotdates');
		        	$sql_base = ChotdatesTools::hotdates_coming_hotdates_sql_base($hotdate);

					// SHOW always 2 hotdates if nothing found

					$hotdates_test = ChotdatesTools::retrieve_from_sql_base($sql_base, 2);
					$check = count($hotdates_test);

					$remove_id[0] = 0;

					if( $check < 2 )
					{
						if($check==1) {
						$hotdates[0] = $hotdates_test[0];
						$remove_id[] = $hotdates[0]['hotdate_id'];
						}

						$check_2 = 2 - $check;
						// remove previous ID
						$sql_base = ChotdatesTools::hotdates_coming_hotdates_category_sql_base($hotdate,$remove_id);
						$hotdates_test = ChotdatesTools::retrieve_from_sql_base($sql_base, 2-$check_2);
						$check = count($hotdates_test);
						if( $check < $check_2 )
						{
							$sql_base = ChotdatesTools::hotdates_coming_hotdates_all_sql_base($hotdate,$remove_id);
							$hotdates_test = ChotdatesTools::retrieve_from_sql_base($sql_base, $check_2);
							if($check_2==1 && count($hotdates_test)) $hotdates[1] = $hotdates_test[0];
						}
						else
						{
							if($check_2==1 && count($hotdates_test)) $hotdates[1] = $hotdates_test[0];
						}

					}

		        }
		        else
		        {
		        	$block_type = l('past_hotdates_alike');
		        	$sql_base = ChotdatesTools::hotdates_past_hotdates_alike_sql_base($hotdate);

					// SHOW always 2 hotdates if nothing found

					$hotdates_test = ChotdatesTools::retrieve_from_sql_base($sql_base, 2);
					$check = count($hotdates_test);

					$remove_id[0] = 0;

					if( $check < 2 )
					{
						if($check==1) {
						$hotdates[0] = $hotdates_test[0];
						$remove_id[] = $hotdates[0]['hotdate_id'];
						}

						$check_2 = 2 - $check;
						// remove previous ID
						$sql_base = ChotdatesTools::hotdates_past_hotdates_alike_category_sql_base($hotdate,$remove_id);
						$hotdates_test = ChotdatesTools::retrieve_from_sql_base($sql_base, 2-$check_2);
						$check = count($hotdates_test);
						if( $check < $check_2 )
						{
							$sql_base = ChotdatesTools::hotdates_past_hotdates_alike_all_sql_base($hotdate,$remove_id);
							$hotdates_test = ChotdatesTools::retrieve_from_sql_base($sql_base, $check_2);
							if($check_2==1  && count($hotdates_test)) $hotdates[1] = $hotdates_test[0];
						}
						else
						{
							if($check_2==1 && count($hotdates_test)) $hotdates[1] = $hotdates_test[0];
						}

					}
		        }

            	break;
        }

		#print_r($sql_base);

        $block_title = l('hotdates_' . $block_type);
        $html->setvar('block_title', $block_title);
    	$html->setvar('block_type', $block_type);
    	$html->setvar('browse_all_params', $browse_all_params);

		if(!isset($hotdates)) $hotdates = ChotdatesTools::retrieve_from_sql_base($sql_base, 2);
        $hotdate_n = 1;

        foreach($hotdates as $hotdate)
        {
            $html->setvar('hotdate_id', $hotdate['hotdate_id']);
            $html->setvar('hotdate_title', strcut(to_html($hotdate['hotdate_title']), 20));
            $html->setvar('hotdate_title_full', to_html($hotdate['hotdate_title']));

            $html->setvar('hotdate_n_comments', $hotdate['hotdate_n_comments']);
            $html->setvar('hotdate_n_guests', $hotdate['hotdate_n_guests']);
            $html->setvar('hotdate_place',  $hotdate['hotdate_place']);
            $html->setvar('hotdate_place_full', to_html($hotdate['hotdate_place']));

	        $html->setvar('hotdate_date', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_date')));
	        $html->setvar('hotdate_datetime_raw', to_html($hotdate['hotdate_datetime']));
	        $html->setvar('hotdate_time', to_html(Common::dateFormat($hotdate['hotdate_datetime'],'hotdates_hotdate_time')));

            $images = ChotdatesTools::hotdate_images($hotdate['hotdate_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);

            if($hotdate_n != count($hotdates))
                $html->parse("hotdate_" . $block_n . "_not_last");
            else
                $html->setblockvar("hotdate" . $block_n . "_not_last", '');

            $html->parse("hotdate_" . $block_n);

            ++$hotdate_n;
        }

    	$html->parse('block_' . $block_n);
    }
}


