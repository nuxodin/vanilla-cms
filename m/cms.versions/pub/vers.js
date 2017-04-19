window.$ && $(function(){
	'use strict';

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

	var CmsVersViewer = function(){
		var self = this;
		this.$container = $(
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
		);
		this.iframe1 = document.createElement('iframe');
		this.iframe2 = document.createElement('iframe');
		this.iframe1.sandbox = this.iframe2.sandbox = 'allow-same-origin allow-scripts';
		this.$container.find('.-preview').append(this.iframe1).append(this.iframe2);
		this.$container.find('.-control > .-head').on('click', function(){
			self.hide();
		});
		this.keydownListener = function(e){
			if (e.which === 27) self.hide();
			if (e.which === 40) self.load(self.$container.find('.-list > li.-active').next().attr('v'));
			if (e.which === 38) self.load(self.$container.find('.-list > li.-active').prev().attr('v'));
		};
		this.$container.on('keydown',this.keydownListener);
	};

	var body = document.body;
	var htmlEl = document.documentElement;

	CmsVersViewer.prototype = {
		show: function(pid){
			var self = this;
			this.pid = pid;

			this.initialScrolltop = htmlEl.scrollTop || body.scrollTop;

			body.appendChild(this.$container[0]);

			if (window.innerWidth < 1000) {
				this.$container[0].msRequestFullscreen     && this.$container[0].msRequestFullscreen();
				this.$container[0].mozRequestFullscreen    && this.$container[0].mozRequestFullscreen();
				this.$container[0].webkitRequestFullscreen && this.$container[0].webkitRequestFullscreen();
				this.$container[0].requestFullscreen       && this.$container[0].requestFullscreen();
			}

			this.$container.focus()[0].c1ZTop();

			this.$container.css('pointer-events','none');
			setTimeout(function(){ self.$container.css('pointer-events',''); },900)

			body.style.overflow = htmlEl.style.overflow = 'hidden';

			$fn('cms_vers::getForPage')(pid).then(function(rows){
				var activeRow = null;
				self.$container.find('.-list').html('');
				rows.forEach(function(row,i){
					$('<li v='+row.vers+'>'+
							'<div class=-date>'+toDate(row.time)+'</div>'+
							'<div class=-usr>'+row.usr+'</div>'+
					  '</li>').
					on('mouseover',function(){
						if (activeRow === row.vers) return;
						activeRow = row.vers;
						self.load(row.vers);
					}).prependTo(self.$container.find('.-list'));
				});
				rows.length && self.load(rows[rows.length-1].vers);
			});
		},
		hide: function(){
			this.$container.detach();
			body.style.overflow = htmlEl.style.overflow = '';
			htmlEl.scrollTop = body.scrollTop = this.initialScrolltop;
		},
		load: function(vers){
			vers = parseInt(vers);
			var self = this;
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
			//this.activeIframe.src = location.href+'&qgCmsVersLog='+(vers+1)+'&qgCmsVersPage='+this.pid+'&qgCmsNoFrontend';

			this.activeIframe.style.opacity = 0;

			self.$container.find('.-preview').addClass('-loading');
			var $li = this.$container.find('.-list > li[v='+vers+']');
			$li.addClass('-active -loading').siblings().removeClass('-active');

			this.activeIframe.onload = function() {
				self.$container.find('.-preview').removeClass('-loading');

				self.activeIframe.c1ZTop();
				self.activeIframe.style.opacity = 1;

				var doc = self.activeIframe.contentWindow.document;
				doc.addEventListener('keydown', self.keydownListener);

				$(doc).ready(function(){
					if (doc.body) {
						doc.documentElement.scrollTop = doc.body.scrollTop = scrollTop;
						var els = doc.querySelectorAll('.-pid'+self.pid);
						for (var i=0,el;el=els[i++];) el.style.outline = '3px solid red';
						el && el.scrollIntoView();
					}
				});
				$li.removeClass('-loading');
			};
			this.trigger('before-load', {vers:vers});
		}.c1Debounce({min:200, max:500}),
	};
	Object.assign(CmsVersViewer.prototype, c1.Eventer);

	/* create Viewer */
	var Viewer = new CmsVersViewer();


	/* more */
	var more = $(
	'<div class=-more>'+
		'<button class=-reactivate>Stand wiederherstellen</button>'+
		'<div class=-txt></div>'+
		'<button class=-compareActive>Mit Aktuell vergleichen</button>'+
		'<button class=-comparePreviews>Änderung sehen</button>'+
	'</div>');
	var pointer = $('<i class=-pointer></i>');
	Viewer.$container.find('.-control').append(more, pointer);
	Viewer.on('before-load',function(e){
		var $li = this.$container.find('.-list > li[v='+e.vers+']');
		var pos = $li[0].getBoundingClientRect();

		var top = pos.top;
		pointer.css({ transform:'translateY('+top+'px) rotate(45deg)'});
		top = Math.min(top, $(window).height() - 260);
		more.css({ transform:'translateY('+top+'px)'});
		more.find('.-txt').html('please wait...');

		$fn('cms_vers::logDetails')(e.vers).run(function(data){
			var date = new Date(data.time * 1000);
			var str = '';
			data.messages.forEach(function(msg){
				str += '<div style="padding:5px 0 5px 8px; margin:8px 0; border-left:1px solid #fff; background:#555">'+msg+'</div>';
			});
			str +=
			'<div class=-date>'+date.toLocaleDateString(locale, {weekday:'short', year:'numeric', month:'short', day:'numeric'}) + ' ' + date.toLocaleTimeString(locale,{hour: '2-digit', minute:'2-digit'}) +'</div>' +
			'<div class=-usr>'+data.usr+'</div>'+
			'<div class=-device title="'+data.user_agent+'">'+data.browser+' | '+data.ip+'</div>'+
			'';
			more.find('.-txt')[0].innerHTML = str;
		});
		more.find('.-reactivate')[0].onclick = function(){
			body.style.opacity = 0.3;
			$fn('cms_vers::publishCont')(Viewer.pid, {fromLog:e.vers+1}).run(function(){
				location.href = location.href.replace(/#.*$/,'');
			});
		};
		more.find('.-compareActive')[0].onclick = function(){
			CmsVersComparer.compare(Viewer.pid, {
				fromLog: e.vers+1,
				fromText: $li.find('.-date').html(),
				toText: 'aktuell',
				accept(){ more.find('.-reactivate')[0].onclick(); },
				acceptText:'Stand wiederherstellen',
			});
		};
		more.find('.-comparePreviews')[0].onclick = function(){
			CmsVersComparer.compare(Viewer.pid, {
				fromLog:e.vers+1,
				fromText:$li.find('.-date').html(),
				toLog:e.vers,
				toText:$li.next().find('.-date').html()
			});
		};
	});
	more.on('mouseover',function(e){
		var mark = e.target.getAttribute('mark');
		if (mark) {
			var all = Viewer.activeIframe.contentWindow.document.querySelectorAll(mark);
			for (var i=0, el; el = all[i++];) {
				el.style.outline = '10px dotted red';
				el.scrollIntoView({
				    behavior: 'smooth',
				    block:    'start',
				});
				Viewer.activeIframe.contentWindow.scrollBy(-100,-100)
			}
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
	Viewer.$container.append('<style>'+css+'</style>');

	/* ui */
	$('#cmsContentWindow [show=lastChanges]').on('click', e=>{
		Viewer.show(Page);
		e.preventDefault();
	});

	$(document).on('keydown', function(e){
		if (e.target.isContentEditable || e.target.form !== undefined) return;
		if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
		if (e.which == 72) {
			Viewer.show(Page);
			e.preventDefault();
		}
	});

	cms.contextMenueContent.addItem('Verlauf', {
		icon: sysURL+'cms.frontend.1/pub/img/contextmenu/undo.png',
		selector: '.qgCmsCont',
		onshow(e) {
			this.activePid = cms.contPos.active.pid;
			this.disabled = !$(e.currentTarget).hasClass('-e'); // todo!!
		},
		onclick() {
			Viewer.show(this.activePid);
		}
	});

	// frontentd1 integration
	$('<div class=-item itemid=history>'+
		'<div class=-title>'+
		'	<div class=-text>Verlauf</div>'+
		'</div>'+
		//'<style> #qgCmsFrontend1 [itemid=history] > .-title:after { content:"\\e903"; }</style>'+
	'</div>')
	.insertAfter('#qgCmsFrontend1 > .-sidebar > [itemid="more"]')
	.on('mousedown', e=>{
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

});
