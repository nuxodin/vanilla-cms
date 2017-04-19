function scrollOffset(){
    return document.getElementById('head').offsetHeight - 1;
}
!function() {

    // initial go to hash
    function gotoHash(){
        var el = document.getElementById(location.hash.substring(1));
        if (!el) return;
        var top = el.getBoundingClientRect().top + pageYOffset - scrollOffset();
        document.documentElement.scrollTop = document.body.scrollTop = top;
    }
    requestAnimationFrame(gotoHash);
    document.addEventListener('DOMContentLoaded',function(){
        gotoHash();
        setTimeout(gotoHash);
        var int = setInterval(gotoHash,1);
        setTimeout(function() { clearInterval(int) },200);
    });

    // a clicks
    var lastHashSet = null;
    document.addEventListener('click', function(e){
        var el = e.target.closest('a');
        if (!el) return;
        var href = e.target.href;
        if (!href) return;
        var parts = href.split('#');
        if (!parts[1]) return;
        var path = parts[0];
        var id = parts[1];
        var actualParts = location.href.split('#');
        if (actualParts[0] !== path) return;
        var el = document.getElementById(id);
        if (!el) return;
        scrollToEl(el, function(e){
            lastHashSet = '#'+id;
            el.removeAttribute('id'); // tricky hack! (ie only needed?)
            location.hash = id; // ie10 does not preventDefault :(
            el.setAttribute('id',id);
        });
        e.preventDefault();
    })

    // hash change
	window.addEventListener('hashchange',function(e){
		var hash = location.hash;
		if (lastHashSet === hash) return;
		lastHashSet = hash;
		var el = document.getElementById(hash);
		if (!el || !el.offsetWidth) return;
		scrollToEl(el);
		e.preventDefault();
	});

    // scroll
	function scrollToEl(el, cb) {
		if (!el) return;
        var top = el.getBoundingClientRect().top + pageYOffset - scrollOffset();
		$('html, body').animate({
		    scrollTop: top
		},{
			queue: false,
			duration: 250,
			done: function() {
				setTimeout(function() { $('html,body').scrollTop(top); },160); // window phone 8
				cb && cb();
			}
		});
	}

    /* mark link */
    var listen = function(e){
		var best = null;
		var winHeight = window.innerHeight * 0.5; // obere 50%
		var winner = null;
		var els = document.body.getElementsByClassName('qgCmsCont');
		for (var i=0,el; el=els[i++];) {
			if (!el.hasAttribute('id')) continue;
			var pos = el.getBoundingClientRect();
			var bottom = Math.min( pos.bottom, winHeight );
			var top = Math.max( pos.top, 0 );
			var visible = bottom-top;
			if (best===null || visible > best) {
				winner = el;
				best = visible;
			}
		}
		els = document.body.getElementsByClassName('scrollSection');
		for (i=0,el; el=els[i++];) {
			el.classList.remove('scrollSection');
		}
        if (!winner) return;
		winner.classList.add('scrollSection');
		winner && markLinksActivated('#'+winner.id);
	};

    document.addEventListener('DOMContentLoaded',listen);
    window.addEventListener('scroll',listen);
    window.addEventListener('resize',listen);

	var latestWinner;
	function markLinksActivated(id){
		if (latestWinner === id) return;
		latestWinner = id;
		var el = $(id);
		$('.c1-source').removeClass('c1-source');
		var els = document.querySelectorAll('a[href]');
		for (var i=0,el; el=els[i++];) {
			var elHash = el.href.match(/#.*/);
			if (!elHash) continue;
			if (elHash[0] !== id) continue;
			$(el).addClass('c1-source')
		}
	}
    /* */

}();
