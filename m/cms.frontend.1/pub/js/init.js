document.addEventListener('keydown', function(e) {
	if (e.target.isContentEditable || e.target.form !== undefined) return;
	if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
	switch (e.which) {
	case 69: //e
		location.href = window.cmsToggleEditUrl;
		break;
	case 68: //d
        if (window.cmsToggleDebugUrl) location.href = cmsToggleDebugUrl;
		break;
	case 66: //b
		if (window.cmsBackendUrl) location.href = cmsBackendUrl;
		break;
	}
});
document.addEventListener('DOMContentLoaded',function(){
	var editToggle = document.createElement('a');
	editToggle.style.cssText = 'position:fixed; z-index:3';
	editToggle.className = 'qgCMS_editmode_switch '+(qgCmsEditmode?'-active':'');
	editToggle.href = cmsToggleEditUrl;
	editToggle.title = 'Bearbeiten [E]';
	editToggle.innerHTML = '<div><i></i></div>';
	//var editToggle = c1.el('<a style="position:fixed; z-index:3" class="qgCMS_editmode_switch '+(qgCmsEditmode?'-active':'')+'" href="'+cmsToggleEditUrl+' title="Bearbeiten (E)"><div><i></i></div></a>');
	document.body.append(editToggle);

	editToggle.addEventListener('click',function(){
		this.classList.toggle('-active');
	});
});
