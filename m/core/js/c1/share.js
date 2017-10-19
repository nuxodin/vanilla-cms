!function(){
    var enc = encodeURIComponent;
    var container = document.createElement('div');
    container.addEventListener('click',function(e){
        var link = e.target.closest('a');
        if (!link) return;
        window.open(link.href, 'share_poly', 'width=520,height=500');
        e.preventDefault();
    });
    container.innerHTML =
    '<style>'+
    '.c1Share {'+
    '   width:500px; '+
    '}'+
    '.c1Share .-items {'+
    '   display:flex; '+
    '   flex-wrap:wrap; '+
    '   margin:-4px -4px 8px -4px; '+
    '}'+
    '.c1Share .-items > a {'+
    '   display:flex;  '+
    '   justify-content: center;  '+
    '   flex-flow:column;  '+
    '   box-sizing:border-box;  '+
    '   flex:1 0 134px;  '+
    '   text-align:center;  '+
    '   margin:4px;  '+
    '   background:#eee;  '+
    '   padding:14px;  '+
    '   cursor:pointer;  '+
    '   transition:background-color .2s;  '+
    '   color:inherit; '+
    '   text-decoration:none'+
    '}'+
    '.c1Share .-items > a:hover {'+
    '   background:#ddd;  '+
    '}'+
    '.c1Share .-items > a > svg {'+
    '   display:block; '+
    '   height:40px; '+
    '   margin:0 auto 10px auto;'+
    '}'+
    '@supports (display:grid) {'+
        '.c1Share .-items {'+
        '  margin:0 0 10px 0; '+
        '  display:grid; '+
        '  grid-gap: 8px; '+
        '  grid-template-columns: repeat(auto-fill, minmax(134px, 1fr) ); '+
        '}'+
        '.c1Share .-items > a {'+
        '  margin:0; '+
        '}'+
    '}'+
    '</style>'+
    '<div class=-items>'+
    '</div>';

    c1.share = function(data){
        var itemsCont = container.c1Find('.-items');
        itemsCont.innerHTML = '';
        c1.share.items.forEach(function(item){
            var a = document.createElement('a');
            a.setAttribute('href',item.url(data));
            a.setAttribute('taret','share_poly');
            a.innerHTML = item.svg+item.name;
            itemsCont.append(a);
        });
        c1.c1Use('dialog',function(){
            var dialog = new c1.dialog({
                body:' ',
                class:'c1Share',
                title:'Teilen',
                buttons:[{title:'schliessen'}],
            })
            dialog.element.c1Find('>.-body').prepend(container)
            dialog.show();
        });
    };
    c1.share.items = [];
    c1.share.addItem = function(item){
        c1.share.items.push(item);
    }
    // items
    c1.share.addItem({
        name:'E-Mail',
        url:function(data){
            if (!data.test && data.url) data.text = 'Have a look at: \n';
            var text = (data.text||'') + ' ' + data.url;
            return 'mailto:?subject='+enc(data.title)+'&body='+enc(text);
        },
        svg:'<svg xmlns="http://www.w3.org/2000/svg" height="80" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/><path fill="none" d="M0 0h24v24H0z"/></svg>',
    });
    c1.share.addItem({
        name:'Twitter',
        url:function(data){
            var text = ((data.title||'')+' ').trim() + (data.text||'');
            return 'https://twitter.com/intent/tweet?'+
            'original_referer='+enc(location.href)+'&ref_src=twsrc%5Etfw&'+
            'text='+enc(text)+'&tw_p=tweetbutton&'+
            'url='+enc(data.url);
        },
        svg:'<svg xmlns="http://www.w3.org/2000/svg" height="80" viewBox="0 0 256 209" preserveAspectRatio="xMidYMid"><path d="M256 25.45c-9.42 4.177-19.542 7-30.166 8.27 10.845-6.5 19.172-16.793 23.093-29.057-10.147 6.018-21.388 10.39-33.35 12.745C205.994 7.2 192.344.822 177.238.822c-29.007 0-52.524 23.516-52.524 52.52 0 4.117.465 8.125 1.36 11.97-43.65-2.19-82.35-23.1-108.255-54.876-4.52 7.757-7.11 16.78-7.11 26.404 0 18.222 9.274 34.297 23.366 43.716-8.61-.273-16.708-2.635-23.79-6.57-.003.22-.003.44-.003.66 0 25.448 18.104 46.676 42.13 51.5-4.407 1.2-9.047 1.843-13.837 1.843-3.385 0-6.675-.33-9.88-.943 6.682 20.866 26.078 36.05 49.06 36.475-17.974 14.086-40.62 22.483-65.227 22.483-4.24 0-8.42-.25-12.53-.734 23.243 14.903 50.85 23.598 80.51 23.598 96.607 0 149.434-80.03 149.434-149.435 0-2.278-.05-4.543-.152-6.795 10.26-7.405 19.166-16.655 26.208-27.188"/></svg>',
    });
    c1.share.addItem({
        name:'Facebook',
        url:function(data){
            return 'https://www.facebook.com/sharer/sharer.php?u='+enc(data.url)+'&t='+enc(data.title);
        },
        svg:'<svg xmlns="http://www.w3.org/2000/svg" height="80" viewBox="88.428 12.828 107.543 207.085"><path d="M158.232 219.912v-94.46h31.707l4.746-36.814h-36.454V65.134c0-10.658 2.96-17.922 18.245-17.922l19.494-.01V14.28c-3.372-.447-14.943-1.45-28.405-1.45-28.106 0-47.348 17.156-47.348 48.662v27.15h-31.79v36.812h31.79v94.46h38.015z"/></svg>',
    });
    c1.share.addItem({
        name:'Google+',
        url:function(data){
            return 'https://plus.google.com/share?url='+enc(data.url);
        },
        svg:'<svg xmlns="http://www.w3.org/2000/svg" height="80" viewBox="0 0 512 512"><path d="M319.317 213.333H153.6v76.8h83.345c-11.204 31.855-38.357 51.2-73.054 51.2-46.382 0-87.09-39.893-87.09-85.367 0-45.44 40.708-85.3 87.09-85.3 22.18 0 40.93 7.084 54.226 20.472l6.033 6.084 57.147-57.148-6.212-6.033c-27.05-26.28-65.492-40.17-111.188-40.17C71.997 93.868 0 165.07 0 255.968c0 90.93 71.996 162.165 163.89 162.165 84.71 0 144.897-49.698 157.074-129.698 1.587-10.377 2.398-21.3 2.406-32.47 0-12.26-1.05-25.565-2.807-35.575l-1.246-7.058z"/><path d="M460.843 213.292v-51.158h-59.776v51.2h-51.2v59.734h51.2v51.2h59.776v-51.2H512v-59.776"/></svg>',
    });
    c1.share.addItem({
        name:'Pinterest',
        url:function(data){
            return 'https://pinterest.com/pin/create/button/?url='+enc(data.url)+'&xmedia='+'&description='+enc(data.title)+': '+enc(data.text);
        },
        svg:'',
    });
    c1.share.addItem({
        name:'WhatsApp',
        url:function(data){
            return 'whatsapp://send?text='+enc(data.title)+' '+enc(data.url);
        },
        svg:'<svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 90 90"><path d="M90 43.84c0 24.2-19.78 43.84-44.18 43.84-7.75 0-15.03-1.98-21.36-5.45L0 90l7.97-23.52c-4.02-6.6-6.33-14.36-6.33-22.64C1.64 19.64 21.4 0 45.8 0S90 19.63 90 43.84zM45.82 6.98c-20.5 0-37.15 16.54-37.15 36.86 0 8.07 2.63 15.53 7.08 21.6l-4.64 13.7 14.3-4.54c5.9 3.85 12.9 6.1 20.5 6.1C66.3 80.7 83 64.17 83 43.84S66.3 6.98 45.8 6.98zm22.3 46.96c-.26-.45-.98-.72-2.07-1.26-1.08-.53-6.4-3.13-7.4-3.5-1-.35-1.7-.53-2.43.55-.73 1.07-2.8 3.5-3.43 4.2-.7.73-1.3.82-2.4.28-1.1-.5-4.6-1.6-8.7-5.3-3.28-2.8-5.4-6.3-6.08-7.4-.64-1.1-.07-1.63.47-2.2.5-.5 1.1-1.22 1.63-1.9.56-.6.74-1.03 1.1-1.8.36-.7.18-1.3-.1-1.83-.26-.54-2.43-5.83-3.33-8C34.5 23.63 33.6 24 33 24c-.63 0-1.35-.1-2.07-.1-.7 0-1.9.27-2.9 1.34-1 1.1-3.76 3.7-3.76 8.97 0 5.3 3.88 10.4 4.42 11.1.52.7 7.47 11.9 18.5 16.2 11 4.3 11 2.9 13 2.7 1.96-.2 6.4-2.6 7.3-5.1.9-2.5.9-4.64.62-5.1z"/></svg>',
    });

    if (!navigator.share) navigator.share = c1.share;
}();

/*
navigator.share({
    title: document.title,
    text: "Hello World",
    url: window.location.href
}).then(() => console.log('Successful share'))
.catch(error => console.log('Error sharing:', error));
*/
