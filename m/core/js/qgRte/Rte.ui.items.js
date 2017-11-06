/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
/*
let x = my.setItem('Bold',
	{
		shortcut:'l'
	}
);
x.addEventListener('mousedown', function() {
	Rte.modifySelection(function(els) {
		let first = $(els[1]||els[0]);
		let act = first.hasClass('SmallText') ? 'removeClass' : 'addClass';
		for (let i = els.length, el; el = els[--i];) {
			$(el)[act]('SmallText');
		}
	});
});
*/
Rte.ui.setItem('Bold', 					{cmd:'bold',		shortcut:'b', xenable:':not(img)'} );
Rte.ui.setItem('Italic', 				{cmd:'italic',		shortcut:'i', xenable:':not(img)'} );
Rte.ui.setItem('Insertunorderedlist',	{cmd:'insertunorderedlist',shortcut:'8'});
Rte.ui.setItem('Insertorderedlist',		{cmd:'insertorderedlist',shortcut:'9'});
Rte.ui.setItem('Underline', 			{cmd:'underline',	shortcut:'u', xenable:':not(img)'});
Rte.ui.setItem('Undo', 					{cmd:'undo',	check:false});
Rte.ui.setItem('Redo', 					{cmd:'redo',	check:false});
Rte.ui.setItem('Unlink', 				{cmd:'unlink',	check:false});
Rte.ui.setItem('Hr', 					{cmd:'inserthorizontalrule', check:false});
Rte.ui.setItem('Strikethrough', 		{cmd:'strikethrough', xenable:':not(img)'});

/* bred-crumb *
let list = $('<div style="padding:2px; margin:2px; color:#000; background:linear-gradient(#fff,#ccc); xborder-radius:3px; box-shadow: 0 0 1px #000;">');
Rte.ui.setItem( 'Tree', {
	el:list[0],
	//enable(el) {  },
	check(el) {
		list.html('');
		if (el===Rte.active) return;
		$(el).parentsUntil(Rte.active).addBack().each(function(i,el) {
			let btn = $('<span style="border-right:1px solid #bbb; padding:1px 3px; cursor:pointer">'+el.tagName+'</span>')
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
{
	let opts = Rte.ui.setSelect('Format',{
		click(e) {
			let tag = e.target.getAttribute('value');
			tag && qgExecCommand('formatblock',false,tag);
			let stat = qgQueryCommandValue('formatblock');
			for (let el of opts.children) {
				el.className = el.tagName.toLowerCase()===stat ? '-selected' : '';
			}
		},
		check() {
			let stat = qgQueryCommandValue('formatblock');
			opts.previousElementSibling.innerHTML = Rte.element ? stat : 'Format';
		}
	});
	opts.innerHTML =
	'<p  value=p >Paragraph</p>'+
	'<h1 value=h1>Heading 1</h1>'+
	'<h2 value=h2>Heading 2</h2>'+
	'<h3 value=h3>Heading 3</h3>'+
	'<h4 value=h4>Heading 4</h4>'+
	'<h5 value=h5>Heading 5</h5>'+
	'<h6 value=h6>Heading 6</h6>'
}
/* CSS classes */
{
	function useClass(cl) { return cl.match(/^[A-Z]/); };
	let hasClasses; /* check if this-handle is used */
	let check = function(el) {
		let classes = getPossibleClasses(el);
		for (let cl of Object.keys(classes)) {
			hasClasses = hasClasses || useClass(cl);
		}
		sopts.parentElement.style.display = hasClasses ? '' : 'none';
	}.c1Debounce(150);

	let sopts = Rte.ui.setSelect('Style', {
		check() {
			check();
			let classes = Rte.element && Rte.element.className.split(' ').filter(useClass).join(' ') || 'Style';
			sopts.previousElementSibling.innerHTML = classes;
		},
		click() {
			sopts.innerHTML = '';
			let el = qgSelection.isElement() || qgSelection.collapsed() ? Rte.element : null;
			// if (el === Rte.active) return;
			let classes = getPossibleClasses(el);
			for (let sty of Object.keys(classes)) {
				if (!useClass(sty)) return;
				let has = el && el.classList.contains(sty);
				let d = c1.dom.fragment('<div class="'+sty+'">'+sty+'</div>').firstChild;
				sopts.append(d);
				has && d.classList.add('-selected');
				d.onmousedown = function() {
					Rte.manipulate(()=>{
						if (!el) {
							el = qgSelection.surroundContents(document.createElement('span'));
							if (getComputedStyle(el)['display'] === 'block') { // zzz
								console.warn('used?');
								let nEl = document.createElement('div');
								el.parentNode.replaceChild(nEl, el);
								qgSelection.toChildren(el);
							}
						}
						el.classList.toggle(sty, !has);
					});
				};
				// d.css({
				// 	fontSize:parseInt(d.css('fontSize')).limit(9,18),
				// 	margin:parseInt(d.css('margin')).limit(0,4),
				// 	padding:parseInt(d.css('padding')).limit(0,4),
				// 	letterSpacing:parseInt(d.css('letterSpacing')).limit(0,11),
				// 	borderWidth:parseInt(d.css('borderWidth')).limit(0,4)
				// });
			}
		}
	});
}

/* show invisibles *
{
	function replaceContents(node){
		for (const el of node.childNodes) replaceNode(el);
	}
	function replaceNode(node) {
		if (node.nodeType === 3) { // text-nodes
			let offset = 0;
			for (const char of node.data) {
				if (char === '\xa0') {  // nbsp
					//var x = node.splitText(offset);
				}
				++offset;
			}
		} else {
			replaceContents(node);
		}
	}
	Rte.ui.setItem('ShowInvisibleChars', {
		click(e) {
			let root = Rte.active;
			replaceContents(root);
		}
		,shortcut:'space'
	});
}
/* clean / remove format */
{
	const removeTags = ['FONT','O:P','SDFIELD','SPAN'].reduce((obj, item)=>{ obj[item]=1; return obj; }, {});
	function cleanNode(node) {
	    if (!node) return;
		cleanContents(node);
	    node.nodeType === Node.COMMENT_NODE && node.remove();
		if (node.nodeType === Node.ELEMENT_NODE) {
			if (!Rte.active.contains(node)) return;
			node.removeAttribute('style');
			node.removeAttribute('class');
			node.removeAttribute('align');
			node.removeAttribute('valign');
			node.removeAttribute('border');
			node.removeAttribute('cellpadding');
			node.removeAttribute('cellspacing');
			node.removeAttribute('bgcolor');
			removeTags[node.tagName] && node.removeNode();
			if (node.tagName !== 'IMG') {
				node.removeAttribute('width');
				node.removeAttribute('height');
			}
		}
	}
	function cleanContents(node){
		if (node.childNodes) for (let child of node.childNodes) cleanNode(child);
	}
	Rte.ui.setItem('Removeformat', {
		click(e) {
			let root = e.ctrlKey ? Rte.element : Rte.active;
			cleanContents(root);
		}
		,shortcut:'space'
	});
}
{ /* code */
	let wrapper = c1.dom.fragment(
		'<div id=qgRteHtml>'+
			'<textarea spellcheck=false class=c1Rst></textarea>'+
			'<style>'+
			'	#qgRteHtml { transition:all .3s; transform:translateY(100%); opacity:0; position:fixed; border:2px solid black; top:40%; left:1%; bottom:1%; right:1%; background:#fff; color:#000; margin:auto; box-shadow:0 0 20px} '+
			'	#qgRteHtml > textarea { position:absolute; top:0; left:0; right:0; bottom:0; width:100%; height:100%; font:11px monospace; } '+
			'	html:hover #qgRteHtml { opacity:1; transform:translateX(0) } '+
			'</style>'+
		'</div>'
	).firstChild;
	let html = wrapper.firstChild;
	let el = Rte.ui.setItem('Code', {
		click() {
			let el = Rte.active;
			let sel = window.getSelection();
			let code;
	        if (sel.rangeCount > 0) {
				let range = sel.getRangeAt(0);
	            let startTextNode = document.createTextNode('marker_start_so9df8as0f0');
	            let endTextNode   = document.createTextNode('marker_end_laseg08a0egga');
				let tmpRange = range.cloneRange();
	            tmpRange.collapse(false);
	            tmpRange.insertNode(endTextNode);
				tmpRange = range.cloneRange();
				tmpRange.collapse(true);
	            tmpRange.insertNode(startTextNode);
				code = domCodeIndent(el.innerHTML);

				startTextNode.remove();
				endTextNode.remove();

				let start = code.indexOf('marker_start_so9df8as0f0');
				code = code.replace('marker_start_so9df8as0f0','');
				let end = code.indexOf('marker_end_laseg08a0egga');
				code = code.replace('marker_end_laseg08a0egga','');

				let brsTotal = (code.match(/\n/g)||[]).length;
				let brs 	 = brsTotal && (code.substr(0,start).match(/\n/g)||[]).length;

				setTimeout(()=>{
					html.focus();

					let y = parseInt((html.scrollHeight / brsTotal)*brs - 250);
					brs && (html.scrollTop = y);

					html.setSelectionRange(start, end);
				},10);
			} else {
				code = domCodeIndent(el.innerHTML);
			}
			html.value = code;
			html.onkeyup = html.onblur = function(){
				el.innerHTML = html.value.replace(/\s*\uFEFF\s*/g,'');
				el.dispatchEvent(new Event('input',{'bubbles':true,'cancelable':true}));
			}
			document.body.append(wrapper);
			wrapper.c1ZTop();

			function hide(e) {
				if (e.which===27 || e.target !== html) {
					wrapper.remove();
					document.removeEventListener('keydown',hide);
					document.removeEventListener('mousedown',hide);
					el.focus();
				}
			};
			setTimeout(()=>{
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
	click() {
		let table = c1.dom.fragment('<table><tr><td>&nbsp;<td>&nbsp;<tr><td>&nbsp;<td>&nbsp;</table>').firstChild;
		let r = getSelection().getRangeAt(0);
		r.deleteContents();
		r.insertNode(table);
		getSelection().collapse(table.c1Find('td'),0);
	}
});
/* delete Element */
Rte.ui.setItem('Del',{
	click(el) { Rte.element.removeNode(); },
	el: c1.dom.fragment('<a style="color:red">Element löschen</a>').firstChild
});
/* Target */
Rte.ui.setItem('LinkTarget', {
	enable:'a, a > *',
	check(el) {
		el = el.closest('a');
		let target = el.getAttribute('target');
		return target && target !== '_self';
	},
	click(){
		let el = Rte.element.closest('a');
		let active = this.el.classList.contains('active');
		el.setAttribute('target', active?'_self':'_blank');
		Rte.trigger('input');
		Rte.active.focus();
		Rte.trigger('elementchange');
	},
	el: c1.dom.fragment('<div class="-item -button">Link in neuem Fenster</div>').firstChild
});
{ /* Titletag */
	let el = c1.dom.fragment('<table style="clear:both"><tr><td style="width:84px">Titel<td><input>').firstChild;
	let inp = el.c1Find('input');
	inp.addEventListener('keyup', function() {
		Rte.element.setAttribute('title',inp.value);
		!inp.value && Rte.element.removeAttribute('title');
		Rte.trigger('input');
	});
	Rte.ui.setItem('AttributeTitle',{
		check(el) {
			inp.value = el ? el.getAttribute('title') : '';
		},
		el: el
	});
}
{ /* Image Attributes */
	let inp = c1.dom.fragment(
		'<table>'+
			'<tr><td style="width:84px">Breite:<td><input class=-x>'+
			'<tr><td>Höhe:<td><input class=-y>'+
			'<tr><td title="Alternativer Text">Alt-Text:<td><input class=-alt>'+
		'</table>').firstChild;
	inp.addEventListener('keyup',e=>{
		let img = Rte.element;
		img.style.width  = inp.c1Find('.-x').value+'px';
		img.style.height = inp.c1Find('.-y').value+'px';
		img.setAttribute('alt', inp.c1Find('.-alt').value);
		if (e.target.classList.contains('-x') || e.target.classList.contains('-y')) {
			Rte.element.dispatchEvent(new Event('qgResize',{bubbles:true}));
		}
		Rte.trigger('input');
	})
	Rte.ui.setItem('ImageDimension', {
		check(el) {
			inp.c1Find('.-x').value = el.offsetWidth;
			inp.c1Find('.-y').value = el.offsetHeight;
			inp.c1Find('.-alt').value = el.getAttribute('alt');
		},
		el:inp,
		enable:'img'
	});
}
/* original image */
Rte.ui.setItem('ImgOriginal', {
	enable: 'img',
	click(e) {
		let img = Rte.element;
		let url = img.getAttribute('src').replace(/\/(w|h|zoom|vpos|hpos|dpr)-[^\/]+/g,'');
		ImageRealSize(url, function(w,h) {
			w /= 2; h /= 2; // vorgängig wird dem Server per Cookie mitteilt, dass er er die doppelte Auflösung ausliefern soll
			make(w,h);
		});
		function make(w,h) { // todo: c1-ratio
			img.setAttribute('src',url);
			img.setAttribute('width',w);
			img.setAttribute('height',h);
			img.style.width  = w+'px';
			img.style.height = h+'px';
			Rte.element.dispatchEvent(new Event('qgResize',{bubbles:true})); // new
			Rte.trigger('input');
			Rte.trigger('elementchange');
		}
	},
	el: c1.dom.fragment('<span class="-item -button" title="Originalgrösse">Originalbild</span>').firstChild
});

/* table handles */
c1.c1Use('tableHandles', tH=>{
	let td, tr, table, index;
	let handles = new tH();
	Rte.on('deactivate',() => handles.hide() );
	function positionize() {
		let e = Rte.element;
		if (!e) return;
		td = e.closest('td');
		if (Rte.active && Rte.active.contains(td)) {
			tr = td.parentNode;
			table = tr.closest('table');
			index = td.cellIndex;
			handles.showTd(td);
		} else {
			handles.hide();
		}
	}
	Rte.on('elementchange activate', positionize);
	handles.root.addEventListener('click',e=>{
		if (e.target.classList.contains('-rowRemove')) {
			tr.remove();
		}
		if (e.target.classList.contains('-rowAdd')) {
			let tr2 = tr.cloneNode(true);
			tr.after(tr2)
		}
		if (e.target.classList.contains('-colRemove')) {
			let trs = table.c1FindAll('> * > tr');
			for (let tr of trs) tr.children[index].remove();
		}
		if (e.target.classList.contains('-colAdd')) {
			let trs = table.c1FindAll('> * > tr');
			for (let tr of trs) {
				let td = c1.dom.fragment('<td>&nbsp;</td>');
				tr.children[index].after(td);
			}
		}
		let hasTds = table.c1FindAll('> * > tr > *').length;
		!hasTds && table.remove();
		getSelection().modify('move', 'right', 'character'); // chrome bug
		getSelection().modify('move', 'left', 'character');
		Rte.checkSelection();
	});
});

Rte.ui.config = {
	rteDef:{
		main:['LinkInput','Bold','Insertunorderedlist','Link','Removeformat','Format','Style'],
		more:['Italic','Insertorderedlist','Strikethrough','Underline','Hr','Code','Table',/*'ShowInvisibleChars',*/'LinkTarget','ImgOriginal','ImgOriginalRetina','AttributeTitle','ImageDimension','Tree']
	},
	rteMin:{
		main:['Bold','Insertunorderedlist','Link','Style']
	},
	rteFull:{
		main:['LinkInput','Bold','Insertunorderedlist','Insertorderedlist','Link','Code','Removeformat','Format','Style','Color','Backcolor']
	}
};
