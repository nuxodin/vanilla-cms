qgStyleEditor = function() {
	'use strict';

	c1.ext(qg.Eventer,this);
	var my = this;
	this.el = $('<div class="qgStyleEditor q1Rst">');
	this.ul = $('<ul class=-nav>').appendTo(this.el);
	my.ul.on('mousedown','li',function(e) {
		e.stopPropagation();
		var data = $(this).data('qg');
		my.ul.find('span.active').removeClass('active');
		$(this).find('span').first().addClass('active');
		if (data.prop) {
			showHandler(data);
		}
		if (data.sub) {
			$(this).parent().children().children('ul').hide();
			$(this).children('ul').show();
		}
	});
	function showHandler(node) {
		var prop = $.camelCase(node.prop);
		var options = node.options;

		my.el.find('.-handler').remove();
		var el = $('<div class=-handler>').appendTo(my.el);

		$('<a class="-sys">default</a>').on('click', function() {
			my.active[prop] = '';
			showHandler(node);
			my.fire('change');
		}).appendTo(el);
		$('<a class=-sys>inherit</a>').on('click', function() {
			my.active[prop] = 'inherit';
			showHandler(node);
			my.fire('change');
		}).appendTo(el);

		el.css({left:0,opacity:0}).animate({left:'100%',opacity:1});
		el.css({right:0,opacity:0}).animate({right:'100%',opacity:1});

		var def = qgCssProps[prop];

		var vInit = my.active[prop];
		if (vInit===undefined) {
			var p = prop.charAt(0).toUpperCase() + prop.slice(1);
			vInit = my.active['webkit'+p] || my.active['moz'+p] || my.active['ms'+p] || my.active['o'+p];
		}

		if (def.length) {
			vInit = parseInt(vInit) || '';
		}

		var inp;
		if (def.color) {
			inp = $('<input type=text>').appendTo(el);
			var setVal = function() {
				var c = inp.spectrum('get').toRgb();
		    	inp.val( 'rgba('+c.r+','+c.g+','+c.b+','+c.a+')' );
		    	inp.trigger('change');
			};
			inp.spectrum({
			    color: vInit,
			    showInput: true,
			    showButtons: false,
			    showAlpha:true,
			    change:setVal,
			    move:setVal,
			    showPalette: true,
			    palette: my.colorPalette
			});
		}
		if (def.options) {
			if (def.options.length===2) {
				inp = $('<input type=checkbox>').appendTo(el);
				inp[0].checked = vInit!==def.options[0];
				inp.on('change', function() { this.value = this.checked ? def.options[1] : def.options[0]; });
			} else {
				inp = $('<select>').appendTo(el);
				inp.append($('<option></option>'));
				def.options.forEach(function(v) {
					inp.append($('<option>'+v+'</option>'));
				});
			}
		}
		if (def.image) {
			inp = $('<select>').appendTo(el);
			inp.append($('<option value=none>none</option>'));
			my.imagePalette.forEach(function(url) {
				inp.append($('<option value="url(\''+url+'\')">'+url.replace(/.*\/([^\/]+)$/,'$1')+'</option>'));
			});
		}
		if (node.handler==='lineargradient') {
		      var handler = qgStyleEditor.gradientHandler(my.colorPalette);
		      handler.el.appendTo(el);
		      inp = handler.inp;
		}
		if (node.handler==='shadow') {
		      var handler = qgStyleEditor.shadowHandler(my.colorPalette,true);
		      handler.el.appendTo(el);
		      inp = handler.inp;
		}
		if (node.handler==='textshadow') {
		      var handler = qgStyleEditor.shadowHandler(my.colorPalette,false);
		      handler.el.appendTo(el);
		      inp = handler.inp;
		}
		if (node.handler==='transform') {
		      var handler = qgStyleEditor.transformHandler();
		      handler.el.appendTo(el);
		      inp = handler.inp;
		}
		if (def.length) {
			var step = (options[1]-options[0])/150;
			inp = $('<input type=range min="'+options[0]+'" max="'+options[1]+'" step="'+step+'">').appendTo(el);
		}
		if (def.float) {
			var step = (options[1]-options[0])/150;
			inp = $('<input type=range min="'+options[0]+'" max="'+options[1]+'" step="'+step+'">').appendTo(el);
		}
		if (node.handler=='bigtext') {
			vInit = vInit.replace(/;\s+/g,';\n');
			var rawInp = $('<textarea style="width:200px; height:300px"/>').appendTo(el);
		} else {
			var rawInp = $('<input>').appendTo(el);
		}

		inp && inp.val(vInit);
		rawInp.val(vInit);
		var change = function(e) {
			var v = $(this).val();
			if (v.match(/[0-9]+/) && def.length) {
				v = Math.round(v)+'px';
			}
			if (def.vendorPrefix) {
				var p = prop.charAt(0).toUpperCase() + prop.slice(1);
				my.active['webkit'+p] = my.active['Moz'+p] = my.active['ms'+p] = my.active['o'+p] = v;
			}
			my.active[prop] = v;
			//if (e.type=='change') {
				e.target === rawInp[0] ? inp && inp.val(v) : rawInp.val(v);
			//}
			my.fire('change');
		};
		inp && inp.on('change input',change);
		rawInp.on('change input',change);
	}
//	build(ul,tree);
};
qgStyleEditor.prototype = {
	_bildNodes: function(parent,tree) {
		var my = this;
		tree.forEach(function(node) {
			var el = $('<li><span>'+node.name+'</span>');
			parent.append(el);
			el.data('qg',node);
			if (node.sub) {
				var ul = $('<ul>').appendTo(el);
				my._bildNodes(ul,node.sub);
			}
			if (my.active[node.prop] !== '' &&my.active[node.prop] !== undefined) {
				el.css({fontWeight:'bold'});
			}
		});
	},
	build:function() {
		this.ul.html('');
		this._bildNodes(this.ul,qgStyleEditor.tree);
	},
	showStyle: function(style) {
		this.hide();
		this.active = style;
		this.build();
		this.ul.children().last().trigger('mousedown');
	},
	hide: function() {
		this.el.find('.-handler').remove();
		this.active = false;
	},
	colorPalette:null,
	imagePalette:null
};

qgStyleEditor.tree = [{
	name:'Text'
    	,sub: [{
    		name:'Color',
    		prop:'color'
    	},{
    		name:'Family',
    		prop:'fontFamily'
    	},{
    		name:'Size',
    		prop:'fontSize',
    		options:[9,50]
    	},{
    		name:'Italic',
    		prop:'fontStyle',
    	},{
    		name:'Bold',
    		prop:'fontWeight',
    	},{
    		name:'Ausrichtung',
    		prop:'textAlign',
    	},{
    	  name:'More',
    	  sub:[{
	        	name:'Shadow (IE10)',
	        	prop:'text-shadow',
	        	handler:'textshadow'
			},{
				name:'Lineheight',
				prop:'lineHeight',
				options:[1,3]
			},{
				name:'Letter-Spacing',
				prop:'letterSpacing',
				options:[0,10]
			},{
				name:'Text-Decoration',
				prop:'textDecoration',
			},{
				name:'Text-Transform',
				prop:'textTransform',
			},{
				name:'Hyphens',
				prop:'hyphens'
			},{
				name:'Einrückung',
				prop:'textIndent',
				options:[0,100]
			},{
				name:'white-space',
				prop:'whiteSpace'
			}]
    	}]
    },{
    	name:'Border',
	    sub: [{
    		name:'Style',
		    prop:'borderStyle',
		    sub:[{
		    	name:'Top',
			    prop:'borderTopStyle',
		    },{
		    	name:'Right',
			    prop:'borderRightStyle',
		    },{
		    	name:'Bottom',
			    prop:'borderBottomStyle',
		    },{
		    	name:'Left',
			    prop:'borderLeftStyle',
		    }]
    	},{
    		name:'Color',
		    prop:'borderColor',
		    sub:[{
		    	name:'Top',
			    prop:'borderTopColor',
		    },{
		    	name:'Right',
			    prop:'borderRightColor',
		    },{
		    	name:'Bottom',
			    prop:'borderBottomColor',
		    },{
		    	name:'Left',
			    prop:'borderLeftColor',
		    }]
    	},{
    		name:'Width',
	    	options:[0,30],
		    prop:'borderWidth',
		    sub:[{
		    	name:'Top',
		    	options:[0,30],
			    prop:'borderTopWidth',
		    },{
		    	name:'Right',
		    	options:[0,30],
			    prop:'borderRightWidth',
		    },{
		    	name:'Bottom',
		    	options:[0,30],
			    prop:'borderBottomWidth',
		    },{
		    	name:'Left',
		    	options:[0,30],
			    prop:'borderLeftWidth',
		    }]
    	},{
    		name:'Radius',
	    	options:[0,70],
			prop:'borderRadius',
		    sub:[{
		    	name:'Top-Left',
		    	options:[0,70],
			    prop:'borderTopLeftRadius',
		    },{
		    	name:'Top-Right',
		    	options:[0,70],
			    prop:'borderTopRightRadius',
		    },{
		    	name:'Bottom-Right',
		    	options:[0,70],
			    prop:'borderBottomRightRadius',
		    },{
		    	name:'Bottom-Left',
		    	options:[0,70],
			    prop:'borderBottomLeftRadius',
		    }]
    	}]
    },{
    	name:'Background',
    	sub: [{
    		name:'Color',
			prop:'backgroundColor'
    	},{
    		name:'Image',
			prop:'backgroundImage'
    	},{
    		name:'Gradient',
			prop:'backgroundImage',
			handler:'lineargradient'
    	},{
    		name:'Position',
			prop:'backgroundPosition'
    	},{
    		name:'Repeat',
			prop:'backgroundRepeat'
    	}]
    },{
    	name:'Abstände',
    	sub: [{
    		name:'Aussen',
    		prop:'margin',
	    	options:[0,70],
	    	sub:[{
	    		name:'Top',
	    		prop:'marginTop',
		    	options:[0,70],
	    	},{
	    		name:'Right',
	    		prop:'marginRight',
		    	options:[0,70],
	    	},{
	    		name:'Bottom',
	    		prop:'marginBottom',
		    	options:[0,70],
	    	},{
	    		name:'Left',
	    		prop:'marginLeft',
		    	options:[0,70],
	    	}]
    	},{
    		name:'Innen',
    		prop:'padding',
	    	options:[0,70],
	    	sub:[{
	    		name:'Top',
	    		prop:'paddingTop',
		    	options:[0,70],
	    	},{
	    		name:'Right',
	    		prop:'paddingRight',
		    	options:[0,70],
	    	},{
	    		name:'Bottom',
	    		prop:'paddingBottom',
		    	options:[0,70],
	    	},{
	    		name:'Left',
	    		prop:'paddingLeft',
		    	options:[0,70],
	    	}]
    	}]
    },{
    	name:'Dimension'
    	,sub: [{
    		name:'Width',
			prop:'width',
	    	options:[70,1200],
    	},{
    		name:'Height',
    		prop:'height',
	    	options:[70,1000],
    	},{
    		name:'Min-Width',
			prop:'minWidth',
	    	options:[70,1200],
    	},{
    		name:'Max-width',
			prop:'maxWidth',
	    	options:[70,1200],
    	},{
    		name:'Min-Height',
			prop:'minHeight',
	    	options:[70,1200],
        },{
    		name:'Max-Height',
			prop:'maxHeight',
	    	options:[70,1200],
    	}]
    },{
    	name:'Divers',
    	sub:[{
    		name:'Display',
			prop:'display',
    	},{
        	name:'Shadow',
        	prop:'box-shadow',
        	handler:'shadow'
    	},{
    		name:'Position',
			prop:'position',
    	},{
    		name:'Top',
			prop:'top',
	    	options:[-500,500],
    	},{
    		name:'Left',
			prop:'left',
	    	options:[-500,500],
    	},{
    		name:'Right',
			prop:'right',
	    	options:[-500,500],
    	},{
    		name:'Bottom',
			prop:'bottom',
	    	options:[-500,500],
    	},{
    		name:'Opacity',
			prop:'opacity',
	    	options:[0,1],
    	},{
        	name:'Overflow',
        	prop:'overflow',
        	sub:[{
        		name:'X',
               	prop:'overflow-x'
        	},{
        		name:'y',
                prop:'overflow-y'
        	}]
		},{
        	name:'Column',
        	prop:'column-width',
        	options:[70,600],
        	sub:[{
        		name:'Gap',
        		prop:'column-gap',
				options:[4,100],
        	}]
    	},{
    		name:'Float',
			prop:'float',
    	},{
    		name:'Clear',
			prop:'clear',
        },{
        	name:'2D Transformation',
			prop:'transform',
			handler:'transform',
        }]
    },{
    	name:'All',
    	prop:'cssText',
    	handler:'bigtext'
}];







getPossibleSelectorsFromElement = function(el,deep) {
	'use strict';

	var selectors = [];
	var max = 140;
	var level = 0;
	deep = deep||5;
	function addSelector(selector,parent,skipSelf) {
		if (deep <= level) return;
		if (!skipSelf) {
			selectors.push(selector);
			if (--max < 0) return;
		}
		if (parent) {
			level++;
			reqursiv(parent, selector);
			level--;
		}
	}
	function reqursiv(el, before) {
		before = before ? ' > '+before : '';
		if (el.className === undefined) return;
		let parent = el.parentNode;

		/* by id */
		let id = el.id;
		id && addSelector('#'+id+before);
		/* by classes */
		el.classList.forEach(function(c){
			//c = c.trim();
			//if (!c) return;
			if (c === '-e') return; // ignore these
			if (c === 'c1-focusIn') return;

			let selector = '.'+c+before;
			if (selector.match(/\.-pid/)) { // unique
				addSelector(selector);
			} else if (selector.match(/\.qgCmsCont/)) { // unique
				addSelector(selector, parent, true); // ok?
			} else {
				addSelector(selector, parent);
			}
		});
		/* by tagname */
		addSelector(el.tagName.toLowerCase()+before, parent, true);
	}
	reqursiv(el,'');
	return selectors;
};


qgStyleEditor.gradientHandler = function(colorPalette) {
	'use strict';

	var el = $('<div>');
	var hidden = $('<input type=hidden style="width:400px">').appendTo(el);

	var startInp = $('<input type=text>').appendTo(el);
	var endInp = $('<input type=text>').appendTo(el);

	var setVal = function() {
		var startC = startInp.spectrum('get').toRgb();
		var startColor = 'rgba('+startC.r+','+startC.g+','+startC.b+','+startC.a+')';

		var endC = endInp.spectrum('get').toRgb();
		var endColor = 'rgba('+endC.r+','+endC.g+','+endC.b+','+endC.a+')';

		hidden[0].value = 'linear-gradient(to bottom,'+startColor+','+endColor+')';
		hidden.trigger('change');
		return hidden;
	};

	startInp.spectrum({
	    showInput: true,
	    showButtons: false,
	    showAlpha: true,
	    change: setVal,
	    move: setVal,
	    showPalette: true,
	    palette: colorPalette
	});
	endInp.spectrum({
	    showInput: true,
	    showButtons: false,
	    showAlpha: true,
	    change: setVal,
	    move: setVal,
	    showPalette: true,
	    palette: colorPalette
	});

	hidden.val = function(value) {
		this[0].value = value;
		var colors = value.match(/#[0-9abcdef]{3,6}|rgb[a]?\([^)]+\)/g);
    	if (colors) {
      		startInp.spectrum('set',colors[0]);
      		endInp.spectrum('set',colors[1]);
        }
	};
	return {inp:hidden, el:el};
};

qgStyleEditor.shadowHandler = function(colorPalette, hasSpread) {
	'use strict';

	var el = $('<div>');
	var hidden = $('<input type=hidden>').appendTo(el);
	var colorInp = $('<input type=text>').appendTo(el);
	$('<div>x-Versatz</div>').appendTo(el);
	var xOffsetInp = $('<input type=range min=-40 max=40 step=1 value=1>').appendTo(el);
	$('<div>y-Versatz</div>').appendTo(el);
	var YOffsetInp = $('<input type=range min=-40 max=40 step=1 value=1>').appendTo(el);
	$('<div>Verlauf-Radius</div>').appendTo(el);
	var radiusInp = $('<input type=range min=0 max=130 step=1 value=1>').appendTo(el);
	var spreadInp = $('<input type=range min=-30 max=30 step=1 value=1>');
	hasSpread && $('<div>Offset</div>').appendTo(el) && spreadInp.appendTo(el);

	var setVal = function() {
		var color = colorInp.spectrum('get').toRgb();
		color = 'rgba('+color.r+','+color.g+','+color.b+','+color.a+')';
		var xOffset = xOffsetInp.val();
		var yOffset = YOffsetInp.val();
		var radius = radiusInp.val();
		var spread = spreadInp.val();

		hidden[0].value = color+' '+xOffset+'px '+yOffset+'px '+radius+'px '+(hasSpread?spread+'px':'');
		hidden.trigger('change');
		return hidden;
	};

	xOffsetInp.on('input',setVal);
	YOffsetInp.on('input',setVal);
	radiusInp.on('input',setVal);
	spreadInp.on('input',setVal);

	colorInp.spectrum({
	    showInput: true,
	    showButtons: false,
	    showAlpha:true,
	    change:setVal,
	    move:setVal,
	    showPalette: true,
	    palette: colorPalette
	});
	hidden.val = function(value) {
		this[0].value = value;
		var colorReg = /#[0-9abcdef]{3,6}|rgb[a]?\([^)]+\)/;
		var colors = value.match(colorReg);
    	if (colors) {
    		value = value.replace(colorReg,'').trim();
      		colorInp.spectrum('set',colors[0]);
        }
    	var vs = value.split(/\s/g);
    	xOffsetInp.val(parseInt(vs[0])||0);
    	YOffsetInp.val(parseInt(vs[1])||0);
    	radiusInp.val(parseInt(vs[2])||0);
    	spreadInp.val(parseInt(vs[3])||0);
	};
	return {inp:hidden,el:el};
};



qgStyleEditor.transformHandler = function() {
	'use strict';

	var el = $('<div>');
	var hidden = $('<input type=hidden>').appendTo(el);

	var setVal = function() {
		hidden[0].value = 'rotate('+rotateInp.val()+'deg) scale('+scaleXInp.val()+','+scaleYInp.val()+') '+
						  'translate('+translateXInp.val()+'px,'+translateYInp.val()+'px) '+
						  'skew('+skewXInp.val()+'deg,'+skewYInp.val()+'deg)';
		hidden.trigger('change');
		return hidden;
	};

	function addRange(titel,min,max) {
		$('<div>'+titel+'</div>').appendTo(el);
		return $('<input type=range min="'+min+'" max="'+max+'" step="'+0.1+'">').appendTo(el).on('input',setVal);
	}
	var rotateInp 		= addRange('Rotate',0,360);
	var scaleXInp 		= addRange('Scale-X',.5,2);
	var scaleYInp 		= addRange('Scale-Y',.5,2);
	var translateXInp 	= addRange('Translate-X',-500,500);
	var translateYInp 	= addRange('Translate-Y',-500,500);
	var skewXInp 		= addRange('Skew-X',0,180);
	var skewYInp 		= addRange('Skew-Y',0,180);

	hidden.val = function(value) {
		value = value || '';
		this[0].value = value;
		function get(reg,def) {
			var v = value.match(reg);
			return v?parseInt(v[1]):def;
		}
		rotateInp.val( get(/rotate\(([^)]+)deg\)/,0) );
		scaleXInp.val( get(/scale\(([^,]+),[^)]+\)/,1) );
		scaleYInp.val( get(/scale\([^,]+,([^)]+)\)/,1) );
		translateXInp.val( get(/translate\(([^,]+),[^)]+\)/,0) );
		translateYInp.val( get(/translate\([^,]+,([^)]+)\)/,0) );
		skewXInp.val( get(/skew\(([^,]+),[^)]+\)/,0) );
		skewYInp.val( get(/skew\([^,]+,([^)]+)\)/,0) );
	};
	return {inp:hidden,el:el};
};
