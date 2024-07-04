var CMobileChat = function(msgArea, contentArea, type, langParts, chatUserId) {

    var $this = this;
    this.updaterTimer = false;
    this.autoUpdateTimeout = 2000;
    this.msgArea = msgArea;
    this.contentArea = contentArea;
    this.openTr = new Array();

    if(type == 'chat') {
        urlSend = 'chat.php?';
        urlUpdate = 'chat_ajax.php?';
    } else {
        urlSend = 'profile_view_chat.php?user_id=' + chatUserId + '&';
        urlUpdate = 'profile_view_chat_ajax.php?user_id=' + chatUserId + '&';
    }

    this.urlSend = urlSend;
    this.urlUpdate = urlUpdate;


    this.send = function() {
        msg = trim($(this.msgArea).val());
        url = this.urlSend + 'dummy=' + new Date().getTime();
        if (msg == '') {
            alert(langParts['msg_empty']);
        } else {
            $.post(url,{'ajax':1, 'dummy': new Date().getTime(), 'message': msg},
                function(data) {
                    if(data=='redirect'){window.location.href='chat.php'
                    }else{$this.updater()}
                }
            );
            $(this.msgArea).val('');
        }
    }

    this.updater = function() {
        clearInterval(this.updaterTimer);
        url = this.urlUpdate + 'dummy=' + new Date().getTime()+'&open='+this.openTr.join(',');
        $.get(
                url,
                function(data) {
                    //if(trim(data) == 'you_are_blocked') {
                        //data = MSG_YOU_ARE_IN_BLOCK_LIST;
                    //}
                    if(trim(data) == 'ban') {
                        location.reload();
                    }
                    $($this.contentArea).html(data);
                    $this.updaterInit();
                }
        );
    }

    this.updaterInit = function() {
        clearTimeout(this.updaterTimer);
        this.updaterTimer = setTimeout(
                function() {
                    $this.updater();
                },
                this.autoUpdateTimeout);
    }

    $(this.msgArea).keypress(function(e) {
        if (e.which == 13 && !e.shiftKey) {
            $this.send();
            return false;
        }
    });

    this.showOriginalMsg = function(e,id)
    {
        $("#tr_"+id).toggle();
        var ind=this.openTr.indexOf(id);
        if(ind==-1){
           this.openTr[this.openTr.length] = id;
        } else {
           this.openTr.splice(ind,1);
        }
        console.log(this.openTr);
    }


    return this;
}

function showImageIm(img) {
    var $img=$(img).addClass('to_show');
}