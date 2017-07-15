!function(){ 'use strict';
	c1.loading = {
		mark(el) {
			clearTimeout(el.c1ShowLoadingTO);
			el.c1Loading_oldCssText = el.style.cssText;
			el.c1ShowLoadingTO = setTimeout(()=>{
				el.classList.add('c1Loading');
				if (!el.offsetHeight) el.style.minHeight = '36px';
				if (!el.offsetWidth)  el.style.minWidth = '36px';
				//if (!el.offsetHeight) el.style.display = 'block';
				if (el.offsetHeight <= 36) el.style.setProperty('background-size', (el.offsetHeight*.6)+'px', 'important');
			},260);
			return function(){
				c1.loading.done(el);
			}
		},
		done(el) {
			clearTimeout(el.c1ShowLoadingTO);
			el.classList.remove('c1Loading');
            el.style.cssText = el.c1Loading_oldCssText;
		},
	}
    const svg = '<?xml version="1.0" encoding="utf-8"?><svg fill="currentColor" width="64px" height="64px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-spin"><g transform="translate(50 50)"><g transform="rotate(0) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="0s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(45) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="0.17s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.17s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(90) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="0.35s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.35s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(135) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="0.52s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.52s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(180) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="0.7s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.7s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(225) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="0.87s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.87s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(270) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="1.04s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="1.04s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(315) translate(34 0)"><circle cx="0" cy="0" r="8"><animate attributeName="opacity" from="1" to="0.1" begin="1.22s" dur="1.4s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="1.22s" dur="1.4s" repeatCount="indefinite"></animateTransform></circle></g></g></svg>';
    const css =
	'.c1Loading { '+
	'  pointer-events: none !important; ' +
	'  transition: opacity .2s !important;' +
    '  background-image:url("data:image/svg+xml;utf8,'+encodeURIComponent(svg)+'") !important; '+
	'  background-repeat: no-repeat !important; ' +
	'  background-position: 50% !important; ' +
	'  background-size: 32px !important; ' +
	'} ' +
	'.c1Loading > * { ' +
	'  opacity:.1; ' +
	'} ' +
	' ';
	var sEl = document.createElement('style');
	sEl.appendChild(document.createTextNode(css));
	document.head.append(sEl);
}();
