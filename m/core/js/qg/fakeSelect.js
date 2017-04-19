
!function(){
	var doc = document;
	var dialog = doc.createElement('div');
	dialog.classList.add('q1Rst');
	dialog.classList.add('c1Dialog');
	dialog.classList.add('c1Select');
	dialog.addEventListener('mousedown', function(e){
		e.preventDefault();
		e.stopPropagation(); // prevent closing cms-panel
	});
	dialog.addEventListener('touchstart', function(e){
		e.stopPropagation(); // prevent closing cms-panel
	});
	dialog.addEventListener('wheel', function(e){
		if (e.wheelDelta > 0 && this.scrollTop === 0) {
			e.preventDefault();
		} else if (e.wheelDelta < 0 && this.scrollHeight < this.scrollTop + this.offsetHeight) {
			e.preventDefault();
		}
	});

	window.c1Combobox = function(input){
		if (input.c1Combobox) return input.c1Combobox;
		input.c1Combobox = this;
		input.setAttribute('autocomplete','off');
		input.addEventListener('input', this);
		input.addEventListener('keydown', this);
		input.addEventListener('mouseover', this);
		input.addEventListener('mousedown', this);
		input.addEventListener('blur', this);
		this.input = input;
	};
	c1Combobox.prototype = {
		showDialog: function(){
			doc.body.appendChild(dialog);
			dialog.c1ZTop();
			dialog.style.minWidth = this.input.offsetWidth + 'px';
			this.positionize();
		},
		positionize: function(){
			c1.c1Use('Placer',Placer=>{
				var Placer = new Placer(dialog);
				Placer.follow(this.input);
			});
			// var rect = this.input.getBoundingClientRect();
			// var Brect = doc.documentElement.getBoundingClientRect();
			// // todo include margins?
			// var height = window.innerHeight - 10 - rect.bottom;
			// if (height > 200) {
			// 	dialog.style.top  = rect.bottom - Brect.top  + 'px';
			// 	dialog.style.left = rect.left   - Brect.left + 'px';
			// } else {
			// 	dialog.style.top  = rect.top  - Brect.top - dialog.offsetHeight + 'px';
			// 	dialog.style.left = rect.left - Brect.left + 'px';
			// 	height = rect.top - 10;
			// }
			// dialog.style.maxHeight = height+'px';
		},
		hideDialog: function(){
			//dialog.offsetWidth && doc.body.removeChild(dialog); // zzz
			dialog.offsetWidth && dialog.remove();
		},
		initOptions: function(){
			this.mark(dialog.firstElementChild);
//			this.positionize();
		},
		searchOptions: function(){
			// searchOptions not implemented
		},
		searchOptionsDebounced:function(){
			this.searchOptions();
		}.c1Debounce(150),
		setOptions: function(array){
			dialog.innerHTML = '';
			var el, i=0, item;
			for (;item = array[i++];) {
				el = doc.createElement('div');
				el.innerHTML = item.html;
				el.setAttribute('value', item.value);
				el.setAttribute('text', item.text);
				dialog.appendChild(el);
			}
			this.initOptions();
		},
		mark: function(el){
			this.marked && this.marked.classList.remove('-marked');
			this.marked = el;
			el && el.classList.add('-marked');
		},
		select: function(el){
			if (!el) return;
			this.mark(el);
			this.selected = el;
			this.input.value = el.getAttribute('value');
			//this.input.dispatchEvent(new Event('input'));
			var e = new Event('change',{bubbles:true});
			this.input.dispatchEvent(e);
			if (el.offsetTop + el.offsetHeight > dialog.scrollTop + dialog.offsetHeight - 10) {
				dialog.scrollTop = el.offsetTop + el.offsetHeight - dialog.offsetHeight + 4;
			}
			if (el.offsetTop < dialog.scrollTop + 10) {
				dialog.scrollTop = el.offsetTop - 2;
			}
		},
		handleEvent: function(e){
			if (!this['on'+e.type]) return;
			this['on'+e.type](e);
		},
		onfocus: function(){
			var self = this;
			this.input.select();
			setTimeout(function(){ // ie11 has blur before focusin
				dialog.innerHTML = '';
				self.lastValue = self.input.value;
				self.showDialog();
				self.searchOptions();
				dialog.onmouseover = function(e){ // neu
					var el = e.target.closest('[value]');
					self.mark(el);
				};
				//dialog.onmousedown = function(e){
				dialog.onmouseup = function(e){
					var el = e.target.closest('[value]');
					self.select(el);
					self.hideDialog(); // neu
					self.input.dispatchEvent(new CustomEvent('select_by_pointer')); // new
				};
				dialog.c1Combobox = self;

				// var positionize = function(){ // make local function to remove as Eventlistener
				// 	self.positionize();
				// };
				// var int = setInterval(function(){
				// 	if (!dialog.offsetWidth || dialog.c1Combobox !== self){
				// 		clearInterval(int);
				// 		window.removeEventListener('scroll',positionize);
				// 		window.removeEventListener('resize',positionize);
				// 	} else {
				// 		positionize();
				// 	}
				// }, 100);
				// window.addEventListener('scroll',positionize);
				// window.addEventListener('resize',positionize);
			});
		},
		onblur: function(){
			dialog.c1Combobox = false;
			this.hideDialog();
		},
		oninput: function(){
			this.showDialog();
			this.searchOptionsDebounced();
		},
		onmousedown: function(){
			//dialog.offsetWidth ? this.hideDialog() : this.showDialog(); // firefox freezing input!!
		},
		onkeydown: function(e) {
			switch (e.which) {
			case 40: // down
				this.select(this.marked.nextElementSibling);
				return;
			case 38: // up
				this.select(this.marked.previousElementSibling);
				return;
			case 13: // enter
				this.select(this.marked);
				this.hideDialog();
				return;
			case 27: // esc
				this.input.value = this.lastValue;
				this.hideDialog();
				return;
			}
		}
	};

	var css =
	'.c1Select { \
		position: fixed; \
		font-size: 13px; \
		background-color:#fff; \
		border: 1px solid #aaa; \
		overflow: auto; \
		left: 0px; \
		white-space:nowrap; \
		line-height:1.2; \
		box-shadow: 0 0 8px rgba(0,0,0,.3); \
		min-height; 10px; \
		max-height: 60vh; \
		background: #fff; \
		box-sizing:border-box; \
	} \
	.c1Select > div { \
		padding:3px 4px; \
		margin:2px; \
	} \
	.c1Select > div:after { \
		content:\'\'; \
		display:block; \
		clear:both; \
	} \
	.c1Select > .-marked { \
		background-color:#0099ff; \
		color:#fff; \
	} \
	.c1Select::-webkit-scrollbar { width: 6px; height: 6px; } \
	.c1Select::-webkit-scrollbar-track { background: rgba(0, 0, 0, .05); } \
	.c1Select::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, .25); } \
	';
	var sEl = document.createElement('style');
	sEl.appendChild(document.createTextNode(css));
	document.head.append(sEl);
}();
