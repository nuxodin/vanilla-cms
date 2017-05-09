c1.c1Use(['focusIn','onElement'],function(){
    'use strict';
	if (c1.dialog) { console.log('c1.dialog loaded once more'); return; }

    document.head.prepend(c1.dom.fragment('<style> \
    .c1-dialog.c1-dialog { \
        display:none; \
    } \
    .c1-dialog { \
    	position:fixed; \
        flex-flow:column; \
    	box-sizing:border-box; \
    	overflow:auto; \
    	background:#fff; \
        max-width:100vw; \
        max-height:100vh; \
        padding: 1em; \
        \
        top:20%; \
        left:50%; \
        transform:translateY(-20%) translateX(-50%); \
        \
        /* if browser support * \
        top:0; \
        left:0; \
        bottom:0; \
        right:0; \
        margin:auto; \
        width: -webkit-fit-content; \
        width: -moz-fit-content; \
        height: -webkit-fit-content; \
        height: -moz-fit-content; \
        /**/ \
    } \
    /*.c1-dialog[open], */ \
    .c1-dialog.c1-focusIn { \
    	display:block; \
        display:flex; \
    } \
    .c1-dialog:focus-within { \
        display:block; \
    } \
    </style>'));

    function open(el){
        //el.setAttribute('open','open');
        backdrop.for(el);
        el.dispatchEvent(new CustomEvent('c1-dialog-open',{bubbles:true,cancelable:true})); // cancelable needed?
        //document.documentElement.style.overflow = 'hidden';
    }
    function close(el){
        //el.removeAttribute('open');
        backdrop.hide();
		el.dispatchEvent(new CustomEvent('c1-dialog-close',{bubbles:true,cancelable:true})); // cancelable needed?
        //if (el.id === location.hash.substr(1)) location.hash = 'notarget';
        //document.documentElement.style.overflow = '';
    }
    c1.onElement('.c1-dialog',function(el){
        el.setAttribute('tabindex','0');
        el.contains(document.activeElement) && open(el);
        el.addEventListener('focusin',function(){
            open(el);
        });
        el.addEventListener('focusout',function(e){
            setTimeout(function(){ // wait for new document.activeElement
    			var active = document.activeElement;
                if (el.contains(active)) return;
                close(el);
            })
        });
        el.addEventListener('keydown',function(e){
            if (e.which !== 27 && e.target !== el) return;
            e.target.blur();
        });
        el.addEventListener('c1-close',function(e){
            var active = document.activeElement;
            el.contains(active) && active.blur()
			e.preventDefault();
            e.stopPropagation();
            close(el)
        });
        function stopPropagation(e){
			e.stopPropagation();
			//el.c1ZTop();
		}
        el.addEventListener('touchstart', stopPropagation);
		el.addEventListener('mousedown', stopPropagation);
        // form.addEventListener('keydown', stopPropagation);
    });

    c1.dialog = function(options){
        var str =
		'<form class="c1-dialog c1-box q1Rst" tabindex=0>'+
        '	<div class=-head>'+
		'		<div class=-title>'+options.title+'</div>'+
		'	</div>'+
        '	<div class=-body>'+
        (options.body?
        '		<div style="margin-bottom:.8em">'+(options.body||'')+'</div>':'')+
        '		<div class=-buttons style="margin:-.4em; display:flex; flex-wrap:wrap; justify-content: flex-end;"></div>'+
		'	</div>'+
		'</form>';
		var element = this.element = c1.dom.fragment(str).firstChild;
        if (options.class) element.className += ' '+options.class
		var btnCont = element.c1Find('>.-body>.-buttons');
		element.addEventListener('submit',  function(e){ e.preventDefault(); });
        options.buttons && options.buttons.forEach(function(btn, i){
            var el = document.createElement('button');
            el.innerHTML = btn.title;
            el.style.margin = '.4em';
            el.style.minWidth = '5em';
            el.addEventListener('click',function(e){
                btn.then && btn.then.call(this,e);
                element.focus(); // not needed in chrome
                element.remove();
            });
            btnCont.appendChild(el);
            if (i === 0) setTimeout(function(){el.focus();});
        });
    }
    c1.dialog.prototype = {
        show:function(){
            var element = this.element;
            var dialog = this;
            document.body.appendChild(this.element);
    		element.c1ZTop();
            element.c1Focus();
            return new Promise(function(resolve, reject){
                element.addEventListener('c1-dialog-close',function(e){
                    if (element !== e.target) return;
                    resolve(dialog.value);
                })
            });
        }
    }

    c1.dialog.alert = function(title) {
        var dialog = new c1.dialog({
            title:title,
            buttons:[{title:'ok'}]
        });
        return dialog.show();
    }
    c1.dialog.confirm = function(title) {
        var dialog = new c1.dialog({
            title:title,
            buttons:[{
                title:'ok',then:function(){
                    dialog.value = true;
                }
            },{title:'schliessen'}]
        });
        dialog.value = false;
        return dialog.show();
    }
    c1.dialog.prompt = function(title) {
        var dialog = new c1.dialog({
            title:title,
            body: '<input style="width:20em; max-width:100%">',
            buttons:[{
                title:'ok',then:function(){
                    dialog.value = input.value;
                }
            },{title:'schliessen'}]
        });
        var input = dialog.element.c1Find('input');
        setTimeout(function(){ input.focus(); });
        dialog.value = null;
        return dialog.show();
    }




    /* HELPERS */

    /* backdrop */
    var bdDiv = document.createElement('div');
    bdDiv.style.cssText = 'position:fixed; top:0; left:0; bottom:0; right:0; background:rgba(0,0,0,.4); transition:opacity .16s linear; opacity:0';
	bdDiv.className = 'c1-backdrop';
    var bdTimeout = null;
    var backdrop = {
        for:function(el){
            clearTimeout(bdTimeout);
            el.before(bdDiv);
            bdDiv.c1ZTop();
            bdDiv.style.opacity = 0;
            requestAnimationFrame(function(){
                bdDiv.style.pointerEvents = '';
                bdDiv.style.opacity = 1;
            })
            el.c1ZTop();
        },
        hide:function(){
            bdDiv.style.opacity = 0;
            var todoTimeout = setTimeout(function(){
	            bdDiv.style.pointerEvents = 'none';
            },100);
            bdTimeout = setTimeout(function(){
                bdDiv.remove();
            },300);
        }
    }
    bdDiv.addEventListener('mousedown', function(e){ e.stopPropagation(); },true);
    bdDiv.addEventListener('touchstart',function(e){ e.stopPropagation(); },true);

    /* focus by target *
    function checkTarget(e){
        var id = location.hash.substr(1);
		var candidate = document.getElementById(id);
        if (!candidate) return;
        var dialog = candidate.closest('.c1-dialog');
        dialog && dialog.c1Focus();
	}
    addEventListener('hashchange',checkTarget);
    document.addEventListener('DOMContentLoaded',checkTarget);
    requestAnimationFrame(checkTarget);
    */


    /* close */
    function listenClose(e){
		var btn = e.target.closest('.c1-close');
		if (!btn) return;
		close(btn);
		btn.dispatchEvent(new CustomEvent('c1-close',{bubbles:true,cancelable:true}));
		e.preventDefault();
	}
	document.addEventListener('mousedown',listenClose);
	document.addEventListener('touchstart',listenClose);

    /* draggable */
    // c1.onElement('[c1-draggable]',function(el){
    //     c1.c1Use('pointerObserver',function(){
    //         var pObserver = new c1.pointerObserver(el);
    //         var selector = el.getAttribute('c1-draggable');
    //         var dragging;
    //         var position;
    //         pObserver.onstart = function(e) {
    //             dragging = null;
    //             if (selector) {
    //                 var handle = e.target.closest(selector);
    //                 if (!handle) return;
    //                 if (el !== handle.closest('[c1-draggable]')) return;
    //             }
    //             dragging = true;
    //             e.preventDefault();
    //             var style = getComputedStyle(el);
    //             position = {
    //                 top: parseFloat(style.top),
    //                 left: parseFloat(style.left),
    //             }
    //         }
    //         pObserver.onmove = function(e) {
    //             if (!dragging) return;
    //             position.top += this.diff.y;
    //             position.left += this.diff.x;
    //             el.style.top = position.top+'px';
    //             el.style.left = position.left+'px';
    //         }
    //     });
    // });

});
