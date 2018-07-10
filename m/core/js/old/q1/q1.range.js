/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function() { 'use strict';

var sel = getSelection();

q1.range = function(oRange) {
	if (!(this instanceof q1.range)) return new q1.range(oRange);
	this.oR = oRange || document.createRange();
};
q1.range.prototype = {
	container() {
		return this.oR.commonAncestorContainer;
	},
	containerElement() {
		var node = this.oR.commonAncestorContainer;
		return node.nodeType === 1 ? node : node.parentNode;
	},
	startContainer() {
		return this.oR.startContainer;
	},
	startContainerElement() {
		var node = this.oR.startContainer;
		return node.nodeType === 1 ?  node : node.parentNode;
	},
	startOffset() {
		return this.oR.startOffset;
	},
	endContainer() {
		return this.oR.endContainer;
	},
	endContainerElement() {
		var node = this.oR.endContainer;
		return node.nodeType === 1 ?  node : node.parentNode;
	},
	endOffset() {
		return this.oR.endOffset;
	},
	clone() {
		return new q1.range( this.oR.cloneRange() );
	},
	extractContents() {
		return this.oR.extractContents();
	},
	rect() {
		var pos = this.oR.getBoundingClientRect();
		pos = pos.top ? pos : this.oR.getClientRects()[0];
		if (!pos) {
			var clone = this.oR.cloneRange();
			if (this.oR.startOffset > 0) {
				clone.setStart( this.oR.startContainer, this.oR.startOffset-1 );
				pos = clone.getBoundingClientRect();
			} else if (this.oR.endOffset <= this.endContainer.length) {
				clone.setEnd( this.oR.endContainer, this.oR.endOffset+1 );
				pos = clone.getBoundingClientRect();
			} else {
				pos = this.containerElement().getBoundingClientRect();
			}
		}
        return q1.rect({
        	x: pos.left+pageXOffset,
        	y: pos.top+pageYOffset,
        	w: pos.width,
        	h: pos.height
        });
	},
	select() {
		sel.removeAllRanges();
		sel.addRange( this.oR );
	},
	toString() {
		return this.oR.toString();
	},
	toCleanString() {
		var fragment = this.oR.cloneContents();
		var div = document.createElement('div');
		[].slice.call(fragment.querySelectorAll('script, style')).forEach( function(el) {
			el.remove();
		});
		div.appendChild(fragment);
		//return div.textContent.replace(/[\f\n\r\t\v\u2028\u2029]+/g, ' '); // like \s but no \u00A0 (does not work)
		return div.textContent.replace(/\s+/g, ' '); // no \u00A0
	},
	sameAs(range) {
		if (!range) {return false;}
		return this.oR.startContainer === range.oR.startContainer
		&& this.oR.startOffset === range.oR.startOffset
		&& this.oR.endContainer === range.oR.endContainer
		&& this.oR.endOffset === range.oR.endOffset;
	},
};
var setters = {
	setStart(node,offset) {
		this.oR.setStart(node,offset);
		return this;
	},
	setStartBefore(node) {
		this.oR.setStartBefore(node);
		return this;
	},
	setEnd(node,offset) {
		this.oR.setEnd(node,offset);
		return this;
	},
	setEndAfter(node) {
		this.oR.setEndAfter(node);
		return this;
	},
	toNode(node) {
		this.oR.selectNode(node);
		return this;
	},
	toContents(node) {
		this.oR.selectNodeContents(node);
		return this;
	},
	insert(node) {
		if (node instanceof Array) {
			for (var i=0, n; n = node[i++];) {
				this.oR.insertNode(n);
			}
			return this;
		}
		if (typeof node === 'string') { node = document.createTextNode(node); }
		this.oR.insertNode(node);
		return this;
	},
	collapse(toStart) {
		this.oR.collapse(toStart);
		return this;
	}
};
q1.ext(setters, q1.range.prototype);

q1.selection = function() {
	if (!sel.rangeCount) return false;
	if (!(this instanceof q1.selection)) return new q1.selection();
	this.oR = sel.getRangeAt(0);
};

q1.selection.prototype = new q1.range();

//	q1.selection.prototype.constructor = q1.selection;
for (var i in setters) {
	q1.selection.prototype[i] = function(fn) {
		return function() {
			var ret = fn.apply(this, arguments);
			sel.removeAllRanges();
			sel.addRange( this.oR );
			return ret;
		};
	}(setters[i]);
}
q1.selection.prototype.range = function() {
	return q1.range(this.oR);
};
q1.selection.prototype.toString = function() {
	return sel.toString();
};


var nextNode = function(node, direction) {
	var child   = direction==='start' ? 'lastChild'       : 'firstChild',
		sibling = direction==='start' ? 'previousSibling' : 'nextSibling';
	if (node[child]) return node[child];
	while (node) {
		if (node[sibling]) return node[sibling];
		node = node.parentNode;
	}
	return false;
};
var nextTextNode = function(node, direction) {
	do {
		node = nextNode(node, direction );
		if (node.nodeType === 3) return node;
	} while (node);
	return false;
};
var nextPosition = function(node, offset, direction) {
	if (direction==='start') {
		offset--;
		if (node.data === undefined || offset < 0) {
			node = nextTextNode(node, direction);
			offset = node.data.length-1;
		}
	} else {
		offset++;
		if (node.data === undefined || offset >= node.data.length) {
			node = nextTextNode(node, direction);
			offset = 0;
		}
	}
	return [node, offset];
};

/* save / restore */
q1.range.prototype.save = function() {
	return {
		sC: this.oR.startContainer,
		sO: this.oR.startOffset,
		eC: this.oR.endContainer,
		eO: this.oR.endOffset,
		oR: this.oR,
		type:'q1SavedRange1'
	};
};
q1.range.prototype.saveHard = function() {
	var sRange = this.oR.cloneRange();
	sRange.collapse(true);
	var sMarker = document.createElement('i');
	sMarker.id = 'q1Tmp_'+Math.random();
	sRange.insertNode(sMarker);

	var eRange = this.oR.cloneRange();
	eRange.collapse(false);
	var eMarker = document.createElement('i');
	eMarker.id = 'q1Tmp_'+Math.random();
	eRange.insertNode(eMarker);
	return {
		startMarker: sMarker.id,
		endMarker: eMarker.id,
		type: 'q1SavedRangeHard'
	};
};
q1.range.prototype.restore = function(saved) {
	if (saved.type==='q1SavedRange1') {
		this.oR.setStart(saved.sC,saved.sO);
		this.setEnd(saved.eC,saved.eO);
	} else if (saved.type==='q1SavedRangeHard') {
		var sMarker = document.getElementById(saved.startMarker);
		var eMarker = document.getElementById(saved.endMarker);
		this.oR.setStartBefore( sMarker );
		this.oR.setEndAfter( eMarker );
		this.setEndAfter(eMarker);
		sMarker.remove();
		eMarker.remove();
	}
	return this;
};


q1.range.prototype.affectedRootNodes = function() {
	var el = this.oR.startContainer,
		end = this.oR.endContainer,
		els = [],
		prev = null;
	do {
		if (el === end) break;
		if (el.contains && el.contains(end)) continue;
		if (prev && prev.contains && prev.contains(el)) continue;
		prev = el;
		els.push(el);
	} while (el = nextNode(el))
	els.push(el);
	return els;
};
q1.range.prototype.splitTextNodes = function() {
	var node = this.oR.startContainer;
	if (node.data) {
		var startNode = node.splitText(this.oR.startOffset);
		this.setStart(startNode,0);
	}
	node = this.oR.endContainer;
	if (node.data) {
		node.splitText(this.oR.endOffset);
		this.setEnd(node,node.data.length);
	}
};
q1.range.prototype.containingRootNodes = function() {
	this.splitTextNodes();
	return this.affectedRootNodes();
};
q1.range.prototype.containingRootNodesForceElements = function() {
	var nodes = this.containingRootNodes(),
		newNodes = [];
	for (var i=0, el; el=nodes[i++];) { // todo: summarize following textNodes
		if (el.data) {
			if (el.data.trim() === '') continue;
			var nEl = document.createElement('span');
			el.parentNode.insertBefore( nEl, el );
			nEl.appendChild(el);
			el = nEl;
		}
		newNodes.push(el);
	}
	if (newNodes[0]) {
		this.setStartBefore(nodes[0]);
		this.setEndAfter(nodes[nodes.length-1]);
	}
	return newNodes;
};

q1.range.prototype.collapseToPoint = function(x, y) {
	// firefox has the rangeParent and rangeOffset properties of e.g. mouse-events

	/* todo: w3c test if its landed in browsers*/
	if (document.caretPositionFromPoint) {
  		var point = document.caretPositionFromPoint(x,y);
    	this.oR.setStart(point.offsetNode, point.offset);
    	return this.setEnd(point.offsetNode, point.offset);
	} else if (document.caretRangeFromPoint) { // chrome (w3c deprecated)
		this.oR = document.caretRangeFromPoint(x,y);
		return this.setEnd(this.oR.endContainer, this.oR.endOffset);
	}

	var r = this.oR;
	var el = document.q1NodeFromPoint(x,y);
	if (el.nodeType!==3) {
		return this.setStart(el, 0).setEnd(el, 0);
	}
	r.setStart(el,0);
	r.setEnd(el,0);
	var rect = this.rect();
	while (rect.y+rect.h < y || rect.x < x) {
		if (r.startOffset-1 > el.data.length) break;
		r.setStart(r.startContainer, r.startOffset+1); // opera error!
		rect = this.rect();
	}
	return this.collapse(true); //.setStart(el,)collapse(true);
};


q1.range.prototype.findElement = function() {
	var r = this.oR;
	if (r.startContainer.nodeType === 1 && r.endOffset-r.startOffset === 1) {
		return r.startContainer.childNodes[r.startOffset];
	}

	// walk thru white-spaces
	var sNode = this.oR.startContainer;
	var eNode = this.oR.endContainer;

	if (sNode.nodeType===3 && sNode.data.substr(this.oR.startOffset).trim()==='') {
		sNode = nextNode(sNode,'end');
	}
	do {
		if (!sNode) break;
		if (sNode === eNode
			|| sNode.tagName === 'IMG'
			|| (sNode.nodeType === 3 && sNode.data.trim() !== '')) {
			break;
		}
	} while (sNode = nextNode(sNode,'end'))


	if (eNode.nodeType === 3) {
		if (eNode.data.substr(0, this.oR.endOffset).trim() === '') {
			eNode = eNode.previousSibling;
		}
	} else {
		eNode = eNode.previousSibling;
	}
	do {
		if (!eNode) break;
		if (eNode === sNode
			|| eNode.tagName === 'IMG'
			|| (eNode.nodeType===3 && eNode.data.trim() !== '')) {
			break;
		}
	} while( eNode = nextNode(eNode,'start') )

	if (eNode === sNode) {
		return eNode.nodeType === 1 ? eNode : eNode.parentNode;
	}

	// else return containerElement
	return this.containerElement();
};

/*
q1.range.prototype.expand = function() {
	// http://msdn.microsoft.com/en-us/library/ms536421%28VS.85%29.aspx
	this.oR.expand('word') // chrome
};
*/
q1.range.prototype.walkChar = function(alter, point, direction) {
	var setEnd   = point === 'start' ? 'setStart' : 'setEnd',
		setStart = point === 'start' ? 'setEnd'   : 'setStart',
//			strBefore = this.toCleanString(), strAfter,
		pos, coordBefore = this.endingCoord(point), coordAfter;
	do {
		pos = nextPosition(this.oR[point+'Container'], this.oR[point+'Offset'], direction );
		if (pos[0] === false) return this;
		this.oR[setEnd](pos[0],pos[1]);
//			strAfter = this.toCleanString();
		coordAfter = this.endingCoord(point);
	} while( /*strAfter === strBefore ||*/ ( coordAfter.y === coordBefore.y && coordAfter.x === coordBefore.x ) ); // check if moved
	alter==='move' && this.oR[setStart](pos[0], pos[1]);
	this[setEnd](pos[0], pos[1]);
	return this;
};

q1.range.prototype.endingCoord = function(which) {
	var rects = this.oR.getClientRects(),
		rect = which === 'start' ? rects[0] : rects[rects.length-1];
	return which === 'start' ? {x:rect.left, y:rect.top} : {x:rect.right, y:rect.bottom};
};


}();
