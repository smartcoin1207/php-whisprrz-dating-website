<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class TimeZone
{
    static $debug = false;

    public static function mysqlFormat($timeZone)
    {
        $time = new DateTime(NULL, new DateTimeZone($timeZone));

        return $time->format('P');
    }

    public static function getTimeZoneOptionsSelect($selectedZone = NULL, $firstItem=null, $continentEnabled=true)
    {
        $regions = array(
            'Africa' => DateTimeZone::AFRICA,
            'America' => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Arctic' => DateTimeZone::ARCTIC,
            'Aisa' => DateTimeZone::ASIA,
            'Atlantic' => DateTimeZone::ATLANTIC,
            'Australia' => DateTimeZone::AUSTRALIA,
            'Europe' => DateTimeZone::EUROPE,
            'Indian' => DateTimeZone::INDIAN,
            'Pacific' => DateTimeZone::PACIFIC,
        );

        $skipZone = array(
            'Pacific/Chatham',
            'Pacific/Fakaofo',
            'Pacific/Kiritimati',
            'Pacific/Apia',
        );

        if($firstItem===null){
            $firstItem = l('choose_a_city');
        }

        if($firstItem!==false){
            $emptyValue = ' ';
            global $sitePart;
            if($sitePart === 'administration') {
                $emptyValue = '';
            }
            $structure = '<option value="' . $emptyValue . '" '.(!$selectedZone?'selected="selected"':'').'>' . $firstItem . '</option>';
        }

        foreach ($regions as $mask) {
            $zones = DateTimeZone::listIdentifiers($mask);
            $zones = self::prepareZones($zones);

            foreach ($zones as $zone) {
                $continent = $zone['continent'];
                $city = $zone['city'];
                $subcity = $zone['subcity'];
                $p = $zone['p'];
                $timeZone = $zone['time_zone'];
                if (in_array($timeZone, $skipZone)) {
                    continue;
                }
                if (self::$debug) {
                    echo $timeZone . '<br>';
                    self::setTimeZone($timeZone);
                }
                if($continentEnabled){
                    if (!isset($selectContinent)) {
                        $structure .= '<optgroup style="font-style: normal;" label="'.$continent.'">';
                    }
                    elseif ($selectContinent != $continent) {
                        $structure .= '</optgroup><optgroup style="font-style: normal;" label="'.$continent.'">';
                    }
                }
                if ($city) {
                    if ($subcity) {
                        $city = $city . '/'. $subcity;
                    }
                    $structure .= "<option style=\"padding-left:15px;\" ".(($timeZone == $selectedZone) ? 'selected="selected "':'') . " value=\"".($timeZone)."\">(".$p. " UTC) " .str_replace('_',' ',$city)."</option>";
                }

                $selectContinent = $continent;
            }
        }

        if($continentEnabled){
            $structure .= '</optgroup>';
        }

        return $structure;
    }

    public static function isTimeZoneExists($timeZone)
    {
        $allZones = timezone_identifiers_list();

        return in_array($timeZone, $allZones);
    }

    public static function sort($a, $b)
    {
        if ($a['p'] < 0 || $b['p'] < 0) {
            return ($a['p'] > $b['p']) ? -1 : 1;
        } else {
            return ($a['p'] < $b['p']) ? -1 : 1;
        }
    }

    private static function prepareZones(array $timeZones)
    {
        $zones = array();
        foreach ($timeZones as $zone) {
            $time = new DateTime('now', new DateTimeZone($zone));
            $p = $time->format('P');
            $parts = explode('/', $zone);

            $offsetParts = explode(':', $p);
            $hours = intval($offsetParts[0]);
            $minutes = intval($offsetParts[1]);
            if($hours > 0) {
                $offset = $hours * 60 + $minutes;
            } else {
                $offset = $hours * 60 - $minutes;
            }

            $zones[] = array(
                'time_zone' => $zone,
                'continent' => isset($parts[0]) ? $parts[0] : '',
                'city' => isset($parts[1]) ? $parts[1] : '',
                'subcity' => isset($parts[2]) ? $parts[2] : '',
                'p' => $p,
                'offset' => $offset,
            );
        }

        $columnOffset = array();
        $columnCity = array();

        foreach($zones as $key => $zone) {
            $columnOffset[$key] = $zone['offset'];
            $columnCity[$key] = $zone['city'];
        }

        array_multisort($columnOffset, SORT_ASC, $columnCity, SORT_ASC, $zones);

        return $zones;
    }

    public static function getDateTimeZone($setZone)
    {
        if (!empty($setZone)) {
            $zone = new DateTimeZone($setZone);
            $timeZone = new DateTime('', $zone);
            $timeZone = $timeZone->format('Y-m-d H:i:s');
        } else {
            $timeZone = new DateTime();
            $timeZone = $timeZone->format('Y-m-d H:i:s');
        }
        return $timeZone;
    }

    public static function setTimeZone($timezoneName = null) {
        if ($timezoneName === null) {
            $timezoneName = Common::getOption('timezone', 'main');
        }
        $defaultTimeZone = date_default_timezone_get();
        if (!empty($timezoneName) && $defaultTimeZone != $timezoneName) {
            @date_default_timezone_set($timezoneName);
            $sql = 'SET time_zone = "' . date('P') . '"';
            DB::execute($sql);
        }
    }
}