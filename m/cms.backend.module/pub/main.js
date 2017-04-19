/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

cms.initCont('cms.backend.module',function(el) {
    const pid = cms.el.pid(el);

    const searchInp = document.getElementById('searchForm');
    const search = function(){
        const search = this.elements.search.value;
        const installed = this.elements.installed.checked;
        $fn('page::loadPart')(pid, 'list', {search, installed}).run();
    }.c1Debounce(200);
    searchInp && searchInp.addEventListener('input', search);
    searchInp && searchInp.addEventListener('change', search);

    const list = el.querySelector('[data-part=list]');
    list && list.addEventListener('click',e=>{
        const tr = e.target.closest('tr');
        const module = tr.getAttribute('itemid');
        let el = null;
        if (el = e.target.closest('.-access')) {
            $fn('page::api')(pid, {module,access:el.checked});
        } else if (el = e.target.closest('.updateBtn')) {
            confirm('wirklich?') && $fn('page::api')(pid, {update:module}).run(done=>{
                el.remove();
                checkUpdateAll();
            });
        } else if (el = e.target.closest('.-uninstall')) {
            if (confirm('Möchten Sie das Modul wirklich löschen?')) {
                $fn('page::api')(pid, {uninstall:module}).run(()=>tr.remove());
            }
        } else if (el = e.target.closest('.-init')) {
            $fn('page::api')(pid, {init:module}).run(()=>el.remove());
        } else if (el = e.target.closest('.-upload')) {
            if (!confirm('Achtung! Das Modul wird auf dem Server überschrieben! \nMöchten Sie das Modul wirklich hochladen?')) return false;
            var notes = prompt('Notes');
            $fn('page::api')(pid, {upload:module, incVersion:2, notes}).run(version=>{
                if (!version) { alert('hat nicht funktioniert!'); }
                el.closest('tr').querySelector('.serverVersion').innerHTML = version;
                el.closest('tr').querySelector('.localVersion').innerHTML  = version;
                el.parentNode.innerHTML = '';
            });
        } else if (el = e.target.closest('.-remoteDelete')) {
            if (confirm('Wirklich löschen auf dem Server löschen!?')) {
                $fn('page::api')(pid, {remoteDelete:module});
            }
        }
        el && e.stopPropagation();
    })

    moduleSetTitle = function(el, module) {
        $fn('page::api')(pid, {module, title:el.value});
    };

    const updateBtn = el.querySelector('.btnUpdateAll');
    updateBtn && updateBtn.addEventListener('click',e=>{
        if (confirm('Wirklich Alle updaten?')) {
            $fn('page::api')(pid, {updateAll:true}).then(()=>{
                //$('input[name=search]').trigger('keyup'); // zzz?
            });
            $fn('page::loadPart')(pid, 'list').then(()=>{
                checkUpdateAll();
            });
        }
        e.preventDefault();
    })

    function checkUpdateAll(){
        updateBtn && !el.querySelector('.updateBtn') && updateBtn.setAttribute('hidden','hidden');
    }
    checkUpdateAll();
});
