!function(){
'use strict';

document.addEventListener('focusin', function(e){
	var input = e.target;
	if (input.tagName !== 'INPUT') return;
	if (input.getAttribute('type') !== 'qg-date') return;
	if (input.qgDateInput) return;
	new qgDateInput(input).onfocus();
}, false);

class qgDateInput {
	constructor(input) {
		if (input.qgDateInput) return input.qgDateInput;
		input.qgDateInput = this;
		this.opt = {
			format: input.getAttribute('qg-format') || '%d.%m.%Y %H:%M',
		};
		this.input = input;
		this.date = new Date();
		this.date.fromStr(input.value, this.opt.format);
		this.hidden = document.createElement('input');
		this.hidden.type = 'hidden';
		this.hidden.name = input.name;
		this.hidden.value = input.value ? Math.ceil(this.date.getTime()/1000) : 0;
		input.after(this.hidden);
		input.addEventListener('focus',this);
		input.addEventListener('blur',this);
		input.addEventListener('keydown',this);
		input.addEventListener('keyup',this);
		input.name = '';

		this.calDiv = c1.dom.fragment('<div class="qgDateInput q1Rst">').firstChild;
		this.calDiv.addEventListener('mousedown',function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		var my = this;
		this.cal = new qgCalendar($(this.calDiv), this.date);
		this.cal.onChange = function() {
			my.input.value = my.date.toStr(my.opt.format);
			my.hidden.value = Math.ceil(my.date.getTime()/1000); // onbeforblur?
		};

	}
	show(){
		document.body.append(this.calDiv);
		c1.c1Use('Placer',P=>{
			var Placer = new c1.Placer(this.calDiv);
			Placer.follow(this.input);
		})
		this.calDiv.c1ZTop();
	}
	hide(){
		this.calDiv.remove();
	}
	handleEvent(e){
		if (!this['on'+e.type]) return;
		this['on'+e.type](e);
	}
	onfocus() {
		this.hidden.value = Math.ceil(this.date.getTime()/1000); // onbeforblur?
		this.cal.showMonth(this.date.getY(), this.date.getM());
		this.show();
	}
	onblur() {
		this.hide();
	}
	onkeydown(e) {
		this.lastValue = this.input.value;
		if (e.which === 40 || e.which === 38) {
			var posStart = e.target.selectionStart,
				posEnd = e.target.selectionEnd;
			var v = stringPosGetValue(e.target.value, posStart);
			var nv = v;
			if (v.match(/^[0-9-]+$/)) {
				var add = e.control ? 5 : 1;
				nv = parseInt(v) + ( e.which === 40  ? -add : add );
				e.ctrlKey && ( nv = Math.ceil(nv/5)*5 );
				nv = nv.toString();
				while (v.length > nv.length) nv = '0'+nv;
			} else if (v === 'pm') {
				nv = 'am';
			} else if (v === 'am') {
				nv = 'pm';
			}
			nv = nv.toString();
			posStart = posStart - (v.length - nv.length);
			e.target.value = stringPosSetValue(e.target.value, posStart, nv);
			this.input.setSelectionRange(posStart,posEnd);
			e.preventDefault();
		}
	}
	onkeyup(){
		setTimeout(()=>{
			this.hidden.value = Math.ceil(this.date.getTime()/1000); // neu
		});
		if (this.lastValue !== this.input.value) {
			this.date.fromStr(this.input.value, this.opt.format);
			this.cal.showMonth(this.date.getY(), this.date.getM());
		}
	}
}

function stringPosGetValue(str, pos) {
	var v;
    str.replace(/[0-9a-zA-Z-]+/g, function(m, apos) {
		if (pos >= apos && pos <= apos+m.length) {
            v = m;
        }
    });
	return v;
}
function stringPosSetValue(str, pos, v) {
    return str.replace(/[0-9a-zA-Z-]+/g, function(m, apos) {
        if (pos >= apos && pos <= apos+m.length) {
            return m = v;
        }
        return m;
    });
}

}();
