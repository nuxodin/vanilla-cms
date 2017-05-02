Rte = {
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
		var sel = getSelection();
		if (!sel.rangeCount) return;

		/* new selection? */
		var newRange = sel.getRangeAt(0).cloneRange(); // cloneRange() needed?

		/* use compareBoundaryPoints? (god example: http://help.dottoro.com/ljxgoxcb.php) */
		var same = newRange.startContainer === Rte.rangeStaticValues.startContainer
					&& newRange.startOffset === Rte.rangeStaticValues.startOffset
					&& newRange.endContainer === Rte.rangeStaticValues.endContainer
					&& newRange.endOffset === Rte.rangeStaticValues.endOffset;
		if (same) return;
		Rte.range = newRange;
		Rte.rangeStaticValues = c1.ext(newRange);

		/* new element? */
		var tmp = qgSelection.element(); // todo

		if (tmp.tagName !== 'IMG') {
			tmp = Rte.range.commonAncestorContainer.data !== undefined ? Rte.range.commonAncestorContainer.parentNode : Rte.range.commonAncestorContainer;
		}
		var newElement = tmp;
		if (newElement === Rte.active) newElement = false;
		if (Rte.element !== newElement) {
			Rte.element = newElement;
			Rte.fire('elementchange');
		}
		Rte.fire('selectionchange');
	},
	manipulate(fn) {
		setTimeout(function() {
			var s = getSelection(); s.removeAllRanges(); s.addRange(Rte.range);
			fn && fn();
			Rte.checkSelection();
	        Rte.fire('input');
			Rte.active.focus(); // firefox
		}, 80);
	},
	modifySelection(fn) {
		var els = [Rte.element];
		if (!Rte.range.collapsed) {
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

		Rte.fire('input');

		setTimeout(function() {
			var s = getSelection();
			s.removeAllRanges();
			s.addRange(Rte.range);
			Rte.checkSelection();
		},90);
	},
	addUiElement(el) { // really needed?
		var activate = function() {
			Rte.dontBlur = true;
			var gMousedown = function(e) {
				if (el===e.target || $.contains(el,e.target)) return;
				document.removeEventListener('mousedown',gMousedown);
				Rte.dontBlur = false;
				if (Rte.active===e.target || $.contains(Rte.active,e.target)) return;
				Rte.fire('deactivate');
	            Rte.active = false;
			};
			document.addEventListener('mousedown',gMousedown);
		};
		el.addEventListener('mousedown',activate);
	},
	isTarget(el) {
		return el.isContentEditable && el.tagName!=='INPUT' && el.tagName!=='TEXTAREA' && el.tagName!=='SELECT';
	},
	init() {
		var root = window;
		root.addEventListener('focus',e=>{
	        if (!Rte.isTarget(e.target)) return;
	        if (Rte.active !== e.target) {
		        Rte.active = e.target;
	            Rte.fire('activate');
		        Rte.checkSelection();
	        }
		},true);
		root.addEventListener('blur',e=>{
	        if (!Rte.isTarget(e.target)) return;
	        if (!Rte.dontBlur && Rte.active) {
	            Rte.fire('deactivate');
	            Rte.active = false;
	        }
		},true);
		root.addEventListener('keyup',e=>{
	        if (!Rte.isTarget(e.target)) return;
			if (e.which === 27) {
				document.body.focus();
				document.activeElement.blur();
				window.getSelection().removeAllRanges();
				return;
			}
	        Rte.checkSelection();
	        Rte.fire('input',e);
		},true);
		root.addEventListener('mouseup',e=>{
            if (!Rte.isTarget(e.target)) return;
	        Rte.checkSelection();
		},true);
		root.addEventListener('beforeunload',()=>{ // blur before unload (save)
            //$.ajaxSetup({async:false});
			Rte.active && $(Rte.active).trigger('blur');
		},true);
	}
};
c1.ext(qg.Eventer,Rte);

Rte.on('activate', function() {
	//insertBrOnReturn ?
	//styleWithCSS ?
	qgExecCommand('enableObjectResizing', false, false);
	qgExecCommand('enableInlineTableEditing', false, false); // bug: if i first click in the table the nativ handles appear
});

//ie: prevent resizable handle bug: if i move the image, it will show the handles
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

// prevent select on contextmenu
document.addEventListener('mousedown',function(e){
	if (!e.target.isContentEditable) return;
	e.which === 3 && e.preventDefault();
});

Rte.init();


{ // cleaner
	let Cleaner;
	Rte.on('input', function() {
		if (!Cleaner) Cleaner = new c1.NodeCleaner();
		Cleaner.cleanContents(Rte.active,true);
	});
}

{ // force li's in contenteditable uls
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
		// [...root.children].forEach(child=>{
		// 	console.log(child)
		// })
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
