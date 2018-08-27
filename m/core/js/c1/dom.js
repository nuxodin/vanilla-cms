/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() { 'use strict';

if (c1.dom) console.warn('c1.dom loaded?');

var w = window,
	d = document,
	elProto = Element.prototype;

c1.dom = {};
c1.dom.fragment = function(html){
	var hasSvg = html.includes('<svg');
	var tmpl = d.createElement(hasSvg?'div':'template');
	tmpl.innerHTML = html;
	if (tmpl.content === void 0) { // ie11 or width svg content (chrome bug)
		var fragment = d.createDocumentFragment();
		var isTableEl = /^[^\S]*?<(t(?:head|body|foot|r|d|h))/i.test(html);
		tmpl.innerHTML = isTableEl ? '<table>'+html : html;
		var els        = isTableEl ? tmpl.querySelector(RegExp.$1).parentNode.childNodes : tmpl.childNodes;
		while(els[0]) fragment.appendChild(els[0]);
		return fragment;
	}
	return tmpl.content;
};

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
	c1Id: function() {
		return this.id || (this.id = 'c1-gen-'+(autoId++));
	},
	c1FindAll: function(selector){
		var elements = this.querySelectorAll('#'+this.c1Id()+' '+selector);
		//return Array.from(elements); // no ie11
		return elements;
	},
	c1Find: function(selector){
		return this.querySelector('#'+this.c1Id()+' '+selector);
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
		if (!this.parentNode) return;
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

poly.closest = function(sel){ return this.parentNode.closest(sel); };
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

// NodeLists
var proto = NodeList.prototype;
if (!proto.forEach) proto.forEach = Array.prototype.forEach;

// iterators
if (w.Symbol && Symbol.iterator) {  // no ie11 :(
	[HTMLCollection,NodeList,StyleSheetList,w.CSSRuleList].forEach(function(Interface){
		if (!Interface) return;
		var proto = Interface.prototype;
		if (proto[Symbol.iterator]) return;
		proto[Symbol.iterator] = Array.prototype[Symbol.iterator];
	});
}

}();
