var quillEditor=null;
var CBlogs = function(guid) {

    var $this=this;

    this.guid=guid*1;
    this.dur=500;
    this.blogId=0;
    this.blogsInfo={
        listLikeUser:''
    };

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
        }
    }

    this.initStyle = function(){
        var fn=function(){
            var w=$jq('#blog_text')[0] ? $jq('#blog_text').width() : $jq('#blogs_post_content').width(),
                d=1.777778;
            $jq('#blog_custom_style')[0].innerHTML=[
                ".wall_video_post .one_media_vimeo{height:", Math.round(w/d), "px;}",
                ".wall_video_post .one_media_youtube{height:", Math.round(w/d), "px;}",
                ".wall_video_post .one_media_metacafe{height:", Math.round(w/d), "px;}",
                ".wall_video_one_post .one_media_vimeo{height:", Math.round((w-80)/d), "px;}",
                ".wall_video_one_post .one_media_youtube, .wall_video_one_post .one_media_metacafe{height:", Math.round((w-80)/d), "px;}"
                ].join("");
        }
        fn();
        //getEventOrientation()
        //getTimeOrientation();
        $win.on('resize', function(){setTimeout(fn,1)});
    }

    /* Editor */
    this.initQuill = function() {
//return;
        initBlockImageCustomBlot();

        //Quill.register('modules/blotFormatter', QuillBlotFormatter.default);

        //var ImageBold = Quill.import('formats/image');
        //ImageBold.className = 'blog_image_post';
        //Quill.register(ImageBold, true);*/

        //var block = Quill.import('blots/block');

        quillEditor = new Quill('#blog_text',{
                modules: {
                    toolbar: '#editor_toolbar',
                    //blotFormatter: {}
                },
                theme: 'snow',
                bounds: $('#blog_text')[0],
                placeholder: l('write_something')
        });

        //Quill.debug('log')

    }

    this.initPhotoId = 0;
    this.initEditor = function() {

        $this.initQuill();

        function isEmptyText(){
            return quillEditor.getLength() <= 1;
        }

        function setDisabledSave($el) {
            var disabled=false,isHideError=false;
            $jq('#blog_post_title, #blog_subject').each(function(){
                var $cur=$(this),
                    $curDef=$(this),
                    val=trim(this.value)
                var isError=!val,msgError=l('required_field');
                if(this.id=='blog_text'){
                    isError=isEmptyText();
                }
                if(isError){
                    if(isSubmit){
                        if($el[0]){
                            if ($el[0] == $curDef[0]) {
                                isHideError=false;
                            } else {
                                isHideError=true;
                            }
                        }
                        showError($cur, msgError, isHideError, isHideError);
                        if (this.id=='blog_text' && !isHideError) {
                            quillEditor.focus();
                        }
                    }
                    isHideError=true;
                    disabled=true;
                } else {
                    hideError($cur);
                }
            })

            disabled = disabled||$this.isProcessUpload;
            $this.$btnAdd[disabled?'addClass':'removeClass']('disabled');

            return disabled;
        }

        $this.$postTitle=$jq('#blog_subject').on('change propertychange input', function(){setDisabledSave($(this))})
             .on('focus',function(){focusError($(this))})
             .on('blur',function(){blurError($(this))}).focus();

        $this.$postTextBl=$jq('#blog_text');
        $this.$postText=$jq('.ql-editor');

        //quillEditor.enable(false);
        //quillEditor.debug('info');


        function getText(){
            if (quillEditor.getLength() > 1) {
                return trim($this.$postText[0].innerHTML);
            } else {
                return '';
            }
            //quillEditor.getText(), quillEditor.getLength()
        }

        quillEditor.on('text-change', function(delta, oldDelta, source) {
            /*if (source == 'api') {
                console.log("An API call triggered this change.");
            } else if (source == 'user') {
                console.log("A user action triggered this change.", delta, oldDelta, source);
            }*/
            if (source == 'user') {
                setDisabledSave($this.$postTextBl);
                if (isEmptyText()) {
                    quillEditor.removeFormat(0);
                }
            }
        })

        quillEditor.on('selection-change', function(range, oldRange, source) {
            if(range){
                focusError($this.$postTextBl)
            }else{
                console.log('Blur Editor');
                blurError($this.$postTextBl);
                $jq('button','#editor_toolbar').prop('disabled', true);
            }
        })

        //quillEditor.focus();

        var isSubmit=false;
        $this.$btnAdd=$('#blog_post_submit').click(function(){
            isSubmit=true;
            //if(setDisabledSave([]))return false;

            var fnDisabledControls=function(disabled){
                $('#blog_subject, #blog_tags, #blog_allowed_comments, #blog_post_submit').prop('disabled', disabled);
                if (disabled) {
                    quillEditor.enable(false);
                    addChildrenLoader($this.$btnAdd);
                } else {
                    quillEditor.enable(true);
                    removeChildrenLoader($this.$btnAdd);
                }
            }

            fnDisabledControls(true);

            var text=getText(),
                $text=$('<div>'+text+'</div>'),
                img={},i=0;

            $text.find('.blog_image_post').each(function(){
                var $el=$(this), $img=$el.find('img');
                if ($img[0]) {
                    var id=$img.data('id')?$img.data('id'):0;
                    img[++i]={id:id, src:$img[0].src};
                    $el.replaceWith('{img:'+i+'}');
                }else{
                    $el.remove();
                }
            })

            $text.find('.wall_video_post').each(function(){
                var $el=$(this),code=$el.data('code');
                if(code){
                    $el.replaceWith('{'+$el.data('type')+':'+code+'}');
                }else{
                    $el.remove();
                }
            })

            data={
                blog_id:$this.blogId,
                subject:trim($this.$postTitle.val()),
                text:$text.html(),
                tags:trim($('#blog_tags').val()),
                images:img,
                comments_enabled: $jq('#blog_allowed_comments').prop('checked')?1:0
            };

            //<p><br></p>
            //console.log(data);
            //return;

            $.post(url_ajax+'?cmd=blog_add',data,function(res){
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
                                console.log(this.className, this.innerText);
                                showError($('#blog_'+this.className), this.innerText, isHideError, isHideError);
                                if (this.className=='text' && !isHideError) {
                                    quillEditor.focus();
                                }
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


        var text='ghghghhttp://sitesman.com/s/1014-2018-02-20_21-42-06.png  sdfasdfasdf  \n\
                  http://sitesman.com/s/1014-2018-02-20_21-48-31.png www.chameleondeveloper.com';
        grabsTextLink(text);

        /*
        var isMobile=device.mobile();
        if (isMobile) {
            $this.$postText.contextmenu(function() {
               return false;
            })
        }

        $('.dropdown-menu input', '#editor_toolbar').click(function(){
            return false
        }).change(function(){
            $(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle')
        }).keydown('esc', function(){
            this.value='';
            $(this).change();
        })

        var stateResize=false, $imgResize;
        $('body').on('mousemove', 'img', function(e){
            if(stateResize)return;
            var $img=$(this),el=getOffsetElement(this),mouse=getMouseOffset(e),
                dT=(mouse.top-el.top > $img.height()-10),
                dL=(mouse.left-el.left > $img.width()-10);
            $img.removeClass('se-resize e-resize s-resize');
            if(dT && dL) {
                $img.addClass('se-resize');
            } else if (dL) {
                $img.addClass('e-resize');
            } else if (dT) {
                $img.addClass('s-resize');
            }
            return false;
        }).on('mousedown touchstart', 'img', function(e){
            var $img=$(this);
            if(e.type=='touchstart'){
                if(e.originalEvent.touches.length!=1)return false;
                $img.addClass('se-resize');
            }
            if ($img.is('.se-resize')||$img.is('.e-resize')||$img.is('.s-resize')) {
                stateResize=getMouseOffset(e);
                $imgResize=$img;
                $this.$postText.prop('contenteditable', false);
            }
            return false;
        })

        $doc.on('mousemove touchmove', function(e){
            if(!stateResize)return;
            isMobile&&$jq('#cham-page').addClass('overflow_hidden');
            var imgH=$imgResize.height(), imgW=$imgResize.width(),
                maxW=$this.$postText.width(), p=imgW/imgH, w,
                mouse=getMouseOffset(e);
            if(e.type=='touchmove'){
                var ev=e.originalEvent.touches;
                if(ev.length!=1)return false;

                e.preventDefault();//????
                var topD = stateResize.top-mouse.top,
                    leftD = stateResize.left-mouse.left;
                if (Math.abs(topD) > Math.abs(leftD)) {
                    w = Math.round((imgH + topD*-1)*p);
                } else {
                    w = imgW + leftD*-1;
                }
            } else {
                if ($imgResize.is('.s-resize')) {
                    w = Math.round((imgH + (stateResize.top-mouse.top)*-1)*p);
                }else{
                    w = imgW + (stateResize.left-mouse.left)*-1;
                }
            }
            stateResize=mouse;

            if(w>maxW){w=maxW} else if(w<50)w=50;
            $imgResize.width(w);
            return false;
        }).on('mouseup touchend', function(e){
            if(!stateResize)return;
            isMobile&&$jq('#cham-page').removeClass('overflow_hidden');
            stateResize=false;
            $imgResize.removeClass('se-resize e-resize s-resize');
            $this.$postText.prop('contenteditable', true);//.focus();
            return false;
        })*/
    }
    /* Editor */


    /* Image upload */
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
            ind = +new Date,
            url = url_ajax +
                  '?cmd=upload_temp_blog_image&input_name=blog_image_file&ind='+ind,
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
                        res=checkDataAjax(data);
                    if (res!=false) {
                        if (res.error) {
                            alertCustom(res.error);
                            var btnTitle=$this.$upPhotoImgId.val()*1?l('use_another'):l('choose_an_image');
                            $this.$upPhotoBtn.find('.btn_title').text(btnTitle);
                            fnRes();
                        } else {
                            console.log(res, res.url);
                            $this.$upPhotoImgId.val(res.id);
                            var url=urlFiles+res.url;
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
    /* Image upload */

    /* Comments */
    this.inViewport = function(el){
        return inViewport(el,{container:$('body')[0],threshold:-40})//container:$('')[0]
    }

    this.scrollToNative = function($el,call,t){
        t=defaultFunctionParamValue(t, $this.dur*1.5);
        clMediaTools.getElOnScroll().scrollTo($el, t, {axis:'y', interrupt:true, easing:'easeOutExpo', over_subtract:{top:3}, onAfter:call});
    }

    this.initFeedComment = function(id,fn,$inp) {
        $inp=$inp||$(id);
        if(!$inp[0])return;
        if(typeof fn!='function')fn=$this.commentAdd;
		initAutoSize($inp,fn);

		var $btn=$inp.nextAll('.comment_action').find('.wall_post_send');
		clMediaTools.initTextareaControl($inp, $btn);
        $btn.click(function(){
            fn($inp);
        })
    }

    this.initFeedCommentReplies = function(id) {
        var $inp=$('#blog_post_comment_replies_input_'+id);
        if(!$inp[0])return;
		initAutoSize($inp,$this.commentAdd);

		var $btn=$inp.nextAll('.comment_action').find('.wall_post_send');
		clMediaTools.initTextareaControl($inp, $btn);
        $btn.click(function(){
            $this.commentAdd($inp);
        })
    }

    this.showFrmTop = function(){
        clMediaTools.showFrmReplyComment([], 'blogs_post_feed_comment_top', false, false, false, true);
    }

    this.hideFrmTop = function(){
        if(!$this.$fieldCommentTop.is(':hidden')){
            clMediaTools.hideFrmReplyComment($this.$fieldCommentTop[0].id, false, false)
        }
    }

    this.showFrmReply = function(el){
        var $el=$(el);
        clMediaTools.showFrmReplyComment($el, 'blog_post_comment_replies_post_'+$(el).data('cid'), false, true, false, true);
    }

    this.hideFrmReply = function(id, call){
        clMediaTools.hideFrmReplyComment('blog_post_comment_replies_post_'+id, false, true, call)
    }

    this.fnBlur = function($inp, $comment, call) {
        var d = isMobileSite ? 150 : 1,
            dt = isMobileSite ? evWndResTime : 1;
        setTimeout(function(){
            $inp.blur();
            setTimeout(function(){
                if (!$this.inViewport($comment[0])) {
                    $this.scrollToNative($comment, call, $this.dur);
                } else {
                    if(typeof call == 'function')call()
                }
            },dt)
        },d)
    }

    this.showLastFieldComment = function($box,number,callCompete){
        $box=$box||$('#blogs_post_comments > .blog_post_comment_item');
        number=number||$this.numberCommentsFrmShow;
        var l=$box.length;

        if(l>$this.numberCommentsFrmShow){
            if($this.$fieldCommentBottom.is(':hidden')){
                clMediaTools.showFrmReplyComment([], 'blogs_post_feed_comment_bottom', false, false, true);
            }
        } else {
            clMediaTools.hideFrmReplyComment($this.$fieldCommentBottom[0].id, false, false)
        }
    }

    this.updatePageCounter = function(count){
        var $loadMore=$jq('#blogs_post_load_more_comments_bl');
        if (count>0) {
            var lVar=count==1?'wall_comments_one_count':'wall_comments_count',
                countTitle=l(lVar).replace('{comments_count}',count);
            $this.$commentsCount.text(countTitle);
            $this.$blCommentsCount.removeClass('to_hide');
        } else {
            $this.$blCommentsCount.addClass('to_hide');
        }
        if ($loadMore[0]) {
            var $loadNumber=$loadMore.find('.comm_to_comm_text_number');
            if ($loadNumber.is('.to_show')) {
                $loadNumber.find('.number_all').text(count);
            }
            var v=$this.$blComments.find('.blog_post_comment_item').length;
            $loadNumber.find('.number_view').text(v);
        }

    }

    this.updateRepliesCounter = function(cid, countReplies){
        var $el=$('#blog_post_comments_replies_load_'+cid).find('.comm_to_comm_text_number.to_show');
        if(!$el[0])return;
        var v=$('#blog_post_comments_replies_list_'+cid).find('.comments_replies_item').length;
        $el.find('.number_view').text(v);
        $el.find('.number_all').text(countReplies);
    }

    this.updateCommentCounter = function($bl, inc){
        return;
        /*if ($bl && $bl[0]) {
            var $loadReplies=$bl;
        } else {
            var $loadReplies=$jq('#blogs_post_load_more_comments_bl');
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
            $numberAll.text(numberAll);*/
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
            $comment;
        if (rCid) {
            $comment = $('<div id="comments_replies_item_'+send+'" data-rcid="'+send+'" class="comment_to_comment_container comments_replies_item">'+
                            '<div class="comment_item_wrapper">'+
                            '</div>'+
                         '</div>');
        } else {
            $comment = $('<div id="blog_post_comment_'+send+'" data-cid="'+send+'" class="blog_post_comment_item item">'+
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
				if (!sticker){
					$this.hideFrmReply(rCid);
					$this.fnBlur($inp, $comment, rCid);
				}
            } else {
                $this.fnBlur($inp, $comment, function(){
                    //$this.hideFrmTop();
                    $this.showLastFieldComment()
                });
            }

            comment=clMediaTools.replaceUserName(comment, $inp.data('name'), $inp.data('uid'), $inp.data('groupId'));
            comment=emojiToHtml(comment);

            var data={comment:comment,
                      blog_id:$this.blogId,
                      reply_id:rCid,
					  audio_message_id:audioMessageId,
					  image_upload:uploadImageLoaded?1:0,
					  ind:uploadImageLoaded
				};
			if (sticker) {
				data['sticker'] = sticker.data;
			}
            $.post(url_ajax+'?cmd=blogs_post_comment_add',data,
            function(res){
                data=checkDataAjax(res);
                if (data!==false){
                    var $data=$(trim(data));
                    if (rCid) {
                        $data=$data.find('.comments_replies_item');
                        if(!$data[0]||$('#'+$data[0].id)[0])return;
                        var resCid=$data.data('rcid');
                    } else {
                        $data=$data.filter('.blog_post_comment_item');
                        if(!$data[0]||$('#'+$data[0].id)[0])return;
                        var resCid=$data.data('cid');
                    }
                    $comment.data('cid', resCid).attr({'id':$data[0].id, 'data-rcid':resCid});
                    clMediaTools.commentUpdate($comment, $data);

                    if (rCid) {
                        //$this.updateCommentCounter($('#blog_post_comments_replies_load_'+rCid));
                    } else {
                        //$this.updateCommentCounter();
                    }

                }else{
                    //$this.commentHide(id, send, true);
                    alertServerError(true)
                }
            })
        }

		var fnSubmit=function(){
			if (rCid) {
                clMediaTools.addCommentToBl($comment, rCid, false, fnAdd, '#blog_post_comments_replies_list_');
            } else {
                clMediaTools.addCommentToBl($comment, 0, false, fnAdd, false, $this.$blComments);
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

    /* Like */
    this.likeAddCommentAjax={};
    this.like = function($el){
        var rcid=$el.data('rcid'),
            cid=$el.data('cid')||rcid;

        if ($el.is('.disabled') || $this.likeAddCommentAjax[cid]) return;
        $this.likeAddCommentAjax[cid]=true;
        $el.addClass('disabled');

        var like=$el.data('like')*1,
            $li=$el.closest('li');

        clMediaTools.likeChangeStatus($el, like);

        $.post(url_ajax+'?cmd=blogs_post_comment_like',
              {blog_id:$this.blogId,
               cid:cid,
               like:like},
            function(res){
                var data=checkDataAjax(res);
                if (data){
                    var $bl=rcid?$('#blog_post_comment_reply_likes_bl_'+rcid):$('#blog_post_comment_likes_bl_'+cid),
                        dataLike={count:data['likes'],title:data['likes_users']};
                    clMediaTools.updateCommentOneLike(dataLike, $bl);
                } else {
                    clMediaTools.likeChangeStatus($li.find('.comment_item'), !like);
                    $el.removeClass('disabled');
                    alertServerError();
                }
                $this.likeAddCommentAjax[cid]=false;
        })
    }

    /* Like post */
    this.likeAddAjax=false;
    this.likeAdd = function($link,cmd) {
        id=$this.blogId;
		$link=$link||$('#blog_post_feed_like_hand');
        if($this.likeAddAjax)return;
        $this.likeAddAjax=true;
        var $icon=$('.icon',$link).addChildrenLoader();
        cmd=cmd||'blogs_post_like';
		$.post(url_ajax+'?cmd='+cmd,{blog_id:$this.blogId}, function(data){
            $icon.removeChildrenLoader();
            data=checkDataAjax(data);
            if(data !== false){
                var $likes=$(data).filter('.who_liked');
                $jq('#update_server').append($(data).filter('script'));
                $this.likeChange($likes);
            } else {
                alertServerError();
            }
            $this.likeAddAjax=false;
        })
	}

	this.likeDelete = function($link) {
        $this.likeAdd($link, 'blogs_post_unlike');
	}

    this.setLikePostInfo = function(list) {
        $this.blogsInfo.listLikeUser=list;
    }

    this.likeChange = function($cont) {
        var $blLikes=$('#blog_post_feed_like_result');

        if(!$blLikes[0]||$blLikes.is('.animate'))return;
        $blLikes.addClass('animate');
        if(!$cont[0]){
            $this.blogsInfo.listLikeUser='';
        }
        $('#blog_post_feed_like_action')[$this.blogsInfo.listLikeUser.indexOf($this.guid) === -1?'removeClass':'addClass']('wall_like_hidden');

        if(!$cont[0]||!$this.blogsInfo.listLikeUser){
            $blLikes.slideUp($this.dur,function(){
                $blLikes.removeClass('animate');
                $blLikes.find('.who_liked_bl').empty();
            });
            return;
        }

        var $el=$cont.find('.who_liked_bl');

        if($blLikes.is(':hidden')){
            $blLikes.find('.who_liked_bl').html($el.html()).end().slideDown($this.dur,function(){
                $blLikes.removeClass('animate');
            })
        }else{
            var $blF=$blLikes.find('.who_liked_bl:last').addClass('to_top')
                    .delay(1).toggleClass('to_top to_state_op',0);
            $blLikes.prepend($el.addClass('to_top_op').delay(1)
            .oneTransEnd(function(){
                $blF.remove();
                $el.removeClass('to_state');
                $blLikes.removeClass('animate');
            }).toggleClass('to_top_op to_state',0));
        }
    }
    /* Like post */
    /* Like */

    this.loadMoreComments = function($el, limit){
        limit=limit||0;
        $el=$el||[];
        var $bl=$jq('#blogs_post_load_more_comments_bl');
		if(!$bl[0] || $bl.is('.disabled'))return;


        var $blComments=$jq('#blogs_post_comments'),
            $firstComment=$blComments.find('.blog_post_comment_item:first');
        if(!$firstComment[0])return;
        $bl.addClass('disabled');

        addChildrenLoader($el);

        var id=$this.blogId,
            cmd='get_blog_post_comment',
            lastId=$firstComment.data('cid');

        var fnLoad=function(){
            var dataRes={blog_id:id,
                         load_more:1, last_id:lastId, limit:limit}

            $.ajax({url:url_ajax+'?cmd='+cmd,
                    type:'POST',
                    data:dataRes,
                    timeout: globalTimeoutAjax,
                    //cache: false,
                    success: function(res){
                        var data=checkDataAjax(res);
                        if(data){
                            var $data=$(data),
                            $comments=$data.find('.blog_post_comment_item').hide();
                            if($comments[0]){
                                var $numberView=$bl.find('.number_view'),
                                    count=$numberView.text()*1,
                                    countAll=$bl.find('.number_all').text()*1,
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
                            removeChildrenLoader($el);
                            $bl.removeClass('disabled');
                        },200)
                    },
                    error: function(xhr, textStatus, errorThrown){
                        globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                            debugLog('Blogs: Retry load more comments', dataRes);
                            fnLoad()
                        })
                    },
            })
        }
        fnLoad();
    }


    this.commentsRepliesLoadMore = function($el, cid, limit) {
        if($el.is('.disabled')) return;
        $el.addClass('disabled');
        limit=limit||0;
        addChildrenLoader($el);

        var $loadReplies=$('#blog_post_comments_replies_load_'+cid),
            $listReplies=$('#blog_post_comments_replies_list_'+cid),
            $firstReplies=$listReplies.find('.comments_replies_item').first(),
            lastId=$firstReplies[0] ? $firstReplies.data('rcid') : 0,
            dataRes={
                comment_id:cid,
                type:'blogs_post',
                last_id:lastId,
                load_more:1,
                limit:limit
            };

        var fnLoad=function(){
            $.ajax({url:url_ajax+'?cmd=get_comment_replies',
                type:'POST',
                data:dataRes,
                timeout: globalTimeoutAjax,
                //cache: false,
                success: function(res){
                    res=checkDataAjax(res);
                    if(res){
                        var $data=$(res),
                            $loadRepliesNumber=$loadReplies.next('.comm_to_comm_text_number'),
                            $numberView=$loadReplies.find('.number_view'),
                            numberStart=$numberView.text()*1,
                            numberAll=$data.find('.number_all').text()*1;

                        if (numberAll) {
                            $loadReplies.find('.number_all').text(numberAll);
                            if (!$loadRepliesNumber.is('.to_show')) {
                                $el.find('.comments_replies_load_title').text(l('view_previous_replies'));
                                $loadRepliesNumber.addClass('to_show');
                            }
                        } else {
                            $this.hideReplyLoad($loadReplies);
                        }

                        $this.addCommentsRepliesLoadMore($data,cid,numberStart,numberAll,function(){
                        })

                    }
                    removeChildrenLoader($el);
                    $el.removeClass('disabled');
                },
                error: function(xhr, textStatus, errorThrown){
                    globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                        debugLog('Blogs: Retry load more replies comments', dataRes);
                        fnLoad();
                    })
                },
            })
        }
        fnLoad();
	}

    this.addCommentReplyFirstPlace = function($comment, cid) {
        if(!$comment[0])return;

        var send=$comment.data('send');
        if (!$('#'+$comment[0].id)[0] && !$('#blog_post_comments_replies_item_'+send)[0]) {
            clMediaTools.addCommentToBl($comment, cid, 'prependTo', false, '#blog_post_comments_replies_list_');
            return true;
        } else {
            return false;
        }
        return false;
    }

    this.addCommentsRepliesLoadMore = function($data, cid, numberStart, numberAll, call) {
        if(!$data[0]) return;
        var $comments=$data.find('.comments_replies_item'),
            $loadReplies=$('#blog_post_comments_replies_load_'+cid),
            $numberView=$loadReplies.find('.number_view');
        if ($comments[0]) {
            var i=$comments.length-1, $el;
            (function fu(){
                $el=$comments.eq(i);
                if(!$el[0] || i < 0){
                    if(typeof call == 'function')call();
                    return;
                }

                if (numberAll) {
                    numberStart++;
                    if(numberStart>=numberAll){
                        $this.hideReplyLoad($loadReplies);
                        numberStart=numberAll;
                    }
                    $numberView.text(numberStart);
                }

                $this.addCommentReplyFirstPlace($el, cid);
                i--; fu();
            })()
            return true;
        } else {
            return false;
        }
    }

    this.hideReplyLoad = function($el){
        $el.slideUp($this.dur,function(){
            $(this).removeClass('to_show').removeAttr('style')
        })
    }

    this.deleteComment = {};
    this.confirmDeleteComment = function($el,cid,rCid){
        rCid=rCid||0;
        var key=cid+'_'+rCid;
        if($this.deleteComment[key])return;
        confirmCustom(l('are_you_sure'), function(){$this.deleteComment(cid,rCid)}, l('confirm_delete_comment'));
        $el.closest('.more_menu_collapse').collapse('hide');
    }

    this.deleteComment = function(cid,rCid){
        rCid=rCid||0;
        var $commItem,cidD=cid,
            key=cid+'_'+rCid;
        $this.deleteComment[key]=true;
        if (rCid) {
            cid=rCid;
            $commItem=$('#blog_post_comments_replies_item_'+rCid);
        } else {
            $commItem=$('#blog_post_comment_'+cid);
        }

        $commItem.addClass('deleted').fadeTo(200,.4);

        $.post(url_ajax+'?cmd=blogs_post_comment_delete',{cid:cid},
            function(res){
                var data=checkDataAjax(res);
                if (data !== false){
                    $this.commentHide(cidD, rCid, function(){
                        if(rCid){
                            $this.updateRepliesCounter(cidD, data);
                            var $el=$('#blog_post_comments_replies_load_'+cidD);
                            if($el[0] && $el.is(':visible')){
                                $this.commentsRepliesLoadMore($el.find('.comments_replies_load_link'), cidD, 1)
                            }
                        }else{
                            $this.updatePageCounter(data);
                            $this.showLastFieldComment();
                            if ($this.$blCommentsLoadMore.is(':visible')) {
                                $this.loadMoreComments($this.$blCommentsLoadMore.find('.comments_replies_load_link'),1);
                            }
                        }
                    })
                } else {
                    $this.deleteComment[key]=false;
                    $commItem.removeClass('deleted').fadeTo(200,1);
                    alertServerError(true);
                }
        })

    }

    this.commentHide = function(cid, rcid, call, noRemove) {
        clMediaTools.commentHide(cid, rcid, false, noRemove, call, '', true);
    }
    /* Comments */
    this.confirmDeleteBlog = function($el){
        if($el.is('.disabled'))return;
        confirmCustom(l('really_remove_this_post'), function(){
            $this.deleteBlog($el);
        }, l('are_you_sure'));
    }


    this.deleteBlog = function($el){
        $el.addClass('disabled');

        addChildrenLoader($el);

        $.post(url_ajax+'?cmd=blogs_post_delete',
              {blog_id:$this.blogId},
            function(res){
                var data=checkDataAjax(res);
                if (data){
                    redirectUrl($this.pageBlogs);
                } else {
                    $el.removeClass('disabled');
                    removeChildrenLoader($el);
                    alertServerError();
                }
        })
    }


    /* Popup Image */
    $this.$ppShowImage=[];
    $this.$ppShowImageClone;
    this.closePpImageBack = function(){
        if(!$this.isOpenImage)return;
        if(!backStateHistory()){
            $this.closePpImage();
        }
    }

    this.closePpImage = function(){
        $this.$ppShowImage[0] && $this.$ppShowImage.modal('hide');
    }

    this.initPpImage = function(){
        $(function(){
            if($this.$ppShowImage[0])return;
            $this.$ppShowImageClone=$jq('#pp_im_image').clone()
                                    .find('.pp_gallery_overflow')
                                    .addClass('gallery_blackout').end()
                                    .find('.icon_close').attr('onclick','clBlogs.closePpImageBack();')
                                    .end().html();
            $this.$ppShowImage=$jq('#pp_im_image').clone();

            $jq('body').on('click', '.blog_image_post > img', function(e){
                $this.showPopupImage($(this), this.src);
                return false;
            }).on('click', function(e){
                var $targ=$(e.target);
                if($targ.is('.pp_gallery_overflow') || $targ.is('.navbar-default') || $targ.is('.navbar-header')){
                    $this.closePpImageBack();
                }
            })

        })
    }

    this.isOpenImage=false;
    this.showPopupImage = function($imgOrig, src){
        debugLog('IM showPopupImage', src);
		if($this.isOpenImage || !$this.$ppShowImage[0])return;

        $this.isOpenImage=true;
        $this.$ppShowImage.empty().html($this.$ppShowImageClone);

        var $img=$('<img src="' + src + '">').addClass('fade_to_1_4 fade_to_0_4')
            .one('load',function(){
                $('.css_loader', $this.$ppShowOnePost).oneTransEnd(function(){
                    $(this).remove();
                }).addClass('hidden');
                $(this).removeClass('fade_to_0_4');
            });

        $('.photo_one_cont', $this.$ppShowImage).append($img);

        $('.pp_im_image_time_ago', $this.$ppShowImage).text($imgOrig.data('timeAgo'));
        $('.pp_im_image_date', $this.$ppShowImage).text($imgOrig.data('date'));

        $('.pp_im_image_user_name', $this.$ppShowImage)
            .attr('href', $imgOrig.data('userUrl'))
            .text($imgOrig.data('userName'));
        $('.pp_im_image_user_photo_link', $this.$ppShowImage).css('background-image', 'url('+urlFiles+$imgOrig.data('userPhoto')+')')
            .attr('href', $imgOrig.data('userUrl'));

        var isLink=isSiteOptionActive('gallery_show_download_original', 'edge_gallery_settings');
        if (isLink) {
            var id= +new Date, sel='pp_im_image_more_options_'+id;
            $('.pp_im_image_more_menu > .upload_menu_link', $this.$ppShowImage).attr('data-target','#'+sel);
            $('.pp_im_image_more_options', $this.$ppShowImage).attr('id', sel);

            $('.pp_im_image_direct_link', $this.$ppShowImage).attr('href', src);
        } else {
            $('.pp_im_image_more_menu', $this.$ppShowImage).remove();
        }

        setPushStateHistory('im_gallery');
        $this.$ppShowImage.one('hidden.bs.modal',function(){
			//checkOpenModal();
        }).one('hide.bs.modal',function(){
            $this.isOpenImage=false;
            $jq('html, body').removeClass('overh');
            $this.$ppShowImage.oneTransEndM(function(){
                $this.$ppShowImage.removeClass('to_show').empty();
                $jq('body').removeClass('gallery_im_open');
            })
        }).one('shown.bs.modal',function(){
        }).one('show.bs.modal',function(){
            $this.$ppShowImage.addClass('to_show');
            $jq('body').addClass('gallery_im_open');
            $jq('html, body').addClass('overh');
        }).modal('show');

	}
    /* Popup Image */

    $(function(){
        $this.$upPhotoBl=$jq('#blog_photo_upload_bl');
        $this.$upPhotoImg=$jq('#blog_photo_upload_img');
        $this.$upPhotoBtn=$('#blog_photo_upload');
        $this.$upPhotoFile=$('#blog_photo_file');
        $this.$upPhotoBtnDelete=$('#blog_photo_upload_delete');
        $this.$upPhotoImgId=$('#blog_photo_upload_id');

        $this.$blComments=$('#blogs_post_comments');
        $this.$fieldCommentTop=$('#blogs_post_feed_comment_top');
        $this.$fieldCommentBottom=$('#blogs_post_feed_comment_bottom');
        $this.$blCommentsLoadMore=$jq('#blogs_post_load_more_comments_bl');
        $this.$blCommentsCount=$jq('#blogs_post_comments_count');
        $this.$commentsCount=$jq('#blogs_post_comments_count').find('.comments_count');

        $jq('body').on('click', '.timeline_photo_comment', function(e){
            if(!$this.$ppShowImage[0])return false;

            var $el=$(this),
                $img=$el.find('img');

            var $time=$el.closest('.comment_text').find('.comment_reply_data, .comment_data'),
                $photo=$el.closest('.comment_item_wrapper').find('.photo'),
                $userInfo=$photo.find('a'),
                photo=$userInfo.data('photo'),
                nameUser=$userInfo.attr('title'),
                urlUser=$userInfo[0].href,
                timeAgo=$time.text(),
                date=$time.data('photoDate');

            $img.data({id:0,date:date,timeAgo:timeAgo,
                       userName:nameUser,userUrl:urlUser,userPhoto:photo})
                .attr({'data-id':0,'data-date':date,'data-time-ago':timeAgo,
                       'data-user-name':nameUser,
                       'data-user-url':urlUser,'data-user-photo':photo});

            $this.showPopupImage($img, $img[0].src);
            return false;
        }).on('click', function(e){
			var $targ=$(e.target);
			smileBlockHideTarget($targ);
			stickerBlockHideTarget($targ);
        })

        $('.menu_blogs_add_edge').click(function(){
            redirectUrl(urlPagesSite.blogs_add);
            return false;
        });

    })
    return this;
}