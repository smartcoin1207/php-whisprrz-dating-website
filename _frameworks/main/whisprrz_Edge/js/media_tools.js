var CMediaTools = function() {

    var $this=this;
    this.dur=400;

    this.getElOnScroll = function(){
        if (isAppAndroid) {
            return $doc;
        }
        return $jq('body, html');
    }

    this.getScrollTopBody = function(){
        return isAppAndroid ? 0 : $jq('body').scrollTop();
    }

    this.getD = function(){
        return ajax_login_status ? $jq('.navbar').height() : 0;
    }

    this.getT = function(d,d1){
        var t=Math.round(Math.sqrt(Math.abs(d-d1))*25);
        if(t<300){t=300} else if(t>800)t=800;
        return t;
    }

    this.scrollToEl = function(sel, fn, d){
        if(isIos12){
            if(!sel)sel='#page_top_area';
            $(sel)[0].scrollIntoView({behavior: "smooth"});
            if(typeof fn == 'function')setTimeout(fn,100)
            return;
        }

        d=d||0;
        sel=sel||'';
        if(typeof fn != 'function')fn=function(){};
        var top=0;
        if(sel)top=$(sel).offset().top;
        var mTop=$this.getScrollTopBody();
        if(top)top=top+mTop;
        top-=$this.getD();
        var t=$this.getT(mTop,top),call=true;
        if(d)top -=d;
        if(top<0)top=0;

        //$(sel)[0].scrollIntoView({behavior: "smooth"});
        //$.scrollTo(sel, t, {axis:'y', queue:false, easing:'easeInOutCubic', onAfter:fn});

        $jq('body, html').stop().animate({scrollTop:top},t,'easeInOutCubic',function(){
            if(!call)return;
            call=false;
            fn();
        })
    }

    this.scrollTop = function(fn){
        $this.scrollToEl('', fn);
        return;

        /*var mTop=$this.getScrollTopBody(),
        t=$this.getT(mTop,0),call=true;
        $this.getElOnScroll().scrollTo(0, t, {axis:'y', interrupt:true, easing:'easeOutExpo', onAfter:function(){
            if(!call)return;
            call=false;
            if(typeof fn == 'function')fn();
        }})*/
    }

    this.checkViewport = function($el){
        var top=$el.offset().top;
        top-=$this.getD();
        return top<0;
    }

    this.scrollTopCheckViewport = function($el,fn){//Not used - previously used, but commented out now wall_edge.js
        if($this.checkViewport($el)){
            $this.scrollTop(fn)
        }else{
            fn=fn||function(){};
            fn()
        }
    }

    this.scrollTopElCheckViewport = function($el,fn,d){//Not used - previously used, but commented out now wall_edge.js
        if($this.checkViewport($el)||true){
            $this.scrollToEl($el,fn,d)
        }else{
            fn=fn||function(){};
            fn()
        }
    }

    this.scrollToGallery = function($el,call){
        if(clProfilePhoto.inViewport($el[0])){
            typeof call=='function' && call();
        } else {
            clProfilePhoto.scrollToNative($el,call);
        }
    }

    this.scrollToBlogs = function($el,call){
        if(clBlogs.inViewport($el[0])){
            typeof call=='function' && call();
        } else {
            clBlogs.scrollToNative($el,call);
        }
    }

    this.scrollToWall = function($el,call){
        if(clWall.inViewport($el[0])){
            typeof call=='function' && call();
        } else {
            clWall.scrollToNative($el,call);
        }
    }

    this.showFrmReplyWall = function(el, replies){
        replies=defaultFunctionParamValue(replies, true);
        var $el=$(el), sel='wall_feed_comment_top_';
        if(replies){
            sel='wall_feed_comment_replies_';
            if (clWall.isElOnePost($el)) {
                sel='pp_'+sel;
            }
        }
        $this.showFrmReplyComment($el, sel+$(el).data('cid'), true, replies)
    }

    this.showFrmReplyComment = function($el, id, isWall, replies, noAction, isBlogs){
        replies=defaultFunctionParamValue(replies, true);
        noAction=defaultFunctionParamValue(noAction, false);
        isWall=isWall||false;
        isBlogs=isBlogs||false;
        var $frmReply=$('#'+id);

        if (noAction) {
            fnScroll=function(){}
        } else {
            var fnScroll=$this.scrollToGallery;
            if(isWall&&!clWall.isElOnePost($el)){
                fnScroll=$this.scrollToWall;
            }else if(isBlogs){
                fnScroll=$this.scrollToBlogs;
            }
        }

        if(!$frmReply[0])return;
        if(!isWall&&$frmReply.closest('.list.disabled')[0])return;

        var isHidden=$frmReply.is(':hidden'),d=0,dl=isIE ? 20 : 5,
            $input=$('textarea',$frmReply), editable=false;
		if (!$input[0]) {
			$input=$("div[contenteditable='true']",$frmReply);
			editable=true;
		}

        if(!noAction){
            var nameLink, name1='', name='', nameMy, uid=0, groupId=0;
            if (replies) {
                nameMy=$frmReply.find('.photo > a').attr('title');
                nameLink=$el.closest('.comment_item_wrapper').find('.photo > a');
                name1=nameLink.attr('title');
                name=nameLink.attr('title') + (editable ? '&nbsp;': ' ');
                if(nameMy==name1){
                    name='';
                    name1='';
                }
                if(name){
                    uid=nameLink.data('uid');
                    groupId=nameLink.data('userGroupOwner');
                }
            }
            $input.attr({'data-uid':uid,'data-name':name1,'data-group-id':groupId})
                  .data({uid:uid,name:name1,groupId:groupId});
		    if(editable){
				$input.html(name);
			} else {
				$input.val(name);
			}
        }

        var isMob=$this.isMobile();

        var fnEnd=function(){
			var $editorImage=$frmReply.find('.comment_upload_img_editor');
			if($editorImage[0]){
				initCommentUploadImageEditor($editorImage);
			}
            if(noAction)return;
            fnScroll($frmReply,function(){
				$input.trigger('autosize').focusEl();
				if (editable) {
					divSetCaretEnd($input[0]);
				} else {
					setCaretToPos(false, $input[0].value.length, $input[0]);
				}
            });
            return;
            //$frmReply.get(0).scrollIntoView();
            //$input.trigger('autosize').focusEl();

            /*$input.trigger('autosize').focusin(function(){
                !isMob && fnScroll($frmReply)
            }).focus();*/

        }
        if (isHidden) {
			$input.trigger('autosize');
            $frmReply.animate({opacity:'toggle', height:'toggle', margin:'toggle'},
                              {duration:$this.dur*.6,specialEasing:{opacity:'linear'},
                               step:function(h,fx){
                                    if(!isMob && fx.prop=='height' && (h-d) > dl){
                                        //fnScroll($frmReply,h)
                                        d=h;
                                    }
                               },
                               complete:function(){
                                    fnEnd()
                               }})
        } else {
            fnEnd()
        }
    }

    this.hideFrmReplyWall = function(id, replies, prf, call){
        prf=prf||'';
        replies=defaultFunctionParamValue(replies, true);
        var sel=prf+'wall_feed_comment_top_';
        if(replies)sel=prf+'wall_feed_comment_replies_';
        $this.hideFrmReplyComment(sel+id, true, replies, call)
    }

    this.hideFrmReplyComment = function(id, isWall, replies, call){
        isWall=isWall||false;
        var $frmReply=$('#'+id);
        if(!$frmReply[0])return;
        if(!isWall&&$frmReply.closest('.list.disabled')[0])return;
        var d=replies ? $this.dur : $this.dur;
        if(!$frmReply.is(':hidden')) {//.delay(dl)
            $frmReply.stop().animate({opacity:'toggle', height:'toggle', margin:'toggle'},
                              {duration:d,specialEasing:{opacity:'linear'},
                               step:function(h,fx){
                               },
                               complete:function(){
                                   if(typeof call=='function')call()
                               }})
        }
    }

	this.initTextareaControl = function($inp, $btn) {
		if(isMobileSite || !$btn[0])return;
		var fn=function(){
			$this.checkTextareaBtn($inp, $btn)
		}
		$btn.data('fn_change', fn);
		$inp.data('fn_change', fn)
			.on('input propertychange', fn)
			.on('focus blur', fn);
	}

	this.checkTextareaBtn = function($inp, $btn) {

		var comment=smileText($inp[0].innerHTML);
		if (comment == $inp.data('placeholder')) {
			comment = '';
		}
		var plName=$inp.data('name'), commentCheck=comment;
        if (plName) {
            commentCheck=$this.checkUserNameComment(comment, plName);
        }

		var $imgUpl=$btn.prev('.comment_upload_img:not(.disabled)');
		if((!comment||!commentCheck)&&!$imgUpl[0]){
			$btn.oneTransEnd(function(){
				$inp.trigger('autosize');
			}).removeClass('to_show');
		} else {
			//!$btn.width()&&
			$btn.oneTransEnd(function(){
				$inp.trigger('autosize');
			}).addClass('to_show');
		}
	}

    this.showLi = function($el, call, d){
        d=d||0;
        d = d ? ' '+d+'s' : '';
        var $allLi=$el.prevAll();
        $el.show();
        var w=$el.outerWidth(true);
        $el.hide().removeClass('to_hide');
        var $all=$el.add($allLi).css({transform:'translateX('+w+'px)', display:'block'});
        $el.oneTransEnd(function(){
            $all.removeAttr('style');
            typeof call=='function' && call();
        })
        setTimeout(function(){
            $all.css({transform:'translateX(0px)', transition:'transform .35s'+d});
        },10)
    }

    this.showLiNext = function($el, d, call){
        d=d||0;
        d = d ? ' '+d+'ms' : '';
        var $allLi=$el.nextAll('li'),isMobile=false;
        if ($allLi.filter('.hide_mobile').is(':hidden')) {//NOT USED comment delete show
            $allLi=$allLi.not('.hide_mobile');
            if (!$allLi[0]) {
                isMobile=true;
            }
        }
        $el.show();
        var w=$el.outerWidth(true);
        $el.hide();
        if (isMobile) {
            $el.oneTransEnd(function(){
                $el.removeClass('to_hide');
                $el.removeAttr('style');
                typeof call=='function' && call();
            }).css({display:'block',opacity:0});
            setTimeout(function(){
                $el.css({opacity:1,transition:'opacity .35s'+d+' linear'})
            },10)
        } else {
            var $all=$el.add($allLi), $ul=$el.closest('ul').css('min-width', '10000px');
            $el.css({display:'block'});
            $allLi.css({transform:'translateX(-'+w+'px)', display:'block'});
            $allLi.last().width(w);
            $allLi.last().oneTransEnd(function(){
                $el.removeClass('to_hide');
                $all.removeAttr('style');
                $ul.removeAttr('style');
                typeof call=='function' && call();
            })
            setTimeout(function(){
                $allLi.css({transform:'translateX(0px)', transition:'transform .35s'+d});
            },10)
        }
    }

    this.hideLi = function($el, call, d){
        d=d||0;
        d = d ? ' '+d+'s' : '';
        var $allLi=$el.prevAll();
        var w=$el.outerWidth(true);
        $el.oneTransEnd(function(){
            $el.hide();
            $el.addClass('to_hide');
            $all.removeAttr('style');
            typeof call=='function' && call();
        })
        var $all=$el.add($allLi).css({transform:'translateX(0px)'});
        setTimeout(function(){
            $all.css({transform:'translateX('+w+'px)', transition:'transform .35s'+d});
        },10)
    }


    this.hideLiNext = function($el,d,call){
        d=d||0;
        d = d ? ' '+d+'ms' : '';
        var $allLi=$el.nextAll('li'),isMobile=false;

        if ($allLi.filter('.hide_mobile').is(':hidden')) {//NOT USED comment delete show
            $allLi=$allLi.not('.hide_mobile');
            if (!$allLi[0]) {
                isMobile=true;
            }
        }
        if (isMobile) {
            $el.oneTransEnd(function(){
                $el.hide();
                $el.addClass('to_hide');
                $el.removeAttr('style');
                typeof call=='function' && call();
            }).css({opacity:0,transition:'opacity .35s'+d+' linear'});
        } else {
            var w=$el.outerWidth(true);
            $allLi.last().width(w);
            $allLi.last().oneTransEnd(function(){
                $el.hide();
                $el.addClass('to_hide');
                $all.removeAttr('style');
                $ul.removeAttr('style');
                typeof call=='function' && call();
            })
            var $all=$el.add($allLi).css({transform:'translateX(0px)'}),
                $ul=$el.closest('ul').css('min-width', '10000px');
            setTimeout(function(){
                $allLi.css({transform:'translateX(-'+w+'px)', transition:'transform .35s'+d});
            },10)
        }
    }

    this.updateCommentOneLike = function(data, $bl, id, noUpdatePp) {
        id=id||0;
        //{count:0,title:0}
        var count=data['count']?data['count']:0,
            title=data['title']?data['title']:'',
            c=count*1,
            $blC=$bl.find('.comment_likes_count'),
            cOld=$bl.data('count_old')||$blC.text()*1;

        $bl.data('count_old',c);
        debugLog('updateCommentOneLike: '+id, 'CountOld: '+cOld+' Count: '+c);

        var fn=function(){};
        if (id) {
            fn=function(){
                if (typeof clStream == 'object' && currentPage == 'live_streaming.php') {
                    clStream.runQueueLikeComments(id)
                } else {
                    clWall.runQueueLikeComments(id)
                }
            }
        }
        if (c == cOld){
            fn();
            return;
        }

        var updateL=function(_$bl,notFn){
            notFn=notFn||false;
            if(!_$bl[0])return;
            var _$blC=_$bl.find('.comment_likes_count')
            if(c) {
                _$blC.text(c);
                _$blC.closest('.comment_item').attr('title', title);
                if(_$bl.is('.to_hide')){
                    clMediaTools.showLiNext(_$bl,100,function(){if(!notFn)fn()});
                    return;
                }
            } else if(!_$bl.is('.to_hide')) {
                clMediaTools.hideLiNext(_$bl,100,function(){
                    _$blC.text(0);
                    _$blC.closest('.comment_item').attr('title', '');
                    if(!notFn)fn()
                });
                return;
            }
            if(!notFn)fn()
        }
        updateL($bl);

        noUpdatePp=noUpdatePp||false;
        if(!noUpdatePp){
            updateL($this.getWallPpEl($bl),true)//Wall popup
        }
    }

    this.updateCommentOneLike_1 = function(count, $bl, id) {
        id=id||0;
        var c=count*1, $blC=$bl.find('.comment_likes_count'),
            cOld=$blC.text()*1, fn=function(){};
        if (id) {
            fn=function(){clWall.runQueueLikeComments(id)};
        }
        if (c == cOld){
            fn();
            return;
        }
        debugLog('updateCommentOneLike: '+id, c);
        if (c) {
            $blC.text(c);
            if($bl.is('.to_hide')){
                clMediaTools.showLiNext($bl,100,fn);
                return;
            }
        } else if(!$bl.is('.to_hide')) {
            clMediaTools.hideLiNext($bl,100,function(){$blC.text(0); fn()});
            return;
        }
        fn();
    }


    this.prepareComment = function() {
        return $this.$placeholderComment.clone();
    }

    this.isMobile = function() {
        return false; //isMobile()
    }

    this.getShowCommentClass = function() {
        return $this.isMobile() ? 'to_hide_wall_mobile' : 'to_hide_wall';
    }

    this.addCommentToBl = function($comment, id, fn, call, sel, $bl) {
        fn=fn||'appendTo';
        sel=sel||'#wall_item_comments_';
        //console.log(7777, fn);
        var $blComments=$bl||$(sel+id), cl=$this.getShowCommentClass();
        if (!$blComments[0]) return;
        $this.showComment($comment.addClass(cl)[fn]($blComments),call);
    }

    this.addCommentToBlAnimate = function($comment, id, fn, call, sel, $bl, callStep) {
        fn=fn||'appendTo';
        sel=sel||'#wall_item_comments_';
        var $blComments=$bl||$(sel+id);
        if (!$blComments[0]) return;
        $this.showCommentAnimate($comment.addClass('to_hide_animated')[fn]($blComments), call, callStep);
    }

    this.showCommentAnimate = function($el,call,callStep) {
        var h=$el.find('.comment_item_wrapper').outerHeight(false);
        if(typeof callStep!='function')callStep=function(){};
        $el.stop().animate(
            {opacity: 1, height: h},
            {duration: 400,
            step: function() {
                callStep();
            },
            complete: function(){
                $el.removeClass('to_hide_animated');
                $el.removeAttr('style');
                if(typeof call=='function')call();
            }
        })
    }

    this.showCommentDelay = 50;
    this.resetShowCommentDelay = function() {
        $this.showCommentDelay = 150;
    }

    this.showComment = function($el,call) {
        if ($this.isMobile()) {
            $el.addClass('overh').oneTransEnd(function(){
                $el.removeClass('to_hide_wall_mobile to_show_wall_mobile overh');
                if(typeof call=='function')call();
            }).delay(10).toggleClass('to_show_wall_mobile',0);//addClass
        }else{
            setTimeout(function(){
                var h=$el.find('.comment_item_wrapper').outerHeight(false),
                    css={height:h, opacity:1};
                    //console.log(55555555,h);
                $el.addClass('overh').oneTransEnd(function(){
                    $el.removeClass('to_hide_wall overh');
                    $el.removeAttr('style');
                    if(typeof call=='function')call();
                },'height').css(css);
            },$this.showCommentDelay)
        }
    }


    /* Add comments with update update*/
    this.addCommentToBlUpdate = function($comment, id, fn, call, sel, $bl) {
        fn=fn||'appendTo';
        sel=sel||'#wall_item_comments_';
        var $blComments=$bl||$(sel+id);
        if (!$blComments[0]) return;
        $this.showCommentUpdate($comment.addClass('to_hide_animated')[fn]($blComments),call);
    }

    this.showCommentUpdate = function($el,call) {
        var h=$el.find('.comment_item_wrapper').outerHeight(false);
        $el.stop().animate(
            {opacity: 1, height: h},
            {duration: 250,
            complete: function(){
                $el.removeClass('to_hide_animated');
                if(typeof call=='function')call();
            }
        })
    }
    /* Add comments with update update*/


    this.commentUpdate = function($comment, $resComment, isWall) {
        isWall=isWall||false;
        var sel='.comment_item_wrapper';
        $comment.addClass('overh');
        var $wrap = $comment.find(sel),
            h = $wrap.outerHeight(false);
        if ($comment.closest('#pp_gallery_comments_bl')[0]) {
            //h += 3;
        }

        $comment.css('height',h);

        var $wrap1 = $resComment.find(sel).addClass('wrap')
        .appendTo($comment).imagesLoaded(function(){
            var h1 = $wrap1.outerHeight(true);


            $wrap.addClass('to_hide');

            $wrap1.oneTransEnd(function(){
                $wrap.remove();
                $wrap1.removeClass('wrap to_show');
                if (h == h1) {
                    $comment.removeAttr('style');
                    $comment.css({transition:''});
                    $comment.removeClass('overh');
                }
            }).delay(10).toggleClass('to_show',0);
            var t=Math.round(Math.sqrt(Math.abs(h1-h)))*60;
            if(t<400){t=400} else if (t>800) t=800;
            console.log('this.commentUpdate', h,h1);
            if (h != h1) {
                $comment.oneTransEnd(function(){
                    $comment.removeAttr('style');
                    $comment.css({transition:''});
                    $comment.removeClass('overh');
                },'height').css({height:h1,transition:'height '+t+'ms cubic-bezier(.52,.14,.49,.87)'});
            }
        })
    }

    this.commentHide = function(cid, rcid, isWall, noRemove, call, prf, isBlog, isLive) {
        prf=prf||'';
        isWall=isWall||false;
        isBlog=isBlog||false;
        isLive=isLive||false;
        rcid=rcid||false;
        noRemove=noRemove||false;
        if (isWall) {
            var sel=rcid ? '#'+prf+'wall_item_comment_reply_'+rcid : '#'+prf+'wall_item_comment_'+cid;
        } else if (isBlog) {
            var sel=rcid ? '#blog_post_comments_replies_item_'+rcid : '#blog_post_comment_' + cid;
        } else if (isLive) {
            var sel=rcid ? '#ls_comments_replies_item_'+rcid : '#ls_comment_' + cid;
        } else{
            var sel=rcid ? '#comments_replies_item_'+rcid : '#pp_gallery_comment_' + cid;
        }
        var $el=$(sel);
        if(!$el[0]||$el.is('.trans'))return;
        $el.addClass('trans');

        var h=$el.find('.comment_item_wrapper').outerHeight(true);
        $el.height(h).addClass('to_remove').oneTransEnd(function(){
            !noRemove && $el.remove();
            typeof call=='function' && call();
        },'height').delay(10).toggleClass('to_remove_end',0)
    }


    this.likeChangeStatusOne = function($el, like) {
        if(!$el[0])return;
        var nLike=like?0:1,
            title=nLike?l('like'):l('liked'),
            titleA=nLike?'':l('unlike');
        if($el.is('.animated') || $el.data('like') == nLike){
            return;
        }

        $el.addClass('animated').width($el.width());
        var $elWrap=$el.clone().removeClass('animated').css({position:'absolute', left:0, width:'', opacity:0})
                    .find('.comment_item_title').text(title).attr('title', titleA).end()
                    .appendTo($el.closest('li'));
        setTimeout(function(){
            $el.oneTransEnd(function(){
                $el.remove();
                $elWrap.removeAttr('style').data({like:nLike}).attr({'data-like':nLike}).removeClass('disabled');
            },'width').css({opacity:0, width: $elWrap.width(), transition:'width .3s opacity .35s linear'});
            $elWrap.css({opacity:1, transition:'opacity .35s linear'})
        },1)
    }

    this.likeChangeStatus = function($el, like) {
        if(!$el[0])return;
        $this.likeChangeStatusOne($this.getWallPpEl($el), like);
        $this.likeChangeStatusOne($el, like);
    }

    this.updateLoadMoreCounter = function($el, v, c){//Not used
        var $counter=$el.find('.comm_to_comm_text_number.to_show');
        if(!$counter[0])return;
        $counter.find('.number_view').text(v);
        $counter.find('.number_all').text(c);
    }

    this.getWallPpEl = function($el) {
        if(!$el[0]) return [];
        var id=$el[0].id;
        if(id){
            var idO=id.replace('pp_','');
            if (idO==id) {
                return $('#pp_'+idO);
            } else {
                return $('#'+idO);
            }
        }
        return [];
    }

    this.highlightEvent = function($el) {
        if(!$el[0])return;
        $el.addClass('event_сomment_highlight').delay(2000).removeClass('event_сomment_highlight',0);
    }

    this.checkUserNameComment = function(msg, name) {
        msg=trim(msg);
        var reg=new RegExp('^\\'+name,'i');
        return msg.replace(reg, '');
    }

    this.replaceUserName = function(msg, name, uid, groupId) {
        msg=trim(msg);
        if(!msg||!uid||!name)return msg;

        name=name.toLowerCase();
        var part=msg.toLowerCase().split(' ');
        if (part[0] != undefined) {
            var namePart=name.split(' '),
                namePartL=namePart.length,
                nameRepl=part[0].toLowerCase();
            for (i = 1; i < namePartL; i++) {
                if (part[i] != undefined) {
                    nameRepl +=' ' + part[i];
                }
            }
            if (nameRepl == name) {
                var l=name.length;
                var rep=groupId ? '{group:'+groupId+'}' : '{user:'+uid+'}';
                msg=rep + msg.substr(l);
            }

        }

        return msg;


        /*groupId=groupId||0;
        var reg=new RegExp('^\\'+name,'i'),
            rep=groupId ? '{group:'+groupId+'} ' : '{user:'+uid+'} ';

        msg=msg.replace(reg, rep);
        return msg;*/
    }

    $(function(){


        $this.$placeholderComment=$('#placeholder_comment').clone();
    })

    return this;
}