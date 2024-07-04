var CUpgrade = function(requestUri) {

    var $this=this;

    this.requestUri=requestUri;
    this.langParts={};

    this.isAction=false;

    this.initChoiceSystem = function(boost){
        boost=boost?'_boost':'';
        var dur=250;
        $('.frame', '#switch_paymant'+boost).on('mousedown touchstart', function(e){
            var $el=$(this), id=$el.data('payment'),
                $vis=$('.cont_pp_profile_upgraded_item'+boost+':visible');
            if(id==$vis[0].id)return;
            $vis.stop().fadeOut(dur,function(){
                $vis.removeClass('show');
                $('#'+id).stop().fadeIn(dur);
            })
            $jq('#switch_paymant_arrow'+boost).css({top:($el.parent('.item')[0].offsetTop+12)});
        })
    }

    this.initReady = function(){
        $('.upgrade_radio_styled:checked').change();

        $('.upgrade_package .items .item').each(function() {
            $(this).click(function() {
                $('.upgrade_package .items .item').removeClass('selected').find('a').removeClass('checked');
                $(this).addClass('selected').find('a').addClass('checked');
            })
        })

        $this.initChoiceSystem();

        $('.pp_upgraded').on('click', function(e){
            if (!$this.isAction && this==e.target) {
                $this.closePopupPaymentSystem();
            }
        })
    }

    this.showPopupPaymentSystem = function(boost){
        boost=boost?'_boost':'';
        var prf=boost?'Boost':'';

        var item=$this['selectedPaymentPlanData'+prf]['item'],id='';
        if(!item) {
            return;
        }

        if(typeof inAppPurchaseProducts === 'object') {
            $(boost ? '#pp_payment_proceed_boost' : '.btn_submit_upgrade').prop('disabled', true).addChildrenLoader();
            MobileApp.inAppPurchaseBuy(inAppPurchaseProducts[item]['id']);
            return;
        }

        $('.cont_pp_profile_upgraded_item'+boost).hide().each(function(){
            if($this['availablePaymentSystemToPlan'+prf][item].indexOf(this.id)===-1){
                $('#payment_'+this.id).hide();
            }else{
                if(!id){
                    $('#'+this.id).show();
                    id=this.id;
                }
                $('#payment_'+this.id).show();
            }
        })
        if(id){
            setTimeout(function(){
                var top=$('#payment_'+id, '#switch_paymant'+boost).first()[0].offsetTop+12,
                    topArrow=parseInt($jq('#switch_paymant_arrow'+boost).css('top'));
                if(top!=topArrow){
                    $jq('#switch_paymant_arrow'+boost).oneTransEnd(function(){
                        $(this).css('transition','all .5s');
                    }).css({top:top,transition:'all .01s'})
                }
            },10)
        }
        $('#pp_payment_plan_name'+boost).html($this['selectedPaymentPlanData'+prf].name);
        $('#pp_payment_total_price'+boost).html($this['selectedPaymentPlanData'+prf].total_price);

        if (boost) {
            var $pp=openPopupList['#pp_boost_ajax']['el'];
            $pp.find('.cont_boost_system').show().delay(5).oneTransEnd(function(){
				$jq('body').addClass('pp_boost_ajax_system_open');
			}).removeClass('to_hide',0);
			setPushStateHistory('pp_boost_ajax_system');
        } else {
            setPushStateHistory('upgrade');
            $jq('#pp_payment')
            .one('show.bs.modal',function(){
                $jq('body').addClass('pp_upgrade_open');
            })
            .one('hide.bs.modal',function(){
                $jq('body').removeClass('pp_upgrade_open');
            })
            .one('hidden.bs.modal',function(){
                checkOpenModal();
            }).modal('show');
        }
    }

    this.closePopupPaymentSystem = function(){
        if(!$jq('body').is('.pp_upgrade_open'))return;
        if(!backStateHistory()){
            $this.closePopup();
        }
    }

    this.closePopup = function(){
        $jq('#pp_payment').modal('hide');
    }


    this.selectedPaymentPlanData={};
    this.selectedPaymentPlanDataBoost={};
    this.selectedPaymentPlan = function(item, name, totalPrice, boost){
        boost=boost?'Boost':'';
        $this['selectedPaymentPlanData'+boost]={item:item,name:name,total_price:totalPrice};
    }

    this.availablePaymentSystemToPlan={};
    this.availablePaymentSystemToPlanBoost={};
    this.setAvailablePaymentSystemToPlan = function(item, data, boost){
        boost=boost?'Boost':'';
        $this['availablePaymentSystemToPlan'+boost][item]=data;
    }

    this.showErrorProfileUpgrade = function(boost){
        boost=boost?'_boost':'';
        $this.isAction=true;
        $('#switch_paymant'+boost).addClass('to_hide');
        $('#profile_upgraded_system'+boost).oneTransEnd(function(){
            $('#profile_upgraded_system'+boost).hide();
            $('#profile_upgraded_error'+boost).fadeIn(250);
        }).addClass('to_hide');
    }

    this.contactFrmShow = function(){
        $jq('#pp_payment').one('hidden.bs.modal',function(){
            contactFrmShow();
        })
        $this.closePopupPaymentSystem();
    }

    this.profileUpgrade = function(requestUri){
        //$jq('#pp_payment_proceed').prop('disabled', true).addChildrenLoader();
        //setTimeout($this.showErrorProfileUpgrade,1000)
        //return;
        var system=$('.cont_pp_profile_upgraded_item:visible')[0].id;
        $.ajax({type: 'POST',
                url: url_main+'upgrade.php?cmd=save&ajax=1',
                data: {item:$this.selectedPaymentPlanData.item,
                       system:system,
                       request_uri:requestUri},
                beforeSend: function(){
                    $jq('#pp_payment_proceed').prop('disabled', true).addChildrenLoader();
                },
                error: $this.showErrorProfileUpgrade,
                success: function(data){
                    $this.isAction=true;
                    var data=checkDataAjax(data);
                    if(data){
                        if(data=='before_error'){
                            $this.showErrorProfileUpgrade();
                        }else if(data.type){
                            if(data.type=='demo'){
                                if (requestUri) {
                                    redirectUrl(url_main+requestUri);
                                    return;
                                }
                                $jq('#switch_paymant').addClass('to_hide');
                                $jq('#profile_upgraded_system').oneTransEnd(function(){
                                    $jq('#profile_upgraded_system').hide();
                                    $jq('#profile_upgraded_sucess').fadeIn(250);
                                }).addClass('to_hide');
                            }else{
                                redirectUrl(url_main+data.url)
                            }
                        }
                    }else{
                        $this.showErrorProfileUpgrade();
                    }
                }
        });
    }
    /* Upgrade */

	this.initIncreasePopularityPlan = function(){
        $('.item_credit_plan:checked').change();

        $('.boost_profile .items .item').each(function() {
            $(this).click(function() {
                $('.boost_profile .items .item').removeClass('selected');
                $(this).addClass('selected');
            })
        })

        $this.initChoiceSystem(true);
    }

    this.checkRequestPopularity = function(data,action){
        action=action||'';
        data=checkDataAjax(data);
        if (data===false) {
            alertServerError();
            return false;
        }

        if(action=='payment'||action=='refill'){
            return typeof data.type!='undefined'?data:$(trim(data));
        }else{
            var $data=$(trim(data));
            if (!$data.is('.increase_payment')){
                alertServerError();
                return false;
            }
            return $data;
        }
    }

    this.changeBalance = function(balance){
        if(balance){
            $jq('#credits_balans_header').html(balance);
        }
    }

	this.requestIncreaseGroup = 0;
	this.requestIncreaseUid = 0;
	this.requestIncreasePopularity = function(cmd,type,uid,group){
        if(clProfile.notAccessToSite())return false;
		uid=uid||0;
		$this.requestIncreaseUid = uid;
		group=group||0;
		$this.requestIncreaseGroup = group;
        openPopupUpdate('pp_boost_ajax');
        //return;
        cmd=cmd||'pp_payment';
        type=type||'search';
        var action='payment';
        if(cmd=='pp_refill'){
            action='refill';
            type='refill';
        }
        $.post(url_main+'increase_popularity.php?cmd='+cmd,{ajax:1,type:type,action:action,id:0,credits:''},function(data){
            var $data=$this.checkRequestPopularity(data);
            if(!$data)return;
            var $pp=openPopupList['#pp_boost_ajax']['el'];

            if(!$pp.is(':visible'))return;
            var $cont=$data.find('.pp_cont_have'),type='';
            if(!$cont[0]){
                $cont=$data.find('.pp_cont_plan');
                if($cont[0])type='plan';
            }
            if(!$cont[0]){
                alertServerError();
                return;
            }
            $pp.find('.cont').oneTransEnd(function(){
				var $ppBody = $pp.find('.modal-body');
                if (type=='plan') {
					$pp.addClass('pp_boost_profile');
                    $ppBody.html('<div class="cont">'+$cont.html()+'</div>')
						   .find('.cont').append($data.find('.cont_boost_system'));
                }else{
                    $pp.find('.modal-body').html('<div class="cont">'+$cont.html()+'</div>').addClass('pp_boost');
                }
                $pp.find('.cont:not(.cont_boost_system), .cont_boost_plan').delay(10).removeClass('to_hide',0);
            }).addClass('to_hide');
        })
    }

    this.showPopupPaymentSystemBoost = function(){

		if(typeof inAppPurchaseProducts === 'object') {
            $this.showPopupPaymentSystem(true);
            return;
        }

        var $pp=openPopupList['#pp_boost_ajax']['el'];
        $pp.find('.cont_boost_plan').oneTransEnd(function(){
			$(this).hide();
            //$pp.removeAttr('class').addClass('pp_upgraded pp_cont');
            $this.showPopupPaymentSystem(true);
        }).addClass('to_hide', 0);
    }

    this.incPopularityPay = function(action, type, btn){
        action=action||'';
        type=type||'';
        var isMedia=type=='video_chat'||type=='audio_chat'||type=='live_stream'||type=='live_stream_past';
        if (action=='payment_service'&&isMedia) {
			addChildrenLoader(btn);
            if (type=='video_chat') {
                clVideoChat.invite($this.requestIncreaseUid, btn, $this.requestIncreaseGroup,function(){closePopupUpdate('pp_boost_ajax')});
            }else if(type=='audio_chat'){
                clAudioChat.invite($this.requestIncreaseUid, btn, $this.requestIncreaseGroup,function(){closePopupUpdate('pp_boost_ajax')});
            }else if(type=='live_stream'){
				_lsPaid();
			}else if(type=='live_stream_past'){
				if (typeof clProfilePhoto.liveViewAllowed == 'function') clProfilePhoto.liveViewAllowed();
			}
			$this.requestIncreaseUid=0;
			$this.requestIncreaseGroup=0;
            return;
        }
        var item='',system='';
        if(action!='payment_service'){
            item=$this.selectedPaymentPlanDataBoost.item;
            var $system=$('.cont_pp_profile_upgraded_item_boost:visible');
            if($system[0]){
                system=$system[0].id.replace(/_boost/, '');
            }
        }
		addChildrenLoader(btn.prop('disabled', true));

        $.ajax({type: 'POST',
                url: url_main+'increase_popularity.php',
                data: {ajax:1,action:action,item:item,system:system,type:type,request_uri:$this.requestUri},
                beforeSend: function(){
					addChildrenLoader(btn.prop('disabled', true));
                },
                error: function(){
					removeChildrenLoader(btn.prop('disabled', false));
                },
                success: function(data){
                    var $data=$this.checkRequestPopularity(data,action);
                    if(!$data){
						removeChildrenLoader(btn.prop('disabled', false));
                        return;
                    }
                    var isError=false;
                    if((action!='payment_service') && !$data[0]){
                        if($data.type=='url_system'){
							redirectUrl(url_main+$data.url);
                        } else {
                            isError=true;
                        }
                    }else{
                        var $cont=$data.find('.pp_cont_payment_success'),
                            $pp=openPopupList['#pp_boost_ajax']['el'];
                        if($cont[0]){
                            $this.changeBalance($cont.data('balance'));
							var $ppBody = $pp.find('.modal-body');
                            if (action=='payment'||action=='refill') {
                                if (isMedia) {//Demo
                                    closePopupUpdate('pp_boost_ajax');
                                    setTimeout(function(){$this.requestIncreasePopularity('pp_payment',type)},300);
                                    return;
                                }
                                $pp.find('.cont_boost_system').oneTransEnd(function(){
                                    $pp.addClass('pp_boost_ajax pp_boost');
                                    $ppBody.html($cont.html());
                                    $ppBody.find('.cont').delay(1).fadeTo(200,1)
                                }).addClass('to_hide');
                            }else{
                                $pp.find('.cont').fadeTo(200,0,function(){
                                    $ppBody.html($cont.html());
                                    $pp.find('.cont').delay(1).fadeTo(200,1)
                                })
                            }
                        }else{
                            isError=true;
                        }
                    }
                    if (isError) {
                        if (action=='payment') {
                            $this.showErrorProfileUpgrade(true);
                        }else{
                            alertServerError();
                        }
                    }
                }
        });
    }
    /* Credits */

    $(function(){
        if(currentPage=='upgrade.php'){
            $this.initReady();
        }
        $('body').on('click', '.pp_upgraded', function(e){
            var target=$(e.target);
            if(target.is('.pp_upgraded')){
                if (openPopupList['#pp_boost_ajax']
                    &&openPopupList['#pp_boost_ajax']['el'].is(':visible')){
                    if(openPopupList['#pp_boost_ajax']['close'])return;
                    var $pp=openPopupList['#pp_boost_ajax']['el'],
                        $ss=$pp.find('.cont_boost_system');
                    if ($ss[0]&&$ss.is(':visible')&&!$('#profile_upgraded_error_boost').is(':visible')) {
                        openPopupList['#pp_boost_ajax']['close']=1;
                        $ss.oneTransEnd(function(){
							$jq('body').removeClass('pp_boost_ajax_system_open');
                            $(this).hide();
                            $pp.addClass('pp_boost_ajax pp_boost_profile')
                               .find('.cont_boost_plan').show().delay(5).oneTransEnd(function(){
                                   openPopupList['#pp_boost_ajax']['close']=0;
                               }).removeClass('to_hide',0);
                        }).addClass('to_hide')
                    }else{
                        closePopupUpdate('pp_boost_ajax');
                    }
                }
            }
        }).on('click', '.credits_balans', function(e){
            $this.requestIncreasePopularity('pp_refill');
            return false;
        })
		$('#navbar_menu_refill_credits_edge, .st_credits').click(function(e){
            $this.requestIncreasePopularity('pp_refill');
            return false;
        })
    })
    return this;
}

var openPopupList={};