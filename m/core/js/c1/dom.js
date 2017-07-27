/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() {
	'use strict';
    if (c1.dom) console.warn('c1.dom loaded?');

    var w = window,
		d = document,
		htmlEl   = d.documentElement,
		elProto  = Element.prototype,
		define   = Object.defineProperty;

	if (!w.c1Data) { // needed?
		define(elProto,'c1Data', {
			get: function() {
				var c1Data = {};
				define(this,'c1Data', {value: c1Data});
				return c1Data;
			}
			,configurable: true
		});
		d.c1Data = {};
		w.c1Data   = {};
	}

    c1.dom = {}
	c1.dom.fragment = function(html){
		var tmpl = d.createElement('template');
		tmpl.innerHTML = html;
		if (tmpl.content === void 0){ // ie11
			var fragment = d.createDocumentFragment();
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
	        var fragment = d.createDocumentFragment();
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
	        while (child=children[--i]) {
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

	poly.closest = function(sel){ return this.parentNode.closest(sel); }
	c1.ext(poly, Text.prototype, false, true);

	function textNodeIfString(node) {
		return typeof node === 'string' ? d.createTextNode(node) : node;
	}
	function mutationMacro(nodes) {
		if (nodes.length === 1) return textNodeIfString(nodes[0]);
		for (var
			fragment = d.createDocumentFragment(),
			list = slice.call(nodes),
			i = 0;
			i < nodes.length;
			i++
		) {
			fragment.appendChild(textNodeIfString(list[i]));
		}
		return fragment;
	}

	/* (IE11, edge ok) contains-bug, textNodes are not containing  */
	var t = d.createTextNode(''), el = d.createElement('span');
	el.appendChild(t);
	if (!el.contains(t)) {
		HTMLElement.prototype.contains = function(contains) {
			return function(el) {
				return contains.call(this, el.nodeType === 1 ? el : el.parentNode);
			};
		}(HTMLElement.prototype.contains);
	}
	t.remove();
	el.remove();

	// Events
    try {
        new w.CustomEvent('?');
    } catch(o_O) {
        w.CustomEvent = function() {
            // (IE11, edge ok) where CustomEvent is there but not usable as construtor.
            // use the CustomEvent interface in such case otherwise the common compatible one
            var eventName = w.CustomEvent ? 'CustomEvent' : 'Event',
                defaultInitDict = {bubbles:false, cancelable:false, detail:null};

            function CustomEvent(type, eventInitDict) {
                var event = d.createEvent(eventName);
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
	var proto = NodeList.prototype;
	if (!proto.forEach) proto.forEach = Array.prototype.forEach;
	//if (w.Symbol && Symbol.iterator && !proto[Symbol.iterator]) proto[Symbol.iterator] = Array.prototype[Symbol.iterator]; // no ie11 :(
	// HTMLCollection
	//var proto = HTMLCollection.prototype;
	//if (w.Symbol && Symbol.iterator && !proto[Symbol.iterator]) proto[Symbol.iterator] = Array.prototype[Symbol.iterator]; // no ie11 :(

	// iterators
	if (w.Symbol && Symbol.iterator) {  // no ie11 :(
		for (var Interface of [HTMLCollection,NodeList,StyleSheetList,CSSRuleList]) {
			var proto = Interface.prototype;
			if (proto[Symbol.iterator]) continue;
			proto[Symbol.iterator] = Array.prototype[Symbol.iterator];
		}
	}

	// divers fix
    c1Use.able(c1,'fix');
	function loadFix(lib) {
        (lib in w) || d.write('<script src="'+c1.c1UseSrc+'/fix/'+lib+'.js"><\/script>');
    }
	if (!w.MutationObserver || !w.requestAnimationFrame || !w.WeakMap) loadFix('oldies');
    loadFix('Promise')
    loadFix('fetch')

}();


/*
!function() {
	// style
    var styleObj = d.documentElement.style;
    var vendors = {'moz':1,'webkit':1,'ms':1,'o':1};
    c1.dom.css = function(el, style, value) {
        if (value === undefined) {
            if (typeof style === 'string') {
                // getter
                if (styleObj[style] !== undefined) return getComputedStyle(el).getPropertyValue(style);
                return getComputedStyle(el).getPropertyValue( c1.dom.css.experimental(style) );
            }
            for (var i in style) this.css(el,i,style[i]);
        } else {
            // setter
            if (styleObj[style] !== undefined) {
                el.style[style] = value;
            } else {
                el.style[c1.dom.css.experimental(style)] = value;
            }
        }
    };

    var vendor = false;
    c1.dom.css.experimental = function(style) {
        if (styleObj[style] !== undefined) return style;
        if (vendor) return styleObj['-'+vendor+'-'+style] !== undefined ? '-'+vendor+'-'+style : undefined;
        for (var v in vendors) {
            if (styleObj['-'+v+'-'+style] !== undefined) {
                vendor = v;
                return '-'+vendor+'-'+style;
            }
        }
    };
}();
*/
