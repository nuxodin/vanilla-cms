/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() { 'use strict';

if (window.onwheel) return;
// native: ff17, chrome31, ie9 (but no onwheel)
// good referenz: https://developer.mozilla.org/en-US/docs/Web/Reference/Events/wheel
// w3c: http://www.w3.org/TR/DOM-Level-3-Events/#events-wheelevents

document.addEventListener('wheel',onWheel);
document.addEventListener('mousewheel',onMousewheel);
function onWheel(e) {
    if (!e.c1Generated)
        document.removeEventListener('mousewheel',onMousewheel);
    setTimeout(function() {
        document.removeEventListener('wheel', onWheel);
    }, 100);
}
function onMousewheel(e) {
    var event = new CustomEvent('wheel', {bubbles:true});
    event.c1Generated = 1;
    event.deltaX = e.wheelDeltaX;
    event.deltaY = e.wheelDeltaY !== undefined ? e.wheelDeltaY : e.wheelDelta;
    event.deltaZ = e.wheelDeltaZ;
    e.target.dispatchEvent(event);
}


}();
