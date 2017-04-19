c1.is.test = function(el){
    el.addEventListener('click',function(){
        alert(1)
    })
}
!function(){
    var div = document.createElement('div')
    div.innerHTML = '<style>[c1-is~=c1\\.is\\.test] {background:green}</style>';
    document.head.appendChild(div);
}();
