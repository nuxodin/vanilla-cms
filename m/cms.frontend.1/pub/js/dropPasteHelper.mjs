/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
//import './../../../core/js/qg/fileHelpers.mjs?qgUniq=93e5e49';
import './../../../core/js/c1/NodeCleaner.mjs?qgUniq=1e3f564';

if (!document.caretRangeFromPoint) { // polyfill for ff
    document.caretRangeFromPoint = function(x,y){
        let caretP = document.caretPositionFromPoint(x,y);
        let range = document.createRange();
        range.setStart(caretP.offsetNode, caretP.offset);
        return range;
    };
}
// txt-id to page-id
const txtIds = {};
cms.txtIdToPid = async function(tid, callb) {
    if (callb) console.warn('callback is deprecated!'); // zzz
	if (txtIds[tid]) return txtIds[tid];
	return txtIds[tid] = await $fn('cms::pidFromTxtId')(tid);
};
// clean texts
cms.txtCleanElement = function(el,tid){
    if (el.tagName === 'IMG' && el.src.match(/^data:/)) {
        cms.txtIdToPid(tid).then( pid => cms.imgToDbFile(el, pid) );
    }
    if (el.tagName === 'IMG' && el.src.match('dbFile/')) {
        const ratio = el.offsetWidth / el.offsetHeight;
        el.style.maxWidth = '100%';
        el.style.width = el.offsetWidth+'px';
        el.style.height = '';
        el.setAttribute('data-c1-ratio', ratio);
        el.style.setProperty('--c1-ratio', ratio);
        el.removeAttribute('width');
        el.removeAttribute('height');
    }
    if (el.src  && el.src.match  && el.src.match('dbFile/')  && el.src .match(location.host)) { el.src  = appURL+el.src .replace(/.*dbFile\//,'dbFile/'); }
    if (el.href && el.href.match && el.href.match('dbFile/') && el.href.match(location.host)) { el.href = appURL+el.href.replace(/.*dbFile\//,'dbFile/'); }
    el.removeAttribute('cmstxt');
    el.classList.remove('qgCmsCont');
    el.classList.remove('qgCmsPage');
};
cms.txtClean = function(el,tid) {
	el = el.data ? el.parentNode : el;
    el.querySelectorAll('*').forEach(function(el) {
        cms.txtCleanElement(el,tid);
        // if (el.tagName === 'IMG' && el.src.match(/^data:/)) {
        //     cms.txtIdToPid(tid).then( pid => cms.imgToDbFile(el, pid) );
		// }
        // if (el.tagName === 'IMG' && el.src.match('dbFile/')) {
        //     const ratio = el.offsetWidth / el.offsetHeight;
        //     el.style.maxWidth = '100%';
        //     el.style.width = el.offsetWidth+'px';
        //     el.style.height = '';
        //     el.setAttribute('data-c1-ratio', ratio);
		// 	el.style.setProperty('--c1-ratio', ratio);
        //     el.removeAttribute('width');
        //     el.removeAttribute('height');
		// }
		// if (el.src  && el.src.match  && el.src.match('dbFile/')  && el.src .match(location.host)) { el.src  = appURL+el.src .replace(/.*dbFile\//,'dbFile/'); }
		// if (el.href && el.href.match && el.href.match('dbFile/') && el.href.match(location.host)) { el.href = appURL+el.href.replace(/.*dbFile\//,'dbFile/'); }
		// el.removeAttribute('cmstxt');
		// el.classList.remove('qgCmsCont')
		// el.classList.remove('qgCmsPage')
	});
};
// text add file from fs
cms.txtAddFile = async function(txtEl, f) {
    const pid = await cms.txtIdToPid( txtEl.getAttribute('cmstxt') );
	const ph = fileGetPreview(f);
	const complete = function(r) {
		if (f.c1IsImage()) {
			const load = function() {
				const file = new dbFile(this);
				const max = txtEl.offsetWidth;
				ph.replaceWith(this);
				if (this.width > max) {
					const h = max / this.width * this.height;
					file.set('w',max); file.set('h',h); file.write();
				}
				qgSelection.toElement(this);
                img.dispatchEvent(new MouseEvent('mousedown',{bubbles:true})); // why
                img.dispatchEvent(new Event('qgResize',{bubbles:true}));
                img.onload = null;
			};
            const img = document.createElement('img');
            img.src = r.url;
            img.onload = load;
		} else {
            ph.style.opacity = '';
            ph.firstElementChild.href = r.url;
            ph.firstElementChild.innerHTML = r.url.replace(/.*\//,'');
		}
		txtEl.focus();
	};
	cms.cont(pid).upload(f,complete);
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
    img.c1ToBlob().then(blob => cms.cont(pid).upload(blob, complete));
};

function fileGetPreview(f) {
    let ph = null;
    if (f.c1IsImage()) {
        ph = c1.dom.fragment('<img style="max-width:101%; opacity:.6; filter:grayscale(1)">').firstChild;
        f.c1ToImage(ph);
    } else {
        ph = c1.dom.fragment('<span><a href="#" target=_blank> '+f.name+' </a></span>').firstChild;
    }
    const range = getSelection().getRangeAt(0);
    range.insertNode(ph);
    return ph;
}


cms.NodeCleanerConf_ForeignContent = {
	tags: {H1:1,H2:1,H3:1,H4:1,H5:1,H6:1,A:1,BR:1,P:1,B:1,STRONG:1,IMG:1,DIV:1,TABLE:1,TR:1,TD:1,TBODY:1,THEAD:1,SPAN:1,LI:1,UL:1},
	tagsRemove: {'O:P':1,'STYLE':1,'SCRIPT':1,'META':1,'LINK':1,'TITLE':1},
	attributes: {src:1,target:1,href:1,alt:1},
	//styles: {},
	//classes: {},
	removeEmptyElements: 1,
	//removeUnusedElements: 1,
	removeDivers: 1,
    removeNbsp: 1,
};

const Cleaner = new c1.NodeCleaner(cms.NodeCleanerConf_ForeignContent);

window.onPasteFormatNode = function(node) {
	Cleaner.cleanContents(node, true);
};

window.q9DataTransfer = class {
    constructor(dt){
        this.dt = dt;
        this.types = dt.types;
        this.files = dt.files || [];
        if (this.types===undefined) this.types = ['url','text'];
    }
    contains(type) {
        console.warn('used?');
        return [].slice.call(this.types).includes(type);
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
};

window.q9ClipboardData = class {
    constructor(cd) {
        this.items = cd.items || [];
    }
    q9GetData(type, cb) { // widthout cb usefull as "hasType"
        console.warn('used?') // zzz
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
};
