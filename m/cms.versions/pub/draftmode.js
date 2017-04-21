'use strict';
$(()=>{
	cms.contextMenueContent.addItem('Veröffentlichen', {
		icon: sysURL+'cms.versions/pub/check.png',
		selector: '.qgCmsCont',
		onshow(e) {
			this.activePid = cms.contPos.active.pid;
			this.disabled = !e.currentTarget.classList.contains('-e');
		},
		onclick() {
			publish(this.activePid);
		}
	});
	function publish(pid, subPages){
		if (!confirm('Möchten Sie die aktuelle Live-Version wirklich überschreiben?')) return;
		$fn('cms_vers::publishCont')(pid, {toSpace:0, subPages}).run(function(){
			location.href = location.href.replace(/#.*$/,'');
		});
	}
	// frontend1 integration
	var css =
	'#qgCmsFrontend1 [itemid=publish].-HasChanges > .-title { '+
	'	background:var(--cms-access-2); '+
	'} '+
	'#qgCmsFrontend1 [itemid=publish].-HasChanges > .-title::before { '+
	'	border-right-color:var(--cms-access-2); '+
	'} '+
	'#qgCmsFrontend1 [itemid=publish].-HasChanges .qgCms_vers_page_changed { '+
	'	display:block; '+
	'} '+
	'';
	var $el = $('<div class=-item itemid=publish>'+
		'<div class=-content>'+
			'<div class=-standalone>'+
				'<div class=-h1>Entwurf</div>'+
				'<div>Überschreiben Sie Ihren Entwurf mit der aktuellen Live-Version</div>'+
				'<div style="text-align:right">'+
					'<button class=-versionUnPublish style="width:200px">Entwurf zurücksetzen</button><br><br>'+
					'<label>inklusive Unterseiten <input class=-subPages type=checkbox style="vertical-align:text-bottom"></label><br>'+
				'</div>'+
				'<br><br><br>'+
				'<div class=-h1>Vergleichen</div>'+
				'<div>Vergleichen Sie die Unterschiede von Entwurfs- und Live-Version</div>'+
				'<div style="text-align:right">'+
					'<button style="width:200px" class=-versionCompare>Vergleichen</button>'+
				'</div>'+
				'<br><br><br>'+
				'<div class=-h1>Veröffentlichen</div>'+
				'<div>Machen Sie Ihren Entwurf öffentlich!</div>'+
				'<div class=qgCms_vers_page_changed hidden style="color:var(--cms-access-2);">Sie haben unveröffentlichte Änderungen!</div>'+
				'<br>'+
				'<div style="text-align:right">'+
					'<button class=-versionPublish style="width:200px">Veröffentlichen</button><br><br>'+
					'<label>inklusive Unterseiten <input class=-subPages type=checkbox style="vertical-align:text-bottom"></label><br>'+
				'</div>'+
			'</div>'+
		'</div>'+
		'<div class=-title style="xposition:relative">'+
			'<div class=-text>Entwurf</div>'+
		'</div>'+
		'<style>'+css+'</style>'+
	'</div>')
	.insertAfter('#qgCmsFrontend1 > .-sidebar > [itemid="more"]');
	let el = $el[0];

	el.querySelector('.-versionCompare').addEventListener('click',()=>{
		CmsVersComparer.compare(Page,{
			toSpace:0,
			accept(){ publish(Page); },
			acceptText:'Veröffentlichen'
		});
	});
	el.querySelector('.-versionPublish').addEventListener('click',function(){
		let subPages = this.parentNode.querySelector('.-subPages').checked;
		publish(Page, subPages);
	});
	el.querySelector('.-versionUnPublish').addEventListener('click',function(){
		let subPages = this.parentNode.querySelector('.-subPages').checked;
		if (!confirm("Achtung! \nMöchten Sie den Entwurf wirklich überschreiben?")) return;
		$fn('cms_vers::publishCont')(Page, {toSpace:1, fromSpace:0, subPages}).run(()=>{
			location.href = location.href.replace(/#.*$/,'');
		});
	});

	// change "changed-status"
	Ask.on('complete', function(res) {
		if (!res || !res.cms_vers_changed) return;
		for (var pid in res.cms_vers_changed) {
			pid == Page && el.classList.add('-HasChanges');
		}
	});
	window.cms_vers_draft_changed && el.classList.add('-HasChanges');

});
