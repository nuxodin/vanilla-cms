!function() {
	'use strict';
	var d = document, tmp;

    q1.dom = function(id) {
        return document.getElementById(id);
    };

	/* usefull */
	Node.prototype.q1RemoveNode = function(removeChildren) {
		if (removeChildren) return this.remove();
		var range = d.createRange();
		range.selectNodeContents(this);
		return this.parentNode.replaceChild(range.extractContents(), this);
	};
	Node.prototype.q1ReplaceNode = function(el) {
		this.parentNode && this.parentNode.insertBefore(el,this);
		el.appendChild(this);
		this.q1RemoveNode();
	};
	Node.prototype.q1Rect = function(rct) {
		if (rct) {
			this.style.top = rct.y+'px';
			this.style.left = rct.x+'px';
			this.style.width = rct.w+'px';
			this.style.height = rct.h+'px';
		} else {
	        var pos = this.getBoundingClientRect();
	        return q1.rect({
	        	x:pos.left+pageXOffset,
	        	y:pos.top+pageYOffset,
	        	w:pos.width,
	        	h:pos.height
	        });
		}
	};
	Node.prototype.q1Position = function(rct) {
		if (rct) {
			this.style.top = rct.y+'px';
			this.style.left = rct.x+'px';
		} else {
	        var pos = this.getBoundingClientRect();
	        return q1.rect({
	        	x: pos.left+pageXOffset,
	        	y: pos.top+pageYOffset,
	        	w: 0,
	        	h: 0
	        });
		}
	};
	d.q1NodeFromPoint = function(x, y) {
		/*document.caretRangeFromPoint for chrome?*/
		//caretPositionFromPoint
		if (y===undefined) {
			y = x.y;
			x = x.x;
		}
		var el = d.elementFromPoint(x, y);
		var nodes = el.childNodes;
		for (var i = 0, n; n = nodes[i++];) {
			if (n.nodeType === 3) {
				var r = d.createRange();
				r.selectNode(n);
				var rects = r.getClientRects();
				for (var j = 0, rect; rect = rects[j++];) {
					if (x > rect.left && x < rect.right && y > rect.top && y < rect.bottom) {
						return n;
					}
				}
			}
		}
		return el;
	};

}();
