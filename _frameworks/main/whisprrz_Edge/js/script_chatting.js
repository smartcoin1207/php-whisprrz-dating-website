var cacheJq={}, openPopupList={};
var isLastLoadBaseListItem=false, isLoadBaseListUsers=false, lastIdBaseListUsers=0, lastIdBaseList=0;
var $win=$(window), $doc=$(document), curHiState=history.state||{};
var isChrome=/Chrome/i.test(navigator.userAgent);
var durClosePp=350,durRemoveListItem=700;

$(function () {
    $('.column_narrow_invite').click(function(){
        var url = location.href.split('/')
        url.pop();
        url = url.join('/');
        FB.ui({
            method: 'send',
            link: url,
			display: 'popup'
        });
        return false;
    })
    /*var $colFix=$jq('.col_fix'),classPrev='',classCur;
    function colFixScroll() {
		classCur=$jq('.main')[0].scrollTop>90;
		if(classCur===classPrev)return;
		classPrev=classCur;
		$colFix[classCur?'addClass':'removeClass']('stick_top');
    }
    $win.on('resize', colFixScroll).resize();
	$jq('.main').on('scroll',colFixScroll);*/

    $win.on('resize',colFixScroll).resize();
    $jq('.main').on('scroll',colFixScroll);
    setPosToHistory();
})

var isPrepareBannerL = false, isPrepareBannerR = false;
function prepareBannerColumns(){
    prepareBannerLColumn();
    prepareBannerLastColumn('r');
    $('.column_main .col').addClass('to_show')
}

function prepareBannerLastColumn(pos){
    pos=pos||'r';
    var $blBanner=$('.bl_banner_'+pos),$colR=$('#colfix_'+pos),$blAds=[];
    if($blBanner[0]){
        $blAds=$blBanner.find('.bl_ads');
    }
    if(!$blBanner[0] || ($blBanner[0] && (!$blAds[0]||$blAds.is(':hidden')))){
        var h,$resEl=$blBanner[0]?$blBanner:$jq('#bl_banner_'+pos+'_empty');
        $resEl.css({transition:''});
        if($blBanner[0]){
            $blBanner.find('.link').removeClass('absolute');
        }
        var hBd=$resEl.height(),
            hB=$resEl.css('height','auto').height(),
            colH=$colR.height();
        if(hBd>hB&&(isPrepareBannerL||isPrepareBannerR)){
            $resEl.height(hBd);
        }else{
            $resEl.height(hB);
        }

        if($blAds[0]){
            h=$win.height()-colH-hB+35;
        }else{
            h=$win.height()-91-colH-hB;
        }
        if (pos == 'l') {
            var d=$('.column_lang_bl')[0]?37:20;
            h -=d;
            if(h<0)h=65;
        }

        if(h>0 && h!=hB){
            //console.log('Prepare Banner:', pos, h);
            if((isPrepareBannerL&&pos=='l')||(isPrepareBannerR&&pos=='r')){
                $resEl.oneTransEnd(function(){
                    if(isPrepareBannerL)isPrepareBannerL=false;
                    if(isPrepareBannerR)isPrepareBannerR=false;
                }).css({transition:'height .35s', height:h});
            }else{
                $resEl.css({transition:'', height:h});
            }
            if($blAds[0]&&h>65){
                $blBanner.find('.link').addClass('absolute');
            }
        }
    }
}

function prepareBannerLColumn(){
    var $blBanner=$('.bl_banner_l');
    if(!$blBanner[0] || $blBanner.next().is('.bl_banner_empty')) {
        prepareBannerLastColumn('l');
    }
    return;

    var hD=hD||0;
    var $blBanner=$('.bl_banner_l'),$colR=$('#colfix_l'),$blAds=[];
    if($blBanner[0]){
        $blAds=$blBanner.find('.bl_ads');
    }
    if(!$blBanner[0] || ($blBanner[0] && (!$blAds[0]||$blAds.is(':hidden')))){
        var next=false;
        if($blBanner[0]){
            next=$blBanner.next().is('#bl_banner_l_empty');
        }
        var h,hB=0,colH,$resEl=$jq('#bl_banner_l_empty');
        if($blBanner[0]){
            $blBanner.find('.link').removeClass('absolute');
            if(next){
                $resEl=$blBanner;
            }else{
                var hE=$resEl.height();
                $resEl.css('height','auto');
            }
            hB=$blBanner.css('height','auto').height();
            colH=$colR.height();
            hD>0&&$blBanner.height(hD);
            if(!next){
                $resEl.height(hE);
            }
        }else{
            hB=$resEl.css('height','auto').height();
            colH=$colR.height();
            $resEl.height(hB);
        }
        if($blAds[0]&&next){
            var d=$('.column_lang_bl')[0]?29:0;
            h=$win.height()-colH-hB-d;
        }else{
            var d=$('.column_lang_bl')[0]?68:0;
            h=$win.height()-91-colH-d;
        }
        if(h<0&&$blAds[0]){
            h=d;
        }
        if(h>=0&&(!next || (next && h!=hB))){//&&h>hB
            if(hD){
                isPrepareBanner=false;
                $jq('.main').animate({scrollTop:0}, 300, 'easeOutQuad');
                $resEl.oneTransEnd(function(){
                    isPrepareBanner=true;
                }).css({transition:'height .35s', height:h});
            }else{
                $resEl.css({transition:'', height:(h)});
            }
            if($blAds[0]&&next){
                $blBanner.find('.link').addClass('absolute');
            }
        }
    }
}

function colFixScroll() {
    if(!ajax_login_status)return;
    $jq('#colfix_l')[0]&&prepareColFix('l');
    $jq('#colfix_r')[0]&&prepareColFix('r');
    prepareBannerColumns();
}

var colD = {};
function prepareColFix(id) {
    var clT='stick_top_'+id,clB='stick_bottom';
    id='colfix_'+id;
    var top=$jq('.main')[0].scrollTop,
        d=91;
    /*if(!colD[id]){
        colD[id]=$jq('#'+id).prev('.logo')[0].offsetHeight+$jq('.header')[0].offsetHeight;
    }else{
        d=colD[id];
    }*/
    var topD=top-d;

    var wh=$jq('.main')[0].clientHeight,sh=$jq('.main')[0].scrollHeight,
        $col=$jq('#'+id),$colB=$jq('#'+id+'_bl'),
        colH=$col[0].offsetHeight,
        trans=isChrome?'none':'all .25s';
    //if(id='colfix_l')colH -=42;
    //console.log(id,colH,wh,topD);
    //if(id='colfix_l'){
        //console.log(id,colH<wh, (wh+topD)>=colH, colH,wh,topD);
    //}
    //return;
    if (colH<wh) {
        if(top>=d){
            if($col.is('.'+clT))return;
            $colB.removeClass('move');
            $colB.oneTransEnd(function(){
                $col.addClass(clT);
                //$colB.removeAttr('style');
            }).css({top:topD,transition:trans});
            if(isChrome){
                $col.addClass(clT);
            }
        }else{
            if($colB.is('.move'))return;
            $col.removeClass(clT);
            $colB.addClass('move').css({top:0,transition:'none'});
        }
        $col.removeClass(clB);
    }else{
        if ((wh+topD)>colH) {
            var d=$('.footer').height(),$bannerFooter=$('.banner_footer_bl');
            if($bannerFooter[0])d +=$bannerFooter.outerHeight()-9;
            if($col.is('.'+clB))return;
            $colB.removeClass('move');
            $colB.oneTransEnd(function(){
                $col.addClass(clB);
                //$colB.removeAttr('style');
            }).css({bottom:(sh-top-wh-d),top:'auto',transition:trans});
            if(isChrome){
                $col.addClass(clB);
            }
        }else{
            if($colB.is('.move'))return;
            $col.removeClass(clB);
            $colB.addClass('move').css({top:0,bottom:'auto',transition:'none'});
        }
        $col.removeClass(clT);
    }
}

function setPosToHistory(){
	var main=$jq('.main')[0], x=$win.scrollLeft(), y=$win.scrollTop();
    var h=main?main.scrollHeight:0;
	try {history.replaceState({doc_h:h, x:x, y:y}, document.title)} catch(e) {};
    name=user_profile_bg;
}

/* Popup ALERT */
var confirmHtmlClose = closeAlert = alertHtmlClose = function(e) {
    /*if(e&&$(e.target).is('.alert_wrapper')&&$('.pp_confirm:visible').last()[0]){
        return false
    }*/
	if ((e&&e.target==this)||!e ){
        var el=$('.pp_alert:visible').last();
        el.close('', 0, !el.data('no_remove'));
        return false
    }
}

var confirmCustom = confirmHtml = function(msg, handler, hCancel_or_title, title, btnOk, icon) {
	closeAlert();
    btnOk=btnOk||ALERT_HTML_OK;
    var noCancel=(typeof(hCancel_or_title) != 'function'),
		title=(noCancel ? hCancel_or_title : title)||ALERT_HTML_PLEASE_CONFIRM;
    if(icon=='video'||icon=='audio'){
        icon='icon_pp_'+icon+'_chat.png';
    }
    icon=icon||'icon_pp_sure.png';
    var $pp=$('<div class="pp_info_start pp_alert pp_confirm">'+
                '<div class="cont">'+
                    '<div class="title">'+title+'</div>'+
                    '<div class="img_chat">'+
                        '<div class="icon"><img src="'+url_tmpl_images+icon+'" height="48" alt="" /></div>'+
                        '<div class="msg">'+msg+'</div>'+
                    '</div>'+
                    '<div class="double_btn">'+
                        '<button class="confirm_close btn small dgrey">'+ALERT_HTML_CANCEL+'</button>'+
                        '<button class="confirm_ok btn small marsh">'+btnOk+'</button>'+
                        '<div class="cl"></div>'+
                    '</div>'+
                '</div>'+
            '</div>').modalPopup({wrClass: 'alert_wrapper',wrCss:{zIndex:1001}}).open()
	$('button', $pp).click(closeAlert)
	$('.confirm_ok', $pp).click(handler);
	if (!noCancel) $('.confirm_close', $pp).click(hCancel_or_title)
}

var alertCustom = alertHtml = function(msg, shadow, title, hClose, icon, btnOk, css)
{
    if(typeof(hClose)!='function'){
        hClose=closeAlert;
    }
    title=defaultFunctionParamValue(title, ALERT_HTML_ERROR);
    closeAlert();
    if(icon=='video'||icon=='audio'){
        icon='icon_pp_'+icon+'_chat.png';
    }else if (icon=='street_chat') {
        icon='icon_pp_street_chat.svg';
    } else {
        icon='icon_pp_voskl.png';
    }

    btnOk=btnOk||ALERT_HTML_OK;
    if(msg){
        msg='<div class="msg td">'+msg+'</div>';
    }else{
        msg='';
    }
    var $pp=$('<div class="pp_info_start pp_alert">'+
        '<div class="cont">'+
            '<div class="title">'+title+'</div>'+
            '<div class="img_chat">'+
                '<div class="icon td"><img src="'+url_tmpl_images+icon+'" alt="" /></div>'+
                msg+
            '</div>'+
            '<div class="one_btn">'+
                '<button class="confirm_ok btn small marsh">'+btnOk+'</button>'+
                '<div class="cl"></div>'+
            '</div>'+
        '</div>'+
    '</div>').modalPopup({wrClass:'alert_wrapper',
                          shClass:(shadow?'':'page_shadow_empty'),
                          wrCss:{zIndex:1001},
                          css:css}).open().find('.confirm_ok').click(hClose).focus();
}

function alertCustomRedirect(r,m,t,d){
    d=d||100;
    m=m||THERE_IS_NO_ONE_HERE_YET;
    t=t||ALERT_HTML_ALERT;
    setTimeout(function(){
        alertCustom(m,true,t);
        $('.confirm_ok').on('click',function(){
            redirectUrl(url_main+r)
        });
        $('.alert_wrapper').on('click',function(){return false});
    },d);
}

function confirmCustomRedirect(r,m,t,d){
    d=d||100;
    m=m||THERE_IS_NO_ONE_HERE_YET;
    t=t||ALERT_HTML_ALERT;
    setTimeout(function(){
        confirmCustom(m,function(){redirectUrl(url_main+r)},t);
        $('.alert_wrapper').on('click',function(){return false});
    },d);
}

var confirmCustomWithProfile = function(data, handler, hCancel_or_title, title, btnOk, btnCancel) {
	closeAlert();
    btnOk=btnOk||ALERT_HTML_OK;
    btnCancel=btnCancel||ALERT_HTML_CANCEL;
    var noCancel=(typeof(hCancel_or_title) != 'function'),
		title=(noCancel ? hCancel_or_title : title)||ALERT_HTML_PLEASE_CONFIRM;
    var userUrl='#';
    if(data.user_url){
        userUrl=urlMain+data.user_url;
    }
    //{action: "request", user_id: "638", user_name: "Mike"}
    var $pp=$('<div class="pp_info_start pp_alert pp_confirm">'+
                '<div class="cont">'+
                    '<div class="title">'+title+'</div>'+
                    '<div class="pic_chat">'+
                        '<div class="pic">'+
                            '<a class="photo" href="'+userUrl+'" target="_blank" title="" style="background-image: url('+urlFiles+data.photo+');"></a>'+
                        '</div>'+
                        '<span class="info">'+
                            '<strong><a class="name" href="'+userUrl+'">'+data.user_name+'</a></strong>'+
                            data.age+' • '+data.city+
                        '</span>'+
                    '</div>'+
                    '<div class="double_btn">'+
                        '<button class="confirm_close btn small dgrey">'+btnCancel+'</button>'+
                        '<button class="confirm_ok btn small marsh">'+btnOk+'</button>'+
                        '<div class="cl"></div>'+
                    '</div>'+
                '</div>'+
            '</div>').modalPopup({wrClass: 'confirm_wrapper',wrCss:{zIndex:1001}}).open()
	$('button', $pp).click(closeAlert)
	$('.confirm_ok', $pp).click(handler);
	if (!noCancel) $('.confirm_close', $pp).click(hCancel_or_title)
}

function alertSuccess(msg, shadow, title, hClose, css)
{
    if(typeof(hClose)!='function'){
        hClose=closeAlert;
    }
    title=defaultFunctionParamValue(title, ALERT_HTML_SUCCESS);
    closeAlert();
    var $pp=$('<div class="pp_info_start pp_alert">'+
        '<div class="cont">'+
            '<div class="title">'+title+'</div>'+
            '<div class="img_chat">'+
                '<div class="icon td"><img src="'+url_tmpl_images+'icon_pp_successful.png" alt="" /></div>'+
                '<div class="msg td">'+msg+'</div>'+
            '</div>'+
            '<div class="one_btn">'+
                '<button class="confirm_ok btn small marsh">'+ALERT_HTML_OK+'</button>'+
                '<div class="cl"></div>'+
            '</div>'+
        '</div>'+
    '</div>').modalPopup({wrClass:'alert_wrapper',
                          shClass:(shadow?'':'page_shadow_empty'),
                          wrCss:{zIndex:1001},
                          css:css}).open();
    $('.confirm_ok', $pp).click(hClose);
}

function alertMutualLike(urlProfile, urlPhoto)
{
    hClose = closeAlert;
    closeAlert();

    title = l('alert_title_mutual_like');

    var $pp=$('<div class="pp_info_start pp_alert">'+
        '<div class="cont">'+
            '<div class="title">'+title+'</div>'+
            '<div class="pic_chat">'+
                '<div class="pic">'+
                    '<a class="photo" href="' + urlMain + urlProfile + '" style="background-image: url(' + urlFiles + urlPhoto + ');"></a>'+
                '</div>'+
            '</div>'+
            '<div class="one_btn">'+
                '<button class="confirm_ok btn small marsh">'+ALERT_HTML_OK+'</button>'+
                '<div class="cl"></div>'+
            '</div>'+
        '</div>'+
    '</div>').modalPopup({wrClass:'alert_wrapper',
                          shClass:'page_shadow_empty',
                          wrCss:{zIndex:1001}
                          }).open();
    $('.confirm_ok', $pp).click(hClose);
}

function alertServerError(notReload)
{
    var fn;
    if(!(notReload||0)){
        $(document).off('click');
        fn=function(){location.reload()};
    }
    alertHtml(siteLangParts['server_error_try_again'],true,ALERT_HTML_ALERT,fn);
}

$(document).on('click', '.pp_alert .icon_close, .alert_wrapper', closeAlert);
/* Popup ALERT */

function stopAllPlayers(){
    var pl;
    for(var k in videoPlayers) {
        pl = videoPlayers[k];
        if (isPlayerNative) {
            if (!pl.paused) pl.pause();
        } else if (typeof pl =='object' && typeof pl.dispose == 'function'){
            if (!pl.paused()) pl.pause();
        }
    }
}
function destroyAllCustomPlayers(){
    var pl;
    if (isPlayerNative)return;
    for(var k in videoPlayers) {
        pl = videoPlayers[k];
        if (typeof pl =='object' && typeof pl.dispose == 'function'){
            pl.dispose();
            delete videoPlayers[k];
        }
    }
}

function showError(sel, text, $btn){
    $btn=$btn||{};
    var $error=getCacheJq(sel+'_error'),hd=0;
    if ($error.is('.to_show')) {
        hd=$error.data('h');
    }
    var h=$error.html(text).css('height', 'auto').height();
    $error.height(hd).data('h', h).css({opacity:1,marginBottom:'-10px'}).height(h).addClass('to_show');
    getCacheJq(sel).addClass('wrong').focus();
    $btn[0]&&$btn.prop('disabled',true);
}

function hideError(sel){
    getCacheJq(sel+'_error').removeAttr('style').removeClass('to_show');
    getCacheJq(sel).removeClass('wrong');
}

function confirmBlockUser(uid){
    if(!checkLoginStatus())return;
    if(Profile.requestAjax['blocked'])return;
    Profile.confirmBlockUser(uid);
}

function sendLike(uid,$btn){
    if (ajax_login_status) {
        Profile.sendLike(uid,$btn);
    }else{
        if (currentPage == 'join2.php') {
            joinLike(uid,$btn);
        }else{
            redirectToLogin();
        }
    }
}

function updateCounterText(el,text){
    var val=$jq(el).text();
    if(val!=text){
        $jq(el).text(text);
    }
}

function updateCounter(el,count,isNew,forceUpdate){
    isNew = parseInt(isNew || 0);
    var val=$jq(el).text()*1;
    forceUpdate = forceUpdate || 0;
    if(val!=count || forceUpdate){
        if(parseInt(count) === 0) {
            count = '';
            isNew = 0;
        }
        $jq(el).text(count)[isNew?'addClass':'removeClass']('decor');
    }
}

function updateCounterTitle(el,inc){
    var count,inc=inc||false;
    el=$(el);
    count=el.text()*1;
    if(inc){count -=1}else{count +=1};
    if(count<0)count=0;
    updateCounter(el,count);
}

function openPopupUpdate(sel,css,shCss,wrClass,shClass,notOpen){
    notOpen=notOpen||0;
    if (!openPopupList[sel]){
        openPopupList[sel]={};
        var $pp=$(sel);
        openPopupList[sel]['html']=$pp.html();
        openPopupList[sel]['class']=$pp.attr('class');
        openPopupList[sel]['close']=0;
        openPopupList[sel]['el']=$pp.modalPopup({css:css||{zIndex:1001},shCss:shCss||{}, wrClass:wrClass||'wrapper_custom',shClass:shClass||'pp_shadow_white'});
    }
    openPopupList[sel]['el'].find('.cont').removeClass('to_hide');
    if(!notOpen)openPopupList[sel]['el'].open();
    return openPopupList[sel]['el'];
}

function closePopupUpdate(name,update){
    if (openPopupList[name]&&openPopupList[name]['el'].is(':visible')){
        update=update||0;
        openPopupList[name]['close']=1;
        var $pp=openPopupList[name]['el'].close();
        if(!update){
            setTimeout(function(){
                openPopupList[name]['close']=0;
                $pp.html(openPopupList[name]['html']);
                $pp.removeAttr('class style').addClass(openPopupList[name]['class']+' pp_cont');
            },210);
        }
    }
}

function updateUsersList($items){
    var l=$items.length;
    if(!l)isLoadBaseListUsers=false;
    var i=0,$users=$([]);
    $items.each(function(){
        if(!$('#'+this.id)[0])$users.push($(this)[0]);
        if(l==++i){
            if($users[0]){
                $users.hide().appendTo('#page_list_users').each(function(){
                    var t=200,i=0;
                    (function fu(){
                        var item=$users.eq(i);
                        if(item[0]){
                            item.slideDown(t*=.8, function(){
                                i++; fu();
                            })
                        }else{
                           isLoadBaseListUsers=false;
                        }
                    })()
                    return false;
                })
            }else{
                isLoadBaseListUsers=false;
            }

        }
    })
}

function checkSupportWebrtc(type){
    var is=supportWebrtc();
    if (is=='ssl') {
        alertCustom(siteLangParts['lChromSsl_'+type], true, ALERT_HTML_ALERT)
        is=false;
    }else if(is===false){
        alertCustom(siteLangParts.lNoWebrtcSupport, true, ALERT_HTML_ALERT)
    }
    return is;
}

function videoChatInvite(uid,noCloseMenu){
    if(!checkLoginStatus())return;
    if(!userAllowedFeature['videochat']){
        redirectUrl(url_main+'upgrade.php');
        return;
    }
    if(!Profile.getRealStatusOnline()){
        alertCustom(siteLangParts.user_not_online, true, ALERT_HTML_ALERT);
        return;
    }
    if(!checkSupportWebrtc('video')){
        //$jq('.video_chat_menu').fadeOut(200);
        return;
    }
    noCloseMenu=noCloseMenu||false;
    !noCloseMenu&&videoChat.ppMenuExpand();
    if(videoChat.price>0){
        $.post(url_main+'ajax.php',{cmd: 'get_available_credits'}, function(res){
        var data=checkDataAjax(res);
		if (data){
            var balance=data*1;
            if(balance<videoChat.price){
                confirmCustom(siteLangParts.you_have_no_enough_credits,
                              function(){upgrade.requestIncreasePopularity('pp_payment','video_chat')},
                              ALERT_HTML_ALERT, false, siteLangParts.buy_credits)
            } else {
                upgrade.requestIncreasePopularity('pp_payment','video_chat')
            }
        }
        })
    } else {
        videoChat.invite(uid,noCloseMenu);
    }
}

function audioChatInvite(uid,noCloseMenu){
    if(!checkLoginStatus())return;
    if(!userAllowedFeature['audiochat']){
        redirectUrl(url_main+'upgrade.php');
        return;
    }
    if(!Profile.getRealStatusOnline()){
        alertCustom(siteLangParts.user_not_online, true, ALERT_HTML_ALERT);
        return;
    }
    if(!checkSupportWebrtc('audio')){
        //$jq('.audio_chat_menu').fadeOut(200);
        return;
    }
    noCloseMenu=noCloseMenu||false;
    !noCloseMenu&&audioChat.ppMenuExpand();
    if(audioChat.price>0){
        $.post(url_main+'ajax.php',{cmd: 'get_available_credits'}, function(res){
        var data=checkDataAjax(res);
		if (data){
            var balance=data*1;
            if(balance<audioChat.price){
                confirmCustom(siteLangParts.you_have_no_enough_credits,
                              function(){upgrade.requestIncreasePopularity('pp_payment','audio_chat')},
                              ALERT_HTML_ALERT, false, siteLangParts.buy_credits)
            } else {
                upgrade.requestIncreasePopularity('pp_payment','audio_chat')
            }
        }
        })
    } else {
        audioChat.invite(uid,name);
    }
}

function showErrorFrm(el, text, $input, cl){
    var cl=cl||'.error_frm',
        $el=$(el).addClass('wrong').focus(),$error=$el.next(cl);
    if ($error.is('.to_show')&&text==$error.text()) {
        //$error.html(text);
        return;
    }
    var h=$error.text(text).css('height', 'auto').height();
    	$error.height(0);
	setTimeout(function(){
		$error.css({height:h}).addClass('to_show');
	},1);
    $input.prop('disabled', true);
}

function hideErrorFrm(el,$input,cl){
    cl=cl||'.error_frm';
    $(el).removeClass('wrong').next(cl).removeClass('to_show').css({height:0});
    $input.prop('disabled', false);
}

function redirectToProfile(e,url){
    var $tag=$(e.target);
    if(!$tag.is('.not_redirect')&&!$tag.closest('.not_redirect')[0]){
        redirectUrl(url);
    }
}

function updateCountersLikes(data) {
    if(data) {
        var counters = ['who_likes_you', 'whom_you_like', 'mutual_likes'];
        for(counter in counters) {
            updateCounter('#narrow_' + counters[counter] + '_count', data['number_' + counters[counter]], data['number_' + counters[counter] + '_new'], true);
        }
    }
}

function stylizeMainPhoto(){
    $('[data-main-photo]').each(function(){
        if($(this).data('mainPhoto')==null)return;
        stylizeOneMainPhoto($(this));
    })
}

function stylizeOneMainPhoto($mainPhoto) {
    $mainPhoto=$mainPhoto||$jq('#pic_main_img');
    if($mainPhoto.data('mainPhoto')){
        if($mainPhoto.is('button')){
            $mainPhoto.removeClass('photo_empty');
            $mainPhoto.addClass('photo_right_column');
        }else{
            $mainPhoto.closest('.pic').removeClass('photo_empty');
        }
    }else{
        if($mainPhoto.is('button')){
            $mainPhoto.addClass('photo_empty');
            $mainPhoto.removeClass('photo_right_column');
        }else{
            $mainPhoto.closest('.pic').addClass('photo_empty');
        }
    }
}

function preparePageWithShowBanner(){
    if(activePage=='general_chat.php' && typeof GeneralChat!='undefined'){
        GeneralChat.prepareViewOneChat(true);
    }else if (activePage=='messages.php' && typeof messages!='undefined'){
        messages.prepareViewOneChat(true);
    }
}

function redirectWithLoader($el, url){
    $el.addLoader();
    redirectUrl(url);
}


function approvePhoto(pid){
    var cmd=$('input[name="do['+pid+']"]:checked').val();
    if(cmd=='del'){
        cmd='add';
    }
    redirectUrl(urlMain+'moderator.php?do%5B'+pid+'%5D='+cmd);
}

function approvePhotoDelete(pid){
    confirmCustom(
        l('confirm_delete_photo'),
        function(){
            redirectUrl(urlMain+'moderator.php?section=photo&do%5B'+pid+'%5D=del');
        }
    )
}