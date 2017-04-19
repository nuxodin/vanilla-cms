/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

(function(){
    'use strict';

    var listeners = [],
        root = document.documentElement,
        Observer;

    c1.onElement = function(selector, fn) {
        var listener = {
            selector: selector,
            fn: fn,
            elements: new WeakMap(), // WeakSet would be better, but no support in ie11
        };

        var els = root.querySelectorAll(listener.selector), i=0, el;
        while (el = els[i++]) {
            listener.elements.set(el,true);
            listener.fn.call(el, el);
        }

        listeners.push(listener);
        if (!Observer) {
            Observer = new MutationObserver(checkMutations);
            Observer.observe(root, {
                childList: true,
                subtree: true
            });
        }
        checkListener(listener);
        //return listener; // todo?
    }
    function checkListener(listener, target) {
        var i=0, el, els = [];
        target && target.matches(listener.selector) && els.push(target);
        if (loaded) { // ok? check inside node on innerHTML - only when loaded
            Array.prototype.push.apply(els, (target||root).querySelectorAll(listener.selector));
        }
        while (el = els[i++]) {
            if (listener.elements.has(el)) continue;
            listener.elements.set(el,true);
            listener.fn.call(el, el);
        }
    }
    function checkListeners(inside) {
        var i=0, listener;
        while (listener = listeners[i++]) checkListener(listener, inside);
    }
    function checkMutations(mutations) {
        var j=0, i, mutation, nodes, target;
        while (mutation = mutations[j++]) {
            nodes = mutation.addedNodes, i=0;
            if (!nodes.length) continue;
            while (target=nodes[i++]) {
                target.nodeType === 1 && checkListeners(target);
            }
        }
    }

    var loaded = false;
    document.addEventListener('DOMContentLoaded',function(){
        loaded = true;
    })
})();
