var CSearch = function() {

    var $this=this;
    this.page='';

    this.setData = function(data){
        for (var key in data) {
           $this[key] = data[key];
        }
    }

    this.getTitleRadius = function(radius) {
        $jq('#radius').val(radius);
        var index = 'city';
        if(radius > 0) {
            index = 'radius';
        }
        if(isMaxFilterDistanceCountry && radius >= maxRadius) {
            index = 'country';
        }
        return sliderTitles[index].replace('%1', radius);
    }

    this.setAttrRadius = function(attr, value) {
        $this.$radius[0] && $this.$radius.bootstrapSlider('setAttribute',attr,value);
    }

    this.prepareSearchPeople = function(){
        if(!$this.$peopleNearby[0])return;
        if($this.$peopleNearby.val()*1){
            $this.$regionControl.closest('.field').hide();
            $this.showRadiusBl(true);
        };
    }

    this.showRadiusBl= function(val){
        if(!$this.$radiusBl[0])return;
        $this.$radiusBl[val?'show':'hide'](1,function(){
            $this.$radius.change();
        })
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

    this.offset=1;
    this.initList = function(){
        $(function(){
            $('body').on('click', '.pagination-container a.go', function(e){
                var $el=$(this);
                if ($el.is('.go')) {
                    if($el.is('.disabled'))return false;
                    $this.listLoad($el.data('value'));
                    return false;
                }
            })

            $jq('#search_submit').click(function(){$this.listLoad()})

            $win.on('resize',$this.resizeList)

            $doc.on('click', function(e){
                var $el=$(e.target);
                if (!$el.is('.bl_filter')
					&& !$el.closest('.bl_filter')[0]
					&& !$el.is('.custom_modal_alert')
					&& !$el.closest('.custom_modal_alert')[0]
					) {
					$this.closeFilter();
				}
            })

            $this.searchParams = '';
            if (!$jq('#filter_results')[0]) {
                return;
            }

            $jq('#global_search_by_username').keydown(function(e){
                if (e.keyCode == 13 && this.value) {
                    $jq('#search_submit').click();
                    return false;
                }
            })

            $this.$radius=$('#radius_slider');
            if ($this.$radius[0]) {
                $this.$radiusBl=$('.radius_slider');
                $this.$radius.bootstrapSlider({
                    tooltip: 'hide',
                    max: maxRadius,
                    value: currentRadius,
                    formatter:$this.getTitleRadius
                })
            }

            $this.$peopleNearby=$jq('#people_nearby');

            if ($this.$peopleNearby[0]){
                $this.$regionControl=$jq('#filter_state, #filter_city');
                $this.prepareSearchPeople();
                var vP=$this.$peopleNearby.val()*1;
                if (vP) {
                    $jq('#filter_country').val('people_nearby').selectpicker('refresh')
                } else if($jq('#filter_country').val()=='people_nearby'){
                    $jq('#filter_country').val(0).selectpicker('refresh')
                }

                /*if(!($this.$peopleNearby.val()*1)&&$jq('#filter_country').val()=='people_nearby'){
                    $jq('#filter_country').val(0).selectpicker('refresh')
                }*/
                if (!($jq('#filter_country').val()*1)) {
                    $this.$regionControl.closest('.field').hide();
                }

                $jq('#filter_city').change(function(){
                    if($this.$peopleNearby.val()*1)return;
                    var val=this.value*1;
                    $this.showRadiusBl(val);
                }).change();
            }

            $this.searchParams = $this.getSearchParams();

            $jq('#filter_results').on('hide.bs.collapse',function(){
                $this.setAttrRadius('value',$jq('#radius').val());
                $this.setAttrRadius('tooltip','hide');
                $this.$radius[0] && $this.$radius.bootstrapSlider('refresh');
            }).on('show.bs.collapse',function(){
                $this.setAttrRadius('value',$jq('#radius').val());
            }).on('shown.bs.collapse',function(){
                $this.setAttrRadius('tooltip','always');
                $this.$radius[0] && $this.$radius.bootstrapSlider('refresh');
            })

            $('.geo', '#filter_results').change(function() {
                var $el=$(this);
                if (!$el.is('select'))return;
                var type=$el.data('location');
                if ($this.$peopleNearby[0]) {
                    if(type=='geo_states' && this.value == 'people_nearby'){
                        $this.$peopleNearby.val(1);
                        $this.prepareSearchPeople();
                        return;
                    }
                    $this.$peopleNearby.val(0);
                    $this.$regionControl.closest('.field').show();
                }
                $this.showRadiusBl(false);
                if (this.id == 'filter_country' && this.value == 0) {
                    $this.$regionControl.closest('.field').hide();
                    $jq('#filter_state').html('<option value="0">'+l('all_regions')+'</option>').selectpicker('refresh');
                    $jq('#filter_city').html('<option value="0">'+l('all_cities')+'</option>').selectpicker('refresh');
                    return;
                }

                $.ajax({type: 'POST',
                        url: url_ajax,
                        data: {cmd:type,
                               select_id:this.value,
                               filter:'1',
                               list: 0},
                        beforeSend: function(){
                            $jq('#search_submit, .location').prop('disabled', true)
                        },
                        success: function(res){
                            $jq('#search_submit, .location').prop('disabled', false);
                            var data=checkDataAjax(res);
                            if (data) {
                                var option='<option value="0">'+l('all_cities')+'</option>';
                                switch (type) {
                                    case 'geo_states':
                                        $jq('#filter_state').html('<option value="0">'+l('all_regions')+'</option>' + data.list).selectpicker('refresh');
                                        $jq('#filter_city').html(option).selectpicker('refresh');
                                        break
                                    case 'geo_cities':
                                        $jq('#filter_city').html(option + data.list).selectpicker('refresh');
                                        break
                                }
                            }
                        }
                })
                return false;
            })
        })
    }

    this.setPage = function(page){
        $this.page=page||'';
    }

    this.getPage = function(){
        return $this.page||urlPagesSite.search_results;
    }

    this.replaceUrl = function(){
        var params='',pageUrl=$this.getPage();
        if ($this.offset && $this.offset!=1) {
            params = (~pageUrl.indexOf('?') ? '&' : '?') + 'offset='+$this.offset;
        }
        //var params=$this.offset?'?offset='+$this.offset:'';
        replaceUrl($this.getPage()+params);
    }

    this.closeFilter = function(){
        if($jq('#filter_results').is('.in'))$jq('#filter_open').click();
    }

    this.resizeList = function(){
        $('.module_filter_result').height(Math.max(200, $('.filter_result:last').height()))
    }

    this.initLoadList = function(){
        $('.item.to_hide', '.filter_result:hidden').toggleClass('to_hide to_show');//??? Not
        $('.filter_result:hidden').css({opacity:0}).show().css({opacity:1,transition:'opacity .3s linear'});//.fadeIn(300);
        $this.resizeList();
    }

    this.getSearchParams =  function() {
        if (!$jq('#filter_results')[0]) {
            return '';
        }
        var searchParams = $jq('#filter_results').find('input[name],select[name]').map(function () {
            return $(this).val().trim() == "" ? null : this;
        }).serialize();

        var filterParamStatus = $jq('#search_status').val(),
        filterParamStatusStart = '';
        if(filterParamStatus) {
            if(searchParams) {
                filterParamStatusStart = '&';
            }
            searchParams += filterParamStatusStart + 'status=' + filterParamStatus;
        }

        filterParamStatusStart = '';
        if(searchParams) {
            filterParamStatusStart = '&';
        }

        searchParams += filterParamStatusStart + 'with_photo=' + $('#module_search_with_photo').prop('checked')*1;
        return searchParams;
    }


    this.listLoad = function(offset){
        offset=offset||$this.offset;

        var params=$this.getSearchParams(),
            isChange=$this.searchParams!=params || offset!=$this.offset;

        $this.closeFilter();

        if (isChange) {
            $this.setDisabledLink(offset);
            if ($this.searchParams!=params) {
                offset='';
                $this.searchParams=params;
            }
            $this.offset=offset;
        } else {
            return;
        }

        $this.replaceUrl();
        clMediaTools.scrollTop();
        $jq('#loader_search_list').toggleClass('to_hide to_show');
        var items0=$('.module_filter_result>*').not('#loader_search_list').css({opacity:.3,transition:'opacity .3s linear'});

        if($this.offset)params +='&offset='+$this.offset;
        $.post($this.getPage() + '?' + params, 'ajax=1&upload_search_page=1&set_filter=1', function(data){
            var dataBlocks = {'.pages' : '.paging'},
                items=$('>*', $(data).filter('.items')).hide();
			//items0.delay(200).fadeTo(200, 0, function(){items0.remove()});
            items.appendTo('.module_filter_result');
            items0.oneTransEnd(function(){
                items0.remove();
            }).css({opacity:0,transition:'opacity .3s linear'})
            setTimeout(function(){
                insertFromDataHtmlToHtml(data, dataBlocks);
                $jq('#loader_search_list').toggleClass('to_show to_hide');
            },100);
            //$this.setResetDisabledLink();
        })
    }

    $(function(){

    })
    return this;
}