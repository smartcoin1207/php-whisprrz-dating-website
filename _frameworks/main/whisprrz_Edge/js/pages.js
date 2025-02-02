var CPages = function(guid) {

    var $this=this;
    this.guid=guid;
    this.prevSearchQuery='';
    this.guestRemove = false;

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
        }
    }

    this.setDisabledLink = function(page){
        page=page||1;
        if(!$('.pagination-container')[0])return;
        $('.pagination-container a.disabled').addClass('dis_link');
        $('.pagination-container a.pagination-active').toggleClass('pagination-active cur_link');
        $('.pagination-container .pagination-inner a[data-value='+page+']').addClass('pagination-active load_link');
        $('.pagination-container a').addClass('disabled');
    }

    this.setResetDisabledLink = function(){
        if(!$('.pagination-container')[0])return;
        $('.pagination-container a.cur_link').toggleClass('pagination-active cur_link');
        $('.pagination-container .pagination-inner a.load_link').removeClass('pagination-active load_link');
        $('.pagination-container a:not(.dis_link)').removeClass('disabled');
    }

    this.initList = function(){
        $(function(){
            //$this.replaceUrl();

            $('body').on('click', '.pagination-container a.go', function(e){
                var $el=$(this);
                if ($el.is('.go')) {
                    if($el.is('.disabled'))return false;
                    var page=$el.data('value');
                    if ($this.filter) {
                        if($jq('#filter_no_result').is('.to_show')){
                            $this.$searchQuery.val($this.prevSearchQuery);
                            $jq('#filter_no_result').removeClass('to_show');
                        }
                        $jq('#filter_results_page')[0].value=page;
                    }
                    $this.listLoad(page);
                    return false;
                }
            })

            //$jq('#filter_results_type_order').change($this.listLoad)
            $this.$controls=$('input[type=text], select, button', '#filter_results');
            $this.$btnSearch=$('#filter_results_search').click(function(){
                $this.listLoad(1,true);
                return false;
            });

            $('.events_guest_users_button').click(function() {
                var guestDataUid = $(this).data('uid');
                
                if(guestDataUid) {
                    console.log(guestDataUid)
                    $this.listLoad(1,true, true, true, guestDataUid, $(this).data('cmd'));
                }
            });

            $win.on('resize',function(){
                setTimeout($this.resizeList,10)
            })

            $doc.on('click', function(e){
                var $el=$(e.target);
                if (!$el.is('.bl_filter')&&!$el.closest('.bl_filter')[0])$this.closeFilter();
            })

            $this.$searchQuery=[];
            if ($this.filter) {
                $this.$searchQuery=$jq('#filter_results_search_query');
                $this.$filterFields=$jq('[name]', '#filter_results').add('.filter_field_add').each(function(){
                    var val=this.value;
                    if(this.name=='only_friends' || this.name=='only_live'){
                        val=$(this).prop('checked')?1:0;
                    }
                    $this[this.name] = val;
                })

                $('#filter_results_tags, #filter_results_search_query').keydown(doOnEnter(function(){
                    $this.$btnSearch.click();
                }))
            }
        })
    }

    //Popcorn modified 2025-01-22
    this.replaceUrl = function(page){
        var param = '';
        if (page && page!=1) {
            param = (~$this.urlPage.indexOf('?') ? '&' : '?') + $this.param+'='+page;
        }
        $this.page=page;

        var pagesSeo=['blogs', 'photos', 'videos', 'friends', 'groups_subscribers', 'group_block_list', 'lives', 'songs'];
        if (isSiteOptionActive('seo_friendly_urls') && in_array($this.pageType, pagesSeo)) {
            param = '';
            if (page && page != 1) {
                param = '/' + page;
            }
        }
        /*if ($this.$searchQuery[0]) {
            var query=trim($this.$searchQuery.val());
            if (query) {
                param += (~param.indexOf('?') ? '&' : '?') + 'search_query='+encodeURIComponent(query);
            }
        }*/

        var queryString = window.location.search;
        var queryStringWithoutQuestionMark = window.location.search.substring(1);
        var replaceUrlPre = $this.urlPage + param + (queryStringWithoutQuestionMark ?  "?" + queryStringWithoutQuestionMark : "");

        if ($this.urlPage.includes(queryStringWithoutQuestionMark)) {
            replaceUrlPre = $this.urlPage + param;
        } else {
            if ($this.urlPage.includes("?")) {
                // Use "&" if "?" is already present 
                replaceUrlPre += param + (queryStringWithoutQuestionMark ? "&" + queryStringWithoutQuestionMark : "");
            } else {
                // Use "?" if no "?" is present
                replaceUrlPre += param + (queryStringWithoutQuestionMark ? "?" + queryStringWithoutQuestionMark : "");
            }
        }

        replaceUrl(replaceUrlPre);
    }

    this.closeFilter = function(){
        if($jq('#filter_results').is('.in'))$jq('#filter_open').click();
    }

    this.resizeList = function(){
        // $('.module_filter_result').height(Math.max(200, $('.filter_result:last').height()))
    }

    this.initLoadList= function(){
        $('.item.to_hide', '.filter_result:hidden').toggleClass('to_hide to_show');//??? Not
        $('.filter_result:hidden').css({opacity:0}).show().css({opacity:1,transition:'opacity .3s linear'});//.fadeIn(300);
        $this.resizeList();
    }

    this.myFriendsReload = function(){
        if($this.isMyFriend!=undefined&&$this.isMyFriend){
            $this.listLoad(0);
        }
    }

    this.isFriendsPage = function(){
        return $this.pageType=='friends';
    }

    this.pageFriendsReloadCheckUser = function(uid){
        uid=uid||0;
        if(!$this.isFriendsPage()||!uid||!$('.list_user_item_photo_'+uid, '.filter_result')[0])return;

        $this.listLoad(0);
    }

    this.groupsSubscribersReload = function(){
        if($this.pageType == 'groups_subscribers' && $this.groupId){
            $this.listLoad(0);
        }
    }

    this.reloadListUser = function(){
        var page=$this.page,
            num=$('.users_list_item').length-1;//,
        if(num==0&&page){
            page--;
        }
        $this.listLoad(page);
    }

    this.isPageMyMediaList = function(type){//videos - photos
        type=type||$this.pageType;
        if ($this.pageType != type) {
            return false;
        }
        if ($this.uid) {
            if(is_nsc_couple_page == 1) {
                return nsc_couple_id == $this.uid;
            } else {
                return $this.guid == $this.uid;
            }
        } else {
            var option='show_your_photo_browse_photos';
            if(type=='videos'){
                option='show_your_video_browse_videos';
            }else if(type=='songs'){
                option='show_your_song_browse_songs';
            }
            return isSiteOptionActive(option, 'edge_member_settings');
        }
    }

    this.myPageReload = function(type,noRedirect,toMyMedia, groupId, photo_offset = ''){
        groupId=groupId||0;
        groupId *=1;
        type=type||$this.pageType;
        noRedirect=noRedirect||false;
        toMyMedia=toMyMedia||false;
        if(!type)return false;

        if(($this.isPageMyMediaList(type) && !siteGroupViewList) && !toMyMedia){
            if(siteGroupViewList)return false;
            if($this.filter) $jq('#filter_results_page')[0].value=1;
            $this.listLoad(1,false,true)
        }else{
            var url='';
            if (type == 'videos') {
                if(groupId && urlPagesSite['group_vids_list_'+groupId]){
                    url=urlPagesSite['group_vids_list_'+groupId];
                } else {
                    url=urlPagesSite.my_vids_list;
                }
            } else if(type == 'photos'){
                if(groupId && urlPagesSite['group_photos_list_'+groupId]){
                    url=urlPagesSite['group_photos_list_'+groupId];
                } else {
                    let paramUrl = new URL(urlPagesSite.my_photos_list);

                    if (photo_offset) {
                        paramUrl.searchParams.set('offset', photo_offset);
                    }
                    url=paramUrl.toString();
                }
            } else if(type == 'songs'){
                if(groupId && urlPagesSite['group_songs_list_'+groupId]){
                    url=urlPagesSite['group_songs_list_'+groupId];
                } else {
                    url=urlPagesSite.my_songs_list;
                }
            }
            if(url&&!noRedirect&&!isPageMyWall){
                redirectUrl(url);
                return true;
            }
        }
        return false;
    }

    this.pageReload = function(page, showBtnLoader, alwaysLoad, noScroll){
        if ($this.filter) $jq('#filter_results_page')[0].value=page;
        $this.listLoad(page, showBtnLoader, alwaysLoad, noScroll);
    }

    this.setOrder = function(order,title){
        $jq('#page_list_title').text(title);
        $jq('#filter_results_page')[0].value=1;
        $jq('#filter_results_type_order').val(order).selectpicker('refresh');
        $this.listLoad(1,true,true);
    }

    this.listLoad = function(page, showBtnLoader, alwaysLoad, noScroll, guestRemoveUid=0, cmd=''){
        var isChange=false, data={};
        showBtnLoader=showBtnLoader||false;
        alwaysLoad=alwaysLoad||false;
        noScroll=noScroll||false;
        data[$this.param]=page;
        data['group_id']=$this.groupId;
        if ($this.groupType) {
            data['group_id']='groups_photo_all';
            data['view']=$this.groupType;
        }

        if ($this.filter) {
            $this.$filterFields.each(function(){
                var val=this.value;
                if(this.name=='only_friends' || this.name=='only_live'){
                    val=$(this).prop('checked')?1:0;
                }else if (this.id == 'filter_results_search_query' || this.id == 'filter_results_tags') {
                    val=trim(val);
                    $(this).val(val);
                }
                if (isChange && this.name == $this.param){
                    return true;
                }
                data[this.name]=val;
                if($this[this.name] != val){
                    if (this.name != $this.param) {
                        data[$this.param]=1;
                    }
                    isChange = true;
                }
            })
            $this.closeFilter();
            if (!isChange&&!alwaysLoad) return;
            $jq('#filter_results_page')[0].value=data[$this.param];
            $this.setData(data);
        } else {
            $this.setData(data);
        }


        $this.setDisabledLink(page);
        if(!noScroll){
            $this.replaceUrl(data[$this.param]);
            clMediaTools.scrollTop();
        }

        if(guestRemoveUid) {
            //popcorn modified 2024-05-24
            data['cmd'] = cmd;
            data['guest_user_id'] = guestRemoveUid;
        }

        $jq('#loader_search_list').toggleClass('to_hide to_show');
        //$jq('#filter_no_result').removeClass('to_show');
        var items0=$('.module_filter_result>*').not('#loader_search_list').css({opacity:.3,transition:'opacity .3s linear'});

        if(showBtnLoader)$this.$btnSearch.addChildrenLoader();
        $this.$controls.prop('disabled', true);
        data['ajax'] = 1;

        ehp_photo_pages = ['event_photo_list.php', 'hotdate_photo_list.php', 'partyhou_photo_list.php'];
        page_url = location.pathname;

        if(page_url && ehp_photo_pages.some(item => page_url.includes(item))) {
             url = location.href;
            const urlParams = new URLSearchParams(new URL(url).search);
            const user_id = urlParams.get('user_id');
            if(user_id) {
                data['user_id'] = user_id;
            }
        }

        var params = new URLSearchParams(window.location.search);
        var offset = params.get('offset');
        data['offset'] = offset;

        $.post($this.urlPage, data, function(data){
            if(~$this.urlPage.indexOf('users_viewed_me')){

            }else{
                data=checkDataAjax(data);
            }
            if(data!==false){
                var dataBlocks = {'.pages' : '.paging'},
                $data=$(data);
                if ($this.filter) {
                    var searchQuery=trim($this.$searchQuery.val());
                    if ($this.$searchQuery.val() && $data.find('.bl_no_one')[0]) {
                        $jq('span','#filter_no_result').text(searchQuery);
                        $jq('#filter_no_result').addClass('to_show');
                        items0.fadeTo(300,1);
                        $jq('#loader_search_list').toggleClass('to_show to_hide');
                        $this.$btnSearch.removeChildrenLoader();
                        $this.$controls.prop('disabled', false);
                        return;
                    }
                    $jq('#filter_no_result').removeClass('to_show');
                    $this.prevSearchQuery=searchQuery;
                }

                var items=$('>*', $data.filter('.items')).hide();
                items0.oneTransEnd(function(){
                    items0.remove();
                }).css({opacity:0,transition:'opacity .3s linear'})
                items.appendTo('.module_filter_result');
                setTimeout(function(){
                    insertFromDataHtmlToHtml(data, dataBlocks);
                    $jq('#loader_search_list').toggleClass('to_show to_hide');
                },100);
            }else{
                $this.setResetDisabledLink();
                items0.fadeTo(300,1);
                $jq('#loader_search_list').toggleClass('to_show to_hide');
                alertServerError();
            }
            $this.$btnSearch.removeChildrenLoader();
            $this.$controls.prop('disabled', false);

            $this.initList();
        })
    }

    $(function(){
    })
    return this;
}