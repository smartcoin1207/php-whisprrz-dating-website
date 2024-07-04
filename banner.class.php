<?php
class CBanner extends CHtmlBlock
{
    public $type = 'home';

    public function setType($type)
    {
        $this->type = $type;
    }

    static public function getBannerHtml($banner)
    {
        global $g;
        $blockHtml = '';
        if(Common::isApp() && Common::isOptionActive('use_only_admob_in_apps')) {
            if(strpos($banner['place'], 'admob_') === 0) {
                $blockHtml = $banner['code'];
            }
        } else {
            if ($banner['type'] == 'flash') {
                $blockHtml =  User::flashBanner($banner['filename'], $banner['width'], $banner['height']);
            } elseif ($banner['type'] == 'code') {
                $blockHtml = $banner['code'];
            } else {
                $bannerPatch = $g['path']['url_files'] . 'banner/' . str_replace(' ', '_', $banner['filename']);
                $blockHtml = "<a target=\"_blank\" href=\"" . $banner['url'] . "\"><img src=\"" . $bannerPatch . "\" alt=\"" . $banner['alt'] . "\" /></a>";
            }
        }
        return  $blockHtml;
    }

    static public function getHtml($place)
    {
        global $g;

        $tmpl = to_sql(Common::getOption('tmpl_loaded', 'tmpl'), 'Plain');
        $lang = Common::getOption('lang_loaded', 'main');

        $sql = 'SELECT `type` FROM `banners_places`
                 WHERE `place` = ' . to_sql($place) . '
                   AND `active` = 1';
        $type = DB::result($sql, 0, DB_MAX_INDEX, true);

        $where = ' `place` = ' . to_sql($place) . '
               AND `active` = 1
               AND (templates LIKE "%' . $tmpl . '%" OR templates = "")
               AND (langs LIKE "%' . $lang . '%" OR langs = "")';

        $sql = 'SELECT COUNT(*) FROM `banners` WHERE ' . $where;
        $count = DB::result($sql, 0, DB_MAX_INDEX, true);

        if($count == 0) {
            return false;
        }

        /* OFF because when banners have no equitable distribution of IDs
         * will be shown only few of them
        $sql = 'SELECT MAX(`id`) FROM `banners` WHERE ' . $where;
        $maxId = to_sql(DB::result($sql, 0, DB_MAX_INDEX, true), 'Number');

        if(!$maxId) {
            return false;
        }
         */

        $numberBannersPlace = intval(Common::getOption('number_banners_place_' . $place, 'template_options'));
        if($type == 'static') {
            $sql = "SELECT *
                      FROM `banners`
                     WHERE $where LIMIT 1";
        } elseif($type == 'random') {

            //$where .= ' AND `id` >= ' . rand(0, $maxId);
            $limitStart = rand(0, $count - 1);

            if ($numberBannersPlace) {
                $sql = "SELECT *
                          FROM `banners`
                         WHERE {$where}
                         LIMIT $limitStart, " . to_sql($numberBannersPlace, 'Number');
            } else {
                $sql = "SELECT *
                          FROM `banners`
                         WHERE $where LIMIT $limitStart, 1";
            }
        } else {
            return false;
        }

        DB::query($sql);
        if (DB::num_rows() == 0) {
            return false;
        }

        $result = array();
        if ($numberBannersPlace) {
            while ($row = DB::fetch_row()) {
                $result[] = self::getBannerHtml($row);
            }
        } else {
            $banner = DB::fetch_row();
            $result = array(self::getBannerHtml($banner));
        }

        return $result;
    }

    static public function getAllowBannerSql($prefix = '', $flag = 0)
	{
        $allowBanner = Common::getOption('banners_places', 'template_options');
        $whereNotAllow = '';
        $where = '';
        if (!empty($prefix)){
            $prefix .= '.';
        }
        if (countFrameworks('mobile') == 0 || Common::isOptionActive('no_mobile_template', 'template_options')) {
            $where = $prefix."place NOT LIKE '%mobile%' ";
        } else {
            if (!Common::isOptionActive('banner_header_mobile', 'template_options_mobile')) {
                $where = " ".$prefix."place != 'header_mobile'";
            }
        }

        if (countFrameworks('main') == 0) {
            $where = $prefix."place LIKE '%mobile%' ";
        } else {
            if (is_array($allowBanner) && !empty($allowBanner)) {
                $placeNot = array();
                foreach ($allowBanner as $pl => $is) {
                    if (!$is) {
                        $placeNot[] = to_sql($pl);
                        $placeNot[] = to_sql("{$pl}_paid");
                    }
                }
                if (!empty($placeNot)) {
                    $whereNotAllow = $prefix.'place NOT IN (' . implode(',', $placeNot) . ')';
                }
            }
        }

        if ($whereNotAllow != '') {
            $where .= ($where != '') ? " AND {$whereNotAllow}" : $whereNotAllow;
        }

        if (Common::isOptionActiveTemplate('not_show_banner_paid')) {
            $where .= $where != '' ? ' AND ' : '';
            $where .= $prefix . "place NOT LIKE '%_paid'";
        }
        if ($where != '') {
            if ($flag == 0){
                $where = ' WHERE ' . $where;
            } elseif($flag == 1) {
                $where = ' AND ' . $where;
            }
        }
        return $where;

    }

    static public function getBlock(&$html, $type, $prf = '')
	{
        global $p;
	global $g;
        global $g_user;

        $optionNameTmpl = Common::getOption('name', 'template_options');
        if ($optionNameTmpl == 'impact' && $p == 'city.php') {
            return false;
        }

        $pos = $type;
        $block = 'banner_' . trim($type) . $prf;
        $isParseBanner = false;
        if ($html->blockexists($block)) {
            $class = str_replace('.php', '', $p);
            $optionSetTmpl = Common::getOption('set', 'template_options');
            // Mobile Urbana
            if ($optionSetTmpl == 'urban') {
                $display = get_param('display');
                if ($p == 'profile_settings.php') {
                    if ($display) {
                        $class .= "_{$display}";
                    }
                } elseif ($p == 'upgrade.php' && $g_user) {
                    $action = get_param('action');
                    if (!$action && User::isSuperPowers()) {
                        $class .= "_activated_super_power";
                    }
                } elseif ($p == 'search_results.php' && $optionNameTmpl == 'impact'
                            && ($display == 'profile' || $display == 'encounters')) {
                    $class = 'profile_view';
                }
            }
            // Mobile Urbana
            if (!Common::isOptionActiveTemplate('not_show_banner_paid') && User::isPaid(guid())) {
                $type = $type . '_paid';
            }
            $banners = self::getHtml($type);
            if ($banners !== false) {
                foreach ($banners as $baner) {
                    $html->setvar($block, $baner);
			//nnsscc-diamond-20200420-start
					if($type=="right_column"){
						DB::query("SELECT * FROM wowslider WHERE user_id='".$g_user['user_id']."' and NOW()>from_datetime AND NOW()<end_datetime ORDER BY event_id DESC");
						$no=0;
						while($image = DB::fetch_row())
						{
							$banner_val = "<a target='_blank' href='".$image["link"]."'><img src='".$g['path']['url_files'] . "wowslider/" . $image['img_path']."' style='width: 155px; height: 78px;' alt='".$image['title']."'></a>";
                            echo $banner_val;

                            if($no==0){	
                                
								$html->setvar("banner_right_column",$banner_val);
							}else{
								break;
							}
							$no++;
						}
										
					}
					//nnsscc-diamond-20200420-end
                    $html->setvar("{$block}_class", $type . '_' . $class);
                    $html->parse($block, true);
                    $isParseBanner = true;
                }
            }
            if ($isParseBanner) {
                $isHide = false;
                if ($html->blockExists($block . '_list_hide') && guid()) {
                    if(Common::isActiveFeatureSuperPowers('kill_the_ads')) {
                        if (User::isSuperPowers()) {
                            $ads = json_decode($g_user['hide_ads'], true);
                            if ($ads && isset($ads[$optionNameTmpl]) && $ads[$optionNameTmpl]) {
                                $isHide = true;
                                $html->clean($block);
                                $html->parse($block . '_list_hide', false);
                            } elseif ($html->blockExists($block . '_icon_hide')) {
                                $html->parse($block . '_icon_hide', false);
                            }
                        }
                        $html->setvar($block . '_title', $isHide ? l('show_ads') : l('remove_ads'));
                        if ($isHide) {
                            $html->parse($block . '_show', false);
                        } else {
                            $html->clean($block . '_show');
                        }
                        $html->parse($block . '_list_action');
                    }
                }
                if ($html->blockexists($block . '_list')) {
                    $html->parse($block . '_list', false);
                }
                if (($optionNameTmpl == 'impact' || $optionNameTmpl == 'edge') && $html->blockexists("{$block}_bl")) {
                    $isParseGeneralBl = true;
                    if ($optionNameTmpl == 'edge') {
                        if (guid() && $isHide && in_array($type, array('footer_paid', 'footer'))) {
                            $isParseGeneralBl = false;
                        }
                    } else {
                        if (!guid() && !in_array($p, array('about.php', 'contact.php', 'page.php'))) {
                            $isParseGeneralBl = false;
                        }
                        if (guid() && $isHide && in_array($type, array('header_paid', 'header'))) {
                            $isParseGeneralBl = false;
                        }
                    }
                    if ($isParseGeneralBl) {
                        $html->parse("{$block}_bl", false);
                    }
                }
            } else {
                $html->setvar("{$block}_class", 'empty');
            }

        }
        return $isParseBanner;
	}

    static public function getBlockAll(&$html, $types)
	{
        $banners = explode(',', $types);
        if (is_array($banners)) {
            foreach ($banners as $type) {
                self::getBlock($html, $type);
            }
        }
	}

	function parseBlock(&$html)
	{
        self::getBlock($html, $this->type);
        parent::parseBlock($html);
	}

    static function isAdmobVisible($html)
    {
        $block = 'admob_banner_status';
        if(Common::isApp() && $html->blockExists($block)) {

            $isAdmobVisible = 'true';

            if(Common::isActiveFeatureSuperPowers('kill_the_ads')) {
                if (User::isSuperPowers()) {
                        $tmplName = Common::getTmplName();
                        $ads = json_decode(guser('hide_ads'), true);
                        if ($ads && isset($ads[$tmplName]) && $ads[$tmplName]) {
                            $isAdmobVisible = 'false';
                    }
                }
            }

            $html->setvar('isAdmobBannerVisible', $isAdmobVisible);
            $html->parse($block);
        }
    }

}