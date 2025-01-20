<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = '../';
include('../_include/core/main_start.php');

$guid = guid();

$siteGuid = get_param('site_guid', false);
if ($siteGuid !== false && $siteGuid != $guid) {
    echo getResponseAjaxByAuth(false);
    die();
}

global $g;
global $g_user;

$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$cmd = get_param('cmd');
$isAuth = ($guid) ? true : false;
$curPage = get_param('page');
$requestUid = get_param('request_user_id');
$display = get_param('display');

$scriptJs = '';
function addJsScript(&$scriptJs, $script){
    $scriptJs .= $script;
}
/* Message */
if ($cmd == 'activate_im') {
    $responseData = CIm::setMessageAsRead();
    CIm::setLastViewedIm();
    if ($display == 'open_list_chats') {
        $_GET['cmd'] = 'update_im';
        $page = new CIm('', "{$dirTmpl}_pp_list_chats_open_item.html");
		$responseData = '<div class="update_built_im">' . getParsePageAjax($page) . '</div>';
        $responseData .= '<div class="script"><script>imChats.updateCounter(' . CIm::getCountNewMessagesFromListUsers() . ');</script></div>';
    }
} elseif ($cmd == 'set_read_msg') {
    $responseData = CIm::setMessageAsRead();
    if ($display == 'open_list_chats') {
        $responseData = CIm::getCountNewMessagesFromListUsers(false);
    }
} elseif ($cmd == 'set_event_window') {
    CIm::setWindowEvent();
    if (get_param('location')) {
        City::setWindowEvent();
    }
} elseif ($cmd == 'read_msg') {
    $responseData = CIm::setMessageAsRead();
} elseif ($cmd == 'update_im') {
    if ($isAuth) {
        $isFbModeTitle = get_param('is_mode_fb');
        $responseData = '';

		//if ($isVisibleMessages == 'true') {
            //if ($isFbModeTitle == 'false') {
                //CIm::setWriting();
            //}
			//$page = new CIm('', "{$dirTmpl}_pp_messages_list_msg.html");
			//$responseData .= getParsePageAjax($page);
		//} else {
            //if (Common::isOptionActive('message_notifications_active')) {
                //$responseData .= "<script>Messages.showNotifAllMsg(" . CIm::getDataJsNewMessages() . ");</script>";
               // $responseData .= User::getDataJsNewVisitors();
            //}
       // }

        $hideImOnPages = array('email_not_confirmed.php');
        $isUpdateIm = !in_array($curPage, $hideImOnPages);
        if ($curPage == 'city.php') {
            $isUpdateIm = !intval(get_param('hide_im_on_page_city'));
        }
        if ($curPage == 'messages.php') {
            /* Update IM */
            if ($display == 'one_chat') {
                CIm::setWritingMobileOneChat();
                $tmpl = "{$dirTmpl}_messages_user_msg.html";
            } else {
                $tmpl = array('main' => "{$dirTmpl}_messages_list_users.html",
                              'msg_list' => "{$dirTmpl}_messages_list_msg.html"
                );
            }
            $page = new CIm('', $tmpl);
			$responseData .= '<div class="update_built_im">' . getParsePageAjax($page) . '</div>';
            /* Update IM */
        } elseif ($isUpdateIm) {
            CIm::setWriting();
            /* Update list IM */
            $_POST['display'] = 'update_msg_open_list_chats';
            $page = new CIm('', "{$dirTmpl}_pp_list_chats_open_im_msg.html");
            $responseData .= '<div class="update_msg_im">' . getParsePageAjax($page) . '</div>';
            $_POST['display'] = 'open_list_chats';
            $page = new CIm('', "{$dirTmpl}_pp_list_chats_open_item.html");
            $responseData .= '<div class="update_built_im">' . getParsePageAjax($page) . '</div>';
            $responseData .= '<div class="script_after"><script>imChats.updateCounter(' . CIm::getCountNewMessagesFromListUsers() . ');</script></div>';
            /* Update list IM */
        }

        if ($curPage != 'city.php') {

            $scriptJs .= Menu::updateCounterAjaxImpact($curPage);

            $numbersCity = null;

            if (get_param('city_counter_street_chat') && City::isActiveStreetChat()) {
                if ($numbersCity === null) {
                    $numbersCity = City::getNumberUsersVisitors();
                }
                $scriptJs .= 'updateCounter("#narrow_street_chat_count", ' . $numbersCity[12] . ');';

                $numbersCity['all_number'] -= $numbersCity[12];
            }

            if (get_param('city_counter_games') && City::isActiveGames()) {
                if ($numbersCity === null) {
                    $numbersCity = City::getNumberUsersVisitors();
                }

                $cityNumberUsersGames = City::getNumberUsersGames($numbersCity);
                $scriptJs .= 'updateCounter("#column_narrow_game_choose_count", ' . $cityNumberUsersGames . ');';

                $numbersCity['all_number'] -= $cityNumberUsersGames;
            }

            if(get_param('city_counter') && CustomPage::isParseCity()) {
                if ($numbersCity === null) {
                    $numbersCity = City::getNumberUsersVisitors();
                }
                $scriptJs .= 'updateCounter("#narrow_city_count", ' . $numbersCity['all_number'] . ');';
            }
			if(Common::isOptionActive('live_streaming')){
				$scriptJs .= 'updateCounter("#narrow_live_list_count", ' . LiveStreaming::getTotalLiveNow() . ');';
				$scriptJs .= 'updateCounter("#narrow_live_list_finished_count", ' . LiveStreaming::getTotalLiveFinished() . ');';
			}
            /* Upadet counter */
        } else {
            //$responseData .="<script>city.updateNumberUsersVisitors(" . json_encode(City::getNumberUsersVisitors()) . ");</script>";
        }

        /* Update tab Title(1)*/
        if ($isFbModeTitle == 'true'){
            $lastImMsg = get_session('window_last_im_msg');
            $where = '`to_user` = ' . to_sql($g_user['user_id'], 'Number')
                   . ' AND id > ' . to_sql($lastImMsg, 'Number')
                   . ' AND `is_new` = 1';
            $count = DB::count('im_msg', $where);

            $cityCount = 0;
            $cityCountWindowEvent = 0;
            $cityCountNew = 0;
            $cityRoom = get_param('location');
            $cityCountEventLast = 0;
            if ($cityRoom) {
                $cityCountNew = City::getCountNewMessages();
                $cityCount = City::getCountNewMessages(null, null, get_session('window_last_city_msg'));
                $cityCountWindowEvent = $cityCount - get_session('window_count_city_event', 0);
                $cityCountEventLast = get_session('window_count_city_event_last', 0);
            }

            $countEvent = $count - get_session('window_count_event', 0) + $cityCountWindowEvent;
            $countEventLast = get_session('window_count_event_last', 0) + $cityCountEventLast;

            if ($countEvent > 0
                || ($countEventLast && !(CIm::getCountNewMessages() + $cityCountNew))) {
                $titleCounter = $countEvent ? lSetVars('title_site_counter', array('count' => $countEvent)) : '';
                $scriptJs .= "localStorage.setItem('title_site_counter', '" . $titleCounter . "');
                              $('title').text('" . $titleCounter . " '+siteTitle);";
                set_session('window_count_event_last', $countEvent);
                set_session('window_count_city_event_last', $cityCountEventLast);
            }
        }
        /* Update title tab (1)*/

        if ($curPage == 'search_results.php') {
            if ($requestUid && $requestUid != $guid) {
                $scriptJs .= "Profile.updateOnlineStatus(" . intval(User::isOnline($requestUid, null)) . "," . intval(User::isOnline($requestUid, null, true)) . ");";
            }
        }

        /* Chat */
        $typeChat = array('audio', 'video');
        foreach ($typeChat as $type) {
            $chatData = Chat::update($type);
            if (Chat::isAction($chatData)) {
                $scriptJs .=  $type . "Chat.request(" . json_encode($chatData) . ");";
            }
        }
        $chatData = CityStreetChat::update();
        if ($chatData) {
            $scriptJs .=  "cityStreetChat.request(" . json_encode($chatData) . ");";
        }
        /* Chat */
        $scriptJs .= "Profile.updateServerMyData(" . User::accessCheckFeatureSuperPowersGetList() . ");";

        if (Common::isOptionActive('credits_enabled')) {
            $scriptJs .= 'updateCounterText("#credits_balans_header", "' . lSetVars('credit_balance', array('credit' => $g_user['credits'])) . '");';
        }

        $geoPointData = User::updateGeoPosition();
        if($geoPointData !== false){
            $scriptJs .= 'setGeoPointData(' . $geoPointData . ');';
        }

		/* Live */
		$liveId = get_param_int('live_id');
		if ($liveId) {
			$liveViewer = get_param_int('live_viewer');
			$liveIdEnd = get_param_int('live_id_end');

			if ($liveViewer) {
				LiveStreaming::updateMyViewer();
			} else {//Presenter
				LiveStreaming::setStatusLive();
				LiveStreaming::updateViewers();
			}

			$tmplsLive = array('main' => "{$dirTmpl}_live_streaming_ajax.html",
							   'likes' => "{$dirTmpl}_live_streaming_likes.html",
							   'comments_list' => "{$dirTmpl}_live_streaming_comment_item.html");
			if (!$liveIdEnd) {
				$tmplsLive['list_viewers'] = "{$dirTmpl}_live_streaming_list_viewers.html";
			}

			$page = new LiveStreaming('', $tmplsLive);
			$responseData .= '<div class="update_comments_live">' . getParsePageAjax($page) . '</div>';
		}
		/* Live */

        if ($scriptJs) {
            $responseData .= '<div class="script"><script>' . $scriptJs . '</script></div>';
        }
    }
} elseif ($cmd == 'chat_invite') {
    $responseData = Chat::invite();
} elseif ($cmd == 'chat_reject') {
    $responseData = Chat::reject();
} elseif ($cmd == 'chat_talk') {
    $responseData = Chat::talk();
} elseif ($cmd == 'chat_paid') {
    $responseData = Chat::paid();
} elseif ($cmd == 'city_street_chat_invite') {
    $responseData = CityStreetChat::invite();
} elseif ($cmd == 'city_street_chat_reject') {
    $responseData = CityStreetChat::reject();
} elseif ($cmd == 'city_street_chat_start') {
    $responseData = CityStreetChat::start();
}


if (isset($responsePage)) {
    echo getResponsePageAjaxByAuth($isAuth, $responsePage);
}

if (isset($responseData)) {
    echo getResponseAjaxByAuth($isAuth, $responseData);
}

DB::close();