'use strict';
{
    cms.frontend1.clipboard = function(pid){
        function close() {
            $fn('cms::clipboardSet')(0);
            div.remove();
            $('.-pid'+pid).css({opacity: 1});
        }
        let div = cms.frontend1.dialog('Aus der Zwischenablage einfügen',[
            {
                title:'Auf dieser Seite einfügen',then(){
                    cms.cont(pid).addPosition();
    				close();
                }
            },{
                title:'Am alten Ort behalten',then:close
            }
        ]);
        $('.-pid'+pid).css({opacity:0.4});
        div.querySelector('.-head').insertAdjacentHTML('afterend',
        '<div class=-body>'+
			'<table>'+
				'<tr>'+
					'<th> Titel: &nbsp;'+
					'<td> <span class=-title></span>'+
				'<tr>'+
					'<th> Modul: &nbsp;'+
					'<td> <span class=-module></span>'+
				'<tr>'+
					'<th> Id: &nbsp;'+
					'<td> '+pid+
			'</table>'+
		'</div>');
        $fn('cms::toJson')(pid).then(function(res){
            div.querySelector('.-body .-title').innerHTML = res.title;
            div.querySelector('.-body .-module').innerHTML = res.module;
        })
    };
}
