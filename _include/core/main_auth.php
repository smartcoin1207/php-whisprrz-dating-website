<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

User::autologinByParam();

User::loginByCookies();

if(((Common::isOptionActive('mobile_redirect')) || (Common::isOptionActive('mobile_site_on_tablet'))) && Common::isOptionActive('mobile_enabled')) {
    Common::mobileRedirect();
}
$cmd = get_param('cmd');

$g['sql']['your_orientation'] = '';

$g_user = array();
$g_info = array();
if (isset($area) and $area == "test") $access = "Y";
if (get_session("user_id") != get_session("user_id_verify")) set_session("user_id", get_session("user_id_verify"));
if (get_session('user_id') != '') {
    $g_user = User::getInfoBasic(get_session('user_id'));

    //Fix search People Nearby
    if (isset($g_user['geo_position_lat']) && (!$g_user['geo_position_lat'] || !$g_user['geo_position_long'])) {
        User::updateGeoPosition($g_user['city_id']);
        $g_user = User::getInfoBasic($g_user['user_id'], false, 0, false);
    }
}

if (get_param('upload_page_content_ajax') && get_param('cmd') == 'logout') {
    User::logoutWoRedirect();
    $_GET['cmd'] = '';
    if(guid()) {
        $g_user['user_id'] = 0;
    }
}

if (Common::isOptionActive('header_color_admin', 'template_options')
    && get_session('set_color') != 'N') {
    $color = User::getColorScheme();
    if (get_session('color_upper') != $color['upper']) {
        set_session('color_upper', $color['upper']);
    }
    if (get_session('color_lower') != $color['lower']) {
        set_session('color_lower', $color['lower']);
    }
    if (get_session('set_color') == 'Y') {
        set_session('set_color', 'N');
    }
}
if (Common::isOptionActive('website_background', 'template_options')
    && get_session('set_bg') != 'N') {
    $background = Common::getOption('website_background_oryx');
    if (Common::isOptionActive('background_only_not_logged_oryx') && guid()) {
        set_session('bg_image', $background);
        set_session('bg_image_changed', '');
    } elseif (get_session('bg_image') != $background) {
        set_session('bg_image', $background);
    }
    if (get_session('set_bg') == 'Y') {
        set_session('set_bg', 'N');
    }
}
if (isset($g_user['user_id']) and $g_user['user_id'] > 0) {

    City::logout();
    if (get_cookie('general_chat_logout', true) == 'logout') {
        if ($p != 'general_chat.php') {
            Flashchat::logout();
        } else {
            set_cookie('general_chat_logout', '', -1, true, false);
        }
    }
    User::isAccountBan();

    $demoLogin = get_param('demo_login', 0);
    if ($demoLogin == 1 && IS_DEMO && $p == 'join.php') {
        User::logout();
    }
    if(guser('color_scheme') != get_cookie('user_color_scheme')) {
        set_cookie('user_color_scheme', guser('color_scheme'));
    }
    $accepted = (Common::isMobile()) ? 1 : 0;
    User::inviteBySession($accepted);

    $responseAjax = get_param('ajax');
    if (isset($area) && $area == "public" && !$responseAjax && $p != 'ajax.php') {//forbidden ajax.php
        $redirect = urldecode(get_param('demo_redirect'));
        if($redirect) {
            if($redirect == '_server/city_js/index.php') {
                if(Common::isMobile(false)) {
                    $redirect = urlencode('_server/city_js/index.php?view=mobile');
                }
            }
            redirect($redirect);
        }
        redirect(Common::getHomePage());
    }

    $optionTemplateSet = Common::getOption('set', 'template_options');
    $optionTemplateName = Common::getOptionTemplate('name');
    $isEdge = $optionTemplateName == 'edge';

    if ($optionTemplateSet != 'urban'
        && (!in_array($p, $g['options']['guest_pages'])
            && $g_user['ban_mails']
            && Common::getOption('auto_ban_messages')
            && (get_param('cmd', '') != 'logout'))) {
        redirect($g['to_root'] . 'ban_mails.php');
    }


    $pagesAllowed = array(
            'tmpl_img_loader.php',
            'email_not_confirmed.php',
            'confirm_email.php',
            'ajax.php',
            'server.php',
            'update_server_ajax.php',
            'update_server_ajax_impact.php',
            'update_server_ajax_edge.php',
            'contact.php',
            'index.php',
            'before.php',
            'after.php',
            'css.php',
            'js.php',
            'manifest.php',
            'profile_settings.php',
    );

	if (!in_array($p, $pagesAllowed)//forbidden ajax.php
        && $g_user['active_code']
        && (time() - strtotime($g_user['register']) >= intval($g['options']['join_unconfirmed_email_max_days']) * 24 * 60 * 60)
        && (get_param('cmd', '') != 'logout'))
            redirect("email_not_confirmed.php");
    if (Common::isOptionActive('access_paying')
        && !User::isPaid(guid())
        && (!in_array($p, $pagesAllowed))//forbidden ajax.php
        && (!in_array($p, array('upgrade.php', 'increase_popularity.php')))
    ) {
        redirect("upgrade.php");
    }

    if ($optionTemplateSet == 'urban') {//пока для урбана
        if (!$isEdge) {
            if (Common::isMobile()) {
                if ($p == 'profile_view.php' && !get_param('user_id')) {
                    $pagesAllowed[] = 'profile_view.php';
                }
                if ($p == 'upgrade.php' && !get_param('action')) {
                    $pagesAllowed[] = 'upgrade.php';
                }
                $pageRedirect = 'profile_photo.php';
                $pagesAllowed[] = 'profile_photo.php';
            } else {
                $pageRedirect = 'profile_view.php?show=photos';
                $pagesAllowed[] = 'profile_view.php';
                $pagesAllowed[] = 'upgrade.php';
                if ($p == 'search_results.php'
                    && get_param('display') == 'profile'
                    && get_param('uid') == $g_user['user_id']) {
                    $pagesAllowed[] = 'search_results.php';
                }
            }
        } else {
            $pageRedirect = 'photos_list.php';
        }

        $minNumberPhotosToUseSite = Common::getOption('min_number_photos_to_use_site');
        if ($minNumberPhotosToUseSite && $optionTemplateName == 'edge') {
            if (guid() == User::getParamUid(0)) {
                $pagesAllowed[] = 'photos_list.php';
            }
            if ($p == 'upgrade.php') {
                $pagesAllowed[] = 'upgrade.php';
            }
        }

		$urlSiteSubfolder = '';
		if (isset($g['3dcity_load_index']) && $g['3dcity_load_index']) {
		//$urlSite = Common::urlSite() . MOBILE_VERSION_DIR . '/';
		//mb_strpos($urlSite, '_server/city_js', 0, 'UTF-8') !== false ) {
			unset($pagesAllowed[array_search('index.php', $pagesAllowed)]);
			$isMobileCity = get_param('view') == 'mobile';
			$urlSiteSubfolder = Common::urlSiteSubfolders() . ($isMobileCity ? MOBILE_VERSION_DIR . '/' : '');
			$pageRedirect =  $urlSiteSubfolder . $pageRedirect;
		}

        if ($minNumberPhotosToUseSite
        && !in_array($p, $pagesAllowed)) {//forbidden ajax.php
            $keyAlert = User::checkAccessToSiteWithMinNumberUploadPhotos();
            if ($keyAlert) {
                setses('error_accessing_user', $keyAlert);
                setses('error_accessing_user_param', $minNumberPhotosToUseSite);
                $pageRedirectTmpl = Common::getOption('page_redirect_min_photos_to_use_site', 'template_options');
                if ($pageRedirectTmpl) {
                    $pageRedirect = $urlSiteSubfolder . Common::pageUrl($pageRedirectTmpl);
                }
                redirect($pageRedirect);
            }
            /*$numberPhotos = CProfilePhoto::getNumberPhotos();
            if ($minNumberPhotosToUseSite > $numberPhotos['Y']) {
                $errorAlert = 'site_available_after_uploading_photos';
                if ($minNumberPhotosToUseSite <= $numberPhotos['all']) {
                    $errorAlert = 'photos_are_approved_by_the_administrator';
                }
                setses('error_accessing_user', $errorAlert);
                setses('error_accessing_user_param', $minNumberPhotosToUseSite);
                redirect($pageRedirect);
            }*/
        }
    }

	$area = "login";
	#CURRENT_TIMESTAMP now() " . to_sql(date("d-m-Y H:i:s"), "Text") . "
	#foreach ($g_user as $k => $v) if ($v == 0) $g_user[$k] = "0";

    $g_user['free_access'] = User::isFreeAccess();
    User::paidLevel();

    $isMobile = Common::isMobile();
    if ($g_user['isMobile'] != $isMobile) {
        DB::execute("UPDATE  `user` SET `isMobile` = " . to_sql( $isMobile, 'Text') . " WHERE `user_id` =" . to_sql(guid(), 'Number'));
        $g_user['isMobile'] =  $isMobile;
    }
	if ($g['options']['your_orientation'] == "Y") {
        $sqlYourOrientation = User::getPartnerOrientationWhereSql('');
        if($sqlYourOrientation) {
            $g['sql']['your_orientation'] = ' AND ' . $sqlYourOrientation;
        }
    }


    //SLOW QUERY PATCH
    $need = date_parse($g_user['last_visit']);
    $need = mktime($need['hour'], $need['minute'], $need['second'], $need['month'], $need['day'], $need['year']);
    $secsFromVisit = (time() - $need);
    $minsFromVisit = floor($secsFromVisit / 60);
    // prevent offline status if user is on site
    if ($minsFromVisit > ($g['options']['online_time'] - 1) ) {
        User::updateLastVisit($g_user['user_id']);
    }
    //SLOW QUERY PATCH

    if($g_user['lang'] != $g['main']['lang_loaded']) {
        // update language value
        $sql = 'UPDATE user '
            . 'SET lang = ' . to_sql($g['main']['lang_loaded'])
            . 'WHERE user_id = ' . to_sql($g_user['user_id'], 'Number');
        DB::execute($sql);
    }

    if($isMobile && Common::getTmplSet() == 'old') {
        $sql = 'SELECT COUNT(*) FROM mail_msg
            WHERE user_id = ' . to_sql(guid(), 'Number') . '
                AND type != "postcard"
                AND folder = 1
                AND new = "Y"';
        $g_user['new_mails'] = DB::result($sql);
    }

	$g_info['new_mails'] = $g_user['new_mails'];
	$g_info['new_interest'] = $g_user['new_interests'];
	$g_info['users_view_new'] = $g_user['new_views'];
	$g_info['users_view_total'] = $g_user['total_views'];

    if (in_array($p, array('wall.php', 'wall_ajax.php', 'profile_view.php', 'search_results.php'))) {
        $templateWallSectionsOnly = Common::getOptionTemplate('wall_sections_only');
        if (is_array($templateWallSectionsOnly)) {
            Wall::setSiteSectionsOnly($templateWallSectionsOnly);
        } else {
            $sections = array(
                'photo',
                'interests',
            );
            Wall::setSectionsHidden($sections);
        }
    }
} else {
    $isVisitorCity = false;
    if (in_array($cmd, array('login'))) {
        City::logoutVisitorUser();
    } else {
        $isVisitorCity = City::loginVisitorUserShowCity();
    }
	if ((isset($area) && $area == "login") && !$isVisitorCity) {
        Common::toLoginPage();
    }

	$area = 'public';
    set_session('user_id', '');

	$g_info['new_mails'] = 0;
	$g_info['new_interest'] = 0;
	$g_info['users_view_new'] = 0;
	$g_info['users_view_total'] = 0;

	$g_user['country_id'] = 0;
	$g_user['state_id'] = 0;
	$g_user['city_id'] = 0;
	$g_user['gold_days'] = 0;
	$g_user['type'] = 'none';
	if (!$isVisitorCity) {
		$g_user['user_id'] = 0;
	}
}

/*
if($p == 'users_online.php') {
    $onlineCacheTimeout = 0;
} else {
    $onlineCacheTimeout = 3;
}
*/
// Problem with online count after login - member see 0
// but online page shows 3
// $g_info['users_online'] = DB::result_cache('users_online' . guser('default_online_view'), $onlineCacheTimeout, $sql);
if(Common::getTmplSet() == 'old') {
    $defaultOnlineView = User::defaultOnlineView();
    $sql = 'SELECT COUNT(*) FROM user
        WHERE hide_time = 0
            AND user_id != ' . to_sql(guid()) . '
            AND (last_visit > ' . to_sql(date('Y-m-d H:i:00', time() - $g['options']['online_time'] * 60), 'Text') . ' OR use_as_online=1)' . $defaultOnlineView;
    $g_info['users_online'] = DB::result($sql);
} else {
    $g_info['users_online'] = 0;
}

$sql = 'SELECT COUNT(*) FROM user
    WHERE hide_time = 0 ' . $g['sql']['your_orientation'];
$g_info['users_total'] = DB::result_cache('users_total' . to_php_alfabet($g['sql']['your_orientation']), 30, $sql);

$sql = 'SELECT COUNT(*) FROM user
        WHERE hide_time = 0
            AND register >= (NOW() - INTERVAL ' . intval($g['options']['new_time'])  . ' DAY)';
$g_info['users_new'] = DB::result_cache("users_new", 30, $sql);