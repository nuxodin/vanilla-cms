
function q1CssText(style) {
	'use strict';

	var props = {};
	$.each(style,function(i,r) {

		/* moz */
		if (r=='padding-right-value') { r = 'padding-right'; }
		if (r=='padding-left-value')  { r = 'padding-left'; }
		if (r=='padding-right-ltr-source') return;
		if (r=='padding-left-ltr-source')  return;
		if (r=='padding-right-rtl-source') return;
		if (r=='padding-left-rtl-source')  return;

		if (r=='margin-right-value') { r = 'margin-right'; }
		if (r=='margin-left-value')  { r = 'margin-left'; }
		if (r=='margin-right-ltr-source') return;
		if (r=='margin-left-ltr-source')  return;
		if (r=='margin-right-rtl-source') return;
		if (r=='margin-left-rtl-source')  return;

		/* chrome */
		if (r=='background-repeat-x') { r = 'background-repeat'; }
		if (r=='background-repeat-y') return;
		if (r=='background-position-x') { r = 'background-position'; } /* firefox has no packground-position-x/y */
		if (r=='background-position-y') return;

		var value = style.getPropertyValue(r);

        if (r === 'text-decoration' && value === 'initial') value = 'none';
        if (r === 'transition-duration'                && value === 'initial') return;
        if (r === 'transition-delay'                   && value === 'initial') return;
        if (r === 'transition-timing-function'         && value === 'initial') return;

		if (value==='initial') { /*return; // why uncommented?*/ }
		if (value==='initial' && r.match(/border-image-/)) return;

        if (r==='content') { // buggy chrome
            var nParts = [];
            value.split(/,/).forEach(function(part) {
                part = part.trim();
                nParts.push( part.match(/^('|"|attr|url)/) ? part : "'"+part+"'" );
            });
            value = nParts.join(' ');
        }
		if (value===null) return;


		if (r=='background-image' && value.match(/-gradient/)) {
			var gradient = value.replace(/^-[a-z]+-(.*)/,'$1');
			value = '-webkit-'+gradient+';';
			var colors = value.match(/#[0-9abcdef]{3,6}|rgb[a]?\([^)]+\)/g); /* ie */
			if (colors) {
				var x = colors[0].match(/\(.*,.*,.*,(.*)\)/);
				var a1 = x ? Math.round(parseFloat(x[1].trim())*255).toString(16) : 'ff';
				x = colors[1].match(/\(.*,.*,.*,(.*)\)/);
				var a2 = x ? Math.round(parseFloat(x[1].trim())*255).toString(16) : 'ff';
				colors[0] = tinycolor(colors[0]).toHex();
				colors[1] = tinycolor(colors[1]).toHex();
				if (colors[0].length===3) { colors[0] = colors[0][0]+colors[0][0]+colors[0][1]+colors[0][1]+colors[0][2]+colors[0][2]; }
				if (colors[1].length===3) { colors[1] = colors[1][0]+colors[1][0]+colors[1][1]+colors[1][1]+colors[1][2]+colors[1][2]; }
				//value += "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#"+a1+colors[0]+"', endColorstr='#"+a2+colors[1]+"');";
			}
			value += 'background-image:-moz-'+gradient+'; background-image:'+gradient.replace('top','to bottom');
		}
        if (r.match(/background/) && value.match(/url\(/)) {
            var regexp = new RegExp( 'http[s]?:\/\/'+RegExp.escape(location.host) );
            if (value.match(regexp)) {
                value = value.replace(regexp,'');
            }
        }
        if (value.match(/rgba\(/)) {
            value = value.replace(/(rgba\([^,]+,[^,]+,[^,]+,)([^)]+\))/g,function(o,m1,m2) {
                var alpha = Math.round( 1000*parseFloat(m2) ) / 1000;
                return m1+alpha+')';
            });
        }

		var camelCase = $.camelCase(r);
		var def = qgCssProps[camelCase];
		var needsPrefix = def ? def.vendorPrefix : false;
		if (r.match(/^-[a-z]+-/)) {
			r = r.replace(/^-[a-z]+-/,'');
			needsPrefix = true;
		}
		if (needsPrefix) {
			props['-webkit-'+r] = value;
			props['-moz-'+r] = value;
			props['-ms-'+r] = value;
		}
		props[r] = value;
	});

	if ( props['overflow-y'] !== undefined && props['overflow-y'] === props['overflow-x'] ) {
		props['overflow'] = props['overflow-y'];
		delete props['overflow-y'];
		delete props['overflow-x'];
	}

    if (props['background-repeat'] === 'no-repeat no-repeat') { props['background-repeat'] = 'no-repeat'; }
    if (props['background-repeat'] === 'no-repeat repeat'  ) { props['background-repeat'] = 'repeat-y'; }
    if (props['background-repeat'] === 'repeat no-repeat'  ) { props['background-repeat'] = 'repeat-x'; }

	if (
		props['padding-top'] !== undefined
		&& props['padding-left'] !== undefined
		&& props['padding-bottom'] !== undefined
		&& props['padding-right'] !== undefined
	) {
		if (props['padding-top']===props['padding-bottom'] && props['padding-left']===props['padding-right']) {
			if (props['padding-top']===props['padding-left']) {
				props['padding'] = props['padding-top'];
			} else {
				props['padding'] = props['padding-top']+' '+props['padding-right'];
			}
		} else {
			props['padding'] = props['padding-top']+' '+props['padding-right']+' '+props['padding-bottom']+' '+props['padding-left'];
		}
		delete props['padding-top'];
		delete props['padding-left'];
		delete props['padding-bottom'];
		delete props['padding-right'];
	}

	if (props['margin-top'] !== undefined && props['margin-left'] !== undefined && props['margin-bottom'] !== undefined && props['margin-right'] !== undefined) {
		if (props['margin-top']===props['margin-bottom'] && props['margin-left']===props['margin-right']) {
			if (props['margin-top']===props['margin-left']) {
				props['margin'] = props['margin-top'];
			} else {
				props['margin'] = props['margin-top']+' '+props['margin-right'];
			}
		} else {
			props['margin'] = props['margin-top']+' '+props['margin-right']+' '+props['margin-bottom']+' '+props['margin-left'];
		}
		delete props['margin-top'];
		delete props['margin-left'];
		delete props['margin-bottom'];
		delete props['margin-right'];
	}

	/* border */
	var singleStyle, singleWidth, singleColor;
	if (props['border-top-style'] !== undefined && props['border-right-style'] !== undefined && props['border-bottom-style'] !== undefined && props['border-left-style'] !== undefined) {
		if (props['border-top-style']===props['border-bottom-style'] && props['border-left-style']===props['border-right-style']) {
			if (props['border-top-style']===props['border-left-style']) {
				props['border-style'] = props['border-top-style'];
				singleStyle = 1;
			} else {
				props['border-style'] = props['border-top-style']+' '+props['border-right-style'];
			}
		} else {
			props['border-style'] = props['border-top-style']+' '+props['border-right-style']+' '+props['border-bottom-style']+' '+props['border-left-style'];
		}
		delete props['border-top-style'];
		delete props['border-right-style'];
		delete props['border-bottom-style'];
		delete props['border-left-style'];
	}
	if (props['border-top-color'] !== undefined && props['border-right-color'] !== undefined && props['border-bottom-color'] !== undefined && props['border-left-color'] !== undefined) {
		if (props['border-top-color']===props['border-bottom-color'] && props['border-left-color']===props['border-right-color']) {
			if (props['border-top-color']===props['border-left-color']) {
				props['border-color'] = props['border-top-color'];
				singleColor = 1;
			} else {
				props['border-color'] = props['border-top-color']+' '+props['border-right-color'];
			}
		} else {
			props['border-color'] = props['border-top-color']+' '+props['border-right-color']+' '+props['border-bottom-color']+' '+props['border-left-color'];
		}
		delete props['border-top-color'];
		delete props['border-right-color'];
		delete props['border-bottom-color'];
		delete props['border-left-color'];
	}
	if (props['border-top-width'] !== undefined && props['border-right-width'] !== undefined && props['border-bottom-width'] !== undefined && props['border-left-width'] !== undefined) {
		if (props['border-top-width']===props['border-bottom-width'] && props['border-left-width']===props['border-right-width']) {
			if (props['border-top-width']===props['border-left-width']) {
				props['border-width'] = props['border-top-width'];
				singleWidth = 1;
			} else {
				props['border-width'] = props['border-top-width']+' '+props['border-right-width'];
			}
		} else {
			props['border-width'] = props['border-top-width']+' '+props['border-right-width']+' '+props['border-bottom-width']+' '+props['border-left-width'];
		}
		delete props['border-top-width'];
		delete props['border-right-width'];
		delete props['border-bottom-width'];
		delete props['border-left-width'];
	}

	if (singleWidth && singleStyle && singleColor) {
		props['border'] = props['border-width']+' '+props['border-style']+' '+props['border-color'];
		delete props['border-width'];
		delete props['border-style'];
		delete props['border-color'];
	}

	/* border-radius */
	if (props['border-top-left-radius'] !== undefined && props['border-top-right-radius'] !== undefined && props['border-bottom-right-radius'] !== undefined && props['border-bottom-left-radius'] !== undefined) {
		if (props['border-top-left-radius']===props['border-bottom-right-radius'] && props['border-top-right-radius']===props['border-bottom-left-radius']) {
			if (props['border-top-left-radius']===props['border-top-right-radius']) {
				props['border-radius'] = props['border-top-left-radius'];
			} else {
				props['border-radius'] = props['border-top-left-radius']+' '+props['border-top-right-radius'];
			}
		} else {
			props['border-radius'] = props['border-top-left-radius']+' '+props['border-top-right-radius']+' '+props['border-bottom-right-radius']+' '+props['border-bottom-left-radius'];
		}
		delete props['border-top-left-radius'];
		delete props['border-top-right-radius'];
		delete props['border-bottom-right-radius'];
		delete props['border-bottom-left-radius'];
	}


	var str = '';
	$.each(props,function(prop,value) {
		var important = style.getPropertyPriority(prop) === 'important';
		str += '  '+prop+': '+value+(important?' !important':'')+';\n';
	});
	return str;
}
