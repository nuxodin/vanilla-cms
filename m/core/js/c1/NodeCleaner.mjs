/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

var defaultConf = {
	tags                   :null,
	tagsRemove             :null,
	styles                 :null,
	attributes             :null,
	removeHiddenElements   :false,
	removeComments         :true,
	removeEmptyInlineSpans :true,
	removeEmptyElements    :false,
	removeUnusedStyles     :true,
	removeNbsp             :false,
	removeDivers           :false,
	replaceCombiningChars  :true,
};

class NodeCleaner {
	constructor (conf) {
		this.conf = c1.ext(defaultConf, conf);
	}
	cleanContents(el, andChildren) {
		if (!el) return;
		for (let child of el.childNodes) this.clean(child, andChildren);
	}
	clean(el, andChildren) {
		if (el.nodeType === 1) {
			andChildren && this.cleanContents(el, true); // can clean all content => then cleanTag can clean el too.
			el = this.cleanTag(el);
			if (!el) return;
			this.cleanAttributes(el);
			this.cleanStyle(el);
			this.conf['removeUnusedStyles'] && removeUnusedStyles(el);
			// this.cleanClass(el); // todo
			removeUnusedAttributes(el);
			this.conf['removeEmptyInlineSpans'] && removeEmptyInlineSpans(el);

			// beta: remove unused divs
			// if (el.attributes.length === 0 && el.tagName === 'DIV') {
			// 	const display = getComputedStyle(el).getPropertyValue('display');
			// 	let different = false;
			// 	for (let child of el.childNodes) {
			// 		if (child.nodeType === 2) continue;
			// 		if (child.nodeType === 3 && child.data.trim() === '') continue; // ok?
			// 		const chDispl = child.nodeType === 3 ? 'inline' : getComputedStyle(child).getPropertyValue('display');
			// 		if (display !== chDispl) {
			// 			different = true;
			// 			break;
			// 		}
			// 	}
			// 	if (!different) el.removeNode();
			// }

		} else if (el.nodeType === 3) {
			if (this.conf['removeNbsp']) el.data = el.data.replace(/\u00a0/g,' ');
			if (this.conf['replaceCombiningChars']) nodeReplaceCombiningDiaeresis(el);
			// if (el.previousSibling && el.previousSibling.nodeType === 3) { // verbinden
			// 	el.previousSibling.data += el.data;
			// 	el.remove();
			// }
			// if (el.data === '') el.remove();
		} else {
			this.conf['removeComments'] && el.remove();
		}
	}
	cleanTag(el) {
		el = modernize(el);

		var display = getComputedStyle(el).getPropertyValue('display');
		if (this.conf['removeDivers']){
			if (el.tagName==='P' || el.tagName==='DIV') {
				if (el.textContent.trim() === '') {
					el.removeNode();
				}
			}
		}
		if (this.conf['removeEmptyElements']
			&& el.textContent === ''
			&& el.tagName !== 'IMG'
			&& el.tagName !== 'BR'
			&& el.tagName !== 'HR'
			&& !el.querySelector('img')
		) {
			el.remove(); return;  // removeNode: no textContent nevertheless can have img!
		}
		if (this.conf['removeHiddenElements'] && display==='none'                   ) { el.remove(); return; }
		if (this.conf['tagsRemove']           && this.conf['tagsRemove'][el.tagName]) { el.remove(); return; }

		if (!this.conf['tags'])            return el;
		if (this.conf['tags'][el.tagName]) return el;

		var nEl = notInline[display] ? document.createElement('div') : document.createElement('span');
		/* dont loose computed styles. Problem?: links keep colored */
		var computed = getComputedStyle(el);
		var beforeComputed = {};
		for (var i=0, style; style = computed[i++];) {
			beforeComputed[style] = computed.getPropertyValue(style);
		}
		el.hasAttribute('class') && nEl.setAttribute('class', el.getAttribute('class'));
		nEl.className = el.className; // copy each attribute?
		el.replace(nEl);
		nEl.appendChild(el);
		el.removeNode();
		computed = getComputedStyle(nEl);
		for (i=0; style = computed[i++];) {
			if (beforeComputed[style] !== computed.getPropertyValue(style)) {
				nEl.style.setProperty(style, beforeComputed[style]);
			}
		}
		return nEl;
	}
	cleanAttributes(el) {
		if (!this.conf['attributes']) return;
		var attributes = c1.ext(el.attributes);
		for (var i=0, attr; attr = attributes[i++];) {
			var name = attr.name;
			var value = attr.value;
			var allowed = this.conf['attributes'][name];
			if (!allowed) { el.removeAttribute(name); continue; }
			if (allowed === true || allowed === 1) continue;
			// values allowed
			if (allowed[value] || allowed.includes(value)) {
				continue;
			} else {
				el.removetAttribute(name);
			}
		}
	}
	cleanStyle(el) {
		if (!this.conf['styles']) return;
		for (var i=0, style; style = el.style[i++];) {
			var allowed = this.conf['styles'][style];
			if (!allowed) {
				el.style.removeProperty(style); continue;
			}
			if (allowed === true || allowed === 1) continue;
			// values allowed
			var value = el.style.getPropertyValue(style);
			if (style === 'font-family') value = value.replace(/^["']/,'').replace(/["']$/,'');
			//if (allowed.includes) { // isArray (array with allowed values)
				if (allowed[value] || allowed.includes(value)) {
					continue;
				} else {
					el.style.removeProperty(style);
				}
			//}
		}
	}
	/*
	cleanClass(el) {
		if (!el.className) return;
		var allowed = this.conf['classes'];
		if (allowed === undefined) return;
		if (allowed.length < 1) {
			el.className = '';
			return;
		}
		var classes = el.className.split(' ');
		var nClasses = [];
		for (var i=0, cl; cl=classes[i++];) {
			allowed.includes(cl) && nClasses.push(cl);
		}
		el.className = nClasses.join(' ');
	}
	*/
}

c1.NodeCleaner = NodeCleaner;

function removeUnusedStyles(el) {
	// be sure the node is attached to the document
	var computed = getComputedStyle(el),
		beforeOriginal = {},
		beforeComputed = {},
		i=0, style;
	while (style = el.style[i++]) {
		beforeOriginal[style] = el.style.getPropertyValue(style);
		beforeComputed[style] = computed.getPropertyValue(style);
	}
	el.style.cssText = '';
	for (style in beforeOriginal) {
		if (beforeComputed[style] !== computed.getPropertyValue(style)) {
			el.style.setProperty(style, beforeOriginal[style]);
		}
	}
}
function removeUnusedAttributes(el) {
	el.hasAttribute('style') && el.getAttribute('style').trim() === '' && el.removeAttribute('style');
	el.hasAttribute('class') && el.getAttribute('class').trim() === '' && el.removeAttribute('class');
	/* bugs ie8/9/10? */
	if (el.tagName === 'IMG') {
		if (!(el.getAttribute('height')+'').match(/^[0-9]+/)) { el.removeAttribute('height'); }
		if (!(el.getAttribute('width')+'') .match(/^[0-9]+/)) { el.removeAttribute('width');  }
	}
}
function removeEmptyInlineSpans(el) {
	if (el.attributes.length) return;
	var display = getComputedStyle(el).getPropertyValue('display');
	if (display === 'inline' && el.tagName === 'SPAN') {
		 el.removeNode();
	}
}
const notInline = {block:1,table:1,flex:1,grid:1,'list-item':1,'table-cell':1};

// COMBINING DIAERESIS
const combininedChars = [
	['ü','u\u0308'],
	['ü','u\u0308'],
	['Ü','U\u0308'],
	['ä','a\u0308'],
	['Ä','A\u0308'],
	['ö','o\u0308'],
	['Ö','O\u0308'],
].map(data => [data[0], new RegExp(data[1], 'g')] );
function nodeReplaceCombiningDiaeresis(textNode){
	var string = textNode.data
	for (let [char,regexp] of combininedChars) {
		if (string.match(regexp)) { // only replace string if found => otherways cursor position will no be restored
			textNode.data = string.replace(regexp, char);
		}
	}
}

function modernize(n) {
	if (n.hasAttribute('bgcolor')) {
		n.style.backgroundColor = n.getAttribute('bgcolor');
		n.removeAttribute('bgcolor');
	}
	if (n.tagName==='FONT') {
		const span = document.createElement('span');
		const color = n.getAttribute('color');
		const face = n.getAttribute('face');
		const size = n.getAttribute('size');
		color && (span.style.color = color);
		face  && (span.style.fontFamily = face);
		size  && (span.style.fontSize = (parseInt(size)+0.6)/2+'em');
		n.after(span);
		span.append(n);
		n.removeNode();
		return span;
	}
	if (n.tagName==='CENTER') {
		const div = document.createElement('div');
		div.style.textAlign = 'center';
		n.after(div);
		div.append(n);
		n.removeNode();
		return div;
	}
	return n;
}


/* example *
var conf = {
	tags:{
		DIV:true,
		SPAN:true,
	},
	tagsRemove: {
		'FONT':  1,
		'O:P':   1,
		'STYLE': 1,
		'SCRIPT':1,
		'META':  1,
		'LINK':  1,
		'TITLE': 1
	}
	styles:{
		'color':       true,
		'font-weight': true,
		'font-family': {Arial:true, Times:true},
		'font-size':   true,
		'font-style':  true,
	},
	//classes: ['myclass', 'yourClass'], // class:[]
};
var cleaner = new c1.NodeCleaner(conf);
cleaner.clean(el, bool_incContainings );
*/



/* todo?
n.className = n.className.replace('Apple-style-span', '');
n.className = n.className.replace('Apple-converted-space', '');
n.className = n.className.replace(/(^| )Mso[^\s]+/, '');
n.className = n.className.replace(/(^| )Apple-[^\s]+/, '');
n.removeAttribute('_moz_dirty');
n.removeAttribute('id');
if (n.style.position==='absolute') { n.style.position = ''; }
*/
