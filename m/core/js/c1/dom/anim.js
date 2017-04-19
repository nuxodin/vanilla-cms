!function() {

	c1.dom.anim = function(el, styles, duration, easing) {
		easing = easings[easing] ? easing : 'swing';
		duration === undefined && (duration = 300);
		var transitions = [];
		var stylesBefore = {};

		for (var i in styles) {
			var anim = i;
			if (styleObj[anim] === undefined) {
				if (c1.dom.css.hooks[anim]) {
					anim = c1.dom.css.hooks[i].anim;
				} else {
					anim = c1.dom.css.experimental(i);
				}
			}
			var value = styles[i];
			if (value instanceof Array) {
				stylesBefore[i] = value[0];
				styles[i] = value[1];
			}
			transitions.push( anim+' '+duration+'ms'+' '+easings[easing] );
		}

		el.style[vendorTransition] = ''; // better "none"?

		c1.dom.css(el,stylesBefore);

		if (!vendorTransition) {
		    easing = jQuery.easing[easing] ? easing : 'swing';
		    for (i in styles) {
		    	if (!styleObj[i] && c1.dom.css.hooks[i]) {
		    		anim = c1.dom.css.hooks[i].anim;
		    		styles[anim] = styles[i];
		    	}
		    }
			return $(el).animate( styles,{duration:duration,queue:false,easing:easing} );
		}

		setTimeout(function() {
			el.style[vendorTransition] = transitions.join();
			c1.dom.css(el,styles);
		}, 20); // firefox (portfolio slider "loading.end");
	};

	var CUBIC_BEZIER_OPEN = 'cubic-bezier(',
		CUBIC_BEZIER_CLOSE = ')',
		easings = {
			bounce: CUBIC_BEZIER_OPEN + '0.0, 0.35, .5, 1.3' + CUBIC_BEZIER_CLOSE,
			linear: 'linear',
			swing: 'ease-in-out',
			// Penner equation approximations from Matthew Lein's Ceaser: http://matthewlein.com/ceaser/
			easeInQuad:     CUBIC_BEZIER_OPEN + '0.550, 0.085, 0.680, 0.530' + CUBIC_BEZIER_CLOSE,
			easeInCubic:    CUBIC_BEZIER_OPEN + '0.550, 0.055, 0.675, 0.190' + CUBIC_BEZIER_CLOSE,
			easeInQuart:    CUBIC_BEZIER_OPEN + '0.895, 0.030, 0.685, 0.220' + CUBIC_BEZIER_CLOSE,
			easeInQuint:    CUBIC_BEZIER_OPEN + '0.755, 0.050, 0.855, 0.060' + CUBIC_BEZIER_CLOSE,
			easeInSine:     CUBIC_BEZIER_OPEN + '0.470, 0.000, 0.745, 0.715' + CUBIC_BEZIER_CLOSE,
			easeInExpo:     CUBIC_BEZIER_OPEN + '0.950, 0.050, 0.795, 0.035' + CUBIC_BEZIER_CLOSE,
			easeInCirc:     CUBIC_BEZIER_OPEN + '0.600, 0.040, 0.980, 0.335' + CUBIC_BEZIER_CLOSE,
			easeInBack:     CUBIC_BEZIER_OPEN + '0.600, -0.280, 0.735, 0.045' + CUBIC_BEZIER_CLOSE,
			easeOutQuad:    CUBIC_BEZIER_OPEN + '0.250, 0.460, 0.450, 0.940' + CUBIC_BEZIER_CLOSE,
			easeOutCubic:   CUBIC_BEZIER_OPEN + '0.215, 0.610, 0.355, 1.000' + CUBIC_BEZIER_CLOSE,
			easeOutQuart:   CUBIC_BEZIER_OPEN + '0.165, 0.840, 0.440, 1.000' + CUBIC_BEZIER_CLOSE,
			easeOutQuint:   CUBIC_BEZIER_OPEN + '0.230, 1.000, 0.320, 1.000' + CUBIC_BEZIER_CLOSE,
			easeOutSine:    CUBIC_BEZIER_OPEN + '0.390, 0.575, 0.565, 1.000' + CUBIC_BEZIER_CLOSE,
			easeOutExpo:    CUBIC_BEZIER_OPEN + '0.190, 1.000, 0.220, 1.000' + CUBIC_BEZIER_CLOSE,
			easeOutCirc:    CUBIC_BEZIER_OPEN + '0.075, 0.820, 0.165, 1.000' + CUBIC_BEZIER_CLOSE,
			easeOutBack:    CUBIC_BEZIER_OPEN + '0.175, 0.885, 0.320, 1.275' + CUBIC_BEZIER_CLOSE,
			easeInOutQuad:  CUBIC_BEZIER_OPEN + '0.455, 0.030, 0.515, 0.955' + CUBIC_BEZIER_CLOSE,
			easeInOutCubic: CUBIC_BEZIER_OPEN + '0.645, 0.045, 0.355, 1.000' + CUBIC_BEZIER_CLOSE,
			easeInOutQuart: CUBIC_BEZIER_OPEN + '0.770, 0.000, 0.175, 1.000' + CUBIC_BEZIER_CLOSE,
			easeInOutQuint: CUBIC_BEZIER_OPEN + '0.860, 0.000, 0.070, 1.000' + CUBIC_BEZIER_CLOSE,
			easeInOutSine:  CUBIC_BEZIER_OPEN + '0.445, 0.050, 0.550, 0.950' + CUBIC_BEZIER_CLOSE,
			easeInOutExpo:  CUBIC_BEZIER_OPEN + '1.000, 0.000, 0.000, 1.000' + CUBIC_BEZIER_CLOSE,
			easeInOutCirc:  CUBIC_BEZIER_OPEN + '0.785, 0.135, 0.150, 0.860' + CUBIC_BEZIER_CLOSE,
			easeInOutBack:  CUBIC_BEZIER_OPEN + '0.680, -0.550, 0.265, 1.550' + CUBIC_BEZIER_CLOSE
		},
		styleObj = document.documentElement.style,
		vendorTransition = c1.dom.css.experimental('transition');


	/**
	// stop the transition
    el.style.transition = 'left 3s';
    el.style.left = '500px';
    setTimeout(function() {
        el.style.left = getComputedStyle(el)['left'];
    }, 1500);

	// chrome gos to the end of the transition / others stops the transition
    el.style.transition = 'left 3s';
    el.style.left = '500px';
    setTimeout(function() {
    	el.style.transition = '';
    }, 1500);
    /**/

}();
