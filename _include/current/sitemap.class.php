<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Sitemap {

    static $url;
    static $allUrls = array(
        'index.php', 'help.php', 'partner/index.php', 'contact.php',
        'news.php', 'forget_password.php', 'join.php', 'join2.php',
        'join3.php', 'music.php', 'vids.php', 'events.php',
        'places.php', 'groups.php', 'blogs.php', 'gallery_index.php',
        'forum.php', 'users_online.php', 'search.php', 'users_hon.php',
        'top5.php', 'adv.php', 'search_results.php', 'm/index.php',
        'm/join.php', 'm/forgot_password.php', 'users_birthdays.php',
        'users_new.php', 'search_advanced.php');
    static $notAvailableUrls = array();
    static $options = array(
        'partner/index.php' => 'partner',
        'contact.php' => '', 'news.php' => '', 'join2.php' => 'join|full',
        'music.php' => '', 'vids.php' => 'videogallery', 'events.php' => '',
        'places.php' => '', 'groups.php' => '', 'blogs.php' => '',
        'gallery_index.php' => 'gallery', 'forum.php' => '', 'top5.php' => '',
        'adv.php' => '', 'help.php' => '', 'users_online.php' => 'online_tab_enabled',
        'users_hon.php' => 'rating', 'users_birthdays.php' => 'birthdays_tab_enabled',
        'users_new.php' => 'new_tab_enabled', 'search_advanced.php' => 'adv_search'
    );
    static $urlsForGuests = array(
        'index.php', 'help.php', 'partner/index.php', 'contact.php',
        'news.php', 'forget_password.php', 'join', 'join2.php',
        'join3.php', 'm/index.php', 'm/join.php', 'm/forgot_password.php'
    );

    static function printMap() {
        global $g;
        self::$url = Common::urlSite();
        if (isset($g['template_options']['sitemap'])) {
            self::$notAvailableUrls = $g['template_options']['sitemap'];
        } else {
            self::$notAvailableUrls = array(
                'disabled' => array(
                    'pages_from_options' => array(),
                    'urls' => array(),
                ),
            );
        }

        $data = self::makeUrls(self::$notAvailableUrls['disabled']['pages_from_options'], self::$notAvailableUrls['disabled']['urls']);
        self::printSitemap(self::sortPages(self::preparePages($data)));
    }

    static function printSitemap($pages) {
        header('Content-Type: application/xml');
        $start_xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $end_xml = '</urlset>';
        echo $start_xml;

        $seoFriendlyUrls = array(
            'index.php' => 'index',
            'join.php' => 'join',
            'forget_password.php' => 'forget_password',
            'search_results.php' => 'search_results',
        );

        foreach ($pages as $page) {
            if(Common::getOptionSetTmpl() == 'urban' && isset($seoFriendlyUrls[$page])) {
                $page = $seoFriendlyUrls[$page];
            }
            echo '<url>' .
            '<loc>' . Sitemap::$url . Common::pageUrl($page) . '</loc>' .
            '</url>';
        }
        echo $end_xml;
    }

    static private function makeUrls($pages, $urls) {
        $resultUrls = array();
        foreach ($pages as $key => $option) {
            if (empty($option)) {
                $resultUrls[] = $key . '.php';
            } else {
                $resultUrls[] = $option;
            }
        }
        foreach ($urls as $url) {
            if (!in_array($url, $resultUrls)) {
                $resultUrls[] = $url;
            }
        }
        return $resultUrls;
    }

    static private function preparePages($notAvailableData) {
        if (!Common::isOptionActive('hide_site_from_guests')) {
            $resultUrls = array_diff(self::$allUrls, $notAvailableData);
        } else {
            $resultUrls = array_diff(self::$urlsForGuests, $notAvailableData);
        }
        foreach ($resultUrls as $key => $url) {
            if (isset(self::$options[$url])) {
                if (empty(self::$options[$url])) {
                    if (!Common::isOptionActive(substr($url, 0, strpos($url, '.')))) {
                        unset($resultUrls[$key]);
                    }
                } else {
                    if (strpos(self::$options[$url], '|')) {
                        if (Common::getOption(substr(self::$options[$url], 0, strpos(self::$options[$url], '|'))) != substr(self::$options[$url], strpos(self::$options[$url], '|') + 1)) {
                            unset($resultUrls[$key]);
                        }
                    } else {
                        if (!Common::isOptionActive(self::$options[$url])) {
                            unset($resultUrls[$key]);
                        }
                    }
                }
            }
        }
        return $resultUrls;
    }

    static private function sortPages($pages) {
        $mobile = array();
        $desktop = array();
        $partner = array();
        $result = array();

        foreach ($pages as $page) {

            if ($page == 'index.php') {
                $result[] = $page;
            } elseif (strpos($page, 'm/') === 0) {
                $mobile[] = $page;
            } elseif (strpos($page, 'partner/') === 0) {
                $partner[] = $page;
            } else {
                $desktop[] = $page;
            }
        }
        sort($mobile);
        sort($partner);
        sort($desktop);
        foreach ($desktop as $item) {
            $result[] = $item;
        }
        foreach ($mobile as $item) {
            $result[] = $item;
        }
        foreach ($partner as $item) {
            $result[] = $item;
        }
        return $result;
    }

}
