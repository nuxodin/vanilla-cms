document.addEventListener('keydown', function(e) {
	if (e.target.isContentEditable || e.target.form !== undefined) return;
	if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
	switch (e.which) {
	case 69: //e
		c1.c1Use('scrollSync',function(scrollSync){
			scrollSync.reevaluate(window);
			var config = scrollSync.getConfig(window);
			localStorage.setItem('cmsLastScrollPosition', JSON.stringify(config));
			location.href = window.cmsToggleEditUrl;
		});
		break;
	case 68: //d
        if (window.cmsToggleDebugUrl) location.href = cmsToggleDebugUrl;
		break;
	case 66: //b
		if (window.cmsBackendUrl) location.href = cmsBackendUrl;
		break;
	}
});

!function(){
	var config = localStorage.getItem('cmsLastScrollPosition');
	if (config) {
		localStorage.removeItem('cmsLastScrollPosition');
		config = JSON.parse(config);
		c1.c1Use('scrollSync',function(scrollSync){
			scrollSync.restoreIn(config, window);
		});
	}
}();

document.addEventListener('DOMContentLoaded',function(){
	var editToggle = c1.dom.fragment('<a style="position:fixed; z-index:3" class="qgCMS_editmode_switch '+(qgCmsEditmode?'-active':'')+'" href="'+cmsToggleEditUrl+' title="Bearbeiten (E)"><div><i></i></div></a>').firstChild;
	document.body.append(editToggle);
	editToggle.addEventListener('click',function(){
		this.classList.toggle('-active');
	});
});
