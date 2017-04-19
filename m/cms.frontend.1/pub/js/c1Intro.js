'use strict';
{
    let Masker = function(){
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
        this.$svg = $(svg);
        this.$container = this.$svg.find('.-container');
        let self = this;
        $(()=>$(document.body).append(this.$svg))
    }
    Masker.prototype.show = function(element){
        this.$svg[0].removeAttribute('hidden');
        this.$svg[0].c1ZTop();
        this.$svg.css({
            height:$(document).height(),
            width:$(document).width()
        })
    }
    Masker.prototype.hide = function(element){
        this.$svg.attr('hidden',true);
        //this.$svg[0].remove();
    }

    $(()=>{
        $('head').append(
            '<style>'+
                '.c1IntroSvg {'+
                  'position:absolute;'+
                  'top:0; '+
                  'left:0; '+
                  'right:0; '+
                  'bottom:0; '+
//                  'pointer-events:none;'+
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
        );
    });

    /* intro */
    let intro = {}
    intro.masker = new Masker();
    let tmp = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    intro.$rect = $(tmp).attr({rx:10,ry:10});
    intro.$rect[0].style.transition = 'all .3s';
    intro.masker.$container.append(intro.$rect);
    intro.$info = $('<div class=c1IntroInfo>');
    $(()=>{
        intro.$info.appendTo(document.body);
    });
    intro.show = function(element, description){
        let coords = element.getBoundingClientRect();
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
        })
        intro.$info[0].c1ZTop();
        intro.$info.html(description).fadeIn();
    };
    intro.hide = function(){
        intro.masker.hide();
        intro.$info.fadeOut();
    }

    /* test */
    $(()=>{
        setTimeout(()=>{
            cms.panel.set('sidebar','settings');
            setTimeout(function(){
                intro.show($('#qgCmsFrontend1 [itemid="settings"]')[0], 'Hier können sie Einstellungen vornehmen')
            },300)
        },10);
        setTimeout(()=>{
            cms.panel.set('sidebar','add');
            intro.show($('#qgCmsFrontend1 [itemid="add"]')[0], 'Ziehen Sie einfach neue Inhalte in Ihre Seite')
        },2000);
        setTimeout(()=>{
            cms.panel.set('sidebar','');
            let el = $('.-m-cms-cont-text')[0];
            cms.contPos(el).mark()
            intro.show(el, 'Dieser Inhalt ist ein Text-Modul')
        },4000);
        setTimeout(()=>{
            let el = $('#qgCmsContPosMenu > .-opts')[0];
            intro.show(el, 'Klicken sie hier um zu den Einstellungen zu gelangen')
        },6000);
        setTimeout(()=>{
            cms.panel.set('sidebar','settings');
            setTimeout(()=>{
                intro.show($('#qgCmsFrontend1 [itemid="settings"] > [widget]')[0], 'Hier können sie Einstellungen vornehmen')
            },200)
        },14000);
    })

};
