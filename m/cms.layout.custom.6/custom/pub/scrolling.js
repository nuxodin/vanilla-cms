!function() { 'use strict';

function l1ScrollOffset(){
	return document.getElementById('head').offsetHeight - 1;
}

// initial go to hash
function gotoHash(){
	var id = location.hash.substring(1);
	if (!id) return;
    var el = document.getElementById(id);
    if (!el) return;
    var top = el.getBoundingClientRect().top + pageYOffset - l1ScrollOffset();
    document.documentElement.scrollTop = document.body.scrollTop = top;
}
requestAnimationFrame(gotoHash);
document.addEventListener('DOMContentLoaded',function(){
    gotoHash();
    setTimeout(gotoHash);
    var int = setInterval(gotoHash,1);
    setTimeout(function() { clearInterval(int); }, 200);
});

// a clicks
var lastHashSet = null;
document.addEventListener('click', function(e){
    var a = e.target.closest('a');
    if (!a) return;
    var href = a.href;
    if (!href) return;
    var parts = href.split('#');
    if (!parts[1]) return;
    var path = parts[0];
    var id = parts[1];
    var actualParts = location.href.split('#');
    if (actualParts[0] !== path) return;
    var target = document.getElementById(id);
    if (!target) return;
    scrollToEl(target, function(e){
        lastHashSet = '#'+id;
        target.removeAttribute('id'); // tricky hack! (ie only needed?)
        location.hash = id; // ie10 does not preventDefault :(
        target.setAttribute('id',id);
		markLinksActivated(id);
    });
    e.preventDefault();
});

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
c1.c1Use('scroll');
function scrollToEl(el, cb) {
	if (!el) return;
    var top = el.getBoundingClientRect().top + pageYOffset - l1ScrollOffset();
	c1.c1Use('scroll',function(){
		c1.scroll.to(null, top, {
			duration:290,
			onfinish:cb,
		});
	});
}

/* mark link, todo: loop nav elements, for now it only works with one nav */
var links = null;
var getLinks = function(){
	if (links) return links;
	var els = document.querySelectorAll('a[href]');
	links = [];
	for (var i=0,el; el=els[i++];) {
		var matches = el.href.match(/#(.*)/);
		if (!matches) continue;
		var id = matches[1]
		if (!id) continue;
		var target = document.getElementById(id);
		if (!target) continue;
		links.push({
			a:el,
			target:target,
			id:id,
		});
	}
	setTimeout(function(){ links = null; },1000);
	return links;
};
var listen = function(e){
	var links = getLinks(), winner, i=0, link;
	while (link=links[i++]) {
		var pos = link.target.getBoundingClientRect();
		if (pos.top > 140) break; // first target below viewport
		winner = link;
	}
	winner && markLinksActivated(winner.id);
};

document.addEventListener('DOMContentLoaded',listen);
window.addEventListener('scroll',listen);
window.addEventListener('resize',listen);

var latestWinner;
function markLinksActivated(id){
	if (latestWinner === id) return;
	latestWinner = id;
	var actives = document.querySelectorAll('.hashLinkActive');
	for (var i=0,item; item=actives[i++];) item.classList.remove('hashLinkActive');
	var links = getLinks();
	for (i=0; item=links[i++];) {
		item.id === id && item.a.classList.add('hashLinkActive');
	}
}


/* old mark link *
var listen = function(e){
	var winHeight = window.innerHeight * 0.5; // obere 50%
	var winner = null;
	var winnerValue = null;
	var els = document.querySelectorAll('.qgCmsCont[id]');
	for (var i=0,el; el=els[i++];) {
		var pos = el.getBoundingClientRect();
		var bottom = Math.min(pos.bottom, winHeight);
		var top = Math.max(pos.top, 0);
		var visible = bottom-top;
		if (winnerValue===null || visible > winnerValue) {
			winner = el;
			winnerValue = visible;
		}
	}
	els = document.querySelectorAll('.scrollSection');
	for (i=0,el; el=els[i++];) el.classList.remove('scrollSection');

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
	var i, el;

	var els = document.querySelectorAll('.hashLinkActive');
	for (i=0,el; el=els[i++];) el.classList.remove('hashLinkActive');

	var els = document.querySelectorAll('a[href]');
	for (i=0,el; el=els[i++];) {
		var elHash = el.href.match(/#.+/);
		if (!elHash) continue;
		if (elHash[0] !== id) continue;
		el.classList.add('hashLinkActive');
	}
}
/* */

}();
