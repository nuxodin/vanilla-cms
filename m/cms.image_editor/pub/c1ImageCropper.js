/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
class c1ImageCropper {

    get top()    { return this.position.top; }
    get left()   { return this.position.left; }
    get height() { return this.position.height; }
    get width()  { return this.position.width; }
    get right()  { return this.svg.getBoundingClientRect().width  - (this.position.left + this.position.width);  }
    get bottom() { return this.svg.getBoundingClientRect().height - (this.position.top  + this.position.height); }

    set top(value) {
        let svgRect = this.svg.getBoundingClientRect();
        value = Math.min(svgRect.height - this.position.height, value);
        value = Math.max(0, value);
        this.position.top = value;
        this._drawArea();
    }
    set left(value){
        let svgRect = this.svg.getBoundingClientRect();
        value = Math.min(svgRect.width - this.position.width, value);
        value = Math.max(0, value);
        this.position.left = value;
        this._drawArea();
    }
    set height(value) {
        let svgRect = this.svg.getBoundingClientRect();
        value = Math.max(value, this.minHeight || 60);
        value = Math.min(value, svgRect.height  - this.position.top);
        if (this.aspectRatio) this.position.width = value * this.aspectRatio;
        this.position.height = value;
        this._drawArea();
    }
    set width(value) {
        let svgRect = this.svg.getBoundingClientRect();
        value  = Math.max(value, this.minWidth  || 60);
        value  = Math.min(value, svgRect.width - this.position.left);
        if (this.aspectRatio) this.position.height = value * (1/this.aspectRatio);
        this.position.width  = value;
        this._drawArea();
    }
    _drawArea(){
        requestAnimationFrame(()=>{
            this.area.setAttribute('y',     this.position.top);
            this.area.setAttribute('x',     this.position.left);
            this.area.setAttribute('height',this.position.height);
            this.area.setAttribute('width', this.position.width);
            this.trigger('crop')
            this.positionizeNobs();
        });
    }
    constructor(image){
        this.image = image;
        this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        this.svg.classList.add('c1ImageCropper-Svg');
        this.svg.style.position = 'absolute';
        this.svg.innerHTML =
        '<style>'+
        '.c1ImageCropper-Svg .-nob {opacity:.6}'+
        '.c1ImageCropper-Svg:hover .-nob {opacity:1}'+
        '</style>'+
        '<defs>'+
           '<mask id="maskX">'+
                '<rect fill="#fff" x="0" y="0" width="9100" height="9100"></rect>'+
                '<rect class=-area x="0" y="0" width="0" height="0"></rect>'+
           '</mask>'+
        '</defs>'+
        '<rect mask="url(#maskX)" fill="rgba(0,0,0,.5)" x="0" y="0" width="9100" height="9100"></rect>';
        ['nw','n','ne','e','se','s','sw','w'].forEach(pos=>{
            let nob = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            nob.style.cursor = pos+'-resize';
            nob.classList.add('-nob');
            for (let i of pos) nob.setAttribute('data-manipulate-'+i, true);
            pos.length > 1 && nob.setAttribute('data-manipulate-edge', true);
            nob.setAttribute('width',26);
            nob.setAttribute('height',26);
            nob.setAttribute('fill','#fff');
            nob.setAttribute('stroke','#000');
            this.svg.appendChild(nob);
        });
        this.position = {top:0,left:0,height:0,width:0};
        this.area = this.svg.querySelector('.-area');
        c1.c1Use('pointerObserver',Observer=>{
            let observer = new Observer(this.svg);
            let started = null;
            let aspectRatio = null;
            let cropper = this;
            let createNew = false;
            observer.onstart = function(e){
                e.preventDefault();
                started = e.target;
                aspectRatio = started.hasAttribute('data-manipulate-edge') ? cropper.width / cropper.height : false;
                createNew = false;
                if (started.classList.contains('-nob')) return;
                let inside = e.layerX > cropper.left && e.layerX < cropper.left + cropper.width && e.layerY > cropper.top && e.layerY < cropper.top + cropper.height;
                if (!inside) { // not Inside
                    createNew = true;
                    cropper.top    = e.layerY;
                    cropper.left   = e.layerX;
                    cropper.width  = 0;
                    cropper.height = 0;
                }
            }
            observer.onmove = function(e){
                e.preventDefault();
                let x      = cropper.left;
                let y      = cropper.top;
                let width  = cropper.width;
                let height = cropper.height;
                let diffX  = this.diff.x;
                let diffY  = this.diff.y;
                if (started.classList.contains('-nob') || createNew) {
                    let distance = Math.sqrt(this.diff.x*this.diff.x + this.diff.y*this.diff.y);
                    let speed = this.diff.time / distance; // miliseconds per pixel
                    if (speed > 6) { diffY = diffY/5; diffX = diffX/5; }
                    if (started.getAttribute('data-manipulate-e') || createNew) {
                        cropper.width = width + diffX;
                        if (aspectRatio && !e.shiftKey) cropper.height = cropper.width / aspectRatio;
                    }
                    if (started.getAttribute('data-manipulate-s') || createNew) {
                        cropper.height = height + diffY;
                        if (aspectRatio && !e.shiftKey) cropper.width = cropper.height * aspectRatio;
                    }
                    if (started.getAttribute('data-manipulate-w')) {
                        cropper.width = width - diffX;
                        if (diffX > 0) diffX = width - cropper.width; // calculate effective diff
                        cropper.left =  x + diffX;
                        if (aspectRatio && !e.shiftKey) cropper.height = cropper.width / aspectRatio;
                    }
                    if (started.getAttribute('data-manipulate-n')) {
                        cropper.height = height - diffY;
                        if (diffY > 0) diffY = height - cropper.height;
                        cropper.top = y + diffY;
                        if (aspectRatio && !e.shiftKey) cropper.width = cropper.height * aspectRatio;
                    }
                } else {
                    cropper.top  = y + diffY;
                    cropper.left = x + diffX;
                }
            }
        })
    }
    positionizeSvg(){
        let pos = this.image.getBoundingClientRect();
        this.svg.style.top    = (pos.top +pageYOffset)+'px';
        this.svg.style.left   = (pos.left+pageXOffset)+'px';
        this.svg.style.width  = pos.width+'px';
        this.svg.style.height = pos.height+'px';
    }
    positionizeNobs(){
        let width  = this.width;
        let height = this.height;
        let x = this.left;
        let y = this.top;
        let all = this.svg.querySelectorAll('.-nob');
        for (let i=0,el; el=all[i++];) {
            let myX = x + width/2  - 15;
            let myY = y + height/2 - 15;
            if (el.hasAttribute('data-manipulate-n')) myY -= height/2;
            if (el.hasAttribute('data-manipulate-e')) myX += width/2;
            if (el.hasAttribute('data-manipulate-s')) myY += height/2;
            if (el.hasAttribute('data-manipulate-w')) myX -= width/2;
            el.setAttribute('x', myX);
            el.setAttribute('y', myY);
        }
    }
    show(){
        let rect = this.image.getBoundingClientRect();
        this.positionizeSvg();
        document.body.appendChild(this.svg);
        this.left   = rect.width  * .1;
        this.top    = rect.height * .1;
        if (this.aspectRatio > rect.width / rect.height) {
            this.height = rect.height * .8;
            this.width  = rect.width  * .8;
        } else {
            this.width  = rect.width  * .8;
            this.height = rect.height * .8;
        }
        this.svg.c1ZTop();
        addEventListener('resize',this);
        this.trigger('show')
    }
    hide(){
        this.svg.remove();
        removeEventListener('resize',this);
        this.trigger('hide')
    }
    hidden(){ return !this.svg.parentNode; }
    toggle(){ this.hidden() ? this.show() : this.hide(); }
    handleEvent(e){
        if (e.type !== 'resize') return
        this.positionizeSvg();
        this.positionizeNobs();
        this.svg.c1ZTop();
    }
}
Object.assign(c1ImageCropper.prototype, c1.Eventer);





/* polyfills */

/* bug in webkit / chrome */
/* https://bugs.webkit.org/show_bug.cgi?id=86010 */
SVGElement.prototype.c1GetTransformToElement = function(toElem) {
	return toElem.getScreenCTM().inverse().multiply(this.getScreenCTM());
};

/* */
Object.defineProperty(SVGElement.prototype, 'innerHTML', { // needed eaven if immerHTML is present (firefox)
	get() {
		var temp, i=0, children, child;
		temp = document.createElement('div');
		children = this.cloneNode(true).childNodes;
		for (;child = children[i++];) {
		    temp.appendChild(child);
		}
		return temp.innerHTML;
	},
	set(markup) {
		let div= document.createElement('div');
        div.innerHTML = "<svg xmlns='http://www.w3.org/2000/svg'>" + markup + "</svg>";
        let children = div.firstChild.childNodes, child;
		while (this.firstChild) this.firstChild.remove();
		while (child=children[0]){
			child.tagName && this.appendChild(child); // why child.tagName?
		}
	},
	enumerable: false,
	configurable: true
});
/* */
!function(){
	function copyProperty(prop, from, to){
		var desc = Object.getOwnPropertyDescriptor(from, prop);
		Object.defineProperty(to, prop, desc);
	}
	if ('classList' in HTMLElement.prototype && !('classList' in Element.prototype)) {  // ie11
		copyProperty('classList', HTMLElement.prototype, Element.prototype);
	}
	if ('children' in HTMLElement.prototype && !('children' in Element.prototype)) {
		// bug, webkit, chrome, ie has not children on the prototype
		copyProperty('children', HTMLElement.prototype, Element.prototype);
	}
	if ('contains' in HTMLElement.prototype && !('contains' in Element.prototype)) { // ie11
		copyProperty('contains', HTMLElement.prototype, Element.prototype);
	}
	if ('getElementsByClassName' in HTMLElement.prototype && !('getElementsByClassName' in Element.prototype)) { // ie11
		copyProperty('getElementsByClassName', HTMLElement.prototype, Element.prototype);
	}
}();
/* */
