/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(){
'use strict';

c1.scroll = {
    options: {
        duration:350,
        easing:'easeInOutQuad',
        onfinish:function(){},
    },
    to: function(targetX, targetY, opt) { // todo different scroll target
        opt = c1.ext(c1.scroll.options, opt);
        var docEl = document.documentElement;
		if (!opt.ignorMaxScroll) { // if scrollarea will grow
			var maxScrollX = ('scrollMaxX' in window) ? scrollMaxX : (docEl.scrollWidth  - docEl.clientWidth);
			var maxScrollY = ('scrollMaxY' in window) ? scrollMaxY : (docEl.scrollHeight - docEl.clientHeight);
			targetX = Math.max(Math.min(maxScrollX, targetX), 0);
			targetY = Math.max(Math.min(maxScrollY, targetY), 0);
		}
    	var obj = {
            targetX: targetX,
            targetY: targetY,
            deltaX: targetX - pageXOffset,
            deltaY: targetY - pageYOffset,
            lastX: pageXOffset,
            lastY: pageYOffset,
    		duration: opt.duration,
    		easing: Easing[opt.easing],
    		onFinish: opt.onfinish,
    		startTime: Date.now(),
    	};
        window.__c1_scroll_running = obj;
    	requestAnimationFrame(step.bind(obj));
    },
	toElement: function(el, opt){
		var rect = el.getBoundingClientRect();
		var left  = rect.left + pageXOffset;
		var top = rect.top + pageYOffset
		var container = getScrollParent(el);
		if (container.c1_scroll_padding_top) {
			top -= container.c1_scroll_padding_top;
		}
		opt.container = container;
		this.to(left, top, opt);
	}
};
function step () {
	// this scrolling is not active! finish but dont trigger finish
    //if (window.__c1_scroll_running !== this) return this.onFinish();
    if (window.__c1_scroll_running !== this) return;
    //if (window.__c1_scroll_running !== this) return this.onFinish();
	// cancel scroll if scroll by hand, exit but dont trigger finish
	// can happen until load :(
	var tDiff = Date.now() - this.startTime;
	if (tDiff > 100 && this.lastY !== pageYOffset || this.lastX !== pageXOffset) return; // tDiff > 100 : ios can trigger mousemove before click fires...
	var t = Math.min(tDiff / this.duration, 1); // time that has passed (0-1)
	//if (t === 1) return this.onFinish(); // Continue as long as the duration is not exceeded // zzz
  	//if (this.targetX === pageXOffset && this.targetY === pageYOffset) return this.onFinish(); // todo? Continue as long as the x/y is not exceeded
    var x = this.targetX - ((1 - this.easing(t)) * (this.deltaX));
	var y = this.targetY - ((1 - this.easing(t)) * (this.deltaY));
    scrollTo(x+.5, y+.5);
	if (t === 1) return this.onFinish(); // Continue as long as the duration is not exceeded
    this.lastX = pageXOffset;
    this.lastY = pageYOffset;
	requestAnimationFrame(step.bind(this));
}
var Easing = { // From https://gist.github.com/gre/1650294
	linear:         function (t) { return t },
	easeInQuad:     function (t) { return t*t },
	easeOutQuad:    function (t) { return t*(2-t) },
	easeInOutQuad:  function (t) { return t<.5 ? 2*t*t : -1+(4-2*t)*t },
	easeInCubic:    function (t) { return t*t*t },
	easeOutCubic:   function (t) { return (--t)*t*t+1 },
	easeInOutCubic: function (t) { return t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1 },
	easeInQuart:    function (t) { return t*t*t*t },
	easeOutQuart:   function (t) { return 1-(--t)*t*t*t },
	easeInOutQuart: function (t) { return t<.5 ? 8*t*t*t*t : 1-8*(--t)*t*t*t },
	easeInQuint:    function (t) { return t*t*t*t*t },
	easeOutQuint:   function (t) { return 1+(--t)*t*t*t*t },
	easeInOutQuint: function (t) { return t<.5 ? 16*t*t*t*t*t : 1+16*(--t)*t*t*t*t }
};

function getScrollParent(node) {
	if (node == null) return null;
	return node.scrollHeight > node.clientHeight ? node : getScrollParent(node.parentNode);
}

}();
