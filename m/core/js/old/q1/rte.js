!function() {
	'use strict';

	q1.rte = {};
	q1.rte.formatBlock = {
		set: function( tag) {
//			document.execCommand('formatBlock',null,'h1');
			var el = q1.selection().container();
			var newElement = document.createElement(tag);
			var savedRange = q1.selection().save();
			do {
				if (isRoot(el)) return false;
				var display = cssDisplay(el);
				if (display === 'block') {
					el.q1ReplaceNode( newElement );
					break;
				}
				if (isRoot(el.parentNode)) {
					rangeWrap( rangeFromInlineSiblings(el) , newElement );
					break;
				}
				var parentDisplay = cssDisplay(el.parentNode);
				if (parentDisplay !== 'block' && ~blockLikes.indexOf(parentDisplay)) {
					rangeWrap( rangeFromInlineSiblings(el) , newElement );
					break;
				}
			} while( el = el.parentNode );
			q1.selection().restore( savedRange );
		},
		get: function( tag) {
			var el = q1.selection().container();
			do {
				if (~blockLikes.indexOf( cssDisplay(el) )) {
					return el.tagName;
					break;
				}
				if (!el.parentNode.parentNode.isContentEditable) {
					return false;
					break;
				}
				el = el.parentNode; // next
			} while (true);
		}
	};
	q1.rte.insertDiv = function() {
		var rand = 'tmp'+Math.random();
		var activeEl = document.activeElement;

        var oldDiv = q1.selection().insert(rand).containerElement();
        var oldCss = oldDiv.style.cssText;

        activeEl.innerHTML = activeEl.innerHTML.replace(rand,'</div><div><span id="tmpQgSvgCursor">x</span>');

		var cursorEl = document.getElementById('tmpQgSvgCursor');
        getSelection().collapse(cursorEl,0);

        document.getElementById('tmpQgSvgCursor').remove();

        var newDiv = q1.selection().containerElement();
        newDiv.style.cssText = oldCss; // generated styles?

        if (newDiv.innerHTML==='') newDiv.innerHTML = '\u00A0';
        getSelection().collapse(newDiv,0);
	};

	var blockLikes = ['block','flex','table','list-item','table-cell'];

	function isRoot(el) {
		return !el.parentNode || !el.parentNode.isContentEditable;
	}
	function cssDisplay(el) {
		return el.nodeType===3 ? 'inline' : getComputedStyle(el).getPropertyValue('display');
	}
	function rangeWrap(range, el) {
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
			if (~blockLikes.indexOf(display)) break; // allow float:left and right?
			current = next;
		}
		range.setStartBefore(current);

		current = el;
		while (current.nextSibling) {
			next = current.nextSibling;
			display = cssDisplay(next);
			if (~blockLikes.indexOf(display))
				break;
			current = next;
		}
		range.setEndAfter(current);
		return range;
	}
}();
