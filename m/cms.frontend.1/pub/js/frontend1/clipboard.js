'use strict';
{
    cms.frontend1.clipboard = function(pid){
        function close() {
            $fn('cms::clipboardSet')(0);
            $('.-pid'+pid).css({opacity: 1});
        }
        $('.-pid'+pid).css({opacity:0.4});
        $fn('cms::toJson')(pid).then(function(res){
            let div = cms.frontend1.dialog(
                'Aus der Zwischenablage einfügen',
                '<table>'+
    				'<tr>'+
    					'<th> Titel: &nbsp;'+
    					'<td> '+res.title+
    				'<tr>'+
    					'<th> Modul: &nbsp;'+
    					'<td> '+res.module+
    				'<tr>'+
    					'<th> Id: &nbsp;'+
    					'<td> '+pid+
    			'</table>',
                [{
                    title:'Auf dieser Seite einfügen',then(){
                        cms.cont(pid).addPosition();
                        $('.-pid'+pid).remove();
        				close();
                    }
                },{
                    title:'Am alten Ort behalten',then:close
                },{
                    title:'Schliessen'
                }]
            );
        });
    };
}
