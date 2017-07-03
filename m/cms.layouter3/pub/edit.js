$(function() {
	function loadFonts() {
		$fn('cmslayouter3::getGoogleFonts')(Page).then(function(fonts) {
			fonts.forEach(function(f) {
				qgCssProps.fontFamily.options.push(f);
			});
		});
        $.each(document.styleSheets, function(i,ss) {
            ss.rules && $.each(ss.rules, function(i,rule) {
                if (rule instanceof CSSFontFaceRule) {
                    var name = rule.style.fontFamily;
                    if (!qgCssProps.fontFamily.options.includes(name)) {
        				qgCssProps.fontFamily.options.push(name);
                    }
                }
            });
        });
	}
	function loadImages() {
		$fn('cmslayouter3::getImages')(Page).then(function(images) {
			qgCssEditor.sEditor.imagePalette = images;
		});
	}
	if (!window.qgCssEditor) {
		window.qgCssEditor = new qgStyleSheetEditor();
		var save = function() {
			var content = q_CSSStyleSheetContents( qgCssEditor.active );
			$fn('cmslayouter3::saveCustomCss')(Page, content);
		}.c1Debounce(500);
		qgCssEditor.on('change', save);
		qgCssEditor.on('close', function(){ $('#cmsContentWindow').fadeIn(); });
		loadFonts();
		loadImages();
	}
	cmsLayouter3_styleEditor = function() {
		$.each(document.styleSheets, function(i,ss) {
			if (ss.href && ss.href.match(/custom\.css/)) {
				qgCssEditor.show(ss);
				$('#cmsContentWindow').hide();
			}
		});
	};
	$(document).on('keyup',function(e) {
		if (e.target.isContentEditable || e.target.form !== undefined) return;
		if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
		e.which==76 && cmsLayouter3_styleEditor();
	});

	/* file-upload */
	window.mCmsLayouter3_initUploader = function(pid) {
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
		uploader.bind('FilesAdded', function(up, files) {
			uploader.start();
		});
		uploader.bind('Error', function(up, err) { alert(err.message); });
		uploader.bind('UploadComplete', function(up, file) {
			loadImages();
			cms.panel.tabs.show('options');
		});
	};
});
