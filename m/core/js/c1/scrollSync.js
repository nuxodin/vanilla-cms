/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
// better known as scrollRestoration: https://www.chromestatus.com/feature/5657284784947200
'use strict';
{
    c1.scrollSync = {
        config : {},
        _elementConfig(el){
			let client = clientDim(el);
            let selector = c1.scrollSync.getSelector(el);
            let config = {
                // pixel: {
                //     x: el.scrollLeft,
                //     y: el.scrollTop,
                // },
                percent: {
                    x: el.scrollLeft / (el.scrollWidth  - client.width) || 0,
                    y: el.scrollTop  / (el.scrollHeight - client.height) || 0,
                }
            };
            let doc = el.ownerDocument;
            if (!doc.c1ScrollSyncConfig) doc.c1ScrollSyncConfig = {};
            doc.c1ScrollSyncConfig[selector] = config;
            return { [selector] : config }
        },
        restoreIn(object, win){
            for (let selector in object) {
                let el = win.document.querySelector(selector);
                if (!el) continue;
                let top, left;
                // let pixel = object[selector].pixel;
                // if (pixel) {
                //     left = pixel.x
                //     top  = pixel.y
                // }
                let percent = object[selector].percent;
                if (percent) {
					let client = clientDim(el);
                    left = (el.scrollWidth  - client.width)  * percent.x;
                    top  = (el.scrollHeight - client.height) * percent.y;
                }
                top  = Math.round(top);
                left = Math.round(left);

				win.c1ScrollSyncPreventFeedback = true;

                if (el.scrollLeft !== left) el.scrollLeft = left;
                if (el.scrollTop  !== top)  el.scrollTop  = top;

                win.clearTimeout(win.c1ScrollSyncPreventFeedbackTimeout);
                win.c1ScrollSyncPreventFeedbackTimeout = win.setTimeout(function(){
                    win.c1ScrollSyncPreventFeedback = false;
                },100)
            }
        },
        syncWindows(fromWindow, toWindow){
            let doc = fromWindow.document;
            if (!doc.c1ScrollSyncTargetWindows) doc.c1ScrollSyncTargetWindows = [];
            doc.c1ScrollSyncTargetWindows.push(toWindow); // massive memory leak? use weekmap?
            fromWindow.addEventListener('scroll',scrollListener,true);
        },
        reevaluate(win){
            var all = win.document.querySelectorAll('*')
            for (var i=0, el; el=all[i++];) {
                if (el.scrollTop || el.scrollLeft) {
                    this._elementConfig(el)
                }
            }
        },
        getConfig(win){
            var doc = win.document;
            if (!doc.c1ScrollSyncConfig) doc.c1ScrollSyncConfig = {};
            return doc.c1ScrollSyncConfig;
        }
    }
    function scrollListener(e) {
        let el = e.target;
        if (el.nodeType === 9) el = el.scrollingElement; // document
        let config = c1.scrollSync._elementConfig(el);
        let doc = el.ownerDocument;
        if (doc.defaultView.c1ScrollSyncPreventFeedback) return;

        if (doc.c1ScrollSyncTargetWindows) {
            doc.c1ScrollSyncTargetWindows.forEach(win=>{
                c1.scrollSync.restoreIn(config, win);
            })
        }

        // localStorage
        //localStorage.setItem('c1.scrollSync', JSON.stringify(c1.scrollSync.config));
    }
    addEventListener('scroll', scrollListener, true)

    // addEventListener('DOMContentLoaded',function(){
    //     c1.scrollSync.config = JSON.parse( localStorage.getItem('c1.scrollSync') ) || {};
    //     c1.scrollSync.restore(c1.scrollSync.config);
    // });
    // addEventListener('load',function(){
    //     c1.scrollSync.restore(c1.scrollSync.config);
    // });

    /* helper */
    c1.scrollSync.getSelector = function(el){
        let doc = el.ownerDocument;
        let selector = '';
        let root = el.closest('[id]') || doc.documentElement;
        var looped = el;
        while (looped != root) {
            selector = ' > '+looped.tagName.toLowerCase()+':nth-of-type('+countPrevSiblings(looped)+')' + selector;
            looped = looped.parentNode;
        }
        selector = (root === doc.documentElement ? 'html' : '#'+root.id) + selector;
        return selector;
    }
    function countPrevSiblings(el){
        let i=0, checked = el, tag = el.tagName;
        while (checked) {
            if (checked.tagName === tag) i++;
            checked = checked.previousElementSibling;
        }
        return i;
    }
    function clientDim(el) {
		if (el = el.ownerDocument.scrollingElement) {
			return {
				height:innerHeight,
				width: innerWidth,
			}
		}
		return {
			height:el.clientHeight,
			width: el.clientWidth,
		}
	}
}
