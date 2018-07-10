/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

const dragOver = function(e) {
	const el = e.target.closest('[cmstxt][contenteditable]');
	if (!el) return;
	e.stopImmediatePropagation();
	if (window.q9DragInside) return;
	e.preventDefault(); // firefox dont need this to access droped data!
	let range = document.caretRangeFromPoint(e.clientX, e.clientY);
	let Sel = getSelection();
	Sel.removeAllRanges();
	Sel.addRange(range);
};

const drop = async function(e) {
	const txtEl = e.target.closest('[cmstxt][contenteditable]');
	if (!txtEl) return;
	const tid = txtEl.getAttribute('cmstxt');
	e.stopImmediatePropagation();
	setTimeout(() => cms.txtClean(e.target, tid));
	if (window.q9DragInside) return;
	const dt = new q9DataTransfer(e.dataTransfer);
	if (dt.files.length) {
		e.preventDefault();
		for (let i=0, file; file=dt.files[i++];) {
			if (file.name.match(/[a-z0-9]{8}\.bmp/)) continue; // ignore chrome generated bmp's when draging a html image, prefere the url
			cms.txtAddFile(txtEl, file);
		}
	}
	const fileUrl = dt.getFileUrl();
	if (!fileUrl) return;
	e.preventDefault(); // before await!!
	const pid = await cms.txtIdToPid(tid);
	// todo: intern file
	// Add file to awoid access problems, but its a copy!!!!
	// we only get here if its on other winodw!! (if window.q9DragInside return)
	if (fileUrl.match(location.host)) {
		const intern = fileUrl.match(/dbFile\/([0-9]+)\//)[1];
		if (intern) {
			$fn('page::FileAdd')(pid, intern).run();
			return;
		}
	}
	const res = await $fn('page::FileAdd')(pid, fileUrl);
	if (!fileUrl.match(/(jpg|jpeg|gif|png)$/i)) return;
	const img = document.createElement('img');
	img.src = res.url+'/'+res.name;
	const r = getSelection().getRangeAt(0);
	r.insertNode(img);
	img.addEventListener('load',e=>{
		cms.txtCleanElement(img,tid);
	},{once:true});
}
const paste = function(e) {
	const txtEl = e.target.closest('[cmstxt][contenteditable]');
	if (!txtEl) return;
	const tid = txtEl.getAttribute('cmstxt');
	const addHtml = function(html) {
		const s = getSelection();
		const r = s.c1GetRange();
		html = html.replace(/[^]*<!--StartFragment-->/i, '');
		html = html.replace(/<!--EndFragment-->[^]*/i, '');
		html = html.replace(/<\/body>[\s\S]*$/i, '</body>'); // needed?
		const fragment = r.createContextualFragment(html);
		onPasteFormatNode(fragment);
		r.deleteContents();
		r.insertNode(fragment);
		r.collapse(false); // curser at the end, (todo: not allways working...)
		s.c1SetRange(r);
		txtEl.dispatchEvent(new Event('input',{bubbles:true, cancelable: true})); // NEU 9.4.18
	};
	const data = new q9ClipboardData(e.clipboardData);
	if (data.items) {
		for (let i=0, item; item=data.items[i++];) {
			item.kind === 'file' && cms.txtAddFile(txtEl, item.getAsFile());
		}
		data.q9GetHtml(addHtml) && e.preventDefault();
	}
	setTimeout(()=>cms.txtClean(txtEl, tid), 1);
};
const root = document.documentElement;
root.addEventListener('dragover', dragOver);
root.addEventListener('drop',     drop);
root.addEventListener('paste',    paste);

root.addEventListener('dragstart',  e => window.q9DragInside = true );
root.addEventListener('mouseleave', e => window.q9DragInside = false );

root.addEventListener('input', e => {
	if (window.q9DragInside) {
		// input while drop from drag inside;
		// chrome dont fire drop if dragover is not canceled
		const el = e.target.closest('[cmstxt]');
		if (!el) return;
		const tid = el.getAttribute('cmstxt');
		cms.txtClean(e.target, tid);
	}
	window.q9DragInside = false;
});

// contents
root.addEventListener('dragover', e=>{
	const el = e.target.closest('.qgCmsCont');
	if (!el) return;
	e.stopPropagation();
	e.preventDefault();
});
root.addEventListener('drop', e=>{
	const pid = cms.el.pid(e.target);
	if (!pid) return;
	e.stopPropagation();
	e.preventDefault();
	const dt = new q9DataTransfer(e.dataTransfer);
	function complete() { $fn('page::reload')(pid).run(); }
	if (dt.files.length) {
		let hasOne = false;
		for (let i=0, file; file=dt.files[i++];) {
			if (file.name.match(/[a-z0-9]{8}\.bmp/)) continue; // ignore chrome generated bmp's when draging a html image, prefere the url
			console.log(file)
			hasOne = true;
			cms.cont(pid).upload(file,complete);
		}
		if (hasOne) return;
	}
	const fileUrl = dt.getFileUrl();
	if (!fileUrl) return;
	if (fileUrl.match(location.host)) {
		const match = fileUrl.match(/dbFile\/([0-9]+)\//);
		if (match) {
			$fn('page::FileAdd')(pid, match[1]).run(complete);
			return;
		}
	}
	$fn('page::FileAdd')(pid, fileUrl).run(complete);
});
