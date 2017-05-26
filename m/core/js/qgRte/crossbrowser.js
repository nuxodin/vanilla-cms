/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
// firefox resize images: enableObjectResizing
'use strict';
window.qgQueryCommandState = function(cmd) {
	try{
		return document.queryCommandState(cmd);
	} catch(e) { /*zzz*/ }
};
window.qgQueryCommandValue = function(cmd) {
	try{
		return document.queryCommandValue(cmd);
	} catch(e) { /*zzz*/ }
};
window.qgExecCommand = function(com,x,val) {
	let _ = qgExecCommand;
	if (!_.cmdUsed) {
		try {
			document.execCommand("styleWithCSS", false, false);
		} catch (e) {}
		_.cmdUsed = true;
	}
	switch (com) {
		case 'formatblock':
				document.execCommand(com,x,'<'+val+'>');
				document.execCommand(com,x,val);
			break;
		default:
			try {
				document.execCommand(com,x,val);
			} catch(e) {}
	}
};

window.qgSelection = {
	element() {
		let el;
		if (!getSelection().rangeCount) return;
		let r = getSelection().getRangeAt(0);
		if (!r.collapsed && r.startContainer.childNodes.length) { // images
			el = r.startContainer.childNodes[r.startOffset];
		} else {
			el = r.commonAncestorContainer;
		}
		while (el.nodeType === 3) el = el.parentNode;
		return el;
	},
	text() {
		return window.getSelection().getRangeAt(0).toString();
	},
	isElement() {
		let el = this.element();
		let text = el.textContent || el.innerText || '';
		return text === this.text();
	},
	bookmark() {
		return getSelection().getRangeAt(0).cloneRange();
	},
	fromBookmark(bookmark) {
		let sel = getSelection();
		sel.removeAllRanges();
		sel.addRange(bookmark);
	},
	toElement(el) {
		let s = getSelection();
		let r = document.createRange();
		s.removeAllRanges();
		if (!el) throw 'el is required';
		r.selectNode(el);
		s.addRange(r);
	},
	toChildren(el) {
		let s = getSelection();
		let r = document.createRange();
		r.selectNodeContents(el);
		s.removeAllRanges();
		s.addRange(r);
	},
	surroundContents(el) {
		let range = getSelection().getRangeAt(0);
		range.surroundContents(el);
		qgSelection.toChildren(el);
		return el;
	},
	collapsed() {
		return getSelection().getRangeAt(0).collapsed;
	},
	collapse(where) {
		try{ // firefox has an error
			where === 'start' ? getSelection().collapseToStart() : getSelection().collapseToEnd();
		} catch(e) {}
	},
	rect() {
		let r = getSelection().getRangeAt(0);
		let pos = r.getBoundingClientRect();
		if (r.collapsed && pos.top===0 && pos.left ===0) { // bug in chrome, webkit
			let tmpNode = document.createTextNode('l');
			r.insertNode(tmpNode);
			pos = r.getBoundingClientRect();
			tmpNode.remove();
		}
		return pos;
	}
};

// if contenteditable inside a link
document.addEventListener('click', e=>{
	if (e.button !== 0) return;
	if (!e.target.isContentEditable) return;
	e.preventDefault(); // inside links
});
// img selectable (webkit,blink) and resize handles
document.addEventListener('mousedown', e=>{
	if (e.button !== 0) return;
	if (!e.target.isContentEditable) return;
	if (e.target.tagName === 'IMG') {
		qgSelection.toElement(e.target);
		qgImageResizeUi(e);
	}
});

{
	let checkIntr;
	let img = null;

	window.qgImageResizeUi = function(e) {
		//Browser.Engine.name === 'presto' && e.preventDefault(); // disables move in safari
		img = e.target;
		let hide = function(e) {
			if (!e || e.target!==img) {
				cont.remove();
				document.removeEventListener('mousedown',hide);
			}
		};
		document.addEventListener('mousedown',hide);
		document.body.append(cont);
		cont.c1ZTop();
		positionize();
		function check() {
			cont.parentNode && img.offsetHeight ? positionize() : (hide(), clearInterval(checkIntr));
		}
		clearInterval(checkIntr);
		checkIntr = setInterval(check, 100);
	};
	let positionize = function() {
		let c      = img.getBoundingClientRect(), // todo: fastdom
			body   = document.documentElement.getBoundingClientRect(),
			bottom = c.bottom - body.top  - 6,
			right  = c.right  - body.left - 6;
		requestAnimationFrame(()=>{
			X.style.left    = right + 'px';                       X.style.top    = (bottom - img.offsetHeight / 2) + 'px';
			Y.style.left    = (right - img.offsetWidth / 2)+'px'; Y.style.top    = bottom + 'px';
			XY.style.left   = right + 'px';                       XY.style.top   = bottom + 'px';
			info.style.left = right + 16 + 'px';                  info.style.top = bottom + 16 + 'px';
		});
	};
	let startFn = function(e) {
		let startM   = {x: e.pageX, y: e.pageY};
		let startDim = {x: img.offsetWidth, y: img.offsetHeight};
		let dragger = e.target;
		let moveFn = function(e) {
			let w = dragger === Y ? startDim.x : Math.max(1, startDim.x + e.pageX - startM.x);
			let h = dragger === X ? startDim.y : Math.max(1, startDim.y + e.pageY - startM.y);
			if (!e.ctrlKey && dragger === XY) {
				if (startDim.x / startDim.y < w / h) {
					h = parseInt(startDim.y / startDim.x * w);
				} else {
					w = parseInt(startDim.x / startDim.y * h);
				}
			}
			let dh = parseFloat(h - startDim.y);
			let dw = parseFloat(w - startDim.x);
			requestAnimationFrame(()=>{
				img.style.width  = w + 'px';
				img.style.height = h + 'px';
				info.innerHTML = w+' x '+h+' ('+(dw>0?'+'+dw:dw)+','+(dh>0?'+'+dh:dh)+')';
				info.style.display = 'block';
				info.c1ZTop();
			})
			positionize();
		};
		let stopFn = function() {
			img.dispatchEvent(new Event('qgResize',{bubbles:true}));
			document.removeEventListener('mousemove', moveFn);
			document.removeEventListener('mouseup', stopFn);
		};
		document.addEventListener('mousemove', moveFn);
		document.addEventListener('mouseup', stopFn);
		e.preventDefault();
		e.stopPropagation();
	};
	let itemCss = ';position:absolute; background-color:#fff; border:1px solid black; height:12px; width:12px; box-sizing:border-box';
	let cont = c1.dom.fragment(
	'<div class=q1Rst style="position:absolute; top:0; left:0; width:100%; height:0">'+
		'<div class=-x  style="cursor:e-resize '+itemCss+'"></div>'+
		'<div class=-y  style="cursor:s-resize '+itemCss+'"></div>'+
		'<div class=-xy style="cursor:se-resize'+itemCss+'" title="press ctrl to disable aspect ratio"></div>'+
		'<div class=-info style="position:absolute; background: #fafafa; box-shadow:0 0 3px; font-size:11px; color:#333; padding:2px 4px; border-radius:2px"></div>'+
	'</div>').firstChild;
	let X  = cont.c1Find('>.-x');
	let Y  = cont.c1Find('>.-y');
	let XY = cont.c1Find('>.-xy');
	let info = cont.c1Find('>.-info');
	cont.addEventListener('mousedown', startFn);
}


/* contenteditable focus bug */
if (/AppleWebKit\/([\d.]+)/.exec(navigator.userAgent) && document.caretRangeFromPoint) {
    document.addEventListener('DOMContentLoaded', function(){
        let fixEl = document.createElement('input');
        fixEl.style.cssText = 'width:1px;height:1px;border:none;margin:0;padding:0; position:fixed; top:0; left:0';
        fixEl.tabIndex = -1;
        let shouldNotFocus = null;
        function fixSelection(){
            document.body.appendChild(fixEl);
            fixEl.focus();
            fixEl.setSelectionRange(0,0);
            setTimeout(function(){
                document.body.removeChild(fixEl);
            },100)
        }
        function checkMouseEvent(e){
            if (e.target.isContentEditable) return;
            let range = document.caretRangeFromPoint(e.clientX, e.clientY);
			if (!range) return;
            let wouldFocus = getContentEditableRoot(range.commonAncestorContainer);
            if (!wouldFocus || wouldFocus.contains(e.target)) return;
            shouldNotFocus = wouldFocus;
            setTimeout(function(){
                shouldNotFocus = null;
            });
            if (e.type === 'mousedown') {
                document.addEventListener('mousemove', checkMouseEvent, false);
            }
        }
        document.addEventListener('mousedown', checkMouseEvent, false);
        document.addEventListener('mouseup', function(){
                document.removeEventListener('mousemove', checkMouseEvent, false);
        }, false);
        document.addEventListener('focus', function(e){
            if (e.target !== shouldNotFocus) return;
            if (!e.target.isContentEditable) return;
            fixSelection();
        }, true);
        document.addEventListener('blur', function(e){
			if (e.target !== shouldNotFocus) return;
        	if (!e.target.isContentEditable) return;
        	setTimeout(function(){
        		if (document.activeElement === e.target) return;
        		if (!e.target.contains(getSelection().baseNode)) return;
                fixSelection();
        	})
	    }, true);
    });
}

function getContentEditableRoot(el) {
    if (el.nodeType === 3) el = el.parentNode;
    if (!el.isContentEditable) return false;
    while (el) {
        let next = el.parentNode;
        if (next.isContentEditable) {
            el = next;
            continue
        }
        return el;
    }
}
