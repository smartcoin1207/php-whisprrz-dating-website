var CWall = function(uid, lastPostId, scrollDelta, commentsShowCount, langParts) {

	var $this = this, lastId, dur=this.dur=500;

	this.updaterTimer = false;
	this.scrollBlock = false;
	this.oldItemsExists = false;
	this.lastPostId = lastPostId;
	this.firstPostId = 0;
	this.autoUpdateTimeout = 10000;
	this.uid = uid;
	this.updaterInfoLoaded = false;
	this.itemsInfo = new Object();
	this.itemComments = new Array();
	this.scrollDelta = scrollDelta;
	this.commentsLoadBlock = new Array();
	this.commentsExists = new Array();
	this.commentsCache = new Object();
	this.commentsVisible=[];
	this.commentsShowCount = commentsShowCount;
	this.commentsRemoveDoublesBlock = false;
	this.OnlySeeFriends = 'yes';
    this.loadMoreComments = 'Load more comments';
    this.loadMoreCommentOne = 'Load comment';
    this.isReload = false;
	this.postIdMax = 0;
    this.isUploadImage = false;
    this.isImageLoaded = false;
    this.isImageUpload = false;
    this.maxFileSizeImageUpload = 0;
	this.singleItemMode = true;

    this.setPostIdMax = function(id)
	{
		if(id > this.getPostIdMax()) {
			this.postIdMax = id;
		}
	}

	this.getPostIdMax = function()
	{
		return this.postIdMax;
	}

	this.share = function(id) {
		var button=$('.feed_share_' + id).addClass('loading'),
		 sh=button.is('.shared'), //{() {&&confirm(langParts['share'])
		 url = 'wall_ajax.php?cmd='+(sh?'unshare':'share')+'&id=' + id + '&wall_uid=' + this.uid;
		$.get(url,function(res) {
                                var obj = jQuery.parseJSON(res),
                                    data = obj.page;
                                    if (obj.status) {
                                        button.removeClass('loading')
                                        if ($this.isAuthOnly(data) == '') {
                                            return false;
                                        }
                                        if ($this.itemExists(id, data)) {
                                            button[sh?'removeClass':'addClass']('shared');
                                        $this.updater();
                                    }
                                }
			}
		);
	}
	this.likeShowHide = function(id, hide) {
		$('#feed_like_result_' + id)[hide?'slideUp':'slideDown'](dur);
		if (hide) $('#feed_like_' + id).show(dur)
	}

	this.likeAdd = function(id) {
		var url = 'wall_ajax.php?cmd=like&id=' + id + '&wall_uid=' + this.uid
		 button=$('#feed_like_' + id).addClass('loading');
		$.get(url, function(res) {
                                  var obj = jQuery.parseJSON(res),
                                      data = obj.page;
                                  if (obj.status) {

					if ($this.isAuthOnly(data) == '') {
						return false;
					}
					if ($this.itemExists(id, data)) {
						$('.wall-info-load-module').html(data);
						// remove button
						button.hide(dur, function(){button.removeClass('loading')});
					}
                                    }
				}
		);
	}

	this.likeDelete = function(id) {
		var el=$('#feed_like_' + id).addClass('loading').show(dur),//$('#feed_like_result_' + id)
		 url = 'wall_ajax.php?cmd=unlike&id=' + id + '&wall_uid=' + this.uid;
		 $.get(url, function(res) {
                            var obj = jQuery.parseJSON(res),
                                data = obj.page;
                                  if (obj.status) {
					if ($this.isAuthOnly(data) == '') {
						el.hide().removeClass('loading')
						return false;
					}
					if ($this.itemExists(id, data)) {
						el.removeClass('loading')
						// show results
						if (trim(data) == '') {
							$this.likeShowHide(id, 1);
						} else {
							$('.wall-info-load-module').html(data);
						}
					}
                                  }
                            }
		);
	}

	this.commentShow = function(id) {
		var block=$('.feed_comment_' + id+':hidden'), inp=$('#wall_comment_area_' + id);
		if (block.length) {
			inp.val('').removeAttr('disabled');
			block.css({opacity:''}).slideDown(dur, function(){inp.focus()})
			if (!inp.data('autosize')) {
				inp.keydown(doOnEnter($this.commentAdd))
				$('a[onclick]', block).removeAttr('onclick')
				 .click(function(){return $this.commentAdd(inp[0])})
			}
			inp.autosize()
		} else {$this.commentAdd(inp[0])}
	}

	this.commentAdd = function(inp) {
		var $inp=$(inp),
		 id=inp.id.replace('wall_comment_area_', ''),
		 url = 'wall_ajax.php?cmd=comment&id='+id + '&last_id='+$this.itemComments[id] + '&wall_uid='+$this.uid;
		 el=$('.feed_comment_' + id).fadeTo(dur, .6),
		 comment = $inp.val();
		if (inp.disabled++) return;
		if (!trim(comment)) {
			$inp.val('').trigger('autosize');
			el.stop().slideUp(dur); return
		};
		var button=$('#wall_item_'+id+' [onclick*="commentShow('+id+')"]').addClass('loading');
        button.removeClass('loading');
		$.post(url, {'comment': comment}, function(res) {
            var obj = jQuery.parseJSON(res),
            data = obj.page;
            if (obj.status) {
                button.removeClass('loading');

                if ($this.isAuthOnly(data) == '') {
                    return false;
                } else {
                    $inp.val('').trigger('autosize');
                    el.slideUp(dur);
                }

                if($this.isBlocked(data)) {
                    return false;
                }

                if ($this.itemExists(id, data)) {
                    // add data to the end item
                    itemId = '#wall_item_comments_' + id;
                    if ($this.itemExists(itemId, data)) {
                        $(trim(data)).css('display', 'none').appendTo(itemId)
                        .not('script').removeAttr('id').slideDown(dur)
                        .not('.feed_bl_chat1').remove();
                        resizeImageWallMobile();
                    }
                }
            }
		});
		return false;
	}

	this.commentDelete = function(id, cid) {
		var el=$('.wall_item_comment_' + cid).addClass('loading');
		var url = 'wall_ajax.php?cmd=comment_delete&id='+id+'&cid='+cid
		 +'&last_id='+this.itemComments[id]+'&wall_uid='+this.uid;
        $('.wall_item_comment_' + cid).animate({'height': '0px', 'opacity': 0}, dur, function(){$(this).remove()});
		$.get(url, function(res) {
                var obj = jQuery.parseJSON(res),
                    data = obj.page;
                if (obj.status) {
                    if ($this.isAuthOnly(data) == '') {
                        return false;
                    }
                    if($this.isBlocked(data)) {
                        return false;
                    }

                    if ($this.itemExists(id, data)) {
                        // add data to the end item
                        itemId = '#wall_item_comments_' + id;
                        if ($this.itemExists(itemId, data)) { // Вроде как не нужна эта проверка
                            $(trim(data)).css('display', 'none').appendTo(itemId)
                            .not('script').removeAttr('id').slideDown(dur)
                            .not('.feed_bl_chat1').remove();
                        }
                    }
                    el.removeClass('loading')
                }
			}
		);

		return false;
	}

	this.commentDeleteFromPage = function(id, cid) {
		if (!this.commentsCache[id][cid]) return;
		$('.wall_item_comment_' + cid).slideUp(dur, function(){$(this).remove()});
		this.itemsInfo[id].commentsCount--;
		this.commentsCacheRemoveByCid(id, cid);
		this.commentsLoadMoreStatus(id, this.itemsInfo[id].commentsCount);
	}

	this.commentsLoad = function(id) {
		if (this.commentsLoadBlock[id]) {
			return;
		} else {
			this.commentsLoadBlock[id] = true;
		}
		var itemLoadMore = $('.wall_load_more_comments_' + id).addClass('loading');
		var url = 'wall_ajax.php?cmd=comments_load&id=' + id + '&last_id=' + this.itemComments[id] + '&wall_uid=' + this.uid;
		// block comments loading to prevent double load
		$.get(url, function(res) {
            var obj = jQuery.parseJSON(res),
                data = obj.page;
            if (obj.status) {
                itemLoadMore.removeClass('loading');
                if ($this.itemExists(id, data)) {
                    // add data to the end item
                    itemId = '#wall_item_comments_' + id;
                    if ($this.itemExists(itemId, data)) {
                        $(trim(data)).css('display', 'none').appendTo(itemId)
                        .removeAttr('id').not('script').slideDown(dur)
                        .not('.feed_bl_chat1').remove();
                        resizeImageWallMobile();
                    }
                }
                $this.commentsLoadBlock[id] = false;
            }
          }
        );
	}

	this.commentsLoadMoreStatus = function(id, count, isViewed) {
		var itemLoadMore = $('.wall_load_more_comments_' + id),
		q=count-(this.commentsVisible[id]||0);
		if (q>0) {
            if (typeof isViewed != 'undefined' && isViewed != '') {
                if (isViewed == 1) {
                    itemLoadMore.css('font-weight', 'bold');
                } else {
                    itemLoadMore.css('font-weight', 'normal');
                }
            }
            //.stop()
            if (itemLoadMore.fadeIn(dur).data('q')==q) return;
            itemLoadMore.data('q',q);
            if (q > 1){
                itemLoadMore.html($this.loadMoreComments).children('span').html(q);
                /*$('<span>&nbsp;('+q+')</span>').prependTo(itemLoadMore).slideDown(dur).scrollTop(20);
                $('>span',itemLoadMore).eq(1).scrollTop(0).css('margin-bottom', '-22px')
                .stop().slideUp(dur, function(){$(this).remove()});*/
            } else {
                itemLoadMore.html($this.loadMoreCommentOne);
            }
		} else {
			//itemLoadMore.data('q',0);
			itemLoadMore.stop().removeClass('loading').fadeOut(dur);
		}
	}

	this.commentsExistsAdd = function(id) {
		this.commentsExists[id] = true;
	}

	this.commentsExistsRemove = function(id) {
		delete this.commentsExists[id];
	}

	this.isCommentExists = function(id) {
		return (this.commentsExists[id]);
	}

	this.commentsRemoveDoubles = function(id) {
		if(this.commentsRemoveDoublesBlock) {
			return;
		}
		var el=$('.wall_item_comment_' + id)
		if (el.length>1) el.filter('[id]').remove()
	}

	this.commentsCacheAdd = function(id, cid) {
		if (!this.commentsCache[id]) {this.commentsCache[id]={}; this.commentsVisible[id]=0}
		if (this.commentsCache[id][cid]) return;
		this.commentsVisible[id]++;
		this.commentsCache[id][cid] = cid;
		this.itemComments[id]=Math.max(this.itemComments[id]||0, cid)
	}

	this.commentsCacheRemoveByCid = function(id, cid) {
		if (!this.commentsCache[id][cid]) return
		delete this.commentsCache[id][cid];
		this.commentsVisible[id]--;
	}

	this.commentsCacheRemoveById = function(id) {
		delete this.commentsCache[id];
	}

	this.commentsAttach = function(id) {
		$itemId = $('#wall_item_comments_' + id);
		$('.wall_item_comment_attach_' + id+'>*:not(script)').css({display:'none'})
		 .removeAttr('id').appendTo($itemId).slideDown(dur)
		$('.wall_item_comment_attach_' + id).remove();
	}

	this.itemAdd = function() {
		var comment=$.trim($('#wall_item_add').val());
		if ($this.isUploadImage||(!comment&&!$this.isImageLoaded)) return false;
		$this.setPostIdMax($this.firstPostId);

		comment=emojiToHtml(comment);
		var url = 'wall_ajax.php?cmd=item&last_item_id='+$this.getPostIdMax()+'&wall_uid='+$this.uid;
		$('#wall_item_add').val('').triggerHandler('autosize');
		$.post( url, {'comment': comment, 'image_upload' : $this.isImageLoaded}, function(res) {
                    var obj = jQuery.parseJSON(res),
                        data = obj.page;
                    if (obj.status) {
                        if ($this.isAuthOnly(data) == '') {
                            return false;
                        }

                        if($this.isBlocked(data)) {
                            return false;
                        }

                        if (trim(data) != 'empty comment') {
                            $this.updater(res);
                        }
                    }
                    // clearImage();
		});
		$('.icon_photo_wall').css('display', 'block');
		$('.icon_photo_upload_wall').css('display', 'none');
		Wall.isUploadImage = false;
		Wall.isImageLoaded = false;
		return false;
	}

	this.unfriend = function(uid) {
		var url = 'wall_ajax.php?cmd=unfriend&friend_id=' + uid +'&wall_uid='+this.uid;
		$.get(url, function(res) {
                    var obj = jQuery.parseJSON(res),
                        data = obj.page;
                        if (obj.status && data == 'ok') {
                            location.reload();
                        }

                   }
		);
		return false;
	}

	this.itemDelete = function(id) {
		//var el=$('#wall_item_' + id+'>*');//.addClass('loading');
		var url = 'wall_ajax.php?cmd=item_delete&id=' + id +'&wall_uid='+this.uid;
        $this.itemExists(id, 'deleted');
		$.get(url, function(res) {
                    var obj = jQuery.parseJSON(res),
                        data = obj.page;
                    if (obj.status) {
                        $this.isAuthOnly(data);
                        /*if ($this.isAuthOnly(data)) {
                            $this.itemExists(id, trim(data))
                        }// else {el.removeClass('loading')}*/
                    }
            }
		);

		return false;
	}

	this.itemExists = function(id, value) {

		exists = true;

		if (value=='not exists'||value=='deleted') {
			var el=$('#wall_item_' + id);
			if (!el[0]) return exists=false
			if (value=='deleted')
			 $(('.feed_share_'+(el[0]||{}).className).replace('wall_shared_', ''))
			 .removeClass('shared').show(dur); // показываем кнопку "поделиться" у оригинала, если удалена зашареная запись
			$('>*', el).css({paddingBottom:0, paddingTop:0, borderWidth:0})
            $('>*>*', el).animate({'height': '0px', 'opacity': 0}, dur, function(){el.remove()});
			//$('>*>*', el).slideUp(dur, function(){el.remove()});
			$this.itemInfoDelete(id);
			exists = false;
		}

		return exists;
	}

	this.itemsHistoryLoad = function() {
		this.setPostIdMax(this.firstPostId);
		$('#load_animation').slideDown(parseInt(dur/3));
		var url = 'wall_ajax.php?cmd=items_old&id=' + this.lastPostId + '&wall_uid=' + this.uid + '&is_see=' + this.OnlySeeFriends;
		$.get( url, function(res) {
            var obj = jQuery.parseJSON(res),
                data = obj.page;
            if (obj.status) {
                if (data=trim(data)) {
                    $('>td, >th', data=$(data))
                    .wrapInner('<div style="display:none" class="wr_in" />')
                    .css({paddingBottom:0, paddingTop:0, borderWidth:0});
                    $('#wall_items_table > tbody').append(data);
                    $('script', '#wall_items_table').remove();
                    $('.feed_bl_chat1').removeAttr('id')

                    var t=dur, i=0;
                    data=data.filter('tr');
                    (function fu(){
                        $this.scrollBlock=!!$('.wr_in', data.eq(i))
                        .scrollTop(1000).slideDown(t*=.8, fu)[0];
                        $('>td, >th', data.eq(i++)).css({padding:'', borderWidth:''});
                        resizeImageWallMobile();
                    })()
                }
                $('#load_animation').slideUp(dur);
            }
		});
	}

	this.itemsHistoryLoader = function() {
		$(window).scroll(function() {
			if ($this.oldItemsExists && !$this.scrollBlock && ($(window).scrollTop() > $(document).height() - $(window).height() - $this.scrollDelta)) {
				//var d =$(document).height() - $(window).height();
                                //console.log('scroll-'+$(window).scrollTop()+' delta-'+d);
                                $this.scrollBlock = true;
				$this.itemsHistoryLoad();
			}
			(function fu() {
			  if ($(window).scrollTop()<$('#wall_items_table').offset().top
				&& !$('.wr_in:animated')[0]){
					var tr=$('#wall_items_table>tbody>tr:hidden:last').show();
					$('.wr_in', tr).slideDown(dur, fu).scrollTop(1000)
				}
			})()
		});
	}

	this.updater = function(data0) {

		if(this.singleItemMode) {
			return;
		}

		function upd(res) {
                    if (res != '') {
                    var obj = jQuery.parseJSON(res),
                        data = obj.page,
                        status = obj.status;
                    } else {
                        data = '',
                        status = 1;
                    }
                    if (status) {
                        //console.log($this.OnlySeeFriends);
                         if (data == 'no'){
                            location.reload(true);
                            return;
                        }
                        if (data=trim(data)) {
				data = $(data);
				data.each(function(i){
					if (this.id&&$('#'+this.id)[0]) data.splice(i,1)
				})
				$('>td, >th', data)
				 .wrapInner('<div style="display:none" class="wr_in" />')
				 .css({paddingBottom:0, paddingTop:0, borderWidth:0});
				$(data).css('display', 'none')
				$('#wall_items_table > tbody').prepend(data);
                resizeImageWallMobile();
				$('>td, >th', data).css({padding:'', border:''})
				$('script', '#wall_items_table').remove();
				data.not('tr').remove();
				if ($(window).scrollTop()<($('#wall_items_table').offset().top ||0)) {
					$('.wr_in', data.show()).slideDown(dur).scrollTop(1000);
				}

			};

			if (!data0) $this.updaterInit()
                    }
                }


		if (data0) {upd(data0); return }
		clearTimeout(this.updaterTimer);
		this.setPostIdMax(this.firstPostId);

		var url='wall_ajax.php?cmd=update&last_item_id='+this.getPostIdMax()+'&wall_uid='+this.uid;
		$.post(url, {
			'items': this.itemsInfo,
			'comments': this.commentsCache,
                        'is_see' : this.OnlySeeFriends
		}, upd);
	}

	this.updaterInit = function() {
		this.singleItemMode = false;
		clearTimeout($this.updaterTimer);
		$this.updaterTimer = setTimeout(
				function() {
					$this.updater();
				},
				$this.autoUpdateTimeout);
	}

	this.isAuthOnly = function(value) {
		if (value == 'please_login') {
			location.href = 'join.php?cmd=please_login';
			value = '';
		}

		return value;
	}

	this.itemInfoSet = function(id, like, comment, commentsCount, isViewed) {
		var info=this.itemsInfo[id] = (this.itemsInfo[id] || {})
		info.like=(like||info.like);
		info.comment=(comment||info.comment);
		info.commentsCount=(commentsCount||info.commentsCount);
		this.commentsLoadMoreStatus(id, info.commentsCount, isViewed);
	}

	this.itemInfoDelete = function(id) {
		delete this.itemsInfo[id];
		this.commentsCacheRemoveById(id);
	}

	this.itemCommentsSet = function(id, cid) {
		this.itemComments[id] = cid;
	}

	this.isBlocked = function(value) {
		if(trim(value) == 'you_are_blocked') {
			alert(MSG_YOU_ARE_IN_BLOCK_LIST);
			return true;
		}
		return false;
	}
    function resizeImageWallMobile(offset) {
        var header = $('#hdr'),
            offset = offset || 75;
        if  (header.length) {
            var newWidth = header.width()-offset;
            $('.image_comment').css('max-width', newWidth+'px');
            $('.feed_title_cmt').css('width', newWidth+'px');
        }
    }

    function clearImage() {
        $.post('wall_image_upload_ajax.php',
               {action: 'delete'}, function(data){
                    if (data == 'ok') {
                        $('.icon_photo_wall').css('display', 'block');
                        $('.icon_photo_upload_wall').css('display', 'none');
                        Wall.isUploadImage = false;
                        Wall.isImageLoaded = false;
                    }
        });
    }

	$(function(){
		$('#wall_item_add').keydown(doOnEnter($this.itemAdd)).autosize();
        resizeImageWallMobile();
        $(window).resize(function(e){
            resizeImageWallMobile();
        });

        if (Wall.isImageUpload) {
        $('#wall_upload_image').fileapi({
            url: 'wall_image_upload_ajax.php?action=upload',
            multiple: false,
            accept: 'image/*',
            maxSize: Wall.maxFileSizeImageUpload * FileAPI.MB,
            autoUpload: true,
            elements: {
                ctrl: { upload: '.icon_photo_wall'},
                active: {show: '.preloader_css', hide: '.icon_photo_wall' }
            },
            onSelect: function (evt, data){
                if (data.other.length){
                    var errors = data.other[0].errors;
                    if (errors){
                        if (errors.maxSize) {
                            alert(wallLangParts['file_exceeds_the_allowable_size']);
                        }
                    }
                } else {
                    Wall.isUploadImage = true;
                }
            },
            onComplete: function (evt, uiEvt){
                var error = uiEvt.error,
                    result = uiEvt.result,
                    error_upload = result.error_upload;
                if (error == false && error_upload == false) {
                    $('.icon_photo_wall').css('display', 'none');
                    $('.icon_photo_upload_wall').css('display', 'block');
                    Wall.isUploadImage = false;
                    Wall.isImageLoaded = true;
                } else {
                    alert(wallLangParts['error_loading_file']);
                };
            }
        });
        }
        $('.icon_photo_upload_wall').click(function(){
            if (confirm(wallLangParts['are_you_sure'])) {
                clearImage();
            } else {
                return false;
            }
        });
	})
	return this;
}
$(function(){
	$('#wall_items_table').mousedown(function(e){
		if (e.target.tagName=='BUTTON') setTimeout(function(){e.target.blur()},0)
	});
	$('.feed_bl_chat1').removeAttr('id');

    $('body').on('click', '.feed_del', function(e){
        $(this).focus().parent('.bl_post_popup').children('.post_popup').stop().animate({height: 'toggle'}, 350);
        return false;
    }).on('focusout', '.feed_del', function(){
        var popup = $(this).parent('.bl_post_popup').children('.post_popup');
        if (popup.css('display') == 'block') {
            setTimeout(function(){popup.stop().animate({height: 'toggle'}, 350)}, 120);
        }
        return false;
    });
})
