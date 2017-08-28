/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
{
	let active, dropCont, dropBefore, oldCss, ghost = document.createElement('div');
	ghost.style.cssText = 'background:#ff5; outline:#ff5 3px solid; min-height:18px; box-shadow:0 0 30px 3px rgba(0,0,0,.8); min-width:20px; z-index:999; position:relative; overflow:hidden; opacity:.9';

	cms.contDrag = function() {
		let self = this;
		let move = function(e) {
			active.style.left = e.clientX - 40 + 'px';
			active.style.top  = e.clientY + 20 + 'px';
			let newDropCont   = getNearestElement2(e,self.targets,active);
			let newDropBefore = getBeforeElement(e,newDropCont);
			if (dropCont !== newDropCont || dropBefore !== newDropBefore) {
				dropCont   = newDropCont;
				dropBefore = newDropBefore;
				change(dropCont,dropBefore);
			}
		}
		function up() {
			moveToTargetEffect();
			if (!dropCont) return; // neu
			dropCont.removeChild(ghost);
			dropCont.insertBefore(active,dropBefore);
			active.style.cssText = oldCss;
			active.style.opacity = 0; // moveToTargetEffect!!
			document.removeEventListener('mousemove',move);
			document.removeEventListener('mouseup',up);
			self.trigger('stop',active);
			dropCont   = 0;
			dropBefore = 0;
		}
		let change = function(){
			dropCont.insertBefore(ghost,dropBefore);
			self.trigger('change',{target:dropCont,before:dropBefore});
		}//.c1Debounce(70);

		this.start = (el, e)=>{
			document.addEventListener('mousemove',move);
			document.addEventListener('mouseup',up);
			self.trigger('start',{target:el, originalEvent:e});
			active = el;
			oldCss = active.style.cssText;
			active.style.position  = 'fixed';
			active.c1ZTop();
			document.body.appendChild(active);
			e && move(e);
		};

	};
	cms.contDrag.prototype = c1.Eventer;

	/*
	function getNearestElement(e, els, notInside) {
		let winner, winner2, min=null;
		for (let i = els.length, el; el = els[--i];) {
			if (notInside && notInside.contains(el)) continue;
			var r = el.getBoundingClientRect();
			var elMin = null;
			var xmin = Math.min(
				Math.abs(r.left - e.clientX),
				Math.abs(r.right - e.clientX)
			);
			var ymin = Math.min(
				Math.abs(r.top - e.clientY),
				Math.abs(r.bottom - e.clientY)
			);
			if (e.clientY < r.top || e.clientY > r.bottom || e.clientX < r.left || e.clientX > r.right) { // is outside
				if (e.clientY > r.top && e.clientY < r.bottom) { // in Y
					elMin = xmin;
				} else if (e.clientX > r.left && e.clientX < r.right) { // in X
					elMin = ymin;
				} else {
					elMin = Math.sqrt(xmin*xmin+ymin*ymin);
				}
			} else { // is inside
				elMin = Math.min(xmin,ymin) / 50; // inside is 50x better!
			}
			if (min === null || elMin < min) {
				min = elMin;
				winner2 = winner;
				winner = el;
			}
		}
		return winner;
	}
	*/



	function elementDistance(el,x,y) {
		let distance = null;
		const rect = el.getBoundingClientRect();
		const distanceX = Math.min(
			Math.abs(rect.left - x),
			Math.abs(rect.right - x)
		);
		const distanceY = Math.min(
			Math.abs(rect.top - y),
			Math.abs(rect.bottom - y)
		);
		const isInside = !(y < rect.top || y > rect.bottom || x < rect.left || x > rect.right);
		if (isInside) {
			distance = Math.min(distanceX,distanceY); // 0?
		} else {
			if (y > rect.top && y < rect.bottom) { // in Y
				distance = distanceX;
			} else if (x > rect.left && x < rect.right) { // in X
				distance = distanceY;
			} else {
				distance = Math.sqrt(distanceX*distanceX + distanceY*distanceY);
			}
		}
		return {
			distance,
			distanceX,
			distanceY,
			isInside,
		};
	}
	function elementDistances(e, els) {
		const items = [];
		for (let element of els) {
			const distances = elementDistance(element, e.clientX, e.clientY);
			let distance = distances.distance;
			if (distances.isInside) distance = distance / 50; // inside is 50x better (nearer)!
			items.push({
				element,
				distances,
				distance,
			});
		}
		return items.sort(function(a, b) {
		    return a.distance - b.distance;
		});
	}
	function getNearestElement2(e, els, notInside) {
		els = Array.prototype.filter.call(els, el => !notInside.contains(el) )
		const items = elementDistances(e, els);
		return items[0] && items[0].element;
	}
	/*
	function getBeforeElement(e, inside) {
		const children = Array.from(inside.children).filter(item=>{ return item !== active && item !== ghost });
		const nearest = getNearestElement2(e, children);
		if (!nearest) return null;

		const rect = nearest.getBoundingClientRect();
		const x = e.clientX
		const y = e.clientY;

		const center = {
			x: rect.left + (rect.width/2),
			y: rect.top + (rect.height/2),
		}
		const isBefore = y < center.y || x < center.x;
		return isBefore ? nearest : nearest.nextElementSibling;
	}
	*/

	function getBeforeElement(e, el) {
		let min=null, winner;
		if (el.children.length) {
			for (var i=0,child; child=el.children[i++];) {
				if (child === active || child === ghost) continue;
				var pos = child.getBoundingClientRect();
				var x = pos.left+(pos.width/2);
				var y = pos.top+(pos.height/2);
				var diffX = (e.clientX-x)*1;
				var diffY = (e.clientY-y)*6;
				var diff = Math.sqrt(diffX*diffX + diffY*diffY);
				if (min === null || diff < min) {
					min = diff;
					winner = child;
					if (/*diffX>110||*/ diffY > 0) {
						winner = winner.nextElementSibling;
					}
				}
			}
		}
		while (winner && (winner===active || winner===ghost)) {
			winner = winner.nextElementSibling;
		}
		return winner;
	}

	function moveToTargetEffect() {
		let clone = active.cloneNode(true);
		document.body.appendChild(clone);
		clone.style.cssText +=
		'width:'+clone.offsetWidth+'px; '+
		'height:'+clone.offsetHeight+'px; '+
		'max-width:none; '+
		'min-width:0; '+
		'max-height:none; '+
		'min-height:0; '+
		'boxSizing:content-box; ';
		let opacity = active.style.opacity;
		setTimeout(()=>{
			let duration = 190;
			let pos = active.getBoundingClientRect();
			active.style.opacity = 0;
			clone.style.cssText +=
			'transition:all '+duration+'ms; '+
			'transition-property:width height top left opacity; '+
			'top:'+pos.top+'px; '+
			'left:'+pos.left+'px; '+
			'width:'+pos.width+'px; '+
			'height:'+pos.height+'px; ';
			setTimeout(()=>{
				clone.style.cssText +=
				'transition-duration:100ms; '+
				'opacity:0; ';
				setTimeout(()=>clone.remove(),100);
				active.style.opacity = opacity;
			},duration);
		});
	}
}
