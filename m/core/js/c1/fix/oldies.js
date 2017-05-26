!function(){
    var w = window;
    var define = Object.defineProperty;
    // date
    if (!Date.now) Date.now = function() { return new Date().getTime(); };
    // requestAnimationFrame
    if (!w.requestAnimationFrame) {
        requestAnimationFrame = w.webkitRequestAnimationFrame || w.mozRequestAnimationFrame || (function () {
    		var clock = Date.now();
    		return function (callback) {
    			var currentTime = Date.now();
    			if (currentTime - clock > 16) {
    				clock = currentTime;
    				callback(currentTime);
    			} else {
    				setTimeout(function () {
    					polyfill(callback);
    				}, 0);
    			}
    		};
    	})();
    }
    // MutationObserver
    if (!w.MutationObserver) {
        MutationObserver = w.WebKitMutationObserver;
    }
    // WeakMap (object as key only)
    if (!w.WeakMap) {
        WeakMap = function(){
            this._weakmap_entry_ = ''+Math.random();
        }
        WeakMap.prototype = {
            get:function(key){ return key[this._weakmap_entry_]; },
            has:function(key){ return this._weakmap_entry_ in key; },
            set:function(key,value){
                define(key, this._weakmap_entry_, {
                    configurable: true,
                    value:value
                });
                return this;
            },
            delete:function(key){
                var had = this.has(key);
                delete key[this._weakmap_entry_];
                return had;
            },
        }
    }
}();
