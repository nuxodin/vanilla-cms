/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
c1.c1Use('tableHandles',function(){
	var handles = new c1.tableHandles();
	var active, pid, doc = document;
	doc.documentElement.addEventListener('focus', e=>{
		let el = e.target.closest('.-m-cms-cont-table1 > table > tbody > tr > td');
		if (!el) return;
		active = el;
		handles.showTd(active);
		pid = cms.el.pid(active);
	},true);
	doc.addEventListener('blur', e=>{
		handles.hide();
	},true);
	handles.rowRemove.addEventListener('click',()=>{
		var row = active.parentNode.rowIndex;
		doc.activeElement.blur();
		$fn('page::api')(pid,{do:'rowRem',row});
	});
	handles.rowAdd.addEventListener('click', ()=>{
		var row = active.parentNode.rowIndex;
		doc.activeElement.blur();
		$fn('page::api')(pid,{do:'rowAddAfter',row});
	});
	handles.colRemove.addEventListener('click',()=>{
		var col = active.cellIndex;
		doc.activeElement.blur();
		$fn('page::api')(pid,{do:'colRem',col});
	});
	handles.colAdd.addEventListener('click',()=>{
		var col = active.cellIndex;
		doc.activeElement.blur();
		$fn('page::api')(pid,{do:'colAddRight',col});
	});
});

cms.initCont('cms.cont.table1',function(el){
	// past tables
	el.addEventListener('paste',e=>{
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
			});
		}
	});
});
