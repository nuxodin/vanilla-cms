/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
/* selectionchange event polyfill */
// ie and webkit has native selectionchange...
// ff52 fixed this? ie bug? https://developer.mozilla.org/en-US/docs/Web/Events/selectionchange
// bug: ie native dont change on keyevents ??? test!
!function() { 'use strict';

if (document.onselectionchange !== undefined) return;
if (!window.getSelection) return;

var d = document,
	sel = getSelection(),
	lastSel = {},
	hasNative;
function check() {
	if (   sel.anchorNode   !== lastSel.anchorNode
		|| sel.anchorOffset !== lastSel.anchorOffset
		|| sel.focusNode    !== lastSel.focusNode
		|| sel.focusOffset  !== lastSel.focusOffset
	) {
		lastSel.anchorNode   = sel.anchorNode;
		lastSel.anchorOffset = sel.anchorOffset;
		lastSel.focusNode    = sel.focusNode;
		lastSel.focusOffset  = sel.focusOffset;

        var event = new CustomEvent('selectionchange', {bubble:true});
        event.c1Generated = true;
        d.dispatchEvent( event );
	}
}
function checkDelayed(e) {
	setTimeout(function() {
		if (hasNative) return;
		check(e);
	});
}
function selectionchange(e) {
	if (!e.c1Generated) {
		hasNative = true;
		d.removeEventListener('keydown'   ,check, true);
		d.removeEventListener('keyup'     ,check, true);
		d.removeEventListener('mouseup'   ,check, true);
		d.removeEventListener('mousedown' ,checkDelayed, true);
		d.removeEventListener('focus'     ,checkDelayed, true);
		d.removeEventListener('paste'     ,checkDelayed, true);
		d.removeEventListener('drop'      ,checkDelayed, true);
	}
    setTimeout(function() {
		d.removeEventListener('selectionchange', selectionchange);
    },100);
}
d.addEventListener('keydown'  ,check, true);
d.addEventListener('keyup'    ,check, true);
d.addEventListener('mouseup'  ,check, true);
d.addEventListener('mousedown',checkDelayed, true);
d.addEventListener('focus'    ,checkDelayed, true);
d.addEventListener('paste'    ,checkDelayed, true);
d.addEventListener('drop'  	  ,checkDelayed, true);
d.addEventListener('selectionchange', selectionchange);


}();
