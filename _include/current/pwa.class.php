<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

use Tymon\JWTAuth\Claims\Custom;

class PWA {

    static public function parseHeader(&$html)
    {
        if(Common::isIosDevice()) {
            $version = Common::getIosVersion();
            if($version) {
                $versionParts = explode('.', $version);
                if($versionParts[0] < 12 || ($versionParts[0] == 12 && $versionParts[1] < 2)) {
                    $html->parse('pwa_ios_old');
                }
            }
        }
    }

    static public function printManifest()
    {
        $backgroundColor = TemplateEdge::getHeaderBackgroundColor();

        $manifest = array(
            'name' => Common::getOption('title', 'main'),
            'start_url' => 'index.php?mobile_app_type=pwa',
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $backgroundColor,
            'icons' => array(
                0 => array(
                    'src' => self::getUrlIcon(),
                    'type' => 'image/png'
                ),
            )
        );

        echo defined('JSON_UNESCAPED_UNICODE') ? json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : json_encode($manifest);

    }

    static public function setModePwa()
    {
        $key = 'is_pwa_app';
        $isPwa = get_param('mobile_app_type') == 'pwa' || get_session($key);
        set_session($key, $isPwa);
    }

    static public function isModePwa()
    {
        $key = 'is_pwa_app';

        $isPwa = Cache::get($key);
        if ($isPwa === null) {
            $isPwa = get_param('mobile_app_type') == 'pwa' || get_session($key);
            Cache::add($key, $isPwa);
        }
        return $isPwa;
    }

    static public function isModePwaIos()
    {
        return self::isModePwa() && Common::isIosDevice();
    }

    static public function getUrlIcon()
    {
        global $g;

        $urlIcon = Common::getOption('url_tmpl_main', 'tmpl') . 'images/icon_pwa.png';
        $iconName = 'logo/icon_pwa_' . $g['tmpl']['tmpl_loaded'] . '.png';
        if (file_exists($g['path']['dir_files'] . $iconName)) {
            $urlIcon = $g['path']['url_files'] . $iconName;
        }
        $urlIcon .=  Common::getOption('cache_version_param', 'site_cache');

        return $urlIcon;
    }

}
