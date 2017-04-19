/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

setTimeout(function(){alert('used'); throw('c1.dom used?');});

c1.dom = {
    id: function(id) {
    	return id.getAttribute ? id : document.getElementById(id+'');
    }
};

!function() {
	/* style */
    var styleObj = document.documentElement.style;
    var vendors = {'moz':1,'webkit':1,'ms':1,'o':1};
    c1.dom.css = function(el, style, value) {
        if (value === undefined) {
            if (typeof style === 'string') {
                // getter
                if (styleObj[style] !== undefined) return getComputedStyle(el).getPropertyValue(style);
                if (c1.dom.css.hooks[style])       return c1.dom.css.hooks[style].get(el);
                return getComputedStyle(el).getPropertyValue( c1.dom.css.experimental(style) );
            }
            for (var i in style) this.css(el,i,style[i]);
        } else {
            // setter
            if (styleObj[style] !== undefined) {
                el.style[style] = value;
            } else if (c1.dom.css.hooks[style]) {
                c1.dom.css.hooks[style].set(el,value);
            } else {
                el.style[c1.dom.css.experimental(style)] = value;
            }
        }
    };

    var vendor = false;
    c1.dom.css.experimental = function(style) {
        if (styleObj[style] !== undefined) return style;
        if (vendor) return styleObj['-'+vendor+'-'+style] !== undefined ? '-'+vendor+'-'+style : undefined;
        for (var v in vendors) {
            if (styleObj['-'+v+'-'+style] !== undefined) {
                vendor = v;
                return '-'+vendor+'-'+style;
            }
        }
    };
    c1.dom.css.matrix = function(el) {
        var val = c1.dom.css(el, this.experimental('transform') );
        return val.substr(7, val.length - 8).split(', ');
    };

    var vendorTransform = c1.dom.css.experimental('transform');
    function setMatrixAtPos(el,pos,value) {
        value = (''+value).match(/%/) ? (el.parentNode.offsetWidth / 100) * parseInt(value) : value;
        var mStr = c1.dom.css(el, vendorTransform);
        var mArr = mStr === 'none' ? [1,0,0,1,0,0] : mStr.substr(7, mStr.length - 8).split(', ');
        mArr[pos] = value;
        mStr = 'matrix('+mArr.join(', ')+')';
        el.style[vendorTransform] = mStr;
    }
    function getMatrixAtPos(el,pos) {
        var mStr = c1.dom.css(el, vendorTransform);
        if (mStr === 'none') return 0;
        var mArr = mStr.substr(7, mStr.length - 8).split(', ');
        return parseInt(mArr[pos]);
    }

    var useTransf = c1.dom.css.experimental('transition') && vendorTransform;
    c1.dom.css.hooks = {
        'c1-x': {
            set: function(el,v) {
                if (useTransf)  setMatrixAtPos(el,4,v);
                else el.style.left = (''+v).match(/[^-0-9\.]/) ? v : v+'px';
            },
            get: function(el) {
                if (useTransf) return getMatrixAtPos(el,4);
                else return el.style.pixelLeft || parseInt( $(el).css('left'));
            },
            anim: useTransf ? vendorTransform : 'left'
        },
        'c1-y': {
            set: function(el, v) {
                if (useTransf) setMatrixAtPos(el,5,v);
                else el.style.left = (''+v).match(/[^0-9]/) ? v : v+'px';
            },
            get: function(el) {
                if (useTransf) return getMatrixAtPos(el,5);
                else return el.style.pixelTop || parseInt( $(el).css('top'));
            },
            anim: useTransf ? vendorTransform : 'right'
        }
    };
}();
