var cacheJq={};

$.fn.oneAnimationEnd=function(fn){
	this.one('animationend webkitanimationend', fn);
    if (!Modernizr.csstransitions) this.trigger('animationend');
	return this
}

$.fn.oneTransEnd=function(fn, prop){
    //"transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
	var trans=Modernizr.csstransitions;
	//console.log(typeof prop);
	return trans ? this.on('webkittransitionend transitionend', function fu(e){
		if ((prop && e.originalEvent.propertyName.replace(/-webkit-|-moz-|-ms-/, '')!=prop) || e.target!=this) return;
		//console.log(e.originalEvent.propertyName, prop, e.target);
		$(this).off('webkittransitionend transitionend', fu)
		fn.call(this, e)
	}) : this.each(fn);
	return this
}

$.event.fixHooks.mousewheel=$.event.fixHooks.wheel={
    filter:function(e, oe){
        e.deltaY=oe.deltaY||-oe.wheelDeltaY;
        if (e.deltaY===undefined) e.deltaY=-oe.wheelDelta||0;
        e.deltaX=oe.deltaX||-oe.wheelDeltaX||0;
        e.deltaZ=oe.deltaZ||0;
        e.deltaMode=oe.deltaMode||0;
        e.toPx=function(el){
            if (e.deltaMode===1) {e.deltaY*=40; e.deltaX*=40; e.deltaZ*=40};
            if (e.deltaMode==2) {
                e.deltaY*=$(el||this).innerHeight();
                e.deltaX*=$(el||this).innerWidth();
            };
        }
        return e
    }
};

if (!window.WheelEvent) $.extend($.event.special, {
    wheel: {delegateType: 'mousewheel', bindType: 'mousewheel'},
    //mousewheel: {preDispatch: function(e) {e.type='wheel'}}
});

$.fn.wheel=function(data, fn) {
    return arguments.length? this.on('wheel', null, data, fn):this.trigger('wheel');
}

var isDisableSmoothScroll=false;
function smooth_scroll(e) {
    if (isDisableSmoothScroll) return;
    if (e.ctrlKey) return;
    e.preventDefault();

    var targ=$(e.target), l, t, x, y, to={}, data={}, isB, dur=0, scale=window.devicePixelRatio||1;
    if (!targ.parent()[0].tagName) targ=$('body');
    while (targ[0]!=$('html')[0]) {
        x=/scroll|auto/.test(targ.css('overflow-x'));
        y=/scroll|auto/.test(targ.css('overflow-y'));
        if (x||y||(isB=x=y=targ.is('body:not(.themodal-lock)'))) {
            l=(targ.data('left0')||(isB?$win:targ).scrollLeft());
            t=(targ.data('top0')||(isB?$win:targ).scrollTop());
            x=x&&e.deltaX&&(e.deltaX<0?l>0:(l+(isB?$win.width():targ[0].clientWidth)<targ[0].scrollWidth));
            y=y&&e.deltaY&&(e.deltaY<0?t>0:(t+(isB?$win.height():targ[0].clientHeight)<targ[0].scrollHeight));
            if (x||y) break;
        }
        targ=targ.parent();
    }
    if (targ[0]==$('html')[0]) return;
    if (x) {
        to.scrollLeft=data.left0=l+=Math.round(e.deltaX/scale);
        dur=Math.abs(l-targ.scrollLeft())*2.5
    };
    if (y) {
        to.scrollTop=data.top0=t+=Math.round(e.deltaY/scale);
        dur=Math.max(dur, (Math.abs(t-targ.scrollTop())*2.5))
    };
    if (isB) targ=$('body, html');
    if (dur<200)dur=200;
    targ.data(data).stop()
     .animate(to, Math.min(400, dur), 'easeOutQuad', function(){targ.data({top0: 0, left0: 0})});
}

function getLoader(cl,isHide,isWhite,notCache){
    cl=cl||'';notCache=notCache||0;
    isHide&&cl+' hidden';
    var $loader=$('#css_loader').clone().addClass(cl).removeAttr('id');
    isWhite&&$loader.find('.spinner').addClass('spinnerw');
    var key='loader_'+cl;
    if(!notCache)cacheJq[key]=$loader;
    return $loader;
}

var $jq = getCacheJq = function(sel,context){
    context=context||false;
    var key=sel;
    if(context!==false){
        key=sel+'_'+context;
    }
    if(typeof cacheJq[key] == 'undefined' || !cacheJq[key][0]){
        if(context){
            cacheJq[key]=$(sel,context);
        }else{
            cacheJq[key]=$(sel);
        }
    }
    return cacheJq[key];
}

function selectText(el){
    el.focus();
    var range = document.createRange();
    range.selectNodeContents(el);
    var selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
}

$.fn.addLoader = function(){
    var $btn=$(this);
    if($btn.is('.color_t')||$btn.is('.color_fade'))return;
    var clLoader=$btn.data('clLoader');
    if(!clLoader)clLoader='btn_action_loader';
    var typeLoader=$btn.data('typeLoader');
    if(typeLoader=='fade_btn'){
        $btn.addClass('color_fade').append(getLoader(clLoader,true,true).delay(1).removeClass('hidden',0));
        if($btn.data('clChildren')){
            $btn.find($btn.data('clChildren')).siblings(':not(.css_loader)').stop().fadeTo(200,0);
        }else{
            $btn.children('button, .frame').stop().fadeTo(200,.5);
            $btn.children(':not(.css_loader)').not('button, .frame').stop().fadeTo(200,0);
        }
    }else{
        $btn.addClass('color_t').append(getLoader(clLoader,false,true));
    }
    return $btn;
}

$.fn.removeLoader = function(){
    var $btn=$(this);
    if($btn.is('.color_t')||$btn.is('.color_fade')){
        if($btn.is('.color_fade')){
            if($btn.data('clChildren')){
                $btn.find($btn.data('clChildren')).siblings().stop().fadeTo(200,1,function(){
                    $btn.find('.css_loader').remove();
                    $btn.removeClass('color_fade');
                })
            }else{
                $btn.children(':not(.css_loader)').stop().fadeTo(200,1,function(){
                    $btn.find('.css_loader').remove();
                    $btn.removeClass('color_fade');
                })
            }
            $btn.find('.css_loader').oneTransEnd(function(){
                $(this).remove();
            }).addClass('hidden');
        }else{
            $btn.find('.css_loader').remove();
            $btn.removeClass('color_t');
        }
    }
    return $btn;
}