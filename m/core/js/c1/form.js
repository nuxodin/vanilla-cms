'use strict';
c1.form = {
    serializeObject: function(element) {
        var els = element.elements || element.querySelectorAll('input, select, textarea');
        els = Array.from(els);
        els.push(els);
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
    }
}
