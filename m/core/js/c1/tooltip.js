{
    let css =
    '.c1-tooltip { '+
    '   position:absolute; '+
    '   background:#000; '+
    '   color:#fff; '+
    '   padding:16px; '+
    '   font-size:14px; '+
    '   border-radius:5px; '+
    '   transition:visibility 0s linear .3s, opacity .3s linear; '+
    '   opacity:0; '+
    '   visibility:hidden; '+
    '   max-width:200px; '+
    '   will-change:opacity; '+
    '} '+
    '.c1-tooltip.-Active { '+
    '   opacity:1; '+
    '   visibility:visible; '+
    '   transition-delay:0s; '+
    '} '+
    '.c1-tooltip:after { '+
    '   content:\'\'; '+
    '   display:block;'+
    '   width:16px;'+
    '   height:16px;'+
    '   position:absolute;'+
    '   background-color:inherit;'+
    '   right:-8px;'+
    '   top:50%; '+
    '   transform:translateY(-50%) rotate(45deg); '+
    '} ';
    document.head.append(c1.dom.fragment('<style>'+css+'<style>'));

}
c1.c1Use(['dom','Placer'],(dom,Placer)=>{
    let tip = dom.fragment('<div class="c1-tooltip q1Rst"></div>').firstChild,
        hideTimeout,
        showTimeout,
        placer = new Placer(tip, {
            x:'before',
            y:'center',
            margin:15,
        });
    document.documentElement.addEventListener('mouseenter', e=>{
        let target = e.target;
        if (!hasTooltip(target)) return;
        let config = target.hasAttribute('c1-tooltip');
        //let text   = target.getAttribute('title');
        let text = getContent(target);

        clearTimeout(hideTimeout);
        clearTimeout(showTimeout);

        var elStyle = getComputedStyle(target);
        var delay = elStyle.getPropertyValue('--c1-tooltip-delay');
        delay = delay === '' ? .1 : parseFloat(delay);

        showTimeout = setTimeout(function(){
            tip.innerHTML = text;
            document.body.append(tip);
            placer.follow(target);
//            setTimeout(()=>{
                tip.classList.add('-Active');
//            },100)
            tip.c1ZTop();
        },delay*1000);
    },true)
    document.documentElement.addEventListener('mouseleave', e=>{
        let target = e.target;
        if (!hasTooltip(target)) return;
        let config = target.hasAttribute('c1-tooltip');
        clearTimeout(hideTimeout);
        clearTimeout(showTimeout);


        var elStyle = getComputedStyle(target);
        var delay = elStyle.getPropertyValue('--c1-tooltip-delay');
        delay = delay === '' ? .1 : parseFloat(delay);
        console.log(delay)
        hideTimeout = setTimeout(()=>{
            tip.classList.remove('-Active');
        },delay*1000)
    },true)
    tip.addEventListener('mouseenter',function(){
        clearTimeout(hideTimeout);
        clearTimeout(showTimeout);
    });
    tip.addEventListener('mouseleave',function(){
        clearTimeout(hideTimeout);
        clearTimeout(showTimeout);
        hideTimeout = setTimeout(()=>{
            tip.classList.remove('-Active');
        },1000)
    })
    tip.addEventListener('mousedown',e=>e.stopPropagation());

    function hasTooltip(target){
        if (!target.hasAttribute('c1-tooltip')) return;
        let text = getContent(target);
        if (!text) return;
        return true;
    }
    function getContent(el){
        if (el.hasAttribute('c1-tooltip-content')) return el.getAttribute('c1-tooltip-content');
        if (el.hasAttribute('title')) {
            let content = el.getAttribute('title');
            el.setAttribute('c1-tooltip-content', content);
            el.removeAttribute('title');
            return content;
        }
    }
});
