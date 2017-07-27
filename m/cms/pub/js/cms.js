/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

(function(){
	'use strict';

	if (window.cms) throw('cms.js already loaded!');

	window.cms = {};
	c1.ext(c1.Eventer, cms);

	cms.cont = function(id) {

		if (id === undefined) console.warn('cms.cont(id): id undefined?');
		if (id === 'undefined') console.warn('cms.cont(id): id undefined?');

		if (cms.cont.all[id]) return cms.cont.all[id];
		if (!(this instanceof cms.cont)) return new cms.cont(id);
		cms.cont.all[id] = this;
		this.id = id;
	};
	c1.ext(c1.Eventer, cms.cont);
	cms.cont.prototype = {
		upload: function(File, complete, replace) {
			var event = c1.ext(c1.Eventer);
			event.pid = this.id;
			event.File = File;
			var progress = function(e) {
				event.trigger('progress', e);
			};
			var wrapComplete = function(res) {
				res = JSON.parse(res);
				res.error && alert(res.error);
				complete && setTimeout(function(){ complete(res); }, 700); // firefox problem?
				event.trigger('complete', res);
			};
			qgfileUpload(File, 'cmsPageFile', {
				url: location.pathname+'?cmspid='+this.id+'&replace='+(replace||''),
				progress: progress,
				complete: wrapComplete
			});
			cms.cont.trigger('upload', event);
		}
	}
	cms.cont.all = {};

	$fn.on('page::insertBefore', function(e) {
		if (e.initiator === 'cms.dnd') return;
		if (e.arguments[1] == Page) {
			$fn('page::reload')(e.arguments[1]);
		} else {
			var els = document.querySelectorAll('.-pid'+e.arguments[1]);
			for (var i=0,el; el=els[i++];) el.parentNode.removeChild(el);
			$fn('page::reload')(e.arguments[0]);
		}
	});

	cms.contInitAdded = {};
	cms.initCont = function(module, fn) {
		// once per module
		if (cms.contInitAdded[module]) throw 'cms.contInit not twice!';
		cms.contInitAdded[module] = true;
		// check if initilized
		var checkAndRun = function(el){
			if (el.cmsInitialized) return;
			el.cmsInitialized = true;
			fn(el);
		}
		// existing elements
		var els = document.querySelectorAll('.-m-'+module.replace(/\./g,'-'));
		for (var i=0, el; el=els[i++];) checkAndRun(el);
		// future elements
		cms.on('contentReady', function(el) {
			cms.el.module(el) === module && checkAndRun(el);
		});
	};

	document.addEventListener('qgCmsCont.ready', function(e) {
		e.target.qgCmsCont_initialized = 1;
		cms.trigger('contentReady', e.target);
	});

	window.dbFile = function(el) {
		var elements = el.getAttribute('src').replace(/.*dbFile\//, '').split(/\//);
		this.el = el;
		this.name = elements.pop();
		this.id   = elements.shift();
		this.parts = {};
		for (var i=0, element; element = elements[i++];) {
			if (element === '') continue;
			var nv = element.split('-', 3);
			this.parts[nv[0]] = nv[1];
		}
	};
	dbFile.prototype = {
		get: function(part){
			return this.parts[part];
		},
		set: function(part, value){
			this.parts[part] = value;
			this.write();
			return this;
		},
		write: function(){
			var src = '';
			for (var part in this.parts) {
				src += '/'+part;
				var value = this.parts[part];
				if (value === undefined) continue;
				src += '-'+value;
			}
			src = this.el.getAttribute('src').replace(/dbFile\/.+/, 'dbFile/' + this.id + src) + '/'+this.name;
			src = src.replace('http://'+location.host, '');
			src = src.replace('https://'+location.host, '');
			src = src.replace(/([^:])\/\//, '$1/');
			this.el.setAttribute('src', src);
		}
	}

	document.addEventListener('focus', function(e) {
		var input = e.target,
			lastRequest,
			box;
		if (input.tagName !== 'INPUT') return;
		if (input.getAttribute('type') === 'qgcms-page') {
			box = new c1Combobox(input);
			box.searchOptions = function(){
				lastRequest && lastRequest.abort();
				lastRequest = $fn('cms::searchPagesByTitle')(input.value, input.getAttribute('qgcms-filter')).run(box.setOptions.bind(box));
			};
		}
		if (input.getAttribute('type') === 'qgcms-file') {
			box = new c1Combobox(input);
			box.searchOptions = function(){
				lastRequest && lastRequest.abort();
				lastRequest = $fn('cms::searchFile')(input.value).run(box.setOptions.bind(box));
			};
		}
		box && box.onfocus(e);
	}, true);

	cms.el = {
		root: function(el){
			return el.closest('.qgCmsCont');
		},
		pid: function(el){
			var root = el.closest('.qgCmsCont');
			return root && root.className.replace(/.*-pid([0-9]+).*/, '$1');
		},
		module: function(el){
			var root = el.closest('.qgCmsCont');
			return root && root.className.replace(/.*-m-([^ ]+).*/, '$1').replace(/-/g, '.');
		}
	}

	document.addEventListener('DOMContentLoaded',function(){
	  	var w = window,
	        d = document;
		// sync txts
		d.body.addEventListener('keyup', function(e) {
			if (!e.target.hasAttribute('cmstxt')) return;
			var el = e.target,
				tid = el.getAttribute('cmstxt'),
				all = d.querySelectorAll('[cmstxt="'+tid+'"]'),
				v = isFormEl(el) ? el.value : el.innerHTML,
				i = 0, other;
			while (other = all[i++]) {
				if (el === other) continue;
				if (isFormEl(other)) {
					other.value = v;
				} else {
					other.innerHTML = v;
				}
			}
		}.c1Debounce(50));
		// save txts
		d.body.addEventListener('blur', saveTxt, true);
		d.body.addEventListener('input', saveTxt.c1Debounce(1600));

		function isFormEl(el) {
	    	return el.value !== undefined && el.tagName !== 'BUTTON';
	    }
		function cleanUpEl(el) {
	        if (isFormEl(el)) return;
	        var els = el.querySelectorAll('img'), i=0, el;
	        while (el=els[i++]) {
	            var src = el.getAttribute('src');
	            src.indexOf(location.origin) === 0 && el.setAttribute('src', src.replace(location.origin,''));
	        }
	    }
		function saveTxt(e) {
			if (!e.target.hasAttribute('cmstxt')) return;
			var el  = e.target;
			if (!el.isContentEditable && el.form === undefined) return;
			var tid = el.getAttribute('cmstxt');
	      	cleanUpEl(el);
			var v = isFormEl(el) ? el.value : el.innerHTML;
			$fn('cms::setTxt')(tid,v).run(); // run() before unload is done!!
		}

	  	/* listen for new contents */
		c1.onElement('.qgCmsPage .qgCmsCont',function(el){  // inside qgCmsPage ok?
			el.dispatchEvent(new CustomEvent('qgCmsCont.ready', {bubbles:true}));
		});

	});

})();
