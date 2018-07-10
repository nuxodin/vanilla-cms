/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
class c1ImageEditor extends c1FullScreenPopup {
    construct(){ // constructor!?!?
        this.minHeight = 0;
        this.minWidth  = 0;
    }
    show(src, options){
        c1.c1Use('form',1);
        this.options = options;
        this.init();
        let img     = this.el('.-img');
        let canvas  = this.el('.-canvas');

        this.cropper = new c1ImageCropper(this.el('.-canvas'));
        this.cropper.on('show',()=>{
            this.el('.-tools').style.display     = 'none';
            this.el('.-btns').style.display      = 'none';
            this.el('.-toolsCrop').style.display = 'block';

            this.cropper.positionizeSvg();
        });
        this.cropper.on('hide',()=>{
            this.el('.-tools').style.display     = 'block';
            this.el('.-btns').style.display      = 'block';
            this.el('.-toolsCrop').style.display = 'none';
        });
        this.cropper.on('crop',()=>{
            for (let prop of ['top','left','width','height']) {
                let el = this.el('.-cropValues [name='+prop+']');
                if (el === document.activeElement) continue;
                el.value = Math.round(this.cropper[prop] * this.scale);
            }
        });

        img.onload = ()=>{
            options.onload && options.onload();
            canvas.width  = img.naturalWidth;
            canvas.height = img.naturalHeight;
            this.initSeriously();
            this.resetCropper();
            URL.revokeObjectURL(img.src);
        };
        img.onerror = e=>{
            alert('Das Bild konnte nicht geladen werden, oder ist nicht vorhanden. Klicken Sie auf "hochladen" um ein Bild von Ihrem Computer auszuwählen.');
            options.onerror && options.onerror();
        };
        img.src = src;
        this.el('.-save').onclick = ()=>{
            this.changed = false;
            options.onsave();
        };
        super.show();
        addEventListener('resize',this);
    }
    hide(){
        if (this.changed && !confirm('Möchten Sie die Änderungen verwerfen?')) return;
        super.hide();
        this.cropper.hide();
        removeEventListener('resize',this);
    }
    async uploadDialog(){
        const [file] = await c1.form.fileDialog({multiple:false, accept:'image/*'});
        if (!file) return;
        if (!file.type.match('image.*')) return;
        if (file.size > 8000000) {
            //await c1Use(sysURL+'core/js/qg/fileHelpers.js',1)
            await c1.import(sysURL+'core/js/qg/fileHelpers.mjs');
            const img = await file.c1ToImage();
            await img.c1ScaleToArea(2000*3000);
            const blob = await img.c1ToBlob(file.type, 1);
            this.el('.-img').src = URL.createObjectURL(blob);
        } else {
            this.el('.-img').src = URL.createObjectURL(file);
        }
    }
    init(){
        this.el().classList.add('c1ImageEditor');
		var pattern =
		'background-color: #fff; '+
		'background-image: linear-gradient(45deg, #d8d8d8 25%, transparent 25%, transparent 75%, #d8d8d8 75%, #d8d8d8), linear-gradient(45deg, #d8d8d8 25%, transparent 25%, transparent 75%, #d8d8d8 75%, #d8d8d8);'+
		'background-size:20px 20px; '+
		'background-position:0 0, 10px 10px; '
        this.el().innerHTML =
        '<div style="display:flex; flex:1 1 auto;">'+
            '<div class=-viewport style="position:relative; flex:1 1 auto; background:#000">'+
                '<div style="display:flex; justify-content:center; position:absolute; top:0; left:0; right:0; bottom:0; overflow:auto; padding:20px;">'+
                    '<canvas class=-canvas style="background:#bbb; display:block; box-shadow:0 0 40px #888; margin:auto; max-height:100%; max-width:100%; '+pattern+'"></canvas>'+
                    '<img class=-img style="display:none; margin:auto; max-height:100%; max-width:100%">'+
                '</div>'+
            '</div>'+
            '<div class=-sidebar style="min-width:280px; padding:20px; display:flex; flex-flow:column">'+
                '<div class=-tools style="flex:auto; overflow:auto">'+
                    '<div class=-title>Bild bearbeiten</div>'+
                    '<br>'+
                    '<button class=-rotate>90° drehen</button> '+
                    '<button class=-crop>zuschneiden</button> '+
                    '<button class=-upload>hochladen</button> '+
                    '<div class=c1AccordionHead tabindex=-1>Einstellen</div>'+
                    '<div>'+
                        '<div>Helligkeit</div>'+
                        '<input class=-brightness type=range style="width:100%" min=.4 max=2 step=any value=1>'+
                        '<div>Kontrast</div>'+
                        '<input class=-contrast type=range style="width:100%" min=.4 max=2 step=any value=1>'+
                    '</div>'+
                '</div>'+
                '<div class=-toolsCrop style="flex:auto; overflow:auto" hidden>'+
                    '<div class=-title>Bild zuschneiden</div>'+
                    '<br>'+
                    '<div style="display:flex;">'+
                        '<button class=-cancelCrop style="flex:1">abbrechen</button> '+
                        '<span style="flex:.1 1 8px;"></span> '+
                        '<button class=-cropit     style="flex:1">zuschneiden</button> '+
                    '</div>'+
                    '<br>'+
                    '<form class=-cropValues>'+
                        '<table style="width:100%"><tbody style="vertical-align:middle">'+
                            '<tr> <td> X:      <td> <input name=top    type=number style="width:100%"> '+
                            '<tr> <td> Y:      <td> <input name=left   type=number style="width:100%"> '+
                            '<tr> <td> Breite: <td> <input name=width  type=number style="width:100%"> '+
                            '<tr> <td> Höhe:   <td> <input name=height type=number style="width:100%"> '+
                        '</table>'+
                    '</form>'+
                '</div>'+
                '<div class=-btns style="flex:0">'+
                    '<div style="display:flex; margin:-10px">'+
                        '<button style="flex:1; margin:10px" class=-save>Speichern</button> '+
                        '<button style="display:none; flex:1; margin:10px" class=-cancel>Abbrechen</button>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>';
        this.el('.-cancel').addEventListener('click',()=>this.hide());
        this.el('.-upload').addEventListener('click',()=>this.uploadDialog());
        this.el('.-cancelCrop').addEventListener('click',()=>this.cropper.hide());
    }
    async initSeriously(){
        if (this.initialized) return;
        var run = ()=>{
            let self = this;
            this.initialized = true;
            let seriously = new Seriously();
            let target    = seriously.target(this.el('.-canvas'));

            this.el('.-img').addEventListener('load',()=>{
                target.width  = this.el('.-img').naturalWidth;
                target.height = this.el('.-img').naturalHeight;
                this.el('.-brightness').value = effect.brightness = 1;
                this.el('.-contrast'  ).value = effect.contrast   = 1;
                crop.top = crop.left = crop.bottom = crop.right  = 0;
                rotate.rotation = 0;
                this.changed = true;
            });

            // effect
			let effect    = seriously.effect('brightness-contrast');

			effect.source = seriously.source(this.el('.-img'));
            this.el('.-brightness').addEventListener('input',function(){ effect.brightness = this.value; self.changed = true; }.c1Debounce(10));
            this.el('.-contrast'  ).addEventListener('input',function(){ effect.contrast   = this.value; self.changed = true; }.c1Debounce(10));

            // crop
            this.el('.-crop').addEventListener('click',()=>{
                this.cropper.toggle();
            });
            let crop = seriously.effect('crop');
			crop.source = effect;

            let cropit = ()=>{
                crop.top    = this.scale * this.cropper.top;
    			crop.left   = this.scale * this.cropper.left;
                crop.bottom = this.scale * this.cropper.bottom;
                crop.right  = this.scale * this.cropper.right;
                this.cropper.hide();
            };
            this.cropper.svg.addEventListener('dblclick',cropit);
            this.el('.-cropit').addEventListener('click',cropit);
            this.el().addEventListener('keydown',e=>{
                if (e.which === 13 && this.cropper.svg.parentNode) {
                    cropit();
                    this.el().focus(); // prevent (activated) button from trigger click again
                }
            });
            this.el('.-cropValues').addEventListener('input',(e=>{
                this.cropper[e.target.name] = e.target.value / this.scale;
            }).c1Debounce(200));


			crop.on('resize', ()=>{
                if (crop.top === 0 && crop.left === 0 && crop.bottom === 0 && crop.right === 0) return;
                target.width  = crop.width;
                target.height = crop.height;
                rewriteImgSource();
			});

            let rewriteImgSource = function(){
                //self.el('.-img').src = self.el('.-canvas').toDataURL();
                self.el('.-sidebar').style.opacity = .4;
                self.el('.-sidebar').style.pointerEvents = 'none';
                self.el('.-canvas').toBlob(function(result){
                    self.el('.-sidebar').style.opacity = '';
                    self.el('.-sidebar').style.pointerEvents = '';
                    self.el('.-img').src = URL.createObjectURL(result);
                });
            }.c1Debounce(100);

            // rotate
            let rotate = seriously.transform('2d');
            rotate.source = crop;
            this.el('.-rotate').addEventListener('click',()=>{
                let canvas = this.el('.-canvas');
                let w = target.width;
                target.width  = target.height;
                target.height = w;
                rotate.rotation += 90;
                rewriteImgSource();
            });

            // go
			target.source = rotate;
			seriously.go();
        }
        var baseUrl = sysURL+'cms.image_editor/pub/Seriously.js/';
        await c1Use(baseUrl+'lib/require.js',1);
        require([
            baseUrl+'seriously.js',
        ], ()=>{
            require([
                baseUrl+'effects/seriously.brightness-contrast.js',
                baseUrl+'effects/seriously.crop.js',
            ], run);
        });
    }
    handleEvent(e) {
        if (e.type !== 'resize') return;
        this.resetCropper();
    }
    resetCropper(){
        let canvas  = this.el('.-canvas');
        this.scale = canvas.width / canvas.clientWidth;
        this.cropper.minHeight = this.minHeight / this.scale;
        this.cropper.minWidth  = this.minWidth  / this.scale;
    }
}
