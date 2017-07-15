/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
// todo? // externe Seiten http://github.com/codepo8/GooHooBi/blob/master/multisearch.html
'use strict';
{
	let urlRegexp = /^[a-zA-Z0-9-]{2,999}\.[a-z0-9]{2,10}/;
	let mailRegexp = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,10})+$/;

	let inp = c1.dom.fragment('<input placeholder=url spellcheck=false type=qgcms-page>').firstChild;
	let end = function() {
		let el = Rte.element.closest('a');
		if (!el) return;

		qgSelection.toElement(el);
		qgSelection.collapse();

		let v = inp.value;
		if (v.trim() === '') {
			el.removeNode();
			Rte.trigger('input');
			return;
		} else if (!isNaN(v)) {
			v = 'cmspid://'+v;
		} else if (v.match(mailRegexp)) {
			v = 'mailto:'+v;
		} else if (v.match(urlRegexp)) {
			v = 'http://'+v;
		}
		inp.value = v;
		el.setAttribute('href',v);
		if (!el.hasAttribute('target')) {
			el.setAttribute('target', v.match(/^(cmspid|mailto)/) ? '_self' : '_blank');
		}
		Rte.active && Rte.active.focus(); // always false? needed?
		Rte.trigger('input');
	};
	inp.addEventListener('blur',end);
	inp.addEventListener('keyup',e => e.which === 13 && end() );

	Rte.ui.setItem('LinkInput', {
		el: inp,
		enable: 'a, a > *',
		check(el) {
			el = el.closest('a');
			let v = el.getAttribute('href');
			if (v) inp.value = v;
		}
	});

	Rte.ui.setItem('Link', {
		click() {
			let cEl = Rte.element;
			let exists = cEl && cEl.closest('a');
			if (exists) {
				exists.removeNode();
			} else {
				let el = qgSelection.surroundContents(document.createElement('a')); // todo: selection on multiple elements
				Rte.element = 0; // force rte-event "elementchange"!
				// set initial value
				const txt = el.textContent.trim();
				if (txt.match(/^http[^\s]+$/) || txt.match(urlRegexp) || txt.match(mailRegexp)) {
					inp.value = txt;
					el.setAttribute('href',txt);
				} else {
					$fn('cms::searchPagesByTitle')(txt).run(res=>{
						if (!res[0]) return;
						inp.value = txt;
						inp.dispatchEvent(new Event('input'));
					});
				}
				setTimeout(()=>inp.focus(),1); // todo: why timeout?
			}
		},
		check(el) { return el && el.matches('a, a > *'); },
		shortcut: 'k'
	});


	/* todo */
	let externMediaDialog = function(txtEl,medias) {
		cms.txtIdToPid(txtEl.getAttribute('cmstxt'), function(pid) {

			c1.c1Use('dialog',function(){
				let dialog = new c1.dialog({
					class:'qgCMS',
					title:'Externe Medien',
					body: 'Welche Dateien möchtest du auf deinen Server kopieren? <br><br>'+
							'<div class=-checkkboxes></div>'+
							'<style>.cmsExtMediaHighlight {outline: 6px solid #fa0} </style>',
					buttons: [{
						title:'fertigstellen',then(){
							for (let [uri,media] of Object.entries(medias)) {
								if (media.checked) {
									$fn('page::FileAdd')(pid,uri).run(v=>{
										if (!v.url) return;
										for (let el of media.els) {
											let att = el.hasAttribute('src') ? 'src' : 'href';
											el.setAttribute(att, v.url+'/'+media.basename);
										}
										txtEl.dispatchEvent(new Event('input',{'bubbles':true}));
										txtEl.focus();
									});
								} else {
									for (let el of media.els) el.classList.add('externMedia')
								}
							}
						}
					}]
				});
				for (let [uri,media] of Object.entries(medias))  {
					let file = new URL(uri).pathname.replace(/.*\//,'');
					let label = c1.dom.fragment('<label style="display:block; padding:2px 6px"><input type=checkbox checked> '+media.basename+'</label>').firstChild;
					label.addEventListener('mouseover', ()=> media.els.forEach(el=>el.classList.add('cmsExtMediaHighlight')) );
					label.addEventListener('mouseleave',()=> media.els.forEach(el=>el.classList.remove('cmsExtMediaHighlight')) );
					label.c1Find('input').addEventListener('change',function(){ media.checked = this.checked; });
					dialog.element.c1Find('.-checkkboxes').append(label);
				}
				dialog.show();
			});

		})
	};
	let checkMedia = function(root) {
		let medias = {}; let has=0;
		for (let el of root.c1FindAll('a, img')) {
			if (el.classList.contains('externMedia')) continue;
			for (let attr of ['src','href']) {
				if (!el.hasAttribute(attr)) continue;
				let uri = new URL(el[attr]);
				if (location.host === uri.host) continue;
				let ext = uri.pathname.replace(/.*\./,'');
				if (!ext) continue;
				if (el.tagName === 'IMG' || ['pdf','doc','xls','jpg','png','gif'].includes(ext)) {
					if (!medias[uri]) medias[uri] = {els:[]};
					medias[uri].els.push(el);
					medias[uri].basename = uri.pathname.replace(/.*\//,'');
					medias[uri].checked = true;
					has=1;
				}
			}
		}
		has && externMediaDialog(root,medias);
	};
	document.addEventListener('paste',function(e){
		if (!e.target.contentEditable) return;
		let txtEl = e.target.closest('[cmstxt]');
		if (!txtEl) return;
		setTimeout(()=>checkMedia(txtEl));
	});

	/* dbfile */
	document.addEventListener('qgResize',e=>{
		let el = e.target;
		if (!el.isContentEditable) return;
		if (el && el.tagName === 'IMG' && el.src.match(/dbFile\//)) {
			let width = el.width;
			let height = el.height;
			let ratio = width / height;
			el.setAttribute('data-c1-ratio', ratio);
			el.style.setProperty('--c1-ratio', ratio);
			el.style.maxWidth = '100%';
			new dbFile(el).set('w',width).set('h',height).set('max', 0);
			el.style.width = el.style.height = '';
			el.removeAttribute('height');
			el.setAttribute('width',width);
			if (el.style.display === 'inline-block') el.style.display = '';
			Rte.trigger('input');
			/* problem will save height on unload
			setTimeout(function(){ // set Height after save
				el.style.height = height+'px';
				$(el).one('load', function(){ // after load set height to auto
					el.style.height = '';
					$(el).trigger('input'); // save again, needed?
				})
			});
			*/
		}
	});

	// dbclick zoomer
    function makeProportional(muster, dim) {
        if ((!dim.h || muster.w/muster.h > dim.h/dim.w) && dim.w != 0) {
            dim.h = Math.round( (muster.h / muster.w) * dim.w );
        } else {
            dim.w = Math.round( (muster.w / muster.h) * dim.h );
        }
    }
    addEventListener('dblclick', function(e) {
        let img = e.target;
        if (img.isContentEditable && img.tagName === 'IMG' && img.src.match(/\/dbFile/)) {
            e.stopPropagation();
            e.preventDefault();

            let zoomImg = new Image();
            let clip = {};
            zoomImg.src = img.src.replace(/([a-z]+)-([^\/]*)\//g,function(match, name, value) {
                switch (name) {
                    case 'w': case 'h': case 'vpos': case 'hpos': case 'zoom':
                        clip[name] = parseFloat(value);
                        return '';
                    default: return match;
                }
            });
            zoomImg.onload = function() {
                let Zoomer = new ImageZoomer(zoomImg);
                Zoomer.activate();
                let change = function() {
                    let vpos = Zoomer.y / ( Zoomer.img.height - Zoomer.h ) || 0;
                    let hpos = Zoomer.x / ( Zoomer.img.width  - Zoomer.w ) || 0;
                    new dbFile(img).set( 'vpos', vpos*100 ).set( 'hpos', hpos*100 ).set( 'zoom', Zoomer.factor() );
					img.dispatchEvent(new Event('qgResize',{bubbles:true}));
                };
                Zoomer.on('change',change.c1Debounce(500));
				let pos = img.getBoundingClientRect();
				let left = pos.left + pageXOffset;
		        let top  = pos.top  + pageYOffset;
                Zoomer.canvas.style.cssText = 'outline:3px solid red; cursor:move; position:absolute; top:'+top+'px; left:'+left+'px';
                Zoomer.setDimension( pos.width, pos.height );

                /* set clip */
                let f = clip.zoom || Math.max( Zoomer.img.height/Zoomer.ctx.height, Zoomer.img.width/Zoomer.ctx.width );
                Zoomer.w = pos.width  * f;
                Zoomer.h = pos.height * f;
                Zoomer.x = (clip.hpos/100) * (Zoomer.img.width  - Zoomer.w ) || 0;
                Zoomer.y = (clip.vpos/100) * (Zoomer.img.height - Zoomer.h ) || 0;

                Zoomer.draw();

                let deactivate = function() {
                    document.body.removeChild(Zoomer.canvas);
                    document.removeEventListener('mousedown', deactivate);
                    change();
                };
                document.addEventListener('mousedown', deactivate);
            };
        }
    });

    function ImageZoomer(img) {
        this.img = img;
        this.canvas = document.createElement('canvas');
        this.canvas.setAttribute('tabindex','0');
        this.ctx = this.canvas.getContext("2d");
        this.setDimension(img.width,img.height);
        document.body.appendChild(this.canvas);
    }
    ImageZoomer.prototype = {
        x: 0,
        y: 0,
        w: 100,
        h: 100,
        f: 1,
        setDimension(w,h) {
            this.ctx.width = this.canvas.width = w;
            this.ctx.height = this.canvas.height = h;
        },
        factor() {
            return this.w / this.ctx.width;
        },
        activate() {
            let self = this;
            self.canvas.addEventListener('wheel', function(e) {
                eventStop(e);
                let oldF = self.factor();
                let f = oldF * wheelIntervalToFaktor(e);
                f = Math.min( self.img.height/self.ctx.height, self.img.width/self.ctx.width,  f ); // limit
                f = Math.max(1,f);

                let offset = self.mouseOffsetCloserToCenter(e);

                /* offset transformed to image */
                self.x = oldF * offset.x + self.x;
                self.y = oldF * offset.y + self.y;

                self.w = self.ctx.width  * f;
                self.h = self.ctx.height * f;
                self.x = self.x - (self.w/2);
                self.y = self.y - (self.h/2);

                self.draw();
            });

            let mousePos = {};
            self.canvas.addEventListener('mousedown', e => {
                e.preventDefault();
                e.stopPropagation();
                mousePos = {x: e.pageX, y: e.pageY};
                document.addEventListener('mousemove', move);
                document.addEventListener('mouseup', up);
            });
            function move(e) {
                let f = self.factor();
                let diff = {x: mousePos.x - e.pageX, y: mousePos.y - e.pageY};
                mousePos = {x: e.pageX, y: e.pageY};
                self.w = self.ctx.width  * f;
                self.h = self.ctx.height * f;
                self.x = self.x + diff.x;
                self.y = self.y + diff.y;
                self.draw();
            }
            function up() {
                document.removeEventListener('mousemove', move);
                document.removeEventListener('mouseup', up);
            }
        },
        draw() {
            this.x = limit(this.x, 0, this.img.width  - this.w );
            this.y = limit(this.y, 0, this.img.height - this.h );
            this.ctx.clearRect(0, 0, this.ctx.width, this.ctx.height);
            this.ctx.drawImage(this.img, this.x, this.y, this.w, this.h, 0, 0, this.ctx.width, this.ctx.height);
            this.trigger('change');
        },
        mouseOffsetCloserToCenter(e) {
            /* real offset on canvas */
            let x = e.offsetX;
            let y = e.offsetY;
            return {
                x: ( x + 2*this.ctx.width  ) / 5, //(x+4*xhalbe durch 5)
                y: ( y + 2*this.ctx.height ) / 5
            };
        }
    };
    c1.ext(c1.Eventer, ImageZoomer.prototype);

    /*******************************/
    /* helpers *********************/
    /*******************************/

    function limit(number, min, max) {
    	return Math.min(max, Math.max(number, min) );
    }
    let lastTime = 0;
    function wheelIntervalToFaktor(e) {
    	// intervall diff
        let time = e.timeStamp;
        let diff = time-lastTime;
    	if (!e._eventChecked) {
            lastTime = time;
            e._eventChecked = true;
    	}
    	// faktor
    	let max = 400;
    	let min = 10;
    	diff = limit(diff, min, max+min);
    	let x = (diff - min) / max; // range from 1 to 0
    	x = 1-x;
    	x = x*x*x;
    	x = 1-x;
    	x = 0.7 + (0.3 * x); // range from 0.7 to 1.0;
    	x = Math.min( x, 0.998 );

    	x = e.deltaY > 0 ? x : 1/x; // up or down?
    	return x;
    }
    function eventStop(e) {
    	e.stopPropagation();
    	e.preventDefault();
    }
}
