'use strict';

window.qgfileUpload = function(f, name, opt) {
	const fileName = f.name || 'file.'+f.type.replace(/.*\/([^ ;]+).*/,'$1');
	if (f.q9IsImage() && f.size > qgfileUpload.clientResizeSize) {
		f.q9ToImage(img=>{
			img.q9ScaleToArea(3000*3000, ()=>{
				img.q9ToBlob(upload, f.type, 1);
			});
		});
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
};
qgfileUpload.clientResizeSize = 6000000;

Blob.prototype.q9IsImage = function() {
	return this.type ? !!this.type.match(/image.*/) : !!this.name.match(/(jpg|jpeg|gif|png)$/i);
};
Blob.prototype.q9ToImage = function(cb,aimg) {
	var imageUrl = URL.createObjectURL(this);
	var image = aimg || document.createElement("img");
    image.onload = function() {
        URL.revokeObjectURL(imageUrl);
		cb && cb(image);
		image.onload = null;
    };
    image.src = imageUrl;
};
HTMLImageElement.prototype.q9ScaleToArea = function(area, cb) {
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
	var ctx = canvas.getContext("2d");
	ctx.drawImage(this,0,0,dim.w,dim.h); // 4. 5. argument needed?
	document.body.removeChild(canvas);
	this.src = canvas.toDataURL();
	setTimeout(cb,1);
};
HTMLImageElement.prototype.q9ToCanvas = function() {
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
HTMLImageElement.prototype.q9ToBlob = function(cb, type, quality, name) {
	var canvas = this.q9ToCanvas();
	canvas.toBlob(cb, type, quality);
};

!HTMLCanvasElement.prototype.toBlob && Object.defineProperty(HTMLCanvasElement.prototype, 'toBlob', { // safari
	value: function (callback, type, quality) {
		var binStr = atob(this.toDataURL(type, quality).split(',')[1]),
		    len = binStr.length,
		    arr = new Uint8Array(len);
		for (var i=0; i<len; i++) {
			arr[i] = binStr.charCodeAt(i);
		}
		callback( new Blob( [arr], {type: type || 'image/png'} ) );
	}
});
