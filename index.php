<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.docfg

This notice may not be removed from the source code. */


if (!file_exists(dirname(__FILE__) . '/_include/config/db.php')) {
    $redirect = 'Location: _install/install.php';
    header($redirect);
}

include("./_include/core/main_start.php");
//include("./_include/current/blogs/tools.php");
if (get_param('cmd', '') == 'logout') {
    User::logout();
}

if (guid() > 0 and !isset($gc)) {
    Common::toHomePage();
}

// Fix for IIS default main page
$p = 'index.php';

Common::mainPageRedirect();

Common::mainPageSetRandomImage();

class CIndex extends CHeader
{
	var $message = '';

    function action()
    {
        $cmd = get_param('cmd');

        if($cmd == 'login') {

            $loginField = 'name';
            if(Common::isOptionActive('login_by_mail', 'template_options')) {
                $loginField = 'mail';
            }

            $login = get_param('user', '');
            $password = get_param('password', '');

            $sql = 'SELECT user_id FROM user
                WHERE ' . to_sql($loginField, 'Plain') . ' = ' . to_sql($login, 'Text') . '
                    AND (password = ' . to_sql($password, 'Text') . ' OR password = ' . to_sql(md5($password), 'Text') . ')';

            $user = User::getUserByLoginAndPassword($login, $password);
            if (!$user) {
                $this->message = '#js:error:' . l('Wrong password or log in information');
                if(Common::isMobile()) {
                    redirect('index.php?cmd=login_incorrect');
                }
            } else {
                $id = $user['user_id'];
                $password = $user['password'];
            }

            if ($this->message == '') {
                $this->message = '#js:logged:' . Common::getHomePage();
                set_session('user_id', $id);
                set_session('user_id_verify', $id);

                if (get_param('remember', '') != '') {
                    $name = User::getInfoBasic($id, 'name');
                    set_cookie('c_user', $name, -1);
                    set_cookie('c_password', $password, -1);
                } else {
                    set_cookie('c_user', '', -1);
                    set_cookie('c_password', '', -1);
                }

                User::updateLastVisit($id);
                CStatsTools::count('logins', $id);
                //redirect(Common::getHomePage());
            }

        }
    }

    function parseBlockIndexImpact(&$html)
	{
        $isParseBlockJoin = false;
        if (UserFields::isActive('orientation')) {
            $default = DB::result('SELECT `id` FROM `const_orientation` WHERE `default` = 1', 0, 1);
            $options = '';
            if (!$default){
                $options = '<option value="0" selected="selected">' . l('please_choose') . '</option>';
            }
            $options .= DB::db_options('SELECT id, title FROM const_orientation ORDER BY id ASC', $default);
            $html->setvar('options_orientation', $options);
            $html->parse('field_orientation', false);
            $isParseBlockJoin = true;
        }

        if (UserFields::isActiveSexuality()) {
            $default = DB::result('SELECT `id` FROM `var_sexuality` WHERE `default` = 1', 0, 1);
            $options = DB::db_options('SELECT id, title FROM var_sexuality ORDER BY id ASC', $default);
            $html->setvar('options_sexuality', $options);
            $html->parse('field_sexuality', false);
            $isParseBlockJoin = true;
        }

        if ($isParseBlockJoin) {
            $html->parse('title_join', false);
        }
    }

	function parseBlock(&$html)
	{
		global $g;
		global $g_info;
        global $g_user;

		$optionTmplName = Common::getOption('name', 'template_options');

        if ($html->varExists('site_options')) {
            $html->setvar('site_options', Common::getAllowedOptionsJs());
        }

        // AUTOCOMPLETE OFF
        $html->setvar("autocomplete",autocomplete_off());

        // AUTOCOMPLETE OFF
        $html->setvar('header_url_logo', Common::getUrlLogo());
        $html->setvar('header_favicon', Common::getfaviconSiteHtml());

        if(Common::isOptionActive('news')) {
            $html->parse('news_on2');
        }
		$html->setvar('user', htmlspecialchars(strip_tags((get_param("user")))));
		$html->setvar('password', htmlspecialchars(strip_tags((get_param("password")))));

        $order = array();

        if(Common::getTmplSet() == 'old') {
            $order = DB::select('col_order', "`status` = 'Y'", 'position', '', array('status', 'name', 'section'));
        }

        $colOrder = array();
        $colOrderRight = array();
        foreach ($order as $row) {
            $key = str_replace(' ', '_', mb_strtolower($row['name'], 'UTF-8'));
            if ($row['section'] == 'main') {
                $colOrder[$key] = $row['status'];
            } elseif ($row['section'] == 'right') {
                $colOrderRight[$key] = $row['status'];
            }
        }

        $isFeaturedUsers = Common::isOptionActive('featured_users_on_main_page', 'template_options');
		if ($html->blockexists('users')
            && (($g['options']['main_users'] == 'Y' &&  $isFeaturedUsers)
                || (isset($colOrderRight['featured_members']) &&  $colOrderRight['featured_members'] == 'Y' && !$isFeaturedUsers)))
		{
            $tmpl = Common::getOption('name', 'template_options');
            if ($tmpl == 'mixer') {
                $usNumber = $g['options']['main_users_number_mixer'];
            } elseif ($tmpl == 'oryx') {
                $usNumber = $g['options']['main_users_number_oryx'];
            } elseif ($tmpl == 'new_age') {
                $usNumber = $g['options']['main_users_number_new_age'];
            }
			$i = 0;
            $sql = "SELECT u.*, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) AS age
				FROM user AS u
				WHERE u.is_photo = 'Y'
                    AND u.hide_time = 0
				ORDER BY u.user_id DESC
				LIMIT " . to_sql($usNumber, 'Number');
            DB::query($sql);
            $isParse = false;
			while ($row = DB::fetch_row()) {
				$i++;
				if ($i == 4) {
                    $html->parse('newline', false);
                } else {
                    $html->setblockvar('newline', '');
                }

				$row['photo'] = User::getPhotoDefault($row['user_id'], 's', false, $row['gender']);

				foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }
				$html->parse('user', true);
                $isParse = true;
			}
            if ($isParse) {
                $html->parse('users', true);
            }
		}

		if (Common::isOptionActive('main_search') and $html->blockexists("search"))
		{
			$html->setvar("p_age_from_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], get_param("p_age_from", $g['options']['users_age'])));
			$html->setvar("p_age_to_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], get_param("p_age_to", $g['options']['users_age_max'])));
			if (UserFields::isActive('orientation')) {
                $html->setvar("p_orientation_options", DB::db_options("SELECT search AS id, title FROM const_orientation ORDER BY const_orientation.id ASC", 'first'));
                $html->parse('orientation', false);
            }
            $html->parse("search", true);
		}

        $vid = 0;
        $isMainColOrder = Common::isOptionActive('main_col_order', 'template_options');
        if(Common::isOptionActive('videogallery')) {
            if ($html->blockexists("videos") || $html->blockexists("video2")) {
                $checkVidsPaymentOff = true;
                include('./_include/current/vids/start.php');
                $isNewVideosOn = Common::isOptionActive('new_videos_on_main_pag', 'template_options');
                if ($html->blockexists("videos")
                    && ((isset($colOrder['videos']) &&  $colOrder['videos'] == 'Y' && !$isNewVideosOn)
                        || ($isNewVideosOn && Common::isOptionActive('main_new_videos')))) {
                    CVidsTools::$numberTrim = 19;
                    CVidsTools::$hardTrim = true;
                    $numberVideo = Common::getOption('number_video_main', 'template_options');
                    $numberVideo = ($numberVideo == NULL) ? 4 : $numberVideo;
                    $items = CVidsTools::getVideosNew('0,' . $numberVideo);
                    $html->items('video', $items, '', 'is_my');
                    $html->setvar("total_videos", DB::result("SELECT COUNT(*) FROM vids_video"));
                    $html->parse("videos", true);
                }

                if ($html->blockexists('video2')) {
                    $fileSmallBanner = Common::getOption('main', 'tmpl') . '_banner02user.jpg';
                    if (isset($colOrderRight['small_banner']) &&  $colOrderRight['small_banner'] == 'Y') {
                        if (!Common::isOptionActive('restore_upload_small_banner_main_page')
                            && Common::isOptionActive('upload_small_banner_main_page', 'template_options')
                            && isUsersFileExists('tmpl', $fileSmallBanner)) {
                            $html->setvar('small_banner_file', $fileSmallBanner);
                        } else {
                            $html->setvar('small_banner_file', 'banner02.jpg');
                        }
                        $urlBanner = trim(Common::getOption('url_small_banner_main_page'));
                        if (!Common::isOptionActive('restore_upload_small_banner_main_page')
                            && Common::isOptionActive('upload_small_banner_main_page', 'template_options')
                            && $urlBanner != ''){
                            $html->setvar('url_small_banner', $urlBanner);
                        } else {
                            $html->setvar('url_small_banner', Common::getOption('url_main', 'path') . 'vids.php');
                        }
                    }

                    if (isset($colOrderRight['featured_video']) &&  $colOrderRight['featured_video'] == 'Y') {
                        $sql = 'SELECT * FROM vids_video
                                 WHERE private = 0
                                 ORDER BY RAND() LIMIT 1';
                        $vid = DB::result($sql);
                        if($vid) {
                            $html->assign("video2", CVidsTools::getVideoById($vid));
                            $html->parse("video2", true);
                        }
                    }

                }
            }
        }

        if ($html->varExists('url_file_main_page_image')) {
            $fileMainPage = $g['tmpl']['url_tmpl_main'] . 'images/main_page_dating_bg.png';
            $fileMainPageUser = Common::getOption('url_files', 'path')
                               . 'tmpl/' . Common::getOption('main', 'tmpl')
                               . '_main_page_dating_bg_user_'
                               . Common::getOption('image_main_page');
            if (file_exists($fileMainPageUser)) {
                $fileMainPage = $fileMainPageUser;
            }
            $html->setvar('url_file_main_page_image', $fileMainPage);
        }
        if ($html->blockexists('big_banner_on')
            && (isset($colOrder['big_banner']) &&  $colOrder['big_banner'] == 'Y')) {
            $fileBigBanner = Common::getOption('main', 'tmpl') . '_banner01user.jpg';
            if (!Common::isOptionActive('restore_upload_big_banner_main_page')
                && Common::isOptionActive('upload_big_banner_main_page', 'template_options')
                && isUsersFileExists('tmpl', $fileBigBanner)) {
                $html->setvar('big_banner_file', $fileBigBanner);
            } else {
                $html->setvar('big_banner_file', 'banner01.jpg');
            }
            $urlBanner = trim(Common::getOption('url_big_banner_main_page'));
            if (!Common::isOptionActive('restore_upload_big_banner_main_page')
                && Common::isOptionActive('upload_big_banner_main_page', 'template_options')
                && $urlBanner != '') {
                $html->setvar('url_big_banner', Common::getOption('url_big_banner_main_page'));
            } else {
                $html->setvar('url_big_banner', Common::getOption('url_main', 'path') . 'search.php');
            }
        }
		if (Common::isOptionActive('blogs') && $html->blockexists('blogs_module')
            && (isset($colOrder['blog_posts']) &&  $colOrder['blog_posts'] == 'Y')) {
            $checkBlogsPaymentOff = true;
			include_once('./_include/current/blogs/start.php');
            $rows = array();
            CBlogsTools::$factor_strlen = 2;
            $rows = CBlogsTools::getNew('2');
            $blogsParsed = false;
			foreach ($rows as $row)	{
				$html->assign("post", $row);
                $images = explode('|', $row['images']);
                if(count($images))
                {
                	if(CBlogsTools::existsImg($row['id'], $images[0]))
					{
						$image = $g['path']['url_files'] . "blogs/" . $row['id'] . '_' . $images[0] . '_m.jpg';
						$html->setvar("image", $image);
						$html->parse("blog_image", false);
					}
	                else $html->setblockvar("blog_image", "");
                }
				$html->parse("blog_item", true);
                $blogsParsed = true;
			}
            if($blogsParsed) {
                $html->parse('blogs_module', true);
            }
		}

		if (Common::isOptionActive('adv') and $html->blockexists('ad1') and $html->blockexists('ad2'))
		{
            $index = 0;
            $advIndex = 1;

            $advTables = array(
                'jobs',
                'music',
                'myspace',
                'housting',
                'services',
                'film',
                'casting',
                'personals',
                'sale',
                'cars',
                'items',
            );

            $sql = '';
            $union = '';
            foreach($advTables as $advTable) {
                $sql .= $union . '(SELECT "' . $advTable . '" AS tablename, id AS ad_id, subject, user_id, created FROM adv_' . $advTable . ')';
                $union = ' UNION ';
            }
            $sql .= ' ORDER BY created DESC LIMIT 4';
            DB::query($sql);
            while($row = DB::fetch_row()) {
                $index++;
                $sql = 'SELECT name FROM user
                    WHERE user_id = ' . to_sql($row['user_id'], 'Number');
                $row['username'] = DB::result($sql, 0, 2);
                htmlSetVars($html, $row);
                if($index > 2) {
                    $advIndex = 2;
                }
                $html->parse('ad' . $advIndex, true);
            }

            if($index) {
                $html->parse('ads');
            }
		}

		foreach ($g_info as $k => $v) $html->setvar($k, $v);

		$html->setvar("login_message", $this->message);

        foreach($g['options'] as $option => $value) {
            if(Common::isOptionActive($option)) {
                $html->parse('link_' . $option);
            }
        }

        $groups = 0;
        if (Common::getOption('main_page_mode') == 'social'
            && $isMainColOrder) {
            if (Common::isOptionActive('groups') && $html->blockexists('most_popular_groups_on')
                && (isset($colOrder['most_popular_groups']) &&  $colOrder['most_popular_groups'] == 'Y')) {
                require_once("./_include/current/groups/tools.php");
                $sql_base = CGroupsTools::groups_most_popular_sql_base();
                $groups = CGroupsTools::retrieve_from_sql_base($sql_base, 2);
                $group_n = 1;

                if (CGroupsTools::number_of_groups_where_user_is_member($g_user["user_id"]))
                    $html->parse("my_groups");

                foreach ($groups as $group) {
                    $html->setvar('group_id', $group['group_id']);
                    $html->setvar('group_title', strcut(to_html($group['group_title']), 25));
                    $html->setvar('group_title_full', to_html($group['group_title']));

                    $html->setvar('group_n_comments', $group['group_n_comments']);
                    $html->setvar('group_n_posts', $group['group_n_posts']);
                    $html->setvar('group_n_members', $group['group_n_members']);

                    $html->setvar('group_description', strcut(to_html($group['group_description']), 30));

                    $images = CGroupsTools::group_images($group['group_id']);
                    $html->setvar("image_thumbnail", $images["image_thumbnail_b"]);

                    if ($group_n == count($groups))
                        $html->parse("group_last");

                    if (CGroupsTools::is_group_member($group['group_id']) || (!$group['group_private'])) {
                        $html->clean("private_group_alert");
                        $html->subparse("group_link");
                        $html->subparse("group_link_img");
                    } else {
                        $html->clean("group_link");
                        $html->clean("group_link_img");
                        $html->subparse("private_group_alert");
                    }
                    $html->parse("group");
                    ++$group_n;
                }
            }

            if (Common::isOptionActive('blogs') && $html->blockexists('most_popular_bloggers_on')
                && (isset($colOrder['most_popular_bloggers']) &&  $colOrder['most_popular_bloggers'] == 'Y')) {
                $checkBlogsPaymentOff = true;
                include_once('./_include/current/blogs/start.php');
                $popularBloggers = CBlogsTools::getPopularBloggers();
                $html->items('pop', $popularBloggers);
            }

            if (Common::isOptionActive('places') &&  $html->blockexists('top_places_this_week_on')
                && (isset($colOrder['top_places_this_week']) &&  $colOrder['top_places_this_week'] == 'Y')) {
                require_once('./_include/current/places/place_list_top.php');
                require_once('./_include/current/places/tools.php');
                $places_place_list_top = new CPlacesPlaceListTop('places_place_list_top', $g['tmpl']['dir_tmpl_main'] . '_places_place_list_top.html');
                $places_place_list_top->shift = 2;
                $places_place_list_top->title = false;
                $places_html = $places_place_list_top->parse($html, true);
                $html->setvar('places_place_list_top', $places_html);
            }

            if ($html->blockexists('title_and_text_on')
                && (isset($colOrder['title_and_text']) &&  $colOrder['title_and_text'] == 'Y')) {
                $mainTitle = get_param('main_page_title');
                if ($mainTitle != '') {
                    $mainTitle = urldecode($mainTitle);
                } else {
                    $mainTitle = l('config_main_main_title');
                    $mainTitle = ($mainTitle == 'config_main_main_title') ? Common::getOption('main_title', 'main') : $mainTitle;
                }

                $mainText = get_param('main_page_text');
                if ($mainText != '') {
                    $mainText = urldecode($mainText);
                } else {
                    $mainText = l('config_main_main_text');
                    $mainText = ($mainText == 'config_main_main_text') ? Common::getOption('main_text', 'main') : $mainText;
                }
                $html->setvar('main_title', $mainTitle);
                $html->setvar('main_text', $mainText);
            }
            //DB::query("SELECT * FROM `col_order` WHERE `section` =  'main' AND `status` = 'Y' ORDER BY `position`");
            //while ($row = DB::fetch_row())
            foreach ($colOrder as $name_block => $item) {
                //$name_block = str_replace(' ', '_', $row['name']);
                $isParse = true;
                if (($name_block == 'most_popular_groups' && !$groups)
                    ||($name_block == 'most_popular_bloggers' && empty($popularBloggers))) {
                    $isParse = false;
                }
                if ($isParse) {
                    $html->parse($name_block . '_on');
                }
                $html->parse('order');
                $html->setblockvar($name_block . '_on', '');
            }
        }



        if(true) {

            $cityInfo = IP::geoInfoCity();

            $html->setvar('ip_city_id', $cityInfo['city_id']);
            $html->setvar('ip_city_title', l($cityInfo['city_title']));


            $sql = Common::sqlUsersNearCity($cityInfo, 7);
            DB::query($sql);
            $userIndex = 0;
            $userRow = 1;
            $userColumn = 1;
            while($row = DB::fetch_row()) {
                if($userIndex == 4) {
                    $userRow = 2;
                    $userColumn = 1;
                }

                $photo = User::getPhotoDefault($row['user_id'], 's', false, $row['gender']);
                $photoNeeded = ($userIndex < 4) ? User::getPhotoDefault($row['user_id'], 'r', false, $row['gender']) : $photo;

                $blockMap = 'ip_map_user';
                $html->setvar($blockMap . '_row', $userRow);
                $html->setvar($blockMap . '_column', $userColumn);
                $html->setvar($blockMap . '_id', $row['user_id']);
                $html->setvar($blockMap . '_name', $row['name']);
                $html->setvar($blockMap . '_photo', $photo);
                $html->setvar($blockMap . '_photo_needed', $photoNeeded);
                $html->parse($blockMap);
                $userIndex++;
                $userColumn++;
            }

            $html->setvar('ip_map_lat', $cityInfo['lat'] / IP::MULTIPLICATOR);
            $html->setvar('ip_map_long',  $cityInfo['long'] / IP::MULTIPLICATOR);
            CHeader::showMap($html);

            // Urban

            if (Common::getOptionSetTmpl() == 'urban'){
                $html->setvar('login_form_position_right', intval(Common::getOption('login_form_position_right')));
                $html->setvar('login_form_position_bottom', intval(Common::getOption('login_form_position_bottom')));
                $html->setvar('info_block_position_left', intval(Common::getOption('info_block_position_left')));
                $html->setvar('info_block_position_bottom', intval(Common::getOption('info_block_position_bottom')));
                $html->parse('main_page_frm_login_position');
            }

            if (Common::isOptionActive('main_page_frm_login_shadow_urban')) {
                $html->parse('main_page_frm_login_shadow');
            }

            if(Common::isOptionActive('information_block_on_main_page_urban', 'template_options')) {
                if (Common::isOptionActive('information_block_on_main_page_urban')) {
                    if (Common::getOption('default_title_with_location_urban') == 'location') {
                        $html->parse('main_page_location_image');
                        $html->parse('main_page_location');
                    } else {
                        $title = l(Common::getOption('main_text_title_urban'));
                        $html->setvar('main_page_title_location_image', $title);
                        $html->parse('main_page_title_location_image');
                        $html->setvar('main_page_title_location', $title);
                        $html->parse('main_page_title_location');
                    }
                    if (in_array(Common::getOption('map_on_main_page_urban'), array('map', 'animated'))) {
                        if (Common::isOptionActive('arrow_on_main_page')) {
                            $html->parse('main_page_arrow');
                        }
                        $html->parse('ip_city');
                    } else {
                        if (Common::isOptionActive('arrow_on_main_page')) {
                            //$html->parse('main_page_arrow_image');
                            $html->parse('main_page_arrow');
                        }
                        $html->parse('ip_city_image');
                    }
                }
            } else {
                $html->parse('ip_city');
            }

            if(Common::isOptionActive('map_on_main_page_urban', 'template_options')) {
                $typeMainPageBg = Common::getOption('map_on_main_page_urban');
                $blockMainPageImage = 'main_page_image';
                if ($typeMainPageBg == 'map' || $typeMainPageBg == 'animated') {
                    $blockMap = 'ip_map';
                    if ($typeMainPageBg == 'map') {
                        $html->parse("{$blockMap}_google_map", false);
                    } else {
                        $classBgAnimate = 'google_map_shadow_transparent';
                        $typeAnimated = Common::getOption('main_page_urban_animated');
                        if ($typeAnimated == 'interests_chart_d3') {
                            $classBgAnimate = 'map_interests_chart';
                        }
                        $html->setvar("{$blockMap}_bg_transparent", $classBgAnimate);
                    }
                    $html->parse($blockMap);
                } else {
                    $codeVideo = Common::getOption('main_page_urban_video_code');
                    if ($typeMainPageBg == 'video' && $codeVideo) {
                        $codeVideo = json_decode($codeVideo, true);
                        $height = (100/$codeVideo['ratio']-4) . 'vw';
                    } else {
                        $height = Common::getOption('image_main_page_height_urban') . 'px';
                    }
                    $html->setvar("{$blockMainPageImage}_height", $height);
                    $html->parse($blockMainPageImage);
                }
            } else {
               $html->parse('ip_map');
            }
            // Urban

        }

        if(IS_DEMO) {
            $html->setvar('login_user', demoLogin());
            $html->setvar('login_password', '1234567');
            $html->parse('demo');
        }

        if ((isset($colOrderRight['login_form']) &&  $colOrderRight['login_form'] == 'Y') || $html->blockExists('social_login')) {
            Social::parse($html);
            $html->parse('loginform_social', false);
        }
        if (Common::getOption('main_page_mode') == 'social'
            && Common::isOptionActive('right_col_order', 'template_options')) {
            //DB::query("SELECT * FROM col_order WHERE section =  'right' AND status = 'Y' ORDER BY position");
            //while ($row = DB::fetch_row()) {
            foreach ($colOrderRight as $name_block => $item) {
                //$name_block = str_replace(' ', '_',  mb_strtolower($row['name'], 'UTF-8'));
                $isParse = true;
                if ($name_block == 'featured_video' && !$vid) {
                    $isParse = false;
                }
                if ($isParse) {
                    $html->parse($name_block . '_right');
                }
                if ($name_block == 'small_banner' || $name_block == 'featured_video') {
                    if (Common::isOptionActive('videogallery')) {
                        $html->parse('video2');
                    }
                }
                $html->parse('right_order');
                $html->setblockvar('video2', '');
                $html->setblockvar($name_block . '_right', '');
            }
        }

        $html->parse('loginform_dating', false);
        $html->setvar('main_page_mode', $g['options']['main_page_mode']);
        $html->parse('main_' . $g['options']['main_page_mode']);

        // Urban
        $block = 'user_profile_delete_alert';
        if($html->blockexists($block) && get_session($block)) {
            $html->parse('user_profile_delete_alert');
            delses($block);
        }
        // Urban

		$parseTemplateMethod = 'parseBlockIndex' . $optionTmplName;
        if (method_exists('CIndex', $parseTemplateMethod)) {
            $this->$parseTemplateMethod($html);
        }

        if (Common::isOptionActiveTemplate('include_template_class')) {
            $classTemplate = 'Template' . $optionTmplName;
            if (class_exists($classTemplate, true) && method_exists($classTemplate, 'indexParseBlock')) {
                $classTemplate::indexParseBlock($html);
            }
        }

        parent::parseBlock($html);
	}
}

$ajax = get_param('ajax');
if($ajax) {
    $index = new CIndex('', '', '', '', true);
    $index->action(false);
    echo $index->message;
    die();
}

$page = new CIndex("", getPageCustomTemplate('index.html', 'index_page_template'));

$tmplList = array('main' => $g['tmpl']['dir_tmpl_main'] . "_header.html");
if (Common::isOptionTemplateSet('urban') && Common::isParseModule('index_animated')) {
    $tmplList['animated_js'] = $g['tmpl']['dir_tmpl_main'] . "_index_animated_js.html";
    $tmplList['animated_html'] = $g['tmpl']['dir_tmpl_main'] . "_index_animated.html";
}
$header = new CHeader("header", $tmplList);

$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);
if($page->m_html->blockexists('join')) {
    $register = new CJoinForm("join", null);
    $page->add($register);
}

include("./_include/core/main_close.php");