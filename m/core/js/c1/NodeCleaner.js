/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

!function() { 'use strict';

	var defaultConf = {
		tags                   :null,
		tagsRemove             :null,
		styles                 :null,
		attributes             :null,
		removeHiddenElements   :false,
		removeComments         :true,
		removeEmptyInlineSpans :true,
		removeUnusedStyles     :true
	};

	var NodeCleaner = function(conf) {
        this.conf = c1.ext(defaultConf, conf);
	};
	NodeCleaner.prototype = {
		cleanContents: function(el, andChildren) {
			if (!el) return;
			var els = c1.ext(el.childNodes);
			for (var i=0; el=els[i++];) this.clean(el, andChildren);
		},
		clean: function(el, andChildren) {
			if (el.nodeType === 1) {
				andChildren && this.cleanContents(el, true);
				el = this.cleanTag(el);
				if (!el) return;
				this.cleanAttributes(el);
				this.cleanStyle(el);
				//this.cleanClass(el);
				this.conf['removeUnusedStyles'] && removeUnusedStyles(el);
				removeUnusedAttributes(el);
				this.conf['removeEmptyInlineSpans'] && removeEmptyInlineSpans(el);
			} else if (el.nodeType === 3) {
				/*
				if (el.previousSibling && el.previousSibling.nodeType === 3) { // verbinden
					el.previousSibling.data += el.data;
					el.remove();
				}
				if (el.data === '') el.remove();
				*/
			} else {
				this.conf['removeComments'] && el.remove();
			}
		},
		cleanTag: function(el) {
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
		},
		cleanAttributes: function(el) {
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
		},
		cleanStyle: function(el) {
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
		/*,
		cleanClass: function(el) {
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
	};
	c1.NodeCleaner = NodeCleaner;

	function removeUnusedStyles(el) {
		// be sure the node is attached to the document
		var computed = getComputedStyle(el);
		var beforeOriginal = {};
		var beforeComputed = {};
		for (var i=0, style; style = el.style[i++];) {
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
		if (!el.attributes.length && getComputedStyle(el).getPropertyValue('display') === 'inline' && el.tagName === 'SPAN') {
			 el.removeNode();
		}
	}
	var notInline = {block:1,flex:1,table:1,'list-item':1,'table-cell':1};


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


}();






/* old stuff zzz */

/*
domClean = function(el) {
	var toAbsolute = function(url) {
		var uri = new URI(url);
		return uri.toAbsolute(location.href);
	};
	$.each( el[0].querySelectorAll('*'), function(i,n) {

		var tag = n.tagName.toLowerCase();

		n.className = n.className.replace('Apple-style-span', '');
		n.className = n.className.replace('Apple-converted-space', '');
		n.className = n.className.replace(/(^| )Mso[^\s]+/, '');
		n.className = n.className.replace(/(^| )Apple-[^\s]+/, '');

		if (n.getAttribute('class') 	=== '') { n.removeAttribute('class'); }
		if (n.style.cssText 		=== '') { n.removeAttribute('style'); }

		if (n.hasAttribute('src')) {
			n.setAttribute( 'src', toAbsolute(n.getAttribute('src')) );
		}
		if (n.hasAttribute('href')) {
			n.setAttribute( 'href', toAbsolute(n.getAttribute('href')) );
		}
		if (n.hasAttribute('bgcolor')) {
			n.style.backgroundColor = n.getAttribute('bgcolor');
			n.removeAttribute('bgcolor');
		}
		if (n.style.position==='absolute') { n.style.position = ''; }
		if (tag==='font') {
			var color = n.getAttribute('color');
			var face = n.getAttribute('face');
			var size = n.getAttribute('size');
			var nEl = document.createElement('span');
			n.replace(nEl);
			n = nEl;
			color && n.setStyle('color',color);
			face && n.setStyle('font-family',face);
			size && n.setStyle('font-size',(size.toInt()+0.6)/2+'em');
			tag = 'span';
		}
		if (tag==='div' && n.childNodes.length===1 && n.firstChild.tagName.toLowerCase() === 'div') {
			n.firstChild.className += ' '+n.className;
			n.firstChild.style.cssText += n.style.cssText;
			n.removeNode();
			return;
		}
		n.removeAttribute('_moz_dirty');
		n.removeAttribute('lang');
		n.removeAttribute('id');

		if (domClean.remove[tag]) {
			n.dispose();
			return;
		}
		if (( !domClean.allowed[tag] )
			|| ( tag === 'span' && !n.hasAttribute('class') && !n.hasAttribute('style') )
//			|| ( !tag==='a' && n.innerHTML.trim() === '' && !domClean.single[tag] )
		) {
			n.removeNode();
		}
	});
};
domClean.allowed = {
	all:{style:1,title:1,'class':1},
	p:{},
	div:{},
	span:{},
	a:{href:1,target:1},
	br:{},
	img:{src:1,height:1,width:1,alt:1,align:1,valign:1},
	b:{},
	strong:{},
	i:{},
	h1:{},
	h2:{},
	h3:{},
	h4:{},
	h5:{},
	h6:{},
	table:{},
	tr:{},
	td:{align:1,valign:1}, // zu css wandeln
	tbody:{},
	li:{},
	ul:{},
	ol:{},
	pre:{},
	tt:{},
	hr:{},
	iframe:1
};
domClean.remove = {
	style:1,script:1,link:1,meta:1
};
domClean.single = {
	img:1,br:1,td:1,hr:1
};
*/
