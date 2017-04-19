qgTableHandles = function() {
	var remImgData = '\'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" stroke="#ee3c3c" stroke-width="14"><line x1="4" y1="4" x2="60" y2="60"/><line x1="4" y1="60" x2="60" y2="4"/></svg>')+'\'';
	var addImgData = '\'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" stroke="#444" stroke-width="14"><line x1="0" y1="32" x2="64" y2="32"/><line x1="32" y1="0" x2="32" y2="64"/></svg>')+'\'';

	this.els = {
		rowRem      : $('<a>').css('background-image','url('+remImgData+')'),
		rowAddAfter : $('<a>').css('background-image','url('+addImgData+')'),
		colAddRight : $('<a>').css('background-image','url('+addImgData+')'),
		colRem    	: $('<a>').css('background-image','url('+remImgData+')')
	};
	this.handles = $(this.els.rowRem).add(this.els.rowAddAfter).add(this.els.colAddRight).add(this.els.colRem)
	.css({
		position: 'absolute',
		border: '1px solid #bbb',
		borderRadius: '50%',
		backgroundColor: '#fff',
		width: 20,
		height: 20,
		backgroundPosition: '50%',
		backgroundSize: '50%',
		backgroundRepeat: 'no-repeat',
		color: '#000',
		cursor:'pointer'
	}).on('mousedown', function(e) {
		e.preventDefault();
	});
};
qgTableHandles.prototype = {
	showTd: function(td) {
		this.active = td;
		this.handles.each(function(){
			document.body.append(this);
			this.c1ZTop();
		});
		this.positionize(td);
		clearInterval(this.checkIntr);
		this.checkIntr = setInterval(this.handleEvent.bind(this), 100);
		document.addEventListener('keydown', this);
	},
	hide: function() {
		this.handles.detach();
	},
	positionize: function(td) {
		var tr = td.parentNode;
		var Cpos  = $(td).offset();
		var Tpos  = $(tr).closest('table').offset();
		var Bpos  = $(document.body).offset();
		Cpos = {
			top:  Cpos.top - Bpos.top,
			left: Cpos.left - Bpos.left,
		};
		Tpos = {
			top:  Tpos.top - Bpos.top,
			left: Tpos.left - Bpos.left,
		};
		this.els.rowRem.css(      { top: Cpos.top + (tr.offsetHeight / 2) - 11, left: Tpos.left - 25 });
		this.els.rowAddAfter.css( { top: Cpos.top + tr.offsetHeight - 4,        left: Tpos.left - 25 });
		this.els.colRem.css(      { top: Tpos.top - 25,                         left: Cpos.left + (td.offsetWidth / 2) - 8});
		this.els.colAddRight.css( { top: Tpos.top - 25,                         left: Cpos.left + td.offsetWidth - 2 });
	},
	handleEvent: function() {
		var my = this;
		setTimeout(function(){
			if (my.handles[0].parentNode) {
				my.positionize(my.active);
			} else {
				clearInterval(my.checkIntr);
				document.removeEventListener('keydown', my);
			}
		});
	}
};
