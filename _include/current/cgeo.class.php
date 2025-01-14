<?php
class CGeo extends CHtmlBlock
{
    public $location = null;
    public $isSearchList = false;

    public function parseCityMap(&$html)
    {
        global $g_user;

        $isNoCity = 0;
        if($this->location) {
            $country = $this->location['country'];
            $state = $this->location['state'];
            $city = $this->location['city'];
        } elseif (guid()) {
            $country = $g_user['country_id'];
            $state = $g_user['state_id'];
            $city = $g_user['city_id'];
            if (!$city && $country) {
                $cityInfo = IP::geoInfoCity();
                if (!empty($cityInfo)) {
                    $state = $cityInfo['state_id'];
                    $city = $cityInfo['city_id'];
                    $isNoCity = 1;
                }
            }
        }

        if ($country && $state && ($city || $this->isSearchList)) {
            $listCity = Common::listCities($state, $city, true);
            $html->setvar('list_options', $listCity);
            $html->setvar('list_options_js', addslashes($listCity));
            
            if($this->isSearchList){
                $html->parse('all_cities');
            }

            $html->setvar('ip_multiplicator', IP::MULTIPLICATOR);
            $html->setvar('selected_options', $city);

            $html->setvar('user_no_city', $isNoCity);
        }
        
        if(($country && $state && $city) || $this->isSearchList){
            $html->setvar('country_id', $country);
            $html->setvar('state_id', $state);
            $html->setvar('city_id', $city);
        }
    }

    function parseBlock(&$html)
	{
        $this->parseCityMap($html);

		parent::parseBlock($html);
	}
}
