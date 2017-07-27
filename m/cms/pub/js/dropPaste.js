/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

'use strict';
{ // texts
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

	const drop = function(e) {
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
				cms.txtAddFile(txtEl, file);
			}
		}
		const fileUrl = dt.getFileUrl();
		if (!fileUrl) return;
		if (fileUrl.match(location.host)) {
			const intern = fileUrl.match(/dbFile\/([0-9]+)\//)[1];
			if (intern) {
				cms.txtIdToPid(tid, pid => $fn('page::addDbFile')(pid, intern).run());
				return;
			}
		}
		e.preventDefault();
		cms.txtIdToPid(tid, pid =>{
			$fn('page::FileAdd')(pid,fileUrl).run(res => {
				if (!fileUrl.match(/(jpg|jpeg|gif|png)$/i)) return;
				const img = document.createElement('img');
				img.src = res.url+'/img.jpg';
				const r = getSelection().getRangeAt(0);
				r.insertNode(img);
			});
		});
	};
	const paste = function(e) {
		const txtEl = e.target.closest('[cmstxt][contenteditable]');
		if (!txtEl) return;
		const tid = txtEl.getAttribute('cmstxt');
		const addHtml = function(html) {
			const s = getSelection();
			const r = s.getRangeAt(0);
			html = html.replace(/[^]*<!--StartFragment-->/i, '');
			html = html.replace(/<!--EndFragment-->[^]*/i, '');
			html = html.replace(/<\/body>[\s\S]*$/i, '</body>'); // needed?
			const fragment = r.createContextualFragment(html);
			onPasteFormatNode(fragment);
			r.deleteContents();
			r.insertNode(fragment);
			r.collapse(false); // curser at the end, (todo: not working...)
			s.removeAllRanges();
			s.addRange(r);
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
			for (let i=0, file; file=dt.files[i++];) {
				cms.cont(pid).upload(file,complete);
			}
		} else {
			const fileUrl = dt.getFileUrl();
			if (!fileUrl) return;
			if (fileUrl.match(location.host)) {
				const match = fileUrl.match(/dbFile\/([0-9]+)\//);
				if (match) {
					$fn('page::addDbFile')(pid, match[1]).run(complete);
					return;
				}
			}
			$fn('page::FileAdd')(pid, fileUrl).run(complete);
		}
	});
}
