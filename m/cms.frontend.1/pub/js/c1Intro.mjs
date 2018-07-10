class Masker {
    constructor(){
        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.classList.add('c1IntroSvg');
        let id = Math.random();
        svg.innerHTML =
        '<defs>'+
            '<mask id="mask'+id+'">'+
                '<rect fill="#fff" x="0" y="0" width="9100" height="9100"></rect>'+
                '<g class="-container">'+
                    //'<rect x="0" y="0" width="130" height="170" rx="20" ry="20" ></rect>'+
                '</g>'+
            '</mask>'+
        '</defs>'+
        '<rect mask="url(#mask'+id+')" fill="#222" x="0" y="0" width="9100" height="9100"></rect>';
        svg.addEventListener('mousedown',e=>{
            e.stopPropagation();
            e.preventDefault();
        });
        this.svg = svg;
        this.$svg = $(this.svg);
        this.$container = this.$svg.find('.-container');
        //let self = this;
        document.body.append(this.svg);
    }
    show(element){
        this.svg.removeAttribute('hidden');
        this.svg.c1ZTop();
        this.svg.style.height = document.innerHeight+'px';
        this.svg.style.width  = document.innerWidth+'px';
    }
    hide(element){
        this.svg.hidden = true;
    }
}

document.head.append(c1.dom.fragment(
    '<style>'+
        '.c1IntroSvg {'+
          'position:absolute;'+
          'top:0; '+
          'left:0; '+
          'right:0; '+
          'bottom:0; '+
          //'pointer-events:none;'+
          'height:100%;'+
          'width:100%;'+
          'opacity:0;'+
          'visibility:hidden;'+
          'display:block;'+
          'overflow:hidden;'+
          'transition:opacity .2s; '+
        '}'+
        '.c1IntroSvg:not([hidden]) {'+
          'opacity:.7;'+
          'visibility:visible;'+
        '}'+
     '</script>'
));

/* intro */
let intro = {};
intro.masker = new Masker();
let tmp = document.createElementNS("http://www.w3.org/2000/svg", "rect");
intro.$rect = $(tmp).attr({rx:10,ry:10});
intro.$rect[0].style.transition = 'all .3s';
intro.masker.$container.append(intro.$rect);
intro.$info = $('<div class=c1IntroInfo>');
$(()=>{
    intro.$info.appendTo(document.body);
});
intro.show = function(selector, description){
    const element = document.querySelector(selector);
    const coords = element.getBoundingClientRect();
    intro.$rect.css({
        x:      coords.left   - 5,
        y:      coords.top    - 5,
        width:  coords.width  + 10,
        height: coords.height + 10
    });
    intro.masker.show();
    intro.$info.fadeOut(100);
    intro.$info.css({
        background:'#fff',
        padding: 20,
        position:'absolute',
        left: coords.left - 410,
        top:  coords.top + 130
    });
    intro.$info[0].c1ZTop();
    intro.$info.html(description).fadeIn();
};
intro.hide = function(){
    intro.masker.hide();
    intro.$info.fadeOut();
};

function wait(seconds){
    return new Promise(ok=>setTimeout(ok,seconds*1000));
}
/* test */
document.addEventListener('DOMContentLoaded', async function(){
    await wait(1);
    cms.panel.set('sidebar','settings');
    await wait(1);
    intro.show('#qgCmsFrontend1 [itemid="settings"]', 'Hier können sie Einstellungen vornehmen');
    await wait(1);
    cms.panel.set('sidebar','add');
    intro.show('#qgCmsFrontend1 [itemid="add"]', 'Ziehen Sie einfach neue Inhalte in Ihre Seite');
    await wait(1);
    cms.panel.set('sidebar','');
    let el = document.querySelector('.-m-cms-cont-text');
    cms.contPos(el).mark()
    intro.show('.-m-cms-cont-text', 'Dieser Inhalt ist ein Text-Modul');
    await wait(1);
    intro.show('#qgCmsContPosMenu > .-opts', 'Klicken sie hier um zu den Einstellungen zu gelangen');
    await wait(1);
    cms.panel.set('sidebar','settings');
    await wait(1);
    intro.show('#qgCmsFrontend1 [itemid="settings"] > [widget]', 'Hier können sie Einstellungen vornehmen');
});
