/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(){ 'use strict';

c1.translations = async function(c1Obj, available){
    var lang = c1.translations.lang;
    if (!available) available = [lang];
    if (!available.contains(lang)) lang = available[0];
    var translations = await c1Obj.c1Use('text_'+lang+'js');
    return function(text){
        if (translations[text] === undefined) {
            console.warn('translation needed: '+lang+' '+c1Obj.c1UseSrc);
            translations[text] = '';
        }
        return translations[text];
    };
}

c1.translations.lang = getLang();

/*
c1.translator = function( c1Obj, available ){
    var lang = getLang();
    if (!available) available = [lang];
    if (!available.contains(lang)) lang = available[0];
    var translations = [];
    var translate = async function(text){
        translations[lang] = await c1Obj.c1Use('text_'+lang+'js');
        if (translations[lang][text] === undefined) {
            console.warn('translation needed: '+lang+' '+c1Obj.c1UseSrc);
            translations[lang][text] = '';
        }
        return translations[lang][text];
    }
    return translate;
};
*/

function getLang(){
    var lang = document.documentElement.getAttribute('lang');
    return lang;
}


}();

/*

ussage:
var l = c1.translator( c1.dialog, ['de','en','fr'] );
l('Seite').then();

*/
