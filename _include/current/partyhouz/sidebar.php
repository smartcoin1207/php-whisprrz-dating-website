<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CpartyhouzSidebar extends CHtmlBlock
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
		if(defined('DEMO_partyhouz')) $demo = true;

        switch($block_type)
        {
            case "most_discussed":
                $sql_base = CpartyhouzTools::partyhouz_most_discussed_sql_base();

				// DEMO
				if($demo)
				{
					$where = " e.partyhou_title='Avatar' AND ";
					$sql_base = CpartyhouzTools::partyhouz_most_discussed_sql_base($where);
					$partyhouz_demo = CpartyhouzTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($partyhouz_demo[0]))
                        $partyhouz[0] = $partyhouz_demo[0];

					$where = " e.partyhou_title='NickelBack' AND ";
					$sql_base = CpartyhouzTools::partyhouz_most_discussed_sql_base($where);
					$partyhouz_demo = CpartyhouzTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($partyhouz_demo[0]))
                        $partyhouz[1] = $partyhouz_demo[0];
				}

            	break;
            case "most_anticipated":
                $sql_base = CpartyhouzTools::partyhouz_most_anticipated_sql_base();
                break;
            case "popular_finished":
                $sql_base = CpartyhouzTools::partyhouz_popular_finished_sql_base();

				// DEMO
				if($demo)
				{
					$where = " e.partyhou_title='Norah Jones' AND ";
					$sql_base = CpartyhouzTools::partyhouz_popular_finished_sql_base($where);
					$partyhouz_demo = CpartyhouzTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($partyhouz_demo[0]))
                        $partyhouz[0] = $partyhouz_demo[0];

					$where = " e.partyhou_title='Carrie Underwood' AND ";
					$sql_base = CpartyhouzTools::partyhouz_popular_finished_sql_base($where);
					$partyhouz_demo = CpartyhouzTools::retrieve_from_sql_base($sql_base, 1);
                    if (isset($partyhouz_demo[0]))
                        $partyhouz[1] = $partyhouz_demo[0];
				}

                break;
            case "partyhou_show":
		        $partyhou_id = get_param('partyhou_id');
		        $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);

		        $browse_all_params = "?partyhou_id=" . $partyhou['partyhou_id'];

		        if(CpartyhouzTools::is_partyhou_finished($partyhou))
		        {
		        	$block_type = l('coming_partyhouz');
		        	$sql_base = CpartyhouzTools::partyhouz_coming_partyhouz_sql_base($partyhou);

					// SHOW always 2 partyhouz if nothing found

					$partyhouz_test = CpartyhouzTools::retrieve_from_sql_base($sql_base, 2);
					$check = count($partyhouz_test);

					$remove_id[0] = 0;

					if( $check < 2 )
					{
						if($check==1) {
						$partyhouz[0] = $partyhouz_test[0];
						$remove_id[] = $partyhouz[0]['partyhou_id'];
						}

						$check_2 = 2 - $check;
						// remove previous ID
						$sql_base = CpartyhouzTools::partyhouz_coming_partyhouz_category_sql_base($partyhou,$remove_id);
						$partyhouz_test = CpartyhouzTools::retrieve_from_sql_base($sql_base, 2-$check_2);
						$check = count($partyhouz_test);
						if( $check < $check_2 )
						{
							$sql_base = CpartyhouzTools::partyhouz_coming_partyhouz_all_sql_base($partyhou,$remove_id);
							$partyhouz_test = CpartyhouzTools::retrieve_from_sql_base($sql_base, $check_2);
							if($check_2==1 && count($partyhouz_test)) $partyhouz[1] = $partyhouz_test[0];
						}
						else
						{
							if($check_2==1 && count($partyhouz_test)) $partyhouz[1] = $partyhouz_test[0];
						}

					}

		        }
		        else
		        {
		        	$block_type = l('past_partyhouz_alike');
		        	$sql_base = CpartyhouzTools::partyhouz_past_partyhouz_alike_sql_base($partyhou);

					// SHOW always 2 partyhouz if nothing found

					$partyhouz_test = CpartyhouzTools::retrieve_from_sql_base($sql_base, 2);
					$check = count($partyhouz_test);

					$remove_id[0] = 0;

					if( $check < 2 )
					{
						if($check==1) {
						$partyhouz[0] = $partyhouz_test[0];
						$remove_id[] = $partyhouz[0]['partyhou_id'];
						}

						$check_2 = 2 - $check;
						// remove previous ID
						$sql_base = CpartyhouzTools::partyhouz_past_partyhouz_alike_category_sql_base($partyhou,$remove_id);
						$partyhouz_test = CpartyhouzTools::retrieve_from_sql_base($sql_base, 2-$check_2);
						$check = count($partyhouz_test);
						if( $check < $check_2 )
						{
							$sql_base = CpartyhouzTools::partyhouz_past_partyhouz_alike_all_sql_base($partyhou,$remove_id);
							$partyhouz_test = CpartyhouzTools::retrieve_from_sql_base($sql_base, $check_2);
							if($check_2==1  && count($partyhouz_test)) $partyhouz[1] = $partyhouz_test[0];
						}
						else
						{
							if($check_2==1 && count($partyhouz_test)) $partyhouz[1] = $partyhouz_test[0];
						}

					}
		        }

            	break;
        }

		#print_r($sql_base);

        $block_title = l('partyhouz_' . $block_type);
        $html->setvar('block_title', $block_title);
    	$html->setvar('block_type', $block_type);
    	$html->setvar('browse_all_params', $browse_all_params);

		if(!isset($partyhouz)) $partyhouz = CpartyhouzTools::retrieve_from_sql_base($sql_base, 2);
        $partyhou_n = 1;

        foreach($partyhouz as $partyhou)
        {
            $html->setvar('partyhou_id', $partyhou['partyhou_id']);
            $html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), 20));
            $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));

            $html->setvar('partyhou_n_comments', $partyhou['partyhou_n_comments']);
            $html->setvar('partyhou_n_guests', $partyhou['partyhou_n_guests']);

	        $html->setvar('partyhou_date', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_date')));
	        $html->setvar('partyhou_datetime_raw', to_html($partyhou['partyhou_datetime']));
	        $html->setvar('partyhou_time', to_html(Common::dateFormat($partyhou['partyhou_datetime'],'partyhouz_partyhou_time')));

            $images = CpartyhouzTools::partyhou_images($partyhou['partyhou_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);

            if($partyhou_n != count($partyhouz))
                $html->parse("partyhou_" . $block_n . "_not_last");
            else
                $html->setblockvar("partyhou" . $block_n . "_not_last", '');

            $html->parse("partyhou_" . $block_n);

            ++$partyhou_n;
        }

    	$html->parse('block_' . $block_n);
    }
}


