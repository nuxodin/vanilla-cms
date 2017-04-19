cms.initCont('cms.backend.users', el=>{
    const pid = cms.el.pid(el);

    const list = el.querySelector('[data-part=list]')
    if (list) {

        var usrSearch = function(e) {
			$fn('page::loadPart')(pid, 'list', {search:e.target.value}).run();
		}.c1Debounce(200);
		document.getElementById('usrSearch').addEventListener('input',usrSearch);

        list.addEventListener('click',e=>{
            const tr = e.target.closest('tr');
            const id = tr.getAttribute('itemid');

            const loginAsBtn = e.target.closest('.-loginAs');
            if (loginAsBtn) {
                $fn('page::api')(pid,{login_as:id}).run(()=>{
                    location.href = appURL;
                });
            }

            const deleteBtn = e.target.closest('.-delete');
            if (deleteBtn && confirm('Möchten Sie den Benutzer wirklich löschen?')) {
                $fn('page::api')(pid,{delete:id}).run(ok => ok && tr.remove());
            }
        })
    }

    const detail = el.querySelector('.-detail');
    if (detail) {
        let id = detail.closest('[itemid]').getAttribute('itemid');
        let input = function(e){
            let el    = e.target;
			el.classList.add('_saving');
            let name  = el.name;
            let value = el.type === 'checkbox' ? el.checked : el.value;
            $fn('page::api')(pid, {save:id, name, value}).then(done=>{
				done && el.classList.remove('_saving');
			});
        }.c1Debounce(160);
        detail.addEventListener('input',input);
        detail.addEventListener('change',input);
    }

    const set_grp = el.querySelector('.-set_grp');
    if (set_grp) {
        let usr_id = set_grp.closest('[itemid]').getAttribute('itemid');
        set_grp.addEventListener('change',e => {
            let grp_id = e.target.value;
            let add = e.target.checked;
            $fn('page::api')(pid, {set_grp:usr_id, grp_id, add});
        });
    }

})
