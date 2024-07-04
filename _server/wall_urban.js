var CWallUrban = function(guid, photoGuid, nameGuid, ageGuid,  uid, lastPostId, scrollDelta, commentsShowCount) {

	var $this = this, lastId, dur=this.dur=700;

	this.updaterTimer = false;
	this.scrollBlock = false;
	this.oldItemsExists = false;
	this.lastPostId = lastPostId;
	this.firstPostId = 0;
	this.autoUpdateTimeout = 10000;
    this.guid = guid;
    this.photoGuid = photoGuid;
    this.nameGuid = nameGuid;
    this.ageGuid = ageGuid;

	this.uid = uid;
	this.updaterInfoLoaded = false;
	this.itemsInfo = new Object();
	this.itemComments = new Array();
	this.itemCommentsLast = new Array();
	this.itemCommentsLastId = 0;
	this.scrollDelta = scrollDelta;
	this.commentsLoadBlock = new Array();
	this.commentsExists = new Array();
	this.commentsCache = new Object();
	this.commentsVisible=[];
	this.commentsShowCount = commentsShowCount;
	this.commentsRemoveDoublesBlock = false;
    this.isOnlyFriendsWallPosts;
	this.OnlySeeFriends = 'yes';
    this.isFriend = 0;
    this.isFriendResponse = 0;
    this.loadMoreComments = 'Load more comments';
    this.loadMoreCommentOne = 'Load comment';
	this.postIdMax = 0;
    this.maxFileSize = 0;
	this.singleItemMode = true;

    this.urlTmpl = '';
    this.isProfileWall = false;
    this.langParts = {};
    this.ajaxRequest = {like:{}};


    /* Leave all of that associated with the cache */
	this.commentsCacheAdd = function(id, cid) {
		if (!$this.commentsCache[id]) {$this.commentsCache[id]={}; $this.commentsVisible[id]=0}
		if ($this.commentsCache[id][cid]) return;
		$this.commentsVisible[id]++;
		$this.commentsCache[id][cid] = cid;
		$this.itemComments[id]=Math.max($this.itemComments[id]||0, cid);
		if(!$this.itemCommentsLast[id]){
			$this.itemCommentsLast[id]=cid;
		}else{
			$this.itemCommentsLast[id]=Math.min($this.itemCommentsLast[id], cid);
		}
		$this.commentsLoadOnePostStatus(cid);
	}

	this.commentsCacheRemoveByCid = function(id, cid) {
		if (!$this.commentsCache[id][cid]) return
		delete $this.commentsCache[id][cid];
		$this.commentsVisible[id]--;
	}

	this.commentsCacheRemoveById = function(id) {
		delete $this.commentsCache[id];
		delete $this.itemComments[id];
		delete $this.itemCommentsLast[id];
	}

	this.itemInfoSet = function(id, like, comment, commentsCount, isViewed, listInterests, listLikeUser) {
		var info=$this.itemsInfo[id] = ($this.itemsInfo[id] || {})
		info.like=(like||info.like);
		info.comment=(comment||info.comment);
		info.commentsCount=(commentsCount||info.commentsCount);
        if(listInterests!=null)info.listInterests=listInterests;
        info.listLikeUser=listLikeUser;
		$this.commentsLoadMoreStatus(id, info.commentsCount, isViewed);
	}

    this.setListInterests = function(id, listInterests) {
        if($this.itemsInfo[id]){
            $this.itemsInfo[id].listInterests=listInterests;
        }
    }

	this.itemInfoDelete = function(id) {
		delete $this.itemsInfo[id];
		$this.commentsCacheRemoveById(id);
	}
    /* Leave all of that associated with the cache */

    /* URBAN */
    this.setPostIdMax = function(id){
		if(id > this.getPostIdMax()) {
			this.postIdMax = id;
		}
	}

	this.getPostIdMax = function(){
		return this.postIdMax;
	}

    this.checkDataAjax = function(res, isCheckBlocked) {
        var data=getDataAjax(res, 'page'),
            isCheckBlocked=isCheckBlocked||0;
        if(data){
            if (!$this.isAuthOnly(data)) {
                return false;
            }
            if(!isCheckBlocked&&$this.isBlocked(data)) {
                return false;
            }
            return data;
        }else{return false}
    }

    this.isAuthOnly = function(value) {
		if (value == 'please_login') {
			location.href = 'join.php?cmd=please_login';
			return false;
		}
		return true;
	}

    this.isBlocked = function(value) {
		if(trim(value) == 'you_are_blocked') {
			alertCustom(MSG_YOU_ARE_IN_BLOCK_LIST);
			return true;
		}
		return false;
	}

    this.itemExists = function(id, value) {
        if ($this.stopUdater) return;
		var exists = true;
		if (value=='not exists'||value=='deleted') {
			var $el=$('#wall_item_' + id), $els=$el.add('#wall_item_' + id+'~.wall_item');
			if (!$el[0]) return false;
            //$el.css({maxHeight:$el.height()}).delay(10).queue(function(){
                //$el.addClass('delete').oneTransEnd(function(){$(this).remove()});
                //$el.dequeue();
            //});
            //$el.slideUp($this.dur, function(){$el.remove()})
			var $sh=$el.prev().addClass('add_shadow'), h=$el.height()+1//+10;
			setTimeout(function(){
				$sh.removeClass('add_shadow');
				$el.remove();
				$els.css({transform: '', transition: '', marginTop: ''})
                $this.showNoItem();
			}, $this.dur+50);
            $el.css({zIndex:0})
			if ($this.wallItems[0].getBoundingClientRect().bottom<$win.height()+h-50) $el.css({marginTop: -h, transition: $this.dur+'ms'})
			else $els.css({transform: 'translateY(-'+h+'px)', transition: $this.dur+'ms'})
			$this.itemInfoDelete(id);
			exists = false;
		}
		return exists;
	}

    this.confirmItemDelete = function(id) {
        if ($this.stopUdater) return;
        confirmCustom(ALERT_HTML_ARE_YOU_SURE, function(){$this.itemDelete(id)}, ALERT_HTML_ALERT);
    }

    this.itemDelete = function(id) {
        if ($this.stopUdater) return;
		var url = 'wall_ajax.php?cmd=item_delete&id='+id+'&wall_uid='+$this.uid;
        setTimeout(function(){$this.itemExists(id, 'deleted')},450);
        //$this.itemExists(id, 'deleted');
		$.post(url, $this.checkDataAjax);
		return false;
	}

    this.setLastItem = function() {
        $('.wall_item').removeClass('last').last().addClass('last');
    }

    this.showNoItem = function() {
        $this.wallNoItems[$('.wall_item')[0]?'removeClass':'addClass']('show');
    }

    this.sendAjaxAdd=0;
    this.isImageLoaded=false;
    this.processUploadImage=false;
    this.itemAdd = function() {
        if($this.stopUdater) return;
        if(!Profile.isAccessToSiteWithMinNumberUploadPhotos()){
            $this.inputPostAdd.val('').trigger('autosize');
            return false;
        }

        var comment=$.trim($this.inputPostAdd.val());
        if($this.processUploadImage)return false;
        if(!comment&&!$this.isImageLoaded)return false;
        $this.inputPostAdd.val('').trigger('autosize');
        $this.setPostIdMax($this.firstPostId);

        $this.addImageWall.hide();
        $this.addImageUploadWall.hide();
        $this.$loadMsgAnimation.removeClass('hidden');
        $this.sendAjaxAdd++;

		comment=emojiToHtml(comment);
        var url = 'wall_ajax.php?cmd=item&last_item_id='+$this.getPostIdMax()+'&wall_uid='+$this.uid,
            data={comment:comment,
                  is_profile_wall:$this.isProfileWall,
                  type_wall:$this.modeWall,
                  wall_filters:$this.filters,
                  image_upload:$this.isImageLoaded}
		$.post(url, data, function(res) {
            var item=$this.checkDataAjax(res);
            if(item && item != 'empty comment'){
                $this.updateItems(item)
            }
            $this.sendAjaxAdd--;
            if(!$this.sendAjaxAdd){
                $this.$loadMsgAnimation.addClass('hidden');
                $this.addImageWall.show();
            }
		})
        $this.processUploadImage=false;
		$this.isImageLoaded=false;
        return false;
	}

    this.updateItems = function(data, isSingle, insertFn, order, isPasteScript) {
        isSingle=isSingle||0;
        if(!isSingle&&$this.singleItemMode) {
			//return;
		}
        insertFn=insertFn||'prependTo';
        var $html=$(trim(data)),isPasteScript=isPasteScript||0;
        if(!isPasteScript)$this.$scriptAjaxUpdate.append($html.filter('script'));
        var $items=$html.filter('div.wall_item'), t=$this.dur, $item, i=0;
        var order=order||0;
        if (!order) {
            i=$items.length-1;
        }
        if ($this.changeCondition&&!$this.isProfileWall){
            $this.inputPostAdd.blur();
            $this.changeCondition=false;
            //var $layer=$('.column_main_cont>*').fadeTo(200,.3);
			$('.wall').addClass('loading')
            $this.$wallContent.fadeTo(0,.3);
            var isChange=false;
            $this.preventLayer.stop().show();
            for (var id in $this.itemsInfo) {
                $this.itemInfoDelete(id);
            }
            $('body, html').stop()
                .animate({scrollTop:0},500,function(){
                            if(isChange)return;
                            isChange=true;
                            //$layer.stop().delay(200).fadeTo(200,1);
                            setTimeout(function(){
                                destroyAllCustomPlayers();
								$('.wall').delay(250).removeClass('loading', 1);
								$this.$wallContent.fadeTo(0,1);
                                $this.preventLayer.stop().hide();
                                $this.wallItems.html($items);
                                $this.setLastItem();
                                $this.initLightbox();
                                $this.showNoItem();
                                $this.stopUdater=false;
                                $this.scrollBlock=false;
                                $this.lastPostId=$this.firstPostId;
                                $this.updaterInit();
                            },200);
            });
            return;
        }

        (function fu(){
			//$('script', $this.wallContent).remove();????????
			$('.wall_items .add_shadow').removeClass('add_shadow')
            $item=$items.eq(i);
            if(!$item[0]){//i<0||
                if(insertFn=='appendTo')$this.scrollBlock=false;
                return;
            }; if(!order)i--; else i++;
            if (!$('#'+$item[0].id)[0]){
                $item.hide()[insertFn]($this.wallItems).addClass(insertFn);
                $this.showNoItem();
				$item.prev().addClass('add_shadow');
                fu();
                $this.setLastItem();
                $this.initLightbox();
            } else {fu()}
        })()
		if (insertFn=='prependTo') {
            $this.checkTop();
        } else {
            $this.showHistory(dur);
    }
    }

    this.itemsHistoryLoad = function() {
        if ($this.stopUdater) return;
		$this.setPostIdMax($this.firstPostId);
        $this.$loadAnimationBl.show();
		$this.$loadAnimation.slideDown(parseInt($this.dur/3));
		var url = 'wall_ajax.php?cmd=items_old&id=' + $this.lastPostId + '&wall_uid=' + $this.uid
                  + '&is_see=' + $this.OnlySeeFriends+ '&is_profile_wall='+$this.isProfileWall;//
		$.get( url, function(res) {
            var items=$this.checkDataAjax(res,1);
            if(items && (items=trim(items))){
                $this.updateItems(items, 1, 'appendTo', 1);
                $this.$loadAnimation.slideUp($this.dur);
            }
		});
	}

	this.itemsHistoryLoader = function() {
		$win.scroll(function() {
            if ($this.stopUdater||!$this.$wallContent.is(':visible'))return false;
			if ($this.oldItemsExists && !$this.scrollBlock && ($win.scrollTop() > $(document).height() - $win.height() - $this.scrollDelta)) {
                $this.scrollBlock = true;
				$this.itemsHistoryLoad();
			}
			$this.checkTop();
		});
	}

    this.showHistory = function(dur) {
        dur*=.7;
        var $item=$('.wall_item.appendTo:hidden:first').slideDown(dur, function(){
            $(this).removeClass('appendTo');
            $this.showHistory(dur);
        }).scrollTop(5000);
        $this.showLoadInterest($item, 1);
	}

    this.checkTop = function() {
		if ($(window).scrollTop()<$('#wall_items').offset().top && !$('.wall_item:animated')[0]) {
            var $item=$('.wall_item.prependTo:hidden:last').slideDown(dur, function(){
                $(this).removeClass('prependTo');
                $this.checkTop();
            }).scrollTop(5000);
            $this.showLoadInterest($item, 1);
		}
	}

    this.showLoadInterest = function($item, dur) {
        if($item.find('.user_list_interest')[0]){
            $this.prepareShowInterests($item.data('id'),dur);
        }
    }

    this.actionShowDots = {};
    this.showMoreInterestsDots = function(id,$dots) {
        var dur=300,
            $ul=$('#wall_interests_ul_'+id).addClass('show_more'),
            h=$('.interests_user_item', $ul).last()[0].offsetTop+28;
        $this.actionShowDots[id]=1;
        $dots.addClass('hide');
        $ul.css({width:($ul[0].offsetWidth-$dots.data('offset'))+'px'});
        $('#wall_interests_bl_'+id).animate({maxHeight:h+'px'},dur,function(){
            $(this).css({maxHeight:'10000px', overflow:'visible'});
        });
    }

    this.prepareInterestsShowTab = function(){
        $('.user_list_interest', $this.$wallContent).each(function(){
            var $el=$(this);
            if(!$el.is('.prepare')){
                $this.prepareShowInterests($el.data('id'),250);
            }
        })
    }

    this.prepareShowInterests = function(id,dl){
        if(dl=='no')return;
        dl=dl*1;
        setTimeout(function(){
            var $ul=$('#wall_interests_ul_'+id).addClass('prepare');
            if(!$ul[0])return;
            if($ul.is('.show_more'))return;
            var is=0,w=0,wP=0,$list=$('.interests_user_item',$ul),of=0;
            $ul.css({width:'auto'});
            $list.each(function(){
                var $li=$(this);
                if($li[0].offsetTop>24){
                    is=1;w += 24;
                    of=18;
                    return false;
                }else{
                    wP=$li[0].offsetLeft+$li[0].offsetWidth;
                    if(wP>630){
                        is=1;
                        w += 24;
                        of=18;
                        return false;
                    }
                    w=wP;
                }
            });

            if(w){
                $ul.width(w+1);
                if(w>670){
                    if (is) {
                        w -=73;
                        of=37;
                    } else {
                        w -=32;
                    }
                } else {
                    w -=54;
                }
                if (is) $list.find('span').css({maxWidth:w});
            }
            $ul.closest('.wall_interests_bl').fadeTo(1,1);
            $('#wall_interests_dots_'+id)[is?'removeClass':'addClass']('hide').data('offset',of);
        },dl||$this.dur*.7);
    }

    this.showInterest = function(id, intId, cat, title, myClass,titleLinkInterests) {
        var myClass=myClass||'';
        var titleLinkInterests=titleLinkInterests||'';
        if ($this.stopUdater||$('#wall_int_'+intId)[0]) return;
        var tmpl='<li style="cursor:pointer;" id="wall_int_'+intId+'" data-interest-id="'+intId+'" class="interests_user_item '+myClass+'" onclick="searchInterests(this);return false;" title="'+titleLinkInterests+'">'+
                 '<span class="field_interests_'+cat+'_profile text-overflow ">'+title+'</span></li>';
        $(tmpl).hide().prependTo($('.user_list_interest', '#wall_item_'+id))
               .show({duration:300,
                      step:function(){$this.prepareShowInterests(id,1)},
                      complete:function(){$this.prepareShowInterests(id,1)}
               });
    }

    this.removeInterest = function(id, intId) {
        if($this.stopUdater||!$('#wall_int_'+intId)[0])return;
        $('#wall_int_'+intId, '#wall_item_'+id)
            .animate({width:0,opacity:0,padding:0},
                     {duration:300,
                      step:function(){$this.prepareShowInterests(id,1)},
                      complete:function(){$this.prepareShowInterests(id,1); $(this).remove();}});
    }

    this.showTabWall = function() {
        if(!$this.isProfileWall||$this.stopUdater||$this.uid==$this.guid)return;
        var $tabProfileWall=$('#tab_wall');
        if(!$tabProfileWall[0]){
            var li='<li><a data-tab="tabs-3" id="tab_wall" class="tab_wall tabs_switch not_allowed" href="#tabs-3" title=""><span>'+$this.langParts['tab_wall']+'</span></a></li>',
                pos=positionTabWall*1-1;
            if(pos<0){
                $this.$tabs.find('ul.tab').prepend(li);
            }else{
                $this.$tabs.find('ul.tab > li').eq(pos).after(li);
            }
            setTimeout(function(){$('#tab_wall').removeClass('not_allowed')},100);
        } else if($tabProfileWall.is('.not_allowed')) {
            $tabProfileWall.removeClass('not_allowed')
        }
    }

    this.disallowFriendShowWall = function() {
        if(!$this.isProfileWall||$this.uid==$this.guid)return;
        var $tabProfileWall=$('#tab_wall');
        if(!$tabProfileWall[0]||$tabProfileWall.is('.not_allowed'))return;
        //$this.stopUdater = true;
        if($this.$wallContent.is(':visible')){
            //$tabProfileWall
            var $next=$tabProfileWall.parent('li').next('li').children('a');
            if(!$next[0]){
                $next=$('ul.tab',$this.$tabs).find('li > a');
            }
            if($next[0]){
                var h=$next[0].href.match(/#(tabs-[1-3])/);
                h[0]&&(location.hash=h[0]);
            }
        }
        var isAlert=isAlert||0,isAddFriend=isAddFriend||0;
        $tabProfileWall.addClass('not_allowed');
        if(!$('.pp_alert:visible')[0])alertCustom($this.langParts['wall_is_not_available'],1,ALERT_HTML_ALERT);
        return true;
    }

    this.checkToSeeWall = function(data) {
        if(!$this.isProfileWall||$this.guid==$this.uid||$this.stopUdater)return;
        //console.log($this.OnlySeeFriends, $this.isFriend, $this.isFriendResponse, $this.guid, $this.uid);
        var isChangedFriend = $this.isFriend != $this.isFriendResponse;
        //console.log(isChangedFriend);
        var is=null;
        if($this.OnlySeeFriends=='yes'&&(data=='no'||data=='no_post')){
            $this.OnlySeeFriends=data;
            $this.disallowFriendShowWall();
            if($this.isFriend||isChangedFriend){
                is=0;
                Profile.requestAjaxAddFriend('remove', $this.uid, 1);
            }
        }else if(($this.OnlySeeFriends=='no' || $this.OnlySeeFriends=='no_post') && data!='no' && data!='no_post'){
            $this.OnlySeeFriends='yes';
            $this.showTabWall();
            if (isChangedFriend) {
                is=0;
                Profile.requestAjaxAddFriend('approve', $this.uid, 1);
            }
        } else if (isChangedFriend) {
            if ($this.isFriendResponse) {
                Profile.requestAjaxAddFriend('approve', $this.uid, 1);
            } else {
                Profile.requestAjaxAddFriend('remove', $this.uid, 1);
            }
        }
        if(is==null){
            $this.isFriend = $this.isFriendResponse;
        }else{
            $this.isFriend = $this.isFriendResponse = is;
        }
    }

    this.isLikeToMeet={};
    this.likeToMeet = function(id, uid, $el) {
        if(uid in $this.isLikeToMeet)return;
        $this.isLikeToMeet[uid]=1;
        var isActive=$el.is('.active')||$el.is('.active_show');
        $('.wall_like_to_meet_'+uid).each(function(){
            var $likeLink=$(this);
            if(isActive){
                $likeLink.clone().attr({title:$this.langParts['like_to_meet'],style:''})
                         .addClass('active_not').removeClass('active_show active')
                         .insertBefore($likeLink);
            }else{
                $likeLink.clone().attr({title:$this.langParts['unlike_to_meet']})
                     .insertBefore($likeLink).addClass('active_show').fadeTo(350,1);
            }
            $likeLink.css({opacity:0, transition:'opacity .31s'}).oneTransEnd(function(){
                $likeLink.remove();
                delete $this.isLikeToMeet[uid];
            });
        })
        var url='wall_ajax.php?cmd=like_to_meet&uid='+uid+'&wall_uid='+$this.uid+'&reply='+(isActive?'N':'Y');
		$.post(url, $this.checkDataAjax);
    }

    /* Like */
    this.likeAdd = function(id) {
        if($this.ajaxRequest.like[id])return;
        $this.ajaxRequest.like[id]=1;
        $this.likeUserAdd(id, $this.guid, {photo:$this.photoGuid, name:$this.nameGuid, age:$this.ageGuid});
		$.get('wall_ajax.php?cmd=like&id='+id+'&wall_uid='+$this.uid, function(res) {
            var data=$this.checkDataAjax(res);
			if (data && $this.itemExists(id, data)) {
                $this.$scriptAjaxUpdate.append($(trim(data)).filter('script'));
			} else {
            }
            $this.ajaxRequest.like[id]=0;
        });
	}

    this.likeDelete = function(id) {
        if($this.ajaxRequest.like[id])return;
        $this.ajaxRequest.like[id]=1;
        $this.likeUserRemove(id, $this.guid);
        $.get('wall_ajax.php?cmd=unlike&id='+id+'&wall_uid='+$this.uid, function(res) {
			var data=$this.checkDataAjax(res);
			if (data && $this.itemExists(id, data)) {
                $this.$scriptAjaxUpdate.append($(trim(data)).filter('script'));
            }else{

            }
            $this.ajaxRequest.like[id]=0;
        });
    }

    this.switchLike = function(id) {
        if(!Profile.isAccessToSiteWithMinNumberUploadPhotos()){
            return;
        }
        if($this.ajaxRequest.like[id])return;
        $this[$('#btn_wall_like_'+id).is('.selected')?'likeDelete':'likeAdd'](id);
    }

    this.prepareLikesItem = function(id, listLikeUser, listLikeUserInfo) {
        if($this.itemsInfo[id]){
            var likesOld=$this.itemsInfo[id].listLikeUser.split(','),
                likesNew=listLikeUser.split(',');
            $this.itemsInfo[id].listLikeUser=listLikeUser;
            for (var key in likesOld) {
                var uid=trim(likesOld[key]);
                if(!uid)continue;
                if(!in_array(uid, likesNew)&&$('#wall_like_user_'+id+'_'+uid+':not(:animated)')[0]) {
                    $this.likeUserRemove(id, uid);
                }
            }
            for (var key in likesNew) {
                var uid=trim(likesNew[key]);
                if(!uid)continue;
                $this.likeUserAdd(id, uid, listLikeUserInfo[uid]);
            }
        }
    }

    this.lastPosLikeUser = {};
    this.likeUserAdd = function(id, uid, infoUser) {
        if ($('#wall_like_user_'+id+'_'+uid)[0])return;
        if ($this.guid == uid) {
            $('#btn_wall_like_'+id).addClass('selected').attr('title', $this.langParts['unlike']);
        }
        var urlPhoto=urlFiles+infoUser['photo'],
            title=infoUser['name']+', '+infoUser['age'],
            tmpl='<a id="wall_like_user_'+id+'_'+uid+'" class="wall_like_user" href="'+url_main+'search_results.php?display=profile&uid='+uid+'&ref=wall&wall_item='+id+'">'+
                 '<div title="'+title+'" class="pic wall_like_user_pic_'+uid+'" data-url="'+urlPhoto+'" style="background-image: url('+urlPhoto+'?v='+Photo.photoVersion+');"></div>'+
                 '</a>';
        var $bl=$('#wall_like_bl_'+id),pos;
        if($this.lastPosLikeUser[id]){
            pos=$this.lastPosLikeUser[id] ? 'l' : 'r';
            $this.lastPosLikeUser[id] = $this.lastPosLikeUser[id] ? 0 : 1;
        }else{
            var cL=$bl.find('.people_like.l > .wall_like_user').length,
                cR=$bl.find('.people_like.r > .wall_like_user').length;
            pos='r';
            $this.lastPosLikeUser[id] = 1;
            if(cR>cL){
                pos='l';
                $this.lastPosLikeUser[id] = 0;
            }
        }
        $bl.find('.people_like.'+pos).prepend($(tmpl));
        setTimeout(function(){
            $('#wall_like_user_'+id+'_'+uid).addClass('show');
            $this.showMoreLikeArrowDelay(id);
        },10);
    }

    this.likeUserRemove = function(id, uid) {
        delete $this.lastPosLikeUser[id];
        if($this.guid == uid) {
            $('#btn_wall_like_'+id).removeClass('selected')
                                   .attr('title', $this.langParts['like']);
        }
        $('#wall_like_user_'+id+'_'+uid).removeClass('show').oneTransEnd(function(){$(this).remove()},'width');
        $this.showMoreLikeArrowDelay(id);
    }

    this.showMoreLikeArrowDelay = function(id, d) {
        d=d||50;
        setTimeout(function(){$this.showMoreLikeArrow(id)},d);
    }

    this.showMoreLikeArrow = function(id) {
        var $likeBl=$('#wall_like_bl_'+id),
            cL=$likeBl.find('.people_like.l > .wall_like_user.show').length,
            cR=$likeBl.find('.people_like.r > .wall_like_user.show').length,
            $arrow=$('#wall_like_more_'+id);
        if(!$arrow[0]){
            $arrow=$('#wall_like_bl_'+id).find('.icon_wall_like_item.hide').removeClass('hide').hide().attr('id', 'wall_like_more_'+id);
        }
        if(cL<=6&&cR<=6){
            $likeBl.removeClass('open').css({height:'63px'})
            $arrow.removeClass('up').stop().slideUp(200);
        }else{
            var $afEl=$likeBl.find('.people_like.r').find('.wall_like_user').eq(4);
            if($afEl[0]){
                $afEl.after($arrow.slideDown(200));
            }else{
                $likeBl.find('.people_like.r').append($arrow.slideDown(200));
            }
        }
    }

    this.switchMoreLike = function(id,$el) {
        var $likeBl=$('#wall_like_bl_'+id);
        $this[$likeBl.is('.open')?'hideMoreLike':'showMoreLike'](id,$likeBl,$el);
    }

    this.showMoreLike = function(id,$likeBl,$el) {
        var h=$likeBl.css({height:'auto'}).height();
        $el.addClass('up');
        $likeBl.addClass('open').css({height:'63px'}).animate({height:h},
                    {duration:200,
                     step:function(){},
                     complete:function(){$(this).css({height:'auto'})}});
    }

    this.hideMoreLike = function(id,$likeBl,$el) {
        $el.removeClass('up');
        $likeBl.removeClass('open').animate({height:'63px'},
                    {duration:220,
                     step:function(){},
                     complete:function(){
                        $this.showMoreLikeArrow(id);
                     }
                 });
    }
    /* Like */
	/* Comments */
	this.commentsLoadMoreStatus = function(id, count, isViewed) {
		count *=1;
		isViewed *=1;
		var itemLoadMore = $('.wall_post_comments_' + id),number;
		if (itemLoadMore.fadeIn(dur).data('q')==count) return;
		itemLoadMore.data('q',count);
		if(!count){
			number=$this.langParts.leave_a_comment;
		}else{
			number=$this.langParts.load_more_comments.replace(/{number}/,count);
		}
		itemLoadMore.html(number)[count?'removeClass':'addClass']('disabled');
		itemLoadMore[(isViewed&&count)?'addClass':'removeClass']('new');
	}

	this.commentsLoadOnePostStatus = function(id) {
		id=id||0;
		//console.log($this.itemCommentsLastId,id,$('#wall_item_comment_'+$this.itemCommentsLastId)[0],$this.itemCommentsLastId==id||$('#wall_item_comment_'+$this.itemCommentsLastId)[0]);
		if($this.itemCommentsLastId==id||$('#wall_item_comment_'+$this.itemCommentsLastId)[0]){
			$this.$ppOnePostLinkLoadComments.fadeOut(300);
		}else{
			$this.$ppOnePostLinkLoadComments.fadeIn(300);
		}
	}

	this.prepareShowOnePost = function(){
		$this.$ppOnePostCont.empty();
		$this.$ppOnePostListComments.empty();
        $this.$ppOnePostBlListComments.css({height:0,transition:'none'});
		$this.$ppOnePostLinkLoadComments.hide();
		$this.$ppOnePostFrm.fadeTo(1,0);
		$this.$wallOnePostLoader.removeClass('hidden');
		$this.$wallOnePostLoadCommentsLoader.addClass('hidden');
		$this.$ppOnePostCommentText.val('').trigger('autosize');
		//$this.$ppOnePostContBl.removeAttr('style');
	}

	this.OnePostId=0;
	this.isOpenOnePost=0;
	this.showOnePost = function(id){
        if(!Profile.isAccessToSiteWithMinNumberUploadPhotos()){
            return;
        }

        if($this.isOpenOnePost)return;
		$this.isOpenOnePost=1;
		$this.prepareShowOnePost();
		var $cont=$('#wall_text_post_'+id).clone();
		$cont.find('.show_one_post').remove();
		$cont.find('img').each(function(){
			var $img=$(this),$parent=$img.parent('.smile');
			if(!$parent[0]){
                $img.css({opacity:0, transition:'none'});
                $img.closest('.wall_image_post').not('.wall_image_post_meta_link').append($this.$wallOnePostLoader.clone().addClass('image_load_wall_one_pos'));
            }
		});
		var $imgUser=$('#wall_text_post_'+id).closest('.wall_item')
				.find('a.user_item_photo').clone()
				.find('.info').remove().end();
		var $bl=$('<div class="pp_wall_post_cont to_hide">'+
					'<div class="pp_wall_wall_one_post_user_info">' +
					'<a class="pp_wall_wall_one_post_name" href="'+$imgUser.attr('href')+'">' + $cont.data('name') + '</a>'+
					'</div>' +
					'<div class="pp_wall_wall_one_post_text"></div>'+
				  '</div>');
		var $postText=$bl.find('.pp_wall_wall_one_post_text').html($cont.html());
		$bl.find('.pp_wall_wall_one_post_user_info').prepend($imgUser);
		$bl.find('br').each(function(){
			var $el=$(this),$prev=$el.prev();
			if($prev[0]&&$prev.is('.wall_image_post, .wall_video_post')){
				$el.remove();
			}
		})
		$this.$ppOnePostCont.append($bl);

		var url = 'wall_ajax.php?cmd=comments_load&id=' + id + '&last_id=0&wall_uid=' + $this.uid;
		//'wall.php?cmd=get_one_post&item='+id+'&uid='+uid
		$.post(url, function(res) {
            var data=$this.checkDataAjax(res,1);
            if(data){
				data=$('<div>'+data+'</div>');
                $this.$scriptAjaxUpdate.append(data.filter('script'));
				var $comment=$('.wall_item_comment', data).fadeTo(1,0);
				if($comment[0]){
					$this.$ppOnePostListComments.append($comment.delay(200).fadeTo(400,1));
					setTimeout(function(){
                        $this.$ppOnePostBlListComments
                             .oneTransEnd(function(){
                                 $this.$ppOnePostBlListComments.css('height', 'auto');
                             })
                             .css({height:$this.$ppOnePostListComments[0].offsetHeight, transition:'all .5s, height .5s'});
						$this.commentsLoadOnePostStatus();
						$this.initLightbox();
					},50)
				}else{
                    $this.$ppOnePostBlListComments.css('height', 'auto');
                }
				$this.$ppOnePostFrm.delay(200).fadeTo(400,1);
				setTimeout(function(){
					$this.$wallOnePostLoader.addClass('hidden');
					$this.isOpenOnePost=0;
				},180);
			}
        })
		setTimeout(function(){
            $postText.find('.wall_image_post img').each(function(){
                var $img=$(this),$parent=$img.parent('.smile');
                if(!$parent[0]){
                    $img.one('load',function(){
                        var h=$img[0].offsetHeight;
                        if(!h)h='auto';
                        $img.css({opacity:1, transition:'opacity .6s linear'});
                        $img.closest('.wall_image_post')
							.not('.wall_image_post_meta_link')
                            .css({height:h, transition:'all .4s, height .4s'})
                            .find('.image_load_wall_one_pos').addClass('hidden');
                    })[0].src=$img.data('src-b');
                }
            });
			$this.initLightbox();
			$this.OnePostId=id;
			$this.$ppOnePost.open();
			$bl.delay(100).removeClass('to_hide',0);
		},100)
	}

	this.closeOnePost = function(id){
		$this.$ppOnePost.close();
		$this.commentsCacheRemoveById($this.OnePostId);
		$this.OnePostId=0;
		$this.itemCommentsLastId=0;
		$this.isOpenOnePost=0;
		setTimeout($this.prepareShowOnePost,200);
	}

	this.ajaxLoadComments=false;
	this.loadComments = function(){
		if(!$this.OnePostId||$this.ajaxLoadComments)return;
		$this.ajaxLoadComments=true;
		$this.$wallOnePostLoadCommentsLoader.removeClass('hidden');
		var url = 'wall_ajax.php?cmd=comments_load&id=' + $this.OnePostId + '&last_id='+$this.itemCommentsLast[$this.OnePostId]+'&wall_uid=' + $this.uid;
		$.post(url, function(res) {
			var items=$this.checkDataAjax(res,1);
            if(items && (items=trim(items))){
                var $html=$(items),$comment,$comments=$html.filter('.wall_item_comment'),i=0;
				(function fu(){
					$comment=$comments.eq(i);
					if(!$comment[0]){
						return;
					}
					if (!$('#'+$comment[0].id)[0]){
						$this.commentsLoadOnePostStatus($comment.data('id'));
						$comment.hide();
						var is=$this.itemComments[$this.OnePostId]>$comment.data('id');
						$this.$ppOnePostListComments[is?'append':'prepend']($comment.delay(50).slideDown(200))
						setTimeout($this.initLightbox,50);
					}
					i++;fu();
				})();
			}
			$this.$wallOnePostLoadCommentsLoader.addClass('hidden');
			$this.ajaxLoadComments=false;
        })
	}

	this.confirmCommentDelete = function(id, cid) {
        confirmCustom(ALERT_HTML_ARE_YOU_SURE, function(){$this.commentDelete(id, cid)}, ALERT_HTML_ALERT);
    }

	this.commentDeleteFromPage = function(id, cid) {
		if (!$this.commentsCache[id][cid]) return;
		$this.itemsInfo[id].commentsCount--;
		$this.commentsCacheRemoveByCid(id, cid);
		$this.commentsLoadMoreStatus(id, $this.itemsInfo[id].commentsCount);
		$('#wall_item_comment_'+cid).toggleClass('opacity').slideUp(400,function(){
            $(this).remove();
		})
	}

	this.commentDelete = function(id, cid) {
		var $comment=$('#wall_item_comment_'+cid).toggleClass('opacity').slideUp(400,function(){
            $(this).remove();
		})
		var url = 'wall_ajax.php?cmd=comment_delete&id='+id+'&cid='+cid
				   +'&last_id='+($this.itemCommentsLast[id]||0)+'&wall_uid='+$this.uid;
		$.post(url,function(res) {
			var data=$this.checkDataAjax(res);
			if (data && $this.itemExists(id, data)) {
				$this.commentDeleteFromPage(id, cid);
				$comment=$(trim(data)).filter('.wall_item_comment').hide();
				$this.showComment($comment);
				/*if($comment[0]&&!$('#'+$comment[0].id)[0]){
					$('#wall_item_comments_'+id).append($comment.delay(100).slideDown(400));
					$this.commentsLoadOnePostStatus($comment.data('id'));
				}*/
			}
		})
		return false;
	}

	this.postComment = function() {
		var comment=$.trim($this.$ppOnePostCommentText.val());
		$this.$ppOnePostCommentText.val('').trigger('autosize');//.focus();
		if(comment){
            var data={comment:comment,
					  id:$this.OnePostId,
					  last_id:$this.itemComments[$this.OnePostId],
					  wall_uid:$this.uid
				};
            $.post('wall_ajax.php?cmd=comment',data,
            function(res){
				var data=$this.checkDataAjax(res);
                if (data){
					var $data=$(trim(data));
                    $this.showComment($data.filter('.wall_item_comment').hide());
					$this.itemsInfo[$this.OnePostId].commentsCount++;
					$this.commentsLoadMoreStatus($this.OnePostId, $this.itemsInfo[$this.OnePostId].commentsCount);
                }
            })
        }
		return false;
	}

	this.showComment = function($comment){
		if(!$this.OnePostId)return;
		if($comment[0]&&!$('#'+$comment[0].id)[0]){
			$comment.hide();
			//$this.commentsLoadOnePostStatus($comment.data('id'));
			var is=$this.itemComments[$this.OnePostId]>$comment.data('id');
			$this.$ppOnePostListComments[is?'append':'prepend']($comment.delay(100).slideDown(400));
			setTimeout($this.initLightbox,100);
		}
    }
	/* Comments */

    this.filters = '';
    this.changeCondition = false;
    this.modeWall = '';
    this.changeMode = function(type) {
        if($this.modeWall==type)return;
        $this.stopUdater=true;
        $this.inputPostAdd.blur();
        $this.modeWall=type;
		$('.wall').addClass('loading');
        $this.$wallContent.fadeTo(0,.3);
        //$('.column_main_cont>*').fadeTo(200,.3);
        $this.preventLayer.stop().show();
        $this.updater(1);
    }

    this.updater = function(isChangeMode) {
        var isChangeMode=isChangeMode||0;
        if ($this.stopUdater&&!isChangeMode) return;
        clearTimeout($this.updaterTimer);
		$this.setPostIdMax($this.firstPostId);
		var url='wall_ajax.php?cmd=update&last_item_id='+$this.getPostIdMax()+'&wall_uid='+$this.uid
                +'&type_wall='+$this.modeWall+'&change_mode='+isChangeMode+'&one_post_id='+$this.OnePostId,
            data={last_comment_id: $this.itemComments[$this.OnePostId]||0,
				  items: $this.itemsInfo,
                  comments: $this.commentsCache,
                  is_see:$this.OnlySeeFriends,
                  is_profile_wall:$this.isProfileWall,
                  wall_filters:$this.filters};
		$.post(url,data, function(res){
            if ($this.stopUdater&&!isChangeMode) return;
            var items=$this.checkDataAjax(res,1);
            if(items && (items=trim(items))){
                var $html=$(items), script=$html.filter('script');

                $this.$scriptAjaxUpdate.append(script);
                //script.remove();
                $this.checkToSeeWall(items);
                if(items!='no'&&items!='no_post')$this.updateItems(items, 0, 0, 0, 1);

				/*comments*/
				if($this.OnePostId&&!$this.isOpenOnePost){
					var $comment,$comments=$html.filter('.wall_item_comment'),i=0;
					(function fu(){
						$comment=$comments.eq(i);
						if(!$comment[0]){
							return;
						}
						if (!$('#'+$comment[0].id)[0]){
							$this.commentsLoadOnePostStatus($comment.data('id'));
							$comment.hide();
							var is=$this.itemComments[$this.OnePostId]>$comment.data('id');
							$this.$ppOnePostListComments[is?'append':'prepend']($comment.delay(100).slideDown(400,function(){
								i++;fu();
							}))
							setTimeout($this.initLightbox,100);
						}else{
							i++;fu();
						}
					})()
				}
				/*comments*/
            }
            $this.updaterInit();
        });
    }

    this.stopUdater = false;
    this.updaterInit = function() {
        if ($this.stopUdater) return;
		$this.singleItemMode = false;
		clearTimeout($this.updaterTimer);
		$this.updaterTimer = setTimeout($this.updater,$this.autoUpdateTimeout);
	}

    this.changeUploadImage = function(file) {
        file.parent('form').find('input[type=submit]').click();
    }

    this.clearUploadImage = function(){
        $this.processUploadImage=false;
        $this.isImageLoaded=false;
        $this.addImageUploadWall.hide();
        $this.addImageWall.show();
    }

    this.initLightbox = function() {
        var data={
            resizeImage:   true,
            maxWidth:      $this.maxWidthLightbox,
            imageLoading:  $this.urlTmpl+'common/lightbox/images/loader_urban.gif',
            imageBtnPrev:  $this.urlTmpl+'common/lightbox/images/prev.gif',
            imageBtnNext:  $this.urlTmpl+'common/lightbox/images/next.gif',
            imageBtnClose: $this.urlTmpl+'common/lightbox/images/close.gif',
            imageBlank:    $this.urlTmpl+'common/lightbox/images/blank.gif'
        };
        $('.wall_image_post .lightbox_pics').lightBox(data);
        data.setWrap=true;
        $('.pp_wall_one_post .lightbox').lightBox(data);
    }

    this.replaceUserInfo = function(name, age) {
        $this.nameGuid=name;
        $this.ageGuid=age;
        $('.wall_item_user_info_'+$this.guid).html(name+', <em>'+age+'</em>');
        $('.wall_like_user_pic_'+$this.guid).attr('title', name+', '+age);
    }

	$(function(){
        var $contW=$('.cont_w');
        $this.maxWidthLightbox=$contW[0]?$contW[0].offsetWidth:995;

        $this.$wallContent=$('#wall_content').css({transition: 'opacity .2s linear'});
        $this.preventLayer=$('#prevent_layer');
        $this.$loadAnimationBl=$('.wall_load_old_items');
        $this.$loadAnimation=$('#load_animation');
        $this.blInpWallMsg=$('.bl_inp_wall_msg');
        $this.$loadMsgAnimation=$('#wall_msg_loader');
        $this.$scriptAjaxUpdate=$('#wall_script_ajax');
        $this.wallItems=$('#wall_items');
        $this.wallNoItems=$('#wall_no_items');
		$this.inputPostAdd=$('#wall_item_add').keydown(doOnEnter($this.itemAdd)).autosize();
        $this.$tabs=$('#tabs');

        $this.setLastItem();
        $this.initLightbox();

        $('input.wall_input_file',$this.$wallContent).click(function(){
            $(this).next('input[type=reset]').click();
        });

        $win.click(function(){
            customHideTip('#add_image_wall','null');
        })

        $this.addImageWall=$('#add_image_wall');
        $this.addImageUploadWall=$('#add_image_upload_wall');
        $('#wall_frm_upload_image').submit(function(e){
            var frm = $(this), file = frm.find('input[type=file]'),
                fileName = file.attr('name'), formData = new FormData(),
                error = '';

                $.each(file[0].files, function(i, file){
                    if ("image/jpeg,image/png,image/gif".indexOf(file.type) === -1) {
                        error = $this.langParts['accept_file_types'];
                        return false;
                    }else if (file.size > $this.maxFileSize) {
                        error = $this.langParts['max_file_size'];
                        return false;
                    }
                    formData.append(fileName, file);
                });
                if (error) {
                    customShowTip('#add_image_wall','null',error,'.footer',7);
                    return false;
                }
                $this.processUploadImage=true;
                $this.addImageWall.hide();
                $this.$loadMsgAnimation.removeClass('hidden');
                var xhr = new XMLHttpRequest();
                xhr.open("POST", url_ajax+'?cmd=upload_image_wall&input_name=' + fileName);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if(xhr.status == 200) {
                            var data = xhr.responseText;
                            data = checkDataAjax(data);
                            if (data) {
                                $this.$loadMsgAnimation.addClass('hidden');
                                $this.processUploadImage = false;
                                if (data!='complete_upload_image') {
                                    $this.addImageWall.show();
                                    customShowTip('#add_image_wall','null',error,'.footer',7);
                                } else {
                                    $this.addImageUploadWall.show();
                                    $this.isImageLoaded=true;
                                }
                            }
                        }
                    }
                };
                xhr.send(formData);
                return false;
        });

        $('body').on('click', '#tab_wall', function(){
            $this.prepareInterestsShowTab();
        })

        if($('#tab_wall').is('.active')){
            $this.prepareInterestsShowTab();
        }

		$this.$ppOnePost=$('#pp_wall_one_post').modalPopup({css:{}, shCss:{opacity: .7}, wrCss:{overflowY: 'scroll'}});//, wrCss:{overflowY: 'scroll'}
		$this.$ppOnePostContBl=$('.wall_one_post_cont');
		$this.$ppOnePostCont=$('#pp_wall_one_post_cont');
		$this.$ppOnePostFoot=$('#pp_wall_one_post_foot');
		$this.$ppOnePostListComments=$('.wall_one_post_list_comment');
        $this.$ppOnePostBlListComments=$('#pp_wall_wall_one_post_list_comment');
		$this.$ppOnePostFrm=$('#pp_wall_wall_one_post_frm');
		$this.$ppOnePostLinkLoadComments=$('#pp_wall_wall_one_post_load_comments');
		$this.$wallOnePostLoader=$('.wall_comments_loader');
		$this.$wallOnePostLoadCommentsLoader=$('.wall_load_comments_loader');
		$this.$ppOnePostCommentText=$('#pp_wall_wall_one_post_comment').val('').keydown(doOnEnter($this.postComment)).autosize();

		$('body').on('click', '.pp_wrapper', function(e){
			if($(e.target).is('.pp_wrapper'))$this.$ppOnePost.close();
		}).on('click', '.wall_posted a.lightbox', function(e){
			return false;
		})

	})
	return this;
}