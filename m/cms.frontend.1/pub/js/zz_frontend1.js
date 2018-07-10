/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
alert('deprecated (use frontend.mjs)');
'use strict';
{

	cms.frontend1 = {
		c1UseSrc: sysURL+'cms.frontend.1/pub/js/frontend1',
		c1Use
	};

	/* cms.element? */
	cms.contPos = function(el) {
		if (el.cmsContPos) return el.cmsContPos;
		if (!(this instanceof cms.contPos)) return new cms.contPos(el);
		el.cmsContPos = this;

		this.el = el;
		this.pid = el.className.replace(/.*-pid([0-9]+).*/, '$1'); // used
		el.addEventListener('mouseleave',this.unmarkDelay.bind(this));
	}
	Object.assign(cms.contPos, c1.Eventer);

	cms.contPos.prototype = {
		isDraggable() {
			if (this.el.classList.contains('-draggable')) return true;
			let p = this.el.parentNode;
			return p.classList.contains('-e') && p.classList.contains('qgCMS-dropTarget');
		},
		mark(e) {
			let _ = cms.contPos;
			e && e.stopPropagation(); // verschachtelt
			_.active && _.active.unmark();
			if (_.moving || _.active === this /*|| this.el.classList.contains('qgCMS-dropTarget')*/) { _.active = false; return; }
			_.active = this;
			this.el.classList.add('qgCmsMarked');
			cms.contPos.trigger('mark', this);
		},
		unmark() {
			clearTimeout(cms.contPos.outTimer);
			if (!cms.contPos.active) return;
			cms.contPos.active.el.classList.remove('qgCmsMarked');
			cms.contPos.active = false;
			cms.contPos.trigger('unmark', this);
		},
		unmarkDelay() {
			clearTimeout(cms.contPos.outTimer);
			cms.contPos.outTimer = setTimeout(this.unmark.bind(this),100);
		},
	};
	cms.contPos.moving = false;
	cms.contPos.active = false;

	function contMarkListener(e) {
		if (e.target.nodeType !== 1) return; // firefox on dragenter
		let target = e.target.closest('.qgCmsCont.-e');
		target && cms.contPos(target).mark(e);
	}
	document.addEventListener('mouseover',contMarkListener);
	document.addEventListener('dragenter',contMarkListener);
	document.addEventListener('mousedown',contMarkListener);

	cms.cont.loadCallback = res=>{
		setTimeout(()=>{ // html possibility has content-script that needs header-script to be executed first
			let el = c1.dom.fragment(res.html).firstChild;
			cms.contPos(el);
			cms.contPos.dd.start(el); // todo. what todo?
			el.style.top = '130px';
			el.style.left = '130px';
		});
	};
	cms.cont.add = mod => $fn('page::addContent')(Page, mod).run(cms.cont.loadCallback);
	cms.cont.prototype.addPosition = function(){
		$fn('Page::getWithHead')(this.id).run(cms.cont.loadCallback);
	}

	/* element menu */
	document.addEventListener('DOMContentLoaded',()=>{

		/* neu  needed? */
		/* init contents *
	    let els = document.getElementsByClassName('qgCmsCont'),
	        el, i=0;
		for (;el=els[i++];) cms.contPos(el);
		/* /neu */

		let p = cms.contPos;
		let menu = c1.dom.fragment(
			'<div id=qgCmsContPosMenu class=q1Rst>'+
			'	<div class=-opts title="Einstellungen"></div>'+
			'	<div class=-drag title="Verschieben"></div>'+
			'	<div class=-mod  title="Module"></div>'+
			'</div>', 'text/html').firstChild;
		document.body.append(menu);
		menu.drag = menu.querySelector('.-drag');
		menu.mod  = menu.querySelector('.-mod');
		menu.opts = menu.querySelector('.-opts')
		menu.opts.addEventListener('click', () => {
			cms.cont.active = p.active.pid;
			cms.panel.set('sidebar', 'settings');
		})
		menu.addEventListener('mouseenter', e => p.active && p.active.mark(e) )
		menu.addEventListener('mouseleave', e => p.active && p.active.unmarkDelay(e) )
		menu.addEventListener('click',     e => e.stopPropagation() );
		menu.addEventListener('mousedown', e => e.stopPropagation() );

		let trash = c1.dom.fragment(
			'<div id=qgCmsContTrash>'+
			'	<svg width="50" height="60" viewBox="0 -5 26 30">'+
			'	  <path class="-lis" d="M18.902 1.194h-1.21C17.368.494 16.66 0 15.843 0H9.727c-.818 0-1.525.493-1.85 1.194h-1.21c-2.242 0-4.076 1.835-4.076 4.078H22.98c0-2.242-1.833-4.078-4.076-4.078z"/>'+
			'	  <path d="M3.83 21.988c0 1.97 1.612 3.582 3.583 3.582H18.16c1.97 0 3.58-1.612 3.58-3.582V6.466H3.83v15.522zm12.537-11.94c0-.66.535-1.194 1.194-1.194s1.194.535 1.194 1.194v11.94c0 .66-.534 1.193-1.193 1.193s-1.193-.534-1.193-1.192v-11.94zm-4.775 0c0-.66.534-1.194 1.194-1.194s1.194.535 1.194 1.194v11.94c0 .66-.534 1.193-1.194 1.193s-1.194-.534-1.194-1.192v-11.94zm-4.777 0c0-.66.534-1.194 1.193-1.194.66 0 1.194.535 1.194 1.194v11.94c0 .66-.534 1.193-1.194 1.193-.66 0-1.193-.534-1.193-1.192v-11.94z"/>'+
			'	</svg>'+
			'</div>', 'text/html').firstChild;
		document.body.append(trash);


		/* drag drop */
		let dd = new cms.contDrag();
		cms.contPos.dd = dd;
		dd.on('start',e=>{
			const el = e.target;
			dd.targets = document.querySelectorAll('.qgCMS-dropTarget.-e, #qgCmsContTrash');
			document.querySelectorAll('.qgCMS-dropTarget').forEach(el=>el.classList.add('dropTarget'))
			p.moving = true;
			menu.style.display = 'none';
			el.classList.add('-moving');
			trash.classList.add('-dropTarget');
			trash.c1ZTop();
		})
		dd.on('change',e=>{
			trash.classList[[(e.target.id==='qgCmsContTrash'?'add':'remove')]]('-full');
		})
		dd.on('stop',el=>{
			document.querySelectorAll('.qgCMS-dropTarget').forEach(el=>el.classList.remove('dropTarget'))
			p.moving = false;
			el.classList.remove('-moving');
			if (!cms.el.pid(el.parentNode)) { // trash
				$fn('page::remove')(cms.el.pid(el));
			} else {
				let next = el.nextElementSibling ? cms.el.pid(el.nextElementSibling) : null; // next '.qgCmsCont'?
				$fn('page::insertBefore')(cms.el.pid(el.parentNode), cms.el.pid(el), next).setInitiator('cms.dnd');
			}
			trash.classList.remove('-dropTarget');
		})

		let startX, startY, ddEl;
		function move(e) {
			if (e.ctrlKey) {
				const pid = cms.el.pid(ddEl);
				$fn('page::copy')(pid).run(ret=>{
					cms.cont(ret).addPosition();
				});
			} else {
				if (Math.max( Math.abs(startX-e.clientX), Math.abs(startY-e.clientY) ) < 6) return;
				dd.start(ddEl, e);
			}
			up();
		}
		function up() {
			document.removeEventListener('mousemove', move);
			document.removeEventListener('mouseup', up);
		}
		menu.addEventListener('mousedown', e=>{
			if (!cms.contPos.active.isDraggable()) return;
			ddEl   = cms.contPos.active.el;
			startX = e.clientX;
			startY = e.clientY;
			document.addEventListener('mousemove', move);
			document.addEventListener('mouseup', up);
			e.preventDefault();
		})
		let Placer = new c1.Placer(menu, {x:'prepend',y:'before', margin:{top:-.4,left:4,bottom:1,right:0} });/* firefox: top:-.4 */
		cms.contPos.on('mark', obj=>{
			let _ = cms.contPos;
			menu.style.display = 'flex'; // todo
			let isDraggable = obj.isDraggable(),
				mod     = obj.el.className.replace(/.*-m-([^\s]+).*/,'$1').replace(/-/g,'.');
			Placer.follow(obj.el);

			menu.mod.innerHTML = mod.replace(/^cms\.cont\./,'');
			menu.mod.setAttribute('title',mod+' ('+obj.pid+')');
			menu.drag.style.display = isDraggable ? 'block' : 'none';
			menu.opts.style.display = obj.el.classList.contains('-e') ? 'block' : 'none';
			menu.style.cursor = (isDraggable?'move':'default');

			if (obj.el.classList.contains('qgCMS-offline')) {
				menu.mod.append(c1.dom.fragment('<span style="animation:qgcms_fadeInOut .4s linear alternate infinite; font-family:qg_cms; font-size:1.2em; line-height:.2; display:inline-block; margin-left:.5em"> &#xe901;</span>'))
			}
			menu.style.backgroundColor = getComputedStyle(obj.el)['outline-color'];

			menu.c1ZTop();
		});
		cms.contPos.on('unmark', () => menu.style.display = 'none' );
		setTimeout(() => document.activeElement.blur());
		window.cmsClipboard && cms.frontend1.c1Use('clipboard', fn=>fn(cmsClipboard));
	});

	cms.console = {
		show(msg, type) {
			let el = this.el();
			el.classList.add('-active');
			el.setAttribute('data-type',type);
			el.c1ZTop();
			el.firstElementChild.innerHTML = msg;
			clearTimeout(this.timeout);
			this.timeout = setTimeout(()=>el.classList.remove('-active'), 2200);
			setTimeout(()=>el.classList.add('-new')   , 1);
			setTimeout(()=>el.classList.remove('-new'), 100);
		},
		el() {
			let el = document.getElementById('cmsConsole');
			if (!el) {
				document.body.insertAdjacentHTML('beforeend',
				'<div id=cmsConsole class="qgCMS q1Rst">'+
					'<div class=-msg></div>'+
				'</div>');
				el = document.getElementById('cmsConsole');
			}
			return el;
		}
	};
	Ask.on('complete', res=>{
		if (!res) return;
		if (res.cmsInfo) {
			cms.console.show(res.cmsInfo,'info');
		} else if (res.cmsWarning) {
			cms.console.show(res.cmsWarning,'warning');
		} else if (res.cmsError) {
			cms.console.show(res.cmsError,'error');
		}
	});

	cms.frontend1.dialog = (title,body,buttons)=>{
		c1.c1Use('dialog',()=>{
			const dialog = new c1.dialog({title,body,buttons,class:'qgCMS'});
			dialog.show();
			return dialog.element;
		})
	}

	$fn.on('page::insertBefore', function(e) {
		if (e.initiator === 'cms.dnd') return;
		if (e.arguments[1] == window.Page) {
			$fn('page::reload')(e.arguments[1]);
		} else {
			var els = document.querySelectorAll('.-pid'+e.arguments[1]);
			for (var i=0,el; el=els[i++];) el.parentNode.removeChild(el);
			$fn('page::reload')(e.arguments[0]);
		}
	});

}
