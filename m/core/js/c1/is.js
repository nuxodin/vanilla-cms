c1Use.able(c1,'is');
c1.onElement('[c1-is]',function(el){
	var is = el.getAttribute('c1-is');
	var multiple = is.split(/\s+/);
    //var lib = c1.is.c1Use(is); // blocking
    //lib(el);
	c1Use(multiple, function(lib){
		for (var arg = 0; arg < arguments.length; ++ arg) {
			var is = arguments[arg];
			if (is.c1UseFailed) continue;
			is(el);
		}
	})
});
