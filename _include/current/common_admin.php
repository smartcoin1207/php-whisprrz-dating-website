<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$isTmplModern = Common::isAdminModer();
$actionParam = get_param("action");

if ($actionParam == "saved" || $actionParam == "changes_send") {
    if ($isTmplModern) {
        $msg = l('changes_saved');
        if ($actionParam == "changes_send") {
            $msg = l('changes_send');
        }
        $l[$p]['changes_save'] = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>' . $msg;
    } elseif ($actionParam == "saved") {
        $l[$p]['title_current'] = '<div class="thumb_left"><div class="thumb_right"><div class="thumb">' . l('changes_saved') . '</div></div></div>' . l('title_current');
        $l[$p]['changes_save'] = '<div class="thumb_left"><div class="thumb_right"><div class="thumb">' . l('changes_saved') . '</div></div></div>';
    }
}
if (get_param("action") == "delete") {
    if ($isTmplModern) {
        $l[$p]['changes_delete'] = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>' . l('changes_deleted');
    } else {
        $l[$p]['title_current'] = '<div class="thumb_left"><div class="thumb_right"><div class="thumb">' . l('changes_deleted') . '</div></div></div>' . l('title_current');
        $l[$p]['changes_save'] = '<div class="thumb_left"><div class="thumb_right"><div class="thumb">' . l('changes_deleted') . '</div></div></div>';
    }
}

define('ADMINISTRATOR', true);

class CAdminHeader extends CHtmlBlock
{

    var $message_template = "";
    static public $linkToMainMenuAdmin = array();

    protected $linkToMainMenu = array(
        'news' => 'help_topic.php',
    );

    protected $linkToMainMenuTmpl = array(
        'urban' => array(
            'news' => 'popup_pages.php',
        )
    );
    protected $linkToMainMenuTmplName = array();

    protected $secondaryMenuItems = array(
        'news',
        'help',
        'pages',
        'blogs_bloggers',
        'places_results',
        //   'music_musicians', //Rade 2023-09-28 delete
        'flashchat_rooms',
        '3dchat_rooms',
        //   'vids_videos', //Rade 2023-09-28 delete
        'adv',
        'groups_groups',
        'events_events',
        'hotdates_hotdates',
        'partyhouz_partyhouz',
        'forum_categories',
        'games',
        'stickers',
        'gifts',
        'cityrooms',
        'albums',
        'posting_popup',

        'popup_pages',
        'groups_social_videos',
        'groups_social_music',
        'groups_social'
    );

    protected $notAvailableItems = array(
        'urban' => array(
            //   'news', // rade 2023-09-20 delete
            //   'help', // rade 2023-09-20 delete
            'pages',
            // rade 2023-09-20 add
            //   'places_results', // rade 2023-09-20 delete
            //   '3dchat_rooms', // rade 2023-09-20 delete
            //   'adv', // rade 2023-09-20 delete
            //   'groups_groups', // rade 2023-09-20 delete
            //   'events_events', // rade 2023-09-20 delete
            //   'forum_categories', // rade 2023-09-20 delete
            //   'games', // rade 2023-09-20 delete
            //   'albums', // rade 2023-09-20 delete
        ),
        'old' => array(
            'stickers',
            'gifts',
            'pages',
            'groups_social',
            'groups_social_videos',
            'groups_social_music',
            'flashchat_rooms',
        )
    );

    protected $notAvailableItemsTemplate = array(
        'impact' => array('stickers', 'gifts', 'groups_social', 'groups_social_videos', 'groups_social_music', 'blogs_bloggers', 'music_musicians'),
        'urban' => array('stickers', 'groups_social', 'groups_social_videos', 'groups_social_music', 'blogs_bloggers', 'music_musicians'),
        'edge' => array('gifts', )
    );



    function setLinkMainMenu(&$html)
    {
        $tmplOptionSet = Common::getOption('set', 'template_options');
        if (!$tmplOptionSet) {
            $tmplOptionSet = 'old';
        }
        $tmplOptionName = Common::getOption('name', 'template_options');
        foreach ($this->linkToMainMenu as $key => $value) {
            if (isset($this->linkToMainMenuTmplName[$tmplOptionName][$key])) {
                $value = $this->linkToMainMenuTmplName[$tmplOptionName][$key];
            } elseif (isset($this->linkToMainMenuTmpl[$tmplOptionSet][$key])) {
                $value = $this->linkToMainMenuTmpl[$tmplOptionSet][$key];
            }
            self::$linkToMainMenuAdmin[$key] = $value;
            $html->setvar("main_menu_link_to_{$key}", $value);
        }

    }

    function parseItemMenu(&$html, $type = 'secondary')
    {
        $tmplOptionSet = Common::getOption('set', 'template_options');
        if (!$tmplOptionSet) {
            $tmplOptionSet = 'old';
        }

        /*if(get_session('set_city_module') != 'on') {
            $this->notAvailableItems[$tmplOptionSet][] = 'cityrooms';
        }*/

        $notAvailableItems = array();
        if (isset($this->notAvailableItems[$tmplOptionSet])) {
            $notAvailableItems = $this->notAvailableItems[$tmplOptionSet];
        }
        $tmplOptionName = Common::getOption('name', 'template_options');
        if ($tmplOptionName) {
            if (isset($this->notAvailableItemsTemplate[$tmplOptionName])) {
                $notAvailableItems = array_merge($notAvailableItems, $this->notAvailableItemsTemplate[$tmplOptionName]);
            }
        }

        $items = "{$type}MenuItems";
        $menuItems = $this->$items;
        foreach ($menuItems as $key => $item) {
            if (!in_array($item, $notAvailableItems)) {
                $html->parse("menu_{$type}_{$item}", false);
            }
        }
    }

    function getActiveMenuItem()
    {
        global $p;
        //city.php = city_logo.php
        $activeMenu = '';
        $adminMenuItems = array(
            'scityrooms' => array('city_rooms.php', 'city_cache.php', 'city_options.php', 'city_video.php', 'city_gallery.php', 'city_whole_world.php', 'city_platform.php', 'city.php'),
            'sipblock' => array('ipblock.php', 'ban_users.php', 'users_reports.php', 'users_reports_content.php', 'users_reports_wall_post.php'),
            'sgroupssocial' => array('groups_social.php', 'groups_group_comments.php', 'groups_categories.php', 'groups_category_add.php', 'groups_social_edit.php', 'groups_social_pages.php', 'groups_social_photo.php', 'groups_social_video.php', 'groups_social_reports.php', 'groups_social_reports_content.php', 'groups_social_reports_wall_post.php'),
            'sgroupssocialvideos' => array('groups_social_vids_groups.php', 'groups_social_vids_videos.php', 'groups_social_vids_groups_comments.php', 'groups_social_vids_groups_videos.php', 'groups_social_vids_comments.php', 'groups_social_vids_video_edit.php'),
            'sgroupssocialmusic' => array('groups_social_music_songs.php')
        );

        foreach ($adminMenuItems as $key => $pages) {
            if (in_array($p, $pages)) {
                $activeMenu = $key;
                break;
            }
        }
        return $activeMenu;
    }

    function parseBlock(&$html)
    {
        global $g;
        global $gm;
        global $gc;
        global $l;
        global $xajax;
        global $p;

        $this->setLinkMainMenu($html);
        $this->parseItemMenu($html);

        if ($html->blockExists('main_menu_compact')) {
            $html->setvar('header_url_logo_inner', Common::getUrlLogo('logo', 'administration', 'inner', true));
            $isMenuCompact = intval(get_cookie('admin_menu_main_collapse', true));
            if ($isMenuCompact) {
                $html->setvar('main_menu_compact', 1);
                $html->parse('main_menu_compact', false);
            }
        }

        $html->setvar('header_favicon', Common::getfaviconSiteHtml());
        $html->setvar('header_url_logo', Common::getUrlLogo('logo', 'administration'));
        if ($html->varExists('site_title')) {
            $html->setvar('site_title', $g['main']['title']);
        }

        // All templates 'menu_admin_banner' = 'Y'
        if (Common::isOptionActive('menu_admin_banner', 'template_options')) {
            $html->parse('menu_admin_banner');
            $html->parse('menu_admin_template_banner');
        }

        if (!empty($xajax) and $p != 'flashchat.php') {
            $sJsFile = 'xajax_js/xajax.js' . $g["site_cache"]["cache_version_param"];
            $html->setvar('xajax_js', $xajax->getJavascript($g['path']['url_main'] . '_server/', $sJsFile));
        }

        if (isset($gm) and $gm) {
            $html->parse("logom", true);
        } elseif (isset($gc) and $gc) {
            $html->parse("logoc", true);
        } else {
            $html->parse("logo", true);
        }

        if (get_session("admin_auth") == 'Y' || get_session("replier_auth") == 'Y') {
            $adminLastLogin = get_session('admin_last_login');

            if (!$adminLastLogin) {
                $sql = 'SELECT * FROM admin_login WHERE success = "Y"
                    ORDER BY id DESC LIMIT 1,1';
                $row = DB::row($sql);
                if ($row) {
                    $adminLastLogin = $row['time'];
                } else {
                    $adminLastLogin = 'never';
                }
                set_session('admin_last_login', $adminLastLogin);
            }
            if ($adminLastLogin == 'never') {
                $adminLastLogin = l('never');
            }
            $html->setvar('last_login', $adminLastLogin);
            if (Common::isMultisite()) {
                $html->parse('support_multisate');
            } else {
                $html->parse('support_no_multisate');
            }
            if (get_session("admin_auth") == 'Y') {
                $html->setvar('replier_name', get_session("replier_name"));
                $html->parse('auth', true);
            }
        }

        $dir = $g['path']['dir_lang'] . "admin/";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($dir . $file) and $file != "." and $file != "..") {
                        $html->setvar("language_value", $file);
                        $html->setvar("language_title", ucfirst($file));
                        $html->parse("language", true);
                    }
                }
                closedir($dh);
            }
        }
        $dir = $g['path']['dir_tmpl'] . "admin/";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($dir . $file) and $file != "." and $file != "..") {
                        $html->setvar("template_value", $file);
                        $html->setvar("template_title", ucfirst($file));
                        $html->parse("template", true);
                    }
                }
                closedir($dh);
            }
        }
        $year = strftime("%Y");
        $html->setvar("year", $year);

        $domainPath = '';
        $uriParts = explode('/', $_SERVER['REQUEST_URI']);

        if (count($uriParts) > 3) {
            array_pop($uriParts);
            array_pop($uriParts);
            $domainPath = implode('/', $uriParts) . '/';
        }

        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $html->setvar("domain_name", getDomainName());

        $template = '';
        if (
            isset($g['tmpl']['main'])
            && !empty($g['tmpl']['main'])
            && is_dir($g['tmpl']['dir_tmpl_main'])
        ) {
            $template .= mb_ucfirst(l($g['tmpl']['main']));
        }
        if (
            isset($g['tmpl']['mobile'])
            && !empty($g['tmpl']['mobile'])
            && is_dir($g['tmpl']['dir_tmpl_mobile'])
            && Common::getOptionTemplate('name') != 'edge'
        ) {
            if ($template != '') {
                $template .= ', ';
            }
            $template .= mb_ucfirst(l($g['tmpl']['mobile'])) . ' ' . l('mobile_templates');
        }
        if (Common::isOptionActive('maintenance')) {
            $html->parse('mode_maintenance');
        }
        if ($template != '') {
            $html->setvar('template_active', $template);
            $html->parse('template_active');
        }

        if ($html->blockExists('header_language')) {
            Common::parseDropDownListLanguage($html, 'header_language', 'header_language_item', 'administration');
        }

        $siteVersion = 'DEV';
        $siteVersionFile = $g['path']['dir_main'] . '_server/version.txt';
        if (file_exists($siteVersionFile) && is_readable($siteVersionFile)) {
            $siteVersion = trim(file_get_contents($siteVersionFile));
        }
        $html->setvar('site_version', $siteVersion);

        $menu = "shome";
        $request = $_SERVER['REQUEST_URI'];
        $arr = explode("/", $request);
        $request = $arr[sizeof($arr) - 1];
        $arr = explode("?", $request);
        $filename = $arr[0];
        if (($filename == "language.php") or ($filename == "language_edit.php")) {
            $part = get_param("part", "main");
            $menu = "slanguage_" . $part;
        } elseif (in_array($filename, array('template.php', 'template_edit.php', 'template_settings.php'))) {
            $part = get_param("part", "main");
            $menu = "stemplate_" . $part;
        } elseif (in_array($filename, array('user_menu_order.php', 'site_options.php', 'image.php', 'menu.php', 'smtp.php', 'date_options.php', 'main_col_order.php', 'right_col_order.php', 'profile_col_narrow.php', 'header_menu_order.php', 'submenu_order.php', 'quick_search_order.php', 'profile_tabs_order.php', 'visitor_menu_order.php'))) {
            $menu = "soptions";
        } elseif (substr($filename, 0, strlen('users_fields')) == 'users_fields') {
            $menu = "sfields";
        } elseif ($filename == 'pay_price.php') {
            $menu = "spay";
        } elseif ($filename == 'music_song_edit.php') {
            $menu = "smusic";
            $songId = get_param('song_id');
            if ($songId) {
                $groupId = DB::result('SELECT `group_id` FROM `music_song` WHERE `song_id` = ' . to_sql($songId, 'Number') . ' LIMIT 1');
                if ($groupId) {
                    $menu = "sgroupssocialmusic";
                }
            }
        }
        // Rade 2023-09-22 add start 
        elseif (in_array($filename, array('partner.php', 'partner_baners.php', 'contact_partner.php', 'partner_main.php', 'partner_tips.php', 'partner_faq.php', 'partner_terms.php'))) //nnsscc-diamond-20200508
        {
            $menu = 'saffiliates';
        } elseif ($filename == 'banner.php') {
            $menu = "saddmainbanner";
        } elseif ($filename == 'pages_add_banner.php') {
            $menu = "saddbanner";
        } elseif ($filename == 'pages_add_wevents.php') {
            $menu = "saddwevent";
        } elseif ($filename == 'donation_admin_cobra.php') {
            $menu = "sdonation";
        } elseif ($filename == 'pages_add_club.php') {
            $menu = "saddclub";
        } elseif ($filename == 'pages.php') {
            $menu = "saddclub";
        } elseif ($filename == 'pages_add.php') {
            $menu = "saddclub";
        } elseif ($filename == 'pages_nsc.php') {
            $menu = "saddmenu";
        } elseif ($filename == 'pages_edit_club.php') {
            $menu = "saddmenu";
        } elseif ($filename == 'pages_add_menu.php') {
            $menu = "saddmenu";
        } elseif ($filename == 'pages_add_nsc_sub.php') {
            $menu = "saddmenu";
        } elseif ($filename == 'add_manager.php') {
            $menu = "saddmanager";
        } elseif ($filename == 'managers.php') {
            $menu = "saddmanager";
        } elseif ($filename == 'edit_manager.php') {
            $menu = "saddmanager";
        } elseif ($filename == 'music_musicians.php') { //eric-cuigao-nsc-20201210-start
            $menu = "smediamusic";
        } elseif ($filename == 'music_musician_edit.php') { //popcorn modified-20231214-start
            $menu = "smediamusic";
        } elseif ($filename == 'music_musician_comments.php') { //eric-cuigao-nsc-20201210-start
            $menu = "smediamusic";
        } elseif ($filename == 'music_songs.php') { //eric-cuigao-nsc-20201210-start
            $menu = "smediamusic";
        } elseif ($filename == 'music_song_comments.php') { //eric-cuigao-nsc-20201210-start
            $menu = "smediamusic";
        } elseif ($filename == 'music_categories.php') { //eric-cuigao-nsc-20201210-start
            $menu = "smediamusic";
        } elseif ($filename == 'music_category_add.php') { //eric-cuigao-nsc-20201210-start
            $menu = "smediamusic";
        } elseif ($filename == 'alb_albums.php') {
            $menu = "smediaalb";
        } elseif ($filename == 'vids_radios.php') { //popcorn 9/29/2023
            $menu = "smediaradio";
        } elseif ($filename == 'vids_videos.php') {
            $menu = "smediavids";
        } elseif ($filename == 'vids_users.php') {
            $menu = "smediavids";
        } elseif ($filename == 'vids_comments.php') {
            $menu = "smediavids";
        } elseif ($filename == 'vids_categories.php') {
            $menu = "smediavids";
        } elseif ($filename == 'vids_category_add.php') {
            $menu = "smediavids";
        } elseif ($filename == 'media_radio.php') {
            $menu = "smediaradio";
        } elseif ($filename == 'media_podcast.php') { //eric-cuigao-nsc-20201210-end
            $menu = "smediapodcast";
        } elseif ($filename == 'partyhouz_partyhouz.php') { //eric-ECA-73023-830PM-Start
            $menu = "spartyhouz";
            // Rade 2023-09-28 delete start
            // }elseif($filename == 'partyhouz_partyhou_comments.php') {
            // $menu = "spartyhouzcomments";
            // }elseif($filename == 'partyhouzcategories.php') {
            //     $menu = "spartyhouz_categories";
            // }elseif($filename == 'partyhouz_category_add.php') {//eric-ECA-73023-830PM--end
            //     $menu = "spartyhouzcategory";
            // Rade 2023-09-28 delete end    
        } elseif ($filename == 'users_reports.php') { // Divyesh - 31072023 - Start
            $menu = "smoderator";
        } elseif ($filename == 'sms_carriers.php') {
            $menu = "scarrier";
        } elseif ($filename == 'masssms.php') {
            $menu = "ssms_mass_text";
        } elseif ($filename == 'autosms.php') {
            $menu = "ssms_auto_mailer";
            // Rade 2023-09-28 add start
        } elseif ($filename == 'groups_social.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_group_comments.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_pages.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_photo.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_video.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_reports.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_reports_content.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_reports_wall_post.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_vids_videos.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_vids_groups_videos.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_vids_groups.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_vids_comments.php') {
            $menu = "sgroups";
        } elseif ($filename == 'groups_social_music_songs.php') {
            $menu = "sgroups";
            // Rade 2023-09-28 add end
        } else { // Divyesh - 31072023 - Start
            // Rade 2023-09-22 add end
            $menu = $this->getActiveMenuItem();
            if (!$menu) {
                $arr = explode('?', $request);
                $arr = explode('.', $arr[0]);
                $arrPage = $arr[0];
                $arr = explode('_', $arr[0]);
                $request = $arr[0];
                $menu = 's' . $request;
                if ($menu == 'sbaner') {
                    $menu = 'sbanner';
                }
                if ($arrPage == 'contact_partner') {
                    $menu = 'spartner';
                }
            }
        }

        //look_message_im.php look_message_chat.php look_message_mail.php

        $pagesUserSection = array(
            'look_message_im.php',
            'look_message_chat.php',
            'look_message_mail.php',
            'users_results.php'
        );

        if (in_array($filename, $pagesUserSection)) {

            $menu = 'susers';

        }

        if ($html->blockExists('data_smenu')) {
            $datesMenu = array(
                'dt_home' => array('shome', 'soptions', 'sstats', 'spay', 'simport', 'sdonation', 'saddclub'),
                'dt_template' => array('stemplate_main', 'stemplate_mobile', 'stemplate_administration', 'stemplate_partner'),
                'dt_lang' => array('slanguage_main', 'slanguage_mobile', 'slanguage_administration', 'slanguage_partner'),
                'dt_pages' => array('shelp', 'snews', 'spopup', 'spages'),
                'dt_users' => array('susers', 'sfields', 'sautomail', 'smassmail', 'smatchmail', 'scontact', 'sipblock', 'sfakes', 'sgifts'),
                'dt_modules' => array(
                    'spartner',
                    'sblogs',
                    'splaces',
                    'sflashchat',
                    's3dchat',
                    'svids',
                    'sadv',
                    'sgroups',
                    'sevents',
                    'sforum',
                    'sbanner',
                    'spartyhouz',
                    'shotdates',
                    'sforum',
                    'sbanner',
                    'sseo',
                    'scityrooms',
                    'sgames',
                    'salb',
                    'sgroupssocial',
                    'sstickers'
                ),
                'dt_advertise' => array('saddbanner', 'saddmainbanner', 'saddwevent', 'saffiliates'),
                'dt_sms' => array('scarrier', 'ssms_mass_text', 'ssms_auto_mailer'),
                //Divyesh - 31072023
                'dt_media' => array('smediaalb', 'smediavids', 'smediamusic', 'smediaradio', 'smediapodcast'),
                //eric-cuigao-nsc-20201210
                'dt_manager' => array('saddmanager', 'smanagers'),
                //popcorn-20230928
                //'dt_party' => array('spartyhouz', 'spartyhouzcomments','spartyhouz_categories', 'spartyhouzcategory')//eric-ECA-73023830pm
            );
            $dtMenu = '';

            foreach ($datesMenu as $dt => $itemsDt) {
                $items = array_flip($itemsDt);
                if (isset($items[$menu])) {
                    $dtMenu = $dt;
                    break;
                }
            }
            if ($dtMenu) {
                $html->setvar('data_smenu', $dtMenu);
                $html->parse('data_smenu', false);
            }
        }

                    // var_dump($menu);



        $html->setvar("smenu", $menu);

        $html->parse("view", true);

        $isTmplModern = Common::isAdminModer();
        $isAllowCheckPage = $filename == 'banner.php'
            || $filename == 'banner_add.php'
            || $filename == 'banner_edit.php'
            || $filename == 'seo.php'
            || in_array($filename, array('template.php', 'template_edit.php', 'template_settings.php'));

        if ($isAllowCheckPage || $isTmplModern) {
            $template = array();
            $main = countFrameworks('main');
            $admin = countFrameworks('administration');
            $partner = countFrameworks('partner');

            $noMobileTemplate = isOptionActiveLoadTemplateSettings('no_mobile_template', null, 'main', Common::getOption('main', 'tmpl'));
            $mobile = countFrameworks('mobile') && !$noMobileTemplate;
            if ($main) {
                $template[] = 'stemplate_main';
                $html->parse('template_main', false);
            }
            if ($admin) {
                $template[] = 'stemplate_administration';
                $html->parse('template_administration', false);
            }
            if ($partner) {
                $template[] = 'stemplate_partner';
                $html->parse('template_partner', false);
            }
            if ($mobile) {
                $template[] = 'stemplate_mobile';
                $html->parse('template_mobile', false);
            }

            $isCheckModern = $isAllowCheckPage && $isTmplModern;
            if (
                $isCheckModern
                && $filename != 'banner.php'
                && $filename != 'banner_add.php'
                && $filename != 'banner_edit.php'
                && $filename != 'seo.php'
            ) {
                if (empty($template)) {
                    redirect('home.php');
                } elseif (!in_array($menu, $template)) {
                    $tmpl = explode('_', $template[0]);
                    $part = $tmpl[1];
                    redirect('template.php?part=' . $part);
                }
            }
        }

        Common::devCustomJs($html);
        if (!Common::isMultisite()) {
            //popcorn 9/29/2023 start
            if (get_session('admin_auth') == "Y" && get_session('manager_name') != "admin") {

                if ($g['tmpl']['admin_access_role']['Options'] == 1) {
                    $html->parse('smenu_options');
                }
                if ($g['tmpl']['admin_access_role']['Statistics'] == 1) {
                    $html->parse('smenu_stats');
                }
                if ($g['tmpl']['admin_access_role']['Payment'] == 1) {
                    $html->parse('smenu_payment');
                }
                if ($g['tmpl']['admin_access_role']['Donation'] == 1) {
                    $html->parse('smenu_donation');
                }
                if ($g['tmpl']['admin_access_role']['New_Page'] == 1) {
                    $html->parse('smenu_club_add');
                }
                if ($g['tmpl']['admin_access_role']['New_Menu'] == 1) {
                    $html->parse('smenu_new_add');
                }

                // if($g['tmpl']['admin_access_role']['Main'] == 1) {
                $html->parse('menu_main');
                $html->parse('smenu_main');
                // }

                if ($g['tmpl']['admin_access_role']['Frameworks'] == 1) {
                    $html->parse('menu_template');
                    $html->parse('menu_template_item');
                }

                if ($g['tmpl']['admin_access_role']['Languages'] == 1) {
                    $html->parse('menu_languages');
                    $html->parse('smenu_languages');
                }

                if ($g['tmpl']['admin_access_role']['Site_news'] == 1) {
                    $html->parse('menu_help_and_news');
                    $html->parse('smenu_help_and_news');
                }

                if ($g['tmpl']['admin_access_role']['Users'] == 1) {
                    $html->parse('menu_users');
                    $html->parse('smenu_users');

                }

                if ($g['tmpl']['admin_access_role']['Modules'] == 1) {
                    $html->parse('menu_modules');
                    $html->parse('smenu_modules');

                }

                if ($g['tmpl']['admin_access_role']['Advertise'] == 1) {
                    $html->parse('menu_advertise');
                    $html->parse('smenu_advertise');

                }

                if ($g['tmpl']['admin_access_role']['Media'] == 1) {
                    $html->parse('menu_media');
                    $html->parse('smenu_media');
                }

                if ($g['tmpl']['admin_access_role']['SMS_TEXT'] == 1) {
                    $html->parse('menu_sms_text');
                    $html->parse('smenu_sms_text');

                }

                // if ($g['tmpl']['admin_access_role']['PartyHouZ'] == 1) {
                //     $html->parse('menu_partyhouz');
                //     $html->parse('smenu_partyhouz');
                // }


            } else {
                $html->parse('menu_main');
                $html->parse('menu_template');
                $html->parse('menu_languages');
                $html->parse('menu_help_and_news');
                $html->parse('menu_users');
                $html->parse('menu_modules');
                $html->parse('menu_advertise');
                $html->parse('menu_media');
                $html->parse('menu_sms_text');
                $html->parse('menu_partyhouz');
                $html->parse('add_manager');

                $html->parse('smenu_options');
                $html->parse('smenu_stats');
                $html->parse('smenu_payment');
                $html->parse('smenu_donation');
                $html->parse('smenu_club_add');
                $html->parse('smenu_new_add');

                $html->parse('smenu_main');
                $html->parse('menu_template_item');
                $html->parse('smenu_languages');
                $html->parse('smenu_help_and_news');
                $html->parse('smenu_users');
                $html->parse('smenu_modules');
                $html->parse('smenu_advertise');
                $html->parse('smenu_media');
                $html->parse('smenu_sms_text');
                $html->parse('smenu_partyhouz');
                $html->parse('smenu_add_manager');




            }
            //popcorn 9/29/2023 end

            //$html->parse('menu_import');
        }

        //if (!Common::isOptionActive('type_payment_free', 'template_options')) {
        $html->parse('menu_payment');
        //}

        if ($html->varExists('url_video_main_page')) {
            $codeVideo = Common::getOption('main_page_urban_video_code');
            if ($codeVideo) {
                $codeVideo = json_decode($codeVideo, true);
                $codeVideo = $codeVideo['code'];
            }
            $html->setvar('url_video_main_page', $codeVideo);
        }

        if (get_session("admin_auth") == 'Y' || get_session("replier_auth") == 'Y') {

            if (get_session("admin_auth") == 'Y') {
                $html->parse('auth_menu', true);
                $html->parse('menu_script_auth', true);
            } else {
                $html->parse('content_full_width', true);
                $html->setvar('replier_name', get_session("replier_name"));
                $html->parse('replier_auth', true);
            }

            /* Modern */
            $html->parse('header_admin', true);
            /* Modern */
        } else {
            $html->parse('auth_menu', true);
            $html->parse('menu_script_auth', true);
        }



        parent::parseBlock($html);
    }
}

class CAdminFooter extends CAdminHeader
{
    function parseBlock(&$html)
    {
        /* Modern */
        if (get_session('admin_auth') == 'Y' || get_session('replier_auth') == 'Y') {

            if (Common::isMultisite()) {
                $html->parse('support_multisate');
            } else {
                $html->parse('support_no_multisate');
            }

            $year = strftime('%Y');
            $html->setvar('year', $year);

            $html->parse('footer_admin', true);
        }
        /* Modern */

        parent::parseBlock($html);
    }
}

class CAdminConfig extends CHtmlBlock
{

    var $module = 'trial';
    var $sort = 'position';
    var $order = 'asc';
    var $allowedOptions = array();
    var $urlParams = '';
    private $set = '';
    private $name = '';

    private $notAvailableOption = array(
        'urban' => array(
            //General website's settings
            //Active pages
            //Rade 2023-09-29 delete start
            // 'music','places','events','hot_dates','hotdates','partyhouz','groups','news','invite_friends','help','blogs','games','chat',//'flashchat',
            // 'forum','biorythm','top5','rating','gallery','personal_settings','partner_settings','adv_search',
            // 'saved_searches','bookmarks','online_tab_enabled','new_tab_enabled','birthdays_tab_enabled ','matching_tab_enabled',
            // 'i_viewed_tab_enabled','viewed_me_tab_enabled','adv','events_calendar','hotdates_calendar','partyhouz_calendar','network','stats',
            //Rade 2023-09-29 delete end
            //Active features
            //Rade 2023-09-29 delete start
            // 'widgets','recorder','im','postcard','mail','favorite_add','wall_like_comment_alert',
            // 'status_relation','mail_message_alert','upgrade_couple','love_calculator',
            // 'couples',
            //Rade 2023-09-29 delete end
            //Main page settings

            'main_new_videos',
            'main_users',
            'main_users_number_oryx',
            'main_users_number_new_age',
            //    'main_users_photo_size', //Rade 2023-09-29 delete
            //    'main_users_number_mixer', //Rade 2023-09-29 delete
            'main_page_header_mode',
            'main_search',

            //Logged in user settings
            // 'hide_profile_enabled', //Rade 2023-09-29 delete
            //'only_friends_wall_posts','wall_posts_by_default',
            /*'your_orientation',*/
            // 'show_home_page_online',
            //Upload settings
            // 'music_mp3_file_size_limit_mbs',
            //Messages settings
            'mails_limit_max',
            'number_friends_show_mail',
            //'auto_ban_messages',
            //Logged in user settings
            // 'frameworks_version',
            'city_language',
            // 'join', // Rade 2023-09-29
            'gps_enabled',
            'watch_geo_position_time',
            'geo_position_max_age',
            'im_audio_messages',
            'audio_comment',
            'app_vibration_duration',
            'type_media_chat',
            'face_input_size',
            'face_score_threshold',
            // 'in_app_purchase_enabled', // Rade 2023-09-29
        ),
        'old' => array(
            //General website's settings
            'minimum_match_percent_on_graphs',
            'youtube_video_background_users_urban',
            'youtube_video_background_users_all_pages_urban',
            'youtube_video_background_users_urban_quality',
            'main_page_urban_video_code',
            'main_page_urban_video_mute',
            'main_page_urban_video_volume',
            'default_search_distance',
            'default_search_location',
            'app_vibration_duration',
            'app_ios_active',
            'app_ios_url',
            'app_android_active',
            'app_android_url',
            'app_btn_position',
            'message_notifications_active',
            'credits_enabled',
            'friends_enabled',
            'message_notifications_lifetime',
            'message_notifications_position',
            'message_notifications_not_show_when_3d_city',
            'city_language',
            '3dcity_menu_item_position',
            'login_form_position_right',
            'login_form_position_bottom',
            'info_block_transparency',
            'info_block_position_left',
            'info_block_position_bottom',
            'fb_link_color',
            'fb_link_color_hover',
            'maps_service',
            'bing_apikey',
            'main_page_urban_animated',
            'main_page_urban_video_show_video_once',
            //
            // 'wall_enabled',
            'max_filter_distance',
            'header_users_module_enabled_urban',
            'credit_transfer_to_another_user',
            'upload_limit_photo_count',
            'forced_profile_picture_upload',
            'min_number_photos_to_use_site',
            'photo_rating_enabled',
            'forced_user_about_me',
            'type_media_chat',
            'spotlight_enabled_urban',
            'seo_friendly_urls',
            'google_plus_settings',
            'google_plus_appid',
            'google_plus_secret',
            'linkedin_settings',
            'linkedin_appid',
            'linkedin_secret',
            'twitter_settings',
            'twitter_appid',
            'twitter_secret',
            'vk_settings',
            'vk_appid',
            'vk_secret',
            'facebook_place',
            'google_plus_place',
            'linkedin_place',
            'twitter_place',
            'vk_place',
            //
            'google_apikey',
            'number_of_profiles_in_the_search_results',
            'profile_verification_enabled',
            'join_impact',
            'join_number_photo_likes',
            'im_history_chat',
            'main_page_video_stop_on_join_page',

            'main_page_header_background_type',
            'main_page_header_background_color',
            'main_page_header_background_color_direction',
            'main_page_header_background_color_upper',
            'main_page_header_background_color_upper_stop',
            'main_page_header_background_color_lower',
            'main_page_header_background_color_lower_stop',

            'hide_im_on_page_city',
            'no_profiles_without_photos_search',
            'facebook_url',
            'google_plus_url',
            'linkedin_url',
            'twitter_url',
            'vk_url',
            'gps_enabled',
            'watch_geo_position_time',
            'geo_position_max_age',

            'webrtc_camera_resolution_width_min',
            'webrtc_camera_resolution_width_ideal',
            'webrtc_camera_resolution_width_max',
            'webrtc_framerate_min',
            'webrtc_framerate_ideal',
            'webrtc_framerate_max',
            'use_only_admob_in_apps',
            'im_audio_messages',
            'audio_comment',
            'auto_ban_messages_min_length',
            'live_streaming',
            // rade 2023-09-20 add
            'live_streaming_auto_connect',
            // rade 2023-09-20 add

            'recorder',
            'videochat',
            'audiochat',
            'postcard',
            'games',
            'flashchat',
            'mode_profile',
            'allow_users_profile_mode',
            'in_app_purchase_enabled',
            'face_input_size',
            'face_score_threshold',

            'main_page_header_text_color',
            'main_page_header_button_border_color',
            'main_page_title_shadow_color',
        ),
    );

    private $allowAvailableOptionTemplate = array(
        'edge' => array(
            // 'favorite_add',
            'im_audio_messages',
            //'audio_comment',
            'music_mp3_file_size_limit_mbs',
            'live_streaming',
            'live_streaming_auto_connect',
            //'face_input_size', 'face_score_threshold'
        )
    );

    private $notAvailableOptionTemplate = array(
        'impact' => array(
            //Active pages
            //General website's settings
            'youtube_video_background_users_urban',
            'youtube_video_background_users_all_pages_urban',
            'youtube_video_background_users_urban_quality',
            'friends_enabled',
            'login_form_position_right',
            'login_form_position_bottom',
            'info_block_transparency',
            'info_block_position_left',
            'info_block_position_bottom',
            'fb_link_color',
            'fb_link_color_hover',
            'maps_service',
            'bing_apikey',
            'main_page_urban_animated',
            'show_interests_search_results_urban',
            // 'users_on_main_page_map_and_mobile', // Rade 2023-10-01
            'header_users_module_enabled_urban',
            //'map_on_main_page_urban',
            'google_apikey',
            'header_color_urban',
            'background_color_urban',
            '3dcity_menu_item_position',
            'app_btn_position',
            //Logged in user settings
            'default_profile_background',
            // 'only_friends_wall_posts', // Rade 2023-09-29
            // 'wall_posts_by_default', // Rade 2023-09-29
            'wall_comments_by_default',
            'rate_see_my_photo_rating',
            'spotlight_photos_number',
            //Active features
            'wink',
            'gifts_enabled',
            'credit_transfer_to_another_user',
            // 'wall_enabled',
            'spotlight_enabled_urban',
            //Messages settings
            'message_notifications_lifetime',
            'message_notifications_position',
            'message_notifications_active',
            'message_notifications_not_show_when_3d_city',
            'facebook_url',
            'google_plus_url',
            'linkedin_url',
            'twitter_url',
            'vk_url',
            'hide_profile_data_for_guests_urban',
            'hide_site_from_guests',
            'wall_like_comment_alert',
            'wall_join_message_enabled',
        ),
        'urban' => array(
            'join_impact',
            'join_number_photo_likes',
            'join_with_photo_only',
            'minimum_match_percent_on_graphs',
            'profile_status',
            'main_page_video_stop_on_join_page',
            'main_page_header_background_type',
            'main_page_header_background_color',
            'main_page_header_background_color_direction',
            'main_page_header_background_color_upper',
            'main_page_header_background_color_upper_stop',
            'main_page_header_background_color_lower',
            'main_page_header_background_color_lower_stop',
            'hide_im_on_page_city',
            'facebook_url',
            'google_plus_url',
            'linkedin_url',
            'twitter_url',
            'vk_url',
            'main_page_header_text_color',
            'main_page_header_button_border_color',
            'main_page_title_shadow_color',
        ),
        'edge' => array(
            //General website's settings
            //'paid_access_mode',
            'view_home_page_urban',
            'youtube_video_background_users_urban',
            'youtube_video_background_users_all_pages_urban',
            'youtube_video_background_users_urban_quality',
            'main_page_video_stop_on_join_page',
            'main_page_header_background_type',
            'main_page_header_background_color',
            'main_page_header_background_color_direction',
            'main_page_header_background_color_upper',
            'main_page_header_background_color_upper_stop',
            'main_page_header_background_color_lower',
            'main_page_header_background_color_lower_stop',
            'main_page_urban_video_code',
            'main_page_urban_animated',
            'main_page_urban_video_mute',
            'main_page_urban_video_volume',
            'main_page_urban_video_show_video_once',
            // 'home_page_mode', // Rade 2023-10-01
            // 'main_page_mode', // Rade 2023-10-01
            // 'feed_as_home_page', // Rade 2023-10-01
            'show_interests_search_results_urban',
            // 'users_on_main_page_map_and_mobile', // Rade 2023-10-01
            '3dcity_menu_item_position',
            'login_form_position_right',
            'login_form_position_bottom',
            'info_block_transparency',
            'info_block_position_left',
            'info_block_position_bottom',
            'fb_link_color',
            'fb_link_color_hover',
            'maps_service',
            'bing_apikey',
            'google_apikey',
            'minimum_match_percent_on_graphs',
            'app_btn_position',
            'number_of_profiles_in_the_search_results',
            // 'only_apps_active', // Rade 2023-10-01
            //User-defined settings
            // Rade 2023-09-29 delete start
            // 'top_select',
            // 'number_of_columns_in_language_selector', 
            // 'mode_profile', 
            // 'allow_users_profile_mode',
            // Rade 2023-09-29 delete end
            //User registration settings
            'join_number_photo_likes',
            //Not logged in user settings
            'mobile_site_on_tablet',
            'mobile_redirect',
            'header_users_module_enabled_urban',
            'photo_rating_enabled',
            'spotlight_enabled_urban',
            //Active pages
            // 'flashchat', // Rade 2023-09-29
            //Active features
            // 'wall_enabled',
            // 'wink', Rade 2023-09-29
            'gifts_enabled',
            // 'profile_status', //Rade 2023-09-29
            'credits_enabled',
            'credit_transfer_to_another_user',
            //Logged in user settings
            'forced_user_about_me',
            'default_profile_background',
            'spotlight_photos_number',
            'search_service_number',
            // 'only_friends_wall_posts', 'wall_comments_by_default', 'wall_posts_by_default', // Rade 2023-09-29
            'type_media_chat',
            // 'your_orientation', //Rade 2023-09-29
            'forced_profile_picture_upload',
            //Messages settings
            'hide_im_on_page_city',
            'message_notifications_active',
            'message_notifications_lifetime',
            'message_notifications_position',
            'message_notifications_not_show_when_3d_city',
            // 'sp_sending_messages_per_day_urban', // Rade 2023-10-02
            'map_on_main_page_urban',
            // 'image_main_page_urban', 
            'image_main_page_compression_ratio_urban',
            'upload_image_main_page_urban',
            'background_color_urban',
            'upload_limit_photo_count',
            // 'videogallery', // Rade 2023-09-29
            // 'mobile_enabled', // Rade 2023-09-29
            'hide_profile_data_for_guests_urban',
            'profile_verification_enabled',
            // 'in_app_purchase_enabled', //Rade 2023-09-29
            // 'in_app_purchase_enabled', 
            // 'join_with_photo_only', // Rade 2023-09-29
            'main_page_header_text_color',
            'main_page_header_button_border_color',
            'main_page_title_shadow_color',
            // 'join',
            // 'join_with_photo_only',
            'main_page_mode',
            'image_main_page',
            'upload_image_main_page',
            'im_history_messages',
            'im_history_chat',
            'main_page_settings',
            'main_users_number_mixer',
            'main_users_photo_size',
            'wall_posts_by_default',
            // 'your_orientation',
            // 'show_home_page_online',
            'wall_comments_by_default',
            'help',
            'games',
            'friends_enabled',
            'invite_friends',
            // 'live_streaming_auto_connect',
            'recorder',
            'im',
            'postcard',
            'favorite_add',
            'audio_greeting'
        )
    );

    private $notAvailableDateFormats = array(
        'urban' => array(
            
        ),
        'old' => array(
            'super_powers_date_format',
            'im_datetime',
            'im_mobile_datetime_today',
            'im_mobile_datetime_this_month',
            'im_mobile_datetime',
            'gift_date',
            'general_chat',
            'profile_birth_edge',
            'profile_birth_full_edge',
            'list_blogs_info_plain_edge',
            'photo_date',
            'group_create_full',
            'task_time'
        )
    );

    private $notAvailableDateFormatsTemplate = array(
        'urban' => array(
            'profile_birth_edge',
            'profile_birth_full_edge',
            'list_blogs_info_plain_edge',
            'photo_date',
            'group_create_full',
            'task_time'
        ),
        'impact' => array(
            'profile_birth_edge',
            'profile_birth_full_edge',
            'list_blogs_info_plain_edge',
            'photo_date',
            'gift_date',
            'group_create_full',
            'task_time'
        ),
        'edge' => array(
            'general_chat',
            'gift_date',
            'photo_comment_date',
            'super_powers_date_format'
        )
    );

    function __construct($name, $html_path, $isTextTemplate = false, $textTemplate = false, $noTemplate = false)
    {
        $this->set = Common::getOption('set', 'template_options');
        $this->name = Common::getOption('name', 'template_options');
        parent::__construct($name, $html_path, $isTextTemplate, $textTemplate, $noTemplate);
    }

    static function getSettings($tmpl){
        $menuSettings = array();
        $defaultArea = '';
        if ($tmpl == 'edge') {
            $defaultArea = 'general_settings';
            $menuSettings = array(
                'general_settings' => array('type' => 'config', 'class' => 'm_general'),
                'media_files_settings' => array('type' => 'config', 'class' => 'm_media_files'),

                'color_scheme_general' => array('type' => 'config', 'class' => 'm_general'),
                'color_scheme_visitor' => array('type' => 'config'),
                'main_page_settings' => array('type' => 'config', 'page' => 'blogs_list.php'),
                'main_page_block_order' => array('type' => 'order', 'page' => 'index.php'),
                'join_page_settings' => array('type' => 'config', 'page' => ''),

                'color_scheme_member' => array('type' => 'config'),

                'member_settings' => array('type' => 'config'),
                'member_home_page' => array('type' => 'order', 'page' => ''),
                'member_profile_tabs' => array('type' => 'order', 'page' => ''),
                'member_profile_inner_tabs' => array('type' => 'order', 'page' => ''),

                'member_column_left_order' => array('type' => 'order', 'page' => ''),
                'member_column_right_order' => array('type' => 'order', 'page' => ''),

                'member_header_menu' => array('type' => 'order', 'page' => ''),
                'member_header_menu_short' => array('type' => 'order', 'page' => ''),
                'member_user_additional_menu' => array('type' => 'order', 'page' => ''),

                'member_visited_additional_menu' => array('type' => 'order', 'page' => ''),
                'member_visited_additional_menu_inner' => array('type' => 'order', 'page' => ''),
                'member_visited_right_column_menu' => array('type' => 'order', 'page' => ''),

                'gallery_settings' => array('type' => 'config', 'class' => 'm_gallery'),
                'wall_settings' => array('type' => 'config', 'class' => 'm_wall'),

                'groups_settings' => array('type' => 'config', 'class' => 'm_group'),
                'member_groups_tabs' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_inner_tabs' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_column_left_order' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_column_right_order' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_header_menu_short' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_additional_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),

                'member_groups_visited_additional_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_visited_additional_menu_inner' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_visited_right_column_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),

                'events_settings' => array('type' => 'config', 'class' => 'm_events'),

                'blogs_settings' => array('type' => 'config', 'class' => 'm_blogs'),
                'blogs_column_right_order' => array('type' => 'order', 'page' => '', 'class' => 'm_blogs'),
                'blogs_visited_right_column_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_blogs'),

                'live_settings' => array('type' => 'config', 'class' => 'm_live'),
            );
        }
        return array('settings' => $menuSettings,
                     'default' => $defaultArea);
    }

    function deactivateOption(&$config)
    {
        $tmplOptionSet = $this->set;
        $tmplOptionName = $this->name;
        if (!$tmplOptionSet) {
            $tmplOptionSet = 'old';
        }
        if ($this->isOptionsTemplate()) {

            if ($tmplOptionName === 'edge') {
                if (TemplateEdge::isModeLms()) {
                    unset($config['main_page_image']);
                } else {
                    unset($config['main_page_image_mode_lms']);
                }
            }

            return;
        }

        if (Common::getAppIosApiVersion() < 48) {
            if (isset($this->allowAvailableOptionTemplate[$tmplOptionName])) {

                $iosAppFeatures = array('audio_greeting', 'im_audio_messages', 'audio_comment');

                foreach ($iosAppFeatures as $iosAppFeature) {
                    $iosAppFeatureKey = array_search($iosAppFeature, $this->allowAvailableOptionTemplate[$tmplOptionName]);
                    if ($iosAppFeatureKey !== false) {
                        unset($this->allowAvailableOptionTemplate[$tmplOptionName][$iosAppFeatureKey]);
                    }
                }
            }
        }

        if (isset($this->notAvailableOption[$tmplOptionSet]) && !empty($this->notAvailableOption[$tmplOptionSet])) {
            $allowAvailableOption = array();
            if (isset($this->allowAvailableOptionTemplate[$tmplOptionName]) && is_array($this->allowAvailableOptionTemplate[$tmplOptionName])) {
                $allowAvailableOption = $this->allowAvailableOptionTemplate[$tmplOptionName];
            }

            if (isset($this->notAvailableOptionTemplate[$tmplOptionName]) && is_array($this->notAvailableOptionTemplate[$tmplOptionName])) {
                $this->notAvailableOption[$tmplOptionSet] = array_merge($this->notAvailableOption[$tmplOptionSet], $this->notAvailableOptionTemplate[$tmplOptionName]);
            }
            foreach ($this->notAvailableOption[$tmplOptionSet] as $key => $item) {
                if (!in_array($item, $allowAvailableOption)) {
                    unset($config[$item]);
                }
            }
        }
        if (isset($this->notAvailableDateFormats[$tmplOptionSet]) && !empty($this->notAvailableDateFormats[$tmplOptionSet])) {
            if (isset($this->notAvailableDateFormatsTemplate[$tmplOptionName]) && is_array($this->notAvailableDateFormatsTemplate[$tmplOptionName])) {
                $this->notAvailableDateFormats[$tmplOptionSet] = array_merge($this->notAvailableDateFormats[$tmplOptionSet], $this->notAvailableDateFormatsTemplate[$tmplOptionName]);
            }
            foreach ($this->notAvailableDateFormats[$tmplOptionSet] as $key => $item) {
                unset($config[$item]);
            }
        }
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    function setModule($module)
    {
        $this->module = $module;
    }

    function getModule()
    {
        return $this->module;
    }

    function setAllowedOptions($options)
    {
        $this->allowedOptions = $options;
    }

    function getAllowedOptions()
    {
        return $this->allowedOptions;
    }

    function setUrlParams($params)
    {
        $this->urlParams = $params;
    }

    function isOptionsTemplate()
    {
        return strpos($this->getModule(), $this->name) !== false;
    }

    function init()
    {

    }

    function action()
    {
        global $g;

        $cmd = get_param('cmd', '');
        $options = get_param_array('option');
        if ($this->name == 'impact' && isset($options['map_on_main_page_urban'])) {
            $options['map_on_main_page_urban'] = Common::impactGetMapOnMainPageUrbanValue($options['map_on_main_page_urban']);
        }
        $errors = array();
        if ($cmd == 'update') {
            $lang = Common::getOption('main', 'lang_value');
            if ($this->module == 'options') {
                if (isset($options['app_vibration_duration'])) {
                    $options['app_vibration_duration'] = abs(intval($options['app_vibration_duration']));
                }
                if (isset($options['status_relation'])) {
                    $statusRelation = ($options['status_relation'] == 'Y') ? 'active' : 'inactive';
                    if ($g['user_var']['relation']['status'] != $statusRelation) {
                        UserFields::updateStatus('relation');
                    }
                }
                $part = Common::getOption('main', 'tmpl');
                $dir = Common::getOption('url_files', 'path') . 'tmpl';

                if (Common::isOptionActive('map_on_main_page_urban', 'template_options')) {
                    $curCodeVideo = Common::getOption('main_page_urban_video_code');
                    $typeMainPageUrban = $options['map_on_main_page_urban'];
                    if ($typeMainPageUrban == 'image' || $typeMainPageUrban == 'random_image') {
                        $uploadBg = 'upload_image_main_page_urban';
                        $minWidth = 0;
                        $minHeight = 517;
                        $numError = 15;
                        if ($this->name == 'impact') {
                            $minWidth = 1920;
                            $minHeight = 1080;
                            $numError = 20;
                        }
                        $error = validUploadFileImage($uploadBg, $numError, $minWidth, $minHeight);
                        if ($error == '') {
                            $setOptions = 'image_main_page_urban';
                            $addPrfFile = '_main_page_image_';
                            $compresion = 'image_main_page_compression_ratio_urban';
                            $fileParams = getParamsFile('main_page_image', $addPrfFile, $setOptions, $compresion);
                            $im = new Image();
                            $im->loadImage($_FILES[$uploadBg]['tmp_name']);
                            $im->saveImage($fileParams['file'], $fileParams['ratio']);
                            Common::saveFileSize($fileParams['file']);

                            if (get_param_int('image_upload_data')) {
                                @unlink($_FILES[$uploadBg]['tmp_name']);
                            }
                            unset($im);
                            $options[$setOptions] = $fileParams['name'];
                        } else {
                            $errors[] = $error;
                        }
                        $options['main_page_urban_video_code'] = $curCodeVideo;
                    } else if ($typeMainPageUrban == 'video') {
                        $codeVideo = '';
                        if ($curCodeVideo) {
                            $codeVideo = json_decode($curCodeVideo, true);
                            $codeVideo = $codeVideo['code'];
                        }
                        $urlBgVideo = trim($options['main_page_urban_video_code']);
                        //echo $urlBgVideo;
                        if ($urlBgVideo != $codeVideo) {
                            preg_match('/(?:^|\/|v=)([\w\-]{11,11})(?:\?|&|#|$)/', $urlBgVideo, $urlBgVideoCode);
                            $isVideoCodeError = false;
                            if (!isset($urlBgVideoCode[1])) {
                                $isVideoCodeError = true;
                            } else {
                                $code = array($urlBgVideoCode[1], 1);
                                $url = 'https://www.youtube.com/oembed?url=youtu.be/' . $urlBgVideoCode[1];
                                $oembed_text = @urlGetContents($url);
                                if ($oembed_data = json_decode($oembed_text, true)) {
                                    $ratio = 1.778;
                                    $width = 0;
                                    $height = 0;
                                    if (isset($oembed_data['width'])) {
                                        $width = $oembed_data['width'];
                                    }
                                    if (isset($oembed_data['height'])) {
                                        $height = $oembed_data['height'];
                                    }
                                    if ($width && $height) {
                                        $ratio = round($oembed_data['width'] / $oembed_data['height'], 3);
                                    }
                                    $code['code'] = $urlBgVideo;
                                    $code['ratio'] = $ratio;
                                    $code['title'] = isset($oembed_data['title']) ? $oembed_data['title'] : '';
                                    $code['width'] = $width;
                                    $code['height'] = $height;
                                    $options['main_page_urban_video_code'] = json_encode($code);
                                } else {
                                    $isVideoCodeError = true;
                                }
                            }
                            if ($isVideoCodeError) {
                                $errors[] = 18;
                                $options['main_page_urban_video_code'] = $curCodeVideo;
                                //$code=$oembed_text;
                            }
                        } else {
                            $options['main_page_urban_video_code'] = $curCodeVideo;
                        }
                    } else {
                        $options['main_page_urban_video_code'] = $curCodeVideo;
                    }
                }

                if (
                    Common::isOptionActive('tiled_footer_urban', 'template_options')
                    && Common::getOption('tiled_footer_urban') == 'tiled'
                ) {
                    $upload = 'upload_footer_tile_image_urban';
                    $error = validUploadFileImage($upload, 16, 0, 0);
                    if ($error == '') {
                        $save = saveConvertImage($upload, 'footer_tiles', '_footer_tile_image_', 'footer_tile_image_urban');
                        if ($save['error'] == '') {
                            $options['footer_tile_image_urban'] = $save['file_name'];
                        } else {
                            $errors[] = $save['error'];
                        }
                    } else {
                        $errors[] = $error;
                    }
                }

                if (Common::isOptionActive('footer_image_urban', 'template_options')) {
                    $upload = 'upload_footer_image_urban';
                    $error = validUploadFileImage($upload, 17, 0, 0);
                    if ($error == '') {
                        $save = saveConvertImage($upload, 'footer_image', '_footer_image_', 'footer_image_urban');
                        if ($save['error'] == '') {
                            $options['footer_image_urban'] = $save['file_name'];
                        } else {
                            $errors[] = $save['error'];
                        }
                    } else {
                        $errors[] = $error;
                    }
                }

                if (Common::isOptionActive('website_background', 'template_options')) {
                    $uploadBg = 'website_background_upload_oryx';
                    $error = validUploadFileImage($uploadBg, 10, 1920, 1200);
                    if ($error == '') {
                        $dirTmpl = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/backgrounds';
                        $files = readAllFileArrayOfDir($dirTmpl, '');
                        $bgCount = count($files);
                        $templateFile = "{$part}_bg_";
                        $files = readAllFileArrayOfDir($dir, '', SORT_NUMERIC, $templateFile);
                        //var_dump($files);
                        $i = getNumUploadFile($files, $bgCount + 1);
                        $name = "{$i}.jpg";
                        $nameSrc = "{$i}_src.jpg";
                        $file = $dir . '/' . $templateFile . $name;
                        $fileSrc = $dir . '/' . $templateFile . $nameSrc;
                        $im = new Image();
                        if ($im->loadImage($_FILES[$uploadBg]['tmp_name'])) {
                            if ($im->getHeight() != 1200 || $im->getWidth() != 1920) {
                                $im->resizeCropped(1920, 1200);
                            }
                        }
                        if (!empty($options['website_background_compression_ratio_oryx'])) {
                            $ratio = intval($options['website_background_compression_ratio_oryx']);
                        } else {
                            $ratio = $g['image']['quality'];
                        }
                        $im->saveImage($file, $ratio);
                        move_uploaded_file($_FILES[$uploadBg]['tmp_name'], $fileSrc);
                        Common::saveFileSize(array($file, $fileSrc));
                        unset($im);
                        if (get_param_int('image_upload_data')) {
                            @unlink($_FILES[$uploadBg]['tmp_name']);
                        }
                        $options['website_background_oryx'] = $name;

                    } else {
                        $errors[] = $error;
                    }
                }



                $lang = ($lang == 'default') ? '' : '_' . $lang;
                if (Common::isOptionActive('upload_image_main_page', 'template_options')) {
                    $files = readAllFileArrayOfDir($dir, '', SORT_NUMERIC, "{$part}_main_page_dating_bg_user");
                    $i = getNumUploadFile($files, 1);
                    $result = uploadFileImagesCropped('upload_image_main_page', 11, 461, 0, "{$part}_main_page_dating_bg_user_{$i}.jpg", false, 'tmpl', true);
                    if (empty($result)) {
                        $options['image_main_page'] = "{$i}.jpg";
                    } else {
                        $errors[] = $result;
                    }
                }

                if (Common::isOptionActive('upload_big_banner_main_page', 'template_options')) {
                    $errors[] = uploadFileImagesCropped('upload_big_banner_main_page', 12, 450, 133, $part . '_banner01user' . $lang . '.jpg');
                }
                if (Common::isOptionActive('upload_small_banner_main_page', 'template_options')) {
                    $errors[] = uploadFileImagesCropped('upload_small_banner_main_page', 13, 250, 141, $part . '_banner02user' . $lang . '.jpg');
                }
                if (Common::isOptionActive('top_five_button', 'template_options')) {
                    $errors[] = uploadFileImagesCropped('upload_image_top_five_button', 14, 86, 91, $part . '_top5user' . $lang . '.png', true, 'tmpl', true);
                }
                if (
                    Common::isOptionActive('map_on_main_page_urban', 'template_options')
                    && isset($options['image_main_page_urban'])
                    && $options['map_on_main_page_urban'] == 'image'
                ) {
                    if ($this->name == 'impact' && $options['image_main_page_urban'] == 'no_image') {

                    } else {
                        $urlImage = getFileUrl('main_page_image', $options['image_main_page_urban'], '_main_page_image_', 'image_main_page_urban', 'main_page_image_default_urban');
                        $infoImage = getimagesize($urlImage);
                        $options['image_main_page_height_urban'] = $infoImage[1];
                    }
                }
                if (isset($options['footer_tile_image_urban'])) {
                    $urlImage = getFileUrl('footer_tiles', $options['footer_tile_image_urban'], '_footer_tile_image_', 'footer_tile_image_urban', 'footer_tile_image_default_urban');
                    $infoImage = getimagesize($urlImage);
                    $options['footer_tile_image_width_urban'] = $infoImage[0];
                }

                if (isset($options['username_length_min'])) {
                    $options['username_length_min'] = (int) $options['username_length_min'];
                    if ($options['username_length_min'] <= 0) {
                        $options['username_length_min'] = 1;
                    }
                }
                if (isset($options['join_number_photo_likes'])) {
                    $options['join_number_photo_likes'] = abs(intval($options['join_number_photo_likes']));
                    if (!$options['join_number_photo_likes']) {
                        $options['join_number_photo_likes'] = 1;
                    }
                }
                if (isset($options['minimum_match_percent_on_graphs'])) {
                    $options['minimum_match_percent_on_graphs'] = abs(intval($options['minimum_match_percent_on_graphs']));
                    if ($options['minimum_match_percent_on_graphs'] > 100) {
                        $options['minimum_match_percent_on_graphs'] = 100;
                    }
                    $minMatchPercent = intval(Common::getOption('minimum_match_percent_on_graphs'));
                    if ($minMatchPercent != $options['minimum_match_percent_on_graphs']) {
                        DB::delete('user_chart_random_value');
                    }
                }

                if (isset($options['color_scheme_mobile_main_page_background_image_impact'])) {
                    $uploadBg = 'color_scheme_mobile_main_page_image_upload_impact';
                    $minWidth = 670;
                    $minHeight = 1080;
                    $numError = 21;
                    $error = validUploadFileImage($uploadBg, $numError, $minWidth, $minHeight);
                    if ($error == '') {
                        $setOptions = 'color_scheme_mobile_main_page_background_image_impact';
                        $addPrfFile = '_main_page_image_';
                        $compresion = 'color_scheme_mobile_main_page_image_compression_ratio_impact';
                        $fileParams = getParamsFile('main_page_image', $addPrfFile, $setOptions, $compresion, 'jpg', 'mobile');
                        $im = new Image();
                        $im->loadImage($_FILES[$uploadBg]['tmp_name']);
                        $im->saveImage($fileParams['file'], $fileParams['ratio']);
                        Common::saveFileSize($fileParams['file']);
                        unset($im);
                        $options[$setOptions] = $fileParams['name'];

                        if (get_param_int('image_upload_data')) {
                            @unlink($_FILES[$uploadBg]['tmp_name']);
                        }
                    } else {
                        $errors[] = $error;
                    }
                }

                if (
                    isset($options['paid_access_mode']) && $options['paid_access_mode'] != 'free_site'
                    && Common::getOption('paid_access_mode') == 'free_site'
                    && Common::isActiveFeatureSuperPowers('invisible_mode')
                ) {
                    User::resetOptionsInvisibleMode();
                }

                if (isset($options['number_of_columns_in_language_selector'])) {
                    $options['number_of_columns_in_language_selector'] = abs(intval($options['number_of_columns_in_language_selector']));
                    if (!$options['number_of_columns_in_language_selector']) {
                        $options['number_of_columns_in_language_selector'] = 1;
                    }
                }
            }


            /* EDGE - all downloads need to be done through this */
            $modules = array("{$this->name}_color_scheme_visitor");
            $optionsUpload = array(
                'edge_color_scheme_visitor' => array(
                    'main_page_image' => array(1920, 1080, 20, 'main')
                )
            );
            $temp_module = 'edge_color_scheme_visitor';
            if ((in_array($this->module, $modules) || $this->module == 'options')  && isset($optionsUpload[$temp_module])) {
                foreach ($optionsUpload[$temp_module] as $key => $param) {
                    $uploadFile = "{$key}_upload";

                    $setOptions = $key;
                    if ($key === 'main_page_image') {
                        $setOptions = $key . Common::templateFilesFolderType($this->name);
                    }

                    if (
                        isset($options[$setOptions])
                        && (isset($_FILES[$uploadFile]) || Common::uploadDataImageFromSetData(null, $uploadFile))
                    ) {
                        $minWidth = $param[0];
                        $minHeight = $param[1];
                        $numError = $param[2];
                        $error = validUploadFileImage($uploadFile, $numError, $minWidth, $minHeight);

                        if ($error == '') {
                            $templateFilesFolderType = Common::templateFilesFolderType($this->name);
                            $addPrfFile = '_main_page_image' . $templateFilesFolderType . '_';
                            $compresion = "{$key}_compression_ratio";
                            $fileParams = getParamsFile('main_page_image' . $templateFilesFolderType, $addPrfFile, $setOptions, $compresion, 'jpg', $param[3], $temp_module);
                            $ratio = $fileParams['ratio'];
                            if (isset($options[$compresion])) {
                                $ratio = $options[$compresion];
                            }
                            $im = new Image();
                            $im->loadImage($_FILES[$uploadFile]['tmp_name']);
                            $im->saveImage($fileParams['file'], $ratio);
                            Common::saveFileSize($fileParams['file']);
                            unset($im);
                            $options[$setOptions] = $fileParams['name'];

                            if (get_param_int('image_upload_data')) {
                                @unlink($_FILES[$uploadFile]['tmp_name']);
                            }
                        } else {
                            $errors[] = $error;
                        }
                    }
                }
            }

            if ($this->module == "{$this->name}_color_scheme_visitor" || $this->module == 'options') {
                if ($options['main_page_background_type'] == 'video') {
                    $codeVideo = '';
                    $curCodeVideo = Common::getOption('main_page_video_code');
                    if ($curCodeVideo) {
                        $codeVideo = json_decode($curCodeVideo, true);
                        $codeVideo = $codeVideo['code'];
                    }

                    $urlBgVideo = trim($options['main_page_video_code']);
                    if ($urlBgVideo != $codeVideo) {
                        preg_match('/(?:^|\/|v=)([\w\-]{11,11})(?:\?|&|#|$)/', $urlBgVideo, $urlBgVideoCode);
                        $isVideoCodeError = false;
                        if (!isset($urlBgVideoCode[1])) {
                            $isVideoCodeError = true;
                        } else {
                            $code = array($urlBgVideoCode[1], 1);
                            $url = 'https://www.youtube.com/oembed?url=youtu.be/' . $urlBgVideoCode[1];
                            $oembed_text = @urlGetContents($url);
                            if ($oembed_data = json_decode($oembed_text, true)) {
                                $ratio = 1.778;
                                $width = 0;
                                $height = 0;
                                if (isset($oembed_data['width'])) {
                                    $width = $oembed_data['width'];
                                }
                                if (isset($oembed_data['height'])) {
                                    $height = $oembed_data['height'];
                                }
                                if ($width && $height) {
                                    $ratio = round($oembed_data['width'] / $oembed_data['height'], 3);
                                }
                                $code['code'] = $urlBgVideo;
                                $code['ratio'] = $ratio;
                                $code['title'] = isset($oembed_data['title']) ? $oembed_data['title'] : '';
                                $code['width'] = $width;
                                $code['height'] = $height;
                                $options['main_page_video_code'] = json_encode($code);
                                // var_dump($code); die();
                            } else {
                                $isVideoCodeError = true;
                            }
                        }
                        if ($isVideoCodeError) {
                            $errors[] = 18;
                            $options['main_page_video_code'] = $curCodeVideo;
                        }
                    }
                } else {
                    unset($options['main_page_video_code']);
                }
            }

            /* EDGE - all downloads need to be done through this */

            $optionsPairedUpdate = array(
                'options' => array(
                    'google_apikey' => array('3d_city_street_chat', 'google_maps_api_key'),
                    '3dcity_menu_item_position' => array('3d_city', '3dcity_menu_item_position'),
                    '3dcity_history_messages' => array('3d_city', '3dcity_history_messages'),
                ),
                '3d_city_street_chat' => array(
                    'google_maps_api_key' => array('options', 'google_apikey'),
                ),
                '3d_city' => array(
                    '3dcity_menu_item_position' => array('options', '3dcity_menu_item_position'),
                    '3dcity_history_messages' => array('options', '3dcity_history_messages'),
                ),
            );
            if (isset($optionsPairedUpdate[$this->module])) {
                foreach ($optionsPairedUpdate[$this->module] as $key => $item) {
                    if (isset($options[$key])) {
                        Config::update($item[0], $item[1], $options[$key]);
                    }
                }
            }

            $params = '';
            if ($this->module == '3d_city_video') {
                $location = get_param('location');
                if ($location) {
                    $option = 'loc_' . $location;
                    $prepareValue = array();
                    foreach ($options[$option] as $value) {
                        $value = trim($value);
                        if ($value) {
                            $prepareValue[] = $value;
                        }
                    }
                    $options[$option] = $prepareValue ? json_encode($prepareValue) : '';
                    $params = '&loc=' . $location;
                }
            }

            $errors = array_diff($errors, array('', 4));
            $error = '';
            if (!empty($errors)) {
                $error = '&error=' . implode('_', $errors);
            }
            if (empty($options['admin_password'])) {
                unset($options['admin_password']);
            }
            if (empty($options['password'])) {
                unset($options['password']);
                unset($options['password2']);
            }

            if(isset($options['color_scheme_oryx'])) {
                $color = $options['color_scheme_oryx'];
                $upper = $options['upper_header_color_oryx'];
                $lower = $options['lower_header_color_oryx'];

                DB::update('color_scheme', array('upper' => $upper, 'lower' => $lower), '`color` = '.to_sql($color));
            }

            if (isset($options['delete_open_partyhouz_everytime'])) {
                $delete_open_partyhouz_everytime = $options['delete_open_partyhouz_everytime'];
                list($hours, $minutes) = explode(':', $delete_open_partyhouz_everytime);
                $delete_open_partyhouz_everytime = ($hours * 60) + $minutes;
                $options['delete_open_partyhouz_everytime'] = $delete_open_partyhouz_everytime;
            }

            if (isset($options['normal_partyhouz_delay_time'])) {
                $normal_partyhouz_delay_time = $options['normal_partyhouz_delay_time'];
                list($hours, $minutes) = explode(':', $normal_partyhouz_delay_time);
                $normal_partyhouz_delay_time = ($hours * 60) + $minutes;
                $options['normal_partyhouz_delay_time'] = $normal_partyhouz_delay_time;
            }

            $this->createColorSchemeSvgFooterFile($options);

            global $p;
            if($p == 'site_options.php') {
                $settingsType = 'edge';
                $settings = self::getSettings($settingsType);

                foreach ($settings['settings'] as $key => $value) {
                    $module = $settingsType . '_' . $key;
                    $new_options = [];

                    $edge_config = Config::getOptionsByModule($module, $this->getSort(), $this->getOrder(), true, $this->getAllowedOptions());
                    foreach ($edge_config as $k => $v) {
                        // if(isset($options["{$module}_{$v['option']}"])) {
                        //     $new_options[$v['option']] = $options["{$module}_{$v['option']}"];
                        //     unset($options["{$module}_{$v['option']}"]);
                        // }

                        if(isset($options["{$v['option']}"])) {
                            $new_options[$v['option']] = $options["{$v['option']}"];
                            unset($options["{$v['option']}"]);
                        }
                    }
                    Config::updateAll($module, $new_options);
                }
            }

            Config::updateAll($this->getModule(), $options);

            if ($this->urlParams) {
                $params = $this->urlParams;
            }
            redirect(Common::page() . '?action=saved' . $error . $params);
        }
    }


    function getConfig() {
        $lang = loadLanguageAdmin();

        $optionSet = $this->set;
        $optionTmplName = $this->name;
        $config = Config::getOptionsAll($this->getModule(), $this->getSort(), $this->getOrder(), true, $this->getAllowedOptions());

        $this->deactivateOption($config);

        /* EDGE - Temporarily deleted, since the option does not work */
        if ($this->module == 'edge_general_settings' || $this->module == 'edge_main_page_settings') {
            unset($config['list_people_number_row']);

            // unset($config['list_blog_posts_type_order']);
            // unset($config['list_blog_posts_display_type']);
            // unset($config['list_blog_posts_number_row']);
            // unset($config['list_blog_posts_number_items']);
            // unset($config['list_blog_posts_hide_from_guests']);
            // unset($config['list_blog_posts_browse_btn']);
        }
        /* EDGE - Temporarily deleted, since the option does not work */

        if ($this->module == 'trial') {
            if ($optionSet == 'urban') {
                // unset($config['type']); // Rade 2023-10-01
                unset($config['credits']);
            } else {
                unset($config['credits']);
            }
        }

        if ($this->module == 'image') {
            $notAvailable = array();
            if (Common::isMultisite()) {
                $notAvailable = array(
                    'quality_orig',
                    'big_x',
                    'big_y',
                    'medium_x',
                    'medium_y',
                    'small_x',
                    'small_y',
                    'root_x',
                    'root_y',
                    'gallery_width',
                    'gallery_height',
                    'affiliates_banner_width',
                    'affiliates_banner_height'
                );
            } elseif ($this->set !== NULL && $this->name != 'edge') {
                $notAvailable = array('blog_middle_x', 'blog_middle_y', 'blog_big_x', 'blog_big_y');
            }

            foreach ($notAvailable as $value) {
                unset($config[$value]);
            }
        }

        /*if ($this->module == 'image') {
                  $nameTmpl = Common::getOption('name', 'template_options');
                  $dimensions = array("big_x_{$nameTmpl}","big_y_{$nameTmpl}");
                  foreach ($config as $key => $value) {
                      $pos = strpos($key, 'big_');
                      if ($pos !== false && !in_array($key, $dimensions)) {
                          unset($config[$key]);
                      }
                  }
              }*/
        if ($this->module == 'image' && $optionSet != 'urban') {
            unset($config['min_photo_height_urban']);
            unset($config['min_photo_width_urban']);
        }


        if (IS_DEMO && $this->getModule() == 'options') {
            $config['detect_user_language']['value'] = 'Y';
        }
        $mobile = countFrameworks('mobile');
        if (!$mobile) {
            unset($config['mobile_redirect']);
        }
        if (Common::getOption('name', 'template_options') == 'mixer') {
            unset($config['main_users_number_oryx']);
            unset($config['main_users_number_new_age']);
        } elseif (Common::getOption('name', 'template_options') == 'oryx') {
            unset($config['main_users_number_new_age']);
            unset($config['main_users_number_mixer']);
        } elseif (Common::getOption('name', 'template_options') == 'new_age') {
            unset($config['main_users_number_oryx']);
            unset($config['main_users_number_mixer']);
        }
        if ($this->module == 'date_formats') {
            if (Common::getOption('name', 'template_options') == 'oryx') {
                unset($config['edit_event_date_mixer_js']);
            } else {
            }
        }
        if ($this->module == 'date_formats') {
            if (Common::getOption('name', 'template_options') == 'oryx') {
                unset($config['edit_hotdate_date_mixer_js']);
            } else {
            }
        }
        if ($this->module == 'date_formats') {
            if (Common::getOption('name', 'template_options') == 'oryx') {
                unset($config['edit_partyhou_date_mixer_js']);
            } else {
            }
        }


        if (!Common::isOptionActive('smooth_scroll', 'template_options')) {
            unset($config['smooth_scroll']);
        }
        if (!Common::isOptionActive('featured_users_on_main_page', 'template_options')) {
            unset($config['main_users']);
        }
        if (!Common::isOptionActive('new_videos_on_main_pag', 'template_options')) {
            unset($config['main_new_videos']);
        }
        if (Common::getOption('main_page_mode') != 'social') {
            unset($config['main_text']);
            unset($config['main_title']);
        }


        $templatesAllowColorSchema = array('oryx' => 1, 'impact' => 1);
        if (
            (Common::isOptionActive('header_color_admin', 'template_options')
                || Common::isOptionActive('color_scheme_activate', 'template_options'))
            && isset($templatesAllowColorSchema[$this->name])
        ) {
            unset($templatesAllowColorSchema[$this->name]);
        }
        // foreach ($templatesAllowColorSchema as $name => $value) {
        //     unset($config['color_scheme_' . $name]);
        //     unset($config['upper_header_color_' . $name]);
        //     unset($config['lower_header_color_' . $name]);
        //     unset($config['color_darker_' . $name]);
        // }

        if (!Common::isOptionActive('color_scheme_settings', 'template_options')) {
            // unset($config['allow_users_color_scheme']); // Rade 2023-09-29 delete
        }
        if (!Common::isOptionActive('website_background', 'template_options')) {
            unset($config['website_background_oryx']);
            unset($config['website_background_upload_oryx']);
            unset($config['website_background_compression_ratio_oryx']);
            unset($config['background_only_not_logged_oryx']);
        }
        $uploadImageMainPage = Common::isOptionActive('upload_image_main_page', 'template_options');
        if (
            !$uploadImageMainPage
            || Common::getOption('main_page_mode') != 'dating'
        ) { //|| Common::getOption('home_page_mode') != 'dating'
            // unset($config['upload_image_main_page']);
            // unset($config['image_main_page']);
        }

        if ($optionTmplName == 'impact') {
            unset($config['image_main_page_urban']);

            if (isset($config['map_on_main_page_urban'])) {
                $config['map_on_main_page_urban']['options'] = 'image|random_image|video';
            }
        } else {
            unset($config['image_main_page_impact']);
        }

        if (
            !Common::isOptionActive('upload_big_banner_main_page', 'template_options')
            || Common::getOption('main_page_mode') == 'dating'
        ) {
            unset($config['upload_big_banner_main_page']);
            unset($config['restore_upload_big_banner_main_page']);
            unset($config['url_big_banner_main_page']);
        }

        if (
            !Common::isOptionActive('upload_small_banner_main_page', 'template_options')
            || Common::getOption('main_page_mode') == 'dating'
        ) {
            unset($config['upload_small_banner_main_page']);
            unset($config['restore_upload_small_banner_main_page']);
            unset($config['url_small_banner_main_page']);
        }

        if (!Common::isOptionActive('top_five_button', 'template_options')) {
            unset($config['upload_image_top_five_button']);
            unset($config['restore_upload_image_top_five_button']);
        }

        if (!Common::isOptionActive('network', 'template_options')) {
            // unset($config['network']);
        }

        // Urban
        if (!Common::isOptionActive('arrow_on_main_page_urban', 'template_options')) {
            unset($config['arrow_on_main_page']);
        }

        if (!Common::isOptionActive('main_page_frm_login_shadow_urban', 'template_options')) {
            unset($config['main_page_frm_login_shadow_urban']);
        }
        if (!Common::isOptionActive('map_on_main_page_urban', 'template_options')) {
            unset($config['header_color_urban']);
            unset($config['map_on_main_page_urban']);
            unset($config['image_main_page_urban']);
            unset($config['upload_image_main_page_urban']);
            unset($config['image_main_page_compression_ratio_urban']);
            unset($config['background_color_urban']);
        } /*elseif(Common::getOption('map_on_main_page_urban') == 'map') {
          unset($config['image_main_page_urban']);
          unset($config['upload_image_main_page_urban']);
          unset($config['image_main_page_compression_ratio_urban']);
          unset($config['background_color_urban']);
      }*/

        if (!Common::isOptionActive('information_block_on_main_page_urban', 'template_options')) {
            unset($config['main_text_title_urban']);
            unset($config['default_title_with_location_urban']);
            unset($config['information_block_on_main_page_urban']);
        } elseif (Common::isOptionActive('information_block_on_main_page_urban')) {
            if (Common::getOption('default_title_with_location_urban') == 'location') {
                unset($config['main_text_title_urban']);
            }
        } else {
            unset($config['main_text_title_urban']);
            unset($config['default_title_with_location_urban']);
        }

        if (!Common::isOptionActive('facebook_like_button', 'template_options')) {
            unset($config['facebook_like_button']);
            unset($config['facebook_like_button_html']);
        }

        if (!Common::isOptionActive('tiled_footer_urban', 'template_options')) {
            unset($config['tiled_footer_urban']);
            unset($config['footer_tile_image_urban']);
            unset($config['footer_tile_image_height_urban']);
            unset($config['upload_footer_tile_image_urban']);
            unset($config['footer_solid_color_urban']);
            unset($config['footer_tile_image_compression_ratio_urban']);
        } elseif (Common::getOption('tiled_footer_urban') == 'color') {
            unset($config['footer_tile_image_urban']);
            unset($config['upload_footer_tile_image_urban']);
            unset($config['footer_tile_image_compression_ratio_urban']);
        } else {
            unset($config['footer_solid_color_urban']);
        }

        if (!Common::isOptionActive('footer_image_urban', 'template_options')) {
            unset($config['footer_image_urban']);
            unset($config['upload_footer_image_urban']);
            unset($config['footer_image_compression_ratio_urban']);
        }

        if (!Common::isOptionActive('rate_see_my_photo_rating', 'template_options')) {
            unset($config['rate_see_my_photo_rating']);
            //People I need to rate to see my photo rating
        }
        // Urban
        if ($optionSet == 'urban') {
            // unset($config['user_choose_default_profile_view']); // Rade 2023-09-29 delete
        } else {
            unset($config['view_home_page_urban']);
            unset($config['default_profile_background']);
            unset($config['search_service_number']);
            unset($config['spotlight_photos_number']);
            //unset($config['max_search_distance']);
            //unset($config['unit_distance']);
            unset($config['standart_like_button']);
            unset($config['show_interests_search_results_urban']);

            // Main
            //unset($config['fb_like_button_script']);
            //unset($config['fb_like_button_html']);

            unset($config['hide_profile_data_for_guests_urban']);
            unset($config['sp_sending_messages_per_day_urban']);
            unset($config['gifts_enabled']);
            unset($config['youtube_video_background_users_urban_quality']);
            unset($config['youtube_video_background_users_all_pages_urban']);
        }

        if (!Common::isOptionActive('main_page_header_background_color', 'template_options') && !$this->isOptionsTemplate()) {
            unset($config['main_page_header_background_color']);
        }

        if ($this->getModule() == 'main') {
            if (!Common::isOptionActive('options_main_title', 'template_options')) {
                unset($config['main_title']);
            }
            if (!Common::isOptionActive('options_main_text', 'template_options')) {
                unset($config['main_text']);
            }
        }


        // unset($config['main_page_mode']);

        return $config;
    }

    function parseBlock(&$html)
    {
        global $p;

        $lang = loadLanguageAdmin();

        $optionSet = $this->set;
        $optionTmplName = $this->name;

        $config = $this->getConfig();
        $hideSiteSections = Common::getOption('hide_site_sections', 'template_options');


        $html->setvar('tmpl', get_param('tmpl', ''));

        $first = 'not_first';

        //collect section object to sectionArrays

        // Group according to items whose type is section
        $sectionPositions = array_filter($config, function($arr) {
            return isset($arr["type"]) && $arr["type"] === "section";
        });

        $sectionArrays = [];

        foreach ($sectionPositions as $position) {

            // Get the next $position
            $nextPosition = next($sectionPositions);
            if ($nextPosition === false) {
                $nextP = 10000;
                // End of the array, do something if needed
            } else {
                // Access the next $position
                $nextP = intval($nextPosition['position']);
                // Use $nextP as needed
            }

            $groupedArrays = [];
            $pp = intval($position['position']);

            foreach ($config as $key => $value) {
                if (intval($value['position']) > $pp && intval($value['position']) < $nextP) {
                    $groupedArrays[$key] = $value;
                }
            }

            $sectionArrays[$position['option']] = $groupedArrays;
        }

        if($p == 'site_options.php') {

            //social login
            $social_login_sections = array('facebook_settings', 'google_plus_settings', 'twitter_settings', 'linkedin_settings', 'vk_settings');

            $field_social_logins = [];

            foreach ($social_login_sections as $key => $social_login_section) {

                $social_item = $sectionArrays[$social_login_section];
                foreach ($social_item as $k => $v) {
                    $field_social_logins[$social_login_section] =  array(
                        'type' => 'section',
                        'option' => $social_login_section,
                        'show_in_admin' => 1,
                        'options' => ''
                    );
                    $field_social_logins[$k] = $v;
                }
                unset($sectionArrays[$social_login_section]);
            }

            $sectionArrays['social_login'] = $field_social_logins;

            $this->setModule('edge_general_settings');
            $config = $this->getConfig();
            $settingsType = 'edge';

            $menuSettingsTemplate = self::getSettings($settingsType);

            foreach ($menuSettingsTemplate['settings'] as $key => $settings_item) {
                if($settings_item['type'] == 'order') {
                    continue;
                } 

                $this->setModule("{$settingsType}_{$key}");

                $config = $this->getConfig();

                if($key == 'general_settings') {
                    unset($config['list_people_display_type']);
                    unset($config['list_people_number_row']);
                    unset($config['list_people_number_users']);
                    unset($config['list_people_hide_from_guests']);
                    unset($config['list_blog_posts_display_type']);
                    unset($config['list_blog_posts_hide_from_guests']);
                    unset($config['list_videos_hide_from_guests']);
                    unset($config['list_photos_hide_from_guests']);
                    unset($config['list_pages_hide_from_guests']);
                    unset($config['list_groups_hide_from_guests']);
                    unset($config['list_live_hide_from_guests']);
                    unset($config['list_songs_hide_from_guests']);
                    unset($config['mobile_3dcity_on_tablet']);
                }

                if($key == 'main_page_settings') {
                    $oryx_active_features_unsets = array(
                        'gdpr_cookie_consent_popup',  'gdpr_cookie_consent_popup_app', 'gdpr_cookie_consent_popup_theme', 'gdpr_cookie_consent_popup_width', 'gdpr_cookie_consent_popup_unit',
                        'gdpr_cookie_consent_popup_position'
                    );

                    $tempArray = [];

                    foreach ($oryx_active_features_unsets as $oryx_key => $oryx_value) {
                        $tempArray[$oryx_value] = $sectionArrays['active_features_settings'][$oryx_value];
                        unset($sectionArrays['active_features_settings'][$oryx_value]);
                    }

                    $config =  $config + $tempArray;


                    unset($config['list_people_type_order']);
                    unset($config['list_people_display_type']);
                    unset($config['list_people_number_row']);
                    unset($config['list_people_number_users']);
                    unset($config['list_people_browse_btn']);
                    unset($config['list_blog_posts_type_order']);
                    unset($config['list_blog_posts_display_type']);
                    unset($config['list_blog_posts_number_row']);
                    unset($config['list_blog_posts_number_items']);
                    unset($config['list_blog_posts_browse_btn']);
                    unset($config['list_videos_type_order']);
                    unset($config['list_videos_number_row']);
                    unset($config['list_videos_number_items']);
                    unset($config['list_videos_browse_btn']);
                    unset($config['list_photos_type_order']);
                    unset($config['list_photos_display_type']);
                    unset($config['list_photos_number_row']);
                    unset($config['list_photos_number_items']);
                    unset($config['list_photos_browse_btn']);
                    unset($config['list_pages_type_order']);
                    unset($config['list_pages_display_type']);
                    unset($config['list_pages_number_row']);
                    unset($config['list_pages_number_items']);
                    unset($config['list_pages_browse_btn']);
                    unset($config['list_groups_type_order']);
                    unset($config['list_groups_display_type']);
                    unset($config['list_groups_number_row']);
                    unset($config['list_groups_number_items']);
                    unset($config['list_groups_browse_btn']);
                    unset($config['list_live_type_order']);
                    unset($config['list_live_number_row']);
                    unset($config['list_live_number_items']);
                    unset($config['list_live_show_not_ended']);
                    unset($config['list_live_browse_btn']);
                    unset($config['list_songs_type_order']);
                    unset($config['list_songs_number_items']);
                    unset($config['list_songs_browse_btn']);
                }

                if($key == 'member_settings')
                {
                    unset($config['number_photos_left_column']);
                    unset($config['number_videos_left_column']);
                    unset($config['number_pages_left_column']);
                    unset($config['number_groups_left_column']);
                    unset($config['number_blogs_left_column']);
                    unset($config['number_songs_left_column']);
                    unset($config['number_friends_right_column']);
                    unset($config['number_friends_online_right_column']);
                    unset($config['videos_list_1_type_order']);
                    unset($config['number_videos_list_1_right_column']);
                    unset($config['videos_list_2_type_order']);
                    unset($config['number_videos_list_2_right_column']);
                    unset($config['photos_list_1_type_order']);
                    unset($config['number_photos_list_1_right_column']);
                    unset($config['photos_list_2_type_order']);
                    unset($config['number_photos_list_2_right_column']);
                    unset($config['songs_list_1_type_order']);
                    unset($config['number_songs_list_1_right_column']);
                    unset($config['songs_list_2_type_order']);
                    unset($config['number_songs_list_2_right_column']);
                    unset($config['blogs_list_1_type_order']);
                    unset($config['number_blogs_list_1_right_column']);
                    unset($config['blogs_list_2_type_order']);
                    unset($config['number_blogs_list_2_right_column']);
                    unset($config['number_blogs_list_last_right_column']);
                    unset($config['min_width_profile_cover_img']);
                    unset($config['min_height_profile_cover_img']);
                    unset($config['header_profile_my']);
                    unset($config['header_profile_someones']);
                    unset($config['header_page_inner']);
                    unset($config['show_your_profile_from_search']);
                    unset($config['show_age_profile']);
                    unset($config['show_columns_inner_pages']);
                }

                if($key == 'color_scheme_general') {
                    $oryx_general_settings_unsets = array(
                        'color_scheme_oryx',  'upper_header_color_oryx', 'lower_header_color_oryx', 'color_darker_oryx', 'website_background_oryx',
                        'website_background_upload_oryx', 'website_background_compression_ratio_oryx', 'background_only_not_logged_oryx'
                    );

                    $tempArray = [];

                    foreach ($oryx_general_settings_unsets as $oryx_key => $oryx_value) {
                        $tempArray[$oryx_value] = $sectionArrays['general_settings'][$oryx_value];
                        unset($sectionArrays['general_settings'][$oryx_value]);
                    }

                    $config = $tempArray + $config;

                    unset($config['footer_background_color']);
                    unset($config['footer_title_orig_color']);
                    unset($config['footer_title_orig_color_opacity']);
                    unset($config['footer_title_h3_color']);
                    unset($config['footer_menu_color']);
                    unset($config['footer_menu_color_opacity']);
                    unset($config['footer_menu_color_hover']);
                    unset($config['footer_menu_color_hover_opacity']);
                    unset($config['footer_text_color']);
                    unset($config['footer_text_color_opacity']);
                    unset($config['footer_btn_background_color']);
                    unset($config['footer_btn_text_color']);
                    unset($config['footer_btn_border_color']);
                    unset($config['footer_btn_border_color_opacity']);
                    unset($config['footer_btn_background_color_hover']);
                    unset($config['footer_btn_text_color_hover']);
                    unset($config['footer_btn_border_color_hover']);
                    unset($config['footer_btn_border_color_hover_opacity']);
                    unset($config['banner_background_color']);
                    unset($config['banner_border_color']);
                    unset($config['banner_btn_remove_color']);
                    unset($config['banner_btn_remove_color_hover']);

                    unset($config['btn_success_background_color']);
                    unset($config['btn_success_border_color']);
                    unset($config['btn_success_text_color']);
                    unset($config['btn_success_background_color_hover']);
                    unset($config['btn_success_border_color_hover']);
                    unset($config['btn_success_text_color_hover']);
                    unset($config['btn_success_background_color_disabled']);
                    unset($config['btn_success_border_color_disabled']);
                    unset($config['btn_success_text_color_disabled']);
                    unset($config['btn_primary_background_color']);
                    unset($config['btn_primary_border_color']);
                    unset($config['btn_primary_text_color']);
                    unset($config['btn_primary_background_color_hover']);
                    unset($config['btn_primary_border_color_hover']);
                    unset($config['btn_primary_text_color_hover']);
                    unset($config['btn_primary_background_color_disabled']);
                    unset($config['btn_primary_border_color_disabled']);
                    unset($config['btn_primary_text_color_disabled']);
                    unset($config['btn_primary_2_background_color']);
                    unset($config['btn_primary_2_border_color']);
                    unset($config['btn_primary_2_text_color']);
                    unset($config['btn_primary_2_border_color_hover']);

                    unset($config['btn_primary_2_text_color_hover']);
                    unset($config['btn_primary_2_background_color_disabled']);
                    unset($config['btn_primary_2_border_color_disabled']);
                    unset($config['btn_primary_2_text_color_disabled']);
                    unset($config['btn_secondary_background_color']);
                    unset($config['btn_secondary_border_color']);
                    unset($config['btn_secondary_text_color']);
                    unset($config['btn_secondary_background_color_hover']);
                    unset($config['btn_secondary_border_color_hover']);
                    unset($config['btn_secondary_text_color_hover']);
                    unset($config['btn_secondary_background_color_disabled']);
                    unset($config['btn_secondary_border_color_disabled']);
                    unset($config['btn_secondary_text_color_disabled']);
                    unset($config['btn_secondary_2_background_color']);
                    unset($config['btn_secondary_2_border_color']);
                    unset($config['btn_secondary_2_text_color']);
                    unset($config['btn_secondary_2_background_color_hover']);
                    unset($config['btn_secondary_2_border_color_hover']);
                    unset($config['btn_secondary_2_text_color_hover']);
                    unset($config['btn_secondary_2_background_color_disabled']);
                    unset($config['btn_secondary_2_border_color_disabled']);
                    unset($config['btn_secondary_2_text_color_disabled']);

                    unset($config['color_1']);
                    unset($config['label_online_opacity']);
                    unset($config['color_2']);

                    unset($config['audio_player_background_color']);
                    unset($config['audio_player_border_color']);
                    unset($config['audio_player_background_progress']);             
                    unset($config['audio_player_background_progress_active']);
                    unset($config['audio_player_btn_play_color']);
                    unset($config['audio_player_btn_play_color_hover']);
                    unset($config['audio_player_btn_remove_color']);
                    unset($config['audio_player_btn_remove_color_hover']);
                    unset($config['loader_color']);

                }

                if($key == 'color_scheme_member') {
                    unset($config['member_content_background_color']);
                    unset($config['member_column_left_background_color']);
                    unset($config['member_column_right_background_color']);
                    unset($config['member_navbar_background_color']);
                    unset($config['member_navbar_menu_short_color']);
                    unset($config['member_navbar_menu_short_color_hover']);
                    unset($config['member_navbar_menu_short_color_disabled']);
                    unset($config['member_navbar_menu_text_color']);
                    unset($config['member_navbar_menu_text_color_active']);
                    unset($config['member_navbar_menu_background_color_active']);
                    unset($config['member_navbar_menu_more_background_color']);
                    unset($config['member_navbar_menu_more_text_color']);
                    unset($config['member_navbar_menu_mobile_background_color']);
                    unset($config['member_navbar_menu_mobile_text_color']);
                    unset($config['member_navbar_menu_counter_background_color']);
                    unset($config['member_navbar_menu_counter_text_color']);
                    unset($config['member_navbar_menu_more_background_color_highlighted']);
                    unset($config['member_navbar_menu_more_text_color_highlighted']);
                }

                if($key == 'color_scheme_visitor') {
                    unset($config['main_page_download_apps_block_background_color']);
                    unset($config['main_page_download_apps_block_text_color']);

                    unset($config['visitor_content_background_color']);
                    unset($config['visitor_icon_color']);
                    unset($config['visitor_header_tag_color']);
                    unset($config['visitor_signature_text_color']);
                    unset($config['visitor_social_btn_color']);
                    unset($config['visitor_form_link_color']);
                    unset($config['visitor_form_link_color_hover']);
                }

                if($key == 'groups_settings') {
                    unset($config['number_photos_left_column']);
                    unset($config['number_videos_left_column']);
                    unset($config['number_subscribers_right_column']);
                }

                if($key == 'live_settings') {
                    unset($config['live_marker_color']);
                } 

                if($key == 'events_settings') {
                    unset($config['calendar_enabled']);
                }

                $new_config = [];
                foreach ($config as $config_key => $config_value) {
                    $new_config_option = "{$settingsType}_{$key}_{$config_key}";
                    $new_config_value = $config_value;
                    $new_config_value['module_option'] = "{$settingsType}_{$key}_{$config_value['option']}";
                    $new_config[$config_key] = $new_config_value;
                }

                $sectionTitle = "{$settingsType}_{$key}";
                $sectionArrays[$sectionTitle] = $new_config;
            }
        }

        if(!$sectionArrays) {
            $sectionArrays['other_options'] = $config;
        }

        //sectionArrays end

        foreach ($sectionArrays as $key_s => $value_s) {

            if($key_s != 'other_options'){
                $html->setvar('section_dropdown_name', l('field_' . $key_s));
                $html->parse('section_dropdown_start');
            }

            foreach ($value_s as $key => $row) {
                $groupOptionsStart = array(
                    'main_page_header_background_type',
                    'color_scheme_background_type_impact',
                    'color_scheme_mobile_main_page_background_type_impact'
                );
                if (in_array($row['option'], $groupOptionsStart) && !$this->isOptionsTemplate()) {
                    $html->setvar('item_group_options_init', $row['option']);
                    $html->parse('item_group_options_start');
                } else {
                    $html->clean('item_group_options_start');
                }

                if (Common::getOption('name', 'template_options') != 'impact' && substr($key, -7) == '_impact') {
                    continue;
                }

                //            if ($this->getModule() == 'main') {
                //                if ($key == 'title' || $key == 'main_title' || $key == 'main_text') {
                //                    $row['value'] = he($row['value']);
                //                }
                //            }

                if ($this->getModule() == 'options' && Common::isValidArray($hideSiteSections) && in_array($key, $hideSiteSections)) {
                    continue;
                }

                if ($row['show_in_admin'] == 0) {
                    continue;
                }

                if (IS_DEMO) {
                    Demo::replaceOptionValue($row);
                }

                if ($row['type'] != 'section' && $row['type'] != 'label') {

                    if ($key === 'main_page_image_mode_lms') {
                        $key = 'main_page_image';
                    }

                    $keyTitle = 'field_' . $key;
                    $titleField = lCascade(l($keyTitle), array($keyTitle . '_' . $optionTmplName));
                    $html->setvar('label', $titleField);
                    if ($row['option'] == 'users_on_main_page_map_and_mobile') {
                        if ($optionSet == 'urban') {
                            $html->setvar('label', l('field_' . $key . '_set_urban'));
                        }
                    }

                    $desc = '';
                    $descKey = 'field_' . $key . '_desc';
                    if ($descKey != l($descKey)) {
                        $desc = l($descKey);
                    }
                    $html->setvar('label_desc', $desc);
                }

                if ($row['type'] == 'text') {
                    if ($row['options'] == 'special_field:background_video' && $row['value']) {
                        $codeVideo = json_decode($row['value'], true);
                        $row['value'] = $codeVideo['code'];
                    } else {
                        $row['value'] = he($row['value']);
                    }
                }

                // var_dump($row); die();

                foreach ($row as $k => $v) {
                    // if($k == 'module_option') continue;
                    $html->setvar($k, $v);
                }
                // if(isset($row['module_option'])) {
                //     $html->setvar('option', $row['module_option']);
                // }

                $field = $row['type'];


                if ($field == 'checkbox') {
                    $checked = '';
                    if ($row['value'] == 1 || $row['value'] == 'Y') {
                        $checked = 'checked';
                    }
                    $html->setvar('checked', $checked);
                    if ($row['options'] == 'special_field:recaptcha_enabled') {
                        $html->parse('recaptcha_enabled', false);
                    } else {
                        $html->clean('recaptcha_enabled');
                    }
                }

                if ($row['type'] == 'select_multiple') {
                    $prepareValue = json_decode($row['value']);
                    if ($prepareValue) {
                        foreach ($prepareValue as $value) {
                            $html->setvar('value', $value);
                            $html->parse('item_select_multiple_item', true);
                        }
                    } else {
                        $html->parse('item_select_multiple_item', false);
                    }
                }

                if ($field == 'selectbox') {

                    $optionsArray = array();
                    if ($key == 'city_language') {
                        if (file_exists(dirname(__FILE__) . '/../../_server/city/langs.php')) {
                            include dirname(__FILE__) . '/../../_server/city/langs.php';
                            foreach ($city_langs as $cl) {
                                $optionsArray[$cl] = $cl;
                            }
                        }
                    } elseif ($key == 'main_users_photo_size' || $row['options'] == 'special_field:user_photo_size') {
                        $photoSizes = array(
                            'r' => 'root',
                            's' => 'small',
                            'm' => 'medium',
                        );
                        $delimiter = 'x';

                        foreach ($photoSizes as $photoSize => $sizeTitle) {
                            $optionsArray[$photoSize] = Common::getOption($sizeTitle . '_x', 'image') . $delimiter . Common::getOption($sizeTitle . '_y', 'image');
                        }
                    } elseif ($row['options'] == 'special_field:background_video_volume') {
                        for ($i = 0; $i <= 10; $i++) {
                            $optionsArray[$i * 10] = $i * 10;
                        }
                    } elseif ($row['options'] == 'special_field:face_input_size') {
                        $optionsArray = array(
                            128 => 128,
                            160 => 160,
                            224 => 224,
                            320 => 320,
                            416 => 416,
                            512 => 512,
                            608 => 608
                        );
                    } 
                     elseif ($row['options'] == 'special_field:face_score_threshold') {
                        for ($i = 1; $i <= 9; $i++) {
                            $optionsArray[$i] = $i / 10;
                        }
                    } elseif ($row['options'] == 'special_field:notifications_position') {
                        $optionsArray = array(
                            'left' => l('left_lower_corner'),
                            'right' => l('right_lower_corner'),
                        );
                    } elseif ($row['options'] == 'special_field:week_days') {
                        $optionsArray = array(
                            1 => l('day_of_week_1'),
                            2 => l('day_of_week_2'),
                            3 => l('day_of_week_3'),
                            4 => l('day_of_week_4'),
                            5 => l('day_of_week_5'),
                            6 => l('day_of_week_6'),
                            0 => l('day_of_week_0'),
                        );
                    } elseif ($key == 'youtube_video_background_users_urban_quality') {
                        $optionsArray = array(
                            'default' => l('video_quality_default'),
                            'small' => l('video_quality_small'),
                            'medium' => l('video_quality_medium'),
                            'large' => l('video_quality_large'),
                            'hd720' => l('video_quality_hd720'),
                            'hd1080' => l('video_quality_hd1080'),
                            'highres' => l('video_quality_highres'),
                        );
                    } elseif ($row['options'] == 'special_field:birth_year') {
                        $start = date('Y') - Common::getOption('users_age_max');
                        $end = date("Y") - Common::getOption('users_age') + 1;
                        for ($i = $start; $i <= $end; $i++) {
                            $optionsArray[$i] = $i;
                        }
                    } elseif ($row['options'] == 'special_field:color_scheme') {
                        $color = Common::getOption('color_scheme', 'template_options');

                        $isColorSchemeJson = false;
                        if (Common::isOptionActive('color_scheme_json', 'template_options')) {
                            $html->setvar('item_color_scheme_by_config', json_encode($color));
                            $html->parse('item_color_scheme_by_config');
                            $isColorSchemeJson = true;
                        }
                        foreach ($color as $title => $value) {
                            $optionsArray[$title] = $value['title'];
                            if (!$isColorSchemeJson) {
                                $html->setvar('title_color', $title);
                                $html->setvar('value_upper', $value['upper']);
                                $html->setvar('value_lower', $value['lower']);
                                $html->parse('item_color_value');
                            }
                        }
                        $html->parse('item_schema_color_js', false);
                    } elseif ($row['options'] == 'special_field:background' || $row['options'] == 'special_field:background_preview') {
                        $optionsArray['none'] = l('none_bg');
                        $removeFromFilename = '';
                        $sortBy = SORT_NUMERIC;
                        $addTmpl = '';
                        $addTitle = '';
                        $extension = 'png';
                        $urlToFileDir = '';
                        $scanFilesTmpl = true;

                        $isMobileTemplateFile = false;

                        if ($row['option'] == 'image_main_page_urban') {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/main_page_image';
                            $default = Common::getOption('main_page_image_default_urban', 'template_options');
                            $addTitle = l('image');
                            $addTmpl = '_main_page_image_';
                            $setOption = 'image_main_page_urban';
                            $ajaxCmd = 'get_url_main_page_image_urban';
                            $emptyAvailable = 0;
                            $extension = 'jpg';
                        } elseif ($row['option'] == 'footer_tile_image_urban') {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/footer_tiles';
                            $default = Common::getOption('footer_tile_image_default_urban', 'template_options');
                            $addTitle = l('tiles');
                            $addTmpl = '_footer_tile_image_';
                            $setOption = 'footer_tile_image_urban';
                            $ajaxCmd = 'get_url_footer_tile_image_urban';
                            $extension = 'png';
                            $emptyAvailable = 0;
                        } elseif ($row['option'] == 'footer_image_urban') {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/footer_image';
                            $default = Common::getOption('footer_image_default_urban', 'template_options');
                            $addTitle = l('image');
                            $addTmpl = '_footer_image_';
                            $setOption = 'footer_image_urban';
                            $ajaxCmd = 'get_url_footer_image_urban';
                            $extension = 'png';
                            $emptyAvailable = 1;
                        } elseif ($row['option'] == 'default_profile_background') {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/patterns';
                            $default = '35.png';
                            $addTmpl = '';
                            $addTitle = l('image');
                            $setOption = 'default_profile_background';
                            $ajaxCmd = 'get_url_background_profile';
                            $extension = 'png';
                            $emptyAvailable = 0;
                        } elseif ($row['option'] == 'color_scheme_menu_icons_impact') {
                            $pathInTmplDir = 'images/menu_icons/';
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . $pathInTmplDir;
                            $urlToFileDir = Common::getOption('url_tmpl_main', 'tmpl') . $pathInTmplDir;
                            $default = 'icons_nav_default.png';
                            $removeFromFilename = 'icon_nav_';
                            $setOption = 'default_profile_background';
                            $ajaxCmd = 'get_url_background_profile';
                            $emptyAvailable = 1;
                            $sortBy = SORT_STRING;
                            $scanFilesTmpl = false;
                        } elseif ($row['option'] == 'color_scheme_background_image_impact') {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/main_page_image';
                            $default = Common::getOption('color_scheme_background_image', 'template_options');
                            $addTitle = l('image');
                            $addTmpl = '_main_page_image_';
                            $setOption = 'color_scheme_background_image';
                            $ajaxCmd = 'get_url_main_page_image_urban';
                            $emptyAvailable = 1;
                            $extension = 'jpg';
                        } elseif ($row['option'] == 'image_main_page_impact') {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/main_page_image';
                            $default = Common::getOption('main_page_image_default', 'template_options');
                            $addTitle = l('image');
                            $addTmpl = '_main_page_image_';
                            $setOption = $row['option'];
                            $ajaxCmd = 'get_url_main_page_image_urban';
                            $emptyAvailable = 1;
                            $extension = 'jpg';
                        } elseif ($row['option'] == 'color_scheme_mobile_main_page_background_image_impact') {
                            $dir = Common::getOption('dir_tmpl_mobile', 'tmpl') . 'images/main_page_image';
                            $default = Common::getOption('color_scheme_mobile_main_page_background_image', 'template_options_mobile');
                            $addTitle = l('image');
                            $addTmpl = '_main_page_image_';
                            $setOption = $row['option'];
                            $ajaxCmd = 'get_url_mobile_main_page_image';
                            $emptyAvailable = 1;
                            $extension = 'jpg';
                            $isMobileTemplateFile = true;
                        } elseif (
                            $row['module'] == "{$optionTmplName}_color_scheme_visitor"
                            && ($row['option'] == 'main_page_image' || $row['option'] == 'main_page_image_mode_lms')
                        ) {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/' . $row['option'];
                            $default = Common::getOption('main_page_image', 'template_options');
                            $addTitle = l('image');
                            $addTmpl = '_main_page_image_';
                            $setOption = $row['option'];
                            $ajaxCmd = 'get_url_main_page_image_urban';
                            $emptyAvailable = 1;
                            $extension = 'jpg';
                        } else {
                            $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/backgrounds';
                            $default = Common::getOption('website_background_default', 'template_options');
                            $addTitle = l('background');
                            $addTmpl = '_bg_';
                            $setOption = 'website_background_oryx';
                            $ajaxCmd = 'get_url_background_tmpl';
                            $emptyAvailable = 0;
                            $extension = 'jpg';
                        }

                        $html->setvar('item_background_js_url_to_file_dir', $urlToFileDir);

                        if (!isset($extension) || empty($extension))
                            $extension = 'jpg';
                        $optionsArray = array();
                        if (in_array($this->name, array('impact', 'edge'))) {
                            if ($row['option'] == 'color_scheme_menu_icons_impact') {
                                $optionsArray['1px.png'] = l('none_bg');
                            } else {
                                $optionsArray['no_image'] = l('none_bg');
                            }
                        } elseif ($row['option'] == 'default_profile_background') {
                            $optionsArray[''] = l('none_bg');
                        }
                        $optionsArray += readAllFileArrayOfDir($dir, $addTitle, $sortBy, '', '', $extension);
                        $isFile = true;
                        if (file_exists($dir . '/' . $row['value']))
                            $isFile = false;
                        $dir = Common::getOption('url_files', 'path') . 'tmpl';
                        $templateFile = Common::getOption($isMobileTemplateFile ? 'mobile' : 'main', 'tmpl') . $addTmpl;
                        if (file_exists($dir . '/' . $templateFile . $row['value']))
                            $isFile = false;

                        if ($scanFilesTmpl) {
                            $optionsArray += readAllFileArrayOfDir($dir, $addTitle, $sortBy, $templateFile, l('uploaded_by_you'), $extension);
                        }
                        if ($row['option'] == 'color_scheme_menu_icons_impact') {
                            foreach ($optionsArray as $optionsArrayKey => $optionsArrayValue) {
                                $optionsArray[$optionsArrayKey] = ucfirst(trim(str_replace('icons_nav_', '', $optionsArrayValue)));
                            }
                        }

                        $html->setvar('ajax_cmd', $ajaxCmd);
                        $html->setvar('emptyAvailable', $emptyAvailable);
                        $html->setvar('item_background_img_default', $default);
                        if ($this->name == 'impact' && $row['value'] == 'no_image') {
                            $isFile = false;
                        }
                        if ($isFile) {
                            Config::update('options', $setOption, $default);
                        }
                    } elseif ($row['options'] == 'special_field:image_main_page') {
                        $optionsArray['default'] = l('default');

                        $ajaxCmd = 'get_url_image_main_page';
                        $addTitle = l('image');
                        $addTmpl = '_main_page_dating_bg_user';

                        $dir = Common::getOption('url_files', 'path') . 'tmpl';
                        $templateFile = Common::getOption('main', 'tmpl') . '_main_page_dating_bg_user';
                        $optionsArray += readAllFileArrayOfDir($dir, $addTitle, SORT_NUMERIC, $templateFile, l('uploaded_by_you'));
                        $html->setvar('ajax_cmd', $ajaxCmd);
                        $html->setvar('emptyAvailable', $emptyAvailable);
                        $html->setvar('item_background_img_default', 'default');
                    } elseif ($row['options'] == 'special_field:timezone') {
                        $options = TimeZone::getTimeZoneOptionsSelect($row['value']);
                        $time = array(
                            'time_utc' => gmdate('Y-m-d H:i:s'),
                            'time_local' => TimeZone::getDateTimeZone($row['value'])
                        );
                        $html->setvar('info_timezone', lSetVars('info_timezone', $time));
                        $html->parse('info_timezone', false);
                    } elseif (
                        in_array(
                            $row['option'],
                            array(
                                'list_people_number_row',
                                'list_blog_posts_number_row',
                                'list_groups_number_row',
                                'list_pages_number_row',
                                'list_videos_number_row',
                                'list_photos_number_row',
                                'list_blog_my_posts_number_row',
                                'list_blog_someones_posts_number_row',
                                'list_live_number_row'
                            )
                        )
                    ) {
                        if (
                            $row['option'] == 'list_people_number_row'
                            || $row['option'] == 'list_photos_number_row'
                        ) {
                            $optionsArray = array(
                                0 => 2,
                                4 => 3,
                                3 => 4,
                                2 => 6
                            );
                        } elseif (
                            $row['option'] == 'list_blog_posts_number_row'
                            || $row['option'] == 'list_blog_my_posts_number_row'
                            || $row['option'] == 'list_blog_someones_posts_number_row'
                            || $row['option'] == 'list_groups_number_row'
                            || $row['option'] == 'list_pages_number_row'
                        ) {
                            $optionsArray = array(
                                0 => 2,
                                4 => 3,
                                3 => 4
                            );
                        } elseif ($row['option'] == 'list_videos_number_row' || $row['option'] == 'list_live_number_row') {
                            $optionsArray = array(
                                0 => 2,
                                4 => 3,
                                //3 => 4
                            );
                        }
                    } elseif (
                        in_array(
                            $row['option'],
                            array(
                                'list_blog_posts_type_order',
                                'blogs_list_1_type_order',
                                'blogs_list_2_type_order',
                                'list_blog_my_posts_type_order',
                                'list_blog_someones_posts_type_order'
                            )
                        )
                    ) {
                        $noRandom = $row['module'] == 'edge_general_settings' || $row['module'] == 'edge_member_settings' || $row['module'] == 'edge_blogs_settings';
                        $optionsArray = Blogs::getTypeOrderList($noRandom);
                    } elseif ($row['option'] == 'list_groups_type_order' || $row['option'] == 'list_pages_type_order') {
                        $optionsArray = GroupsList::getTypeOrderList($row['module'] == 'edge_general_settings');
                    } elseif (in_array($row['option'], array('list_videos_type_order', 'videos_list_1_type_order', 'videos_list_2_type_order'))) {
                        $noRandom = in_array($row['option'], array('videos_list_1_type_order', 'videos_list_2_type_order'))
                            || $row['module'] == 'edge_general_settings';
                        $optionsArray = CProfileVideo::getTypeOrderVideosList($noRandom);
                    } elseif (in_array($row['option'], array('list_photos_type_order', 'photos_list_1_type_order', 'photos_list_2_type_order'))) {
                        $noRandom = in_array($row['option'], array('photos_list_1_type_order', 'photos_list_2_type_order'))
                            || $row['module'] == 'edge_general_settings';
                        $optionsArray = CProfilePhoto::getTypeOrderPhotosList($noRandom);
                    } elseif ($row['option'] == 'list_live_type_order') {
                        $noRandom = $row['module'] == 'edge_general_settings';
                        $optionsArray = LiveStreaming::getTypeOrderList($noRandom);
                    } elseif (in_array($row['option'], array('list_songs_type_order', 'songs_list_1_type_order', 'songs_list_2_type_order'))) {
                        $noRandom = in_array($row['option'], array('songs_list_1_type_order', 'songs_list_2_type_order'))
                            || $row['module'] == 'edge_general_settings';
                        $optionsArray = Songs::getTypeOrderList($noRandom);
                    } elseif ($row['module'] == 'trial' && $row['option'] == 'type') {

                        $set = Common::getOption('set', 'template_options');
                        $sql_trial = 'SELECT * FROM `payment_plan` WHERE `set` = ' . to_sql($set) . ' AND `type` = ' . to_sql('payment', 'Text') .  ' ORDER BY `item` ASC';
                        $trial_rows = DB::rows($sql_trial);

                        $optionsArray['0'] = "All";

                        foreach ($trial_rows as $key => $trial_row) {
                            $optionsArray[$trial_row['item']] = $trial_row['item_name'];
                        }

                    } else {
                        $optionsValues = explode('|', $row['options']);
                        $optionsArray = array();
                        $prf = '';
                        if ($row['option'] == 'map_on_main_page_urban') {
                            $prf = 'main_page_';
                        }
                        foreach ($optionsValues as $optionValue) {
                            $lOptionValue = $prf . $optionValue;
                            $optionsArray[$optionValue] = lCascade(l($lOptionValue), array('field_' . $row['option'] . '_' . $lOptionValue));
                        }
                      
                    }

                    if($key == 'map_default_mile') {
                        foreach ($optionsArray as $key => $value) {
                            if(!intval($value)) continue;
                            $optionsArray[$key] = $value . " mile";
                        }
                    }


                    if (
                        $row['options'] != 'special_field:background'
                        && $row['options'] != 'special_field:image_main_page'
                    ) {
                        $html->setblockvar('item_background_class', '');
                        $html->setblockvar('item_background_js', '');
                    } else {
                        $html->setvar('item_background_img_current', $row['value']);
                        $html->parse('item_background_class', false);
                        $html->parse('item_background_js', false);
                    }
                    if ($row['options'] != 'special_field:timezone') {
                        $options = h_options($optionsArray, $row['value']);
                    }
                    if ($row['options'] != 'special_field:background_preview') {
                        $html->setblockvar('item_background_preview_js', '');
                    } else {
                        $html->setvar('item_background_img_current', $row['value']);

                        if (Common::isAdminModer()) { //Carousel
                            global $g;

                            $fileTmplUrl = "{$g['path']['url_tmpl']}main/edge/images/" . $row['option'] . "/";
                            $fileUserPart = Common::getOption('url_files', 'path') . 'tmpl/edge';
                            $i = 0;
                            $blockPreview = 'item_background_preview';

                            $html->clean("{$blockPreview}_img");
                            $html->clean("{$blockPreview}_li");

                            foreach ($optionsArray as $file => $name) {
                                $value = $file;
                                if ($file == 'no_image') {
                                    $fileUrl = "{$g['path']['url_tmpl']}main/edge/images/no_image.jpg";
                                } else {
                                    $fileUrl = "{$fileTmplUrl}{$file}";
                                }
                                if (!file_exists($fileUrl)) {
                                    $fileUrl = false;
                                    $fileUser = "{$fileUserPart}_{$row['option']}_{$file}";
                                    if (file_exists($fileUser)) {
                                        $fileUrl = $fileUser;
                                    }
                                }
                                if ($fileUrl) {
                                    $html->setvar("{$blockPreview}_value", $value);
                                    if ($row['value'] == $value) {
                                        $html->parse("{$blockPreview}_img_active", false);
                                        $html->parse("{$blockPreview}_li_active", false);
                                    }
                                    $html->setvar("{$blockPreview}_li", $i);
                                    $html->parse("{$blockPreview}_li", true);

                                    $html->setvar("{$blockPreview}_img", $fileUrl);
                                    $html->parse("{$blockPreview}_img", true);

                                    $html->clean("{$blockPreview}_img_active");
                                    $html->clean("{$blockPreview}_li_active");
                                    $i++;
                                }
                            }
                        }

                        $html->parse('item_background_preview_js', false);
                    }

                    $html->setvar('selectbox_options', $options);
                }

                if ($field == 'section') {
                    $html->setvar('section_name', l('field_' . $key));
                    $html->parse('item_section');
                    if (!isset($first)) {
                        $html->parse('yes_save', false);
                    } else {
                        $html->setblockvar('yes_save', '');
                        unset($first);
                    }
                }

                if ($field == 'label') {
                    $value = l($row['option']);
                    if ($row['option'] == 'upload_settings_php') {
                        $value = lSetVars('upload_settings_php', array('upload_max_filesize' => min(ini_get('upload_max_filesize'), ini_get('post_max_size'))));
                    }
                    $html->setvar('value', $value);
                }

                if ($field == 'radio') {
                    $optionsValues = explode('|', $row['options']);

                    $optionsArray = array();
                    foreach ($optionsValues as $optionValue) {
                        $checked = '';
                        if ($row['value'] == $optionValue) {
                            $checked = 'checked';
                        }
                        $optionsArray[$optionValue] = l($optionValue);
                        $html->setvar('checked', $checked);
                        $html->setvar('value', $optionValue);
                        $html->setvar('label', l('field_' . $key . '_' . $optionValue));
                        $html->parse('item_radio');
                    }
                    //$html->parse('item_separator', true);
                }
                if ($field == 'separator') {
                    //$html->parse('item_separator', true);
                }
                if (
                    $row['options'] == 'special_field:upload_image'
                    && $field == 'file'
                ) {
                    $html->parse('item_upload_image_js', false);
                }

                if ($row['option'] == 'watermark_position') {
                    $html->setvar('block', 'watermark');
                    $html->setvar('rand', rand());
                    $fileWatermark = Common::getOption('url_files', 'path') . 'watermark.png';
                    if (file_exists($fileWatermark)) {
                        $watermarkSize = getimagesize($fileWatermark);
                        if ($watermarkSize) {
                            $html->setvar('watermark_width', $watermarkSize[0]);
                            $html->setvar('watermark_height', $watermarkSize[1]);
                        }
                    } else {
                        $fileWatermark = Common::getOption('url_tmpl', 'path') . 'common/images/1px.png';
                        $html->setvar('watermark_width', '');
                        $html->setvar('watermark_height', '');
                    }

                    $html->setvar('watermark_img', $fileWatermark);
                    $html->parse('watermark_image', false);
                    $html->parse('delete', false);

                    $html->parse('watermark_params', false);
                } else {
                    $html->clean('watermark_params');
                }



                if (($field != 'radio') && ($field != 'separator')) {
                    if ($field == 'number') {
                        $maxNamber = intval($row['options']);
                        if (!$maxNamber) {
                            $maxNamber = 100;
                        }

                        if($key == 'map_user_field_number'){
                            $maxNamber = 4;
                        } 
                        $html->setvar('item_number_max', $maxNamber);

                    }
                    if ($field == 'text' && $this->module == 'trial') {
                        $html->setvar('item_' . $field . '_max_length', 100);
                        $html->parse('item_' . $field . '_max_length', false);
                    }

                    if ($field == 'color') {
                        $html->subcond(strpos($row['options'], 'allow_empty') !== false, 'item_' . $field . '_allow_empty');
                    }

                    $html->parse('item_' . $field, false);
                }

                if (
                    $field != 'separator' && $field != 'checkbox' && $field != 'radio'
                    && $field != 'section' && $field != 'color' && $field != 'label' && $field != 'time'
                ) {
                    $html->parse('item_title', false);
                } else {
                    $html->setblockvar('item_title', '');
                }

                $parseBr = array('home_page_mode'); //, 'map_on_main_page_urban'
                if (in_array($key, $parseBr)) {
                    $html->parse('item_br', true);
                } else {
                    $html->setblockvar('item_br', '');
                }
                if (
                    $key == 'background_only_not_logged_oryx'
                    || $key == 'restore_upload_image_main_page'
                    || $key == 'url_big_banner_main_page'
                    || $key == 'url_small_banner_main_page'
                    || $key == 'restore_upload_image_top_five_button'
                ) {
                    $html->parse('item_separator', true);
                } else {
                    $html->setblockvar('item_separator', '');
                }

                if ($row['option'] == 'color_scheme_impact') {
                    if (Common::isOptionActive('color_scheme_options_hide_impact')) {
                        $colorSchemeOptionsStartClass = 'color_scheme_options_edit';
                        $colorSchemeOptionsHide = 'hide';
                        $colorSchemeOptionsEditClass = '';
                        $colorSchemeOptionsHideClass = 'hide';
                    } else {
                        $colorSchemeOptionsStartClass = 'color_scheme_options_hide';
                        $colorSchemeOptionsHide = '';
                        $colorSchemeOptionsEditClass = 'hide';
                        $colorSchemeOptionsHideClass = '';
                    }
                    $html->setvar('color_scheme_options_start_class', $colorSchemeOptionsStartClass);
                    $html->setvar('edit_color_scheme_options_class', $colorSchemeOptionsEditClass);
                    $html->setvar('hide_color_scheme_options_class', $colorSchemeOptionsHideClass);

                    $html->setvar('item_color_scheme_options_start_class', $colorSchemeOptionsHide);
                    $html->parse('item_color_scheme_options_start');
                } else {
                    $html->clean('item_color_scheme_options_start');
                }

                if ($row['option'] == 'color_scheme_mobile_button_message_reply_rate_background_color_impact') {
                    $html->parse('item_color_scheme_options_end');
                } else {
                    $html->clean('item_color_scheme_options_end');
                }



                $groupOptionsEnd = array(
                    'main_page_header_background_color',
                    'color_scheme_background_color_impact',
                    'color_scheme_mobile_main_page_background_color_impact'
                );
                if (in_array($row['option'], $groupOptionsEnd) && !$this->isOptionsTemplate()) {
                    $html->parse('item_group_options_end');
                } else {
                    $html->clean('item_group_options_end');
                }

                $html->parse('item');
                $html->setblockvar('item_schema_color_js', '');
                $html->setblockvar('item_' . $field, '');
                $html->setblockvar('item_title', '');
                $html->clean('item_select_multiple_item');
            }

            if($key_s != 'other_options'){
                $html->parse('section_dropdown_end');
            }

            $html->parse('section_dropdown', true);
            $html->clean('section_dropdown_start');
            $html->clean('section_dropdown_end');

            $html->clean('item');
        }

        parent::parseBlock($html);
    }

    public function createColorSchemeSvgFooterFile($options)
    {
        global $g;

        if (isset($g['template_options_mobile']['color_scheme_svg_footer_file_settings']) && isset($options[$g['template_options_mobile']['color_scheme_svg_footer_file_settings']['config_option']])) {
            $color = $options[$g['template_options_mobile']['color_scheme_svg_footer_file_settings']['config_option']];
            if ($color != Common::getOption($g['template_options_mobile']['color_scheme_svg_footer_file_settings']['config_option'])) {
                $svgSource = file_get_contents($g['tmpl']['dir_tmpl_mobile'] . $g['template_options_mobile']['color_scheme_svg_footer_file_settings']['source_file']);
                $svg = Common::replaceByVars($svgSource, array('color' => $color));
                $svgFile = $g['path']['dir_files'] . 'tmpl/' . $g['template_options_mobile']['name'] . $g['template_options_mobile']['color_scheme_svg_footer_file_settings']['svg_file_name'] . ltrim($color, '#') . '.svg';
                @file_put_contents($svgFile, $svg);
                @chmod($svgFile, 0777);
            }
        }
    }

}


class CAdminLangs extends CHtmlBlock
{

    function parseBlock(&$html)
    {
        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        adminParseLangsModule($html, $languageCurrent);

        parent::parseBlock($html);
    }

}

class CAdminParnerPage extends CHtmlBlock
{

    var $table = 'terms';

    function setTable($table)
    {
        $this->table = $table;
    }

    function getTable()
    {
        return $this->table;
    }

    function lang()
    {
        return Common::langParamValue('lang', 'partner');
    }

    function action()
    {
        global $p;

        $cmd = get_param('cmd', '');
        $table = $this->getTable();

        $name = get_param('name', '');
        $text = get_param('text', '');
        $lang = $this->lang();
        $id = get_param('id', '');

        if ($cmd == "delete") {
            $sql = 'DELETE FROM partner_' . $table . '
                WHERE id = ' . to_sql($id);
            DB::execute($sql);
        } elseif ($cmd == "edit") {
            DB::execute("
				UPDATE partner_" . $table . "
				SET
				name=" . to_sql(get_param("name", ""), "Text") . ",
				text=" . to_sql(get_param("text", ""), "Text") . "
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");

            redirect("$p?lang=$lang&action=saved");
        } elseif ($cmd == "add") {
            DB::execute("
				INSERT INTO partner_$table (name, text, lang)
				VALUES(
				" . to_sql($name) . ",
				" . to_sql($text) . ",
				" . to_sql($lang) . ")
			");

            redirect("$p?lang=$lang");
        }
    }

    function parseBlock(&$html)
    {
        $table = $this->getTable();
        $html->setvar('table', $table);

        $lang = $this->lang();

        $html->setvar('lang', $lang);

        $html->setvar('partner_' . $table . '_active', ' class="active"');

        $html->setvar('langs', adminLangsSelect('partner', $lang));

        DB::query("SELECT * FROM partner_$table WHERE lang = " . to_sql($lang) . " ORDER BY id");
        while ($row = DB::fetch_row()) {
            foreach ($row as $k => $v) {
                $html->setvar($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
            }

            $html->parse("question", true);
        }

        $html->parse("add", true);

        parent::parseBlock($html);
    }

}


function adminLangsSelect($part, $lang)
{
    $langs = Common::listLangs($part);
    $langs = h_options($langs, $lang);

    return $langs;
}

function adminParseLangsModule(&$html, $languageCurrent, $part = 'main', $langs = false)
{
    if ($langs === false) {
        $langs = Common::listLangs($part);
    } else {
        $langs = Common::setFirstCurrentLanguage($langs, $part);
    }
    if ($langs) {
        foreach ($langs as $file => $title) {
            $html->setvar('language_value', $file);
            $html->setvar('language_title', $title);
            if ($file == $languageCurrent) {
                $html->parse('language_active', false);
                $html->setblockvar('language_link', '');
            } else {
                $html->parse('language_link', false);
                $html->setblockvar('language_active', '');
            }
            $html->parse('language');
        }
    }
}

function unsetDisabledStats($columns)
{
    $optionSet = Common::getOption('set', 'template_options');
    $optionName = Common::getOption('name', 'template_options');
    $columns = array_flip($columns);
    if ($optionSet == 'urban') {
        unset($columns['gifts_sent']); // rade 2023-09-20 add
    } else {
        unset($columns['gifts_sent']);
    }
    $columns = array_flip($columns);
    return $columns;
}

class CAdminOptions extends CHtmlBlock
{

    private $logo;
    private $dirTmpl;
    private $urlTmpl;
    private $patchDir;
    private $patchSave;
    private $dirSave;
    private $fileNameSave;
    private $particle;
    private $methodExistsLogo;
    private $typeImage;
    private $allowTypeImage;
    private $keyOption;
    private $transformation;
    private $newWidth;
    private $newHeight;
    private $isSetBlock = true;
    private $isCustomLogo = true;
    private $titleBlock = array();
    private $lang = false;
    private $block = array(
        'favicon' => 1,
        'icon_pwa' => 1,
        'logo' => 1,
        'logo_footer' => 0,
        'logo_inner' => 0,
        'watermark' => 0,
        'logo_mobile' => 1,
        'logo_mobile_inner' => 0,
        'logo_affiliates' => 1,
        'logo_admin' => 1,
        'logo_admin_inner' => 0,
        'logo_mail' => 1
    );

    private $logoPart = array(
        'favicon' => '',
        'icon_pwa' => '',
        'logo' => 'main',
        'logo_footer' => 'main',
        'logo_inner' => 'main',
        'watermark' => 'main',
        'logo_mobile' => 'mobile',
        'logo_mobile_inner' => 'mobile',
        'logo_affiliates' => 'partner',
        'logo_admin' => 'administration',
        'logo_admin_inner' => 'administration',
        'logo_mail' => 'mail'
    );

    function setBlock($block)
    {
        $this->isSetBlock = false;
        $this->block = $block;
    }

    function setParts($parts)
    {
        $this->isCustomLogo = false;
        $this->logoPart = $parts;
    }

    function setTitleBlock($block)
    {
        $this->titleBlock = $block;
    }

    function setLang($lang)
    {
        $this->lang = $lang;
    }

    function setParams($block = 'logo')
    {
        global $g;

        $this->patchDir = array('images', 'img');
        $this->typeImage = array('png', 'gif');
        $this->allowTypeImage = array('png', 'gif', 'jpg', 'jpeg');
        $this->isSvg = false;
        $this->patchSave = '';
        $this->dirSave = Common::getOption('url_files', 'path');
        $this->transformation = 'logo';
        $this->keyOption = 'path';
        $this->particle = '';
        $this->fileNameSave = '';
        $this->methodExistsLogo = 'existsLogoMain';
        $this->part = '';
        $this->partCustom = '';
        $sfx = ($g['multisite'] != '') ? '_joomph' : '';
        $templateFilesFolderType = Common::templateFilesFolderType(Common::getOption('name', 'template_options'));

        if (in_array($block, array('logo', 'logo_footer', 'logo_inner'))) {
            $this->part = 'main';
        } elseif (in_array($block, array('logo_mobile', 'logo_mobile_inner'))) {
            $this->part = 'mobile';
        }

        $isModern = Common::isAdminModer();

        if ($this->part) {
            $templateOptions = loadTemplateSettings($this->part, Common::getOption($this->part, 'tmpl'));
            //$this->isSvg = $this->prepareAllowTypeImage($block, $templateOptions);
        }
        /* For all svg */
        $this->allowTypeImage[] = 'svg';
        $this->typeImage[] = 'svg';
        $this->isSvg = true;
        /* For all svg */

        switch ($block) {
            case 'favicon':
                $this->logo = basename(Common::getfaviconFilename(), '.ico');
                $this->urlUploaded = Common::getOption('url_files', 'path') . Common::faviconName() . '.ico';
                $this->dirTmpl = 'dir_files';
                $this->urlTmpl = 'url_files';
                $this->patchDir = '';
                $this->typeImage = array('ico');
                $this->allowTypeImage = array('ico', 'png', 'gif', 'jpg', 'jpeg');
                $this->transformation = 'favicon';
                $this->methodExistsLogo = '';
                $this->newWidth = 16;
                $this->newHeight = 16;
                $this->patchSave = Common::getOption('dir_files', 'path') . Common::faviconName() . '.ico';
                break;

            case 'logo':
                //$this->part = 'main';
                $this->logo = 'logo' . $templateFilesFolderType . $sfx;
                $this->particle = trim($templateFilesFolderType, '_');
                $this->dirTmpl = 'dir_tmpl_main';
                $this->urlTmpl = 'url_tmpl_main';
                $this->newWidth = Common::getOption('logo_w', 'template_options');
                $this->newHeight = Common::getOption('logo_h', 'template_options');
                break;

            case 'logo_footer':
                //$this->part = 'main';
                $this->logo = 'logo_footer' . $templateFilesFolderType . $sfx;
                $this->dirTmpl = 'dir_tmpl_main';
                $this->urlTmpl = 'url_tmpl_main';
                $this->particle = 'footer' . $templateFilesFolderType;
                $this->newWidth = Common::getOption('logo_footer_w', 'template_options');
                $this->newHeight = Common::getOption('logo_footer_h', 'template_options');
                break;

            case 'logo_inner':
                //$this->part = 'main';
                $this->logo = 'logo_inner' . $templateFilesFolderType . $sfx;
                $this->dirTmpl = 'dir_tmpl_main';
                $this->urlTmpl = 'url_tmpl_main';
                $this->particle = 'inner' . $templateFilesFolderType;
                $this->newWidth = Common::getOption('logo_inner_w', 'template_options');
                $this->newHeight = Common::getOption('logo_inner_h', 'template_options');
                break;

            case 'watermark':
                //$this->part = 'main';
                $this->logo = 'logo_watermark' . $sfx;
                $this->dirTmpl = 'dir_files';
                $this->urlTmpl = 'url_files';
                $this->allowTypeImage = array('ico', 'png', 'gif', 'jpg', 'jpeg');
                $this->particle = 'watermark';
                $this->newWidth = 219;
                $this->newHeight = 50;
                $this->methodExistsLogo = 'watermarkName';
                $this->transformation = 'watermark';
                $this->fileNameSave = 'watermark';
                break;

            case 'logo_mobile':
                //$this->part = 'mobile';
                $this->logo = 'logo' . $sfx;
                $this->dirTmpl = 'dir_tmpl_mobile';
                $this->urlTmpl = 'url_tmpl_mobile';
                $this->newWidth = getOptionToDefaultLoadTemplateSettings('logo_w', $templateOptions, 131);
                $this->newHeight = getOptionToDefaultLoadTemplateSettings('logo_h', $templateOptions, 29);
                break;

            case 'logo_mobile_inner':
                //$this->part = 'mobile';
                $this->logo = 'logo_inner' . $sfx;
                $this->dirTmpl = 'dir_tmpl_mobile';
                $this->urlTmpl = 'url_tmpl_mobile';
                $this->particle = 'inner';
                $this->newWidth = getOptionToDefaultLoadTemplateSettings('logo_inner_w', $templateOptions, 108);
                $this->newHeight = getOptionToDefaultLoadTemplateSettings('logo_inner_h', $templateOptions, 19);
                break;

            case 'logo_affiliates':
                $this->part = 'partner';
                $this->logo = 'logo' . $sfx;
                $this->dirTmpl = 'dir_tmpl_partner';
                $this->urlTmpl = 'url_tmpl_partner';
                $this->patchDir = array('images');
                $this->newWidth = 219;
                $this->newHeight = 50;
                break;

            case 'logo_admin':
                $this->part = 'administration';
                $this->logo = 'logo' . $sfx;
                $this->dirTmpl = 'dir_tmpl_administration';
                $this->urlTmpl = 'url_tmpl_administration';
                $this->patchDir = array('images');
                $this->newWidth = $isModern ? 160 : 202;
                $this->newHeight = $isModern ? 26 : 43;

                break;

            case 'logo_admin_inner':
                $this->part = 'administration';
                $this->logo = 'logo_inner' . $sfx;
                $this->dirTmpl = 'dir_tmpl_administration';
                $this->urlTmpl = 'url_tmpl_administration';
                $this->particle = 'inner';
                $this->patchDir = array('images');
                $this->newWidth = 36;
                $this->newHeight = 20;

                break;

            case 'logo_mail':
                $this->part = 'mail';
                $this->logo = 'logo_auto_mail' . $sfx;
                $this->dirTmpl = 'dir_tmpl_administration';
                $this->urlTmpl = 'url_tmpl_administration';
                $this->patchDir = array('images');
                $this->particle = 'auto_mail';
                $this->newWidth = 254;
                $this->newHeight = 52;
                $this->allowTypeImage = array('png', 'gif', 'jpg', 'jpeg');
                break;

            case mb_strpos($block, 'logo_location_', 0, 'UTF-8') !== false:
                $id = str_replace('logo_location_', '', $block);
                $this->logo = '';
                $this->dirSave = Common::getOption('url_files', 'path') . 'city/';
                $this->dirTmpl = 'dir_files';
                $this->urlTmpl = 'url_files';
                $logoParam = json_decode(Common::getOption('logo_param', '3d_city'), true);
                $this->newWidth = $logoParam[$id]['w'];
                $this->newHeight = $logoParam[$id]['h'];
                $logoParamMobile = json_decode(Common::getOption('logo_param_mobile', '3d_city'), true);
                $this->newWidthMobile = $logoParamMobile[$id]['w'];
                $this->methodExistsLogo = 'existsLogoCity';
                $this->fileNameSave = $block;
                break;

            case 'icon_pwa':
                $this->logo = 'icon_pwa';
                $this->partCustom = 'icon_pwa';
                $this->transformation = 'icon_pwa';
                $this->dirTmpl = 'dir_tmpl_main';
                $this->urlTmpl = 'url_tmpl_main';
                $this->newWidth = 512;
                $this->newHeight = 512;
                break;
        }
    }

    function prepareAllowTypeImage($block, $templateOptions)
    {
        $result = false;
        if (isOptionActiveLoadTemplateSettings("{$block}_svg", $templateOptions)) {
            $this->allowTypeImage[] = 'svg';
            $this->typeImage[] = 'svg';
            $result = true;
        }
        return $result;
    }

    // not used
    // $this->getOption('height', getOptionToDefaultLoadTemplateSettings('logo_inner_h', $templateOptions, 19));
    function getOption($option, $default)
    {
        $value = $default;
        if ($this->isSvg) {
            $valueOption = Common::getOption($this->getSiteLogoNameNotExt() . '_' . $option, 'main');
            if ($valueOption) {
                $value = $valueOption;
            }
        }
        return $value;
    }

    // not used
    // $this->updateOptionToConfig($this->fileNameSave . '_width', $width);
    function updateOptionToConfig($option, $value)
    {
        if (Common::getOption($option, 'main')) {
            Config::update('main', $option, $value);
        } else {
            Config::add('main', $option, $value, 'max', 0);
        }
    }

    function action()
    {
        parent::action();

        if (strpos(get_param('cmd', ''), 'delete') === 0) {
            $cmdDelete = str_replace('delete_', '', get_param('cmd', ''));
            if (isset($this->block[$cmdDelete])) {
                $this->actionLogo($cmdDelete, true);
            }
        } elseif (strpos(get_param('cmd', ''), 'update') === 0) {
            $cmdUpdate = str_replace('update_', '', get_param('cmd', ''));
            if (isset($this->block[$cmdUpdate])) {
                $this->actionLogo($cmdUpdate);
            }
        }
    }

    // not used
    // $mimeTypes = $this->isSvg ? 'image|svg' : 'image';
    function isValidMime($mime, $allowMime)
    {
        return preg_match('/' . $allowMime . '/', $mime);
    }

    static function updateParamLogoCity($block)
    {
        $result = false;
        if (mb_strpos($block, 'logo_location_', 0, 'UTF-8') !== false) {
            $id = str_replace('logo_location_', '', $block);
            $logoParam = json_decode(Common::getOption('logo_param', '3d_city'), true);
            $width = intval(get_param($block . '_width'));
            $height = intval(get_param($block . '_height'));
            $logoParam[$id]['w'] = $width;
            $logoParam[$id]['h'] = $height;
            Config::update('3d_city', 'logo_param', json_encode($logoParam));

            $logoParam = json_decode(Common::getOption('logo_param_mobile', '3d_city'), true);
            $width = floatval(get_param($block . '_mobile_width'));
            if ($width > 100) {
                $width = 100;
            }
            $logoParam[$id]['w'] = $width;
            Config::update('3d_city', 'logo_param_mobile', json_encode($logoParam));
            $result = true;
        }
        return $result;
    }

    function saveParamSizeLogo($fileLogoSetParamSize, $urlLogo = '', $w = 0, $h = 0)
    {
        if ($urlLogo && file_exists($urlLogo)) {
            $infoLogo = @getimagesize($urlLogo);
            if (isset($infoLogo[1])) {
                $w = $infoLogo[0];
                $h = $infoLogo[1];
            }
        }
        if ($w > 0 && $h > 0) {
            $isUpdate = true;
            $logosSizeparams = Common::getOption('logos_size_params', 'image');
            if ($logosSizeparams !== null) {
                $logosSizeparams = json_decode($logosSizeparams, true);
                if (!is_array($logosSizeparams)) {
                    $logosSizeparams = array();
                }
            } else {
                $isUpdate = false;
                $logosSizeparams = array();
            }
            $logosSizeparams[$fileLogoSetParamSize] = array('w' => $w, 'h' => $h);
            if ($isUpdate) {
                Config::update('image', 'logos_size_params', json_encode($logosSizeparams), true);
            } else {
                Config::add('image', 'logos_size_params', json_encode($logosSizeparams), 'max', 0, '', true);
            }
        }
    }

    function deleteParamSizeLogo($fileLogoDelete)
    {
        $fileLogoDelete = explode('/', $fileLogoDelete);
        $fileLogoDelete = end($fileLogoDelete);
        if (!$fileLogoDelete) {
            return;
        }
        $logosSizeparams = Common::getOption('logos_size_params', 'image');
        if ($logosSizeparams !== null) {
            $logosSizeparams = json_decode($logosSizeparams, true);
            if (is_array($logosSizeparams)) {
                if (isset($logosSizeparams[$fileLogoDelete])) {
                    unset($logosSizeparams[$fileLogoDelete]);
                    Config::update('image', 'logos_size_params', json_encode($logosSizeparams), true);
                }
            }
        }
    }

    function actionLogo($block = 'logo', $flagDelete = false)
    {
        global $g;
        if ($flagDelete) {
            $_FILES['logo']['name'] = '1px.png';
            $_FILES['logo']['type'] = 'image/png';
            $_FILES['logo']['tmp_name'] = $g['path']['dir_tmpl'] . 'common/images/1px.png';
            $_FILES['logo']['error'] = 0;
            $_FILES['logo']['size'] = 95;

            if ($block == 'watermark') {
                unlink(Common::getOption('dir_files', 'path') . 'watermark.png');
                $response['status'] = 1;
                $response['url'] = Common::getOption('url_tmpl', 'path') . 'common/images/1px.png';
                die(json_encode($response));
            }

        }
        $filename = $this->neededExistsLogo($block);
        if ($this->patchSave != '') {
            $filename = $this->patchSave;
        }
        $response['status'] = 1;
        $response['url'] = '';

        $fileImageData = false;
        if (!$flagDelete) {
            $fileTemp = $g['path']['dir_files'] . 'temp/admin_upload_' . $block . time();
            $fileImageData = Common::uploadDataImage($fileTemp, 'logo_data');
            if ($fileImageData) {
                $_FILES['logo']['name'] = pathinfo($fileImageData, PATHINFO_BASENAME);
                $_FILES['logo']['tmp_name'] = $fileImageData;
                $_FILES['logo']['error'] = 0;
                $_FILES['logo']['type'] = '';
                $fileImageDataInfo = @getimagesize($fileImageData);
                if (isset($fileImageDataInfo['mime'])) {
                    $_FILES['logo']['type'] = $fileImageDataInfo['mime'];
                }
                $_FILES['logo']['size'] = filesize($fileImageData);
            }
        }

        if (
            $filename
            && isset($_FILES['logo'])
            && $_FILES['logo']['error'] == 0
        ) {
            //$isImage = true;
            //if (!$this->isSvg) {
            //$isImage = Image::isValid($_FILES['logo']['tmp_name']);
            //}
            $ext = mb_strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $this->allowTypeImage)) { // && $isImage
                //if ($this->isSvg) {
                $method = $this->methodExistsLogo;
                $this->methodExistsLogo = '';
                $filenameDelete = $this->neededExistsLogo($block);
                $this->methodExistsLogo = $method;
                $url = $this->dirSave . 'logo/' . $this->fileNameSave . '.svg';
                if ($filenameDelete == $filename || $filenameDelete == $url) {
                    Common::saveFileSize($filenameDelete, false);
                    $this->deleteParamSizeLogo($filenameDelete);
                    @unlink($filenameDelete);
                }
                if ($ext == 'svg') {
                    include("../_include/lib/svg/svglib.php");
                    $width = intval(get_param($block . '_width'));
                    $height = intval(get_param($block . '_height'));
                    $response['width'] = $width;
                    $response['height'] = $height;
                    $svg = SVGDocument::getInstance($_FILES['logo']['tmp_name']);
                    $svgWidth = intval($svg->getWidth());
                    $svgHeight = intval($svg->getHeight());

                    $dW = $svgWidth / $width;
                    $widthNew = round($svgWidth / $dW);
                    $heightNew = round($svgHeight / $dW);
                    if ($heightNew > $height) {
                        $dH = $svgHeight / $height;
                        $heightNew = round($svgHeight / $dH);
                        $widthNew = round($svgWidth / $dH);
                    }
                    if (!$this->updateParamLogoCity($block)) {
                        $svg->setWidth($widthNew);
                        $svg->setHeight($heightNew);
                    }
                    if (!$svg->getAttribute('viewBox')) {
                        $svg->setViewBox(0, 0, $svgWidth, $svgHeight);
                    }
                    $svg->setAttribute('preserveAspectRatio', 'xMidYMid meet');
                    $svg->asXML($url);

                    Common::saveFileSize($url);
                    $response['url'] = $url;
                    $this->saveParamSizeLogo($this->fileNameSave . '.svg', '', $widthNew, $heightNew);
                    die(json_encode($response));
                } else {
                    $isImage = Image::isValid($_FILES['logo']['tmp_name']);
                    if ($isImage) {
                        Common::saveFileSize($filename, false);
                    } else {
                        $response['status'] = 0;
                        $response['msg'] = lSetVars(
                            'error_extension',
                            array('ext' => mb_strtoupper(implode(', ', $this->allowTypeImage), 'UTF-8'))
                        );
                        return $response;
                    }
                }
                //} else {
                //Common::saveFileSize($filename, false);
                //}

                // don't delete file - favicon area will disappear if happened upload error
                //if (file_exists($filename)) unlink($filename);

                $save = true;
                if ($flagDelete || $fileImageData) {
                    $image = new uploadImage($_FILES['logo']['tmp_name']);
                } else {
                    $image = new uploadImage($_FILES['logo']);
                }

                $image->file_safe_name = false;
                $imgSz = getimagesize($_FILES['logo']['tmp_name']);
                $fileLogoSetParamSize = '';
                if ($this->transformation == 'favicon') {
                    if ($imgSz[2] == 17) {
                        unset($image);
                        $save = false;
                        $ico = new IcoThumb($_FILES['logo']['tmp_name']);
                        $ico->Save($filename);
                        unset($ico);
                    } else {
                        $image->image_resize = true;
                        $image->image_ratio_crop = true;
                        $image->image_y = 16;
                        $image->image_x = 16;
                        $image->file_new_name_body = basename($this->patchSave, '.ico');
                        $image->file_new_name_ext = 'ico';
                    }
                    $url = $this->urlUploaded;
                } elseif ($this->transformation == 'logo' || $this->transformation == 'icon_pwa') {
                    $newWidth = intval(get_param($block . '_width'));
                    if (empty($newWidth)) {
                        $newWidth = $this->newWidth;
                    }
                    $newHeight = intval(get_param($block . '_height'));
                    if (empty($newHeight)) {
                        $newHeight = $this->newHeight;
                    }
                    $image->image_convert = 'png';
                    $image->image_resize = true;
                    $image->image_ratio = true;
                    if ($this->transformation == 'icon_pwa') {
                        $image->image_ratio_crop = true;
                    }

                    $image->image_y = ($imgSz[1] > $newHeight) ? $newHeight : $imgSz[1];
                    $image->image_x = ($imgSz[0] > $newWidth) ? $newWidth : $imgSz[0];
                    if ($this->transformation == 'icon_pwa') {
                        $image->image_y = 512;
                        $image->image_x = 512;
                    }
                    $response['width'] = $image->image_x;
                    $response['height'] = $image->image_y;
                    //City rooms logo
                    if ($this->updateParamLogoCity($block)) {
                        $image->image_y = $imgSz[1];
                        $image->image_x = $imgSz[0];
                    }
                    $image->file_new_name_body = $this->fileNameSave;
                    $image->file_new_name_ext = 'png';
                    $url = $this->dirSave . 'logo/' . $this->fileNameSave . '.png';
                    $this->dirSave = $this->dirSave . 'logo/';
                    $fileLogoSetParamSize = $this->fileNameSave . '.png';
                } elseif ($this->transformation == 'watermark') {
                    $newWidth = intval(get_param($block . '_width'));
                    if (empty($newWidth)) {
                        $newWidth = $this->newWidth;
                    }
                    $newHeight = intval(get_param($block . '_height'));
                    if (empty($newHeight)) {
                        $newHeight = $this->newHeight;
                    }
                    $image->image_convert = 'png';
                    $image->image_resize = true;
                    $image->image_ratio = true;
                    $image->image_y = ($imgSz[1] > $newHeight) ? $newHeight : $imgSz[1];
                    $image->image_x = ($imgSz[0] > $newWidth) ? $newWidth : $imgSz[0];
                    $image->file_new_name_body = $this->fileNameSave;
                    $image->file_new_name_ext = 'png';
                    $url = $this->dirSave . $this->fileNameSave . '.png';
                    $this->dirSave = $this->dirSave;
                    $response['width'] = $image->image_x;
                    $response['height'] = $image->image_y;

                }
                $error = '';
                if ($save) {
                    if (!$image->uploaded) {
                        $error = $image->error;
                    }
                    $image->Process($this->dirSave);
                    if (!$image->processed) {
                        $error = $image->error;
                    }
                    unset($image);
                }

                if ($fileImageData) {
                    @unlink($fileImageData);
                }

                if ($error) {
                    $response['status'] = 0;
                    $response['msg'] = $error;
                } else {
                    Common::saveFileSize($filename);
                    if ($fileLogoSetParamSize) {
                        $this->saveParamSizeLogo($fileLogoSetParamSize, $url);
                    }
                    $response['url'] = $url;
                }
            } else {
                $response['status'] = 0;
                $response['msg'] = lSetVars(
                    'error_extension',
                    array('ext' => mb_strtoupper(implode(', ', $this->allowTypeImage), 'UTF-8'))
                );
            }
        } else {
            $response['status'] = 0;
            $response['msg'] = l('file_type_is_incorrect');
        }
        die(json_encode($response));
    }

    function pathLogo($option, $dir, $type, $key = 'tmpl')
    {
        $dir = (!empty($dir)) ? $dir . '/' : '';
        //echo $this->logo . ' 333/ ' . $type . '<br>';
        $path = Common::getOption($option, $key) . $dir . $this->logo . '.' . $type;
        return $path;
    }

    function getSitePart()
    {
        $sitePart = '';
        $sitePartPrepare = explode('_', $this->dirTmpl);

        if (isset($sitePartPrepare[2])) {
            $sitePart = $sitePartPrepare[2];
        }
        return $sitePart;
    }

    function getSiteLogoNameNotExt()
    {
        $particle = '';
        $partPrf = $this->getSitePart();
        $part = $partPrf;

        if ($this->particle != '') {
            $particle = '_' . $this->particle;
        }
        if ($this->partCustom) {
            $partPrf = $this->partCustom;
        }
        $fileName = $partPrf . '_' . Common::getOption($part, 'tmpl') . $particle;

        return $fileName;
    }

    function existsLogo()
    {
        $urlLogo = '';
        if (!is_array($this->patchDir)) {
            $urlLogo = $this->existsLogoFile($this->patchDir, $this->keyOption);
        } else {
            foreach ($this->patchDir as $vPatch) {
                $urlLogo = $this->existsLogoFile($vPatch);
                if ($urlLogo != '')
                    break;
            }
        }
        return $urlLogo;
    }

    function existsLogoMain()
    {
        $tempDir = $this->dirTmpl;
        $tempUrl = $this->urlTmpl;
        $tempLogo = $this->logo;
        $fileName = $this->getSiteLogoNameNotExt();
        $this->dirTmpl = 'dir_files';
        $this->urlTmpl = 'url_files';
        $this->logo = $fileName;
        $urlLogo = $this->existsLogoFile('logo', $this->keyOption);
        if ($urlLogo == '') {
            $this->dirTmpl = $tempDir;
            $this->urlTmpl = $tempUrl;
            $this->logo = $tempLogo;
            $urlLogo = $this->existsLogo();
        }
        $this->patchSave = Common::getOption('url_files', 'path') . 'logo/' . $fileName . '.png';
        $this->fileNameSave = $fileName;
        return $urlLogo;
    }

    function existsLogoCity()
    {
        global $g;

        $path = $g['path']['url_main'] . Common::getOption('url_city', 'path') . 'tmpl/common/logo/';
        $urlLogo = $path . $this->fileNameSave . '.png';
        if (!file_exists($urlLogo)) {
            $urlLogo = $path . 'default.png';
        }
        $exts = array('.png', '.svg');
        foreach ($exts as $ext) {
            $url = Common::getOption('url_files', 'path') . 'city/logo/' . $this->fileNameSave . $ext;
            if (custom_file_exists($url)) {
                $urlLogo = $url;
                break;
            }
        }
        $this->patchSave = Common::getOption('url_files', 'path') . 'city/logo/' . $this->fileNameSave . '.png';

        return $urlLogo;
    }

    function existsLogoFile($vPatch, $key = 'tmpl')
    {
        $urlLogo = '';
        foreach ($this->typeImage as $vType) {
            if (file_exists($this->pathLogo($this->dirTmpl, $vPatch, $vType, $key))) {
                $this->ext = $vType;
                $urlLogo = $this->pathLogo($this->urlTmpl, $vPatch, $vType, $key);
                break;
            }
        }
        return $urlLogo;
    }

    function watermarkName()
    {
        return 'watermark.png';
    }

    function neededExistsLogo($block)
    {
        $this->setParams($block);
        $method = $this->methodExistsLogo;

        if (empty($method) || !is_string($method) || !method_exists('CAdminOptions', $method)) {
            $method = 'existsLogo';
        }
        $urlLogo = $this->$method(); //$block

        return $urlLogo;
    }

    function parseBlockLogo($html, $block = 'logo')
    {
        $urlLogo = $this->neededExistsLogo($block);

        if ($urlLogo) {
            $html->setvar('url_logo', $urlLogo);
            $html->setvar('block', $block);
            if (isset($this->titleBlock[$block])) {
                $html->setvar('title_block', l($this->titleBlock[$block]));
            } else {
                $html->setvar('title_block', l($block, $this->lang));
            }
            if ($block != 'favicon' && $block != 'icon_pwa') {
                $html->setvar('logo_width', $this->newWidth);
                $html->setvar('logo_height', $this->newHeight);
                if ($html->varExists('logo_width_mobile')) {
                    $html->setvar('logo_width_mobile', $this->newWidthMobile);
                }
                $html->parse('logo_block_params', false);
                $html->parse('delete', false);
            } else {
                $html->clean('logo_block_svg');
                $html->clean('logo_block_params');
            }

            $html->parse('logo_block');
        }
    }

    function parseBlockAll(&$html)
    {
        $html->setvar('rand', rand(0, 100000));

        foreach ($this->block as $block => $parse) {
            if ($parse) {
                $this->parseBlockLogo($html, $block);
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;
        global $sitePart;

        if ($this->isCustomLogo) {
            $customLogo = array(
                'logo_inner' => 'Y',
                'logo_footer' => 'Y',
                'logo_mobile' => 'N',
                'logo_mobile_inner' => 'Y',
                'icon_pwa' => 'N'
            );
            $templateOptions = loadTemplateSettings('mobile', $g['tmpl']['mobile']);
            foreach ($customLogo as $type => $status) {
                if ($this->logoPart[$type] == 'main') {
                    $this->block[$type] = Common::isOptionActive($type, 'template_options') && $this->isSetBlock;
                } elseif ($this->logoPart[$type] == 'mobile') {
                    if ($status == 'Y') {
                        $this->block[$type] = isOptionActiveLoadTemplateSettings($type, $templateOptions) && $this->isSetBlock;
                    } else {
                        $this->block[$type] = getOptionLoadTemplateSettings($type, $templateOptions) != 'N' && $this->isSetBlock;
                    }
                }
            }

            if ($sitePart == 'administration' && Common::isAdminModer() && $p == 'options.php') {
                $this->block['logo_admin_inner'] = 1;
            }
        }

        //$this->logoPart[$type]
        $this->parseBlockAll($html);

        parent::parseBlock($html);
    }

}


class CAdminPageMenu extends CHtmlBlock
{

    private $active = '';
    protected $items = array();
    protected $notAvailableItems = array();
    protected $notAvailableItemsTemplate = array();

    function __construct($name = 'menu_page', $path = null)
    {
        $pageActive = Common::page();
        if ($pageActive == 'partner_edit.php') {
            $pageActive = 'partner.php';
        } elseif ($pageActive == 'flashchat_edit.php') {
            $pageActive = 'flashchat_rooms.php';
        } elseif ($pageActive == '3dchat_edit.php' && get_param('action') == 'edit_room') {
            $pageActive = '3dchat_rooms.php';
        } elseif ($pageActive == 'vids_category_edit.php' && get_param_int('category_id')) {
            $pageActive = 'vids_categories.php';
        }
        $this->setActive($pageActive);
        if ($path === null) {
            $path = Common::getOption('tmpl_loaded_dir', 'tmpl') . '_menu_page.html';
        }

        $tmplOptionSet = Common::getOption('set', 'template_options');
        if (!$tmplOptionSet) {
            $tmplOptionSet = 'old';
        }
        if (isset($this->notAvailableItems[$tmplOptionSet]) && !empty($this->notAvailableItems[$tmplOptionSet])) {
            foreach ($this->notAvailableItems[$tmplOptionSet] as $key => $item) {
                unset($this->items[$item]);
            }
        }

        $tmplOptionName = Common::getOption('name', 'template_options');
        if ($tmplOptionName) {
            if (isset($this->notAvailableItemsTemplate[$tmplOptionName]) && !empty($this->notAvailableItemsTemplate[$tmplOptionName])) {
                foreach ($this->notAvailableItemsTemplate[$tmplOptionName] as $key => $item) {
                    unset($this->items[$item]);
                }
            }
        }

        parent::__construct($name, $path);
    }

    function setActive($active)
    {
        $this->active = $active;
    }

    function parseBlock(&$html)
    {
        if (!Common::isValidArray($this->items)) {
            return;
        }

        foreach ($this->items as $itemPage => $itemTitle) {

            $params = '';
            $icon = '';
            if (is_array($itemTitle)) {
                $item = $itemTitle;
                $itemTitle = $itemTitle['title'];
                if (isset($item['params'])) {
                    $params = $item['params'];
                }
                if (isset($item['icon'])) {
                    $icon = $item['icon'];
                }
            }

            $itemClass = '';
            if ($this->active == $itemPage) {
                $itemClass = 'active';
            }
            $itemPage .= $params;

            $html->setvar('item_class', $itemClass);
            $html->setvar('item_icon', $icon);
            $html->setvar('item_page', $itemPage);
            $html->setvar('item_alias', $itemTitle);
            $html->setvar('item_title', l($itemTitle));
            $html->parse('item');
        }

        parent::parseBlock($html);
    }

}

class CAdminPageMenuEvents extends CAdminPageMenu
{
    protected $items = array(
        'events_events.php' => array('title' => 'menu_events', 'icon' => '<i class="fa fa-file-text" aria-hidden="true"></i>'),
        'events_event_comments.php' => array('title' => 'menu_event_comments', 'icon' => '<i class="fa fa-commenting" aria-hidden="true"></i>'),
        'events_categories.php' => array('title' => 'menu_categories', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'events_category_add.php' => array('title' => 'menu_categories_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>')
    );
}

class CAdminPageMenuUsersFields extends CAdminPageMenu
{

    protected $items = array(
        'users_fields.php' => array('title' => 'menu_profile_fields', 'icon' => '<i class="fa fa-list-ul" aria-hidden="true"></i>'),
        'users_fields_add.php' => array('title' => 'menu_table_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
        'users_fields_countries.php' => array('title' => 'menu_countries', 'icon' => '<i class="icon-globe"></i>'),
        'users_fields_states.php' => array('title' => 'menu_states', 'icon' => '<i class="icon-globe"></i>'),
        'users_fields_cities.php' => array('title' => 'menu_cities', 'icon' => '<i class="icon-globe"></i>'),
        'users_fields_interests.php' => array('title' => 'menu_fields_interests', 'icon' => '<i class="fa fa-star" aria-hidden="true"></i>'),
        'users_fields_add_nickname.php' => 'menu_fields_nickname_add',
    );
    protected $notAvailableItems = array('old' => array('users_fields_interests.php'));
    protected $notAvailableItemsTemplate = array(
        'impact' => array('users_fields_interests.php'),
        'edge' => array('users_fields_interests.php')
    );

}

class CAdminPageMenuGifts extends CAdminPageMenu
{

    protected $items = array(
        'gifts.php' => array('title' => 'menu_gifts', 'icon' => '<i class="fa fa-gift" aria-hidden="true"></i>'),
        'gifts_edit.php' => array('title' => 'menu_gifts_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
        'gifts_set.php' => array('title' => 'menu_style_set', 'icon' => '<i class="fa fa-folder" aria-hidden="true"></i>')
    );
}

class CAdminPageMenuStickers extends CAdminPageMenu
{

    protected $items = array(
        'stickers_settings.php' => array('title' => 'menu_stickers_settings', 'icon' => '<i class="ft-settings"></i>'),
        'stickers_scheme.php' => array('title' => 'menu_stickers_scheme', 'icon' => '<i class="fa fa-tasks" aria-hidden="true"></i>'),
        'stickers_collections.php' => array('title' => 'menu_stickers_collections', 'icon' => '<i class="fa fa-th" aria-hidden="true"></i>'),
        'stickers_collections_sticker.php' => array('title' => 'menu_stickers_collections_sticker', 'icon' => '<i class="fa fa-smile-o" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuPartner extends CAdminPageMenu
{

    protected $items = array(
        'partner.php' => array('title' => 'menu_partners', 'icon' => '<i class="fa fa-users" aria-hidden="true"></i>'),
        'partner_baners.php' => array('title' => 'menu_partners_banners', 'icon' => '<i class="fa fa-wpforms" aria-hidden="true"></i>'),
        'contact_partner.php' => array('title' => 'menu_from_partners', 'icon' => '<i class="fa fa-user-circle" aria-hidden="true"></i>'),
        'partner_main.php' => array('title' => 'main_page', 'icon' => '<i class="fa fa-file-text" aria-hidden="true"></i>'),
        'partner_tips.php' => array('title' => 'page_tips', 'icon' => '<i class="fa fa-file-o" aria-hidden="true"></i>'),
        'partner_faq.php' => array('title' => 'page_faq', 'icon' => '<i class="fa fa-file-text-o" aria-hidden="true"></i>'),
        'partner_terms.php' => array('title' => 'page_terms', 'icon' => '<i class="fa fa-file" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuBlogs extends CAdminPageMenu
{

    protected $items = array(
        'blogs_bloggers.php' => array('title' => 'menu_blogs_bloggers', 'icon' => '<i class="fa fa-user" aria-hidden="true"></i>'),
        'blogs_posts.php' => array('title' => 'menu_blogs_posts', 'icon' => '<i class="fa fa-file-text-o" aria-hidden="true"></i>'),
        'blogs_comments.php' => array('title' => 'menu_blogs_comments', 'icon' => '<i class="fa fa-commenting" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuAdv extends CAdminPageMenu
{

    protected $items = array(
        'adv.php' => array('title' => 'menu_adv', 'icon' => '<i class="fa fa-buysellads" aria-hidden="true"></i>')
    );
}

class CAdminPageMenuGroups extends CAdminPageMenu
{

    protected $items = array(
        'groups_social.php' => array('title' => 'menu_groups', 'icon' => '<i class="fa fa-users" aria-hidden="true"></i>'),
        'groups_group_comments.php' => array('title' => 'menu_group_comments', 'icon' => '<i class="fa fa-commenting" aria-hidden="true"></i>'),
        // 'groups_forums.php' => array('title' => 'menu_forums', 'icon' => '<i class="fa fa-clone" aria-hidden="true"></i>'),
        // 'groups_forum_comments.php' => array('title' => 'menu_forum_comments', 'icon' => '<i class="fa fa-commenting-o" aria-hidden="true"></i>'),
        'groups_categories.php' => array('title' => 'menu_group_categories', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'groups_category_add.php' => array('title' => 'menu_group_categories_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuPlace extends CAdminPageMenu
{

    protected $items = array(
        'places_results.php' => array('title' => 'menu_places', 'icon' => '<i class="fa fa-globe" aria-hidden="true"></i>'),
        'places_reviews.php' => array('title' => 'menu_reviews', 'icon' => '<i class="fa fa-file-text-o" aria-hidden="true"></i>'),
        'places_categories.php' => array('title' => 'menu_categories', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'places_category_add.php' => array('title' => 'menu_categories_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuFlashChat extends CAdminPageMenu
{
    protected $items = array(
        'flashchat_rooms.php' => array('title' => 'menu_flashchat_rooms', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'flashchat_edit.php' => array('title' => 'menu_flashchat_rooms_edit', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
        'flashchat_ban.php' => array('title' => 'menu_flashchat_ban', 'icon' => '<i class="fa fa-ban" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenu3DChat extends CAdminPageMenu
{
    protected $items = array(
        '3dchat_rooms.php' => array('title' => 'menu_3dchat_rooms', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        '3dchat_edit.php' => array('title' => 'menu_3dchat_rooms_edit', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>')
    );
}

class CAdminPageMenuGroupsSocial extends CAdminPageMenu
{

    protected $items = array(
        'groups_social.php' => array('title' => 'menu_groups_social_groups', 'icon' => '<i class="fa fa-users" aria-hidden="true"></i>'),
        // 'groups_social_pages.php' => array('title' => 'menu_groups_social_pages', 'icon' => '<i class="fa fa-newspaper-o" aria-hidden="true"></i>'),
        // 'groups_social_photo.php' => array('title' => 'menu_groups_social_photo', 'icon' => '<i class="la la-photo"></i>'),
        // 'groups_social_video.php' => array('title' => 'menu_groups_social_video', 'icon' => '<i class="la la-youtube-play"></i>'),
        // 'groups_social_reports.php' => array('title' => 'menu_groups_social_reports', 'icon' => '<i class="ft-file-text"></i>'),
        // 'groups_social_reports_content.php' => array('title' => 'menu_groups_social_reports_content', 'icon' => '<i class="la la-clone"></i>'),
        // 'groups_social_reports_wall_post.php' => array('title' => 'menu_groups_social_reports_wall_post', 'icon' => '<i class="la la-clone"></i>'),

        'groups_group_comments.php' => array('title' => 'menu_group_comments', 'icon' => '<i class="la la-clone"></i>'),
        'groups_categories.php' => array('title' => 'menu_group_categories', 'icon' => '<i class="la la-clone"></i>'),
        'groups_category_add.php' => array('title' => 'menu_group_categories_add', 'icon' => '<i class="la la-clone"></i>'),

    );
}

class CAdminPageMenuNews extends CAdminPageMenu
{

    protected $items = array(
        'news.php' => array('title' => 'menu_news_edit', 'icon' => '<i class="ft-file-text"></i>'),
        'news_add.php' => array('title' => 'menu_news_add', 'icon' => '<i class="ft-file-plus"></i>'),
        'news_cats.php' => array('title' => 'menu_news_cats', 'icon' => '<i class="ft-list"></i>')
    );

    function parseBlock(&$html)
    {
        global $g, $l;

        $lang = Common::langParamValue();
        if ($lang) {
            foreach ($this->items as $key => $value) {
                $this->items[$key]['params'] = '?lang=' . $lang;
            }
        }

        parent::parseBlock($html);
    }
}

class CAdminPageMenuBanner extends CAdminPageMenu
{

    protected $items = array(
        'banner.php' => array('title' => 'menu_banners', 'icon' => '<i class="fa fa-wpforms" aria-hidden="true"></i>'),
        'banner_add.php' => array('title' => 'menu_add_banner', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuOptions extends CAdminPageMenu
{

    protected $items = array(
        //<!--begin_space_page--><li><a href="{url_main}administration/main_page.php">{l_menu_options_main_page}</a></li><!--end_space_page-->
        'options.php' => array('title' => 'menu_main_options', 'icon' => '<i class="ft-settings"></i>'),
        'site_options.php' => array('title' => 'menu_site_options', 'icon' => '<i class="icon-globe"></i>'),
        'image.php' => array('title' => 'menu_images', 'icon' => '<i class="la la-picture-o"></i>'),
        'menu.php' => array('title' => 'menu_menu', 'icon' => '<i class="la la-list"></i>'),
        'submenu_order.php' => array('title' => 'submenu_order', 'icon' => '<i class="la la-list-alt"></i>'),
        'headermenu_order.php' => array('title' => 'header_menu_order', 'icon' => '<i class="la la-h-square"></i>'),
        'quick_search_order.php' => array('title' => 'quick_search_order', 'icon' => '<i class="ft-search"></i>'),
        'main_col_order.php' => array('title' => 'main_col_order', 'icon' => '<i class="ft-grid"></i>'),
        'profile_tabs_order.php' => array('title' => 'profile_tabs_order', 'icon' => '<i class="icon-user"></i>'),
        'profile_col_narrow.php' => array('title' => 'profile_col_narrow', 'icon' => '<i class="la la-dedent"></i>'),
        'user_menu_order.php' => array('title' => 'user_menu_order', 'icon' => '<i class="la la-list"></i>'),
        'right_col_order.php' => array('title' => 'right_col_order', 'icon' => '<i class="la la-indent"></i>'),
        'visitor_menu_order.php' => array('title' => 'visitor_menu_order', 'icon' => '<i class="la la-list-alt"></i>'),
        'smtp.php' => array('title' => 'menu_smtp', 'icon' => '<i class="ft-sliders"></i>'),
        'cache.php' => array('title' => 'menu_cache', 'icon' => '<i class="ft-trash-2"></i>'),
        'date_options.php' => array('title' => 'menu_date_options', 'icon' => '<i class="la la-calendar"></i>'),
    );

    protected $notAvailableItems = array('urban' => array()); // rade 2023-09-20 delete
    protected $notAvailableItemsTemplate = array(
        'impact' => array('visitor_menu_order.php'),
        'edge' => array(
            'headermenu_order.php',
            'profile_tabs_order.php',
            'visitor_menu_order.php'
        )
    );

    function parseBlock(&$html)
    {
        global $g, $l;

        $optionTmplName = Common::getOption('name', 'template_options');
        if ($optionTmplName == 'impact') {
            $l['all']['profile_col_narrow'] = $l['all']['impact_left_column'];
            $l['profile_col_narrow.php']['title_current'] = $l['all']['impact_left_column'];
        }

        $tmplOptionSet = Common::getOption('set', 'template_options');
        if (!$tmplOptionSet) {
            $tmplOptionSet = 'old';
        }

        if ($tmplOptionSet != 'urban') {
            unset($this->items['headermenu_order.php']);
            unset($this->items['profile_tabs_order.php']);
            unset($this->items['visitor_menu_order.php']);
        }

        if (Common::getOption('main_page_mode') != 'social') {
            unset($this->items['main_col_order.php']);
            unset($this->items['right_col_order.php']);
            unset($this->items['submenu_order.php']);
        } else {
            if (!Common::isOptionActive('main_col_order', 'template_options')) {
                //    unset($this->items['main_col_order.php']); // rade 2023-09-20 delete
            }
            if (!Common::isOptionActive('right_col_order', 'template_options')) {
                //    unset($this->items['right_col_order.php']); // rade 2023-09-20 delete
            }
            if (!Common::isOptionActive('profile_col_narrow', 'template_options')) {
                unset($this->items['profile_col_narrow.php']);
            }

            if (!isOptionActiveLoadTemplateSettings('order_mobile_user_menu', null, 'mobile', $g['tmpl']['mobile'])) {
                unset($this->items['user_menu_order.php']);
            }

        }
        parent::parseBlock($html);
    }

}

class CAdminPageMenuFakes extends CAdminPageMenu
{
    protected $items = array(
        'fakes_reply_mails.php' => array('title' => 'menu_fakes_reply_mails', 'icon' => '<i class="ft-mail"></i>'),
        'fakes_reply_im.php' => array('title' => 'menu_fakes_reply_im', 'icon' => '<i class="fa fa-weixin"></i>'),
        'fakes_reply_winks.php' => array('title' => 'menu_fakes_reply_winks', 'icon' => '<i class="ft-heart"></i>'),
        'fakes_friend_requests.php' => array('title' => 'menu_friend_requests', 'icon' => '<i class="fa fa-user-plus"></i>'),
        'fakes_reply_replier.php' => array('title' => 'fake_profiles_replier', 'icon' => '<i class="fa fa-users"></i>'),
    );

    // rade 2023-09-20 delete start
    protected $notAvailableItems = array('urban' => array());
    // rade 2023-09-20 delete end
    protected $notAvailableItemsTemplate = array(
        'impact' => array('fakes_reply_winks.php'),
        'edge' => array(),
        // rade 2023-09-20 delete
    );

    function parseBlock(&$html)
    {
        if (get_session("replier_auth") == 'Y') {
            unset($this->items['fakes_reply_replier.php']);
        }
        parent::parseBlock($html);
    }
}

class CAdminPageMenuPay extends CAdminPageMenu
{
    protected $items = array(
        'pay.php' => array('title' => 'menu_payment_systems', 'icon' => '<i class="fa fa-credit-card"></i>'),
        'pay_order.php' => array('title' => 'menu_payment_systems_order', 'icon' => '<i class="la la-list"></i>'),
        'pay_plans.php' => array('title' => 'menu_payment_plans', 'icon' => '<i class="la la-list-alt"></i>'),
        'pay_price.php' => array('title' => 'menu_features_price', 'icon' => '<i class="fa fa-dollar"></i>'),
        'pay_features.php' => array('title' => 'menu_features', 'icon' => '<i class="fa fa-shopping-cart"></i>'),
        'pay_cat.php' => array('title' => 'pay_areas', 'icon' => '<i class="la la-file-text-o"></i>'),
        'pay_type.php' => array('title' => 'pay_access', 'icon' => '<i class="fa fa-check"></i>'),
        'pay_trial.php' => array('title' => 'pay_trial', 'icon' => '<i class="fa fa-gift"></i>'),
    );

    protected $notAvailableItems = array(
        'old' => array('pay_price.php', 'pay_features.php'),
        'urban' => array('pay_price.php', 'pay_features.php') // rade 2023-09-20 delete
    );
    protected $notAvailableItemsTemplate = array(); /*'edge' => array('pay_price.php')*/
}

class CAdminPageMenuCustomPages extends CAdminPageMenu
{
    protected $items = array(
        'pages.php' => array('title' => 'menu_pages', 'icon' => '<i class="ft-file-text"></i>'),
        'pages_add.php' => array('title' => 'menu_pages_add', 'icon' => '<i class="ft-file-plus"></i>')
    );
}

//nnsscc-diamond-20200301-start
class CAdminPageMenuCustomPagesClub extends CAdminPageMenu
{
    protected $items = array(
        'pages_nsc.php' => 'menu_pages',
        'pages_add_nsc_sub.php' => 'menu_pages_add',
    );
}
//nnsscc-diamond-20200301-end

class CAdminPageMenuUsers extends CAdminPageMenu
{
    protected $items = array(
        'users_results.php' => array('title' => 'menu_users', 'icon' => '<i class="ft-users"></i>'),
        'users_approval.php?view=activate' => array('title' => 'menu_users_activate', 'icon' => '<i class="ft-user-plus"></i>'),
        'users_approval.php' => array('title' => 'menu_users_approval', 'icon' => '<i class="ft-user-check"></i>'),
        'users_search.php' => array('title' => 'menu_search', 'icon' => '<i class="ft-search"></i>'),
        'users_photo.php' => array('title' => 'menu_photos', 'icon' => '<i class="la la-photo"></i>'),
        'users_video.php' => array('title' => 'menu_videos', 'icon' => '<i class="la la-youtube-play"></i>'),
        'users_text.php' => array('title' => 'menu_texts', 'icon' => '<i class="la la-file-text-o"></i>'),
        'users_filter.php' => array('title' => 'menu_filter', 'icon' => '<i class="la la-filter"></i>'),
    );
}

class CAdminPageAlbum extends CAdminPageMenu
{
    protected $items = array(
        'alb_albums.php' => array('title' => 'menu_right_alb_albums', 'icon' => '<i class="fa fa-picture-o" aria-hidden="true"></i>'),
        'alb_albums_show.php' => array('title' => 'menu_right_alb_images', 'icon' => '<i class="fa fa-file-image-o" aria-hidden="true"></i>'),
        'alb_comments.php' => array('title' => 'menu_right_alb_comments', 'icon' => '<i class="fa fa-commenting" aria-hidden="true"></i>'),
        'alb_users.php' => array('title' => 'menu_right_alb_users', 'icon' => '<i class="fa fa-user" aria-hidden="true"></i>')
    );
}

class CAdminPageAutoMail extends CAdminPageMenu
{

    protected $items = array(
        'automail.php' => array('title' => 'menu_auto_mail', 'icon' => '<i class="ft-mail"></i>'),
        'automail_settings.php' => array('title' => 'menu_auto_mail_settings', 'icon' => '<i class="ft-settings"></i>')
    );

    function parseBlock(&$html)
    {
        global $g, $l;

        $lang = Common::langParamValue();
        if ($lang) {
            $this->items['automail_settings.php']['params'] = '?lang=' . $lang;
        }

        parent::parseBlock($html);
    }
}

class CAdminPageMassMail extends CAdminPageMenu
{
    protected $items = array(
        'massmail.php' => array('title' => 'menu_mass_send', 'icon' => '<i class="ft-mail"></i>'),
        'massmail_edit.php' => array('title' => 'menu_mass_add', 'icon' => '<i class="ft-edit"></i>')
    );
}

class CAdminPageMenuCity extends CAdminPageMenu
{
    protected $items = array(
        'city_options.php' => array('title' => 'menu_city_options', 'icon' => '<i class="ft-settings"></i>'),
        'city_logo.php' => array('title' => 'menu_city_logo', 'icon' => '<i class="fa fa-flag" aria-hidden="true"></i>'),
        'city_rooms.php' => array('title' => 'menu_city_rooms', 'icon' => '<i class="fa fa-th-list"></i>'),
        'city_platform.php' => array('title' => 'menu_city_platform', 'icon' => '<i class="icon-globe"></i>'),
        'city_video.php' => array('title' => 'menu_city_video', 'icon' => '<i class="fa fa-film"></i>'),
        'city_gallery.php' => array('title' => 'menu_city_gallery', 'icon' => '<i class="la la-picture-o"></i>'),
        'city_cache.php' => array('title' => 'menu_city_cache', 'icon' => '<i class="ft-trash-2"></i>'),
        //'city_avatar_face.php' => 'menu_city_avatar_face_default',
    );
}

class CAdminPageMenuForums extends CAdminPageMenu
{
    protected $items = array(
        'forum_forums.php' => array('title' => 'menu_forum_forums', 'icon' => '<i class="fa fa-clone" aria-hidden="true"></i>'),
        'forum_forum_add.php' => array('title' => 'menu_forum_forums_add', 'icon' => '<i class="fa fa-plus-circle" aria-hidden="true"></i>'),
        'forum_topics.php' => array('title' => 'menu_forum_topics', 'icon' => '<i class="fa fa-file" aria-hidden="true"></i>'),
        'forum_messages.php' => array('title' => 'menu_forum_messages', 'icon' => '<i class="fa fa-commenting-o" aria-hidden="true"></i>'),
        'forum_categories.php' => array('title' => 'menu_forum_categories', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'forum_category_add.php' => array('title' => 'menu_forum_categories_add}', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>')
    );
}

class CAdminPageMenuBlock extends CAdminPageMenu
{
    protected $items = array(
        'ipblock.php' => array('title' => 'menu_ipblock', 'icon' => '<i class="ft-cloud-off"></i>'),
        'ban_users.php' => array('title' => 'menu_users_ban_mails', 'icon' => '<i class="la la-ban"></i>'),
        'users_reports.php' => array('title' => 'menu_users_reports', 'icon' => '<i class="la la-user"></i>'),
        'users_reports_content.php' => array('title' => 'menu_content_reports', 'icon' => '<i class="la la-clone"></i>'),
        'users_reports_wall_post.php' => array('title' => 'menu_content_wall_post', 'icon' => '<i class="ft-file-text"></i>'),
    );
    protected $notAvailableItems = array(
        'old' => array('users_reports.php', 'users_reports_content.php', 'users_reports_wall_post.php'),
    );
    protected $notAvailableItemsTemplate = array(
        'impact' => array('users_reports_wall_post.php'),
        'urban' => array('users_reports_wall_post.php'),
    );
}

class CAdminPageMenuVids extends CAdminPageMenu
{
    protected $items = array(
        'vids_videos.php' => array('title' => 'menu_vids_videos', 'icon' => '<i class="fa fa-file-video-o" aria-hidden="true"></i>'),
        'vids_users.php' => array('title' => 'menu_vids_users', 'icon' => '<i class="fa fa-film" aria-hidden="true"></i>'),
        'vids_comments.php' => array('title' => 'menu_vids_comments', 'icon' => '<i class="fa fa-commenting-o" aria-hidden="true"></i>'),
        'vids_categories.php' => array('title' => 'menu_categories', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'vids_category_add.php' => array('title' => 'menu_categories_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
    );

    protected $notAvailableItems = array(
        // rade 2023-09-20 delete start
        // 'urban' => array('vids_categories.php', 'vids_category_add.php'),
        // rade 2023-09-20 delete end
    );
}

class CAdminPageMenuGroupsSocialVids extends CAdminPageMenu
{
    protected $items = array(
        'groups_social_vids_videos.php' => array('title' => 'menu_vids_groups_videos', 'icon' => '<i class="fa fa-users" aria-hidden="true"></i>'),
        'groups_social_vids_groups.php' => array('title' => 'menu_vids_groups', 'icon' => '<i class="fa fa-file-video-o" aria-hidden="true"></i>'),
        'groups_social_vids_comments.php' => array('title' => 'menu_vids_groups_comments', 'icon' => '<i class="fa fa-commenting-o" aria-hidden="true"></i>'),
    );
}

class CAdminPageMenuMusic extends CAdminPageMenu
{
    protected $items = array(
        'music_musicians.php' => array('title' => 'menu_musicians', 'icon' => '<i class="fa fa-user-circle" aria-hidden="true"></i>'),
        'music_musician_comments.php' => array('title' => 'menu_musician_comments', 'icon' => '<i class="fa fa-commenting" aria-hidden="true"></i>'),
        'music_songs.php' => array('title' => 'menu_songs', 'icon' => '<i class="fa fa-music" aria-hidden="true"></i>'),
        'music_song_comments.php' => array('title' => 'menu_song_comments', 'icon' => '<i class="fa fa-commenting-o" aria-hidden="true"></i>'),
        'music_categories.php' => array('title' => 'menu_categories', 'icon' => '<i class="fa fa-th-list" aria-hidden="true"></i>'),
        'music_category_add.php' => array('title' => 'menu_categories_add', 'icon' => '<i class="fa fa-plus-square" aria-hidden="true"></i>'),
    );

    protected $notAvailableItemsTemplate = array(
        // 'edge' => array('music_musicians.php', 'music_musician_comments.php', 'music_song_comments.php', 'music_categories.php', 'music_category_add.php'), // rade 2023-09-20 delete
        'edge' => array(),
        // rade 2023-09-20 add
    );
}

class CAdminPageMenuMusicGroupSocial extends CAdminPageMenu
{
    protected $items = array(
        'groups_social_music_songs.php' => array('title' => 'menu_songs', 'icon' => '<i class="fa fa-music" aria-hidden="true"></i>'),
    );

    protected $notAvailableItemsTemplate = array(
    );
}

class CAdminCacheVersion extends CHtmlBlock
{

    function action()
    {
        global $p;

        $cmd = get_param('cmd');
        if ($cmd == 'update') {
            Config::updateSiteVersion();
            redirect("{$p}?action=saved");
        }
    }
}

class CAdminMenuOrder extends CHtmlBlock
{

    function action()
    {
        global $g;
        $cmd = get_param('cmd');

        if ($cmd == 'update') {
            $module = $this->module;
            $order = get_param('order');
            $status = get_param('status');
            $submenuItems = array();
            foreach ($order as $key => $item) {
                if (isset($status[$item])) {
                    $stat = 1;
                } else {
                    $stat = 0;
                }
                $submenuItems[$item] = $stat;
            }

            if (isset($g[$module]['order_list'])) {
                Config::update($module, 'order_list', serialize($submenuItems), true);
            } else {
                Config::add($module, 'order_list', serialize($submenuItems), 'max', 1, '', true);
            }

            if ($module == 'header_menu') {
                $setHomePageUrban = get_param('set_home_page_urban', '');
                Config::update('options', 'set_home_page_urban', $setHomePageUrban, true);
            }

            global $p;
            redirect($p . "?action=saved");


        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;
        $module = $this->module;
        $submenuList = Menu::getSubmenuItemsList($module);

        $optionTmplName = Common::getOption('name', 'template_options');
        if ($optionTmplName != 'new_age') {
            unset($submenuList['menu_my_account']);
        }

        if ($module == 'header_menu') {
            $checked = Common::getOption('set_home_page_urban');
            $html->setvar('current_checked', $checked);
        }

        if ($optionTmplName == 'impact' && $html->blockExists('custom_style')) {
            $html->parse('custom_style', false);
        }

        $submenuOrder = array();
        $submenuOrderList = array();

        if (isset($g[$module]['order_list'])) {

            $submenuOrder = unserialize($g[$module]['order_list']);
            if (!is_array($submenuOrder)) {
                $submenuOrder = array();
            }
        }

        foreach ($submenuOrder as $k => $v) {
            $isPresent = false;
            foreach ($submenuList as $ks => $vs) {
                if ($ks == $k) {
                    $submenuOrderList[$k] = $v;
                    unset($submenuList[$ks]);
                    $isPresent = true;
                }
            }
        }

        foreach ($submenuList as $k => $v) {
            $submenuOrderList[$k] = 1;
        }

        $lang = loadLanguageAdmin();
        $langsPage = false;
        $pLast = false;
        if (isset($this->langsPage)) {
            $langsPage = $this->langsPage;
        }

        foreach ($submenuOrderList as $k => $v) {
            $html->setvar('menu_value', $v);
            $html->setvar('menu_key', $k);
            if ($langsPage) {
                $pLast = $p;
                $p = $langsPage;
            }
            $html->setvar('menu_title', l($k, $lang));
            if ($pLast !== false) {
                $p = $pLast;
            }
            if ($v == 1) {
                $html->setvar('checked', 'checked');
            } else {
                $html->setvar('checked', '');
            }
            $html->parse('order_item');
        }

        parent::parseBlock($html);
    }
}

function adminFileBackupExists($filename)
{
    $fileInfo = pathinfo($filename);

    if (!file_exists($fileInfo['dirname'])) {
        return false;
    }

    $files = scandir($fileInfo['dirname']);

    if ($files) {
        $backupFilenameMask = $fileInfo['basename'] . '_';
        foreach ($files as $file) {
            if (strpos($file, $backupFilenameMask) !== false) {
                return true;
            }
        }
    }

    return false;
}

function adminFileBackupRestore($filename)
{
    $backupFile = false;

    $fileInfo = pathinfo($filename);

    if (!file_exists($fileInfo['dirname'])) {
        return false;
    }

    $files = scandir($fileInfo['dirname']);
    if ($files) {
        $backupFiles = array();
        $backupFilenameMask = $fileInfo['basename'] . '_';
        foreach ($files as $file) {
            if (strpos($file, $backupFilenameMask) !== false) {
                $backupFiles[] = $file;
            }
        }

        if ($backupFiles) {
            rsort($backupFiles);
            if (count($backupFiles) > 2) {
                // current file in last backup file, need to restore from previous version
                $backupFileIndex = 2;
            } else {
                $backupFileIndex = 0;
            }

            $backupFile = $backupFiles[$backupFileIndex];

            //var_dump_pre($backupFiles);
            //var_dump_pre($backupFile);
        }
    }

    if ($backupFile) {
        $backupFilePath = pathinfo($filename, PATHINFO_DIRNAME) . '/' . $backupFile;
        copy($backupFilePath, $filename);
    }
}

function deleteReport($del)
{
    $debug = false;

    $listUsersReport = explode(',', $del);
    foreach ($listUsersReport as $rid) {
        $where = 'id = ' . to_sql($rid);
        $report = DB::select('users_reports', $where);
        $usersToReport = array();
        if ($report && isset($report[0])) {
            $report = $report[0];

            $idField = 'id';
            $usersReportsField = 'users_reports';
            $idFieldValue = $report['photo_id'];

            if ($report['comment_type'] == 'video') {
                $table = 'vids_comment';
                $usersReportsField = 'users_reports_comment';
                $idFieldValue = $report['comment_id'];
            } elseif ($report['comment_type'] == 'photo') {
                $table = 'photo_comments';
                $usersReportsField = 'users_reports_comment';
                $idFieldValue = $report['comment_id'];
            } elseif ($report['comment_type'] == 'wall') {
                $table = 'wall_comments';
                $usersReportsField = 'users_reports_comment';
                $idFieldValue = $report['comment_id'];
            } elseif ($report['wall_id']) {
                $table = 'wall';
                $idFieldValue = $report['wall_id'];
            } elseif ($report['video']) {
                $table = 'vids_video';
            } elseif ($report['photo_id']) {
                $table = 'photo';
                $idField = 'photo_id';
            }

            $whereContent = "`$idField` = " . to_sql($idFieldValue);

            $usersToReport = DB::field($table, $usersReportsField, $whereContent);
            if ($debug) {
                var_dump_pre($rid);
                var_dump_pre($table);
                var_dump_pre($whereContent);
                var_dump_pre($usersToReport);
            } else {
                DB::delete('users_reports', $where);
            }
        }
        if ($usersToReport && isset($usersToReport[0])) {
            $usersToReport = $usersToReport[0];
            $usersToReport = explode(',', $usersToReport);
            unset_from_array($report['user_from'], $usersToReport);
            if ($debug) {
                var_dump_pre($usersReportsField);
                var_dump_pre($usersToReport);
            } else {
                DB::update($table, array($usersReportsField => implode(',', $usersToReport)), $whereContent);
            }
        }

        if ($debug) {
            die();
        }
    }
}

class CUsersResultsBase
{

    static public function init(&$m_field)
    {
        if (Common::isEdgeLmsMode()) {
            $m_field['lms_user_type'] = array("lms_user_type", null);
        } else {
            $m_field['orientation'] = array("orientation", null);
        }
    }

    static public function parse($html, $m_field, $row)
    {
        if (Common::isEdgeLmsMode()) {
            $userTypeBlock = 'lms_user_type';
            $m_field[$userTypeBlock][1] = DB::result('SELECT title FROM ' . LMS::getTableUserTypes() . ' WHERE id = ' . $row[$userTypeBlock], 0, 2);
            $invalidValue = "Invalid type";
        } else {
            $userTypeBlock = 'orientation';
            $m_field[$userTypeBlock][1] = DB::result("SELECT title FROM const_orientation WHERE id=" . $row[$userTypeBlock] . "", 0, 2);
            $invalidValue = "Invalid orientation";
        }

        if ($m_field[$userTypeBlock][1] == '') {
            $m_field[$userTypeBlock][1] = l($invalidValue);
        }

        $html->setvar($userTypeBlock, $m_field[$userTypeBlock][1]);
        $html->parse($userTypeBlock, false);
    }

}