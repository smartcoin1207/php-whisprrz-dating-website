var CGeneralChat = function(uid, room) {
    var $this=this;

    this.uid = uid;
    this.lastId = 0;
    this.room = room;

    this.timeOut=5000;
    this.isUpdate=true;
    this.initUpdate = function(){
        if(!ajax_login_status)return;
        $this.isUpdate=true;
        clearTimeout($this.timeOutChat);
        $this.timeOutChat=setTimeout('GeneralChat.update()',$this.timeOut);
    }

    this.stopUpdate = function(){
        $this.isUpdate=false;
        clearTimeout($this.timeOutChat);
    }

    this.update = function(){
        if(!$this.isUpdate)return;
        $.post(activePage+'?cmd=general_chat_update', {ajax:1, room:$this.room, last_id:$this.lastId},
                function(res){
                    var data=checkDataAjax(res);
                    if (data){
                        var $data=$(trim(data));
                        if(!$data[0])return;
                        $this.scriptUpdate.append($data.find('script'));
                        var $users=$this.$listUsers.find('div.user_info_bl'), i=0, $user;
                        (function fu(){
                            $user=$users.eq(i);
                            if(!$user[0]){
                                return;
                            }; i++;
                            if(!$data.find('#'+$user[0].id)[0]){
                                $user.stop().animate({height:'toggle', marginTop:0, marginBottom:0},
                                    {duration:300,
                                    step:function(){
                                        $this.updatePlListUsers('relative')
                                    },
                                    complete:function(){
                                        $(this).remove();
                                        $this.updatePlListUsers('relative');
                                    }
                                })
                            }
                            fu();
                        })();

                        $users=$data.find('div.user_info_bl'); i=0;
                        (function fu(){
                            $user=$users.eq(i);
                            if(!$user[0]){
                                return;
                            }; i++;
                            if(!$this.$listUsers.find('#'+$user[0].id)[0]){
                                $user.stop().hide().appendTo($this.$listUsers)
                                .animate({height:'toggle', marginTop:6, marginBottom:0},
                                    {duration:300,
                                    step:function(){
                                        $this.updatePlListUsers('relative')
                                    },
                                    complete:function(){
                                        $this.updatePlListUsers('relative');
                                        fu();
                                    }
                                })
                            }else{
                                fu();
                            }
                        })()

                        var $items=$data.find('div.msg_item'), $item, t=300;
                        i=0;
                        (function fu(){
                            $item=$items.eq(i);
                            if(!$item[0]){
                                return;
                            }; i++;
                            if (!$('#'+$item[0].id)[0] && !$('#general_chat_msg_'+$item.data('send'))[0]){
                                $this.showMsgOne($item,t*=.7,fu);
                            } else {
                                fu()
                            }
                        })()
                    }
        })
        $this.initUpdate();
    }

    this.setLastId = function(lastId){
        $this.lastId = lastId*1;
        console.log('SET LAST ID',$this.lastId);
    }

    this.moduleChangeShow = function(dur){
        var dur=dur||300;
        $this.roomsItem.stop().animate({height:'toggle'},dur);
    }

    this.showSaver = function(){
        $('#loader_layer').show().find('.frame_loader_search_list').toggleClass('to_show to_hide');
    }

    this.hideSaver = function(){
        $('.frame_loader_search_list').oneTransEnd(function(){
            $('#loader_layer').hide();
        }).toggleClass('to_show to_hide');
    }

    this.changeRoom = function(el, id){
        $this.stopUpdate();
        var oldRoom=$this.room*1;
        if(oldRoom==id){
            $this.moduleChangeShow(1);
            return;
        }
        $this.room=id;
        $this.roomsItemShow.html(el.children('span').html());
        $('a.room_item.selected').removeClass('selected');
        el.addClass('selected');
        $this.moduleChangeShow(1);
        $this.msgText.val('').trigger('autosize');
        $this.showSaver();
        $.post(activePage+'?cmd=general_chat_change_room', {ajax:1, room:$this.room, room_old:oldRoom},
                function(res){
                    var data=checkDataAjax(res);
                    if (data){
                        var $data=$(trim(data));
                        $jq('#bl_overview, #bl_overview_list_users').removeClass('animate');
                        $this.scriptUpdate.append($data.find('script'));
                        $this.scrollBoxThumbListUsers.removeClass('animate');
                        $this.$listUsers.html($data.find('.general_chat_list_users').html());
                        $this.updatePlListUsers();

                        $this.scrollBoxThumb.removeClass('animate');
                        $this.$listMessages.html($data.find('.general_chat_list_messages').html());
                        $this.updatePlListMessages();

                        $this.initUpdate();
                    }else{
                        alertServerError();
                    }
                    $this.hideSaver();
        })
        return false;
    }

    this.showMsgOne = function($msg,t,call,scrollToY){
        $jq('#bl_overview').removeClass('animate');
        $msg.addClass('sent');
		$msg.css({opacity:0, display:'none'}).appendTo($this.$listMessages)
        .animate({opacity:1,height:'toggle'},
                 {duration:t||450,
				  specialEasing: {
					opacity: 'linear'
				  },
                  step:function(h,fn){
                    $this.reInitToScrollPanel(scrollToY||0);
                  },
                  complete:function(){
                    $this.reInitToScrollPanel(scrollToY||0);
                    if(typeof call=='function')call();
                    $msg.removeClass('sent');
                    if(!$jq('#bl_overview').find('.msg_item.sent')[0]){
                        $jq('#bl_overview').addClass('animate');
                    }
                  }
        })
	}

    this.hideMsg = function($msg){
        $msg.stop().slideUp({step:function(){
            $this.reInitToScrollPanel();
        },complete:function(){
            $this.reInitToScrollPanel();
        }})
    }

    this.sendMsg = function(){
        var msg=$.trim($this.msgText.val());
        if(!msg)return false;
        $this.msgText.val('').trigger('autosize');

        var send= +new Date,room=$this.room,
            $msg=$this.tmplMsg.clone();
        $msg.attr({id:$msg[0].id+send, 'data-send':send, 'data-room':$this.room}).data({room:$this.room,send:send});
        var $node=$msg.find('.info').html(strToHtml(msg));
        $this.showMsgOne($msg);

        $.post(activePage+'?cmd=general_chat_send', {ajax:1, messages:msg, room:$this.room, send:send},
                function(res){
                    var data=checkDataAjax(res);
                    if (room==$this.room){
                        if (data) {
                            data = trim(data);
                            if (data == 'system_user_banned') {
                                $this.hideMsg($msg);
                                alertCustomRedirect(urlPagesSite.home, l('you_have_been_banned_by_the_admin_please_try_later'), l('ooops'));
                            } else {
                                var $data=$(data).find('.msg_item');
                                if(!$data[0]||!$data[0].id)return;
                                if($('#'+data[0].id,$this.scrollBox)[0])return;
                                $msg.attr({id:$data[0].id});
                                var node=trim($data.find('.info').html());
                                if(node!=$node.html()){
                                    $node.html(node);
                                }
                            }
                        } else {
                            $this.hideMsg($msg);
                            alertServerError();
                        }
                    }
        })
        return false;
    }

    this.prepareViewOneChat = function(transH){
        if (!$jq('.message_field_chat')[0])return;
        transH=transH||false;

        var d=$jq('.message_field_chat')[0].offsetHeight+30+$jq('#bl_cont').offset().top+$jq('.main').scrollTop();
        var hw=$jq('.main').innerHeight(),h=hw-d+15,hwd=hw-43;
        if(h<300){
            h=300;
            $jq('.column_main').css({minHeight:hwd,maxHeight:''});
            $jq('.message_field_chat').css({marginBottom:-12});
        }else{
            $jq('.column_main').css({minHeight:hwd,maxHeight:hwd});
            $jq('.message_field_chat').css({marginBottom:''});
        }

        var fnEnd=function(){
            $jq('#bl_overview, #bl_overview_list_users').removeClass('animate');
            $this.scrollBoxThumb.removeClass('animate');
            $this.scrollBoxThumbListUsers.removeClass('animate');
            $this.reInitToScrollPanel();
            $this.scrollBoxThumbAnimate();
            $this.reInitToScrollPanelListUsers();
            $this.scrollBoxThumbAnimateListUsers();
        }
        if(transH===true){
            $jq('#bl_viewport, #bl_viewport_list_users').stop().animate({height:h},
                {duration:350, step:fnEnd, complete:fnEnd})
        }else{
            $jq('#bl_viewport, #bl_viewport_list_users').stop().height(h);
            fnEnd();
        }
    }

    this.isReInitPanel=false;
	this.reInitToScrollPanel = function(posY, el){
        posY=posY||'bottom';
        el=el||'scrollBoxPl';
        $this[el].update(posY);
    }

    this.reInitToScrollPanelListUsers = function(posY){
        $this.reInitToScrollPanel(posY,'scrollBoxListUsersPl')
    }

    this.scrollBoxThumbAnimate = function(el,d){
        el=el||'scrollBoxThumb';
        d=d||1;
        setTimeout(function(){
            $this[el].addClass('animate');
        },d);
    }

    this.scrollBoxThumbAnimateListUsers = function(d){
        $this.scrollBoxThumbAnimate('scrollBoxThumbListUsers',d||1);
    }

    this.updatePlListUsers = function(pos){
        setTimeout(function(){
            $this.reInitToScrollPanelListUsers(pos);//'top'
            $this.scrollBoxThumbAnimateListUsers(20);
            setTimeout(function(){$jq('#bl_overview_list_users').addClass('animate')},20);
        },5)
    }

    this.updatePlListMessages = function(){
        setTimeout(function(){
            $this.reInitToScrollPanel();
            $this.scrollBoxThumbAnimate(false,20);
            setTimeout(function(){$jq('#bl_overview').addClass('animate')},20);
        },5)
    }

    $(function(){
        $this.scrollBox=$jq('#message_list_scroll');
        $this.scrollBox.tinyscrollbar({wheelSpeed:35,thumbSize:45});
        $this.scrollBoxPl=$this.scrollBox.data('plugin_tinyscrollbar');
        $this.scrollBoxThumb=$jq('#scrollbarY_thumb');

        $this.scrollBoxListUsers=$jq('#message_list_users_scroll');
        $this.scrollBoxListUsers.tinyscrollbar({wheelSpeed:35,thumbSize:45});
        $this.scrollBoxListUsersPl=$this.scrollBoxListUsers.data('plugin_tinyscrollbar');
        $this.scrollBoxThumbListUsers=$jq('#scrollbarY_thumb_list_users');

        $this.tmplMsg=$('#tmpl_message > .msg_item');
        $this.roomsItem=$('#rooms_item');
        $this.roomsItemShow=$('#rooms_item_show');

        $this.$listMessages=$('#general_chat_list_messages');
        $this.$listUsers=$('.general_chat_list_users');

        $this.prepareViewOneChat();
        $win.on('resize', $this.prepareViewOneChat)
        .on('beforeunload', function(){
            setCookie('general_chat_logout', 'logout', {path:'/'});
        });

        $this.msgText=$jq('#msg_text')
			.keydown(doOnEnter($this.sendMsg))
            .autosize({isSetScrollHeight:false,callback:$this.prepareViewOneChat}).focus();
        $jq('#bl_cont').css('opacity',1);
        $jq('#bl_overview, #bl_overview_list_users').addClass('animate');

        /*$('#rooms').hover(
            function(e){if($(e.target).not('.selected')){$this.moduleChangeShow()}},
            function(){if ($('#rooms_item:visible')[0]){$this.moduleChangeShow()}}
        )*/
        $('#rooms').click(function(e){
            var $targ=$(e.target);
            console.log($targ.not('.selected'),!$targ.is('.room_item'),$targ.closest('.room_item')[0]);
            if(!$targ.is('.room_item')&&!$targ.closest('.room_item')[0]){
                $this.moduleChangeShow();
            }
        }).find('#rooms_item').mouseleave(function(){
            if ($('#rooms_item:visible')[0]){$this.moduleChangeShow()}
        })
        $doc.on('click', function(e){
            var $targ=$(e.target),$roomsDrop=$('#rooms_item');
            if($targ.is('#rooms')||$targ.closest('#rooms')[0])return;
            if($roomsDrop.is(':animated')||!$roomsDrop.is(':visible'))return;
            $this.moduleChangeShow();
        })

        $this.scriptUpdate=$('#general_chat_ajax');
        $this.initUpdate();
    })

    return this;
}