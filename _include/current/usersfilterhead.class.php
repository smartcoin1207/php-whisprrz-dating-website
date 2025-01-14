<?php

class UsersFilterHead extends CHtmlBlock {

    static function parseHeaderFilter(&$html){
        global $p;

        $display = get_param('display');
        $uid = guid();
        $filtersInfo = array();
        if ($uid) {
            $filters = guser('user_search_filters');
            if($filters) {
                $filtersInfo = json_decode($filters, true);
            }
        }
        if (!isset($filtersInfo['status']) || !$filtersInfo['status']['value']){
            $filtersInfo['status']['value'] = 'all';
        }
        $html->setvar('module_search_status_title', l('search_show_' .$filtersInfo['status']['value'] ));
        $html->parse('module_search_status_' . $filtersInfo['status']['value'], false);

		//if (!Common::isOptionActive('no_profiles_without_photos_search')) {
			if (!$display) {
				if (!isset($filtersInfo['with_photo'])){
					$filtersInfo['with_photo']['value'] = 1;
				}
				if (intval($filtersInfo['with_photo']['value'])){
					$html->parse('module_search_with_photo', false);
				} else {
                    $html->parse('module_search_with_photo_no_check', false);
                }
                $html->parse('module_search_with_photo_param', false);
			} else {
				if (!isset($filtersInfo['with_photo'])){
					$filtersInfo['with_photo']['value'] = 1;
				}
                $html->setvar('module_search_with_photo_no_search_value', intval($filtersInfo['with_photo']['value']));
				$html->parse('module_search_with_photo_no_search', false);
			}

		//}
    }

    public function parseBlock(&$html)
    {
        global $p;

        self::parseHeaderFilter($html);

        parent::parseBlock($html);
    }


}