domCodeIndent = function(str) {
	var res = '';
	var ind = '';
	var pre = false;
	str = str.replace(/\n|\t/g, ' ').replace(/<([\/a-zA-Z0-9]+)/g, function(a) { return a.toLowerCase(); });

	var makeStartTag = function(tag, attrs, unary) {
		var str = '<' + tag;
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

function getPossibleClasses(el) { /* eventuell better performance? */
	var ret = {};
	var test = function(sel) {
		sel = sel.trim();
		if (!~sel.indexOf('.')) return;
		if (!sel.match(/\.[A-Z]/)) return;
		var a = el ? new RegExp('(^'+el.tagName+'|^)\\.[^ ]+$', 'i') : new RegExp('^\\.[^ ]+$');
		if (sel.match(a)) {
			var x = sel.replace(/^(.*\.)([^: ]*)(.*)$/, function(m, a1, a2) { return a2; });
			ret[x] = 1;
		}
	};
	for (let sheet of document.styleSheets) {
		if (sheet.href && sheet.href.indexOf(location.host) === -1) { // only inline and same domain
			continue;
		}
		if (sheet.href === null) {
			try {
				if (sheet.ownerNode.innerHTML === '') { // adblock chrome
					continue;
				}
			} catch(e) { }
		}
        try { // (not same domain) security error in ff
    		var rules = sheet.rules || sheet.cssRules;
			if (rules)
				for (let rule of rules) {
					if (!rule.selectorText) continue;
	    			rule.selectorText.split(',').forEach(test);
				}
        } catch(e) { console.log(e); }
	}
	return ret;
}

rangeExpandToStart = function(range) {
	var node = range.startContainer;
	while (node.previousSibling && node.previousSibling.data) {
		node = node.previousSibling;
	}
	//range.setStart(node,0);
	range.setEndBefore(node);
};
rangeExpandToEnd = function(range) {
	var node = range.endContainer;
	while (node.nextSibling && node.nextSibling.data) {
		node = node.nextSibling;
	}
	//range.setEnd(node,node.data.length);
	range.setEndAfter(node);
};
rangeExpandToElements = function(range) {
	rangeExpandToStart(range);
	rangeExpandToEnd(range);
};
rangeIsElement = function(range) {
	return range.toString() === range.commonAncestorContainer.textContent;
};
rangeGetNodes = function(r) {
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
		els.push( node );
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

rangeGetElements = function(r) {
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
