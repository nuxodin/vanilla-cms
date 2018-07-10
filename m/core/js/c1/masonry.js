/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(){
'use strict';

if (c1.masonry) return;

c1.masonry = function(el){
    el.classList.add('c1-masonry');
    el.classList.add('-Js');
	render(el);
	el.addEventListener('load', innerLoaded, true);  // loading images inside masonry
};
var innerLoaded = function (){ render(this); };//.c1Debounce(120);

c1.onElement('.c1-masonry',c1.masonry);

function renderAll(){ document.body.querySelectorAll('.c1-masonry').forEach(render); }
document.addEventListener('DOMContentLoaded', renderAll);
addEventListener('load', renderAll);
addEventListener('resize', renderAll);

function render(container){
	var parentStyle = getComputedStyle(container);
	var widthContainer = container.clientWidth; // - parseFloat(parentStyle['padding-left']) - parseFloat(parentStyle['padding-right']); // Is not taken into account when rendering
	if (!widthContainer) return;
    var minWidth = parseFloat(parentStyle['--column-width']) || parseFloat(parentStyle['column-width']) || 200;
	minWidth = Math.min(widthContainer, minWidth);
	var columns = Math.floor(widthContainer / minWidth);
	var columnWidth = widthContainer / columns;
	var children = container.children;
	var columnHeights = [], i;
	for (i = 0; i < columns; i++){
		columnHeights[i] = [i, 0];
	}
	var currentStyle, i=0, current;
	while (current = children[i++]){
		if (current.offsetParent === null) continue;
		current.style.width = columnWidth + 'px';
		current.style.left = columnHeights[0][0] * columnWidth + 'px';
		current.style.top  = columnHeights[0][1] + 'px';
		currentStyle = getComputedStyle(current);
		//columnHeights[0][1] += parseFloat(currentStyle['height']) + parseFloat(currentStyle['margin-top']) + parseFloat(currentStyle['margin-bottom']); zzz
		columnHeights[0][1] += current.offsetHeight + parseFloat(currentStyle['margin-top']) + parseFloat(currentStyle['margin-bottom']); /*  margin not used... (margin:0 !important) */
		columnHeights.sort(sortByHeight);
	}
	container.style.height = columnHeights[columns - 1][1] + 'px';
}
function sortByHeight(a, b){
	return a[1] - b[1] || a[0] - b[0];
}
//addEventListener('c1-render', function(e){ e.target.classList.contains('c1-masonry') && render(e.target); });

}();
