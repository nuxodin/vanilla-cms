/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
// not used! experiment!
!function(){ 'use strict'

var root = document.scripts[document.scripts.length-1].src+'/../';

function fillProp(obj, prop, resource){
    Object.defineProperty(obj, prop, {
        configurable:true,
        get:function(){
            delete obj[prop];
            var request = new XMLHttpRequest();
            request.open('GET', resource, false);
            request.send(null);
            if (request.status === 200) {
                var scriptEl = d.createElement('script');
                scriptEl.text = request.responseText;
                document.head.appendChild(scriptEl);
            }
            return obj[prop];
        },
        set:function(value){
            delete obj[prop];
            obj[prop] = value;
        }
    });
}

window.magicFill = function(obj, props, resource){
    var i=0; prop;
    while (prop = props[i++]) {
        if (prop in obj) continue;
        resource = resource ? root+resource : 'magicFill/'+prop+'.js';
        fillProp(obj, prop, resource);
    }
};

magicFill(window,['fetch']);
magicFill(window,'Promise');
magicFill(HTMLElement.prototype,['closest','after','before','append','prepend','remove'], 'HTMLElement.prototype.dom4.js');


}();
