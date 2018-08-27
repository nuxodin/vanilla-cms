/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
window.qg = {};

window.Ask = function(obj, opt) {
	opt = opt || {};
	Ask.trigger('start', obj);
	var data = new FormData();
	data.append('qgToken', qgToken);
	data.append('askJSON', JSON.stringify(obj));
	var http = new XMLHttpRequest();
	var url = opt.url || location.href;
	http.open('POST', url, Ask.async);
	http.onreadystatechange = function() {
	    if (http.readyState == 4 && http.status == 200) {
			var res = JSON.parse(http.responseText);
			Ask.trigger('complete', res); // first! loads head
			res && res.script && eval(res.script); // todo, only used in cms.cont.shp3.paypement.paypal/qg.php
			opt.onComplete && opt.onComplete(res);
	    }
	}
	http.send(data);
	return http;
};
c1.ext(c1.Eventer, Ask);

Ask.async = true;
addEventListener('beforeunload',function() { // blur before unload (save)
	Ask.async = false;
	document.activeElement && document.activeElement.blur(); // ok?
});

Ask.on('complete', function(res) {
	if (!res) return;
	function executeHTML(html){
		if (!html.match('<script')) return;
		var range = document.createRange();
		range.selectNode(document.head); // required in Safari
		var fragment = range.createContextualFragment(html);
		var div = document.createElement('div');
		div.append(fragment);
		document.documentElement.append(div)
		div.remove();
		//throw('unsave inline script');
	}
	if (res.head) {
		var range = document.createRange();
		range.selectNode(document.head); // required in Safari
		var fragment = range.createContextualFragment('<div>'+res.head+'</div>');
		document.head.append(fragment);
		// $('head').append( $('<div>'+res.head+'</div>') );
	}
	if (res.updateElements) {
		for (var selector in res.updateElements) {
			if (!res.updateElements.hasOwnProperty(selector)) continue;
			var html = res.updateElements[selector];
			var els = document.querySelectorAll(selector);
			for (var i=0, el; el=els[i++];) {
				el.innerHTML = html;
				executeHTML(html)
			}
			//$(selector).html(html);
		}
	}
	if (res.replaceElements) {
		for (var selector in res.replaceElements) {
			if (!res.replaceElements.hasOwnProperty(selector)) continue;
			var html = res.replaceElements[selector];
			var els = document.querySelectorAll(selector);
			for (var i=0, el; el=els[i++];) {
				el.outerHTML = html;
				executeHTML(html)
			}
			//$(selector).replaceWith(html);
		}
	}
});

window.$fn = function(fn) {
	function params() {
		var data = {fn: fn, args: [].slice.call(arguments), callb: undefined};
		$fn.stack.push(data);
		$fn.runCollected();
		return {
			run: function(callb) {
				data.callb = callb;
				return $fn.run();
			},
			then: function(callb) { // should return a Promise ... :(
				data.callb = callb;
			},
			setInitiator: function(initiator) {
				data.initiator = initiator;
				return this;
			}
		};
	}
	return params;
};
$fn.runCollected = function() { $fn.run(); }.c1Debounce(10);
$fn.stack = [];
$fn.run = function(cb) {
	var fns = $fn.stack;
	if (!fns.length) return;
	var request = Ask({ serverInterface: fns },{
		url: location.href,
		onComplete: function(res) {
			if (!res) return;
			var results = res.serverInterface;
			if (!results) {
				console.warn('serverInterface no results');
				return;
			}
			while (results.length) {
				var value = results.shift();
				var data  = fns.shift();
				data.callb && data.callb(value);
				$fn.trigger(data.fn, {arguments:data.args, returnValue:value, initiator:data.initiator});
				//data.value = value;
				//$fn.trigger('after-'+data.fn, data); // new?
			}
			cb && cb(res.serverInterface);
		},
	});
	$fn.stack = [];
	return request;
};
c1.ext(c1.Eventer, $fn);
