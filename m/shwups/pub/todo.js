$(function(){

    var els = querySelectorAll('img');
    for (var i=0,img; img=els[i++];) {
        //'img:not([alt]), img[alt=""]:not([role="presentation"])';
        if (!img.getAttribute('alt')) {
            console.error('no alt attribute:');
            console.log(img);
        }
        if (img.complete) {
            console.error('not loaded:');
            console.log(img);
        }
    }

});
