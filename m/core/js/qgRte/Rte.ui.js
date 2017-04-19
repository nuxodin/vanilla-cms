
Rte.ui = {
	init: function() {
		var my = this;
		my.div = document.createElement('div');
		my.div.id = 'qgRteToolbar';
		my.div.addEventListener('mousedown',  function(e){ e.stopPropagation(); });
		my.div.addEventListener('touchstart', function(e){ e.stopPropagation(); });

		Rte.addUiElement(my.div);

		my.mainContainer = document.createElement('div');
		my.mainContainer.className = '-main';
		my.div.appendChild(my.mainContainer);

		my.moreContainer = document.createElement('div');
		my.moreContainer.className = '-more q1Rst';
		my.div.appendChild(my.moreContainer);

		Rte.on('activate', function() {

			var config = Rte.ui.config['rteDef']; // todo

			my.activeItems = {};
			var appendTo = my.mainContainer;
			var addItem = function(i,n) {
				if (my.items[n]) {
					my.activeItems[n] = my.items[n];
					appendTo.appendChild(my.items[n].el);
				}
			};
			$.each(config.main, addItem);
			appendTo = my.moreContainer;
			$.each(config.more, addItem);

			if (my.activeItems) {
				document.body.appendChild(my.div);
				my.div.style.display = 'none';
				my.div.c1ZTop();
				setTimeout(function() {
					$(my.div).show();
				},90);
				document.addEventListener('keydown', shortcutListener, false);
			}

		});
		Rte.on('deactivate', function() {
			document.removeEventListener('keydown', shortcutListener, false);
			setTimeout(function() { // need timeout because inputs inside have to blur first (ff, no chrome)
				!Rte.active && my.div.parentNode && document.body.removeChild(my.div);
			},1);
		});
		Rte.on('elementchange', function() {
			//if (!Rte.element) return; needed?
			$.each(my.activeItems, function(name,item) {
				if (!item.enable || item.enable(Rte.element)) {
					item.enabled = true;
					$(item.el).attr('hidden',null);
					if (item.check) {
						var act = item.check( Rte.element )?'add':'remove';
						$(item.el)[act+'Class']('active');
					}
				} else {
					item.enabled = false;
					$(item.el).attr('hidden',true);
				}
			});
		});
		var shortcutListener = function(e) {
			if (e.ctrlKey && !e.metaKey && !e.shiftkey && !e.altkey) {
				var char = String.fromCharCode(e.which).toLowerCase();
				$.each(my.activeItems,function(i,item) {
					if (item.enabled && item.shortcut === char) {
						var ev = document.createEvent('Events');
						ev.initEvent('mousedown', true, false);
		                item.el.dispatchEvent(ev);
		                e.preventDefault();
					}
				});
			}
		};
		/* ui hover */
		var moreTimeout = null;
		$(my.div).on({
			mouseenter:function() {
				clearTimeout(moreTimeout);
				moreTimeout = setTimeout(function() {
					Rte.ui.div.querySelector('.-more').style.display = 'flex';
				},300);
				Rte.ui.mouseover = 1;
			},
			mouseleave:function() {
				clearTimeout(moreTimeout);
				moreTimeout = setTimeout(function() {
					Rte.ui.div.querySelector('.-more').style.display = 'none';
				},300);
				Rte.ui.mouseover = 0;
			}
		});
	}
	,setItem: function(name, opt) {
		if (!opt.el) {
			opt.el = document.createElement('span');
			opt.el.className = '-item -'+name;
		}

		if (opt.cmd) {
			if (!opt.click && opt.click != false) { opt.click = function() { qgExecCommand(opt.cmd,false,false); }; }
			if (!opt.check && opt.check != false) { opt.check = function() { return qgQueryCommandState(opt.cmd); }; }
		}
		var enable = opt.enable;
		if (enable && enable.toLowerCase) {
			opt.enable = function(el) { return $(el).is(enable); };
		}

		opt.click && opt.el.addEventListener('mousedown',function(e) {
			Rte.manipulate(function() { // todo: manipulate schon hier??
				opt.click(e);
			});
		},false);

		opt.shortcut && opt.el.setAttribute('title','ctrl+'+opt.shortcut);

		this.items[name] = opt;
		return opt.el;
	},
	setSelect: function(name, opt) {
		var timeout = null;
		var el =  $('<div class="-item -select">').on({
			mousedown: function() { opts.show(); },
			mouseover: function() { clearTimeout(timeout); },
			mouseout:  function() { timeout = setTimeout(function() {opts.hide();},300); }
		});
		$('<div class=-state>').appendTo(el).html(name);
		var opts = $('<div class=-options>').appendTo(el);
		opt.el = el[0];
		this.setItem(name,opt);
		return opts;
	},
	items:{}
};

Rte.on('selectionchange', function() {
	if (!Rte.active) return;
	if (Rte.ui.mouseover) return;
	var pos = qgSelection.rect();
	if (!pos) {
		if (Rte.element) {
			pos = Rte.element.getBoundingClientRect();
		} else return;
	}
	var distance = getSelection().isCollapsed ? 60 : 20;
	var top  = pos.top  + pos.height + 10 + distance;
	var left = pos.left + distance;
	if (top > $(window).height() - 100) {
		top  = pos.top - 110 - distance;
	}
	left = Math.min(left, $(window).width()-250);
	top  = Math.max(top, 0);
	left = Math.max(left, 0);
	var Bpos = document.body.getBoundingClientRect();
	Rte.ui.div.style.top  = top  - Bpos.top  +'px';
	Rte.ui.div.style.left = left - Bpos.left +'px';
});

Rte.on('ready', function() {
	Rte.ui.init();
});
