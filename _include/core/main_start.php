<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include_once(__DIR__ . '/starter.php');
include_once(__DIR__ . '/start.php');
$routerLoadCore = isset($g['router']) && intval($g['router']['load_core']);

if (!$routerLoadCore && $p != 'updater.php') {
    Social::init();
    include(__DIR__ . '/main_auth.php');
}

global $p, $g;

if($p && !(isset($g['is_router_page']) && $g['is_router_page'] == 1)) {
    $file_code = str_replace(".php", "", $p);

    $code_match = array(
        'hotdates' => 'hotdates_hotdates',
        'search_advanced' => 'search_advanced',
        'hotdates_hotdate_edit' => 'create_hotdate',
        'partyhouz_partyhou_edit' => 'partyhouz_partyhou_edit',
        'events_event_edit' => 'events_event_edit',
        'events_calendar' => 'event_calendar',
        'group_add' => 'create_group',
        'city' => '3d_city',
        'events' => 'events',
        'partyhouz' => 'partyhouz',
        'songs_list' => 'music',
        'gallery_albums_user' => 'gallery_albums_user',
        'vids_collect' => 'videogallery',
        'hotdates_calendar' => 'Hotdate_calendar',
        'invite' => 'invite',
        'live_streaming' => 'live_streaming',
        'love_calculator' => 'love_calc',
        'main_calendar' => 'calendar',
        'live_list' => 'live_list',
        'profile_photo' => 'profile_photo',
        'partyhouz' => 'partyhouz',
        'partyhouz_calendar' => 'partyhou_calendar',
        'live_list_finished' => 'live_list_finished',
        'adv_edit' => 'adv_edit',
        'blogs_list' => 'blogs_read',
        'biorythm' => 'biorythm',
        'ajax' => array('code' => 'im', 'user_id' => get_param('user_id', '')),
        'mail_compose' => 'mail_compose',
        'show_interest' => 'show_interest',
        // 'video_upload' => 'video_upload',
        'chat' => '3d_chat',
        'audiochat' => 'audiochat',
        'general_chat' => 'general_chat',
        'forum' => 'forum',
        'games' => 'games',
        'groups_list' => 'groups',
        'groups_search_advanced' => 'groups',
        'places' => 'places',
        'wall' => 'wall',
        'wowslider' => 'whispslider',
        // 'wowslider' => array( 'code' =>  'whispslider'),
        'videochat' => 'videochat',
        'photos_list' => 'gallery_view',
        'Whisprrz Wevents' => 'whisprrz_Wevents',
        'users_viewed_me' => 'viewed_me',
        'blogs_add' => 'blogs_write',
        'adv_edit_done' => 'adv_add'
    );

    if(isset($code_match[$file_code])){
        if(is_array($code_match[$file_code])) {
            payment_check($code_match[$file_code]['code']);
        } else {
            payment_check($code_match[$file_code]);
        }
    }
}
