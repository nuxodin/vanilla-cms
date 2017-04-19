/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
cms.initCont('cms.cont.search1',function(el){
	var pid = cms.el.pid(el);
	var $el = $(el);
	$el.find('input[type=search]').on('input',function(){
		$fn('page::loadPart')(pid, 'res', {'search':this.value});
		history.pushState && history.pushState({}, null, updateQueryStringParameter(location.href, 'CmsPage'+pid, encodeURIComponent(this.value) ) );
	}.c1Debounce(200) );
	$el.find('button').hide();
});

function updateQueryStringParameter(uri, key, value) {
  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
  if (uri.match(re)) {
    return uri.replace(re, '$1' + key + "=" + value + '$2');
  } else {
    return uri + separator + key + "=" + value;
  }
}
