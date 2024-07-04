var CCityStreetChat = function() {

    var $this=this;
    this.langParts = {};
    this.dur=300;

    this.invite = function($btn, onlineUser){
        if($btn.is('.disabled'))return;
        if($btn.data('tooltip'))$btn.blur();

		if(!userAllowedFeature['3d_city']){
            redirectToUpgrade();
            return;
        }

        var uid=$btn.data('uid');
        onlineUser=onlineUser||clProfile.getRealStatusOnline();
        if(!onlineUser){
            alertCustom(l('the_user_is_offline_now_please_try_later'));
            clProfile.hideMoreMenu();
            return;
        }

        addChildrenLoader($btn.addClass('disabled'));

        $.post(url_server+'?cmd=city_street_chat_invite',{user_id:uid},function(res){
            var data=checkDataAjax(res);
            if(data){
                if(data=='upgrade') {
                    redirectToUpgrade();
                }else{
                    var msg,title;
                    if(data===true){
                        msg=l('please_wait_for_confirmation');
                        title=l('invitation_sent');
                    } else {
                        msg=data;
                        title=l('alert_html_alert');
                    }
                    alertCustom(msg, title);
                }
            }else{
                alertServerError();
            }
            removeChildrenLoader($btn.removeClass('disabled'));
        })
    }

    this.request = function(data){
        try{
            if(data.action=='request'){
                showNotifMediaChat('street',data,
                                   function(){$this.start(data)},
                                   function(){$this.reject(data.user_id)});
            }else if(data.action=='reject'){
                alertCustom(l('refused_street_chat'),l('attention'));
            }else if(data.action=='start'){
                $this.toCity(data.url);
            }else{return false}
        }catch(e){return false}
    }

    this.reject = function(uid){
        resetHashMedia('street');
        $.post(url_server+'?cmd=city_street_chat_reject',{user_id:uid},function(res){
            if(checkDataAjax(res)){}
        })
    }

    this.start = function(data){
        resetHashMedia('street');
        closeAlert();
        if(false && !userAllowedFeature['3d_city']){
            redirectToUpgrade();
            return;
        }
        var uid=data.user_id;
        $.post(url_server+'?cmd=city_street_chat_start',{user_id:uid, data:data.data},function(res){
            var data=checkDataAjax(res);
            if(data){
                $this.toCity(data.url);
            }
        })
    }

    this.isLoadCity = function(){
        return typeof city == 'object' && city.isSceneLoaded;
    }

    this.toCity = function(url){
        redirectUrl(url);
    }

    $(function(){
    })

    return this;
}