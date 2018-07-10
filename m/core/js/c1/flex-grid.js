/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() { 'use strict';

var gridSupport = window.CSS && CSS.supports('display:grid');

function cssVar(style,variable) {
	return style.getPropertyValue('--'+variable) || style['-'+variable] /*ie11*/ ;
}
function renderElement(Parent) {

	var i=0, Children = [], Child, widthContainer, minWidth, cols, style, colGap, rowGap;
	style = getComputedStyle(Parent);
	widthContainer = Parent.clientWidth // subpixel-ready ??
	 	- parseFloat(style.getPropertyValue('padding-left'))
	 	- parseFloat(style.getPropertyValue('padding-right'))
	widthContainer -= .4;
	if (!widthContainer) return;
	minWidth = cssVar(style,'c1-items-min-width');
	if (!minWidth) minWidth = 200;
	minWidth = parseInt(minWidth);

	minWidth = Math.min(widthContainer, minWidth);
	var gap = cssVar(style,'c1-gap');
	colGap = parseInt( cssVar(style,'c1-col-gap') || gap ) || 0;
	rowGap = parseInt( cssVar(style,'c1-row-gap') || gap ) || 0;

	cols = Math.floor((widthContainer + colGap) / (minWidth + colGap));

	Parent.setAttribute('c1-flex-grid-numCols', cols);
	if (gridSupport) return;

	var available = widthContainer - (cols-1)*colGap;
	var itemWidth = available / cols;
	itemWidth = itemWidth - .55 / cols; // ensure it can't get too big.

	// collect visible children
	while (Child = Parent.children[i++]) {
		if (Child.offsetParent === null) continue;
		Children.push(Child);
	}

	i=0;
	while (Child = Children[i++]) {
		Child.style.width    = itemWidth +'px';
		// Child.style.minWidth = itemWidth +'px'; // dont shrink if nowrap

		var isLeft  = cols===1 || i % cols === 1;
		var isRight = cols===1 || i % cols === 0;
		var isFirstRow = i <= cols;

		Child.style.marginLeft  = isLeft  ? 0 : colGap / 2 + 'px';
		Child.style.marginRight = isRight ? 0 : colGap / 2 + 'px';
		Child.style.marginTop   = isFirstRow ? 0 : rowGap + 'px';
	}
}
function calc() {
	var Parents = document.querySelectorAll('.c1-flex-grid'), i=0, Parent;
	while (Parent = Parents[i++]) {
		renderElement(Parent);
		//ro.observe(Parent);
	}
}

/* calc, recalc */
var calc2 = function(){ requestAnimationFrame(calc); };
var calcDebounced = calc2.c1Debounce(8);
window.addEventListener('resize', calc);
addEventListener('load', calc2);
addEventListener('DOMContentLoaded', calc2);
addEventListener('DOMContentLoaded', function(){ setTimeout(calc); });
var observer = new MutationObserver(function(){
	calcDebounced();
});
observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
setInterval(calc2, 1000);
calc2();
addEventListener('c1-render', function(e){ e.target.classList.contains('c1-flex-grid') && renderElement(e.target); });

// var ro = new ResizeObserver(function(entries){
// 	var i=0, entry;
// 	while (entry = entries[i++]) {
// 		renderElement(entry.target);
// 		console.log(entry.target)
// 	}
// });


}();
