var CMessages = function(guid, guidPhoto, imHistoryMessages) {
    var $this=this;
    this.guid = guid;
    this.guidPhoto = guidPhoto;
    this.imHistoryMessages = imHistoryMessages*1;
    this.$pp=[];
    this.userTo=0;
    this.groupId=0;
    this.lastEnteredMessage='';
    this.dur=300;
    this.isInitLoad=false;
    this.imH = 0;
    this.imW = 0;
    this.eventState = {};
    this.groupOwner = '';

    this.userCache = {};
    this.isShowOnlyGroup = false;

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
        }
    }

    this.isCityPage = function(){
        return typeof isPageCity != 'undefined' && isPageCity;
    }

    this.show = function(uid,$el,groupUserId,groupId){
        closeNavbarMenuCollapse(function(){
            $this.showPrepare(uid,$el,groupUserId,groupId);
        })
    }

    this.showPrepare = function(uid,$el,groupUserId,groupId){
        //if(notPageLoad())return;
        uid=uid||0;
        groupUserId = defaultFunctionParamValue(groupUserId, false);
        groupId = defaultFunctionParamValue(groupId, false);
        $el=$el||[];
        if(!uid&&$el[0]&&$el.is('.disabled')){
            return;
        }

        //if($this.$pp.is('.animated'))return;
        if($this.$pp.is('.to_show')){
            $this.closePopup();
            return;
        }

        $this.$ppLoader=[];
        $this.$ppContentLoader=[];
        $this.clear();

        $this.$ppChat.append($this.$ppLoader=createLoader('pp_messages_loader',false));
        var $body=$('body');

        setPushStateHistory('im');
        $this.$pp.addClass('animated').one('hidden.bs.modal',function(){
            checkOpenModal();
            //$this.$pp.addClass('to_hide');
            //$this.clear();
            //$body.removeClass('message_open');
        }).one('hide.bs.modal',function(){
            //History windows
            $body.removeClass('message_open');
            $this.$pp.oneTransEndM(function(){
                $this.$pp.removeClass('to_show');
                $this.$pp.removeClass('animated');
                $this.clear();
            })
            if ($this.isCityPage()) {
                city.notResizeWindow = false;
                if (typeof cityInterface == 'object' && typeof cityInterface.resizeWindow == 'function') {
                    cityInterface.resizeWindow();
                }
            }
            $this.setGlobalMessagesCount();
        }).one('shown.bs.modal',function(){
            //console.log('shown.bs.modal');
        }).one('show.bs.modal',function(){
            $this.setHeightIm('');
            if(!isMobile()){
                var imH=$this.getHeightIm();
                $this.setHeightIm(imH);
                var imW=$this.getWidthIm();
                $this.setWidthIm(imW);
            }
            if ($this.isCityPage()) {
                city.notResizeWindow = true;
            }
            $this.$pp.addClass('to_show');
            $body.addClass('message_open');
            $this.open(uid,groupUserId,groupId);
        }).oneTransEndM(function(){
            $this.$pp.removeClass('animated')
        }).removeClass('to_hide').modal('show');
    }

    this.getGroupParam = function(uid){
        var result={group_im_id:0, from_group_id:0, to_group_id:0};
        if ($this._siteGroupId && uid != siteGuid) {
            if (siteGuid == $this._siteGroupUserId) {
                result={group_im_id:$this._siteGroupId, from_group_id:$this._siteGroupId, to_group_id:0};
            } else {
                result={group_im_id:$this._siteGroupId, from_group_id:0, to_group_id:$this._siteGroupId};
            }
        }
        return result;
    }

    $this._siteGroupId=0;
    $this._siteGroupUserId=0;
    this.open = function(uid,groupUserId,groupId){
        $this.actionOpenIm=false;
        $this.isSearchContact=false;
        $this.isShowOnlyOnline=false;

        $this._siteGroupId=siteGroupId,
        $this._siteGroupUserId=siteGroupUserId;

        var data={user_id:uid,is_mode_fb:isFbModeTitle};
        
        //group_id, uid, guid, groupuserId

        if(uid && groupUserId && groupId) {

            data={user_id:"100000001",is_mode_fb:isFbModeTitle};

            var result={group_im_id:$this._siteGroupId, from_group_id:$this._siteGroupId, to_group_id:0};
            for (var key in result) {
                data[key] = result[key];
            }
        } else if(uid){
            groupUserId = defaultFunctionParamValue(groupUserId, false);
            groupId = defaultFunctionParamValue(groupId, false);

            var isUpdateSiteGroup=groupUserId !== false || groupId !== false;
            if (isUpdateSiteGroup) {
                groupUserId *=1;
                groupId *=1;
                var _siteGroupId=$this._siteGroupId,
                    _siteGroupUserId=$this._siteGroupUserId;
                $this._siteGroupId=groupId;
                $this._siteGroupUserId=groupUserId;
            }

            paramsGroup=$this.getGroupParam(uid);
            for (var key in paramsGroup) {
                data[key] = paramsGroup[key];
            }

            if (isUpdateSiteGroup) {
                $this._siteGroupId=_siteGroupId;
                $this._siteGroupUserId=_siteGroupUserId;
            }
        }

        var fnOpen=function(){
            debugLog('IM open', data);
            $ajax(url_ajax+'?cmd=pp_messages', data, fnSuccess, fnError);
        },
        fnSuccess=function(res){
            if(!$this.$pp.is('.to_show'))return;
            var data=checkDataAjax(res);
            if(data!==false){
                //if(!imH)$this.endResizeIm();
                //$this.imH=imH;
                var $data=$(data);
                $this.$ppSidepanel.html($data.filter('.sidepanel').html()).removeClass('empty');
                $this.$ppContent.html($data.filter('.content').html());

                var online=$.cookie('edge_im_show_only_online'),
                    fnHideLoader=function(){
                        $this.hideLoader($this.initControls);
                    };
                if(online && online=='true'){
                    setTimeout(function(){
                        $this.showOnlyOnline(true);
                        setTimeout(fnHideLoader,250)
                    },1)
                } else{
                    fnHideLoader();
                }
            } else{
                $this.closePopup();
                alertServerError();
            }
        },
        fnError=function(){
            if(!$this.$pp.is('.to_show'))return;
            fnOpen();
        };
        fnOpen();
    }

    this.clear = function(){
        $this.$ppLoader[0]&&$this.$ppLoader.remove();
        $this.$ppChatContent.removeClass('to_show');
        $this.$ppSidepanel.empty();
        $this.$ppContent.empty();
        $this.deleteDataChats();
    }

    this.closePopup = function(){
        if(!$jq('body').is('.message_open'))return;
        $this.isInitLoad=false;
        if(!backStateHistory()){
            $this.close();
        }
    }

    this.close = function(){
		playImAudioMessageStopAll();
		if ($this.$ppAudioRecord.is('.record')){
			$this.runAudioRecorder();
		}
        $this.isInitLoad=false;
        $this.$pp.addClass('animated')
        $this.$ppSidepanel.addClass('empty');
        $this.$pp.modal('hide');
        $this.userTo=0;
        $.post(url_ajax,{cmd:'delete_empty_im'});

        return false;
    }

    this.hideLoader = function(call){
        $this.$ppLoader.oneTransEnd(function(){
            $(this).remove();
        }).delay(10).toggleClass('hidden',0);
        $this.$ppChatContent.delay(50).oneTransEnd(function(){
            if(typeof call=='function')call();
        }).toggleClass('to_show',0);
    }

    this.isVisible = function(){
        return ($this.isInitLoad && $this.$pp[0] && $this.$pp.is('.to_show') && !$this.$ppSidepanel.is('.empty')) ? true : false;
    }

    this.isOpened = function(){
        return $this.$pp[0] && $this.$pp.is('.to_show') ? true : false;
    }

    this.setUserCacheData = function(uid,kUid,data){
        users_list[uid]=data.online*1;
        if (!$this.userCache[kUid]) {
            $this.userCache[kUid]={uid:uid};
        }
        for (var key in data) {
           $this.userCache[kUid][key] = data[key];
        }
        $this.userCache[kUid]['contact'] = $('#pp_message_user_'+kUid);
        $this.userCache[kUid]['preview_msg'] = $('#pp_message_user_preview_msg_'+kUid);
    }

    this.initUserChat = function(uid, groupId){
        var kUid=$this.getKeyUid(uid, groupId), pl;
        if (isMobile()) {
            pl=$('#im_viewport_'+kUid).scroll(function(){
                if(!$(this).scrollTop())$this.uploadingMsg(kUid, uid, groupId);

                //$('[data-tooltip-data="true"].init_data').tooltip('hide');
            });
        } else {
            var sb=$('#pp_message_list_message_'+kUid).tinyscrollbar({wheelSpeed:30,thumbSize:45})
                    .on('move',function(){
                    if($this.userCache[kUid]['pl'].contentPosition==0){
                        $this.uploadingMsg(kUid, uid, groupId)
                    }
            }),
            pl=sb.data('plugin_tinyscrollbar');
        }

        if (!$this.userCache[kUid]) {
            $this.userCache[kUid]={};
        }
        $this.userCache[kUid]['pl']=pl;
        $this.userCache[kUid]['trans'] = $('.im_trans_'+kUid);

        $this.userCache[kUid]['content'] = $('#pp_messages_user_title_info_'+kUid+',#pp_message_list_message_'+kUid);
        $this.userCache[kUid]['list_msg'] = $('#im_overview_'+kUid);

        $this.prepareImage($this.userCache[kUid]['list_msg'],pl);

        $this.initUserChatListMsg(kUid);
    }

    this.deleteDataOneChat = function(uid){
        delete $this.userCache[uid];
        delete users_list[uid];
    }

    this.deleteDataChats = function(){
        for (var uid in $this.userCache) {
            $this.deleteDataOneChat(uid);
        }
    }

    this.initUserChatListMsg = function(kUid,posY){
        kUid=kUid||$this.getKeyUid($this.userTo,$this.groupId);
        if(!kUid)return;

        $this.setAnimateUserChat(kUid,true);
        $this.reInitToScroll($this.userCache[kUid]['pl'],posY)
        setZeroTimeout(function(){
            $this.setAnimateUserChat(kUid)
        })
    }

    this.setAnimateUserChat = function(kUid, remove){
        if(!isMobile())$this.userCache[kUid]['trans'][remove?'removeClass':'addClass']('animate')
    }

    this.ppMsgMaxH=0;
    this.ppMsgMaxW=0;
    this.setMaxWidthMsg = function(check){
        var check=check||false;
        if (check && $this.ppMsgMaxW) {
            return;
        }

        if (!$('#msg_img_template_ration')[0]) {
            var msg='<div class="mod_im_msg_image"><img id="msg_img_template_ration" class="msg_img_template_ration" src="'+url_tmpl_images+'1px.png"></div>',
                $msg=$this.getTemplateMsg('template_ration', msg, '', 0, 0, 0, 0);
            $('#im_overview_template_ration').append($msg);
        }
        /*var d=isMobile()?152:160,
            w=$('#pp_message_list_message').width()-d;*/

        var d=isMobile()?0:15,
            w=$('#msg_img_template_ration').width()-d;
        $this.ppMsgMaxW=w;

        var d=1.777778;
        $jq('#style_header_im')[0].innerHTML=[
            ".mod_im_msg_video .one_media_youtube{width:", w,"px;","height:", Math.round(w/d), "px;}"
        ].join("");

        //debugLog('SET im width', [w, Math.round(w/d)]);

    }

    this.initLoad = function(){
        debugLog('IM initLoad - ' + $this.userTo, $this.groupId, $this.userCache);

        $this.setMaxWidthMsg();

        var kUid=$this.getKeyUid($this.userTo, $this.groupId);
        if($this.isVisChats()){
            $this.$ppMsg[0].innerHTML=$this.lastEnteredMessage;
        }else{
            $this.lastEnteredMessage='';
        }
        $this.$ppSearch.keydown(doOnEnter(function(){$this.searchContact()}));

        $this.ppMsgMaxH=parseInt($this.$ppMsg.css('maxHeight'), 10);
        $this.$ppMsg.autosize_editable({
			isSetScrollHeight:false,
			callback:$this.prepareListMsg,
			callbackResize:$this.prepareListMsg,
			callbackSend:$this.send})
        .on('input propertychange', function(){
            status_writing[$this.userTo] = parseInt(new Date()/1000);
            $this.lastEnteredMessage=this.innerHTML;
        }).focus(function(){
            //setTimeout(function(){scrollToEl($this.$ppMsg)},1000);
        })

        $this.$ppSend.click($this.send);
        //$this.initControls();
        $this.showSplashNoChats();
        $this.$ppUserMoreMenu.click(function(){
            if($this.isContentLoader())return;
            $this.prepareMoreMenu();
            if($this.$ppUserMoreMenuBl.is('.in')){
                $this.$ppUserMoreMenuBl.collapse('hide');
            }else{
                $this.$ppUserMoreMenuBl.collapse('show');
            }
        })
        if($this.userCache[kUid]!==undefined && $this.userCache[kUid].list_msg){

            var online=$.cookie('edge_im_show_only_online');
            online = online && online=='true';
            if (online && !$this.userCache[kUid]['online']) {
                $.cookie('edge_im_show_only_online', false)
            }
            $this.prepareMoreMenu();
            $this.changePlaceholderInput(kUid);
        }
        $this.initTooltipData();

        //this.prepareMoreMenu
        $this.isInitLoad=true;

        $this.setGlobalMessagesCount();

        $this.initPpImage();
    }

    this.setGlobalMessagesCount = function(){
        imMessagesCount=$('#message_notification_link').find('.count').data('counter')*1;
    }

    this.ppMsgH=0;
    this.prepareListMsg = function(ta,ta,posY){
        if(!$this.isOpened())return;
        $this.ppMsgH=ta;
        var taD=ta;
        if(taD>$this.ppMsgMaxH)taD=$this.ppMsgMaxH;
        var h=$this.$ppUserChatContent.height()-(taD+$this.$ppUserChatInfo.height());
		if ($this.$ppMessageAction.css('position') == 'absolute') {
			h -=$this.$ppMessageAction.height();
		}
        $this.$ppListMsg.css({height:h+'px'});
        $this.$ppSend.css({height:ta+'px'});
        $this.initUserChatListMsg(0,posY);
    }

    this.showSplashNoChats = function(){
        if($this.isVisChats()){
            $this.$ppUserMenu.show();
            $this.$ppSplashNoChats.removeClass('to_show');
        } else {
            $this.$ppUserMenu.hide();
            $this.$ppSplashNoChats.addClass('to_show');
        }
    }

    this.sendMsgControlsDisabled = function(disabled){
        disabled=disabled||false;
        $this.$ppMsg.prop('disabled',disabled);
        $this.$ppSend.prop('disabled',disabled);
        if (disabled) {
            $this.clearUploadImage(true);
        }
        $this.$ppUploadImage[disabled?'addClass':'removeClass']('no_active');
    }

    this.initControls = function(){
        var disabled=$this.isVisChats()?false:true;
        $this.sendMsgControlsDisabled(disabled);
        var $contactAll=$('.contact', $this.$ppListContact);
        if($contactAll[0]){
            $this.$ppSearch.prop('disabled',false);
            $this.$ppBtnOnline.prop('disabled',false);
            if (!$contactAll.filter(':visible')[0]) {
                if(!$this.isSearchContact){
                    $this.$ppSearch.prop('disabled',true);
                }
                if (!$this.isShowOnlyOnline) {
                    $this.$ppBtnOnline.prop('disabled',true);
                }
            }
        } else {
            $this.$ppSearch.prop('disabled',true);
            $this.$ppBtnOnline.prop('disabled',true);
        }
    }

    this.isSearchContact=false;
    this.searchContact = function(empty){
        if($this.actionOpenIm)return;
        empty=empty||0;
        var search=$this.getNameSearch(),$contact,$activeIm=[];

        if(!$this.isSearchContact && !search && !empty){
            return;
        }
        var clOnline=$this.getClOnline();
        if (!search||empty) {
            $this.isSearchContact=false;
            $('li', $this.$ppListContact).removeClass('search');
            $contact=$('li'+clOnline, $this.$ppListContact);
            $this.changeActiveFirstChat($contact);
            return;
        }
        $this.isSearchContact=true;
        $('.pp_message_name', $this.$ppListContact).each(function(){
            var $el=$(this),s=$this.isNameSearch(this),
                $contact=$el.closest('.contact');
            if(s){
                if(!$activeIm[0]){
                    $activeIm=$contact;
                    $this.changeActiveFirstChat($contact,true);
                }
                $contact.addClass('search');
                var isShow=true;
                if(clOnline&&!$contact.is(clOnline)){
                    isShow=false;
                }
                $contact[isShow?'show':'hide']();
            }else{
                $contact.removeClass('search').hide();
            }
            $this.initControls();
        })
        if (!$this.isVisChats()) {
            $this.hideUserContent();
        }
    }

    this.showOnlyOnline = function(online, notBtnActive){
        if(!$this.isShowOnlyGroup) {
            if($this.actionOpenIm)return;
            if($this.isShowOnlyOnline==online)return;
        }
        $this.isShowOnlyGroup = false;

        notBtnActive=notBtnActive||0;
        $this.isShowOnlyOnline=online;
        $.cookie('edge_im_show_only_online', online);
        var clSearch=$this.getClSearch(), $visible;

        if(online){
            $('#pp_messages_show_all').removeClass('active');
            $('#pp_messages_show_online').addClass('active');
            $('#pp_messages_show_group').removeClass('active');
            
            $('.contact'+clSearch+':not(.online)', $this.$ppListContact).hide();
            $visible=$('.contact'+clSearch+'.online', $this.$ppListContact).show();
        }else{
            $('#pp_messages_show_all').addClass('active');
            $('#pp_messages_show_online').removeClass('active');
            $('#pp_messages_show_group').removeClass('active');
            
            $('.contact'+clSearch+'.group', $this.$ppListContact).hide();
            $visible=$('.contact'+clSearch + ':not(.group)', $this.$ppListContact).show();
        }
        console.log('%cIM: ShowOnlyOnline', 'background: #73afda', online);

        //console.log('ShowOnlyOnline', online, !notBtnActive);
        $this.changeActiveFirstChat($visible);
    }

    this.showOnlyGroup = function(group, notBtnActive){

        if($this.isShowOnlyGroup == true) return;
        notBtnActive=notBtnActive||0;
        $this.isShowOnlyOnline=false;
        $this.isShowOnlyGroup=true;
        $.cookie('edge_im_show_only_online', false);
        var clSearch=$this.getClSearch(), $visible;

        $('#pp_messages_show_all').removeClass('active');
        $('#pp_messages_show_online').removeClass('active');
        $('#pp_messages_show_group').addClass('active');

        $('.contact'+clSearch+':not(.group)', $this.$ppListContact).hide();
        $visible=$('.contact'+clSearch + '.group', $this.$ppListContact).show();
    
        console.log('%cIM: ShowOnlyGroup', 'background: #73afda', true);

        $this.changeActiveFirstChat($visible);
    }

    this.getClOnline = function(){
        return $this.isShowOnlyOnline?'.online ':'';
    }

    this.getClSearch = function(){
        return $this.isSearchContact?'.search':''
    }

    this.getNameSearch = function(){
        var name=trim($this.$ppSearch[0].value);
        $this.$ppSearch.val(name);
        return name.toLowerCase();
    }

    this.isNameSearch = function(el){
        return ~el.innerText.toLowerCase().indexOf($this.getNameSearch())
    }

    this.disabledActiveChat = function(){
        $('li.active', $this.$ppListContact).removeClass('active');
    }

    this.isVisChats = function(){
        return $this.getChats()[0];
    }

    this.getChats = function(){
        return $('.contact:visible', $this.$ppListContact);
    }

    this.getChatsFirst = function(){
        return $this.getChats().first()
    }

    this.setActiveFirstChat = function($firstIm){
        $this.disabledActiveChat();
        $firstIm.css('transition','none').addClass('first active');
        $firstIm.click();
        setTimeout(function(){$firstIm.removeAttr('style')},1);
    }

    this.changeActiveFirstChat = function($contact,noTrans){
        noTrans=noTrans||0;
        if(!$contact[0]){
			$this.changePlaceholder(l('write_your_message'));
            $this.hideUserContent();
            return;
        }
        var $active=$contact.filter('.active'),
            visActive=$active[0] && $active.is(':visible'),
            $activeIm=$active[0] ? $active : $contact.first();

        //console.log('ChangeActiveFirstChat',$active.is(':visible'));//,$active
        $this.disabledActiveChat();
        if(!visActive){
            $this.showContentLoader();
        }
        if(noTrans)$activeIm.css('transition','none');
        $this.changeIm($activeIm.addClass('first active').data('uid'), $activeIm.data('groupId'));
        $contact.show();
        setZeroTimeout(function(){$activeIm.removeAttr('style')});
    }

    this.hideUserContent = function(call){
        $this.userTo=0;
        $this.$ppContentUser.oneTransEnd(function(){
            if(typeof call=='function')call()
        }).addClass('to_hide');
        $this.initControls();
    }

    this.showPic = function(url,uid){
        onLoadImgToShow(url,[],function(){
            $('.pp_message_profile_pic_'+uid+'.to_hide').removeClass('to_hide')
        })
    }

    this.showOriginalMessage = function($el){
        $el.hide().next('.original_message').show();

        //$this.initUserChatListMsg();
        var kUid=$this.getKeyUid($this.userTo,$this.groupId);
        if(!kUid)return;

        //$this.setAnimateUserChat(kUid,true);
        $this.reInitToScroll($this.userCache[kUid]['pl'],'relative')

    }

    this.durMoveChat=300;
    this.moveImToFirst = function(kUid,onlyMove){
        var $li=$this.userCache[kUid]['contact'];
        if($li.is(':animated'))return;
        var $chatFirst=$('.contact', $this.$ppListContact);
        onlyMove=onlyMove||0;
        if($li[0]==$('.contact.active', $chatFirst)[0]||$li.is('.first')){
            $li.prependTo($this.$ppListContact).removeClass('first');
            return;
        }
        if($li[0]==$chatFirst[0]){
            if(!onlyMove){
                $this.disabledActiveChat();
                $li.addClass('active');
            }
            return;
        }
        if($this.$ppListContactBl[0].scrollTop){
            if(isVisiblePage){
                $this.$ppListContactBl.scrollTo(0, $this.dur*1.5, {axis:'y', queue:false, easing:'easeInOutCubic'});
            } else {
                $this.$ppListContactBl[0].scrollTop=0;
            }
        }
        var isHidden=$li.isHidden();
        if(onlyMove){
            console.log('%cIM: MoveImToFirst ONLYMOVE UPDATE SERVER', 'background: #73afda');
            //console.log('MoveImToFirst ONLYMOVE UPDATE SERVER');
            var $el=$this.$ppListContact, fnTo='prependTo';
            if ($chatFirst[0]) {
                if ($chatFirst[1]&&$li[0]==$chatFirst[1]) {
                    return;
                }
                $el=$chatFirst.eq(0);
                fnTo='insertAfter';
            }
            if(isHidden){
                console.log('%cIM: MoveImToFirst ONLYMOVE UPDATE SERVER HIDDEN', 'background: #73afda');
                $li[fnTo]($el);
            }else{
                console.log('%cIM: MoveImToFirst ONLYMOVE UPDATE SERVER VISIBLE', 'background: #73afda');
                $li.slideUp($this.durMoveChat, function(){
                    $li[fnTo]($el).slideDown($this.durMoveChat)
                })
            }
        }else{
            if(isHidden){
                console.log('%cIM: MoveImToFirst HIDDEN', 'background: #73afda');
                $this.disabledActiveChat();
                $li.show()
                $li.prependTo($this.$ppListContact);
                $li.addClass('active');
                $this.initControls();
            } else {
                console.log('%cIM: MoveImToFirst ACTIVE', 'background: #73afda');
                $li.slideUp($this.durMoveChat, function(){
                    $this.disabledActiveChat();
                    $li.prependTo($this.$ppListContact).addClass('active').slideDown($this.durMoveChat,$this.initControls)
                })
            }
        }
    }

    this.actionOpenIm=false;
    this.selUserTitleInfo='.pp_message_user_title_info';
    this.selUserListMsg='.pp_message_user_list_message';
    this.responseOpenIm = function(uid, groupId){
        $this.userTo=uid;
        $this.groupId=groupId;
        var kUid=$this.getKeyUid(uid, groupId);
        $this.moveImToFirst(kUid);
        $this.hideContentLoader();
        $($this.selUserTitleInfo,$this.$ppUserTitleInfo).hide();
        $($this.selUserListMsg,$this.$ppListMsg).hide();
        $this.sendMsgControlsDisabled();
    }

    this.slideMsgShow = function($listMsg, kUid){
        if($('#pp_message_user_'+kUid).is('.active'))return;

        $listMsg.find('.pp_message_msg_item').oneAnimationEnd(function(){
            $(this).removeClass('to_slide_show');
        }).addClass('to_slide_show');
    }

    this.showContentIm = function(kUid){
        $this.$ppContentUser.oneTransEnd(function(){
            $this.actionOpenIm=false;
        }).removeClass('to_hide');
        $this.prepareImage($this.userCache[kUid]['list_msg'], $this.userCache[kUid]['pl']);

        $this.userCache[kUid]['contact'].removeClass('first');
        setZeroTimeout($this.initControls);
        $this.prepareMoreMenu();
    }

    this.showContentLoader = function(){
        if (!$this.$ppContentLoader[0]) {
            $this.$ppContent.append($this.$ppContentLoader=createLoader('pp_messages_loader',false));//.addClass('box')
        } else {
            $this.$ppContentLoader.removeClass('hidden')
        }
        $this.sendMsgControlsDisabled(true);
    }

    this.hideContentLoader = function(){
        $this.$ppContentLoader[0]&&$this.$ppContentLoader.addClass('hidden');
    }

    this.isContentLoader = function(){
        return $this.$ppContentLoader[0]&&!$this.$ppContentLoader.is('.hidden');
    }

    this.openOneIm = function(uid, groupId, fromGroupId, toGroupId){
        console.log('OPEN NEW IM', uid, groupId, fromGroupId, toGroupId);

        var kUid=$this.getKeyUid(uid, groupId);

        fromGroupId=defaultFunctionParamValue(fromGroupId,false);
        toGroupId=defaultFunctionParamValue(toGroupId,false);
        if (fromGroupId===false) {
            fromGroupId=$this.userCache[kUid]['fromGroupId'];
        }
        if (toGroupId===false) {
            toGroupId=$this.userCache[kUid]['toGroupId'];
        }

        var data={user_id:uid,
                  group_im_id:groupId,
                  from_group_id:fromGroupId,
                  to_group_id:toGroupId,
                  is_mode_fb:'true',
                  upload_im:1,
                  upload_im_new:1};

        if(groupId && !fromGroupId && toGroupId==groupId) {
            return false;
            console.log('xxx');
        }
        
        $.post(url_ajax+'?cmd=pp_messages',data,function(res){
            if(!$this.isVisible())return;
            var data=checkDataAjax(res);
            if(data!==false){
                var $data=$(trim(data));
                var $contactAll=$('li.contact', $this.$ppListContact),
                    $contact=$data.filter('li.contact').hide(),
                    $userInfo=$data.filter($this.selUserTitleInfo).hide().prependTo($this.$ppUserTitleInfo),
                    $listMsg=$data.filter($this.selUserListMsg).hide().prependTo($this.$ppListMsg),
                    clOnilne=$this.getClOnline(),
                    clSearch=$this.getClSearch(),
                    isShow=true;
                if(clOnilne&&!$contact.is(clOnilne)){
                    isShow=false;
                }
                if(clSearch){
                    if($this.isNameSearch($('.pp_message_name', $contact)[0])){
                        $contact.addClass('search')
                    }else{
                        isShow=false;
                    }
                }
                debugLog('IM: openOneIm', $contact[0].id);
                if ($('#'+$contact[0].id)[0]) {
                    debugLog('IM: openOneIm No Open', $contact[0].id);
                    return;
                }
                if($contactAll[0]){
                    if(isShow){
                        if(!$contactAll.filter(':visible')[0]){
                            $this.changeActiveFirstChat($contact.insertBefore($contactAll.first()),true);
                        }else{
                            $contact.removeClass('active').insertBefore($contactAll.first()).aSlideDown({dur:$this.durMoveChat});
                        }
                    }else{
                        $contact.removeClass('active').insertBefore($contactAll.first())
                    }
                }else{
                    $this.userTo=uid;
                    $contact.prependTo($this.$ppListContact).aSlideDown({dur:dur,complete:$this.initControls});
                    if($this.$ppSplashNoChats.is('.to_show')){
                        var dur=400;
                        $this.$ppSplashNoChats.oneTransEnd(function(){
                            $this.$ppUserMenu.fadeIn(dur);
                            $userInfo.fadeIn(dur);
                            $listMsg.fadeIn(dur);
                        }).removeClass('to_show');
                    }else{
                        $this.$ppUserMenu.show();
                        $userInfo.show();
                        $listMsg.show();
                    }
                }

            }
        })
    }

	this.changePlaceholder = function(plc){
		$this.$ppMsg.attr('data-placeholder-new', plc).data('placeholderNew', plc).trigger('placeholder');
	}

    this.changePlaceholderInput = function(kUid){
        var plc=l('write_your_message'),
            info=$this.userCache[kUid];
        if(info['groupId']){
            plc=info['fromGroupId'] ? l('reply_as_group_im') : l('reply_to_group_im');
        } else {
            plc=l('reply_to_user_im');
        }
        plc=plc.replace('{im_name}',info['imName']);
        plc=plc.replace('{im_name_real}',info['imNameReal']);

        var n=isMobile() ? 22 : 82,
            ln=plc.length;
        if (ln>(n+3)) {
            plc=plc.slice(0,n)
            plc=trim(plc) + '...';
        }
		$this.changePlaceholder(plc);
    }

    this.winFocusReadMsg = function(){
        if($this.isVisible()){
            var kUid=$this.getKeyUid($this.userTo, $this.groupId);
            var fromGroupId=$this.userCache[kUid]['fromGroupId'],
                toGroupId=$this.userCache[kUid]['toGroupId'],
            data={user_current:$this.userTo,
                  group_im_id:$this.groupId,
                  from_group_id:fromGroupId,
                  to_group_id:toGroupId,
                  is_mode_fb:'false'};
            $.post(url_server+'?cmd=read_msg',data);
        }
    }

    this.changeIm = function(uid, groupId){
        var kUid=$this.getKeyUid(uid, groupId);

        $this.changePlaceholderInput(kUid);
        $this.$ppMsg.text('').trigger('autosize').blur();

        $this.$ppListMsg.removeAttr('style');
        $this.$ppSend.removeAttr('style');

		var isRecord=$this.$ppAudioRecord.is('.record'),
			isDeleteRecord=$this.$ppAudioRecord.is('.im_audio_message_delete');
		if (isRecord || isDeleteRecord){
			$this.$ppAudioRecord.data('stop_process',1);
			$this.runAudioRecorder();
			//$this.$ppAudioRecord.data('stop_process',0);
		}

        var fromGroupId=$this.userCache[kUid]['fromGroupId'],
            toGroupId=$this.userCache[kUid]['toGroupId'],
            data={group_im_id:groupId,
                  from_group_id:fromGroupId,
                  to_group_id:toGroupId,
                  is_mode_fb:isFbModeTitle};

        if ($this.userCache[kUid]['content']) {
            data['user_current']=uid;

            $.post(url_server+'?cmd=activate_im',data,function(res){
                var data=checkDataAjax(res);
                if(data!==false){
                    clCounters.updateNewMsg(data);
                }
            });
            $this.responseOpenIm(uid, groupId);
            $this.userCache[kUid]['content'].show();

            $this.slideMsgShow($this.userCache[kUid]['list_msg'], kUid);
            $this.userCache[kUid]['list_msg'].find('.pp_message_msg_item.to_show').removeClass('to_show');
            $this.initUserChatListMsg(kUid);
            $this.showContentIm(kUid);
        } else {
            data['user_id']=uid;
            data['upload_im']=1;

            $.post(url_ajax+'?cmd=pp_messages',data,function(res){
                if(!$this.isVisible())return;
                var data=checkDataAjax(res);
                if(data!==false){
                    $this.removePreviewNewMsg(kUid);
                    var $data=$(trim(data));
                    $data.filter('.update_script_chat').appendTo('#update_server');
                    $this.responseOpenIm(uid, groupId);
                    $data.filter($this.selUserTitleInfo).prependTo($this.$ppUserTitleInfo);

                    var $msgList=$data.filter($this.selUserListMsg);
                    $this.slideMsgShow($msgList, kUid);
                    $msgList.prependTo($this.$ppListMsg);
                    $this.showContentIm(kUid);
                }else{
                    $this.$ppContentLoader.addClass('hidden');
                    $this.actionOpenIm=false;
                    $this.showContentIm(kUid);
                    $this.sendMsgControlsDisabled();
                    alertServerError(true);
                }
            })
        }
    }

    this.openIm = function(uid, groupId){
        uid *=1;
        groupId *=1;
        if ((uid==$this.userTo && $this.groupId==groupId)
            ||$this.actionOpenIm) return;

        $this.actionOpenIm=true;

        $this.showContentLoader();
        if ($this.$ppContentUser.is('.to_hide')) {
            $this.changeIm(uid, groupId);
        }else{
            $this.$ppContentUser.oneTransEnd(function(){
                $this.changeIm(uid, groupId)
            }).addClass('to_hide');
        }

		playImAudioMessagePauseAll();
    }

    this.reInitToScroll = function(pl,posY,call,noAnimate){
        posY=defaultFunctionParamValue(posY, 'bottom');
        noAnimate=noAnimate||false;
        if(posY===false)return;
        if(typeof call!='function')call=function(){};
        if(isMobile()) {
            if(posY=='relative')return;
            var t=600;
            if(posY=='bottom'){
                posY=noAnimate?pl[0].scrollHeight:'max';
            }
            if (noAnimate) {
                pl.stop();
                pl[0].scrollTop = posY;
                return;
            }
            pl.scrollTo(posY, t, {axis:'y', interrupt:true, easing:'easeOutExpo', onAfter:call});
        } else {
            /*if(posY=='bottom'&&((pl.contentSize-pl.trackSize)<=20)){
                posY=0;
            }*/
            pl.update(posY,call);
        }
    }

    this.getTemplateMsg = function(send, msg, msgOrig, audioMessageId, groupId, fromGroupId, toGroupId, sticker){
		if (typeof sticker != 'object' || typeof sticker.code == 'undefined') {
			sticker=false;
		}
        var linkProfile=urlPagesSite.profile_view,
            photoProfile=urlFiles+$this.guidPhoto;
        if(fromGroupId){
            var kUid=$this.getKeyUid($this.userTo, groupId);
            linkProfile=$this.userCache[kUid]['groupLink'];
            photoProfile=urlFiles+$this.userCache[kUid]['groupPhoto'];
        }
		var clStickerCl=sticker?'sticker_wrap_bl':'';

        return  $('<div id="pp_message_msg_'+send+'" data-msg="'+(he(msgOrig))+'" data-send="'+send+'" data-id="0" data-to-user-id="'+$this.userTo+'" data-msg-user-id="'+$this.guid+'" data-audio-message-id="' + audioMessageId + '" data-group-id="'+groupId+'" data-from-group-id="'+fromGroupId+'" data-to-group-id="'+toGroupId+'" class="pp_message_msg_item to_show replies '+clStickerCl+'">'+
                    '<button class="pp_message_profile_pic pp_message_profile_pic_'+$this.guid+'" onclick="redirectUrl(\''+linkProfile+'\');" style="background-image: url('+photoProfile+')"></button>'+
                    '<div class="msg to_send">'+
                        '<div class="text" data-tooltip-data="true" title="'+l('just_now')+'"><span class="im_message">'+msg+'</span></div>'+
                        '<div class="message_extension">'+
                            '<div class="message_more_menu">'+
                                '<div class="ellipsis">'+
                                    '<span></span>'+
                                '</div>'+
                                    '<ul id="pp_message_msg_menu_'+send+'" class="more_menu_collapse collapse more_msg_menu">'+
                                        '<li>'+
                                            '<a href="#" class="delete_from_me">'+
                                                '<span class="icon_fa" data-cl-loader="msg_loader_menu_more"><i class="fa fa-times" aria-hidden="true"></i></span>'+
                                                l('delete_for_me')+
                                            '</a>'+
                                        '</li>'+
                                        '<li>'+
                                            '<a href="#" class="delete_everywhere">'+
                                                '<span class="icon_fa" data-cl-loader="msg_loader_menu_more"><i class="fa fa-trash" aria-hidden="true"></i></span>'+
                                                l('delete_for_everyone')+
                                            '</a>'+
                                        '</li>'+
                                    '</ul>'+
                            '</div>'+
                            '<div id="msg_read_" data-mid="" class="icon_check_msg to_hide"></div>'+
                        '</div>'+
                    '</div>'+
                 '</div>');
    }

    this.prepareMsg = function(kUid,$msg){
        var $list=$this.userCache[kUid]['list_msg'],
            $lastMsg=$list.find('.pp_message_msg_item:not(.write)').last(),fn='appendTo',cont=$list;
        if($lastMsg[0]){
            if($lastMsg.data('msgUserId')==$msg.data('msgUserId')){
                if($msg.is('.write')){
                    $msg.find('.pp_message_profile_pic').hide();
                }else{
                    $msg.find('.pp_message_profile_pic').remove();
                }
            }
            cont=$lastMsg;
            fn='insertAfter';
        }
        var $lastMsgWr=$list.find('.pp_message_msg_item').last();
        if($lastMsgWr[0]&&$lastMsgWr.is('.write')){
            var is=$lastMsgWr.data('msgUserId')==$msg.data('msgUserId');
            $lastMsgWr.find('.pp_message_profile_pic')[is?'fadeOut':'fadeIn'](300);
            if (!$lastMsg[0]) {
                fn='insertBefore';
                cont=$lastMsgWr;
            }
        }
        return {msg:$msg, cont:cont, fn:fn};
    }

    /*this.showMsgOne = function($msg,t,call,notReInitScroll){
        var uid=$this.userTo;
        var data=$this.prepareMsg(uid,$msg);
        $msg = data.msg;
        notReInitScroll=notReInitScroll||0;
        $this.userCache[uid]['list_msg'].removeClass('animate');
        var scrollPl=$this.userCache[$this.userTo]['pl'];
        $msg.addClass('animate_sent').hide()[data.fn](data.cont)
            .slideDown({duration:t||250,
                        step:function(){
                            if(!notReInitScroll)$this.reInitToScroll(scrollPl)
                        },
                        complete:function(){
                            if(!notReInitScroll)$this.reInitToScroll(scrollPl)
                            if(typeof call=='function')call();
                            $msg.removeClass('animate_sent');
                            if(!$this.userCache[uid]['list_msg'].find('.animate_sent')[0]){
                               $this.userCache[uid]['list_msg'].addClass('animate');
                            }
                        }})
	}*/

    this.setNewMsg = function($msg,uid,groupId,kUid){
        var $prev=$this.userCache[kUid]['preview_msg'].addClass('to_new');
        if ($this.userTo == uid && $this.groupId == groupId) {
            $msg.addClass('to_new').delay(2000).removeClass('to_new',0);
            setTimeout(function(){
                $prev.is('.to_new')&&!$prev.is(':animated')&&$prev.removeClass('to_new');
            },2000)
        } else {
            $msg.addClass('to_new');
        }
    }

    this.removeNewMsg = function(kUid){
        $this.userCache[kUid]['preview_msg'].removeClass('to_new');
        $('.to_new',$this.userCache[kUid]['list_msg']).removeClass('to_new');
    }

    this.removePreviewNewMsg = function(kUid){
        $this.userCache[kUid]['preview_msg'].removeClass('to_new');
    }

    this.showMsgBouncein = function($msg,fnTo,$cont,scrollToY,call,notReInitScroll,update){
        var uid=$msg.data('toUserId'),
            groupId=$msg.data('groupId'),
            kUid=$this.getKeyUid(uid, groupId),
            $listMsg=[];

        var scrollPl=$this.userCache[kUid]['pl'];
        notReInitScroll=notReInitScroll||0;
        $cont=$cont||$this.userCache[kUid]['list_msg'];
        update=update||false;

        $this.prepareImage($msg,scrollPl);

        if (fnTo) {
            $listMsg=$this.userCache[kUid]['list_msg'].removeClass('animate');
        } else {
            var data=$this.prepareMsg(kUid,$msg);
            fnTo=data.fn;
            $cont=data.cont;
            $msg = data.msg;
        }

        if (($cont.is(':hidden')||$this.$ppContentUser.is('.to_hide'))
             &&!$cont.is('.pp_messages_upload_msg_loader')) {
            $msg.removeClass('to_show')[fnTo]($cont);
            if (update&&false) {
                if ($msg.is('.sent')) {
                    var $list=$this.userCache[kUid]['list_msg'];
                    $this.setNewMsg($msg,uid,groupId,kUid);
                    $list.off('mouseover').one('mouseover',function(){
                        $this.removeNewMsg(kUid);
                        $cont.removeClass('to_new');
                    });
                } else {
                    $this.removePreviewNewMsg(kUid);
                }
            }
            if(typeof call=='function')call();
            return;
        }


        $msg.oneAnimationEnd(function(){
            $msg.removeAttr('style');
            if(typeof call=='function')call();
            if(!notReInitScroll){
                if(typeof scrollToY == 'function'){
                    scrollToY()
                } else {
                    $this.reInitToScroll(scrollPl,scrollToY);
                }
            }
            $listMsg[0]&&$listMsg.addClass('animate');
        })[fnTo]($cont);
        setTimeout(function(){
            if(!notReInitScroll){
                if(typeof scrollToY == 'function'){
                    scrollToY()
                } else {
                    $this.reInitToScroll(scrollPl,scrollToY);
                }
            }
            $this.initTooltipData($msg);
        },10);
    }

    this.prepareImageResize = function(){
        if(!$this.isOpened())return;
        $this.setMaxWidthMsg();

        if($this.userTo){
            var kUid=$this.getKeyUid($this.userTo, $this.groupId);
            $this.prepareImage($this.userCache[kUid]['list_msg'], $this.userCache[kUid]['pl'], true);
        }

        $('.pp_message_user_list_message:hidden', $this.$ppContent ).find('.mod_im_msg_image img').each(function(){
            $(this).css({width:'',height:''}).data('set-params',false);
        })
    }

    this.prepareImage = function($node, pl, resize){
        $this.setMaxWidthMsg(true);
        pl=pl||false;
        var $images=$node.find('.mod_im_msg_image img');
        if (!$images[0])return $node;

        resize=resize||false;
        var ratio=0;

        //wWrap=$this.ppMsgMaxW||$images.eq(0).closest('.pp_message_msg_item').width() - 80;//original = 78
        wWrap=$this.ppMsgMaxW||$('#msg_img_template_ration').width();

        $images.each(function(){
            var $img=$(this);
            if($img.data('setParams')&&!resize) return false;

            $img.css({width:'',height:''}).data('set-params',true);
            var w=$img.data('width'),
                wOrig=w,
                h=$img.data('height'),
                hOrig=h,
                ratio=wWrap/w;
                if (w>wWrap) {
                    w=wWrap;
                    h=Math.round(h * ratio, 1);
                }
                var maxHeight=300,
                    dpr=window.devicePixelRatio
                if(dpr&&$jq('body').is('.city_body.city_mobile')){
                    maxHeight=Math.round(maxHeight*dpr);
                }
                if (h > maxHeight) {
                    ratio=maxHeight/hOrig;
                    w=Math.round(wOrig * ratio, 1);
                    h=maxHeight;
                }
            $img.css({width:w+'px',height:h+'px'});
            pl&&$this.reInitToScroll(pl);
        })

        return $node;
    }

    this.send = function(sticker){
		if (typeof sticker != 'object' || typeof sticker.code == 'undefined') {
			sticker=false;
		}
		sticker=defaultFunctionParamValue(sticker, false);
        if(!sticker && ($this.processUploadImage || $this.mediaRecorderProcess))return false;

		var audioMessageId=0;
		if (!sticker && $this.$ppAudioRecord[0]) {
			audioMessageId=$this.$ppMsg.data('im-audio-message-id');
			$this.$ppMsg.find('.im_audio_message_send_play').remove();
		}

		if(!sticker) {
			audioMessageId = $('.app_ios_im_audio_message_recorder').data('im-audio-message-id');
			if(typeof(audioMessageId) == 'undefined') {
				audioMessageId = 0;
			}
		}

		var msgIn=sticker||$this.$ppMsg[0].innerHTML,
			msgPrepare=smileTextPrepare(msgIn),
			msg=msgPrepare.msg, emoji=msgPrepare.emoji;

		if (msg == $this.$ppMsg.data('placeholder')) {
			msg = '';
		}

		if (!sticker){
			$this.$ppMsg.trigger('clear-caret-position');
			if(!msg && !audioMessageId && !$this.isImageLoaded){
				$this.$ppMsg.text('').trigger('autosize').focus();
				return false;
			}
		}

        var uid=$this.userTo,
            groupId=$this.groupId,
            kUid=$this.getKeyUid($this.userTo, groupId),
            fromGroupId=$this.userCache[kUid]['fromGroupId'],
            toGroupId=$this.userCache[kUid]['toGroupId'];

        if($this.$ppNewMsgLink.is('.disabled'))$this.$ppNewMsgLink.removeClass('disabled');

        $this.lastEnteredMessage='';
        var msgHtml=strToHtml(msg),
            send= +new Date,
            preload='';

        if(!sticker &&($this.isImageLoaded || audioMessageId)){
            preload = '...';//l('image_loading');
        }/* else if(msg.match(/https?:\/\/\S+(?:jpg|jpeg|png|gif)/)) {///.jpg|.png|.jpeg|.gif/i.test(msg)
            preload = '...';//l('im_msg_processed');
        } else if (msg.match(/(?:(https?):\/\/)?(?:(?:www|m)\.)?youtube\.com\/watch.*v=([a-zA-Z0-9_-]+)/)
                    || msg.match(/(?:(https?):\/\/)?(?:(?:www|m)\.)?youtu\.be\/([a-zA-Z0-9_-]+)/)) {
            preload = '...';//l('im_msg_processed');
        }*/

		msgPrepare = smileTextReplace(emoji, msg, msgHtml);
		msg = msgPrepare.msg;
		var msgTmpl=msgPrepare.msgTmpl;

		if(sticker){
			msg=sticker.code;
			msgTmpl=sticker.html;
		}

		var msgOrig=msgTmpl;//msgHtml;
        if (preload) {
            msgTmpl ='<div class="image_preload">' + preload + '</div>' + '\n' + msgTmpl;
            if (!msgOrig) {
                msgOrig = preload;
            }
        }

		msgOrig=trim(br2nl(msgOrig));
		msgOrig=msgOrig.replace(/\n|\t/g, ' ');

        var $msg=$this.getTemplateMsg(send, msgTmpl, msgOrig, audioMessageId, groupId, fromGroupId, toGroupId, sticker),
            $node=$msg.find('.msg > .text');

		if (sticker){
			$this.showMsgBouncein($msg);
		} else {
			$this.$ppMsg.text('').trigger('autosize',function(){
				$this.showMsgBouncein($msg);
			}).focus();
		}

        $this.updateMsgPreview($msg);

        msg=emojiToHtml(msg);

        $this.prepareMoreMenu();
        debugLog('IM: SEND msg',[kUid,msg],'#d0e8cd');

		var imageUpload = 0, indImageUpload =0;
		if (sticker) {

		} else {
			if ($this.$ppAudioRecord[0] && audioMessageId) {
				$this.$ppMsg.data('im-audio-message-id', 0);
				$this.$ppAudioRecord.removeClass('im_audio_message_delete');
			}
			imageUpload = $this.isImageLoaded?1:0;
			indImageUpload = $this.isImageLoaded;
		}

		$('.app_ios_im_audio_message_delete').removeClass('app_ios_im_audio_message_delete').data('im-audio-message-id', 0);

        var fnSend=function(retry){
            var data = {
                    user_to:uid,
                    group_im_id:groupId,
                    from_group_id:fromGroupId,
                    to_group_id:toGroupId,
                    msg:msg,
                    send:send,
                    to_delete:0,
                    retry:retry,
					audio_message_id:audioMessageId,
                    image_upload:imageUpload,
                    ind:indImageUpload
                };
			if (sticker) {
				data['sticker'] = sticker.data;
			}
            retry=retry||0;
            $.ajax({url:url_ajax+'?cmd=send_message',
                type:'POST',
                data:data,
                timeout: globalTimeoutAjax,
                //cache: false,
                success: function(res){
                    if(!$this.isVisible())return;
                    var data=checkDataAjax(res);
                    if(data!==false){
                        if(data=='redirect'){
                            redirectUrl(data['url'])
                        }else{
                            data=$(trim(data));
                            if(!data[0]){
                                debugLog('IM: SEND msg ERROR NO DATA',data,'#d0e8cd');
                                return;
                            }
                            debugLog('IM: SEND msg RESPONSE',true,'#d0e8cd');
                            var $msgRes=data.find('.pp_message_msg_item'),
                                mid=$msgRes.data('id'),sendId=$msgRes.data('send'),
                                dataMsg=$msgRes.data('msg');

                            if($msgRes.data('blockList')){
                                $msg.find('.message_extension').remove();
                            }
                            //if(!$('#pp_message_msg_'+mid)[0]&&!$('#pp_message_msg_'+sendId)[0]){
                            $msg.attr({'id':'pp_message_msg_'+mid,'data-id':mid,'data-msg':dataMsg}).data({id:mid,msg:dataMsg});
                            $msg.find('.icon_check_msg').data('mid', mid).attr({'id':'msg_read_'+mid,'data-mid':mid});
                            $msg.find('.more_msg_menu').attr({'pp_message_msg_menu_':'msg_read_'+mid});

                            $msg.find('.delete_from_me').click(function(){
                                $this.removeMsgFromChat(mid, true);
                                return false;
                            })
                            $msg.find('.delete_everywhere').click(function(){
                                $this.removeMsgFromChat(mid);
                                return false;
                            })
                            var txt=$msgRes.find('.msg > .text').html();
							txt=trim(txt.replace(/\n|\t/g,''));

                            if(txt!=$node.html()){
                                $this.updateMsgPreview($msg);
                                data.imagesLoaded(function(){
                                    $node.html(txt);
                                    if (!$msgRes.find('.smile, a')[0]) {
                                        $msg.data('msg', $msgRes.data('msg'));
                                    }
                                    $this.prepareImage($node,$this.userCache[kUid]['pl']);
                                    $this.reInitToScroll($this.userCache[kUid]['pl']);
                                })
                            }
                            $msg.find('.msg').removeClass('to_send');
                            //}
                        }
                    } else {
                        debugLog('IM: SEND msg error OR msg already sent',data,'#d0e8cd');
                        //$this.deleteMsg($msg, uid);
                    }
                },
                error: function(xhr, textStatus, errorThrown){
                    if(!$this.isVisible())return;
                    globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                        debugLog('IM: retry send msg',true,'#d0e8cd');
                        fnSend(1);
                    })
                    //$this.deleteMsg($msg, uid);
                },
            })
        }
        fnSend();

        !sticker && $this.clearUploadImage();

		return false;
    }

    this.uploadingMsg = function(kUid, uid, groupId){
        if($this.userCache[kUid]==undefined)return;

        var firstMid=$this.userCache[kUid]['first_msg_id'];
        if(!firstMid||$this.userCache[kUid]['ajax_upload_msg']||$('#pp_message_msg_'+firstMid)[0]) return;
        $this.userCache[kUid]['ajax_upload_msg']=true;
        //console.log('UPLOAD MSG', kUid);
        var $listBox=$this.userCache[kUid]['list_msg'];
        if($this.userCache[kUid]['loader']){
            $this.userCache[kUid]['loader'].show();
        }else{
            $this.userCache[kUid]['loader']=createLoader('pp_messages_upload_msg_loader',false).prependTo($listBox);
        }
        $this.reInitToScroll($this.userCache[kUid]['pl'], 0);

        $this.userCache[kUid]['limit_start']+=$this.imHistoryMessages;

        var fromGroupId=$this.userCache[kUid]['fromGroupId'],
            toGroupId=$this.userCache[kUid]['toGroupId'];

        var url=url_ajax+'?cmd=uploading_msg',
        dataRes={
            user_id:uid,
            limit_start:$this.userCache[kUid]['limit_start'],
            group_im_id:groupId,
            from_group_id:fromGroupId,
            to_group_id:toGroupId,
        },
        fnStopLoad=function(){
            if ($this.userTo!=uid && $this.groupId!=groupId) {
                $this.userCache[kUid]['loader'].aSlideUp({dur:200});
                $this.userCache[kUid]['ajax_upload_msg']=false;
                $this.userCache[kUid]['limit_start']-=$this.imHistoryMessages;
                return true;
            }
            return false;
        },
        fnSuccess=function(res){
            var data=checkDataAjax(res);
            if(data!==false){
                if (fnStopLoad())return;
                var $data=$(trim(data)), $listMsg=$data.find('.pp_message_msg_item');
                if($('#pp_message_msg_'+firstMid,$listMsg)[0]||!$listMsg[0]){
                    $this.userCache[kUid]['first_msg_id']=0;
                }
                var fnComplete=function(){
                    $this.showUploadingMsg(kUid,$listMsg);
                },
                loaderParam={
                    step:function(){$this.reInitToScroll($this.userCache[kUid]['pl'], 0)},
                    complete:fnComplete,
                    duration:300
                };
                $this.userCache[kUid]['loader'].aSlideUp({complete:fnComplete,param:loaderParam});
            }else{
                fnStopLoad();
                alertServerError();
            }
        },
        fnError=function(){
            if(!fnStopLoad())fnLoad();
        },
        fnLoad=function(){
            debugLog('IM uploadingMsg', dataRes);
            $ajax(url, dataRes, fnSuccess, fnError);
        };
        fnLoad();

        /*$.post(url_ajax+'?cmd=uploading_msg',dataRes, function(res){

            var data=checkDataAjax(res);
            if(data!==false){
                if ($this.userTo==uid) {
                    $this.userCache[uid]['loader'].aSlideUp({dur:200});
                    $this.userCache[uid]['ajax_upload_msg']=false;
                    return;
                }
                //console.log(data);
                var $data=$(trim(data)), $listMsg=$data.find('.pp_message_msg_item');
                if($('#pp_message_msg_'+firstMid,$listMsg)[0]||!$listMsg[0]){
                    $this.userCache[uid]['first_msg_id']=0;
                }
                var fnComplete=function(){
                    $this.showUploadingMsg(uid,$listMsg);
                },
                loaderParam={
                    step:function(){$this.reInitToScroll($this.userCache[uid]['pl'], 0)},
                    complete:fnComplete,
                    duration:300
                };
                $this.userCache[uid]['loader'].aSlideUp({complete:fnComplete,param:loaderParam});
            }else{
                $this.userCache[uid]['loader'].aSlideUp({dur:200});
                alertServerError();
                $this.userCache[uid]['ajax_upload_msg']=false;
            }
        })*/
    }

    this.showUploadingMsg = function(kUid,$listMsg){
        if(!$listMsg[0]){
            $this.userCache[kUid]['ajax_upload_msg']=false;
            return;
        }

        $this.prepareImage($listMsg,false);

        var $listUser=$this.userCache[kUid]['list_msg'],
            $msg, mid, t=1000, i=$listMsg.length-1, mUid, m=0,
            $firstMsg=$('.pp_message_msg_item:first',$this.userCache[kUid]['list_msg']),
            $firstPhoto=$firstMsg.find('.pp_message_profile_pic'),
            $elH=[], posY,
            fnReintScroll=function(is){
                is=is||false;
                posY=$firstMsg.position().top;
                if(is&&$elH[0]){
                    $elH.each(function(){
                        posY=posY-$(this).outerHeight(true);
                    })
                }
                /*if (posY>=$this.userCache[kUid]['pl'].contentSize-$this.userCache[kUid]['pl'].viewportSize) {
                    posY='bottom';
                }*/
                //(pl,posY,call,noAnimate)
                $this.reInitToScroll($this.userCache[kUid]['pl'], posY, false, !is);
            };

        $listUser.removeClass('animate');
        (function fu(){
            if(i<0){
                if ($listMsg[0]) {
                    $listMsg.insertAfter($this.userCache[kUid]['loader']);
                    fnReintScroll();
                    setZeroTimeout(function(){
                        if (isMobile()) {
                            $this.userCache[kUid]['ajax_upload_msg']=false;
                        } else {
                            $listUser.oneTransEnd(function(){
                                $listUser.toggleClass('animate_1 animate');
                                $this.userCache[kUid]['ajax_upload_msg']=false;
                            },'top').addClass('animate_1');
                        }
                        fnReintScroll(true)
                        $this.initTooltipData($listMsg);
                    })
                } else {
                    fnReintScroll();
                    !isMobile() && $listUser.addClass('animate');
                    $this.userCache[kUid]['ajax_upload_msg']=false;
                }
                return false;
            }

            $msg=$listMsg.eq(i);
            mUid=$msg.data('msgUserId');
            if($firstPhoto[0] && mUid==$firstMsg.data('msgUserId')){
                $firstPhoto.oneTransEnd(function(){
                    $(this).remove()
                },'opacity').css({opacity:0});
            }
            $firstPhoto=[];
            //$msg.addClass(mUid==$this.guid?'to_show_upload':'to_show_upload left');
            mid=$msg.data('id');
            if($('#pp_message_msg_'+mid,$this.$messagesBox)[0]){
                $msg.remove()
            } else {
                if(++m<4)$elH=m==1?$msg:$elH.add($msg);
            }
            i--; fu();
            /*if(!$('#pp_message_msg_'+mid,$this.$messagesBox)[0]){
                $msg.insertAfter($this.userCache[kUid]['loader']);
                fnReintScroll();
                /*$this.showMsgBouncein($msg.css({animationDuration:(t*=.7)+'ms'}),
                                      'insertAfter',
                                      $this.userCache[kUid]['loader'],
                                      fnReintScroll,
                                      function(){i--; fu();});*/
            /*} else{
                i--; fu();
            }*/
        })()
    }

    this.setLastId = function(mid){
        mid=mid*1;
        console.log('SET LAST ID', mid, (mid^0)!==mid, last_id>=mid);
        if((mid^0)!==mid||last_id>=mid)return;
        console.log('SET LAST ID :', mid);
        last_id=mid;
    }

    this.updateMsgPreviewToMoveFirst = function(uid,groupId,kUid,$msg){
        console.log('%cIM: updateMsgPreviewToMoveFirst', 'background: #73afda', uid,groupId,kUid);
        $this.moveImToFirst(kUid,true);
        if ($msg.is('.sent')) {//$this.userTo!=uid&&
            $this.setNewMsg($msg,uid,groupId,kUid);
        } else {
            $this.removePreviewNewMsg(kUid);
        }
        $this.updateMsgPreview($msg);
    }

    /* Update preview msg */
    this.updateMsgPreview = function($msg){
        var data=$msg.data(),
            toUid=data['toUserId'],
            groupId=data['groupId'],
            kUid=$this.getKeyUid(toUid, groupId);

        if(!$this.userCache[kUid])return;
        var mUid=data['msgUserId'],
            msg=data['msg'].toString(),
            $msgPreview=$('<div>'+msg+'</div>'),
            //msg=$msg.find('.msg > span').html(),
            $preview=$this.userCache[kUid]['preview_msg'],
            $youMsg=$preview.find('.you_message'),
            isHidden=$preview.isHidden(),
            $cont;

        if (!$msgPreview.find('.fa-picture-o, .fa-play-circle, i.image_preview, span')[0]) {
            msg=strToHtml(msg, true);
        }

        if ($preview.is(':empty')) {
            $preview.hide();
        }
        if($youMsg[0] && $this.guid==mUid){
            $cont=$youMsg;
        } else {
            $cont=$preview;
            if($this.guid==mUid){
                msg=l('you_message').replace(/{message}/, msg);
            }
        }

        if(data['msg'] === '' && data['audioMessageId']) {
            msg += '<i class="fa fa-play-circle" aria-hidden="true"></i>';
            //console.log('message player added', msg);
        }

        if(isHidden){
            $cont.html(msg).removeClass('to_hide');
        }else{
            $cont.stop().fadeOut(200,function(){
                $cont.html(msg).stop().fadeIn(200);
            })
        }
        if($preview.is(':hidden')){
            $preview.aSlideDown({dur:250});
        }
    }
    /* Update preview msg */

    this.notifNewMsg = function(){
        debugLog('IM: Notif Sound',mobileAppLoaded && !getGUserOption('sound'),'#d0e8cd');
        if(mobileAppLoaded && !getGUserOption('sound'))return;
        playSound();
    }

    this.notifFromNoVisibilityPage = function(){
        if($this.isVisible())return;
        $this.notifNewMsg();
    }

    this.updateServer = function($data){
        debugLog('IM: server update',$data[0]?true:false,'#d0e8cd');
        if(!$data[0])return;

        var $listMsg=$data.find('.pp_message_msg_item'),
            fnUpdateScriptAfter=function(){
                $data.find('script.script_update_after').appendTo('#update_server');
            };

        $data.find('script.script_update_before').appendTo('#update_server');
        if(!$listMsg[0]){
            fnUpdateScriptAfter();
            return;
        }
        debugLog('IM: server update ITEMS',$listMsg[0].length*1,'#d0e8cd');
        var toUserId, msgUserId, groupId, kUid, d=225, j=0;
        (function fu(){
            var $item=$listMsg.eq(j);
            if(!$item[0]){
                fnUpdateScriptAfter();
                return;
            }

            toUserId=$item.data('toUserId');
            groupId=$item.data('groupId');
            msgUserId=$item.data('msgUserId');
            kUid=$this.getKeyUid(toUserId, groupId);

            $this.setLastId($item.data('id'));
            if(!$('#'+$item[0].id)[0] && !$('#pp_message_msg_'+$item.data('send'))[0]){
                if ($this.userCache[kUid]) {

                    $this.updateMsgPreviewToMoveFirst(toUserId,groupId,kUid,$item);
                    if ($this.userCache[kUid]['list_msg']){
                        var $wr=$this.userCache[kUid]['list_msg'].find('.pp_message_msg_item.writing');
                        if($wr[0]){
                            $this.deleteMsg($wr, kUid, function(){
                                $this.showMsgBouncein($item.addClass('to_show'),false,false,'bottom',function(){
                                    j++; fu();
                                },0,true)
                            })
                        } else{
                            $this.showMsgBouncein($item.addClass('to_show'),false,false,'bottom',function(){
                                j++; fu();
                            },0,true)
                        }
                    } else {
                        j++; fu();
                    }
                } else {
                    if(toUserId && groupId && $item.data('toGroupId') && !$item.data('fromGroupId')) {
                        if(groupId == siteGroupId) {
                            return false;
                        }
                    }
                    console.log(toUserId, groupId, $item.data('toGroupId'), $item.data('fromGroupId'), siteGroupId);
                    $this.openOneIm(toUserId, groupId, $item.data('toGroupId'), $item.data('fromGroupId'));
                }
            }else{
                j++; fu();
            }
        })();
    }

    this.updateOnlineUsers = function(usersStatus){
        //console.log('ONLINE IM ONE',usersStatus);
        var l=Object.keys(usersStatus).length,i=1;
        for (var uid in usersStatus) {
            users_list[uid]=usersStatus[uid];
            $this.setOnlineUser(uid, usersStatus[uid]);
            if(l==i && $this.isShowOnlyOnline){
                $this.showOnlyOnline(true,true);
            }
            i++;
        }
    }

    this.setOnlineUser = function(uid, status){
        var $contact=$('.pp_message_user_'+uid+', .pp_message_user_title_info_'+uid);
        if(!$contact[0])return;
        $contact[status?'addClass':'removeClass']('online');
    }

    this.getReadMsgFromIm = function(){
        var getReadMsgFromIm={};
        for (var uid in $this.userCache) {
            if($this.userCache[uid]['list_msg']&&$this.userCache[uid]['list_msg'][0]
                &&$this.userCache[uid]['list_msg'].find('.icon_check_msg.to_hide')[0]){
                getReadMsgFromIm[$this.userCache[uid]['uid']]=1;
                /*
                 getReadMsgFromIm[uid]={
                    uid:$this.userCache[uid]['uid'],
                    group:$this.userCache[uid]['groupId']
                };
                */
            }
        }
        return JSON.stringify(getReadMsgFromIm);
    }

	this.showReadMsg = function(list){
        for (var uid in list) {
            if($this.userCache[uid]&&$this.userCache[uid]['list_msg']&&$this.userCache[uid]['list_msg'][0]){
                var id=list[uid]*1;
                if($('#msg_read_'+id).is('.to_hide')){
                    $this.userCache[uid]['list_msg'].find('.icon_check_msg.to_hide').each(function(){
                        var $el=$(this),
							audio=$el.closest('.msg').find('.im_audio_message')[0];
                        if($el.data('mid')<=id && !audio)$el.removeClass('to_hide');
                    })
                }
            }
        }
    }

	this.getReadAudioMsgFromIm = function(){
        var getReadAudioMsgFromIm={};
        for (var uid in $this.userCache) {
            if($this.userCache[uid]['list_msg']&&$this.userCache[uid]['list_msg'][0]
                &&$this.userCache[uid]['list_msg'].find('.icon_check_msg.to_hide')[0]){
				$this.userCache[uid]['list_msg'].find('.icon_check_msg.to_hide').each(function(){
					var $el=$(this),
						audio=$el.closest('.msg').find('.im_audio_message')[0];
					if(audio){
						getReadAudioMsgFromIm[$el.data('mid')]=1;
					}
				})
            }
        }
        return JSON.stringify(getReadAudioMsgFromIm);
    }

	this.showReadAudioMsg = function(msgs){
        for (var mid in msgs) {
			var $el=$('#msg_read_'+mid);
            if($el.is('.to_hide')){
				var audio=$el.closest('.msg').find('.im_audio_message')[0];
				if(audio)$el.removeClass('to_hide');
            }
        }
    }

    this.prepareMoreMenu = function($msg){
        var kUid=$this.getKeyUid($this.userTo, $this.groupId);
        $msg=defaultFunctionParamValue($msg,$('.pp_message_msg_item', $this.userCache[kUid].list_msg));
        $this.$ppLinkClearChat[$msg[0]?'show':'hide']();
        $this.$ppUserMenuStreetchat[$this.userCache[kUid]['groupId']?'hide':'show']();
        $this.$ppUserMenuCalendar[$this.userCache[kUid]['groupId']?'hide':'show']();
		$this.$ppUserMoreMenuReport[$this.userCache[kUid]['report_sent']?'hide':'show']();
        if(!$this.$ppUserMoreMenuOneLink){
            $this.$ppUserMoreMenuLi[$msg[0]?'show':'hide']();
        }
    }

    this.confirmCloseChat = function(){
        $this.closeMoreMenu();
        confirmCustom(l('message_close_conversation'), function(){$this.clearChat(1)});
    }

    this.confirmClearChat = function(){
        $this.closeMoreMenu();
        confirmCustom(l('message_clear_conversation'), function(){$this.clearChat(0)});
    }

    this.clearChat = function(onlyCloseIm){
        var uid=$this.userTo,
            kUid=$this.getKeyUid($this.userTo, $this.groupId),
            firstMid=$this.userCache[kUid]['first_msg_id'];
        $this.userCache[kUid]['first_msg_id']=0;
        onlyCloseIm=onlyCloseIm||0;

        $this.showContentLoader();
        $this.prepareMoreMenu(false);

        var fromGroupId=$this.userCache[kUid]['fromGroupId'],
            toGroupId=$this.userCache[kUid]['toGroupId'],

            data={user_id:uid,
                  group_im_id:$this.groupId,
                  from_group_id:fromGroupId,
                  to_group_id:toGroupId,
                  get_count_msg_all:1,
                  only_close_im:onlyCloseIm
            };

        $.post(url_ajax+'?cmd=clear_history_messages',data,function(res){
            $this.hideContentLoader();
            $this.sendMsgControlsDisabled();
            var data=checkDataAjax(res);
            if(data!==false){
                clCounters.update(data);
                if(onlyCloseIm){
                    $this.blockUserResponse(kUid);
                } else {
                    $this.userCache[kUid]['list_msg'].html('');
                    var msg='';//l('you_message').replace(/{message}/, l('no_messages_yet'));
                    $this.userCache[kUid]['preview_msg'].aSlideUp({dur:250,complete:function(){$(this).html(msg)}});
                }
            }else{
                alertServerError(true);
                $this.userCache[kUid]['first_msg_id']=firstMid;
            }
        })
        return false;
    }

    this.closeMoreMenu = function(){
        $this.closeChatMenu();
        $this.closeMsgMenu();
    }

    this.closeChatMenu = function(){
        if($this.$ppUserMoreMenuBl[0] && $this.$ppUserMoreMenuBl.is('.in'))$this.$ppUserMoreMenuBl.collapse('hide');
    }

    this.confirmBlockUser = function(){
        $this.closeMoreMenu();
        confirmCustom(l('do_you_want_to_block_the_user'), function(){
            var uid=$this.userTo,
                kUid=$this.getKeyUid($this.userTo, $this.groupId),
                data=false,
                cmd='block_visitor_user';
            $this.showContentLoader();

            if($this.groupId){
                data={group_im_id:$this.groupId,
                      from_group_id:$this.userCache[kUid]['fromGroupId'],
                      to_group_id:$this.userCache[kUid]['toGroupId']},
                cmd='block_user_group';
            }
            clProfile.blockUser(false,uid,cmd,function(){$this.blockUserResponse(kUid)},data);
        })
    }

    this.blockUserResponse = function(kUid){
        $this.hideContentLoader();
        var $contact=$this.userCache[kUid]['contact'],
            $firstIm=$contact.next('.contact:visible');
        $contact.aSlideUp({dur:$this.durMoveChat, complete:function(){
            $contact.remove();
            $this.userCache[kUid]['list_msg'].remove();
            $this.deleteDataOneChat();
            $this.initControls();
        }})
        if($firstIm[0]){
            $this.setActiveFirstChat($firstIm);
        }else{
            $this.$ppNewMsgLink.addClass('disabled');
            $this.hideUserContent($this.close);
        }
    }

    this.redirectToProfile = function(e,url){
        if($(e.target).closest('#pp_messages_contacts').width()<59)return;
        redirectUrl(url);
    }

    this.getOnlineUser = function(kUid){
        var $contact=$this.userCache[kUid]['contact'];
        if(!$contact[0])return false;
        return $contact.is('.online');
    }

    this.inviteVideoChat = function($link){
        var kUid=$this.getKeyUid($this.userTo, $this.groupId);
        clVideoChat.checkInvite($link.data('uid', $this.userTo), $this.getOnlineUser(kUid), $this.groupId, $this.userCache[kUid]['groupType'] == 'page');
    }

    this.inviteAudioChat = function($link){
        var kUid=$this.getKeyUid($this.userTo, $this.groupId);
        clAudioChat.checkInvite($link.data('uid', $this.userTo), $this.getOnlineUser(kUid), $this.groupId, $this.userCache[kUid]['groupType'] == 'page');
    }

    this.inviteStreetChat = function($link){
        var kUid=$this.getKeyUid($this.userTo, $this.groupId);
        clCityStreetChat.invite($link.data('uid', $this.userTo), $this.getOnlineUser(kUid));
    }


    this.toCalendar = function($link){
        var kUid=$this.getKeyUid($this.userTo, $this.groupId);
        redirectUrlLoader($link, $this.userCache[kUid]['calendar_url'])
    }

    this.updateWritingUsers = function(writingUsers){
        debugLog('IM updateWritingUsers', writingUsers);
        for (var uid in writingUsers) {
            if(uid==$this.userTo){
                var $listMsg=$('#pp_message_list_message_'+uid),
                    $contact=$('#pp_message_user_'+uid);
                if ($listMsg[0] && $contact[0] && !$listMsg.find('.writing')[0]) {
                    var $html='<div id="pp_message_msg_writing_'+uid+'" data-id="pp_message_msg_writing_'+uid+'" data-to-user-id="'+uid+'" data-msg-user-id="'+uid+'" class="pp_message_msg_item to_show sent writing">'+
                                    '<div class="msg">'+
                                        '<div class="load_dot">'+
                                            '<div class="line"></div>'+
                                            '<div class="line"></div>'+
                                            '<div class="line"></div>'+
                                        '</div>'+
                                   '</div>'+
                            '</div>';
                    $html=$($html).prepend($contact.find('button').clone().addClass('pp_message_profile_pic pp_message_profile_pic_'+uid));
                    $this.showMsgBouncein($html);
                }
                break;
            }
        }
    }

    this.deleteMsg = function($msg, kUid, call){
        var $listMsg=$('#pp_message_list_message_'+kUid).removeClass('animate');
        var scrollPl=$this.userCache[kUid]['pl'];

        var checkPhoto=$msg.is('.writing') ? false : true,
            $photo=[],
            $msgNext=[],
            $photoRemove=[];
        if (checkPhoto) {
            $photo=$msg.find('.pp_message_profile_pic');
            if($photo[0]){
                $msgNext=$msg.next('.pp_message_msg_item');
                var cl=$msg.is('.sent')?'.sent':'.replies';
                if($msgNext.is(cl)){
                    if($msgNext.find('.pp_message_profile_pic')[0]){
                        $photo=[];
                    }else{
                        $photo=$photo.clone().stop().fadeTo(0,0);
                    }
                } else {
                    $photo=[];
                    $photoRemove=$msgNext.find('.pp_message_profile_pic');
                }
            }
        }

        var $cont=$msg.addClass('animate_sent').css('bottom',0).find('.msg'),
            h=Math.round($cont.height()/2)+1;

	    $msg.stop().animate({
                        //marginBottom: '-'+(h*2+15)+'px'
                        height:'toggle',
                        opacity: .25,
                        marginBottom: '-15px'
                        },
                        {duration:$this.dur,//2000,
                        step:function(){
                            $this.reInitToScroll(scrollPl,'relative');
                        },
                        complete:function(){
                            if ($photo[0] && $msgNext[0]) {
                                $msgNext.prepend($photo.stop().fadeTo(150,1));
                            }
                            if($photoRemove[0]){
                                $photoRemove.stop().fadeTo(150,0,function(){
                                    $photoRemove.remove();
                                })
                            }

                            $msg.remove();
                            $this.reInitToScroll(scrollPl,'relative');
                            if(typeof call=='function')call();
                            //$msg.removeClass('animate_sent');
                            if(!$listMsg.find('.animate_sent')[0]){
                                $listMsg.addClass('animate');
                            }
                        }})
    }

    this.deleteWritingUsers = function(deleteWritingUsers){
        debugLog('IM deleteWritingUsers', deleteWritingUsers);
        for (var uid in deleteWritingUsers) {
            var $listMsg=$('#pp_message_list_message_'+uid),
                $wr=$listMsg.find('.pp_message_msg_item.writing');
            if($wr[0]){
                if(uid!=$this.userTo){
                    $wr.remove();
                } else {
                    $this.deleteMsg($wr, uid);
                }
            }
        }
    }

    this.initTooltipData = function($context){
        $context=$context||'#pp_message_list_message';
        $('[data-tooltip-data="true"]:not(.init_data)').each(function(){
            var $el=$(this).addClass('init_data');
            var placement='right';
            if($el.closest('.replies')[0]){
                placement='left';
            }
            if (isMobileSite) {
                /*$el.tooltip({trigger:'manual',placement:'top'})//,container:$el.closest('.viewport')
                .click(function(){
                    $el.tooltip('show');
                    clearTimeout($el.data('action'));
                    $el.data('action',setTimeout(function(){
                        $el.tooltip('hide');
                    },1000))
                })*/
            } else {
                $el.tooltip({viewport:'',container:'#pp_messages_chat_content',placement:placement,delay:{'show':500}})
                .click(function(){
                    $el.tooltip('hide')
                })
            }
        })
    }

    /* Height */
    this.getDHeight = function(){
        var hw=$win[0].innerHeight;
        return hw - 50 - .03*hw;
    }

    this.getHeightIm = function(){
        var height=$.cookie('edge_height_im')*1,
            hd=$this.getDHeight();
        if (height < 300 || height > hd) {
            height = hd;
        }
        return height;
    }

    this.setHeightIm = function(height){
        if(height===0)return;
        $this.$ppChat.css({height:height,maxHeight:height});
    }

    this.endResizeIm = function(e){
        e&&e.preventDefault();
        $doc.off('mouseup touchend', $this.endResizeIm)
            .off('mousemove touchmove', $this.resizingIm);
        $.cookie('edge_height_im', $this.$ppChat.height());
    }

    this.resizingIm = function(e){
        if(!$this.$ppChat[0] || !$this.isVisible())return;
        var mouse={},height;
            mouse.y = (e.clientY || e.pageY || e.originalEvent.touches[0].clientY) + $win.scrollTop();

        height = $this.eventState.container_height - $this.eventState.mouse_y + mouse.y;
        var hd=$this.getDHeight();
        if (height > 300 && height < hd) {
            $this.setHeightIm(height);
            $this.imH=height;
            $this.prepareListMsg($this.ppMsgH,$this.ppMsgH,'relative');
        }
    }
    /* Height */

    /* Width */
    this.getDWidth = function(){
        var ww=$win[0].innerWidth;
        return ww - 20;
    }

    this.getWidthIm = function(){
        var width=$.cookie('edge_width_im')*1,
            wd=$this.getDWidth();
        if (width < 768 || width > wd) {
            width = wd;
        }
        return width;
    }

    this.setWidthIm = function(width){
        if(width===0)return;
        $this.$ppChat.css({width:width,maxWidth:width});
    }

    this.resizingRightIm = function(e){
        if(!$this.$ppChat[0] || !$this.isVisible())return;

        var mouse={},width;
            mouse.x = (e.clientX || e.pageX || e.originalEvent.touches[0].clientX) + $win.scrollLeft();

        width = $this.eventState.container_width - $this.eventState.mouse_x + mouse.x;

        var wd=$this.getDWidth();
        if (width > 768 && width < wd) {
            $this.setWidthIm(width);
            $this.imW=width;
            $this.prepareImageResize()
        }
    }

    this.endResizeRightIm = function(e){
        e&&e.preventDefault();
        $doc.off('mouseup touchend', $this.endResizeRightIm)
            .off('mousemove touchmove', $this.resizingRightIm);
        $.cookie('edge_width_im', $this.$ppChat.width());
    }
    /* Width */

    /* Resize */
    this.saveEventState = function(e){
        if(!$this.$ppChat[0])return;
        $this.eventState.container_height = $this.$ppChat.height();
        $this.eventState.container_width = $this.$ppChat.width();
        $this.eventState.mouse_x = (e.clientX || e.pageX || e.originalEvent.touches[0].clientX) + $win.scrollLeft();
        $this.eventState.mouse_y = (e.clientY || e.pageY || e.originalEvent.touches[0].clientY) + $win.scrollTop();
        if(typeof e.originalEvent.touches !== 'undefined'){
            $this.eventState.touches = [];
            $.each(e.originalEvent.touches, function(i, ob){
                $this.eventState.touches[i] = {};
                $this.eventState.touches[i].clientX = 0+ob.clientX;
                $this.eventState.touches[i].clientY = 0+ob.clientY;
            });
        }
        $this.eventState.evnt = e;
    };
    /* Resize */

    this.removeMsgFromChat = function(mid, fromMe){
        fromMe=fromMe||false;
        $this.closeMsgMenu();
		var $msg=$('#pp_message_msg_'+mid),
			$msgPrev=$msg.prev('.pp_message_msg_item');
        $this.deleteMsg($msg, $this.getKeyUid($this.userTo, $this.groupId), function(){
        })
		if($msgPrev[0]){
			$this.updateMsgPreview($msgPrev);
		} else {
			var kUid=$this.getKeyUid($this.userTo, $this.groupId);
            $this.userCache[kUid]['preview_msg'].aSlideUp({dur:250,complete:function(){$(this).html('')}});
		}

        $.post(url_ajax+'?cmd=delete_messages',{mid:mid,from_me:fromMe?1:0},function(res){
            var data=checkDataAjax(res);
            if(data!==false){

            }
        })
    }

    this.moreMsgMenuVisible = function(el){
        var $el=$(el),
            $menu=$el.next('.more_menu_collapse');
        if ($menu.is(':hidden')) {
            var $viewport=$el.closest('.viewport'),
                //$lastMsg=$('.pp_message_msg_item:last',$viewport).find('.more_msg_menu'),
                my='', at='', is=true;
            if (false && $lastMsg[0]==$menu[0] && !inViewport($menu,{container:$viewport[0],threshold:72})) {
                my='right bottom-19';
                at='right-5 bottom';
            } else if($menu.closest('.pp_message_msg_item.sent')[0]){
                my='left top';
                at='right-15 bottom';
            } else {
                my='right top';
                at='left+15 bottom';
            }

            $menu.stop().slideDown({
            duration:150,
            step:function(){
                if(is){
                    is=false;
                    $menu.position({my:my,at:at,of:$el,within:$viewport,collision:'flip'});
                }
            }})
        } else {
            $menu.stop().slideUp(200);
        }
    }

    this.closeMsgMenu = function($notEl){
        $notEl=$notEl||[];
        $('.more_msg_menu:visible', $this.$ppContent).not($notEl).stop().slideUp(200);
    }

    this.checkExistenceMessages = function(deleteMsgs){
        deleteMsgs = jQuery.parseJSON(deleteMsgs);
        if(!deleteMsgs)return;
        var sel='';
        for (var id in deleteMsgs) {
            if(sel){
              sel +=', #pp_message_msg_'+id;
            } else {
              sel +='#pp_message_msg_'+id;
            }
        }
        if (sel) {
            var $listMsg=$(sel);
            if ($listMsg[0]) {
                var toUserId, msgUserId, groupId, j=0, mid;
                (function fu(){
                    var $item=$listMsg.eq(j);
                    if(!$item[0]){
                        return;
                    }
                    toUserId=$item.data('toUserId');
                    groupId=$item.data('groupId');
					mid=$item.data('id')+'';
					if(mid.indexOf('system')!==-1){
						j++; fu();
					} else {
						if (toUserId==$this.userTo && $this.groupId==groupId) {
							$this.deleteMsg($item, $this.getKeyUid(toUserId, groupId), function(){
								j++; fu();
							})
						} else {
							$item.remove();
							j++; fu();
						}
					}
                })()
            }
        }

        /*for (var id in deleteMsgs) {
            var $msg=$('#pp_message_msg_'+id, $this.$ppContent),
                uid=deleteMsgs[id];
            if($msg[0]){
                if(uid==$this.userTo){

                    $this.deleteMsg($('#pp_message_msg_'+mid), $this.userTo, function(){
                    })
                } else {
                    $msg.remove();
                }
            }
        }*/
    }

    this.getMsgCurrentIm = function(){
        var getMsgFromIm={};
        if($this.isVisible()){
            var $imVisible=$('.pp_message_user_list_message:visible',$this.$ppContent);
            if($imVisible[0]){
                var uid=$imVisible.data('uid'),
                    kUid=uid+'_'+$imVisible.data('groupId');
                if($this.userCache[kUid]['list_msg']&&$this.userCache[kUid]['list_msg'][0]){
                    $this.userCache[kUid]['list_msg'].find('.pp_message_msg_item').not('.writing').each(function(){
                        var id=$(this).data('id'), idS=id+'';
						if (idS.indexOf('system')==-1) {
							getMsgFromIm[id]=uid;
						} else {
						}
                    })
                }
            }
        }
        return JSON.stringify(getMsgFromIm);
    }

    this.getKeyUid = function(uid, groupId){
        return uid+'_'+groupId;
    }

    this.openMessagesFromAppNotifications = function(uid, groupUserId, groupId){
        if(!checkLoginStatus())return false;
        groupUserId=groupUserId||0;
        groupId=groupId||0;

        if($this.isOpened()){
            var kUid=$this.getKeyUid(uid, groupId);
            if ($('#pp_message_user_'+kUid)[0]) {
                $this.openIm(uid, groupId);
            } else {
                $this.$pp.one('hidden.bs.modal',function(){
                    checkOpenModal();
                    setZeroTimeout(function(){
                        $this.show(uid,[],groupUserId,groupId);
                    })
                })
                $this.closePopup();
            }
        } else {
            $this.show(uid,[],groupUserId,groupId);
        }


        return true;
    }

    /* Upload image */
    this.checkPaydUploadImage = function() {
        if(!userAllowedFeature['upload_image_chat_paid']){
            confirmCustom(l('upload_im_need_upgrade'), function(){
                redirectUrl(urlPagesSite.upgrade)
            },l('alert_html_alert'));
            return true;
        }
        return false;
    }

    this.changeUploadImage = function($file) {
        if($this.checkPaydUploadImage())return false;
        $file.parent('form').find('input[type=submit]').click();
    }

    this.clickUpload = function($file) {
        $file.next('input[type=reset]').click();
    }

    this.processUploadImage=false;
    this.isImageLoaded=0;
    this.clearUploadImage = function(reset,checkPayd,e){
        if (checkPayd||false) {
            if($this.checkPaydUploadImage())return;
        }
        var $bl=$this.addImageIm;
        if (reset||false) {
            if ($bl.is('.disabled')) {
                return;
            }
        }
		if (e) {
			var $targ=$(e.target);
			if ($targ.is('#pp_message_img_editor') || $targ.closest('#pp_message_img_editor')[0]) {
				return;
			}
		}
		$this.fileImageUrl = '';
        $bl.addClass('disabled');
		$bl.removeClass('disable_editor_image');
        $bl.find('.fa-camera').attr('title', l('upload_image'));
		$this.$ppMsg.trigger('autosize');

        $this.processUploadImage=false;
        $this.isImageLoaded=0;
        $this.addImageIm.removeClass('to_hide');
        $this.addImageImLoader.removeChildrenLoader();
        if(userAllowedFeature['upload_image_chat_paid']){
            $this.clickUpload($bl.find('.pp_message_upload_img_input_file'));
        }
    }

    this.initCheckPaydUploadImage = function(){
        if(userAllowedFeature['upload_image_chat_paid']){
            if ($this.addImageIm.is('.no_available')) {
                $this.addImageIm.removeClass('no_available');
            }

            return false;
        } else if(!$this.addImageIm.is('.no_available')) {
            $this.addImageIm.addClass('no_available');
            return true;
        }
    }

    this.initAddImage = function(){
		$this.fileImageUrl = '';
        $this.addImageIm=$('#pp_message_upload_img');
        $this.addImageImLoader=$('#pp_message_upload_img_loader');

        $this.initCheckPaydUploadImage();

        $('#pp_message_upload_img_frm').submit(function(e){
			$this.fileImageUrl = '';
            var indx=+new Date,
                frm = $(this), file = frm.find('input[type=file]'),
                fileName = file.attr('name'), formData = new FormData(),
                error = '';
                $.each(file[0].files, function(i, file){
                    if ("image/jpeg,image/png,image/gif".indexOf(file.type) === -1) {
                        error = l('accept_file_types');
                        return false;
                    }else if (file.size > clProfilePhoto.maxphotoFileSize) {
                        error = clProfilePhoto.maxphotoFileSizeLimit;
                        return false;
                    }
                    formData.append(fileName, file);
                });

                if (error) {
                    alertCustom(error, l('alert_html_alert'));
                    return false;
                }
                $this.processUploadImage=true;
                $this.addImageImLoader.addChildrenLoader();
                $this.addImageIm.addClass('to_hide')
				$this.addImageIm.find('.fa-camera').attr('title', '');

                var xhr = new XMLHttpRequest();
                xhr.open("POST", url_ajax+'?cmd=upload_image_im&input_name='+fileName+'&ind='+indx);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if(xhr.status == 200) {
                            var data = xhr.responseText;
                            data = checkDataAjax(data);
                            if (data) {
                                if (data.status == 'error') {
                                    alertCustom(data.error, l('alert_html_alert'));
                                } else {
									$this.fileImageUrl = data.file;
                                    $this.isImageLoaded=indx;

									$this.addImageIm[data.image_editor_enabled?'removeClass':'addClass']('disable_editor_image');
                                    $this.addImageIm.removeClass('disabled');
                                    $this.addImageIm.find('.fa-camera').attr('title', l('upload_image_delete'));

									$this.$ppMsg.trigger('autosize');
                                }
                            }
                            $this.processUploadImage = false;
                            $this.addImageIm.removeClass('to_hide');
                            $this.addImageImLoader.removeChildrenLoader();

                            //$this.send();
                        }
                    }
                };
                xhr.send(formData);
                return false;
        });
    }

	$this.fileImageUrl = '';
	this.openEditorImage = function(){
		if (!$this.fileImageUrl) {
			return;
		}
		imageEditorInfo = {
			id: 0,
			uid: $this.guid,
			preview: '',
			img: [],
			type: 'im_image',
			info: {},
			btn: $('#pp_message_img_editor')
		}
		$ppImageEditor.open($this.fileImageUrl);
	}

	this.initBtnImageEditor = function(){
		var $btn=$('#pp_message_img_editor'),
		w=$btn.find('span.btn_wrap')[0].offsetWidth;
		$btn.css('width', w);
	}
    /* Upload image */

    /* Popup Image */
    $this.$ppShowImage=[];
    $this.$ppShowImageClone;
    this.closePpImageBack = function(){
        if(!$this.isOpenImage)return;
        if(!backStateHistory()){
            $this.closePpImage();
        }
    }

    this.closePpImage = function(){
        $this.$ppShowImage[0] && $this.$ppShowImage.modal('hide');
    }

    this.initPpImage = function(){
        if($this.$ppShowImage[0])return;
        $this.$ppShowImageClone=$jq('#pp_im_image').clone().html();
        $this.$ppShowImage=$jq('#pp_im_image');
    }

    this.isOpenImage=false;
    this.showPopupImage = function(src, $imgOrig){
        debugLog('IM showPopupImage', src);
		if($this.isOpenImage)return;

        $this.isOpenImage=true;
        $this.$ppShowImage.empty().html($this.$ppShowImageClone);

        var $img=$('<img src="' + src + '">').addClass('fade_to_1_4 fade_to_0_4')
            .one('load',function(){
                $('.css_loader', $this.$ppShowOnePost).oneTransEnd(function(){
                    $(this).remove();
                }).addClass('hidden');
                $(this).removeClass('fade_to_0_4');
            });

        $('.photo_one_cont', $this.$ppShowImage).append($img);

        $('.pp_im_image_time_ago', $this.$ppShowImage).text($imgOrig.data('timeAgo'));
        $('.pp_im_image_date', $this.$ppShowImage).text($imgOrig.data('date'));

        $('.pp_im_image_user_name', $this.$ppShowImage)
            .attr('href', $imgOrig.data('userUrl'))
            .text($imgOrig.data('userName'));
        $('.pp_im_image_user_photo_link', $this.$ppShowImage).css('background-image', 'url('+urlFiles+$imgOrig.data('userPhoto')+')')
            .attr('href', $imgOrig.data('userUrl'));

        var isLink=isSiteOptionActive('gallery_show_download_original', 'edge_gallery_settings')
        if (isLink) {
            var id= +new Date, sel='pp_im_image_more_options_'+id;
            $('.pp_im_image_more_menu > .upload_menu_link', $this.$ppShowImage).attr('data-target','#'+sel);
            $('.pp_im_image_more_options', $this.$ppShowImage).attr('id', sel);

            $('.pp_im_image_direct_link', $this.$ppShowImage).attr('href', src);
        } else {
            $('.pp_im_image_more_menu', $this.$ppShowImage).remove();
        }

        setPushStateHistory('im_gallery');
        $this.$ppShowImage.one('hidden.bs.modal',function(){
            checkOpenModal();
        }).one('hide.bs.modal',function(){
            $this.isOpenImage=false;
            $jq('html, body').removeClass('overh');
            $this.$ppShowImage.oneTransEndM(function(){
                $this.$ppShowImage.removeClass('to_show').empty();
                $jq('body').removeClass('gallery_im_open');
            })
        }).one('shown.bs.modal',function(){
        }).one('show.bs.modal',function(){
            $this.$ppShowImage.addClass('to_show');
            $jq('body').addClass('gallery_im_open');
            $jq('html, body').addClass('overh');
        }).modal('show');

	}
    /* Popup Image */

    this.initMessages = function(){
        $this.$pp            = $jq('#pp_messages');
        $this.$ppChat        = $jq('#pp_messages_chat');
        $this.$ppChatContent = $jq('#pp_messages_chat_content');
        $this.$ppChatFoot    = $jq('#pp_messages_chat_foot');
        $this.$ppChatRightResize  = $jq('#pp_messages_chat_right_resize');
        $this.$ppSidepanel   = $jq('#pp_messages_sidepanel');
        $this.$ppContent     = $jq('#pp_messages_content');
        $this.$ppMsgList     = $jq('#pp_message_list_message');
        $this.$ppNewMsgLink  = $('.new_message_link');

        $jq('body').on('touchstart mousedown', function(e){
            var $targ=$(e.target);
            if($targ.is('.chat_wrap') || $targ.is('.navbar-default') || $targ.is('.navbar-header')){
                $this.closePopup();
            }
        }).on('click', function(e){
            var $targ=$(e.target);
            if($targ.is('.chat')||$targ.closest('.chat')[0]){
				if (!$targ.is('.emoji_bl')
					&& !$targ.closest('.emoji_bl')[0]
					 && !$targ.is('.pp_message_smile_btn')
					 && !$targ.closest('.pp_message_smile_btn')[0]
					 && !$targ.is('.pp_messages_msg')
					 && !$targ.closest('.pp_messages_msg')[0]) {
					 smileBlockHide($this.$ppSmileBtn)
				}

				if (!$targ.is('.stickers_bl')
					&& !$targ.closest('.stickers_bl')[0]
					 && !$targ.is('.pp_message_sticker_btn')
					 && !$targ.closest('.pp_message_sticker_btn')[0]) {
					 stickerBlockRemove($('#pp_message_sticker_btn'));
				}

                if (!$targ.is('.more')&&!$targ.closest('.more')[0]) {
                    $this.closeChatMenu();
                }
                //if (!$targ.is('.more_msg_menu')&&!$targ.closest('.more_msg_menu')[0]) {
                    //$this.closeMsgMenu();
                //}
                if(!$targ.is('.message_extension')&&!$targ.closest('.message_extension')[0]){
                    $this.closeMsgMenu();
                }
                return;
            } else if($targ.is('.pp_gallery_overflow') || $targ.is('.navbar-default') || $targ.is('.navbar-header')){
                $this.closePpImageBack();
            }
        }).on('click', '.message_more_menu .ellipsis', function(e){
            $this.closeMsgMenu($(this).next('.more_menu_collapse'));
            $this.moreMsgMenuVisible(this);
        }).on('click', '.lightbox_pics_im', function(e){
            $this.showPopupImage(this.href, $(this).find('img'));
            return false;
        }).on('click', '.menu_messages_edge', function(e){
            clProfile.sendMessages($(this));
            return false;
        }).on('click', '.menu_messages_edge_group', function(e){
            var groupUserId = siteGroupUserId? siteGroupUserId : '0';
            var groupId = siteGroupId? siteGroupId : '0';
            var uid = user_id? user_id : '0';

            this.groupOwner = true;

            clMessages.show(uid, $(this), groupUserId, groupId);
            return false;

            // clProfile.sendMessages($(this));
            // return false;
        })

        //pp_message_user_list_message
        $('body').on('mouseover', '.pp_message_user_list_message', function(e){
            var $el=$(this),
                uid=$this.getKeyUid($el.data('uid'), $el.data('groupId'));
            if($this.userCache[uid]
               &&$this.userCache[uid]['list_msg'][0]
               &&($('.to_new',$this.userCache[uid]['list_msg'])[0]
                    ||$this.userCache[uid]['preview_msg'].is('.to_new'))){
                $this.removeNewMsg(uid);
            }
        })

        $this.$ppChatFoot.on('touchstart mousedown', function(e){
            if($this.$ppChatFoot.is(':hidden'))return;
            e.preventDefault();
            e.stopPropagation();
            $this.saveEventState(e);
            $doc.on('mousemove touchmove', $this.resizingIm)
                .on('mouseup touchend', $this.endResizeIm);
        })


        $this.$ppChatRightResize.on('touchstart mousedown', function(e){
            if($this.$ppChatRightResize.is(':hidden'))return;
            e.preventDefault();
            e.stopPropagation();
            $this.saveEventState(e);
            $doc.on('mousemove touchmove', $this.resizingRightIm)
                .on('mouseup touchend', $this.endResizeRightIm);
        })


        /*$win.on('resize',function(){
            if($this.isVisible()&&$this.userTo){
                $('#pp_message_list_message, #pp_message_list_message .scrollbarY, #pp_message_list_message .scrollbarY .track').removeAttr('style');
                setTimeout(function(){
                    $this.reInitToScroll($this.userCache[$this.userTo]['pl']);
                },1)
            }
        })*/
    }

	/* Record audio message */
	this.mediaRecorderDetect = false;
	this.mediaRecorderApi;
	this.mediaRecorderStream;
	this.mediaRecorderProcess=false;
	this.hideAudioRecorderControl = function(){
		$this.$ppAudioRecord.remove();
		$this.$ppMsg.removeClass('im_audio_message_enabled');
	}

	this.pushChankAudioRecorder = function(e){

		var $microphone=$this.$ppAudioRecord.find('.im_audio_message_recorder_icon_bl');
		if ($this.$ppAudioRecord.data('stop_process')) {

			$this.mediaRecorderProcess=false;
			removeChildrenLoader($microphone);
			$this.$ppAudioRecord.removeClass('disabled');
			$microphone.attr('title', l('record_audio_message'));

			$this.$ppAudioRecord.data('stop_process', 0);
			return;
		}

		var mediaRecorderChunks = e.data,
			formData = new FormData(),
			url=url_ajax+'?cmd=save_im_audio_message';

		formData.append('im_msg_audio_blob', mediaRecorderChunks);

		var xhr = new XMLHttpRequest();
        xhr.open("POST", url);
        xhr.onreadystatechange = function() {
			if (xhr.readyState == 4) {
				if(xhr.status == 200) {
					var data=xhr.responseText, isErrorResponse = true,
						error=l('something_went_wrong_please_try_later');

					$this.mediaRecorderProcess=false;
					removeChildrenLoader($microphone);
					$this.$ppAudioRecord.removeClass('disabled');

                    data=checkDataAjax(data);
                    if(data) {
                        if(data.result == 'success' && data.id) {
							$this.$ppMsg.data('im-audio-message-id', data.id);

							var v=trim($this.$ppMsg.text());
							if(v==$this.$ppMsg.data('placeholder')&&!$this.$ppMsg.find('.emoji')[0]){
								$this.$ppMsg.text('');
							}

							if ($this.$ppAudioPlayCur[0]) {
								$this.$ppAudioPlayCur.remove();
							}
							$this.$ppAudioPlayCur=$this.$ppAudioPlay.clone()
								.show()
								.attr({'contenteditable':false, id:'im_audio_message_send_play'});

							$this.$ppAudioPlayCur.find('.fa-times').click(function(){
								$this.runAudioRecorder()
							})
							$this.$ppAudioPlayBtn=$this.$ppAudioPlayCur
													   .find('.im_audio_message_loader')
													   .data('audio-message-file', data.url);

							$this.$ppMsg.prepend($this.$ppAudioPlayCur.delay(20).toggleClass('im_audio_message_delete',0))
								 .trigger('autosize');

							$this.$ppAudioRecord.addClass('im_audio_message_delete');
							$microphone.attr('title', l('record_audio_message_delete'));
                            isErrorResponse=false;
						} else if (data.error) {
							error=data.error;
						}
                    }

					if(isErrorResponse) {
						$microphone.attr('title', l('record_audio_message'));
						alertCustom(error);
					}
				}
			}
        };
        xhr.send(formData);
	}

	this.stopTrakAudioRecorder = function(){
		//$this.mediaRecorderStream.getTracks().forEach(function(track) {
			//track.stop()
		//})

		$this.mediaRecorderStream && $this.mediaRecorderStream.getAudioTracks()[0].stop();
	}

	this.audioMessageInputAutosizeDelay = function(){
		setTimeout(function(){
			$this.$ppMsg.trigger('autosize');
		},200)
	}

	this.imAudioMessageDelete = function(id){
		var $microphone=$this.$ppAudioRecord.find('.im_audio_message_recorder_icon_bl');
		$this.$ppMsg.data('im-audio-message-id', 0);
		$this.$ppAudioRecord.addClass('disabled').removeClass('im_audio_message_delete');
		$this.$ppAudioPlayCur.oneTransEnd(function(){
			$this.audioMessageInputAutosizeDelay()
		}).removeClass('im_audio_message_delete');

		$this.mediaRecorderProcess=false;

		if ($this.$ppAudioRecord.data('stop_process')) {

			$this.$ppAudioPlayBtn.data('audio-message-file', '');
			$this.$ppAudioPlayCur.remove();
			$microphone.attr('title', l('record_audio_message'));

			$this.$ppAudioRecord.removeClass('disabled').data('stop_process', 0);
			return;
		}

		addChildrenLoader($microphone);

		$.post('ajax.php', {cmd:'im_audio_message_delete', id:id}, function(res){
			var data=checkDataAjax(res);
            if(data!==false){
				setTimeout(function(){
					removeChildrenLoader($microphone);
					$this.$ppAudioPlayBtn.data('audio-message-file', '');
					$this.$ppAudioPlayCur.remove();
					$microphone.attr('title', l('record_audio_message'));
					$this.audioMessageInputAutosizeDelay();
				},200)
			} else {
				removeChildrenLoader($microphone);
				$this.$ppMsg.data('im-audio-message-id', id);
				$this.$ppAudioRecord.addClass('im_audio_message_delete');
				$this.$ppAudioPlayCur.oneTransEnd(function(){
					$this.audioMessageInputAutosizeDelay()
				}).addClass('im_audio_message_delete');
				alertCustom(l('something_went_wrong_please_try_later'));
			}
			$this.$ppAudioRecord.removeClass('disabled');
		})
	}

	this.runAudioRecorder = function(){
		if ($this.$ppAudioRecord.is('.disabled')) {
			return;
		}

		var $microphone=$this.$ppAudioRecord.find('.im_audio_message_recorder_icon_bl');
//$this.$ppAudioRecord.addClass('record');
//return;

		$this.mediaRecorderProcess=false;
		if ($this.mediaRecorderDetect === false) {
			var ssl=window.location.protocol == 'https:' || window.location.host == 'localhost';
			if(!ssl){
				alertCustom(l('your_browser_needs_ssl_certificate_to_run_audio_record'));
				$this.hideAudioRecorderControl();
				return false;
			}
		}
		$this.mediaRecorderDetect = true;

		var fnStartRecord=function(){
			if ($this.$ppAudioRecord.is('.im_audio_message_delete')) {//Delete
				if ($this.$ppAudioRecord.data('stop_process')) {
					$this.imAudioMessageDelete($this.$ppMsg.data('im-audio-message-id'))
				} else {
					confirmCustom(l('are_you_sure'), function(){
						$this.imAudioMessageDelete($this.$ppMsg.data('im-audio-message-id'))
					}, l('confirm_delete_audio_message'));
				}
			}else if ($this.$ppAudioRecord.is('.record')) {//Upload

				$this.mediaRecorderProcess=true;

				$this.$ppAudioRecord.removeClass('record')
						   .addClass('disabled');
				$microphone.attr('title', l('record_audio_message_process'));

				addChildrenLoader($microphone);

				$this.stopTrakAudioRecorder();

				$this.mediaRecorderApi.stop();

				stopAudioVisualizer('im_audio_message_visualizer');

			} else {//Record
				createAudioVisualizerElement('im_audio_message_visualizer')

				var optionsDevice = {
					//audio: { sampleSize: 16, channelCount: 1, sampleRate: 16000 } ,
					audio: true,
					video: false
				};
				navigator.mediaDevices.getUserMedia(optionsDevice).then(function(stream){
					$this.mediaRecorderStream = stream;

					initAudioVisualizer('im_audio_message_visualizer', stream);

					/*var types = ['audio/webm', 'audio/webm\;codecs=opus'];
					for (var i in types) {
						console.log(types[i] + ' - ' + (MediaRecorder.isTypeSupported(types[i]) ? 'Maybe' : 'No'));
					}*/

					optionsDevice = {
						audioBitsPerSecond: 128000,
						//bitsPerSecond:
						//mimeType : 'audio/webm'
					}
					$this.mediaRecorderApi = new MediaRecorder(stream, optionsDevice);
					$this.mediaRecorderApi.addEventListener('dataavailable',$this.pushChankAudioRecorder);
					$this.mediaRecorderApi.start();

					$this.$ppAudioRecord.oneTransEnd(function(){
						$this.$ppMsg.trigger('autosize');
					}).addClass('record');
					$microphone.attr('title', l('record_audio_message_stop'));
				}).catch(function(error) {
					console.log('Error audio record', error);
					$this.stopTrakAudioRecorder();
					alertCustom(l('error_recording_audio_message'));
				})
			}
		}
		if (isFx) {
			fnStartRecord();
		} else {
			navigator.permissions.query({name:'microphone'}).then(function(result) {
				if (result.state == 'granted') {
					fnStartRecord();
				} else if (result.state == 'prompt') {
					fnStartRecord();
				} else if (result.state == 'denied') {
					alertCustom(l('microphone_is_not_available'));
					$this.hideAudioRecorderControl();
				}
				result.onchange = function(e) {
					console.log('Microphone permissions', this.state);
				}
			})
		}
		/* Check Permissions */
	}
	/* Record audio message */

    $(function(){
        $win.on('resize', function(){setTimeout($this.prepareImageResize,1)});
        /*$win.on(evWndRes,function(){
            $this.prepareImageResize();
            //setTimeout($this.prepareImageResize,evWndResTime)
        })*/
    })

    return this;
}

function showImageIm(img) {

    var $img=$(img).addClass('to_show');
     return;

    messages.initLightbox($img.closest('a'));
    if(isOneChat){
        //messages.reInitToScrollPanelChat('relative');
    }else{
        //$img.closest('.scrollbarY').data('plugin_tinyscrollbar').update('relative');
    }
}