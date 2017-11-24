/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
cms.initCont('cms.cont.search1',function(el){
	var pid = cms.el.pid(el);
	el.c1Find('input[type=search]').addEventListener('input',function(){
		$fn('page::loadPart')(pid, 'res', {'search':this.value});
		var url = updateQueryStringParameter(location.href, 'CmsPage'+pid, encodeURIComponent(this.value) );
		history.pushState({}, null, url);
	}.c1Debounce(200));
	el.c1Find('button').style.display = 'none';

	function updateQueryStringParameter(uri, key, value) {
		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		var separator = uri.indexOf('?') !== -1 ? "&" : "?";
		if (uri.match(re)) {
			return uri.replace(re, '$1' + key + "=" + value + '$2');
		} else {
			return uri + separator + key + "=" + value;
		}
	}

});

