/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

!function() {
	'use strict';
	c1.focusIn = {};
    var w = window,
        d = w.document;

    function onIn(e) {
        var el = e.target;
		while (el && el.classList) {
			el.classList.add('c1-focusIn');
			el = el.parentNode
		}
    }
    function onOut() {
        setTimeout(function() { // wait for next focus
            var befores = d.querySelectorAll('.c1-focusIn'),
                active = d.activeElement,
                i = 0,
                before = null;
            while (before = befores[i++]) {
                !before.contains(active) && before.classList.remove('c1-focusIn');
            }
        },0);
    }
    d.addEventListener('focusin' , onIn);
    d.addEventListener('focusout', onOut);
	Element.prototype.c1Focus = function() {
        var el = this;
        do {
        	if (d.activeElement === el) break;
			el.classList.add('c1-focusIn');
			var candidate = el.parentNode;
			if (!candidate || !candidate.classList) break;
            el = candidate;
        } while (el.classList);

//		d.activeElement.blur(); // used? not good for contextmenu!

		this.focus();
    };
	// chrome false negative :(
	!("onfocusin" in document) && c1.c1Use('fix/event-focusinout',function(){});
}();
