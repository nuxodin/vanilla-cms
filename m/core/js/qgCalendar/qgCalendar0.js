//http://en.design-noir.de/webdev/JS/Date.format/

qgCalendar = function(el, selected) {
	'use strict';

	var my = this;
	var today = new Date();
	my.selected = selected;

	var init = function() {
		el.addClass('qgCalendar');

		my.top 		= $('<table>').appendTo(el);
		var tbody 	= $('<tbody>').appendTo(my.top);
		var tr 		= $('<tr>').appendTo(tbody);

		my.goLeft   = $('<td>&lt;</td>').appendTo(tr);
		my.head     = $('<td>').appendTo(tr);
		my.goRight  = $('<td>&gt;</td>').appendTo(tr);
		my.table    = $('<table class=-table>').appendTo(el);
		var xbody 	= $('<tbody>').appendTo(my.table);

		my.table.addRow = function() {
			var tr = $('<tr>').appendTo(xbody);
			tr.addCell = function() {
				return $('<td>').appendTo(tr);
			};
			return tr;
		};
		my.table.clean = function() {xbody.children().each(function() {this.remove();});};
	};

	my.show12Years = function(y) {
		y = y-(y+6)%12;
		my.head.html(y+' - '+(y+12));
		my.goLeft[0].onclick  = my.show12Years.q9Pass(y-12);
		my.goRight[0].onclick = my.show12Years.q9Pass(y+12);

		my.table.clean();
		(3).q9Times(function() {
			var tr = my.table.addRow();
			(4).q9Times(function() {
				var time = new Date(++y,0);
				var td = tr.addCell();
				time.getFullYear() === today.getFullYear()       && td.addClass('-today');
				time.getFullYear() === my.selected.getFullYear() && td.addClass('-selected');
				td[0].innerHTML = y;
				td[0].value = y;
				td[0].onclick = function() {
					my.selected.setY(td[0].value);
					my.onChange() !== false && my.showYear(td[0].value);
				};
			});
		});
	};
	my.showYear = function(y) {
		my.head[0].innerHTML  = y;
		my.head[0].onclick    = my.show12Years.q9Pass(y);
		my.goLeft[0].onclick  = my.showYear.q9Pass(y-1);
		my.goRight[0].onclick = my.showYear.q9Pass(y+1);

		my.table.clean();
		var time = new Date(y,0);
		var m = 0;
		(3).q9Times(function() {
			var tr = my.table.addRow();
			(4).q9Times(function() {
				time   = new Date(y, m++);
				var td = tr.addCell();
				if (today.getFullYear() === time.getFullYear() &&
					today.getMonth() 	=== time.getMonth()) {
					td.addClass('-today');
				}
				if (my.selected.getFullYear() === time.getFullYear() &&
					my.selected.getMonth()    === time.getMonth()) {
					td.addClass('-selected');
				}
				td[0].innerHTML = time.toString().substr(4,3);
				td[0].value = time.getMonth();

				td[0].onclick = function() {
					my.selected.setM(td[0].value);
					my.onChange() !== false && my.showMonth(y,td[0].value);
				};
			});
		});
	};
	my.showMonth = function(y,m) {

		var D = new Date(y,m);
		var y = D.getFullYear();
		var m = D.getMonth();

		var month = new Date(y, m).toString().substr(4,3);

		my.head.html(month+' '+y);
		my.head[0].onclick    = my.showYear.q9Pass(y);
		my.goLeft[0].onclick  = my.showMonth.q9Pass(y,m-1);
		my.goRight[0].onclick = my.showMonth.q9Pass(y,m+1);

		my.table.clean();
		var d = - new Date(y,m,-1).getDay(); // -1 wochenstart montag
		var time = new Date(y, m, d);
		(6).q9Times(function() {
			var tr = my.table.addRow();
			(7).q9Times(function() {
				time = new Date(y, m, d++);

				var td = tr.addCell();
				m != time.getMonth() && td.addClass('-outMonth');

				if (time.getFullYear() 	=== today.getFullYear() &&
					time.getMonth() 	=== today.getMonth() &&
					time.getDate() 		=== today.getDate()) {
						td.addClass('-today');
				}
				if (time.getFullYear() 	=== my.selected.getFullYear() &&
					time.getMonth() 	=== my.selected.getMonth() &&
					time.getDate() 		=== my.selected.getDate()) {
						td.addClass('-selected');
				}
				td[0].innerHTML = time.getDate();
				td.year  = time.getFullYear();
				td.month = time.getMonth();
				td.date  = time.getDate();
				td[0].onclick = function() {
					my.selected.setY(td.year);
					my.selected.setM(td.month);
					my.selected.setD(td.date);
					if (my.onChange() !== false && my.selected.getH() !== null) {
						my.showDay(td.year,td.month,td.date);
					}
				};
			});
		});
	};
	my.showDay = function(y,m,d) {
		var month = new Date(y, m).toString().substr(4,3);
		my.head[0].innerHTML = d+'. '+month+' '+y;

		my.head[0].onclick    = my.showMonth.q9Pass(y,m);
		my.goLeft[0].onclick  = my.showDay.q9Pass(y,m,d-1);
		my.goRight[0].onclick = my.showDay.q9Pass(y,m,d+1);

		my.table.clean();
		var tr = my.table.addRow();
		var td = tr.addCell();
		var div = $('<div style="margin:4px auto;position:relative">').appendTo(td);
		var tp = new TimePicker(div, {onChange:function() {
			my.selected.setH(tp.time.hour);
			my.selected.setMin(tp.time.minute);
			my.onChange();
		}});
		tp.time.hour   = selected.getH();
		tp.time.minute = selected.getMin();
		tp.updateAmPm();
		tp.moveHands();
	};

	init();
	my.dates = {};
};

Function.prototype.q9Pass = function() {
	var fn = this, args = arguments;
	return function() {
		return fn.apply(this,args);
	};
};
Number.prototype.q9Times = function(fn) {
	for (var i = this; i--; fn() );
};
