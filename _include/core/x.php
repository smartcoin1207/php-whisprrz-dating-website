<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$langsForBrowsers = array(
    'en' => 'default',
    'af' => 'afrikaans',
    'sq' => 'albanian',
    'am' => 'amharic',
    'ar' => 'arabic',
    'hy' => 'armenian',
    'az' => 'azeerbaijani',
    'eu' => 'basque',
    'be' => 'belarusian',
    'bn' => 'bengali',
    'bs' => 'bosnian',
    'bg' => 'bulgarian',
    'ca' => 'catalan',
    'ceb' => 'cebuano',
    'zh' => 'chinese',
    'co' => 'corsican',
    'hr' => 'croatian',
    'cs' => 'czech',
    'da' => 'danish',
    'nl' => 'dutch',
    'eo' => 'esperanto',
    'et' => 'estonian',
    'fi' => 'finnish',
    'fr' => 'french',
    'fy' => 'frisian',
    'gl' => 'galician',
    'ka' => 'georgian',
    'de' => 'german',
    'el' => 'greek',
    'gu' => 'gujarati',
    'ht' => 'haitian creole',
    'ha' => 'hausa',
    'haw' => 'hawaiian',
    'iw' => 'hebrew',
    'hi' => 'hindi',
    'hmn' => 'hmong',
    'hu' => 'hungarian',
    'is' => 'icelandic',
    'ig' => 'igbo',
    'id' => 'indonesian',
    'ga' => 'irish',
    'it' => 'italian',
    'ja' => 'japanese',
    'jw' => 'javanese',
    'kn' => 'kannada',
    'kk' => 'kazakh',
    'km' => 'khmer',
    'ko' => 'korean',
    'ku' => 'kurdish',
    'ky' => 'kyrgyz',
    'lo' => 'lao',
    'la' => 'latin',
    'lv' => 'latvian',
    'lt' => 'lithuanian',
    'lb' => 'luxembourgish',
    'mk' => 'macedonian',
    'mg' => 'malagasy',
    'ms' => 'malay',
    'ml' => 'malayalam',
    'mt' => 'maltese',
    'mi' => 'maori',
    'mr' => 'marathi',
    'mn' => 'mongolian',
    'my' => 'myanmar',
    'ne' => 'nepali',
    'no' => 'norwegian',
    'ny' => 'nyanja',
    'ps' => 'pashto',
    'fa' => 'persian',
    'pl' => 'polish',
    'pt' => 'portuguese',
    'pa' => 'punjabi',
    'ro' => 'romanian',
    'ru' => 'russian',
    'sm' => 'samoan',
    'gd' => 'scots gaelic',
    'sr' => 'serbian',
    'st' => 'sesotho',
    'sn' => 'shona',
    'sd' => 'sindhi',
    'si' => 'sinhala',
    'sk' => 'slovak',
    'sl' => 'slovenian',
    'so' => 'somali',
    'es' => 'spanish',
    'su' => 'sundanese',
    'sw' => 'swahili',
    'sv' => 'swedish',
    'tl' => 'tagalog',
    'tg' => 'tajik',
    'ta' => 'tamil',
    'te' => 'telugu',
    'th' => 'thai',
    'tr' => 'turkish',
    'uk' => 'ukrainian',
    'ur' => 'urdu',
    'uz' => 'uzbek',
    'vi' => 'vietnamese',
    'cy' => 'welsh',
    'xh' => 'xhosa',
    'yi' => 'yiddish',
    'yo' => 'yoruba',
    'zu' => 'zulu',
);

// include_once(dirname(__FILE__) . '/../current/cache.class.php');
//include_once(dirname(__FILE__) . '/../current/facebook.class.php');
// include_once(dirname(__FILE__) . '/../current/user.class.php');
//include_once(dirname(__FILE__) . '/../current/wall.class.php');
//require_once(dirname(__FILE__) . '/../current/outside_images.php');
// include_once(dirname(__FILE__) . '/../current/common.class.php');
//include_once(dirname(__FILE__) . '/../current/menu.class.php');
//include_once(dirname(__FILE__) . '/../current/smtp.class.php');
//include_once(dirname(__FILE__) . '/../current/video_hosts.php');
// include_once(dirname(__FILE__) . '/../current/mobile_detect.class.php');
include_once(__DIR__ . '/../current/flipcard.class.php');
include_once(__DIR__ . '/../lib/user.php');

set_include_path(realpath(__DIR__ . '/../../'));

#ini_set("session.save_path", dirname(__FILE__) . "/../../" . $g['dir_files'] . "temp/");
#ini_set("upload_once_tmp_dir", dirname(__FILE__) . "/../../" . $g['dir_files'] . "temp/");
#ini_set("upload_max_filesize", "30M");
#ini_set("post_max_size", "30M");
$g['error_output'] = "browser"; #browser|mail|none

$is_ru_demo = false;

// demo trigger inside script config directory!
if (file_exists(__DIR__ . '/../../_include/config/demo') || file_exists(__DIR__ . '/../../_include/config/demo' . $domain)) {
    define('IS_DEMO', true);
    // DEMO MODE FOR EVENTS
    if (!defined('DEMO_EVENTS')) {
        define('DEMO_EVENTS', true);
    }
} else {
    define('IS_DEMO', false);
}

if (IS_DEMO) {
    $demo_users = array(638 => 'Mike Smith', 12 => 'Lily');
} else {
    $demo_users = array();
}

function is_demo_user()
{
    global $demo_users;
    global $g_user;
    if (in_array($g_user['name'], $demo_users)) {
        return true;
    } else {
        return false;
    }
}

$g['options']['guest_pages'] = array(
                    'index.php',
                    'info.php',
                    'join.php',
                    'join_activate.php',
                    'join_facebook.php',
                    'join2.php',
                    'join3.php',
                    'join_space.php',
                    'contact.php',
                    'about.php',
                    'forget_password.php',
                    'forgot_password.php',
                    'help.php',
                    'news.php',
                    'email_not_confirmed.php',
                    'ban_mails.php',
                    'terms.php',
                    'page.php',
                    'css.php',
                    'js.php',
                    'cron.php',
                    'donation_cobra.php'   // cobra-55555 -display donation_cobra page without login
);

if (Common::isMobile()) {
    $g['options']['guest_pages'][] = 'ajax.php';
}

// GALLERY options
$g['options']['gallery_album_title_length'] = 20;
$g['options']['gallery_album_description_length'] = 50;
$g['options']['gallery_image_title_length'] = 15;
$g['options']['gallery_image_description_length'] = 80;
$g['options']['profile_photo_description_length'] = 120;

$g['adv_image']['big_x'] = "400";
$g['adv_image']['big_y'] = "400";
$g['adv_image']['thumbnail_x'] = "160";
$g['adv_image']['thumbnail_y'] = "107";
$g['adv_image']['thumbnail_small_x'] = "110";
$g['adv_image']['thumbnail_small_y'] = "72";

$g['places_image']['big_x'] = "400";
$g['places_image']['big_y'] = "400";
$g['places_image']['thumbnail_x'] = "80";
$g['places_image']['thumbnail_y'] = "80";
$g['places_image']['thumbnail_big_x'] = "140";
$g['places_image']['thumbnail_big_y'] = "140";

$g['music_musician_image']['big_x'] = "400";
$g['music_musician_image']['big_y'] = "400";
$g['music_musician_image']['thumbnail_x'] = "85";
$g['music_musician_image']['thumbnail_y'] = "64";
$g['music_musician_image']['thumbnail_big_x'] = "160";
$g['music_musician_image']['thumbnail_big_y'] = "120";
$g['music_musician_image']['thumbnail_small_x'] = "39";
$g['music_musician_image']['thumbnail_small_y'] = "29";

$g['music_song_image']['big_x'] = "825";
$g['music_song_image']['big_y'] = "825";
$g['music_song_image']['thumbnail_x'] = "85";
$g['music_song_image']['thumbnail_y'] = "64";
$g['music_song_image']['thumbnail_big_x'] = "160";
$g['music_song_image']['thumbnail_big_y'] = "120";
$g['music_song_image']['thumbnail_small_x'] = "39";
$g['music_song_image']['thumbnail_small_y'] = "29";

$g['events_event_image']['big_x'] = "400";
$g['events_event_image']['big_y'] = "400";
$g['events_event_image']['thumbnail_x'] = "85";
$g['events_event_image']['thumbnail_y'] = "64";
$g['events_event_image']['thumbnail_big_x'] = "160";
$g['events_event_image']['thumbnail_big_y'] = "120";
$g['events_event_image']['thumbnail_small_x'] = "39";
$g['events_event_image']['thumbnail_small_y'] = "29";

//popcorn 2023/11/9 start
$g['hotdates_hotdate_image']['big_x'] = "400";
$g['hotdates_hotdate_image']['big_y'] = "400";
$g['hotdates_hotdate_image']['thumbnail_x'] = "85";
$g['hotdates_hotdate_image']['thumbnail_y'] = "64";
$g['hotdates_hotdate_image']['thumbnail_big_x'] = "160";
$g['hotdates_hotdate_image']['thumbnail_big_y'] = "120";
$g['hotdates_hotdate_image']['thumbnail_small_x'] = "39";
$g['hotdates_hotdate_image']['thumbnail_small_y'] = "29";
//popcorn 2023/11/9 end


//popcorn 2023/11/9 start
$g['partyhouz_partyhou_image']['big_x'] = "400";
$g['partyhouz_partyhou_image']['big_y'] = "400";
$g['partyhouz_partyhou_image']['thumbnail_x'] = "85";
$g['partyhouz_partyhou_image']['thumbnail_y'] = "64";
$g['partyhouz_partyhou_image']['thumbnail_big_x'] = "160";
$g['partyhouz_partyhou_image']['thumbnail_big_y'] = "120";
$g['partyhouz_partyhou_image']['thumbnail_small_x'] = "39";
$g['partyhouz_partyhou_image']['thumbnail_small_y'] = "29";
//popcorn 2023/11/9 end

$g['groups_group_image']['big_x'] = "400";
$g['groups_group_image']['big_y'] = "400";
$g['groups_group_image']['thumbnail_x'] = "85";
$g['groups_group_image']['thumbnail_y'] = "64";
$g['groups_group_image']['thumbnail_big_x'] = "160";
$g['groups_group_image']['thumbnail_big_y'] = "120";
$g['groups_group_image']['thumbnail_small_x'] = "39";
$g['groups_group_image']['thumbnail_small_y'] = "29";

$g['options']['audio_approval'] = "N";
$g['options']['video_approval'] = "N";

$g['media_server_firewall'] = 'Y';
$g['media_server_firewall'] = 'N';
$g['media_server'] = 'https://mediaserver.chameleonintranet.com';
$g['webrtc_app'] = 'wss://zls.qflirt.com:8443/one2one';
$g['webrtc_app_live_streaming'] = 'wss://zls.qflirt.com:8444/one2many';

$g["forum"]["n_topics_per_page"] = 20;
$g["forum"]["n_messages_per_page"] = 20;

// Flash

$swf['profile']['flashvars']['gooleMapPinServer'] = '_server/editor/geo_users.php';
$swf['profile']['flashvars']['lang'] = 'profile_view.php%3Fcmd%3Dlang%26lang_loaded%3D{lang}';
$swf['profile']['flashvars']['langConfig'] = '_server/editor/translate.xml';
$swf['profile']['flashvars']['system_security'] = '%s';
$swf['profile']['flashvars']['id_owner'] = '%u';
$swf['profile']['flashvars']['upload_php'] = 'upload.php';
$swf['profile']['flashvars']['upload_mp_php'] = 'uploadmp.php';
$swf['profile']['flashvars']['shapexml'] = '_server/editor/list_shape.xml';
$swf['profile']['flashvars']['xml_friends'] = '_server/editor/script_friends.php';
$swf['profile']['flashvars']['xml_comments'] = '_server/editor/script_comments.php';
$swf['profile']['flashvars']['csr_comment_add'] = '_server/editor/comment_add.php';
$swf['profile']['flashvars']['csr_comment_del'] = '_server/editor/comments.php';
$swf['profile']['flashvars']['imgPath'] = '_server/editor/images/';
$swf['profile']['flashvars']['backgroundxml'] = '_server/editor/background_space.xml';
$swf['profile']['flashvars']['galleryxml'] = '_server/editor/gallery.php?id=%u';
$swf['profile']['flashvars']['calendarxml'] = '_server/editor/get_events_for_calender.php';
$swf['profile']['flashvars']['xml_music'] = '_server/editor/music.php?id=%u';
$swf['profile']['flashvars']['xml_video'] = '_server/editor/video.php?id=%u';
$swf['profile']['flashvars']['xml_file'] = '_server/editor/thehistory.php?action=xml&uid=%u&c=%u';
$swf['profile']['flashvars']['prof'] = '%s_server/editor/fields.php?id=%u';
$swf['profile']['flashvars']['r'] = '%u';
$swf['profile']['flashvars']['colorbgeditor'] = '0x7EA7B7';
$swf['profile']['flashvars']['colorbgabout'] = '0x8DB7C7';
$swf['profile']['flashvars']['colorbgfontabout'] = '0x7EA7B7';
$swf['profile']['flashvars']['colorheaderabout'] = '0xD9F4FC';
$swf['profile']['flashvars']['colorfontabout'] = '0xD9F4FC';
$swf['profile']['flashvars']['colorbgmenu1'] = '0xC1D6DD';
$swf['profile']['flashvars']['colorbgmenu2'] = '0x5F93A7';
$swf['profile']['flashvars']['colorcontourmenu'] = '0x333333';
$swf['profile']['flashvars']['editborder'] = '0x8DB7C7';
$swf['profile']['flashvars']['bgtabmenu'] = '0x7EA7B7';
$swf['profile']['flashvars']['tabmenubtn'] = '0x5F93A7';
$swf['profile']['flashvars']['bgcanvas'] = '0x719AAA';
$swf['profile']['flashvars']['colorcontourmenu'] = '0x666666';
$swf['profile']['flashvars']['colormskforbg'] = '0x8DB7C7';
$swf['profile']['flashvars']['bgColorPreloader'] = '0x7ea7b7';

$swf['profile']['attributes']['id'] = 'geditor';
$swf['profile']['attributes']['name'] = 'geditor';
$swf['profile']['attributes']['width'] = '100';
$swf['profile']['attributes']['height'] = '200';
$swf['profile']['attributes']['align'] = 'middle';

$swf['profile']['params']['allowScriptAccess'] = 'always';
$swf['profile']['params']['movie'] = '_server/editor/%s';
$swf['profile']['params']['quality'] = 'high';
$swf['profile']['params']['bgcolor'] = '#7ea7b7';
$swf['profile']['params']['wmode'] = 'transparent';

#Postcard
$swf['postcard']['flashvars']['uid'] = '%u';
$swf['postcard']['flashvars']['lang'] = '{url_page}%3Fcmd%3Dlang%26lang%3D{lang_loaded}';
$swf['postcard']['flashvars']['upFile'] = 'upload.php';
$swf['postcard']['flashvars']['urlPreview'] = './postcard_';
$swf['postcard']['flashvars']['params'] = '%s';

$swf['postcard']['attributes']['id'] = 'surprise';
$swf['postcard']['attributes']['width'] = '746';
$swf['postcard']['attributes']['height'] = '426';
$swf['postcard']['attributes']['align'] = 'middle';
$swf['postcard']['attributes']['bgcolor'] = 'ffffff';
$swf['postcard_inbox']['attributes']['width'] = '746';

$swf['postcard']['params']['allowScriptAccess'] = 'sameDomain';
$swf['postcard']['params']['allowFullScreen'] = 'false';
$swf['postcard']['params']['movie'] = '_server/postcard/surprise.swf';
$swf['postcard']['params']['quality'] = 'high';
$swf['postcard']['params']['wmode'] = 'transparent';


#FlashChat
$swf['flashchat']['flashvars']['btnRoomsColor'] = '0x00AACE';
$swf['flashchat']['flashvars']['usersColor'] = '0x00AACE';
$swf['flashchat']['flashvars']['lang'] = '_server%2Fflashchat%2Flang.php%3Flang%3D{lang}';
$swf['flashchat']['flashvars']['skin'] = '_server/flashchat/skin.xml';
$swf['flashchat']['flashvars']['dateFormat'] = 'hh:mm DD.MM.YY';
$swf['flashchat']['flashvars']['redirect_kick'] = 'flashchat.php?reason=kick';
$swf['flashchat']['flashvars']['redirect_ban'] = 'flashchat.php?reason=ban';

$swf['flashchat']['attributes']['id'] = 'chat';
$swf['flashchat']['attributes']['width'] = '750';
$swf['flashchat']['attributes']['height'] = '500';
$swf['flashchat']['attributes']['align'] = 'middle';
$swf['flashchat']['attributes']['bgcolor'] = '7EA7B7';

$swf['flashchat']['params']['allowScriptAccess'] = 'sameDomain';
$swf['flashchat']['params']['movie'] = '_server/flashchat/chat.swf?login=%s';
$swf['flashchat']['params']['quality'] = 'high';
$swf['flashchat']['params']['wmode'] = 'opaque';

#Banner
$swf['banner']['params']['quality'] = 'high';
$swf['banner']['params']['wmode'] = 'transparent';

#Games
$swf['games']['attributes']['width'] = '746';
$swf['games']['attributes']['height'] = '500';
$swf['games']['attributes']['align'] = 'middle';
$swf['games']['attributes']['bgcolor'] = '7EA7B7';

$swf['games']['params']['allowScriptAccess'] = 'sameDomain';
$swf['games']['params']['quality'] = 'high';
$swf['games']['params']['wmode'] = 'opaque';

$swf['games']['flashvars']['bgColor'] = '0x7EA7B7';
$swf['games']['flashvars']['lang'] = 'games.php%3Fcmd%3Dlang%26lang_loaded%3D{lang}';

#test
$swf['test']['attributes']['height'] = '426';
$swf['test']['attributes']['id'] = 'game0';
$swf['test']['params']['movie'] = '_server/game0/first.swf?kolvoPopitok=%d&kolvoAll=%d&myLoginIN=%s&myEnemyIN=%s';
#lovetree
$swf['lovetree']['attributes']['height'] = '426';
$swf['lovetree']['attributes']['id'] = 'game1';
$swf['lovetree']['params']['movie'] = '_server/game1/first.swf?kolvoPopitok=%d&kolvoAll=%d&myLoginIN=%s&myEnemyIN=%s';
#morboy
$swf['morboy']['attributes']['height'] = '426';
$swf['morboy']['attributes']['id'] = 'game2';
$swf['morboy']['params']['movie'] = '_server/game2/morboy.swf?user_id=%d&myLoginIN=%s&myEnemyIN=%s';
#shashki
$swf['shashki']['attributes']['id'] = 'game3';
$swf['shashki']['params']['movie'] = '_server/game3/shashki.swf?user_id=%d&myLoginIN=%s&myEnemyIN=%s';
#chess
$swf['chess']['attributes']['id'] = 'game4';
$swf['chess']['params']['movie'] = '_server/game4/Chess.swf?user_id=%d&myLoginIN=%s&myEnemyIN=%s&mainPath=_server/game4/';
#pool
$swf['pool']['attributes']['id'] = 'game5';
$swf['pool']['params']['movie'] = '_server/game5/Pool.swf?user_id=%d&myLoginIN=%s&myEnemyIN=%s&mainPath=_server/game5/';
#tanks
$swf['tanks']['attributes']['id'] = 'game6';
$swf['tanks']['params']['movie'] = '_server/game6/tanks.swf?user_id=%d&myLoginIN=%s&myEnemyIN=%s&mainPath=_server/game6/';
// Flash
// FACEBOOK style date difference

//popcorn modified 2024-05-02 start
function getFileDirectoryType($directory = '') {
    // $x1 = @ini_get('memory_limit'); echo $x1; die();
    if($directory) {
        if(Common::isOptionActive($directory . '_directory', 'edge_media_files_settings')) {
            return 2;
        } else {
            return 1;
        }
    }
    
    return 1;
}

function custom_get_file_object() {
    S3ClientClass::getFileObject();
}

function isS3SubDirectory($filename) {
    if(!$filename) return false;
    if(is_array($filename)) return false;
    
    $parts = explode('_files/', $filename);
    
    if(isset($parts[1]) && !empty($parts[1])) {
        $subDirParts = explode("/", $parts[1]);
        if(isset($subDirParts[0]) && !empty($subDirParts[0] && getFileDirectoryType($subDirParts[0]) == 2)) {
            return true;
        } else {
            return false;
        }
    } else{
        return false;
    }
}

function custom_getFileDirectUrl($filename) {
    if(!$filename) {
        return '';
    }
    if(isS3SubDirectory($filename)) {
        return S3ClientClass::get_file_direct_url($filename);
    } else {
        return $filename;
    }
}

function custom_file_exists($filename) {
    if(!$filename) return false;

    if(isS3SubDirectory($filename)) {
        return S3ClientClass::isFileExists($filename);
    } else {
        return file_exists($filename);
    }
}

function custom_filemtime($filename) {
    if(!$filename) return false;

    if(isS3SubDirectory($filename)) {
        return S3ClientClass::getFilemtime($filename);
    } else {
        return filemtime($filename);
    }
}

function custom_getimagesize($filename) {
    if(isS3SubDirectory($filename)) {
        return getimagesize($filename);
    } else {
        return getimagesize($filename);
    }
}

function custom_file_upload($file_path, $filename = '') {
    global $g;
    if(isS3SubDirectory($g['path']['dir_files'] . $filename)) {
        if(file_exists($file_path)) {
            S3ClientClass::uploadFilePrepare($file_path, $filename);
        }
    }
}

function custom_unlink($file_path) {
    if(!$file_path) return false;

    if(isS3SubDirectory($file_path)) {
        custom_file_delete($file_path);
    } else {
        @unlink($file_path);
    }
}

function custom_file_delete($file_path) {
    $parts = explode('_files/', $file_path);
    
    if(isset($parts[1]) && !empty($parts[1])) {
        S3ClientClass::deleteFileFromS3Prepare($parts[1]);
    }
}

function custom_file_copy($file_path1, $file_path2) {
    if(!($file_path1 && $file_path2)) return false;
    
    if(isS3SubDirectory($file_path1)) {
        S3ClientClass::CopyObjectS3Prepare($file_path1, $file_path2);
    } else {
        @copyUrlToFile($file_path1, $file_path2);
    }
}

function custom_temp_file_download($filename) {
    if(isS3SubDirectory($filename)) {
        return S3ClientClass::downloadTempFilePrepare($filename);
    } else {
        return $filename;
    }
}
//popcorn modified 2024-05-02 end

function timeAgo($date, $now = 'now', $return = 'string', $justNowLimit = false, $justNowPeriod = false, $short = false)
{
    if (empty($date)) {
        return "No date provided";
    }

    if ($now === 'now') {
        $now = date('Y-m-d H:i:s');
    }

    global $l;

    $nowDate = $now;

    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

    //$now             = time();
    $unix_date = strtotime($date);
    $now = strtotime($now);

    // check validity of date
    // FIX 64 bit version use negative timestamp
    if (empty($unix_date) || $unix_date <= 0) {
        return "Bad date";
    }

    // is it future date or past date
    if ($now > $unix_date) {
        $difference = $now - $unix_date;
        $tense = "ago";
    } else {
        $difference = $unix_date - $now;
        $tense = "from now";
    }

    if(function_exists('date_diff')) {

        $difference = 0;
        $j = 0;

        $dateDiffKeyValues = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        $dateStart = new DateTime($nowDate);
        $dateEnd = new DateTime($date);
        $dateDiff = date_diff($dateStart, $dateEnd);
        foreach($dateDiff as $dateDiffKey => $dateDiffValue) {
            if($dateDiffValue) {
                $j = array_search($dateDiffKeyValues[$dateDiffKey], $periods);
                $difference = $dateDiffValue;
                break;
            }
        }

        if($dateDiffKey == 'y' && $difference >= 10) {
            $difference = floor($difference / 10);
            $j++;
        }

    } else {

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = floor($difference);

        if ($difference >= 12 && $periods[$j] == 'month') {
            $difference = floor($difference / 12);
            $j++;
        }

    }

    $periodJustNowIndex = array_search($justNowPeriod, $periods);

    if ($difference != 1) {
        //$periods[$j] .= "s";
    }

    if ($return === 'string') {
        // zero seconds - just now + translation
        //echo "$difference :: $justNowLimit :: $periodJustNowIndex :: $j<br>";
        if($justNowLimit && $justNowPeriod && $difference < $justNowLimit && $periodJustNowIndex >= $j) {
            $result = l('Just now');
        } else {
            //$result = $difference . ' ' . l($periods[$j]) . ' ' . l('ago');
            $method = Common::getOption('lang_loaded', 'main');
            if(!method_exists('Plural', $method)) {
                $method = 'defaultMethod';
            }
            $plural = intval(Plural::$method($difference));
            $lKeyValue = 'plural_' . $plural . '_' . $periods[$j] . '_ago';
            $lKeyValueShort = $lKeyValue . '_short';
            if ($short && isset($l['all'][$lKeyValueShort])) {
                $lKeyValue = $lKeyValueShort;
            }

            $result = lSetVars($lKeyValue, array('count' => $difference));
        }

    } else {
        $result = array('value' => $difference, 'key' => $periods[$j]);
    }



    return $result;
}

function timeAgoJustNow($date)
{
    return timeAgo($date, 'now', 'string', 60,'second');
}

class Moderator {

    static $sections = array(
        'profiles',
        'photo',
        'vids_video',
        'texts',
        'events',
        'hotdates',
        'partyhouz',
        'craigs'
        );

    static $notificationType = '';

    static $notificationInfo = array();


    static function setNotificationType($notificationType)
    {
        self::$notificationType = $notificationType;
        self::setNotificationInfo('');
    }

    static function getNotificationType()
    {
        return self::$notificationType;
    }

    static function setNotificationInfo($notificationInfo)
    {
        self::$notificationInfo = $notificationInfo;
    }

    static function getNotificationInfo()
    {
        return self::$notificationInfo;
    }



    static function checkAccess($return = false)
    {
        global $g_user;

        $default = '';
        foreach (self::$sections as $section) {
            if (isset($g_user['moderator_' . $section])
                    && $g_user['moderator_' . $section] == 1) {
                $default = $section;
                break;
            }
        }

        if ($return) {
            return $default;
        }

        if ($default == '') {
            redirect("home.php");
        }

        $cmd = get_param('section', $default);

        if (!isset($g_user['moderator_' . $cmd])
                || $g_user['moderator_' . $cmd] != 1) {
            redirect('moderator.php?section=' . $default);
        }

        return $default;
    }
    static function moderator_totalNum() {
        global $g_user;
        
        $buttons = array(
            0 => array('title' => l('moderator_profiles'), 'section' => 'profiles'),
            1 => array('title' => l('Moderator photo'), 'section' => 'photo'),
            2 => array('title' => l('Moderator video'), 'section' => 'vids_video'),
            3 => array('title' => l('Moderator essay'), 'section' => 'texts'),
            4 => array('title' => l('moderator_events'), 'section' => 'events'),
            5 => array('title' => l('moderator_hotdates'), 'section' => 'hotdates'),
            6 => array('title' => l('moderator_partyhouz'), 'section' => 'partyhouz'),
            7 => array('title' => l('moderator_craigs'), 'section' => 'craigs'),
            8 => array('title' => l('moderator_wowslider'), 'section' => 'wowslider'),
            9 => array('title' => l('moderator_users_reports'), 'section' => 'users_reports'),
            10 => array('title' => l('moderator_support_tickets'), 'section' => 'support_tickets')
        );

        $totalNum = 0;

        foreach ($buttons as $v) {
            $num = 0;

            switch ($v['section']) {
                case 'profiles':
                    DB::query("SELECT * FROM user  WHERE active = 0", 2);
                    $num = DB::num_rows(2);
                    break;
                case 'texts':
                    DB::query("SELECT * FROM texts", 2);
                    $num = DB::num_rows(2);
                break;
                case 'vids_video':
                    DB::query('SELECT * FROM `vids_video` WHERE `active` = 3', 2);
                    $num = DB::num_rows(2);
                break;    
                case 'photo':
                    DB::query("SELECT * FROM photo WHERE " . CProfilePhoto::moderatorVisibleFilter(), 2);
                    $num = DB::num_rows(2);
                    break;
                case 'events':
                    DB::query("SELECT * FROM events_event WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
                break;  
                case 'hotdates':
                    DB::query("SELECT * FROM hotdates_hotdate WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
                break;  
                case 'partyhouz':
                    DB::query("SELECT * FROM partyhouz_partyhou WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
                break;  
                case 'craigs':
                    $num = 0;
                    for($i=1;$i<=11;$i++)
                    {
                        DB::query("select * from adv_cats where id=".$i);
                        if ($cat = DB::fetch_row()) {
        
                            $adv_table = "adv_" . $cat['eng'];
                            DB::query("SELECT * FROM ". $adv_table . " WHERE approved = 0", 2);
                            $num1 = DB::num_rows(2);
                            $num = $num + $num1;
                        }
                        
                    }
                break;  
                case 'wowslider':
                    DB::query("SELECT * FROM wowslider WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
                break;
                case 'users_reports':
                    DB::query("SELECT * FROM users_reports", 2);
                    $num = DB::num_rows(2);
                    break;
                case 'support_tickets':
                    DB::query("SELECT * FROM support_tickets where assign_to={$g_user['user_id']}  AND status='1'", 2);
                    $num = DB::num_rows(2);
                    break;
                default:
                    # code...
                    break;
            }
            if ($g_user['moderator_' . $v['section']] == 1) {
               $totalNum += $num;
            }
        }

        return $totalNum;
    }

    static function buttonsParse(&$html)
    {
        global $g_user;

        $cmd = get_param('section', self::checkAccess());

        $buttons = array(
            0 => array('title' => l('moderator_profiles'), 'section' => 'profiles'),
            1 => array('title' => l('Moderator photo'), 'section' => 'photo'),
            2 => array('title' => l('Moderator video'), 'section' => 'vids_video'),
            3 => array('title' => l('Moderator essay'), 'section' => 'texts'),
            4 => array('title' => l('moderator_events'), 'section' => 'events'),
            5 => array('title' => l('moderator_hotdates'), 'section' => 'hotdates'),
            6 => array('title' => l('moderator_partyhouz'), 'section' => 'partyhouz'),
            7 => array('title' => l('moderator_craigs'), 'section' => 'craigs'),
            8 => array('title' => l('moderator_wowslider'), 'section' => 'wowslider'),
            9 => array('title' => l('moderator_users_reports'), 'section' => 'users_reports'),
            10 => array('title' => l('moderator_support_tickets'), 'section' => 'support_tickets')
        );

        foreach ($buttons as $v) {
            $html->setvar('button_title', $v['title']);
            $html->setvar('section', $v['section']);
            if ($cmd == $v['section']) {
                if (Common::getOption('set', 'template_options') !== 'urban1') {
                    $html->setvar('active_button', 'button_active');
                } else {
                    $html->setvar('active_button', 'black');
                }
            } else {
                if (Common::getOption('set', 'template_options') !== 'urban1') {
                    $html->setvar('active_button', '');
                } else {
                    $html->setvar('active_button', 'green');
                }
            }
     
            $num = 0;

            switch ($v['section']) {
                case 'profiles':
                    DB::query("SELECT * FROM user  WHERE active = 0", 2);
                    $num = DB::num_rows(2);       
                    break;
                case 'texts':
                    DB::query("SELECT * FROM texts", 2);
                    $num = DB::num_rows(2);

                break;
                case 'vids_video':
                    DB::query('SELECT * FROM `vids_video` WHERE `active` = 3', 2);
                    $num = DB::num_rows(2);

                break;    
                case 'photo':
                        DB::query("SELECT * FROM photo WHERE " . CProfilePhoto::moderatorVisibleFilter(), 2);
                        $num = DB::num_rows(2);
        
                        break;
                
                case 'events':
                    DB::query("SELECT * FROM events_event WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
  
                break;  
                case 'hotdates':
                    DB::query("SELECT * FROM hotdates_hotdate WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
       
                break;  
                case 'partyhouz':
                    DB::query("SELECT * FROM partyhouz_partyhou WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
                 
                break;  
                case 'craigs':
                $num = 0;
                for($i=1;$i<=11;$i++)
                {
                    DB::query("select * from adv_cats where id=".$i);
                    if ($cat = DB::fetch_row()) {
    
                        $adv_table = "adv_" . $cat['eng'];
                        DB::query("SELECT * FROM ". $adv_table . " WHERE approved = 0", 2);
                        $num1 = DB::num_rows(2);
                        $num = $num + $num1;
                    }
                    
                }
          
                break;  
                case 'wowslider':
                    DB::query("SELECT * FROM wowslider WHERE approved = 0", 2);
                    $num = DB::num_rows(2);
               
                break;  
                case 'users_reports':
                    DB::query("SELECT * FROM users_reports", 2);
                    $num = DB::num_rows(2);
               
                break; 
                case 'support_tickets': /* Added by Divyesh on 13-10-2023 */
                    DB::query("SELECT * FROM support_tickets WHERE assign_to={$g_user['user_id']} AND status='1'", 2);
                    $num = DB::num_rows(2);
               
                break;  
                default:
                    # code...
                    break;
            }
            if ((isset($g_user['moderator_' . $v['section']]) && $g_user['moderator_' . $v['section']] == 1)) {
                if($num) {
                    $html->setvar('moderator_num', $num);
                    $html->parse('moderator_waiting_num', true);
                }
                $html->parse('button');
                $html->clean('moderator_waiting_num');
            }
        }
    }

    static function sendNotification($isApproved = true)
    {
        $type = self::getNotificationType();
        $notificationType = self::prepareNotificationType($type, $isApproved);

        if (Common::isEnabledAutoMail($notificationType)) {
            $info = self::getNotificationInfo();
            if(isset($info['user_id']) && $info['user_id']) {
                self::sendNotificationToEmail($notificationType, $info, self::prepareVars($type, $info));
            }
        }

    }

    static function sendNotificationApproved()
    {
        self::sendNotification(true);
    }

    static function sendNotificationDeclined()
    {
        self::sendNotification(false);
    }

    static function prepareVars($type, $info)
    {
        $vars = array(
            'title' => Common::getOption('title', 'main'),
            'name' => $info['name'],
        );
        if ($type == 'photo' || $type == 'video') {
            $vars['item_comment'] = isset($info['item']['comment_declined']) ? $info['item']['comment_declined'] : '';
        }
        if($type == 'photo') {
            $vars['item_title'] = $info['item']['description'] ? "\"{$info['item']['description']}\"" : '';
            $vars['item_id'] = $info['item']['photo_id'];
        } elseif ($type == 'video') {
            $vars['item_title'] = $info['item']['subject'] ? "\"{$info['item']['subject']}\"" : '';
            $vars['item_id'] = $info['item']['id'];
        }

        return $vars;
    }

    static function prepareNotificationType($type, $isApproved)
    {
        $notificationType = $type . '_' . ($isApproved ? 'approved' : 'declined');
        return $notificationType;
    }

    static function prepareNotificationInfo($uid, $params = null)
    {
        $info = User::getInfoBasic($uid);

        $type = self::getNotificationType();

        if ($type == 'photo') {
            $info['item'] = $params;
        } elseif ($type == 'video') {
            $info['item'] = $params;
        }

        self::setNotificationInfo($info);
    }

    static function sendNotificationToEmail($type, $user, $vars)
    {
        Common::sendAutomail($user['lang'], $user['mail'], $type, $vars);
    }

    static function setNotificationTypeText()
    {
        self::setNotificationType('text');
    }

    static function setNotificationTypePhoto()
    {
        self::setNotificationType('photo');
    }

    static function setNotificationTypeVideo()
    {
        self::setNotificationType('video');
    }

    static function isAllowedViewingUsers($key = null)
    {
        if ($key === null) {
            $key = 'this_user_has_blocked_you';
        }
        return $key == 'this_user_has_blocked_you' && get_param_int('moderator') && self::checkAccess(true);
    }

}

class IP {

    const MULTIPLICATOR = 10000000;
    static $geoInfoCity = false;

    static function getIp()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $forwardedIps[0];
        }

        if(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return $ip;
    }

    static function getIpMasks($ip)
    {
        $ipArr=explode('.',$ip);
        $ipMasks=array(to_sql($ip));

        if(count($ipArr) == 4) {
            for($i=0;$i<4;$i++){
                $ipTmp1=$ipArr;
                $ipTmp1[$i]='*';
                $ipMasks[]=to_sql(implode('.',$ipTmp1));
                for($j=$i+1;$j<4;$j++){
                    $ipTmp2=$ipTmp1;
                    if($ipTmp2[$j]!='*'){
                        $ipTmp2[$j]='*';
                        $ipMasks[]=to_sql(implode('.',$ipTmp2));
                    }
                    for($k=$j+1;$k<4;$k++){
                        $ipTmp3=$ipTmp2;
                        if($ipTmp3[$k]!='*'){
                            $ipTmp3[$k]='*';
                            $ipMasks[]= to_sql(implode('.',$ipTmp3));
                        }
                    }
                }
            }
            $ipMasks[]= to_sql('*.*.*.*');
        } else {
            $ipv6Arr = explode(':', $ip);
            $ipv6ArrCount = count($ipv6Arr);
            if($ipv6ArrCount > 1) {
                for($i = $ipv6ArrCount; $i > 1; $i--) {
                    $ipv6Arr[$i - 1] = '*';
                    $ipMasks[] = to_sql(implode(':', $ipv6Arr));
                }
                $ipMasks[]= to_sql('*:*:*:*:*:*:*:*');
            }

        }

        return $ipMasks;
    }

    static function block()
    {
        if(!Common::isOptionActive('active', 'ipblock')) {
            return;
        }

        global $p;
        if($p == 'cron.php') {
            return;
        }

        $ip = self::getIp();

        if(get_session('ip_block_check_time_' . $ip) > Common::getOption('last_update_time', 'ipblock')) {
            return;
        }

        $ipMasks=IP::getIpMasks($ip);

        $sql = 'SELECT id FROM ip_block
            WHERE ip IN (' . implode(',', $ipMasks) . ')';
        $block = DB::result($sql);

        if ($block) {
            $url = Common::getOption('url', 'ipblock');
            redirect($url);
        }

        set_session('ip_block_check_time_' . $ip, time());
    }

    static function updateIpBlockStatus()
    {
        $active = 'N';

        if(DB::count('ip_block')) {
            $active = 'Y';
            Config::update('ipblock', 'last_update_time', time());
        }

        Config::update('ipblock', 'active', $active);
    }

    static function geoInfo($ip, $dbIndex = DB_MAX_INDEX)
    {
// FROM DB
//        $ipLong = sprintf('%u', ip2long($ip));
//        $sql = 'SELECT * FROM geoip
//            WHERE ipfrom <= ' . $ipLong . '
//                AND ipto >= ' . $ipLong;
//        $ipInfo = DB::row($sql, 0, $dbIndex);

        global $g;

        $ipInfo = new IpInfo($g['path']['dir_main'] . '_include/current/misc/ip.db', IpInfo::FILE_IO);
        $info = $ipInfo->lookup($ip, IpInfo::ALL);
        $result = array();
        $result['lat'] = round((isset($info['latitude']) ? floatval($info['latitude']) : 0) * self::MULTIPLICATOR, 0);
        $result['long'] = round((isset($info['longitude']) ? floatval($info['longitude']) : 0) * self::MULTIPLICATOR, 0);

        return $result;
    }

    static function geoInfoCity()
    {


        global $p;
        if(self::$geoInfoCity === false) {
            if(Common::isOptionActive('ip_location')){
                $ip = self::getIp();
                $ipParam = get_param('ip');
                if($ipParam) {
                    $ip = $ipParam;
                }

                $cityInfo = false;

                /*if (IS_DEMO) {
                    self::$geoInfoCity = self::geoInfoCityDefault();
                    set_session('ip_city_info', self::$geoInfoCity);
                    return self::$geoInfoCity;
                }*/

                $ipSession = get_session('ip');
                if($ipSession != $ip) {
                    set_session('ip', $ip);
                    $geoinfo = self::geoInfo($ip);

                    if($geoinfo && ($geoinfo['lat'] != 0 && $geoinfo['long'] != 0)) {
                        $ipLat = to_sql($geoinfo['lat'] / self::MULTIPLICATOR, 'Number');
                        $ipLong = to_sql($geoinfo['long'] / self::MULTIPLICATOR, 'Number');

                        $cityInfo = self::geoInfoCityFindInRadius($ipLat, $ipLong, 100);
                        if(!$cityInfo) {
                            $cityInfo = self::geoInfoCityFindInRadius($ipLat, $ipLong, 1000);
                        }
                        if($cityInfo) {
                            if(Common::isCountryHidden($cityInfo['country_id'])) {
                                $cityInfo = false;
                            } elseif(Common::isStateHidden($cityInfo['state_id'])) {
                                $cityInfo = self::loadInfoState($cityInfo);
                                $cityInfo = self::loadInfoCity($cityInfo);
                            }
                        }
                    }

                    if(!$cityInfo) {
                        $cityInfo = self::geoInfoCityDefault();
                    }
                    set_session('ip_city_info', $cityInfo);
                } else {
                    $cityInfo = get_session('ip_city_info');
                }
            }

            if(!isset($cityInfo['city_id'])) {
                $cityInfo = self::geoInfoCityDefault();
                if($cityInfo) {
                    set_session('ip_city_info', $cityInfo);
                }
            }


            self::$geoInfoCity = $cityInfo;
        }


        return self::$geoInfoCity;
    }

    static function geoInfoCityFindInRadius($ipLat, $ipLong, $distance = 100, $limit = 1, $isAll = false)
    {
        if(false) {
            $multiplicator = self::MULTIPLICATOR;
            $sql = 'SELECT *, 7912 * ASIN(SQRT( POWER(SIN((' . $ipLat . ' - lat/' . $multiplicator . ' ) *  pi()/180 / 2), 2) + COS(' . $ipLat . ' * pi()/180) * COS(lat/' . $multiplicator . ' * pi()/180) * POWER(SIN((' . $ipLong . ' - `long`/' . $multiplicator . ') * pi()/180 / 2), 2) )) as distance FROM geo_city
                WHERE hidden = 0
                HAVING distance < 100
                ORDER by distance ASC LIMIT 1';
            return DB::row($sql);
        }

        // optimize query - limit by borders
        // distance in miles
        $ipLatMin = $ipLat - ($distance / 69);
        $ipLatMax = $ipLat + ($distance / 69);
        $ipLongMin = $ipLong - $distance / abs(cos(deg2rad($ipLat)) * 69);
        $ipLongMax = $ipLong + $distance / abs(cos(deg2rad($ipLat)) * 69);
        $limitSql = '';
        if ($limit) {
            $limitSql = ' LIMIT ' . $limit;
        }
        $sql = 'SELECT *, 7912 * ASIN(SQRT( POWER(SIN((' . $ipLat . ' - lat/' . self::MULTIPLICATOR . ' ) *  pi()/180 / 2), 2) + COS(' . $ipLat . ' * pi()/180) * COS(lat/' . self::MULTIPLICATOR . ' * pi()/180) * POWER(SIN((' . $ipLong . ' - `long`/' . self::MULTIPLICATOR . ') * pi()/180 / 2), 2) )) as distance FROM geo_city
                 WHERE hidden = 0 AND `lat` BETWEEN ' . round($ipLatMin * self::MULTIPLICATOR, 0) . ' AND ' . round($ipLatMax * self::MULTIPLICATOR, 0) . '
                   AND `long` BETWEEN ' . round($ipLongMin * self::MULTIPLICATOR, 0) . ' AND ' . round($ipLongMax * self::MULTIPLICATOR, 0) . '
                 ORDER by distance ASC ' . $limitSql;

        if ($isAll) {
            $result = DB::rows($sql);
        } else {
            $result = DB::row($sql);
        }
        return $result;
    }

    static function geoInfoCityDefault()
    {
        $sql = 'SELECT * FROM geo_city WHERE hidden = 0 AND city_id = ' . to_sql(Common::getOption('city_default'));
        $cityInfo = DB::row($sql);
        if(!$cityInfo) {
            $sql = 'SELECT * FROM geo_city WHERE hidden = 0 ORDER BY city_id ASC LIMIT 1';
            $cityInfo = DB::row($sql);
        }

        return $cityInfo;
    }

    public static function loadInfoCity($data)
    {
        $data['city_id'] = 0;
        $data['city_title'] = '';
        $city = Common::getFirstCityInState($data['state_id']);
        if($city) {
            $data['city_id'] = $city['city_id'];
            $data['city_title'] = $city['city_title'];
        }
        return $data;
    }

    public static function loadInfoState($data)
    {
        $data['state_id'] = 0;
        $data['state_title'] = '';
        $state = Common::getFirstStateInCountry($data['country_id']);
        if($state) {
            $data['state_id'] = $state['state_id'];
            $data['state_title'] = $state['state_title'];
        }
        return $data;
    }
}

class Config {

    static $table = 'config';

    static function getOption($module, $option, $index = DB_MAX_INDEX)
    {
        $sql = 'SELECT value FROM ' . self::$table . '
            WHERE module = ' . to_sql($module, 'Text') . '
                AND `option` = ' . to_sql($option, 'Text');
        $value = DB::result($sql, 0, $index);

        return $value;
    }

    static function getOptionId($module, $option, $index = DB_MAX_INDEX)
    {
        $sql = 'SELECT id FROM ' . self::$table . '
            WHERE module = ' . to_sql($module, 'Text') . '
                AND `option` = ' . to_sql($option, 'Text');
        $value = DB::result($sql, 0, $index);

        return $value;
    }


    static function getOptionsAll($module, $sort = false, $order = false, $all = false, $allowedOptions = array(), $index = DB_MAX_INDEX)
    {
        $options = array();
        $where = '';
        if ($allowedOptions) {
            $i = 0;
            foreach ($allowedOptions as $option) {
                if (!$i++) {
                   $where .= ' AND (`option` = ' . to_sql($option);
                } else {
                   $where .= ' OR `option` = ' . to_sql($option);
                }
            }
            if ($where) {
                $where .= ')';
            }
        }
        $sql = 'SELECT * FROM ' . self::$table . '
            WHERE module = ' . to_sql($module, 'Text') . $where;

        if($sort) {
            $sql .= ' ORDER BY `' . to_sql($sort, 'Plain') . '`';
        }

        if($sort && $order) {
            $sql .= ' ' . to_sql($order, 'Plain');
        }

        DB::query($sql, $index);
        while ($row = DB::fetch_row($index)) {
            $options[$row['option']] = $all ? $row : $row['value'];
        }
        return $options;
    }

    static function getOptionsByModule($module, $sort = false, $order = false, $all = false, $allowedOptions = array(), $index = DB_MAX_INDEX)
    {
        $options = array();
        $where = '';
        if ($allowedOptions) {
            $i = 0;
            foreach ($allowedOptions as $option) {
                if (!$i++) {
                   $where .= ' AND (`option` = ' . to_sql($option);
                } else {
                   $where .= ' OR `option` = ' . to_sql($option);
                }
            }
            if ($where) {
                $where .= ')';
            }
        }
        $sql = 'SELECT `option` FROM ' . self::$table . '
            WHERE module = ' . to_sql($module, 'Text') . $where;
        $options = DB::rows($sql);

        return $options;
    }


    static function update($module, $option, $value, $update = true)
    {
        global $g;
        $sql = 'UPDATE ' . self::$table . '
            SET value = ' . to_sql($value, 'Text') . '
            WHERE module = ' . to_sql($module, 'Text') . '
                AND `option` = ' . to_sql($option, 'Text');

        DB::execute($sql);

        if ($update) {
            $g[$module][$option] = $value;
        }
    }


    static function updateAll($module, $options)
    {
        if (is_array($options) && count($options)) {
            foreach ($options as $k => $v) {
                self::update($module, $k, $v);
            }
        }

        if($module == 'options') {
            self::updateSiteVersion();
        }
    }

    static function remove($module, $option = '')
    {
        $sql = 'DELETE FROM ' . self::$table . '
            WHERE module = ' . to_sql($module, 'Text');
        if($option != '') {
            $sql .= ' AND `option` = ' . to_sql($option, 'Text');
        }
        DB::execute($sql);
    }

    static function add($module, $option, $value, $position = 'max', $showInAdmin = 1, $type = '', $update = false)
    {
        global $g;
        if($position === 'max') {
            $sql = 'SELECT MAX(`position`) FROM ' . self::$table . '
                WHERE module = ' . to_sql($module, 'Text');
            $position = DB::result($sql) + 1;
        } else {
            //$position = 0;
            $position = intval($position);
        }
        $sql = 'INSERT INTO ' . self::$table . '
            SET module = ' . to_sql($module, 'Text') . ',
                `option` = ' . to_sql($option, 'Text') . ',
                `value` = ' . to_sql($value, 'Text') . ',
                `position` = ' . to_sql($position, 'Number') . ',
                `show_in_admin` = ' . to_sql($showInAdmin, 'Number') . ',
                `type` = ' . to_sql($type, 'Text');
        DB::execute($sql);

        if ($update) {
            $g[$module][$option] = $value;
        }
        return $position;
    }

    static function addAll($module, $options)
    {
        if (is_array($options) && count($options)) {
            foreach ($options as $k => $v) {
                self::add($module, $k, $v);
            }
        }
    }

    static function allCache()
    {
        global $g;
        global $pay;

        if($g['cache_config_all'] === false) {
            $sql = 'SELECT * FROM ' . self::$table . '
                ORDER BY `module` ASC, `position` ASC';
            DB::setFetchType(MYSQL_ASSOC);
            $configAll = DB::all($sql);
            DB::setFetchType(MYSQL_BOTH);

            $config = array();
            foreach($configAll as $configOption) {
                if($configOption['module'] == 'user_var') {
                    $configOption['value'] = unserialize($configOption['value']);
                    $configOption['value']['id'] = $configOption['id'];
                }
                $config[$configOption['module']][$configOption['option']] = $configOption['value'];
            }

            foreach($config['lang'] as $langKey => $langValue) {
                $config['lang'][$langKey] = $g['path']['dir_lang'] . $langKey . '/' . $langValue . '/';
                $config['lang_value'][$langKey] = $langValue;
            }

            foreach($config['tmpl'] as $tmplKey => $tmplValue) {
                $config['tmpl']['dir_tmpl_' . $tmplKey] = $g['path']['dir_tmpl'] . $tmplKey . '/' . $tmplValue . '/';
                $config['tmpl']['url_tmpl_' . $tmplKey] = $g['path']['url_tmpl'] . $tmplKey . '/' . $tmplValue . '/';
            }

            $config['main']['title_orig'] = $config['main']['title'];

            #$config['cache_config_all']['g']['main'] = $config['main'];

            $paymentModules = $config['payment_modules'];
            if(is_array($paymentModules) && count($paymentModules)) {
                foreach ($paymentModules as $paymentModule => $values) {
                    $pay[$paymentModule] = $config[$paymentModule];
                }
            }

            $g['cache_config_all']['g'] = $config;
            $g['cache_config_all']['pay'] = $pay;
            $g['config_all_update'] = true;
       }

        $pay = $g['cache_config_all']['pay'];
        // error - new items will overwrite exists
        $g = array_merge($g, $g['cache_config_all']['g']);
    }

    static function all()
    {
        global $g;
        global $pay;


        // $sql = 'SELECT * FROM ' . self::$table . '
        //    ORDER BY `module` ASC, `position` ASC';
        // Sort in PHP 2-3 faster then MySQL

        $sql = 'SELECT * FROM ' . self::$table;
        DB::setFetchType(MYSQL_ASSOC);
        $configAll = DB::all($sql);
        DB::setFetchType(MYSQL_BOTH);

        $module = array();
        $position = array();

        $sortConfigArray = false;

        foreach($configAll as $key => $value) {
            $module[$key] = $value['module'];
            $position[$key] = $value['position'];
            $sortConfigArray = true;
        }

        if($sortConfigArray) {
            array_multisort($module, SORT_ASC, $position, SORT_ASC, $configAll);
        }

        foreach($configAll as $configOption) {
            if($configOption['module'] == 'user_var') {
                $configOption['value'] = unserialize($configOption['value']);
                $configOption['value']['id'] = $configOption['id'];
            }
            $g[$configOption['module']][$configOption['option']] = $configOption['value'];
            $g['table_cache_config'][$configOption['module']][$configOption['option']] = $configOption;
        }

        foreach($g['lang'] as $langKey => $langValue) {
            $g['lang'][$langKey] = $g['path']['dir_lang'] . $langKey . '/' . $langValue . '/';
            $g['lang_value'][$langKey] = $langValue;
        }

        foreach($g['tmpl'] as $tmplKey => $tmplValue) {
            $g['tmpl']['dir_tmpl_' . $tmplKey] = $g['path']['dir_tmpl'] . $tmplKey . '/' . $tmplValue . '/';
            $g['tmpl']['url_tmpl_' . $tmplKey] = $g['path']['url_tmpl'] . $tmplKey . '/' . $tmplValue . '/';
        }

        $g['main']['title_orig'] = htmlspecialchars($g['main']['title'], ENT_QUOTES, 'UTF-8');

        if(!Common::isAppAndroid()) {
            Common::setOptionRuntime('N', 'active', 'iapgoogle');
        }
        if(!Common::isAppIos()) {
            Common::setOptionRuntime('N', 'active', PayIapApple::getSystem());
        }

        $paymentModules = $g['payment_modules'];
        if(is_array($paymentModules) && count($paymentModules)) {
            foreach ($paymentModules as $paymentModule => $values) {
                $pay[$paymentModule] = $g[$paymentModule];
            }
        }
        if(isset($g['site_cache']) && is_array($g['site_cache'])){
            foreach ($g['site_cache'] as $key => $value) {
                $g['site_cache']["{$key}_param"] = "?v={$value}";
                $g['site_cache']["cache_{$key}"] = $value;
                $g['site_cache']["cache_{$key}_param"] = "?v={$value}";
                $g['site_cache']["cache_{$key}_param_single"] = "&v={$value}";
            }
        }
        if (isset($g['options']['paid_access_mode'])){
            $g['options'][$g['options']['paid_access_mode']] = 'Y';
        }

        // ios app only special version because need camera/audio permissions
        if (Common::isAppIos() && !Common::isAppIosWebrtcAvailable()) {
                $g['options']['audiochat'] = 'N';
                $g['options']['videochat'] = 'N';
        }

        $g['options']['type_media_chat'] = 'webrtc';

    }

    static function updatePosition($module, $option, $value)
    {
        $sql = 'UPDATE ' . self::$table . '
                   SET `position` = ' . to_sql($value, 'Number') . '
                 WHERE module = ' . to_sql($module, 'Text') . '
                   AND `option` = ' . to_sql($option, 'Text');

        DB::execute($sql);
    }

    static function getPosition($module, $option, $useCache = true)
    {
        $result = 0;
        if($useCache) {
            global $g;
            if(isset($g['table_cache_config'][$module][$option]['position'])) {
                $result = $g['table_cache_config'][$module][$option]['position'];
            }
        } else {
            $sql = 'SELECT `position` FROM ' . self::$table . '
                     WHERE `module` = ' . to_sql($module, 'Text') . '
                       AND `option` = ' . to_sql($option, 'Text');
            $result = DB::result($sql);
        }

        return $result;
    }

    public static function updateSiteVersion()
    {
        global $g;
        $module = 'site_cache';
        $option = 'version';
        Config::update($module, $option, ++$g[$module][$option], true);
    }
}

//popcorn add this function 2024-07-10
function calculateDistance($lat1, $long1, $lat2, $long2) {    
    // convert from degrees to radians
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($long1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($long2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
        
    $val = pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2);
    $angle = 2 * asin(sqrt($val));
    $radius = 3958.756;
        
    return ($angle * $radius);
}

function getInRadiusWhere($radius)
{
    $radius = intval($radius);
    $where = '';

    if (guid()) {
        $lat = guser('geo_position_lat');
        $long = guser('geo_position_long');
    } else {

        $geoCityInfo = IP::geoInfoCity();
        $lat = $geoCityInfo['lat'];
        $long = $geoCityInfo['long'];
    }

    if ($lat && $long) {
        $multiplicator = IP::MULTIPLICATOR;
        $lat = $lat / $multiplicator;
        $long = $long / $multiplicator;
        if (Common::getOption('unit_distance') != 'miles') {
            $radius /= 1.609;
        }
        $latMin = ($lat - ($radius / 69)) * $multiplicator;
        $latMax = ($lat + ($radius / 69)) * $multiplicator;
        $longMin = ($long - $radius / abs(cos(deg2rad($lat)) * 69)) * $multiplicator;
        $longMax = ($long + $radius / abs(cos(deg2rad($lat)) * 69)) * $multiplicator;

        $where = ' AND u.geo_position_lat BETWEEN ' . round($latMin, 0) . ' AND ' . round($latMax, 0) . '
                   AND u.geo_position_long BETWEEN ' . round($longMin, 0) . ' AND ' . round($longMax, 0) . "
                   AND (POW((69.1*(u.geo_position_long - ($long))*" . cos($lat/57.3) . "),2)+POW((69.1*(u.geo_position_lat - ($lat))),2))<" . ($radius * $radius) . " ";
                //    AND (POW((69.1*(u.geo_position_long/$multiplicator - ($long))*" . cos($lat/57.3) . "),2)+POW((69.1*(u.geo_position_lat/$multiplicator - ($lat))),2))<" . ($radius * $radius) . " ";

    }

    return $where;
}

function inradius($city, $radius)
{
    $radius = intval($radius);

    $sql = 'SELECT * FROM geo_city
        WHERE city_id = ' . to_sql($city, 'Number');
    DB::query($sql);

    $multiplicator = 10000000;

    if ($row = DB::fetch_row()) {
        $lat = $row['lat'] / $multiplicator;
        $lon = $row['long'] / $multiplicator;

        // distance in miles
        if (Common::getOption('unit_distance') != 'miles') {
            $radius /= 1.609;
        }

        // optimize query - limit by borders
        $latMin = ($lat - ($radius / 69)) * $multiplicator;
        $latMax = ($lat + ($radius / 69)) * $multiplicator;
        $longMin = ($lon - $radius / abs(cos(deg2rad($lat)) * 69)) * $multiplicator;
        $longMax = ($lon + $radius / abs(cos(deg2rad($lat)) * 69)) * $multiplicator;  

        // !!! it use MILES only !!!
        // without borders
        // $sql = " AND (POW((69.1*(gc.long/$multiplicator -\"$lon\")*cos($lat/57.3)),\"2\")+POW((69.1*(gc.lat/$multiplicator - \"$lat\")),\"2\"))<($radius * $radius) ";

        $sql = ' AND gc.`lat` BETWEEN ' . round($latMin, 0) . ' AND ' . round($latMax, 0) . '
                AND gc.`long` BETWEEN ' . round($longMin, 0) . ' AND ' . round($longMax, 0) . "
            AND (POW((69.1*(gc.long/$multiplicator - ($lon))*" . cos($lat/57.3) . "),2)+POW((69.1*(gc.lat/$multiplicator - ($lat))),2))<" . ($radius * $radius) . " ";
    } else {
        $sql = '';
    }
    return $sql;
}

function inradiusLatLong($lat, $lon, $radius)
{
    $radius = intval($radius);
    
    $multiplicator = 10000000;

    // distance in miles
    if (Common::getOption('unit_distance') != 'miles') {
        $radius /= 1.609;
    }

    // optimize query - limit by borders
    $latMin = ($lat - ($radius / 69.172 ));
    $latMax = ($lat + ($radius / 69.172 ));
    $longMin = ($lon - $radius / abs(cos(deg2rad($lat)) * 69.172 )) ;
    $longMax = ($lon + $radius / abs(cos(deg2rad($lat)) * 69.172 )) ;  

    $sql = ' AND u.`geo_position_lat` BETWEEN ' . round($latMin, 4) . ' AND ' . round($latMax, 4) . '
            AND u.`geo_position_long` BETWEEN ' . round($longMin, 4) . ' AND ' . round($longMax, 4) . "
        AND (POW((69.172 *(u.`geo_position_long` - ($lon))*" . cos($lat/57.2957) . "),2)+POW((69.172 *(u.`geo_position_lat` - ($lat))),2))<" . ($radius * $radius) . " ";
    
    return $sql;
}

function closestCity($city)
{
    $sql = 'SELECT * FROM geo_city
        WHERE city_id = ' . to_sql($city, 'Number');
    DB::query($sql);

    $multiplicator = 10000000;

    if ($row = DB::fetch_row()) {
        $lat = $row['lat'] / $multiplicator;
        $lon = $row['long'] / $multiplicator;

        // distance in miles
        // if (Common::getOption('unit_distance') != 'miles') {
        //     $radius /= 1.609;
        // }

        // optimize query - limit by borders

        // !!! it use MILES only !!!
        // without borders

        $sql = " ((POW((69.1*(gc.long/$multiplicator - ($lon))*" . cos($lat/57.3) . "),2)+POW((69.1*(gc.lat/$multiplicator - ($lat))),2))) AS distance";
    } else {
        $sql = '';
    }
    return $sql;
}

function checkBannerExt($ext = '', $type = 0)
{
    $allowExt = array('jpg', 'jpeg', 'gif', 'png', 'swf');
    if ($type == 1) {
       $allowExt = array('swf');
    } elseif($type == 2) {
       $allowExt = array('jpg', 'jpeg', 'gif', 'png');
    }

    $ext = strtolower($ext);

    if (!in_array($ext, $allowExt)) {
        return false;
    }

    return true;
}

function htmlSetVars(&$html, $vars)
{
    if(is_array($vars) && count($vars)) {
        foreach($vars as $key => $value) {
            $html->setvar($key, $value);
        }
    }
}

function g_user_full()
{
    global $g_user;
    $g_user = User::getInfoFull(guid(), 0, false);
    //User::paidLevel();
    $g_user['free_access'] = User::isFreeAccess();
}

function user($user_id) {
    $row = User::getInfoBasic($user_id);
    if($row) {
        $row['name_short'] = User::nameShort($row['name']);
    }
    return $row;
}
function guser($field = null)
{
    global $g_user;
    if ($field == null) {
        return $g_user;
    } else {
        return isset($g_user[$field]) ? $g_user[$field] : null;
    }
}
function guid()
{
    global $g_user;
    if (isset($g_user['user_id'])) {
        return $g_user['user_id'];
    } else {
        return '0';
    }
}

// FIND and show deafult photo
function urphoto($user_id, $gender = false) {
    return uphoto($user_id, 'r', $gender);
}
function usphoto($user_id, $gender = false) {
    return uphoto($user_id, 's', $gender);
}
function umphoto($user_id, $gender = false) {
    return uphoto($user_id, 'm', $gender);
}
function ubphoto($user_id, $gender = false) {
    return uphoto($user_id, 'b', $gender);
}
function ubmphoto($user_id, $gender = false) {
    return uphoto($user_id, 'bm', $gender);
}
function uphoto($user_id, $size = "s", $gender = false) {
    global $g;
    return $g['path']['url_files'] . User::getPhotoDefault($user_id, $size, false, $gender, DB_MAX_INDEX);
}

function demoImAdd()
{
    if (defined('IS_DEMO') && IS_DEMO) {
        define('WIDGET_DEMO_WHERE', ' AND session = "' . addslashes(session_id()) . '" ');
        define('WIDGET_DEMO_INSERT', ', session = "' . addslashes(session_id()) . '", session_date = NOW()');
    } else {
        define('WIDGET_DEMO_WHERE', '');
        define('WIDGET_DEMO_INSERT', '');
    }
    if (IS_DEMO && guid()) {
        $toUser = 12;
        global $g;
        if(isset($g['demo']['to_user'])) {
            $toUser = $g['demo']['to_user'];
        }
        Cim::firstOpenIm($toUser);
        /*DB::query("SELECT * FROM im_open WHERE to_user = " . to_sql($toUser) . " AND from_user=" . to_sql(guid(), "Number") . WIDGET_DEMO_WHERE);
        if (DB::num_rows() == 0) {
            DB::execute("INSERT INTO im_open SET z = " . to_sql(time()) . ", mid = 1, to_user = " . to_sql($toUser) . ", from_user=" . to_sql(guid(), "Number") . WIDGET_DEMO_INSERT);
        }*/
    }
}

function demoLogin()
{
    $logins = demoLoginId();
    $login = User::getInfoBasic($logins[array_rand($logins)], 'name');
    return $login;
}

function demoLoginId()
{
    $users = array(638, 452, 436, 445);
    return $users;
}

function getDemoCapitalCountry()
{
    if (IS_DEMO) {
        global $g;
        $capital = 56211;
        $capitals = array('mexico' => 27483,
                          'russia' => 32930,
                          'brazil' => 4462,
                          'german' => 16146,
                          'usa' => 56211,
                    );
        if (isset($capitals[$g['db_local']])) {
            $capital = $capitals[$g['db_local']];
        }
        $sql = 'SELECT * FROM geo_city WHERE city_id = ' . to_sql($capital);
        $cityInfo = DB::row($sql);
    } else {
            $cityInfo = IP::geoInfoCity();
    }
    return $cityInfo;
}

function getDemoMessages($typeDb = null, $path = '', $section = 'im')
{
/*
    if ($typeDb === null) {
        $typeDb = get_param('type_db');
    }
    $path .= 'bot_im/';
    $botTexts = "{$path}usa.txt";
    if (file_exists("{$path}{$typeDb}.txt")) {
        $botTexts = "{$path}{$typeDb}.txt";
    }

    $m = file_get_contents($botTexts);

    $m = str_replace("\r", "", $m);
    $m = explode("\n", $m);
    if ($isPrepare) {
        foreach ($m as $key => $value) {
            if (strripos($value, 'lily') !== false) {
                unset($m[$key]);
            }
        }
    }
*/
    static $botTexts = null;

    if($botTexts === null) {
        $path .= 'bot_im/';
        $botTextsFile = "{$path}usa.php";

        if ($typeDb === null) {
            $typeDb = get_param('type_db');
        }
        if($typeDb) {
            $botTextsFileLocal = "{$path}{$typeDb}.php";
            if (file_exists($botTextsFileLocal)) {
                $botTextsFile = $botTextsFileLocal;
            }
        }

        include $botTextsFile;
    }

    $m = isset($botTexts[$section]) ? $botTexts[$section] : $botTexts['im'];

    return $m;
}

function getDemoMsg($msgs = array(), $field = 'msg', $demoMsgs = null)
{
    if ($demoMsgs === null) {
        $demoMsgs = getDemoMessages();
    }
    $msgsArray = array();
    if ($msgs) {
        foreach ($msgs as $msgItem) {
            $msgsArray[] = $msgItem[$field];
        }

        if (count($msgsArray)) {
            foreach ($demoMsgs as $mIndex => $mValue) {
                if (in_array($mValue, $msgsArray)) {
                    unset($demoMsgs[$mIndex]);
                }
            }
        }
    }
    $msg = $demoMsgs[array_rand($demoMsgs)];

    return $msg;
}

//popcorn added 2024-0710 
function convertWordsByUnderLine($inputString) {
    // Ensure the input string is not null
    if ($inputString === null) {
        $inputString = '';
    }
    $inputString = strtolower($inputString);

    // Replace multiple spaces with a single space
    $inputString = preg_replace('/\s+/', ' ', $inputString);
    // Replace spaces with underscores
    return str_replace(' ', '_', $inputString);
}

class Plural {

    static function defaultMethod($count)
    {
        return intval($count != 1);
    }

    static function chinese($count)
    {
        return 0;
    }

    static function french($count)
    {
        return intval($count > 1);
    }

    static function lithuanian($count)
    {
        return intval(
                (($count % 10 == 1) && ($count % 100 != 11)) ? 0 : ( (($count%10>=2) && ($count % 100 < 10 || $count % 100 >= 20)) ? 1 : 2)
        );
    }

    static function russian($count)
    {
        return  intval( ( ($count % 10 == 1) && ($count % 100 != 11) ) ? 0 : (($count % 10 >= 2) && ($count % 10 <= 4) && ( ($count % 100 < 10) || ($count % 100 >= 20) ) ? 1 : 2) );
    }

    static function turkish($count)
    {
        return intval($count > 1);
    }

    // $langKey - без префикса "plural_X_"
    static function get($count,$langKey,$params=array())
    {
        $method = Common::getOption('lang_loaded', 'main');
        if(!method_exists('Plural', $method)) {
            $method = 'defaultMethod';
        }
        $plural = intval(Plural::$method($count));
        $lKeyValue = 'plural_' . $plural . '_' . $langKey;
        $result = lSetVars($lKeyValue, $params);
        return $result;
    }


}

define('MOBILE_VERSION_DIR', 'm');
