!function(undf,k) {
	'use strict';
	self.q1 = {};
	/* rect */
	q1.rect = function(obj) {
		if (!(this instanceof q1.rect)) return new q1.rect(obj);
		this.x = obj.x || 0;
		this.y = obj.y || 0;
		this.w = obj.w || 0;
		this.h = obj.h || 0;
	};
	q1.rect.prototype = {
        x:0,y:0,w:0,h:0
        ,r:        function() { return this.x + this.w; }
        ,b:        function() { return this.y + this.h; }
        ,isInX:    function(rct) { return rct.x > this.x && rct.r() < this.r(); }
        ,isInY:    function(rct) { return rct.y > this.y && rct.b() < this.b(); }
        ,isIn:     function(rct) { return this.isInX(rct) && this.isInY(rct); }
        ,touchesX: function(rct) { return rct.x < this.r() && rct.r() > this.x; }
        ,touchesY: function(rct) { return rct.y < this.b() && rct.b() > this.y; }
        ,touches:  function(rct) { return this.touchesX(rct) && this.touchesY(rct); }
        ,grow:     function(value) { this.w += value; this.h += value; }
        ,area:     function() { return this.h * this.w; }
	};
}();
