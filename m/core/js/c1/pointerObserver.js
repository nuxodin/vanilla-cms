/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

!function() {
	'use strict';

	var d = document;
	var touching = false;
	var Observer = function(el, options) {
	    this.options = options || {mouse: true, touch: true};
        this.el   = el;
		this.pos  = {};
		this.last = {};
		this.posStart = {};
		this.diff = {};

		var self = this,
		start = function(e) {
			if (e.type === 'mousedown'  && !self.options.mouse) return;
			if (e.type === 'touchstart' && !self.options.touch) return;

			var pointer = e;
			if (e.touches) {
				pointer = e.touches[0];
				if (e.touches.length > 1) return;
				self.identifier = pointer.identifier;
			}

			self.posStart = self.pos = {
				x:pointer.pageX,
				y:pointer.pageY
			};
			if (self.options.mouse) {
	            d.addEventListener('mousemove', move);
	            d.addEventListener('mouseup'  , stop);
			}
			if (self.options.touch) {
				d.addEventListener('touchmove', move);
	            d.addEventListener('touchend' , stop);
//	            d.addEventListener('touchstart', gstart);
			}
			self.onstart && self.onstart(e);
			touching = true;
		},
/* zzz
		gstart = function(e) {
			var pointer = e.touches[0];
			var finger2 = e.touches[1];
			if (finger2) {
				var deltaX = pointer.pageX - finger2.pageX;
				var deltaY = pointer.pageY - finger2.pageY;
				self.degStart = Math.atan2(deltaY, deltaX) * 180 / Math.PI;
				self.distStart = Math.sqrt(deltaX*deltaX + deltaY*deltaY);
				self.ongesturestart && self.ongesturestart(e);
			}
		},
*/
		move = function(e) {
			var pointer = e;

			if (e.touches) {
				pointer = e.touches[0];

				var finger2 = e.touches[1];
				if (finger2 && self.ongesture) {
					var deltaX = pointer.pageX - finger2.pageX;
					var deltaY = pointer.pageY - finger2.pageY;

					var deg = Math.atan2(deltaY, deltaX) * 180 / Math.PI;
					e.rotate = deg -= self.degStart;

			        var dist = Math.sqrt(deltaX*deltaX + deltaY*deltaY);
					e.scale = dist / self.distStart;

					self.ongesture && self.ongesture(e);
				}

				if (pointer.identifier !== self.identifier) return;
			}

			self.last = self.pos;
			self.pos = {
				x: pointer.pageX,
				y: pointer.pageY,
				time: e.timeStamp,
			};
	        if (self.last.x===self.pos.x && self.last.y === self.pos.y) return;
	        self.diff = {
        		x: self.pos.x-self.last.x,
        		y: self.pos.y-self.last.y,
				time: self.pos.time - self.last.time,
        	};
	        self.onmove && self.onmove(e);
		},
		stop = function(e) {
			if (e.changedTouches) {
				if (e.changedTouches[0].identifier !== self.identifier) return;
			}
			self.onstop && self.onstop(e);
			d.removeEventListener('mousemove', move);
			d.removeEventListener('mouseup'  , stop);
            d.removeEventListener('touchmove', move);
			d.removeEventListener('touchend' , stop);
//			d.removeEventListener('touchstart', gstart);
			touching = false;
		};
		!touching && el.addEventListener('mousedown', start);
		el.addEventListener('touchstart', start);
	};
	Observer.prototype.lastDiff = function() {
		return {
			x: this.pos.x - this.last.x,
			y: this.pos.y - this.last.y
		};
	};
	Observer.prototype.startDiff = function() {
		return {
			x: this.pos.x - this.posStart.x,
			y: this.pos.y - this.posStart.y
		};
	};
	c1.pointerObserver = Observer;
}();

/* ussage *
observer = new c1.pointerObserver(el,{mouse:true,touch:false});
observer.onmove = function(e) {
	console.log(this.pos);
}
/**/
