/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
document.addEventListener('DOMContentLoaded',function(){

	let panel = cms.panel = new xCollection(cmsFrontend1Data);
    let el = document.getElementById('qgCmsFrontend1');

	/* sidebar */
	panel.loadWidget = (widget, params, cb)=>{
		const widgetEl = el.c1Find('[widget="'+widget+'"]');
		if (!widgetEl) return;
		c1.c1Use('loading',loading=>{
			loading.mark(widgetEl);
			if (!params) params = {};
			params.pid = params.pid || cms.cont.active || Page; // neu
			$fn('cms_frontend_1::widget')(widget, params).then(res => {
				loading.done(widgetEl)
				//widgetEl.innerHTML = res; // scripts are not executed :(
				$(widgetEl).html(res)
				cb && cb({target:$(widgetEl)});
			});
		})
	}
	panel.on('set', function(e){
		e.old !== e.value && $fn('Setting')(this.data, ['cms.frontend.1','custom']);
		if (e.name==='sidebar') {

			el.c1FindAll('> .-sidebar > .-item').forEach(el=>el.classList.remove('-open'));

			if (e.value) {
				let item = el.c1Find('> .-sidebar > .-item[itemid="'+e.value+'"]');
				item.classList.add('-open');
				item.focus();

				el.c1ZTop();

				document.querySelectorAll('.qgCMS_editmode_switch').forEach(item=>item.c1ZTop());

				el.classList.add('-open');
				const load = el.c1Find('> .-sidebar > [itemid="'+e.value+'"] > .-content').getAttribute('widget');
				load && panel.loadWidget(e.value, {pid: cms.cont.active || Page});
			} else {
				el.classList.remove('-open');
			}
		}
	});

	function titleDown(e){
		if (e.type === 'mousedown' && e.which !== 1) return;
		let titelEl = e.target.closest('.-sidebar > .-item > .-title');
		if (!titelEl) return;
		cms.cont.active = Page;
		const sidebar = titelEl.closest('[itemid]').getAttribute('itemid');
		panel.set('sidebar',sidebar);
		//e.preventDefault(); zzz need to scroll on mobile
	}
	el.addEventListener('mousedown',titleDown);
	el.addEventListener('touchstart',titleDown);

	/* widgets */
	panel.get('widget').on('set', function(e) {
		$fn('Setting')(this.data, ['cms.frontend.1','custom','widget']);
		if (e.value) {
			panel.loadWidget(e.name, {pid: cms.cont.active || Page});
		} else {
			this.innerHTML = '';
		}
    });
	el.addEventListener('mousedown',e=>{
		if (e.which !== undefined && e.which !== 1) return;
		let wHead = e.target.closest('.-widgetHead');
		if (!wHead) return;
		e.preventDefault();
		const value = wHead.classList.toggle('-open');
		const widget = wHead.nextElementSibling.getAttribute('widget');
		if (!widget) return;
		panel.get('widget').set(widget,value);
	})

	function enterSensor(){ el.classList.add('-sidebar-open'); }
	el.c1Find('> .-sidebar > .-sensor').addEventListener('mouseenter', enterSensor);
	el.c1Find('> .-sidebar > .-sensor').addEventListener('touchstart', enterSensor);

	function outsideDown(e){
		if (e.type === 'mousedown' && e.which !== 1) return;
		if (el.children[0] !== e.target && el.contains(e.target)) return;
		el.classList.remove('-sidebar-open');
		panel.set('sidebar','');
	}
	document.addEventListener('mousedown',outsideDown);
	document.addEventListener('touchstart',outsideDown);

	// shortcuts
	document.addEventListener('keydown', e=>{
    	if (e.target.isContentEditable || e.target.form !== undefined) return;
    	if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;

		if (e.which == 84) { // t
			cms.cont.active = cms.contPos.active.pid;
			cms.panel.set('sidebar', 'tree');
    		e.preventDefault();
    	}
		if (e.which == 32) { // space
			cms.cont.active = cms.contPos.active.pid;
			cms.panel.set('sidebar', 'settings');
    		e.preventDefault();
    	}
		if (e.which == 86) { // v
			cms.panel.toggle('sidebar','add');
			setTimeout(()=>{
				let inp = el.c1Find('[widget="add"] .-h1 > input');
				inp && inp.focus();
			}, 400);
		}
		if (e.which == 27) { // esc
			cms.panel.set('sidebar','');
		}
		if (e.which == 78) { // n
			cms.panel.set('sidebar','tree');
			setTimeout(()=>{
				const inp = el.c1Find('#cmsPageAddInp')
				inp && inp.focus()
			}, 400);
		}
    });

	$fn.on('page::addContent', e => cms.panel.set('sidebar','') );

	cms.cont.on('upload', ev => {
		cms.cont(ev.pid).showWidget('media');
		//var File = ev.File; zzz
		ev.on('progress', e => {
			const percent = Math.round(e.loaded * 100 / e.total);
			const button = el.c1Find('[cmsconf="contMedia_overview"] button');
			if (button) {
				button.innerHTML = percent+'%';
				button.style.minWidth = '150px';
				button.style.backgroundImage = 'linear-gradient(to right, var(--cms-color); 0%, var(--cms-color); '+percent+'%, transparent '+percent+'%, transparent)';
			}
		});
		ev.on('complete', e => {
			cms.console.show('Datei hochgeladen');
			cms.cont(ev.pid).showWidget('media',true);
		});
	})

	$fn.on('page::FileAdd',   e => cms.cont(e.arguments[0]).showWidget('media',true) );
	cms.cont.prototype.showWidget = function(what, reload) {
		if (!reload) {
			if (cms.cont.active == this.id && what === cms.panel.get('widget').get(what)) return;
		}
		cms.cont.active = this.id;
		cms.panel.get('widget').set(what, 1)
		cms.panel.set('sidebar','settings');
		cms.Tree && cms.Tree.goTo(this.id);
	}

	!document.querySelector('.-e.qgCMS-dropTarget') && el.c1Find('> .-sidebar > [itemid=add]').setAttribute('hidden','hidden');

	const switches = document.querySelectorAll('.qgCMS_editmode_switch');
	for (let switc of switches) {
		function enter(){ el.classList.add('-open','-sidebar-open'); }
		switc.addEventListener('mouseenter', enter);
		switc.addEventListener('touchstart', enter);
	}

	/* update accordion-heads */
	$fn.on('page::onlineStart',       e=>panel.loadWidget('access.time.head') );
	$fn.on('page::onlineEnd',         e=>panel.loadWidget('access.time.head') );
	$fn.on('page::setPublic',         e=>panel.loadWidget('access.grp.head') );
	$fn.on('page::changeGroup',       e=>panel.loadWidget('access.grp.head') );
	$fn.on('page::changeUser',        e=>panel.loadWidget('access.usr.head') );
	$fn.on('page::FileDelete',        e=>panel.loadWidget('media.head') );
	$fn.on('page::filesDeleteDouble', e=>panel.loadWidget('media.head') );
	$fn.on('page::filesDeleteAll',    e=>panel.loadWidget('media.head') );
	$fn.on('page::filesDeleteDouble', e=>panel.loadWidget('media.head') );
	$fn.on('page::addClass',          e=>panel.loadWidget('classes.head') );
	$fn.on('page::removeClass',       e=>panel.loadWidget('classes.head') );
	$fn.on('page::requestAdd',        e=>panel.loadWidget('urls.head') );
	$fn.on('page::requestDel',        e=>panel.loadWidget('urls.head') );


	/* beta */
	// panel.on('set', function(e){
	// 	if (e.name === 'crowd out') {
	// 		document.body.classList.toggle('qgCmsFrontend1-crowdOut', e.value);
	// 	}
	// });
	// panel.set('crowd out', panel.get('crowd out'));

	// deprecated, frontend.0 compatibility
	cms.panel.tabs = {};
	cms.panel.tabs.show = what=>{
		console.warn('deprecated');
		cms.cont(cms.cont.active).showWidget(what);
	};
});

c1.onElement('.qgCmsTreeManager',el=>{
	// add Page
	const inp = document.getElementById('cmsPageAddInp');
	function add() {
		const v = inp.value.trim();
		v && cms.Tree.addPage(v);
		inp.value = '';
	}
	inp.addEventListener('blur',function(){
		this.value && confirm('Möchten Sie die Seite "'+this.value+'" erstellen?') ? add() : null;
	});
	inp.addEventListener('keydown',function(e){
		e.which === 13 && add();
		if (e.which === 27) {
			this.value = '';
			this.blur();
		}
	});
	const tree = JSON.parse(el.getAttribute('data'));
	cmsTreeInit(tree);
	// change placeholder
	const old = cms.Tree.options.onActivate
	cms.Tree.options.onActivate = function(node){
		inp.placeholder = inp.placeholder.replace(/"([^"]*)"/, '"'+node.data.title+'"');
		old.apply(this, arguments);
	}
	/* go to hash-url  if (!isset(G()->ASK['serverInterface']) && G()->SET['cms.frontend.1']['custom']['tree_show_c']->v) { ?>
	setTimeout(function(){
		var to = location.hash.match(/cmspid([0-9]+)/);
		to && cms.Tree.goTo(to[1]);
	})
	} */
});

c1.onElement('.qgCmsFileManager',el=>{
	var pid = el.getAttribute('pid');
	var $el = $(el);
	c1.c1Use('form',function(){
		el.c1Find('.-uploadBtn').addEventListener('click', async function(){
			const files = await c1.form.fileDialog();
			upload(files);
		})
	});
	var tbody = $el.find('tbody');
	if (tbody[0]) {
		tbody.children().each(function(i, tr) {
			var img  = tr.querySelector('.-preview > img'),
				f = 1, oW, oH;
			if (!img) return;
			img.parentNode.addEventListener('wheel',function(e){
				if (f===1) {
					oW = img.offsetWidth;
					oH = img.offsetHeight;
				}
				e.preventDefault();
				var newf = e.wheelDelta < 0 ? f / 0.6666 : f * 0.6666;
				if (newf >= 1 && newf <= 15) {
					f = newf;
					var w = parseInt(f * oW);
					var h = parseInt(f * oH);
					new dbFile(img).set('h', h).set('w', w).write();
					img.height = h;
					img.width  = w;
				}
			});
		});
		tbody.sortable({
			handle:'.-handle',
			axis: 'y',
			stop(x) {
				var sort = [];
				tbody.children().each((i, el) => sort.push(el.getAttribute('itemid')) );
				$fn('page::FilesSort')(pid, sort).run();
			}
		});
		tbody.on('click','.-delete',function(){
			var tr = this.closest('tr');
			confirm('Möchten Sie die Datei wirklich löschen?') && $fn('page::FileDelete')(pid, tr.getAttribute('itemid')).run( () => tr.remove() );
		});
		tbody.on('click','.-preview', function(e) {
			var replaces = this.closest('tr').getAttribute('itemid');
			$('<input type=file>').on('change', function(){
				upload(this.files, replaces);
			})[0].click();
		});
		tbody.on('dragstart','.-preview > [draggable], .-link > a', e=>{
			cms.panel.set('sidebar','');
		});
		tbody[0].addEventListener('dragstart',e=>{
			if (!e.target.matches('audio')) return;
			e.dataTransfer.effectAllowed = 'copy';
			e.dataTransfer.setData('text/html', e.target.outerHTML);
		});
	}

	var upload = (files, replaces)=>{
		for (const file of files) cms.cont(pid).upload(file, reload, replaces);
	}
	var reload = ()=>{
		$fn('page::reload')(pid);
		cms.panel.get('widget').set('media',1);
	}
	el.c1Find('.-addExistingFile').addEventListener('select_by_pointer',function(){
		this.value && $fn('page::FileAdd')(pid,this.value).run();
	})
	if (el.c1Find('.-sortFilesSelect')) {
		el.c1Find('.-sortFilesSelect').addEventListener('change',function(){
			this.value && $fn('page::filesSetOrder')(pid,this.value,'asc') && reload();
		});
		el.c1Find('.-deleteFilesSelect').addEventListener('change',function(){
			const val = this.options[this.selectedIndex].value;
			val === 'double' && $fn('page::filesDeleteDouble')(pid) && reload();
			val === 'all'    && confirm('Möchten Sie die Dateien wirklich löschen?') && $fn('page::filesDeleteAll')(pid) && reload();
		});
	}
});
c1.onElement('.qgCmsFront1ModuleManager',el=>{
	c1.c1Use(['loading'/*,'tooltip'*/],1); // preload

	const searchInp = el.c1Find('input');
	searchInp.addEventListener('input',function(){
		for (let box of el.c1FindAll('.-module-boxes > *')) {
			box.style.display = box.textContent.toLowerCase().match(this.value.toLowerCase())?'flex':'none';
		}
	})

	/* add module */
	el.addEventListener('mousedown',e=>{
		if (e.which !== 1) return;
		const box = e.target.closest('.-module-boxes > [itemid]');
		if (!box) return;
        const itemid = box.getAttribute('itemid');
		c1.c1Use('loading',loading=>{
			loading.mark(box);
			if (box.closest('.cmsAddModels')) {
				$fn('page::copy')(itemid).run(ret=>{
					cms.panel.set('sidebar','');
					cms.cont(ret).addPosition();
				});
			} else {
				cms.cont.add(itemid);
			}
		});
        e.preventDefault();
	});
});
c1.onElement('.qgCmsFront1AccessGrpManager',el=>{
	const pid = el.getAttribute('pid');
	el.c1Find('.-inherit').addEventListener('change',function(){
		const value = this.checked ? null : this.value; // not inherit ? set it to what it was inherited
		$fn('page::setPublic')(pid, value); cms.panel.get('widget').set('access.grp',1);
	});
	const searchInp = el.c1Find('.-search');
	searchInp && searchInp.addEventListener('keyup',function(){
		cms.panel.loadWidget('access.grp.list',{pid, search:this.value});
	}.c1Debounce(150));
	// change grp access
	el.addEventListener('change',e=>{
		var inp = e.target;
		if (!inp.closest('[widget="access.grp.list"]')) return;
		if (inp.name === 'public') {
			$fn('page::setPublic')(pid,inp.value).run();
		} else {
			$fn('page::changeGroup')(pid,inp.name.replace('g_', ''), inp.value).run();
		}
	});
});
c1.onElement('.qgCmsFront1AccessUsrManager',el=>{
	const pid = el.getAttribute('pid');
	const searchInp = el.c1Find('.-search');
	searchInp && searchInp.addEventListener('keyup',function(){
		cms.panel.loadWidget('access.usr.list',{pid, search:this.value});
	}.c1Debounce(150));
	// change usr access
	el.addEventListener('change',e=>{
		var inp = e.target;
		if (!inp.closest('[widget="access.usr.list"]')) return;
		$fn('page::changeUser')(pid,inp.name.replace('u_', ''), inp.value);
	});
});
c1.onElement('.qgCmsFront1AccessTimeManager',el=>{
	const pid = el.getAttribute('pid');

	var inpStart = el.c1Find('.-start');
	inpStart.addEventListener('blur',()=>{
		var value = inpStart.qgDateInput.hidden.value;
		$fn('page::onlineStart')(pid,value).run();
		cms.panel.get('widget').set('access.time',1);
	});
	el.c1Find('.-start_always').addEventListener('click',()=>{
		$fn('page::onlineStart')(pid, '0');
		cms.panel.get('widget').set('access.time',1);
	})
	var startNow = el.c1Find('.-start_now');
	startNow.addEventListener('click', ()=>{
		$fn('page::onlineStart')(pid, Math.ceil(Date.now()/1000) );
		cms.panel.get('widget').set('access.time',1);
	});
	el.c1Find('.-start_inherit').addEventListener('click', ()=>{
		$fn('page::onlineStart')(pid, null).run();
		cms.panel.get('widget').set('access.time',1);
	});
	inpStart.style.display = inpStart.value ? 'block' : 'none';
	startNow.style.display = inpStart.value ? 'none'  : 'block';


	var inpEnd = el.c1Find('.-end');
	inpEnd.addEventListener('blur',()=>{
		var value = inpEnd.qgDateInput.hidden.value;
		$fn('page::onlineEnd')(pid,value).run();
		cms.panel.get('widget').set('access.time',1);
	});
	el.c1Find('.-end_always').addEventListener('click',()=>{
		$fn('page::onlineEnd')(pid, '0');
		cms.panel.get('widget').set('access.time',1);
	})
	var endNow = el.c1Find('.-end_now');
	endNow.addEventListener('click', ()=>{
		$fn('page::onlineEnd')(pid, Math.ceil(Date.now()/1000) );
		cms.panel.get('widget').set('access.time',1);
	});
	el.c1Find('.-end_inherit').addEventListener('click', ()=>{
		$fn('page::onlineEnd')(pid, null).run();
		cms.panel.get('widget').set('access.time',1);
	});
	inpEnd.style.display = inpEnd.value ? 'block' : 'none';
	endNow.style.display = inpEnd.value ? 'none'  : 'block';
});
c1.onElement('.qgCmsFront1ClassesManager',el=>{
	const pid = el.getAttribute('pid');
	el.addEventListener('change', e=>{
		const item = e.target.closest('.-added');
		if (!item) return;
		const fn = item.checked?'addClass':'removeClass';
		$fn('page::'+fn)(pid, item.nextElementSibling.innerHTML);
		$fn('page::reload')(pid);
	}, false);
	el.c1Find('.-add').addEventListener('keydown', function(e){
		if (e.which === 13) {
			$fn('page::addClass')(pid, this.value);
			cms.panel.get('widget').set('classes',1);
			$fn('page::reload')(pid);
		}
	});
});
c1.onElement('.qgCmsFront1UrlManager',el=>{
	const pid = el.getAttribute('pid');
	el.c1Find('> .-urls').addEventListener('change',e=>{
		var tr = e.target.closest('[data-lang]');
		var lang = tr.getAttribute('data-lang');

		var inp = e.target.closest('.-target');
		if (inp) {
			$fn('page::urlTargetSet')(pid,lang,inp.checked?'_blank':'')
		}
		inp = e.target.closest('.-url');
		if (inp) {
			$fn('page::urlCustomSet')(pid,lang,inp.value);
			tr.c1Find('.-custom').checked = true;
		}
		inp = e.target.closest('.-custom');
		if (inp) {
			$fn('page::urlCustomUnset')(pid,lang).run(url=>{
				tr.c1Find('.-url').value = url;
			});
		}
	})
	el.c1Find('> .-directlinks').addEventListener('click',e=>{
		const del = e.target.closest('.-delete');
		if (!del) return;
		const tr = del.closest('tr');
		const v = tr.firstElementChild.innerHTML;
		tr.remove();
		$fn('page::requestDel')(pid,v).run();
	});

	var addInp = el.c1Find('.-add_inp');
	addInp.addEventListener('keyup',function(){
		$fn('cms::requestUsed')(this.value).then(v=>{
			this.style.border = v ? '1px solid red' : '1px solid green';
		});
	}.c1Debounce(200))
	addInp.addEventListener('keydown',e=>{
		e.which === 13 && cmsRequestSet();
	});
	el.c1Find('.-add').addEventListener('click',cmsRequestSet);

	function cmsRequestSet() {
		var v = addInp.value;
		$fn('page::requestAdd')(pid,v);
		cms.panel.get('widget').set('urls',1);
	}
});
c1.onElement('.qgCmsFront1DiversManager',el=>{
	const pid = el.getAttribute('pid');
	el.c1Find('.-visible').addEventListener('change',function(){
		$fn('page::setVisible')(pid,this.checked);
	});
	el.c1Find('.-searchable').addEventListener('change',function(){
		$fn('page::setSearchable')(pid,this.checked);
	});
	el.c1Find('.-name').addEventListener('input',function(){
		$fn('page::name')(pid, this.value)
	}.c1Debounce(400));
	el.c1Find('.-name').addEventListener('change',function(){
		$fn('page::name')(pid, this.value)
	});
	el.c1Find('.-model').addEventListener('change',function(){
		$fn('Setting')(this.value,this.name);
		cms.panel.loadWidget('divers',{pid});
	})
	el.c1Find('.-basis').addEventListener('blur',function(){
		this.value && $fn('page::insertBefore')(this.value, pid);
	});
	el.c1Find('.-childXML').addEventListener('change',function(){
		$fn('page::setDefault')(pid,{'childXML':this.value});
	});
});
c1.onElement('.qgCmsFront1SeoManager',el=>{
	const desc = el.c1Find('.-desc');
	function checkTextarea(el){ el.classList[el.value.match(/^.{60,156}$/)?'remove':'add']('-invalid'); }
	desc.addEventListener('input',function(){ checkTextarea(this); });
	checkTextarea(desc);

});
c1.onElement('.qgCmsFront1MoreManager',el=>{
	// feedback-formular
	el.c1Find('.-feedbackform').addEventListener('submit',function(e){
		e.preventDefault();
		cms.panel.loadWidget('more',{
			pid:  Page,
			msg:  this.c1Find('[name=msg]').value,
			link: location.href
		});
	});
	el.c1Find('.-feedbackform [name=msg]').addEventListener('input', function() {
		$fn('Setting')(this.value,['cms','feedback','text']);
	}.c1Debounce(200));
	// change password
	el.c1Find('.-pwchange').addEventListener('submit', function(e) {
		e.preventDefault();
		var old = this.c1Find('[name=old]').value;
		var n   = this.c1Find('[name=new]').value;
		var n2  = this.c1Find('[name=new2]').value;
		if (n!==n2) alert('Die Passwörter stimmen nicht überein');
		else {
			$fn('core::changePw')(old, n).run(res=>{
				switch (res) {
					case  1: alert('Das Passwort wurde erfolgreicht geändert.'); break;
					case -1: alert('Das alte Passwort ist nicht korrekt.'); break;
					case -2: alert('Das Passwort ist zu kurz.'); break;
				}
			});
		}
	});
	el.c1Find('.-changelang').addEventListener('change', function(e) {
		var val = this.options[this.selectedIndex].value;
		var SET_id = this.name;
		$fn('Setting')(val, SET_id).run(()=>{
			location.href = location.href.replace(/#.*$/,'');
		});
	});
	el.c1Find('.-tree-show-c').addEventListener('change', function(e) {
		var SET_id = this.name;
		$fn('Setting')(this.checked, SET_id).run(()=>{
			location.href = location.href.replace(/#.*$/,'');
		});
	});
	// show editables
	el.c1Find('.-showEditables').addEventListener('mouseenter', e=>{
		document.documentElement.classList.add('cmsShowEditables');
	});
	el.c1Find('.-showEditables').addEventListener('mouseleave', e=>{
		document.documentElement.classList.remove('cmsShowEditables');
	});
});

c1.onElement('.qgCMSFron1ContManager',el=>{
	const pid = el.getAttribute('pid');
	// change module
	el.c1Find('.-changemodule').addEventListener('change', function(e) {
		const val = this.options[this.selectedIndex].value;
		const type = el.getAttribute('page-type');
		$fn('page::setModule')(pid, val).then(()=>{
			if (type === 'p') location.href = location.href.replace(/#.*$/,'');
		});
		if (type !== 'p') {
			$fn('page::reload')(pid);
			cms.panel.set('sidebar','settings');
		}
	});
	// übergeordnet
	var editparent = el.c1Find('.-editparent');
	editparent && editparent.addEventListener('click', function(e) {
		var pid = this.getAttribute('parent');
		var type = this.getAttribute('page-type');
		if (type!=='p') {
			e.preventDefault();
			cms.cont.active = pid;
			cms.panel.set('sidebar','settings');
		}
	});
});
c1.onElement('.qgCmsFront1SuperuserManager',el=>{
	const pid = el.getAttribute('pid');
	el.addEventListener('keyup',e=>{
		if (e.which !== 13 ) return;
		const create = e.target.closest('.-create');
		if (!create) return;
		const scope = e.target.closest('[scope]').getAttribute('scope');
		cms.panel.loadWidget('superuser', {pid, create:create.value, in:scope});
	})
	el.addEventListener('click', e=>{
		const scopeEl = e.target.closest('[scope]');
		if (!scopeEl) return;
		//const scope = scopeEl.getAttribute('scope');
		const remove = e.target.closest('.-remove');
		if (remove) {
			const file = remove.parentNode.getAttribute('itemid');
			confirm('Möchten Sie die Datei löschen?') && cms.panel.loadWidget('superuser', {pid,delete:file});
		}
	})
});

c1.onElement('.qgCmsSettingsEditor',el=>{
	const pid = el.getAttribute('pid');
	el.addEventListener('qgSettingsEditorChange',()=>$fn('page::reload')(pid));
});

/* xCollection */
var xCollection = function(obj) {
	this.data = {};
	obj && this.set(obj);
};
c1.ext(c1.Eventer, xCollection.prototype);
c1.ext({
	set(n, v) {
		if (typeof n === 'object') {
			for (var key in n) {
				n.hasOwnProperty(key) && this.set(key, n[key]);
			}
			return;
		}
		var old_value = this.data[n];
		if (typeof v === 'object') {
			this.data[n] = new xCollection(v);
		} else {
			this.data[n] = v;
		}
		this.trigger('set', {name:n, value:v, old:old_value});
	},
	get(n) {
		return this.data[n];
	},
	toggle(n, v1, v2) {
		if (v2 === undefined) v2 = '';
		this.set(n, this.data[n] === v1 ? v2 : v1);
	},
	getAll() {
		const ret = {};
		for (const key in this.data) {
			if (this.data[key] instanceof xCollection) {
				ret[key] = this.data[key].getAll();
			} else {
				ret[key] = this.data[key];
			}
		}
		return ret;
	}
}
,xCollection.prototype);


c1.onElement('#qgCmsFrontend1 .-widgetHead', el=>{
	el.addEventListener('mousedown',e=>el.focus());
	el.addEventListener('keydown',e=>{
		if (e.keyCode === 13) {
			$(el).siblings('.-widgetHead').removeClass('-open');
			el.classList.toggle('-open');
		}
		if (e.keyCode === 38) {
			$(el).parent().children('.-widgetHead').removeClass('-open');
			$(el).prevAll('.-widgetHead').first().focus().trigger('mousedown');
		}
		if (e.keyCode === 40) {
			$(el).parent().children('.-widgetHead').removeClass('-open');
			$(el).nextAll('.-widgetHead').first().focus().trigger('mousedown');
		}
	});
	el.setAttribute('tabindex','0');
});
/* */
