/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';

c1.Placer = class {
    constructor(el, options={}){
        this.el = el;
        this.options = c1.ext({
            y: 'after',
            x: 'prepend',
            margin: 0
        }, options);
        this.positionize = this.positionize.bind(this);
    }
    follow(el){
        if (this.following === el) {
            //console.log('again!');
            return;
        }
        //let position = getComputedStyle(this.el).getPropertyValue('position');
        //if (position !== 'fixed') console.warn('use fixed!')
        this.following = el;
        if (!el) return;
        clearInterval(this.followInterval);
        this.followInterval = setInterval(this.positionize,200);
        addEventListener('resize',this.positionize,{passive:true});
        document.addEventListener('mousemove',this.positionize,{passive:true})
        document.addEventListener('mouseup',this.positionize,{passive:true})
        document.addEventListener('input',this.positionize,{passive:true, capture:true});
        document.addEventListener('scroll',this.positionize,{passive:true, capture:true});
        this.positionize();
    }
    positionize(){
        let run = this.following && this.el.parentNode && this.following.parentNode && this.el.offsetWidth && this.following.offsetWidth;
        if (!run) {
            this.following = null;
            clearInterval(this.followInterval);
            removeEventListener('resize',this.positionize,{passive:true});
            document.removeEventListener('mousemove',this.positionize,{passive:true})
            document.removeEventListener('mouseup',this.positionize,{passive:true})
            document.removeEventListener('input',this.positionize,{passive:true, capture:true});
            document.removeEventListener('scroll',this.positionize,{passive:true, capture:true});
        } else {
            this.toElement(this.following);
        }
    }
    toElement(el) {
        let rect = el.getBoundingClientRect();
        this.toClientRect(rect);
    }
    toClientRect(rect){
        if (this.options.margin) {
            let margin = this.options.margin;
            if (margin.top === void 0) {
                margin = {
                    top: margin,
                    left: margin,
                    bottom: margin,
                    right: margin,
                }
            }
            rect = {
                top:    rect.top - margin.top,
                bottom: rect.bottom + margin.bottom,
                left:   rect.left - margin.left,
                right:  rect.right + margin.right,
                height: rect.height + margin.top + margin.bottom,
                width:  rect.width + margin.left + margin.right,
            }
        }
        let viewport = { // viewport relative to the layer
            top: 0,
            left: 0,
        }
        // css-position
        let position = getComputedStyle(this.el).getPropertyValue('position');
        if (position !== 'fixed') {
            //if (position !== 'absolute') this.el.style.position = 'absolute';
            let root = c1.Placer.offsetParent(this.el);
            if (root) {
                if (!root) root = document.documentElement;
                viewport = root.getBoundingClientRect();
                rect = {
                    top:    rect.top    - viewport.top,
                    bottom: rect.bottom - viewport.top,
                    left:   rect.left   - viewport.left,
                    right:  rect.right  - viewport.left,
                    width:  rect.width,
                    height: rect.height,
                }
            }
        }
        // start
        var placeY = this.options.y;
        var placeX = this.options.x;
        var layerWidth  = this.el.offsetWidth; // scrollWidth?
        var layerHeight = this.el.offsetHeight;
        var innerWidth  = document.documentElement.clientWidth;
        var innerHeight = document.documentElement.clientHeight;
        var x = 0;
        var y = 0;
        if (placeX==='prepend') x = rect.left;
        if (placeX==='after')   x = rect.right;
        if (x + layerWidth + viewport.left > innerWidth) placeX = placeX === 'prepend' ? 'append' : 'before';
        if (placeX==='before')  x = rect.left  - layerWidth;
        if (placeX==='append')  x = rect.right - layerWidth;
        if (x < -viewport.left) x = placeX === 'before' ? rect.right : rect.left;
        if (placeX==='center')  x = rect.right - rect.width/2 - layerWidth/2;

        if (placeY==='prepend') y = rect.top;
        if (placeY==='after')   y = rect.bottom; // botton
        if (y + layerHeight + viewport.top > innerHeight) placeY = placeY === 'prepend' ? 'append' : 'before';
        if (placeY==='before')  y = rect.top    - layerHeight;
        if (placeY==='append')  y = rect.bottom - layerHeight;
        if (y < -viewport.top) y = placeY === 'before' ? rect.bottom : rect.top;
        if (placeY==='center')  y = rect.top + rect.height/2 - layerWidth/2;

        x = Math.c1Limit(x, -viewport.left, innerWidth  - viewport.left - layerWidth);
        y = Math.c1Limit(y, -viewport.top,  innerHeight - viewport.top  - layerHeight)

        if (position === 'absolute') {
            x += pageXOffset;
            y += pageYOffset;
        }

        if (this.options.use === 'transform') {
            this.el.style.transform  = 'translate('+x+'px,'+y+'px)';
        } else {
            this.el.style.top  = y + 'px';
            this.el.style.left = x + 'px';
        }
    }
    static offsetParent(el){
        var parent = el.offsetParent;
        if (parent === document.body) {
            let position = getComputedStyle(parent).getPropertyValue('position');
            if (position === 'static') {
                parent = null;
            }
        }
        return parent;
    }
}
