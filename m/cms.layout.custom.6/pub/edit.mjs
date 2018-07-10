/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

var qgCssEditor = null;
function showEditor(ss){
	if (!qgCssEditor) {
		qgCssEditor = new qgStyleSheetEditor();
		const save = function() {
			const saveCustomCss = q_CSSStyleSheetContents(qgCssEditor.active);
			$fn('page::api')(Page, {saveCustomCss});
		}.c1Debounce(400);
		qgCssEditor.on('change', save);
		$fn('page::api')(Page, {getImages:1}).then(images=>{
			qgCssEditor.sEditor.imagePalette = images;
		});
	}
	qgCssEditor.show(ss);
}
document.fonts && document.fonts.forEach(font=>{ // no edge 17
	const family = font.family;
	const opts = qgCssProps.fontFamily.options;
	!opts.includes(family) && opts.push(family);
});

window.cmsLayouter3_styleEditor = function() {
	for (var ss of document.styleSheets) {
		if (ss.href && ss.href.match(/\/cms\.layout\.custom\.6\/pub\/custom\.css/)) {
			showEditor(ss);
		}
	}
};
document.addEventListener('keyup',e=>{
	if (e.target.isContentEditable || e.target.form !== undefined) return;
	if (e.shiftKey || e.metaKey || e.altKey || e.ctrlKey) return;
	e.which==76 && cmsLayouter3_styleEditor();
});
