/*polyfill*/

c1.fix.contextMenu = {};

document.addEventListener('DOMContentLoaded',()=>{
	'use strict';
	var support = document.body.contextMenu !== undefined;
	if (support) return;

	c1.c1Use('focusIn',()=>{});

	document.documentElement.addEventListener('contextmenu', e=>{
		if (e.shiftKey) return;
		var base = e.target.closest('[contextmenu]');
		if (base) {
			var id = base.getAttribute('contextmenu');
			var mEl = document.getElementById(id);
			if (mEl && mEl.children.length) {
				e.preventDefault();
				parse(mEl, poly);
				poly.style.display = 'block';
				var top  = e.clientY;
				var left = e.clientX;
				top  = Math.min(innerHeight - poly.offsetHeight, top);
				left = Math.min(innerWidth  - poly.offsetWidth, left);
				poly.style.top  = top+'px';
				poly.style.left = left+'px';
				poly.c1ZTop();
				poly.focus();
			}
		}
	});
	var poly = c1.dom.fragment('<ul id=contextMenuePolyfill tabindex=0 class=q1Rst>').firstChild;
	document.body.append(poly);
	poly.addEventListener('focusout',e=>{
		if (poly.contains(e.relatedTarget)) return;
		poly.style.display = 'none';
	});
	function parse(mEl, poly) {
		poly.addEventListener('keydown',e=>{ // todo
			switch (e.which) {
				case 38: // top
				case 40: // bottom
				case 39: // right
				case 13:
				break;
				default:
				return;
			}
			e.preventDefault();
		})
		poly.innerHTML = '';
		for (let mChild of mEl.children) {
			let polyChild = c1.dom.fragment('<li>'+mChild.getAttribute('label')).firstChild;
			let icon = mChild.getAttribute('icon');
			if (icon) polyChild.style.backgroundImage = 'url('+icon+')';
			let disabled = mChild.hasAttribute('disabled') || mChild.disabled; // todo: check value of attribute
			if (disabled) {
				polyChild.classList.add('-disabled');
				polyChild.disabled = true;
			}
			polyChild.addEventListener('mouseenter',e=>{
				clearTimeout(openTimeout);
				openTimeout = setTimeout(()=>{
					open(polyChild)
				}, 250)
			})
			polyChild.addEventListener('click', e=>{
				if (e.target !== polyChild) return;
				if (open(polyChild)) return;
				if (!disabled) {
					mChild.dispatchEvent(new Event('click'));
					poly.style.display = 'none';
				}
				e.stopPropagation();
			});
			polyChild.addEventListener('mousedown',  e=>e.stopPropagation())
			polyChild.addEventListener('touchstart', e=>e.stopPropagation())
			poly.append(polyChild);
			mChild.c1RealElement = polyChild;
			if (mChild.children.length) {
				polyChild.classList.add('-sub');
				let ul = c1.dom.fragment('<ul tabindex=0>').firstChild;
				ul.c1Placer = new c1.Placer(ul, {x:'after',y:'prepend',margin:{top:1,right:-3,bottom:1,left:-3}});
				polyChild.append(ul)
				parse(mChild, ul);
			}
		}
		if (poly.id === 'contextMenuePolyfill') {
			var fragment = c1.dom.fragment('<li style="font-size:12px; padding:5px" class=-disabled>shift + rightclick to show the<br> native menu</li>');
			poly.append(fragment);
		}
	}
	let openTimeout;
	function open(polyChild){
		clearTimeout(openTimeout);
		polyChild.parentNode.c1Focus();
		let ul = polyChild.c1Find('>ul');
		if (ul) {
			ul.c1Focus();
			ul.c1ZTop();
			ul.c1Placer.follow(polyChild);
			return true;
		}
	}
	var arrow = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="32" fill="none" viewBox="0 0 16 32"><path stroke="#000" stroke-width="2" d="M2 2l12 12L2 26" stroke-linecap="round"/></svg>';

	var css =
	'menu[type=context] {'+
	'	display:none; '+
	'}'+
	'#contextMenuePolyfill, #contextMenuePolyfill ul { '+
	'	position:fixed; '+
	'	display:none; '+
	'	background:#fff; '+
	'	box-shadow:0 0 8px rgba(0,0,0,.3); '+
	'	list-style:none; '+
	'	font-family:Arial; '+
	'	font-size:15px; '+
	'	margin:0; '+
	'	padding:0; '+
	'	min-width:100px; '+
	'	color:#000; '+
	'	cursor:default; '+
	'	border: 1px solid #aaa; '+
	'} '+
	'#contextMenuePolyfill ul.c1-focusIn { '+
	'	display:block; '+
	'} '+
	'#contextMenuePolyfill:focus { outline:none } '+
	'#contextMenuePolyfill li { '+
	'	display:flex; '+
	'	padding:6px 10px 6px 30px; '+
	'	background-position:6px 50%; '+
	'	background-repeat:no-repeat; '+
	'	background-size: 16px 16px; '+
	'} '+
	'#contextMenuePolyfill li:hover, #contextMenuePolyfill li.c1-focusIn { '+
	'	background-color:#f3f3f3; '+
	'} '+
	'#contextMenuePolyfill li.-disabled { '+
	'	opacity:0.36 '+
	'}'+
	'#contextMenuePolyfill li.-sub:after { '+
	'	content:"";'+
	'	flex:1 0 10px;'+
	'	background:url("data:image/svg+xml;utf8,'+encodeURIComponent(arrow)+'") no-repeat 100% 50%;'+
	'	background-size:contain;'+
	'	height:.9em; '+
	'	margin:auto; '+
	'}';
	document.head.prepend(c1.dom.fragment('<style>'+css+'</style>'));
});
