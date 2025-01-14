var opens = [];
var writing = [];
var status_writing = [];
var set_is_read_msg = [];
var uploading_msg = [];
var uploading_first_msg = [];
var blink = [];
var timeout;
var timeoutSec = 10000;
var timeoutSecServer = timeoutSec/1000*1.5;
var cycles_count=1, skip_widgets=3;
var updateStart = false;
var widgets_site_count = 10;//ECA-input from 9 to 10
var last_id = 0;
var imScrollbar = false;
var isEnter = false;
var imMsgLayout = 'default';
var siteTitle = '';
var isFbModeTitle = 'false';
var dur = 600;

document.cookie = "pagechk=0";
function updateAjax() {
	if (updateStart) {
		cycles_count%=skip_widgets;
		var widgets=(!cycles_count++ && $('.bl_widget')[0])?
		 (eval(is_home_widget)?2:1):
		 ($('#widget_6')[0]?6:0);
        xajax_update(last_id, imMsgLayout, dirTmplMain, isFbModeTitle, widgets, status_writing, timeoutSecServer, set_is_read_msg);
	}
	clearTimeout(timeout);
	timeout = setTimeout("updateAjax()", timeoutSec);
}

function initAjax() {
	$('<div id="xajax_im_open" />').hide().appendTo('#xajax_im');
	// ON for authorized only
	if(!xajax_login_status) {
		return;
	}
    xajax_init_client(imMsgLayout, dirTmplMain, is_home_widget)
	/*xajax_widget(is_home_widget);
	xajax_im(imMsgLayout, dirTmplMain);*/

	updateStart = true;
	// timeout = setTimeout("updateAjax()", timeoutSec);
	xajax_im_save_position_input=function(){};
}

function reset_opens(id) {$('#xajax_im_open_'+id).mousedown(); $(document).mouseup()};

function key(evt)
{
	evt = (evt) ? evt : (window.event) ? window.event : '';
    var key;
    if (evt) {
        key = (evt.which) ? evt.which : evt.keyCode;
    }
    return (key == 13);
};

function im_sent(el) {
	if (el.value=$.trim(el.value)) xajax_im_sent(xajax.getFormValues1(el.form), last_id, imMsgLayout, dirTmplMain, isFbModeTitle, timeoutSecServer, set_is_read_msg);
	$(el).val('').triggerHandler('autosize');
	return false
};

function resetEnterKey(evt)
{
    if (key(evt) == true && isEnter == true) {
        isEnter = false;
    }
};

function delete_writing_user(id) {
	$('#xajax_im_open_' + id).find('.is_writing')
                             .fadeOut(dur, function() {clearInterval(blink[id]);$(this).remove();});
};

function is_writing_user(id) {
    var dirTmpl = dirTmplMain.substr(1, dirTmplMain.length-1),
        imImgWriting = "<img class='is_writing' src='" + dirTmpl + "images/is_writing.png' width='21' height='9'>",
        div=$('.user_photo_' + id).last(),
        is_writing = div.prev('.is_writing').length;
    if (is_writing == 0) {
        var images = $(imImgWriting );
        images.hide().insertBefore(div).fadeIn(dur);
        blink[id] = setInterval(function(){images.animate({opacity: 'toggle'}, 600)}, 600);
    }
};

function is_read_msg(id) {
    var dirTmpl = dirTmplMain.substr(1, dirTmplMain.length-1),
        imImgCheck = "'<img class='read' src='" + dirTmpl + "images/checkmark.png' width='10' height='8'>",
        div = $('#no_read_' + id).attr('id', 'read_' + id);
    $(imImgCheck).hide().appendTo(div).fadeIn(dur);
    set_is_read_msg[id] = 1;
};


function uploading_im_msg(uid, imMsgLayout, dirTmplMain) {
    if($('#msg_'+uploading_first_msg[uid])[0]||uploading_first_msg[uid]==0)return;
    var top,$blMsg=$('#xajax_im_msgs_' + uid),
        tiny=$('.scrollbarY_'+uid);
    if(tiny[0]){
        top=$('.scrollbarY_'+uid).find('.overview').position().top
    }else{top=$blMsg.scrollTop()}
    if(uploading_msg[uid]!=undefined&&!uploading_msg[uid])return;
    if(!top&&!$blMsg.find('.message:animated')[0]){
        var lastMsg;
        if(tiny[0]){lastMsg=$blMsg.find('.message:first')
        }else{lastMsg=$blMsg.find('.cumsg:first')}
        if(!lastMsg[0])return;
        uploading_msg[uid]=0;
        $blMsg.prepend('<div class="loader"></div>');
        xajax_uploading_msg(uid,lastMsg.attr('id').replace('msg_',''),imMsgLayout,dirTmplMain);
    }
}

function showOriginalMsg(e,id)
{
    $(e).siblings('.original_message').toggle();
    var  tiny=$('.scrollbarY_'+id);
    if(tiny[0]){
        $('.scrollbarY_'+id).data('plugin_tinyscrollbar').update('relative');
    }
}


function append_msg(id, msg, msg_id, isUpload) {
    var isUpload=isUpload||0;
	var div=$('#xajax_im_msgs_' + id);
	if (div[0]) {
        if (isUpload){
            div.find('.loader').remove();
        }
        if (!div.children('#msg_'+msg_id)[0]) {
            if (Drag.el) $('#xajax_im_open_'+id).insertBefore($(Drag.el))
            else $('#xajax_im_open_'+id+':not(:last-child)').appendTo($('#xajax_im'));
            $(msg).hide()[!isUpload?'appendTo':'prependTo'](div)
                  .css({background: !isUpload?'rgba(256,256,0,0.3)':'', transition: !isUpload?'background 1s':''})
                  .stop().slideDown({step: function(){
                        div.scrollTop(!isUpload?div[0].scrollHeight:1);
                        if (imScrollbar) {
                            $('.scrollbarY_'+id).data('plugin_tinyscrollbar').update(!isUpload?'bottom':1);
                        }
                  }});
            if (!isUpload) {
                $('#xajax_im_open_'+id).mouseover(function hideNew(){
                    if (this.parentNode.lastChild!=this) return;
                    setTimeout("$('#msg_"+msg_id+"').css('background', '')", 10);
                    $(this).off('mouseover', hideNew)
                })
            }else{
                uploading_msg[id]=1;
            }
        }
	}
	else
	{
		xajax_im_open_new(id, imMsgLayout, dirTmplMain, 0, 0, isFbModeTitle);
	}
};

function imSetTopPosition(imIndex)
{
		// var wtop = parseInt(document.getElementById('xajax_im_open_' + imIndex).style.top);
	// if(typeof(siteTopOffset) != 'undefined' && wtop < siteTopOffset) {
		// document.getElementById('xajax_im_open_' + imIndex).style.top = siteTopOffset + siteTopOffsetUnit;
	// }
}

function showImageIm(img) {
    var $img=$(img).addClass('to_show');
    if(!$img[0])return;
    initLightboxOldTemplate($img.closest('a'));
}
if (typeof vdrag == 'undefined') {
	var vdrag = true;
}

var Drag = {
	el:null,
	init: function(TargEl, el){
		el='#'+(el||TargEl); TargEl='#'+TargEl; //el+' .im_bottom_3,
		var $el=$(el).css({'z-index':1, position:'fixed', opacity:0, visibility:'visible'}),
		  cBody=$(el+' .chatbody'), pos=$el.position(), left=pos.left, top=pos.top, stop;
		if ($(TargEl).hasClass('draggable')) return;
		if (cBody[0]) cBody.scrollTop(cBody[0].scrollHeight);
		$(TargEl).addClass('draggable');
		// Fix IM hidden on window resize
		$(el).addClass('draggable');
		$el.attr('title', $.trim($('.im_top, .w_txt', $el).text()));
		$('#xajax_im>*').each(function(i){
			if ($el[0]==this) return;
			var pos=$(this).position();
			if (Math.abs(pos.left-left)<10 && Math.abs(pos.top-top)<10) {
				left+=30; top+=30;
			}
		})
		if (left!=pos.left||top!=pos.top) $el.css({left:left, top:top});
        //Control update scroll IM
        var isImNew = el.match("xajax_im_open");
		if(isImNew) {
			// Fix IM hidden on window resize
			$(el).addClass('draggable');
		}
        if (isImNew && imScrollbar) {
            $('.scrollbarY_'+el.replace(/#xajax_im_open_/, "")).data('plugin_tinyscrollbar').update('bottom');
        }
		//nnsscc-diamond-20200317-start
		if(el=="#widget_9"){
			$(el).find("#widget_title_9").html("Chat");
			$(el).addClass("chat_widget");	
			$(el).find("#widget_inner_9").html('<iframe src="oryx_public_chat.php"></iframe>');
		}
		//nnsscc-diamond-20200317-end

		//ECA_input-start
		if(el=="#widget_10"){
			$(el).find("#widget_title_10").html("Radio");
			$(el).addClass("radio_widget");	
			$(el).find("#widget_inner_9").html('<iframe src="Whisprrz_Radio.php"></iframe>');
		}
		//ECA_input-end
		
		Drag.onResize(el);
		$(el+' [onclick*="widget_close"]').click(function(){return false});
		$(el+' [onclick*="xajax_im_close"]').click(function(){ //alert();
			$el.fadeOut(300, function(){$el.remove()})
		});
		$el.on('mousedown touchstart', function(e){
			$el.css({zIndex: 2});
			function dragStart(){
				if ($(e.target).is('[onclick]')) return;
				$el.one('dragstart', function(){return false});
				if (e.type=='mousedown') e.preventDefault();
				var pos=$el.addClass('active').position(),
				  tch=(e.type=='touchstart')?e.originalEvent.touches:[];
				if (pos.left||pos.top) $el.removeClass('new');
				e=tch[0]||e
				if (tch.length>1||Drag.el||!e.clientX) return;
				Drag.x=e.clientX-pos.left;
				Drag.y=e.clientY-pos.top;
				Drag.el=el;
			}
			if ($(e.target).closest('.im_top, .chathead, .bl_widget_head_title')[0] || e.type=='touchstart') dragStart()
			else if (e.clientX) Drag.timer=setTimeout(dragStart, 500);
			$(document).one('mouseup touchend',function(e){
				setTimeout(function(){
					var focus=$(el+' *:focus'), cTop=cBody.scrollTop()||0;
					$el.css('z-index', 1).removeClass('active')
					 .not(':last-child').appendTo('#xajax_im').mouseover();
					focus.focus();
					cBody.scrollTop(cTop);
				},1);
				clearTimeout(Drag.timer); Drag.el=0;
				if ($el.is(':last-child')&&!Drag.start) return;
				var pos=$el.position(), x=Math.round(pos.left), y=Math.round(pos.top);
				if(el.match("xajax_im_open")) {
					im_id = el.substring(15);
					xajax_im_save_position(xajax.getFormValues('sent_msg_'+im_id), x, y, 1);
				} else if (el.match("widget")) {
					xajax_widget_save(el.substr(1), x, y, 1);
				}
				if (!Drag.start) return;
				$(e.target).one('click', function(){return false});
				Drag.onResize(el); Drag.start=0
			});
		})
	},
	onResize: function(el){
		if (el&&typeof(el)!="string") el=0;
		$(Drag.el||el||'.draggable').each(function(){
			var pos=$(this).position(), isIm=this.id.match("xajax_im_open");
			$(this).stop().animate({
				left: Math.round(Math.min(Math.max(pos.left, isIm?-100:pos.left), $(window).width()-50)),
				top: Math.round(Math.min(Math.max(pos.top, isIm?0:pos.top), $(window).height()-50)),
				opacity: 1
			}, 300, function(){
				$('.bl_widget_cont', this).css('margin-top', '')
			})
		})
	}
}

/*var timerWriting = setInterval(function() {
    var currentTime = Date.now(),
        diffTime,
        temp = [];
    for(user_id in writing)  if (writing.hasOwnProperty(user_id)) {
        diffTime = currentTime - writing[user_id];
        status_writing[user_id] = (diffTime <= 10000) ? 1 : 0;
    }
}, 2000);*/

$(window).resize(Drag.onResize)
$(function(){
	$('body').on('mousemove touchmove', function(e){
		clearTimeout(Drag.timer);
		if (!Drag.el) return;
		Drag.start=1;
		var tch=(e.type=='touchmove')?e.originalEvent.touches:[];
		if (tch.length>1) return;
		e=tch[0]||e;
		$(Drag.el).css({
			left:Math.round(Math.max(e.clientX-Drag.x, 50-$(Drag.el).width())),
			top: Math.round(Math.max(e.clientY-Drag.y, Drag.el.match("widget")?50-$(Drag.el)[0].scrollHeight:-10))
		})
		return false
	});
})

var isReadMsg=false;
$(function(){
    $.winFocus({
        blur: function(e) {
            if (xajax_login_status) {
                localStorage.setItem('is_fb_mode', 'true');
                isFbModeTitle = 'true';
                xajax_unset_window_active();
            }
        },
        focus: function(e) {
            if (xajax_login_status) {
                localStorage.setItem('is_fb_mode', 'false');
                isFbModeTitle = 'false';
                localStorage.removeItem('is_title');
                localStorage.setItem('is_title', 'true');
                document.title = siteTitle;
                if(isReadMsg)xajax_read_msg();
                isReadMsg=true;
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