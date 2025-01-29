var CGroups = function(guid, uid, groupId) {

    var $this=this;

    this.guid=guid*1;
    this.uid=uid*1;
    this.groupId=groupId*1;

    this.getId = function(){
        return siteGroupId;
    }

    this.isPage = function(){
        return siteGroupId && siteGroupView == 'group_page';
    }

    this.initAddPage = function(isPage, initPhotoId){
        $(function(){
            $this.initAdd(isPage, initPhotoId);
        })
    }

    this.initPhotoId = 0;
    this.initAdd = function(isPage, initPhotoId){
        
        $this.initPhotoId = initPhotoId*1;
        isPage *=1;
        $this.$blFrmAdd = $('#bl_group_add_frm');

        $this.$flLocation=$jq('.geo, #city', $this.$blFrmAdd);
        $this.$flLocationGeo=$jq('.field_geo', $this.$blFrmAdd);

        $this.$state=$('#state', $this.$blFrmAdd),
        $this.$city=$('#city', $this.$blFrmAdd),

        $('.geo', $this.$blFrmAdd).change(function() {
            var type=$(this).data('location'),
                $elLoader=[],$field=[],$btn;

            $this.$flLocation.prop('disabled', true);
            $field=$this.$flLocationGeo.find('button.dropdown-toggle').addClass('disabled');

            if (type == 'geo_states') {
                $elLoader=$this.$state.closest('.field').addChildrenLoader();
                $btn=$('button.dropdown-toggle[data-id="state"]').addClass('trans');
            } else {
                $elLoader=$this.$city.closest('.field').addChildrenLoader();
                $btn=$('button.dropdown-toggle[data-id="city"]').addClass('trans');
            }

            $.ajax({type: 'POST',
                url: url_ajax,
                data: { cmd:type,
                        select_id:this.value,
                        filter:'1',
                        list: 0},
                        beforeSend: function(){
                        },
                        success: function(res){
                            $this.$flLocation.prop('disabled', false);
                            $elLoader.removeChildrenLoader();
                            $btn.removeClass('trans');
                            $field.removeClass('disabled');
                            var data=checkDataAjax(res);
                            if (data) {
                                var option='<option value="0">'+l('choose_a_city_location')+'</option>';
                                switch (type) {
                                    case 'geo_states':
                                        $this.$state.html('<option value="0">'+l('choose_a_state')+'</option>' + data.list).selectpicker('refresh');
                                        $this.$city.html(option).selectpicker('refresh');
                                        break
                                    case 'geo_cities':
                                        $this.$city.html(option + data.list).selectpicker('refresh');
                                        break
                                }
                            }

                        }
            })
            return false;
        })

        function setDisabledSave($el) {
            var disabled=false,isHideError=false;
            $jq('.field_required', '#bl_group_add_frm').each(function(){
                var $cur=$(this),
                    val=trim(this.value)
                var isError=!val,msgError=l('required_field');
                if(this.name=='group_name_seo' && /[%#&'"\/\\<>*|]/.test(val)){
                    isError=true;
                    msgError=l('invalid_public_url');
                }
                if(isError){
                    if(isSubmit){
                        if($el[0]){
                            if ($el[0] == $cur[0]) {
                                isHideError=false;
                            }else{
                                isHideError=true;
                            }
                        }
                        showError($cur, msgError, isHideError, isHideError);
                    }
                    isHideError=true;
                    disabled=true;
                } else {
                    hideError($cur);
                }
            })

            disabled = disabled||$this.isProcessUpload;
            //if (isSubmit) {
                //$this.$btnAdd.prop('disabled', disabled);
                //$this.$btnAdd.removeClass('disabled');
            //} else {
                $this.$btnAdd[disabled?'addClass':'removeClass']('disabled');
            //}

            return disabled;
        }

        var isSubmit=false;
        $this.$nameSeo = $('#group_name_seo').on('change propertychange input', function(){
            var val=trim(this.value);
            $this.$nameSeo.data('user_change', val);
        }).blur(function(){
            var nameSeo=trim($this.$nameSeo.val());
            if(!nameSeo)return;
            $.post(url_ajax+'?cmd=group_check_name_seo',{'name_seo':nameSeo,group_id:$this.groupId},function(res){
                var data=checkDataAjax(res);
                if(data!=false && data){
                    showError($('#group_name_seo'), data);
                }
            })
        });

        if ($this.groupId) {
            $this.$nameSeo.data('user_change', true);
        }

        $this.$title=$('#group_title',$this.$blFrmAdd).on('change propertychange input', function(e){
            if($this.$nameSeo.data('user_change'))return;
            if(this.value) {
                var nameSeo=this.value.toLowerCase().replace(/_/g," ").replace(/\s+/g,' ');
                nameSeo=trim(nameSeo);
                nameSeo=nameSeo.replace(/[%#&'"\/\\<>*|]/gi, '').replace(/ /g,"_");
                $this.$nameSeo.val(nameSeo);
            } else {
                $this.$nameSeo.val('');
            }
        });
        $this.$tags=$('#group_tags',$this.$blFrmAdd);
        
        $this.$description=$jq('#group_description').autosize({isSetScrollHeight:false,callback:function(){}});


        $('.field_required', $this.$blFrmAdd).on('change propertychange input', function(){setDisabledSave($(this))})
             .on('focus',function(){focusError($(this))})
             .on('blur',function(){blurError($(this))});

        $this.$country = $('#country');
        $this.$state = $('#state');
        $this.$city = $('#city');



        $this.$btnAdd=$('#group_add').click(function(){
            isSubmit=true;
            if(setDisabledSave([]))return false;

            var fnDisabledControls=function(disabled){
                $('input, textarea, select, button', $this.$blFrmAdd).prop('disabled', disabled);
                if (disabled) {
                    addChildrenLoader($this.$btnAdd);
                } else {
                    removeChildrenLoader($this.$btnAdd);
                }
            }

            fnDisabledControls(true);
            private='N';
            if(!isPage){
                private=$jq('#group_access').val();
            }
            var group_category = $('#group_category').val();
            var group_show_owner_checked = $('#group_show_owner').prop('checked');
            var group_show_owner = '0';
            if(group_show_owner_checked) {
                group_show_owner = '1';
            }

            var data={
                title:trim($this.$title.val()),
                description:trim($this.$description.val()),
                country_id:$this.$country[0]?$this.$country.val():0,
                state_id:$this.$state[0]?$this.$state.val():0,
                city_id:$this.$city[0]?$this.$city.val():0,
                name_seo:trim($this.$nameSeo.val()),
                photo_id:$this.$upPhotoImgId.val(),
                tags:trim($this.$tags.val()),
                group_id:$this.groupId,
                is_page:isPage,
                private:private,
                category_id:group_category,
                group_show_owner: group_show_owner
            };

            $.post(url_ajax+'?cmd='+$jq('#group_cmd').val(),data,function(res){
                var data=checkDataAjax(res), resError=true;
                if(data){
                    resError=false;
                    var $data=$(trim(data)),
                        $res=$data.filter('span');
                    if ($res[0]) {
                        var $redirect=$res.filter('.redirect')[0];
                        if($redirect){
                            //console.log($redirect.innerText);
                            redirectUrl($redirect.innerText);
                        } else {
                            fnDisabledControls(false);
                            var isHideError=false;
                            $res.each(function(){
                                showError($('#group_'+this.className), this.innerText, isHideError, isHideError);
                                isHideError=true;
                            })
                        }
                    } else {
                        resError=true;
                    }
                }
                if (resError) {
                    alertServerError();
                    fnDisabledControls(false);
                }
            })
        })
    }

    this.isProcessUpload=false;
    this.clickUploadPhoto = function($file) {
        $file.next('input[type=reset]').click();
    }

    this.changeUploadPhoto = function($file) {
        $file.parent('form').find('input[type=submit]').click();
    }

    this.showDeletePhoto = function() {
        if(!$this.initPhotoId){
            $this.$upPhotoBl[($this.$upPhotoImgId.val()*1)?'removeClass':'addClass']('bl_photo_update');
        }
    }

    this.submitUploadPhoto = function($frm) {
        $this.$btnAdd.prop('disabled', true);
        var file = $frm.find('input[type=file]'),
            fileName = file.attr('name'),
            url = url_ajax +
                  '?cmd=photo_add_upload&type=public&one=1&size=b&file_input=group_photo_file',
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

        if (error) {
            alertCustom(error);
            return false;
        }

        //$this.$upPhotoBtnDelete.prop('disabled',true);
        //if($this.$upPhotoBl.is('.bl_photo_update')){
            //$this.$upPhotoBtnDelete.hide();
        //}
        $this.$upPhotoFile.css('cursor','default');
        $this.$upPhotoBtn.find('.btn_title').text(l('uploading_image'));
        addChildrenLoader($this.$upPhotoBtn.prop('disabled',true));
        $this.isProcessUpload=true;

        var fnRes=function(){
            removeChildrenLoader($this.$upPhotoBtn.prop('disabled',false));
            $this.$upPhotoFile.css('cursor','pointer');
            //$this.$upPhotoBtnDelete.show();
            $this.showDeletePhoto();
            $this.isProcessUpload=false;
        }

        //return false;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if(xhr.status == 200) {
                    var data=xhr.responseText,
                        res=jQuery.parseJSON(data);

                    if (typeof res=='object') {
                        if (res.error) {
                            alertCustom(res.error);
                            var btnTitle=$this.$upPhotoImgId.val()*1?l('use_another'):l('choose_an_image');
                            $this.$upPhotoBtn.find('.btn_title').text(btnTitle);
                            fnRes();
                        } else {
                            $this.$upPhotoImgId.val(res.id);
                            var url=res.src_r;
                            img=new Image();
                            img.onload = function(){
                                $this.$upPhotoImg[0].src=url;
                                //$this.$upPhotoBtnDelete.prop('disabled',false);
                                fnRes();
                                $this.$upPhotoBtn.find('.btn_title').text(l('use_another'));
                            }
                            img.src=url;
                        }
                    }else{
                        alertServerError(true);
                        fnRes();
                    }
                    $this.$btnAdd.prop('disabled', false);
                }
            }
        };
        xhr.send(formData);
        return false;
    }

    this.deleteUploadPhoto = function() {
        var id=$this.$upPhotoImgId.val()*1;
        if(!id || $this.isProcessUpload)return;
        $this.isProcessUpload=true;
        $this.$upPhotoBtn.prop('disabled',true);
        addChildrenLoader($this.$upPhotoBtnDelete.prop('disabled',true));

        $.post(url_ajax+'?cmd=delete_temp_photo_group',{photo_id:id},function(res){
            removeChildrenLoader($this.$upPhotoBtnDelete.prop('disabled',false));
            $this.$upPhotoBtn.prop('disabled',false);
            var data=checkDataAjax(res);
            if(data){
                $this.$upPhotoImg[0].src=urlFiles+'edge_nophoto_group_b.png';
                $this.$upPhotoImgId.val(0);
                $this.$upPhotoBtn.find('.btn_title').text(l('choose_an_image'));
                $this.showDeletePhoto();
            } else {
                alertServerError(true);
            }
            $this.isProcessUpload=false;
        })
    }

    this.hideMoreMenuAction = function(){
        $('.list_group_item_menu.in').collapse('hide');
    }

    /* Subscribe */
    this.ajaxRequestSubscribe = {};
    this.sendRequestSubscribeNotif = function(uid, cmd, groupId){
        if($this.ajaxRequestSubscribe[uid])return;
        var fn=function(){
            $this.ajaxRequestSubscribe[uid]=true;
            $.post(url_ajax+'?cmd=group_subscribe_action_notif',
                  {group_id: groupId,
                   action: cmd,
                   uid:uid,
                   get_counter_pending:1},
            function(res){
                var data=checkDataAjax(res);
                if(data){
                    clCounters.setDataOneCounter('friends_pending', data.counter_pending, data.counter_pending, true);

                    if(cmd=='approve'){
                        alertCustom(l('there_is_a_new_member_in_your_group'), l('alert_success'));
                    }
                    $this.updateListSubscribeFromBlockedUser(data);
                }else{
                    var $notif=$('#friends_notification_'+uid+'_'+groupId);
                    if ($notif[0]) {
                        $notif.find('button').prop('disabled', false).removeChildrenLoader();
                        clFriends.showNotification(uid);
                    }
                    alertServerError();
                }
                $this.ajaxRequestSubscribe[uid]=false;
            })
        }

        clFriends.removeNotification(uid+'_'+groupId);
        fn()
    }

    this.setGroupModerator = function($btn, groupId, uid) {
        var layer='#list_user_item_layer_action_'+uid,
            $layer=$(layer);
        if($layer.is('.to_show'))return;
        confirmCustom(l('the_user_will_be_as_moderator'), function(){
            $layer.addClass('to_show').addChildrenLoader();

            $.post(url_ajax + '?cmd=group_moderator', {group_id: groupId, user_id: uid},
                function(res){
                    var data  = checkDataAjax(res);

                    if(data !== false){
                        $this.updatePageData(data);
                        location.reload();
                    } else {
                        alert("server error again")
                    }
                    $layer.removeClass('to_show').removeChildrenLoader();

                }
            )
        })
    }

    this.setGroupUnModerator = function($btn, groupId, uid) {
        var layer='#list_user_item_layer_action_'+uid,
            $layer=$(layer);
        if($layer.is('.to_show'))return;
        confirmCustom(l('the_user_will_be_as_unmoderator'), function(){
            $layer.addClass('to_show').addChildrenLoader();
            $.post(url_ajax + '?cmd=group_unmoderator', {group_id: groupId, user_id: uid},
                function(res){
                    var data  = checkDataAjax(res);

                    if(data !== false){
                        $this.updatePageData(data);
                        location.reload();
                    } else {
                        alert("server error again")
                    }
                    $layer.removeClass('to_show').removeChildrenLoader();

                }
            )
        })
    }

    this.confirmRemoveSubscribeList = function($btn, groupId, cmd, uid){
        var layer='#list_user_item_layer_action_'+uid,
            $layer=$(layer);
        if($layer.is('.to_show'))return;
        confirmCustom(l('the_user_will_be_excluded_from_the_group'), function(){
            $layer.addClass('to_show').addChildrenLoader();
            $this.sendRequestSubscribe([], groupId, cmd, uid, false, $layer);
        })
    }

    this.sendRequestSubscribe = function($btn, groupId, cmd, uid, notAlert, $layer){
        $layer=$layer||[];
        cmd=cmd||'';
        $btn=$btn||[];
        groupId=groupId||$this.getId();
        if($btn[0]){
            //groupId=$btn.data('group-id');
            cmd=$btn.data('cmd');
        }

        if($this.ajaxRequestSubscribe[groupId])return;

        var fn=function(){
            $this.ajaxRequestSubscribe[groupId]=true;
            if($btn[0]){
                if($btn.is('.link_join_group')){
                    $btn.data('clLoader', 'link_join_group_loader');
                }
                addChildrenLoader($btn.prop('disabled',true));
            }
            uid=uid||$this.guid;

            $.post(url_ajax+'?cmd=group_subscribe_action',
                  {group_id: groupId,
                   action: cmd,
                   user_id: uid
                   },
            function(res){
                $btn[0]&&removeChildrenLoader($btn.prop('disabled',false));
                $layer&&$layer[0]&&$layer.removeClass('to_show').removeChildrenLoader();
                var data=checkDataAjax(res);

                if ($btn[0] && data.group_private == 'N'
                    && (cmd == 'request' || cmd == 'remove')
                        && typeof clWall == 'object' && clWall.uid == siteGroupUserId
                            && groupId == siteGroupId && uid != siteGroupUserId) {
                    var act=data.action == 'approve'?'approve':'remove';

                    clWall.showPostForFriend($this.uid, act, 1, false, false);
					clWall.updateContentWall();
                }
                if(data){
                    $this.responseRequestSubscribe(data, notAlert);
                }else{
                    alertServerError();
                }
                $this.ajaxRequestSubscribe[groupId]=false;
            })
        }

        if($btn[0] && $btn.data('tooltip'))$btn.blur();
        fn();

        /*if($btn[0] && cmd == 'remove') {
            confirmCustom(l('really_remove_from_friends').replace('{user_name}',$btn.data('userName')),fn,l('are_you_sure'));
        } else {
            fn()
        }*/
    }

    this.responseRequestSubscribe = function(data, notAlert){
        notAlert=notAlert||0;
        //clCounters.setDataOneCounter('friends_pending', data.counter, data.counter);
        var action=data.action;

        /*var $counter=$('#menu_inner_groups_subscribers_edge');
        if ($counter[0]) {
            $counter.find('.number').text(data.counter);
        }
        if(data.list_subscribe){
            clFriends.updateFriends(data.list_subscribe);
            if ($('.list_user_item_photo_'+$this.guid)[0] || action=='approve') {
                clPages.groupsSubscribersReload();
            }
        }*/

        var cmd='request';
        if($this.isPage()){
            var btnName=l('menu_groups_like_edge'),
                btnIcon='fa-thumbs-o-up';
            if(action=='approve'){
                btnName=l('menu_groups_liked_edge');
                cmd='remove';
                btnIcon='fa-thumbs-up';
            }
        } else {
            var btnName=l('menu_groups_unjoin_edge'),
                btnIcon='fa-user-times';
            cmd='remove';
            if(action=='remove_request'){
                btnIcon='fa-user-times';
                btnName=l('remove_request');
                cmd='remove_request';
                location.reload();

            }else if(action=='request'){

                btnIcon='fa-user-plus';
                btnName=l('menu_groups_join_edge');
                cmd='request';
                location.reload();

            } else if (action=='approve' && typeof isPageNoAccessGroup != 'undefined' && isPageNoAccessGroup) {
                location.reload();
                return;
            } else {
                location.reload();

            }
        }

        $('.menu_groups_like_edge, #bl_group_no_access').each(function(){
            var $el=$(this).data('cmd', cmd).attr('data-cmd',cmd);
            if($el.is('#bl_group_no_access')){
                var dur=200,
                    h=$this.$linkPage[0].offsetHeight,
                    txt=l('group_no_access').replace('{data_action}',cmd);
                if(cmd=='remove'||cmd=='remove_request'){
                    txt=l('group_no_access_request');
                }
                var fnTrans=function(){
                    $this.$linkPage.html(txt);
                    $this.$linkPage.css({height:''});
                    var h1=$this.$linkPage[0].offsetHeight;
                    $this.$linkPage.css({height:h});
                    setTimeout(function(){
                        $this.$linkPage.css({height:h1,opacity:1});
                    },100)
                };

                $this.$linkPage.oneTransEnd(fnTrans).css({height:h, width:$this.$linkPage[0].offsetWidth, opacity:0});

                return false;
            }
            var fnUpdate=function(){
                var $tooltip=$el.find('[data-tooltip]');
                if($tooltip[0] && $tooltip.data('tooltip')){
                    $tooltip.tooltip('hide').attr('data-original-title',btnName);
                }else{
                    $el.find('.btn_name, .btn_title').text(btnName);
                }
                $el.find('.fa').removeClass('fa-thumbs-o-up fa-thumbs-up fa-user-times fa-user-plus').addClass(btnIcon);
            }
            if ($el.closest('.bl_column_like_page')[0]) {
                var $bl=$el.closest('.bl_column_like_page'),
                    isHidden=$bl.is('.to_hide_btn');
                isHidden&&fnUpdate();
                $bl.oneTransEnd(function(){
                    if(!isHidden){
                        fnUpdate();
                        $this.visibleMenuItemLikePage();
                    }
                },'height')[(action!='approve')?'removeClass':'addClass']('to_hide_btn');
                isHidden&&$this.visibleMenuItemLikePage();
            } else {
                fnUpdate();
            }
        })

        $this.updateListSubscribeFromBlockedUser(data);

        //$this.updateLink(cmd, title, uidRequest);
    }

    this.updateListSubscribeFromBlockedUser = function(data){
        if(data.group_id == undefined || !$this.groupId || $this.groupId != data.group_id)return;

        var $counter=$('#menu_inner_groups_subscribers_edge');
        if ($counter[0]) {
            $counter.find('.number').text(data.counter);
        }
        if(data.list_subscribe){
            clFriends.updateFriends(data.list_subscribe);
            //if ($('.list_user_item_photo_'+$this.guid)[0]) {
                clPages.groupsSubscribersReload();
            //}
        }
    }

    /* Subscribe */

    this.confirmDeleteList = function(groupId, page){
        var title=(page*1)?l('confirm_delete_page'):l('confirm_delete_group');
        confirmCustom(l('this_action_can_not_be_undone'),function(){$this.deleteList(groupId)}, title);
    }

    this.deleteList = function(groupId){
        var $layer=$('#list_group_image_layer_action_'+groupId);
        if($layer.is('.to_show'))return;
        $layer.addClass('to_show').addChildrenLoader();
        $this.delete(groupId, $layer);
    }

    this.delete = function(groupId, $layer){
        $.post(url_ajax+'?cmd=group_delete',{group_id:groupId},
            function(res){
                var data=checkDataAjax(res);
                if (data !== false){
                    $this.updatePageData(data);
                } else {
                    if($layer[0])$layer.removeClass('to_show').removeChildrenLoader();
                    alertServerError(true);
                }
        })
    }

    this.updaterCounterPage = function(type,title,count){
        if (title) {
            $jq('#left_column_'+type+'_count').html(title);
            $jq('#menu_inner_'+type+'_edge').find('.number').text(count);
        }
    }

    this.updatePageData = function(data) {
        var groupId=data.group_id,
            type=data.type,
            count=data.count*1,
            count_title=data.count_title;


        var $listGroup=$('.list_group_image_'+groupId);
        if ($listGroup[0]) {
            var page=clPages.page,noReplaceHistory=true;
            if($('.users_group_item').length==1){
                page--;
                if(page<=0)page=1;
                noReplaceHistory=false;
            }
            clPages.pageReload(page,false,true,noReplaceHistory);
        }

        if (count) {
            var $li=$('.column_'+type+'_item_'+groupId);
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

    this.confirmBlockUserFromList = function($btn){
        var uid=$btn.data('uid'),layer='#list_user_item_layer_action_'+uid;
        if($(layer).is('.to_show'))return;
        $btn.data('layer',layer);
        var msg=$btn.data('cmd')=='block_user_group'?l('do_you_want_to_block_the_user'):l('the_user_will_be_unblocked');
        confirmCustom(msg, function(){
            clProfile.blockUser($btn,uid,false,function(){
                if((clPages.pageType == 'group_block_list' || clPages.pageType == 'groups_subscribers') && clPages.groupId){
                    clPages.reloadListUser()
                }
            })
        })
    }

    this.visibleMenuItemLikePage = function(hide){
        hide=hide||0;
        var $blBtn=$('.bl_column_like_page');
        if(!$blBtn[0])return;
        var $menu=$jq('#profile_user_more_menu_bl'),
            $li=$menu.find('.menu_groups_like_edge_li'),dur=300;
        if($li[0]){
            var is=$blBtn.is('.to_hide_btn'),$el=$jq('#profile_user_more_menu_wrap');
            $li[is?'show':'hide']();
            $li[is?'removeClass':'addClass']('li_hide');
            if($menu.find('li:not(.li_hide)')[0]){
                $el.stop().css('overflow', 'hidden').slideDown(dur,function(){
                    $el.css('overflow', '')
                })
            }else{
                if(hide){
                    $el.hide();
                } else {
                    $el.stop().css('overflow', 'hidden').slideUp(dur,function(){
                        $el.css('overflow', '')
                    })
                }
            }
        }
    }

    $(function(){
        //$this.groupId=$jq('#group_id').val()*1;
        $this.$upPhotoBl=$jq('#group_photo_upload_bl');
        $this.$upPhotoImg=$jq('#group_photo_upload_img');
        $this.$upPhotoBtn=$('#group_photo_upload');
        $this.$upPhotoFile=$('#group_photo_file');
        $this.$upPhotoBtnDelete=$('#group_photo_upload_delete');
        $this.$upPhotoImgId=$('#group_photo_upload_id');

        $jq('body').on('click', function(e){
            var $target = $(e.target);
            $this.hideMoreMenuAction();
        }).on('click', '.link_join_group', function(e){
            groupSubscribe($(this))
            return false;
        })


        $this.$linkPage=$jq('#bl_group_no_access');
        if($this.$linkPage[0]){
            $this.$linkPage.css({width:$this.$linkPage[0].offsetWidth});
            $win.on('resize orientationchange', function(){
                $this.$linkPage.css({width:'',height:''});
            });
        }

    })

    return this;
}