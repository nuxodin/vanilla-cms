/* input event polyfill, ie 11 only? */
!function() {
	var d = document
	,hasNativeOnContenteditable
	,hasNativeOnFormFields
	,isNotChar = {16:1,17:1,18:1,20:1,27:1,33:1,34:1,35:1,36:1,37:1,38:1,39:1,40:1,45:1,91:1,255:1}
	,isInput   = {INPUT:1,TEXTAREA:1,SELECT:1};

	function check(e) {
		if (isInput[e.target.tagName]) {
			if (hasNativeOnFormFields) return;
		} else if (e.target.isContentEditable) {
			if (hasNativeOnContenteditable) return;
		} else {
		    return;
		}
        var event = new CustomEvent('input',{bubble:true});
        event.c1Generated = true;
		var target = e.target; //var target = d.activeElement;?
        target.dispatchEvent(event);
	}
	function checkKey(e) {
		if (isNotChar[e.keyCode]) return;
		var char = e.char !== undefined ? e.char : String.fromCharCode(e.keyCode);
		if (char === undefined || char === null) return;
        if (e.keyCode === 46 || char.length) {
        	setTimeout(function() {
        		check(e);
        	});
		}
	}
	var capturing = false;
	function oninput(e) {
		if (e.c1Generated) return;
		if (isInput[e.target.tagName]) {
			hasNativeOnFormFields = true;
		} else {
			hasNativeOnContenteditable = true;
            d.body.removeEventListener('keydown' ,checkKey,capturing);
            d.body.removeEventListener('paste'   ,check   ,capturing);
            d.body.removeEventListener('cut'     ,check   ,capturing);
            d.body.removeEventListener('drop'    ,check   ,capturing); // ie8 has some events only on the body
            d.body.removeEventListener('input'   ,oninput ,capturing);
		}
	}
	d.addEventListener('DOMContentLoaded', function() {
	    d.body.addEventListener('keydown' ,checkKey,capturing);
	    d.body.addEventListener('paste'   ,check   ,capturing);
	    d.body.addEventListener('cut'     ,check   ,capturing);
	    d.body.addEventListener('drop'    ,check   ,capturing);
	    d.body.addEventListener('drop'    ,check   ,capturing);
	    d.body.addEventListener('input'   ,oninput ,capturing);
	});
}();
