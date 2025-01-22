var CProfilePhoto = function(guid,uid, nsc_couple_id=0) {

    var $this=this;
    this.guid=guid*1;
    this.uid=uid*1;
    this.nsc_couple_id=nsc_couple_id*1;
    this.dur=500;
    this.isImageEditorEnabled = true;

    this.uploadFileData = {public:{},private:{},video:{},photo:{},song:{}};

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
           //console.log(key, data[key]);
        }
    }

    this.clearUploadFileData = function(type){
        for(var id in $this.uploadFileData[type]) {
            delete $this.uploadFileData[type][id];
        }
    }

    this.visDropZone = function(show, type, call){
        show=show||'show';
        if(show=='show'){
            setPushStateHistory('upload_file');
        }
        $this.$ppUpload[type]['pp']
        .one('hide.bs.modal',function(){
            $jq('body').removeClass('upload_file_'+type);
        }).one('hidden.bs.modal',function(){
            checkOpenModal();
            if(typeof call=='function')call();
        }).one('show.bs.modal',function(){
            $jq('body').addClass('upload_file_'+type);
        }).modal(show);
    }

    this.closeDropZone = function(type){
        $this.$ppUpload[type]['upload_count'] = 0;
        $this.visDropZone('hide', type, function(){$this.removeUploadFile(type)})
    }

    this.closeDropZonePopup = function(type){
        if(!$jq('body').is('.upload_file_'+type))return;
        $this.$ppUpload[type]['upload_count'] = 0;
        if(!backStateHistory()){
            $this.closeDropZone(type);
        }
    }

    this.getEHPType = function () {
        eventEHPPages = ['events_event_show.php', 'events_event_add_photos.php' ,'event_wall.php', 'events_guest_users.php', 'event_photo_list.php',  'event_mail.php', 'select_event_users.php', 'event_photo_list.php'];
        hotdateEHPPages = ['hotdates_hotdate_show.php', 'hotdates_hotdate_add_photos.php' ,'hotdate_wall.php', 'hotdates_guest_users.php', 'hotdate_photo_list.php',  'hotdate_mail.php', 'select_hotdate_users.php', 'hotdate_photo_list.php'];
        partyhouEHPPages = ['partyhouz_partyhou_show.php', 'partyhouz_partyhou_add_photos.php', 'partyhou_wall.php', 'partyhou_guest_users.php', 'partyhou_photo_list.php',  'partyhou_mail.php', 'select_partyhou_users.php', 'partyhou_photo_list.php'];

        const page_url = location.pathname;

        ehp_type = "";

        if(eventEHPPages.some(item => page_url.includes(item))) {
            ehp_type = "event";
        } else if(hotdateEHPPages.some(item => page_url.includes(item))) {
            ehp_type = "hotdate";

        } else if(partyhouEHPPages.some(item => page_url.includes(item))) {
            ehp_type = "partyhou";

        }

        return ehp_type;
    }

    this.getPhotoCmd = function() {
        photoCmd = "";
        ehp_type = $this.getEHPType();

        if(ehp_type == 'event') {
            photoCmd = "&photo_cmd=event_photos";
        } else if(ehp_type == 'hotdate') {
            photoCmd = "&photo_cmd=hotdate_photos";
        } else if(ehp_type == 'partyhou') {
            photoCmd = "&photo_cmd=partyhou_photos";
        }

        return photoCmd;
    }

    this.getPhotoCmdString = function () {
        photoCmd = "";
        ehp_type = $this.getEHPType();

        if(ehp_type == 'event') {
            photoCmd = "event_photos";
        } else if(ehp_type == 'hotdate') {
            photoCmd = "hotdate_photos";
        } else if(ehp_type == 'partyhou') {
            photoCmd = "partyhou_photos";
        }

        return photoCmd;
    }

    this.isEHP = function () {
        isEhp = false;
        ehp_type = $this.getEHPType();

        if(ehp_type == 'event') {
            isEhp = true;
        } else if(ehp_type == 'hotdate') {
            isEhp = true;
        } else if(ehp_type == 'partyhou') {
            isEhp = true;
        }

        return isEhp;
    }

    this.publishFile = function(type) {
        var showError=function(){
            alertServerError(true);
            $this.$ppUpload[type]['btn_cancel'].prop('disabled', false);
            $this.$ppUpload[type]['btn_publish'].prop('disabled', false).removeChildrenLoader();
        }
        $this.$ppUpload[type]['btn_cancel'].prop('disabled', true);
        $this.$ppUpload[type]['btn_publish'].prop('disabled', true).addChildrenLoader();

        var paramType=type,sel='videos';
        if (type == 'photo') {
            paramType='public';
            sel='photos';
        } else if (type == 'song') {
            sel='songs';
        }
        //var  requestuploadFileData=Object.assign({}, $this.uploadFileData[type]);
        for(var id in $this.uploadFileData[type]) {
            var $desc=$('#dz_item_desc_'+id);
            $this.uploadFileData[type][id]['desc']=$desc[0]?$desc.val():'';
            /*if (type == 'photo'){
                var photo=$this.uploadFileData[type][id];
                checkImageFaceDetection(photo.src_bm, photo.id);
                delete $this.uploadFileData[type][id]['src_bm'];
            }*/
            if (type == 'song') {
                var $cover=$('#dz_item_cover_image_'+id),
                    cover='';
                if ($cover[0] && $cover.data('image')) {
                    cover=$cover.data('image');
                }
                $this.uploadFileData[type][id]['cover']=cover;
            }
        }

        var groupId=$this.loadGroup ? $this.groupId : 0,
            toMyMedia=false;
        if(!$this.loadGroup && $this.groupId){
            toMyMedia=true;
        }
        var photoDefaultId=0;
        if (type == 'photo') {
            photoDefaultId=$this.getPhotoDefaultId(groupId);
        }
        var data;
        var photo_upload_offset = '';

        if (type == 'song') {
            data={cmd:'publish_songs',
                  songs:$this.uploadFileData[type],
                  group_id:groupId}
        } else {
            photoCmdString = $this.getPhotoCmdString();
            ehp_type = $this.getEHPType();
            ehpid = '';
            if(ehp_type == 'event') {
                url = location.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const eventId = urlParams.get('event_id');
                ehpid = eventId;
            } else if(ehp_type == 'hotdate') {
                url = location.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const hotdateId = urlParams.get('hotdate_id');
                ehpid = hotdateId;
            } else if(ehp_type == 'partyhou') {
                url = location.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const partyhouId = urlParams.get('partyhou_id');
                ehpid = partyhouId;
            } else if(!groupId || groupId == "0") {
                const selected_photo_offset_el = document.getElementById("photo_upload_offset_select");
                const selectedValue = selected_photo_offset_el.value;
              
                photo_upload_offset = selectedValue;
            }

            data={cmd:'publish_photos_gallery',type:paramType,
                  photos:$this.uploadFileData[type],
                  group_id:groupId,
                  photo_cmd: photoCmdString,
                  activity_id: ehpid,
                  photo_upload_offset: photo_upload_offset,
              };
            
            if(is_nsc_couple_page == 1) {
                data['is_nsc_couple_page'] = 1;
            }
        }

        // data[]
        $.ajax({
            url: url_ajax,
            type: 'POST',
            data: data,
            beforeSend: function () {
            },
            success: function (res) {
                $this.$ppUpload[type]['upload_count'] = 0;
                var data = checkDataAjax(res);
                if (data !== false) {
                    $this.clearUploadFileData(type);

                    $this.$ppUpload[type]['pp'].one('hidden.bs.modal', function () {
                        $this.$ppUpload[type]['btn_cancel'].prop('disabled', false);
                        $this.$ppUpload[type]['btn_publish'].removeChildrenLoader();
                        $this.removeUploadFile(type);
                    }).modal('hide');

                    if (!clPages.myPageReload(sel, false, toMyMedia, groupId, photo_upload_offset)) {
                        wallUpdater();

                        if (type != 'song') {
                            updateGridPhotoFromPublish();
                        }
                        $this.updaterCounterPage(sel, data.data.count_title, data.data.count);
                        if (type == 'photo') {
                            $this.isImageEditorEnabled = data.data.isImageEditorEnabled;
                            $this.replacePhotoDefaultCheck(data.data.photo_default, photoDefaultId, data);
                        }
                        delete data.data;
                    }
                } else {
                    showError()
                }
            },
            error: function () {
                showError()
            },
            complete: function () {
            }
        })
    }
    /* Divyesh - Added on 11-04-2024 */
    this.makePrivateAssets = function(type) {
        var showError=function(){
            alertServerError(true);
            $this.$ppUpload[type]['btn_cancel'].prop('disabled', false);
            $this.$ppUpload[type]['btn_make_private'].prop('disabled', false).removeChildrenLoader();
        }
        $this.$ppUpload[type]['btn_cancel'].prop('disabled', true);
        $this.$ppUpload[type]['btn_make_private'].prop('disabled', true).addChildrenLoader();

        var paramType=type,sel='videos';
        if (type == 'photo') {
            paramType='public';
            sel='photos';
        } else if (type == 'song') {
            sel='songs';
        }
        //var  requestuploadFileData=Object.assign({}, $this.uploadFileData[type]);
        for(var id in $this.uploadFileData[type]) {
            var $desc=$('#dz_item_desc_'+id);
            $this.uploadFileData[type][id]['desc']=$desc[0]?$desc.val():'';
            /*if (type == 'photo'){
                var photo=$this.uploadFileData[type][id];
                checkImageFaceDetection(photo.src_bm, photo.id);
                delete $this.uploadFileData[type][id]['src_bm'];
            }*/
            if (type == 'song') {
                var $cover=$('#dz_item_cover_image_'+id),
                    cover='';
                if ($cover[0] && $cover.data('image')) {
                    cover=$cover.data('image');
                }
                $this.uploadFileData[type][id]['cover']=cover;
            }
        }

        var groupId=$this.loadGroup ? $this.groupId : 0,
            toMyMedia=false;
        if(!$this.loadGroup && $this.groupId){
            toMyMedia=true;
        }
        var photoDefaultId=0;
        if (type == 'photo') {
            photoDefaultId=$this.getPhotoDefaultId(groupId);
        }
        var data;
        if (type == 'song') {
            data={cmd:'publish_songs',
                  songs:$this.uploadFileData[type],
                  group_id:groupId}
        } else {
            photoCmdString = $this.getPhotoCmdString();
            ehp_type = $this.getEHPType();
            ehpid = '';

            if(ehp_type == 'event') {
                url = location.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const eventId = urlParams.get('event_id');
                ehpid = eventId;
            } else if(ehp_type == 'hotdate') {
                url = location.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const hotdateId = urlParams.get('hotdate_id');
                ehpid = hotdateId;
            } else if(ehp_type == 'partyhou') {
                url = location.href;
                const urlParams = new URLSearchParams(new URL(url).search);
                const partyhouId = urlParams.get('partyhou_id');
                ehpid = partyhouId;
            }

            data={cmd:'publish_make_private',type:paramType,
                  photos:$this.uploadFileData[type],
                  group_id:groupId,
                  photo_cmd: photoCmdString,
                  activity_id: ehpid
              };
        }

        // data[]
        $.ajax({url:url_ajax,
            type:'POST',
            data:data,
            beforeSend: function(){
            },
            success: function(res){
                $this.$ppUpload[type]['upload_count']=0;
                var data=checkDataAjax(res);
                if (data!==false){
                    $this.clearUploadFileData(type);
                    /*for(var id in $this.uploadFileData[type]) {
                        delete $this.uploadFileData[type][id];
                    }*/
                    //console.log(data);
                    $this.$ppUpload[type]['pp'].one('hidden.bs.modal', function(){
                        $this.$ppUpload[type]['btn_cancel'].prop('disabled', false);
                        $this.$ppUpload[type]['btn_make_private'].removeChildrenLoader();
                        $this.removeUploadFile(type);
                    }).modal('hide');

                    if(!clPages.myPageReload(sel,false,toMyMedia,groupId)){
                        wallUpdater();

                        if (type != 'song') {
                            updateGridPhotoFromPublish();
                        }
                        $this.updaterCounterPage(sel,data.data.count_title, data.data.count);
                        if (type == 'photo') {
                            $this.isImageEditorEnabled = data.data.isImageEditorEnabled;
                            $this.replacePhotoDefaultCheck(data.data.photo_default, photoDefaultId, data);
                        }
                        delete data.data;
                    }

                    /*type == 'photo' && setTimeout(function(){
                        for(var id in requestuploadFileData) {
                            var photo=requestuploadFileData[id];
                            checkImageFaceDetection(photo.src_bm, photo.id);
                        }
                    },1)*/
                }else{
                    showError()
                }
            },
            error: function(){
                showError()
            },
            complete: function(){
            }
        });
    }
    /* Divyesh - Added on 11-04-2024 */

    this.enabledPublish = function(type){
        var $pp=$this.$ppUpload[type]['pp'],
            n=$('.'+$this.$ppUpload[type]['sel_item']+'',$pp).not('.dz-error').length,
            nC=$('.'+$this.$ppUpload[type]['sel_item']+'.dz-complete',$pp).not('.dz-error').length;

        $this.$ppUpload[type]['btn_publish'].prop('disabled', (!n || n!=nC));
        $this.$ppUpload[type]['btn_make_private'].prop('disabled', (!n || n!=nC)); /* Divyesh - Added on 11-04-2024 */
        $this.$ppUpload[type]['btn_cancel'].text(n>0?l('cancel'):l('close_window'));
    }

    this.removeUploadFile = function(type){
        $this.$ppUpload[type]['dropzone'].removeAllFiles(true);
        $this.clearUploadFileData(type);
        $this.reInitScroll(type, 0);
    }

    this.reInitScroll = function(type, posY){
        posY=defaultFunctionParamValue(posY, 'bottom');
        $this.$ppUpload[type]['pl_scroll'].update(posY);
    }

    /* Upload cover */
    this.hideUploadCoverError = function(e, $inp){
        if (!$inp[0]) return;
        var $bl=$inp.closest('.dz-cover-song');
        if (!$(e.target).is('input[type="reset"]')) {
            hideError($bl);
            $bl.find('input[type="reset"]').click();
        }
    }


    this.uploadCover = function($inp){
        var $bl=$inp.closest('.dz-cover-song'),
            $btn=$bl.find('button'),
            file = $inp,
            fileName = 'song_image',
            url = url_ajax +
                  '?cmd=upload_temp_song_image&id='+$inp.data('id'),
            formData = new FormData(),
            error = '',
            acceptTypes='image/jpeg,image/png,image/gif';

        $.each(file[0].files, function(i, file){
            var tpp = file.type;
            if (acceptTypes.indexOf(tpp) === -1) {
                error = l('accept_file_types');
                return false;
            } else if (file.size > (clProfilePhoto.maxphotoFileSize)) {
                error = clProfilePhoto.maxphotoFileSizeLimit;
                return false;
            }
            formData.append(fileName, file);
        });

        /*var showError = function(msg){
            var $preview=$inp.closest('.dz-file-preview');
            $preview.find('.dz-error-message > .dz-error-wrap > span').text(msg);
            $preview.addClass('dz-error');
        }*/

        if (error) {
            //showError(error);
            showError($bl, error, false, true);
            return false;
        }

        $btn.prop('disabled', true).addChildrenLoader();
        $inp.prop('disabled', true);

        $this.$ppUpload['song']['upload_count']++;
        //$inp.data('image',0);

        var fnRes=function(){
            $btn.prop('disabled', false).removeChildrenLoader();
            $inp.prop('disabled', false);
            $this.$ppUpload['song']['upload_count']--;
        }

        //return false;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if(xhr.status == 200) {
                    var data=xhr.responseText,
                        res=jQuery.parseJSON(data);
                    if (typeof res == 'object' && typeof res.data == 'object') {
                        res=res.data;
                        if (res.error) {
                            showError($bl, res.error, false, true);
                            fnRes();
                        } else {
                            //$this.$upPhotoImgId.val(res.id);
                            var v=+new Date,
                                url=urlFiles+res.src+'?v='+v;
                            img=new Image();
                            img.onload = function(){
                                $bl.closest('.pp_song_upload_item').find('.dz-image > img')[0].src=url;
                                fnRes();
                            }
                            img.src=url;
                            $inp.data('image',1);
                            $bl.find('.btn_cover_music_title').text(l('change_cover'));
                        }
                    }else{
                        showError($bl, l('photo_file_upload_failed'), false, true);
                        fnRes();
                    }
                }
            }
        };
        xhr.send(formData);
        return false;
    }
    /* Upload cover */
    /* Init editor image */
    this.openEditorImage = function(pid){
        imageEditorInfo = {};
        var info=false;
        if ($this.visibleMediaData[pid]!=undefined) {
            info=$this.visibleMediaData[pid];
        }
        if (!info) {
            return false;
        }

        var $layer=$('#list_image_layer_action_'+pid);
        if($layer.is('.to_show'))return;

        openEditorImage(pid, 0, 'edit_list_image', []);
    }

    this.openEditorProfilePhoto = function($el){
        var pid=$el.data('photoId')*1;
        if(!pid)return;
        openEditorImage(pid, $this.guid, 'edit_list_image', [], $el);
        //wall_photo_413860
    }

    this.openEditorImageGallery = function(typeDefaultEffect){
        imageEditorInfo = {};
        var info=false, pid=$this.curPid;
        if ($this.visibleMediaData[pid]!=undefined) {
            info=$this.visibleMediaData[pid];
        } else if ($this.galleryMediaData[pid] != undefined){
            // Fix - default profile photo not available in visibleMediaData
            info = $this.visibleMediaData[pid] = $this.galleryMediaData[pid];
        }
        if (!info) {
            console.log('openEditorImageGallery no info');
            return false;
        }
        if (info['user_id']!=$this.guid) {
            return false;
        }
        openEditorImage(pid, 0, 'edit_gallery', info, $this.$el['btnImageEdit'], typeDefaultEffect);
    }

    this.restoreImage = function($btn){
        var fn=function(){
        imageEditorInfo = {};
        var info=false, pid=$this.curPid;
        if ($this.visibleMediaData[pid]!=undefined) {
            info=$this.visibleMediaData[pid];
        } else if ($this.galleryMediaData[pid] != undefined){
            // Fix - default profile photo not available in visibleMediaData
            info = $this.visibleMediaData[pid] = $this.galleryMediaData[pid];
        }
        if (!info) {
            return false;
        }
        if (info['user_id']!=$this.guid) {
            return false;
        }
        $btn.addChildrenLoader();

        photoCmd = "";
        photoCmd = $this.getPhotoCmd();

        $.post(url_ajax+'?cmd=photo_restore_image' + photoCmd,
               {photo_id:pid},function(res){
            var data=checkDataAjax(res);
            if(data){
                if (pid == $this.curPid) {
                    $this.controlRestoreImage(0);
                }
                $this.updateGalleryImageAfterEdit(pid, [], 0);
                faceDetecionCleanRequest(pid);
            }else{
                alertServerError(true);
            }
            setTimeout(function(){$btn.removeChildrenLoader()},300);
        })
        }

        confirmCustom(l('all_changes_will_be_lost'), fn)
    }
    /* Init editor image */

    this.$ppUpload={public:{},private:{},video:{},photo:{},song:{}};
    this.initUploadFile = function(type){
        var sel='pp_'+type+'_upload', selItem=sel+'_item', $pp=$('#'+sel);

        $this.$ppUpload[type]['pp']=$pp;
        //$this.$ppUpload[type]['pp_modal']=$pp.find('.modal-dialog');
        //$this.$ppUpload[type]['w'] = 0;
        $this.$ppUpload[type]['pl_scroll']=$pp.find('.scrollbarY').tinyscrollbar({wheelSpeed:30,thumbSize:45}).data('plugin_tinyscrollbar');

        $this.$ppUpload[type]['upload_count']=0;
        $this.$ppUpload[type]['sel_item']=selItem;

        $this.$ppUpload[type]['btn_cancel']=$('button.btn_close', $pp).click(function(){
            //var $remove=$('[data-dz-remove]',$pp);
            if($this.$ppUpload[type]['upload_count']){
                confirmCustom(l('you_have_already_uploaded_several_'+type+'s'), function(){
                    //$this.removeUploadFile(type);
                    setTimeout(function(){
                        $this.closeDropZonePopup(type);
                    },250)
                })
            }else{
                $this.closeDropZonePopup(type)
            }
        });
        $this.$ppUpload[type]['btn_publish']=$('button.btn_publish', $pp).click(function(){
            $this.publishFile(type);
        });

        /* Divyesh - Added on 11-04-2024 */
        $this.$ppUpload[type]['btn_make_private']=$('button.btn_make_private', $pp).click(function(){
            $this.makePrivateAssets(type);
        });
        /* Divyesh - Added on 11-04-2024 */


        var reduceUploadCount = function(){
            if($this.$ppUpload[type]['upload_count']>0)$this.$ppUpload[type]['upload_count']--;
        }
        var showError = function(file,msg){
            if (file.previewElement) {
                var msgError = {
                    max_file_size : $this['max'+type+'FileSizeLimit'],
                    accept_file_types : l('accept_file_types')
                }
                if(file.size > ($this['max'+type+'Size']*1024*1024)){
                    debugLog('Error upload max size',file.size);
                    msg=msgError['max_file_size'];
                }
                if(msg&&msgError[msg])msg=msgError[msg];
                msg=msg||l('photo_file_upload_failed');

                var $preview=$(file.previewElement);
                $preview.find('.dz-error-message > .dz-error-wrap > span').text(msg);
                $preview.addClass('dz-error');
            }
            reduceUploadCount();
        }


        var coverSong='',
            editImage='',
            acceptFileTypes='image/*', paramName='file_photo';
        if (type=='video') {
            acceptFileTypes='video/*';
            paramName='file_video';
        } else if (type=='song') {
            acceptFileTypes='audio/*';
            paramName='file_song';
            coverSong='<div class="dz-cover-song">'+
                        '<form action="">'+
                        '<button data-cl-loader="btn_cover_loader" class="btn btn-info btn_cover_music" disabled><span class="btn_cover_music_title">'+l('upload_cover')+'</span></button>'+
                        '<input type="reset" value=""/>'+
                        '<input type="file" accept="image/*" onchange="clProfilePhoto.uploadCover($(this));" onclick="clProfilePhoto.hideUploadCoverError(event, $(this));" class="dz-cover-image" disabled>'+
                        '</form>'+
                        '</div>';
        } else if (type == 'photo') {
            initEditorImage('edge');


            editImage='<button class="btn btn-primary btn_edit_image hide" disabled >'+
                            '<span class="icon_fa">'+
                            '<svg height="24" width="24" viewBox="0 0 24 24"><path d="M17.484 12c0.844 0 1.5-0.656 1.5-1.5s-0.656-1.5-1.5-1.5-1.5 0.656-1.5 1.5 0.656 1.5 1.5 1.5zM14.484 8.016c0.844 0 1.5-0.656 1.5-1.5s-0.656-1.5-1.5-1.5-1.5 0.656-1.5 1.5 0.656 1.5 1.5 1.5zM9.516 8.016c0.844 0 1.5-0.656 1.5-1.5s-0.656-1.5-1.5-1.5-1.5 0.656-1.5 1.5 0.656 1.5 1.5 1.5zM6.516 12c0.844 0 1.5-0.656 1.5-1.5s-0.656-1.5-1.5-1.5-1.5 0.656-1.5 1.5 0.656 1.5 1.5 1.5zM12 3c4.969 0 9 3.609 9 8.016 0 2.766-2.25 4.969-5.016 4.969h-1.734c-0.844 0-1.5 0.656-1.5 1.5 0 0.375 0.141 0.703 0.375 0.984s0.375 0.656 0.375 1.031c0 0.844-0.656 1.5-1.5 1.5-4.969 0-9-4.031-9-9s4.031-9 9-9z"/></svg>'+
                            '</span>'+
                            '<span class="hidden-xs">'+l('edit_image')+'</span>'+
                       '</button>';
        }

        var previewTemplate='<div class="'+selItem+' dz-preview dz-file-preview" onclick="clProfilePhoto.hideUploadCoverError(event, $(this).find(\'.dz-cover-image\'));">'+
                                '<div class="dz-image">'+
                                    '<img data-dz-thumbnail />'+
                                    '<i class="fa fa-pencil" aria-hidden="true"></i>'+
                                    '<input type="text" class="dz-desc" placeholder="'+l('add_a_title')+'">'+
                                    coverSong+editImage+
                                '</div>'+
                                '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>'+
                                '<div class="dz-converting"><span>'+l('processing')+'</span><i class="fa fa-cog fa-spin"></i></div>'+
                                '<div class="dz-converting-complete"><div class="icon_check"></div></div>'+ //<i class="fa fa-check" aria-hidden="true"></i><span>'+l('uploaded')+'</span>
                                '<div class="dz-error-message"><div class="dz-error-wrap"><span></span></div></div>'+
                                '<div class="dz-cancel"><i data-dz-remove title="'+l('cancel_download')+'" class="fa fa-times" aria-hidden="true"></i></div>'+
                              '</div>';
        var options={
            dictDefaultMessage: l('drop_files_here_or_click_to_upload'),
            dictFallbackMessage: l('your_browser_does_not_support_dragndrop_file_uploads'),
            dictInvalidFileType: 'accept_file_types',
            dictFileTooBig: 'max_file_size',
            acceptedFiles: acceptFileTypes,
            paramName: paramName,
            maxFilesize: $this['max'+type+'Size'],
            //maxFilesize:20, - how many files are processed by Dropzone(dz-max-files-reached)
            //capture
            parallelUploads:6,
            ignoreHiddenFiles:true,
            timeout:3600000,
            createImageThumbnails:false,
            dictRemoveFileConfirmation:false,
            dictCancelUploadConfirmation:'',
            previewTemplate:previewTemplate,
            clickable:'.'+type+'_upload_zone'
        }

        $this.$ppUpload[type]['dropzone'] = new Dropzone('#pp_'+type+'_upload_frm',options);
        $this.$ppUpload[type]['dropzone'].on('uploadprogress',function(file,progress){
            if(progress==100){
                var $preview=$(file.previewElement);
                //$preview.find('.dz-converting').append(createLoader('dz-progress-loader'));
                $preview.addClass('dz-progress-full');
            }
        }).on('addedfile',function(file){
            var $preview=$(file.previewElement);
            $preview.find('.dz-desc').keydown(function(e){
                if(e.keyCode == 13){
                    if($this.$ppUpload[type]['upload_count'] == 1 && !$this.$ppUpload[type]['btn_publish'].prop('disabled')){
                        $this.$ppUpload[type]['btn_publish'].click();
                    }
                    e.preventDefault();
                    return false;
                }
            })
            /*var w;
            if ($this.$ppUpload[type]['w']) {
                w=$this.$ppUpload[type]['pp_modal'].css('width', 'auto').width();

                //if (w > $this.$ppUpload[type]['w']) {
                    $this.$ppUpload[type]['pp_modal'].css({width:$this.$ppUpload[type]['w']+'px',transition:'width .4s'});
                    setTimeout(function(){
                        $this.$ppUpload[type]['pp_modal'].css({width:w})
                    },1)
                    $this.$ppUpload[type]['w'] = w;

                //}
            } else {
                w=$this.$ppUpload[type]['pp_modal'].width();
                $this.$ppUpload[type]['w']=w;
                $this.$ppUpload[type]['pp_modal'].width(w);
            }*/
            //debugLog('ADDED file', file);
            $this.enabledPublish(type);
            $this.$ppUpload[type]['upload_count']++;
            $this.reInitScroll(type);
        }).on('removedfile',function(file){
            var id=$(file.previewElement).data('id')
            if(id){
                delete $this.uploadFileData[type][id];
            }
            //debugLog('REMOVE file',file);
            $this.enabledPublish(type);
            reduceUploadCount();
            $this.reInitScroll(type, 'relative');
        }).on('complete',function(file){
            if (file.previewElement && file.status=='success'){
                var $preview=$(file.previewElement);
                try {
                    var res=jQuery.parseJSON(file.xhr.response);
                    if (typeof res=='object') {
                        var src;
                        if (type=='video') {
                            res=res.file_video[0];
                            src=urlFiles+res.src_b;
                        } else if (type=='song') {
                            src=res.src;
                        } else {
                            src=res.src_r;
                        }

                        if(res.error){
                            showError(file, res.error);
                            //debugLog('COMPLETE ERROR', res.error);
                            return;
                        }

                        if (type == 'photo' && !res.gif && res.isImageEditorEnabled) {
                            console.log('show editor');
                            $preview.find('.btn_edit_image').removeClass('hide');
                        }

                        var $img=$preview.find('.dz-image > img').on('load', function(){
                            $preview.addClass('dz-complete-full').data({id:res.id});
                        });
                        $img[0].src=src;
                        if($img[0].complete)$img.load();
                        $preview.find('.dz-desc')[0].id='dz_item_desc_'+res.id;
                        if (type=='photo') {
                            if (type=='photo' && isSiteOptionActive('gallery_photo_face_detection', 'edge_gallery_settings')) {
                                checkImageFaceDetection(urlFiles+res.src_bm, res.id);
                            }
                            $this.$ppUpload[type]['editor_'+res.id] = new FilerobotImageEditor();
                            $preview.find('.btn_edit_image').prop('disabled', false)
                            .on('click',function(){
                                console.log('Open editor image', res);
                                imageEditorInfo = {
                                    id: res.id,
                                    preview: src,
                                    img: $img,
                                    btn: $(this)
                                }
                                $ppImageEditor.open(urlMain+'get_img_editor.php?photo_id='+res.id);
                                return false;
                            });
                        } else if (type=='song') {
                            $preview.find('.dz-cover-song form > *')
                                    .data('id', res.id)
                                    .prop('disabled', false);
                            var $cover=$preview.find('.dz-cover-image');
                            $cover[0].id='dz_item_cover_image_'+res.id;
                            $cover.data('id',res.id);
                            $cover.data('image',0);
                        }

                        $this.uploadFileData[type][res.id]={id:res.id,
                                                            desc:''};
                        if (type=='song') {
                            $this.uploadFileData[type][res.id]['length'] = res.length;
                        } else if (type=='photo') {
                            $this.uploadFileData[type][res.id]['src_bm'] = urlFiles+res.src_bm;
                        }
                        //$('.dz-caption-text',$preview).data('value')
                        $preview.data('id',res.id);
                        $('.fa-times',$preview).attr('title',l('delete'));
                        //debugLog('COMPLETE UPLOAD FILE', $this.uploadFileData[type][res.id]);
                    } else {
                        //debugLog('COMPLETE ERROR NO OBJECT', file);
                        showError(file)
                    }
                } catch(e){
                    //debugLog('COMPLETE ERROR TRY', e, file);
                    showError(file)
                }
                $this.enabledPublish(type);
            } else {
                showError(file)
                //debugLog('COMPLETE NO PREVIEW', file);
            }
        }).on('error',function(file,errorMessage,xhr){
            if(file.status!='canceled'){
                //debugLog('ERROR', [file, errorMessage, xhr]);
                showError(file,errorMessage)
            }
        })

        var sel='#navbar_menu_'+type+'_add_edge, .menu_'+type+'_add_edge, .'+type+'_upload';
        $this.initClickUploadFile(sel,type);
    }

    this.loadGroup = false;
    this.initClickUploadFile = function(sel,type) {
        $(sel).click(function(e){
            var $el=$(e.target);

            // Stop file upload window after click on the first uploaded photo
            if(($el.attr('class') === $('.user_pic_frame_ie img').attr('class')) && $el.data('pid') > 0) {
                return false;
            }

            //popcorn modified 2024-11-25
            if (($el.is('.photo_upload') || $el.closest('.photo_upload')[0]) && $this.uid && ($this.guid != $this.uid && !is_nsc_couple_page)) {
                return false;
            }
            var $link=$(this);
            $this.loadGroup = !$link.is('.navbar_menu_more_item');
            closeAllMenuAndPopup();
            closeNavbarMenuCollapse(function(){
                $this.$ppUpload[type]['upload_count']=0;
                $this.enabledPublish(type);
                $this.visDropZone(false,type);
            })
            return false;
        })
    }

    /* Gallery */
    this.isVideo = function(id) {
        var s=''+id;
        return s.indexOf('v_')!==-1;
    }

    this.getVideoId = function(pid) {
        pid=''+pid;
        return parseInt(pid.replace('v_',''));
    }

    this.prepareId = function(pid) {
        if ($this.ppGalleryIsVideo) {
            pid=$this.getVideoId(pid);
        }
        return pid;
    }

    this.isPublic = function(pid,info){
        info=info||$this.galleryMediaData[pid];
        return (($this.guid==info['user_id']||info['is_friend']||info['private']=='N'))||$this.ppGalleryIsVideo;
    }

    this.prepareLoadParamPhoto = function(pid){
        pid=pid||$this.curPid;
        $this.setLoadParamPhoto(pid);
        $this.preLoadingPhotos('right', pid);
        $this.preLoadingPhotos('left', pid);
    }

    this.setLoadParamPhoto = function(pid){
        var info=$this.galleryMediaData[pid],$content;
        if(info && !info.load) {
            if($this.isPublic(pid,info)){
                $content=$('<img src="'+urlFiles+info['src_bm']+'" class="hidden">');
            }else{
                $content=$('<img src="'+urlFiles+info['src_bm']+'" class="hidden">');
            }
            $this.galleryMediaData[pid].load=$content;
        }
    }

    this.preLoadingPhotos = function(direct, pid, n){
        if($this.countAllMedia<2)return;
        var direct=direct=='left'?'prev_id':'next_id',pid=pid||$this.curPid,
            info=$this.galleryMediaData[pid];
        if (!info[direct]) return;
        for(var i=1;i<(n||4);i++) {
            info=$this.galleryMediaData[pid];
            pid=info[direct];
            $this.setLoadParamPhoto(pid);
        }
    }

    this.galleryMediaData = {};
    this.offsetInfo = {};

    this.setGalleryMediaData = function(data, pageUpdateDate, curPid, stopPreloadPhoto) {
        stopPreloadPhoto = defaultFunctionParamValue(stopPreloadPhoto,$this.stopPreloadPhoto);
        //debugLog('Gallery - setGalleryMediaData', pageUpdateDate);
        if (pageUpdateDate) {

            if ($.isEmptyObject(data)) {
                $this.stopPreloadPhoto = true;
                return;
            }
            //$this.stopPreloadPhoto = true;

            var dataOffset = {}, offsetMax=0, c=0;
            for (var pid in data) {//Dublicate
                if ($this.galleryMediaData[pid]) {
                    var info=data[pid],
                        off=info['offset'],
                        pidNext=info['next_id'],
                        pidPrev=info['prev_id'];
                    data[pidNext]['prev_id'] = pidPrev;
                    data[pidNext]['prev_title'] = data[pidPrev]['description'];
                    data[pidPrev]['next_id'] = pidNext;
                    data[pidPrev]['next_title'] = data[pidNext]['description'];
                    delete data[pid];
                    for (var pid in data) {
                        if (data[pid]['offset'] > off) {
                            data[pid]['offset'] -=1;
                        }
                    }
                }
            }

            if ($.isEmptyObject(data)) {
                $this.stopPreloadPhoto = true;
                return;
            }

            for (var pid in data) {
                var info=data[pid],off=info['offset'];
                dataOffset[off]=info;
                if(info['offset']>offsetMax)offsetMax=off;
                c++;
            }

            if (pageUpdateDate == 'right') {
                var curOffset=$this.offsetInfo['first_offset'];
                for (var pid in $this.galleryMediaData) {
                    var off=$this.galleryMediaData[pid]['offset'];
                    if (off >= curOffset) {
                        $this.galleryMediaData[pid]['offset'] += c;
                    }
                }

                for (var pid in data) {
                    data[pid]['offset'] += curOffset+1;
                }

                var id1=$this.offsetInfo['first_id'];
                var id2=$this.offsetInfo['last_id'];

                var id3=dataOffset[0]['photo_id'];
                var id4=dataOffset[offsetMax]['photo_id'];

                $this.offsetInfo['first_id'] = id3;
                $this.offsetInfo['first_offset'] = data[id3]['offset'];
                $this.offsetInfo['max_offset'] += c;

                $this.galleryMediaData[id1]['prev_id'] = id4;
                $this.galleryMediaData[id1]['prev_title'] = data[id4]['description'];

                $this.galleryMediaData[id2]['next_id'] = id3;
                $this.galleryMediaData[id2]['next_title'] = data[id3]['description'];

                data[id3]['prev_id'] = id2;
                data[id3]['prev_title'] = $this.galleryMediaData[id2]['description'];

                data[id4]['next_id'] = id1;
                data[id4]['next_title'] = $this.galleryMediaData[id1]['description'];

            } else {
                var curOffset=$this.offsetInfo['last_offset'];
                /*for (var pid in $this.galleryMediaData) {
                    var off=$this.galleryMediaData[pid]['offset'];
                    if (off >= curOffset) {
                        $this.galleryMediaData[pid]['offset'] += c;
                    }
                }*/

                for (var pid in data) {
                    data[pid]['offset'] += curOffset+1;//+1
                }

                var id1=$this.offsetInfo['first_id'];
                var id2=$this.offsetInfo['last_id'];

                var id3=dataOffset[0]['photo_id'];
                var id4=dataOffset[offsetMax]['photo_id'];

                $this.offsetInfo['last_id'] = id4;
                $this.offsetInfo['last_offset'] = data[id4]['offset'];
                $this.offsetInfo['max_offset'] += c;

                $this.galleryMediaData[id1]['prev_id'] = id4;
                $this.galleryMediaData[id1]['prev_title'] = data[id4]['description'];

                $this.galleryMediaData[id2]['next_id'] = id3;
                $this.galleryMediaData[id2]['next_title'] = data[id3]['description'];

                data[id3]['prev_id'] = id2;
                data[id3]['prev_title'] = $this.galleryMediaData[id2]['description'];

                data[id4]['next_id'] = id1;
                data[id4]['next_title'] = $this.galleryMediaData[id1]['description'];
            }

            for (var pid in data) {
                $this.galleryMediaData[pid] = data[pid];
            }

            $this.prepareLoadParamPhoto(curPid);

            console.log('MEDIA DATA UPDATE',$this.galleryMediaData, $this.offsetInfo);

        } else {
            for (var pid in $this.galleryMediaData) {
                delete $this.galleryMediaData[pid];
            }
            $this.galleryMediaData=data;
            if (!stopPreloadPhoto) {
                $this.updateOffsetMediaData(curPid);
            }
        }
        
        ehp_type = $this.getEHPType();

        if(ehp_type == 'event') {
            $this.visibleMediaData = $this.galleryMediaData;
        } else if(ehp_type == 'hotdate') {
            $this.visibleMediaData = $this.galleryMediaData;
        } else if(ehp_type == 'partyhou') {
            $this.visibleMediaData = $this.galleryMediaData;
        }
    }

    this.updateOffsetMediaData = function(curPid, noFirstUpdate, noLastUpdate, data) {
        noFirstUpdate=noFirstUpdate||false;
        noLastUpdate=noLastUpdate||false;
        data=data||$this.galleryMediaData;
        var c=0,offsetMax=0, galleryMediaOffset={};
        for (var pid in data) {
            var info=data[pid],off=info['offset'];
            galleryMediaOffset[off]=info;
            if(info['offset']>offsetMax)offsetMax=off;
            c++;
        }

        var curOffset=data[curPid]['offset'];
        var iF=curOffset-$this.limitLoadMediaData;
        if(iF<0)iF=c-Math.abs(iF);
        var iL=curOffset+$this.limitLoadMediaData;
        if(iL>=c)iL=iL-c;

        if (!noFirstUpdate) {
            $this.offsetInfo['first_id'] = galleryMediaOffset[iF]['photo_id'];
            $this.offsetInfo['first_offset'] = iF;
        }

        if (!noLastUpdate) {
            $this.offsetInfo['last_id'] = galleryMediaOffset[iL]['photo_id'];
            $this.offsetInfo['last_offset'] = iL;
        }

        $this.offsetInfo['max_offset'] = offsetMax;
        console.log('CUR PHOTO', curPid);
        console.log('FIRST PHOTO', iF);
        console.log('LAST PHOTO', iL);
        console.log('OFFSET PHOTO', galleryMediaOffset);
        console.log('OFFSET PHOTO INFO', $this.offsetInfo);

    }

    this.visibleMediaData = {};
    this.setVisibleMediaData = function(pid, data) {
        if(!$this.isVideo(pid)){
            pid=pid*1;
        }
        if(!pid || pid=='v_')return;
        if (typeof $this.visibleMediaData[pid] == 'undefined') {
            $this.visibleMediaData[pid] = {};
        }
        $this.visibleMediaData[pid] = data;
    }

    /* Media info */
    this.setDescription = function(desc){
        $this.$el['desc'].attr('title', desc);
        $('span.text_overflow_page',$this.$el['desc']).html(desc);
    }

    this.updatePhotoLikeData = function(pid, data){
        console.log('Update LIKE', pid, data);
        if(pid==='')return;
        if ($this.galleryMediaData[pid]) {
            $this.galleryMediaData[pid]['like'] = data.like;
            $this.galleryMediaData[pid]['dislike']= data.dislike;
            $this.galleryMediaData[pid]['my_like'] = data.my_like;
        }

        if ($this.visibleMediaData[pid]) {
            $this.visibleMediaData[pid]['like'] = data.like;
            $this.visibleMediaData[pid]['dislike']= data.dislike;
            $this.visibleMediaData[pid]['my_like'] = data.my_like;
        }
    }

    this.updatePhotoLike = function(pid, info){
        info=info||$this.galleryMediaData[pid];

        var like=info['like']*1,
            dislike=info['dislike']*1,
            n=0;
        if(like){
            if (dislike){
                n = (like*100)/(like+dislike);
            } else {
                n = 100;
            }
        }

        $this.$ppGalleryLikesBl.removeClass('action');
        $this.$ppGalleryLikeCountBtn.removeClass('active').removeChildrenLoader();
        $this.$ppGalleryDislikeCountBtn.removeClass('active').removeChildrenLoader();

        $this.$ppGalleryLikeCount.text(like);
        $this.$ppGalleryDislikeCount.text(dislike);
        $this.$ppGalleryLikesRange.css('width', n+'%');

        if (info['my_like']) {
            if (info['my_like'] == 'Y') {
                $this.$ppGalleryLikeCountBtn.addClass('active');
            } else if(info['my_like'] == 'N') {
                $this.$ppGalleryDislikeCountBtn.addClass('active');
            }
        }
    }

    this.updatePhotoInfo = function(pid,info){
        info=info||$this.galleryMediaData[pid];
        if(pid!=$this.curPid||!info)return;

        var isMy=$this.guid==info['user_id'],
            desc=info['description'],tags=info['tags_html'],
            space='&nbsp;',
            isPublic=$this.isPublic(pid,info);

        if ($this.$el['directLink'][0]) {
            var isLink=isSiteOptionActive('gallery_show_download_original', 'edge_gallery_settings')
            if (isLink) {
                //popcorn modified s3 bucket photo 2024-05-03
                var srcLink=$this.ppGalleryIsVideo ? info.src_v : urlFiles+info.src_bm;
                $this.$el['directLink'][0].href = srcLink;
            } else {
                $this.$el['directLink'].remove();
            }
        }

        /* Like - dislike photo */
        $this.updatePhotoLike(pid, info);
        /* Like - dislike photo */

        if(isMy){
            if(!desc){
                desc=$this.ppGalleryIsVideo?l('click_here_to_add_a_video_caption'):l('click_here_to_add_a_photo_caption');
            }
            $this.$el['desc'].addClass('my_desc');
        }else{
            if(!isPublic)desc=space;
            $this.$el['desc'].removeClass('my_desc');
        }
        if(isMy){
            if(!tags){
                tags=l('click_to_add_tags');
            }
            $this.$el['tagsLink'].addClass('my_tags').attr('title', l('edit_tags'));
        }else{
            $this.$el['tagsLink'].removeClass('my_tags').attr('title', '');//info['tags_title']
        }

        $this.$el['tagsList'].removeChildrenLoader();
        $this.$el['tagsList'].html(tags);
        $this.$el['tagsBl'][tags?'show':'hide']();

        $this.setDescription(desc);
        if(isMy){
            $this.$el['descWrap'].removeClass('empty');
            $this.cancelEditDesc();
        } else {
            $this.$el['descWrap'][desc?'removeClass':'addClass']('empty');
        }

        $this.$ppGalleryDescPhoto
            .addClass('profile_photo_r_'+info['user_id'])
            .css('background-image', 'url('+urlFiles+info['user_photo_r']+')')[0].href=info['user_url'];
        $this.$ppGalleryDescName.text(info['user_name_short']).attr('title', info['user_name'])[0].href=info['user_url'];

        var commentsCount=info['comments_count']*1;
        if(commentsCount){
            $this.$el['commentCount'].text(commentsCount)
            $this.$el['commentCountBl'].show();
        }else{
            $this.$el['commentCountBl'].hide();
            $this.$el['commentCount'].text('');
        }

        $this.setTitleFaceAndDate(pid,info);

        $this.$ppGalleryTimeAgo.text(info['time_ago']);

        $this.$el['infoBl'].addClass('to_show');
        $this.$el['metaBl'].addClass('to_show');
    }

    this.showLastFieldComment = function(callCompete,noAnimate,$box,number){
        noAnimate=noAnimate||false;
        $box=$box||$('.bl_comments > .pp_gallery_comment_item', $this.$ppGallery);
        number=number||$this.numberCommentsFrmShow;
        var l=$box.length,
            frmV=$this.$ppGalleryFieldCommentBottom.is(':visible'),
            fn='', n=number, n1=n+1;
        if(l>n && !frmV){
            fn=noAnimate?'show':'slideDown';
        }else if(l<n1 && frmV){
            fn=noAnimate?'hide':'slideUp';
            callCompete=false;
        }
        if(typeof callStep!='function')callStep=function(){};
        if(typeof callCompete!='function')callCompete=function(){};
        if (fn){
            if (noAnimate) {
                $this.$ppGalleryFieldCommentBottom.stop()[fn]();
            } else {
                $this.$ppGalleryFieldCommentBottom.stop()[fn](
                {complete:callCompete, duration:400})
            }
        } else {
            callCompete();
        }
    }

    this.changeLinkHideHeaderPicture = function(hide,$title,pid){
        pid=pid||0;
        if(!$title&&pid){
            var $elList=$('#list_photos_image_menu_hide_header_'+pid);
            if($this.isVideo(pid)){
                $elList=$('#list_videos_image_menu_hide_header_'+pid);
            }
            $this.changeLinkHideHeaderPicture(hide, $elList);
        }
        $title=$title||$this.$el['linkHideHeader'];
        if(!$title[0])return;
        $title.find('.hide_header_picture_title').text(hide?l('picture_add_in_header'):l('picture_remove_from_header'));
        $title.find('.fa').removeClass('fa-plus-square fa-minus-square').addClass(hide?'fa-plus-square':'fa-minus-square');
    }

    this.getKeyDefaultPhoto = function(groupId){
        return groupId ? 'default_group' : 'default';
    }

    this.controlRestoreImage = function(restore){
        if(restore == 0) restore = '';
        $this.$el['btnAdditional'].find('.pp_gallery_restore_image')[restore?'addClass':'removeClass']('to_show');
    }

    this.showConrolsComment = function(pid,info){

        var d=$this.$el['mediaMenuMore'].is('.collapsing')?200:1;
        setTimeout(function(){
            $this.$el['linkSetDefault'].removeChildrenLoader();
        },d)
        var info=info||$this.galleryMediaData[pid],
            groupId=info['group_id']!=undefined?info['group_id']*1:0;

        removeChildrenLoader($this.$el['linkSetDefault']);
        removeChildrenLoader($this.$el['linkReport']);
        removeChildrenLoader($this.$el['linkHideHeader']);

        removeChildrenLoader($this.$el['btnImageEdit']);
        removeChildrenLoader($this.$el['btnMakeProfile']);
        removeChildrenLoader($this.$el['btnAdditional'].find('.pp_gallery_restore_image'));

        if ($this.guid==info['user_id'] || info?.is_host == true) {
            $this.controlRestoreImage(info['restore']);

            if (info['visible']=='N'||info['visible']=='Nudity') {
                $this.$el['linkHideHeader'].hide();
                $this.$el['notChecked'].show();
                $this.$el['linkSetDefault'].stop().delay(d).fadeOut(1);

                $this.$el['btnMakeProfile'].removeClass('to_show');
            } else {
                $this.$el['linkHideHeader'].show();
                $this.$el['notChecked'].hide();
                if (!$this.ppGalleryIsVideo && info[$this.getKeyDefaultPhoto(groupId)]=='N' && (info['public'] == 'Y')){ // Divyesh - added on 23042024
                    if($this.isEHP()) {
                        if(info?.is_host){
                            $this.$el['linkSetDefault'].stop().delay(d).fadeIn(1);
                            $this.$el['btnMakeProfile'].addClass('to_show');    
                        } else {
                            $this.$el['linkSetDefault'].stop().delay(d).fadeOut(1);
                            $this.$el['btnMakeProfile'].removeClass('to_show');
                        }
                    } else {
                        $this.$el['linkSetDefault'].stop().delay(d).fadeIn(1);
                        $this.$el['btnMakeProfile'].addClass('to_show');  
                    }
               
                } else{
                        $this.$el['linkSetDefault'].stop().delay(d).fadeOut(1);
                        $this.$el['btnMakeProfile'].removeClass('to_show');
                }
            }

            $this.changeLinkHideHeaderPicture(info['hide_header']);

            $this.$el['linkDelete'].show();

            /* IOS */
            $this.$el['linkEdit'][$this.ppGalleryIsVideo?'hide':'show']();
            $this.$el['linkCrop'][$this.ppGalleryIsVideo?'hide':'show']();
            $this.$el['linkEditVideo'][$this.ppGalleryIsVideo?'show':'hide']();
            /* IOS */
            $this.$el['linkReport'].hide();
            $this.$el['mediaMenu'].show();
            if (!$this.ppGalleryIsVideo) {
                if(info['gif']) {
                    $this.$el['btnAdditionalEditor'].addClass('hide');
                } else {
                    $this.$el['btnAdditionalEditor'].removeClass('hide');
                }
                $this.$el['btnAdditional'].addClass('to_show');
            } else {
                $this.$el['btnAdditional'].removeClass('to_show');
            }

            if($this.guid != info['user_id']) {
                $this.$el['btnImageEdit'].hide();
                $this.$el['btnAdditionalEditor'].addClass('hide');
                $this.$el['btnAdditional'].find('.pp_gallery_restore_image').hide();
                $this.$el['linkHideHeader'].hide();
                // $this.$el['linkDelete'].hide();
                $this.$el['linkEdit'].hide();
                $this.$el['linkCrop'].hide();
                $this.$el['linkEditVideo'].hide();
                $this.$el['notChecked'].hide();
                if(!in_array($this.guid, info['reports'].split(',')) && $this.isPublic(pid,info)){
                    var title=$this.ppGalleryIsVideo ? l('report_video') : l('report_photo');
                    $('span:not(.icon_fa)', $this.$el['linkReport']).text(title);
                    $this.$el['linkReport'].show();
                    //$this.$el['mediaMenu'].show();
                } else {
                    $this.$el['linkReport'].hide();
                    //$this.$el['mediaMenu'].hide();
                }
            }
            if($this.isEHP()) {
                $this.$el['linkHideHeader'].hide();
            }
        } else {
            $this.$el['btnAdditional'].removeClass('to_show');
            $this.$el['btnMakeProfile'].removeClass('to_show');
            $this.$el['linkSetDefault'].hide();
            $this.$el['linkHideHeader'].hide();
            $this.$el['linkDelete'].hide();
            $this.$el['linkEdit'].hide();
            $this.$el['linkCrop'].hide();
            $this.$el['linkEditVideo'].hide();
            $this.$el['notChecked'].hide();
            if(!in_array($this.guid, info['reports'].split(',')) && $this.isPublic(pid,info)){
                var title=$this.ppGalleryIsVideo ? l('report_video') : l('report_photo');
                $('span:not(.icon_fa)', $this.$el['linkReport']).text(title);
                $this.$el['linkReport'].show();
                //$this.$el['mediaMenu'].show();
            } else {
                $this.$el['linkReport'].hide();
                //$this.$el['mediaMenu'].hide();
            }
        }
        if($this.isPublic(pid,info)){
            $this.$ppGalleryFieldComment.show();
            $this.$ppGalleryCommentsHidden.hide();
            $this.$ppGalleryComments.show();
        }else{
            $this.$ppGalleryFieldComment.hide();
            $this.$ppGalleryComments.hide().html('');
            $this.$ppGalleryCommentsHidden.show();
        }
        return info;
    }

    this.setArrowsTitle = function(pid) {
        if($this.$el['arrows'].is(':hidden')) return;
        var info=$this.galleryMediaData[pid]||false,
            isPublic=$this.isPublic(pid),
            prev=$this.galleryMediaData[info['prev_id']],
            next=$this.galleryMediaData[info['next_id']],
            nextTitle=l('next_photo'),
            prevTitle=l('prev_photo');
        if($this.ppGalleryIsVideo){
            nextTitle=l('next_video');
            prevTitle=l('prev_video');
        }
        $this.$el['arrowsPrev'].attr('title',(isPublic&&prev&&prev['description'])||prevTitle);
        $this.$el['arrowsNext'].attr('title',(isPublic&&next&&next['description'])||nextTitle);
    }

    this.increaseCommentsCounter = function(pid,inc) {
        var count=$this.$el['commentCount'].text()*1;
        inc=defaultFunctionParamValue(inc, true);
        if(inc){
            count++;
        }else{
            count--;
        }
        $this.$el['commentCount'].text(count);
        $this.$el['commentCountBl'].show();
        if($this.galleryMediaData[pid])$this.galleryMediaData[pid]['comments_count']=count;
        if($this.visibleMediaData[pid])$this.visibleMediaData[pid]['comments_count']=count;
        if ($this.ppGalleryIsVideo) {
            $('.video_comments_count_'+$this.prepareId(pid)).text(count);
        } else {
            $('.photo_comments_count_'+pid).text(count);
        }
    }
    /* Media info */

    this.countAllMedia = 0;
    this.photoLoad=true;
    this.curPid=0;
    this.updateOnlyData=false;
    this.stopPreloadPhoto=true;
    this.pagePreloadLimit={};
    this.initShowGallery = function(curPid, data,
                                    count, countProfile, countProfileTiltle, photoDefault, noMediaRequested,
                                    stopPreloadPhoto, pagePreloadUpdate, pagePreloadLimit) {

        debugLog('initShowGallery', [data, curPid,
                                     count, countProfile, countProfileTiltle, photoDefault, noMediaRequested,
                                     stopPreloadPhoto, pagePreloadLimit, pagePreloadUpdate]);

        if ($this.ppGalleryIsVideo) {
            pagePreloadUpdate=false;
            stopPreloadPhoto=true;
        } else {
            stopPreloadPhoto *=1;
            $this.pagePreloadLimit=pagePreloadLimit;
            
            if (!stopPreloadPhoto){
                var limit=pagePreloadLimit;
                if ($.isEmptyObject(limit)) {
                    $this.stopPreloadPhoto=true;
                } else {
                    debugLog('initShowGallery SET LIMIT', limit);
                    if (limit['next'][1] == limit['prev'][0] || limit['next'][0] == limit['prev'][1]) {
                        $this.stopPreloadPhoto=true;
                    }
                }
            }
        }

        $this.setGalleryMediaData(data, pagePreloadUpdate, curPid, stopPreloadPhoto);

        if($this.updateOnlyData || pagePreloadUpdate){
            //debugLog('Gallery Update only DATA');
            return;
        }
        if ($this.ppGalleryIsVideo) {
            $this.stopPreloadPhoto=true;
        } else {
            $this.stopPreloadPhoto=stopPreloadPhoto*1;
        }

        $this.countAllMedia=count*1;
        noMediaRequested=noMediaRequested*1;//no picture

        $this.$ppGalleryPrivateBox = [];

        $this.$el['arrows']         = $('#photo_show_prev, #photo_show_next');
        $this.$el['arrowsPrev']     = $('#photo_show_prev');
        $this.$el['arrowsNext']     = $('#photo_show_next');
        $this.$el['mediaMenu']      = $('#pp_gallery_more_menu');
        $this.$el['mediaMenuMore']  = $('#pp_gallery_more_options');
        $this.$el['linkSetDefault'] = $('#pp_gallery_make_profile_picture');
        $this.$el['linkHideHeader'] = $('#pp_gallery_hide_header_picture');
        $this.$el['linkDelete']     = $('#pp_gallery_delete_picture');
        $this.$el['linkEdit']       = $('#pp_gallery_edit_picture');
        $this.$el['linkCrop']       = $('#pp_gallery_crop_picture');
        $this.$el['linkEditVideo']  = $('#pp_gallery_edit_video');
        $this.$el['linkReport']     = $('#pp_gallery_report');
        $this.$el['btnAdditional']  = $('#pp_gallery_btn_additional');
        $this.$el['btnAdditionalEditor'] = $this.$el['btnAdditional'].find('.edit_image_icons, .btn_additional_first');
        $this.$el['btnImageEdit']   = $('#pp_gallery_btn_edit_image');
        $this.$el['btnMakeProfile']   = $('#pp_gallery_btn_make_profile');


        $this.$el['notChecked']     = $('#pp_gallery_not_checked');

        /* No media file */
        if (noMediaRequested) {
            debugLog('No media file - DELETED', curPid);
            if ($this.ppGalleryIsVideo) {
                $this.videoFilePlayFailed();
                $this.updatePageData($this.curPid, countProfileTiltle, countProfile, true);
            } else {
                $this.prepareLoadParamPhoto(curPid);
                $this.updatePageData($this.curPid, countProfileTiltle, countProfile);
                setTimeout(function(){
                    $this.show('right', curPid, true);
                    photoDefault && $this.replacePhotoDefault(photoDefault);
                    setTimeout(function(){
                        if($this.countAllMedia>1){
                            $this.$el['arrows'].removeClass('to_hide');
                        } else {
                            $this.$ppGalleryContainer.addClass('one_photo');
                        }
                    },300)
                },100)
            }
            return;
        }

        if ($this.curPid!=curPid) {
            $this.curPid=curPid;
        }
        var pid=$this.curPid;
//return;
        $this.showConrolsComment(pid);
           // console.log($this.galleryMediaData[$this.curPid])
            //$this.curPid=curPid;
        if($this.countAllMedia>1){
            if ($this.ppGalleryIsVideo) {

            } else {
                $this.activateSwipeGallery();
                $this.prepareLoadParamPhoto(pid);
                $this.setArrowsTitle(pid);
                $this.$el['arrows'].removeClass('to_hide');
            }
        }else{
            $this.$ppGalleryContainer.addClass('one_photo');
        }
    }

    this.showCommentId=0;
    this.prevUid=0;
    this.closeCall=false;
    this.curDataOpenGallery={};
    this.openGallery = function(e, pid, video, uid, cid, list, groupId, dataCustom) {
        list=list||false;
        groupId=groupId||0;
        cid *=1;
        if (!video) {
           pid *=1;
        }
        if (!pid) {
            return false;
        }
        if (!cid) {
            cid=0;
        } else {
            cid *=1;
        }

        if (e&&($(e.target).closest('.tag')[0] || $(e.target).is('.name'))) {
            return false;
        }
        if (!checkLoginStatus()) {
            return false;
        }

        $this.closeCall=false;

        $this.prevUid=$this.uid;
        uid=uid||0;
        if (uid) {
            $this.uid=uid;
        }

        /* Events */
        $this.showCommentId=cid||0;
        var curDataOpenGallery={pid:pid, video:video, uid:uid};
        if (cid && $this.isShowGallery) {//Events
            if (JSON.stringify(curDataOpenGallery)==JSON.stringify($this.curDataOpenGallery)){
                if($this.showUploadCommentsEnd()){
                    return;
                }
            }
            $this.closeCall=function(){$this.openGallery(e, pid, video, uid, cid)};
            $this.closeGallery();
            return;
        }
        $this.curDataOpenGallery={pid:pid, video:video, uid:uid};
        /* Events */


        $this.closeCall=false;

        if (!isSiteOptionActive('audio_comment')) {
            $this.$ppGalleryClone.find('.im_audio_message_recorder').remove();
        }
        $this.$ppGallery.removeClass('first_update').empty().html($this.$ppGalleryClone.html());

        $this.ppGalleryIsVideo=video||false;
        if ($this.ppGalleryIsVideo) {
            $this.$ppGallery.removeClass('pp_photo_gallery');
            $this.$ppGallery.addClass('pp_video_gallery');
        }else{
            $this.$ppGallery.addClass('pp_photo_gallery');
            $this.$ppGallery.removeClass('pp_video_gallery');
        }

        $this.updateOnlyData=false;
        $this.curPid=0;

        var dataMedia = $this.visibleMediaData[pid];
        $this.ajaxLikes = {};
        if(dataMedia==undefined){//Fix - not data from gallery
            setTimeout(function(){$this.showGallery(pid, false, false, true, groupId, e, dataCustom)},100);
            return;


            /*$this.updateOnlyData=true;
            var cmd=$this.ppGalleryIsVideo?'get_video_comment':'get_photo_comment';
            $.post(url_ajax+'?cmd='+cmd,
               {uid:$this.uid, photo_id:pid, photo_cur_id:pid,
                get_data_edge:1, load_more:0, last_id:0, limit:0},function(res){
                if($this.isShowGallery||$this.curPid||!$this.updateOnlyData)return;
                var data=checkDataAjax(res);
                if(data){
                    var $data=$(data);
                    $data.filter('.init_gallery').appendTo('#update_server');
                    setTimeout(function(){
                        $this.showGallery(pid, $this.galleryMediaData, list, false, false, e);
                        $this.updateOnlyData=false;
                    },100);
                }else{
                    $this.updateOnlyData=false;
                }
            })*/
        } else {
            setTimeout(function(){$this.showGallery(pid, false, list, false, groupId, e)},100);
        }
    }

    $this.isShowGallery=false;
    this.noAction = function(pid){
        var is=!$this.isShowGallery;
        if(pid||0){
            is = is || pid!=$this.curPid;
        }
        return is;
    }

    this.isNeedLiveUpgrade = function(dataMedia){
        if (dataMedia && dataMedia['is_video'] && !userAllowedFeature['live_streaming'] && dataMedia['live_id']
            && dataMedia['user_id'] != $this.guid) {
            confirmCustom(l('watch_pasts_stream_need_upgrade'), function(){
                redirectUrl(urlPagesSite.upgrade)
            },l('alert_html_alert'));
            return true;
        }
        return false;
    }


    this.checkNeedLiveCreditAction={};
    this.liveViewAllowed = false;
    this.checkNeedLiveCredit = function(dataMedia, call, e){
        $this.liveViewAllowed = false;
        if (dataMedia && dataMedia['is_video'] && dataMedia['live_id']
            && $this.live_price && dataMedia['user_id'] != $this.guid) {
            var vid=dataMedia['video_id'];
            if ($this.checkNeedLiveCreditAction[vid]) {
                return;
            }
            $this.checkNeedLiveCreditAction[vid]=1;
            var $targ=e?$(e.target):[], $layer=[], isLayer=false;

            /* Loader */
            if ($targ[0]) {
                var bl={1:'.grid_item_v_'+vid, 2:'.column_video_'+vid, 3:'.list_videos_image_'+vid},
                    $bl, $blC;
                for (var k in bl) {
                    $bl=$(bl[k]);
                    $blC=$targ.closest(bl[k]);
                    isLayer=k == 3;
                    if ($targ[0] == $bl[0]) {
                        $layer=$bl;
                        break;
                    } else if ($blC[0]) {
                        $layer=$blC;
                        break;
                    }
                }
                if ($layer[0]) {
                    if (isLayer) {
                        $layer=$layer.find('.layer_action_list');
                        $layer.addClass('to_show').addChildrenLoader();
                    } else {
                        $layer.addChildrenLoader();
                    }
                }
            }
            /* Loader */

            $this.liveViewAllowed = function(){

                $.post(url_ajax,{cmd:'live_stream_paid'},function(res){
                    var data=checkDataAjax(res);
                    if(data!==false){
                        closePopupUpdate('pp_boost_ajax', false, call)
                    } else {
                        closePopupUpdate('pp_boost_ajax', false, function(){alertServerError(true)})
                    }
                })
            }

            $.post(url_ajax+'?cmd=get_available_credits',{}, function(res){
                var data=checkDataAjax(res);
                if (data){
                    var balance=data*1;
                    if(balance<$this.live_price){
                        // Buy credits
                        var msg=l('you_have_no_enough_credits');
                        if(typeof isInAppPurchaseEnabled != 'undefined' && !isInAppPurchaseEnabled){
                            msg += '<br>'+l('buy_credits');
                            alertCustom(msg, l('alert_html_alert'));
                        }else{
                            confirmCustom(msg,function(){
                                setTimeout(function(){
                                    clUpgrade.requestIncreasePopularity('pp_payment','live_stream_past');
                                },350)
                            },l('alert_html_alert'),l('alert_html_ok'),l('buy_credits'))
                        }
                    } else {
                        //This service costs *** credits
                        clUpgrade.requestIncreasePopularity('pp_payment','live_stream_past');
                    }
                }else{
                    alertServerError(true)
                }

                $this.checkNeedLiveCreditAction[vid]=0;
                if ($layer[0]) {
                    if (isLayer) {
                        $layer.removeChildrenLoader().removeClass('to_show');
                    } else {
                        $layer.removeChildrenLoader();
                    }
                }

            })

            //call();
            return;
        }
        call();
    }

    $this.$el={};
    $this.mediaList=false;
    $this.mediaOffset=false;
    this.showGallery = function(pid, dataMedia, list, reloadData, groupId, e, dataCustom){

        //popcorn modified for event notification...
        var $target = $(e.target).closest('.events_notification_item');
        var eventId = $target.data('event-id');
        var is_access_offset_all = eventId ? true :  false;

        //in profile photo page get all access(public, private, folder, personal) for this is_access_offset_all = true;
        var $target = $(e.target).closest('.img_border');
        var profile_photo = $target.data('profile-photo');
        if(profile_photo == '1') {
            is_access_offset_all = true;
        }

        reloadData=reloadData||false;
        groupId=groupId||false;

        $this.isShowGallery=true;

        $this.mediaList=false;
        $this.mediaOffset=false;
        if (list) {
            var $itemList=$('.list_photos_image_'+pid);
            if ($itemList[0]) {
                var $list=$itemList.closest('.module_filter_result');
                if ($list[0]) {
                    var offset=$list.find('.item').index($itemList.closest('.item')),
                        page=clPages.page-1;
                    if(page<0) page=0;
                    $this.mediaList=true;
                    $this.mediaOffset=page*9+offset;
                }
            }
       }

        dataMedia = (dataMedia && dataMedia[pid]) || $this.visibleMediaData[pid];

        $this.$el={
            layerBlocked   : $('#pp_gallery_layer_blocked'),
            container      : $('#pp_gallery_photos_img_box').addClass('change_photo'),
            infoBl         : $('#pp_gallery_info'),
            metaBl         : $('#pp_gallery_meta'),
            descWrap       : $('#pp_gallery_desc_wrap'),
            desc           : $('#pp_gallery_desc'),
            descEditBl     : $('#pp_gallery_desc_bl_edit'),
            descEditText   : $('input','#pp_gallery_desc_bl_edit').keydown(function(e){
                                if (e.keyCode==13) {
                                    $this.saveEditDesc();
                                    return false;
                                }
                             }),
            tagsBl         : $('#pp_gallery_tags_bl'),
            tagsLink       : $('.pp_gallery_tags_edit_link', '#pp_gallery_tags_bl'),
            tags           : $('.pp_gallery_tags', '#pp_gallery_tags_bl'),
            tagsList       : $('.pp_gallery_tags_list', '#pp_gallery_tags_bl'),
            tagsEdit       : $('#pp_gallery_tags_edit'),
            tagsEditText   : $('input','#pp_gallery_tags_edit').keydown(function(e){
                                if (e.keyCode==13) {
                                    $this.saveEditTags();
                                    return false;
                                }
                             }),
            commentCountBl : $('#pp_gallery_comments_count_bl'),
            commentCount   : $('#pp_gallery_comments_count'),
            directLink     : $('#pp_gallery_direct_link')
        };
        //$this.$el['commentCount']
        $this.$ppGalleryOverflow       = $('.pp_gallery_overflow',$this.$ppGallery);
        $this.$ppGalleryContainer      = $('#pp_gallery_photos_img_box').addClass('change_photo');
        $this.$ppGalleryOneBl          = $('#pp_gallery_photo_one_bl');
        $this.$ppGalleryOneImg         = $('#pp_gallery_photo_one_img').on('dragstart',function(){return false}).addClass('ready');
        $this.$ppGalleryDescPhoto      = $('#pp_gallery_desc_user_photo');
        $this.$ppGalleryDescName       = $('#pp_gallery_desc_user_name');
        $this.$ppGalleryCommentsBl     = $('#pp_gallery_comments_bl');
        $this.$ppGalleryComments       = $('#pp_gallery_comments');
        $this.$ppGalleryCommentsHidden = $('#pp_gallery_comments_hidden');
        $this.$ppGalleryFieldComment            = $('.pp_gallery_field_comment');
        $this.$ppGalleryFieldCommentInput       = $('.textarea', $this.$ppGalleryFieldComment);
        $this.$ppGalleryFieldCommentBottom      = $('.pp_gallery_field_comment_bottom').hide();
        $this.$ppGalleryFieldCommentBottomInput = $('.textarea', $this.$ppGalleryFieldCommentBottom);

        $this.$ppGalleryCommentText = $('.pp_gallery_comment_text').text('');//.keydown(doOnEnter($this.commentAdd));
        $this.$ppGalleryCommentText.each(function(){
            initAutoSize($(this),$this.commentAdd);
        })

        $this.$ppGalleryTimeAgo        = $('#pp_gallery_time_ago');
        $this.$ppGalleryDate           = $('#pp_gallery_date');

        $this.$ppGalleryLikesBl        = $('#pp_gallery_likes');
        $this.$ppGalleryLikesRange     = $('#pp_gallery_likes_range');
        $this.$ppGalleryLikeCount      = $('#pp_gallery_like_count');
        $this.$ppGalleryLikeCountBtn   = $('#pp_gallery_like_count_btn');
        $this.$ppGalleryDislikeCount   = $('#pp_gallery_dislike_count');
        $this.$ppGalleryDislikeCountBtn= $('#pp_gallery_dislike_count_btn');
        $this.$ppGalleryFriendListBl   = $('#gallery_friend_list');


        $this.$ppGalleryOneBl.on('mouseenter mousemove', function(){
            if(isMobileSite)return;
            var $boxTips=$('.face_box[data-original-title]:not([aria-describedby])');
            if ($boxTips[0]) {
                $boxTips.tooltip('show');
            }
        }).on('mouseleave', function() {
            if(isMobileSite)return;
            var $boxTips=$('.face_box[data-original-title][aria-describedby]');
            if ($boxTips[0]) {
                $boxTips.tooltip('hide');
            }
        })

        $this.curPid=pid;

        if ($this.ppGalleryIsVideo) {
            $('#pp_gallery_photo_one_bl').remove();
        }else{
            $('#pp_gallery_video_one_bl').remove();
        }
        if(isMobileSite)$this.resizeImage();

        $this.replacePhotoDefaultInput(pid, dataMedia);

        $this.$ppGalleryLoader=$('.css_loader', $this.$ppGallery);
        $this.$ppGalleryLoaderFrame = [];
        if ($this.showCommentId) {//Events
            $this.$ppGalleryLoaderFrame = $this.$ppGalleryLoader.clone().addClass('frame_loader_gallery').insertAfter($this.$ppGalleryLoader);
            $this.$ppGalleryLoader.hide();
        }

        if ($this.ppGalleryIsVideo) {
            $this.$ppGalleryOneBl = $('#pp_gallery_video_one_bl').addClass('to_show');

            if (reloadData && dataCustom) {//Events notif
                if ($this.isNeedLiveUpgrade(dataCustom)) return false;
            } else {
                if ($this.isNeedLiveUpgrade(dataMedia)) return false;
            }
        }

        var fnShowGallery=function(){
            /*var d=isIE?300:1;
            setTimeout(function(){
                $this.$ppGalleryCommentText.autosize({isSetScrollHeight:false,callback:function(){}})
            },d)*/
            setPushStateHistory('gallery');
            $this.$ppGallery.on('hide.bs.modal',function(){
                $this.curDataOpenGallery={};
                $this.showCommentId=0;
                $this.uid=$this.prevUid;
                $this.isShowGallery=false;

                if ($this.ppGalleryIsVideo) {
                    //if($this.isVideo&&isPlayerNative){
                        //setTimeout(function(){$('.bl_video_one_cont', '#pp_gallery_photo_one_cont').find('video, object, script').remove()},120);
                    //}
                    $this.stopVideoPlayer();
                }
                $jq('html, body').removeClass('overh');
                $this.$ppGallery.oneTransEndM(function(){
                    $this.$ppGallery.removeClass('to_show');
                    $jq('body').removeClass('gallery_open');
                    if(typeof $this.closeCall=='function')$this.closeCall();
                    $this.closeCall=false;
                })
            }).one('shown.bs.modal',function(){
            }).one('show.bs.modal',function(){
                $this.$ppGallery.oneTransEndM(function(){
                    $this.$ppGalleryCommentText.trigger('autosize');
                    //$this.$ppGalleryCommentText.autosize({isSetScrollHeight:false,callback:function(){}})
                }).addClass('to_show');
                $jq('body').addClass('gallery_open');
                $jq('html, body').addClass('overh');
            }).modal('show');


            /*notLockedUser=notLockedUser||$this.notLockedUser;
            if (!notLockedUser) {
                alertCustom($this.langParts.not_see_this_gallery,true,ALERT_HTML_ALERT);
                return false;
            }*/
            //if ($this.isShowGalleryPhoto || $this.checkUploadPhotoToSeePhotos() || !pid) return false;
        }

        var fnPrepareGallery=function(){
            if ($this.ppGalleryIsVideo) {
                //stopAllPlayers();
                $this.setVideoPlayer(pid, dataMedia);
            }else{
                $this.$ppGalleryOneImg.load(function(){
                    if($this.noAction())return;
                    if(!_isFaceDetectionLoad && $this.checkDetectFace(dataMedia)){

                    } else {
                        $this.$ppGalleryLoader.addClass('hidden');
                    }

                    $this.$ppGalleryOneImg.oneTransEnd(function(){
                    }).removeClass('to_hide');
                    //$this.$ppGalleryContainer.removeClass('change_photo');
                    setTimeout(function(){
                        $this.renderFace(dataMedia, $this.$ppGalleryOneImg[0].src);
                    },350)
                })[0].src = urlFiles+dataMedia.src_bm;
            }
            $this.updatePhotoInfo(pid,dataMedia);
        }

        var fnStart = function(){
            if(reloadData){
                debugLog('Reupload Gallery', pid);
                $this.showUploadComments(pid,1,0,function(){
                    setTimeout(function(){
                        dataMedia = $this.galleryMediaData && $this.galleryMediaData[pid];
                        fnPrepareGallery();
                    },10)
                },groupId, is_access_offset_all)
            } else {
                fnPrepareGallery();
                $this.showUploadComments(pid,1,0,false,groupId, is_access_offset_all);
            }
            fnShowGallery();
        }

        if (reloadData && dataCustom) {//Events notif
            $this.checkNeedLiveCredit(dataCustom, fnStart, e);
        } else {
            $this.checkNeedLiveCredit(dataMedia, fnStart, e);
        }

        //fnStart();
        //fnShowGallery();

    }

    this.closeGalleryPopup = function(){
        if(!$this.isShowGallery)return;
        if(!backStateHistory()){
            $this.closeGallery();
        }
    }

    this.closeGallery = function(){
        $this.$ppGallery.modal('hide');
    }

    this.initInputPostReply = function(cid) {
        if(!cid)return;
        initAutoSize($('.textarea','#comment_replies_post_'+cid),function(){});
        //$('.textarea','#comment_replies_post_'+cid).autosize({isSetScrollHeight:false,callback:function(){}})
    }

    this.startVideoParams={};
    this.videoPlayer;
    this.videoPlayerNative;
    this.autoPlayVideo='autoplay';
    this.currentFormatVideo='';
    this.stopVideoPlayer = function() {
        /*if(typeof $this.videoPlayer =='object' && typeof $this.videoPlayer.dispose == 'function'){
            $this.videoPlayer.pause();
        }
        if(typeof $this.videoPlayerNative =='object'){
            $this.videoPlayerNative.pause();
        }*/

        var pl;
        for(var k in videoPlayers) {
            pl = videoPlayers[k];
            if (isPlayerNative) {
                if (!pl.paused) pl.pause();
            } else if (typeof pl =='object' && typeof pl.dispose == 'function'){
                if (!pl.paused()) pl.pause();
            }
            delete videoPlayers[k];
        }

        setTimeout(function(){$this.$ppGalleryOneBl.find('video, object, script').remove()},150);
    }

    this.videoFilePlayFailed = function() {
        setTimeout(function(){
            $this.$ppGallery.one('hidden.bs.modal',function(){
                setTimeout(function(){
                    alertCustom(l('video_file_playback_failed'));
                },200)
            }).modal('hide')
            debugLog('Video ERROR');
        },500)
    }

    this.setVideoPlayer = function(pid,dataMedia,direct) {
        if (direct) {
            if(!$this.curPid)return false;
            direct=direct=='left'?'next_id':'prev_id';
            pid=pid||$this.galleryMediaData[$this.curPid][direct];

            dataMedia=$this.galleryMediaData[pid];
            $this.curPid=pid;

            $this.showConrolsComment(pid);
            $this.setArrowsTitle(pid);
            $this.updatePhotoInfo(pid);
            $this.showUploadComments(pid);
        }
        var id=$this.getVideoId(pid),
            params=$this.startVideoParams;

        if (params.id!=id) params={};
        var src=params.poster||(urlFiles+dataMedia['src_src']),
            $media=$this.$ppGalleryOneBl.removeClass('ready'), d=400, loaded=0;

        var debugBuffer=false;
        function initTestBuffered(video){
            if(!debugBuffer)return;
            $('#media_buffer_test').show();
            video.addEventListener('timeupdate', function() {
                var duration=video.duration;
                if(duration>0){
                    $('#video_progress')[0].style.width=((video.currentTime/duration)*100)+"%";
                }
            })
            video.addEventListener('progress', function() {
                var duration=video.duration;
                if(duration>0){
                    for (var i = 0; i < video.buffered.length; i++) {
                        //console.log(video.buffered.start(video.buffered.length - 1 - i), video.currentTime);
                        if (video.buffered.start(video.buffered.length - 1 - i) < video.currentTime) {
                            $('#video_buffer')[0].style.width=(video.buffered.end(video.buffered.length-1-i)/duration)*100+"%";
                            break;
                        }
                    }
                }
            })
        }

        if(isPlayerNative){
            var $videoNative=$('.video_native', $media);
            if(!$videoNative[0]){
                if(mobileAppLoaded) {
                    /*var srcV=dataMedia.src_v,
                        idV=dataMedia.video_id,
                        frmt=/.+\.([^\?#]+)/.exec(srcV)[1],
                        html='<video style="opacity:0;" class="video_native" id="user_video_'+idV+'_gallery" preload="metadata" webkit-playsinline="webkit-playsinline" controls poster="'+urlFiles+dataMedia.src_src+'">'+
                                '<source src="'+urlFiles+srcV+'" type="video/'+frmt+'"/>'+
                             '</video>';
                    $videoNative=$(html).prependTo($media);
                    initNativeVideoPlayer(idV+'_gallery');*/
                    var html=dataMedia['html_code'];
                    html +='<div class="video_native_poster" style="background-image: url(\''+urlFiles+dataMedia.src_src+'\')">'+
                                '<button class="play_button" type="button" aria-live="polite"></button>'+
                           '</div>';
                    $videoNative=$(html);
                    var $mediaV=$videoNative.not('script, .video_native_poster'),
                        autoPlay=$mediaV.attr('autoplay')!==undefined && !$this.showCommentId;

                    $mediaV.addClass('to_hide');
                    $mediaV.attr({muted: true, preload: 'metadata'});//crossorigin:'anonymous', playsinline: true,
                    $mediaV.data('autoplay', autoPlay);
                    $mediaV.removeAttr('autoplay');

                    /*
                    type:"video/mp4; codecs='avc1.42E01E, mp4a.40.2'"
                    <video autobuffer="true"
                           x-webkit-airplay="allow"
                           controlslist="nodownload"
                           controls=""
                           playinfullscreen="false"
                           src="https://video.fiev4-1.fna.fbcdn.net/v/t42.9040-2/43037400_371099033465046_3215103816458305536_n.mp4?_nc_cat=107&amp;efg=eyJ2ZW5jb2RlX3RhZyI6InN2ZV9zZCJ9&amp;_nc_ht=video.fiev4-1.fna&amp;oh=f6e9dd4865c29acc5746f566f28cd004&amp;oe=5C61E04C">
                    </video>*/

                    $('object',$videoNative).remove();
                    initTestBuffered($videoNative[0]);
                    $media.prepend($videoNative);
                    if (detectApiFullScreen()) {
                        changeFullScreen($mediaV[0],function(){
                            if(isFullScreen()){
                                $jq('body').addClass('full-screen-mode-app');
                            } else {
                                $jq('body').removeClass('full-screen-mode-app');
                                if (versionAndroid=='6.0.1') {//Fix for android 6.01
                                    if ($mediaV[0].paused){
                                        $mediaV[0].play();
                                        $mediaV[0].pause();
                                    } else {
                                        $mediaV[0].pause();
                                        $mediaV[0].play();
                                    }
                                }
                            }
                        })
                    }
                } else {
                    $videoNative=$(dataMedia['html_code']);
                    if($this.showCommentId)$videoNative.removeAttr('autoplay');
                    $media.addClass('ready');
                    initTestBuffered($videoNative[0]);
                    $media.prepend($videoNative);
                }
                $this.startVideoParams={};

            }else{//No use EDGE
                $videoNative.stop().fadeTo(d,0,function(){
                    $('>:not(.css_loader)',$media).remove();
                    $(dataMedia['html_code']).fadeTo(0,0).prependTo($media)
                    .delay(100).fadeTo(d,1);
                    $media.addClass('ready');
                    $this.startVideoParams={};
                })
            }
            //$this.applyNativePlayerParams(params);
            loaded=1;
        }else{
            //popcorn modified s3 bucket photo 2024-05-03
            var srcV=params.src||(dataMedia['src_v']),
                frmt=/.+\.([^\?#]+)/.exec(srcV)[1];
            if (!$this.videoPlayer || !$this.videoPlayer.dispose || !$('#video-js', $media)[0]){
                if ($this.videoPlayer && $this.videoPlayer.dispose){
                    $this.videoPlayer.dispose();
                }
                loaded=1;
                $media.prepend($this.getVideoCode());

                $this.currentFormatVideo=frmt;
                $this.videoPlayer=videojs('#video-js').volume(getVolumeVideoPlayer())
                .on('fullscreenchange', function() {
                    //$this.$ppGallery.toggleClass('full-screen-mode');
                }).on('ended', function() {
                    debugLog('Video ENDED');
                    var th=this;
                    setTimeout(function(){
                        th.load();
                        th.pause();
                    },250)
                }).on("error",function(){

                }).on("volumechange",function(){
                    if(this.muted()){
                        this.volume(0);
                    }
                    //setCookie('videojs_volume', $this.videoPlayer.volume());
                    $.cookie('videojs_volume', $this.videoPlayer.volume(), {path:'/'});
                }).on('loadedmetadata', function(){this.controls(true)});
            }
            var $mediaVideo=$('#video-js', $media);
            if($mediaVideo.is('.to_hide')){
                loaded=2;
            } else {
                $mediaVideo.oneTransEnd(function(){
                    if (!$(this).is('.to_hide')) return;
                    loaded=2;
                    $this.videoPlayer.pause();
                    if (img && img[0].complete) {
                        img.load()
                    }
                }).addClass('to_hide');
            }
        }
        //to move in the event -> .on('ready')
        if (!isPlayerNative) {
            var img=$('<img />').on('load',function(){
                if (!loaded) return;
                loaded=0;
                //in the chain of functions does not work in chrome(Mazilu error in the console), each separately connected
                $this.videoPlayer.poster(src).src({type: "video/"+frmt, src:srcV});
                $this.videoPlayer.controls(!$this.autoPlayVideo||params.paused);
                $this.videoPlayer.currentTime(params.currentTime);
                var isAutoPlay = ($this.autoPlayVideo&&params.currentTime!==0||params.currentTime)&&!$this.showCommentId;
                $this.videoPlayer[isAutoPlay?'play':'pause']();
                if (params.currentTime) $this.videoPlayer[(params.paused)?'pause':'play']();
                $mediaVideo.delay(100).removeClass('to_hide',0);
                $media.addClass('ready');
                $this.startVideoParams={};
            }).prop('src',src);
        }

        //$.post(url_ajax+'?cmd=increase_view_count_video',{vid:id,user_id:uid}, checkDataAjax);

        $this.replaceHistory(id);
    }

    this.replaceHistory = function(id,link) {
        try {
            if (!link && id) {
                var link=location.href.split('#');
                link=link[0]+'#site_video:'+id;
            }
            if (link) {
                history.replaceState(history.state, document.title, link);
            }
        } catch(e) {};
    }

    this.getVideoCode = function() {
        return '<video id="video-js" class="video-js vjs-default-skin to_hide" preload="auto" data-setup="{&quot;example_option&quot;:true}" />'
    }

    this.applyNativePlayerParams = function(params) {
        var player=videoPlayers[params.id+'_gallery'];
        if(player){
            $this.videoPlayerNative=player;
            player.currentTime=params.currentTime;
            player[($this.autoPlayVideo&&params.currentTime!==0||params.currentTime)?'play':'pause']();
            if (params.currentTime) player[(params.paused)?'pause':'play']();
        }
    }

    this.scrollTop = function(delay) {
        delay=delay||1;
        var top=$this.$ppGallery[0].scrollTop,t=200;
        if(top>450)t=450;
        $this.$ppGallery.stop().delay(delay).animate({scrollTop:0},t,'easeInOutCubic')
    }

    this.loaderGalleryShow = function(){
        $this.$ppGalleryLoader.stop().delay(200).removeClass('hidden', 0)
    }

    this.loaderGalleryHidden = function(){
        $this.$ppGalleryLoader.stop().addClass('hidden',0);
    }

    this.checkPreloadData = function(curPid, direct){
        //left - next
        //right - prev
        var offset=direct=='left'?'last_offset':'first_offset',
            curOffset=$this.galleryMediaData[curPid]['offset'],
            toOffset=$this.offsetInfo[offset],
            maxOffset=$this.offsetInfo['max_offset'],
            i,noViewed=0,limit=5;

        //debugLog('Gallery checkPreloadData Param', [curOffset, toOffset, direct]);
        if (direct=='left') {
            if (toOffset > curOffset) {
                for(i=curOffset+1; i < toOffset; i++){
                    //debugLog('1Gallery checkPreloadData Last noViewed "i"', i);
                    noViewed++;
                }
            } else {
                for(i=curOffset; i < maxOffset; i++){
                    //debugLog('2Gallery checkPreloadData Last noViewed "i"', i);
                    noViewed++;
                }
                for(i=0; i < toOffset; i++){
                    //debugLog('3Gallery checkPreloadData Last noViewed "i"', i);
                    noViewed++;
                }
            }
        } else {
            if (toOffset > curOffset) {
                var curOffset1=curOffset-1;
                if (curOffset1 > 0) {
                    for(i=curOffset1; i > 0; i--){
                        //debugLog('3Gallery checkPreloadData First noViewed "i"', i);
                        noViewed++;
                    }
                } else if (curOffset1 < 0) {
                    maxOffset--;
                }

                for(i=maxOffset; i >= toOffset; i--){
                    //debugLog('4Gallery checkPreloadData First noViewed "i"', i);
                    noViewed++;
                }
            } else {
                for(i=curOffset-1; i > toOffset; i--){
                    //debugLog('5Gallery checkPreloadData First noViewed "i"', i);
                    noViewed++;
                }
            }
        }
        debugLog('Gallery checkPreloadData', [curPid, noViewed, limit >= noViewed, $this.galleryMediaOffset]);

        return limit >= noViewed;
    }

    this.show = function(direct, pid, arrow){
        if ($this.ppGalleryIsVideo) {
            $this.setVideoPlayer(pid||0, false, direct);
            return;
        }
        if(!$this.photoLoad||!$this.curPid)return false;

        $this.hideFace();

        $this.saveEditDesc();
        var paramPid=pid||0,
            curPid=$this.curPid;
        arrow=arrow||false;
        var _dir=direct=='left'?'right':'left',
            nextPid = pid||$this.galleryMediaData[$this.curPid][direct=='left'?'next_id':'prev_id'],
            dir=_dir;


        if(arrow)_dir=direct='';
        //console.log(nextPid,pid,dir,direct=='left'?'next_id':'prev_id',$this.galleryMediaData,$this.galleryMediaData[nextPid].load);
        if(!$this.galleryMediaData[nextPid]||!$this.galleryMediaData[nextPid].load) return false

        $this.photoLoad=false;
        var isPrivatePrevImg=false;
        if(!paramPid)isPrivatePrevImg=!$this.isPublic($this.curPid);
        if($this.galleryMediaData[$this.curPid])$this.galleryMediaData[$this.curPid]['show'] = 1;
        $this.curPid=nextPid;
        $this.curDataOpenGallery['pid']=$this.curPid;
        //$this.$photoInfo.stop().fadeTo(0,0);

        var img0=[],pr,isPublic=$this.isPublic(nextPid);
        if($this.$ppGalleryOneImg[0]){
            img0=$this.$ppGalleryOneImg.addClass(direct);
        }

        if(!isPublic)$this.photoLoad=pr=true;

        var img=$this.$ppGalleryOneImg=$this.galleryMediaData[nextPid].load.removeClass(direct);

        var infoImg=$this.galleryMediaData[nextPid];
        $this.showConrolsComment(nextPid);
        $this.setArrowsTitle(nextPid);
        $this.updatePhotoInfo(nextPid);

        if($this.isEHP()) {
            isPublic = true;
        }

        if(isPublic){
            var pagePreload=false;
            $this.galleryMediaData[nextPid]['show'] = 1;
            //debugLog('Gallery PRELOAD PHOTO STOP', $this.stopPreloadPhoto);
            if (!$this.stopPreloadPhoto && $this.galleryMediaData[curPid]) {
                if ($this.checkPreloadData(curPid, direct)) {
                    pagePreload=direct;
                    console.log('PRELOAD PHOTO DATA', nextPid, curPid, direct);
                }
            }
            if($jq('body').is('.ie11')){
                setTimeout(function(){
                    $this.showUploadComments(nextPid, 0, pagePreload);
                },300)
            } else {
                $this.showUploadComments(nextPid, 0, pagePreload);
            }
        }
        //$jq('.pp_gallery_overflow',$this.$ppGallery).stop().delay(750).animate({scrollTop:0},500,'easeOutCubic');

        /*if($this.display == 'profile' && !isOneLoad && $jq('#main')[0].scrollTop){
            var top=$jq('#main')[0].scrollTop;
            if(top<200)top=200;if(top>450)top=450;
            $jq('#main').stop().delay(750).animate({scrollTop:0},top,'easeOutCubic');
        }*/

        $this.$ppGalleryContainer.addClass('change_photo');
        $this.preLoadingPhotos(dir);

        var oneLoadImage=function(event){

            $this.replacePhotoDefaultInput(nextPid, $this.galleryMediaData[nextPid]);

            event=event||'transform';
            if($jq('body').is('.ie11')){
                event='';
            }
            var loadImage=function(event){
                //$this.resImg(img[0]);
                if(!_isFaceDetectionLoad && $this.checkDetectFace(infoImg)){

                } else {
                    $this.$ppGalleryContainer.removeClass('change_photo');
                }

                //$this.$ppGalleryLoader.stop().addClass('hidden',0);
                img.stop().show().delay(10).removeClass('left right hidden', 0).oneTransEnd(function(){
                    if(img0==$this.$ppGalleryOneImg) return;
                    img.addClass('ready');
                    img0[0]&&img0.remove().removeClass(direct);
                    if(!isPrivatePrevImg&&!isPublic){
                        setTimeout(function(){$this.$ppGalleryOneImg.appendTo($this.$ppGalleryCont.removeAttr('style'))},10);
                        $this.$ppGalleryCont.css({transition: '0s', transform: 'none', opacity:1}).removeClass('left right anim');
                    }
                    $this.renderFace(infoImg, img[0].src);
                }, event);
                $this.photoLoad = true;
            }
            img.addClass(_dir).hide().prependTo($this.$ppGalleryContainer).one('load', function(){
                loadImage(event);
            });
            if(pr||img[0].complete)img.load();
        }

        if (arrow) {
            if(img0==$this.$ppGalleryOneImg) return;
            img0.oneTransEnd(function(){
                $this.loaderGalleryShow();
                oneLoadImage('opacity')
            }).addClass('hidden');
            return true;
        }
        $this.loaderGalleryShow();
        setTimeout(function(){oneLoadImage()}, 150);
        return true
    }

    this.activateSwipeGallery = function() {
        var $container=$this.$ppGalleryContainer,
            $cont=$('<div class="bl_img trans"/>').insertBefore($container),
            dW=100,abs=0,_abs=0,el,_swipe, allowPageScroll='vertical',
            preventDefaultEvents=true;

        $this.$ppGalleryCont=$cont;
        if(isIframeDemo)preventDefaultEvents=false;

        $this.$ppGalleryOneBl.css({overflow: 'hidden'}).swipe({
                swipeStatus:function(e,ph,dir,d,duration,c,f) {
                    /* Face */
                    if($this.$ppGalleryFriendListBl[0]
                        && $this.$ppGalleryFriendListBl.is('.to_show,.animated')) return;
                    //if('.face_box:visible') return;
                    /* Face */
                    if (ph=='start') {
                        _swipe=$this.isSwipe=true;
                        el=$this.$ppGalleryOneImg.not('.hidden, .left, .right');
                        //console.log(el,el.closest($container)[0]);
                        if (el.closest($container)[0]) {
                            setTimeout(function(){if (el) el.appendTo($cont.removeAttr('style'))}, 10);
                            $cont.off(transEvent).css({transition: '0s', transform: 'none', opacity:1}).removeClass('left right anim');
                        }
                        $this.$ppGalleryOneBl.addClass('moving');
                        //$this.$ppGalleryLoader.addClass('hidden');
                        $this.$ppGalleryContainer.removeClass('change_photo');
                        $this.hideMoreMenu();
                        //if($this.display=='profile'){
                            //$this.toggleMoreMenu(true);
                            //$this.$photoFrmPostCommentText.blur();
                       // }
                        return;
                    }
                    var isAnimPhoto=/left|right/.test(dir);
                    if (ph=='move'&&_swipe) { _swipe--;
                        if ($this.countAllMedia<2) return false;
                        if (!el[0] && dir!='left' && !/mouse/.test(e.type)) return false;
                    }
                    if (/end|move/.test(ph)&&isAnimPhoto) {
                        var ds=f[0].end.x-f[0].start.x,
                            dr=ds>0?'right':'left',
                           _dr=ds<0?'right':'left';
                        if (ph=='move') _abs=abs;
                        abs=Math.abs(ds);
                        /* Fix IPhone*/
                        if(f[0].end.x==f[0].start.x && d){
                            abs=d;
                            dr=dir;
                            _dr=dir=='left'?'right':'left';
                            ds=dir=='left'?-1:1;
                        }
                        /* Fix IPhone*/
                        if(el && abs>1)$cont.not('.anim').removeClass(_dr).addClass(dr+' anim');
                        if(el && abs>dW*.1)$cont.not('.'+dr).removeClass(_dr).addClass(dr);
                        if(el && abs<dW*.1 && _abs>abs){
                            $cont.removeClass(dr);
                        };
                        if(abs>dW&&el){
                            $this.swipeCallback(e, dr);
                            $this.isSwipe=el=abs=_abs=0;
                            return false;
                        }
                    }
                    if (/end|cancel/.test(ph)) {
                        if ((abs>dW*.4 || _abs<abs) && el && $cont.hasClass(dr) && ph=='end') {
                            if(el&&isAnimPhoto)$this.swipeCallback(e, dr);
                        } else {
                            if(el){
                                $cont.removeClass('left right anim');
                            }
                        }
                        $this.isSwipe=el=abs=_abs=0;
                        if (ph=='end' && d<2 && !$cont.is('.anim')) $this.swipeCallback(e);
                    }
                }, threshold:0,
                excludedElements: 'button, a, #photo_gallery_set_default, #photo_gallery_report, #request_access, .icon_close, .face_box, .bl_gallery_friend_list, .layer_blocked_gallery',
                allowPageScroll:allowPageScroll,
                preventDefaultEvents:preventDefaultEvents
        })
    }

    this.swipeCallback = function(e, direct) {
        $this.$ppGalleryOneBl.removeClass('moving');
        if(!$this.photoLoad||!$this.curPid||$this.countAllMedia<2)return;
        if (direct) {
            //if ($('.tip_alert:visible')[0])return;
            //if ($this.uid != $this.guid && $this.isBlockedUser && activePage == 'profile_view.php') return;
            if (!$this.show(direct)) $this.$ppGalleryCont.removeClass('left right')
            else{$this.loaderGalleryShow()}
        } else {
            if($(e.target).is('#pp_gallery_photos_img_box')){
                var offset = $this.$ppGalleryContainer.offset(),
                    x=(e.pageX - offset.left),wB=$this.$ppGalleryContainer.width(),
                    xC=wB/2,dir='left';
                if(Math.abs(xC-x)<100 || x > xC){
                    dir='right';
                }
                $this.show(dir)
            }
            //if ($(e.target).filter('#request_access button').click()[0]) return;
        }
    }

    this.commentsRepliesLoadMore = function($el,limit){
        limit=limit||0;
        if($el.is('.disabled')) return;
        $el.addClass('disabled');
        var $icon=$el.find('.icon').addChildrenLoader(),
            cid=$el.data('cid'),
            pid=$this.curPid,
            $listReplies=$('#comments_replies_list_'+cid),
            $loadReplies=$('#comments_replies_load_'+cid),
            $loadRepliesNumber=$loadReplies.find('.comm_to_comm_text_number'),
            lastId=$listReplies.find('.comments_replies_item').first().data('rcid');
        if(!lastId)lastId=0;

        var dataRes={type:$this.getTypeGallery(), load_more:1, last_id:lastId, comment_id:cid, limit:limit};

        var fnLoad=function(){
            debugLog('Gallery commentsRepliesLoadMore', dataRes);
            photoCmd = $this.getPhotoCmd();

            $.ajax({url:url_ajax+'?cmd=get_comment_replies' + photoCmd,
                type: 'POST',
                data: dataRes,
                timeout: globalTimeoutAjax,
                //cache: false,
                success: function(res){
                    if($this.noAction(pid))return;
                    var data=checkDataAjax(res);
                    if(data){
                        var $data=$(data),
                            $comments=$data.find('.comments_replies_item').hide(),
                            $listReplies=$('#comments_replies_list_'+cid);
                        if(!$listReplies[0]||!$comments[0])return;

                        var $numberView=$loadReplies.find('.number_view'),
                            numberStart=$numberView.text()*1,
                            numberAll=$data.find('.number_all').text()*1;

                        if (numberAll) {
                            $loadReplies.find('.number_all').text(numberAll);
                            if (!$loadRepliesNumber.is('.to_show')) {
                                $el.find('.comments_replies_load_title').text(l('view_previous_replies'));
                                $loadRepliesNumber.addClass('to_show');
                            }
                        } else {
                            $this.hideReplyLoad($loadRepliesNumber);
                        }

                        var $comment,i=0,t=300,i=$comments.length-1;
                        (function fu(){
                            $comment=$comments.eq(i).show();
                            if(!$comment[0]||i<0)return;
                            if(!$('#'+$comment[0].id)[0]){
                                if (numberAll) {
                                    numberStart++;
                                    if(numberStart>=numberAll){
                                        $this.hideReplyLoad($loadReplies);
                                        //$loadReplies.find('.comments_replies_load_link').addClass('disabled');
                                        numberStart=numberAll;
                                    }
                                    $numberView.text(numberStart);
                                }
                                clMediaTools.addCommentToBl($comment, cid, 'prependTo', false, '#comments_replies_list_');
                            }
                            i--;fu();
                        })()
                    }else{
                        alertServerError(true)
                    }
                    $icon.removeChildrenLoader();
                    $el.removeClass('disabled');
                },
                error: function(xhr, textStatus, errorThrown){
                    if($this.noAction(pid))return;
                    globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                        if($this.noAction(pid))return;
                        fnLoad();
                    })
                },
            })
        }
        fnLoad();

        return;
        /*$.post(url_ajax+'?cmd=get_comment_replies&type=' + $this.getTypeGallery(),
              {load_more:1, last_id:lastId, comment_id:cid, limit:limit},
              function(res){
            if($this.noAction(pid))return;
            var data=checkDataAjax(res);
            if(data){

                var $data=$(data),
                    $comments=$data.find('.comments_replies_item').hide(),
                    $listReplies=$('#comments_replies_list_'+cid);
                if(!$listReplies[0]||!$comments[0])return;

                var $numberView=$loadReplies.find('.number_view'),
                    numberStart=$numberView.text()*1,
                    numberAll=$data.find('.number_all').text()*1;

                if (numberAll) {
                    $loadReplies.find('.number_all').text(numberAll);
                    if (!$loadRepliesNumber.is('.to_show')) {
                        $el.find('.comments_replies_load_title').text(l('view_previous_replies'));
                        $loadRepliesNumber.addClass('to_show');
                    }
                } else {
                    $this.hideReplyLoad($loadRepliesNumber);
                }

                var $comment,i=0,t=300,i=$comments.length-1;
                (function fu(){
                            $comment=$comments.eq(i).show();
                            if(!$comment[0]||i<0)return;
                            if(!$('#'+$comment[0].id)[0]){
                                if (numberAll) {
                                    numberStart++;
                                    if(numberStart>=numberAll){
                                        $this.hideReplyLoad($loadReplies);
                                        //$loadReplies.find('.comments_replies_load_link').addClass('disabled');
                                        numberStart=numberAll;
                                    }
                                    $numberView.text(numberStart);
                                }
                                clMediaTools.addCommentToBl($comment, cid, 'prependTo', false, '#comments_replies_list_');
                            }
                            i--;fu();
                })()
            }else{
                alertServerError(true)
            }
            $icon.removeChildrenLoader();
            $el.removeClass('disabled');
        })*/
    }


    this.hideReplyLoad = function($el){
        $el.closest('.comments_reply_load').slideUp($this.dur,function(){
            $(this).removeClass('to_show').removeAttr('style')
        })
    }

    this.loadMoreComments = function(limit, $el){
        limit=limit||0;
        $el=$el||[];
        var $bl=$('#pp_gallery_load_more_comments_bl');
        if(!$bl[0] || $bl.is('.disabled'))return;

        var $firstComment=$this.$ppGalleryComments.find('.pp_gallery_comment_item:first');
        if(!$firstComment[0])return;
        $bl.addClass('disabled');

        var $icon=[];
        if($el[0]){
            $icon=$el.find('.icon').addChildrenLoader();
        }
        var pid=$this.curPid,
            cmd=$this.ppGalleryIsVideo?'get_video_comment':'get_photo_comment',
            uid=$this.uid,
            lastId=$firstComment.data('cid');

        var fnLoad=function(){
            var dataRes={uid:uid, photo_id:pid,
                         load_more:1, last_id:lastId, limit:limit}
            debugLog('Gallery loadMoreComments', dataRes);
            photoCmd = $this.getPhotoCmd();

            $.ajax({url:url_ajax+'?cmd=' + cmd + photoCmd,
                    type:'POST',
                    data:dataRes,
                    timeout: globalTimeoutAjax,
                    //cache: false,
                    success: function(res){
                        if($this.noAction(pid))return;
                        var data=checkDataAjax(res);
                        if(data){
                            var $data=$(data),
                            $comments=$data.find('.item').hide();
                            if($comments[0]){
                                var $numberView=$bl.find('.number_view'),
                                    count=$numberView.text()*1,
                                    countAll=$bl.find('.number_all').text()*1,
                                    $blComments=$this.$ppGalleryComments.find('.bl_comments'),
                                    $comment,i=0;
                                (function fu(){
                                    $comment=$comments.eq(i).show();
                                    if(!$comment[0])return;
                                    if(!$('#'+$comment[0].id)[0]){
                                        if($numberView[0]){
                                            count++;
                                            if (count == countAll) {
                                                $bl.stop().slideUp($this.dur,function(){
                                                    $bl.remove()
                                                })
                                            } else {
                                                $numberView.text(count);
                                            }
                                        }
                                        clMediaTools.addCommentToBl($comment, 0, 'prependTo', $this.showLastFieldComment, '', $blComments);
                                    }
                                    i++;fu();
                                })()
                            }
                        }
                        setTimeout(function(){
                            $icon[0]&&$icon.removeChildrenLoader();
                            $bl.removeClass('disabled');
                        },200)
                    },
                    error: function(xhr, textStatus, errorThrown){
                        if($this.noAction(pid))return;
                        globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                            if($this.noAction(pid))return;
                            fnLoad()
                        })
                    },
            })
        }
        fnLoad();
    }

    this.showUploadCommentsEnd = function(){
        var sel='#pp_gallery_comment_'+$this.showCommentId,
            $toComment=$(sel),
            $toCommentH=$toComment.find('.comment_text_cont');
        if(!$toComment[0]){
            sel='#comments_replies_item_'+$this.showCommentId;
            $toComment=$(sel);
            $toCommentH=$toComment.find('.comment_text_reply_one');
        }
        clMediaTools.highlightEvent($toCommentH);

        setTimeout(function(){
            //if(!$toComment[0])sel='#pp_gallery_comments_bl';
            $this.scrollToNative($toComment);
            $this.$ppGalleryLoader.show();
        },50)
        return $toComment[0];   
    }

    this.showUploadComments = function(pid,isUpdateData,direct,callRes,groupId, is_access_offset_all = false){
        groupId=groupId||0;
        var cmd='get_photo_comment',pidD=pid;
        if ($this.ppGalleryIsVideo) {
            cmd='get_video_comment';
            pid=$this.getVideoId(pid);
        }
        isUpdateData=isUpdateData||0;
        //$this.$ppGalleryCommentText.prop('disabled',true);
        $this.$ppGalleryCommentText.data('disabled', true).trigger('disabled');
        $this.$ppGalleryCommentsBl.addClass('to_hidden');

        if (!$this.$ppGallery.is('.first_update')) {
            $('.photo_one_comments').stop().fadeTo(0,0);
        }

        var dataMedia = $this.galleryMediaData[pidD] || $this.visibleMediaData[pidD];
        var dataRes={uid:$this.uid,
                photo_id:pid,
                photo_cur_id:pid,
                group_id: groupId ? groupId : ((dataMedia && dataMedia['group_id'])  ? dataMedia['group_id'] : 0),  // popcorn mmodified for group image scroll 2024-05-23
                get_data_edge:1,
                load_more:0,
                last_id:0,
                limit:0,
                // offset_media:$this.mediaOffset,
                show_comment_id: $this.showCommentId};
        
        direct=direct||0;
        if (direct) {
            isUpdateData=true;
            dataRes['get_data_edge']=1;
            dataRes['page_preload_limit']=$this.pagePreloadLimit;
            dataRes['page_preload_direct']=direct;
            //data['page_preload_count_all']=$this.countAllMedia;
        }

        photoCmd = '';
        photoCmd = $this.getPhotoCmd();
        ehp_type = $this.getEHPType();

        //when event notification is in ehp(event, hotdate, partyhou) pages
        if(is_access_offset_all) {
            photoCmd = '';
        }

        //popcorn modified 2024-08-05 start
        if(ehp_type == 'event') {
            url = location.href;
            const urlParams = new URLSearchParams(new URL(url).search);
            const eventId = urlParams.get('event_id');
            dataRes['activity_id'] = eventId;
        } else if(ehp_type == 'hotdate') {
            url = location.href;
            const urlParams = new URLSearchParams(new URL(url).search);
            const hotdateId = urlParams.get('hotdate_id');
            dataRes['activity_id'] = hotdateId;
        } else if(ehp_type == 'partyhou') {
            url = location.href;
            const urlParams = new URLSearchParams(new URL(url).search);
            const partyhouId = urlParams.get('partyhou_id');
            dataRes['activity_id'] = partyhouId;
        }
        //popcorn modified 2024-08-05 end

        /* Divyesh - added on 23042024 */
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        var offset = urlParams.get('offset');

        var offset_str = 'public';
        if (offset === 'private') {
            offset_str = 'private';
        } else if (offset === 'personal') {
            offset_str = 'personal';
        } else if (!isNaN(offset) && offset > 0) {  // Check if offset is a number
            offset_str = offset;
        } else if (offset == 'custom_folders') {
            offset_str = 'folder';
        }

        if(!is_access_offset_all) {
            dataRes['offset'] = offset_str;
        }
        
        /* Divyesh - added on 23042024 */
        var fnLoad=function(){
            debugLog('Gallery showUploadComments', dataRes);
            $.ajax({url: url_ajax+'?cmd=' + cmd + photoCmd,
                    type: 'POST',
                    data: dataRes,
                    timeout: globalTimeoutAjax,
                    //cache: false,
                    success: function(res){
                        if($this.noAction(pidD))return;
                        var data=checkDataAjax(res);
                        if(data){
                            var $data=$(data);
                            $this.$ppGallery.addClass('first_update');

                            if(isUpdateData){
                                $data.filter('.init_gallery').appendTo('#update_server');
                            }

                            var fn = function(){
                                $this.$ppGalleryComments.html($data.filter('#pp_gallery_comments').html());
                                if(!$this.showCommentId)return;
                                //Events
                                if($this.$ppGalleryLoaderFrame[0]){
                                    $this.$ppGalleryLoaderFrame.oneTransEnd(function(){
                                        $(this).remove();
                                    }).addClass('to_hide');
                                    $this.$ppGalleryLoaderFrame = [];
                                }
                                $this.showUploadCommentsEnd();
                            }
                            if(typeof callRes=='function')callRes();
                            $('.photo_one_comments').delay(50).fadeTo(350,1);
                            fn();
                            $this.showLastFieldComment(false,true);
                        }else{
                            if($this.showCommentId){
                                $this.$ppGalleryLoader.show();
                            };
                            alertServerError(true)
                        }
                        $this.showCommentId=0;
                        //$this.$ppGalleryCommentText.prop('disabled',false);
                        $this.$ppGalleryCommentText.data('disabled', false).trigger('disabled');
                        $this.$ppGalleryCommentsBl.removeClass('to_hidden');
                    },
                    error: function(xhr, textStatus, errorThrown){
                        if($this.noAction(pidD))return;
                        globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                            if($this.noAction(pidD))return;
                            fnLoad();
                        })
                    },
            })
        }
        fnLoad();
        $this.mediaOffset = false;


        return;
        /*$.post(url_ajax,data,function(res){
            if($this.noAction(pidD))return;
            var data=checkDataAjax(res);
            if(data){
                var $data=$(data);
                $this.$ppGallery.addClass('first_update');

                if(isUpdateData){
                    $data.filter('.init_gallery').appendTo('#update_server');
                }

                var fn = function(){
                    $this.$ppGalleryComments.html($data.filter('#pp_gallery_comments').html());
                    if(!$this.showCommentId)return;
                    //Events
                    if($this.$ppGalleryLoaderFrame[0]){
                        $this.$ppGalleryLoaderFrame.oneTransEnd(function(){
                            $(this).remove();
                        }).addClass('to_hide');
                        $this.$ppGalleryLoaderFrame = [];
                    }
                    $this.showUploadCommentsEnd();
                }
                $('.photo_one_comments').delay(50).fadeTo(350,1);
                fn();
                $this.showLastFieldComment(false,true);
            }else{
                if($this.showCommentId){
                    $this.$ppGalleryLoader.show();
                };
                alertServerError(true)
            }
            $this.showCommentId=0;
            $this.$ppGalleryCommentText.prop('disabled',false);
            $this.$ppGalleryCommentsBl.removeClass('to_hidden');
        })
        $this.mediaOffset = false*/
    }

    /* -------------------------  */
    this.scrollToNative = function($el,call,t){
        t=defaultFunctionParamValue(t, $this.dur*1.5);
        $this.$ppGalleryOverflow.scrollTo($el, t, {axis:'y', interrupt:true, easing:'easeOutExpo', over_subtract:{top:3}, onAfter:call});
    }

    this.scrollToInto = function($el,t){
        t=defaultFunctionParamValue(t, $this.dur);
        $this.scrollToNative($el,false,t);
        return;
        /*$this.$ppGalleryOverflow.stop(true,true);
        $el.get(0).scrollIntoView(false);*/
    }

    this.scrollBottomAnimationFrame = function(){
        globalID = requestAnimationFrame($this.scrollBottomAnimationFrame);
        $this.scrollBottomAnimation();
    }

    this.scrollBottomAnimation = function(){
        var $el=$this.$ppGalleryOverflow;
        $el[0].scrollTop = $el[0].scrollHeight;
    }

    this.scrollBottomNative = function(call){
        $this.$ppGalleryOverflow.scrollTo('max', $this.dur*1.5, {axis:'y', queue:false, easing:'easeInOutCubic', onAfter:call});
    }
    /* -------------------------  */


    this.sel = {
        replies_post : 'comment_replies_post_',
        replies_input : '#comment_replies_input_'
    }

    this.showFrmReply = function(el){
        var $el=$(el);
        clMediaTools.showFrmReplyComment($el, $this.sel.replies_post+$(el).data('cid'), false, true);
    }

    this.hideFrmReply = function(id, call){
        clMediaTools.hideFrmReplyComment($this.sel.replies_post+id, false, true, call)
    }

    this.initFeedCommentReplies = function(id) {
        var $inp=$($this.sel.replies_input+id);
        if(!$inp[0])return;
        initAutoSize($inp,$this.commentAdd);

        var $btn=$inp.nextAll('.comment_action').find('.wall_post_send');
        clMediaTools.initTextareaControl($inp, $btn);
        $btn.click(function(){
            $this.commentAdd($inp);
        })

        //$inp.autosize({isSetScrollHeight:true,callback:function(){}}).keydown(doOnEnter($this.commentAdd));
    }

    this.fnBlur = function($inp, $comment, rCid) {
        var d = isMobileSite ? 150 : 1,
            dt = isMobileSite ? evWndResTime : 1;
        setTimeout(function(){
            $inp.blur();
            setTimeout(function(){
                if (rCid) {
                    if (!$this.inViewport($comment[0])) {
                        $this.scrollToNative($comment, false, $this.dur);
                    }
                } else {
                    $this.scrollBottomAnimation();
                }
            },dt)
        },d)
    }


    this.updateCommentCounter = function($bl, inc){
        if ($bl && $bl[0]) {
            var $loadReplies=$bl;
        } else {
            var $loadReplies=$('#pp_gallery_load_more_comments_bl');
        }

        var $loadRepliesNumber=$loadReplies.find('.comm_to_comm_text_number');

        if (!$loadRepliesNumber.is('.to_show')) {
            return;
        }

        inc=inc||1;

        var $numberView=$loadRepliesNumber.find('.number_view'),
            numberStart=$numberView.text()*1 + inc,
            $numberAll=$loadRepliesNumber.find('.number_all'),
            numberAll=$numberAll.text()*1 + inc;

            $numberView.text(numberStart);
            $numberAll.text(numberAll);
    }

    this.commentAdd = function(inp) {
        var $inp=$(inp),$commentAction=[];
        if($inp.is('button')){
            $commentAction=$inp.closest('.comment_action');
            $inp=$commentAction.prev('.textarea');
        }

        var sticker=$inp.data('sticker');
        if (typeof sticker != 'object' || typeof sticker.code == 'undefined') {
            sticker=false;
        }

        if(!$commentAction[0]){
            $commentAction=$inp.next('.comment_action');
        }

        var audioMessageId=0,
            $plAudio=$commentAction.find('.im_audio_message_recorder'),
            audioProcess=false;
        if (!sticker && $plAudio[0]) {
            var audioBlId='#'+$inp.closest('.comment_container_textarea')[0].id;
            audioProcess=mediaRecorderProcess[audioBlId]!=undefined&&mediaRecorderProcess[audioBlId];
        }

        var imageUploadBlId=0,
            uploadImageLoaded=0,
            uploadImageProcess=false,
            $uploadImage=$commentAction.find('.comment_upload_img');
        if (!sticker && $uploadImage[0]) {
            imageUploadBlId=$uploadImage[0].id;
            if (_addCommentImage[imageUploadBlId]!=undefined) {
                uploadImageProcess=_addCommentImage[imageUploadBlId].process;
                uploadImageLoaded=_addCommentImage[imageUploadBlId].load;
            }
        }

        if (sticker) {
            var comment=sticker.code;
        } else {
            if(uploadImageProcess||audioProcess)return false;
            if ($plAudio[0]) {
                audioMessageId=$inp.data('im-audio-message-id');
                $inp.find('.im_audio_message_send_play').remove();
            }
            var comment=smileText($inp[0].innerHTML);
        }

        if (comment == $inp.data('placeholder')) {
            comment = '';
        }

        var rCid=$inp.data('cid'),
            plName=$inp.data('name'),
            commentCheck=comment;
        if (plName) {
            commentCheck=clMediaTools.checkUserNameComment(comment, plName);
        }

        if (!sticker) {
            smileBlockRemoveWall($commentAction.find('.wall_comment_smile_btn'));
            $inp.trigger('clear-caret-position');

            if((!comment||!commentCheck)&&!audioMessageId&&!uploadImageLoaded){
                $inp.text('').trigger('autosize').blur();
                if (rCid) {
                    $this.hideFrmReply(rCid);
                }
                return false;
            }
        }

        var send = +new Date,
            pid=$this.curPid,
            $comment;
        if (rCid) {
            $comment = $('<div id="comments_replies_item_'+send+'" data-rcid="'+send+'" class="comment_to_comment_container comments_replies_item">'+
                            '<div class="comment_item_wrapper">'+
                            '</div>'+
                         '</div>');
        } else {
            $comment = $('<div id="pp_gallery_comment_'+send+'" data-cid="'+send+'" class="pp_gallery_comment_item item">'+
                            '<div class="comment_item_wrapper">'+
                            '</div>'+
                         '</div>');
        }
        $comment.find('.comment_item_wrapper').append(clMediaTools.prepareComment());

        var fnAdd=function(){
            if (!sticker) {
                if ($plAudio[0] && audioMessageId) {
                    $inp.data('im-audio-message-id', 0);
                    $plAudio.removeClass('im_audio_message_delete');
                }
                clearCommentUploadImage(imageUploadBlId);
            }
            if (rCid) {
                !sticker && $this.hideFrmReply(rCid, function(){
                    //clearCommentUploadImage(imageUploadBlId);
                    $this.fnBlur($inp, $comment, rCid)
                })
            } else {
                //clearCommentUploadImage(imageUploadBlId);
                $this.showLastFieldComment(function(){

                    //$this.$ppGalleryFieldCommentBottomInput.is(':visible') && $this.$ppGalleryFieldCommentBottomInput.focus();
                    cancelAnimationFrame(globalID);
                    $this.scrollBottomAnimation();
                    $this.fnBlur($inp, $comment, rCid);
                })
            }

            comment=clMediaTools.replaceUserName(comment, $inp.data('name'), $inp.data('uid'), $inp.data('groupId'));
            comment=emojiToHtml(comment);

            var data={comment:comment,
                      photo_id:pid,
                      photo_user_id:$this.galleryMediaData[pid]['user_id'],
                      reply_id:rCid,
                      private:$this.galleryMediaData[pid]['private'],
                      audio_message_id:audioMessageId,
                      image_upload:uploadImageLoaded?1:0,
                      ind:uploadImageLoaded
            };
            if (sticker) {
                data['sticker'] = sticker.data;
            }

            photoCmd = "";
            photoCmd = $this.getPhotoCmd();

            $.post(url_ajax+'?cmd=photo_comment_add' + photoCmd, data,
            function(res){
                if ($this.noAction(pid))return;
                data=checkDataAjax(res);
                if (data!==false){
                    var $data=$(trim(data));
                    if (rCid) {
                        $data=$data.find('.comments_replies_item');
                        if(!$data[0]||$('#'+$data[0].id)[0])return;
                        var resCid=$data.data('rcid');
                    } else {
                        $data=$data.filter('.pp_gallery_comment_item');
                        if(!$data[0]||$('#'+$data[0].id)[0])return;
                        var resCid=$data.data('cid');
                    }

                    $comment.data('cid', resCid).attr({'id':$data[0].id, 'data-rcid':resCid});
                    clMediaTools.commentUpdate($comment, $data);

                    if (rCid) {
                        $this.updateCommentCounter($('#comments_replies_load_'+rCid));
                    } else {
                        $this.updateCommentCounter();
                    }
                }else{
                    //$this.commentHide(id, send, true);
                    alertServerError(true)
                }
            })
        }

        var fnSubmit=function(){
            if (rCid) {
                clMediaTools.addCommentToBl($comment, rCid, false, fnAdd, '#comments_replies_list_');
            } else {
                var fnSend = function(){
                    //$this.$ppGalleryFieldCommentBottomInput.is(':visible') && $this.$ppGalleryFieldCommentBottomInput.focus();
                    $this.scrollBottomAnimationFrame();
                    clMediaTools.addCommentToBlUpdate($comment, 0, 'appendTo', fnAdd, '', $this.$ppGalleryComments.find('.bl_comments'));
                }
                var $last=$('.pp_gallery_comment_item:last',$this.$ppGalleryComments);
                if ($inp[0] == $this.$ppGalleryFieldCommentInput[0] && $last[0] && !$this.inViewport($last[0])) {
                    $this.scrollBottomNative(fnSend)
                } else {
                    fnSend()
                }
            }
        }
        if (sticker) {
            $inp.data('sticker', false);
            fnSubmit();
        } else {
            $inp.text('').trigger('autosize',fnSubmit).focus();
        }

        return false;
    }

    this.updateRepliesCounter = function(cid, countReplies){
        var $el=$('#comments_replies_load_'+cid).find('.comm_to_comm_text_number.to_show');
        if(!$el[0])return;
        var v=$('#comments_replies_list_'+cid).find('.comments_replies_item').length;
        $el.find('.number_view').text(v);
        $el.find('.number_all').text(countReplies);
    }

    this.updatePageCounter = function(count){
        var pid=$this.curPid, $pageConter;
        if($this.ppGalleryIsVideo) {
            $pageConter=$('.video_comments_count_'+$this.prepareId(pid));
        } else {
            $pageConter=$('.photo_comments_count_'+pid);
        }
        if (count>0) {
            $this.$el['commentCount'].text(count);
            $this.$el['commentCountBl'].show();
            $pageConter[0] && $pageConter.text(count).show();
        } else {
            $this.$el['commentCountBl'].hide();
            $pageConter[0] && $pageConter.hide();
        }

        if($this.galleryMediaData[pid])$this.galleryMediaData[pid]['comments_count']=count;
        if($this.visibleMediaData[pid])$this.visibleMediaData[pid]['comments_count']=count;
    }

    /* Photo default */
    this.setPhotoDefault = function(pid,$btn){
        var isGallery=false;
        $btn=$btn||[];
        if(!$btn[0]){
            isGallery=true;
            $btn=$this.$el['linkSetDefault'];
        }
        pid=pid||$this.curPid;
        var groupId=$this.getMediaField(pid, 'group_id');

        photoCmdString = "";
        photoCmdString = $this.getPhotoCmdString();

        if($btn.find('.css_loader')[0])return;
        $.ajax({type: 'POST',
                url: url_ajax,
                data: {cmd:'set_photo_default', photo_cmd: photoCmdString, photo_id: pid, group_id:groupId, nsc_couple_id: $this.nsc_couple_id},
                beforeSend: function(){
                    if (isGallery) {
                        addChildrenLoader($btn, false);
                        addChildrenLoader($this.$el['btnMakeProfile']);
                    } else {
                        addChildrenLoader($btn);
                    }
                },
                success: function(res){
                    var is=$this.curPid==pid;
                    if (!isGallery) {
                        is=false;
                    }
                    if (checkDataAjax(res)){
                        if(!$this.isEHP()) {
                            $this.replacePhotoDefaultDelay(pid);
                        }
                        if(is){
                            $this.$el['btnMakeProfile'].removeClass('to_show');
                            removeChildrenLoader($this.$el['btnMakeProfile']);
                            if ($this.$el['mediaMenuMore'].is(':hidden')) {
                                $btn.hide();
                                removeChildrenLoader($btn);
                            } else {
                                $this.$el['mediaMenuMore'].one('hidden.bs.collapse',function(){
                                    $btn.hide();
                                    removeChildrenLoader($btn);
                                })
                            }
                            $this.hideMoreMenu();
                            alertCustomIcon(l('this_photo_has_been_set'),l('alert_success'),'set_profile_photo');
                        }else if (!isGallery) {
                            removeChildrenLoader($btn);
                            alertCustomIcon(l('this_photo_has_been_set'),l('alert_success'),'set_profile_photo');
                        }
                        var groupIdSet=$this.getMediaField(pid, 'group_id')*1;
                        $('.list_photos_image_menu_profile_pic.to_hide').each(function(){
                            var $el=$(this),
                                pidList=this.id.replace('list_photos_image_menu_profile_pic_',''),
                                groupId=$this.getMediaField(pidList, 'group_id')*1;
                            if(groupIdSet==groupId){
                                 $el.removeClass('to_hide');
                            }
                        });
                        $('#list_photos_image_menu_profile_pic_'+pid).addClass('to_hide');
                    }else if(is||!isGallery){
                        removeChildrenLoader($btn);
                        $this.hideMoreMenu();
                    }
                }
        })
        return;
    }

    this.getMediaField = function(pid, field) {
        var data=$this.galleryMediaData;
        if(!data[pid]){
            data=$this.visibleMediaData;
        }
        if(!data[pid] || data[pid][field]==undefined)return 0;
        return data[pid][field];
    }

    this.getPhotoDefaultId = function(isGroup) {
        var data=$this.galleryMediaData, info,
            key=$this.getKeyDefaultPhoto(isGroup);
        if($.isEmptyObject(data)){
            data=$this.visibleMediaData;
        }
        for (var id in data) {
            info=data[id];
            if(info['user_id']==$this.guid && info[key]=='Y'){
                return id;
                break;
            }
        }
        return 0;
    }

    this.updatePhotoInfoDefault = function(pid, url){
        var fnUpdate=function(mediaData, pid, url){
            if (mediaData) {
                var groupIdPhoto=0;
                if (mediaData[pid]!=undefined && mediaData[pid]['group_id']!=undefined) {
                    groupIdPhoto=mediaData[pid]['group_id']*1;
                }
                for (var id in mediaData) {
                    var data=mediaData[id],
                        groupId=data['group_id']!=undefined?data['group_id']*1:0,
                        key=$this.getKeyDefaultPhoto(groupId);
                    if(data['user_id']==$this.guid && groupIdPhoto == groupId){
                        mediaData[id]['user_photo_r']=url;
                        mediaData[id][key]=(id==pid)?'Y':'N';
                        var k=$this.guid+'_'+groupId;
                        if (mediaData[id]['responding_user'] == k) {
                            mediaData[id]['responding_user_photo_r']=url;
                        }
                    }
                }
            }
        }

        fnUpdate($this.visibleMediaData, pid, url);
        fnUpdate($this.galleryMediaData, pid, url);

    }


    this.replaceImg = false;
    this.replacePhotoDefaultInput = function(pid, data){
        var info=false;
        data=data||false;
        if(pid){
            if(data && data[pid]){
                info=data[pid];
            } else if($this.galleryMediaData[pid]){
                info=$this.galleryMediaData[pid];
            }else if($this.visibleMediaData[pid]){
                info=$this.visibleMediaData[pid];

            }
        }
        if(info===false && pid)return;

        var url=urlFiles+'edge_nophoto_r.png', img=new Image();
        if(pid)url=urlFiles+info['responding_user_photo_r'];
        if($this.replaceImg == url)return;
        $this.replaceImg=url;

        var groupId=info['group_id']==undefined?0:info['group_id']*1;
        if (siteGroupViewList) {
            groupId=0;
        }

        var cl='.photo_and_field_comment .profile_photo_r_'+$this.guid+'_0, .photo_and_field_comment .profile_photo_r_'+$this.guid+'_'+groupId,
            cl='.photo_and_field_comment .profile_photo_r_'+$this.guid+'_'+groupId,
            //cl=cl+'.comments_replies_post .profile_photo_'+s+'_'+$this.guid+'_0, .comments_replies_post .profile_photo_'+s+'_'+$this.guid+'_'+groupId,
            $photos=$this.$ppGallery.find(cl);//.add($this.$ppGalleryTmplCommentReply.find(cl));

        if(!$photos[0])return;

        img.onload = function(){
                $photos.each(function(){
                    var $el=$(this).attr({title:info['responding_user_name'],href:info['responding_user_url']});
                    if ($el[0].src) {
                        $el[0].src=url;
                    }else{
                        $el.css('background-image','url('+url+')')
                    }
                })
                delete img;
        }
        img.src=url;

    }

    this.sizePhotoDefault={bm:1,r:1,s:1,m:1};
    this.replacePhotoDefault = function(pidS, data){
        var pidDef=$this.getPhotoDefaultId(),
            pid=defaultFunctionParamValue(pidS, pidDef);

        if(pidS==-1)pid=0;
        var info=false;
        data=data||false;
        if(pid){
            if(data && data[pid]){
                info=data[pid];
            } else if($this.galleryMediaData[pid]){
                info=$this.galleryMediaData[pid];
            }else if($this.visibleMediaData[pid]){
                info=$this.visibleMediaData[pid];
            }
        }
        if(info===false && pid)return;

        var groupId=info['group_id']==undefined?0:info['group_id']*1;
        //if (groupId && siteGroupId != groupId) {
            //return;
        //}

        var urlDefault=info ? info['src_r'] : 'edge_nophoto_r.png';
        if(pid){
            $this.updatePhotoInfoDefault(pid, urlDefault);
            if(urlDefault === 'edge_nophoto_r.png' || info['gif'] || !$this.isImageEditorEnabled) {
                $('.edit_main_user_pic').addClass('to_hide');
            } else {
                $('.edit_main_user_pic').removeClass('to_hide');
            }
            setTimeout(function(){
                $this.replacePhotoDefaultInput($this.curPid);
            },100)
        } else {
            $('.edit_main_user_pic').addClass('to_hide');
        }

        var $headerPhotoBigEditor=$jq('#profile_photo_big_'+$this.guid+'_'+groupId);
        if ($headerPhotoBigEditor[0]) {
            $headerPhotoBigEditor.data('photoId', pid).attr('data-photo-id',pid);
        }

        var replace=function(s){
            var cl='.profile_photo_'+s+'_'+$this.guid+', .profile_photo_'+s+'_'+$this.guid+'_'+groupId,
                $photos=$(cl).add($this.$ppGalleryClone.find(cl)).add($this.$ppGalleryTmplCommentReply.find(cl));
            if(!$photos[0])return;
            var url=urlFiles+'edge_nophoto_'+s+'.png', img=new Image();
            if(pid)url=urlFiles+info['src_'+s];
            img.onload = function(){
                $photos.each(function(){
                    if ($photos.closest('.user_pic_frame_ie')[0]) {
                        $photos.data('pid', pid);
                    }
                    var $el=$(this);
                    if ($el[0].src) {
                        $el[0].src=url;
                    }else{
                        $el.css('background-image','url('+url+')')
                    }
                })
                delete img;
            }
            img.src=url;
        }
        for (var s in $this.sizePhotoDefault) {
            replace(s);
        }
    }

    this.replacePhotoDefaultDelay = function(pid){
        setTimeout(function(){
            $this.replacePhotoDefault(pid);
        },100);
    }

    this.replacePhotoDefaultCheck = function(setPhotoDefault, photoDefaultId, data){
        photoDefaultId=photoDefaultId||$this.getPhotoDefaultId();
        if(setPhotoDefault != photoDefaultId){
            $this.replacePhotoDefault(setPhotoDefault, data);
        }
    }
    /* Photo default */

    /* Photo delete */
    this.confirmPhotoDelete = function(pid, isVideo){
        $this.hideMoreMenu();
        if(isVideo==undefined){
            isVideo=$this.ppGalleryIsVideo;
        }
        confirmCustom(l('this_action_can_not_be_undone'),function(){$this.photoDelete(pid)}, isVideo?l('confirm_delete_video'):l('confirm_delete_photo'))
    }

    this.toggleShowLayerBlocked = function(vis){
        vis=vis||'show';
        if(vis=='show'){
            $this.scrollTop();
            $this.$el['layerBlocked'].show();
            $this.$ppGalleryContainer.addClass('change_photo');
            $this.stopVideoPlayer();
            $this.loaderGalleryShow();
        } else {
            $this.$el['layerBlocked'].hide();
            $this.$ppGalleryContainer.removeClass('change_photo');
            $this.loaderGalleryHidden();
        }
    }

    this.updaterCounterPage = function(sel,title,count){
        if (title) {
            $jq('#left_column_'+sel+'_count').html(title);
            $jq('#menu_inner_'+sel+'_edge').find('.number').text(count);
        }
    }

    this.updatePageData = function(pid, count_title, count, isVideo) {
        var uid=$this.uid||$this.guid;
        count *=1;
        pid=$this.getVideoId(pid);
        if(isVideo==undefined){
            isVideo=$this.ppGalleryIsVideo;
        }
        var type=isVideo?'videos':'photos',
            $listPhoto=$('.list_'+type+'_image_'+pid);
        if ($listPhoto[0]) {
            var page=clPages.page,noReplaceHistory=true;
            if($('.'+type+'_list_user .cham-post-image').length==1){
                page--;
                if(page<=0)page=1;
                noReplaceHistory=false;
            }
            clPages.pageReload(page,false,true,noReplaceHistory);
        }

        if (count) {
            var $li=$('.column_'+type+'_item_'+pid);
            if($li[0]){
                $li.oneTransEnd(function(){
                    $(this).remove()
                }).addClass('to_hide_bl', 0);
            }
            $this.updaterCounterPage(type, count_title, count);
        } else {
            var $bl=$('#left_column_'+type);
            if($bl[0]){
                $bl.slideUp(300,function(){
                    $bl.find('li').remove();
                    $this.updaterCounterPage(type, count_title, count);
                })
            } else {
                $this.updaterCounterPage(type, count_title, count);
            }
        }
    }
    
    /** Popcorn - added 2024-10-14 */
    // access: public, private, personal, custom folders
    this.changeAccessPhoto = function(pid, cmd = '', folder_id = 0, isVideo=false) {
        notGallery = true;
        $.ajax({type:'POST',
                url:url_ajax,
                data:{cmd:cmd, photo_cmd: "", id:pid, uid:$this.uid, folder_id: folder_id},
                beforeSend: function(){
                    if($this.isShowGallery && !notGallery){
                        $this.toggleShowLayerBlocked()
                    }
                },
                success: function(res){
                    if ($this.noAction()&&!notGallery)return;
                    var data=checkDataAjax(res);
                    if (data!==false){
                        updateGridPhotoFromDelete(pid);

                        $this.updatePageData(pid, data.count_title, data.count, isVideo);
                        if(!notGallery){
                            if (isVideo){
                                $this.closeGalleryPopup();
                                return;
                            } else {
                                $this.countAllMedia -= 1;
                            }
                        }

                        var fnSuccess = function(){
                            if(!notGallery){
                                $this.toggleShowLayerBlocked('hide');
                                if ($this.countAllMedia<1) {
                                    $this.closeGalleryPopup();
                                } else{
                                    if ($this.countAllMedia==1) {
                                        $this.$ppGalleryContainer.css('cursor','default');
                                        $this.$el['arrows'].addClass('to_hide');
                                    }
                                    if(isVideo){

                                    } else {
                                        $this.prepareLoadParamPhoto(pidNext);
                                    }
                                    $this.show('left', pidNext)
                                }
                            }
                            var photoDefaultId=0,
                            groupId=$this.getMediaField(pid, 'group_id')*1;
                            if (!isVideo){
                                photoDefaultId=$this.getPhotoDefaultId(groupId);
                                $this.replacePhotoDefaultCheck(data.photo_default, photoDefaultId);
                            }
                        }
                        setTimeout(fnSuccess,200);
                    } else{
                        alertServerError(true);
                        if(notGallery){
                            $('#list_image_layer_action_'+pid).removeClass('to_show').removeChildrenLoader();
                        } else {
                            $this.toggleShowLayerBlocked('hide')
                        }
                    }
                }
        })
    }

    this.photoDelete = function(pid,notGallery,isVideo) {
        pid=pid||$this.curPid;
        notGallery=notGallery||0;
        if(isVideo==undefined){
            isVideo=$this.ppGalleryIsVideo;
        }

        var photoDefaultId=0,
            groupId=$this.getMediaField(pid, 'group_id')*1;
        if(!isVideo){
            //photoDefaultId=notGallery ? -1 : $this.getPhotoDefaultId(groupId);
            photoDefaultId=$this.getPhotoDefaultId(groupId);
        }

        var pidNext=0;
        if(!notGallery)pidNext=$this.galleryMediaData[pid].next_id;

        photoCmdString = "";
        photoCmdString = $this.getPhotoCmdString();

        $.ajax({type:'POST',
                url:url_ajax,
                data:{cmd:'delete_photo', photo_cmd: photoCmdString, id:pid, uid:$this.uid, get_data_edge:1},
                beforeSend: function(){
                    if($this.isShowGallery && !notGallery){
                        $this.toggleShowLayerBlocked()
                    }
                },
                success: function(res){
                    if ($this.noAction()&&!notGallery)return;
                    var data=checkDataAjax(res);
                    if (data!==false){
                        updateGridPhotoFromDelete(pid);

                        /* Events delete */
                        var evPid=$this.getVideoId(pid),
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
                        $this.updatePageData(pid, data.count_title, data.count, isVideo);
                        if(!notGallery){
                            if (isVideo){
                                $this.closeGalleryPopup();
                                return;
                            } else {
                                $this.countAllMedia -= 1;
                                //$this.countAllMedia = data.count;
                                /* Delete photo info */
                                if($this.galleryMediaData[pid]){
                                    var dataMedia=$this.galleryMediaData,
                                        info1=$this.galleryMediaData[pid],
                                        off=info1['offset'],
                                        pidNext1=info1['next_id'],
                                        pidPrev1=info1['prev_id'];
                                    dataMedia[pidNext1]['prev_id'] = pidPrev1;
                                    dataMedia[pidNext1]['prev_title'] = dataMedia[pidPrev1]['description'];
                                    dataMedia[pidPrev1]['next_id'] = pidNext1;
                                    dataMedia[pidPrev1]['next_title'] = dataMedia[pidNext1]['description'];
                                    delete dataMedia[pid];
                                    for (var id in dataMedia) {
                                        if (dataMedia[id]['offset'] > off) {
                                            dataMedia[id]['offset'] -=1;
                                        }
                                    }
                                    //console.log(555, id, dataMedia);
                                }
                                /* Delete photo info */
                                //$this.setGalleryMediaData(data.photos_info);
                            }
                        }

                        var fnSuccess = function(){
                            if(!notGallery){
                                $this.toggleShowLayerBlocked('hide');
                                if ($this.countAllMedia<1) {
                                    $this.closeGalleryPopup();
                                }else{
                                    if ($this.countAllMedia==1) {
                                        $this.$ppGalleryContainer.css('cursor','default');
                                        $this.$el['arrows'].addClass('to_hide');
                                    }
                                    if(isVideo){

                                    } else {
                                        $this.prepareLoadParamPhoto(pidNext);
                                    }
                                    $this.show('left', pidNext)
                                }
                            }
                            if (!isVideo){
                                $this.replacePhotoDefaultCheck(data.photo_default, photoDefaultId);
                            }
                        }
                        setTimeout(fnSuccess,200);
                    }else{
                        alertServerError(true);
                        if(notGallery){
                            $('#list_image_layer_action_'+pid).removeClass('to_show').removeChildrenLoader();
                        } else {
                            $this.toggleShowLayerBlocked('hide')
                        }
                    }
                }
        })
    }
    /* Photo delete */

    /* Comment delete */
    this.confirmDeleteComment = function($el,cid,rCid){
        confirmCustom(l('are_you_sure'), function(){$this.deleteComment(cid,rCid)}, l('confirm_delete_comment'));
        $el.closest('.more_menu_collapse').collapse('hide');
    }

    this.deleteComment = function(cid,rCid){
        var $stick,pid=$this.curPid,cidD=cid;
        rCid=rCid||0;
        if (rCid||0) {
            cid=rCid;
            $stick=$('#pp_gallery_comm_delete_'+rCid);
        } else {
            $stick=$('#pp_gallery_comm_delete_'+cid);
        }

        photoCmd = $this.getPhotoCmd();

        //$stick.addChildrenLoader();
        $this.commentHideGallery(cid, rCid, function(){
            $this.showLastFieldComment();
            $.post(url_ajax+'?cmd=photo_comment_delete' + photoCmd, {parent_id:cidD,cid:cid,user_id:$this.uid,pid:pid},
                function(res){
                    if($this.noAction(pid))return;
                    var data=checkDataAjax(res);
                    if (data !== false){
                        if(rCid){
                            $this.updateRepliesCounter(cidD, data)
                        }else{
                            $this.updatePageCounter(data)
                        }
                    } else {
                        alertServerError();
                    }
                })
        })
    }

    this.commentHideGallery = function(cid, rcid, call, noRemove) {
        clMediaTools.commentHide(cid, rcid, false, noRemove, call);
    }
    /* Comment delete */

    /* Edit desc - tag */
    this.getListTags = function(tags){//!!! not used
        var list='';
        for (var id in tags) {
            list +=', <a href="'+urlPagesSite.photos_list+'?tag='+id+'">'+tags[id]+'</a>';
        }
        if (list) {
            list=list.slice(1);
        }
        return list;
    }

    this.openEditTags = function(){
        var data=$this.galleryMediaData[$this.curPid];
        if(!data || data['user_id']!=$this.guid)return true;// || !$this.uid
        $this.$el['tags'].hide();
        $this.$el['tagsEditText'].val(data.tags_title);
        $this.$el['tagsEdit'].show();
        $this.$el['tagsEditText'].focus();
        return false;
    }

    this.cancelEditTags = function(e){
        if($this.$el['tagsEdit'].is(':hidden'))return;
        if(e){
            var $targ=$(e.target);
            if($targ.is('#pp_gallery_tags_bl')||$targ.closest('#pp_gallery_tags_bl')[0]){
                return;
            }
        }
        $this.$el['tagsEdit'].hide();
        $this.$el['tags'].show();
        $this.$el['tagsEditText'].val('');
    }

    this.saveEditTags = function(){
        if($this.$el['tagsEdit'].is(':hidden'))return;
        var pid=$this.curPid,
            dataTags=$this.galleryMediaData[pid],
            tags=trim($this.$el['tagsEditText'].val());
        tags=strip_tags(tags);

        if (dataTags['tags_title']!=tags) {
            $this.$el['tagsList'].html(l('saving')+'&nbsp;&nbsp;&nbsp;&nbsp;').addChildrenLoader();

            photoCmd = "";
            photoCmd = $this.getPhotoCmd();

            $.ajax({type: 'POST',
                    url: url_ajax+'?cmd=update_media_tags' + photoCmd + '&type=' + $this.getTypeGallery(),
                    data: {tags: tags, photo_id: $this.prepareId(pid)},
                    beforeSend: function(){
                    },
                    success: function(res){
                        var data=checkDataAjax(res);
                        if(data){
                            $this.galleryMediaData[pid]['tags'] = data.tags;
                            $this.galleryMediaData[pid]['tags_html'] = data.tags_html;
                            $this.galleryMediaData[pid]['tags_title'] = data.tags_title;

                            $this.visibleMediaData[pid]['tags'] = data.tags;
                            $this.visibleMediaData[pid]['tags_html'] = data.tags_html;
                            $this.visibleMediaData[pid]['tags_title'] = data.tags_title;

                            if ($this.ppGalleryIsVideo) {
                                var $listTags=$('.video_list_tags_'+pid);
                            } else {
                                var $listTags=$('.photo_list_tags_'+pid);
                            }
                            if ($listTags[0]) {
                                $listTags.html(data.tags_html)
                                $listTags.closest('.tag').show();
                                //clPages.listLoad(clPages.page,false,true,true);
                            }

                            if($this.noAction(pid))return;
                            tags=data.tags_html;
                            if(!tags){
                                tags=l('click_to_add_tags');
                            }
                            $this.$el['tagsList'].html(tags);
                        }else{
                            if($this.noAction(pid))return;
                            $this.$el['tagsList'].html(dataTags['tags_html']);
                            alertServerError(true);
                        }
                    }
            })
        }
        $this.cancelEditTags();
    }

    this.openEditDesc = function(){
        var data=$this.galleryMediaData[$this.curPid];
        if(!data || data['user_id']!=$this.guid)return;
        $this.$el['desc'].hide();
        $this.$el['descEditText'].val(data.description);
        $this.$el['descEditBl'].show();
        $this.$el['descEditText'].focus();
    }

    this.cancelEditDesc = function(e){
        if($this.$el['descEditBl'].is(':hidden'))return;
        if(e){
            var $targ=$(e.target);
            if($targ.is('#pp_gallery_desc_bl_edit')||$targ.closest('#pp_gallery_desc_bl_edit')[0]
               ||$targ.is('#pp_gallery_desc')||$targ.closest('#pp_gallery_desc')[0]){
                return;
            }
            $this.saveEditDesc();
        }

        $this.$el['descEditBl'].hide();
        $this.$el['desc'].show();
        $this.$el['descEditText'].val('');
    }

    this.updateDescFromPage = function(id, desc){
        debugLog('Update description media from page', id);
        $('.photo_description_'+id).attr('title', desc);
        var $blPage=$('#list_media_description_bl_'+id);
        if ($blPage[0]) {
            if (desc) {
                $blPage.find('.subject').attr('title', desc);
                $blPage.find('.subject > span').text(desc).end().addClass('to_show');
            } else {
                $blPage.removeClass('to_show')
            }
        }
        /* Wall */
        var sel=$this.ppGalleryIsVideo?'#wall_video_'+$this.getVideoId(id):'#wall_photo_'+id,
            $wallDesc=$(sel);
        if($wallDesc[0]){
            var $blWall=$wallDesc.closest('.wall_image_post');
            if ($blWall[0]) {
                var $desc=$blWall.siblings('h4');

                if (desc) {
                    if($desc[0]){
                        $desc.attr('title',desc).text(desc);
                    } else {
                        $('<h4 title="'+desc+'">'+desc+'</h4>').insertAfter($blWall)
                    }
                } else {
                    $desc.remove();
                }
            }
        }
        /* Wall */
    }

    this.saveEditDesc = function(){
        if($this.$el['descEditBl'].is(':hidden'))return;
        var pid=$this.curPid,
            desc=trim($this.$el['descEditText'].val()),
            data=$this.galleryMediaData[pid];
        desc=strip_tags(desc);
        $this.visibleMediaData[pid] = $this.galleryMediaData[pid];

        if (data['description']!=desc) {
            var descTitle=desc;
            if(!descTitle){
                descTitle=$this.ppGalleryIsVideo?l('click_here_to_add_a_video_caption'):l('click_here_to_add_a_photo_caption');
            }
            $this.setDescription(descTitle);
            var descOld=data['description'];
            $this.galleryMediaData[pid]['description']=desc;
            $this.visibleMediaData[pid]['description']=desc;
            $this.cancelEditDesc();
            $this.updateDescFromPage(pid,desc);

            photoCmd = "";
            photoCmd = $this.getPhotoCmd();

            $.ajax({type: 'POST',
                    url: url_ajax+'?cmd=photo_save_desc' + photoCmd,
                    data: {desc: desc, pid: pid},
                    beforeSend: function(){
                    },
                    success: function(res){
                        if(!checkDataAjax(res)){
                            $this.galleryMediaData[pid]['description']=descOld;
                            $this.visibleMediaData[pid]['description']=descOld;
                            if(!$this.noAction(pid)){
                                $this.setDescription(descOld);
                                alertServerError(true);
                            }
                            $this.updateDescFromPage(pid,descOld);
                        }
                    }
            })
        } else {
            $this.cancelEditDesc();
        }
    }
    /* Edit desc - tag */

    this.like = function($el){
        if ($el.is('.disabled')) return;
        $el.addClass('disabled');

        var rcid=$el.data('rcid'),
            cid=$el.data('cid')||rcid,
            pid=$this.curPid,
            like=$el.data('like')*1;

        clMediaTools.likeChangeStatus($el, like);

        photoCmd = "";
        photoCmd = $this.getPhotoCmd();

        $.post(url_ajax+'?cmd=comment_like&type=' + $this.getTypeGallery() + photoCmd,
              {cid:cid,like:like,user_id:$this.galleryMediaData[pid]['user_id'], id:pid},
            function(res){
                if($this.noAction(pid))return;
                var data=checkDataAjax(res);
                if (data){
                    var $bl=rcid?$('#comment_reply_likes_bl_'+rcid):$('#comment_likes_bl_'+cid),
                        dataLike={count:data['likes'],title:data['likes_users']};
                    clMediaTools.updateCommentOneLike(dataLike, $bl);
                } else {
                    $el.removeClass('disabled');
                    alertServerError();
                }
        })
    }

    this.hideMoreMenu = function(e){
        if(!$this.$el['mediaMenuMore'])return;
        if(e){
            var $targ=$(e.target);
            if($targ.is('.wrap_upload_menu')||$targ.closest('.wrap_upload_menu')[0]){
                return;
            }
        }
        if($this.$el['mediaMenuMore'][0] && $this.$el['mediaMenuMore'].is('.in'))$this.$el['mediaMenuMore'].collapse('hide');
        $('.more_menu_right.in').collapse('hide');
    }
    /* Gallery */

    /* Report */
    this.setDataReports = function(pid){
        if(pid){
            //$this.$el['mediaMenu'].hide();
            $this.$el['linkReport'].hide();
            if (!in_array($this.guid, $this.galleryMediaData[pid]['reports'].split(','))) {
                if(trim($this.galleryMediaData[pid]['reports'])){
                    $this.galleryMediaData[pid]['reports'] +=','+$this.guid;
                }else{
                    $this.galleryMediaData[pid]['reports']=$this.guid+'';
                }
            }
        }
    }

    this.openReport = function() {
        $this.hideMoreMenu();
        var data=$this.galleryMediaData[$this.curPid];
        if(!data || data['user_id']==$this.guid)return;
        clProfile.openReport(data['user_id'], $this.curPid);
    }
    /* Report */

    this.getTypeGallery = function(){
        return $this.ppGalleryIsVideo?'video':'photo';
    }

    this.hideMoreMenuAction = function(){
        $('.list_photos_image_menu.in').collapse('hide');
    }

    this.updateRestoreImage = function(pid, restore){
        if (typeof $this.visibleMediaData[pid] != 'undefined') {
            $this.visibleMediaData[pid]['restore']=restore;
        }
        if (typeof $this.galleryMediaData[pid] != 'undefined') {
            $this.galleryMediaData[pid]['restore']=restore;
        }
        if (pid == $this.curPid) {
            $this.controlRestoreImage(restore);
        }
        delete $this.photoRotateInit[pid];
    }

    this.updateGalleryImageAfterEdit = function(pid, imageInfo, restore){
        $this.refreshImage(pid, true, restore);
        var $img=$this.$ppGalleryOneImg;
        if ($this.curPid==pid&&$img[0]) {
            var v=+new Date; v='?v='+v,
                size='bm';//$img.data('size')
            if (imageInfo['info'] != undefined && imageInfo['info']['src_'+size]!=undefined) {
                var src=imageInfo['info']['src_'+size];
                src=url_files+src.split('?')[0]+v;
            } else {
                src=url_files+'photo/'+$this.guid+'_'+pid+'_'+size+'.jpg?'+v;
            }
            $img[0].src= urlAddUniqueVersionParam($img[0].src);
        }
    }

    this.refreshImage = function(pid, edit, restore){
        if($this.visibleMediaData[pid] != undefined){
            var photo=$this.visibleMediaData[pid]['user_id']+'_'+pid,
                sizes=['b', 's', 'r', 'm', 'bm'],
                preloadArr=[],i=0,url,v=+new Date; v='?v='+v,
                isMediaData=typeof $this.galleryMediaData[pid] != 'undefined';
            sizes.forEach(function(size,i,arr) {
                url = urlAddUniqueVersionParam($this.visibleMediaData[pid]['src_'+size]);
                $this.visibleMediaData[pid]['src_'+size] = url;
                if (isMediaData) {
                    $this.galleryMediaData[pid]['src_'+size] = url;
                }
                preloadArr[i++]=urlFiles+url;
            })

            if (isMediaData) {
                $this.galleryMediaData[pid].load = false;
                $this.setLoadParamPhoto(pid);
            }

            if (edit) {
                restore=defaultFunctionParamValue(restore, 1);
                $this.updateRestoreImage(pid, restore);
            }
            preloadImageInsertInDom(preloadArr);
            $this.replaceRotatePhoto(pid, edit);

            if($this.visibleMediaData[pid]['default'] === 'Y') {
                var userId = $this.visibleMediaData[pid]['user_id'];
                var images = $('.profile_photo_m_' + userId + '_0, .profile_photo_r_' + userId + '_0');
                if (images.length) {
                    images.each(function(){
                        var image = $(this);
                        var backgroundImageStyle = image.css('backgroundImage');
                        var regexUrl = /url\("(.*)"\)/i;
                        var backgroundUrlMatch = backgroundImageStyle.match(regexUrl);
                        if(backgroundUrlMatch.length) {
                            var backgroundUrl = backgroundUrlMatch[1];
                            var backgroundUrlNew = addUniqueVariableToURL(backgroundUrl, 'refreshVersion', Date.now() + Math.random());
                            backgroundImageStyle = backgroundImageStyle.replace(backgroundUrl, backgroundUrlNew);
                            image.css({'backgroundImage' : backgroundImageStyle});
                        }
                    })
                }
            }
        } else if ($('.user_pic_frame_ie img').data('pid') == pid) {
            // Fix for default profile photo on the profile page
            $('.user_pic_frame_ie img').attr('src', urlAddUniqueVersionParam($('.user_pic_frame_ie img').attr('src')));
            var userId = $this.guid;
            var images = $('.profile_photo_m_' + userId + '_0, .profile_photo_r_' + userId + '_0');
            if (images.length) {
                images.each(function(){
                    var image = $(this);
                    var backgroundImageStyle = image.css('backgroundImage');
                    var regexUrl = /url\("(.*)"\)/i;
                    var backgroundUrlMatch = backgroundImageStyle.match(regexUrl);
                    if(backgroundUrlMatch.length) {
                        var backgroundUrl = backgroundUrlMatch[1];
                        var backgroundUrlNew = urlAddUniqueVersionParam(backgroundUrl);
                        backgroundImageStyle = backgroundImageStyle.replace(backgroundUrl, backgroundUrlNew);
                        image.css({'backgroundImage' : backgroundImageStyle});
                    }
                })
            }
        }
    }

    this.photoRotateInit = {};
    this.rotate = function(pid){
        pid *=1;
        if(!pid)return;
        $this.hideMoreMenuAction();

        var $layer=$('#list_image_layer_action_'+pid);
        if($layer.is('.to_show'))return;
        $layer.addClass('to_show').addChildrenLoader();

        if($this.photoRotateInit[pid] == undefined){
            $this.photoRotateInit[pid] = {angle:0,start:0,set:0};
        }
        $this.photoRotateInit[pid]['set']=1;

        var angle=$this.photoRotateInit[pid]['angle']+90;
        var $photo=$('#list_photos_image_photo_'+pid).css({transform:'rotate3d(0, 0, 1, '+angle+'deg)'});

        var fnShowBtn=function(pid,angle){
            $this.photoRotateInit[pid]['angle']=angle;
            $this.photoRotateInit[pid]['set']=0;
            $layer.removeClass('to_show').removeChildrenLoader();
        },
        fnError=function(pid,angle){
            angle -=90;
            $photo.css({transform:'rotate3d(0, 0, 1, '+angle+'deg)'});
            $this.photoRotateInit[pid]['set']=0;
            $this.photoRotateInit[pid]['angle']=angle;
            fnShowBtn(pid,angle);
            alertServerError(true);
        };

        photoCmd = $this.getPhotoCmd();

        $.ajax({url:url_ajax+'?cmd=photo_rotate' + photoCmd,
                type:'POST',
                data:{photo_id:pid, angle:90},
                beforeSend: function(){},
                success: function(res){
                    if (checkDataAjax(res)){
                        $this.refreshImage(pid);
                        fnShowBtn(pid,angle);
                    }else{
                        fnError(pid,angle);
                    }
                },
                error: function(){
                    fnError(pid,angle);
                }
        })
    }

    this.replaceRotatePhoto = function(pid, edit) {
        var info=$this.visibleMediaData[pid],
            groupId=info['group_id']*1, _default=info.default == 'Y',
            sel,$els;
        if (groupId) {
            _default=info.default_group == 'Y';
        }
        var el={
            s:'img.column_photo_s_'+pid,
            b:'.grid_item_'+pid
        }
        el.bm=false;
        if(_default){
            el.bm='img.profile_photo_bm_'+$this.guid+'_'+groupId;
            el.r='img.profile_photo_r_'+$this.guid+'_'+groupId;
        }
        if (edit) {
            if (el.bm) {
                el.bm+=',#list_photos_image_photo_'+pid;
            } else {
                el.bm='#list_photos_image_photo_'+pid;
            }
            el.bm+=',#wall_photo_'+pid+' > img';
        }

        for (var size in el) {
            sel=el[size];
            if (sel) {
                $els=$(sel);
                if($els[0]){
                    $els.each(function(){
                        var $img=$(this),
                            src=urlFiles+$this.visibleMediaData[pid]['src_'+size];
                        if($img.attr('src')){
                            this.src=src;
                        }else{
                            $img.css({'background-image':'url('+src+')', 'transform':'none'});
                        }
                    })
                }
            }
        }
    }

    this.confirmPhotoDeleteList = function(pid, isVideo){
        $this.hideMoreMenu();
        isVideo=isVideo||false;
        confirmCustom(l('this_action_can_not_be_undone'),function(){
            $this.photoDeleteList(pid)
        }, isVideo?l('confirm_delete_video'):l('confirm_delete_photo'))
    }

    this.photoDeleteList = function(pid){
        var $layer=$('#list_image_layer_action_'+pid);
        if($layer.is('.to_show'))return;
        $layer.addClass('to_show').addChildrenLoader();
        $this.hideMoreMenuAction();

        var isVideo=$this.isVideo(pid);
        if(!pid)return;

        $this.photoDelete(pid,true,isVideo);
    }

    this.inViewport = function(el){
        return inViewport(el,{container:$this.$ppGalleryOverflow[0],threshold:-40})//
    }

    this.resizeImageOne = function(h){
        var style='';
        if (h) {
            h = Math.round(h*$this.galleryImageHeightMobile/100);
            //console.log(33333,'H: '+ h+'/'+$this.galleryImageHeightMobile);
            //$('#pp_gallery_photo_one_bl, #pp_wall_one_post_photo_one_bl').css({'height':h,'max-height':h});
            //$('#pp_gallery_photo_one_bl img, #pp_wall_one_post_photo_one_bl img').css('max-height',h);
            style='#pp_gallery_photo_one_bl, #pp_wall_one_post_photo_one_bl{height:'+h+'px; max-height:'+h+'px;}'+
                  '#pp_gallery_photo_one_bl img, #pp_wall_one_post_photo_one_bl img{max-height:'+h+'px;}';
        }
        $jq('#style_gallery')[0].innerHTML=style;
    }

    this.resizeImage = function(){
        var h=$win[0].innerHeight;
        if(!h)return;
        h -=$jq('.navbar').height();
        $this.resizeImageOne(h);
    }

    this.isWallGallery = function(){
        if(typeof clWall != 'object')return false;
        return clWall.isOpenOnePostImage;
    }

    this.checkScrollInput = function(){
        var $context=$this.isShowGallery?$this.$ppGallery:clWall.$ppGalleryOverflow;
        var $fl=$('textarea:focus, input:focus', $context);
        if($fl[0]){
            !$this.inViewport($fl[0]) && $this.scrollToInto($fl);
        }
    }

    this.hideHeaderPicture = function($btn,pid){
        var isGallery=false;
        $btn=$btn||[];
        if(!$btn[0]){
            isGallery=true;
            $btn=$this.$el['linkHideHeader'];
        }
        if($btn.find('.css_loader')[0])return;
        pid=pid||$this.curPid;

        photoCmd = "";
        photoCmd = $this.getPhotoCmd();

        $.ajax({type: 'POST',
                url: url_ajax+'?cmd=hide_from_header_picture' + photoCmd,
                data: {photo_id: pid},
                beforeSend: function(){
                    addChildrenLoader($btn);
                },
                success: function(res){
                    var is=$this.curPid==pid||!isGallery,
                        data=checkDataAjax(res);
                    if (data!==false){
                        var hideHeader=data*1;
                        if($this.galleryMediaData[pid]!=undefined){
                            $this.galleryMediaData[pid]['hide_header']=hideHeader;
                        }
                        if(is){
                            if(isGallery){
                                $this.$el['mediaMenuMore'].one('hidden.bs.collapse',function(){
                                    $this.changeLinkHideHeaderPicture(hideHeader,false,pid);
                                    removeChildrenLoader($btn);
                                })
                                $this.hideMoreMenu();
                            } else {
                                $this.changeLinkHideHeaderPicture(hideHeader,$btn);
                                removeChildrenLoader($btn);
                            }
                            alertCustomIcon(hideHeader?l('picture_remove_from_header_alert'):l('picture_add_in_header_alert'),l('alert_success'), 'remove_header');
                        }
                        if (hideHeader) {
                            updateGridPhotoFromDelete(pid);
                        } else {
                            var groupId=$this.getMediaField(pid, 'group_id');
                            if (groupId) {
                                siteGroupId == groupId && updateGridPhoto();
                            }else{
                                updateGridPhoto();
                            }
                        }
                    }else if(is){
                        if(isGallery){
                            $this.hideMoreMenu();
                        }
                        removeChildrenLoader($btn);
                    }
                }
        })
        return;
    }

    this.addLike = function($btn, like){
        if($this.$ppGalleryLikesBl.is('.action'))return;// || $btn.is('.active')

        var pid=$this.curPid;
        $this.$ppGalleryLikesBl.addClass('action');
        $btn.addChildrenLoader();

        var isVideo=$this.isVideo(pid),pidP=pid;
        if (isVideo) {
            pidP=$this.getVideoId(pid);
        }

        photoCmd = "";
        photoCmd = $this.getPhotoCmd();

        var data={
            photo_id: pidP,
            like: like,
            type: isVideo ? 'video' : 'photo'
        }

        $.post(url_ajax+'?cmd=photo_like_add' + photoCmd,data,
            function(res){
                if ($this.noAction(pid))return;
                data=checkDataAjax(res);
                if (data!==false){
                    $this.updatePhotoLikeData(pid, data);
                    $this.updatePhotoLike(pid);
                    wallUpdater();
                }else{
                    $this.$ppGalleryLikesBl.removeClass('action');
                    $btn.removeChildrenLoader();
                    alertServerError(true)
                }
        })
    }

    /* Face detection */
    $.fn.tipFace = function(){
        var $el=$(this);
        if(!$el.is('.user_assigned'))return $el;
        var data=$el.data(), name=data['user_name'];
        if(!name)return $el;
        var url=data['user_url'];
        if(!url)url='';
        var tmplRemoveFace='', clTip='';

        if ($el.data('photo_user_id')==$this.guid || $el.data('uid')==$this.guid) {
            tmplRemoveFace='<i data-photo_id="'+$el.data('photo_id')+'" data-box_id="'+$el.data('id')+'" title="'+l('user_face_tip_delete')+'"'+
                               'class="fa fa-times-circle" aria-hidden="true" onclick="clProfilePhoto.confirmUserFaceRemove($(this));"></i>';
            clTip='tooltip_user_face_my_photo';
        }
        var marginTop=0;
        if (data['tip']) {
            marginTop=data['tip'];
        }
        var tmpl='<div title="'+l('go_to_the_profile')+'" data-user-url="'+url+'" class="tooltip tooltip_user_face '+clTip+'" style="margin-top:'+marginTop+'px;" role="tooltip">'+
                        '<div class="arrow"></div>'+
                        '<div class="tooltip-inner"></div>'+
                        tmplRemoveFace+
                     '</div>';


        $el.attr('data-original-title', name).tooltip({
            template:tmpl,
            trigger:'manual',
            title: name,
            animation: true,
            container: this,
            placement: 'top',
            //offset: 100,
            boundary: $this.$ppGalleryOneBl[0]
        })
        /*.on('mouseenter touchstart', function() {
            $el.data('fn_hide', '');
            !$el.find('.tooltip_user_face').is('.in') && $el.tooltip('show');
        }).on('mouseleave', function() {
            $el.data('fn_hide',function(){$el.tooltip('hide')});
            setTimeout(function(){
                var fn=$el.data('fn_hide');
                typeof fn=='function' && fn();
            },100)
        })*/

        return $el;
    }

    this.darwFace = function(pid, faceDetection){
        if(!isFaceDetectionEnabled())return;
        if(typeof faceDetection['face'] == 'undefined'||$this.noAction(pid))return;

        var face=faceDetection['face'],
            imageData=faceDetection['image'];

        var _wImage=$this.$ppGalleryOneImg.width(),
            _hImage=$this.$ppGalleryOneImg.height();

        /* Position container */
        var _wc=$this.$ppGalleryOneBl.width(),
            _hc=$this.$ppGalleryOneBl.height();

        var _dw=_wc-_wImage,
            _dh=_hc-_hImage;

        _dw=_dw>0 ? Math.round(_dw/2) : 0;
        _dh=_dh>0 ? Math.round(_dh/2) : 0;
        /* Position container */

        var dem=1, div, css, parseDiv, clUser, nameUser='';
        if (_wImage != imageData.width) {
            dem=(_wImage/imageData.width).toFixed(3)*1;
        }
        var $boxAll=false, lF;
        if (typeof face == 'object') {
            lF=Object.keys(face).length-1;
        } else {
            lF=face.length-1;
        }
        var boxChangeParam=[];

        var isAllowAssign=$this.checkFriendsAssignAllowUser(face, true),
            wIm=$this.$ppGalleryOneImg.width(),
            hIm=$this.$ppGalleryOneImg.height(),
            tIm=$this.$ppGalleryOneImg.offset().top - $this.$ppGalleryOneBl.offset().top,
            dZ=1.6, wDz, hDz, hTip=isMobileSite?29:15, dOffsetTip=1.3;
        if(tIm<0)tIm=0;
        if(isMobileSite && $win[0].innerWidth<$win[0].innerHeight){
            dOffsetTip=1.2;
        }

        var fnDimensionsNormalize = function(wB1, lB1, hB1, tB1){
            var dB=hB1-wB1, dbW=false;

            if (wB1 > hB1) {
                dB=wB1-hB1;
                dbW=true;
            }
            if (dB>1) {
                if (dbW) {
                    hB1=wB1;
                    lB1-=dbW/2;
                } else {
                    wB1=hB1;
                    tB1-=dbW/2;
                }
            }
            return [wB1, lB1, hB1, tB1];
        }

        /* Delete intersection box */
        /*for (var i in face) {
            var box=face[i];
            for (var m in face) {
                var box1=face[m];
                if (m>i) {
                    var res=intersectionBoxFaceDetection(box, box1, i, m);
                    if(face[m]['intersect']==undefined){
                        face[m].intersect = res.intersect;
                    } else if (res.intersect) {
                        face[m].intersect = res.intersect;
                    }
                    if (res.s>0) {
                        face[m].s1 -= res.s;
                        face[i].s1 -= res.s;
                    }
                }
            }
        }

        for (var i in face) {
            var box=face[i], s=box.s*1/4.5, s1=box.s1*1;
            if(box.intersect*1 || s > s1){
                delete face[i];
                //console.log(box.intersect, i);
            }
        }*/
        /* Delete intersection box */

        for (var i in face) {
            if (typeof face[i] != 'object') continue;
            var box=face[i],
                wB=box.width*1, lB=box.left*1,
                hB=box.height*1, tB=box.top*1;

            var dataDem=fnDimensionsNormalize(wB, lB, hB, tB);
            wB=dataDem[0]; lB=dataDem[1]; hB=dataDem[2]; tB=dataDem[3];

            /*var dB=hB-wB,dbW=false;

            if (wB > hB) {
                dB=wB-hB;
                dbW=true;
            }
            if (dB>1) {
                if (dbW) {
                    hB=wB;
                    lB-=dbW/2;
                } else {
                    wB=hB;
                    tB-=dbW/2;
                }
            }*/

            var left=Math.round(lB*dem+_dw),
                width=Math.round(wB*dem),
                top=Math.round(tB*dem+_dh),
                height=Math.round(hB*dem),
                uid=box.uid != undefined ? box.uid*1 : 0,
                parseDiv=true;

            top -=Math.round(height*.12);

            /* Big box */
            if ((height > (hIm/dZ)) || (width > (wIm/dZ))) {
                //console.log(3333, dDem);
                wDz=width/dZ;
                left += (width - wDz)/2;
                width = wDz;

                hDz = height/dZ;
                top += (height - hDz)/2;
                height = hDz;
            }
            var hBottom=top+height,
                //topTip=hBottom*dOffsetTip + hTip + 10,
                topTip=hBottom + hTip*2 + 40,
                dDem=topTip - hIm - tIm;
            if (box.name != undefined && dDem > 0 && (height - dDem) > hTip) {
                width -=dDem;
                left +=dDem/2;
                height -=dDem;
                top +=dDem/2;
                //console.log(22222, dDem, height, hIm - hBottom, hIm, hBottom, topTip, topTip - hIm)
            }
            /* Big box */

            boxChangeParam[i] = {
                id:i,
                left:left,
                //top:top,
                //height:height,
                bound:top+height,
                tip:0
            };

            if (uid) {

            } else if (imageData['photo_user_id'] != $this.guid) {
                parseDiv=false;
            } else {
                parseDiv=isAllowAssign;
            }

            if (parseDiv) {
                if (uid) {
                    clUser='user_assigned';
                    nameUser=box.name != undefined ? box.name : '';
                    urlUser=box.user_url != undefined ? box.user_url : '';
                } else {
                    clUser='';
                    nameUser='';
                    urlUser='';
                }
                css={left:left, top:top, width:width, height:height};
                div='<div id="face_box_'+pid+'_'+i+'" class="face_box '+clUser+' to_hide">';
                var $box=$(div).data({
                    id:i, uid:uid, user_name: nameUser,
                    photo_id:pid, photo_user_id:imageData['photo_user_id'], user_url:urlUser
                }).css(css).appendTo($this.$ppGalleryOneBl);
                //.tipFace()
                //.removeClass('to_hide',0);
                if(!clUser)$box.attr('title', l('user_face_tag_a_friend'));
                if(!$boxAll){
                    $boxAll=$box;
                } else {
                    $boxAll=$boxAll.add($box);
                }
            }
            if (lF==(i*1) && $boxAll[0]) {
                boxChangeParam.sort(function(a,b){return a.left - b.left;});
                var boxChangeParamCheck=[],
                    data,bound=0,boundPrev=0,dir=0,d,prev;
                for (var i in boxChangeParam) {
                    i*=1;
                    data=boxChangeParam[i];
                    bound=0
                    boundPrev=0;
                    if(i){
                        prev=boxChangeParam[i-1];
                        bound=prev.bound+prev.tip*1;
                        /*if(i>1){
                            prev=boxChangeParam[i-2];
                            boundPrev=prev.bound+prev.tip*1;
                        }*/
                    }
                    if (bound || boundPrev) {
                        if (bound && !boundPrev) {
                            d=Math.abs(bound-data.bound);
                            if (d < hTip) {
                                d=(hTip-d)/1.8;
                                boxChangeParam[i]['tip'] = (bound>data.bound || bound==data.bound) ? '-'+d : d;
                            }
                        } else {//Not used
                            d=Math.abs(bound-data.bound);
                            if (d < hTip) {
                                d=(hTip-d)/1.8;
                                boxChangeParam[i]['tip'] = (bound>data.bound || bound==data.bound) ? '-'+d : d;
                            }
                        }
                        dir=dir?0:1;
                    } else {
                        boxChangeParam[i]['tip'] = 0;
                    }
                }

                for (var i in boxChangeParam) {
                    data=boxChangeParam[i];
                    boxChangeParamCheck[data['id']]=data;
                }

                $boxAll.each(function(){
                    var $box=$(this),
                        i=$box.data('id');
                    $box.data('tip',boxChangeParamCheck[i]['tip']);
                    $box.tipFace().removeClass('to_hide',0);
                })
            }
        }

    }

    this.hideFace = function(){
        $('.face_box', $this.$ppGalleryOneBl).remove();
        $this.$ppGalleryFriendListBl.data('face_box',false).removeClass('to_show animated');
        /*$('.face_box', $this.$ppGalleryOneBl).fadeOut(200,function(){
            $(this).remove();
        })
        $('.face_box', $this.$ppGalleryOneBl).oneTransEnd(function(){
            $(this).remove();
        }).addClass('to_hide');*/

    }

    this.updateDataFace = function(pid, data, title){
        if(!isFaceDetectionEnabled() ||!pid )return;
        if($this.galleryMediaData[pid]){
            $this.galleryMediaData[pid]['face_detect_data']=data;
            $this.galleryMediaData[pid]['face_detect_title']=title;
        }
        if($this.visibleMediaData[pid]){
            $this.visibleMediaData[pid]['face_detect_data']=data;
            $this.visibleMediaData[pid]['face_detect_title']=title;
        }
    }

    this.isFriendsUser;
    this.listFriendsUser={};
    this.checkFriendsUser = function(){
        if(!isFaceDetectionEnabled())return false;
        if($this.isFriendsUser==undefined){
            if($this.$ppGalleryFriendListBl==undefined){
                $this.isFriendsUser = false;
            } else {
                var $li=$this.$ppGalleryFriendListBl.find('li');
                $li.each(function(){
                    $this.listFriendsUser[$('a', this).data('uid')]=1;
                })
                $this.isFriendsUser = $li[0] ? true : false;
            }
        }
        return $this.isFriendsUser;
    }

    this.getFriendsListUser = function(){
        if(!isFaceDetectionEnabled())return [];
        if($this.isFriendsUser==undefined){
            $this.checkFriendsUser();
        }
        return $this.listFriendsUser;
    }

    this.checkFriendsAssignAllowUser = function(face, check){
        var checkFace={};
        check=check||false;
        for (var i in face) {
            if(face[i].uid)checkFace[face[i].uid] = 1;
        }
        if (check) {
            var listFriends=$.extend({}, $this.getFriendsListUser());
            for (var uid in listFriends) {
                if(checkFace[uid]){
                    delete listFriends[uid]
                }
            }
            return !$.isEmptyObject(listFriends);
        } else {
            $this.$ppGalleryFriendListBl.find('li').each(function(){
                var uid=$('a', this).data('uid');
                $(this)[checkFace[uid]?'hide':'show']()
            })
        }
    }

    this.checkDetectFace = function(data){
        if(!isFaceDetectionEnabled()||!data)return false;
        var dataFace=data['face_detect_data'],
            groupId=data['group_id']*1;
        if(dataFace == 'none'||groupId)return false;

        if(data['user_id']!=$this.guid || dataFace){
            return false;
        }
        if(!$this.checkFriendsUser()){//No friends - we are not looking for user faces
            return false;
        }
        return true;
    }

    this.renderFace = function(data, src){
        if(!isFaceDetectionEnabled()||!data)return;
        var dataFace=data['face_detect_data'],
            groupId=data['group_id']*1;
        if(dataFace == 'none'||groupId)return;
        if(dataFace)$this.darwFace(data['photo_id'], dataFace);

        if(data['user_id']!=$this.guid || dataFace){
            return;
        }
        if(!$this.checkFriendsUser()){//No friends - we are not looking for user faces
            return;
        }
        checkImageFaceDetection(src, data['photo_id']);
    }

    this.resizeFace = function(check){
        if(!isFaceDetectionEnabled())return;
        check=check||0;
        var pid=$this.curPid;
        if (!$this.isShowGallery || !pid)return;
        var $faceBl=$('.face_box', $this.$ppGalleryOneBl);
        if(!$faceBl[0]&&check)return;

        $('.face_box', $this.$ppGalleryOneBl).remove();
        if($this.galleryMediaData[pid]==undefined)return;
        $this.darwFace(pid, $this.galleryMediaData[pid]['face_detect_data']);
    }

    this.setTitleFaceAndDate = function(pid,info){
        info=info||$this.galleryMediaData[pid];
        if(pid!=$this.curPid||!info)return;
        var date=info['date'];

        if(isFaceDetectionEnabled()) {
            date += ' ' + info['face_detect_title'];
        }

        $this.$ppGalleryDate.html(date);
    }

    this.clearFaceBoxHide = function(){
        $('.face_box').removeClass('to_show to_show_one to_hide_active');
    }

    this.hideFaceFriendBox = function(){
        if(!isFaceDetectionEnabled())return;
        $this.clearFaceBoxHide();
        $this.$ppGalleryFriendListBl
        .addClass('animated').data('face_box',false).oneTransEnd(function(){
            $this.$ppGalleryFriendListBl.removeClass('animated');
            $this.$ppGalleryFriendListBl.find('li').show();
        }).removeClass('to_show');
    }

    this.setFaceFriend = function($el){
        var $box=$this.$ppGalleryFriendListBl.data('face_box');
        if(!$box || !$box[0] || !isFaceDetectionEnabled() ){
            return;
        }
        var data=$box.data();
        if(data.uid)return;

        $el.addChildrenLoader();
        var pid=data.photo_id;
        $.post(url_ajax+'?cmd=face_detect_set_friend',
               {pid:pid,box_id:data.id,uid:$el.data('uid')},
        function(res){
            var data=checkDataAjax(res);
            if(data && typeof data == 'object' && typeof data.data == 'object'){
                $this.updateDataFace(pid, data.data, data.title);
            }
            if(!$this.noAction(pid)){
                $el.removeChildrenLoader();
                $this.hideFaceFriendBox();
                if(data){
                    if (typeof data == 'object') {
                        $this.resizeFace();
                        $this.setTitleFaceAndDate(pid);
                        setTimeout(function(){
                            $('.face_box[data-original-title]:not([aria-describedby])').tooltip('show');
                            $('.face_box').addClass('to_show_one');
                        },100)
                    }
                } else {
                    alertServerError(true);
                }
            }
        })
    }

    this.confirmUserFaceRemove = function($el){
        confirmCustom(l('user_face_remove_tag'),function(){
            $this.friendFaceClear($el);
        })
    }

    this.friendFaceClear = function($el){
        if(!isFaceDetectionEnabled())return;
        var data=$el.data();
        var pid=data.photo_id;
        $.post(url_ajax+'?cmd=face_detect_clear_friend',
               {pid:pid,box_id:data.box_id},
        function(res){
            var data=checkDataAjax(res);
            if(data && typeof data == 'object' && typeof data.data == 'object'){
                $this.updateDataFace(pid, data.data, data.title);
            }
            if(!$this.noAction(pid)){
                if(data){
                    if (typeof data == 'object') {
                        $this.resizeFace();
                        $this.setTitleFaceAndDate(pid);
                    }
                } else {
                    alertServerError(true);
                }
            }
        })
    }

    this.friendFaceAllClear = function(pid, data, type){
        if(!isFaceDetectionEnabled())return;
        if(data){
            $this.updateDataFace(pid, '', '');
            if (type == 'edit_gallery' && !$this.noAction(pid)){
                $this.setTitleFaceAndDate(pid);
                $('.face_box', $this.$ppGalleryOneBl).remove();
                setTimeout(function(){
                    $this.renderFace($this.galleryMediaData[pid], $this.$ppGalleryOneImg[0].src);
                },100)
            }
        } else {
            //alertServerError(true);
        }
    }
    /* Face detection */

    /* Upload default photo */
    this.changeUploadDefaultPhoto = function($file) {
        $file.closest('form').find('input[type=submit]').click();
    }

    this.clickUploadDefaultPhoto = function($file) {
        $file.next('input[type=reset]').click();
    }

    this.initUploadDefaultPhoto = function(){
        var $framePhoto=$('#user_pic_frame');
        //$framePhoto.find('.edit_main_user_pic, .upload_icon').tooltip({
            //container: $framePhoto,
            //placement: 'bottom'
        //})
        $('#header_upload_default_photo_frm').submit(function(e){
            var indx=+new Date,
                frm = $(this), file = frm.find('input[type=file]'),
                fileName = file.attr('name'), formData = new FormData(),
                error = '';
                $.each(file[0].files, function(i, file){
                    if ("image/jpeg,image/png,image/gif".indexOf(file.type) === -1) {
                        error = l('accept_file_types');
                        return false;
                    }else if (file.size > $this.maxphotoFileSize) {
                        error = $this.maxphotoFileSizeLimit;
                        return false;
                    }
                    formData.append(fileName, file);
                });

                if (error) {
                    alertCustom(error, l('alert_html_alert'));
                    return false;
                }
                $framePhoto.addClass('action_set_default_photo').addChildrenLoader();

                var groupId=$this.groupId*1;

                var xhr = new XMLHttpRequest();
                xhr.open("POST", url_ajax+'?cmd=upload_default_photo&input_name='+fileName+'&group_id='+groupId);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if(xhr.status == 200) {
                            var data = xhr.responseText;
                            data = checkDataAjax(data);
                            if (data) {
                                if (data.error) {
                                    alertCustom(data.error, l('alert_html_alert'));
                                } else {
                                    $this.isImageEditorEnabled = data.data.isImageEditorEnabled;
                                    var photoDefaultId=$this.getPhotoDefaultId(groupId),
                                        sel='photos';
                                    if(!clPages.myPageReload(sel,true,false,groupId)){
                                        wallUpdater();
                                        updateGridPhotoFromPublish();
                                        $this.updaterCounterPage(sel,data.data.count_title, data.data.count);
                                        $this.replacePhotoDefaultCheck(data.data.photo_default, photoDefaultId, data);

                                        alertCustomIcon(l('this_photo_has_been_set'),l('alert_success'),'set_profile_photo');
                                    }
                                }
                            }
                            $framePhoto.removeChildrenLoader().removeClass('action_set_default_photo');
                        }
                    }
                };
                xhr.send(formData);
                return false;
        });
    }
    /* Upload default photo */

    /* Upload cover photo */
    this.onloadCoverPhoto = function($img) {
        $img.closest('#grid').removeClass('cover_init');
    }

    this.initElUploadCoverPhoto = function() {
        $this.$upcGridBl=$('#block_grid');
        $this.$upcGrid=$('#grid');
        $this.$upcImg=[];
    }

    this.changeUploadCoverPhoto = function($file) {
        $file.closest('form').find('input[type=submit]').click();
    }

    this.clickUploadCoverPhoto = function($file) {
        $file.next('input[type=reset]').click();
    }

    this.setWidthBtnSaveCoverPhoto = function(w,checkW){
        return; // incorrect width on resize, looks like no way to calculate correct size and better to use CSS-styles
        if(isMobileSite)return;
        if(checkW||false){
            var w1=$this.$upcWrap[0].offsetWidth;
            if(w1==w)return;
        }
        $this.$upcWrap.width(w);
    }

    this.getStyleCoverPhoto = function(data){
        var css={left:'', top:''};
        if(typeof data['translate']!='undefined'&&data.translate){
            css['height'] = data.height+'%';
            css['transform'] = 'translateY('+data.translate+'%)';
        }
        return css;
    }

    this.setStyleCoverPhoto = function(data){
        if (data==undefined) {
            data=$this.$upcImg.data();
        }
        var css=$this.getStyleCoverPhoto(data);
        $this.$upcImg.css(css);
    }

    this.setBtnSaveCoverPhoto = function(){
        var wSave=$this.$upcSaveBl.width();
        $this.setWidthBtnSaveCoverPhoto(wSave, true);
        $this.$upcControlBl.addClass('upload_cover');
    }

    this.setBtnWidthEditCoverPhoto = function(){
        var wSave=$this.$upcEditBl.width();
        $this.setWidthBtnSaveCoverPhoto(wSave, true);
    }

    this.setBtnEditCoverPhoto = function(){
        $this.setBtnWidthEditCoverPhoto();
        $this.$upcControlBl.toggleClass('upload_cover upload_cover_edit');
    }

    this.removeCoverPhoto = function(){
        if ($this.$upcAction)return false;
        $this.$upcAction=true;

        $this.$upcControlBl.addClass('disabled');
        addChildrenLoader($this.$upcControlBl);

        var fnResponse=function(){
            removeChildrenLoader($this.$upcControlBl);
            $this.setBtnWidthEditCoverPhoto();
            $this.$upcControlBl.removeClass('upload_cover_edit disabled');
            $this.$upcAction=false;
        }
        updateGridPhoto(fnResponse, true);
    }

    this.changeCoverPhoto = function(src){
        var v=+new Date;
        img=new Image();
        img.onload = function(){
            $this.$upcGridBl.addClass('grid_update');

            var $grid=$this.$upcGrid,
                $gridNew=$('<div id="grid" class="grid_cover">');
            $this.$upcGrid=$gridNew;
            $this.$upcGridBl.prepend($gridNew.append(img));

            setTimeout(function(){
                $grid.oneTransEnd(function(){
                    $grid.remove();
                    $this.$upcGridBl.removeClass('grid_update');
                    var $img=$gridNew.find('img').on('dragstart',function(){return false}),
                        hI=$img.height(), hD=310;
                    $this.$upcImg=$img.on('contextmenu',function(){return false});
                    if (hI>(hD+10)) {
                        $gridNew.addClass('adjust');
                        var hC=hI*100/hD;
                        hC=(parseInt(hC*100))/100;
                        $img.css('height', hC+'%')
                            .attr({'data-height':hC, 'data-translate':0})
                            .data({height:hC, translate:0});

                        dragCover.init($this.$upcGridBl, $img, false);
                        //$this.$upcSave.text(l('adjust_and_save'));
                    } else {
                        //$this.$upcSave.text(l('save'));
                        //var wSave=$this.$upcSaveBl.width();
                        //$this.setWidthBtnSaveCoverPhoto(wSave, true);
                    }
                    $this.setBtnSaveCoverPhoto();
                }).delay(150).toggleClass('to_hide', 0);
            },10)
        }
        img.src=src+'?v='+v;
    }

    this.initUploadCoverPhoto = function(){
        $this.$upcAction = false;
        $this.$upcFileName = '';

        $this.$upcWrap=$('#cover_button_wrap');
        $(function(){
            $this.setWidthBtnSaveCoverPhoto($this.$upcWrap[0].offsetWidth);
        })

        $this.initElUploadCoverPhoto();

        $this.$upcUploadBl=$('#header_upload_cover_bl');
        $this.$upcSaveBl=$('#header_upload_cover_save_bl');
        $this.$upcSave=$('#header_upload_cover_save_btn');
        $this.$upcEditBl=$('#header_upload_cover_edit_bl');
        $this.$upcControlBlNew=$('#header_upload_cover_new_bl');
        $this.$upcControlBlNewLink=$('#header_upload_cover_new_bl_link');

        $this.$upcControlBl=$('#header_upload_cover_photo_bl').click(function(){
            if ($this.$upcControlBl.is('.upload_cover')){
                if ($this.$upcAction)return false;
                $this.$upcAction=true;

                $this.$upcGrid.removeClass('adjust');
                dragCover.dragStop=1;


                $this.$upcControlBl.addClass('disabled');
                addChildrenLoader($this.$upcControlBl);

                var dataI=$this.$upcImg.data(), data={};
                if(typeof dataI['translate']!='undefined'&&dataI.translate){
                    data['height'] = dataI.height+'%';
                    data['transform'] = 'translateY('+dataI.translate+'%)';
                }

                var groupId=$this.groupId*1;
                $.post(url_ajax+'?cmd=set_profile_cover',{params:data, file_name:$this.$upcFileName, group_id:groupId},
                function(res){
                    $this.$upcAction=false;
                    dragCover.dragStop=0;
                    $this.$upcControlBl.removeClass('disabled');
                    removeChildrenLoader($this.$upcControlBl);

                    var data=checkDataAjax(res);
                    if (data) {
                        dragCover.destroy();
                        $this.setStyleCoverPhoto();
                        $this.setBtnEditCoverPhoto();
                    } else {
                        $this.$upcGrid.addClass('adjust');
                    }

                })
            }
        })

        $this.$upcBtnDelete=$('#header_upload_cover_delete').click($this.removeCoverPhoto)

        $('#header_upload_cover_photo_frm, #header_upload_cover_photo_new_frm').submit(function(e){
            if ($this.$upcAction)return false;
            $this.$upcAction=true;

            $this.$upcFileName = '';
            $this.initElUploadCoverPhoto();
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

                var isNewCover=frm.is('#header_upload_cover_photo_new_frm');
                if (isNewCover) {
                    closeMenuCollapse('.more_menu_collapse.in');
                }

                $this.$upcControlBl.addClass('disabled');
                addChildrenLoader($this.$upcControlBl);

                var groupId=$this.groupId*1,
                fnHideLoader=function(){
                    $this.$upcControlBl.removeClass('disabled');
                    removeChildrenLoader($this.$upcControlBl);
                    $this.$upcAction=false;
                },
                fnError=function(){};

                $this.clickUploadCoverPhoto(file);

                var xhr = new XMLHttpRequest();
                xhr.open("POST", url_ajax+'?cmd=upload_profile_cover&input_name='+fileName+'&group_id='+groupId);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if(xhr.status == 200) {
                            var data = xhr.responseText;
                            data = checkDataAjax(data);
                            if (data) {
                                if (data.error || !data.src) {
                                    alertCustom(data.error, l('alert_html_alert'));
                                    fnError();
                                } else {
                                    if (isNewCover) {
                                        $this.$upcControlBl.removeClass('upload_cover_edit');
                                    }
                                    $this.changeCoverPhoto(data.src);
                                    $this.$upcFileName=data.filename;
                                }
                            } else {
                                fnError();
                            }
                            fnHideLoader();
                        }
                    }
                };
                xhr.send(formData);
                return false;
        });
    }
    /* Upload cover photo */

    Dropzone.autoDiscover = false;
    $(function(){
        $this.initUploadFile('video');
        $this.initUploadFile('song');
        $this.initUploadFile('photo');

        $this.$ppGallery=$('#pp_gallery_photos_content');
        $this.$ppGalleryClone=$this.$ppGallery.clone();
        $this.ppGalleryIsVideo=false;
        $this.$ppGalleryTmplComment=$('.bl_templates').find('.pp_gallery_comment_item').addClass('to_post');
        $this.$ppGalleryTmplCommentReply=$('.bl_templates').find('.comments_replies_item').addClass('to_post');
        $this.$ppGalleryTmplComment.find('.comments_replies_list').empty();

        window.onbeforeunload = function (e) {
            //console.log($this.$ppUpload['photo']['upload_count'],$this.$ppUpload['video']['upload_count']);
            if($this.$ppUpload['photo']['upload_count']||$this.$ppUpload['video']['upload_count']
                    ||$this.$ppUpload['song']['upload_count']){
                var message = l('your_photos_will_not_be_published');
                if($this.$ppUpload['song']['upload_count']){
                    message = l('your_songs_will_not_be_published');
                }
                if(typeof e =='undefined'){e=window.event}
                if(e){e.returnValue = message}
                return message;
            }
        }

        /* Face detect */
        /*
         * $this.$ppGalleryOneBl.on('mouseenter mousemove ', function(){
            var $boxTips=$('.face_box[data-original-title]:not([aria-describedby])');
            if ($boxTips[0]) {
                $boxTips.tooltip('show');
            }
        }).on('mouseleave', function() {
            var $boxTips=$('.face_box[data-original-title][aria-describedby]');
            if ($boxTips[0]) {
                $boxTips.tooltip('hide');
            }
        })
         */
        $jq('body').on('mousemove', '.photo_one_bl', function() {
            $('.face_box.to_show_one').removeClass('to_show_one');
        /* No mobile device */
        }).on('mouseenter mouseleave', '.users_face_with > a', function(e) {
            if(isMobileSite)return;
            var event=e.type,isEnter=event=='mouseenter',
                $el=$(this), box=$el.data('box'),$box;
            if(isEnter){
                $box=$('#face_box_'+$this.curPid+'_'+box+'.user_assigned:not([aria-describedby])');
            } else {
                $box=$('#face_box_'+$this.curPid+'_'+box+'.user_assigned[aria-describedby]');
            }
            if ($box[0]) {
                $box[isEnter?'addClass':'removeClass']('to_show_tip').tooltip(isEnter?'show':'hide');
            }
        }).on('mouseenter mouseleave', '.face_box:not(.user_assigned)', function(e){
            if(isMobileSite)return;
            var $el=$(this),event=e.type;
            $('.face_box').not($el)[event=='mouseenter'?'addClass':'removeClass']('to_hide',0);
            return false;
        /* No mobile device */
        }).on('click', '.tooltip_user_face', function(e) {
            var $targ=$(e.target), $el=$(this);
            if(!$targ.is('.fa')){
                var url=$el.data('user-url');
                url && redirectUrl(url);
            }
        }).on('touchstart click', function(e){
            var $target = $(e.target),event=e.type,
                isTargetFaceBox=$target.is('.face_box:not(.user_assigned)') || $target.closest('.face_box:not(.user_assigned)')[0];
            /* Face detect */
            if (isTargetFaceBox) {
                if (event=='click'){
                    e.preventDefault();
                    var $faceBox=$target;
                    if(!$faceBox.is('.face_box')){
                        $faceBox=$target.closest('.face_box');
                    }
                    if($this.$ppGalleryFriendListBl.is('.to_show')
                        &&$this.$ppGalleryFriendListBl.data('face_box')
                        &&$this.$ppGalleryFriendListBl.data('face_box')[0]==$faceBox[0]){
                        return false;
                    }
                    $this.clearFaceBoxHide();
                    var fnShowFreind=function(){
                        var face=$this.galleryMediaData[$this.curPid]['face_detect_data']['face'];
                        $this.checkFriendsAssignAllowUser(face);

                        $this.$ppGalleryFriendListBl.removeClass('animated')
                            .find('a.add_loader_transparent')
                            .removeChildrenLoader();
                        $this.$ppGalleryFriendListBl.data('face_box', $faceBox)
                        .position({
                            my:'left top+5',
                            at:'left bottom',
                            of:$target,
                            collision: 'fit fit',
                            within: '#pp_gallery_photo_one_bl'
                        })
                        .delay(10).addClass('to_show',0);

                        $faceBox.addClass('to_show');
                        $('.face_box').not($faceBox).addClass('to_hide_active');
                    }
                    if ($this.$ppGalleryFriendListBl.is('.to_show')) {
                        $this.$ppGalleryFriendListBl.addClass('animated').oneTransEnd(fnShowFreind)
                        .removeClass('to_show',0);
                    } else {
                        fnShowFreind();
                    }
                    return false;
                }
            } else {

                if ($this.$ppGalleryFriendListBl != undefined
                    && $this.$ppGalleryFriendListBl[0] && $this.$ppGalleryFriendListBl.is('.to_show')
                    && !$target.is('#gallery_friend_list') && !$target.closest('#gallery_friend_list')[0]) {
                    e.preventDefault();
                    $this.hideFaceFriendBox();
                    if(isMobileSite){
                        $('.face_box').addClass('to_show',0);
                        $('.face_box[data-original-title]:not([aria-describedby])').tooltip('show');
                    }
                    return false;
                }

                if (event=='touchstart') {
                    if ($target.is('#pp_gallery_photo_one_bl') || $target.closest('#pp_gallery_photo_one_bl')[0]) {
                        var $fb=$('.face_box');
                        if($fb.is('.to_show')){
                            $fb.removeClass('to_show',0);
                            $('.face_box[data-original-title][aria-describedby]').tooltip('hide');
                        } else {
                            $fb.addClass('to_show',0);
                            $('.face_box[data-original-title]:not([aria-describedby])').tooltip('show');
                        }
                    }
                }
            }
        /* Face detect */

        }).on('click', '.pp_file_upload', function(e){
            var $targ=$(e.target), type=$(this).data('type');
            if ($targ.is('.modal-content')||$targ.closest('.modal-content')[0]
                ||$this.$ppUpload[type]['upload_count']){
                return true;
            }
            $this.$ppUpload[type]['btn_cancel'].click();
        }).on('click', function(e){
            var $target = $(e.target);
            if($target.is('.pp_gallery_overflow') || $target.is('.navbar-default') || $target.is('.navbar-header')){
                if ($this.isShowGallery) {
                    if($this.$ppGalleryOverflow.is('.moving'))return;
                    $this.closeGalleryPopup();
                    return;
                } else if ($this.isWallGallery()) {
                    clWall.closePpOnePostPopup();
                    return;
                }
            }
            if (!$this.isShowGallery)return;

            if($target.is('.app_ios_video_editor_link')
                || $target.closest('.app_ios_video_editor_link')[0]
                || $target.is('.app_ios_image_editor_link')
                || $target.closest('.app_ios_image_editor_link')[0]) {
                return;
            }
            $this.hideMoreMenuAction();
            if($this.noAction())return;
            $this.hideMoreMenu(e);
            $this.cancelEditDesc(e);
            $this.cancelEditTags(e);
        })

        $win.on('popstate',function(){
            if (window.history && typeof history.state == 'object') {
                if ($jq('body').is('.pp_boost_ajax_system_open')) {
                    $('.pp_upgraded').click();
                } else if ($jq('body').is('.pp_boost_ajax_open')) {
                    if(!isAppAndroid) {
                        closePopupUpdate('pp_boost_ajax');
                    }
                } else if ($jq('#events_notification').is('.pp_show')) {
                    clEvents.visibleList('hide');
                } else if ($jq('#friends_notification').is('.pp_show')) {
                    clFriends.visiblePendingList('hide');
                } else if ($jq('#btn_header_menu_nav').is('.history_open')) {
                    $jq('#btn_header_menu_nav').addClass('history_close_back').click();
                } else if ($jq('body').is('.upload_file_video')) {
                    if($this.$ppUpload['video']['upload_count']){
                        setPushStateHistory('upload_file');
                    }else{
                        $this.closeDropZone('video');
                    }
                } else if ($jq('body').is('.upload_file_photo')) {
                    if($this.$ppUpload['photo']['upload_count']){
                        setPushStateHistory('upload_file');
                    }else{
                        $this.closeDropZone('photo');
                    }
                } else if ($jq('body').is('.upload_file_song')) {
                    if($this.$ppUpload['song']['upload_count']){
                        setPushStateHistory('upload_file');
                    }else{
                        $this.closeDropZone('song');
                    }
                } else if ($jq('body').is('.gallery_im_open')) {
                    if (typeof clStream == 'object' && clStream.isOpenImage) {
                        clStream.closePpImage();
                    } else if (clBlogs.isOpenImage) {
                        clBlogs.closePpImage();
                    } else {
                        clMessages.closePpImage();
                    }
                } else if ($jq('body').is('.message_open')) {
                    clMessages.close();
                } else if ($this.isShowGallery) {
                    $this.closeGallery();
                } else if ($this.isWallGallery()) {
                    clWall.closePpOnePost();
                } else if ($jq('body').is('.pp_upgrade_open')) {
                    clUpgrade.closePopup();
                }
            }
        })
        $win.on(evWndRes,function(e){
            if ($this.isShowGallery) {
                $this.hideFaceFriendBox();
                if(isMobileSite){
                    $('.face_box', $this.$ppGalleryOneBl).remove();
                    var timeUpdateFace=setTimeout($this.resizeFace,600);
                    $this.$ppGalleryOneImg.oneTransEnd(function(e){
                        clearTimeout(timeUpdateFace);
                        $this.resizeFace();
                    })
                } else {
                    $this.resizeFace(true)
                }
            }

            if(!isMobileSite || isChangeDevice){
                $this.resizeImageOne('');
                return;
            }
            if ($this.isShowGallery || $this.isWallGallery()) {
                var ev=e.type;
                setTimeout(function(){
                    if(!isMobileSite || isChangeDevice){
                        $this.resizeImageOne('');
                        return;
                    }
                    if (ev == 'orientationchange') {
                        $this.resizeImage();
                    }
                },evWndResTime)
                if (!isMobileSite || isChangeDevice) {
                    return;
                }
                if(isAppAndroid){
                    setZeroTimeout($this.checkScrollInput)
                }else{
                    setTimeout($this.checkScrollInput,evWndResTime)
                }
            }
        })

        faceDetecionWorkerInit();

    })

    return this;
}