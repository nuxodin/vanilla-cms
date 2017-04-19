/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
$(function() {
	var handles = new qgTableHandles();
	var els = handles.els;
	var active, pid;

	$(document).on('focus','.-m-cms-cont-table1 > table > tbody > tr > td', function(e) {
		active = this;
		handles.showTd(active);
		pid = cms.el.pid(active);
	});
	$(document).on('blur','.-m-cms-cont-table1 > table > tbody > tr > td', function(e) {
		handles.hide();
	});
	els.rowRem.on('click', function() {
		$('#cmsContentWindow').focus(); // to save the text!!
		var row = active.parentNode.rowIndex;
		document.activeElement.blur();
		$fn('page::api')(pid,{do:'rowRem',row:row});
	});
	els.rowAddAfter.on('click', function() {
		$('#cmsContentWindow').focus();
		var row = active.parentNode.rowIndex;
		document.activeElement.blur();
		$fn('page::api')(pid,{do:'rowAddAfter',row:row});
	});
	els.colRem.on('click', function() {
		$('#cmsContentWindow').focus();
		var col = active.cellIndex;
		document.activeElement.blur();
		$fn('page::api')(pid,{do:'colRem',col:col});
	});
	els.colAddRight.on('click', function() {
		$('#cmsContentWindow').focus();
		var col = active.cellIndex;
		document.activeElement.blur();
		$fn('page::api')(pid,{do:'colAddRight',col:col});
	});
});

cms.initCont('cms.cont.table1',function(el){
	// past tables
	el.addEventListener('paste',function(e){
		if (e.clipboardData.types.includes('text/html')) {
			var html = e.clipboardData.getData('text/html');
			html = html.replace(/([\s\S]*)<body>/,'');
			html = html.replace(/<\/body>([\s\S]*)/,'');
			html = html.replace('<!--StartFragment-->','');
			html = html.replace('<!--EndFragment-->','');
			html = $(html);
			if (html.length !== 1 || html[0].tagName !== 'TABLE') return;
			e.preventDefault(); // not working!
			setTimeout(function(){
				var table = html[0];
				var targetTd = e.target.closest('.qgCmsCont > table > * > tr > td');
				var startCellIndex = targetTd.cellIndex;
				for (var i=0, tbody; tbody = table.children[i++];) {
					for (var j=0, tr; tr = tbody.children[j++];) {
						for (var k=0, td; td = tr.children[k++];) {
							var div = targetTd.firstElementChild
							div.innerHTML = td.innerHTML;
							$(div).trigger('input');
							if (!targetTd.nextSibling) break;
							targetTd = targetTd.nextSibling;
						}
						var targetTr = targetTd.parentNode.nextElementSibling;
						if (!targetTr) break;
						targetTd = targetTr.children[startCellIndex];
					}
				}
			})
		}
	})
})
