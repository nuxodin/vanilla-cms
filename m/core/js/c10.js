/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(w,undf,k) { 'use strict';

	/* Frequently used and small polyfills */
	if (!HTMLFormElement.prototype.reportValidity) { // ie 11, edge 14, safari 10
	    HTMLFormElement.prototype.reportValidity = function() {
			if (this.checkValidity()) return true;
			var btn = document.createElement('button');
			this.appendChild(btn);
			btn.click();
			this.removeChild(btn);
			return false;
	    }
	}
	/* Waits for the execution of the function (min) and then executes the last call, but waits maximal (max) millisecunds.
	*  If the function-scope changes, the function executes immediatly (good for event-delegation)
	*/
	Function.prototype.c1Debounce = function(options) {
		if (typeof options === 'number') options = {min:options, max:options*2};
		var fn = this,
			inst,
			args,
			timerMin = 0,
			timerMax = 0,
			triggered = true,
		    trigger = function() {
		        triggered = true;
		        clearTimeout( timerMax );
		        clearTimeout( timerMin );
		        timerMax = 0;
		        fn.apply(inst,args);
		    };
	    return function() {
	        inst !== this && !triggered && trigger();
	        triggered = false;
	        inst = this;
	        args = arguments;
	        clearTimeout(timerMin);
	        timerMin = setTimeout(trigger, options.min);
	        !timerMax && options.max && (timerMax = setTimeout(trigger, options.max));
	    };
	};
    if (!RegExp.escape) {
        RegExp.escape = function(text) {
            return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
        };
    }
    Math.c1Limit = function(number,min,max) {
        return Math.min( Math.max( parseFloat(min) , parseFloat(number) ), parseFloat(max) );
    };

	w.c1 = w.c1 || {};

	/* eventer */
	c1.Eventer = {
	    _getEvents : function(n) {
	        !this._Es && (this._Es={});
	        !this._Es[n] && (this._Es[n]=[]);
	        return this._Es[n];
	    },
		on: function(ns, fn) {
	    	ns = ns.split(' ');
	    	for (var i=0, n; n = ns[i++];) {
		        this._getEvents(n).push(fn);
	    	}
	    },
		off: function(ns, fn) {
	    	ns = ns.split(' ');
	    	for (var i=0, n; n = ns[i++];) {
		        var Events = this._getEvents(n);
		        Events.splice(Events.indexOf(fn) ,1);
	    	}
	    },
		trigger: function(ns, e) {
            var self = this, n, i=0;
            ns = ns.split(' ');
            while (n = ns[i++]) {
                this._getEvents(n).forEach(function(Event) {
                    Event.call(self,e);
                });
	    	}
	    }
	};
	/* ext */
	c1.ext = function (src, target, force, deep) {
	    target = target || {};
	    for (k in src) {
	    	if (!src.hasOwnProperty(k)) continue;
	        if (force || target[k] === undf) {
	            target[k] = src[k];
	        }
			if (!deep) continue;
			if (typeof k === 'string') continue;
	        c1.ext(src[k], target[k], force, deep);
	    }
        return target;
	};

	var dataEl = document.querySelector('script[type="json/c1"]');
	if (dataEl) {
		var data = JSON.parse(dataEl.textContent);
		c1.ext(data, window, false, true);
	}

}(this);

// c1Use
!function(global, undefined) {
	'use strict';
	var CALLBACKS = 'pseudosymbol_&/%f983';
    global.c1Use = function (prop_or_opts, cb) {
		var scope = prop_or_opts.scope || this || self;
		var prop = prop_or_opts.property || prop_or_opts;
        if (prop in scope && scope[prop] !== void 0) { // loadet? // (test if it is the depencency setter)
        	cb && cb.call(scope, scope[prop]);
			return scope[prop];
        }
    	var callbacks = scope[CALLBACKS] || ( scope[CALLBACKS] = {} );
    	if (callbacks[prop] && cb) { // is it loading? (and async)
    	    callbacks[prop].push(cb);
    	} else { // load!
			var src = (prop_or_opts.from || scope.c1UseSrc) + '/' +prop;
    		callbacks[prop] = [cb];
			var onload = function(e) {
                function runCallbacks(){
                    var fn, object = c1Use.able(scope,prop);
    				if (e.type === 'error') object.c1UseFailed = true;
    				//object.c1UseSrc = src; // neu. why? ist von c1Use.able bereits gesetzt !?
                	while (fn = callbacks[prop].shift()) fn.call(scope, object);
                }
                if (prop in scope || prop_or_opts.from) {
                    // property gesetzt oder per url (from) geladen
                    runCallbacks();
                } else {
                    // script geladen, aber darin wurde die property noch nicht gesetzt
                    Object.defineProperty(scope,prop,{
                        set: function(value){
                            delete this[prop];
                            this[prop] = value;
                            setTimeout(runCallbacks);
                            // setTimeout(function(){ // zzz?
                            //     runCallbacks();
                            // });
                        },
                        configurable: true
                    });
                }
            };
			(cb ? loadScript : loadScriptSync)(src+'.js?c1Use_'+moduleAge, onload, onload);
    	}
    	return cb ? null : scope[prop];
    };

    /* multiple and path
     * extend c1Use so it can have an array as first arguments
     * */
    c1Use = function (use) {
    	var fn = function (props, cb) {
    		var scope = this || self, i, prop;
    		if (!scope.c1UseSrc) { throw new Error("c1Use: the Object needs a c1UseSrc property!"); }
            if (props instanceof Array) {
            	var returns = [], index=0, counter=0;
            		while (prop = props[index++]) {
            			c1Use.call(scope, prop, function(index) {
            				var fn = function(res) {
            				counter++;
            					returns[index-1] = res;
            					if (props.length === counter) cb.apply(scope, returns);
            				};
            				return fn;
            			}(index));
            		}
            } else if (typeof props === 'string') {
            	if (props.indexOf('/') === 0) { // neu beta
            		var parts = props.match(/(.*)\/([^\/]*)\..+$/);
            		return use.call(scope, {from:parts[1], property:parts[2]}, cb);
            	} else {
                    // parts ("jQuery.fn.velocity")
                    var parts = props.split(/\./g), part;
                    i=0;
                    prop = parts.pop();
                    while (part = parts[i++]) {
                        c1Use.able(scope, part);
                        scope = scope[part];
                    }
            		return use.call(scope, prop, cb);
            	}
            } else {
            	//console.log('todo?', props) // todo?
            	return use.call(scope, props, cb);
            }
    	};
    	return fn;
    } (c1Use);

    /* return Promise */
    c1Use = function (use) {
    	var fn = function (props,cb) {
            var scope = this;
            if (!cb) return use.call(scope, props);
            var p = new Promise(function(resolve, reject) {
                use.call(scope, props, function(returns){
                    cb.apply && cb.apply(scope, arguments);
                    resolve(arguments);
                    //reject('todo');
                });
            });
            return p;
    	};
    	return fn;
    } (c1Use);

    //var hasOwn = Object.prototype.hasOwnProperty;
    /* make the object useable */
    c1Use.able = function (obj, prop) {
        if (obj[prop] === undefined) obj[prop] = {};
        obj[prop].c1Use    = c1Use;
        obj[prop].c1UseSrc = obj.c1UseSrc + '/' + prop;
        return obj[prop];
    };
    c1Use.addGetter = function (obj, prop) {
    	if (obj.hasOwnProperty(prop)) return;
    	//if (hasOwn.call(obj, prop)) return;
        /* other libaries should check properties like so: if (prop in obj) { ... }; so the getter will not fire */
        Object.defineProperty(obj, prop, {
    		configurable: true,
    		get: function() {
        		delete obj[prop];
        		return c1Use.call(this, prop);
            },
    		set: function(v) {
        		delete obj[prop];
        		obj[prop] = v;
    		}
    	});
    };
    /* browser! */
	var d = document;
    function loadScript(path, cb, eb) {
        var elem = d.createElement('script');
        elem.async   = false;
        elem.src     = path;
        elem.onload  = cb;
        elem.onerror = eb;
        d.documentElement.firstChild.appendChild(elem);
    }
    function loadScriptSync(path, cb, eb) {
        var request = new XMLHttpRequest();
        request.open('GET', path, false);
        request.send(null);
        if (request.status === 200) {
            var elem = d.createElement('script');
            elem.text = request.responseText;
            d.documentElement.firstChild.appendChild(elem);
            elem.setAttribute('data-c1-src',path);
            cb({type:'load'});
        } else {
            eb({type:'error'});
        }
        console.warn('deprecated to load '+path+' sync');
    }
    if (!global.c1UseSrc) {
        var tmp = d.getElementsByTagName('script');
        tmp = tmp[tmp.length-1];
        global.c1UseSrc = tmp.getAttribute('src').replace(/[^\/]+$/,'');
    }

    c1.import = function(path) {
        let entry = window.c1.import.__db[path];
        if (entry === undefined) {
            const escape = path.replace("'", "\\'");
            const script = Object.assign(document.createElement('script'), {
                type: 'module',
                textContent: 'import * as x from "'+escape+'"; c1.import.__db["'+escape+'"].resolve(x);',
            });
            entry = c1.import.__db[path] = {};
            entry.promise = new Promise(function(resolve, reject){
                entry.resolve = resolve;
                script.onerror = reject;
            });
            document.head.appendChild(script);
            script.remove();
        }
        return entry.promise;
    };
    c1.import.__db = {};


    /*
    //ussage with jQuery:
    c1Use.able(window.'jQuery');
    c1Use.able(jQuery,'fn');
    //----------------
    //sync:
    c1Use.addGetter(jQuery.fn,'myplugin');
    $('#test').myplugin();
    //----------------
    //or async:
    $('#text').c1Use('myplugin', function() {
    	this.myplugin();
    })
    */
    var w = global;
    c1Use.able(window,'c1');

    // divers fix
    c1Use.able(c1,'fix');
    function loadFix(lib) {
        (lib in w) || d.write('<script src="'+c1.c1UseSrc+'/fix/'+lib+'.js"><\/script>');
    }
    if (!w.MutationObserver || !w.requestAnimationFrame || !w.WeakMap || !Array.prototype.includes) loadFix('oldies');
    loadFix('Promise');
    loadFix('fetch');


}(this);
