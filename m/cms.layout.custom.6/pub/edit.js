document.addEventListener('DOMContentLoaded',()=>{
	function loadFonts() {
		document.fonts && document.fonts.forEach(font=>{ // not in edge 15
			const family = font.family;
			const opts = qgCssProps.fontFamily.options;
			!opts.includes(family) && opts.push(family);
		})
	}
	function loadImages() {
		$fn('cmslayouter3::getImages')(Page).then(images=>{
			qgCssEditor.sEditor.imagePalette = images;
		});
	}
	if (!window.qgCssEditor) {
		window.qgCssEditor = new qgStyleSheetEditor();
		const save = function() {
			const saveCustomCss = q_CSSStyleSheetContents(qgCssEditor.active);
			$fn('page::api')(Page, {saveCustomCss});
		}.c1Debounce(500);
		qgCssEditor.on('change', save);
		qgCssEditor.on('close', ()=>$('#cmsContentWindow').fadeIn());
		loadFonts();
		loadImages();
	}
	cmsLayouter3_styleEditor = function() {
		for (let i=0, ss; ss = document.styleSheets[i++];) {
			if (ss.href && ss.href.match(/custom\.css/)) {
				qgCssEditor.show(ss);
				$('#cmsContentWindow').hide();
			}
		};
	};
	$(document).on('keyup',e=>{
		if (e.target.isContentEditable || e.target.form !== undefined) return;
		if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
		e.which==76 && cmsLayouter3_styleEditor();
	});

	/* file-upload */
	window.mCmsLayouter3_initUploader = function(pid) {
		return console.log('todo')
		var uploader = new plupload.Uploader({
			runtimes : 'html5',
			browse_button : 'mCmsLayouter3_pickfiles',
			container : 'mCmsLayouter3_container',
			drop_element: 'mCmsLayouter3_container',
			max_file_size : '30mb',
			url : appURL+'?mCmsLayouter3_uploadPid='+pid,
			filters : [
				{title : "Image files", extensions : "jpg,gif,png"},
			],
		});
		uploader.init();
		uploader.bind('FilesAdded', (up, files)=>{
			uploader.start();
		});
		uploader.bind('Error', (up, err)=>{ alert(err.message); });
		uploader.bind('UploadComplete', (up, file)=>{
			loadImages();
			cms.panel.tabs.show('options');
		});
	};
});
