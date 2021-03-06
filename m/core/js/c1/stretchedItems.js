/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() { 'use strict';

var css =
'.c1StretchedItems > * { '+
'  display:block; '+
'  width:25%; '+
//'  float:left; '+
'  min-width:inherit; '+
'  box-sizing:border-box; '+
'  flex-basis:var(--c1-items-width); '+
'} '+
// '.c1StretchedItems:after { '+
// '  content:\'\'; '+
// '  display:block; '+
// '  clear:both; '+
// '}'+
'.c1StretchedItems { '+
'  -moz-column-gap:0; '+ // firefox has a default gap on every element
'  display:flex; '+
'  flex-wrap:wrap; '+
'}';

var style = document.createElement('style');
if (style.styleSheet) {   // for IE
    style.styleSheet.cssText = css;
} else {                  // others
    style.appendChild(document.createTextNode(css));
}
document.head.insertBefore(style, document.head.firstChild);

function renderElement(Parent) {
	var i=0, Children = [], Child, widthContainer, minWidth, cols, style, colGap, rowGap;

	style = getComputedStyle(Parent);

	//widthContainer = Parent.getBoundingClientRect().width // bad: includes transform-scaling
	/*
	widthContainer = Parent.offsetWidth // subpixel-ready ??
		- parseFloat(style.getPropertyValue('padding-left'))
		- parseFloat(style.getPropertyValue('padding-right'))
		- parseFloat(style.getPropertyValue('border-right-width'))
		- parseFloat(style.getPropertyValue('border-left-width'));
	/* */
	widthContainer = Parent.clientWidth // subpixel-ready ??
	 	- parseFloat(style.getPropertyValue('padding-left'))
	 	- parseFloat(style.getPropertyValue('padding-right'))
	/**/
	widthContainer -= .4;

	if (!widthContainer) return;

	//minWidth = parseInt(Parent.getAttribute('data-items-min-width') || style.getPropertyValue('min-width')) || 200;
	minWidth = style.getPropertyValue('--c1-items-width') || style['-c1-items-width']/*ie11*/;
	if (!minWidth) {
		console.warn('strechedItems: --c1-items-width or -c1-items-width not set!  (deprecated attribute "data-items-min-width" and css "min-width")');
		console.log(Parent);
		minWidth = Parent.getAttribute('data-items-min-width') || style.getPropertyValue('min-width');
	}
	if (!minWidth) minWidth = 200;
	minWidth = parseInt(minWidth);

	minWidth = Math.min(widthContainer, minWidth);
	colGap   = rowGap = style.getPropertyValue('column-gap') || style.getPropertyValue('-webkit-column-gap') || style.getPropertyValue('-moz-column-gap') || 0;
	if (Parent.hasAttribute('data-items-gap'))     colGap = Parent.getAttribute('data-items-gap');
	if (Parent.hasAttribute('data-items-row-gap')) rowGap = Parent.getAttribute('data-items-row-gap');
	rowGap = parseInt(rowGap)||0;
	colGap = parseInt(colGap)||0;

	cols = Math.floor((widthContainer + colGap) / (minWidth + colGap));

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
		Child.style.width    = itemWidth +'px'; // better then .00003 ?
		Child.style.minWidth = itemWidth +'px'; // dont shrink if nowrap

		var isLeft  = cols===1 || i % cols === 1;
		var isRight = cols===1 || i % cols === 0;
		var isFirstRow = i <= cols;

		Child.style.marginLeft  = isLeft  ? 0 : colGap / 2 + 'px';
		Child.style.marginRight = isRight ? 0 : colGap / 2 + 'px';
		Child.style.marginTop   = isFirstRow ? 0 : rowGap + 'px';
	}
}
function calc() {
	var Parents = document.querySelectorAll('.c1StretchedItems'), i=0, Parent;
	while (Parent = Parents[i++]) {
		renderElement(Parent);
		//ro.observe(Parent);
	}
}
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
addEventListener('c1-render', function(e){ e.target.classList.contains('c1StretchedItems') && renderElement(e.target); });

// var ro = new ResizeObserver(function(entries){
// 	var i=0, entry;
// 	while (entry = entries[i++]) {
// 		renderElement(entry.target);
// 		console.log(entry.target)
// 	}
// });


}();
