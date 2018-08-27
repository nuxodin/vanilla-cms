/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
!function(){ 'use strict';

//  document.head.append(c1.dom.fragment('<style>'+
// '.c1-accordion > .-panel {'+
// 	'margin: 0;'+
// '}'+
// '</style>'))

c1.accordion = true;
c1.onElement('.c1-accordion', function (accordion) {
    accordion.addEventListener('click', function (event) {
        var target = event.target.closest('.c1-accordion > .-trigger');
        if (!target || target.parentNode !== accordion) return;
        var isExpanded = target.getAttribute('aria-expanded') == 'true';
        var singleMode = accordion.hasAttribute('data-singlemode');
        if (singleMode) {
            var triggers = accordion.c1FindAll('> .-trigger');
            triggers.forEach(function (trigger) {
                if (trigger.getAttribute('aria-expanded') == 'false') return;
				var panel = document.getElementById(trigger.getAttribute('aria-controls'));
				panel.style.height = panel.c1Find('> .-content').offsetHeight+'px';
				setTimeout(function(){
					panel.style.height = '';
					panel.setAttribute('hidden', '');
					trigger.setAttribute('aria-expanded', 'false');
				},10)
            });
        }
        var panel = document.getElementById(target.getAttribute('aria-controls'));
        panel.addEventListener('transitionend',transitionend);
        panel.addEventListener('transitioncancel',transitionend);
        panel.style.overflow = 'hidden';
        if (isExpanded) {
			panel.style.height = panel.c1Find('> .-content').offsetHeight+'px';
			setTimeout(function(){
                panel.style.height = '';
                panel.setAttribute('hidden', '');
                target.setAttribute('aria-expanded', 'false');
				target.id && history.replaceState('', '', location.pathname+location.search);
            },10);
        } else {
            panel.removeAttribute('hidden');
            var height = panel.c1Find('> .-content').offsetHeight;
			panel.style.height = '0';
            setTimeout(function(){
                panel.style.height = height+'px';
                panel.removeAttribute('hidden');
                target.setAttribute('aria-expanded', 'true');
				target.id && history.replaceState('', '', location.pathname+location.search+'#'+target.id);
            },10);

			if (singleMode) {
				c1.c1Use('scroll');
				var dur = getComputedStyle(panel).getPropertyValue('transition-duration');
				dur = parseFloat(dur)*1000;
				setTimeout(function(){
					c1.c1Use('scroll',function(){

						c1.scroll.toElement(target, {
							ignorMaxScroll:true,
							duration:dur*.8,
						});
						/*
						c1.scroll.to(rect.left + pageXOffset, rect.top + pageYOffset, {
							ignorMaxScroll:true,
							duration:dur*.8,
						});
						*/
					})
				},dur)
			}

        }
        event.preventDefault();
    });

    accordion.addEventListener('keydown', function (event) {
        var target = event.target;
        var key = event.which.toString();
        var ctrlModifier = (event.ctrlKey && key.match(/33|34/)); // 33 = Page Up, 34 = Page Down

        if (target.matches('.c1-accordion > .-trigger')) {
            var triggers = accordion.c1FindAll('> .-trigger');
            triggers = Array.prototype.slice.call(triggers);
            // Up/ Down arrow | Control + Page Up/ Page Down
            if (key.match(/38|40/) || ctrlModifier) { // 38 = Up, 40 = Down
                var index = triggers.indexOf(target);
                var direction = (key.match(/34|40/)) ? 1 : -1;
                var newIndex = index + direction;
                triggers[newIndex] && triggers[newIndex].focus();
                event.preventDefault();
            } else if (key.match(/35|36|13|32/)) {
                switch (key) { // 35 = End, 36 = Home, 13 = Enter, 32 = Space
                    case '36': triggers[0].focus(); break;
                    case '35': triggers[triggers.length - 1].focus(); break;
                    case '13':
                    case '32':
                        var clickEvent = document.createEvent('MouseEvent');
                        clickEvent.initMouseEvent('click', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                        target.dispatchEvent(clickEvent);
                }
                event.preventDefault();
            }
        } else if (ctrlModifier) {
            // Control + Page Up/ Page Down keyboard operations
            // Catches events that happen inside of panels
            var panel = target.closest('.c1-accordion > .-panel');
            if (panel && panel.parentNode === accordion) {
                panel.previousElementSibling.focus();
                //panel.previousElementSibling.c1Find('button').focus();
            }
        }
    });
	if (location.hash) {
		var el = accordion.c1Find('> .-trigger' + location.hash.replace(/[=&"\\]/g, '\\$&') );
		if (el) {
			el.setAttribute('aria-expanded', 'true');
			var panel = document.getElementById(el.getAttribute('aria-controls'));
			panel.removeAttribute('hidden');
		}
	}

});

function transitionend(e){
    e.target.style.height = '';
    e.target.style.overflow = '';
}


}();
