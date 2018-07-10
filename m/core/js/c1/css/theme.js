/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() { 'use strict';

var d = document;

/* add column-hover class (c1-col-hover) */
var tCells = {TH: 1, TD: 1,};
var mouse_enter_leave = function(e) {
	if (!tCells[e.target.tagName]) return;
	var el = e.target,
		tr = el.parentNode,
		add_rem = e.type === 'mouseenter' ? 'add' : 'remove',
		tableGroups = tr.parentNode.parentNode.children,
		td = tr.firstElementChild,
		index = 0;
	while (td) {
		if (td === el) break;
		index += td.colSpan;
		td = td.nextElementSibling;
	}
	for (var i = 0, tableGroup; tableGroup=tableGroups[i++];) {
		var trs = tableGroup.children;
		for (var j = 0; tr=trs[j++];) {
			var tds = tr.children;
			var colSpans = 0, k = 0;
			while (td=tds[k++]) {
				colSpans += td.colSpan;
				if (colSpans > index) {
					td.classList[add_rem]('c1-col-hover');
					break;
				}
			}
		}
	}
};
d.addEventListener('mouseenter', mouse_enter_leave, 1);
d.addEventListener('mouseleave', mouse_enter_leave, 1);

/* c1-pop esc */
var inputTags = {INPUT:1,SELECT:1,TEXTAREA:1};
d.addEventListener('keydown', function(e) {
	if (e.keyCode !== 27) return;
	var el = e.target;
	if (el.isContentEditable || inputTags[el.tagName]) return;
	do {
		if (el.classList.contains('c1-pop')) {
			el.parentNode && el.parentNode.c1Focus();
			return;
		}
		el = el.parentNode;
	} while (el && el.classList);
	d.activeElement.blur();
});


}();
