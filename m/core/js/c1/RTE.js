/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
{
	throw('not used!');
	c1.RTE = {};
	/*
	c1.RTE.formatBlock = function(tag) {
		if (tag) {
			//document.execCommand('formatBlock',null,'h1');
			var el = q1.selection().container();
			var newElement = document.createElement(tag);
			var savedRange = q1.selection().save();
			do {
				if (isRoot(el)) return false;
				if (display === cssDisplay(el)) {
					newElement.replace(el);
					break;
				}
				if (isRoot(el.parentNode)) {
					rangeWrap(rangeFromInlineSiblings(el), newElement);
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
				if (blockLikes.includes( cssDisplay(el) )) return el.tagName;
				if (!el.parentNode.parentNode.isContentEditable) return false;
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

        if (newDiv.innerHTML==='') newDiv.innerHTML = '\u00A0';
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
		var next;
		while (next = current.previousSibling) {
			if (blockLikes.includes(cssDisplay(next))) break; // allow float:left and right?
			current = next;
		}
		range.setStartBefore(current);
		current = el;
		while (next = current.nextSibling) {
			if (blockLikes.includes(cssDisplay(next))) break;
			current = next;
		}
		range.setEndAfter(current);
		return range;
	}
	*/
}
