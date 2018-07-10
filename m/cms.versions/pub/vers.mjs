/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
//import '../../cms.frontend.1/pub/js/contextMenu.mjs?qgUniq=6fea602';
import '../../cms.frontend.1/pub/js/frontend.mjs?qgUniq=7c4097f';

var css =
' '+
'#qgCms_vers { '+
'	display:flex; position:fixed; top:0; left:0; bottom:0; right:0; z-index:13; '+
'	background:#fff; '+
'} '+
'#qgCms_vers > .-control { '+
'	position:relative; '+
'	display:flex; '+
'	flex-flow:column; '+
'	border:1px solid var(--cms-light); '+
'	border-width:0 1px;' +
'	flex:0 1 auto; '+
'	min-width: 160px; '+
'} '+
'#qgCms_vers .-head { '+
'	-webkit-tap-highlight-color: rgba(0, 0, 0, 0); '+
'	background-color: rgb(60, 60, 60); '+
'	color: #fff; '+
'	cursor: pointer; '+
'	font-size: 16px; '+
'	padding: 12.8px 19.2px 11.2px 10px; '+
'} '+
'#qgCms_vers .-head:after { '+
'	font-family: "qg_cms"; '+
'	content: "\\e902"; '+
'	position: absolute; '+
'	right: .8em; '+
'	border-left: 1px solid; '+
'	padding-left: 10px; '+
'	transition: opacity .2s; '+
'} '+
'#qgCms_vers .-list { '+
'	flex:1 1 auto; '+
'	overflow:auto; '+
'	display:block; margin:0; padding:0; '+
'} '+
'#qgCms_vers .-list > li { '+
'	display:block; '+
'	background:#fff; '+
'	border-top:1px solid var(--cms-light); '+
'	padding:5px 10px; '+
'	position:relative; '+
'} '+
'#qgCms_vers .-list > li:hover { '+
'	color:var(--cms-color); '+
'} '+
'@keyframes spin { 100% { transform:rotate(360deg); } }'+
'#qgCms_vers .-list > li.-loading:after { '+
'	content: "|"; '+
'	position:absolute; '+
'	color:#000; '+
'	top:5px; right:10px; '+
'	animation:spin .5s linear infinite; '+
'} '+
'#qgCms_vers .-list > li.-active { '+
'	color:var(--cms-color); '+
'} '+
'#qgCms_vers .-list .-date { '+
'	font-size:1.2em; '+
'} '+
'#qgCms_vers .-list .-usr { '+
'	white-space:nowrap; '+
'	overflow:hidden; '+
'	text-overflow:ellipsis; '+
'	width:140px; '+
'} '+
'#qgCms_vers > .-preview { '+
'	flex:100 1 auto; '+
'	position:relative; '+
'	background:#000; '+
'	z-index:0; '+
'} '+
'#qgCms_vers > .-preview:before { '+
'	content:" "; '+
'	position:absolute; '+
'	top:0; left:0; right:0;'+
'	height:3px; '+
'	background-color:white; '+
'	transform:translateX(-100%); '+
'	transition:transform .5s linear; '+
'	will-change:transform; '+
'} '+
'#qgCms_vers > .-preview.-loading:before { '+
'	transform:translateX(0); '+
'} '+
'#qgCms_vers > .-preview > iframe { '+
'	position:absolute; '+
'	top:3px; left:0px; right:0px; bottom:0px; '+
'	width:100%; height:100%; '+
'	border:0; '+
'	transition:opacity .4s linear; '+
'	background-color:#fff; '+
'	will-change: opacity; '+
'}'+
'';
var body = document.body;
var htmlEl = document.documentElement;

var CmsVersViewer = function(){
	this.container = c1.dom.fragment(
		'<div id=qgCms_vers tabindex=-1 class="q1Rst qgCMS">'+
			'<div class=-preview></div>'+
			'<div class=-control>'+
				'<div class=-head>'+
					'Verlauf'+
				'</div>'+
				'<ul class=-list></ul>'+
			'</div>'+
			'<style id="x">'+css+'</style>'+
		'</div>'
	).firstChild;
	this.iframe1 = document.createElement('iframe');
	this.iframe2 = document.createElement('iframe');
	this.iframe1.sandbox = this.iframe2.sandbox = 'allow-same-origin allow-scripts';
	this.container.c1Find('.-preview').append(this.iframe1);
	this.container.c1Find('.-preview').append(this.iframe2);
	this.container.c1Find('.-control > .-head').addEventListener('click', ()=>{
		this.hide();
	});
	this.keydownListener = e=>{
		if (e.which === 27) this.hide();
		if (e.which !== 40 && e.which !== 38) return;
		let active = this.container.c1Find('.-list > li.-active');
		let next = active.nextElementSibling;
		let prev = active.previousElementSibling;
		if (e.which === 40 && next) this.load(next.getAttribute('v'));
		if (e.which === 38 && prev) this.load(prev.getAttribute('v'));
	};
	this.container.addEventListener('keydown',this.keydownListener);
};
CmsVersViewer.prototype = {
	show: function(pid){
		this.pid = pid;
		this.initialScrolltop = htmlEl.scrollTop || body.scrollTop;
		body.appendChild(this.container);
		if (window.innerWidth < 900) {
			this.container.mozRequestFullscreen    && this.container.mozRequestFullscreen();
			this.container.webkitRequestFullscreen && this.container.webkitRequestFullscreen();
			this.container.requestFullscreen       && this.container.requestFullscreen();
		}
		this.container.focus()
		this.container.c1ZTop();
		this.container.style.pointerEvents = 'none';
		setTimeout(()=> { this.container.style.pointerEvents = ''; } ,900)
		body.style.overflow = htmlEl.style.overflow = 'hidden';
		$fn('cms_vers::getForPage')(pid).then(rows=>{
			var activeRow = null;
			this.container.c1Find('.-list').innerHTML = '';
			rows.forEach(row=>{
				const li = c1.dom.fragment(
					'<li v='+row.vers+'>'+
						'<div class=-date>'+toDate(row.time)+'</div>'+
						'<div class=-usr>'+row.usr+'</div>'+
					'</li>'
				).firstChild;
				li.addEventListener('mouseover',e=>{
					if (activeRow === row.vers) return;
					activeRow = row.vers;
					this.load(row.vers);
				});
				this.container.c1Find('.-list').prepend(li);
			});
			rows.length && this.load(rows[rows.length-1].vers);
		});
	},
	hide: function(){
		this.container.remove();
		body.style.overflow = htmlEl.style.overflow = '';
		htmlEl.scrollTop = body.scrollTop = this.initialScrolltop;
	},
	load: function(vers){
		vers = parseInt(vers);
		var scrollTop = this.initialScrolltop;
		if (this.activeIframe) {
			var doc = this.activeIframe.contentWindow.document;
			if (doc.body) scrollTop = doc.documentElement.scrollTop || doc.body.scrollTop;
			this.activeIframe.style.opacity = 1;
		}
		this.activeIframe = this.activeIframe === this.iframe1 ? this.iframe2 : this.iframe1;

		var src = new URL(location.href);
		src.hash = '';
		src.searchParams.append('qgCmsVersLog',vers+1)
		src.searchParams.append('qgCmsVersPage',this.pid)
		src.searchParams.append('qgCmsNoFrontend','');
		this.activeIframe.src = src;

		this.activeIframe.style.opacity = 0;

		this.container.c1Find('.-preview').classList.add('-loading');

		for (let li of this.container.c1FindAll('.-list > li')) li.classList.remove('-active');
		const li = this.container.c1Find('.-list > li[v="'+vers+'"]');
		li.classList.add('-active','-loading');

		this.activeIframe.onload = ()=>{
			this.container.c1Find('.-preview').classList.remove('-loading');
			this.activeIframe.c1ZTop();
			this.activeIframe.style.opacity = 1;
			const doc = this.activeIframe.contentWindow.document;
			doc.addEventListener('keydown', this.keydownListener);
			const ready = ()=>{
				if (!doc.body) return;
				doc.documentElement.scrollTop = doc.body.scrollTop = scrollTop;
				let els = doc.querySelectorAll('.-pid'+this.pid), el;
				for (el of els) el.style.outline = '3px solid red';
				el && el.scrollIntoView();
			}
			document.readyState === 'complete' ? ready() : doc.addEventListener('DOMContentLoaded',ready);
			li.classList.remove('-loading');
		};
		this.trigger('before-load', {vers});
	}.c1Debounce({min:200, max:500}),
};
Object.assign(CmsVersViewer.prototype, c1.Eventer);

/* create Viewer */
var Viewer = new CmsVersViewer();

/* more */
var more = c1.dom.fragment(
'<div class=-more>'+
	'<button class=-compareActive>Mit Aktuell vergleichen</button>'+
	'<button class=-reactivate>Stand wiederherstellen</button>'+
	'<div class=-txt></div>'+
'</div>').firstChild;
var pointer = c1.dom.fragment('<i class=-pointer></i>').firstChild;
Viewer.container.c1Find('.-control').append(more);
Viewer.container.c1Find('.-control').append(pointer);
Viewer.on('before-load',function(e){
	var li = this.container.c1Find('.-list > li[v="'+e.vers+'"]');
	var pos = li.getBoundingClientRect();
	var top = pos.top;
	pointer.style.transform = 'translateY('+top+'px) rotate(45deg)';
	top = Math.min(top, window.innerHeight - 260);
	more.style.transform = 'translateY('+top+'px)';
	more.c1Find('.-txt').innerHTML = 'please wait...';
	$fn('cms_vers::logDetails')(e.vers).run(function(data){
		var date = new Date(data.time * 1000);
		var str = '';
		for (let msg of data.messages) {
			str += '<div style="padding:5px 0 5px 8px; margin:8px 0; border-left:1px solid #fff; background:#555">'+msg+'</div>';
		}
		str +=
		'<div class=-date>'+date.toLocaleDateString(locale, {weekday:'short', year:'numeric', month:'short', day:'numeric'}) + ' ' + date.toLocaleTimeString(locale,{hour: '2-digit', minute:'2-digit'}) +'</div>' +
		'<div class=-usr>'+data.usr+'</div>'+
		'<div class=-device title="'+data.user_agent+'">'+data.browser+' | '+data.ip+'</div>'+
		'';
		more.c1Find('.-txt').innerHTML = str;
	});
	more.c1Find('.-reactivate').onclick = function(){
		body.style.opacity = 0.3;
		$fn('cms_vers::publishCont')(Viewer.pid, {fromLog:e.vers+1}).run(function(){
			location.href = location.href.replace(/#.*$/,'');
		});
	};
	more.c1Find('.-compareActive').onclick = async function(){
		await c1.import(sysURL+'cms.versions/pub/comparer.mjs?qgUniq=b002660');
		CmsVersComparer.compare(Viewer.pid, {
			fromLog: e.vers+1,
			fromText: li.c1Find('.-date').innerHTML,
			toText: 'aktuell',
			accept(){ more.c1Find('.-reactivate').onclick(); },
			acceptText:'Stand wiederherstellen',
		});
	};
});
more.addEventListener('mouseover',function(e){
	var mark = e.target.getAttribute('mark');
	if (!mark) return;
	var all = Viewer.activeIframe.contentWindow.document.querySelectorAll(mark);
	for (let el of all) {
		el.style.outline = '10px dotted red';
		el.scrollIntoView({
		    behavior: 'smooth',
		    block:    'start',
		});
		Viewer.activeIframe.contentWindow.scrollBy(-100,-100)
	}
});

css =
'#qgCms_vers .-more { '+
'	position:absolute; '+
'	right:100%; '+
'	top:0; '+
'	padding:20px; '+
'	background:var(--cms-dark); '+
'	color:#fff; '+
'	line-height:1.8; '+
'	min-height:80px; '+
'	min-width:220px; '+
'	z-index:4; '+
'	opacity:0; '+
'	visibility:hidden; '+
'	transition:all .3s; '+
'	transition-property:opacity, visibility; '+
'	will-change:tramsform, opacity; '+
'} '+
'#qgCms_vers .-pointer { '+
'	position: absolute; '+
'	top: 15px; '+
'	left:-6px; '+
'	width: 12px; '+
'	height: 12px; '+
'	background:var(--cms-dark); '+
'	transform: rotate(45deg); '+
'	opacity:0; '+
'	transition:all .3s; '+
'	transition-property:opacity, visibility; '+
'	will-change:transform, opacity; '+
'} '+
'#qgCms_vers .-control:hover > .-more, '+
'#qgCms_vers .-control:hover > .-pointer { '+
'	opacity:1; '+
'	visibility:visible; '+
'}' +
'#qgCms_vers .-control > .-more > button { '+
'	margin-bottom:8px; '+
'	width:100%; '+
'}' +
'#qgCms_vers .-control > .-more > button:hover { '+
'	color:var(--cms-dark); '+
'	background:#fff; '+
'}' +
'#qgCms_vers .-control > .-more .-txt { '+
'	max-height:170px; '+
'	overflow:auto; '+
'}' +
'#qgCms_vers .-control > .-more .-date:before { '+
'	font-family: qg_cms; ' +
'	padding-right: 7px; ' +
'	content: "\\e901"; ' +
'}' +
'#qgCms_vers .-control > .-more .-usr:before { '+
'	font-family: qg_cms; ' +
'	padding-right: 7px; ' +
'	content: "\\e600"; ' +
'}' +
'#qgCms_vers .-control > .-more .-device:before { '+
'	font-family: qg_cms; ' +
'	padding-right: 7px; ' +
'	content: "\\e601"; ' +
'}' +
'';
Viewer.container.append(c1.dom.fragment('<style>'+css+'</style>'));

/* ui */
document.addEventListener('keydown',e=>{
	if (e.target.isContentEditable || e.target.form !== undefined) return;
	if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
	if (e.which == 72) { // H
		Viewer.show(Page);
		e.preventDefault();
	}
});

cms.contextMenueContent.addItem('Verlauf', {
	icon: sysURL+'cms.frontend.1/pub/img/contextmenu/undo.png',
	selector: '.qgCmsCont, #qgCmsContPosMenu',
	onshow(e) {
		this.activePid = cms.contPos.active.pid;
		this.disabled = !cms.contPos.active.el.classList.contains('-e');
	},
	onclick() {
		Viewer.show(this.activePid);
	}
});

// frontentd1 integration
const sidebarItem = c1.dom.fragment(
'<div class=-item itemid=history>'+
	'<div class=-title>'+
	'	<div class=-text>Verlauf</div>'+
	'</div>'+
	//'<style> #qgCmsFrontend1 [itemid=history] > .-title:after { content:"\\e903"; }</style>'+
'</div>').firstChild;
htmlEl.c1Find('#qgCmsFrontend1 > .-sidebar > [itemid="more"]').after(sidebarItem)
sidebarItem.addEventListener('mousedown', e=>{
	e.stopPropagation();
	Viewer.show(Page)
});


var locale = document.documentElement.getAttribute('lang') || window.navigator.userLanguage || window.navigator.language;
function toDate(timestamp) {
	var now = new Date();
	var date = new Date(timestamp*1000);
	if (date > now) now = date;

	var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };

	var sameYear   = date.getFullYear() == now.getFullYear();
	var sameMonth  = sameYear  && date.getMonth()   == now.getMonth();
	var sameDay    = sameMonth && date.getDate()    == now.getDate();
	var yesterDay  = sameMonth && date.getDate() +1 == now.getDate();
	var sameHour   = sameDay   && date.getHours()   == now.getHours();
	var sameMinute = sameHour  && date.getMinutes() == now.getMinutes();

	if (sameMinute) {
		var seconds = Math.floor((now - date) / 1000);
		return seconds + ' Sekunden';
	}
	if (sameHour) return Math.floor((now - date) / 1000 / 60)+1  + ' Minuten';
	if (sameDay) return 'heute '+date.toLocaleTimeString(locale,{hour: '2-digit', minute:'2-digit'});
	if (yesterDay) return 'gestern '+date.toLocaleTimeString(locale,{hour: '2-digit', minute:'2-digit'});
	if (sameMonth) { } //delete options.month; ?
	if (sameYear) delete options.year;
	return date.toLocaleDateString(locale,options) + ' ' + date.toLocaleTimeString(locale,{hour: '2-digit', minute:'2-digit'});
}
