'use strict';
c1.form = {
    serializeObject: function(element) {
        var els = element.elements || element.querySelectorAll('input, select, textarea');
        els = Array.prototype.slice.call(els);
        els.push(els); // zzz needed?
        var object = Object.create(null);
        els.forEach(function(el){
            var value = c1.form.elementValue(el);
            if (value === void 0) return;
            var name = el.name;
            if (!name) return;
            var matches = name.match(/(^[^\[]+|\[[^\]]*\])/g);
            var active = object;
            for (var i=0, match; match=matches[i++];) { // walk path (item[xy][])
                if (i>1) match = match.replace(/(^\[|\]$)/g,'');
                if (matches.length === i) {
                    if (Array.isArray(active)) active.push(value);
                    else active[match] = value;
                } else if (!active[match]) {
                    active[match] = matches[i] === '[]' ? [] : Object.create(null);
                }
                active = active[match];
            }
        });
        return object;
    },
    elementValue: function(el){
        //if (el.tagName === 'SELECT') return el.options[el.selectedIndex].value;
        if (el.type === 'checkbox') return el.checked ? el.value : false;
        if (el.type === 'radio') {
            var form = el.form;
            var radios = document.getElementsByName(el.name);
            for (var i = 0, radio; radio = radios[i++];) {
                if (form !== radio.form) continue;
                if (!radio.checked) continue;
                return radio.value;
            }
            return;
        }
        return el.value;
    },
    fileDialog: function(options){
        if (!options) options = {};
        options = Object.assign({
            multiple: true,
            accept: '',
        },options);
        var inp = document.createElement('input');
        inp.type = 'file';
        inp.multiple = options.multiple;
        inp.accept   = options.accept;
        var P = new Promise(function(resolve, reject){
			setTimeout(function(){ // bug, change sometimes not fired without this timeout (chrome tested)
				inp.onchange = function(){
					resolve(inp.files);
					inp.onchange = null;
				}
			    inp.click();
			},50)
        });
        return P;
    }
}
