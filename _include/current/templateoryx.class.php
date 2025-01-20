<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Class Templateoyrx {

    static $listUserGroupUid = 0;
    static $listUserGroupId = 0;
    static $nameUser = '';
    static $isProfileTabs = false;

    static function headerParseBlock(&$html)
    {
        global $g;
        global $p;
        global $g_user;

        $guid = guid();

        $uid = User::getParamUid();
        $html->setvar('header_uid', $uid);
        $uidParam = User::getParamUid(0);
        $html->setvar('header_param_uid', $uidParam);

        $groupId = Groups::getParamId();
        $html->setvar('header_group_id', $groupId);
        $noAccessGroup = 0;
        if ($groupId){
            $isPageGroup = Groups::isPage();
            $html->setvar('header_group_view', $isPageGroup ? 'group_page' : 'group');
            $html->setvar('header_group_is_page', intval($isPageGroup));

            $groupUserId = Groups::getInfoBasic($groupId, 'user_id');
            $html->setvar('header_group_user_id', $groupUserId);

            $noAccessGroup = Groups::isAccessGroup();
            if ($noAccessGroup !== 'no_group' && !$noAccessGroup) {
                $noAccessGroup = 1;
            } else {
                $noAccessGroup = 0;
            }
        }
        $html->setvar('header_group_no_access', $noAccessGroup);
        $html->setvar('header_group_view_list', get_param('view_list'));

        $cmd = get_param('cmd');
        $isMobile = Common::isMobile(false);
        $isAppAndroid = Common::isAppAndroid();
        $typeUsers = 'member';
        $varsHeader = array();
        $varsFooter = array();
        $isHeader = $html->blockExists('header_visitor');
        $isFooter = $html->blockExists('footer_visitor');

        if (IS_DEMO){
            $html->parse('check_demo_iframe', false);
            $html->parse('check_demo_iframe_body', false);
        }

        if ($guid) {
            if ($isHeader) {
                $maxFileSize = Common::getOption('photo_size');
                $maxVideoSize = Common::getOption('video_size');
                $maxSongSize = Common::getOption('music_mp3_file_size_limit_mbs');
                $varsHeader = array(
                    'user_photo_m' => User::getPhotoDefault($guid, 'm'),//Group replace
                    'photo_file_size_limit' => $maxFileSize,
                    'max_photo_file_size_limit' => lSetVars('max_file_size', array('size' => $maxFileSize), 'toJsL'),
                    'song_file_size_limit' => $maxSongSize,
                    'max_song_file_size_limit' => lSetVars('max_file_size', array('size' => $maxSongSize), 'toJsL'),
                    'video_file_size_limit' => $maxVideoSize,
                    'max_video_file_size_limit' => lSetVars('max_file_size', array('size' => $maxVideoSize), 'toJsL'),
                    'auto_play_video' => Common::isOptionActive('video_autoplay')?'autoplay':'',
                    'body_class' => '',
                    'number_comments_frm_show' => Common::getOptionTemplateInt('gallery_number_comments_to_show_bottom_frm'),
                    'gallery_preload_data_number' => Common::getOptionTemplateInt('gallery_preload_data'),
                    'crop_min_width' => intval(trim(Common::getOption('min_photo_width_urban', 'image'))),
                    'crop_min_height' => intval(trim(Common::getOption('min_photo_height_urban', 'image'))),
					'live_price' => Pay::getServicePrice('live_stream', 'credits'),
                );
                if ($p == 'videochat.php' || $p == 'live_streaming.php') {
                    $varsHeader['body_class'] = 'body_video_chat';
                } elseif ($p == 'audiochat.php') {
                    $varsHeader['body_class'] = 'body_audio_chat';
                } elseif ($p == 'email_not_confirmed.php') {
                    $varsHeader['body_class'] = 'email_not_confirmed';
                }

                if (!self::isTemplateColums()) {
                    if (isset($varsHeader['body_class'])) {
                        $varsHeader['body_class'] .= ' body_no_colums';
                    } else {
                        $varsHeader['body_class'] = 'body_no_colums';
                    }
                }

                $galleryImageHeight = Common::getOptionInt('gallery_image_height_mobile', 'edge_gallery_settings');
                if ($galleryImageHeight <= 0 || $galleryImageHeight < 20) {
                    $galleryImageHeight = 20;
                } elseif ($galleryImageHeight > 100) {
                    $galleryImageHeight = 100;
                }
                $varsHeader['gallery_image_height_mobile'] = intval($galleryImageHeight);

                $isMobile = CityBase::isMobile();
                $html->setvar('city_class', $isMobile ? 'city_mobile' : 'city_desktop');

                $counter = CIm::getCountNewMessages();
                $html->setvar('number_messages', $counter);

                if ($guid != $uid) {
                    $html->setvar('is_profile_blocked', User::isEntryBlocked($guid, $uid));
                }

                if ($uid != $guid) {
                    $html->setvar('user_status_online', intval(User::isOnline($uid)));
                    $html->setvar('real_status_online', intval(User::isOnline($uid, null, true)));
            }
            }
            if ($isFooter) {

                $cmd = get_param('cmd');
                $type = get_param('type');
                if ($cmd == 'payment_thank' && !$type && $html->blockexists('payment_thank')) {
                    $html->parse('payment_thank');
                }

                $arrayKeys = array('comment_current', 'comment', 'comments_reply_item');
                $userData = User::getDataUserOrGroup($guid, $groupId, $g_user);
                $varsGallery = array(
                    'user_id'    => $guid,
                    'group_id'   => $userData['group_id'],
                    'like_title' => l('like'),
                    'like'       => 1,
                    'user_photo' => $userData['photo'],
                    'user_url'   => $userData['url'],
                    'user_name'  => $userData['name']
                );


                /*if ($groupId) {
                    $groupInfo = Groups::getInfoBasic($groupId);
                    $varsGallery['user_photo'] = GroupsPhoto::getPhotoDefault($guid, $groupId, 'r');
                    $varsGallery['user_url'] = Groups::url($groupId, $groupInfo);
                    $varsGallery['user_name'] = $groupInfo['title'];
                } else {
                    $varsGallery['user_photo'] = User::getPhotoDefault($guid, 'r');
                    $varsGallery['user_url'] = User::url($guid);
                    $varsGallery['user_name'] = guser('name');
                }*/

                foreach ($arrayKeys as $key => $value) {
                    $html->assign($value, $varsGallery);
                }
                $html->parse('comments_reply_item_likes_hide', false);
                $html->parse('comments_reply_item_delete', false);
                $html->parse('comments_reply_item', false);
                $html->parse('comments_reply_list', false);
                $html->parse('comment_likes_hide', false);
                $html->parse('comment_delete', false);

                $show = get_param('show');
                if ($show) {
                    $isParse = in_array($show, array('friend_request'));
                    if ($show == 'message') {
                        $uidSender = get_param_int('uid_sender');
                        if ($uidSender) {
                            $isParse = true;
                            $groupIdSender = get_param_int('group_id_sender');
                            if ($groupIdSender) {
                                $groupUserIdSender = Groups::getInfoBasic($groupIdSender, 'user_id');
                                if ($groupUserIdSender) {
                                    $html->setvar('show_message_group_user_id_sender', $groupUserIdSender);
                                    $html->setvar('show_message_group_id_sender', $groupIdSender);
                                }
                            }
                            $html->setvar('show_message_uid_sender', $uidSender);
                        }
                    }
                    if ($isParse) {
                        $html->parse("show_{$show}_js", false);
                    }
                }

				/* Response payment system */
				$type = get_param('type');
				$blockPaymentShow = 'payment_pop_show_' . $type;
				if ($html->blockExists($blockPaymentShow)) {
					$param = explode('-', base64_decode(get_param('custom')));
					$isErrorPayment = false;
					if (count($param) && isset($param[5])) {
						if ($param[5] == 'payment_error') {
							$isErrorPayment = true;
							if ($p != 'upgrade.php') {
								$html->parse('system_payment_error', false);
							}
						}
					}
					if (!$isErrorPayment && $cmd == 'payment_thank') {
						$html->parse($blockPaymentShow, false);
					}
				}
				/* Response payment system */

				/* Gallery list friends */
				$blockFriend = 'gallery_friend';
				if ($html->blockExists($blockFriend) && Common::isOptionActive('gallery_photo_face_detection', 'edge_gallery_settings')) {
					$friends = User::getListFriends($guid);

					$friendMy = array(array('user_id' => $guid, 'name' => $g_user['name']));
					$friends = array_merge($friendMy, $friends);
					foreach ($friends as $friend) {
						$fid = $friend['user_id'];
						$info = array(
							'user_id' => $fid,
							'name'    => toAttr($friend['name']),
							'name_title'   => toAttr($friend['name']),
							'photo'   => User::getPhotoDefault($fid, 's')
						);
						$html->assign($blockFriend, $info);
						$html->parse("{$blockFriend}_item", true);
					}
					if (count($friends)) {
						$html->parse("{$blockFriend}_ul", false);
					}
					$html->parse($blockFriend, false);
				}
				/* Gallery list friends */

				if (Common::isOptionActive('im_audio_messages')) {
					$html->parse('pl_message_recorder_player');
				}
            }

            self::parseNavbarMenuShort($html, 'menu_short_md');
            ListBlocksOrder::parseMenu($html, 'member_header_menu', 'member_header_menu', 4, 'header_menu_desktop');
            //ListBlocksOrder::parseMenu($html, 'member_header_menu', 'member_header_menu', 7, 'header_menu_mobile');
            self::parseCustomHeader($html);
            if ($p == 'city.php' && $html->blockExists('body_city')) {
				$html->parse('body_city', false);
			}

            if ($p == 'live_streaming.php' && $guid == $uid) {
                $html->parse('pp_presenter_start', false);
            }

            Common::parseErrorAccessingUser($html);
        } else {
            $loginPage = $p == 'join.php' && $cmd == 'please_login';
            $typeUsers = 'visitor';
            $blHeaderParseCustom = 'header_visitor_inner';
            if ($p == 'index.php') {
                $blockInfoHeader = 'info_block_header_visitor';
                if ($html->blockExists($blockInfoHeader)) {
					$isHeaderShort = Common::isOptionActive('header_short_show', 'edge_main_page_settings');
					if ($isHeaderShort) {
						$html->parse('header_short', false);
					} else {
						$blockInfoHeaderLeayout = Common::getOption('info_block_leayout', 'edge_main_page_settings');
						$html->parse($blockInfoHeaderLeayout, false);
						if (Common::isOptionActive('info_block_header_show', 'edge_main_page_settings')) {
							$html->parse($blockInfoHeader, false);
						}
					}
                }
                $blHeaderParseCustom = 'header_visitor_main_page';
                $html->parse('body_main_page', false);
            }

            $settingsModule = 'edge_color_scheme_visitor';
            $mainPageBackgroundType = Common::getOption('main_page_background_type', $settingsModule);
            if($isHeader) {

                $mainPageVideoCode = '';

                if($mainPageBackgroundType == 'video') {

                    $mainPageVideoCode = Common::getOption('main_page_video_code', $settingsModule);

                    if ($mainPageVideoCode) {
                        $html->setvar('main_page_video_mute', intval(Common::isOptionActive('main_page_video_mute', $settingsModule)));
                        $html->setvar('main_page_video_volume', intval(Common::getOption('main_page_video_volume', $settingsModule)));
                        $html->setvar('main_page_video_show_video_once', intval(Common::isOptionActive('main_page_video_show_video_once', $settingsModule)));
                        $html->setvar('main_page_video_play_disabled', intval($p != 'index.php'));
                        $html->setvar('main_page_video_background_head_js_is_index_page', intval($p == 'index.php'));

                        if(Common::isOptionActive('main_page_image_darken', $settingsModule)) {
                            $html->setvar('main_page_video_background_darken', 'main_page_video_background_darken');
                        }

                        $html->parse('main_page_video_background_head_js', false);
                        $html->parse('main_page_video_background_js', false);
                        $html->parse('main_page_video_background', false);
                    }

                }

                if($mainPageVideoCode == '') {
                    $mainPageVideoCode = '{}';
                }
                $html->setvar('user_profile_bg_video', $mainPageVideoCode);
            }

            $prf = $p == 'index.php' ? '' : '_inner';

            if(!isset($mainPageBackgroundType) || $mainPageBackgroundType != 'video') {
                Common::parseBackgroundImage($html, 'edge_color_scheme_visitor', $prf);
            }

            if ($html->blockExists('popup_forgot_password')) {
                $html->parse('popup_forgot_password', false);
            }


            if (!$isMobile && !$loginPage) {
                if ($p == 'index.php') {
                    Social::parse($html);
                }
                $blHeaderNavbar = 'header_visitor_navbar';
                if ($html->blockExists($blHeaderNavbar)) {
                    $html->parse($blHeaderNavbar, false);
                }
            }

            if ($html->blockExists($blHeaderParseCustom)) {
                $html->parse($blHeaderParseCustom, false);
            }

            Common::parseErrorForNotLoginUserNotExist($html);
        }

        $isCheckMobileDevice = Common::isMobile(false, true, true);
        if ($isHeader) {
            $varsHeader['url_site_subfolder'] = Common::urlSiteSubfolders();
            $varsHeader['is_app_android'] = intval($isAppAndroid);
            if ($isAppAndroid && $html->blockExists('app_android_style')) {
                if (Common::getOption('lang_loaded_rtl', 'main')) {
                    $html->parse('app_android_style_rtl', false);
                }
                $html->parse('app_android_style', false);
            }

            if ($html->blockExists('app_ios_style') && Common::isAppIos()) {
                $html->parse('app_ios_style', false);
            }

            $varsHeader['user_allowed_feature'] = User::accessCheckFeatureSuperPowersGetList();

            $isPlayerNative = $isCheckMobileDevice || Common::getOption('video_player_type') == 'player_native';
            $varsHeader['is_player_native_site'] = intval($isPlayerNative);

            $html->assign('', $varsHeader);
            if ($p != 'city.php' || ($p == 'city.php' && get_param('view') != 'mobile')) {
                if ($isCheckMobileDevice) {
                    $html->parse('meta_viewport_device_dpi', false);
                }
                $html->parse('meta_viewport', false);
            }
        }

        if ($isFooter) {
            if ($varsFooter) {
                $html->assign('', $varsFooter);
            }

            if($guid) {
                $verifiedSystemsUser = User::getProfileVerificationData(User::getInfoBasic($guid));
                $verificationSystemsData = $verifiedSystemsUser['data'];
                if($verificationSystemsData) {
                    $html->setvar('footer_verification_system_options', h_options($verificationSystemsData, ''));
                }

                if (Common::isAppIos() && Common::getAppIosApiVersion() >= 48) {
                    $html->setvar('app_ios_auth_key', User::urlAddAutologin('', $g_user));
                    $html->parse('app_ios_image_editor');
                    //$html->parse('app_ios_video_editor');
                }
                if (!$isCheckMobileDevice) {
                    $html->parse('sound_silence_activate', false);
                }
            }
        }

        if ($p != 'city.php') {
            CustomPage::parseMenu($html, 'bottom');

            Social::parseLinks($html);

            $blockFooterAbout = 'footer_about';
            if ($html->blockExists($blockFooterAbout)) {
                $id = CustomPage::getIdFromAlias('menu_bottom_about_us', 'bottom');
                if ($id) {
                    CustomPage::parsePage($html, $id, 'footer_about', 90);
                    if (Common::isOptionActive('contact')) {
                        $html->parse("{$blockFooterAbout}_contact", false);
                    }
                    $html->parse($blockFooterAbout, false);
                }
            }

            if ($isFooter) {
                CBanner::getBlock($html, 'footer');

                if (!$guid && ($p == 'index.php' || $p == 'join.php')) {
                    $html->assign('terms', PageInfo::getInfo('term_cond'));
                    $html->assign('priv', PageInfo::getInfo('priv_policy'));
                    $html->parse('page_info', false);
                }
            }
        }

        $parseBlocks = array('header_js' => "header_{$typeUsers}_js",
                             'header' => "header_{$typeUsers}",
                             'color_scheme_general' => 'color_scheme_general',
                             'color_scheme' => "color_scheme_{$typeUsers}",
                             'events_color_scheme' => 'events_settings',
                             'wall_color_scheme' => 'wall_settings',
                             'live_color_scheme' => 'live_settings',
							 'stickers_settings' => 'stickers_scheme',
                             'footer' => "footer_{$typeUsers}");

        foreach ($parseBlocks as $key => $block) {
            if ($html->blockExists($block)) {
                if ($key == 'color_scheme' || $key == 'color_scheme_general' ||
                    $key == 'events_color_scheme' || $key == 'wall_color_scheme'
                    || $key == 'live_color_scheme' || $key == 'stickers_settings') {
                    $colorSchemeOptions = $g["edge_{$block}"];
					if ($key == 'stickers_settings') {
						$stickerOptionsCheck = array('sticker_block_background',
													 'comment_sticker_block_background',
													 'comment_reply_sticker_block_background'
											   );
						foreach ($stickerOptionsCheck as $k => $v) {
							$vT = $v . '_transparent';
							if ($colorSchemeOptions[$vT] == 'Y') {
								$colorSchemeOptions[$v] = 'transparent';
							}
							unset($colorSchemeOptions[$vT]);
						}
					} elseif ($key == 'events_color_scheme' || $key == 'wall_color_scheme' ||
                        $key == 'live_color_scheme') {
                        foreach ($colorSchemeOptions as $k => $v) {
                            if(mb_strpos($k, '_color', 0, 'UTF-8') === false){
                                unset($colorSchemeOptions[$k]);
                            }
                        }
                    }
                    if ($block == 'color_scheme_visitor') {
                        $headerBackgroundColor = self::getHeaderBackgroundColor();
                        $colorSchemeOptions['main_page_header_background_color_inner'] = $headerBackgroundColor;
                        $value = Common::getBackgroundColorSheme('main_page_header_background', '', 'edge_color_scheme_visitor');
                        $colorSchemeOptions['main_page_header_background_color'] = $value;
                        $colorSchemeOptions['meta_theme_color'] = $headerBackgroundColor;
                    } elseif ($block == 'color_scheme_general') {
                        $colorSchemeOptions['color_online_user'] = getHex2Rgba(Common::getOption('color_1', 'edge_color_scheme_general'), Common::getOption('label_online_opacity', 'edge_color_scheme_general'));

                        $rgba = array('footer_title_orig_color', 'footer_menu_color',
                                      'footer_menu_color_hover', 'footer_text_color',
                                      'footer_btn_border_color', 'footer_btn_border_color_hover');
                        foreach ($rgba as $value) {
                            $colorSchemeOptions[$value] = getHex2Rgba(Common::getOption($value, 'edge_color_scheme_general'), Common::getOption("{$value}_opacity", 'edge_color_scheme_general'));
                        }
                    } elseif ($block == 'color_scheme_member') {
                        $colorSchemeOptions['meta_theme_color'] = $colorSchemeOptions['member_navbar_background_color'];
					}

					if ($html->blockExists("{$block}_css")) {
						$html->parse("{$block}_css", false);
					}

                    $html->assign('', $colorSchemeOptions);
                }
                $html->parse($block, false);
            }
        }

        if ($isHeader) {

            $htmlBackgroundColor = '';

            if($p == 'index.php') {
                $htmlBackgroundColor = self::getHeaderBackgroundColor();
            } elseif($p == 'city.php') {
                $htmlBackgroundColor = Common::getOption('3dcity_background_color', 'edge_color_scheme_general');
            } else {
                $htmlBackgroundColor = Common::getOption('page_content_background_color', 'edge_color_scheme_general');
            }

            $html->setvar('html_background_color', $htmlBackgroundColor);

            PWA::parseHeader($html);
        }
    }

    static function getHeaderBackgroundColor()
    {
        $mainPageHeaderBackgroundType = Common::getOption('main_page_header_background_type', 'edge_color_scheme_visitor');

        if($mainPageHeaderBackgroundType == 'color') {
            $headerBackgroundColor = Common::getOption('main_page_header_background_color', 'edge_color_scheme_visitor');
        } else {
            $gradientDirection = Common::getOption('main_page_header_background_color_direction', 'edge_color_scheme_visitor');
            if($gradientDirection == 'top' || $gradientDirection == 'left') {
                $headerBackgroundColor = Common::getOption('main_page_header_background_color_lower', 'edge_color_scheme_visitor');
            } else {
                $headerBackgroundColor = Common::getOption('main_page_header_background_color_upper', 'edge_color_scheme_visitor');
            }
        }

        return $headerBackgroundColor;
    }

    static function getUserName($row, $groupId = 0)
    {
        $vars = array('name_1' => $row['name'], 'name_2' => '', 'age' => $row['age']);
        if (!$groupId) {
            $name = preg_replace('/(\s)+/u', ' ', $row['name']);
            if ($name) {
                $name = User::nameOneLetterFull($row['name']);
                $parts = explode(' ', $name);
                $numParts = count($parts);
                if ($numParts > 1) {
                    $vars['name_2'] = $parts[$numParts - 1];
                    $vars['name_1'] = str_replace($vars['name_2'], '', $name);
                }
            }
        }
        self::$nameUser = $vars['name_1'] . $vars['name_2'];
        if ($groupId) {
            $userName = lSetVars('edge_profile_user_name', $vars);
        } else {
            if (User::isShowAge($row)) {
                $userName = lSetVars('edge_profile_user_name_and_age', $vars);
            } else {
                $userName = lSetVars('edge_profile_user_name', $vars);
            }
        }
        return $userName;
    }

    static function getOptionCustomHeader()
    {
        global $p;

        if ($p == 'city.php') {
            return 'header_custom_only_navbar';
        }

        $guid = guid();
        $option = 'header_page_inner';

        $pageChecked = array(
            'profile_view.php',
            'search_results.php'
        );

        $groupId = Groups::getParamId();
        $paramGetUid = User::getParamUid(0);
        $key = 'member_profile_tabs';
        $optionTab = 'set_default_profile_tab';
        if ($groupId) {
            $key = 'member_groups_tabs';
            $optionTab = 'set_default_groups_tab';
        }

        if ($paramGetUid || $groupId) {
            $pageProfileList = ListBlocksOrder::getOrderItemsList($key);
            $defaultProfileTab = Common::getOption($optionTab, 'edge');

            if (isset($pageProfileList[$defaultProfileTab]['url_page'])) {
                $pageChecked = array($pageProfileList[$defaultProfileTab]['url_page']);
            }
        }
        if (in_array($p, $pageChecked)) {
            $display = get_param('display');
            if ($p == 'profile_view.php') {
                $option = 'header_profile_my';
            }elseif (($p == 'search_results.php' && $display == 'profile')
                        || $p != 'search_results.php') {

                if ($guid == $paramGetUid) {
                    if ($groupId) {
                        $option = 'header_groups';
                    } else {
                        $option = 'header_profile_my';
                    }
                } else {
                    $option = 'header_profile_someones';
                }
            }
        }

        if ($option == 'header_profile_my') {
            self::$isProfileTabs = true;
        }

        return $option;
    }

    static function parseCustomHeader(&$html, $uid = null)
    {
        global $g;
        global $p;

        if (!$html->blockExists('header_custom_big')) {
            return;
        }

        if (in_array($p, array('videochat.php', 'audiochat.php', 'email_not_confirmed.php', 'live_streaming.php'))) {
           return;
        }

        $groupsTypeContent = Groups::getTypeContentList();
        Groups::setTypeContentList(false);

        $guid = guid();
        $groupId = Groups::getParamId();

        $option = self::getOptionCustomHeader();

        if ($groupId && $option == 'header_groups') {
            $blockHeader = Common::getOption($option, 'edge_groups_settings');
        } else {
            $blockHeader = Common::getOption($option, 'edge_member_settings');
        }

        if ($blockHeader == 'header_custom_only_navbar') {
            return;
        }

        $html->setvar('type_custom_header', $blockHeader);

        if ($uid === null) {
            $uid = User::getRequestUserId('uid', $guid);
        }


        $groupInfo = array();
        if ($groupId) {
            $groupInfo = Groups::getInfoBasic($groupId);
        }

        $row = User::getInfoBasic($uid);

        $blockHeaderCustom = 'header_custom';
        $sizePhotoMain = Common::getOption('profile_photo_main_size', 'template_options');
        if ($groupId) {
            $infoName = array('name' => $groupInfo['title'], 'age' => '');
			$nameShort = User::nameShort($groupInfo['title']);
            $infoBlock = array('name' => self::getUserName($infoName, $groupId),
                               'user_name' => $nameShort,
							   'user_name_attr' => toAttr($nameShort),
                               'user_name_title' => toAttr($groupInfo['title']),
                               'visible_name_short' => intval(self::$nameUser != $groupInfo['title']),
                               'url' => Groups::url($uid, $groupInfo)
                         );
            $photoMain = User::getPhotoDefault($row['user_id'], $sizePhotoMain, false, '', DB_MAX_INDEX, false, false, false, false, $groupId);
            $photoMainR = User::getPhotoDefault($row['user_id'], 'r', false, '', DB_MAX_INDEX, false, false, false, false, $groupId);
            $photoMainId = User::getPhotoDefault($row['user_id'], $sizePhotoMain, true, '', DB_MAX_INDEX, false, false, false, false, $groupId);
        } else {
			$nameShort = User::nameShort($row['name']);
            $infoBlock = array('name' => self::getUserName($row),
                               'user_name' => $nameShort,
							   'user_name_attr' => toAttr($nameShort),
                               'user_name_title' => toAttr($row['name']),
                               'visible_name_short' => intval(self::$nameUser != $row['name']),
                               'url' => User::url($uid, $row),
                         );
            $photoMain = User::getPhotoDefault($row['user_id'], $sizePhotoMain, false, $row['gender']);
            $photoMainR = User::getPhotoDefault($row['user_id'], 'r', false, $row['gender']);
            $photoMainId = User::getPhotoDefault($row['user_id'], $sizePhotoMain, true);
        }
        $infoBlock['photo'] = $photoMain;
        $infoBlock['photo_r'] = $photoMainR;
        $infoBlock['photo_id'] = $photoMainId;
        $infoBlock['uid'] = $uid;

        $html->assign($blockHeaderCustom, $infoBlock);

        if ($option == 'header_profile_someones' && $guid != $uid) {
            if (User::isOnline($uid, $row)) {
                $html->parse('status_online_profile', false);
            }
            if (!$groupId && Common::isOptionActive('contact_blocking')) {
                $isEntryBlocked = intval(User::isEntryBlocked($guid, $uid));
                $blockProfileBlocked = 'profile_user_blocked_bl';
                if ($isEntryBlocked) {
                    $html->parse("{$blockProfileBlocked}_show", false);
                }
                $html->parse($blockProfileBlocked, false);
            }
        }

		$blockPhotosGrid = "{$blockHeader}_grid";
		$isParseProfileCover = false;
		if (User::parseProfileBgCover($html, $row['user_id'], $groupId, $blockPhotosGrid)) {
			$isParseProfileCover = true;
		}

        if ($blockHeader == 'header_custom_big') {
            if (!$groupId) {
               User::parseProfileVerification($html, $row, 'profile_verification_verified_from_big_header');
            }

            //$photoMainId = User::getPhotoDefault($row['user_id'], $sizePhotoMain, true);
			if ($guid == $uid) {
				$blockCustom = 'header_custom';
				if (!$photoMainId) {
					$html->parse("{$blockCustom}_empty_photo", false);
					$html->parse("{$blockCustom}_empty_photo_title", false);
					$html->parse("{$blockCustom}_upload_profile_photo_editor_hide", false);
				}
				$html->parse("{$blockCustom}_upload_profile_cover", false);
				$html->parse("{$blockCustom}_upload_profile_photo", false);
			}
            $blockAdditionMenu = 'mn_circle';
            $numberItem = 6;
        } else {
            $blockAdditionMenu = 'mn_circle_small';
            $numberItem = 3;
        }

        if ($groupId) {
            $module = 'member_groups_additional_menu';
            if ($guid != $uid) {
                $module = 'member_groups_visited_additional_menu';
                if ($blockHeader != 'header_custom_big') {
                    $module = 'member_groups_visited_additional_menu_inner';
                }
            }
        } else {
            $module = 'member_user_additional_menu';
            if ($guid != $uid) {
                $module = 'member_visited_additional_menu';
                if ($blockHeader != 'header_custom_big') {
                    $module = 'member_visited_additional_menu_inner';
                }
            }
        }
        ListBlocksOrder::parseAdditionMenu($html, $module, $blockAdditionMenu, $numberItem);

		if (!$isParseProfileCover) {
			/* Grid Photos */
			$numberPhoto = 26;

			$whereSql = ' AND `hide_header` = 0';
			$profilePhoto = CProfilePhoto::preparePhotoList($row['user_id'], null, $whereSql, 26, false, false, false, $groupId);
			$profileVideo = CProfileVideo::getVideosList('', 26, $row['user_id'], false, true, 0, $whereSql, $groupId);

			$i = 0;
			$profilePhoto = array_merge($profilePhoto, $profileVideo);
			shuffle($profilePhoto);

			$blockPhotosGridItem = "{$blockPhotosGrid}_item";
			$varPhotosGridItemId = "{$blockPhotosGridItem}_id";
			$varPhotosGridItemDesc = "{$blockPhotosGridItem}_desc";
			$varPhotosGridItemUrl = "{$blockPhotosGridItem}_url";
			$profilePhotoDisplay = array();
			$noPrivatePhoto = Common::isOptionActiveTemplate('no_private_photos');
			$showPrivatePhoto = false;
			if ($noPrivatePhoto) {
				$showPrivatePhoto = true;
			}
			$field = 'default';
			if ($groupId) {
				$field = 'default_group';
			}
			foreach ($profilePhoto as $id => $photo) {
				if ($photo[$field] === 'Y' || ($photo['private'] === 'Y' && !$showPrivatePhoto) || $photo['visible'] !== 'Y') {
					continue;
				}
				$isVideo = isset($photo['video_id']);
				if ($isVideo) {
					$urlPhoto = $g['path']['url_files'] . $photo['src_src'];
				} else {
					$urlPhoto = $g['path']['url_files'] . $photo['src_b'];
				}
				$vars = array('id'          => $photo['photo_id'],
                          'url'         => $urlPhoto,
                          'description' => $photo['description'],
						  'description_attr' => toAttr($photo['description']),
                          'info'        => json_encode($photo),
                          'video'       => intval($isVideo));
				$profilePhotoDisplay[] = $vars;
				$html->assign($blockPhotosGridItem, $vars);
				$html->parse($blockPhotosGridItem, true);
				if ($i++ == ($numberPhoto - 1)) {
					break;
				}
			}
			if ($i < $numberPhoto) {//&& false
				if ($profilePhotoDisplay) {
					$numberPhoto = 10;
					$d = $numberPhoto - count($profilePhotoDisplay);
					$j = 0;
					for ($i = 1; $i <= $d; $i++) {
						if (!isset($profilePhotoDisplay[$j])) {
							$j = 0;
						}
						$html->assign($blockPhotosGridItem, $profilePhotoDisplay[$j]);
						$html->parse($blockPhotosGridItem, true);
						$j++;
					}
				} else {
					for ($i = 1; $i <= $numberPhoto; $i++) {
						$html->setvar("{$blockPhotosGridItem}_class", $guid == $uid ? 'photo_upload' : '');
						$html->setvar("{$blockPhotosGridItem}_info", json_encode(array()));
						$html->setvar($varPhotosGridItemId, 'empty_photo');
						$html->setvar($varPhotosGridItemDesc, l('upload_photo_link'));
						$html->setvar($varPhotosGridItemUrl, $g['tmpl']['url_tmpl_main'] . 'images/photo_camera.png');
						$html->parse($blockPhotosGridItem, true);
					}
				}
			}
		}
		/* Grid Photos */

        if ($blockHeader == 'header_custom_small') {
            $option = $groupId ? 'member_groups_inner_tabs' : 'member_profile_inner_tabs';
        } else {
            $option = $groupId ? 'member_groups_tabs' : 'member_profile_tabs';
        }

        if ($blockHeader == 'header_custom_small') {
            ListBlocksOrder::parseMenu($html, $option, 'profile_menu_inner_small');
        } else {
            ListBlocksOrder::parseMenu($html, $option, 'profile_menu_inner_big', ListBlocksOrder::$numberItemProfileMenuBig);
        }

        $html->parse($blockHeader, false);

        Groups::setTypeContentList($groupsTypeContent);
    }

    static function isTemplateColums($showAlways = false)
    {
        global $p;

        $guid = guid();
        if ($guid) {
            $header = self::getOptionCustomHeader();
            if ($header == 'header_profile_my' || $header == 'header_profile_someones' || $header == 'header_groups') {
                $showAlways = true;
            }
            /*$paramUid = get_param_int('uid');
            $display = get_param('display');
            if ($p == 'search_results.php' && $display == 'profile' && $guid == $paramUid){
                $guid = 1;
            }*/
        }

        return $guid && Common::isAllowedModuleTemplate('profile_column')
               && (Common::isOptionActive('show_columns_inner_pages', 'edge_member_settings')  || $showAlways)
               && !in_array($p, Common::getOptionTemplate('pages_one_column'));
    }

    static function parseColumnListImg(&$html, $uid, $type, $numColumn, $typeOrder, $blockColumnType, $groupId = 0)
    {
        global $g;

        if (!$numColumn) {
            $numColumn = 9;
        }
        $isPhotos = in_array($type, array('photos', 'photos_list_1', 'photos_list_2'));
        //$isBlogs = in_array($type, array('photos', 'photos_list_1', 'photos_list_2'));
        if ($isPhotos) {
            $list = CProfilePhoto::getPhotosList($typeOrder, false, '0, ' . $numColumn, $uid, $groupId);
            $count = CProfilePhoto::getTotalPhotos($uid, false, $groupId);
        } else {
            $list = CProfileVideo::getVideosList($typeOrder, '0, ' . $numColumn, $uid, false, true, 0, '', $groupId);
            $count = CProfileVideo::getTotalVideos($uid, $groupId);
        }

        $guid = guid();
        $isParseColumn = false;
        $blockColumn = "{$blockColumnType}_{$type}";
        $blockColumnItem = "{$blockColumn}_item";

        $groupsPhotoList = Groups::getParamTypeContentList();
        $isPagesPhotosList = $groupsPhotoList == 'group_page';

        if (in_array($type, array('photos_list_1', 'photos_list_2', 'videos_list_1', 'videos_list_2'))) {
            $typeKey = str_replace(array('_1', '_2'), '', $type);
            $lTitle = "edge_column_{$typeKey}_{$typeOrder}_title";
            $html->setvar("{$blockColumn}_title", l($lTitle));
        } else {

            $vars = array('count' => $count);
            $keyType = $type;
            $url = '';
            if ($groupId) {
                $keyType = (Groups::isPage() ? 'page_' : 'group_') . $keyType;
                if ($type == 'photos') {
                    $url = Common::pageUrl('group_photos_list', $groupId);
                } elseif($type == 'videos') {
                    $url = Common::pageUrl('group_vids_list', $groupId);
                }
            } else {
                if ($groupsPhotoList && $uid == $guid) {
                    if ($type == 'photos') {
                        $url = $isPagesPhotosList ? Common::pageUrl('user_my_pages_photos_list', $guid) : Common::pageUrl('user_my_groups_photos_list', $guid);
                    } elseif($type == 'videos') {
                        $url = $isPagesPhotosList ? Common::pageUrl('user_my_pages_vids_list', $guid) : Common::pageUrl('user_my_groups_vids_list', $guid);
                    }
                } else {
                    if ($type == 'photos') {
                        $url = Common::pageUrl('user_photos_list');
                    } elseif($type == 'videos') {
                        $url = Common::pageUrl('user_vids_list');
                    }
                }
            }

            $lTitle = "edge_column_{$keyType}_title";
            if (in_array($type, array('photos', 'videos')) && guid() != $uid) {
                $lTitle = "edge_column_{$keyType}_title_other_user";
            }

            $html->setvar("{$blockColumn}_url", $url);
            $html->setvar("{$blockColumn}_title", lSetVars($lTitle, $vars));
        }
        $html->setvar("{$blockColumn}_type_order", $typeOrder);

        foreach ($list as $id => $item) {
            /*if ($type == 'photos'
                    && (($item['private'] == 'Y') || ($uid == guid() && $item['default'] == 'Y') && $count > $numColumn)) {
                    continue;
            }*/
            if ($numColumn) {
                $isParseColumn = true;
                $id = $isPhotos ? $item['photo_id'] : $item['video_id'];
                $html->setvar("{$blockColumnItem}_id", $id);
                $html->setvar("{$blockColumnItem}_info", json_encode($item));
                $html->setvar("{$blockColumnItem}_user_id", $item['user_id']);
				$html->setvar("{$blockColumnItem}_desc_attr", toAttr($item['description']));
                $html->setvar("{$blockColumnItem}_desc", $item['description']);
                $urlPhoto = $g['path']['url_files'] . $item['src_s'];
                $html->setvar("{$blockColumnItem}_url", $urlPhoto);
                $html->parse($blockColumnItem, true);
            } else {
                break;
            }
            $numColumn--;
        }
        return $isParseColumn;
    }

    static function parseColumnListGroups(&$html, $uid, $type, $blockColumnType)
    {
        global $g;

        $numColumn = Common::getOptionInt("number_{$type}_left_column", 'edge_member_settings');
        $typeOrder = Common::getOption("list_{$type}_type_order", 'edge_general_settings');

        $isPage = intval($type == 'pages');

		$whereCustomUser = '';
		$listSubscribers = Groups::getUserGroupsSubscribers($uid);
		if ($listSubscribers) {
			$whereCustomUser = ' OR `group_id` IN(' . to_sql($listSubscribers, 'Plain') . ')';
		}
        $list = GroupsList::getListGroups($numColumn, $typeOrder, $uid, $isPage, $whereCustomUser);
        $count = GroupsList::getTotalGroups($uid, $isPage, $whereCustomUser);

        $isParseColumn = false;
        $blockColumn = "{$blockColumnType}_{$type}";
        $blockColumnItem = "{$blockColumn}_item";

        $vars = array('count' => $count);
        $url = $isPage ? Common::pageUrl('user_pages_list') : Common::pageUrl('user_groups_list');
        $lTitle = "edge_column_{$type}_title";
        if (guid() != $uid) {
            $lTitle = "edge_column_{$type}_title_other_user";
        }

        $html->setvar("{$blockColumn}_url", $url);
        $html->setvar("{$blockColumn}_title", lSetVars($lTitle, $vars));

        $html->setvar("{$blockColumn}_type_order", $typeOrder);

        foreach ($list as $id => $item) {
            if ($numColumn) {
                $isParseColumn = true;
                $html->setvar("{$blockColumnItem}_id", $item['group_id']);
                $html->setvar("{$blockColumnItem}_user_id", $item['user_id']);
                $html->setvar("{$blockColumnItem}_title", $item['title']);
				$html->setvar("{$blockColumnItem}_title_attr", toAttr($item['title']));
                $html->setvar("{$blockColumnItem}_url", Groups::url($item['group_id']));
                $urlPhoto = GroupsPhoto::getPhotoDefault($item['user_id'], $item['group_id'], 's');
                $html->setvar("{$blockColumnItem}_photo_url", $g['path']['url_files'] . $urlPhoto);

                $html->parse($blockColumnItem, true);
            } else {
                break;
            }
            $numColumn--;
        }
        return $isParseColumn;
    }


    static function parseColumnListBlogs(&$html, $uid, $type, $blockColumnType, $numColumn, $typeOrder, $parseTitle = true)
    {
        global $g;

        $list = Blogs::getList($typeOrder, $numColumn, $uid);
        $count = Blogs::getTotalBlogs($uid);

        $isParseColumn = false;
        $blockColumn = "{$blockColumnType}_{$type}";
        $blockColumnItem = "{$blockColumn}_item";

        $url = Common::pageUrl('user_blogs_list');
        $html->setvar("{$blockColumn}_url", $url);

        if ($parseTitle) {
            $vars = array('count' => $count);
            $lTitle = "edge_column_{$type}_title";
            if (guid() != $uid) {
                $lTitle = "edge_column_{$type}_title_other_user";
            }
            $html->setvar("{$blockColumn}_title", lSetVars($lTitle, $vars));
        }

        $html->setvar("{$blockColumn}_type_order", $typeOrder);

        foreach ($list as $id => $item) {
            if ($numColumn) {
                $isParseColumn = true;
                $html->setvar("{$blockColumnItem}_id", $item['id']);
                $html->setvar("{$blockColumnItem}_user_id", $item['user_id']);
                $html->setvar("{$blockColumnItem}_url", Blogs::url($item['id']));
                $html->setvar("{$blockColumnItem}_title", toAttr($item['subject']));
                $image = explode('|', $item['images']);
                if ($image) {
                    $image = CBlogsTools::getImg($item['id'], $image[0], 't');
                }
                if ($image) {
                    $urlPhoto = $image;
                } else {
                    $urlPhoto = $g['path']['url_files'] . 'blog_t.png';
                }
                $html->setvar("{$blockColumnItem}_photo_url", $urlPhoto);

                $html->parse($blockColumnItem, true);
            } else {
                break;
            }
            $numColumn--;
        }
        return $isParseColumn;
    }

    static function parseColumnListSongs(&$html, $uid, $type, $numColumn, $typeOrder, $blockColumnType, $groupId = 0)
    {
        global $g;

        if (!$numColumn) {
            $numColumn = 9;
        }

        $showAllMySongs = true;
        $list = Songs::getList($typeOrder, '0,' . $numColumn, $uid, $groupId, $showAllMySongs);
        $count = Songs::getTotal($uid, $groupId);

        $guid = guid();
        $isParseColumn = false;
        $blockColumn = "{$blockColumnType}_{$type}";
        $blockColumnItem = "{$blockColumn}_item";

        $groupsSongsList = Groups::getParamTypeContentList();
        $isPagesPhotosList = $groupsSongsList == 'group_page';

        if (in_array($type, array('songs_list_1', 'songs_list_2'))) {
            $typeKey = str_replace(array('_1', '_2'), '', $type);
            $lTitle = "edge_column_{$typeKey}_{$typeOrder}_title";
            $html->setvar("{$blockColumn}_title", l($lTitle));
        } else {
            $vars = array('count' => $count);
            $keyType = $type;
            $url = '';
            if ($groupId) {
                $keyType = (Groups::isPage() ? 'page_' : 'group_') . $keyType;
                $url = Common::pageUrl('group_songs_list', $groupId);
            } else {
                if ($groupsSongsList && $uid == $guid) {
                    $url = $isPagesPhotosList ? Common::pageUrl('user_my_pages_songs_list', $guid) : Common::pageUrl('user_my_groups_songs_list', $guid);
                } else {
                    $url = Common::pageUrl('user_songs_list');
                }
            }

            $lTitle = "edge_column_{$keyType}_title";
            if (guid() != $uid) {
                $lTitle = "edge_column_{$keyType}_title_other_user";
            }

            $html->setvar("{$blockColumn}_url", $url);
            $html->setvar("{$blockColumn}_title", lSetVars($lTitle, $vars));
        }
        $html->setvar("{$blockColumn}_type_order", $typeOrder);

        foreach ($list as $id => $item) {
            if ($numColumn) {
                $isParseColumn = true;
				$item['song_title'] = strip_tags($item['song_title']);//Fix old template
                $html->setvar("{$blockColumnItem}_id", $item['song_id']);
                $html->setvar("{$blockColumnItem}_user_id", $item['user_id']);
                $html->setvar("{$blockColumnItem}_desc", toAttr($item['song_title']));

                $urlPhoto = Songs::getImageDefault($item['song_id']);
                $html->setvar("{$blockColumnItem}_url", $urlPhoto);

                $html->setvar("{$blockColumnItem}_mp3", toJs(Songs::getFile($item['song_id'])));
                $html->setvar("{$blockColumnItem}_mp3_title", toJs($item['song_title']));

                $html->parse($blockColumnItem, true);
            } else {
                break;
            }
            $numColumn--;
        }
        return $isParseColumn;
    }

    static function parseColumn(&$html, $uid = null, $row = null, $showAlways = false)
    {
        global $g, $p;

        if (!self::isTemplateColums($showAlways)) {
            $pOneColumn = Common::getOptionTemplate('pages_parse_block_one_column');
            if (!is_array($pOneColumn)) {
                $pOneColumn = array();
            }
            if (in_array($p, $pOneColumn)) {
                $html->parse('one_column', false);
            }
            return;
        }

        $guid = guid();
        $blockColumnLeft = 'left_column';

        if ($uid === null) {
            $uid = $guid;
        }
        $groupId = Groups::getParamId();
        $isPageGroup = Groups::isPage();

        $parseBlockPhotos = true;
        if($groupId && !$isPageGroup){
            $isAccessGroup = Groups::isAccessGroup();
            if (!$isAccessGroup) {
                $parseBlockPhotos = false;
            }
        }

        $html->setvar('profile_column_user_id', $uid);
        /*$param = $uid == guid() ? '' : 'uid=' . $uid;
        if ($param) {
            $param1 = $param2 = '?' . $param;
            $html->setvar('profile_column_page_param_1', $param1);
            if (!Common::isOptionActive('seo_friendly_urls')) {
                $param2 = '&' . $param;
            }
            $html->setvar('profile_column_page_param_2', $param2);
        }*/

        $isPageVids = Common::isPage('vids_list');
        $isPagePhotos = Common::isPage('photos_list');
        $isPageSongs = Common::isPage('songs_list');

        $isPagePages = ($p == 'groups_list.php' && $isPageGroup) || Common::isPage('page_add');
        $isPageGroups = $p == 'groups_list.php' || Common::isPage('group_add');
        $isPageBlogs = $p == 'blogs_list.php' || Common::isPage('blogs_add') ;

        $isPhotosListGroups = Common::isPage('pages_photos_list') || Common::isPage('groups_photos_list');
        $isVideosListGroups = Common::isPage('pages_vids_list') || Common::isPage('groups_vids_list');
        $isSongsListGroups = Common::isPage('pages_songs_list') || Common::isPage('groups_songs_list');
        /* Left */
        $keyLeftColumn = $groupId ? 'member_groups_column_left_order' : 'member_column_left_order';

        $blocksColumnLeft = ListBlocksOrder::getOrderItemsList($keyLeftColumn);
        $blockColumnLeftItem = "{$blockColumnLeft}_item";
        $blockItem = '';
        if ($isPageVids || $isPagePhotos) {
            unset($blocksColumnLeft['songs']);
            unset($blocksColumnLeft['blogs']);
            unset($blocksColumnLeft['pages']);
            unset($blocksColumnLeft['groups']);

            if ($isPageVids) {
                unset($blocksColumnLeft['photos']);
            } else {
                unset($blocksColumnLeft['videos']);
            }
        } elseif ($isPagePages || $isPageGroups) {
            unset($blocksColumnLeft['songs']);
            unset($blocksColumnLeft['blogs']);

            unset($blocksColumnLeft['photos']);
            unset($blocksColumnLeft['videos']);
            if ($isPagePages) {
                unset($blocksColumnLeft['groups']);
            } else {
                unset($blocksColumnLeft['pages']);
            }
        } elseif ($isPageBlogs) {
            unset($blocksColumnLeft['songs']);
            unset($blocksColumnLeft['pages']);
            unset($blocksColumnLeft['groups']);
            unset($blocksColumnLeft['photos']);
            unset($blocksColumnLeft['videos']);
        } elseif ($isPageSongs) {
            unset($blocksColumnLeft['blogs']);
            unset($blocksColumnLeft['pages']);
            unset($blocksColumnLeft['groups']);
            unset($blocksColumnLeft['photos']);
            unset($blocksColumnLeft['videos']);
        }

        if ($groupId || $isPhotosListGroups || $isVideosListGroups || $isSongsListGroups) {
            unset($blocksColumnLeft['blogs']);
        }

        foreach ($blocksColumnLeft as $type => $value) {
            if ($blockItem) {
                $html->clean($blockItem);
            }
            $blockItem = '';
            if (($type == 'photos' || $type == 'videos') && $parseBlockPhotos) {
                $numColumn = Common::getOptionInt("number_{$type}_left_column", self::getOptionsSettingsKey($groupId));
                $typeOrder = Common::getOption("list_{$type}_type_order", 'edge_general_settings');
                $blockColumn = "{$blockColumnLeft}_{$type}";

                $isParse = self::parseColumnListImg($html, $uid, $type, $numColumn, $typeOrder, $blockColumnLeft, $groupId);
                if (!$isParse && $uid == $guid) {
                    $html->parse("{$blockColumn}_hide", false);
                    $isParse = true;
                }
                if ($isParse) {
                    $blockItem = $blockColumn;
                }

            } elseif ($type == 'blogs') {

                $numColumn = Common::getOptionInt('number_blogs_left_column', 'edge_member_settings');
                $typeOrder = Common::getOption('list_blog_posts_type_order', 'edge_general_settings');

                $isParse = self::parseColumnListBlogs($html, $uid, $type, $blockColumnLeft, $numColumn, $typeOrder);

                $blockColumn = "{$blockColumnLeft}_{$type}";
                if (!$isParse && $uid == $guid) {
                    $html->parse("{$blockColumn}_hide", false);
                    $isParse = true;
                }
                if ($isParse) {
                    $blockItem = $blockColumn;
                }
            } elseif ($type == 'songs') {

                $numColumn = Common::getOptionInt('number_songs_left_column', 'edge_member_settings');
                $typeOrder = Common::getOption('list_songs_type_order', 'edge_general_settings');

                $isParse = self::parseColumnListSongs($html, $uid, $type, $numColumn, $typeOrder, $blockColumnLeft, $groupId);

                $blockColumn = "{$blockColumnLeft}_{$type}";
                if (!$isParse && $uid == $guid) {
                    $html->parse("{$blockColumn}_hide", false);
                    $isParse = true;
                }
                if ($isParse) {
                    $blockItem = $blockColumn;
                }
            } elseif ($type == 'pages' || $type == 'groups') {
                $isParse = self::parseColumnListGroups($html, $uid, $type, $blockColumnLeft);

                $blockColumn = "{$blockColumnLeft}_{$type}";
                if (!$isParse && $uid == $guid) {
                    $html->parse("{$blockColumn}_hide", false);
                    $isParse = true;
                }
                if ($isParse) {
                    $blockItem = $blockColumn;
                }
            } elseif ($type == 'banner') {
                $blockItem = 'left_banner';
                CBanner::getBlock($html, 'left_column');
            } elseif ($type == 'custom_menu') {
                $blockItem = 'left_menu';
                CustomPage::parseMenu($html, 'left_column');
            }
            if ($blockItem) {
                $html->parse($blockItem, false);
                $html->parse($blockColumnLeftItem, true);
            }
        }
        if ($blocksColumnLeft) {
            $html->parse($blockColumnLeft, false);
        }
        /* Left */
        /* Right */
        $blogId = Blogs::getParamId();
        if ($blogId) {
            $keyColumnRight = 'blogs_column_right_order';
        } else {
            $keyColumnRight = $groupId ? 'member_groups_column_right_order' : 'member_column_right_order';
        }

        $blocksColumnRight = ListBlocksOrder::getOrderItemsList($keyColumnRight);
        $blockColumnRight = 'right_column';
        $blockColumnRightItem = "{$blockColumnRight}_item";
        $blockItem = '';

        $infoColumn = array();

        if ($groupId) {
            $row = Groups::getInfoBasic($groupId);
            $infoColumn = array(
                'name'  => trim($row['title']),
                'birth' => $row['date'],
                'about' => trim($row['description']),
                'gender' => '',
                'orientation' => '',
                'city'   => $row['city']
            );
            $userNameShort = $infoColumn['name'];
        } else {
            if ($row === null) {
                $row = User::getInfoFull($uid);
            }
            $orientation = User::getOrientationInfo($row['orientation']);
            $orientationTitle = '';
            if ($orientation) {
                $orientationTitle = l($orientation['title']);
            }
            $infoColumn = array(
                'name'  => $row['name'],
                'birth' => $row['birth'],
                'about' => trim(isset($row['about_me']) ? trim($row['about_me']) : ''),
                'gender' => $row['gender'],
                'orientation' => $orientationTitle,
                'city'   => $row['city']
            );
            $userNameShort = User::nameShort($infoColumn['name']);

            if ($blogId) {
                $infoColumn['city'] = '';
                $infoColumn['gender'] = '';
                $infoColumn['orientation'] = '';
                $blogCreated = Blogs::getInfo($blogId, 'dt');
                $infoColumn['birth'] = $blogCreated;
            }
        }

        $html->setvar('profile_column_user_name', $userNameShort);

        if (!self::$isProfileTabs) {
            unset($blocksColumnRight['blogs_list_last']);
        }

        $parseTypeList = array('videos_list_1', 'videos_list_2', 'photos_list_1', 'photos_list_2');
        $isBlogsList = Common::isPage('blogs_list');
        if ($isBlogsList) {
            unset($blocksColumnRight['friends']);
            unset($blocksColumnRight['friends_online']);
            unset($blocksColumnRight['profile_info']);
        } else {
            unset($blocksColumnRight['blogs_list_1']);
            unset($blocksColumnRight['blogs_list_2']);
        }

        if ($isPageSongs) {
            unset($blocksColumnRight['friends']);
            unset($blocksColumnRight['friends_online']);
            unset($blocksColumnRight['profile_info']);
        } else {
            unset($blocksColumnRight['songs_list_1']);
            unset($blocksColumnRight['songs_list_2']);
        }

        if ($isPageVids || $isPagePhotos
                || $isVideosListGroups || $isPhotosListGroups) {
            unset($blocksColumnRight['friends']);
            unset($blocksColumnRight['friends_online']);
            unset($blocksColumnRight['profile_info']);
            if ($isPageVids || $isVideosListGroups) {
                unset($blocksColumnRight['photos_list_1']);
                unset($blocksColumnRight['photos_list_2']);
            } else {
                unset($blocksColumnRight['videos_list_1']);
                unset($blocksColumnRight['videos_list_2']);
            }
        } else {
            unset($blocksColumnRight['photos_list_1']);
            unset($blocksColumnRight['photos_list_2']);
            unset($blocksColumnRight['videos_list_1']);
            unset($blocksColumnRight['videos_list_2']);
        }

        foreach ($blocksColumnRight as $type => $value) {
            if ($blockItem) {
                $html->clean($blockItem);
            }
            $blockItem = '';

            if ($type == 'subscribers_group' || $type == 'subscribers_group_online') {
                $blockItem = "{$blockColumnRight}_friend";
                $uidCur = $uid;
                $vars = array();
                $keyL = $isPageGroup ? 'page_' : 'group_';
                if ($type == 'subscribers_group_online') {
                    $blockItem .= '_online';
                    $maxNumberFriends = Common::getOptionInt('number_subscribers_online_right_column', 'edge_groups_settings');
                    $uidCur = guid();
                    $lTitle = "edge_column_{$keyL}subscribers_online_title";
                    $urlPage = 'group_page_liked';
                } else {
                    $maxNumberFriends = Common::getOptionInt('number_subscribers_right_column', 'edge_groups_settings');
                    $lTitle = "edge_column_{$keyL}subscribers_title";
                    if ($isPageGroup) {
                        $urlPage = 'group_page_liked';
                    } else {
                        $urlPage = 'group_subscribers';
                    }
                }

                $friends = Groups::getListSubscribers($groupId, $type == 'subscribers_group_online', $maxNumberFriends);
                $blockFriendItem = "{$blockItem}_item";
                $count = 0;
                if ($friends) {
                    if ($type == 'subscribers_group_online') {
                        $count = Groups::getNumberSubscribersOnline($groupId);
                    } else {
                        $count = Groups::getNumberSubscribers($groupId);
                    }
                    foreach ($friends as $key => $friend) {
                        $html->setvar("{$blockFriendItem}_user_id", $friend['user_id']);
                        $title = $friend['name'];
                        if (User::isShowAge($friend)) {
                            $title .= ', ' .  getAge($friend['birth']);
                        }
                        $html->setvar("{$blockFriendItem}_name", $title);
                        $photo = $g['path']['url_files'] .  User::getPhotoDefault($friend['user_id'], 's', false, $friend['gender']);
                        $html->setvar("{$blockFriendItem}_photo", $photo);
                        $html->setvar("{$blockFriendItem}_url", User::url($friend['user_id']));
                        if ($type == 'subscribers_group' && $uidCur == $guid) {
                            //$html->parse("{$blockFriendItem}_link_im", false);
                        }

                        $html->parse($blockFriendItem, true);
                    }
                    if ($type == 'subscribers_group_online') {
                        if ($count <= $maxNumberFriends) {
                            $html->parse("{$blockItem}_more_hide", false);
                        }
                    }
                    $html->parse("{$blockItem}_show", false);
                }
                $vars['count'] = $count;
                $html->setvar("{$blockItem}_title", lSetVars($lTitle, $vars));
                $html->setvar("{$blockItem}_url", Common::pageUrl($urlPage, $groupId));
            } elseif (($type == 'friends' || $type == 'friends_online') && Common::isOptionActive('friends_enabled')) {
                $blockItem = "{$blockColumnRight}_friend";
                $uidCur = $uid;
                $vars = array();
                if ($type == 'friends_online') {
                    $blockItem .= '_online';
                    $maxNumberFriends = Common::getOptionInt('number_friends_online_right_column', 'edge_member_settings');
                    $uidCur = guid();
                    $lTitle = 'edge_column_friends_online_title';
                    $urlPage = 'my_friends_online';
                } else {
                    $maxNumberFriends = Common::getOptionInt('number_friends_right_column', 'edge_member_settings');
                    $lTitle = 'edge_column_friends_title';
                    if (guid() != $uid) {
                        $lTitle .= '_other_user';
                    }
                    $urlPage = 'user_friends_list';
                }

                $friends = User::getListFriends($uidCur, $type == 'friends_online', $maxNumberFriends);
                $blockFriendItem = "{$blockItem}_item";
                $count = 0;
                if ($friends) {
                    if ($type == 'friends_online') {
                        $count = User::getNumberFriendsOnline();
                    } else {
                        $count = User::getNumberFriends($uid);
                    }
                    foreach ($friends as $key => $friend) {
                        $html->setvar("{$blockFriendItem}_user_id", $friend['user_id']);
                        $title = $friend['name'];
                        if (User::isShowAge($friend)) {
                            $title .= ', ' .  getAge($friend['birth']);
                        }
                        $html->setvar("{$blockFriendItem}_name", $title);
                        $photo = $g['path']['url_files'] .  User::getPhotoDefault($friend['user_id'], 's', false, $friend['gender']);
                        $html->setvar("{$blockFriendItem}_photo", $photo);
                        $html->setvar("{$blockFriendItem}_url", User::url($friend['user_id']));
                        if ($type == 'friends' && $uidCur == $guid) {
                            $html->parse("{$blockFriendItem}_link_im", false);
                        }
                        if ($type == 'friends_online') {
                            $userLiveNowId = LiveStreaming::getUserLiveNowId($friend['user_id']);
                            if ($userLiveNowId) {
                                $liveNowUrl = Common::pageUrl('live_id', $friend['user_id'], $userLiveNowId);
                                $html->setvar("{$blockFriendItem}_live_now_url", $liveNowUrl);
                                $html->parse("{$blockFriendItem}_live_now", false);
                            } else {
                                $html->clean("{$blockFriendItem}_live_now");
                            }
                        }

                        $html->parse($blockFriendItem, true);
                    }
                    if ($type == 'friends_online') {
                        if ($count <= $maxNumberFriends) {
                            $html->parse("{$blockItem}_more_hide", false);
                        }
                    }
                    $html->parse("{$blockItem}_show", false);
                }
                $vars['count'] = $count;
                $html->setvar("{$blockItem}_title", lSetVars($lTitle, $vars));
                $html->setvar("{$blockItem}_url", Common::pageUrl($urlPage));

            } elseif (in_array($type, array('blogs_list_1', 'blogs_list_2', 'blogs_list_last'))) {
                if ($type == 'blogs_list_last') {
                    $typeOrder = 'order_new_blogs';
                    $typeOrderKey = 'last_user';
                    $uidList = $uid;
                    $html->setvar("{$blockColumnRight}_{$type}_url", Common::pageUrl('user_blogs_list', $uid));
                } else {
                    $uidList = 0;
                    $typeOrder = Common::getOption("{$type}_type_order", 'edge_member_settings');
                    $typeOrderKey = $typeOrder;
                }
                $numColumn = Common::getOptionInt("number_{$type}_right_column", 'edge_member_settings');

                $typeKey = str_replace(array('_1', '_2', '_last'), '', $type);
                $lTitle = "edge_column_{$typeKey}_{$typeOrderKey}_title";

                $html->setvar("{$blockColumnRight}_{$type}_title", l($lTitle));

                $blockColumn = "{$blockColumnRight}_{$type}";
                if (self::parseColumnListBlogs($html, $uidList, $type, $blockColumnRight, $numColumn, $typeOrder, false)) {
                    $blockItem = $blockColumn;
                }

            } elseif (in_array($type, array('songs_list_1', 'songs_list_2'))) {
                $typeOrder = Common::getOption("{$type}_type_order", 'edge_member_settings');
                $numColumn = Common::getOptionInt("number_{$type}_right_column", 'edge_member_settings');

                $blockColumn = "{$blockColumnRight}_{$type}";
                if (self::parseColumnListSongs($html, 0, $type, $numColumn, $typeOrder, $blockColumnRight)) {
                    $blockItem = $blockColumn;
                }

            } elseif (in_array($type, $parseTypeList)) {
                $numColumn = Common::getOptionInt("number_{$type}_right_column", 'edge_member_settings');
                $typeOrder = Common::getOption("{$type}_type_order", 'edge_member_settings');
                $blockColumn = "{$blockColumnRight}_{$type}";
                if (self::parseColumnListImg($html, 0, $type, $numColumn, $typeOrder, $blockColumnRight)) {
                    $blockItem = $blockColumn;
                }
            } elseif ($type == 'profile_info') {
                $blockItem = "{$blockColumnRight}_profile_info";

                $aboutMe = $infoColumn['about'];
                if (IS_DEMO && $aboutMe) {
                    preg_match_all("/.*?[.?!](?:\s|$)/s", $aboutMe, $matches);
                    if ($matches && isset($matches[0][0])) {
                        $aboutMe = $matches[0][0];
                    }
                }

                if ($groupId || $blogId) {
                    $lKeyBirthTitle = 'edge_column_create_group';
                    $optionDate = 'group_create_full';
                    $birthIcon = 'fa-calendar';
                    $html->parse("{$blockItem}_bl_group", false);
                    $isUserBirthday = true;
                } else {
                    $lKeyBirthTitle = 'edge_column_birth_' . strtolower($infoColumn['gender']);
                    $optionDate = 'profile_birth_edge';
                    if (User::isShowAge($row)) {
                        $optionDate = 'profile_birth_full_edge';
                    }
                    $birthIcon = 'fa-birthday-cake';
                    $isUserBirthday = !User::isDisabledBirthday();
                }

                $info = array('city' => $infoColumn['city'] ? l($infoColumn['city']) : '',
                              'birth_title' => l($lKeyBirthTitle),
                              'birth' => Common::dateFormat($infoColumn['birth'], $optionDate, false, false, false, true),
                              'birth_icon' => $birthIcon,
                              'about_me' => $aboutMe);

                $html->assign($blockItem, $info);

                if ($isUserBirthday) {
                    $html->parse("{$blockItem}_birthday", false);
                }

                if (Common::isOptionActive('location_enabled', 'edge_join_page_settings')) {

                    if (!$info['city']) {
                        $html->parse("{$blockItem}_location_hide", false);
                    }
                    $html->parse("{$blockItem}_location", false);
                }

                if ($groupId || $blogId) {
                    $tags = array();
                    if ($groupId) {
                        $tags = Groups::getTagsView($groupId, false);
                    } else {
                        $blogWordCount = Blogs::getWordCount($blogId);

                        $min = round($blogWordCount/130,1);
                        $html->setvar("{$blockItem}_blog_read_time", lSetVars('read_time_value', array('min' => $min)));
                        $html->setvar("{$blockItem}_blog_word_count", $blogWordCount);
                        $html->parse("{$blockItem}_blog_word_count", false);

                        $tags = Blogs::getTagsView($blogId, false);
                    }
                    if ($tags) {
                        $html->setvar("{$blockItem}_tags", $tags);
                        $html->parse("{$blockItem}_tags", false);
                    }
                } else {
                    if (UserFields::isActive('orientation')) {
                        $html->setvar("{$blockItem}_orientation", $infoColumn['orientation']);
                        $html->parse("{$blockItem}_orientation", false);
                    }
                }

                if (!$blogId && ($groupId || UserFields::isActiveAboutMe())) {
                    if ($aboutMe) {
                        $html->parse("{$blockItem}_about_me_show", false);
                    }
                }
            } elseif ($type == 'banner') {
                $blockItem = 'right_banner';
                CBanner::getBlock($html, 'right_column');
            } elseif ($type == 'send_message' && $uid != $guid) {
                $blockItem = "{$blockColumnRight}_send_message";
            } elseif ($type == 'friend_add' && $uid != $guid) {
                $blockItem = "{$blockColumnRight}_friend_add";
                $icon = 'fa-user-times';
                $title = l('unfriend');
                $cmd = 'remove';
                $uidReqiest = 0;
                $isHideBtn = User::isFriend($uid, $guid);
                if (!$isHideBtn) {
                    $title = l('add_to_friends');
                    $icon = 'fa-user-plus';
                    $cmd = 'request';
                    $uidReqiest = User::isFriendRequestExists($uid, $guid);
                    if ($uidReqiest) {
                        $icon = 'fa-user-times';
                        $title = l('approve_request');
                        $cmd = 'approve';
                        if ($uidReqiest == $guid) {
                            $icon = 'fa-user-times';
                            $title = l('remove_request');
                            $cmd = 'remove';
                            $isHideBtn = true;
                        }
                    }
                }
                $vars = array('user_id'   => $uid,
                              'user_name' => $userNameShort,
                              'icon'      => $icon,
                              'title'     => $title,
                              'cmd'       => $cmd,
                              'param'     => $uidReqiest
                );
                $html->assign($blockItem, $vars);
                if ($isHideBtn) {
                    $html->parse("{$blockItem}_hide", false);
                }
            } elseif ($type == 'user_menu' && ($uid != $guid || $groupId || $blogId)) {
                $blockItem = '';
                $keyOptionMenu = 'member_visited_right_column_menu';
                if ($groupId) {
                    $keyOptionMenu = 'member_groups_visited_right_column_menu';
                } elseif ($blogId) {
                    $keyOptionMenu = 'blogs_visited_right_column_menu';
                }
                $isParseMenu = ListBlocksOrder::parseMenu($html, $keyOptionMenu, 'right_column_user_menu');
                if ($isParseMenu) {
                    $blockItem = "{$blockColumnRight}_user_menu";
                }
            } elseif ($type == 'custom_menu') {
                $blockItem = 'right_menu';
                CustomPage::parseMenu($html, 'right_column');
            } elseif ($type == 'profile_verification') {
                $blockItem = "{$blockColumnRight}_profile_verification";
            } elseif ($type == 'group_add') {
                $blockItem = "{$blockColumnRight}_group_add";
            } elseif ($type == 'blog_add') {
                $blockItem = "{$blockColumnRight}_blog_add";
            } elseif ($type == 'page_add') {
                $blockItem = "{$blockColumnRight}_page_add";
            } elseif ($type == 'request_subscribe_group') {
                $isSubscribers = Groups::isSubscribeUser($guid, $groupId);
                if ($isPageGroup) {
                    $btnName = $isSubscribers  ? l('menu_groups_liked_edge') : l('menu_groups_like_edge');
                    $btnIcon = $isSubscribers  ? 'fa-thumbs-up' : 'fa-thumbs-o-up';
                    $action = $isSubscribers  ? 'remove' : 'request';
                } else {
                    $action = 'request';
                    if ($isSubscribers) {
                        $btnName = l('menu_groups_unjoin_edge');
                        $btnIcon = 'fa-user-times';
                        $action = 'remove';
                    } else {
                        $subscribeRequestInfo = Groups::getSubscribeRequestInfo($guid, $groupId);
                        if ($subscribeRequestInfo && !$subscribeRequestInfo['accepted']) {
                            $btnName = l('remove_request');
                            $btnIcon = 'fa-user-times';
                            $action = 'remove_request';
                        } else {
                            $btnName = l('menu_groups_join_edge');
                            $btnIcon = 'fa-user-plus';
                        }
                    }
                }

                $vars = array('btn_action' => $action,
                              'btn_group_id' => $groupId,
                              'btn_name'   => $btnName,
                              'btn_icon'   => $btnIcon
                );

                $blockItem = "{$blockColumnRight}_like_page";
                if ($isSubscribers) {
                    $html->parse("{$blockItem}_hide", false);
                }

                $html->assign($blockItem, $vars);
            }
            if ($blockItem) {
                $html->parse($blockItem, false);
                $html->parse($blockColumnRightItem, true);
            }
        }
        if ($blocksColumnRight) {
            $html->parse($blockColumnRight, false);
        }
        /* Right */
    }

    static function indexParseBlock(&$html)
    {
        global $g;

        $mainPageBlock = 'main_page';
        $mainPageBlocks = ListBlocksOrder::getOrderItemsList('main_page_block_order');
        $blockItems = '';
        foreach($mainPageBlocks as $k => $active){
            if ($blockItems) {
                $html->clean($blockItems);
            }
            $blockItems = "{$mainPageBlock}_{$k}";
            $blockItem = "{$blockItems}_item";
            if ($k == 'list_people') {
                $blockItems = self::parseListUsersMainPage($html);
            } elseif ($k == 'list_blog_posts') {
                $blockItems = self::parseListBlogsMainPage($html);
            } elseif ($k == 'list_videos') {
                $blockItems = self::parseListVideosMainPage($html);
            } elseif ($k == 'list_songs') {
                $blockItems = self::parseListSongsMainPage($html);
            } elseif ($k == 'list_live') {
                $blockItems = self::parseListLiveMainPage($html);
            }  elseif ($k == 'list_photos') {
                $blockItems = self::parseListPhotosMainPage($html);
            }  elseif ($k == 'list_groups') {
                $blockItems = self::parseListGroupMainPage($html);
            }  elseif ($k == 'list_pages') {
                $blockItems = self::parseListGroupMainPage($html, true);

            } elseif ($k == 'log_in') {
                Social::parse($html, 'log_in_social');
            } elseif ($k == 'register_now') {
                $registerFrm = new CJoinForm('register_frm', null, false, false, true);
                $registerFrm->parseBlock($html);
            } elseif ($k == 'our_app') {
                $blockBtn = 'btn_download_app_bottom';
                $isParse = Common::parseMobileBtnDownloadApp($html, "{$blockBtn}_item");
                if ($isParse){
                    $html->parse($blockBtn, false);
                } else {
                    $isParse = Common::parseBtnDownloadApp($html, 'bottom', array('ios' => 'apple'));
                }
                if (!$isParse) {
                    $blockItems = '';
                }
            } elseif ($k == 'info_block') {
                $id = CustomPage::getIdFromAlias('social_network_info', 'not_in_menu');
                if ($id) {
                    CustomPage::parsePage($html, $id);
                } else {
                    $blockItems = '';
                }
            }
            if ($blockItems) {
                $isBrowseBtn = Common::isOptionActive("{$k}_browse_btn", 'edge_main_page_settings');
                if ($isBrowseBtn) {
                    $html->parse("{$blockItems}_browse_btn", false);
                }
                $html->parse($blockItems, false);
            }

            $html->parse("{$mainPageBlock}_items", true);
        }
        if ($mainPageBlocks) {
            $html->parse($mainPageBlock, false);
        }
    }

    static function parseListBlogsMainPage(&$html)
    {
        $numberPosts = Common::getOptionInt('list_blog_posts_number_items', 'edge_main_page_settings');
        $limit = '0,' . $numberPosts;
        $typeOrder = Common::getOption('list_blog_posts_type_order', 'edge_main_page_settings');
        return self::parseListBlogs($html, $typeOrder, $limit, 'main_page_list_blog_posts');
    }

    static function getOptionList($param)
    {
        global $p;

        $module = 'edge_general_settings';
        if ($p == 'index.php') {
            $module = 'edge_main_page_settings';
        }

        return Common::getOption($param, $module);
    }

    static function parseListBlogs(&$html, $typeOrder, $limit, $blockItems)
    {
        global $p;

        //$postDisplayType = self::getOptionList('list_blog_posts_display_type');
        //$numberRow = intval(self::getOptionList('list_blog_posts_number_row'));

        if ($p == 'index.php') {
            $postDisplayType = Common::getOption('list_blog_posts_display_type', 'edge_main_page_settings');
            $numberRow = intval(Common::getOption('list_blog_posts_number_row', 'edge_main_page_settings'));
        } else {
            $postDisplayType = Common::getOption('list_blog_posts_display_type', 'edge_general_settings');
            $numberRow = intval(Common::getOption('list_blog_posts_number_row', 'edge_general_settings'));
            $uid = User::getParamUid(0);
            $guid = guid();
            if ($uid) {
                if ($uid == $guid) {
                    $postDisplayType = Common::getOption('list_blog_my_posts_display_type', 'edge_blogs_settings');
                    $numberRow = intval(Common::getOption('list_blog_my_posts_number_row', 'edge_blogs_settings'));
                } else {
                    $postDisplayType = Common::getOption('list_blog_someones_posts_display_type', 'edge_blogs_settings');
                    $numberRow = intval(Common::getOption('list_blog_someones_posts_number_row', 'edge_blogs_settings'));
                }
            }
        }

        $rows = Blogs::getList($typeOrder, $limit);

        if ($rows) {
            $i = 1;
            foreach ($rows as $row) {
                self::parseBlogPost($html, $row, $numberRow, $postDisplayType, $i);
                $i++;
            }
            $html->parse("list_blogs_{$postDisplayType}");
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }


    static function parseBlogPost(&$html, $row, $numberRow, $postDisplayType, $i)
    {
        $blockItem = 'list_blog_posts_item';
        $blockItemType = "{$blockItem}_{$postDisplayType}";
        if ($html->blockExists($blockItemType)) {
            global $g;

            $image = explode('|', $row['images']);
            if ($image) {
                $image = CBlogsTools::getImg($row['id'], $image[0], 'bm');
            }
            if ($image) {
                $html->clean("{$blockItemType}_image_placeholder");
            } else {
                $image = $g['path']['url_files'] . 'blog_bm.png';
                //$image = $g['tmpl']['url_tmpl_main'] . 'images/blogs_placeholder.svg';
                $html->parse("{$blockItemType}_image_placeholder", false);
            }
            $uid = $row['user_id'];
            $subject = trim($row['subject']);
            if (!$subject) {
                $subject = neat_trim($row['text_short'], 55, '');
            }

            $userUrl = User::getParamUid(0) ? User::url($uid, $row['user_info']) : Common::pageUrl('user_blogs_list', $uid);
            $info = array('number_row'     => $numberRow,
                          'id'             => $row['id'],
                          'url'            => Blogs::url($row['id']),
                          'user_name'      => User::nameOneLetterFull($row['name']),
                          'user_url'       => $userUrl,
                          'user_city'      => $row['city'] ? l($row['city']) : l($row['country']),
                          'image'          => $image,
                          'subject'        => $subject,
                          'count_comments' => $row['count_comments'],
                          'text'           => $row['text_short'],
                          'time_ago'       => timeAgo($row['dt'], 'now', 'string', 60, 'second'),
                          'date'           => Common::dateFormat($row['dt'], 'list_blogs_info_plain_edge')
                    );

            $html->assign($blockItem, $info);
            if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            if ($postDisplayType == 'info') {
                $html->subcond(User::isOnline($uid), "{$blockItemType}_online");
            } elseif ($postDisplayType == 'info_big') {
                $html->subcond($i%2 == 0, "{$blockItemType}_right");
            }
            $html->parse($blockItemType);
        }
    }

    static function countPostsByUser($uid)
	{
        return DB::count('blogs_post', 'user_id=' . to_sql($uid));
    }

    static function parseListVideosMainPage(&$html)
    {
        $numberPosts = Common::getOptionInt('list_videos_number_items', 'edge_main_page_settings');
        $limit = '0,' . $numberPosts;
        $typeOrder = Common::getOption('list_videos_type_order', 'edge_main_page_settings');
        return self::parseListVideos($html, $typeOrder, $limit, 'main_page_list_videos');
    }

    static function parseListVideos(&$html, $typeOrder, $limit, $blockItems, $groupId = 0, $showAllMyVideo = false)
    {
        global $p;

        $postDisplayType = 'info';

        $rows = CProfileVideo::getVideosList($typeOrder, $limit, null, guid(), true, 0, '', $groupId, $showAllMyVideo);
        $numberRow = intval(self::getOptionList('list_videos_number_row'));
        $html->parse("list_video_{$postDisplayType}");

        if ($rows) {
            foreach ($rows as $row) {
                self::parseVideo($html, $row, $numberRow, $postDisplayType);
            }
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }


    static function parseVideo(&$html, $row, $numberRow, $postDisplayType)
    {
        $blockItem = 'list_video_item';
        $blockItemType = "{$blockItem}_{$postDisplayType}";
        if ($html->blockExists($blockItemType)) {
            global $g;
            $uidParam = User::getParamUid(0);
            $uid = $row['user_id'];
            $guid = guid();
            $info = array('number_row'     => $numberRow,
                          'id'             => $row['video_id'],
                          'video_id'       => 'v_' . $row['video_id'],
                          'user_id'        => $row['user_id'],
                          'user_name'      => User::nameOneLetterFull($row['name']),
                          'user_url'       => User::url($uid, $row['user_info']),
                          'user_city'      => $row['city'] ? l($row['city']) : l($row['country']),
                          'image'          => $g['path']['url_files'] . $row['src_src'],
                          'src'            => $g['path']['url_files'] . $row['src_v'],
                          'subject'        => $row['subject'],
                          'subject_attr'  => toAttr($row['subject']),
                          'subject_short' => neat_trim($row['subject'], 100, ''),
                          'count_comments' => $row['count_comments'],
                          'text'           => $row['description'],
                          'time_ago'       => $row['time_ago'],
                          'info'           => json_encode($row),
                          'tags'           => $row['tags_html'],
                          'hide_header'     => $row['hide_header'] ? l('picture_add_in_header') : l('picture_remove_from_header'),
                          'hide_header_icon'=> $row['hide_header'] ? 'fa-plus-square' : 'fa-minus-square'
                    );
            $html->assign($blockItem, $info);
            if (guid()) {
                $html->parse('set_video_data', false);
            }
            $html->subcond($row['tags_html'], "{$blockItemType}_tags");

            $html->subcond(CProfilePhoto::isVideoOnVerification(0, $row['visible']), "{$blockItemType}_not_checked");

            if (!$uidParam) {
                $html->subcond(User::isOnline($uid), "{$blockItemType}_online");
                $html->subcond(!$uidParam, "{$blockItemType}_name");
            }
            $html->subcond($row['subject'], "{$blockItemType}_description");

            if($uidParam == $guid && Common::isAppIos() && Common::getAppIosApiVersion() >= 48) {
                //$html->parse('app_ios_video_editor', false);
            }

            if ($uidParam && $uidParam == $guid && $row['user_id'] == $guid) {
                if (Common::isOptionActive('gallery_show_download_original', 'edge_gallery_settings')) {
                    $html->parse("{$blockItemType}_link_download", false);
                }
                $html->parse("{$blockItemType}_menu", false);
            } else {
                $html->clean("{$blockItemType}_menu");
            }

            //$html->subcond($uidParam && $uidParam == $guid && $uid == $guid, "{$blockItemType}_menu");

            if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            $html->parse($blockItemType);
        }
    }

    static function parseListUsersMainPage(&$html, $blockItems = 'main_page_list_people')
    {
        $typeOrder = Common::getOption('list_people_type_order', 'edge_main_page_settings');
        $profileDisplayType = Common::getOption('list_people_display_type', 'edge_main_page_settings');
        $numberUsers = Common::getOptionInt('list_people_number_users', 'edge_main_page_settings');
        $numberRow = Common::getOptionInt('list_people_number_row', 'edge_main_page_settings');

        $rows = User::listUsers($typeOrder, $numberUsers);
        if ($rows) {
            foreach ($rows as $row) {
                self::parseUser($html, $row, $numberRow, $profileDisplayType);
                $html->parse('users_list_item');
            }
            $html->parse("{$blockItems}_{$profileDisplayType}");
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }

    static function parseUser(&$html, $row, $numberRow, $profileDisplayType, $blockItem = 'users_list_item')
    {
        if ($html->blockExists($blockItem)) {
            global $p, $g;

            $guid = guid();
            $blockItemDisplay = "{$blockItem}_{$profileDisplayType}";
            $display = get_param('display');
            $paramsLink = array();
            if ($p == 'search_results.php' && !$display) {
                $paramsLink = array('ref' => 'people_nearby',
                                    'ref_offset' => get_param('offset', 1));
            }

            $infoProfile = '';
            $delimiter = '';

            $isGroup = false;
            if (self::$listUserGroupId && self::$listUserGroupUid && self::$listUserGroupUid == $row['user_id']) {
                $isGroup = true;
                $photoUrl = $g['path']['url_files'] . GroupsPhoto::getPhotoDefault($row['user_id'], self::$listUserGroupId, 'bm');
                $groupInfo = Groups::getInfoBasic(self::$listUserGroupId);
                $row['name'] = $groupInfo['title'];
                $aboutTitle = $groupInfo['description'];
                $urlProfile = Groups::url(self::$listUserGroupId, $groupInfo);
            } else {
                if (Common::isOptionActive('location_enabled', 'edge_join_page_settings')) {
                    $infoProfile .= $row['city'] ? l($row['city']) : '';
                    $delimiter = ', ';
                }
                if (User::isShowAge($row)) {
                    $infoProfile .= $delimiter .  $row['age'];
                }
                $photoUrl = ubmphoto($row['user_id']);
                $urlProfile = User::url($row['user_id'], $row);
            }
            $info = array('number_row'  => $numberRow,
                          'user_id'     => $row['user_id'],
                          'url_profile' => $urlProfile,
                          'url_profile_params' => http_build_query($paramsLink),
                          'name'         => User::nameOneLetterFull($row['name']),
                          //'age'        => $age,
                          //'city'       => l($row['city']),
                          'photo'        => $photoUrl,
                          'info_profile' => $infoProfile,
                          'group_id'     => 0
                    );

            if (isset($row['last_visit_date'])) {
                $row['last_visit'] = $row['last_visit_date'];
            }
            if (isset($row['group_id'])) {
                $info['group_id'] = $row['group_id'];
            }

            $isOnline = User::isOnline($row['user_id'], $row);

            if ($profileDisplayType == 'info') {
                $lastVisitTitle = l('online_now');
                if (!$isOnline) {
                    $lastVisitTitle = timeAgo($row['last_visit'], 'now', 'string', 60, 'second');
                }
                $info['last_visit'] = $lastVisitTitle;
                if (UserFields::isActiveAboutMe()) {
                    if (!$isGroup) {
                        $rowInfo = User::getInfoFull($row['user_id'], DB_MAX_INDEX);
                        $aboutTitle = $rowInfo['about_me'];
                    }
                    $info['about_title'] = neat_trim($aboutTitle,100);
                }
            }
            $html->assign($blockItem, $info);

            if ($profileDisplayType == 'info') {
                if (isset($info['about_title'])) {
                    $html->parse("{$blockItem}_about", false);
                }
            } else {
                if ($isOnline) {
                    $html->parse("{$blockItem}_online", false);
                } else {
                    $html->clean("{$blockItem}_online");
                }
            }

            $pageParseMenu = array('groups_social_block_list.php');
            if ($p == 'groups_social_subscribers.php' && isset($row['group_user_id']) && $row['group_user_id'] == guid()) {
                $pageParseMenu[] = 'groups_social_subscribers.php';
            }
            if (in_array($p, $pageParseMenu)) {
                $isParseMenu = true;
                $blockItemMenu = "{$blockItemDisplay}_menu";
                if ($p == 'groups_social_block_list.php') {
                    $html->parse("{$blockItemMenu}_groups_unblocked", false);
                } elseif ($p == 'groups_social_subscribers.php') {
                    if (Common::isOptionActive('contact_blocking')) {
                        $html->subcond($row['group_user_id'] != $row['user_id'], "{$blockItemMenu}_groups_blocked");
                    }
                    $html->setvar('menu_groups_unjoin_title', $row['page'] ? l('menu_page_remove_like_edge') : l('menu_groups_unjoin_edge'));
                    if (!$row['page']) {
                        if ($row['group_user_id'] != $row['user_id']) {
                            $html->parse("{$blockItemMenu}_groups_unjoin", false);
                        } else {
                            $isParseMenu = false;
                            $html->clean("{$blockItemMenu}_groups_unjoin");
                        }
                    }
                }
                if ($isParseMenu) {
                    $html->parse($blockItemMenu, false);
                } else {
                    $html->clean($blockItemMenu);
                }
            } elseif($p != 'user_block_list.php') {
                $blockItemChat = "{$blockItemDisplay}_chat";
                $html->subcond($guid != $row['user_id'], $blockItemChat);

                if (!isset($row['group_user_id']) || !$row['group_user_id']) {
                    $blockItemLive = "{$blockItemDisplay}_live_now";
                    $userLiveNowId = LiveStreaming::getUserLiveNowId($row['user_id']);
                    if ($userLiveNowId) {
                        $urlLive = Common::pageUrl('live_id', $row['user_id'], $userLiveNowId);
                        $html->setvar($blockItemLive . '_url', $urlLive);
                        $html->parse($blockItemLive . '_bl', false);
                        $html->parse($blockItemLive, false);
                    } else {
                        $html->clean($blockItemLive . '_bl');
                        $html->clean($blockItemLive);
                    }
                }
            }

            $html->parse($blockItemDisplay, false);
        }
    }

    static function parseListPhotosMainPage(&$html, $blockItems = 'main_page_list_photos')
    {
        $typeOrder = Common::getOption('list_photos_type_order', 'edge_main_page_settings');
        $layoutType = str_replace('layout_photos_', '', Common::getOption('list_photos_display_type', 'edge_main_page_settings'));
        $numberRow = Common::getOptionInt('list_photos_number_row', 'edge_main_page_settings');
        $numberItems = Common::getOptionInt('list_photos_number_items', 'edge_main_page_settings');
        if ($layoutType == 'small') {
            $numberItems = 4;
        }
        $limit = '0,' . $numberItems;
        $rows = CProfilePhoto::getPhotosList($typeOrder, true, $limit);
        if ($layoutType == 'small' && count($rows) < 4) {
            $layoutType = 'default';
        }
        if ($rows) {
            if ($layoutType == 'small') {
                global $g;
                $blockItem = '';
                $i = 0;
                foreach ($rows as $row) {
                    if ($blockItem) {
                        $html->clean($blockItem);
                    }
                    $info = array('photo_id'  => $row['photo_id'],
                                  'src'       => $g['path']['url_files'] . $row['src_bm'],
                                  'user_name' => User::nameOneLetterFull($row['user_name']),
                                  'user_url'  => User::url($row['user_id'])
                    );

                    $blockItem = 'list_photos_item_' . $i;
                    $html->assign($blockItem, $info);
                    $html->subcond(User::isOnline($row['user_id']), "{$blockItem}_online");

                    $i++;
                    if ($i == 4) {
                        break;
                    }
                }
                $html->parse('list_photos_item_small', false);
            } else {
                foreach ($rows as $row) {
                    self::parsePhoto($html, $row, $numberRow, $layoutType);
                }
            }
            $html->parse("list_photos_{$layoutType}", false);
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }

    static function parseListPhotos(&$html, $typeOrder, $limit, $groupId = 0, $showAllMyPhoto = false)
    {
        $layoutType = 'default';
        $rows = CProfilePhoto::getPhotosList($typeOrder, false, $limit, null, $groupId, $showAllMyPhoto);
        $numberRow = intval(self::getOptionList('list_photos_number_row'));
        if ($rows) {
            foreach ($rows as $row) {
                self::parsePhoto($html, $row, $numberRow, $layoutType);
            }
        }
        $html->parse('list_photos_default');

        return $rows;
    }

    static function parsePhoto(&$html, $row, $numberRow, $layoutType)
    {
        global $g;

        $blockItem = 'list_photos_item';
        $blockItemType = "{$blockItem}_{$layoutType}";
        if ($html->blockExists($blockItemType)) {
            global $g;
            $guid = guid();
            $uidParam = User::getParamUid(0);
            $info = array('number_row' => $numberRow,
                          'photo_id'   => $row['photo_id'],
                          'src'        => $g['path']['url_files'] . $row['src_bm'],
                          'user_id'    => $row['user_id'],
                          'user_name'  => User::nameOneLetterFull($row['user_name']),
                          'user_url'   => $row['user_url'],
                          'info'       => json_encode($row),
                          'time_ago'   => $row['time_ago'], //timeAgo($row['date'], 'now', 'string', 60, 'second'),
                          'comments_count'    => $row['comments_count'],
                          'tags'              => $row['tags_html'],
                          'description'       => $row['description'],
                          'description_attr'  => toAttr($row['description']),
                          'description_short' => neat_trim($row['description'], 100, ''),
                          'hide_header'       => $row['hide_header'] ? l('picture_add_in_header') : l('picture_remove_from_header'),
                          'hide_header_icon'  => $row['hide_header'] ? 'fa-plus-square' : 'fa-minus-square'
                    );
            $html->assign($blockItem, $info);
            if (guid()) {
                $html->parse('set_photo_data', false);
            }
            $html->subcond($row['tags_html'], "{$blockItemType}_tags");

            $html->subcond(CProfilePhoto::isPhotoOnVerification($row['visible']), "{$blockItemType}_not_checked");

            if (!$uidParam) {
                $html->subcond(User::isOnline($row['user_id']), "{$blockItemType}_online");
                $html->subcond(!$uidParam, "{$blockItemType}_name");
            }
            $html->subcond($row['description'], "{$blockItemType}_description");

            if ($uidParam == $guid && Common::isAppIos() && Common::getAppIosApiVersion() >= 48) {
                global $g_user;
                $html->setvar('app_ios_auth_key', User::urlAddAutologin('', $g_user));
                $html->parse('app_ios_image_editor', false);
            }

            $editorHideClass = '';
            if($row['gif']) {
                $editorHideClass = 'hide';
            }
            $html->setvar('editor_hide_class', $editorHideClass);

            if ($uidParam && $uidParam == $guid && $row['user_id'] == $guid) {
                if (Common::isOptionActive('gallery_show_download_original', 'edge_gallery_settings')) {
                    $html->parse("{$blockItemType}_link_download", false);
                }
                $keyDefault = $row['group_id'] ? 'default_group' : 'default';
                $html->subcond($row[$keyDefault] == 'Y', "{$blockItemType}_profile_pic");

				$html->subcond(!$row['gif'], "{$blockItemType}_editor");

                $html->parse("{$blockItemType}_menu", false);
            } else {
                $html->clean("{$blockItemType}_menu");
            }

            //$html->subcond($uidParam && $uidParam == $guid && $row['user_id'] == $guid, "{$blockItemType}_menu");

            if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            $html->parse($blockItemType);
        }
    }

    /* Groups */
    static function parseListGroupMainPage(&$html, $isPage = false)
    {
        $numberPosts = GroupsList::getOptionGroup('number_items', $isPage, 'edge_main_page_settings');
        $limit = '0,' . $numberPosts;
        $typeOrder = GroupsList::getOptionGroup('type_order', $isPage, 'edge_main_page_settings');
        $block = $isPage ? 'main_page_list_pages' : 'main_page_list_groups';

        $blockItem = $isPage ? 'list_page_item' : 'list_group_item';

        return self::parseListGroups($html, $typeOrder, $limit, $block, $isPage, $blockItem);
    }

    static function parseListGroups(&$html, $typeOrder, $limit, $blockItems, $isPage = null, $blockItem = 'list_group_item')
    {
        global $p;
        $postDisplayType = GroupsList::getDisplayType($isPage);
        $numberRow = GroupsList::getNumberRow($isPage);
        $rows = GroupsList::getListGroups($limit, $typeOrder, null, $isPage);

        if ($p == 'index.php') {
            $block = $isPage ? "list_pages_display_{$postDisplayType}" : "list_groups_display_{$postDisplayType}";
        } else {
            $block = "list_groups_{$postDisplayType}";
        }
        $html->parse($block);

        if ($rows) {
            $i = 1;
            foreach ($rows as $row) {
                self::parseGroup($html, $row, $numberRow, $postDisplayType, $i, $blockItem);
                $i++;
            }
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }


    static function parseGroup(&$html, $row, $numberRow, $postDisplayType, $i, $blockItem = 'list_group_item')
    {
        $blockItemType = "{$blockItem}_{$postDisplayType}";
        if ($html->blockExists($blockItemType)) {
            $guid = guid();
            $uidParam = User::getParamUid(0);
            $uid = $row['user_id'];
            $isPage = $row['page'];
            $info = array('number_row'     => $numberRow,
                          'id'             => $row['group_id'],
                          'page'           => $row['page'],
                          //'user_name'      => User::nameOneLetterFull($row['name']),
                          'url'            => $row['url'],
                          'user_city'      => $row['city'] ? l($row['city']) : l($row['country']),
                          'image'          => GroupsPhoto::getPhotoDefault($row['user_id'], $row['group_id'], 'b'),
                          'image_no_photo' => GroupsPhoto::getPhotoDefault($row['user_id'], $row['group_id'], 'b', true) ? '' : 'nophoto',
                          'title'          => $row['title'],
                          'title_attr'     => toAttr($row['title']),
                          'title_short'    => neat_trim($row['title'], 100, ''),
                          'description'    => $row['description'],
                          'time_ago'       => timeAgo($row['date'], 'now', 'string', 60, 'second'),
                          'date'           => Common::dateFormat($row['date'], 'list_blogs_info_plain_edge'),
                          'number_post'    => Groups::getCountPosts($row['user_id'], $row['group_id'])
                    );
            $html->assign($blockItem, $info);
            if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            if ($uidParam && $uidParam == $guid && $row['user_id'] == $guid) {
                $html->setvar("{$blockItem}_url_edit", Common::pageUrl($isPage ? 'page_edit' : 'group_edit', $row['group_id']));
                $html->parse("{$blockItemType}_menu", false);
            } else {
                $html->clean("{$blockItemType}_menu");
            }

            if ($postDisplayType == 'info_big') {
                $html->subcond($i%2 == 0, "{$blockItemType}_right");
            }
            $html->parse($blockItemType);
        }
    }
    /* Groups */

    /* Live */
    static function parseListLiveMainPage(&$html)
    {
        $numberPosts = Common::getOptionInt('list_live_number_items', 'edge_main_page_settings');
        $limit = '0,' . $numberPosts;
        $typeOrder = Common::getOption('list_live_type_order', 'edge_main_page_settings');

        $online = Common::isOptionActive('list_live_show_not_ended', 'edge_main_page_settings');
        return self::parseListLive($html, $typeOrder, $limit, 'main_page_list_live', $online);
    }

    static function parseListLive(&$html, $typeOrder, $limit, $blockItems, $online = true)
    {
        global $p;

        $postDisplayType = 'info';

        $rows = LiveStreaming::getLists($limit, $typeOrder, $online);

        $numberRow = intval(self::getOptionList('list_live_number_row'));
        $html->parse("list_live_{$postDisplayType}");

        if ($rows) {
            foreach ($rows as $row) {
                self::parseLive($html, $row, $numberRow, $postDisplayType, $online);
            }
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }


    static function parseLive(&$html, $row, $numberRow, $postDisplayType, $online = true)
    {
        $blockItem = 'list_live_item';
        $blockItemType = "{$blockItem}_{$postDisplayType}";
        if ($html->blockExists($blockItemType)) {
            global $g;
            $uidParam = User::getParamUid(0);
            $uid = $row['user_id'];
            $guid = guid();
            $info = array('number_row'     => $numberRow,
                          'id'             => $row['id'],
                          'url'            => $row['url'],
                          'user_id'        => $row['user_id'],
                          'user_name'      => User::nameOneLetterFull($row['name']),
                          'user_url'       => $row['user_url'],
                          'user_city'      => $row['city'] ? l($row['city']) : l($row['country']),

                          'count_comments' => $row['count_comments'],
                          'time_ago'       => $row['time_ago'],
                          'image'          => $g['path']['url_files'] . $row['src_bm'],
                          'tags'           => $row['tags_html'],
                          'subject'        => $row['subject'],
						  'subject_attr'   => toAttr($row['subject']),
                    );
            $html->assign($blockItem, $info);

            $html->subcond($row['subject'], "{$blockItemType}_description");
            //$html->subcond($row['tags_html'], "{$blockItemType}_tags");

            if (!$uidParam) {
                if (User::isOnline($uid) || !$online) {
                    $html->parse("{$blockItemType}_online_top", false);
                    $html->parse("{$blockItemType}_online", false);
                } else {
                    $html->clean("{$blockItemType}_online_top");
                    $html->clean("{$blockItemType}_online");
                }

                $html->subcond(!$uidParam, "{$blockItemType}_name");
            }

             if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            $html->parse($blockItemType);
        }
    }
    /* Live */

    /* Songs */
    static function parseListSongsMainPage(&$html)
    {
        $numberSongs = Common::getOptionInt('list_songs_number_items', 'edge_main_page_settings');
        $limit = '0,' . $numberSongs;
        $typeOrder = Common::getOption('list_songs_type_order', 'edge_main_page_settings');

        return self::parseListSongs($html, $typeOrder, $limit, 'main_page_list_songs');
    }

    static function parseListSongs(&$html, $typeOrder, $limit, $blockItems, $groupId = 0, $showAllMySongs = false)
    {
        global $p;

        $layoutType = 'default';

        $rows = Songs::getList($typeOrder, $limit, null, $groupId, $showAllMySongs, false);
        $numberRow = intval(self::getOptionList('list_songs_number_row'));
        $numberRow = 4;
        $html->parse("list_songs_{$layoutType}");

        if ($rows) {
            $i = 0;
            foreach ($rows as $row) {
                self::parseSong($html, $row, $numberRow, $layoutType, $i);
                $i++;
            }
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }

    static function parseSong(&$html, $row, $numberRow, $layoutType, $i)
    {
        global $g;

        $blockItem = 'list_songs_item';
        $blockItemType = "{$blockItem}_{$layoutType}";
        if ($html->blockExists($blockItemType)) {
            global $g;
            $guid = guid();
            $uidParam = User::getParamUid(0);

            $row['song_title'] = trim(strip_tags($row['song_title']));//Fix old template

            $isShortTimeAgo = Common::isOptionActiveTemplate('song_time_ago_short');
            $timeAgo = timeAgo($row['updated_at'], 'now', 'string', 60, 'second', $isShortTimeAgo);

            $info = array('number_row'  => $numberRow,
                          'song_id'     => $row['song_id'],
                          'user_id'     => $row['user_id'],

                          'time_ago'    => $timeAgo,
                          'description' => $row['song_title'],
                          'description_attr'  => toAttr($row['song_title']),
                          'description_short' => neat_trim($row['song_title'], 100, ''),

                          'image'       => Songs::getImageDefault($row['song_id']),
                          'mp3'         => toJs(Songs::getFile($row['song_id'])),
                          'mp3_index'   => $i,
                          'mp3_title'   => toJs($row['song_title']),

                          'user_name'   => User::nameOneLetterFull($row['name']),
                          'user_url'    => User::url($row['user_id']),
                    );
            $html->assign($blockItem, $info);

            if (!$uidParam) {
                $html->subcond(User::isOnline($row['user_id']), "{$blockItemType}_online");
                $html->parse("{$blockItemType}_status_online", false);
                $html->subcond(!$uidParam, "{$blockItemType}_name");
            }
            $html->subcond($row['song_title'], "{$blockItemType}_description");

            if ($uidParam && $uidParam == $guid && $row['user_id'] == $guid) {
                if (Common::isOptionActive('gallery_show_download_original', 'edge_gallery_settings')) {
                    $html->parse("{$blockItemType}_link_download", false);
                }
                $html->parse("{$blockItemType}_menu", false);
            } else {
                $html->clean("{$blockItemType}_menu");
            }

            if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            $html->parse($blockItemType);
        }
    }
    /* Songs */

    static function getNumberFriendsAndSubscribersPending($uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }

        $sql = "SELECT
                    (SELECT COUNT(*) as cnt
                       FROM `friends_requests`
                      WHERE `friend_id` = " . to_sql($uid) . "
                        AND `accepted` = 0)
                    +
                    (SELECT COUNT(*) as cnt
                       FROM `groups_social_subscribers`
                      WHERE `group_user_id` = " . to_sql($uid) . "
                        AND `group_private` = 'Y'
                        AND `accepted` = 0)
                    ";
		return DB::result($sql);
    }

    static function getFriendsPending($where = '', $order = 'DESC',  $ajax = false)
    {
        global $g;

        $guid = guid();
        $sql = "(SELECT FR.user_id, IF(true, 0, 0) AS group_id, IF(true, '', '') AS group_title, FR.created_at,
                        CU.name, CU.name_seo
                  FROM `friends_requests` AS FR
                  LEFT JOIN `user` AS CU ON CU.user_id = FR.user_id
                 WHERE FR.friend_id = " . to_sql($guid) .
                 " AND FR.accepted = 0 " . $where . ")

                UNION

               (SELECT FR.user_id, FR.group_id, GR.title AS group_title, FR.created_at,
                       CU.name, CU.name_seo
                  FROM `groups_social_subscribers` AS FR
                  LEFT JOIN `user` AS CU ON CU.user_id = FR.user_id
                  LEFT JOIN `groups_social` AS GR ON GR.group_id = FR.group_id
                 WHERE FR.group_user_id = " . to_sql($guid) .
                 " AND FR.group_private = 'Y'
                   AND FR.accepted = 0 " . $where .
                ") ORDER BY created_at {$order}";


        $result = DB::rows($sql);

        $friendsPending = array();
        foreach ($result as $key => $item) {
            $urlUserPending = User::url($item['user_id'], array('name' => $item['name'], 'name_seo' => $item['name_seo']));
            $vars = array('name' => User::nameOneLetterFull($item['name']),
                          'url'  => $urlUserPending
            );
            $lKey = 'wants_to_add_you_to_friends';
            if ($item['group_id']) {
                $vars['group_title'] = $item['group_title'];
                $lKey = 'wants_to_join_the_group_page_group_profile';
                $btnApprove = l('group_approve_join');
                $btnReject  = l('group_reject_join');
            } else {
                $btnApprove = l('add_friend');
                $btnReject  = l('ignore');
            }
            $title = Common::lSetLink($lKey, $vars);
            $friendsPending[] = array(
                'user_id'  => $item['user_id'],
                'user_id_sel'  => $item['user_id'] . ($item['group_id'] ? '_' . $item['group_id'] : ''),
                'group_id' => $item['group_id'],
                'title'    => $title,
                'created'  => $item['created_at'],
                'url'      => $urlUserPending,
                'photo'    => $g['path']['url_files'] . User::getPhotoDefault($item['user_id']),
                'btn_approve' => $btnApprove,
                'btn_reject'  => $btnReject,
            );
        }
        return $friendsPending;
    }

    static function getListFriends($uid = null, $online = false, $groupId = null, $isLiveUserCheck = false)
    {
        if ($uid === null) {
            $uid = guid();
        }

        if ($groupId === null) {
            $groupId = Groups::getParamId();
        }

        if ($groupId) {
            $isPageGroup = Groups::getInfoBasic($groupId, 'page');
            $keyL = $isPageGroup ? 'page_' : 'group_';
            if ($online) {//Not used - we have no subscribers online
                $count = Groups::getNumberSubscribersOnline($groupId);
                $maxNumberFriends = Common::getOptionInt('number_subscribers_online_right_column', 'edge_groups_settings');//Not admin settings
                $lTitle = "edge_column_{$keyL}subscribers_online_title";
            } else {
                $count = Groups::getNumberSubscribers($groupId);
                $maxNumberFriends = Common::getOptionInt('number_subscribers_right_column', 'edge_groups_settings');
                $lTitle = "edge_column_{$keyL}subscribers_title";
            }
            if ($online) {
                $list = array();
            } else {
                $list = Groups::getListSubscribers($groupId, $online, $maxNumberFriends, true);
            }
        } else {
            if ($online) {
                $count = User::getNumberFriendsOnline($uid);
                $maxNumberFriends = Common::getOptionInt('number_friends_online_right_column', 'edge_member_settings');
                $lTitle = 'edge_column_friends_online_title';
            } else {
                $count = User::getNumberFriends($uid);
                $maxNumberFriends = Common::getOptionInt('number_friends_right_column', 'edge_member_settings');
                $lTitle = 'edge_column_friends_title';
                if (guid() != $uid) {
                    $lTitle .= '_other_user';
                }
            }
            $list = User::getListFriends($uid, $online, $maxNumberFriends, true, $isLiveUserCheck);
        }
        $countList = count($list);
        if ($countList < $maxNumberFriends && $count > $countList) {
            $count = $countList;
        }
        $result = array('count' => $count,
                        'count_title' => lSetVars($lTitle, array('count' => $count)),
                        'max_number' => $maxNumberFriends,
                        'list' => $list);
        return $result;
    }

    static function parseNavbarMenuShort(&$html, $block)
    {
        global $g;

        if (!$html->blockExists($block)) {
            return;
        }

        $key = 'member_header_menu_short';
        $groupId = 0;
        $groupIdEvent = 0;
        $isMyGroup = Groups::isMyGroup();
        if ($isMyGroup) {
            $groupId = Groups::getParamId();
            $groupIdEvent = $groupId;
            $key = 'member_groups_header_menu_short';
        }

        $orderItemsList = ListBlocksOrder::getOrderItemsList($key);
        if (!$orderItemsList) {
            return;
        }

        $guid = guid();
        $blockItem = "{$block}_item";
        $blockItemClean = '';
        foreach($orderItemsList as $k => $v){
            if ($blockItemClean) {
                $html->clean($blockItemClean);
            }
            $blockItemClean = "{$blockItem}_{$k}";
            $count = 0;
            $disabled = 0;
            if ($k == 'friends_pending') {
                if (!Common::isOptionActive('friends_enabled')) {
                    continue;
                }
                $count = self::getNumberFriendsAndSubscribersPending();
                $disabled = $count;
                $blockFriendsPending = 'notif_friends_pending';
                if ($count) {
                    $friendsPending = self::getFriendsPending();
                    $blockFriendsPendingItem = "{$blockFriendsPending}_item";
                    foreach ($friendsPending as $key => $item) {
                        $html->assign($blockFriendsPendingItem, $item);
                        $html->parse($blockFriendsPendingItem, true);
                    }
                }
                $html->parse($blockFriendsPending, false);
            } elseif ($k == 'subscribers_pending') {
                $count = Groups::getNumberRequestsToSubscribePending($groupId);
                $disabled = $count;
                $blockFriendsPending = 'notif_friends_pending';
                if ($count) {
                    $friendsPending = Groups::getSubscribePending($groupId);
                    $blockFriendsPendingItem = "{$blockFriendsPending}_item";
                    foreach ($friendsPending as $key => $item) {
                        $html->assign($blockFriendsPendingItem, $item);
                        $html->parse($blockFriendsPendingItem, true);
                    }
                }
                $html->parse($blockFriendsPending, false);
            } elseif ($k == 'new_message') {
                $count = CIm::getCountNewMessages();
                $disabled = CIm::getCountAllMsgIm();
            } elseif ($k == 'new_tasks') {
                $html->setvar("{$k}_url", Common::pageUrl('user_calendar', $guid));
                $count = TaskCalendar::getCountOpenTasksByCurrentDay($guid);
                $newTasksTitle = TaskCalendar::getNotifTitle($count);
                $html->setvar('new_tasks_title', toAttr($newTasksTitle));
                $disabled = $count;
            } elseif ($k == 'new_events') {
                $blockEvents = 'notif_events';
                $blockEventsItem = "{$blockEvents}_item";

                $count = 0;

                $events = User::getListGlobalEvents(null, 'DESC', $groupIdEvent);
                $eventsCount = count($events);
                $eventsCountAll = 0;
                if ($eventsCount) {
                    $eventsCountAll = User::getNumberGlobalEvents(true, $groupIdEvent);
                    $count = User::getNumberGlobalEvents(false, $groupIdEvent);
                }
                $disabled = $eventsCount;
                $rank = 0;
                foreach ($events as $key => $item) {
                    $html->assign($blockEventsItem, $item);
                    $html->subcond($item['new'], "{$blockEventsItem}_new");
                    //$html->subcond($item['new'], "{$blockEventsItem}_menu_mark_see");
                    $html->parse($blockEventsItem, true);

                    $rank = $item['rank'];
                }
                $html->setvar("{$blockEvents}_rank", $rank);
                if ($eventsCountAll == $eventsCount) {
                    $html->parse("{$blockEvents}_hide_more", false);
                }
                $html->parse($blockEvents, false);
            }

            $html->setvar("{$k}_count", intval($count));
            $html->subcond($count, "{$blockItemClean}_count_show");
            $html->subcond(!$disabled, "{$blockItemClean}_disabled");

            $html->parse($blockItemClean, false);
            $html->parse($blockItem, true);
        }
        $html->parse($block, true);
    }

    static function updateListFriends($uid = null)
    {
        if ($uid === null) {
            $uid = guid();
        }
        $script = '<script>';
        $script .= 'clFriends.updateFriends(' . json_encode(self::getListFriends(null, true)) . ',true);';
        $script .= 'clFriends.updateFriends(' . json_encode(self::getListFriends($uid)) . ');';
        $script .= '</script>';
        return $script;
    }

    static function getOptionsSettingsKey($groupId)
    {
        $optionsKey = 'edge_member_settings';
        if ($groupId) {
            $optionsKey = 'edge_groups_settings';
        }
        return $optionsKey;
    }
}