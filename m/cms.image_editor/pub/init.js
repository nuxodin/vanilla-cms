/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
'use strict';
{
    // hover-icon
    let icon = document.createElement('div');
    icon.style.cssText = 'transition:opacity .3s; border-radius:2px; overflow:hidden; position:absolute; background:#fff; cursor:pointer; font-size:13px';
    icon.innerHTML = '<svg style="display:block; background:#fff" xmlns="http://www.w3.org/2000/svg" width="32" height="28" viewBox="0 0 32 28"><path d="M29.996 2c.002 0 .003.002.004.004v23.992c0 .002-.002.003-.004.004H2.004C2.002 26 2 25.998 2 25.996V2.004C2 2.002 2.002 2 2.004 2h27.992zM30 0H2C.9 0 0 .9 0 2v24c0 1.1.9 2 2 2h28c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2z"/><path d="M26 7c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm2 17H4v-4l7-12 8 10h2l7-6z"/></svg>';
    icon.title = 'Bild bearbeiten';
    icon.onmouseleave = () => setIconEl(null);
    icon.onmousedown  = () => showEditor(iconActive);
    let hideTimeout;
    let iconActive = null;
    function setIconEl(el){
        icon.style.opacity = 0;
        clearTimeout(hideTimeout);
        if (el) {
            iconActive = el;
            setTimeout(()=>icon.style.opacity = 1)
            document.body.appendChild(icon);
            let pos = el.getBoundingClientRect();
            icon.style.top  = pos.top  + pageYOffset + 2 + 'px';
            icon.style.left = pos.left + pageXOffset + 2 + 'px';
            icon.c1ZTop();
        } else {
            hideTimeout = setTimeout(()=>{
                icon.remove();
                iconActive = null;
            },300);
        }
    }
    function testTarget(el){
        if (!el) return;
        if (!el.getAttribute) return;
        if (!el.hasAttribute('data-dbfile-editable')) return
        let src = el.getAttribute('data-dbfile-editable') || el.src;
        if (!src.match(/dbFile\/[0-9]+\//)) return;
        setIconEl(el);
        return el;
    }
    document.addEventListener('mouseenter',e=>{
        testTarget(e.target);
    },true);
    document.addEventListener('mouseleave',e=>{
        if (icon.contains(e.relatedTarget)) return; // leave into icon?
        let el = e.target;
        if (el !== iconActive) return; // leaving active el?
        if (testTarget(el.parentNode)) return; // is the parent a target?
        setIconEl(null); // hide icon
    },true);

    // file list
    var initFileList = function() {
        let div = document.getElementById('cmsWidgetContent_media_list');
        if (!div || div.qg_image_editor_initialized) return;
        div.qg_image_editor_initialized = 1;
        let tr, i = 0, els = div.querySelectorAll('[itemid]');
        while (tr=els[i++]) {
            let img = tr.querySelector('.-preview > img');
            let td = document.createElement('td');
            tr.lastElementChild.before(td);
            td.style.cssText = 'width:24px; cursor:pointer';
            if (!img || !img.src.match(/\.(jpg|jpeg|png)/i)) continue;
            td.title = 'Bild bearbeiten';
            td.innerHTML = '<svg style="display:block; background:#fff" xmlns="http://www.w3.org/2000/svg" width="24" height="21" viewBox="0 0 32 28"><path d="M29.996 2c.002 0 .003.002.004.004v23.992c0 .002-.002.003-.004.004H2.004C2.002 26 2 25.998 2 25.996V2.004C2 2.002 2.002 2 2.004 2h27.992zM30 0H2C.9 0 0 .9 0 2v24c0 1.1.9 2 2 2h28c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2z"/><path d="M26 7c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm2 17H4v-4l7-12 8 10h2l7-6z"/></svg>';
            td.onmousedown = function(){ showEditor(img); }
        }
    }
    c1.onElement('[cmsconf="media_list_trs"]',initFileList)

    // ctrl-click (hidden function)
    addEventListener('dblclick',e=>{
        if (!e.ctrlKey || e.target.tagName !== 'IMG') return;
        if (!e.target.src.match(/dbFile\/[0-9]+\//)) return;
        showEditor(e.target);
    });
    // show the editor
    let showEditor = function(el) {
        let src = el.getAttribute('data-dbfile-editable') || el.src;
        var baseUrl = sysURL+'cms.image_editor/pub/';
        c1.c1Use('focusIn',()=>{});
        c1Use([
            baseUrl+'c1FullScreenPopup.js',
            baseUrl+'c1ImageCropper.js',
            baseUrl+'c1ImageEditor.js',
            baseUrl+'qgDbFileImageEditor.js',
        ],()=>{
            let editor = new qgDbFileImageEditor();
            editor.show(src);
        });
    }
}
