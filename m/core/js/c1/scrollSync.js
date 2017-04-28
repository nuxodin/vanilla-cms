'use strict';
{
    c1.scrollSync = {
        config : {},
        _elementConfig(el){
            return {
                [getSelector(el)] : {
                    // pixel: {
                    //     x: el.scrollLeft,
                    //     y: el.scrollTop,
                    // },
                    percent: {
                        x: el.scrollLeft / (el.scrollWidth  - el.clientWidth) || 0,
                        y: el.scrollTop  / (el.scrollHeight - el.clientHeight) || 0,
                    }
                }
            }
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
                    left = (el.scrollWidth  - el.clientWidth)  * percent.x;
                    top  = (el.scrollHeight - el.clientHeight) * percent.y;
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
        if (!doc.c1ScrollSyncConfig) doc.c1ScrollSyncConfig = {};
        for (let selector in config) {
            doc.c1ScrollSyncConfig[selector] = config[selector];
        }
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
    function getSelector(el){
        let doc = el.ownerDocument;
        let selector = '';
        let root = el.closest('[id]') || doc.body;
        var looped = el;
        while (looped != root) {
            selector = ' > :nth-child('+countPrevSiblings(looped)+')' + selector;
            looped = looped.parentNode;
        }
        selector = (root === doc.body ? 'body' : '#'+root.id) + selector;
        return selector;
    }
    function countPrevSiblings(el){
        let i=0, checked = el;
        while (checked) {
            i++;
            checked = checked.previousElementSibling;
        }
        return i;
    }
}
