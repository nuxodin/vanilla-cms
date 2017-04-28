"use strict";
{
    let div, iframe1, iframe2, pid, view1;
    window.CmsVersComparer = {
        _ensure(){
            if (div) return;
            let html =
            '<div id=qgCmsVersionComparer class=qgCMS>'+
                '<style>'+css+'</style>'+
                '<div class=-tools>'+
                    '<div style="flex:1 0 200px">'+
                        '<button class=-mode-side>Ansicht wechseln</button> '+
                        '<button class=-diffs>Unterschiede anzeigen</button> '+
                    '</div>'+
                    '<div style="flex:auto; display:flex; align-items:center; justify-content:center">'+
                        '<span class=-toText style="flex:1 0 30px; text-align:right">Live</span>'+
                        ' &nbsp;&nbsp; <input class=-fade min=0 max=1 step=any type=range><span class=-splitter></span> &nbsp;&nbsp; '+
                        '<span class=-fromText style="flex:1 0 30px"> &nbsp; Entwurf</span>'+
                    '</div>'+
                    '<div style="flex:1 0 200px; text-align:right">'+
                        '<button class=-accept>Veröffentlichen</button> '+
                        '<button class=-close>schliessen</button> '+
                    '</div>'+
                '</div>'+
                '<div class=-views>'+
                    '<div class=-v2><iframe class=-i2></iframe></div>'+
                    '<div class=-v1><iframe class=-i1></iframe></div>'+
                '</div>'+
            '</div>';
            div = new DOMParser().parseFromString(html, 'text/html').body.firstChild;
            iframe1 = div.querySelector('.-i1');
            iframe2 = div.querySelector('.-i2');
            view1   = div.querySelector('.-v1');
            div.querySelector('.-fade').addEventListener('input',function(){
                view1.style.opacity = this.value;
            })
            div.querySelector('.-mode-side').addEventListener('click',function(){
                let has = div.classList.toggle('-Mode-side');
                has && div.classList.remove('-Diffs');
            })
            div.querySelector('.-diffs').addEventListener('click',function(){
                div.classList.toggle('-Diffs');
            })
            div.querySelector('.-close').addEventListener('click',this.close);

            function initFrame(){
                let other = this === iframe1 ? iframe2 : iframe1;
                let win = this.contentWindow;
                let doc1 = win.document;

                // scrollSync
                c1.c1Use('scrollSync',scrollSync=>{
                    // sync scroll
                    scrollSync.syncWindows(win, other.contentWindow);
                    // sync clicks
                    win.addEventListener('click',function(e){
                        if (e.c1Synced) return;
                        let selector = scrollSync.getSelector(e.target);
                        let otherEl = other.contentWindow.document.querySelector(selector);
                        var event = new MouseEvent('click', {
                            'view': window,
                            'bubbles': true,
                            'cancelable': true
                        });
                        event.c1Synced = true;
                        otherEl.dispatchEvent(event);
                    },true)
                })

                // mousemove  => opacity
                doc1.addEventListener('mousemove',function(e){
                    let opacity = e.clientX / win.innerWidth;
                    div.querySelector('.-fade').value = opacity;
                    view1.style.opacity = opacity;
                });
                let prevent = e => e.preventDefault() && e.stopPropagation();
                doc1.addEventListener('mousedown', prevent);
                doc1.addEventListener('click', prevent);
                doc1.addEventListener('touchstart', prevent);
            }
            iframe1.addEventListener('load',initFrame);
            iframe2.addEventListener('load',initFrame);
        },
        keyListener(e){
            e.which===27 && CmsVersComparer.close();
        },
        compare(page_id, options) {
            this._ensure();
            window.addEventListener('keydown',this.keyListener);
            pid = page_id;
            options = Object.assign({fromSpace:'active', fromLog:0, toSpace:'active', toLog:0, fromText:'Entwurf', toText:'Live', accept:null, acceptText:'Übernehmen'}, options);
            // accept function
            let acceptEl = div.querySelector('.-accept');
            acceptEl.style.display = options.accept ? 'inline-block' : 'none';
            if (options.accept) {
                acceptEl.onclick   = options.accept;
                acceptEl.innerHTML = options.acceptText;
            }
            div.querySelector('.-fromText').innerHTML = options.fromText;
            div.querySelector('.-toText').innerHTML   = options.toText;
            this.setMain  (options.fromSpace, options.fromLog);
            this.setSecond(options.toSpace,   options.toLog);
            document.body.append(div);
            div.c1ZTop();
        },
        close(){
            window.removeEventListener('keydown',this.keyListener);
            div.remove();
        },
        setMain(space, log) {
            iframe1.src = appURL+'?cmspid='+Page+'&qgCmsVersSpace='+space+'&qgCmsVersLog='+log+'&qgCmsVersPage='+pid+'&qgCmsNoFrontend';
        },
        setSecond(space, log) {
            iframe2.src = appURL+'?cmspid='+Page+'&qgCmsVersSpace='+space+'&qgCmsVersLog='+log+'&qgCmsVersPage='+pid+'&qgCmsNoFrontend';
        }
    };
    let css =
    '#qgCmsVersionComparer { '+
    '    position:fixed; '+
    '    top:0; '+
    '    left:0; '+
    '    right:0; '+
    '    bottom:0; '+
    '    background:#fff; '+
    '    display:flex; '+
    '    flex-flow:column; '+
    '}'+
    '#qgCmsVersionComparer .-tools { '+
    '    display:flex; '+
    '    border-bottom:2px solid #000; '+
    '}'+
    '#qgCmsVersionComparer .-tools > * { '+
    '    margin:10px; '+
    '}'+
    '#qgCmsVersionComparer .-views { '+
    '    display:flex; '+
    '    position:relative; '+
    '    flex:auto; '+
    '}'+
    '#qgCmsVersionComparer .-views > div { '+
    '    position:absolute; '+
    '    top:0; left:0; right:0; bottom:0; '+
    '    background:#fff; '+
    '    flex:auto; '+
    '} '+
    '#qgCmsVersionComparer .-v1 { '+
    '    opacity:.5; '+
    '}'+
    '#qgCmsVersionComparer iframe { '+
    '    border:none; '+
    '    position:absolute; '+
    '    top:0; left:0; right:0; bottom:0; '+
    '    width:100%; '+
    '    height:100%; '+
    '    box-sizing:border-box; '+
    '}'+
    '#qgCmsVersionComparer.-Mode-side .-views > div { '+
    '    position:relative; '+
    '    opacity:1 !important; '+
    '}'+
    '#qgCmsVersionComparer.-Mode-side .-diffs { display:none; }'+
    '#qgCmsVersionComparer.-Mode-side .-fade { display:none; }'+
    '#qgCmsVersionComparer.-Mode-side .-splitter { display:inlinb-block; height:2em; border-left:2px solid #000; }'+
    '#qgCmsVersionComparer.-Mode-side .-i1 { border-left: 1px solid #000; }'+
    '#qgCmsVersionComparer.-Mode-side .-i2 { border-right:1px solid #000; }'+
    '#qgCmsVersionComparer.-Diffs .-views { '+
    '    filter:invert(100%); '+
    '}'+
    '#qgCmsVersionComparer.-Diffs .-views > .-v1 { '+
    '    mix-blend-mode:difference; '+
    '    opacity:1 !important; '+
    '}'+
    '#qgCmsVersionComparer.-Diffs .-fade { display:none; }'+
    '';
}
