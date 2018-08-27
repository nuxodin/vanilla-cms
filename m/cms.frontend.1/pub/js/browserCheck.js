(function(){
    'use strict';
    let htmlEl = document.documentElement;
    let style = htmlEl.style;
    let ok =
        'grid' in style &&
        'will-change' in style &&
        //'serviceWorker' in navigator && // zzz not without https
        window.CSS &&
        CSS.supports('color', 'var(--primary)');

    if (ok) return;

    window.error_report_count = 1000; // dont send errors

    let ignore = document.cookie.replace(/(?:(?:^|.*;\s*)qgCMS_browserCheck_ignore\s*\=\s*([^;]*).*$)|^.*$/, "$1");
    if (ignore) return;

    let OS = '';
    if (navigator.appVersion.indexOf("Win")!=-1)   OS = 'Windows';
    if (navigator.appVersion.indexOf("Mac")!=-1)   OS = 'Mac';
    if (navigator.appVersion.indexOf("X11")!=-1)   OS = 'UNIX';
    if (navigator.appVersion.indexOf("Linux")!=-1) OS = 'Linux';

    let browsers = {
        'IE' : {
            'name' : 'Edge',
            'link' : 'https://www.microsoft.com/de-de/windows/microsoft-edge',
            'icon' : '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" version="1" viewBox="0 0 32 32"><path fill="#188FD1" d="M11.47 18.94V19c0 1.06.24 2.07.72 3.03.56 1 1.34 1.8 2.33 2.4.98.58 2.08.88 3.3.88s2.3-.3 3.28-.88c1-.6 1.77-1.4 2.34-2.4h7.84c-1 2.85-2.77 5.2-5.27 7-2.5 1.82-5.3 2.73-8.4 2.73-2.34 0-4.53-.5-6.6-1.5-4.6 2.22-7.9 2.33-9.93.32C.37 29.9 0 28.8 0 27.27c0-1.52.3-3.23.9-5.1.6-1.9 1.58-3.96 2.97-6.22 1.38-2.25 3-4.3 4.9-6.14 1.1-1.13 1.8-1.83 2.1-2.1-2.72 1.32-5.17 3.36-7.37 6.12.8-3.2 2.5-5.83 5.1-7.9 2.6-2.04 5.6-3.07 9-3.07.34 0 .7.02 1.04.06 2.46-1.08 4.7-1.7 6.7-1.83 2.03-.13 3.46.2 4.3 1 1.66 1.7 1.8 4.37.45 8 1.26 2.24 1.9 4.65 1.9 7.23 0 .6-.02 1.16-.07 1.64H11.47zM10.1 29.7c-2.95-1.78-5.02-4.3-6.2-7.54-1.98 3.85-2.24 6.5-.8 7.95 1.28 1.28 3.6 1.15 7-.4zm13.54-14.57c-.1-1.57-.72-2.92-1.9-4.04-1.2-1.12-2.58-1.68-4.2-1.68-1.6 0-2.98.56-4.16 1.67-1.2 1.1-1.83 2.46-1.9 4.03h12.16zM22.92 3.9c2.8 1.14 5.04 2.98 6.7 5.52 1.02-2.72.97-4.62-.12-5.72-1.2-1.22-3.38-1.16-6.58.2z"/></svg>',
            'beta' : true,
        },
        'Safari' : {
            'name' : 'Safari',
            'link' : 'https://support.apple.com/en-us/HT204416',
            'icon' : '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox=".91 -.2 32 32"><path fill="#26A6D1" fill-rule="evenodd" d="M16.9-.2c-8.83 0-16 7.16-16 16s7.17 16 16 16 16-7.17 16-16-7.16-16-16-16zm0 29c-7.17 0-13-5.82-13-13s5.83-13 13-13 13 5.82 13 13-5.8 13-13 13z" clip-rule="evenodd"/><path fill="#E2574C" fill-rule="evenodd" d="M25.22 7.48l-6.2 10.44-4.23-4.25 10.42-6.2z" clip-rule="evenodd"/><path fill="#E4E7E7" fill-rule="evenodd" d="M8.6 24.1l10.43-6.18-4.24-4.25L8.6 24.1z" clip-rule="evenodd"/></svg>',
            'beta' : true,
        },
        'Chrome' : {
            'name' : 'Chrome',
            'link' : 'https://www.google.com/chrome/browser/desktop/index.html',
            'icon' : '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" version="1" viewBox="0 0 32 32"><path fill="#4AAE48" d="M16 24.2c-1.63 0-3.1-.45-4.44-1.32-1.33-.88-2.33-2-3-3.38L2 8c-1.38 2.46-2 5.2-2 8 0 4 1.3 7.5 3.9 10.47 2.6 2.98 5.85 4.76 9.72 5.34l4.65-8.02c-.47.14-1.25.4-2.27.4z"/><path fill="#EA3939" d="M10.97 9.53C12.45 8.4 14.12 8 16 8h13.75c-1.42-2.42-3.34-4.46-5.78-5.88C21.53.73 18.87 0 16 0c-2.5 0-4.83.53-7 1.6-2.17 1.06-4.17 2.6-5.64 4.6L8 14c.46-1.8 1.5-3.32 2.97-4.47z"/><path fill="#FED14B" d="M30.8 10h-9.3c1.63 1.63 2.7 3.7 2.7 6 0 1.7-.5 3.27-1.45 4.7L16.2 32c4.36-.04 8.1-1.63 11.18-4.75C30.46 24.12 32 20.37 32 16c0-2.04-.34-4.2-1.2-6z"/><circle cx="16" cy="16" r="6" fill="#188FD1"/></svg>',
            'beta' : false,
        },
        'Firefox' : {
            'name' : 'Firefox',
            'link': 'https://www.mozilla.org/de/firefox/new/',
            'icon' : '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 -.5 32 31"><path fill="#2394BC" d="M16-.5c8.28 0 15 6.7 15 15 0 8.28-6.72 15-15 15s-15-6.72-15-15c0-8.3 6.7-15 15-15z"/><path fill="#EC8840" d="M18.56 30.27C26.18 29.05 32 22.47 32 14.5l-.15.18c.23-1.5.2-2.86-.12-4.04-.1.87-.24 1.4-.36 1.6 0-.06-.02-.96-.3-2.23-.14-.92-.34-1.78-.62-2.6.07.38.1.7.13.97C29.4 5.25 26.55 1.4 19.5 1.5c0 0 2.48.26 3.65 2.03 0 0-1.2-.3-2.1.15 1.1.44 2.06.9 2.87 1.37l.07.05c.2.12.37.25.56.38 1.5 1.05 2.9 2.55 2.78 4.5-.32-.53-.75-.86-1.3-1.03.68 2.66.75 4.86.2 6.6-.38-1.16-.73-1.85-1.04-2.1.43 3.57-.15 6.2-1.74 7.92.3-1.04.43-1.9.36-2.58-1.87 2.8-4 4.27-6.37 4.36-.94 0-1.83-.15-2.67-.43-1.23-.4-2.34-1.12-3.33-2.13 1.55.13 2.96-.13 4.2-.77l2.03-1.33c.26-.1.5-.1.75.02.5-.07.67-.34.5-.8-.24-.32-.6-.62-1.07-.9-1.02-.52-2.08-.44-3.2.27-1.04.6-2.06.56-3.04-.07-.65-.45-1.28-1.05-1.88-1.8l-.24-.36c-.1.85.02 1.95.4 3.3-.4-1.35-.5-2.45-.4-3.3.03-.75.34-1.16.92-1.25l-.24-.02.25.02c.66.06 1.42.2 2.28.46.15-.83-.04-1.7-.56-2.58v-.02c.8-.75 1.53-1.3 2.14-1.65.27-.14.43-.36.48-.66l.02-.02.04-.03c.16-.24.1-.43-.17-.6-.56.03-1.13 0-1.7-.12v.02c-.23-.06-.53-.27-.9-.62l-.92-.9-.27-.23v.03-.04l-.06-.05.08-.05c.13-.7.34-1.28.64-1.8l.07-.05c.3-.5.87-1.04 1.72-1.62-1.58.2-3.02.9-4.3 2.14-1.04-.38-2.3-.3-3.73.25l-.18.13H5.1l.2-.13c-.9-.42-1.52-1.62-1.8-3.57C2.33 3.04 1.77 5.08 1.8 8.02l-.33.5-.1.06h-.02v.02l-.02.03-.72 1.25C.2 10.66.06 11.32 0 11.9v.18l.02-.04c0 .13 0 .26.04.38l.94-.77c-.34.86-.57 1.77-.68 2.74l-.03.43-.3-.33c0 3.43 1.1 6.6 2.92 9.2l.06.08.1.1c1.3 1.82 3 3.35 4.94 4.47 1.4.83 2.92 1.42 4.52 1.76l.33.1 1.02.14.75.1.34.03h.48l.52.03.42-.02.7-.04c.4-.03.8-.07 1.2-.13l.24-.03zm-9.4-16.75zm19.52-2.74v.13-.12z"/></svg>',
            'beta' : true,
        }
    };

    if (OS !== 'Windows') delete browsers.IE;
    if (OS !== 'Mac')     delete browsers.Safari;

    let windows10 = navigator.appVersion.match(/(Windows 10.0|Windows NT 10.0)/);
    if (!windows10) delete browsers.IE;

    let html =
    '<div style="position:fixed; top:20px; left:0; right:0; margin:auto; width:310px; background:#fff; border:1px solid var(--cms-light); padding:20px; box-shadow:0 0 8px rgba(0,0,0,.5)" class="q1Rst qgCMS">'+
        '<div style="font-size:1.3em; margin-bottom:1.2em">Browser vom CMS nicht unterstützt</div>' +
        '<div>Folgende Browser sind technisch auf einem geeigneten Stand für die Verwendung des CMS</div>' +
        '<div style="display:table; margin:20px 0">';
    for (let id in browsers) {
        let browser = browsers[id];
        if (browser.beta) continue;
        html +=
        '<a style="display:table-cell; padding:20px; width:10%; text-align:center" href="'+browser.link+'" target=browser>'+
            browser.icon+
            '<div>'+browser.name+'</div>'+
        '</a>';
    }
    html +=
        '</div>'+
        '<div style="text-align:right">'+
            '<button class=-ignoreBtn>Ignorieren</button> '+
            '<button class=-closeBtn>Editmodus verlassen</button>'+
        '</div>'+
    '</div>';

    let div = document.createElement('div');
    div.innerHTML = html;

    div.querySelector('.-closeBtn').addEventListener('click',function(){
        location.href = location.href + '&qgCms_editmode=0';
    });
    div.querySelector('.-ignoreBtn').addEventListener('click',function(){
        alert('Fehler bitte melden.');
        let end = new Date( Date.now() + 1*60*60*1000 );
        document.cookie = 'qgCMS_browserCheck_ignore=1; expires=' + end.toGMTString();
        this.closest('.qgCMS').remove();
    });

    let iv = setInterval(function(){
        if (!document.body) return;
        document.body.append(div);
        div.c1ZTop();
        clearInterval(iv);
    },200);

})();
