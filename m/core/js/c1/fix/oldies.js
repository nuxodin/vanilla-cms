!function(){
    var w = window;
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

}();
