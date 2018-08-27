/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(){ 'use strict';

var w = window,
    define = Object.defineProperty;


/* form.reportValidity | ie 11, edge 14, safari 10 */
if (!HTMLFormElement.prototype.reportValidity) {
    HTMLFormElement.prototype.reportValidity = function() {
		if (this.checkValidity()) return true;
		var btn = document.createElement('button');
		this.appendChild(btn);
		btn.click();
		this.removeChild(btn);
		return false;
    };
}
// array.includes
if (!Array.prototype.includes) {
    Array.prototype.includes = function(search /*, fromIndex*/) {
        if (this == null) throw new TypeError('Array.prototype.includes called on null or undefined');
        var O = Object(this);
        var len = parseInt(O.length, 10) || 0;
        if (len === 0) return false;
        var n = parseInt(arguments[1], 10) || 0;
        var k;
        if (n >= 0) {
            k = n;
        } else {
            k = len + n;
            if (k < 0) k = 0;
        }
        var current;
        while (k < len) {
            current = O[k];
            if (search === current || (search !== search && current !== current)) return true;
            k++;
        }
        return false;
    };
}
// String
if (!String.prototype.includes) {
    String.prototype.includes = function(search, start) {
        if (typeof start !== 'number') start = 0;
        if (start + search.length > this.length) return false;
        else return this.indexOf(search, start) !== -1;
    };
}
// date
if (!Date.now) Date.now = function() { return new Date().getTime(); };
// requestAnimationFrame
if (!w.requestAnimationFrame) {
    var last_RAF_Time = 0;
    w.requestAnimationFrame = w.webkitRequestAnimationFrame || w.mozRequestAnimationFrame || function(callback) {
        var currTime = new Date().getTime();
        var timeToCall = Math.max(0, 16 - (currTime - last_RAF_Time));
        var id = window.setTimeout(function() { callback(currTime + timeToCall); }, timeToCall);
        last_RAF_Time = currTime + timeToCall;
        return id;
    };
}
if (!w.cancelAnimationFrame) {
    w.cancelAnimationFrame = w.webkitCancelAnimationFrame || w.mozCancelAnimationFrame || function(id) { clearTimeout(id); };
}
// MutationObserver
if (!w.MutationObserver) {
    w.MutationObserver = w.WebKitMutationObserver;
}
// WeakMap (object as key only)
if (!w.WeakMap) {
    w.WeakMap = function(){
        this._weakmap_entry_ = ''+Math.random();
    };
    WeakMap.prototype = {
        get:function(key){ return key[this._weakmap_entry_]; },
        has:function(key){ return this._weakmap_entry_ in key; },
        set:function(key,value){
            define(key, this._weakmap_entry_, {
                configurable: true,
                value:value
            });
            return this;
        },
        delete:function(key){
            var had = this.has(key);
            delete key[this._weakmap_entry_];
            return had;
        },
    };
}

// Custom Events
try {
    new w.CustomEvent('?');
} catch(o_O) {
    w.CustomEvent = function() {
        // (IE11, edge ok) where CustomEvent is there but not usable as construtor.
        // use the CustomEvent interface in such case otherwise the common compatible one
        var eventName = w.CustomEvent ? 'CustomEvent' : 'Event',
            defaultInitDict = {bubbles:false, cancelable:false, detail:null};
        function CustomEvent(type, eventInitDict) {
            var event = document.createEvent(eventName);
            if (eventName === 'Event') event.initCustomEvent = initCustomEvent;
            if (eventInitDict == null) eventInitDict = defaultInitDict;
            event.initCustomEvent(type, eventInitDict.bubbles,eventInitDict.cancelable, eventInitDict.detail);
            return event;
        }
        function initCustomEvent(type, bubbles, cancelable, detail) {
            this.initEvent(type, bubbles, cancelable);
            this.detail = detail;
        }
        return CustomEvent;
    }();
}

/* (IE11, edge ok) contains-bug, textNodes are not containing  */
var t = document.createTextNode(''), el = document.createElement('span');
el.appendChild(t);
if (!el.contains(t)) {
	HTMLElement.prototype.contains = function(contains) {
		return function(el) {
			return contains.call(this, !el || el.nodeType === 1 ? el : el.parentNode);
		};
	}(HTMLElement.prototype.contains);
}
//t.remove(); // needed? (no remove in ie11)
//el.remove();

// ie11
if (!SVGSVGElement.prototype.blur) {
    SVGSVGElement.prototype.focus = HTMLElement.prototype.focus;
    SVGSVGElement.prototype.blur = HTMLElement.prototype.blur;
}

}();
