'use strict';
{
	let today;
	window.qgCalendar = function(el, selected) {
		today = new Date();
		this.selected = selected;
		el.classList.add('qgCalendar');
		let fragment = c1.dom.fragment(
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
		let xbody    = this.table.firstElementChild;
		el.append(fragment);
		this.table.addRow = function() {
			let tr = document.createElement('tr');
			xbody.append(tr);
			tr.addCell = ()=>{
				let td = document.createElement('td');
				tr.append(td);
				return td;
			}
			return tr;
		};
		this.table.clean = function(){ xbody.innerHTML = ''; };
		this.dates = {};
	};
	qgCalendar.prototype = {
		show12Years(y) {
			y = y-(y+6)%12;
			this.head.immerHTML  = y+' - '+(y+12);
			this.goLeft.onclick  = ()=>this.show12Years(y-13);
			this.goRight.onclick = ()=>this.show12Years(y+12);
			this.table.clean();
			times(3,()=>{
				let tr = this.table.addRow();
				times(4,()=>{
					let time = new Date(++y,0);
					let td = tr.addCell();
					time.getFullYear() === today.getFullYear()         && td.classList.add('-today');
					time.getFullYear() === this.selected.getFullYear() && td.classList.add('-selected');
					td.innerHTML = y;
					td.value = y;
					td.onclick = ()=>{
						this.selected.setY(td.value);
						this.onChange() !== false && this.showYear(td.value);
					};
				});
			});
		},
		showYear(y) {
			this.head.innerHTML  = y;
			this.head.onclick    = ()=>this.show12Years(y);
			this.goLeft.onclick  = ()=>this.showYear(y-1);
			this.goRight.onclick = ()=>this.showYear(y+1);
			this.table.clean();
			let time = new Date(y,0);
			let m = 0;
			times(3,()=>{
				let tr = this.table.addRow();
				times(4,()=>{
					time   = new Date(y, m++);
					let td = tr.addCell();
					if (today.getFullYear() === time.getFullYear() &&
						today.getMonth() 	=== time.getMonth()) {
						td.classList.add('-today');
					}
					if (this.selected.getFullYear() === time.getFullYear() &&
						this.selected.getMonth()    === time.getMonth()) {
						td.classList.add('-selected');
					}
					td.innerHTML = time.toString().substr(4,3);
					td.value = time.getMonth();
					td.onclick = ()=>{
						this.selected.setM(td.value);
						this.onChange() !== false && this.showMonth(y,td.value);
					};
				});
			});
		},
		showMonth(y,m) {
			let D = new Date(y,m);
			y = D.getFullYear();
			m = D.getMonth();
			let month = new Date(y, m).toString().substr(4,3);
			this.head.innerHTML  = month+' '+y;
			this.head.onclick    = ()=>this.showYear(y);
			this.goLeft.onclick  = ()=>this.showMonth(y,m-1);
			this.goRight.onclick = ()=>this.showMonth(y,m+1);
			this.table.clean();
			let d = - new Date(y,m,-1).getDay(); // -1 wochenstart montag
			let time = new Date(y, m, d);
			times(6,()=>{
				let tr = this.table.addRow();
				times(7,()=>{
					time = new Date(y, m, d++);
					let td = tr.addCell();
					m != time.getMonth() && td.classList.add('-outMonth');
					if (time.getFullYear() === today.getFullYear() &&
						time.getMonth()	   === today.getMonth() &&
						time.getDate()     === today.getDate()) {
							td.classList.add('-today');
					}
					if (time.getFullYear() === this.selected.getFullYear() &&
						time.getMonth()    === this.selected.getMonth() &&
						time.getDate()     === this.selected.getDate()) {
							td.classList.add('-selected');
					}
					td.innerHTML = time.getDate();
					td.year  = time.getFullYear();
					td.month = time.getMonth();
					td.date  = time.getDate();
					td.onclick = ()=>{
						this.selected.setY(td.year);
						this.selected.setM(td.month);
						this.selected.setD(td.date);
						if (this.onChange() !== false && this.selected.getH() !== null) {
							this.showDay(td.year,td.month,td.date);
						}
					};
				});
			});
		},
		showDay(y,m,d) {
			let month = new Date(y, m).toString().substr(4,3);
			this.head.innerHTML  = d+'. '+month+' '+y;
			this.head.onclick    = ()=>this.showMonth(y,m);
			this.goLeft.onclick  = ()=>this.showDay(y,m,d-1);
			this.goRight.onclick = ()=>this.showDay(y,m,d+1);
			this.table.clean();
			let tr = this.table.addRow();
			let td = tr.addCell();
			let div = c1.dom.fragment('<div style="margin:4px auto;position:relative">').firstChild;
			td.append(div);
			let tp = new TimePicker(div, {onChange:()=>{
				this.selected.setH(tp.time.hour);
				this.selected.setMin(tp.time.minute);
				this.onChange();
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
}
