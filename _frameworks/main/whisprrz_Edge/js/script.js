var isMobileSite,
    isMobileUserAgent,
    isIE,
    isIos, isIosApp, isCriOS, isFxiOS, notLoaderIos, isIos12,
    evWndRes,
    evWndResTime,
    versionAndroid=0,
    isChromeWheele,
    openPopupList={};

function setSytemVars(){
    versionAndroid=getAndroidVersionUa();
    isCheckMobileSite=null;
    isMobileSite=device.mobile();
    var userAgent=navigator.userAgent;
    isMobileUserAgent=/Android|webOS|iPhone|iPad|iPod/i.test(userAgent);

    isIE=userAgent.indexOf('Trident/')>0;
    isIos=/iPhone|iPad|iPod/i.test(userAgent);
    isIosApp=/IOSWebview/i.test(userAgent);
    isIos12 = iOSversion() == 12;
    notLoaderIos=isIos && !isIosApp && (!isPwaIos || (isPwaIos && isIos12));

    isCriOS=userAgent.match('CriOS');
    isFxiOS=userAgent.match('FxiOS');
    isFx=userAgent.match('Firefox');

    isChromeWheele=false;
    if (window.WheelEvent && !window.MouseScrollEvent
        && /chrome/.test(userAgent.toLowerCase())) {
        isChromeWheele=true;
    }

    evWndRes='resize orientationchange';
    if(isIos && typeof window.orientation!='undefined'){
        evWndRes='orientationchange';
        if(isFxiOS || isCriOS){
            evWndRes='resize orientationchange';
        }
    }

    evWndResTime=200;//Test - Сhrom mobile
    if(isAppAndroid)evWndResTime=200;
    if(isFx)evWndResTime=200;
    if(isIos)evWndResTime=100;
    if(isFxiOS||isCriOS)evWndResTime=200;
}
setSytemVars();

function setWndResizeEvent(fn){
    fn();
    $win.on(evWndRes,function(){
        setTimeout(fn,evWndResTime)
    })
}
var hNavbar=0;
var hNavbarMenu=false;
var isVisiblenavMenuMobile=false;

$(function(){
    var ieVer=ieVersion();
    if(ieVer==10||ieVer==11)$jq('body').addClass('ie11');

    //isPlayerNative = isPlayerNative || isMobileSite;/Not used
    //mobileAppLoaded=true;
    if(isMobileSite) {
        $('.selectpicker').selectpicker('mobile');
    }
    //$.removeCookie('videojs_volume_last', {path:''});
    //$.removeCookie('videojs_volume', {path:''});
    //initCustomStyle();

    initClickOnLogoMainPage(false, function(){
        if (ajax_login_status) {
            if(clMessages.isOpened()){
                clMessages.closePopup();
                return true;
            } else if(clProfilePhoto.isShowGallery) {
                clProfilePhoto.closeGalleryPopup();
                return true;
            }
        }
        createLayerPage();
    });
    setPosToHistory();
    //setTimeout(function(){confirmCustom(111111111)},300);
    //setTimeout(function(){alertCustom(111111111)},300);

    $win.on('resize',function(){
        $('.error').tooltip('hide');
    })

    $jq('body').on('click', '.modal', function(e){
        closeAlert(e);
    })

    $('body').on('click', function(e){
        var $targ=$(e.target);
        if($targ.is('.navbar-toggle') || !$jq('#member_header_menu').is('.in'))return;
        if(!$targ.is('#member_header_menu') && !$targ.closest('#member_header_menu')[0]){
            $jq('#member_header_menu').collapse('hide');
        }
    })

    if (ajax_login_status) {
        $jq('#pp_contact').find('.for_visitor').hide();
    }

    $('.menu_videochat_edge').click(function(){
        videoChatInvite($(this));
        return false;
    })

    $('.menu_audiochat_edge').click(function(){
        audioChatInvite($(this));
        return false;
    })

    $('.menu_favorite_add_edge, .menu_favorite_remove_edge').click(function(){
        actionFavorite($(this));
        return false;
    })

    $('.menu_user_block_edge, .menu_user_unblock_edge').click(function(){
        confirmBlockUser($(this));
        return false;
    })

    $('.menu_friends_add_edge').click(function(){
        addToFriends($(this));
        return false;
    })

    $('.menu_groups_like_edge').click(function(){
        groupSubscribe($(this));
        return false;
    })

    $('.menu_user_report_edge').click(function(){
        userReport($(this))
        return false;
    })

    $('.menu_street_chat_edge').click(function(){
        if($(this).data('href'))return true;
        inviteStreetChat($(this))
        return false;
    })

    $jq('body').on('contextmenu dragstart', '.nocontextmenu', function() {
        return false;
    })

    $jq('body').addClass('page_ready');

    onDragPage();

    $('.history_back').click(function(){
        var $btn=$(this);
        if($btn.find('.icon_fa')[0]){
            addChildrenLoader($btn)
        }
        history.back();
        return false;
    });
})

function createLayerPage(noCheckVisible){
    noCheckVisible=noCheckVisible||false;
    if(!noCheckVisible){
       if(!isMobileSite || notLoaderIos)return false;
    }
    $('<div class="layer_page">').append(createLoader('frame_loader_page'))
    .appendTo($jq('body')).toggleClass('to_show',0)
}

var isDisableSmoothScroll=false;
function smooth_scroll(e) {
    if(isDisableSmoothScroll || isMobileSite || !!navigator.platform.match(/^Mac/i))return;
    if($jq('body').is('.modal-open'))return;
    if (e.ctrlKey) return;
    e.preventDefault();
    var targ=$(e.target), l, t, x, y, to={}, data={}, isB, dur=0, scale=window.devicePixelRatio||1;
    if (!targ.parent()[0].tagName) targ=$('body');
    while (targ[0]!=$('html')[0]) {
        x=/scroll|auto/.test(targ.css('overflow-x'));
        y=/scroll|auto/.test(targ.css('overflow-y'));
        if (x||y||(isB=x=y=targ.is('body:not(.themodal-lock)'))) {
            l=(targ.data('left0')||(isB?$win:targ).scrollLeft());
            t=(targ.data('top0')||(isB?$win:targ).scrollTop());
            x=x&&e.deltaX&&(e.deltaX<0?l>0:(l+(isB?$win.width():targ[0].clientWidth)<targ[0].scrollWidth));
            y=y&&e.deltaY&&(e.deltaY<0?t>0:(t+(isB?$win.height():targ[0].clientHeight)<targ[0].scrollHeight));
            if (x||y) break;
        }
        targ=targ.parent();
    }
    if (targ[0]==$('html')[0]) return;
    if (x) {
        to.scrollLeft=data.left0=l+=Math.round(e.deltaX/scale);
        dur=Math.abs(l-targ.scrollLeft())*2.5
    };
    if (y) {
        to.scrollTop=data.top0=t+=Math.round(e.deltaY/scale);
        dur=Math.max(dur, (Math.abs(t-targ.scrollTop())*2.5))
    };
    if (isB) targ=$('body, html');

    if (dur<200)dur=200;
    targ.data(data).stop()
     .animate(to, Math.min(400, dur), 'easeOutQuad', function(){targ.data({top0: 0, left0: 0})});
}

function showErrorFocus($sel, msg, hide, nofocus){
    if(!$sel.is(':focus'))return;
    showError($sel, msg, hide, nofocus)
}

function showError($sel, msg, hide, nofocus){
    hide=hide||false;
    nofocus=nofocus||false;

    if(!hide&&!nofocus)$sel.focusEl();
    $sel.attr('data-original-title', msg)
        .prop('disabled', false).tooltip({
        template:'<div class="tooltip tooltip_error" role="tooltip">'+
                    '<div class="arrow"></div>'+
                    '<div class="tooltip-inner"></div>'+
                 '</div>',
        trigger:'manual',
        title: msg,
        animation:true
    }).on('hidden.bs.tooltip', function(){
        $sel.removeClass('show_error');
        //console.log('hidden');
        //.tooltip('destroy')
    }).addClass('wrong');

    if (hide) {
        $sel.tooltip('hide');
    }else{
        if($sel.is('.show_error')&&$sel.attr('aria-describedby')){
            var tId=$sel.attr('aria-describedby'),
                msgOld=$('#'+tId).find('.tooltip-inner').text();
            if (msgOld==msg) {
                $('#'+tId).find('.tooltip-inner').text(msg);
            }else{
                $sel.tooltip('show').addClass('show_error');
            }
        } else {
            $sel.tooltip('show').addClass('show_error');
        }
    }

    if ($sel.closest('.bootstrap-select')[0]) {
        $sel.closest('.bootstrap-select').addClass('wrong');
    } else if ($sel[0].id=='blog_subject') {
        $sel.closest('.title').addClass('wrong');
    }
}

function showError11($sel, msg, hide, nofocus){
    hide=hide||false;
    nofocus=nofocus||false;
    if(!hide&&!nofocus)$sel.focusEl();
    if($sel.data('originalTitle'))$sel.attr('data-original-title', msg);
    $sel.prop('disabled', false).attr('title', msg).tooltip({
        template:'<div class="tooltip tooltip_error" role="tooltip">'+
                    '<div class="arrow"></div>'+
                    '<div class="tooltip-inner"></div>'+
                 '</div>',
        trigger:'manual',
        animation:true
    }).on('hidden.bs.tooltip', function(){
        //console.log('hidden');
        //.tooltip('destroy')
    }).addClass('wrong').tooltip(hide?'hide':'show');
    if ($sel.closest('.bootstrap-select')[0]) {
        $sel.closest('.bootstrap-select').addClass('wrong');
    }
}

function focusError($sel){
    if($sel.is('.show_error')&&$sel.attr('aria-describedby'))return;
    if($sel.is('.wrong'))$sel.tooltip('show')
}

function blurError($sel){
    if($sel.is('.wrong'))$sel.tooltip('hide').removeClass('show_error');
}

function hideError(sel){
    var $sel=$(sel);
    $sel.removeClass('wrong').tooltip('hide').removeClass('show_error');
    if ($sel.closest('.bootstrap-select')[0]) {
        $sel.closest('.bootstrap-select').removeClass('wrong');
    } else if ($sel[0].id=='blog_subject') {
        $sel.closest('.title').removeClass('wrong');
    }
}

function scrollToEl(sel, fn){
    sel=sel||'';
    fn=fn||function(){};
    var top=0;
    if(sel)top=$(sel).offset().top;
    var mTop=$jq('body').scrollTop(),
        t=Math.round(Math.sqrt(Math.abs(mTop-top))*25);
    if(top)top=top+mTop;
    if(t<200){t=200} else if(t>800)t=800;
    var d=0;
    if (ajax_login_status) {
        d=$('.navbar').height()+10;
    }
    $jq('body, html').stop().animate({scrollTop:top-d},t,'easeInOutCubic',function(){
        if(this==$jq('body')[0])fn();
    })
}

function onLoadImgFromList($el, url){
    if(!$el[0])return;
    onLoadImgToShow(url,0,function(){
        $el.removeClass('to_hide');
    })
}

function onLoadProfilePhoto(sel){
    $(sel+'.to_hide').removeClass('to_hide');
}

function backStateHistory(){
    try {
        history.back();
        return true;
    } catch(e) {
        return false;
    };
}

function setPushStateHistory(param, value){
    try {
        value=value||1;
        var state=history.state,obj={};
        obj[param]=value;
        if(typeof history.state == 'object'){
            if (history.state !== null && typeof history.state[param] != 'undefined') {
                history.state[param]=value;
            }else{
                state=$.extend(obj, history.state);
            }
        } else {
            state=obj;
        }
        history.pushState(state, document.title);
    } catch(e) { console.log('setPushStateHistory error', e); };
}

function setPosToHistory(url){
    try {
        var data={doc_h:0, x:0, y:0, gallery:0, im:0, upload_file:'',
                  mobile_menu_open:0, mobile_events_list_open:0, mobile_pending_friends_list_open:0, upgrade:0};
        if(url){
            history.replaceState(data, document.title, url)
        } else {
            history.replaceState(data, document.title)
        }

    } catch(e) {};
}

function checkOpenModal(){
    if ($('.modal:visible').length) {
        $jq('body').addClass('modal-open');
    }
}

var confirmCustom = confirmHtml = function(msg, handler, hCancel_or_title, title, btnOk, btnCancel, hideCancel, noCloseOk) {
    var fn=function(){
        btnOk=btnOk||l('alert_html_ok');
        btnCancel=btnCancel||l('cancel');
        var titleDefault=title,
        noCancel=(typeof(hCancel_or_title) != 'function');
        title=(noCancel ? hCancel_or_title : title)||l('are_you_sure');
        var cancelHtml='<button type="button" class="btn btn-secondary">'+
                   '<span><span class="icon remove"></span>'+btnCancel+'</span></button>',
            titleHtml='<div class="modal-header">'+
                    '<h3 class="modal-title">'+title+'</h3>'+
                  '</div>';

        if(hideCancel)cancelHtml='';

        if(titleDefault=='')titleHtml='';
        var backdrop='static';
        //if($('.modal-backdrop.in')[0]){
            //backdrop='false';
        //}

        var $pp=$('<div class="modal fade bs-example-modal-sm custom_modal custom_confirm_modal custom_modal_alert" tabindex="-1" role="dialog" data-backdrop="'+backdrop+'">'+
                '<div class="modal-dialog modal-sm" role="document">'+
                    '<div class="modal-content">'+
                        titleHtml+
                        '<div class="modal-body"><div class="info">'+msg+'</div></div>'+
                        '<div class="modal-footer">'+
                            '<div class="double">'+
                                cancelHtml+
                                '<button type="button" class="btn btn-success">'+
                                '<span><span class="icon check"></span>'+btnOk+'</span></button>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
              '</div>')
        .appendTo('body')
        .on('hidden.bs.modal', function () {
            checkOpenModal();
            $pp.remove();
        }).modal('show');

        var button='.btn-secondary';
        if(!noCloseOk)button +=',.btn-success';
        $(button, $pp).on('click',function(){closeAlert()});

        $('.btn-success', $pp).click(function(){
            handler($(this));
            if(noCloseOk)$('button', $pp).prop('disabled', true)
        });
        if (!noCancel) $('.btn-secondary', $pp).click(hCancel_or_title);
    }

    if($('.custom_modal_alert')[0]){
        $('.custom_modal_alert').eq(0).one('hidden.bs.modal', fn);
        closeAlert();
    }else{
        closeAlert();
        fn();
    }
}

var confirmCustomWithProfile = function(data, msg, handler, hCancel_or_title, title, btnOk, btnCancel, hideCancel, noCloseOk) {
    var photoHtml = '<div class="pic">'+
                        '<a class="photo" href="'+urlMain+data.url+'" target="_blank" title="" style="background-image: url('+urlFiles+data.photo+');"></a>'+
                    '</div>';
    msg=msg.replace(/{user_photo}/,photoHtml).replace(/{user_name}/,data.user_name);
    if(data['group_name']){
        msg=msg.replace(/{group_name}/,data.group_name);
    }
    confirmCustom(msg, handler, hCancel_or_title, title, btnOk, btnCancel, hideCancel, noCloseOk);
}

var showNotifMediaChat = function(type, data, handler, hCancel_or_title, msg, btnOk, hideCancel) {
    if(data['group_name']){
        type += '_' + (data['group_page'] ? 'page' : 'group');
    }
    msg=msg||l(type+'_chat_from_user_edge');
    btnOk=btnOk||l('mediachat_start');
    confirmCustomWithProfile(data, msg, handler, hCancel_or_title, '', btnOk, l('mediachat_decline'), hideCancel);

    msg=l(type+'_chat_from_user_notifications');
    if(data['group_name']){
        msg=msg.replace(/{group_name}/,data.group_name);
    }
    var options={
        tag : type+'_chat_request-'+data.user_id,
        icon: urlFiles+data.photo
    }
    if(type=='street'){
        options['tag'] += '-'+data.data;
    }
    msg=msg.replace(/{user_name}/,data.user_name);
    var data={
        resetHash: true
    }
    notifSend('', msg, options, data);
    return;
}

var alertCustomIcon = function(msg, title, icon)
{
    alertCustom(msg, title, false, false, false, icon);
}

var alertCustom = alertHtml = function(msg, title, hClose, btnOk, classModal, icon)
{
    var fnBackdrop = false;
    if($('.pp_stream_title.in')[0]){
        //fnBackdrop = true;
    }
    var fn=function(){
        if(typeof(hClose)!='function')hClose=closeAlert;
        title=defaultFunctionParamValue(title, l('alert_html_alert'));//l('alert_html_alert') l('alert_success')
        btnOk=btnOk||l('alert_html_ok');
        var backdrop='true';
        if($('.modal-backdrop.in')[0] && !fnBackdrop){
            backdrop='false';
        }
        classModal=classModal||'';
        icon=icon||false;
        var iconHtml = '',
            icons = {
                set_profile_photo: 'svg',
                remove_header: 'svg',
                success: 'svg',
                video_chat: 'svg',
                audio_chat: 'svg'
            }
        if (icon && icons[icon] != undefined) {
            icon='icon_alert_'+icon+'.'+icons[icon];
            iconHtml = '<span class="icon_alert icon_'+icon+'"><img src="'+url_tmpl_images+icon+'"></span>';
        }
        var $pp=$('<div class="modal fade bs-example-modal-sm custom_modal custom_modal_alert '+classModal+'" tabindex="-1" role="dialog" data-backdrop="'+backdrop+'">'+
                '<div class="modal-dialog modal-sm" role="document">'+
                    '<div class="modal-content">'+
                        '<div class="modal-header">'+
                            '<h3 class="modal-title">'+title+'</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+msg+iconHtml+'</div>'+
                        '<div class="modal-footer">'+
                            '<button type="button" class="btn btn-success">'+
                            '<span class="icon check"></span>'+btnOk+'</button>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
              '</div>')
        .appendTo('body')
        .on('hidden.bs.modal', function(){
            checkOpenModal();
            $pp.remove();
        })
        .modal('show');
        $('.btn-success', $pp).click(function(){hClose()});
    }

    if($('.custom_modal_alert')[0]){
        $('.custom_modal_alert').eq(0).one('hidden.bs.modal', fn);
        closeAlert();
    } else if($('.pp_stream_title.in:visible')[0]){
        $('.pp_stream_title').one('hidden.bs.modal', fn).modal('hide');
        closeAlert();
    }else{
        closeAlert();
        fn();
    }
}

function alertServerError(notReload)
{
    var fn;
    if(!(notReload||0)){
        fn=function(){location.reload()};
        $('body').on('click',fn);
    }
    alertCustom(l('server_error_try_again'), l('alert_html_alert'),fn);
}

function alertCustomRedirect(url,msg,title){
    alertCustom(msg,title,function(){
        redirectUrl(url);
    });
    $('body').one('click', '.modal', function(e){
        redirectUrl(url);
    })
}

function closeAlert(e){
    if(e){
        var $targ=$(e.target);

        if ($targ.is('.custom_confirm_modal')||$targ.closest('.custom_confirm_modal')[0]
            ||$targ.is('.pp_file_upload')||$targ.closest('.pp_file_upload')[0]
            ||$targ.is('.pp_info_page')||$targ.closest('.pp_info_page')[0]
            ||$targ.is('.pp_stream_title')||$targ.closest('.pp_stream_title')[0]){
            return false;
        }
    }
    if ((e&&($targ.is('.modal')||$targ.is('.modal-dialog')))||!e){
        if (ajax_login_status && clProfile.checkCloseReport()) {
            return false;
        }

        $('.custom_modal_alert.in').modal('hide');
        $('.custom_modal.in').modal('hide');
        return false;
    }
}

function showBackgroundImagePage(urlImage){
    var ready;
    function bgReady() {
        var $bgPage=$('.main_page_image');
        if(ready){
            if(!$bgPage.is('.show'))$bgPage.addClass('to_show')
        }else if(document.cookie.indexOf('bgEdgeMainPage='+urlImage)>=0)$bgPage.addClass('show');
        document.cookie='bgEdgeMainPage='+urlImage;
        ready=1
    }
    bgReady();
    var img=new Image();
    img.onload = bgReady;
    img.src=urlImage;
}

/* Contact us */
function contactFrmShow(){
    var $content=$jq('.contact_frm','#pp_contact');
    if ($content.is(':empty')) {
        $jq('#pp_contact').find('.contact_frm').append(createLoader('contact_frm_loader')).end()
        .one('shown.bs.modal',function(){
            $.post('contact.php?get_page_ajax=1',{},function(res){
                var data=getDataAjax(res, 'data');
                if (data) {
                    var h=$content.html(data).find('.bl_modal_body').height();
                    setTimeout(function(){
                        if(ajax_login_status){
                            $content.css({height:'auto',overflow:'visible'});
                        } else {
                            $content.oneTransEnd(function(){
                                $content.css({height:'auto',overflow:'visible'});
                            }).height(h);
                        }
                        $content.find('.bl_modal_body').addClass('to_show');
                        var fnSuccess=function(){
                            $jq('#pp_contact').one('hidden.bs.modal', function(){
                                setTimeout(function(){alertCustom(l('message_sent'),l('success'))},0)
                            }).modal('hide')
                        }
                        initContactUs($jq('#pp_contact'),fnSuccess,showError,hideError);
                    },0)
                }else{
                    $jq('#pp_contact').one('hidden.bs.modal', alertServerError).modal('hide');
                }
            })
        }).on('hidden.bs.modal', function(){
            $jq('input, textarea', '#pp_contact').each(function(){
                hideError(this)
            })
            if(isRecaptchaContact){
                hideError('#contact_recaptcha');
                grecaptcha.reset(recaptchaWdContact);
            }
        }).modal('show');
    }else{
        $jq('input, textarea', '#pp_contact').val('');
        $jq('#pp_contact').modal('show');
    }
}

function refreshCaptcha(sel){
    sel=sel||'#pp_contact_captcha';
    $jq(sel)[0].src = urlMain+'_server/securimage/securimage_show_custom.php?sid=' + Math.random();
}
/* Contact us */

function ppTermsShow(type){
    type=type||'term_cond';
    var sel='#pp_'+type,
        $content=$jq('.pp_information',sel);
    if ($content.is(':empty')) {
        $content.addChildrenLoader().closest(sel).one('shown.bs.modal',function(){
            $.post('ajax.php?cmd=get_page_info',{type:type},function(res){
                var data=getDataAjax(res, 'data');
                if (data && data.text) {
                    var html='<div class="modal-body">'+
                                '<div class="scrollbarY">'+
                                    '<div class="scrollbar">'+
                                        '<div class="track">'+
                                            '<div class="thumb"><div class="end"></div></div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="viewport">'+
                                        '<div class="overview">'+data.text+
                                    '</div>'+
                                    '</div>'+
                                '</div>'+
                             '</div>'+
                             '<div class="modal-footer">'+
                                '<div class="col-lg-12 col-md-12">'+
                                    '<button type="button" class="btn btn-success">'+
                                        l('i_understand')+
                                    '</button>'+
                                '</div>'+
                             '</div>';
                    $content.html('<div class="bl_modal_body">'+html+'</div>').find('.bl_modal_body').addClass('to_show');
                    $content.find('.scrollbarY').tinyscrollbar({wheelSpeed:30,thumbSize:30,deltaHeight:12});
                    setTimeout(function(){
                        $content.find('button.btn-success').click(function(){
                            $jq(sel).modal('hide');
                            $jq('.join_agree').prop('checked',true);
                        });
                    },0)
                }else{
                    $jq(sel).modal('hide');
                    alertServerError();
                }
                $content.removeChildrenLoader()
            })
        }).modal('show');
    }else{
        $jq(sel).modal('show');
    }
}


/* Function visitors */

function checkLoginStatus(notCheckGroup){
    if (!ajax_login_status) {
        redirectToLogin();
        return false;
    }
    if(siteGroupId && siteGroupNoAccess && !(notCheckGroup||0)){
        alertCustom(l('please_join_the_group'),l('the_group_is_private'));
        return false;
    }

    return true;
}

function checkBlockedAndStatus($btn){
    if(!checkLoginStatus()) return false;

    if(clProfile.isBlockedProfile()) {
        $btn=$btn||[];
        var t=0;
        if($jq('#bl_user_blocked').is('.to_show')){
            if($btn[0]&&$btn.data('tooltip')){
                $btn.blur();
                t=100;
            }
        }
        setTimeout(function(){
            alertCustom(l('please_unblock_first'),l('user_blocked'));
        },t)
        return false;
    }
    return true;
}

function sendMessages(uid,$btn,noGroup){
    if(!checkBlockedAndStatus($btn))return;
    if (noGroup||false) {
        clMessages.show(uid,$btn||[],0,0);
    } else {
        clMessages.show(uid,$btn||[]);
    }
}

function sendMessagesWall(uid,groupUserId,groupId){
    if(!checkLoginStatus()) return false;
    clMessages.show(uid,[],groupUserId*1,groupId*1);
}

function addToFriends($btn){
    if(!checkBlockedAndStatus($btn))return;
    clFriends.sendRequestFriend($btn);
}

function groupSubscribe($btn){
    if(!checkLoginStatus(true)) return;
    clGroups.sendRequestSubscribe($btn);
}

function checkSupportWebrtc(type){
    var is=supportWebrtc();
    if (is=='ssl') {
        alertCustom(l('your_browser_needs_ssl_certificate_to_run_'+type+'_chat'))
        is=false;
    }else if(is===false){
        alertCustom(l('the_browser_has_no_webrtc_support'))
    }
    return is;
}

function videoChatInvite($btn){
    if(!checkBlockedAndStatus($btn))return;
    clVideoChat.checkInvite($btn);
}

function audioChatInvite($btn){
    if(!checkBlockedAndStatus($btn))return;
    clAudioChat.checkInvite($btn);
}

function inviteStreetChat($btn){
    if(!checkBlockedAndStatus($btn))return;
    clCityStreetChat.invite($btn);
}

function openReport(uid){
    if(!checkLoginStatus())return;
}

function confirmBlockUser($btn){
    if(!checkLoginStatus())return;
    clProfile.confirmBlockUser($btn);
}

function openGallery(e, pid, video, uid, cid, list, groupId, liveUrl, dataCustom){
    if(!checkLoginStatus() || notPageLoad())return;
    cid=cid||false;
    list=list||false;
    groupId=groupId||0;
    liveUrl=liveUrl||'';
    if (liveUrl) {
        redirectUrl(liveUrl);
        return;
    }
    clProfilePhoto.openGallery(e, pid, video, uid, cid, list, groupId, dataCustom);
}

function openGalleryDataId($el, e, video, uid, cid, list){
    if($el.closest('.action_set_default_photo')[0])return false;
    var groupId = $el.data('group-id'); // Retrieve the data-group-id attribute
    
    openGallery(e, $el.data('pid'), video, uid, cid, list, groupId);
}

function openGalleryList(e, pid, video, uid){
    var $layer=$('#list_image_layer_action_'+pid);
    if($layer[0]&&$layer.is('.to_show'))return;
    if(notPageLoad())return;
    openGallery(e, pid, video, uid, false, true);
}

function actionFavorite($btn){
    if(!checkBlockedAndStatus($btn))return;
    clProfile.actionFavorite($btn);
}

function userReport($btn){
    if(!checkLoginStatus()) return false;
    clProfile.openReport($btn.data('uid'),0,$btn);
}


var playListSongs = {};
function setPlayListSong(id, url, title){
    //playListSongs.push.apply(playListSongs, [{id:id, icon:'', title:title, file:url}]);
    playListSongs[id]={id:id, icon:'', title:title, file:url};

    if (!ajax_login_status) {
        return;
    }
    clProfileSongs.changeControlsPlayer();
}

var songPlayer = null;
function playSong(id, el, e){
    if(!checkLoginStatus() || notPageLoad())return;

    var $el=$(el), $layer=$el.find('.layer_action_list');
    if ($layer[0]&&$layer.is('.to_show')) {
        return;
    }

    if ($el.is('.play_pause') && $('#player_song').is('.to_show')) {
        songPlayer.playToggle(true);
        return;
    }
    if (songPlayer !== null) {
        if (id == songPlayer.getCurrentTrack() && songPlayer.getAudio().paused) {
            if(!$('#player_song').hasClass('to_show')) {
                $('#player_song').addClass('to_show');
            }
            songPlayer.playToggle();
            return;
        }
    }

    $('#player_song').addClass('to_show');
    songPlayer = AP.init({
        autoPlay: true,
        notification: false,
        changeDocTitle: false,
        confirmClose: false,
        playSeek: true,
        playList: playListSongs,
        //index: index,
        id: id
    })
}
/* Function visitors */

function addChildrenLoader($btn, notHidden){
    notHidden=defaultFunctionParamValue(notHidden, true);
    var $iconFa = $btn.find('.icon_fa, .icon, .loader_wrap');
    if (notHidden) {
        $iconFa = $iconFa.not(':hidden');
    }
    if($iconFa[0]){
        $iconFa.addChildrenLoader();
    }else{
        $btn.addChildrenLoader();
    }
}

function addLoaderCheckDevice($btn, notHidden){
    if(notLoaderIos)return false;
    addChildrenLoader($btn, notHidden)
}

function removeChildrenLoader($btn){
    var $iconFa = $btn.find('.icon_fa, .icon');//.not(':hidden');
    if($iconFa[0]){
        $iconFa.removeChildrenLoader();
    }else{
        $btn.removeChildrenLoader();
    }
}

var calcStyle='';
var widthScroll=false;
var prevUserAgent=navigator.userAgent;
var isChangeDevice=false;
var lastStyleD=0;
function initCustomStyle(){
    var fn=function(){

        isChangeDevice=prevUserAgent!=navigator.userAgent;
        var $body=$jq('body'),
            $bodyModal=$('body.modal-open'),
            hw=$win[0].innerHeight;

        var hCity=hw-$jq('.navbar-header').height()+1;
        /*var dpr=window.devicePixelRatio*.9;
        if(dpr){
            hCity=hw-(49*dpr);
        }*/

        /* City */
        var stylesAlways  ='.city_body .city_bl{height: '+hCity+'px;}';

        stylesAlways +='.city_body.city_mobile .frame_pp_map{height:'+hCity+'px;}';
        stylesAlways +='.city_body.city_mobile .frame_pp_map .pp_map .pp_3dcity_frm_cont{height:'+hCity+'px;}';
        stylesAlways +='.city_body.city_mobile .frame_pp_map .pp_map .pp_3dcity_frm_cont .map_location{height:'+hCity+'px;}';
        stylesAlways +='.city_body.city_mobile .frame_pp_map .pp_map .pp_3dcity_frm_cont .map_location > div{height:'+hCity+'px!important;}';
        /* City */

        stylesAlways +='.mobile .wrap_icons_info .scrollbarY .viewport{max-height:'+hCity+'px;}';

        if(isChangeDevice){
            setSytemVars();
            if(ajax_login_status){
                if(isMobileSite)clProfilePhoto.resizeImage();
                //if($jq('body').is('.message_open')) {
                    //clMessages.closePopup();
                //}
            }
            $bodyModal.addClass('to_check');
        }

        $body[isMobileSite?'addClass':'removeClass']('mobile');

        var widthScrollbar=function(){
            var a=document.createElement('div');
                a.className="modal-scrollbar-measure",
            $body.append(a);
            var b=a.offsetWidth-a.clientWidth;
            return $body[0].removeChild(a),b
        }
        //var d=Math.abs($body[0].clientWidth - $body[0].offsetWidth);
        var d=widthScrollbar();
        //console.log('Scroll width', 'Bootstrap: ' + widthScrollbar(), 'Site: ' + d);

        if($bodyModal[0]){
            if (!lastStyleD) {
                // $jq('#style_calc')[0].innerHTML=stylesAlways;
                return;
            }
            d=lastStyleD;
        }

        var customStyle=stylesAlways;
        lastStyleD=d;
        if(isChangeDevice){
            $bodyModal.removeClass('to_check');
        }
        prevUserAgent=navigator.userAgent;
        if(widthScroll===false)widthScroll=d;

        if(d){
            customStyle=
                'body.modal-open .bl_grid_inner_photo_center{margin-left: '+d+'px;}'+
                '.header_grid{width: calc(100vw + '+(2*d)+'px); margin-left: -'+d+'px; overflow-x:hidden;}'+
                'body.modal-open #cham-page,'+
                'body.body_member.modal-open #cham-page,'+
                'body.body_member.modal-open #cham-page .header{width: calc(100vw + '+d+'px); margin-left: -'+d+'px;}'+

                'body.body_member.modal-open #cham-page .header .wrap_profile_menu_inner_small .pl_grid_count,'+
                'body.body_member.modal-open #cham-page .page_content{width: calc(100vw - '+d+'px); margin-left: '+d+'px;}'+
                //'body.body_member.modal-open .navbar-fixed-top {padding-right: '+d+'px;}'+
                'body.body_member.modal-open #cham-page .header_grid{padding-left: '+d+'px;}'+
                'body.body_member.modal-open .cover_button_bl{margin-right: '+d+'px;}'
                //'.stickers_bl .stickers_list_wrap .stickers_list_bl{width: calc(100% + '+(d+6)+'px);}'
                //'.chat_wrap{margin-right: '+d+'px;}'+
                //'.chat .sidepanel .contacts {margin: 0 -'+d+'px 0 0;}';
            if($jq('.column')[0]){
                if ($jq('.column').is(':hidden')) {
                customStyle +='body.body_member.modal-open #cham-page .bl_wall{overflow:visible;}'+
                              'body.body_member.modal-open #cham-page .bl_post_wall{width:100vw;}'+
                              'body.body_member.modal-open #cham-page .bl_post_wall .field_comment{width: calc(100% - 50px - '+d+'px);}'+
                              'body.body_member.modal-open #cham-page .bl_grid_inner_photo{right: '+d+'px;}';
                } else {
                    customStyle +='body.body_member.modal-open #cham-page .bl_grid_inner_photo{right: '+d+'px;}';
                }
            } else {
                customStyle +='body.body_member.modal-open #cham-page .bl_wall{overflow:visible;}'+
                              'body.body_member.modal-open #cham-page .bl_grid_inner_photo{right: '+d+'px;}';
            }
        } else {
            customStyle +='body.modal-open, .modal.in{padding-right:0px!important;}';
        }
        customStyle +='.height_no_navbar_cl{height:'+hCity+'px!important;}';
        if (!isMobileSite) {
            customStyle +='ul.menu_vertical_scroll{max-height:'+hCity+'px;}';
        }

        if(calcStyle!==customStyle){
            //console.log('STYLE CALC',customStyle);
            // $jq('#style_calc')[0].innerHTML=customStyle;
        }
        calcStyle=customStyle;
    }
    fn();
    $win.on('resize orientationchange', fn);
}


var user_profile_bg_w, user_profile_bg_h;
function chProfileBg(pic, noSave){
    if (noSave==-1) pic=$.trim(user_profile_bg);
    // prevent transform at every page loading
    //if (pic==bgLast[0]) return;
    if (pic===bgLast[0]&&!user_profile_bg_video[1]){
        $('html').css({background:''});
        return;
    }
    if (noSave==2) user_profile_bg+=' ';

    var url=url_tmpl_main+'images/patterns/'+pic,
        no=/iPhone|iPad|iPod|MSIE [5-9]/i.test(navigator.userAgent)||
            $('<div />').css('transition')===undefined||user_profile_bg_video[1],
        bg=$('.profile_bg:last')
            .css({transition: (no?'':'none'), transform: 'none', willChange: 'opacity, '+(no?'background':'transform')}),
        s=(Math.random()+(noSave?42.5:1.5))/(noSave?43:1), t=Math.random()-.5, x=noSave?50:Math.random()*10+45,
        //y=(noSave?200:(Math.random()*((window.innerHeight||$win.height())-200)))+$win.scrollTop()+100-bg.offset().top;
        y=(noSave?200:(Math.random()*((window.innerHeight||$win.height())-200)))+$win.scrollTop()+100;
    $('.profile_bg:not(:last)').remove();
    function trans(s,t,x,y,o){ return {
        transition: 'all '+1.2+'s, transform-origin 0s',
        transform: 'scale('+s+') rotate('+t*(noSave?.03:2)+'rad) translateZ(0)',
        transformOrigin: x+'% '+y+'px',
        opacity: o
    }};

    if (pic) {
        $(new Image()).load(function(){
            bgLast=[pic, 0, 0]; setPosToHistory();
            $('.profile_custom .cont *').removeClass('loading').removeAttr('disabled').delay(300).removeClass('preloader', 1);
            if (no) return bg.show().css({backgroundImage: 'url('+url+')'});
            bg.addClass('changing').css(trans(s, -t, x, y))
            var bg1=bg.clone().one('transitionend webkittransitionend', function(){
                $('html').css({background:''});
                bg.remove();
                bg1.css({transformOrigin: '', transition: 'none'}).removeClass('changing');
            }).css({background: 'url('+url+') 0 0 repeat'}).css(trans(1/s, t, x, y, 0))
             .insertAfter(bg.eq(-1)).fadeTo(0,1).css({transform: '', willChange: ''});
        }).on('error', function(){user_profile_bg=bgLast[0]}).prop('src', url)
    } else {
        if(noSave==1){
           $('html').css({background:''});
        }
        $('.profile_bg[style]').css(no?{backgroundImage: 'none'}:trans(s,t,x,y,0))
         .one('transitionend webkittransitionend', function(){$(this).removeAttr('style')});
        bgLast=['', 0, 0]; setPosToHistory();//''
    };
    if (!noSave) {
        $.post(url_main+'ajax.php',{cmd: 'set_profile_bg', bg: pic||''}, function(res){
        var data=checkDataAjax(res);
        if (data){
            var lang=$('#column_lang', '.column_narrow');
            if(lang[0]){
                if(data=='none'){$('a.language', lang).addClass('white')
                } else {$('a.language', lang).removeClass('white')}
            }
        }
    });
}
}

function initProfileDeleteFrm(){
    initProfileDelete($('#fields_7'),showError,hideError);
}

function initProfileChangeEmailFrm(){
    initProfileChangeEmail($('#fields_6'),showError,hideError,focusError,blurError);
}

function initProfileChangePasswordFrm(){
    initProfileChangePassword($('#fields_5'),showError,hideError,focusError,blurError);
}

var isCheckMobileSite=null;
function isMobile(){
    if(isCheckMobileSite==null){
        isCheckMobileSite = $doc.is('.mobile') || $jq('body').is('.mobile');//In the class .mobile also connect styles
    }
    return isCheckMobileSite;
}

function cityParentClickTemplate(){
    if($('#navbar_menu_more').closest('.dropdown.open')[0])closeMoreMenu();
    $('.navbar-collapse.in').collapse('hide');
    closeMenuCollapse();
    clFriends.closeList();
    clEvents.closeList();
}

function notPageLoad(){
    return !$jq('body').is('.page_ready');
}

function appPreloaderShow() {
    $('.page_preloader').show();
    $('#cham-page, .navbar').hide();
}

/*
 if(/Android/.test(navigator.appVersion)) {
   window.addEventListener("resize", function() {
     if(document.activeElement.tagName=="INPUT" || document.activeElement.tagName=="TEXTAREA") {
       document.activeElement.scrollIntoView();
     }
  })
}
 */

// bg video
var pageBackgroundVideoPlayer, isBgVideoMute = false,
    bgVideoVolume = 10, bgVideoOnce = false,
    isYError = false, videoPrev = {},
    isVideoBgPageLoads = false, isDestroyPageBackgroundVideoPlayer = false,
    isBackgroundVideoMuteFixUsed = false;

function pageBackgroundVideo() {

    var video = user_profile_bg_video;

    pageBackgroundVideoShow();

    if (pageBackgroundVideoPlayer) {
        $('#bg_video').fadeOut(0, function () {
            pageBackgroundVideoPlayer.stopVideo();
            if (video[1]) {
                pageBackgroundVideoPlayer.loadVideoById(video[0]);
                pageBackgroundVideoPlayer.setPlaybackQuality(profile_bg_video_quality);
            }
        })
    } else if (video[1]) {
        pageBackgroundVideoPlayer = $.getScript('https://www.youtube.com/iframe_api')
    }
}

function onYouTubeIframeAPIReady() {
    var video = user_profile_bg_video;
    // sometimes player is shown in small size, need resize it before show on screen
    videoPlayerOnPageResize();
    $('#bg_video').show()[0];
    pageBackgroundVideoPlayer = new YT.Player('bg_video', {
        videoId: video[0],
        playerVars: {
            showinfo: 0,
            autoplay: 1,
            controls: 0,
            modestbranding: 1,
            rel: 0,
            iv_load_policy: 3,
            theme: 'light',
            wmode: 'opaque'
        }, events: {
            onReady: function (e) {
                pageBackgroundVideoPlayer.setPlaybackQuality(profile_bg_video_quality);
                if (isBgVideoMute && bgVideoVolume) {
                    pageBackgroundVideoPlayer.setVolume(bgVideoVolume)
                } else {
                    pageBackgroundVideoPlayer.mute()
                }
            },
            onStateChange: function (e) {
                if (e.data * 1 === 0 && bgVideoOnce) {
                    console.log('hide after play');
                    $('#bg_video').hide(1, function () {
                        pageBackgroundVideoPlayer.destroy()
                    })
                    return;
                }
                if (isBgVideoMute && bgVideoVolume) {
                    pageBackgroundVideoPlayer.setVolume(bgVideoVolume)
                } else if (!pageBackgroundVideoPlayer.isMuted()) {
                    pageBackgroundVideoPlayer.mute()
                }
                //e.data = -1 / Error
                if (e.data * 1 === 1) {
                    isVideoBgPageLoads = false;
                    $('#bg_video').stop().fadeTo(1, 1);

                    videoPrev = $.extend({}, user_profile_bg_video);
                }
                if (e.data * 1 === 0 && !bgVideoOnce) {
                    pageBackgroundVideoPlayer.playVideo();
                }
                if (e.data == -1) {
                    console.log('onYouTubeIframeAPIReady onStateChange error - try to mute video for play in Chrome');
                    if(!isBackgroundVideoMuteFixUsed) {
                        setTimeout(function(){
                            pageBackgroundVideoPlayer.mute()
                            pageBackgroundVideoPlayer.playVideo();
                            isBackgroundVideoMuteFixUsed = true;
                        }, 100);
                    } else {
                        destroyPageBackgroundVideoPlayer();
                    }
                }
            },
            onError: function (e) {
                pageBackgroundVideoPlayer.mute();
                isYError = true;

                if (videoPrev[1]) {
                    user_profile_bg_video = videoPrev;
                    pageBackgroundVideo();
                } else {
                    destroyPageBackgroundVideoPlayer();
                }
                /*}else if (!Profile.isMyProfile()) {
                    destroyPageBackgroundVideoPlayer();
                } else {
                    $('.bg_video.tumb:visible').fadeOut(400);
                    pageBackgroundVideoPlayer.stopVideo();
                }*/
            }
        }
    });
}


function destroyPlayerForVisitor() {
    if(isDestroyPlayerVisitor)return;
    isDestroyPlayerVisitor=true;
    user_profile_bg_video={};
    $('.bg_video.tumb:visible').fadeTo(600,0,function(){
        chProfileBg('',-1);
    });
    $('#bg_video').hide(1,function(){
        profileBgPlayer.destroy()
    })
}

function destroyPageBackgroundVideoPlayer() {
    if (isDestroyPageBackgroundVideoPlayer)
        return;
    isDestroyPageBackgroundVideoPlayer = true;
    user_profile_bg_video = {};
    //$('.bg_video.tumb:visible').fadeTo(600, 0, function () {

    //});
    $('#bg_video').hide(1, function () {
        pageBackgroundVideoPlayer.destroy()
    })
}

function chProfileBgVideo(save){
    var video=user_profile_bg_video,is=false;
    if(save!==undefined) {
        if(save)video[0]=save;
        video[1]=save?1:'';
        if(save=='')videoPrev={};
        updateVideo=video;
        if(video[1]&&!isMobileDevice){is=true
        }else{profileVideoUpdate(isMobileDevice)}
    }else{
        chVideoPreviev();
    }

    if(!is_bg_video_all_page||isMobileDevice)return;
    if(profileBgPlayer) {
        $('#bg_video').fadeOut(0,function(){
            profileBgPlayer.stopVideo();
            if (video[1]) {
                isUpdateVideo=is;
                profileBgPlayer.loadVideoById(video[0]);
                profileBgPlayer.setPlaybackQuality(profile_bg_video_quality);
            }
        })
    } else if(video[1]) {
        isUpdateVideo=is;
        profileBgPlayer=$.getScript('https://www.youtube.com/iframe_api')
    }
}


function chVideoPreviev(isUpdate) {
    //console.log('tmb', user_profile_bg_video);
    var video=user_profile_bg_video, path='https://i.ytimg.com/vi/'+video[0],
        t=new Date*1, ratio=video.ratio;
    isUpdate=isUpdate||0;
    //chProfileBg('', video[1]?2:-1);
    function insRes(url){
        chProfileBg('', video[1]?2:-1);
        var prev=$('.bg_video.tumb'), cur=$(prev[0]);
        if (url) {
            cur.clone().css({backgroundImage:'url('+url+')', opacity:0})
            .insertAfter(prev.last()).stop().fadeTo(1,1).oneTransEnd(function(){//durVideoPreviev
                if (isUpdate){
                    $('#bg_video').stop().fadeTo(600, 1);
                    hideWinAddVideo(1);
                }
                prev.remove();
                $('html').css({background:''})
            }).add('#bg_video').css({width: 100*ratio+'vh', height: 100/ratio+'vw'});
            durVideoPreviev=0;
        } else {
            prev.fadeTo(0,0)
        }
        //console.log('tmb - insRes', url);
        bgLast=[url, cur.width(), ''];
        name=bgLast.join(',')
    };
    if (video[1]&&(!bgLast[0]||bgLast[0].search(video[0]<0))) $('<img />').load(function(){
        var img=this;
        if(img.width>400||img.src==path+'/hqdefault.jpg'){
            insRes(img.src);
            return;
        }
        /*if (this.width<400) return;
        var img=this;
        insRes(img.src)
        console.log('insRes');
        if (img.src==path+'/maxresdefault.jpg') return;*/
        setTimeout(function(){img.src=path+'/hqdefault.jpg'}, Math.max(1, 100-new Date*1+t));
    }).error(function(){})[0].src=path+'/maxresdefault.jpg';
    else insRes(video[1]?bgLast[0]:0);
}
function pageBackgroundVideoShow() {

    // sometimes player is shown in small size, need resize it before show on screen
    videoPlayerOnPageResize();

    var video = user_profile_bg_video, path = 'https://i.ytimg.com/vi/' + video[0];
    var timestampAtFunctionStart = new Date * 1;

    function insRes(url) {

        var prev = $('.bg_video.tumb'), cur = $(prev[0]);
        if (url) {

            videoPlayerOnPageResize();

            //timerEnd = new Date() * 1;
            //console.log('timer', timerEnd - timerStart);

            cur.clone().addClass('cloned').css({backgroundImage: 'url(' + url + ')'})
                .insertAfter(prev.last()).stop().fadeTo(1, 1).oneTransEnd(function () {
                prev.remove();
                //$('html').css({background:''});
            }).add('#bg_video');

        } else {
            prev.fadeTo(0, 0);
        }
        bgLast = [url, cur.width(), ''];
        name = bgLast.join(',');
    }

    if (video[1] && (!bgLast[0] || bgLast[0].search(video[0] < 0))) {
        $('<img />').load(function () {
            var img = this;

            if (img.width > 400 || img.src == path + '/hqdefault.jpg') {

                insRes(img.src);
                return;
            }
            setTimeout(function () {
                img.src = path + '/hqdefault.jpg'
            }, Math.max(1, 100 - (new Date * 1) + timestampAtFunctionStart));
        }).error(function () { console.log('load image error'); })[0].src = path + '/maxresdefault.jpg';
    } else {
        insRes(video[1] ? bgLast[0] : 0);
    }
}

function videoPlayerOnPageResize()
{
    //console.log('videoPlayerOnPageResize start');
    var video = user_profile_bg_video;

    var videoItemArea = $('.cham-cover.wrap_cham-cover-text');

    if($('.main_page_image').length) {
        videoItemArea = $('.main_page_image');
        $('.main_page_image').css({'opacity': 1});
    }

    var videoSize = centerItemInAreaByHeightWithCrop(video['width'], video['height'], videoItemArea.width(), videoItemArea.height());

    $('#bg_video, #bg_video_tumb').css({width: videoSize['width'], height: videoSize['height'], left: videoSize['horizontalGap']});
    //console.log('videoPlayerOnPageResize end');
}

$win.on('resize', function () {
    if (typeof user_profile_bg_video!='object' || !user_profile_bg_video[1]) {
        return;
    }
    videoPlayerOnPageResize();
}).on('beforeunload', function () {
    if (pageBackgroundVideoPlayer) {
        pageBackgroundVideoPlayer.mute();
    }
    $('#bg_video').stop().fadeOut(500);
});

function pageBackgroundVideoInit() {

    if(profile_bg_video_play_disabled) {
        pageBackgroundVideoShow()
        return;
    }

    if (user_profile_bg_video[1]) {
        isVideoBgPageLoads = true;
        pageBackgroundVideo();
    }
}

function hideRightColumnWall(){
    if(/WebKit||Chrome/i.test(navigator.userAgent)){
        $jq('#bl_page_columns').addClass('hide_column_right')
    }
}

function showRightColumnWall(){
    $jq('#bl_page_columns').removeClass('hide_column_right')
}

function notHashMedia(type){
    type=type||false;//video/audio/street
    var hash=location.hash,
        noHash=!/video_chat_request|audio_chat_request|street_chat_request/.test(hash);
    if(type){
        var re=type+'_chat_request',
            patt = new RegExp(re);
        noHash=!patt.test(hash);
    }
    return !ajax_login_status || !hash || noHash;
}

function resetHashMedia(type){
    if(notHashMedia(type))return;
    //location.hash='';
    var path=location.href.split('#'),
        pageUrl=path[0];
    setPosToHistory(pageUrl);
}

function checkMediaChatRequest(){
    if(notHashMedia())return;

    var hash=location.hash,
        param=hash.split('-'),
        uid=param[1]*1,
        type=param[0].replace('#', '').replace('_chat_request', ''),
        data='';
    if(!uid||!type)return;

    resetHashMedia();
    if(type=='street'){
        data=param[2];
    }
    $.post(url_server+'?cmd=chat_request_check',{type:type,uid:uid,data:data},function(res){
        var data=checkDataAjax(res);
        if(data!==false){
            if(type=='video'){
                clVideoChat.request(data);
            }else if(type=='audio'){
                clAudioChat.request(data);
            }else if(type=='street'){
                console.log(data);
                clCityStreetChat.request(data);
            }
        }
    })
}

function goLinkProfile($el, params, url){
    if($el.is('.go_to_profile'))return;
    $el.addClass('go_to_profile');
    var $l=$el.closest('.users_list_item').find('.layer_blocked_item_list');
    if(!notLoaderIos){
        $l.addClass('to_show');
    }
    addLoaderCheckDevice($l);
    url=url||$el[0].href;
    goLink(url,params);
}

var movingWrap=0;
function onDragPage(){
    if(!isIframeDemo)return;
    onDragPageEl();

    $('.menu_more_menu_navbar_site *').on('click mouseup', function(e){
        if(movingWrap){
            if (movingWrap) setTimeout(function(){
                $jq('.menu_vertical_scroll').removeClass('moving hand');
                movingWrap=0
            }, 1)
            e.stopPropagation();
            return false;
        }
    })
}

function onDragPageEl(){
    if(!('ontouchmove' in document)){
        var swX, swY, swX0, swY0;
        var $el=$('body'),$elS=$el;

        $win.on('mousemove',function(e){
            var dx=e.pageX-swX, dy=e.pageY-swY, sT=swY0-dy;
            if (movingWrap && !$elS.is('.moving') && Math.abs(dx)>Math.abs(dy)) {
                movingWrap=0;
                $elS.removeClass('hand');
            }
            if (movingWrap && sT != movingWrap.scrollTop()) {
                $elS.addClass('moving');
                movingWrap.stop().animate({scrollTop: sT}, 100)
            }
        }).on('mousedown',function(e){
            var targ=$(e.target);
            if (targ.is('select, option, .layer_page')) return;
            if (targ.is('.pp_gallery_overflow') || targ.closest('.pp_gallery_overflow')[0]){
                $elS=targ.is('.pp_gallery_overflow') ? $(this) : targ.closest('.pp_gallery_overflow');
            } else if (targ.is('.menu_vertical_scroll') || targ.closest('.menu_vertical_scroll')[0]){
                $elS=targ.is('.menu_vertical_scroll') ? $(this) : targ.closest('.menu_vertical_scroll');
            } else {
                $elS=$('body');
            }
            movingWrap=$elS.addClass('hand');
            targ.add(targ.parents('a')).one('dragstart', function(){return !movingWrap});
            swX0=movingWrap.scrollLeft();
            swY0=movingWrap.scrollTop();
            swX=e.pageX;
            swY=e.pageY;
        }).on('mouseup',function(){
            if (movingWrap) setTimeout(function(){
                //try {document.getSelection().collapseToStart()} catch(e) {}
                $elS.removeClass('moving hand');
                movingWrap=0
            }, 1)
        }).on('click', '.moving a', function(e){e.preventDefault()})
    }
}


function redirectUrlLoader($el, url){
    if(!url)return;
    if(notLoaderIos){
        redirectUrl(url);
        return false;
    }
    addLoaderCheckDevice($el);
    redirectUrl(url);
}

function redirectFormUrlLoader($el, url, param){
    if(!url)return;
    if(notLoaderIos){
        goLink(url,param);
        return false;
    }
    addLoaderCheckDevice($el);
    goLink(url,param);
}

function goLinkGroup($el, url){
    var $p=$el.closest('.users_group_item');
    if(!$p[0] || $p.is('.go_to_page'))return;
    $p.addClass('go_to_page');
    var $l=$p.find('.layer_blocked_item_list');
    if(!notLoaderIos){
        $l.addClass('to_show');
    }
    addLoaderCheckDevice($l);
    redirectUrl(url);
}

function goLinkBlog(e, $el, url){
    var $p=$el.closest('.cham-post'),
        $targ=$(e.target);
    if(!$p[0] || $p.is('.go_to_page') || $targ.is('.name') || $targ.closest('.name')[0])return;

    goLinkUserBlogs($el, url);
}

function goLinkUserBlogs($el, url, sel){
    var sel=sel||'.cham-post',
        $p=$el.closest(sel);
    if(!$p[0] || $p.is('.go_to_page'))return;

    $p.addClass('go_to_page');
    var $l=$p.find('.layer_blocked_item_list');
    if(!notLoaderIos){
        $l.addClass('to_show');
    }
    addLoaderCheckDevice($p.find('.pic'));

    redirectUrl(url);
}

function goToLive(lid, url){
    var $layer=$('#list_image_layer_live_action_'+lid);
    if($layer[0]&&$layer.is('.to_show'))return;
    $layer.addClass('to_show').addChildrenLoader();

    if(!checkLoginStatus())return;
    redirectUrl(url);
}

function redirectWithLoader($el, url){
    $el.addChildrenLoader();
    redirectUrl(url);
}

function initLocaleCalendar(){
    if (!ajax_login_status)return;
    initLocale();
}


function cityIframeClickTemplate(){

}


function initNavbarMenu(){
    if (!ajax_login_status)return;

    hNavbar=$jq('.navbar-header').height();
    var $navMenu=$jq('#member_header_menu'),
        $navMenuMobile=$jq('#btn_header_menu_nav');
    if($navMenu[0]){
        isVisiblenavMenuMobile=$navMenuMobile.is(':visible');
        $navMenuMobile.click(function(){
            if(isVisiblenavMenuMobile){
                if (!$navMenuMobile.is('.history_open')) {
                    $navMenuMobile.addClass('history_open');
                    setPushStateHistory('mobile_menu_open');
                }
            }
        })

        $navMenu.on('shown.bs.collapse',function(){
                if(hNavbarMenu!==false)return;
                setTimeout(function(){
                    if(hNavbarMenu!==false)return;
                    hNavbarMenu=$navMenu.height();
                    var h=$win.height();
                    h=h-hNavbar-hNavbarMenu+1;
                    $('#style_header_menu')[0].innerHTML='.navbar-header .dropdown.open .menu_more_menu_navbar_site{height:'+h+'px; max-height:'+h+'px;}';
                },1)
        }).on('hide.bs.collapse',function(){
                if ($navMenuMobile.is('.history_open')&&!$navMenuMobile.is('.history_close_back')
                    &&!$jq('body').is('.pp_boost_ajax_open')){
                    backStateHistory();
                }
                $navMenuMobile.removeClass('history_open history_close_back');
        })
        $win.on('resize orientationchange', function(){
            isVisiblenavMenuMobile=$navMenuMobile.is(':visible');
            hNavbarMenu=false;
            $('#style_header_menu')[0].innerHTML='';
            $('.dropdown.open',$navMenu)[0]&&closeMoreMenu();
            $navMenu.is('.collapse.in')&&$navMenu.collapse('hide');
        })
    }
}

function disableLink(){
    $('.link_disabled_js').click(function(){
        return false;
    })
}

function onLoadImgTimeLine(id){
    var $el=$('.'+id+':not(.to_show)');
    if(!$el[0])return;
    var $wallPostBl=$el.closest('.wall_image_post ');
    if($wallPostBl[0]){
        var d=$wallPostBl.width()-$el.width();
        if (d && d<41) {
            $el.addClass('outside_img_stretch');
        }
    }
    onLoadImgToShow($el[0].href,$el)
}

function openPopupUpdate(selD,notOpen){
    notOpen=notOpen||0;
    var sel='#'+selD;
    if (!openPopupList[sel]){
        openPopupList[sel]={};
        var $pp=$(sel);
        openPopupList[sel]['html']=$pp.html();
        openPopupList[sel]['class']=$pp.attr('class');
        openPopupList[sel]['close']=0;
        openPopupList[sel]['el']=$pp.modal('hide');
    }
    openPopupList[sel]['el'].find('.cont').removeClass('to_hide');
    if(!notOpen){
        setPushStateHistory(selD);
        openPopupList[sel]['el'].one('show.bs.modal',function(){
            $jq('body').addClass(selD+'_open');
        })
        .one('hide.bs.modal',function(){
            $jq('body').removeClass(selD+'_open');
        })
        .one('hidden.bs.modal',function(){
            checkOpenModal();
        }).modal('show');
    }
    return openPopupList[sel]['el'];
}


function getLoaderCl(ind,cl,sc,sp){
    var ind=ind||+new Date,
        cl=cl||'loader_btn',
        sc=sc||false,
        sp=sp||1,
        cln=$('#loader_spinner').clone();
    $('#'+ind).remove();
    return cln.attr('id',ind).addClass(cl).find('.spinner')
            .removeClass('spinnerw').addClass(!sc?'spinnerw':'').end().stop().fadeIn(sp);
}

function getLoader(cl,isHide,isWhite,notCache){
    cl=cl||'';
    isHide&&(cl=cl+' hidden');
    var key='loader_'+cl,
        clSpin=isWhite?'spinnerw':'',
        $loader=$('<div class="css_loader '+cl+'">'+
                '<div class="spinner center '+clSpin+'">'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '</div>'+
            '</div>');
    return $loader;
}

function searchInterests(e){
    var interestId=$(e).attr('data-interest-id');
    window.location.href='search_results.php?set_filter_interest=1&interest='+interestId;
}


function closePopupUpdate(nameD,update,call){
    var name='#'+nameD;
    if (openPopupList[name]&&openPopupList[name]['el'].is(':visible')){
        update=update||0;
        openPopupList[name]['close']=1;
        var $pp=openPopupList[name]['el']
            .modal('hide');
        if(!update){
            $pp.one('hidden.bs.modal',function(){
                openPopupList[name]['close']=0;
                $pp.html(openPopupList[name]['html']);
                $pp.removeAttr('class style').addClass(openPopupList[name]['class']+' pp_cont');
                if(typeof call == 'function')call();
            })
        }
    }
}

function redirectLikePage(url,e){
    var $el=$(e.target);
    if (!$el.is('.glyphicon')) {
        $el=$el.closest('.comment_item').find('.glyphicon');
    }
    $el.data({noFadeIn:true, clLoader:'icon_wall_loader'}).addChildrenLoader();
    redirectUrl(url);
}

function wallUpdater(){
    if(typeof clWall == 'object' && typeof clWall.updater == 'function'){
        clWall.updater();
    }
}

function hideWinAddVideo(d){
    var pp_add_video=$('#pp_add_video:visible');
    if (pp_add_video[0]) {
        var d=d||3500;
        pp_add_video.delay(d).fadeOut(400,function(){
            $('#pp_add_video_loader').hide();
            $('#pp_add_video_bg').show();
        });
        customHideTip(bgVideoCode, '#pp_add_video_bg')
    }
}

function customHideTip(el,btn){
    var tip=$(el).data('customTip');
    if(!tip) return;
    $(el).removeClass('wrong');
    $(btn).prop('disabled', false);
    tip.fadeTo(0,0).oneTransEnd(function(){tip.remove()});
}
