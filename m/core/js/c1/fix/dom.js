/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() {
	'use strict';

	if (!c1.fix) c1.fix = {};
	c1.fix.dom = {};

	var htmlEl   = document.documentElement,
		elProto  = Element.prototype,
		define   = Object.defineProperty;

	if (!window.c1Data) { // needed?
		define(elProto,'c1Data', {
			get: function() {
				var c1Data = {};
				define(this,'c1Data', {value: c1Data});
				return c1Data;
			}
			,configurable: true
		});
		document.c1Data = {};
		window.c1Data   = {};
	}

	if (!c1.dom) c1.dom = {};

	c1.dom.fragment = function(html){
		var tmpl = document.createElement('template');
		tmpl.innerHTML = html;
		if (tmpl.content == void 0){ // ie11
			var fragment = document.createDocumentFragment();
			var isTableEl = /^[^\S]*?<(t(?:head|body|foot|r|d|h))/i.test(html);
			tmpl.innerHTML = isTableEl ? '<table>'+html : html;
			var els        = isTableEl ? tmpl.querySelector(RegExp.$1).parentNode.childNodes : tmpl.childNodes;
			while(els[0]) fragment.appendChild(els[0]);
			return fragment;
		}
		return tmpl.content;
	}

	/* needed polyfills */
	var poly = {
		matches: elProto.msMatchesSelector || elProto.webkitMatchesSelector,
		closest: function(sel) {
			return this.matches(sel) ? this : (this.parentNode && this.parentNode.closest ? this.parentNode.closest(sel) : null);
		},
		prepend: function prepend() {
			this.insertBefore(mutationMacro(arguments) , this.firstChild);
		},
		append: function append() {
			this.appendChild(mutationMacro(arguments));
		},
		before: function before() {
			var parentNode = this.parentNode;
			parentNode && parentNode.insertBefore(mutationMacro(arguments), this);
		},
		after: function after() {
			var parentNode = this.parentNode;
			parentNode && parentNode.insertBefore(mutationMacro(arguments) , this.nextSibling);
		},
		replace: function replace() {
			var parentNode = this.parentNode;
			parentNode && parentNode.replaceChild(mutationMacro(arguments), this);
		},
		remove: function remove() {
			var parentNode = this.parentNode;
			parentNode && parentNode.removeChild(this);
		},
		c1FindAll: function(selector){
			if (!this.id) this.id = 'c1-gen-'+(autoId++);
			var elements = this.querySelectorAll('#'+this.id+' '+selector);
			//return Array.from(elements); // no ie11
			return elements;
		},
		c1Find: function(selector){
			if (!this.id) this.id = 'c1-gen-'+(autoId++);
			return this.querySelector('#'+this.id+' '+selector);
		},
		/* (non standard) only ie supports native */
		removeNode: function(children) {
			if (children) return this.remove();
	        var fragment = document.createDocumentFragment();
	        while (this.firstChild) fragment.appendChild(this.firstChild);
	        this.parentNode.replaceChild(fragment, this);
		},
		/* (non standard) */
		c1ZTop: function() {
			var children = this.parentNode.children,
	            i=children.length,
	            maxZ=0,
	            child,
	            myZ=0;
	        for (;child=children[--i];) {
	            var childZ = getComputedStyle(child).getPropertyValue('z-index') || 0;
				if (child.style.zIndex > childZ) childZ = child.style.zIndex; // neu 5.16, computed after paint => check for real
				if (childZ === 'auto') childZ = 0;
	            if (child === this) myZ = childZ;
				else maxZ = Math.max(maxZ, childZ);
	        }
			if (myZ <= maxZ) this.style.zIndex = maxZ+1;
		}
	};
	var autoId = 0;
	c1.ext(poly, elProto, false, true);
	c1.ext(poly, Text.prototype, false, true);

	function textNodeIfString(node) {
		return typeof node === 'string' ? document.createTextNode(node) : node;
	}
	function mutationMacro(nodes) {
		if (nodes.length === 1) return textNodeIfString(nodes[0]);
		for (var
				fragment = document.createDocumentFragment(),
				list = slice.call(nodes),
				i = 0;
				i < nodes.length;
				i++
		) {
			fragment.appendChild(textNodeIfString(list[i]));
		}
		return fragment;
	}
	// Events
    try {
        new window.CustomEvent('?');
    } catch(o_O) {
        window.CustomEvent = function() {
            // (IE11, edge ok) where CustomEvent is there but not usable as construtor.
            // use the CustomEvent interface in such case otherwise the common compatible one
            var eventName = window.CustomEvent ? 'CustomEvent' : 'Event',
                defaultInitDict = {bubbles : false, cancelable : false, detail : null};

            function CustomEvent(type, eventInitDict) {
                var event = document.createEvent(eventName);
                if (eventName === 'Event')
                    event.initCustomEvent = initCustomEvent;
                if (eventInitDict == null)
                    eventInitDict = defaultInitDict;
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
	// NodeLists
	//if (!NodeList.prototype.forEach) NodeList.prototype.forEach = Array.prototype.forEach;
	if (!NodeList.prototype.forEach) {
		NodeList.prototype.forEach = function(callback){
			return Array.prototype.forEach.call(this, callback);
		}
		//NodeList.prototype.forEach = Array.prototype.forEach;
	}

	if (window.Symbol && Symbol.iterator && !NodeList.prototype[Symbol.iterator]) { // no ie11 :(
		NodeList.prototype[Symbol.iterator] = HTMLCollection.prototype[Symbol.iterator] = Array.prototype[Symbol.iterator];
	}
	// divers fix
	function loadFix(lib){
        (lib in window) || document.write('<script src="'+c1.c1UseSrc+'/fix/'+lib+'.js"><\/script>');
    }
    loadFix('Promise')
    loadFix('fetch')

}();
