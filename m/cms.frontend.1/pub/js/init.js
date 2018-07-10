!function(){ 'use strict';

var editable = ('qgCmsEditmode' in window); // not available if in backend but no edit-access
function qgCmsToggleEdit(){
	if (!editable) return;
	var url = new URL(location.href);
	url.searchParams.set('qgCms_editmode', qgCmsEditmode?0:1);
	url.searchParams.set('cmspid',qgCmsRequestedPage);
	c1.import(sysURL+'core/js/c1/scrollSync.mjs').then(function(){
		c1.scrollSync.reevaluate(window);
		var config = c1.scrollSync.getConfig(window);
		localStorage.setItem('cmsLastScrollPosition', JSON.stringify(config));
		location.href = url;
	});
}

document.addEventListener('keydown', function(e) {
	if (e.target.isContentEditable || e.target.form !== undefined) return;
	if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
	switch (e.which) {
	case 69: //e
		qgCmsToggleEdit();
		break;
	case 68: //d
		if (qgDebugmode!==null) {
			var cmsToggleDebugUrl = new URL(location.href);
			cmsToggleDebugUrl.searchParams.set('debugmode', qgDebugmode?0:1);
			location.href = cmsToggleDebugUrl;
		}
		break;
	case 66: //b
		if (window.cmsBackendUrl) location.href = cmsBackendUrl;
		break;
	}
});

var config = localStorage.getItem('cmsLastScrollPosition');
if (config) {
	localStorage.removeItem('cmsLastScrollPosition');
	config = JSON.parse(config);
	c1.import(sysURL+'core/js/c1/scrollSync.mjs').then(function(){
		c1.scrollSync.restoreIn(config, window);
	});
}

editable && document.addEventListener('DOMContentLoaded',function(){
	var editToggle = c1.dom.fragment('<a style="position:fixed; z-index:3; cursor:pointer" class="qgCMS_editmode_switch '+(qgCmsEditmode?'-active':'')+'" title="Bearbeiten (E)"><div><i></i></div></a>').firstChild;
	document.body.append(editToggle);
	editToggle.addEventListener('click',function(){
		qgCmsToggleEdit();
		this.classList.toggle('-active');
	});
});

}();
