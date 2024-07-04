<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "login";
include("./_include/core/main_start.php");

if(!Common::isOptionActive('live')) {
    redirect(Common::getHomePage());
}

LiveStreaming::checkAviableLiveStreaming();

// checkAccessFeatureByPayment('live_streaming', false);

User::accessCheckToProfile(true);

class CLiveStreaming extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $typeChat = 'webrtc';
        $html->setvar('type_chat', $typeChat);
        $liveView = get_param_int('live');
        $group_id = get_param('group_id', "0");

        $guid = $g_user['user_id'];
        $clientId = User::getParamUid($guid);

        $html->setvar('client_guid', $guid);

        $lStartStreaming = l('start_striming_presenter');
        $lStopStreaming = l('stop_striming_presenter');
        if ($guid != $clientId) {
            $lStartStreaming = l('start_striming_viewer');
            $lStopStreaming = l('stop_striming_viewer');
        }
        $html->setvar('start_striming_j', toJs($lStartStreaming));
        $html->setvar('stop_striming_j', toJs($lStopStreaming));
        $html->setvar('start_striming', $lStartStreaming);

        $isPresenter = intval($guid == $clientId);
        $html->setvar('user_presenter', $isPresenter);

		$price = Pay::getServicePrice('live_stream', 'credits');
		$html->setvar('ls_pay_price', $price);


        $isStreamStart = get_param_int('stream');
        if ($isStreamStart) {
            $isAutoConnect = 0;
        } else {
            $isAutoConnect = $isPresenter ? 0 : intval(Common::isOptionActive('live_streaming_auto_connect'));
        }

        $isParseChat = true;
        $userInfo = User::getInfoBasic($clientId);
        $pageTitle = '';
		$pageTitleAll = '';

        $liveDemo = false;
        $liveInfo = array();
        if ($userInfo) {
            if ($isPresenter) {
                $pageTitle = l('page_title_my');
                $langMobile = loadLanguageSiteMobile(Common::getOption('lang_loaded', 'main'));

                global $p;
                $pSrc = $p;
                $p = 'videochat.php';
                $html->setvar('app_does_not_have_permissions_to_access_the_camera_and_the_microphone', toJs(l('app_does_not_have_permissions_to_access_the_camera_and_the_microphone', $langMobile)));
                $p = $pSrc;
            } else {
                //$name = User::nameOneLetterFull($userInfo['name']);
                $name = User::nameShort($userInfo['name']);
                $pageTitle = lSetVars('page_title', array('name' => $name));//if (IS_DEMO && get_param('demo')) {
                $userLiveNowId = LiveStreaming::getUserLiveNowId($clientId);
                $videoInfo = array();
                if (!$liveView && $userLiveNowId) {
                    $liveView = $userLiveNowId;
                }
                if (!$userLiveNowId || ($liveView && $liveView != $userLiveNowId)) {
                    $isParseChat = false;
                } elseif($liveView) {
                    $liveInfo = LiveStreaming::getInfoLive($liveView);
                    if (!$liveInfo || !$liveInfo['status']) {
                        $isParseChat = false;
                    } elseif($liveInfo) {
                        $videoInfo = DB::one('vids_video', 'id = ' . to_sql($liveInfo['video_id']));
                        if ($videoInfo) {
							if(!in_array($guid, explode(',', $videoInfo['users_reports']))){
								$html->parse('ls_report_user', false);
							}
                            if (trim($videoInfo['subject'])) {
                                $pageTitle = trim($videoInfo['subject']);
								$pageTitleAll = $pageTitle;
                            }
                            if (IS_DEMO && $liveInfo['demo']) {
                                $liveDemo = true;
                                $html->setvar('ls_demo_viewer_live_id', $liveView);
                                $html->setvar('ls_demo_viewer_url', toJs($g['path']['url_files'] . User::getVideoFile($videoInfo, 'video_src', '')));
                            }
                        }
                    }
                }
            }

			if ($pageTitleAll) {
				$html->setvar('page_title_all', toAttr($pageTitleAll));
			}
            $html->setvar('page_title', $pageTitle);

            if ($isParseChat) {
                if ($isPresenter || User::isOnline($clientId, $userInfo) || $liveDemo) {
                    $clientIdStr = LiveStreaming::getIdByLiveStreaming($clientId);
                    if (IS_DEMO) {
                        $session = '';
                        if ($isPresenter) {
                            $session = addslashes(session_id());
                        } elseif (!$liveDemo && isset($liveInfo['demo_session'])) {
                            $session = $liveInfo['demo_session'];
                        }
                        if ($session) {
                            $clientIdStr = $clientIdStr  . '_' . $session;
                        }
                        $html->setvar('client_id_session_demo', $session);
                    }

                    $html->setvar('client_id', $clientIdStr);

                } else {
                    $isParseChat = false;
                }
            }
        } else {
            $isParseChat = false;
        }

        $html->setvar('user_client_uid', $guid);
        $html->setvar('user_client_url', User::url($guid));
        $html->setvar('user_client_photo', User::getPhotoDefault($guid, 'r'));

        if (!$isParseChat && !$isPresenter) {
            $isAutoConnect = 0;
        }

        //if (!$isPresenter && !$isStreamStart && $isAutoConnect) {
        if (!$isPresenter && !$isStreamStart) {
            $html->setvar('page_url_start', Common::pageUrl('live_', $clientId, $liveView));
            $html->setvar('page_live_view_id', $liveView);
            $html->parse('viewer_auto_connect_js');
        }
        $html->setvar('auto_connect', $isAutoConnect);

        if ($isParseChat) {
			ImAudioMessage::parseControlAudioCommentPost($html);
			
            LiveStreaming::parseBlockComments($html, 0);//2667

            if (!$isPresenter && !$isStreamStart && $isAutoConnect) {
                $html->parse('module_hide_btn_connect_bl', false);
                $html->parse('module_hide_btn_connect', false);
            } elseif ($isPresenter) {
                $html->parse('presenter_start_js', false);
                $html->parse('module_hide_btn_connect_bl', false);
                $html->parse('module_hide_btn_connect', false);
            }

            $html->setvar('is_mobile', Common::isMobile(false, true, true));
            $html->setvar('media_server', $g['webrtc_app_live_streaming']);//. '1'

            $html->parse('live_streaming_webrtc_js');
        } else {
            if (!$pageTitle) {
                $html->setvar('page_title', l('page_title_empty'));
            }

            if ($liveView) {
                $msgAlert = $isPresenter ? toJs(l('you_cannot_attend_your_live')) : '';
                $html->setvar('no_live_streaming_js_msg', $msgAlert);
            }
            $html->parse('no_live_streaming_js');
        }

        TemplateEdge::parseColumn($html, $clientId);

		parent::parseBlock($html);
	}
}

$optionTmplName = Common::getTmplName();
if ($optionTmplName == 'edge') {
	$tmpl = getPageCustomTemplate('live_streaming.html', 'live_streaming_template');

} else {
	$tmpl = array('main' => $g['tmpl']['dir_tmpl_main'] . 'live_streaming.html',
				  'live_streaming' => $g['tmpl']['dir_tmpl_main'] . '_live_streaming.html');
}


$page = new CLiveStreaming("", $tmpl);
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

if (Common::isParseModule('profile_colum_narrow')){
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
}

if (Common::isParseModule('profile_head')){
    $profileHead = new ProfileHead('profile_head', $g['tmpl']['dir_tmpl_main'] . '_profile_head.html');
    $profileHead::setUserId(get_param('id', 0));
    $page->add($profileHead);
}

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");