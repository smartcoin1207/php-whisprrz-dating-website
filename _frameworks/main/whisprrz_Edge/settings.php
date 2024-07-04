<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
global $g;
//Rade 2023-10-12 add start
Common::setOptionRuntime('Y', 'main_users');
Common::setOptionRuntime('N', 'status_relation');
// $g['user_var']['relation']['status'] = 'inactive';
//Rade 2023-10-12 add end

$listPeopleNumberUsers = Common::getOptionInt('list_people_number_users', 'edge_general_settings');

$g['template_options'] = array(
    'set' => 'urban',
    'name' => 'edge',
    'include_template_class' => 'Y',
    '3d_chat_custom_css' => 'Y', 	//Rade 2023-10-12 add
    'type_payment_features' => 'edge',
    'type_payment_plan' => 'edge',
    'type_payment_module_credits' => 'payment_module_boost',
    // Rade 2023-10-01 add start
    'hide_site_sections' => array(
        'main_search',
        'main_page_header_mode',
    ),
    'new_videos_on_main_pag' => 'Y',
    'featured_users_on_main_page' => 'Y',
    'main_col_order' => 'Y',
    'right_col_order' => 'Y',
//Rade 2023-10-12 add start
    'events_quests_per_page' => 8,
    'groups_members_per_page' => 8,
    'im_msg_layout' => 'oryx',
//Rade 2023-10-12 add end
    'header_color_admin' => 'Y',
    //'type_payment_free' => 'Y',

    // rade 2023-09-20 delete start
    // 'no_mobile_template' => 'Y',
    // rade 2023-09-20 delete end

    'logo_w' => 207, 	//Rade 2023-10-12 update
    'logo_h' => 47, 	//Rade 2023-10-12 update
    'logo_inner' => 'Y',
    'logo_inner_w' => 195,
    'logo_inner_h' => 24,
    'logo_footer' => 'Y',
    'logo_footer_w' => 210,
    'logo_footer_h' => 26,
    'icon_pwa' => 'Y',

    // Rade 2023-10-01 add start
    'website_background' => 'Y',
    'website_background_default' => '77.jpg',
    'website_background_compression_ratio' => 'Y',
    'upload_image_main_page' => 'Y',
    'upload_big_banner_main_page' => 'N',
    'upload_small_banner_main_page' => 'N',
    'top_five_button' => 'Y',
    'network' => 'Y',
    'stats' => 'N',
    'header_block_info' => 'Y',
    'color_scheme_settings' => 'Y',
    'color_scheme' => array(
        'default_header_color' => array('title' => l('default header color'), 'upper' => '#5a9dd2', 'lower' => '#185f92'),
        'blue_whisp' => array('title' => 'Blue whisp', 'upper' => '#5a9dd2', 'lower' => '#185f92'),
        'purple_whisprrz' => array('title' => 'Purple Whisprrz', 'upper' => '#7935df', 'lower' => '#2e56ac'),
        'Pink_Passion' => array('title' => 'Pink Passion', 'upper' => '#d0328d', 'lower' => '#8e045a'),
        'green_water' => array('title' => 'Green Water', 'upper' => '#63c7b5', 'lower' => '#2b837b'),
        'russian_red' => array('title' => 'Russian Red', 'upper' => '#f23131', 'lower' => '#850000'),
        'autumn_woods' => array('title' => 'Autumn Woods', 'upper' => '#bf8f00', 'lower' => '#835700'),
        'fresh_morning' => array('title' => 'Fresh Morning', 'upper' => '#40c7db', 'lower' => '#1972a3'),
        'royal' => array('title' => 'Royal', 'upper' => '#bf0000', 'lower' => '#000000'),
        'business' => array('title' => 'Business', 'upper' => '#f23a3a', 'lower' => '#606060'),
        'natural' => array('title' => 'Natural', 'upper' => '#a2d45a', 'lower' => '#a37505'),
        'bubble_gum' => array('title' => 'Bubble Gum', 'upper' => '#e3368a', 'lower' => '#007e62'),
        'eric_choice' => array('title' => 'Eric Choice', 'upper' => '#696cc5', 'lower' => '#2e3076'),
        'mint_candy' => array('title' => 'Mint Candy', 'upper' => '#63caa6', 'lower' => '#1d7b89'),
        'pink' => array('title' => 'Pink', 'upper' => '#ee8bcd', 'lower' => '#ae036d'),
        'vanilla' => array('title' => 'Vanilla', 'upper' => '#ee8bcd', 'lower' => '#00b27b'),
        'bread_and_butter' => array('title' => 'Bread and Butter', 'upper' => '#e3be00', 'lower' => '#ab8700'),
        'strict' => array('title' => 'Strict', 'upper' => '#6ADB90', 'lower' => '#606060'),
        'navy' => array('title' => 'Navy', 'upper' => '#5DC5DB', 'lower' => '#606060'),
        'tenderness' => array('title' => 'Tenderness', 'upper' => '#D84882', 'lower' => '#606060'),
        'green_and_gray' => array('title' => 'Green and Gray', 'upper' => '#A1C348', 'lower' => '#606060'),
        'african_safari' => array('title' => 'African Safari', 'upper' => '#E3B400', 'lower' => '#606060'),
        'french_evening' => array('title' => 'French Evening', 'upper' => '#8A73C5', 'lower' => '#606060'),
        'sea_morning' => array('title' => 'Sea Morning', 'upper' => '#47A8C3', 'lower' => '#5C5C5C'),
        'freshness' => array('title' => 'Freshness', 'upper' => '#42B262', 'lower' => '#5C5C5C'),
        "elenas_tears" => array('title' => "Elena's Tears", 'upper' => '#31B0B6', 'lower' => '#5C5C5C'),
        'grayscale' => array('title' => 'Grayscale', 'upper' => '#ABABAB', 'lower' => '#5C5C5C'),
        'rainy_day' => array('title' => 'Rainy Day', 'upper' => '#79B0C1', 'lower' => '#5C5C5C'),
        'clean_fun' => array('title' => 'Clean Fun', 'upper' => '#4CB1D0', 'lower' => '#7C7C7C'),
        'modest_green' => array('title' => 'Modest Green', 'upper' => '#8FB662', 'lower' => '#7C7C7C'),

    ),

    'captcha_settings_custom' => 'Y',
    'width_captcha' => 165,
    'height_captcha' => 42,
    'ratio_font_captcha' => 1.5,
    'ratio_distance_captcha' => 2,

    'menu_admin_banner' => 'Y',
    'banners_places' => array(
        'home' => 1, 	// Rade 2023-10-12 update
        'top' => 1, 	// Rade 2023-10-12 update
        'header' => 1, 	// Rade 2023-10-12 update
        'right_column' => 1,
        'left_column' => 0, // Rade 2023-10-12 update
        'footer' => 1,
        'footer_mobile' => 0,
        'footer_additional' => 1 // Rade 2023-10-12 update
    ),
    'number_banners_place_right_column' => 1,
    'number_banners_place_right_column_paid' => 1,
    'number_banners_place_left_column' => 1,
    'number_banners_place_left_column_paid' => 1,

    /* Templates */
    'custom_template_pages' => 'Y',

    'page_no_columns_template' => array(
        'pp_gallery_template',
        'wall_items',
        'wall_load_comments',
        'wall_ajax',
        'blogs_post_comment_template',
        'blogs_post_comment_replies_template'
    ),

    'header_template' => array(
        'main' => $g['tmpl']['dir_tmpl_main'] . '_header.html',
        'header_custom' => $g['tmpl']['dir_tmpl_main'] . '_header_custom.html',
        'pp_messages' => $g['tmpl']['dir_tmpl_main'] . 'pp_messages.html'
    ),

    'footer_template' => array(
        'main' => $g['tmpl']['dir_tmpl_main'] . '_footer.html',
        'footer_custom' => $g['tmpl']['dir_tmpl_main'] . '_footer_custom.html',
        'pp_galley_comment_item' => $g['tmpl']['dir_tmpl_main'] . '_pp_gallery_item.html'
    ),

    'index_page_template' => array(
        'main' => 'index.html',
        'login_frm_index' => '_login.html',
        'register_frm_index' => '_register.html',
        'our_app_index' => '_our_app.html',
        'info_block_index' => '_info_index.html',
        'list_people_item' => '_list_users_info_item.html',
        'list_blog_posts_item' => '_list_blogs_item.html',
        'list_videos_item' => '_list_vids_item.html',
        'list_songs_item' => '_list_songs_item.html',
        'list_live_item' => '_list_live_item.html',
        'list_photos_item' => '_list_photos_item.html',
        'list_pages_item' => '_list_pages_main_page_item.html',
        'list_groups_item' => '_list_groups_item.html'
    ),

    'login_page_template' => array(
        'main' => 'login.html',
        'login_frm' => '_login.html'
    ),

    'register_page_template' => array(
        'main' => 'register.html',
        'register_frm' => '_register.html'
    ),

    /* Columns template */
    'pages_one_column' => array('city.php'),
    'pages_parse_block_one_column' => array(
        'profile_settings.php',
        'group_add.php',
        'blogs_add.php',
        'events_event_edit.php'
    ),

    'columns_template' => array(
        'profile_column_left' => '_profile_column_left.html',
        'profile_column_right' => '_profile_column_right.html'
    ),

    'custom_page_template' => array('main' => 'page.html'),


    'profile_settings_template' => array('main' => 'profile_settings.html'),
    'profile_settings_template_columns' => array('main' => 'profile_settings.html'),

    'games_template' => array('main' => 'games.html'),
    'calendar_template' => array('main' => 'calendar.html'),
    'options_main_title' => 'Y', 	//Rade 2023-10-12 add
    'wall_template' => array(
        'main' => 'wall.html',
        'wall_custom' => '_wall_custom.html',
        'wall_content' => '_wall_content.html',
        'wall_comments' => '_wall_items_comments.html',
        'wall_comments_replies' => '_wall_items_comments_replies.html'
    ),

    'wall_template_no_access_group' => array(
        'main' => 'wall.html',
        'wall_content' => '_wall_content_no_access_group.html'
    ),

    'wall_items' => array(
        'main' => '_wall_items.html',
        'wall_comments' => '_wall_items_comments.html',
        'wall_comments_replies' => '_wall_items_comments_replies.html'
    ),

    'wall_load_comments' => array(
        'main' => '_wall_items_comments.html',
        'wall_comments_replies' => '_wall_items_comments_replies.html'
    ),

    'wall_ajax' => array(
        'main' => 'wall_ajax.html',
        'wall_comments' => '_wall_items_comments.html',
        'wall_comments_replies' => '_wall_items_comments_replies.html'
    ),

    'my_friends_template' => array(
        'main' => 'page_list.html',
        'list' => '_list_page_info.html',
        'items' => '_list_page_users_items.html',
        'item' => '_list_users_info_item.html',
        'pages' => '_my_friends_pages.html'
    ),
    'my_friends_template_columns' => array('list' => '_list_page_info_columns.html'),

    'groups_subscribers_template' => array(
        'main' => 'page_list.html',
        'list' => '_list_page_info.html',
        'items' => '_list_page_users_items.html',
        'item' => '_list_users_info_item.html',
        'pages' => '_list_page_pages.html'
    ),
    'groups_subscribers_template_columns' => array('list' => '_list_page_info_columns.html'),


    'groups_block_list_template' => array(
        'main' => 'page_list.html',
        'list' => '_list_page_info.html',
        'items' => '_list_page_users_items.html',
        'item' => '_list_users_info_item.html',
        'pages' => '_list_page_pages.html'
    ),
    'groups_block_list_template_columns' => array('list' => '_list_page_info_columns.html'),

    'videochat_template' => array(
        'main' => 'videochat.html',
        'videochat' => '_videochat.html'
    ),
    'audiochat_template' => array(
        'main' => 'audiochat.html',
        'audiochat' => '_audiochat.html'
    ),
    'user_list_template' => 'users_list_base.html',

    'pp_gallery_template' => array(
        'main' => '_pp_gallery_items.html',
        'comment_item' => '_pp_gallery_item.html'
    ),
    'pp_gallery_comment_template' => '_pp_gallery_item.html',

    'moderator_template' => array(
        'main' => 'moderator.html',
        'content' => '_moderator_items.html'
    ),

    'search_results_list_page' => 'search_results.html',
    'search_results_list' => array(
        'main' => '_list_users_info.html',
        'items' => '_list_users_info_items.html',
        'item' => '_list_users_info_item.html',
        'users_filter' => '_list_users_filter_empty.html',
        'pages' => '_list_users_info_pages.html'
    ),

    'group_add_template' => array('main' => 'group_add.html'),
    'group_add_template_columns' => array('main' => 'group_add.html'),

    'upgrade_template' => array(
        'main' => 'upgrade.html',
        'upgrade' => '_upgrade.html'
    ),

    'events_event_edit_template' => array('main' => 'events_event_edit.html'),
    'events_event_edit_template_columns' => array('main' => 'events_event_edit.html'),

    'blogs_add_template' => array('main' => 'blogs_add.html'),
    'blogs_add_template_columns' => array('main' => 'blogs_add.html'),

    'blogs_post_template' => array(
        'main' => 'blogs_post.html',
        'blogs_post' => '_blogs_post.html',
        'comment_item' => '_blogs_post_comment_item.html'
    ),
    'blogs_post_comment_template' => array(
        'main' => '_blogs_post.html',
        'comment_item' => '_blogs_post_comment_item.html'
    ),
    'blogs_post_comment_replies_template' => array('main' => '_blogs_post_comment_item.html'),


    'live_streaming_template1' => array(
        'main' => 'live_streaming.html',
        'live_streaming' => '_live_streaming.html',
        'content' => '_live_streaming_content.html',
        'likes' => '_live_streaming_likes.html',
        'comments_list' => '_live_streaming_comment_item.html'
    ),

    'live_streaming_template' => array(
        'main' => 'live_streaming.html',
        'live_streaming' => '_live_streaming.html'
    ),
    /* Templates */

    'list_users_info_ajax' => 'Y',
    'list_users_info_tmpl_parts' => 'Y',
    'list_users_info_tmpl_parts_item' => 'Y',

    'not_display_module' => array(
        'index_animated',
        'wall_custom_head',
        'wall_custom_content',
        'profile_colum_narrow',
        // 'complite',
        // 'profile_menu',
        'profile_head',
        'profile_charts',
        'profile_photos_init',
        // 'search',
        // 'users_list_header_buttons',
        'custom_page',
        'custom_head',
        'friends_menu',
        'people_nearby_spotlight',
        // 'events_header',
        // 'events_custom_head',
        // 'events_sidebar'
    ),
    'display_module' => array('profile_column', 'profile_settings_with_editor_main'),

    'list_users_filter' => 'Y',
    'list_users_filter_head_hide' => 'Y',
    'list_users_filter_social' => 'N',

    'template_edit_settings' => 'Y',
    'template_edit_settings_type' => 'edge',
    'profile_photo_main_size' => 'bm',
    'no_photo_by_template' => 'Y',
    'private_photo_by_template' => 'Y',

    'usersinfo_per_page' => 'number_of_profiles_in_the_search_results',

    'content_popup_on_page' => 'N',

    'main_page_image_default' => '1.jpg',

    'usersinfo_pages_per_list' => 5,
    'user_custom_per_page' => $listPeopleNumberUsers,

    'custom_pages_sections' => array('left_column', 'right_column'),

    'captcha_contact_only_visitor' => 'Y',

    'im_type' => 'edge',
    'im_no_system_msg' => 'Y',
    //only "welcoming_message"

    'wall_type' => 'edge',
    'wall_sections_only' => array('comment', 'photo', 'vids', 'pics', 'group_social_created', 'photo_default', 'blog_post', 'music', 'event_added', 'event_photo', 'hotdate_added', 'hotdate_photo', 'partyhou_added', 'partyhou_photo','event_member', 'hotdate_member', 'partyhou_member'),
    'wall_mode' => 'all',
    'wall_video_item_without_styles' => 'Y',
    'wall_parse_likes' => 'Y',
    'wall_parse_comments' => 'Y',
    'wall_parse_comments_likes' => 'Y',
    'wall_parse_comments_replies' => 'Y',
    'wall_number_comments_to_show_bottom_frm' => 2,
    'wall_friend_allow_except_posting' => 'Y',

    'hide_profile_settings' => array(
        'set_can_comment_photos',
        // 'color_scheme',
        // 'albums_to_see',
        // 'default_online_view',
        // 'autologin',
        // 'smart_profile',
        // 'framework_version',
        // 'set_email_mail',
        'set_notif_want_to_meet_you',
        'set_notif_mutual_attraction',
        'set_notif_gifts',
        'set_notif_voted_photos',
        // 'set_email_interest'
    ),
    'show_profile_settings' => array('set_notif_show_my_age'),
    'group_profile_settings' => array('sound' => 2),

    'access_check_to_profile' => 'Y',
    'redirect_user_blocked' => 'N',
    'allow_you_to_view_blocked_profile' => 'Y',
    'redirect_from_profile_to_home_page' => 'Y',

    'no_private_photos' => 'N',
    'no_rating_photos' => 'Y',
    'options_main_text' => 'Y', // Rade 2023-10-12 add
    'gallery_type' => 'edge',
    'gallery_comment_time_ago' => 'Y',
    'gallery_comment_replies' => 'Y',
    'gallery_comment_like' => 'Y',
    'gallery_comment_parse_media' => 'Y',
    'gallery_tags' => 'Y',
    'gallery_preload_data' => 30,
    'gallery_number_comments_to_show_bottom_frm' => 2,

    'smooth_scroll' => 'Y',

    'profile_edit_main_location' => 'Y',

    'get_text_tags_to_br_no_parse_br' => 'Y',

    'do_not_show_me_in_search' => Common::isOptionActive('show_your_profile_from_search', 'edge_member_settings') ? 'N' : 'Y',
    'only_webrtc_mediachat' => 'Y',

    /* Copy from Impact
     * + Add
     * 349 - Age range
     * 454 - I'm here to
     * 456 - Interested in
     * 460 - Location
     */
    'fields_social' => 'N',
    // 'fields_not_availableOLD' => array(133, 134, 136, 137, 139, 140, 142, 144, 145, 146, 148, 149, 150, 152, 154, 155, 156, 157, 158, 160, 162, 163, 164, 165, 166, 167, 168, 169, 170, 348, 468, 471),
    // 'fields_not_available_admin' => array(133, 134, 136, 137, 139, 140, 142, 144, 145, 146, 148, 149, 150, 152, 154, 156, 157, 158, 160, 162, 163, 164, 165, 166, 167, 168, 169, 170, 348, 468, 471, 485, 494), // Rade 2023-10-11 delete
    // 'fields_not_available_admin' => array(473, 136, 137, 139, 140, 142, 144, 146, 148, 149, 150, 152, 154, 156, 157, 158, 160, 162, 167, 170, 348, 468, 471, 485, 494), // Rade 2023-10-11 delete
    'fields_not_available_admin' => array(), 
    // popcorn 2023-11-24 modified 
    'fields_not_available' => array(541, 457, 460),
    // 'fields_mode' => 'urban',

    'join_location_allow_disabled' => 'Y',
    'no_br_error' => 'Y',
    'redirect_if_single_only_member' => 'Y',
    'video_poster_app' => 'Y',

    'comments_replies' => 'Y',
    'page_redirect_min_photos_to_use_site' => 'user_photos_list',

    'is_prepare_url_auto_mail' => 'Y',

    'forgot_password_redirect_login' => 'Y',
    'groups_social_enabled' => 'Y',
    'event_social_enabled' => 'Y',
    'hotdate_social_enabled' => 'Y',
    'partyhou_social_enabled' => 'Y',


    'blogs_social_enabled' => 'Y',
    'blogs_post_number_comments_to_show_bottom_frm' => 2,

    'photo_time_ago_short' => 'Y',
    'song_time_ago_short' => 'Y',


    'im_send_image' => 'Y',
    'im_send_image_width' => 'Y',
    'im_send_image_data_params' => 'Y',
    'get_text_tags_to_br_no_parse_br' => 'Y',
    'verified_account_title_list' => 'Y',

    'feature_super_powers_allowed' => array('message_read_receipts'),
    'upgraded_redirect_to_home_page' => 'Y',

    'join_birthday_disabled' => 'Y',

    'live_enabled' => 'Y',

    'custom_profile_html' => 'profile_html',
    'live_list_filter_disabled' => 'Y', // Rade 2023-10-12 add
    'near_me_radius' => 25,
    'list_live_number_items' => 8, 	//Rade 2023-10-12 add
);

// Rade 2023-10-12 add start
global $swf;

$swf['profile']['attributes']['width'] = '547';
$swf['profile']['attributes']['height'] = '864';
$swf['profile']['attributes']['bgcolor'] = 'FFFFFF';
$swf['profile']['flashvars']['colorbgeditor'] = '0xFFFFFF';
$swf['profile']['flashvars']['colorbgabout'] = '0xFFFFFF';
$swf['profile']['flashvars']['colorbgfontabout'] = '0xFFFFFF';
$swf['profile']['flashvars']['colorheaderabout'] = '0x000000';
$swf['profile']['flashvars']['colorfontabout'] = '0x000000';
$swf['profile']['flashvars']['colorbgmenu1'] = '0xF0F0F0';
$swf['profile']['flashvars']['colorbgmenu2'] = '0x989898';
$swf['profile']['flashvars']['colorcontourmenu'] = '0x6A6A6A';
$swf['profile']['flashvars']['editborder'] = '0xFAFAFA';
$swf['profile']['flashvars']['bgtabmenu'] = '0x6f6f6f';
$swf['profile']['flashvars']['tabmenubtn'] = '0xffffff';
$swf['profile']['flashvars']['bgcanvas'] = '0xFAFAFA';
$swf['profile']['flashvars']['colorcontourmenu'] = '0x666666';
$swf['profile']['flashvars']['colormskforbg'] = '0xFFFFFF';
$swf['profile']['flashvars']['bgColorPreloader'] = '0xFFFFFF';

$swf['flashchat']['attributes']['width'] = '746';
$swf['flashchat']['attributes']['height'] = '500';
$swf['flashchat']['attributes']['bgcolor'] = 'FFFFFF';
$color = str_replace('#', '0x', get_session('color_lower'));
$swf['flashchat']['flashvars']['btnRoomsColor'] = $color;
$swf['flashchat']['flashvars']['usersColor'] = $color;
$swf['flashchat']['flashvars']['bgColor'] = '0xFFFFFF';

$swf['games']['attributes']['bgcolor'] = 'FAFAFA';
$swf['games']['flashvars']['bgColor'] = '0xFAFAFA';

global $gc;
$gc = true;
// Rade 2023-10-12 add end
// Common::setOptionRuntime('N', 'mobile_enabled');
// Common::setOptionRuntime('N', 'mobile_redirect');
// Common::setOptionRuntime('N', 'mobile_site_on_tablet');
// Common::setOptionRuntime('N', 'only_apps_active');
// Common::setOptionRuntime('N', 'your_orientation');
//Common::setOptionRuntime('Y', 'free_site');
//Common::setOptionRuntime('N', 'access_paying');
//Common::setOptionRuntime('N', 'site_paid_features');


$optionsRuntime = array(
    'wall_enabled' => 'Y',
    // 'forum' => 'N',
    // 'gallery' => 'Y',
    // 'music' => 'Y',
    // 'places' => 'N',
    // 'events' => 'N',
    // 'groups' => 'N',
    'groups_social' => 'Y',
    // 'only_friends_wall_posts' => 'N',
    // 'partner_settings' => 'N',
    // 'videogallery' => 'Y'
);
if (Common::isMobile(false, true, true)) {
    $optionsRuntime['video_player_type'] = 'player_native';
}

foreach ($optionsRuntime as $option => $value) {
    Common::setOptionRuntime($value, $option);
}

if (PWA::isModePwaIos()) {
    Common::setOptionRuntime('N', 'videochat');
    Common::setOptionRuntime('N', 'audiochat');
}

if (User::isDisabledBirthday()) {
    Common::setOptionRuntime('N', 'show_age_profile', 'edge_member_settings');
}
//'upload_limit_photo_count'

Common::setOptionRuntime($listPeopleNumberUsers, 'number_of_profiles_in_the_search_results');

if (IS_DEMO) {
    Demo::setEdgeSettings();
}