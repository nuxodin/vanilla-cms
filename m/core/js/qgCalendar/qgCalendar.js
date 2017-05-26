!function(){
	'use strict';
	var today;
	window.qgCalendar = function(el, selected) {
		today = new Date();
		this.selected = selected;
		el.classList.add('qgCalendar');
		var fragment = c1.dom.fragment(
			'<table>'+
				'<tbody>'+
					'<tr>'+
						'<td class=-calLeft>&#9668;'+
						'<td class=-calHead>;'+
						'<td class=-calRight>&#9658;'+
			'</table>'+
			'<table class=-table><tbody></table>'
		);
		this.goLeft  = fragment.querySelector('.-calLeft');
		this.head    = fragment.querySelector('.-calHead');
		this.goRight = fragment.querySelector('.-calRight');
		this.table   = fragment.querySelector('.-table');
		el.append(fragment);
		this.dates = {};
	};
	qgCalendar.prototype = {
		show12Years:function(y) {
			var _this = this;
			y = y-(y+6)%12;
			this.head.immerHTML  = y+' - '+(y+12);
			this.goLeft.onclick  = function(){ _this.show12Years(y-13); };
			this.goRight.onclick = function(){ _this.show12Years(y+12); };
			this.table.innerHTML = '';
			times(3,function(){
				var tr = _this.table.insertRow();
				times(4,function(){
					var time = new Date(++y,0);
					var td = tr.insertCell();
					time.getFullYear() === today.getFullYear()         && td.classList.add('-today');
					time.getFullYear() === _this.selected.getFullYear() && td.classList.add('-selected');
					td.innerHTML = y;
					td.value = y;
					td.onclick = function(){
						_this.selected.setY(td.value);
						_this.onChange() !== false && _this.showYear(td.value);
					};
				});
			});
		},
		showYear:function(y) {
			var _this = this;
			this.head.innerHTML  = y;
			this.head.onclick    = function(){_this.show12Years(y);};
			this.goLeft.onclick  = function(){_this.showYear(y-1);};
			this.goRight.onclick = function(){_this.showYear(y+1);};
			this.table.innerHTML = '';
			var time = new Date(y,0);
			var m = 0;
			times(3,function(){
				var tr = _this.table.insertRow();
				times(4,function(){
					time   = new Date(y, m++);
					var td = tr.insertCell();
					if (today.getFullYear() === time.getFullYear() &&
						today.getMonth() 	=== time.getMonth()) {
						td.classList.add('-today');
					}
					if (_this.selected.getFullYear() === time.getFullYear() &&
						_this.selected.getMonth()    === time.getMonth()) {
						td.classList.add('-selected');
					}
					td.innerHTML = time.toString().substr(4,3);
					td.value = time.getMonth();
					td.onclick = function(){
						_this.selected.setM(td.value);
						_this.onChange() !== false && _this.showMonth(y,td.value);
					};
				});
			});
		},
		showMonth:function(y,m) {
			var _this = this;
			var D = new Date(y,m);
			y = D.getFullYear();
			m = D.getMonth();
			var month = new Date(y, m).toString().substr(4,3);
			this.head.innerHTML  = month+' '+y;
			this.head.onclick    = function(){_this.showYear(y);};
			this.goLeft.onclick  = function(){_this.showMonth(y,m-1);};
			this.goRight.onclick = function(){_this.showMonth(y,m+1);};
			this.table.innerHTML = '';
			var d = - new Date(y,m,-1).getDay(); // -1 wochenstart montag
			var time = new Date(y, m, d);
			times(6,function(){
				var tr = _this.table.insertRow();
				times(7,function(){
					time = new Date(y, m, d++);
					var td = tr.insertCell();
					m != time.getMonth() && td.classList.add('-outMonth');
					if (time.getFullYear() === today.getFullYear() &&
						time.getMonth()	   === today.getMonth() &&
						time.getDate()     === today.getDate()) {
							td.classList.add('-today');
					}
					if (time.getFullYear() === _this.selected.getFullYear() &&
						time.getMonth()    === _this.selected.getMonth() &&
						time.getDate()     === _this.selected.getDate()) {
							td.classList.add('-selected');
					}
					td.innerHTML = time.getDate();
					td.year  = time.getFullYear();
					td.month = time.getMonth();
					td.date  = time.getDate();
					td.onclick = function(){
						_this.selected.setY(td.year);
						_this.selected.setM(td.month);
						_this.selected.setD(td.date);
						if (_this.onChange() !== false && _this.selected.getH() !== null) {
							_this.showDay(td.year,td.month,td.date);
						}
					};
				});
			});
		},
		showDay:function(y,m,d) {
			var _this = this;
			var month = new Date(y, m).toString().substr(4,3);
			this.head.innerHTML  = d+'. '+month+' '+y;
			this.head.onclick    = function(){_this.showMonth(y,m);};
			this.goLeft.onclick  = function(){_this.showDay(y,m,d-1);};
			this.goRight.onclick = function(){_this.showDay(y,m,d+1);};
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
		}
	};
	function times(number, fn){
		while (number--) fn();
	}
}();
