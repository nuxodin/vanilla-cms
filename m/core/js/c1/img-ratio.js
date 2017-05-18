/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

(function(){
    'use strict';
    var listener = function(){
        this.removeEventListener('load',listener);
        this.removeEventListener('error',listener);
      	this.src = this.c1RealSrc;
    }
    function render(el){
        if (el.complete) return;
        var ratio = el.getAttribute('data-c1-ratio');
        if (!ratio) return;
        if (el.c1RealSrc) return;
        el.c1RealSrc = el.src;
        el.addEventListener('load',listener);
        el.addEventListener('error',listener); // not working in ie 11, bad browser
        el.src = 'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="'+ratio*1024+'" height="1024" style="background:rgba(200,200,200,.1);"></svg>');
    }
    // function renderAll(){
    //     var all = document.querySelectorAll('[data-c1-ratio]'),
    //         i = 0, el, ratio;
    //     while (el=all[i++]) render(el);
    // }
    // requestAnimationFrame(renderAll);
    // document.addEventListener('DOMContentLoaded',renderAll);

    c1.onElement('[data-c1-ratio]',render) // requestAnimationFrame?
})();
