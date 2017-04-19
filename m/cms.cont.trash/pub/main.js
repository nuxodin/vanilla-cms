/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
cms.initCont('cms.cont.trash', function(el) {
    var pid = cms.el.pid(el);
    var preview = el.querySelector('.-preview');
    var previewIframe = preview.querySelector('iframe');
    function closePreview(e){
        previewIframe.src = '';
        preview.classList.remove('-show');
        e.preventDefault();
        e.stopPropagation();
    }
    preview.addEventListener('mousedown',closePreview);

    el.c1Find('.-list').addEventListener('click',e=>{
        var item = e.target.closest('.-item');
        var id = item.getAttribute('itemid');
        if (e.target.closest('.-remove')) {
            item.style.opacity = .4;
            $fn('page::remove')(id);
            $fn('page::reload')(pid);
        } else if (e.target.closest('.-restore')) {
            item.style.opacity = .4;
            $fn('page::reload')(pid, {restore:id});
        } else {
            preview.classList.add('-show');
            previewIframe.onload=function(){
                this.contentDocument.addEventListener('mousedown',closePreview);
            }
            previewIframe.src = appURL+'?cmspid='+id+'&qgCms_editmode=1&qgCmsNoFrontend';
        }
    });
    var removeAll = el.c1Find('.-removeAll');
    removeAll && removeAll.addEventListener('click',function(){
        $fn('page::reload')(pid, {removeAll:1});
        this.style.opacity=.3;
    })
});
