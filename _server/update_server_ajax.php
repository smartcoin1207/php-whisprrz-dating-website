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

/* Message */
if ($cmd == 'read_msg') {
    $responseData = CIm::setMessageAsRead();
} elseif ($cmd == 'activate_im') {
    $responseData = null;
    if ($isAuth) {
        CIm::setMessageAsRead();
        CIm::setLastViewedIm();
	    $responseData = true;
    }
} elseif ($cmd == 'set_event_window') {
    CIm::setWindowEvent();
    if (get_param('location')) {
        City::setWindowEvent();
    }
} elseif ($cmd == 'update_im') {

    //if ($isVisibleMessages == 'true') {
        //$responsePage = null;
    //} else {
        $responseData = null;
    //}
    if ($isAuth) {
        $isVisibleMessages = get_param('is_visible_messages');
        $isFbModeTitle = get_param('is_mode_fb');
        $responseData = '';
		if ($isVisibleMessages == 'true') {
            //if ($isFbModeTitle == 'false') {
                CIm::setWriting();
            //}
			$page = new CIm('', "{$dirTmpl}_pp_messages_list_msg.html");
			$responseData .= getParsePageAjax($page);
		} else {
            if (Common::isOptionActive('message_notifications_active')) {
                $responseData .= "<script>Messages.showNotifAllMsg(" . CIm::getDataJsNewMessages() . ");</script>";
                $responseData .= User::getDataJsNewVisitors();
            }
            $counter = CIm::getCountNewMessages();
            $responseData .= "<script>Messages.set_counter_all(" . $counter . "," . intval($g_user['sound'] == 1) . ");</script>";
        }

        $isVisiblePage = get_param('is_visible_page');
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
                $responseData .="<script>localStorage.setItem('title_site_counter', '" . $titleCounter . "');
                                         $('title').text('" . $titleCounter . " '+siteTitle);</script>";
                set_session('window_count_event_last', $countEvent);
                set_session('window_count_city_event_last', $cityCountEventLast);
            }
        }

        /* Update */
        /* Counters */
        /*1-#narrow_visitors_count
        2-#narrow_rated_photos_count
        3-#narrow_want_count
        4-#narrow_mutual_count
        5-#narrow_private_photo_count
        6-#narrow_blocked_count
        7-#narrow_city_count
        8-#narrow_friends_count*/

        $viewers = User::getNumberViewersMeProfiles();
        $responseData .="<script>
                          updateCounter('#narrow_mutual_count'," . MutualAttractions::getNumberMutualAttractions() . ");
                          updateCounter('#narrow_want_count'," . MutualAttractions::getNumberMutualAttractions('Wanted') . ");
                          updateCounterRated(" . $viewers['count'] . "," . $viewers['new'] . ",'#narrow_visitors_count');
                          updateCounter('#narrow_friends_count'," . User::getNumberFriendsAndPending() . ");
                          updateCounter('#narrow_private_photo_count'," . User::getNumberFriends() . ");
                         </script>";

        if (Common::isOptionActive('flashchat')) {
            $responseData .="<script>updateCounter('#narrow_general_chat_count'," . Flashchat::getNumberUsersVisitors() . ");</script>";
        }
        if (Common::isOptionActive('wink')) {
            $isNew = DB::count('users_interest', "`new` = 'Y' AND `user_to` = " . to_sql($guid));
            $countWink = DB::count('users_interest', "`user_to` = " . to_sql($guid));
            $responseData .="<script>updateCounterRated(" . $countWink . "," . $isNew . ",'#narrow_winks_from_count');</script>";
        }

		if(Common::isOptionActive('live_streaming')){
			$responseData .= '<script>updateCounter("#narrow_live_list_count", ' . LiveStreaming::getTotalLiveNow() . ');</script>';
			$responseData .= '<script>updateCounter("#narrow_live_list_finished_count", ' . LiveStreaming::getTotalLiveFinished() . ');</script>';
		}
        /* Counters */
        /* City */
        if ($curPage == 'city.php') {
            $responseData .="<script>city.updateNumberUsersVisitors(" . json_encode(City::getNumberUsersVisitors()) . ");</script>";
        } elseif (Common::isModuleCityActive()) {
            $numberCity = City::getNumberUsersVisitors();
            if (CustomPage::isParseCity()) {
                $responseData .="<script>updateCounter('#narrow_city_count'," . $numberCity['all_number'] . ");</script>";
            }
            if (City::isActiveStreetChat()) {
                $responseData .="<script>updateCounter('#narrow_street_chat_count'," . $numberCity[12] . ");</script>";
            }

        }

        /* City */
        /* Rating photos */
        $usersRatedMyPhotoNew = CProfilePhoto::getNumberUsersRatedMePhoto(null, true);
        if ($usersRatedMyPhotoNew) {
            $responseData .="<script>updateCounterRated(" . CProfilePhoto::getNumberUsersRatedMePhoto() . ",true);</script>";
        }
        /* Rating photos */
        if (Common::isOptionActive('credits_enabled')) {
            $responseData .="<script>Profile.setBalansCredit('" . $g_user['credits'] . "');</script>";
        }

        if ($curPage == 'search_results.php') {
            if ($requestUid && $requestUid != $guid) {
                $responseData .= "<script>Profile.updateOnlineStatus(" . intval(User::isOnline($requestUid, null)) . "," . intval(User::isOnline($requestUid, null, true)) . ");</script>";
            }
        }

        /* Gift */
        $curPage = get_param('page');
        if (in_array($curPage, array('search_results.php', 'profile_view.php'))) {
            $requestUserId = get_param('request_user_id');
            if ($curPage == 'search_results.php') {
                User::setUserVisitor($guid, $requestUserId);
            }
            /*if (($guid == $requestUserId && $curPage == 'search_results.php')
                || $curPage == 'profile_view.php') {
                $isUserBroadcost = intval(get_param('is_user_broadcost'));
                if ($isUserBroadcost) {
                    User::setLastBroadcast();
                }
                $responseData .="<script>updateListenerBroadcast(" . json_encode(User::getUsersVisitors()) . ");</script>";
            }
            if ($guid != $requestUserId && $curPage == 'search_results.php') {
                $responseData .="<script>connectionToBroadcast(" . User::isUserBroadcast($requestUserId) . ");</script>";
            }*/
            $page = new ProfileGift('', "{$dirTmpl}_profile_gift.html");
            $page::setCmd('update');
            $responseData .= getParsePageAjax($page);
            if (Common::isOptionActive('city')) {
                $responseData .="<script>updateStatusCity(" . City::isUserOnline($requestUserId) . ");</script>";
            }

        }
        /* Gift */
        /* Spotlight */
        if (!Common::isOptionActive('free_site')) {
            $time = time();
            if(get_session('spotlight_last_update_time') < time() - 60) {
                $page = new Spotlight('', "{$dirTmpl}_spotlight_items.html");
                $page = htmlToJs(getParsePageAjax($page));
                //if (!empty($page)) {
                    $responseData .= "<script>Profile.updateSpotlight('" . $page . "');</script>";
                //}
                set_session('spotlight_last_update_time', $time);
            }
        }
        /* Spotlight */
        /* Chat */
        $typeChat = array('audio', 'video');
        foreach ($typeChat as $type) {
            $chatData = Chat::update($type);
            if (Chat::isAction($chatData)) {
                $responseData .= "<script>" . $type . "Chat.request(" . json_encode($chatData) . ");</script>";
            }
        }
        $chatData = CityStreetChat::update();
        if ($chatData) {
            $responseData .= "<script>cityStreetChat.request(" . json_encode($chatData) . ");</script>";
        }
        /* Chat */
        $geoPointData = User::updateGeoPosition();
        if($geoPointData !== false){
            $responseData .= '<script>setGeoPointData(' . $geoPointData . ');</script>';
        }

        $responseData .= "<script>Profile.updateServerMyData(" . User::accessCheckFeatureSuperPowersGetList() . ");</script>";
        /* Update */

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



/* Message */

// URBAN
if (isset($responsePage)) {
    echo getResponsePageAjaxByAuth($isAuth, $responsePage);
}

if (isset($responseData)) {
    echo getResponseAjaxByAuth($isAuth, $responseData);
}

DB::close();