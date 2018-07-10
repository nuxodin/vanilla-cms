/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(){ 'use strict';

var w = window,
    define = Object.defineProperty;
// date
if (!Date.now) Date.now = function() { return new Date().getTime(); };
// requestAnimationFrame
if (!w.requestAnimationFrame) {
    var last_RAF_Time = 0;
    requestAnimationFrame = w.webkitRequestAnimationFrame || w.mozRequestAnimationFrame || function(callback) {
        var currTime = new Date().getTime();
        var timeToCall = Math.max(0, 16 - (currTime - last_RAF_Time));
        var id = window.setTimeout(function() { callback(currTime + timeToCall); }, timeToCall);
        last_RAF_Time = currTime + timeToCall;
        return id;
    };
}
if (!w.cancelAnimationFrame) {
    cancelAnimationFrame = w.webkitCancelAnimationFrame || w.mozCancelAnimationFrame || function(id) { clearTimeout(id); };
}
// MutationObserver
if (!w.MutationObserver) {
    MutationObserver = w.WebKitMutationObserver;
}
// WeakMap (object as key only)
if (!w.WeakMap) {
    WeakMap = function(){
        this._weakmap_entry_ = ''+Math.random();
    };
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
    };
}


}();
