var CCounters = function() {

    var $this=this;
    this.counters = {
        friends_pending : [],
        new_message : [],
        new_events : [],
        new_tasks : [],
    };

    this.init = function(){
        for (var key in $this.counters) {
            $this.counters[key]=$jq('.'+key+'_counter').on('count:update', $this.updateOne);
            $this.counters[key+'_link']=$jq('.'+key+'_link');
        }
    }

    this.setDataOneCounter = function(key, counter, enabled, nostagger, title){
        if ($this.counters[key] && $this.counters[key][0]) {
            title=defaultFunctionParamValue(title, false);
            $this.counters[key].data({key:key, counter:counter,enabled:enabled,nostagger:nostagger,title:title})
                               .attr({'data-key':key,'data-counter':counter,'data-enabled':enabled,
                                      'data-nostagger':nostagger, 'data-title':title})
                               .trigger('count:update');
        }
    }

    this.updateOne = function(){
        var $el=$(this),
            oldCount=$el[0].innerText*1, newCount=$el.data('counter')*1;
        if (oldCount!=newCount) {
            var trans=isVisiblePage?{transition:''}:{transition:'none'};
            if (typeof $el.data('title') != 'undefined' && $el.data('title') !== false) {
                $el.prev('figure').attr('title',$el.data('title'));
            }
            if (newCount) {
                $el.css(trans).text(newCount).addClass('to_show');
                if ($el.closest('.wrap_icons_info')[0] && !($el.data('nostagger')*1)) {
                    $el.prev('.fa, .glyphicon').oneAnimationEnd(function(){
                       $(this).removeClass('to_active');
                    }).addClass('to_active');
                }
            } else {
                $el.css(trans).oneTransEnd(function(){
                    $el[0].innerText=0;
                }).removeClass('to_show');
                if(!isVisiblePage)$el[0].innerText=0;
            }
        }
        var enabled=$el.data('enabled')*1;
        $this.counters[$el.data('key')+'_link'][enabled?'removeClass':'addClass']('disabled');
    }

    this.update = function(counters){
        for (var key in counters) {
            var counter=counters[key]['count']*1;

            var title=typeof counters[key]['title'] != 'undefined' ? counters[key]['title'] : false;
            $this.setDataOneCounter(key, counter, counters[key]['enabled']*1, false, title);
            if (key == 'new_message' && counter && counters[key]['uid']) {
                var imMessagesCountCurrent = imMessagesCount;
                imMessagesCount = counter;
                var lastNewMessageUserId = counters[key]['uid'],
                    lastNewMessageText = counters[key]['msg'];
                if(counter > imMessagesCountCurrent) {
                    if(lastNewMessageUserId) {
                        if (mobileAppLoaded) {
                            mobileAppNotification(lastNewMessageUserId, lastNewMessageText, false, false, counters[key]['url_notif']);
                        } else {
                            clMessages.notifFromNoVisibilityPage();
                        }
                    }
                }
            }
        }
    }

    this.updateNewMsg = function(counters){
        $this.setDataOneCounter('new_message', counters['count']*1, counters['enabled']*1);
    }

    $(function(){
        $this.init()
    })
    return this;
}