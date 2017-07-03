/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
setTimeout(functrion(){ throw('used?') })

!function() {
	'use strict';
	c1.RTE = {};
	c1.RTE.formatBlock = function(tag) {
		if (tag) {
			//document.execCommand('formatBlock',null,'h1');
			var el = q1.selection().container();
			var newElement = document.createElement(tag);
			var savedRange = q1.selection().save();
			do {
				if (isRoot(el)) return false;
				var display = cssDisplay(el);
				if (display === 'block') {
					newElement.replace(el);
					break;
				}
				if (isRoot(el.parentNode)) {
					rangeWrap(rangeFromInlineSiblings(el) , newElement);
					break;
				}
				var parentDisplay = cssDisplay(el.parentNode);
				if (parentDisplay !== 'block' && blockLikes.includes(parentDisplay)) {
					rangeWrap(rangeFromInlineSiblings(el) , newElement);
					break;
				}
			} while (el = el.parentNode);
			q1.selection().restore(savedRange);
		} else {
			var el = q1.selection().container();
			do {
				if (blockLikes.includes( cssDisplay(el) )) {
					return el.tagName;
				}
				if (!el.parentNode.parentNode.isContentEditable) {
					return false;
				}
				el = el.parentNode; // next
			} while (true);
		}
	};
	c1.RTE.insertDiv = function() {
		var rand     = 'tmp'+Math.random();
		var activeEl = document.activeElement;
		var sel      = getSelection();
		var range    = sel.getRangeAt(0);
		var oldDiv   = sel.c1FocusElement();
        var oldCss   = oldDiv.style.cssText;
        var oldCLassName = oldDiv.className;

		range.c1Insert(rand);
        activeEl.innerHTML = activeEl.innerHTML.replace(rand, '</div><div><span id="tmpQgSvgCursor">x</span>');

		var cursorEl = document.getElementById('tmpQgSvgCursor');
        sel.collapse(cursorEl, 0);
        cursorEl.remove();

        var newDiv = getSelection().c1FocusElement();
        newDiv.style.cssText = oldCss;
        newDiv.className = oldCLassName;

        if (newDiv.innerHTML==='') { newDiv.innerHTML = '\u00A0'; }
        getSelection().collapse(newDiv, 0);
	};

	var blockLikes = ['block','flex','table','list-item','table-cell'];

	function isRoot(el) {
		return !el.parentNode || !el.parentNode.isContentEditable;
	}
	function cssDisplay(el) {
		return el.nodeType===3 ? 'inline' : getComputedStyle(el).getPropertyValue('display');
	}
	function rangeWrap(range,el) {
		var fragment = range.extractContents();
		range.insertNode(el);
		el.appendChild(fragment);
	}
	function rangeFromInlineSiblings(el) {
		var current = el;
		var range = document.createRange();
		var next,display = null;

		while (current.previousSibling) {
			next = current.previousSibling;
			display = cssDisplay(next);
			if (blockLikes.includes(display)) { // allow float:left and right?
				break;
			}
			current = next;
		}
		range.setStartBefore(current);

		current = el;
		while (current.nextSibling) {
			next = current.nextSibling;
			display = cssDisplay(next);
			if (blockLikes.includes(display)) break;
			current = next;
		}
		range.setEndAfter(current);
		return range;
	}
}();

// polyfills / extensions
if (!window.Selection) { // Android 4.3, Safari 5.1
  window.Selection = {};
  Selection.prototype = getSelection().__proto__;
}
Selection.prototype.c1AnchorElement = function() {
	var node = this.anchorNode;
	return node.nodeType === 1 ? node : node.parentNode;
};
Selection.prototype.c1FocusElement = function() {
	var node = this.focusNode;
	return node.nodeType === 1 ? node : node.parentNode;
};
Selection.prototype.c1GetRange = function() {
	return this.getRangeAt(0);
};
Selection.prototype.c1SetRange = function(range) {
	this.removeAllRanges();
	this.addRange(range);
};
Range.prototype.c1Insert = function(node) {
	if (node instanceof Array) {
		for (var i=0, n ; n = node[i++];) this.insertNode(n);
		return;
	}
	if (typeof node === 'string') { node = document.createTextNode(node); }
	this.insertNode(node);
};
