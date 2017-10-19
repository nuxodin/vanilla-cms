/* alpha */
!function(){
	'use strict';

	var css =
	'.c1Calendar { '+
	'	display:inline-block; '+
	'	min-width:20em; '+
	'	min-height:17em; '+
	'	background-color:#fff; '+
	'	overflow: auto; '+
	'	white-space:nowrap; '+
	'	box-shadow:0 0 10px rgba(0,0,0,.5); '+
	'	font-family: Arial, Helvetica, sans-serif; '+
	'} '+
	'.c1Calendar .-table { '+
	'	width:100%; '+
	'	min-height:18em; '+
	'	border-collapse: collapse; '+
	'} '+
	'.c1Calendar td { '+
	'	padding:.7em; '+
	'	text-align:center; '+
	'	vertical-align:middle; '+
	'	cursor:pointer; '+
	'} '+
	'.c1Calendar .-outMonth { '+
	'	opacity: .3; '+
	'} '+
	'.c1Calendar .-td:hover, '+
	'.c1Calendar .-today { '+
	'	color: #0099ff;'+
	'}'+
	'.c1Calendar .-selected {'+
	'	background:#0099ff;'+
	'	color:#fff;'+
	'}';
	document.head.prepend(c1.dom.fragment('<style>'+css+'</style>'));

	var today;
	c1.calendar = function(el) {
		today = new Date();
		this.selected = [];
		el.classList.add('c1Calendar');
		var fragment = c1.dom.fragment(
			'<table style="width:100%; text-align:center;">'+
				'<tbody>'+
					'<tr>'+
						'<td class=-calLeft>&#9668;'+
						'<td class=-calHead>;'+
						'<td class=-calRight>&#9658;'+
			'</table>'+
			'<div class=-body></div>'
		);
		this.goLeft  = fragment.querySelector('.-calLeft');
		this.head    = fragment.querySelector('.-calHead');
		this.goRight = fragment.querySelector('.-calRight');
		this.table   = fragment.querySelector('.-table');
		this.body    = fragment.querySelector('.-body');
		el.append(fragment);
		this.dates = {};
	};

	c1.calendar.prototype = {
        select: function(date){ this.selected.push(date); },
		show12Years:function(date) {
			var _this = this;
			var y = date.getFullYear();
			y = y-(y+6)%12;
			this.head.immerHTML  = y+' - '+(y+12);
			this.goLeft.onclick  = function(){_this.show12Years(y-13);};
			this.goRight.onclick = function(){_this.show12Years(y+12);};
			times(3,function(){
				var tr = _this.table.insertRow();
				times(4,function(){
					var time = new Date(++y,0);
                    var td = tr.insertCell();
                    _this._addClasses(td, time, 'Y');
					td.innerHTML = y;
					td.value = y;
					td.onclick = function(){ _this.showYear(td.value); };
				});
			});

			this.body.innerHTML = '';
			this.body.append(table);
		},
		showYear:function(date) {
			var y = date.getFullYear();
			var _this = this;
			this.head.innerHTML  = y;
			this.head.onclick    = function(){ _this.show12Years(date); };
			this.goLeft.onclick  = function(){ date.setFullYear(date.getFullYear()-1); _this.showYear(date); };
			this.goRight.onclick = function(){ date.setFullYear(date.getFullYear()+1); _this.showYear(date); };
			var table = c1.calendar.renderYear(date, function(td){
				_this._addClasses(td, td.c1Date, 'YM');
				td.onclick = function(){ _this.showMonth(td.c1Date); };
			});
			this.body.innerHTML = '';
			this.body.append(table);
		},
		showMonth:function(date) {
			var y = date.getFullYear();
			var m = date.getMonth();
			var _this = this;
			var month = date.toString().substr(4,3);
			this.head.innerHTML  = month+' '+y;
			this.head.onclick    = function(){_this.showYear(date);};
			this.goLeft.onclick  = function(){ date.setMonth(date.getMonth()-1); _this.showMonth(date); };
			this.goRight.onclick = function(){ date.setMonth(date.getMonth()+1); _this.showMonth(date); };
			var table = c1.calendar.renderMonth(date, function(td){
				_this._addClasses(td, td.c1Date, 'YMD');
				td.onclick = function(){ _this.showDay(td.c1Date); };
			})
			this.body.innerHTML = '';
			this.body.append(table);
		},
        showDay:function(date){
            var _this = this;
			var y = date.getFullYear();
			var m = date.getMonth();
			var d = date.getDate();
            var month = date.toString().substr(4,3);
			this.head.innerHTML  = d+'. '+ month + ' ' + y;
			this.head.onclick    = function(){_this.showMonth(date);};
			this.goLeft.onclick  = function(){_this.showDay( date.setDate(date.getDate()-1) );};
			this.goRight.onclick = function(){_this.showDay( date.setDate(date.getDate()+1) );};
			var table = c1.calendar.renderDay(date,function(td){
				td.onclick = function(){ };
			})
			this.body.innerHTML = '';
			this.body.append(table);
        },
		showClock:function(date) {
			var _this = this;
			var month = new Date(y, m).toString().substr(4,3);
			this.head.innerHTML  = d+'. '+month+' '+y;
			this.head.onclick    = function(){_this.showMonth(date);};
			this.goLeft.onclick  = function(){_this.showClock([y,m,d-1]);};
			this.goRight.onclick = function(){_this.showClock([y,m,d+1]);};
			this.table.innerHTML = '';
			var tr = this.table.insertRow();
            var td = tr.insertCell();
			var div = c1.dom.fragment('<div style="margin:4px auto;position:relative">').firstChild;
			td.append(div);
			var tp = new TimePicker(div, {onChange:function(){
				_this.selected.setH(tp.time.hour);
				_this.selected.setMin(tp.time.minute);
				_this.onChange();
			}});
			tp.time.hour   = this.selected.getH();
			tp.time.minute = this.selected.getMin();
			tp.updateAmPm();
			tp.moveHands();
		},
        _addClasses(el, time, format) {
            var timeStr  = dateFormat(time, format);
            var todayStr = dateFormat(today, format);
            timeStr === todayStr && el.classList.add('-today');
            for (var i=0, selected; selected=this.selected[i++];) {
                var selectedStr = dateFormat(selected, format);
                if (timeStr === selectedStr) {
                    el.classList.add('-selected');
                    break;
                }
            }
        },
        onChange(){},
	};




	c1.calendar.renderMonth = function(date, cellCallback){
		var y = date.getFullYear();
		var m = date.getMonth();
		var d = - new Date(y, m, -1).getDay(); // -1 wochenstart montag
		var table = document.createElement('table');
		table.classList.add('-table');
		times(6,function(){
			var tr = table.insertRow();
			times(7,function(){
				var time = new Date(y, m, d++);
				var td = tr.insertCell();
				td.classList.add('-td');
				td.innerHTML = time.getDate();
				m != time.getMonth() && td.classList.add('-outMonth');
				td.c1Date = time;
				cellCallback(td);
			});
		});
		return table;
	};
	c1.calendar.renderYear = function(date, cellCallback){
		var y = date.getFullYear();
		var m = 0;
		var table = document.createElement('table');
		table.classList.add('-table');
		times(3,function(){
			var tr = table.insertRow();
			times(4,function(){
				var time = new Date(y, m++);
				var td = tr.insertCell();
				td.classList.add('-td');
				td.innerHTML = time.toString().substr(4,3);
				td.c1Date = time;
				cellCallback(td);
			});
		});
		return table;
	};
	c1.calendar.renderDay = function(date, cellCallback){
		var y = date.getFullYear();
		var m = date.getMonth();
		var d = date.getDate();
		var h = 0;
		var table = document.createElement('table');
		table.classList.add('-table');
		times(6,function(){
			var tr = table.insertRow();
			times(4,function(){
				var time = new Date(y, m, d, h++);
				var td = tr.insertCell();
				td.classList.add('-td');
				td.innerHTML = time.getHours()+':00';
				td.c1Date = time;
				cellCallback(td);
			});
		});
		return table;
	}

    var trans = {
        'Y':'FullYear',
        'M':'Month',
        'D':'Date',
        'h':'Hours',
        'm':'Minutes',
        's':'Seconds',
    }
    function dateFormat(date, format) {
        var str = '';
        for (var i=0, char; char=format[i++];) {
            var fn = trans[char];
            str += date['get'+fn]() + '|';
        }
        return str;
    }
	function times(number, fn){
		while (number--) fn();
	}

}();
