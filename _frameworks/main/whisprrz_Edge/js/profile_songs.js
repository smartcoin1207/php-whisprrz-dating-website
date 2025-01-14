var CProfileSongs = function(guid,uid) {

    var $this=this;
    this.guid=guid*1;
    this.uid=uid*1;
    this.dur=500;

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
           //console.log(key, data[key]);
        }
    }

    this.hideMoreMenuAction = function(){
        $('.list_song_menu.in').collapse('hide');
    }

    this.isActivePlayer = function(){
        if($('#player_song').is('.to_show') && songPlayer!== null
                && songPlayer.isActive()) {
            return true;
        }
        return false;
    }

    this.changeControlsPlayer = function(){
        if($this.isActivePlayer()) {
            songPlayer.changeControls();
        }
    }

    this.confirmDeleteFromList = function(id){
        //$this.hideMoreMenu();
        confirmCustom(l('this_action_can_not_be_undone'),function(){
            $this.songDeleteList(id)
        }, l('confirm_delete_song'))
    }

    this.songDeleteList = function(id){
        var $layer=$('#list_song_layer_action_'+id);
        if($layer.is('.to_show'))return;
        $layer.addClass('to_show').addChildrenLoader();
        $this.hideMoreMenuAction();

        $this.songDelete(id);
    }

    this.updaterCounterPage = function(title,count){
        if (title) {
            $jq('#left_column_songs_count').html(title);
            $jq('#menu_inner_songs_edge').find('.number').text(count);
        }
    }

    this.updatePageData = function(id, count_title, count) {
        count *=1;

        var $listSong=$('.list_songs_item_photo_'+id);
        if ($listSong[0]) {
            var page=clPages.page,noReplaceHistory=true;
            if($('.songs_list_user .cham-post-image').length==1){
                page--;
                if(page<=0)page=1;
                noReplaceHistory=false;
            }
            clPages.pageReload(page,false,true,noReplaceHistory);
        }
        if (count) {
            var $li=$('.column_songs_item_'+id);
            if($li[0]){
                $li.oneTransEnd(function(){
                    $(this).remove()
                }).addClass('to_hide_bl', 0);
            }
            $this.updaterCounterPage(count_title, count);
        } else {
            var $bl=$('#left_column_songs');
            if($bl[0]){
                $bl.slideUp(300,function(){
                    $bl.find('li').remove();
                    $this.updaterCounterPage(count_title, count);
                })
            } else {
                $this.updaterCounterPage(count_title, count);
            }
        }
    }

    this.songDelete = function(id) {
        $.ajax({type:'POST',
                url:urlMain+'music_song_delete_ajax.php?ajax=1',
                data:{song_id:id, uid:$this.uid},
                beforeSend: function(){

                },
                success: function(res){

                    var data=checkDataAjax(res);
                    if (data!==false){

                        if($this.isActivePlayer()) {
                            var isClose=false;
                            if(id == songPlayer.getCurrentTrack()){
                                if (songPlayer.lengthPlayList() == 1) {//Close
                                    songPlayer.close();
                                    isClose=true;
                                } else {
                                    songPlayer.next();
                                }
                            }
                            delete playListSongs[id];
                            if (!isClose) {
                                $this.changeControlsPlayer();
                            }
                        }

                        /* Events delete */
                        /*var evPid=$this.getVideoId(pid),
                        sel1='.events_notification_item[data-type="photo_comments_likes"][data-event-id="'+evPid+'"]',
                        sel2='.events_notification_item[data-type="photo_comments"][data-event-id="'+evPid+'"]';
                        if ($this.isVideo(pid)) {
                            sel1='.events_notification_item[data-type="vids_comments_likes"][data-event-id="'+evPid+'"]',
                            sel2='.events_notification_item[data-type="vids_comment"][data-event-id="'+evPid+'"]';
                        }
                        $(sel1).remove();
                        $(sel2).remove();
                        clEvents.reInitToScroll();
                        /* Events delete */
                        $this.updatePageData(id, data.count_title, data.count);
                    }else{
                        alertServerError(true);
                        $('#list_song_layer_action_'+id).removeClass('to_show').removeChildrenLoader();
                    }
                }
        })
    }

    this.increasePlays = function(id) {
        id *=1;
        if (!id) {
            return;
        }
        $.post(url_ajax+'?cmd=song_increase_plays',{song_id:id},function(res){
            var data=checkDataAjax(res);
            if (data!==false){}
        })
    }

    $(function(){

    })

    return this;
}