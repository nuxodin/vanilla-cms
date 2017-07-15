!function(){
    window.error_report_count = 1000; // dont send errors
    var iv = setInterval(function(){
        if (!document.body) return;
        clearInterval(iv);
        var div = document.createElement('div');
        var browsers = [{
                name: 'chrome',
                link: 'https://www.google.com/chrome/browser/desktop/index.html'
            },{
                name: 'firefox',
                link: 'https://getfirefox.com'
            },{
                name: 'opera',
                link: 'https://www.opera.com/'
            }
        ];
        var OS = '';
        if (navigator.appVersion.indexOf("Win")!=-1)   OS = 'Windows';
        if (navigator.appVersion.indexOf("Mac")!=-1)   OS = 'Mac';
        if (navigator.appVersion.indexOf("X11")!=-1)   OS = 'UNIX';
        if (navigator.appVersion.indexOf("Linux")!=-1) OS = 'Linux';
        if (OS === 'Windows') {
            browsers.push({
                name: 'edge',
                link: 'https://www.microsoft.com/en-us/windows/microsoft-edge/microsoft-edge'
            });
        }
        if (OS === 'Mac') {
            browsers.push({
                name: 'safari',
                link: 'https://support.apple.com/de-ch/HT204416'
            });
        }
        var html =
        '<div style="position:fixed; z-index:999999; top:0; left:0; right:0; padding:10px; background:#ff8; border-bottom:1px solid #777; font-size:14px">'+
            '<table style="vertical-align:middle; width:100%; margin-bottom:.6em">'+
                '<tr>'+
                    '<td style="width:80%">'+
                        '<b>Ihr Browser wird von uns nicht unterstützt</b><br>'+
                        'Um eine zeitgemässe Darstellung zu gewährleisten und zu Ihrer eigenen Sicherheit, empfehlen wir <b>dringend</b> einen aktuellen Browser zu verwenden.'+
                    // '<td style="text-align:right">'+
                    //     '<button>nicht mehr anzeigen</button>'+
            '</table>'
        for (var i=0,browser; browser = browsers[i++];) {
            html +=
            '<a href="'+(browser.link)+'" style="display:inline-block; margin-right:20px" target="install_'+browser.name+'">'+
                '<img alt="'+browser.name+'" title="download '+browser.name+'" src="/m/cms.backend.webmaster/pub/browsers/'+(browser.name)+'.png" style="height:34px">'+
            '</a>';
        }
        html += '</div>';
        div.innerHTML = html;
        document.body.appendChild(div);
    },100)

}();
