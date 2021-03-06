/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
import {HTMLParser} from './htmlparser.mjs?qgUniq=9df0672';

window.domCodeIndent = function(str) {
	let res = '';
	let ind = '';
	let pre = false;
	str = str.replace(/\n|\t/g, ' ').replace(/<([\/a-zA-Z0-9]+)/g, function(a) { return a.toLowerCase(); });
	var makeStartTag = function(tag, attrs, unary) {
		let str = '<' + tag;
		for (var i = 0, att; att = attrs[i++];) {
			str += ' ' + att.name + '="' + att.escaped + '"';
		}
		str += (unary?'/':'')+'>';
		return str;
	};
	HTMLParser(str,{
		start(tag, attrs, unary) {
			pre = tag==='pre' ? true : pre;
			!pre && (res += ind);
			res += makeStartTag(tag,attrs,unary);
			!pre && (res+='\n');
			!unary && (ind += '\t');
		},
		end(tag) {
			pre = tag==='pre' ? false : pre;
			!pre && (ind=ind.substr(1));
			res += ind+'</' + tag.toLowerCase() + '>';
			!pre && (res+='\n');
		},
		chars(text) {
			!pre && (res += ind);
			if (!text.match(/^\s/)) text = '\uFEFF'+text; // mark if no whitespace
			if (!text.match(/\s$/)) text = text+'\uFEFF';
			res += text;
			!pre && (res+='\n');
		},
		comment(text) {
			res += "<!--" + text + "-->";
		}
	});
	return res;
};

window.getPossibleClasses = function (el) { /* eventuell better performance? */
	var ret = {};
	function test(sel) {
		sel = sel.trim();
		if (!~sel.indexOf('.')) return;
		if (!sel.match(/\.[A-Z]/)) return;
		var reg = el ? new RegExp('(^'+el.tagName+'|^)\\.[^ ]+$', 'i') : new RegExp('^\\.[^ ]+$');
		if (sel.match(reg)) {
			var x = sel.replace(/^(.*\.)([^: ]*)(.*)$/, function(m, a1, a2) { return a2; });
			ret[x] = sel;
		}
	}
	for (let sheet of document.styleSheets) {
		if (sheet.href && sheet.href.indexOf(location.host) === -1) continue; // only inline and same domain
		if (sheet.href === null) {
			try {
				if (sheet.ownerNode.innerHTML === '') continue; // adblock chrome
			} catch(e) { }
		}
        try { // (not same domain) security error in ff
			if (sheet.cssRules)
				for (let rule of sheet.cssRules) {
					if (!rule.selectorText) continue;
	    			rule.selectorText.split(',').forEach(test);
				}
        } catch(e) { console.log(e); }
	}
	return ret;
};

/*

window.rangeExpandToStart = function(range) {
	var node = range.startContainer;
	while (node.previousSibling && node.previousSibling.data) {
		node = node.previousSibling;
	}
	//range.setStart(node,0);
	range.setEndBefore(node);
};
window.rangeExpandToEnd = function(range) {
	var node = range.endContainer;
	while (node.nextSibling && node.nextSibling.data) {
		node = node.nextSibling;
	}
	//range.setEnd(node,node.data.length);
	range.setEndAfter(node);
};
window.rangeExpandToElements = function(range) {
	rangeExpandToStart(range);
	rangeExpandToEnd(range);
};
window.rangeIsElement = function(range) {
	return range.toString() === range.commonAncestorContainer.textContent;
};
window.rangeGetNodes = function(r) {
	var start = r.startContainer;
	var end = r.endContainer;
	if (start === end) {
		var node = r.extractContents().firstChild;
		r.insertNode(node);
		return [node];
	}
	var els = [];

	walk(start);

	var sRange = document.createRange();
	sRange.setStart(r.startContainer,r.startOffset);
	sRange.setEndAfter(r.startContainer);
	var node = sRange.extractContents().firstChild;
	if (node.data) {
		sRange.insertNode(node);
		els.push(node);
	}
	sRange = document.createRange();
	sRange.setEnd(r.endContainer,r.endOffset);
	sRange.setStartBefore(r.endContainer);
	var node = sRange.extractContents().firstChild;
	if (node.data) {
		sRange.insertNode(node);
		els.push(node);
	}
	return els;

	function walk(el) {
		if (el !== end && !$.contains(el,end)) {
			el !== start && els.push(el);
			if (el.nextSibling) {
				walk(el.nextSibling);
			} else { // walk next parant with a nextSibling
				var parent = el.parentNode;
				while (parent && !parent.nextSibling) {
					parent = parent.parentNode;
				}
				walk(parent.nextSibling);
			}
		} else {
			el.firstChild && walk(el.firstChild);
		}
	}
};
window.rangeGetElements = function(r) {
	els = rangeGetNodes(r);
	for (var i = els.length, el; el = els[--i];) {
		if (el.data && el.data.toString().trim()) {
			var span = document.createElement('span');
			el.parentNode.insertBefore(span, el);
			span.appendChild(el);
			els[i] = span;
		}
	}
	return els;
};
*/
