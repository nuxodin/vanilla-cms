/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
window.Rte.ui = {
	init() {
		var my = this;
		my.div = document.createElement('div');
		my.div.id = 'qgRteToolbar';
		my.div.addEventListener('mousedown',  e=>e.stopPropagation());
		my.div.addEventListener('touchstart', e=>e.stopPropagation());

		Rte.addUiElement(my.div);

		my.mainContainer = document.createElement('div');
		my.mainContainer.className = '-main';
		my.div.appendChild(my.mainContainer);

		my.moreContainer = document.createElement('div');
		my.moreContainer.className = '-more q1Rst';
		my.div.appendChild(my.moreContainer);

		Rte.on('activate', function() {
			var config = Rte.ui.config['rteDef']; // todo
			my.activeItems = {};
			var appendTo = my.mainContainer;
			var addItem = function(n) {
				if (!my.items[n]) return;
				my.activeItems[n] = my.items[n];
				appendTo.appendChild(my.items[n].el);
			};
			config.main.forEach(addItem);
			appendTo = my.moreContainer;
			config.more.forEach(addItem);
			if (my.activeItems) {
				document.body.appendChild(my.div);
				my.div.style.display = 'block';
				my.div.style.opacity = '0';
				my.div.style.pointerEvent = 'none';
				my.div.c1ZTop();
				setTimeout(function() {
					my.div.style.opacity = '1';
					my.div.style.pointerEvent = '';
				},120);
				document.addEventListener('keydown', shortcutListener, false);
			}
		});
		Rte.on('deactivate', function() {
			document.removeEventListener('keydown', shortcutListener, false);
			setTimeout(()=>{ // need timeout because inputs inside have to blur first (ff, no chrome)
				!Rte.active && my.div.parentNode && document.body.removeChild(my.div);
			},1);
		});
		Rte.on('elementchange', function() {
			//if (!Rte.element) return; needed?
			for (let [name,item] of Object.entries(my.activeItems)){
				if (!item.enable || item.enable(Rte.element)) {
					item.enabled = true;
					item.el.removeAttribute('hidden');
					if (item.check) {
						const act = item.check(Rte.element) ? 'add' : 'remove';
						item.el.classList[act]('active');
					}
				} else {
					item.enabled = false;
					item.el.setAttribute('hidden',true);
				}
			}
		});
		var shortcutListener = function(e) {
			if (e.ctrlKey && !e.metaKey && !e.shiftkey && !e.altkey) {
				var char = String.fromCharCode(e.which).toLowerCase();
				for (let [name,item] of Object.entries(my.activeItems)){
					if (item.enabled && item.shortcut === char) {
						let event = new MouseEvent('mousedown',{'bubbles': true,'cancelable': true});
						item.el.dispatchEvent(event);
		                e.preventDefault();
					}
				}
			}
		};
		/* ui hover */
		var moreTimeout = null;
		my.div.addEventListener('mouseenter',()=>{
			clearTimeout(moreTimeout);
			moreTimeout = setTimeout(()=>{
				Rte.ui.div.querySelector('.-more').style.display = 'flex';
			},300);
			Rte.ui.mouseover = 1;
		});
		my.div.addEventListener('mouseleave',()=>{
			clearTimeout(moreTimeout);
			moreTimeout = setTimeout(()=>{
				Rte.ui.div.querySelector('.-more').style.display = 'none';
			},300);
			Rte.ui.mouseover = 0;
		});
	},
	setItem(name, opt) {
		if (!opt.el) {
			opt.el = document.createElement('span');
			opt.el.className = '-item -'+name;
		}
		if (opt.cmd) {
			if (!opt.click && opt.click != false) opt.click = ()=>qgExecCommand(opt.cmd,false,false);
			if (!opt.check && opt.check != false) opt.check = ()=>qgQueryCommandState(opt.cmd);
		}
		var enable = opt.enable;
		if (enable && enable.toLowerCase) {
			opt.enable = el => el && el.matches(enable);
			// opt.enable = el => { // todo?
			// 	if (!el) return false;
			// 	let target = el.closest(enable);
			// 	return Rte.active !== target && Rte.active.contains(target);
			// }
		}
		opt.click && opt.el.addEventListener('mousedown',e=>{
			Rte.manipulate( ()=>opt.click(e) ); // todo: manipulate schon hier??
		}, false);
		opt.shortcut && opt.el.setAttribute('title','ctrl+'+opt.shortcut);
		this.items[name] = opt;
		return opt.el;
	},
	setSelect(name, opt) {
		let timeout = null;
		let el = c1.dom.fragment('<div class="-item -select"><div class=-state></div><div class=-options></div></div>').firstChild;
		el.addEventListener('mousedown', e=> { opts.style.display = 'block'; e.preventDefault(); });
		el.addEventListener('mouseover', e=> clearTimeout(timeout) );
		el.addEventListener('mouseout',  e=> timeout = setTimeout(()=> opts.style.display = 'none' ,300) );
		let opts = el.c1Find('>.-options');
		opt.el = el;
		this.setItem(name,opt);
		return opts;
	},
	items:{}
};

Rte.ui.init();

Rte.on('selectionchange', ()=>{
	if (!Rte.active) return;
	if (Rte.ui.mouseover) return;
	var distance = getSelection().isCollapsed ? 100 : 20;
	c1.c1Use('Placer',function(){
		let Placer = new c1.Placer(Rte.ui.div, {
			xuse:'transform',
			x:'center',
			y:'after',
			margin:distance,
		});
		var pos = qgSelection.rect();
		Placer.toClientRect(pos);
	});
});

// Rte.on('selectionchange', function() {
// 	if (!Rte.active) return;
// 	if (Rte.ui.mouseover) return;
// 	var pos = qgSelection.rect();
// 	if (!pos) {
// 		if (Rte.element) {
// 			pos = Rte.element.getBoundingClientRect();
// 		} else return;
// 	}
// 	var distance = getSelection().isCollapsed ? 60 : 20;
// 	var top  = pos.top  + pos.height + 10 + distance;
// 	var left = pos.left + distance;
// 	if (top > $(window).height() - 100) {
// 		top  = pos.top - 110 - distance;
// 	}
// 	left = Math.min(left, $(window).width()-250);
// 	top  = Math.max(top, 0);
// 	left = Math.max(left, 0);
// 	var Bpos = document.body.getBoundingClientRect();
// 	Rte.ui.div.style.top  = top  - Bpos.top  +'px';
// 	Rte.ui.div.style.left = left - Bpos.left +'px';
// });
