$(function() {
	$(document.body).on('mousedown','.qgShowTable', function(e) {
		var el = $(e.target);
		var cont = cms.el.pid(e.target);
		var table = el.html();
		$fn('page::loadPart')(cont, 'tStruct', {table:table}).run();
		$('.qgShowTable').each(function(i,a) {
			a.style.color = a.innerHTML === table ? 'red' : '';
		});
		e.preventDefault();
	});
});
function dbAddTable(cont) {
	var name = prompt('Name?');
	if (name) {
		$fn('superuser_db::addTable')(name);
		$fn('page::reload')(cont);
		$fn('page::loadPart')(cont, 'tStruct', {table:name}).run();
	}
}
function dbAddField(cont,table) {
	var name = prompt('Name?');
	if (name) {
		$fn('superuser_db::addField')(table, name);
		$fn('page::loadPart')(cont,'tStruct',{table:table});
		$fn('page::loadPart')(cont,'fStruct',{table:table,field:name}).run();
	}
}
function dbRes(pid, obj) {
	var part = 'resTable';
	if (obj.table && obj.table!==dbRes.queryObj.table) {
		part = 'tEntries';
		dbRes.queryObj.search = '';
		dbRes.queryObj.find = '';
		dbRes.queryObj.page = 1;
		$fn('page::loadPart')(pid,'tStruct',{table:obj.table});
	}
	c1.ext(obj, dbRes.queryObj, 1);
	$fn('page::loadPart')(pid, part, dbRes.queryObj).run();
}
dbRes.queryObj = {};
function setFieldShow(pid, t, f, v) {
	var set = {};
	set.table = {};
	set.table[t] = {};
	set.table[t].field = {};
	set.table[t].field[f] = {show:v};
	$fn('page::setUser')(pid,set);
	dbRes(pid,{table:t});
}
