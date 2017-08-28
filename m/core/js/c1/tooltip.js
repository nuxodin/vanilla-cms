{
    let css =
    '.c1-tooltip { '+
    '   position:absolute; '+
    '   background:#000; '+
    '   color:#fff; '+
    '   padding:9px; '+
    '   font-size:13px; '+
    '   border-radius:3px; '+
    '   transition:opacity .2s; '+
    '   opacity:0; '+
    '   max-width:200px; '+
    '} '+
    '.c1-tooltip.-Active { '+
    '   opacity:1; '+
    '} '+
    '.c1-tooltip:after { '+
    '   content:\'\'; '+
    '   display:block;'+
    '   width:12px;'+
    '   height:12px;'+
    '   position:absolute;'+
    '   background-color:#000;'+
    '   right:-5px;'+
    '   top:50%; '+
    '   transform:translateY(-50%) rotate(45deg); '+
    '} ';
    document.head.append(c1.dom.fragment('<style>'+css+'<style>'));

}
c1.c1Use(['dom','Placer'],(dom,Placer)=>{
    let tip = dom.fragment('<div class="c1-tooltip q1Rst"></div>').firstChild,
        timeout,
        placer = new Placer(tip, {
            x:'before',
            y:'center',
            margin:15,
        });
    document.documentElement.addEventListener('mouseenter', e=>{
        let target = e.target, config = target.hasAttribute('c1-tooltip');
        if (!config) return;
        let text = target.getAttribute('title');
        clearTimeout(timeout);
        tip.innerHTML = text;
        placer.follow(target);
        document.body.append(tip);
        setTimeout(()=>{
            tip.classList.add('-Active');
        },100)
        tip.c1ZTop();
    },true)
    document.documentElement.addEventListener('mouseleave', e=>{
        let target = e.target, config = target.hasAttribute('c1-tooltip');
        if (!config) return;
        clearTimeout(timeout);
        timeout = setTimeout(()=>{
            tip.classList.remove('-Active');
        },100)
    },true)
});
