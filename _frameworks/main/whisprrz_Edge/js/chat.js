var CChat = function(type) {

    var $this=this;

    this.type=type;
    this.typeKey=type+'chat';

    this.checkInvite = function($link, onlineUser, groupId, isPage){
        if($link.is('.disabled')) return;
        if($link.data('tooltip'))$link.blur();

        if(!userAllowedFeature[$this.typeKey]){
            redirectToUpgrade();
            return;
        }

        var uid=$link.data('uid');

        groupId=defaultFunctionParamValue(groupId, clGroups.getId());
        isPage=defaultFunctionParamValue(isPage, clGroups.isPage());
        onlineUser=onlineUser||clProfile.getRealStatusOnline();
        if(!onlineUser){
            var msg=l('the_user_is_offline_now_please_try_later');
            if (groupId) {
                msg=isPage?l('the_page_is_offline_now_please_try_later'):l('the_group_is_offline_now_please_try_later');
            }
            alertCustom(msg);
            clProfile.hideMoreMenu();
            return;
        }

        if(!checkSupportWebrtc(type)){
            return;
        }

        addChildrenLoader($link.addClass('disabled'));

        var price=$this.price;
        if(price>0){
            $.post(url_ajax+'?cmd=get_available_credits',{}, function(res){
                var data=checkDataAjax(res);
                if (data){
                    var balance=data*1;
                    clProfile.hideMoreMenu();
                    if(balance<price){
                        var msg=l('you_have_no_enough_credits');
                        if(!isInAppPurchaseEnabled){
                            msg += '<br>'+l('buy_credits');
                            alertCustom(msg)
                        }else{
                            confirmCustom(msg,
                            function(){
								setTimeout(function(){
									clUpgrade.requestIncreasePopularity('pp_payment',$this.type+'_chat',uid,groupId);
								},350)
                            },l('alert_html_alert'),l('alert_html_ok'),l('buy_credits'))
                        }
                    } else {
						clUpgrade.requestIncreasePopularity('pp_payment',$this.type+'_chat',uid,groupId)
                        /*var msg=l('service_costs_credits_edge').replace('{credit}',balance).replace('{price}',price);
                        confirmCustom(msg, function(){
							addChildrenLoader($link.addClass('disabled'));
                            $this.invite(uid,$link,groupId);
                        },l('service_costs_credits_title_edge'))*/
                    }
                }else{
                    alertServerError();
                }
                removeChildrenLoader($link.removeClass('disabled'));
            })
        } else {
            $this.invite(uid,$link,groupId)
        }
    }

	this.alertCustom = function(d,msg,title){
		title=title||'';
		if (d) {
			setTimeout(function(){
				alertCustom(msg,title);
			},d)
		} else {
			alertCustom(msg,title);
		}
	}

	this.alertCustomIcon = function(d,msg,title,icon){
		title=title||'';
		if (d) {
			setTimeout(function(){
				alertCustomIcon(msg,title,icon);
			},d)
		} else {
			alertCustomIcon(msg,title,icon);
		}
	}

    this.invite = function(uid,$link,groupId,call){
        groupId=groupId||0;
        var data={type:$this.type,user_id:uid};
        if(groupId){
            data['group_id'] = groupId;
        }
		var d=0;
        $.post(url_server+'?cmd=chat_invite',data,function(res){
			if (typeof call=='function') {
				call();
				d=300;
			}
            var data=checkDataAjax(res);
            if(data){
                if (data!='upgrade') {
                    clProfile.hideMoreMenu();
                    removeChildrenLoader($link.removeClass('disabled'));
                }
                if (data=='upgrade') {
                    redirectToUpgrade();
                }else if(data===true){
                    $this.alertCustomIcon(d,l('please_wait_for_user'),l('invitation_sent'),$this.type+'_chat');
                }else{
                    $this.alertCustom(d,data);
                }
            }else{
                removeChildrenLoader($link.removeClass('disabled'));
                alertServerError();
            }
        })
    }

    this.prepareType = function(data){
        var type=$this.type;
        if(data['group_id']){
            type += '_' + (data['group_page'] ? 'page' : 'group');
        }
        return type;
    }

    this.request = function(data){
        try{
            var type=$this.prepareType(data), groupId=data.group_id ? data.group_id : 0;
            if(data.action=='request'){
                var fnTalk=function($link){
                        $link.addChildrenLoader();
                        $this.talk(data)
                    },
                    fnReject=function(){
                        $this.reject(data.user_id, groupId);
                    };
                var support=supportWebrtc();
                if(support=='ssl'){
                    showNotifMediaChat($this.type,data,fnReject,'',l('your_browser_needs_ssl_certificate_to_run_'+$this.type+'_chat'),l('alert_html_ok'),true);
                } else if(support){
                    showNotifMediaChat($this.type,data,fnTalk,fnReject);
                }else{
                    showNotifMediaChat($this.type,data,fnReject,'',l(type+'_chat_from_user_no_webrtc_support'),l('alert_html_ok'),true);
                }
            }else if(data.action=='reject'){
                alertCustom(l('refused_'+type+'chat'));
            }else if(data.action=='start_talk'){
                var uid = data.user_id;
                var price=chatsPrice[$this.type];
                if(price>0){
                    $.post(url_server,{cmd:'chat_paid',type:$this.type},function(res){
                        var data=checkDataAjax(res);
                        if(data && data>=0){
                            $this.startTalk(uid,groupId);
                        }
                    })
                } else {
                    $this.startTalk(uid,groupId);
                }

            }else{return false}
        }catch(e){return false}
    }

    this.reject = function(uid,groupId){
        resetHashMedia($this.type);
        groupId=groupId||0;
        $.post(url_server+'?cmd=chat_reject',{type:$this.type,user_id:uid,group_id:groupId},function(res){
            if(checkDataAjax(res)){}
        });
    }

    this.talk = function(data){
        resetHashMedia($this.type);
        if(data.request_uri&&false){
            redirectToUpgrade('request_uri='+data.request_uri);
            return;
        }
        var uid=data.user_id, groupId=data.group_id ? data.group_id : 0;
        $.post(url_server+'?cmd=chat_talk',{type:$this.type,user_id:uid,group_id:groupId},function(res){
            if(checkDataAjax(res)){
                $this.startTalk(uid,groupId)
            }
        })
    }

    this.startTalk = function(uid,groupId){
        groupId=groupId||0;
        resetHashMedia($this.type);
        redirectUrl(urlMain+$this.type+'chat.php?id='+uid+'&group_id='+groupId);
    }

    $(function(){

    })

    return this;
}