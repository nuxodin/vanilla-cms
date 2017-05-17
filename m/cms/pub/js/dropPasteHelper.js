/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

'use strict';
{
    if (!document.caretRangeFromPoint) { // polyfill for ff
        document.caretRangeFromPoint = function(x,y){
            let caretP = document.caretPositionFromPoint(x,y);
            let range = document.createRange();
            range.setStart(caretP.offsetNode, caretP.offset);
            return range;
        }
    }
	// txt-id to page-id
	const txtIds = {};
	cms.txtIdToPid = function(tid, cb) {
		if (txtIds[tid]) return cb(txtIds[tid]);
		$fn('cms::pidFromTxtId')(tid).run(function(pid) {
			txtIds[tid] = pid;
			cb(pid);
		});
	};
	// clean texts
	cms.txtClean = function(el,tid) {
		el = el.data ? el.parentNode : el;
		$(el).find('*').each(function(i,el) {
			if (el.tagName === 'IMG' && el.src.match(/^data:/)) {
				cms.txtIdToPid(tid, pid=>{
					cms.imgToDbFile(el,pid);
				});
			}
			if (el.src  && el.src.match  && el.src.match('dbFile/')  && el.src .match(location.host)) { el.src  = appURL+el.src .replace(/.*dbFile\//,'dbFile/'); }
			if (el.href && el.href.match && el.href.match('dbFile/') && el.href.match(location.host)) { el.href = appURL+el.href.replace(/.*dbFile\//,'dbFile/'); }
			el.removeAttribute('cmstxt');
			el.classList.remove('qgCmsCont')
			el.classList.remove('qgCmsPage')
		});
	};
	// text add file
	cms.txtAddFile = function(txtEl, f) {
		const txtId = txtEl.getAttribute('cmstxt');
		cms.txtIdToPid(txtId, function(pid) {
			const ph = fileGetPreview(f);
			const complete = function(r) {
				if (f.q9IsImage()) {
					const load = function() {
						const file = new dbFile(this);
						const max = txtEl.offsetWidth;
						ph.replaceWith(this);
						if (this.width > max) {
							const h = max / this.width * this.height;
							file.set('w',max); file.set('h',h); file.write();
						}
						qgSelection.toElement(this);
						img.trigger('mousedown');
                        img[0].dispatchEvent(new Event('qgResize',{bubbles:true}));
					};
					const img = $('<img>').attr('src',r.url).one('load',load);
				} else {
					ph.css({opacity:1}).children().attr('href',r.url).html(r.url.replace(/.*\//,''));
				}
				txtEl.focus();
			};
			cms.cont(pid).upload(f,complete);
		});
	};

	// img to dbfile
	cms.imgToDbFile = function(img, pid, cb) {
		const complete = function(r) {
			const load = function() {
                img.removeEventListener('load',load);
				cb && cb(img);
			};
            img.addEventListener('load',load);
            img.src = r.url;
		};
		img.q9ToBlob(blob => cms.cont(pid).upload(blob, complete));
	};

	function fileGetPreview(f) {
		let ph = null;
		if (f.q9IsImage()) {
			ph = $('<img style="max-width:101%; opacity:.6; filter:grayscale(1);">');
			f.q9ToImage(null,ph[0]);
		} else {
			ph = $('<span><a href="#" target=_blank> '+f.name+' </a></span>');
		}
		const range = getSelection().getRangeAt(0);
		range.insertNode(ph[0]);
		return ph;
	}

	cms.NodeCleanerConf_ForeignContent = {
		tags: {H1:1,H2:1,H3:1,H4:1,H5:1,H6:1,A:1,BR:1,P:1,B:1,STRONG:1,IMG:1,DIV:1,TABLE:1,TR:1,TD:1,TBODY:1,THEAD:1,SPAN:1,LI:1,UL:1},
		tagsRemove: {'O:P':1,'STYLE':1,'SCRIPT':1,'META':1,'LINK':1,'TITLE':1},
		attributes: {src:1,target:1,href:1,alt:1},
		//styles: {},
		//classes: {},
		removeEmptyElements: 1,
		removeUnusedElements: 1,
		removeDivers:1,
	};
	const Cleaner = new c1.NodeCleaner(cms.NodeCleanerConf_ForeignContent);

	window.onPasteFormatNode = function(node) {
		Cleaner.cleanContents(node, true);
	};

}

class q9DataTransfer {
    constructor(dt){
        this.dt = dt;
        this.types = dt.types;
        this.files = dt.files || [];
        if (this.types===undefined) this.types = ['url','text'];
    }
    contains(type) {
        return [].slice.call(this.types).indexOf(type)!==-1;
    }
    getData(type) {
        return this.dt.getData(type);
    }
    getFileUrl() {
        const fileurl1 = this.getData('application/x-moz-file-promise-url');
        const html     = this.getData('text/html') || '';
        const matches  = html.match(/.*<img src="([^"]*)".*/, '$1');
        const fileurl2 = matches && matches[1];
        let   url = null;
        if (this.getData('text/x-moz-url')) {
            url = this.getData('text/x-moz-url').split('\n')[0];
        }
        let fileurl3 = this.getData('url') || url || '';
        fileurl3 = fileurl3.trim();
        const fileurl = fileurl1 || fileurl2 || fileurl3;
        if (fileurl.match(/^file/)) return null;
        return fileurl1 || fileurl2 || fileurl3;
    }
}

class q9ClipboardData {
    constructor(cd) {
        if (cd) {
            this.items = cd.items || [];
        } else if (window.clipboardData) { // ie / edge supports it // zzz?
            this.items = [];
            const text = clipboardData.getData('Text');
            if (text) {
                this.items.push({
                    kind: 'string',
                    type: 'text/plain',
                    getAsString(cb) { cb && cb(text); }
                });
            }
            const url = clipboardData.getData('URL');
            if (url) {
                this.items.push({
                    kind: 'string',
                    type: 'text/url',
                    getAsString(cb) { cb && cb(url); }
                });
            }
            console.warn('used?')
        }
    }
    q9GetData(type, cb) { // widthout cb usefull as "hasType"
        for (let i=this.items.length, item; item=this.items[--i];) {
            if (item.type===type) {
                item.getAsString(cb);
                return 1;
            }
        }
        return 0;
    }
    q9GetHtml(cb) {
        let alternative = null;
        for (let i = 0, item; item = this.items[i++];) {
            if (item.type === 'text/html') {
                cb && item.getAsString(cb);
                return 1;
            }
            if (item.type === 'text/plain') {
                alternative = item;
            }
        }
        if (alternative) {
            alternative.getAsString(text => {
                cb && cb( text.replace(/\n/g,'<br>') );
            });
            return 1;
        }
        return 0;
    }
}
