/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

document.addEventListener('DOMContentLoaded',()=>{
	'use strict';

	let Menu = cms.contextMenueContent = c1.globalContextMenu.addMenu('CMS Inhalt',{
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/settings.png',
		selector: '.qgCmsCont',
	});

	Menu.addItem('Einstellungen', {
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/settings.png',
		selector: '.qgCmsCont',
		onshow(e) {
			this.activePid = cms.contPos.active.pid;
			this.disabled = !e.currentTarget.classList.contains('-e');
		},
		onclick() {
			cms.cont.active = this.activePid;
			cms.panel.set('sidebar','settings');
		}
	});
	Menu.addItem('Verschieben', {
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/move.png',
		selector: '.qgCmsCont',
		onshow(e) {
			this.activeEl = e.currentTarget;
			this.disabled = !cms.contPos(this.activeEl).isDraggable();
		},
		onclick() { cms.contPos.dd.start(this.activeEl);  }
	});
	Menu.addItem('Kopieren', {
		selector: '.qgCmsCont',
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/copy.png',
		onshow(e) {
			this.activePid = cms.contPos.active.pid;
			this.disabled = !e.currentTarget.classList.contains('-e');
		},
		onclick() {
			$fn('page::copy')(this.activePid).run(ret=>{
				cms.cont(ret).addPosition();
			});
		}
	});
	Menu.addItem('Ausschneiden', {
		selector: '.qgCmsCont',
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/cut.png',
		onshow(e) {
			this.activePid = cms.contPos.active.pid;
			this.disabled = !e.currentTarget.classList.contains('-e');
		},
		onclick() {
			var pid = this.activePid;
			$fn('cms::clipboardSet')(pid).run(()=>{
				let els = document.querySelectorAll('.-pid'+pid);
				for (let el of els) el.style.opacity = .3;
			});
		}
	});
	Menu.addItem('Löschen', {
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/delete.png',
		selector: '.qgCmsCont',
		onshow(e) {
			this.activeEl = e.currentTarget;
			this.disabled = !cms.contPos(this.activeEl).isDraggable();
		},
		onclick() {
			var el = this.activeEl;
			if (!confirm('Möchten Sie den Inhalt wirklich löschen?')) return;
			var pid = cms.el.pid(el);
			el.remove();
			$fn('page::remove')(pid).run();
		}
	});

	let TreeMenu = c1.globalContextMenu;

	TreeMenu.addItem('Einstellungen', {
		selector: '#qgCmsFrontend1 .dynatree-node',
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/settings.png',
		onshow(e) {
			this.contextTarget = e.currentTarget;
			this.lastPid = this.contextTarget.parentNode.title.replace('ID ','');
			var access = e.currentTarget.className.match(/-access-([0-9])/)[1];
			this.disabled = access < 2;
			var n = cms.Tree.getNodeByKey(this.lastPid);
			n.activate();
		},
		onclick() {
			cms.cont.active = this.lastPid;
			cms.panel.set('sidebar','settings');
		}
	});
	TreeMenu.addItem('Umbenennen', {
		selector:'#qgCmsFrontend1 .dynatree-node',
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/edit.png',
		onshow(e) {
			var el = e.currentTarget;
			this.lastPid = el.parentNode.title.replace('ID ','');
			var access = el.className.match(/-access-([0-9])/)[1];
			this.disabled = access < 2;
		},
		onclick() {
			var node = cms.Tree.getNodeByKey(this.lastPid);
			cms.Tree.editNode(node);
		}
	});
	TreeMenu.addItem('Kopieren', {
		selector:'#qgCmsFrontend1 .dynatree-node',
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/copy.png',
		onshow(e) {
			var el = e.currentTarget;
			this.lastPid = el.parentNode.title.replace('ID ','');
			var access = el.className.match(/-access-([0-9])/)[1];
			this.disabled = access < 2;
		},
		onclick() {
			var n = cms.Tree.getNodeByKey(this.lastPid);
			cms.frontend1.dialog('Die Seite "'+n.data.title+'" kopieren?',[
				{
					title:'Seite kopieren',then(){
						$fn('page::copy')(n.data.key).run(ret=>{
							n.parent.reloadChildren();
						});
					}
				},{
					title:'inklusiv Unterseiten',then(){
						$fn('page::copy')(n.data.key,true).run(ret=>{
							n.parent.reloadChildren();
						});
					}
				},{
					title:'Abbrechen',then(){
						this.closest('.-Box').remove();
					}
				}
			]);
		}
	});
	TreeMenu.addItem('Löschen', {
		selector: '#qgCmsFrontend1 .dynatree-node',
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/delete.png',
		onshow(e) {
			var el = e.currentTarget;
			this.lastPid = el.parentNode.title.replace('ID ','');
			this.disabled = !el.classList.contains('-access-3');
		},
		onclick() {
			var n = cms.Tree.getNodeByKey(this.lastPid);
			if (!confirm('Möchten Sie die Seite "'+n.data.title+'" wirklich löschen?')) return;
			$fn('page::remove')(n.data.key).run(ret=>{
				if (ret.parent_id && n.data.key==Page) {
					location.href = "?cmspid="+ret.parent_id;
				} else {
					var s = n.getPrevSibling() || n.getNextSibling() || n.parent;
					n.remove();
					s && s.activate();
				}
			});
		}
	});

});

// on contextmenu stop marking other contents. Also for native contextmenu (firefox)
!function(){
	var ignoreMouse = function(e){
		e.stopPropagation();
		e.preventDefault();
	}
	var ignoreMouseEnd = function(){
		document.removeEventListener('mouseover',ignoreMouse,true);
		document.removeEventListener('mouseleave',ignoreMouse,true);
		document.removeEventListener('mousedown',ignoreMouseEnd,true);
	}
	document.addEventListener('contextmenu',()=>{
		ignoreMouseEnd();
		if (!cms.contPos.active) return;
		document.addEventListener('mouseover',ignoreMouse,true);
		document.addEventListener('mouseleave',ignoreMouse,true);
		document.addEventListener('mousedown',ignoreMouseEnd,true);
		cms.contPos.active.mark();
	})
}();
