c1.c1Use('dom',function(){

	var byTouch = false;

    function listen(e){

		if (e.type === 'mousedown' && byTouch) return;

		var nav_root = e.target.closest('#nav');
        if (nav_root) return;
        var btn = e.target.closest('.mob_nav_btn');
        var action = btn?'toggle':'remove';
        document.body.classList[action]('mob_nav_open');

		btn && e.preventDefault();

		if (e.type === 'touchstart') {
			byTouch = true;
			setTimeout(function(){ byTouch = false; },900);
		}

    }
	document.addEventListener('mousedown',listen);
	document.addEventListener('touchstart',listen);
});
