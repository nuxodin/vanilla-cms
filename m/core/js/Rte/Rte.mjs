/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
import '../c1/NodeCleaner.mjs?qgUniq=1e3f564';
import './crossbrowser.mjs?qgUniq=4d068ab';

window.Rte = {
	range : {},
	rangeStaticValues : {},
	checkSelection() {
		/*
		 * Problem:
		 * Es sollte auch nach neuem element geprüft werden wenn eltern elemente geändert werden
		 * jetzt ist es so, dass die Selection und das element gleich bleiben obwohl sich die dom struktur geänder hat.
		 *
		 * In webkit ist das Element dann z.T nicht mehr das selbe. dann gehts... (ul maker)
		 *  */
		const sel = getSelection();
		if (!sel.rangeCount) return;
		/* new selection? */
		const newRange = sel.getRangeAt(0).cloneRange(); // cloneRange() needed?
		/* use compareBoundaryPoints? (god example: http://help.dottoro.com/ljxgoxcb.php) */
		const same = newRange.startContainer === Rte.rangeStaticValues.startContainer
				&& newRange.startOffset === Rte.rangeStaticValues.startOffset
				&& newRange.endContainer === Rte.rangeStaticValues.endContainer
				&& newRange.endOffset === Rte.rangeStaticValues.endOffset;
		if (same) return;
		Rte.range = newRange;
		Rte.rangeStaticValues = c1.ext(newRange);

		/* new element? */
		let tmp = qgSelection.element(); // todo

		if (tmp.tagName !== 'IMG') {
			tmp = Rte.range.commonAncestorContainer.data !== undefined ? Rte.range.commonAncestorContainer.parentNode : Rte.range.commonAncestorContainer;
		}
		let newElement = tmp;
		if (newElement === Rte.active) newElement = false;
		if (Rte.element !== newElement) {
			Rte.element = newElement;
			Rte.trigger('elementchange');
		}
		Rte.trigger('selectionchange');
	},
	manipulate(fn) {
		setTimeout(function() {
			getSelection().c1SetRange(Rte.range);
			fn && fn();
			Rte.checkSelection();
	        Rte.trigger('input');
			Rte.active.focus(); // firefox
		}, 80);
	},
	/*
	modifySelection(fn) {
		var els = [Rte.element];
		if (!Rte.range.collapsed) {
			console.log(Rte.range)
			if (rangeIsElement(Rte.range)) {
				els = [Rte.range.commonAncestorContainer];
			} else {
				els = rangeGetElements(Rte.range);
			}
		} else if (els[0] === Rte.active) {
			rangeExpandToElements(Rte.range);
			el = document.createElement('span');
			Rte.range.surroundContents(el);
			Rte.range.selectNodeContents(el);
		}
		fn(els);

		Rte.trigger('input');

		setTimeout(function() {
			var s = getSelection();
			s.c1SetRange(Rte.range);
			Rte.checkSelection();
		},90);
	},
	*/
	addUiElement(el) { // really needed?
		const activate = function(e) {
			Rte.dontBlur = true;
			const gMousedown = function(e) {
				if (el.contains(e.target)) return;
				document.removeEventListener('mousedown',gMousedown);
				Rte.dontBlur = false;
				if (!Rte.active) return;
				if (Rte.active.contains(e.target)) return;
				Rte.trigger('deactivate');
	            Rte.active = false;
			};
			document.addEventListener('mousedown',gMousedown);
			e.stopPropagation();
		};
		el.addEventListener('mousedown',activate);
	},
	isTarget(el) {
		return el.isContentEditable && el.tagName!=='INPUT' && el.tagName!=='TEXTAREA' && el.tagName!=='SELECT';
	},
	init() {
		const root = window;
		root.addEventListener('focus',e=>{
	        if (!Rte.isTarget(e.target)) return;
	        if (Rte.active !== e.target) {
		        Rte.active = e.target;
	            Rte.trigger('activate');
		        Rte.checkSelection();
	        }
		},true);
		root.addEventListener('blur',e=>{
	        if (!Rte.isTarget(e.target)) return;
	        if (!Rte.dontBlur && Rte.active) {
	            Rte.trigger('deactivate');
	            Rte.active = false;
	        }
		},true);
		root.addEventListener('keyup',e=>{ // use selectionchange?
	        if (!Rte.isTarget(e.target)) return;
			if (e.which === 27) {
				document.body.focus();
				document.activeElement.blur();
				window.getSelection().removeAllRanges();
				return;
			}
	        Rte.checkSelection();
	        Rte.trigger('input',e);
		},true);
		root.addEventListener('mouseup',e=>{ // use selectionchange?
			if (!Rte.active) return; // new 15.7.17 // zzz, if used: "root.addEventListener('selectionchange',e=>{"
            if (!Rte.isTarget(e.target)) return;
	        Rte.checkSelection();
		},true);
		root.addEventListener('beforeunload',()=>{ // blur before unload (save), needed?
			if (Rte.active) {
				Rte.active.blur();
				// let event = new Event('blur',{'bubbles': true,'cancelable': true});
				// Rte.active.dispatchEvent(event);
			}
		},true);
	}
};
c1.ext(c1.Eventer,Rte);


qgExecCommand('enableInlineTableEditing', false, false); // bug: if i first click in the table the nativ handles appear
document.addEventListener('DOMContentLoaded',function(){
	qgExecCommand('enableObjectResizing', false, false);
});


// fake Selection
{
	let el = c1.dom.fragment(`<style>.qgRte_fakeSelection {}</style>`).firstChild;
	document.head.append(el);
	let style = el.sheet.cssRules[0].style;
	style.background = 'rgba(150,150,150,.9)';
	style.color = '#fff';
	Rte.fakeSelection = {
		addClass   (el){ el.classList.add   ('qgRte_fakeSelection'); },
		removeClass(el){ el.classList.remove('qgRte_fakeSelection'); },
	}
}

//ie: prevent resizable handle bug: if i move the image, it will show the handles
/*
document.addEventListener('mouseup', e=>{
	if (e.button!==0) return;
	if (e.target.isContentEditable && e.target.tagName === 'IMG' && !getSelection().isCollapsed && getSelection().toString() === '') {
		qgSelection.toElement(e.target);
		setTimeout(function() {
			qgSelection.toElement(e.target);
			Rte.checkSelection();
		});
	}
});
*/

Rte.init();

/* prevent select on contextmenu */
document.addEventListener('mousedown',function(e){
	if (!e.target.isContentEditable) return;
	e.which === 3 && e.preventDefault();
});
/* cleaner */
{
	let Cleaner;
	Rte.on('input', function() {
		if (!Cleaner) Cleaner = new c1.NodeCleaner();
		Cleaner.cleanContents(Rte.active, true);
	});
}

/* force li's in contenteditable uls */
{
	let check = function(){
		for (let child of Rte.active.childNodes) {
			if (child.tagName === 'LI') continue;
			if (child.nodeType === 3 && !child.textContent.trim()) continue;
			if (child.nodeName === 'UL') {
				child.removeNode();
				continue;
			}
			let li = document.createElement('li');
			child.before(li);
			li.append(child);
		}
	}
	Rte.on('activate',()=>{
		if (Rte.active.tagName !== 'UL') return;
		check()
		Rte.active.addEventListener('input', check);
	});
	Rte.on('deactivate',()=>{
		if (Rte.active.tagName !== 'UL') return;
		Rte.active.removeEventListener('input', check);
	});
}
/* force p tag inside contenteditable divs */
document.addEventListener('input',function(e){
	if (!e.target.isContentEditable) return;
	if (e.target.tagName !== 'DIV') return;
	const sel = getSelection();
	const range = sel.c1GetRange();
	const text = range.startContainer;
	const offset = range.startOffset;
	if (text.nodeType !== 3) return; /* text-node */
	if (text.parentNode === e.target) { // warp blank text-nodes with p
		const p = document.createElement('p');
		text.after(p);
		p.append(text);
		range.setStart(text, offset);
		sel.c1SetRange(range);
	} else { // replace every div with a p
		const div = text.parentNode;
		if (div.tagName !== 'DIV') return;
		//if (div.parentNode !== e.target) return;
		const p = document.createElement('p');
		div.after(p);
		p.append(div)
		div.removeNode();
		range.setStart(text, offset);
		sel.c1SetRange(range);
	}
});
/* force p's to not contain a ul */
document.addEventListener('input',function(e){
	if (!e.target.isContentEditable) return;
	if (e.target.tagName !== 'DIV') return;
	const sel = getSelection();
	const range = sel.c1GetRange();
	const text = range.startContainer;
	const offset = range.startOffset;
	if (text.nodeType !== 3) return; /* text-node */
	const ul = text.parentNode.parentNode;
	if (ul.tagName !== 'UL') return;
	const p = ul.parentNode;
	if (p.tagName !== 'P') return;
	if (p.childNodes.length !== 1) return;
	p.removeNode();
	range.setStart(text, offset);
	sel.c1SetRange(range);
});

/* force br's tag inside contenteditable if not a div, (todo, should it be p,h1,h2...?) */
document.addEventListener('keydown',function(e){
	if (!e.target.isContentEditable) return;
	if (e.target.tagName === 'DIV') return;
	if (e.keyCode === 13) {
		const br = document.createElement('br');
		let range = getSelection().c1GetRange();
		range.insertNode(br);
		br.append(document.createTextNode(' \n\n'))
		range.setStartAfter(br)
		getSelection().c1SetRange(range);
		e.preventDefault();
	}
});

/* prevent links inside links */
document.addEventListener('input',function(e){
	if (!e.target.isContentEditable) return;
	if (!e.target.closest('A')) return;
	let a;
	while (a = e.target.c1Find('a')) a.removeNode();
});

/*  */
document.addEventListener('input',function(e){
	if (!e.target.isContentEditable) return;
	const check = function(node) {
		for (let el of node.children) {
			check(el);
		}
		if (isPHX(node.parentNode) && isPHX(node)) {
			node.removeNode();
		}
	}
	function isPHX (node){
		const tag = node.tagName;
		return tag === 'p' || tag === 'H1' || tag === 'H2' || tag === 'H3' || tag === 'H4' || tag === 'H5' || tag === 'H6'
	}
	check(e.target)
});
