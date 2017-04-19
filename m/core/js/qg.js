/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(undf, k) { // old stuff
	'use strict';
    Function.prototype.c1Multi = function() {
	    var fn = this;
	    return function(a,b) {
	        if (b === undf && typeof a === 'object') {
	            for (var i in a)
	                if (a.hasOwnProperty(i))
	                    fn.call(this, i, a[i]);
	            return;
	        }
	        return fn.apply(this,arguments);
	    };
    };
	window.qg = {};
    qg.Eventer = {
		initEvent: function(n) {
		    !this._Es && (this._Es={});
		    !this._Es[n] && (this._Es[n]=[]);
		    return this._Es[n];
		},
		on: function(ns,fn) {
		    ns = ns.split(' ');
		    for (var i=0, n; n = ns[i++];) {
		        this.initEvent(n).push(fn);
		    }
		}.c1Multi(),
		no: function(ns,fn) {
		    ns = ns.split(' ');
		    for (var i=0, n; n = ns[i++];) {
		        var Events = this.initEvent(n);
		        Events.splice(Events.indexOf(fn) ,1);
		    }
		}.c1Multi(),
		fire: function(ns,e) {
		    var self = this, i=0, n;
		    ns = ns.split(' ');
		    for (;n = ns[i++];) {
		        var Events = this.initEvent(n);
		        Events.forEach(function(E) {
		            E.call(self,e);
		        });
		    }
		}
    };

    /* devicePixelRatio polyfill */
    if (!('devicePixelRatio' in window)) window.devicePixelRatio = ('systemXDPI' in screen) ? screen.systemXDPI / screen.logicalXDPI : 1;
    if (window.devicePixelRatio) document.cookie = "q1_dpr=" + devicePixelRatio + "; path=/";

    window.qgReplaceNode = function (el, nel) {
    	el.parentNode.insertBefore(nel, el);
    	nel.appendChild(el);
    	el.removeNode();
    	return nel;
    }
    window.ImageRealSize = function() {
        var cache = {}, undef;
        return function (url, cb) {
            if (cache[url]===undef) {
        		var nImg = new Image();
        		nImg.src = url;
				nImg.onload = function() {
                    cb.apply(null, cache[url] = [nImg.width, nImg.height]);
        		};
            } else {
                cb.apply(null,cache[url]);
            }
        };
    }();

}();

// remote
Ask_async = true;
addEventListener('beforeunload',function() { // blur before unload (save)
	document.activeElement && document.activeElement.blur(); // ok?
	Ask_async = false;
});
Ask = function(obj, opt) {
	opt = opt || {};
	Ask.fire('start', obj);
	var data = new FormData();
	data.append('qgToken', qgToken);
	data.append('askJSON', JSON.stringify(obj));
	var http = new XMLHttpRequest();
	var url = opt.url || location.href;
	http.open('POST', url, Ask_async);
	http.onreadystatechange = function() {
	    if (http.readyState == 4 && http.status == 200) {
			var res = JSON.parse(http.responseText);
			Ask.fire('complete', res); // first! loads head
			res && res.script && eval(res.script); // todo
			opt.onComplete && opt.onComplete(res);
	    }
	}
	http.send(data);
	return http;
};
c1.ext(qg.Eventer, Ask);
Ask.on('complete', function(res) {
	'use strict';
	if (!res) return;
	if (res.head) {
		$('head').append( $('<div>'+res.head+'</div>') );
		//var div = document.createElement('div');
		//div.innerHTML = res.head;
		//document.head.append(div);
	}
	if (res.updateElements) {
		for (var selector in res.updateElements) {
			if (!res.updateElements.hasOwnProperty(selector)) continue;
			var html = res.updateElements[selector];
			var els = document.querySelectorAll(selector);
//			for (var i=0, el; el=els[i++];) {
//				el.innerHTML = html; // does not execute script-tags
//			}
			$(selector).html(html);
		}
	}
	if (res.replaceElements) {
		for (var selector in res.replaceElements) {
			if (!res.replaceElements.hasOwnProperty(selector)) continue;
			var html = res.replaceElements[selector];
			var els = document.querySelectorAll(selector);
//			for (var i=0, el; el=els[i++];) {
//				el.outerHTML = html; // does not execute script-tags
//			}
			$(selector).replaceWith(html);
		}
	}
});

$fn = function(fn) {
	function params() {
		var data = {fn: fn, args: [].slice.call(arguments), callb: undefined};
		$fn.stack.push(data);
		$fn.runCollected();
		return {
			run: function(callb) {
				data.callb = callb;
				return $fn.run();
			},
			then: function(callb) {
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
$fn.runCollected = function() { $fn.run(); }.c1Debounce(20);
$fn.stack = [];
$fn.run = function(cb) {
	var fns = $fn.stack;
	if (!fns.length) return;
	for (var i=0, data; data = fns[i++];) { // new
		$fn.fire('before-'+data.fn, data); // needed? can not find some
	}
	var request = Ask({
		serverInterface: fns
	},{
		url: location.href,
		evalScripts: true,
		evalResponse: true,
		onComplete: function(res) {
			if (!res) return;
			var results = res.serverInterface;
			while (results.length) {
				var value = results.shift();
				var data  = fns.shift();
				data.callb && data.callb(value);
				$fn.fire(data.fn, {arguments:data.args, returnValue:value, initiator:data.initiator});
				//data.value = value;
				//$fn.fire('after-'+data.fn, data); // new?
			}
			cb && cb(res.serverInterface);
		},
	});
	$fn.stack = [];
	return request;
};
c1.ext(qg.Eventer, $fn);
