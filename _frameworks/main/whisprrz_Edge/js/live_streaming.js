var CLiveStreaming = function(guid) {

    var $this=this;

    this.guid=guid*1;
    this.dur=500;
    this.lsId=0;
    this.lsVideoId=0;

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
        }
    }


    this.initLoad = function(){
        $(function(){
            $this.$liveCommentBl=$('#ls_content');
            $this.$liveCommentBlHeight=$this.$liveCommentBl.height();
            $this.$tmplCommentBlClone=$('#template_bl_comment').clone();
        })
    }

    this.init = function(){
        $this.initBlock();
        $this.initStyle();
        $this.initPpImage();
    }

    this.initBlock = function(){
        $(function(){
            var isChromeWheele=false;
            if (window.WheelEvent && !window.MouseScrollEvent
                && /chrome/.test(navigator.userAgent.toLowerCase())) {
                isChromeWheele=true;
            }

            $this.$blComments=$('#ls_comments');

            $this.$liveListViewer=$('#ls_list_viewers');
            $this.$liveListViewerTitle=$('#ls_list_viewers_title');
            $this.$liveListViewerUl=$('#ls_list_viewers_ul');
            $this.$liveListViewerItemHide=0;
            $this.$listVwMore=$('#ls_list_viewers_more'),
            $this.$listVwMoreLink=$('#ls_list_viewers_more_up, #ls_list_viewers_more_down');
            $this.$listVwMoreUp=$('#ls_list_viewers_more_up');
            $this.$listVwMoreDown=$('#ls_list_viewers_more_down');

            var fnWheele=function(e){
                if($this.$liveListViewer.is('.disabled')) return false;
                var dir=e.deltaY>0?'+=':'-=',
                    d=$this.getRowHViewer()*2;
                $this.$listVwBl.not(':animated')
                     .animate({scrollTop: dir+d},250,function(){$this.$listVwBl.scroll()});
            }
            $this.$listVwBl=$('#ls_list_viewers_bl')
            .on('scroll', function(e){
                //if($this.$liveListViewer.is('.disabled')) return false;
                var h=$this.getRowHViewer(),sT=$this.$listVwBl.scrollTop();
                $this.$listVwBl.not(':animated').clearQueue().delay(200).animate({
                    scrollTop: Math.round(sT/h)*h
                }, 200, function(){
                    sT=$this.$listVwBl.scrollTop();
                    if (sT<5) {
                        $this.$listVwMoreUp.addClass('disabled');
                        $this.$listVwMoreDown.removeClass('disabled');
                    } else {
                        $this.$listVwMoreUp.removeClass('disabled');
                        if ($this.$listVwBl[0].scrollHeight < (sT + (h*2) + 5)) {
                        //if ($this.$listVwBl[0].scrollHeight < (sT + (h*2) + 10)) {
                            $this.$listVwMoreDown.addClass('disabled');
                        } else {
                            $this.$listVwMoreDown.removeClass('disabled');
                        }
                    }
                })
            }).wheel(function(e){
                if(isChromeWheele)return !$this.$listVwMore.is('.to_show');
                fnWheele(e);
                return !$this.$listVwMore.is('.to_show');
            }).on('touchmove', function(e){
                e.preventDefault();
            })

            if (isChromeWheele) {
                $this.$listVwBl[0].addEventListener('wheel', function(e){
                    fnWheele(e);
                    return false
                }, {useCapture: true, passive: false});
            }

            $this.$listVwMoreLink.click(function(){
                if($this.$liveListViewer.is('.disabled')) return false;
                var $el=$(this);
                if ($el.is('.disabled')) return false;
                var dir=$el.data('up')?'-=':'+=',
                    d=$this.getRowHViewer()*2;
                $this.$listVwBl
                .animate({scrollTop: dir+d}, 250, function(){
                    $this.$listVwBl.scroll()
                })
                return false;
            })

            $this.$fieldCommentTop=$('#ls_feed_comment_top');
            $this.$fieldCommentBottom=$('#ls_feed_comment_bottom');
            $this.$blCommentsCount=$('#ls_comments_count');
            $this.$commentsCount=$('#ls_comments_count').find('.comments_count');
        })
    }

    this.initStyle = function(){
        var fn=function(){
            var w=$jq('#ls_content').width(),
                d=1.777778;
            $jq('#ls_custom_style')[0].innerHTML=[
                ".wall_video_post .one_media_vimeo{height:", Math.round(w/d), "px;}",
                ".wall_video_post .one_media_youtube{height:", Math.round(w/d), "px;}",
                ".wall_video_post .one_media_metacafe{height:", Math.round(w/d), "px;}",
                ".wall_video_one_post .one_media_vimeo{height:", Math.round((w-80)/d), "px;}",
                ".wall_video_one_post .one_media_youtube, .wall_video_one_post .one_media_metacafe{height:", Math.round((w-80)/d), "px;}"
                ].join("");
            $this.setRowElViewer(false,true);

            if ($this.$liveListViewer.is('.to_show, .to_show0')) {
                $this.lsListViewerCheck($this.lsViewerShowMore);
            }
        }
        fn();
        //getEventOrientation()
        //getTimeOrientation();
        $win.on('resize', function(){setTimeout(fn,1)});
    }

    this.clearData = function(){
        $this.lsId=0;
        $this.lsVideoId=0;

        $this.queueComments = {};
        $this.queueCommentsInc = 0;

        $this.queueUpdateCounters = {};
        $this.queueCountersInc = 0;
        $this.itemsInfo = {};
        $this.commentsCacheAll = {};
        $this.commentsCache = {};
        $this.commentsLast = {};
        $this.commentsFirst = {};
        $this.commentsVisible = {};

        $this.commentsReplyCache = {};
        $this.itemCommentsReplyFirst = {};
        $this.itemCommentsReplyLast = {};
        $this.commentsInfo = {};
    }

    this.noAction = function(){
        //return false;
        var liveId=_lsLiveId||_lsLiveIdEnd;
        var res=!liveId || liveId != $this.lsId;
        if (res) {
            debugLog('Live stream: noAction', [_lsLiveId, $this.lsId], '#f74e4e');
        }
        return res;
    }

    /* Comments */
    this.inViewport = function(el, container){
        if (typeof container == 'undefined') {
            container=$('body')[0];
        }
        return inViewport(el,{container:container,threshold:-40})//container:$('')[0]
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
        var $inp=$('#ls_comment_replies_input_'+id);
        if(!$inp[0])return;
        initAutoSize($inp,$this.commentAdd);

		var $btn=$inp.nextAll('.comment_action').find('.wall_post_send');
		clMediaTools.initTextareaControl($inp, $btn);
        $btn.click(function(){
            $this.commentAdd($inp);
        })
    }

    this.showFrmTop = function(){
        clMediaTools.showFrmReplyComment([], 'ls_feed_comment_top', false, false, false, true);
    }

    this.hideFrmTop = function(){
        if(!$this.$fieldCommentTop.is(':hidden')){
            clMediaTools.hideFrmReplyComment($this.$fieldCommentTop[0].id, false, false)
        }
    }

    this.showFrmReply = function(el){
        var $el=$(el);
        clMediaTools.showFrmReplyComment($el, 'ls_comment_replies_'+$(el).data('cid'), false, true, false, true);
    }

    this.hideFrmReply = function(id, call){
        clMediaTools.hideFrmReplyComment('ls_comment_replies_'+id, false, true, call)
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
        $box=$box||$('#ls_comments > .ls_comment_item');
        number=number||$this.numberCommentsFrmShow;
        var l=$box.length;

        if(l>$this.numberCommentsFrmShow){
            if($this.$fieldCommentBottom.is(':hidden')){
                clMediaTools.showFrmReplyComment([], 'ls_feed_comment_bottom', false, false, true);
            }
        } else {
            clMediaTools.hideFrmReplyComment($this.$fieldCommentBottom[0].id, false, false)
        }
    }

    /* Like comment */
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

        $.post(url_ajax+'?cmd=live_stream_comment_like',
              {id:$this.lsVideoId,
               cid:cid,
               like:like},
            function(res){
                if ($this.noAction()) return;
                var data=checkDataAjax(res);
                if (data){
                    var $bl=rcid?$('#ls_comment_reply_likes_bl_'+rcid):$('#ls_comment_likes_bl_'+cid),
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

    this.queueUpdateLikeComments = {};
    this.setQueueLikeComments = function(id,fn){
        var check = false;
        if(typeof $this.queueUpdateLikeComments[id] == 'undefined'){
            $this.queueUpdateLikeComments[id] = [];
        }
        if($this.queueUpdateLikeComments[id].length){
            check = true;
        }
        $this.queueUpdateLikeComments[id].push(fn);
        //$this.debug('SetQueueLikeComments:'+id, check);
        return check;
    }

    this.runQueueLikeComments = function(id){
        if(typeof $this.queueUpdateLikeComments[id] == 'undefined'){
            return;
        }
        var fn=$this.queueUpdateLikeComments[id].shift();
        if(typeof fn=='function')fn();
        //$this.debug('RunQueueLikeComments:', [id, $this.queueUpdateLikeComments[id]]);
        return;
    }

    this.updateCommentLike = function(dataLike, $bl, id) {
        if($this.setQueueLikeComments(id,function(){
            clMediaTools.updateCommentOneLike(dataLike,$bl,id,true)
        })){
            return;
        }
        //$this.queueUpdateLikeComments[id].shift();
        clMediaTools.updateCommentOneLike(dataLike,$bl,id,true);
    }

    this.updateLikeComments = function(liveId, data){
        if ($this.noAction()) return;
        debugLog('Live stream: UpdateLikeComments', data);

        if (typeof data == 'object' && data.items != undefined) {
            if (typeof $this.itemsInfo[liveId] != 'undefined') {
                $this.itemsInfo[liveId].actionLikeComment = data.actionLikeComment;
            }
            var comment=data.items;
            for(var key in comment) {
                var com=comment[key],
                    pCid=com['parent_id']*1,
                    cid=com['id'];

                var $bl=pCid?$('#ls_comment_reply_likes_bl_'+cid):$('#ls_comment_likes_bl_'+cid),
                    dataLike={count:com['likes'],title:com['likes_users']};
                $this.updateCommentLike(dataLike, $bl, cid);

                var $el=pCid?$('#ls_comment_reply_like_link_'+cid):$('#ls_comment_like_link_'+cid);
                clMediaTools.likeChangeStatus($el, com['my_like']*1);
            }
        }
    }
    /* Like comment */

    /* Like post */
    this.likeHeartOne = function(){
        var $userPhoto=$('#call_user_photo');
        if((!_lsLiveId && !_lsLiveIdEnd) || (_lsLiveIdEnd && !$userPhoto.data('screen')))return;

        var randInt = function (min, max) {
            var rand = min - 0.5 + Math.random() * (max - min + 1);
            return Math.round(rand);
        }
        var scale=randInt(1,5), dur=randInt(3,6), dur1=Math.round((dur*1000)/4),
            lDirect=randInt(1,2), left=randInt(5,20);
        left=left+'% + 55px';
        if (lDirect==2) {
            left=randInt(80,95);
            left=left+'% - 55px'
        }
        $('<span class="heart x1 no_transparent" ' +
                        'style="left:calc(' + left + ');'+
                               'animation-duration: '+dur+'s, '+dur1+'ms;">'+
                               //'transform: scale(0.' + scale + ') rotate(45deg);">'+
                               //'height:' + size + 'px;'+
                               //'bottom:-' + (size*2) +'px">'+
        '</span>').oneAnimationEnd(function(){
            $(this).remove();
        }).appendTo($userPhoto);
    }

    this.$lsLiveLikeHeart=[];
    this.$lsLiveLikeHeartToggle=[];
    this.setLikeLiveHeart = function(like) {
        like=like?false:true;
        $this.$lsLiveLikeHeart=$('#ls_video_like').addClass('to_show');

        $this.$lsLiveLikeHeartToggle=$this.$lsLiveLikeHeart.find('input[type="checkbox"]');
        $this.changeLikeLiveHeart(like);
    }

    this.hideLikeLiveHeart = function() {
        $this.$lsLiveLikeHeart[0] && $this.$lsLiveLikeHeart.removeClass('to_show');
    }

    this.changeLikeLiveHeart = function(like) {
        $this.$lsLiveLikeHeartToggle.prop('checked', !like).attr('checked', !like);
        $this.$lsLiveLikeHeart.data('like', !like);
        $this.$lsLiveLikeHeart.attr('title', like?l('like'):l('unlike'));
    }

    this.likeAddLiveHeart = function() {
        $this.likeAdd(false, $this.$lsLiveLikeHeart.data('like')?0:1);
    }

    this.likeAddAjax=false;
    this.likeAdd = function($link, like) {
        id=$this.blogId;
		$link=$link||$('#ls_feed_like_hand');
        if($this.likeAddAjax)return;
        $this.likeAddAjax=true;
        var $icon=$('.icon',$link).addChildrenLoader();
        like=defaultFunctionParamValue(like, 1);

        if (like) {
            $this.likeHeartOne();
        }
        $this.changeLikeLiveHeart(like?false:true);


		$.post(url_ajax+'?cmd=live_stream_like',{id:$this.lsVideoId, like:like}, function(data){
            if ($this.noAction()) return;
            $icon.removeChildrenLoader();
            data=checkDataAjax(data);
            if(data !== false){
                var $likes=$(data).filter('.who_liked');
                $this.likeChange($likes, true);
            } else {
                alertServerError();
            }
            $this.likeAddAjax=false;
        })
	}

	this.likeDelete = function($link) {
        $this.likeAdd($link, 0);
	}

    this.likeChange = function($cont, noHeart) {

        noHeart=noHeart||false;
        var $blLikes=$('#ls_feed_like_result_'+$this.lsId);

        if(!$blLikes[0]||$blLikes.is('.animate'))return;
        $blLikes.addClass('animate');
        if($cont[0]){
            $jq('#update_server').append($cont.find('script'));
        } else {
            $this.itemsInfo[$this.lsId].listLikeUser='';
        }

        $('#ls_feed_like_action_'+$this.lsId)[$this.itemsInfo[$this.lsId].listLikeUser.indexOf($this.guid) === -1?'removeClass':'addClass']('wall_like_hidden');

        if(!$cont[0]||!$this.itemsInfo[$this.lsId].listLikeUser){
            $blLikes.slideUp($this.dur,function(){
                $blLikes.removeClass('animate');
                $blLikes.find('.who_liked_bl').empty();
            });
            return;
        }

        var countLike=$blLikes.data('count'),
            newCountLike=$cont.filter('.who_liked').data('count');
        $blLikes.data('count', newCountLike);
        if (!noHeart) {
            if (newCountLike > countLike) {
                var d=newCountLike - countLike;
                for (var i = 0; i < d; i++) {
                    $this.likeHeartOne();
                }
            }
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
        var $bl=$('#ls_load_more_comments_bl_'+$this.lsId);
		if(!$bl[0] || $bl.is('.disabled'))return;


        var $blComments=$this.$blComments,
            $lastComment=$blComments.find('.ls_comment_item:last');
        if(!$lastComment[0])return;
        $bl.addClass('disabled');

        addChildrenLoader($el);

        var cmd='get_live_stream_comment',
            lastId=$lastComment.data('cid');

        var fnLoad=function(){
            var dataRes={id:$this.lsVideoId,
                         load_more:1,
                         last_id:lastId,
                         limit:limit}

            $.ajax({url:url_ajax+'?cmd='+cmd,
                    type:'POST',
                    data:dataRes,
                    timeout: globalTimeoutAjax,
                    //cache: false,
                    success: function(res){
                        if ($this.noAction()) return;
                        var data=checkDataAjax(res);
                        if(data){
                            var $data=$('<div>'+data+'</div>'),
                            $comments=$data.find('.ls_comment_item').hide();
                            if($comments[0]){
                                var $numberView=$bl.find('.number_view'),
                                    count=$numberView.text()*1,
                                    countAll=$bl.find('.number_all').text()*1,
                                    $comment,i=0;
                                (function fu(){
                                    $comment=$comments.eq(i).show();
                                    if(!$comment[0])return;
                                    console.log('#'+$comment[0].id, !$('#'+$comment[0].id)[0]);
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

                                        clMediaTools.addCommentToBl($comment, 0, 'appendTo', $this.showLastFieldComment, '', $blComments);
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
                            debugLog('Live stream: Retry load more comments', dataRes);
                            fnLoad()
                        })
                    },
            })
        }
        fnLoad();
    }

    /* Replies load more comments */
    this.commentsRepliesLoadMore = function($el, cid, limit) {
        if($el.is('.disabled')) return;
        $el.addClass('disabled');

        debugLog('commentsRepliesLoadMore',$this.itemCommentsReplyFirst[cid]);

        limit=limit||0;
        addChildrenLoader($el);

        var dataRes={
                live_id: $this.lsId,
                comment_id:cid,
                last_id:$this.itemCommentsReplyFirst[cid],
                load_more:1,
                limit:limit
            };

        var fnLoad=function(){
            $.ajax({url:url_ajax+'?cmd=get_live_stream_comment_replies',
                type:'POST',
                data:dataRes,
                timeout: globalTimeoutAjax,
                //cache: false,
                success: function(res){
                    if ($this.noAction()) return;
                    res=checkDataAjax(res);
                    if(res){
                        var $data=$(res),
                            $num=$el.next('.comm_to_comm_text_number');

                        $this.addCommentsRepliesLoadMore($data,cid,function(){
                            setTimeout(function(){
                                if($num[0] && !$num.is('.to_show')){
                                    $el.find('.comments_replies_load_title').text(l('view_previous_replies'));
                                    $num.addClass('to_show');
                                }
                            },400)
                        })

                    }
                    removeChildrenLoader($el);
                    $el.removeClass('disabled');
                },
                error: function(xhr, textStatus, errorThrown){
                    globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                        debugLog('Live streaming: Retry load more replies comments', dataRes);
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
        if (!$('#'+$comment[0].id)[0] && !$('#ls_comments_replies_item_'+send)[0]) {
            clMediaTools.addCommentToBl($comment, cid, 'prependTo', false, '#ls_comments_replies_list_');
            return true;
        } else {
            return false;
        }
        return false;
    }

    this.addCommentsRepliesLoadMore = function($data, cid, call) {
        if(!$data[0]) return;
        var $comments=$data.find('.comments_replies_item');
        if ($comments[0]) {
            var i=$comments.length-1, $el;
            (function fu(){
                $el=$comments.eq(i);
                if(!$el[0] || i < 0){
                    if(typeof call == 'function')call();
                    return;
                }

                $this.addCommentReplyFirstPlace($el, cid);
                i--; fu();
            })()
            return true;
        } else {
            return false;
        }
    }

    this.updateRepliesLoadMoreStatus = function(cid, count) {
        if (!$this.commentsInfo[cid]) {
            $this.commentsInfo[cid] = {
                replyCount:0,
                replyVisible:0
            }
        }

        var info=$this.commentsInfo[cid];
        info.replyCount=count||info.replyCount;
        $this.commentsRepliesLoadMoreStatusOne(cid, info.replyCount);
    }

    this.commentsRepliesLoadMoreStatusOne = function(cid, count) {
        var $bl=$('#ls_comments_replies_load_' + cid);

        if (!$bl[0] || !$bl.is(':visible')) return;
        count=count||0;
        count *=1;
		var $itemLoadMore=$bl.find('.comm_to_comm_text_number'),
            v=$this.commentsInfo[cid].replyVisible||0,
            q=count-v,
            dur=$this.dur;
        debugLog('Live stream: commentsRepliesLoadMoreStatus',[cid, q>0, count, v]);
		if (q>0) {
            if ($bl.data('all')!=count){
                $itemLoadMore.find('.number_all').text(count);
            }
            $bl.data('all',count);
            if ($bl.data('vis')==v) return;
            $bl.data('vis',v);
            $itemLoadMore.find('.number_view').text(v);
		} else {
			$bl.stop().slideUp(dur);
		}
	}
    /* Replies load more comments */

    this.updateCommentCounter = function($bl, inc){
        if ($bl && $bl[0]) {
            var $loadReplies=$bl;
        } else {
            var $loadReplies=$('#ls_load_more_comments_bl_'+$this.lsId);
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
		if(!$commentAction[0]){
			$commentAction=$inp.next('.comment_action');
		}

		var sticker=$inp.data('sticker');
		if (typeof sticker != 'object' || typeof sticker.code == 'undefined') {
			sticker=false;
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
		};

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
            $comment = $('<div id="ls_comment_'+send+'" data-cid="'+send+'" class="ls_comment_item item">'+
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
                      id:$this.lsVideoId,
                      live_id:$this.lsId,
                      reply_id:rCid,
					  audio_message_id:audioMessageId,
					  image_upload:uploadImageLoaded?1:0,
					  ind:uploadImageLoaded
				};
			if (sticker) {
				data['sticker'] = sticker.data;
			}
            $.post(url_ajax+'?cmd=live_stream_comment_add',data,
            function(res){
                if ($this.noAction()) return;
                data=checkDataAjax(res);
                if (data!==false){
                    var $data=$(trim(data));
                    if (rCid) {
                        $data=$data.find('.comments_replies_item');
                        if(!$data[0]||$('#'+$data[0].id)[0])return;
                        var resCid=$data.data('rcid');
                    } else {
                        $data=$data.filter('.ls_comment_item');
                        if(!$data[0]||$('#'+$data[0].id)[0])return;
                        var resCid=$data.data('cid');
                    }
                    $comment.data('cid', resCid).attr({'id':$data[0].id, 'data-rcid':resCid});
                    clMediaTools.commentUpdate($comment, $data);

                    if (rCid) {
                        $this.updateCommentCounter($('#ls_comments_replies_load_'+rCid));
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
                clMediaTools.addCommentToBl($comment, rCid, false, fnAdd, '#ls_comments_replies_list_');
            } else {
                clMediaTools.addCommentToBl($comment, 0, 'prependTo', fnAdd, false, $this.$blComments);
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

    this.showFrmBottomComment = function(id) {
        var frm='ls_feed_comment_bottom',
            $frm=$('#'+frm);
        if(!$frm[0])return;
        var c=$this.commentsVisible[id]||0;
        if(c>$this.numberCommentsFrmShow){
            if($frm.is(':hidden')){
                clMediaTools.showFrmReplyComment([], frm, false, false, true);
            }
        } else {
            clMediaTools.hideFrmReplyComment(frm, true, false)
        }
    }

    this.showFrmBottomCommentDelay = function(id, d) {
        d=d||200;
        setTimeout(function(){$this.showFrmBottomComment(id)},d);
    }

    this.confirmDeleteComment = function($el,cid,rCid){
        confirmCustom(l('are_you_sure'), function(){$this.commentDelete($el,cid,rCid)}, l('confirm_delete_comment'));
        $el.closest('.more_menu_collapse').collapse('hide');
    }

    this.commentDelete = function($el,cid,rCid) {

        var lastId, listComments, $commItem;

        if (rCid) {
            cidP=cid;
            lastId=$this.itemCommentsReplyFirst[cid]?$this.itemCommentsReplyFirst[cid]:0;
            cid=rCid;
            listComments=$this.commentsReplyCache[cid]?JSON.stringify($this.commentsReplyCache[cid]):'';
            $commItem=$('#ls_comments_replies_item_'+rCid);
        } else {
            cidP=0;
            lastId=$this.commentsLast[$this.lsId]?$this.commentsLast[$this.lsId]:0;
            listComments=JSON.stringify($this.commentsCache);
            $commItem=$('#ls_comment_'+cid);
        }

        if($commItem.is('.deleted'))return;
        $commItem.addClass('deleted').fadeTo(200,.4);

        var data={
            live_id: $this.lsId,
            video_id: $this.lsVideoId,
            cid:cid,
            cid_parent:cidP,
            list_comments:listComments,
            last_id:lastId
        }

        $.post(url_ajax+'?cmd=live_stream_comment_delete',data,
                function(res){
                    if($this.noAction())return;
                    var data=checkDataAjax(res);
                    if (data !== false){
                        var $data=$(data);
                        //return;
                        $jq('#update_server').append($data.filter('script'));
                        if(cidP){
                            $this.commentHide($this.lsId, cidP, rCid);
                            var $comment=$data.find('.comments_replies_item');
                            if($comment[0] && !$('#'+$comment[0].id)[0]){
                                clMediaTools.addCommentToBl($comment, cidP, 'prependTo', false, '', $('#ls_comments_replies_list_'+cidP));
                            }
                        } else {
                            $this.commentHide($this.lsId, cid, rCid);
                            var $comment=$data.filter('.ls_comment_item');
                            if($comment[0] && !$('#'+$comment[0].id)[0]){
                                clMediaTools.addCommentToBl($comment, cid, 'appendTo', false, '', $this.$blComments);
                                $this.showFrmBottomCommentDelay($this.lsId);
                            } else {
                                $this.showFrmBottomComment($this.lsId);
                            }
                        }
                    } else {
                        alertServerError();
                    }
        })

		return false;
	}

    this.commentHide = function(id, cid, rcid, notUpdateData) {
        debugLog('Live stream: CommentHide', [id, cid, rcid, notUpdateData]);
        rcid=rcid||false;
        notUpdateData=notUpdateData||false;

        clMediaTools.commentHide(cid, rcid, false, false, false, false, false, true);

        if (!notUpdateData) {
            var c;
            if (rcid) {
                $this.commentsReplyCacheRemoveByCid(id, cid, rcid)
                c = $this.commentsInfo[cid].replyCount;
                $this.commentsRepliesLoadMoreStatusOne(cid, c);
            } else if($this.itemsInfo[id]){
                $this.commentsCacheRemoveByCid(id, cid);
                c = $this.itemsInfo[id].commentsCount;
                $this.updateCommentsCounter(id, c);
                $this.commentsLoadMoreStatus(id, c);
            }
        }
    }

    this.commentDeleteFromPage = function(id, cid, rcid) {
        if ($this.noAction()) return;
        debugLog('Live stream: CommentDeleteFromPage', [id,cid,rcid]);
        if(rcid === '0'){
            rcid=0;
        }
        $this.commentHide(id, cid, rcid, true);
        if(rcid){
            $this.commentsReplyCacheRemoveByCid(id, cid, rcid)
        }else{
            $this.commentsCacheRemoveByCid(id, cid);
        }
	}

    /* Comment */

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
                                    .find('.icon_close').attr('onclick','clStream.closePpImageBack();')
                                    .end().html();
            $this.$ppShowImage=$jq('#pp_im_image').clone();
        })
    }

    this.isOpenImage=false;
    this.showPopupImage = function($imgOrig, src){
        debugLog('Live stream: showPopupImage', src);
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


    this.queueComments = {};
    this.queueCommentsInc = 0;
    this.lsSetQueueBlComments = function(fn){
        var check = false;
        if(!$.isEmptyObject($this.queueComments)){
            check = true;
        }
        $this.queueComments[$this.queueCommentsInc++]=fn;
        return check;
    }

    this.lsRunQueueComments = function(){
        var i=true;
        for(var id in $this.queueComments) {
            if(i){
                i=false;
                delete $this.queueComments[id]
            } else {
                $this.queueComments[id]();
                break;
            }
        }
        console.log('QueueComments:', $this.queueCommentsInc);
    }

    this.lsStartCommentsShowQueue = function(data){
        if($this.lsSetQueueBlComments(function(){
            $this.lsStartCommentsShow(data)
        })){
            return;
        }
        $this.lsStartCommentsShow(data);
    }

    this.lsViewerShowMore = function(noTop){
        var $items=$this.$liveListViewer.find('.ls_list_viewers_item');
        if (!$items[0]) {
            $this.$listVwMore.removeClass('to_show');
            return;
        }

        var h=$this.$liveListViewerUl.height();
        if (h > ($this.hRowList*2)) {
            $this.$listVwMoreDown.removeClass('disabled');
            $this.$listVwMoreUp.addClass('disabled');
            if (!(noTop||false)) {
                $this.$listVwBl[0].scrollTop=0;
            }
            $this.$listVwMore.addClass('to_show');
        } else {
            $this.$listVwMore.removeClass('to_show');
        }
    }

    this.setRowElViewer = function($items, set){
        set=set||false;
        if ($this.$liveListViewerItemHide&&!set)return;

        $this.$liveListViewerItemHide=0;
        if (!$this.$liveListViewer.is('.to_show, .to_show0'))return;

        $items=$items||$this.$liveListViewer.find('.ls_list_viewers_item');
        if (!$items[0])return;

        $items.each(function(e){
            if (!inViewport(this,{container:$this.$liveListViewer[0],threshold:0})) {
                $this.$liveListViewerItemHide=e;
                return false;
            }
        })
    }

    this.getRowHViewer = function(){
        if (!$this.$liveListViewer.is('.to_show, .to_show0'))return 0;

        var $items=$this.$liveListViewer.find('.ls_list_viewers_item');
        if (!$items[0])return 0;
        if (!$this.$liveListViewerItemHide)$this.setRowElViewer($items);

        var h=$items.eq($this.$liveListViewerItemHide).offset().top-$items.eq(0).offset().top;
        if ($this.$liveListViewer.is('.to_show0')) {
            h=h/2;
        }
        return h;
    };

    this.hRowList=45;
    this.lsListViewerCheck = function(call){
        var $items=$this.$liveListViewer.find('.ls_list_viewers_item'),
            fnCall=function(){if(typeof call=='function')call()}

        if (!$items[0]) {
            $this.lsStartListViewerHide(fnCall);
            return;
        }

        var cl=$this.$listVwBl.height()>$this.hRowList?'to_show0':'to_show',
            cl1=$this.$liveListViewer.is('.to_show0')?'to_show0':'to_show';

        if (cl == cl1) {
            $this.lsViewerShowMore(true);
            fnCall();
            return;
        }

        $this.$liveListViewer.oneTransEnd(function(){
            $this.setRowElViewer($items);
            $this.lsViewerShowMore(true);
            fnCall()
        },'height').toggleClass(cl+' '+cl1);
    }

    this.lsStartListViewerShow = function(call){
        var $items=$this.$liveListViewer.find('.ls_list_viewers_item'),
            fnCall=function(){if(typeof call=='function')call()}

        if (!$items[0] || $this.$liveListViewer.is('.to_show, .to_show0')) {
            fnCall()
            return;
        }

        var cl=$this.$listVwBl.height()>$this.hRowList?'to_show0':'to_show';
        $this.$liveListViewer.oneTransEnd(function(){
            $this.setRowElViewer($items);

            $this.lsViewerShowMore();
            fnCall()
        },'height').toggleClass(cl,0);
    }

    this.lsStartListViewerHide = function(call){
        var fnCall=function(){if(typeof call=='function')call()};
        if ($this.$liveListViewer.is('.to_show, .to_show0')) {
            $this.$liveListViewer.oneTransEnd(function(){

                $this.$liveListViewerUl.find('.ls_list_viewers_item').remove();
                $this.$listVwMore.removeClass('to_show');
                $this.$liveListViewerItemHide=0;

                fnCall();
            },'height').removeClass('to_show to_show0',0);
        } else {
            fnCall()
        }
    }

    this.lsStartCommentsShow = function(data){
        var $data=$('<div class="ls_content_bl_comments to_hide">'+data+'</div>'),
            $tmplBlComment=$('#template_bl_comment'),
            hB=$tmplBlComment.height(),
            hB1=$this.$liveCommentBl.outerHeight(false),
            $blLiked=$data.find('.who_liked'),
            showLiked=function(d){},
            $blC=$data.find('.ls_comments').addClass('to_hide');

        $data.find('.photo_and_field_comment .pic').removeClass('to_hide');
        if($blLiked[0] && !$blLiked.is('.wall_like_hide')){
            $blLiked.addClass('wall_like_hide');
            showLiked=function(d){
                d=d||1;
                $blLiked.delay(d).slideDown(300,function(){
                    $blLiked.removeClass('wall_like_hide');
                })
            }
        }

        $this.$liveCommentBl.css('height',hB1+'px');
        $tmplBlComment.addClass('to_top');
        $data.appendTo($this.$liveCommentBl)

        setTimeout(function(){
            var dH=$data.height(),
                t=Math.round(Math.sqrt(Math.abs(dH)))*35;
            if(hB==dH){
               $blC.removeClass('to_hide');
            } else {
               $blC.oneTransEnd(function(){
                   $blC.removeAttr('style');
               }).css({transition: 'opacity '+(t*2.5)+'ms linear'}).removeClass('to_hide');
            }

            $data.oneTransEnd(function(){
                $tmplBlComment.remove();

                if(hB==dH){
                    showLiked();
                    $this.$liveCommentBl.removeAttr('style');

                    $this.lsStartListViewerShow($this.lsRunQueueComments);
                } else {
                    $this.$liveCommentBl.oneTransEnd(function(){
                        $this.$liveCommentBl.removeAttr('style');

                        $this.lsStartListViewerShow($this.lsRunQueueComments);

                    },'height').css({height:dH+'px', transition: 'height '+t+'ms cubic-bezier(.52,.14,.49,.87)'});
                    showLiked(50);
                }
            }).toggleClass('to_hide', 0);
        },10)
    }


    this.lsStartCommentsHideQueue = function(){
        if($this.lsSetQueueBlComments(function(){
            $this.lsStopCommentsHide()
        })){
            return;
        }
        $this.lsStopCommentsHide();
    }

    this.scrollTop = function(fn){
        if(typeof fn != 'function')fn=function(){};

        /*if(isIos12 || true){
            $('.wrap_live')[0].scrollIntoView({behavior: "smooth"});
            if(typeof fn == 'function')setTimeout(fn,100)
            return;
        }*/

        $('.wrap_live').stop().animate({scrollTop:0},300,'easeInOutCubic',function(){
            fn();
        })
    }

    this.lsStopCommentsHide = function(){
        if($this.$liveCommentBl.find('#template_bl_comment:visible').not(':animated')[0]){
            $this.lsRunQueueComments();
            return;
        }

        if (isMobileSite) {
            $this.scrollTop();
        }


        var $blLiked=[],
            $con=$('.ls_content_bl_comments');
        if ($con[0]) {
            $blLiked=$con.find('.who_liked');
        }
        var fn=function(){

            var $tmplBl=$this.$tmplCommentBlClone.clone().addClass('to_top to_hide');
            $tmplBl.find('.photo_and_field_comment .pic').removeClass('to_hide');

            if ($con[0]) {
            var h=$this.$liveCommentBl.outerHeight(false),
                $blC=$con.find('.ls_comments');

            var fnHidden = function(){
                    if ($this.$liveCommentBlHeight == h) {
                        $tmplBl.appendTo($this.$liveCommentBl).oneTransEnd(function(){
                            $con.remove();
                            $tmplBl.removeClass('to_top');

                            $this.lsRunQueueComments();
                        }).toggleClass('to_hide',0);
                    } else {
                        $this.$liveCommentBl.css('height',h+'px');
                            $tmplBl.appendTo($this.$liveCommentBl).oneTransEnd(function(){
                        })

                        $tmplBl.removeClass('to_hide');

                        setTimeout(function(){
                            var dH=$tmplBl.height(),
                                t=Math.round(Math.sqrt(Math.abs(dH)))*35;

                            $blC.css({transition: 'opacity '+(t*2.5)+'ms linear'}).addClass('to_hide');
                            $this.$liveCommentBl.oneTransEnd(function(){
                                $con.remove();
                                $tmplBl.removeClass('to_top');

                                $this.$liveCommentBl.removeAttr('style');
                                $this.lsRunQueueComments();
                            }).css({height:dH+'px', transition: 'height '+t+'ms  cubic-bezier(.52,.14,.49,.87)'});
                        },10)
                    }
                }
                /*if($blLiked[0] && !$blLiked.is(':hidden')){
                    $blLiked.slideUp(400,function(){
                        fnHidden()
                    })
                } else {
                    fnHidden()
                }*/
                fnHidden()
            } else {
                $tmplBl.appendTo($this.$liveCommentBl).oneTransEnd(function(){
                    $con.remove();
                    $tmplBl.removeClass('to_top');
                    $this.lsRunQueueComments();
                }).toggleClass('to_hide',0);
            }
        }


        var fnHideLike=function(call){
            var fnCall=function(){if(typeof call=='function')call()};
            if($blLiked[0] && !$blLiked.is(':hidden')){
                $blLiked.oneTransEnd(function(){
                    fnCall();
                },'height').addClass('to_hide',0);
            } else {
                fnCall();
            }
        }

        if ($this.$liveListViewer.is('.to_show, .to_show0')) {
            fnHideLike();
            $this.lsStartListViewerHide(fn);
        } else {
            fnHideLike(fn);
        }

    }
    /* Updater */
    /* ----------------------- CACHE DATA ----------------------------------- */
    this.itemsInfo = {};
    this.itemInfoSet = function(id, like, comment, commentsCount, listLikeUser, actionLikeComment) {
        if ($this.noAction()) return;
        if(!id) return;
        if(!$this.itemsInfo[id]) {
            $this.itemsInfo[id] = {
                like:'',
                comment:'',
                commentsCount:0,
                listLikeUser:'',
                actionLikeComment:'0000-00-00 00:00:00'
            }
        }
		var info=$this.itemsInfo[id];
		info.like=(like||info.like);
		info.comment=(comment||info.comment);
		info.commentsCount=(commentsCount||info.commentsCount);
        info.listLikeUser=listLikeUser||info.listLikeUser;
        info.actionLikeComment=actionLikeComment||info.actionLikeComment;

        debugLog('Live stream: ItemInfoSet: '+id, info);

        $this.updateCommentsCounter(id, info.commentsCount);

        $this.commentsLoadMoreStatus(id, info.commentsCount);
	}

    this.itemLikeSet = function(id, like, listLikeUser) {
        if ($this.noAction()) return;
        if (!id) return;
        $this.itemsInfo[id].like=like;
        $this.itemsInfo[id].listLikeUser=listLikeUser;
        debugLog('Live stream: ItemLikeSet: ',[id,like,listLikeUser]);
    }

    this.getInfo = function(id) {
        if (!$this.itemsInfo[id]) {
            return {};
        }
        return $this.itemsInfo[id];
    }

    this.commentsCacheAll = {};
    this.commentsCache = {};
    this.commentsLast = {};
    this.commentsFirst = {};
    this.commentsVisible = {};

    this.commentsReplyCache = {};
    this.itemCommentsReplyFirst = {};
    this.itemCommentsReplyLast = {};
    this.commentsInfo = {};

    this.commentsCacheAddAll = function(id, cid) {
        if ($this.noAction()) return;
        if (!$this.commentsCacheAll[id]) {
            $this.commentsCacheAll[id]={};
        }
        $this.commentsCacheAll[id][cid] = cid;
    }

    this.commentsCacheAdd = function(id, cid) {
        if ($this.noAction()) return;
        if (!id) return;

        $this.commentsCacheAddAll(id, cid);

		if (!$this.commentsCache[id]) {
            $this.commentsCache[id]={};
            $this.commentsVisible[id]=0;
        }

		if ($this.commentsCache[id][cid]) return;

        if (!$this.commentsInfo[cid]) {
            $this.commentsInfo[cid] = {
                replyCount:0,
                replyVisible:0
            }
        }

		$this.commentsCache[id][cid] = cid;
        $this.commentsVisible[id]++;

        if (!$this.commentsReplyCache[cid]) {
            $this.commentsReplyCache[cid]={};
        }

        if(!$this.commentsLast[id]){
            $this.commentsLast[id]=cid;
        }
        $this.commentsLast[id]=Math.min($this.commentsLast[id], cid);

        if(!$this.commentsFirst[id]){
            $this.commentsFirst[id]=cid;
        }
		$this.commentsFirst[id]=Math.max($this.commentsFirst[id], cid);

        debugLog('Live stream: commentsCacheAdd', $this.commentsCacheAll);
        //debugLog('commentsCacheAll', $this.commentsCacheAll);
        //debugLog('commentsCache', $this.commentsCache);
        //debugLog('commentsLast', $this.commentsLast);
        //debugLog('commentsFirst', $this.commentsFirst);
        //debugLog('commentsVisible', $this.commentsVisible);
	}

    this.commentsCacheRemoveByCid = function(id, cid) {
        if ($this.noAction()) return;
        if (!$this.commentsCache[id][cid]) return;
		if ($this.commentsCache[id] && $this.commentsCache[id][cid]){
            delete $this.commentsCache[id][cid];
        }
		if ($this.commentsCacheAll[id] && $this.commentsCacheAll[id][cid]){
            delete $this.commentsCacheAll[id][cid];
        }
        if ($this.commentsReplyCache[cid]) {
            for(var rcid in $this.commentsReplyCache[cid]) {
                if ($this.commentsCacheAll[id] && $this.commentsCacheAll[id][rcid]) {
                    delete $this.commentsCacheAll[id][rcid];
                }
            }
            delete $this.commentsReplyCache[cid];
        }
        if ($this.commentsInfo[cid]) {
            delete $this.commentsInfo[cid];
        }
        var c=$this.commentsVisible[id] - 1;
        if(c<0)c=0;
		$this.commentsVisible[id] = c;
        if (typeof $this.itemsInfo[id] != 'undefined'
                && typeof $this.itemsInfo[id].commentsCount != 'undefined') {
            c=$this.itemsInfo[id].commentsCount - 1;
            if(c<0)c=0;
            $this.itemsInfo[id].commentsCount = c;
        }
	}

    this.commentsReplyCacheRemoveByCid = function(id, cid, rcid) {
        if ($this.noAction()) return;
        if (!$this.commentsCacheAll[id][rcid])return;
        if ($this.commentsCacheAll[id] && $this.commentsCacheAll[id][rcid]) {
            delete $this.commentsCacheAll[id][rcid];
        }
        if ($this.commentsReplyCache[cid] && $this.commentsReplyCache[cid][rcid]) {
            delete $this.commentsReplyCache[cid][rcid];
        }
        var c=$this.commentsInfo[cid].replyVisible - 1;
        if(c<0)c=0;
		$this.commentsInfo[cid].replyVisible = c;

        c=$this.commentsInfo[cid].replyCount - 1;
        if(c<0)c=0;
		$this.commentsInfo[cid].replyCount = c;
	}

/* ----------------------- CACHE DATA ----------------------------------- */
    this.commentsReplyCacheAdd = function(id, cid, rcid, count) {
        if ($this.noAction()) return;
        if (!id || !cid) return;
        count *=1;
        $this.commentsCacheAddAll(id, rcid);

		if (!$this.commentsReplyCache[cid]) {
            $this.commentsReplyCache[cid]={};
        }

        if (!$this.commentsInfo[cid]) {
            $this.commentsInfo[cid] = {
                replyCount:0,
                replyVisible:0
            }
        }

        //debugLog('commentsInfo', [id, cid, rcid, count]);

        $this.commentsInfo[cid].replyVisible++;
        $this.updateRepliesLoadMoreStatus(cid, count);

		if ($this.commentsReplyCache[cid][rcid]) return;
        $this.commentsReplyCache[cid][rcid] = rcid;


        if(!$this.itemCommentsReplyFirst[cid]){
            $this.itemCommentsReplyFirst[cid]=rcid;
        }
        $this.itemCommentsReplyFirst[cid]=Math.min($this.itemCommentsReplyFirst[cid], rcid);

        $this.setReplyCommentInfoLoad(cid, rcid);
        debugLog('Live stream: commentsReplyCacheAdd', $this.commentsReplyCache);
        //debugLog('commentsCacheAll', $this.commentsCacheAll);
        //debugLog('commentsReplyCache', $this.commentsReplyCache);
        //debugLog('itemCommentsReplyFirst', $this.itemCommentsReplyFirst);
        //debugLog('itemCommentsReplyLast', $this.itemCommentsReplyLast);
	}

    this.setReplyCommentInfoLoad = function(cid, rcid) {
        if ($this.noAction()) return;
        debugLog('Live stream: setReplyLastId', [cid, rcid]);

        if(!$this.itemCommentsReplyLast[cid]){
            $this.itemCommentsReplyLast[cid]=rcid;
        }
		$this.itemCommentsReplyLast[cid]=Math.max($this.itemCommentsReplyLast[cid], rcid);
    }


    this.queueUpdateCounters = {};
    this.queueCountersInc = 0;
    this.setQueueUpdateCounters = function(fn){
        var check = false;
        if(!$.isEmptyObject($this.queueUpdateCounters)){
            check = true;
        }
        $this.queueUpdateCounters[$this.queueCountersInc++]=fn;
        return check;
    }

    this.runQueueUpdateCounters = function(){
        var i=true;
        for(var id in $this.queueUpdateCounters) {
            if(i){
                i=false;
                delete $this.queueUpdateCounters[id]
            } else {
                $this.queueUpdateCounters[id]();
                break;
            }
        }
        debugLog('Live stream: QueueUpdateCounters ', $this.queueUpdateCounters);
    }

    this.updateCommentsCounter = function(id,count,call) {
        if($this.setQueueUpdateCounters(function(){
            $this.updateCommentsCounter(id,count,call)
        })){
            return;
        }
        count *=1;

        var $counterBl=$('#ls_comments_count_' + id),
            fnCall=function(){
                if(typeof call=='function'){
                    call();
                } else {
                    $this.runQueueUpdateCounters();
                }
            };

        if(!$counterBl[0] || $counterBl.data('count')==count) {
            fnCall();
            return;
        }

        $counterBl.data('count',count).attr('data-count',count);
        var $counter=$counterBl.find('.comments_count'),
            cl='to_hide',lVar=count==1?'wall_comments_one_count':'wall_comments_count',
            countTitle=l(lVar).replace('{comments_count}',count);

        if(count){
            $counter.text(countTitle);
            if($counterBl.is('.'+cl)){
                $counterBl.oneTransEnd(fnCall).removeClass(cl);
            } else {
                fnCall()
            }
        } else if(!$counterBl.is('.'+cl)) {
            $counterBl.oneTransEnd(function(){
                if(!$counterBl.data('count'))$counter.text(countTitle);
                fnCall()
            }).addClass(cl);
        } else {
            fnCall()
        }
    }

    this.commentsLoadMoreStatus = function(id, count) {
        $this.commentsLoadMoreStatusOne(id, count);
    }

    this.commentsLoadMoreStatusOne = function(id, count) {
        var $itemLoadMore = $('#ls_load_more_comments_bl_' + id);
        debugLog('Live stream: commentsLoadMoreStatus',[id, count, $itemLoadMore.is(':visible')]);
        if (!$itemLoadMore[0] || !$itemLoadMore.is(':visible')) return;

        count=count||0;
        count *=1;
		var v=$this.commentsVisible[id]||0,
            q=count-v,
            dur=$this.dur;

        debugLog('Live stream: commentsLoadMoreStatus_START',[id, q>0, count, v]);

		if (q>0) {
            if ($itemLoadMore.data('all')!=count){
                $itemLoadMore.find('.number_all').text(count);
            }
            $itemLoadMore.data('all',count);
            if ($itemLoadMore.data('vis')==v) return;
            $itemLoadMore.data('vis',v);
            $itemLoadMore.find('.number_view').text(v);

		} else {
			$itemLoadMore.stop().slideUp(dur);
		}
	}

    this.updateLive = function($data, notScriptItemInfo) {

        var selScript='script';
        if(notScriptItemInfo)selScript='script:not(.update_item_info_set)';
        $jq('#update_server').append($data.children(selScript));

    }

    this.updater = function(liveId, $data){
        if ($this.noAction()) return;

        var $scrInfo=$data.find('script.update_item_info_set');

        $this.updateLive($data, true);

        var $viewersList=$data.find('.ls_list_viewers');

        if ($viewersList[0]) {
            $this.$liveListViewer.addClass('disabled');
            var isVisList=$this.$liveListViewer.is('.to_show, .to_show0'),
                $items=$viewersList.find('.ls_list_viewers_item'),
                $items0=$this.$liveListViewerUl.find('.ls_list_viewers_item'),
                $listTitle=$viewersList.find('#ls_list_viewers_title'),
                listTitle=$listTitle.html();

            var fnEnd=function(){
                    $this.$liveListViewer.removeClass('disabled');
                    if (isVisList) {
                        $this.lsListViewerCheck();
                    } else {
                        $this.lsStartListViewerShow();
                    }
                }

            var fn1=function($items){
                    if(!$items[0]){
                        fnEnd();
                        return;
                    }
                    var i=0, $item, prevWp=true;
                    (function fn(){
                        $item=$items.eq(i);
                        if (!$item[0]) {
                            fnEnd();
                            //END
                            return;
                        }
                        i++;
                        if (!$('#'+$item[0].id)[0]) {
                            if (isVisList && isVisiblePage && prevWp) {
                                $item.addClass('to_hide_bl').oneTransEnd(function(){
                                    if(i>1){
                                        prevWp=inViewport($item[0],{container:$this.$liveListViewer[0],threshold:0})
                                    }
                                    fn()
                                },'width').appendTo($this.$liveListViewerUl)
                                .removeClass('to_hide_bl',0);
                                //fn()
                            } else {
                                $item.appendTo($this.$liveListViewerUl);
                                fn();
                            }
                        } else {
                            fn()
                        }
                    })()
            }
            /* -------------------------------------------------------------- */
            if ($items[0]) {
                var count0=$items0.length,
                    count=$items.length;
                if (count0!=count) {
                    $this.$liveListViewerTitle.data('count', count);
                    $this.$liveListViewerTitle.text(l('watching_now').replace(/{count}/, count));
                }
                /*if ($this.$liveListViewerTitle.html()!=listTitle) {
                    $this.$liveListViewerTitle.data('count', $listTitle.data('count'));
                    $this.$liveListViewerTitle.html(listTitle);
                }*/

                var i=$items0.length-1,$item;
                (function fu(){//С конца
                    $item=$items0.eq(i);
                    if(!$item[0] || i < 0){
                        $items=$viewersList.find('.ls_list_viewers_item');
                        if ($items[0]) {
                            fn1($items);
                        } else {
                            fnEnd();
                            //END
                        }
                        return;
                    }
                    i--;

                    var sel='#'+$item[0].id;
                    if ($items.filter(sel)[0]) {
                        $viewersList.find(sel).remove();
                        fu();
                    } else {
                        if (isVisList && isVisiblePage && inViewport($item[0],{container:$this.$liveListViewer[0],threshold:0})) {
                            $item.oneTransEnd(function(){
                                $(this).remove();
                                fu();
                            },'width').addClass('to_hide_bl',0);
                            //fu();
                        } else {
                            $item.remove();
                            fu();
                        }
                    }
                })()

                /*$items0.each(function(n){
                    $item=$(this);
                    var sel='#'+$item[0].id,$it=$items.filter(sel);
                    if ($it[0]) {
                        $viewersList.find(sel).remove();
                        $it.remove();
                    } else {
                        $item.oneTransEnd(function(){
                            $item.remove();
                        }).addClass('to_hide_bl');
                    }
                    if (l==(n+1)) {
                        $items=$viewersList.find('.ls_list_viewers_item');
                        $items.each(function(n){
                            $(this).addClass('to_hide_bl')
                            .appendTo($this.$liveListViewerUl)
                            .removeClass('to_hide_bl',0);
                        })
                    }
                })*/
            } else {

                $this.lsStartListViewerHide();
                //Delete all
            }
            //console.log('UPDATER', $viewersList.html());
        } else {
            $this.$liveListViewer.removeClass('disabled');
        }


        var $liked=$data.find('.who_liked');
        if($liked[0]){
            $this.likeChange($liked);
        }

        var fnAddComment = function($comments, reply, fnAppend, $commentsPrevLoad, notReverse){
            if (!$comments[0])return;
            reply=reply||false;
            fnAppend=fnAppend||false;
            $commentsPrevLoad=$commentsPrevLoad||[];
            notReverse=notReverse||false;

            var ord = (fnAppend == 'prependTo' || reply) && !notReverse;
            debugLog('UPDATE COMMENTS ONE',reply,$comments);
            var $comment,i=ord ? 0 : $comments.length-1;
            (function fu(){
                $comment=$comments.eq(i);
				if(!$comment[0] || i < 0){
					return;
				}
                if(ord){
                    i++
                } else i--;
                var send=$comment.data('send'),
                    cid=$comment.data('cid'),
                    sel=reply ? '#ls_comments_replies_item_' : '#ls_comment_',
                    $bl=reply ? $('#ls_comments_replies_list_'+cid) : $this.$blComments;

                //console.log(33333, '#'+$comment[0].id, sel+send);
                var isParse=true;
                if (fnAppend == 'appendTo' && $commentsPrevLoad[0]) {//ATTACH
                    if ($commentsPrevLoad.filter('#'+$comment[0].id)[0]) {
                        console.log('No Parse '+$comment[0].id);
                        isParse=false;
                    }
                }
                if (!$('#'+$comment[0].id)[0] && !$(sel+send)[0] && isParse){
                    var fn = 'appendTo';
                      //fn = reply ? 'appendTo' : 'prependTo';
                    if(fnAppend)fn=fnAppend;
                    clMediaTools.addCommentToBl($comment, 0, fn, false, '', $bl);
				}
                fu();
			})()
        }

        var $commentsParse=[];

        /* Replies */
        var $comments = $data.find('.update_comments_list_bl .comment_to_comment_container.comment_attach_reply_one');

        if ($comments[0]) {
            debugLog('Live stream: UPDATE COMMENTS REPLIES', $comments, '#2ef293');
            fnAddComment($comments.clone(),true);
            $data.find('.update_comments_list_bl .ls_comment_item.comment_attach_reply').remove();
        }

        $comments=$data.find('.update_comments_list_bl .comment_to_comment_container.comment_attach_reply_one_add');

        if ($comments[0]) {
            debugLog('Live stream: UPDATE COMMENTS REPLIES ADD', $comments, '#2ef293');
            fnAddComment($comments.clone(),true,'prependTo',false,true);
            $data.find('.update_comments_list_bl .ls_comment_item.comment_attach_reply_add').remove();
        }
        /* Replies */

        /* Comments */
        $comments=$data.find('.update_comments_list_bl .ls_comment_item')
                       .not('.comment_attach, .comment_attach_reply, .comment_attach_reply_add');

        if ($comments[0]) {
            debugLog('Live stream: UPDATE COMMENTS',$comments, '#2ef293');
            $commentsParse=$comments.clone();
            fnAddComment($commentsParse, false, 'prependTo');
            $comments.remove();
        }

        $comments = $data.find('.update_comments_list_bl .ls_comment_item.comment_attach');
        if ($comments[0]){
            debugLog('Live stream: UPDATE COMMENTS ATTACH', $comments, '#2ef293');
            fnAddComment($comments.clone(), false, 'appendTo', $commentsParse);
            $comments.remove();
        }
        /* Comments */

        if ($scrInfo[0]) {
            setTimeout(function(){
                $jq('#update_server').append($scrInfo);
            },500)
        }
    }
    /* Updater */

    this.nextLive = function($el){
        $this.searchLive($el);
    }

    this.prevLive = function($el){
        $this.searchLive($el,1);
    }

    this.searchLive = function($el, prev){
        prev=prev||0;
        var liveId=_lsLiveIdEnd*1, $bl=$('#ls_icons_search_live_bl');
        if (!liveId || $bl.is('.disabled')) {
            $bl.removeClass('to_show');
            return false;
        }
        $bl.addClass('disabled');
        $el.addChildrenLoader();
        /* Test */
        //liveId = 9;
        /* Test */
        $.post(url_ajax+'?cmd=live_search',
              {cur_live_id:liveId,
               prev:prev},
            function(res){
                var data=checkDataAjax(res);
                if (data !== false){
                    if (data) {
                        redirectUrl(data);
                    } else {
                        $el.removeChildrenLoader();
                        $bl.removeClass('to_show');
                        alertCustom(l('no_more_live_broadcasts_currently'), l('alert_html_alert'));
                    }
                    /* Test */
                    //$bl.removeClass('disabled');
                    //$el.removeChildrenLoader();
                    /* Test */
                } else {
                    $bl.removeClass('disabled');
                    $el.removeChildrenLoader();
                    alertServerError();
                }

        })

    }

    $(function(){
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
            if($targ.is('.pp_gallery_overflow') || $targ.is('.navbar-default') || $targ.is('.navbar-header')){
                $this.closePpImageBack();
            }
        })

    })



    return this;

}