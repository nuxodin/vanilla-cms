/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

qgStyleSheetEditor = function() {
	'use strict';

	var my = this;
	c1.ext(c1.Eventer,this);

	my.div = $('<div class="q1Rst qgStyleSheetEditor"></div>').appendTo(document.body).draggable({handle:'.-title'});

	function checkHeight() {
		if (!my.div[0].offsetWidth) return;
		my.div.find('.-contents').each(function() {
			var el = $(this);
			var h = $(window).height() + $(window).scrollTop() - el.offset().top;
            h = Math.min(h,867);
			el.css('max-height',h-27);
		});
	}
	$(window).on('resize',checkHeight);
	my.div.on('drag',checkHeight);
	my.on('show',checkHeight);

	var markMatches = function(selector) {
		$('.qgStyleEditorMarkEl').removeClass('qgStyleEditorMarkEl');
		$('.qgStyleEditorMarkEl_invisible').removeClass('qgStyleEditorMarkEl_invisible');
		if (selector) {
			try {
				var $els = $(selector);
			} catch (e) {
				return;
			}
			$els.addClass('qgStyleEditorMarkEl');
			$els.parents().add( $els ).each(function(i, el) {
				var style = getComputedStyle(el);
				if (style.display==='none' || style.opacity < 0.3 || style.visibility === 'hidden' || el.offsetHeight === 0) {
					el.classList.add('qgStyleEditorMarkEl_invisible');
				}
			});
		}
		return true;
	};
	var hoverMarkEvents = {
		mouseover() {
			markMatches($(this).data('unPseudoSel'));
		},
		mouseout() {
			markMatches();
		}
	};

	/* sheet box */
	my.sheetBox = $('<div><div class="-title"><b>Bitte wählen<span></b><span class="-close">x</span></div><div class="-contents"><table><tbody class="-rules"></tbody></div></div>').appendTo(my.div);
	my.sheetBox.find('.-close').on('click', my.close.bind(this));
	my.sheetBox.find('.-rules')
	.on(hoverMarkEvents,'tr')
	.on({
		click(e) {
			var td = $(e.target);
			var tr = td.parent();
			var cssRule = tr.data('qg');
			if (e.ctrlKey) {
				my.showAddRule(cssRule.selectorText);
			} else if (td.hasClass('-rem')) {
				var index = tr.data('index');
				my.deleteRule(index);
				tr.trigger('mouseout');
				my.show(my.active);
			} else if (e.shiftKey || td.hasClass('-redefine')) {
				my.showRedefineRule(cssRule);
			} else {
				tr.parent().children('.active').removeClass('active');
				tr.addClass('active');
				styleBox.find('.-title').data('unPseudoSel',tr.data('unPseudoSel'));
				styleBox.find('.-title > b').html(cssRule.selectorText);
				styleBox.show();
				my.sheetBox.hide();
				my.sEditor.showStyle(cssRule.style);
				my.sEditor.el.show();
				my.sEditor.el.find('.-handler textarea').focus();
			}
		}
	});

	/* stop scrolling on parent */
	my.div.find('.-contents').on('wheel', function(e) {
		e = e.originalEvent;
		if (e.wheelDelta > 0 && this.scrollTop === 0) {
			e.preventDefault();
		} else if (e.wheelDelta < 0 && this.scrollHeight === this.scrollTop + this.offsetHeight) {
			e.preventDefault();
		}
	});

	/* style box */
	var styleBox = $('<div style="display:none"><div class="-title"><b>selector</b><span class="-close">x</span></div></div>').appendTo(my.div).on(hoverMarkEvents,'.-title');
	my.sEditor = new qgStyleEditor();
	my.sEditor.el.appendTo(styleBox).hide();
	my.sEditor.on('change', function() {
		my.sEditor.colorPalette = my.getColors();
		my.trigger('change');
	});
	!function() {
		var close = function() {
			my.sEditor.hide();
			my.sheetBox.show();
			styleBox.hide();
		};
		styleBox.find('.-close').on('click', close);

		$(document).on('keydown', function(e){ // not perfect
			if (e.which !== 27) return;
			if (my.sheetBox[0].offsetWidth) my.close();
			close();
		});
	}();


	/* redefine */
	!function() {
		var activeRule = null;
		var redefineBox = $('<div style="display:none;"><div class="-title"><b>redefine selector</b><span class="-close">x</span></div><input style="width:300px; margin:2px 0; box-sizing:border-box"></div>').appendTo(my.div);
		var set = function(v) {
			activeRule.selectorText = v;
			my.trigger('change');
			close();
		};
		var close = function() {
			my.show(my.active);
			redefineBox.hide();
			my.sheetBox.show();
		};
		var show = function(e) {
			redefineBox.show().find('input').focus();
			my.sheetBox.hide();
			e && e.preventDefault();
		};
		my.showRedefineRule = function(cssRule) {
			show();
			activeRule = cssRule;
			var selector = cssRule.selectorText;
			redefineBox.find('input').val(selector);
		};
		redefineBox.find('.-close').on('click',close);

		redefineBox.find('input').on({
			keyup: function(e) {
				var valid = markMatches(this.value);
				this.style.backgroundColor = valid ? '#fff' : '#fddd';
			},
			keydown: function(e) {
				e.which===13 && this.value && set(this.value);
				e.which===27 && close();
			}
		});
	}();

	var crosshair = $('<style>* { cursor:crosshair !important; }</style>');
	/* add */
	!function() {
		var addBox = $('<div style="display:none;"><div class="-title"><b>add</b><span class="-close">x</span></div><input style="width:300px; margin:2px 0; box-sizing:border-box"></div>').appendTo(my.div);
		var tmout=0;
		var mouseout = function() {clearTimeout(tmout);};
		var mousemove = function(e) {
			if (e.target.closest('.qgStyleSheetEditor')) return;
			var el = $(e.target);
			clearTimeout(tmout);
			tmout = setTimeout(function() {
				markMatches();
				el.addClass('qgStyleEditorMarkEl');
				var sels = getPossibleSelectorsFromElement(el[0],5);
				inspEl.html('');
				sels = sels.sort(function(a,b) { return a.length - b.length; } );
				sels.forEach(function(sel) {
					if (sel.match(/\.cmsLink|\.qgCmsMarked|\.cmsChilds|qgStyleEditorMarkEl|\.-draggable/)) return;
					$('<li>').html(sel).data('unPseudoSel',sel).appendTo(inspEl);
				});
			},300);
		};
		var add = function(rule) {
			var success = my.insertRule(rule);
			close();
			success && my.sheetBox.find('.-rules tr:last-child td:first-child').trigger('click');
		};
		var close = function() {
			my.show(my.active);
			addBox.hide();
			my.sheetBox.show();
			crosshair.detach();
			$(document).off('mousemove',mousemove);
			$(document).off('mouseout',mouseout);
		};
		var show = function(e) {
			addBox.show().find('input').focus();
			my.sheetBox.hide();
			crosshair.appendTo($('head'));
			e && e.preventDefault();
			$(document).on('mousemove',mousemove);
			$(document).on('mouseout',mouseout);
		};
		my.showAddRule = function(selector) {
			show();
			selector && addBox.find('input').val(selector);
		};
		addBox.find('.-close').on('click', close);
		addBox.find('input').on('keyup', function(e) {
			var valid = markMatches(this.value);
			this.style.backgroundColor = valid ? '#fff' : '#fdd';
		});
		addBox.find('input').on('keydown',function(e) {
			e.which===13 && this.value && add(this.value);
			e.which===27 && close();
		});
		var inspEl = $('<ul>').appendTo(addBox).css({background:'#ffa',listStyle:'none',margin:0,padding:0,cursor:'pointer'})
		.on(hoverMarkEvents,'li')
		.on('click',function(e) {
			var sel = $(e.target).data('unPseudoSel');
			e.ctrlKey ? addBox.find('input').val(sel).focus() : add(sel);
		});
		$('<a href="#" style="margin:6px; padding:4px; display:inline-block">hinzufügen</a>').insertAfter(my.sheetBox.find('.-title')).on('click',show);
	}();

	/* inspect */
	!function() {
        function inspect(e) {
            my.sheetBox.find('table tr').hide();
			e && e.preventDefault();
			crosshair.appendTo($('head'));
			$(document).on('mousemove', mousemove);
			$(document).on('mouseout' , mouseout);
            inspEl.hide();
            allEl.show();
        }
        function showAll(e) {
            my.sheetBox.find('table tr').show();
			e && e.preventDefault();
			crosshair.detach();
			$(document).off('mousemove', mousemove);
			$(document).off('mouseout' , mouseout);
			clearTimeout(tmout);
            inspEl.show();
            allEl.hide();
        }
		var tmout=0;
		var mouseout  = function() {clearTimeout(tmout);};
		var mousemove = function(e) {
			if (e.target.closest('.qgStyleSheetEditor')) return;
			clearTimeout(tmout);
			tmout = setTimeout(function() {
                my.sheetBox.find('table tr').each(function() {
                    var tr = $(this);
                    var selector = tr.data('unPseudoSel').replace(/::(before|after)/,'');
                    try {
						e.target.closest(selector) ? tr.show() : tr.hide();
                    } catch(e) {
                        tr.hide();
                    }
                });
			}, 300);
		};
		var inspEl = $('<a href="#" style="margin:6px; padding:4px; display:inline-block">Inspect</a>').insertAfter(my.sheetBox.find('.-title')).on('click',inspect);
		var allEl = $('<a href="#" style="margin:6px; padding:4px; display:inline-block">Show all</a>').insertAfter(my.sheetBox.find('.-title')).on('click',showAll).hide();
	}();
};

qgStyleSheetEditor.prototype = {
	insertRule(rule, pos) {
		pos = pos===undefined ? this.active.cssRules.length : pos;
		try {
			this.active.insertRule(rule+' {}', pos);
        } catch (e) {
        	return false;
        }
		return true;
	},
	deleteRule(index) {
		this.active.deleteRule(index);
		this.trigger('change');
	},
	moveRule(index, toPos) {
		var cssText = this.active.cssRules.item(index).cssText;
		toPos > index && ++toPos;
		this.active.insertRule(cssText,toPos);
		toPos < index && ++index;
		this.active.deleteRule( index );
		this.trigger('change');
		this.show(this.active); // the moved item as not saveable / hack?
	},
	show(ss) {
		this.active = ss;
		var my = this;
		my.div.show();
		my.div[0].c1ZTop();
		var table = my.sheetBox.find('.-rules'); // name it tbody
		table.html('');
		$.each(ss.cssRules, function(i,cssRule) {
			var selectorText = cssRule.selectorText.replace(/\[([^\]]+)[^\\]:([^\]]+)\]/g, '[$1\\:$2]')
			var unPseudoSel = selectorText.replace(/:focus|:hover|:visited|::after|::before/g,'');
			//unPseudoSel = unPseudoSel.replace(/:/g,'\\:'); // but in chrome and edge, ugly hack?
			var len = $(unPseudoSel).length;
			$('<tr'+(cssRule.style.length?' class=-has':'')+'><td>'+cssRule.selectorText+'<td>'+len+'<td class=-redefine title=redefine>edit<td class=-rem title=remove>x</tr>').data({'qg':cssRule,'unPseudoSel':unPseudoSel,'index':i}).appendTo(table);
		});
		my.sEditor.colorPalette = my.getColors();

		/* move rules */
		var startPos = null;
		table.sortable({
			update(e,ui) {
				var toPos = ui.item.prevAll().length;
				my.moveRule(startPos,toPos);
			},
			start(e,ui) {
				startPos = ui.item.prevAll().length;
			}
		});
		var fileName = my.active.href.replace(/.*\/([^\/?]+).*/,'$1');
		my.sheetBox.children('.-title').find('b').html(fileName+' | stylesheet editor');
		this.trigger('show');
	},
	close(){
		this.div.hide();
		this.trigger('close');
	},
	getColors() {
		var content = q_CSSStyleSheetContents(this.active);
		var colors = content.match(/#[0-9abcdef]{3,6}|rgb[a]?\([^)]+\)/g);
		var tmp = {};
		colors && colors.forEach(color=>{
			tmp[color] = tmp[color] ? tmp[color]+1 : 1;
		});
		var tmp1 = [];
		for (var i in tmp) {
			if (tmp.hasOwnProperty(i)) {
				tmp1.push({value:i,num:tmp[i]});
			}
		}
		tmp1.sort(function(a,b) {return a.num < b.num;});
		var res = [];
		tmp1.forEach(obj => res.push(obj.value));
		return res;
	}
};

/* polyfill */
q_CSSStyleSheetContents = function(sheet) {
	var str = '';
	var rules = sheet.rules || sheet.cssRules;
	$.each(rules, function(i,rule) {
		var selectorText = rule.selectorText;
		str += selectorText+' {\n';
		str += q1CssText(rule.style);
		str += '}\n';
	});
	return str;
};
// hacky correction polyfill for CSSRule.selectorText, edge, safari, https://bugs.chromium.org/p/chromium/issues/detail?id=681814
{
	let desc = Object.getOwnPropertyDescriptor(CSSStyleRule.prototype, 'selectorText');
	let getter = desc.get;
	desc.get = function(){
		let str = getter.apply(this).replace(/\[([^\]]+[^\\\]]):([^\]]+)\]/g, '[$1\\:$2]');
		return str;
	}
	Object.defineProperty(CSSStyleRule.prototype, 'selectorText', desc)
}
