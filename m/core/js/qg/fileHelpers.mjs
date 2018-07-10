/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
window.qgfileUpload = async function(f, name, opt) {
	const fileName = f.name || 'file.'+f.type.replace(/.*\/([^ ;]+).*/,'$1');
	if (f.c1IsImage() && f.size > qgfileUpload.clientResizeSize) {
		const img = await f.c1ToImage();
		await img.c1ScaleToArea(3000*3000);
		img.c1ToBlob(f.type, 1).then(upload);
	} else {
		upload(f);
	}
	function upload(blob) {
		var formData = new FormData();
		formData.append(name, blob, fileName);
		var xhr = new XMLHttpRequest();
		xhr.open('POST', opt.url || location.href, true);
		xhr.upload && (xhr.upload.onprogress = function(e) { opt.progress && opt.progress(e); } );
		xhr.onload = function() {
			opt.complete && opt.complete(xhr.responseText);
		};
		xhr.send(formData);
	}
}
qgfileUpload.clientResizeSize = 6000000;

Blob.prototype.c1IsImage = function() {
	return this.type ? !!this.type.match(/image.*/) : !!this.name.match(/(jpg|jpeg|gif|png)$/i);
};
Blob.prototype.c1ToImage = function(aimg) {
	const imageUrl = URL.createObjectURL(this);
	return new Promise((resolve, reject)=>{
		const image = aimg || document.createElement('img');
	    image.onload = function() {
	        URL.revokeObjectURL(imageUrl);
			image.onload = null;
			resolve(image);
	    };
	    image.src = imageUrl;
	});
};
HTMLImageElement.prototype.c1ScaleToArea = function(area) {
	var scale = function(w,h) {
		var f = Math.sqrt(area/(w*h));
		f = Math.min(f,1);
		return {
			w: Math.floor(w*f),
			h: Math.floor(h*f)
		};
	};
	var dim = scale(this.width,this.height);
	var canvas = document.createElement('canvas');
	canvas.width  = dim.w;
	canvas.height = dim.h;
	canvas.style.display = 'none';
	document.body.appendChild(canvas);
	var ctx = canvas.getContext('2d');
	ctx.drawImage(this,0,0,dim.w,dim.h); // 4. 5. argument needed?
	document.body.removeChild(canvas);
	this.src = canvas.toDataURL();
	return new Promise((resolve, reject)=>{
		setTimeout(resolve,1);
	});
};
HTMLImageElement.prototype._c1ToCanvas = function() {
	var canvas = document.createElement('canvas');
	canvas.width  = this.width;
	canvas.height = this.height;
	canvas.style.display = 'none';
	document.body.appendChild(canvas);
	var ctx = canvas.getContext("2d");
	ctx.drawImage(this,0,0);
	document.body.removeChild(canvas);
	return canvas;
};
HTMLImageElement.prototype.c1ToBlob = function(type, quality) {
	var canvas = this._c1ToCanvas();
	return new Promise((resolve, reject)=>{
		canvas.toBlob(resolve, type, quality);
	});
};

/*
!HTMLCanvasElement.prototype.toBlob && Object.defineProperty(HTMLCanvasElement.prototype, 'toBlob', { // safari
	value: function (callback, type, quality) {
		console.warn('used?');
		var binStr = atob(this.toDataURL(type, quality).split(',')[1]),
		    len = binStr.length,
		    arr = new Uint8Array(len);
		for (var i=0; i<len; i++) {
			arr[i] = binStr.charCodeAt(i);
		}
		callback( new Blob( [arr], {type: type || 'image/png'} ) );
	}
});
*/
