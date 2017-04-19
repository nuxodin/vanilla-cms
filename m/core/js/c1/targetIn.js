/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

!function() {
  'use strict';
  c1.targetIn = {};
  var w = window,
      d = document;

  function checkTarget() {
	//if (!location.hash) return;
    var el = d.getElementById(location.hash.substr(1));
    var actives = d.querySelectorAll('.c1-targetIn');
    for (var l=actives.length, active; active = actives[--l];) {
    	active.classList.remove('c1-targetIn');
    }
    if (el) {
      var evt = new CustomEvent('c1-targetIn');
      el.dispatchEvent(evt);
      do {
        el.classList.add('c1-targetIn');
        el = el.parentNode;
      } while (el && el.classList);
    }
  }
  w.addEventListener('hashchange', checkTarget);
  d.addEventListener('DOMContentLoaded', checkTarget);
}();
