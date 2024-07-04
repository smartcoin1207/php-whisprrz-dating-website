$(function(){
    $jq('.menu_sign_out_edge').click(function(){
        logOut();
        return false;
    })

    $('.dropdown-menu a').not('.menu_invite_friends_by_sms_edge').click(closeMoreMenu);

    $('body, .menu_collapse a').not('.menu_audio_greeting_edge').on('click', function(e){
		var $target=$(e.target),sel='.menu_collapse.in';
        if(!$target.is('.more_menu_collapse')&&!$target.closest('.more_menu_collapse')[0]){
           //&&!$target.is('.btn.btn-success')&&!$target.closest('.btn.btn-success')[0]
            sel +=', .more_menu_collapse.in';
        }
        closeMenuCollapse(sel);
    });

    initGridPhoto();

    $('.header_navbar_item_menu').on('click', function(e){
        var $el=$(this),$targ=$(e.target);

        if($targ.is('.header_small_item_menu_name')||$targ.closest('.header_small_item_menu_name')[0]){
            return;
        }
        //if($el.is('.active')||$targ.closest('.active')[0])return false;
        var url=$el.data('url')||$el.attr('href');

        if (notLoaderIos){
            if(url)redirectUrl(url);
            return false;
        }

        var clLoader='header_navbar_item_menu_loader';
        if($el.closest('.wrap_profile_menu_inner_small')[0])clLoader='header_navbar_item_menu_small_loader';
        var $loader=$el.find('.count_p');
        if($loader[0]){
            clLoader +='_p';
        } else {
            $loader=$el.find('.count');
            if ($loader.find('.header_small_item_menu_name')[0]) {
                $loader.data('clChildren','.header_small_item_menu_name');
            }
        }
        $loader.data('clLoader',clLoader).addChildrenLoader();
        if(url)redirectUrl(getPrepareUrl(url))
        return false;
    })

    $('a.mn_circle, .circles a.circle').on('click', function(e){
        var $el=$(this),$targ=$(e.target),href=$el.attr('href');
        if (href && href != '#mn_circle_more_list' && href != '#mn_circle_small_more_list') {
            if ($el.is('.menu_live_streaming_edge') && $el.is('.disabled')) {
                return false;
            }
            if(!notLoaderIos)$el.find('.icon_fa').addChildrenLoader();
            redirectUrl(getPrepareUrl(href));
            return false;
        }
    })

    $('a.menu_live_streaming_edge').on('click', function(e){
        var $el=$(this);
        if ($el.is('.disabled')) {
            return false;
        }
    })

    $('.menu_audio_greeting_edge').on('click', function() {

        if(audioGreetingPlayerIsLoaderActive) {
            return false;
        }

        if(audioGreetingPlayer === null || audioGreetingPlayer.playState === 0) {
            audioGreetingPlay();
        } else {
            audioGreetingPlayer.stop();
        }

        return false;
    });

    $('.menu_audio_greeting_edge').on('mouseout', function() {
        $(this).removeClass('menu_audio_greeting_edge_stop');
    });


    $('.header_user_name_tooltip').tooltip({trigger:'manual', viewport:'', container:'.header_user_name_tooltip'})
    .hover(
        function(e){
            var $el=$(this), $st=$el.find('strong');
            if(!$el.data('short') && ($st.find('span')[0].offsetWidth <= ($st[0].offsetWidth+2))){
                return false;
            }
            $el.tooltip('show');

        },
        function(e){
            var $el=$(this);
            $el.tooltip('hide');
        }
    )

    if (isMobileSite) {
        /*$jq('body').on('touchmove', function(e){
            if($jq('body')[0].scrollTop==0){
                e.preventDefault();
            }
        })*/
    } else if(isIframeDemo){
        $('[data-tooltip="true"], .pl_grid_count .bl').attr('title', '');
    } else {
        $('[data-tooltip="true"]').tooltip({viewport:'', container:'body', delay:{'show': 500}}).click(function(){
           $(this).tooltip('hide')
        })

        $('.pl_grid_count .bl').tooltip({trigger:'manual', viewport:''}).hover(
        function(e) {
            var $el=$(this), $targ=$(e.target);
            if($targ.is('.header_small_item_menu_name')){
                $el.closest('.item').addClass('disabled');
                return;
            }
            if ($targ.is('.icon_upload_file') || $targ.closest('.icon_upload_file')[0]
                    || $targ.is('.header_user_name_tooltip') || $targ.closest('.header_user_name_tooltip')[0]){
                return;
            }
            $el.data('hide',false);
            setTimeout(function(){
                !$el.data('hide') && $el.tooltip('show')
            },500)
        },function(e) {
            var $el=$(this), $targ=$(e.target);
            if($targ.is('.header_small_item_menu_name')){
                $el.closest('.item').removeClass('disabled');
                return;
            }
            if ($targ.is('.icon_upload_file') || $targ.closest('.icon_upload_file')[0]
                    || $targ.is('.header_user_name_tooltip') || $targ.closest('.header_user_name_tooltip')[0]){
                return;
            }
            $el.data('hide',true).tooltip('hide')
        }).mousemove(function(e){
            var $el=$(this), $targ=$(e.target);
            if ($targ.is('.icon_upload_file') || $targ.closest('.icon_upload_file')[0]){
                $el.data('hide',true).tooltip('hide')
                return;
            }
        }).click(function(){
           $(this).data('hide',true).tooltip('hide');
        })
    }

    $jq('body').on('wheel mousewheel', function(e){
        if(e.ctrlKey)return;
        if(isWheelMenu)e.stopPropagation();
    })

    var isWheelMenu=false;
    $('.menu_more_menu_navbar_site').on('mouseenter mouseleave', function(e){
        isWheelMenu=e.type=='mouseenter';
    })

    $('.menu_more_menu_navbar_site a, .navbar-nav a').on('click mouseup', function(e){
		if (isDragCoverStart(e)){
			return false;
		}
        if(e.which && e.which == 3){
            return false;
        }
        var $btn=$(this);
        if (this.id=='navbar_menu_more' || !$btn.data('href'))return true;
        if ($btn.closest('li.active')[0]) return false;
        if (e.type == 'mouseup') {
            if (!notLoaderIos){
                addChildrenLoader($btn);
            }
            redirectUrl(this.href);
            return false;
        }
    })

    $jq('#navbar_menu_more').click(function(){
        $jq('#navbar_menu_more_dropdown').dropdown('toggle');
        return false;
    })

    var menu_invite_friends_by_sms_edge_running = false;

    $jq('.menu_invite_friends_by_sms_edge').click(function(){

        if(menu_invite_friends_by_sms_edge_running) {
            return false;
        }

        menu_invite_friends_by_sms_edge_running = true;

        function iosInviteFriendsHideLoader() {
            removeChildrenLoader($('.menu_invite_friends_by_sms_edge'));
            menu_invite_friends_by_sms_edge_running = false;
        }

        addChildrenLoader($('.menu_invite_friends_by_sms_edge'));
        YoolpContacts.inviteFriends(inviteFriendsBySmsMessage);

        setTimeout(iosInviteFriendsHideLoader, 2000);

        return false;
    })

    $('.menu_fb_invite_edge').click(function(){
		inviteFriendsFacebook();
        return false;
    })

    $('.menu_profile_verification_edge, .profile_verification_btn, .profile_verification_show').click(function(){
        clProfile.openPopupEditorVerification();
        return false;
    })

})


function closeMoreMenu(){
    //$jq('#navbar_menu_more').click();
	closeMenuCollapse();
}

function closeAllMenuAndPopup(){
    closeMenuCollapse();
}

function closeMenuCollapse(sel){
    sel=sel||'.menu_collapse.in';
	$(sel).collapse('hide');
}

function closeNavbarMenuCollapse(fn){
    if(typeof fn!='function')fn=function(){};
    if(isMobileSite){
        var $nav=$('.navbar-collapse.in');
        if ($nav[0]) {
            $nav.collapse('hide');
        }
        setTimeout(fn,50);
    } else {
       fn();
    }
}

var typeGridPhoto = '';
function setTypeGridPhoto(type){
    typeGridPhoto=type;
}

function updateGridPhotoFromDelete(pid){
    var $gEl=$('.grid_item_'+pid+':visible');
    if(!$gEl[0])return;
    if($gEl.position().top>=$('#block_grid')[0].offsetHeight)return;
    updateGridPhoto();
}

function updateGridPhotoFromPublish(redirect){
    if(redirect||false)return;
    updateGridPhoto();
}

function updateGridPhoto(fnResponse, clearCover){
	if(typeof fnResponse !==  'function'){
		fnResponse=function(){}
	}
    var $blGrid=$('#block_grid').addClass('grid_update'),
        $grid=$('#grid'),
        pageUrl=(loadRouter?'router.php':currentPage)+currentPageParam,
        $blGridNew,
        $gridNew;
    debugLog('Update grid header: ', pageUrl);
	var params={upload_only_header_ajax:1};
	if (clearCover||false) {
		params['clear_cover'] = 1;
	}
    $.post(pageUrl,params,function(res){
        var $page=$(res);

        $blGridNew=$page.find('#block_grid');
        $gridNew=$page.find('#grid');
        if($blGridNew[0]){
            $blGridNew.imagesLoaded(function(){
                initGridHeader.destroy();
                $gridNew.prependTo($blGrid);
                setTimeout(function(){
                    clProfilePhoto.initClickUploadFile($('.photo_upload',$gridNew),'photo');
                    initGridPhoto($gridNew);
                    //setTimeout(function(){$win.resize();},100)
                    $grid.oneTransEnd(function(){
                        $grid.remove();
                        $blGrid.removeClass('grid_update');
						fnResponse()
                    }).delay(150).toggleClass('to_hide', 0);
                },10)
            })
        }
    })
}

var initGridHeader;
function initGridPhoto($grid){
    if(!$('#block_grid')[0])return;
    var sizes=[[2,1],[1,1]];
    if(typeGridPhoto == 'header_custom_big'){
        sizes=[[2,1],[1,1],[2,1]];
    }
    $grid=$grid||$('#grid');
    initGridHeader=$grid.mason({
        itemSelector: '.block',
        viewport: '#block_grid',
        numRows : typeGridPhoto == 'header_custom_big' ? 2 : 1,
        ratio: 1,
        sizes: sizes,//[[1,1],[2,1],[1,2]],
        debug : false,
        filler: {
            itemSelector: '.block',
            filler_class: 'mason_filler',
            keepDataAndEvents: true
        },
        layout: 'fluid',
        //fillerDefault:'<div title="Upload" class="block '+l('upload_photo')+'" style="background-image:url('+url_tmpl_images+'/photo_camera.png);"></div>'
        //gutter: 10,
        //randomFillers: true,
    },function(){
        //console.log('INIT GRID');
    })
}

function showGridPhoto(cl,src){
    $(function(){
        onLoadImgFromList($(cl+'.to_hide'), src);
    })
}

function searchUserByName(){
    $('#search_by_username').submit();
}

function getPrepareUrl(url){
    if (ieVersion() == 11) {
        url = url.replace(urlMain, '');
        url = url.replace(window.location.origin, '');
        url = urlSiteSubfolder + url;
    }
    return url;
}

var audioGreetingPlayer = null;
var audioGreetingPlayerIsLoaderActive = false;

function audioGreetingPlay()
{
    $('.menu_audio_greeting_edge span').data('noFadeIn', true);

    if(audioGreetingPlayerIsLoaderActive) {
        return;
    }

    if (typeof soundManager != 'undefined' &&  typeof urlMain != 'undefined') {

        audioGreetingPlayerIsLoaderActive = true;
        addChildrenLoader($('.menu_audio_greeting_edge'));

        $('.menu_audio_greeting_edge').removeClass('menu_audio_greeting_edge_stop');
        $('.menu_audio_greeting_edge').addClass('menu_audio_greeting_edge_playing');

        var urlFile = urlMain + $('.menu_audio_greeting_edge').data('cmd');

        if(!audioGreetingPlayer) {

            audioGreetingPlayer = soundManager.createSound(
                {
                    url: urlFile,
                    onload: function() {
                        // sometimes doesn't switch on first/second play
                        // $('.menu_audio_greeting_edge i').switchClass('fa-microphone', 'fa-stop');
                        $('.menu_audio_greeting_edge i').addClass('fa-stop').removeClass('fa-microphone');
                        removeChildrenLoader($('.menu_audio_greeting_edge'));
                        audioGreetingPlayerIsLoaderActive = false;
                    },
                    onstop: audioGreetingPlayerOnStop,
                    onfinish: audioGreetingPlayerOnStop,
                    onerror: function(errorCode, description) {
                        audioGreetingPlayer.stop();
                    }
                }
            );
        }

        audioGreetingPlayer.play();
    }
}

function audioGreetingPlayerOnStop()
{
    $('.menu_audio_greeting_edge i').switchClass('fa-stop', 'fa-microphone');
    $('.menu_audio_greeting_edge').removeClass('menu_audio_greeting_edge_playing');
    $('.menu_audio_greeting_edge').addClass('menu_audio_greeting_edge_stop');
    // delete object to prevent error at second load of not exists file
    // reinit audio player because in ios no sound from moment when stop button clicked
    audioGreetingPlayer.destruct();
    audioGreetingPlayer = null;
}

function audioGreetingDelete()
{
    confirmCustom(
        l('this_action_can_not_be_undone'),
        function() {
            location.href = $('.delete_audio_greeting').attr('href');
        },
        l('confirm_delete_audio_greeting')
    );
}

function runAppIosPhotoEditor(imageId, isGallery, authKey)
{
    var isGallery = isGallery || false;
    var authKey = appIosPrepareAuthKey(authKey);

    if(isGallery) {
        addChildrenLoader($('.app_ios_image_editor_' + imageId));
    } else {
        addChildrenLoader($('.app_ios_image_editor_link'));
    }

    var photoUrl = $('base').prop('href') + 'download_source_file.php?cmd=photo&id=' + imageId + authKey;
    var saveUrl = $('base').prop('href') + 'ajax.php?cmd=save_photo_file&photo_id=' + imageId + authKey;
    YoolpImageEditor.editImage(
        photoUrl,
        saveUrl,
        function(isSaved){
            if(isSaved == 'callback') {
                reloadImagesOnPage(imageId, isGallery, 'editor')
            } else {
                appIosImageEditorHideLoader(imageId, isGallery, false, null, 'editor');
            }
        }
    );
}

function runAppIosPhotoCrop(imageId, isGallery)
{
    isGallery = isGallery || false;

    if(isGallery) {
        addChildrenLoader($('.app_ios_image_crop_' + imageId));
    } else {
        addChildrenLoader($('.app_ios_image_crop_link'));
    }

    /*
    setTimeout(function(){
        console.log('reloader');
        console.log(imageId, isGallery, 'crop');
        reloadImagesOnPage(imageId, isGallery, 'crop');
    }, 5000);

    return;
     */

    var photoUrl = $('base').prop('href') + 'download_source_file.php?cmd=photo&id=' + imageId;
    var saveUrl = $('base').prop('href') + 'ajax.php?cmd=save_photo_file&photo_id=' + imageId;
    YoolpImageCropper.cropImage(
        photoUrl,
        clProfilePhoto.cropMinWidth,
        clProfilePhoto.cropMinHeight,
        saveUrl,
        function(){
            reloadImagesOnPage(imageId, isGallery, 'crop');
        },
        function(){
            appIosImageEditorHideLoader(imageId, isGallery, false, false, 'crop');
            alert('Failed to crop image');
        }
    );
}

function reloadImagesOnPage(imageId, isGallery, loaderType)
{
    var randomValue = Math.random();

    var selectorsChangeSrc = [
        '#pp_gallery_photos_img_box img', // viewer
		'#pp_gallery_photo_one_img', // mobile viewer
		'#pp_gallery_photo_one_bl .bl_img.trans img', // mobile on swipe viewer
        '.column_photo_s_' + imageId, // left column
        'a#wall_photo_' + imageId + ' img' // wall
    ];

    var selectorsChangeBackgroundImage = [
        '.grid_item_' + imageId, // header
        '#list_photos_image_photo_' + imageId // photo list
    ];

    if((clProfilePhoto.visibleMediaData[imageId] && clProfilePhoto.visibleMediaData[imageId]['default'] == 'Y') || (clProfilePhoto.galleryMediaData[imageId] && clProfilePhoto.galleryMediaData[imageId]['default'] == 'Y')) {
        selectorsChangeSrc.push('.profile_photo_bm_' + siteGuid);

        selectorsChangeBackgroundImage.push('.profile_photo_r_' + siteGuid);
        selectorsChangeBackgroundImage.push('.profile_photo_s_' + siteGuid);
        selectorsChangeBackgroundImage.push('.wall_profile_photo_user_' + siteGuid);
        selectorsChangeBackgroundImage.push('.wall_profile_info_name_loader');
    }

    var selector;
    var selectorCurrent;
    var photoUrl;
    var photoUrlForUpdate;

    for(selector in selectorsChangeSrc) {
        selectorCurrent = selectorsChangeSrc[selector];
        if($(selectorCurrent).length) {
            photoUrlForUpdate = addUniqueVariableToURL($(selectorCurrent).prop('src'), 'r', randomValue);
            $(selectorCurrent).prop('src', photoUrlForUpdate);
        }
    }

    for(selector in selectorsChangeBackgroundImage) {
        selectorCurrent = selectorsChangeBackgroundImage[selector];

        if($(selectorCurrent).length) {
            photoUrl = $(selectorCurrent).css('backgroundImage').replace(/url\(|\)|"|'/gi, '');
            photoUrlForUpdate = addUniqueVariableToURL(photoUrl, 'r', Math.random());

            $(selectorCurrent).css('backgroundImage', 'url(' + photoUrlForUpdate + ')');
        }
    }

    var changeFields = [
        'src_b',
        'src_bm',
        'src_m',
        'src_r',
        'src_s',
        'user_photo_r'
    ];

    var fieldIndex;
    var fieldName;
    var fieldValue;

    for(fieldIndex in changeFields) {
        fieldName = changeFields[fieldIndex];
        if(clProfilePhoto.visibleMediaData[imageId]) {
            clProfilePhoto.visibleMediaData[imageId][fieldName] = addUniqueVariableToURL(clProfilePhoto.visibleMediaData[imageId][fieldName], 'r', randomValue);
    }
        if(clProfilePhoto.galleryMediaData[imageId]) {
            clProfilePhoto.galleryMediaData[imageId][fieldName] = addUniqueVariableToURL(clProfilePhoto.galleryMediaData[imageId][fieldName], 'r', randomValue);
        }
    }

    var srcBm = clProfilePhoto.visibleMediaData[imageId] ? clProfilePhoto.visibleMediaData[imageId]['src_bm'] : clProfilePhoto.galleryMediaData[imageId]['src_bm'];
    appIosImageEditorHideLoader(imageId, isGallery, true, urlFiles + srcBm, loaderType);

}

function appIosImageEditorHideLoader(imageId, isGallery, isSaved, imageUrl, loaderType)
{
    isSaved = isSaved || false;

    // hide loader by signal from plugin - save/cancel
    if(isGallery) {
        if(isSaved) {
            var img = new Image();
            img.onload = function(){
                removeChildrenLoader($('.app_ios_image_' + loaderType + '_' + imageId));
            }
            img.src = imageUrl;

        } else {
            removeChildrenLoader($('.app_ios_image_' + loaderType + '_' + imageId));
        }
    } else {
        removeChildrenLoader($('.app_ios_image_' + loaderType + '_link'));
    }
}

function runAppIosVideoEditor(videoId, isVigeoGallery)
{
    isVigeoGallery = isVigeoGallery || false;
    if(isVigeoGallery) {
        addChildrenLoader($('.app_ios_video_editor_' + videoId));
    } else {
        addChildrenLoader($('.app_ios_video_editor_link'));
    }

    var randomValue = Math.random();

    var videoIdSrc = videoId;

    videoId = videoId.replace('v_', '');

    var videoPlayer = $('#user_video_' + videoId + '_gallery');

    if(videoPlayer.length) {
        videoPlayer[0].pause();
        videoPlayer[0].currentTime = 0;
    }

    var videoUrl = addUniqueVariableToURL($('base').prop('href') + urlFiles + 'video/' + videoId + '.mp4', 'r', randomValue);
    var saveUrl = $('base').prop('href') + 'ajax.php?cmd=save_video_file&id=' + videoId;
    var posterUrl = addUniqueVariableToURL($('base').prop('href') + urlFiles + 'video/' + videoId + '_src.jpg', 'r', randomValue);

    YoolpVideoEditor.editVideo(
        videoUrl,
        saveUrl,
        posterUrl,
        function(isSaved) {
            if(isSaved == 'saved') {
                reloadVideoOnPage(videoId, isVigeoGallery);
            } else {
                appIosVideoEditorHideLoader(videoIdSrc, isVigeoGallery, false);
            }
        },
        function() {
            alertCustom(l('something_went_wrong_please_try_later'));
            appIosVideoEditorHideLoader(videoIdSrc, isVigeoGallery, false);
        }
    );
    // hide menu item
    //$('#pp_gallery_more_menu .upload_menu_link.dropdown-toggle').click();
}

function appIosVideoEditorHideLoader(videoId, isVigeoGallery, isSaved, posterUrl)
{
    isSaved = isSaved || false;

    // hide loader by signal from plugin - save/cancel
    if(isVigeoGallery) {
        if(isSaved) {
            var img = new Image();
            img.onload = function(){
                removeChildrenLoader($('.app_ios_video_editor_' + videoId));
            }
            img.src = posterUrl;

        } else {
            removeChildrenLoader($('.app_ios_video_editor_' + videoId));
        }
    } else {
        removeChildrenLoader($('.app_ios_video_editor_link'));
    }
}

function reloadVideoOnPage(videoId, isVigeoGallery)
{
    var randomValue = Math.random();

    var selectorsChangeSrc = [
        'a.column_video_' + videoId + ' img', // left column
        '#user_video_' + videoId + '_gallery source', // player
        '#wall_video_' + videoId + ' img' // wall
    ];

    var selector;
    var selectorCurrent;
    var fileUrl;
    var fileUrlForUpdate;

    for(selector in selectorsChangeSrc) {
        selectorCurrent = selectorsChangeSrc[selector];
        if($(selectorCurrent).length) {
            fileUrlForUpdate = addUniqueVariableToURL($(selectorCurrent).prop('src'), 'r', randomValue);
            $(selectorCurrent).prop('src', fileUrlForUpdate);
        }
    }

    var selectorsChangeBackgroundImage = [
        '.grid_item_v_' + videoId, // header
        '.list_videos_image_' + videoId + ' .pic', // video list
        '.video_native_poster' // mobile app
    ];

    for(selector in selectorsChangeBackgroundImage) {
        selectorCurrent = selectorsChangeBackgroundImage[selector];

        if($(selectorCurrent).length) {
            fileUrl = $(selectorCurrent).css('backgroundImage').replace(/url\(|\)|"|'/gi, '');
            fileUrlForUpdate = addUniqueVariableToURL(fileUrl, 'r', Math.random());

            $(selectorCurrent).css('backgroundImage', 'url(' + fileUrlForUpdate + ')');
        }
    }

    var changeFields = [
        'src_b',
        'src_s',
        'src_src',
        'src_v'
    ];

    var fieldIndex;
    var fieldName;
    var fieldValue;

    var videoIdSrc = 'v_' + videoId;

    for(fieldIndex in changeFields) {
        fieldName = changeFields[fieldIndex];
        fieldValue = addUniqueVariableToURL(clProfilePhoto.visibleMediaData[videoIdSrc][fieldName], 'r', randomValue);
        clProfilePhoto.visibleMediaData[videoIdSrc][fieldName] = fieldValue;
    }

    var regexIndex;
    var regexForLinks = [
        /poster=\"(.*?)\"/g,
        /src=\"(.*?)\"/g
    ];

    var videoHtmlCode = clProfilePhoto.visibleMediaData[videoIdSrc]['html_code'];

    for(regexIndex in regexForLinks) {
        videoHtmlCode = videoHtmlCode.replace(regexForLinks[regexIndex], function(match, value) {
            return match.replace(value, addUniqueVariableToURL(value, 'r', randomValue));
        });
    }

    clProfilePhoto.visibleMediaData[videoIdSrc]['html_code'] = videoHtmlCode;

    if(!isVigeoGallery) {
        clProfilePhoto.stopVideoPlayer();
        clProfilePhoto.setVideoPlayer(videoIdSrc, clProfilePhoto.visibleMediaData[videoIdSrc]);
    }

    appIosVideoEditorHideLoader(videoIdSrc, isVigeoGallery, true, fileUrlForUpdate);
}

function approvePhoto(pid){
    var cmd=$('input[name="do['+pid+']"]:checked').val();
    if(cmd=='del'){
        cmd='add';
    }
    redirectUrl(urlMain+'moderator.php?section=photo&do%5B'+pid+'%5D='+cmd);
}

function approvePhotoDelete(pid){
    confirmCustom(
        l('confirm_delete_photo'),
        function(){
            redirectUrl(urlMain+'moderator.php?section=photo&do%5B'+pid+'%5D=del');
        }
    )
}

function redirectUrlLoader($btn, url){
    addLoaderCheckDevice($btn);
    redirectUrl(url);
}

function appIosPrepareAuthKey(authKey)
{
    var authKey = authKey || '';
    authKey = authKey.replace('?', '&');

    return authKey;
}

var dragCover={
	isW: false, isH: false,
	z: 0, x: 0, y: 0,
	offsetX: null, offsetY: null,
	$container: null, dragEl: null, $dragEl: null,
	dragApproved : 0, dragStop : 0, $navbarPage: [],
	init: function($container, $el, adjustw){
		if(isMobileSite)return;

		this.$container=$container;
		this.dragEl=$el[0];
		this.dragEl.ondragstart=function(){return false};
		this.$dragEl=$el;

		this.isW=adjustw;
		this.isH=!adjustw;

		this.$navbarPage=$('.navbar');
		$doc.on('mousedown',this.dragStart);
		$doc.on('mouseup',this.mouseup);
	},
	destroy: function(){
		if(isMobileSite)return;

		$doc.off('mousedown', this.dragStart);
		$doc.off('mouseup', this.mouseup);
		$doc.off('mousemove', this.mousemove);
		this.dragApproved=0;
		this.dragStop=0;
		this.dragEl=null;
	},
	dragStart: function(e){
		var evt=window.event? window.event : e,
			dragEl=dragCover.dragEl;

		this.dragEl=window.event? event.srcElement : e.target;
		if (this.dragEl==dragEl){//.className=="drag"
			this.dragApproved=1;
			dragCover.dragApproved=1;
			if (isNaN(parseInt(dragEl.style.left))){
				dragEl.style.left=0;
			}
			if (isNaN(parseInt(this.dragEl.style.top))){
				dragEl.style.top=0;
			}
			this.offsetX=parseInt(dragEl.style.left);
			this.offsetY=parseInt(dragEl.style.top);
			this.x=evt.clientX;
			this.y=evt.clientY;
			if (evt.preventDefault)evt.preventDefault();
			$doc.on('mousemove', dragCover.mousemove);
			$jq('body').addClass('drag_cover');
		}
	},
	clear: function(e){
		this.dragApproved=0;
		$doc[0].dragApproved=0;
	},
	mouseup: function(e){
		dragCover.dragApproved=0;
		this.dragApproved=0;
		this.dragEl=null;
		$jq('body').removeClass('drag_cover');
	},
	mousemove: function(e){
		if (this.dragApproved==1 && !dragCover.dragStop){
			var evt=window.event ? window.event : e,
				dragEl=dragCover.dragEl,
				$dragEl=dragCover.$dragEl,
				$container=dragCover.$container,
				tr;
			dragCover.isW && (dragEl.style.left=this.offsetX+evt.clientX-this.x+'px');

			if (dragCover.isH) {
				var top=this.offsetY+evt.clientY-this.y,
					d=$container.height()+Math.abs(top),
					dh=$dragEl.height();
				if (top<0 && d<=dh) {
					tr=(parseInt((top*100/dh)*100))/100;
					$dragEl.data('translate', tr).attr('data-translate', tr);
					dragEl.style.top=top+'px';
				}
			}
			return false;
		}
	}
}

function isDragCoverStart(e){
	if(isMobileSite)return false;
	if(e&&e.type=='mouseup'&&dragCover.dragApproved){
		dragCover.clear();
		return true;
	}
	return dragCover.dragApproved;
}

function runAppIosImAudioMessageRecorder(authKey)
{
    var debug = false;

    if($('.app_ios_im_audio_message_recorder').hasClass('app_ios_im_audio_message_delete')) {

        confirmCustom(l('are_you_sure'), function() {appIosImAudioMessageDelete($('.app_ios_im_audio_message_delete').data('im-audio-message-id'))}, l('confirm_delete_audio_message'));

    } else {

        addChildrenLoader($('.app_ios_im_audio_message_recorder'));

        if(debug) {
            //alert('record message');
            $('.app_ios_im_audio_message_recorder').addClass('app_ios_im_audio_message_delete');
            appIosImAudioMessageRecorderHideLoader();
        }

        document.addEventListener(
            'deviceready',
            function() {
                navigator.device.capture.captureAudio(
                    function(response) {

						console.log('runAppIosImAudioMessageRecorder response', response);

                        var isErrorResponse = true;

                        var responseStripSlashes = response.replace(/\"/g,'"');
                        var data = getDataAjax(responseStripSlashes, 'data');

                        if(data) {
                            if(data.result == 'success' && data.id) {
                                $('.app_ios_im_audio_message_recorder').addClass('app_ios_im_audio_message_delete').data('im-audio-message-id', data.id);
                                isErrorResponse = false;
                            }
                        }

                        appIosImAudioMessageRecorderHideLoader();

                        if(isErrorResponse) {
                            alertCustom(l('something_went_wrong_please_try_later'));
                        }
                    },
                    function() { appIosImAudioMessageRecorderHideLoader(); },
                    {
                        limit: 1,
                        successUrl: $('base').prop('href') + 'ajax.php?cmd=save_im_audio_message_IOS' + appIosPrepareAuthKey(authKey)
                    }
                )
            }
        );
    }

}

function appIosImAudioMessageRecorderHideLoader()
{
    removeChildrenLoader($('.app_ios_im_audio_message_recorder'));
}

function appIosImAudioMessageDelete(id)
{
    addChildrenLoader($('.app_ios_im_audio_message_recorder'));

    $.post('ajax.php', { cmd: 'im_audio_message_delete', id: id }, function(response) {

        var data = getDataAjax(response, 'data');

        var isErrorResponse = true;

        if(data) {
            if(data.result == 'success') {
                isErrorResponse = false;
                if($('.app_ios_im_audio_message_delete').data('im-audio-message-id') == id) {
                    $('.app_ios_im_audio_message_delete').removeClass('app_ios_im_audio_message_delete').data('im-audio-message-id', 0);
                }
            }
        }

        if(isErrorResponse) {
            alertCustom(l('something_went_wrong_please_try_later'));
        }

        appIosImAudioMessageRecorderHideLoader();
    });

}