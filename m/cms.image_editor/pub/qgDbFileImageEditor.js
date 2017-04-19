/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
class qgDbFileImageEditor extends c1ImageEditor {
    show(src, options) {
        if (!options) options = {};
        let eSrc = src.replace(/(dbFile\/[0-9]+\/).*/, '$1');
        let unique = src.match(/\/(u-[^/]+\/)/);
        unique = unique ? unique[1] : '';
        this.file_id = eSrc.match(/(dbFile\/([0-9]+)\/).*/)[2];
        this.el().classList.add('qgCMS');
        super.show(eSrc + unique + 'img.jpg', {
            onload: this.loading(()=>{
                // cropper settings
                let width  = src.match(/\/w-([0-9]+)(\/|$)/);
                let height = src.match(/\/h-([0-9]+)(\/|$)/);
                width  = width  && width[1];
                height = height && height[1];

                // get the max part (max means image will be scaled to have place in "width/height") if "not max", it will be cropped on the server
                let maxMatch    = src.match(/\/max-?([^/]*)(\/|$)/);
                let max = false;
                if (maxMatch) {
                    max = maxMatch && maxMatch[1];
                    max = max === '' ? true : !!parseInt(max);
                }

                if (width)  this.minWidth  = width*2;  // *2 => retina
                if (height) this.minHeight = height*2;

                if (width && height && !max) {
                    let img = this.el('.-img');
                    let aspectRatio = width / height;
                    // force aspectRatio
                    this.cropper.aspectRatio = aspectRatio;
                    // if the expected aspectRatio, show cropper
                    let natural_aspectRatio = img.naturalWidth / img.naturalHeight;
                    if (aspectRatio.toFixed(1) !== natural_aspectRatio.toFixed(1)) {
                        setTimeout(()=>this.cropper.show());
                    }
                }
                //setTimeout(renderHotspot);
            }),
            onerror:()=>{
                c1Loading.stop(this.el('.-tools'));
            },
            onsave: ()=>{
                this.upload(this.loading(()=>{
                    this.hide();
                }))
            }
        }, options);

        // history
        this.el('.-tools').insertAdjacentHTML('beforeend',
            '<div class=c1AccordionHead tabindex=-1>Verlauf</div>'+
            '<div class=-history></div>'
        );
        setTimeout(()=>this.loadHistory(),100);
        // styling
        let head = this.el('.-tools :first-child');
        head.classList.add('c1AccordionHead');
        head.onclick=()=>{
            this.hide();
        }
        head = this.el('.-toolsCrop :first-child');
        head.classList.add('c1AccordionHead');
        head.onclick = ()=>this.cropper.hide()

        // meta
        this.meta = {};
        this.el('.-tools').insertAdjacentHTML('beforeend',
            '<div class=c1AccordionHead tabindex=-1>Meta-Daten</div>'+
            '<div class=-meta>'+
                '<input name=name placeholder="Dateiname" style="width:100%"><br>'+
            '</div>'
        );
        this.el('.-meta [name=name]').addEventListener('input',(e=>{
            this.meta.name = e.target.value;
            $fn('dbFileImageEditor::setMeta')(this.file_id, this.meta);
        }).c1Debounce(500));

        this.el('.-canvas').addEventListener('mousedown',e=>{
            var rect = this.el('.-canvas').getBoundingClientRect();
			var layerX = e.clientX - rect.left;
			var layerY = e.clientY - rect.top;
            var hpos = (layerX / this.el('.-canvas').offsetWidth) * 100;
            var vpos = (layerY / this.el('.-canvas').offsetHeight) * 100;
            $fn('dbFileImageEditor::setMeta')(this.file_id, {hpos,vpos}).then(()=>{
                this.meta.hpos = hpos;
                this.meta.vpos = vpos;
                renderHotspot();
            });
        });

        var renderHotspot = ()=>{
            if (this.meta.hpos===null || this.meta.vpos === null) return;
            var rect = this.el('.-canvas').getBoundingClientRect();
            var x = rect.left + rect.width  * (this.meta.hpos*1 / 100);
            var y = rect.top  + rect.height * (this.meta.vpos*1 / 100);
            this.el('.-hotspot').style.left = x - 10 + 'px';
            this.el('.-hotspot').style.top  = y - 10 + 'px';
        }
        $fn('dbFileImageEditor::getMeta')(this.file_id).then(meta => {
            this.meta = meta;
            this.el('.-viewport').insertAdjacentHTML('beforeend',
            '<div class=-hotspot style="position:absolute; width:20px; height:20px; border-radius:50%; background:#f00; pointer-events:none; opacity:.2">'+
                '<div style="position:absolute; bottom:-20px; color:#fff; text-shadow:0 0 10px #000; transform: translateX(-50%);left: 50%;">Hotspot</div>'+
                '<style>.-viewport:hover > .-hotspot {opacity:.6 !important}</style>'+
            '</div>');
			renderHotspot();
            this.el('.-meta [name=name]').value = meta.name;
        });
    }
    upload(cb){

        var canvas = this.el('.-canvas');
        var jpeg, png, hasAlpha = false;

        // has Alpha
        var gl = canvas.getContext("webgl");
        var pixels = new Uint8Array(gl.drawingBufferWidth * gl.drawingBufferHeight * 4);
        gl.readPixels(0, 0, gl.drawingBufferWidth, gl.drawingBufferHeight, gl.RGBA, gl.UNSIGNED_BYTE, pixels);
        for (let i = 0, n = pixels.length; i < n; i += 4) {
            let alpha = pixels[i+3];
            if (alpha < 255) { hasAlpha = true; break; }
        }

        !hasAlpha && canvas.toBlob(Blob=>{ jpeg = Blob; upload(); },'image/jpeg', 1);
                     canvas.toBlob(Blob=>{ png  = Blob; upload(); },'image/png', 1);

        let upload = ()=>{
            let Blob = null;
            if (hasAlpha) {
                Blob = png;
            } else {
                if (!jpeg || !png) return; // wait for both versions
                Blob = jpeg.size > png.size ? png : jpeg;
            }
            qgfileUpload(Blob, 'qgDbFileImageEditor', {
                url: appURL+'?file_id='+this.file_id,
                complete: () => {
                    this.reloadElements();
                    cb && cb()
                }
            });
        };
    }
    loading(fn){
        const sidebar = this.el('.-sidebar');
        sidebar && c1Loading.start(sidebar);
        return ()=>{
            sidebar && c1Loading.stop(sidebar);
            return fn ? fn() : null;
        }
    }
    reloadElements() {
        location.reload();
        // var images = document.querySelectorAll('img');
        // for (var i in images) {
        //     var image = images[i];
        //     var re = new RegExp('dbFile/'+this.file_id+'/');
        //     if (image.src && image.src.match(re)) {
        //         image.src = image.src.replace(/\/u-[^\/]*\//,'/u-'+Math.round(Math.random()*10000)+'/');
        //     }
        // }
    }
    loadHistory(){
        $fn('dbFileImageEditor::getHistory')(this.file_id).then(res=>{
            this.el('.-history').innerHTML = '<div style="max-height:400px; overflow:auto">'+res+'</div>';
        });
        this.el('.-history').onmousedown = (e)=>{
            let log = e.target.getAttribute('log');
            if (!log) return;
            if (!confirm('Möchten Sie das Bild wiederherstellen?')) return;
            $fn('dbFileImageEditor::restore')(this.file_id, log*1+1).then(res=>{
                location.href = location.href.replace(/#.*$/,'');
                //this.el('.-img').src += '?asef='+Math.random();
            });
        }
    }
}

$(function(){
    let el = document.createElement('style');
    el.innerHTML = `
    .c1AccordionHead {
    	position:relative;
    	background-color:var(--cms-light);
    	color:var(--cms-dark);
    	padding:.8em 1.2em .7em 1.2em;
    	cursor:pointer;
    	margin-top:1em;
    	transition:all .1s;
    	font-size:16px;
    	-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    }
    .c1AccordionHead::after {
    	font-family:'qg_cms';
    	content:'\\e800';
    	position:absolute;
    	display: flex;
        align-items: center;
    	right:.8em;
    	top:.7em;
    	bottom:.7em;
    	border-left: 1px solid;
    	padding-left:10px;
    	transition:opacity .2s;
    }
    .c1AccordionHead:hover::after {
    	opacity:1;
    }
    .c1AccordionHead:first-child {
    	margin-top:0;
    }
    .c1AccordionHead:focus {
    	color:#fff;
    	background-color: var(--cms-dark);
    }
    .c1AccordionHead:focus::after {
    	content:'\\e801';
    }
    .c1AccordionHead + div {
    	border:1px solid var(--cms-light);
    	transition-duration: .2s;
    	transition-property: max-height, padding;
    	max-height:0;
    	padding:0 15px;
    	overflow:hidden;
    }
    .c1AccordionHead + div.c1-focusIn,
    .c1AccordionHead:focus + div {
    	max-height:90vh;
    	padding:15px;
    	overflow:auto;
    }
    /* head */
    .c1AccordionHead.-title:first-child {
        background-color: rgb(60, 60, 60);
        color:#fff;
    }
    .c1AccordionHead.-title:first-child:after {
        content: "\\e902";
    }
    `;
    document.head.appendChild(el);
});
