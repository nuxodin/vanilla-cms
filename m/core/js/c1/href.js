/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
c1.href = {
    ignoreSelector:'[onmousedown]'
};
document.addEventListener('click',function(e){
    if (e.which !== 1) return;
	if (e.defaultPrevented) return;
	if (!e.target.closest) return;
    var A = e.target.closest('[data-c1-href]');
    if (!A) return;
    if (e.target.closest('a,input,textarea,select,button')) return;
    if (e.target.closest('[fn], [onclick]')) return;
    if (e.target.closest(c1.href.ignoreSelector)) return;
    if (e.target.isContentEditable) return;
    var href = A.getAttribute('data-c1-href');
	if (!href) return;
    var target = A.getAttribute('data-c1-target');
    if (e.ctrlKey) target = '_blank'; // better random-string?
	if (target) {
		window.open(href, target);
		//!e.ctrlKey && win.focus(); // not needed in chrome, not working in ff
	} else {
		location.href = href;
	}
});
document.head.insertAdjacentHTML('beforeend','<style>[data-c1-href] {cursor:pointer},[data-c1-href=""]{cursor:normal}</style>');
