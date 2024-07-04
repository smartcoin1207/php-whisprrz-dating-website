/*$.fn.oneAnimationEnd=function(fn){
	this.one('animationend webkitanimationend', fn);
    if (!Modernizr.csstransitions) this.trigger('animationend');
	return this
}*/

var animateEvent='animationend webkitanimationend';

$.fn.oneAnimationEnd=function(fn){
    var el=this;
    el.on(animateEvent, function f(e){
        $(this).off(animateEvent, f);
        fn.call(this, e);
    });
    if (!window.AnimationEvent && !window.WebKitAnimationEvent) {
        setTimeout(function(){el.trigger('animationend')},10)
    }
	return this
}

$.fn.oneTransEndM=function(fn, prop){
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

var transEvent='transitionend webkitTransitionEnd';
$.fn.oneTransEnd=function(fn, prop){
	var el=this;
    el.on(transEvent, function f(e){
        var eProp=e.propertyName;
		if(e.originalEvent!=undefined){
			eProp=e.originalEvent.propertyName;
		}
        if (!eProp || new RegExp(prop||'', 'i').test(eProp)) {
            $(this).off(transEvent, f);
            fn.call(this, e);
        }
    });
	if (!window.TransitionEvent && !window.WebKitTransitionEvent) {
        setTimeout(function(){el.trigger('transitionend');},10)
    }
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


inViewport = function(element, options) {
    var settings = {
        threshold  : 0,
        container  : window.mainContentBlock||window
    };
    if(options) {
        $.extend(settings, options);
    }
	if (!element.getBoundingClientRect) return true;
		settings.threshold>>=0;
		var elRect=element.getBoundingClientRect(), top=0, left=0,
			bottom=window.innerHeight||$window.height(), right=window.innerWidth||$window.width();
        if (settings.container) {
			var contRect=settings.container.getBoundingClientRect();
			top = Math.max(0, contRect.top);
			left = Math.max(0, contRect.left);
			bottom = Math.min(bottom, contRect.bottom);
			right = Math.min(right, contRect.right);
		}
    return top   < elRect.bottom + settings.threshold
            && left  < elRect.right + settings.threshold
			&& bottom> elRect.top - settings.threshold
			&& right > elRect.left - settings.threshold;
};

$.fn.focusEl = function(options){
    if(!isIos){
        $(this).focus();
    };
    return this
};