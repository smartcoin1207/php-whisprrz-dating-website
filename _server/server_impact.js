var timeout,
    last_id=0,
    lastNewMsgId=0,
    lastGift=0,
    updateStart=false,
    isVisibleMessages=false,
    isFbModeTitle='false',
    isVisiblePage=true,
    status_writing={},
    blink=[],
    users_list={},
    users_list_open_im={},
    timeoutSec=10000,
    timeoutSecServer=timeoutSec/1000*1.5,
    url_main,
    url_server='_server/update_server_ajax_impact.php',
    userTo=0,
    requestUserId=0,
    isCityLoad,
    limitStart=0,
    isOneChat='',
    getReadMsgFromIm=0;

function updateServer(){
    if(updateStart){
        var usersListOpenIm={}, status;
        if (isOneChat=='one_chat') {//general_chat
            prepareStatusWritingImOne();
            status=status_writing;
            getReadMsgFromIm=0;
            if(userAllowedFeature['message_read_receipts'] && $('[data-msg-user-id='+siteGuid+'].im_msg_one .msg_read.hide')[0]){
                getReadMsgFromIm=1;
            }
        }else if(isOneChat=='open_list_chats'){
            usersListOpenIm=JSON.stringify(users_list_open_im);
            prepareStatusWritingIm();
            status=JSON.stringify(status_writing);
            getReadMsgFromIm={};
            if(userAllowedFeature['message_read_receipts']){
                var $imOpen=$('.open_im_chat');
                $imOpen.each(function(){
                    var $el=$(this), $lastNoReadMsg=$el.find('.icon_check_msg.hide');
                    if($lastNoReadMsg[0]){
                        getReadMsgFromIm[$el.data('uid')]=1;
                    }
                })
                getReadMsgFromIm=JSON.stringify(getReadMsgFromIm);
            }
        }
        //console.log('AJAX:',isOneChat, getReadMsgFromIm);
        var data={
                cmd:'update_im',
                is_visible_messages:'true',
                users_list:JSON.stringify(users_list),
                users_list_open_im:usersListOpenIm,
                user_to:userTo,
                display:isOneChat,
                last_id:last_id,
                status_writing:status,
                get_read_msg_from_im:getReadMsgFromIm,
                is_mode_fb:isFbModeTitle,
                timeout_server:timeoutSecServer,
                page:activePage,
                request_user_id:requestUserId,
                city_counter_street_chat: $('#narrow_street_chat_count').length,
                city_counter_games: $('#column_narrow_game_choose_count').length,
                city_counter: $('#narrow_city_count').length,
                hide_im_on_page_city:isSiteOptionActive('hide_im_on_page_city')*1,
                geo_position:geoPoint
              };

		/* Live */
		var liveId = 1;
		var liveId = typeof _lsLiveId == 'undefined' ? 0 : _lsLiveId,
			liveIdEnd = typeof _lsLiveIdEnd == 'undefined' ? 0 : _lsLiveIdEnd;

		if (!liveId) {
			liveId = liveIdEnd;
		}

		if (liveId) {
			data.live_id=liveId;
			data.live_id_end=liveIdEnd;
			data.live_info=JSON.stringify(clStream.getInfo(liveId));
			//data.last_id=clStream.getLastId();
			if (_lsIsUserPresenter) {
				data.live_viewer=0;
				data.live_time=parseInt(new Date()/1000);
				data.live_list_viewers=JSON.stringify(listViwersUsers);
			} else {
				data.live_viewer=1;
				data.live_demo_viewer=_lsDemoViewerUrl?1:0;
			}

			data.comments_all = JSON.stringify(clStream.commentsCacheAll);
			data.comments = JSON.stringify(clStream.commentsCache);
			data.comments_replies = JSON.stringify(clStream.commentsReplyCache);
			data.comments_first = JSON.stringify(clStream.commentsFirst);
			data.comments_reply_last = JSON.stringify(clStream.itemCommentsReplyLast);
		}
		/* Live */

        if (isLoadCity()) {
            data['location']=city.idChangeLoc;
        }
        $.post(url_server, data,
                function(res){
                    var data=$.trim(checkDataAjax(res));
                    if (data!==false) {
                        var $data=$(data);
                        $data.filter('div.script').appendTo('#update_server');
                        if (activePage == 'messages.php') {
                            if(isOneChat=='general_chat'){
                                messages.update($data.filter('div.update_built_im'))
                            }else{
                                messages.updateOneChat($data.filter('div.update_built_im'))
                            }
                        } else if(imChats.initAjaxIm){
                            imChats.updateServer($data);
                        }
						/* Live */
						if (liveId) {
							clStream.updater(liveId, $data.filter('div.update_comments_live'));
						}
						/* Live */
                }
        });
    }
    clearTimeout(timeout);
    timeout=setTimeout('updateServer()', timeoutSec);
}

function initServer(){
    if(!ajax_login_status)return;
    updateStart=true;
    timeout=setTimeout('updateServer()', timeoutSec);
}

function isLoadCity(){
    return activePage == 'city.php' && typeof city == 'object' && city.isSceneLoaded;
}

$(function(){
    $.winFocus({
        blur: function(e) {
            if (ajax_login_status) {
                localStorage.setItem('is_fb_mode', 'true');
                isFbModeTitle = 'true';
                isVisiblePage = false;
                var data={cmd:'set_event_window'};
                if (isLoadCity()) {
                    data['location']=city.idChangeLoc;
                }
                $.post(url_server,data);
            }
        },
        focus: function(e) {
            if (ajax_login_status) {
                localStorage.setItem('is_fb_mode', 'false');
                isFbModeTitle = 'false';
                localStorage.removeItem('is_title');
                localStorage.setItem('is_title', 'true');
                document.title = siteTitle;
                isVisiblePage = true;
                if (activePage == 'messages.php' && isOneChat == 'one_chat') {
                    $.post(url_server+'?cmd=read_msg',{display:isOneChat,is_mode_fb:'false',user_id:userTo},function(res){
                        var count=checkDataAjax(res);
                        if(count&&$.isNumeric(count)){
                            messages.updateCounter(count,true);
                        }
                    })
                }
                if (isLoadCity() && city.mainChatPanel.is('.is_open')) {
                    city.setMessagesUserRead();
                }
            }
        }
    });
    $(window).on('storage', function(e) {
        var event = e.originalEvent;
        if (event.key === 'is_title') {
            $('title').text(siteTitle);
        } else if (event.key === 'is_fb_mode') {
            isFbModeTitle = event.newValue;
        } else if (event.key === 'title_site_counter') {
            $('title').text(event.newValue+' '+siteTitle);
        }
    });
});