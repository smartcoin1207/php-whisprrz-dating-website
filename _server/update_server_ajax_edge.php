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

$groupId = Groups::getParamId();
$groupIdEvent = Groups::getEventId();
$isPageGroup = Groups::isPage();
$responseData = false;
$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$cmd = get_param('cmd');
$curPage = get_param('page');
$requestUid = get_param('request_user_id');
$display = get_param('display');

$scriptJs = '';
function addJsScript(&$scriptJs, $script){
    $scriptJs .= $script;
}

if ($cmd == 'read_msg') {
    $responseData = CIm::setMessageAsRead();
} elseif ($cmd == 'activate_im') {
    CIm::setMessageAsRead();
    CIm::setLastViewedIm();
	$responseData = array('count' => CIm::getCountNewMessages(), 'enabled' => CIm::getCountAllMsgIm());
} elseif ($cmd == 'set_event_window') {
    CIm::setWindowEvent();
    if (get_param('location')) {
        City::setWindowEvent();
    }
} elseif ($cmd == 'update_im' && $guid) {
    $responseData = '';
    $isVisibleMessages = get_param('is_visible_messages');
    $isFbModeTitle = get_param('is_mode_fb');
    if ($isVisibleMessages == 'true') {
        CIm::setWriting();
        $page = new CIm('', "{$dirTmpl}_pp_messages_list_msg.html");
        $responseData .= '<div class="update_msg_im">' . getParsePageAjax($page) . '</div>';
    }

    $countNewMessages = CIm::getCountNewMessages();
    if ($isFbModeTitle == 'true'){
        $lastImMsg = get_session('window_last_im_msg');
        $where = '`to_user` = ' . to_sql($g_user['user_id'], 'Number')
               . ' AND id > ' . to_sql($lastImMsg, 'Number')
               . ' AND `is_new` = 1' . CIm::getWhereNoSysytem();
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
            || ($countEventLast && !($countNewMessages + $cityCountNew))) {
            $titleCounter = $countEvent ? lSetVars('title_site_counter', array('count' => $countEvent)) : '';
            $setEvent = "localStorage.setItem('title_site_counter', '" . $titleCounter . "');
                         $('title').text('" . $titleCounter . " '+siteTitle);";
            addJsScript($scriptJs, $setEvent);
            set_session('window_count_event_last', $countEvent);
            set_session('window_count_city_event_last', $cityCountEventLast);
        }
    }

    $counters = array();
    /* Notification new friends request and update friends list */
    if (Common::isOptionActive('friends_enabled') || $groupId) {
        $isUpdatePending = true;
        if ($groupId) {
            $isUpdatePending = false;
            $groupInfo = Groups::getInfoBasic($groupId);
            if ($groupInfo && $groupInfo['private'] == 'Y' && $groupInfo['user_id'] == $guid) {
                $isUpdatePending = true;
            }
        }
        if($isUpdatePending){
            if ($groupId) {
                $countPendingFriends = Groups::getNumberRequestsToSubscribePending($groupId);
            } else {
                $countPendingFriends = TemplateEdge::getNumberFriendsAndSubscribersPending();//User::getNumberRequestsToFriendsPending();
            }

            $counters['friends_pending'] = array('count' => $countPendingFriends, 'enabled' => $countPendingFriends);
            if ($countPendingFriends) {
                $where = '';
                $friendsNotification = get_param('friends_notification');
                if ($friendsNotification) {
                    $where = ' AND FR.created_at > ' . to_sql($friendsNotification);
                }
                if ($groupId) {
                    $friendsPendingList = Groups::getSubscribePending($groupId, $where, 'ASC');
                } else {
                    $friendsPendingList = TemplateEdge::getFriendsPending($where, 'ASC', true);
                }
                if ($friendsPendingList) {
                    addJsScript($scriptJs, 'clFriends.updateListNotification(' . json_encode($friendsPendingList) . ');');
                }
            }
        }

        if (!$groupId) {
            if (LiveStreaming::isAviableLiveStreaming() && $requestUid && $requestUid != $guid) {
                $userLiveNowId = LiveStreaming::getUserLiveNowId($requestUid);
                $url = $userLiveNowId ? Common::pageUrl('live_id', $requestUid, $userLiveNowId) : '';
                addJsScript($scriptJs, 'clProfile.updateStatusLiveProfile(' . $requestUid . ',\'' . $url . '\');');
            }

            addJsScript($scriptJs, 'clFriends.updateFriends(' . json_encode(TemplateEdge::getListFriends(null, true, null, true)) . ',true);');
        }
        if (get_param_int('get_list_friends')) {//What for?
            addJsScript($scriptJs, 'clFriends.updateFriends(' . json_encode(TemplateEdge::getListFriends()) . ');');
        }
    }
    /* Notification new friends request and update friends list */

    /* Notification new events */
	/* Clear old events older than a week */
	User::getListGlobalEvents(null, 'ASC', $groupIdEvent, true);
	/* Clear old events older than a week */


    $events = array(
        'new_count' => User::getNumberGlobalEvents(false, $groupIdEvent),
        'new_list'  => User::getListGlobalEvents(null, 'ASC', $groupIdEvent)
    );

    addJsScript($scriptJs, 'clEvents.updateEvents(' . json_encode($events) . ');');


    $countNewTask = TaskCalendar::getCountOpenTasksByCurrentDay();
    $newTasksTitle = TaskCalendar::getNotifTitle($countNewTask);
    $counters['new_tasks'] = array('count' => $countNewTask,
                                   'enabled' => $countNewTask,
                                   'title'   => toJs($newTasksTitle)
                            );

    /* Notification new events */
    $lastNewMessageInfo = CIm::getLastNewMessageInfo();
    $counters['new_message'] = array('count' => $countNewMessages,
                                     'enabled' => CIm::getCountAllMsgIm(),
                                     'uid' => $lastNewMessageInfo['uid'],
                                     'url_notif' => User::url($guid, null, array('show' => 'message', 'uid_sender' => $lastNewMessageInfo['uid'])),
                                     'msg' => $lastNewMessageInfo['message']
                               );


    $counters = json_encode($counters);
    addJsScript($scriptJs, 'clCounters.update(' . $counters . ');');

    addJsScript($scriptJs, 'mobileAppSetBadgeNumber(' . $countNewMessages . ');');

    $lastNewMessageInfo = City::getLastNewMessageInfo();
    addJsScript($scriptJs, 'mobileAppCityNotification(' . json_encode($lastNewMessageInfo) . ');');

    //if ($curPage == 'search_results.php') {
        if ($requestUid && $requestUid != $guid) {
            addJsScript($scriptJs, "clProfile.updateOnlineStatus(" . intval(User::isOnline($requestUid, null)) . "," . intval(User::isOnline($requestUid, null, true)) . ");");
        }
    //}

    /* Chat */
    $typeChat = array('audio', 'video');
    foreach ($typeChat as $type) {
        $chatData = Chat::update($type);
        if (Chat::isAction($chatData)) {
            addJsScript($scriptJs, 'cl' . ucfirst($type) . "Chat.request(" . json_encode($chatData) . ");");
        }
    }
    $chatData = CityStreetChat::update();
    if ($chatData) {
        addJsScript($scriptJs, "clCityStreetChat.request(" . json_encode($chatData) . ");");
    }

    addJsScript($scriptJs, "clProfile.updateServerMyData(" . User::accessCheckFeatureSuperPowersGetList() . ");");
    addJsScript($scriptJs, "setGUserOptions(" . Common::getGUserJs() . ");");

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
} elseif ($cmd == 'chat_invite') {
    $responseData = Chat::invite();
} elseif ($cmd == 'chat_reject') {
    $responseData = Chat::reject();
} elseif ($cmd == 'chat_talk') {
    $responseData = Chat::talk();
} elseif ($cmd == 'chat_paid') {
    $responseData = Chat::paid();
} elseif ($cmd == 'chat_request_check') {//For notification
    $type = get_param('type');
    $uid = get_param_int('uid');
    if ($type == 'street') {
        $responseData = CityStreetChat::checkRequest($uid, get_param('data'));
    } else {
        $responseData = Chat::checkRequest($type, $uid);
    }
} elseif ($cmd == 'city_street_chat_invite') {
    $responseData = CityStreetChat::invite();
} elseif ($cmd == 'city_street_chat_reject') {
    $responseData = CityStreetChat::reject();
} elseif ($cmd == 'city_street_chat_start') {
    $responseData = CityStreetChat::start();
}


if (isset($responsePage)) {
    echo getResponsePageAjaxByAuth($guid, $responsePage);
}

if (isset($responseData)) {
    echo getResponseAjaxByAuth($guid, $responseData);
}

DB::close();