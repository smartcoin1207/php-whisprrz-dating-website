<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include(__DIR__ . '/starter.php');

$g['path']['url_main'] = '../';
$sitePart = 'administration';
include(__DIR__ . '/start.php');

if (get_session('admin_auth') == 'Y') {
	$area = 'login';
	if ($p == 'index.php' and get_param('cmd') != 'logout') {
        Common::toHomePage();//redirect('home.php');
    }
} elseif (get_param('cmd') == 'login_token') {
    $token = get_param('token');
    $tokenAdmin = Config::getOptionsAll('token_admin', 'option');
    $isToken = false;
    foreach ($tokenAdmin as $key => $value) {
        if ($key == $token) {
            $isToken = true;
            break;
        }
    }
    if ($isToken) {
        Config::remove('token_admin');
        set_session('admin_auth', 'Y');
        Common::toHomePage();
    } else {
        Common::toLoginPage();
    }
}elseif(get_session('replier_auth') == 'Y'){
	$area = 'login';
	if ($p == 'index.php' and get_param('cmd') != 'logout') {
        //Common::toHomePage();//redirect('home.php');
        redirect("fakes_reply_mails.php");
    }
	$pages_zone = array('index.php', 'fakes_reply_mails.php','fakes_reply_im.php','fakes_reply_winks.php', 'fakes_friend_requests.php');
}

 else {
	$area = 'public';
	$pages_zone = array('index.php', 'forget_password.php', 'js.php');
}

if(isset($pages_zone)){
	$access = 'N';
	foreach ($pages_zone as $k => $v) {
        if ($p == $v) {
            $access = 'Y';
        }
    }
	if ($access == 'N') {
        Common::toLoginPage();//redirect('index.php');
    }
}

$tmplParam = get_param('tmpl');
if ($p == 'template_settings.php' && $tmplParam) {
    $g['tmpl']['main'] = $tmplParam;
    $g['tmpl']['mobile'] = "{$tmplParam}_mobile";
    $g['tmpl']['dir_tmpl_main'] = "{$g['path']['dir_tmpl']}main/{$tmplParam}/";
    $g['tmpl']['url_tmpl_main'] = "{$g['path']['url_tmpl']}main/{$tmplParam}/";
}

$g['template_options'] = loadTemplateSettings('main', Common::getOption('main', 'tmpl'));
$g['template_options_mobile'] = loadTemplateSettings('mobile', Common::getOption('mobile', 'tmpl'));


//popcorn changed 9/29/2023 start
if(get_session('admin_auth') == "Y") {
$name = get_session('manager_name');

$sql = "SELECT * FROM add_manager WHERE name='$name'";
DB::query($sql);
$data = DB::fetch_row();
$g['tmpl']['admin_access_role'] = $data;

        if($name != "admin"){
            $admin_urls = array(
                "Main" => array(
                    "home_.php", 
                    "options.php", "site_options.php", "image.php", "menu.php", "quick_search_order.php", "profile_col_narrow.php", "user_menu_order.php", "smtp.php", "cache.php", "date_options.php",
                    "stats.php",
                    "pay.php", "pay_order.php", "pay_plans.php", "pay_cat.php", "pay_type.php", "pay_trial.php", 
                    "donation_admin_cobra.php",
                    "pages_add_club.php",
                    "pages_add_menu.php"
                    ),
                "Frameworks" => array(
                    "template.php", "template_edit.php"
                ),
                "Languages" => array(
                    "language.php", "language_edit.php"
                ), 
                "Site_news" => array(
                    "help_topic.php", "news.php", "popup_pages.php", "posting_popup.php"
                ), 
                "Users" => array(
                    "users_results.php", "users_approval.php", "users_search.php", "users_photo.php", "users_video.php", "users_text.php", "users_events.php", "users_hotdates.php", "users_partyhouz.php", "users_craigs.php", "users_wowslider.php", "users_filter.php", "users_reports.php",
                    "users_fields", "users_fields_add.php", "users_fields_countries.php", "users_fields_states.php", "users_fields_cities.php", "users_fields_add_nickname.php",
                    "automail.php", "massmail.php", "massmail_edit.php", "matchmail.php", "contact.php",
                    "ipblock.php", "ban_users.php", "users_reports.php", "fakes_reply_mail.php", "fakes_reply_im.php",
                    "fakes_reply_winks.php", "fakes_friend_requests.php", "fakes_reply_replier.php"
                ),
                "Modules" => array(
                    "blogs_bloggers.php", "blogs_posts.php", "blogs_comments.php", 
                    "places_results.php", "places_reviews.php", "places_categories.php", "places_category_add.php", "music_musicians.php", "music_musicians_comments.php", "music_songs.php", "music_song_comments.php", "music_categories.php", "music_category_add.php",
                    "flashchat_rooms.php", "flashchat_edit.php", "flashchat_ban.php", "3dchat_rooms.php", "3dchat_edit.php", "vids_users.php", "vids_videos.php", "vids_comments.php", "vids_categories.php", "vids_category_add.php",
                    "adv.php", "groups_groups.php", "groups_group_comments.php", "groups_forums.php", "groups_forum_comments.php", "groups_categories.php", "groups_category_add.php",
                    "events_events.php", "events_event_comments.php", "events_categories.php", "events_category_add.php", 
                    "hotdates_hotdates.php", "hotdates_hotdate_comments.php", "hotdates_categories.php", "hotdates_category_add.php",
                    "forum_categories.php", "forum_category_add.php", "forum_forums.php", "forum_forum_add.php", "forum_topics.php", "forum_messages.php",
                    "seo.php", "city_options.php", "city_logo.php", "city_rooms.php", "city_platform.php", "city_video.php", "city_gallery.php", "city_cache.php"
                ), 
                "Advertise" => array(
                    "pages_add_banner.php", "banner.php", "pages_add_wevents.php", "partner.php", "partner_baners.php", "contact_partner.php", "partner_main.php", "partner_tips.php", "partner_faq.php", "partner_terms.php"
                ),
                "Media" => array(
                    "alb_albums.php", "alb_albums_show.php", "alb_comments.php", "alb_users.php", "media_podcast.php", "vids_radios.php", "music_musicians.php", "music_musicians_comments.php", "music_songs.php", "music_song_comments.php", "music_categories.php", "music_category_add.php",
                    "vids_users.php", "vids_videos.php", "vids_comments.php", "vids_categories.php", "vids_category_add.php", "partyhouz_partyhouz.php", "partyhouz_partyhou_comments.php", "partyhouz_categories.php", "partyhouz_category_add.php"
                ), 
                "SMS_TEXT" => array(
                    "sms_carriers.php", "autosms.php", "masssms.php"
                ),
             
                "Home" => array(
                    "home_.php"
                ), 
                "Options" => array(
                    "options.php", "site_options.php", "image.php", "menu.php", "quick_search_order.php", "profile_col_narrow.php", "user_menu_order.php", "smtp.php", "cache.php", "date_options.php"
                ), 
                "Statistics" => array(
                    "stats.php"
                ),
                "Payment" => array(
                    "pay.php", "pay_order.php", "pay_plans.php", "pay_cat.php", "pay_type.php", "pay_trial.php"
                ), 
                "Donation" => array(
                    "donation_admin_cobra.php",
                ), 
                "New_Page" => array(
                    "pages_add_club.php",
                ),
                "New_Menu" => array(
                    "pages_add_menu.php"
                ), 
                "admin_manages" => array(
                    "add_manager.php", "managers.php", "edit_manager.php"
                )
            );

            if (in_array($p, $admin_urls["admin_manages"])) {
                redirect("index.php");
                die();
            }
            foreach ($admin_urls as $menu_field => $menu_field_urls) {
                if (in_array($p, $menu_field_urls)) {
                    if(isset($g['tmpl']['admin_access_role'][$menu_field]) && $g['tmpl']['admin_access_role'][$menu_field] != 1) {
                        redirect("index.php");
                        die();
                    }
                    break;
                }
            }
        }
        //popcorn changed 9/29/2023 end

}

include_once($g['path']['dir_main'] . "_include/current/common_admin.php");