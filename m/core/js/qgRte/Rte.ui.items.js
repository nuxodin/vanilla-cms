Rte.on('ready', function() {
	'use strict';

	Rte.ui.setItem('Bold', 					{cmd:'bold',	shortcut:'b'} );
	/*
	var x = my.setItem('Bold',
		{
			shortcut:'l'
		}
	);
	x.addEventListener('mousedown', function() {
		Rte.modifySelection(function(els) {
			var first = $(els[1]||els[0]);
			var act = first.hasClass('SmallText') ? 'removeClass' : 'addClass';
			for (var i = els.length, el; el = els[--i];) {
				$(el)[act]('SmallText');
			}
		});
	});
	*/
	Rte.ui.setItem('Italic', 				{cmd:'italic',	shortcut:'i'} );
	Rte.ui.setItem('Insertunorderedlist',	{cmd:'insertunorderedlist',shortcut:'8'});
	Rte.ui.setItem('Insertorderedlist',		{cmd:'insertorderedlist',shortcut:'9'});
	Rte.ui.setItem('Underline', 			{cmd:'underline',shortcut:'u'});
	Rte.ui.setItem('Undo', 					{cmd:'undo',	check:false});
	Rte.ui.setItem('Redo', 					{cmd:'redo',	check:false});
	Rte.ui.setItem('Unlink', 				{cmd:'unlink',	check:false});
	Rte.ui.setItem('Hr', 					{cmd:'inserthorizontalrule', check:false});
	Rte.ui.setItem('Strikethrough', 		{cmd:'strikethrough'});

	/* bred-crumb *
	var list = $('<div style="padding:2px; margin:2px; color:#000; background:linear-gradient(#fff,#ccc); xborder-radius:3px; box-shadow: 0 0 1px #000;">');
	Rte.ui.setItem( 'Tree', {
		el:list[0],
		//enable(el) {  },
		check(el) {
			list.html('');
			if (el===Rte.active) return;
			$(el).parentsUntil(Rte.active).addBack().each(function(i,el) {
				var btn = $('<span style="border-right:1px solid #bbb; padding:1px 3px; cursor:pointer">'+el.tagName+'</span>')
				.on({
					click() {
						qgSelection.toChildren(el);
						Rte.checkSelection();
					},
					mouseover(e) {
						$('.tmp-rgRteMarked').removeClass('tmp-rgRteMarked');
						$(el).addClass('tmp-rgRteMarked');
						e.stopPropagation();
					}
				});
				list.append(btn);
			});
		}
	});

	/* Headings */
	var opts = Rte.ui.setSelect('Format',{
		click(e) {
			var tag = $(e.target).attr('value');
			tag && qgExecCommand('formatblock',false,tag);
			var stat = qgQueryCommandValue('formatblock');
			opts.children().each(function(i,el) {
				el.className = el.tagName.toLowerCase()===stat ? 'selected' : '';
			});
		},
		check() {
			var stat = qgQueryCommandValue('formatblock');
			opts.prev().html( Rte.element ? stat : 'Format' );
		}
	}).html(
		'<p  value=p >Paragraph</p>'+
		'<h1 value=h1>Heading 1</h1>'+
		'<h2 value=h2>Heading 2</h2>'+
		'<h3 value=h3>Heading 3</h3>'+
		'<h4 value=h4>Heading 4</h4>'+
		'<h5 value=h5>Heading 5</h5>'+
		'<h6 value=h6>Heading 6</h6>'
	);

	/* CSS classes */
	!function() {
		var useClass = function(cl) { return cl.match(/^[A-Z]/); };
		var hasClasses; /* check if this-handle is used */
		var check = function(el) {
			var classes = getPossibleClasses(el);
			$.each(classes,function(cl) {
				hasClasses = hasClasses || useClass(cl);
			});
			sopts.parent()[0].style.display = hasClasses ? '' : 'none';
		}.c1Debounce(150);

		var sopts = Rte.ui.setSelect('Style', {
			check() {
				check();
				var classes = Rte.element && Rte.element.className.split(' ').filter( useClass ).join(' ') || 'Style';
				sopts.prev().html( classes );
			},
			click() {
				sopts.empty();
				var el = qgSelection.isElement() || qgSelection.collapsed() ? Rte.element : null;
//				if (el === Rte.active) return;
				$.each(getPossibleClasses(el), function(sty) {
					if (!useClass(sty)) return;
					var has = el && $(el).hasClass(sty);
					var d = $('<div class="option '+sty+'">'+sty+'</div>').appendTo(sopts);
					has && d.addClass('selected');
					d[0].onmousedown = function() {
						Rte.manipulate(function() {
							if (!el) {
								el = qgSelection.surroundContents( document.createElement('span') );
								if ($(el).css('display') === 'block') {
									el = qgReplaceNode(el,document.createElement('div'));
									qgSelection.toChildren(el);
								}
							}
							has ? $(el).removeClass(sty) : el.className += ' '+sty;
						});
					};
					// d.css({
					// 	fontSize:parseInt(d.css('fontSize')).limit(9,18),
					// 	margin:parseInt(d.css('margin')).limit(0,4),
					// 	padding:parseInt(d.css('padding')).limit(0,4),
					// 	letterSpacing:parseInt(d.css('letterSpacing')).limit(0,11),
					// 	borderWidth:parseInt(d.css('borderWidth')).limit(0,4)
					// });
				});
			}
		});
	}();

	/* clean / remove format */
	function deleteComments(d) {
	    if (!d) return;
	    if (d.childNodes) {
			for (let child of d.childNodes) deleteComments(child);
	    }
	    d.nodeType === 8 && d.remove();
	}
	Rte.ui.setItem('Removeformat', {
		click(e) {
			var fn = function(i,el) {
				if (!$.contains(Rte.active, el)) return;
				el.removeAttribute('style');
				el.removeAttribute('class');
				el.removeAttribute('align');
				el.removeAttribute('valign');
				el.removeAttribute('border');
				el.removeAttribute('cellpadding');
				el.removeAttribute('cellspacing');
				el.removeAttribute('bgcolor');
				['FONT','O:P','SDFIELD','SPAN'].indexOf(el.tagName) !== -1 && el.removeNode();
				if (el.tagName !== 'IMG') {
					el.removeAttribute('width');
					el.removeAttribute('height');
				}
			};
			var root = e.ctrlKey ? Rte.element : Rte.active;
			fn(0,root);
			deleteComments(root);
			$(root).find('*').each(fn);
		}
		,shortcut:'space'
	});

	/* code */
	{
		let wrapper = c1.dom.fragment(
			'<div id=qgRteHtml>'+
				'<textarea spellcheck=false class=c1Rst></textarea>'+
				'<style>'+
				'	#qgRteHtml { transition:all .3s; transform:translateY(100%); opacity:0; position:fixed; border:2px solid black; top:40%; left:1%; bottom:1%; right:1%; background:#fff; color:#000; margin:auto; box-shadow:0 0 20px} '+
				'	#qgRteHtml > textarea { position:absolute; top:0; left:0; right:0; bottom:0; width:100%; font:11px monospace; } '+
				'	html:hover #qgRteHtml { opacity:1; transform:translateX(0) } '+
				'</style>'+
			'</div>'
		).firstChild;
		let html = wrapper.firstChild;
		let el = Rte.ui.setItem('Code', {
			click() {
				let el = Rte.active;
				let sel = window.getSelection();
		        if (sel.rangeCount > 0) {
					var range = sel.getRangeAt(0);
		            var startTextNode = document.createTextNode('marker_start_so9df8as0f0');
		            var endTextNode   = document.createTextNode('marker_end_laseg08a0egga');
					var tmpRange = range.cloneRange();
		            tmpRange.collapse(false);
		            tmpRange.insertNode(endTextNode);
					var tmpRange = range.cloneRange();
		            tmpRange.collapse(true);
		            tmpRange.insertNode(startTextNode);
				}
				let code = domCodeIndent(el.innerHTML);
				if (range) {
					startTextNode.remove();
					endTextNode.remove();

					let start = code.indexOf('marker_start_so9df8as0f0');
					code = code.replace('marker_start_so9df8as0f0','');
					let end = code.indexOf('marker_end_laseg08a0egga');
					code = code.replace('marker_end_laseg08a0egga','');

					let brsTotal = (code.match(/\n/g)||[]).length;
					let brs 	 = brsTotal && (code.substr(0,start).match(/\n/g)||[]).length;

					setTimeout(function(){
						html.focus();

						let y = parseInt((html.scrollHeight / brsTotal)*brs - 250);
						brs && (html.scrollTop = y);

						html.setSelectionRange(start, end);
					},10);
				}
				html.value = code;
				html.onkeyup = html.onblur = function(){
					el.innerHTML = html.value.replace(/\s*\uFEFF\s*/g,'');
					el.dispatchEvent(new Event('input',{'bubbles':true,'cancelable':true}));
				}
				document.body.append(wrapper);
				wrapper.c1ZTop();

				var hide = function(e) {
					if (e.which===27 || e.target !== html) {
						wrapper.remove();
						document.removeEventListener('keydown',hide);
						document.removeEventListener('mousedown',hide);
						el.focus();
					}
				};
				setTimeout(function() {
					document.addEventListener('keydown',hide);
					document.addEventListener('mousedown',hide);
				},3);
			},
			shortcut:'h'
		});
		el.classList.add('expert');
	}

	/* insert table */
	Rte.ui.setItem('Table', {
		//enable(el) {  },
		click() {
			var table = c1.dom.fragment('<table><tr><td>&nbsp;<td>&nbsp;<tr><td>&nbsp;<td>&nbsp;</table>').firstChild;
			var r = getSelection().getRangeAt(0);
			r.deleteContents();
			r.insertNode(table);
			getSelection().collapse(table.c1Find('td'),0);
		}
	});

	/* delete Element */
	Rte.ui.setItem('Del',{
		//enable(el) {  },
		click(el) { Rte.element.removeNode(); },
		el: c1.dom.fragment('<a style="color:red">Element löschen</a>').firstChild
	});

	/* Target */
	!function() {
		Rte.ui.setItem('LinkTarget', {
			enable:'a, a > *',
			check(el) {
				el = el.closest('a');
				let target = el.getAttribute('target');
				return target && target !== '_self';
			},
			click(){
				var el = $(Rte.element).closest('a');
				var active = this.el.classList.contains('active');
				el.attr('target',active?'_self':'_blank');
				Rte.fire('input');
				Rte.active.focus();
				Rte.fire('elementchange');
			},
			el: c1.dom.fragment('<div class=-item style="width:auto">neues Fenster</div>').firstChild
		});
	}();


	{ /* Titletag */
		let el = c1.dom.fragment('<table style="clear:both"><tr><td style="width:84px">Titel<td><input>').firstChild;
		let inp = el.c1Find('input');
		inp.addEventListener('keyup', function() {
			Rte.element.setAttribute('title',inp.value);
			!inp.value && Rte.element.removeAttribute('title');
			Rte.fire('input');
		});
		Rte.ui.setItem('AttributeTitle',{
			check(el) {
				inp.value = el ? el.getAttribute('title') : '';
			},
			el: el
		});
	}

	/* Image Attributes */
	var inp = $(
		'<table>'+
			'<tr><td style="width:84px">Breite:<td><input class="x">'+
			'<tr><td>Höhe:<td><input class="y">'+
			'<tr><td title="Alternativer Text">Alt-Text:<td><input class="alt">'+
		'</table>')
	.on('keyup',function(e) {
		var img = $(Rte.element);
		img.css({width: inp.find('.x').val()+'px' ,height: inp.find('.y').val()+'px' });
		img.attr('alt', inp.find('.alt').val());
		if (['x','y'].indexOf(e.target.className) !== -1) {
			Rte.element.dispatchEvent(new Event('qgResize',{bubbles:true}));
		}
		Rte.fire('input');
	});
	Rte.ui.setItem( 'ImageDimension', {
		check(el) {
			inp.find('.x').val(el.offsetWidth);
			inp.find('.y').val(el.offsetHeight);
			inp.find('.alt').val( el.getAttribute('alt') );
		},
		el:inp[0],
		enable:'img'
	});

});

/* table handles */
document.addEventListener('DOMContentLoaded',function(){
//{
	let td, tr, table, index;
	let handles = new qgTableHandles();
	Rte.on('deactivate',function(){
		handles.hide();
	});
	let positionize = function() {
		let e = Rte.element;
		if (!e) return;
		td = $(e).closest('td')[0];
		if (Rte.active && Rte.active.contains(td)) {
			tr = $(td).parent();
			table = $(tr).closest('table');
			index = td.cellIndex;
			handles.showTd(td);
		} else {
			handles.hide();
		}
	};
	Rte.on('elementchange activate', positionize);
	handles.els.rowRem.on('click', function() {
		tr.remove();
		Rte.checkSelection();
	});
	handles.els.rowAddAfter.on('click', function() {
		tr.clone().insertAfter(tr);
		Rte.checkSelection();
	});
	handles.els.colRem.on('click', function() {
		$.each(table.children('tbody')[0].children, function() {
			$(this.children[index]).remove();
		});
		Rte.checkSelection();
	});
	handles.els.colAddRight.on('click', function() {
		$.each(table.children('tbody')[0].children, function() {
			$('<td>&nbsp;</td>').insertAfter(this.children[index]);
		});
		Rte.checkSelection();
	});
});

/* original image */
Rte.ui.setItem('ImgOriginal', {
	enable(el) {
		el = $(el);
		return el.is('img');
	},
	click(e) {
       var $img = $(Rte.element);
       var url = $img.attr('src').replace(/\/(w|h|zoom|vpos|hpos)-[^\/]+/g,'');
	   if (e.target.classList.contains('-ret')) {
          ImageRealSize(url, function(w,h) {
              w /= 2; h /= 2;
              // vorgängig wird dem Server per Cookie mitteilt, dass er er die doppelte Auflösung ausliefern soll
              new dbFile(Rte.element).set('w',w).set('h',h).set('max',0);
              make(w,h);
          });
       } else {
          make('auto', 'auto');
       }
       function make(w,h) { // todo: c1-ratio
          $img.attr('src',url)
          .attr({width:w,height:h})
          .css({width:w,height:h});
          Rte.fire('input');
		  Rte.fire('elementchange');
       }
	},
	el: c1.dom.fragment('<div><div style="display:flex;">'+
		  '<span class="-item"       style="width:61px" title="Originalgrösse">Original</span> '+
		  '<span class="-item -ret"  style="width:61px" title="Halbe Grösse">Retina</span> '+
        '</div><div>').firstChild
});

Rte.ui.config = {
	rteDef:{
		main:['LinkInput','Bold','Insertunorderedlist','Link','Removeformat','Format','Style'],
		more:['Italic','Insertorderedlist','Strikethrough','Underline','Hr','Code','Table','Speech','LinkTarget','ImgOriginal','ImgOriginalRetina','AttributeTitle','ImageDimension','Tree']
	},
	rteMin:{
		main:['Bold','Insertunorderedlist','Link','Style']
	},
	rteFull:{
		main:['LinkInput','Bold','Insertunorderedlist','Insertorderedlist','Link','Code','Removeformat','Format','Style','Color','Backcolor']
	}
};
